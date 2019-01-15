<?php
class EnterpriseAuthAction extends SAViewLoginAction {
	
	public function readEnterprise(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'enterpriseView', '', '', true ) ); // 防止恶意打开
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 以生效时间降序排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$epmap = array(
				'is_del' => 0
		);
	
		$eptbl = M ( 'enterprise' );	
		$total = $eptbl->where ( $epmap )->count ();
		$eplist = array ();
	
		if ($total) {
			$eplist = $eptbl->where ( $epmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $eplist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 搜索商家信息
	 */
	public function searchEnterSev(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/SubbranchAuthorize/enterpriseContinue' ) );
		$searchcondition = I('searchcondition');
		$searchcontent = I('searchcontent');
		if($searchcondition=='enter_account'){
			$esevinfomap = array(
					'account' => array('like','%'.$searchcontent.'%')
			);
		}else{
			$esevinfomap = array(
					'e_name' => array('like','%'.$searchcontent.'%')
			);
		}
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'service_start_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$estable = M('einfo_manage');
		$total = $estable->where($esevinfomap)->count();
		$asinfolist = array ();
	
		if($total){
			$esinfolist = $estable->where($esevinfomap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$asnum = count($esinfolist);
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $esinfolist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	
	}
	
	/**
	 * 特别注意，现在做的是统一续期，没区分单个服务的续期，所有服务的end_time=单个服务的end_time=企业的end_time
	 * 给账号续期的函数(传入e_id,续期时间)
	 * @param string $e_id 商家信息
	 * @param int $month 需要续期的时间
	 * @return void
	 */
	public function putOffEnterpriseService() {
		//p(I());die;
		$e_id = I('e_id');
		$month = I('continuetime');
		$continueorminus = I('continueOrMinus');
		// 初始化事务失败返回的ajax结果
		$this->ajaxresult ['errCode'] = 10001;
		$this->ajaxresult ['errMsg'] = "网络繁忙，无法进行续期操作，请稍后重试！";
	
		// 1、针对e_id的合法性进行校验
		$emap = array(
				'e_id'=>$e_id,
				'is_del'=>0
		);
		$eFindResult = M('enterprise')->where($emap)->find();
		if( !$eFindResult) {
			$this->$ajaxresult['errCode'] = 10002;
			$this->$ajaxresult['errMsg'] = "该商户不存在,请检查后重试";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 2、对用户账号进行续期操作
		// 1) 首先从t_enterprise表中得出service_end_time(所有子服务的最后时间也一并设置为一样)
		$service_end_time = $eFindResult['service_end_time'];
		$enpriTable = M('enterprise');
		$enpriSerTable = M('enterpriseservice');
		$enpriTable->startTrans();
		// 2) 首先对t_enterprise表中的service_end_time进行相应的修改
		$epMap = array(
				'e_id'=>$e_id,
				'is_del'=>0
		);
		if($continueorminus==1){
			$epData['service_end_time'] = date('Y-m-d H:i:s',strtotime("$service_end_time + $month month"));
		}else{
			$epData['service_end_time'] = date('Y-m-d H:i:s',strtotime("$service_end_time - $month month"));
		}
		
		$epResult = $enpriTable->where($epMap)->save($epData);
		//p($epResult);
		if( !$epResult) {	// 如果该步保存失败,则回滚
			$enpriTable->rollback ();
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 3) 再对t_enterpriseservice表中的end_date进行相关的修改
	
		$epSerFindResult = $enpriSerTable->where($epMap)->find();
		if( $epSerFindResult) {	// 如果该表中确实有服务信息存在，那么做相应的更新,否则直接就做事务成功返回处理
			if($continueorminus==1){
				$epSerData['end_date'] = date('Y-m-d',strtotime("$service_end_time + $month month"));
			}else{
				$epSerData['end_date'] = date('Y-m-d',strtotime("$service_end_time - $month month"));
			}
			$epSerResult = $enpriSerTable->where($epMap)->save($epSerData);
			if( !$epSerResult) {	// 如果该步保存失败,则回滚
				$enpriTable->rollback ();
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		// 事务成功，提交
		$enpriTable->commit();
		//p();die();
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	public function addEnterpriseInfo(){
		$this->display();
	}
	
	public function addAuthConfirm(){
		//接收前台数据
		$eid = I('eid');//企业编号，以下多处用到
		//实例化相关表
		$eptable = M('enterprise');
		$eitable = M('enterpriseinfo');
		$estable = M ('enterpriseservice');
		
		$ajaxresult = array (); 									// 要返回的ajax信息
		
		//判断登录账号是否已注册
		$account = I('eac');
		$accmap = array(
				'account' => $account,
				'is_del' => 0
		);
		$accinfo = $eptable->where($accmap)->select();
		$accnum = count($accinfo);
		if ($accnum){
			$ajaxresult = array (
					'errCode' => 10001,
					'errMsg' => '该账号已存在，请重新添加!'
			);
			$this->ajaxReturn ( $ajaxresult );
		}
		
		$eptable->startTrans(); // 开始事务过程，添加一个商家信息一共有三个插入步骤
		//事务过程1：往enterprise表插入编号、账号、密码等信息
		$epinfo = array(
				'e_id' => $eid,
				'account' => I('eac'),
				'password' => md5(I('epa')),
				'service_version' => 0,
				'add_time' => time()
		);
		$starttime = todaystart();
		$endtime = mktime ( 23, 59, 59, date ( 'm' )+1, date ( 'd' )-1, date ( 'Y' ) );
		
		$epinfo['service_start_time'] = timetodate( $starttime );
		$epinfo['service_end_time'] = timetodate( $endtime );
		$epresult = $eptable->add($epinfo);
		
		//事务过程2：往enterpriseinfo表插入编号信息
		$eiinfo = array(
				'e_info_id' => md5 ( uniqid( rand (), true ) ),
				'e_id' => $eid,
				'e_name' => I ( 'ena' ), // 填写企业名称方便管理
				'authorize_open' => 0,
				'login_style' => 0
		);
		$eiresult = $eitable->add($eiinfo);
	
		//事务过程3：往enterpriseservice表插入服务信息
		$smap = array(
				'is_del' => 0
		);
		$servicelist = M('standardservice')->where($smap)->field('servicenav_id')->select();
		
		$esdata = array();
		for($i = 0; $i < count ( $servicelist ); $i ++) {
			$esdata [$i] = array(
					'service_id' => md5 ( uniqid( rand (), true ) ),
					'e_id' => $eid,
					'servicenav_id' => $servicelist [$i]['servicenav_id'],
					'start_date' => $epinfo['service_start_time'],
					'end_date' => $epinfo['service_end_time'],
					'add_time' => time()
			);
		}
		$esresult = $estable->addAll ( $esdata ); 
		
		if ($epresult && $eiresult && $esresult){
			$eptable->commit(); // 提交添加服务的事务
			$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => '添加成功!'
			);
		} else {
			$eptable->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	public function delEnterprise(){
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'enterpriseView', '', '', true ) ); // 防止恶意打开
		$delidlist = I ( 'rowdata' );
		
		$delmap = array (
				'e_id' => array ( 'in' , $delidlist ),
				'is_del' => 0
		); 
		$eresult = M('enterprise')->where ( $delmap )->setField ( 'is_del', 1 );
		$eiresult = M('enterpriseinfo')->where ( $delmap )->setField ( 'is_del', 1 );
		$esresult = M('enterpriseservice')->where ( $delmap )->setField ( 'is_del', 1 );
		
		$ajaxinfo = array ();
		if ($eresult && $eiresult && $esresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "删除信息失败"
			);
		}
		$this->ajaxReturn( $ajaxinfo ); // 将结果返回给前台
	}
	
	/**
	 * 导出企业申请者信息。
	 */
	public function exportapplicant() {
		
		$applymap = array (
				'is_del' => 0
		);
		$field = "company_name, industry, location, brand, name, cellphone, email, 
				wechat_account, qq_number, wechat_public, appid, appsecret, original_id,
				plan_time,self_property,recognize_way,focus_question,your_expiration,add_time,is_authorized"; // 字段
		$applylist = M ( 'serviceapply' )->where ( $applymap )->field ( $field )->select (); // 得到所有申请者信息
		
		// 格式化查询出的数据
		for ($i =0; $i < count ( $applylist ); $i ++) {
			if ($applylist [$i] ['is_authorized'] == 0) {
				$applylist [$i] ['is_authorized'] = "未审批";
			} else {
				$applylist [$i] ['is_authorized'] = "已审批";
			}
			$applylist [$i] ['add_time'] = timetodate ( $applylist [$i] ['add_time'] );
		}
		
		// 准备标题准备打印
		$title = array (
				0 => '公司名称',
				1 => '所属行业',
				2 => '所在地区',
				3 => '品牌名称',
				4 => '姓名',
				5 => '手机号',
				6 => '邮箱',
				7 => '微信号',
				8 => 'QQ号',
				9 => '微信公众号',
				10 => 'AppID',
				11 => 'AppSecret',
				12 => '公众号原始id',
				13 => '计划使用时间',
				14 => '企业性质',
				15 => '了解渠道',
				16 => '关心方向',
				17 => '期待感想',
				18 => '申请时间',
				19 => '是否审批'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $applylist, '申请者详情'.time(), '所有申请者信息一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
}
?>