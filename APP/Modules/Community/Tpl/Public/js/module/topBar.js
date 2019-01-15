/**
 * @filename topBar
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2014-04-09 14:56:03
 * 修改记录:
 *
 * $Id: topBar.js 32585 2014-09-03 01:19:00Z kamichen $
 **/

define('module/topBar', ['module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var followSite = require('module/followSite');
    module.exports = {
        init: function() {
            // 关注
            jq('#followButton, .followButton').on('click', function() {
                var thisObj = jq(this);
                followSite.followSite.call(thisObj, 'site_index');
            });

            var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
            if (MQQBrowser && MQQBrowser[1] >= '5.2') {
                require.async('lib/QQBrowser', function(qb) {
                    // q浏览器api
                    if (typeof window.x5 !== 'undefined') {
                        // 获取应用打开场景
                        window.x5.getAppShowType(function(re) {
                            // 轻应用中打开且无referrer
                            if (re.isLight && !document.referrer) {
                                jq('#goback').hide();
                            }
                        }, '');
                    }
                });
            }
            var re = /^http(s)?:\/\/((mq|wx)\.wsq\.qq\.com)(\/.*)*/;
            var qqReg= /^http(s)?:\/\/(([^\/\.]+\.)*)?(qq|qzone)\.com(\/.*)*$/;
            // 看帖页后退按纽
            jq('#goback').on('click', function() {

                var _referer = jq.DIC.getQuery('_referer');
                if (_referer) {
                    jq.DIC.reload(decodeURIComponent(_referer));
                    return false;
                }

                if (isForceReload == 1) {
                    if (!sId && isWX) {
                        return false;
                    }
                    if (!sId && isMQ) {
                        jq.DIC.showLoading(null, null, true);
                        jq.DIC.reload('/my/sites');
                        return false;
                    }
                    jq.DIC.showLoading(null, null, true);
                    jq.DIC.reload('/' + sId);
                } else {
                    if (document.referrer) {
                        history.go(-1);
                    } else {
                        if (!sId) {
                            return false;
                        }
                        if (isMQ) {
                            jq.DIC.showLoading(null, null, true);
                            jq.DIC.reload('/portal');
                            return false;
                        }
                        jq.DIC.showLoading(null, null, true);
                        jq.DIC.reload('/' + sId);
                    }
                }
            });
            //点击发帖时如果已选中标签，则传递参数
            jq('.topBar').on('click', '.qPublish', function(event){
                var $this = jq(this);
                if(jq.DIC.getQuery('filterType')){
                    var filerType = jq.DIC.getQuery('filterType');
                   if($this.hasClass('qPublish')){
                      event.preventDefault();
                      window.location.href = $this.attr('href') + '?filterType=' + filerType;
                   };
                }
            });

            jq('#qPublish').on('click', function() {
                var sId = jq(this).attr('sId');
                if (!sId) return;
                var url = jq(this).attr('href');
                //不加入社区也可发帖，自动加入社区修改
                if(jq.DIC.getQuery('filterType')){
                    var filerType = jq.DIC.getQuery('filterType');
                    url = url + '?filterType=' + filerType;
                }

                jq.DIC.reload(url);
                
                return false;
            });

                /*
            jq('#mqOption').on('click', function() {
                if (sId) {
                    var url = '/' + sId + '/label';
                    var opts = {
                        'success': function(re) {
                            var status = parseInt(re.errCode);
                            if (status != 0) {
                                return false;
                            }

                            var tmpl = template.render('tmpl_sideBar', re.data);
                            jq.DIC.dialog({content:tmpl, id:'sideBar', isMask:true, isHtml:true, callback:function() {
                                var sideBar = jq('.sideBar');
                                if (!sideBar.is(':visible')) {
                                    sideBar.show();
                                    sideBar.animate({right:0},'normal',function(){});
                                    //                    } else {
                                    //                        sideBar.animate({right:'-190px'},'normal',function(){sideBar.hide();});
                            }
                            jq('#fwin_mask_sideBar').on('click', function() {
                                var thisObj = jq(this);
                                sideBar.animate({right:'-190px'},'normal',function(){jq.DIC.dialog({id:'sideBar'});});
                            });
                            }});
                        }
                    };
                    jq.DIC.ajax(url, '', opts);
                }
                var html = template.render('optionMenu', {'inviteUrl': inviteUrl, 'sId': sId, 'isLiked': isLiked, 'isWX': isWX, 'isMQ': isMQ, 'newMsgCount': newMsgCount});
                var opts = {
                    'id': 'optionMenu',
                    'isHtml':true,
                    'isMask':true,
                    'content':html,
                    'callback': function() {
                        jq('.g-mask,.manageLayer').on('click', function(e) {
                            jq.DIC.dialog({id:'optionMenu'});
                        });

                        jq.DIC.touchState('#optionLayer li', 'on');

                        // 同步用户资料
                        jq('#syncInfo').on('click', function() {
                            var opts = {
                                'noShowLoading' : true
                            }
                            jq.DIC.ajax('/my/info/sync', {'CSRFToken' : CSRFToken}, opts);
                        });
                    }
                };
                jq.DIC.dialog(opts);
            });
//            jq.DIC.touchState('#mqOption');
                */

        }

    };
    module.exports.init();
});
