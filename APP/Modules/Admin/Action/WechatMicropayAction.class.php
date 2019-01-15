<?php
import ( 'Class.API.WeChatPayV3.WeActWxPay.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动微信支付安全类

class WechatMicropayAction extends PCViewLoginAction {
	
	public function queryOrder() {
		$eid = "201406261550250006";
		$oid = "b35f8b2ad9051c0a6c917b1d2909214f";
		
		//$weactorder = new WeActWechatOrder ( $eid );
		//$wechatorder = $weactorder->queryWechatOrder ( $oid );
		
		$weactmicropay = new WeActMicroPay ( $eid );
		$wechatorder = $weactmicropay->queryWechatOrder ( $oid );
		
		p($wechatorder);die;
	}
	
	public function reverseOrder() {
		$eid = "201406261550250006";
		$oid = "0fec06370442a8e212b323e556101397";
	
		$weactmicropay = new WeActMicroPay ( $eid );
		$reverseresult = $weactmicropay->reverseWechatOrder ( $oid );
		p($reverseresult);die;
	}
	
	/**
	 * 测试微动刷卡支付。
	 */
	public function micropay() {
		$eid = "201406261550250006";
		$oid = "2e007412f3a6e760b8dab3ea15fccd7e";
		$authcode = "130332332845354462";
	
		$weactmicropay = new WeActMicroPay ( $eid );
		$payresult = $weactmicropay->cardMicropay ( $oid, $authcode ); // 调用结果
		if ($payresult) {
			p($payresult);die;
		} else {
			p($weactmicropay->getError ());die;
		}
	
	}
	
	public function orderCardPay() {
		// Step1：查询所有商品的编号和名称
		$protable = M ( 'product' ); // 实例化商品表
		$productmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'on_shelf' => 1, // 上架中的商品
				'is_del' => 0
		);
		$productlist = $protable->where ( $productmap )->order ( "add_time desc" )->field ( "product_id, product_number, product_name" )->select ();
		
		// Step2：查询商家所有商品的条形码
		$proskuview = M ( 'product_sku' ); // 实例化商品sku视图
		$codemap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'on_shelf' => 1, // 上架中的商品
				'bar_code' => array ( "neq", "" ), // 条形码不空
				'is_del' => 0
		);
		$barcodelist = $proskuview->where ( $codemap )->order ( "add_time desc" )->field ( "sizecolor_id, product_id, bar_code" )->select ();
		
		$this->productlist = $productlist; // 推送商品信息
		$this->barcodelist = $barcodelist; // 推送条形码信息
		$this->display ();
	}
	
}
?>