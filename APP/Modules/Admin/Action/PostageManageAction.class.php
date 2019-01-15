<?php
/**
 * 邮费设定管理相关控制器
 * @author hufuling
 *
 */
class PostageManageAction extends PCViewLoginAction {
	
	/**
	 * 邮费模板视图。
	 */
	public function postageModeView(){
		$this->display();
	}
	
	/**
	 * 新增模板
	 */
	public function addPostageMode(){
		$this->mid = md5(uniqid(rand(),true));
		$this->display();
	}
	
	/**
	 * 编辑模板
	 */
	public function editPostageMode() {
		$editmap = array (
				'mode_id' => $_REQUEST ['mode_id'],
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$editinfo = M ( 'postagemode' )->where ( $editmap )->find ();
	
		$editdetailmap = array (
				'mode_id' => $editmap ['mode_id'],
				'is_del' => 0
		);
		$detailinfo = M ( 'postagedetail' )->where ( $editdetailmap )->select ();
	
		// 对不能勾选地区进行预处理
		$disabledregion = array (); 													// 存放不能勾选的地区数组
		$detailcount = count ( $detailinfo ); 											// 统计已经添加过的邮费地区数量
		for($i = 0; $i < $detailcount; $i ++) {
			if (! empty ($detailinfo [$i] ['designated_area'])) {
				$tempregions = explode ( ",", $detailinfo [$i] ['designated_area'] ); 	// 直接取出当前地区分割进入临时地区列表
				foreach ($tempregions as $singleregion) {
					array_push ( $disabledregion, $singleregion ); 						// 将临时地区数组中的地区一个个列入$disabledregion不能勾选地区数组中
				}
			}
		}
	
		// Step1：推送编辑的主信息
		$this->editinfo = $editinfo;
	
		// Step2：将二维数组以json方式打到页面上用js模板渲染
		$postageinfo ['detaillist'] = $detailinfo; 										// 将地区详情信息包装到detailist字段中
		$ajaxinfo = json_encode ( $postageinfo ); 										// json_encode 成ajaxinfo
		$this->detaillist = str_replace ( '"', '\\"', $ajaxinfo ); 						// 打到页面上对双引号转义下，防止出错
	
		// Step3：推送不能勾选地区数组到前台
		$this->disabledregion = implode ( ",", $disabledregion );
	
		$this->display();
	}
	
	
}
?>