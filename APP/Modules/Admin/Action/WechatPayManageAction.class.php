<?php
/**
 * 微信支付管理控制器
 * @author hufuling
 */
class WechatPayManageAction extends PCViewLoginAction {
	
	/**
	 * 读取所有微信支付通知数据，用于前台easyUI显示
	 */
	public function wechatPayView(){
		$this->display();
	}
	
}
?>