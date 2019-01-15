<?php
//include_once ("../Product/product.php"); // 加载商品处理基类
import ( 'Class.Model.Home.Product.product', APP_PATH, '.php' ); // 使用import代替include，include命令在文件中再引文件路径立刻出错
import ( 'Class.Model.Home.Navigation.cascadenavigation', APP_PATH, '.php' ); // 加载微动移动端加速访问导航类

/**
 * 微动平台所有模板的基类，属于基类模板，包含一些模板共用的函数。
 * @author 赵臣升。
 * CreateTime:2015/01/15 10:00:00.
 */
class Template {
	
	/**
	 * 顶级导航信息。
	 * 原因：考虑到好多模板都要顶级导航、所以直接提炼出来放到template下，
	 * 反正总要查询导航的，一次查询，立刻存session，2分钟不会影响数据库。
	 * @var array $topnav
	 */
	protected $topnav;
	
	/**
	 * 模板初始化完成后渲染的data值
	 * @var array
	 */
	protected $initdata;
	
	/**
	 * 模板分享信息。
	 * @var array $shareinfo 模板分享信息
	 */
	protected $shareinfo;
	
	/**
	 * 移动端导航加速访问函数。
	 * 使用缓存机制和一次读写一次组装方法，加速用户在前端的访问。
	 * 特别警示：要注意微动平台有不同的商家，一定要注意区分储存不同商家的导航。
	 * @param array $navinfo 导航信息
	 * @property string $e_id 商家编号
	 */
	protected function accelerateNavInfo($navinfo) {
		// 全局变量
		$newgetflag = true; // 需要重新获取导航标志，默认为重新获取
		$allnavinfo = array (); // 该商家所有导航信息数组
		// 判断是否过期
		if (isset ( $_SESSION ['cascadenav'] [$navinfo ['e_id']] ['allnavinfo'] )) {
			$timenow = time(); // 获取现在时间
			$timeold = $_SESSION ['cascadenav'] [$navinfo ['e_id']] ['allnavinfo'] ['refreshtime']; // 获取上次获取导航时间
			if ($timenow - $timeold < 120) {
				// 默认120秒（2分钟）过期一次，秒数多少可以写在配置文件中，这里先写默认的120秒了
				$newgetflag = false; // 不需要重新获取导航信息
				$allnavinfo = $_SESSION ['cascadenav'] [$navinfo ['e_id']] ['allnavinfo']; // 旧的信息就可用
			}
		}
		// 过期处理方法
		if ($newgetflag) {
			$nav = new CascadeNavigation ( $navinfo ['e_id'] ); // 新建导航类变量
			$allnavinfo = $nav->getCascadeNavInfo (); // 得到新的层级导航信息给全局变量
			$cascadenav = array (
					$navinfo ['e_id'] => array (
							'allnavinfo' => $allnavinfo,
							'refreshtime' => time () // 得到当前层级导航刷新时间
					)
			); // 产生导航结构体信息
			session( 'cascadenav', null ); // 先清空session中的变量cascadenav
			session( 'cascadenav', $cascadenav ); // 新信息重新存入session中的cascadenav
		}
		return $allnavinfo;
	}
	
	/**
	 * 从层叠式商家导航中抽取顶级导航的信息。
	 * 原因：一次性查询的商家层级导航信息是按照哈希索引而不是按照数字索引的，哈希索引可以随机存取，处理二级导航和二级导航信息特别快，处理后兼容原平台所有导航。
	 * @param array $navinfo 导航信息
	 * @property string $e_id 商家编号
	 * @return array $topnav 顶级导航
	 */
	protected function extractTopNavInfo($navinfo = NULL) {
		$cascadenavinfo = $this->accelerateNavInfo ( $navinfo ); // 读取层级导航信息（未格式化字段）
		$topnav = array ();
		$i = 0; // 数字索引的循环变量
		foreach ( $cascadenavinfo as $key => $value) {
			$topnav [$i ++] = $this->formatterNav ( $value ); // 格式化父级导航信息
			// 若此处不做控制，在主页地方做出控制
			/* if ($i >= 8) {
				break; // 最多4个主页显示4个（这里在共有函数中直接处理不太好，最好在4格主页的地方提取，6格的提取6个，这样又多个模板，请修改：2015/05/25备注。）
			} */
		} // 循环foreach抽取哈希顶级导航为数字索引导航
		return $topnav;
	}
	
