<?php
/**
 * 订单管理ajax请求控制器。
 * @author 王健。
 * @modify author 赵臣升。
 * @secondmodify author 胡福玲。
 */
class OrderManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyUI获取所有订单主表信息。
	 */
	public function getAllMainOrder() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
	
		$ordermap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 获取当前商家id，以便显示当前商家的客户
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
		$oinfoview = M ( 'order_cinfo' ); 														// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$oinfolist = array (); 																			// 订单信息数组
	
		$ordertotal = $oinfoview->where ( $ordermap )->count (); 										// 计算当前商家下的订单总数
		if ($ordertotal) {
			$oinfolist = $oinfoview->where ( $ordermap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $oinfolist ); $i ++) {
				//处理订单所属分店
				if ($oinfolist [$i] ['subbranch_id'] != null && $oinfolist [$i] ['subbranch_id'] != '-1' && $oinfolist [$i] ['subbranch_id'] != ''){
					$submap = array(
							'subbranch_id' => $oinfolist [$i] ['subbranch_id'],
							'is_del' => 0
					);
					$subinfo = M('subbranch')->where($submap)->find();
					if($subinfo){
						$oinfolist [$i] ['subbranch_id'] = $subinfo['subbranch_name'];
					}else {
						$oinfolist [$i] ['subbranch_id'] = '云总店';
					}
				}else {
					$oinfolist [$i] ['subbranch_id'] = '云总店';
				}
	
				//时间格式转换
				$oinfolist [$i] ['order_time'] = timetodate ( $oinfolist [$i] ['order_time'] );
			}
		}
	
		$json = '{"total":' . $ordertotal . ',"rows":' . json_encode ( $oinfolist ) . '}';
		echo $json;
	}
	
	/**
	 * 单击订单详情按钮展开订单详情。
	 */
	public function getOrderDetail() {
		$detailmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'order_id' => I ( 'oid' ), // 接收订单编号
				'is_del' => 0
		);
		$detailinfo = M ( 'order_detail_proinfo' )->where ( $detailmap )->select ();
		for ($i = 0; $i < count ( $detailinfo ); $i ++) {
			$detailinfo [$i] ['micro_path'] = assemblepath ( $detailinfo [$i] ['micro_path'] );
			$detailinfo [$i] ['macro_path'] = assemblepath ( $detailinfo [$i] ['macro_path'] );
		}
		if ($detailinfo) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'detaillist' => $detailinfo
					)
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => '没有查到订单详情信息。',
					'data' => array ()
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 按条件查找订单。
	 * @author hufuling
	 */
	public function conditionSearchOrder() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
	
		if($condition == 'visual_number' || $condition == 'express_id' || $condition == 'customer_id' || $condition == 'customer_tel' || $condition == 'receive_tel'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if ($condition == 'normal_status') {
		//}else if ($condition == 'receive_status' || $condition == 'is_payed' || $condition == 'is_send') {
			$searchmap [$condition] = $content;
		}else if($condition == 'order_time'){
			$type = I('type');
			if ($type == 0){
				//日期状态查询：今天、昨天、最近7天、最近30天
				if($content == 'today'){
					$starttime = todaystart();
					$endtime = todayend();
				}else if($content == 'yesterday'){
					$starttime = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) - 1, date ( 'Y' ) );
					$endtime = todaystart() - 1;
				}else if($content == 'lastweek'){
					$starttime = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) - 6, date ( 'Y' ) );
					$endtime = todayend();
				}else if($content == 'lastmonth'){
					$starttime = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) - 29, date ( 'Y' ) );
					$endtime = todayend();
				}
				$searchmap [$condition] = array ( 'between', array($starttime,$endtime));
			}else if ($type == 1){
				//日期范围查询
				$timeStart = strtotime(I('startsearchcontent'));
				$timeEnd = strtotime(I('endsearchcontent'));
				if(($timeStart == null || $timeStart == '') && $timeEnd != null && $timeEnd != ''){
					$searchmap [$condition] = array ( 'elt', $timeEnd);
				}else if($timeStart != null && $timeStart != '' && ($timeEnd == null || $timeEnd == '')){
					$searchmap [$condition] = array ( 'egt', $timeStart);
				}else $searchmap [$condition] = array ( 'between', array($timeStart,$timeEnd));
			}
		}else if ($condition == 'receive_status') {
			$searchmap [$condition] = $content;
		}
	   
		$oinfoview = M ( 'order_cinfo' ); 															// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$searchinfo = array (); 																	// 搜索订单信息数组
		$searchtotal = $oinfoview->where( $searchmap )->count (); 									// 查询所搜索现有记录总条数
		if($searchtotal) {
			$searchinfo = $oinfoview->where( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 查询出easyUI需要的数据
			for($i = 0; $i < count ( $searchinfo ); $i ++) {
				//处理订单所属分店
				if ($searchinfo [$i] ['subbranch_id'] != null && $searchinfo [$i] ['subbranch_id'] != '-1' && $searchinfo [$i] ['subbranch_id'] != ''){
					$submap = array(
							'subbranch_id' => $searchinfo [$i] ['subbranch_id'],
							'is_del' => 0
					);
					$subinfo = M('subbranch')->where($submap)->find();
					if($subinfo){
						$searchinfo [$i] ['subbranch_id'] = $subinfo['subbranch_name'];
					}else {
						$searchinfo [$i] ['subbranch_id'] = '暂无门店信息';
					}
				}else {
					$searchinfo [$i] ['subbranch_id'] = '暂无门店信息';
				}
	
				//时间格式转换
				$searchinfo [$i] ['order_time'] = timetodate ( $searchinfo [$i] ['order_time'] ); 	//时间戳转换日期显示的处理
			}
		}
		$json = '{"total":' . $searchtotal . ',"rows":' . json_encode ( $searchinfo ) . '}';
		echo $json;
	}
	
	/**
	 * 店铺接收订单。
	 */
	public function receiveOrder() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		$orderidlist = I ( 'receivelist' ); 															// 要接收的订单编号
		if (! empty ( $orderidlist )) {
			$ordertable = M ( 'ordermain' ); 															// 订单表对象
			$receivemap = array (
					'order_id' => array ( 'in', $orderidlist ), 										// 用IN语句，最多10条
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 									// 当前商家下
					'is_del' => 0
			);
			$receiveresult = $ordertable->where ( $receivemap )->setField ( 'receive_status', 1 ); 		// 店铺接收订单
		}
	
		$ajaxresult = array ();
		if ($receiveresult) {
			$ajaxresult = array (
					'errCode' => 0,																		// 如果接收成功，则直接返回码成功
					'errMsg' => 'ok',
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => "网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 店铺标注已发货，并对相应顾客进行商家已发货提醒
	 */
	public function markSend() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		$orderidlist = I ( 'sendlist' ); 																// 要标记发货的订单编号
		if (! empty ( $orderidlist )) {
			$ordertable = M ( 'ordermain' ); 															// 订单表对象
			// 准备要标记发货的订单数组
			$sendmap = array (
					'order_id' => array ( 'in', $orderidlist ), 										// 用IN语句，最多10条
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 									// 当前商家下
					'is_del' => 0
			);
			$sendresult = $ordertable->where ( $sendmap )->setField ( 'is_send', 1 ); 					// 订单标记发货
		}
	
		$ajaxresult = array ();
		if ($sendresult){
			$eimap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 									// 当前商家下
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $eimap )->find ();
			$text = "温馨提示，尊敬的" . $_SESSION ['curEnterprise'] ['e_name'] . "会员，您的订单已发货！"; 			// 个性化提示信息
			$customerlist = I ( 'customerlist' );
			$openidlist = explode ( ",", $customerlist );
			$swc = A ( 'Service/WeChat' );
			for($i = 0; $i < count ( $openidlist ); $i ++) {
				$swc->sendText ( $einfo, $openidlist[$i], $text );										// 给下单用户发送已发货提醒
			}
	
			$ajaxresult = array (
					'errCode' => 0,																		// 如果接收成功，则直接返回码成功
					'errMsg' => 'ok',
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10001,
					'errMsg' => "网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 店铺标注已发货(到店自提)
	 * @author lutingting
	 */
	public function markSendOrder() {
		$orderid = I ( 'oid' ); 
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];	                                    // 设置全局变量
		// 初始化事务失败返回的ajax结果
		$this -> ajaxresult ['errCode'] = 10002;
		$this -> ajaxresult ['errMsg'] = "发货失败，系统繁忙,请稍后再试！";	
	    //校验order_id是否为空
		if (! isset ( $orderid )) {
			$this -> ajaxresult ['errCode'] = 10003;
			$this -> ajaxresult ['errMsg'] = "您提交的订单编号不能为空！";	
			$this -> ajaxReturn ($this -> $ajaxresult ); // 返回给前端ajax信息
		}
		
		//实例化订单主表、订单流水表对象
		$ordertable = M ( 'ordermain' );                            
		$orderstatus = M ('orderstatusrecord');						
		// 先查找是否存在该订单
		$sendmap = array (
				'order_id' => $orderid,
				'e_id' => $e_id, 									// 当前商家下
				'is_del' => 0
		);
		$orderviewresult = M ( 'orderinfo_view' ) -> where ( $sendmap ) -> find ();
		if (! $orderviewresult) {
			$ajaxresult = array (
					'errCode' => 10004,
					'errMsg' => "该订单不存在，请核对！"
			);
			$this -> ajaxReturn($ajaxresult);
		}	
		
		//启动事物
		$ordertable -> startTrans();
		// 订单标记发货
		$orderdata = array (
				'normal_status' => 2
		);
		$sendresult = $ordertable -> where($sendmap) -> save($orderdata);					
		if (! $sendresult) {
			$ordertable -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		
		//记订单流水
		$orderstatusrecorddata = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $e_id,
				'mall_type' => $orderviewresult ['mall_type'],
				'order_id' => $orderid,
				'status_flag' => $orderviewresult ['status_flag'],
				'normal_status' => 2,
				'refund_status' => $orderviewresult ['refund_status'],
				'is_read' => 0,
				'add_time' => time (),
				'remark' => "商家在" . timetodate ( time () ) . "确认顾客到店自提，记录订单流水。", // 订单流水原因
		);
		$sendstatusresult = $orderstatus -> add ( $orderstatusrecorddata );              
		if (! $sendstatusresult) {
			$ordertable -> rollback ();
			$this -> ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		//插入导购提成
		$statusTable = M("guideprofitstatus");
		$balanceTable = M("guidebalance");
		$recordTable = M("guideprofitrecord");
		
		$ordermap = array(
				'e_id' => $e_id,
				'order_id' => $orderid ,
				'is_del' => 0
		);
		$profit_money=M ( 'orderdetail' ) -> where ( $ordermap ) -> sum ('cutprofit_amount');
		if($profit_money <= 0){
			// 如果没有导购提成直接提交事务
			$ordertable->commit ();
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		//否则插入导购提成
		$subbranch_id = $orderviewresult['subbranch_id'];
		$guide_id = $orderviewresult['guide_id'];
		$order_price = $orderviewresult['total_price'];
		
		// 构造要输入的数据
		// 1、构造提成状态表的数据
		$statusData = array (
				'profit_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $e_id,
				'subbranch_id' => $subbranch_id,
				'guide_id' => $guide_id,
				'order_id' => $orderid,
				'order_price' => $order_price,
				'profit_money' => $profit_money,
				'profit_status' => 0,
				'extract_time' => -1,
				'cancel_time' => -1,
				'add_time' => time(),
				'remark' => ''
		);
		// 2、构造导购账户表的数据
		// 1)查库判断导购账户表中存不存在当前导购账号的记录
		$balMap = array(
				'guide_id' => $guide_id,
				'is_del' => 0
		);
		$balFindResult = $balanceTable -> where($balMap) -> find();
		$balanceData = array();
		// 2)没有则新增，有则更新导购账户表
		if($balFindResult) {
			// 如果返回值不为空,说明本来就有账户记录，直接构造更新数据
			$balanceData['balance_id'] = $balFindResult['balance_id'];
			$balanceData['frozen_balance'] = $balFindResult['frozen_balance'] + $profit_money;
			$balanceData['modify_time'] = time();
		} else {
			// 生成新增的账户记录
			$balanceData['balance_id'] = md5 ( uniqid ( rand (), true ) );
			$balanceData['guide_id'] = $guide_id;
			$balanceData['e_id'] = $e_id;
			$balanceData['subbranch_id'] = $subbranch_id;
			$balanceData['extract_balance'] = 0;
			$balanceData['frozen_balance'] = $profit_money;
			$balanceData['modify_time'] = time();
		}
		// 3、构造导购提成流水表的数据
		$recordData = array(
				'profit_id' => md5(uniqid(rand(),true)),
				'e_id' => $e_id,
				'subbranch_id' => $subbranch_id,
				'guide_id' => $guide_id,
				'order_id' => $orderid,
				'order_price' => $order_price,
				'profit_money' => $profit_money,
				'change_type' => 1,
				'add_time' => time()
		);
		
		// 1、插入数据到t_guideprofitstatus表中
		$statusResult = $statusTable -> add ( $statusData );
		if ($statusResult == false) {
			// 失败回滚
			$ordertable -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		// 2、更新t_guidebalance表:要注意
		if($balFindResult)	{
			// 1)有的话，则是更新记录
			$balUpdateResult = $balanceTable -> save($balanceData);
			if ($balUpdateResult == false) {
				// 失败回滚
				$ordertable -> rollback ();
				$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
			}
		} else {
			// 2)如果没有的话要插入一条
			$balInsertResult = $balanceTable -> add($balanceData);
			if ($balInsertResult == false) {
				// 失败回滚
				$ordertable -> rollback ();
				$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
			}
		}
		// 3、插入数据到t_guideprofitrecord表中
		$recordResult = $recordTable -> add ( $recordData );
		if ($recordResult == false) {
			// 失败回滚
			$ordertable -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		
		// 事务都成功，返回结果
		$ordertable -> commit();
		$this -> ajaxresult ['errCode'] = 0;
		$this -> ajaxresult ['errMsg'] = "ok";
		$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息	
	}
	
	/**
	 * 备注订单确认。
	 */
	public function remarkOrderConfirm() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		$remarkinfo = array (
				'order_id' => I ( 'oid' ),
				'remark' => I ( 'remark' ),
		);
		$remarkresult = M ( 'ordermain' )->save ( $remarkinfo ); 										// 特别注意：有主键才可以进行save，没主键必须加上where条件限制
	
		$ajaxresult = array ();																			// 准备ajax返回信息
		 if ($remarkresult) {
			$ajaxresult ['errCode'] = 0; 																// 如果接收成功，则直接返回码成功
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 协商撤销订单操作。
	 */
	public function negotiateCancelOrder() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/orderView', '', '', true ) ); // 防止恶意进入
	
		// 准备要标记发货的订单数组
		$cancelmap = array (
				'order_id' => I('nego'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 									// 当前商家下
				'is_del' => 0
		);
		$cancelresult = M ( 'ordermain' )->where ( $cancelmap )->setField ( 'e_mark_del', 1 ); 		// 订单标记商家撤销
	
		// to do cancel order...
		// to tip customer
	}
	
	/**
	 * 企业同意用户取消订单的ajax处理函数，该函数在WeMall商城下，被迁移过来。
	 */
	public function cancelOrder() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 提交订单的用户
		$orderID = I ( 'orderID', '' );
		// 检测订单编号是否为空
		if (empty ( $orderID )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "订单退单失败，订单编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 检测当前顾客是否存在该订单编号的该退订单!（加上时间判断,未超过七天）
		$ordermap = array (
				'order_id' => $orderID,
				'customer_id' => $customer_id,
				//以下字段取消，该如何修改
				'status_flag' => 0,
				'is_refund' => 0, // 未退款
				'consult_cancel' => 0, // 未协商撤销
				'timeout_cancel' => 0, // 未超时取消
				'is_del' => 0
		);
		$order = M ( "ordermain" )->where ( $ordermap )->find ();
		if (! $order) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "订单退单失败，该顾客不存在该笔订单或该笔订单已撤销或已退款！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 取出订单中的有效信息
		$e_id = $order ['e_id'];
		$subbranch_id = $order ['subbranch_id'];
		$customer_id = $order ['customer_id'];
		$open_id = $order ['openid'];
		$guide_id = $order ['guide_id'];
		//$normal_status = $order ['normal_status'];
		$is_paid = $order ['is_payed'];
		$is_send = $order ['is_send'];
		$is_signed = $order ['is_signed'];
	     
		// 根据$order_id从t_orderdetail查询子订单信息
		$orderDetailMap = array (
				'order_id' => $orderID,
				'is_del' => 0
		);
		$orderDetailItem = M ( "orderdetail" )->where ( $orderDetailMap )->select ();
		if (! $orderDetailItem) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "订单退单失败，该笔订单未检测到商品信息，请重试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 检测该商家的微信支付是否完备
		$epayconfigmap = array (
				'e_id' => $e_id, // 当前商家
				'is_del' => 0
		);
		$wechatpayinfo = M ( 'secretinfo' )->where ( $epayconfigmap )->find (); // 找到商家的微信支付信息
		if (! $wechatpayinfo) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "订单退单失败，当前商家没有设置微信支付信息，请联系该商家完善！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 初始化事务失败返回的ajax结果
		$this->ajaxresult ['errCode'] = 10006;
		$this->ajaxresult ['errMsg'] = "订单退单失败，系统繁忙,请稍后再试！";
	
		// 启动事务
		$subbranchSkuTable = M ( 'subbranchsku' ); // 实例化分店商品表
		$subbranchSkuTable->startTrans ();
	
		/**
		 * 退订单的过程中
		 * 1、邮费不退（分发货没发货）
		 * 2、优惠券不退
		 * 3、购物积分要退； 用户增加的积分要退 ,暂不考虑刷积分的可能性，(暂不考虑积分商城的商品涉及到的积分回退)
		 * 回退积分的时候判别下，扣除后有没有小于0，若小于0不够扣，直接变成0
		*/
		// Step1:改变对应分店相关商品库存
		for($i = 0; $i < count ( $orderDetailItem ); $i ++) {
			$updateskumap = array (
					'subbranch_id' => $subbranch_id, // 分店ID
					'product_id' => $orderDetailItem [$i] ['product_id'],
					'is_del' => 0
			);
			$subSku = $subbranchSkuTable->where ( $updateskumap )->find ();
			// 假如商品已撤销sku（当初做的是真删），那么此处不用撤销
			if (! empty ( $subSku )) {
				// 如果分店中有该商品库存,那么设置卖出量减少
				$result = $subbranchSkuTable->where ( $updateskumap )->setDec ( 'subsku_sell', $orderDetailItem [$i] ['amount'] ); // 改变分店商品表库存(卖出量减少)
				if ($result == false) {
					// 一旦有一次失败，则回滚
					$subbranchSkuTable->rollback ();
					$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
				}
			}
		}
	
		// Step2:客户退款(同时改变t_ordermain中consult_cancel\is_refund\refund_reason字段)
		// 退款为0，仅改变consult_cancel字段
		// 退款不为0.还需改变is_refund、refund_reason字段
		// $customer_id 作为需要退款的顾客ID
		// 获取退款金额
		$money = 0;
		if ($is_paid == 1 && $is_send == 0) {
			$money = $order ['pay_indeed'];
		} else if ($is_send == 1) {
			$money = $order ['pay_indeed'] - $order ['express_fee']; // 邮费不退，退的是(总费用-邮费)
		}
	
		$orderMainData = array (); // 定义更新的订单主单数据
		if ($money != 0) {
			// 已付款，表明此时是撤单（协商撤销）,订单主表is_del = 0不变，更新is_refund字段,订单子表不变
			// 进行退款操作
			$moneyBackRes = true; // 先默认为true
			if ($moneyBackRes == false) { // 退款失败，则回滚
				$subbranchSkuTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 同时更新t_order表中is_refund字段和refund_reason字段
			$orderMainData = array (
					'order_id' => $orderID, // 订单ID
					'consult_cancel' => 1, // 协商撤销设为true
					'is_refund' => 1,
					'refund_reason' => '撤单退款'
			);
		} else {
			// 表示并未付款,此时订单主表is_del要置为1,表明是删单
			// Step2:订单关闭,拼装订单主表数据构造 (订单主表标记为关单)
			$orderMainData = array (
					'order_id' => $orderID, // 订单ID
					'is_del' => 1
			);
			// Step3:订单子表关闭 t_orderdetail,选择未删单的订单子表
			$orderDetailMap = array (
					'order_id' => $orderID, // 订单ID
					'is_del' => 0
			);
			$orderDetailData ['is_del'] = 1;
			$orderDetailResult = M ( "orderdetail" )->where ( $orderDetailMap )->save ( $orderDetailData );
			if ($orderDetailResult == false) { // 失败，则回滚
				$subbranchSkuTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		$orderMainResult = M ( "ordermain" )->save ( $orderMainData );
		if ($orderMainResult == false) { // 失败，则回滚
			$subbranchSkuTable->rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 假设用户并没有签收，那么就不用退积分、导购提成了，事务至此结束
		if ($is_signed == 0) {
			// Step5：事务成功提交，并返回给前台信息
			$subbranchSkuTable->commit ();
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 已签收退款，则进行接下来的步骤
		/*
		 * Step4:退购物积分
		 * 购物积分要退； 用户增加的积分要退 ,暂不考虑刷积分的可能性，(暂不考虑积分商城的商品涉及到的积分回退)
		 * 回退积分的时候判别下，扣除后有没有小于0，若小于0不够扣，直接变成0(暂时允许负积分存在的情况)
		 */
		// 首先统计有多少积分
		$scoreAmount = 0;
		// 根据$order_id从t_orderdetail查询子订单总共可获得的积分数
		$orderDetailMap = array (
				'order_id' => $orderID,
				'is_del' => 0
		);
		$scoreAmount = M ( "orderdetail" )->where ( $orderDetailMap )->sum ( 'get_score' );
		// 记录为空或者0的情况下，不做任何处理
		if (! empty ( $scoreAmount )) {
			// 通过在t_customerscore中加入一条负值记录，触动触发器更新即可
			$cusScoreTable = M ( "customerscore" );
			$cusScoreData = array (
					'score_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $e_id,
					'subbranch_id' => $subbranch_id,
					'customer_id' => $customer_id,
					'change_time' => time (),
					'change_amount' => - $scoreAmount,
					'change_reason' => '退单退积分',
					'remark' => '',
					'is_del' => 0
			);
			$scoreResult = $cusScoreTable->add ( $cusScoreData );
			if ($scoreResult == false) { // 失败回滚
				$subbranchSkuTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
	
		/*
		 * 4、导购提成要退（导购的钱）
		 * 导购提成可看到：
		 * 1、顾客确认收货的时候,导购看到被冻结的提成
		 * 2、确认收货后七天，冻结的提成变为可提现
		 * 用户退货：
		 * 1、t_guideprofit的is_frozen = 0， is_cancel = 1
		 * 2、t_guidebalance的冻结金额-=profit
		 */
		// 通过t_guideprofit表,找出其中的订单提成记录，将is_cancel置为1
		$guideProfitTable = M ( "guideprofit" );
		$profitData = array (
				'guide_id' => $guide_id, // 考虑到导购为空的情况，此处可能无记录
				'order_id' => $orderID,
				'change_type' => 0,
				'is_frozen' => 1,
				'is_cancel' => 0,
				'is_del' => 0
		);
		$profitRecord = $guideProfitTable->where ( $profitData )->find ();
		if (! empty ( $profitRecord )) {
			// t_guideprofit的is_frozen = 0， is_cancel = 1
			$data  = array (
					'profit_id'=>$profitRecord ['profit_id'],
					'is_frozen'=>0,
					'is_cancel'=>1,
			);
			$guideProRes = $guideProfitTable->save ( $data ); // 根据条件保存修改的数据
			if ($guideProRes == false) {
				// 失败回滚
				$subbranchSkuTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// t_guidebalance的冻结金额-=profit
			$guideBalanceTable = M ( "guidebalance" );
			$guideBalanMap = array (
					'guide_id' => $guide_id,
					'is_del' => 0
			);
			$balanResult = $guideBalanceTable->where ( $guideBalanMap )->setDec ( 'frozen_balance', $profitRecord ['moneychanged'] ); // 导购的冻结金额减掉
			if ($balanResult == false) {
				// 失败回滚
				$subbranchSkuTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
	
		// Step5：事务成功提交，并返回给前台信息
		$subbranchSkuTable->commit ();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 企业让用户退单。
	 */
	public function refundOrderConfirm() {
		$order_id = I ( 'oid' ); 							// 接收订单编号
		$refund = A ( 'Admin/Refund' ); 					// 实例化退款控制器
		$refundresult = $refund->orderRefund ( $order_id ); // 执行订单退款
		$this->ajaxReturn ( $refundresult ); 				// 退款
	}
	
	/**
	 * 删除订单
	 */
	public function delOrder(){
		$deloidlist = I ( 'rowdata' );
	
		$delomap = array(
				'order_id' => array('in', $deloidlist),
				'e_mark_del' => 0,
				'is_del' => 0
		);
	
		$deloresult = M('ordermain')->where($delomap)->setField('e_mark_del' , 1);
	
		$ajaxresult = array ();
		if($deloresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 如果eflag=1，编辑快递信息，且快递费和收货人信息不能修改。
	 * 如果eflag=0，创建新的快递信息，并发货
	 */
	public function deliverConfirm(){
		$eflag = I('eflag');
		$e_id = $_SESSION['curEnterprise']['e_id'];
		// 初始化事务失败返回的ajax结果
		$this -> ajaxresult ['errCode'] = 10002;
		$this -> ajaxresult ['errMsg'] = "快递信息设置失败，请检查网络状况!";
		//判断订单是否存在 
		$ordermap = array(
				'e_id' => $e_id,
				'order_id' => I('eo'),
				'is_del' => 0
		);
		$orderviewresult = M ( 'ordermain' ) -> where ( $ordermap ) -> find ();
		if( !$orderviewresult){
			$ajaxresult = array (
					'errCode' => 10003,
					'errMsg' => "该订单不存在，请核对！"
			);
			$this -> ajaxReturn($ajaxresult);
		}
		
		//快递信息表要更新的内容
		$deliverdata = array(
				'express_number' => I('en'),
				'express_company' => I('cc'),
				'deliver_time' => strtotime(I('st')),
				'send_address' => I('ea'),
				'remark' => I('er'),
		);
		if($eflag){//编辑快递，不需要修改订单状态
			$deliverdata['express_id'] = I('ei');
			$eresultsave = M('express') -> save($deliverdata);
			if (! $eresultsave) {
				$this -> ajaxresult ['errCode'] = 10004;
				$this -> ajaxresult ['errMsg'] = "请不要重复提交！";
				$this -> ajaxReturn ( $this->ajaxresult );
			}else {
				
				$this -> ajaxresult ['errCode'] = 0;
				$this -> ajaxresult ['errMsg'] = "ok";
				$this -> ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		
		//实例化订单主表、订单流水表对象
		$expressinfo = M('express');
		$ordertable = M ( 'ordermain' );                            
		$orderstatus = M ('orderstatusrecord');		
		
		$expressinfo -> startTrans();
		
		//添加新的快递
		$deliverdata['express_id'] = md5(uniqid(rand(),true));
		$eresult = $expressinfo -> add($deliverdata);
		if (! $eresult) {
			$expressinfo -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		
		//订单状态更新
		$orderdata = array(
				'order_id' => I('eo'),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'express_id' => $deliverdata['express_id'],
				'express_fee' => I('ef'),
				'receive_person' => I('rn'),
				'receive_tel' => I('rt'),
				'receive_address' => I('ra'),
				'normal_status' => 2,
				'receive_status' => 1
		);
		$oresult = $ordertable -> save($orderdata);                      //更改订单状态
		if (! $oresult) {
			$expressinfo -> rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}

		//订单流水更新
		$orderstatusrecord = array (          
				'record_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'mall_type' => $orderviewresult ['mall_type'],
				'order_id' => I('eo'),
				'status_flag' => $orderviewresult ['status_flag'],
				'normal_status' => 2,
				'refund_status' => $orderviewresult ['refund_status'],
				'is_read' => 0,
				'add_time' => time (),
				'remark' => "商家在" . timetodate ( time () ) . "发货，记录订单流水。", // 订单流水原因
		);
		$sendstatusresult = $orderstatus -> add( $orderstatusrecord );         //记录订单流水 
		if (! $sendstatusresult) {
			$expressinfo -> rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		//插入导购提成
		$statusTable = M("guideprofitstatus");
		$balanceTable = M("guidebalance");
		$recordTable = M("guideprofitrecord");
		
		$ordermap = array(
				'e_id' => $e_id,
				'order_id' => I('eo'),
				'is_del' => 0
		);
		$profit_money = M ( 'orderdetail' ) -> where ( $ordermap ) -> sum ('cutprofit_amount');
		if($profit_money <= 0){
			// 如果没有导购提成直接提交事务
			$expressinfo -> commit ();
			$this -> ajaxresult ['errCode'] = 0;
			$this -> ajaxresult ['errMsg'] = "ok";
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		//否则插入导购提成
		$subbranch_id = $orderviewresult['subbranch_id'];
		$guide_id = $orderviewresult['guide_id'];
		$order_id = I('eo');
		$order_price = $orderviewresult['total_price'];
		
		// 构造要输入的数据
		// 1、构造提成状态表的数据
		$statusData = array (
				'profit_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $e_id,
				'subbranch_id' => $subbranch_id,
				'guide_id' => $guide_id,
				'order_id' => $order_id,
				'order_price' => $order_price,
				'profit_money' => $profit_money,
				'profit_status' => 0,
				'extract_time' => -1,
				'cancel_time' => -1,
				'add_time' => time(),
				'remark' => ''
		);
		// 2、构造导购账户表的数据
		// 1)查库判断导购账户表中存不存在当前导购账号的记录
		$balMap = array(
				'guide_id' => $guide_id,
				'is_del' => 0
		);
		$balFindResult = $balanceTable -> where($balMap) -> find();
		$balanceData = array();
		// 2)没有则新增，有则更新导购账户表
		if($balFindResult) {
			// 如果返回值不为空,说明本来就有账户记录，直接构造更新数据
			$balanceData['balance_id'] = $balFindResult['balance_id'];
			$balanceData['frozen_balance'] = $balFindResult['frozen_balance'] + $profit_money;
			$balanceData['modify_time'] = time();
		} else {
			// 生成新增的账户记录
			$balanceData['balance_id'] = md5 ( uniqid ( rand (), true ) );
			$balanceData['guide_id'] = $guide_id;
			$balanceData['e_id'] = $e_id;
			$balanceData['subbranch_id'] = $subbranch_id;
			$balanceData['extract_balance'] = 0;
			$balanceData['frozen_balance'] = $profit_money;
			$balanceData['modify_time'] = time();
		}
		// 3、构造导购提成流水表的数据
		$recordData = array(
				'profit_id' => md5(uniqid(rand(),true)),
				'e_id' => $e_id,
				'subbranch_id' => $subbranch_id,
				'guide_id' => $guide_id,
				'order_id' => $order_id,
				'order_price' => $order_price,
				'profit_money' => $profit_money,
				'change_type' => 1,
				'add_time' => time()
		);
		
		// 1、插入数据到t_guideprofitstatus表中
		$statusResult = $statusTable -> add ( $statusData );
		if ($statusResult == false) {
			// 失败回滚
			$statusTable -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		// 2、更新t_guidebalance表:要注意
		if($balFindResult)	{
			// 1)有的话，则是更新记录
			$balUpdateResult = $balanceTable -> save($balanceData);
			if ($balUpdateResult == false) { // 失败回滚
				$statusTable -> rollback ();
				$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
			}
		} else {
			// 2)如果没有的话要插入一条
			$balInsertResult = $balanceTable -> add($balanceData);
			if ($balInsertResult == false) { // 失败回滚
				$statusTable -> rollback ();
				$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
			}
		}
		// 3、插入数据到t_guideprofitrecord表中
		$recordResult = $recordTable -> add ( $recordData );
		if ($recordResult == false) {
			// 失败回滚
			$statusTable -> rollback ();
			$this -> ajaxReturn ( $this -> ajaxresult ); // 返回给前端ajax信息
		}
		
		// 事务都成功，返回结果
		$expressinfo -> commit ();
		$eimap = array (
				'e_id' => $e_id, 									// 当前商家下
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' ) -> where ( $eimap ) -> find ();
		$text = "温馨提示，尊敬的" . $_SESSION ['curEnterprise'] ['e_name'] . "会员，您的订单已发货！"; 			// 个性化提示信息
		$customerid = I ( 'cid' );
		$swc = A ( 'Service/WeChat' );
		$swc -> sendText ( $einfo, $customerid, $text );
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息	
  }
	
	/**
	 * 订单详情页面进行收货人信息修改处理
	 */
	public function changeRecInfo(){
		$recdata = array(
				'order_id' => I('orderid'),
				'receive_person' => I('receiver'),
				'receive_tel' => I('telphone'),
				'receive_address' => I('address')
		);
	
		$recresult = M('ordermain')->save($recdata);
	
		$ajaxresult = array();
		if ($recresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息设置成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息设置失败，请勿重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 订单详情页面进行支付信息修改处理
	 */
	public function changePayInfo(){
		$paydata = array(
				'order_id' => I('orderid'),
				'pay_indeed' => I('payindeed')
		);
		$payresult = M('ordermain')->save($paydata);
	
		$ajaxresult = array();
		if ($payresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息设置成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息设置失败，请勿重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 订单退款的例子。
	 */
	public function testRefund() {
		import ( 'Class.API.WeChatPayV3.WeActWxPay.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动平台安全支付SDK
	
		$e_id = "201406261550250006"; 						// 给出订单所属企业编号
		$order_id = "afd8d0474bdaaaadc6a615d818d7c2d0"; 	// 给出要退的订单编号
		$refundmoney = 6; 									// 给出要退款的金额（元）
	
		$weactrefund = new WeActRefund ( $e_id ); 			// 创建退款类对象
		$refundresult = $weactrefund->orderRefund ( $order_id, $refundmoney ); // 为某笔订单退款多少钱
		p($refundresult);die; 								// 显示退款结果
	}
	
	
	/**
	 * ========以下是邮费设定功能模块管理控制部分=======
	 */
	
	/**
	 * easyUI的post请求，初始化读取邮费模板数据
	 */
	public function read(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/postageModeView','','',true ));
			
		$pmmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],                          				// 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'mode_sort';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$pmtbl = M ( 'postagemode' );
		$pminfolist = array ();
		$pmtotal = $pmtbl->where ( $pmmap )->count ();                                     				// 计算当前商家下不被删除餐饮商品的总数
		if($pmtotal){
			$pminfolist = $pmtbl->where ( $pmmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i<count($pminfolist); $i++){
				$pminfolist [$i] ['add_time'] = timetodate ( $pminfolist [$i] ['add_time'] ); 			// 添加商品时间转为可视化
			}
		}
	
		$json = '{"total":' . $pmtotal . ',"rows":' . json_encode ( $pminfolist ) . '}'; 				// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 单击模板详情按钮展开模板详情。
	 */
	public function getModeDetail() {
		$detailmap = array (
				'mode_id' => I ( 'mid' ), // 接收订单编号
				'is_del' => 0
		);
		$detailinfo = M ( 'postagedetail' )->where ( $detailmap )->select ();
	
		if ($detailinfo) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'detaillist' => $detailinfo
					)
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => '没有查到模板详情信息。',
					'data' => array ()
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 新增邮费模板确认。
	 */
	public function addPostageModeCfm(){
		// 新增邮费模板
		$postagedata = array(
				'mode_id' => I('mid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'mode_name' => I('mna'),
				'exp_company' => I('cuc'),
				'mode_sort' => I('mso'),
				'first_weight' => I('fwe'),
				'added_weight' => I('awe'),
				'status' => I('status'),
				'remark' => I('mre'),
				'add_time' => time()
		);
		$postageresult = M('postagemode')->add($postagedata);
	
		// 指定地区数据输入（接收）
		$regiondata = array (
				'districts' => $_REQUEST["vallist"], 				// 地区列表
				'firstfee' => $_REQUEST["deflist"], 				// 首重费用
				'addfee' => $_REQUEST["inclist"] 					// 续重费用
		);
		// 切分数据
		$regionlist = explode ( "|", $regiondata ['districts'] ); 	// 切割地区数组
		$firstfeelist = explode ( "|", $regiondata ['firstfee'] ); 	// 切割首重费用数组
		$addfeelist = explode ( "|", $regiondata ['addfee'] ); 		// 切割续重费用数组
	
		$regionlistnum = count ( $regionlist ); 					// 计算总数量
		$detaillist = array();
		for($i = 0; $i < $regionlistnum; $i ++) {
			$detaillist [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ),// 记录主键
					'mode_id' => $postagedata['mode_id'],
					'designated_area' => $regionlist [$i], 			// 该记录的地区
					'first_fee' => $firstfeelist [$i], 				// 该地区的首重费用
					'added_fee' => $addfeelist [$i], 				// 该地区的续重费用
			);
		}
		$detailresult = M('postagedetail')->addAll($detaillist);
	
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($postageresult && $detailresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '添加成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 确认邮费编辑模板。
	 */
	public function editPostageModeCfm() {
		//更新邮费模板主要信息
		$modedata = array(
				'mode_id' => I('mid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'mode_name' => I('mna'),
				'exp_company' => I('cuc'),
				'mode_sort' => I('mso'),
				'first_weight' => I('fwe'),
				'added_weight' => I('awe'),
				'status' => I('status'),
				'remark' => I('mre'),
		);
		$modeupresult = M('postagemode')->save($modedata);
	
		// 更新邮费模板指定地区费用详情信息
		$detaildata = array (
				'detailid' => $_REQUEST["detailidlist"],
				'districts' => $_REQUEST["vallist"], 				// 地区列表
				'firstfee' => $_REQUEST["deflist"], 				// 首重费用
				'addfee' => $_REQUEST["inclist"] 					// 续重费用
		);
	
		$detailidlist = explode ( "|", $detaildata ['detailid'] );
		$regionlist = explode ( "|", $detaildata ['districts'] ); 	// 切割地区数组
		$firstfeelist = explode ( "|", $detaildata ['firstfee'] ); 	// 切割首重费用数组
		$addfeelist = explode ( "|", $detaildata ['addfee'] ); 		// 切割续重费用数组
	
		$detaillistnum = count ( $regionlist ); 					// 计算总数量
		$detaillist = array();
		for($i = 0; $i < $detaillistnum; $i ++) {
			$detaillist [$i] = array (
					'designated_area' => $regionlist [$i], 			// 该记录的地区
					'first_fee' => $firstfeelist [$i], 				// 该地区的首重费用
					'added_fee' => $addfeelist [$i], 				// 该地区的续重费用
			);
			if(!empty($detailidlist [$i])){
				$detaillist [$i]['detail_id'] = $detailidlist[$i];					// 已有主键，更新数据
				$detailupresult[$i] = M('postagedetail')->save($detaillist[$i]);
			}else {
				$detaillist [$i]['detail_id'] = md5 ( uniqid ( rand (), true ) );	// 没有主键，新增数据
				$detaillist [$i]['mode_id'] = $modedata['mode_id'];
				$detailaddresult[$i] = M('postagedetail')->add($detaillist[$i]);
			}
		}
	
		$ajaxresult = array ();
		if(!empty($detailaddresult) || !empty($detailaddresult) || $modeupresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息更新成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息更新失败，请勿重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 删除模板处理函数
	 */
	public function delPostageMode(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$delidlist = I ( 'rowdata' ); 					// 接收要删除的邮费模板id列表
		$pmtbl = M ( 'postagemode' ); 					// 邮费模板表
		$pdtbl = M ( 'postagedetail' ); 				// 邮费模板详情表
	
		$delpmap = array(
				'mode_id' => array('in', $delidlist),
				'is_del' => 0
		);
	
		$delpdresult = $pdtbl->where($delpmap)->delete();
		$delpmap['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
		$delpmresult = $pmtbl->where($delpmap)->setField('is_del', 1);
	
		$ajaxresult = array ();
		if($delpdresult && $delpmresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 指定地区的删除处理
	 */
	public function deleteDetail(){
		$delmap = array(
				'detail_id' => I('delid'),
				'is_del' => 0
		);
		$delresult = M('postagedetail')->where($delmap)->delete();
	
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($delresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 按条件搜索模板
	 */
	public function searchMode(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/OrderManage/postageModeView','','',true ));
			
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'mode_sort';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
			
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'is_del' => 0
		);
			
		if($condition == 'mode_name'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if ($condition == 'exp_company') {
			if($content != -1){
				$searchmap [$condition] = $content;
			}
		}
			
		$pmtbl = M ( 'postagemode' );
		$pminfolist = array ();
		$pmtotal = $pmtbl->where ( $searchmap )->count ();                                     				// 计算当前商家下不被删除餐饮商品的总数
		if($pmtotal){
			$pminfolist = $pmtbl->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i<count($pminfolist); $i++){
				$pminfolist [$i] ['add_time'] = timetodate ( $pminfolist [$i] ['add_time'] ); 			// 添加商品时间转为可视化
			}
		}
	
		$json = '{"total":' . $pmtotal . ',"rows":' . json_encode ( $pminfolist ) . '}'; 			// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 切换模板开闭状态
	 */
	public function changeStatus() {
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' );				// 接收参数
		$type = I ( 'type' );					// 接收操作类型
	
		// 定义要操作的范围
		$changemap = array (
				'mode_id' => array( 'in', explode ( ',', $rowdata ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$handleresult = false;					// 默认没成功
		if ($type == "on") {
			$onSave = array (
					'status' => 1,				// 模板开启状态
			);
			$handleresult = M ( 'postagemode' )->where ( $changemap )->save ( $onSave );
		} else if ($type == "off") {
			$offSave = array (
					'status' => 0, 				// 模板关闭状态
			);
			$handleresult = M ( 'postagemode' )->where ( $changemap )->save ( $offSave );
		}
	
		// 返回给前台结果
		$ajaxresult = array ();
		if ($handleresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "操作失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 查看订单流水状态
	 */
	public function getOrderStatus() {
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
        
		$ordstatusmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0,
				'order_id' => $_REQUEST['oid']
		);
	
		$orderinfoview = M ( 'orderstatusrecord' ); 
		$orderinfolist = array (); // 订单状态信息数组
		
		$orderstatustotal = $orderinfoview->where ( $ordstatusmap )->count (); // 计算当前订单下的订单状态总数 
		if ($orderstatustotal) {
			$orderinfolist = $orderinfoview->where ( $ordstatusmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
		
			for($i = 0; $i < count ( $orderinfolist ); $i ++) {
				$orderinfolist [$i] ['add_time'] = timetodate ( $orderinfolist [$i] ['add_time'] ); // 最近修改时间转为可视化
			}
		}
		$json = '{"total":' . $orderstatustotal . ',"rows":' . json_encode ( $orderinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
}
?>