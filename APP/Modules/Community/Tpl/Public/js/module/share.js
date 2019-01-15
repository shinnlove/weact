/**
 * @filename share
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: share.js 32466 2014-08-29 07:24:58Z vissong $
 **/

define('module/share', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function() {
            var shareDesc = window.shareDesc;
            var shareTitle = window.shareTitle || jq(document).attr('title');
            var shareImgUrl = window.shareImgUrl || window.siteLogo;

            // 帖子详情页特殊处理
            if (window.tId > 0 && window.parentId < 1) {
                // 分享内容
                var content = jq.DIC.trim(jq('.dCon').text()) || jq.DIC.trim(jq('.detailShow').text());
                if (content) {
                    shareDesc = content.substr(0, content.indexOf('。') + 1);
                    if (jq.DIC.mb_strlen(shareDesc) < 60 || jq.DIC.mb_strlen(shareDesc) > 105) {
                        shareDesc = jq.DIC.mb_cutstr(content, 105);
                    }
                }

                var imgObj = jq('.dImg');
                if (imgObj) {
                    shareImgUrl = jq('.dImg').first().attr('data-original') || jq('.dImg').first().attr('src');;
                }
            }

            // 页面分享初始化微信分享
            if (isWX) {
                require.async('module/wxshare', function(wxshare) {
                    wxshare.initWXShare({
                        'sId': sId,
                        'tId': tId,
                        'img': shareImgUrl,
                        'desc': shareDesc,
                        'title': shareTitle,
                        'callback': function(re) {}
                    });
                });
            } else if (isMQ) {
                // 右上角分享定制
                if (typeof(mqq.data.setShareInfo) != 'undefined') {
                    mqq.data.setShareInfo({
                        'share_url': window.shareUrl,
                        'title': shareTitle,
                        'desc': shareDesc,
                        'image_url': shareImgUrl
                    }, function() {});
                }
            }

            // 分享按纽事件绑定
            jq('.warp, #bottomBar').on('click', '.shareBtn', function(e) {
                e.stopPropagation(e);
                var that = jq(this);
                module.exports.shareBind.call(that);
                return false;
            });
        },
        shareBind: function() {
            var that = jq(this);
            var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
            if (!isMQ && !isWX && MQQBrowser && MQQBrowser[1] >= '5.2') {
                require.async('lib/QQBrowser', function(qb) {
                    // mq浏览器api
                    if (typeof window.x5 !== 'undefined') {
                        window.x5.getAppShowType(function(re) {
//                            if (!re.isTab && !re.isLight) {
                                var shareUrl = that.attr('sUrl') || window.shareUrl,
                                shareTitle = that.attr('sTitle') || window.shareTitle;
                                shareDesc = that.attr('sDesc') || window.shareDesc,
                                shareImgUrl = that.attr('sImg') || window.shareImgUrl;
                                window.x5.share({
                                    'url': shareUrl,
                                    'title': shareTitle,
                                    'description': shareDesc,
                                    'img_url': shareImgUrl,
                                    'img_title': ''
                                }, '', '');
                                return false;
//                            }
                            // 轻应用走空间分享
//                            module.exports.shareJump.call(that);
                        }, '');
                    }
                });
                return false;
            }
            module.exports.shareJump.call(that);
        },
        shareJump: function() {
            var that = jq(this);
            // 非手Q和微信中 有空间分享链接的直接走空间分享
            if (!isMQ && !isWX) {
                var qqShareLink = that.attr('_qq');
                var qzoneShareLink = that.attr('_qzone');
                // 存在链接 接管默认按纽点击
                if (qqShareLink || qzoneShareLink) {
                    if (qqShareLink && qzoneShareLink) {
                        var html = template.render('tmpl_share', {qqShareLink: qqShareLink, qzoneShareLink: qzoneShareLink});
                        jq.DIC.dialog({
                            id: 'share',
                            content: html,
                            isHtml: true,
                            isMask: true,
                            callback: function() {
                                jq('#fwin_mask_share, #cancleShare, .shareLayer a').on('click', function() {
                                    jq.DIC.dialog({id: 'share'});
                                });
                            }
                        });
                    } else {
                        var jumpUrl = qqShareLink || qzoneShareLink;
                        jq.DIC.reload(jumpUrl);
                    }
                    return false;
                }
            }
            // 否则走跳到新页面
            var link = that.attr('data-link') || '';
            if (link) {
                jq.DIC.reload(link);
                return false;
            } else {
            // 最后为当前页直接弹出浮层提示
                var tmpl = template.render('tmpl_pageTip', {'msg':'喜欢这个页面，请点击右上角图标分享'});
                jq.DIC.dialog({
                    id: 'shareMask',
                    top:0,
                    content: tmpl,
                    isHtml: true,
                    isMask: true,
                    callback: function() {
                        jq('.g-mask').on('click', function() {
                            jq.DIC.dialog({id:'shareMask'});
                            jq('#showShare').hide();
                            return false;
                        });
                    }
                });
                jq('#showShare').show();
                scroll(0,0);
            }
            return false;
        }
    };
    module.exports.init();
});
