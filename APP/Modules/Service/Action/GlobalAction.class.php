<?php
/**
 * 全局控制器。
 * @author 赵臣升。
 * 本控制器处理一些全局的或静态的变量。
 */
class GlobalAction extends Action {
	
	/**
	 * ============access_token区域===============
	 */
	
	/**
	 * 对外接口获取商家的access_token函数getWeChatToken。
	 * 如果调用此服务，说明商家在微动平台是享有开发者服务的。
	 * @param array $einfo 要获取token的商家信息
	 * @return array $tokeninfo	返回token信息
	 */
	public function getAccessToken($einfo = NULL) {
		$tokeninfo = array();					//最终的token信息
		$tokenavailble = false;					//当前token可用标志，默认为不可用
		// Step1：检测商家现有的token值
		$checktoken = array(
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$tokeninfo = M ( 'wechattoken' )->where( $checktoken )->find();			//尝试去找token信息
		if($tokeninfo) {
			if(time() - $tokeninfo ['add_time'] < 7000) {
				$tokenavailble = true;			//如果找到现有的token，则进行时间比对，如果时间差小于7000秒，则token还在有效时间内，可用
			}
		}
		if(! $tokenavailble) {
			$tokeninfo = $this->getNewAccessToken( $einfo );		//如果token不可用，则去重新拉取token，先清空token信息，再重新获取token信息
		}
		//不管$tokeninfo中共有多少字段，只取2个有效字段
		$tokenfinal = array(
				'access_token' => $tokeninfo ['access_token'],
				'expires_in' => $tokeninfo ['expires_in']
		);
		return $tokenfinal;
	}
	
	/**
	 * 私有方法：从微信公众平台新获取token值，同时删除原来的过期token（无论是否存在），然后写入新token值并返回。
	 * @param array $einfo	企业信息
	 * @return array $tokeninfo	企业的access_token信息数组
	 */
	private function getNewAccessToken($einfo = NULL) {
		$token = C ( 'WECHAT_TOKEN' );						//使用ThinkPHP的点语法获取已经设定的token（这个貌似在获取access_token时没什么用）
		$url = 'https://api.weixin.qq.com/cgi-bin/token';	//请求获取accesstoken的url
		$params = array ();
		$params ['grant_type'] = 'client_credential';
		$params ['appid'] = $einfo ['appid'];
		$params ['secret'] = $einfo ['appsecret'];
		$httpstr = http ( $url, $params );
		$tokeninfo = json_decode ( $httpstr, true );
		if($tokeninfo) {
			$this->clearExistToken( $einfo ['e_id'] );		//清除存在token
			$this->recordNewToken( $einfo, $tokeninfo );	//记录下本次新获得的token信息
		}
		return $tokeninfo;
	}
	
	/**
	 * 私有方法，清理所有目前存在的、未删除的token值。
	 * @param string $e_id	要删除token的商家编号
	 * @return boolean $clearresult	删除token信息
	 */
	private function clearExistToken($e_id = '') {
		$clearmap = array(
				'e_id' => $e_id,
				'is_del' => 0
		);
		$clearresult = M ( 'wechattoken' )->where( $clearmap )->setField( 'is_del', 1 );		//清理商户目前存在的无效token
		return $clearresult;
	}
	
	/**
	 * 记录企业新获得的token信息。
	 * @param string $einfo	要记录新token信息的企业信息
	 * @param string $tokeninfo	要记录的新token信息
	 * @return boolean	$recordresult	返回记录成功与否
	 */
	private function recordNewToken($einfo = NULL, $tokeninfo = NULL) {
		$recordresult = false;			//记录token成功与否标记
		if(! empty( $einfo ) && ! empty( $tokeninfo )) {
			$recordinfo = array(
					'token_id' => md5( uniqid( rand(), true ) ),
					'e_id' => $einfo ['e_id'],
					'access_token' => $tokeninfo ['access_token'],
					'expires_in' => $tokeninfo ['expires_in'],
					'add_time' => time()
			);
			$recordresult = M ( 'wechattoken' )->add( $recordinfo );
		}
		return $recordresult;
	}
	
	/**
	 * ============jsapi_ticket区域===============
	 */
	
	/**
	 * 对外接口获取商家的jsapi_ticket函数getJSAPITicket。
	 * 如果调用此服务，说明商家在微动平台是享有开发者服务的。
	 * @param array $einfo 要获取ticket的商家信息
	 * @return array $tokeninfo	返回token信息
	 */
	public function getJSAPITicket($einfo = NULL) {
		$ticketinfo = array ();					//最终的ticket信息
		$ticketavailble = false;				//当前ticket可用标志，默认为不可用
		// Step1：检测商家现有的token值
		$checkticket = array(
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$ticketinfo = M ( 'jssdkticket' )->where ( $checkticket )->find();			//尝试去找ticket信息
		if ($ticketinfo) {
			if (time () - $ticketinfo ['add_time'] < 7000) {
				$ticketavailble = true;			//如果找到现有的ticket，则进行时间比对，如果时间差小于7000秒，则ticket还在有效时间内，可用
			}
		}
		if (! $ticketavailble) {
			$ticketinfo = $this->getNewJSAPITicket ( $einfo );		//如果token不可用，则去重新拉取ticket，先清空ticket信息，再重新获取ticket信息
		}
		//不管$tokeninfo中共有多少字段，只取2个有效字段
		$ticketfinal = array(
				'ticket' => $ticketinfo ['ticket'],
				'expires_in' => $ticketinfo ['expires_in']
		);
		return $ticketfinal;
	}
	
	/**
	 * 私有方法：从微信公众平台重新获取jsapi_ticket的方法，同时删除原来的过期ticket（无论是否存在），然后写入新ticket值并返回。
	 * @param array $einfo 企业信息
	 * @return array $ticketinfo 企业的jsapi_ticket信息数组
	 */
	private function getNewJSAPITicket($einfo = NULL) {
		$tokeninfo = $this->getAccessToken ( $einfo );
		// 如果是企业号用以下 URL 获取 ticket
		// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';	//请求获取jsapi_ticket的url
		$params = array ();
		$params ['type'] = 'jsapi';
		$params ['access_token'] = $tokeninfo ['access_token'];
		$httpstr = http ( $url, $params );
		$ticketinfo = json_decode ( $httpstr, true );
		if ($ticketinfo) {
			$this->clearExistTicket ( $einfo ['e_id'] );	//清除存在ticket
			$this->recordNewTicket ( $einfo, $ticketinfo );	//记录下本次新获得的ticket信息
		}
		return $ticketinfo;
	}
	
	/**
	 * 私有方法，清理所有目前存在的、未删除的ticket值。
	 * @param string $e_id 要删除ticket的商家编号
	 * @return boolean $clearresult	删除ticket信息
	 */
	private function clearExistTicket($e_id = '') {
		$clearmap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$clearresult = M ( 'jssdkticket' )->where ( $clearmap )->setField ( 'is_del', 1 );		//清理商户目前存在的无效ticket
		return $clearresult;
	}
	
	/**
	 * 记录企业新获得的ticket信息。
	 * @param string $einfo	要记录新ticket信息的企业信息
	 * @param string $ticketinfo 要记录的新ticket信息
	 * @return boolean $recordresult 返回记录成功与否
	 */
	private function recordNewTicket($einfo = NULL, $ticketinfo = NULL) {
		$recordresult = false;			//记录token成功与否标记
		if (! empty ( $einfo ) && ! empty ( $ticketinfo )) {
			$recordinfo = array(
					'ticket_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $einfo ['e_id'],
					'ticket' => $ticketinfo ['ticket'],
					'expires_in' => $ticketinfo ['expires_in'],
					'add_time' => time () 
			);
			$recordresult = M ( 'jssdkticket' )->add ( $recordinfo );
		}
		return $recordresult;
	}
	
}
?>