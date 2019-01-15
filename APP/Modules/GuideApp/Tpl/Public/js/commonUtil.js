/*一些共用函数和js获取URL参数的方式*/
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

/*一些共用函数和arttemplate的帮助*/

// artTemplate模板扩展（格式化时间|转2位小数）
template.helper('dateFormat', function (date, format) {
    format = getLocalTime(date,true).replace(/-/g,".");
    return format;
});
// 整型转2位小数
template.helper('toFixed2', function (data, format) {
    format = data.toFixed(2);
    return format;
});
// 订单状态函数
template.helper('getOrderStatus', function (orderself, format) {
	if (orderself.is_payed == 0) {
		format = "待付款";
	} else if (orderself.is_payed == 1 && orderself.is_send == 0) {
    	format = "等待卖家发货";
    } else if (orderself.is_payed == 1 && orderself.is_send == 1 && orderself.is_signed == 0) {
    	format = "等待收货";
    } else if (orderself.is_payed == 1 && orderself.is_send == 1 && orderself.is_signed == 1 && orderself.is_appraised == 0) {
    	format = "交易成功，待评价";
    } else if (orderself.is_payed == 1 && orderself.is_send == 1 && orderself.is_signed == 1 && orderself.is_appraised == 1) {
    	format = "交易成功，已评价";
    } else {
    	format = "暂时无法获取状态";
    }
    return format;
});
// 主页店铺列表控制地址不能太长
template.helper('handleLongAddress', function (address, format) {
	var length = getStrLength(address);
	if (length > 20) {
		format = address.substr(0,10)+"...";
	} else {
		format = address;
	}
	return format;
});
// 统计字符串长度（中文字符算一个，英文算2个）
function getStrLength(str) {  
	var cArr = str.match(/[^\x00-\xff]/ig);  
	return str.length + (cArr == null ? 0 : cArr.length);  
}
// 店铺主页URL地址
template.helper('shopIndexURL', function(sid) {
	var url = "/weact/WeMall/Store/storeIndex/sid/" + sid;
	return url;
});
// 生成商品列表的URL地址
template.helper('productListURL', function(navinfo) {
	var url = "/weact/WeMall/Product/productList/sid/" + window.sid + "/nid/" + navinfo.nav_id;
	return url;
});
// 生成商品详情的URL地址（特别注意：不同店铺收藏的商品所属分店不一样，所以不能用window.sid：2015/05/20改）
template.helper('productDetailURL', function(detailinfo) {
	var url = "/weact/WeMall/Product/productDetail/sid/" + detailinfo.subbranch_id + "/pid/" + detailinfo.product_id;
	return url;
});
// 生成导购评价的URL地址
template.helper('guideCommentURL', function(gid) {
	var url = "/weact/WeMall/Guide/guideComment/sid/" + window.sid + "/gid/" + gid;
	return url;
});
// 生成订单详情的URL地址
template.helper('orderInfoURL', function(oid){
	var url = "/weact/WeMall/Order/orderInfo/sid/" + window.sid + "/oid/" + oid;
	return url;
});