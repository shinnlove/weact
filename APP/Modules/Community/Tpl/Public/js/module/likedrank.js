/**
 * @filename viewthread
 * @description
 * 作者: jeffjzhang(jeffjzhang@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: likedrank.js 29288 2014-05-29 12:38:23Z yixizhou $
 **/

define('module/likedrank', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function() {

            jq('.header').on('click', function() {
                jq.DIC.reload('/' + sId);
            });
            // user redirect
            jq('.rankBox dd').on('click', function() {
                var uId = jq(this).attr('_uId'),
                    url = DOMAIN + 'profile/' + uId;
                jq.DIC.reload(url);
            });
        }
    };
    module.exports.init();
});
