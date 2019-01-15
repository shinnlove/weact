<?php
/**
 * 微信上下文只做对微信不同状态SDK的透明访问，
 * 创建多态和调用多态，不处理数据库读写。
 */
import ( 'Class.API.WeActPay.PayContext.WeActPayContext', APP_PATH, '.php' ); // 载入微动支付上下文基类
import ( 'Class.API.WeActPay.PaySDK.WechatPaySDK.WechatPaySDKState', APP_PATH, '.php' ); // 载入微信支付服务状态SDK

/**
 * 微信支付各种上下文场景基类。
 * @author Shinnlove
 */
class WechatPayContext extends WeActPayContext {
	
	protected $securityinfo;
	
	/**
	 * 微信支付上下文环境类构造函数。
	 * @param string $securityinfo
	 */
	public function __construct($securityinfo = NULL) {
		$this->securityinfo = $securityinfo;
	}
	
}

/**
 * 统一下单类上下文环境。
 * @author Shinnlove
 */
class UnifiedOrderContext extends WechatPayContext {
	
	/**
	 * 统一下单基类SDK对象
	 * @var object $unifiedorder
	 */
	protected $unifiedorder;
	
	/**
	 * 微信支付统一下单类上下文构造函数，依据不同服务状态多态生成SDK。
	 * @param array $securityinfo 企业安全支付信息
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
		if ($securityinfo ['wechatpay_type'] == 1) {
			$this->unifiedorder = new ServiceUnifiedOrder ( $securityinfo ); 	// 微信服务商模式的统一下单
		} else {
			$this->unifiedorder = new OrdinaryUnifiedOrder ( $securityinfo ); 	// 普通商户模式的统一下单
		}
	}
	
	/**
	 * 设置当前统一下单的上下文环境
	 * @param object $unifiedorder 统一下单上下文环境
	 */
	public function setServiceState($unifiedorder = NULL) {
		$this->unifiedorder = $unifiedorder; // 设置实现ServiceState的对象实例
	}
	
	/**
	 * 统一下单上下文环境直接透明调用不同SDK进行统一下单。
	 * @param string $prepayinfo 订单支付等信息
	 * @return array $prepayresult 统一下单结果
	 */
	public function unifiedOrder($prepayinfo = NULL) {
		if (! $this->unifiedorder->checkSDKParams ( $prepayinfo )) {
			return false; // 不通过参数校验停止执行
		}
		$this->unifiedorder->setSDKParams ( $prepayinfo ); 						// 多态调用设置参数，给SDK赋值
		$prepayresult = $this->unifiedorder->getPostResult ( "unifiedorder" ); 	// 调用SDK统一下单返回预支付信息
		return $prepayresult; 													// 上下文对结果不作处理直接给微动支付（有数据库读写）
	}
	
}

/**
 * 订单查询上下文环境。
 * @author Shinnlove
 */
class OrderQueryContext extends WechatPayContext {
	
	/**
	 * 订单查询基类SDK对象
	 * @var object $orderquery
	 */
	protected $orderquery;
	
	/**
	 * 订单查询类上下文构造函数，依据不同服务状态多态生成SDK。
	 * @param array $securityinfo 企业安全支付信息
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
		if ($securityinfo ['wechatpay_type'] == 1) {
			$this->orderquery = new ServiceOrderQuery ( $securityinfo ); 		// 微信服务商模式的订单查询
		} else {
			$this->orderquery = new OrdinaryOrderQuery ( $securityinfo ); 		// 普通商户模式的订单查询
		}
	}
	
	/**
	 * 统一订单查询环境直接透明调用不同SDK进行查询。
	 * @param array $queryinfo 要查询的订单信息
	 * @return array $wechatorderinfo 从微信服务器查询到的订单信息
	 */
	public function queryWechatOrderInfo($queryinfo = NULL) {
		if (! $this->orderquery->checkSDKParams ( $queryinfo )) {
			return false; // 不通过参数校验停止执行
		}
		$this->orderquery->setSDKParams ( $queryinfo ); 						// 多态调用设置参数，给SDK赋值
		$wechatorderinfo = $this->orderquery->getPostResult ( "orderquery" ); 	// 调用SDK统一下单返回预支付信息
		return $wechatorderinfo; 												// 上下文对结果不作处理直接给微动支付
	}
	
}

/**
 * 订单退款上下文环境
 * @author Shinnlove
 */
class OrderRefundContext extends WechatPayContext {
	
	/**
	 * 订单退款基类SDK对象。
	 * @var object $orderrefund
	 */
	protected $orderrefund;
	
	/**
	 * 订单退款查询基类SDK对象。
	 * @var object $orderrefundquery
	 */
	protected $orderrefundquery;
	
	/**
	 * 设置当前微信退款的上下文环境。
	 * @param object $orderrefund 微信退款类
	 * @param object $orderrefundquery 微信退款查询类
	 */
	public function setServiceState($orderrefund = NULL, $orderrefundquery = NULL) {
		if (! empty ( $orderrefund ) && ! empty ( $orderrefundquery )) {
			$this->orderrefund = $orderrefund;
			$this->orderrefundquery = $orderrefundquery;
		}
	}
	
	/**
	 * 订单退款类上下文环境构造函数，依据不同服务状态多态生成SDK。
	 * @param array $securityinfo 企业安全支付信息
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); // 调用父类构造函数
		if ($securityinfo ['wechatpay_type'] == 1) {
			$this->orderrefund = new ServiceOrderRefund ( $securityinfo ); 				// 微信服务商模式的订单退款
			$this->orderrefundquery = new ServiceOrderRefundQuery ( $securityinfo ); 	// 微信服务商模式的订单退款查询
		} else {
			$this->orderrefund = new OrdinaryOrderRefund ( $securityinfo ); 			// 普通商户模式的订单退款
			$this->orderrefundquery = new OrdinaryOrderRefundQuery ( $securityinfo ); 	// 普通商户模式的订单退款查询
		}
	}
	
	/**
	 * 统一下单上下文环境直接透明调用不同SDK进行订单微信退款。
	 * @param array $refundinfo 订单退款信息
	 * @return array $refundresult 微信退款结果
	 */
	public function orderRefund($refundinfo = NULL) {
		if (! $this->orderrefund->checkSDKParams ( $refundinfo )) {
			return false; // 不通过参数校验停止执行
		}
		$this->orderrefund->setSDKParams ( $refundinfo ); 								// 多态调用设置参数，给SDK赋值
		$refundresult = $this->orderrefund->getPostResult ( "refund", true ); 			// 调用SDK微信退款返回退款信息（带证书的通信）
		return $refundresult; 															// 上下文对结果不作处理直接给微动支付（有数据库读写）
		
	}
	
	/**
	 * 统一下单上下文环境直接透明调用不同SDK进行订单微信退款查询。
	 * @param array $refundqueryinfo 订单退款查询信息
	 * @return array $refundqueryresult 微信退款查询结果
	 */
	public function orderRefundQuery($refundqueryinfo = NULL) {
		if (! $this->orderrefundquery->checkSDKParams ( $refundqueryinfo )) {
			return false; // 不通过参数校验停止执行
		}
		$this->orderrefundquery->setSDKParams ( $refundqueryinfo ); 					// 多态调用设置参数，给SDK赋值
		$refundqueryresult = $this->orderrefundquery->getPostResult ( "refundquery" ); 	// 调用SDK微信退款返回退款信息
		return $refundqueryresult; 														// 上下文对结果不作处理直接给微动支付（有数据库读写）
	}
	
}

?>