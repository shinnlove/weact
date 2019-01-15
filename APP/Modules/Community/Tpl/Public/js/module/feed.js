/**
 * @filenamea feed
 * @description
 * 作者: vissong
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: feed.js 31497 2014-08-04 02:12:07Z andyzheng $
 **/

define('module/feed', ['lib/scroll', 'module/thread'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var thread = require('module/thread');
    module.exports = {
        // 初始化滚动图片，如果宽度小于滚动宽度则不显示张数
        initScrollImage :function() {
            jq('.slideBox').each(function(e) {
                var liArray = jq(this).find('li');
                var liWidth = 0;
                for (var i = 0; i< liArray.length; i++) {
                    liWidth += jq(liArray[i]).width();
                }
                if (jq(this).width() < liWidth) {
                    jq(this).find('.pageNum').show();
                }
            });
        },
        init:function() {

            // lazyload
            initLazyload('.warp img');

            // 图片张数初始化
            module.exports.initScrollImage();

            // 图片横滑初始化
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.container'});

            // 图片点击查看大图
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });

            // 点击卡片进入帖子
            jq('.warp').on('click', '.topicCon', function() {
                var thisObj = jq(this);
                // todo 晒图帖处理
                var parentId = thisObj.attr('parentId') || 0;
                var tId = thisObj.attr('tId');
                var url = '/' + sId;
                if (parentId) {
                    url += '/v/' + parentId;
                }
                url += '/t/' + tId;
                jq.DIC.open(url);
                return false;
            });

            // 全文展开显隐初始化
            thread.initViewBtn('.detailCon');
            thread.initViewBtn('.replyCon');
            thread.initViewBtnClick('.container');


            // 卡片头像点击
            jq('.container').on('click', '.threadAvatar', function(e) {
                e.stopPropagation();
                var uId = jq(this).attr('uId');
                jq.DIC.open('/profile/' + uId);
                return false;
            });

            var replyId = '.threadReply';
            jq.DIC.touchState(replyId, 'incoBg', '.warp');

            // 主题和底部bar 帖点击回复
            jq('.container').on('click', replyId, function(e) {
                e.stopPropagation();
                var thisObj = jq(this);
                var nodeId = thisObj.closest('.topicBox').attr('id');
                var divId = thisObj.attr('id'), pId, floorPId, author;
                var parentId = thisObj.attr('parentId') || 0;
                if (/p_\d+_\d+_\d+/.test(divId)) {
                    if (match = divId.match(/p_(\d+)_(\d+)_(\d+)/)) {
                        tId = match[1];
                        pId = match[2];
                        floorPId = match[3];
                    }
                    author = thisObj.attr('author') || '';
                    thread.reply(sId, tId, parentId, pId, floorPId, author, false, nodeId);
                }
            });

            // 模板中使用方法
            template.helper('isDOMExist', function (id) {
                if (jq('#' + id)[0]) {
                    return true;
                } else {
                    return false;
                }
            });

            // like
            jq.DIC.touchState('.like', 'incoBg', '.warp');
            jq('.container').on('click', '.like', function(e) {
                e.stopPropagation();
                // 未登录且是应用吧页
                var reapp = /qqdownloader\/([^\s]+)/i;
                if (authUrl && reapp.test(navigator.userAgent)) {
                    return false;
                }
                // 未登录
                if (authUrl) {
                    jq.DIC.reload(authUrl);
                    return false;
                }
                var thisObj = jq(this);
                if(thisObj.children('i').attr('class') == 'praise') {
                    return;
                }
                var tId = thisObj.attr('tId');
                var parentId = thisObj.attr('parentId') || 0;
                var opts = {
                    'success': function(result) {
                        if (result.errCode == 0 && result.data && result.data.likeNumber) {
                            if (parentId > 0) {
                                jq.DIC.likeTips(thisObj);
                            }
                            thisObj.html('<i class="praise"></i>' + result.data.likeNumber);
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'CSRFToken':CSRFToken, 'parentId':parentId}, opts);
            });

            jq('.topicRank').on('click', function() {
                jq.DIC.reload('/likedrank/' + sId + '?position=st.top');
            });

            // 头部点击
            jq('.header').on('click', function() {
                if (sId) {
                    jq.DIC.open('/' + sId);
                }
                return false;
            });

            var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
            // 全屏触摸上下滑
            jq.DIC.initTouch({obj:jq('.warp')[0], end:function(e, offset) {
                document.ontouchmove = function(e){ return true;}
                var loadingObj = jq('#loadNext');
                var loadingPos = jq('#loadNextPos');
                // var loadingObjTop = loadingObj.offset().top + loadingObj.height() - jq(window).scrollTop();
                var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                // 向上滑
                if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !module.exports.isLoading) {
                    module.exports.getThreadList('drag');
                }
                // 向下拉刷新
                if (offset.y < level && document.body.scrollTop <= 0) {
                    module.exports.getThreadList('pull', 0);
                }
            }
            });

        },
        isLoadingNew: true,
        isLoading: false,
        getThreadList: function(action, nextStart) {
            var start = 0;
            if (typeof nextStart == 'undefined') {
                start = window.nextStart;
            }

            module.exports.isLoading = true;
            var url = '/' + sId + '/star?start=' + start;
            var opts = {
                'beforeSend': function() {
                    switch(action) {
                        case 'pull':
                            jq('#refreshWait').show();
                            jq('#showAll').hide();
                            module.exports.isLoadingNew = true;
                            break;
                        case 'drag':
                            jq('#loadNext').show();
                            module.exports.isLoadingNew = true;
                            break;
                        default:
                            jq.DIC.showLoading();
                    }
                    module.exports.isLoadingNew = true;
                },
                'complete': function() {
                    jq('#waitForLoad').hide();
                    jq('#refreshWait').hide();
                    jq('#loadNext').hide();
                },
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status == 0) {
                        var allThreadListObj = jq('.container #allThreadList');
                        re.data.isWX = isWX;
                        if (action == 'pull') {
                            // 先把内容清空，否则主题已经存在就不渲染模板
                            allThreadListObj.html('');
                            var tmpl = template.render('tmpl_thread', re.data);
                            allThreadListObj.html(tmpl);
                        } else {
                            var tmpl = template.render('tmpl_thread', re.data);
                            allThreadListObj.append(tmpl);
                            // 最后无数据不再加载
                            if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                                module.exports.isLoadingNew = false;
                                jq('#showAll').show();
                            }
                        }
                        window.nextStart = re.data.nextStart;

                        // 新消息数
                        if (re.data.newMsgCount > 0) {
                            newMsgCount = re.data.newMsgCount;
                            if (re.data.newMsgCount > 99) {
                                jq('#navMsgNum').html('');
                                jq('#navMsgNum').addClass('redP');
                            } else {
                                jq('#navMsgNum').removeClass('redP');
                                jq('#navMsgNum').html(re.data.newMsgCount);
                            }
                            jq('#navMsgNum').show();
                        } else {
                            jq('#newMsgCount').html(0);
                            jq('#navMsgNum').hide();
                        }

                        thread.initViewBtn('.detailCon');
                        thread.initViewBtn('.replyCon');
                        // 图片张数初始化
                        thread.initScrollImage();
                    }
                    // initLazyload('.warp img');
                    module.exports.isLoading = false;
                },
                error: function() {
                    module.exports.isLoading = false;
                }
            };
            jq.DIC.ajax(url, '', opts);
        }
    };
    module.exports.init();
});
