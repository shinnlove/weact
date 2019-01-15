/**
 * @filename showpic
 * @description
 * 作者: jeffjzhang(jeffjzhang@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: showpic.js 32586 2014-09-03 01:45:25Z jinhuiguo $
 **/

define('module/showpic', ['module/followSite', 'module/wxFollow'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var followSite = require('module/followSite');
    var wxFollow = require('module/wxFollow');
    module.exports = {
        isLoadingNew: true,
        isLoading: false,
        isNoShowToTop: false,
        picTId: 0,
        isEnd: 0,
        nextStart: 0,
        order: '',
        initNav: function() {

            module.exports.order = jq.DIC.getQuery('order') || 'hot';
            module.exports.load(0, false);

            //最热点击事件
            jq('#listHot').on('click', function() {
                module.exports.order = 'hot'
                module.exports.load(0, false);
            });
            //最新点击事件
            jq('#listNew').on('click', function() {
                module.exports.order = 'new'
                module.exports.load(0, false);
            });
            //微视点击事件
            jq('#listWeishi').on('click', function() {
                module.exports.order = 'weishi'
                module.exports.load(0, false);
            });
            jq('.topicTab a').on('click', function() {
                jq('.topicTab a').removeClass('on');
                jq(this).addClass('on');
            });
        },
        // load data,all in one
        load: function(start, action) {
            start = start || 0;
            action = action || '';

            if (start == 0) {
                jq('#showAll').hide();
                module.exports.isLoadingNew = true;
            }

            module.exports.isLoading = true;
            var url = DOMAIN + window.sId + '/v/' + module.exports.picTId
                + '?start=' + start + '&order=' + module.exports.order;
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
                },
                'complete': function() {
                    jq('#waitForLoad').hide();
                },
                'success': function(re) {
                    jq('#refreshWait').hide();
                    jq('#loadNext').hide();
                    jq.DIC.showLoading('none');
                    if (re.errCode == 0) {
                        module.exports.isEnd = re.data.isEnd;

                        module.exports.renderList(re, !start);
                    } else {
                        jq.DIC.dialog({content: '拉取数据失败，请重试', autoClose: true});
                    }
                    module.exports.isLoading = false;
                }
            };
            jq.DIC.ajax(url, '', opts);
        },
        // render data
        renderList: function(re, clear) {
            if (clear) {
                jq('.container #allThreadList').html('');
            }

            // 最后无数据不再加载
            if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                module.exports.isLoadingNew = false;
                if (jq('#allThreadList li').length < 1) {
                    jq('#showAll').html('快点击“我也参加”，越早参与，排名越高哦~').show();
                } else {
                    jq('#showAll').html('已显示全部').show();
                }
                return false;
            }
            re.data.order = module.exports.order;

            var tmpl = template.render('tmpl_thread', re);
            jq('.container #allThreadList').append(tmpl);
            module.exports.nextStart = re.data.nextStart;

            if (clear) {
                if (module.exports.order == 'hot') {
                    jq('.badge').show();
                } else {
                    jq('.badge').hide();
                }
            }
        },
        init: function() {

//            window.isForceReload = 1;
            module.exports.picTId = window.picThreadTId;

            initLazyload('.warp img');

            module.exports.initNav();

            // 翻页相关
            var query = '';
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&');
            }

            var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
            // 全屏触摸
            jq.DIC.initTouch({
                obj: jq('.warp')[0],
                end: function(e, offset) {
                    document.ontouchmove = function(e){ return true;}
                    var loadingObj = jq('#loadNext');
                    var loadingPos = jq('#loadNextPos');
                    // var loadingObjTop = loadingObj.offset().top + loadingObj.height() - jq(window).scrollTop();
                    var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                    var readTIdsArr = [];
                    // 向上滑
                    if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !module.exports.isLoading) {
                        module.exports.load(module.exports.nextStart, 'drag');
                    }
                    // 向下拉刷新
                    if (offset.y < level && document.body.scrollTop <= 0) {
                        module.exports.load(0, 'pull');
                    }
                }
            });

            // content && summary
            /**
             * @desc 活动内容点击展示全部内容，如果显示为接要时，显示展开详情链接，点击展示，再次点击显示摘要
             * @objs {}
             *  contentWrap : 显示域名容器
             *  contentBox  : 内容容器
             *  summaryData : 摘要内容
             *  contentHideWrap ：隐藏的内容容器
             *  contentHideText ：隐藏全文
             * */
            var contentWrap = jq('.detailShow'),
                contentBox =  contentWrap.children('.evtContent'),
                summaryData = contentBox.html(),
                contentHideWrap = jq('.evtContentHide'),
                contentHideText = contentHideWrap.html();


            if (contentBox.attr('showMore') === "1") {
                jq('.detailShow a.evtShowAll').show();
                jq.DIC.touchState('.detailShow');

                // 展开内容点击绑定
                contentWrap.on('click', function() {
                    var obj = jq(this);
                    var aObj = obj.find('.evtShowAll');

                    if (aObj.hasClass('evtShowAll')) {
                        if (aObj.hasClass('showMore')) {
                            aObj.removeClass('showMore');
                            contentBox.html(summaryData);
                        } else {
                            aObj.addClass('showMore');
                            contentBox.html(contentHideText);
                            aObj.remove();//需求上没要求再收起，先remove
                        }
                    }
                });
            }

            // see one
            jq('.listShow').on('click', 'li', function() {
                var tId = jq(this).attr('_tId'),
                    url = DOMAIN + window.sId + '/v/' + module.exports.picTId + '/t/' + tId;
                jq.DIC.open(url);
            });

            jq('#joinpic').on('click', function() {
                var url = jq(this).attr('href');
                
                if(jq.DIC.getQuery('filterType')){
                    var filerType = jq.DIC.getQuery('filterType');
                    url = url + '?filterType=' + filerType;
                }

                jq.DIC.reload(url);
                
                return false;
            });


            // like
            jq.DIC.touchState('.like', 'incoBg', '.warp');
            jq('.listShow').on('click', '.like', function(e) {
                e.stopPropagation();
                if (module.exports.isEnd) {
                    jq.DIC.dialog({content: '活动已结束，请不要再赞了', autoClose: true});
                    return false;
                }

                var thisObj = jq(this);
                if(thisObj.children('i').attr('class') == 'praise') {
                    return;
                }
                var tId = thisObj.attr('tId'),
                    parentId = thisObj.attr('parentId');
                var opts = {
                    'success': function(result) {
                        if (result.errCode == 0 && result.data && result.data.likeNumber) {
                            jq.DIC.likeTips(thisObj);
                            thisObj.html('<i class="praise"></i>' + result.data.likeNumber);

                            if (isWX && isWeixinLink && jq.DIC.getQuery('source')) {
                                wxFollow.wxFollowTips();
                            }
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'parentId': parentId, 'CSRFToken':CSRFToken}, opts);
            });

            //晒图点击查看全部，判断是否有参数showAll=1，如果有展开活动内容，否则不展开
            if(jq.DIC.getQuery('showAll')){
                jq('.detailShow').trigger('click');
            }
        }
    };
    module.exports.init();
});
