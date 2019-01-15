<?php
/**
 * 活动管理控制器。
 * @author Administrator
 *
 */
class ActivityAction extends PCViewLoginAction {
	
	/**
	 * 跳转到添加活动商品页面
	 */
	public function preaddProduct() {
		$activity_id = I ( 'activity_id' );
		$this->assign ( 'activity_id', $activity_id );
		$this->display ();
	}
	
	// 添加活动前
	public function preaddActivity() {
		$this->display ();
	}
	
	// 第三步-----添加活动成功页面
	public function success() {
		$this->display ();
	}
	
	//进入我的活动页面
	public function preMyActivity() {
		$this->display ();
	}
	
}
?>