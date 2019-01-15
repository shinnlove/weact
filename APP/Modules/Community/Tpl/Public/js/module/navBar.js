/**
 * @filename navBar
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: navBar.js 32585 2014-09-03 01:19:00Z kamichen $
 **/

define('module/navBar', ['module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var followSite = require('module/followSite');
    module.exports = {
        init: function() {
            // 底部按钮模态
            jq('#navBtn').bind('touchstart', function() {
                jq(this).addClass('on');
            });
            jq('#navBtn').bind('touchend', function() {
                jq(this).removeClass('on');
            });

            if(jq('.bNav')) {
                jq('.bNav').slideDown();
            }

            // 关注
            jq('#followButton, .followButton').on('click', function() {
                if (!isAppBar || !authUrl) {
                    var thisObj = jq(this);
                    followSite.followSite.call(thisObj, 'site_index');
                }
            });

            // 应用宝逻辑
            var reapp = /qqdownloader\/([^\s]+)/i,
                re = /^http(s)?:\/\/(([^\/\.]+\.)*)?(qq|qzone)\.com(\/.*)*$/,
                isAppBar = reapp.test(navigator.userAgent);
            if (isAppBar && !re.test(document.referrer)) {
                jq('#goback').hide();
            }

            // 邀请
            jq('.inviteQQ').on('click', function() {
                if (inviteUrl.length > 1) {
                    inviteUrl = inviteUrl.replace(/&amp;/g, '&');
                    jq.DIC.reload(inviteUrl);
                }
            });

            // 微信关注引导
            jq('#followWXBtn').on('click', function() {
                jq.DIC.reload('/' + sId + '/wxLink');
            });

            //点击发帖时如果已选中标签，则传递参数
            jq('.bottomBar').on('click', '.publish', function(event){
                var $this = jq(this);
                if(jq.DIC.getQuery('filterType')){
                    var filerType = jq.DIC.getQuery('filterType');
                   if($this.hasClass('publish')){
                      event.preventDefault();
                      window.location.href = $this.attr('href') + '?filterType=' + filerType;
                   };
                }
            });

            // 看帖页后退按纽
            jq('#goback').on('click', function() {
                if (typeof(WeixinJSBridge) != 'undefined') {
                    WeixinJSBridge.call('showOptionMenu');
                }

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
                        if (!sId && isWX) {
                            return false;
                        }
                        if (isMQ) {
                            jq.DIC.showLoading(null, null, true);
                            jq.DIC.goBack('/my/sites');
//                            jq.DIC.reload('/my/sites');
                            return false;
                        }
                        jq.DIC.showLoading(null, null, true);
                        jq.DIC.reload('/' + sId);
                    }
                }
            });

            jq('#publishThread').on('click', function() {
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

        }
    };
    module.exports.init();
});
