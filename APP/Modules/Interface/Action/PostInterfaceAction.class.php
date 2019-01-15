<?php
/**
 * 微动POST类型接口
 * @author 赵臣升
 * CreateTime:2015/03/13 17:43:25.
 */
class PostInterfaceAction extends BasicAuthorizeAction {
	function _initialize() {
		parent::_initialize(); // 先调用父类的初始化函数。
		if (empty ( $this->postInfo )) {
			exit ( 'require POST method!' ); // 这是一个post类型接口，参数必须是post发送
		}
		$this->params = json_decode ( $this->postInfo, true ); // 将post过来的参数统一decode，再封装params参数
		// 拒绝post token，不安全
		if (! empty ( $this->params ['access_token'] )) {
			exit ( "It's unsafe to post access_token value, please put token on URL and only try to post data." ); // 这是一个post类型接口，参数必须是post发送
		}
		// 默认服务器返回信息（默认必须错误）
		$this->ajaxresult ['errCode'] = -1;
		$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
	}
}
?>