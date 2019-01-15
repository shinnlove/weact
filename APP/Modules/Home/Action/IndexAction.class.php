<?php
/**
 * 本类主页管理客户访问首页的时候进行的页面跳转和主页显示。
 * @author 王健、赵臣升。
 * 本控制器已经完成对图片路径的组装。
 * 特别注意：父类控制器里已经查询过einfo，所以企业信息保存在$this->einfo，子类继承后可以访问。
 * 优化时间：2014/11/13 04:03:25.
 * //'e_id' =>'201403231018300001'泽然服饰的e_id（项目制作之初，王健的第一句注释，保留至今。）
 * 特别注意：主页控制器已经全部改用einfo来表示企业信息，原enterprise已经废弃不用，2014/11/17 20:10:50。
 */
class IndexAction extends MobileGuestAction {
	public function index() {
		$tpl_indexpath = strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ); // strtolowerPHP自带函数，转为小写,ThinkPHP自带功能，自动获取“当前分组/控制器名/Action函数名”
		$mobilecommon = A ( 'Home/MobileCommon' ); // 实例化Home分组下，名为MobileCommon的控制器，创建其对象$mobilecommon
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] );
		$this->pageinfo = $mobilecommon->selectTpl ( $navinfo, $tpl_indexpath ); // 多态查找模板
		unset ( $mobilecommon ); // 注销此对象释放内存
		$this->display ( $this->pageinfo ['template_realpath'] );
	}
}
?>