if ($("#salesId").val()) {
	sessionStorage.salesId = $("#salesId").val();
} else {
	if (sessionStorage.notFirst == "undefined") {
		// 如果第一次进店铺，读取导购列表
		var params = {
				eid : eid,
				sid : sid
		}
		
		$.post(guidelistURL, params, function(result) {
			var list = data.result.salesVoList;
			if (list.length == 0) return false;
			$('html,body').css({
				"height": $(window).height(),
				"overflow": "hidden"
			});
			var tempData = {
				list: list
			}
			var listStr = template('salesTempData', tempData);
			$(".chooseSalesBox").show().find(".storeSales").height($(window).height() - 160).append(listStr);
			$(".chooseSalesBox .storeSales .appProgress").each(function(i, e) {
				var stars = parseFloat($(e).find(".val").data("stars"));
				$(e).find(".val").width((stars * 20).toFixed(1) + "%");
				$(e).siblings(".grade").html((stars * 2).toFixed(1));
			});
		});
		//sessionStorage.removeItem("firstInStore");
		sessionStorage.notFirst = true; // 访问过一次以后就不是第一次访问了
	}
}

// 跳过按钮点击事件
$(".chooseSalesBox .jumpStep").on("click", function(e) {
	$(".chooseSalesBox").hide(); // 选择导购弹层隐藏
	$('html,body').css({
		"height": "auto",
		"overflow": "auto"
	});
});
// 选导购弹层上任何一个导购点击
$(".chooseSalesBox .storeSales").on("click", ".item", function(e) {
	var _t = $(this),
		salesId = _t.data("id");
	$.getJSON("bandingSales.php?salesId=" + salesId, function(data) {
		if (data.status == "0") {
			$(".chooseSalesBox").hide();
			mobileAlert(_t.find(".namer").text() + "很高兴为您服务");
			setTimeout(function() {
				location.reload();
			}, 1200);
			sessionStorage.salesId = salesId;
		} else if (data.status == "401") {
			showMPLoginBox(function() {
				_t.trigger("click");
			});
		}
	});
});

// 进入页面，优先获取浏览记录
if (getUrlHash("hLength") && getUrlHash("hTop")) {
	getAjaxData(0, getUrlHash("hLength"));
} else {
	getAjaxData(0);
}


function getAjaxData(idx, length) {
	$(".loadingBox a").html("正在加载中···");
	var param = {
		storeId: $('input[name=storeId]').val(),
		orderName: "off_time",
		orderType: "desc",
		index: idx,
		length: length ? length : baseOption.pageSize
	}
	$.getJSON("getStockListForCustomer.php", param, function(data) {
		var tempData = {
			list: data.result.stockList
		}
		var htmlStr = template('tempData', tempData);
		$('#stockList ul').append(htmlStr);
		if (length) {
			$("body").scrollTop(getUrlHash("hTop"));
		}
		$(".popLoading").remove();
		if (data.result.all) {
			$(".loadingBox a").html("没有更多了").off();
		} else {
			$(".loadingBox a").html("点击查看更多").off().on("click", function(e) {
				e.preventDefault();
				getAjaxData(idx + (length ? ~~length : ~~baseOption.pageSize));
			});
		}
	});
}

// 绑定事件
$("body").on("click", ".openFav", function(e) {
	// 点击...弹出收藏遮罩层
	e.stopPropagation(); // 阻止事件传播
	var item = $(this).closest(".stockLink"); // 找到点击的商品编号
	Zepto.getJSON("isCustomerFavorite.json?stockId=" + item.data("id"), function(data) {
		if (data.status == 0) {
			if (data.result.customerFavoriteCount == 1) {
				item.find(".fav").addClass("faved").text("已收藏");
			}
		}
	});
	item.find(".cover").show();
	return false;
}).on("click", ".cover .fav", function(e) {
	// 点击收藏按钮
	e.stopPropagation(); // 阻止事件传播
	var _t = $(this),
		// 当前点中的对象
		stockId = _t.closest(".stockLink").data("id"); // 找到离他最近的商品链接的data-id，也可以：$(this).parent().parent().attr("data-id");
	if (_t.hasClass("faved")) {
		// 如果商品被收藏过，取消收藏
		Zepto.getJSON("deleteCustomerFavorite.php?stockId=" + stockId, function(data) {
			if (data.status == 0) {
				_t.removeClass("faved").text("已取消收藏");
				setTimeout(function() {
					_t.parent().hide(); // 延时一秒遮罩层消失
				}, 1000);
			}
		});
	} else {
		// 如果商品没有被收藏过，加入收藏
		Zepto.getJSON("insertCustomerFavorite.php?stockId=" + stockId, function(data) {
			if (data.status == 0) {
				_t.addClass("faved").text("已收藏");
				setTimeout(function() {
					_t.parent().hide(); // 延时一秒遮罩层消失
				}, 1000);
			} else if (data.status == "401") {
				showMPLoginBox(function() {
					location.reload(); // 收藏失败刷新页面
				});
			}
		});
	}
	return false;
}).on("click", ".cover", function(e) {
	// 喜欢收藏的遮罩层点击，隐藏遮罩（没点中收藏心的情况）
	e.stopPropagation(); // 阻止事件传播
	if (e.target == this) {
		$(this).hide();
	}
	return false;
}).on("click", ".stockBox", function() {
	// 定义锚节点（返回后还在点击的位置，而不是从头开始）?
	var scTop = $("body").scrollTop(),
		length = $("#stockList .stockBox").length;
	location.href = "#hLength=" + length + "&hTop=" + scTop;
});

// 控制悬浮
var menuTop = $(".s-options").offset().top;
$(document).on("scroll", function() {
	if ($("body").scrollTop() >= menuTop) {
		$(".s-options").css({
			"position": "fixed",
			"top": "0",
			"z-index": "5"
		});
		$(".searchBox.stock").css("padding-top", "48px");
	} else {
		$(".s-options").css({
			"position": "static"
		});
		$(".searchBox.stock").css("padding-top", "8px");
	}
});