	/**
	 * 工具函数一：divideTopNav
	 * 将四格版主页顶级导航区分的函数，分为中部的导航：$centernav，channel为1；底部的导航：$footnav，channel为0.
	 * Author：赵臣升。
	 * CreateTime：2014/07/14 17:38:25.
	 * @param array $topnav 顶级导航
	 * @return array $divideresult 返回一个二维数组，里边将顶级导航按照channel字段区分开。
	 * 特别注意：经过此函数，$rsData增加'navigation','center'，分别为中部导航和底部导航。
	 */
	public function divideTopNav($topnav = NULL) {
		$centernav = array (); // 初始化一个数组$navList存放中央4格菜单（特别注意：不能用array_push压自身）
		$footnav = array (); // 初始化一个菜单数组$menu存放底部3格菜单
		foreach ( $topnav as $key ) {
			if ($key ['channel'] == 1) {
				array_push ( $centernav, $key ); // 表navigation中channel字段为1表示是四格内容，使用PHP自带函数压栈，将中央4格导航数组存放在$rsData的navList字段中
			} else {
				array_push ( $footnav, $key ); // 表navigation中channel字段为0表示是底部内容，使用PHP自带函数压栈
			}
		}
		$divideresult = array (
				'center' => $centernav, // 先将中央4格导航数组存放在$rsData的navList字段中
				'foot' => $footnav  // 再将底部3格活动（1格固定）导航数组存放在$rsData的menu字段中
		);
		return $divideresult;
	}
	
	/**
	 * 工具函数二：formatterNav。
	 * 将新导航字段格式化成原来导航的字段。
	 * @param array $cascadenavinfo 层级导航信息
	 * @param boolean $defaultparent 默认格式化父级导航，如果为false，就是格式化子级导航
	 * @return array $originalnav 原来字段样式的导航信息
	 */
	public function formatterNav($cascadenavinfo = NULL, $defaultparent = TRUE) {
		$originalnav = array (); // 全局格式化后的信息
		if (! empty ( $cascadenavinfo )) {
			if ($defaultparent) {
				$originalnav = array (
						'nav_id' => $cascadenavinfo ['p_nav_id'],
						'nav_name' => $cascadenavinfo ['p_nav_name'],
						'nav_english' => $cascadenavinfo ['p_nav_english'],
						'nav_image_path' => $cascadenavinfo ['p_nav_image_path'],
						'nav_url' => $cascadenavinfo ['p_nav_url'],
						'channel' => $cascadenavinfo ['p_channel'],
						'nav_type' => $cascadenavinfo ['p_nav_type'],
						'nav_order' => $cascadenavinfo ['p_nav_order'],
						'description' => $cascadenavinfo ['p_description'],
						'display_tailor' => $cascadenavinfo ['p_display_tailor'],
						'create_time' => $cascadenavinfo ['p_create_time'],
						'temporary_stop' => $cascadenavinfo ['p_temporary_stop'],
						'remark' => $cascadenavinfo ['p_remark'],
						'url' => $cascadenavinfo ['p_jumpurl']
				);
			} else {
				$originalnav = array (
						'nav_id' => $cascadenavinfo ['c_nav_id'],
						'nav_name' => $cascadenavinfo ['c_nav_name'],
						'nav_english' => $cascadenavinfo ['c_nav_english'],
						'nav_image_path' => $cascadenavinfo ['c_nav_image_path'],
						'nav_url' => $cascadenavinfo ['c_nav_url'],
						'channel' => $cascadenavinfo ['c_channel'],
						'nav_type' => $cascadenavinfo ['c_nav_type'],
						'nav_order' => $cascadenavinfo ['c_nav_order'],
						'description' => $cascadenavinfo ['c_description'],
						'display_tailor' => $cascadenavinfo ['c_display_tailor'],
						'create_time' => $cascadenavinfo ['c_create_time'],
						'temporary_stop' => $cascadenavinfo ['c_temporary_stop'],
						'remark' => $cascadenavinfo ['c_remark'],
						'url' => $cascadenavinfo ['c_jumpurl']
				);
			}
		}
		return $originalnav;
	}
	
