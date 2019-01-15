<?php
class CouponAction extends Action {
	
	/**
	 * 得到优惠券信息的函数。
	 * 
	 * @param string $coupon_id
	 *        	优惠券编号
	 * @return array $couponinfo 优惠券信息
	 */
	public function getCouponInfo($coupon_id = '') {
		$couponinfo = array ();
		if (! empty ( $coupon_id )) {
			$couponmap = array (
					'coupon_id' => $coupon_id,
					'is_del' => 0 
			);
			$couponinfo = M ( 'coupon' )->where ( $couponmap )->find ();
		}
		return $couponinfo;
	}
	
	/**
	 * 通过优惠券信息和顾客信息发送优惠券到某顾客账号里的函数。
	 * 
	 * @param string $couponinfo
	 *        	要发送的优惠券信息
	 * @param string $customerinfo
	 *        	要发送到的顾客信息
	 * @return boolean $addresult 发送优惠券成功或失败的标记
	 */
	public function addCustomerCoupon($couponinfo = NULL, $customerinfo = NULL) {
		$addresult = false; // 默认添加优惠券标记失败
		$batchnumber = time(); // 批量有序的编号
		if (! empty ( $couponinfo ) && ! empty ( $customerinfo )) {
			$cctable = M ( 'customercoupon' );
			$checksend = array(
					'e_id' => $couponinfo ['e_id'],
					'coupon_id' => $couponinfo ['coupon_id'],
					'customer_id' => $customerinfo ['customer_id'],
					'is_del' => 0
			);
			$exist = $cctable->where( $checksend )->find();
			if(! $exist){
				// 开始记录一条顾客优惠券信息
				$customercoupon = array(
						'customercoupon_id' => md5( uniqid( rand(), true) ),
						'e_id' => $couponinfo ['e_id'],
						'subbranch_id' => $customerinfo ['subbranch_id'],
						'coupon_id' => $couponinfo ['coupon_id'],
						'coupon_name' => $couponinfo ['coupon_name'],
						'customer_id' => $customerinfo ['customer_id'],
						'get_time' => time(),
						//'coupon_sncode' => 'NZQ'.$batchnumber,
						//'coupon_password' => randCode(8, 1)
				);
				$addresult = M ( 'customercoupon' )->add( $customercoupon );
			}
		}
		return $addresult;
	}
	
	/**
	 * 向某个企业某个用户发送图文信息提醒
	 * 
	 * @param array $einfo
	 *        	企业信息
	 * @param array $customerinfo
	 *        	接收提醒的用户微信号
	 * @param string $msgnews_id
	 *        	要提醒的图文消息编号
	 * @return boolean $sendstatus 发送提醒的状况
	 */
	public function sendCouponRemind($einfo = NULL, $customerinfo = NULL, $msgnews_id = '') {
		$sendstatus = false; // 先默认没法送成功
		if (! empty ( $msgnews_id )) {
			$swc = A ( 'Service/WeChat' );
			$sendstatus = $swc->sendLocalNews ( $einfo, $customerinfo ['openid'], $msgnews_id );
		}
		return $sendstatus;
	}
}
?>