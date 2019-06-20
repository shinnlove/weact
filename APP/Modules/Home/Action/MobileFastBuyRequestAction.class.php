<?php
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 				// 载入微动微信APISDK类
import ( 'Class.API.MessageVerify.mobileMessage', APP_PATH, '.php' ); 				// 载入短信通知模板类
/**
 * 立即购买请求控制器，为了完成立即购买商品信息的准备，走服务器缓存。
 * @author 赵臣升。
 * CreateTime:2015/10/03 01:32:25.
 */
class MobileFastBuyRequestAction extends MobileGuestRequestAction {
	
	/**
	 * 准备立即购买的商品。
	 */
	public function prepareFastBuy() {
		$product_id = I ( 'pid' ); 			// 商品编号
		$product_type = I ( 'ptype', 2 ); 	// 商品类型（默认服装）
		$skuid = I ( 'skuid' ); 			// 加入购物车的sku
		$amount = I ( 'count', 0 ); 		// 加入购物车的数量
		
		// 检测参数
		if (empty ( $product_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $skuid )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "商品规格不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $amount )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "商品数量不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过参数检验，查询sku信息并加入购物车
		$skumap = array (
				'sizecolor_id' => $skuid,
				'is_del' => 0
		);
		$skuinfo = M ( 'productsizecolor' )->where ( $skumap )->find (); // 从总店sku表中找到sku信息
		$skuamountleft = $skuinfo ['storage_amount'] - $skuinfo ['sell_amount']; // 计算云总店剩余的sku数量
		if ($amount > $skuamountleft) {
			// 如果要加入的数量直接比库存剩余还多，无法加入
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "要购买的SKU数量已超过当前SKU库存量，请及时刷新！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		$product_id = $skuinfo ['product_id']; // 取出product_id
		$productmap = array (
				'product_id' => $product_id, 
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0,
		);
		$productinfolist = M ( 'product_image' )->where ( $productmap )->limit ( 1 )->select (); // 查找商品视图
		$productinfo = $productinfolist [0]; // 取出productinfo信息
		if (empty ( $productinfo )) {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "要购买的商品已失效！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 立即购买的，后台压包后授权，再直接跳到预订单页面取包
		$cartList = array (
				'cartlist' => array (
						0 => array (
								'img' => assemblepath ( $productinfo ['micro_path'] ),
								'e_id' => $this->einfo ['e_id'],
								'cartId' => "-1", 										// 不需要购物车id
								'nav_id' => $productinfo ['nav_id'],
								'product_id' => $product_id,
								'productNum' => $productinfo ['product_number'],
								'name' => $productinfo ['product_name'],
								'skuId' => $skuid,
								'size' => $skuinfo ['product_size'], 					// sku的尺码规格
								'color' => $skuinfo ['product_color'], 					// sku的颜色
								'amount' => $amount,
								'price' => $productinfo ['current_price'],
						),
				),
		);
		$fastbuyjson = jsencode ( $cartList ); 					// json加压
		$currentinfo [$this->einfo ['e_id']] = $fastbuyjson; 	// 放入当前商家下，免得不同商家缓存出错
		session ( "fastbuyinfo", $currentinfo ); 				// 存入浏览器缓存
		
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); 	// 返回给前端ajax信息
	}
	
	/**
	 * 立即购买提交订单。
	 */
	public function fastBuyOrderConfirm() {
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 提交订单的用户
		// 接收订单提交的参数
		$usecoupon_id = I ( 'useCouponId', '' ); 	// 本次订单使用的优惠券编号
		$expressfee = 0;							// 快递费用（已经包含在pay_indeed里），当前暂定0块钱，应该是界面上算好了传入的
		$pay_indeed = I ( 'payment' ); 				// 用户实际支付的价格(订单加邮费的价格)
		$logistics = I ( 'sendType', 0 ); 			// 配送方式，默认快递配送
		$delivery_id = I ( 'deliveryId' ); 			// 如果是快递配送，该字段不能为空，收货人配送信息主键
		$pay_method = I ( 'payType', 2 ); 			// 支付类型默认是2，（微信支付）
		$selfget_name = I ( 'receivePersonName' ); 	// 到店自提的姓名
		$selfget_mobile = I ( 'receiveMobile' ); 	// 到店自提人手机
		$specialremark = I ( 'specialMark' ); 		// 订单的特殊备注
		$shoppingcartlist = stripslashes ( $_REQUEST ['shoppingListJson'] ); // 提交订单的购物车信息（这里是字符串json格式）
		$orderproductlist = json_decode ( $shoppingcartlist, true );
		
		// 对参数进行检验（不满足就打回去）
		/**
		 * 如果使用了优惠券,检测该优惠券编号是否存在（前台读取优惠券是选择没有被使用过的，所以判断是否必须呢？）
		 */
		if (! empty ( $usecoupon_id )) {
			$cusCouponMap = array (
					'customercoupon_id' => $usecoupon_id, 	// 当前使用的优惠券编号
					'e_id' => $this->einfo ['e_id'], 		// 当前商家
					'customer_id' => $customer_id, 			// 当前顾客
					'is_used' => 0, 						// 没用过的券
					'is_del' => 0, 							// 没有被删除的
			);
			$cusCouponInfo = M ( "customercoupon" )->where ( $cusCouponMap )->find ();
			if (! $cusCouponInfo) {
				// 如果所选的优惠券不存在或者已经用过了
				$this->ajaxresult ['errCode'] = 10001;
				$this->ajaxresult ['errMsg'] = "订单提交失败，选择的优惠券信息无效，请重新选择！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		
		// 检测支付金额
		if (empty ( $pay_indeed )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "订单提交失败，支付金额不能为0！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 检测配送方式
		if ($logistics != 0 && $logistics != 1) {
			// 目前物流只支持邮费和到店自提，邮费配送为0，到店自提为1
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "订单提交失败，配送方式只能为邮费快递或到店自提！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 检测收货地址完备性
		$receive_name = ""; 	// 全局变量：收货人姓名
		$receive_mobile = ""; 	// 全局变量：收货人手机
		$receive_address = ""; 	// 全局变量：收货人地址
		if ($logistics == 0) {
			// 如果是邮费快递配送，根据配送信息主键查询配送信息表
			if (empty ( $delivery_id )) {
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "订单提交失败，邮费配送方式收货人信息不能为空！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			$deliverymap = array (
					'deliveryinfo_id' => $delivery_id, 	// 接收到的快递编号
					'e_id' => $this->einfo ['e_id'], 	// 当前商家
					'customer_id' => $customer_id, 		// 当前顾客
					'is_del' => 0, 						// 没有被删除的
			);
			$deliveryinfo = M ( 'deliveryinfo' )->where ( $deliverymap )->find (); // 根据快递主键找到快递信息
			if (! $deliveryinfo) {
				// 如果所选择的快递信息已经被删除了
				$this->ajaxresult ['errCode'] = 10005;
				$this->ajaxresult ['errMsg'] = "订单提交失败，配送方式选择的收货人信息无效，请重新选择！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 如果通过检验，则把收货人姓名、电话和地址给过去
			$receive_name = $deliveryinfo ['receive_person']; 		// 全局变量：收货人姓名
			$receive_mobile = $deliveryinfo ['contact_number']; 	// 全局变量：收货人手机
			$receive_address = $deliveryinfo ['province'] . $deliveryinfo ['city'] . $deliveryinfo ['region'] . $deliveryinfo ['receive_address']; 	// 全局变量：收货人地址
		} else if ($logistics == 1) {
			// 如果是到店自提，直接把自提人姓名和电话给收货人
			if (empty ( $selfget_name ) || empty ( $selfget_mobile )) {
				$this->ajaxresult ['errCode'] = 10006;
				$this->ajaxresult ['errMsg'] = "订单提交失败，到店自提人的信息不完整！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			$receive_name = $selfget_name; 		// 全局变量：收货人姓名
			$receive_mobile = $selfget_mobile; 	// 全局变量：收货人手机
			$receive_address = "顾客到店自提"; 		// 地址是到店自提
		}
		
		// 对支付类型的检测
		if ($pay_method != 2) {
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "订单提交失败，目前只支持微信支付！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 还要检测该商家的微信支付是否完备
		$epayconfigmap = array (
				'e_id' => $this->einfo ['e_id'], // 当前商家
				'is_del' => 0
		);
		$wechatpayinfo = M ( 'secretinfo' )->where ( $epayconfigmap )->find (); // 找到商家的微信支付信息
		if (! $wechatpayinfo) {
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "订单提交失败，当前商家没有设置微信支付信息，请联系该商家完善！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 检测订单子项提交的数据是否为空
		if (empty ( $orderproductlist )) {
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "订单提交失败，要下单购买的东西不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// p('参数通过验证，准备验证价格');die;
		
		// 验证该商品价格是否有变动（其实就一件商品）
		$totalpricenow = 0; 		// 当前应该付的总价
		foreach ( $orderproductlist as &$singlecart ) {
			$fastbuypromap = array (
					'product_id' => $singlecart ['product_id'], // 立即购买的商品
					'sizecolor_id' => $singlecart ['skuId'], // 当前SKU
					'e_id' => $this->einfo ['e_id'], // 当前商家
					'is_del' => 0,
			);
			$productinfo = M ( 'product_sku_new' )->where ( $fastbuypromap )->find (); // 查找商品视图取出productinfo信息
			if (empty ( $productinfo )) {
				$this->ajaxresult ['errCode'] = 10010;
				$this->ajaxresult ['errMsg'] = "要购买的商品已失效！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 比价
			if ($singlecart ['price'] != $productinfo ['current_price']) {
				$this->ajaxresult ['errCode'] = 10011;
				$this->ajaxresult ['errMsg'] = "要购买的商品价格有变动，请重新购买！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 比对当前这一件商品的库存
			if ($singlecart ['amount'] > $productinfo ['sku_storage_left']) {
				$this->ajaxresult ['errCode'] = 10012;
				$this->ajaxresult ['errMsg'] = "要购买的商品数量超过库存！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 补充商品最新积分信息
			$singlecart ['present_score'] = $productinfo ['present_score'];	// 当前商品的回赠积分
			// 叠加计算总价
			$totalpricenow += $singlecart ['amount'] * $productinfo ['current_price']; // 订单总价叠加
		}
		
		// 基本通过信息，可以提交订单：to do ...
		// 进入订单提交要特别注意：$orderproductlist是提交上来的json数据解开，而$buylistinfo是根据解开json的购物车主键查询的最新商品和购物车信息
		
		// to do ... confirm order
		// p('ready confirm!');die;
		
		// 网页调起微信支付需要微信用户的openid（有了支付宝可以修改这部分逻辑，让斯大林类型的账号也进入下单支付流程）
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 获取当前微信用户的openid
		// 特别注意：微信支付的JSAPI支付是必定必定需要openid的，否则订单无法对账了
		if (empty ( $openid )) {
			$this->ajaxresult ['errCode'] = 10012;
			$this->ajaxresult ['errMsg'] = "订单提交失败，下单请使用微信授权方式，您还未微信授权登录！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 首先构造生成的订单主表数据
		$orderMainData = array (
				'order_id' => md5 ( uniqid ( rand (), true ) ), 			// 订单编号（主键）
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => 1, 											// 商城类型是云总店（没有分店、导购或分销商编号）
				'guide_id' => "-1", 										// 商城类型是云总店（没有导购编号，置为-1）
				'visual_number' => time () . randCode ( 4, 1 ), 			// 产生一个可视化编号
				'customer_id' => $customer_id, 								// 当前顾客编号
				'openid' => $openid, 										// 当前微信用户的openid
				'order_time' => time (), 									// 下单时间
				'total_price' => $totalpricenow, 							// 购物车最新的价格(不包括任何打折条件的商品价格)
				'receive_person' => $receive_name, 							// 订单收货人姓名
				'receive_tel' => $receive_mobile, 							// 订单收货人手机
				'receive_address' => $receive_address, 						// 订单收货人地址
				'express_fee' => $expressfee,								// 快递费
				'special_remark' => $specialremark, 						// 顾客的特殊备注（目前没有开放）
				'receive_status' => 0, 										// 订单接收状态（刚提交的订单还没有被商家接收）
				'pay_method' => $pay_method, 								// 订单的支付方式
				'pay_indeed' => $pay_indeed, 								// 实际支付(订单折后价格加邮费，不用再额外加邮费)
				'logistics_method' => $logistics, 							// 物流方式
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 0, 										// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
		);
		if (! empty ( $usecoupon_id )) {
			$orderMainData ['coupon_id'] = $usecoupon_id; 					// 如果使用优惠券，将优惠券编号一并记录到订单主表中
		}
		
		// 接着构造每条订单详情表的数据
		$orderDetailData = array ();
		for($i = 0; $i < count ( $orderproductlist ); $i ++) {
			// 云总店不计算导购提成cutprofit_type和cutprofit_amount直接默认为0
			$orderDetailData [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ), 		// 订单子表
					'order_id' => $orderMainData ['order_id'], 				// （外键）主单的编号
					'product_id' => $orderproductlist [$i] ['product_id'], 	// 商品编号
					'unit_price' => $orderproductlist [$i] ['price'], 		// 当前价格
					'amount' => $orderproductlist [$i] ['amount'], 			// 当前购买数量
					'sku_id' => $orderproductlist [$i] ['skuId'], 			// 云总店的sku主键
					'pro_size' => $orderproductlist [$i] ['size'], 			// 当前sku的尺寸
					'pro_color' => $orderproductlist [$i] ['color'], 		// 当前sku的颜色
					'get_score' => $orderproductlist [$i] ['present_score'] * $orderproductlist [$i] ['amount'], 	// 当前回赠的积分（单件商品积分乘以数量）
					'cutprofit_type' => 0, 									// 云总店无导购提成方式
					'cutprofit_amount' => 0, 								// 云总店无导购提成金额
			);
		}
		
		// 然后构造这笔订单的初始流水状态（刚下单，是一笔订单最初的流水状态）
		$orderStatusData = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => 1, 											// 云总店订单流水
				'order_id' => $orderMainData ['order_id'], 					// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 0, 										// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "在云总店下单，记录订单流水。", // 订单流水原因
		);
		
		// 启动事务
		$orderMainTable = M ( "ordermain" ); 								// 实例化订单主表
		$orderDetailTable = M ( "orderdetail" ); 							// 实例化订单子表
		$orderStatusTable = M ( "orderstatusrecord" ); 						// 实例化订单流水表
		$orderMainTable->startTrans ();
		
		// Step1:插入订单主表和订单子表
		$addmainresult = $orderMainTable->add ( $orderMainData ); 			// 插入订单主表
		$adddetailresult = $orderDetailTable->addAll ( $orderDetailData ); 	// 插入订单子表
		$addstatusresult = $orderStatusTable->add ( $orderStatusData ); 	// 插入订单流水表
		if (! $addmainresult || ! $adddetailresult || ! $addstatusresult) {
			// 三个任何一个不成功就回滚，然后直接返回,否则继续往下
			$orderMainTable->rollback ();
			// 初始化事务失败返回的ajax结果
			$this->ajaxresult ['errCode'] = 10013;
			$this->ajaxresult ['errMsg'] = "订单提交失败，系统接收订单失败，请稍后再试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// Step2:立即购买商品不用删除购物车
		
		// Step3:改变对应分店相关商品库存
		$skutable = M ( 'productsizecolor' ); // 实例化分店商品表
		for($i = 0; $i < count ( $orderproductlist ); $i ++) {
			$updateskumap = array (
					'sizecolor_id' => $orderproductlist [$i] ['skuId'], // 总店商品sku主键
					'is_del' => 0
			);
			$result = $skutable->where ( $updateskumap )->setInc ( 'sell_amount', $orderproductlist [$i] ['amount'] ); // 卖出量增加
			if (! $result) {
				// 一旦有一次失败，则回滚
				$orderMainTable->rollback ();
				// 初始化事务失败返回的ajax结果
				$this->ajaxresult ['errCode'] = 10015;
				$this->ajaxresult ['errMsg'] = "订单提交失败，库存信息回退失败！请稍后重试";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		
		// Step4:如果使用过优惠券,将顾客优惠券表置为使用过
		if (! empty ( $usecoupon_id )) {
			$cusCouponMap = array (
					'customercoupon_id' => $usecoupon_id, 	// 接收到本次订单使用的优惠券编号
					'e_id' => $this->einfo ['e_id'], 		// 当前商家
					'customer_id' => $customer_id, 			// 当前顾客
					'is_used' => 0, 						// 没用过
					'is_del' => 0							// 没有被删除的
			);
			$saveData ['is_used'] = 1; 						// 优惠券被使用了
			$saveData ['used_subbranch'] = "-1"; 			// 云总店使用
			$saveData ['used_for'] = "云总店线上购物"; 			// 云总店线上购物
				
			$useResult = M ( "customercoupon" )->where ( $cusCouponMap )->save ( $saveData );
			if (! $useResult) {
				// 优惠券使用失败，则回滚
				$orderMainTable->rollback ();
				// 初始化事务失败返回的ajax结果
				$this->ajaxresult ['errCode'] = 10016;
				$this->ajaxresult ['errMsg'] = "订单提交失败，优惠券无法使用！请稍后重试";
				$this->ajaxReturn ( $this->ajaxresult ); 	// 返回给前端ajax信息
			}
		}
		
		// Step5：事务成功提交，并返回给前台信息
		$orderMainTable->commit ();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] ['oid'] = $orderMainData ['order_id']; // 附上提交的订单编号
		
		// Step6：发送微信模板消息通知
//		$domain = C ( 'DOMAIN' ); 	// 提取域名
//		$fontcolor = "#DA70D6"; 	// 下单是淡紫色的字体颜色
//		$tpldata = array (
//				'visual_number' => $orderMainData ['visual_number'],
//				'total_price' => $orderMainData ['total_price'] . "元",
//				'receive_person' => $orderMainData ['receive_person'],
//				'receive_tel' => $orderMainData ['receive_tel'],
//				'receive_address' => $orderMainData ['receive_address'],
//		);
//		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->einfo ['e_id']; // 在云总店商城下单则跳转云总店商城的订单中心（2015/08/25 23:59:25）
//		// 策略模式发送下单微信模板消息
//		$ordernotify = new OrderNotify ( $tpldata, $url, $fontcolor ); // 下单通知
//		$msgwechater = new MsgToWechater ( $ordernotify ); // 上下文类对象
//		$sendresult = $msgwechater->sendMsgToWechater ( $this->einfo, $openid ); // 发送模板消息
//
//		// Step7：发送短信通知
//		$type = 'ORDER';
//		$telNum = $orderMainData ['receive_tel'];
//		$ename = $this->einfo ['e_name'];
//		$orderNumber = $orderMainData ['visual_number'];
//		$mobileMsg = new mobileMessage();
//		$sendresult = $mobileMsg->sendMsgNotify ( $telNum, $type, $ename, $orderNumber ); // 发送消息
		
		// 清空快速购买，不让第二次购买
		$_SESSION ['fastbuyinfo'] [$this->einfo ['e_id']] = ""; // 置空json
		unset ( $_SESSION ['fastbuyinfo'] [$this->einfo ['e_id']] );
		
		$this->ajaxReturn( $this->ajaxresult );
	}
	
}
?>