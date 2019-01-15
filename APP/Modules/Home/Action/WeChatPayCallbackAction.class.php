<?php
import ( 'Class.API.WeActPay.Main.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动平台安全支付SDK
/**
 * 本控制器只做固定接收微信支付通知。
 * @author 赵臣升。
 * CreateTime：2015/01/07 20:20:20.
 */
class WeChatPayCallbackAction extends Action {
	
	/**
	 * 固定接收微信支付通知的控制器。
	 */
	public function notifyurl() {
		$e_id = $_REQUEST ['e_id']; 							// 获取在平台被通知的商家编号
		if (empty ( $e_id )) exit ( "" );
		
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA']; 				// 用xml存储微信的回调
		
		$weactnotify = new WeActPayNotify ( $e_id ); 			// 实例化微动扫码支付类
		$handleresult = $weactnotify->notifyHandle ( $xml ); 	// 处理扫码支付
		
		exit ( $handleresult ); // 返回给微信
	}
	
}
?>