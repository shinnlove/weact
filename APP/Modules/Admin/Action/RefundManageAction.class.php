<?php 
/**
 * 退款控制器
 */
class RefundManageAction extends PCViewLoginAction {
	/**
	 * 退款一览视图
	 */
	public function refundApplyView(){
		$this->display ();
	}
	
	/**
	 * 成功过退款一览视图
	 * 
	 */
	public function refundSuccessedView(){
		$this->display();
	}
	
	
	
}

?>