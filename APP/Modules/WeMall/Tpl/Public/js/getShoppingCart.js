$("input.yes").prop("checked", false); // 商品选择框默认不选中
sessionStorage.removeItem("usedCoupon"); // 去除使用优惠券
var pricetotal = 0, // 总价格
	selectedStore; // 所选的商店

// 某个店铺的全选勾选框点击触发事件
$(".storeMsg .yes").click(function() {
	var thisStoreAllSelect = $(this).closest(".cart-list").find('input[name="checkProduct"]'); // 找到最近一个店铺section区域里边所有的checkProduct勾选框
	if ($(this).prop("checked")) {
		thisStoreAllSelect.prop("checked", true); // 如果全选是勾中的，则所有商品全部勾中
	} else {
		thisStoreAllSelect.prop("checked", false); // 如果全选是取消的，则所有商品取消勾中
	}
});

// 店铺勾选框或者单个商品勾选框点击触发事件(不允许跨店铺结账和到店自提与快递一起结账)
$('.list .yes, .storeMsg .yes').click(function() {
	if ($(this).prop("checked")) {
		var otherStore = $(this).closest(".cart-list").siblings().find('input[name="checkProduct"]:checked'); // 获取临近店铺商品勾选情况
		if (otherStore.length > 0) {
			mobileAlert("暂不支持跨店铺结算");
			otherStore.prop("checked", false); // 取消其他店铺的勾选状况
		}
		if ($(this).closest(".cart-list").find("input.selfDelivery:checked").length > 0 && $(this).closest(".cart-list").find("input.delivery:checked").length > 0) {
			$(this).closest(".cart-list").find("input.selfDelivery").prop("checked", false);
			mobileAlert("到店自提的商品请单独下单");
		}
	}
	getPayment(); // 计算价格
});

// 具体某件商品勾选框触发事件（单个商品勾选是否引起全选）
$('.list .yes').click(function() {
	if ($(this).closest(".list").find('input[name="checkProduct"]').not("input:checked").length == 0) {
		$(this).closest(".cart-list").find(".storeMsg .yes").prop("checked", true); // 如果所有商品都勾中了，全选
	} else {
		$(this).closest(".cart-list").find(".storeMsg .yes").prop("checked", false); // 还有商品没勾中，就不全选
	}
});

//数量框加减失去焦点
$(".d-plus .count").on("blur", function() {
	var count = ~~$(this).val(), // 当前购买数量
		maxCount = ~~$(this).data("max"), // 最大购买数量
		limitBuyCount = ~~$(this).data("limit"); // 每人限购数量
	if (limitBuyCount) {
		// 限购获取库存和限购数量的最小值
		if (count > Math.min(limitBuyCount, maxCount)) {
			$(this).val(Math.min(limitBuyCount, maxCount));
			return false;
		}
	} else {
		// 未限购
		if (count > maxCount) {
			$(this).val(maxCount); // 超过最大的数量，只能是最大的数量
			return false;
		}
	}
	// 允许更改的情况下
	if (count < 1) {
		$(this).val("1"); // 如果数量比1小，直接为1
	} else {
		$(this).val(count);
	}
	getPayment(); // 更改完了计算总价
});

//数量框加减点击事件
$(".d-plus .jia, .d-plus .jian").on("click", function() {
	var countInput = $(this).parent().find(".count"), // 找到当前的数量
		priceSingle = $(this).closest(".list").find(".price").text(), // 找到单价
		max = ~~countInput.data("max"), // 找到最大允许购买量
		limitBuyCount = ~~countInput.data("limit"); // 找到最大限购量
	if ($(this).hasClass("jia")) {
		// 如果点的是加号
		if (limitBuyCount) {
			// 如果限购
			if (~~countInput.val() >= Math.min(limitBuyCount, max)) {
				return false;
			}
		} else {
			// 不限购看数量是否超标
			if (~~countInput.val() >= max) {
				return false;
			}
		}
		// 允许加的情况下
		pricetotal = pricetotal + parseFloat(priceSingle); // 总价再加上一个物品的单价
		countInput.val(~~countInput.val() + 1); // 数量+1
	} else {
		// 如果点的是减号
		if (~~countInput.val() <= 1) return false;
		pricetotal = pricetotal - parseFloat(priceSingle); // 总价减去一个物品的单价
		countInput.val(~~countInput.val() - 1); // 数量-1
	}
	getPayment(); // 更改完了计算总价
});

