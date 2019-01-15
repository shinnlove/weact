
var utils = {
	getQuery: function(a) {
		var b = arguments[1] || window.location.search,
			c = new RegExp("(^|&)" + a + "=([^&]*)(&|$)"),
			d = b.substr(b.indexOf("?") + 1).match(c);
		return null != d ? d[2] : ""
	},
	namespace: function(str) {
		for (var arr = str.split(","), i = 0, len = arr.length; len > i; i++) for (var arrJ = arr[i].split("."), parent = {}, j = 0, jLen = arrJ.length; jLen > j; j++) {
			var name = arrJ[j],
				child = parent[name];
			0 === j ? eval("(typeof " + name + ')==="undefined"?(' + name + '={}):"";parent=' + name) : parent = parent[name] = "undefined" == typeof child ? {} : child
		}
	},
	urlReplace: function(a, b) {
		var c = b.url || location.search.substring(1),
			d = new RegExp("(^|&)(" + a + "=)([^&]*)"),
			e = b.content ? b.content : "";
		return c.replace(d, function(a, b, c) {
			return e ? b + c + e : ""
		})
	},
	showBubble: function(a, b) {
		if (a) {
			b = b || 1500;
			var c = $("#bubble");
			c.css("opacity", 1), c.hasClass("qb_none") || c.html(a), c.html(a).removeClass("qb_none"), setTimeout(function() {
				c.animate({
					opacity: 0
				}, 500, "ease", function() {
					$(this).addClass("qb_none").removeAttr("style")
				})
			}, b)
		}
	},
	showConfirm: function() {
		var a = {
			sureNode: $("#notice-sure"),
			cancelNode: $("#notice-cancel"),
			contentNode: $("#notice-content"),
			dialogNode: $("#message-notice")
		};
		return function(b) {
			function c() {
				f.sureFn.call(this, arguments), e()
			}
			function d() {
				f.cancelFn.call(this, arguments), e()
			}
			function e() {
				a.contentNode.empty(), a.sureNode.html("确定").off("click", c), a.cancelNode.html("取消").off("click", d), a.dialogNode.addClass("qb_none")
			}
			var f = {
				describeText: "",
				sureText: "确定",
				cancelText: "取消",
				showNum: 2,
				sureFn: function() {
					return !0
				},
				cancelFn: function() {
					return !0
				}
			};
			$.extend(f, b), a.dialogNode.hasClass("qb_none") && (a.dialogNode.removeClass("qb_none"), a.sureNode.on("click", c), a.cancelNode.on("click", d), 1 == f.showNum && a.cancelNode.addClass("qb_none"), a.sureNode.html(f.sureText), a.cancelNode.html(f.cancelText), a.contentNode.html(f.describeText))
		}
	}(),
	ajaxReq: function(a, b, c) {
		var d = {
			type: "GET",
			url: "",
			data: "",
			dataType: "html",
			timeout: 5e3
		};
		$.extend(d, a), c || (c = function() {}), $.ajax({
			type: d.type,
			url: d.url,
			data: d.data,
			dataType: d.dataType,
			success: function(a) {
				"json" == d.dataType && (a.errCode = parseInt(a.errCode, 10)), b(a)
			},
			error: c
		})
	},
	showAjaxErr: function(a, b) {
		utils.showBubble(a.msgType ? a.errMsg : b)
	},
	strReplace: function(a, b) {
		var c = a;
		for (var d in b) {
			var e = new RegExp("{#" + d + "#}", "g");
			c = c.replace(e, b[d])
		}
		return c
	},
	cssProperty: function() {
		var a = document.documentElement,
			b = "modernizr";
		return {
			injectStyle: function(c, d) {
				var e, f, g = document.createElement("div"),
					h = document.body,
					i = h || document.createElement("body");
				return e = ["&#173;", '<style id="s', b, '">', c, "</style>"].join(""), g.id = b, (h ? g : i).innerHTML += e, i.appendChild(g), h || (i.style.background = "", a.appendChild(i)), f = d(g, c), h ? g.parentNode.removeChild(g) : i.parentNode.removeChild(i), !! f
			},
			pSupport: function(b) {
				for (var c = a.style, d = "Webkit Moz O ms".split(" "), e = b.charAt(0).toUpperCase() + b.substr(1), f = (e + " " + d.join(e + " ") + e).split(" "), g = null, h = 0, i = f.length; i > h; h++) if (f[h] in c) {
					g = f[h];
					break
				}
				return g
			},
			has3d: function() {
				var b = !! this.pSupport("perspective");
				return b && "webkitPerspective" in a.style && this.injectStyle("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}", function(a) {
					b = 9 === a.offsetLeft && 3 === a.offsetHeight
				}), b
			}
		}
	}(),
	initStock : function() {
	var a = this;
	$.each(window.availSku, function(b) {
		a.propList.push(b)
	})
}, initProperty : function() {
	var a = this,
		b = window.sku,
		c = $("#prop-tpl").html(),
		d = '<span data-value="{#key#}" class="property {#className#}">{#optionName#}</span>',
		e = [],
		f = [],
		g = {};
	b.forEach(function(b, h) {
		var i = [];
		b.pList.forEach(function(a) {
			var c, e = b.pName + ":" + a;
			c = d.replace(/{#key#}/, e).replace(/{#optionName#}/, a).replace(/{#className#}/, 1 === b.pList.length ? "current" : ""), i.push(c)
		}), 1 == b.pList.length ? (g.key || (g.key = b.pName + ":" + b.pList[0], g.index = h, g.propName = b.pName), a.matchProperty[h] = b.pName + ":" + b.pList[0], a.selectedCount++) : f.push(b.pName), e.push(c.replace(/{#index#}/, h).replace(/{#pName#}/g, b.pName).replace(/{#pList#}/, i.join("")))
	}), a.errorMsg = "请选择" + f.join("/"), $("#prop-list").html(e.join("")), a.pNodeList = $(".mod_property"), g.key && a.loopProp(g.key, g.index, g.propName), g = null
}
};

$.extend(loopImage.prototype, {
	initEl: function() {
		var a = this.config,
			b = a.param;
		b.scw = a.pageWidth || $(window).width(), a.autoAdapt && b.outContainer.width(b.scw), b.container = b.outContainer.find(a.contentWrap), b.contentList = b.container.find(a.contentTag), b.prevNode = a.prev && b.outContainer.find(a.prev), b.nextNode = a.next && b.outContainer.find(a.next), b.statusNode = a.statusWrap && b.outContainer.find(a.statusWrap), b.statusList = a.statusWrap && b.statusNode.children(), b.counts = b.contentList.length, b.eleWidth = b.contentList.width();
		var c = utils.cssProperty.has3d();
		return this.transform = utils.cssProperty.pSupport("transform"), this.tfpre = c ? "translate3d(" : "translate(", this.tfsufix = c ? ",0px)" : ")", this
	},
	init: function() {
		var a = this.config,
			b = a.param,
			c = a.cycle && b.counts > 1 ? b.counts + 2 : b.counts;
		return this.current = a.initIndex = a.initIndex > b.counts ? b.counts - 1 : a.initIndex < 0 ? 0 : a.initIndex, a.onInit(), a.setWidth && b.contentList.width(b.eleWidth), b.statusStepWidth = a.statusNow && parseInt(b.eleWidth / b.counts, 10), a.statusNow && b.statusList.width(b.statusStepWidth), b.container.css({
			width: parseInt((b.eleWidth + a.margin) * c, 10),
			left: a.offset ? parseInt((b.scw - b.eleWidth) / 2, 10) : "0px",
			"-webkit-backface-visibility": "hidden",
			"-webkit-transform": this.tfpre + -this.current * (1 * b.eleWidth + 1 * this.config.margin) + "px,0px" + this.tfsufix
		}), b.container.parent().css({
			"-webkit-transform": "translate3d(0,0,0)"
		}), b.contentList.css({
			"-webkit-transform": this.tfpre + "0px,0px" + this.tfsufix
		}), this.config.statusNow && b.statusList.css({
			"-webkit-transform": this.tfpre + this.current * b.statusStepWidth + "px,0px" + this.tfsufix,
			"-webkit-backface-visibility": "hidden"
		}), this.initImages(), b.counts <= 1 && a.loadImg ? (this.loadSingleImg(this.getEle(this.current)), void 0) : (this.direct = "left", a.autoGen ? this.generateStatus() : "", a.cycle ? this.cloneNode() : "", this.updateStatus(), this.bindEvent(), a.auto && this.processAuto(), void 0)
	},
	getEle: function(a) {
		return this.config.param.contentList.eq(a)
	},
	generateStatus: function() {
		for (var a = this.config, b = a.param, c = [], d = 0; d < b.counts; d++) c.push("<" + a.statusTag + " class=" + a.autoGenClass + "></" + a.statusTag + ">");
		b.statusNode.html(c.join("")), b.statusList = b.statusNode.children(), a.tabs = b.statusList, c = null
	},
	bindEvent: function() {
		var a = this.config.param,
			b = a.container[0];
		b.addEventListener("touchstart", this, !1), b.addEventListener("touchmove", this, !1), b.addEventListener("touchend", this, !1), b.addEventListener("touchcancel", this, !1), b.addEventListener("webkitTransitionEnd", this, !1), a.prevNode && a.prevNode.on("click", $.proxy(this.prev, this)), a.nextNode && a.nextNode.on("click", $.proxy(this.next, this))
	},
	handleEvent: function(a) {
		switch (a.type) {
		case "touchstart":
			this.eventStart(a);
			break;
		case "touchmove":
			this.eventMove(a);
			break;
		case "touchend":
		case "touchcancel":
			this.eventEnd(a);
			break;
		case "webkitTransitionEnd":
			this.processEnd()
		}
	},
	prev: function(a) {
		var b = $(a.target);
		if (!b.hasClass(this.config.btnDisClass)) return this.direct = "right", this.stop(), b.addClass(this.config.btnTouchClass), this.setPrevPage(), !1
	},
	next: function(a) {
		var b = $(a.target);
		if (!b.hasClass(this.config.btnDisClass)) return this.direct = "left", this.stop(), b.addClass(this.config.btnTouchClass), this.setNextPage(), !1
	},
	setPrevPage: function() {
		var a = parseInt(this.current, 10),
			b = this.config.cycle,
			c = parseInt(this.config.param.counts, 10);
		switch (a) {
		case -1:
			a = c - 2;
			break;
		case c:
			a = -1;
			break;
		case 0:
			a = b ? -1 : 0;
			break;
		default:
			a -= 1
		}
		this.current = a, this.processScroll()
	},
	setNextPage: function() {
		var a = parseInt(this.current, 10),
			b = this.config.cycle,
			c = parseInt(this.config.param.counts, 10);
		switch (a) {
		case c:
			a = 1;
			break;
		case -1:
			a = c;
			break;
		case c - 1:
			a = b ? c : c - 1;
			break;
		default:
			a += 1
		}
		this.current = a, this.processScroll()
	},
	eventStart: function(a) {
		var b = this.config,
			c = b.param;
		this.sp = this.getPosition(a);
		var d = -1 == this.current ? c.counts - 1 : this.current == c.counts ? 0 : this.current;
		this.curOffset = -(d * (c.eleWidth + b.margin)), this.statusOffset = b.statusNow && c.statusStepWidth * d, this.stop()
	},
	eventMove: function(a) {
		if (null != this.curOffset) {
			var b = this.getPosition(a),
				c = b.x - this.sp.x;
			this.disx = c, Math.abs(c) > Math.abs(b.y - this.sp.y) && (a.preventDefault(), this.setOffset(c, 0))
		}
	},
	eventEnd: function(a) {
		null != this.curOffset && (this.disx > this.config.leastDis ? (a.preventDefault(), this.setPrevPage(), this.direct = "right") : this.disx < -this.config.leastDis ? (a.preventDefault(), this.setNextPage(), this.direct = "left") : (this.setOffset(0, this.config.aniTime), this.config.auto && this.processAuto()), this.disx = null, this.curOffset = null, this.statusOffset = null, this.sp = null)
	},
	stop: function() {
		this.ptr && clearInterval(this.ptr), this.ptr = null
	},
	setOffset: function(a, b) {
		var c = this.config,
			d = c.param;
		d.container.css({
			"-webkit-transform": this.tfpre + (this.curOffset + a) + "px,0px" + this.tfsufix,
			"-webkit-transition": b + "ms"
		}), c.statusNow && d.statusList.css({
			"-webkit-transform": this.tfpre + (this.statusOffset + -a / d.counts) + "px,0px" + this.tfsufix,
			"-webkit-transition": b + "ms"
		})
	},
	processScroll: function() {
		var a = this,
			b = this.current,
			c = this.config.param;
		// =======b这个变量就是li标签的index，所以只要在这里控制li标签即可 start=======
		var bullets = $(".mod-bullet__box");
		bullets.find("li").removeClass("mod-bullet__item_current"); 		// 移除所有其他的li亮点
		bullets.find("li:eq("+b+")").addClass("mod-bullet__item_current"); 	// 为当前的li添加亮点
		// =======b这个变量就是li标签的index，所以只要在这里控制li标签即可 end=======
		c.container.css({
			"-webkit-transform": this.tfpre + -b * (1 * c.eleWidth + 1 * this.config.margin) + "px,0px" + this.tfsufix,
			"-webkit-transition": this.config.aniTime + "ms"
		}), this.config.statusNow && setTimeout(function() {
			var d = -1 == b ? c.counts - 1 : b == c.counts ? 0 : b;
			c.statusList.css({
				"-webkit-transform": a.tfpre + c.statusStepWidth * d + "px,0px" + a.tfsufix,
				"-webkit-transition": a.config.aniTime + "ms"
			})
		}, 0), this.config.loadImg && (this.config.loadNext ? this.loadNextImage(b) : this.loadSingleImg(c.contentList.eq(b)))
	},
	processEnd: function() {
		var a = this.config,
			b = a.param;
		this.updateStatus(), this.current == b.counts ? this.moveElement() : -1 == this.current && b.container.css({
			"-webkit-transform": this.tfpre + -(1 * b.eleWidth + 1 * a.margin) * (b.counts - 1) + "px,0px" + this.tfsufix,
			"-webkit-transition": "0ms"
		}), a.auto && this.processAuto()
	},
	moveElement: function() {
		for (var a = this.config.param, b = this.getEle(a.counts + 1), c = a.contentList, d = 1, e = c.length - 2; e > d; d++) c.eq(d).remove().insertBefore(b);
		if (c.eq(0).remove().insertBefore(b), this.transform) {
			var f = parseInt(a.contentList.css("margin-left"), 10);
			a.container.css({
				"-webkit-transform": "translate3d(" + -f + "px,0px,0px)",
				"-webkit-transition": ""
			})
		}
	},
	updateStatus: function() {
		var a = this.config,
			b = a.cycle,
			c = this.current,
			d = a.param;
		c == d.counts ? c = 0 : -1 == c && (c = d.counts - 1), b || 0 != c ? d.prevNode && d.prevNode.removeClass(a.btnDisClass) : d.prevNode && d.prevNode.addClass(a.btnDisClass), b || c != d.counts - 1 ? d.nextNode && d.nextNode.removeClass(a.btnDisClass) : d.nextNode && d.nextNode.addClass(a.btnDisClass), d.statusList && d.statusList.eq(c).addClass(a.statusClass).siblings().removeClass(a.statusClass), this.config.onProcess(c)
	},
	getPosition: function(a) {
		var b = a.changedTouches ? a.changedTouches[0] : a;
		return {
			x: b.pageX,
			y: b.pageY
		}
	},
	cloneNode: function() {
		var a = this.config,
			b = a.param;
		b.container.append(b.contentList.eq(0).clone());
		var c = b.contentList.eq(b.counts - 1).clone();
		c.css({
			position: "relative",
			left: -((b.eleWidth + a.margin) * (b.counts + 2))
		}), b.container.append(c), b.contentList = b.container.children()
	},
	processAuto: function() {
		var a = this;
		a.ptr && (clearInterval(a.ptr), a.ptr = null), a.ptr = setInterval(function() {
			a.config.cycle || a.current != a.config.param.counts - 1 ? a.setNextPage() : a.stop()
		}, a.config.autoTime)
	},
	loadSingleImg: function(a) {
		if (!a.attr("l")) {
			var b = this.config,
				c = a.find("img");
			return $.each(c, function(c, d) {
				var d = $(d),
					e = d.attr(b.imgProp);
				b.setWidth && d.attr("width", b.param.eleWidth), e && (d.attr("src", e).removeAttr(b.imgProp), a.attr("l", !0))
			}), this
		}
	},
	initImages: function() {
		var a = this.config,
			b = a.param,
			c = b.scw;
		if (a.autoAdapt && a.loadImg && a.offset) for (var d = c - (parseInt((c - b.eleWidth) / 2, 10) + b.eleWidth), e = Math.ceil(d / (b.eleWidth + 2 * a.margin)) + a.initIndex + 1, f = 0; e > f; f++) {
			var g = this.getEle(f);
			this.loadSingleImg(g)
		} else this.loadSingleImg(this.getEle(this.current)), a.cycle && (this.loadSingleImg(this.getEle(b.counts - 1)), 0 != this.current && this.loadSingleImg(b.contentList.eq(this.current - 1))), a.loadNext && this.loadNextImage(this.current)
	},
	loadNextImage: function(a) {
		var b, c = this.config,
			d = c.param;
		b = -1 == a ? d.counts - 2 : a == d.counts ? 1 : "right" == this.direct ? a - 1 : a + 1;
		var e = d.contentList.eq(b);
		this.loadSingleImg(e)
	},
	destroy: function() {
		var a = this.config.param,
			b = a.container[0];
		b.removeEventListener("touchstart", this, !1), b.removeEventListener("touchmove", this, !1), b.removeEventListener("touchend", this, !1), b.removeEventListener("toushcancel", this, !1), b.removeEventListener("WebkitTransitionEnd", this, !1), a.prevNode && a.prevNode.off("click", $.proxy(this.prev, this)), a.nextNode && a.nextNode.off("click", $.proxy(this.next, this))
	},
	setCurrent: function(a) {
		a = 0 > a ? 0 : a >= this.config.param.counts ? this.config.param.counts - 1 : a, this.current = a, this.processScroll()
	}
}), loopImage.nodeList = [], $.fn.scroll = function(a) {
	return this.each(function(b, c) {
		loopImage.nodeList.push(new loopImage(a, c))
	}), {
		getIndex: function(a) {
			return loopImage.nodeList[a]
		}
	}
}

$.fn.scroll = function(a) {
	return this.each(function(b, c) {
		loopImage.nodeList.push(new loopImage(a, c))
	}), {
		getIndex: function(a) {
			return loopImage.nodeList[a]
		}
	}
}

function loopImage(a, b) {
	this.config = {
		initIndex: 0,
		contentWrap: "",
		contentTag: "li",
		statusWrap: "",
		statusTag: "i",
		statusClass: "current",
		autoGenClass: "",
		statusNow: !1,
		prev: "",
		next: "",
		btnDisClass: "disabled",
		btnTouchClass: "",
		aniTime: 300,
		autoTime: 5e3,
		autoAdapt: !1,
		setWidth: !1,
		pageWidth: "",
		auto: !1,
		cycle: !1,
		autoGen: !0,
		margin: 0,
		loadImg: !1,
		imgProp: "back_src",
		loadNext: !1,
		offset: !1,
		leastDis: 20,
		cont: [],
		tabs: [],
		onInit: function() {
			return !0
		},
		onProcess: function() {
			return !0
		}
	}, a && ($.extend(this.config, a), this.config.param = {}, this.config.param.outContainer = $(b), 0 != this.config.param.outContainer.length && this.initEl().init())
}