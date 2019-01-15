// mobile.js
function getUrlParam(key){
	var reg = new RegExp("(^|&)" + key + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
function getUrlHash(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)", "i");
    var r = location.hash.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
String.prototype.getParam = function(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)");
    var r = this.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
function mobileAlert(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-50)+'px;" class="ma-box">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}
function mobileToast(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-140)+'px;" class="ma-box toast">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}
var baseOption = {
	pageSize:"20",
	ickdID:"108386",
	ickdKey:"e5f4bb052cc515e85f217f7fc9d7d580"
}
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
function scrollToLoadMore(option){
	var wHeight = $(window).height();
	window.onscroll = function(){
        var sHeight = $("body").scrollTop(), cHeight = $(document).height();
        if(sHeight >= cHeight-wHeight-(option.distance ? option.distance : 20)){
            if($(".loading-bottom").length > 0) {
                return false;
            }else{
	            dataPage += (option.length ? option.length : ~~baseOption.pageSize);
	            option.callback();
	        }
        }
	}
}
function getOrderStatus (code) {
	var status = "";
    switch(code){
        case 1 : 
        status="待付款";
        break;
        case 2 :
        status="已发货";
        break;
        case 10 :
        status="待发货";
        break;
        case 3 :
        status="待评价";
        break;
        case 4 :
        status="已完成";
        break;
        case 5 :
        status="已关闭";
        break;
    }
    return status;
}

if(typeof $ != "function"){
	location.reload();
}
$(function(){
	// 商品搜索
	$(".searchCover").height($(this).height()-51);
	$(".searchBox.stock input[type=search]").focus(function(){
	    var box = $(this).closest(".searchBox");
    	$(".stockList").hide();
	    box.addClass("doing").find(".s-cancel").show();
	    var searchHistory = sessionStorage.searchHistory ? JSON.parse(sessionStorage.searchHistory) : [],
        	historyStr = "";
	    $.each(searchHistory,function(i,e){
	        historyStr+='<li class="wbox"><a href="" class="wbox-1">'+e+'</a><a href="" class="remove">删除</a></li>';
	    });
	    if(searchHistory.length > 0){
	        historyStr+='<li class="tx-c clear"><a href="" class="btn">清除搜索记录</a></li>';
	    }else{
	        historyStr+='<li class="noResult"><span>暂无搜索历史记录</span></li>';
	    }
	    $(".searchCover").show().find(".history").empty().append(historyStr);
	});
	$(".s-cancel").on("click",function(){
    	$(".searchCover").hide();
	    $(".stockList").show();
	    $(".searchBox").removeClass("doing").find(".s-cancel").hide();
	});
	$(".searchBox.stock form").submit(function(){
	    var searchHistory = sessionStorage.searchHistory ? JSON.parse(sessionStorage.searchHistory) : [];
	    var word = $(".searchBox input[type=search]").val();
	    if(word && $.inArray(word,searchHistory) < 0){
		    searchHistory.unshift(word);
		    sessionStorage.searchHistory = JSON.stringify(searchHistory);
	    }
	});
	$(".searchCover").on("click",".wbox-1",function(e){
		e.preventDefault();
       	var word = $(this).text();
        $(".searchBox input[type=search]").val(word);
        $(".searchBox form").submit();
	}).on("click",".clear .btn",function(e){
		e.preventDefault();
		sessionStorage.searchHistory = "";
	    $(this).parent().html('<span>暂无搜索历史记录</span>').removeClass("clear").addClass("noResult").siblings().remove();
	}).on("click",".remove",function(e){
		e.preventDefault();
		var word = $(this).siblings(".wbox-1").text();
		var searchHistory = JSON.parse(sessionStorage.searchHistory);
		var searchHistoryNew = $.grep(searchHistory,function(item){
		    return item != word;
		});
		$(this).parent().remove();
		sessionStorage.searchHistory=JSON.stringify(searchHistoryNew)
	});
});

// artTemplate模板扩展
template.helper('dateFormat', function (date, format) {
    format = getLocalTime(date,true).replace(/-/g,".");
    return format;
});
template.helper('toFixed2', function (data, format) {
    format = data.toFixed(2);
    return format;
});
template.helper('getStatus', function (data, format) {
    format = getOrderStatus(data);
    return format;
});

// base.js
// 弹出手机号注册
function showMPLoginBox(_callback){
	var loginStr = '<div id="mpLoginBox">\
	  <div class="cont">\
	    <div class="tx-c fs16 pb10">请使用手机号登录<span class="cancelLogin">×</span></div>\
	    <input type="tel" class="tel" placeholder="请填写11位手机号码">\
	    <div class="wbox pt15">\
	      <div class="wbox-1 pr10"><input class="code" type="text" maxlength="6" placeholder="短信收到的验证码"></div>\
	      <button class="btn btn-red getCode">获取</button>\
	    </div>\
	    <div class="fc-red lh22 tips pb15"></div>\
	    <button class="btn btn-red full submit" disabled>确认</button>\
	  </div>\
	</div>';
	$("body").append(loginStr);
	$("#mpLoginBox").on("click",".cancelLogin",function(e){
		$("#mpLoginBox").remove();
	}).on("click",".getCode",function(){
		if(!$(this).hasClass("btn-red")){
			return false;
		}
		var tel = $.trim($("#mpLoginBox .tel").val());
		if(tel.length == 11 && /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(tel)){
			var btn=$(this),timeEnd = 59;
			$.getJSON("../user/getRegisterCode.json?phone="+tel,function(data){
				if(data.status=="0"){
					$("#mpLoginBox .tips").html("验证码已发送，请注意查收手机短信");
					btn.removeClass("btn-red").text(timeEnd);
					var getCodeCount = setInterval(function(){
						if(timeEnd>0){
							timeEnd--;
							btn.text(timeEnd);
						}else{
							clearInterval(getCodeCount);
							btn.addClass("btn-red").text("获取");
						}
						
					},1000);
				}
			});
		}else{
			$("#mpLoginBox .tips").html("手机号码错误");
		}
	}).on("input",".code",function(){
		if($(this).val().length > 0 && $("#mpLoginBox .tel").val()){
			$("#mpLoginBox .submit").removeAttr("disabled");
		}else{
			$("#mpLoginBox .submit").attr("disabled","disabled");
		}
	}).on("click",".submit",function(){
		var tel = $.trim($("#mpLoginBox .tel").val())
			code = $("#mpLoginBox .code").val();
		$.getJSON("../user/customerRegister.json?phone="+tel+"&code="+code,function(data){
			if(data.status=="0"){
				$("#mpLoginBox").remove();
				sessionStorage.isLogin="true";
				_callback();
			}else{
				$("#mpLoginBox .tips").html(data.errmsg);
			}
		});
	}).on("click",function(e){
		e.preventDefault();
		if(e.target == this){
			$("#mpLoginBox").remove();
		}
	});
}

var Chat = function(socket, uid) {
    this.socket = socket;
    this.uid = uid;
};
Chat.prototype.login = function() {
    var message = {
        fId: 0,
        uId: this.uid
    };
    this.socket.emit('login', message);
};
// 建立websocket连接
function do_chat(uid){
    var socketServer = location.host.indexOf("qiakr")>0 ? "ws://183.131.76.18:29000" :"ws://61.174.12.217:29000"; 
    var socket = io.connect(socketServer);
    var c = new Chat(socket, uid);
    c.login();
    socket.on('message', function(data) {
        // console.log(data);
        if(data.fId != "0"){
        	var from = data.data.sender;
        	var newMsgAccountArray = localStorage.hasNewMessage ? JSON.parse(localStorage.hasNewMessage) : [];	
        	if(from && $.inArray(from,newMsgAccountArray) < 0){
                newMsgAccountArray.push(from);
                if(newMsgAccountArray.length>10){
                    newMsgAccountArray.shift();
                }
                localStorage.hasNewMessage = JSON.stringify(newMsgAccountArray);
            }
            if($("#chatMessageList").length == 0){
            	if(!$("#gotoTalk").hasClass("new")){
            		$("#gotoTalk").addClass("new").append('<i class="ac"></i>');
            	}
            }else{
	        	// 消息中心处理
        		location.reload();
	        }
	        if($("#newMsgVoice").length == 0){
	        	$("body").append('<audio id="newMsgVoice" src="http://static.qiakr.com/webim/converted_newMsg.mp3"></audio>');
	        }
	        $("#newMsgVoice")[0].play();
	    }
    });
    return c;
};

$(function(){
	if(getUrlParam("salesId")){
		sessionStorage.salesId = getUrlParam("salesId");
	}
	if(getUrlParam("storeId")){
		sessionStorage.storeId = getUrlParam("storeId");
	}
	if($("#noTalking").length==0){
		$("body").append('<a href="getShoppingCart.htm" class="linkNeedLogin toShoppingCart '+($("#talkHeigher").length>0 ? "higher" : "")+'"></a><a href="javascript:;" id="gotoTalk"  class="toAskSales'+(sessionStorage.salesId ? " sales" : '')+($("#talkHeigher").length>0 ? " higher" : "")+'"></a>');
	}
	if(localStorage.hasNewMessage && JSON.parse(localStorage.hasNewMessage).length > 0 && !$("#gotoTalk").hasClass("new")){
    	$("#gotoTalk").addClass("new").append('<i class="ac"></i>');
    }
	$("body").on("click","#gotoTalk",function(e){
		var _t = $(this);
		if($(".products-msg").length > 0){
			sessionStorage.talkAboutStock = '{"Description":"'+$(".productName").text()+'","Url":"'+location.href+'&from=booking","PicUrl":"'+$("#productPicUrl").val()+'","Title":""}'
		}
		if(_t.hasClass("new")){
			location.href="../webim/messageCenter.htm?ownerId="+sessionStorage.loginAccountId+"&talkingId="+sessionStorage.salesId;
			return false;
		}
		if(sessionStorage.salesId){
			if(sessionStorage.isLogin=="true"){
				// if(getUrlParam("type")=="share"){
	   //      		$.getJSON("../getQrcodeBySalesId.json?salesId="+sessionStorage.salesId,function(data){
	   //      			$("body").append('<div class="qrCodeToWx tx-c"><div class="cont"><div class="pb5"><b>'+data.result.sales.name+'</b></div><div>'+data.result.store.name+'</div><div class="qrcode"><img width="100%" src="'+data.result.qrcode.qrcode_url+'"></div><div><p>长按二维码</p><p>添加我为您的私人导购</p></div></div></div>')
	   //      		});
	   //      	}else{
		            location.href="../webim/chat.htm?salesId="+sessionStorage.salesId;
	        	// }
			}else{
				showMPLoginBox(function(){
	        		location.reload();
	        	});
			}
		}else{
			location.href="storeDetail.htm?storeId="+sessionStorage.storeId;
		}
	}).on("click",".linkNeedLogin",function(e){
		if(sessionStorage.isLogin == "false"){
			showMPLoginBox(function(){
	          	location.href=href;
	        });
	        return false;
		}
	});
	function loginToChat(id){
		var getIO = setTimeout(function(){
		    if(typeof io != "undefind"){
		        do_chat(id);
		        clearTimeout("getIO");
		    }else{
		        getIO();
		    }
		},100);
	}
	if(sessionStorage.loginAccountId){
		loginToChat(sessionStorage.loginAccountId);
	}else{
		$.getJSON("../user/getLoginAccount.json",function(data){
			if(data.status=="0"){
				sessionStorage.isLogin="true";
				sessionStorage.loginAccountId = data.result.accountId;
				loginToChat(data.result.accountId);
			}else{
				sessionStorage.isLogin="false";
			}
		});
	}
});

var qkUtil = {
	loading:{
		show:function(string){
			var loadingHtml='<div class="popLoading">\
		        <div class="cont">\
		            <div class="loadingAmt">\
		                <div class="img">\
		                    <div class="img2"></div>\
		                </div>\
		            </div>\
		            <div class="pt10">'+(string ? string : '正在加载中')+'</div>\
		        </div>\
		    </div>';
		    $("body").append(loadingHtml);
		},
		hide:function(){
			$(".popLoading").hide();
		}
	}
}