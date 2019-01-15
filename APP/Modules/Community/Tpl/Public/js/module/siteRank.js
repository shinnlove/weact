
define('module/siteRank', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        init: function() {
              jq('.topicBan .voteCon .voteBtn').on('click', function() {
                  var opt = {
                      'success': function(re) {
                          var status = parseInt(re.errCode);
                          if (status == 0) {
                              jq('.topicBan .voteCon .voteBtn').hide();
                              jq('.topicBan .voteCon').append('<a href="javascript:;" class="fl castBtn db">您已投票</a>')
                              jq('.topicBan .voteCon i').html(re.data.ticketNum);
                          }
                      }
                  };
                  jq.DIC.ajaxForm('voteForm', opt, true);
              });
          }
    };
    module.exports.init();
});
