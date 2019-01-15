<?php
/**
 * PC端登录控制器，检测当前企业是否登录。
 * @author 赵臣升。
 * CreateTime:2015/07/18 16:20:36.
 */
class PCViewLoginAction extends Action {
	/**
	 * 初始化控制登录。
	 */
	public function _initialize() {
		if (! isset ( $_SESSION ['curEnterprise'] )) {
			$this->redirect ( 'Admin/Index/index' );
		}
	}
}
?>