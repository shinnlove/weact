<?php
/**
 * 游客动态请求控制器。
 * @author 赵臣升。
 * CreateTime:2015/05/15 16:06:25.
 */
class GuestRequestAction extends GuestMallAction {
	/**
	 * 登录检测。
	 */
	public function loginCheck() {
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		}
		
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		
		$account = I ( 'username' ); // 接收用户名
		$password = I ( 'password' ); // 接收用户密码
		
		// 进行检测
		if (empty ( $account ) || empty ( $password )) {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "用户名或密码不能为空。";
		} 
		
		$customermap = array (
				'e_id' => $this->eid,
				'account' => $account,
				'password' => md5 ( $password ), // 密码是md5加密的
				'is_del' => 0
		);
		$customerinfo = M ( 'customerinfo' )->where ( $customermap )->find (); // 找到这个用户
		
		if ($customerinfo) {
			// 用户成功登录，记录session
			$loginresult = $this->loginRecord ( $this->einfo ['e_id'], $ajaxinfo ['customer_id'] ); // 记录用户登录
			session ( 'currentcustomer', null ); // 先清空当前登录用户信息
			session ( 'currentcustomer', $customerinfo ); // 再记录当前用户信息
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "用户名或密码错误！";
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
	
	/**
	 * 线下O2O的微信授权登录。
	 */
	public function wechatAuthorizeLogin() {
		$authURL = I ( 'from' ); // 接收最后一次访问的页面URL
		if (empty ( $authURL )) {
			$authURL = U ( 'WeMall/Store/storeIndex', array ( 'sid' => $this->sid ), '' ); // 如果没有接收到来自的地址，默认访问店铺首页
		}
		$openid = $_SESSION ['currentwechater'] [$this->eid] ['openid']; // 尝试取出openid
		if ($openid) {
			// 已经微信授权，直接跳转页面
			header ( 'Location:' . C ( 'DOMAIN' ) . $authURL );	//跳转正确的地址
		}
		$wechatauth = A ( 'WeMall/WeChatAuthorize' ); 		// 采用本分组下的微信授权（和HOME分组下的微信授权有点不一样，切勿混为一谈）
		$wechatauth->getAuth ( C ( 'DOMAIN' ) . $authURL, $this->sinfo ); 	// 授权带参路由路径
		
		
		/* $openid = $_SESSION ['currentwechater'] [$this->eid] ['openid']; // 尝试取出openid
		if (! empty ( $openid )) {
			// 已经微信授权，直接跳转页面
			header ( 'Location:' . C ( 'DOMAIN' ) . $authURL );	//跳转正确的地址
		} else {
			// 没有微信授权过，就去微信授权
			$wechatauth = A ( 'WeMall/WeChatAuthorize' ); 		// 采用本分组下的微信授权（和HOME分组下的微信授权有点不一样，切勿混为一谈）
			$wechatauth->getAuth ( C ( 'DOMAIN' ) . $authURL, $this->sinfo ); 	// 授权带参路由路径
		} */
	}
	
}
?>