<?php
/**
 * 本类继承自template.php的IndexTemplate，
 * 处理各种主页模板的多态显示。
 */
include_once ("template.php"); // 加载模板基类

/**
 * 九宫格幻灯片主页模板类。
 * 最早的主页模板类。
 */
class NineIndexTpl extends IndexTemplate {
	
	/**
	 * 构造函数，传入4个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 */
	function __construct($navinfo = NULL) {
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // Step1：获取主页菜单
		$this->sliders = $this->indexSlider ( $navinfo ); // Step2：获取主页幻灯片
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // Step3：生成分享信息
		$this->initdata = array(
				'navigation' => $this->topnav,
				'sliders' => $this->sliders,
				'shareinfo' => $this->shareinfo,
		);
	}
}

/**
 * 九宫格国际版主页模板类。
 * 第二个主页模板类。
 */
class NineLuxuryIndexTpl extends IndexTemplate {
	
	/**
	 * 构造函数，传入4个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 */
	function __construct($navinfo = NULL) {
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); // Step1：获取主页菜单
		$this->sliders =  $this->indexSlider ( $navinfo ); // Step2：获取主页幻灯片
		$this->randomSlider ( $this->sliders ); // Step3：随机抽取一张幻灯片作为背景图
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // Step4：生成分享信息
		$this->initdata = array(
				'navigation' => $this->topnav,
				'sliders' => $this->sliders,
				'shareinfo' => $this->shareinfo,
		);
	}
	
	/**
	 * 从幻灯片中随机一张作为背景幻灯片（这个模板只能显示一张图）。
	 * @param array $sliderinfo 查询出的幻灯片数组（二维数组）
	 */
	public function randomSlider($sliderinfo = NULL) {
		$this->sliders [0] = $sliderinfo [rand ( 0, count ( $sliderinfo ) - 1 )]; // 特别注意：随机推送一张主页图片
	}
}

/**
 * 四格版主页模板类。
 * 第三个主页模板类。
 */
class FourIndexTpl extends IndexTemplate {
	
	/**
	 * 构造函数，传入4个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 */
	function __construct($navinfo = NULL) {
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); 				// Step1：获取主页菜单
		$this->sliders =  $this->indexSlider ( $navinfo ); 					// Step2：获取主页幻灯片
		$this->randomSlider ( $this->sliders ); 							// Step3：随机抽取一张幻灯片作为背景图
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); 			// Step4：生成分享信息
		// 下面对四格奢华版主页顶部导航的居中显示进行额外处理：修改日期：2015/05/25 00:58:00
		$handlenav = $this->divideTopNav ( $this->topnav ); 				// 区分一下导航
		$toplimitnav = array (); // 四格奢华版主页顶部导航只能有4个
		$limitcount = 0;
		foreach($handlenav ['center'] as $singcenternav) {
			array_push ( $toplimitnav, $singcenternav );
			$limitcount += 1;
			if ($limitcount >= 4) {
				break; // 只允许4个中间导航
			}
		}
		$handlenav ['center'] = $toplimitnav;
		$this->initdata = array(
				'navigation' => $handlenav, // 处理后的导航
				'sliders' => $this->sliders,
				'shareinfo' => $this->shareinfo,
		);
	}
	
	/**
	 * 从幻灯片中随机一张作为背景幻灯片（这个模板只能显示一张图）。
	 * @param array $sliderinfo 查询出的幻灯片数组（二维数组）
	 */
	public function randomSlider($sliderinfo = NULL) {
		$this->sliders [0] = $sliderinfo [rand ( 0, count ( $sliderinfo ) - 1 )]; // 特别注意：随机推送一张主页图片
	}
	
}

/**
 * 四格奢华版主页模板类。
 * 第四个主页模板类。
 */
class FourLuxuryIndexTpl extends IndexTemplate {
	
	/**
	 * 构造函数，传入4个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 */
	function __construct($navinfo = NULL) {
		$this->topnav = $this->extractTopNavInfo ( $navinfo ); 				// Step1：获取主页菜单
		$this->sliders =  $this->indexSlider ( $navinfo ); 					// Step2：获取主页幻灯片
		$this->randomSlider ( $this->sliders ); 							// Step3：随机抽取一张幻灯片作为背景图
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); 			// Step4：生成分享信息
		// 下面对四格奢华版主页顶部导航的居中显示进行额外处理：修改日期：2015/05/25 00:58:00
		$handlenav = $this->divideTopNav ( $this->topnav ); 				// 区分一下导航
		$toplimitnav = array (); // 四格奢华版主页顶部导航只能有4个
		$limitcount = 0;
		foreach($handlenav ['center'] as $singcenternav) {
			array_push ( $toplimitnav, $singcenternav );
			$limitcount += 1;
			if ($limitcount >= 4) {
				break; // 只允许4个中间导航
			}
		}
		$handlenav ['center'] = $toplimitnav;
		$this->initdata = array(
				'navigation' => $handlenav, // 处理后的导航
				'sliders' => $this->sliders,
				'shareinfo' => $this->shareinfo,
		);
	}
	
	/**
	 * 从幻灯片中随机一张作为背景幻灯片（这个模板只能显示一张图）。
	 * @param array $sliderinfo 查询出的幻灯片数组（二维数组）
	 */
	public function randomSlider($sliderinfo = NULL) {
		$this->sliders [0] = $sliderinfo [rand ( 0, count ( $sliderinfo ) - 1 )]; // 特别注意：随机推送一张主页图片
	}
	
}
?>