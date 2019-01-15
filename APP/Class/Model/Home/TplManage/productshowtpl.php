<?php
/**
 * 本类继承自template.php的ProductShowTemplate，
 * 处理不同的商品详情展示模板。
 */
//include_once ("template.php"); // 加载模板基类
import ( 'Class.Model.Home.TplManage.template', APP_PATH, '.php' ); // 使用import代替include，include文件中再引文件路径立刻出错

/**
 * 最初的产品展示模板。
 * 该模板在展示商品详情的时候，会同时推荐一些同类产品。
 */
class RecProductShowTpl extends ProductShowTemplate {
	
	/**
	 * 构造函数，传入6个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 * @property string $product_id 商品详情的商品编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		if ($this->CONST_NAV_TYPE != 2 && $this->CONST_NAV_TYPE != 5) $this->recheckNavType ( $navinfo ); // 接上一步，防止出错再重新检查导航类型
		$this->getProductInfo ( $navinfo ); // 获取商品在前
		$this->getSameProductList ( $navinfo ); // 获取同类商品信息
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'productinfo' => $this->productinfo, // 产品信息
				'sameproductlist' => $this->sameproductlist, // 同类商品信息
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
}

/**
 * 天猫淘宝式产品展示模板。
 * 该模板的样式比较简洁。
 */
class TMallProductShowTpl extends ProductShowTemplate {
	
	/**
	 * 构造函数，传入6个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 * @property string $product_id 商品详情的商品编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		if ($this->CONST_NAV_TYPE != 2 && $this->CONST_NAV_TYPE != 5) $this->recheckNavType ( $navinfo ); // 接上一步，防止出错再重新检查导航类型
		$this->getProductInfo ( $navinfo ); // 获取商品在前
		//$this->getSameProductList ( $navinfo ); // 获取同类商品信息
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'productinfo' => $this->productinfo, // 产品信息
				//'sameproductlist' => $this->sameproductlist, // 同类商品信息
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
}
?>