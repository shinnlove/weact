/**
 * 上传模块
 * @author shinnlove
 */
define('module/uploadImg', ['module/jpegMeta', 'module/imageCompresser'], function(require, exports, module) {
	"require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
	var jpegMeta = require('module/jpegMeta');
	var imageCompresser = require('module/imageCompresser');
	module.exports = {
		uploadURL: "/weact/Shinnlove/ImageUpload/uploadHandle",
		singleUploadMaxSize: 1024 * 1024 * 8.5,
		maxUploadNum: 9,
		DOMUploadRemainTip: ".upload-remain",
		uploadQueue: [],
		previewQueue: [],
		uploadInfo: {},
		xhr: {},
		isBusy: false,
		countUpload: function() {
			var num = 0;
			jq.each(module.exports.uploadInfo, function(i, e) {
				if (e) {
					++num
				}
			});
			return num
		},
		previewUpload: function(id) {
			if (typeof FileReader == "undefined") {
				jq.utilExt.dialog({
					content: '当前环境不支持预览',
					autoClose: true
				});
				jq("#" + id).remove();
				return false
			}
			var reader = new FileReader();
			var uploadBase64;
			var conf = {},
				imgfile = module.exports.uploadInfo[id].file;
			if (window.NETTYPE == window.NETTYPE_WIFI) {
				conf = {
					maxW: 3000,
					maxH: 1280,
					quality: 0.8,
				}
			}
			reader.onload = function(e) {
				var result = this.result;
				if (imgfile.type == 'image/jpeg') {
					try {
						var jpg = new jpegMeta.JpegMeta.JpegFile(result, imgfile.name)
					} catch (e) {
						jq.utilExt.dialog({
							content: '图片不是正确的图片数据',
							autoClose: true
						});
						jq('#' + id).remove();
						return false
					}
					if (jpg.tiff && jpg.tiff.Orientation) {
						conf = jq.extend(conf, {
							orien: jpg.tiff.Orientation.value
						})
					}
				}
				if (imageCompresser.ImageCompresser.support()) {
					var img = new Image();
					img.onload = function() {
						try {
							uploadBase64 = imageCompresser.ImageCompresser.getImageBase64(this, conf)
						} catch (e) {
							jq.utilExt.dialog({
								content: '压缩图片失败',
								autoClose: true
							});
							jq('#' + id).remove();
							return false
						}
						if (uploadBase64.indexOf('data:image') < 0) {
							jq.utilExt.dialog({
								content: '上传照片格式不支持',
								autoClose: true
							});
							jq('#' + id).remove();
							return false
						}
						module.exports.uploadInfo[id].file = uploadBase64;
						jq('#' + id).css('background-image', 'url(' + uploadBase64 + ')');
						module.exports.uploadQueue.push(id)
					}
					img.onerror = function() {
						jq.utilExt.dialog({
							content: '解析图片数据失败',
							autoClose: true
						});
						jq('#' + id).remove();
						return false
					}
					img.src = imageCompresser.ImageCompresser.getFileObjectURL(imgfile)
				} else {
					uploadBase64 = result;
					if (uploadBase64.indexOf('data:image') < 0) {
						jq.utilExt.dialog({
							content: '上传照片格式不支持',
							autoClose: true
						});
						jq('#' + id).remove();
						return false
					}
					module.exports.uploadInfo[id].file = uploadBase64;
					jq('#' + id).css('background-image', 'url(' + uploadBase64 + ')');
					module.exports.uploadQueue.push(id)
				}
			}
			reader.readAsBinaryString(module.exports.uploadInfo[id].file)
		},
		createUpload: function(id, callmodule, checkUploadTimer) {
			if (!module.exports.uploadInfo[id]) {
				return false
			}
			var uploadurl = module.exports.uploadURL + "?t=" + Date.now();
			var imgfile = module.exports.uploadInfo[id].file;
			var formData = new FormData();
			formData.append("img-upload", imgfile);
			var progress = function(e) {
					if (e.LengthComputable) {
						var percent = Math.round(e.loaded / e.total * 100);
						if (percent > 100) {
							percent = 100
						}
					}
					jq("#" + id + " .uploading .progressBar .progress").animate({
						'width': '95%'
					}, 300);
					setTimeout(function() {
						if (percent == 100) {
							doneUpload(id);
							if (checkUploadTimer) {
								clearInterval(checkUploadTimer)
							}
						}
					}, 400)
				},
				complete = function(e) {
					jq("#" + id + " .uploading .progressBar .progress").animate({
						'width': '100%'
					}, 200);
					setTimeout(function() {
						jq("#" + id).find(".uploading").remove();
						doneUpload(id);
						var result = jq.parseJSON(e.target.response);
						if (result.errCode == 0) {
							var imgId = result.data.imgId;
							var imgpath = result.data.uploadpath;
							var uploadresult = '<input type="hidden" name="loaded_imgId[]" value="' + imgId + '"><input type="hidden" name="loaded_imgpath[]" value="' + imgpath + '">';
							jq("#" + id).append(uploadresult)
						} else {
							jq.utilExt.dialog({
								'content': '网络不稳定，请稍后重新操作',
								'autoClose': true
							});
							removePic(id);
							delete module.exports.uploadInfo[id];
							module.exports.uploadRemain();
							if (module.exports.countUpload() < module.exports.maxUploadNum) {
								jq("#previewUpload").show()
							}
						}
					}, 500)
				},
				fail = function() {
					jq.utilExt.dialog({
						'content': '网络断开，请稍后重新操作',
						'autoClose': true
					});
					removePic(id)
				},
				cancel = function() {
					jq.utilExt.dialog({
						'content': '上传已取消',
						'autoClose': true
					});
					removePic(id)
				},
				removePic = function(id) {
					doneUpload(id);
					jq("#" + id).remove()
				},
				doneUpload = function(id) {
					module.exports.isBusy = false;
					if (typeof module.exports.uploadInfo[id] != 'undefined') {
						module.exports.uploadInfo[id].isDone = true
					}
					if (typeof module.exports.xhr[id] != 'undefined') {
						module.exports.xhr[id] = null
					}
				};
			module.exports.xhr[id] = new XMLHttpRequest();
			module.exports.xhr[id].upload.addEventListener("progress", progress, false);
			module.exports.xhr[id].addEventListener("load", complete, false);
			module.exports.xhr[id].addEventListener("error", fail, false);
			module.exports.xhr[id].addEventListener("abort", cancel, false);
			module.exports.xhr[id].open("POST", uploadurl);
			module.exports.xhr[id].send(formData)
		},
		uploadRemain: function() {
			var remain = module.exports.maxUploadNum - module.exports.countUpload();
			jq(module.exports.DOMUploadRemainTip).html(remain)
		},
		checkUploadBySysVer: function() {
			var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
			if (jQuery.os.android) {
				if (!MQQBrowser || MQQBrowser && MQQBrowser[1] < '5.2') {
					if (jQuery.os.version.toString().indexOf('4.4') === 0 || jQuery.os.version.toString() <= '2.1') {
						jq.utilExt.dialog({
							'content': '您的手机系统暂不支持传图',
							'autoClose': true
						});
						return false
					}
				}
			} else if (jQuery.os.ios && jQuery.os.version.toString() < '6.0') {
				jq.utilExt.dialog({
					'content': '手机系统不支持传图，请升级到ios6.0以上',
					'autoClose': true
				});
				return false
			}
			if (jQuery.os.wx && jQuery.os.wxVersion.toString() < '5.2') {
				jq.utilExt.dialog({
					'content': '当前微信版本不支持传图，请升级到最新版',
					'autoClose': true
				});
				return false
			}
			return true
		},
		checkImageFormat: function(file) {
			var imageRule = /\.jpg$|\.jpeg$|\.bmp$|\.png$|\.gif$/i;
			if (imageRule.test(file.name)) {
				return true
			} else {
				return false
			}
		},
		checkImageSize: function(file) {
			if (module.exports.singleUploadMaxSize >= file.size) {
				return true
			} else {
				return false
			}
		}
	}
});