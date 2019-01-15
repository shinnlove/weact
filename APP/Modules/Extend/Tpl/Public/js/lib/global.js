// JavaScript Document
define('lib/global', ['lib/jquery'], function(require, exports, module){
	// 引入依赖
	require('lib/jquery');
	window.jq = jQuery.noConflict();
	
	// 扩展jQuery的ajax方法请求缓存数组
    jQuery.extend(jQuery.ajax, {
    	_requestCache:{}
    });
	
	// 扩展jQuery的lib
	jQuery.extend({
		utilExt:{
			charset: 'utf-8', // 当前js库中文编码utf-8，与html页面保持一致
			// set cookie
			setcookie: function (cookieName, cookieValue, seconds, path, domain, secure) {
                var expires = new Date();
                expires.setTime(expires.getTime() + seconds);
                document.cookie = escape(cookieName) + '=' + escape(cookieValue)
                    + (expires ? '; expires=' + expires.toGMTString() : '')
                    + (path ? '; path=' + path : '/')
                    + (domain ? '; domain=' + domain : '')
                    + (secure ? '; secure' : '');
            },
			// get cookie
			getcookie: function(name) {
                var cookies = document.cookie.split('; ');
                var len = cookies.length;
                for (var i = 0; i < len; i++) {
                    var cookie = cookies[i].split('=');
                    if (cookie[0] == name) {
                        return unescape(cookie[1]);
                    }
                }
                return '';
            },
			/**
			 * 检测数组中是否存在已知字符串或数字。
			 * @param needle 要查询的字符串|数字
			 * @param haystack 要搜索的数组
			 */
			in_array: function(needle, haystack) {
				if(typeof needle == 'string' || typeof needle == 'number') {
					for(var i in haystack) {
						if(haystack[i] == needle) {
							return true;
						}
					}
				}
				return false;
			},
			/**
			 * 解析url传参函数。
			 * @param str 要解析的url传参
			 * @param key 如果需要找寻某个key值，则返回某个key值，没找到返回null；如果不需要找寻则返回解析后的传参数组
			 */
			parseStr: function(str, key) {
				var params = str.split('&'); 	// 字符串以&分割name=shinnlove,pwd=abcd
				var query = {}; 				// 解析后的URL传参键值对
				var q = []; 					// 临时解析用的字符串数组，q[0]是name;q[1]是value
				var name = ''; 					// 临时存放urldecode后的key键名
	
				for (i = 0; i < params.length; i++) {
					q = params[i].split('='); 	// name=shinnlove再通过等号分割
					name = decodeURIComponent(q[0]); // decodeURIComponent，如果urlencode函数编码过，则可以转成正常的编码
					if (!name) {
						continue; // 空字符串处理下一个
					}
					if (name.substr(-2) == '[]') {
						// substr()形参如果是负数，-1代表最后一个字符到末尾即最后一个字符；-2代表倒数第二个字符到末尾即最后两个字符
						// 如果形参是个空数组
						if (!query[name]) {
							query[name] = [];
						}
						query[name].push(q[1]);
					} else {
						query[name] = q[1]; 	// 存入解析对象中
					}
				}
				
				// 如果形参传入key则寻找key
				if (key) {
					if (query[key]) {
						return query[key]; 	// 如果对象中该key索引存在则返回
					} else {
						return null; 		// 没找到key直接返回null
					}
				} else {
					return query; 			// 单纯解析URL则直接返回
				}
			},
			/**
			 * 查询url参数函数。
			 * @param key 可能要找寻的url的key值
			 */
			getQuery: function(key) {
				var search = window.location.search; // window.location.search返回连同?后边的参数
				if (search.indexOf('?') != -1) {
					// 如果search字符串中带上了url传参
					var str = search.substr(1); // 截取?后边到末尾的字符串
					return jQuery.utilExt.parseStr(str, key); // 进行解析
				}
			},
			/**
			 * 判断对象是否为空函数。
			 * 实现思路：巧妙的利用了for循环，如果循环了代表对象不空，return false；没进入循环return true
			 * @param obj 要检查是否为空的对象
			 */
			isObjectEmpty: function(obj) {
				for (i in obj) {
					return false;
				}
				return true;
			},
			/**
			 * 统计字符串长度函数（不含中文）。
			 * 小常识：/msie/为IE独有的userAgent，因此/msie/.test(navigator.userAgent.toLowerCase())就是判断是否为IE浏览器，true|false。
			 * 在IE浏览器中检测到字符串有\n换行，则替换\n为_，占1个字符位。
			 * @param str 要统计长度的字符串
			 * @return 返回字符串的length。
			 */
			strlen: function(str) {
				return (/msie/.test(navigator.userAgent.toLowerCase()) && str.indexOf('\n') !== -1) ? str.replace(/\r?\n/g, '_').length : str.length;
			},
			/**
			 * 统计含有中文字符串长度。
			 * 实现思路：charCodeAt()返回指定字符的unicode编码，依次来判断是英文字符还是中文字符。
			 * 英文字符或数字长度占1，charCodeAt()函数返回在[0,255]区间的数字。
			 * 第0～32号及第127号(共34个)是控制字符或通讯专用字符，如控制符：LF（换行）、CR（回车）、FF（换页）、DEL（删除）、BEL（振铃）等
			 * 第33～126号(共94个)是字符，其中第48～57号为0～9十个阿拉伯数字；65～90号为26个大写英文字母，97～122号为26个小写英文字母，其余为一些标点符号、运算符号等。
			 * 127~255之间有很多法文、西欧的字符。
			 * 情形：str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255表示非单字节英文字母。
			 * 一旦非英文字符，以中文汉字编码方式来判断长度：
			 * i) 如果当前汉字编码方式为utf-8则占据3个长度（依据本js的charset设置，与html页面保持一致）；
			 * ii)不是utf-8（如GBK或GB2312编码）则占据2个长度。
			 * @param str 要统计长度的含有中文的字符串
			 */
			mb_strlen: function(str) {
				var len = 0;
				for(var i = 0; i < str.length; i++) {
					len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (jQuery.utilExt.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1;
				}
				return len;
			},
			/**
			 * 当显示的中文字符串过长的裁剪显示函数。
			 * 英文西欧字符长度1；utf-8中文长度3；gbk和gb2312中文长度2。
			 * @param str 要截取的带中文字符串
			 * @param maxlen 最大允许显示长度
			 * @param dot 截取后多余部分显示代替符号
			 */
			mb_cutstr: function(str, maxlen, dot) {
				var len = 0; // 当前统计到第几个字符
				var ret = ''; // 截取后的字符串
				var dot = !dot && dot !== '' ? '...' : dot; // 如果dot未定义默认...显示超长部分
				maxlen = maxlen - dot.length; // 最大允许显示长度要减去dot的长度
				for(var i = 0; i < str.length; i++) {
					len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (jQuery.utilExt.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1; 
					if(len > maxlen) {
						ret += dot; // 如果超过长度的时候，ret截取字符串再带上...
						break;
					}
					ret += str.substr(i, 1); // 每次从i位置截取1个字符给ret
				}
				return ret;
			},
			/**
			 * 计算当前还可以输入字符数函数。
			 * 只有纯数字和英文才算1个length，其他字符全部当作2字符处理。
			 * @param obj obj.value是当前输入的内容字符串
			 * @param showId 结果显示到DOM结构的id
			 * @param maxlen 最大可以输入的字符数，这是英文字符长度
			 */
			strLenCalc: function(obj, showId, maxlen) {
				var v = obj.value, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = jQuery.utilExt.strlen(v); // 默认可以输入200字符
				for(var i = 0; i < v.length; i++) {
					if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 127) {
						curlen -= 2; // 其余字符全部算2个字符
					} else {
						curlen -= 1; // 纯英文、数字、标点、控制字符、括号等算1个字符
					}
				}
				return Math.floor(curlen / 2); // 显示还可以输入的字符数
			},
			/**
			 * html格式文本编码成转义html格式。
			 * @param String text 要转义编码的html文本
			 * @return String 编码后的html文本
			 */
			htmlEncode: function(text){
				return text.replace(/&/g, '&amp').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			},
			/**
			 * 转义html格式文本解码成html格式。
			 * @param String text 要解码的转义html文本
			 * @return String 转义成正确的html格式文本
			 */
			htmlDecode: function(text){
				return text.replace(/&amp;/g, '&').replace(/&quot;/g, '/"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
			},
			// 下面扩展自定义弹窗
			/**
             * 自定义对话框主调函数，延时5毫秒调用。
             * param opts 对话框参数
             */
            dialog: function(opts) {
                setTimeout(function() {
                    jQuery.utilExt._dialog(opts);
                }, 5);
            },
            /**
             * 自定义对话框私有函数，被dialog调用。
             * @param opts 对话框参数
             * @property String id 对话框DOM的id，默认tips；会拼接生成fwin_dialog_id值（必须传入的值，否则默认是tips）
             * @property String title 对话框标题
             * @property String content 对话框内容
             * @property String okValue 对话框确认键文字
             * @property String cancelValue 对话框取消键文件
             * @property boolean isMask 对话框弹出是否需要遮罩层
             * @property boolean isHtml 是否以html格式显示信息
             * @property boolean autoClose 对话框是否自动关闭
             * @property boolean isConfirm 对话框是否询问类型
             * @property String icon 对话框图标的css样式class名称
             * @property function ok 对话框确认执行函数
             * @property function cancel 对话框取消执行函数
             * @property function close 对话框关闭回调函数，执行完必须返回true|false，根据返回结果来选择是否关闭对话框
             */
            _dialog: function(opts) {
                var opts = opts || {}; 
                // var dId = opts.id || parseInt((Math.random()*6)+1); // id生成策略，这样生成每次都要控制传入正确的id
                var dId = opts.id || 'tips';
                var dialogId = 'fwin_dialog_' + dId; 	// 拼接当前dialog的DOMid
                var maskId = 'fwin_mask_' + dId; 		// 拼接当前遮罩层的DOMid
                
                // 如果未传content内容则表示关闭对应的dialog的窗口
                if (!opts.content) {
                    document.ontouchmove = function(e){ return true;} 	// 允许safari等浏览器可以拖动了
                    jQuery('#' + dialogId).remove(); 					// 移除对话框
                    jQuery('#' + maskId).remove(); 						// 移除遮罩层
                    return false;
                }
                
                // 对话框属性设置
                var title = opts.title || '提示信息'; // title
                var content = opts.content || ''; // msg
                var btnOk = opts.okValue || false; // ok
                var btnCancel = opts.cancelValue || false; // cancel
                var isShowMask = opts.isMask || false; // is show mask
                
                // 新对话框盖掉老对话框（zindex在上边），计算新对话框与其遮罩层的zindex
                var existDialogCount = jq('div[id^="fwin_dialog_"]').length || 0; // 当前还有没有其他的对话框
                var maskZIndex = 10000 + existDialogCount * 10; // 对话框遮罩层的zindex值，一般html中zindex最高设置为9999，这里对话框高于页面元素，所以起始位置10000开始
                var dialogZIndex = maskZIndex + 1; // 本次新对话框zindex在遮罩层上额外加1

                // mask style（对话框遮罩层：动态获取屏幕宽和高，满屏幕设置黑色，60%透明度）
                var maskStyle = 'position:absolute;top:-0px;left:-0px;width:' + jQuery(document).width() + 'px;height:' + jQuery(document).height() + 'px;background:#000;filter:alpha(opacity=60);opacity:0.5; z-index:' + maskZIndex + ';';

                var isHtml = opts.isHtml || false;

                var autoClose = opts.autoClose || false;

                var isConfirm = opts.isConfirm || false;

                var iconClass = ''; // 对话框的图标的css的class名称
                switch (opts.icon) {
                    case 'success':
                        iconClass = 'icon_success'; // 成功css图标，如：jQuery.utilExt.dialog({content:result.message, icon:'success', autoClose:true});
                        break;
                    case 'none':
                        iconClass = ''; 			// 不要图标
                        break;
                    case 'error':
                    	iconClass = ''; 			// 错误css图标，如：jQuery.utilExt.dialog({content:result.errmsg, icon:'error', autoClose:true});
                    	break;
                    default:
                        iconClass = 'g-layer-tips'; // 默认是提示
                        break;
                }
                
                // 处理对话框遮罩层
                if (isShowMask) {
                    var dialogMaskHtmlArr = []; // 遮罩层的htmlDOM内容
                    dialogMaskHtmlArr.push('<div id=' + maskId + ' class="g-mask" style="' + maskStyle + '"></div>'); // 创建出遮罩层的div
                    var dialogMaskHtml = dialogMaskHtmlArr.join('');
                    jQuery(dialogMaskHtml).appendTo('body'); // 追加到body后
                    document.ontouchmove = function(e){ e.preventDefault();} // 重要：禁止拖动的默认事件：针对 IOS 上的 safari，目的是禁止默认的拖动事件，但允许其中某div的滚动拖拽
                }
                
                // 自定义弹窗
                var dialogHtmlArr = []; // 对话框的htmlDOM内容
                if (isHtml) {
                	// 纯显示内容
                    dialogHtmlArr.push('<div style="width:100%;position:fixed;z-index:' + dialogZIndex +';" id="' + dialogId + '">' + content + '</div>'); // 创建对话框DOM
                } else {
                    if (!opts.title && !btnOk && !btnCancel) {
                    	// 无标题、无确认或取消，简单的弱提示，如：jQuery.utilExt.dialog({content:msg, autoClose:true});
                        dialogHtmlArr.push('<div style="position:fixed;z-index:' + dialogZIndex +';" id="' + dialogId + '"><div class="tips br5">'); // 追加提示层
                        if (dId == 'loading') {
                            // 如果是loading等待，如：jQuery.utilExt.dialog({id:'loading'});
                            dialogHtmlArr.push('<div class="loadicon tipL" style="vertical-align: -5px;"><span class="blockG" id="rotateG_01"></span><span class="blockG" id="rotateG_02"></span><span class="blockG" id="rotateG_03"></span><span class="blockG" id="rotateG_04"></span><span class="blockG" id="rotateG_05"></span><span class="blockG" id="rotateG_06"></span><span class="blockG" id="rotateG_07"></span><span class="blockG" id="rotateG_08"></span></div> ');
                        }
                        dialogHtmlArr.push(content + '</div></div>');
                    } else if (isConfirm) {
                    	// 确认型对话框，询问是否XXX
                        if (confirm(content)) {
                        	// 确认对话框后如果有ok函数，则执行
                            if (typeof opts.ok == 'function') {
                                opts.ok();
                            }
                        } else {
                        	// 取消对话框后，如果有cancel函数，则执行
                            if (typeof opts.cancel == 'function') {
                                opts.cancel();
                            }
                        }
                        return true;
                    } else {
                    	// 对话框的其他情况
                        dialogHtmlArr.push('<div style="min-width:350px;position:fixed;z-index:' + dialogZIndex +';" id="' + dialogId + '"><span class="close"></span>');
                        dialogHtmlArr.push('<div class="popLayer pSpace" style="width:80%">');
                        dialogHtmlArr.push('<p class="editTCon">' + content + '</p>');
                        dialogHtmlArr.push('<div class="editArea">');
                        dialogHtmlArr.push(btnOk ? '<a href="javascript:;" class="editBtn1 db" title="">' + btnOk + '</a>' : '');
                        dialogHtmlArr.push(btnCancel ? '<a href="javascript:;" class="editBtn2 db" title="">' + btnCancel + '</a>' : '');
                        dialogHtmlArr.push('</div></div>');
                    }
                }
                var dialogHtml = dialogHtmlArr.join(''); // 拼接最终生成的对话框html
                
                // 额外做个处理：如果一直没有传入id使用默认tips，则相同id对话框存在，必须移除先前的对话框，同一个页面上不允许出现2个相同id
                if (jQuery('#' + dialogId)[0]) {
                    jQuery('#' + dialogId).remove();
                    jQuery('#' + maskId).remove();
                }
                jQuery(dialogHtml).appendTo('body'); 	// 向DOM结构写入对话框
                
                // 定位对话框，保证出现在屏幕最中央
                // 特别注意：$(xxx).outerWidth()方法用于获得包括内边界(padding)和边框(border)的元素宽度，如果outerWidth()方法的参数为true则外边界(margin)也会被包括进来
                var clientWidth = jQuery(window).width(); 	// 获取窗体宽度
                var clientHeight = jQuery(window).height(); // 获取窗体高度
                var dialogLeft =  (clientWidth - jQuery('#' + dialogId).outerWidth()) / 2; // 对话框距离左端：屏幕宽度减去对话框宽度除以2
                var dialogTop =  (clientHeight - jQuery('#' + dialogId).height()) * 0.382; // 对话框距离上端：屏幕高度减去对话框高度除以2
                
                // position left
                dialogLeft = opts.left || dialogLeft;
                // position top
                dialogTop = typeof opts.top === 'undefined' ? dialogTop : opts.top;

                jQuery("#" + dialogId).css({ "top": dialogTop + "px", "left": dialogLeft + "px" }); // 控制对话框显示位置

                // close click，绑定对话框关闭事件
                jQuery('#' + dialogId + ' .close').click(function() {
                    if (isShowMask) {
                        document.ontouchmove = function(e){ return true;} // 有弹出遮罩层的，取消的时候允许safari拖动
                    }
                    var closeCBResult = true; // 对话框正确关闭
                    // close callback
                    if (typeof opts.close == 'function') {
                        closeCBResult = opts.close(); 		// close的方法中必须返回true|false
                    }
                    if (closeCBResult) {
                        jQuery('#' + maskId).hide(); 		// 遮罩层隐藏
                        jQuery('#' + maskId).remove(); 		// 移除遮罩层
                        jQuery('#' + dialogId).hide(); 		// 对话框隐藏
                        jQuery('#' + dialogId).remove(); 	// 移除对话框
                    }
                });
                
                // callback
                if (typeof opts.callback == 'function') {
                    if (isShowMask) {
                        document.ontouchmove = function(e){ e.preventDefault();}
                    }
                    opts.callback();
                }

                // ok callback，绑定确认按钮事件
                if (typeof opts.ok == 'function') {
                    jQuery('#' + dialogId + ' .editBtn1').click(function() {
                        opts.ok();
                    });
                }

                // cancel callback，绑定取消按钮事件
                if (typeof opts.cancel == 'function') {
                    jQuery('#' + dialogId + ' .editBtn2').click(function() {
                        opts.cancel();
                    });
                }
                
                // 点击确认按钮关闭对话框
                if (jQuery('#' + dialogId + ' .editBtn1')[0]) {
                    jQuery('#' + dialogId + ' .editBtn1').click(function() {jQuery('#' + dialogId + ' .close').click()});
                }
                
                // 点击取消按钮关闭对话框
                if (jQuery('#' + dialogId + ' .editBtn2')[0]) {
                    jQuery('#' + dialogId + ' .editBtn2').click(function() {jQuery('#' + dialogId + ' .close').click()});
                }
                
                // 自动关闭弹窗对话框
                if (!opts.title && !btnOk && !btnCancel && autoClose) {
                    autoClose = autoClose > 1 ? autoClose : 1000; 		// 默认1秒关闭
                    // 定时autoClose秒后执行jQuery动画效果fadeOut，缓慢
                    setTimeout(function() {
                        jQuery('#' + dialogId).fadeOut('slow', function() {
                            jQuery('#' + maskId).hide(); 		// 遮罩层隐藏
                            jQuery('#' + maskId).remove(); 		// 移除遮罩层
                            jQuery('#' + dialogId).hide(); 		// 对话框隐藏
                            jQuery('#' + dialogId).remove(); 	// 移除对话框
                            // close callback
                            if (typeof opts.close == 'function') {
                                opts.close(); 					// 关闭对话框如果有回调函数，则执行
                            }
                        });
                    }, autoClose);
                }
            },
			/**
             * 显示自定义加载等待对话框，依据_dialog封装的显示等待函数。
             * @param String display 对话框显示样式，默认block居中显示；none就是关闭对话框
             * @param String waiting 对话框等待时显示字样
             * @param boolean|int autoClose 是否自动关闭，true代表默认1秒自动关闭；需要指定3秒则autoClose = 3000
             */
            showLoading: function(display, waiting, autoClose) {
                var display = display || 'block';		//有没有指定显示参数，没有的话默认block
                var autoClose = autoClose || false;		//有没有指定自动关闭，没有的话不自动关闭
                waiting = waiting || '正在加载...';		//有没有指定提示消息，没有的话默认正在加载...
                if (display == 'block') {
                    //jQuery.utilExt.dialog({id:'loading', content:waiting, noMask:true, autoClose:autoClose});	//不遮罩显示loading
                    jQuery.utilExt.dialog({id:'loading', content:waiting, isMask:false, autoClose:autoClose});	//不遮罩显示loading
                } else {
                    jQuery.utilExt.dialog({id:'loading'});														// 关闭loading对话框
                }
            },
			// 扩展最重要的ajax请求
			/**
             * ajax请求数据函数。
             * @param url 请求参数的路径
             * @param data 请求附带的数据
             * @param opts 请求前后、完成、失败等要做的option
             * @property boolean cache 是否缓存请求
             * @property boolean isUpload 是否ajax上传数据，要将发送数据专为对象
             * @property String dataType ajax提交使用的数据类型：xml|html|script|json|jsonp|text，最常使用json，跨域js使用jsonp
             * @property int timeout ajax超时时间
             * @property String success jsonp方式跨域请求服务端响应函数名
             * @property String requestIndex 发生ajax请求的DOM结构id，用于阻塞请求或终止请求
             * @property String requestMode ajax请求类型，当传入请求id时候一般使用abort或者block
             * @property boolean noShowLoading 发送ajax期间是否要显示等待框
             * @property boolean noMsg 不要提示信息
             */
            ajax: function(url, data, opts) {
            	var opts = opts || {};
                var loadingTimer = ''; // 延时loading器
                
                // 处理url为json
                url = url.indexOf('?') === -1 ? url + '?' : url + '&';
                url = url.replace(/\&resType\=[^\&]+/g, '') + 'resType=json';
                url = url.replace(/\&isAjax\=1/g, '') + '&isAjax=1';
                
                // 定义jQuery的ajax方法参数和执行结构体（调用方法见newthread.js的563~591行）
                var ajaxOpts = {
                    url: url, 							// 要请求的url
                    data: data, 						// 请求要传递的参数
                    cache: opts.cache || false,			// 属性设置是否使用缓存请求，设置为false将不会从浏览器缓存中加载请求信息
                    processData: opts.isUpload, 		// 默认情况下，发送的数据将被转换为对象（从技术角度来讲并非字符串）以配合默认内容类型"application/x-www-form-urlencoded"。如果要发送DOM树信息或者其他不希望转换的信息，请设置为false。
                    contentType: opts.isUpload ? false : 'application/x-www-form-urlencoded', // 是上传数据吗，不是就是默认表单encode
                    type: data ? 'POST' : 'GET',		// 提交数据一律用POST，如果没有提交数据，仅仅是请求url，用GET
                    dataType: opts.dataType || 'json',	// opts中没有指定dataType就默认使用json
                    timeout: opts.timeout || 30000, 	// 默认30秒超时
                    jsonp: opts.dataType === 'jsonp' ? 'callback' : null, // 是jsonp传输回调函数默认用callback
                    jsonpCallback: opts.dataType === 'jsonp' ? opts.success : null, // json传输状态下服务端响应函数用opts.success
                    /**
                     * ajax请求发送到服务器之前激活的回调函数，如果return false则取消此次请求。
                     * beforeSend回调使得程序有机会在XMLHttpRequest对象上设置自定义HTTP头部。
                     * 特别注意：跨域的"script"和"jsonp"请求没有使用XMLHttpRequest对象，因此不会触发beforeSend！！！
                     * 这里通常用来阻止请求或者显示等待框。
                     * @param object XHR XMLHttpRequest对象
                     * @param object option 该请求的选项对象
                     */
                    beforeSend: function(XHR, option) {
                        if (opts.requestIndex) {
                        	// 如果传入了ajax请求索引
                            if (opts.requestMode == 'block') {
                            	// 如果是阻塞ajax请求
                                if (jQuery.ajax._requestCache[opts.requestIndex]) {
                                    return false; // 取消本次ajax请求
                                }
                            } else if (opts.requestMode == 'abort') {
                            	// 取消ajax请求
                                if (jQuery.ajax._requestCache[opts.requestIndex]) {
                                    jQuery.ajax._requestCache[opts.requestIndex].abort(); // 根据传入取消的id，终止该ajax请求
                                }
                            }
                        }
                        
                        var result = true; // 默认提交本次ajax
                        if (typeof opts.beforeSend == 'function') {
                            result = opts.beforeSend(XHR, option); 	// 执行执行前要做的事情，如果返回false则不提交ajax了
                        }

                        if (result) {
                            jQuery.ajax._requestCache[opts.requestIndex] = XHR; // 保存XHR对象到请求缓存中
                            // 要显示loading
                            if (!opts.noShowLoading) {
                                loadingTimer = setTimeout(function() {
                                    jQuery.utilExt.showLoading();
                                }, 100);
                            }
                        }
                        
                        return result; // return false取消本次ajax
                    },
                    /**
                     * ajax请求成功完成时的回调函数。
                     * @param object result 服务器响应的数据：如果是xml则为document；如果是json|jsonp则是JSON格式解析结果...
                     * @param String jqStatus 通常是字符串success
                     * @param object curXHR 当前发送该ajax请求的XMLHttpRequest对象
                     */
                    success: function(result, jqStatus, curXHR) {
                        clearTimeout(loadingTimer); // 取消延迟执行
                        jQuery.utilExt.showLoading('none'); // 关闭对话框
                        
                        // 没有收到回复
                        if (result == null && !opts.noMsg) {
                            jQuery.utilExt.dialog({content:'您的网络有些问题，请稍后再试 [code:1]', autoClose:true});
                        }
                        
                        if (typeof result !== 'object') {
                            result = jQuery.parseJSON(result);
                        }
                        
                        // 执行成功的回调函数
                        if (typeof opts.success == 'function') {
                            opts.success(result, jqStatus, opts);
                        }
                        
                        // 执行成功
                        if (result.errCode == 0) {
                        	// 是否要提示成功
                            if (!opts.noMsg) {
                                jQuery.utilExt.dialog({content:result.message, icon:'success', autoClose:true}); // 提示成功
                            }
                            // 是否需要跳转
                            if (!opts.noJump && result.jumpURL) {
                                var locationTime = result.locationTime || 2000;
                                jQuery.utilExt.reload(result.jumpURL, locationTime);
                            }
                        } else if (result.errCode) {
                        	// 有错误码，则提示错误信息
                            if (!opts.noMsg) {
                                var msg = result.message + '<span style="display:none;">' + result.errCode + '</span>';
                                jQuery.utilExt.dialog({content:msg, autoClose:true});
                            }
                        } else {
                        	// 没有解析出正确格式，解析失败~
                            if (!opts.noMsg) {
                                jQuery.utilExt.dialog({content:'数据解析失败，请稍后再试 [code:2]', autoClose:true});
                            }
                        }
                    },
                    /**
                     * ajax请求出错的回调函数。
                     * @param object XHR 当前发起ajax请求的XMLHttpRequest对象
                     * @param String jqStatus jQuery状态码
                     * @param object errThrown 抛出的error对象
                     */
                    error: function(XHR, jqStatus, errorThrown) {
                        clearTimeout(loadingTimer);
                        jQuery.utilExt.showLoading('none');

                        if (XHR.readyState == 0 || XHR.status == 0) {
                            if (!opts.noMsg) {
                                var msg = '您的网络有些问题，请稍后再试[code:4]';
                                jQuery.utilExt.dialog({content: msg, autoClose:true});
                            }
                        } else if (jqStatus != 'abort' && !opts.noMsg) {
                            if (!opts.noMsg) {
                                var msg = '';
                                switch (jqStatus) {
                                    case 'timeout':
                                        msg = '对不起，请求服务器网络超时'; // HTTP超时
                                        break;
                                    case 'error':
                                        msg = '网络出现异常，请求服务器错误'; // HTTP错误
                                        break;
                                    case 'parsererror':
                                        msg = '网络出现异常，服务器返回错误'; // 解析XML或者JSON对象不符合格式
                                        break;
                                    case 'notmodified': // 如果设置ifModified选项，第一个参数是undefined
                                    default:
                                        msg = '您的网络有些问题，请稍后再试[code:3]';
                                }
                                jQuery.utilExt.dialog({content: msg, autoClose:true});
                            }
                        }
                        if (typeof opts.error == 'function') {
                            opts.error();
                        }
                    },
                    /**
                     * ajax请求完成时激活的回调函数。
                     * 特别注意：每一个ajax请求成功调用success，失败调用error；在这之后，jQuery会调用complete回调。
                     * 声明：虽然可以统一在complete中处理loading消失，但是success和error还有操作DOM结构的时间，还是在得到响应操作DOM结构之前就取消loading框。
                     * @param object XHR XMLHttpRequest对象
                     * @param int status 服务器返回的状态码
                     */
                    complete: function(XHR, status) {
                        if (jQuery.ajax._requestCache[opts.requestIndex]) {
                            jQuery.ajax._requestCache[opts.requestIndex] = null; // 请求结束把对应xhr请求数组中的内容清除
                        }
                        // 请求成功或失败时均调用这个函数
                        if (typeof opts.complete == 'function') {
                            opts.complete(XHR, status);
                        }
                    }
                };
                jQuery.ajax(ajaxOpts); // 调用jQuery的ajax方法
				
                return false;
            }
		}
	});
	
	// 扩展当前用户操作系统
    jQuery.extend({
        os:{
            ios : false,
            android: false,
            version: false
        }
    });
	
	// 获取浏览器请求头（立即执行获得用户浏览器请求头）
    (function() {
        var ua = navigator.userAgent;
        var browser = {},
            weixin = ua.match(/MicroMessenger\/([^\s]+)/),
            webkit = ua.match(/WebKit\/([\d.]+)/),
            android = ua.match(/(Android)\s+([\d.]+)/),
            ipad = ua.match(/(iPad).*OS\s([\d_]+)/),
            ipod = ua.match(/(iPod).*OS\s([\d_]+)/),
            iphone = !ipod && !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/),
            webos = ua.match(/(webOS|hpwOS)[\s\/]([\d.]+)/),
            touchpad = webos && ua.match(/TouchPad/),
            kindle = ua.match(/Kindle\/([\d.]+)/),
            silk = ua.match(/Silk\/([\d._]+)/),
            blackberry = ua.match(/(BlackBerry).*Version\/([\d.]+)/),
            mqqbrowser = ua.match(/MQQBrowser\/([\d.]+)/),
            chrome = ua.match(/CriOS\/([\d.]+)/),
            opera = ua.match(/Opera\/([\d.]+)/),
            safari = ua.match(/Safari\/([\d.]+)/);
        if (weixin) {
           jQuery.os.wx = true;
           jQuery.os.wxVersion = weixin[1];
        }
        // if (browser.webkit = !! webkit) browser.version = webkit[1]
        if (android) {
            jQuery.os.android = true;
            jQuery.os.version = android[2];
        }
        if (iphone) {
            jQuery.os.ios = jQuery.os.iphone = true;
            jQuery.os.version = iphone[2].replace(/_/g, '.');
        }
        if (ipad) {
            jQuery.os.ios = jQuery.os.ipad = true;
            jQuery.os.version = ipad[2].replace(/_/g, '.');
        }
        if (ipod) {
            jQuery.os.ios = jQuery.os.ipod = true;
            jQuery.os.version = ipod[2].replace(/_/g, '.');
        }
        // 定义全局htmlEncode编码
		/* 现在把全局的编码封装到jQuery.utilExt里去
        window.htmlEncode = function(text) {
            return text.replace(/&/g, '&amp').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        // 定义htmlDecode编码
        window.htmlDecode = function(text) {
            return text.replace(/&amp;/g, '&').replace(/&quot;/g, '/"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        }
		*/
        // 网络类型定义
        window.NETTYPE = 0;
        window.NETTYPE_FAIL = -1;
        window.NETTYPE_WIFI = 1;
        window.NETTYPE_EDGE = 2;
        window.NETTYPE_3G = 3;
        window.NETTYPE_DEFAULT = 0;
    })();
	
});