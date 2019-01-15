/**
 * @filename thread
 * @description
 * 作者: yixizhou
 * 创建时间: 2014-04-24 09:06:03
 * 修改记录:
 *
 * $Id: thread.js 33338 2014-09-26 04:13:36Z vissong $
 **/

define('module/thread', ['module/followSite', 'module/uploadImg'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var followSite = require('module/followSite');
    var uploadImg = require('module/uploadImg');
    module.exports = {
        popTId: 0,														// 本js模块全局变量：本模块当前要操作的帖子（在perPopBtn）点击后选中的帖子编号
        uploadTimer: null,
        // 初始化滚动图片，如果宽度小于滚动宽度则不显示张数
        initScrollImage: function(id) {
            var id = id || '';
            // 抓取目标class为slideBox，每一个目标都
            jq(id + ' .slideBox').each(function(e) {
                var thisObj = jq(this);
                var liArray = thisObj.find('li');						// 找到当前目标里的li标签
                var liWidth = 0;										// 当前所有li的总宽度全局变量
                for (var i = 0; i< liArray.length; i++) {
                    liWidth += jq(liArray[i]).width();					// 对每一个li标签的宽度进行叠加
                }
                if (thisObj.width() < liWidth) {
                    thisObj.find('.pageNum').show();					// 如果当前slideBox的宽度小于图片叠加总宽度，出现一个第几页的span标签
                }
            });
        },
        // 初始化全文收起按纽、点击事件
        initViewBtn: function(selector, height) {
            var height = height || 165;
            jq(selector).each(function(e) {
                var thisObj = jq(this);
                var pNode = thisObj.find('p[id^="content_"]')[0];
                if (pNode) {
                    if (pNode.scrollHeight > height) {
                        thisObj.find('.viewBtn:first').show();
                    }
                }
            });
        },
        initViewBtnClick: function() {
            jq('.container').on('click', '.viewBtn', function(e) {
                e.stopPropagation();
                var thisObj = jq(this);
                // todo 点击态移走
                thisObj.addClass('commBg');
                setTimeout(function(){
                    thisObj.removeClass('commBg');
                    module.exports._foldSwith.call(thisObj);
                }, 50);

                pgvSendClick({hottag:'QUAN.SITE.LIST.QUANWEN'});
            });
        },
        _foldSwith: function(e) {
            var thisObj = jq(this);
            var text = thisObj.html();
            var height = '', returnTop = false;
            if (text == '收起') {
                returnTop = true;
                height = '150px';
                text = '全文';
            } else {
                height = 'none';
                text = '收起';
            }
            thisObj.parent().find('p').css('max-height', height);
            thisObj.html(text);
            // 收起的时候，回文章处
            if (returnTop) {
                scroll(0, thisObj.closest('.topicBox').position().top);
            }
        },
        // 管理面板弹层
        showManagerPanel: function(tId, parentId, pId, floorPId, uId, author, isViewthread, isReply, nodeId) {
            var isViewthread = isViewthread || false;
            var parentId = parentId || 0;
            var isReply = isReply || false;
            var tmpl = template.render('tmpl_manage', {'tid':tId, 'isReply':isReply});
            var opts = {
                'id': 'operationMenu',
                'isHtml':true,
                'isMask':true,
                'content':tmpl,
                'callback':function() {
                    jq('.g-mask').on('click', function(e) {
                        jq.DIC.dialog({id:'operationMenu'});
                    });

                    jq('.manageLayer').find('a').on('click', function(e) {
                        var that = jq(this);
                        jq.DIC.touchStateNow(that);
                        jq.DIC.dialog({id:'operationMenu'});
                        var btnType = that.attr('btnType');
                        switch (btnType) {
                            case 'delThread':
                                module.exports._deleteThread(tId, parentId, isViewthread);
                                break;
                            case 'banUser':
                                module.exports._banUser(uId, author);
                                break;
                            case 'closeUpdate':
                                module.exports._closeUpdate(sId, tId, parentId, that);
                                break;
                            case 'cleanPost':
                                module.exports._cleanPost(uId, author);
                                break;
                            case 'reply':
                                module.exports.reply(window.sId, tId, parentId, pId, floorPId, author, isViewthread, nodeId);
                                break;
                            case 'delReply':
                                module.exports._delReply(tId, pId, floorPId, isViewthread);
                                break;
                            case 'profile':
                                jq.DIC.open('/profile/' + uId);
                                break;
                        }
                    });
                }
            };
            jq.DIC.dialog(opts);			// 生成对话框
        },
        // 锁定话题函数
        _closeUpdate: function(sId, tId, parentId, obj) {
            var threadStatus = parseInt(obj.attr('threadStatus'));
             var content = '话题锁定后可以点赞和回复，但不会再被顶起';
             if (threadStatus) {
                content = '话题解锁后可以被顶起';
             }
             var opts = {
                 'id':'opertionConfirm',
                 'isMask':true,
                 'content':content,
                 'okValue':'确定',
                 'cancelValue':'取消',
                 'ok':function() {
                     var closeForm = jq('#closeUpdateForm');
                     if (threadStatus) {
                        closeForm.attr('action', '/thread/open');
                     } else {
                        closeForm.attr('action', '/thread/close');
                     }
                     closeForm.find('input[name="sId"]').val(sId);
                     closeForm.find('input[name="tId"]').val(tId);
                     closeForm.find('input[name="parentId"]').val(parentId);
                     var opt = {
                         success:function(re) {
                             jq.DIC.dialog({id:'operationMenu'});
                             var status = parseInt(re.errCode);
                             if(status === 0) {
                                 if (threadStatus) {
                                    obj.attr('threadStatus', '0');
                                    obj.html('锁定');
                                 } else {
                                    obj.attr('threadStatus', '1');
                                    obj.html('解锁');
                                 }
                             }
                         },
                         'noJump':true
                     };
                     jq.DIC.ajaxForm('closeUpdateForm', opt, true);
                 }
             };
             jq.DIC.dialog(opts);
        },
        // 自己点击自己的帖子可以删除操作
        _deleteThread: function(tId, parentId, isViewthread) {
            var opts = {
                'id':'opertionConfirm',
                'isMask':true,
                'content':'确定（调用_deleteThread）删除吗？',
                'okValue':'确定',
                'cancelValue':'取消',
                'ok':function() {
                    var delForm = jq('#delThreadForm');
                    delForm.find('input[name="tIds[]"]').val(tId);
                    if (!delForm.find('input[name="parentId"]')[0]) {
                        delForm.append('<input type="hidden" name="parentId" value="0">');
                    }
                    delForm.find('input[name="parentId"]').val(parentId);
                    var opt = {
                        success:function(re) {
                            jq.DIC.dialog({id:'operationMenu'});
                            var status = parseInt(re.errCode);
                            if(status === 0) {
                                // 详情页跳回列表页
                                if (isViewthread) {
                                    jq.DIC.open('/' + sId);
                                } else {
                                    jq("#t_" + tId + '_0_0').remove();
                                }
                            }
                        },
                        'noJump':true
                    };
                    jq.DIC.ajaxForm('delThreadForm', opt, true);
                }
            };
            jq.DIC.dialog(opts);
        },
        // 管理员禁言某用户
        _banUser: function(uId, author) {
        	var content = '确认（调用_banUser）将“'+author+'”禁言吗';
        	var opts = {
                 'id':'opertionConfirm',
                 'isMask':true,
                 'content':content,
                 'okValue':'确定',
                 'cancelValue':'取消',
                 'ok':function() {
                     var banForm = jq('#banUserForm');							// 非管理员页面上没有禁言form，要么就申请个管理员账号，要么就推断一个banForm
                     banForm.find('input[name="uId"]').val(uId);
                     var opt = {
                         success:function(re) {
                             jq.DIC.dialog({id:'operationMenu'});
                             var status = parseInt(re.errCode);
                             if(status === 0) {
                                 //todo del list
                             }
                         },
                         'noJump':true
                     };
                     jq.DIC.ajaxForm('banUserForm', opt, true);
                 }
             };
             jq.DIC.dialog(opts);
        },
        // 管理员清理某人的所有话题
        _cleanPost: function(uId, author) {
             var content = '确认（调用_cleanPost）清理“'+author+'”的所有话题吗';
             var opts = {
                 'id':'opertionConfirm',
                 'isMask':true,
                 'content':content,
                 'okValue':'确定',
                 'cancelValue':'取消',
                 'ok':function() {
                     var cleanForm = jq('#cleanPostForm');						// 非管理员页面上没有清理所有话题的form，要么就申请个管理员账号，要么就推断一个cleanPostForm
                     cleanForm.find('input[name="uId"]').val(uId);				// 将要清理的用户id放入cleanPostForm里，然后进行提交
                     var opt = {
                         success:function(re) {
                             jq.DIC.dialog({id:'operationMenu'});
                             var status = parseInt(re.errCode);
                             if(status === 0) {
                                 //todo del list
                            	 //可以刷新，也可以直接使用jquery移除页面上所有该用户的发言
                             }
                         },
                         'noJump':true
                     };
                     jq.DIC.ajaxForm('cleanPostForm', opt, true);
                 }
             };
             jq.DIC.dialog(opts);
        },
        // 删除回复
        _delReply: function (tId, pId, floorPId, isViewthread) {
            var floorPId = floorPId || 0;
            jq.DIC.dialog({
                id: '_delReply',
                content:'确定（调用_delReply）删除这条回复吗？',
                okValue:'确定',
                cancelValue:'取消',
                isMask:true,
                ok:function (){
                    var url = '/' + sId + '/r/del';
                    if (floorPId > 0) {
                        var url = '/' + sId + '/f/del';
                    }
                    jq.DIC.ajax(url, {'CSRFToken': CSRFToken, 'tId':tId, 'pId':pId, 'parentId': window.parentId, 'floorPId':floorPId}, {
                        'success': function (re) {
                            jq.DIC.dialog({id:'operationMenu'});
                            var status = parseInt(re.errCode);
                            if (status == 0) {
                                if (isViewthread) {
                                    if (floorPId > 0) {
                                        jq('#p_' + tId + '_' + pId + '_' + floorPId).remove();
                                        var floorList = jq('#fl_' + pId + ' li[id^=p_]');
                                        var moreObj = jq('#fl_' + pId + ' .moreInReply');
                                        if (floorList.length < 1 && !moreObj.is(':visible')) {
                                            jq('#fl_' + pId).parent().hide();
                                        }
                                    } else {
                                        jq('#p_' + tId + '_' + pId + '_0').remove();
                                        var replyList = jq('#replyList');
                                        //如果replylist中不存在回复item，则删除该父容器
                                        if(replyList.children().length < 1){
                                            replyList.parent().hide();
                                        }
                                    }
                                } else {
                                    var replyNode = jq('#p_' + tId + '_' + pId + '_' + floorPId);
                                    var replyList = replyNode.closest('.replyList');
                                    replyNode.remove();
                                    //如果replylist中不存在回复item，则删除该父容器
                                    // todo 移到回调中
                                    var moreObj = jq('#rCount_' + tId);
                                    if(replyList.children().length < 1 && !moreObj.is(':visible')){
                                        replyList.parent().hide();
                                    }
                                }
                            }
                        },
                        'noJump':true,
                        'error' : function () {}
                    });
                }
            });
            return true;
        },
        labelData: {},
        /**
         * _changeLabel
         * @desc 管理员对用户的帖子修改标签
         * @param sId	社区编号
         * @param tId	帖子编号
         * @param e
         * @private
         */
        _changeLabel: function(sId,tId,e){
            var e = e = e || window.event,
                target = e.target,
                noLabel = jq(target).attr('nolabel'),
                topicConWarp = jq(target).closest('.topicCon').find('.detailCon .dCon'),
                topicLabel = topicConWarp.find('.evtTag'),
                oldLabelId = typeof(window.threadLabelId)=='undefined' ? (topicLabel.attr('labelId') || '') : window.threadLabelId,
                filerType = 0;
            //如果是标签页，取urlfilterType
            if(jq.DIC.getQuery('filterType')){
                filerType = jq.DIC.getQuery('filterType');
                oldLabelId = filerType;
            }
            if (jq.isEmptyObject(module.exports.labelData)) {
                var url = '/' + sId + '/label';
                var opts = {
                    'success': function(re) {
                        var status = parseInt(re.errCode);
                        if (status != 0) {
                            return false;
                        }
                        if(re.data.labelList.length == 0){
                            jq.DIC.dialog({content: '还没有标签，请在管理台设置标签', autoClose: true});
                            return false;
                        }
                        module.exports.labelData = re.data;
                        module.exports.labelData.filterType = oldLabelId;
                        var tmpl = template.render('tmpl_changLabel', module.exports.labelData);
                        jq.DIC.dialog({content:tmpl, id:'changLabel', isMask:true, isHtml:true, callback:labelEvent});
                    },
                    'noMsg':true
                };
                jq.DIC.ajax(url, '', opts);
            } else {
                module.exports.labelData.filterType = oldLabelId;
                var tmpl = template.render('tmpl_changLabel', module.exports.labelData);
                jq.DIC.dialog({content:tmpl, id:'changLabel', isMask:true, isHtml:true, callback:labelEvent});

            }
            /**
             * @desc 回调事件
             */
            function labelEvent(){
                /**
                 * @desc 列表中的tablelist
                 * @type {*}
                 */
                var labelListTeam = jq('.evtLabelList a'),
                    labelOn = jq('.evtLabelList a[class=on]'),
                    labelOkBtn = jq('.evtLabelOk'),
                    labelObj=null;

                labelListTeam.trigger('click');

                //判断当明是否有判断，如果没有全部选中状态
                if(labelOn.length==0){
                    jq('.evtLabelAll').addClass('on');
                }

                /**
                 * @desc 选择标签
                 */
                labelListTeam.on('click',function(){
                    labelListTeam.removeClass('on')
                    jq(this).addClass('on');
                    labelObj = jq(this);
                });

                /**
                 * @desc 确认
                 */
                labelOkBtn.on('click',function(){
                    jq.DIC.dialog({id:'changLabel'});
                    if(!tId || labelObj==null){
                        return false;
                    }
                    var labelId = labelObj.attr('labelid'),
                        labelName = labelObj.html(),
                        url = 'http://m.wsq.qq.com/'+sId+'/label/thread',
                        data = {tId:tId,labelId:labelId,CSRFToken: CSRFToken},
                        opts = {
                        'noMsg': true,
                        'success': function(re) {
                                if (re.errCode == 0) {
                                    if(typeof(window.threadLabelId)!='undefined') {
                                        window.threadLabelId = labelId;
                                    }
                                    //修改标签成功后更新话题列表中标签，话题详情页不显示
                                    //如果是在列表页,标签已存在
                                    if(typeof noLabel=='undefined' && filerType>0 && labelId!=oldLabelId) {
                                        var topicBoxId = '#t_'+tId+'_0_0';
                                        jq(topicBoxId).hide();
                                    }

                                    if(typeof noLabel=='undefined' && topicLabel.length>0) {
                                        if(parseInt(labelId)==0){
                                            topicLabel.remove();
                                            return false;
                                        }
                                        var dataLink = '/'+sId+'?filterType='+labelId;
                                        topicLabel.attr('data-link',dataLink);
                                        topicLabel.attr('labelId',labelId);
                                        topicLabel.html(labelName);
                                    }else if(typeof noLabel=='undefined' && topicLabel.length==0 && parseInt(labelId)>0) {
                                        //如果是在列表页，当前帖子没有标签
                                        var dataLink = '/'+sId+'?filterType='+labelId,
                                            labelHtmlStr = '<a href="javascript:;" data-link="'+dataLink+'" labelid="'+labelId+'" class="topBtn br f11 c2 db evtTag">'+labelName+'</a>';
                                        topicConWarp.prepend(labelHtmlStr);
                                    }
                                    jq.DIC.dialog({content: '修改标签成功！', autoClose: true});
                                }
                            }
                        };

                    jq.DIC.ajax(url, data, opts);
                });
            }
        },
        /** 
         * thread这个js模块对外的提供函数接口reply，可以被forumdisplay调用。
         * 在reply弹窗后调用initReplyEvents执行回复的初始化。
         * */
        reply: function (sId, tId, parentId, pId, floorPId, autor, isViewthread, nodeId, hasTid) {
            var isViewthread = isViewthread || false;
            var author = autor || '';
            var floorPId = floorPId || 0;
            var nodeId = nodeId || 't_' + tId  + '_0_0';		//nodeId就是每一条独立的帖子div<div class="topicBox" id="t_173_0_0" tid="173"></div>的id值
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

            var replyDialog = function() {
                var replyTimer = null;
                var replyForm = template.render('tmpl_replyForm', {data:{'sId':sId, 'tId':tId, 'pId':pId, 'floorPId':floorPId, 'parentId':parentId}});		//渲染页面模板tmpl_replyForm，即<script id="tmpl_replyForm" type="text/html">处

                // 使用DIC的弹出回复框
                jq.DIC.dialog({
                    content:replyForm,
                    id:'replyForm',		// 弹窗的form的id值
                    isHtml:true,
                    isMask:true,		// 阴影遮罩
                    top: 0,				// 绝对位置，距离顶端是0
                    // 弹出后执行的回调函数
                    callback:function() {

                        //非回复主帖，隐藏发图
                        if(!hasTid){jq('.uploadPicBox').css('visibility', 'hidden')};

                        // 定义回复对象
                        var obj = {
                        		pId: pId,
                        		isViewthread: isViewthread,
                        		nodeId: nodeId,					// 要回复的帖子div的id值
                        		floorPId: floorPId,
                        		replyTimer: replyTimer,
                        		author: author,					// 要回复的作者姓名
                        		tId: tId,						// 要回复的帖子编号
                        		sId: sId						// 微社区编号
                        	};
                        //初始化回复窗口事件
                        module.exports.initReplyEvents(obj);

                    },
                    // 关闭回复框
                    close: function() {
                       // 内容非空确认
                       clearInterval(replyTimer);				// 停止存储本地文档
                       module.exports.isNoShowToTop = false;
                       jq('.bNav').show();
                       jq('.floatLayer').show();
                       return true;

                       // 文本框焦点
                       jq('#replyForm .sInput').blur();
                   }
                });
            }

            //不加入社区也可发帖，自动加入社区修改
            replyDialog();

            return true;
        },
        // 检测回复的表单是否为空
        checkReplyForm: function() {
            var content = jq('textarea[name="content"]').val();						// 获取内容
            var contentLen = jq.DIC.mb_strlen(jq.DIC.trim(content));				// 去除空格转中文长度
            if (contentLen <= 0) {
                jq.DIC.dialog({content:'回复内容不能为空', autoClose:true});				// 弹框警告内容不能空
                return false;
            }
            return true;
        },
        publicEvent: function() {

            var sId = window.sId || 0;
            // 世界杯期间 世界杯微社区不在QQ浏览器中banner独出 兼内置QB的手Q
            if (sId && sId == 231914647 && (!isQQBrowser || isMQ)) {
                jq('#pEvent p').hide();
                jq('#pEventImg').attr('src', 'http://dzqun.gtimg.cn/quan/images/worldcup2014.jpg');
                jq('#pEvent').on('click', function() {
                    jq.DIC.reload('http://v.html5.qq.com/#p=worldCupFrame&g=2&ch=001203');
                    return false;
                }).slideDown();
            } else if (!window.isAppBar) {
                // public event
                var url = 'http://localhost/weact/index.php/Login/publicEvent?sId=' + sId;
                var eOpts = {
                    'success': function(re) {
                        var status = parseInt(re.errCode);
                        if (status === 0) {

                            if (jq.isEmptyObject(re.data.event) && jq.isEmptyObject(re.data.ad)) {
                                return false;
                            }

                            var pEvent = re.data.event,
                                ad = re.data.ad;
                        	//if (re.data.event.peNum < 1) {
                        		//return false;
                        	//}

                            var showEvent = true;
                            // 如果有广告的话，看一下是否参与了全局活动
                            if (!jq.isEmptyObject(re.data.ad)) {
                                if (re.data.hadJoin) {
                                    // 已参与全局活动，随机显示广告
                                    if (Math.random() * 100 > 50) {
                                        showEvent = false;
                                    }
                                } else {
                                    showEvent = false;
                                }
                            }

                            if (showEvent) {
                                if (!re.data.hadJoin) {
                                    return false;
                                }

                                jq('#pEventImg').attr('src', pEvent.peBanner);
                                if (pEvent.showJoinNum) {
                                    jq('#pEventNum').html(pEvent.peNum || 0);
                                } else {
                                    jq('#pEvent p').hide();
                                }
                                // todo pEvent.peClickUrl 默认值
                                var url = DOMAIN + pEvent.peClickUrl + '?peId=' + pEvent.peId + '&sId=' + sId;
                            } else {
                                jq('#pEventImg').attr('src', ad.banner);
                                jq('#pEvent p').hide();
                                // todo pEvent.peClickUrl 默认值
                                var url = ad.url;
                            }

                            jq('#pEvent').on('click', function() {
                                jq.DIC.reload(url);
                                return false;
                            }).slideDown();
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg': true
                };
                jq.DIC.ajax(url, '', eOpts);
            }
        },
        // 帖子上边倒三角管理（特别注意，其中markAd是屏幕某用户，不看他发的朋友圈）
        initPopBtn: function() {
        	// 每一条帖子右上角都有一个class为PerPopBtn的a标签，点击后有不同的操作效果，下面是处理操作效果的
            jq('.warp').on('click', '.PerPopBtn', function(e) {
                /**
                 * 右上角弹层点击后的页面效果，执行事件放到点击class为adBCon的p标签后进行操作。
                 */
                e.stopPropagation(e);		// 停止事件DOM冒泡
                
                // 定义一个名叫perPop的事件
                var perPop = function () {
                    var tId = jq(this).attr('tId');										// 抓取当前帖子的编号
                    if (tId != module.exports.popTId) {
                        jq('#t_' + module.exports.popTId + '_0_0 .perPop').hide();		// 如果点击的帖子并不是之前已经点过并弹出来的操作标签的帖子，就把之前的操作标签隐藏掉
                    }
                    var popObj = jq('#t_' + tId + '_0_0 .perPop');						// 取到当前帖子的perPop标签
                    if(popObj.css('display') != 'none') {
                        popObj.hide();													// 如果这次点击的时候，本来它已经是block的，则隐藏掉他
                        module.exports.popTId = 0;										// 本模块目前没有要操作的帖子
                    } else {
                        popObj.show();													// 如果这次点击的时候，本来是none的，（原来帖子不是这个，或者原来没有帖子两种情况），展现这个perPop
                        module.exports.popTId = tId;									// 同时模块内将要操作帖子的编号变量（popTId）变成当前帖子的变量
                    }
                };

                var thisObj = jq(this);													// 抓到当前点击的PerPopBtn
                perPop.call(thisObj);													// 对其调用定义的perPop事件
                return false;
            }).on('click', '.adBCon p', function(e) {
            	/** 右上角弹层点击各操作功能，方法描述如下：
            	 * 1、delThread：删除（自己的）帖子；
            	 * 2、banUser：禁止某用户发言（管理员用的）；
            	 * 3、closeUpdate：锁定帖子（锁定后不会被弹起，有一个threadstatus标志位区分）；
            	 * 4、cleanPost：清理帖子（管理员用的）；
            	 * 5、markAd：屏蔽某用户发的微博；
            	 * 6、changeLabel：修改帖子标记label（管理员用的）；
            	 * 7、pmDialog：私信某用户。
            	 * */
                e.stopPropagation(e);								// 停止DOM事件传播
                var thisObj = jq(this);								// 抓取当前点击的对象
                var btnType = thisObj.attr('btnType');				// 获取点击对象的btntype属性（页面上是小写btntype，大写T貌似也能识别到）
                var tId = thisObj.parent().attr('tId');				// 找到当前点击对象的父级元素中的帖子编号
                var author = thisObj.parent().attr('author');		// 找到当前点击对象的父级元素中的作者名字
                var uId = thisObj.parent().attr('uId');				// 找到当前点击对象的父级元素中的作者编号
                var parentId = window.parentId || 0;
                if (uId && tId) {
                    switch (btnType) {
                        case 'delThread':
                            module.exports._deleteThread(tId, parentId, false);				// 如果是“删除”操作，调用本模块的函数
                            break;
                        case 'banUser':
                            module.exports._banUser(uId, author);							// 如果是“禁言”操作，调用本模块的函数
                            break;
                        case 'closeUpdate':
                            module.exports._closeUpdate(sId, tId, parentId, thisObj);		// 如果是“锁定”操作，调用本模块的函数
                            break;
                        case 'cleanPost':
                            module.exports._cleanPost(uId, author);							// 如果是“清理”操作，调用本模块的函数
                            break;
                        case 'markAd':
                            var opts = {
                                'success': function(re) {
                                    var status = parseInt(re.errCode);
                                    if (status === 0) {
                                        jq('#t_' + tId + '_0_0').hide();					// 如果屏蔽成功了，将该用户的该条微博屏蔽，注意是该条微博，不是该用户的所有微博（可以做成所有）
                                    }
                                }
                            }
                            jq.DIC.ajax('/' + sId + '/markads', {'CSRFToken': CSRFToken, 'tId':tId, 'parentId':parentId}, opts);	//如果是“屏蔽”用户操作，发送屏蔽的ajax请求
                            break;
                        case 'changeLabel':
                            module.exports._changeLabel(sId,tId,e);							// 如果是“修改标签”操作，调用本模块的函数
                            break;
                        case 'pmDialog':
                            var url = '/my/pm/dialog?targetUid='+uId+'&sId='+sId;			// 如果是“私信”操作，直接跳转私信的网址
                            jq.DIC.reload(url)
                            break;
                    }
                }
                return false;
            });

            // 在文档中绑定点击事件，点任意处倒三角弹窗关闭
            jq(document).bind("click", function(){
                if (module.exports.popTId) {
                    jq('#t_' + module.exports.popTId + '_0_0 .perPop').hide();				// 如果当前模块有正在操作的帖子，将其倒三角隐藏
                    module.exports.popTId = 0;												// 默认点击非倒三角区域，当前模块不操作任何帖子
                }
            });
        },
        //初始化回复窗口事件initReplyEvents
        initReplyEvents: function(obj){
            var storageKey = obj.sId + 'reply_content';							// 定义storageKey变量：“微社区编号_reply_content”
            // require.async(模块id, callback) 方法用来在模块内部异步加载模块，并在加载完成后执行指定回调。callback 参数可选。
            require.async('module/emotion', function(emotion) {
                // 表情开关
                var reInit = true;
                emotion.init(reInit);

                //此种写法兼容ios7
                //jq('.iconExpression').on('touchstart', emotion.toggle);
                //jq('.iconExpression').on('click', emotion.toggle);

                //表情 图片点击切换
                var aOperatIcon = jq('.operatIcon');
                aOperatIcon.on('click', function(){
                    var thisObj = jq(this);
                    var thisNum = thisObj.attr('data-id');
                    var aOperatList = jq('.operatList');
                    aOperatList.hide();
                    jq(aOperatList[thisNum]).show();
                    if(thisNum == 0){
                        jq('.expreList').show();
                        jq('.expreBox').show();
                    }
                    //如果是当前选中状态，则点击隐藏
                    if(thisObj.hasClass('on')){
                        jq(aOperatList[thisNum]).hide();
                        thisObj.removeClass('on');
                    }else{
                        aOperatIcon.removeClass('on');
                        thisObj.addClass('on');
                    }
                });
                //表情总个数大于手机宽度时显示更多按钮
                var expressionMenu = jq('.expressionMenu').find('a');
                var haveMenuWidth = expressionMenu.length*76;
                var operatingBoxWidth = jq('.operatingBox').width();
                if(haveMenuWidth > operatingBoxWidth){
                    jq('.iconArrowR').show();
                };
                //输入框选中时隐藏表情，如果当只有表情打开时（好像不隐藏体验效果更好）
                /*jq('#content').on('focus', function(){
                    if(jq('.photoTipsBox').is(':hidden')){
                        emotion.hide();
                        aOperatIcon.removeClass('on');
                    }
                });*/

            });
            //如果回复自带图片
            if (obj.pId > 0) {
                //jq('#replyForm').attr('action', '/' + obj.sId + '/f/new/submit');				//设置上传action为f...
                jq('textarea[name="content"]').attr('placeholder', '回复 ' + obj.author + '：');	//回复谁
                jq('input[name="floorPId"]').val(obj.floorPId);
            } else {
            	//如果回复不带图片
                //jq('#replyForm').attr('action', '/' + obj.sId + '/r/new/submit');				//设置上传action为r...
                // 信息恢复
                jq('textarea[name="content"]').val(localStorage.getItem(storageKey));			//本地信息
            }

            // 弹层回复发送按纽绑定事件
            var isSendBtnClicked = false;				// 发送按钮被点击的标志isSendBtnClicked
            jq('#comBtn').on('click', function() {
                if (isSendBtnClicked){
                    return false;
                }
                var opt = {
                    success:function(re) {
                        var status = parseInt(re.errCode);
                        if (status === 0) {
                            if (re.data.authorUid != '') {
                            	// 原来是if (re.data.authorUid > 0)，现在是不等于''，代表回复成功
                                localStorage.removeItem(storageKey);
                                if (obj.isViewthread) {
                                	// 在帖子详情页面可以对帖子的回复进行再回复
                                    // 如果是回复回复
                                    if (obj.pId) {
                                        var tmpl = template.render('tmpl_reply_floor', {floorList:{0:re.data}});
                                        jq('#fl_' + obj.pId + ' ul').append(tmpl);
                                        jq('#fl_' + obj.pId).parent().parent().show();
                                        // 普通回复
                                    } else {
                                        // 直接显示回复的内容到页面
                                        // 格式化用户等级
                                        if(re.data.authorExpsRank){
                                            re.data.authorExps = {};
                                            re.data.authorExps.rank = re.data.authorExpsRank;
                                        }
                                        re.data.restCount = 0;
                                        var tmpl = template.render('tmpl_reply', {replyList:{0:re.data}, rIsAdmin:window.isManager, rGId:window.gId, groupStar:window.groupStar, isWX:window.isWX});
                                        // 结构变了与列表不同
                                        var allLabelBox = jq('#allLabelBox'),
                                            replyList = jq('#replyList');
                                        allLabelBox.show();
                                        allLabelBox.next('.topicList').show();
                                        /**
                                         * @desc    window.desc from viewthread.js, 回复列表排序 0 或者 1, 默认 0
                                         *          如果为1，发表的新内容插入到列表最上面，否则插入到列表最下面
                                         */
                                        if (!window.desc) {
                                            jq('#allReplyList').append(tmpl);
                                        } else {
                                            jq('#allReplyList').prepend(tmpl);
                                        }

                                        jq('#rCount').html(re.data.rCount);
                                        replyList.parent().show();
                                    }
                                } else {
                                	// 特别注意：个人感觉最后一条回复顶到帖子下边第一条回复会比较科学，也就是倒序排列，2014-12-26。
                                	// 在论坛社区页面，貌似只能回复帖子本身，在详情页面还可以回复帖子的回复（产生楼中楼）
                                    // 如果是回复帖子，则直接显示回复的内容到页面（注意check服务器回复后的json数据格式）
                                    var tmpl = template.render('tmpl_reply', {replyList:{0:re.data}});			// 使用返回数据的data字段里的回复信息渲染tmpl_reply模板
                                    var replyList = jq('#' + obj.nodeId + ' .replyList');						// 抓取该帖子topicBox这个div里、class名为topicList的div里、class名为replyListdiv的ul
                                    replyList.append(tmpl);														// 将渲染后的html信息附加到ul的最后一条li后
                                    if (re.data.rCount > 0) {
                                        var rCount = parseInt(jq('#rCount_' + obj.tId).attr('rCount') || 0) + 1;									// 显示全部几条回复的数量+1
                                        jq('#rCount_' + obj.tId).html('查看全部' + re.data.rCount + '条回复');											// 将这个全部几条回复的字写回去
                                        jq('#t_'+obj.tId+'_0_0').find('.threadReply').html('<i class="iconReply f18 cf"></i>'+re.data.rCount);		// 更新（点赞、转发、回复）这里的回复数目的值
                                    }
                                    replyList.parent().show();													// 如果没有任何回复信息，replyList的父级元素topicList的display是none的，所以这里要展现
                                }
                            }
                            // initLazyload('.warp img');

                            clearInterval(obj.replyTimer);				// 取消输入框文字的周期计算

                            // 关闭弹窗
                            module.exports.isNoShowToTop = false;
                            jq.DIC.dialog({id:'replyForm'});
                            jq('.bNav').show();
                            jq('.floatLayer').show();
                        }
                        isSendBtnClicked = false;
                    },
                    error:function(re) {
                        isSendBtnClicked = false;
                    }
                };
                if (!module.exports.checkReplyForm()) {
                    return false;
                }
                isSendBtnClicked = true;
                //此处还是没有定义opt.url，所以是在global的ajaxForm里默认生成url的
                jq.DIC.ajaxForm('replyForm', opt, true);
                return false;
            });

            // 回复输入框文字存储定时事件：replyTimer，1秒钟执行一次，将本地文本框内容存入storageKey
            obj.replyTimer = setInterval(function() {
                //jq.DIC.strLenCalc(jq('textarea[name="content"]')[0], 'pText', 280);
                if (jq('textarea[name="content"]').val()) {
                    localStorage.removeItem(storageKey);										// 先移除
                    localStorage.setItem(storageKey, jq('textarea[name="content"]').val());		// 再写入
                }
            }, 1000);

            module.exports.isNoShowToTop = true;
            // 隐藏底部导航和向上
            jq('.bNav').hide();
            jq('.floatLayer').hide();

            jq('#fwin_dialog_replyForm').css('top', '0');

            jq('#cBtn').bind('touchstart', function() {
                jq(this).addClass('cancelOn');
            }).bind('touchend', function() {
                jq(this).removeClass('cancelOn');
                if(jq.os.android && parseInt(jq.os.version) <= 2){
                    jq(this).click();
                }
            });

            jq('#comBtn').bind('touchstart', function() {
                jq(this).addClass('sendOn');
            }).bind('touchend', function() {
                jq(this).removeClass('sendOn');
            });

            module.exports.initUpload();

        },
        // 检测回复框的表单是否可以提交函数checkForm
        checkForm: function() {
        	// 检测每一个上传框里的图片是否完整上传
            jq.each(uploadImg.uploadInfo, function(i,n) {
                if (n && !n.isDone) {
                    jq.DIC.dialog({content:'图片上传中，请等待', autoClose:true});
                    return false;
                }
            });
            // 检测内容是否小于7个字
            var content = jq('#content').val();
            var contentLen = jq.DIC.mb_strlen(jq.DIC.trim(content));				// 获取去除空格后格式化后的内容长度
            if (contentLen < 15) {
                jq.DIC.dialog({content:'内容过短', autoClose:true});					// 如果还是小于7个字（15个半角），则提示内容过短
                return false;
            }
            return true;
        },
        // 上传图片事件initUpload
        initUpload: function() {
            // 上传图片的绑定
            jq('#addPic, .uploadPicBox').on('click', function() {
                if(!uploadImg.checkUploadBySysVer()){
                    return false;
                };
            });

            jq('#uploadFile, #fistUploadFile').on('click', function() {
                var thisObj = jq(this);
                if (uploadImg.isBusy) {
                    jq.DIC.dialog({content:'上传中，请稍后添加', autoClose:true});
                    return false;
                }
            });

            jq('body').on('click', '.iconSendImg, .iconArrowR', function(e){
                var thisObj = jq(this);
                var photoList = jq('.photoList');
                //点击图片图标
                if(thisObj.hasClass('iconSendImg')){
                    if(photoList.is(':hidden')){
                        photoList.show();
                    }
                }
                //查看更多表情
                if(thisObj.hasClass('iconArrowR')){
                    var expressionMenu = jq('.expressionMenu').find('a');
                    var haveMenuWidth = expressionMenu.length*76;
                    var expressionTabWidth = jq('.expressionTab').width();
                    if(haveMenuWidth > expressionTabWidth){
                        var firstChild = jq(expressionMenu[0]);
                        jq('.expressionMenu').append(firstChild.clone());
                        firstChild.remove();
                    }else{
                        jq.DIC.dialog({id:'haveMoreExpression', content:'没有更多表情了哦~',autoClose:true});
                    }
                }

            });
            //首次点击图片的图标，触发一次手机的默认上传事件
            jq('body').on('change', '#fistUploadFile', function(e){
                var content = jq('#content')[0];
                jq('.photoList').show();
                jq('.operatList').hide();
                jq('.photoTipsBox').show();
                jq('.operatIcon').removeClass('on');
                jq('.iconSendImg').addClass('on');

                //传图时输入框定位到底部
                content.scrollTop = content.scrollHeight
            });

            // 文件表单发生变化时
            jq('body').on('change', '#uploadFile, #fistUploadFile', function(e) {
                //执行图片预览、压缩定时器
                uploadTimer = setInterval(function() {
                    // 预览
                    setTimeout(function() {
                        if (uploadImg.previewQueue.length) {
                            var jobId = uploadImg.previewQueue.shift();
                            uploadImg.uploadPreview(jobId);
                        }
                    }, 1);
                    // 上传
                    setTimeout(function() {
                        if (!uploadImg.isBusy && uploadImg.uploadQueue.length) {
                            var jobId = uploadImg.uploadQueue.shift();
                            uploadImg.isBusy = true;
                            uploadImg.createUpload(jobId, 'replyForm', uploadTimer);
                        }
                    }, 10);
                }, 300);

                e = e || window.event;
                var fileList = e.target.files;
                uploadNum = jq('.photoList').find('li').length || 0;
                if (!fileList.length) {
                    return false;
                }

                for (var i = 0; i<fileList.length; i++) {
                    if (uploadNum > 8) {
                        jq.DIC.dialog({content:'你最多只能上传8张照片',autoClose:true});
                        break;
                    }

                    var file = fileList[i];

                    if (!uploadImg.checkPicType(file)) {
                        jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                        continue;
                    }
                    if (!uploadImg.checkPicSize(file)) {
                        jq.DIC.dialog({content: '图片体积过大', autoClose:true});
                        continue;
                    }

                    var id = Date.now() + i;
                    // 增加到上传对象中, 上传完成后，修改为 true
                    uploadImg.uploadInfo[id] = {
                        file: file,
                        isDone: false,
                    };
                    var current_default = '/weact/APP/Modules/Community/Tpl/Public'; // 如果变更默认图片的地址要更改这里的值
                    var html = '<li id="li' + id + '"><div class="photoCut"><img src="'+current_default+'/images/defaultImg.png" class="attchImg" alt="photo"></div>' +
                            '<div class="maskLay"></div>' +
                            '<a href="javascript:;" class="cBtn cBtnOn pa db" title="" _id="'+id+'">关闭</a></li>';
                    jq('#addPic').before(html);

                    uploadImg.previewQueue.push(id);

                    // 图片已经上传了 8 张，隐藏 + 号
                    if (uploadNum > 7) {
                        jq('#addPic').hide();
                    }

                    //更新剩余上传数
                    setTimeout(function(){
                        uploadImg.uploadRemaining();
                    }, 400);

                }
                // 把输入框清空
                jq(this).val('');

            });
            
            // 上传照片列表取消按钮点击事件
            jq('.photoList').on('click', '.cBtn', function() {

                var id = jq(this).attr('_id');			// 抓取点击的id编号
                // 取消这个请求
                if (uploadImg.xhr[id]) {
                    uploadImg.xhr[id].abort();			// 取消这个图片上传
                }
                // 图片删除
                jq('#li' + id).remove();				// 页面效果：移除图片
                // 表单中删除
                jq('#input' + id).remove();				// 在表单中移除隐藏input
                delete uploadImg.uploadInfo[id];

                // 图片变少了，显示+号
                if (uploadImg.countUpload() < uploadImg.maxUpload) {
                    jq('#addPic').show();
                    jq('.iconSendImg').removeClass('fail');
                }
                //更新剩余上传数
                uploadImg.uploadRemaining();

            });

        }
    };
});