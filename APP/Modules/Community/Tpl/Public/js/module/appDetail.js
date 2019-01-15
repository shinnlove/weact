/**
 * @filename appDetail
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2014-06-26 09:56:03
 * 修改记录:
 *
 * $Id: appDetail.js 33400 2014-09-30 05:44:02Z vissong $
 **/

define('module/appDetail', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        appFrame: '',
        appFrameWin: '',
        validAction: ['share', 'resize', 'initShare', 'payOrder', 'showUserInfoForm'],
        validOrigin: '',
        init: function() {
//            module.exports._wsqMsgCallReady();
            // 头部点击
            jq('.header').on('click', function() {
                jq.DIC.open(DOMAIN + jq(this).attr('_sId') + '?source=openapp');
            });

            jq('.feedback').on('click', 'a', function() {
                jq.DIC.open(jq(this).attr('link'));
            });

            module.exports.appFrame = document.getElementById('appFrame');
            module.exports.appFrameWin = module.exports.appFrame.contentWindow;
            var href = window.appUrl.match(/https?\:\/\/[^\/]+/);
//                href = module.exports.appFrame.src.match(/https?\:\/\/[^\/]+/);
            if (!href) {
                jq.DIC.dialog({content:'无效的应用地址', autoClose:true});
                return false;
            }
            module.exports.validOrigin = href[0];
            window.addEventListener('message', function(e) {
                // 限制域名
                if (e.origin != module.exports.validOrigin) {
                    jq.DIC.dialog({content:'请求来源非法', autoClose:true});
                    return false;
                }
                var data = e.data || {};
                    action = data.action || 'default';
                    params = data.params || {};
                // 操作过滤
                if (jq.inArray(action, module.exports.validAction) === -1) {
                    jq.DIC.dialog({content:'无效的操作', autoClose:true});
                    return false;
                }
                // 执行操作
                if (typeof module.exports[action] === 'function') {
                    module.exports[action](params);
                }
            },false);
        },
        // 分享框
        share: function(params) {
            var content = params.content || '';
            var tmpl = template.render('tmpl_share', {'content':content});
            jq.DIC.dialog({
                'id': 'appShareWin',
                'content':tmpl,
                'isHtml':true,
                'isMask':true,
                'callback': function() {
                    var isSendBtnClicked = false;
                    // 分享确认点击
                    jq('#appShareBtn').on('click', function() {
                        if (isSendBtnClicked){
                            return false;
                        }
                        isSendBtnClicked = true;
                        var opts = {
                            'error': function() {
                                isSendBtnClicked = false;
                            },
                            'success': function(re) {
                                isSendBtnClicked = false;
                                var status = parseInt(re.errCode);
                                if (status !== 0) {
                                    return false;
                                }
                                jq.DIC.dialog({id:'appShareWin'});
                            }
                        };
                        jq.DIC.ajaxForm('appShareForm', opts, true);
                        return false;
                    });
                }
            });
            return true;
        },
        // 修改iframe 宽高 刷新页面
        resize: function(params) {
            // 刷新
            if (params.r) {
                window.location.reload();
                return true;
            }
            // 宽 todo 不允许修改宽
            var width = params.w;
            if (width) {
                module.exports.appFrame.setAttribute('width', width);
            }
            // 高
            var height = params.h;
            if (height) {
                module.exports.appFrame.setAttribute('height', height);
            }
            return true;
        },
        // 初始化手Q微信分享
        initShare: function(params) {
        	
            // 页面分享初始化微信分享
            if (isWX) {
                require.async('module/wxshare', function(wxshare) {
                    wxshare.initWXShare({
                        'sId': window.sId,
                        'tId': 0,
                        'img': params.img || window.shareImgUrl,
                        'desc': params.desc || window.shareDesc,
                        'title': params.title || window.shareTitle,
                        'callback': function(re) {
                            module.exports._callback(params.cbName, re.err_msg);
                        }
                    });
                });
            } else if (isMQ) {
                // 右上角分享定制
                if (typeof(mqq.data.setShareInfo) != 'undefined') {
                    mqq.data.setShareInfo({
                        'share_url': window.shareUrl,
                        'title': params.title || window.shareTitle,
                        'desc': params.desc || window.shareDesc,
                        'image_url': params.img || window.shareImgUrl
                    }, function(re) {
                        module.exports._callback(params.cbName, re.err_msg);
                    });
                }
            }
            return true;
        },
        // 支付确认
        payOrder: function(params) {
            var orderId = params.orderId || '';
            jq.DIC.ajax('/app/order?sId=' + window.sId + '&appId=' + window.appId + '&orderId=' + orderId, {}, {
                noMsg: true,
                success: function(re) {
                    var status = parseInt(re.errCode);
                    // 查询订单失败通知应用
                    if (status !== 0) {
                        module.exports._callback(params.cbName, {errCode:re.errCode, errMsg:re.message});
                        return false;
                    }

                    var desc = re.data.desc || '';
                    var price = re.data.price || 0;
                    var tmpl = template.render('tmpl_pay_comfirm', {desc:desc, price:price});
                    // 支付确认框
                    jq.DIC.dialog({
                        id: 'orderConfirm',
                        isMask: true,
                        isHtml: true,
                        content: tmpl,
                        callback: function() {
                            var isSendBtnClicked = false;
                            // 用户确认
                            jq('#payOrder').on('click', function() {
                                if (isSendBtnClicked){
                                    return false;
                                }
                                isSendBtnClicked = true;
                                var data = {sId: window.sId, appId: window.appId, CSRFToken: window.CSRFToken, orderId: orderId};
                                var opts = {
                                    error: function(info) {
                                        isSendBtnClicked = false;
                                    },
                                    success: function(re) {
                                        isSendBtnClicked = false;
                                        var status = parseInt(re.errCode);
                                        if (status !== 0) {
                                            return false;
                                        }
                                        jq.DIC.dialog({id:'orderConfirm'});
                                        // 成功通知应用
                                        module.exports._callback(params.cbName, {errCode:0, errMsg:'支付成功'});
                                    }
                                };
                                jq.DIC.ajax('/app/order/submit', data, opts);
                                return false;
                            });

                            // 用户取消支付
                            jq('#cancelOrder').on('click', function() {
                                module.exports._callback(params.cbName, {errCode:1, errMsg:'用户取消支付'});
                                jq.DIC.dialog({id:'orderConfirm'});
                                return false;
                            });
                        },
                        // 关闭窗口
                        close: function() {
                            module.exports._callback(params.cbName, {errCode:1, errMsg:'用户取消支付'});
                            jq.DIC.dialog({id:'orderConfirm'});
                        }
                    });
                }
            });
        },
        // 填写用户资料
        showUserInfoForm: function(params) {
            var fields = params.fields || [];
            var strFields = fields.join(',');
            if (!strFields) {
                module.exports._callback(params.cbName, {errCode:1, errMsg:'未填写需要用户填写的资料项'});
                return false;
            }

            jq.DIC.ajax('/app/userInfo?sId=' + window.sId + '&appId=' + window.appId + '&fields=' + strFields, {}, {
                noMsg: true,
                success: function(re) {
                    // 请求失败
                    if (parseInt(re.errCode) !== 0) {
                        module.exports._callback(params.cbName, {errCode:re.errCode, errMsg:re.message});
                        return false;
                    }

                    if (!re.data) {
                        module.exports._callback(params.cbName, {errCode:2, errMsg:'接口未返回数据，请重试'});
                        return false;
                    }

                    // console.log(re.data);
                    var tmpl = template.render('tmpl_userinfo_form', re.data);

                    jq.DIC.dialog({
                        id: 'userInfoForm',
                        isMask: true,
                        isHtml: true,
                        content: tmpl,
                        callback: function() {
                            var isSendBtnClicked = false;
                            // 用户确认
                            jq('#userInfoComfrim').on('click', function() {

                                // 整理需要提交的用户资料数据
                                var infoData = {};
                                for (var i = 0;i < fields.length; ++i) {
                                    var name = fields[i],
                                        val = jq('.exchangeBox').find("input[name='" + name + "']").val();

                                    if (!val) {
                                        jq.DIC.dialog({content:'有未填写的资料，请检查', autoClose:true});
                                        return false;
                                    }

                                    infoData[name] = val;
                                }

                                // 防止快速提交
                                if (isSendBtnClicked){
                                    return false;
                                }
                                isSendBtnClicked = true;

                                // 提交表单
                                var data = {sId: window.sId, appId: window.appId, CSRFToken: window.CSRFToken, info: infoData};
                                var opts = {
                                    error: function(re) {
                                        isSendBtnClicked = false;
                                    },
                                    success: function(re) {
                                        isSendBtnClicked = false;
                                        if (parseInt(re.errCode) !== 0) {
                                            return false;
                                        }

                                        jq.DIC.dialog({id:'userInfoForm'});
                                        // 成功通知应用
                                        module.exports._callback(params.cbName, {errCode:0, errMsg:'用户资料提交成功', info: infoData});
                                    }
                                };
                                jq.DIC.ajax('/app/userInfo/submit', data, opts);
                                return false;
                            });

                            // 用户取消支付
                            jq('#userInfoCancle').on('click', function() {
                                module.exports._callback(params.cbName, {errCode:3, errMsg:'用户取消'});
                                jq.DIC.dialog({id:'userInfoForm'});
                                return false;
                            });
                        },
                        // 关闭窗口
                        close: function() {
                            module.exports._callback(params.cbName, {errCode:3, errMsg:'用户取消'});
                            jq.DIC.dialog({id:'userInfoForm'});
                        }
                    });

                }
            });
        },
        _callback: function(cbName, data) {
            module.exports.appFrameWin.postMessage(
                {cbName: cbName, params:data},
                module.exports.validOrigin
            );
        },
        _wsqMsgCallReady: function() {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent("wsqMsgCallReady", true, true);
            document.dispatchEvent(evt);
        }
    };
    module.exports.init();
});
