// 加载评论
$.getJSON("getStockAppraiseList.php?stockId=" + _stockId + "&index=0&length=3", function(data) {
	if (data.status = "0") {
		//$("#commentCount").html(data.result.count);
		var comStr = "";
		$.each(data.result.appraiseStockVoList, function(i, e) {
			comStr += '<div class="item">\
              <div class="wbox">\
                <img src="' + (e.customer.avatar ? e.customer.avatar : "http://static.qiakr.com/mall/default-photo.png") + '" class="size43 round mr10">\
                <div class="wbox-1 lh22">\
                  <div>\
                    ' + (e.customer.name ? e.customer.name : "匿名") + '<div class="appProgress ml5"><span class="val" style="width:' + (e.appraiseStock.stars * 20).toFixed(2) + '%"></span></div>\
                  </div>\
                  <div class="fc-grey fs12">' + getLocalTime(e.appraiseStock.gmtCreate) + '</div>\
                </div>\
              </div>\
              <div class="fc-grey pt5">' + e.appraiseStock.comment + '</div>\
            </div>';
		});
		$(".appriseBox .cont").html(comStr);
	}
});