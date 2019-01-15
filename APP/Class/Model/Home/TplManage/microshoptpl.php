<?php
/**
 * 本类继承自template.php的MicroShopTemplate，
 * 处理各种主页模板的多态显示。
 */
include_once ("template.php"); // 加载模板基类

/**
 * 列表式微商城模板类。
 * 最早的微商城模板。
 */
class ListMicroShopTpl extends MicroShopTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为商城类别导航2服饰，5常用商品）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（商品）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级商品导航
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
 * 区块式微商城模板类。
 * 诺奇式微商城模板。
 */
class SquareMicroShopTpl extends MicroShopTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为商城类别导航2服饰，5常用商品）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（商品）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级商品导航
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // 当前的顶级导航（顶部或底部显示）
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'secondnavigation' => $this->secondnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 切割一下顶级导航
				'shareinfo' => $this->shareinfo, 
		);
	}
	
}

/**
 * 横条式微商城模板类。
 * only的微商城模板。
 */
class BannerMicroShopTpl extends MicroShopTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为商城类别导航2服饰，5常用商品）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（商品）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级商品导航
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // 当前的顶级导航（顶部或底部显示）
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'secondnavigation' => $this->secondnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 切割一下顶级导航
				'shareinfo' => $this->shareinfo, 
		);
	}
	
}

/**
 * 时间轴式微商城模板类。
 * 重庆lily式微商城模板。
 */
class TimeMicroShopTpl extends MicroShopTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入的顶级导航编号（该导航为商城类别导航2服饰，5常用商品）
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo ); // 当前进入的顶级（商品）导航
		$this->secondnav = $this->extractSecondNavInfo ( $navinfo ); // 当前二级商品导航
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