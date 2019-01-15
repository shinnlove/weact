/**
 *  社区管理
 *
 *  作者: kamichen
 *  创建时间: 2013-12-31
 *
 *  $Id: followlist.js 25932 2014-03-04 10:51:08Z danvinhe $
 */

define('module/followlist', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        init : function () {
            jq('.warp').on('touchstart', '#followdSites li a', function() {
                var thisObj = jq(this);
                if (tabType) {
                    module.exports.pushAction.call(thisObj);
                } else {
                    module.exports.followAction.call(thisObj);
                }
            });

            jq('.warp').on('click', '#tabFList', function() {
                module.exports.switchToList.call();
            });

            jq('.warp').on('click', '#tabFPush', function() {
                module.exports.switchToPush.call();
            });

            jq('.warp').on('click', '#followdSites li h4, #followdSites li img, #followdSites li .subTitle', function() {
                if(typeof jq(this).parent().attr('sid') !== 'undefined') {
                    sId = jq(this).parent().attr('sid');
                } else {
                    sId = jq(this).parent().parent().attr('sid');
                }
                jq.DIC.reload('/' + sId);
            });
        },
        switchToList : function () {
            jq('.infoSendTips').hide();
            tabType = 0;
            jq('#tabFPush').removeClass('on');
            jq('#tabFList').addClass('on');
            jq('.tabList').show();
            jq('.tabPush').hide();
        },
        switchToPush : function () {
            var noFirstVisit = false;
            if (window.localStorage) {
                noFirstVisit = localStorage.getItem('noFirstVisit') || false;
                localStorage.setItem('noFirstVisit', true);
            }
            if (!noFirstVisit) {
                jq('.infoSendTips').show();
            }
            tabType = 1;
            jq('#tabFList').removeClass('on');
            jq('#tabFPush').addClass('on');
            jq('.tabList').hide();
            jq('.tabPush').show();
        },
        pushAction : function () {
            var sId, url;
            var thisObj = jq(this);

            var liObj = thisObj.parent().eq(0);
            sId = liObj.attr('sid');
            fDisabled = liObj.attr('fdisabled');

            jq.DIC.ajax('/my/push/manage', {'CSRFToken' : CSRFToken, 'sId' : sId,'actionType' : fDisabled != "yes" ? '1' : '0'}, {
                'noShowLoading': true,
                'noMsg':true,
                'success' : function(result) {
                    var status = parseInt(result.errCode);
                    if (status == 0) {
                        if (fDisabled != "yes") {
                            thisObj.addClass('p_bg');
                            thisObj.children().attr("style", "right:25px;");
                            liObj.attr("fdisabled", "yes");
                        } else {
                            thisObj.removeClass('p_bg');
                            thisObj.children().attr("style", "right:1px;");
                            liObj.attr("fdisabled", "no");
                        }
                    }
                }
            });
        },


        followAction : function () {
            var sId, url;
            var thisObj = jq(this);

            var liObj = thisObj.parent().eq(0);
            sId = liObj.attr('sid');
            fStatus = liObj.attr('fstatus');

            if (fStatus != "yes") {
                url = '/' + sId + '/follow';
            } else {
                url = '/' + sId + '/unfollow';
            }

            if (fStatus != 'yes') {

                if (isAppBar) {
                    jq.DIC.ajax(url, {'CSRFToken' : CSRFToken}, {
                        'noShowLoading': true,
                        'noMsg':true,
                        'success' : function(result) {
                            var status = parseInt(result.errCode);
                            if (status == 0) {
                                thisObj.addClass('iBtnOn');
                                thisObj.removeClass('iInco').addClass('iSucc');
                                liObj.attr("fstatus", "yes");
                            }
                        }
                    });
                } else {
                    jq.DIC.dialog({
                        'content':'加入社区后将会定期接收来自社区的精华话题推送，确认加入？',
                        'okValue':'加入',
                        'cancelValue':'取消',
                        'isMask':true,
                        'ok':function () {
                            jq.DIC.ajax(url, {'CSRFToken' : CSRFToken}, {
                                'noShowLoading': true,
                            'noMsg':true,
                            'success' : function(result) {
                                var status = parseInt(result.errCode);
                                if (status == 0) {
                                    thisObj.addClass('iBtnOn');
                                    thisObj.removeClass('iInco').addClass('iSucc');
                                    liObj.attr("fstatus", "yes");
                                }
                            }
                            });
                        }
                    });
                }
            } else {
                jq.DIC.ajax(url, {'CSRFToken' : CSRFToken}, {
                    'noShowLoading': true,
                    'noMsg':true,
                    'success' : function(result) {
                        var status = parseInt(result.errCode);
                        if (status == 0) {
                            thisObj.removeClass('iBtnOn');
                            thisObj.removeClass('iSucc').addClass('iInco');
                            liObj.attr("fstatus", "no");
                        }
                    }
                });
            }
        }
    };
    module.exports.init();
});