// 编辑按钮点击事件
$(".storeMsg .edit").on("click", function(e) {
	e.preventDefault();
	if (!$(this).hasClass("doing")) {
		$(this).text("完成").addClass("doing").closest(".cart-list").find(".d-plus, .remove").show(); // 编辑变成完成，增加修改状态，一并显示该店铺的所有数量框操作与删除
		$(this).closest(".cart-list").find(".money").hide(); // 价格与数量隐藏
	} else {
		$(this).text("编辑").removeClass("doing").closest(".cart-list").find(".d-plus, .remove").hide(); // 完成变成编辑，去掉修改状态，一并隐藏该店铺所有数量框操作与删除
		$(this).closest(".cart-list").find(".money").show(); // 价格与数量展现
		// 对每一条商品可能变更的数量，重新写入class = money的数量框中
		$(this).closest(".cart-list").find(".shopCount").each(function(i, e) {
			$(e).html($(this).closest("dl").find("input.count").val());
		})
	}
});

// 计算总价格
function getPayment() {
	pricetotal = 0; // 默认总价格为0
	// 所勾选的每个对象
	Zepto.each($('.list .yes:checked'), function(i, e) {
		var dl = $(e).closest("dl"); // 找到dl
		pricetotal += (parseFloat(dl.find(".price").text()) * parseFloat(dl.find(".count").val())) || 0; // 单价乘以数量或没有价格
	});
	$("#pricetotal").html(pricetotal.toFixed(2)); // 转2位小数
	if (pricetotal == 0) {
		$("#gotoPay").attr("disabled", "disabled"); // 没有价格不能结算
	} else {
		$("#gotoPay").removeAttr("disabled"); // 有价格可以结算
	}
}

//自身防误点
$(".d-plus").on("click", function(e) {
	e.preventDefault();
});

// 去结算按钮事件
$("#gotoPay").on("click", function() {
	if (!$(this).attr("disabled")) {
		var stockArray = [],
			tit = $('.list .yes:checked:first').closest(".cart-list").find(".storeMsg").find("a");
		var stockMsg = {
			"storeName": tit.text(),
			"storeId": tit.data("id"),
			"stockList": [],
			"delivery": $('.list .yes:checked:first').hasClass("delivery") ? 0 : 1
		}
		Zepto.each($('.list .yes:checked'), function(i, e) {
			var dd = $(e).closest("dl").find(".stockInfo");
			var stockItem = {
				"img": dd.find(".size40").attr("src"),
				"name": dd.find(".name").text(),
				"id": dd.data("id"),
				"cartId": dd.data("cartid"),
				"size": dd.find(".size").text(),
				"color": dd.find(".color").text(),
				"skuId": dd.data("skuid"),
				"count": dd.find(".count").val(),
				"price": dd.siblings(".action").find(".price").text()
			};
			stockMsg.stockList.push(stockItem);
		});
		stockArray.push(stockMsg)
		sessionStorage.stockInfo = JSON.stringify(stockArray);
		var bindingSales = $('.list .yes:checked:first').closest(".cart-list").data("sales");
		var salesId = bindingSales ? bindingSales : getUrlParam("salesId");
		sessionStorage.thisOrderSales = salesId;
		location.href = "confirmOrderInfo.htm";
	}
});

//移除购物车
$(".cart-list .remove").on("click", function(e) {
	var _t = $(this),
		cartid = _t.data("id");
	$.getJSON("deleteCustomerShoppingCart.json?customerShoppingCartId=" + cartid, function(data) {
		if (data.status == 0) {
			_t.closest("dl").remove();
		}
	})
});