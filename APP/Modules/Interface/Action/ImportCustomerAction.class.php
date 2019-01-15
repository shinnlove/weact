<?php
/**
 * 顾客个人信息输入接口(性别、出生日期、手机号码、尺码/体型、个人喜好、详细备注)
 * @author 胡睿
 * CreateTime:2015/09/04 18:25:25
 */
class ImportCustomerAction extends PostInterfaceAction {
	
	/**
	 * =======以下区域为修改顾客信息接口=========
	 * 包含：1、性别；2、出生日期；3、手机号码；4、尺码/体型；5、个人喜好；6、详细备注。
	 * 因为每次都是异步保存，所以采用分接口方式。
	 */
	
	/**
	 * 修改信息接口一、修改顾客性别接口(表t_wechatuserinfo中的sex字段修改,字段值仅为1和2)
	 */
	public function modifyCustomerSex() {
		$modifyfield = "sex"; // 接口字段
		$dbfield = "sex"; // 数据库字段
		$sex = intval ( $this->params [$modifyfield] ); // 接收到的性别
		
		$modifytable = "wechatuserinfo";
		// 接收到的性别更改字段为空
		if (empty ( $sex )) {
			$this->ajaxresult ['errCode'] = 49305;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客性别参数，性别参数也不能为0！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if ($sex != 1 && $sex != 2) {
			$this->ajaxresult ['errCode'] = 49306;
			$this->ajaxresult ['errMsg'] = '接口参数错误，性别参数只能为1（男）或2（女）！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable, $modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口二、修改顾客出生日期接口(表t_customerinfo中的birthday字段修改)
	 */
	public function modifyCustomerBirthday() {
		$modifyfield = "birth"; // 接口字段
		$dbfield = "birthday"; // 数据库字段
	
		$modifytable = "customerinfo";
		// 接收到的姓名更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 49309;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客生日参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable,$modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口三、修改顾客手机号码接口(表t_customerinfo中的contact_number字段修改)
	 */
	public function modifyCustomerCellphone() {
		$modifyfield = "contactnum"; // 接口字段
		$dbfield = "contact_number"; // 数据库字段

		$modifytable = "customerinfo";
		// 接收到的姓名更改字段为空
		if (empty ( $this->params [$modifyfield] )) {
			$this->ajaxresult ['errCode'] = 49310;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客手机号码参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable,$modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口四、修改顾客尺码/体型接口(表t_customerfittingfile中的size字段修改)
	 */
	public function modifyCustomerSize() {
		$modifyfield = "size"; // 接口字段
		$dbfield = "size"; // 数据库字段
		$modifytable = "customerfittingfile";
		
		$size = $this->params [$modifyfield]; // 获取顾客的尺码体型
		// 接收到的尺码体型字段为空
		if (empty ( $size )) {
			$this->ajaxresult ['errCode'] = 49311;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客尺码/体型参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (strlen ( $size ) > 45) {
			$this->ajaxresult ['errCode'] = 49312;
			$this->ajaxresult ['errMsg'] = '接口参数错误，顾客尺码/体型参数长度不能超过45！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable,$modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口五、修改顾客个人穿衣喜好接口(表t_customerfittingfile中的wear_prefer字段修改)
	 */
	public function modifyCustomerWearPrefer() {
		$modifyfield = "wearprefer"; // 接口字段
		$dbfield = "wear_prefer"; // 数据库字段
		$modifytable = "customerfittingfile";
	
		$prefer = $this->params [$modifyfield]; // 获取顾客的尺码体型
		// 接收到的个人穿衣喜好字段为空
		if (empty ( $prefer )) {
			$this->ajaxresult ['errCode'] = 49313;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客个人喜好参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (strlen ( $prefer ) > 255) {
			$this->ajaxresult ['errCode'] = 49314;
			$this->ajaxresult ['errMsg'] = '接口参数错误，顾客个人喜好参数长度不能超过255！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable,$modifyfield, $dbfield );
		}
	}
	
	/**
	 * 修改信息接口六、修改顾客详细备注接口(表t_customerfittingfile中的detail_remark字段修改)
	 */
	public function modifyCustomerDetailRemark() {
		$modifyfield = "detailremark"; // 接口字段
		$dbfield = "detail_remark"; // 数据库字段
		$modifytable = "customerfittingfile";
	
		$detailremark = $this->params [$modifyfield]; // 获取顾客的详细备注
		// 接收到的详细备注字段为空
		if (empty ( $detailremark )) {
			$this->ajaxresult ['errCode'] = 49315;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客详细备注参数！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else if (strlen ( $detailremark ) > 255) {
			$this->ajaxresult ['errCode'] = 49316;
			$this->ajaxresult ['errMsg'] = '接口参数错误，顾客详细备注参数长度不能超过100！';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$this->modifyCusInfo ( $modifytable,$modifyfield, $dbfield );
		}
	}
	
	/**
	 * 私有被调函数，公共的修改导购信息接口，被以上6个函数调用，需指明要更新的字段
	 * @param string $modifytable 数据库表名
	 * @param string $interfacefield 接口字段
	 * @param string $localdbfield 本地数据库字段
	 */
	private function modifyCusInfo( $modifytable = '', $interfacefield = '', $localdbfield = '') {
		// 接收到的post数据中gid如果为空
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 判断导购是否真实存在
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$guideinfo = M("shopguide")->where ( $guidemap )->find (); // 查找当前要修改信息的导购
		if(!$guideinfo){
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中cid如果为空
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 46106;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 判断顾客是否真实存在
		$cusmap = array (
				'customer_id' => $this->params ['cid'],
				'is_del' => 0
		);
		$cusinfo = M("customerinfo")->where ( $cusmap )->find (); // 查找当前要修改信息的导购
		if(!$cusinfo){
			$this->ajaxresult ['errCode'] = 46107;
			$this->ajaxresult ['errMsg'] = '不存在该顾客！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 判断当前顾客是否仍然在该导购名下
		$cusguidemap = array (
			'customer_id'=>$this->params ['cid'],
			'guide_id'=>$this->params ['gid'],
			'is_del'=>0	
		);
		$cusguideResult = M("customerguide")->where($cusguidemap)->find();
		if(!$cusguideResult) {
			$this->ajaxresult ['errCode'] = 46314;
			$this->ajaxresult ['errMsg'] = '该顾客已经选换其他导购,无法修改其信息！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 针对不同的表结构，进行相应的数据修改
		$modifymap = array(
			'customer_id' => $this->params ['cid'],
			'is_del' => 0
		);
		$modifyinfo[$localdbfield] = $this->params [$interfacefield]; // 要修改的信息
		$isdatasave = true;		// 状态标志位（是新增数据还是修改数据）
		
		if( $modifytable == "wechatuserinfo"){	// 修改wechatuserinfo中的sex字段
			$modifymap = array(
				'openid'=>$cusinfo['openid'],
				'is_del'=>0		
			);
		}
		else if( $modifytable == "customerfittingfile")	{
			$modifyinfo['modify_time'] = time();
			// 首先查找数据库中有无相关记录
			$findResult = M("customerfittingfile")->where($modifymap)->find();
			if(!$findResult) {	// 如果没找到,则为新增
				$isdatasave = false;	// 说明是新增数据
				// 要修改的字段之前有，此处不用加上
				$modifyinfo['file_id'] = md5 ( uniqid ( rand (), true ) );
				$modifyinfo['customer_id'] = $this->params['cid'];
				$modifyinfo['add_time'] = time();
				$modifyinfo['is_del'] = 0;	
				$saveresult = M("customerfittingfile")->add($modifyinfo);			
			}
		}		
		if($isdatasave)	{	// 如果是修改数据
			$saveresult = M($modifytable)->where ( $modifymap )->save ( $modifyinfo ); // 修改导购信息
		}
		
		if ($saveresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = 'ok';
		} else {
			$this->ajaxresult ['errCode'] = 46313;
			$this->ajaxresult ['errMsg'] = '保存信息失败，请不要重复提交相同数据！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
}
?>