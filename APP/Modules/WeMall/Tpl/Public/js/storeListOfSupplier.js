$(function() {
	/*localStorage.removeItem("storeHistory");
	var latitude, longitude, userSupplierId = getUrlParam("supplierId"),
		totalHistory = localStorage.storeHistory2 ? JSON.parse(localStorage.storeHistory2) : {},
		storeHistory = totalHistory[userSupplierId] || [];*/
	var userSupplierId = "124234";
	var autoGetData = setTimeout(function() {
		getAjaxData(userSupplierId, "", "", "", 0, true);
	}, 1000);
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(getStoreList, getLocationFailed);
	}
	
	function getStoreList(position) {
		latitude = position.coords.latitude;
		longitude = position.coords.longitude;
		// alert(latitude+"==="+longitude)
		clearTimeout(autoGetData);
		getAjaxData(userSupplierId, longitude, latitude, "", 0, true)
	}

	function getLocationFailed(error) {
		clearTimeout(autoGetData);
		getAjaxData(userSupplierId, "", "", "", 0, true)
		// if(error.code=="1"){
		// 	alert("用户拒绝访问地理位置权限");
		// }else{
		// 	alert("获取地理位置失败");
		// }
	}
	$("#storeListForm").submit(function() {
		var keywords = $("#storeListForm input[name=keywords]").val();
		getAjaxData(userSupplierId, longitude, latitude, keywords, 0, true)
		return false;
	});

	function getDistance(d) {
		var distance;
		if (!d) {
			return ""
		} else if (d < 500) {
			return "&lt500m";
		} else if (d < 1000) {
			return ~~d + "m";
		} else if (d < 10000) {
			return (d / 1000).toFixed(2) + "Km";
		} else if (d < 100000) {
			return (d / 1000).toFixed(1) + "Km";
		} else {
			return "&gt100Km";
		}
	}

	function getAjaxData(supplierId, lon, lat, keywords, idx, clear) {
		if (clear) {
			$("#storeAround").empty();
		}
		$(".loadingBox a").html("正在加载中···");
		var param = {
			supplierId: supplierId,
			longitude: lon,
			latitude: lat,
			keywords: keywords,
			index: idx,
			length: baseOption.pageSize,
			open: "0"
		};
		$.post(storelistURL, param, function(data) {
			alert(storelistURL);
			alert(data.status);
			
			if (storeHistory.length > 0) {
				$("#storeHistory,.historyTit").show();
				var historyStr = '';
				$.each(storeHistory, function(i, e) {
					historyStr += e;
				});
				$("#storeHistory").html(historyStr);
			}

			if (data.status == "0") {
				var list = data.result.storeSearchVoList,
					listStr = "";

				if (list.length == 0 && clear) {
					listStr = '<li class="noResult"><span class="store">没有找到相关门店</span></li>';
				} else {
					$.each(list, function(i, e) {
						listStr += '<li>' + '<a href="getStoreHomePage.htm?storeId=' + e.store.id + '" class="with-go-right wbox">' + '<img class="storeLogo" src="' + (e.store.entityPicture ? (e.store.entityPicture.split(",")[0] + "?imageView2/2/h/120") : "http://static.qiakr.com/mall/Store@2x.png") + '">' + '<div class="storeMsg wbox-1">' + '<div class="wbox">' + '<div class="name wbox-1">' + e.store.name + '</div>' + '<div class="dist">' + getDistance(e.distance) + '</div>' + '</div>' + '<div class="addr">' + (e.store.province || "") + (e.store.city || "") + (e.store.district || "") + " " + (e.store.detail || "") + '</div>' + '<div class="time">' + (e.store.businessHours || "") + '</div>' + '</div>' + '</a>' + '</li>';
					});
				}
				$("#storeAround").append(listStr);
				$(".popLoading").remove();
				$(".loadingBox,.aroundTit").show();
				if (data.result.all) {
					$(".loadingBox .loadMore").html("没有更多了").off();
				} else {
					$(".loadingBox a").html("点击查看更多").off().on("click", function(e) {
						e.preventDefault();
						getAjaxData(userSupplierId, lon, lat, keywords, (idx + ~~baseOption.pageSize));
					});
				}
			}
		});
	}

	$("#storeAround").on("click", "li a", function(e) {
		$(this).find(".dist").remove();
		var storeName = $(this).find(".name").text();
		var str = '<li>' + $(this).parent().html() + '</li>';
		// console.log(str);
		sessionStorage.removeItem("salesId");
		if (storeHistory.join(",").indexOf(storeName) < 0) {
			if (storeHistory.length > 4) {
				storeHistory.pop();
			}
			storeHistory.unshift(str);
			totalHistory[userSupplierId] = storeHistory;
			localStorage.storeHistory2 = JSON.stringify(totalHistory);
		}
		sessionStorage.firstInStore = "true";
	});
	$("#storeHistory").on("click", "a.wbox", function(e) {
		//sessionStorage.removeItem("salesId");
		sessionStorage.firstInStore = "true";
	});
	$("#clearHistory").on("click", function() {
		//localStorage.storeHistory = "";
		$("#storeHistory,.historyTit").remove();
	});
});