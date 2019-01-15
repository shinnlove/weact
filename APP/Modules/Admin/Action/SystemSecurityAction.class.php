<?php
class SystemSecurityAction extends PCViewLoginAction {
	/**
	 * 安全中心：商家可在其中修改账户登录密码
	 */
	public function safetyCenter(){
		$this->display();
	}
}
?>