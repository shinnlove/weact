<?php
/**
 * 此控制器处理微商城的各种设置。
 * 包括：
 * 1、商城首页模板；
 * 2、产品陈列样式。
 * @author 赵臣升
 *
 */
class MicroshopRequestAction extends PCRequestLoginAction {
	/**
	 * 设置微商城主页。
	 */
	public function setShopIndex(){
		$setinfo = array(
				'setpath' => strtolower(GROUP_NAME.'_'.MODULE_NAME.'_'.ACTION_NAME),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'template_id' => I('selected')
		);
		$tplManage = A('Admin/TplManage');
		$rs = $tplManage->EntrustTemplate($setinfo);
		if($rs == 1){
			$this->ajaxReturn( array('status' => 1, 'msg' => '提交更改成功!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '模板更改失败!'.$rs['msg']) );
		}
	}
	
	/**
	 * 设置商品陈列模板
	 */
	public function setProductList(){
		$setinfo = array(
				'setpath' => strtolower(GROUP_NAME.'_'.MODULE_NAME.'_'.ACTION_NAME),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'template_id' => I('selected')
		);
		$TplManage = A('Admin/TplManage');
		$rs = $TplManage->EntrustTemplate($setinfo);
		if($rs == 1){
			$this->ajaxReturn( array('status' => 1, 'msg' => '提交更改成功!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '模板更改失败!'.$rs['msg']) );
		}
	}
	
}
?>