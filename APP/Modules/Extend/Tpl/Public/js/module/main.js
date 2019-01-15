// JavaScript Document
define('module/main', ['lib/artTemplate'], function(require, exports, module) {
	// 引入依赖
	var template = require('lib/artTemplate');
	
	module.exports = {
		init:function(){
			// first:字符串长度测试
			var str1 = "shinnlove", // 长度9
				str2 = "shinnlove是猪", // 长度9+3*2=15
				str3 = "shinnlove是纯真的猪，Oyeah！"; // 长度9+3*7+5=35，中文标点跟中文字一样算
			var len1 = jq.utilExt.strlen(str1),
				len2 = jq.utilExt.mb_strlen(str2),
				len3 = jq.utilExt.mb_strlen(str3);
			var result1 = "<p>"+"str1纯英文字符长度1为"+len1+"。</p>"+"<p>"+"str2英文与中文混合长度2为"+len2+"。</p>"+"<p>"+"str3英文中文与标点混合长度3为"+len3+"。</p>"
			jq(".display").append(result1);
			
			// second:测试截取url传参
			//location.href="?name=shinnlove&pwd=zcs5201314"; // 直接在地址栏上输入http://localhost/shinnlovejs/jqExtUtil.html?name=shinnlove&pwd=zcs5201314测试
			var username = jq.utilExt.getQuery("name"),
				password = jq.utilExt.getQuery("pwd");
			var result2 = "<p>"+"当前用户登录名为："+username+"。</p>"+"<p>"+"当前用户登录密码为："+password+"。</p>";
			jq(".display").append(result2);
			
			var longstr = "今天我给亲爱滴宝宝倩倩发了一个拼手气红包。倩倩运气真好，抢了一大半数量过去，剩下我就一点点份额。哈哈，要过年了真开心。",
				maxlen = 90, // 注意是英文字符长度，中文一个字的话utf-8乘以3倍，gbk和gb2312乘以2倍
				suffix1 = "...",
				suffix2 = "`(*∩_∩*)′";
			var shortstr1 = jq.utilExt.mb_cutstr(longstr, maxlen, suffix1), // 剪切字符串
				shortstr2 = jq.utilExt.mb_cutstr(longstr, maxlen, suffix2); // 剪切字符串
			var result3 = "<p>"+"长字符串经过剪切后的显示结果为："+shortstr1+"</p>"+"<p>"+"长字符串经过剪切后的显示结果为："+shortstr2+"</p>";
			jq(".display").append(result3);
			
			// third: test ios and wxVersion
			var isWx = jq.os.wx, wxVer = jq.os.wxVersion;
			var isIOS = jq.os.ios, isAndroid = jq.os.android, osVer = jq.os.version;
			var sysResult = "";
			if(isWx) {
				sysResult += "<p>"+"当前是在微信中访问，微信版本为"+wxVer+"</p>";
			} 
			if(isIOS) {
				sysResult += "<p>"+"当前操作系统是IOS，IOS系统版本为"+osVer+"</p>";
			} else if(isAndroid) {
				sysResult += "<p>"+"当前操作系统是安卓，Android系统版本为"+osVer+"</p>";
			} else {
				sysResult += "<p>"+"当前操作系统是PC电脑，操作系统版本为"+osVer+"</p>";
			}
			jq(".display").append(sysResult);
			
			// fourth:encode与decode
			var htmlstr = '<img src="images/123.jpg" alt="" />';
			var encodeStr = jq.utilExt.htmlEncode(htmlstr);
			console.log(encodeStr);
			var decodeStr = jq.utilExt.htmlDecode(encodeStr);
			console.log(decodeStr);
			
			// fifth:bind event on DOM
			jq(".wrap").on("click", ".start", function(){
				jq(".baby").html("Baby say I love you.");
			});
			
			// sixth: ajax request and template render
			var url = '/weact/APP/Modules/Extend/Tpl/getStudents.php';
			var param = {
				classId:123456
			}
			var opts = {
				'noShowLoading' : true,
				'noMsg': true,
				'success' : function(result) {
					if (result.errcode == 0) {
						var tmpl = template("studentstpl", result.data);
						if (tmpl == '{Template Error}') {
							tmpl = ""; // 渲染出错
						} 
						jq(".students").html(tmpl);
					} else {
						alert(result.errmsg);
					}
				}
			}
			jq.utilExt.ajax(url, param, opts); // ajax请求
			
			// seventh: dialog
			//jq.utilExt.showLoading(); // 显示等待
			//jq.utilExt.showLoading("none"); // 关闭对话框
			//jq.utilExt.showLoading('block', 'Loading', true); // 自动1秒关闭
			//jq.utilExt.showLoading('block', '', 10000); // 自动1秒关闭
			
			// demo2 confirm dialog
			/*var conStr = "我很喜欢我家小倩倩";
			var opts = {
                'id': 'xxxConfirm', 	// xxx确认对话框
				'title': '确认提示', 		// 确认对话框标题
                'isMask':true, 			// 确认对话框需要弹层
                'content':conStr, 		// 需要确认的内容
				'okValue':'确定', 		// 确定按钮文字
				'cancelValue':'取消', 	// 取消按钮文字
				'ok':function() {
                	alert('点击了确定'); // 确定函数
				}, 
				'cancel':function(){
					alert('点击了取消'); 	// 取消函数
				},
				'callback':function() {
                    //alert("执行回调");
				}
            };
			jq.utilExt.dialog(opts); // 弹窗
*/		}
	};
	
	module.exports.init();
});