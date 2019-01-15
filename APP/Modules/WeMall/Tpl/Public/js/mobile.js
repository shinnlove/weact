function getUrlParam(key){
	var reg = new RegExp("(^|&)" + key + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
function getUrlHash(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)", "i");
    var r = location.hash.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
String.prototype.getParam = function(key){
	var reg = new RegExp("(#|&)" + key + "=([^&]*)(&|$)");
    var r = this.match(reg);
    if(r) return decodeURIComponent(r[2]);  return "";
}
function mobileAlert(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-50)+'px;" class="ma-box">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}
function mobileToast(con,time){
	$(".ma-box").remove();
	$("body").append('<div class="ma-box-back"></div><div style="width:'+(document.body.clientWidth-140)+'px;" class="ma-box toast">'+con+'</div>');
	hideMobileAlert = setTimeout(function(){$(".ma-box, .ma-box-back").remove()},time||1500);
}
var baseOption = {
	pageSize:"20",
	ickdID:"108386",
	ickdKey:"e5f4bb052cc515e85f217f7fc9d7d580"
}
function getLocalTime(ms,day){
	var _date = new Date(ms);
	var year=_date.getFullYear(),
        month=_date.getMonth()+1,
        date=_date.getDate(),
        hour=_date.getHours(),
        minute=_date.getMinutes(),
        second=_date.getSeconds();
    return year+"-"+(month<10 ? ("0"+month) : month)+"-"+(date<10 ? ("0"+date) : date)+ 
        (!day ? (" "+(hour<10 ? ("0"+hour) : hour)+":"+(minute<10?("0"+minute):minute)+":"+(second<10?("0"+second):second)) : ""); 
}
function scrollToLoadMore(option){
	var wHeight = $(window).height();
	window.onscroll = function(){
        var sHeight = $("body").scrollTop(), cHeight = $(document).height();
        if(sHeight >= cHeight-wHeight-(option.distance ? option.distance : 0)){
            if($(".loading-bottom").length > 0) {
                return false;
            }else{
	            dataPage += (option.length ? option.length : ~~baseOption.pageSize);
	            option.callback();
	        }
        }
	}
}
function getOrderStatus (code) {
	var status = "";
    switch(code){
        case 1 : 
        status="待付款";
        break;
        case 2 :
        status="已发货";
        break;
        case 10 :
        status="待发货";
        break;
        case 3 :
        status="待评价";
        break;
        case 4 :
        status="已完成";
        break;
        case 5 :
        status="已关闭";
        break;
    }
    return status;
}

$(function(){
	// 商品搜索
	$(".searchCover").height($(this).height()-51);
	$(".searchBox.stock input[type=search]").focus(function(){
	    var box = $(this).closest(".searchBox");
    	$(".stockList").hide();
	    box.addClass("doing").find(".s-cancel").show();
	    var searchHistory = sessionStorage.searchHistory ? JSON.parse(sessionStorage.searchHistory) : [],
        	historyStr = "";
	    $.each(searchHistory,function(i,e){
	        historyStr+='<li class="wbox"><a href="" class="wbox-1">'+e+'</a><a href="" class="remove">删除</a></li>';
	    });
	    if(searchHistory.length > 0){
	        historyStr+='<li class="tx-c clear"><a href="" class="btn">清除搜索记录</a></li>';
	    }else{
	        historyStr+='<li class="noResult"><span>暂无搜索历史记录</span></li>';
	    }
	    $(".searchCover").show().find(".history").empty().append(historyStr);
	});
	$(".s-cancel").on("click",function(){
    	$(".searchCover").hide();
	    $(".stockList").show();
	    $(".searchBox").removeClass("doing").find(".s-cancel").hide();
	});
	$(".searchBox.stock form").submit(function(){
	    var searchHistory = sessionStorage.searchHistory ? JSON.parse(sessionStorage.searchHistory) : [];
	    var word = $(".searchBox input[type=search]").val();
	    if(word && $.inArray(word,searchHistory) < 0){
		    searchHistory.unshift(word);
		    sessionStorage.searchHistory = JSON.stringify(searchHistory);
	    }
	});
	$(".searchCover").on("click",".wbox-1",function(e){
		e.preventDefault();
       	var word = $(this).text();
        $(".searchBox input[type=search]").val(word);
        $(".searchBox form").submit();
	}).on("click",".clear .btn",function(e){
		e.preventDefault();
		sessionStorage.searchHistory = "";
	    $(this).parent().html('<span>暂无搜索历史记录</span>').removeClass("clear").addClass("noResult").siblings().remove();
	}).on("click",".remove",function(e){
		e.preventDefault();
		var word = $(this).siblings(".wbox-1").text();
		var searchHistory = JSON.parse(sessionStorage.searchHistory);
		var searchHistoryNew = $.grep(searchHistory,function(item){
		    return item != word;
		});
		$(this).parent().remove();
		sessionStorage.searchHistory=JSON.stringify(searchHistoryNew)
	});
});

// artTemplate模板扩展
template.helper('dateFormat', function (date, format) {
    format = getLocalTime(date,true).replace(/-/g,".");
    return format;
});
template.helper('toFixed2', function (data, format) {
    format = data.toFixed(2);
    return format;
});
template.helper('getStatus', function (data, format) {
    format = getOrderStatus(data);
    return format;
});

