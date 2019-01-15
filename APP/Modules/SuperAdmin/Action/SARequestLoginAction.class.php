<?php
/**
 * SuperAdmin的PC端ajax请求基类控制器，用于超级管理员用户发送相应的ajax请求。
 * @author 赵臣升。
 * CreateTime:2015/08/01 20:55:36.
 */
class SARequestLoginAction extends SARequestGuestAction {
	/**
	 * 初始化控制登录。
	 */
	public function _initialize() {
		if (! isset ( $_SESSION ['administrator'] )) {
			$this->ajaxresult ['errCode'] = 20001; // 未登录
			$this->ajaxresult ['errMsg'] = "您未登录，请先登录！"; // 未登录错误信息
			$this->ajaxReturn ( $this->ajaxresult ); // 未登录直接毙掉
		}
	}
}
?>