	/**
	 * 模板返回渲染数据的函数，所有子类模板继承使用。
	 */
	public function getInitData() {
		return $this->initdata;
	}
	
	/**
	 * 虚函数：在微信中的分享信息。
	 */
	public function wechatShareInfo(){
		// to do: share template info in wechat...
	}
	
}

/**
 * 主页模板，继承自基类模板。
 * 级别：Level1。
 */
class IndexTemplate extends Template{
	
	public $sliders; // 主页幻灯片信息
	
	/**
	 * 获得主页幻灯片的函数。
	 * @param array $navinfo 传入导航信息
	 * @return array $sliders 返回幻灯片信息
	 */
	public function indexSlider($navinfo = NULL){
		$slidermap = array (
				'e_id' => $navinfo ['e_id'],
				'is_del' => 0
		);
		$sliders = M ( 'slider' )->where ( $slidermap )->select (); // 找出当前店家的slider图片，未组装路径
		for($i = 0; $i < count ( $sliders ); $i ++) {
			$sliders [$i] ['image_path'] = assemblepath ( $sliders [$i] ['image_path'] ); // 已组装路径2014/09/16 22:06:25
		}
		return $sliders;
	}
	
	/**
	 * 虚函数：单背景图模板处理幻灯片（随机一张）显示。
	 * @param array $slider 主页幻灯片信息
	 */
	public function randomSlider($slider = NULL) {
		// to do: varies handle by template, four and nineluxury template needs...
	}
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 主页导航分享的数据
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '欢迎进入' . $einfo ['e_name'] . '微世界', // 主页分享的标题
					'desc' => '快去逛逛您喜欢的商品吧', // 主页分享的描述
					'link' => U ( 'Home/Index/index', array ( 'e_id' => $einfo ['e_id'] ), 'shtml', 0, true ) // 分享的地址
			);
		}
		return $sharedata;
	}
}

/**
 * 一体两翼架构下，子级导航模板，继承自基类模板。
 * 级别：Level:between 1 to 2.
 * 特殊说明：该类属于中间类，鉴于4个子级页面：
 * 1、信息二级列表；2、商品二级列表（微商城）；3、信息详情；4、商品陈列（众多商品详情），
 * 这些子级导航页面与主页不同，他们都有公共的2个部分：
 * a)当前导航的信息（从哪个顶级导航进入的）；
 * b)需要顶级导航（原始模板需要所有，奢华模板需要底部的导航，有时也需要顶部）。
 * 其中读取主页导航的函数，还是继承自基类模板（鉴于IndexTemplate也要使用该函数，就不放到本类中了，让它留在基类里）。
 */
class ChildNavTemplate extends Template {
	
	/**
	 * 当前进入的顶级导航
	 * @var array
	 */
	protected $currentnav;
	
	/**
	 * 查询当前导航的函数currentNav。
	 * 特别注意：因为一体两翼架构下，二级导航和三级详情是共用这个currentNav函数的，
	 * 所以通过哈希索引顶级导航（进入的导航）信息很容易，但是要继续索引二级导航（继续进入的导航）信息，还要进行遍历哈希索引才能取到，因为程序不知道二级导航是从哪个顶级导航进入的。
	 * @param array $navinfo 传入导航的信息
	 * @return array $currentnav 返回导航的信息
	 */
	public function currentNav($navinfo = NULL){
		$cascadenav = $this->accelerateNavInfo ( $navinfo ); // 读取层级导航信息
		if (! empty( $cascadenav [$navinfo ['nav_id']] )) {
			return $this->formatterNav ( $cascadenav [$navinfo ['nav_id']] ); // 哈希索引到当前进入的顶级导航，并格式化层级导航信息字段
		} else {
			$currentnavinfo = $this->seakCurrentNavInfo ( $cascadenav, $navinfo ['nav_id'] ); // 遍历哈希索引当前进入的二级导航
			return $this->formatterNav ( $currentnavinfo, false ); // 格式化层级导航信息字段，注意这里格式化的是子级导航信息，带上第二个参数
		}
		
	}
	
