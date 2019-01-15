<?php
import ( 'Class.API.WeChatAPI.WechatAuthorize', APP_PATH, '.php' ); // 载入微信授权模块
/**
 * 本控制器对用户访问页面进行微信授权认证。
 * @author 赵臣升
 * CreateTime：2014/10/28 23:50:36。
 */
class WeChatAuthorizeAction extends Action {
	
	/**
	 * 新版本微信授权回调处理。
	 */
	public function wechatAuthCallback() {
		$code = $_GET ['code']; 		// 尝试接收微信平台发来的数据
		$state = $_GET ['state']; 		// 尝试接收系统随机码
		$wechatauth = new WechatAuthorize ();
		$errorinfo = $wechatauth->authorizeCallbackHandle ( $code, $state ); // 微信授权回调处理
		$this->error ( $errorinfo ); 	// 不出错就直接跳走了，不会弹出错误信息
	}
	
}
?>