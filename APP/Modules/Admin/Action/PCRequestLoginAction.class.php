<?php
/**
 * PC端ajax请求基类控制器，用于总品牌登录用户发送相应的ajax请求。
 * @author 赵臣升。
 * CreateTime:2015/07/18 16:20:36.
 */
class PCRequestLoginAction extends PCRequestGuestAction {
	/**
	 * 初始化控制登录。
	 */
	public function _initialize() {
		if (! isset ( $_SESSION ['curEnterprise'] )) {
			$this->ajaxresult ['errCode'] = 20001; // 未登录
			$this->ajaxresult ['errMsg'] = "您未登录，请先登录！"; // 未登录错误信息
			$this->ajaxReturn ( $this->ajaxresult ); // 未登录直接毙掉
		}
	}
}
?>