	/**
	 * 从层级导航哈希中寻找二级导航所属的顶级导航。
	 * @param array $cascadenavinfo 层级导航信息
	 * @param string $secondnav_id 要找的二级导航编号
	 * @return array $currentnavinfo 当前二级导航的信息
	 */
	private function seakCurrentNavInfo ($cascadenavinfo = NULL, $secondnav_id = '') {
		$currentnavinfo = array (); // 要找的二级导航的信息
		foreach ($cascadenavinfo as $key => $value) {
			if (! empty ( $value ['sub_nav'] [$secondnav_id] )) {
				$currentnavinfo = $value ['sub_nav'] [$secondnav_id]; // 如果索引到这样的二级导航信息，则返回当前信息
				break;
			}
		}
		return $currentnavinfo;
	}
	
}

/**
 * 二级导航模板，该导航模板分化出商品类别和信息类别。
 * 级别：Level:between 1 to 2.
 * 作用：处理二级导航及其URL跳转的拼接。
 */
class SecondNavTemplate extends ChildNavTemplate {
	
	/**
	 * 该顶级导航下的二级导航
	 * @var array
	 */
	protected $secondnav;
	
	/**
	 * 从层级导航中抽取出二级导航信息的函数。
	 * @param array $navinfo 包含商家编号和当前进入顶级导航的信息
	 * @property string e_id 商家编号
	 * @property string nav_id 进入的顶级导航编号
	 * @return array $secondnavinfo 包含拼接跳转地址的信息
	 */
	protected function extractSecondNavInfo($navinfo = NULL) {
		$cascadenav = $this->accelerateNavInfo ( $navinfo ); // 读取层级导航信息
		$cascadesecondnav = $cascadenav [$navinfo ['nav_id']] ['sub_nav']; // 将二级导航所有的导航信息取出来（未格式化的哈希索引）
		
		$secondnavinfo = array (); // 全局变量：格式化字段的二级导航二维数组
		$i = 0; // 数字索引的循环变量
		foreach ( $cascadesecondnav as $key => $value) {
			$secondnavinfo [$i ++] = $this->formatterNav ( $value, false ); // 格式化子级导航信息，第二个参数带上false
		} // 循环foreach抽取哈希顶级导航为数字索引导航
		return $secondnavinfo;
	}
	
}

/**
 * 二级信息导航模板，继承自基类模板。
 * 级别：Level2。
 */
class NavListTemplate extends SecondNavTemplate {
	
	/**
	 * 该模板是二级模板。
	 * @var number
	 */
	protected $CONST_NAV_LEVEL = 2;
	
	/**
	 * 该模板类别为1，属于信息二级导航
	 * @var number
	 */
	protected $CONST_NAV_TYPE = 1;
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 主页导航分享的数据
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo')->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '为您分享 ' . $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'], // 二级信息导航分享的标题
					'desc' => $einfo ['e_name'] . $this->currentnav ['nav_name'] . ' 有您喜欢的!', // 二级信息导航分享的描述
					'link' => U ( 'Home/CustomerView/showNavList', array ( 'e_id' => $einfo ['e_id'], 'nav_id' => $this->currentnav ['nav_id'] ), 'shtml', 0, true ) // 分享的地址
			);
		}
		return $sharedata;
	}
}

/**
 * 微商城模板（二级商品导航），继承自子级导航模板。
 * 级别：Level2。
 */
class MicroShopTemplate extends SecondNavTemplate {
	
	/**
	 * 该模板是二级模板。
	 * @var number
	 */
	protected $CONST_NAV_LEVEL = 2;
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 主页导航分享的数据
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo')->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '为您分享 ' . $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'], // 微商城分享的标题
					'desc' => $einfo ['e_name'] . $this->currentnav ['nav_name'] . ' 有您喜欢的!', // 微商城分享的描述
					'link' => U ( 'Home/CustomerView/showNavList', array ( 'e_id' => $einfo ['e_id'], 'nav_id' => $this->currentnav ['nav_id'] ), 'shtml', 0, true ) // 分享的地址
			);
		}
		return $sharedata;
	}
	
}

/**
 * 信息详情模板，继承自基类模板。
 * 级别：Level3。
 */
class SimpleInfoTemplate extends ChildNavTemplate {
	
	/**
	 * 该模板是三级模板。
	 * @var number
	 */
	protected $CONST_NAV_LEVEL = 3;
	
