/**
 * @filename showMessage
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: viewthread.js 21874 2013-09-11 07:55:29Z danvinhe $
 **/

define('module/showMessage', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        showLoginBox: function() {
            var opt = {
                domain: 'qq.com',
                daid: 0,
                appid: '710044201',
                link_target: 'blank',
                target: 'top',
                hide_title_bar: 1,
                hide_close_icon: 0,
                style: 11,
                mask: true,
                draggable: true,
                width: 620,
                height: 366,
                s_url: encodeURIComponent(location.href),
                qlogin_auto_login: '0'  // 自动登录标志
            };
            document.domain = opt.domain;

            var ptparam = [], url = 'http://ui.ptlogin2.' + opt.domain + '/cgi-bin/login', html, c;
            jq.each(opt, function(n) {
                if (c = opt[n]) {
                    ptparam.push(n + '=' + c);
                }
            });
            url = url + '?' + ptparam.join('&');

            html = '<div style="text-align:center"><iframe id="login_iframe" width="' + (opt.width - 2) + '" height="' + opt.height + '" frameborder="0" allowtransparency="yes" src="' + url + '"></iframe></di>';

            var opts = {
                'isHtml':true,
                'isMask':true,
                'content':html,
            };
            jq.DIC.dialog(opts);

            window.ptlogin2_onClose = function() {
                jq('#fwin_mask_tips').remove();
                jq('#fwin_dialog_tips').remove();
            };
        },
        logoutConfirm: function() {
            if (!confirm("您确定要退出吗?")) {
                return;
            }

            for (var i = 0, ar = ["uin", "skey", "zzpaneluin", "zzpanelkey", "prvk"], l = ar.length; i < l; i++) {
                QZFL.cookie.del(ar[i]);
            }

            window.location.reload();
        },
        init: function() {
            // 应用宝sdk
            var resdk = /qqdownloader\/([^\/]+\/sdk)/i;
            if (authUrl && resdk.test(navigator.userAgent)) {
                return false;
            }

            if (jumpURL) {
                jQuery.DIC.reload(jumpURL);
            }

            if (!jumpURL && showLogin) {
                module.exports.showLoginBox();
            }
        }
    };
    module.exports.init();
});
