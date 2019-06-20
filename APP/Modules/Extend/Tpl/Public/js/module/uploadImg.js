/**
 * my diy web image upload module.
 * @author shinnlove
 * @create 2016/02/08 01:23:26.
 */
define('module/uploadImg', ['module/jpegMeta', 'module/imageCompresser'], function(require, exports, module){
	
	var jpegMeta = require('module/jpegMeta');
	var imageCompresser = require('module/imageCompresser');
	
	module.exports = {
		
		uploadURL : "/Extend/ImageUpload/uploadHandle", // 模块上传图片需要提交到的服务端地址
		
		singleUploadMaxSize : 1024*1024*8.5, // 单张上传图片最大尺寸，这里允许略微超过一点点（文件头）
		
		maxUploadNum : 9, // 最大可上传图片，这里定为9张，和微信一样
		
		DOMUploadRemainTip : ".upload-remain", // 上传数量变更提醒到页面的DOM结构
		
		uploadQueue : [], // 上传队列
		
		previewQueue : [], // 预览队列
		
		uploadInfo : {}, // 上传文件信息
		
		xhr : {}, // XMLHttpRequest对象数组
		
		isBusy : false, // 本模块是否正在上传图片
		
		/**
		 * 统计已上传图片数量
		 */
		countUpload:function(){
			var num = 0;
			jq.each(module.exports.uploadInfo, function(i, e) {
                if (e) {
                    ++ num;
                }
            });
			return num;
		},
		/**
		 * 预览图片函数。
		 * @param string id 要预览的图片id
		 */
		previewUpload:function(id){
			// 判断当前浏览器是否支持FileReader对象
			if (typeof FileReader == "undefined") {
				jq.utilExt.dialog({content:'当前环境不支持预览',autoClose:true});
				jq("#"+id).remove();
				return false;
			}
			
			var reader = new FileReader();
			var uploadBase64; // 图片压缩后的uploadBase64
			var conf = {}, imgfile = module.exports.uploadInfo[id].file; // 定义预览配置、取出图片文件
			
			// wifi下预览图片高质量
            if (window.NETTYPE == window.NETTYPE_WIFI) {
                conf = {
                    maxW: 3000, 	// 目标宽
                    maxH: 1280, 	// 目标高
                    quality: 0.8, 	// 目标jpg图片输出质量
                };
            }
            
            // FileReader读入图片
            reader.onload = function(e){
            	var result = this.result; 
            	
            	// 如果后缀名为image/jpeg是jpeg图片，检测图片格式并读取图片拍摄方向，自动旋转
                if (imgfile.type == 'image/jpeg'){
                    try {
                        var jpg = new jpegMeta.JpegMeta.JpegFile(result, imgfile.name); // 检测是否jpeg格式图片
                    } catch (e) {
                    	//console.log(e);
                    	jq.utilExt.dialog({content:'图片不是正确的图片数据', autoClose:true});
                        jq('#' + id).remove();
                        return false;
                    }
                    if (jpg.tiff && jpg.tiff.Orientation) {
                        //设置旋转
                        conf = jq.extend(conf, {
                            orien: jpg.tiff.Orientation.value
                        });
                    }
                }
            	
            	if (imageCompresser.ImageCompresser.support()) {
            		// 如果支持本地压缩
                    var img = new Image();
                    img.onload = function() {
                        try {
                        	// 在image的load方法中，this指代的就是image本身
                            uploadBase64 = imageCompresser.ImageCompresser.getImageBase64(this, conf); // 核心：调用imageCompresser对象中的成员变量ImageCompresser的getImageBase64方法压缩图片
                        } catch (e) {
                            jq.utilExt.dialog({content: '压缩图片失败', autoClose:true});
                            jq('#' + id).remove();
                            return false;
                        }
                        if (uploadBase64.indexOf('data:image') < 0) {
                            jq.utilExt.dialog({content: '上传照片格式不支持', autoClose:true});
                            jq('#' + id).remove();
                            return false;
                        }
                        // 支持压缩下、成功压缩后
                        module.exports.uploadInfo[id].file = uploadBase64; // 转成附带二进制流的图片信息
                        jq('#' + id).css('background-image', 'url('+uploadBase64+')');
                        //console.log(uploadBase64);
                        module.exports.uploadQueue.push(id); // 把图片文件同时加入上传队列
                    }
                    img.onerror = function() {
                    	jq.utilExt.dialog({content:'解析图片数据失败', autoClose:true});
                        jq('#' + id).remove();
                        return false;
                    }
                    img.src = imageCompresser.ImageCompresser.getFileObjectURL(imgfile); // 追加文件所在位置
            	} else {
            		// 如果本地不支持压缩
            		uploadBase64 = result; // 如果filereader的结果不是data:image开头
                    if (uploadBase64.indexOf('data:image') < 0) {
                    	jq.utilExt.dialog({content:'上传照片格式不支持', autoClose:true});
                        jq('#' + id).remove();
                        return false;
                    }
                    // 如果读入图片文件格式正确
                    module.exports.uploadInfo[id].file = uploadBase64;
                    jq('#' + id).css('background-image', 'url('+uploadBase64+')');
                    module.exports.uploadQueue.push(id); // 把图片文件同时加入上传队列
            	}
            }
            reader.readAsBinaryString(module.exports.uploadInfo[id].file); // 读取压缩后的图片
		},
		/**
		 * 上传图片函数。
		 * @param String id 要上传的DOM结构索引
		 * @param String callmodule 外部调用本模块createUpload的模块名称
		 * @param object checkUploadTimer 是否定时检测上传（针对大图片上传延迟）
		 */
		createUpload:function(id, callmodule, checkUploadTimer){
			// 检测是否真的有上传任务
			if (!module.exports.uploadInfo[id]) {
				return false;
			}
			
			var uploadurl = module.exports.uploadURL + "?t=" + Date.now(); // XHR要提交的地址，带上时间戳
			
			var imgfile = module.exports.uploadInfo[id].file;// 取出图片
			
			var formData = new FormData(); // 新建一个表单对象
			formData.append("img-upload", imgfile); // 附加一个要上传的图片到表单中
			
			// 定义上传监听事件函数
			// 上传中
			var progress = function(e){
				/**
				 * 特别注意：这里console.log("progress"+e.target.response); → undefined，进度事件中并没有e.target.response只说，load事件里有。
				 */
                if (e.LengthComputable) {
					var percent = Math.round( e.loaded / e.total * 100 ); // 计算进度百分比
					// 控制进度条不要超出（一般不会）
					if (percent > 100) {
	                    percent = 100;
	                }
				}
                jq("#"+id+" .uploading .progressBar .progress").animate({'width': '95%'}, 300); 
                //0.4秒内执行
                setTimeout(function(){
                    if (percent == 100) {
                    	doneUpload(id);							//完成上传图片
                        if(checkUploadTimer){
                            clearInterval(checkUploadTimer); 	// 特别注意：如果这张图片的上传请求被正确响应了，取消因为要上传这张图片而存在的定时检测请求器，配合newthread.js中114行
                        }
                    } 
                }, 400);
			},
			// 上传完成时，移除进度条统一在complete中做
			complete = function(e){
				// 完成时候先解决进度条的问题
				jq("#"+id+" .uploading .progressBar .progress").animate({'width': '100%'}, 200); 
				
				setTimeout(function(){
					
					jq("#"+id).find(".uploading").remove(); // 移除进度条
					doneUpload(id); // 完成上传图片
					
					var result = jq.parseJSON(e.target.response); // 解析JSON数据
					if (result.errCode == 0) {
						// 正确处理：在form表单中追加一条input，附带图片信息，或者写入div的attr属性中
						var imgId = result.data.imgId;
						var imgpath = result.data.uploadpath;
						var uploadresult = '<input type="hidden" name="loaded_imgId[]" value="'+imgId+'"><input type="hidden" name="loaded_imgpath[]" value="'+imgpath+'">'; // 要拼接到form中的input
						jq("#"+id).append(uploadresult); // 追加写入到DOM中
					} else {
						// 出错处理
						jq.utilExt.dialog({'content':'网络不稳定，请稍后重新操作', 'autoClose':true}); // 提示信息
						removePic(id); // 移除这张失败的图片
						delete module.exports.uploadInfo[id];			//因为上传并没有成功，所以删除这个上传信息对象
						module.exports.uploadRemain(); // 更新DOM上的upload
						if (module.exports.countUpload() < module.exports.maxUploadNum) {
							// 如果要上传的数量小于最大可上传的数量
							jq("#previewUpload").show();
						}
					}
				}, 500);
				
			},
			// 提醒用户网络出错或断开，移除图片
			fail = function(){
				jq.utilExt.dialog({'content':'网络断开，请稍后重新操作', 'autoClose':true});
				removePic(id); // 失败移除这张上传中的图片
			},
			// 用户取消，提醒，并移除图片
			cancel = function(){
				jq.utilExt.dialog({'content':'上传已取消', 'autoClose':true});
				removePic(id); // 取消移除这张上传中的图片
			},
			// 移除图片
			removePic = function(id){
				doneUpload(id);
				jq("#"+id).remove();
			},
			// 上传完成
			doneUpload = function(id){
				module.exports.isBusy = false; // done就要把模块忙置为不忙
				if (typeof module.exports.uploadInfo[id] != 'undefined') {
                    module.exports.uploadInfo[id].isDone = true; // 上传成功，设置完成
                }
                if (typeof module.exports.xhr[id] != 'undefined') {
                    module.exports.xhr[id] = null; // 上传成功，清空xhr
                }
			};
			
			// createUpload函数的最后执行XMLHttpRequest发送表单
			module.exports.xhr[id] = new XMLHttpRequest(); // 新建一个XMLHttpRequest对象
			
			module.exports.xhr[id].upload.addEventListener("progress", progress, false); 	// 监听上传进度
			module.exports.xhr[id].addEventListener("load", complete, false); 				// 监听上传完成处理
			module.exports.xhr[id].addEventListener("error", fail, false); 					// 监听上传出错
			module.exports.xhr[id].addEventListener("abort", cancel, false); 				// 监听用户上传取消
			
			module.exports.xhr[id].open("POST", uploadurl); 								// POST提交的时候附带当前系统时间
			module.exports.xhr[id].send(formData); 											// 发送表单数据
		},
		/**
		 * 统计页面上还有多少图片可以上传，更新到DOM结构上显示。
		 */
		uploadRemain:function(){
			var remain = module.exports.maxUploadNum - module.exports.countUpload();
			jq(module.exports.DOMUploadRemainTip).html(remain);
		},
		/**
		 * 检测当前系统是否可以上传图片
		 * @returns boolean true|false
		 */
		checkUploadBySysVer:function(){
			// 手机QQ和微信低于5.0以下版本不允许传图
			var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
			if (jQuery.os.android) {
				// 安卓情况下
				if (!MQQBrowser || MQQBrowser && MQQBrowser[1] < '5.2') {
					if (jQuery.os.version.toString().indexOf('4.4') === 0 || jQuery.os.version.toString() <= '2.1') {
						jq.utilExt.dialog({'content':'您的手机系统暂不支持传图', 'autoClose':true});		// MQQ浏览器的低于2.1版本和4.4版本不能上传图片
                        return false;
                    }
				}
			} else if (jQuery.os.ios && jQuery.os.version.toString() < '6.0') {
				// IOS情况下，操作系统版本小于6不允许传图
				jq.utilExt.dialog({'content':'手机系统不支持传图，请升级到ios6.0以上', 'autoClose':true});
				return false;
			}
			if (jQuery.os.wx && jQuery.os.wxVersion.toString() < '5.2') {
				// 微信版本小于5.2不允许上传图片
				jq.utilExt.dialog({'content':'当前微信版本不支持传图，请升级到最新版', 'autoClose':true});
				return false;
			}
			return true; // 默认允许上传，除非上述几种特殊情况
		},
		/**
		 * 检测图片格式是否正确。
		 * @param object file 要检测的文件
		 */
		checkImageFormat:function(file){
			// 对文件的后缀名进行检测，出错提示
			var imageRule = /\.jpg$|\.jpeg$|\.bmp$|\.png$|\.gif$/i; // .jpg|.jpeg|.bmp|.png|.gif后缀不区分大小写
			if (imageRule.test(file.name)) {
				return true; 
			} else {
				return false;
			}
		},
		/**
		 * 检测图片大小是否符合。
		 * @param object file 要检测的文件
		 */
		checkImageSize:function(file){
			// 检测文件的大小，超过允许最大则提示
			if (module.exports.singleUploadMaxSize >= file.size) {
				return true;
			} else {
				return false
			}
		}
	}
	
});