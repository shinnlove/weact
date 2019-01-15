define('module/siteCategory', ['module/gps', 'module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var gps = require('module/gps');
    var followSite = require('module/followSite');
    template.helper('isDOMExist', function (id) {
        if (jq('#' + id)[0]) {
            return true;
        } else {
            return false;
        }
    });
    module.exports = {
        init: function() {
            // 站点关注点击
            jq('.warp').on('click', '#categoryList li a', function() {
                var thisObj = jq(this);
                thisObj.off('click');

                if(thisObj.hasClass('iSucc')) {
                    return;
                }

                followSite.followSite.call(thisObj, 'category_index');
            });

            // 站点点击
            jq('.warp').on('click', '#categoryList li h4, #categoryList li i, #categoryList li .subTitle', function() {
                sId = jq(this).parent().attr('sid');
                sId && jq.DIC.open('/' + sId);
            });

            // 分类下站点列表更多点击
            jq('.warp').on('click', '#categoryList .showMore', function() {
                var moreObj = jq(this);
                var cateId = moreObj.attr('cateid');
                var start = moreObj.attr('nextStart');
                var obj = moreObj.parent();
                module.exports.showCategorySiteList(obj, cateId, start);
            });

            // 分类点击展开收起
            jq('#categoryList .interestTit').on('click', function() {
                var thisObj = jq(this);
                var siteListNode = thisObj.closest('.interestList');
                var siteList = siteListNode.find('ul');
                var parentNode = thisObj.parent();
                // 箭头处理
                var folderBtn = thisObj.find('a');
                var unfold = parentNode.attr('unfold');
                if (unfold == 1) {
                    parentNode.attr('unfold', 0);
                    folderBtn.removeClass('iBtnOn1');
                    siteList.hide();
                } else {
                    parentNode.attr('unfold', 1);
                    // 只在无列表时请求站点列表
                    if (siteListNode.find('ul li').length < 1) {
                        var cateId = thisObj.attr('cateid');
                        module.exports.showCategorySiteList(thisObj, cateId, 0);
                    } else {
                        jq('#categoryList ul').hide();
                        jq('#categoryList .fixnode').attr('unfold', 0);
                        jq('#categoryList .interestTit a').removeClass('iBtnOn1');
                        parentNode.attr('unfold', 1);
                        siteList.show();
                        folderBtn.addClass('iBtnOn1');
                        var scrollTop = thisObj.offset().top;
                        // 存在顶部导航时位置调整
                        if (jq('.topBar').is(':visible')) {
                            scrollTop -= 44;
                        }
                        jq(window).scrollTop(scrollTop);
                    }
                }
            });

            var currHash = window.location.hash;
            if (currHash.substr(0, 2) == '#c' && currHash.substr(2)) {
                jq('#categoryList .interestTit[cateid="' + currHash.substr(2) + '"]').click();
            }
            var cId = jq.DIC.getQuery('cId');
            if (cId) {
                jq('#categoryList .interestTit[cateid="' + cId + '"]').click();
            }

/*
                jq(window).scroll(function() {
//                    module.exports.initCatePos();

                    var scrollTop = jq(window).scrollTop();
                    for (var i = 0; i < jq('.fixnode').length; i++) {
                        var currNode = jq(jq('.fixnode')[i]);
                        var currParent = currNode.parent();
                        var nextNode = currParent.find('._nextNode');
                        var currUl = currParent.find('ul');
                        if (currNode.attr('unfold') == 1) {
                            var currNodeTop = currParent.offset().top;
                            if (currNodeTop <= scrollTop && (currNodeTop + currParent.height()) >= (scrollTop + currNode.height())) {
//                                currNode.addClass('fixTop');
                                currNode.css({top:0,position:'fixed', zIndex:999, width:'100%'});
                                currUl.addClass('topS');
                            } else {
                                if ((currNodeTop + currParent.height()) < (scrollTop + currNode.height() + 5) && (currNodeTop + currParent.height()) > scrollTop) {
                                    currNode.css({top:nextNode.offset().top - currNode.height() - scrollTop});
                                } else {
                                    jq(jq('.fixnode')[i+1]).css({top:0,position:'fixed', zIndex:999, width:'100%'});
                                    currNode.css({position:''});
                                    currUl.removeClass('topS');
                                }
                            }
                        } else {
                            currNode.css({position:''});
                            currUl.removeClass('topS');
                        }
                    }
                });
*/
//                jq.DIC.initTouch({obj:jq('.warp')[0], move:function(e, offset) {
//                    var scrollTop = jq(window).scrollTop();
//                    jq('.fixnode').each(function(i, e) {
//                        var thisObj = jq(e);
//                        var thisParentHeight = thisObj.parent().offset().top + thisObj.parent().height();
//                        console.info(scrollTop, thisParentHeight);
//                        if (scrollTop >= thisParentHeight) {
//                            thisObj.addClass('fixTop');
//                            return false;
//                        }
//                    });
//                }});

            //var get_gps = jq.DIC.getcookie('get_gps');
            var get_gps = 0;
            if (!get_gps) {
                gps.getLocation(function(latitude, longitude) {

                    /*
                    if (latitude == undefined) {
                        return;
                    }
                    */

                    jq.DIC.ajax('/checkcity', {
                        'CSRFToken' : CSRFToken,
                        'latitude' : latitude,
                        'longitude' : longitude
                    }, {
                        'noShowLoading' : true,
                        'noMsg': true,
                        'success' : function(result) {
                            var status = parseInt(result.errCode);
                            if (status == 0 && result.data.isShow == 1) {
                                jq.DIC.dialog({
                                    id: 'confirmBox',
                                    content:result.message,
                                    okValue:'确定',
                                    cancelValue:'取消',
                                    isMask:true,
                                    ok:function (){
                                        jq.DIC.ajax('/setcity', {
                                            'CSRFToken' : CSRFToken,
                                            'latitude' : latitude,
                                            'longitude' : longitude
                                        }, {
                                            'noShowLoading' : true,
                                            'success' : function () {
                                                jq.DIC.reload('/category');
                                            }
                                        });
                                    }
                                });
                            }
                        },
                        'complete' : function () {
                            jq.DIC.setcookie('get_gps', '1', 2592000);
                        }
                    });
                });
            }
        },
        // 取分类下站点列表并显示
        showCategorySiteList: function(obj, cateId, start) {
            var url = '/category/' + cateId + '?start=' + start + '&cityflag=' + cityFlag;
            jq.DIC.ajax(url, '', {
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status == 0) {
                        cityFlag = re.data.cityFlag;
                        var parentNode = obj.parent();
                        var siteListNode = obj.closest('.interestList');
                        var siteList = siteListNode.find('ul');
                        var folderBtn = obj.find('a');
                        var moreNode = siteListNode.find('.showMore');

                        // 显示站点列表
                        var tmpl = template.render('tmpl_cateList_site', re.data);
                        moreNode.before(tmpl);
                        // 显示隐下一页按纽
                        if (re.data.more) {
                            moreNode.attr('nextStart', re.data.pageOptions.start);
                            moreNode.show();
                        } else {
                            moreNode.hide();
                        }

                        if (start < 1) {
                            jq('#categoryList ul').hide();
                            jq('#categoryList .fixnode').attr('unfold', 0);
                            jq('#categoryList .interestTit a').removeClass('iBtnOn1');
                            folderBtn.addClass('iBtnOn1');
                            parentNode.attr('unfold', 1);
                            siteList.show();
                            var scrollTop = obj.offset().top;
                            // 存在顶部导航时位置调整
                            if (jq('.topBar').is(':visible')) {
                                scrollTop -= 44;
                            }
                            jq(window).scrollTop(scrollTop);
                        }
                    }
                }
            });
        },

        initCatePos: function() {
            var scrollTop = jq(window).scrollTop();
            var cateNode = jq('.interestList');
            for (var i = 0; i < cateNode.length; i++) {
                var currNode = jq(cateNode[i]);
                var fixNode = currNode.find('.fixnode');
                var fixNodeHeight = fixNode.height();
                var currUlNode =  currNode.find('ul');
                var nextNode = currNode.find('._nextNode');
                if (fixNode.attr('unfold') == 1) {
                    var currNodeTop = currNode.offset().top;
                    var currNodeHeight = currNode.height();
                    // 滚动条在节点上下区域内
                    if (currNodeTop <= scrollTop && (currNodeTop + currNodeHeight - fixNodeHeight) > scrollTop) {
                        fixNode.addClass('fixTop');
                        //                            fixNode.css({top:0,position:'fixed'});
                        currUlNode.addClass('topS');
                    } else {
                        if ((currNodeTop + currNodeHeight - fixNodeHeight) <= scrollTop && (currNodeTop + currNodeHeight) > scrollTop) {
                            fixNode.css({top:nextNode.offset().top - fixNodeHeight - scrollTop});
                        } else {
                            //                                    var nextFixNode = jq(jq(cateNode[i+1])).find('.fixnode');
                            //                                    nextFixNode.css({top:0,position:'fixed', zIndex:999, width:'100%'});
                            fixNode.css({position:''});
                            currUlNode.removeClass('topS');
                        }
                    }
                } else {
                    fixNode.css({position:''});
                    currUlNode.removeClass('topS');
                }
            }
        }
    };
    module.exports.init();
});
