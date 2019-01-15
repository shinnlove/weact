<?php
/**
 * 新改版的购物车控制器，用2015年全新的方法，没有使用任何2014年版本的购物车。
 * @author 赵臣升。
 * CreateTime:2015/06/25 14:04:25.
 */
class CartAction extends MobileLoginAction {
	
	/**
	 * 最新版购物车页面视图。
	 * 该段代码已经适应云总店mall_type=1的调整。
	 */
	public function shoppingCart() {
		// 查询购物车信息
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$cartmap = array (
				'e_id' => $this->einfo ['e_id'], 	// 当前商家下
				'mall_type' => 1, 					// 云总店
				'customer_id' => $customer_id, 		// 当前顾客的
				'is_del' => 0, 						// 没有被删除的
		);
		$cartlist = M ( 'cart_product_image' )->where ( $cartmap )->order ( 'add_time desc' )->select (); // 从总店购物车视图中查询出商品
		$cartnum = count ( $cartlist ); // 计算购物车数量
		for($i = 0; $i < $cartnum; $i ++) {
			$cartlist [$i] ['add_time'] = timetodate ( $cartlist [$i] ['add_time'] );
			$cartlist [$i] ['latest_modify'] = timetodate ( $cartlist [$i] ['latest_modify'] );
			$cartlist [$i] ['onshelf_time'] = timetodate ( $cartlist [$i] ['onshelf_time'] );
			$cartlist [$i] ['macro_path'] = assemblepath ( $cartlist [$i] ['macro_path'] );
			$cartlist [$i] ['micro_path'] = assemblepath ( $cartlist [$i] ['micro_path'] );
		}
		$cartinfo ['cartlist'] = $cartlist;
		$jsondata = json_encode ( $cartinfo );
		$this->cartinfojson = $jsondata;
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
}
?>