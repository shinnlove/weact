wx.ready(function() {
	$('#getLocation').click(function() {
		wx.getLocation({
			success: function(res) {
				//alert(JSON.stringify(res));
				//longitude,latitude,accuracy,speed,errMsg
				scscms_alert("您所在东经："+res.longitude+"度，北纬："+res.latitude+"度。", "ok", "", 1); 
				var sid = $("#sid").val();
				if (sid == "-1") {
					setTimeout(function(){
						scscms_alert("当前商家还没有添加实体店。", "ok", "", 2); 
					},1500);
				} else {
					setTimeout(function(){
						scscms_alert("将带你去我们最近的实体店。", "ok", "", 1); 
						setTimeout(function(){
							location.href = entityshopurl;
						},1200);
					},1500);
				}
			},
			cancel: function(res) {
				scscms_alert("您拒绝了授权获取地理位置，搜索附近门店请允许定位", "warn", "", 2); 
			}
		});
	});
	
	$('#scanQRCode').click(function() {
		wx.scanQRCode({
			needResult: 1,
			desc: '用户网页扫码',
			success: function(res) {
				//alert(JSON.stringify(res));
				//resultStr,errMsg,scanQRCode
				window.location.href = res.resultStr; // 跳转扫码的网址
			}
		});
	});
});

wx.error(function(res) {
	alert(res.errMsg);
});