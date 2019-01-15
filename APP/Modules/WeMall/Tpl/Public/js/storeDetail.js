Zepto.each(previewArr,function(i,e){
    previewStr += '<li style="width:'+document.body.clientWidth+'px;"><img src="'+e+'?imageView2/2/h/360"></li>';
    iconStr += '<a href="#" class="">'+i+'</a>';
});
$('.main_image ul').append(previewStr);
if(previewArr.length > 1){
    $('.flicking_con').append(iconStr);
    $('.flicking_con a:eq(0)').addClass("on");
    $('.flicking_con').css("margin-left","-"+$('.flicking_con a').length*7.5+"px");
    $.mggScrollImg('.main_image ul',{
        loop : true,//循环切换
        auto : true,//自动切换
        callback : function(ind){//这里传过来的是索引值
            $('.flicking_con a').removeClass("on");
            $('.flicking_con a:eq('+ind+')').addClass("on");
        }
    });
}

$(".storeSales .appProgress").each(function(i,e){
    var stars = parseFloat($(e).find(".val").data("stars"));
    $(e).find(".val").width((stars*20).toFixed(1)+"%");
    $(e).siblings(".grade").html((stars*2).toFixed(1));
});

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
