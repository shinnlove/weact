/**
 * main html DOM page to call image upload in web.
 * @author shinnlove
 * @create 2016/02/08 01:23:25.
 */
define('module/uploadPage', ['module/uploadImg'], function(require, exports, module){
	var uploadImg = require('module/uploadImg'); // 引入依赖
	
	module.exports = {
		init: function(){
			
			// 生成DOM结构事件
			jq(".upload-wrap").on("mouseover", "#previewUpload", function(e){
				jq(this).children(".webuploader-pick").addClass("webuploader-pick-hover");
			}).on("mouseleave", "#previewUpload", function(e){
				jq(this).children(".webuploader-pick").removeClass("webuploader-pick-hover");
			}).on("change", ".webuploader-file", function(e){
				// 检查当前手机系统是否支持传图片
				if (!uploadImg.checkUploadBySysVer()) {
					return false;
				}
				//console.log("监听模块是否忙，1为忙，0为不忙： "+uploadImg.isBusy);
				// 设置定时检测上传
				var checkUploadTimer = setInterval(function(){
					// 先预览
					setTimeout(function(){
						if (uploadImg.previewQueue.length) {
							var id = uploadImg.previewQueue.shift(); // 取出一个要预览的图片id
							uploadImg.previewUpload(id);
						}
					}, 1);
					// 如果有上传，再上传；★★★应该是如果模块不忙，并且上传队列中有图片，再上传！！！★★★
					setTimeout(function(){
						if (!uploadImg.isBusy && uploadImg.uploadQueue.length) {
							var id = uploadImg.uploadQueue.shift(); // 取出一个要上传的图片
							uploadImg.isBusy = true; // 特别重要：要上传要设置模块正忙
							uploadImg.createUpload(id, "module/uploadPage", checkUploadTimer);
						}
					}, 10);
				}, 300);
				
				// 捕获事件，提取文件
				e = e || window.event;
				var files = e.target.files; // 获取所有变动文件列表
				
				// 检测文件是否存在
				if (files.length < 0) {
					jq.utilExt.dialog({'content':"请上传图片", autoClose:true}); 
					return false;
				}
				// 文件存在则循环检测每个文件是否符合条件
				for(var i = 0; i < files.length; i++) {
					
					var fileinfo = files[i]; // 取出当前文件
					
					// 检测当前上传图片是否超过数量限制（用uploadImg的uploadInfo里内容和maxUploadNum比较，不要用DOM元素长度比较，不准）
					if (uploadImg.countUpload() >= uploadImg.maxUploadNum) {
						jq.utilExt.dialog({'content':"亲，最多支持上传" + uploadImg.maxUploadNum + "张图片", autoClose:true}); 
						return false;
					}
					// 检查文件格式（注意正则不要写错）
					if (!uploadImg.checkImageFormat(fileinfo)) {
						jq.utilExt.dialog({'content':"不支持的图片类型", autoClose:true}); 
						return false;
					}
					// 检查文件大小
					if (!uploadImg.checkImageSize(fileinfo)) {
						jq.utilExt.dialog({'content':"上传图片过大，请压缩后上传", autoClose:true}); 
						return false;
					}
					
					// 允许上传为文件生成id
					var id = Date.now() + i;
					
					// 加入uploadImg的uploadInfo队列中
					uploadImg.uploadInfo[id] = {
							file:fileinfo,
							isDone:false
					}
					
					// 生成DOM结构，并产生进度条，追加到DOM中，绑定删除事件
					var tmpl = '<div id="'+id+'" data-imgid="" data-imgpath="" class="webuploader-container loaded"><span class="cancel">×</span><div class="webuploader-pick uploading"><div class="progressBar"><div class="progress" style="width: 0%;"></div></div></div></div>';
					jq("#previewUpload").before(tmpl); // 追加到DOM上（在上传+之前追加新预览框）
					jq("#" + id + " .cancel").on("click", function(){
						var rmid = jq(this).parent().attr("id");
						// 先取消这个请求（如果还在请求的话，完成请求xhr[id]会被定义成undefined）
						if (uploadImg.xhr[rmid]) {
							uploadImg.xhr[rmid].abort();
						}
						jq("#" + rmid).remove(); // 再移除DOM结构
						uploadImg.uploadInfo[id] = null; // ★★★很重要的一句话，否则页面上无法控制点击上传+号
						uploadImg.uploadRemain(); // 更新上传数量
						if (uploadImg.countUpload() < uploadImg.maxUploadNum) {
							jq("#previewUpload").show(); // 控制点击上传+号的显示
						}
					});
					
					uploadImg.previewQueue.push(id); // 加入预览队列
					
					// 如果上传数量超过最大数量，则移除点击上传+
					if (uploadImg.countUpload() >= uploadImg.maxUploadNum) {
						jq("#previewUpload").hide();
					}
					
					// 在下一次循环之前更新剩余上传数（DOM显示 ）
					setTimeout(function(){
						uploadImg.uploadRemain(); // 延时一点做，因为可能压缩图片失败会被移除
					}, 400);
					
				}
				jq(this).val(''); // 重要：清空就算是再选择一样的图片也会触发change
			});
		}
	}
	
	module.exports.init();
	
});