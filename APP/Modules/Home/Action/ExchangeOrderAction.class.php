<?php
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 				// 载入微动微信APISDK类
import ( 'Class.API.MessageVerify.mobileMessage', APP_PATH, '.php' ); 				// 载入短信通知模板类
/**
 * 积分商城订单ajax请求控制器（mall_type为5）
 * @author 胡睿，万路康
 * CreateTime:2015/09/15 12:13:36
*/
class ExchangeOrderAction extends MobileLoginAction {

	/**
	 * ==========PART1：顾客下单部分==========
	 */

	/**
	 * 用户提交生成订单的ajax处理函数(mall_type为5)
	 * 快递费用方面，数据库里无法保留使用EMS、平邮、快递的信息，到时候如何选取..
	 */
	public function orderConfirm() {
		//$this->ajaxresult ['errCode'] = 0;
		//$this->ajaxresult ['errMsg'] = "ok";
		//$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		
		$shoppinglist = stripslashes ( $_REQUEST ['shoppingListJson'] );
		$orderproductlist = json_decode ( $shoppinglist, true );
		//p(I());p($orderproductlist);die;
		
		$rule_id = $orderproductlist[0]['rule_id'];	// 接收该换购产品对应的换购规则
		$pro_level = $orderproductlist[0]['member_level'];	// 接收该换购产品所需的等级
		$pro_score = $orderproductlist[0]['score_amount'];	// 接收该换购产品所对应的换购积分
		$buy_amount = $orderproductlist[0]['amount'];	// 此次换购的数量
		$pro_id = $orderproductlist[0]['product_id'];	// 接收该换购产品对应的商品sku编号 
		$pro_sku = $orderproductlist[0]['skuId'];	// 此次换购的数量
		//p($orderproductlist);die;
		// 接收参数并查数据库，分页读取数据
		$e_id = $this->einfo ['e_id'];
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 获取当前微信用户的openid
		/* $rule_id = I('rule_id','');		// 接收该换购产品对应的换购规则
		$pro_level = I('prolevel',0);	// 接收该换购产品所需的等级
		$pro_score = I('proscore',0);   // 接收该换购产品所对应的换购积分
		$pro_id = I('proid',0); 		// 接收该换购产品对应的商品ID
		$pro_sku = I('prosku',0); 		// 接收该换购产品对应的商品sku编号 
		$buy_amount = I('buyamount',1);	// 此次换购的数量 */
		
		// 取货信息
		$logistics = I ( 'sendType', 0 ); 									// 配送方式，默认快递配送
		$delivery_id = I ( 'deliveryId' ); 								// 如果是快递配送，该字段不能为空，收货人配送信息主键
		$selfget_name = I ( 'receiveName' ); 								// 到店自提的姓名
		$selfget_mobile = I ( 'receiveCellphone' ); 								// 到店自提人手机
		
		/* * 以下为为了应对积分+钱的兑换方式预留的相关信息,还包括可能有积分商城购物车
		 * $expressfee = I('expressfee',0);										// 快递费用,当前暂定0块钱，目前积分商城都是包邮的
		 * $pay_indeed = I ( 'payment',0 ); 									// 用户实际支付的价格(订单加邮费的价格)
		 * $pay_method = I ( 'payType', 2 ); 									// 支付类型默认是2，（微信支付）
		 * $shoppingcartlist = stripslashes ( $_REQUEST ['shoppingListJson'] ); 	// 提交订单的购物车信息（这里是字符串json格式）
		 * $orderproductlist = json_decode ( $shoppingcartlist, true );
		 * 
		 */
		
		/* * 对积分商城的订单进行检测
		 *  1)检测会员等级,积分数是否相符（会员等级>=商品换购等级，积分数>= 本次兑换所需的积分数）
		 *  2)检测支付积分是否发生变动（ 支付的积分必须>0且必须等于对应规则的积分）
		 *  3)检测配送方式与收货地址的完备性是否符合要求
		 *  4)检测换购的数量是否在库存sku允许范围内
		 */
		// 1)检测会员等级是否符合要求（会员等级>=商品换购等级 且积分数也足够）
		$cusinfomap = array(
			'customer_id'=>$customer_id,
				'is_del'=>0	
		);
		$cusfind = M("customerinfo")->where($cusinfomap)->find();
		if( !$cusfind)	{	// 如果不存在
			$this->ajaxresult['errCode'] = 10001;
			$this->ajaxresult['errMsg'] = "订单提交失败，无法获取会员等级信息";
			$this->ajaxReturn( $this->ajaxresult);	// 返回给前端ajax信息
		}
		else {
			$member_level = $cusfind['member_level'];
			$total_score = $cusfind['total_score'];
			if( ( $member_level < $pro_level) || ( $total_score < ( $pro_score * $buy_amount)) ) {	// 如果顾客等级小于商品换购等级或者顾客总积分小于商品所需总积分
				$this->ajaxresult['errCode'] = 10002;
				$this->ajaxresult['errMsg'] = "订单提交失败，会员等级不够或者积分不足！";
				$this->ajaxReturn( $this->ajaxresult);	// 返回给前端ajax信息
			}
		}

		// 2)检测商品兑换积分是否发生变动（ 支付的积分必须>0且必须等于对应规则的积分）
		if ( $pro_score <= 0) {	// 所支付的积分必须>0
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "订单提交失败,所需积分兑换金额必须大于0！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		else {	// 商品兑换积分必须等于对应规则的积分
			$rulemap = array(
				'rule_id'=>$rule_id,
				'e_id'=>$e_id,
				'product_id'=>$pro_id,
				'member_level'=>$pro_level,
				'score_amount'=>$pro_score,
				'is_use'=>1,			
				'is_del'=>0
			);
			$rulefind = M("productexchangerule")->where($rulemap)->find();
			//p(M('productexchangerule')->getLastSql());die;
			if( !$rulefind) {	// 如果没找到的话
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "订单提交失败,积分兑换规则已经改变,请重新下单！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}

		// 3)检测配送方式和收货地址完备性
		if ($logistics != 0 && $logistics != 1) {	// 检测配送方式的正确性
			// 目前物流只支持邮费和到店自提，邮费配送为0，到店自提为1
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "订单提交失败，配送方式只能为邮费快递或到店自提！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		$receive_name = ""; 	// 全局变量：收货人姓名
		$receive_mobile = ""; 	// 全局变量：收货人手机
		$receive_address = ""; 	// 全局变量：收货人地址
		if ($logistics == 0) {
			// 如果是邮费快递配送，根据配送信息主键查询配送信息表
			if (empty ( $delivery_id )) {
				$this->ajaxresult ['errCode'] = 10006;
				$this->ajaxresult ['errMsg'] = "订单提交失败，邮费配送方式收货人信息不能为空！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			$deliverymap = array (
					'deliveryinfo_id' => $delivery_id, 	// 接收到的快递编号
					'e_id' => $e_id, 				// 当前商家
					'customer_id' => $customer_id, 		// 当前顾客
					'is_del' => 0, 						// 没有被删除的
			);
			$deliveryinfo = M ( 'deliveryinfo' )->where ( $deliverymap )->find (); // 根据快递主键找到快递信息
			//p(M('deliveryinfo')->getLastSql());die;
			if (! $deliveryinfo) {
				// 如果所选择的快递信息已经被删除了
				$this->ajaxresult ['errCode'] = 10007;
				$this->ajaxresult ['errMsg'] = "订单提交失败，配送方式选择的收货人信息无效，请重新选择！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 如果通过检验，则把收货人姓名、电话和地址给过去
			$receive_name = $deliveryinfo ['receive_person']; 		// 全局变量：收货人姓名
			$receive_mobile = $deliveryinfo ['contact_number']; 	// 全局变量：收货人手机
			$receive_address = $deliveryinfo ['receive_address']; 	// 全局变量：收货人地址
		} else if ($logistics == 1) {
			// 如果是到店自提，直接把自提人姓名和电话给收货人
			if (empty ( $selfget_name ) || empty ( $selfget_mobile )) {
				$this->ajaxresult ['errCode'] = 10008;
				$this->ajaxresult ['errMsg'] = "订单提交失败，到店自提人的信息不完整！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			$receive_name = $selfget_name; 		// 全局变量：收货人姓名
			$receive_mobile = $selfget_mobile; 	// 全局变量：收货人手机
			$receive_address = "顾客到店自提"; 		// 地址是到店自提
		}

		// 4)检测换购的数量是否在库存sku允许范围内	
		$skumap = array(
			'sizecolor_id'=>$pro_sku,
				'is_del'=>0	
		);
		$skufind = M("productsizecolor")->where($skumap)->find();
		if( !$skufind) {	// 如果未找到对应的sku
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "订单提交失败，没有找到对应的款号信息,请重试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		else {	// 对能够买到的库存量进行校验
			$storage_amount = $skufind['storage_amount'];
			$sell_amount = $skufind['sell_amount'];
			$exist_amount = $storage_amount - $sell_amount;
			if( $buy_amount > $exist_amount) {	// 如果购买量超出了范围，直接回滚
				$this->ajaxresult ['errCode'] = 10010;
				$this->ajaxresult ['errMsg'] = "订单提交失败，所购商品库存数量不足，请修改数量后重新提交！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}

		/**
		 * 构造订单主表、子表、订单流水表的数据
		 */
		// 首先构造生成的订单主表数据
		$orderMainData = array (
				'order_id' => md5 ( uniqid ( rand (), true ) ), 			// 订单编号（主键）
				'e_id' => $e_id,											// 当前商家
				'mall_type' => 5, 											// 积分商城
				'visual_number' => time () . randCode ( 4, 1 ), 			// 产生一个可视化编号
				'customer_id' => $customer_id, 								// 当前顾客编号				
				'openid' => $openid, 										// 当前微信用户的openid				
				'order_time' => time (), 									// 下单时间
				'pay_time'=>time(),											// 支付时间			
				'total_price' =>0, 											// 兑换商品的价格(不包括任何打折条件的商品价格)
				'receive_person' => $receive_name, 							// 订单收货人姓名
				'receive_tel' => $receive_mobile, 							// 订单收货人手机
				'receive_address' => $receive_address, 						// 订单收货人地址
				'pay_method'=> 0,											// 支付方式（默认0表示未选择）
				'pay_indeed'=>0,											// 实际支付现金
				'score_pay'=>($pro_score*$buy_amount),						// 实际支付积分数目
				'express_fee' => 0,											// 快递费
				'logistics_method' => $logistics, 							// 物流方式
				'status_flag'=>0,											// 正常单
				'normal_status'=>1,											// 已付款
				'refund_status'=>0											// 未进入退单环节
		);
		// 接着构造订单子表数据		
		$orderDetailData = array (
				'detail_id' => md5 ( uniqid ( rand (), true ) ), 				// 订单子表
				'order_id' => $orderMainData ['order_id'], 						// （外键）主单的编号
				'product_id' => $pro_id,						 				// 商品编号
				'unit_price' => 0,									 			// 当前价格
				'unit_score'=> $pro_score,										// 产品积分								
				'amount' => $buy_amount,				 						// 当前购买数量
				'sku_id' => $pro_sku,						 					// sku主键				
				'pro_size' => $skufind['product_color'], 						// 当前sku的尺寸
				'pro_color' => $skufind['product_size'], 						// 当前sku的颜色				
				'get_score' =>0 												// 当前回赠的积分（单件商品积分乘以数量）
		);

		// 然后构造这笔订单的初始流水状态（刚下单，是一笔订单最初的流水状态）
		$orderStatusData = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 							// 流水表主键
				'e_id' => $e_id, 															// 当前商家
				'mall_type' => 5, 															// 微猫商城订单流水
				'order_id' => $orderMainData ['order_id'], 									// 这笔订单的主单编号
				'status_flag' => 0, 														// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 1, 														// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => 0, 														// 正常态的订单无退款异常（2.0版本新增订单流水状态）
				'add_time' => time (), 														// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "在积分商城" . "下单，记录订单流水!" 	// 订单流水原因
		);

		/** 启动事务
		 *  1) 订单主表、子表、流水表进行相关的改变
		 *	2) 库存的相关改变
		 *  3) 积分的相关改变
		 */  		
		$orderMainTable = M ( "ordermain" ); 								// 实例化订单主表
		$orderDetailTable = M ( "orderdetail" ); 							// 实例化订单子表
		$orderStatusTable = M ( "orderstatusrecord" ); 						// 实例化订单流水表
		$orderMainTable->startTrans ();

		// Step1:插入订单主表、子表和流水表
		$addmainresult = $orderMainTable->add ( $orderMainData ); 			// 插入订单主表
		$adddetailresult = $orderDetailTable->add( $orderDetailData ); 	// 插入订单子表
		$addstatusresult = $orderStatusTable->add ( $orderStatusData ); 	// 插入订单流水表
		if (! $addmainresult || ! $adddetailresult || ! $addstatusresult) {
			// 三个任何一个不成功就回滚，然后直接返回,否则继续往下
			$orderMainTable->rollback ();
			// 初始化事务失败返回的ajax结果
			$this->ajaxresult ['errCode'] = 10011;
			$this->ajaxresult ['errMsg'] = "订单提交失败，系统接收订单失败！请稍后重试";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}

		// Step2:改变对应总店相关商品库存
		$skutable = M ( 'productsizecolor' ); // 实例化分店商品表
		$updateskumap = array (
				'sizecolor_id' => $pro_sku, // 总店商品sku主键
				'is_del' => 0
		);
		$updateskures = $skutable->where ( $updateskumap )->setInc ( 'sell_amount', $buy_amount); // 卖出量增加
		if (! $updateskures) {
			// 一旦有一次失败，则回滚
			$orderMainTable->rollback ();
			// 初始化事务失败返回的ajax结果
			$this->ajaxresult ['errCode'] = 10012;
			$this->ajaxresult ['errMsg'] = "订单提交失败，库存信息回退失败！请稍后重试";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// Step3:往t_customerscore(顾客积分表)中插入一条记录，将顾客已经兑换的积分给扣除掉(已经有触发器，直接触发就好了)
		$cusscoretable = M('customerscore');	// 实例化客户积分表
		$cusScoreData = array(
			'score_id' => md5 ( uniqid ( rand (), true ) ), 									// 客户积分表主键
			'e_id' => $e_id, 																	// 当前商家
			'customer_id'=>$customer_id,														// 当前顾客				
			'mall_type' => 5, 																	// 积分商城
			'change_time'=> time(),																// 积分改变时间
			'change_amount'=> (-1)*($pro_score*$buy_amount),									// 插入负积分
			'change_reason'=>  "顾客在" . timetodate ( time () ) . "在积分商城" . "下单，扣除相应积分!", 	// 订单流水原因
			'is_del'=>0
		);
		$addcusscoreresult = $cusscoretable->add ( $cusScoreData ); 			// 插入客户积分表
		
		// Step4：事务成功提交，并返回给前台信息
		$orderMainTable->commit ();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] ['oid'] = $orderMainData ['order_id']; // 附上提交的订单编号

		// Step6：发送微信模板消息通知
		$domain = C ( 'DOMAIN' ); 	// 提取域名
		$fontcolor = "#DA70D6"; 	// 下单是淡紫色的字体颜色
		$tpldata = array (
				'visual_number' => $orderMainData ['visual_number'],
				'total_price' => $orderMainData ['score_pay'] . "积分",
				'receive_person' => $orderMainData ['receive_person'],
				'receive_tel' => $orderMainData ['receive_tel'],
				'receive_address' => $orderMainData ['receive_address'],
		);
		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->einfo ['e_id']; // 在云总店商城下单则跳转云总店商城的订单中心（2015/08/25 23:59:25）
		// 策略模式发送下单微信模板消息
		$ordernotify = new OrderNotify ( $tpldata, $url, $fontcolor ); // 下单通知
		$msgwechater = new MsgToWechater ( $ordernotify ); // 上下文类对象
		$sendresult = $msgwechater->sendMsgToWechater ( $this->einfo, $openid ); // 发送模板消息
		
		// Step7：发送短信通知
		$type = 'ORDER';
		$telNum = $orderMainData ['receive_tel'];
		$ename = $this->einfo ['e_name'];
		$orderNumber = $orderMainData ['visual_number'];
		$mobileMsg = new mobileMessage();
		$sendresult = $mobileMsg->sendMsgNotify ( $telNum, $type, $ename, $orderNumber ); // 发送消息
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}

	/**
	 * ==========PART2：积分商城顾客签收订单部分==========
	 */

	/**
	 * 积分商城签收订单， 用户确认订单已收货的ajax处理函数
	 */
	public function signExchangeReceiveOrder() {
		// 接收提交信息
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ), 			// 接收订单编号
		);
		$maintable = M ( 'ordermain' ); 			// 实例化订单主表
		$detailtable = M ( 'orderdetail' ); 		// 实例化订单子表

		// 查询出订单主单信息
		$ordermap = array (
				'order_id' => $ajaxinfo ['order_id'],
				'is_del' => 0,
		);
		$maininfo = $maintable->where ( $ordermap )->find (); // 找到这笔需要签收的订单的主单信息

		// 开始判断订单是否满足可以签收的条件（如下几种情况直接毙掉）
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "不存在的订单，无法签收！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		// 对订单不同流程分开判断逻辑更为清晰
		if ($maininfo ['status_flag'] == 0) {
			// 正常流程中，处于已付款|待发货状态，未发货不能签收
			if ($maininfo ['normal_status'] <= 1) {
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "商家还没有发货不能签收，请耐心等待或联系商家！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
			// 正常流程中，处于已签收状态，不能重新签收
			if ($maininfo ['normal_status'] >= 3) {
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "订单已经被签收，请不要重复提交！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 退款流程中的订单不能签收
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "订单处于退款流程中，无法签收！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}

		// 根据$order_id从t_orderdetail查询子订单信息
		$orderDetailMap = array (
				'order_id' => $ajaxinfo ['order_id'],
				'is_del' => 0
		);
		$orderDetailItem = $detailtable->where ( $orderDetailMap )->select ();
		if (! $orderDetailItem) {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "签收失败，订单没有任何商品信息，请及时刷新！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}

		// 初始化事务失败返回的ajax结果，并启动事务
		$this->ajaxresult ['errCode'] = 10007;
		$this->ajaxresult ['errMsg'] = "签收失败，系统繁忙，请稍后再试！";
			
		$statustable = M ( "orderstatusrecord" ); // 实例化订单流水表
		$statustable->startTrans ();
		
		$order_id = $ajaxinfo ['order_id'];
		$mall_type = $maininfo['mall_type'];
		$refundstatus = $maininfo['refund_status'];

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
				'mall_type' => $mall_type, 											// 表示积分商城的订单
				'order_id' => $order_id, 									// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 3, 										// 正常态3代表订单被签收（2.0版本新增订单流水状态）
				'refund_status' => $refundstatus, 							// 修改正常态流水，退款态沿用原来的值（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "签收订单，记录订单流水。" // 订单流水原因
		);
		$statusresult = $statustable->add ( $statusinfo ); 					// 增加一笔签收的订单流水

		// Step5：事务成功提交，并返回给前台信息
		$statustable->commit ();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
}
?>