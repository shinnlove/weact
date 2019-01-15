<?php
/**
 * SuperAdmin的PC端游客ajax处理控制器，该控制器目的在于对游客登录成超级管理员的请求进行过滤。
 * @author 赵臣升。
 * 2015/07/18 19:23:25.
 */
class SARequestGuestAction extends Action {
	/**
	 * ajax返回信息。
	 * @var array $ajaxresult
	 */
	protected $ajaxresult = array (
			'errCode' => 10001,
			'errMsg' => "网络繁忙，请稍后再试！"
	);
	
	/**
	 * 游客移动端控制器初始化。
	*/
	public function _initialize() {
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist！" ); // ajax提交地址不能被随意打开
		}
		// To do login request continue...
	}
}
?>