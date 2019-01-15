/**
 * 上传模块主程序
 * @author shinnlove
 */
define('module/uploadPage', ['module/uploadImg','module/MLoading'], function(require, exports, module) {
	"require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
	var uploadImg = require('module/uploadImg');
	var MLoading = require('module/MLoading');
	module.exports = {
		init: function() {
			jq(".upload-wrap").on("mouseover", "#previewUpload", function(e) {
				jq(this).children(".webuploader-pick").addClass("webuploader-pick-hover")
			}).on("mouseleave", "#previewUpload", function(e) {
				jq(this).children(".webuploader-pick").removeClass("webuploader-pick-hover")
			}).on("change", ".webuploader-file", function(e) {
				if (!uploadImg.checkUploadBySysVer()) {
					return false
				}
				var checkUploadTimer = setInterval(function() {
					setTimeout(function() {
						if (uploadImg.previewQueue.length) {
							var id = uploadImg.previewQueue.shift();
							uploadImg.previewUpload(id)
						}
					}, 1);
					setTimeout(function() {
						if (!uploadImg.isBusy && uploadImg.uploadQueue.length) {
							var id = uploadImg.uploadQueue.shift();
							uploadImg.isBusy = true;
							uploadImg.createUpload(id, "module/uploadPage", checkUploadTimer)
						}
					}, 10)
				}, 300);
				e = e || window.event;
				var files = e.target.files;
				if (files.length < 0) {
					jq.utilExt.dialog({
						'content': "请上传图片",
						autoClose: true
					});
					return false
				}
				for (var i = 0; i < files.length; i++) {
					var fileinfo = files[i];
					if (uploadImg.countUpload() >= uploadImg.maxUploadNum) {
						jq.utilExt.dialog({
							'content': "亲，最多支持上传" + uploadImg.maxUploadNum + "张图片",
							autoClose: true
						});
						return false
					}
					if (!uploadImg.checkImageFormat(fileinfo)) {
						jq.utilExt.dialog({
							'content': "不支持的图片类型",
							autoClose: true
						});
						return false
					}
					if (!uploadImg.checkImageSize(fileinfo)) {
						jq.utilExt.dialog({
							'content': "上传图片过大，请压缩后上传",
							autoClose: true
						});
						return false
					}
					var id = Date.now() + i;
					uploadImg.uploadInfo[id] = {
						file: fileinfo,
						isDone: false
					}
					var tmpl = '<div id="' + id + '" data-imgid="" data-imgpath="" class="webuploader-container loaded"><span class="cancel">×</span><div class="webuploader-pick uploading"><div class="progressBar"><div class="progress" style="width: 0%;"></div></div></div></div>';
					jq("#previewUpload").before(tmpl);
					jq("#" + id + " .cancel").on("click", function() {
						var rmid = jq(this).parent().attr("id");
						if (uploadImg.xhr[rmid]) {
							uploadImg.xhr[rmid].abort()
						}
						jq("#" + rmid).remove();
						uploadImg.uploadInfo[id] = null;
						uploadImg.uploadRemain();
						if (uploadImg.countUpload() < uploadImg.maxUploadNum) {
							jq("#previewUpload").show()
						}
					});
					uploadImg.previewQueue.push(id);
					if (uploadImg.countUpload() >= uploadImg.maxUploadNum) {
						jq("#previewUpload").hide()
					}
					setTimeout(function() {
						uploadImg.uploadRemain()
					}, 400)
				}
				jq(this).val('')
			}).on("click", "input[name='showLoading']", function(){
				MLoading.show("数据加载中...", true);
				setTimeout(function(){
					MLoading.hide();
				}, 3000);
			});
		}
	}
	module.exports.init()
});