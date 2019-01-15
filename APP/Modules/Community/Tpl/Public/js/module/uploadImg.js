/**
 * @filename uploadImg
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2014-8-6 14:56:03
 * 修改记录:
 *
 * $Id: uploadImg.js 31364 2014-07-29 09:41:54Z jinhuiguo $
 **/

define('module/uploadImg', ['module/jpegMeta', 'module/imageCompresser'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var JpegMeta = require('module/jpegMeta').JpegMeta;					// 针对jpeg图片检测文件格式正确性、并对其进行拍摄方向旋转
    var ImageCompresser = require('module/imageCompresser');			// base64压缩图片的模块
    module.exports = {
        maxUpload : 8,		// 当前页面最大可上传图片的张数
        
        uploadInfo: {},		// 要上传的图片信息，一旦上传失败就移除对应id的file信息；上传成功就追加字段isDone=true
        
        uploadQueue: [],	// 上传队列，里面保存的是DOM结构id，给外部访问的id字符串数组
        
        previewQueue: [],	// 预览队列，里面保存的是 id
        
        xhr: {},			// 请求对象 用id
        
        isBusy: false,		// 是否有图片正在上传
        //计算已经上传图片数目，因为上传完成uploadInfo中保留着图片信息
        countUpload: function() {
        	
            var num = 0;
            jq.each(module.exports.uploadInfo, function(i, e) {
                if (e) {
                    ++ num;
                }
            });
            return num;
            
        },
        /**
         * 图片预览函数。
         * @param String id 要预览的图片文件所在已上传队列中的索引
         */
        uploadPreview: function(id) {
        	
        	// 很有用，支持预览图片的FileReader对象必须被定义，13年4月只有FF3.6+和Chrome6.0+实现了FileReader接口
        	if (typeof FileReader == "undefined") {
        		jq.DIC.dialog({content: '当前浏览器版本不支持图片预览',autoClose:true});
        		jq('#li' + id).remove();
        		return false;
        	}
        	
            var reader = new FileReader(); // 创建一个filereader对象
            var uploadBase64; // 图片用base64方式读取
            var conf = {}, file = module.exports.uploadInfo[id].file; // 要显示的配置与图片文件

            // wifi下预览图片高质量
            if (window.NETTYPE == window.NETTYPE_WIFI) {
                conf = {
                    maxW: 3000, 	// 目标宽
                    maxH: 1280, 	// 目标高
                    quality: 0.8, 	// 目标jpg图片输出质量
                };
            }
            
            /**
             * FileReader对象介绍：FileReader接口提供了一个异步API，使用该API可以在浏览器主线程中异步访问文件系统，读取文件中的数据。
             * filereader对象有onload事件，形参为e。
             * 当调用filereader的readAsBinaryString方法读入文件的二进制流时，filereader会将结果放在内部的result对象中。
             * FileReader接口方法：
             * readAsBinaryString file 将文件读取为二进制编码
             * readAsText file,[encoding] 将文件读取为文本
             * readAsDataURL file 将文件读取为DataURL
             * abort (none) 终端读取操作
             * FileReader接口事件：
             * onabort 中断
             * onerror 出错
             * onloadstart 开始
             * onprogress 正在读取
             * onload成功读取
             * onloadend 读取完毕，不知成功与否
             */
            reader.onload = function(e) {
                var result = this.result; // 当filereader读取图片文件，把二进制文件流放在result中
                
                // 如果后缀名为image/jpeg，检测图片格式并读取图片拍摄方向，自动旋转
                if (file.type == 'image/jpeg'){
                    try {
                        var jpg = new JpegMeta.JpegFile(result, file.name); // 检测是否jpeg格式图片
                    } catch (e) {
                        jq.DIC.dialog({content: '图片不是正确的图片数据',autoClose:true});
                        jq('#li' + id).remove();
                        return false;
                    }
                    if (jpg.tiff && jpg.tiff.Orientation) {
                        //设置旋转
                        conf = jq.extend(conf, {
                            orien: jpg.tiff.Orientation.value
                        });
                    }
                }
                
                // 判断本地是否支持压缩，处理方式不同
                if (ImageCompresser.ImageCompresser.support()) {
                	// 如果支持本地压缩
                    var img = new Image();
                    img.onload = function() {
                        try {
                        	// 在image的load方法中，this指代的就是image本身
                            uploadBase64 = ImageCompresser.ImageCompresser.getImageBase64(this, conf); // 核心：调用ImageCompresser对象中的成员变量ImageCompresser的getImageBase64方法压缩图片
                        } catch (e) {
                            jq.DIC.dialog({content: '压缩图片失败',autoClose:true});
                            jq('#li' + id).remove();
                            return false;
                        }
                        if (uploadBase64.indexOf('data:image') < 0) {
                            jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                            jq('#li' + id).remove();
                            return false;
                        }
                        // 支持压缩下、成功压缩后
                        module.exports.uploadInfo[id].file = uploadBase64; // 转成附带二进制流的图片信息
                        jq('#li' + id).find('img').attr('src', uploadBase64); // 在DOM上显示这张压缩后的图片
                        module.exports.uploadQueue.push(id); // 把图片文件同时加入上传队列
                    }
                    img.onerror = function() {
                        jq.DIC.dialog({content: '解析图片数据失败',autoClose:true});
                        jq('#li' + id).remove();
                        return false;
                    }
                    img.src = ImageCompresser.ImageCompresser.getFileObjectURL(file); // 追加文件所在位置
                } else {
                	// 如果不支持本地压缩
                    uploadBase64 = result; // 如果filereader的结果不是data:image开头
                    if (uploadBase64.indexOf('data:image') < 0) {
                        jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                        jq('#li' + id).remove();
                        return false;
                    }
                    // 如果读入图片文件格式正确
                    module.exports.uploadInfo[id].file = uploadBase64;
                    jq('#li' + id).find('img').attr('src', uploadBase64);
                    module.exports.uploadQueue.push(id);
                }
            }

            reader.readAsBinaryString(module.exports.uploadInfo[id].file); // reader方法用二进制字符串方式读取文件，触发onload事件
        },
        // 创建上传请求
        createUpload: function(id, type, uploadTimer) {
        	// 如果要上传的图片文件信息不存在则直接返回（所以uploadInfo[id]={图片文件信息}是在外部模块调用本模块时传入的，见newthread.js中152行）
            if (!module.exports.uploadInfo[id]) {
                return false;
            }

            // 图片posturl
            //var uploadUrl = DOMAIN + sId + '/pic/upload?isAjax=true&resType=json';
            var uploadUrl = IMAGEUPLOADURL+'?isAjax=true&resType=json';					//每一次图片的post提交地址
            // 产生进度条
            var progressHtml = '<div class="progress brSmall pr" id="progress'+id+'"><div class="proBar" style="width:0%;"></div></div>';	//创建进度条
            jq('#li' + id).find('.maskLay').after(progressHtml);						//创建上传遮罩层

            var formData = new FormData();												//创建表单数据
            formData.append('pic', module.exports.uploadInfo[id].file);					//图片文件数据附加到name为pic中
            formData.append('CSRFToken', CSRFToken);									//附加数据CSRFToken
            formData.append('sId', sId);												//附加数据sId
            formData.append('id', id);													//附加数据id
            //定义进度条这个对象事件progress，注意这是个事件
            var progress = function(e) {
                if (e.target.response) {
                    var result = jq.parseJSON(e.target.response);
                    if (result.errCode != 0) {
                        // jq('#content').val(result.errCode);
                        jq.DIC.dialog({content:'网络不稳定，请稍后重新操作',autoClose:true});
                        removePic(id);
                        //更新剩余上传数
                        module.exports.uploadRemaining();
                        return false;
                    }
                }
                //在progress事件内部定义个变量progress（页面的进度条）
                var progress = jq('#progress' + id).find('.proBar');
                if (e.total == e.loaded) {
                    var percent = 100;
                } else {
                    var percent = 100*(e.loaded / e.total);
                }
                // 控制进度条不要超出
                if (percent > 100) {
                    percent = 100;
                }
                //progress.css('width', percent + '%');		//直接让进度条到100%
                progress.animate({'width': '95%'}, 1500);	//在1.5秒内让进度条到95%
                //0.4秒内执行
                setTimeout(function(){
                    if (percent == 100) {
                        /*jq('#li' + id).find('.maskLay').remove();
                        jq('#li' + id).find('.progress').remove();*/
                        donePic(id);						//完成上传图片
                        if(uploadTimer){
                            clearInterval(uploadTimer);		// 特别注意：如果这张图片的上传请求被正确响应了，取消因为要上传这张图片而存在的定时检测请求器，配合newthread.js中114行
                        }
                    } 
                }, 400);
                
            }
            //将图片移除
            var removePic = function(id) {
                donePic(id);								//上传完成
                jq('#li' + id).remove();					//抓取li标签，从DOM元素中移除
            }
            //线程上传图片完成函数donePic
            var donePic = function(id) {
                module.exports.isBusy = false;				//整个模块的忙碌标记置为false（如果为true默认表单不能提交）

                if (typeof module.exports.uploadInfo[id] != 'undefined') {
                    module.exports.uploadInfo[id].isDone = true;
                }
                if (typeof module.exports.xhr[id] != 'undefined') {
                    module.exports.xhr[id] = null;
                }
            }
            //上传完成时的函数
            var complete = function(e) {
                var progress = jq('#progress' + id).find('.proBar');	//抓取当前上传图片的进度条对象
                progress.css('width', '100%');							//将其进度加到100%
                jq('#li' + id).find('.maskLay').remove();				//去除遮罩层
                jq('#li' + id).find('.progress').remove();				//去除上传进度条
                // 上传结束
                donePic(id);

                var result = jq.parseJSON(e.target.response);			//解析服务器返回的json数据为result
                if (result.errCode == 0) {
                    var input = '<input type="hidden" id="input' + result.data.id + '" name="picIds[]" value="' + result.data.picId + '">';		//在form中动态拼接一条input
                    if(type == 'replyForm'){
                        jq('#replyForm').append(input); 				//如果是回复则追加在replyform下
                    }else{
                        jq('#newthread').append(input); 				//如果是新评论，则追加在新form下
                    }
                } else {
                    // jq('#content').val(result.errCode);				//将错误信息写入content中
                    jq.DIC.dialog({content:'网络不稳定，请稍后重新操作',autoClose:true});	//使用自定义DIC透明框提示网络不稳定，上传失败，并自动关闭
                    removePic(id);										//将图片本次上传的图片移除
                    //更新剩余上传数
                    module.exports.uploadRemaining();					//调用uploadRemaining更新剩余可上传图片的数目
                    delete module.exports.uploadInfo[id];				//因为上传并没有成功，所以删除这个上传信息对象
                    // 如果传略失败，上传个数少于8张则再显示加号
                    if (module.exports.countUpload() < module.exports.maxUpload) {
                        var iconSendImg = jq('.iconSendImg');
                        jq('#addPic').show();							//可上传图片的id出现
                        if(iconSendImg.hasClass('fail')){
                            iconSendImg.removeClass('fail');			//如果有上传失败图片造成错误的class，移除上传失败的class
                        }
                    }
                }
            }
            //上传失败函数
            var failed = function() {
                jq.DIC.dialog({content:'网络断开，请稍后重新操作',autoClose:true});		//调用自定义DIC框提示网络断开，并且自动关闭
                removePic(id)
            }
            //上传终止函数
            var abort = function() {
                jq.DIC.dialog({content:'上传已取消',autoClose:true});
                removePic(id)
            }

            // 创建 ajax 请求
            module.exports.xhr[id] = new XMLHttpRequest();
            // module.exports.xhr[id].addEventListener("progress", progress, false); 		// xhr自身的progress事件针对下载；xhr.upload的prgress针对上传！！！这里下载暂时用不到
            module.exports.xhr[id].upload.addEventListener("progress", progress, false);	//监听发送进度，使用模块内progress事件响应：138行事件
            // xhr.addEventListener("loadstart", loadStart, false);							//如果需要，可以添加loadStart函数增加一些页面特效
            module.exports.xhr[id].addEventListener("load", complete, false);				//监听XMLHttpRequest的load事件，使用模块内complete函数响应：193行
            module.exports.xhr[id].addEventListener("abort", abort, false);					//监听XMLHttpRequest的abort事件，使用模块内abort函数响应：232行
            module.exports.xhr[id].addEventListener("error", failed, false);				//监听XMLHttpRequest的error事件，使用模块内failed函数响应：227行
            module.exports.xhr[id].open("POST", uploadUrl + '&t=' + Date.now());			//打开静态POST提交，并加上时间作为参数，又如：xhr[id].open("GET",url,true);打开一个GET请求
            
            module.exports.xhr[id].send(formData);											//使用ajax请求 发送表单
            
        },
        // 不能上传系统提示
        checkUploadBySysVer: function() {
            if (jQuery.os.android) {
                var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
                if (!MQQBrowser || MQQBrowser && MQQBrowser[1] < '5.2') {
                    if (jQuery.os.version.toString().indexOf('4.4') === 0 || jQuery.os.version.toString() <= '2.1') {
                        jq.DIC.dialog({'content':'您的手机系统暂不支持传图', 'autoClose':true});		//MQQ浏览器的低于2.1版本和4.4版本不能上传图片
                        return false;
                    }
                }
            } else if (jQuery.os.ios && jQuery.os.version.toString() < '6.0') {
                jq.DIC.dialog({'content':'手机系统不支持传图，请升级到ios6.0以上', 'autoClose':true});		//IOS系统版本太低
                return false;
            }
            
            if (jQuery.os.wx && jQuery.os.wxVersion.toString() < '5.2') {
                jq.DIC.dialog({'content':'当前微信版本不支持传图，请升级到最新版', 'autoClose':true});		//微信版本过低
                return false;
            }
            return true;
        },
        //剩余上传数，此函数被调用能更新剩余可上传图片的数目
        uploadRemaining: function(){
            var uploadNum = 0;
            uploadNum = jq('.photoList').find('li').length;		//找到class为photoList中li标签并统计个数
            /*if(!jq('#addPic').is(':hidden')){
                uploadNum--
            }*/
            //特别注意：#addPic也是一个li，所以这里要统计最大8张，实际li的length是9
            var canOnlyUploadNum = 8; 							//定义还能上传的数目
            switch(uploadNum){
                case 1:
                  canOnlyUploadNum = 8;							//只有addPic，还可上传8张
                  break;
                case 2:
                  canOnlyUploadNum = 7;
                  break;
                case 3:
                  canOnlyUploadNum = 6;
                  break;
                case 4:
                  canOnlyUploadNum = 5;
                  break;
                case 5:
                  canOnlyUploadNum = 4;
                  break;
                case 6:
                  canOnlyUploadNum = 3;
                  break;
                case 7:
                  canOnlyUploadNum = 2;
                  break;
                case 8:
                  canOnlyUploadNum = 1;
                  break;
                case 9:
                  canOnlyUploadNum = 0;
                  break;
                default:
                  canOnlyUploadNum = 8;
                  break;
            }
            //更新剩余可上传图片数
            jq('#onlyUploadNum').html(canOnlyUploadNum);		//还能上传div提示能上传的图片张数
        },
        // 检查图片大小
        checkPicSize: function(file) {
            // 最大允许8M
            if (file.size > 10000000) {
                return false;
            }
            return true;
        },
        // 检查图片类型
        checkPicType: function(file) {
            var photoReg = (/\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$/i);		//定义图片格式正则
            if(!photoReg.test(file.name)){
               return false; 
            }else{
                return true;
            }
            
        }
    };
});
