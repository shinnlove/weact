<?php
/**
 * 本类继承自template.php的template，
 * 处理客户中心页面需要展示顶级导航的页面视图。
 * 特别注意：该类目前暂时不用，除非有不同的模板。
 * CreateTime:2015/02/24 20:52:25.
 */
import ( 'Class.Model.Home.TplManage.template', APP_PATH, '.php' ); // 使用import代替include，include文件中再引文件路径立刻出错
class DeliveryInfoTpl extends MemberHandleTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入信息详情的父级导航编号
	 */
	function __constract() {
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->initdata = array (
				'navigation' => $this->divideTopNav ( $this->topnav ) // 切割导航
		);
	}
	
}

?>