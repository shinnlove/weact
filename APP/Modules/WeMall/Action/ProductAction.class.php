<?php
/**
 * 管理分店商品的控制器。
 * @author Administrator
 * CreateTime:2015/04/29 14:02:25.
 */
class ProductAction extends GuestMallAction {
	/**
	 * 所有商品类别视图。
	 */
	public function allCategory() {
		$subproduct = M ( 'subbranch_product_image' ); // 分店商品视图
		$navtable = M ( 'navigation' ); // 导航表
		$navbuck = array (); // 级联导航桶
		$finalnav ['navlist'] = array (); // 最终格式化的数组
		
		// Step1：先读取该商家所有商品类别为服装或日常用品的导航（nav_type==2或nav_type==5）
		$navmap = array (
				'e_id' => $this->eid,
				'nav_type' => array ( 2, 5, 'or' ), // nav_type==2或nav_type==5
				'is_del' => 0
		);
		$navlist = $navtable->where ( $navmap )->order ( 'nav_level asc, nav_order asc' )->select (); // 找出该商家所有服装和日常用品导航（先顶级后子级导航）
		
		// Step2：如果导航不空，开始分桶子
		if ($navlist) {
			$navcount = count ( $navlist ); // 统计有多少导航数量
			for($i = 0; $i < $navcount; $i ++) {
				if ($navlist [$i] ['father_nav_id'] == "-1") {
					// 如果是顶级导航
					if (! isset ( $navbuck [$navlist [$i] ['nav_id']] )) {
						$navlist [$i] ['children'] = array (); // 准备孩子数组（为else中array_push地方做准备）
						$navbuck [$navlist [$i] ['nav_id']] = $navlist [$i]; // 为顶级导航开辟一个数组
					}
				} else {
					// 如果是子级导航
					$navlist [$i] ['nav_product_count'] = 0; // 先默认这个分类下商品数量为0
					array_push ( $navbuck [$navlist [$i] ['father_nav_id']] ['children'], $navlist [$i] ); // 把子级导航推入父级导航编号的下标数组中
				}
			}
		}
		
		// Step3：对顶级导航检查是否有子级导航
		foreach ($navbuck as &$topnav) {
			if (empty ( $topnav ['children'] )) {
				// 如果没有子级导航，把自己的信息作为一个子级导航
				$tempcopy = $topnav; // 复制一份
				unset($tempcopy ['children']); // 撤销孩子字段
				$tempcopy ['nav_product_count'] = 0; // 默认这个分类下商品数量为0
				$topnav ['children'] = $tempcopy; // 将自己的信息复制一份作为唯一的子级导航
			}
		}
		
		// Step4：查询分店商品表统计各个分类下的商品
		$subpromap = array (
				'e_id' => $this->eid, // 当前商家下
				'subbranch_id' => $this->sid, // 当前分点下
				'on_shelf' => 1, // 上架状态的商品
				'is_del' => 0 // 没有被删除的
		);
		$subpronavlist = $subproduct->where ( $subpromap )->group ( 'nav_id' )->order ( 'onshelf_time desc, nav_id asc, nav_product_count asc' )->field ( 'nav_id, nav_name, count(sub_pro_id) as nav_product_count' )->select (); // 按条件找出商品类别的数量
		
		// Step5：对每个二级分类进行标注商品数量，两次引用改变这个值
		foreach ($navbuck as &$topnav) {
			foreach ($topnav ['children'] as &$secondnav) {
				// $secondnav 是 $navbuck [$topnav ['nav_id']] ['children']里的某一个
				foreach ($subpronavlist as $subnav) {
					if ($secondnav ['nav_id'] == $subnav ['nav_id']) {
						$secondnav ['nav_product_count'] = $subnav ['nav_product_count']; // 如果nav_id对上，则把商品总数值复制过去
						break; // 终止当前对$subpronavlist的循环
					}
				}
				
			}
		}
		
		// Step6：最终拆解打包一下格式和数据
		foreach ($navbuck as $singletopnav) {
			array_push ( $finalnav ['navlist'], $singletopnav );
		}
		
		$this->navlistjson = jsencode ( $finalnav ); // json压缩导航与商品数信息$finalnav
		$this->display ();
	}
	
