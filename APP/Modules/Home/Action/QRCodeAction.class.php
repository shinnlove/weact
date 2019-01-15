<?php
/**
 * 非微信二维码扫描控制器，处理微动平台所有的非微信二维码扫描、线上支付等。
 * @author 赵臣升。
 * CreateTime:2014/11/10 01:19:20.
 * 该控制器为核心控制器，其测试代码必须放到服务器上。
 * 特别注意：想要生成自定义的二维码，必须满足条件：
 * 1、在微动平台开启开发者模式，有appid和appsecret。
 * 2、在微动平台开启授权模式，域名为www.we-act.cn，否则无法进行微信用户授权。
 * 使用appid为了识别是哪个企业的哪个微信用户，否则没办法知道扫描的微信用户是谁，
 * 很多服务必须识别微信用户，因此二维码地址中必须带有e_id的参数，如果没有e_id默认二维码已过期。
 */
class QRCodeAction extends Action {
	
	/**
	 * 处理微信用户扫描二维码。
	 * 暂定二维码用途顺序：
	 * 1、企业推广活动二维码；
	 * 2、门店（分店）集粉二维码；
	 * 3、导购（推销员）二维码名片；
	 * 4、商品详情（产品：服饰款式、饮食菜名）；
	 * 5、线上扫码支付；
	 * 6、餐桌二维码扫描（在餐桌坐下）；
	 * 7、漂流瓶、抛绣球等交流互动活动；
	 * 8、场景应用SceneApp。
	 */
	public function qrScan() {
		$params = $_REQUEST; // 接收URL参数
		$scanquery = array(
				'qrscan_id' => $params ['scanid'],
				'out_service' => 0,
				'is_del' => 0
		);
		$scanresponse = M ('qrscanresponse')->where ( $scanquery )->find ();							// 查询扫描服务表
		
		if (empty ( $_SESSION ['currentwechater'] [$scanresponse ['e_id']] ['openid'] )) {
			$urlparams = $_REQUEST ['_URL_'];										// 某些URL方式可能没有e_id
			$authURL = '';
			$existe_id = false;														// 检测是否识别到e_id
			for($i = 0; $i < count ( $urlparams ); $i ++) {
				$authURL = $authURL . $urlparams [$i] . '/'; 						// 拼接路由参数
				if ($urlparams [$i] == 'e_id')
					$existe_id = true;
			}
			if (! $existe_id) {
				$authURL = $authURL . 'e_id/' . $_REQUEST ['e_id'] . '/';			// 未识别到e_id再拼接e_id
			}
			$wechatauth = A ( 'Home/WeChatAuthorize' );
			$wechatauth->getAuth ( $authURL, $scanresponse ['e_id'] ); 						// 授权带参路由路径
		}
		$this->openid = $_SESSION ['currentwechater'] [$scanresponse ['e_id']] ['openid'];
		
		if($scanresponse) {
			if ($scanresponse ['handle_type'] == 0) {
				$service = A ( $scanresponse ['group_index'] . '/' . $scanresponse ['action_index'] );	// 直接实例化控制器
				$service->$scanresponse ['function_index'] ( $params );									// 调用函数处理扫描事件
			} else if ($scanresponse ['handle_type'] == 1) {
				import ( $scanresponse ['object_path'], APP_PATH, '.php' ); // 载入处理类
				$service = new $scanresponse ['object_name']; // 实例化类名
				$serviceresult = $service->$scanresponse ['handle_function'] ( $scanresponse ['e_id'], $this->openid, $params ); // 调用处理函数
			}
		} else {
			$this->error( '该二维码已过期!' );
		}
	}
}
?>