	/**
	 * 该模板类别为1，属于信息类三级导航
	 * @var number
	 */
	protected $CONST_NAV_TYPE = 1;
	
	/**
	 * 该导航下的信息详情。
	 * @var array
	 */
	protected $simpleinfo;
	
	/**
	 * 读取某导航下的信息详情。
	 * @param array $navinfo 导航信息
	 * @return array $simpleinfo 该导航下的信息详情
	 */
	public function readSimpleInfo($navinfo = NULL) {
		$infomap = array(
				'e_id' => $navinfo ['e_id'],
				'nav_id' => $navinfo ['nav_id'],
				'is_del' => 0
		);
		$simpleinfo = M ( 'simpleinfo' )->where ( $infomap )->find (); // 找到信息详情
		return $simpleinfo;
	}
	
	/**
	 * 该函数对于该组装语句已经作出特别注意标注。
	 * 从html格式语言中提取图片的函数，为了保证模板选取不会出错。
	 * @param string $htmlinfo 信息展示的html格式语言
	 * @return array $imageList 信息展示页面纯图文方式的图片数组
	 */
	public function extractHtmlImage($htmlinfo = NULL) {
		$html = 'from ' . unescape ( $htmlinfo ['html_content'] ); // 信息详情的html_description
	
		$k = 0; // 循环变量k置为0
		$imageList = array ();
		while ( strlen ( $html ) > 0 ) {
			$start = stripos ( $html, "<img src=" ); // Step1：找到img标签开始
			if ($start != false) {
				$firstend = stripos ( $html, "\"", $start ); // Step2：找到<img src="第一个引号的位置
				$secondend = stripos ( $html, "\"", $firstend + 1 ); // Step3：找到src=""第二个引号的位置
				$final = substr ( $html, $firstend + 1, $secondend - $firstend - 1 ); // Step4：切割出图片的路径
	
				$exist = stripos ( $final, 'ttp://' );
	
				if ($exist != false) {
					$weactstart = stripos ( $final, "/weact" ); // 特别注意，如果没有/weact，则应该找/Updata文件夹，或者自己拼接/weact项目名称！2014/09/17 14:13:25
					$final = substr ( $final, $weactstart );
				}
	
				$imageList [$k ++] = $final;
				$html = substr ( $html, $secondend + 1 );
			} else {
				break;
			}
		}
		return $imageList;
	}
	
	/**
	 * 对图文描述信息进行抽取纯文本函数。
	 * @param array $htmlinfo 导航下查询出来的信息数组
	 * @return string $htmltext html的纯文本
	 */
	public function extractPureHtmlText($htmlinfo = NULL) {
		$htmltext = '';
		if(! empty ( $htmlinfo )){
			$htmltext = strip_tags ( $htmlinfo ['html_content'] ); // 剥离html标签。
		}
		return $htmltext;
	}
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 主页导航分享的数据
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo')->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '为您分享 ' . $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'], // 信息详情分享的标题
					'desc' => $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'] . ' 请您查阅!', // 信息详情分享的描述
					'link' => U ( 'Home/CustomerView/showSimpleInfo', array ( 'e_id' => $einfo ['e_id'], 'nav_id' => $this->currentnav ['nav_id'] ), 'shtml', 0, true ) // 分享的地址
			);
		}
		return $sharedata;
	}
}

/**
 * 商品陈列模板，继承自基类模板。
 * 级别：Level3。
 */
class ProductListTemplate extends ChildNavTemplate {
	
	/**
	 * 该模板是三级模板。
	 * @var number
	 */
	protected $CONST_NAV_LEVEL = 3;
	
	/**
	 * 商品陈列的数据（二维数组）
	 * @var array
	 */
	protected $productlist = array ();
	
	/**
	 * 导航类别，默认0，构造函数中重置（2属于服装商品，5属于公共商品）。
	 * @var number
	 */
	public $CONST_NAV_TYPE = 0;
	
	/**
	 * 本类私有函数，根据导航类别不同创建不同的产品多态对象。
	 * @return object $pro 返回产品类多态类型对象，如果导航不符合，返回null避免出错
	 */
	private function getProductObject() {
		$pro = NULL;
		if ($this->CONST_NAV_TYPE == 2) {
			$pro = new CostumesService();
		} else if ($this->CONST_NAV_TYPE == 5) {
			$pro = new CommodityService();
		}
		return $pro;
	}
	
