/**
 * @filename showpic
 * @description
 * 作者: jinhuiguo(jinhuiguo@tencent.com)
 * 创建时间: 2014-8-13 14:56:03
 * 修改记录:
 *
 **/

define('module/topicEvent', ['lib/scroll', 'module/thread'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var thread = require('module/thread');
    module.exports = {
        isLoadingNew: true, // 需要请求数据
        isLoading: false, // 正在请求数据
        getThreadList: function(action, nextStart) {
            var start = 0;
            if (typeof nextStart == 'undefined') {
                start = window.nextStart;
            }
            var query = '';
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&').replace(/&?start=[^d]+/g, '');
            }

            module.exports.isLoading = true;
            var url = '/publicEvent?start=' + start + query;
            
            var opts = {
                'beforeSend': function() {
                    switch(action) {
                        case 'pull':
                            jq('#showAll').hide();
                            module.exports.isLoadingNew = true;
                            break;
                        case 'drag':
                            module.exports.isLoadingNew = true;
                            break;
                        default:
                            jq.DIC.showLoading();
                    }
                    module.exports.isLoadingNew = true;
                },
                'complete': function() {
                    jq('#waitForLoad').hide();
                    jq('#refreshWait').slideUp();
                    jq('#loadNext').slideUp();
                },
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0) {
                        module.exports.isLoading = false;
                        return false;
                    }

                    re.data.uId = uId || 0;
                    re.data.isFriendSite = isFriendSite || 0;
                    re.data.tlNodeId = 'tl_' + (new Date).getTime();
                    re.data.isWX = isWX;

                    var allThreadListObj = jq('.userRank');
                    var zero = new Date;
                    if (action == 'pull') {
                        // 先把内容清空，否则主题已经存在就不渲染模板
                        allThreadListObj.html('');
                        var tmpl = template.render('tmpl_thread', re.data);
                        allThreadListObj.html(tmpl);
                    } else {
                        var tmpl = template.render('tmpl_thread', re.data);
                        if (tmpl == '{Template Error}') {
                            tmpl = '';
                        }
                        jq('.infobox').hide();
                        allThreadListObj.append(tmpl);
                        // 最后无数据不再加载
                        if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                            module.exports.isLoadingNew = false;
                            jq('#loadNext').stop(true, true).hide();
                            jq('#showAll').show();
                        }
                    }
                    window.nextStart = nextStart = re.data.nextStart;

                    // 新消息数
                    if (re.data.newMsgCount > 0) {
                        newMsgCount = re.data.newMsgCount;
                        if (re.data.newMsgCount > 99) {
                            jq('#navMsgNum').html('').addClass('redP');
                        } else {
                            jq('#navMsgNum').removeClass('redP').html(re.data.newMsgCount);
                        }

                        // 上一次新消息时间
                        var date = new Date();
                        localStorage.setItem('lastNewTime', date.getTime());

                        jq('#navMsgNum').show();
                    } else {
                        jq('#newMsgCount').html(0);
                        jq('#navMsgNum').hide();
                    }

                    // 帖子数
                    if (re.data.threadCount >= 0) {
                        jq('#threadCount').html(re.data.threadCount);
                    }

                    // pv
                    if (re.data.sitePV >= 1) {
                        jq('#sitePV').html(re.data.sitePV);
                    }
                    // 图片张数初始化
                    thread.initScrollImage('#' + re.data.tlNodeId);
                    module.exports.isLoading = false;
                    jq('#refreshWait').hide();
                },
                error: function() {
                    module.exports.isLoading = false;
                }
            };
            jq.DIC.ajax(url, '', opts);
        },
        // init
        init: function() {

            // 图片横滑初始化
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.warp'});
            // lazyload
            initLazyload('.warp img');
            // 点击看大图
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });
            
            //热门话题 社区排行标签点击
            jq('.topicTab').on('click', '.topicTabBtn', function(){
                var thisObj = jq(this),
                    thisId = thisObj.attr('data-id'),
                    detailShow = jq('.detailShow'),
                    topicTabBtn = jq('.topicTabBtn'),
                    rankBoxCon = jq('.rankBoxCon');
                detailShow.hide();
                topicTabBtn.removeClass('on');
                rankBoxCon.hide();
                jq(detailShow[thisId]).show();
                thisObj.addClass('on');
                jq(rankBoxCon[thisId]).show();
                return false;
            })

            //活动规划查看全文
            jq('.detailShow').on('click', '.evtShowAll', function(){
                var thisObj = jq(this);
                var thisParent = thisObj.parent();
                var evtContent = thisParent.find('.evtContent');
                var dataSummary = evtContent.attr('data-summary');
                var dataDesc = evtContent.attr('data-desc');
                if(thisObj.text() == '查看全文'){
                    evtContent.html(dataDesc);
                    thisObj.text('收起');
                }else{
                    evtContent.html(dataSummary);
                    thisObj.text('查看全文');
                }
                return false;
            });

            //dConText-话题内容 siteList-社区排行点击
            jq('.rankBoxCon').on('click', '.dConText, .siteList', function(){
                var thisObj = jq(this);
                var url = thisObj.attr('data-link');
                jq.DIC.touchStateNow(thisObj);
                jq.DIC.open(url);
                return false;
            })

            // like
            jq.DIC.touchState('.like', 'incoBg', '.warp');
            jq('.userRank').on('click', '.like', function(e) {
                e.stopPropagation();

                var thisObj = jq(this);
                if(thisObj.children('i').attr('class') == 'praise') {
                    return;
                }
                var sId = thisObj.parents('.personTopic').attr('sId'),
                    tId = thisObj.parents('.personTopic').attr('tId'),
                    parentId = thisObj.parents('.personTopic').attr('parentId');
                var opts = {
                    'success': function(result) {
                        if (result.errCode == 0 && result.data && result.data.likeNumber) {
                            jq.DIC.likeTips(thisObj);
                            thisObj.html('<i class="iconPraise f18 cf"></i>' + result.data.likeNumber);
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'parentId': parentId, 'CSRFToken':CSRFToken}, opts);
                return false;
            });
            //加载热门话题
            module.exports.getThreadList('pull', 0);
            //向下滚动加载
            var scrollPosition = jq(window).scrollTop();
            var loadingObj = jq('#loadNext');
            var loadingPos = jq('#loadNextPos');
            jq(window).scroll(function() {
                if (scrollPosition < jq(window).scrollTop() && jq('#listThread').hasClass('on')) {
                    if (!module.exports.isLoading && module.exports.isLoadingNew) {
                        var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                        if (loadingObjTop <= 10) {
                            module.exports.isLoading = true;
                            jq('#loadNext').stop(true, true).slideDown('fast');
                            module.exports.getThreadList('drag');
                        }
                    }
                }
                scrollPosition = jq(window).scrollTop();
            });

        }
    };
    module.exports.init();
});
