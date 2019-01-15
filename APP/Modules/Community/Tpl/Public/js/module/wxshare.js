/**
 * @filename viewthread
 * @description
 * 作者: vissong
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: wxshare.js 32492 2014-08-29 10:22:43Z danvinhe $
 **/

define('module/wxshare', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        appId: '',				//appid不能写死
        reportShareStat: function(shareTo) {
            if (!shareTo || sId < 1) {
                return false;
            }

            var url = '/' + sId + '/share';
            var data = {
                'tId': tId,
                'CSRFToken': CSRFToken,
                'shareTo': shareTo
            };

            if (window.parentId) {
                data.parentId = window.parentId;
            }

            if (window.appId) {
                data.appId = window.appId;						//如果页面上有appid，使用页面的appid
            }

            jq.DIC.ajax(url, data);
        },
       initWXShare: function(opts) {
            if (!isWX) {
                return false;
            }
            opts = opts || {};
            opts.url = opts.url || window.location.href;
            opts.url = opts.url.indexOf('?') === -1 ? opts.url + '?' : opts.url;
            opts.url = opts.url.replace(/\&?code\=[^\&]+/g, '');
            opts.url = opts.url.replace(/\&?action=share/, '');

            opts.desc = opts.desc || window.shareDesc;
            opts.title = opts.title || window.shareTitle;

            if (typeof WeixinJSBridge != 'undefined') {
                module.exports.bindWXMenu(opts);
            } else {
                jq(document).bind('WeixinJSBridgeReady', function() {
                    module.exports.bindWXMenu(opts);
                });
            }
        },
        bindWXMenu: function(opts) {
            WeixinJSBridge.on('menu:share:appmessage', function(argv){
                var url = opts.url;
                url = url.replace(/\&source\=[^\&]+/g, '') + '&source=appmessage';
                WeixinJSBridge.invoke('sendAppMessage',{
                    'appid': module.exports.appId,
                    'img_url': opts.img,
                    'img_width': opts.width || '120',
                    'img_height': opts.height || '120',
                    'link': url,
                    'desc': opts.desc,
                    'title': opts.title
                }, function(res){(opts.callback)(res);});

                module.exports.reportShareStat('appmessage');
            });
            WeixinJSBridge.on('menu:share:timeline', function(argv){
                var url = opts.url;
                url = url.replace(/\&source\=[^\&]+/g, '') + '&source=timeline';
                //(dataForWeixin.callback)();
                WeixinJSBridge.invoke('shareTimeline',{
                    'img_url': opts.img,
                    'img_width': opts.width || '120',
                    'img_height': opts.height || '120',
                    'link': url,
                    'desc': opts.desc,
                    'title': opts.desc
                }, function(res){(opts.callback)(res);});

                module.exports.reportShareStat('timeline');
            });
            WeixinJSBridge.on('menu:share:weibo', function(argv){
                var url = opts.url;
                url = url.replace(/\&source\=[^\&]+/g, '') + '&source=weibo';
                WeixinJSBridge.invoke('shareWeibo',{
                    'img_url': opts.img,
                    'img_width': opts.width || '120',
                    'img_height': opts.height || '120',
                    'link': url,
                    'url': url,
                    'content': opts.desc,
                    'desc': opts.desc,
                    'title': opts.title
                }, function(res){(opts.callback)(res);});

                module.exports.reportShareStat('weibo');
            });
            WeixinJSBridge.on('menu:share:facebook', function(argv){
                var url = url.replace(/\&source=[^\&]+/g, '') + '&source=facebook';
                (dataForWeixin.callback)();
                WeixinJSBridge.invoke('shareFB',{
                    'img_url': opts.img,
                    'img_width': opts.width || '120',
                    'img_height': opts.height || '120',
                    'link': url,
                    'desc': opts.desc,
                    'title': opts.title
                }, function(res){(opts.callback)();});
            });
        }
    }
});

