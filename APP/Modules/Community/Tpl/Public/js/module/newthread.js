/**
 * @filename newthread
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: newthread.js 32319 2014-08-26 02:16:44Z jinhuiguo $
 **/

define('module/newthread', ['module/emotion', 'module/gps', 'module/uploadImg'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var gps = require('module/gps');						//加载模块定位传感器js
    var emotion = require('module/emotion');				//加载模块emotion表情js（手机触屏版）
    var uploadImg = require('module/uploadImg');			//加载模块线程上传图片js
    module.exports = {
        // 获取地理位置状态
        getgps: 1,
        contentHeight: 0,
        uploadTimer: null,
        // 上传相关
        initUpload: function() {
        	/**
        	 * 绑定最早的DOM结构事件：上传+号点击时检查手机系统版本是否允许上传图片。
        	 */
            jq('#addPic, .uploadPicBox').on('click', function() {
                if(!uploadImg.checkUploadBySysVer()){
                    return false;
                }
            });
            /**
             * 绑定其次的DOM结构事件：点击小圆圈或图片区域+号，打开上传图片对话框前的事件判断。
             * 1、检测是否还附带微视；
             * 2、检测模块是否正在上传图片；
             * 3、检测上传图片数量是否超过总数量。
             */
            jq('#uploadFile, #fistUploadFile').on('click', function() {
                var thisObj = jq(this);
                // 选择过视频
                if(jq('.photoList').find('#livideo').length > 0){
                    jq.DIC.dialog({id: 'addWsTips', content: '图片和微视只能发一种哦~', autoClose: 2000});
                    return false;                    
                }
                // 上传模块忙
                if (uploadImg.isBusy) {
                    jq.DIC.dialog({content:'上传中，请稍后添加', autoClose:true});
                    return false;
                }
                // 有上传失败
                if(thisObj.attr('id') == 'fistUploadFile'){
                    if(jq('.iconSendImg').hasClass('fail')){
                        jq.DIC.dialog({content:'不能再上传了，最多只能上传8张图片哦~', autoClose:true});
                        return false;
                    }
                }
            });

            jq('body').on('click', '.iconSendImg, .iconArrowR', function(e){
                var thisObj = jq(this);
                var photoList = jq('.photoList');
                //点击图片图标
                if(thisObj.hasClass('iconSendImg')){
                    if(photoList.is(':hidden')){
                        //jq('.sendCon').animate({height: '60'}, 300);
                        jq('.sendCon').css('height', '60');
                        photoList.show();
                    }
                }
                //查看更多表情
                if(thisObj.hasClass('iconArrowR')){
                    var expressionMenu = jq('.expressionMenu').find('a');
                    var haveMenuWidth = expressionMenu.length*76;
                    var expressionTabWidth = jq('.expressionTab').width();
                    if(haveMenuWidth > expressionTabWidth){
                        var firstChild = jq(expressionMenu[0]);
                        jq('.expressionMenu').append(firstChild.clone());
                        firstChild.remove();
                    }else{
                        jq.DIC.dialog({id:'haveMoreExpression', content:'没有更多表情了哦~',autoClose:true});
                    }
                }

            });
            //首次点击图片的图标，触发一次手机的默认上传事件
            jq('body').on('change', '#fistUploadFile', function(e){
                var content = jq('#content')[0];
                jq('.photoList').show();
                jq('.operatList').hide();
                jq('.photoTipsBox').show();
                jq('.operatIcon').removeClass('on');
                jq('.iconSendImg').addClass('on');
                //jq('.sendCon').css('height', 'auto');
                if(jq('.sendCon').height() != 60){
                    //jq('.sendCon').animate({height: '60'}, 300);
                    jq('.sendCon').css('height', '60');

                }
                //传图时输入框定位到底部
                content.scrollTop = content.scrollHeight
            });

            // 文件表单发生变化时
            jq('body').on('change', '#uploadFile, #fistUploadFile', function(e) {
                // 执行图片预览、上传的定时器
            	// 如果模块上传正忙，是无法进入if调用createUpload的，所以定时器0.3秒检测一次模块是否忙
                uploadTimer = setInterval(function() {
                	// 如果先提交了一个大文件图片，还在上传中，还可以选择其他的文件，此时加入上传队列中，需要定时器定时上传
                	// 正常情况下上传完就预览了，但是上传中后边卡了2张小图。所以总是先预览，再上传，保证上一张图片如果被传完了，一定会预览
                    // 先预览
                    setTimeout(function() {
                        if (uploadImg.previewQueue.length) {
                            var jobId = uploadImg.previewQueue.shift();
                            uploadImg.uploadPreview(jobId);
                        }
                    }, 1);
                    // 后上传
                    setTimeout(function() {
                        if (!uploadImg.isBusy && uploadImg.uploadQueue.length) {
                            var jobId = uploadImg.uploadQueue.shift();
                            uploadImg.isBusy = true;
                            uploadImg.createUpload(jobId, 'newthread', uploadTimer); // 配合uploadImg中的170行
                        }
                    }, 10);
                }, 300);

                e = e || window.event;
                var fileList = e.target.files; // 获取表单中的文件
                if (!fileList.length) {
                    return false; // 如果没有选中文件，直接返回
                }

                for (var i = 0; i<fileList.length; i++) {
                    if (uploadImg.countUpload() >= uploadImg.maxUpload) {
                        jq.DIC.dialog({content:'你最多只能上传8张照片',autoClose:true});
                        break;
                    }

                    var file = fileList[i];

                    if (!uploadImg.checkPicType(file)) {
                        jq.DIC.dialog({content: '上传照片格式不支持',autoClose:true});
                        continue;
                    }
                    // console.log(file);
                    if (!uploadImg.checkPicSize(file)) {
                        jq.DIC.dialog({content: '图片体积过大', autoClose:true});
                        continue;
                    }

                    var id = Date.now() + i; // 生成id策略：当前时间描述+i（第几张图）
                    // 增加到上传对象中, 上传完成后，修改为 true
                    uploadImg.uploadInfo[id] = {
                        file: file,
                        isDone: false,
                    };
                    //留意此处http://dzqun.gtimg.cn/quan/images/defaultImg.png
                    var html = '<li id="li' + id + '"><div class="photoCut"><img src="http://dzqun.gtimg.cn/quan/images/defaultImg.png" class="attchImg" alt="photo"></div>' +
                            '<div class="maskLay"></div>' +
                            '<a href="javascript:;" class="cBtn cBtnOn pa db" title="" _id="'+id+'">关闭</a></li>';
                    jq('#addPic').before(html);

                    uploadImg.previewQueue.push(id);

                    // 图片已经上传了 8 张，隐藏 + 号
                    if (uploadImg.countUpload() >= uploadImg.maxUpload) {
                        jq('#addPic').hide();
                        jq('.iconSendImg').addClass('fail');
                    }
                    //更新剩余上传数
                    setTimeout(function(){
                        uploadImg.uploadRemaining();
                    }, 400);

                }

                // 把文件名框框清空
                jq(this).val('');
            });
            
            jq('.photoList').on('click', '.cBtn', function() {
                // var result = confirm('取消上传这张图片?');
                // if (!result) {
                    // return false;
                // }
                var id = jq(this).attr('_id');
                // 取消这个请求
                if (uploadImg.xhr[id]) {
                    uploadImg.xhr[id].abort();
                }
                // 图片删除
                jq('#li' + id).remove();
                // 表单中删除
                jq('#input' + id).remove();
                uploadImg.uploadInfo[id] = null; // ★★★很重要的一句话，否则页面上无法控制点击上传+号
                //如果删除的微视，添加微视图标高亮
                if(id == 'video'){
                    jq('.iconVideo').addClass('iconVideoOn');
                }

                // 图片变少了，显示+号
                if (uploadImg.countUpload() < uploadImg.maxUpload) {
                    jq('#addPic').show();
                    jq('.iconSendImg').removeClass('fail');
                }
                //更新剩余上传数
                uploadImg.uploadRemaining();

                //当删除所有图片后隐藏添加图片的图标加号（这个页面独有的）
                if(jq('.photoList').find('li').length < 2){
                    jq('.photoList').hide();
                    jq('.sendCon').css('height', module.exports.contentHeight);
                }
            });

        },
        init: function() {

            module.exports.contentHeight = jq('.sendCon').height();

            // 发帖的本地存储 key
            var storageKey = sId + "thread_content";
            // 本地存储 + 输入框文字计算
            jq('#content').val(localStorage.getItem(storageKey));
            timer = setInterval(function() {
                //jq.DIC.strLenCalc(jq('textarea[name="content"]')[0], 'pText', 1000);
                localStorage.removeItem(storageKey);
                localStorage.setItem(storageKey, jq('#content').val());
            }, 500);

            // 发送本次帖子
            var isSubmitButtonClicked = false;
            jq('#submitButton').bind('click', function() {
                if (uploadImg.isBusy) {
                    jq.DIC.dialog({content:'上传中，请稍后发帖', autoClose:true});
                    return false;
                }
                if (isSubmitButtonClicked || !module.exports.checkForm()) {
                    return false;
                }
                var opt = {
                    'noMsg': true,
                    success:function(re) {
                        var status = parseInt(re.errCode);
                        if (status == 0) {
                            clearInterval(timer);
                            localStorage.removeItem(storageKey);
                            //alert(isWX);	//明明在微信里为什么isWX是false?isWX是服务器端返回的?
                            if(re.data.subscribeTip && isWX){
                                jq.DIC.dialog({
                                    content: '发表成功。是否接收回复提醒？',
                                    okValue: '确定',
                                    cancelValue: '取消',
                                    isMask: true,
                                    ok: function (){
                                        pgvSendClick({hottag:'wx.guide.follow.yes'});
                                        //此处有修改
                                        //jq.DIC.reload(re.jumpURL);
                                        location.href = re.jumpURL;
                                    },
                                    cancel: function(){
                                       pgvSendClick({hottag:'wx.guide.follow.no'});
                                       //此处有修改
                                       //jq.DIC.reload(re.jumpURL);
                                       location.href = re.jumpURL;
                                    }
                                });
                                pgvSendClick({hottag:'wx.guide.follow.show'});
                            }else{
                            	//alert('即将跳转'+re.jumpURL);
                            	location.href = re.jumpURL;				//执行的好像是这个
                            }
                            
                            return false;
                        } else {
                            if (status == 34428){ //请先设置性别  re.message.indexOf('性别') != -1){
                               module.exports.userGenderPopWin();
                            }
                            isSubmitButtonClicked = false;
                            if (status != 34428){
                                jq.DIC.dialog({content: re.message, autoClose:true});
                            }
                        }
                    },
                    error:function(re) {
                        isSubmitButtonClicked = false;
                    },
                    'noJump': true
                };
                isSubmitButtonClicked = true;
                jq.DIC.ajaxForm('newthread', opt, true);
                return false;
            });

            // 取消
            jq('.cancelBtn').bind('click', function() {
                // 如果有图片，提示
                if (jq('.photoList .attchImg').length > 0) {
                    jq.DIC.dialog({
                        content:'是否放弃当前内容?',
                        okValue:'确定',
                        cancelValue:'取消',
                        isMask:true,
                        ok:function (){
                            history.go(-1);
                        }
                    });
                } else {
                    history.go(-1);
                }

            });

            jq('#content').on('focus', function() {
                jq('.bNav').hide();
            }).on('blur', function() {
                jq('.bNav').show();
            });

            module.exports.initUpload();
            module.exports.initModal();

            // 表情开关
            emotion.init();
            jq(".photoSelect").on("click", emotion.hide);
            jq(".tagBox a").on("click", function() {
                jq(".tagBox").find('a').attr('class', '');
                jq('.tagTopic').hide();
                var labelId = jq(this).attr('labelId');
                if(jq('input[name="fId"]').val() != labelId) {
                    jq(this).attr('class', 'on');
                    //添加当前标签到输入框上，并设置输入框的css缩进
                    jq('.tagTopic').text(jq(this).text()).show();
                    jq('.sendCon').addClass('tagCon');
                    jq('input[name="fId"]').val(labelId);
                } else {
                    jq('input[name="fId"]').val(0);
                    jq('.sendCon').removeClass('tagCon');
                }
            });
            //选中当前标签
            var selTagId = jq.DIC.getQuery('filterType');
            if(selTagId){
                var tagArr = jq('.tagBox').find('a');
                jq.each(tagArr, function(key, value){
                    jq(value).removeClass('on');
                    if(jq(value).attr('labelid') == selTagId){
                        jq(value).addClass('on');
                        jq(value).click();
                        jq('input[name="fId"]').val(selTagId);
                    }
                })
            }
            if (parseInt(jq('.locationCon').attr('closeStatus')) != 1) {
                module.exports.checkLBS();
            } else {
                jq('.locationCon').removeClass('c1').addClass('c9').html('<i class="iconloc f16 c9 cf"></i>' + '点击开启定位');
                module.exports.getgps = 0;
            }
            jq(".locationCon").on('click', function() {
                if (module.exports.getgps == 1 || module.exports.getgps == 2) {
                    module.exports.getgps = 0;
                    jq('.locationCon').removeClass('c1').addClass('c9').html('<i class="iconloc f16 c9 cf"></i>' + '点击开启定位');
                    jq('#LBSInfoLatitude').val('');
                    jq('#LBSInfoLongitude').val('');
                    jq('#LBSInfoProvince').val('');
                    jq('#LBSInfoCity').val('');
                    jq('#LBSInfoStreet').val('');
                    jq('#cityCode').val('');
                    module.exports.closeLBS();
                } else if (module.exports.getgps == 0) {
                    module.exports.getgps = 1;
                    jq('.locationCon').html('<i class="iconloc f16 c9 cf"></i>' + '正在定位...');
                    module.exports.checkLBS();
                }
            });

            //表情 图片 标签点击切换
            var aOperatIcon = jq('.operatIcon');
            aOperatIcon.on('click', function(){
                var thisObj = jq(this);
                var thisNum = thisObj.attr('data-id');
                var aOperatList = jq('.operatList');
                aOperatList.hide();
                jq(aOperatList[thisNum]).show();
                if(thisNum == 0){
                    jq('.expreList').show();
                    jq('.expreBox').show();
                }
                aOperatIcon.removeClass('on');
                thisObj.addClass('on');
                if(!thisObj.hasClass('iconSendImg')){
                    var photoList = jq('.photoList');
                    if(photoList.find('li').length < 2){
                        photoList.hide();
                        jq('.sendCon').css('height', module.exports.contentHeight);
                    }
                }
            });
            //表情总个数大于手机宽度时显示更多按钮
            var expressionMenu = jq('.expressionMenu').find('a');
            var haveMenuWidth = expressionMenu.length*76;
            var operatingBoxWidth = jq('.operatingBox').width();
            if(haveMenuWidth > operatingBoxWidth){
                jq('.iconArrowR').show();
            };

            //添加微视
            if(jq.DIC.getQuery('syncUnionid')){
                //如果是在授权之后，弹出微视弹窗
                setTimeout(function(){
                   jq('.iconVideo').click(); 
                }, 300)
            }
            jq('.sendNav').on('click', '.iconVideo', function(){
                var thisObj = jq(this);
                var photoList = jq('.photoList');
                if(photoList.find('li').length > 1 && thisObj.hasClass('iconVideoOn')){
                    jq.DIC.dialog({id: 'addWsTips', content: '图片和微视只能发一种哦~', autoClose: 2000});
                    return false;
                };
                if(thisObj.hasClass('iconVideoOn')){
                    module.exports.addWeishiPop(); 
                }
                return false;
            });
            //微视弹窗事件
            jq(document).on('click', '.microTab, .weishiList, .close', function(){
                var thisObj = jq(this);
                //关闭窗口
                if(thisObj.hasClass('close')){
                    jq('#content').show();
                }
                //标签切换
                if(thisObj.hasClass('microTab')){
                    var tabId = thisObj.attr('data-id'),
                        microVideoList = jq('.microVideoList'),
                        microTab = jq('.microTab');
                    FastClick.attach(this);
                    microVideoList.hide();
                    microTab.removeClass('on');
                    thisObj.addClass('on');
                    jq(microVideoList[tabId]).show();
                }
                //列表点击
                if(thisObj.hasClass('weishiList')){

                    var weishiList = jq('.weishiList'),
                        weishiSelect = jq('.weishiSelect'),
                        wsId = thisObj.attr('data-id') || '',
                        wsVid = thisObj.attr('data-vid'),
                        wsPlayer = thisObj.attr('data-player'),
                        wsInsertTime = thisObj.attr('data-inserttime'),
                        wsTimeStamp = thisObj.attr('data-timestamp'),
                        wsText = thisObj.find('.mvText').html(),
                        wsPicUrl = thisObj.find('.mvImg').attr('src');
                    jq('#content').show();
                    weishiList.removeClass('on');
                    thisObj.addClass('on');
                    weishiSelect.removeClass('iconSelect');
                    thisObj.find('.weishiSelect').addClass('iconSelect');
                    setTimeout(function(){
                        var photoList = jq('.photoList');
                        var hasWeishi = photoList.find('#livideo').length > 0;
                        //过滤多次点击
                        if(!hasWeishi){
                            var html = '<li id="livideo"><div class="photoCut"><img src="'+wsPicUrl+'" class="attchImg" alt="photo"></div>' +
                                '<input type="hidden" name="showPicUseableType" value="2">'+
                                '<input type="hidden" name="weishiInfo[id]" value="'+wsId+'">'+
                                '<input type="hidden" name="weishiInfo[vid]" value="'+wsVid+'">'+
                                '<input type="hidden" name="weishiInfo[picUrl]" value="'+wsPicUrl+'">'+
                                '<input type="hidden" name="weishiInfo[player]" value="'+wsPlayer+'">'+
                                    '<a href="javascript:;" class="cBtn cBtnOn pa db" title="" _id="video">关闭</a><i class="iconMicroVideo cf imv pa db"></i></li>';
                            jq('#addPic').before(html).hide();
                            jq('.textTip').hide();
                            jq('.sendCon').css('height', '60');
                            photoList.show();
                            jq('.iconVideo').removeClass('iconVideoOn');
                            jq.DIC.dialog({id: 'weishiPop'});
                        }
                    }, 300);
                }
                return false;
            });
            
        },
        
        //用户性别弹窗
        userGenderPopWin : function(){
           var selectGender = '';
           //交友社区，且未设置性别
           if((!userGender || userGender == '0') && isFriendSite == '1' ){
               var genderForm = template.render('tmpl_setGender');
               // 弹出回复框
               jq.DIC.dialog({
                     content:genderForm,
                     id:'genderForm',
                     isHtml:true,
                     isMask:true,
                     top: 23,
                     // 弹出后执行
                     callback:function() {
                         jq('#genderForm [id="CSRFToken"]').val(CSRFToken);
                         jq('#comBtnGender').on('click', function() {
                            var opt = {
                                success:function(re) {
                                    var status = parseInt(re.errCode);
                                    if (status == 0) {//'操作成功'
                                        userGender = selectGender;//message: ,
                                        // 关闭弹窗
                                        module.exports.isNoShowToTop = false;
                                        jq.DIC.dialog({id:'genderForm'});
                                        jq('.bNav').show();
                                        jq('.floatLayer').show();
                                    }else{
                                        userGender = '';
                                        jq.DIC.dialog({content: re.message, autoClose:true});
                                    }
                                },
                                error:function(re) {
                                    userGender = '';
                                },
                                noJump:true
                            };

                            jq.DIC.ajaxForm('genderForm', opt, true);
                            return false;
                         });

                         jq('#cBtnGender').on('click', function() {
                            jq('#fwin_dialog_genderForm').hide();
                            jq('.g-mask').css('display', 'none');
                            //jq.DIC.dialog({content: '请选择性别', autoClose:true});
                         });

                         // 回复楼中楼
                         jq('.friendsCon li').on('click', function() {
                            var thisObj = jq(this);

                            if(thisObj.attr('id')=='iconMan'){
                                 selectGender = '1';
                                 jq('#genderForm [id="gender"]').val(1);
                                 //jq('#gender').val = 1;
                                 jq('#iconMan').attr('class', 'on');
                                 jq('#iconWoman').attr('class', '');
                            }else{
                                 selectGender = '2';
                                 jq('#genderForm [id="gender"]').val(2);
                                 //jq('#gender').val = 2;
                                 jq('#iconMan').attr('class', '');
                                 jq('#iconWoman').attr('class', 'on');
                            }
                            return;
                            });
                      }
                });//dialog end
           }
        },
        //添加微视窗口
        addWeishiPop : function(){
            var syncUnionid = jq.DIC.getQuery('syncUnionid');
            //请求我的微视
            if(syncUnionid){
               var url = '/weishi/my?pageFlag=0&lastId=0&pageTime=0&syncUnionid='+syncUnionid; 
            }else{
                var url = '/weishi/my?pageFlag=0&lastId=0&pageTime=0';
            }

            var opts = {
                    'success': function(re) {
                        if (re.errCode == 0 && !re.jumpURL) {
                            var weishiPopTmp = template.render('tmpl_newthread_weishi');
                            var myMicroContent = template.render('tmpl_newthread_weishi_list', {data: re.data});
                            // 弹出回复框
                            jq.DIC.dialog({
                                content: weishiPopTmp,
                                id: 'weishiPop',
                                isHtml: true,
                                isMask: true,
                                // 弹出后执行
                                callback: function() {
                                    //添加我的微视内容
                                    jq('#myMicro').html(myMicroContent);
                                    //添加热门微视
                                    module.exports.addHotMircro();
                                    //滚动加载微视
                                    module.exports.getThreadList(); 
                                    jq('#content').hide();
                                }

                            }); 
                        }

                    }
                };
            var data = {CSRFToken: CSRFToken}
            jq.DIC.ajax(url, data, opts);

        },
        addHotMircro: function(){
            //添加热门微视
            var parentId = jq.DIC.getQuery('parentId');
            var url = '/weishi/recommend?start=0&sId='+sId+'&parentId='+parentId;
            var data = {CSRFToken: CSRFToken};
            var opts = {
                'noShowLoading': true,
                'noMsg': true,
                'success': function(re) {
                    if (re.errCode == 0) {
                        var hotMicroContent = template.render('tmpl_newthread_weishi_list', {data: re.data.list});
                        //添加热门微视
                        jq('#hotMicro').html(hotMicroContent);
                        
                    }

                }
            };
            jq.DIC.ajax(url, data, opts);
        },
        getThreadList: function() {
            //窗口滑动加载
            var noMoreMy = false, noMoreHot = false, nextStart = 10;
            jq('#myMicro, #hotMicro').scroll(function(){
                var thisObj = jq(this);
                var parentId = jq.DIC.getQuery('parentId');
                var thisHeight = thisObj.height();
                var scrollTop = thisObj.scrollTop();
                var scrollHeight = thisObj[0].scrollHeight;
                var lastChildId = thisObj.find('.weishiList').last().attr('data-id');
                var pageTime = thisObj.find('.weishiList').last().attr('data-timestamp') || thisObj.find('.weishiList').last().attr('data-inserttime');
                document.body.scrollTop = 0;
                //当窗口滚动到底部自动加载
                if (scrollHeight - thisHeight - scrollTop == 0 ) {
                    //请求我的微视
                    if(thisObj.attr('id') == 'myMicro'){
                        var syncUnionid = jq.DIC.getQuery('syncUnionid');
                        //请求我的微视
                        if(syncUnionid){
                            var url = '/weishi/my?pageFlag=2&lastId=' + lastChildId + '&pageTime=' + pageTime + '&syncUnionid=' + syncUnionid;
                        }else{
                            var url = '/weishi/my?pageFlag=2&lastId=' + lastChildId + '&pageTime=' + pageTime;
                        }
                    }else{
                        var url = '/weishi/recommend?start=' + nextStart + '&sId='+sId+'&parentId='+parentId;
                    }
                    
                    var opts = {
                            'success': function(re) {
                                if (re.errCode == 0 && re.data) {
                                    //我的微视
                                    if(thisObj.attr('id') == 'myMicro'){
                                        var myMicroContent = template.render('tmpl_newthread_weishi_list', {data: re.data});
                                        //追加我的微视
                                        jq('#myMicro').append(myMicroContent);
                                    }else{
                                        //热门微视
                                        if(nextStart != re.data.nextStart){
                                            nextStart = re.data.nextStart;
                                            var hotMicroContent = template.render('tmpl_newthread_weishi_list', {data: re.data.list, type: 'hot'});
                                            //添加热门微视
                                            jq('#hotMicro').append(hotMicroContent);
                                        }
                                    }
                                    
                                }
                                if(!re.data){
                                    if(!noMoreMy && thisObj.attr('id') == 'myMicro' || !noMoreHot && thisObj.attr('id') == 'hotMicro'){
                                        var noMoreHtml = '<li class="showAll" style="height:auto;padding-right:10px;text-align:center;">已显示全部</li>'
                                        if(thisObj.attr('id') == 'myMicro'){
                                            jq('#myMicro').append(noMoreHtml);
                                            noMoreMy = true;
                                        }else{
                                            jq('#hotMicro').append(noMoreHtml);
                                             noMoreHot = true;
                                        }
                                    }
                                }
                            }
                        };
                    var data = {CSRFToken: CSRFToken}
                    jq.DIC.ajax(url, data, opts);
                }
            }); 
        },
        // 关闭用户LBS地理位置
        closeLBS: function() {
            jq.DIC.ajax('/closeLBS', {'CSRFToken' : CSRFToken}, {'noShowLoading' : true,'noMsg': true,'success' : function(result) {}});
        },
        // 获取当前地理位置
        checkLBS : function() {
            gps.getLocation(function(latitude, longitude) {

                /*
                if (latitude == undefined) {
                    module.exports.getgps = 0;
                    jq('#LBSInfoLatitude').val('');
                    jq('#LBSInfoLongitude').val('');
                    jq('#LBSInfoProvince').val('');
                    jq('#LBSInfoCity').val('');
                    jq('#LBSInfoStreet').val('');
                    jq('#cityCode').val('');
                    var gpsError = gps.getError();
                    var errorStr = '获取位置失败';
                    switch(gpsError) {
                        case -1:
                            break;
                        case -2:
                            break;
                        case -3:
                            break;
                    }
                    jq('.locationCon').html('<i class="iconloc f16 c1 cf">' + errorStr);
                    return;
                }
                */
                jq.DIC.ajax('checkLBS', {
                    'CSRFToken' : CSRFToken,
                    'latitude' : latitude,
                    'longitude' : longitude
                }, {
                    'noShowLoading' : true,
                    'noMsg': true,
                    'success' : function(result) {
                        var status = parseInt(result.errCode);
                        var LBSInfo = result.data.LBSInfo;
                        var cityCode = result.data.cityCode;
                        if (status == 0 && module.exports.getgps == 1) {
                            module.exports.getgps = 2;
                            jq('.locationCon').removeClass('c9').addClass('c1').html('<i class="iconloc f16 c1 cf"></i>' + LBSInfo.city + (LBSInfo.street ? (' ' + LBSInfo.street) : ''));
                            if (cityCode) jq('#cityCode').val(cityCode);
                            if (LBSInfo) {
                                jq('#LBSInfoLatitude').val(LBSInfo.latitude);
                                jq('#LBSInfoLongitude').val(LBSInfo.longitude);
                                jq('#LBSInfoProvince').val(LBSInfo.province);
                                jq('#LBSInfoCity').val(LBSInfo.city);
                                jq('#LBSInfoStreet').val(LBSInfo.street);
                            }
                        } else if (module.exports.getgps == 1) {
                            module.exports.getgps = 0;
                            jq('#LBSInfoLatitude').val('');
                            jq('#LBSInfoLongitude').val('');
                            jq('#LBSInfoProvince').val('');
                            jq('#LBSInfoCity').val('');
                            jq('#LBSInfoStreet').val('');
                            jq('#cityCode').val('');
                            jq('.locationCon').addClass('c9').html('<i class="iconloc f16 c9 cf"></i>' + '获取位置失败');
                        }
                    }
                });
            });
        },
        // 按钮模态相关
        initModal: function() {
            // 发送按钮模态
            jq('#submitButton').bind('touchstart', function() {
                jq(this).addClass('sendOn');
            }).bind('touchend', function() {
                jq(this).removeClass('sendOn');
            });
            jq('#cBtn').bind('touchstart', function() {
                jq(this).addClass('cancelOn');
            }).bind('touchend', function() {
                jq(this).removeClass('cancelOn');
            });
        },
        checkForm: function() {

            jq.each(uploadImg.uploadInfo, function(i,n) {
                if (n && !n.isDone) {
                    jq.DIC.dialog({content:'图片上传中，请等待', autoClose:true});
                    return false;
                }
            });

            var content = jq('#content').val();
            var contentLen = jq.DIC.mb_strlen(jq.DIC.trim(content));
            if (contentLen < 15) {
                jq.DIC.dialog({content:'内容过短', autoClose:true});
                return false;
            }

            return true;
        }

    };
    module.exports.init();
});
