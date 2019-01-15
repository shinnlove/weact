<?php
class CustomerViewAction extends MobileGuestAction {
	/**
	 * 二级菜单页面，已经经过MobileCommon优化。
	 * Author：王健，Optimize：赵臣升。
	 * OptimizeTime:19:53:52.
	 * OptimizeAgain:2014/11/17 19:58:25.
	 * 开启二级信息导航模板选择，OptimizeAgainTime:2014/07/24 03:20:25.
	 */
	public function showNavList() {
		$this->nav_id = I ( 'nav_id' ); // 特别重要：为前台分享推送nav_id
		 
		// Step1：判别二级导航菜单类型
		$currentmap = array (
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => $this->nav_id,
				'is_del' => 0
		);
		$currentnav = M ( 'navigation' )->where ( $currentmap )->find (); // 必须要查一下当前导航，才能判断哪种类型的
		
		// Step2：进行二级导航菜单模板选择并统一赋值，二级信息导航的nav_type是1，微商城的nav_type是2。
		$navinfo = array (
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => $this->nav_id
		);
		$tpl_indexpath = strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ); 					// PHP自带函数，转为小写
		$mobilecommon = A ( 'Home/MobileCommon' ); 																// 实例化Home分组下，名为MobileCommon的控制器，创建其对象$mobilecommon
		$this->pageinfo = $mobilecommon->selectTpl ( $navinfo, $tpl_indexpath, $currentnav ['nav_type'] );
		unset ( $mobilecommon ); // 注销此对象释放内存
		$this->display ( $this->pageinfo ['template_realpath'] );
	}
	
	/**
	 * 信息详情视图。
	 */
	public function showSimpleInfo() {
		// 多态查找信息展示模板并统一赋值
		$navinfo = array (
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => I ( 'nav_id' ) 
		);
		$tpl_indexpath = strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ); 	// PHP自带函数，转为小写，读取当前分组、当前控制器和当前action
		$mobilecommon = A ( 'Home/MobileCommon' ); 												// 实例化Home分组下，名为MobileCommon的控制器，创建其对象$mobilecommon
		$this->pageinfo = $mobilecommon->selectTpl ( $navinfo, $tpl_indexpath );
		unset ( $mobilecommon ); // 注销此对象释放内存
		$this->display ( $this->pageinfo ['template_realpath'] );
	}
	
	public function showEmptyInfo() {
		$data = array (
				'nav_id' => I ( 'nav_id' ),
				'e_id' => $this->einfo ['e_id']
		);
		
		// 顶部导航菜单的名字
		$currentmap = array (
				'e_id' => $data ['e_id'],
				'nav_id' => $data ['nav_id'],
				'is_del' => 0 
		);
		$currentNav = M ( 'navigation' )->where ( $currentmap )->find ();
		$currentNav ['nav_image_path'] = assemblepath ( $currentNav ['nav_image_path'] );
		$this->currentnav = $currentNav; // 推送当前的导航菜单名字
		
		// 详情页面（虽为空）必不可少的查询代码
		$map ['e_id'] = $data ['e_id'];
		$map ['nav_id'] = $data ['nav_id'];
		$map ['is_del'] = '0';
		$simpleinfo = M ( 'simpleinfo' )->where ( $map )->find ();
		$this->simpleinfo = $simpleinfo;
		
		$mobilecommon = A ( 'Home/MobileCommon' ); // 实例化Home分组下的MobileCommon控制器
		$this->pageinfo ['navigation'] = $mobilecommon->rootNavWithURL ( $data );
		$this->display ();
	}
	
}
?>