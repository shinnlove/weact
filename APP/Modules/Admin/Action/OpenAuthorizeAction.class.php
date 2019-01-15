<?php
import ( 'Class.API.WechatAPI.WeactWechatOpen', APP_PATH, '.php' ); // 载入微动微信开放平台类
/**
 * 微信开放平台预授权。
 * @author 万路康
 */
class OpenAuthorizeAction extends PCViewLoginAction {
	
	/**
	 * 微信开放平台预授权前置页面。
	 */
	public function preAuth() {
		// 实例化微信开放平台类
		$einfo = $_SESSION ['curEnterprise']; // 取当前企业的信息
		$wechat = new WeactWechatOpen ( $einfo );
		// 依次获得$ticket和$componentAccessToken
		$component_appid = $wechat->getCurrentComponentAppId ();
		$preauthcode = $wechat->generatePreAuthCode ();
		$redirect_uri = "http://www.we-act.cn/weact/Admin/OpenAuthorize/authorizeCheck";
		// 拼接授权地址
		$this->authurl = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=' . $component_appid . '&pre_auth_code=' . $preauthcode . '&redirect_uri=' . $redirect_uri;
		$this->display ();
	}
	
	/**
	 * 授权回调检测页面。
	 */
	public function authorizeCheck() {
		$this->traceHttp();
		
		$auth_code = $_GET ['auth_code']; // 尝试获取auth_code
		
		if (! empty ( $auth_code )) {
			// 如果auth_code不空，则实例化微信开放平台类对象
			$einfo = $_SESSION ['curEnterprise'];
			$wechat = new WeactWechatOpen ( $einfo );
				
			$authorizerinfo = $wechat->authCodeForAuthorizerInfo ( $auth_code ); // 利用auth_code换取授权者信息
			if (! empty ( $authorizerinfo ['authorization_info'] )) {
				// 如果有授权信息
				$this->logger ( jsencode ( $authorizerinfo ) ); // 记录授权信息
				$this->display ();
			} else {
				$this->error ( "系统繁忙，请稍后再试！" );
			}
		} else {
			$this->error ( "您输入的网址不合法！" );
		}
	}
	
	public function traceHttp() {
		$this->logger ( "REMOTE_ADDR:" . $_SERVER ['REMOTE_ADDR']. ( ( strpos( $_SERVER ['REMOTE_ADDR'], "101.226" )) ? " From WeiXin" : " Unknown IP" ) );
		$this->logger ( "QUERY_STRING:" . $_SERVER ['QUERY_STRING'] );
	}
	
	public function logger($content){
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/authorizerinfo/"; 	// 授权者信息文件夹
		$filename = "authorizer" . date ( "Ymd" ) . ".log"; 										// 文件名按天存放
		file_put_contents ( $filepath . $filename, date ( "Y-m-d H:i:s" ) . $content . "\n\n", FILE_APPEND );
	}
	
}
?>