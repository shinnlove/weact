/**
 * @filename portalMySites
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2014-04-09 14:56:03
 * 修改记录:
 *
 * $Id$
 **/

define('module/portalMySites', ['module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var follow = require('module/followSite');
    module.exports = {
        init: function(divId) {
            template.helper('isObjEmpty', function (obj) {
                if (jq.isEmptyObject(obj)) {
                    return true;
                } else {
                    return false;
                }
            });

            var sid = jq.DIC.getQuery('sid');
            // todo
            var url = '/my/sites?qzone=1';
            var opts = {
                'requestMode': 'abort',
                'requestIndex': 'portal',
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if(status == 0) {
                        var tmpl = template.render('tmpl_mySites', re.data);
                        jq('#' + divId).html(tmpl);

                        // 热门微社区点击加入
                        jq('#' + divId).on('click', '#hotSiteList li a', function() {
                            var thisObj = jq(this);
                            thisObj.off('click');
                            var opts = {
                                'before': function() {
                                    thisObj.addClass('iBtnOn');
                                },
                                'after': function() {
                                    setTimeout(function(){
                                        thisObj.removeClass('iBtnOn');
                                    }, 50);
                                }
                            };
                            follow.followSite.call(thisObj, 'my_sites', opts);
                        });

                        // 热门微社区点击进入
                        jq('#hotSiteList li h4, #hotSiteList li i, #hotSiteList li .subTitle').on('click', function() {
                            var sId = jq(this).parent().attr('sid');
                            jq.DIC.reload('/' + sId);
                        });

                        // 我的微社区点击
                        jq('#' + divId).on('click', '#followdSites li h4, #followdSites li img, #followdSites li .subTitle', function() {
                            var thisObj = jq(this);
                            thisObj.off('click');
                            var sId = thisObj.parent().attr('sid');
                            jq.DIC.reload('/' + sId);
                        });

                        // 查看全部点击
                        jq('#findAll').on('click', function() {
                            jq('.topicTab a[data-module="FindSite"]').click();
                        });
                    }
                },
                'noShowLoading' : true,
                'noMsg' : true
            }
            jq.DIC.ajax(url, '', opts);
        }
    };
});
