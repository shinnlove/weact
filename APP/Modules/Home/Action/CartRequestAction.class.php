<?php
/**
 * 云总店购物车ajax请求控制器。
 * @author 赵臣升
 * CreateTime:2015/06/27 21:22:36.
 */
class CartRequestAction extends MobileLoginRequestAction {
	
	/**
	 * 用户添加购物车的ajax请求处理。
	 * 该段代码已经适应云总店mall_type=1的调整。
	 */
	public function addCart() {
		$product_id = I ( 'pid' ); 			// 商品编号
		$product_type = I ( 'ptype', 2 ); 	// 商品类型（默认服装）
		$skuid = I ( 'skuid' ); 			// 加入购物车的sku
		$amount = I ( 'count', 0 ); 		// 加入购物车的数量
		
		// 检测参数
		if (empty ( $product_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "加入购物车失败，商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $skuid )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "加入购物车失败，商品规格不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $amount )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "加入购物车失败，商品数量不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过参数检验，查询sku信息并加入购物车
		$skumap = array (
				'sizecolor_id' => $skuid,
				'is_del' => 0
		);
		$skuinfo = M ( 'productsizecolor' )->where ( $skumap )->find (); // 从总店sku表中找到sku信息
		$skucolor = $skuinfo ['product_color']; // sku的颜色
		$skusize = $skuinfo ['product_size']; // sku的尺码规格
		$skuamountleft = $skuinfo ['storage_amount'] - $skuinfo ['sell_amount']; // 计算云总店剩余的sku数量
		if ($amount > $skuamountleft) {
			// 如果要加入的数量直接比库存剩余还多，无法加入
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "要加入的SKU数量已超过当前SKU库存量，请及时刷新！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 检验当前顾客购物车中是否有相同商品，如果有，数量叠加不能超过库存；如果没有，重新向购物车里添加一条记录
		$cartid = ""; // 最终的cartid
		$carttable = M ( 'cart' ); // 购物车表
		$cartmap = array (
				'e_id' => $this->einfo ['e_id'], 	// 当前商家下
				'mall_type' => 1, 					// 云总店
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'], // 当前顾客
				'product_id' => $product_id, 		// 当前商品的（这件sku）
				'sku_id' => $skuid, 				// 特别特别注意：不同SKU千万不能在购物车算成一件商品
				'is_del' => 0
		);
		$existcartinfo = $carttable->where ( $cartmap )->find (); // 找到当前顾客可能存在的购物车信息
		if ($existcartinfo) {
			// 如果存在这样的购物车信息，检测加入后数量是否超过
			$existcartinfo ['amount'] += $amount; // 现存购物车数量+要加入的数量
			if ($existcartinfo ['amount'] > $skuamountleft) {
				// 叠加后超过SKU库存数量，则无法加入
				$this->ajaxresult ['errCode'] = 10006;
				$this->ajaxresult ['errMsg'] = "购物车中已存在当前商品SKU，加入后超过SKU库存量！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
			// 如果加入后不超过库存，允许加入，数量叠加
			$existcartinfo ['latest_modify'] = time (); // 更新购物车最后一次修改时间
			$updateresult = $carttable->save ( $existcartinfo ); // 更新回数据库
			if ($updateresult) {
				// 更新购物车成功
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
			}
			$cartid = $existcartinfo ['cart_id']; // 原来的购物车主键
		} else {
			// 如果不存在这样的购物车信息，准备加入购物车
			$cartmap ['cart_id'] = md5 ( uniqid ( rand (), true ) ); // 增加购物车主键
			$cartmap ['product_type'] = $product_type; 	// 补充商品类型
			$cartmap ['sku_id'] = $skuid; 				// SKU编号
			$cartmap ['product_size'] = $skusize; 		// SKU尺码规格
			$cartmap ['product_color'] = $skucolor; 	// SKU颜色
			$cartmap ['amount'] = $amount; 				// SKU数量
			$cartmap ['add_time'] = time (); 			// 第一次添加购物车的时间
			if (isset ( $_SESSION ['currentwechater'] [$this->einfo ['e_id']] )) {
				// 如果用户微信授权登录，补充微信openid字段
				$cartmap ['openid'] = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
			}
			$addresult = $carttable->add ( $cartmap );
			if ($addresult) {
				// 添加购物车成功
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
			}
			$cartid = $cartmap ['cart_id'];
		}
		// 如果添加成功
		if ($this->ajaxresult ['errCode'] == 0) {
			$refresh = array (
					'cart_id' => $cartid, // 购物车编号
					'is_del' => 0
			);
			$cartinfo = M ( 'cart_product_image' )->where ( $refresh )->find (); // 从分店购物车里再重新查一次
			$cartinfo ['macro_path'] = assemblepath ( $cartinfo ['macro_path'] ); // 组装下大图路径
			$cartinfo ['micro_path'] = assemblepath ( $cartinfo ['micro_path'] ); // 组装下小图路径
			// 如果添加成功，重查购物车信息
			$this->ajaxresult ['data'] = $cartinfo;
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 用户从购物车里删除商品的请求。
	 * 该段代码已经适应云总店mall_type=1的调整。
	 */
	public function deleteCart() {
		$cart_id = I ( 'cid' ); 			// 当前被删除的购物车主键
		
		// 检测参数
		if (empty ( $cart_id )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "删除失败，购物车编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 准备删除
		$cartmap = array (
				'cart_id' => $cart_id, 				// 要删除的购物车编号
				'e_id' => $this->einfo ['e_id'], 	// 要删除的商家编号
				'mall_type' => 1, 					// （此分组只能删除）云总店
				'is_del' => 0
		);
		$deleteresult = M ( 'cart' )->where ( $cartmap )->delete (); // 删除分店购物车内的某商品
		if ($deleteresult) {
			// 删除购物车成功
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			// 删除购物车失败
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "删除失败，请勿重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 用户更新购物车商品数量。
	 * 在微猫商城的处理方法：
	 * 特别注意：完成的时候自动更新了购物车数量。因此用户如果在编辑购物车时删除了所有商品再完成，接收到的cartidlist都是空的了。
	 * 因此最佳的办法是：前台在得到用户删除商品成功的返回后，及时判断是否当前购物车里是否还有商品，如果一条都没了，直接remove掉（也不用点完成了，因为完成是确认购物车里数量的）。
	 * 在云总店的处理方法：
	 * 用户每次更新一件商品数量都会触发一次请求，不像微猫商城一样点击完成才统一更新。
	 * 该段代码已经适应云总店mall_type=1的调整。
	 */
	public function updateCart() {
		$editcid = I ( 'cid' ); 		// 被编辑的购物车编号清单
		$eidtamount = I ( 'amount' ); 	// 被编辑的购物车编号对应的数量
	
		// 检测参数
		if (empty ( $editcid )) {
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $eidtamount )) {
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号对应的数量不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过检验，准备更新
		$currentcartmap = array (
				'cart_id' => $editcid, 				// 当前编辑购物车
				'e_id' => $this->einfo ['e_id'], 	// 当前商家下
				'mall_type' => 1, 					// 更新云总店
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'], // 当前顾客（通过当前顾客来操作购物车，而不是微信用户）
				'is_del' => 0, 						// 没有被删除的
		);
		$updateinfo = array (
				'amount' => $eidtamount, 			// 要修改的数量
				'latest_modify' => time (), 		// 取现在时间作为修改时间
		);
		$updateresult = M ( 'cart' )->where ( $currentcartmap )->save ( $updateinfo ); // 更新该顾客购物车数量
		if ($updateresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10011;
			$this->ajaxresult ['errMsg'] = "更新失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
}
?>