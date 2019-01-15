<?php 
/**
 * 订单管理 ajax请求控制器
 * 主要针对退账订单请求
 */
class RefundManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyui获取申请退款订单信息
	 */
	public function getApplyRefundOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/RefundManage/refundApplyView', '', '', true ) ); // 防止恶意进入
		//p($_SESSION);die;
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
		$ordermap = array(
				'e_id'=>$_SESSION ['curEnterprise'] ['e_id'], 
				'status_flag' => 1,
				'refund_status' => 1,
				'e_mark_del' => 0,	//没有被商家删除的
				'is_del' => 0
		);
		$total = M('ordermain') -> where($ordermap) -> count();
		$orderresult = array ();
		if($total){
			$orderresult = M('ordermain')->where($ordermap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select();
			for($i=0;$i<count($orderresult);$i++){
				$orderresult[$i]['order_time'] = timetodate($orderresult[$i]['order_time']);
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $orderresult ) . '}';
		echo $json;
	}
	
	/**
	 * easyui获取成功退款订单信息
	 */
	public function getSuccessRefundOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/RefundManage/refundSuccessedView', '', '', true ) ); // 防止恶意进入
		//p($_SESSION);die;
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
		$ordermap = array(
				'e_id'=>$_SESSION ['curEnterprise'] ['e_id'],
				'status_flag' => 1,
				'refund_status' => 2, 	//TODO
				'e_mark_del' => 0,	//没有被商家删除的
				'is_del' => 0
		);
		$total = M('ordermain') -> where($ordermap) -> count();
		$orderresult = array ();
		if($total){
			$orderresult = M('ordermain')->where($ordermap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select();
			for($i=0;$i<count($orderresult);$i++){
				$orderresult[$i]['order_time'] = timetodate($orderresult[$i]['order_time']);
				if($orderresult[$i]['refund_time']==-1){
					$orderresult[$i]['refund_time']='';
				}else{
					$orderresult[$i]['refund_time'] = timetodate($orderresult[$i]['refund_time']);
				}
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $orderresult ) . '}';
		echo $json;
	}
	
	/**
	 * 同意退款申请操作
	 */
	public function agreeRefund(){
		// 接收参数
		$orderid = I ( 'oid' ); // 要退款的订单编号
		$refundfee = I ( 'refundfee' ); // 要退款的金额
		
		// 准备全局变量
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
		
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
				'is_del' => 0
		);
		$wechatpayinfo = M ( 'secretinfo' )->where ( $epayconfigmap )->find (); // 找到商家的微信支付信息
		if (! $wechatpayinfo) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您没有设置微信支付信息，请先进行完善！";
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
		$malltype = $orderviewresult [0] ['mall_type']; // 提取malltype方便使用
		
		if (! $orderviewresult) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您所操作的订单的状态可能已发生改变，请刷新页面后重试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// step1.4 校验商家输入的退款金额是否合法
		if ($refundfee <= 0) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您输入的退款金额应该大于0！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if ($orderviewresult [0] ['pay_indeed'] < $refundfee) {
			// 视图中每个order_id对应的pay_indeed都是相同的
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "退款操作失败，您输入的退款金额不得高于订单总额！";
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
						$this->ajaxresult ['errCode'] = 10008;
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
				$this->ajaxresult ['errCode'] = 10009;
				$this->ajaxresult ['errMsg'] = "退款操作失败，积分信息添加失败！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
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
			$this->ajaxresult ['errCode'] = 10010;
			$this->ajaxresult ['errMsg'] = "退款操作失败，退款状态记录表添加失败！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 调用微信退款接口，向微信发送退款申请
		import ( 'Class.API.WeChatPayV3.WeActWxPay.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动平台安全支付SDK
		
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
			$this->ajaxresult ['errCode'] = 10011;
			$this->ajaxresult ['errMsg'] = "退款失败，" . $refundresult ['errMsg'];
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 拒绝退款
	 * @author Ltt
	 */
	public function rejectRefund(){
		//接收参数
		$rejectid = I('rejectid');
		$refundreason = I('refundreason');
	
		//校验order_id(拒绝的订单id)是否为空
		if (! isset ( $rejectid )) {
			$this->ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => "操作失败，您提交的订单号不能为空"
			);
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		$ordermap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'order_id' => $rejectid,
				'is_del' => 0
		);
		$orderviewresult = M ( 'orderinfo_view' )->where ( $ordermap )->find (); //查询该订单是否存在
		if( !$orderviewresult){
			$this->ajaxresult = array (
					'errCode' => 10003,
					'errMsg' => "该订单不存在，请核对！"
			);
			$this -> ajaxReturn($this->ajaxresult);
		}
	
		
		//实例化订单主表、订单流水表对象
		$ordertable = M ( 'ordermain' );
		$orderstatus = M ('orderstatusrecord');
		$ordertable -> startTrans();                   //启动事物
		//记录订单状态和理由
		$savedata = array(
				'refund_status'=>3,
				'refund_reason' => $refundreason		//退款理由
		);
		$saveResult = $ordertable ->where($ordermap) ->save ($savedata);
		if (! $saveResult) {
			$this->ajaxresult = array (
					'errCode' => 10004,
					'errMsg' => "操作失败，订单系统网络繁忙"
			);
			$ordertable->rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
			
		//记订单流水
		$orderstatusrecorddata = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'mall_type' => $orderviewresult ['mall_type'],
				'order_id' => $rejectid,
				'status_flag' => $orderviewresult ['status_flag'],
				'normal_status' => $orderviewresult ['normal_status'],
				'refund_status' => 3,
				'is_read' => 0,
				'add_time' => time ()
		);
		$sendstatusresult = M ( 'orderstatusrecord' )->add ( $orderstatusrecorddata );
		if (! $sendstatusresult) {
			$this->ajaxresult = array (
					'errCode' => 10005,
					'errMsg' => "操作失败，订单系统网络繁忙"
			);
			$ordertable->rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
	
		// 事务都成功，返回结果
		$ordertable->commit ();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 按条件查找申请退款订单
	 */
	public function conditionSearchRefundOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/RefundManage/refundApplyView', '', '', true ) ); // 防止恶意进入
		//p(I());die;
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
		
		// 根据不同查询条件定义searchmap
		$statustag = I ( 'statustag' );
		$onum = I ( 'onum' );
		$cnum = I ( 'cnum' );
		$rtel = I ('rtel');
		$timeStart = strtotime(I('startsearchcontent'));
		$timeEnd = strtotime(I('endsearchcontent'));
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'status_flag' => 1,
				'refund_status' => 1,
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
		// 过滤订单状态
		if ($statustag == "0") {
			$searchmap ['normal_status'] = 1; // 待发货
		} else if ($statustag == "1") {
			$searchmap ['normal_status'] = 2; // 待收货
		} else if ($statustag == "2") {
			$searchmap ['normal_status'] = 3; // 已收货
		} else if ($statustag == "3") {
			$searchmap ['normal_status'] = 4; // 已评价
		}
		
		// 过滤订单编号
		if (! empty ( $onum )) {
			$searchmap ['visual_number'] = array ( "like", "%" . $onum . "%" ); // 订单编号
		}

		// 过滤顾客编号
		if (! empty ( $cnum )) {
			$searchmap ['customer_id'] = array ( "like", "%" . $cnum . "%" ); // 订单编号
		}

		//过滤收货人电话
		if (! empty ( $rtel )) {
			$searchmap ['receive_tel'] = array ( "like", "%" . $rtel . "%" ); // 订单编号
		}
		
		/* $type = I('type');
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
		} */
		
		//日期范围查询
		if($timeStart != null || $timeStart != '' || $timeEnd != null && $timeEnd != '') {
			if(($timeStart == null || $timeStart == '') && $timeEnd != null && $timeEnd != ''){
				$searchmap ['order_time'] = array ( 'elt', $timeEnd);
			}else if($timeStart != null && $timeStart != '' && ($timeEnd == null || $timeEnd == '')){
				$searchmap ['order_time'] = array ( 'egt', $timeStart);
			}else $searchmap ['order_time'] = array ( 'between', array($timeStart,$timeEnd));
		}
		$ordermain = M ( 'order_cinfo' ); 															// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$searchinfo = array (); 																	// 搜索订单信息数组
		$searchtotal = $ordermain->where( $searchmap )->count (); 									// 查询所搜索现有记录总条数
        
		if($searchtotal) {
			$searchinfo = $ordermain->where( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 查询出easyUI需要的数据
			for($i = 0; $i < count ( $searchinfo ); $i ++) {
				//时间格式转换
				$searchinfo [$i] ['order_time'] = timetodate ( $searchinfo [$i] ['order_time'] ); 	//时间戳转换日期显示的处理
			}
		}
		$json = '{"total":' . $searchtotal . ',"rows":' . json_encode ( $searchinfo ) . '}';
		echo $json;
	}
	
	/**
	 * 按条件查找申请退款订单
	 */
	public function conditionSearchSuccessRefundOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/RefundManage/refundSuccessedView', '', '', true ) ); // 防止恶意进入
		//p(I());die;
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'is_refund' => 1,
				'e_mark_del' => 0, 																		// 没有被商家删除的
				'is_del' => 0
		);
		if($condition == 'visual_number' || $condition == 'customer_id'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if ($condition == 'is_signed' || $condition == 'is_payed' || $condition == 'is_send') {
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
		$ordermain = M ( 'ordermain' ); 															// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$searchinfo = array (); 																	// 搜索订单信息数组
		$searchtotal = $ordermain->where( $searchmap )->count (); 									// 查询所搜索现有记录总条数
		if($searchtotal) {
			$searchinfo = $ordermain->where( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 查询出easyUI需要的数据
			for($i = 0; $i < count ( $searchinfo ); $i ++) {
				//时间格式转换
				$searchinfo [$i] ['order_time'] = timetodate ( $searchinfo [$i] ['order_time'] ); 	//时间戳转换日期显示的处理
			}
		}
		$json = '{"total":' . $searchtotal . ',"rows":' . json_encode ( $searchinfo ) . '}';
		echo $json;
	}
	
}
?>