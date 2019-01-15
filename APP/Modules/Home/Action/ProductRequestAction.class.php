<?php
/**
 * 商品请求控制器。
 * @author 赵臣升。
 * CreateTime:2015/07/05 14:49:36.
 */
class ProductRequestAction extends MobileGuestRequestAction {
	
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
		$finalresult = $this->queryRecommendByPage ( $this->einfo ['e_id'], $product_id, $nav_id, $nextstart, $perpage ); // 查询商品的同类推荐
	
		$this->ajaxReturn ( $finalresult ); // 将信息返回给前台
	}
	
	/**
	 * 分页请求推荐商品函数。
	 * 设计思路：
	 * 1、如果商品的导航编号不空且不为-1，则直接查询同导航下的商品，随机取几件；
	 * 2、如果导航编号为-1（未分类）或者该导航下没有其他商品，直接随机查询其他几件商品（1.0版本先这么设计，日后再从推荐搭配表里查询）。
	 * @param string $e_id 总店编号
	 * @param string $product_id 商品编号
	 * @param string $nav_id 该商品目前所属的导航编号
	 * @param number $nextstart 下一页开始
	 * @param number $perpage 每页几条数据
	 * @param string $firstinit 是否页面初始化请求
	 */
	private function queryRecommendByPage($e_id = '', $product_id = '', $nav_id = '',  $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$subprotable = M ( 'product_image' ); 			// 实例化表结构或视图结构
		$orderby = "onshelf_time desc"; 							// 定义要排序的方式（每个表都不一样）
		$recommendlist = array (); 									// 本次请求的数据
	
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $e_id, 									// 当前商家的
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