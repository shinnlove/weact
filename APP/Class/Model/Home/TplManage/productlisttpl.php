<?php
/**
 * 本类继承自template.php的ProductListTemplate，
 * 处理各种产品陈列模板的多态显示。
 */
//include_once ("template.php"); // 加载模板基类
import ( 'Class.Model.Home.TplManage.template', APP_PATH, '.php' ); // 使用import代替include，include文件中再引文件路径立刻出错

/**
 * 橱窗式商品陈列模板。
 * 第一个商品陈列模板，左右两列。
 */
class WindowExpoProTpl extends ProductListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->getProduct ( $navinfo ); // 获取商品在前
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->topnav, // 橱窗式商品陈列底部没有导航，只有顶部有下拉菜单
				'productlist' => $this->productlist, // 产品信息
				'listcount' => count ( $this->productlist ), // 统计产品数量
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
	
}

/**
 * 诺奇左右瀑布流商品陈列模板。
 * 第二个商品陈列模板，左右两列，首部banner图。
 */
class DoubleWaterfallProTpl extends ProductListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->getProduct ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 诺奇商品陈列底部有导航
				'productlist' => $this->productlist, // 产品信息
				'listcount' => count ( $this->productlist ), // 统计产品数量
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
	
}

/**
 * only式相册瀑布流商品陈列模板。
 * 第三个商品陈列模板，自带微信相册功能。
 */
class AlbumWaterfallProTpl extends ProductListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->getProduct ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // only商品陈列底部有导航
				'productlist' => $this->albumPreview ( $this->productlist ), // only商品组装相册信息
				'listcount' => count ( $this->productlist ), // 统计产品数量
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
	
	/**
	 * 组装only样式自带相册功能的商品陈列（图片必须是绝对路径）。
	 * 该函数对于该组装语句已经作出特别注意标注。
	 * 对于Only式样的productlist模板，需要在相册里展示图片信息。
	 * Function：传入产品信息数组，循环添加产品路径，用于相册展示。
	 * Author：赵臣升。
	 * CreateTime：2014/07/13 14:32:25.
	 * @param array $productList 形参传入产品信息数组
	 * @return array $productList 返回产品信息数组，多增preview_image字段存放预览图片信息
	 */
	private function albumPreview($productList = NULL){
		for($i=0; $i < count ( $productList ); $i++){
			//对每一件产品都遍历进行操作，do:
			$html = $productList[$i]['html_description'];								//产品的html_description
	
			$sum = '';
			while(strlen($html)>0){
	
				$start = stripos($html, "<img src=");									//Step1：找到img标签开始
				if($start != false){
	
					$firstend = stripos($html, "\"", $start);							//Step2：找到<img src="第一个引号的位置
					$secondend = stripos($html, "\"", $firstend + 1);					//Step3：找到src=""第二个引号的位置
					$final = substr($html, $firstend + 1, $secondend - $firstend -1);	//Step4：切割出图片的路径
	
					$exist = stripos($final, 'ttp://');
	
					if($exist!=false){
						$weactstart = stripos($final, "/weact");						//特别注意，如果没有/weact，则应该找/Updata文件夹，或者自己拼接/weact项目名称！2014/09/17 14:12:25
						$final = substr($final, $weactstart);
					}
					$final = 'http://www.we-act.cn'.$final;
	
					if(strlen($sum) < 1){
						$sum .= $final;
					}else{
						$sum .= '^' . $final;
					}
	
					$html = substr($html, $secondend+1);
				}else{
					break;
				}
			}
			$productList[$i]['preview_image'] = $sum;
		}
		return $productList;
	}
	
}

/**
 * 时间轴式瀑布流商品陈列模板。
 * 第四个商品陈列模板，根据DHU时间轴大事记改版原创制成。
 */
class TimeAxisProTpl extends ProductListTemplate {
	
	/**
	 * 构造函数，传入5个参数得到模板信息。
	 * @param string $navinfo 导航信息。
	 * @property string $e_id 商家编号
	 * @property string $template_realpath 模板名称
	 * @property string $tpl_indexpath 模板索引
	 * @property number $template_type 模板类型
	 * @property string $nav_id 进入商品陈列的父级导航编号
	 */
	function __construct($navinfo = NULL) {
		$this->currentnav = $this->currentNav ( $navinfo );
		$this->CONST_NAV_TYPE = $this->currentnav ['nav_type']; // 初始化本类的nav_type，非常关键的一步赋值！这步出错，商品初始化方面全部会乱掉。
		$this->topnav = $this->extractTopNavInfo ( $navinfo );
		$this->getProduct ( $navinfo );
		$this->shareinfo = $this->wechatShareInfo ( $navinfo ); // 生成分享信息在后
		$this->initdata = array (
				'currentnav' => $this->currentnav,
				'navigation' => $this->divideTopNav ( $this->topnav ), // 时间轴陈列底部有导航
				'productlist' => $this->productlist, // 产品信息
				'listcount' => count ( $this->productlist ), // 统计产品数量
				'shareinfo' => $this->shareinfo, // 分享信息
		);
	}
	
}
?>