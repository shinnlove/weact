var utils = {
	namespace : function(str) {
		var arr = str.split(',');
		for ( var i = 0, len = arr.length; i < len; i++) {
			// 将命名空间切成N部分, 比如mini、common等
			var arrJ = arr[i].split("."), parent = {};
			for ( var j = 0, jLen = arrJ.length; j < jLen; j++) {
				var name = arrJ[j], child = parent[name];
				j === 0 ? eval('(typeof ' + name + ')==="undefined"?(' + name
						+ '={}):"";parent=' + name)
						: (parent = parent[name] = (typeof child) === 'undefined' ? {}
								: child);
			}
			;
		}
	},
	urlReplace : function(name, param) {
		var r = param.url || location.search.substring(1), reg = new RegExp(
				"(^|&)(" + name + "=)([^&]*)"), content = !param.content ? ""
				: param.content;
		return r.replace(reg, function(a, b, c, d) {
			return !content ? "" : b + c + content
		});
	},
	showBubble : function(content) {
		var node = $("#bubble");
		node.css("opacity", 1);
		if (!node.hasClass("qb_none")) {
			node.html(content);
			return;
		}
		node.html(content).removeClass("qb_none");
		setTimeout(function() {
			node.animate({
				opacity : 0
			}, 500, "ease", function() {
				$(this).addClass("qb_none").removeAttr("style");
			});
		}, 2300);
	},
	ajaxReq : function(opt, suc, error) {
		var option = {
			type : "GET",
			url : "",
			data : "",
			dataType : "html",
			timeout : 5000
		};
		$.extend(option, opt);
		if (!error) {
			error = function() {
				utils.showBubble("请求遇到错误，请重试");
			}
		}
		$.ajax({
			type : option.type,
			url : option.url,
			data : option.data,
			dataType : option.dataType,
			success : suc,
			error : error
		});
	},
	strReplace : function(template, json) {
		var s = template;
		for ( var d in json) {
			var reg = new RegExp("{#" + d + "#}");
			s = s.replace(reg, json[d]);
		}
		return s;
	},
	validate : function(rule,hideClass) {
		var errArr = [];
		$.each(rule, function(id, item) {
			var node = $("#" + id), value = node.val() + "";
			value = value.replace(/^\s*|\s*$/g, "");
			if (node.attr("disabled")) {
				return;
			}
			var valLen = value.length;
			if (item.dByte) {
				valLen = (value.replace(/[\u0391-\uFFE5]/g, "__")).length;
			}
			if (item.required) {
				if (value == "") {
					showError(item.emptyMsg || "您输入的" + item.itemName + "不能为空",
							id);
				} else if (value != "" && !item.reg.test(value)) {
					showError(item.errMsg, id);
				} else if (item.maxLen && valLen > item.maxLen) {
					showError(item.errMsg || "您输入的" + item.itemName + "超过"
							+ item.maxLen + "个字符", id);
				} else if (item.minLen && valLen < item.minLen) {
					showError(item.errMsg || "您输入的" + item.itemName + "不足"
							+ item.minLen + "个字符", id);
				} else if ((item.minVal && value < item.minVal)
						|| (item.maxVal && value > item.maxVal)) {
					showError("请输入" + item.minVal + "-" + item.maxVal + "的数字",
							id);
				} else {
					var err = $("#" + id + "_msg");
					err.addClass(hideClass || "false");
					item.callback && eval(item.callback + "(value, err)");
				}
			} else {
				if (value != "" && !item.reg.test(value)) {
					showError(item.errMsg, id);
				} else if (item.maxLen && valLen > item.maxLen) {
					showError(item.errMsg || "您输入的" + item.itemName + "超过"
							+ item.maxLen + "个字符", id);
				} else if (value != "" && item.minLen && valLen < item.minLen) {
					showError(item.errMsg || "您输入的" + item.itemName + "不足"
							+ item.minLen + "个字符", id);
				} else {
					$("#" + id + "_msg").addClass(hideClass || "false");
				}
			}
		});
		function showError(content, name) {
			var errNode = $("#" + name + "_msg");
			errNode.removeClass(hideClass || "false").html(content);
			errArr.push(name);
		}
		if (errArr.length > 0) {
			document.getElementById(errArr[0]).focus();
			return false;
		}
		return true;
	},
	cssProperty : (function() {
		var css3 = {}, docElement = document.documentElement, mod = 'modernizr';
		return {
			injectStyle : function(rule, callback) {
				var style, ret, node, div = document.createElement('div'), body = document.body, fakeBody = body
						|| document.createElement('body');
				style = [ '&#173;', '<style id="s', mod, '">', rule, '</style>' ]
						.join('');
				div.id = mod;
				(body ? div : fakeBody).innerHTML += style;
				fakeBody.appendChild(div);
				if (!body) {
					fakeBody.style.background = "";
					docElement.appendChild(fakeBody);
				}
				ret = callback(div, rule);
				!body ? fakeBody.parentNode.removeChild(fakeBody)
						: div.parentNode.removeChild(div);
				return !!ret;
			},
			pSupport : function(pName) {
				var style = docElement.style, css3s = 'Webkit Moz O ms'
						.split(' '), cstr = pName.charAt(0).toUpperCase()
						+ pName.substr(1), //首字母转换成大写
				rstr = (cstr + ' ' + css3s.join(cstr + ' ') + cstr).split(' '), //属性都加上前缀
				result = null;
				for ( var i = 0, len = rstr.length; i < len; i++) {
					if (rstr[i] in style) {
						result = rstr[i];
						break;
					}
				}
				return result;
			},
			has3d : function() {
				var ret = !!this.pSupport('perspective');
				if (ret && 'webkitPerspective' in docElement.style) {
					this
							.injectStyle(
									'@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}',
									function(node, rule) {
										ret = node.offsetLeft === 9
												&& node.offsetHeight === 3;
									});
				}
				return ret;
			}
		};
	})()
};

