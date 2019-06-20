<?php
/**
 * 微信用户授权模块。
 * @author 赵臣升
 * CreateTime:2015/10/01 12:06:28.
 */
class WechatAuthorize {
	
	/**
	 * 本类的静态变量，微动微信授权统一回调地址
	 * @var string $unifiedRedirectURI
	 */
	private $unifiedRedirectURI = ''; 
	
	/**
	 * 微信授权类的构造函数。
	 */
	public function __construct() {
		$this->unifiedRedirectURI = C ( 'WECHAT_AUTHORIZE_REDIRECT' ); // 读取微信授权回调页面
	}
	
	/**
	 * ==========开始请求微信授权部分==========
	 */
	
	/**
	 * 组装微信授权路径的函数。，已经urlencode了。
	 * @param string $appid
	 * @param string $redirect_uri
	 * @param string $response_type
	 * @param string $scope
	 * @param string $state
	 * @return string $wechatauthorizeurl 微信授权的url
	 */
	private function assembleAuthPath($appid = 'APPID', $redirect_uri = 'REDIRECT_URI', $response_type = 'code', $scope = 'SCOPE', $state = 'STATE') {
		$basicURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
		$authSuffix = '#wechat_redirect';
		$finalURL = $basicURL . '?appid=' . $appid . '&redirect_uri=' . urlencode ( $redirect_uri ) . '&response_type=' . $response_type . '&scope=' . $scope . '&state=' . $state . $authSuffix;
		return $finalURL;
	}
	
	/**
	 * 请求微信授权获得用户openid函数。
	 * @param array $einfo 请求授权用户所在的企业信息
	 * @param string $authorizeRequestURL 授权请求来自的URL（授权后回跳URL）
	 */
	public function authorizeForCode($einfo = NULL, $authorizeRequestURL = '', $authorizeType = 'snsapi_base') {
		// Step1：随机生成一个验证码
		$state = md5 ( uniqid ( rand (), true ) ); 			// 随机生成一个验证的state码（作为授权服务的主键）
		
		// Step4：存入wechatauthorize微动授权表中，加固回跳机制
		$authorizeinfo = array (
				'authorize_id' => $state, 					// state作为回跳参数，微信会原样返回，这里作为主键方便检索
				'e_id' => $einfo ['e_id'], 					// 当前需要静默授权的企业,
				'redirect_uri' => $authorizeRequestURL, 	// 授权回跳页面URL
				'grant_time' => time (), 					// 微动授权时间
		);
		$addauth = M ( 'wechatauthorize' )->add ( $authorizeinfo ); // 添加授权信息
		if (! $addauth) {
			$this->error ( "商户微信授权系统当前繁忙，请稍后再尝试微信授权登录。" ); // 数据库繁忙的友善提醒信息
		}
		
		// 存授权表都妥当了，再去微信
		header ( 'Location:' . $this->assembleAuthPath ( $einfo ['appid'], $this->unifiedRedirectURI, 'code', $authorizeType, $state ) );
	}
	
	/**
	 * ==========微信授权回调部分==========
	 */
	
	/**
	 * 进一步利用code获取用户信息的函数。
	 * @param array $einfo 企业信息
	 * @param string $code 回跳code
	 * @param string $grant_type 授权方式：authorization_code
	 * @return array $grantinfo|false 微信授权用户信息|失败返回false
	 */
	private function openidAuth($einfo = NULL, $code = '', $grant_type = 'authorization_code') {
		$grantinfo = false;
		if (! empty ( $einfo ['appid'] ) && ! empty ( $einfo ['appsecret'] )) {
			$getopenidURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
			$openidParams = array (
					'appid' => $einfo ['appid'],
					'secret' => $einfo ['appsecret'],
					'code' => $code, // 授权回跳code
					'grant_type' => $grant_type,
			);
			$jsoninfo = http ( $getopenidURL, $openidParams );
			$grantinfo = json_decode ( $jsoninfo, true );
		}
		return $grantinfo;
	}
	
	/**
	 * 微动平台记录微信授权信息的函数。
	 * @param string $state
	 * @param string $e_id
	 * @param string $authurl
	 * @param string $recvauthinfo
	 */
	private function updateRecordAuth($state = '', $recvauthinfo = NULL) {
		// 要更新的索引
		$updatemap = array (
				'authorize_id' => $state,
				'is_del' => 0
		);
		// 要更新的内容
		$grantAuthorize = array (
				'access_token' => $recvauthinfo ['access_token'],
				'refresh_token' => $recvauthinfo ['refresh_token'],
				'expires_in' => $recvauthinfo ['expires_in'],
				'openid' => $recvauthinfo ['openid'],
				'scope' => $recvauthinfo ['scope']
		);
		$waresult = M ( 'wechatauthorize' )->where ( $updatemap )->save ( $grantAuthorize );
	}
	
