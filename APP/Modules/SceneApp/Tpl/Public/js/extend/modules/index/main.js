define(function(require,exports,module){var $=require("lib/zepto/zepto"),$=require("./animationCloudBg"),$=require("./meteorShower"),$indexPages=$(".page-index");module.exports={init:function(){var $app=$("body");$indexPages.each(function(i,item){console.log("index init"),$page=$(item),function(){var $animationBox=$page.find(".m-animationBox"),appBgClass="appBg1";$animationBox.is(".m-animationCloudBg")?($animationBox.animationCloudBg(),appBgClass="appBg1"):$animationBox.is(".m-meteorShower")&&($animationBox.meteorShower({starCount:30,meteorCount:26}),appBgClass="appBg2")}(),$page.on("active",function(){console.log("index active")}).on("current",function(){console.log("index current")})});$(".link-to").on("click",function(){window.location.href=$(this).attr("data-url")})}}});