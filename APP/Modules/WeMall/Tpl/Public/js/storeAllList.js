//qkUtil.loading.show("正在为您查找所有的店");
var latitude=longitude="",userSupplierId=getUrlParam("supplierId"),dataPage=0,allowScroll=true;
var autoGetData=setTimeout(function(){
        getAjaxData(userSupplierId,"","",0,true);
    },6000);
if(sessionStorage.getPosition == "true"){
    clearTimeout(autoGetData);
    latitude=sessionStorage.latitude||"";
    longitude=sessionStorage.longitude||"";
    getAjaxData(userSupplierId,longitude,latitude,0,true);
}else{
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(getStoreList,getLocationFailed); // 第一个参数是调用成功回调函数，第二个参数是调用失败回调函数
    }
}

// 地理位置获取成功执行本函数
function getStoreList(position) {
    latitude = position.coords.latitude;
    longitude = position.coords.longitude;
    clearTimeout(autoGetData);
    getAjaxData(userSupplierId,longitude,latitude,0,true)
}

// 地理位置获取失败执行本函数（更好的改进方法是执行腾讯的获取地理位置方式）
function getLocationFailed(error){
    clearTimeout(autoGetData);
    getAjaxData(userSupplierId,"","",0,true)
}

var totalHistory=localStorage.storeHistory3 ? JSON.parse(localStorage.storeHistory3) : {},
storeHistory=totalHistory[userSupplierId]||[];

// getAjaxData(userSupplierId,longitude,latitude,0,true);

function getDistance(d){
    if(!d){
    	return ""
    }else if(d<500){
    	return "&lt500米";
    }else if(d < 1000){
    	return ~~d+"米";
    }else if(d<10000){
    	return (d/1000).toFixed(2)+"千米";
    }else if(d<100000){
    	return (d/1000).toFixed(1)+"千米";
    }else{
    	return "&gt100千米";
    }
}
function getAjaxData(supplierId,lon,lat,idx,clear){
    if(allowScroll){
        allowScroll=false;
        if($(".loading-bottom").length == 0 && $("#storeAround li").length>0){
            $("body").append('<div class="loading-bottom"><i class="iconfont">&#xe607;</i></div>');
            $("body").scrollTop($("body").scrollTop()+80)
        }
    	var param={
    		supplierId : supplierId,
    		longitude : lon,
    		latitude : lat,
    		index : idx,
    		length : baseOption.pageSize,
    		open : "0"
    	};
    	$.post("searchStoreBySupplierId.php",param,function(data){
    		if(data.status=="0"){
    			var list = data.result.storeSearchVoList,listStr = "";
                if(list.length == 0){
                    listStr = '<li class="noResult"><span class="store">没有找到相关门店</span></li>';
                }else{
        			$.each(list,function(i,e){
                        listStr+='<li>'+
                            '<a data-id='+e.store.id+' href="getStoreHomePage.htm?storeId='+e.store.id+'" class="with-go-right">'+
                                '<div class="wbox">'+
                                    '<div class="name wbox-1">'+e.store.name+($.inArray(e.store.id,storeHistory)>-1 ? '<span class="historyTag c-or fs12"> 最近逛过</span>' : '')+'</div>'+
                                    '<div class="c-9">'+getDistance(e.distance)+'</div>'+
                                '</div>'+
                                '<div class="c-9 pt5">'+(e.store.province||"")+(e.store.city||"")+(e.store.district||"")+" "+(e.store.detail||"")+'</div>'+
                            '</a>'+
                        '</li>';
                    });
                }
    			$("#storeAround").append(listStr);
                $(".container").removeClass("op0");
                qkUtil.loading.hide();
                $(".loading-bottom").remove();
                allowScroll = data.result.all ? false : true;
    		}
    	});
    }
}

$("#storeAround").on("click","li a",function(e){
    var storeId=$(this).data("id");
    if($.inArray(storeId,storeHistory) < 0){
        if(storeHistory.length>4){
            storeHistory.pop();
        }
        storeHistory.unshift(storeId);
        totalHistory[userSupplierId] = storeHistory;
        localStorage.storeHistory3 = JSON.stringify(totalHistory);
    }
    sessionStorage.removeItem("salesId");
    sessionStorage.firstInStore = "true";
    sessionStorage.latitude=latitude;
    sessionStorage.longitude=longitude;
});

scrollToLoadMore({
    callback:function(){
        getAjaxData(userSupplierId,longitude,latitude,dataPage,false);
    }
});