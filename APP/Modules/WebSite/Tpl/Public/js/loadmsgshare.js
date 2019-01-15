/*----------以下是微信分享函数----------*/

function onBridgeReady(){
		
	WeixinJSBridge.call('showOptionMenu'); //显示分享按钮等操作菜单
	WeixinJSBridge.call('hideToolbar'); // 屏蔽安卓手机系统的工具条
	
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
	
}

if (typeof WeixinJSBridge == "undefined"){
	if( document.addEventListener ){
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);// 添加微信浏览器监听器
	}else if (document.attachEvent){
		document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
		document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
	}
}else{
	onBridgeReady();
}
	//onBridgeReady();

/*
*  本js是由赵臣升改造的、适用于手机端的对话框。本对话框样式模仿手机APP的类型，界面透明友好。
*/
function scscms_alert(msg,sign,ok,can){
	var c_=false;//是否已经关闭窗口，解决自动关闭与手动关闭冲突
	sign=sign||"";
	var s="<div id='mask_layer'></div><div id='scs_alert'><div id='alert_top'></div><div id='alert_bg'><table width='260' align='center' border='0' cellspacing='0' cellpadding='1'><tr>";
	if (sign!="")s+="<td width='45'><div id='inco_"+sign+"'></div></td>";
	s+="<td id='alert_txt'>"+msg+"</td></tr></table>";
	if (sign=="confirm"){
		s+="<a href='javascript:void(0)' id='confirm_ok'>确 定</a><a href='javascript:void(0)' id='confirm_cancel'>取 消</a>";
	}else{
		s+="<a href='javascript:void(0)' id='alert_ok'>确 定</a>"
	}
	s+="</div><div id='alert_foot'></div></div>";
	$("body").append(s);
	$("#scs_alert").css("margin-top",-($("#scs_alert").height()/2)+"px"); //使其垂直居中
	$("#scs_alert").focus(); //获取焦点，以防回车后无法触发函数

	if (typeof can == "number"){
	//定时关闭提示
		setTimeout(function(){
			close_info();
		},can*1000);
	}
	function close_info(){
	//关闭提示窗口
		// 以下是jquery版本
		/*if(!c_){
		$("#mask_layer").fadeOut("fast",function(){
			$("#scs_alert").remove();
			$(this).remove();
		});
		c_=true;
		}*/
		// 以下是zepto版本
		if(!c_){
			$("#mask_layer").hide('fast'); // 针对zepto改良，快速消失遮罩层
			$("#scs_alert").remove(); // 针对zepto改良，移除弹框
			$("#mask_layer").remove(); // 针对zepto改良，移除mask_layer事件
			c_=true;
		}
	}
	$("#alert_ok").click(function(){
		close_info();
		if(typeof(ok)=="function")ok();
	});
	$("#confirm_ok").click(function(){
		close_info();
		if(typeof(ok)=="function")ok();
	});
	$("#confirm_cancel").click(function(){
		close_info();
		if(typeof(can)=="function")can();
	});
	function modal_key(e){	
		e = e||event;
		close_info();
		var code = e.which||event.keyCode;
		if (code == 13 || code == 32){if(typeof(ok)=="function")ok()}
		if (code == 27){if(typeof(can)=="function")can()}		
	}
	//绑定回车与ESC键
	if (document.attachEvent)
		document.attachEvent("onkeydown", modal_key);
	else
		document.addEventListener("keydown", modal_key, true);
}

// 以下是MLoading框的js
var MDialog = (function() {
	var e = "javascript:void(0)";
	var c = function(m) {
			return (typeof m == "undefined")
		};
	var g = function() {
			var o = '<div class="mModal"><a href="' + e + '"></a></div>';
			document.querySelector("body").insertAdjacentHTML("beforeEnd", o);
			o = null;
			var n = document.querySelector(".mModal:last-of-type");
			if (document.querySelectorAll(".mModal").length > 1) {
				n.style.opacity = 0.01
			}
			document.querySelector("a", n).style.height = window.innerHeight + "px";
			n.style.zIndex = window._dlgBaseDepth++;
			return n
		};
	var h = function() {
			if (c(window._dlgBaseDepth)) {
				window._dlgBaseDepth = 900
			}
		};
	var l = function(r, t) {
			if (document.querySelector("#mLoading")) {
				return
			}
			if (c(r)) {
				r = ""
			}
			if (c(t)) {
				t = false
			}
			h();
			var q = window.innerWidth,
				s = window.innerHeight,
				p = null,
				n = null;
			if (t) {
				n = g();
				n.id = "mLoadingModal"
			}
			p = '<div id="mLoading"><div class="lbk"></div><div class="lcont">' + r + "</div></div>";
			document.querySelector("body").insertAdjacentHTML("beforeEnd", p);
			var o = document.querySelector("#mLoading");
			o.style.top = (document.querySelector("body").scrollTop + 0.5 * (s - o.clientHeight)) + "px";
			o.style.left = 0.5 * (q - o.clientWidth) + "px";
			return o
		};
	var a = {
		showLoading: l,
	};
	return a
}());

var MLoading = {
	show: MDialog.showLoading,
	hide: function() {
		var b = document.querySelector("#mLoading");
		if (b) {
			b.parentNode.removeChild(b)
		}
		var a = document.querySelector("#mLoadingModal");
		if (a) {
			a.parentNode.removeChild(a)
		}
	}
};

//获取当前时间
function getLocalTime(ms,day){
	var _date = new Date(ms);
	var year=_date.getFullYear(),
        month=_date.getMonth()+1,
        date=_date.getDate(),
        hour=_date.getHours(),
        minute=_date.getMinutes(),
        second=_date.getSeconds();
    return year+"-"+(month<10 ? ("0"+month) : month)+"-"+(date<10 ? ("0"+date) : date)+ 
        (!day ? (" "+(hour<10 ? ("0"+hour) : hour)+":"+(minute<10?("0"+minute):minute)+":"+(second<10?("0"+second):second)) : ""); 
}

// 居中手机端弹窗，默认1.5秒
function mobileAlert(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-50)+'px;" class="ma-box">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}

// 靠左手机端弹窗，默认1.5秒
function mobileToast(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-140)+'px;" class="ma-box toast">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}
