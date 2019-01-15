<?php
/**
 * 扫描二维码后跳转地址处理控制器，该控制器目前处理商品二维码的扫描。
 * @author 赵臣升。
 * CreateTime：2015/05/11 20:14:36.
 */
class QRCodeAction extends Action {
	
	/**
	 * 扫码显示商品信息。
	 */
	public function product() {
		// Step1：处理参数
		$product_id = I ( 'pid', '' ); // 接收商品参数
		if (empty ( $product_id )) {
			$this->error ( "商品参数错误，无法查看信息！" );
		}
		
		// Step2：查询商品信息
		$productmap = array (
				'product_id' => $product_id
		);
		$productinfo = M ( 'product_image' )->where ( $productmap )->find (); // 尝试从商品总店视图里查询
		if (! $productinfo) {
			$this->error ( "该商品不存在！" ); // 如果没有查询到相关商品，直接显示商品不存在
		}
		
		// 处理商品FAB信息
		$productinfo ['add_time'] = timetodate ( $productinfo ['add_time'] ); // 格式化添加商品的时间
		$productinfo ['latest_modify'] = timetodate ( $productinfo ['latest_modify'] ); // 格式化最后一次修改商品的时间
		$productinfo ['onshelf_time'] = timetodate ( $productinfo ['onshelf_time'] ); // 格式化上架时间
		$productinfo ['qr_code'] = assemblepath ( $productinfo ['qr_code'] ); // 组装二维码路径
		$productinfo ['macro_path'] = assemblepath ( $productinfo ['macro_path'] ); // 组装大图路径
		$productinfo ['micro_path'] = assemblepath ( $productinfo ['micro_path'] ); // 组装小图路径
		$productinfo ['preview_images'] = assemblepath ( $productinfo ['macro_path'], true ) . "^" . $this->singleAlbumPreview ( $productinfo ['html_description'] ); // 组装相册预览图片
		if ($productinfo ['is_del'] == 1) {
			$productinfo ['on_shelf'] = 0; // 如果商品被删除，统一认为商品已经被下架
		}
		//$productinfo ['on_shelf'] = 0; // 测试阶段临时统一对商品做出下架处理（2015/05/11 21:24:36）
		// 推送商品信息
		$this->pid = $product_id; 		// 商品编号
		$this->pinfo = $productinfo; 	// 商品信息
		
		// 处理商品评论数量
		$nextstart = 0; // 从第一条开始请求
		$perpage = 5; // 商品详情页面最多加载5条评论（剩余的评论跳转评论列表看）
		$commentlist = $this->getProCommentByPage ( $product_id, $nextstart, $perpage, true ); // 加载商品评论
		$this->commentlist = jsencode ( $commentlist ); // 商品评论数量
		
		// 尝试接收店铺参数，如果有的话（没有就随机一家店铺，因为要进入微猫商城可能需要通行证）
		$subbranch_id = I ( 'sid' ); // 尝试接收分店编号
		if (empty ( $subbranch_id )) {
			// 如果没有接收到，随机一家分店（出售该商品的分店）查看该商品的评价
			$productmap = array (
					'product_id' => $product_id,
					'is_del' => 0
			);
			$subbranchproductlist = M ( 'subbranchproduct' )->where ( $productmap )->limit ( 1 )->select (); // 选择一家分店即可
			if ($subbranchproductlist) {
				$subbranch_id = $subbranchproductlist [0] ['subbranch_id']; // 取该分店的编号
			} else {
				$subbranch_id = "-1"; // 如果分店都不出售该商品，去总店
			}
		}
		$this->sid = $subbranch_id; 	// 推送店铺编号（随机选取的）
		
		$this->display ();
	}
	
	/**
	 * 单商品的相册图片路径组装函数。
	 * @param string $htmldescription 需要拆解的html格式语言
	 * @param string $defaultspilt 需要组装的符号
	 * @return string $imagepathassembled 组装好的图片路径
	 */
	private function singleAlbumPreview($htmldescription = '', $defaultspilt = '^') {
		$imagepathassembled = ""; // 最终组装图片的结果
		if (! empty ( $htmldescription )) {
			$maxcount = 5; // 定义最多显示8张slider
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
	 * 分页读取某商品在商家所有分店的评价信息函数。
	 * 特别注意：这里读取的是该商家下该商品所有的口碑，而不是某个店铺的，在店铺内部可以看到具体自己店铺的口碑。
	 * （分店内部的评论）特别注意：因为同一件商品，可以在不同店铺售卖，而褒贬不一，所以这里商品评价还带上分店编号（防止出现因评价正义导致店铺之间关系不和谐）。
	 * @param string $product_id 商品编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getProCommentByPage($product_id = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$pcommenttable = M ( 'product_comment_view' ); 				// 实例化表结构或视图结构
		$orderby = "comment_time desc"; 							// 定义要排序的方式（每个表都不一样）
		$pcommentlist = array (); 									// 本次请求的数据
			
		// Step1：定义查询条件并计算总数量
		$querymap = array (
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
	
}
?>