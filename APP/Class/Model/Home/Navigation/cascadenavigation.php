<?php
/**
 * 层级式导航类，读一次数据库，完成导航处理。
 * 性能测试200次平均结果：0.0095~0.0200秒之间，平均在0.0150左右。
 * 开销内存：790KB（无本类情况下2个函数内存开销）
 * @author 赵臣升
 * CreateTime：2015/02/23 18:50:36.
 */
class CascadeNavigation {
	
	/**
	 * @var string $e_id 商家编号
	 */
	private $e_id = '';
	
	/**
	 * @var array $cascadenav 层级导航信息
	 */
	private $cascadenav = array ();
	
	/**
	 * 层级导航类构造函数。
	 * @param string $e_id 商家编号
	 */
	function __construct($e_id = '') {
		if (empty ( $e_id )) return null; // 要查询的层级导航所属商家编号不能为空
		$this->getNavInfo ( $e_id ); // 读取并处理层级导航
	}
	
	/**
	 * =================私有函数===================
	 */
	
	/**
	 * 读取并拼接层级导航。
	 * @param string $e_id 商家编号
	 */
	private function getNavInfo($e_id = '') {
		//G ( 'begin' ); // 性能测试开始
		// 查询一次数据库的层级导航视图，得到所有导航信息
		$cascadenavmap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$allnavinfo = M ( 'cascade_navigation' )->where ( $cascadenavmap )->select (); // 从级联导航视图中查询该商家的级联导航信息
		// p($allnavinfo);die;
	
		// 一次性处理导航
		$totalnavcount = count ( $allnavinfo ); // 查询到连接后的顶级导航数目（包含因为左连接产生的重复）
		$cascadenav = array (); // 总的导航信息
		for($i = 0; $i < $totalnavcount; $i ++) {
			// Step1：对每一条导航信息处理图片路径和跳转链接
			$allnavinfo [$i] = $this->navURLImageHandle ( $allnavinfo [$i] );
			// Step2：如果导航数组中没有出现过该顶级导航，将顶级导航添加进数组中
			if (! isset ( $cascadenav [$allnavinfo [$i] ['p_nav_id']] )) {
				$cascadenav [$allnavinfo [$i] ['p_nav_id']] = array (
						'p_nav_id' => $allnavinfo [$i] ['p_nav_id'],
						'p_nav_name' => $allnavinfo [$i] ['p_nav_name'],
						'p_nav_english' => $allnavinfo [$i] ['p_nav_english'],
						'p_nav_image_path' => $allnavinfo [$i] ['p_nav_image_path'],
						'p_nav_url' => $allnavinfo [$i] ['p_nav_url'],
						'p_channel' => $allnavinfo [$i] ['p_channel'],
						'p_nav_type' => $allnavinfo [$i] ['p_nav_type'],
						'p_nav_order' => $allnavinfo [$i] ['p_nav_order'],
						'p_description' => $allnavinfo [$i] ['p_description'],
						'p_display_tailor' => $allnavinfo [$i] ['p_display_tailor'],
						'p_create_time' => $allnavinfo [$i] ['p_create_time'],
						'p_temporary_stop' => $allnavinfo [$i] ['p_temporary_stop'],
						'p_remark' => $allnavinfo [$i] ['p_remark'],
						'p_jumpurl' => $allnavinfo [$i] ['p_jumpurl']
				);
			}
			// Step3：如果子级导航不空，压栈到父级导航的sub_nav中
			if (! empty ( $allnavinfo [$i] ['c_nav_id'] )) {
				$cascadenav [$allnavinfo [$i] ['p_nav_id']] ['sub_nav'] [$allnavinfo [$i] ['c_nav_id']] = array (
						'c_nav_id' => $allnavinfo [$i] ['c_nav_id'],
						'c_nav_name' => $allnavinfo [$i] ['c_nav_name'],
						'c_nav_english' => $allnavinfo [$i] ['c_nav_english'],
						'c_nav_image_path' => $allnavinfo [$i] ['c_nav_image_path'],
						'c_nav_url' => $allnavinfo [$i] ['c_nav_url'],
						'c_channel' => $allnavinfo [$i] ['c_channel'],
						'c_nav_type' => $allnavinfo [$i] ['c_nav_type'],
						'c_nav_order' => $allnavinfo [$i] ['c_nav_order'],
						'c_description' => $allnavinfo [$i] ['c_description'],
						'c_display_tailor' => $allnavinfo [$i] ['c_display_tailor'],
						'c_create_time' => $allnavinfo [$i] ['c_create_time'],
						'c_temporary_stop' => $allnavinfo [$i] ['c_temporary_stop'],
						'c_remark' => $allnavinfo [$i] ['c_remark'],
						'c_jumpurl' => $allnavinfo [$i] ['c_jumpurl']
				);
			}
		}
		unset ( $allnavinfo ); // 注销变量释放内存
		
		//G ( 'end' ); // 性能测试结束
		//echo G ( 'begin', 'end' ) . "s" . "\n" . G ( 'begin', 'end', 'm' ) . "kb"; // 输出性能测试结果（时间复杂度与空间复杂度）
		
		$this->cascadenav = $cascadenav; // 将层级导航赋给类变量
	}
	