	/**
	 * 重新检查nav_type，防止因为商品类临时改为超链接引起的报错。
	 * 重查商品表中该导航关联的商品，如果有，修改其类型为商品类型，如果没有，强制变成非服装类商品，兼容性更大。
	 * @param array $navinfo 导航信息
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @property string product_id 商品编号
	 */
	protected function recheckNavType($navinfo = NULL) {
		$productmap = array (
				'e_id' => $navinfo ['e_id'],
				'nav_id' => $navinfo ['nav_id'],
				'is_del' => 0,
		);
		$productinfo = M ( 'product' )->where ( $productmap )->select ();
		if ($productinfo){
			$this->CONST_NAV_TYPE = $productinfo [0] ['product_type'];
		} else {
			$this->CONST_NAV_TYPE = 5;
		}
	}
	
	/**
	 * 获取某导航下的商品信息列表，返回给本类的$this->productlist数组。
	 * @param array $navinfo 导航信息
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @return array $productlist 商品信息列表
	 */
	public function getProduct($navinfo = NULL) {
		if (! empty ( $navinfo )) {
			$pro = $this->getProductObject(); // 生成商品调度对象
			$this->productlist = $pro->getProListByNav ( $navinfo ); // 普通读取导航下商品
			unset ( $pro ); // 注销商品调度对象
		}
	}
	
	/**
	 * 按条件搜索商品。
	 * @param array $searchinfo 搜索信息
	 * @property string e_id 商家编号
	 * @property string searchcondition 搜索条件，这里$searchinfo ['searchcondition']是product_name,current_price,sex
	 * @property string searchcontent 搜索内容，这里是商品名称
	 * @param number $pricerange 可缺省参数搜索价格范围，只在搜索价格的时候用到
	 * @return array $productlist 按条件搜索的商品结果
	 */
	public function searchProduct($searchinfo = NULL, $pricerange = 0) {
		if (! empty ( $searchinfo )) {
			$pro = $this->getProductObject();
			switch( $searchinfo ['searchcondition'] ) {
				case 'product_name':
					$this->productlist = $pro->searchProByName ( $searchinfo );
					break;
				case 'current_price':
					$this->productlist = $pro->searchProByPrice ( $searchinfo, $pricerange );
					break;
				case 'sex':
					$this->productlist = $pro->searchProBySex ( $searchinfo );
					break;
				default:
					$this->productlist = $pro->searchProByName ( $searchinfo );
					break;
			}
		}
	}
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 主页导航分享的数据
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo')->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '特别推荐 ' . $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'] . ' 大热卖', // 商品陈列分享的标题
					'desc' => $einfo ['e_name'] . ' ' . $this->currentnav ['nav_name'] . ' 快来抢购!', // 商品陈列分享的描述
					'link' => U ( 'Home/ProductView/productList', array ( 'e_id' => $einfo ['e_id'], 'nav_id' => $this->currentnav ['nav_id'] ), 'shtml', 0, true ) // 分享的地址
			);
			if (! empty ( $this->productlist )) {
				$sharedata ['img_url'] = $this->productlist [0] ['macro_path']; // 如果产品列表不空，则用该导航下第一件产品的图片替换分享图片
			}
		}
		return $sharedata;
	}
}

/**
 * 商品展示模板，继承自基类模板。
 * 级别：Level4。
 */
class ProductShowTemplate extends ChildNavTemplate {
	
	/**
	 * 产品详情的产品信息
	 * @var array $productinfo 
	 */
	protected $productinfo = array ();
	
	/**
	 * 产品详情的同类产品
	 * @var array $sameproductlist 
	 */
	protected $sameproductlist = array ();
	
	/**
	 * 重新检查nav_type，防止因为商品类临时改为超链接引起的报错。
	 * 重查商品表中该导航关联的商品，如果有，修改其类型为商品类型，如果没有，强制变成非服装类商品，兼容性更大。
	 * @param array $navinfo 导航信息
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @property string product_id 商品编号
	 */
	protected function recheckNavType($navinfo = NULL) {
		$productmap = array (
				'e_id' => $navinfo ['e_id'],
				'nav_id' => $navinfo ['nav_id'],
				'is_del' => 0,
		);
		$productinfo = M ( 'product' )->where ( $productmap )->select ();
		if ($productinfo){
			$this->CONST_NAV_TYPE = $productinfo [0] ['product_type'];
		} else {
			$this->CONST_NAV_TYPE = 5;
		}
	}
	
