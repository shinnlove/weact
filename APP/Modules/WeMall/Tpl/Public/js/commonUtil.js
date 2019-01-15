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
//统计字符串长度（中文字符算一个，英文算2个）
function getStrLength(str) {  
	var cArr = str.match(/[^\x00-\xff]/ig);  
	return str.length + (cArr == null ? 0 : cArr.length);  
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
// 优惠券跳转预订单页面的URL地址
template.helper('couponPreOrder', function(customercouponinfo){
	var url = "/weact/WeMall/Order/preOrder/sid/" + window.sid;
	return url;
});
// 跳转优惠券详情页面
template.helper('couponInfo', function(customercouponinfo){
	var url = "/weact/WeMall/Coupon/couponInfo/sid/" + window.sid + "/cid/" + customercouponinfo.coupon_id;
	return url;
});

//订单状态函数（新改版的订单状态2015/08/18 17:17:17）
template.helper('getOrderStatus', function (orderself, format) {
	if (orderself.status_flag == 0) {
		// 正常订单流程normal_status
		if (orderself.normal_status == -2) {
			format = "商家发货超时";
		} else if (orderself.normal_status == -1) {
			format = "支付超时";
		} else if (orderself.normal_status == 0) {
			format = "待付款";
		} else if (orderself.normal_status == 1) {
			format = "已付款，等待卖家发货";
		} else if (orderself.normal_status == 2) {
			format = "已发货，待确认";
		} else if (orderself.normal_status == 3) {
			format = "交易成功，待评价";
		} else if (orderself.normal_status == 4) {
			format = "交易成功，已评价";
		} else {
			format = "暂时无法获取状态";
		}
	} else if (orderself.status_flag == 1) {
		// 退款订单流程refund_status
		if (orderself.refund_status == 1) {
			format = "申请退单中";
		} else if (orderself.refund_status == 2) {
			format = "商家同意退单，处理中";
		} else if (orderself.refund_status == 3) {
			format = "商家拒绝退单，请致电协商";
		} else if (orderself.refund_status == 4) {
			format = "已退单";
		} else if (orderself.refund_status == 5) {
			format = "退款关闭";
		} else {
			format = "暂时无法获取状态";
		}
	} else {
		format = "暂时无法获取状态";
	}
    return format;
});

/*业务逻辑状态函数*/
//获取订单状态（中文描述）
function orderStatus(statusflag, normalstatus, refundstatus, withhtml) {
	var html = withhtml || 0; // 是否需要html格式
	var orderstatus = ""; // 订单状态
	if (html) {
		if(flagstatus == 0 && normalstatus == 0) {
			orderstatus = "<font style='color:#F34;'>待支付</font>";
		} else if(flagstatus == 0 && normalstatus == 1) {
			orderstatus = "<font style='color:#449103;'>已支付|待发货</font>";
		} else if(flagstatus == 0 && normalstatus == 2) {
			orderstatus = "<font style='color:#449103;'>已发货|待签收</font>";
		} else if(flagstatus == 0 && normalstatus == 3) {
			orderstatus = "<font style='color:#449103;'>已签收|待评价</font>";
		} else if(flagstatus == 0 && normalstatus == 4) {
			orderstatus = "<font style='color:#F34;'>已评价</font>";
		} else if(flagstatus == 1 && refundstatus == 1) {
			orderstatus = "<font style='color:#F34;'>退款申请中</font>";
		} else if(flagstatus == 1 && refundstatus == 2) {
			orderstatus = "<font style='color:#F34;'>商家同意退款</font>";
		} else if(flagstatus == 1 && refundstatus == 3) {
			orderstatus = "<font style='color:#F34;'>商家拒绝退款</font>";
		} else if(flagstatus == 1 && refundstatus >= 4) {
			orderstatus = "<font style='color:#666;'>已关闭</font>";
		} else {
			orderstatus = "<font style='color:#666;'>未知状态</font>";
		}
	} else {
		if(flagstatus == 0 && normalstatus == 0) {
			orderstatus = "待支付";
		} else if(flagstatus == 0 && normalstatus == 1) {
			orderstatus = "已支付|待发货";
		} else if(flagstatus == 0 && normalstatus == 2) {
			orderstatus = "已发货|待签收";
		} else if(flagstatus == 0 && normalstatus == 3) {
			orderstatus = "已签收|待评价";
		} else if(flagstatus == 0 && normalstatus == 4) {
			orderstatus = "已评价";
		} else if(flagstatus == 1 && refundstatus == 1) {
			orderstatus = "退款申请中";
		} else if(flagstatus == 1 && refundstatus == 2) {
			orderstatus = "商家同意退款";
		} else if(flagstatus == 1 && refundstatus == 3) {
			orderstatus = "商家拒绝退款";
		} else if(flagstatus == 1 && refundstatus >= 4) {
			orderstatus = "已关闭";
		} else {
			orderstatus = "未知状态";
		}
	}
	return orderstatus;
}

//获取订单支付状态
function orderPayMethod(paymethod, withhtml) {
	var html = withhtml || 0; // 是否需要html格式
	var method = ""; // 订单状态
	if (html) {
		if (paymethod == 0) {
			method = "<font style='color:#F34;'>未选择</font>";
		} else if (paymethod == 1) {
			method = "<font style='color:#449103;'>现金支付</font>";
		} else if (paymethod == 2) {
			method = "<font style='color:#449103;'>微信支付</font>";
		} else if (paymethod == 3) {
			method = "<font style='color:#449103;'>刷卡支付</font>";
		} else {
			method = "<font style='color:#666;'>其他类型</font>";
		}
	} else {
		if (paymethod == 0) {
			method = "未选择";
		} else if (paymethod == 1) {
			method = "现金支付";
		} else if (paymethod == 2) {
			method = "微信支付";
		} else if (paymethod == 3) {
			method = "刷卡支付";
		} else {
			method = "其他类型";
		}
	}
	return method;
}
