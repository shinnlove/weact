<?php
/**
 * 本控制器为社区游客控制器，必须去微信授权登录才行。
 * @author 赵臣升。
 * CreateTime：2014/12/26 12:20:36.
 */
class CommunityGuestAction extends Action {
	// _initialize为必须有的
	public function _initialize() {
		$data = array (
				'site_id' => $_REQUEST ['sId'], // 站点编号，现在先统一使用sId，否则前台修改量太大了
				'e_id' => $_REQUEST ['e_id'], 	// 页面接收传来的e_id
				'is_del' => 0  					// 在服务期内，没有被删除的企业
		);
		
		if (empty ( $data ['site_id'] )) $this->redirect ( 'Home/Tip/bindEID' ); 	// 如果页面没有接收到site_id，则直接输出请绑定一个ID号
		
		$sinfo = M ( 'communitysite' )->where ( $data )->find ();
		if (! $sinfo) $this->redirect ( 'Home/Tip/bindEID' ); 						// 如果企业编号错误，或者企业已经被删除，直接跳转错误页面（2014/11/13 03:10:25）
		$einfo = array();	// 企业信息
		if(! empty ( $data ['e_id'] ) && $data ['e_id'] != '-1' ) {
			$emap = array(
					'e_id' => $data ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		}
			
		$sinfo ['site_logo'] = assemblepath ( $sinfo ['site_logo'], true ); 		// 组装路径：2014/09/17 20:40:30，使用绝对路径供分享用
		$sinfo ['site_icon'] = assemblepath ( $sinfo ['site_icon'], true ); 		// 组装路径：2014/09/17 20:40:30
		$this->sinfo = $sinfo; 														// 子类使用父类的$this->einfo企业信息变量（以后打算不用enterprise）
		$this->einfo = $einfo;														// 可能为空，大部分有值
		$this->site_id = $sinfo ['site_id'];
		$this->e_id = $data ['e_id']; 												// 推送e_id到前台，保留传统风格（除非页面更改，否则必须有这个参数，后面的可能要用）
		
		// 授权登录部分：当且仅当微动平台开启授权、企业开启授权，用户并没有登录的情况下，进行授权验证
		$authorize_open = C ( 'AUTHORIZE_OPEN' ); 									// 获取授权开关
		if ($authorize_open && empty ( $_SESSION ['currentwechater'] [$data ['e_id']] ['openid'] )) {
			$authURL = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // 获取URL全路径
			$wechatauth = A ( 'Home/WeChatAuthorize' );
			$wechatauth->getAuth ( $authURL, $data ['e_id'] ); 						// 授权带参路由全路径
		}
		$_SESSION ['currentwechater'] [$this->e_id] ['openid'] = 'oeovpty2ScWq6YXxuMG0hY5qHOGA';	// 先模拟一个米拉雅测试的openid（放到服务器上一定要删掉！）
		$this->openid = $_SESSION ['currentwechater'] [$data ['e_id']] ['openid']; 	// 授权成功会有currentcustomer的openid，带上e_id区别，不管有没有这个值，放到if外边
	}
}
?>