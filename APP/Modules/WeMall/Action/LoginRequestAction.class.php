<?php
/**
 * 处理登录后才能ajax请求的控制器。
 * @author 赵臣升
 * CreateTime:2015/05/15 20:53:36.
 */
class LoginRequestAction extends GuestMallAction {
	/**
	 * ajax返回信息。
	 * @var array $ajaxresult
	 */
	protected $ajaxresult = array (
			'errCode' => 10001,
			'errMsg' => "网络繁忙，请稍后再试！"
	);
	
	/**
	 * 登录后才能进行的ajax请求，初始化函数。
	 */
	public function _initialize() {
		parent::_initialize(); // 调用父类的初始化函数
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist！" ); // ajax提交地址不能被随意打开
		}
		// 检测登录状态
		if (! isset ( $_SESSION ['currentcustomer'] )) {
			$this->ajaxresult ['errCode'] = 20001; // 未登录
			$this->ajaxresult ['errMsg'] = "您未登录，请先登录！"; // 未登录错误信息
			$this->ajaxReturn ( $this->ajaxresult ); // 未登录直接毙掉
		}
		// continue to handle ajax...
	}
}
?>