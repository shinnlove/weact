<?php
class ProOrderAction extends CommonAction {
	public function subOrderView(){
		$this->display();
	}
	
	/**
	 * 获取当前店铺所有订单
	 */
	public function getAllSubOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
		
		$subordermap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 											// 获取当前商家id，以便显示当前商家的客户
				'subbranch_id' => $_SESSION ['curSubbranch'] ['subbranch_id'],							// 获取当前分店id
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
		$oinfoview = M ( 'order_cinfo' ); 														// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$oinfolist = array (); 																			// 订单信息数组
		
		$ordertotal = $oinfoview->where ( $subordermap )->count (); 										// 计算当前商家下的订单总数
		if ($ordertotal) {
			$oinfolist = $oinfoview->where ( $subordermap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $oinfolist ); $i ++) {
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
	public function getSubOrderDetail() {
		$detailmap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
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
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 											// 获取当前商家id，以便显示当前商家的客户
				'subbranch_id' => $_SESSION ['curSubbranch'] ['subbranch_id'],							// 获取当前分店id
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
	
		if($condition == 'visual_number' || $condition == 'express_id' || $condition == 'customer_id' || $condition == 'customer_tel' || $condition == 'receive_tel'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if ($condition == 'receive_status' || $condition == 'is_payed' || $condition == 'is_send') {
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
		}
	
		$oinfoview = M ( 'order_cinfo' ); 															// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$searchinfo = array (); 																	// 搜索订单信息数组
		$searchtotal = $oinfoview->where( $searchmap )->count (); 									// 查询所搜索现有记录总条数
		if($searchtotal) {
			$searchinfo = $oinfoview->where( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 查询出easyUI需要的数据
			for($i = 0; $i < count ( $searchinfo ); $i ++) {
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
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
	
		$orderidlist = I ( 'receivelist' ); 															// 要接收的订单编号
		if (! empty ( $orderidlist )) {
			$ordertable = M ( 'ordermain' ); 															// 订单表对象
			$receivemap = array (
					'order_id' => array ( 'in', $orderidlist ), 										// 用IN语句，最多10条
					'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 										// 当前商家下
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
					'errCode' => 10001,
					'errMsg' => "网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 查看订单详情信息处理函数
	 */
	public function subOrderDetail(){
		$orderdetailmap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
				'order_id' => I ( 'orderid' ), // 接收订单编号
				'is_del' => 0
		);
		//查询订单详情信息
		$orderdetailinfo = M ( 'order_cinfo' )->where ( $orderdetailmap )->find();
		//查询订单关联快递信息
		$expinfo = M('order_express')->where($orderdetailmap)->find();
	
		if($orderdetailinfo){
			$orderdetailinfo['order_time'] = timetodate($orderdetailinfo['order_time']); 		// 时间初始化格式转换：时间戳->日期格式
			$orderdetailinfo['payindeed'] = $orderdetailinfo['pay_indeed'];						// 默认设置实付款金额等于应付金额
			if ($orderdetailinfo['timeout_cancel'] == 1 || $orderdetailinfo['is_refund'] == 1 || $orderdetailinfo['consult_cancel'] != 0) {
				// 如果订单被超时取消、已经退完款了或者正在协商撤销中，就属于作废订单状态
				if ($orderdetailinfo['timeout_cancel'] == 1) {
					$orderstatus = '交易超时';
				} else if ($orderdetailinfo['is_refund'] == 1) {
					$orderstatus = '已退款  交易关闭';
				} else if ($orderdetailinfo['consult_cancel'] == 1) {
					$orderstatus = '申请退单中';
				} else if ($orderdetailinfo['consult_cancel'] == 2) {
					$orderstatus = '退单处理中';
				} else if ($orderdetailinfo['consult_cancel'] == 3) {
					$orderstatus = '商家拒绝退单';
				}
			} else {
				// 正常订单状态
				if($orderdetailinfo['is_payed'] == 0) {
					$orderstatus = '待付款';
					$orderdetailinfo['payindeed']='0';//在未付款时,实付款金额为0
				}else if($orderdetailinfo['is_payed'] == 1 && $orderdetailinfo['is_send'] == 0) {
					$orderstatus = '等待卖家发货';
				}else if($orderdetailinfo['is_payed'] == 1 && $orderdetailinfo['is_send'] == 1 && $orderdetailinfo['is_signed'] == 0) {
					$orderstatus = '等待收货';
				} else if ($orderdetailinfo['is_payed'] == 1 && $orderdetailinfo['is_send'] == 1 && $orderdetailinfo['is_signed'] == 1 && $orderdetailinfo['is_appraised'] == 0) {
					$orderstatus = '交易成功  待评价';
				} else if ($orderdetailinfo['is_payed'] == 1 && $orderdetailinfo['is_send'] == 1 && $orderdetailinfo['is_signed'] == 1 && $orderdetailinfo['is_appraised'] == 1) {
					$orderstatus = '交易成功  已评价';
				}else {
					$orderstatus = '暂时无法获取订单状态';
				}
			}
				
			//支付方式的初始化格式转换
			$paymethod = $orderdetailinfo['pay_method'];
			$ispayed = $orderdetailinfo['is_payed'];
			if($ispayed == 1){
				if($paymethod == 1){
					$orderdetailinfo['pay_method'] = '现金收讫';
				}else if($paymethod == 2){
					$orderdetailinfo['pay_method'] = '微信支付';
				}else if($paymethod == 3){
					$orderdetailinfo['pay_method'] = '刷卡支付';
				}else {
					$orderdetailinfo['pay_method'] = '未选择';
				}
			}else {
				$orderdetailinfo['pay_method'] = '/';//未付款时，设置支付方式为'/'
			}
		}
	
		$this->orderstatus = $orderstatus;
		$this->detailinfo = $orderdetailinfo;
		$this->expinfo = $expinfo;
		//区别账单通知信息中的订单详情显示
		$showtype = $_REQUEST['showtype'];
		$this->showtype = $showtype;
	
		$this->display();
	}
	
	/**
	 * 删除订单
	 */
	public function delSubOrder(){
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
	 * 店铺标注已发货，并对相应顾客进行商家已发货提醒
	 */
	public function markSend() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
	
		$orderidlist = I ( 'sendlist' ); 																// 要标记发货的订单编号
		if (! empty ( $orderidlist )) {
			$ordertable = M ( 'ordermain' ); 															// 订单表对象
			// 准备要标记发货的订单数组
			$sendmap = array (
					'order_id' => array ( 'in', $orderidlist ), 										// 用IN语句，最多10条
					'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 									// 当前商家下
					'is_del' => 0
			);
			$sendresult = $ordertable->where ( $sendmap )->setField ( 'is_send', 1 ); 					// 订单标记发货
		}
	
		$ajaxresult = array ();
		if ($sendresult){
			$eimap = array (
					'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 									// 当前商家下
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $eimap )->find ();
			$text = "温馨提示，尊敬的" . $_SESSION ['curSubbranch'] ['e_name'] . "会员，您的订单已发货！"; 			// 个性化提示信息
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
	 * 导出订单数据处理函数
	 * @author hufuling
	 */
	public function exportOrder(){
		//查询数据
		$exmap = array(
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
				'subbranch_id' => $_SESSION['curSubbranch']['subbranch_id'],
				'e_mark_del' => 0,
				'is_del' => 0
		);
		$field ="order_time, visual_number, customer_id, customer_name, customer_tel, total_price,pay_method, pay_indeed, receive_status, is_payed, express_number, receive_person, receive_tel, receive_address, remark,timeout_cancel,is_refund,consult_cancel,is_send,is_signed,is_appraised" ;
		$orderlist = M( 'order_cinfo' )->where( $exmap )->field( $field )->order ( 'order_time desc')->select();
		
		//格式化查询出的数据
		for ($i =0; $i < count ( $orderlist ); $i ++) {
			//支付方式格式化:因为is_payed在下面处理中会改变值，所有必须放在所有处理的最前面。
			if($orderlist[$i]['is_payed'] == 1){
				if ($orderlist[$i]['pay_method'] == '0'){
					$orderlist[$i]['pay_method'] = '未选择';
				}else if ($orderlist[$i]['pay_method'] == '1'){
					$orderlist[$i]['pay_method'] = '现金支付';
				}else if ($orderlist[$i]['pay_method'] == '2'){
					$orderlist[$i]['pay_method'] = '微信支付';
				}else if ($orderlist[$i]['pay_method'] == '3'){
					$orderlist[$i]['pay_method'] = '刷卡支付';
				}else $orderlist[$i]['pay_method'] = '不明方式';
			}else{
				$orderlist[$i]['pay_method'] = '/';
			}
			
			//订单状态分类
			if ($orderlist[$i]['timeout_cancel'] == 1 || $orderlist[$i]['is_refund'] == 1 || $orderlist[$i]['consult_cancel'] != 0) {
				// 如果订单被超时取消、已经退完款了或者正在协商撤销中，就属于作废订单状态
				if ($orderlist[$i]['timeout_cancel'] == 1) {
					$orderlist[$i]['is_payed'] = '交易超时';
				} else if ($orderlist[$i]['is_refund'] == 1) {
					$orderlist[$i]['is_payed'] = '已退款  交易关闭';
				} else if ($orderlist[$i]['consult_cancel'] == 1) {
					$orderlist[$i]['is_payed'] = '申请退单中';
				} else if ($orderlist[$i]['consult_cancel'] == 2) {
					$orderlist[$i]['is_payed'] = '退单处理中';
				} else if ($orderlist[$i]['consult_cancel'] == 3) {
					$orderlist[$i]['is_payed'] = '商家拒绝退单';
				}
			}else {
				// 正常订单状态$orderlist[$i]['is_appraised']
				if($orderlist[$i]['is_payed'] == 0) {
					$orderlist[$i]['is_payed'] = '待付款';
				}else if($orderlist[$i]['is_payed'] == 1 && $orderlist[$i]['is_send'] == 0) {
					$orderlist[$i]['is_payed'] = '等待卖家发货';
				}else if($orderlist[$i]['is_payed'] == 1 && $orderlist[$i]['is_send'] == 1 && $orderlist[$i]['is_signed'] == 0) {
					$orderlist[$i]['is_payed'] = '等待收货';
				} else if ($orderlist[$i]['is_payed'] == 1 && $orderlist[$i]['is_send'] == 1 && $orderlist[$i]['is_signed'] == 1 && $orderlist[$i]['is_appraised'] == 0) {
					$orderlist[$i]['is_payed'] = '交易成功  待评价';
				} else if ($orderlist[$i]['is_payed'] == 1 && $orderlist[$i]['is_send'] == 1 && $orderlist[$i]['is_signed'] == 1 && $orderlist[$i]['is_appraised'] == 1) {
					$orderlist[$i]['is_payed'] = '交易成功  已评价';
				}else {
					$orderlist[$i]['is_payed'] = '暂时无法获取订单状态';
				}
			}
				
			if ($orderlist[$i]['receive_status'] == '0'){
				$orderlist[$i]['receive_status'] = '未接收';
			}else {
				$orderlist[$i]['receive_status'] = '已接收';
			}
				
			//时间格式化
			$orderlist[$i]['order_time'] = timetodate( $orderlist[$i]['order_time'] );
			
			//销毁不用显示在导出订单表中的字段timeout_cancel,is_refund,consult_cancel,is_send,is_signed,is_appraised，放在所有处理的最后
			unset($orderlist[$i]['timeout_cancel'], $orderlist[$i]['is_refund'], $orderlist[$i]['consult_cancel'],$orderlist[$i]['is_send'],$orderlist[$i]['is_signed'],$orderlist[$i]['is_appraised']);
		}
		// 准备标题准备打印
		$title = array (
				0 => '下单时间',
				1 => '订单编号',
				2 => '顾客编号',
				3 => '下单人名',
				4 => '联系电话',
				5 => '总价（元）',
				6 => '支付方式',
				7 => '实付款（元）',
				8 => '店铺受理',
				9 => '订单状态',
				10 => '快递单号',
				11 => '收货人',
				12 => '收货人电话',
				13 => '收货人地址',
				14 => '备注'
		);
	//p($_SESSION['curSubbranch']['subbranch_name']);die;
	$sub_name = $_SESSION['curSubbranch']['subbranch_name'];
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $orderlist, $sub_name.'订单详情'.time(), '所有订单信息一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
	/**
	 * 快递信息查看与编辑处理
	 */
	public function deliverProduct(){
		$expmap = array(
				'order_id' => I('oid'),
				'e_id' => $_SESSION['curSubbranch']['e_id'],
				'visual_number' => I('vid'),
				'is_del' => 0
		);
		$expinfo = M('order_express')->where($expmap)->find();
	
		if($expinfo['deliver_time'] == '-1' || $expinfo['deliver_time'] == ''){
			$expinfo['deliver_time'] = '';
		}else {
			$expinfo['deliver_time'] = timetodate($expinfo['deliver_time']);
		}
		$this->expinfo = $expinfo;
		$this->display();
	}
	
	/**
	 * 确认快递信息。
	 */
	public function deliverConfirm(){
		$eflag = I('eflag');
		//快递信息表要更新的内容
		$deliverdata = array(
				'express_number' => I('en'),
				'express_company' => I('cc'),
				'express_fee' => I('ef'),
				'deliver_time' => strtotime(I('st')),
				'send_address' => I('ea'),
				'remark' => I('er'),
		);
	
		if($eflag){
			$deliverdata['express_id']=I('ei');
			$eresult = M('express')->save($deliverdata);
		}else {
			$deliverdata['express_id'] = md5(uniqid(rand(),true));
			$eresult = M('express')->add($deliverdata);
		}
	
		//订单主表要更新的内容
		$orderdata = array(
				'order_id' => I('eo'),
				'e_id' => $_SESSION['curSubbranch']['e_id'],
				'express_id' => $deliverdata['express_id'],
				'express_fee' => I('ef'),
				'receive_person' => I('rn'),
				'receive_tel' => I('rt'),
				'receive_address' => I('ra')
		);
		$oresult = M('ordermain')->save($orderdata);
		if ($eresult || $oresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '快递信息设置成功!',
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '快递信息设置失败，请检查网络状况!',
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 备注订单确认。
	 */
	public function remarkOrderConfirm() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
	
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
			$ajaxresult ['errCode'] = 10001;
			$ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 协商撤销订单操作。
	 */
	public function negotiateCancelOrder() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Subbranch/ProOrder/subOrderView', '', '', true ) ); // 防止恶意进入
	
		// 准备要标记发货的订单数组
		$cancelmap = array (
				'order_id' => I('nego'),
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'], 									// 当前商家下
				'is_del' => 0
		);
		$cancelresult = M ( 'ordermain' )->where ( $cancelmap )->setField ( 'e_mark_del', 1 ); 		// 订单标记商家撤销
	
		// to do cancel order...
		// to tip customer
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
	
}
?>