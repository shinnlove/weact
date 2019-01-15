<?php
/**
 * 本控制器处理游客浏览的页面，和推送企业信息，处理授权和分店。
 * @author 王健、赵臣升。
 * 优化时间：2014/11/13 03:24:25.
 */
class GuestCommonAction extends Action {
	// _initialize为必须有的
	public function _initialize() {
		$data = array (
				'e_id' => I ( 'e_id' ), 											// 页面接收传来的e_id
				'is_del' => 0  														// 在服务器内，没有被删除的企业
		);
		
		$sid = I ( 'shop_id' ); 													// 尝试接收shop_id字段，如果没有接收到，就是默认的Session变量（或空）
		if (! empty ( $sid )) session ( 'shop_id', $sid ); 							// 刷新session的shop_id变量，只要不传新的shop_id，就默认是原来的shop_id变量
		if (empty ( $data ['e_id'] )) $this->redirect ( 'Home/Tip/bindEID' ); 		// 如果页面没有接收到e_id，则直接输出请绑定一个ID号
		
		$einfo = M ( 'enterpriseinfo' )->where ( $data )->find ();
		if (! $einfo) $this->redirect ( 'Home/Tip/bindEID' ); 						// 如果企业编号错误，或者企业已经被删除，直接跳转错误页面（2014/11/13 03:10:25
		
		$einfo ['e_square_logo'] = assemblepath ( $einfo ['e_square_logo'], true ); // 组装路径：2014/09/17 20:40:30，使用绝对路径供分享用
		$einfo ['e_rect_logo'] = assemblepath ( $einfo ['e_rect_logo'], true ); 	// 组装路径：2014/09/17 20:40:30
		$this->enterprise = $einfo; 												// 始终推送到前台
		$this->einfo = $einfo; 														// 子类使用父类的$this->einfo企业信息变量（以后打算不用enterprise）
		$this->e_id = $data ['e_id']; 												// 推送e_id到前台，保留传统风格（除非页面更改，否则必须有这个参数，后面的可能要用）
		
		// 授权登录部分：当且仅当微动平台开启授权、企业开启授权，用户并没有登录的情况下，进行授权验证
		$authorize_open = C ( 'AUTHORIZE_OPEN' ); 									// 获取授权开关
		if ($authorize_open && $einfo ['authorize_open'] && empty ( $_SESSION ['currentwechater'] [$data ['e_id']] ['openid'] )) {
			$authURL = C ( 'DOMAIN' ) . $_SERVER ['REQUEST_URI']; 	// 特别注意：一定是访问微动才经过这个控制器，所以这是跟着微动根目录的，再带上域名就是全路径;
			$wechatauth = A ( 'WebSite/WeChatAuthorize' ); 			// 实例化授权控制器
			$wechatauth->getAuth ( $authURL, $this->einfo ); 		// 授权带参路由路径
		}
		$local_test = C ( 'LOCAL_TEST_ON' ); // 判断是否本地测试（远程服务器端是false配置）
		if ($local_test) {
			if ($this->einfo ['e_id'] == "201405011018300002") $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'] = 'oN_J8ji0p59PDs_X9Dy5lFa43EoU'; // 模拟一个G5G6测试的openid
			else if ($this->einfo ['e_id'] == "201405291912250003") $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'] = 'oYN0nuDlldmiqglpXHL1GpeuHjt0'; // 模拟一个G5G6测试的openid
			else if ($this->einfo ['e_id'] == "201406261550250006") $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'] = 'oeovpty2ScWq6YXxuMG0hY5qHOGA'; // 模拟一个微动测试的openid
			else if ($this->einfo ['e_id'] == "201412021712300012") $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'] = 'ovNEts3zv0n7REfdDfi0WKEOyCSM'; // 模拟一个米拉雅测试的openid
		}
		$this->openid = $_SESSION ['currentwechater'] [$data ['e_id']] ['openid']; 	// 授权成功会有currentcustomer的openid，带上e_id区别，不管有没有这个值，放到if外边
	}
}
?>