<?php
/**
 * 条形码或二维码搜索商品的控制器。
 * @author 微动团队,黄昀
 * CreateTime 2015/03/11 16:47:00
 */
class ProductCodeAction extends Action {
	/**
	 * 该函数处理由三方APP摄像头扫描条形码后，做出的URL跳转。
	 * 接收三方公司传来的条形码参数，并且查询商家编号、导航编号、商品编号后跳转Home分组。
	 */
	public function barCodeScan() {
		// 接收条形码参数，查询数据库，拼接URL地址并跳转。
		$barcode = I ( 'barcode', '0' );
		if ($barcode < 0) {
			$this->error ( '条形码参数错误！' );
		}
		$proscmap = array (
				'bar_code' => $barcode,
				'is_del' => 0 
		);
		$productlist = M ( 'productsizecolor' )->where ( $proscmap )->limit ( 1 )->select ();
		// 只找其中一条商品
		if (! empty ( $productlist )) {	
			$productinfo = $productlist [0]; // 取出第一条商品信息
			$targeturl = 'WeMall/QRCode/product';
			$redirectparams = array (
					'pid' => $productinfo ['product_id'] 
			);
			$this->redirect ( $targeturl, $redirectparams ); // 跳转网址
		} else {
			$this->error ( '您要找的商品信息不存在！' );
		}
	}
}
?>