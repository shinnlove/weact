define('module/common', ['module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    require('lib/fastclick');
    var followSite = require('module/followSite');
    module.exports = {
        showSide: function(data) {
            data.sId = sId;
            data.isLiked = window.isLiked;
            var tmpl = template.render('tmpl_sideBar', data);
            jq.DIC.dialog({content:tmpl, id:'sideBar', isMask:true, isHtml:true, callback:function() {
            	jq('#fwin_dialog_sideBar').css({top:'0px', left:'', height:'100%', width:'190px', right:'0px'});
                var sideBar = jq('.sideBar');
                if (!sideBar.is(':visible')) {
                    sideBar.show();
                    //sideBar.animate({right:0},'normal',function(){});
                    //                    } else {
                    //                        sideBar.animate({right:'-190px'},'normal',function(){sideBar.hide();});
                }

                // mask 点击弹出侧边栏
                jq('#fwin_mask_sideBar').on('click', function() {
                        jq.DIC.dialog({id:'sideBar'});
                        //sideBar.animate({right:'-190px'},'normal',function(){
                        //	jq.DIC.dialog({id:'sideBar'});
                        //});
                });
                // class为filter的分类点击
                jq('#sideBarCon').on('click', '.filter', function() {
                    jq.DIC.showLoading();
                    thisObj = jq(this);
                    setTimeout(function() {
                        var labelId = thisObj.attr('labelid') || '';
                        var url = '/' + sId;
                        if (labelId) {
                            url += '?filterType=' + labelId;
                        }
                        jq.DIC.reload(url);
                    }, 10);
                });
                // 自定义标签获取
                module.exports.showCustomTag(data.filterType);

                // 点击进入profile
                jq('#sideProfile').on('click', function() {
                    var url = '/profile/' + uId;
                    if (isWX && sId) {
                        url += '?sId=' + sId;
                    }
                    jq.DIC.open(url);
                    return false;
                });

                // 退出社区
                jq('#sideUnfollow').on('click', function() {
                    var thisObj = jq(this);
                    followSite.unfollowSite.call(thisObj, 'site_index');
                    return false;
                });

                // 禁用滚动
                setTimeout(function() {
                    document.ontouchmove = function(e){ e.preventDefault();};
                }, 10);
            }});
        },
        showCustomTag: function(filterType) {
            if (jq.isEmptyObject(module.exports.labelData)) {
                var url = '/' + sId + '/label';
                var opts = {
                    'beforeSend': function() {
                        jq('#customTagWait').show();
                    },
                    'complete': function() {
                        jq('#customTagWait').hide();
                    },
                    'success': function(re) {
                        var status = parseInt(re.errCode);
                        if (status != 0) {
                            return false;
                        }
                        re.data.filterType = filterType;
                        module.exports.labelData = re.data
                        var tmpl = template.render('tmpl_customTag', module.exports.labelData);
                        jq('#customTag').html(tmpl);
                    },
                    'noShowLoading':true,
                    'noMsg':true
                };
                jq.DIC.ajax(url, '', opts);
            } else {
                jq('#customTagWait').hide();
                var tmpl = template.render('tmpl_customTag', module.exports.labelData);
                jq('#customTag').html(tmpl);
            }
                /*
                var startY, startTop, endY, offsetY;
                var objTop = jq('#sideFilter').scrollTop();
                jq('#sideFilter').on('touchstart', function(e) {
                    jq(this).css({'position':'relative'});
                    startY = e.originalEvent.touches[0].clientY;
                    startTop = jq('#sideFilter').scrollTop();
                }).on('touchmove', function(e) {
                    endY = e.originalEvent.touches[0].clientY;
                    offsetY = -1 * (endY - startY);
                    jq('#sideFilter').scrollTop(startTop + offsetY);
                }).on('touchend', function(e) {
                    startY = endY;
                });
                */
        },
        labelData: {},
        init: function() {
            // 非消息页进入页面时清掉时间
            if (!jq.DIC.in_array('module/myMsg', window.g_module)) {
                localStorage.removeItem('seeMsgTime');
            }
            setInterval(function() {
                // todo 放到 touch里面
                // 回到顶部
                if (window.pageYOffset > 500 && !window.isNoShowToTop) {
                    jq('#goTop').show();
                } else {
                    jq('#goTop').hide();
                }

                // 新消息数的检测
                var lastNewTime = localStorage.getItem('lastNewTime');
                var seeMsgTime = localStorage.getItem('seeMsgTime');
                if (seeMsgTime > lastNewTime) {
                    window.newMsgCount = 0;
                    jq('#newMsgCount').html(0);
                    jq('#navMsgNum').hide();
                    jq('#sideMsgNum').hide();
                    jq('.topicRank .numP').hide();
                }
            }, 200);
            jq('.upBtn').on('click', function() {
                jq('#goTop').hide();
                scroll(0,0);
            });

            if (isNullNick) {
                jq.DIC.dialog({content:'对不起，暂不支持纯表情昵称登录，请调整微信昵称后登录', autoClose:false});
            }

            // 菜单
            jq('#mqOption').on('click', function(){
                var thisObj = jq(this);
                var isSite = thisObj.attr('isSite') || 2;
                if (isSite == 1) {
                    var filterType = jq.DIC.getQuery('filterType');
                    filterType = filterType == 'undefined' ? '' : filterType;
                    var data = {'filterType':filterType, 'newMsgCount':newMsgCount};
                    module.exports.showSide(data);
                    if (window.newMsgCount > 0) {
                        jq('#sideMsgNum').html(window.newMsgCount).show();
                    }
                } else {
                    // 非站点页面点击跳到profile页
                    jq.DIC.open('/profile/' + uId);
                }
                return false;
            });
            jq.DIC.touchState('#mqOption');
        }
    };
    module.exports.init();
});

