/**
 * @filename userThread.js
 * @description
 * 作者: vissong
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: userThread.js 32319 2014-08-26 02:16:44Z jinhuiguo $
 **/

define('module/userThread', ['lib/scroll', 'module/thread'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var thread = require('module/thread');
    module.exports =  {
        initViewBtn: function() {
            jq('.detailCon').each(function(e) {
                if (jq(this).find('p')[0].scrollHeight > 165) {
                    jq(this).find('.viewBtn').show();
                }
            });
        },
        // 初始化滚动图片，如果宽度小于滚动宽度则不显示张数
        initScrollImage :function() {
            jq('.slideBox').each(function(e) {
                var liArray = jq(this).find('li');
                var liWidth = 0;
                for (var i = 0; i< liArray.length; i++) {
                    liWidth += jq(liArray[i]).width();
                }
                // console.log(liWidth);
                // console.log(liWidth, jq(this).width());
                if (jq(this).width() < liWidth) {
                    jq(this).find('.pageNum').show();
                }
            });
        },
        cleanPost: function(uId, author) {
             var content = '确认清理“'+author+'”的所有话题吗';
             var opts = {
                 'id':'opertionConfirm',
                 'isMask':true,
                 'content':content,
                 'okValue':'确定',
                 'cancelValue':'取消',
                 'ok':function() {
                     jq('#cleanPostForm [name="uId"]').val(uId);
                     var opt = {
                         success:function(re) {
                             jq.DIC.dialog({id:'operationMenu'});
                             var status = parseInt(re.errCode);
                             if(status == 0) {
                                 //todo del list
                             }
                         },
                         'noJump':true
                     };
                     jq.DIC.ajaxForm('cleanPostForm', opt, true);
                 }
             };
             jq.DIC.dialog(opts);
        },
        foldSwith: function(e) {
            var text = jq(this).html();
            var height = '', returnTop = false;
            if (text == '收起') {
                returnTop = true;
                height = '150px';
                text = '全文';
            } else {
                height = 'none';
                text = '收起';
            }
            jq(this).parent().find('p').css('max-height', height);
            jq(this).html(text);
            // 收起的时候，回文章处
            if (returnTop) {
                scroll(0, jq(this).parent().parent().parent().position().top);
            }
        },
        isLoadingNew: true,
        init: function(nextStart) {

            // 全文展开显隐初始化
            module.exports.initViewBtn();

            // 图片张数初始化
            module.exports.initScrollImage();

            jq('.container').on('click', '.ptTitle, .threadContent', function() {
                var thisObj = jq(this);
                if(thisObj.hasClass('ptTitle')){
                    var link = thisObj.find('.threadContent').attr('data-link') || '';
                }else{
                    var link = thisObj.attr('data-link') || '';
                }
                
                if (!link) {
                    return false;
                }
                jq.DIC.open(link);
                return false;
            });

            // 全文展开切换
            jq('.container').on('click', '.viewBtn', function() {
                var thisObj = jq(this);
                thisObj.addClass('commBg');
                setTimeout(function(){
                    thisObj.removeClass('commBg');
                    module.exports.foldSwith.call(thisObj);
                }, 50);
            });

            // 图片横滑
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.container'});

            // 点击看大图
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });

            initLazyload('.slideBox img');

            var query = '';
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&');
            }

            var isLoading = false;
            // 全屏触摸
            jq.DIC.initTouch({
                'obj': jq('.warp')[0],
                'end': function(e, offset) {
                    document.ontouchmove = function(e){ return true;}
                    var loadingObj = jq('#loadNext');
                    var loadingPos = jq('#loadNextPos');
                    // var loadingObjTop = loadingObj.offset().top + loadingObj.height() - jq(window).scrollTop();
                    var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                    // 向上滑
                    if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !isLoading) {
                        isLoading = true;
                        var url = '/my/thread?start=' + nextStart + query;
                        var opts = {
                            'beforeSend': function() {
                                loadingObj.show();
                            },
                            'complete': function() {
                                loadingObj.hide();
                            },
                            'success': function(re) {
                                var status = parseInt(re.errCode);
                                if (status == 0) {
                                    var tmpl = template.render('tmpl_thread', re.data);
                                    jq('.container #allThreadList').append(tmpl);
                                    nextStart = re.data.nextStart;
                                    module.exports.initViewBtn();
                                    // 图片张数初始化
                                    module.exports.initScrollImage();
                                    // 最后无数据不再加载
                                    if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                                        module.exports.isLoadingNew = false;
                                        jq('#showAll').show();
                                    }
                                }
                                // initLazyload('.slideBox img');
                                isLoading = false;
                            },
                            error: function() {
                                isLoading = false;
                            }
                        };
                        jq.DIC.ajax(url, '', opts);
                    }
                }
            });
            jq('#cleanPost').on('click', function() {
                module.exports.cleanPost(jq(this).attr("uId"), jq(this).attr("author"));
            });

            // Pop菜单
            var perPop = function () {
                    var tId = jq(this).attr('tId');
                    if(jq('#t_' + tId + ' .perPop').css('display') == 'none') {
                        jq('#t_' + tId + ' .perPop').show();
                        setTimeout(function(){
                            jq(document).attr('perPopId', tId);
                        }, 50);
                    } else {
                        jq('#t_' + tId + ' .perPop').hide();
                        jq(document).attr('perPopId', '');
                    }
            };

            jq(document).bind("click", function(){
                var perPopId = jq(document).attr('perPopId');
                if(perPopId) {
                   jq('#t_' + perPopId + ' .perPop').hide();
                   jq(document).attr('perPopId', '');
                }
            });

            jq('.warp').on('click', '.PerPopBtn', function() {
                var thisObj = jq(this);
                perPop.call(thisObj);
            });

            jq('.warp').on('click', '.perPop .perBCon', function() {
                var thisObj = jq(this);
                var tId = thisObj.parent().attr('tId');
                thisObj.addClass('perH');
                setTimeout(function(){
                    thisObj.removeClass('perH');
                     jq('#t_' + tId + ' .perPop').hide();
                }, 50);
            });

            // 删除自已主题
            jq('.warp').on('click', '.perPop .deleteThread', function() {
                var thisObj = jq(this);
                var tId = thisObj.parent().attr('tId').split('_');
                jq('#threadForm [name="tId"]').val(tId[1]);
                jq('#threadForm').attr('action', '/' + tId[0] + '/t/del');

                jq.DIC.dialog({
                    'content':'确定删除这个主题吗？',
                    'okValue':'确定',
                    'cancelValue':'取消',
                    'isMask':true,
                    'ok':function () {
                        var opt = {
                            success:function(re) {
                                jq.DIC.dialog({id:'operationMenu'});
                                var status = parseInt(re.errCode);
                                if(status == 0) {
                                    jq("#t_" + thisObj.parent().attr('tId')).remove();
                                }
                            },
                            'noJump':true
                        };
                        jq.DIC.ajaxForm('threadForm', opt, true);
                    }
                });
            });
            jq('.warp').on('click', '.like', function(e) {
            // 点赞
                e.stopPropagation(e);
                var that = jq(this);
                var sId = that.attr('sId');
                var tId = that.attr('tId');
                var likeObj = that.find('i');
                jq.DIC.touchStateNow(that);

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
                // 已赞
                if (likeObj.hasClass('iconPraise')) {
                    jq.DIC.dialog({id: 'likeed', content: '已赞过', autoClose: true});
                    return false;
                }
                var opts = {
                    'success': function(re) {
                        var status = parseInt(re.errCode);
                        if (status !== 0 || !re.data || !re.data.likeNumber) {
                            return false;
                        }

                        likeObj.removeClass('iconNoPraise').addClass('iconPraise');
                        that.find('.likeNum').html(re.data.likeNumber);

                        if (window.isFriendSite) {
                            jq('#t_' + tId + '_0_0').find('.blur').removeClass();
                            jq('#t_' + tId + '_0_0').find('.slideText').css('display', 'none');
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'CSRFToken':CSRFToken}, opts);
                return false;
            }).on('click', '.threadReply', function(e) {
            // 主题的回复按纽
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var sId = that.parent().attr('sId');
                var tId = that.parent().attr('tId');
                var nodeId = 't_' + tId + '_0_0';
                window.sId = sId;
                thread.reply(sId, tId, 0, 0, 0, '', false, nodeId, true);
                return false;
            })

            jq('.topicRank').on('click', function() {
                var thisObj = jq(this);
                var sId = thisObj.attr('sId');
                jq.DIC.reload('/likedrank/' + sId + '?position=mt.top');
            });
        }
    };
    module.exports.init(nextStart);
});
