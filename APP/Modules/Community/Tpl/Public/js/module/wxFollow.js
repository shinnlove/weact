define('module/wxFollow', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        wxFollowTips : function() {
            if (!isWX || !isWeixinLink || !jq.DIC.getQuery('source')) {
                return false;
            }
            if (window.localStorage) {
                var noFirstFollowSite = localStorage.getItem('noFirstFollowSite_'+sId) || false;
                if (noFirstFollowSite) {
                    return false;
                }
                localStorage.setItem('noFirstFollowSite_'+sId, true);
            }
            var wxFollowForm = '<div class="popLayer br"><div class="tipsCon"><p class="f16 c6">喜欢我们的微社区么？喜欢就关注我们的公众账号吧，下次可以直接进入微社区咯。</p><div class="btWrap"><a href="javascript:;" class="btnCancel br" id="wxFollowBtnCancel">取消</a><a href="javascript:;" class="btnFocus br" id="wxFollowBtnFocus">关注</a></div></div><div class="popBarImg"><img src="http://dzqun.gtimg.cn/quan/images/popBg.png" alt="" /></div></div>';
            jq.DIC.dialog({
                content:wxFollowForm,
                id:'wxFollowForm',
                isHtml:true,
                isMask:true,
                // 弹出后执行
                callback:function() {
                    jq('#wxFollowBtnCancel').on('click', function() {
                        jq.DIC.dialog({id:'wxFollowForm'});
                    });

                    jq('#wxFollowBtnFocus').on('click', function() {
                        jq.DIC.dialog({id:'wxFollowForm'});
                        jq.DIC.reload('/' + sId + '/wxLink');
                    });

                }
            });
        }
    };
});
