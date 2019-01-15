<?php
/**
 * 微动网页聊天GET类型接口控制器，继承自网页聊天控制器。
 * @author 赵臣升
 * CreateTime:2015/07/08 11:09:25.
 */
class WebChatGetAction extends WebChatAction {
	function _initialize() {
		parent::_initialize(); // 先调用父类的初始化函数。
		$this->params = $this->getInfo; // 统一封装params参数
		
		// 默认服务器返回信息（默认必须错误）
		$this->ajaxresult ['errCode'] = -1;
		$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
	}
}
?>