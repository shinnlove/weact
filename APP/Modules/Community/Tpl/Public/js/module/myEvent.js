/**
 * Created by babuwang on 14-9-12.
 */

define('module/myEvent', [], function (require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        isLoadingNew: true,
        isLoading: false,
        isNoShowToTop: false,
        nextStart: 0,
        load: function (start, action) {
            start = start || 0;
            action = action || '';

            module.exports.isLoading = true;

            var url = '/my/event'
                + '?start=' + start;
            var opts = {
                'beforeSend': function () {
                    switch (action) {
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
                'complete': function () {
                },
                'success': function (re) {
                    jq('#refreshWait').hide();
                    jq('#loadNext').hide();
                    jq.DIC.showLoading('none');
                    if (re.errCode == 0) {
                        module.exports.renderList(re, !start);
                    } else {
                        jq.DIC.dialog({content: '拉取数据失败，请重试', autoClose: true});
                    }
                    module.exports.isLoading = false;
                }
            };
            jq.DIC.ajax(url, '', opts);
        },
        renderList: function (re, clear) {

            if (clear) {
                jq('.evtActivityList').html('');
            }
            // 最后无数据不再加载
            if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                module.exports.isLoadingNew = false;
                jq('#loadNext').hide();
                if (jq('.evtActivityList .evtTopicBox').length < 1) {
                    jq('#showAll').html('快去参加活动，精彩多多~').show();
                } else {
                    jq('#showAll').html('已显示全部').show();
                }
                return true;
            }

            var threadHtml = template.render('tmpl_event', re.data);
            if (jq.trim(threadHtml) !== '') {
                jq('.evtActivityList').append(threadHtml);
            }
            module.exports.nextStart = re.data.nextStart;

        },
        init: function () {
            //首次加载数据，渲染
            module.exports.load(module.exports.nextStart, 'drag');
            //事件绑定
            module.exports.bindEvent();

            initLazyload('.warp img');

            var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
            // 全屏触摸
            jq.DIC.initTouch({
                obj: jq('.warp')[0],
                end: function (e, offset) {
                    document.ontouchmove = function (e) {
                        return true;
                    }
                    var loadingObj = jq('#loadNext');
                    var loadingPos = jq('#loadNextPos');
                    var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                    // 向上滑
                    if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !module.exports.isLoading) {
                        module.exports.load(module.exports.nextStart, 'drag');
                    }
                    // 向下拉刷新
                    if (offset.y < level && document.body.scrollTop <= 0) {
                    }
                }
            });
        },
        bindEvent: function () {
            jq('.warp').on('click', '.evtTopicBox',function () {
                //点击话题区域
                var that = jq(this),
                    link = that.attr('data-link') || '';
                if (link) {
                    jq.DIC.open(link);
                }
                return false;
            }).on('click', '.evtLink', function (e) {
                //点击微社区名称
                e.stopPropagation(e);
                var that = jq(this),
                    link = that.attr('data-link') || '';
                if (link) {
                    jq.DIC.open(link);
                }
                return false;
            });
        }
    };
    module.exports.init();

});