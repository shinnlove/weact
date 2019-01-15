define('module/recommend', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        init : function () {
            // 对热门微社区处理
            initLazyload('.interestBox img');

            jq('#hotSiteList li h4, #hotSiteList li i, #hotSiteList li .subTitle').on('click', function() {
                sId = jq(this).parent().attr('sId');
                jq.DIC.reload('/' + sId + '?adtag=gexinghua.fx.click.site&position=rec');
            });
        }
    };
    module.exports.init();
});
