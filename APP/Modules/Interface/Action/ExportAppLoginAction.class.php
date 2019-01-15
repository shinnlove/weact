<?php
/**
 * APP用户登录接口。
 * @author 胡睿。
 * CreateTime:2014/02/20 21:28:30.
 */
class ExportAppLoginAction extends PostInterfaceAction {
	/**
	 * APP用户登录接口。
	 * 实现思路：
	 * 1、根据前台提交的账号、密码信息，先查t_subbranch_login_info表，判断是不是分店老板，再根据t_shopguide_subordinate_shop视图，判断是不是导购；
	 * 2、如果是分店老板或者导购的话，则显示相关的信息，如果均不是的话，那么返回状态码；
	 * 3、前台提交的数据account，password。
	 */
	public function checkLogin() {
		// 首先判断前台提交的数据是否符合查询要求（账户名均密码不能为空）
		$account = $this->params ['account']; // 获取账号
		$password = $this->params ['password']; // 获取密码
		if (empty ( $account ) || empty ( $password )) {
			$this->ajaxresult ['errCode'] = 46201;
			$this->ajaxresult ['errMsg'] = 'APP登录接口参数错误！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		/**
		 * **** 如果前台提出的数据是符合要求的***
		 */
	
		// 第一步查询老板表t_subbranchauth，因为是直接查表，需加上is_del字段
		$bossloginmap = array (
				'auth_account' => $account, 
				'auth_password' => $password, 
				//'is_del' => 0 // 视图若变更，小心处理
		);
		$bosslogininfo = M ( 'subbranch_login_info' )->where ( $bossloginmap )->find (); // 此处刚才说用count，后来我觉得data里还要用到账号的一些信息，所以干脆还是直接取信息就好
		if ($bosslogininfo) {
			// 发现是分店老板账号
			// 对$bosslogininfo数据进行一些变换
			$bosslogininfo ['image_path'] = assemblepath ( $bosslogininfo ['image_path'], true ); // 分店图片
			$bosslogininfo ['auth_time'] = timetodate ( $bosslogininfo ['auth_time'] );
			// 对于ajaxresult赋值
			$this->ajaxresult ['data'] ['logintype'] = 2; // 2代表是老板
			$this->ajaxresult ['data'] ['logininfo'] = $this->bossLoginInfoPackage ( $bosslogininfo ); // 分店老板的一些信息打包
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 如果不是分店老板账号，接下来看是不是导购
		// 第二步查询导购视图t_shopguide_subordinate_shop，is_del字段在制作视图的时候已经处理了，此处不用加上
		$guideloginmap = array (
				'account' => $account, 
				'password' => $password 
		);
		$guidelogininfo = M ( 'shopguide_subbranch' )->where ( $guideloginmap )->find ();
		if ($guidelogininfo) {
			// 发现确实是导购
			// 对$guidelogininfo数据进行一些变换
			$guidelogininfo ['add_time'] = timetodate ( $guidelogininfo ['add_time'] );
			$guidelogininfo ['latest_modify'] = timetodate ( $guidelogininfo ['latest_modify'] );
			$guidelogininfo ['dimension_code'] = assemblepath ( $guidelogininfo ['dimension_code'], true ); // 导购二维码，绝对地址
			$guidelogininfo ['headimg'] = assemblepath ( $guidelogininfo ['headimg'], true ); // 导购头像，绝对地址
			$guidelogininfo ['image_path'] = assemblepath ( $guidelogininfo ['image_path'], true ); // 分店图片，绝对地址
			// 对于ajaxresult赋值
			$this->ajaxresult ['data'] ['logintype'] = 1; // 1代表是导购
			$this->ajaxresult ['data'] ['logininfo'] = $this->guideLoginInfoPackage ( $guidelogininfo ); // 导购与分店的一些信息打包
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else { // 既不是老板，也不是导购，说明用户名、密码错误
			$this->ajaxresult ['errCode'] = 46202;
			$this->ajaxresult ['errMsg'] = '用户名或密码错误！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 将老板登录信息字段打包的函数。
	 * @param array $packageinfo 要打包的信息
	 */
	private function bossLoginInfoPackage($packageinfo = NULL) {
		$finalinfo = array (); // 最终打包的信息
		if (! empty ( $packageinfo )) {
			$finalinfo = array (
					'sid' => isset ( $packageinfo ['subbranch_id'] ) ? $packageinfo ['subbranch_id'] : "", // subbranch_id字段封装为sid
					'eid' => isset ( $packageinfo ['e_id'] ) ? $packageinfo ['e_id'] : "", // e_id字段封装为eid
					'sname' => isset ( $packageinfo ['subbranch_name'] ) ? $packageinfo ['subbranch_name'] : "", // subbranch_name字段封装为sname
					'brand' => isset ( $packageinfo ['subbranch_brand'] ) ? $packageinfo ['subbranch_brand'] : "", // subbranch_brand字段封装为brand
					'province' => isset ( $packageinfo ['province'] ) ? $packageinfo ['province'] : "",
					'city' => isset ( $packageinfo ['city'] ) ? $packageinfo ['city'] : "",
					'county' => isset ( $packageinfo ['county'] ) ? $packageinfo ['county'] : "",
					'address' => isset ( $packageinfo ['subbranch_address'] ) ? $packageinfo ['subbranch_address'] : "", // subbranch_address字段封装为address
					'description' => isset ( $packageinfo ['subbranch_description'] ) ? $packageinfo ['subbranch_description'] : "", // subbranch_description字段封装为description
					'stype' => isset ( $packageinfo ['subbranch_type'] ) ? $packageinfo ['subbranch_type'] : 0, // subbranch_type字段封装为stype
					'manager' => isset ( $packageinfo ['manager'] ) ? $packageinfo ['manager'] : "", // manager字段封装为manager
					'cellphone' => isset ( $packageinfo ['contact_number'] ) ? $packageinfo ['contact_number'] : "", // contact_number字段封装为cellphone
					'simg' => isset ( $packageinfo ['image_path'] ) ? $packageinfo ['image_path'] : "" // image_path字段封装为simg
			);
		}
		return $finalinfo;
	}
	
	/**
	 * 将导购登录信息字段打包的函数。
	 * 带上isset字段处理null类型的值，方便三方判断类型。
	 * @param array $packageinfo 要打包的信息
	 */
	private function guideLoginInfoPackage($packageinfo = NULL) {
		$finalinfo = array (); // 最终打包的信息
		if (! empty ( $packageinfo )) {
			$finalinfo = array (
					'gid' => isset ( $packageinfo ['guide_id'] ) ? $packageinfo ['guide_id'] : "", // guide_id字段封装为gid
					'eid' => isset ( $packageinfo ['e_id'] ) ? $packageinfo ['e_id'] : "", // e_id字段封装为eid
					'sid' => isset ( $packageinfo ['subbranch_id'] ) ? $packageinfo ['subbranch_id'] : "", // subbranch_id字段封装为sid
					'gnumber' => isset ( $packageinfo ['guide_number'] ) ? $packageinfo ['guide_number'] : "", // guide_number字段封装为gnumber
					'gname' => isset ( $packageinfo ['guide_name'] ) ? $packageinfo ['guide_name'] : "", // guide_name字段封装为gname
					'nickname' => isset ( $packageinfo ['nickname'] ) ? $packageinfo ['nickname'] : "", // 一并返回导购的nickname
					'sex' => isset ( $packageinfo ['sex'] ) ? $packageinfo ['sex'] : 0, 
					'idcard' => isset ( $packageinfo ['id_card'] ) ? $packageinfo ['id_card'] : "", // id_card字段封装为idcard
					'birthday' => isset ( $packageinfo ['birthday'] ) ? $packageinfo ['birthday'] : "", 
					'cellphone' => isset ( $packageinfo ['cellphone'] ) ? $packageinfo ['cellphone'] : "", 
					'signature' => isset ( $packageinfo ['signature'] ) ? $packageinfo ['signature'] : "",
					'qrcode' => isset ( $packageinfo ['dimension_code'] ) ? $packageinfo ['dimension_code'] : "", // dimension_code字段封装为qrcode
					'headimg' => isset ( $packageinfo ['headimg'] ) ? $packageinfo ['headimg'] : "",
					'level' => isset ( $packageinfo ['guide_level'] ) ? $packageinfo ['guide_level'] : 0, // guide_level字段封装为level
					'type' => isset ( $packageinfo ['guide_type'] ) ? $packageinfo ['guide_type'] : 0, // guide_type字段封装为type
					'status' => isset ( $packageinfo ['busy_status'] ) ? $packageinfo ['busy_status'] : 1, // busy_status字段封装为status
					'star' => isset ( $packageinfo ['star_level'] ) ? $packageinfo ['star_level'] : 0.00, // 增加导购星级评定
					'sname' => isset ( $packageinfo ['subbranch_name'] ) ? $packageinfo ['subbranch_name'] : "", // subbranch_name字段封装为sname
					'brand' => isset ( $packageinfo ['subbranch_brand'] ) ? $packageinfo ['subbranch_brand'] : "", // subbranch_brand字段封装为brand
					'province' => isset ( $packageinfo ['province'] ) ? $packageinfo ['province'] : "", 
					'city' => isset ( $packageinfo ['city'] ) ? $packageinfo ['city'] : "", 
					'county' => isset ( $packageinfo ['county'] ) ? $packageinfo ['county'] : "", 
					'address' => isset ( $packageinfo ['subbranch_address'] ) ? $packageinfo ['subbranch_address'] : "", // subbranch_address字段封装为address
					'stype' => isset ( $packageinfo ['subbranch_type'] ) ? $packageinfo ['subbranch_type'] : 0, // subbranch_type字段封装为stype
					'manager' => isset ( $packageinfo ['manager'] ) ? $packageinfo ['manager'] : "", // manager字段封装为manager
					'simg' => isset ( $packageinfo ['image_path'] ) ? $packageinfo ['image_path'] : "" // image_path字段封装为simg
			);
		}
		return $finalinfo;
	}
}
?>