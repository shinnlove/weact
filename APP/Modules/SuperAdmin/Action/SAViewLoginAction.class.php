<?php
/**
 * SuperAdmin的PC端登录控制器，检测当前超级管理员是否登录。
 * @author 赵臣升。
 * CreateTime:2015/08/01 20:54:36.
 */
class SAViewLoginAction extends Action {
	/**
	 * ajax返回信息。
	 * @var array $ajaxresult
	 */
	protected $ajaxresult = array (
			'errCode' => 10001,
			'errMsg' => "网络繁忙，请稍后再试！"
	);
	/**
	 * 初始化控制登录。
	 */
	public function _initialize() {
		if (! isset ( $_SESSION ['administrator'] )) {
			$this->redirect ( 'SuperAdmin/Index/index' );
		}
	}
}
?>