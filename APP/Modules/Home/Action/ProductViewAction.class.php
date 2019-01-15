<?php
class ProductViewAction extends MobileGuestAction {
	
	/**
	 * 展示商品列表，微商城视图。
	 */
	public function productList() {
		$this->nav_id = I ( 'nav_id' ); // 获取导航信息
		$navinfo = array (
				'e_id' => $this->einfo ['e_id'], // 必须参数
				'nav_id' => $this->nav_id, // 可选参数
				'searchcondition' => I ( 'searchcondition' ), // 可选参数，尝试接收搜索条件
				'searchcontent' => I ( 'searchcontent' ), // 可选参数，尝试接收搜索内容
				'Type' => I ( 'Type' ) 
		); // $navinfo有两个必选参数，有三个供查询的可选参数
		// Step1：多态查找模板并统一赋值
		$tpl_indexpath = strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ); 	// PHP自带函数，转为小写
		$mobilecommon = A ( 'Home/MobileCommon' ); 												// 实例化Home分组下，名为MobileCommon的控制器，创建其对象$mobilecommon
		$this->pageinfo = $mobilecommon->selectTpl ( $navinfo, $tpl_indexpath );
		unset ( $mobilecommon ); // 注销此对象释放内存
		if ($navinfo ['Type'] == 1) {
			$this->searchcondition = $navinfo ['searchcondition']; // 推送查询条件到页面支持刷新
			$this->searchcontent = $navinfo ['searchcontent']; // 推送查询条件到页面支持刷新
		}
		$this->display ( $this->pageinfo ['template_realpath'] );
	}
	
	/**
	 * 供页面顶部导航查询购物车数量的。
	 */
	public function queryCartNumber() {
		// 查询当前客户的购物车商品数量
		$cartmap = array (
				'e_id' => $this->einfo ['e_id'],
				'mall_type' => 1, // 总店购物车只查询总店的（不要造成购物车误显示数量）
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$cartcount = M ( 'cart' )->where ( $cartmap )->count ();
		// 返回给前台信息
		$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => 'ok',
				'data' => array (
						'cart_count' => $cartcount
				)
		);
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * ==========商品详情改版===========
	 */
	
	/**
	 * 商品详情。
	 */
	public function productShow() {
		$product_id = I ( 'product_id' ); // 接收商品编号
		if (empty ( $product_id )) {
			$this->error ( "商品参数输入错误！" ); // 如果没有接收到商品编号，直接显示商品不存在
		}
		
		// 查询商品信息
		$promap = array (
				'product_id' => $product_id,
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$prolist = M ( 'product_sku_new' )->where ( $promap )->group('sizecolor_id')->order ( 'size_order asc' )->select (); // 尝试从总店商品sku视图中找出商品FAB信息与sku信息（不用查询2次），尺码有序
		if (! $prolist) {
			$this->error ( "该商品不存在或已下架！" ); // 如果没有查询到商品的SKU信息，直接显示商品不存在
		}
		
		// 处理商品FAB信息
		$proinfo = $prolist [0]; // FAB信息只取一条即可
		$proinfo ['add_time'] = timetodate ( $proinfo ['add_time'] );
		$proinfo ['latest_modify'] = timetodate ( $proinfo ['latest_modify'] );
		$proinfo ['onshelf_time'] = timetodate ( $proinfo ['onshelf_time'] );
		$proinfo ['macro_path'] = assemblepath ( $proinfo ['macro_path'] );
		$proinfo ['micro_path'] = assemblepath ( $proinfo ['micro_path'] );
		$proinfo ['preview_images'] = $this->extractProductImage ( $product_id ); // 组装相册预览图片
		
		// 处理商品SKU信息
		$skuresult = $this->handleProductSKU ( $prolist );
		$proinfo ['skutotalnum'] = $skuresult ['skutotalnum'];
		$proinfo ['skulist'] = jsencode ( $skuresult ['skulist'] );
		$proinfo ['sizelist'] = jsencode ( $skuresult ['sizelist'] );
		$proinfo ['colorlist'] = jsencode ( $skuresult ['colorlist'] );
		
		// 推送商品信息
		$this->pinfo = $proinfo;
		
		// Step3：查找该商家下所有门店并选择一家
		$subbranch_id = ""; // 该商家一家分店的编号
		$subbranchmap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$subbranchlist = M ( 'subbranch' )->where ( $subbranchmap )->order ( 'add_time desc' )->limit ( 1 )->select (); // 找到分店列表中第一条
		if ($subbranchlist) {
			$subbranch_id = $subbranchlist [0] ['subbranch_id']; // 分店编号
		}
		$this->sid = $subbranch_id; // 推送分店编号
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	
	/**
	 * 商品详情(积分商城)。
	 */
	public function scoreProductShow() {
		$product_id = I ( 'product_id' ); // 接收商品编号
		if (empty ( $product_id )) {
			$this->error ( "商品参数输入错误！" ); // 如果没有接收到商品编号，直接显示商品不存在
		}
	
		// 查询商品信息
		$promap = array (
				'product_id' => $product_id,
				'e_id' => $this->einfo ['e_id'],
				'member_level' => I('member_level'),
				'is_del' => 0
		);
		$prolist = M ( 'score_product_sku' )->where ( $promap )->order ( 'size_order asc' )->select (); // 尝试从总店商品sku视图中找出商品FAB信息与sku信息（不用查询2次），尺码有序
		if (! $prolist) {
			$this->error ( "该商品不存在或已下架！" ); // 如果没有查询到商品的SKU信息，直接显示商品不存在
		}
	
		// 处理商品FAB信息
		$proinfo = $prolist [0]; // FAB信息只取一条即可
		$proinfo ['add_time'] = timetodate ( $proinfo ['add_time'] );
		$proinfo ['latest_modify'] = timetodate ( $proinfo ['latest_modify'] );
		$proinfo ['onshelf_time'] = timetodate ( $proinfo ['onshelf_time'] );
		$proinfo ['macro_path'] = assemblepath ( $proinfo ['macro_path'] );
		$proinfo ['micro_path'] = assemblepath ( $proinfo ['micro_path'] );
		$proinfo ['preview_images'] = $this->extractProductImage ( $product_id ); // 组装相册预览图片
	
		// 处理商品SKU信息
		$skuresult = $this->handleProductSKU ( $prolist );
		$proinfo ['skutotalnum'] = $skuresult ['skutotalnum'];
		$proinfo ['skulist'] = jsencode ( $skuresult ['skulist'] );
		$proinfo ['sizelist'] = jsencode ( $skuresult ['sizelist'] );
		$proinfo ['colorlist'] = jsencode ( $skuresult ['colorlist'] );
		
		//商品所属的会员等级
		
		//$proinfo ['member_level'] = $proinfo['member_level'];
		//$proinfo ['score_amount'] = $proinfo['score_amount'];
		
		// 推送商品信息
		$this->pinfo = $proinfo;
	
		// Step3：查找该商家下所有门店并选择一家
		$subbranch_id = ""; // 该商家一家分店的编号
		$subbranchmap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$subbranchlist = M ( 'subbranch' )->where ( $subbranchmap )->order ( 'add_time desc' )->limit ( 1 )->select (); // 找到分店列表中第一条
		if ($subbranchlist) {
			$subbranch_id = $subbranchlist [0] ['subbranch_id']; // 分店编号
		}
		$this->sid = $subbranch_id; // 推送分店编号
	
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		//p($this->pinfo);die;
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
			$imagelist [$i] ['macro_path'] = assemblepath ( $imagelist [$i] ['macro_path'] ); // 组装绝对路径
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
			$size = $skulist [$i] ['product_size']; // 取出尺寸
			if ($skulist [$i] ['product_type'] == 2) {
				// 如果是服装的话，就用sku的颜色（这里做一个商品类别的sku区分，2015/05/12 01:29:25）
				$color = $skulist [$i] ['product_color']; // 取出颜色
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
					'id' => $skulist [$i] ['sizecolor_id'], // sku编号
					'size' => $size, // 尺寸规格
					'color' => $color, // 颜色
					'count' => $skulist [$i] ['sku_storage_left'], // sku剩余商品数量
					'price' => $skulist [$i] ['current_price'], // 当前sku价格
			);
			array_push ( $skuinfolist, $singleskuinfo ); // 将sku信息加入sku数组中
			$skutotalnum += $skulist [$i] ['sku_storage_left']; // 总的数量叠加
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
	
}
?>