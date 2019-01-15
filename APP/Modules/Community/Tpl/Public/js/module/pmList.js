/**
 * @file pmList.js
 * 搜索模块
 *
 * @author jinhuiguo, jinhuiguo@tencent.com
 * @version
 * @date 2014-9-9
 *
 */

define('module/pmList', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        nextStart: 0,
        isLoading: false,
        isLoadingNew: true,
        init: function() {
            //事件
            module.exports.bindEvents();
            module.exports.getPmList('new');
        },
        bindEvents: function() {

            jq(document).on('click', '.messageListCont', function(e){
                var thisObj = jq(this);

                //模拟手指触摸效果
                jq.DIC.touchStateNow(thisObj);

                //对话列表点击
                if(thisObj.hasClass('messageListCont')){
                    var targetuid = thisObj.attr('targetuid');
                    var url = '/my/pm/dialog?targetUid='+targetuid;
                    jq.DIC.reload(url);
                };

            });

            //滚屏自动加载
            var loadingPos = jq('#loadNextPos');
            var scrollPosition = jq(window).scrollTop();
            jq(window).scroll(function() {
                if (scrollPosition < jq(window).scrollTop()) {
                    if (!module.exports.isLoading && module.exports.isLoadingNew) {
                        var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                        if (loadingObjTop <= 100) {
                            module.exports.isLoading = true;
                            jq('#loadNext').stop(true, true).slideDown('fast');
                            
                            module.exports.getPmList();
                        }
                    }
                }
                scrollPosition = jq(window).scrollTop();
            });
        },
        getPmList: function(type){
            var messageList = jq('#messageList');
            var targetUid = jq.DIC.getQuery('targetUid') || 0;
            var url = '/my/pm';

            if(type != 'new'){
                url += '?start=' + module.exports.nextStart;
            }
            var opts = {
                complete: function() {
                    jq('#refreshWait').slideUp();
                },
                success: function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0 || !re.data) {
                        jq('#loadNext').stop(true, true).hide();
                        module.exports.isLoading = false;
                        return false;
                    }
                    //如果结果为空，显示已加载全部
                    if (jq.DIC.isObjectEmpty(re.data.pmList)) {
                        module.exports.isLoadingNew = false;
                        jq('#loadNext').stop(true, true).hide();
                        if(type != 'new'){
                            jq('#showAll').show();
                        }
                        return false;
                    }

                    re.data.uId = window.uId;
                    var tmpl = template.render('tmpl_pmList', re.data);
                    if(type == 'new'){
                        messageList.html(tmpl);
                    }else{
                        messageList.append(tmpl);
                    }
                    
                    module.exports.nextStart = re.data.nextStart;

                    module.exports.isLoading = false;
                },
                error: function() {
                    //module.exports.isLoading = false;
                },
                'noShowLoading' : true
            };
            jq.DIC.ajax(url, '', opts);
        }
    };
    module.exports.init();
});
