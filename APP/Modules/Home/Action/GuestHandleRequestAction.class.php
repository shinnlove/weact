<?php
/**
 * 处理注册、登录等游客请求的控制器。
 * @author 赵臣升
 * CreateTime:2015/10/02 17：18:25.
 */
class GuestHandleRequestAction extends MobileGuestRequestAction {
	
	/**
	 * 请求登录微动。
	 */
	public function weactLogin() {
		// 接收前台传来的登录账号和密码
		$ajaxinfo = array (
				'account' => I ( 'account' ),
				'password' => md5 ( I ( 'password' ) ),
				'e_id' => $this->einfo ['e_id'], // 特别注意，手机账号登录可以不唯一，对企业唯一（及其重要！！！）
				'is_del' => 0
		);
		
		if (! empty ( $ajaxinfo ['account'] ) && ! empty ( $ajaxinfo ['password'] )) {
			$customerinfo = M ( 'customerinfo' )->where ( $ajaxinfo )->find (); // 进行登录验证
			if ($customerinfo) {
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
				
				// 处理微动用户信息
				$weactlogin = array ();
				if (! empty ( $_SESSION ['currentcustomer'] )) {
					$weactlogin = $_SESSION ['currentcustomer']; 			// 如果session不空，先取出当前登录的用户
				}
				$weactlogin [$this->einfo ['e_id']] = $customerinfo; 		// 组装下微动用户登录格式
				session ( 'currentcustomer', $weactlogin ); 				// 重新存回session记录下登录的记录
				
				// 为了兼容老版本的用户和斯大林绑定微信女婿的用户，必须还看一下这个账号是否有微信用户信息
				if ($customerinfo ['user_type'] == 1 && ! empty ( $customerinfo ['openid'] )) {
					$wechaterlogin = array (); // 微信用户信息
					if (! empty ( $_SESSION ['currentwechater'] )) {
						$wechaterlogin = $_SESSION ['currentwechater']; 	// 如果session不空，先取出当前登录的微信用户
					}
					
					$openid = $customerinfo ['openid']; // 取出微信用户openid
					// 尝试找寻微信用户信息
					$wechatermap = array (
							'openid' => $openid, 
							'enterprise_wechat' => $this->einfo ['original_id'], 
							'is_del' => 0,
					);
					$wechaterinfo = M ( 'wechatuserinfo' )->where ( $wechatermap )->find (); // 尝试找寻当前微信用户信息
					if ($wechaterinfo) {
						$wechaterlogin [$this->einfo ['e_id']] = $wechaterinfo; // 微信用户信息存在
					} else {
						$wechaterlogin [$this->einfo ['e_id']] ['openid'] = $openid; // 没有微信用户也将openid绑定以免出错
					}
					session ( 'currentwechater', $wechaterlogin ); 			// 存入微信用户信息
				} 
				
				$loginresult = $this->loginRecord ( $this->einfo ['e_id'], $customerinfo ['customer_id'] ); // 最后记录一笔登录
			} else {
				$this->ajaxresult ['errCode'] = 10002;
				$this->ajaxresult ['errMsg'] = "账号或密码错误！";
			}
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台数据
	}
	
	/**
	 * 帮助微动用户注册斯大林账号，可后期在会员中心绑定微信，但注册时一律不绑定微信。
	 */
	public function weactRegister() {
		// 接收ajax的post参数
		$ajaxinfo = array (
				'customer_id' => date ( 'YmdHms' ) . randCode ( 4, 1 ), // 生成随机的customer_id（不用md5生成，因为可读性比较强）
				'customer_name' => I ( 'customer_name', '' ), 			// 如果真实姓名也填写了，则一并记录到数据库
				'account' => I ( 'account', '' ),
				'password' => I ( 'password', '' ),
				'e_id' => $this->einfo ['e_id'],
				'email' => I ( 'email', '' ), 							// 如果email也填写了，则一并记录到数据库
				'register_time' => time (), 							// 生成注册时间register_time，time()时间戳，过渡类型字段
				'member_level' => 0, 									// 新注册的会员默认都是普通会员（0）
				'inviter' => I ( 'yaoqingma', '' ), 					// 邀请其注册的用户
				'user_type' => 0, 										// 斯大林类型账号
		);
		
		// 检测数据必备性
		if (empty ( $ajaxinfo ['account'] ) || empty ( $ajaxinfo ['password'] )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "服务器忙，请稍后再试！";
			$this->ajaxReturn ( $this->ajaxresult ); // 直接毙掉ajax返回
		}
		
		$regresult = false; // 注册成功标记，默认为false
		$cinfotable = M ( 'customerinfo' ); // 实例化表对象
		
		// 检测当前微信用户openid是否注册过账号，防止微信用户注册多个号：
		$repeatcheck = array (
				'e_id' => $this->einfo ['e_id'], 		// 当前企业下
				'account' => $ajaxinfo ['account'], 	// 当前手机账号
				'is_del' => 0,
		);
		$repeatresult = $cinfotable->where ( $repeatcheck )->count (); // 检测该账号是否被注册过
		if ($repeatresult) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "当前商家该手机账号已经存在，忘记密码可使用密码找回！";
			$this->ajaxReturn ( $this->ajaxresult ); // 直接毙掉ajax返回
		}
		
		// 通过检测，可以注册（这样的斯大林账号注册必须保证系统内openid并不是必须空的）
		$registerresult = $cinfotable->add ( $ajaxinfo ); 			// 注册用户
		if ($registerresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			
			// 注册成功后等于已经默认登录，记录session同时记录login信息
			$customerinfo = array ();
			if (! empty ( $_SESSION ['currentcustomer'] )) {
				$customerinfo = $_SESSION ['currentcustomer']; 		// 先取出来给临时变量
			}
			$customerinfo [$this->einfo ['e_id']] = $ajaxinfo; 		// 增加当前商家注册的信息
			
			// 特别注意斯大林账号注册成功时要清空当前商家且仅为当前商家的微信用户信息（重要！！！）
			if (! empty ( $_SESSION ['currentwechater'] [$this->einfo ['e_id']] )) {
				$wechaterinfo = $_SESSION ['currentwechater']; 		// 取出当前商家的微信用户信息
				unset ( $wechaterinfo [$this->einfo ['e_id']] ); 	// 删除当前商家其他账号的微信信息，只有斯大林账号
				session ( "currentwechater", $wechaterinfo ); 		// 清空信息后存回去
			}
			
			// 记录用户的注册后自动登录
			$loginresult = $this->loginRecord ( $this->einfo ['e_id'], $ajaxinfo ['customer_id'], false, true );
		} else {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "网络繁忙，请使用其他登录方式或稍后再尝试！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端信息
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