	/**
	 * 记录登录/登出函数。
	 * @param string $e_id	形参传入当前商家
	 * @param string $customer_id 形参传入当前操作顾客
	 * @param boolean $loginOut  默认形参是登录，如果此处是true，代表该函数重载处理登出。
	 * @param boolean $registerlogin 以注册方式登录，如果此处是true，代表当前登录方式是新用户注册
	 * @return boolean $result 返回true代表记录成功，返回false代表记录失败。
	 */
	private function loginRecord($e_id = '', $customer_id = '', $loginOut = FALSE, $registerlogin = FALSE) {
		$loginHandleInfo = array (
				'login_record_id' => md5 ( uniqid ( rand (), true ) ),		// 随机生成32位的loginrecord编号
				'e_id' => $e_id,											// 记录商家的编号（区分是哪个商家的客户）
				'customer_id' => $customer_id,								// 记录下当前登录用户的编号
				'operate_time' => time (),									// 记录下操作时间，现在用operate过度，以后改回operate_time
				'operate_type' => intval ( $loginOut ),						// 当前操作类型是注销（0：登录；1：注销），把false或true转成整型0或1
				//device和ip暂时先不记录
		);
		if ($registerlogin) {
			$loginHandleInfo ['remark'] = "新用户授权注册，系统默认注册后登录";
		} else {
			$loginHandleInfo ['remark'] = "微信用户授权登录";
		}
		$result = M ( 'loginrecord' )->add ( $loginHandleInfo );			// 向数据库添加登录、登出记录
		return $result;
	}
	
	/**
	 * 微信授权回调处理函数，只能由指定控制器调用处理微信回调。
	 * @param string $code 微信授权回调code
	 * @param string $state 微信授权回调状态
	 */
	public function authorizeCallbackHandle($code = '', $state = '') {
		// Step1：检测参数
		if (empty ( $code ) || empty ( $state )) {
			$error = "当前商家未配置微信appsecret或绑定授权域名，请联系其完善。"; 						// 友好提示信息，不用让他回微信，其他非微信打开就直接留在微信了
			return $error;
		}
		
		// Step2：检测授权信息真实性
		$authrecheck = array (
				'authorize_id' => $state, // 刚才授权的id编号
				'is_del' => 0,
		);
		$reauthinfo = M ( 'wechatauthorize' )->where ( $authrecheck )->find (); 		// 检测刚才的授权信息
		if (! $reauthinfo) {
			$error = "授权失败，请从授权页开始进行微信授权。"; 										// 友善提示恶意编造的微信回跳
			return $error;
		}
		
		// Step3：获取企业信息
		$emap = array (
				'e_id' => $reauthinfo ['e_id'], // 授权的企业编号
				'is_del' => 0,
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); 						// 找到当前授权的企业信息
		if (! $einfo) {
			$error = "授权失败，您所访问的商家已不在服务区。"; 										// 友善提示非法、过期、恶意编造的商家（不在微动服务列表）
			return $error;
		}
		
		// Step4：调用code获取用户的openid，记录到session中并且完成授权
		$grantinfo = $this->openidAuth ( $einfo , $code ); 								// 获得授权信息
		if (! $grantinfo) {
			$error = "授权失败，商家没有配置微信appid或appsecret，请联系其完善。"; 					// 友善提示企业信息的问题
			return $error;
		} else if (empty ( $grantinfo ['openid'] )) {
			$error = "授权失败，微信平台授权系统繁忙，请稍后再试。"; 									// 友善提示微信授权接口的错误
			return $error;
		}
		$this->updateRecordAuth ( $state, $grantinfo );					// 补充记录微信用户的授权行为
		
		$openid = $grantinfo ['openid']; 												// 取出微信用户openid
		
		// Step5：调用微动用户模块，完成授权用户从无到有再到读的过程，记录session后跳转授权前页面
		import ( 'Class.BusinessLogic.UserModule.WechatUserModule', APP_PATH, '.php' ); // 载入微信授权用户模块
		
		$wechaterhandle = new WechatUserModule (); // 实例化处理微信用户授权登录类
		
		$userinfo = $wechaterhandle->getUserInfoByOpenId ( $einfo, $openid, true ); 	// 一定要处理返回值为空的情况
		if (empty ( $userinfo )) {
			$error = "授权失败，当前商家未填写微信original_id或微信授权系统繁忙，请稍后再尝试。"; 			// 友善提示获取微信授权用户信息失败的情况
			return $error;
		}
		
		$customerinfo = array (); 
		$wechaterinfo = array (); 
		
		if (! empty ( $_SESSION ['currentcustomer'] )) {
			$customerinfo = $_SESSION ['currentcustomer']; 								// 微信授权用户关联微动用户在其他商家访问过也保留
		}
		if (! empty ( $_SESSION ['currentwechater'] )) {
			$wechaterinfo = $_SESSION ['currentwechater']; 								// 微信授权用户在其他商家访问过也保留
		}
		
		$customerinfo [$einfo ['e_id']] = $userinfo ['weactuserinfo']; 					// 取出微信授权用户关联的微动用户信息
		$wechaterinfo [$einfo ['e_id']] = $userinfo ['wechatuserinfo']; 				// 取出微信用户信息
		
		session ( 'currentcustomer', $customerinfo ); 									// 在缓存中储存微信授权用户关联的微动用户信息
		session ( 'currentwechater', $wechaterinfo ); 									// 在缓存中储存微信用户信息
		
		// 如果系统需要，记录一笔用户登录的日志
		$this->loginRecord ( $einfo ['e_id'], $userinfo ['weactuserinfo'] ['customer_id'] ); // 登录系统日志
		
		header ( 'Location:' . $reauthinfo ['redirect_uri'] . '?authorize_id=' . $state );							// 跳转授权之前正确的地址
	}
	
}
?>