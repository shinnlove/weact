<?php
/**
 * 本类继承自template.php的SimpleInfoTemplate，
 * 处理各种信息模板的多态显示。
 */
include_once ("template.php"); // 加载模板基类

/**
 * 图文并茂的信息详情模板。
 * 最初的信息模板。
 */
class GraphicInfoTpl extends SimpleInfoTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入信息详情的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->simpleinfo = $this->readSimpleInfo ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->topnav,
				'simpleinfo' => $this->simpleinfo, 
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
	
}

/**
 * 首图文字的信息详情模板。
 * 第二个信息模板。
 * 该模板首部一张图，下边是纯文字介绍。
 */
class BannerInfoTpl extends SimpleInfoTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入信息详情的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->simpleinfo = $this->readSimpleInfo ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->infoimagelist = $this->extractHtmlImage ( $this->simpleinfo );
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 该模板底部有导航
				'simpleinfo' => $this->simpleinfo,
				'shareinfo' => $this->shareinfo, // 分享信息
				'infobanner' => $this->infoimagelist [0], // 信息模板的首图
				'infotext' => $this->extractPureHtmlText ( $this->simpleinfo ) // 信息模板的纯文字
		);
	}
	
}

/**
 * 纯图片的信息详情模板。
 * 第三个信息模板。
 */
class PictureInfoTpl extends SimpleInfoTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入信息详情的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->simpleinfo = $this->readSimpleInfo ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 该模板底部有导航
				'simpleinfo' => $this->simpleinfo,
				'shareinfo' => $this->shareinfo, // 分享信息
				'imageInfoList' => $this->extractHtmlImage ( $this->simpleinfo ) // 抽取html中的图片信息
		);
	}
	
}
?>