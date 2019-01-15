<?php
/**
 * 分店授权管理控制器
 * @author 胡福玲。
 */
class SubbranchAuthorizeRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyUI的post请求，初始化读取当前商家下所有分店的授权登录账号信息
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/SubbranchAuthorize/subAuthorityView' ) );
			
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'auth_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$asmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],								// 获取当前商家id，以便显示当前商家的客户
		);
		$astable = M('auth_subbranch');
		$total = $astable->where($asmap)->count();
		$asinfolist = array ();
	
		if($total){
			$asinfolist = $astable->where ( $asmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$asnum = count($asinfolist);
			for($i = 0; $i < $asnum; $i ++){
				$asinfolist [$i] ['auth_time'] = timetodate( $asinfolist [$i] ['auth_time'] );
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $asinfolist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 添加分店授权信息post函数处理。
	 */
	public function addAuthorityConfirm() {
		$account = I ( 'aac' );		// 接收账号参数
		$ajaxresult = array (); 	// 定义要返回的ajax信息
	
		//判断当前选择分店是否已添加过账号信息
		$submap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'subbranch_id' => I ('cus'),
				'is_del' => 0
		);
		$satbl = M ( 'subbranchauth' );
		$subresult = $satbl->where($submap)->find();
	
		if($subresult){
			//当前选择分店已添加过账号，返回修改
			$ajaxresult = array (
					'errCode' => 10004,
					'errMsg' => '您已添加过该分店的授权信息，请勿重复添加!'
			);
		}else {
			//当前选择分店没有添加过账号，进一步判断当前输入账号是否在系统中已存在
			$accmap = array(
					'e_id' => $submap['e_id'],
					'auth_account' => $account,
					'is_del' => 0
			);
			$accresult = $satbl->where($accmap)->find();
			if($accresult){
				//当前输入账号在系统中已存在，返回修改
				$ajaxresult = array (
						'errCode' => 10005,
						'errMsg' => '该授权账号已存在，请修改后重新添加!'
				);
			}else{
				//当前输入账号在系统中不存在，可正常进行添加
				$authdata = array (
						'auth_id' => I ('aid'),
						'e_id' => $submap['e_id'],
						'subbranch_id' => I ('cus'),
						'auth_account' => $account,
						'auth_password' => md5(I ( 'apa' )),
						'plaintext_password' => I ( 'apa' ),
						'auth_open' => I ( 'iop' ),
						'auth_time' => time()
				);
				$authres = M ( 'subbranchauth' )->add ($authdata);
					
				if ($authres) {
					$ajaxresult = array (
							'errCode' => 0,
							'errMsg' => 'ok!'
					);
				} else {
					$ajaxresult = array (
							'errCode' => 10002,
							'errMsg' => '信息添加失败，请检查网络状况!'
					);
				}
			}
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 编辑授权功能。
	 */
	public function editAuthorityConfirm(){
		$oriacc = I ( 'oac' );
		$newacc = I ( 'aac' );
		$eid = $_SESSION ['curEnterprise'] ['e_id'];
		$satable = M ( 'subbranchauth' );
		$ajaxresult = array (); 	// 定义要返回的ajax信息
		//判断授权账号本次是否改动
		if($newacc != $oriacc){
			//账号做了改动，进一步判断本次修改账号是否在系统中已存在
			$newaccmap = array(
					'e_id' => $eid,
					'auth_account' => $newacc,
					'is_del' => 0
			);
			$newaccresult = $satable->where($newaccmap)->find();
			if($newaccresult){
				//当前修改的账号在系统中已存在，返回修改
				$ajaxresult = array (
						'errCode' => 10005,
						'errMsg' => '本次修改的账号已存在，请重新修改后再提交!'
				);
				$this->ajaxReturn ( $ajaxresult );
			}
		}
		$ecdata = array (
				'auth_id' => I ('aid'),
				'e_id' => $eid,
				'auth_account' => $newacc,
				'auth_password' => md5(I ( 'apa' )),
				'plaintext_password' => I ( 'apa' ),
				'auth_open' => I ( 'iop' )
		);
		$ecres = $satable->save ($ecdata);
	
		if ($ecres) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '分店授权信息修改成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '没有修改任何信息，请勿重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 删除授权信息post处理函数。
	 */
	public function deleteAuthority(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$delidlist = I ( 'rowdata' );			// 接收要删除的授权id列表
		$subauthtbl = M ( 'subbranchauth' );
		$delmap = array(
				'auth_id' => array('in', $delidlist),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$delresult = $subauthtbl->where($delmap)->setField('is_del', 1);
		if($delresult){
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		}else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "删除分店授权信息失败，请不要重复操作！"
			);
		}
		$this->ajaxReturn( $ajaxinfo );			// 将结果返回给前台
	}
	
	/**
	 * 分店权限开闭ajax处理函数。
	 */
	public function isOpenAuthority(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' ); // 接收参数
		$type = I ( 'type' ); 		// 接收操作类型
	
		// 定义要操作的范围
		$handlemap = array (
				'auth_id' => array( 'in', explode ( ',', $rowdata ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$handleresult = false; 		// 默认没成功
		if ($type == "on") {
			$onSave = array (
					'auth_open' => 1,
			);
			$handleresult = M ( 'subbranchauth' )->where ( $handlemap )->save ( $onSave );
		} else if ($type == "off") {
			$offSave = array (
					'auth_open' => 0,
			);
			$handleresult = M ( 'subbranchauth' )->where ( $handlemap )->save ( $offSave );
		}
	
		// 返回给前台结果
		if ($handleresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "操作失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 根据搜索条件进行模糊查询：分店名称、授权账号
	 */
	public function searchSubAuthority(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'subAuthorityView' ) );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'auth_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 			// 接收查询条件
		$content =  I ( 'searchcontent' ) ;				// 接收查询内容
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
	
		$searchmap = array (
				'e_id' => $e_id,
				$condition => array('like','%'.$content.'%')
		);
	
		$astable = M('auth_subbranch');
		$total = $astable->where($searchmap)->count();
		$asinfolist = array ();
	
		if($total){
			$asinfolist = $astable->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$asnum = count($asinfolist);
			for($i = 0; $i < $asnum; $i ++){
				$asinfolist [$i] ['auth_time'] = timetodate( $asinfolist [$i] ['auth_time'] );
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $asinfolist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	
}
?>