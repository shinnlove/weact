var baseurl = "/WeMall/CustomerRequest",
	checkCollectedURL = baseurl+"/checkCollected", // 检测某商品是否收藏URL
	addCollectionURL = baseurl+"/addCollection", // 增加收藏URL
	deleteCollectionURL = baseurl+"/deleteCollection"; // 删除收藏URL

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
		// 取消搜索
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
		// 点击某一条搜索记录
		e.preventDefault();
       	var word = $(this).text();
        $(".searchBox input[type=search]").val(word);
        $(".searchBox form").submit();
	}).on("click",".clear .btn",function(e){
		// 点击清除历史记录
		e.preventDefault();
		sessionStorage.searchHistory = "";
	    $(this).parent().html('<span>暂无搜索历史记录</span>').removeClass("clear").addClass("noResult").siblings().remove();
	}).on("click",".remove",function(e){
		// 移除某一条搜索记录
		e.preventDefault();
		var word = $(this).siblings(".wbox-1").text();
		var searchHistory = JSON.parse(sessionStorage.searchHistory);
		var searchHistoryNew = $.grep(searchHistory,function(item){
		    return item != word;
		});
		$(this).parent().remove();
		sessionStorage.searchHistory=JSON.stringify(searchHistoryNew)
	});
	
	// 商品收藏绑定事件
	$("body").on("click", ".openFav", function(e) {
		// 点击...弹出收藏遮罩层
		e.stopPropagation(); // 阻止事件传播
		var item = $(this).closest(".stockLink"); // 找到点击的商品编号
		var pid = item.data("id"); // 获取商品编号
		var params = {
				sid : sid,
				pid : pid
		}
		$.post(checkCollectedURL, params, function(result){
			if (result.errCode == 0) {
				if (result.data.collected == 1) {
					item.find(".fav").addClass("faved").text("已收藏");
					item.attr("data-cid", result.data.cid); // 写入收藏夹属性（方便删除）
				}
			} else if (result.errCode == 20001) {
				window.location.href = window.loginurl+"?from="+window.curpage;
			} else {
				mobileAlert(result.errMsg); // 弹出错误信息
			}
		},"json");
		item.find(".cover").show(); // 弹出遮罩层
		return false;
	}).on("click", ".cover .fav", function(e) {
		// 点击收藏按钮
		e.stopPropagation(); // 阻止事件传播
		var _t = $(this), // 当前点中的对象
			pid = _t.closest(".stockLink").data("id"), // 找到离他最近的商品链接的data-id，也可以：$(this).parent().parent().attr("data-id");
			cid = _t.closest(".stockLink").data("cid"), // 找到收藏夹id
			params = {
				sid : sid,
				pid : pid,
				cid : cid
			}
		if (_t.hasClass("faved")) {
			// 如果商品被收藏过，取消收藏
			$.post(deleteCollectionURL, params, function(result){
				if (result.errCode == 0) {
					_t.removeClass("faved").text("已取消收藏");
					setTimeout(function() {
						_t.parent().hide(); // 延时一秒遮罩层消失
						_t.text("收藏");
					}, 1000);
				} else if (result.errCode == 20001) {
					window.location.href = window.loginurl+"?from="+window.curpage;
				} else {
					mobileAlert(result.errMsg); // 弹出错误信息
				}
			},"json");
		} else {
			// 如果商品没有被收藏过，加入收藏
			$.post(addCollectionURL, params, function(result){
				if (result.errCode == 0) {
					_t.addClass("faved").text("已收藏");
					setTimeout(function() {
						_t.parent().hide(); // 延时一秒遮罩层消失
					}, 1000);
				} else if (result.errCode == 20001) {
					window.location.href = window.loginurl+"?from="+window.curpage;
				} else {
					mobileAlert(result.errMsg); // 弹出错误信息
				}
			},"json");
		}
		return false;
	}).on("click", ".cover", function(e) {
		// 喜欢收藏的遮罩层点击，隐藏遮罩（没点中收藏心的情况）
		e.stopPropagation(); // 阻止事件传播
		if (e.target == this) {
			$(this).hide();
		}
		return false;
	}).on("click", ".stockBox", function() {
		// 定义锚节点（返回后还在点击的位置，而不是从头开始）
		var scTop = $("body").scrollTop(), length = $(".stockList .stockBox").length; // 取当前滚动到的位置结点，取DOM结构上当前商品总量
		location.href = "#lastCount=" + length + "&lastTop=" + scTop; // 定义锚节点
	});
});