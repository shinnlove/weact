<?php
/**
 * 本控制器处理后台用户管理这一模块的数据。
 * @author 赵臣升
 * CreateTime：2014/04/10.
 */
class CustomerAction extends PCViewLoginAction {
	
	/**
	 * 新增店铺客户页面。
	 * Author：赵臣升。
	 * CreateTime：2014/10/13 21:49:25.
	 * 特别注意：目前分店采用的是subbranch表，不是enterpriselocation。
	 */
	public function addCustomer() {
		$leveltable = M ( 'memberlevel' ); 						// 定义用户等级表
		$shoptable = M ( 'subbranch' ); 						// 定义企业分店表
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; 			// 当前商家编号
	
		// Step1：将当前商家下所有用户等级查询出来
		$levelmap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$current_level = $leveltable->where ( $levelmap )->order ( 'level asc' )->select ();
		$this->current_level = $current_level;
	
		// Step2：将当前商家下所有企业分店查询出来，并初始化
		$branchmap = array (
				'e_id' => $e_id, 								// 当前商家
				'closed_status' => 0, 							// 正常运营中的店
				'is_del' => 0  									// 没有被删除的店
		);
		$shopall = $shoptable->where ( $branchmap )->select (); // 筛选出所有满足条件的店铺
		$this->shopall = $shopall;
	
		// Step3：将新增顾客的主键编号推送到前台
		$this->cid = date ( 'YmdHms' ) . randCode ( 4, 1 ); 	// 预先生成可能要新增的客户主键
		$this->display ();
	}
	
	/**
	 * 编辑客户信息页面。
	 * Author：赵臣升。
	 * CreateTime：2014/10/14 21:50:25.
	 * 特别注意：目前分店采用的是subbranch表，不是enterpriselocation。
	 */
	public function editCustomer(){
		$leveltable = M ( 'memberlevel' ); 						// 定义用户等级表
		$shoptable = M ( 'subbranch' ); 						// 定义企业分店表
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; 			// 当前商家编号
	
		// Step1：将当前商家下所有用户等级查询出来
		$levelmap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$current_level = $leveltable->where ( $levelmap )->order ( 'level asc' )->select ();
		$this->current_level = $current_level;
	
		// Step2：将当前商家下所有企业分店查询出来，并初始化
		$branchmap = array (
				'e_id' => $e_id, 								// 当前商家
				'closed_status' => 0, 							// 正常运营中的店
				'is_del' => 0  									// 没有被删除的店
		);
		$shopall = $shoptable->where ( $branchmap )->select (); // 筛选出所有满足条件的店铺
		$this->shopall = $shopall;
	
		$editcondition = array(
				'customer_id' => I('cid'),
				'e_id' => $e_id,
				'is_del' => 0
		);
		$citable = M('customerinfo');
		$ciresult = $citable->where($editcondition)->find();
		$ciresult ['register_time'] = timetodate( $ciresult ['register_time'] );
		if($ciresult ['sex']=='男'){
			$ciresult ['sex']=1;
		}else if($ciresult ['sex']=='女'){
			$ciresult ['sex']=2;
		}else {
			$ciresult ['sex']=-1;
		}
		$this->cinfo = $ciresult;
		$this->display();
	}
	
	/**
	 * 显示客户信息页面
	 */
	public function customerView() {
		$this->display ();
	}
	
	/**
	 * 微信线上会员显示页面
	 */
	public function onlineMember() {
		$this->display ();
	}
	
	/**
	 * 实体店线下会员显示页面
	 */
	public function offlineMember() {
		$this->display ();
	}
	
	
}
?>