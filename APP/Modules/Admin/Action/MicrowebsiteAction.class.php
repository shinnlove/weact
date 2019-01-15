<?php
/**
 * 此控制器处理微官网的各种设置。
 * 包括：
 * 1、官网首页模板；
 * 2、幻灯片设定；
 * 3、导航文字样式。
 * @author 骆泽刚、赵臣升。
 * 修改日期：2014/09/06 16:25:25.
 * 该控制器对图片的上传、路径的组装都做的非常好。
 */
class MicrowebsiteAction extends PCViewLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则会出错）。
	 * DefineTime:2014/09/18 21:57:25.
	 * @var string variable DBfield
	 */
	var $table_name = 'slider';
	var $cc_slider_id = 'slider_id';
	var $cc_e_id = 'e_id';
	var $cc_image_path = 'image_path';
	var $cc_target_url = 'target_url';
	var $cc_create_time = 'create_time';
	var $cc_latest_modify = 'latest_modify';
	var $cc_is_del = 'is_del';
	
	/**
	 * 官网首页模板页面。
	 */
	public function indexTemplate() {
		$tplinfo = array (
				'tplpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ), // tplpath字段代表是读取模板
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id']
		);
		$tplManage = A ( 'Admin/TplManage' );
		$this->tplresult = $tplManage->EntrustTemplate ( $tplinfo );
		$this->display();
	}
	
	/**
	 * 幻灯片一览页面。
	 */
	public function slider(){
		$this->display();
	}
	
	/**
	 * 显示添加幻灯片页面。
	 */
	public function addSlider(){
		$tplinfo = array(
				'tplpath' => 'admin_microwebsite_indextemplate',		//要查询的是当前主页模板，路径设置为admin_microwebsite_indextemplate
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id']
		);
		$tplManage = A ( 'Admin/TplManage' );
		$tplresult = $tplManage->InitTemplate ( $tplinfo );
		for($i = 0; $i < count ( $tplresult ); $i ++) {
			if ($tplresult [$i] ['selected'] == 1) {
				$this->selectedTpl = $tplresult[$i]; // 推送选中的模板类型
				break;
			}
		}
		$this->display();
	}
	
	/**
	 * 显示编辑幻灯片页面。
	 */
	public function editSlider(){
		$data = array(
				$this->cc_slider_id => I('sid')
		);
		$sdtable = M ( $this->table_name );
		$sdinfo = $sdtable->where ( $data )->find ();					//找到这条记录
		$sdinfo [$this->cc_image_path] = assemblepath ( $sdinfo [$this->cc_image_path] );//处理一下图片路径
		$this->sdinfo = $sdinfo;									//推送到前台
	
		$tplinfo = array(
				'tplpath' => 'admin_microwebsite_indextemplate',		//要查询的是当前主页模板，路径设置为admin_microwebsite_indextemplate
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id']
		);
		$tplManage = A ( 'Admin/TplManage' );
		$tplresult = $tplManage->InitTemplate ( $tplinfo );
		for($i = 0; $i < count ( $tplresult ); $i ++){
			if ($tplresult [$i] ['selected'] == 1){
				$this->selectedTpl = $tplresult [$i]; // 将选中的模板信息推到前端作为提示
				break;
			}
		}
		$this->display();
	}
	
	/**
	 * -------------------------------↑以上为首页幻灯片的页面、请求与处理函数↑------------------------------------
	 * */
	
	/**
	 * 导航文字样式页面（暂时不需要做这个功能）。
	 */
	public function navigatorFontStyle(){
		$this->display();
	}
	
}
?>