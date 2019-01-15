// JavaScript Document
//显示分享按钮
	function onBridgeReady(){
		/* document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	        WeixinJSBridge.call('hideToolbar');
		}); */
		document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
			WeixinJSBridge.call('showOptionMenu');
		});
	}

	if (typeof WeixinJSBridge == "undefined"){
		if( document.addEventListener ){
			document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
		}else if (document.attachEvent){
			document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
			document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
		}
	}else{
		onBridgeReady();
	}
	onBridgeReady();
	
	// 添加微信浏览器监听器
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		// 发送给好友
		WeixinJSBridge.on('menu:share:appmessage', function(argv) {
			WeixinJSBridge.invoke('sendAppMessage', {
				"appid": shareData.appid,
		        "img_url": shareData.img_url,
		        "img_width": shareData.img_width,
		        "img_height": shareData.img_height,
		        "title": shareData.title,
		        "desc": shareData.desc,
		        "link": shareData.link,
			}, function(res) {
				(shareData.callback)(res.err_msg);
			});
		});

		// 分享到朋友圈
		WeixinJSBridge.on('menu:share:timeline', function(argv) {
			WeixinJSBridge.invoke('shareTimeline', {
				"appid": shareData.appid,
		        "img_url": shareData.img_url,
		        "img_width": shareData.img_width,
		        "img_height": shareData.img_height,
		        "title": shareData.title,
		        "desc": shareData.desc,
		        "link": shareData.link,
			}, function(res) {
				(shareData.callback)(res.err_msg);
			});
		});
		
		// 分享到微博
		WeixinJSBridge.on('menu:share:weibo', function(argv) {
			WeixinJSBridge.invoke('shareWeibo', {
				"content" : shareData.title,
				"url" : shareData.link
			}, function(res){
				(shareData.callback)(res.err_msg);
			});
		});
		
		// 分享到Facebook
		WeixinJSBridge.on('menu:share:facebook', function(argv) {
			WeixinJSBridge.invoke('shareFB', {
				"appid": shareData.appid,
		        "img_url": shareData.img_url,
		        "img_width": shareData.img_width,
		        "img_height": shareData.img_height,
		        "title": shareData.title,
		        "desc": shareData.desc,
		        "link": shareData.link,
			}, function(res) {
				(shareData.callback)(res.err_msg);
			});
		});
		
	}, false);