<?php
/**
 * 接收文件类型提交的接口，与POST类型接口稍有不同，本控制器只接收网页聊天传来的文件。
 * @author 赵臣升
 * CreateTime:2015/07/08 11:10:25.
 */
class WebChatFileAction extends WebChatAction {
	function _initialize() {
		parent::_initialize(); // 先调用父类的初始化函数。
		if (empty ( $this->fileInfo )) {
			exit ( 'require POST method!' ); // 文件信息被封装在fileInfo里，post类型接口，参数必须是post发送
		}
		$this->params = $this->getInfo; // 将文件信息要发送的参数封装在params中
		// 默认服务器返回信息（默认必须错误）
		$this->ajaxresult ['errCode'] = -1;
		$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后再试！";
	}
}
?>