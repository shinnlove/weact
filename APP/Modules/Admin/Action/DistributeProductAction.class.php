<?php
/**
 * 分发商品控制器（测试用）。
 * @author Administrator
 *
 */
class DistributeProductAction extends Action {
	/**
	 * 测试分发商品。
	 */
	public function testDistributor() {
		$product_id = "01cdb7a6ef84265494538311a94e889a"; // 模拟的productid，男士羽绒带毛领帽外套
		// 模拟10家店铺
		$subbranchidlist = array (
				'浙江杭州学士店' => '028f7734e7c810e9c9d10b13bfc3c353', 
				'浙江绍兴钱清店' => '03544a0ed0ef8cdc1985b4f4a717af51', 
				'江苏徐州沛县2店' => '06f80c59179ea48b6c4f857b9f2e5039', 
				'浙江金华浦江店' => '070b107fd7ecae417e7a2266ebd7bc9c', 
				'江苏淮安涟水店' => '07819f9d424646c92c180594c4cd2f2a', 
				'湖南湘潭浏阳店' => '07efa0b1b700245158266f913fd5c0cc', 
				'浙江杭州瓜沥店' => '081765e7e7b9fce3e40f96809b28d3f7', 
				'浙江杭州萧山夜市店' => '0a4d4d72b82abadf598dca6c78f8c348', 
				'江苏无锡梅村店' => '0a67dc97811ef1d9e5e38070ce130fd2', 
				'浙江温岭泽国店' => '0c0767a3c9999161fc2fd6ef60ff0913' 
		);
		$this->pid = $product_id; // 推送商品编号
		$this->sidlist = implode ( ",", $subbranchidlist ); // 推送分店编号
		$this->display ();
	}
	
	/**
	 * 接收前台ajax提交然后处理的函数。
	 */
	public function handleDistributor() {
		// 接收信息
		if (! IS_POST) {
			$this->error ( 'Sorry, page not exist!' ); // 防止恶意打开
		}
		
		$ajaxinfo = array (
				'product_id' => I ( 'pid', '' ), // 接收商品编号（要操作的商品编号绝对不能为空）
				'sidlist' => I ( 'sidlist' ), // 分配给的分店编号（可能为空代表删除，也可能不空要比对）
		);
		p($ajaxinfo);die;
		$this->ajaxReturn ( $data );
	}
}
?>