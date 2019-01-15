<?php
/**
 * 购物车ajax请求控制器。
 * 将购物车相关的ajax请求处理放到这里，代码不会集中在一个控制器中太多。
 * 特别注意：购物车添加是只能在当前店铺添加一些商品的，但是购物车修改数量和删除是可以在一家店铺操作另外一家店铺的购物车。
 * @author 赵臣升，胡睿。
 * CreateTime:2015/05/15 21:22:36.
 */
class CartRequestAction extends LoginRequestAction {
	
	/**
	 * 用户添加购物车的ajax请求处理。
	 * 改版：2015/06/27 对商品详情页面立即购买做出调整。
	 * 已在2015/08/20 16:42:36改版成mall_type类型。
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
		$subskumap = array (
				'sub_sku_id' => $skuid,
				'is_del' => 0
		);
		$subskuinfo = M ( 'subbranchsku' )->where ( $subskumap )->find (); // 从分店sku表中找到sku信息
		$skucolor = $subskuinfo ['sku_color']; // sku的颜色
		$skusize = $subskuinfo ['sku_size']; // sku的尺码规格
		$skuamountleft = $subskuinfo ['subsku_storage'] - $subskuinfo ['subsku_sell']; // 计算当前分店剩余的sku数量
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
				'e_id' => $this->eid, 			// 当前商家下
				'mall_type' => 2, 				// 微猫商城（2015/08/20 16:42:36）
				'subbranch_id' => $this->sid, 	// 当前分店下（因为别的分店的商品够不着，也加不了）
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前顾客
				'product_id' => $product_id, 	// 当前商品的（这件sku）
				'sku_id' => $skuid, 			// 特别特别注意：不同SKU千万不能在购物车算成一件商品
				'is_del' => 0, 					// 没有被删除的
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
			if (isset ( $_SESSION ['currentwechater'] [$this->eid] )) {
				// 如果用户微信授权登录，补充微信openid字段
				$cartmap ['openid'] = $_SESSION ['currentwechater'] [$this->eid] ['openid']; 
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
					'cart_id' => $cartid, 	// 购物车编号
					'mall_type' => 2, 		// 微猫商城（2015/08/20 16:42:36）
					'is_del' => 0
			);
			$cartinfo = M ( 'subbranch_cart' )->where ( $refresh )->find (); // 从分店购物车里再重新查一次
			$cartinfo ['macro_path'] = assemblepath ( $cartinfo ['macro_path'] ); // 组装下大图路径
			$cartinfo ['micro_path'] = assemblepath ( $cartinfo ['micro_path'] ); // 组装下小图路径
			// 如果添加成功，重查购物车信息
			$this->ajaxresult ['data'] = $cartinfo;
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 用户从购物车里删除商品的请求。
	 * 已在2015/08/20 16:42:36改版成mall_type类型。
	 */
	public function deleteCart() {
		$cart_id = I ( 'cid' ); 			// 当前被删除的购物车主键
		$editshopid = I ( 'handleshopid' ); // 当前操作删除的购物车店家编号
		// 检测参数
		if (empty ( $cart_id )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "删除失败，购物车编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 准备删除
		$cartmap = array (
				'cart_id' => $cart_id, 				// 要删除的购物车编号
				'e_id' => $this->eid, 				// 要删除的商家编号
				'mall_type' => 2, 					// 微猫商城（2015/08/20 16:42:36）
				'subbranch_id' => $editshopid, 		// 要删除的分店编号（这里不能用$this->sid用户所在分店，当然不建议把操作删除分店当做sid发送过来，容易混淆）
				'is_del' => 0, 						// 没有被删除的
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
	 * 特别注意：完成的时候自动更新了购物车数量。因此用户如果在编辑购物车时删除了所有商品再完成，接收到的cartidlist都是空的了。
	 * 因此最佳的办法是：前台在得到用户删除商品成功的返回后，及时判断是否当前购物车里是否还有商品，如果一条都没了，直接remove掉（也不用点完成了，因为完成是确认购物车里数量的）。
	 * 已在2015/08/20 16:42:36改版成mall_type类型。
	 */
	public function updateCart() {
		//$this->sid; // 这是顾客当前所在的店铺（在这家店铺可以操作其他店铺的购物车editshopid）
		$editshopid = I ( 'editsid' ); 		// 接收被编辑购物车的店铺编号
		$editcid = I ( 'cartidlist' ); 		// 被编辑的购物车编号清单
		$eidtamount = I ( 'amountlist' ); 	// 被编辑的购物车编号对应的数量
		
		// 检测参数
		if (empty ( $editshopid )) {
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车所属店铺编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $editcid )) {
			// 特别注意：完成的时候自动更新了购物车数量。因此用户如果在编辑购物车时删除了所有商品再完成，接收到的cartidlist都是空的了。（见台头标注）
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $eidtamount )) {
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号对应的数量不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		$editcidlist = explode ( ",", $editcid ); 		// 切割成购物车数组
		$eidtamountlist = explode ( ",", $eidtamount ); // 切割成数量数组
		
		// 再检验一次数据合法性
		if (empty ( $editcidlist )) {
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $eidtamountlist )) {
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "更新失败，所编辑的购物车编号对应的数量不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过检验，准备更新
		// 查询该顾客所操作中的分店的购物车
		$carttable = M ( 'cart' ); 				// 实例化购物车表对象
		$currentcartmap = array (
				'e_id' => $this->eid, 			// 当前商家下
				'mall_type' => 2, 				// 微猫商城（2015/08/20 16:42:36）
				'subbranch_id' => $editshopid, 	// 当前编辑购物车的商家编号
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前顾客（通过当前顾客来操作购物车，而不是微信用户）
				'is_del' => 0 					// 没有被删除的
		);
		$currentcartlist = $carttable->where ( $currentcartmap )->select (); 	// 找到该顾客所编辑的分店购物车下的所有购物车list（要进行哈希键值对的格式化）
		
		// 进行比对，防止数据库读写过多（数量不变更的就不用更新）
		$checkcartlist = array (); 				// 要比对的$checkcartlist
		foreach ($currentcartlist as $singlecart) {
			$checkcartlist [$singlecart ['cart_id']] = $singlecart; // 购物车主键作为key不可能重复！！！
		}
		
		$updatecartlist = array (); 				// 要更新的购物车数组
		$needupdatecount = 0; 						// 需要更新的数量
		$updatecount = 0; 							// 总的更新数量（实际更新的数量，小于等于$needupdatecount）
		$originalcount = count ( $editcidlist ); 	// 统计要循环的次数
		// 组装数组
		for($i = 0; $i < $originalcount; $i ++) {
			$updatecartlist [$i] = array (
					'cart_id' => $editcidlist [$i], // 取出购物车编号
					'amount' => $eidtamountlist [$i] // 取出要更新的数量
			);
		}
		// 更新购物车（小技巧：在每次更新完一条的时候，将更新时间秒数+1，这样购物车还是原来的顺序，因为购物车是按最新修改时间更新的）
		$updatetime = time (); // 当前更新时间
		for($j = 0; $j < $originalcount; $j ++) {
			
			$cartid = $updatecartlist [$j] ['cart_id']; // 取出购物车编号
			$amount = $updatecartlist [$j] ['amount']; // 取出最新的数量
			
			if ($checkcartlist [$cartid] ['amount'] != $amount) {
				$needupdatecount += 1; // 需要更新数量+1
				// 更新该顾客所操作中的购物车
				$updatecartmap = array (
						'cart_id' => $updatecartlist [$j] ['cart_id'], 	// 购物车编号
						'mall_type' => 2, 								// 微猫商城（2015/08/20 16:42:36）
						'e_id' => $this->eid, 							// 当前商家
						'subbranch_id' => $editshopid, 					// 特别注意：所编辑的店铺（很有可能不是用户所在店铺，是当前购物车操作的店铺）
						'is_del' => 0
				);
				$updateinfo = array (
						'amount' => $updatecartlist [$j] ['amount'], 	// 购物车数量
						'latest_modify' => $updatetime - $j 			// (小技巧：保持购物车最近更新降序有序)
				);
				$updatecount += $carttable->where ( $updatecartmap )->save ( $updateinfo ); // 更新购物车
			}
		}
		// 检验更新结果
		if ($updatecount) {
			// 有更新
			if ($updatecount == $needupdatecount) {
				// 购物车全部更新成功
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
			} else if ($updatecount < $needupdatecount) {
				// 购物车部分更新成功
				$this->ajaxresult ['errCode'] = 10010;
				$this->ajaxresult ['errMsg'] = "网络繁忙，购物车部分更新成功，请及时刷新页面！";
			}
			$this->ajaxresult ['data'] ['updatecount'] = $updatecount; // 追加本次更新的数量
		} else {
			// 没有更新
			if ($needupdatecount == 0) {
				// 没有需要更新的数量，不必更新
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
			} else {
				// 需要更新，但是没有一条数据更新成功
				$this->ajaxresult ['errCode'] = 10011;
				$this->ajaxresult ['errMsg'] = "更新失败，请不要重复提交！";
			}
			$this->ajaxresult ['data'] ['updatecount'] = 0; // else分支的两种情况都是更新了0个
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
}
?>