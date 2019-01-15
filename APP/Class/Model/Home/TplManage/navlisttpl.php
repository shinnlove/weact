<?php
/**
 * 本类继承自template.php的SimpleInfoTemplate，
 * 处理各种信息模板的多态显示。
 */
include_once ("template.php"); // 加载模板基类

/**
 * 列表式信息二级导航模板。
 * 最初的信息二级导航模板。
 */
class InfoNavListTpl extends NavListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为信息类别导航1）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（信息）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级信息导航
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // 当前的顶级导航（顶部或底部显示）
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'secondnavigation' => $this->secondnav,
				'navigation' => $this->topnav,
				'shareinfo' => $this->shareinfo, 
		);
	}
	
}

/**
 * 图片标题式信息二级导航模板。
 * 第二个信息二级导航模板。
 */
class BannerNavListTpl extends NavListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为信息类别导航1）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（信息）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级信息导航
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // 当前的顶级导航（顶部或底部显示）
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'secondnavigation' => $this->secondnav,
				'navigation' => $this->topnav,
				'shareinfo' => $this->shareinfo, 
		);
	}
	
}
?>