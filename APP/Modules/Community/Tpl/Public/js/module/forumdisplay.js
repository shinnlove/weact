/**
 * @filename forumdisplay
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: forumdisplay.js 32816 2014-09-11 09:47:26Z babuwang $
 **/

define('module/forumdisplay', ['lib/scroll', 'module/thread', 'module/followSite', 'module/wxFollow'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');			// 帖子图片滑动js
    var thread = require('module/thread');			// 帖子倒三角管理、回复弹层、社区圈的全局事件等处理js
    var followSite = require('module/followSite');	// 关注本社区js
    var wxFollow = require('module/wxFollow');		// 微信弹窗关注
    var stat = require('lib/stat');
    module.exports = {
        popTId: 0,									// 本js模块全局变量：本模块当前要操作的帖子（在perPopBtn）点击后选中的帖子编号（这个模块有用到这个变量吗?!不是在thread里用的吗?）
        isLoadingNew: true, 						// 本js模块全局变量：本模块需要请求数据标记
        isLoading: false, 							// 本js模块全局变量：本模块正在请求数据标记
        
        /**
         * 微社区主页面（论坛版块）瀑布流分页请求数据的方式：global.js里的ajax方法，
         * 当线程请求数据，此处action参数有pull和drag两个操作，
         * 会出发getThreadList函数。
         * @params action 用户刷新数据的方式：pull操作或drag操作
         * @params nextStart 下一页第一条数据的编号（每页10条数据是固定的）
         */
        getThreadList: function(action, nextStart) {
            var start = 0;							// 定义起始页为0
            if (typeof nextStart == 'undefined') {
                start = window.nextStart;			// 如果没有定义nextStart下一页数据，就用window.nextStart这个值
            }
            var query = '';							// 定义查询参数query，目前还不知道干嘛用???是否用来话题筛选，很有可能???
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&').replace(/&?start=[^d]+/g, '');
            }
            
            module.exports.isLoading = true;		// 模块设置为正在请求数据
            
            /**
             * 开始定义分页请求论坛数据的url地址：
             * 现在必须传的参数：1、e_id；2、sId；3、start。
             * 选传参数：4、query（可能为空，用户感兴趣的话题）。
             */
            var url = DOMAIN + 'weact/Community/MicroCommunity/getTopicByPage?e_id=' + e_id + '&sId=' + sId + '&start=' + start + query;						//请求数据地址
            if (isLive) {
            	// 目前暂时不知道isLive是干嘛用的
                var url = DOMAIN + 'weact/Community/MicroCommunity/getTopicByPage?e_id=' + e_id + '&sId=' + sId + '&start=' + start + '&live=1' + query;		//活跃用户请求数据地址
            }
            
            // 开始定义本次操作方法options，然后交给ajax或ajaxForm执行
            var opts = {
            	// 发送前执行：
                'beforeSend': function() {
                    switch(action) {
                        case 'pull':									// 如果是下拉刷新操作
                            jq('#showAll').hide();						// 默认已显示全部的div隐藏
                            module.exports.isLoadingNew = true;			// 模块开始请求新数据的标记置为true
                            break;
                        case 'drag':									// 如果是上推操作
                            module.exports.isLoadingNew = true;			// 模块开始请求新数据的标记置为true
                            break;
                        default:
                            jq.DIC.showLoading();						// 默认是正在加载中
                    }
                    module.exports.isLoadingNew = true;					// 模块开始请求新数据的标记置为true（上边代码是否啰嗦了?）
                },
                // 完成后执行：
                'complete': function() {
                    jq('#waitForLoad').hide();							// 等待加载div隐藏
                    jq('#refreshWait').slideUp();						// 顶部正在加载div下滑
                    jq('#loadNext').slideUp();							// 底部正在加载div也下滑
                },
                // 被响应后执行：
                'success': function(re) {
                    var status = parseInt(re.errCode);					// 将服务器返回错误码转整
                    if (status !== 0) {
                        module.exports.isLoading = false;				// 模块不在请求数据中
                        return false;									// errCode不为0，有错误码，表示请求数据失败
                    }
                    
                    // 以下4个变量isLive、uId、isFriendSite、isWX、分页请求时服务器端不做返回，直接使用页面上的，默认值都是0，还有一个变量tlNodeId担任div的id功能，它根据时间来定义
                    re.data.isLive = isLive || 0;						// 用户是否活跃???
                    re.data.uId = uId || 0;								// 用户的编号
                    re.data.isFriendSite = isFriendSite || 0;			// 目前还不知道isFriendSite是干嘛用的参数???
                    re.data.tlNodeId = 'tl_' + (new Date).getTime();	// 取当前时间，拼接tl_值。这个是用来渲染从第二页开始，每页10条数据存放在一个div id="tl_"+tiNodeId里，另：getTime() 方法可返回距 1970 年 1 月 1 日之间的毫秒数。
                    re.data.isWX = isWX;								// 是否微信用户

                    var allThreadListObj = jq('#threadList');			// 抓取页面中class为container，id为threadList的div，命名为allThreadListObj
                    var zero = new Date;								// 取当前的时间给到zero变量
                    if (action == 'pull') {
                    	// 如果加载数据的动作是下拉，则有新微博可能已经更新，需要清空原来的
                        // 先把内容清空，否则主题已经存在就不渲染模板
                        allThreadListObj.html('');
                        var tmpl = template.render('tmpl_thread', re.data);			// 使用re的data数据渲染art模板引擎得到渲染后的html结构
                        allThreadListObj.html(tmpl);								// 写入新html数据
                    } else {
                        var tmpl = template.render('tmpl_thread', re.data);			// 使用新数据渲染art模板引擎
                        if (tmpl == '{Template Error}') {
                            tmpl = '';												// 如果渲染失败，则html置空
                        }
                        jq('.infobox').hide();										// 隐藏正在努力加载div
                        allThreadListObj.append(tmpl);								// 在文档尾追加新html数据
                        // 如果返回的json数据的data字段的threadList字段里无数据，就不再加载任何数据
                        if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                            module.exports.isLoadingNew = false;					// 模块不在加载数据中
                            jq('#loadNext').stop(true, true).hide();				// 正在加载停止，并隐藏（关于stop(true, true)的解释请见项目收藏夹）
                            jq('#showAll').show();									// 出现已经显示全部数据的div
                        }
                    }
                    stat.reportPoint('listRender', 10, new Date, zero);				// ???js模块stat的reportPoint函数，zero貌似在这里就是个new Date的功能
                    window.nextStart = nextStart = re.data.nextStart;				// 将下一页数据给到nextStart，同时给到window.nextStart
                    
                    // 更新站点用户排行榜（自己加的），貌似目前腾讯对于这一块又有改版，可以横向滚动叫做旋转木马
                    //alert( jq('.customImg').html() );
                    //alert( re.data.siteRankListTopThree );
                    var newrank = '';
                	for(var key in re.data.siteRankListTopThree){
                		newrank += '<li>'
                			+ '<i class="brBig cuImg db"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==" data-original="'+re.data.siteRankListTopThree[key].avatar+'" width="40" height="40"></i>'
                			+ '<span class="cuText f11 c4 br db">'+re.data.siteRankListTopThree[key].praise+'赞</span>'
                			+	'</li>'
                    }
                	jq('.customImg').html('');										// 清空排行榜内容，.customImg是ul无序列表里的内容
                    jq('.customImg').html(newrank);									// 重新写入排行榜
                    
                    // 处理新消息数
                    if (re.data.newMsgCount > 0) {
                        newMsgCount = re.data.newMsgCount;							// 如果新消息数目大于0，则获取新消息数目
                        if (re.data.newMsgCount > 99) {
                            jq('#navMsgNum').html('').addClass('redP');				// 默认最多显示99条消息，如果超过了99条，直接不显示数目
                        } else {
                            jq('#navMsgNum').removeClass('redP').html(re.data.newMsgCount);		//没超过99条就显示numP，并写入消息数目
                        }

                        // 上一次新消息时间
                        var date = new Date();
                        localStorage.setItem('lastNewTime', date.getTime());		// 本地记录这次消息时间

                        jq('#navMsgNum').show();									// 显示消息数目提示
                    } else {
                        jq('#newMsgCount').html(0);									// 新信息数目为0
                        jq('#navMsgNum').hide();									// 隐藏消息数目提示
                    }

                    // 更新帖子数
                    if (re.data.threadCount >= 0) {
                        jq('#threadCount').html(re.data.threadCount);				// 页面上threadCount的div里的值改变
                    }

                    // 更新站点访问数
                    if (re.data.sitePV >= 1) {
                        jq('#sitePV').html(re.data.sitePV);							// 站点访问数（刷新一次数据就算一次）
                    }
                    
                    // 图片张数初始化
                    thread.initScrollImage('#' + re.data.tlNodeId);					// 调用thread.js里的initScrollImage方法对新的10条数据的图片进行初始化
                    
                    // 至此，一次pull或者drag操作所有DOM元素与结构、特效变更结束
                    module.exports.isLoading = false;								// 本模块正在loading数据的状态变为false
                    jq('#refreshWait').hide();										// 隐藏刷新等待提示框
                },
                // 请求url错误或未响应时执行：
                error: function() {
                    module.exports.isLoading = false;								// 本模块正在loading数据的状态变为false
                }
            };
            // 所有事情ajax的操作opts都定义完了，才开始调用global.js中定义的DIC的ajax方法静态请求数据
            jq.DIC.ajax(url, '', opts);
        },
        // 展示旋转木马函数showCarousel
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
                            jq( (curr-o.scroll<0 && o.btnPrev) || (curr+o.scroll > itemLength-v && o.btnNext) || [] ).addClass("disabled");
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
        // 论坛模块初始化函数
        init:function() {
            if (window.isBindQQ) {
                jQuery.DIC.dialog({content:'绑定成功', autoClose:true});						// 如果已经绑定过QQ，显示绑定QQ成功
            }
            
            // todo 移到navBar中
            
            // 扫描二维码进来弹出关注提示
            var source = jq.DIC.getQuery('source');											// 扫码进来的时候，会规定参数source，值为follow_qrcode
            if (source == 'follow_qrcode' && !isWX && !window.isLiked) {
                // 条件1：source参数为follow_qrcode；条件2：必须是微信浏览器；条件3：必须还没有喜欢过该站点（喜欢过就不用关注了）
            	followSite.followSite.call({}, 'nothing', {'sId':sId});						// 3种条件缺少一个，就不弹出关注提示
            }

            // 直出渲染art模板引擎，第一次的加载tlNode是没有id值的，第二页开始，每次ajax请求后，都会在tlNode上加上请求的时间
            // 特别注意，第一次直出渲染，是不从服务器端加包的，所以jsonData从threadList开始
            // 特别注意，jquery的parseJSON是将字符串类型转成javascript的对象类型，是object，所以两者根本不相同，第一次加载数据的时候就没考虑周全这个事情绕了点弯
            var jsonData = parseJSON(window.jsonData);										// 解析页面的json数据（不管是带了反斜杠，还是没有反斜杠，都可以解析json数据）
            jsonData.showEmptyTip = true;													// showEmptyTip是用来干嘛的变量???
            jsonData.isWX = isWX;															// 页面上的json数据里不包含是否微信浏览器判断，直接使用页面上的静态量
            var tmpl = template.render('tmpl_thread', jsonData);							// 利用json数据渲染art模板引擎，返回渲染后的htmlDOM结构与数据
            var allThreadListObj = jq('#threadList');										// 抓取id为threadList的div，定义为allThreadListObj对象
            if (tmpl == '{Template Error}') {
                jq('#threadList').find('.infobox i').removeClass('iconSuccess').addClass('iconPrompt');		// 如果渲染模板出错，更换样式
                jq('#threadList').find('.infobox p').html('好像出了点问题，请联系管理员');							// 显示出错信息
            } else {
                allThreadListObj.html(tmpl);																// 渲染成功就把模板渲染的信息写入
            }
            g_ts.first_render_end = new Date();																// 记录第一次渲染模板时间???这又是干嘛的

            // todo 交友社区图片宽度判断影响效率暂不处理

            // 图片张数初始化
            thread.initScrollImage();		// 反正第一次也就一个tlNode的div，直接初始化

            // 图片横滑初始化：libScroll.js横滑控件
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.container'});

            // lazyload images
            initLazyload('.warp img');

            // 点击看大图（图片预览功能）
            // sea.js的API：require.async(模块id, callback) 方法用来在模块内部异步加载模块，并在加载完成后执行指定回调。callback 参数可选。
            require.async('module/imageviewCommon', function(imageviewCommon) {
                imageviewCommon.init('.slideBox li');			// 异步加载imageviewCommon.js模块，并执行回调函数去初始化.clideBox li里面的预览图片
            });

            // 站点首页定制区域轮播，改为新版本的内容
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
                	// 如果点击到.customImg, .customNotice li, .topicSelection li, .cuTopicImg a这些对象
                	var link = jq(this).attr('data-link') || '';
                    if (link) {
                        jq.DIC.open(link);			// 尝试打开showCarousel的data-link属性里的网址url
                    }
                    return false;
                });
            }
            
            // ???此处是话题喜欢分类???
            var query = '';
            if (window.location.search.indexOf('?') !== -1) {
                query = window.location.search.replace(/\?/g, '&');
            }
            
            // 灵敏度兼容
            var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
            if (/MQQBrowser/.test(window.navigator.userAgent)) {
                level = -10;
            }
            
            var loadingObj = jq('#loadNext');				// 底部正在加载
            var loadingPos = jq('#loadNextPos');			// 紧挨着上边div的div对象
            
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
                    module.exports.getThreadList('pull', 0);			// 由本模块内的init方法来调用getThreadList方法下拉刷新
                }
            });

            var scrollPosition = jq(window).scrollTop();
            jq(window).scroll(function() {
                if (scrollPosition < jq(window).scrollTop()) {
                    if (!module.exports.isLoading && module.exports.isLoadingNew) {
                        var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                        if (loadingObjTop <= 100) {
                            module.exports.isLoading = true;
                            jq('#loadNext').stop(true, true).slideDown('fast');
                            module.exports.getThreadList('drag');
                        }
                    }
                }
                scrollPosition = jq(window).scrollTop();
            });

            /*
            // 全屏触摸上下滑
            jq.DIC.initTouch({
                obj: jq('.warp')[0],
                end: function(e, offset) {
                    document.ontouchmove = function(e){ return true;}
                    var loadingObj = jq('#loadNext');
                    var loadingPos = jq('#loadNextPos');
                    // var loadingObjTop = loadingObj.offset().top + loadingObj.height() - jq(window).scrollTop();
                    var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                    // 向上滑
                    if (offset.y > 10 && loadingObjTop <= 10 && module.exports.isLoadingNew && !module.exports.isLoading) {
                        module.exports.getThreadList1('drag');
                    }
                    // 向下拉刷新
                    if (offset.y < level && document.body.scrollTop <= 0) {
                        module.exports.getThreadList1('pull', 0);
                    }
                }
            });
            */

            // 帖子页卡点击事件???开始定义每一条帖子点击的事件，为啥要叫这个帖子是“页卡”???会不会误认为很卡???
            /**
             * 这里加一段注释：为了更好地理解这些事件到底是做什么的，解释下DOM结构，约定缩进代表子级html标签。
             * 
             * 首先，tlNode这个是一次分页的话题数据div，内部固定话题数为10条；
             * 其次，对于每一条话题数据，是在class为topicBox这个div里的，topicBox这个div附带属性：id、帖子编号tid、还可能有data-link等属性；
             * 再次，话题内容content存放在topicCon这个div内部，它被分为两部分：personalImgDate和detailCon，上边存放作者信息等，下边存放内容与回复等；
             * 然后，将personalImgDate部分称为partTop，将detailCon这部分称为partBottom，
             * 如此，则partTop的用户头像可以点击、右上角帖子操作小三角可以点击；partBottom的图片内容和用户回复的头像（如果有回复）可以点击；
             * 对于partTop，最重要的倒三角div叫做PerPopBtn；对于partBottom，最重要的点赞、转发、评论的div叫做operation。
             * 最后，列出DOM结构（id=tlNode_时间）这个div里内容：
             * level 1:	topicBox
             * level 2:		topicCon
             * level 3:			personImgData
             * level 4:				头像+名字
             * level 4:				时间+地理位置
             * level 4:				PerPopBtn	帖子管理倒三角（对帖子的操作功能由thread.js接管）
             * level 3:			detailCon
             * level 4:				dCon	帖子内容
             * level 4:				slideBox	图片预览框（如果发图）
             * level 4:				operation	点击操作点赞、转发、评论
             * 
             * 特别提醒：如果按DOM结构从外往里定义click事件，则可以使用e.stopPropagation(e)来停止内层事件的DOM冒泡传播，事件结构也会非常清晰。
             * */
            jq('.warp').on('click', '.topicBox', function(e) {
                // 帖子被点击
            	var that = jq(this);									// 抓取点击对象
                return;
                var tId = that.attr('tId') || 0;						// 获得点击的帖子编号
                var link = that.attr('data-link') || '';				// 获得其data-link的url属性
                if (tId) {
                    jq.DIC.open('/' + sId + '/t/' + tId);				// 跳转进入帖子页详情（修改跳转地址，并附带参数）
                } else if (link) {
                    jq.DIC.open(link);									// 否则就直接进入预设定的url
                }
                return false;
            }).on('click', '.avatar', function(e) {
            	// 头像点击
                e.stopPropagation(e);									// 停止事件DOM冒泡
                var uId = jq(this).attr('uId') || 0;					// 获取点击头像的用户编号
                if (uId) {
                    jq.DIC.open('/profile/' + uId);						// 如果用户编号不空，直接跳转到这个用户页面（展示一些她关注的社区、发过的话题等）
                }
                return false;
            }).on('click', '.topBtn, .showBtn', function(e) {
            	// 置顶、晒图之类的按钮标签点击
                e.stopPropagation(e);
                var link = jq(this).attr('data-link') || '';			// 获取链接
                if (link) {
                    jq.DIC.open(link);									// 打开链接
                }
                return false;
            }).on('click', '.videoArea', function(e) {
            	// 视频区域点击
                e.stopPropagation(e);
                var that = jq(this);

                var tId = that.attr('tId') || 0;
                if (tId) {
                    jq.DIC.open('/' + sId + '/t/' + tId + '?video=1');	// 跳转到视频页面
                }
                return false;
            }).on('click', '.operation', function(e) {
            	// 操作区域阻止防误点（想表达的意思是：想点赞的，不小心没点中，结果跳转到帖子详情页面了）
                e.stopPropagation(e);
                return false;
            }).on('click', '.sourceApp a', function(e) {
            	// 应用尾巴点击???貌似用不着
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var appId = that.attr('appId') || 0;
                if (appId) {
                    jq.DIC.open('http://www.we-act.cn/app?sId=' + sId + '&appId=' + appId);
                }
                return false;
            }).on('click', '.like', function(e) {
            	// 点赞事件
                e.stopPropagation(e);							// 停止事件DOM冒泡
                
                var that = jq(this);							// 抓取点赞的对象
                jq.DIC.touchStateNow(that);						// 添加0.1秒动态点击效果
                
                // 未登录且是应用吧页???需要吗???
                var reapp = /qqdownloader\/([^\s]+)/i;
                if (authUrl && reapp.test(navigator.userAgent)) {
                    return false;
                }

                // 未登录???需要吗???
                if (authUrl) {
                    jq.DIC.reload(authUrl);
                    return false;
                }
                
                var likeObj = that.find('i');						// 找到点赞的对象i标签
                // 已赞返回不做处理（已经赞过的地方都会有iconPraise，没有赞过的地方都是iconNoPraise。）
                if (likeObj.hasClass('iconPraise')) {
                    return false;
                }
                var tId = that.parent().attr('tId');				// 抓取点赞帖子编号
                
                var likeTopic = DOMAIN + 'weact/Community/MicroCommunity/likeTopic'; // 如果这里不传参，就必须在jq.DIC.ajax的第二个参数里传参
                var likeParams = {
                	'sId' : sId, 
                	'e_id' : e_id, 
                	'tId' : tId, 
                	'CSRFToken' : CSRFToken
                } // 定义点赞需要请求的参数（这些参数除了点赞的帖子编号tId，其余3个都来自页面上的全局量，其中sId和e_id是必须传的参数，加上安全机制后CSRFToken也是必须传的参数）
                
                // 使用ajax提交点赞，开始定义option操作方法
                var opts = {
                	// ajax请求得到响应后：
                    'success': function(re) {
                        var status = parseInt(re.errCode);											// 检查请求错误编码
                        if (status !== 0 || !re.data || !re.data.likeNumber) {
                        	// 条件1：服务器返回errCode是0（点赞成功）；条件2：服务器的data数据不空且有likeNumber字段标记被赞了几次（需要服务器返回总得被赞次数）
                            return false;															// 点赞失败并停止操作
                        }

                        likeObj.removeClass('iconNoPraise').addClass('iconPraise');					// （对当前用户）移除没有赞的class，加上有赞的class
                        that.find('.likeNum').html(re.data.likeNumber);								// 在赞的数目上直接加上服务器端返回的总的赞数
                        
                        // ???检测是否友好站点???是友好站点的话???可以先false不执行
                        if (window.isFriendSite) {
                            jq('#t_' + tId + '_0_0').find('.blur').removeClass();
                            jq('#t_' + tId + '_0_0').find('.slideText').css('display', 'none');
                        }
                        // ???检测是否弹出微信关注提示???可以先false不执行
                        if (isWX && isWeixinLink && jq.DIC.getQuery('source')) {
                            wxFollow.wxFollowTips();
                        }
                    },
                    'noShowLoading' : true,			// 点赞不用显示等待框
                    'noMsg' : true					// 没有消息
                }
                
                jq.DIC.ajax(likeTopic, likeParams, opts);		// 使用global.js里的DIC自定义的ajax请求数据
                return false;
            }).on('click', '.threadReply', function(e) {
            	// 主题的回复按钮点击事件
                e.stopPropagation(e);
                var that = jq(this);													// 获取当前点击回复的对象
                jq.DIC.touchStateNow(that);
                
                var tId = that.parent().attr('tId');									// 获取点击对象父级：回复的帖子编号
                var nodeId = 't_' + tId + '_0_0';										// 拼接得到当前帖子的div的id值，作为nodeId（点击节点）
                thread.reply(sId, tId, 0, 0, 0, '', false, nodeId, true);				// 调用thread.js里的弹层进行回复
                return false;
            /*
            }).on('click', '.topicList ul li', function(e) {
            // 点击三条回复
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                // 获取帖子id
                var divId = this.id, pId, tId;
                if (/p_\d+_\d+_\d+/.test(divId)) {
                    if (match = divId.match(/p_(\d+)_(\d+)_(\d+)/)) {
                        tId = match[1];
                        pId = match[2];
                        floorPId = match[3];
                    }
                }
                var author = that.attr('author');
                var authorUId = that.attr('uId');
                // 当前用户自已的帖子
                if (authorUId == uId) {
                    // todo 楼中楼
                    thread._delReply(tId, pId, floorPId);
                    return false;
                }

                var nodeId = 't_' + tId + '_0_0';
                // 非管理员点击其他人回复
                if (!isManager) {
                    thread.reply(sId, tId, 0, pId, floorPId, author, false, nodeId);
                    return false;
                }

                // 管理员点击回复
                thread.showManagerPanel(tId, 0, pId, floorPId, authorUId, author, false, true, nodeId);
                return false;
            */
            }).on('click', '.moreReply', function() {
            	// 点击展开更多回复，就直接进入详情页（和直接点击帖子本身一样效果）
                try {
                    pgvSendClick({hottag:'QUAN.SITE.LIST.ALL'});
                } catch(e) {}
            }).on('click', '.attendPic', function(e) {
            	// 参加晒图???留意其他操作
                e.stopPropagation(e);
                var that = jq(this);
                jq.DIC.touchStateNow(that);

                var parentId = that.attr('parentId') || 0;
                jq.DIC.reload('/' + sId + '/t/new?parentId=' + parentId);			// 注意修改地址
                return false;
            }).on('click','.evtShowAllPic',function(e){
                // 晒图点击查看全部???留意其他操作
                e.stopPropagation(e);
                var that = jq(this),
                    showAll = that.attr('showAll') ? 1 : 0,
                    tId = that.attr('tId') || 0;
                if (tId) {
                    jq.DIC.open('/' + sId + '/v/' + tId + '?showAll='+showAll);		// 注意修改地址
                }
                return false;
            }).on('click','header .evtSiteRank',function(){
            	return;//目前暂时return
            	// 点击微社区顶部站点排名，打开站点排名统计（微动平台下所有社区rank）
                var link = jq(this).attr('data-link') || '';						// 注意data-link的设定和跳转
                if (link) {
                    jq.DIC.open(link);
                }
            }).on('click','header .logo', function() {
            	return;//目前暂时return
            	// 点击微社区顶部企业标签logo，打开网址
                if (sId) {
                    jq.DIC.open('/' + sId);											// 注意跳转地址
                }
                return false;
            });
            
            // 初始化全局活动（一些公共事件，不依赖于特定社区的，比如世界杯来了，每个社区都能进行一些全局的活动，想的非常周到啊，这些功能抛给其他模块去做）
            thread.publicEvent();
            
            // 帖子倒三角管理操作（在本js模块内不进行帖子管理操作，只负责帖子信息内容的显示与跳转，叫管理帖子功能抛给thread.js模块，符合简单极致的js开发规则）
            thread.initPopBtn();		// 调用thread.js里的initPopBtn方法为PerPopBtn倒三角生成点击事件
            
            // todo
            
            // 发送QQ邀请点击事件
            jq('.inviteQQ').on('click', function() {
                if (inviteUrl.length > 1) {
                    inviteUrl = inviteUrl.replace(/&amp;/g, '&');
                    jq.DIC.reload(inviteUrl);
                }
                return false;
            });
        }
    };
    module.exports.init();
});
