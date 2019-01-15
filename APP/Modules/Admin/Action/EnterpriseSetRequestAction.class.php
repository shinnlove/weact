<?php
/**
 * 企业信息设置请求控制器。
 * @author 赵臣升。
 * CreateTime:2015/07/18 21:53:36.
 */
class EnterpriseSetRequestAction extends PCRequestLoginAction {
	
	/**
	 * 修改用户添加的微信服务信息。
	 * 特别注意：企业信息在微动表里必然有，不然不可能登录。
	 */
	public function editInfoConfirm() {
		$edata = array (
				'e_name' => I ( 'en' ),
				'wechat_account' => I ( 'wa' ),
				'wechat_name' => I ( 'pan' ),
				'industry' => I ( 'inse' ),
				'site_name' => I ( 'sn' ),
				'focus_url' => I ( 'ajp' ),
				'e_square_logo' => I ( 'squarelogo' ), // 企业正方形LOGO上传
				'e_rect_logo' => I ( 'rectlogo' ), // 企业矩形LOGO上传
				'qr_code' => I ( 'qrcode' ), // 企业二维码上传
				'e_contact_person' => I ( 'ph' ),
				'e_province' => I ( 'prov' ),
				'e_city' => I ( 'city' ),
				'e_county' => I ( 'area' ),
				'e_address' => I ( 'addr' ),
				'e_longitude' => I ( 'lngx' ),
				'e_latitude' => I ( 'laty' ),
		);
	
		$condition = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$eitable = M ( 'enterpriseinfo' );
		$eiresult = $eitable->where ( $condition )->save ( $edata ); // 只更新data中的几个字段
	
		$ajaxresult = array (); // 要返回的ajax信息
		if ($eiresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '更新成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '没有修改新信息，请不要重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 企业上传LOGO图片并预览的函数。
	 */
	public function logoUpload() {
		$saveFilePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/logo/'; // 指定保存相对路径$saveFilePath
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUpload ( $saveFilePath ); // 处理上传及文件夹路径
		$this->ajaxReturn ( $uploadresult );
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/logo/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
	
	/**
	 * 接收绑定公众号Form表单提交确认的处理函数。
	 */
	public function bindPublicNumberConfirm() {
		$data = array (
				'original_id' => I ( 'oid' ),
				'appid' => I ( 'ai' ),
				'appsecret' => I ( 'as' )
		);
		// 缩写enterpriseinfo→ei
		$eimap = array (
				'e_id' => $_SESSION ['curEnterprise'] [e_id],
				'is_del' => 0
		);
		$eitable = M ( 'enterpriseinfo' );
		$einfo = $eitable->where ( $eimap )->find ();
		$einfo ['original_id'] = $data ['original_id'];
		$einfo ['appid'] = $data ['appid'];
		$einfo ['appsecret'] = $data ['appsecret'];
		$eiresult = $eitable->save ( $einfo ); // 带上了主键，不用where没关系
		if ($eiresult) {
			$this->ajaxReturn ( array (
					'status' => 1,
					'msg' => '信息更新成功！'
			), 'json' );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'msg' => '没有修改新信息，请不要重复提交！'
			), 'json' );
		}
	}
	
	/**
	 * 添加企业用户的微信支付V3信息（包括修改）的ajax处理函数。
	 */
	public function wechatPayV2Confirm() {
		if (! IS_POST) _404 ( "sorry 404 error, page not exist!", U ( 'Admin/EnterpriseSet/wechatPayV2', '', '', true ) ); // 阻止非法进入提交页面
	
		// 定义错误返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// 当前要操作的企业
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$einfotable = M ( 'enterpriseinfo' ); // 企业信息表enterpriseinfo对象
		$statushandle = false; // 默认支付状态不成功
		$saveresult = false; // 默认保存信息不成功
	
		// 接收微信支付V2的设置参数
		$data = array (
				'paysignkey' => I ( 'paysignkey' ),
				'partnerid' => I ( 'partnerid' ),
				'partnerkey' => I ( 'partnerkey' )
		);
		$opennow = I ( 'checked' ); // 接收开启支付的状态
	
		if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 0) {
			if ($opennow) {
				// 如果原来没开启，现在要开启，改0为2
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 2 );
			} else {
				// 原来没开启，现在也不开，不理会
				$statushandle = true;
			}
		} else if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 2) {
			if ($opennow) {
				// 如果原来已开启并且为2版，现在要开启，不理会
				$statushandle = true;
			} else {
				// 如果原来已开启并且为2版，现在要关闭，设置为0即可
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 0 );
			}
		} else if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 3) {
			if ($opennow) {
				// 如果原来已开启并且为3版，现在要开启，设置为2即可
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 2 );
			} else {
				// 如果原来已开启并且为3版，现在要关闭，不理会
				$statushandle = true;
			}
		} else {
			$statushandle = true;
		}
	
		// 保存信息（没有latest_modify，没变更的修改可能会失败，索性就不判断是否保存成功了）
		$saveresult = $einfotable->where ( $emap )->save ( $data );
	
		// 判断最终是否更新成功
		if ($statushandle) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 处理证书文件的上传
	 */
	public function uploadCertHandle() {
		$savepath = "./Certificate/WeChatPayCert/Version3/" . $_SESSION ['curEnterprise'] ['e_id'] . "/";
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUploadCert ( $savepath );
		$this->ajaxReturn ( $uploadresult );
	}
	
	/**
	 * 设置微信支付V3页面视图处理表单的提交
	 * 1、首先查找数据库中有没有相关e_id的记录
	 * 2、如果不存在该记录，那么此处应该是添加进去一条记录
	 * 3、如果存在该记录:说明此处是更新
	 */
	public function wechatPayV3Confirm() {
		if (! IS_POST) _404 ( "sorry 404 error, page not exist!", U ( 'Admin/EnterpriseSet/wechatPayV3', '', '', true ) ); // 阻止非法进入提交页面
	
		// 定义错误返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// 当前要操作的企业
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$secrettable = M ( "secretinfo" ); // 实例化secretinfo对象
		$einfotable = M ( 'enterpriseinfo' ); // 企业信息表enterpriseinfo对象
		$statushandle = false; // 默认支付状态不成功
		$saveresult = false; // 默认保存信息不成功
	
		// 拼装相关需要存入数据库的支付信息
		$data = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'apikey' => I ( 'psk' ),
				'cert_p12' => I ( 'acp12' ),
				'sslcert_path' => I ( 'acpem' ),
				'sslkey_path' => I ( 'akpem' ),
				'rootca_pem' => I ( 'rcpem' ),
				'appid' => $_SESSION ['curEnterprise'] ['appid'],
				'appsecret' => $_SESSION ['curEnterprise'] ['appsecret'],
				'mch_id' => I ( 'mid' ),
				'latest_modify' => time (),
				'remark' => '',
				'is_del' => 0
		);
		// 处理开启状况
		$opennow = I ( 'opennow' ); // 现在是否开启微信支付V3
	
		if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 0) {
			if ($opennow) {
				// 如果原来没开启，现在要开启，改0为3
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 3 );
			} else {
				// 原来没开启，现在也不开，不理会
				$statushandle = true;
			}
		} else if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 2) {
			if ($opennow) {
				// 如果原来已开启并且为2版，现在要开启，改2为3
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 3 );
			} else {
				// 如果原来已开启并且为2版，现在要关闭，不理会
				$statushandle = true;
			}
		} else if ($_SESSION ['curEnterprise'] ['tenpay_open'] == 3) {
			if ($opennow) {
				// 如果原来已开启并且为3版，现在要开启，不理会
				$statushandle = true;
			} else {
				// 如果原来已开启并且为3版，现在要关闭，设置为0即可
				$statushandle = $einfotable->where ( $emap )->setField ( 'tenpay_open', 0 );
			}
		} else {
			$statushandle = true;
		}
	
		// 查找数据库有无相关商家的敏感信息记录
		$selectresult = $secrettable->where ( $emap )->find ();
	
		if ($selectresult) {
			// 数据库中存在该记录，说明是更新	(add_time不变),不存在该记录，那么添加
			$data ['secretinfo_id'] = $selectresult ['secretinfo_id']; // 给出主键
			$saveresult = $secrettable->save ( $data ); // 根据条件保存修改的数据
			if (! $saveresult) {
				$ajaxresult ['errCode'] = 10002;
				$ajaxresult ['errMsg'] = '保存支付信息失败！';
			}
		} else {
			$data ['secretinfo_id'] = uniqid ( rand (), true );
			$data ['add_time'] = time ();
			$saveresult = $secrettable->add ( $data );
			if (! $saveresult) {
				$ajaxresult ['errCode'] = 10003;
				$ajaxresult ['errMsg'] = '添加支付信息失败！';
			}
		}
	
		if ($statushandle && $saveresult) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		$this->ajaxReturn ( $ajaxresult, 'json' );
	}
	
}
?>