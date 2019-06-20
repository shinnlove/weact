<?php
/**
 * 微信JSSDK支持类。
 * @author 赵臣升
 * CreateTime:2015/07/09 17:32:26.
 */
class JSSDK {

	/**
	 * 微动平台微信jsapiticket中控系统请求地址。
	 * @var String WECHAT_TOKEN_CENTRAL_SYSTEM
	 */
	const WECHAT_JSAPITICKET_CENTRAL_SYSTEM = "http://www.we-act.cn/Interface/ExportWeChat/jsAPITicket";
	
	/**
	 * 企业编号e_id
	 * @var String $e_id
	 */
	private $e_id;
	
	/**
	 * 商家的appid
	 * @var String $appId
	 */
	private $appId;
	
	/**
	 * 商家的appsecret
	 * @var String $appSecret
	 */
	private $appSecret;
	
	/**
	 * JSSDK的构造函数
	 * @param string $e_id 企业编号
	 * @param String $appId 企业appid
	 * @param String $appSecret 企业appsecret
	 */
	public function __construct($e_id, $appId, $appSecret) {
		$this->e_id = $e_id;
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}
	
	/**
	 * 生成签名包。
	 * @return array $signPackage
	 */
	public function getSignPackage() {
		$jsapiTicket = $this->getJsApiTicket ();
		
		// 注意 URL 一定要动态获取，不能 hardcode.
		$protocol = (! empty ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] !== 'off' || $_SERVER ['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		$timestamp = time ();
		$nonceStr = $this->createNonceStr ();
		
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		
		$signature = sha1 ( $string );
		
		$signPackage = array (
				"appId" => $this->appId,
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => $url,
				"signature" => $signature,
				"rawString" => $string 
		);
		return $signPackage;
	}
	
	/**
	 * 生成随机数
	 * @param number $length 生成随机数的长度，默认长度为16
	 * @return string $str 随机数字符串序列
	 */
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	
	/**
	 * 私有敏感函数：向微动中控系统请求jsapi_ticket函数。
	 * 该接口类型为GET。
	 * @return string $access_token 企业的jsapi_ticket信息
	 */
	private function getJsApiTicket() {
		$url = self::WECHAT_JSAPITICKET_CENTRAL_SYSTEM; // 请求获取jsapi_ticket的url
		$params ['e_id'] = $this->e_id; // 商家编号
		$httpstr = http ( $url, $params ); // 使用http方法获取服务器返回数据$httpstr
		$jsonresult = json_decode ( $httpstr, true ); // 使用json格式对数据解码，第二个参数为true时，将返回数组而非对象object
		return $jsonresult ['ticket']; // 返回jsapi_ticket信息
	}
	
}
?>