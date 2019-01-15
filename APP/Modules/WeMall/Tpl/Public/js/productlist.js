/**
 * @filename productlist
 * @description
 * @author shinnlove
 * 创建时间: 2015-5-9 10:56:03
 */
define('module/productlist', ['lib/zepto', 'lib/artTemplate', 'module/loadmsgshare', 'module/commonUtil', 'module/productfav'], function(require, exports, module) {
	var $ = require('lib/zepto');
	var template = require('lib/artTemplate');
	var loading = require('module/loadmsgshare');
	var util = require('module/commonUtil');
	var productfav = require('module/productfav');
	module.exports = {
			hasMoreData : true,
			isLoadingData : false,
			init : function() {
				// 模块初始化函数
				
				// 第一次直出渲染模板（默认是上架时间）
				var productlist = $.parseJSON(window.productlistjson); // 解析页面的json数据（不管是带了反斜杠，还是没有反斜杠，都可以解析json数据） 
				var tmpl = template.render('productlisttpl', productlist.data); // 利用json数据渲染art模板引擎，返回渲染后的htmlDOM结构与数据
				var productListObj = $('.stockList ul'); 		// 抓取class为stockList的ul，定义为productListObj对象
				if (tmpl == '{Template Error}') {
					tmpl = ""; // 置空tmpl
				}
				productListObj.html(tmpl);					// 渲染成功就把模板渲染的信息写入
				window.nextstart = productlist.nextstart;		// 下一页开始 
				$(".s-items li a[data-order='current_price']").addClass("curr").removeClass("up").addClass("down"); // 默认上架时间标签被选中，选中的时候默认是降序排列
				$(".total-pro-count").html(productlist.totalcount); // 写入所有商品数量
				
				productfav.init(); // 初始化商品收藏事件
				
				// 生成DOM结构事件
				$("body").on("click", ".s-items li a", function(e){
					// 按序查询商品
					e.preventDefault();
					var _t = $(this); // 抓取自身
					window.querysort = _t.data("order"); // 获取按什么顺序排的
					if (_t.hasClass("curr")) {
						// 如果已经是当前页卡
						if (_t.hasClass("down")) {
							_t.removeClass("down").addClass("up");
							window.queryorder = 1; // 已经降序排列则进行升序排列
						} else {
							_t.removeClass("up").addClass("down");
							window.queryorder = 0; // 已经升序排列则进行降序排列
						}
					} else {
						// 如果是切换标签卡
						_t.parent().siblings().find("a").removeClass("curr").removeClass("up").removeClass("down"); // 先移除兄弟节点的这个curr与其up或down
						_t.addClass("curr").removeClass("up").addClass("down"); // 自己标签被选中，选中的时候默认是降序排列
						window.queryorder = 0; // 切换页卡的第一次点击默认是降序排列
					}
					module.exports.getProductList("pull", 0); // 切换/排序页卡，都是相当于是下拉新查询商品（nextstart=0）
				});

				// 向上推（到底部才加载）与向下拉（zepto不是很敏感，jquery较佳）
				$(window).scroll(function() {
					var scrollh = document.body.scrollHeight; // 网页正文全文高
					var clienth = document.documentElement.clientHeight; // 网页可见区域高度
					var scrollt = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop; // 网页被卷去的高
					var limitheight = 20; // 触碰屏幕的距离底部
					if (scrollt + clienth + limitheight >= scrollh && !module.exports.isLoadingData && module.exports.hasMoreData) {
						module.exports.isLoadingData = true;
						module.exports.getProductList('drag'); // 由本模块内的init方法来调用getProductList方法上推加载下一页
					}
					scrollPosition = $(window).scrollTop();
				}).swipeDown(function(){
					if (! module.exports.isLoadingData && document.body.scrollTop <= 1) {
				        module.exports.isLoadingData = true;
				        module.exports.getProductList('pull', 0);			// 由本模块内的init方法来调用getSubbranchList方法下拉刷新
				    }
				});
			},
			getProductList : function(action, nextstart) {
				// Step1：定义请求参数
				var start = 0; // 定义起始页为0
				if (typeof nextstart == 'undefined') {
					start = window.nextstart; // 如果没有定义nextstart下一页数据，就用window.nextstart这个值
				} 
				
				// Step2：根据不同的请求方式（ajax的beforesend）
				switch (action) {
					case 'pull':
						$('.no-more').addClass("fn-hide"); // 默认已显示全部的div隐藏
						break;
					case 'drag':
						// 如果是上推操作
						loading.MLoading.show('加载中');
						break;
					default:
						loading.MLoading.show('加载中');
						break;
				}
				module.exports.hasMoreData = true; // 模块开始请求新数据的标记置为true
				module.exports.isLoadingData = true; // 模块设置为正在请求数据

				// 准备请求参数
				var params = {
						eid : window.eid, // 商家编号
						sid : window.sid, // 分店编号
						querysort : window.querysort, // 查询的字段
						queryorder : window.queryorder, // 查询的顺序
						nextstart : start // 下一页店铺开始的位置
				}
				// Step3：请求并且完成界面不同显示
				$.post(window.requestProductListURL, params, function(result){
					
					loading.MLoading.hide(); // 隐藏等待刷新框
					module.exports.isLoadingData = false; // 本模块正在loading数据的状态变为false
					
					if (result.errCode == 0) {
						// 如果正确请求到了商品数据
						var productListObj = $('.stockList ul'); 		// 抓取class为stockList的ul，定义为productListObj对象
						
						if (action == 'pull') {
							// 如果加载数据的动作是下拉，则有新收藏可能已经更新，需要清空原来的
							productListObj.html('');					// 下拉先清空模板
							var tmpl = template.render('productlisttpl', result.data); // 利用json数据渲染art模板引擎，返回渲染后的htmlDOM结构与数据
							if (tmpl == '{Template Error}') {
								tmpl = ""; // 如果渲染模板出错，置空tmpl
							} 
							productListObj.html(tmpl); // 写入新html数据
						} else {
							// 上推加载下一页的处理
							var tmpl = template.render('productlisttpl', result.data); // 利用json数据渲染art模板引擎，返回渲染后的htmlDOM结构与数据
							if (tmpl == '{Template Error}') {
								tmpl = ''; // 如果渲染失败，则html置空
							}
							productListObj.append(tmpl); // 在文档尾追加新html数据
							
							// 如果返回的json数据的data字段的threadList字段里无数据，就不再加载任何数据
							if (result.data.productlist.length == 0) {
								module.exports.hasMoreData = false; // 模块不在加载数据中
								$('.no-more').removeClass("fn-hide"); // 显示所有数据
							}
						}
						window.nextstart = nextstart = result.nextstart; // 将下一页数据给到nextstart，同时给到window.nextstart
						// 至此，一次pull或者drag操作所有DOM元素与结构、特效变更结束
					} else {
						util.mobileAlert("请求店铺列表失败！");
						return false; // errCode不为0，有错误码，表示请求数据失败
					}
				}, 'json');
			}
	};
	module.exports.init();
});