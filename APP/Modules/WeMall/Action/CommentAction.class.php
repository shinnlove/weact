<?php
/**
 * 导购商品评价处理控制器。
 * @author 赵臣升。
 * CreateTime:2015/06/03 21:38:25.
 */
class CommentAction extends LoginMallAction {
	
	/**
	 * 导购商品评论。
	 */
	public function guideProduct() {
		// 接收订单编号
		$order_id = I ( 'oid', '' ); // 接收订单编号
		if (empty ( $order_id )) {
			$this->error ( "订单参数错误！" );
		}
		
		// 查询订单是否存在（现在状态改成status了，2015/08/24 17:29:36）
		$ordermap = array (
				'order_id' => $order_id, 	// 订单编号
				'status_flag' => 0, 		// 订单处于正常状态
				'normal_status' => 3, 		// 已经被签收了的订单才具备评价的可能性
				'is_del' => 0, 				// 没有被删除的
		);
		$orderlist = M ( "orderinfo_view" )->where ( $ordermap )->select (); // 从订单视图里找出这笔订单信息
		if (! $orderlist) {
			$this->error ( "不存在的订单或不满足评价条件！" );
		}
		
		// 通过检查，可以评价订单
		$orderguide = array (); 	// 订单所属导购
		$orderproduct = array (); 	// 订单所包含的商品
		$orderlistcount = count ( $orderlist ); // 统计订单购买的商品数量
		
		// 检测订单是否有专属导购
		if (! empty ( $singleinfo ['guide_id'] ) && $singleinfo ['guide_id'] != "-1") {
			// 如果有专属导购，则查询导购信息
			$guidemap = array (
					'guide_id' => $singleinfo ['guide_id'], // 订单上导购编号
					'is_del' => 0
			);
			$guideinfo = M ( "shopguide_subbranch" )->where ( $guidemap )->find (); // 导购分店视图
			$guideinfo ['headimg'] = assemblepath ( $guideinfo ['headimg'] ); // 组装导购头像
			if ($guideinfo) {
				array_push ( $orderguide, $guideinfo ); // 如果该导购还在，将信息给到全局变量中
			}
		}
		
		// 获得订单商品信息
		for($i = 0; $i < $orderlistcount; $i ++) {
			$singleproduct = array (
					'subbranch_id' => $orderlist [$i] ['subbranch_id'], 				// 分店编号
					'product_id' => $orderlist [$i] ['product_id'], 					// 商品编号
					'product_name' => $orderlist [$i] ['product_name'], 				// 商品名称
					'unit_price' => $orderlist [$i] ['unit_price'], 					// 订单购买时商品价格
					'micro_path' => assemblepath ( $orderlist [$i] ['micro_path'] ), 	// 商品小图
			);
			array_push ( $orderproduct, $singleproduct ); // 将商品信息压入订单商品里
		}
		
		// 推送到页面上等待评价
		$orderinfo = array (
				'data' => array (
						'guidelist' => $orderguide, 	// 订单所属导购信息
						'productlist' => $orderproduct, // 订单所有商品信息
				),
		);
		$this->oinfo = json_encode ( $orderinfo ); 	// 将订单信息打到页面上
		$this->oid = $order_id; 					// 订单编号
		$this->display ();
	}
	
}
?>