/**
 * @filename portalHotTopics
 * @description
 * 作者: zoroweili(zoroweili@tencent.com)
 * 创建时间: 2014-04-010 10:28:00
 * 修改记录:
 *
 * $Id: portalHotTopics.js 31122 2014-07-22 03:49:21Z jeffjzhang $
 **/
define('module/portalHotTopics', null, function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        isLoadingNew: true,
        isLoading: false,
        isNoShowToTop: false,
        picTId: 0,
        nextStart: 0,
        hotTopoicLikeTIds: [],
        htltKey: '',
        init: function(divId) {
            template.helper('in_array', function (id, arr) {
                if (jq.DIC.in_array(id, arr)) {
                    return true;
                } else {
                    return false;
                }
            });
            if (uId) {
                module.exports.htltKey = 'htLikeTIds_' + uId
                var htlt = localStorage.getItem(module.exports.htltKey) || [];
                if (htlt.length > 0) {
                    module.exports.hotTopoicLikeTIds = htlt.split(',');
                }
            }
            var url = '/cityhot?start=0';
            var opts = {
                'requestMode': 'abort',
                'requestIndex': 'portal',
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if(status == 0) {
                        re.data.likeTIds = module.exports.hotTopoicLikeTIds;
                        var tmpl = template.render('tmpl_hotTopics', re.data);
                        jq('#HotTopics').html(tmpl);

                        var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
                        // 全屏触摸
                        jq.DIC.initTouch({
                            obj: jq('.warp')[0],
                            end: function(e, offset) {
                                document.ontouchmove = function(e){ return true;}
                                var loadingObj = jq('#loadNext');
                                var loadingPos = jq('#loadNextPos');
                                //var loadingObjTop = loadingObj.offset().top + loadingObj.height() - jq(window).scrollTop();
                                var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                                var readTIdsArr = [];
                                //向上滑
                                if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !module.exports.isLoading) {
                                    module.exports.load(module.exports.nextStart, 'drag');
                                }
                                //向下拉刷新
                                if (offset.y < level && document.body.scrollTop <= 0) {
                                    module.exports.load(0, 'pull');
                                }
                            }
                        });
                        module.exports.nextStart = re.data.nextStart;
                        module.exports.bindEvent(re);
                    }
                },
                'noShowLoading' : true,
                'noMsg' : true
            }
            jq.DIC.ajax(url, '', opts);
        },
        load: function(start, action) {
            start = start || 0;
            action = action || '';

            if (start == 0) {
                jq('#showAll').hide();
                module.exports.isLoadingNew = true;
            }

            module.exports.isLoading = true;
            var url = '/cityhot?start='+start;
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
                },
                'success': function(re) {
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
        // render data
        renderList: function(re, clear) {
            if (clear) {
                jq('.container #hot_thread_container').html('');
            }

            // 最后无数据不再加载
            if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                module.exports.isLoadingNew = false;
                jq('#showAll').show();
                module.exports.bindEvent(re);
                return false;
            }

            var tmpl = template.render('tmpl_hotTopics', re.data);
            jq('.container #hot_thread_container').append(tmpl);
            module.exports.nextStart = re.data.nextStart;
            module.exports.bindEvent(re);
        },
        bindEvent: function(re) {
            // 图片张数初始化
            module.exports.initScrollImage();

            // 打开站点
            jq.DIC.touchState('.categoryCon', 'incoBg', '.warp');
            jq('.categoryCon').on('click', function(e) {
                var sId = jq(this).attr('sId');
                jq.DIC.open('/' + sId);
            });

            // like
            jq.DIC.touchState('.like', 'incoBg', '.warp');
            jq('.like').on('click', function(e) {
                e.stopPropagation();
                var thisObj = jq(this);
                if(thisObj.children('i').attr('class') == 'praise') {
                    return;
                }
                var sId = thisObj.attr('sId');
                var tId = thisObj.attr('tId');
                var opts = {
                    'success': function(result) {
                        if (result.errCode == 0 && result.data) {
                            if (module.exports.htltKey && !jq.DIC.in_array(tId, module.exports.hotTopoicLikeTIds)) {
                                module.exports.hotTopoicLikeTIds.push(tId);
                                if (module.exports.hotTopoicLikeTIds.length > 100) {
                                    module.exports.hotTopoicLikeTIds.shift();
                                }
                                localStorage.setItem(module.exports.htltKey, module.exports.hotTopoicLikeTIds.join(','));
                            }
                            thisObj.children('i').removeClass('noPraise');
                            thisObj.children('i').addClass('praise');
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'CSRFToken':CSRFToken}, opts);
            });

            //reply
            jq.DIC.touchState('.reply', 'incoBg', '.warp');
            jq('.reply').on('click', function(e) {
                e.stopPropagation();
                var thisObj = jq(this);
                var sId = thisObj.attr('sId');
                var tId = thisObj.attr('tId');
                jq.DIC.open('/' + sId + '/t/' + tId + '?action=reply');
                return false;
            });

            //查看全文
            jq.DIC.touchState('.viewBtn', 'incoBg', '.warp');
            jq('.viewBtn').on('click', function(e) {
                e.stopPropagation();
                var thisObj = jq(this);
                var sId = thisObj.attr('sId');
                var tId = thisObj.attr('tId');
                jq.DIC.open('/' + sId + '/t/' + tId);
                return false;
            });

            // 图片横滑
            var x, y, endX, endY, offsetX, offsetY, objLeft, left = 0;
            jq('.container').on('touchstart', '.slideBoxWrapper', function(e) {
                x = endX = e.originalEvent.touches[0].pageX;
                y = endY = e.originalEvent.touches[0].pageY;
                objLeft = left;
            });
            var timer = null;
            jq('.container').on('touchmove', '.slideBoxWrapper', function(e) {
                // document.ontouchmove = function(e){ e.preventDefault();}
                endX = e.originalEvent.touches[0].pageX;
                endY = e.originalEvent.touches[0].pageY;
                offsetX = endX - x;
                offsetY = endY - y;
                // 图片上竖滑不明显时禁用上下滑
                if (Math.abs(offsetY) < Math.abs(offsetX)) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    //document.ontouchmove = function(e){ e.preventDefault();}
                } else {
                    return true;
                    //document.ontouchmove = function(e){ return true;}
                }
                var obj = jq(this);
                left =  objLeft + parseInt(offsetX);
                // 防止左滑过头
                if (left > 0) {
                    left = 0;
                    offsetX = 0;
                    offsetY = 0;
                }
                var min = -1 * (this.scrollWidth - jq(this).parent().width());
                // 防止左滑过头
                if (this.scrollWidth >= jq(this).parent().width() && left < min) {
                    left = min;
                    offsetX = 0;
                    offsetY = 0;
                }
                jq(this).css("left", left);

            });
            jq('.container').on('touchend', '#slidePicBox', function(e) {
                objLeft = left;
                document.ontouchstart = function(e){ return true;}
            });


            // 点击看大图
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });

            initLazyload('.warp img');
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
        }
    };
    // module.exports.init();
});
