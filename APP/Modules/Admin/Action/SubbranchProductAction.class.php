<?php
/**
 * 分店商品管理控制器，主要处理分店商品的分拨。
 * @author 胡睿
 * CreateTime:2015/05/18 21:54:36.
 */
class SubbranchProductAction extends PCViewLoginAction {
	
	/**
	 * 分发商品视图页面。
	 */
	public function distributeProduct() {
		// 接收商品编号（要操作的商品编号不能为空）
		$product_id = I ( 'pid' ); // 接收商品编号
	
		// 校验$pid合法性(查找该e_id下该商品是否存在)
		$proMap = array (
				'product_id'=> $product_id,
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$productinfo = M ( "product" )->where ( $proMap )->find ();
	
		if (! $productinfo) $this->error ( "该商品不存在" ); // 该e_id下不存在此编号的商品
		// 如果存在该商品
		// 1、获取该e_id下所有分店subbranch_id信息
		$subMap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$subbranchlist = M ( "subbranch" )->where ( $subMap )->order ( 'subbranch_name asc' )->field ( 'subbranch_id, subbranch_name' )->select ();
		// 2、设置分店是否拥有售卖商品的权限，初始化为没有权限
		$subSet = array ();
		foreach ( $subbranchlist as $subValue ) {
			$subSet [$subValue ['subbranch_id']] ['subbranch_id'] = $subValue ['subbranch_id']; // 当前店铺的名字
			$subSet [$subValue ['subbranch_id']] ['subbranch_name'] = $subValue ['subbranch_name']; // 当前店铺的名字
			$subSet [$subValue ['subbranch_id']] ['current_sell'] = 0; // 初始化所有分店都没有权限（当前没有在卖）
			$subSet [$subValue ['subbranch_id']] ['current_storage'] = 0; // 初始化库存为0（没卖也是库存为0）
		}
		// 3、从t_subbranchproduct中读取product_id当前售卖的所有分店subbranch_id送入$proSubList
		$proSubMap = array (
				'product_id' => $product_id,
				'is_del' => 0
		);
		$proSubList = M ( "subbranchproduct" )->where ( $proSubMap )->field ( 'subbranch_id, sub_storage-sub_sell as current_storage' )->select ();
		// 4、循环$proSubList，通过hash值改变$subSet的value为1
		foreach ( $proSubList as $proSubValue ) { // 依次读取售卖某商品的分店id
			$sub_id = $proSubValue ['subbranch_id']; // 本次读取到的分店id
			// 如果恰好该分店id属于该e_id门下，则更新该分店的售卖权限
			if (isset ( $subSet [$sub_id] )) {
				$subSet [$sub_id] ['current_sell'] = 1;
				$subSet [$sub_id] ['current_storage'] = $proSubValue ['current_storage']; // 附上分店库存
			}
		}
	
		// Step3：推送给前台信息
		$this->subbranchlist = $subSet; 	// 分店商品列表
		$this->pid = $product_id; 			// 当前分发的商品列表
		$this->pinfo = $productinfo; 		// 推送商品信息
		$this->display ();
	}
	
	
	
}
?>