<?php
/**
 * 商品服务层类，基类。
 * 微动平台所有商品的服务层基类，包含一些处理商品共用的函数。
 * @author 赵臣升。
 * CreateTime:2015/01/15 10:00:00.
 */
class ProductService {
	
	/**
	 * 商品类别，2属于服装，5属于常规商品，基类中默认0。
	 * @var number
	 */
	public $CONST_PRODUCT_TYPE = 0;
	
	/**
	 * 以下为商品详情页面读取数据所需。
	 */
	
	/**
	 * 处理图片路径函数，将图片路径组装成绝对路径。
	 * @param array $productlist 要组装图片路径的商品列表
	 * @param boolean $assembleimage 是否组装产品图片路径，默认组装
	 * @param boolean $assembleurl 是否组装产品详情路径，默认组装
	 * @param boolean $assembleactivity 是否组装产品活动路径，默认组装
	 * @return array $productlist 经过组装图片地址、详情地址、活动地址的商品列表信息
	 */
	private function assembleProImgURLAct($productlist = NULL, $assembleimage = TRUE, $assembleurl = TRUE, $assembleactivity = TRUE) {
		if(! empty ( $productlist )) {
			for($i = 0; $i < count ( $productlist ); $i++){
				if ($assembleimage) {
					$productlist [$i] ['macro_path'] = assemblepath($productlist [$i] ['macro_path'], true);
					$productlist [$i] ['micro_path'] = assemblepath($productlist [$i] ['micro_path'], true);
				}
				
				if ($assembleurl) {
					$productlist [$i] ['detailURL'] = U('Home/ProductView/productShow', array('e_id' => $productlist[$i]['e_id'], 'nav_id'=>$productlist[$i]['nav_id'], 'product_id'=>$productlist[$i]['product_id']), 'shtml', 0, true);
				}
				
				if ($assembleactivity) {
					if(! empty ( $productlist [$i]['activity_id'] )) {
						$productlist [$i] ['activityURL'] = U('Home/Activity/getMyActivity', array('e_id'=>$productlist[$i]['e_id'],'activity_id'=>$productlist[$i]['activity_id']), 'shtml', 0, true);
					} else{
						$productlist [$i] ['activityURL'] = '';		//暂无活动详情
					}
				}
			}
		}
		return $productlist;
	}
	
	/**
	 * 获取某导航下的商品信息列表（服装类读取服装类，常规商品读取常规商品类）。
	 * @param array $nav_id 导航编号
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @param boolean $autoassemble 自动组装产品的图片、详情与活动地址
	 * @return array $productlist 商品信息列表
	 */
	public function getProListByNav($navinfo = NULL, $autoassemble = TRUE) {
		$productlist = array ();
		if(! empty ( $navinfo )) {
			if (! empty ( $navinfo ['Type'] ) && $navinfo ['Type'] == 1) {
				// 要搜索
				$navinfo ['product_type'] = $this->CONST_PRODUCT_TYPE; // 为$navinfo增加product_type字段值，区别商品
				if ($navinfo ['searchcondition'] == 'product_name') {
					return $this->searchProByName ( $navinfo ); // 返回按商品名称搜索的结果
				} else if ($navinfo ['searchcondition'] == 'current_price') {
					return $this->searchProByPrice( $navinfo ); // 返回按商品价格模糊搜索的结果，现在默认使用50块，以后可以传入第二个参数进行查询（价格范围）
				} else if ($navinfo ['searchcondition'] == 'sex') {
					return $this->searchProBySex ( $navinfo ); // 返回按商品性别搜索的结果
				}
			} else {
				// 正常打开商品导航
				$searchmap = array (
						'e_id' => $navinfo ['e_id'],
						'nav_id' => $navinfo ['nav_id'],
						'product_type' => $this->CONST_PRODUCT_TYPE, // 服装类的查服装，普通商品查普通商品（已测试通过）。
						'on_shelf' => 1, // 选择已上架的商品
						'is_del' => 0
				);
			}
			$productlist = M ( 'product_image' )->where ( $searchmap )->order ( 'add_time desc' )->select ();
			if ($autoassemble) {
				$productlist = $this->assembleProImgURLAct( $productlist );
			}
		}
		return $productlist;
	}
	
