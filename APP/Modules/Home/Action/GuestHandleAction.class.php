<?php
/**
 * @author 赵臣升。
 * 本控制器开放权限：所有人。
 * 作用：处理无需用户登录的事项和相关页面展示。
 * 
 * 本控制器函数一览：
 * customerRegister()，显示会员注册页面；
 * customerLogin()，显示会员登录页面；
 * customerReg()，处理会员注册函数；
 * customerLog()，处理会员登录函数；
 * customerLoginOut()，处理会员登出函数。
 */
class GuestHandleAction extends MobileGuestAction {
	/**
	 * 会员注册视图。
	 */
	public function customerRegister(){
		$afterloginurl = I ( 'redirecturi' ); 	// 获取回跳页面
		if (! empty ( $afterloginurl )) {
			$afterloginurl = urldecode ( $afterloginurl ); // 先解码URL
		}
		if (! empty ( $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] )) {
			header ( "Location: " . $afterloginurl ); // 已经登录过屏蔽登录页，则去授权后页面
		}
		$this->redirecturi = $afterloginurl; 	// 跳转URI
		$this->currentID = I ( 'cid' );			//如果是推广进入注册，则接收推广人的ID号
		//推送主页的分享信息
		$this->sharetitle = '欢迎注册并进入 '.$this->einfo ['e_name'].' 微世界!';
		$this->sharedesc = '快来注册'.$this->einfo ['e_name'].'的会员，享受各类会员特权吧!';
		$this->shareimg = $this->einfo ['e_square_logo'];
		$this->display();
	}
	
	/**
	 * 会员登录视图。
	 */
	public function customerLogin() {
		// 接收要跳转的URL地址并解码参数
		$afterloginurl = I ( 'redirecturi' ); // 获取回跳页面
		if (! empty ( $afterloginurl )) {
			$afterloginurl = urldecode ( $afterloginurl ); // 先解码URL
		}
		if (! empty ( $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] )) {
			header ( "Location: " . $afterloginurl ); // 已经登录过屏蔽登录页，则去授权后页面
		}
		// 未登录状态继续
		$this->refererURL = $afterloginurl;							// 前台js有判断是否为空
		//推送主页的分享信息
		$this->sharetitle = '欢迎注册并进入 ' . $this->einfo ['e_name'] . ' 微世界!';
		$this->sharedesc = '快登录' . $this->einfo ['e_name'] . '会员中心，享受各类会员特权吧!';
		$this->shareimg = $this->einfo ['e_square_logo'];
		$this->display();
	}
	
	/**
	 * 处理用户登出post的函数。
	 */
	public function customerLoginOut(){
		if (! IS_POST) _404 ( "页面不存在!" );
		
		$ajaxresult = array (
				'errCode' => 10000,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		
		if ($_REQUEST ['loginout']) {
			$result = $this->loginRecord ( $this->einfo ['e_id'], $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'], true );
			if ($result) {
				// 登出成功
				session('currentcustomer', null);
				$ajaxresult ['errCode'] = 0;
				$ajaxresult ['errMsg'] = "ok";
			}
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 记录登录/登出函数。
	 * @param string $e_id	形参传入当前商家
	 * @param string $customer_id 形参传入当前操作顾客
	 * @param boolean $loginOut  默认形参是登录，如果此处是true，代表该函数重载处理登出。
	 * @param boolean $registerlogin 以注册方式登录，如果此处是true，代表当前登录方式是新用户注册
	 * @return boolean $result 返回true代表记录成功，返回false代表记录失败。
	 */
	public function loginRecord($e_id = '', $customer_id = '', $loginOut = FALSE, $registerlogin = FALSE) {
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
	
	/**
	 * 给推广者加分。
	 * @param string $e_id 企业编号
	 * @param string $inviter 推广人编号
	 */
	public function inviterRecord($e_id = '', $inviter = '') {
		$inviterrecord = false; // 默认记录不成功
		if (! empty ( $inviter )) {
			$invitemap ['score_id'] = md5 ( uniqid ( rand (), true ) );
			$invitemap ['e_id'] = $e_id;
			$invitemap ['customer_id'] = $inviter;
			$invitemap ['change_time'] = time ();
			$invitemap ['change_amount'] = 5;
			$invitemap ['change_reason'] = '推广积分';
			$result = M ( 'customerscore' )->add ( $invitemap );
			if ($result) $inviterrecord = true;
		}
		return $inviterrecord;
	}
	
	/**
	 * 找回密码视图。
	 */
	public function findPwd(){
		$this->display();
	}
	
	/**
	 * 密码找回视图的验证码。
	 */
	public function verify() {
		import('ORG.Util.Image');
		ob_end_clean();
		Image::buildImageVerify (4,1,'png',60,26);
		ob_end_flush();
	}
	
	/**
	 * 实现密码找回，核对验证码，并发送验证邮件。
	 */
	public function customerFindPwd() {
		// 接收前台数据
		$data = array (
			'e_id' => I('e_id'),
			'account' => I ( 'account' ).trim(),
			'verifyCode' => I ( 'code', '', 'md5' ).trim()
		);
	
		if ($data ['verifyCode'] != session ( 'verify' )) {
			$this->error ( '验证码错误' );			// 验证码错误
		} else if (empty( $data ['account'] )) {
			$this->error ( '数据丢失，请重试！' );	// 服务器端未能接收到用户名或密码
		} else {
			$map ['account'] = $data ['account'];	//提取用户账户
			$map ['e_id'] = $data ['e_id'];			//提取商家id，不同店家可能有相同手机号的用户
			$result = M ( "customerinfo" )->where ( $map )->find ();	//查询该用户的信息

			if (! $result) {
				$this->error ( '账户不存在！' );		// 数据库中没有这个用户
			} else {
				if($result ['email']) {
					// 产生随机激活码，并存入session
					$userVerifyData = randCode ( 6, 1 );
					session ( 'userVerifyData', $userVerifyData );
					$currentTime = date ( "Y-m-d H:i:s" );
	
					//发送验证邮件
					sendmail ( $result ['email'], '帐号--邮箱身份验证', ' 您好！感谢您使用我们的服务，您正在进行邮箱验证，本次请求的验证码为： ' . $userVerifyData . '(为了保障您帐号的安全性，请在1小时内完成验证,仅限本次有效。)' . '————' . $currentTime );
					
					//准备好页面间传递的参数
					$returnData = array(
						'e_id' => $data['e_id'],
						'customer_id' => $result['customer_id'],
						'account' => $result['account']
					);

					$this->success ( '操作成功！', U ( 'Home/GuestHandle/verifyPwd',$returnData, '' ) );
				}else{
					$this->error('你未设置邮箱，无法使用此功能，请及时联系微信号或致电商家找回密码！');
				}
			}
		}
	}
	
	/**
	 * 密码激活视图。
	 */
	public function verifyPwd(){
		$this->customer_id = $_REQUEST ['customer_id'];
		$this->account = $_REQUEST ['account'];
		$this->display();
	}
	
	/**
	 * 激活码验证。
	 */
	public function customerVerifyPwd(){
		//接收前台数据
		$data = array (
			'customer_id' => I ('customer_id'),
			'e_id' => I ('e_id'),
			'account' => I ( 'account' ),
			'userVerifyData' => I( 'userVerifyData' ).trim(),
		);
		$userVerifyData = $_SESSION ['userVerifyData'];
			
		if($userVerifyData == $data['userVerifyData']){
			session('userVerifyData',0);
			session('resetPwdLimit',1);
			$this->customer_id = $data['customer_id'];
			$this->account = $data['account'];
			$this->redirect("resetPwd", array('customer_id' => $data ['customer_id'], 'account' => $data ['account'], 'e_id' => $data['e_id']));
		} else{
			$this->error('激活码错误或已经失效！');
		}
	}
	
	/**
	 * 密码重置视图。
	 */
	public function resetPwd() {
		$this->customer_id = $_REQUEST ['customer_id'];
		$this->account = $_REQUEST ['account'];
		$this->display();
	}
	
	/**
	 * 密码重置(找回密码)处理函数。
	 */
	public function customerResetPwd() {
		// 接收前台数据
		$data = array (
			'customer_id' => I ( 'customer_id' ),
			'account' => I ( 'account' ),
			'password' => md5(I ( 'password' ).trim()),
			'e_id' => I('e_id')
		);
		$userVerifyData = $_SESSION ['userVerifyData'];
		$resetPwdLimit = session ( 'resetPwdLimit' );
	
		if ($userVerifyData == 0 && $resetPwdLimit == 1) {
			if (empty( $data ['account'] )) {
				$this->error ( '操作失败,请重试' );		// 服务器端未能接收到用户账号或密码
			} else {
				$map ['customer_id'] = $data ['customer_id'];	//客户id
				$map ['e_id'] = $data ['e_id'];					//店家id
				$result = M ( 'customerinfo' )->where($map)->setField('password', $data['password']);
				if ($result) {
					// 重置成功，返回登陆页面
					$result = M ( 'customerinfo' )->where ( $map )->find ();
					session ( 'resetPwdLimit', null );
					session ( 'userVerifyData', null );
					session ( 'currentcustomer', $result );
					//重置密码后默认登录，在此记录登录的时间
					$result = $this->loginRecord( $data ['e_id'], $data ['customer_id'] );
					
					if($result){
						$this->success ( "密码重置成功！即将跳转客户中心！", U ( "Home/MemberHandle/customerCenter", array( 'e_id' => $data ['e_id'] ), '' ) );
					}
				} else {
					$this->error ( '密码重置失败！请检查网络状况！' );
				}
			}
		}
	}
}
?>