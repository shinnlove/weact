<?php
class CustomerAction extends Action {
	
	/**
	 * 从customerinfo表中得到顾客信息的函数。
	 * 为了保证客户编号确定不重复，检测的时候再带上一个企业编号。
	 * 
	 * @param string $e_id
	 *        	企业编号
	 * @param string $customer_id
	 *        	顾客编号
	 * @return array $customerinfo 顾客信息
	 */
	public function getCustomerInfo($e_id = '', $customer_id = '') {
		$customerinfo = array ();
		if (! empty ( $customer_id )) {
			$customermap = array (
					'customer_id' => $customer_id,
					'is_del' => 0 
			);
			$customerinfo = M ( 'customerinfo' )->where ( $customermap )->find ();
		}
		return $customerinfo;
	}
}
?>