<?php
import ( 'Class.API.WeChatPayV3.WeActWxPay.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动微信支付安全类
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 				// 载入微动微信APISDK类
import ( 'Class.API.MessageVerify.mobileMessage', APP_PATH, '.php' ); 				// 载入短信通知模板类

class WechatMicropayRequestAction extends PCRequestLoginAction {
	
	/**
	 * 查询商品sku信息。
	 */
	public function querySkuInfo() {
		$product_id = I ( "pid" );
		
		$skulist = array (); // 要查询的sku列表信息
		$skuview = M ( 'product_sku' ); // 实例化商品sku视图
		
		$productmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => $product_id, 	// 这件商品的
				'on_shelf' => 1, 				// 上架中的商品
				'is_del' => 0
		);
		$skucount = $skuview->where ( $productmap )->count (); // 统计sku数量
		if ($skucount) {
			$skulist = $skuview->where ( $productmap )->order ( "add_time desc" )->select (); // 查询出这些SKU
			for($i = 0; $i < $skucount; $i ++) {
				$skulist [$i] ['micro_path'] = assemblepath ( $skulist [$i] ['micro_path'], true );
				$skulist [$i] ['macro_path'] = assemblepath ( $skulist [$i] ['macro_path'], true );
			}
		}
		
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = 0;
		$this->ajaxresult ['data'] = array (
				'skulist' => $skulist, 
		);
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 刷卡订单提交。
	 */
	public function micropayConfirm() {
		$einfo = $_SESSION ['curEnterprise'];
		$eid = $_SESSION ['curEnterprise'] ['e_id'];
		
		// 接收参数
		$receive_person = I ( 'person' ); // 收货人姓名
		$receive_tel = I ( 'tel' ); // 收货人电话
		$authcode = I ( 'authcode' ); // 刷卡条码
		$orderjson = stripslashes ( $_REQUEST ['orderlist'] ); // 接收提交订单的商品信息（这里是字符串json格式）
		$orderlist = json_decode ( $orderjson, true );
		
		// 解包商品参数
		$listcount = count ( $orderlist ); // 统计下单商品数量
		$productlist = array (); // 下单商品列表
		for($i = 0; $i < $listcount; $i ++) {
			$productlist [$i] = json_decode ( $orderlist [$i], true );
		}
		//p($productlist);die;
		// 重查商品
		//$buylist = array (); // 刷卡等待购买商品
		
		// 计算总价
		$totalpricenow = 0;
		for($i = 0; $i < $listcount; $i ++) {
			$totalpricenow = $productlist [$i] ['price'] * $productlist [$i] ['amount'];
		}
		
		// 构造订单信息
		
		// 构造主单信息
		$ordermain = array (
				'order_id' => md5 ( uniqid ( rand (), true ) ), 			// 订单编号（主键）
				'e_id' => $eid, 											// 当前商家
				'mall_type' => 1, 											// 商城类型是云总店（没有分店、导购或分销商编号）
				'guide_id' => "-1", 										// 商城类型是云总店（没有导购编号，置为-1）
				'visual_number' => time () . randCode ( 4, 1 ), 			// 产生一个可视化编号
				'customer_id' => "-1", 										// 刷卡支付，顾客编号为-1
				'openid' => "-1", 											// 刷卡支付，微信用户openid为-1
				'order_time' => time (), 									// 下单时间
				'total_price' => $totalpricenow, 							// 购物车最新的价格(不包括任何打折条件的商品价格)
				'receive_person' => $receive_person, 						// 订单收货人姓名
				'receive_tel' => $receive_tel, 								// 订单收货人手机
				'receive_address' => "顾客到店刷卡自提", 							// 订单收货人地址
				'express_fee' => 0,											// 到店自提没有快递费
				'special_remark' => "", 									// 顾客的特殊备注（目前没有开放）
				'receive_status' => 0, 										// 订单接收状态（刚提交的订单还没有被商家接收）
				'pay_method' => 3, 											// 订单的支付方式，3是刷卡支付
				'pay_indeed' => $totalpricenow, 							// 实际支付(订单折后价格加邮费，不用再额外加邮费)
				'logistics_method' => 1, 									// 物流方式，到店刷卡自提方式
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 0, 										// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
		);
		
		// 构造子单信息
		$orderdetail = array ();
		for($i = 0; $i < $listcount; $i ++) {
			// 云总店不计算导购提成cutprofit_type和cutprofit_amount直接默认为0
			$orderdetail [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ), 		// 订单子表
					'order_id' => $ordermain ['order_id'], 					// （外键）主单的编号
					'product_id' => $productlist [$i] ['pid'], 		// 商品编号
					'unit_price' => $productlist [$i] ['price'], 		// 当前价格
					'amount' => $productlist [$i] ['amount'], 				// 当前购买数量
					'sku_id' => $productlist [$i] ['skuid'], 				// 云总店的sku主键
					'pro_size' => $productlist [$i] ['size'], 		// 当前sku的尺寸
					'pro_color' => $productlist [$i] ['color'], 		// 当前sku的颜色
					//'get_score' => $ordermain [$i] ['present_score'] * $buylistinfo [$i] ['amount'], 	// 当前回赠的积分（单件商品积分乘以数量）
					'get_score' => 0, 	// 当前回赠的积分（单件商品积分乘以数量）
					'cutprofit_type' => 0, 									// 云总店无导购提成方式
					'cutprofit_amount' => 0, 								// 云总店无导购提成金额
			);
		}
		
		// 构造流水信息
		$orderstatus = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $eid, 											// 当前商家
				'mall_type' => 1, 											// 云总店订单流水
				'order_id' => $ordermain ['order_id'], 						// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 0, 										// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "在云总店下单等待刷卡，记录订单流水。", // 订单流水原因
		);
		
		// 开启事务过程下单
		$maintable = M ( "ordermain" );
		$detailtable = M ( "orderdetail" );
		$statustable = M ( "orderstatusrecord" );
		
		$maintable->startTrans (); // 开启事务
		
		$mainresult = $maintable->add ( $ordermain );
		$detailresult = $detailtable->addAll ( $orderdetail );
		$statusresult = $statustable->add ( $orderstatus );
		
		// 处理三表插入结果
		if (! $mainresult || ! $detailresult || ! $statusresult) {
			$maintable->rollback ();
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "下单失败，订单系统繁忙。";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
		}
		// 插入成功则提交刷卡支付
		$oid = $ordermain ['order_id'];
		
		$weactmicropay = new WeActMicroPay ( $eid ); 
		$payresult = $weactmicropay->cardMicropay ( $oid, $authcode ); // 调用结果
		if ($payresult) {
			// 刷卡支付成功，回写订单openid和customer_id
			$updateorder = array (
					'openid' => $payresult ['openid'], // 提取刷卡支付的openid
					'normal_status' => 1, // 刷卡支付成功，更新订单状态
					'pay_time' => time (), // 刷新支付时间
			); // 回写信息
			$customermap = array (
					'e_id' => $eid,
					'openid' => $updateorder ['openid'],
					'is_del' => 0
			);
			$customerinfo = M ( 'customerinfo' )->where ( $customermap )->find ();
			if ($customerinfo) {
				// 如果有顾客编号
				$updateorder ['customer_id'] = $customerinfo ['customer_id'];
			} else {
				$newcustomer_id = md5 ( uniqid ( rand (), true ) ); // 新生成一个编号
				$updateorder ['customer_id'] = $newcustomer_id;
				// 系统代注册
			}
			
			// 回写订单系统状态
			$ordermap = array (
					'order_id' => $oid, 
					'e_id' => $eid, 
					'is_del' => 0,
			);
			$payupdateresult = $maintable->where ( $ordermap )->save ( $updateorder ); // 更新订单支付状态
			
			// 回写订单系统流水
			$cardpaystatus = array (
					'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
					'e_id' => $eid, 											// 当前商家
					'mall_type' => 1, 											// 云总店订单流水
					'order_id' => $ordermain ['order_id'], 						// 这笔订单的主单编号
					'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
					'normal_status' => 1, 										// 正常态1代表订单已付款（2.0版本新增订单流水状态）
					'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
					'add_time' => time (), 										// 订单流水添加的时间
					'remark' => "顾客在" . timetodate ( time () ) . "在云总店下单后刷卡付款成功，记录订单流水。", // 订单流水原因
			);
			$payrecordresult = $statustable->add ( $cardpaystatus ); 				// 记录刷卡成功
			//p($ordermap);p($updateorder);p($payupdateresult);p($payrecordresult);die;
			// 处理回写结果
			if (! $payupdateresult || ! $payrecordresult) {
				$maintable->rollback ();
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "订单系统回写失败！";
			} 
			
			// 整个事务过程成功
			$maintable->commit ();
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			
			// Step6：发送微信模板消息通知
			$domain = C ( 'DOMAIN' ); 	// 提取域名
			$fontcolor = "#DA70D6"; 	// 下单是淡紫色的字体颜色
			$tpldata = array (
					'visual_number' => $ordermain ['visual_number'],
					'total_price' => $ordermain ['total_price'] . "元",
					'receive_person' => $ordermain ['receive_person'],
					'receive_tel' => $ordermain ['receive_tel'],
					'receive_address' => $ordermain ['receive_address'],
			);
			$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $eid; // 在云总店商城下单则跳转云总店商城的订单中心（2015/08/25 23:59:25）
			// 策略模式发送下单微信模板消息
			$ordernotify = new OrderNotify ( $tpldata, $url, $fontcolor ); // 下单通知
			$msgwechater = new MsgToWechater ( $ordernotify ); // 上下文类对象
			$sendresult = $msgwechater->sendMsgToWechater ( $einfo, $openid ); // 发送模板消息
			
			// Step7：发送短信通知
			$type = 'ORDER';
			$telNum = $ordermain ['receive_tel'];
			$ename = $einfo ['e_name'];
			$orderNumber = $ordermain ['visual_number'];
			$mobileMsg = new mobileMessage();
			$sendresult = $mobileMsg->sendMsgNotify ( $telNum, $type, $ename, $orderNumber ); // 发送消息
			
		} else {
			$maintable->rollback ();
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "下单刷卡失败，" . $weactmicropay->getError ();
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
}
?>