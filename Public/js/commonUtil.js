/*一些共用函数和js获取URL参数的方式*/
function getUrlParam(key){
	var reg = new RegExp("(^|&)" + key + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
function getUrlHash(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)", "i");
    var r = location.hash.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
String.prototype.getParam = function(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)");
    var r = this.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
//统计字符串长度（中文字符算一个，英文算2个）
function getStrLength(str) {  
	var cArr = str.match(/[^\x00-\xff]/ig);  
	return str.length + (cArr == null ? 0 : cArr.length);  
}

/*一些共用函数和arttemplate的帮助*/
// artTemplate模板扩展（格式化时间|转2位小数）
template.helper('dateFormat', function (date, format) {
    format = getLocalTime(date,true).replace(/-/g,".");
    return format;
});
// 整型转2位小数
template.helper('toFixed2', function (data, format) {
    format = data.toFixed(2);
    return format;
});
// 订单状态函数（新改版的订单状态2015/08/18 17:17:17）
template.helper('getOrderStatus', function (orderself, format) {
	if (orderself.status_flag == 0) {
		// 正常订单流程normal_status
		if (orderself.normal_status == -2) {
			format = "商家发货超时";
		} else if (orderself.normal_status == -1) {
			format = "支付超时";
		} else if (orderself.normal_status == 0) {
			format = "待付款";
		} else if (orderself.normal_status == 1) {
			format = "已付款，等待卖家发货";
		} else if (orderself.normal_status == 2) {
			format = "已发货，待确认";
		} else if (orderself.normal_status == 3) {
			format = "交易成功，待评价";
		} else if (orderself.normal_status == 4) {
			format = "交易成功，已评价";
		} else {
			format = "暂时无法获取状态";
		}
	} else if (orderself.status_flag == 1) {
		// 退款订单流程refund_status
		if (orderself.refund_status == 1) {
			format = "申请退单中";
		} else if (orderself.refund_status == 2) {
			format = "商家同意退单，处理中";
		} else if (orderself.refund_status == 3) {
			format = "商家拒绝退单，请致电协商";
		} else if (orderself.refund_status == 4) {
			format = "已退单";
		} else if (orderself.refund_status == 5) {
			format = "退款关闭";
		} else {
			format = "暂时无法获取状态";
		}
	} else {
		format = "暂时无法获取状态";
	}
    return format;
});
// 店铺主页URL地址
template.helper('shopIndexURL', function(sid) {
	var url = "/weact/WeMall/Store/storeIndex/sid/" + sid;
	return url;
});
// 云总店商品URL地址
template.helper('productDetailURL', function(proinfo){
	var url = "/weact/Home/ProductView/productShow/e_id/" + proinfo.e_id + "/nav_id/" + proinfo.nav_id + "/product_id/" + proinfo.product_id;
	return url;
});

/*业务逻辑状态函数*/
// 获取订单状态（中文描述）
function orderStatus(statusflag, normalstatus, refundstatus, withhtml) {
	var html = withhtml || 0; // 是否需要html格式
	var orderstatus = ""; // 订单状态
	if (html) {
		if(flagstatus == 0 && normalstatus == 0) {
			orderstatus = "<font style='color:#F34;'>待支付</font>";
		} else if(flagstatus == 0 && normalstatus == 1) {
			orderstatus = "<font style='color:#449103;'>已支付|待发货</font>";
		} else if(flagstatus == 0 && normalstatus == 2) {
			orderstatus = "<font style='color:#449103;'>已发货|待签收</font>";
		} else if(flagstatus == 0 && normalstatus == 3) {
			orderstatus = "<font style='color:#449103;'>已签收|待评价</font>";
		} else if(flagstatus == 0 && normalstatus == 4) {
			orderstatus = "<font style='color:#F34;'>已评价</font>";
		} else if(flagstatus == 1 && refundstatus == 1) {
			orderstatus = "<font style='color:#F34;'>退款申请中</font>";
		} else if(flagstatus == 1 && refundstatus == 2) {
			orderstatus = "<font style='color:#F34;'>商家同意退款</font>";
		} else if(flagstatus == 1 && refundstatus == 3) {
			orderstatus = "<font style='color:#F34;'>商家拒绝退款</font>";
		} else if(flagstatus == 1 && refundstatus >= 4) {
			orderstatus = "<font style='color:#666;'>已关闭</font>";
		} else {
			orderstatus = "<font style='color:#666;'>未知状态</font>";
		}
	} else {
		if(flagstatus == 0 && normalstatus == 0) {
			orderstatus = "待支付";
		} else if(flagstatus == 0 && normalstatus == 1) {
			orderstatus = "已支付|待发货";
		} else if(flagstatus == 0 && normalstatus == 2) {
			orderstatus = "已发货|待签收";
		} else if(flagstatus == 0 && normalstatus == 3) {
			orderstatus = "已签收|待评价";
		} else if(flagstatus == 0 && normalstatus == 4) {
			orderstatus = "已评价";
		} else if(flagstatus == 1 && refundstatus == 1) {
			orderstatus = "退款申请中";
		} else if(flagstatus == 1 && refundstatus == 2) {
			orderstatus = "商家同意退款";
		} else if(flagstatus == 1 && refundstatus == 3) {
			orderstatus = "商家拒绝退款";
		} else if(flagstatus == 1 && refundstatus >= 4) {
			orderstatus = "已关闭";
		} else {
			orderstatus = "未知状态";
		}
	}
	return orderstatus;
}

// 获取订单支付状态
function orderPayMethod(paymethod, withhtml) {
	var html = withhtml || 0; // 是否需要html格式
	var method = ""; // 订单状态
	if (html) {
		if (paymethod == 0) {
			method = "<font style='color:#F34;'>未选择</font>";
		} else if (paymethod == 1) {
			method = "<font style='color:#449103;'>现金支付</font>";
		} else if (paymethod == 2) {
			method = "<font style='color:#449103;'>微信支付</font>";
		} else if (paymethod == 3) {
			method = "<font style='color:#449103;'>刷卡支付</font>";
		} else {
			method = "<font style='color:#666;'>其他类型</font>";
		}
	} else {
		if (paymethod == 0) {
			method = "未选择";
		} else if (paymethod == 1) {
			method = "现金支付";
		} else if (paymethod == 2) {
			method = "微信支付";
		} else if (paymethod == 3) {
			method = "刷卡支付";
		} else {
			method = "其他类型";
		}
	}
	return method;
}

/*TX定义的util*/
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
					
					/*node.animate({
						opacity : 0
					}, 500, "ease", function() {
						$(this).addClass("qb_none").removeAttr("style");
					});*/
					//node.css("opacity",0);
					//node.addClass("qb_none").removeAttr("style");
					
					/*c.animate({
						opacity: 0
					}, 500, "ease", function() {
						$(this).addClass("qb_none").removeAttr("style")
					})*/
					
					c.css("opacity",0);
					c.addClass("qb_none").removeAttr("style");
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