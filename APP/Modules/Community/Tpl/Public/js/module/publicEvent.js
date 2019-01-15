/**
 * @filename showpic
 * @description
 * 作者: jeffjzhang(jeffjzhang@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: publicEvent.js 33219 2014-09-23 09:54:38Z babuwang $
 **/

define('module/publicEvent', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        initNav: function() {

            jq('#listSite').on('click', function() {
                jq('.siteRank').show();
                jq('.userRank').hide();
                jq('#siteRule').show();
                jq('#userRule').hide();
            });

            jq('#listThread').on('click', function() {
                jq('.siteRank').hide();
                jq('.userRank').show();
                jq('#siteRule').hide();
                jq('#userRule').show();
            });
            //用户社区排行点击事件
            jq('.evtTopicTab a').on('click', function() {
                jq('.evtTopicTab a').removeClass('on');
                jq(this).addClass('on');
            });
            //用户排行下，全部、视频、图片点击事件
            jq('.evtThreadTab a').on('click', function() {
                jq('.evtThreadTab a').removeClass('on');
                jq(this).addClass('on');
            });
            //点击全部
            jq('#allThreadTab').on('click',function(){
                jq('#allThreadList').show();
                jq('#weishiThreadList').hide();
                jq('#picThreadList').hide();
            });
            //点击视频
            jq('#weishiThreadTab').on('click',function(){
                jq('#allThreadList').hide();
                jq('#weishiThreadList').show();
                jq('#picThreadList').hide();
            });
            //点击图片
            jq('#picThreadTab').on('click',function(){
                jq('#allThreadList').hide();
                jq('#weishiThreadList').hide();
                jq('#picThreadList').show();
            });
        },

        // init
        init: function() {
            module.exports.initNav();

            initLazyload('.warp img');

            if (jq('.detailShow')[0].scrollHeight > 26) {
                jq('.detailShow a.incoA').show();
                jq.DIC.touchState('.detailShow');
                // 展开内容点击绑定
                jq('.titShow').on('click', function() {
                    var obj = jq(this).find('.detailShow');
                    var aObj = obj.find('a');
                    if (aObj.hasClass('iBtnOn1')) {
                        aObj.removeClass('iBtnOn1');
                        obj.css('height', '26px');
                        obj.removeClass('dsOn');
                    } else {
                        aObj.addClass('iBtnOn1');
                        aObj.parent().css('height', 'auto');
                        obj.addClass('dsOn');
                    }
                });
            }
            // 第二个活动的排行入口点击
            jq('#pEventImg').on('click', function() {
                var link = jq(this).attr('data-link') || '';
                if (link) {
                    jq.DIC.open(link);
                }
                return false;
            });
            // see one
            jq('.listShow').on('click', '.showImg', function() {
                var sId = jq(this).parent().attr('sId'),
                tId = jq(this).parent().attr('tId'),
                parentId = jq(this).parent().attr('parentId'),
                url = DOMAIN + sId + '/v/' + parentId + '/t/' + tId;
                jq.DIC.open(url);
            });

            jq('.listShow').on('click', '.showSum', function() {
                var sId = jq(this).parent().attr('sId'),
                tId = jq(this).parent().attr('tId'),
                url = DOMAIN + sId;
                jq.DIC.open(url);
            });

            // like
            jq.DIC.touchState('.like', 'incoBg', '.warp');
            jq('.listShow').on('click', '.like', function(e) {
                e.stopPropagation();
                
                var thisObj = jq(this);
                if(thisObj.children('i').attr('class') == 'praise') {
                    return;
                }
                var sId = thisObj.parent().parent().attr('sId'),
                    tId = thisObj.parent().parent().attr('tId'),
                    parentId = thisObj.parent().parent().attr('parentId');
                var opts = {
                    'success': function(result) {
                        if (result.errCode == 0 && result.data && result.data.likeNumber) {
                            jq.DIC.likeTips(thisObj);
                            thisObj.html('<i class="praise"></i>' + result.data.likeNumber);
                        }
                    },
                    'noShowLoading' : true,
                    'noMsg' : true
                }
                jq.DIC.ajax('/' + sId + '/like', {'tId':tId, 'parentId': parentId, 'CSRFToken':CSRFToken}, opts);
            });
        }
    };
    module.exports.init();
});
