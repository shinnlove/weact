// 确认收货按钮点击后
$("body").on("click", ".completeOrder", function(e) {
	e.preventDefault();
	if (confirm("请在真正收到货的时候才确认收货\n是否确定？")) {
		var id = $(this).data("id");
		$.getJSON("completeOrder.json?orderId=" + id, function(data) {
			if (data.status == "0") {
				mobileAlert("确认收货成功");
				setTimeout(function() {
					location.reload();
				}, 1500)
			}
		});
	}
});

// 查看快递信息按钮点击
$(".viewExpress").on("click", function() {
	$(this).hide();
	$(".expressItem").html('<div class="loading-bottom">努力加载中...</div>')
	var id = $(this).data("id"),
		code = $(this).data("code");
	$.ajax({
		url: "http://api.ickd.cn/",
		data: "id=" + baseOption.ickdID + "&secret=" + baseOption.ickdKey + "&com=" + id + "&nu=" + code + "&callback=expressCallback",
		dataType: "jsonp",
		success: function(data) {
			console.log(data)
			expressCallback(data);
		}
	});
});

// 快递回调函数
function expressCallback(data) {
	var dataStr = '';
	if (data.status == "0") {
		dataStr += data.message ? data.message : "暂时未跟踪到物流信息，请联系导购";
	} else {
		$.each(data.data, function(i, e) {
			dataStr += '<div class="time">' + e.time + '</div>' + '<div class="msg">' + e.context + '</div>'
		});
	}
	$(".expressItem").empty().append(dataStr);
}

// 微信支付回跳
if ($("#pageFrom").val() == "wechat") {
	$("#cover").show();
	var openId = $("#payOpenId").val(),
		orderId = $("#payOrderId").val();
	Zepto.getJSON("wechatJsPayWithOpenId.json?openId=" + openId + "&orderId=" + orderId, function(data) {
		if (data.status == "0") {
			var payJson = data.result.requestJson;
			var onBridgeReady = function() {
					var payParam = {
						"appId": payJson.appId,
						"timeStamp": payJson.timeStamp,
						"nonceStr": payJson.nonceStr,
						"package": payJson.package_,
						"signType": payJson.signType,
						"paySign": payJson.paySign
					}
					WeixinJSBridge.invoke('getBrandWCPayRequest', payParam, function(res) {
						$("#cover").hide();
						if (res.err_msg == "get_brand_wcpay_request:ok") {
							location.href = "payOrderResult.htm?orderId=" + orderId;
						}
					});
				}
			if (typeof WeixinJSBridge == "undefined") {
				document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
			} else {
				onBridgeReady();
			}
		} else {
			mobileAlert(data.errmsg ? data.errmsg : "系统繁忙，请稍后再试");
		}
	});
}

// 关闭订单
var _changeBox = $(".mobilePrompt");
$("body").on("click", ".closeOrder", function() {
	// 关闭订单字样点击后
	_changeBox.show(); // 弹出关闭订单输入框
}).on("click", ".cancelBtn", function(e) {
	// 取消按钮点击
	_changeBox.hide(); // 隐藏关闭订单输入框
}).on("click", ".changeBtn", function(e) {
	// 确认点击后
	var id = $(this).data("id"),
		comment = _changeBox.find(".comment").val(),
		name = $("#cName").val();
	$.getJSON("closeOrder.json?orderId=" + id + "&closePersonName=" + name + "&closeComment=" + comment, function(data) {
		if (data.status == "0") {
			_changeBox.hide();
			mobileAlert("订单关闭成功");
			setTimeout(function() {
				location.href = "getOrderListOfCustomer.htm?salesId=" + getUrlParam("salesId");
			}, 1500);
		} else {
			mobileAlert(data.errmsg ? data.errmsg : "系统繁忙，请稍后再试");
		}
	});
});