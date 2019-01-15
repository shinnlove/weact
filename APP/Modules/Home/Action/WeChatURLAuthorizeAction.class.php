<?php
import ( 'Class.API.WeChatAPI.WechatAuthorize', APP_PATH, '.php' ); 	// 载入微信授权模块
/**
 * 微信URL授权登录。
 * @author 赵臣升。
 * CreateTime:2015/10/2 18:51:25.
 */
class WeChatURLAuthorizeAction extends MobileGuestAction {
	
	/**
	 * 网页申请微信授权。
	 */
	public function urlWechatAuthorize() {
		$requesturl = I ( 'requesturl' );
		if (empty ( $requesturl )) {
			$this->error ( "需要申请微信授权的URL地址不能为空。" );
		}
		if (empty ( $this->einfo ['appid'] ) || empty ( $this->einfo ['appsecret'] )) {
			$this->error ( "商家并没有配置微信授权信息，请迅速联系其配置。" );
		}
		$wechatauth = new WechatAuthorize ();
		$wechatauth->authorizeForCode ( $this->einfo, $requesturl ); 			// 静默授权
	}
	
}
?>