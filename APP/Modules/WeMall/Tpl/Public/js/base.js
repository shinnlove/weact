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
	        if($("#chatMessageList").length == 0 && !$("#gotoTalk").hasClass("new")){
	        	$("#gotoTalk").addClass("new").append('<i class="ac"></i>');
	        }else{
	        	//TODO 消息中心处理
        		location.reload();
	        }
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
			location.href="../webim/messageCenter.htm?ownerId="+sessionStorage.loginAccountId;
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
	/*if(sessionStorage.loginAccountId){
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
	}*/
});