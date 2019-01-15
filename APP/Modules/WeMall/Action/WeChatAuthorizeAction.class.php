<?php
/**
 * 本控制器对用户访问页面进行微信授权认证。
 * @author 赵臣升
 * CreateTime：2014/10/28 23:50:36。
 */
class WeChatAuthorizeAction extends Action {
	var $currentURL = 'http://www.we-act.cn/weact/WeMall/WeChatAuthorize/wechatAuthCallback'; // 本类的静态变量，微信授权回调地址
	
	/**
	 * 微信授权认证函数。
	 * @param string $authURL 
	 * @param string $sinfo 请求授权的分店信息
	 * 当前用户没有去微信授权中心验证过，跳转微信中心验证（判断条件在调用授权认证控制器里）。
	 * 特别注意：不能授权跳转前后是同一个页面，否则接收不到code和state（已测试N次）
	 */
	public function getAuth($authURL = '', $sinfo = NULL) {
		// Step1：随机生成一个验证码
		$state = md5 ( uniqid ( rand (), true ) ); 			// 随机生成一个验证的state码
		
		// Step2：查出企业信息，将验证码作为索引，存入企业和要验证的url地址给$authinfo
		$emap = array (
				'e_id' => $sinfo ['e_id'],
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		$authinfo = array (
			$state => array (
					'curEnterprise' => $einfo,
					'authURL' => $authURL 
			) 
		);
		
		// Step3：存入session，并跳转微信授权认证。
		session ( 'authinfo', null ); 						// 保险起见，先清空一下这个变量
		session ( 'authinfo', $authinfo ); 					// 利用session存入authinfo数组信息，里边包含企业信息和要授权的地址，state作为索引
		
		// Step4：存入wechatauthorize微动授权表中，加固回跳机制
		$authorizeinfo = array (
				'authorize_id' => $state, 		// state作为回跳参数，微信会原样返回，这里作为主键方便检索
				'e_id' => $einfo ['e_id'], 		// 当前需要静默授权的企业,
				'redirect_uri' => $authURL, 	// 授权回跳页面URL
				'grant_time' => time (), 		// 微动授权时间
		);
		$addauth = M ( 'wechatauthorize' )->add ( $authorizeinfo ); // 添加授权信息
		
		// 存session和表都妥当了，再去微信
		header ( 'Location:' . $this->assembleAuthPath ( $einfo ['appid'], $this->currentURL, 'code', 'snsapi_base', $state ) );
	}
	
	/**
	 * 微信平台回调函数。
	 * 注：非主动打开，需要微信回调。主动打开也没有任何效果。
	 */
	public function wechatAuthCallback() {
		// Step1：检测参数
		$recvinfo = array (
				'code' => $_GET ['code'], 						// 尝试接收微信平台发来的数据
				'state' => $_GET ['state']  					// 尝试接收系统随机码
		);
		
		if (empty ( $recvinfo ['code'] ) || empty ( $recvinfo ['state'] )) header ( 'Location:' . $this->assembleAuthPath () ); // 没有code或state参数，直接回微信
		
		// Step2：检测授权信息
		$authrecheck = array (
				'authorize_id' => $recvinfo ['state'], // 刚才授权的id编号
				'is_del' => 0
		);
		$reauthinfo = M ( 'wechatauthorize' )->where ( $authrecheck )->find (); 	// 检测刚才的授权信息
		if (! $reauthinfo) header ( 'Location:' . $this->assembleAuthPath () ); 	// 授权信息不存在，直接去微信
		
		// Step3：获取企业信息
		$emap = array (
				'e_id' => $reauthinfo ['e_id'], // 授权的企业编号
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); 					// 找到当前授权的企业信息
		if (! $einfo) header ( 'Location:' . $this->assembleAuthPath () ); 			// 没有企业信息，也转到微信中（不在微动服务列表）
		
		// Step4：调用code获取用户的openid，记录到session中并且完成授权
		$wechatuser = $this->openidAuth ( $einfo , $recvinfo ['code'] ); 			// 获得用户信息
		$_SESSION ['currentwechater'] [$einfo ['e_id']] = $wechatuser; 				// 将授权的微信用户信息存入session中
		$this->updateRecordAuth ( $recvinfo ['state'], $wechatuser );				// 补充记录微信用户的授权行为
		
		// 用微信用户信息换取customer用户信息，如果没有，则系统代注册一条信息
		$customermap = array (
				'e_id' => $einfo ['e_id'], 				// 当前商家下
				'openid' => $wechatuser ['openid'], 	// 当前微信用户openid
				'is_del' => 0 							// 没有被删除的客户
		);
		$customerinfo = M ( 'customerinfo' )->where ( $customermap )->find (); // 尝试找出这样的用户
		if (! $customerinfo) {
			// 如果没有这样的用户，系统代注册一条信息
			$customerinfo = $this->delegateRegister ( $einfo ['e_id'], $wechatuser ); // 用e_id和openid代注册一条用户信息
		}
		// 如果有这样的用户，取消原来任何登录的用户，默认为微信重新登录，将用户信息放入session中
		session ( 'currentcustomer', null ); 						// 先清空当前登录用户信息（可能和微信账号不是一个账号）
		session ( 'currentcustomer', $customerinfo ); 				// 再记录当前用户信息
		$loginresult = $this->loginRecord ( $einfo ['e_id'], $customerinfo ['customer_id'] ); // 记录用户登录
		
		header ( 'Location:' . $reauthinfo ['redirect_uri'] );		// 跳转授权之前正确的地址
	}
	
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
	 * 进一步利用code获取用户信息的函数。
	 * @param array $einfo 企业信息 
	 * @param string $code 回跳code
	 * @param string $grant_type 授权方式：authorization_code
	 * @return array $wechatuser 微信授权用户信息。
	 */
	private function openidAuth($einfo = NULL, $code = '', $grant_type = 'authorization_code') {
		$getopenidURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
		$openidParams = array (
				'appid' => $einfo ['appid'],
				'secret' => $einfo ['appsecret'],
				'code' => $code, // 授权回跳code
				'grant_type' => $grant_type 
		);
		$jsoninfo = http ( $getopenidURL, $openidParams );
		$wechatuser = json_decode ( $jsoninfo, true );
		// 进一步调用微信用户信息接口获取用户信息
		$openid = $wechatuser ['openid']; 					// 取出得到的openid
		$swc = A ( 'Service/WeChat' ); 						// 实例化控制器
		$getinfo = $swc->getUserInfo ( $einfo, $openid ); 	// 获取微信用户信息
		$wechatuser ['subscribe'] = $getinfo ['subscribe']; 	// 用户是否关注公众号
		if ($getinfo ['subscribe'] == 1) {
			// 关注则继续补充信息
			$wechatuser ['nickname'] = $getinfo ['nickname']; 		// 用户的微信昵称
			$wechatuser ['sex'] = $getinfo ['sex']; 				// 性别
			$wechatuser ['language'] = $getinfo ['language']; 		// 使用语言
			$wechatuser ['city'] = $getinfo ['city']; 				// 所在城市
			$wechatuser ['province'] = $getinfo ['province']; 		// 所在省份
			$wechatuser ['country'] = $getinfo ['country']; 		// 所在国家
			$wechatuser ['headimgurl'] = $getinfo ['headimgurl']; 	// 用户头像（插表的时候更换成head_img_url）
			$wechatuser ['subscribe_time'] = $getinfo ['subscribe_time']; // 关注公众号时间
			$wechatuser ['remark'] = $getinfo ['remark']; // 企业对用户的备注名
			$wechatuser ['groupid'] = $getinfo ['groupid']; // 用户所在的分组号
		}
		// 不管是否关注如果没有找到微信用户信息，都注册一下
		$delegatewechater = $this->delegateWechatRegister ( $einfo, $wechatuser ); // 委托注册
		return $wechatuser;
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
	 * 为某企业的某微信用户注册
	 * @param array $einfo 企业信息
	 * @param array $wechaterinfo 微信用户信息
	 * @return $boolean $delegatehandle 是否委托处理成功
	 */
	private function delegateWechatRegister($einfo = NULL, $wechaterinfo = NULL) {
		$delegatehandle = false; // 默认处理结果
		// Step2：检测wechatuserinfo表中是否有该用户，如果没有则新插入一条信息（新用户）；如果有更新其信息（老用户新关注）。
		$wutable = M ( 'wechatuserinfo' ); // 实例化微信用户表
		$wuexist = array (
				'enterprise_wechat' => $einfo ['original_id'], // 当前微信公众号original_id
				'openid' => $wechaterinfo ['openid'], // 当前微信用户
				'is_del' => 0 // 没有被删除的
		);
		$oldwuinfo = $wutable->where ( $wuexist )->find (); // 尝试找到微信用户信息
		if (! $oldwuinfo) {
			// 如果没找到这样的用户，说明是全新的用户，系统为其插入一条新微信用户信息
			$newwuinfo = array (
					'user_info_id' => md5 ( uniqid ( rand (), true ) ), // 生成主键
					'enterprise_wechat' => $einfo ['original_id'], // 企业微信号openid
					'group_id' => $wechatuser ['groupid'], 
					'subscribe' => 1, // 用户关注
					'openid' => $wechatuser ['openid'], // 当前微信用户openid
					'nickname' => $wechatuser ['nickname'], // 微信用户昵称
					'sex' => $wechatuser ['sex'], // 微信用户性别
					'language' => $wechatuser ['language'], // 微信用户使用语言
					'city' => $wechatuser ['city'], // 微信用户所在城市
					'province' => $wechatuser ['province'], // 微信用户所在省份
					'country' => $wechatuser ['country'], // 微信用户所在国家
					'head_img_url' => $wechatuser ['headimgurl'], // 微信用户头像
					'subscribe_time' => $wechatuser ['subscribe_time'], // 用户的关注时间
					'add_time' => time (), // 新增这条用户记录的时间
					'latest_active' => time (), // 新用户关注，最新活跃时间是现在
			
			);
			$delegatehandle = $wutable->add ( $newwuinfo ); // 将新微信用户信息插入到微信用户表中
		} 
		return $delegatehandle;
	}
	
	/**
	 * 为某个商家下的某个微信用户代注册一个账号。
	 * @param string $e_id 商家编号
	 * @param array $wechaterinfo 微信用户编号
	 * @return array $customerinfo 用户信息
	 */
	private function delegateRegister($e_id = '', $wechaterinfo = NULL) {
		$newuserinfo = array (
				'customer_id' => date ( 'YmdHms' ) . randCode ( 4, 1 ), // 顾客编号
				'openid' => $wechaterinfo ['openid'], // 微信用户openid
				'nick_name' => $wechaterinfo ['nickname'], // 用户昵称
				'e_id' => $e_id, // 商家编号
				'register_time' => time (),
				'remark' => "用户（微信openid为" . $wechaterinfo ['openid'] . "）在" . timetodate ( time () ) . "授权登录微动平台移动端总店商城，系统为其代注册账号。"
		);
		if (empty ( $newuserinfo ['nick_name'] )) {
			$newuserinfo ['nick_name'] = "未关注某匿名游客（微信openid为）" . $newuserinfo ['openid']; // 补充没有关注下无法获取昵称的情况
		}
		$addresult = M ( 'customerinfo' )->add ( $newuserinfo );
		return $newuserinfo;
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
				'operate_type' => intval ( $loginOut )						// 当前操作类型是注销（0：登录；1：注销），把false或true转成整型0或1
				//device和ip暂时先不记录
		);
		if ($registerlogin) {
			$loginHandleInfo ['remark'] = "新用户注册，系统默认登录";
		}
		$result = M ( 'loginrecord' )->add ( $loginHandleInfo );			// 向数据库添加登录、登出记录
		return $result;
	}
	
}
?>