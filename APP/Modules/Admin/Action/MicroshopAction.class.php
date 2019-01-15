<?php
/**
 * 此控制器处理微商城的各种设置。
 * 包括：
 * 1、商城首页模板；
 * 2、产品陈列样式。
 * @author 赵臣升
 *
 */
class MicroshopAction extends PCViewLoginAction {
	/**
	 * 微商城首页。
	 */
	public function shopIndexTemplate(){
		$tplinfo = array(
				'tplpath' => strtolower(GROUP_NAME.'_'.MODULE_NAME.'_'.ACTION_NAME),
				'e_id' => $_SESSION['curEnterprise']['e_id']
		);
		$tplManage = A('Admin/TplManage');
		$this->tplresult = $tplManage->EntrustTemplate($tplinfo);
		$this->display();
	}
	
	/**
	 * 商品陈列模板。
	 */
	public function productListTemplate(){
		$tplinfo = array(
				'tplpath' => strtolower(GROUP_NAME.'_'.MODULE_NAME.'_'.ACTION_NAME),
				'e_id' => $_SESSION['curEnterprise']['e_id']
		);
		$tplManage = A('Admin/TplManage');
		$this->tplresult = $tplManage->EntrustTemplate($tplinfo);
		$this->display();
	}
	
	
}
?>