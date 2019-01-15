<?php
/**
 * 小黄鸡服务类。
 * @author 赵臣升。
 * CreateTime：2014/11/06 00:59:25.
 */
class Simsimi {
	
	var $key = '3ed5c0c6-e98f-494e-806e-22f71b51ec2a';		//小黄鸡用户服务密钥
	var $ft = '0.0';										//是否过滤骂人的词汇
	var $lc = 'ch';											//语言
	
	/**
	 * 小黄鸡API接口函数。
	 * Author：赵臣升。
	 * CreateTime：2014/06/29 21:46:25。
	 * @param string $msg 用户的消息
	 * @return string $response	SimSimi回复内容
	 */
	public function callSimsimi($msg = '') {
		$url = 'http://sandbox.api.simsimi.com/request.p';
		$params = array (
				'key' => $this->key,
				'ft' => $this->ft,
				'lc' => $this->lc,
				'text' => $msg 
		);
		$response = json_decode ( http ( $url, $params ), true );
		return $response;
	}
	
	/**
	 * 模仿小黄鸡自动回复的接口函数。
	 * @param string $msg 用户的消息
	 * @return string $response	小九机器人的回复内容
	 */
	public function apeSimsimi($msg = '') {
		$url = "http://www.xiaojo.com/bot/chata.php";
		$params = array (
				'chat' => $msg,
		);
		$response = json_decode ( http ( $url, $params ), true );
		return $response;
	}
	
}
?>