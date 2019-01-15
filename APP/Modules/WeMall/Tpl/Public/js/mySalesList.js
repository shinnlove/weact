$(".mySalesList .wbox").each(function(i, e) {
	var avgStars = $(this).find(".val").data("star")
	$(this).find(".val").html((parseFloat(avgStars) * 2).toFixed(1));
});
$(".change").on("click", function(e) {
	var storeId = $(this).data("store"),
		nowId = $(this).data("now");
	$.getJSON("getSalesListOfStore.json?storeId=" + storeId + "&index=0&length=200", function(data) {
		var list = data.result.salesVoList;
		var tempData = {
			list: list,
			now: nowId
		}
		var listStr = template('tempData', tempData);
		if (listStr == "") {
			listStr = "抱歉，该店暂无其他导购";
		}
		$(".chooseSalesBox").show().find(".storeSales").height($(window).height() - 130).html(listStr);
		$(".chooseSalesBox .storeSales .appProgress").each(function(i, e) {
			var stars = parseFloat($(e).find(".val").data("stars"));
			$(e).find(".val").width((stars * 20).toFixed(1) + "%");
			$(e).siblings(".grade").html((stars * 2).toFixed(1));
		});
	});
});
$(".chooseSalesBox .jumpStep").on("click", function(e) {
	$(".chooseSalesBox").hide();
});
$(".chooseSalesBox .storeSales").on("click", ".item", function(e) {
	$(this).addClass("ac");
	var _t = $(this),
		salesId = _t.data("id");
	$.getJSON("bandingSales.json?salesId=" + salesId, function(data) {
		if (data.status == "0") {
			$(".chooseSalesBox").hide();
			mobileAlert(_t.find(".namer").text() + "很高兴为您服务");
			setTimeout(function() {
				location.reload();
			}, 1200);
		} else if (data.status == "401") {
			showMPLoginBox(function() {
				_t.trigger("click");
			});
		}
	});
});
$(".gotoStore").on("click", function(e) {
	e.preventDefault();
	if (sessionStorage.storeId) {
		location.href = "storeDetail.htm?storeId=" + sessionStorage.storeId;
	} else {
		location.href = $(this).attr("href");
	}
})