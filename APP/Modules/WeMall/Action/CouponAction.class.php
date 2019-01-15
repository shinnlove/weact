<?php
/**
 * 线下微猫商城优惠券控制器。
 * @author 赵臣升，胡福玲。
 * CreateTime:2015/05/27 22:24:25.
 */
class CouponAction extends GuestMallAction {
	/**
	 * 优惠券信息页面视图。
	 */
	public function couponInfo() {
		$coupon_id = I ( 'cid' ); // 接收优惠券编号
		$fromwhere = $_REQUEST ['from'];//可能来自订单或者来自我的账户中心
		
		if (empty ( $coupon_id )) {
			$this->error ( "优惠券参数错误！" ); 
		}
		
		// 查找优惠券信息的条件范围
		$couponmap = array (
				'coupon_id' => $coupon_id, 
		);
		//如果是从订单确认页面跳转过来，只推送可使用的优惠券信息
		if ($fromwhere == 'order') {
			$couponmap['is_del'] = 0;
		}
		
		$couponinfo = M ( 'coupon' )->where ( $couponmap )->find (); // 查找优惠券信息
		$couponinfo ['coupon_cover'] = assemblepath ( $couponinfo ['coupon_cover'] ); // 组装优惠券封面图片路径
		
		if (! $couponinfo) {
			$this->error ( "优惠券不存在！" );
		}
		
		$this->couponinfo = $couponinfo; // 向前台推送优惠券信息
		$this->display (); 
	}
	
	/**
	 * 顾客领取优惠券页面视图。
	 */
	public function sendCoupon() {
		$coupon_id = I ( 'coupon_id' );
		$coupondata = array (
				'coupon_id' => $coupon_id, 	// 当前优惠券编号
				'e_id' => $this->eid, 		// 当前商家
		);
		$couponinfo = M ( 'coupon' )->where ( $coupondata )->find(); // 找到这张优惠券
		if (! $couponinfo) {
			$this->error ( "不存在的优惠券，无法领取！" );
		}
		$this->couponinfo = $couponinfo;
		$this->cid = $coupon_id;
		$this->display ();
	}
	
}
?>