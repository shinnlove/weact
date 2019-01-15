$(function() {
	$("#sendMethod .item").on("click", function(e) {
		var _val = $(this).find("input[name=delivery]").val();
		if (_val == "1") {
			if (sessionStorage.receiveAddress && sessionStorage.receiveAddressId) {
				$("#gotoPay").removeAttr("disabled");
			} else {
				$("#gotoPay").attr("disabled", "disabled");
			}
			$("#addressSel").closest(".selection").show();
			$("#recerverMsg").hide();
		} else {
			$("#gotoPay").removeAttr("disabled");
			$("#addressSel").closest(".selection").hide();
			$("#recerverMsg").show();
		}
	});
	
	if (sessionStorage.receiveAddress && sessionStorage.receiveAddressId) {
		$('#addressSel .link').html(sessionStorage.receiveAddress).data('id', sessionStorage.receiveAddressId);
		$("#gotoPay").removeAttr("disabled");
	}
	if (sessionStorage.usedCoupon) {
		var usedCoupon = JSON.parse(sessionStorage.usedCoupon);
		$("#couponSel").data("id", usedCoupon.id).find(".link").html(usedCoupon.name + " ( -" + parseFloat(usedCoupon.value).toFixed(2) + "元 )");
	}
	if (sessionStorage.confirmOrderMsg) {
		$("input[name='payType'][value='" + sessionStorage.confirmOrderMsg.getParam("pay") + "']").attr("checked", true);
		var delivery = sessionStorage.confirmOrderMsg.getParam("delivery");
		if (delivery == "2") {
			$("input[name='delivery'][value='2']").attr("checked", true).closest(".item").trigger("click");
		} else {
			$("input[name='delivery'][value='1']").attr("checked", true);
		}
		$("input.rName").val(sessionStorage.confirmOrderMsg.getParam("rName"));
		$("input.rMobile").val(sessionStorage.confirmOrderMsg.getParam("rMobile"));
	}
	if (sessionStorage.stockInfo) {
		var stockInfo = JSON.parse(sessionStorage.stockInfo),
			stockHtml = "";
		var shoppingListArray = [],
			storeId = "";
		Zepto.each(stockInfo, function(i, e) {
			storeId = e.storeId;
			Zepto.each(e.stockList, function(j, t) {
				var shoppingListItem = '{"shoppingCount":' + t.count + ',"skuId":' + t.skuId + ',"stockId":' + t.id + (t.cartId ? ',"id":' + t.cartId + '' : '') + '}';
				shoppingListArray.push(shoppingListItem);
				stockHtml += '<a href="getStockInfoForCustomer.htm?stockId=' + t.id + '" class="item">\
            <div class="wbox">\
             <img src="' + t.img + '" class="size40">\
             <div class="wbox-1 detail">\
                <div class="name">' + t.name + '</div>\
                <div class="fc-grey">' + t.size + ' / ' + t.color + '</div>\
            </div>\
            <div class="tx-r">\
              <p>￥' + t.price + '</p>\
              <p>x ' + t.count + '</p>\
            </div>\
          </div></a>';
			});
			if (e.delivery == 1) {
				$("#dev1").closest(".item").hide();
				$("#dev2").prop("checked", true);
				$("#addressSel").closest(".selection").hide();
				$("#gotoPay").removeAttr("disabled");
				$("#recerverMsg").show();
			}
		});
		$(".confirmList").append(stockHtml);

		// console.log(shoppingListArray)
		var shoppingListJson = '[' + shoppingListArray.join(",") + ']';
		$.getJSON("prepareOrder.php?shoppingListJson=" + shoppingListJson, function(data) {
			if (data.status == 0) {
				var couponParam = {
					storeId: storeId,
					payment: data.result.payment,
					shoppingListJson: shoppingListJson
				};
				if (sessionStorage.usedCoupon) {
					var usedCoupon = JSON.parse(sessionStorage.usedCoupon);
					$("#totlePrice").html((parseFloat(data.result.payment) - parseFloat(usedCoupon.value)).toFixed(2));
				} else if (getUrlParam("from") == "removeCoupon") {
					$("#totlePrice").html(data.result.payment);
					$.getJSON("getAvailableCoupon.php", couponParam, function(data2) {
						if (data2.status == 0) {
							$("#usableCoupon").html(data2.result.availableList.length);
							sessionStorage.usedCoupon = "";
							sessionStorage.useCouponList = JSON.stringify(data2.result);
						}
					});
				} else {
					$.getJSON("getAvailableCoupon.php", couponParam, function(data2) {
						if (data2.status == 0) {
							var maxCoupon = {
								id: "",
								name: "",
								value: 0
							};
							if (data2.result.availableList.length > 0) {
								$.each(data2.result.availableList, function(i, e) {
									if (e.coupon.couponValue > maxCoupon.value) {
										maxCoupon = {
											id: e.customerCouponId,
											name: e.coupon.couponName,
											value: e.coupon.couponValue
										};
									}
								});
								$("#couponSel").data("id", maxCoupon.id).find(".link").html(maxCoupon.name + " (-" + maxCoupon.value.toFixed(2) + "元)");
								$("#totlePrice").html((parseFloat(data.result.payment) - parseFloat(maxCoupon.value)).toFixed(2));
								sessionStorage.usedCoupon = JSON.stringify(maxCoupon);
							} else {
								$("#totlePrice").html(data.result.payment);
							}
							sessionStorage.useCouponList = JSON.stringify(data2.result);
						}
					});
				}
			}
		});

		$("#addressSel, #couponSel").on("click", function() {
			sessionStorage.confirmOrderMsg = "#delivery=" + $("input[name=delivery]:checked").val() + "&pay=" + $("input[name=payType]:checked").val() + "&rName=" + $(".rName").val() + "&rMobile=" + $(".rMobile").val();
		});

		$("#gotoPay").on("click", function() {
			if (!sessionStorage.receiveAddressId && $("input[name=delivery]:checked").val() == "1") {
				mobileAlert("请选择收货人");
			} else if ($("input[name=delivery]:checked").val() == "2" && ($(".rName").val() == "" || $(".rMobile").val() == "")) {
				mobileAlert("请填写收货人信息");
			} else {
				// if($("#phoneBanded").val()!="true"){
				//   showMPLoginBox(function(){
				//     $("#phoneBanded").val("true");
				//     $("#gotoPay").trigger("click");
				//   });
				//   return false;
				// }
				if (sessionStorage.isLogin == "false") {
					showMPLoginBox(function() {
						$("#gotoPay").trigger("click");
					});
					return false;
				}
				var param = {
					storeId: storeId,
					customerCouponId: $("#couponSel").data("id"),
					payment: $("#totlePrice").text(),
					salesId: sessionStorage.thisOrderSales,
					deliveryType: $("input[name=delivery]:checked").val(),
					payType: $("input[name=payType]:checked").val(),
					customerAddressId: ($("input[name=delivery]:checked").val() == "1") ? $('#addressSel .link').data("id") : "0",
					receivePersonName: $(".rName").val(),
					receiveMobile: $(".rMobile").val(),
					shoppingListJson: shoppingListJson
				}
				$.getJSON("createOrder.json", param, function(data) {
					if (data.status == 0) {
						sessionStorage.removeItem("usedCoupon");
						if (param.payType == 2) {
							location.href = "wechatPayOpenId.htm?orderId=" + data.result.orderId;
						} else {
							location.href = "alipayConfirm.htm?orderId=" + data.result.orderId;
						}
					} else if (data.status == 1) {
						mobileAlert(data.msg);
					} else {
						alert(JSON.stringify(data));
					}
				});
			}
		});
	}
});