$(".storeSales .appProgress").each(function(i,e){
    var stars = parseFloat($(e).find(".val").data("stars"));
    $(e).find(".val").width((stars*20).toFixed(1)+"%");
    $(e).siblings(".grade").html((stars*2).toFixed(1));
});
getAjaxData(0)
function getAjaxData(idx){
  var param = {
    salesId : getUrlParam("salesId"),
    index : idx,
    length : baseOption.pageSize
  }
  Zepto.getJSON("getSalesAppraiseVoList.php",param,function(data){
    if(data.status=="0"){
      $("#commentCount").html(data.result.count);
      var comStr = "";
      $.each(data.result.salesAppraiseVoList,function(i,e){
          comStr+='<div class="item">\
            <div class="wbox">\
              <img src="'+(e.customer.avatar ? e.customer.avatar :"http://static.qiakr.com/mall/default-photo.png")+'" class="size43 round mr10">\
              <div class="wbox-1 lh20">\
                <div>\
                  '+(e.customer.name ? e.customer.name : "匿名")+'<div class="appProgress ml5"><span class="val" style="width:'+(e.appraise.stars*20).toFixed(2)+'%"></span></div>\
                </div>\
                <div class="fc-grey fs12">'+getLocalTime(e.appraise.gmtCreate)+'</div>\
              </div>\
            </div>\
            <div class="fc-grey pt5">'+e.appraise.comment+'</div>\
          </div>';
      });
      $(".appriseBox .cont").append(comStr);
      if(data.result.all){
          $(".loadingBox a").html("没有更多了").off();
      }else{
          $(".loadingBox a").html("点击查看更多").off().on("click",function(e){
              e.preventDefault();
              getAjaxData(idx + ~~baseOption.pageSize);
          });
      }
    }
  });
}

$(".storeSales .talk a").on("click",function(e){
    e.preventDefault();
    var id=$(this).data("id");
    if(sessionStorage.isLogin=="true"){
        location.href="../webim/chat.htm?salesId="+id;
    }else{
        showMPLoginBox(function(){
            location.reload();
        });
    }
});