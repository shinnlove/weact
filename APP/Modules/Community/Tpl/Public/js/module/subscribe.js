/**
 * Created by babuwang on 14-9-18.
 */
define('module/subscribe', ['lib/scroll', 'module/gps', 'module/share'], function (require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var gps = require('module/gps');
    var libScroll = require('lib/scroll');
    var share = require('module/share');
    module.exports = {
        isLoadingNew: true,
        isLoading: false,
        isNoShowToTop: false,
        nextStart: 0,
        load: function (start, action) {
            start = start || 0;
            action = action || '';

            module.exports.isLoading = true;

            var url = '/cityhot'
                + '?start=' + start
                + '&subscribe=' + 1;
            var opts = {
                'beforeSend': function () {
                    switch (action) {
                        case 'loading':

                            break;
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
                    if (re.errCode == 0 && re.jumpURL == null) {
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
                jq('.evtSubscribeList').html('');
            }
            // 最后无数据不再加载
            if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                module.exports.isLoadingNew = false;
                jq('#loadNext').hide();
                if (jq('.evtSubscribeList .evtTopicBox').length < 1) {
                    jq('#showAll').html('快去参加活动，精彩多多~').show();
                } else {
                    jq('#showAll').html('已显示全部').show();
                }
                return true;
            }
            re.data.isWX = window.isWX;

            var threadHtml = template.render('tmpl_subscribe', re.data);
            if (jq.trim(threadHtml) !== '') {
                jq('.evtSubscribeList').append(threadHtml);
            }
            module.exports.initScrollImage();
            module.exports.nextStart = re.data.nextStart;

        },
        init: function () {
            // 检查地理信息
            var get_gps = jq.DIC.getcookie('get_gps');
            if (!get_gps) {
                gps.getLocation(function (latitude, longitude) {
                    jq.DIC.ajax('/checkcity', {
                        'CSRFToken': CSRFToken,
                        'latitude': latitude,
                        'longitude': longitude
                    }, {
                        'noShowLoading': true,
                        'noMsg': true,
                        'success': function (re) {
                        },
                        'complete': function () {
                            jq.DIC.setcookie('get_gps', '1', 2592000);
                        }
                    });
                });
            }
            // 图片横滑初始化
            libScroll.initScroll({ulSelector: '.slideBox ul', isFlip: false, cSelector: '.warp'});
            // lazyload
            initLazyload('.warp img');
            // 点击看大图
            require.async('module/imageviewCommon', function (imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });
            //首次加载数据，渲染
            module.exports.load(module.exports.nextStart, 'loading');
            //事件绑定
            module.exports.bindEvent();

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
        },// 初始化滚动图片，如果宽度小于滚动宽度则不显示张数
        initScrollImage: function (id) {
            var id = id || '';
            jq(id + ' .slideBox').each(function (e) {
                var thisObj = jq(this);
                var liArray = thisObj.find('li');
                var liWidth = 0;
                for (var i = 0; i < liArray.length; i++) {
                    liWidth += jq(liArray[i]).width();
                }
                if (thisObj.width() < liWidth) {
                    thisObj.find('.pageNum').show();
                }
            });
        },
        bindEvent: function () {
            jq('.warp').on('click', '.evtTopicBox',function () {
                //点击话题区域
                var thisObj = jq(this),
                    sId = thisObj.attr('sId') || 0,
                    tId = thisObj.attr('tId') || 0;

                if (sId && tId) {
                    jq.DIC.open('/' + sId + '/t/' + tId);
                }
                return false;

            }).on('click', '.evtLink',function (e) {
                //点击微社区名称
                e.stopPropagation(e);
                var thisObj = jq(this),
                    sId = thisObj.parents('.evtTopicBox').attr('sId') || 0,
                    tId = thisObj.parents('.evtTopicBox').attr('tId') || 0;
                if (sId && tId) {
                    jq.DIC.open('/' + sId);
                }
                return false;

            }).on('click', '.evtLike',function (e) {
                //点赞
                e.stopPropagation();
                var thisObj = jq(this);
                if (thisObj.children('i').hasClass('iconPraise')) {
                    return;
                }

                var sId = thisObj.parents('.evtTopicBox').attr('sId') || 0,
                    tId = thisObj.parents('.evtTopicBox').attr('tId') || 0,
                    parentId = thisObj.parents('.personTopic').attr('parentId');
                var opts = {
                    'success': function (result) {
                        if (result.errCode == 0 && result.data && result.data.likeNumber) {
                            jq.DIC.likeTips(thisObj);
                            thisObj.html('<i class="iconPraise f18 cf"></i>赞');
                        }
                    },
                    'noShowLoading': true,
                    'noMsg': true
                }
                if (sId && tId) {
                    jq.DIC.ajax('/' + sId + '/like', {'tId': tId, 'parentId': parentId, 'CSRFToken': CSRFToken}, opts);
                }
                return false;

            }).on('click', '.evtReply', function (e) {
                //回复
                e.stopPropagation();
                var thisObj = jq(this),
                    sId = thisObj.parents('.evtTopicBox').attr('sId') || 0,
                    tId = thisObj.parents('.evtTopicBox').attr('tId') || 0;
                if (sId && tId) {
                    jq.DIC.open('/' + sId + '/t/' + tId + '?action=reply');
                }
                return false;

            });
        }
    };
    module.exports.init();
});
