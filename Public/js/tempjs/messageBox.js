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
		if(!c_){
		$("#mask_layer").fadeOut("fast",function(){
			$("#scs_alert").remove();
			$(this).remove();
		});
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

//====================================以下为测试函数=======================================//
function test1(){
	scscms_alert("默认提示信息");
}
function test2(){
	scscms_alert("成功提示信息","ok");//重载正确图标
}
function test3(){
	scscms_alert("成功提示后回调函数","ok",function(){alert("回调成功！")});//回调函数
}
function test4(){
	scscms_alert("失败提示信息","error");//重载错误图标
}
function test5(){
	scscms_alert("失败提示信息","error",function(){alert("哦！no!")});
}
function test6(){
	scscms_alert("警告提示信息","warn");//重载警告图标
}
function test7(){
	scscms_alert("警告提示信息","warn",function(){alert("哦！警告!")});
}
function test8(){
	scscms_alert("您喜欢此信息提示吗？","confirm",function(){
		scscms_alert("您选择了喜欢，谢谢！","ok");
	},function(){
		scscms_alert("您选择了不喜欢，汗！","error");
	});
}
function test9(){
	scscms_alert("本信息3秒后自动关闭","ok","",3);//重载定时关闭
}
function test10(){
	scscms_alert("询问信息定时关闭提示信息,3秒后自动关闭，无取消时回调函数.不推荐使用。","confirm",function(){alert("确定回调用！")},3);
}