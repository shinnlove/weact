var type = "",dataPage = 0,allowScroll=true;

// 进入页面，优先获取浏览记录
if(getUrlHash("hLength") && getUrlHash("hTop")){
	$('.s-items a').removeClass("curr");
	$('.s-items a[data-type="'+getUrlHash("hType")+'"]').addClass("curr");
    getOrderData(getUrlHash("hType"),0,false,getUrlHash("hLength"));
    dataPage = parseInt(getUrlHash("hLength"))-20;
}else{
	getOrderData(type,dataPage,false);
}
$(".s-items li a").click(function(e){
    e.preventDefault();
    if($(this).hasClass("curr")){
        return false;
    }
    $(".s-items li a").removeClass("curr");
    $(this).addClass("curr");
    type=$(this).data("type");
    dataPage = 0;
    allowScroll=true;
    getOrderData(type,0,false);
});

function getOrderData(type,idx,more,length){
	if(allowScroll){
		allowScroll=false;
	    if(!more){
	        $(".orderList,.noResult").remove();
	    }
	    if($(".loading-bottom").length == 0){
	        $("body").append('<div class="loading-bottom">加载中...</div>');
	    }
	    var param={
	    	status:type,
	    	index:idx,
	    	length:length ? length.toString() : baseOption.pageSize
	    }
	    $.getJSON("getOrderListOfCustomer.php",param,function(data){
	        var list = data.result.orderList,dataStr = "";
	        if(list.length==0){
	        	dataStr = '<section class="noResult"><span class="order">没有订单记录</span></section>';
	        }else{
		        var tempData={
		            list : data.result.orderList
		        }
		        dataStr = template('tempData', tempData);
			}
			$(".popLoading").remove();
	        $("body").append(dataStr);
	        if(length){
	            $("body").scrollTop(getUrlHash("hTop"));
	        }
	        $(".loading-bottom").remove();
	        allowScroll = data.result.all ? false : true;
	    });
	}
}

$("body").on("click",".payOrder",function(e){
	e.preventDefault();
	var id=$(this).data("id"),payType=$(this).data("paytype");
	$.getJSON("checkBeforePayOrder.json?orderId="+id,function(data){
		if(data.status=="0"){
			if(payType=="1"){
				location.href="alipayConfirm.htm?orderId="+id;
			}else if(payType=="2"){
				location.href="wechatPayOpenId.htm?orderId="+id;
			}else{
				mobileAlert("不支持的支付方式")
			}
		}else{
			mobileAlert(data.msg);
		}
	});
}).on("click",".completeOrder",function(e){
	e.preventDefault();
	if(confirm("请在真正收到货的时候才确认收货\n是否确定？")){
		var id=$(this).data("id"),order = $(this).closest("section");
		$.getJSON("completeOrder.json?orderId="+id,function(data){
			if(data.status == "0"){
				mobileAlert("确认收货成功");
				if(type){
					order.remove();
				}else{
					location.reload();
				}
			}
		});
	}
}).on("click",".orderList .tit",function(){
    var scTop = $("body").scrollTop(),length = $("section.orderList").length;
    location.href="#hLength="+length+"&hTop="+scTop+"&hType="+type;
});

scrollToLoadMore({
    callback:function(){
        getOrderData(type,dataPage,true);
    }
});