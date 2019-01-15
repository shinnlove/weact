<?php
/**
 * 微信支付多态帮助库。
 * 微信分为普通商户模式和服务商模式，并且依据注册开通使用流程，两种状态不会随意切换，
 * 因而使用上下文环境包含状态，将状态类多态独立在此文件中，上下文环境依据数据库字段对Service层透明多态。
 */
import ( 'Class.API.WeActPay.PaySDK.WechatPaySDK.WechatPaySDKBase', APP_PATH, '.php' ); // 载入微信支付基础SDK

/**
 * 普通商户统一下单SDK。
 * @author Shinnlove
 */
class OrdinaryUnifiedOrder extends UnifiedOrder_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 设置统一下单参数。
	 * 特别注意：这跟ServiceUnifiedOrder里的有本质不同，不能合并，已出错+1.
	 * @param array $setparams 订单支付等信息
	 */
	public function setSDKParams($setparams = NULL) {
		// 统一下单的必填参数，其中appid公众账号id, mch_id商户号, noncestr随机字符串, spbill_create_ip订单生成机器的IP（weact服务器地址）, sign签名等5项由基类生成
		$this->setParameter ( "body", $setparams ['body'] );															// 商品描述
		$this->setParameter ( "out_trade_no", $setparams ['out_trade_no'] ); 											// 设置商户订单号，商户系统内部订单号，32个字符内、字母和数组，唯一性
		$this->setParameter ( "total_fee", $setparams ['total_fee'] ); 													// 总金额，单位为分，不能带小数点
		$this->setParameter ( "notify_url", $setparams ['notify_url'] );												// 支付通知地址，接收微信支付成功通知
		$this->setParameter ( "trade_type", $setparams ['trade_type'] ); 												// 本函数处理的交易类型:JSAPI（JSAPI、NATIVE、APP三种）
		// 统一下单不同类型需要的参数
		if (isset ( $setparams ['openid'] )) $this->setParameter ( "openid", $setparams ['openid'] ); 					// 用户的微信号openid,jsAPI下，此参数必须
		if (isset ( $setparams ['product_id'] )) $this->setParameter ( "product_id", $setparams ['product_id'] ); 		// 商品ID，只在trade_type为native时需要填写，id是二维码中商品ID，商户自行维护
		// 统一下单的非必填参数，商户可根据实际情况选填
		if (isset ( $setparams ['device_info'] )) $this->setParameter ( "device_info", $setparams ['device_info'] ); 	// 微信支付分配的终端设备号
		if (isset ( $setparams ['attach'] )) $this->setParameter ( "attach", $setparams ['attach'] ); 					// 附加数据，原样返回
		if (isset ( $setparams ['time_start'] )) $this->setParameter ( "time_start", $setparams ['time_start'] ); 		// 订单生成时间，yyyyMMddHHmmss，20091225091010，取自商户服务器
		if (isset ( $setparams ['time_expire'] )) $this->setParameter ( "time_expire", $setparams ['time_expire'] ); 	// 订单失效时间，yyyyMMddHHmmss，格式同上，取自商户服务器
		if (isset ( $setparams ['goods_tag'] )) $this->setParameter ( "goods_tag", $setparams ['goods_tag'] ); 			// 商品标记，该字段不能随便填，不适用请填写空
	}
	
	/**
	 * 普通商户模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为普通商户模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["spbill_create_ip"] = $_SERVER ['REMOTE_ADDR']; 		// 支付终端ip
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters ); 
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 服务商模式统一下单SDK。
 * @author Shinnlove
 */