//<script type="text/javascript" src="js/tempjs/scroll-multi.js"></script>

$(function(){$(".scrollImage").scrollImage();})

try{!function(){function t(){this.init.apply(this,arguments)}t.prototype={init:function(t){this.url=window.location.href,this.hrefUrlPart=t,this.hrefHashPart="";var e=t.indexOf("#");0>e||(this.hrefUrlPart=t.substring(0,e),this.hrefHashPart=t.substring(e))},appendParam:function(t,e){return this.hrefUrlPart+=this.hrefUrlPart.indexOf("?")<0?"?"+t+"="+e:"&"+t+"="+e,this},getHref:function(){return this.hrefUrlPart+this.hrefHashPart},appendShopId:function(){var t=/[^\w]shopId=(\w+)/.exec(this.url);if(!t)return this;var e=/[^\w]shopId=(\w+)/.exec(this.hrefUrlPart);if(e)return this;var r=t[1];return this.appendParam("shopId",r),this},appendPtag:function(){var t=/[\?|&]ptag=([\.\w]+)/.exec(this.hrefUrlPart);return t?this:(this.appendParam("ptag","50001.1.1"),this)}},window.addEventListener("load",function(){for(var e=document.getElementsByTagName("A"),r={},a=function(){for(var t=[],e=0;e<localStorage.length;++e)try{var n=localStorage.key(e);if(0==n.search("pv-cache-")){var i=localStorage[n].split("\n");3!=i.length||r[i[0]]||(t.push({time:i[0],target:i[1],source:i[2]}),r[i[0]]=n)}}catch(h){}t.length?$.ajax({type:"POST",url:"http://weigou.qq.com/wkd/click.json",contentType:"application/json",data:JSON.stringify({pvs:t}),complete:function(e){for(var n="ok"==e.responseText,i=0;i<t.length;++i)n&&localStorage.removeItem(r[t[i].time]),delete r[t[i].time];setTimeout(a,3e3)}}):setTimeout(a,3e3)},n=0;n<e.length;++n)!e[n].href||0!=e[n].href.indexOf("http:")&&0!=e[n].href.indexOf("https:")||(e[n].href=new t(e[n].href).appendShopId().appendPtag().getHref(),e[n].addEventListener("click",function(t){var e=t.currentTarget.href;e&&e.search("weigou.qq.com/wkd/")<0&&(localStorage["pv-cache-"+(new Date).getTime()]=(new Date).getTime()+"\n"+e+"\n"+window.location.href,a())},!1));a()},!1)}()}catch(ex){}

//原搜索按钮js

$(document).ready(function(){
  
     $("#btn_search").click(function(){
     		
     		var sellerUin = $('#sellerUin').val();
     		var keywords= $('#keywords').val();
     		var totalNum= $('#totalNum').val();
     		var url = 'http://weigou.qq.com/wkd/modulepage/search/'+sellerUin+'/'+totalNum+'?keywords='+keywords;
			window.location=url;
	 })
  })
  
  <script type="text/javascript">
