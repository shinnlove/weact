<?php
/**
 * 本控制器处理后台导购信息管理
 * @author 胡福玲
 * CreateTime 2015/02/28
 */
class GuideManageAction extends PCViewLoginAction {
	
	/**
	 * 所有导购信息列表一览
	 */
	public function guideView() {
		// 分类查找导购所属店铺时读取分类
		$allsubmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$this->sublist = M ( 'shopguide_subbranch' )->where( $allsubmap )->Distinct (true)->field ( 'subbranch_name' )->select (); // 从这个视图中查询，可以找到存在导购的店铺名字
		$this->display ();
	}
	
	/**
	 * 新增导购视图页面。
	 */
	public function addGuideInfo(){
		// 将新增导购的主键编号推送到前台
		$this->gid = date ( 'YmdHms' ) . randCode ( 4, 1 );			// 预先生成可能要新增的导购主键
		$submap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$subbranchlist = M ( 'subbranch' )->where ( $submap )->select (); // 查询当前商家所有店铺
		$this->sinfo = $subbranchlist;
		$this->display ();
	}
	
	/**
	 * 编辑导购视图页面。
	 */
	public function editGuideInfo(){
		$guide_id = I ( 'gid' ); // 接收导购编号
		if (empty ( $guide_id )) {
			$this->error ( "导购编号参数错误！" );
		}
	
		$egmap = array (
				'guide_id' => $guide_id,
				'is_del' => 0
		);
		$sgview = M ( 'shopguide_subbranch' );
		$girst = $sgview->where ( $egmap )->find ();
		$girst ['headimg'] = assemblepath ( $girst ['headimg'] );
		if ($girst ['birthday'] == '0000-00-00') {
			$girst ['birthday'] = '';
		}
		$this->ginfo = $girst;
	
		// 再查询当前商家的所有店铺
		$submap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' =>0
		);
		$subinfo = M ( 'subbranch' )->where ( $submap )->select ();
		$this->sinfo = $subinfo;
	
		$this->display();
	}
	
	/**
	 * 导出导购数据处理函数
	 */
	public function exportGuide(){
		//查询数据
		$exmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$field ="guide_number, account, guide_name, nickname, sex, id_card, birthday, guide_level, guide_type, cellphone, add_time, subbranch_name" ;
		$guidelist = M( 'shopguide_subbranch' )->where( $exmap )->field( $field )->select();
		//格式化查询出的数据
		for ($i =0; $i < count ( $guidelist ); $i ++) {
			if ($guidelist[$i]['guide_level'] == '0'){
				$guidelist[$i]['guide_level'] = '普通';
			}else if ($guidelist[$i]['guide_level'] == '1'){
				$guidelist[$i]['guide_level'] = '中级';
			}else if ($guidelist[$i]['guide_level'] == '2'){
				$guidelist[$i]['guide_level'] = '高级';
			}
	
			if ($guidelist[$i]['guide_type'] == '0'){
				$guidelist[$i]['guide_type'] = '普通导购';
			}else if ($guidelist[$i]['guide_type'] == '1'){
				$guidelist[$i]['guide_type'] = '大堂经理';
			}
	
			if ($guidelist[$i]['sex'] == '1'){
				$guidelist[$i]['sex'] = '男';
			}else if ($guidelist[$i]['sex'] == '2'){
				$guidelist[$i]['sex'] = '女';
			}
	
			$guidelist[$i]['add_time'] = timetodate( $guidelist[$i]['add_time'] );
		}
		// 准备标题准备打印
		$title = array (
				0 => '导购工号',
				1 => '登录帐号',
				2 => '姓名',
				3 => '昵称',
				4 => '性别',
				5 => '身份证号',
				6 => '出生年月',
				7 => '导购等级',
				8 => '导购类别',
				9 => '联系号码',
				10 => '成为导购时间',
				11 => '所属店铺'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $guidelist, '导购详情'.time(), '所有导购信息一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
	
	public function exportGuideCus(){
		//查询数据
		$gcusmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$field ="subbranch_name, manager, manager_mobile, guide_number, guide_name, cellphone, guide_level, star_level, openid, nickname, guide_remarkname, customer_name, sex, contact_number, email, birthday, customer_address, register_time, original_membercard, member_level, inviter, city, province, country, subscribe_time, latest_active, choose_time, remark" ;
		$gcuslist = M( 'guide_customer_info' )->where( $gcusmap )->field( $field )->order('subscribe_time desc')->select();
		//p($gcuslist);die;
		//格式化查询出的数据
		for ($i =0; $i < count ( $gcuslist ); $i ++) {
			//粉丝性别格式化
			$cussex = $gcuslist[$i]['sex'];
			if($cussex == 1){
				$gcuslist[$i]['sex'] = '男';
			}else if($guidelevel == 2){
				$gcuslist[$i]['sex'] = '女';
			}else{
				$gcuslist[$i]['sex'] = '未知';
			}
				
			//导购等级格式化
			$guidelevel = $gcuslist[$i]['guide_level'];
			if($guidelevel == 0){
				$gcuslist[$i]['guide_level'] = '普通';
			}else if($guidelevel == 1){
				$gcuslist[$i]['guide_level'] = '中级';
			}else if($guidelevel == 2){
				$gcuslist[$i]['guide_level'] = '高级';
			}else{
				$gcuslist[$i]['guide_level'] = '未知';
			}
				
			//会员等级格式化
				
			//时间显示格式化
			$gcuslist[$i]['register_time'] = timetodate( $gcuslist[$i]['register_time'] );
			$gcuslist[$i]['subscribe_time'] = timetodate( $gcuslist[$i]['subscribe_time'] );
			$gcuslist[$i]['choose_time'] = timetodate( $gcuslist[$i]['choose_time'] );
				
			$latime = $gcuslist[$i]['latest_active'];
			if($latime == -1){
				$gcuslist[$i]['latest_active'] = '不活跃';
			}else{
				$gcuslist[$i]['latest_active'] = timetodate( $gcuslist[$i]['latest_active'] );
			}
		}
		// 准备标题准备打印
		$title = array (
				0 => '所属分店',
				1 => '店铺负责人',
				2 => '负责人电话',
				3 => '导购编号',
				4 => '导购姓名',
				5 => '导购手机',
				6 => '导购等级',
				7 => '导购星级',
				8 => '粉丝openid',
				9 => '粉丝昵称',
				10 => '导购备注名',
				11 => '粉丝姓名',
				12 => '性别',
				13 => '联系电话',
				14 => '邮箱',
				15 => '生日',
				16 => '所在地区',
				17 => '注册时间',
				18 => '原始会员卡',
				19 => '会员等级',
				20 => '邀请码',
				21 => '城市',
				22 => '省份',
				23 => '国家',
				24 => '关注时间',
				25 => '最近活跃时间',
				26 => '选择导购时间',
				27 => '备注'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $gcuslist, '所有粉丝' . time (), '所有粉丝信息一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
}
?>