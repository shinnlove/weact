var _order = getUrlParam('orderName'),
	_type = getUrlParam('orderType');
var defaultTag = $('.s-items a[data-order="' + _order + '"]');
defaultTag.addClass("curr");
if (_type == "asc") {
	defaultTag.addClass("up");
} else {
	defaultTag.addClass("down");
}
if (getUrlParam('fuzzyName') || getUrlParam('categoryName') || getUrlParam('tags')) {
	$("#keyWords").html("与 " + (getUrlParam('fuzzyName') || getUrlParam('categoryName') || getUrlParam('tags')) + " 有关的商品");
} else {
	$("#keyWords").html('全部商品');
}
// 按序查询商品
$(".s-items li a").click(function(e) {
	e.preventDefault();
	_order = $(this).data("order");
	if ($(this).hasClass("curr")) {
		if ($(this).hasClass("down")) {
			_type = "asc";
		} else {
			_type = "desc";
		}
	} else {
		_type = "desc";
	}
	location.href = "productList?fuzzyName=" + getUrlParam('fuzzyName') + "&storeId=" + getUrlParam('storeId') + "&salesId=" + getUrlParam('salesId') + "&orderName=" + _order + "&orderType=" + _type + "&index=0&length=20";
});
// 点击后继续查询下一页
$(".loadingBox a").on("click", function(e) {
	e.preventDefault();
	getAjaxData(_order, _type, ~~baseOption.pageSize, false);
});

// 进入页面，优先获取浏览记录
if (getUrlHash("hLength") && getUrlHash("hTop")) {
	getAjaxData(_order, _type, 20, false, getUrlHash("hLength") - 20);
}

// getAjaxData(_order,_type,0,true);

function getAjaxData(order, type, idx, clear, length) {
	if (clear) {
		$(".stockList ul").empty();
	}
	$(".loadingBox a").html("正在加载中···");
	var param = {
		storeId: getUrlParam('storeId'),
		fuzzyName: getUrlParam('fuzzyName'),
		categoryIds: getUrlParam('categoryIds'),
		tags: getUrlParam('tags'),
		orderName: order,
		orderType: type,
		index: idx,
		length: length ? length.toString() : baseOption.pageSize
	}
	$.getJSON("getStockListForCustomer.json", param, function(data) {
		var tempData = {
			list: data.result.stockList
		}
		var htmlStr = template('tempData', tempData);
		$('.stockList ul').append(htmlStr);
		if (length) {
			$("body").scrollTop(getUrlHash("hTop"));
		}
		if (data.result.all) {
			$(".loadingBox a").html("没有更多了").off();
		} else {
			$(".loadingBox a").html("点击查看更多").off().on("click", function(e) {
				e.preventDefault();
				getAjaxData(order, type, (idx + (length ? ~~length : ~~baseOption.pageSize)), false);
			});
		}
	});
}