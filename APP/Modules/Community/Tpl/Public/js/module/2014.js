/**
 * @filename 2014
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2014-05-29 14:56:03
 * 修改记录:
 *
 * $Id$
 **/

define('module/2014', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function() {
            initLazyload('.warp img');

            // 说明展收
            if (jq('.detailShow')[0].scrollHeight > 26) {
                jq('.detailShow a.incoA').show();
                jq.DIC.touchState('.detailShow');
                // 展开内容点击绑定
                jq('.detailShow').on('click', function() {
                    var obj = jq(this);
                    var aObj = obj.find('a');
                    if (aObj.hasClass('iBtnOn1')) {
                        aObj.removeClass('iBtnOn1');
                        obj.css('height', '26px');
                        obj.removeClass('dsOn');
                    } else {
                        aObj.addClass('iBtnOn1');
                        aObj.parent().css('height', 'auto');
                        obj.addClass('dsOn');
                    }
                });
            }

            // 点击跳转
            jq('.rankBox').on('click', '.rank', function() {
                var that = jq(this);
                var link = that.attr('data-link');
                if (link) {
                    jq.DIC.open(link);
                }
                return false;
            });

            // 赞球队按纽
            jq('#teamBox').on('click', '.noClickInco', function(e) {
                e.stopPropagation();
                var that = jq(this);
                var teamNode = that.closest('dd');
                if (teamNode.hasClass('grey')) {
                    return false;
                }
                var nId = teamNode.attr('nid');
                var opts = {
                    'success': function(re) {
                        var status = parseInt(re.errCode);
                        if (status !== 0) {
                            return false;
                        }
                        jq('#teamBox dd').removeClass('on');
                        teamNode.addClass('on');
                        jq('#teamBox .clickInco').removeClass('clickInco').addClass('noClickInco');
                        that.removeClass('noClickInco').addClass('clickInco');
                    },
                    'noShowLoading' : true
//                    'noMsg' : true
                }
                jq.DIC.ajax('/worldCup/like', {'nId':nId, 'CSRFToken':CSRFToken}, opts);
            });

            // 从url中获取当前球队id
            var path = window.location.pathname.match(/\/worldCup\/(\d+)/i);
            if (!path) {
                return false;
            }
            var nId = path[1];

            template.helper('isObjEmpty', function (obj) {
                if (jq.isEmptyObject(obj)) {
                    return true;
                } else {
                    return false;
                }
            });
            // 进入页面加载站点排行
            module.exports.load('user', nId);

            // 社区排行tab切换
            jq('.topicTab a').on('click', function() {
                var that = jq(this);
                var ac = that.attr('ac') || 'site';
                jq('.topicTab a').removeClass('on');
                that.addClass('on');
                if (jq('#' + ac + 'List dd').length < 1) {
                    module.exports.load(ac, nId);
                } else {
                    jq('.thelist').hide();
                    jq('#' + ac + 'List').show();
                }
            });
        },
        load: function(action, nId) {
            var url = '';
            switch (action) {
                case 'user':
                    url = '/worldCup/' + nId + '/user';
                    break;
                case 'site':
                default:
                    url = '/worldCup/' + nId + '/site';
            }

            var opts = {
//                'beforeSend': function() {},
                'complete': function() {
                },
                'success': function(re) {
                    jq('#refreshWait').hide();
                    jq('#loadNext').hide();
                    var status = parseInt(re.errCode);
                    if (status !== 0) {
                        jq.DIC.dialog({content: re.message || '拉取数据失败，请重试', autoClose: true});
                        return false;
                    }

                    var tmpl = template.render('tmpl_2014_' + action, re);
                    jq('#waitForLoad').hide();
                    jq('.thelist').hide();
                    jq('#' + action + 'List').html(tmpl).show();

                    module.exports.initLazyload();
                }
            };
            jq.DIC.ajax(url, '', opts);
        }
    };
    module.exports.init();
});