	/**
	 * 商品列表页面。
	 */
	public function productList() {
		// 设置请求参数：基本参数部分
		$nextstart = 0; // 从0开始查询
		$perpage = 10; // 默认每页10条商品
		$sortfield = "current_price"; // 默认按上架时间排序
		$sortorder = "desc"; // 默认排序0是desc
		// 设置请求参数：搜索方式参数部分（默认优先查询关键字，然后是导航，然后是属性分类）
		$searchparams = array (
				'keyword' => I ( 'keyword', "" ), // 默认自定义搜索关键字为空
				'navid' => I ( 'nid', "" ), // 接收导航编号，默认为空
				'querytype' => I ( 'querytype', 0 ), // 查询商品类型，0为全部商品，1为精选商品，2为新品，3为折扣商品
		);
		// 关键字过滤处理
		if (! empty ( $searchparams ['keyword'] )) {
			$searchparams ['keyword'] = str_replace ( ",", "", $searchparams ['keyword'] ); // 清除不必要的英文逗号
			$searchparams ['keyword'] = str_replace ( "，", "", $searchparams ['keyword'] ); // 清除不必要的中文逗号
		}
		
		// 分页查询商品
		$finalresult = $this->getProductByPage ( $this->sid, $searchparams, $nextstart, $perpage, $sortfield, $sortorder, true );
		$this->nid = $searchparams ['navid']; // 推送导航类型（很重要）
		
		// 设置页面标题
		$title = "店铺商品"; // 商品陈列页面标题
		if (! empty ( $searchparams ['keyword'] )) {
			// 优先关键字搜索
			$title = $searchparams ['keyword']; // 标题变成关键字搜索
		} else {
			if (! empty ( $searchparams ['navid'] )) {
				// 如果导航不空
				$navmap = array (
						'nav_id' => $searchparams ['navid'], // 当前进入的导航编号
						'is_del' => 0
				);
				$navinfo = M ( 'navigation' )->where( $navmap )->find (); // 尝试找出navinfo信息
				if ($navinfo) {
					$title = $navinfo ['nav_name']; // 标题换成导航名字
				}
			} else {
				// 如果导航空，则是全部商品或新品
				if ($searchparams ['querytype'] == 0) {
					$title = "全部商品";
				} else if ($searchparams ['querytype'] == 1) {
					$title = "店铺精选";
				} else if ($searchparams ['querytype'] == 2) {
					$title = "店铺新品";
				} else if ($searchparams ['querytype'] == 3) {
					$title = "特惠折扣";
				}
			}
		}
		
		// 推送一些关键值和展示页面
		$this->keyword = $searchparams ['keyword']; // 推送商品陈列的关键字（可能为自定义搜索）
		$this->title = $title; // 推送商品陈列的标题
		$this->querytype = $searchparams ['querytype']; // 推送查询类型
		$this->productlistjson = jsencode ( $finalresult ); // 将数据打包成json
		$this->display ();
	}
	
	/**
	 * ajax请求商品处理函数。
	 */
	public function queryProduct() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		// 设置请求参数：搜索方式参数部分（默认优先查询关键字，然后是导航，然后是属性分类）
		$searchparams = array (
				'keyword' => I ( 'keyword', "" ), // 默认自定义搜索关键字为空
				'navid' => I ( 'nid', "" ), // 接收导航编号，默认为空
				'querytype' => I ( 'querytype', 0 ), // 查询商品类型，0为全部商品，1为精选商品，2为新品，3为折扣商品
		);
		// 关键字过滤处理
		if (! empty ( $searchparams ['keyword'] )) {
			$searchparams ['keyword'] = str_replace ( ",", "", $searchparams ['keyword'] ); // 清除不必要的英文逗号
			$searchparams ['keyword'] = str_replace ( "，", "", $searchparams ['keyword'] ); // 清除不必要的中文逗号
		}
		// 设置请求参数：基本参数
		$nextstart = I ( 'nextstart', 0 ); 		// 下一页开始s
		$perpage = I ( 'perpage', 10 ); 		// 默认每页10条商品
		$sortfield = I ( 'querysort', "onshelf_time" ); // 默认按上架时间排序
		$sortorder = ""; 						// 默认排序0是desc
		$orderby = I ( 'queryorder', 0 ); 		// 默认0降序排列
		if ($orderby == 1) {
			$sortorder = "asc";
		} else {
			$sortorder = "desc";
		}
		
