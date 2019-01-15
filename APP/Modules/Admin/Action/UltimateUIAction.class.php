<?php
/**
 * 本控制器主要控制用户设定的UI样式，目前主要是UI按钮。
 * @author Shinnlove
 *
 */
class UltimateUIAction extends PCViewLoginAction {
	/**
	 * UI列表风格页面。
	 */
	public function uiListEffect() {
		$tplinfo = array (
				'tplpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id']
		);
		$tplManage = A ( 'Admin/TplManage' );
		$this->ctresult = $tplManage->EntrustTemplate ( $tplinfo );
		$this->display ();
	}
	
	/**
	 * UI按钮初始化页面。
	 */
	public function uiButton() {
		$tplinfo = array (
				'tplpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id']
		);
		$tplManage = A ( 'Admin/TplManage' );
		$this->ctresult = $tplManage->EntrustTemplate ( $tplinfo );
		$this->display ();
	}
	
	
}
?>