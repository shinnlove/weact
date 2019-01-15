<?php
/**
 * 购物车控制器。
 * @author Administrator
 * CreateTime:2015/04/29 14:02:25.
 */
class CartAction extends LoginMallAction {
	
	/**
	 * 购物车视图。
	 */
	public function shoppingCart() {
		// 查询购物车信息
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$cartmap = array (
				'e_id' => $this->eid, 				// 当前商家下
				//'subbranch_id' => $this->sid, 	// 当前分店下，暂不用限制，多购物车
				'mall_type' => 2, 					// 只查询微猫商城的购物车
				'customer_id' => $customer_id, 		// 当前顾客的
				'is_del' => 0, 						// 没有被删除的
		);
		$cartlist = M ( 'subbranch_cart' )->where ( $cartmap )->order ( 'add_time desc' )->select (); // 从分店购物车视图中查询出商品
		
		// 处理购物车的商品信息，并分到不同分店的购物车里
		$subbranchcartlist = array (); // 分店列表
		$listnum = count ( $cartlist ); // 统计购物车商品数量
		for($i = 0; $i < $listnum; $i ++) {
			$subbranch_id = $cartlist [$i] ['subbranch_id']; // 取当前分店编号
			if (! array_key_exists ( $subbranch_id, $subbranchcartlist )) {
				$subbranchcartlist [$subbranch_id] = array (); // 开辟这样的分店购物车数组
			}
			// 处理商品信息和图片路径
			$cartlist [$i] ['add_time'] = timetodate ( $cartlist [$i] ['add_time'] );
			$cartlist [$i] ['latest_modify'] = timetodate ( $cartlist [$i] ['latest_modify'] );
			$cartlist [$i] ['onshelf_time'] = timetodate ( $cartlist [$i] ['onshelf_time'] );
			$cartlist [$i] ['macro_path'] = assemblepath ( $cartlist [$i] ['macro_path'] );
			$cartlist [$i] ['micro_path'] = assemblepath ( $cartlist [$i] ['micro_path'] );
			
			array_push ( $subbranchcartlist [$subbranch_id], $cartlist [$i] ); // 将购物车信息加到该分店购物车下
		}
		
		// 推送购物车信息到前台
		$cartinfo ['cartlist'] = $subbranchcartlist;
		$jsondata = jsencode ( $cartinfo );
		$jsonresult = str_replace ( '"', '\\"', $jsondata );
		$this->cartinfo = $jsonresult;
		$this->display ();
	}
	
}
?>