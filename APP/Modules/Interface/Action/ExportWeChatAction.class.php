<?php
import ( 'Class.API.WeChatAPI.WeactWechatOpen', APP_PATH, '.php' );
/**
 * 本控制器是微动平台对外的接口。
 * @author 赵臣升。
 * CreateTime：2014/12/16 18:46:25.
 */
class ExportWeChatAction extends Action {
	
	/**
	 * 获取开放平台全网发布token。
	 */
	public function getOpenToken() {
		$appid = I ( 'appid' ); // 尝试接收appid
		$query_auth_code = I ( 'query_auth_code' ); // 尝试接收query_auth_code
		
		$einfo = array (
				'e_id' => $appid, // e_id用appid代替
				'appid' => $appid,
				'is_auth' => 1, // 特别注意：打开一键授权
		);
		$wechat = new WeactWechatOpen ( $einfo );
		
		//获取公众号授权方信息
		$authorizerinfo = $wechat->authCodeForAuthorizerInfo ( $query_auth_code ); // 通过query_auth_code获取授权方authorizer_access_token等信息
		//debugLog("token info : " . jsencode($authorizerinfo));
		//取授权方的auth_access_token和expires_in信息存入$authtoken中
		$authtoken = array (
				'access_token' => $authorizerinfo ['authorization_info'] ['authorizer_access_token'],	// 取出授权方的authorizer_access_token，这里便于一致所以key为access_token
				'expires_in' => $authorizerinfo ['authorization_info'] ['expires_in'],		// 取出授权方的authorizer_access_token的有效期
		);
		
		exit ( json_encode ( $authtoken ) );
	}
	
	/**
	 * 微动对外提供企业token的接口。
	 */
	public function getWeChatToken() {
		$invalidinfo = array (); // 定义错误信息
		$openauth = I ( 'is_auth' ); // 是否微信开放平台
		$emap = array (
				'e_id' => $_REQUEST ['e_id'], // 获取参数企业编号
				'is_del' => 0 
		);
		if (! empty ( $emap ['e_id'] )) {
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
			if (empty ( $einfo )) {
				$invalidinfo ['errcode'] = 20001;
				$invalidinfo ['errmsg'] = '本平台没有该企业用户!';
				exit ( json_encode ( $invalidinfo ) );
			} else {
				if ($openauth == 0) {
					// 原来的方式
					$swechat = A ( 'Service/Global' );
					exit ( json_encode ( $swechat->getAccessToken ( $einfo ) ) );
				} else if ($openauth == 1) {
					// 开放平台的方式
					$weactwechatopen = new WeactWechatOpen ( $einfo ); // 实例化开放平台类
					$access_token = $weactwechatopen->getAuthorizerAccessTokenById ( $einfo );
					$authtoken = array (
							'access_token' => $access_token,	// 授权方的authorizer_access_token，这里便于一致所以key为access_token
							'expires_in' => 7200,				// 授权方的authorizer_access_token的有效期，可以认为微动平台返回的就一定是2个小时有效的
					);
					exit ( json_encode ( $authtoken ) );
				}
			}
		} else {
			$invalidinfo ['errcode'] = 20000;
			$invalidinfo ['errmsg'] = '缺少参数!';
			exit ( json_encode ( $invalidinfo ) );
		}
	}
	
	/**
	 * 微动对外提供企业H5网页微信JSSDK专有apo的接口。
	 */
	public function jsAPITicket() {
		$invalidinfo = array (); // 定义错误信息
		$emap = array (
				'e_id' => $_REQUEST ['e_id'], // 获取参数企业编号
				'is_del' => 0
		);
		if (! empty ( $emap ['e_id'] )) {
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
			if (empty ( $einfo )) {
				$invalidinfo ['errcode'] = 20001;
				$invalidinfo ['errmsg'] = '本平台没有该企业用户!';
				exit ( json_encode ( $invalidinfo ) );
			} else {
				$swechat = A ( 'Service/Global' );
				exit ( json_encode ( $swechat->getJSAPITicket ( $einfo ) ) );
			}
		} else {
			$invalidinfo ['errcode'] = 20000;
			$invalidinfo ['errmsg'] = '缺少参数!';
			exit ( json_encode ( $invalidinfo ) );
		}
	}
	
}
?>