	/**
	 * 按商品名称模糊搜索。
	 * @param array $searchinfo 搜索信息
	 * @property string e_id 商家编号
	 * @property string searchcondition 搜索条件，这里$searchinfo ['searchcondition']是product_name
	 * @property string searchcontent 搜索内容，这里是商品名称
	 * @param boolean $autoassemble 自动组装产品的图片、详情与活动地址
	 * @return array $productlist 商品信息列表
	 */
	public function searchProByName($searchinfo = NULL, $autoassemble = TRUE) {
		$productlist = array ();
		if (! empty ( $searchinfo )) {
			$searchmap = array(
					'e_id' => $searchinfo ['e_id'],
					$searchinfo ['searchcondition'] => array ( 'like', '%' . $searchinfo ['searchcontent'] . '%' ), // 模糊匹配商品名
					'product_type' => $this->CONST_PRODUCT_TYPE, // 这个非常重要！！！服装类的查服装，普通商品查普通商品（已测试通过）。
					'on_shelf' => 1, // 选择已上架的商品
					'is_del' => 0
			);
			$productlist = M ( 'product_image' )->where ( $searchmap )->order ( 'add_time desc' )->select ();
			if ($autoassemble) {
				$productlist = $this->assembleProImgURLAct( $productlist );
			}
		}
		return $productlist;
	}
	
	/**
	 * 按价格范围搜索商品。
	 * @param array $searchinfo 搜索信息
	 * @property string e_id 商家编号
	 * @property string searchcondition 搜索条件，这里$searchinfo ['searchcondition']是current_price
	 * @property number searchcontent 搜索内容，这里是商品价格（精确到元）
	 * @param number $searchpricerange 搜索价格范围，默认是50块
	 * @param boolean $autoassemble 自动组装产品的图片、详情与活动地址
	 * @return array $productlist 商品信息列表
	 */
	public function searchProByPrice($searchinfo = NULL, $searchpricerange = 50, $autoassemble = TRUE) {
		$productlist = array ();
		if (! empty ( $searchinfo )) {
			$searchmap = array(
					'e_id' => $searchinfo ['e_id'],
					$searchinfo ['searchcondition'] => array (
							array ( 'gt', $searchinfo ['searchcontent'] - $searchpricerange ), // 搜索价格区间下限
							array ( 'lt', $searchinfo ['searchcontent'] + $searchpricerange ), // 搜索价格区间上限
							'and'
					), // 价格范围搜索
					'product_type' => $this->CONST_PRODUCT_TYPE, // 这个非常重要！！！服装类的查服装，普通商品查普通商品（已测试通过）。
					'on_shelf' => 1, // 选择已上架的商品
					'is_del' => 0
			);
			$productlist = M ( 'product_image' )->where ( $searchmap )->order ( 'add_time desc' )->select ();
			if ($autoassemble) {
				$productlist = $this->assembleProImgURLAct( $productlist );
			}
		}
		return $productlist;
	}
	
	/**
	 * 区分性别的商品独有的按性别搜索。
	 * @param array $searchinfo 搜索信息
	 * @property string e_id 商家编号
	 * @property string searchcondition 搜索条件，这里$searchinfo ['searchcondition']是sex
	 * @property number searchcontent 搜索内容，男1，女2，通用（不区分性别）0，必须在搜索前把男女转成整型
	 * @param boolean $autoassemble 自动组装产品的图片、详情与活动地址
	 * @return array $productlist 商品信息列表
	 */
	public function searchProBySex($searchinfo = NULL, $autoassemble = TRUE) {
		if ($searchinfo ['searchcontent'] == '男') {
			$searchinfo ['searchcontent'] = 1; // 男1
		} else if ($searchinfo ['searchcontent'] == '女') {
			$searchinfo ['searchcontent'] = 2; // 女2
		} else {
			if ($searchinfo ['product_type'] == 2) {
				$searchinfo ['searchcontent'] == 0; // 不传入值或错误值，服装类商品直接默认为0
			} else if ($searchinfo ['product_type'] == 5) {
				$searchinfo ['searchcontent'] == -1; // 不传入值或错误值，非服装类商品直接默认为-1，与性别无关
			}
		}
		$productlist = array ();
		if (! empty ( $searchinfo )) {
			$searchmap = array(
					'e_id' => $searchinfo ['e_id'],
					$searchinfo ['searchcondition'] => $searchinfo ['searchcontent'], // 男1，女2，通用（不区分性别）0，必须在搜索前把男女转成整型
					'product_type' => $this->CONST_PRODUCT_TYPE, // 这个非常重要！！！服装类的查服装，普通商品查普通商品（已测试通过）。
					'on_shelf' => 1, // 选择已上架的商品
					'is_del' => 0
			);
			$productlist = M ( 'product_image' )->where ( $searchmap )->order ( 'add_time desc' )->select ();
			if ($autoassemble) {
				$productlist = $this->assembleProImgURLAct( $productlist );
			}
		}
		return $productlist;
	}
	
	/**
	 * 以下为商品详情页面读取数据所需。
	 */
	