	/**
	 * 本类私有函数，根据导航类别不同创建不同的产品多态对象。
	 * @return object $pro 返回产品类多态类型对象，如果导航不符合，返回null避免出错
	 */
	private function getProductObject() {
		$pro = NULL;
		if ($this->CONST_NAV_TYPE == 2) {
			$pro = new CostumesService();
		} else if ($this->CONST_NAV_TYPE == 5) {
			$pro = new CommodityService();
		}
		return $pro;
	}
	
	/**
	 * 获取某导航下的商品信息，返回给本类的$this->productinfo数组。
	 * @param array $navinfo 导航信息
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @property string product_id 商品编号
	 * @return array $productlist 商品信息列表
	 */
	public function getProductInfo($navinfo = NULL) {
		if (! empty ( $navinfo )) {
			$pro = $this->getProductObject(); // 生成商品调度对象
			$this->productinfo = $pro->getProInfoByIdList ( $navinfo ); // 读取要展示的商品信息
			unset ( $pro ); // 注销商品调度对象
		}
	}
	
	/**
	 * 获取某导航下的商品信息，返回给本类的$this->productinfo数组。
	 * @param array $navinfo 导航信息
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @property string product_id 商品编号
	 * @return array $productlist 商品信息列表
	 */
	public function getSameProductList($navinfo = NULL) {
		if (! empty ( $navinfo )) {
			$pro = $this->getProductObject(); // 生成商品调度对象
			$this->sameproductlist = $pro->getSameProByNav ( $navinfo ); // 读取要展示的同类商品信息
			unset ( $pro ); // 注销商品调度对象
		}
	}
	
	/**
	 * 微信中分享后的信息
	 * @param array $navinfo 导航信息
	 * @return array $sharedata 在微信中分享的信息
	 */
	public function wechatShareInfo($navinfo = NULL) {
		$sharedata = array (); // 初始化分享信息
		if (! empty ( $navinfo )) {
			$emap = array (
					'e_id' => $navinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo')->where ( $emap )-> find(); // 找出企业信息
			$sharedata = array (
					'appid' => $einfo ['appid'], // 分享的商家appid
					'img_url' => assemblepath ( $einfo ['e_square_logo'], true ), // 分享的图片必须是绝对路径
					'img_width' => '160', // 宽度
					'img_height' => '160', // 高度
					'title' => '特别推荐 ' . $this->productinfo ['product_name'] . ' 热卖抢购中!', // 商品详情分享的标题
					'desc' => '快来看看' . $einfo ['e_name'] . '的' . $this->productinfo ['product_name'] . ' 分享给您!', // 商品详情分享的描述
					'link' => U ( 'Home/ProductView/productShow', array ( 'e_id' => $einfo ['e_id'], 'nav_id' => $this->currentnav ['nav_id'], 'product_id' => $this->productinfo ['product_id'] ), 'shtml', 0, true ) // 分享的地址
			);
			if (! empty ( $this->productinfo )) {
				$sharedata ['img_url'] = $this->productinfo ['macro_path']; // 如果产品列表不空，则用该导航下第一件产品的图片替换分享图片
			}
		}
		return $sharedata;
	}
}

/**
 * 商品详情参数模板，继承自基类模板。
 * 级别：Level5。
 */
class ProductDetailTemplate extends Template{
	
}

/**
 * 购物车模板，继承自基类模板。
 * 级别：Level6。
 */
class CartTemplate extends Template {
	
}

/**
 * 订单预处理模板，继承自基类模板。
 * 级别：Level7。
 */
class OrderPreHandleTemplate extends Template {
	
}

/**
 * 订单信息模板，继承自基类模板。
 * 级别：Level8。
 */
class OrderInfoTemplate extends Template {
	
}

/**
 * 客户中心需要登录页面的模板，处理客户中心一些需要顶级导航的模板。
 * 级别：Level.10。
 */
class MemberHandleTemplate extends Template {
	
}

?>