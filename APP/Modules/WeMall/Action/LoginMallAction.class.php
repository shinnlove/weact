<?php
/**
 * 用户登录控制器，对需要登录的页面进行管控。
 * @author Administrator
 * CreateTime:2015/04/29 14:02:25.
 */
class LoginMallAction extends GuestMallAction {
	/**
	 * 登录控制器初始化，视图页面都必须登录。
	 */
	public function _initialize() {
		parent::_initialize (); // 调用父类GuestMall控制器的初始化
		$fromurl = $_SERVER ['REQUEST_URI']; // 获取正在请求访问的URL地址
		if (! isset ( $_SESSION ['currentcustomer'] ) || empty ( $_SESSION ['currentwechater'] [$this->eid] ) || $_SESSION ['currentcustomer'] ['e_id'] != $this->eid) {
			// 微信授权一定会换取用户登录，所以只要检测用户登录即可
			$redirecturi = U ( 'WeMall/GuestMall/login', array ( 'sid' => $this->sid), '' ) . "?from=" . $fromurl; // 生成登录页面地址（带上回跳参数）
			header ( 'Location:' . $redirecturi ); // 跳转登录
		} 
		$this->cinfo = $_SESSION ['currentcustomer']; // 当前用户信息cinfo
		$this->winfo = $_SESSION ['currentwechater'] [$this->eid]; // 当前微信用户信息winfo
		// to do ......
	}
}
?>