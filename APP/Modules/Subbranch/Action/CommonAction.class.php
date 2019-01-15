<?php
/**
 * 分店登录检测控制器
 * @author 赵臣升
 * CreateTime:2014/10/24 15:08:25.
 */
class CommonAction extends Action{
	//_initialize为必须有的
	public function _initialize(){
		if(!isset($_SESSION['curSubbranch'])){
			$this->redirect('Subbranch/Index/index');		//跳转至登录页面Index控制器下的index页面
		}
	}
}
?>