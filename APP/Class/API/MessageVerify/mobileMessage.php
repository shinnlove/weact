<?php
import ( 'Class.API.MessageVerify.Phone', APP_PATH, '.php' );
import ( 'Class.API.MessageVerify.ShortMsg', APP_PATH, '.class.php' );

/**
 * 本类处理各种短信验证或通知，sendYanzhengma处理需要发送验证码类型的情况，sendMsgNotify处理需要发送通知的情况
 * @author wlk
 *
 */
class mobileMessage{	
	
	/**
	 * 
	 * @param string $telNum 要发送的联系电话
	 * @param string $type 验证码类型
	 * @return 返回发送结果,正确返回0，错误返回错误码，错误码类型参照云之讯错误码文档
	 */
	public function sendYanzhengma($telNum = '13162580835',$type = 'REGISTER'){
		$verify = "" . rand(100000, 999999);
		$sms = new ShortMsg();
        switch($type) {
            case 'REGISTER' :
                //注册
				$result = $sms->sendRegistYanzheng($verify, $telNum);
                break;
            case 'ALTERINFO' :
                //修改重要信息
				$result = $sms->sendRegistYanzheng($verify, $telNum);
                break;
			case 'FINDCODE' :
				//找回密码
				$result = $sms->findCodeYanzheng($verify, $telNum);
				break;
            default :
            	$result = -1;
                break;
            return $result;
        }
	}

	
	
	/**
	 *
	 * @param string $telNum 要发送的联系电话
	 * @param string $type 短信通知类型
	 * @param string $ename 商家名称
	 * @param string $orderNumber 定单编号
	 * @return 返回发送结果,正确返回0，错误返回错误码，错误码类型参照云之讯错误码文档
	 */
	public function sendMsgNotify($telNum = '13162580835',$type = 'PAYSUCCESS',$ename = '微动商城',$orderNumber = 'NO.10002'){
		$sms = new ShortMsg();
		switch($type) {
			case 'REFUND' :
				//退款成功通知
				$result = $sms->refundSuccess($telNum,$ename,$orderNumber);
				break;
			case 'PAYSUCCESS':
				//付款成功通知
				$result = $sms->paySuccess($telNum,$ename,$orderNumber);
				break;
			case 'ORDER' :
				//下单通知
				$result = $sms->orderSuccess($telNum,$ename,$orderNumber);
				break;
			case 'CANCELORDER' :
				//取消下单通知
				$result = $sms->cancelOrder($telNum,$ename,$orderNumber);
				break;
			case 9 :
				//通知
				$result = $sms->refundSuccess($telNum,$ename,$orderNumber);
				break;
			default :
				$result = -1;
				break;
			return $result;
		}
	}
}
?>