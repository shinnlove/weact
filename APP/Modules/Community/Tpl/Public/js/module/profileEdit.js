/**
 * @filename profileEdit
 * @description
 * @author babuwan
 * @version 1.0
 * Created on 14-8-6.
 */

define('module/profileEdit', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    require('lib/fastclick');
    module.exports = {
        maxUpload : 8,
        // 上传信息 主要是 id 对应信息
        uploadInfo: {},
        // 上传队列，里面保存的是 id
        uploadQueue: [],
        // 预览队列，里面保存的是 id
        previewQueue: [],
        // 请求对象
        xhr: {},
        // 是否有任务正在上传
        isBusy: false,
        //正在压缩
        isCompress: false,
        //正在同步
        isSync: false,
        // 获取地理位置状态
        getgps: 1,
        contentHeight: 0,
        init: function() {

            /**
             * @desc    : 性别选择,A-B，AA=null
             */
            jq('.evtGender a').on('click', function() {
                var genderBtn = jq(this);
                if(genderBtn.hasClass('current')){
                    genderBtn.removeClass('current');
                }else{
                    jq('.evtGender a').removeClass('current');
                    genderBtn.addClass('current');
                }
                return false;
            });

            /**
             * @desc    : 填写资料后，点击保存,异步请求保存数据
             */
            jq('.evtBtnSave').on('click',function(){
                var nickName = jq('.evtNickname').val();
                if(nickName.length==0){
                    jq.DIC.dialog({content: '请输入用户昵称！', autoClose: true});
                    return false;
                }

                /*** ajax start ***/
                //提交数据
                var genderBtn = jq('.evtGender a[class="current"]'),
                    gender = genderBtn.length==0 ? '' : genderBtn.attr('data-gender'),
                    url = '/my/info/edit/submit',
                    data = {nickName:nickName, gender:gender, 'CSRFToken':CSRFToken},
                    opts = {
                        'success': function(result) {
                            if (result.errCode == 0) {
                                jq.DIC.dialog({content: '用户资料修改成功！', autoClose: true});
                            }
                        }
                    };
                jq.DIC.ajax(url, data, opts);
                /*** ajax end ***/
            });

            /**
             * @desc    : 清空昵称输入框内容
             */
            jq('.evtCancel').on('click',function(){
                var nickNameObj = jq('.evtNickname');
                nickNameObj.val('');
            });

            /**
             * @desc    : 点击编辑头像，生成浮层
             * @event   : dialog show
             *
             */
            jq('.evtPhotoBtn,.evtAvatarUrl').on('click',function(){
                var photoHtml = template.render('tmpl_edit_photo',{isWX:isWX}),
                    opts = {
                    'id': 'editPhoto',
                    'isHtml':true,
                    'isMask':true,
                    'content':photoHtml,
                    'callback':function() {
                        /**
                         * @desc    : 生成浮层后的回调方
                         * @event   : 绑定同步QQ按钮事件、绑定上传头像事件、关闭浮事件
                         * DOM OBJ:
                         *  syncBtn  : 同步按钮
                         *  uploadBtn: 上传头像 input file
                         */
                        var closeBtn = jq('.evtCloseLayer'),
                            syncBtn = jq('.evtSyncQQ'),
                            uploadBtn = jq('#uploadFile');

                        /**
                         * @desc    : 点击同步QQ头像按钮事件，发送post请求
                         *
                         */
                        syncBtn.on('click',function(){
                            /**
                             * @desc    : 同步QQ数据
                             * @param   : {String} url 接口地址
                             * @param   : {Obj} data 提交的数据1.syn=1非0同步微信和手Q头像 2.CSRFToken 可post,后台安全限制
                             * @param   : {Obj} opts ajax参数，@link global.ajax,return同前
                             * @see    : dialog
                             */

                            if(module.exports.isBusy){
                                jq.DIC.dialog({content:'上传中，请稍后再试', autoClose:true});
                                return false;
                            }
                            module.exports.isSync = true;
                            /**** ajax start ****/
                            var url = '/my/info/changeAvatar',
                                data = {sync:1, 'CSRFToken':CSRFToken},
                                opts = {
                                    'noMsg': true,
                                    'success': function(result) {
                                        if (result.errCode == 0 && result.jumpURL==null) {
                                            jq.DIC.dialog({content: '同步头像成功！', autoClose: true,callback:function(){
                                                jq('.evtAvatarUrl').attr('src', result.data.avatarUrl);
                                                setTimeout(function(){jq('.evtCloseLayer').trigger('click');},100);
                                                module.exports.isSync = false;
                                            }});

                                        }else if(result.errCode == 0 && result.jumpURL!=null){
                                            jq.DIC.dialog({content: '获取身份信息中！', autoClose: true});
                                        }
                                    }
                                };
                            jq.DIC.ajax(url, data, opts);
                            /**** ajax end ****/
                        });
                        /**
                         * @desc    ：点击上传头像按钮事件,如果上传中 return
                         */
                        uploadBtn.on('click',function(){
                            if(!module.exports.checkUploadBySysVer()){
                                return false;
                            }

                            if(module.exports.isSync){
                                jq.DIC.dialog({content:'同步中，请稍后再试', autoClose:true});
                                return false;
                            }
                            if(module.exports.isBusy){
                                jq.DIC.dialog({content:'上传中，请稍后再试', autoClose:true});
                                return false;
                            }
                            if(module.exports.isCompress){
                                return false;
                            }


                        });

                        /**
                         * @desc    : 选中图片，触发input chang
                         * @param   : {Obj} e 事件对象
                         */
                        uploadBtn.on('change',function(e){
                            module.exports.isCompress = true;
                            e = e || window.event;
                            var fileList = e.target.files;
                            if (!fileList.length) {
                                return false;
                            }

                            /**
                             * @desc    : filelist 放到上传对列里
                             */
                            for (var i = 0; i<fileList.length; i++) {
                                var file = fileList[i];

                                if (!module.exports.checkPicType(file)) {
                                    jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                                    continue;
                                }
                                // console.log(file);
                                if (!module.exports.checkPicSize(file)) {
                                    jq.DIC.dialog({content: '图片体积过大', autoClose:true});
                                    continue;
                                }

                                var id = Date.now() + i;
                                // 增加到上传对象中, 上传完成后，修改为 true
                                module.exports.uploadInfo[id] = {
                                    file: file,
                                    isDone: false
                                };

                                module.exports.previewQueue.push(id);

                            }

                            /**
                             * @desc    ：压缩、上传
                             * @type {number}
                             */
                            module.exports.uploadTimer = setInterval(function() {
                                // 预览
                                setTimeout(function() {
                                    if (module.exports.previewQueue.length) {
                                        jq('.evtLoading').show();
                                        var jobId = module.exports.previewQueue.shift();
                                        module.exports.uploadPreview(jobId);
                                    }
                                }, 1);
                                // 上传
                                setTimeout(function() {
                                    if (!module.exports.isBusy && module.exports.uploadQueue.length) {
                                        //var jobId = module.exports.uploadQueue.shift();
                                        module.exports.isBusy = true;
                                        var jobId = module.exports.uploadQueue[0];
                                        module.exports.createUpload(jobId);

                                    }
                                }, 10);
                            }, 300);


                        });

                        /**
                         * @desc    : 取消上传
                         */
                        closeBtn.on('click',function(){
                            var id = module.exports.uploadQueue.shift();
                            if (module.exports.xhr[id]) {
                                module.exports.xhr[id].abort();
                            }
                        });

                    }
                };
                //显示浮层
                jq.DIC.dialog(opts);

            });

        },
        // 图片预览
        uploadPreview: function(id,callback) {

            var reader = new FileReader();
            var uploadBase64;
            var conf = {}, file = module.exports.uploadInfo[id].file;

            // wifi 下图片高质量
            if (window.NETTYPE == window.NETTYPE_WIFI) {
                conf = {
                    maxW: 3000, //目标宽
                    maxH: 1280, //目标高
                    quality: 0.8 //目标jpg图片输出质量
                };
            }

            reader.onload = function(e) {
                var result = this.result;

                // 如果是jpg格式图片，读取图片拍摄方向,自动旋转
                if (file.type == 'image/jpeg'){
                    // console.log(result);
                    // console.log(file.name);
                    try {
                        var jpg = new JpegMeta.JpegFile(result, file.name);
                    } catch (e) {
                        jq.DIC.dialog({content: '图片不是正确的图片数据',autoClose:true});
                        return false;
                    }
                    if (jpg.tiff && jpg.tiff.Orientation) {
                        //设置旋转
                        conf = jq.extend(conf, {
                            orien: jpg.tiff.Orientation.value
                        });
                    }
                }

                // 压缩
                if (ImageCompresser.support()) {
                    var img = new Image();
                    img.onload = function() {
                        //console.log(conf);
                        try {
                            uploadBase64 = ImageCompresser.getImageBase64(this, conf);
                        } catch (e) {
                            jq.DIC.dialog({content: '压缩图片失败',autoClose:true});
                            return false;
                        }
                        if (uploadBase64.indexOf('data:image') < 0) {
                            jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                            return false;
                        }

                        module.exports.uploadInfo[id].file = uploadBase64;
                        module.exports.uploadQueue.push(id);
                    }
                    img.onerror = function() {
                        jq.DIC.dialog({content: '解析图片数据失败',autoClose:true});
                        return false;
                    }
                    img.src = ImageCompresser.getFileObjectURL(file);
                } else {
                    uploadBase64 = result;
                    if (uploadBase64.indexOf('data:image') < 0) {
                        jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                        jq('#li' + id).remove();
                        return false;
                    }

                    module.exports.uploadInfo[id].file = uploadBase64;
                    jq('#li' + id).find('img').attr('src', uploadBase64);
                    module.exports.uploadQueue.push(id);

                }

            }
            reader.readAsBinaryString(module.exports.uploadInfo[id].file);

        },
        // 创建上传请求
        createUpload: function(id) {
            if (module.exports.isBusy && !module.exports.uploadInfo[id]) {
                return false;
            }

            // 图片posturl
            var uploadUrl = '/my/info/changeAvatar?isAjax=true&resType=json';

            var formData = new FormData();
            formData.append('pic', module.exports.uploadInfo[id].file);
            formData.append('CSRFToken', CSRFToken);

            //上传进度
            var progress = function(e) {
                if (e.target.response) {
                    var result = jq.parseJSON(e.target.response);
                    if (result.errCode != 0) {
                        uploadError();
                        return false;
                    }
                }
            }
            //图片完成后清除对列、清除xhr
            var donePic = function(id) {
                module.exports.isCompress = false;
                module.exports.isBusy = false;

                if (typeof module.exports.uploadInfo[id] != 'undefined') {
                    module.exports.uploadInfo[id].isDone = true;
                }
                if (typeof module.exports.xhr[id] != 'undefined') {
                    module.exports.xhr[id] = null;
                }
            }
            //上传失败
            var uploadError = function(){
                jq.DIC.dialog({content:'网络不稳定，请稍后重新操作',autoClose:true});
                jq('.evtLoading').hide();
                donePic(id);
            }
            //上传完成
            var complete = function(e) {
                donePic(id);

                var result = jq.parseJSON(e.target.response);
                if (result.errCode == 0) {
                    //var input = '<input type="hidden" id="input' + result.data.id + '" name="picIds[]" value="' + result.data.picId + '">';
                   // jq('#newthread').append(input);
                    jq('.evtAvatarUrl').attr('src', module.exports.uploadInfo[id].file);
                    jq.DIC.dialog({content:'上传头像成功',autoClose:true,callback:function(){
                        setTimeout(function(){jq('.evtCloseLayer').trigger('click');},100);
                    }});

                } else {
                    // jq('#content').val(result.errCode);
                    uploadError();
                }

                clearInterval(module.exports.uploadTimer);
                module.exports.uploadQueue = [];
            }

            //上传失败
            var failed = function() {
                uploadError();
            }

            //取消上传
            var abort = function() {
                donePic(id);
                jq.DIC.dialog({content:'上传已取消',autoClose:true});
            }

            // 创建 ajax 请求
            module.exports.xhr[id] = new XMLHttpRequest();
            module.exports.xhr[id].addEventListener("progress", progress, false);
            module.exports.xhr[id].upload.addEventListener("progress", progress, false);
            // xhr.addEventListener("loadstart", loadStart, false);
            module.exports.xhr[id].addEventListener("load", complete, false);
            module.exports.xhr[id].addEventListener("abort", abort, false);
            module.exports.xhr[id].addEventListener("error", failed, false);
            module.exports.xhr[id].open("POST", uploadUrl + '&t=' + Date.now());
            module.exports.xhr[id].send(formData);

        },
        /**
         * @ desc   ：不能上传系统提示
         * @returns {boolean}
         */
        checkUploadBySysVer: function() {
            if (jQuery.os.android) {
                var MQQBrowser = navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);
                if (!MQQBrowser || MQQBrowser && MQQBrowser[1] < '5.2') {
                    if (jQuery.os.version.toString().indexOf('4.4') === 0 || jQuery.os.version.toString() <= '2.1') {
                        jq.DIC.dialog({'content':'您的手机系统暂不支持传图', 'autoClose':true});
                        return false;
                    }
                }
            } else if (jQuery.os.ios && jQuery.os.version.toString() < '6.0') {
                jq.DIC.dialog({'content':'手机系统不支持传图，请升级到ios6.0以上', 'autoClose':true});
                return false;
            }

            if (jQuery.os.wx && jQuery.os.wxVersion.toString() < '5.2') {
                jq.DIC.dialog({'content':'当前微信版本不支持传图，请升级到最新版', 'autoClose':true});
                return false;
            }
            return true;
        },
        /**
         * @desc : 检查图片大小
         * @param file
         * @returns {boolean}
         */
        checkPicSize: function(file) {
            // 8M
            if (file.size > 10000000) {
                return false;
            }

            return true;
        },
        /**
         * @desc 检查图片类型,png、bmp、jpg、jpeg、gif
         * @param file
         * @returns {boolean}
         */
        checkPicType: function(file) {
            var photoReg = (/\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$/i);
            if(!photoReg.test(file.name)){
                return false;
            }else{
                return true;
            }

        }
    };
    module.exports.init();
});
