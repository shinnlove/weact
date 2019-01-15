<?php
/**
 * 订单退单控制器。
 * @author 赵臣升。
 */
class RefundAction extends Action {
	
	/**
	 * 订单退单调起函数。
	 * @param string $order_id 订单编号
	 * @return array $refundresult 订单退单是否成功
	 * @property number errCode 错误码
	 * @property string errMsg 错误信息
	 */
	public function orderRefund($order_id = '') {
		$refundresult = array (
				'errCode' => 10001, 
				'errMsg' => "网络繁忙，请稍后再试！"
		); // 退单结果
		
		$maintable = M ( 'ordermain' ); 	// 订单主表
		$detailtable = M ( 'orderdetail' ); // 订单子表
		
		// 进行参数的检验
		
		// 订单编号不允许为空
		if (empty ( $order_id )) {
			$refundresult ['errCode'] = 10001;
			$refundresult ['errMsg'] = "订单退单失败，订单编号不能为空！";
			return $refundresult;
		}
		
		// 检测是否存在该订单编号的该退订单!（加上时间判断,未超过七天）
		$ordermap = array (
				'order_id' => $order_id,
				'is_refund' => 0, 		// 未退款
				'consult_cancel' => 0, 	// 未协商撤销
				'timeout_cancel' => 0, 	// 未超时取消
				'is_del' => 0
		);
		$orderinfo = $maintable->where ( $ordermap )->find ();
		if (! $orderinfo) {
			$refundresult ['errCode'] = 10002;
			$refundresult ['errMsg'] = "订单退单失败，该顾客不存在该笔订单或该笔订单已撤销/退款！";
			return $refundresult;
		}
		
		// 判断订单在不在退款范围内，如果没有签收肯定可以退；签收了，一个礼拜之内也可以退。
		
		
		return $refundresult;
	}
	
}
?>