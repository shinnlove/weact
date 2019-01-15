/**
 * @filename viewthread
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: profile.js 32961 2014-09-16 10:15:36Z jeffjzhang $
 **/

define('module/profile', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function() {
            // 同步用户资料
            jq('#syncInfo').on('click', function() {
                var opts = {
                    'noShowLoading' : true
                }
                jq.DIC.ajax('/my/info/sync', {'CSRFToken' : CSRFToken}, opts);
                return false;
            });

            // 赞排行点击
            jq('.tRank').on('click', function() {
                var thisObj = jq(this);
                var sId = thisObj.attr('sId');
                jq.DIC.reload('/likedrank/' + sId);
                return false;
            });

            jq('.container li, .personMy .needsclick').on('click', function() {
                var link = jq(this).attr('data-link') || '';
                if (!link) {
                    return false;
                }
                jq.DIC.reload(link);
                return false;
            });
            //荣誉点击事件
            jq('.evtHonor').on('click', function() {
                var link = jq(this).attr('data-link') || '',
                    reg = /(http:\/\/|https:\/\/)((\w|=|\?|\.|\/|&|-)+)/g;
                if (!link) {
                    return false;
                }
                if(!reg.test(link)){
                    link = 'http://'+link;
                }
                jq.DIC.reload(link);
                return false;
            });

            //点击头像编辑
            jq('.evtEditProfile').on('click', function() {
                FastClick.attach(this);
                var link = jq(this).attr('data-link') || '';
                if (!link) {
                    return false;
                }
                jq.DIC.reload(link);
                return false;
            });

            //全局活动点击
            jq('.evtTogetherEvent').on('click', function () {
                var that = jq(this),
                    link = that.attr('data-link') || '';
                if (link) {
                    jq.DIC.open(link);
                    return false;
                }
            });
        }
    };
    module.exports.init();
});
