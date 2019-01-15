/**
 * @filename topicIndex
 * @description
 * 作者: jinhuiguo(jinhuiguo@tencent.com)
 * 创建时间: 2014-6-15 14:56:03
 * 修改记录:
 *
 **/

define('module/topicIndex', ['lib/scroll', 'module/thread', 'module/followSite', 'module/wxFollow'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var thread = require('module/thread');
    var followSite = require('module/followSite');
    var wxFollow = require('module/wxFollow');
    module.exports = {
        popTId: 0,
        isLoadingNew: true, // 需要请求数据
        isLoading: false, // 正在请求数据
        getThreadList: function(action, nextStart, type) {
            var start = 0;
            if (typeof nextStart == 'undefined') {
                start = window.nextStart;
            }

            module.exports.isLoading = true;
            
            if(type == 'hot'){
                var url = '/' + sId + '?topicTId='+picThreadTId+'&start=' + start + '&order=hot';
            }else{
                var url = '/' + sId + '?topicTId='+picThreadTId+'&start=' + start;
            }
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
                    if(type == 'hot'){
                        setTimeout(function(){
                            module.exports.getThreadList('drag', 0, 'new');
                        }, 100)
                    }
                    var status = parseInt(re.errCode);
                    if (status !== 0) {
                        module.exports.isLoading = false;
                        return false;
                    }

                    re.data.uId = uId || 0;
                    re.data.isFriendSite = isFriendSite || 0;
                    re.data.tlNodeId = 'tl_' + (new Date).getTime();
                    re.data.isWX = isWX;
                    if(type == 'hot'){
                        var allThreadListObj = jq('#hotThreadList');
                        if(!!re.data.threadList[0] || re.data.threadList.length > 0){
                            jq('.hotLabelBox').show();
                        }
                    }else{
                        var allThreadListObj = jq('#newThreadList');
                        if(re.data.threadList.length > 0){
                            jq('.newLabelBox').show();
                        }
                    }
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
                        if(type == 'new' || !!re.data.threadList[0] || re.data.threadList.length > 0){
                            jq('.infobox').hide();
                        }
                        allThreadListObj.append(tmpl);
                        // 最后无数据不再加载
                        if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                            module.exports.isLoadingNew = false;
                            jq('#loadNext').stop(true, true).hide();
                            if(type != 'hot'){
                                jq('#showAll').show();
                            }
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
        showCarousel: function(obj, o) {
            o = jq.extend({
                btnPrev: null,
                btnNext: null,
                btnGo: null,
                mouseWheel: false,
                auto: null,
                speed: 200,
                easing: null,
                vertical: false,
                circular: true,
                visible: 3,
                start: 0,
                scroll: 1,
                beforeStart: null,
                afterEnd: null
            }, o || {});

            return obj.each(function() {

                var running = false, animCss=o.vertical?"top":"left", sizeCss=o.vertical?"height":"width";
                var div = obj, ul = jq(o.childUl, div), tLi = jq(o.childLi, ul), tl = tLi.length, v = o.visible;

                if(o.circular) {
                    ul.prepend(tLi.slice(tl-v-1+1).clone())
                      .append(tLi.slice(0,v).clone());
                    o.start += v;
                }

                var li = jq(o.childLi, ul), itemLength = li.length, curr = o.start;
                div.css("visibility", "visible");

                li.css({width: obj.width(), height: 'auto'});
                li.css({overflow: "hidden", float: o.vertical ? "none" : "left"});
                ul.css({margin: "0", padding: "0", position: "relative", "list-style-type": "none", "z-index": "1"});
                div.css({overflow: "hidden", position: "relative", "z-index": "2", left: "0px"});

                var liSize = o.vertical ? height(li) : width(li);
                var ulSize = liSize * itemLength;
                var divSize = liSize * v;

                ul.css(sizeCss, ulSize+"px").css(animCss, -(curr*liSize));

                div.css(sizeCss, divSize+"px");

                if(o.btnPrev)
                    jq(o.btnPrev).click(function() {
                        return go(curr-o.scroll);
                    });

                if(o.btnNext)
                    jq(document).on('click', o.btnNext, function() {
                        return go(curr+o.scroll);
                    });

                if(o.btnGo)
                    jq.each(o.btnGo, function(i, val) {
                        jq(val).click(function() {
                            return go(o.circular ? o.visible+i : i);
                        });
                    });

                if(o.mouseWheel && div.mousewheel)
                    div.mousewheel(function(e, d) {
                        return d>0 ? go(curr-o.scroll) : go(curr+o.scroll);
                    });

                if(o.auto)
                    setInterval(function() {
                        go(curr+o.scroll);
                    }, o.auto+o.speed);

                function vis() {
                    return li.slice(curr).slice(0,v);
                };

                function go(to) {
                    if(!running) {

                        if(o.beforeStart)
                            o.beforeStart.call(this, vis());

                        if(o.circular) {
                            if(to<=o.start-v-1) {
                                ul.css(animCss, -((itemLength-(v*2))*liSize)+"px");

                                curr = to==o.start-v-1 ? itemLength-(v*2)-1 : itemLength-(v*2)-o.scroll;
                            } else if(to>=itemLength-v+1) {
                                ul.css(animCss, -( (v) * liSize ) + "px" );
                                curr = to==itemLength-v+1 ? v+1 : v+o.scroll;
                            } else curr = to;
                        } else {
                            if(to<0 || to>itemLength-v) return;
                            else curr = to;
                        }

                        running = true;

                        ul.animate(
                            animCss == "left" ? { left: -(curr*liSize) } : { top: -(curr*liSize) } , o.speed, o.easing,
                            function() {
                                if(o.afterEnd)
                                    o.afterEnd.call(this, vis());
                                running = false;
                            }
                        );
                        if(!o.circular) {
                            jq(o.btnPrev + "," + o.btnNext).removeClass("disabled");
                            jq( (curr-o.scroll<0 && o.btnPrev)
                                ||
                               (curr+o.scroll > itemLength-v && o.btnNext)
                                ||
                               []
                             ).addClass("disabled");
                        }

                    }
                    return false;
                };
            });

            function css(el, prop) {
                return parseInt(jq.css(el[0], prop)) || 0;
            };
            function width(el) {
                return  el[0].offsetWidth + css(el, 'marginLeft') + css(el, 'marginRight');
            };
            function height(el) {
                return el[0].offsetHeight + css(el, 'marginTop') + css(el, 'marginBottom');
            };
        },
        init:function() {
           
            module.exports.getThreadList('drag', 0, 'hot');

            // todo 移到navBar中
            // 扫描二维码进来弹出关注提示
            var source = jq.DIC.getQuery('source');
            if (source == 'follow_qrcode' && !isWX && !window.isLiked) {
                followSite.followSite.call({}, 'nothing', {'sId':sId});
            }

            // 直出渲染
            var jsonData = parseJSON(window.jsonData);
            jsonData.showEmptyTip = true;
            jsonData.isWX = isWX;
            var tmpl = template.render('tmpl_thread', jsonData);
            var allThreadListObj = jq('#threadList');
            if (tmpl == '{Template Error}') {
                jq('#threadList').find('.infobox i')
                .removeClass('iconSuccess')
                .addClass('iconPrompt');
                jq('#threadList').find('.infobox p').html('好像出了点问题，请联系管理员');
            } else {
                allThreadListObj.html(tmpl);
            }
            g_ts.first_render_end = new Date();

            // todo 交友社区图片宽度判断影响效率暂不处理

            // 图片张数初始化
            thread.initScrollImage();

            // 图片横滑初始化
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.container'});

            // lazyload
            initLazyload('.warp img');

            // 点击看大图
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');
            });

            //站点首页定制区域轮播
            if (jq('#showCarousel .sCLi')[0]) {
                jq('#showCarousel .sCLi').show();
                module.exports.showCarousel(jq('#showCarousel'), {
                    afterEnd: function(e) {
                        var isInitlazyload = jq(e).data('initlzl') || false;
                        if (!isInitlazyload) {
                            jq(e).data('initlzl', true);
                            initLazyload('#showCarousel img', {checkDuplicates: false});
                        }
                    },
                    btnNext: '.sCNext',
                    childUl: '.sCUl',
                    childLi: '.sCLi',
                    circular: true,
                    scroll: 1,
                    visible: 1
                });

                // 定制区域事件点击
                jq('#showCarousel').on('click', '.customImg, .customNotice li, .topicSelection li, .cuTopicImg a', function() {
                    var link = jq(this).attr('data-link') || '';
                    if (link) {
                        jq.DIC.open(link);
                    }
                    return false;
                });
            }

            var query = '';
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&');
            }
            // 灵敏度兼容
            var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
            if (/MQQBrowser/.test(window.navigator.userAgent)) {
                level = -10;
            }

            var loadingObj = jq('#loadNext');
            var loadingPos = jq('#loadNextPos');

            var x, y , endX, endY, offsetY, loadingAction;
            jq('.warp').on('touchstart', function(e) {
                x = endX = e.originalEvent.touches[0].pageX;
                y = endY = e.originalEvent.touches[0].pageY;
            }).on('touchmove', function(e) {
                endX = e.originalEvent.touches[0].pageX;
                endY = e.originalEvent.touches[0].pageY;
                offsetY = endY - y;
                /*
                // 向上滑
                if (offsetY < -10 && !module.exports.isLoading && module.exports.isLoadingNew) {
                    var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                    if (loadingObjTop <= 10) {
                        module.exports.isLoading = true;
                        jq('#loadNext').stop(true, true).slideDown('fast');
                        module.exports.getThreadList('drag');
                    }
                }
                */

                // 向下拉刷新
                if (offsetY > 10 && !module.exports.isLoading && document.body.scrollTop <= 1) {
                    module.exports.isLoading = true;
                    jq('#refreshWait').stop(true, true).show();
                    module.exports.getThreadList('pull', 0, 'hot');
                }
            });

            var scrollPosition = jq(window).scrollTop();
            jq(window).scroll(function() {
                if (scrollPosition < jq(window).scrollTop()) {
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

            jq('#joinpic').on('click', function() {
                var url = jq(this).attr('href');
                
                if(jq.DIC.getQuery('filterType')){
                    var filerType = jq.DIC.getQuery('filterType');
                    url = url + '?filterType=' + filerType;
                }

                jq.DIC.reload(url);
                
                return false;
            });

            // 帖子页卡点击
            jq('.warp').on('click', '.topicBox', function(e) {
                var that = jq(this);

                var tId = that.attr('tId') || 0;
                var parentId = that.attr('parentId') || 0;
                var link = that.attr('data-link') || '';
                if (tId) {
                    jq.DIC.open('/' + sId + '/v/'+parentId+'/t/' + tId);
                } else if (link) {
                    jq.DIC.open(link);
                }
                return false;
            }).on('click', '.avatar', function(e) {
            // 头像点击
                e.stopPropagation(e);
                var uId = jq(this).attr('uId') || 0;
                if (uId) {
                    jq.DIC.open('/profile/' + uId);
                }
                return false;
            }).on('click', '.topBtn, .showBtn', function(e) {
            // 标签点击
                e.stopPropagation(e);
                var link = jq(this).attr('data-link') || '';
                if (link) {
                    jq.DIC.open(link);
                }
                return false;
            }).on('click', '.videoArea', function(e) {
            // 视频点击
                e.stopPropagation(e);
                var that = jq(this);

                var tId = that.attr('tId') || 0;
                var parentId = that.attr('parentId') || 0;
                if (tId) {
                    jq.DIC.open('/' + sId + '/v/'+ parentId +'/t/' + tId + '?video=1');
                }
                return false;
            }).on('click', '.operation', function(e) {
            // 操作区域阻止防误点
                e.stopPropagation(e);
                return false;
            }).on('click', '.sourceApp a', function(e) {
            // 应用尾巴点击
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var appId = that.attr('appId') || 0;
                if (appId) {
                    jq.DIC.open('http://m.wsq.qq.com/app?sId=' + sId + '&appId=' + appId);
                }
                return false;
            }).on('click', '.like', function(e) {
            // 点赞
                e.stopPropagation(e);
                var that = jq(this);
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

                var likeObj = that.find('i');
                // 已赞
                if (likeObj.hasClass('iconPraise')) {
                    return false;
                }
                var tId = that.parent().attr('tId');
                var parentId = that.parent().attr('parentId');
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

                        if (isWX && isWeixinLink && jq.DIC.getQuery('source')) {
                            wxFollow.wxFollowTips();
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'parentId':parentId, 'CSRFToken':CSRFToken}, opts);
                return false;
            }).on('click', '.threadReply', function(e) {
            // 主题的回复按纽
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var tId = that.parent().attr('tId');
                var parentId = that.parent().attr('parentId');
                var nodeId = 't_' + tId + '_0_0';
                thread.reply(sId, tId, parentId, 0, 0, '', false, nodeId, true);
                return false;
        
            }).on('click', '.moreReply', function() {
            // 直接进入详情页
                try {
                    pgvSendClick({hottag:'QUAN.SITE.LIST.ALL'});
                } catch(e) {}
            }).on('click', '.attendPic', function(e) {
            // 参加晒图
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var parentId = that.attr('parentId') || 0;
                jq.DIC.reload('/' + sId + '/t/new?parentId=' + parentId);
                return false;
            }).on('click','.evtShowAllPic',function(e){
                //晒图点击查看全部
                e.stopPropagation(e);
                var that = jq(this),
                    showAll = that.attr('showAll') ? 1 : 0,
                    tId = that.attr('tId') || 0;
                if (tId) {
                    jq.DIC.open('/' + sId + '/v/' + tId + '?showAll='+showAll);
                }
                return false;
            });

            //活动规划查看全文
            jq('.detailShow').on('click', '.evtShowAll', function(){
                var thisObj = jq(this);
                var thisParent = thisObj.parent();
                var evtContent = thisParent.find('.evtContent');
                var dataSummary = thisParent.find('.showMoreSummary').html();
                var dataDesc = thisParent.find('.showMoreContent').html();
                if(thisObj.text() == '查看全文'){
                    evtContent.html(dataDesc);
                    thisObj.text('收起');
                }else{
                    evtContent.html(dataSummary);
                    thisObj.text('查看全文');
                }
                return false;
            });
            
            // 全局活动
            thread.publicEvent();
            // 管理操作
            thread.initPopBtn();

            
        }
    };
    module.exports.init();
});
