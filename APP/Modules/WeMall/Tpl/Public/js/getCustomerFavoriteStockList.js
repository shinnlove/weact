var dataPage = 0,
	allowScroll = true;
getAjaxData(dataPage);

function getAjaxData(idx) {
	if (allowScroll) {
		allowScroll = false;
		$("body").append('<div class="loading-bottom">加载中...</div>');
		$.getJSON("getCustomerFavoriteStockList.php?index=" + idx + "&length=" + baseOption.pageSize, function(data) {
			var htmlStr = "";
			$.each(data.result.stockList, function(i, e) {
				htmlStr += '<li class="mask">\
                <div class="item-list">\
                    <div class="img">\
                        <a href="getStockInfoForCustomer.htm?stockId=' + e.stock.id + '"><img src="' + e.productSupplier.picUrl + '?imageView2/2/h/320"></a>\
                    </div>\
                    <div class="des">\
                        <p class="tit">' + (e.productSupplier.tags ? ('<b>' + e.productSupplier.tags + '</b>') : 　'') + '<a href="getStockInfoForCustomer.htm?stockId=' + e.stock.id + '">' + e.productSupplier.name + '</a></p>\
                        <p class="other-info"> ￥' + parseFloat(e.minSkuPrice).toFixed(2) + '</p>\
                        <p class="price"><del>￥' + parseFloat(e.stock.tagPrice).toFixed(2) + '</del></p>\
                        <p class="more openFav"></p>\
                    </div>\
                    <div class="cover"><div data-id="' + e.stock.id + '" class="fav faved">已收藏</div></div>\
                </div>\
            </li>';
			});
			$('.products-list ul').append(htmlStr);
			$(".loading-bottom").remove();
			allowScroll = data.result.all ? false : true;
		});
	}
}
$("body").on("click", ".openFav", function() {
	var item = $(this).closest(".item-list");
	item.find(".cover").show();
}).on("click", ".cover .fav", function(e) {
	var _t = $(this),
		stockId = _t.data("id")
		Zepto.getJSON("deleteCustomerFavorite.json?stockId=" + stockId, function(data) {
			if (data.status == 0) {
				_t.removeClass("faved").text("已取消收藏");
				setTimeout(function() {
					_t.closest(".mask").hide(300);
				}, 1000);
			}
		});
});

scrollToLoadMore({
	callback: function() {
		getAjaxData(dataPage);
	}
});