class ServiceUnifiedOrder extends UnifiedOrder_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 设置统一下单参数。
	 * 特别注意：这跟OrdinaryUnifiedOrder里的有本质不同，不能合并，已出错+1.
	 * @param array $setparams 订单支付等信息
	 */
	public function setSDKParams($setparams = NULL) {
		// 统一下单的必填参数，其中appid公众账号id, mch_id商户号, noncestr随机字符串, spbill_create_ip订单生成机器的IP（weact服务器地址）, sign签名等5项由基类生成
		$this->setParameter ( "body", $setparams ['body'] );															// 商品描述
		$this->setParameter ( "out_trade_no", $setparams ['out_trade_no'] ); 											// 设置商户订单号，商户系统内部订单号，32个字符内、字母和数组，唯一性
		$this->setParameter ( "total_fee", $setparams ['total_fee'] ); 													// 总金额，单位为分，不能带小数点
		$this->setParameter ( "notify_url", $setparams ['notify_url'] );												// 支付通知地址，接收微信支付成功通知
		$this->setParameter ( "trade_type", $setparams ['trade_type'] ); 												// 本函数处理的交易类型:JSAPI（JSAPI、NATIVE、APP三种）
		// 统一下单不同类型需要的参数
		if (isset ( $setparams ['openid'] )) $this->setParameter ( "sub_openid", $setparams ['openid'] ); 				// 用户的微信号openid,jsAPI下，此参数必须
		if (isset ( $setparams ['product_id'] )) $this->setParameter ( "product_id", $setparams ['product_id'] ); 		// 商品ID，只在trade_type为native时需要填写，id是二维码中商品ID，商户自行维护
		// 统一下单的非必填参数，商户可根据实际情况选填
		if (isset ( $setparams ['device_info'] )) $this->setParameter ( "device_info", $setparams ['device_info'] ); 	// 微信支付分配的终端设备号
		if (isset ( $setparams ['attach'] )) $this->setParameter ( "attach", $setparams ['attach'] ); 					// 附加数据，原样返回
		if (isset ( $setparams ['time_start'] )) $this->setParameter ( "time_start", $setparams ['time_start'] ); 		// 订单生成时间，yyyyMMddHHmmss，20091225091010，取自商户服务器
		if (isset ( $setparams ['time_expire'] )) $this->setParameter ( "time_expire", $setparams ['time_expire'] ); 	// 订单失效时间，yyyyMMddHHmmss，格式同上，取自商户服务器
		if (isset ( $setparams ['goods_tag'] )) $this->setParameter ( "goods_tag", $setparams ['goods_tag'] ); 			// 商品标记，该字段不能随便填，不适用请填写空
	}
	
	/**
	 * 服务商模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为服务商模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["sub_appid"] = $this->securityinfo ['sub_appid']; 	// 子商户公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["sub_mch_id"] = $this->securityinfo ['sub_mch_id']; 	// 子商户财付通ID
			$this->parameters ["spbill_create_ip"] = $_SERVER ['REMOTE_ADDR']; 		// 支付终端ip
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters ); 
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 普通商户订单查询SDK
 * @author Shinnlove
 */
class OrdinaryOrderQuery extends OrderQuery_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 普通商户模式订单查询接口生成参数xml
	 */
	public function createXml() {
		try {
			// 为普通商户模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters );
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 服务商模式订单查询SDK
 * @author Shinnlove
 */
class ServiceOrderQuery extends OrderQuery_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 服务商模式订单查询接口生成参数xml
	 */
	public function createXml() {
		try {
			// 为服务商模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["sub_appid"] = $this->securityinfo ['sub_appid']; 	// 子商户公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["sub_mch_id"] = $this->securityinfo ['sub_mch_id']; 	// 子商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters ); 
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 普通商户订单退款SDK。
 * @author Shinnlove
 */
class OrdinaryOrderRefund extends Refund_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 普通商户模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为普通商户模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters );
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 服务商模式订单退款SDK
 * @author Shinnlove
 */
class ServiceOrderRefund extends Refund_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 服务商模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为服务商模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 必须附加的子商户号
			$this->parameters ["sub_mch_id"] = $this->securityinfo ['sub_mch_id']; 	// 子商户号
			// 附带的子商户号信息
			if (! empty ( $this->securityinfo ['sub_appid'] )) $this->parameters ["sub_appid"] = $this->securityinfo ['sub_appid']; // 子商户号的appid
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters );
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 普通商户订单退款查询SDK
 * @author Shinnlove
 */
class OrdinaryOrderRefundQuery extends RefundQuery_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 普通商户模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为服务商模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters );
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

/**
 * 服务商模式订单退款查询SDK
 * @author Shinnlove
 */
class ServiceOrderRefundQuery extends RefundQuery_pub {
	
	/**
	 * 构造函数
	 * @param array $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
	}
	
	/**
	 * 服务商模式生成接口参数xml
	 */
	public function createXml() {
		try {
			// 为服务商模式微信支付追加账户及签名参数
			$this->parameters ["appid"] = $this->securityinfo ['appid']; 			// 公众账号ID
			$this->parameters ["mch_id"] = $this->securityinfo ['mch_id']; 			// 主商户财付通ID
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 主公众号签名
			// 必须附加的子商户号
			$this->parameters ["sub_mch_id"] = $this->securityinfo ['sub_mch_id']; 	// 子商户号
			// 附带的子商户号信息
			if (! empty ( $this->securityinfo ['sub_appid'] )) $this->parameters ["sub_appid"] = $this->securityinfo ['sub_appid']; // 子商户号的appid
			// 生成通信XML压缩包
			$xmlpackage = $this->arrayToXml ( $this->parameters );
			return  $xmlpackage;
		} catch (SDKRuntimeException $e) {
			die ( $e->errorMessage () );
		}
	}
	
}

?>