		// 分页查询商品
		$finalresult = $this->getProductByPage ( $this->sid, $searchparams, $nextstart, $perpage, $sortfield, $sortorder );
		$this->ajaxReturn ( $finalresult ); // ajax返回
	}
	
	/**
	 * 通过导航类别|商品性质，分页获取商品函数。
	 * 本函数优先识别导航分类，如果导航分类为空，才去判断querytype。
	 * @param string $sid 分店编号
	 * @param array $searchparams 搜索参数（如下三个必然包含其中之一）
	 * @property string keyword 关键字
	 * @property string navid （当前进入）导航编号
	 * @property string querytype （属性商品）查询类型，0为全部商品，1为精选商品，2为新品，3为折扣商品
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param string $sortfield 要排序的字段
	 * @param string $sortorder 要排序的顺序desc|asc
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getProductByPage($sid = '', $searchparams = NULL, $nextstart = 0, $perpage = 10, $sortfield = '', $sortorder = '', $firstinit = FALSE) {
		$subproductview = M ( 'subbranch_product_image_new' ); // 实例化视图结构
		$orderby = $sortfield . " " . $sortorder; // 定义要排序的方式（每个表都不一样）
		$subproductlist = array (); // 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'subbranch_id' => $sid, // 当前分店
				'on_shelf' => 1, 		// 上架的商品（2015/05/25修改）
				'is_del' => 0, 			// 没有被删除的
		);
		// 补充查询参数
		if (! empty ( $searchparams ['keyword'] )) {
			// 优先关键字查询
			$querymap ['product_name'] = array ( "like", "%" . $searchparams ['keyword'] . "%" );
		} else {
			// 其次是导航编号和商品属性查询
			if (! empty ( $searchparams ['navid'] ) && $searchparams ['navid'] != "-1") {
				// 导航如果不空，代表是从分类跳转过来的（先检验是否是子级导航，如果是子级导航，则可以直接查询商品，否则必须做or处理）
				$navmap = array (
						'father_nav_id' => $searchparams ['navid'], // 上级导航编号
						'is_del' => 0 // 没有被删除的导航
				);
				$childnavlist = M ( 'navigation' )->where ( $navmap )->select (); // 尝试找寻孩子导航
				if ($childnavlist) {
					// 如果有子级导航（子级导航才连接商品陈列），一开始用array（a1,a2,'or'），thinkphp解析SQL还是有问题。
					$expstring = ""; // 最终拼接出来的查询SQL
					foreach ($childnavlist as $childnav) {
						$expstring .= "nav_id = '" . $childnav ['nav_id'] . "'" . " or ";
					}
					$expstring = substr ( $expstring, 0, strlen ( $expstring ) - 4 ); // 取出最后的or与2个空格
					$querymap ['_string'] = $expstring; // 组合条件查询，多个导航or连接
				} else {
					// 如果自己就是直接连接商品陈列，则直接查询商品类别下的商品
					$querymap ['nav_id'] = $searchparams ['navid']; // 增加导航类别的限制
				}
			} else {
				// 导航如果空，代表是直接查询商品属性类别
				// 判断查询条件是精选商品或新品或是特价商品
				if ($searchparams ['querytype'] == 1) {
					$querymap ['is_feature'] = 1;
				} else if ($searchparams ['querytype'] == 2) {
					$querymap ['is_new'] = 1;
				} else if ($searchparams ['querytype'] == 3) {
					$querymap ['is_preferential'] = 1;
				}
			}
		}
		
		$totalcount = $subproductview->where ( $querymap )->count ( 'distinct sub_pro_id'); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$subproductlist = $subproductview->where ( $querymap )->group('sub_pro_id')->order ( $orderby )->limit ( $nextstart, $realgetnum )->select (); // 查询商品信息
			
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$subproductlist [$i] ['onshelf_time'] = timetodate ( $subproductlist [$i] ['onshelf_time'] ); // 格式化上架时间
				$subproductlist [$i] ['macro_path'] = assemblepath ( $subproductlist [$i] ['macro_path'] ); // 组装大图路径
				$subproductlist [$i] ['micro_path'] = assemblepath ( $subproductlist [$i] ['micro_path'] ); // 组装小图路径
				unset($subproductlist [$i] ['html_description']); // html字段在解析json的时候可能会出错（用户不规则输入）
				unset($subproductlist [$i] ['material']); // 用户乱输商品规格质地
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'productlist' => $subproductlist
				),
				'nextstart' => $newnextstart, // 下一页开始的时候
				'totalcount' => $totalcount // 商品总数量
		);
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * 商品详情控制器。
	 */
	public function productDetail() {
		$product_id = I ( 'pid' ); // 接收商品编号
		if (empty ( $product_id )) {
			$this->error ( "商品参数输入错误！" ); // 如果没有接收到商品编号，直接显示商品不存在
		}
		
		// 查询商品信息
		$subpromap = array (
				'product_id' => $product_id,
				'subbranch_id' => $this->sid,
				'is_del' => 0
		);
		#$subprolist = M ( 'subbranch_product_sku' )->where ( $subpromap )->order ( 'size_order asc' )->select (); // 尝试从分店商品sku视图中找出商品FAB信息与sku信息（不用查询2次），尺码有序
		$subprolist = M ( 'subbranch_product_sku_new' )->where ( $subpromap )->group('sub_sku_id')->order ( 'size_order asc' )->select (); // 尝试从分店商品sku视图中找出商品FAB信息与sku信息（不用查询2次），尺码有序
		if (! $subprolist) {
			$this->error ( "该商品不存在或已下架！" ); // 如果没有接收到商品编号，直接显示商品不存在
		}
		
		// 处理商品FAB信息
		$subproinfo = $subprolist [0]; // FAB信息只取一条即可
		$subproinfo ['add_time'] = timetodate ( $subproinfo ['add_time'] );
		$subproinfo ['latest_modify'] = timetodate ( $subproinfo ['latest_modify'] );
		$subproinfo ['onshelf_time'] = timetodate ( $subproinfo ['onshelf_time'] );
		$subproinfo ['macro_path'] = assemblepath ( $subproinfo ['macro_path'] );
		$subproinfo ['micro_path'] = assemblepath ( $subproinfo ['micro_path'] );
		$subproinfo ['preview_images'] = $this->extractProductImage ( $product_id ); // 组装相册预览图片
		
		// 处理商品SKU信息
		$skuresult = $this->handleProductSKU ( $subprolist );
		$subproinfo ['skutotalnum'] = $skuresult ['skutotalnum'];
		$subproinfo ['skulist'] = jsencode ( $skuresult ['skulist'] );
		$subproinfo ['sizelist'] = jsencode ( $skuresult ['sizelist'] );
		$subproinfo ['colorlist'] = jsencode ( $skuresult ['colorlist'] );
		
		// 处理商品评论数量
		$nextstart = 0; // 从第一条开始请求
		$perpage = 5; // 商品详情页面最多加载5条评论（剩余的评论跳转评论列表看）
		$commentlist = $this->getProCommentByPage ( $this->sid, $product_id, $nextstart, $perpage, true ); // 加载商品评论
		
		// 推送商品信息
		$this->pinfo = $subproinfo;
		$this->commentlist = jsencode ( $commentlist ); // 商品评论数量
		$this->display ();
	}
	
	/**
	 * 单独抽取商品Slider图片。
	 * @param string $product_id 商品编号
	 * @return string $imagestring 组装好后图片的字符串
	 */
	private function extractProductImage($product_id = '') {
		$imagestring = "";
		$productmap = array (
				'product_id' => $product_id, // 商品编号
				'is_del' => 0
		);
		$imagelist = M ( 'productimage' )->where ( $productmap )->order ( "add_time asc" )->select ();
		$imagenum = count ( $imagelist ); // 计算图片数量
		for($i = 0; $i < $imagenum; $i ++) {
			$imagelist [$i] ['macro_path'] = assemblepath ( $imagelist [$i] ['macro_path'], true ); // 组装绝对路径
			if (empty ( $imagestring )) {
				$imagestring = $imagelist [$i] ['macro_path']; // 第一张图片
			} else {
				$imagestring .= "^" . $imagelist [$i] ['macro_path']; // 不是第一张图片
			}
		}
		return $imagestring;
	}
	
	/**
	 * 处理商品sku信息。
	 * @param array $skulist 商品的sku列表信息
	 * @return array $skuhandleinfo 处理完的sku信息
	 * @property string skuinfo sku信息
	 * @property string skusize 尺码规格
	 * @property string skucolor 颜色
	 */
	public function handleProductSKU($skulist = NULL) {
		$finalskuinfo = array (); // 函数返回结果
		$skutotalnum = 0; // 所有sku商品数量之和
		$skuinfolist = array (); // 最终处理的sku数组
		$sizelist = array (); // 尺寸规格数组
		$colorlist = array (); // 颜色数组
		$listnum = count ( $skulist ); // 计算sku规格数量
		for($i = 0; $i < $listnum; $i ++) {
			$size = $skulist [$i] ['sku_size']; // 取出尺寸
			if ($skulist [$i] ['product_type'] == 2) {
				// 如果是服装的话，就用sku的颜色（这里做一个商品类别的sku区分，2015/05/12 01:29:25）
				$color = $skulist [$i] ['sku_color']; // 取出颜色
			} else {
				// 如果是非服装，日常商品的话，就用默认颜色
				$color = "默认"; // 默认颜色
			}
			if (! in_array ( $size, $sizelist )) {
				array_push ( $sizelist, $size ); // 将新的规格加入规格数组中
			}
			if (! in_array ( $color, $colorlist )) {
				array_push ( $colorlist, $color ); // 将新的颜色加入颜色数组中
			}
			$singleskuinfo = array (
					'tag' => $color . "-" . $size, // 颜色-尺码
					'id' => $skulist [$i] ['sub_sku_id'], // sku编号
					'size' => $size, // 尺寸规格
					'color' => $color, // 颜色
					'count' => $skulist [$i] ['sub_sku_storage_left'], // sku剩余商品数量
					'price' => $skulist [$i] ['current_price'], // 当前sku价格
			);
			array_push ( $skuinfolist, $singleskuinfo ); // 将sku信息加入sku数组中
			$skutotalnum += $skulist [$i] ['sub_sku_storage_left']; // 总的数量叠加
		}
		$finalskuinfo ['skutotalnum'] = $skutotalnum; // sku数量
		$finalskuinfo ['skulist'] = $skuinfolist; // sku信息
		$finalskuinfo ['sizelist'] = $sizelist; // 尺码规格列表
		$finalskuinfo ['colorlist'] = $colorlist; // 颜色列表
		return $finalskuinfo;
	}
	
	/**
	 * 单商品的相册图片路径组装函数（自动补足详情图片为滚动图片，由商家提议图片详情比例不好）。
	 * @param string $htmldescription 需要拆解的html格式语言
	 * @param string $defaultspilt 需要组装的符号
	 * @return string $imagepathassembled 组装好的图片路径
	 */
	private function singleAlbumPreview($htmldescription = '', $defaultspilt = '^') {
		$imagepathassembled = ""; // 最终组装图片的结果
		if (! empty ( $htmldescription )) {
			$maxcount = 5; // 定义最多显示5张slider
			$k = 0; // 当前的图片数量
			while ( strlen ( $htmldescription ) > 0 && $k < $maxcount) {
				$htmldescription = "prefix" . $htmldescription; // 增加prefix
				$start = stripos ( $htmldescription, "<img src=" ); // Step1：找到img标签开始
				if ($start) {
						
					$firstend = stripos ( $htmldescription, "\"", $start ); // Step2：找到<img src="第一个引号的位置
					$secondend = stripos ( $htmldescription, "\"", $firstend + 1 ); // Step3：找到src=""第二个引号的位置
					$final = substr ( $htmldescription, $firstend + 1, $secondend - $firstend - 1 ); // Step4：切割出图片的路径
						
					$exist = stripos ( $final, 'ttp://' );
						
					if ($exist) {
						$weactstart = stripos ( $final, "/weact" ); // 特别注意，如果没有/weact，则应该找/Updata文件夹，或者自己拼接/weact项目名称！2014/09/17 14:12:25
						$final = substr ( $final, $weactstart );
					}
					$final = 'http://www.we-act.cn' . $final;
						
					if (strlen ( $imagepathassembled ) < 1) {
						$imagepathassembled .= $final;
					} else {
						$imagepathassembled .= $defaultspilt . $final; // 分隔符
					}
						
					$htmldescription = substr ( $htmldescription, $secondend + 1 );
					
					$k += 1; // 图片数量+1
				} else {
					break;
				}
			}
		}
		return $imagepathassembled;
	}
	
	/**
	 * 商品评价视图。
	 */
	public function comment() {
		// 检测商品参数
		$product_id = I ( 'pid', '' ); // 接收要查看的商品参数
		if (empty ( $product_id )) {
			$this->error ( "商品参数错误！" );
		}
		
		// 检查总店是否还出售这件商品（分店没权卖没关系，只要总店有这件商品即可，如果总店也删除了，直接说商品不存在）
		$productmap = array (
				'product_id' => $product_id,
				'is_del' => 0
		);
		$productinfo = M ( 'product' )->where ( $productmap )-> find (); // 查找商品信息
		if (! $productinfo) {
			$this->error ( "您要查看的商品不存在或已被下架！" );
		}
		
		// 通过检查，查询商品的评论信息
		$nextstart = 0; // 从第一条开始请求
		$perpage = 10; // 默认每页加载10条数据
		$commentlist = $this->getProCommentByPage ( $this->sid, $product_id, $nextstart, $perpage, true ); // 加载商品评论
		
		// 推送数据到页面上
		$this->pinfo = $productinfo; // 推送商品信息（包括商品编号）
		$this->commentlist = jsencode ( $commentlist ); // json压缩数据
		$this->display ();
	}
	
	/**
	 * ajax请求商品评论处理函数。
	 */
	public function queryProductComment() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		// 接收ajax参数
		$product_id = I ( 'pid', '' ); // 接收要查看的商品编号
		$nextstart = I ( 'nextstart', 0 ); // 下一页开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getProCommentByPage ( $this->sid, $product_id, $nextstart, $perpage ); // 分页读取商品评价信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取商品评价信息函数。
	 * 特别注意：因为同一件商品，可以在不同店铺售卖，而褒贬不一，所以这里商品评价还带上分店编号（防止出现因评价正义导致店铺之间关系不和谐）。
	 * @param string $subbranch_id 店铺编号
	 * @param string $product_id 商品编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getProCommentByPage($subbranch_id = '', $product_id = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$pcommenttable = M ( 'product_comment_view' ); 				// 实例化表结构或视图结构
		$orderby = "comment_time desc"; 							// 定义要排序的方式（每个表都不一样）
		$pcommentlist = array (); 									// 本次请求的数据
			
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'subbranch_id' => $subbranch_id,
				'product_id' => $product_id, 						// 当前商品
				'is_del' => 0 										// 没有被删除的
		);
		$totalcount = $pcommenttable->where ( $querymap )->count (); 	// 计算总数量
	
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
			
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
	
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
	
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
	
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$pcommentlist = $pcommenttable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询导购服务评价信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$pcommentlist [$i] ['comment_time'] = timetodate ( $pcommentlist [$i] ['comment_time'] );
				$pcommentlist [$i] ['head_img_url'] = assemblepath ( $pcommentlist [$i] ['head_img_url'] );
			}
		}
	
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'pcommentlist' => $pcommentlist
				),
				'nextstart' => $newnextstart, // 下一次请求记录开始位置
				'totalcount' => $totalcount // 本店铺该商品总的评论数
		);
	
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ajax获得同类商品推荐的处理函数。
	 */
	public function recommendProduct() {
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		}
		
		// 接收参数，准备返回值
		$product_id = I ( 'pid' ); // 接收商品参数
		$nav_id = I ( 'nid' ); // 接收导航编号
		$finalresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！" 
		);
		
		// 检测参数完整性
		if (empty ( $product_id ) || empty ( $nav_id )) {
			$finalresult ['errCode'] = 10002;
			$finalresult ['errMsg'] = "要推荐同类的商品编号或导航编号不能为空！";
			$this->ajaxReturn ( $finalresult );
		}
		
		$nextstart = 0; // 从0条开始读
		$perpage = 10; // 默认每页10条数据
		$finalresult = $this->queryRecommendByPage ( $this->sid, $product_id, $nav_id, $nextstart, $perpage ); // 查询商品的同类推荐
		
		$this->ajaxReturn ( $finalresult ); // 将信息返回给前台
	}
	
	/**
	 * 分页请求推荐商品函数。
	 * 设计思路：
	 * 1、如果商品的导航编号不空且不为-1，则直接查询同导航下的商品，随机取几件；
	 * 2、如果导航编号为-1（未分类）或者该导航下没有其他商品，直接随机查询其他几件商品（1.0版本先这么设计，日后再从推荐搭配表里查询）。
	 * @param string $subbranch_id 分店编号
	 * @param string $product_id 商品编号
	 * @param string $nav_id 该商品目前所属的导航编号
	 * @param number $nextstart 下一页开始
	 * @param number $perpage 每页几条数据
	 * @param string $firstinit 是否页面初始化请求
	 */
	private function queryRecommendByPage($subbranch_id = '', $product_id = '', $nav_id = '',  $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$subprotable = M ( 'subbranch_product_image' ); 			// 实例化表结构或视图结构
		$orderby = "onshelf_time desc"; 							// 定义要排序的方式（每个表都不一样）
		$recommendlist = array (); 									// 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'subbranch_id' => $subbranch_id, 					// 当前店铺的
				'product_id' => array ( 'neq', $product_id ), 		// 同类推荐不是当前展示的商品
				'on_shelf' => 1, 									// 上架状态的商品
				'is_del' => 0 										// 没有被删除的
		);
		
		if (! empty ( $nav_id ) && $nav_id != "-1") {
			// 该商品有导航且不是未分类
			$querymap ['nav_id'] = $nav_id; // 搜索当前分类下的同类商品
		} else {
			// 该商品属于未分类，或其他情况
			// 就从所有商品中随机几件（1.0版本目前先这么处理，日后可在这里更改）
		}
		
		$totalcount = $subprotable->where ( $querymap )->count (); 	// 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$recommendlist = $subprotable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询同类商品推荐信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$recommendlist [$i] ['add_time'] = timetodate ( $recommendlist [$i] ['add_time'] );
				$recommendlist [$i] ['latest_modify'] = timetodate ( $recommendlist [$i] ['latest_modify'] );
				$recommendlist [$i] ['onshelf_time'] = timetodate ( $recommendlist [$i] ['onshelf_time'] );
				$recommendlist [$i] ['macro_path'] = assemblepath ( $recommendlist [$i] ['macro_path'] );
				$recommendlist [$i] ['micro_path'] = assemblepath ( $recommendlist [$i] ['micro_path'] );
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'recommendlist' => $recommendlist // 推荐商品列表
				),
				'nextstart' => $newnextstart, // 下一次请求记录开始位置
				'totalcount' => $totalcount // 本店铺同类推荐商品总数
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
}
?>