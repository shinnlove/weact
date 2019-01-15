/**
 * @file pmDialog.js
 * 搜索模块
 *
 * @author jinhuiguo, jinhuiguo@tencent.com
 * @version
 * @date 2014-9-9
 *
 */

define('module/pmDialog', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        nextStart: 0,
        isLoading: false,
        init: function() {
            //事件
            module.exports.bindEvents();
            module.exports.getDialogList('new');
        },
        bindEvents: function() {
            var evtMessageInput = jq('.evtMessageInput');
            var evtSendMessage = jq('.evtSendMessage');

            jq(document).on('click', '.evtSendMessage, .evtAvatar', function(e){
                var thisObj = jq(this);

                //模拟手指触摸效果
                jq.DIC.touchStateNow(thisObj);

                //发送
                if(thisObj.hasClass('evtSendMessage')){
                    if(evtMessageInput.val() == ''){
                        jq.DIC.dialog({content: '请输入私信消息内容', autoClose: true});
                    }else{
                        module.exports.sendMessage();
                    }
                };

                //头像点击
                if(thisObj.hasClass('evtAvatar')){
                    var thisUid = thisObj.attr('uId');
                    var url = '/profile/'+thisUid;
                    jq.DIC.reload(url);
                };

                return false;

            });

            var x, y , endX, endY, offsetY;
            jq('.warp').on('touchstart', function(e) {
                x = endX = e.originalEvent.touches[0].pageX;
                y = endY = e.originalEvent.touches[0].pageY;
            }).on('touchmove', function(e) {
                endX = e.originalEvent.touches[0].pageX;
                endY = e.originalEvent.touches[0].pageY;
                offsetY = endY - y;

                // 向下拉刷新
                if (offsetY > 10 && !module.exports.isLoading && document.body.scrollTop <= 1) {
                    module.exports.isLoading = true;
                    jq('#refreshWait').stop(true, true).show();
                    module.exports.getDialogList();
                }
            });

            //解决ios下在唤起键盘时，fixed定位失效的问题
            var evtMessageInput = jq('.evtMessageInput');
            var inputWrap = jq('.inputWrap');
            evtMessageInput.blur(function(){
                inputWrap.css({'position':'fixed'});
                jq('body').css('padding', '0 0 49px');
                return false;
            });
            jq.DIC.initTouch({
                obj: evtMessageInput[0],
                end: function(e, offset) {
                    document.ontouchmove = function(e){ return true;}
                    
                    jq('body').css('padding', '0');
                    setTimeout(function(){
                        var marginNum = document.body.scrollHeight - jq('#privatelyMessage').height() - 49;
                        var clientHeight = document.body.clientHeight;
                        var scrollHeight = document.body.scrollHeight;
                        
                        if(clientHeight == scrollHeight || inputWrap.css('position') == 'static' ){
                            inputWrap.css({'position':'static'});
                        }else if(clientHeight+8 >= scrollHeight){
                            inputWrap.css({'position':'static', 'margin': '20px 0 0'});
                        }else{
                            inputWrap.css({'position':'static', 'margin': marginNum+'px 0 0'});
                        }
                        
                        window.scrollTo(0, document.body.clientHeight+document.body.scrollTop+49);
                        evtMessageInput.focus();
                    },100);
                }
            });
            
        },
        sendMessage: function(){
            var privatelyMessage = jq('#privatelyMessage'),
                inputWrap = jq('.inputWrap'),
                evtSendMessage = jq('.evtSendMessage'),
                evtMessageInput = jq('.evtMessageInput'),
                sId = jq.DIC.getQuery('sId') || 0,
                url = '/my/pm/send',
                targetUid = jq.DIC.getQuery('targetUid') || 0,
                content = jq('.evtMessageInput').val();

            var data = {
                targetUid: targetUid,
                content: content,
                CSRFToken: window.CSRFToken
            };
            if(sId){
                data.sId = sId;
            };
            var opts = {
                beforeSend: function() {
                    //初始化输入状态
                    inputWrap.addClass('disabled');
                    evtMessageInput.attr('disabled', 'disabled');
                    evtSendMessage.html('发送中');
                },
                success: function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0) {
                        //初始化输入状态
                        inputWrap.removeClass('disabled');
                        evtMessageInput.removeAttr('disabled');
                        evtSendMessage.html('发送');
                        return false;
                    }
                    re.data.uId = window.uId;
                    var tmpl = template.render('tmpl_pmDialog', re.data);
                    privatelyMessage.append(tmpl);

                    //初始化输入状态
                    inputWrap.removeClass('disabled');
                    evtMessageInput.removeAttr('disabled');
                    evtSendMessage.html('发送');
                    evtMessageInput.val('');
                    jq('html, body').animate({scrollTop:privatelyMessage.height()},1000);

                    module.exports.nextStart++;
                },
                error: function() {
                    //初始化输入状态
                    inputWrap.removeClass('disabled');
                    evtMessageInput.removeAttr('disabled');
                    evtSendMessage.html('发送');
                },
                'noShowLoading' : true
            };
            jq.DIC.ajax(url, data, opts);
        },
        getDialogList: function(type){
            var privatelyMessage = jq('#privatelyMessage');
            var targetUid = jq.DIC.getQuery('targetUid') || 0;
            var url = '/my/pm/dialog?targetUid=' + targetUid;

            if(type != 'new'){
                url += '&start=' + module.exports.nextStart;
            }
            var opts = {
                complete: function() {
                    jq('#refreshWait').slideUp();
                },
                success: function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0) {
                        module.exports.isLoading = false;
                        return false;
                    }
                    re.data.uId = window.uId;
                    var tmpl = template.render('tmpl_pmDialog', re.data);
                    if(type == 'new'){
                        privatelyMessage.html(tmpl);
                    }else{
                        privatelyMessage.prepend(tmpl);
                    }
                    
                    module.exports.nextStart = re.data.nextStart;

                    module.exports.isLoading = false;
                },
                error: function() {
                    //module.exports.isLoading = false;
                },
                'noShowLoading' : true
            };
            jq.DIC.ajax(url, '', opts);
        }
    };
    module.exports.init();
});
