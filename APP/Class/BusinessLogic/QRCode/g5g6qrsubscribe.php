<?php
class QRSubbranch {
	
	/**
	 * G5G6关注图文信息
	 * @var string $subscribeURL
	 */
	private $subscribeURL = 'http://mp.weixin.qq.com/s?__biz=MzA4OTYyMDMyMA==&mid=204000187&idx=1&sn=9d356210ee6af92b0814524414bc811b#rd';
	
	/**
	 * 构造函数。
	 */
	function __construct() {
		// to do construct...
	}
	
	/**
	 * 处理店铺扫码情况的函数。
	 * @param array $scaninfo 扫码信息
	 */
	public function subscribe($e_id = '', $openid = '', $scaninfo = NULL) {
		// Step2-1：根据微信用户openid检查该用户有没有在微动的商家平台注册过账号：（customerinfo→ci）
		$cimap = array (
				'openid' => $openid, // 当前用户的微信openid
				'e_id' => $e_id, // 虽然微信平台说，同一个微信用户对于不同公众号的openid是唯一的，不同公众号openid是不同的，但是这里还是再加商家区分一下
				'is_del' => 0
		);
		$citable = M ( 'customerinfo' );
		$ciExist = $citable->where ( $cimap )->find (); // 计算有没有这样openid的用户存在
		// Step2-2：如果没找到这样的用户就替他注册一个扫码关注账号
		if (! $ciExist) {
			$newcustomer = array ();
			$newcustomer ['customer_id'] = date ( 'YmdHms' ) . randCode ( 4, 1 ); // 产生顾客编号
			$newcustomer ['openid'] = $openid; // 关联微信号
			$newcustomer ['e_id'] = $e_id; 											// 关联商家编号
			$newcustomer ['subordinate_shop'] = $scaninfo ['subbranch']; 			// 关键的一步：帮用户先在微动商家平台用微信号注册，日后登录或注册再进行账号绑定更新数据
			$newresult = $citable->data ( $newcustomer )->add ();					// 特别注意：用户账号和密码可以为空，否则记录插不进去。
		} else {
			if ($ciExist ['subordinate_shop'] == '-1' || empty ( $ciExist ['subordinate_shop'] )) {
				$ciExist ['subordinate_shop'] = $scaninfo ['subbranch'];
				$updateresult = $citable->save ( $ciExist );
			}
		}
		header ( 'Location:' . $this->subscribeURL );
	}
}
?>