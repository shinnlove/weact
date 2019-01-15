function localStore(a) {
	this.key = a
}
function getCmdyCount() {
	var a = $(".icon_number_bubble");
	0 != a.length && utils.ajaxReq({
		url: window.basePath + "/cn/cmdy/count.xhtml",
		dataType: "json",
		data: {
			t: +new Date
		}
	}, function(b) {
		!b.errCode && b.data > 0 ? (b.data = b.data >= 100 ? "N" : b.data, a.html(b.data).removeClass("qb_none")) : a.addClass("qb_none")
	})
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
function setLogisData(a, b, c) {
	var d, e = a.data.traceInfos.length,
		f = $("#wuliu-detail"),
		g = $("#wuliu-tpl").html();
	if (a && 0 == a.retCode && a.data.traceInfos.length > 0) {
		d = '<div class="mod_bb"><div class="mod_color_weak">' + a.data.traceInfos[e - 1].time + '<span class="mod_sup_em">NEW</span></div>' + a.data.traceInfos[e - 1].desc + "</div>";
		for (var h = e - 2; h >= 0; h--) d += utils.strReplace(g, a.data.traceInfos[h]);
		f.html(d), b()
	} else c(f)
}
function getlogisJsonCallback(a) {
	if (window.dealStatus) {
		var b = a.data.traceInfos.length;
		$("#wuliu-info-first").append('<div class="mod_bb"><div class="mod_arrow mod_arrow_right"><div class="mod_color_weak">' + a.data.traceInfos[b - 1].time + '<span class="mod_sup_em">NEW</span></div>' + a.data.traceInfos[b - 1].desc + "</div></div>"), setLogisData(a, function() {
			$("#wuliu-info-fail").addClass("qb_none"), $("#wuliu-detail-fail").addClass("qb_none")
		}, function() {
			$("#wuliu-info-fail").removeClass("qb_none"), $("#wuliu-detail-fail").removeClass("qb_none")
		})
	} else mobile.o2ocn.logistic.can.closeLoading(), mobile.o2ocn.logistic.can = null, setLogisData(a, function() {
		$("#wuliu-detail-fail").addClass("qb_none")
	}, function(a) {
		$("#wuliu-detail-fail").removeClass("qb_none"), a.addClass("qb_none")
	})
}
function canvasLoading() {
	this.ptr = "", this.cNode = "", this.canvasNode = document.getElementById("loading")
}!
function(a) {
	String.prototype.trim === a && (String.prototype.trim = function() {
		return this.replace(/^\s+/, "").replace(/\s+$/, "")
	}), Array.prototype.reduce === a && (Array.prototype.reduce = function(b) {
		if (void 0 === this || null === this) throw new TypeError;
		var c, d = Object(this),
			e = d.length >>> 0,
			f = 0;
		if ("function" != typeof b) throw new TypeError;
		if (0 == e && 1 == arguments.length) throw new TypeError;
		if (arguments.length >= 2) c = arguments[1];
		else for (;;) {
			if (f in d) {
				c = d[f++];
				break
			}
			if (++f >= e) throw new TypeError
		}
		for (; e > f;) f in d && (c = b.call(a, c, d[f], f, d)), f++;
		return c
	})
}();
var Zepto = function() {
		function a(a) {
			return "[object Function]" == M.call(a)
		}
		function b(a) {
			return a instanceof Object
		}
		function c(b) {
			var c, d;
			if ("[object Object]" !== M.call(b)) return !1;
			if (d = a(b.constructor) && b.constructor.prototype, !d || !hasOwnProperty.call(d, "isPrototypeOf")) return !1;
			for (c in b);
			return c === p || hasOwnProperty.call(b, c)
		}
		function d(a) {
			return a instanceof Array
		}
		function e(a) {
			return "number" == typeof a.length
		}
		function f(a) {
			return a.filter(function(a) {
				return a !== p && null !== a
			})
		}
		function g(a) {
			return a.length > 0 ? [].concat.apply([], a) : a
		}
		function h(a) {
			return a.replace(/::/g, "/").replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2").replace(/([a-z\d])([A-Z])/g, "$1_$2").replace(/_/g, "-").toLowerCase()
		}
		function i(a) {
			return a in z ? z[a] : z[a] = new RegExp("(^|\\s)" + a + "(\\s|$)")
		}
		function j(a, b) {
			return "number" != typeof b || B[h(a)] ? b : b + "px"
		}
		function k(a) {
			var b, c;
			return y[a] || (b = x.createElement(a), x.body.appendChild(b), c = A(b, "").getPropertyValue("display"), b.parentNode.removeChild(b), "none" == c && (c = "block"), y[a] = c), y[a]
		}
		function l(a, b) {
			return b === p ? r(a) : r(a).filter(b)
		}
		function m(b, c, d, e) {
			return a(c) ? c.call(b, d, e) : c
		}
		function n(a, b, c) {
			var d = a % 2 ? b : b.parentNode;
			d ? d.insertBefore(c, a ? 1 == a ? d.firstChild : 2 == a ? b : null : b.nextSibling) : r(c).remove()
		}
		function o(a, b) {
			b(a);
			for (var c in a.childNodes) o(a.childNodes[c], b)
		}
		var p, q, r, s, t, u, v = [],
			w = v.slice,
			x = window.document,
			y = {},
			z = {},
			A = x.defaultView.getComputedStyle,
			B = {
				"column-count": 1,
				columns: 1,
				"font-weight": 1,
				"line-height": 1,
				opacity: 1,
				"z-index": 1,
				zoom: 1
			},
			C = /^\s*<(\w+|!)[^>]*>/,
			D = [1, 3, 8, 9, 11],
			E = ["after", "prepend", "before", "append"],
			F = x.createElement("table"),
			G = x.createElement("tr"),
			H = {
				tr: x.createElement("tbody"),
				tbody: F,
				thead: F,
				tfoot: F,
				td: G,
				th: G,
				"*": x.createElement("div")
			},
			I = /complete|loaded|interactive/,
			J = /^\.([\w-]+)$/,
			K = /^#([\w-]+)$/,
			L = /^[\w-]+$/,
			M = {}.toString,
			N = {},
			O = x.createElement("div");
		return N.matches = function(a, b) {
			if (!a || 1 !== a.nodeType) return !1;
			var c = a.webkitMatchesSelector || a.mozMatchesSelector || a.oMatchesSelector || a.matchesSelector;
			if (c) return c.call(a, b);
			var d, e = a.parentNode,
				f = !e;
			return f && (e = O).appendChild(a), d = ~N.qsa(e, b).indexOf(a), f && O.removeChild(a), d
		}, t = function(a) {
			return a.replace(/-+(.)?/g, function(a, b) {
				return b ? b.toUpperCase() : ""
			})
		}, u = function(a) {
			return a.filter(function(b, c) {
				return a.indexOf(b) == c
			})
		}, N.fragment = function(a, b) {
			b === p && (b = C.test(a) && RegExp.$1), b in H || (b = "*");
			var c = H[b];
			return c.innerHTML = "" + a, r.each(w.call(c.childNodes), function() {
				c.removeChild(this)
			})
		}, N.Z = function(a, b) {
			return a = a || [], a.__proto__ = arguments.callee.prototype, a.selector = b || "", a
		}, N.isZ = function(a) {
			return a instanceof N.Z
		}, N.init = function(b, e) {
			if (!b) return N.Z();
			if (a(b)) return r(x).ready(b);
			if (N.isZ(b)) return b;
			var g;
			if (d(b)) g = f(b);
			else if (c(b)) g = [r.extend({}, b)], b = null;
			else if (D.indexOf(b.nodeType) >= 0 || b === window) g = [b], b = null;
			else if (C.test(b)) g = N.fragment(b.trim(), RegExp.$1), b = null;
			else {
				if (e !== p) return r(e).find(b);
				g = N.qsa(x, b)
			}
			return N.Z(g, b)
		}, r = function(a, b) {
			return N.init(a, b)
		}, r.extend = function(a) {
			return w.call(arguments, 1).forEach(function(b) {
				for (q in b) b[q] !== p && (a[q] = b[q])
			}), a
		}, N.qsa = function(a, b) {
			var c;
			return a === x && K.test(b) ? (c = a.getElementById(RegExp.$1)) ? [c] : v : 1 !== a.nodeType && 9 !== a.nodeType ? v : w.call(J.test(b) ? a.getElementsByClassName(RegExp.$1) : L.test(b) ? a.getElementsByTagName(b) : a.querySelectorAll(b))
		}, r.isFunction = a, r.isObject = b, r.isArray = d, r.isPlainObject = c, r.inArray = function(a, b, c) {
			return v.indexOf.call(b, a, c)
		}, r.trim = function(a) {
			return a.trim()
		}, r.uuid = 0, r.map = function(a, b) {
			var c, d, f, h = [];
			if (e(a)) for (d = 0; d < a.length; d++) c = b(a[d], d), null != c && h.push(c);
			else for (f in a) c = b(a[f], f), null != c && h.push(c);
			return g(h)
		}, r.each = function(a, b) {
			var c, d;
			if (e(a)) {
				for (c = 0; c < a.length; c++) if (b.call(a[c], c, a[c]) === !1) return a
			} else for (d in a) if (b.call(a[d], d, a[d]) === !1) return a;
			return a
		}, r.fn = {
			forEach: v.forEach,
			reduce: v.reduce,
			push: v.push,
			indexOf: v.indexOf,
			concat: v.concat,
			map: function(a) {
				return r.map(this, function(b, c) {
					return a.call(b, c, b)
				})
			},
			slice: function() {
				return r(w.apply(this, arguments))
			},
			ready: function(a) {
				return I.test(x.readyState) ? a(r) : x.addEventListener("DOMContentLoaded", function() {
					a(r)
				}, !1), this
			},
			get: function(a) {
				return a === p ? w.call(this) : this[a]
			},
			toArray: function() {
				return this.get()
			},
			size: function() {
				return this.length
			},
			remove: function() {
				return this.each(function() {
					null != this.parentNode && this.parentNode.removeChild(this)
				})
			},
			each: function(a) {
				return this.forEach(function(b, c) {
					a.call(b, c, b)
				}), this
			},
			filter: function(a) {
				return r([].filter.call(this, function(b) {
					return N.matches(b, a)
				}))
			},
			add: function(a, b) {
				return r(u(this.concat(r(a, b))))
			},
			is: function(a) {
				return this.length > 0 && N.matches(this[0], a)
			},
			not: function(b) {
				var c = [];
				if (a(b) && b.call !== p) this.each(function(a) {
					b.call(this, a) || c.push(this)
				});
				else {
					var d = "string" == typeof b ? this.filter(b) : e(b) && a(b.item) ? w.call(b) : r(b);
					this.forEach(function(a) {
						d.indexOf(a) < 0 && c.push(a)
					})
				}
				return r(c)
			},
			eq: function(a) {
				return -1 === a ? this.slice(a) : this.slice(a, +a + 1)
			},
			first: function() {
				var a = this[0];
				return a && !b(a) ? a : r(a)
			},
			last: function() {
				var a = this[this.length - 1];
				return a && !b(a) ? a : r(a)
			},
			find: function(a) {
				var b;
				return b = 1 == this.length ? N.qsa(this[0], a) : this.map(function() {
					return N.qsa(this, a)
				}), r(b)
			},
			closest: function(a, b) {
				for (var c = this[0]; c && !N.matches(c, a);) c = c !== b && c !== x && c.parentNode;
				return r(c)
			},
			parents: function(a) {
				for (var b = [], c = this; c.length > 0;) c = r.map(c, function(a) {
					return (a = a.parentNode) && a !== x && b.indexOf(a) < 0 ? (b.push(a), a) : void 0
				});
				return l(b, a)
			},
			parent: function(a) {
				return l(u(this.pluck("parentNode")), a)
			},
			children: function(a) {
				return l(this.map(function() {
					return w.call(this.children)
				}), a)
			},
			siblings: function(a) {
				return l(this.map(function(a, b) {
					return w.call(b.parentNode.children).filter(function(a) {
						return a !== b
					})
				}), a)
			},
			empty: function() {
				return this.each(function() {
					this.innerHTML = ""
				})
			},
			pluck: function(a) {
				return this.map(function() {
					return this[a]
				})
			},
			show: function() {
				return this.each(function() {
					"none" == this.style.display && (this.style.display = null), "none" == A(this, "").getPropertyValue("display") && (this.style.display = k(this.nodeName))
				})
			},
			replaceWith: function(a) {
				return this.before(a).remove()
			},
			wrap: function(a) {
				return this.each(function() {
					r(this).wrapAll(r(a)[0].cloneNode(!1))
				})
			},
			wrapAll: function(a) {
				return this[0] && (r(this[0]).before(a = r(a)), a.append(this)), this
			},
			unwrap: function() {
				return this.parent().each(function() {
					r(this).replaceWith(r(this).children())
				}), this
			},
			clone: function() {
				return r(this.map(function() {
					return this.cloneNode(!0)
				}))
			},
			hide: function() {
				return this.css("display", "none")
			},
			toggle: function(a) {
				return (a === p ? "none" == this.css("display") : a) ? this.show() : this.hide()
			},
			prev: function() {
				return r(this.pluck("previousElementSibling"))
			},
			next: function() {
				return r(this.pluck("nextElementSibling"))
			},
			html: function(a) {
				return a === p ? this.length > 0 ? this[0].innerHTML : null : this.each(function(b) {
					var c = this.innerHTML;
					r(this).empty().append(m(this, a, b, c))
				})
			},
			text: function(a) {
				return a === p ? this.length > 0 ? this[0].textContent : null : this.each(function() {
					this.textContent = a
				})
			},
			attr: function(a, c) {
				var d;
				return "string" == typeof a && c === p ? 0 == this.length || 1 !== this[0].nodeType ? p : "value" == a && "INPUT" == this[0].nodeName ? this.val() : !(d = this[0].getAttribute(a)) && a in this[0] ? this[0][a] : d : this.each(function(d) {
					if (1 === this.nodeType) if (b(a)) for (q in a) this.setAttribute(q, a[q]);
					else this.setAttribute(a, m(this, c, d, this.getAttribute(a)))
				})
			},
			removeAttr: function(a) {
				return this.each(function() {
					1 === this.nodeType && this.removeAttribute(a)
				})
			},
			prop: function(a, b) {
				return b === p ? this[0] ? this[0][a] : p : this.each(function(c) {
					this[a] = m(this, b, c, this[a])
				})
			},
			data: function(a, b) {
				var c = this.attr("data-" + h(a), b);
				return null !== c ? c : p
			},
			val: function(a) {
				return a === p ? this.length > 0 ? this[0].value : p : this.each(function(b) {
					this.value = m(this, a, b, this.value)
				})
			},
			offset: function() {
				if (0 == this.length) return null;
				var a = this[0].getBoundingClientRect();
				return {
					left: a.left + window.pageXOffset,
					top: a.top + window.pageYOffset,
					width: a.width,
					height: a.height
				}
			},
			css: function(a, b) {
				if (b === p && "string" == typeof a) return 0 == this.length ? p : this[0].style[t(a)] || A(this[0], "").getPropertyValue(a);
				var c = "";
				for (q in a)"string" == typeof a[q] && "" == a[q] ? this.each(function() {
					this.style.removeProperty(h(q))
				}) : c += h(q) + ":" + j(q, a[q]) + ";";
				return "string" == typeof a && ("" == b ? this.each(function() {
					this.style.removeProperty(h(a))
				}) : c = h(a) + ":" + j(a, b)), this.each(function() {
					this.style.cssText += ";" + c
				})
			},
			index: function(a) {
				return a ? this.indexOf(r(a)[0]) : this.parent().children().indexOf(this[0])
			},
			hasClass: function(a) {
				return this.length < 1 ? !1 : i(a).test(this[0].className)
			},
			addClass: function(a) {
				return this.each(function(b) {
					s = [];
					var c = this.className,
						d = m(this, a, b, c);
					d.split(/\s+/g).forEach(function(a) {
						r(this).hasClass(a) || s.push(a)
					}, this), s.length && (this.className += (c ? " " : "") + s.join(" "))
				})
			},
			removeClass: function(a) {
				return this.each(function(b) {
					return a === p ? this.className = "" : (s = this.className, m(this, a, b, s).split(/\s+/g).forEach(function(a) {
						s = s.replace(i(a), " ")
					}), this.className = s.trim(), void 0)
				})
			},
			toggleClass: function(a, b) {
				return this.each(function(c) {
					var d = m(this, a, c, this.className);
					(b === p ? !r(this).hasClass(d) : b) ? r(this).addClass(d) : r(this).removeClass(d)
				})
			}
		}, ["width", "height"].forEach(function(a) {
			r.fn[a] = function(b) {
				var c, d = a.replace(/./, function(a) {
					return a[0].toUpperCase()
				});
				return b === p ? this[0] == window ? window["inner" + d] : this[0] == x ? x.documentElement["offset" + d] : (c = this.offset()) && c[a] : this.each(function(c) {
					var d = r(this);
					d.css(a, m(this, b, c, d[a]()))
				})
			}
		}), E.forEach(function(a, c) {
			r.fn[a] = function() {
				var a = r.map(arguments, function(a) {
					return b(a) ? a : N.fragment(a)
				});
				if (a.length < 1) return this;
				var d = this.length,
					e = d > 1,
					f = 2 > c;
				return this.each(function(b, g) {
					for (var h = 0; h < a.length; h++) {
						var i = a[f ? a.length - h - 1 : h];
						o(i, function(a) {
							null != a.nodeName && "SCRIPT" === a.nodeName.toUpperCase() && (!a.type || "text/javascript" === a.type) && window.eval.call(window, a.innerHTML)
						}), e && d - 1 > b && (i = i.cloneNode(!0)), n(c, g, i)
					}
				})
			}, r.fn[c % 2 ? a + "To" : "insert" + (c ? "Before" : "After")] = function(b) {
				return r(b)[a](this), this
			}
		}), N.Z.prototype = r.fn, N.camelize = t, N.uniq = u, r.zepto = N, r
	}();
window.Zepto = Zepto, "$" in window || (window.$ = Zepto), function(a) {
	function b(a) {
		return a._zid || (a._zid = l++)
	}
	function c(a, c, f, g) {
		if (c = d(c), c.ns) var h = e(c.ns);
		return (k[b(a)] || []).filter(function(a) {
			return !(!a || c.e && a.e != c.e || c.ns && !h.test(a.ns) || f && b(a.fn) !== b(f) || g && a.sel != g)
		})
	}
	function d(a) {
		var b = ("" + a).split(".");
		return {
			e: b[0],
			ns: b.slice(1).sort().join(" ")
		}
	}
	function e(a) {
		return new RegExp("(?:^| )" + a.replace(" ", " .* ?") + "(?: |$)")
	}
	function f(b, c, d) {
		a.isObject(b) ? a.each(b, d) : b.split(/\s/).forEach(function(a) {
			d(a, c)
		})
	}
	function g(c, e, g, h, i, j) {
		j = !! j;
		var l = b(c),
			m = k[l] || (k[l] = []);
		f(e, g, function(b, e) {
			var f = i && i(e, b),
				g = f || e,
				k = function(a) {
					var b = g.apply(c, [a].concat(a.data));
					return b === !1 && a.preventDefault(), b
				},
				l = a.extend(d(b), {
					fn: e,
					proxy: k,
					sel: h,
					del: f,
					i: m.length
				});
			m.push(l), c.addEventListener(l.e, k, j)
		})
	}
	function h(a, d, e, g) {
		var h = b(a);
		f(d || "", e, function(b, d) {
			c(a, b, d, g).forEach(function(b) {
				delete k[h][b.i], a.removeEventListener(b.e, b.proxy, !1)
			})
		})
	}
	function i(b) {
		var c = a.extend({
			originalEvent: b
		}, b);
		return a.each(p, function(a, d) {
			c[a] = function() {
				return this[d] = n, b[a].apply(b, arguments)
			}, c[d] = o
		}), c
	}
	function j(a) {
		if (!("defaultPrevented" in a)) {
			a.defaultPrevented = !1;
			var b = a.preventDefault;
			a.preventDefault = function() {
				this.defaultPrevented = !0, b.call(this)
			}
		}
	}
	var k = (a.zepto.qsa, {}),
		l = 1,
		m = {};
	m.click = m.mousedown = m.mouseup = m.mousemove = "MouseEvents", a.event = {
		add: g,
		remove: h
	}, a.proxy = function(c, d) {
		if (a.isFunction(c)) {
			var e = function() {
					return c.apply(d, arguments)
				};
			return e._zid = b(c), e
		}
		if ("string" == typeof d) return a.proxy(c[d], c);
		throw new TypeError("expected function")
	}, a.fn.bind = function(a, b) {
		return this.each(function() {
			g(this, a, b)
		})
	}, a.fn.unbind = function(a, b) {
		return this.each(function() {
			h(this, a, b)
		})
	}, a.fn.one = function(a, b) {
		return this.each(function(c, d) {
			g(this, a, b, null, function(a, b) {
				return function() {
					var c = a.apply(d, arguments);
					return h(d, b, a), c
				}
			})
		})
	};
	var n = function() {
			return !0
		},
		o = function() {
			return !1
		},
		p = {
			preventDefault: "isDefaultPrevented",
			stopImmediatePropagation: "isImmediatePropagationStopped",
			stopPropagation: "isPropagationStopped"
		};
	a.fn.delegate = function(b, c, d) {
		var e = !1;
		return ("blur" == c || "focus" == c) && (a.iswebkit ? c = "blur" == c ? "focusout" : "focus" == c ? "focusin" : c : e = !0), this.each(function(f, h) {
			g(h, c, d, b, function(c) {
				return function(d) {
					var e, f = a(d.target).closest(b, h).get(0);
					return f ? (e = a.extend(i(d), {
						currentTarget: f,
						liveFired: h
					}), c.apply(f, [e].concat([].slice.call(arguments, 1)))) : void 0
				}
			}, e)
		})
	}, a.fn.undelegate = function(a, b, c) {
		return this.each(function() {
			h(this, b, c, a)
		})
	}, a.fn.live = function(b, c) {
		return a(document.body).delegate(this.selector, b, c), this
	}, a.fn.die = function(b, c) {
		return a(document.body).undelegate(this.selector, b, c), this
	}, a.fn.on = function(b, c, d) {
		return void 0 == c || a.isFunction(c) ? this.bind(b, c) : this.delegate(c, b, d)
	}, a.fn.off = function(b, c, d) {
		return void 0 == c || a.isFunction(c) ? this.unbind(b, c) : this.undelegate(c, b, d)
	}, a.fn.trigger = function(b, c) {
		return "string" == typeof b && (b = a.Event(b)), j(b), b.data = c, this.each(function() {
			"dispatchEvent" in this && this.dispatchEvent(b)
		})
	}, a.fn.triggerHandler = function(b, d) {
		var e, f;
		return this.each(function(g, h) {
			e = i("string" == typeof b ? a.Event(b) : b), e.data = d, e.target = h, a.each(c(h, b.type || b), function(a, b) {
				return f = b.proxy(e), e.isImmediatePropagationStopped() ? !1 : void 0
			})
		}), f
	}, "focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout change select keydown keypress keyup error".split(" ").forEach(function(b) {
		a.fn[b] = function(a) {
			return this.bind(b, a)
		}
	}), ["focus", "blur"].forEach(function(b) {
		a.fn[b] = function(a) {
			if (a) this.bind(b, a);
			else if (this.length) try {
				this.get(0)[b]()
			} catch (c) {}
			return this
		}
	}), a.Event = function(a, b) {
		var c = document.createEvent(m[a] || "Events"),
			d = !0;
		if (b) for (var e in b)"bubbles" == e ? d = !! b[e] : c[e] = b[e];
		return c.initEvent(a, d, !0, null, null, null, null, null, null, null, null, null, null, null, null), c
	}
}(Zepto), function(a) {
	function b(a) {
		var b = this.os = {},
			c = this.browser = {},
			d = a.match(/WebKit\/([\d.]+)/),
			e = a.match(/(Android)\s+([\d.]+)/),
			f = a.match(/(iPad).*OS\s([\d_]+)/),
			g = !f && a.match(/(iPhone\sOS)\s([\d_]+)/),
			h = a.match(/(webOS|hpwOS)[\s\/]([\d.]+)/),
			i = h && a.match(/TouchPad/),
			j = a.match(/Kindle\/([\d.]+)/),
			k = a.match(/Silk\/([\d._]+)/),
			l = a.match(/(BlackBerry).*Version\/([\d.]+)/);
		(c.webkit = !! d) && (c.version = d[1]), e && (b.android = !0, b.version = e[2]), g && (b.ios = b.iphone = !0, b.version = g[2].replace(/_/g, ".")), f && (b.ios = b.ipad = !0, b.version = f[2].replace(/_/g, ".")), h && (b.webos = !0, b.version = h[2]), i && (b.touchpad = !0), l && (b.blackberry = !0, b.version = l[2]), j && (b.kindle = !0, b.version = j[1]), k && (c.silk = !0, c.version = k[1]), !k && b.android && a.match(/Kindle Fire/) && (c.silk = !0)
	}
	b.call(a, navigator.userAgent), a.__detect = b
}(Zepto), function(a, b) {
	function c(a) {
		return a.toLowerCase()
	}
	function d(a) {
		return e ? e + a : c(a)
	}
	var e, f = "",
		g = {
			Webkit: "webkit",
			Moz: "",
			O: "o",
			ms: "MS"
		},
		h = window.document,
		i = h.createElement("div"),
		j = /^((translate|rotate|scale)(X|Y|Z|3d)?|matrix(3d)?|perspective|skew(X|Y)?)$/i,
		k = {};
	a.each(g, function(a, d) {
		return i.style[a + "TransitionProperty"] !== b ? (f = "-" + c(a) + "-", e = d, !1) : void 0
	}), k[f + "transition-property"] = k[f + "transition-duration"] = k[f + "transition-timing-function"] = k[f + "animation-name"] = k[f + "animation-duration"] = "", a.fx = {
		off: e === b && i.style.transitionProperty === b,
		cssPrefix: f,
		transitionEnd: d("TransitionEnd"),
		animationEnd: d("AnimationEnd")
	}, a.fn.animate = function(b, c, d, e) {
		return a.isObject(c) && (d = c.easing, e = c.complete, c = c.duration), c && (c /= 1e3), this.anim(b, c, d, e)
	}, a.fn.anim = function(c, d, e, g) {
		var h, i, l, m = {},
			n = this,
			o = a.fx.transitionEnd;
		if (d === b && (d = .4), a.fx.off && (d = 0), "string" == typeof c) m[f + "animation-name"] = c, m[f + "animation-duration"] = d + "s", o = a.fx.animationEnd;
		else {
			for (i in c) j.test(i) ? (h || (h = []), h.push(i + "(" + c[i] + ")")) : m[i] = c[i];
			h && (m[f + "transform"] = h.join(" ")), !a.fx.off && "object" == typeof c && (m[f + "transition-property"] = Object.keys(c).join(", "), m[f + "transition-duration"] = d + "s", m[f + "transition-timing-function"] = e || "linear")
		}
		return l = function(b) {
			if ("undefined" != typeof b) {
				if (b.target !== b.currentTarget) return;
				a(b.target).unbind(o, arguments.callee)
			}
			a(this).css(k), g && g.call(this)
		}, d > 0 && this.bind(o, l), setTimeout(function() {
			n.css(m), 0 >= d && setTimeout(function() {
				n.each(function() {
					l.call(this)
				})
			}, 0)
		}, 0), this
	}, i = null
}(Zepto), function(a) {
	function b(b, c, d) {
		var e = a.Event(c);
		return a(b).trigger(e, d), !e.defaultPrevented
	}
	function c(a, c, d, e) {
		return a.global ? b(c || s, d, e) : void 0
	}
	function d(b) {
		b.global && 0 === a.active++ && c(b, null, "ajaxStart")
	}
	function e(b) {
		b.global && !--a.active && c(b, null, "ajaxStop")
	}
	function f(a, b) {
		var d = b.context;
		return b.beforeSend.call(d, a, b) === !1 || c(b, d, "ajaxBeforeSend", [a, b]) === !1 ? !1 : (c(b, d, "ajaxSend", [a, b]), void 0)
	}
	function g(a, b, d) {
		var e = d.context,
			f = "success";
		d.success.call(e, a, f, b), c(d, e, "ajaxSuccess", [b, d, a]), i(f, b, d)
	}
	function h(a, b, d, e) {
		var f = e.context;
		e.error.call(f, d, b, a), c(e, f, "ajaxError", [d, e, a]), i(b, d, e)
	}
	function i(a, b, d) {
		var f = d.context;
		d.complete.call(f, b, a), c(d, f, "ajaxComplete", [b, d]), e(d)
	}
	function j() {}
	function k(a) {
		return a && (a == x ? "html" : a == w ? "json" : u.test(a) ? "script" : v.test(a) && "xml") || "text"
	}
	function l(a, b) {
		return (a + "&" + b).replace(/[&?]{1,2}/, "?")
	}
	function m(b) {
		r(b.data) && (b.data = a.param(b.data)), b.data && (!b.type || "GET" == b.type.toUpperCase()) && (b.url = l(b.url, b.data))
	}
	function n(b, c, d, e) {
		var f = a.isArray(c);
		a.each(c, function(c, g) {
			e && (c = d ? e : e + "[" + (f ? "" : c) + "]"), !e && f ? b.add(g.name, g.value) : (d ? a.isArray(g) : r(g)) ? n(b, g, d, c) : b.add(c, g)
		})
	}
	var o, p, q = 0,
		r = a.isObject,
		s = window.document,
		t = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
		u = /^(?:text|application)\/javascript/i,
		v = /^(?:text|application)\/xml/i,
		w = "application/json",
		x = "text/html",
		y = /^\s*$/;
	a.active = 0, a.ajaxJSONP = function(b) {
		var c, d = "jsonp" + ++q,
			e = s.createElement("script"),
			f = function() {
				a(e).remove(), d in window && (window[d] = j), i("abort", h, b)
			},
			h = {
				abort: f
			};
		return b.error && (e.onerror = function() {
			h.abort(), b.error()
		}), window[d] = function(f) {
			clearTimeout(c), a(e).remove(), delete window[d], g(f, h, b)
		}, m(b), e.src = b.url.replace(/=\?/, "=" + d), a("head").append(e), b.timeout > 0 && (c = setTimeout(function() {
			h.abort(), i("timeout", h, b)
		}, b.timeout)), h
	}, a.ajaxSettings = {
		type: "GET",
		beforeSend: j,
		success: j,
		error: j,
		complete: j,
		context: null,
		global: !0,
		xhr: function() {
			return new window.XMLHttpRequest
		},
		accepts: {
			script: "text/javascript, application/javascript",
			json: w,
			xml: "application/xml, text/xml",
			html: x,
			text: "text/plain"
		},
		crossDomain: !1,
		timeout: 0
	}, a.ajax = function(b) {
		var c = a.extend({}, b || {});
		for (o in a.ajaxSettings) void 0 === c[o] && (c[o] = a.ajaxSettings[o]);
		d(c), c.crossDomain || (c.crossDomain = /^([\w-]+:)?\/\/([^\/]+)/.test(c.url) && RegExp.$2 != window.location.host);
		var e = c.dataType,
			i = /=\?/.test(c.url);
		if ("jsonp" == e || i) return i || (c.url = l(c.url, "callback=?")), a.ajaxJSONP(c);
		c.url || (c.url = window.location.toString()), m(c);
		var n, q = c.accepts[e],
			r = {},
			s = /^([\w-]+:)\/\//.test(c.url) ? RegExp.$1 : window.location.protocol,
			t = a.ajaxSettings.xhr();
		c.crossDomain || (r["X-Requested-With"] = "XMLHttpRequest"), q && (r.Accept = q, q.indexOf(",") > -1 && (q = q.split(",", 2)[0]), t.overrideMimeType && t.overrideMimeType(q)), (c.contentType || c.data && "GET" != c.type.toUpperCase()) && (r["Content-Type"] = c.contentType || "application/x-www-form-urlencoded"), c.headers = a.extend(r, c.headers || {}), t.onreadystatechange = function() {
			if (4 == t.readyState) {
				clearTimeout(n);
				var a, b = !1;
				if (t.status >= 200 && t.status < 300 || 304 == t.status || 0 == t.status && "file:" == s) {
					e = e || k(t.getResponseHeader("content-type")), a = t.responseText;
					try {
						"script" == e ? (1, eval)(a) : "xml" == e ? a = t.responseXML : "json" == e && (a = y.test(a) ? null : JSON.parse(a))
					} catch (d) {
						b = d
					}
					b ? h(b, "parsererror", t, c) : g(a, t, c)
				} else h(null, "error", t, c)
			}
		};
		var u = "async" in c ? c.async : !0;
		t.open(c.type, c.url, u);
		for (p in c.headers) t.setRequestHeader(p, c.headers[p]);
		return f(t, c) === !1 ? (t.abort(), !1) : (c.timeout > 0 && (n = setTimeout(function() {
			t.onreadystatechange = j, t.abort(), h(null, "timeout", t, c)
		}, c.timeout)), t.send(c.data ? c.data : null), t)
	}, a.get = function(b, c) {
		return a.ajax({
			url: b,
			success: c
		})
	}, a.post = function(b, c, d, e) {
		return a.isFunction(c) && (e = e || d, d = c, c = null), a.ajax({
			type: "POST",
			url: b,
			data: c,
			success: d,
			dataType: e
		})
	}, a.getJSON = function(b, c) {
		return a.ajax({
			url: b,
			success: c,
			dataType: "json"
		})
	}, a.fn.load = function(b, c) {
		if (!this.length) return this;
		var d, e = this,
			f = b.split(/\s/);
		return f.length > 1 && (b = f[0], d = f[1]), a.get(b, function(b) {
			e.html(d ? a(s.createElement("div")).html(b.replace(t, "")).find(d).html() : b), c && c.call(e)
		}), this
	};
	var z = encodeURIComponent;
	a.param = function(a, b) {
		var c = [];
		return c.add = function(a, b) {
			this.push(z(a) + "=" + z(b))
		}, n(c, a, b), c.join("&").replace("%20", "+")
	}
}(Zepto), function(a) {
	a.fn.serializeArray = function() {
		var b, c = [];
		return a(Array.prototype.slice.call(this.get(0).elements)).each(function() {
			b = a(this);
			var d = b.attr("type");
			"fieldset" != this.nodeName.toLowerCase() && !this.disabled && "submit" != d && "reset" != d && "button" != d && ("radio" != d && "checkbox" != d || this.checked) && c.push({
				name: b.attr("name"),
				value: b.val()
			})
		}), c
	}, a.fn.serialize = function() {
		var a = [];
		return this.serializeArray().forEach(function(b) {
			a.push(encodeURIComponent(b.name) + "=" + encodeURIComponent(b.value))
		}), a.join("&")
	}, a.fn.submit = function(b) {
		if (b) this.bind("submit", b);
		else if (this.length) {
			var c = a.Event("submit");
			this.eq(0).trigger(c), c.defaultPrevented || this.get(0).submit()
		}
		return this
	}
}(Zepto), function(a) {
	function b(a) {
		return "tagName" in a ? a : a.parentNode
	}
	function c(a, b, c, d) {
		var e = Math.abs(a - b),
			f = Math.abs(c - d);
		return e >= f ? a - b > 0 ? "Left" : "Right" : c - d > 0 ? "Up" : "Down"
	}
	function d() {
		g = null, h.last && (h.el.trigger("longTap"), h = {})
	}
	function e() {
		g && clearTimeout(g), g = null
	}
	var f, g, h = {},
		i = 750;
	a(document).ready(function() {
		var j, k;
		a(document.body).bind("touchstart", function(c) {
			j = Date.now(), k = j - (h.last || j), h.el = a(b(c.touches[0].target)), f && clearTimeout(f), h.x1 = c.touches[0].pageX, h.y1 = c.touches[0].pageY, k > 0 && 250 >= k && (h.isDoubleTap = !0), h.last = j, g = setTimeout(d, i)
		}).bind("touchmove", function(a) {
			e(), h.x2 = a.touches[0].pageX, h.y2 = a.touches[0].pageY
		}).bind("touchend", function() {
			e(), h.isDoubleTap ? (h.el.trigger("doubleTap"), h = {}) : h.x2 && Math.abs(h.x1 - h.x2) > 30 || h.y2 && Math.abs(h.y1 - h.y2) > 30 ? (h.el.trigger("swipe") && h.el.trigger("swipe" + c(h.x1, h.x2, h.y1, h.y2)), h = {}) : "last" in h && (h.el.trigger("tap"), f = setTimeout(function() {
				f = null, h.el.trigger("singleTap"), h = {}
			}, 250))
		}).bind("touchcancel", function() {
			f && clearTimeout(f), g && clearTimeout(g), g = f = null, h = {}
		})
	}), ["swipe", "swipeLeft", "swipeRight", "swipeUp", "swipeDown", "doubleTap", "tap", "singleTap", "longTap"].forEach(function(b) {
		a.fn[b] = function(a) {
			return this.bind(b, a)
		}
	})
}(Zepto), $.extend(localStore.prototype, {
	setValue: function(a, b) {
		localStorage.setItem(this.key, b ? JSON.stringify(a) : a)
	},
	getValue: function() {
		return localStorage.getItem(this.key)
	},
	remove: function() {
		localStorage.removeItem(this.key)
	},
	support: function() {
		try {
			return window.localStorage.setItem("test", !0), window.localStorage.removeItem("test"), !0
		} catch (a) {
			return !1
		}
	}
});
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
	getCookie: function(a) {
		var b = new RegExp("(^| )" + a + "(?:=([^;]*))?(;|$)"),
			c = document.cookie.match(b);
		return c ? c[2] ? unescape(c[2]) : "" : null
	},
	delCookie: function(a, b, c, d) {
		var e = utils.getCookie(a);
		if (null != e) {
			var f = new Date;
			f.setMinutes(f.getMinutes() - 1e3), b = b || "/", document.cookie = a + "=;expires=" + f.toGMTString() + (b ? ";path=" + b : "") + (c ? ";domain=" + c : "") + (d ? ";secure" : "")
		}
	},
	setCookie: function(a, b, c, d, e, f) {
		var g = new Date,
			c = arguments[2] || null,
			d = arguments[3] || "/",
			e = arguments[4] || null,
			f = arguments[5] || !1;
		c ? g.setMinutes(g.getMinutes() + parseInt(c)) : "", document.cookie = a + "=" + escape(b) + (c ? ";expires=" + g.toGMTString() : "") + (d ? ";path=" + d : "") + (e ? ";domain=" + e : "") + (f ? ";secure" : "")
	},
	validate: function(rule) {
		function showError(a, b) {
			var c = $("#" + b + "_msg");
			c.removeClass("qb_none").html(a), errArr.push(b)
		}
		var errArr = [];
		return $.each(rule, function(id, item) {
			var node = item.node || $("#" + id),
				value = item.value || node.val().toString();
			if (value = value.replace(/^\s*|\s*$/g, ""), !node.attr("disabled")) {
				var valLen = value.length;
				if (item.dByte && (valLen = value.replace(/[Α-￥]/g, "__").length), item.required) if ("" == value) showError(item.emptyMsg || "您输入的" + item.itemName + "不能为空", id);
				else if ("" == value || item.reg.test(value)) if (item.maxLen && valLen > item.maxLen) showError(item.errMsg || "您输入的" + item.itemName + "超过" + item.maxLen + "个字符", id);
				else if (item.minLen && valLen < item.minLen) showError(item.errMsg || "您输入的" + item.itemName + "不足" + item.minLen + "个字符", id);
				else if (item.minVal && value < item.minVal || item.maxVal && value > item.maxVal) showError("请输入" + item.minVal + "-" + item.maxVal + "的数字", id);
				else {
					var err = $("#" + id + "_msg");
					err.addClass("qb_none"), item.callback && eval(item.callback + "(value, err)")
				} else showError(item.errMsg, id);
				else "" == value || item.reg.test(value) ? item.maxLen && valLen > item.maxLen ? showError(item.errMsg || "您输入的" + item.itemName + "超过" + item.maxLen + "个字符", id) : item.minLen && valLen < item.minLen ? showError(item.errMsg || "您输入的" + item.itemName + "不足" + item.minLen + "个字符", id) : $("#" + id + "_msg").addClass("qb_none") : showError(item.errMsg, id)
			}
		}), errArr.length > 0 ? (document.getElementById(errArr[0]).focus(), !1) : !0
	},
	payDeal: function(a, b) {
		var c, d = a.data;
		d.payType ? location.href = window.basePath + "/cn/deal/codSuc.xhtml?dc=" + d.dealCode + "&suin=" + d.sellerUin + "&" + window.baseParam : d.payChannel ? (c = window.basePath + "/cn/minipay/tcallback.xhtml?paySuc=true&dealCode=" + d.dealCode + "&feeCash=" + d.minipayPo.feeCash + "&t=" + (new Date).getTime(), WeixinJSBridge.invoke("getBrandWCPayRequest", {
			appId: d.minipayPo.appId,
			timeStamp: d.minipayPo.timeStamp + "",
			nonceStr: d.minipayPo.nonceStr,
			"package": d.minipayPo.packageStr,
			signType: "SHA1",
			paySign: d.minipayPo.sign
		}, function(a) {
			b && b.removeClass("btn_disabled"), "get_brand_wcpay_request:ok" == a.err_msg && (location.href = c)
		})) : location.href = d.tenpayUrl
	}
};
$(document).ready(function() {
	function a() {
		var a = window.pageYOffset;
		a > 45 && a - d > 0 ? e.addClass("animate hidden_css3").removeClass("qb_none") : 0 > a - d && (0 >= a ? e.addClass("qb_none") : e.removeClass("hidden_css3").addClass("animate")), d = a
	}
	$(".go_back").on("click", function() {
		history.go(-1)
	});
	var b = "onorientationchange" in window,
		c = b ? "orientationchange" : "resize";
	window.addEventListener(c, function() {
		window.location.reload()
	}, !1), getCmdyCount();
	var d = 0,
		e = $("#lay_head_fixed"),
		f = window.innerHeight,
		g = document.documentElement.scrollHeight - 45;
	return f + 45 >= g ? void 0 : $.os.android ? ($("#lay_head").addClass("qb_none"), e.removeClass("qb_none"), void 0) : ($(window).on("scroll", a), void 0)
});
var business = {
	focus: function(a, b) {
		utils.showConfirm({
			describeText: a,
			sureFn: function() {
				WeixinJSBridge.invoke("addContact", {
					webtype: "1",
					username: window.wxInfo.wxId
				}, function(a) {
					if ("add_contact:ok" == a.err_msg || "add_contact:added" == a.err_msg) {
						window.wxInfo.isFocus = "true";
						var c = new Image;
						c.src = window.basePath + "/cn/focus/updateRelate.xhtml", b()
					}
				})
			}
		})
	}
};
utils.namespace("mobile.o2ocn.addrList"), utils.namespace("mobile.o2ocn.addrchoose"), function() {
	var a = mobile.o2ocn.addrList,
		b = mobile.o2ocn.addrchoose;
	a.init = function() {
		this.initParam(), this.bindEvent()
	}, a.initParam = function() {
		0 == window.addrLen && $("#no-address").removeClass("qb_none");
		var a = $("#tips");
		switch (window.oTip) {
		case 1:
			a.addClass("mod_tip_pass").removeClass("qb_none").html("修改成功");
			break;
		case 2:
			a.addClass("mod_tip_warn").removeClass("qb_none").html("修改失败，请稍后重试！");
			break;
		case 3:
			a.addClass("mod_tip_pass").removeClass("qb_none").html("删除成功");
			break;
		case 4:
			a.addClass("mod_tip_warn").removeClass("qb_none").html("删除失败，请稍后重试！");
			break;
		case 5:
			a.addClass("mod_tip_pass").removeClass("qb_none").html("成功添加收货地址");
			break;
		case 6:
			a.addClass("mod_tip_warn").removeClass("qb_none").html("添加收货地址失败，请稍后重试！");
			break;
		case 7:
			a.addClass("mod_tip_pass").removeClass("qb_none").html("收货地址导入成功");
			break;
		case 8:
			a.addClass("mod_tip_pass").removeClass("qb_none").html("部分收货地址导入成功");
			break;
		case 9:
			a.addClass("mod_tip_warn").removeClass("qb_none").html("收货地址导入失败")
		}
		a.hasClass("qb_none") || a.animate({
			opacity: 0
		}, 3e3, "ease-out", function() {
			a.addClass("qb_none")
		})
	}, a.bindEvent = function() {
		$("#forward").on("click", function() {
			var a, b = new localStore("order"),
				c = window.basePath + "/cn/my/index.xhtml?" + window.baseParam;
			b.support() ? (a = b.getValue(), c = a ? a : c, b.remove()) : navigator.cookieEnabled && (a = utils.getCookie("o2o_re_url"), c = a ? a : c, utils.delCookie("o2o_re_url")), location.href = c
		}), $("#add").on("click", function() {
			return addrLen >= 10 ? (utils.showBubble("最多可保存10条收货地址，请先删除不需要的地址"), !1) : void 0
		}), $(".delete").on("click", function() {
			var a = $(this);
			return utils.showConfirm({
				describeText: "确定删除收货地址吗？",
				sureFn: function() {
					location.href = a.attr("href")
				}
			}), !1
		})
	}, b.init = function() {
		this.initPage(), this.bindEvent()
	}, b.initPage = function() {
		if (this.last = $(".list_address li").eq(0), "" != window.callback) {
			$(".qb_icon").removeClass("icon_nike");
			var a = new localStore("confirm");
			a.support() ? a.setValue(window.callback, !1) : navigator.cookieEnabled && utils.setCookie("re_url_confirm", window.callback)
		}
	}, b.bindEvent = function() {
		var a = this,
			b = new localStore("confirm"),
			c = new localStore("order");
		$("#toAdd").on("click", function() {
			return len >= 10 ? (utils.showConfirm({
				describeText: "您最多可以保存10条收货地址，请删除不需要地址后再添加",
				sureFn: function() {
					return c.support() ? c.setValue(location.href, !1) : navigator.cookieEnabled && utils.setCookie("o2o_re_url", location.href), location.href = window.basePath + "/cn/recvaddr/list.xhtml?" + window.baseParam, !1
				}
			}), !1) : !0
		}), $(".list_address li").on("click", function() {
			var c, d = $(this),
				e = d.attr("adid"),
				f = a.last.attr("adid");
			e != f && (a.last.find("i").remove(), d.find(".mod_color_weak").after('<i class="qb_icon icon_nike"></i>')), a.last = d, b.support() ? c = b.getValue() : navigator.cookieEnabled ? (c = utils.getCookie("re_url_confirm"), utils.delCookie("re_url_confirm")) : c = document.referrer, -1 != c.indexOf("adid") ? c = c.replace(/adid=\d+/, "adid=" + e) : c += 0 != c.indexOf("&") ? "&adid=" + e : "?adid=" + e, location.href = c
		})
	}
}(), utils.namespace("mobile.o2ocn.address"), function() {
	function validate(rule) {
		function showError(a, b) {
			var c = $("#" + b + "_msg");
			c.removeClass("qb_none").html(a), errArr.push(b)
		}
		var errArr = [];
		return $.each(rule, function(id, item) {
			var node = $("#" + id),
				value = node.val() + "";
			if (value = value.replace(/^\s*|\s*$/g, ""), !node.attr("disabled")) {
				var valLen = value.length;
				if (item.dByte && (valLen = value.replace(/[Α-￥]/g, "__").length), item.required) if ("" == value) showError(item.emptyMsg || "您输入的" + item.itemName + "不能为空", id);
				else if ("" == value || item.reg.test(value)) if (item.maxLen && valLen > item.maxLen) showError(item.errMsg || "您输入的" + item.itemName + "超过" + item.maxLen + "个字符", id);
				else if (item.minLen && valLen < item.minLen) showError(item.errMsg || "您输入的" + item.itemName + "不足" + item.minLen + "个字符", id);
				else if (item.minVal && value < item.minVal || item.maxVal && value > item.maxVal) showError("请输入" + item.minVal + "-" + item.maxVal + "的数字", id);
				else {
					var err = $("#" + id + "_msg");
					err.addClass("qb_none"), item.callback && eval(item.callback + "(value, err)")
				} else showError(item.errMsg, id);
				else "" == value || item.reg.test(value) ? item.maxLen && valLen > item.maxLen ? showError(item.errMsg || "您输入的" + item.itemName + "超过" + item.maxLen + "个字符", id) : item.minLen && valLen < item.minLen ? showError(item.errMsg || "您输入的" + item.itemName + "不足" + item.minLen + "个字符", id) : $("#" + id + "_msg").addClass("qb_none") : showError(item.errMsg, id)
			}
		}), errArr.length > 0 ? (document.getElementById(errArr[0]).focus(), !1) : !0
	}
	var address = mobile.o2ocn.address;
	address.init = function() {
		this.formRule = {
			name: {
				emptyMsg: "请填写收件人姓名",
				itemName: "收件人姓名",
				reg: /^[Α-￥a-zA-Z]+$/,
				errMsg: "请填写正确的收件人姓名",
				dByte: !0,
				required: !0,
				maxLen: 30,
				minLex: 4
			},
			address: {
				emptyMsg: "请填写收件人的详细地址",
				itemName: "详细地址",
				reg: /.*/,
				errMsg: "请填写正确的地址信息",
				dByte: !0,
				required: !0,
				maxLen: 254,
				minLen: 4
			},
			mobile: {
				emptyMsg: "请填写收件人的联系电话",
				itemName: "收件人联系电话",
				reg: /^(1[3584]\d{9})$/,
				errMsg: "请填写正确的联系电话",
				required: !0,
				minLen: 5
			}
		}, this.initParam(), this.bindEvent()
	}, address.initParam = function() {
		this.isUpdate = parseInt($("#isUpdate").val(), 10), this.provId = parseInt($("#provId").val(), 10), this.oprTip = parseInt($("#oprTip").val(), 10), this.provinceNode = $("#province"), this.cityNode = $("#city"), this.regionNode = $("#regionId"), this.allProvince = [{
			pname: "北京",
			provinceId: 0
		}, {
			pname: "天津",
			provinceId: 1
		}, {
			pname: "上海",
			provinceId: 2
		}, {
			pname: "重庆",
			provinceId: 3
		}, {
			pname: "河北",
			provinceId: 4
		}, {
			pname: "河南",
			provinceId: 5
		}, {
			pname: "黑龙江",
			provinceId: 6
		}, {
			pname: "吉林",
			provinceId: 7
		}, {
			pname: "辽宁",
			provinceId: 8
		}, {
			pname: "山东",
			provinceId: 9
		}, {
			pname: "内蒙古",
			provinceId: 10
		}, {
			pname: "江苏",
			provinceId: 11
		}, {
			pname: "安徽",
			provinceId: 12
		}, {
			pname: "山西",
			provinceId: 13
		}, {
			pname: "陕西",
			provinceId: 14
		}, {
			pname: "甘肃",
			provinceId: 15
		}, {
			pname: "浙江",
			provinceId: 16
		}, {
			pname: "江西",
			provinceId: 17
		}, {
			pname: "湖北",
			provinceId: 18
		}, {
			pname: "湖南",
			provinceId: 19
		}, {
			pname: "贵州",
			provinceId: 20
		}, {
			pname: "四川",
			provinceId: 21
		}, {
			pname: "云南",
			provinceId: 22
		}, {
			pname: "新疆",
			provinceId: 23
		}, {
			pname: "宁夏",
			provinceId: 24
		}, {
			pname: "青海",
			provinceId: 25
		}, {
			pname: "西藏",
			provinceId: 26
		}, {
			pname: "广西",
			provinceId: 27
		}, {
			pname: "广东",
			provinceId: 28
		}, {
			pname: "福建",
			provinceId: 29
		}, {
			pname: "海南",
			provinceId: 30
		}, {
			pname: "台湾",
			provinceId: 31
		}, {
			pname: "香港",
			provinceId: 32
		}, {
			pname: "澳门",
			provinceId: 33
		}];
		var a = this;
		$.each(a.allProvince, function(b, c) {
			c.provinceId == a.provId ? a.provinceNode.append("<option value='" + c.provinceId + "' selected='selected'>" + c.pname + "</option>") : a.provinceNode.append("<option value='" + c.provinceId + "'>" + c.pname + "</option>")
		}), a.isUpdate > 0 || 6 == a.oprTip ? ("0" == a.provId || "1" == a.provId || "2" == a.provId || "3" == a.provId) && a.cityNode.parent().addClass("qb_none") : (a.cityNode.parent().addClass("qb_none"), a.regionNode.parent().addClass("qb_none"))
	}, address.bindEvent = function() {
		var a = this;
		$("#province").on("change", function() {
			var b = a.provinceNode.val(),
				c = $("#sprovince").attr("data-url"),
				d = {};
			d = {
				pvid: b,
				t: (new Date).getTime()
			}, d.pvid = b;
			var e = {
				type: "get",
				url: c,
				data: d,
				dataType: "json"
			};
			utils.ajaxReq(e, function(f) {
				f.errCode || (a.setSelArea(a.cityNode, "选择市", f.data), a.regionNode.parent().addClass("qb_none"), ("0" == b || "1" == b || "2" == b || "3" == b) && (c = $("#scity").attr("data-url"), d.ctid = f.data[0].id, a.cityNode.val(d.ctid), e.url = c, e.data = d, utils.ajaxReq(e, function(b) {
					b.errCode || (a.setSelArea(a.regionNode, "选择区", b.data), a.cityNode.parent().addClass("qb_none"))
				})))
			})
		}), $("#city").on("change", function() {
			var b = a.cityNode.val(),
				c = $("#scity").attr("data-url"),
				d = {};
			d.ctid = b;
			var e = {
				type: "get",
				url: c,
				data: d,
				dataType: "json"
			};
			utils.ajaxReq(e, function(b) {
				b.errCode || a.setSelArea(a.regionNode, "选择区", b.data)
			})
		}), $("#save_addr").on("click", function() {
			return a.save(), !1
		})
	}, address.setSelArea = function(a, b, c) {
		return a.empty(), a.append("<option value='-1'>" + b + "</option> "), c.length <= 0 ? (a.parent().addClass("qb_none"), void 0) : ($.each(c, function(b, c) {
			a.append("<option value='" + c.id + "'>" + c.name + "</option>")
		}), a.parent().removeClass("qb_none"), void 0)
	}, address.save = function() {
		this.check() && document.forms[0].submit()
	}, address.check = function() {
		var a = this,
			b = validate(a.formRule),
			c = !0,
			d = "",
			e = $("#region_msg");
		return "-1" == a.provinceNode.val() ? (d = "请选择省", e.removeClass("qb_none").html(d), c = !1) : a.cityNode.parent().hasClass("qb_none") || "-1" != a.cityNode.val() ? a.regionNode.parent().hasClass("qb_none") || "-1" != a.regionNode.val() || (d = "请选择区", e.removeClass("qb_none").html(d), c = !1) : (d = "请选择市", e.removeClass("qb_none").html(d), c = !1), c && b
	}
}(), utils.namespace("mobile.o2ocn.cmdyDeal"), function(g) {
	g.init = function() {
		this.initPage(), this.handleFeeChange(), this.scanCoupon(), this.scanPromote(!0), this.bindEvent()
	}, g.initPage = function() {
		var a = this,
			b = window.pageParam;
		b.totalPrice = parseInt(b.totalPrice, 10), a.evtConfig = {
			click: {
				submitOrder: a.handleSure,
				toAddrList: a.handleToAddr
			},
			change: {
				changePay: a.changePay,
				sendType: a.handleFeeChange,
				couponChange: a.handleToChooseCoupon,
				handlePromote: a.handlePromote
			}
		}, a.priceNode = $("#total-price"), a.pkgShipFeeNode = $("#select_shipFee"), a.pkgLen = parseInt(b.pkgLen, 10), a.curMtype = "", a.curShipFee = "", a.isFreeFee = !1, a.reduce = 0, a.couponReduce = 0, a.promoteId = 0, a.couponId = null, a.useCoupon = !0, a.payPrice = 0, a.pc = "true" === b.minipay ? -1 : 0, a.promoteIndex = {
			0: {
				op: "-",
				num: b.totalPrice
			},
			1: {
				op: "*",
				num: b.totalCount
			},
			num: b.totalPrice
		}, a.promoteInfo = b.promotion, a.pkgFavorParam = {}
	}, g.bindEvent = function() {
		document.body.addEventListener("change", this, !1), document.querySelector(".lay_page").addEventListener("click", this, !1)
	}, g.handleEvent = function(a) {
		var b = a.type,
			c = a.target,
			d = c.getAttribute("evtTag");
		d && this.evtConfig[b][d] && this.evtConfig[b][d].call(this, c)
	}, g.handleFeeChange = function(a) {
		var b = this,
			a = a || b.pkgShipFeeNode[0],
			c = a.options[a.selectedIndex];
		b.curMtype = c.getAttribute("mtype"), b.curShipFee = parseInt(c.value, 10), a && b.countPrice()
	}, g.scanCoupon = function() {
		var a, b, c = window.pageParam,
			d = this,
			e = c.coupon,
			f = d.promoteIndex;
		0 != e.length && (a = $("#promote-tpl").html(), b = '<option packetPrice="0" value="0">不使用优惠券</option>', $.each(e, function(a, c) {
			b += "<option " + (0 == a ? "selected" : void 0) + ' value="' + c.id + '" packetPrice="' + c.amount + '">' + (c.amount / 100).toFixed(2) + "元优惠券</option>", 0 == a && (d.couponId = c.id, d.couponReduce = -parseInt(c.amount, 10))
		}), a = a.replace(/{#id#}/, "").replace(/{#evtTag#}/, "couponChange").replace(/{#optList#}/, b), $("#coupon-node").html(a).removeClass("qb_none"), f["0"].num = f.num = c.totalPrice + d.couponReduce < 1 ? 1 : c.totalPrice + d.couponReduce)
	}, g.handleToChooseCoupon = function(a) {
		var b = a.selectedIndex,
			c = this,
			d = c.promoteIndex,
			e = a.options[b].getAttribute("packetPrice");
		c.couponReduce = 0 - e, c.useCoupon = 0 === c.couponReduce ? !1 : !0, c.couponId = a.value, d["0"].num = d.num = window.pageParam.totalPrice + c.couponReduce, c.scanPromote(!1)
	}, g.changePay = function(a) {
		this.pc = a.value
	}, g.countPrice = function() {
		var a, b = this,
			c = window.pageParam,
			d = c.totalPrice + b.couponReduce;
		if (1 > d && (d = 1), d += b.reduce, 1 > d && (d = 1), a = d + (b.isFreeFee ? 0 : b.curShipFee), "1" === c.payType && 500 > a && utils.showBubble("您购买的商品应付总价低于5元，无法使用货到付款功能，请用在线付款下单!"), "1" === c.payType) {
			var e = b.divPrice(a);
			"0.00" != e.free ? $("#free-div").html(utils.strReplace($("#free-tpl").html(), e)).removeClass("qb_none") : $("#free-div").addClass("qb_none"), a = 100 * e.pkgTotal
		} else $("#free-div").addClass("qb_none");
		b.payPrice = a, b.priceNode.html("&yen;" + (a / 100).toFixed(2)), b.reduce + b.couponReduce < 0 ? ($("#dealoff-div").removeClass("qb_none"), $("#dealoff-price").html("&yen;" + ((-1 * b.reduce + -1 * b.couponReduce) / 100).toFixed(2))) : $("#dealoff-div").addClass("qb_none")
	}, g.divPrice = function(a) {
		var b = {
			price: (a / 100).toFixed(2),
			free: "0.00",
			pkgTotal: "0.00"
		},
			c = a.toString(),
			d = c.substring(c.length - 2),
			e = c.substring(0, c.length - 2);
		return d && (b.free = (parseInt(d) / 100).toFixed(2)), e && (b.pkgTotal = parseInt(e).toFixed(2)), b
	}, g.handleSure = function(a) {
		var b = this,
			c = window.pageParam,
			d = c.payType,
			e = [],
			f = [],
			a = $(a);
		if (!a.hasClass("btn_disabled")) {
			if (!b.useCoupon && b.couponId && "0" === b.couponId) return utils.showConfirm({
				describeText: "您暂未使用任何店铺优惠券",
				sureText: "确认不使用",
				cancelText: "返回使用",
				sureFn: function() {
					b.useCoupon = !0, b.handleSure(a)
				}
			}), !1;
			if ("1" === d && b.payPrice < 500) return utils.showBubble("您购买的商品总价低于5元，无法使用货到付款功能，请用在线付款下单!"), void 0;
			if (b.promList && 0 == b.promoteId && !b.showed) return utils.showBubble("您的订单可以享受满减优惠，赶快选择优惠方案吧！"), b.showed = !0, !1;
			if ("0" === d && b.pc < 0) return utils.showBubble("请选择在线支付方式"), void 0;
			$.each(window.subParam, function(a, b) {
				e.push(b.ic + "-" + b.attr + "-" + b.bc + "-" + b.priceType)
			}), f.push(c.payType + "~" + b.promoteId + "~" + c.suin + "~" + b.curMtype + "~" + b.couponId + "~" + e.join("~")), a.addClass("btn_disabled"), utils.ajaxReq({
				url: window.basePath + "/cn/cmdy/makeOrder.xhtml?" + window.baseParam,
				dataType: "json",
				type: "POST",
				data: {
					orderStrList: f.toString(),
					adid: c.adid,
					payType: d,
					pc: b.pc
				}
			}, function(b) {
				b.errCode ? 260 == b.errCode ? (a.removeClass("btn_disabled"), utils.showBubble("登录超时")) : (a.removeClass("btn_disabled"), utils.showAjaxErr(b, "下单失败，请重试")) : utils.payDeal(b, a)
			})
		}
	}, g.scanPromote = function(a) {
		var b = this,
			c = b.promoteInfo,
			d = [];
		if (a ? c.splice(0, 1) : (b.reduce = 0, b.isFreeFee = !1, b.promoteId = 0), $.each(c, function(a, c) {
			var e = b.countPromote(c);
			e.show && (e.freight <= 0 ? c.freeFee = !0 : "", c.rm = e.payable, c.show = e.show, d.push(c), e = null)
		}), 0 == d.length) return b.promList && b.promList.parent().remove() && (b.promList = null), b.countPrice(), void 0;
		d = d.sort(function(a, b) {
			return a.rm > b.rm
		});
		var e = $("#promote-tpl").html(),
			f = "<option value='999999'>请选择店铺促销</option>";
		$.each(d, function(a, b) {
			f += "<option value='" + b.rm + "' " + (b.freeFee ? "freeFee='true'" : "") + " promId='" + b.id + "'>" + b.desc + "</option>"
		}), e = e.replace(/{#id#}/, "promote-list").replace(/{#evtTag#}/, "handlePromote").replace(/{#optList#}/, f), $("#promote-node").html(e).removeClass("qb_none"), f = null, b.promList = $("#promote-list"), b.countPrice()
	}, g.handlePromote = function(a) {
		var b, c = this,
			d = a.value;
		return "999999" == d ? (c.reduce = 0, c.isFreeFee = !1, c.promoteId = 0, c.countPrice(), void 0) : (b = a.options[a.selectedIndex], c.reduce = parseInt(d, 10), c.isFreeFee = b.getAttribute("freeFee") ? !0 : !1, c.promoteId = b.getAttribute("promId"), this.countPrice(), void 0)
	}, g.countPromote = function(rule) {
		var _this = this,
			cnds = rule.cnd,
			reduce, favor = 0,
			freight = parseInt(_this.pkgShipFeeNode.val(), 10),
			pi = _this.promoteIndex;
		return $.each(cnds, function(seq, cnd) {
			"0" !== cnd && pi[seq].num >= parseInt(cnd, 10) && ("0" !== rule.feeCarriage[0] && (freight = 0 - freight), reduce = rule.reduce, $.each(reduce, function(index, item) {
				"0" !== item && "0.0" !== item && (favor = -(pi.num - eval(pi.num + pi[index].op + item)))
			}))
		}), {
			freight: freight,
			payable: favor,
			show: rule.guide ? !0 : 0 == favor ? !1 : !0
		}
	}, g.handleToAddr = function() {
		var a = new localStore("confirm");
		a.support() ? a.setValue(location.href, !1) : navigator.cookieEnabled && utils.setCookie("re_url_confirm", location.href), location.href = $("#goaddlist").attr("addr")
	}
}(mobile.o2ocn.cmdyDeal), utils.namespace("mobile.o2ocn.cmdy"), function() {
	var a = mobile.o2ocn.cmdy;
	a.init = function() {
		this.initParam(), this.initCmdyList(), this.bindEvent()
	}, a.initParam = function() {
		this.itemLen = parseInt($("#items-len").val(), 10), this.proLen = parseInt($("#problem-Len").val(), 10), this.itemCache = window.itemArray, this.selectItems = {
			number: 0,
			items: {}
		}, this.payType = 0, this.unSupCodLen = 0, this.chosedUnCodLen = 0, this.confirmNodes = $(".confirm_order"), this.selectDivNode = $("#pay-type"), this.arrivePayNode = $("#pay-arrive"), this.unSupCodList = {}, this.choseNode = []
	}, a.initCmdyList = function() {
		var a = this;
		a.itemList = $("#item-sec .item"), 0 != a.itemLen && (a.selectAll = $("#choose-all"), a.itemList.each(function() {
			var b = $(this),
				c = b.find(".cmdy-data"),
				d = c.attr("supportCod"),
				e = b.find("i").eq(0);
			a.selectItems.number++, a.selectItems.items[e.attr("index")] = e, a.choseNode.push(e), "false" == d && (a.unSupCodList[e.attr("index")] = e, a.unSupCodLen++, a.chosedUnCodLen++), e.on("click", function() {
				var b, c = $(this),
					d = c.attr("index");
				return c.hasClass("icon_checkbox_disabled") ? (utils.showBubble("您选择的商品不支持货到付款，建议您选择在线支付"), void 0) : (b = c.hasClass("icon_checkbox_checked"), b ? (c.removeClass("icon_checkbox_checked"), delete a.selectItems.items[d], a.selectItems.number--) : (c.addClass("icon_checkbox_checked"), a.selectItems.items[d] = c, a.selectItems.number++), a.selectItems.number < a.itemLen ? a.selectAll.removeClass("icon_checkbox_checked") : a.selectAll.addClass("icon_checkbox_checked"), a.callUnCod(b, d), a.changeSel(), void 0)
			})
		}), 0 != a.unSupCodLen && a.arrivePayNode.addClass("btn_disabled"))
	}, a.callUnCod = function(a, b) {
		this.unSupCodList[b] && (a ? this.chosedUnCodLen-- : this.chosedUnCodLen++), 0 == this.chosedUnCodLen ? this.arrivePayNode.removeClass("btn_disabled") : this.arrivePayNode.addClass("btn_disabled")
	}, a.bindEvent = function() {
		var a = this;
		0 != a.itemLen && (a.selectAll.on("click", $.proxy(this.checkboxAll, this)), $(".confirm_order").on("click", $.proxy(this.confirmOrder, this)), "false" === window.onlyonline && a.selectDivNode.on("touchstart", $.proxy(this.handlePayType, this)), a.itemEvent()), 0 != a.proLen && $(".problem_item").each(function() {
			var b = $(this);
			b.find(".icon_delete").on("click", function() {
				utils.showConfirm({
					describeText: "确定要删除该商品吗？",
					sureFn: function() {
						a.del(b, !1)
					}
				})
			})
		})
	}, a.checkboxAll = function(a) {
		var b = this,
			c = $(a.target);
		b.selectItems = {
			number: 0,
			items: {}
		}, c.hasClass("icon_checkbox_checked") ? ($.each(b.choseNode, function(a, b) {
			$(b).removeClass("icon_checkbox_checked")
		}), b.arrivePayNode.removeClass("btn_disabled"), b.chosedUnCodLen = 0) : ($.each(b.choseNode, function(a, c) {
			c = $(c), c.hasClass("icon_checkbox_disabled") || (c.addClass("icon_checkbox_checked"), b.selectItems.number++, b.selectItems.items[$(c).attr("index")] = c)
		}), b.unSupCodLen > 0 && 0 == b.payType ? b.arrivePayNode.addClass("btn_disabled") : void 0, b.chosedUnCodLen = b.unSupCodLen), b.selectAll.toggleClass("icon_checkbox_checked"), b.changeSel()
	}, a.handlePayType = function(a) {
		var b = this,
			c = $(a.target),
			d = !0;
		if (!c.hasClass("active")) {
			if (c.hasClass("btn_disabled")) return utils.showBubble("您选择的部分商品不支持货到付款，请选择在线支付"), void 0;
			if (c.addClass("active").siblings().removeClass("active"), b.payType = c.attr("pt"), "0" == b.payType) return $.each(b.choseNode, function(a, c) {
				c = $(c), c.hasClass("icon_checkbox_checked") || (c.hasClass("icon_checkbox_disabled") ? c.removeClass("icon_checkbox_disabled") : "", c.addClass("icon_checkbox_checked"), b.selectItems.number++, b.selectItems.items[a] = c)
			}), b.changeSel(), b.selectAll.addClass("icon_checkbox_checked"), 0 != b.unSupCodLen ? b.arrivePayNode.addClass("btn_disabled") : void 0, b.chosedUnCodLen = b.unSupCodLen, !1;
			$.each(b.unSupCodList, function(a, c) {
				c = $(c), c.addClass("icon_checkbox_disabled"), c.hasClass("icon_checkbox_checked") && (c.removeClass("icon_checkbox_checked"), delete b.selectItems.items[c.attr("index")], b.selectItems.number--, d = !1)
			}), b.changeSel(), d || (b.selectAll.removeClass("icon_checkbox_checked"), utils.showBubble("已经为您取消了不支持货到付款的商品"))
		}
	}, a.updateList = function() {
		this.choseNode = $("i[name='cart_checkbox']")
	}, a.itemEvent = function() {
		var a = this;
		a.itemList.each(function() {
			var b = $(this),
				c = b.find(".cmdy-data"),
				d = c.attr("ic"),
				e = parseInt(c.attr("limit"), 10),
				f = parseInt(c.attr("storeNum"), 10),
				g = parseInt(c.attr("num"), 10);
			f > 0 && g > f && (a.modifyNum(b, f), utils.showBubble("您填写的购买数量已超过上限")), b.find(".count").on("input", function() {
				var c = this;
				if (c.value) if (isNaN(c.value) || -1 != c.value.indexOf(".")) {
					utils.showBubble("请输入正确的数量");
					var d = setTimeout(function() {
						$(c).val(b.attr("last-count"))
					}, 500)
				} else {
					var g = setTimeout(function() {
						var d = parseInt(c.value, 10),
							g = b.attr("last-count");
						e > 0 && d >= e ? (d = e, utils.showBubble("最多购买" + e + "件"), a.modifyNum(b, e)) : d >= f ? (d = f, utils.showBubble("最多购买" + f + "件"), a.modifyNum(b, f)) : 1 > d ? (d = 1, utils.showBubble("至少购买1件"), a.modifyNum(b, 1)) : d != g && a.modifyNum(b, d)
					}, 500);
					this.onkeydown = function() {
						g && (clearTimeout(g), g = null), d && (clearTimeout(d), d = null)
					}
				}
			}), b.find(".icon_delete").on("click", function() {
				utils.showConfirm({
					describeText: "确定要删除该商品吗？",
					sureFn: function() {
						a.del(b, !0)
					}
				})
			}), b.find(".item_detail").on("click", function() {
				window.location = window.basePath + "/cn/item/detail.xhtml?ic=" + d + "&" + window.baseParam
			})
		})
	}, a.changeSel = function() {
		var a = this;
		0 == a.selectItems.number ? a.confirmNodes.removeClass("btn_strong") : a.confirmNodes.addClass("btn_strong"), a.setTotalPrice()
	}, a.modifyNum = function(a, b) {
		var c = this,
			d = a.find(".cmdy-data");
		utils.ajaxReq({
			type: "POST",
			url: window.basePath + "/cn/cmdy/modifyNum.xhtml?" + window.baseParam,
			data: {
				ic: d.attr("ic"),
				attr: d.attr("sa"),
				bc: b,
				t: (new Date).getTime()
			},
			dataType: "json"
		}, function(d) {
			d.errCode ? 5122 == d.errCode && 105 == d.retCode ? location.reload() : (utils.showAjaxErr(d, "修改数量失败,请稍候再试"), a.find(".count").val(a.attr("last-count"))) : (a.find(".count").val(b), a.attr("last-count", b), a.find(".single-total").html("&yen;" + (b * a.attr("price") / 100).toFixed(2)), c.itemCache[a.attr("id")].bc = b, c.setTotalPrice())
		}, function() {
			utils.showBubble("修改数量失败,请稍候再试"), a.find(".count").val(a.attr("last-count"))
		})
	}, a.del = function(a, b) {
		var c = this,
			d = a.find(".cmdy-data"),
			e = d.attr("ic"),
			f = d.attr("sa"),
			g = a.find(".count").val(),
			h = d.attr("supportCod"),
			i = {
				itemList: e + "-" + f + "-" + g,
				t: (new Date).getTime()
			};
		utils.ajaxReq({
			type: "POST",
			url: window.basePath + "/cn/cmdy/remove.xhtml?" + window.baseParam,
			data: i,
			dataType: "json"
		}, function(d) {
			if (d.errCode) utils.showAjaxErr(d, "操作失败,请稍候再试");
			else {
				if (getCmdyCount(), b ? c.itemLen-- : c.proLen--, a.animate({
					opacity: 0
				}, 300, "ease-out", function() {
					$(this).remove(), c.updateList()
				}), 0 == c.itemLen && 0 == c.proLen) return c.emptyCartIfNone(), void 0;
				if (0 == c.itemLen && b) return c.emptyCartIfNone("item-sec"), void 0;
				if (0 == c.proLen && !b) return $("#un-sec").hide(), void 0;
				b && a.find("i[name=cart_checkbox]").hasClass("icon_checkbox_checked") && (delete c.selectItems.items[a.attr("id")], c.selectItems.number--), b && ("false" == h && c.unSupCodLen--, c.unSupCodLen == c.itemLen && c.selectDivNode.html("<p>付款方式：在线支付</p>"), setTimeout(function() {
					c.changeSel()
				}, 550), delete c.itemCache[a.attr("id")])
			}
		})
	}, a.setTotalPrice = function() {
		var a = 0,
			b = this;
		b.selectItems.number > 0 && $.each(b.selectItems.items, function(c, d) {
			a += parseFloat(d.attr("price")) * b.itemCache[d.attr("index")].bc
		}), $("#total").text((a / 100).toFixed(2))
	}, a.emptyCartIfNone = function(a) {
		a = a ? a : "content", getCmdyCount(), $("#" + a).html("<div class='qb_gap qb_tac qb_pt10' style='padding:70px 0 5px;margin-bottom: 0px;'><img src='http://3glogo.gtimg.com/mobilelife/o2o/cn/img/s/icon_cart_empty.png' width='81' class='qb_mb10'><p class='qb_fs_xl mod_color_comment'>你的购物车空空如也哦</p></div> <div class='qb_tac qb_gap'><a href='" + window.basePath + "/cn/index.xhtml?" + window.baseParam + "' class='mod_btn'>去逛逛</a></div>")
	}, a.confirmOrder = function() {
		var b = this,
			c = [],
			d = $(".confirm_order");
		if (!d.hasClass("btn_strong")) return utils.showBubble("请选择商品"), void 0;
		if (window.wxInfo && "true" != window.wxInfo.isFocus) return business.focus("购买商品，需要关注该商户，是否立即关注?", $.proxy(a.confirmOrder, this)), void 0;
		for (var e in b.selectItems.items) c.push(b.itemCache[e].ic + "-" + b.itemCache[e].attr + "-" + b.itemCache[e].bc);
		$("#payType").val(b.payType), $("#trandom").val((new Date).getTime() / 1e3), $("#itemList").val(c.join("~")), document.cartForm.submit()
	}
}(), utils.namespace("mobile.o2ocn.coupon"), function() {
	var a = mobile.o2ocn.coupon;
	a.init = function() {
		$(".tab_coupon").click(function() {
			$(this).hasClass("active") || ($(".tab_coupon").toggleClass("active"), $(".fn_coupon").toggleClass("qb_none"))
		})
	}
}(), utils.namespace("mobile.o2ocn.orderdetail"), function() {
	var a = mobile.o2ocn.orderdetail;
	a.init = function() {
		this.initParam(), this.initPageBtn(), this.bindEvent(), this.initLogis()
	}, a.initParam = function() {
		this.evtConfig = {
			click: {
				retryLogis: this.getLogis,
				retryDetail: this.getLogis,
				cancelDeal: this.cancelDeal,
				confirmRecv: this.confirmRecv,
				drawback: this.applyDrawback,
				regoods: this.returnGoods,
				cancelgoods: this.cancelgoods
			},
			change: {
				changePay: this.changePay
			}
		}
	}, a.initLogis = function() {
		$("#deliver-id").val() && (this.getLogis(), $("#wuliu-info").removeClass("qb_none"))
	}, a.initPageBtn = function() {
		var a = [];
		switch (window.dealStatus) {
		case 1:
			a.push('<div class="mod_cell ui_p10"><div class="qb_flex">'), a.push($("#cancel-tpl").html()), a.push($("#topay-tpl").html()), a.push("</div></div>");
			break;
		case 2:
			2 == window.payType && (a.push('<div class="qb_flex qb_mb10 ui_gap">'), a.push($("#cancel-tpl").html()), a.push("</div>")), 1 == window.payType && a.push($("#drawback-tpl").html());
			break;
		case 3:
			2 == window.payType ? $("#suc-msg").removeClass("qb_none") : (a.push('<div class="qb_flex qb_mb10 ui_gap">'), a.push($("#recv-tpl").html()), a.push("</div>"));
			break;
		case 7:
			a.push($("#onback-tpl").html().replace(/{#text#}/, "退款中..."));
			break;
		case 8:
			a.push($("#onback-tpl").html().replace(/{#text#}/, "退款完成"));
			break;
		case 18:
			break;
		case 19:
			a.push($("#cancelgoods-tpl").html())
		}
		$("#operate_area").html(a.join(""))
	}, a.bindEvent = function() {
		$(".lay_page")[0].addEventListener("click", this, !1), $("#wuliu-info-first").on("click", $.proxy(this.toLogisDetail, this)), $(".btn_back").on("click", $.proxy(this.toDealDetail, this)), document.body.addEventListener("change", this, !1)
	}, a.handleEvent = function(a) {
		var b = a.type,
			c = a.target,
			d = c.getAttribute("evtTag");
		d && this.evtConfig[b] && this.evtConfig[b][d] && this.evtConfig[b][d].call(this, c)
	}, a.toLogisDetail = function() {
		document.title = "物流详情", $("#page_tracert").removeClass("qb_none"), $("#page_order_detail").addClass("qb_none")
	}, a.toDealDetail = function() {
		document.title = "订单详情", $("#page_order_detail").removeClass("qb_none"), $("#page_tracert").addClass("qb_none")
	}, a.cancelDeal = function() {
		utils.showConfirm({
			describeText: "您确定要取消这个订单吗？",
			sureFn: function() {
				var a = $("#cancel-deal").attr("data-url"),
					b = window.dc,
					c = window.ds,
					d = 0;
				2 == window.payType && (d = 1), utils.ajaxReq({
					type: "POST",
					data: {
						dc: b,
						suin: c,
						pt: d
					},
					url: a,
					dataType: "json"
				}, function(a) {
					a.errCode ? utils.showAjaxErr(a, "取消订单失败，请您稍候再试") : (utils.showBubble("取消订单成功"), $("#operate_area").addClass("qb_none"), $("#deal-status").html("已关闭"))
				})
			}
		})
	}, a.confirmRecv = function() {
		var a = $("#to-confirm").attr("data-url"),
			b = window.dc,
			c = window.sdc;
		utils.ajaxReq({
			type: "POST",
			data: {
				dc: b,
				sdc: c
			},
			url: a,
			dataType: "json"
		}, function(a) {
			a.errCode ? utils.showAjaxErr(a, "很抱歉，确认收货失败") : (utils.showBubble("确认收货成功"), $("#deal-status").html("交易成功"), $("#to-confirm").addClass("qb_none"))
		})
	}, a.getLogis = function() {
		utils.ajaxReq({
			type: "get",
			url: window.logisUrl,
			dataType: "jsonp"
		}, function() {})
	}, a.applyDrawback = function() {
		utils.showConfirm({
			cancelFn: function() {
				utils.ajaxReq({
					url: window.basePath + "/cn/deal/applyRefund.xhtml",
					data: {
						dc: window.dc,
						t: +new Date
					},
					type: "POST",
					dataType: "json"
				}, function(a) {
					a.errCode ? 260 == a.errCode ? utils.showBubble("登录校验失败") : utils.showAjaxErr(a, "申请退款遇到错误，请重试") : utils.showConfirm({
						describeText: "退款申请已经提交！我们会尽快处理您的请求。若订单已经发货，退款申请将被驳回，我们会及时与您联系告知退货事宜。敬请理解，谢谢！",
						showNum: 1,
						sureFn: function() {
							location.href = window.basePath + "/cn/my/index.xhtml?dback=true&index=2"
						}
					})
				}, function() {
					utils.showBubble("请求遇到错误，请重试")
				})
			},
			cancelText: "申请退款",
			sureText: "取消",
			describeText: "确定要申请退款吗？提交退款申请后，我们会根据实际情况处理您的订单，若尚未发货，我们会将已支付的款项退还到您的支付账户；若已经发货，退款申请可能会被驳回。强烈建议您先通过微信与我们联系。"
		})
	}, a.returnGoods = function() {
		utils.showConfirm({
			describeText: "确定要申请退货吗？如果对商品和服务有任何疑问，建议您先通过微信与我们沟通，我们会热心为您解答。",
			sureText: "申请退货",
			cancelText: "取消",
			sureFn: function() {
				location.href = window.basePath + "/cn/refund/applyIndex.xhtml?dc=" + window.dc + "&" + window.baseParam
			}
		})
	}, a.cancelReGoods = function() {
		utils.showConfirm({
			describeText: "您确定要取消退货吗？为确保您的权益，取消退货后，如有其它问题，请与我们联系。",
			sureFn: function() {
				utils.ajaxReq({
					url: window.basePath + "/cn/refund/bcancel.xhtml",
					dataType: "json",
					data: {
						dc: window.dc
					}
				}, function(a) {
					a.errCode ? utils.showAjaxErr(a, "取消退货失败，请重试。") : location.reload()
				}, function() {
					utils.showBubble("取消退货失败，请重试。")
				})
			}
		})
	}, a.changePay = function(a) {
		return "true" === stockEmpty ? (utils.showBubble("商品库存不足"), void 0) : (0 == a.value ? location.href = a.getAttribute("link") : 1 == a.value && utils.payDeal({
			data: {
				minipayPo: window.wxpay,
				payChannel: 1,
				payType: 0,
				dealCode: window.dc
			}
		}), void 0)
	}
}(), utils.namespace("mobile.o2ocn.deal"), function(g) {
	g.init = function() {
		this.initPage(), this.scanCoupon(), this.scanPromote(!0), this.bindEvent()
	}, g.initPage = function() {
		var a = window.pageParam,
			b = this;
		b.evtConfig = {
			change: {
				sendType: b.handleSelect,
				changePay: b.changePayType,
				couponChange: b.handleCouponSelect,
				handlePromote: b.handlePromote
			}
		}, a.price = parseInt(a.price, 10), b.freeNode = $("#free-div"), b.mtypeNode = $("#mtype"), b.priceNode = $("#total-price");
		var c = b.mtypeNode.get(0),
			d = c.options[c.selectedIndex];
		b.curMtype = d.getAttribute("mtype"), b.curPtype = d.getAttribute("ptype"), b.pc = "true" === a.minipay ? -1 : 0, b.payTypeNode = $("#payType"), b.curShipFee = parseInt(d.value, 10), b.isFreeFee = !1, b.reduce = 0, b.couponReduce = 0, b.promoteId = 0, b.couponId = null, b.useCoupon = !0, b.promoteIndex = {
			0: {
				op: "-",
				num: a.price
			},
			1: {
				op: "*",
				num: a.buyCount
			},
			num: a.price
		}, b.payPrice = 0, b.changePayType()
	}, g.bindEvent = function() {
		var a = this;
		document.body.addEventListener("change", a, !1), $("#submit-order").on("click", $.proxy(a.handleSure, a)), $("#toAddrList").on("click", $.proxy(a.handleToAddr, a))
	}, g.handleEvent = function(a) {
		if ("change" == a.type) {
			var b = a.target,
				c = b.getAttribute("evtTag");
			c && this.evtConfig.change[c] && this.evtConfig.change[c].call(this, b)
		}
	}, g.countPrice = function() {
		var a, b = this,
			c = window.pageParam.price + b.couponReduce;
		if (1 > c && (c = 1), c += b.reduce, 1 > c && (c = 1), a = c + (b.isFreeFee ? 0 : b.curShipFee), "1" == b.curPtype && 500 > a && utils.showBubble("您购买的商品应付总价低于5元，无法使用货到付款功能，请用在线付款下单!"), "1" == b.curPtype) {
			var d = b.divPrice(a);
			"0.00" != d.free ? b.freeNode.removeClass("qb_none").html(utils.strReplace($("#free-tpl").html(), d)) : b.freeNode.addClass("qb_none"), a = 100 * d.disPrice
		} else b.freeNode.addClass("qb_none");
		b.payPrice = a, b.priceNode.html("&yen;" + (a / 100).toFixed(2)), b.reduce + b.couponReduce < 0 ? ($("#dealoff-div").removeClass("qb_none"), $("#dealoff-price").html("&yen;" + ((-1 * b.reduce + -1 * b.couponReduce) / 100).toFixed(2))) : $("#dealoff-div").addClass("qb_none")
	}, g.divPrice = function(a) {
		var b = {
			price: (a / 100).toFixed(2),
			free: "0.00",
			disPrice: "0.00"
		},
			c = a.toString(),
			d = c.substring(c.length - 2),
			e = c.substring(0, c.length - 2);
		return d && (b.free = (parseInt(d) / 100).toFixed(2)), e && (b.disPrice = parseInt(e).toFixed(2)), b
	}, g.handleSelect = function() {
		var a = this,
			b = parseInt(a.mtypeNode.val(), 10),
			c = a.mtypeNode.get(0),
			d = c.selectedIndex,
			e = c.options[d],
			f = e.getAttribute("mtype"),
			g = e.getAttribute("ptype");
		a.curMtype = f, a.curPtype = g, a.curShipFee = parseInt(b, 10), "11" == f && "1" == g ? $("#payType").addClass("qb_none", !0) : $("#payType").removeClass("qb_none", !1), a.countPrice()
	}, g.handleCouponSelect = function(a) {
		var b = a.selectedIndex,
			c = this,
			d = c.promoteIndex,
			e = a.options[b].getAttribute("packetPrice");
		c.couponReduce = 0 - e, c.useCoupon = 0 === c.couponReduce ? !1 : !0, c.couponId = a.value, d["0"].num = d.num = window.pageParam.price + c.couponReduce, c.scanPromote(!1)
	}, g.handleSure = function(a) {
		var b = this,
			c = $(a.target),
			d = window.subParam,
			e = window.pageParam;
		if (!c.hasClass("btn_disabled")) {
			if (!b.useCoupon && b.couponId && "0" === b.couponId) return utils.showConfirm({
				describeText: "您暂未使用任何店铺优惠券",
				sureText: "确认不使用",
				cancelText: "返回使用",
				sureFn: function() {
					b.useCoupon = !0, b.handleSure(a)
				}
			}), !1;
			if (b.promList && 0 == b.promoteId && !b.showed) return utils.showBubble("您的订单可以享受满减优惠，赶快选择优惠方案吧！"), b.showed = !0, !1;
			if (1 == b.curPtype && b.payPrice < 500) return utils.showBubble("您购买的商品总价过低，无法使用货到付款功能，请用在线付款下单!"), !1;
			if (b.pc < 0 && 1 != b.curPtype) return utils.showBubble("请选择在线支付方式"), !1;
			d.mt = b.curMtype, d.pt = b.curPtype, d.adid = e.adid, d.promId = b.promoteId ? b.promoteId : 0, d.pc = b.pc, d.comeFrom = e.comeFrom, d.t = +new Date, d.couponId = b.couponId ? b.couponId : 0, utils.showBubble("正在提交订单数据..."), c.addClass("btn_disabled"), utils.ajaxReq({
				url: window.basePath + "/cn/deal/makeOrder.xhtml?t=" + Math.random() + "&" + window.baseParam,
				dataType: "json",
				type: "POST",
				data: d
			}, function(a) {
				a.errCode ? 260 == a.errCode ? (utils.showBubble("登录超时"), c.removeClass("btn_disabled")) : (utils.showAjaxErr(a, "下单失败，请重试"), c.removeClass("btn_disabled")) : utils.payDeal(a, c)
			})
		}
	}, g.scanCoupon = function() {
		var a, b, c = window.pageParam,
			d = this,
			e = d.promoteIndex,
			f = c.coupon;
		0 != f.length && (a = $("#promote-tpl").html(), b = '<option packetPrice="0" value="0">不使用优惠券</option>', $.each(f, function(a, c) {
			b += "<option " + (0 == a ? "selected" : void 0) + ' value="' + c.id + '" packetPrice="' + c.amount + '">' + (c.amount / 100).toFixed(2) + "元优惠券</option>", 0 == a && (d.couponId = c.id, d.couponReduce = -parseInt(c.amount, 10))
		}), a = a.replace(/{#id#}/, "").replace(/{#evtTag#}/, "couponChange").replace(/{#optList#}/, b), $("#coupon-node").html(a).removeClass("qb_none"), e["0"].num = e.num = c.price + d.couponReduce < 1 ? 1 : c.price + d.couponReduce)
	}, g.scanPromote = function(a) {
		var b = window.pageParam,
			c = b.promote,
			d = [],
			e = this;
		if (a ? "1" !== b.comeFrom ? c.splice(0, 1) : c.length > 0 && (c[0].desc = "收藏商品减免运费", c[0].guide = !0) : (e.reduce = 0, e.isFreeFee = !1, e.promoteId = 0), c.length <= 0) return e.countPrice(), void 0;
		if ($.each(c, function(a, b) {
			var c = e.countPromote(b);
			c.show && (c.freight <= 0 ? b.freeFee = !0 : "", b.rm = c.payable, b.show = c.show, d.push(b), c = null)
		}), 0 == d.length) return e.promList && e.promList.parent().remove() && (e.promList = null), e.countPrice(), void 0;
		d = d.sort(function(a, b) {
			return a.rm > b.rm
		});
		var f = $("#promote-tpl").html(),
			g = "<option value='999999'>请选择店铺促销</option>";
		$.each(d, function(a, b) {
			g += "<option value='" + b.rm + "' " + (b.freeFee ? "freeFee='true'" : "") + " promId='" + b.id + "' " + (b.guide ? "selected" : "") + ">" + b.desc + "</option>", b.guide && (e.reduce = parseInt(b.rm, 10), e.isFreeFee = b.freeFee ? !0 : !1, e.promoteId = b.id)
		}), f = f.replace(/{#id#}/, "promote-list").replace(/{#evtTag#}/, "handlePromote").replace(/{#optList#}/, g), $("#promote-node").html(f).removeClass("qb_none"), g = null, e.promList = $("#promote-list"), e.countPrice()
	}, g.countPromote = function(rule) {
		var _this = this,
			cnds = rule.cnd,
			reduce, favor = 0,
			freight = parseInt(this.mtypeNode.val(), 10),
			pi = _this.promoteIndex;
		return $.each(cnds, function(seq, cnd) {
			"0" !== cnd && pi[seq].num >= parseInt(cnd, 10) && ("0" !== rule.feeCarriage[0] && (freight = 0 - freight), reduce = rule.reduce, $.each(reduce, function(index, item) {
				"0" !== item && "0.0" !== item && (favor = -(pi.num - eval(pi.num + pi[index].op + item)))
			}))
		}), {
			freight: freight,
			payable: favor,
			show: rule.guide ? !0 : 0 == favor ? !1 : !0
		}
	}, g.handlePromote = function(a) {
		var b = this,
			c = b.promList.val();
		if ("999999" == c) return b.reduce = 0, b.isFreeFee = !1, b.promoteId = 0, b.countPrice(), void 0;
		var d = a.options[a.selectedIndex];
		b.promoteId = d.getAttribute("promId");
		var e = d.getAttribute("freeFee");
		b.isFreeFee = e ? !0 : !1, b.reduce = parseInt(c, 10), b.countPrice()
	}, g.handleToAddr = function() {
		var a = new localStore("confirm");
		a.support() ? a.setValue(location.href, !1) : navigator.cookieEnabled && utils.setCookie("re_url_confirm", location.href), location.href = $("#goaddlist").attr("addr")
	}, g.changePayType = function(a) {
		return !a && this.curPtype ? (this.payTypeNode.removeClass("qb_none"), void 0) : (this.pc = a.value, void 0)
	}
}(mobile.o2ocn.deal), utils.namespace("mobile.o2ocn.index"), function() {
	var a = mobile.o2ocn.index;
	a.init = function(a) {
		this.initParam(a), this.bindEvent()
	}, a.initParam = function(a) {
		this.curTabIndex = a, this.tabCache = window.itemArray, this.tabImgCurNum = {
			1: 0,
			2: 0,
			3: 0,
			4: 0
		}, this.fTpl = $("#flow-tpl").html(), this.tpl = $("#flow-item-tpl").html(), this.clientHeight = $(window).height(), this.imgLoadQueue = [], this.renderTabContent()
	}, a.bindEvent = function() {
		var a = $(".fn_brand");
		$("#index-tab li").on("tap", $.proxy(this.handleTabChange, this)), a.length > 0 && this.loadScript(window.local ? window.basePath + "/assets/src/ui/scroll-multi.js" : "http://3glogo.gtimg.com/mobilelife/o2o/cn" + window.env + "/assets/src/ui/scroll-multi-min.js?t=20130509", function() {
			a.scrollImage({
				gapWidth: 3,
				loadSee: !0,
				showNum: 3.5
			})
		})
	}, a.handleTabChange = function(a) {
		for (var b, c = a.target;
		"LI" != c.tagName;) c = c.parentNode;
		c = $(c), b = c.attr("index"), c.hasClass("current") || (c.addClass("current").siblings().removeClass("current"), this.curTabIndex = b, $(window).off("scroll", $.proxy(this.scrollLoad, this)), this.renderTabContent(), a.preventDefault())
	}, a.renderTabContent = function() {
		var a = this.tabCache[this.curTabIndex],
			b = this;
		if (a) {
			var c = a.length;
			if (0 >= c) return $("#flow-container").html("<div class='qb_tac'>暂无数据</div>"), void 0;
			var d = [],
				e = [],
				f = [],
				g = [];
			if (10 > c) {
				$("#in-loading").addClass("qb_none");
				for (var h = 0; c > h; h++) {
					var i = Math.floor(h % 3);
					0 == i ? d.push(utils.strReplace(b.tpl, a[h])) : 1 == i ? e.push(utils.strReplace(b.tpl, a[h])) : 2 == i && f.push(utils.strReplace(b.tpl, a[h]))
				}
				b.tabImgCurNum[b.curTabIndex] = c
			} else {
				$("#in-loading").removeClass("qb_none");
				for (var h = 0; 9 > h; h += 3) d.push(utils.strReplace(b.tpl, a[h])), e.push(utils.strReplace(b.tpl, a[h + 1])), f.push(utils.strReplace(b.tpl, a[h + 2]));
				b.tabImgCurNum[b.curTabIndex] = 9, $(window).on("scroll", $.proxy(this.scrollLoad, this))
			}
			g.push(b.fTpl.replace(/{#itemList0#}/, d.join("")).replace(/{#itemList1#}/, e.join("")).replace(/{#itemList2#}/, f.join(""))), $("#flow-container").html(g.join("")), b.lazyLoad(), this.container = {
				ct0: $("#flow0"),
				ct1: $("#flow1"),
				ct2: $("#flow2")
			}, setTimeout(function() {
				window.scrollTo(0, 1)
			}, 200)
		}
	}, a.scrollLoad = function() {
		var a = window.pageYOffset,
			b = document.documentElement.scrollHeight,
			c = this.tabCache[this.curTabIndex].length;
		if (b - (a + this.clientHeight) <= 160) {
			for (var d = this.imgLoadQueue.length, e = d, f = d - 1; f >= 0; f--) this.imgLoadQueue[f] || (this.imgLoadQueue.splice(f, 1), e--);
			if (6 == e) return;
			for (var g = 0 == e ? 6 : 6 - e, h = this, i = 0; g > i && this.tabImgCurNum[this.curTabIndex] < c; i++) this.imgLoadQueue.push(this.tabCache[this.curTabIndex][this.tabImgCurNum[this.curTabIndex]]), this.tabImgCurNum[this.curTabIndex]++;
			$.each(this.imgLoadQueue, function(a, b) {
				var c = new Image;
				c.onload = function() {
					if (h.imgLoadQueue[a]) {
						var b = Math.floor(a % 3);
						h.container["ct" + b].append(utils.strReplace(h.tpl, h.imgLoadQueue[a])), h.imgLoadQueue.splice(a, 1, "")
					}
				}, c.src = h.imgLoadQueue[a] ? h.imgLoadQueue[a].imgUrl : b.imgUrl
			})
		}
		this.tabImgCurNum[this.curTabIndex] == c && ($("#in-loading").addClass("qb_none"), $(window).off("scroll", $.proxy(this.scrollLoad, this)))
	}, a.lazyLoad = function() {
		function a() {
			var a = $("img[lazy]");
			a.length > 0 ? a.each(function() {
				var a = $(this);
				this.onload = function() {
					a.removeAttr("lazy")
				}, a.attr("src", a.attr("lazy"))
			}) : (clearInterval(b.ptr), b.ptr = null)
		}
		var b = this;
		b.ptr && (clearInterval(b.ptr), b.ptr = null), b.ptr = setInterval(a, 100)
	}, a.loadScript = function(a, b) {
		var c = document.createElement("script");
		c.type = "text/javascript", c.onload = function() {
			b()
		}, c.src = a, document.getElementsByTagName("head")[0].appendChild(c)
	}
}(), utils.namespace("mobile.o2ocn.topic"), function() {
	var a = mobile.o2ocn.topic;
	a.init = function() {
		window.setShareListener(this.setShareData), window.topicType && 5 != window.topicType && 1 != window.topicType && 2 != window.topicType && 3 != window.topicType || (this.initDifTopic(), this.initParam())
	}, a.initParam = function() {
		1 != window.topicType && 2 != window.topicType && 5 != window.topicType && window.topicType ? 3 == window.topicType && (this.itemTpl = $("#show3_item_tpl").html(), this.itemContainer = $("#show3_container")) : (this.itemTpl = $("#show_item_tpl").html(), this.itemContainer = $("#show_container")), this.itemCache = window.itemArray && window.itemArray[1], this.clientHeight = $(window).height(), this.pSize = 4, this.curNum = 0, this.initRenderContent()
	}, a.initDifTopic = function() {
		if (window.lz) {
			var a, c, d, e, f = $(".mod_clipimg");
			f.length > 0 && (a = window.innerWidth, $.each(f, function(f, g) {
				g = $(g), tag = g.attr("tag"), "indexad" == tag ? (c = window.innerHeight - 75, e = a, g.width(a), g.height(c)) : $.os.ios || $.os.android ? "discount" == tag ? (e = a - 100, c = 160, g.width(e)) : (e = parseInt(a / 2, 10), c = 160, g.width(e)) : (e = 162, c = 160, g.width(e));
				var h = g.find("img"),
					i = h.attr("lazy");
				b(i, function(a, b) {
					e / c >= a / b ? (d = parseInt(b / a * e, 10), h.css({
						width: e,
						height: d,
						"margin-top": parseInt((c - d) / 2, 10)
					})) : (d = parseInt(a / b * c, 10), h.css({
						width: d,
						height: c,
						"margin-left": parseInt((e - d) / 2, 10)
					}))
				}, function() {
					h.attr("src", i).removeAttr("lazy")
				})
			}))
		}
	};
	var b = function() {
			var a = [],
				b = null,
				c = function() {
					for (var b = 0; b < a.length; b++) a[b].end ? a.splice(b--, 1) : a[b]();
					!a.length && d()
				},
				d = function() {
					clearInterval(b), b = null
				};
			return function(d, e, f, g) {
				var h, i, j, k, l, m = new Image;
				return m.src = d, m.complete ? (e(m.width, m.height), f && f(m.width, m.height), void 0) : (i = m.width, j = m.height, h = function() {
					k = m.width, l = m.height, (k !== i || l !== j || k * l > 1024) && (e(k, l), h.end = !0)
				}, h(), m.onerror = function() {
					g && g(), h.end = !0, m = m.onload = m.onerror = null
				}, m.onload = function() {
					f && f(m.width, m.height), !h.end && h(), m = m.onload = m.onerror = null
				}, h.end || (a.push(h), null === b && (b = setInterval(c, 40))), void 0)
			}
		}();
	a.initRenderContent = function() {
		var a = this;
		if (a.itemCache) {
			var b = a.itemCache.length;
			0 >= b || (a.renderItemContent(0), a.curNum < b && ($("#loading").removeClass("qb_none"), $(window).on("scroll", $.proxy(this.scrollLoad, this))))
		}
	}, a.renderItemContent = function(a) {
		var b, c = this,
			d = c.itemCache.length,
			e = [],
			f = a + c.pSize;
		f > d && (f = d);
		for (var g = a; f > g; g++) b = c.itemCache[g], b.discountHtml = "", b.discount && (b.discountHtml = "<div class='mod_corner'>" + b.discount + "<sup>折</sup></div>"), e.push(utils.strReplace(c.itemTpl, b));
		c.curNum = f, a ? c.itemContainer.append(e.join("")) : c.itemContainer.html(e.join(""))
	}, a.scrollLoad = function() {
		var a = this,
			b = window.pageYOffset,
			c = document.documentElement.scrollHeight,
			d = a.itemCache.length;
		c - (b + a.clientHeight) <= 160 && a.renderItemContent(a.curNum), a.curNum == d && ($("#loading").addClass("qb_none"), $(window).off("scroll", $.proxy(this.scrollLoad, this)))
	}, a.setShareData = function() {
		var a = document.querySelector(".fn_titles") || document.querySelector(".fn_readme"),
			b = a ? a.innerText : "";
		return {
			img: $(".lay_page_wrap").find("img")[0].src,
			content: b
		}
	}
}(), utils.namespace("mobile.o2ocn.detail"), function() {
	var a = mobile.o2ocn.detail;
	a.init = function() {
		this.initParam(), this.bindEvent(), this.initNewItem()
	}, a.initParam = function() {
		window.totalStock = parseInt(window.totalStock), this.pCache = {}, this.curSelect = {}, this.buyNum = 1, this.curStock = window.totalStock ? parseInt(window.totalStock) : 0, this.matchProperty = [], this.selectedCount = 0, this.propList = [], this.buyProperty = "", this.errorMsg = "", $("#buyNum").val(1), this.infoNode = $("#detail-info"), this.infoNode[0].scrollHeight > 200 && $("#info-arrow").removeClass("qb_none"), this.initStock(), this.curStock > 0 && window.sku.length > 0 && this.initProperty()
	}, a.initStock = function() {
		var a = this;
		$.each(window.availSku, function(b) {
			a.propList.push(b)
		})
	}, a.initProperty = function() {
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
	}, a.buildIndex = function(a) {
		var b = !1;
		return this.propList.forEach(function(c) {
			return -1 != c.indexOf(a) ? (b = !0, !1) : void 0
		}), b
	}, a.bindEvent = function() {
		window.totalStock > 0 && (this.buyNode = $("#buy-now"), $(".mod_property span").on("tap", $.proxy(this.handleProperty, this)), $("#buyNum").on("input", $.proxy(this.handleInput, this)), $(".minus").on("touchstart", $.proxy(this.handleBtnNum, this)), $(".plus").on("touchstart", $.proxy(this.handleBtnNum, this)), this.buyNode.on("click", $.proxy(this.handleBuy, this)), "false" === window.isNew && $("#add-cart").on("click", $.proxy(this.addCart, this))), $("#info-arrow").on("click", $.proxy(this.handleOpen, this)), $(".mod_slider").scroll({
			contentWrap: "#detail-slider",
			autoGen: !1,
			loadImg: !0,
			margin: 20,
			autoTime: 3e3,
			autoAdapt: !0,
			offset: !0,
			onInit: function() {
				this.param.container.css("visibility", "visible")
			}
		}), $("#viewPic").on("click", $.proxy(this.viewPic, this))
	}, a.handleBuy = function() {
		if (this.buyNode.hasClass("btn_strong")) {
			if (window.totalStock > 0 && window.sku.length > 0 && !this.buyProperty) return utils.showBubble(this.errorMsg), void 0;
			if (window.wxInfo && "true" !== window.wxInfo.isFocus) return business.focus("购买商品，需要关注该商户，是否立即关注?", $.proxy(a.handleBuy, this)), void 0;
			$("#attr").val(encodeURIComponent(this.buyProperty)), $("#bc").val(this.buyNum), document.detailForm.submit()
		}
	}, a.addCart = function() {
		if (window.totalStock > 0 && window.sku.length > 0 && !this.buyProperty) return utils.showBubble(this.errorMsg), void 0;
		var a = this,
			b = {
				ic: $("#ic").val(),
				attr: this.buyProperty,
				bc: this.buyNum,
				t: (new Date).getTime()
			};
		utils.ajaxReq({
			url: window.basePath + "/cn/cmdy/add.xhtml?" + window.baseParam,
			dataType: "json",
			type: "POST",
			data: b
		}, function(b) {
			b.errCode ? utils.showConfirm({
				describeText: b.msgType ? b.errMsg : "加入购物车失败，请稍候再试",
				sureText: "重试",
				cancelText: "取消",
				sureFn: function() {
					a.addCart()
				}
			}) : utils.showConfirm({
				describeText: "添加成功！",
				sureText: "去购物车结算",
				cancelText: "再逛逛",
				sureFn: function() {
					location.href = window.basePath + "/cn/cmdy/list.xhtml?" + window.baseParam
				}
			})
		})
	}, a.handleOpen = function() {
		this.infoNode.parent().toggleClass("fold")
	}, a.handleProperty = function(a) {
		var b = $(a.target);
		if (!b.hasClass("current") && !b.hasClass("disabled")) {
			b.addClass("current").siblings().removeClass("current");
			var c = b.parent();
			this.loopProp(b.attr("data-value"), c.attr("index"), c.attr("skuName"))
		}
	}, a.loopProp = function(a, b, c) {
		var d = [],
			e = !0,
			f = this;
		f.matchProperty[b] || f.selectedCount++, f.matchProperty[b] = a, d[b] = a, $.each(window.sku, function(a, b) {
			var d, g, h = b.pName,
				i = b.pList,
				j = f.pNodeList.eq(a),
				k = j.find("span");
			h !== c && $.each(i, function(a, b) {
				d = h + ":" + b, g = [], g.push(d), $.each(f.matchProperty, function(a, b) {
					b && b.match(/.*(?=:)/)[0] != h && g.push(b)
				}), $.each(f.propList, function(a, b) {
					return e = !0, e = g.every(function(a) {
						return -1 !== b.indexOf(a)
					}), e ? !1 : void 0
				}), e ? k.eq(a).removeClass("disabled") : k.eq(a).addClass("disabled")
			})
		}), f.statisticSelected()
	}, a.statisticSelected = function() {
		var a, b, c = this;
		if (c.selectedCount == window.sku.length) {
			var d = c.matchProperty.join("|");
			c.buyProperty = d, a = window.availSku[d], a && ($("#buyNum").val(1), b = a.stockCount, $("#stock-num").html(b), c.curStock = parseInt(b, 10), $("#price").html("&yen;" + (a.stockPrice / 100).toFixed(2)))
		}
	}, a.handleInput = function(a) {
		var b = $(a.target),
			c = b.val();
		c && isNaN(c) ? (b.val(1), utils.showBubble("购买数量只能输入数字，已自动修改")) : c && 0 >= c ? (b.val(1), utils.showBubble("至少购买一件")) : /\d+\.\d*/.test(c) ? (b.val(parseInt(c, 10)), utils.showBubble("购买数量只能输入整数")) : c > 50 ? this.curStock > 50 ? (utils.showBubble("单次购买数量不可超过50件，已自动修改"), b.val(50), this.buyNum = 50) : (utils.showBubble("购买数量超过库存限制，已自动修改"), b.val(this.curStock), this.buyNum = this.curStock) : c > this.curStock ? (utils.showBubble("购买数量超过库存限制，已自动修改"), b.val(this.curStock), this.buyNum = this.curStock) : this.buyNum = c
	}, a.handleBtnNum = function(a) {
		var b = $(a.target),
			c = b.attr("tag"),
			d = $("#buyNum"),
			e = 1 * d.val();
		if (e) {
			if (isNaN(e)) d.val(1);
			else {
				if (1 == e && "sub" == c) return utils.showBubble("至少购买一件"), void 0;
				if ("add" == c) {
					if (this.curStock > 50 && e >= 50) return utils.showBubble("单次购买数量不可超过50件"), void 0;
					if (e == this.curStock) return utils.showBubble("购买数量不能超过库存限制"), void 0
				}
			}
			e = "sub" == c ? e - 1 : 1 * e + 1, d.val(e), this.buyNum = e
		}
	}, a.initNewItem = function() {
		if ("false" !== window.isNew && window.totalStock) {
			var a = 1 * window.bookBeginTime,
				b = 1 * window.bookEndTime,
				c = 1 * window.st,
				d = $("#buy-now"),
				e = new Date(a);
			a > c ? d.html("即将开售/" + (e.getMonth() + 1) + "月" + e.getDate() + "日开始预订").removeClass("btn_strong") : c > b && d.html("预订结束").removeClass("btn_strong"), d.removeClass("qb_none")
		}
	}, a.viewPic = function(a) {
		function b() {
			e ? c() : utils.showConfirm({
				describeText: "您当前使用的是移动网络，查看大图需要消耗一定的流量，是否继续？",
				sureFn: c,
				sureText: "继续",
				cancelFn: function() {
					f.removeClass("clicking")
				}
			})
		}
		function c() {
			var a, b, c, e = new localStore("pic"),
				g = e.getValue(),
				h = $("#ic").val(),
				i = +new Date;
			if (g = g ? JSON.parse(g) : "", a = g ? g[h] : "", a && (b = a.time, 1728e5 >= i - b ? c = a.imgList : (delete g[h], e.setValue(g, !0))), c) d(c);
			else {
				var j = "";
				f.html("加载图片中，请稍后");
				var k = setInterval(function() {
					f.html("加载图片中，请稍后" + j), j += ".", j.length >= 4 && (j = "")
				}, 300);
				utils.ajaxReq({
					url: window.basePath + "/cn/item/moreImg.xhtml",
					dataType: "json",
					data: {
						ic: h
					}
				}, function(a) {
					if (f.html("商品相册").removeClass("clicking"), clearInterval(k), a.errCode) utils.showAjaxErr(a, "拉取详情图片失败");
					else {
						c = a.data, d(c);
						var b = {};
						b[h] = {
							time: i,
							imgList: c
						}, e.setValue(b, !0), b = null
					}
				}, function() {
					clearInterval(k), f.removeClass("clicking").html("商品相册"), utils.showBubble("拉取详情图片失败")
				})
			}
		}
		function d(a) {
			f.removeClass("clicking"), WeixinJSBridge.invoke("imagePreview", {
				current: a[0],
				urls: a
			})
		}
		var e = !0,
			f = $(a.target);
		f.hasClass("clicking") || WeixinJSBridge && WeixinJSBridge.invoke("getNetworkType", {}, function(a) {
			e = -1 != a.err_msg.indexOf("wifi"), f.addClass("clicking"), b()
		})
	}
}(), $.extend(loopImage.prototype, {
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
}, utils.namespace("mobile.o2ocn.logistic"), function() {
	var a = mobile.o2ocn.logistic;
	a.init = function() {
		this.can = new canvasLoading, this.can.showLoading(), this.loadSudi()
	}, a.loadSudi = function() {
		utils.ajaxReq({
			type: "get",
			url: window.logisUrl,
			dataType: "jsonp"
		}, function() {})
	}
}(), $.extend(canvasLoading.prototype, {
	showLoading: function() {
		function a() {
			b.save(), b.clearRect(0, 0, 50, 50), b.translate(25, 25), c = 13 === ++c ? c - 12 : c;
			for (var a = 1; 12 >= a; a++) b.beginPath(), 1 == a ? b.rotate(c * d) : b.rotate(d), b.arc(0, -10, .2 * a, 0, 2 * Math.PI, !0), b.closePath(), b.fill();
			b.restore()
		}
		this.cNode && this.closeLoading();
		var b = this.canvasNode.getContext("2d");
		this.cNode = b, b.clearRect(0, 0, 50, 50), b.fillStyle = "transparent", b.fillRect(0, 0, 50, 50), b.fillStyle = "#000000";
		var c = 0,
			d = Math.PI / 6;
		this.ptr = setInterval(a, 60), $(this.canvasNode).removeClass("qb_none")
	},
	closeLoading: function() {
		clearInterval(this.ptr), this.cNode.clearRect(0, 0, 50, 50), $(this.canvasNode).addClass("qb_none")
	}
}), utils.namespace("mobile.o2ocn.myorder"), function() {
	var a = mobile.o2ocn.myorder;
	a.init = function() {
		return "0" !== window.dealData.errCode ? ($("#itemList").html("<div class='qb_tac mod_color_weak qb_p10'>哎哟,系统跑累了,请歇会再来看看吧!</div>"), void 0) : (this.initParam(), this.bindEvent(), void 0)
	}, a.initParam = function() {
		this.evtConfig = {
			click: {
				showDrawList: this.displayDrawList,
				showBookList: this.displayBookList,
				confirmRecv: this.confirmReceive,
				toPay: this.toPay,
				loadMore: this.loadMore
			},
			change: {
				changePay: this.changePay
			}
		}, this.curTabIndex = utils.getQuery("index") || 1, this.curTabPageNum = 1, this.itemListTpl = $("#item-tpl").html(), this.containerTpl = $("#container-tpl").html(), this.drawbackTpl = $("#drawback-tpl").html(), this.booknewTpl = $("#booknew-tpl").html(), this.noneTpl = $("#none-tpl").html(), this.tabNode = $("#tab-list"), this.textMap = {
			1: {
				tabName: "待付款",
				data: "waitPayData"
			},
			2: {
				tabName: "待收货",
				data: "waitRecvData"
			},
			3: {
				tabName: "已结束",
				data: "end"
			}
		}, this.statusMap = {
			1: "待付款",
			2: "待发货",
			3: "待收货",
			4: "已关闭",
			5: "已拒签",
			6: "交易成功",
			7: "退款中",
			8: "退款完成",
			0: "未知状态",
			14: "待店员确认",
			15: "门店取货成功",
			16: "新款预定待发货",
			17: "待卖家确认退货",
			18: "待买家退货",
			19: "待卖家确认收货",
			20: "取消退货",
			21: "退货完成"
		}, this.tabNode.children().eq(this.curTabIndex - 1).addClass("current"), this.curTabMaxPage = Math.ceil(window.dealData[this.textMap[this.curTabIndex].data].totalNum / 10), this.emptyHeight = window.innerHeight - 150, this.loadNode = $("#load-more"), this.dback = utils.getQuery("dback") || !1, this.renderTabContent(), setTimeout(function() {
			!$.os.android && (document.body.scrollTop = 45)
		}, 200)
	}, a.bindEvent = function() {
		this.tabNode.find("div").on("tap", $.proxy(this.handleTabChange, this)), $(".lay_page")[0].addEventListener("click", this, !1), document.body.addEventListener("change", this, !1)
	}, a.handleEvent = function(a) {
		var b = a.type,
			c = a.target,
			d = c.getAttribute("evtTag");
		d && this.evtConfig[b] && this.evtConfig[b][d] && this.evtConfig[b][d].call(this, c)
	}, a.renderTabContent = function(a) {
		var b = this.curTabIndex,
			c = a || window.dealData[this.textMap[b].data].dealList,
			d = [],
			e = window.dealData.drawback.dealList,
			f = window.dealData.newItems.dealList;
		return this.curTabMaxPage <= 1 ? this.loadNode.addClass("qb_none") : this.loadNode.html("点击加载更多").removeClass("qb_none"), 0 == c.length ? (2 == b ? (e.length > 0 && d.push(this.genDrawbackList(e)), f.length > 0 && d.push(this.genBookList(f)), d.length > 0 ? ($("#itemList").html(d.join("")), this.drawNode = $("#drawbackList"), this.bookNewNode = $("#bookList"), this.showDrawbackAni(b)) : $("#itemList").html(this.noneTpl.replace(/{#height#}/g, this.emptyHeight).replace(/{#text#}/, this.textMap[b].tabName))) : $("#itemList").html(this.noneTpl.replace(/{#height#}/g, this.emptyHeight).replace(/{#text#}/, this.textMap[b].tabName)), void 0) : (d.push("<div id='normalList'>" + this.genOrderList(c).join("") + "</div>"), a || (d.push(this.genDrawbackList(e)), d.push(this.genBookList(f))), a ? $("#normalList").append(d.join("")) : $("#itemList").html(d.join("")), this.drawNode = $("#drawbackList"), this.bookNewNode = $("#bookList"), this.showDrawbackAni(b), void 0)
	}, a.genDrawbackList = function(a) {
		var b = [];
		return 2 == this.curTabIndex && a && a.length > 0 && (b.push(this.drawbackTpl.replace(/{#nodeId#}/, "drawback-node")), b.push("<div class='fn_tuikuan'><div id='drawbackList' class='fn_tuikuan_list " + (this.dback ? "animate" : "qb_none") + "'>" + this.genOrderList(a).join("") + "</div></div>")), b.join("")
	}, a.genBookList = function(a) {
		var b = [];
		return 2 == this.curTabIndex && a && a.length > 0 && (b.push(this.booknewTpl.replace(/{#nodeId#}/, "booknew-node")), b.push("<div class='fn_tuikuan'><div id='bookList' class='fn_tuikuan_list qb_none'>" + this.genOrderList(a).join("") + "</div></div>")), b.join("")
	}, a.genOrderList = function(a) {
		for (var b, c, d, e, f, g, h = [], i = 0, j = a.length; j > i; i++) {
			b = a[i], c = b.il, d = [], e = 0, f = "";
			for (var k = 0, l = c.length; l > k; k++) {
				g = c[k];
				var m = g.pic,
					n = m.substr(m.lastIndexOf("."), m.length);
				m = m.replace(n, ".80x80" + n), d.push(utils.strReplace(this.itemListTpl, {
					pic: m,
					singleShow: 1 == l ? "" : "qb_none",
					itemName: g.itn,
					attr: g.at ? '<p class="qb_fs_s ui_color_weak">' + g.at + "</p>" : "",
					singleLi: "single qb_mb10 qb_bfc bfc_f",
					imgClass: "bfc_f"
				})), f += g.dsc + "-", e += parseInt(g.din, 10)
			}
			b.sdc = f.substr(0, f.length - 1), b.receive_show = 62 == b.ds && 1 == b.pt ? "" : "qb_none", b.pay_show = 60 == b.ds ? "" : "qb_none", b.itemPicList = d.join(""), b.totalCount = e, b.stateName = this.statusMap[b.sc], b.totalPay = (b.dt / 100).toFixed(2), b.show = 61 == b.ds || 71 == b.ds ? "" : "qb_none", h.push(utils.strReplace(this.containerTpl, b)), d = null
		}
		return h
	}, a.handleTabChange = function(a) {
		for (var b, c = a.target;
		"DIV" != c.tagName;) c = c.parentNode;
		c = $(c), b = c.attr("index"), c.hasClass("current") || (c.addClass("current").siblings().removeClass("current"), this.curTabPageNum = 1, this.curTabIndex = b, this.curTabMaxPage = Math.ceil(window.dealData[this.textMap[b].data].totalNum / 10), this.renderTabContent(), this.curTabMaxPage > 1 && this.loadNode.html("点击加载更多订单").removeClass("qb_none").css("opacity", 1), location.hash = "", this.dback = !1, a.preventDefault())
	}, a.loadMore = function() {
		var a = this;
		a.curTabPageNum++, a.loadNode.html("加载中..."), utils.ajaxReq({
			url: window.basePath + "/cn/my/next.xhtml?" + window.baseParam,
			dataType: "json",
			data: {
				currTab: a.curTabIndex,
				pageNo: a.curTabPageNum
			}
		}, function(b) {
			a.loadNode.html("点击加载更多订单"), b.errCode ? 260 == b.errCode ? (a.curTabPageNum--, utils.showBubble("登录超时")) : (utils.showAjaxErr(b, ""), a.curTabPageNum--, a.loadNode.html("加载失败，请重试")) : (a.curTabPageNum == a.curTabMaxPage && a.loadNode.html("没有更多记录了").animate({
				opacity: 0
			}, 200, "ease-out", function() {
				$(this).addClass("qb_none")
			}), this.dback = !1, b.dealList.length > 0 && a.renderTabContent(b.dealList))
		})
	}, a.displayDrawList = function(a) {
		var b = $(a);
		this.drawNode.toggleClass("qb_none"), b.hasClass("arrow_up") ? b.removeClass("arrow_up").addClass("arrow_down") : b.removeClass("arrow_down").addClass("arrow_up")
	}, a.displayBookList = function(a) {
		var b = $(a);
		this.bookNewNode.toggleClass("qb_none"), b.hasClass("arrow_up") ? b.removeClass("arrow_up").addClass("arrow_down") : b.removeClass("arrow_down").addClass("arrow_up")
	}, a.showDrawbackAni = function(a) {
		var b = this;
		if (b.dback && 2 == a) {
			var c = 0,
				d = $("#drawback-node");
			if (0 != d.length) var e = setInterval(function() {
				document.body.scrollTop = c + 100, c += 100, c >= d.offset().top - 125 && (clearInterval(e), b.drawNode.addClass("show"))
			}, 1)
		}
	}, a.toPay = function(a, b) {
		b = b ? b : 0;
		var c = {
			0: window.basePath + "/cn/pay/topay.xhtml",
			1: window.basePath + "/cn/minipay/generateMinipay.xhtml?" + window.baseParam
		};
		c[b] && utils.ajaxReq({
			url: c[b],
			data: {
				dc: a.getAttribute("dc"),
				t: +new Date
			},
			dataType: "json"
		}, function(c) {
			c.errCode ? 260 == c.errCode ? utils.showBubble("登录超时") : utils.showAjaxErr(c, "请求遇到错误，请重试。") : b ? (c.data.payType = 0, c.data.payChannel = 1, c.data.dealCode = a.getAttribute("dc"), utils.payDeal(c)) : location.href = c.data.payUrl
		}, function() {
			utils.showBubble("请求遇到了点问题，请重试。")
		})
	}, a.confirmReceive = function(a) {
		utils.ajaxReq({
			url: a.getAttribute("url"),
			dataType: "json"
		}, function(b) {
			b.errCode ? 260 == b.errCode ? utils.showBubble("登录超时") : utils.showAjaxErr(b, "确认收货失败，请重试。") : (utils.showBubble("确认收货成功"), $(a).addClass("qb_none"))
		}, function() {
			utils.showBubble("请求遇到了点问题，请重试。")
		})
	}, a.changePay = function(a) {
		var b = parseInt(a.value, 10); - 1 != b && (utils.showBubble(0 == b ? "正在前往财付通支付中心，请稍后" : "正在调用微信支付"), this.toPay(a, b))
	}
}();