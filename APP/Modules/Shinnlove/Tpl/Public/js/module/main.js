/**
 * 主页面
 * @author shinnlove
 */
define('module/main', ['lib/artTemplate'], function(require, exports, module) {
	var template = require('lib/artTemplate');
	module.exports = {
		init: function() {
			var str1 = "shinnlove",
				str2 = "shinnlove是猪",
				str3 = "shinnlove是纯真的猪，Oyeah！";
			var len1 = jq.utilExt.strlen(str1),
				len2 = jq.utilExt.mb_strlen(str2),
				len3 = jq.utilExt.mb_strlen(str3);
			var result1 = "<p>" + "str1纯英文字符长度1为" + len1 + "。</p>" + "<p>" + "str2英文与中文混合长度2为" + len2 + "。</p>" + "<p>" + "str3英文中文与标点混合长度3为" + len3 + "。</p>"
			jq(".display").append(result1);
			var username = jq.utilExt.getQuery("name"),
				password = jq.utilExt.getQuery("pwd");
			var result2 = "<p>" + "当前用户登录名为：" + username + "。</p>" + "<p>" + "当前用户登录密码为：" + password + "。</p>";
			jq(".display").append(result2);
			var longstr = "今天我给亲爱滴宝宝倩倩发了一个拼手气红包。倩倩运气真好，抢了一大半数量过去，剩下我就一点点份额。哈哈，要过年了真开心。",
				maxlen = 90,
				suffix1 = "...",
				suffix2 = "`(*∩_∩*)′";
			var shortstr1 = jq.utilExt.mb_cutstr(longstr, maxlen, suffix1),
				shortstr2 = jq.utilExt.mb_cutstr(longstr, maxlen, suffix2);
			var result3 = "<p>" + "长字符串经过剪切后的显示结果为：" + shortstr1 + "</p>" + "<p>" + "长字符串经过剪切后的显示结果为：" + shortstr2 + "</p>";
			jq(".display").append(result3);
			var isWx = jq.os.wx,
				wxVer = jq.os.wxVersion;
			var isIOS = jq.os.ios,
				isAndroid = jq.os.android,
				osVer = jq.os.version;
			var sysResult = "";
			if (isWx) {
				sysResult += "<p>" + "当前是在微信中访问，微信版本为" + wxVer + "</p>"
			}
			if (isIOS) {
				sysResult += "<p>" + "当前操作系统是IOS，IOS系统版本为" + osVer + "</p>"
			} else if (isAndroid) {
				sysResult += "<p>" + "当前操作系统是安卓，Android系统版本为" + osVer + "</p>"
			} else {
				sysResult += "<p>" + "当前操作系统是PC电脑，操作系统版本为" + osVer + "</p>"
			}
			jq(".display").append(sysResult);
			var htmlstr = '<img src="images/123.jpg" alt="" />';
			var encodeStr = jq.utilExt.htmlEncode(htmlstr);
			console.log(encodeStr);
			var decodeStr = jq.utilExt.htmlDecode(encodeStr);
			console.log(decodeStr);
			jq(".wrap").on("click", ".start", function() {
				jq(".baby").html("Baby say I love you.")
			});
			var url = '/weact/APP/Modules/Extend/Tpl/getStudents.php';
			var param = {
				classId: 123456
			}
			var opts = {
				'noShowLoading': true,
				'noMsg': true,
				'success': function(result) {
					if (result.errcode == 0) {
						var tmpl = template("studentstpl", result.data);
						if (tmpl == '{Template Error}') {
							tmpl = ""
						}
						jq(".students").html(tmpl)
					} else {
						alert(result.errmsg)
					}
				}
			}
			jq.utilExt.ajax(url, param, opts)
		}
	};
	module.exports.init()
});