	/**
	 * 导航跳转URL和图片路径拼接获取函数。
	 * @param array $navinfo 顶级连接子级的导航视图信息
	 * @return array $navinfo 当前顶级导航扩展信息（拼接完url、nav_image_path）
	 */
	private function navURLImageHandle($navinfo = NULL) {
		if (empty ( $navinfo ['c_nav_id'] )) {
			// 处理没有子菜单的顶级导航跳转和图片
			if ($navinfo ['p_nav_type'] == 1) {
				$navinfo ['p_jumpurl'] = U ( 'Home/CustomerView/showSimpleInfo', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 拼接信息详情页面URL
			} else if ($navinfo ['p_nav_type'] == 2) {
				$navinfo ['p_jumpurl'] = U ( 'Home/ProductView/productList', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 拼接产品列表URL
			} else if ($navinfo ['p_nav_type'] == 3) {
				$tempurl = $navinfo ['p_nav_url']; 					// 提取出url地址
				$weactheader = "http://www.we-act.cn"; 				// 微动域名
				$checkstring = strstr ( $tempurl, $weactheader ); 	// 检测域名是否存在
				if (! empty ( $checkstring ) ) { 
					// 如果是微动，检测是否访问云总店，如果是总店才去检测是否有e_id
					$domain = C ( 'DOMAIN' ); // 提取域名
					$homegroupheader = $domain . "/weact/Home/"; 	// 云总店分组的URL前缀
					$visithome = strstr ( $tempurl, $homegroupheader ); // 检测是否访问云总店
					// 处理结果
					if (! empty ( $visithome )) {
						// 访问云总店
						if (! strpos ( $tempurl, 'e_id' )) {
							$navinfo ['p_jumpurl'] = $tempurl . '/e_id/' . $navinfo ['e_id']; // 超链接导航如果没有e_id带上e_id
						} else {
							$navinfo ['p_jumpurl'] = $tempurl; // 有e_id直接放过
						}
					} else {
						$navinfo ['p_jumpurl'] = $tempurl; // 不是访问云总店，也直接放过
					}
				} else { 
					// 微动以外域名直接放过不处理（2015/06/01 17:10:25改）
					$navinfo ['p_jumpurl'] = $navinfo ['p_nav_url'];
				}
			} else if ($navinfo ['p_nav_type'] == 4) {
				// 也有可能直接进入餐饮某一类的产品陈列
				$navinfo ['p_jumpurl'] = U ( 'CateIndustry/MenuView/menu', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 拼接餐饮版块菜品列表URL
			} else if ($navinfo ['p_nav_type'] == 5) {
				// 常用商品和服装一样都进入productlist页面，只不过读取的商品不同
				$navinfo ['p_jumpurl'] = U ( 'Home/ProductView/productList', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 拼接产品列表URL
			}
		} else {
			// 如果该顶级导航有子菜单→!empty情况
			if ($navinfo ['p_nav_type'] == 4) {
				// 进入餐饮带分类的产品陈列
				$navinfo ['p_jumpurl'] = U ( 'CateIndustry/MenuView/menu', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 拼接餐饮版块菜品列表URL
			} else {
				// 原来的服装版块的信息或微商城（这里分为信息二级导航和微商城，合并在一起了），1和2还有5；3是不可能有子菜单的，也进不到这里
				$navinfo ['p_jumpurl'] = U ( 'Home/CustomerView/showNavList', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['p_nav_id'] ), 'shtml', false, true ); // 如果还有子级导航，则组装子级导航url
			}
				
			// 如果有子级菜单，继续处理子级导航跳转和图片
			if ($navinfo ['c_nav_type'] == 1) {
				$navinfo ['c_jumpurl'] = U ( 'Home/CustomerView/showSimpleInfo', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['c_nav_id'] ), 'shtml', false, true ); // 拼接信息详情页面URL
			} else if ($navinfo ['c_nav_type'] == 2) {
				$navinfo ['c_jumpurl'] = U ( 'Home/ProductView/productList', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['c_nav_id'] ), 'shtml', false, true ); // 拼接产品列表URL
			} else if ($navinfo ['c_nav_type'] == 3) {
				/**
				 * 1、此处非微动的跳转链接肯定是不带e_id的，此处逻辑是自然加上e_id
				 * 2、/weact开头的需要不需要进行拼接
				 * 3、同上注释，e_id如果已经带上了，p_jumpurl字段如何设置
				 */
				$ctempurl = $navinfo ['c_nav_url']; 				// 提取出url地址
				$weactheader = "http://www.we-act.cn"; 				// 微动域名
				$checkstring = strstr ( $ctempurl, $weactheader ); 	// 检测域名是否存在
				if (! empty ( $checkstring ) ) {
					// 如果是微动，检测是否访问云总店，如果是总店才去检测是否有e_id
					$homegroupheader = "http://www.we-act.cn/weact/Home/"; // 云总店分组的URL前缀
					$visithome = strstr ( $ctempurl, $homegroupheader ); // 检测是否访问云总店
					// 处理结果
					if (! empty ( $visithome )) {
						// 访问云总店
						if (! strpos ( $ctempurl, 'e_id' )) {
							$navinfo ['c_jumpurl'] = $ctempurl . '/e_id/' . $navinfo ['e_id']; // 超链接导航如果没有e_id带上e_id
						} else {
							$navinfo ['c_jumpurl'] = $ctempurl; // 有e_id直接放过
						}
					} else {
						$navinfo ['c_jumpurl'] = $ctempurl; // 不是访问云总店，也直接放过
					}
				} else {
					// 微动以外域名直接放过不处理（2015/06/01 17:10:25改）
					$navinfo ['c_jumpurl'] = $navinfo ['c_nav_url'];
				}
			} else if ($navinfo ['c_nav_type'] == 4) {
				// 也有可能直接进入餐饮某一类的产品陈列
				$navinfo ['c_jumpurl'] = U ( 'CateIndustry/MenuView/menu', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['c_nav_id'] ), 'shtml', false, true ); // 拼接餐饮版块菜品列表URL
			} else if ($navinfo ['c_nav_type'] == 5) {
				// 常用商品和服装一样都进入productlist页面，只不过读取的商品不同
				$navinfo ['c_jumpurl'] = U ( 'Home/ProductView/productList', array ( 'e_id' => $navinfo ['e_id'], 'nav_id' => $navinfo ['c_nav_id'] ), 'shtml', false, true ); // 拼接产品列表URL
			}
			if (! empty ( $navinfo ['c_nav_image_path'] )) $navinfo ['c_nav_image_path'] = assemblepath ( $navinfo ['c_nav_image_path'], true ); // 如果子级菜单有导航图片，组装路径
		}
		if (! empty ( $navinfo ['p_nav_image_path'] )) $navinfo ['p_nav_image_path'] = assemblepath ( $navinfo ['p_nav_image_path'], true ); // 如果顶级菜单有导航图片，组装路径
	
		return $navinfo;
	}
	
	/**
	 * =================公有接口===================
	 */
	
	/**
	 * 外部访问本类的层级导航信息调用的函数。
	 */
	public function getCascadeNavInfo () {
		return $this->cascadenav;
	}
	
}
?>