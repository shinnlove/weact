<?php
class CateOrderAction extends CommonAction {
	
	/**
	 * 订单视图页面。
	 */
	public function orderView() {
		$this->e_id = $_SESSION ['curSubbranch'] ['e_id'];
		$this->subbranch_id = $_SESSION ['curSubbranch'] ['subbranch_id'];
		$this->display();
	}
	
	/**
	 * easyUI的post请求，初始化读取店铺餐饮订单数据。
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'orderView', '', '', 1, false ) );
		
		$scate = A ('Service/Cate');
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'order_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
		
		$jsoninfo = $scate->getOrderListByPage( $_SESSION ['curSubbranch'] ['e_id'], $pagenum, $rowsnum, $sort, $order );
		
		if ($jsoninfo ['total']) {
			$json = '{"total":' . $jsoninfo ['total'] . ',"rows":' . json_encode ( $jsoninfo ['rows'] ) . '}';  // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                              // 否则就输出空数组
		}
		echo $json;
	}
	
	/**
	 * 点击展开订单详情的post请求。
	 */
	public function getOrderDetail() {
		$scate = A ( 'Service/Cate' );
		$this->ajaxReturn( $scate->getOrderCateInfoById( I ( 'order_id' ) ) );
	}
	
	/**
	 * 分店系统确认接收某订单的post处理函数。
	 */
	public function orderReceiveConfirm() {
		$scate = A ( 'Service/Cate' );
		$liststring = I ( 'orderlist' );			// 可能是若干个订单主键拼接成的string
		$orderlist = explode(",", $liststring);		// 解析成数组
		$receiveresult = $scate->subbranchOrderReceive( $orderlist );
		if($receiveresult){
			for($i = 0; $i < count( $orderlist ); $i ++) {
				$scate->sendOrderInfo( $orderlist );	// 通知这些用户
			}
			$this->ajaxReturn( array( 'errCode' => 0, 'errMsg' => 'ok' ) );
		}else{
			$this->ajaxReturn( array( 'errCode' => 50001, 'errMsg' => '订单接收失败!' ) );
		}
	}
	
	/**
	 * 分店系统确认某订单已经支付的post处理函数。
	 */
	public function orderPayedConfirm() {
		$order_id = I ( 'order_id' );
		$scate = A ( 'Service/Cate' );
		$confirmresult = $scate->subbranchOrderPay( $order_id );
		if($confirmresult){
			$scate->sendOrderPayInfo( $order_id );				// 分店后台一次只能缴费一笔订单
			$this->ajaxReturn( array( 'errCode' => 0, 'errMsg' => 'ok' ) );
		}else{
			$this->ajaxReturn( array( 'errCode' => 50002, 'errMsg' => '订单缴费失败!' ) );
		}
	}
	
	/**
	 * 分店系统确认餐品已上齐的post处理函数。
	 */
	public function sendAllConfirm() {
		$scate = A ( 'Service/Cate' );
		$liststring = I ( 'orderlist' );				// 可能是若干个订单主键拼接成的string
		$orderlist = explode(",", $liststring);			// 解析成数组
		$sendresult = $scate->subbranchOrderCompleted( $orderlist );		// 调用完成订单函数
		if($sendresult){
			for($i = 0; $i < count( $orderlist ); $i ++) {
				$scate->sendOrderCompletedInfo( $orderlist );	// 通知这些用户
			}
			$this->ajaxReturn( array( 'errCode' => 0, 'errMsg' => 'ok' ) );
		}else{
			$this->ajaxReturn( array( 'errCode' => 50001, 'errMsg' => '订单接收失败!' ) );
		}
	}
	
	/**
	 * 条件查询订单信息的post处理函数。
	 */
	public function conditionSearchOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'orderView', '', '', 1, false ) );
		
		$scate = A ('Service/Cate');
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'order_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
		
		$conditon = array(
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
				$_REQUEST ['searchcondition'] =>  array( 'like', '%' . $_REQUEST ['searchcontent'] . '%' ),
				'is_del' => 0
		);
		
		$jsoninfo = $scate->getOrderInfoByPageQuery( $conditon, $pagenum, $rowsnum, $sort, $order );
		
		if ($jsoninfo ['total']) {
			$json = '{"total":' . $jsoninfo ['total'] . ',"rows":' . json_encode ( $jsoninfo ['rows'] ) . '}';  // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                              // 否则就输出空数组
		}
		echo $json;
	}
	
	/**
	 * 检测分店有没有新订单。
	 */
	public function checkNewOrder() {
		$checkmap = array(
				'e_id' => I ( 'e_id' ),
				'receive_status' => 0,
				'is_del' => 0
		);
		$count = M( 'cateordermain' )->where( $checkmap )->count();
		$msg = array(
				'errCode' => 0,
				'errMsg' => 'ok',
				'newordernumber' => $count
		);
		$this->ajaxReturn( $msg );
	}
	
	/**
	 * 对订单添加备注信息。
	 */
	public function remarkOrder() {
		$scate = A ( 'Service/Cate' );
		$ajaxinfo = array(
				'order_id' => I ( 'oid' ),
				'remark' => I ( 'remark' )
		);
		$remarkresult = $scate->remarkOrder ( $ajaxinfo ['order_id'], $ajaxinfo ['remark'] );
		if($remarkresult) {
			$resultinfo = array(
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$resultinfo = array(
					'errCode' => 10000,
					'errMsg' => '订单备注失败'
			);
		}
		$this->ajaxReturn( $resultinfo );
	}
}
?>