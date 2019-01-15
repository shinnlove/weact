<?php
/**
 * 优惠券视图控制器。
 * @author Shinnlove
 *
 */
class CouponAction extends PCViewLoginAction {
	/**
	 * 店铺优惠券视图。
	 */
	public function preMyCoupon(){
		$this->display();
	}
	
	public function shopCoupon(){
		$subbranchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$singlesubbranch = M ( 'subbranch' )->where ( $subbranchmap )->limit ( 1 )->select ();
		$this->sid = $singlesubbranch [0] ['subbranch_id']; // 选其中一家店铺的主键
		$this->display();
	}
	
	/**
	 * 添加优惠券本身信息视图。（暂时已经不用）
	 */
	public function preaddCoupon() {
		$this->display ();
	}
	
	//添加商品前
	public function preaddCouponProduct(){
		$coupon_id = I ( 'coupon_id' );
		$this->assign ( 'coupon_id', $coupon_id );
		$this->display();
	}
	
	//用户领取的优惠券
	public function preUserCoupon(){
		$this->display();
	}
	
	/**
	 * 添加优惠券适用分店。
	 */
	public function addCouponSubbranch() {
		$coupon_id = I ( 'coupon_id' );
		$this->assign ( 'coupon_id', $coupon_id );
		$this->display();
	}
	
	/**
	 * 优惠券勾选分店视图。
	 */
	public function frameSubbranch() {
		$this->display ();
	}
	
	/**
	 * 添加优惠券商品。
	 */
	public function addCouponProduct(){
		$this->display ();
	}
	
	/**
	 * 产品树形展示视图。
	 */
	public function frameTreeProduct() {
		$this->display ();
	}
	
	/**
	 * 添加优惠券第一步操作视图。
	 */
	public function addCouponFirst() {
		$this->display ();
	}
	
	/**
	 * 添加优惠券第二步操作视图——抵扣券相关事宜设置。
	 */
	public function addCouponSecond1() {
		$this->display ();
	}
	
	/**
	 * 添加优惠券第二步操作视图——折扣券相关事宜设置。
	 */
	public function addCouponSecond2() {
		$this->display ();
	}
	
	/*
	 * =====================以下是526临时使用的最简洁版本优惠券=====================
	 */
	
	/**
	 * 优惠券类别规则简洁化：固定O2O类别为线上优惠券、价格限制为所有商品适用、发放规则为注册线上会员；
	 * 优惠券种类：抵扣券、折扣券、特价券
	 */
	public function addBriefCoupon(){
		$couponid = md5 ( uniqid ( rand (), true ) );
		$this->couponid = $couponid;
		$this->display();
	}
	
	/**
	 * 查看优惠券所属商品页面
	 */
	public function couponFitProduct(){
		$cid = I('cid');
		$this->coupon_id = $cid;
		$this->display();
	}
	
	/**
	 * 查看优惠券所属分店页面
	 */
	public function couponFitSubbranch(){
		$cid = I('cid');
		$this->coupon_id = $cid;
		$this->display();
	}
}
?>