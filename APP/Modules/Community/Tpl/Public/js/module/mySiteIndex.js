define('module/mySiteIndex', ['module/gps', 'module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var gps = require('module/gps');
    var followSite = require('module/followSite');
    module.exports =  {
        init : function () {

            // 检查地理信息
            var get_gps = jq.DIC.getcookie('get_gps');
            if (!get_gps) {
                gps.getLocation(function(latitude, longitude) {
                    jq.DIC.ajax('/checkcity', {
                        'CSRFToken' : CSRFToken,
                        'latitude' : latitude,
                        'longitude' : longitude
                    }, {
                        'noShowLoading' : true,
                        'noMsg': true,
                        'success' : function(re) {
                        },
                        'complete' : function() {
                            jq.DIC.setcookie('get_gps', '1', 2592000);
                        }
                    });
                });
            }

            // 对热门微社区处理
            initLazyload('.interestBox img');

            jq('.warp').on('click', '#findSites li a', function() {
                var thisObj = jq(this);
                thisObj.off('click');
                thisObj.addClass('iBtnOn');
                followSite.followSite.call(thisObj);
                setTimeout(function(){
                    thisObj.removeClass('iBtnOn');
                }, 50);
            });
            jq('.warp').on('click', '#followdSites li a', function() {
                var thisObj = jq(this);
                thisObj.off('click');
                thisObj.addClass('iBtnOn');
                followSite.unfollowSite.call(thisObj);
                setTimeout(function(){
                    thisObj.removeClass('iBtnOn');
                }, 50);
            });
            jq('.warp').on('click', '#hotSiteList li a', function() {
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
                    },
                };
                followSite.followSite.call(thisObj, 'my_sites', opts);

            });
            jq('.warp').on('click', '#newSiteList li a', function() {
                var thisObj = jq(this);
                thisObj.off('click');
                thisObj.addClass('iBtnOn');
                followSite.followSite.call(thisObj);
                setTimeout(function(){
                    thisObj.removeClass('iBtnOn');
                }, 50);
            });

            jq('#findSites li h4, #findSites li i, #findSites li .subTitle').on('click', function() {
                sId = jq(this).parent().attr('sid');
                jq.DIC.reload('/' + sId);
            });
            jq('.warp').on('click', '#followdSites li h4, #followdSites li img, #followdSites li .subTitle', function() {
                sId = jq(this).parent().attr('sid');
                jq.DIC.reload('/' + sId);
            });
            jq('#hotSiteList li h4, #hotSiteList li i, #hotSiteList li .subTitle').on('click', function() {
                sId = jq(this).parent().attr('sid');
                jq.DIC.reload('/' + sId);
            });
            jq('#newSiteList li h4, #newSiteList li i, #newSiteList li .subTitle').on('click', function() {
                sId = jq(this).parent().attr('sid');
                jq.DIC.reload('/' + sId);
            });
        }
    };
    module.exports.init();
});