	/**
	 * 根据id编号读取商品信息的函数，并为服装类商品读取尺码。
	 * @param string $idlist id编号列表
	 * @property string e_id 商家编号
	 * @property string nav_id 产品所在的导航编号（一级或二级）
	 * @property string product_id 产品编号
	 * @return array $productinfo 产品信息数组
	 */
	public function getProInfoByIdList($idlist = NULL) {
		$productinfo = array ();
		if (! empty ( $idlist )) {
			// Step1：找出商品信息、拼接图片绝对路径，并查询出商品的库存
			$productinfo = M ( 'product_image' )->where ( $idlist )->find (); // 找出商品信息
			$productinfo ['macro_path'] =  assemblepath ( $productinfo ['macro_path'], true ); // 处理大图片路径
			$productinfo ['micro_path'] =  assemblepath ( $productinfo ['micro_path'], true ); // 处理小图片路径
			$productinfo ['storage_amount'] = $this->getProductTotalStorage ( $idlist ['product_id'] ); // 查询出商品的库存量
			// Step2：处理商品的颜色尺码等
			$sizecolorinfo = $this->getSizeColor ( $idlist ['product_id'] );
			$productinfo ['size'] = $sizecolorinfo ['size'];
			if ($this->CONST_PRODUCT_TYPE == 2) {
				$productinfo ['color'] = $sizecolorinfo ['color']; // 服装类加上颜色
			}
		}
		return $productinfo;
	}
	
	/**
	 * 获取某商品的同类商品（服装类读取服装类，常规商品读取常规商品类）。
	 * @param array $nav_id 导航编号
	 * @property string e_id 商家编号
	 * @property string nav_id 导航编号
	 * @property string product_id 商品编号
	 * @param boolean $autoassemble 自动组装产品的图片、详情与活动地址
	 * @return array $sameproductlist 商品信息列表
	 */
	public function getSameProByNav($navinfo = NULL, $autoassemble = TRUE) {
		$sameproductlist = array ();
		if(! empty ( $navinfo )) {
			$searchmap = array (
					'e_id' => $navinfo ['e_id'],
					'nav_id' => $navinfo ['nav_id'],
					'product_type' => $this->CONST_PRODUCT_TYPE, // 服装类的查服装，普通商品查普通商品（已测试通过）。
					'product_id' => array ( 'neq', $navinfo ['product_id'] ), // 将自己排除在外，查询同类的产品
					'on_shelf' => 1, // 选择已上架的商品
					'is_del' => 0
			);
			$sameproductlist = M ( 'product_image' )->where ( $searchmap )->limit ( 3 )->order ( 'rand()' )->select ();
			if ($autoassemble) {
				$sameproductlist = $this->assembleProImgURLAct( $sameproductlist );
			}
		}
		return $sameproductlist;
	}
	
	/**
	 * 商品获得尺码/规格和颜色（服装类商品）的函数。
	 * @param string $product_id 形参传入服装编号
	 * @return array $sizecolor 包含size|color两个字段，分别是尺码数组和颜色数组。
	 */
	protected function getSizeColor($product_id = '') {
		$sizecolor = array (); // 尺寸/规格、颜色信息数组
		if (! empty ( $product_id )) {
			$sizecolormap = array(
					'product_id' => $product_id,
					'is_del' => 0
			);
			$sizecolor ['size'] = M ( 'productsizecolor' )->where ( $sizecolormap )->distinct ( 'product_size' )->order ( 'size_order asc' )->getField ( 'product_size', true );
			if ($this->CONST_PRODUCT_TYPE == 2) {
				$sizecolor ['color'] = M ( 'productsizecolor' )->where ( $sizecolormap )->distinct ( 'product_color' )->order ( 'size_order asc' )->getField ( 'product_color', true );
			}
		}
		return $sizecolor;
	}
	
	/**
	 * 查询某件商品的总库存量。
	 * @param string $product_id 商品编号
	 * @return number $totalstorage 商品所有规格/尺码、颜色总库存量
	 */
	protected function getProductTotalStorage($product_id = '') {
		$totalstorage = 0; // 默认总库存为0
		if (! empty ( $product_id )) {
			$totalmap = array (
					'product_id' => $product_id,
					'is_del' => 0
			);
			$storageinfo = M ( 'productsizecolor' )->where ( $totalmap )->select (); // 查询出所有库存信息
			if (! empty ( $storageinfo )) {
				for ($i = 0; $i < count ( $storageinfo ); $i ++) {
					$totalstorage += $storageinfo [$i] ['storage_amount'] - $storageinfo [$i] ['sell_amount']; // 统计商品总剩余数量
				}
			}
		}
		return $totalstorage;
	}
	
}

/**
 * 服饰商品类，继承自商品类。
 */
class CostumesService extends ProductService {
	
	/**
	 * 无参构造函数。
	 */
	function __construct() {
		$this->CONST_PRODUCT_TYPE = 2; // 服饰类商品类型为2
	}
	
}

/**
 * 公共商品类，继承自商品类。
 * 处理细分程度没有太深的商品。
 */
class CommodityService extends ProductService {
	
	/**
	 * 无参构造函数。
	 */
	function __construct() {
		$this->CONST_PRODUCT_TYPE = 5; // 公共类商品类型为5
	}
	
}
?>