(function(){
	/**
 * 功能:图片惰性加载。在需要图片惰性加载的页面底部载入。再将图片加上class=“lazy”。并将图片的真实地址放置在图片的自定义属性dataimg中。
 * @author haunghm
 * @version 1.0.0
 * @dependencies jquery 或者 zepto
 */
var lazyLoad = {
	
	init: function() {
		var that = this;
		that.onerrorImgUrl = "";//图片加载失败用什么图片替换
		that.srcStore      = "data-original";   //图片真实地址存放的自定义属性
		that.class         = "lazy";      //惰性加载的图片需要添加的class
		that.sensitivity   = 5;           //该值越小，惰性越强（加载越少）      
		
		minScroll = 5,
		slowScrollTime = 200,
		ios = navigator.appVersion.match(/(iPhone\sOS)\s([\d_]+)/),
		isIos = ios && !0 || !1,
		//isIos=true;
		isoVersion = isIos && ios[2].split("_");
	
		isoVersion = isoVersion && parseFloat(isoVersion.length > 1 ? isoVersion.splice(0, 2).join(".") : isoVersion[0], 10),
		isIos = that.isPhone = isIos && isoVersion < 6;
		//isIos = that.isPhone = isIos;
		if (isIos) {
			
			var startSyAndTime,
			setTimeOut;
			$(window).on("touchstart",function() {
				startSyAndTime = {
					sy: window.scrollY,
					time: Date.now()
				},
				setTimeOut && clearTimeout(setTimeOut)
			}).on("touchend",function(e) {
				if (e && e.changedTouches) {
					var subtractionY = Math.abs(window.scrollY - startSyAndTime.sy);
					if (subtractionY > minScroll) {
						var subtractionTime = Date.now() - startSyAndTime.time;
						setTimeOut = setTimeout(function() {
							that.changeimg(),
							startSyAndTime = {},
							clearTimeout(setTimeOut),
							setTimeOut = null
						},
						subtractionTime > slowScrollTime ? 0: 200)
					}
				} else {
					that.changeimg();
				}
			}).on("touchcancel", function() {
				setTimeOut && clearTimeout(setTimeOut),
				startSyAndTime = {}
			})
		} else {
			$(window).on("scroll", function() {
				that.changeimg();
			});
		}
		setTimeout(function() {
			that.trigger();				
		},90);
		
	},
	trigger: function() {
		var that = this;
		eventType = that.isPhone && "touchend" || "scroll";
		that.imglist = $('img.'+that.class+'');
		$(window).trigger(eventType);
	},
	changeimg: function() {
		function loadYesOrno(img) {
			var windowPageYOffset = window.pageYOffset,
			windowPageYOffsetAddHeight = windowPageYOffset + window.innerHeight,
			imgOffsetTop = img.offset().top;
			return imgOffsetTop >= windowPageYOffset && imgOffsetTop - that.sensitivity <= windowPageYOffsetAddHeight;
		}
		function loadImg(img, index) {
			
			var imgUrl = img.attr(that.srcStore);
			img.attr("src", imgUrl);
			img[0].onload || (img[0].onload = function() {
				$(this).removeClass(that.class).removeAttr(that.srcStore),
				that.imglist[index] = null,
				this.onerror = this.onload = null
			},
			img[0].onerror = function() {
				this.src = that.onerrorImgUrl,
				$(this).removeClass(that.class).removeAttr(that.srcStore),
				that.imglist[index] = null,
				this.onerror = this.onload = null
			})
		}
		if( this.imglist != undefined ){
			var that = this;
			that.imglist.each(function(index, val) {
				if (!val) return;
				var img = $(val);
				if (!loadYesOrno(img)) return;
				if (!img.attr(that.srcStore)) return;
				loadImg(img, index);
			});
		}
		
	}
};
lazyLoad.init();
	
}());

