$.fn.scrollImage = function(e) {
	function i(e) {
		var t = {
			showNum: 3,
			speed: 300,
			gapWidth: 0,
			imageAttr: "back_src",
			loadSee: !0,
			autoGen: !1,
			scrollTag: "ul",
			statusContainer: "",
			statusTag: "i",
			statusClassName: "active",
			scrollType: "full",
			curPage: 0,
			hideStatus: !1
		};
		return t.transform = utils.cssProperty.pSupport("transform"), t.transition = utils.cssProperty.pSupport("transition"), t.is3D = utils.cssProperty.has3d(), $.extend(t, e), r = t.loadSee, t
	}
	function s(e, t) {
		function h(t) {
			function h(t) {
				function g() {
					function l(e) {
						n.statusList && n.statusList.length > 0 && n.statusList.eq(e).addClass(n.statusClassName).siblings().removeClass(n.statusClassName)
					}
					function h() {
						t = Math.abs(t);
						var r = f - t;
						if (r < i) a(-(f - i), e);
						else {
							var s = t + i;
							if (s % o != 0) {
								var u = m == "add" ? Math.ceil(s / o) : Math.floor(s / o);
								a(-(u - n.showNum) * o, e)
							}
						}
					}
					var t = parseInt(c, 10) + d;
					if (t > 0) a(0, e), n.curPage = 0, l(0);
					else {
						var i = o * n.showNum,
							u = n.curPage;
						n.scrollType == "full" ? (m == "sub" ? u-- : u++, n.curPage = u = u < 0 ? 0 : u > n.pageSize - 1 ? n.pageSize - 1 : u, console.log(n.curPage), s % n.showNum != 0 ? u + 1 != n.pageSize ? (a(-i * n.curPage, e), l(n.curPage)) : (h(), l(n.pageSize - 1)) : (a(-i * n.curPage, e), l(n.curPage))) : h()
					}
					r[0].ontouchmove = null, r[0].ontouchend = null
				}
				var i = t.changedTouches[0],
					h = i.clientX,
					p = i.clientY,
					d = h - u,
					v = p - l;
				Math.abs(d) > Math.abs(v) && (t.preventDefault(), n.is3D ? r.css({
					"-webkit-transform": "translate3d(" + (c + d) + "px,0px,0px)",
					"-webkit-transition": ""
				}) : n.transform ? r.css({
					"-webkit-transform": "translateX(" + (c + d) + "px)",
					"-webkit-transition": ""
				}) : r.css("left", c + d));
				var m = d > 0 ? "sub" : "add";
				r[0].ontouchend = g
			}
			var i = t.changedTouches[0],
				u = i.clientX,
				l = i.clientY,
				c = r.attr("curLeft") * 1 || 0;
			r[0].ontouchmove = h
		}
		var n = e.config,
			r = e.node;
		n.loadSee && (n.top = r.offset().top);
		var i = r.children(),
			s = i.length,
			o = i.width() + n.gapWidth,
			f = o * s;
		n.pageSize = Math.ceil(s / n.showNum), n.pageSize <= 1 && n.hideStatus && e.statusBar.hide();
		if (n.autoGen && e.statusBar.length > 0 && n.pageSize > 1) {
			for (var l = 0; l < n.pageSize; l++) {
				var c = document.createElement(n.statusTag);
				l == 0 ? c.className = n.statusClassName : "", e.statusBar.append($(c))
			}
			n.statusList = e.statusBar.children()
		}
		n.eleWidth = o, n.childList = i, n.loadSee || setTimeout(function() {
			u(0, e)
		}, 1e3 * t), n.transform ? r.css({
			width: f,
			"-webkit-transform": "translateX(0px)"
		}) : r.css({
			width: f,
			left: 0
		}), r[0].ontouchstart = h
	}
	function o() {
		var e = window.pageYOffset + $(window).height();
		for (var n = 0, r = t.length; n < r; n++) {
			var i = t[n],
				s = i.node,
				o = i.config.top;
			if (!s.attr("scrolled") && e + 400 > o) {
				s.attr("scrolled", !0), u(0, i);
				break
			}
		}
	}
	function u(e, t) {
		var n = t.node;
		if (n.attr("loaded")) return;
		var r = t.config,
			i = r.showNum,
			s = r.childList,
			o = 0;
		e = Math.abs(e);
		if (e != 0) {
			var u = parseInt((n.attr("curLeft") || 0) / r.eleWidth, 10);
			i = parseInt(e / r.eleWidth), o = r.showNum + u, i += r.showNum
		}
		i == s.length && n.attr("loaded", !0);
		for (; o < i; o++) {
			var a = s.eq(o),
				f = a.find("img");
			f.attr("back_src") && (f.attr("src", f.attr("back_src")), f.removeAttr("back_src"))
		}
	}
	function a(e, t) {
		var n = t.node,
			r = t.config;
		u(e, t), n.attr("curLeft", e), r.transform ? n.css({
			"-webkit-transform": "translateX(" + e + "px)",
			"-webkit-transition": r.speed / 1e3 + "s ease-out",
			"-webkit-backface-visibility": "hidden"
		}) : r.transition ? n.css({
			left: e,
			"-webkit-transition": "left " + r.speed / 1e3 + "s ease-out",
			"-webkit-backface-visibility": "hidden"
		}) : n.animate({
			left: e
		}, r.speed), r.is3D && setTimeout(function() {
			n.css({
				"-webkit-transform": "translate3d(" + e + "px,0px,0px)",
				"-webkit-transition": "",
				"-webkit-backface-visibility": "hidden"
			})
		}, r.speed)
	}
	var t = [],
		n = $(this),
		r = !0;
	e = e ? e : {}, n.each(function(n) {
		var r = i(e);
		t[n] = {
			node: $(this).find(r.scrollTag),
			config: r
		}, r.autoGen && r.statusContainer && (t[n].statusBar = $(this).find(r.statusContainer)), s(t[n], n)
	}), r && window.addEventListener("scroll", o, !1)
}