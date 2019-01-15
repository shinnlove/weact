getAjaxData(3,0);

$(".s-items li a").click(function(e){
    e.preventDefault();
    if($(this).hasClass("curr")){
        return false;
    }
    $(".s-items li a").removeClass("curr");
    $(this).addClass("curr");
    type=$(this).data("type");
    getAjaxData(type,0,true);
});

function getAjaxData(status,idx,clear){
    if(clear){
        $("#couponList").empty();
    }
    $(".loadingBox a").html("正在加载中···");
	var param={
        storeId : getUrlParam("storeId"),
        wechatAccount : getUrlParam("wechatAccount"),
        processing : status,
		index : idx,
		length : baseOption.pageSize
	};
	$.getJSON("getMyCouponList.php",param,function(data){
		if(data.status=="0"){
			var list = data.result.couponList,listStr = "";
            if(list.length == 0){
                listStr = '<div class="noResult"><span class="coupon">咦？一张优惠券都没有</span></div>';
                $(".loadingBox").hide();
            }else{
                $(".loadingBox").show();
    			var tempData={
                    list : data.result.couponList,
                    status : status
                }
                listStr = template('tempData', tempData);
            }
			$("#couponList").append(listStr);
			if(data.result.all){
				$(".loadingBox a").html("没有更多了").off();
			}else{
				$(".loadingBox a").html("点击查看更多").off().on("click",function(e){
					e.preventDefault();
			    	getAjaxData(status,(idx + ~~baseOption.pageSize));
			    });
			}
		}
	});
}