/**
 * @filename myMsg
 * @description
 * 作者: vissong(vissong@tencent.com)
 * 创建时间: 2013-6-5 14:56:03
 * 修改记录:
 *
 * $Id: myMsg.js 31497 2014-08-04 02:12:07Z andyzheng $
 **/

define('module/myMsg', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    template.helper('isDOMExist', function (id) {
        if (jq('#' + id)[0]) {
            return true;
        } else {
            return false;
        }
    });
    module.exports = {
        init: function() {
            // 记录进入到消息页的时间
            var date = new Date();
            localStorage.setItem('seeMsgTime', date.getTime());

            jq('#historyInfo').on('click', module.exports.nextPageMessage);
            jq('.container').on('click', '.topicBox', function() {
                var thisObj = jq(this);
                var parentId = thisObj.attr('parentId') || 0,
                sId = thisObj.attr('sId') || 0,
                tId = thisObj.attr('tId') || 0,
                pId = thisObj.attr('pId') || 0,
                floorPId = thisObj.attr('floorPId') || 0,
                toFloorPId = thisObj.attr('toFloorPId') || 0;
                if (!sId) {
                    return false;
                }

                var url = '/' + sId + '/';
                if (parentId > 0) {
                    url += 'v/' + parentId + '/';
                }
                url += 't/' + tId + '?pId=' + pId;
                if (floorPId > 0) {
                    url += '&floorPId=' + floorPId;
                }
                if (toFloorPId > 0) {
                    url += '&toPId=' + toFloorPId;
                }
                jq.DIC.reload(url);

                return false;
            });
            jq('.container').on('click', '.sImg', function() {
                var thisObj = jq(this);
                var url = thisObj.parent().attr('href');
                jq.DIC.reload(url);
                return false;
            })
            jq('.container').on('click', '.msgfrom', function() {
                e.stopPropagation();
                return false;
            });
        },
        nextPageMessage: function() {
            var next = jq('#historyInfo').attr('next');
            var url = '/my/message?start=' + next;
            // 单站点消息下一页
            var sId = jq.DIC.getQuery('sId') || 0;
            if (sId) {
                url += '&sId=' + sId;
            }
            var opts = {
                'noMsg': true,
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status == 0) {
                        if (!jq.DIC.isObjectEmpty(re.data.dataList)) {
                            var tmpl = template.render('tmpl_msg', re.data);
                            jq('#historyInfo').before(tmpl);
                            jq('#historyInfo').attr('next', re.data.nextStart);
                        } else {
                            // 没有消息，隐藏点击更多
                            jq('#historyInfo').hide();
                            jq('#showAll').show();
                        }
                    }
                },
                'error': function() {
                    var msg = '您的网络有些问题，请稍后再试';
                    jQuery.DIC.dialog({content: msg, autoClose:true});
                }
            };
            jq.DIC.ajax(url, '', opts);
        }
    };
    module.exports.init();
});
