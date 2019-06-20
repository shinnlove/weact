<?php
import ( 'Class.API.WeActPay.Main.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动平台安全支付SDK
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 				// 载入微动微信APISDK类
import ( 'Class.API.MessageVerify.mobileMessage', APP_PATH, '.php' ); 				// 载入短信通知模板类
/**
 * 订单请求控制器。
 * @author 赵臣升。
 * 2015/06/18 19:12:25.
 */
class OrderRequestAction extends MobileLoginRequestAction {
	
	/**
	 * ================微信支付请求部分================
	 */
	
	/**
	 * 用户请求提交一笔订单。
	 * 该函数已经针对云总店商城类型和订单状态做过更改：2015/08/18 15:45:36.
	 */
	public function orderConfirm() {
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
		
		// 验证购物车价格是否有变动
		$cartidstring = ""; // 勾选中结算的购物车id编号
		$cartidlist = array (); // 购物车编号数组
		foreach ( $orderproductlist as $singlecart ) {
			array_push ( $cartidlist, $singlecart ['cartId'] ); // 将购物车主键压栈
		}
		$cartidstring = implode ( ",", $cartidlist ); 			// 拼接idlist
		
		// 重查云总店购物车
		$currentcartmap = array (
				'cart_id' => array ( "in", $cartidstring ), 	// 当前购物车编号
				'e_id' => $this->einfo ['e_id'], 				// 当前商家
				'mall_type' => 1, 								// 云总店购物车
				'is_del' => 0, 									// 没有被删除的
		);
		$buylistinfo = M ( 'cart_product_image' )->where ( $currentcartmap )->select (); // 查询出本次订单要购买的商品信息，进行价格比对
		
		// 比价和比对库存
		$storageavailable = true; 	// 默认库存足够，允许提交订单
		$totalpricenow = 0; 		// 当前应该付的总价
		$pricelistnow = array (); 	// (购物车内选中商品的最新信息数组)
		foreach ( $buylistinfo as $currentsinglecart ) {
			$pricelistnow [$currentsinglecart ['cart_id']] = $currentsinglecart; // 购物车主键绝对不会重复
		}
		// 计算总价（$orderproductlist中存放购物车界面信息）
		foreach ( $orderproductlist as $singlecompare ) {
			// 同时判断库存
			if ($singlecompare ['amount'] > $pricelistnow [$singlecompare ['cartId']] ['sku_storage_left']) {
				// 如果要买的数量，大于当前的库存数量，提醒购物车数量不足（防止预订单页面停留过久）
				// 特别注意，比对的是云总店的库存
				$storageavailable = false; // 库存不满足条件
				break; // 立刻终止循环，库存不满足，原价也不需要统计了
			}
			// 同时计算最新价格
			$totalpricenow += $pricelistnow [$singlecompare ['cartId']] ['current_price'] * $singlecompare ['amount']; // 当前的价格乘以需要购买的数量
		}
		// 如果库存通不过检验，直接返回
		if (! $storageavailable) {
			$this->ajaxresult ['errCode'] = 10010;
			$this->ajaxresult ['errMsg'] = "订单提交失败，所购商品库存数量不足，请返回购物车修改数量后重新提交！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// p($totalpricenow);p($pay_indeed);die;
		// 库存通过检验，再比对当前价格，如果当前价格有变动，请顾客重新提交一次预订单（这个不应该比对，因为实际支付价格可能是购物车价格叠加后再减去优惠券减免金额）
		/*
		 * if ($totalpricenow != $pay_indeed) {
		 * //p('not equal');die;
		 * $this->ajaxresult ['errCode'] = 10011;
		 * $this->ajaxresult ['errMsg'] = "订单提交失败，购物车内商品价格有变动，请返回购物车重新提交！";
		 * $this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		 * }
		 */
		
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
		for($i = 0; $i < count ( $buylistinfo ); $i ++) {
			// 云总店不计算导购提成cutprofit_type和cutprofit_amount直接默认为0
			$orderDetailData [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ), 				// 订单子表
					'order_id' => $orderMainData ['order_id'], 						// （外键）主单的编号
					'product_id' => $buylistinfo [$i] ['product_id'], 				// 商品编号
					'unit_price' => $buylistinfo [$i] ['current_price'], 			// 当前价格
					'amount' => $buylistinfo [$i] ['amount'], 						// 当前购买数量
					'sku_id' => $buylistinfo [$i] ['sku_id'], 						// 云总店的sku主键
					'pro_size' => $buylistinfo [$i] ['product_size'], 				// 当前sku的尺寸
					'pro_color' => $buylistinfo [$i] ['product_color'], 			// 当前sku的颜色
					'get_score' => $buylistinfo [$i] ['present_score'] * $buylistinfo [$i] ['amount'], 	// 当前回赠的积分（单件商品积分乘以数量）
					'cutprofit_type' => 0, 											// 云总店无导购提成方式
					'cutprofit_amount' => 0, 										// 云总店无导购提成金额
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
		
		// Step2:删除对应分店购物车内选中商品
		$clearcartresult = M ( 'cart' )->where ( $currentcartmap )->delete (); // 删除本次购物车
		if (! $clearcartresult) {
			$orderMainTable->rollback ();
			// 初始化事务失败返回的ajax结果
			$this->ajaxresult ['errCode'] = 10014;
			$this->ajaxresult ['errMsg'] = "订单提交失败，结算并清除购物车内商品信息失败，请稍后再试！";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// Step3:改变对应分店相关商品库存
		$skutable = M ( 'productsizecolor' ); // 实例化分店商品表
		for($i = 0; $i < count ( $buylistinfo ); $i ++) {
			$updateskumap = array (
					'sizecolor_id' => $buylistinfo [$i] ['sku_id'], // 总店商品sku主键
					'is_del' => 0
			);
			$result = $skutable->where ( $updateskumap )->setInc ( 'sell_amount', $buylistinfo [$i] ['amount'] ); // 卖出量增加
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
//		$url = $domain . "/Home/Order/myOrder/e_id/" . $this->einfo ['e_id']; // 在云总店商城下单则跳转云总店商城的订单中心（2015/08/25 23:59:25）
//		// 策略模式发送下单微信模板消息
//		$ordernotify = new OrderNotify ( $tpldata, $url, $fontcolor ); // 下单通知
//		$msgwechater = new MsgToWechater ( $ordernotify ); // 上下文类对象
//		$tpl_msg_result = $msgwechater->sendMsgToWechater ( $this->einfo, $openid ); // 发送模板消息
//
//		// Step7：发送短信通知
//		$type = 'ORDER';
//		$telNum = $orderMainData ['receive_tel'];
//		$ename = $this->einfo ['e_name'];
//		$orderNumber = $orderMainData ['visual_number'];
//		$mobileMsg = new mobileMessage();
//		$mobile_msg_result = $mobileMsg->sendMsgNotify ( $telNum, $type, $ename, $orderNumber ); // 发送消息
		
		$this->ajaxReturn( $this->ajaxresult );
	}
	
	/**
	 * ================微信支付请求部分================
	 */
	
	/**
	 * 为某笔订单请求微信支付。
	 * 该函数已经针对云总店商城类型和订单状态做过更改：2015/08/18 15:45:36.
	 */
	public function wechatPay() {
		// 接收提交信息
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ), // 接收订单编号
				'pay_method' => I ( 'method' ), // 接收支付方式
		);
		
		$this->ajaxresult = $this->prepareWechatPay ( $ajaxinfo ['order_id'] ); // 准备微信支付信息
		if ($this->ajaxresult ['errCode'] == 0) {
			// 完成微信支付的准备
			$jsapi = new WeActJsAPIPay ( $this->einfo ['e_id'] ); // 微信支付检验控制器
			$readyinfo = $jsapi->callUpJsAPIPayV3 ( $this->ajaxresult ['data'] ); // 准备调起微信支付
			if ($readyinfo) {
				$domain = C ( 'DOMAIN' ); // 读取服务器域名配置
				$params = array (
						'wcpid' => $readyinfo,
						'redirecturi' => $domain . "/Home/Order/myOrder/e_id/" . $this->einfo ['e_id'] . "/checkwxpay/1/wxpayoid/" . $ajaxinfo ['order_id']
				);
				$this->ajaxresult ['data'] = $params;
			} else {
				$this->ajaxresult ['errCode'] = 10006;
				$this->ajaxresult ['errMsg'] = "微信支付调起失败,请等待商家准备好微信支付!";
			}
		}
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 新版本的准备微信支付方法（含原来未处理的各种容错）。
	 * @param string $order_id 订单编号
	 * @return array $resultinfo 微信支付的准备信息
	 * 该函数已经针对云总店商城类型和订单状态做过更改：2015/08/18 15:45:36.
	 */
	public function prepareWechatPay($order_id = "") {
		$resultinfo = array (); // 等待微信支付的信息包
		if (! empty ( $order_id )) {
			$ordermap = array (
					'order_id' => $order_id,
					'is_del' => 0, 
			);
			$orderinfo = M ( 'ordermain' )->where ( $ordermap )->find ();
			if ($orderinfo) {
				// 如果订单存在
				if ($orderinfo ['status_flag'] == 0 && $orderinfo ['normal_status'] == 0) {
					// 0,0状态代表待付款
					$resultinfo ['errCode'] = 0;
					$resultinfo ['errMsg'] = "ok";
					$resultinfo ['data'] = array (
							'e_id' => $orderinfo ['e_id'],
							'openid' => $orderinfo ['openid'],
							'body' => '支付商品订单 ' . $orderinfo ['visual_number'],
							'out_trade_no' => $orderinfo ['order_id'],
							'total_fee' => $orderinfo ['pay_indeed'] * 100, // （应该是实际支付价格，而不再是订单总价了）微信支付转化为分
							'trade_type' => "JSAPI", // 网页下单属于JSAPI
							'time_start' => formatwechatpaydate ( $orderinfo ['order_time'] ),
							'time_end' => formatwechatpaydate ( $orderinfo ['order_time'] + 7200 ), // 默认2小后失效
					);
				} else if ($orderinfo ['status_flag'] == 0 && $orderinfo ['normal_status'] == 1) {
					// 如果订单已经支付
					$resultinfo ['errCode'] = 10002;
					$resultinfo ['errMsg'] = "要准备微信支付的订单已支付，请勿重复提交！";
				} else {
					// 如果订单其他状态不满足支付
					$resultinfo ['errCode'] = 10003;
					$resultinfo ['errMsg'] = "要支付的订单并不满足可支付状态，请及时刷新页面！";
				}
			} else {
				// 订单不存在
				$resultinfo ['errCode'] = 10004;
				$resultinfo ['errMsg'] = "要准备微信支付的订单不存在，请勿重复提交！";
			}
		} else {
			// 订单编号为
			$resultinfo ['errCode'] = 10005;
			$resultinfo ['errMsg'] = "要准备微信支付的订单编号不能为空！";
		}
		return $resultinfo;
	}
	
	/**
	 * ================订单收货请求部分================
	 */
	
	/**
	 * 2015/08/26 11:08:36已复审签收代码无误。
	 * 为某笔订单收货进行确认。
	 * 该函数已经针对云总店商城类型和订单状态做过更改：2015/08/19 16:10:36.
	 * 订单签收，正常流程状态+1，退款流程状态沿用。
	 */
	public function signReceiveOrder() {
		// 接收提交信息
		$order_id = I ( 'oid' ); 					// 接收订单编号
		if (empty ( $order_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "签收失败，要签收的订单编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		
		// 实例化三张表
		$maintable = M ( 'ordermain' ); 			// 实例化订单主表
		$detailtable = M ( 'orderdetail' ); 		// 实例化订单子表
		$statustable = M ( 'orderstatusrecord' ); 	// 实例化订单流水表
		$customerid = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 当前顾客编号
		
		// 查询出订单主单信息
		$ordermap = array (
				'order_id' => $order_id, 
				'is_del' => 0, 
		);
		$maininfo = $maintable->where ( $ordermap )->find (); 	// 找到这笔需要签收的订单的主单信息
		
		// 开始判断订单是否满足可以签收的条件（如下几种情况直接毙掉）
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "不存在的订单，无法签收！";
			$this->ajaxReturn ( $this->ajaxresult ); 			// ajax返回给前台信息
		} 
		// 对订单不同流程分开判断逻辑更为清晰
		if ($maininfo ['status_flag'] == 0) {
			// 正常流程中，处于已付款|待发货状态，未发货不能签收
			if ($maininfo ['normal_status'] <= 1) {
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "商家还没有发货不能签收，请耐心等待或联系商家！";
				$this->ajaxReturn ( $this->ajaxresult ); 		// ajax返回给前台信息
			}
			// 正常流程中，处于已签收状态，不能重新签收
			if ($maininfo ['normal_status'] >= 3) {
				$this->ajaxresult ['errCode'] = 10005;
				$this->ajaxresult ['errMsg'] = "订单已经被签收，请不要重复提交！";
				$this->ajaxReturn ( $this->ajaxresult ); 		// ajax返回给前台信息
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 退款流程中的订单不能签收
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "订单处于退款流程中，无法签收！";
			$this->ajaxReturn ( $this->ajaxresult ); 			// ajax返回给前台信息
		}
		
		// 通过检验，进行订单签收的事务过程
		$maintable->startTrans (); // 开启签收订单事务
		
		$malltype = $maininfo ['mall_type']; 					// ！！！重要：提取该笔订单的商城类型（云总店可以操作各种流程的）
		$refundstatus = $maininfo ['refund_status']; 			// ！！！重要：主单现在的退款状态（更新正常流程，退款态沿用原来的值）
		
		// Step1：订单主表更改签收状态和记录签收时间
		$signinfo = array (
				'normal_status' => 3, 							// 更新为已签收状态
				'signed_time' => time (), 						// 签收时间
		);
		$signresult = $maintable->where ( $ordermap )->save ( $signinfo ); // 签收订单
		
		// Step2：在订单流水表中记录一笔订单的签收记录
		$statusinfo = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => $malltype, 									// 签收订单属于哪个商城就用哪个商城的
				'order_id' => $order_id, 									// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 3, 										// 正常态3代表订单被签收（2.0版本新增订单流水状态）
				'refund_status' => $refundstatus, 							// 修改正常态流水，退款态沿用原来的值（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "签收订单，记录订单流水。", // 订单流水原因
		);
		$statusresult = $statustable->add ( $statusinfo ); 					// 增加一笔签收的订单流水
		
		// Step3：如果有积分，则结算积分给顾客
		$sendscore = true; 													// 给顾客积分
		$scoresum = 0; 														// 订单所获得的总积分
		$detaillist = $detailtable->where ( $ordermap )->select (); 		// 查询订单子单
		$detailcount = count ( $detaillist ); 								// 计算子单数量
		if ($detailcount) {
			for ($i = 0; $i < $detailcount; $i ++) {
				$scoresum += $detaillist [$i] ['get_score']; 				// 总积分叠加
			}
		}
		// 有积分则执行插入
		if ($scoresum) {
			$addscoreinfo = array (
					'score_id' => md5 ( uniqid ( rand (), true ) ), 		// 生成顾客积分
					'e_id' => $this->einfo ['e_id'], 						// 商家编号
					'customer_id' => $customerid, 							// 顾客编号
					'mall_type' => $malltype, 								// 积分来源订单属于哪个商城就用哪个商城的
					'subbranch_id' => $maininfo ['subbranch_id'], 			// 订单来自哪个分店就属于哪个
					'distributor_id' => $maininfo ['distributor_id'], 		// 订单来自哪个分销店铺
					'change_time' => time (), 								// 取当前时间
					'change_amount' => $scoresum, 							// 增加相应数量积分
					'change_reason' => "顾客签收订单，赠送购买订单相应积分", 			// 积分变换理由
			);
			$sendscore = M ( 'customerscore' )->add ( $addscoreinfo ); 		// 往顾客积分表里插入数据
		} 
		
		// 处理签收和流水结果
		if ($signresult && $statusresult && $sendscore) {
			$maintable->commit (); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$maintable->rollback (); // 回滚事务
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "签收失败，系统繁忙，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
	}
	
	/**
	 * ================订单协商撤销部分================
	 */
	
	/**
	 * 2015/08/26 15:38:36已复审取消代码无误。
	 * 取消毕竟是对未支付的订单进行取消，而完成了的订单叫做删除。
	 * 用户对未付款订单进行取消，未付款订单取消和删除是一样的处理。
	 * 特别注意：取消订单条件是，用户未付款的订单都可以取消，付款的订单只能协商撤销。
	 * 2015/08/19 16:07:25改动：针对订单新状态进行判断是否已支付。
	 * 未支付订单取消，正常流程状态变为-1，退款流程状态沿用。
	 */
	public function cancelOrder() {
		// 接收提交信息
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ), 			// 接收订单编号
		);
		$maintable = M ( 'ordermain' ); 			// 实例化订单主表
		$statustable = M ( 'orderstatusrecord' ); 	// 实例化订单流水表
		
		// 查询出订单主单信息
		$ordermap = array (
				'order_id' => $ajaxinfo ['order_id'], 
				'is_del' => 0, 
		);
		$maininfo = $maintable->where ( $ordermap )->find (); // 找出要取消的订单信息
		
		// 检测订单能否被取消
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "不存在的订单，无法取消，请不要重复提交！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		// 对订单不同流程分开判断逻辑更为清晰
		if ($maininfo ['status_flag'] == 0) {
			// normal_status >= 1代表订单已支付 2015/08/19 16:05:36改
			if ($maininfo ['normal_status'] >= 1) {
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "无法取消已支付订单，请联系商家协商撤销！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 退款流程中的订单不能取消
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "退款中的订单无法取消，请联系商家协商撤销！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		
		// 通过检测，进入取消订单流程
		$maintable->startTrans (); // 开启取消订单事务过程
		
		$malltype = $maininfo ['mall_type']; 					// ！！！重要：提取该笔订单的商城类型（云总店可以操作各种流程的）
		$refundstatus = $maininfo ['refund_status']; 			// ！！！重要：主单现在的退款状态（更新正常流程，退款态沿用原来的值）
		
		// Step1：先取消并删除订单
		$cancelinfo = array (
				'normal_status' => -1, 				// 顾客超时、手动删除订单
				'cus_mark_del' => 1, 				// 顾客删除订单
				'remark' => "顾客在" . timetodate ( time () ) . "手动取消未支付的订单。", // 记录顾客取消订单的行为
				'is_del' => 1, 						// 订单被删除（未曾支付订单不需要保留）
		);
		$cancelresult = $maintable->where ( $ordermap )->save ( $cancelinfo ); // 取消订单
		
		// 未支付订单取消，归为支付超时一类
		$statusinfo = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => $malltype, 									// 沿用原来的订单所属商城
				'order_id' => $ajaxinfo ['order_id'], 						// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => -1, 										// 正常态-1代表订单超时未支付或被顾客手动取消（2.0版本新增订单流水状态）
				'refund_status' => $refundstatus, 							// 修改正常态流水，退款态沿用原来的值（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "手动取消未支付订单，记录订单流水。", // 订单流水原因
		);
		$statusresult = $statustable->add ( $statusinfo ); 					// 增加一笔取消订单的订单流水
		
		// 处理取消订单结果
		if ($cancelresult && $statusresult) {
			$maintable->commit (); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$maintable->rollback (); // 回滚事务
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "取消订单失败，系统繁忙，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
	}
	
	/**
	 * ================订单协商撤销部分================
	 */
	
	/**
	 * 2015/08/26 15:41:36已复审退款申请代码无误。
	 * 顾客请求协商撤销某笔订单的ajax处理函数。
	 * 特别注意：协商撤销订单条件是，用户已经付款后的订单，在收货后7天内（收货也有默认天数）才能退。
	 * 2015/08/19 17:00:25改动：针对订单新状态进行判断是否可以提交协商撤销申请和更改订单状态。
	 * 协商撤销申请将订单的状态改为status_flag = 1，而后进入退款第一步骤，正常状态流程沿用。
	 */
	public function refundApplyOrder() {
		// 接收提交信息
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ), 			// 接收订单编号
				'consult_reason' => I ( 'reason' ), // 接收退款理由
		);
		$maintable = M ( 'ordermain' ); 			// 实例化订单主表
		$statustable = M ( 'orderstatusrecord' ); 	// 实例化订单流水表
		
		// 查询出订单主单信息
		$ordermap = array (
				'order_id' => $ajaxinfo ['order_id'], 
				'is_del' => 0, 
		);
		$maininfo = $maintable->where ( $ordermap )->find (); // 找到要协商撤销的订单信息
		
		// 检测订单能否被协商撤销
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "不存在的订单，无法协商撤销，请不要重复提交！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		// 对订单不同流程分开判断逻辑更为清晰
		if ($maininfo ['status_flag'] == 0) {
			// 订单未支付不能提交协商撤销请求，normal_status<=0代表订单未支付
			if ($maininfo ['normal_status'] <= 0) {
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "订单未支付，无需协商撤销！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
			// 如果订单被签收，一个星期之内的才可以退款，检测是否满足一星期，status_flag == 0 并且 normal_status >= 3是签收状态
			if ($maininfo ['normal_status'] >= 3) {
				$timenow = time (); // 取当前时间
				$timespan = $timenow - $maininfo ['signed_time']; // 减去签收时间，获取时间差
				if ($timespan > 604800) {
					$this->ajaxresult ['errCode'] = 10004;
					$this->ajaxresult ['errMsg'] = "签收超过7天的订单无法协商撤销！";
					$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
				}
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 已经提交过协商撤销请求，正在处理中，status_flag == 1代表订单退款流程中
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "正在协商撤销中，请不要重复提交！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		
		// 如果通过检验，则允许提交订单的协商撤销申请
		
		$maintable->startTrans (); // 订单主表开启事务
		
		$malltype = $maininfo ['mall_type']; 			// ！！！重要：提取该笔订单的商城类型（云总店可以操作各种流程的）
		$normalstatus = $maininfo ['normal_status']; 	// 得到订单的正常流程状态，更改退款态不需要更改正常流水
		
		// Step1：更新订单的状态，从正常态改为退款流程申请第一步
		$applyinfo = array (
				'refund_apply_time' => time (), 		// 非常重要！！！一定要填写退款申请时间，才可以显示出客户端是否可以亮起申领退款
				'status_flag' => 1, 					// 订单进入退款流程态
				'normal_status' => $normalstatus, 		// 沿用订单正常流水的状态
				'refund_status' => 1, 					// 1代表顾客提交退款申请等待商家处理
				'consult_reason' => $ajaxinfo ['consult_reason'], // 顾客对订单协商撤销理由
		);
		$applyresult = $maintable->where ( $ordermap )->save ( $applyinfo );
		
		// Step2：记录订单流水，从正常态转为退款流程的第一步
		$statusinfo = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => $malltype, 									// 沿用原来的订单所属商城
				'order_id' => $ajaxinfo ['order_id'], 						// 这笔订单的主单编号
				'status_flag' => 1, 										// 订单进入退款流水状态（2.0版本新增订单流水状态）
				'normal_status' => $normalstatus, 							// 处理订单退款，正常流程状态值沿用（2.0版本新增订单流水状态）
				'refund_status' => 1, 										// 订单进入退款流程，退款流程进入第一步（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "对该订单申请退款，记录订单流水。", // 订单流水原因
		);
		$statusresult = $statustable->add ( $statusinfo ); 					// 增加一笔取消订单的订单流水
		
		// 处理订单退款流程事务结果
		if ($applyresult && $statusresult) {
			$maintable->commit (); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$maintable->rollback (); // 回滚事务
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "提交申请失败，系统繁忙，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
	}
	
	/**
	 * ================订单删除请求部分================
	 */
	
	/**
	 * 顾客删除某笔订单的ajax处理函数。
	 * 特别注意：删除订单条件是，订单未付款或者已经被签收的订单（走完一个流程了也可以删除），而且并不一定要强制评价。
	 * 2015/08/19 17:18:25改动：针对订单新状态进行判断，是否可以删除订单：
	 * 满足status_flag == 0，并且交易完成（normal_status >= 3）就可以进行删除；
	 * 满足status_flag == 1，并且退款完成或者退款完结（refund_status >= 4），也可以删除。
	 * 特别注意：顾客删除订单，只是在移动端不可见，读取订单时由移动端后端屏蔽被删除的订单，所以删除一笔可以删除的订单，并不需要记录订单流水。
	 */
	public function deleteOrder() {
		// 接收提交信息
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ), 	// 接收订单编号
		);
		$maintable = M ( 'ordermain' ); 	// 实例化订单主表
		
		// 查询出订单主单信息
		$ordermap = array (
				'order_id' => $ajaxinfo ['order_id'],
				'cus_mark_del' => 0, 		// 顾客没有删除的订单
				'is_del' => 0,
		);
		$maininfo = $maintable->where ( $ordermap )->find (); // 查找出这笔要准备删除的订单
		
		// 判断订单是否满足可被删除条件
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "不存在的订单，无法删除，请不要重复提交！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		// 对订单不同流程分开判断逻辑更为清晰
		if ($maininfo ['status_flag'] == 0) {
			// 订单处于正常流程态，可删除的订单只有未支付的或者已经完成的订单，因此
			if ($maininfo ['normal_status'] >= 1 && $maininfo ['normal_status'] < 3) {
				// 处于已支付并且未签收的订单不能删除
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "无法删除已支付但未签收的订单！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 订单处于退款状态，可删除的订单只有处理完成退款的订单，只有4退款成功和5退款关闭才是退款完成
			if ($maininfo ['normal_status'] <= 3) {
				// 没有达到退款完成的订单不能删除
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "无法删除处于退款流程中的订单，请联系商家完成退款处理！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
		}
		
		// 未支付、已经签收完毕或退款完成的订单可以删除，通过验证后准备删除订单（顾客端删除）
		$deleteinfo = array (
				'cus_mark_del' => 1, // 顾客删除
		);
		$deleteresult = $maintable->where ( $ordermap )->save ( $deleteinfo ); // 顾客删除订单
		
		// 处理删除订单结果
		if ($deleteresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "删除失败，系统繁忙，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
	}
	
	/**
	 * 作者：万路康，2015/08/25 15:45:36初审。
	 * 顾客同意退款申请操作,移动端客户点击退款按钮触发，与后台商家处理操作大致相同
	 * 不同的是要加个退款按钮是否开启的判断，防止客户通关修改审查元素改变按钮的disable属性
	 */
	public function agreeRefund() {
		// 接收参数
		$orderid = I ( 'oid' ); // 要退款的订单编号
		$e_id = $this->einfo ['e_id'];
		
		// step1 判断退单条件
		// step1.1校验order_id是否为空
		if (! isset ( $orderid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您提交的订单号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// step1.2校验该商家的微信支付是否完备 secret_info
		$epayconfigmap = array (
				'e_id' => $e_id, // 当前商家
				'is_del' => 0, 
		);
		$wechatpayinfo = M ( 'secretinfo' )->where ( $epayconfigmap )->find (); // 找到商家的微信支付信息
		if (! $wechatpayinfo) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "退款操作失败，商家没有设置微信支付信息，请先联系商家进行完善！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// step1.3校验是否存在该order_id并且refund_status为1的记录
		$ordermap = array (
				'order_id' => $orderid,
				'refund_status' => 1,
				'id_del' => 0
		); // 这里的查询条件后面会再次用到
		$orderviewresult = M ( 'orderinfo_view' )->where ( $ordermap )->select (); // ****这里查找到的视图记录后面会用到****
		$orderlistcount = count ( $orderviewresult ); // 统计订单详情数量
		if (! $orderviewresult) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您所操作的订单的状态可能已发生改变，请刷新页面后重试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		$malltype = $orderviewresult [0] ['mall_type']; // 提取malltype方便使用
		$refundfee = $orderviewresult [0] ['pay_indeed'];
		
		// step1.4 校验移动端传入的退款金额是否合法
		if ($refundfee <= 0) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您的退款金额应该大于0！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		//step1.6 加入检测移动端退款按钮是否正常开启校验，校验是否该笔定单已经提交退款申请大于3天
		$currentTime = time ();
		$refundApplyTime = $orderviewresult [0] ['refund_apply_time'];
		if ($currentTime - $refundApplyTime < 60*24*3) {
			// 视图中每个order_id对应的pay_indeed都是相同的
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "退款操作失败，申请退款3天后，商家未响应申请才能申领退款！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// step2 退单执行，这步只需要改变ordermain表的退款状态，后续步骤将在成功退款后执行
		// 启动事务
		$orderMainTable = M ( "ordermain" ); // 实例化订单主表
		$orderMainTable->startTrans ();
		// 更新订单主表，因为这步操作是退单的必做操作，所以将它作为事务的第一个操作
		$ordersavedata = array (
				'refund_status' => 2,
				'refund_fee' => $refundfee
		);
		$orderresult = $orderMainTable->where ( $ordermap )->save ( $ordersavedata );
		if (! $orderresult) {
			$orderMainTable->rollback ();
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "退款操作失败，订单状态无法更改！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 修改商品sku库存，无论商品现在上架与否都要进行库存更新的操作
		$orderscore = 0; // 这笔订单得到的总分数
		$skutable = M ( 'productsizecolor' ); // 实例化sku表
		$subskutable = M ( 'subbranchsku' ); // 实例化分店sku表
		for($i = 0; $i < $orderlistcount; $i ++) {
			if ($malltype == 1 || $malltype == 3) {
				// 如果是云总店或者分销店，库存退入总库
				// Step1-1：循环准备总店每一条商品的退库存情况
				$skumap = array (
						'sizecolor_id' => $orderviewresult [$i] ['sku_id'], // 当前要退单的商品sku编号
						'is_del' => 0, // 没有被删除的sku
				);
				// 判断商品是否存在
				$exist = $skutable->where ( $skumap )->count ();
				if ($exist) {
					$skudata = array (
							'sell_amount' => array ( 'exp', 'sell_amount-' . $orderviewresult [$i] ['amount'] ), // 在sku表回退卖出量
					);
					$skuresult = $skutable->where ( $skumap )->save ( $skudata ); // 在sku表进行卖出量回退，触发product表也会库存回退
					if (! $skuresult) {
						$orderMainTable->rollback ();
						$this->ajaxresult ['errCode'] = 10008;
						$this->ajaxresult ['errMsg'] = "退款操作失败，商品库存回退失败！";
						$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
					}
				}
			} else if ($malltype == 2) {
				// 如果是微猫商城，库存退入分店库存里
				// Step1-1：循环准备微猫商城每一条商品的退库存情况
				$skumap = array (
						'sub_sku_id' => $orderviewresult [$i] ['sku_id'], // 当前要退单的商品sku编号
						'is_del' => 0, // 没有被删除的sku
				);
				// 判断商品是否存在
				$exist = $subskutable->where ( $skumap )->count ();
				if ($exist) {
					$skudata = array (
							'sell_amount' => array ( 'exp', 'sell_amount-' . $orderviewresult [$i] ['amount'] ), // 在sku表回退卖出量
					);
					$skuresult = $subskutable->where ( $skumap )->save ( $skudata ); // 在sku表进行卖出量回退，触发product表也会库存回退
					if (! $skuresult) {
						$orderMainTable->rollback ();
						$this->ajaxresult ['errCode'] = 10009;
						$this->ajaxresult ['errMsg'] = "退款操作失败，商品库存回退失败！";
						$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
					}
				}
			}
			
			// Step2：叠加每条订单商品获得的积分
			$orderscore += $orderviewresult [$i] ['get_score'] * $orderviewresult [$i] ['amount']; // 总分叠加积分×数量
		}
		// 如果有积分要退，则插入一条扣除相应总积分的记录
		if ($orderscore) {
			$scorerollback = array (
					'score_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $e_id,
					'customer_id' => $orderviewresult [0] ['customer_id'], // 每个order_id对应的customer_id是相同的
					'mall_type' => $malltype, // 根据不同的商城类别
					'subbranch_id' => - 1,
					'distributor_id' => - 1,
					'change_time' => time (),
					'change_amount' => - $orderscore, // 注意积分是负的~！！！
					'remark' => "顾客在" . timetodate ( time () ) . "退单，扣除该单相应积分" . $orderscore . "分。",
			);
			$scorerollbackresult = M ( 'customerscore' )->add ( $scorerollback );
			if (! $scorerollbackresult) {
				$orderMainTable->rollback ();
				$this->ajaxresult ['errCode'] = 10010;
				$this->ajaxresult ['errMsg'] = "退款操作失败，积分信息添加失败！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		
		// 修改导购提成状态表，将该笔定单对应的导购提成状态改为已取消
		$guideprostatusmap = array(
				'order_id' => $orderid,
				'is_del' => 0
		);
		$guideprostatustable = M('guideprofitstatus');
		$guideprostatusresult = $guideprostatustable ->where($guideprostatusmap)->setField('profit_status', -1);
		if(!$guideprostatusresult){
			$orderMainTable->rollback ();
			$this->ajaxresult ['errCode'] = 10011;
			$this->ajaxresult ['errMsg'] = "退款操作失败，导购提成状态更新失败！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 将订单状态改变记录存入orderstatusrecord表
		$orderstatusrecorddata = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $e_id,
				'mall_type' => $malltype,
				'order_id' => $orderid,
				'status_flag' => 1,
				'normal_status' => $orderviewresult [0]['normal_status'],
				'refund_status' => 2,
				'is_read' => 0,
				'add_time' => time ()
		);
		$orderstatusrecordresult = M ( 'orderstatusrecord' )->add ( $orderstatusrecorddata );
		if (! $orderstatusrecordresult) {
			$orderMainTable->rollback ();
			$this->ajaxresult ['errCode'] = 10012;
			$this->ajaxresult ['errMsg'] = "退款操作失败，退款状态记录表添加失败！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		$order_id = $orderid; // 给出要退的订单编号
		$refundmoney = $refundfee; // 给出要退款的金额（元）
		
		$weactrefund = new WeActRefund ( $e_id ); // 创建退款类对象
		$refundresult = $weactrefund->orderRefund ( $order_id, $refundmoney ); // 为某笔订单退款多少钱
		// p($refundresult);die;
		if ($refundresult ['data'] ['resultCode'] == 'SUCCESS') {
			// 该处取data下的resultCode进行判断，而非errCode==0进行判断，表示退款成功
			$orderMainTable->commit ();
			$this->ajaxresult ['errCode'] = 0;
			//$this->ajaxresult ['errMsg'] = "退款操作成功，微信将在24小时之内将金额转入客户账户！";
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$orderMainTable->rollback ();
			$this->ajaxresult ['errCode'] = 10013;
			$this->ajaxresult ['errMsg'] = "退款失败，" . $refundresult ['errMsg'];
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
}
?>