<?php
/**
 * 图灵自动回复，接口来自：http://www.tuling123.com/openapi/cloud/access_api.jsp#API
 * @author 万路康
 */
class TuLingAutoResponse {
	
	const APIURL = "http://www.tuling123.com/openapi/api";
	
	const APIKEY = "49b799d1aa1f3c9c2aade82850c7bd2c";
	
	/**
	 * 构造函数。
	 */
	public function __construct() {
		// to do construct...
	}
	
	/**
	 * 对外输出函数，寻求自动回复。
	 */
	public function autoResponse($askmsg = "你好") {
		$response = "";
		// 构造请求
		$params = array (
				'key' => self::APIKEY,
				'info' => $askmsg,
		);
		$httpjson = http ( self::APIURL, $params );
		$httpresult = json_decode ( $httpjson, true );
		// 解析结果
		if ($httpresult ['code'] == 100000) {
			$response = $httpresult ['text']; // 请求正确才将结果给$response
		}
		return $response;
	}
	
}
?>