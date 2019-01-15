/**
 * @filenamea imageviewCommon
 * @description
 * 作者: vissong
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: imageviewCommon.js 33263 2014-09-24 08:40:27Z jinhuiguo $
 **/

define('module/imageviewCommon', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function(rule, subRule) {
            subRule = subRule || 'img';
            // 点击看大图
            jq('.warp').on('click', rule, function(e) {
                var isHaveGif = false;
                e.stopPropagation();
                FastClick.attach(this);
                var pics = [],
                    picClass = []; //图片样式

                jq(this).parent().find(subRule).each(function(e, i) {
                    pics.push(jq(i).attr('data-original').replace(/\/(150|300|600)(\?.+)?/g, '\/1280$2'));
                    picClass.push(jq(i).attr('class'));
                    //如果列表包含GIF图片，不使用微信的看图
                    if(jq(i).attr('data-original').indexOf('_GIF') != -1){
                        isHaveGif = true;
                    };
                });

                if (isWX && isFriendSite != 1 && !isHaveGif) {
                    WeixinJSBridge.invoke('imagePreview', {
                        "current": jq(this).find('img').attr('data-original').replace(/\/(150|300|600)(\?.+)?/g, '\/1280$2'),
                        "urls": pics
                    });
                } else if (!isAppBar)  {
                    //手Q图片放大
                    if (!jq('#imageView')[0]) {
                        jq('body').append('<div id="imageView" class="slide-view" style="display:none;"></div>');
                    }
                    var index = 0;
                    //确定当前查看的图片是第几张
                    if(pics.length > 1) {
                        var imgSrc = jq(this).find('img').attr('data-original').replace(/\/(150|300|600)(\?.+)?/g, '\/1280$2');
                        for(var i = 0; i < pics.length; i++){

                            if(imgSrc == pics[i]){
                                index = i;
                                break;
                            }
                        }
                    }
                    // 使用seajs的use接口，回调函数中初始化
                    seajs.use(['zepto','imageview'],function($, imageview){
                        window.$ = $;
                        var view = imageview.get('./init');
                        view.init(pics,index, null, picClass);
                    });
                }
            });
        }
    }
});
