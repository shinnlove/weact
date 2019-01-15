<?php
/**
 * 顾客历史订单控制器， 该控制器管理顾客历史订单的实时查询。
 * @author 微动团队，胡睿。
 * CreateTime 2015/09/04 16:47:00
 */
class CustomerOrderAction extends GuideAppCommonAction {
	/**
	 * 展示顾客历史订单列表视图
	 * 该视图由三方在APP主界面进行跳转，带上三个参数：1、商家编号；2、分店编号；3、导购编号
	 * 微动接收参数，查询该顾客的所有订单(超时取消的不算)默认显示已付款为高亮状态
	 * 数据按下单时间进行降序排序，分页读取
	 */
	public function customerOrderList() {
		// 接收参数并查数据库，分页读取数据
		$e_id = $this->eid;
		$customer_id =  I ( 'cusid', '' );			
		$visual_number = I ( 'visualnumber', '' );
		$query_type = I ( 'querytype', 0 );	// 默认0，查询全部订单
		$startindex = 0;
		$count = 10;
		$firstInitData = true;
		
		$jsondata = $this->orderListLimit ( $e_id, $customer_id, $visual_number, $query_type, $startindex, $count, $firstInitData );
		$finaljson = json_encode ( $jsondata );
		
		$this->customerorderjson = $finaljson;
		$this->display ();
	}
	
	/**
	 * 当要读取下一页订单数据或要进行刷新订单数据时候，请求该函数进行ajax处理。
	 * 该顾客的所有订单、超时取消的不算
	 * 因为这是ajax处理，所以post的时候，带上了要查询的页数nextStart（从第几页开始，默认查询一页）
	 */
	public function requestCustomerOrder() {
		// 接收参数读取订单
		$e_id = $this->eid;		
		$customer_id =  I ( 'cusid', '' );		
		$visual_number = I ( 'visualnumber', '' );
		$query_type = I ( 'querytype', 0 );	// 默认0，查询全部订单
		$startindex = $_REQUEST ['nextstart'];
		$count = 10;
		// 正式查询信息		
		$ajaxinfo = $this->orderListLimit ( $e_id, $customer_id, $visual_number, $query_type, $startindex, $count);
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 分页获取某顾客的分类订单数据并且打包推送到前台的查询函数（被显示页面和查询订单ajax处理函数调用）
	 *
	 * @param string $e_id
	 *        	商家ID
	 * @param string $customer_id
	 *        	顾客ID
	 * @param string $visual_number
	 *        	搜索框内容(订单编号visual_number)模糊匹配
	 * @param int $query_type 为了跟数据库保持一致，这里设置全部为-1
	 *        	查询类别（全部-1、待付款0、待发货1、待收货2、待评价3）
	 *			全部0 :默认,表示显示全部未取消订单 normal_status >=0
	 *      	待付款1:status_flag = 0, normal_status = 0, is_del = 0;
	 *        	待发货2:status_flag = 0, normal_status = 1,is_del = 0;
	 *			待收货3:status_flag = 0, normal_status = 2,is_del = 0;
	 *        	待评价4:status_flag = 0, normal_status = 3, is_del = 0;
	 * @param number $startindex
	 *        	从第几条开始
	 * @param number $count
	 *        	想要读取几条
	 * @param bool $firstInitData
	 *        	是否是第一次读取
	 */
	public function orderListLimit($e_id = '', $customer_id = '', $visual_number = '', $query_type = 0, $nextstart = 0, $perpage = 10, $firstInitData = FALSE) {
		$ordermain = M ( "order_cinfo" );
		$orderlist = array (); // 本次查询的订单列表
		
		// 建立查询条件 (已付款条件类别)
		$ordermap = array (
				'e_id' => $e_id,
				'customer_id' => $customer_id,
				'normal_status'=>array('egt',0),	// 被取消的订单不算
				'visual_number' => array ( 'like', '%' . $visual_number . '%' ), // 模糊匹配
				'is_del' => 0, 
		);
		
		// 剩下的查询条件(在已付款的条件下，添加）
		if ($query_type == 1) {	
			// 待付款
			$ordermap ['status_flag'] = 0;
			$ordermap ['normal_status'] = 0;
		} else if ($query_type == 2) { 
			// 待发货
			$ordermap ['status_flag'] = 0;
			$ordermap ['normal_status'] = 1;
		} else if ($query_type == 3) { 
			// 待收货
			$ordermap ['status_flag'] = 0;
			$ordermap ['normal_status'] = 2;
		} else if ($query_type == 4) { 
			// 待评价
			$ordermap ['status_flag'] = 0;
			$ordermap ['normal_status'] = 3;
		}
		
		$totalcount = $ordermain->where ( $ordermap )->count (); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		
		if ($realgetnum) {
			$orderlist = $ordermain->where ( $ordermap )->order ( 'order_time desc' )->limit ( $nextstart, $realgetnum )->select ();
			$listcount = count ( $orderlist );
			// 对查询出来的数据进行相应的处理（改编）
			for($i = 0; $i < $listcount; $i ++) {
				$orderlist [$i] ['order_time'] = timetodate ( $orderlist [$i] ['order_time'] ); // 对$orderlist信息格式化时间
			}
		}
		
		$ajaxresult = array (
				'data' => array (
						'orderlist' => $orderlist, // 订单列表
				),
				'nextStart' => $newnextstart,
				'totalcount' => $totalcount
		);
		
		if (! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
	
	/**
	 * 展开某个订单，查询订单中的所有商品详情
	 *
	 * @param string $order_id	
	 * 			订单编号
	 */
	public function getOrderDetailByID() {
		
		// 准备返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！",
				'data' => array () 
		);
		
		$order_id = I ( 'oid', '' );
		if (empty ( $order_id )) {	// 没有传订单编号直接返回
			$ajaxresult['errCode'] = '10002';
			$ajaxresult['errMsg'] = '订单编号不能为空!';
			$this->ajaxReturn ( $ajaxresult ); 
		}
		
		// 准备订单信息
		$detailprolist = array ();
		
		$orderview = M ( "orderinfo_view" );
		// 建立查询条件 (已付款条件类别)
		$ordermap = array (
				'order_id' => $order_id,				
				'normal_status'=>array('egt',0),	// 被取消的订单不算
				'is_del' => 0 
		);
		$detailprolist = $orderview->where ( $ordermap )->select ();
		
		// 对查询出来的数据进行相应的处理
		if ($detailprolist) {
			// 返回真实得到的条数
			$realcount = count ( $detailprolist );
			// 数据的变换（改编）
			for($i = 0; $i < $realcount; $i ++) {
				// 对guideinfo信息进行一定的变换
				$detailprolist [$i] ['order_time'] = timetodate ( $detailprolist [$i] ['order_time'] );
				$detailprolist [$i] ['macro_path'] = assemblepath ( $detailprolist [$i] ['macro_path'] );
				$detailprolist [$i] ['micro_path'] = assemblepath ( $detailprolist [$i] ['micro_path'] );
			}		
			$ajaxresult ['data'] ['detailprolist'] = $detailprolist;
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		} 
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
	
}
?>