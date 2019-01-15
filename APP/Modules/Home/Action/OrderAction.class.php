<?php
/**
 * 总店订单控制器。
 * 现已改版成订单流水状态，2015/08/19 15:45:36.
 * @author 赵臣升。
 * CreateTime:2015/06/11 20:58:36.
 */
class OrderAction extends MobileLoginAction {
	
	/**
	 * =========预订单部分=========
	 */
	
	/**
	 * 新版预订单页面。
	 */
	public function preOrder() {
		$this->fastbuy = 0; 	// 立即购买方式
		$this->fastbuyinfo = ""; // 空json
		
		// 处理预订单页面是否经由微信授权跳转
		$fastbuy = I ( 'fastbuy' ); // 是否立即购买的结算预订单
		if ($fastbuy || ! empty ( $_SESSION ['fastbuyinfo'] [$this->einfo ['e_id']] )) {
			if (! empty ( $_SESSION ['fastbuyinfo'] [$this->einfo ['e_id']] )) {
				$this->fastbuy = 1; 	// 立即购买方式
				$this->fastbuyinfo = $_SESSION ['fastbuyinfo'] [$this->einfo ['e_id']]; // 从浏览器缓存中取出快速购买信息
			}
		} 
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	/**
	 * 新版积分换购预订单页面。
	 */
	public function preOrderScore() {
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
	
		$this->display ();
	}
	
	/**
	 * =========订单详情部分=========
	 */
	
	/**
	 * 订单详情视图页面。
	 * 已按照最新订单流水状态进行过更改，2015/08/18 15:41:36.
	 */
	public function orderInfo() {
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$order_id = I ( 'oid' ); // 接收订单编号
		if (empty ( $order_id )) {
			$this->error ( "订单编号错误！" ); // 没有传订单编号
		}
		
		// 查询订单信息
		$ordermap = array (
				'order_id' => $order_id, 		// 订单编号
				'customer_id' => $customer_id, 	// 当前顾客的
				'cus_mark_del' => 0, 			// 顾客没删除的
				'is_del' => 0, 					// 系统里没删除的
		);
		$orderlistinfo = M ( 'orderinfo_view' )->where ( $ordermap )->select (); // 查找订单信息
		if (! $orderlistinfo) {
			$this->error ( "您要查看的订单信息已经不存在！" ); // 没有查询到相关订单
		}
		
		// 对订单数据进行处理
		$detailcount = count ( $orderlistinfo ); // 统计该笔订单买了多少种商品
		for($i = 0; $i < $detailcount; $i ++) {
			$orderlistinfo [$i] ['order_time'] = timetodate ( $orderlistinfo [$i] ['order_time'] );
			$orderlistinfo [$i] ['macro_path'] = assemblepath ( $orderlistinfo [$i] ['macro_path'] );
			$orderlistinfo [$i] ['micro_path'] = assemblepath ( $orderlistinfo [$i] ['micro_path'] );
		}
		
		// 推送订单信息到前台
		$orderjsondata ['orderproductlist'] = $orderlistinfo; 	// 订单数据
		$this->orderlistjson = jsencode ( $orderjsondata ); 	// 推送json数据
		$this->oinfo = $orderlistinfo; 							// 推送订单原来的数据
		$this->oid = $order_id; 								// 推送订单编号
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 推送微信用户编号
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	/**
	 * =========我的订单与分页读取数据部分=========
	 */
	
	/**
	 * 我的订单视图页面。
	 */
	public function myOrder() {
		// 原版的检查微信支付是否成功（现在在通知中心已经处理一次了，因为要插入流水，所以这里不便重复检测和插入）
		/* $checkwxpay = $_REQUEST ['checkwxpay']; // 尝试接收checkwxpay字段信息（如果有），该字段的信息是：是否是微信支付回跳
		$checkorder = $_REQUEST ['wxpayoid']; 	// 尝试接收wxpayoid字段信息（如果有），该字段信息是：如果微信支付，则有一笔微信支付的订单编号
		if (! empty ( $checkwxpay )) $this->checkWeChatPayStatus ( $checkorder ); // 如果是微信支付回跳，则直接调用检查微信支付成功是否成功 */
		
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		
		// 进入页面，默认加载全部订单
		$nextstart = 0; 		// 页面初始化时数据开始处（默认从头开始）
		$perpage = 10; 			// 每页10条
		$querytype = 0; 		// 查询全部订单（0页卡是全部订单）
		
		// 初始化订单信息
		$orderlist = $this->getOrderListByPage ( $this->einfo ['e_id'], $customer_id, $nextstart, $perpage, $querytype, true ); // 初始化查询当前顾客在该企业下的订单数据
		$this->orderlistjson = jsencode ( $orderlist ); // 压缩成json信息
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 推送微信用户编号
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	/**
	 * ajax请求订单信息处理函数。
	 */
	public function queryOrder() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		// 设置请求参数
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$nextstart = I ( 'nextstart', 0 ); 		// 本次请求下一页开始的位置
		$perpage = I ( 'perpage', 10 ); 		// 默认每页10条订单数据
		$queryordertype = I ( 'querytype', 0 ); // 查询订单种类，0为全部订单，1为待付款，2为已付款|待发货，3为已发货|待收货，4为已收货|待评价，5为已评价，6为已关闭
		
		// 分页查询商品
		$finalresult = $this->getOrderListByPage ( $this->einfo ['e_id'], $customer_id, $nextstart, $perpage, $queryordertype ); // 分页读取订单信息
		$this->ajaxReturn ( $finalresult ); 	// ajax返回给前台
	}
	
	/**
	 * 分页读取订单主单信息函数。
	 * 已按照最新订单流水状态进行过更改，2015/08/18 15:41:36.
	 * 
	 * 判断查询订单条件：0为全部订单，1为待付款，2为待收货，3为待评价，4为已关闭。
	 * @param string $eid 商家编号
	 * @param string $customer_id 顾客编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param number $queryordertype 要查询的订单类型
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getOrderListByPage($eid = '', $customer_id = '', $nextstart = 0, $perpage = 10, $queryordertype = 0, $firstinit = FALSE) {
		$ordermain = M ( 'ordermain' ); 			// 订单主表（用来统计数量和拉去主键的）
		$orderview = M ( 'orderinfo_view' ); 		// 实例化表结构或视图结构
		$orderby = "order_time desc"; 				// 定义要排序的方式（每个表都不一样）
		$orderlist = array (); 						// 本次请求的数据
		$orderbucket = array (); 					// 订单桶子
		
		// Step1：定义查询条件并计算总数量（不需要限制分店数量）
		$querymap = array (
				'e_id' => $eid, 					// 当前商家下
				'customer_id' => $customer_id, 		// 当前顾客
				'cus_mark_del' => 0, 				// 没有被顾客删除的（顾客删单了就无法看到了）！！！特别注意，一定要带上这个条件
				'is_del' => 0, 						// 没有被删除的
		);
		
		// 判断查询订单条件：0为全部订单，1为待付款，2为已付款|待发货，3为已发货|待收货，4为已收货|待评价，5为已评价，6为已关闭
		if ($queryordertype == 1) {
			// 待付款
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 0; 	// 已下单|待付款
		} else if ($queryordertype == 2) {
			// 已付款|待发货，但是商家没有发货的
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 1; 	// 已付款|待发货
		} else if ($queryordertype == 3) {
			// 已发货，待收货
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 2; 	// 已发货|待收货
		} else if ($queryordertype == 4) {
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 3; 	// 已收货|待评价
		} else if ($queryordertype == 6) {
			// 额外（原标签卡代码，如果要用到的话，这里包含已完成和已结束）2015/08/18 20:38:26备注
			// 已关闭订单包括：交易完成|已评价、付款超时|发货超时关闭、退单退款一致|已退款4种
			$querymap ['_string'] = "(status_flag = 0 and normal_status <= 0) or (status_flag = 0 and normal_status >= 3) or (status_flag = 1 and refund_status >= 4)";
		}
		
		$totalcount = $ordermain->where ( $querymap )->count (); 				// 计算订单主表总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 								// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0;			 						// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$ordermainlist = $ordermain->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询订单主单信息
				
			$querynum = count ( $ordermainlist ); // 统计本次查询一共得到几条订单（数据库层返回的记录）
			
			$timenow = time (); 		// 取当前时间
			$orderidlist = array (); 	// 准备拼接在一起的订单主键数组
			for($i =0; $i < $querynum; $i ++) {
				// 特别备注：2015/08/25 20:52:36 增加对订单申请退款状态的判别，如果申请退款已经超过3天，则将该笔订单置为可以被顾客强制退款状态！！！
				if ($ordermainlist [$i] ['status_flag'] == 1 && $ordermainlist [$i] ['refund_status'] == 1 && ( $timenow - $ordermainlist [$i] ['refund_apply_time'] > 3 * 24 * 3600 )) {
					$ordermainlist [$i] ['compel_refund'] = 1; // 这笔订单可以被强制退款
				} else {
					$ordermainlist [$i] ['compel_refund'] = 0; // 这笔订单并不能被强制退款
				}
				// 继续原来的代码2015/08/25 20:52:36 以前
				array_push ( $orderidlist, $ordermainlist [$i] ['order_id'] );
				$ordermainlist [$i] ['detailinfo'] = array (); // 预先为子单开辟数组空间（为array_push准备）
				$orderbucket [$ordermainlist [$i] ['order_id']] = $ordermainlist [$i]; // 为每条订单开辟一个数组（主键一定不会重复，不判重了）
			}
			$orderidstring = implode ( ",", $orderidlist ); // 拼接order_id字符串
				
			$querymap ['order_id'] = array ( "in", $orderidstring ); // 增加订单主键的限制，用SQL IN查询
			$orderlist = $orderview->where ( $querymap )->order ( $orderby )->select (); // 查询出订单全信息（按子表一条记录算一条）
			
			$detailcount = count ( $orderlist ); // 计算处理子单的循环数量
			// 先进行信息的格式化，可能需要的格式化信息（转换时间或路径等）；再进行数组桶子的循环打包
			for($i = 0; $i < $detailcount; $i ++) {
				$orderlist [$i] ['order_time'] = timetodate ( $orderlist [$i] ['order_time'] );
				$orderlist [$i] ['macro_path'] = assemblepath ( $orderlist [$i] ['macro_path'] );
				$orderlist [$i] ['micro_path'] = assemblepath ( $orderlist [$i] ['micro_path'] );
				
				array_push ( $orderbucket [$orderlist [$i] ['order_id']] ['detailinfo'], $orderlist [$i] ); // 将一条子订单信息压入主订单信息中
			}
			// 因为子单是一起处理的，所以这里要对主单再单独count一次（这里用到了引用，小心出错）
			foreach($orderbucket as &$bucketvalue) {
				$bucketvalue ['detailcount'] = count ( $bucketvalue ['detailinfo'] ); // 再打上标记方便前台处理
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'orderlist' => $orderbucket
				),
				'nextstart' => $newnextstart, 	// 下一页订单开始位置
				'totalcount' => $totalcount, 	// 总的订单数量
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * =========订单支付成功部分=========
	 */
	
	/**
	 * 订单支付成功视图页面。
	 */
	public function orderSuccess() {
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	/**
	 * =========微信支付回调检查订单=========
	 */
	
	/**
	 * 检查一笔订单微信支付的结果。
	 * @param string $order_id 订单编号
	 * 已按照最新订单流水状态进行过更改，2015/08/18 15:41:36.
	 */
	public function checkwechatPayStatus($order_id = '') {
		$payresult = $this->checkPaySuccessNotify ( $order_id );
		if ($payresult) {
			$ordertable = M ( 'ordermain' ); // 订单主表
			$statustable = M ( 'orderstatusrecord' ); // 订单流水表
			$ordertable->startTrans (); // 启动事务
			
			// Step1如果支付成功，更改订单状态，
			$ordermap = array (
					'order_id' => $order_id,
					'is_del' => 0
			);
			$paydata = array (
					'status_flag' => 0, 
					'normal_status' => 1, 
					'pay_time' => time (), 
			);
			$updatepayresult = $ordertable->where ( $ordermap )->save ( $paydata ); // 更新支付成功的状态
			
			// Step2在订单流水里记录一笔订单的改变状态
			// 构造这笔订单的支付流水状态
			$paystastus = array (
					'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
					'e_id' => $this->einfo ['e_id'], 							// 当前商家
					'mall_type' => 1, 											// 云总店订单流水
					'order_id' => $order_id, 									// 这笔订单的主单编号
					'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
					'normal_status' => 1, 										// 正常态1代表订单已支付（2.0版本新增订单流水状态）
					'refund_status' => 0, 										// 正常态的订单无退款异常（2.0版本新增订单流水状态）
					'add_time' => time (), 										// 订单流水添加的时间
					'remark' => "顾客在" . timetodate ( time () ) . "成功使用微信支付该订单，记录订单流水。", // 订单流水原因
			);
			$payrecordresult = $statustable->add ( $paystastus ); // 记录支付订单流水
			
			// Step3处理支付检测结果
			if ($updatepayresult && $payrecordresult) {
				$ordertable->commit (); // 提交支付成功事务
			} else {
				$ordertable->rollback (); // 回撤支付成功处理事务
			}
		}
	}
	
	/**
	 * 检查通知结果。
	 * @param string $order_id 订单编号
	 * @return boolean $wxpaysuccess 检查微信支付通知结果
	 */
	public function checkPaySuccessNotify($order_id = '') {
		$wxpaysuccess = false;
		if (! empty ( $order_id )) {
			$checkpay = array (
					'out_trade_no' => $order_id, // 该笔订单
					'return_code' => 'SUCCESS', // 返回码成功
					'result_code' => 'SUCCESS', // 业务结果成功
					'is_del' => 0
			);
			$wxpaysuccess = M ( 'wechatpaynotify' )->where ( $checkpay )->find (); // 查找支付成功的订单
		}
		return $wxpaysuccess;
	}
	
}
?>