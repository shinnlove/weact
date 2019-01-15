// my jquery extends utility lib
$.extend({
	myUtilExt:{
		charset: 'utf-8', // 当前js库中文编码utf-8，与html页面保持一致
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
				return jQuery.myUtilExt.parseStr(str, key); // 进行解析
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
				len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (jQuery.myUtilExt.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1;
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
				len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (jQuery.myUtilExt.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1; 
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
			var v = obj.value, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = jQuery.myUtilExt.strlen(v); // 默认可以输入200字符
			for(var i = 0; i < v.length; i++) {
				if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 127) {
					curlen -= 2; // 其余字符全部算2个字符
				} else {
					curlen -= 1; // 纯英文、数字、标点、控制字符、括号等算1个字符
				}
			}
			jQuery('#' + showId).html(Math.floor(curlen / 2)); // 显示还可以输入的字符数
		}
	}
});