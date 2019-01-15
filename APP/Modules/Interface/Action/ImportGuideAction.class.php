<?php
/**
 * 导购信息、状态等输入接口。
 * @author 黄昀
 * CreateTime:2015/03/13 14:39:25
 */
class ImportGuideAction extends PostInterfaceAction {
	/**
	 * 修改导购密码接口。
	 */
	public function modifyGuidePassword() {
		// 以下几种情况直接毙掉
		// 接收到的post数据中gid如果为空
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中old_pwd如果为空
		if (empty ( $this->params ['old_pwd'] )) {
			$this->ajaxresult ['errCode'] = 46251;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少原密码！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中new_pwd如果为空
		if (empty ( $this->params ['new_pwd'] )) {
			$this->ajaxresult ['errCode'] = 46252;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少新密码！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		
		$guidetable = M ( 'shopguide' ); // 导购表
		// 可能要修改的信息
		$updateinfo = array (
				'password' => $this->params ['new_pwd'], // 新密码
				'latest_modify' => time ()
		);
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$guideinfo = $guidetable->where ( $guidemap )->find (); // 查找当前要修改信息的导购
		if ($guideinfo) {
			// 如果有这样的导购存在
			if ($guideinfo ['password'] == $this->params ['old_pwd']) {
				// 如果原始密码正确，则允许其修改
				$saveresult = $guidetable->where ( $guidemap )->save ( $updateinfo ); // 修改导购账号的密码
				if ($saveresult) {
					$this->ajaxresult ['errCode'] = 0;
					$this->ajaxresult ['errMsg'] = 'ok';
				} else {
					$this->ajaxresult ['errCode'] = 46254;
					$this->ajaxresult ['errMsg'] = '修改密码失败，请不要重复提交相同数据！';
				}
			} else {
				// 如果原始密码错误，则不允许修改
				$this->ajaxresult ['errCode'] = 46253;
				$this->ajaxresult ['errMsg'] = '修改密码失败，原密码不正确！';
			}
		} else {
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 接收POST并更改导购状态的接口函数。
	 */
	public function alterGuideStatus() {
		$status = $this->params ['status']; // 接收三方POST过来的导购挂起或在线的状态，0代表在线，1代表挂起
		// 直接毙掉的情况有：
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (empty ( $status )) {
			$this->ajaxresult ['errCode'] = 46301;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购在线或挂起状态码！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if ($status != 1 && $status != 2) {
			$this->ajaxresult ['errCode'] = 46302;
			$this->ajaxresult ['errMsg'] = '接口参数错误，导购在线或挂起状态码有误！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$guidetable = M ( 'shopguide' );
		// 要更新的信息
		$updatedata = array (
				'busy_status' => $status,
				'latest_modify' => time () 
		);
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0 
		);
		$guideinfo = $guidetable->where ( $guidemap )->find (); // 查询该导购的信息
		if ($guideinfo) {
			$updateresult = false; // 默认改变状态不成功
			if ($guideinfo ['busy_status'] != $status) {
				$updateresult = $guidetable->where ( $guidemap )->save ( $updatedata );
			} else {
				$updateresult = true; // 要改变的状态就是当前状态（网络卡顿），直接默认true
			}
			if ($updateresult) {
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
			} else {
				$this->ajaxresult ['errCode'] = 46303;
				$this->ajaxresult ['errMsg'] = '更新导购在线或挂起状态失败！';
			}
		} else {
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * =======以下区域为修改导购信息接口=========
	 * 包含：1、姓名；2、性别；3、身份证号；4、生日；5、手机号；6、个性签名。
	 * 因为每次都是异步保存，所以采用分接口方式。
	 */
	
	/**
	 * 修改信息接口一、修改导购姓名接口。
	 */
	public function modifyGuideName() {
		$modifyfield = "name"; // 接口字段
		$dbfield = "guide_name"; // 数据库字段
		
		// 接收到的姓名更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 46304;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购姓名参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口二、修改导购昵称接口。
	 */
	public function modifyGuideNickname() {
		$modifyfield = "nickname"; // 接口字段
		$dbfield = "nickname"; // 数据库字段
		
		// 接收到的昵称更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 46315;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购昵称参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口三、修改导购性别接口。
	 */
	public function modifyGuideSex() {
		$modifyfield = "sex"; // 接口字段
		$dbfield = "sex"; // 数据库字段
		$sex = intval ( $this->params [$modifyfield] ); // 接收到的性别
		// 接收到的性别更改字段为空
		if (empty ( $sex )) {
			$this->ajaxresult ['errCode'] = 46305;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购性别参数，性别参数也不能为0！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if ($sex != 1 && $sex != 2) {
			$this->ajaxresult ['errCode'] = 46306;
			$this->ajaxresult ['errMsg'] = '接口参数错误，性别参数只能为1（男）或2（女）！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口四、修改导购身份证号接口。
	 */
	public function modifyGuideIDCard() {
		$modifyfield = "id"; // 接口字段
		$dbfield = "id_card"; // 数据库字段
		$idcard = $this->params [$modifyfield]; // 身份证号
		// 接收到的姓名更改字段为空
		if (empty ( $idcard )) {
			$this->ajaxresult ['errCode'] = 46307;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购身份证参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (strlen ( $idcard ) != 15 && strlen ( $idcard ) != 18) {
			$this->ajaxresult ['errCode'] = 46308;
			$this->ajaxresult ['errMsg'] = '接口参数错误，身份证号码无效！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口五、修改导购生日接口。
	 */
	public function modifyGuideBirthday() {
		$modifyfield = "birthday"; // 接口字段
		$dbfield = "birthday"; // 数据库字段
		
		// 接收到的姓名更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 46309;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购生日参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口六、修改导购联系电话接口。
	 */
	public function modifyGuideCellphone() {
		$modifyfield = "cellphone"; // 接口字段
		$dbfield = "cellphone"; // 数据库字段
		
		// 接收到的姓名更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 46310;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购手机号码参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口七、修改导购个性签名接口。
	 */
	public function modifyGuideSignature() {
		$modifyfield = "signature"; // 接口字段
		$dbfield = "signature"; // 数据库字段
		$signaturetemp = $this->params [$modifyfield]; // 获取个性签名
		// 接收到的姓名更改字段为空
		if (empty ( $signaturetemp )) {
			$this->ajaxresult ['errCode'] = 46311;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购个性签名参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (strlen ( $signaturetemp ) > 100) {
			$this->ajaxresult ['errCode'] = 46312;
			$this->ajaxresult ['errMsg'] = '接口参数错误，个性签名参数长度不能超过100！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyGuideInfo ( $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口八、修改导购粉丝的备注名。
	 * 特别注意：当顾客换导购的时候，一定要将备注名置空。
	 */
	public function modifyFansRemarkName() {
		// 以下几种参数错误直接毙掉
		// 接收到的post数据中gid如果为空
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中cid如果为空
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 46251;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中remarkname如果为空
		if (empty ( $this->params ['remarkname'] )) {
			$this->ajaxresult ['errCode'] = 46252;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购对顾客的备注名！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		
		// 如果上述几种情况都通过验证，则继续看该顾客是不是当前导购是参数里的导购编号
		$customerguidetable = M ( 'customerguide' );
		$checkmap = array (
				'customer_id' => $this->params ['cid'],
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$cinfo = $customerguidetable->where ( $checkmap )->find (); // 找出该导购的该顾客信息
		if ($cinfo) {
			// 如果找到这样的顾客
			$modifyresult = $customerguidetable->where ( $checkmap )->setField ( 'guide_remarkname', $this->params ['remarkname'] ); // 修改其备注名
			if ($modifyresult) {
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = 'ok';
			} else {
				$this->ajaxresult ['errCode'] = 10001;
				$this->ajaxresult ['errMsg'] = '请不要重复提交相同的备注名！';
			}
		} else {
			// 如果找不到这样的顾客，说明该顾客不存在或者该顾客并没有选择当前导购
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '该顾客没有选择该导购，无法修改备注名！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 私有被调函数，公共的修改导购信息接口，被以上6个函数调用，需指明要更新的字段（头像信息除外）。
	 * @param string $interfacefield 接口字段
	 * @param string $localdbfield 本地数据库字段
	 */
	private function modifyGuideInfo($interfacefield = '', $localdbfield = '') {
		// 接收到的post数据中gid如果为空
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$guidetable = M ( 'shopguide' ); // 导购表
		// 要更新的信息
		$modifyinfo = array (
				$localdbfield => $this->params [$interfacefield], // 要修改的信息
				'latest_modify' => time (),
		);
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$guideinfo = $guidetable->where ( $guidemap )->find (); // 查找当前要修改信息的导购
		if ($guideinfo) {
			$saveresult = $guidetable->where ( $guidemap )->save ( $modifyinfo ); // 修改导购信息
			if ($saveresult) {
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = 'ok';
			} else {
				$this->ajaxresult ['errCode'] = 46313;
				$this->ajaxresult ['errMsg'] = '保存信息失败，请不要重复提交相同数据！';
			}
		} else {
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
}
?>