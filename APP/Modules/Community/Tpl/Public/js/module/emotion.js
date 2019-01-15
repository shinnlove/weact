/**
 *  表情
 *
 *  作者: 刘卫锋 (kevonliu@tencent.com)
 *  创建时间: 2013-12-11
 *
 *  $Id: emotion.js 31385 2014-07-30 03:53:38Z yixizhou $
 */

define('module/emotion', ['lib/scroll', 'module/smiley'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var mSmiley = require('module/smiley');
    module.exports =  {
        init : function(reInit) {
            // 如果不指定，则只初始化一次
            if(!reInit && window.expreInit == true) {
                return;
            }

            window.expreInit = true;
            window.expreConTab = 0;

            var enabledSmiley = window.enabledSmiley || '';
            var siteSmiley = enabledSmiley.split('|');
            var smileyLen = siteSmiley.length;
            if (enabledSmiley === '' || smileyLen < 1) {
                siteSmiley = [1];
            }
            var cate = [],
                emo = [],
                minPx = [],
                minYPx = [];
            for (var i = 0; i < siteSmiley.length; ++i) {
                var key = 's' + siteSmiley[i];
                if (typeof smiley[key] == 'undefined') {
                    continue;
                }
                minPx.push(smiley[key]['minPx']);
                minYPx.push(smiley[key]['minYPx']);
                cate.push(smiley[key]['title']);
                emo.push(smiley[key]);
            }

            var html = template.render('tmpl_expreBox', {'cate':cate, 'emo':emo});

                if(jq(".tipLayer").size() > 0) {
                    jq(".tipLayer").append(html);
                    jq('.expreCon li').css('background-position', 'center center');
                } else if(jq("#replyForm").size() > 0) {
                    html = "<div class=\"tipLayer mt10\" style=\"display:none\">" + html + "</div>";
                    jq("#replyForm").append(html);

                    jq('.expreCon li').css('background-size', '256px auto');
//                    jq('.expreCon li a').css({'margin':'0', width:'32px', height:'32px'});
//                    jq('#exp0 li a').css({'margin':'0', width:'32px', height:'32px'});
//                    jq('#exp1 li a').css({'margin':'0', width:'51px', height:'51px'});
                    var minPxLen = minPx.length;
                    for (var i = 0; i < minPxLen; ++i) {
                        jq('#exp_emo' + i + ' li a').css({'margin':'0', width: minPx[i] + 'px', height: minYPx[i] + 'px'});
                    }
                    jq('.expreCon li').height('97');
                    jq('.expreCon li').width('256');
                    jq('.expreList').width('256');
                    jq('.expreCon li').css('background-position', 'center center');
                } else {
                    return;
                }

                // 点击输入框下发表情开关
                // jq(".expreSelect").on("click", module.exports.toggle);

                // 点击输入框隐藏表情框
                // jq("#content").on("focus", module.exports.hide);

                // 分组标签卡点击
                jq('.expressionMenu').on('click', 'a', function() {
                    var obj = jq(this);
                    // 分组标签显隐
                    jq('.expressionMenu a').removeClass('on');
                    obj.addClass('on');
                    // 表情组显隐
                    jq('#expreList ul').hide();
                    jq('#exp_' + this.id).show();
                    // todo 分页小点显隐
                    jq('.pNumCon').hide();
                    jq('#exp_' + this.id + '_page').show();
                });

                if (!module.exports.isInit) {
                    new libScroll.initScroll({ulSelector:'#expreList ul', isFlip:true, cSelector:'body', pageOnClass:'on'});
                    new libScroll.initScroll({ulSelector:'#expreBox .expressionMenu', cSelector:'body', childTag:'a'});
                    module.exports.isInit = true;
                }
                libScroll.tabIndex = 0;
                var expreBox = jq("#expreList ul");
                expreBox.on('click', function(e) {
                    return false;
                });

                // 点击每个小表情
                jq.DIC.touchState('#expreBox .expreCon li a', 'on');
                jq("#expreBox .expreCon li a").each(function(i){
                    jq(this).on("click", function() {
                        var title = jq(this).attr("title");
                        if(jq("#content")) {
                            var content = jq("#content").val();
                            if(!title) {
                                if(content && content.lastIndexOf(']') == content.length - 1) {
                                    var LeftIndex = content.lastIndexOf('[');
                                    content = content.substring(0, LeftIndex);
                                } else {
                                    content = content.substring(0, content.length - 1);
                                }
                            } else {
                                content = content + "[" + title + "]";
                            }
                            jq("#content").val(content);
                        }
                    });
                });

            },

            show : function() {
                // epOn
                jq('.expreSelect').addClass('epOn');
                jq('.photoSelect').removeClass('epOn');
                jq('.photoList').hide();
                jq('#expreBox').show();
                jq('.tagBox').hide();
                jq('.locationCon').hide();

                // 如果是回复框
                if(jq('#replyForm').size() > 0) {
                    jq('.tipLayer').show();
                }
            },

            hide : function() {
                // 如果是回复框
                if(jq('#replyForm').size() > 0) {
                    jq('.tipLayer').hide();
                }

                jq('.expreSelect').removeClass('epOn');
                jq('.photoSelect').addClass('epOn');
                jq('#expreBox').hide();
                jq('.photoList').show();
                jq('.tagBox').show();
                jq('.locationCon').show();
            },

            toggle : function() {
                if(jq('#expreBox').css('display') == 'none') {
                    module.exports.show();
                } else {
                    module.exports.hide();
                }
            },
        isInit: false
    }
});
