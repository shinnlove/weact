<?php
/**
 * 云总店收藏夹请求控制器。
 * @author 赵臣升
 * CreateTime:2015/07/06 15:54:26.
 */
class CollectionRequestAction extends MobileLoginRequestAction {
	
	/**
	 * 处理加入收藏夹的ajax请求。
	 */
	public function addCollection() {
		$product_id = I ( "pid" ); // 接收要加入收藏夹的商品编号
		
		// 检测参数
		if (empty ( $product_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "加入收藏夹失败，商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 检测该商品是否加入过收藏夹
		$collectiontable = M ( 'collection' ); // 实例化收藏夹表
		$collectionmap = array(
				'e_id' => $this->einfo ['e_id'],								// 商家编号
				'product_id' => $product_id,									// 商品编号
				'subbranch_id' => "-1", 										// 云总店的收藏夹
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],	// 顾客编号
				'is_del' => 0													// 没有被删除
		);
		$proexist = $collectiontable->where ( $collectionmap )->find ();		// 判断是否被收藏过
		if ($proexist) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "该商品已在收藏夹中！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 没有加入过收藏夹，正式加入云总店收藏夹
		$collectionmap ['record_time'] = time ();
		$collectionmap ['collection_id'] = md5 ( uniqid ( rand (), true ) ); 	// 用md5码产生一个随机的32位编号
		$addresult = $collectiontable->add ( $collectionmap );
		// 检测加入收藏夹结果
		if ($addresult) {
			// 加入收藏夹成功
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			// 加入收藏夹失败
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "加入收藏夹失败，请勿重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
}
?>