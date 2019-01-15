<?php
/**
 * 微信支付管理ajax请求控制器。
 * @author hufuling
 */
class WechatPayManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * 获取订单的支付信息。
	 */
	public function getOrderPayInfo(){
		if (! IS_POST) _404('Sorry, Http://404, Not Found.',U('Admin/WechatPayManage/wechatPayView','','',true));
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'receive_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$orderpaymap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$orderpaytbl = M ( 'order_wechatpay' );
		$total = $orderpaytbl->where ( $orderpaymap )->count (); // 计算总数
		$orderpaylist = array ();
	
		if($total){
			$orderpaylist = $orderpaytbl->where ( $orderpaymap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$paynum = count($orderpaylist);
			for($i = 0; $i < $paynum; $i ++){
				//所属分店处理
				if ($orderpaylist [$i] ['subbranch_id'] != null && $orderpaylist [$i] ['subbranch_id'] != '-1' && $orderpaylist [$i] ['subbranch_id'] != ''){
					$submap = array(
							'subbranch_id' => $orderpaylist [$i] ['subbranch_id'],
							'is_del' => 0
					);
					$subinfo = M('subbranch')->where($submap)->find();
					if($subinfo){
						$orderpaylist [$i] ['subbranch_id'] = $subinfo['subbranch_name'];
					}else {
						$orderpaylist [$i] ['subbranch_id'] = '暂无门店信息';
					}
				}else {
					$orderpaylist [$i] ['subbranch_id'] = '暂无门店信息';
				}
	
				//格式化时间数据
				$orderpaylist [$i] ['receive_time'] = timetodate( $orderpaylist [$i] ['receive_time'] );
				$orderpaylist [$i] ['order_time'] = timetodate( $orderpaylist [$i] ['order_time'] );
				$orderpaylist [$i] ['time_end'] = strtotime( $orderpaylist [$i] ['time_end'] );
				$orderpaylist [$i] ['time_end'] = timetodate( $orderpaylist [$i] ['time_end'] );
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $orderpaylist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 批量删除账单信息
	 */
	public function delwechatPay(){
		$delnidlist = I ( 'rowdata' );
		$delnmap = array(
				'paynotify_id' => array('in', $delnidlist),
				'is_del' => 0
		);
		$delnresult = M('wechatpaynotify')->where($delnmap)->setField('is_del',1);
	
		$ajaxresult = array ();
		if($delnresult){
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
	 * 按条件查询对账单
	 */
	public function conditionSearchPay(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/WechatPayManage/wechatPayView', '', '', true ) ); // 防止恶意进入
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'receive_time'; 				// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'is_del' => 0
		);
	
		if($condition == 'visual_number'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if($condition == 'time_end'){
			//日期范围查询
			$timeStart=strtotime(I('startsearchcontent'));
			$timeEnd = strtotime(I('endsearchcontent'));
			//$timeStart = date('YmdHis',strtotime(I('startsearchcontent')));
			//$timeEnd = date('YmdHis',strtotime(I('endsearchcontent')));
			if(($timeStart == null || $timeStart == '') && $timeEnd != null && $timeEnd != ''){
				$searchmap [$condition] = array ( 'elt', date('YmdHis',$timeEnd));
			}else if($timeStart != null && $timeStart != '' && ($timeEnd == null || $timeEnd == '')){
				$searchmap [$condition] = array ( 'egt', date('YmdHis',$timeStart));
			}else $searchmap [$condition] = array ( 'between', array(date('YmdHis',$timeStart),date('YmdHis',$timeEnd)));
		}
	
		$oinfoview = M ( 'order_wechatpay' ); 														// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
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
	
				//格式化时间数据
				$searchinfo [$i] ['receive_time'] = timetodate( $searchinfo [$i] ['receive_time'] );
				$searchinfo [$i] ['order_time'] = timetodate( $searchinfo [$i] ['order_time'] );
				$searchinfo [$i] ['time_end'] = strtotime( $searchinfo [$i] ['time_end'] );
				$searchinfo [$i] ['time_end'] = timetodate( $searchinfo [$i] ['time_end'] );
			}
		}
		$json = '{"total":' . $searchtotal . ',"rows":' . json_encode ( $searchinfo ) . '}';
		echo $json;
	}
	
}
?>