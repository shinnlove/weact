/**
 * @filename portalFindSite
 * @description
 * 作者: jazzcai(jazzcai@tencent.com)
 * 创建时间: 2014-04-10 09:35:03
 * 修改记录:
 *
 * $Id$
 **/
define('module/portalFindSite', ['module/gps', 'module/followSite'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var gps = require('module/gps');
    var followSite = require('module/followSite');
    module.exports = {
        init: function(divId){
            // 站点关注点击
            jq('.warp').on('click','#FindSite li a',function() {
                var thisObj = jq(this);
                thisObj.off('click');

                if(thisObj.hasClass('iSucc')) {
                    return;
                }

                followSite.followSite.call(thisObj, 'category_index');
            });

            // 站点点击
            jq('.warp').on('click', '#FindSite li h4, #FindSite li i, #FindSite li .subTitle', function() {
                sId = jq(this).parent().attr('sid');
                sId && jq.DIC.reload('/' + sId);
            });

            // 分类下站点列表更多点击
            jq('.warp').on('click', '#FindSite .showMore', function() {
                var moreObj = jq(this);
                var cateId = moreObj.attr('cateid');
                var start = moreObj.attr('nextStart');
                var obj = moreObj.parent();
                module.exports.showCategorySiteList(obj, cateId, start);
            });

            //获取box的距离Y的位移，用于计算点击分类时移动滚动条的位置
            module.exports.boxOffsetTop = jq('.topBar').height() + jq('.topFixed').height();

            var url = '/category';
            var opts = {
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if(status == 0) {
                        var html = template.render('tmpl_findSites',re.data);
                        jq('#'+ divId).html(html);

                        jq(".interestTit").on('click',function(){
                            var cateId = jq(this).attr("cateid");
                            var isOpened = jq("#arrow_" + cateId).attr('class') != "iBtn db";

                            if(isOpened){
                                jq("#arrow_" + cateId).attr('class','iBtn db');
                                jq('#subSites_'+ cateId).hide();
                                return;
                            } else {
                                var thisObj = jq(this);
                                var siteListNode = thisObj.closest('.interestList');
                                if (siteListNode.find('ul li').length < 1) {
                                    module.exports.showCategorySiteList(jq(this),cateId,0);
                                } else {
                                    var siteList = siteListNode.find('ul');
                                    var folderBtn = thisObj.find('a');
                                    jq('#FindSite ul').hide();
                                    jq('#FindSite .interestTit a').removeClass('iBtnOn1');
                                    folderBtn.addClass('iBtnOn1');
                                    siteList.show();
                                    jq(window).scrollTop(thisObj.offset().top - module.exports.boxOffsetTop);
                                }
                            }
                        });
                    }
                },
                'noShowLoading' : true,
                'noMsg' : true
            }
            jq.DIC.ajax(url, '', opts);
        },
        boxOffsetTop : 0,
        // showSubSites: function(obj){
        //     var cateId = jq(obj).attr("cateid");

        //     var isOpened = jq("#arrow_" + cateId).attr('class') != "iBtn db";

        //     if(isOpened){
        //         jq("#arrow_" + cateId).attr('class','iBtn db');
        //         jq('#subSites_'+ cateId).hide();
        //         return;
        //     }

        //     var url = 'category/' + cateId + '?qzone=1';
        //     var opts = {
        //         'success': function(re) {
        //             var status = parseInt(re.errCode);
        //             if(status == 0) {
        //                 var html = template.render('tmpl_findSites_sub',re.data);
        //                 jq('#subSites_'+ cateId).html(html);
        //                 jq('#subSites_'+ cateId).show();
        //                 jq("#arrow_" + cateId).attr('class','iBtn iBtnOn1 db');

        //                 // 显示隐下一页按纽
        //                 if (re.data.more) {
        //                     jq('#showMore_' + cateId).attr('nextStart', re.data.pageOptions.start);
        //                     jq('#showMore_' + cateId).show();
        //                 } else {
        //                     jq('#showMore_' + cateId).hide();
        //                 }
        //             }
        //         },
        //         'noShowLoading' : true,
        //         'noMsg' : true
        //     }
        //     jq.DIC.ajax(url, '', opts);
        // },
        // 取分类下站点列表并显示
        showCategorySiteList: function(obj, cateId, start) {
            var cityflag = obj.closest('.interestList').find('.showMore').attr('cityFlag');
            var url = '/category/' + cateId + '?qzone=1&start=' + start + '&cityflag='+(cityflag? cityflag : '');
            jq.DIC.ajax(url, '', {
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status == 0) {
                        var parentNode = obj.parent();
                        var siteListNode = obj.closest('.interestList');
                        var siteList = siteListNode.find('ul');
                        var folderBtn = obj.find('a');
                        var moreNode = siteListNode.find('.showMore');
                        moreNode.attr('cityFlag',re.data.cityFlag);
                        // 显示站点列表
                        var tmpl = template.render('tmpl_findSites_sub', re.data);

                        moreNode.before(tmpl);
                        // 显示隐下一页按纽
                        if (re.data.more) {
                            moreNode.attr('nextStart', re.data.pageOptions.start);
                            moreNode.show();
                        } else {
                            moreNode.hide();
                        }

                        if (start < 1) {
                            jq('#FindSite ul').hide();
                            jq('#FindSite .fixnode').attr('unfold', 0);
                            jq('#FindSite .interestTit a').removeClass('iBtnOn1');
                            folderBtn.addClass('iBtnOn1');
                            parentNode.attr('unfold', 1);
                            siteList.show();
                            jq(window).scrollTop(obj.offset().top - module.exports.boxOffsetTop);
                        }
                    }
                }
            });
        }
    };
});