!function(){function a(a){"undefined"==typeof WeixinJSBridge||"undefined"==typeof WeixinJSBridge.invoke?setTimeout("loadWeixin("+a+")",200):a()}$(document).ready(function(){function b(a){return"A"!=a[0].tagName.toUpperCase()||"#"==a.prop("href")&&0==a.prop("href").indexOf("javascript")?!1:!0}$(document).on("click","IMG",function(){var c,d;$(this).attr("data-imgs")||(c=[],c[0]=this.src,d=$(this).parent(),b(d)||b(d.parent())||b(d.parent().parent())||a(function(){WeixinJSBridge.invoke("imagePreview",{current:c[0],urls:c})}))})})}();
</script>
<script>
$(function(){var e=/[\?|&]autobid=([\w]+)/.exec(window.location.search);e&&e[1]&&"false"==e[1]||-1!=window.location.href.indexOf("autobid=false")?$("a").each(function(){var e=$(this),t=e.attr("href");(0==t.indexOf("http://m.buy.qq.com/p/item/wxdetails.xhtml")||0==t.indexOf("http://weigou.qq.com/o2ov1/cn/item/preview.xhtml")||0==t.indexOf("http://mm.wanggou.com/item/weishop.shtml")||0==t.indexOf("http://bases.wanggou.com/itemweb/item?scence=102"))&&t.indexOf("?")>0&&t.indexOf("&bid=")>0&&e.attr("href",t.replace(/bid=\w+&?/g,"").replace(/(\?|&)$/,""))}):$("a").each(function(){var e=$(this),t=e.attr("href");(0==t.indexOf("http://m.buy.qq.com/p/item/wxdetails.xhtml")||0==t.indexOf("http://weigou.qq.com/o2ov1/cn/item/preview.xhtml")||0==t.indexOf("http://mm.wanggou.com/item/weishop.shtml")||0==t.indexOf("http://bases.wanggou.com/itemweb/item?scence=102"))&&t.indexOf("?")>0&&-1==t.indexOf("&bid=")&&e.attr("href",t+"&bid="+"1814851631")})});
</script>
<script type="text/javascript">
    function loadWeixin(f){
        (typeof WeixinJSBridge == 'undefined'||(typeof WeixinJSBridge.invoke == 'undefined'))?setTimeout('loadWeixin('+f+')',200):f()
    };  
    
    $(document).ready(function(){
       if(false && 0>0){
          loadWeixin(function(){  
             WeiXinAddContactDialog();  
         }); 
       }
       function WeiXinAddContactDialog(){
      	 WeixinJSBridge.invoke("addContact",{
    						webtype:"1", 					    // 添加联系人的场景，1表示企业联系人，不用改——kardelchen。
    						username:""　	// 需要添加的联系人username
    					},function(res){						
    						if(res.err_msg && (res.err_msg=='add_contact:ok' || res.err_msg=='add_contact:added')){
    							return;
    						}else{
    							return;
    						}
    					});
   		};
       
  
    });
    
(function(){
		
		function getWxShareData() {
			var img = '';
			var title = document.title;
			var desc = '';
			var url = window.location.href.replace(/([&|\?]{1})ticket=[\w\-]+(&?)/, '$1').replace(/\?$/, '');
			
			if(!img){
				var imgs = document.getElementsByTagName('IMG');
				if(imgs.length){
					img = imgs[0].src;
				}
			}
			
			return {
				'img_url' : img,
				'title' : title,
				'link' : url,
				'desc' : desc
			};
		}
		
		$(document).bind('WeixinJSBridgeReady', function() {
			WeixinJSBridge.on('menu:share:appmessage', function(){
				WeixinJSBridge.invoke('sendAppMessage', getWxShareData(), function(r){});
			});
			WeixinJSBridge.on('menu:share:timeline', function(){
				WeixinJSBridge.invoke('shareTimeline',getWxShareData(), function(r){});
			});
			WeixinJSBridge.on('menu:share:weibo', function(){
				var data = getWxShareData();
				data = {
					url : data.link,
					content : '【' + data.title + '】' + ' ' + data.desc + ' ' +  data.link,	
					img_url : data.img_url
				};
				WeixinJSBridge.invoke('shareWeibo', data, function(r){});
			});
		});
		
})();		
</script>

<script type="text/javascript">
	var _speedMark = new Date();
</script>





















