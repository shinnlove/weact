<?php
/**
 * 总店系统的登录控制器。
 * @author 赵臣升，胡福玲。
 * CreateTime:2015/07/18 16:20:36.
 */
class IndexAction extends Action {
	
	/**
	 * 总店系统登陆页视图。
	 */
	public function index() {
		if (isset ( $_SESSION ['curEnterprise'] )) {
			$this->redirect ( 'Admin/Enterprise/main' ); // 登录以后跳转总店下的企业主业
		} else {
			$this->display ();
		}
	}
	
	/**
	 * 添加验证码。
	 */
	public function getRandomVerifyCode() {
		import ( 'ORG.Util.Image' );
		Image::buildImageVerify ( 4, 1, 'png', 60, 23 );
	}
	
}
?>