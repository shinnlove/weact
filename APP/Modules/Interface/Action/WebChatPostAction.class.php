<?php
/**
 * 微动网页聊天窗POST类型接口
 * @author 赵臣升
 * CreateTime:2015/07/08 11:07:25.
 */
class WebChatPostAction extends WebChatAction {
	function _initialize() {
		parent::_initialize(); // 先调用父类的初始化函数。
		if (empty ( $this->postInfo )) {
			exit ( 'require POST method!' ); // 这是一个post类型接口，参数必须是post发送
		}
		if ($this->datatype == "jsonp") {
			$this->params = $this->postInfo; // jsonp不用解码
		} else {
			$this->params = json_decode ( $this->postInfo, true ); // 将post过来的参数统一decode，再封装params参数
		}
		
		// 默认服务器返回信息（默认必须错误）
		$this->ajaxresult ['errCode'] = -1;
		$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
	}
}
?>