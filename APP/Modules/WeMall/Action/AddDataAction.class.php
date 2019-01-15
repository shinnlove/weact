<?php
/**
 * 增加数据控制器。
 * @author Shinnlove
 *
 */
class AddDataAction extends Action {
	/**
	 * 增加分店商品。
	 */
	public function addSubbranchProduct() {
		$protable = M ( 'product' ); // 商品表
		$proskutable = M ( 'productsizecolor' ); // 颜色尺寸表
		$subprotable = M ( 'subbranchproduct' ); // 分店商品表
		$subproskutable = M ( 'subbranchsku' ); // 分店颜色尺寸表
		$proimgtable = M ( 'productimage' ); // 商品图片表
		$subprolist = array (); // 要插入的分店商品信息
		$subproskulist = array (); // 要插入的分店sku信息
		
		$e_id = "201406261550250006"; // 商家编号
		$subbranch_id = "070b107fd7ecae417e7a2266ebd7bc9c"; // 分店编号
		$emap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$productlist = $protable->where ( $emap )->select ();
		
		$totalnum = count ( $productlist );
		
		for($i = 0; $i < $totalnum; $i ++) {
			$subprolist [$i] = array (
					'sub_pro_id' => md5 ( uniqid ( rand (), true ) ),
					'product_id' => $productlist [$i] ['product_id'],
					'subbranch_id' => $subbranch_id,
					'sub_storage' => $productlist [$i] ['storage_amount'],
					'sub_sell' => 0,
					'sub_storage_warn' => $productlist [$i] ['storage_warn'],
					'browsed_amount' => $productlist [$i] ['browsed_amount'],
					'followed_amount' => $productlist [$i] ['followed_amount'],
					'recommended_amount' => $productlist [$i] ['recommended_amount'],
					'add_time' => time (), // 取当前时间
					//'on_shelf' => 0, // 当前商品添加后不上架
					'on_shelf' => 1,
					'onshelf_time' => time (),
					'is_feature' => $productlist [$i] ['is_feature'],
					'is_new' => $productlist [$i] ['is_new'],
					'is_preferential' => $productlist [$i] ['is_preferential']
			);
		}
		
		for($i = 0; $i < $totalnum; $i ++) {
			$subproid = "";
			$product_id = "";
			$temppromap = array ();
			
			$subproid = $subprolist [$i] ['sub_pro_id'];
			$product_id = $subprolist [$i] ['product_id']; // 取出商品编号
			$temppromap = array (
					'product_id' => $product_id,
					'is_del' => 0
			);
			$tempskulist = $proskutable->where ( $temppromap )->select ();
			if ($tempskulist) {
				$skucount = count ( $tempskulist );
				for($j = 0; $j < $skucount; $j ++) {
					$singlesku = array (
							'sub_sku_id' => md5 ( uniqid ( rand (), true ) ),
							'sub_pro_id' => $subproid,
							'sku_color' => $tempskulist [$j] ['product_color'],
							'sku_size' => $tempskulist [$j] ['product_size'],
							'size_order' => $tempskulist [$j] ['size_order'],
							'subsku_storage' => $tempskulist [$j] ['storage_amount'],
							'subsku_sell' => $tempskulist [$j] ['sell_amount']
					);
					array_push ( $subproskulist, $singlesku );
				}
			}
		}
		
		$addparent = $subprotable->addAll ( $subprolist );
		$addchild = $subproskutable->addAll ( $subproskulist );
		if ($addparent && $addchild) {
			p('success');die;
		} else {
			p('fail');die;
		}
		p($subprolist);p($subproskulist);die;
	}
	
	/**
	 * 表结构改动后为product设置导航名称。
	 */
	public function setNavName() {
		$updatenum = 0;
		$navtable = M ( 'navigation' );
		$protable = M ( 'product' );
		// 记得先0再1，删除商品也要更新导航名称的
		$promap ['is_del'] = 0;
		//$promap ['is_del'] = 1;
		$productlist = $protable->where ( $promap )->select ();
		
		$totalnum = count ( $productlist ); // 计算总数量
		for($i = 0; $i < $totalnum; $i ++) {
			if ($productlist [$i] ['nav_id'] != "-1") {
				$navmap = array (
						'nav_id' => $productlist [$i] ['nav_id'],
						//'is_del' => 0
				);
				$navinfo = $navtable->where ( $navmap )->find ();
				if ($navinfo && $navinfo ['nav_name'] != $productlist [$i] ['nav_name']) {
					$productlist [$i] ['nav_name'] = $navinfo ['nav_name'];
					$updatenum += $protable->save ( $productlist [$i] );
				}
			}
		}
		p("ok, find " . $totalnum . "records, and update " . $updatenum . " records!");die;
	}
	
	/**
	 * 测试emoji表情。
	 */
	public function testEmoji() {
		$originaltext = "哈哈😊😛大笨蛋";
		$tempstr = json_encode ( $originaltext );
		$newtext = preg_replace ( "#(\\\ue[0-9a-f]{3})#ie", "addslashes('\\1')", $tempstr );
		$handletext = json_decode ( $newtext, true );
		p($originaltext);p($newtext);p($handletext);die;
	}
	
	/**
	 * 批量生成某商家的商品二维码。
	 */
	public function addProductQRCode() {
		// 初始化配置变量
		$productview = M ( 'product_image' ); // 实例化商品视图
		$e_id = "201405291912250003"; // 准备商家编号
		$baseurl = "http://www.we-act.cn/weact/WeMall/QRCode/product/pid/"; // 要写入的二维码地址
		$createsum = 0; // 本次总添加二维码数量
		
		// 查询商品列表
		$productmap = array (
				'e_id' => $e_id, // 当前商家下的
				'is_del' => 0 // 没有被删除的
		);
		$productlist = $productview->where ( $productmap )->select (); // 查询当前商家下的所有商品
		$productcount = count ( $productlist ); // 计算总得商品数量
		
		for($i = 0; $i < $productcount; $i ++) {
			$usetype = "product"; // 二维码用途，可指定product,customer,guide,subbranch,nativepay等多种
			$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/productcode/" . $productlist [$i] ['product_id'] . "/"; // 保存路径按导航、商品编号来存放，必须以./相对路径开头
			$saveqrname = $productlist [$i] ['product_id']; // 是否指定二维码文件名，默认空就用md5生成文件名
			$logopathname = $_SERVER ['DOCUMENT_ROOT'] . assemblepath ( $productlist [$i] ['macro_path'] ); // 默认用第一张图片作为要嵌入作为logo的图片（相对路径+文件名与后缀）
			$url = $baseurl . $productlist [$i] ['product_id']; // 商品二维码URL地址
			
			import ( 'Class.Common.phpqrcode.weactqrcode', APP_PATH, '.php' ); // 载入WeAct的二维码类
			$wqr = new WeActQRCode (); // 生成微动二维码类对象
			$createresult = $wqr->createQRCode ( $e_id, $url, $usetype, $saveqrpath, $saveqrname, $logopathname ); // 调用二维码函数生成二维码并返回生成结果
			if ($createresult ['errCode'] == 0) {
				$createsum += 1; // 总添加数量+1
			}
		}
		p ( "本次共生成商品二维码" . $createsum . "张。" );die;
	}
	
}
?>