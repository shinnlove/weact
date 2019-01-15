/**
 * @file search.js
 * 搜索模块
 *
 * @author vissong, vissong@tencent.com
 * @version
 * @date 2014-03-13
 *
 * $Id$
 */

define('module/search', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        searchUrl: 'http://api.wsq.qq.com/search?source=' + pageName,
        showMask: function() {
            jq('#cancleText').show();
            jq('.s_input_con').addClass('on');
            jq('.searchCon').css('z-index', 10011);
            jq.DIC.dialog({'content': ' ', isMask: true, isHtml: true});
        },
        hideMask: function() {
            jq('.s_input_con').removeClass('on');
            jq('#cancleText').hide();
            jq('#clearBtn').hide();
            jq('.searchCon').css('z-index', 0);
            jq.DIC.dialog({'content': ''});
        },
        listId: '',
        init: function() {
            module.exports.listId = window.siteListId || 'categoryList';
            var top = parseInt(jq('.warp').css('margin-top'));
            if (top < 0) {
                module.exports.initTouch();
            }

            // 尚未出结果是，mark 切换
            jq('#searchInput').on('focus', function() {
                jq('.bottomBar').hide();
                jq('#searchInput').val('');
                if (jq('.searchResult').css('display') == 'none') {
                    module.exports.showMask();
                }
                module.exports.startMonitor();
            }).on('blur', function() {
                jq('.bottomBar').show();
                if (jq('.searchResult').css('display') == 'none') {
                    module.exports.hideMask();
                }
                module.exports.cancleMonitor();
            });

            jq('#cancleText').on('click', function() {
                setTimeout(function() {
                    jq('.s_input_con').removeClass('on');
                    jq('#cancleText').hide();
                    jq('#clearBtn').hide();
                    jq('.searchCon').css('z-index', 0);
                    jq.DIC.dialog({'content': ''});
                    module.exports.cancle();
                }, 10)
            });
            jq('#clearBtn').on('click', function(e) {
                setTimeout(function() {
                    jq('#searchInput').val('');
                    jq('#clearBtn').hide();
                    jq('#' + module.exports.listId).show();
                    jq('.searchResult').hide();
                    module.exports.showMask();
                }, 10)
            });
        },
        showInput: function() {
            var top = parseInt(jq('.warp').css('margin-top'));
            var t;
            t = setInterval(function() {
                if (top < 0) {
                    top = Math.min(top + 3, 0);
                    jq('.warp').css('margin-top', top + 'px');
                } else {
                    window.clearInterval(t);
                }
            }, 1);
        },
        hideInput: function() {
            var top = parseInt(jq('.warp').css('margin-top'));
            var t;
            t = setInterval(function() {
                if (top <= -55) {
                    window.clearInterval(t);
                } else {
                    top = Math.min(top - 2, 0);
                    jq('.warp').css('margin-top', top + 'px');
                }
            }, 1);
        },
        initTouch: function() {
            var startY, offsetY, top, touchEnd;
            top = parseInt(jq('.warp').css('margin-top'));

            jq(document).on('touchend', function(e) {
                if (offsetY > 0 && top < 0 && document.body.scrollTop <= 0) {
                    module.exports.showInput();
                }
            });

            jq(document).on('touchstart', function(e) {
                // console.log(e.originalEvent.touches[0]);
                if (top < 0 && document.body.scrollTop <= 0) {
                    startY = e.originalEvent.touches[0].pageY;
                }
            });
            jq(document).on('touchmove', function(e) {
                if (top < 0 && document.body.scrollTop <= 0) {
                    offsetY = e.originalEvent.touches[0].pageY - startY;
                    // console.log(offsetY);
                    if (offsetY > 0) {
                        top = Math.min(top + offsetY, 0);
                        jq('.warp').css('margin-top', top + 'px');
                    }
                }
            });
        },
        startMonitor: function() {
            var input;
            jq.DIC.timerId = setInterval(function() {
                if (input != jq('#searchInput').val()) {
                    input = jq('#searchInput').val();
                    if (input) {
                        jq('#clearBtn').show();
                        jq('#' + module.exports.listId).hide();
                        jq('.searchResult').show();
                        jq.DIC.dialog({content: ''});
                        module.exports.search(input);
                    }
                }
            }, 50);
        },
        cancleMonitor: function() {
            window.clearInterval(jq.DIC.timerId);
        },
        cancle: function() {
            jq('#searchInput').val('');
            jq('#' + module.exports.listId).show();
            jq('.searchResult').hide();
        },
        search: function(word) {
            word = word.trim();
            if (!word) {
                return false;
            }
            jq.DIC.ajax(module.exports.searchUrl + '&word=' + word + '&t=' + Math.random(), null, {
                noShowLoading: true,
                success: function(ret) {
                    if (ret.errCode != 0) {
                        var html = template.render('searchList', {list: []});
                    } else {
                        for(var i=0;i<ret.data.siteList.length;i++) {
                            ret.data.siteList[i].name = htmlEncode(ret.data.siteList[i].name);
                            ret.data.siteList[i].name = ret.data.siteList[i].name.replace(word, '<span class="c0">' +word+'</span>');
                        }
                        var html = template.render('searchList', {list: ret.data.siteList});
                    }
                    jq('.searchResult').html(html);
                    jq('.searchResult li').on('click', function() {
                        var link = jq(this).attr('_link');
                        if (link) {
                            jq.DIC.reload(link);
                        }
                    });
                },
                error: function(ret) {
                    var html = template.render('searchList', {list: []});
                    jq('.searchResult').html(html);
                }
            });
        }
    };
    module.exports.init();
});
