<?php
/**
 * 处理WebChat端发送的消息与文件类型数据的控制器。
 * @author 赵臣升
 * CreateTime:2015/07/08 11:14:25.
 */
class ReceiveWebChatFileAction extends WebChatFileAction {
	
	/**
	 * 接收APP多媒体上传到服务器的消息接口。
	 */
	public function webUploadMedia() {
		// 没有商家编号直接毙掉（要区分多媒体属于哪个商家的，分文件夹）
		if (empty ( $this->params ['eid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "接口参数错误，缺少上传多媒体所属的商家编号！";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 没有指明多媒体文件类型直接毙掉
		if (empty ( $this->params ['type'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "接口参数错误，缺少上传多媒体文件类型！";
			exit ( json_encode ( $this->ajaxresult ) );
		}
	
		// 根据不同类型的多媒体分别处理
		$mediatype = trim ( $this->params ['type'] ); // 获得多媒体文件的类型
		if ($mediatype == 'image') {
			// 如果多媒体文件类型是图片
			$this->webPictureMsg (); // 调用本类的处理图片多媒体消息的函数
		} else if ($mediatype == 'voice') {
			// 如果多媒体文件类型是声音
			$this->webVoiceMsg (); // 调用本类的处理声音多媒体消息的函数
		} else if ($mediatype == 'video') {
			// 如果多媒体文件类型是视频
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "上传多媒体文件失败，暂不开放视频文件上传！";
		} else if ($mediatype == 'music') {
			// 如果多媒体文件类型是音乐
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "上传多媒体文件失败，暂不开放音乐文件上传！";
		} else if ($mediatype == 'thumb') {
			// 如果多媒体文件类型是缩略图
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "上传多媒体文件失败，暂不开放缩略图文件上传！";
		} else {
			// 其他多媒体文件类型暂不支持
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "未知多媒体类型，请上传接口文档中允许的媒体类型！";
		}
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口处理多媒体的结果信息
	}
	
	/**
	 * 接收WebChat图片消息接口。
	 */
	public function webPictureMsg() {
		// 条件齐全开始处理图片，在用户上传图片等待时间里，wechatSingleUpload将图片转手发给微信先手获得media_id，等到第二次调用接口直接使用media_id
		$savepath = "./InterfaceLog/picture/" . date( "Ymd" ) . "/"; // 要用相对路径（这里要提取.），可以定义不同的聊天内容图片存放地方，但是要用相对路径带.
		$filehandle = A ( 'Interface/FileHandle' ); // 实例化文件控制器
		$saveresult = $filehandle->wechatSingleUpload ( $this->params ['eid'], $savepath ); // 处理单张图片文件，注意要送入企业编号
		$this->ajaxresult ['errCode'] = $saveresult ['errCode']; // 赋值错误码
		$this->ajaxresult ['errMsg'] = $saveresult ['errMsg']; // 赋值错误信息
		$this->ajaxresult ['data'] = $saveresult ['data']; // 赋值数据信息
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 接收WebChat语音消息接口。
	 */
	public function webVoiceMsg() {
		// 条件齐全开始处理语音，在用户上传语音的等待时间里，wechatVoiceUpload将语音转手发给微信先手获得media_id，等到第二次调用接口直接使用media_id
		$savepath = "./InterfaceLog/voice/" . date( "Ymd" ) . "/"; // 要用相对路径（这里要提取.），可以定义不同的聊天语音内容存放地方，但是要用相对路径带.
		$filehandle = A ( 'Interface/FileHandle' ); // 实例化文件控制器
		$saveresult = $filehandle->wechatVoiceUpload ( $this->params ['eid'], $savepath ); // 处理单个语音文件，注意要送入企业编号
		$this->ajaxresult ['errCode'] = $saveresult ['errCode']; // 赋值错误码
		$this->ajaxresult ['errMsg'] = $saveresult ['errMsg']; // 赋值错误信息
		$this->ajaxresult ['data'] = $saveresult ['data']; // 赋值数据信息
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
}
?>