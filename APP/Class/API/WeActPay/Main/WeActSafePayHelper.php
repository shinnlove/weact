<?php
/**
 * 微动-微信安全支付Service层。
 * author：shinnlove
 * email:306086640@qq.com
 * ====================================================
 * 目前支付方式支持两种类型：
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 【响应型接口】--Wxpay_server_
 * 		通用通知接口--Notify
 * 		Native支付——请求商家获取商品信息接口--NativeCall
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * =====================================================
 * 【CommonUtil】常用工具：
 * 		trimString()，设置参数时需要用到的字符处理函数
 */
import ( 'Class.API.WeActPay.PayContext.AliPayContext', APP_PATH, '.php' ); 				// 载入微动支付支付宝上下文
import ( 'Class.API.WeActPay.PayContext.WechatPayContext', APP_PATH, '.php' ); 				// 载入微动支付微信上下文
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 						// 载入微动微信APISDK类
import ( 'Class.API.MessageVerify.mobileMessage', APP_PATH, '.php' ); 						// 载入短信通知模板类

/**
 * 微动平台全局安全支付帮助类，所有微动支付类的安全支付基类。
 */
class WeActSafePayHelper {
	
	/**
	 * 商家支付信息。
	 * @var array $securityinfo
	 */
	protected $securityinfo;
	
	/**
	 * 微动支付调用错误信息。
	 * @var string $error 
	 */
	protected $error;
	
	/**
	 * 微动平台全局安全支付类构造函数，初始化域名。
	 */
	public function __construct ($e_id = '') {
		if (empty ($e_id)) {
			return false;
		}
		$this->securityinfo = $this->getSecurityInfo ( $e_id ); // 获取企业支付信息
	}
	
	/**
	 * ==========业务逻辑函数==========
	 */
	
	/**
	 * 可被子类继承的私有函数：获取企业支付敏感信息。
	 * @param string $e_id 企业编号
	 * @return array $securityinfo 企业安全保密信息（支付信息）
	 */
	protected function getSecurityInfo($e_id = '') {
		$securityinfo = array(); // 企业的安全信息
		if (! empty ( $e_id )) {
			$securitytable = M ( 'secretinfo' );
			$security = array(
					'e_id' => $e_id,
					'is_del' => 0
			);
			$securityinfo = $securitytable->where ( $security )->find (); // 查找企业敏感信息
			$domain = C ( 'DOMAIN' ); // 提取域名
			// 转换证书为本地路径
			$securityinfo ['cert_p12'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( $domain, "", $securityinfo ['cert_p12'] );
			$securityinfo ['sslcert_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( $domain, "", $securityinfo ['sslcert_path'] );
			$securityinfo ['sslkey_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( $domain, "", $securityinfo ['sslkey_path'] );
			$securityinfo ['rootca_pem'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( $domain, "", $securityinfo ['rootca_pem'] );
		}
		return $securityinfo;
	}
	
	/**
	 * 根据订单编号查询订单主单信息。
	 * @param string $order_id 订单编号
	 * @return array $orderinfo 订单主单信息
	 */
	protected function getOrderMainInfo($order_id = '') {
		$orderinfo = array ();
		if (! empty ( $order_id )) {
			$ordermap = array (
					'order_id' => $order_id, // 订单编号
					'is_del' => 0
			);
			$orderinfo = M ( 'ordermain' )->where ( $ordermap )->find (); // 找到订单主单信息
		}
		return $orderinfo;
	}
	
	/**
	 * ==========日志工具函数==========
	 */
	
	/**
	 * 调试专用函数，记录信息到日志。
	 * @param string|array $loginfo 要记录的日志信息
	 */
	protected function debugInfo($loginfo = NULL) {
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/debug" . __CLASS__ . "/"; // 类名作文件夹
		$filename = "debug" . timetodate ( time (), true ) . ".log"; // 文件名按天存放
		$this->logResult ( $filepath, $filename, json_encode ( $loginfo ) ); // 记录文件信息
	}
	
	/**
	 * 重写打印日志文件函数，权限：子类可见。
	 * CreateTime:2015/08/30 17:33:25.
	 * @author shinnlove
	 * @param string $filefolder 日志文件存放的文件夹名
	 * @param string $filename 日志文件存档的文件名
	 * @param string $loginfo 日志文件需要记录的信息
	 */
	protected function logResult($filefolder = "", $filename = "", $loginfo = NULL) {
		$logsuccess = false; // 记录日志文件失败
		if (! empty ( $filefolder ) && ! empty ( $filename )) {
			// 如果文件夹路径和文件名都不空，则记录日志文件
			if (! is_dir ( $filefolder ) ) mkdirs ( $filefolder ); // 如果没有存在文件夹，直接创建文件夹
			$fp = fopen ( $filefolder . $filename, "a" ); // 所有权限打开这个日志文件，文件夹路径+文件名
			flock ( $fp, LOCK_EX ); 	// 锁定文件读写权限
			fwrite ( $fp, "日志记录接口消息时间：" . strftime ( "%Y-%m-%d %H:%M:%S", time () ) . "\n" . $loginfo . "\n\n" ); // 记录日志信息
			flock ( $fp, LOCK_UN ); 	// 解锁文件读写权限
			fclose ( $fp ); 			// 关闭文件句柄
			$logsuccess = true; 		// 到此日志文件记录成功
		}
		return $logsuccess;
	}
	
	/**
	 * ==========对外接口函数==========
	 */
	
	/**
	 * 获取企业信息。
	 * @param string $e_id 企业编号
	 * @return array $einfo 企业信息
	 */
	public function getEnterpriseInfo($e_id = '') {
		$einfo = array ();
		if (! empty ( $e_id )) {
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		}
		return $einfo;
	}
	
	/**
	 * 获取微信支付调用错误信息。
	 * @return string $error 错误信息
	 */
	public function getError() {
		return $this->error;
	}
	
}

/**
 * 微动微信统一下单类。
 * JSAPI和NATIVE两种方式都需要调用统一下单接口进行微信端下单操作。
 */
class WeActUnifiedOrder extends WeActSafePayHelper {
	
	/**
	 * 统一下单上下文对象
	 * @var object $unifiedordercontext
	 */
	protected $unifiedordercontext;
	
	/**
	 * 微信统一下单类的消息回调地址（不带域名，构造函数中需初始化）。
	 * 特别注意：据说支付地址和消息回调地址不能在同一个目录下，否则收不到消息！！！
	 * @var String $CONST_NOTIFY_URL
	 */
	protected $CONST_NOTIFY_URL = "/weact/Home/WeChatPayCallback/notifyurl/e_id/";
	
	/**
	 * 微信统一下单类构造函数。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类构造函数读取支付信息
		$this->unifiedordercontext = new UnifiedOrderContext ( $this->securityinfo ); 	// 构建统一下单上下文对象
		$this->CONST_NOTIFY_URL = C ( 'DOMAIN' ) . $this->CONST_NOTIFY_URL . $e_id; 	// 拼接当前商家统一下单类支付异步通知地址
	}
	
	/**
	 * ==========对外接口区域==========
	 */
	
	/**
	 * 调用微信统一下单接口进行统一下单获得prepay_id。
	 * 下单成功后，更新wechatpayrecord表。
	 * @param array $prepayinfo 统一下单所需支付信息
	 * @return boolean|string false|$prepay_id 如果下单成功返回prepayid字符串，不成功返回false
	 */
	public function unifiedOrder($prepayinfo = NULL) {
		$prepayinfo ['notify_url'] = $this->CONST_NOTIFY_URL; 							// 增加支付通知地址参数
		
		$prepayresult = $this->unifiedordercontext->unifiedOrder ( $prepayinfo ); 		// 统一下单返回预支付信息
		
		// 如果统一下单成功，则回写wechatpayrecord记录
		if ($prepayresult ['return_code'] == "SUCCESS" && $prepayresult ['result_code'] == "SUCCESS") {
			$prepay_id = $prepayresult ['prepay_id']; // 抽取prepay_id
			
			if ($prepayinfo ['trade_type'] == "JSAPI") {
				// JSAPI回写wechatpayrecord
				$updatepay = array (
						'nonce_str' => $prepayresult ['nonce_str'], 		// 本次统一下单请求的随机数
						'sign' => $prepayresult ['sign'], 					// 本次统一下单请求的签名
						'notify_url' => $this->CONST_NOTIFY_URL, 			// 本次统一下单请求的异步通知地址
						'result_code' => $prepayresult ['result_code'], 	// 业务结果
						'prepay_id' => $prepay_id, 							// 微信统一下单接口返回的预支付id
				);
				$updatepaymap = array (
						'wechatpay_id' => $prepayinfo ['wechatpay_id'], // 要更新的待支付记录
						'is_del' => 0,
				);
				$updatepayresult = M ( 'wechatpayrecord' )->where ( $updatepaymap )->save ( $updatepay ); // 更新这条待支付记录
			}
		} else {
			$this->error = $prepayinfo ['err_code_des']; // 统一下单不成功，记录错误信息
		}
		
		return $prepay_id;
	}
	
}

/**
 * 微动JsAPI支付类，继承自安全支付基类。
 * 微动平台的H5网页发起支付请求使用此类。
 * H5网页的JsAPI支付也需要使用统一支付安全类UnifiedOrder_pub下单。
 */
class WeActJsAPIPay extends WeActUnifiedOrder {
	
	/**
	 * 本类的jsapi支付对象。
	 * @var object $jsapi
	 */
	private $jsapi;
	
	/**
	 * JSAPI安全支付类的构造函数，初始化域名。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类：统一下单类构造函数
		$this->jsapi = new JsApi_pub ( $this->securityinfo ); // 创建本类的jsapi对象
	}
	
	/**
	 * ====================PART2：私有函数部分，处理敏感信息和关键步骤。======================
	 */
	
	/**
	 * 私有函数：检测jsapi支付信息是否完整（支付必要的字段信息是否存在），由callUpJsPayV3函数调用完成信息完整性的检测。
	 * @param array $jsapipayinfo jsapi支付信息数组
	 * @return boolean $completed true|false 返回jsapi支付信息是否完整
	 */
	private function jsAPIPayInfoCompleted($jsapipayinfo = NULL) {
		$completed = false; // 默认信息未完整
		if (isset ( $jsapipayinfo ['body'] ) && isset ( $jsapipayinfo ['out_trade_no'] ) && isset ( $jsapipayinfo ['total_fee'] ) && isset ( $jsapipayinfo ['trade_type'] )) {
			$completed = true;
		}
		return $completed;
	}
	
	/**
	 * 私有函数：在微动本地数据库记录一笔微信支付的操作，已添加有效时间判断2015/02/22 16:49:25。
	 * 情形一：如果没有这笔待支付的记录，生成一条记录并返回待支付记录的编号；
	 * 情形二：如果有这笔待支付的记录，判断是否过期：如果不过期直接返回记录，如果过期则删除原来的、再重新生成一条新的记录返回。
	 * 特别注意：微动平台待支付记录默认保存时间是2小时，记录在config配置文件中（有效秒数7200）。
	 * 额外提醒：为何不更新记录生成的时间，因为想要更新其md5主键，得不到正确的主键md5，就无法进入最终支付页面，能一定程度防止被攻击，其主键带有一定的CSRFToken效果。
	 * @param array $wechatpayinfo 等待微信支付的预准备信息
	 * @return boolean|string $recordpayresult 记录等待微信支付结果，记录不成功返回false，记录成功返回记录的编号。
	 */
	private function recordWeChatPayInfo($wechatpayinfo = NULL) {
		$wechatpayid = false; // 新生成的微信支付编号（默认没有生成成功）
		$validity = false; // 如果有待支付记录的话，先默认待支付记录的无效
		if (! empty ( $wechatpayinfo )) {
			$payrectable = M ( 'wechatpayrecord' ); // 实例化微信支付记录表
			$checkpayexist = array(
					'e_id' => $wechatpayinfo ['e_id'], // 该商家下
					'out_trade_no' => $wechatpayinfo ['out_trade_no'], // 该笔订单的
					'openid' => $wechatpayinfo ['openid'], // 这个用户的
					'is_del' => 0
			);
			$payinfo = $payrectable->where( $checkpayexist )->find(); // 尝试寻找是否有这样一笔待支付订单（用户第二次及以上对同笔订单尝试微信支付）
			// 如果存在待支付记录，检查其有效性
			if ($payinfo) {
				// 检测是否过期，如果不过期直接返回
				$timenow = time (); // 取当前时间
				$effectivetime = C ( 'WECHATPAY_RECORD_VALIDTIME' ); // 读取配置常量，微支付有效时间（秒数）
				if ($timenow - $payinfo ['create_time'] <= $effectivetime) {
					$validity = true; // 这笔记录是有效的
					$wechatpayid = $payinfo ['wechatpay_id']; // 确实存在，并且在有效时间内，则直接让其支付
				} else {
					// 过期的记录无效，要删除原来的
					$delresult = $payrectable->where ( $checkpayexist )->delete (); // 直接真删除，貌似暂时不用做逻辑删除，接不接受返回值随意
				}
			}
			// 待支付记录无效或没有待支付记录，必然要重新生成记录（见本函数备注的额外提醒）
			if (! $validity) {
				$tobepaid = array (
						'wechatpay_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id' => $wechatpayinfo ['e_id'],
						'appid' => $this->securityinfo ['appid'],
						'mch_id' => $this->securityinfo ['mchid'],
						'body' => $wechatpayinfo ['body'],
						'out_trade_no' => $wechatpayinfo ['out_trade_no'],
						'total_fee' => $wechatpayinfo ['total_fee'],
						'spbill_create_ip' => $_SERVER ['REMOTE_ADDR'],
						'time_start' => $wechatpayinfo ['time_start'],
						'time_expire' => $wechatpayinfo ['time_end'],
						'openid' => $wechatpayinfo ['openid'],
						'trade_type' => $wechatpayinfo ['trade_type'],
						'create_time' => time ()
				); // 待支付信息
				$addpayresult = $payrectable->add ( $tobepaid ); // 插入待支付信息
				if ($addpayresult) {
					$wechatpayid = $tobepaid ['wechatpay_id']; // 返回新增的待支付记录
				}
			}
		}
		return $wechatpayid;
	}
	
	/**
	 * ===================PART3：公有接口部分。=======================
	 */
	
	/**
	 * 公有接口：其他模块需要调起微信支付的入口：传入准备好的微信支付信息，系统会判断是否满足支付条件，如果满足，会将待支付记录存入数据库。
	 * 使用方法：是在用户打算进行微信支付时，由调起微信支付的页面预先使用该函数为一笔支付生成预留信息（有效期2小时），再跳转到最终支付页面，是一个数据的预处理接口。
	 * 建议其他模块合理的操作步骤（以本函数返回值为准）：
	 * 当调用本函数执行成功，会返回待支付的待支付编号，其他模块可以使用redirect带参（待支付编号wcpid和支付回跳地址redirecturi）跳转至支付目录；
	 * 当调用本函数执行失败，返回false则代表不满足支付条件或调起支付失败（数据库写入失败等信息），提示用户调起商户微信支付未准备好。
	 * @param array $preparepayinfo 准备好的微信支付信息。
	 * @return boolean|string $payreadyinfo 调起微信支付准备就绪标记，false|string。
	 */
	public function callUpJsAPIPayV3($preparepayinfo = NULL) {
		$payreadyinfo = false; // 默认微信支付未准备好
		if (! empty ( $preparepayinfo )) {
			$infocompleted = $this->jsAPIPayInfoCompleted ( $preparepayinfo ); // Step2：检测支付信息完整性
			if ($infocompleted) {
				$payreadyinfo = $this->recordWeChatPayInfo ( $preparepayinfo ); // Step3：在数据库中生成一条待微信支付的信息
			}
		}
		return $payreadyinfo;
	}
	
	/**
	 * 最核心的对外接口：商户JsAPI微支付处理函数jsapiPayHandleV3，本函数只提供对视图控制器生成最终支付参数的服务。
	 * 使用方法：在用户来到最终支付页面时，展示页面前由控制器调用生成最终的支付参数给支付页面。
	 * 特别注意：$payparameters是最终的订单支付参数，如果本函数执行完还是空值，说明商家的微支付信息没有准备好（如敏感的支付信息没有完整）。
	 * @param array $wechatpayid 微信待支付信息
	 * @return string $jsPayParameters 返回微信JSAPI支付的参数（json）
	 */
	public function jsapiPayHandleV3($payinfo = NULL) {
		// 调用统一下单接口得到预支付id并使用$jsApi对象生成支付参数
		$prepay_id = $this->unifiedOrder ( $payinfo ); 		// 调用统一下单接口得到预支付id
		$this->jsapi->setPrepayId ( $prepay_id ); 			// 为$jsApi对象设置预支付参数
		$payparameters = $this->jsapi->getParameters (); 	// $jsApi生成最终支付参数
		return $payparameters;
	}
	
}

/**
 * 微动Native支付类，继承自安全支付基类。
 * 微动平台的扫码支付使用此类。
 * 扫码支付也要使用统一支付接口类UnifiedOrder_pub下单。
 */
class WeActNativePay extends WeActUnifiedOrder {
	
	/**
	 * 定义本类私有对象nativeCall.
	 * @var object $nativeCall 扫码支付处理对象
	 */
	private $nativeCall;
	
	/**
	 * 原生支付类构造函数，形参传入企业编号。
	 * @param string $e_id 商家编号
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类：统一下单类构造函数
		$this->nativeCall = new NativeCall_pub ( $this->securityinfo ); // 生成本类的对象
	}
	
	/**
	 * 微信用户原生态扫码支付处理函数。
	 * @param xml $xml 微信用户扫码后发来的数据
	 * @return xml $handleresult 扫码支付处理结果
	 */
	public function nativeScanHandle($xml = NULL) {
		$this->nativeCall->saveData ( $xml, "native" ); // 解析微信发来的xml数据
		$nativepayinfo = $this->nativeCall->getData (); // 获取解析扫码支付信息
		
		// 验证微信签名
		if ($this->nativeCall->checkSign() == FALSE) {
			$this->nativeCall->setReturnParameter ( "return_code", "FAIL" ); 		// （return_code管通信）返回状态码
			$this->nativeCall->setReturnParameter ( "return_msg", "签名失败" ); 		// （return_msg管通信）返回信息
		} else {
			// 进行预设参数（默认微信发来的xml没有商品与其对应）
			$this->nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); 	// （return_code管通信）返回服务器处理微信状态码结果
			$this->nativeCall->setReturnParameter ( "result_code", "FAIL" ); 		// （result_code管业务）业务结果失败
			$this->nativeCall->setReturnParameter ( "err_code_des", "此商品无效" ); 	// （err_code_des管业务）业务结果失败原因描述，默认没有找到商品
			
			// 开始查找商品
			$product_id = $this->nativeCall->getProductId (); // 提取微信发来的xml中的product_id，与生成二维码连接中的product_id一致
			$productinfo = $this->getProductInfo ( $product_id ); // 尝试获取商品信息，该商品信息可能为空
			
			// 商品不空才去下单，否则就直接返回商品无效
			if (! empty ( $productinfo )) {
				// 准备扫码支付和下单信息
				$price = $productinfo ['current_price']; 										// 要付的钱款，这一行中以元为单位
				// 价格小于等于0的商品不允许扫码支付
				if ($price > 0) {
					$totalfee = intval ( $price * 100 ); 										// 微信支付钱款，这一行后以分为单位。商品现价转化成分，然后转整数
					$body = "扫码支付 " . $productinfo ['product_number'] . " " . $productinfo ['product_name'] . " " . $productinfo ['current_price'] . "元。";
					$order_id = md5 ( uniqid ( rand (), true ) ); 								// 订单编号
					$openid = $nativepayinfo ['openid']; 										// 获取扫码微信用户openid
					$is_subscribe = $nativepayinfo ['is_subscribe']; 							// 用户是否关注（如果关注，调用接口获取其信息）
					$e_id = $this->securityinfo ['e_id']; 										// 提取商家编号
					// 再做一个下单未支付的操作，然后生成一笔待支付订单（第一次就不用wechatpayrecord了，因为不走暗线，但是订单生成后，如果改用其他方式支付，还是可以生成一条wechatpayrecord记录的）
					$orderinfo = $this->scanOrder ( $e_id, $openid, $is_subscribe, $order_id, $productinfo ); 	// 扫码支付下单
					
					if (! empty ( $orderinfo )) {
						// 如果下单成功，才返回扫码支付成功信息，去生成$prepay_id让用户支付
						
						// 准备扫码支付信息
						$prepayinfo = array (
								'openid' => $openid, 			// 用户的微信号openid,NATIVE下，此参数可选
								'body' => $body, 				// 扫码支付的商品描述
								'out_trade_no' => $order_id, 	// 设置商户订单号，商户系统内部订单号，32个字符内、字母和数组，唯一性
								'total_fee' => $totalfee, 		// 总金额，单位为分，不能带小数点
								'trade_type' => "NATIVE", 		// 本函数处理的交易类型:JSAPI（JSAPI、NATIVE、APP三种）
								'product_id' => $product_id, 	// 商品ID，只在trade_type为native时需要填写，id是二维码中商品ID，商户自行维护
						);
						
						// 现在wechatpayrecord中生成一条记录
						
						// 调用统一下单接口下单
						$prepay_id = $this->unifiedOrder ( $prepayinfo ); // 获取prepay_id
						
						if (! empty ( $prepay_id )) {
							// 更改返回码与必填参数
							$this->nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); 	// 返回状态码
							$this->nativeCall->setReturnParameter ( "result_code", "SUCCESS" ); 	// 服务器处理业务结果
							$this->nativeCall->setReturnParameter ( "prepay_id", $prepay_id ); 		// 本次NATIVE扫码支付的预支付ID
						} else {
							// 下单不成功，预支付id没有得到
							$this->nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); 	// （return_code管通信）返回服务器处理微信状态码结果
							$this->nativeCall->setReturnParameter ( "result_code", "FAIL" ); 		// （result_code管业务）业务结果失败
							$this->nativeCall->setReturnParameter ( "err_code_des", $this->getError () ); // （err_code_des管业务）业务结果失败原因描述，商户下单未成功
						}
					} else {
						// 下单不成功
						$this->nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); 	// （return_code管通信）返回服务器处理微信状态码结果
						$this->nativeCall->setReturnParameter ( "result_code", "FAIL" ); 		// （result_code管业务）业务结果失败
						$this->nativeCall->setReturnParameter ( "err_code_des", "该商户繁忙、扫码支付没有准备好，请您稍后再试。" ); // （err_code_des管业务）业务结果失败原因描述，商户下单未成功
					}
				} else {
					$this->nativeCall->setReturnParameter ( "return_code", "SUCCESS" ); 		// （return_code管通信）返回服务器处理微信状态码结果
					$this->nativeCall->setReturnParameter ( "result_code", "FAIL" ); 			// （result_code管业务）业务结果失败
					$this->nativeCall->setReturnParameter ( "err_code_des", "此商品价格无效，无法支付，请联系商家调整价格。" ); 	// （err_code_des管业务）业务结果失败原因描述，商品价格不合法
				}
			}
		}
		
		$returnXml = $this->nativeCall->returnXml (); // 生成响应微信扫码的xml文件
		return $returnXml; // 返回微动扫码处理结果，交易完成
	}
	
	/**
	 * 根据商品编号获取商品信息（尤其是价格），日后要根据快表进行比对。
	 * @param string $product_id 商品编号
	 */
	private function getProductInfo($skuid = '') {
		$productinfo = array ();
		$skuinfo = array ();
		$fabinfo = array ();
		if (! empty ( $skuid )) {
			// 先查询总店商品表，没有结果就查询分店商品表，都没有，就返回没有该商品信息
			$skumap = array (
					'sizecolor_id' => $skuid,
					'is_del' => 0
			);
			$skuinfo = M ( 'productsizecolor' )->where ( $skumap )->find (); // 查找总店商品表商品信息
			if ($skuinfo) {
				$productmap = array (
						'product_id' => $skuinfo ['product_id'], // 商品编号
						'is_del' => 0, 
				);
				$fabinfo = M ( 'product' )->where ( $productmap )->find (); // 找到商品FAB信息
				
				$productinfo ['product_color'] = $skuinfo ['product_color'];
				$productinfo ['product_size'] = $skuinfo ['product_size'];
				
			} else {
				// 如果没找到，继续找寻分店商品信息
				$subskumap = array (
						'sub_sku_id' => $skuid, 
						'is_del' => 0,
				);
				$skuinfo = M ( 'subbranchsku' )->where ( $subskumap )->find (); // 查找分店商品表商品信息
				if ($skuinfo) {
					$subpromap = array (
							'sub_pro_id' => $skuinfo ['sub_pro_id'], // 分店商品编号
							'is_del' => 0,
					);
					$pid = M ( 'subbranchproduct' )->where ( $subpromap )->getField ( "product_id" ); // 找到商品FAB信息
					// 再查找商品信息
					$productmap = array (
							'product_id' => $pid, // 商品编号
							'is_del' => 0,
					);
					$fabinfo = M ( 'product' )->where ( $productmap )->find (); // 找到商品FAB信息
					
					$productinfo ['product_color'] = $skuinfo ['sku_color'];
					$productinfo ['product_size'] = $skuinfo ['sku_size'];
					
				}
			}
		}
		if (! empty ( $fabinfo )) {
			// 补充商品信息（下单用）
			$productinfo ['product_id'] = $fabinfo ['product_id']; 			// 商品主键
			$productinfo ['product_number'] = $fabinfo ['product_number']; 	// 商品编号
			$productinfo ['product_name'] = $fabinfo ['product_name']; 		// 商品名称
			$productinfo ['current_price'] = $fabinfo ['current_price']; 	// 商品价格
		}
		return $productinfo;
	}
	
	/**
	 * 扫码支付下单函数，传入6个形参。
	 * @param string $e_id 商家编号
	 * @param string $openid 微信用户openid
	 * @param number $is_subscribe 微信用户是否关注
	 * @param string $order_id 订单编号
	 * @param array $productinfo 扫码支付的商品信息
	 * @return array $orderinfo 下单信息（订单主表）
	 */
	private function scanOrder($e_id = '', $openid = '', $is_subscribe = "Y", $order_id = '', $productinfo = NULL) {
		$orderinfo = array (); // 扫码支付要下的订单信息
		// 商家、用户、订单、价格有一个缺少就直接下单不成功
		if (! empty ( $e_id ) && ! empty ( $openid ) && ! empty ( $order_id ) && ! empty ( $productinfo )) {
			// 检测参数通过，准备下单
			
			$receive_person = "路人甲"; 		// 收货人姓名默认路人
			if ($is_subscribe == "Y") {
				// 如果微信用户关注，则调取其信息
				$swc = A ( 'Service/WeChat' );
				$wechaterinfo = $swc->getUserInfo ( $openid ); // 获取微信用户信息
				$receive_person = $wechaterinfo ['nickname']; // 提取昵称
			}
			
			$delegate = false; // 默认不用代替用户注册
			$customertable = M ( 'customerinfo' ); // 实例化顾客表
			// 查询顾客表信息，如果无法查询到，直接为其注册生成一条信息
			$customermap = array (
					'e_id' => $e_id, 		// 当前商家
					'openid' => $openid, 	// 微信用户openid
					'is_del' => 0 			// 没有删除
			);
			$customerinfo = $customertable->where ( $customermap )->find (); // 尝试找寻顾客
			if (! $customerinfo) {
				// 如果没有用户信息，代注册一条顾客信息
				$delegate = true; 			// 本次下单是系统代理注册客户账号的
				$customerinfo = $this->delegateRegister ( $e_id, $openid, $receive_person ); // 系统代微信用户注册一条消息
			}
			
			// 准备事务过程变量
			$maintable = M ( 'ordermain' ); 							// 实例化订单主表
			$detailtable = M ( 'orderdetail' ); 						// 实例化订单子表
			$statustable = M ( 'orderstatusrecord' ); 					// 实例化订单状态表
			$addmainresult = false; 									// 向订单那主表插入记录结果
			$adddetailresult = false; 									// 向订单子表插入记录结果
			$timenow = time (); 										// 取当前时间
			
			// 主表开始事务过程
			$maintable->startTrans ();
			
			// Step1：插入订单主表
			$maininfo = array (
					'order_id' => $order_id, 							// 订单主键
					'e_id' => $e_id, 									// 商家编号
					'mall_type' => 1, 									// 扫码支付暂定商城类型是云总店（没有分店、导购或分销商编号）
					'guide_id' => "-1", 								// 商城类型是云总店（没有导购编号，置为-1）
					'visual_number' => $timenow . randCode ( 4, 1 ), 	// 订单可视化编号
					'customer_id' => $customerinfo ['customer_id'], 	// 顾客编号
					'openid' => $openid, 								// 微信用户编号
					'order_time' => $timenow, 							// 下单时间
					'total_price' => $productinfo ['current_price'], 	// 订单总价
					'receive_person' => $receive_person, 				// 下单人昵称（微信昵称或路人）
					'pay_indeed' => $productinfo ['current_price'], 	// 微信用户实际支付价格
					'pay_method' => 2, 									// 扫码支付就是微信支付
					'remark' => "微信openid为" . $openid . "的用户在" . timetodate ( $timenow ) . "原生扫码支付该订单。", // 用户扫码支付
					'status_flag' => 0, 								// 订单处于正常流水状态（2.0版本新增订单流水状态）
					'normal_status' => 0, 								// 正常态0代表订单代付款（2.0版本新增订单流水状态）
					'refund_status' => 0, 								// 正常态的订单无退款异常（2.0版本新增订单流水状态）
			); // 订单主表信息
			
			// Step2：插入订单子表
			$detailinfo = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ),
					'order_id' => $order_id,
					'product_id' => $productinfo ['product_id'], 		// 要购买的商品编号
					'unit_price' => $productinfo ['current_price'], 	// 单价就是商品现价
					'amount' => 1, 										// 特别注意：默认一次扫码只进行一次购买
					'pro_size' => $productinfo ['product_size'], 		// 默认购买第一个sku（以后要调整）
					'pro_color' => $productinfo ['product_color'], 		// 默认购买第一个sku（以后要调整）
					'remark' => "扫码支付 " . $productinfo ['product_number'] . " " . $productinfo ['product_name'] . " " . $productinfo ['current_price'] . "元。",
			); // 订单子表信息（应该是一件商品，一个数量）
			
			// Step3：插入订单流水状态表
			$statusinfo = array (
					'record_id' => md5 ( uniqid ( rand (), true ) ), 	// 流水表主键
					'e_id' => $e_id, 									// 当前商家
					'mall_type' => 1, 									// 云总店订单流水
					'order_id' => $order_id, 							// 这笔订单的主单编号
					'status_flag' => 0, 								// 订单处于正常流水状态（2.0版本新增订单流水状态）
					'normal_status' => 0, 								// 正常态0代表订单代付款（2.0版本新增订单流水状态）
					'refund_status' => 0, 								// 正常态的订单无退款异常（2.0版本新增订单流水状态）
					'add_time' => time (), 								// 订单流水添加的时间
					'remark' => "顾客在" . timetodate ( time () ) . "扫码下单，记录订单流水。", // 订单流水原因
			);
			
			// Step4：执行订单事务
			$addmainresult = $maintable->add ( $maininfo ); 			// 插入订单主表
			$adddetailresult = $detailtable->add ( $detailinfo ); 		// 插入订单子表
			$addstatusresult = $statustable->add ( $statusinfo ); 		// 插入订单流水状态表
			
			if ($addmainresult && $adddetailresult && $addstatusresult) {
				$maintable->commit (); 									// 提交扫码支付事务
				$orderinfo = $maininfo; 								// 把扫码下单生成的信息给到订单信息中
			} else {
				$maintable->rollback(); 								// 回滚扫码支付事务
			}
		}
		return $orderinfo;
	}
	
	/**
	 * 系统替用户代注册一条记录。
	 * @param string $e_id 系统代注册的商家编号
	 * @param string $openid 系统代注册的微信用户openid
	 * @param string $nickname 微信接口获取（关注的）或者没获取（路人）的昵称
	 * @return array $customerinfo 顾客信息
	 */
	private function delegateRegister($e_id = '', $openid = '', $nickname = '') {
		$customerinfo = array (
				'customer_id' => date ( "YmdHis" ) . randCode ( 4, 1 ), // 顾客编号
				'openid' => $openid, // 顾客微信编号
				'nick_name' => $nickname, // 用户昵称（有两种情况）
				'e_id' => $e_id, // 商家编号
				'register_time' => time (), // 代注册时间
				'remark' => "系统代扫码支付用户进行注册", // 备注代注册
		); // 代注册用户信息
		$addresult = M ( 'customerinfo' )->add ( $customerinfo ); // 插入这样一条代注册信息
		return $customerinfo;
	}
	
}

/**
 * 微动微信订单类，处理微信端订单的类。
 * 在该类的基础上，类似WeActMicroPay、WeActPayNotify、WeActRefund和WeActCheckBill都需要操作微信订单。
 *
 */
class WeActWechatOrder extends WeActSafePayHelper {
	
	/**
	 * 订单查询上下文类对象。
	 * @var object $orderquerycontext
	 */
	protected $orderquerycontext;
	
	/**
	 * 微动微信订单类构造函数。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类构造函数读取支付信息
		$this->orderquerycontext = new OrderQueryContext ( $this->securityinfo ); // 生成本类的对象
	}
	
	/**
	 * ==========对外接口函数部分==========
	 */
	
	/**
	 * 从微信服务器查询订单状态。
	 * @param array $queryinfo 订单查询条件
	 * @property string out_trade_no 商户订单号
	 * @property string transaction_id 微信服务器订单号
	 * @return array $orderinfo 从微信服务器查询到的订单信息
	 */
	public function queryWechatOrder($queryinfo = NULL) {
		$orderinfo = $this->orderquerycontext->queryWechatOrderInfo ( $queryinfo ); // 查询微信订单信息
		return $orderinfo;
	}
	
}

/**
 * 微动刷卡支付类，继承自安全基类。
 * 这是微信新增的一种支付模式，微动相应的类处理业务逻辑。
 */
class WeActMicroPay extends WeActWechatOrder {
	
	/**
	 * 刷卡支付微信SDK类对象。
	 * @var object $micropay
	 */
	private $micropay;
	
	/**
	 * 刷卡支付类构造函数。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 重要：先调用直接父类的构造函数，先初始化securityinfo和orderquery2个类对象
		$this->micropay = new MicroPay_pub ( $this->securityinfo ); // 再生成本类的扫码支付对象
		// 特别注意：刷卡支付是一件很实时的事情，所以这里超时时间设置为5秒
		$this->micropay->setCURL_TIMEOUT ( 5 ); 
	}
	
	/**
	 * ==========对外接口函数部分，后台同步轮询刷卡支付方式==========
	 */
	
	/**
	 * 刷卡支付的对外接口：处理微动终端IPOS或门店系统的刷卡支付。
	 * 该接口为后台执行同步轮询刷卡的方法。
	 * 特别注意：主动POST得到响应接口无需验证签名，被动接收消息需要验证签名。
	 * @param string $order_id 刷卡支付的订单号
	 * @param string $auth_code 扫描获取用户的授权刷卡码
	 * @return boolean array|false 刷卡支付结果，刷卡成功，返回订单交易信息|刷卡失败返回false
	 */
	public function cardMicropay($order_id = '', $auth_code = '') {
		
		$orderinfo = $this->getOrderMainInfo ( $order_id ); // 得到刷卡的订单信息
		
		$paydescription = "测试刷卡支付订单" . $orderinfo ['visual_number']; 				// 刷卡支付描述
		$total_fee = $orderinfo ['pay_indeed'] * 100; 								// 刷卡支付金额，单位为分
		
		// 设置必填参数
		$this->micropay->setParameter ( "out_trade_no", $order_id ); 				// 商户订单号
		$this->micropay->setParameter ( "body", $paydescription ); 					// 商品或支付单简要描述
		$this->micropay->setParameter ( "total_fee", $total_fee ); 					// 总金额
		$this->micropay->setParameter ( "auth_code",  $auth_code ); 				// 扫码支付授权码，设备读取用户微信中的条码或者二维码信息
		
		// 非必填参数，商户可根据实际情况选填
		//$this->micropay->setParameter ( "detail", $detail ); 						// 商品名称明细列表
		//$this->micropay->setParameter ( "attach", $attach ); 						// 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
		//$this->micropay->setParameter ( "fee_type", $fee_type ); 					// 符合ISO4217标准的三位字母代码，默认人民币：CNY
		//$this->micropay->setParameter ( "goods_tag", $goods_tag ); 				// 商品标记，代金券或立减优惠功能的参数，说明详见
		//$this->micropay->setParameter ( "limit_pay", $limit_pay ); 				// $limit_pay == no_credit--指定不能使用信用卡支付
		
		// 得到刷卡支付结果
		$micropayresult = $this->micropay->getPostResult ( "micropay" ); 			// 调用接口返回数组结果（返回的openid是付款的微信用户，在auth_code里关联用户）
		
		$return_code = $micropayresult ['return_code']; 	// 提取接口通信码（根据这个判断与微信服务器通信是否成功）
		$return_msg = $micropayresult ['return_msg']; 		// 提取接口通信结果信息
		$result_code = $micropayresult ['result_code']; 	// 提取交易业务处理结果（根据这个判断交易是否成功）
		$out_trade_no = $micropayresult ['out_trade_no']; 	// 提取刷卡支付的交易单号
		
		//①如果微信返回成功
		if (! $return_code || ! $result_code || ! $out_trade_no) {
			throw new SDKRuntimeException ( "接口调用失败，请确认是否输入是否有误！" . "<br>" );
			return false;
		}
		
		//②、接口调用成功，非用户支付中或系统故障情况下，如果返回成功但业务失败，说明业务明确返回失败
		if ($return_code == "SUCCESS" && $result_code == "FAIL") {
			$err_code = $micropayresult ["err_code"]; // 提取可能存在的错误码
			if ($err_code != "USERPAYING" && $err_code != "SYSTEMERROR") {
				return false;
			}
		}
		
		//③、确认支付是否成功
		$queryTimes = 10;
		while ($queryTimes > 0) {
			$succResult = 0;
			$queryResult = $this->queryMicropay ( $out_trade_no, $succResult ); // $succResult这个参数是被引用的
			// 如果需要等待1s后继续
			if ($succResult == 2) {
				sleep(2); // 线程等待2秒继续轮询
				continue;
			} else if ($succResult == 1) {
				return $queryResult; // 查询成功，如果订单交易成功，这里直接返回了
			} else {
				break; //订单交易失败
			}
			$queryTimes--;
		}
		
		//④、代码走到这里，10次轮询确认失败，则撤销订单，而且刷卡支付是失败的。
		if (! $this->reverseMicropay ( $out_trade_no )) {
			throw new SDKRuntimeException ( "撤销单失败！" . "<br>" );
		}
		
		return false;
	}
	
	/**
	 * 不断轮询刷卡支付的订单情况
	 * @param string $out_trade_no  商户订单号
	 * @param int $succCode         查询订单结果
	 * @return 0 订单不成功，1表示订单成功，2表示继续等待
	 */
	private function queryMicropay($out_trade_no, &$succCode) {
		$result = $this->queryWechatOrder ( $out_trade_no ); // 查询微信订单状态
		
		// 处理订单查询结果
		if ($result ["return_code"] == "SUCCESS" && $result ["result_code"] == "SUCCESS") {
			if ($result ["trade_state"] == "SUCCESS") {
				//支付成功
				$succCode = 1;
				return $result; // 支付成功直接返回订单状态
			} else if ($result ["trade_state"] == "USERPAYING") {
				//用户支付中
				$succCode = 2;
				return false;
			}
		} else if ($result["err_code"] == "ORDERNOTEXIST") {
			//如果返回错误码为“此交易订单号不存在”则直接认定失败
			$succCode = 0;
		} else {
			$succCode = 2; // 如果是系统错误，则后续继续查询与等待
		}
		return false;
	}
	
	/**
	 * 撤销订单，如果失败需要recall则会递归调用10次。
	 * @param string $out_trade_no 需要撤单的商户订单号
	 * @param 调用深度 $depth
	 * @return boolean true|false 撤单结果
	 */
	private function reverseMicropay($out_trade_no, $depth = 0) {
		if ($depth > 10) {
			return false; // 超过10次不再递归调用
		}
		
		$clostOrder = new WxPayReverse();
		$clostOrder->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::reverse($clostOrder);
		
		// 接口调用失败
		if ($result["return_code"] != "SUCCESS"){
			return false; // 接口调用失败直接返回false
		}
		
		// 如果结果为success且不需要重新调用撤销，则表示撤销成功
		if ($result ["result_code"] == "SUCCESS" && $result ["recall"] == "N") {
			return true;
		} else if ($result ["recall"] == "Y") {
			return $this->reverseMicropay ( $out_trade_no, ++$depth ); // 需要递归调用继续撤销订单，则递归调用自己继续撤销
		}
		return false;
	}
	
	/**
	 * ==========对外接口函数部分，前后台异步轮询刷卡支付方式==========
	 */
	
	/**
	 * 刷卡支付的对外接口：处理微动终端IPOS或门店系统的刷卡支付。
	 * 该接口为前后台异步执行轮询刷卡的方法。
	 * 特别注意：主动POST得到响应接口无需验证签名，被动接收消息需要验证签名。
	 * @param string $order_id 刷卡支付的订单号
	 * @param string $auth_code 扫描获取用户的授权刷卡码
	 * @return boolean array|false 刷卡支付结果，刷卡成功，返回订单交易信息|刷卡失败返回false
	 */
	public function asyncCardMicropay($order_id = '', $auth_code = '') {
		
	}
	
}

/**
 * 微动微信支付通知类，继承自安全支付基类。
 * 本类接收微信支付通知，验证签名后完成记录、订单流水、支付通知等业务逻辑。
 */
class WeActPayNotify extends WeActWechatOrder {
	
	/**
	 * 定义本类私有对象paynotify
	 * @var object $paynotify
	 */
	private $paynotify;
	
	/**
	 * 微动微信支付类构造函数。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 重要：先调用直接父类的构造函数，先初始化securityinfo和orderquery2个类对象
		$this->paynotify = new Notify_pub ( $this->securityinfo ); 			// 生成本类的微信支付通知类对象
	}
	
	/**
	 * ==========业务逻辑函数==========
	 */
	
	/**
	 * 检查一笔订单交易成功的通知在数据库中是否存在
	 * @param string $transaction_id 微信支付交易编号
	 * @param string $out_trade_no 原订单编号
	 * @return boolean $exist 返回是否存在true|false
	 */
	private function checkNotifyExist($transaction_id = '', $out_trade_no = '') {
		$exist = false; // 默认数据库中并不存在这样一笔记录
		if (! empty ( $transaction_id ) && ! empty ( $out_trade_no )) {
			$checkinfo = array(
					'transaction_id' => $transaction_id, // 这笔微信交易编号
					'out_trade_no' => $out_trade_no, // 这笔订单
					'return_code' => 'SUCCESS', // 交易成功的通知
					'is_del' => 0
			);
			$exist = M ( 'wechatpaynotify' )->where ( $checkinfo )->find (); // 检测数据库中是否存在这样的支付通知
		}
		return $exist;
	}
	
	/**
	 * 记录微动平台某商家的一笔新的微信交易信息。
	 * @param string $e_id 商家编号
	 * @param array $newnotifyinfo 新微信交易通知信息
	 * @return boolean $recordsuccess 返回是否记录交易成功的通知成功 true|false
	 */
	private function recordNewNotify($newnotifyinfo = NULL) {
		$recordsuccess = false; // 默认新通知并没有记录到数据库
		if (! empty ( $newnotifyinfo )) {
			$notifyrecord = array (
					'paynotify_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $this->securityinfo ['e_id'],
					'appid' => $newnotifyinfo ['appid'],
					'mch_id' => $newnotifyinfo ['mch_id'],
					'nonce_str' => $newnotifyinfo ['nonce_str'],
					'sign' => $newnotifyinfo ['sign'],
					'result_code' => $newnotifyinfo ['result_code'], // result_code才是业务成功与否的处理
					'err_code' => $newnotifyinfo ['err_code'],
					'err_code_des' => $newnotifyinfo ['err_code_des'],
					'openid' => $newnotifyinfo ['openid'],
					'is_subscribe' => $newnotifyinfo ['is_subscribe'],
					'trade_type' => $newnotifyinfo ['trade_type'],
					'bank_type' => $newnotifyinfo ['bank_type'],
					'total_fee' => $newnotifyinfo ['total_fee'],
					'coupon_fee' => $newnotifyinfo ['coupon_fee'],
					'fee_type' => $newnotifyinfo ['fee_type'],
					'transaction_id' => $newnotifyinfo ['transaction_id'],
					'out_trade_no' => $newnotifyinfo ['out_trade_no'],
					'attach' => $newnotifyinfo ['attach'],
					'time_end' => $newnotifyinfo ['time_end'],
					'return_code' => $newnotifyinfo ['return_code'],
					'return_msg' => $newnotifyinfo ['return_msg'],
					'sub_mch_id' => $newnotifyinfo ['sub_mch_id'],
					'receive_time' => time (), 
			);
			$recordsuccess = M ( 'wechatpaynotify' )->add ( $notifyrecord ); // 向数据库中加入支付信息
		}
		return $recordsuccess;
	}
	
	/**
	 * ==========对外接口函数部分==========
	 */
	
	/**
	 * 处理微信支付通知。
	 * @param xml $xml 微信支付通知发来的数据
	 * @return xml $handleresult 微信支付通知处理结果
	 */
	public function notifyHandle($xml = NULL) {
		$this->paynotify->saveData ( $xml, "notify" ); // 解析微信发来的支付通知xml数据
		
		/**
		 * Step1：验证签名，并回应微信。
		 * 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		 * 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		 * 尽可能提高通知的成功率，但微信不保证通知最终能成功。
		 */
		
		if ($this->paynotify->checkSign () == FALSE) {
			$this->paynotify->setReturnParameter ( "return_code", "FAIL" ); 		// 验证微信签名失败，返回状态码
			$this->paynotify->setReturnParameter ( "return_msg", "签名失败" ); 		// 验证微信签名失败，返回信息
		} else {
			$this->paynotify->setReturnParameter ( "return_code", "SUCCESS" ); 	// 验证微信签名成功，设置返回码
			
			/**
			 * Step2：商户根据实际情况设置相应的处理流程，并回应微信。
			 */
			$notifyinfo = $this->paynotify->getData (); // 取出data信息进行处理
			
			$notifyrecordexist = $this->checkNotifyExist ( $notifyinfo ['transaction_id'], $notifyinfo ['out_trade_no'] ); // 检测交易通知信息是否存在
			if (! $notifyrecordexist) {
				$newrecordok = $this->recordNewNotify ( $notifyinfo ); // 将新交易通知记录到数据库
				if (! $newrecordok) {
					$this->paynotify->setReturnParameter ( "return_code", "FAIL" ); 					// 微信通知存入数据库失败，返回状态码
					$this->paynotify->setReturnParameter ( "return_msg", "记录失败，请求微信重发通知。" ); 	// 微信通知存入数据库失败，返回信息
				}
			}
			
			/**
			 * Step3：如果result_code和return_code都是success，并且trade_type是native。
			 * 特别注意：2015/07/11 18:33:25 增加：微信服务器开始卡壳了，这里就直接做到跟扫码一样，等收到通知的时候再去改订单支付状态。
			 * $trade_type = $notifyinfo ['trade_type']; // 如果是NATIVE直接置为已支付（因为没有订单回跳），现在JSAPI也这么做（为了防止微信服务器延迟，导致责任在微动）
			 */
			$result_code = $notifyinfo ['result_code']; // 微信返回码
			$return_code = $notifyinfo ['return_code']; // 微信业务结果
			if ($result_code == "SUCCESS" && $return_code == "SUCCESS") {
				// 如果两个都成功，代表支付成功，才去改变订单状态，NATIVE/JSAPI一起来处理
				
				$ordermaintable = M ( 'ordermain' ); 			// 订单主表
				$statustable = M ( 'orderstatusrecord' ); 		// 订单状态表
				$notifyorderid = $notifyinfo ['out_trade_no']; 	// 被通知支付成功的订单编号
		
				// 查询收到通知的订单信息
				$ordermap = array (
						'order_id' => $notifyorderid,
						'is_del' => 0
				);
				$ordermaininfo = $ordermaintable->where ( $ordermap )->find (); // 查询订单信息
				// 有订单信息且未支付状态下才去改变支付状态，已经支付的情况下就不会更改了
				// 适应微信文档上的那句话：由于存在重新发送后台通知的情况，因此同样可能会多次给商户系统。 系统必须能够正确处理重复的通知。
				if ($ordermaininfo) {
					if ($ordermaininfo ['status_flag'] == 0 && $ordermaininfo ['normal_status'] == 0) {
						// 如果这笔订单处于正常流程、待支付，则进行状态更改
						
						$ordermaintable->startTrans (); 		// 开启支付记录事务过程
						
						// Step1：更改订单支付状态
						$ordermaininfo ['normal_status'] = 1; 	// 正常态的订单已支付
						$ordermaininfo ['pay_time'] = time (); 	// 记录支付时间
						$payresult = $ordermaintable->save ( $ordermaininfo ); // 更新所支付订单信息
						
						// Step2：记录支付订单流水（被动通知支付成功）
						$statusinfo = array (
								'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
								'e_id' => $this->securityinfo ['e_id'], 					// 被通知的商家
								'mall_type' => $ordermaininfo ['mall_type'], 				// 属于哪个商城
								'order_id' => $notifyorderid, 								// 这笔订单的主键（被通知支付成功的订单编号）
								'status_flag' => 0, 										// 订单处于正常流水状态
								'normal_status' => 1, 										// 订单处于刚支付成功状态（2.0版本新增订单流水状态）
								'refund_status' => 0, 										// 订单刚支付成功肯定不会退款（2.0版本新增订单流水状态）
								'add_time' => time (), 										// 订单流水添加的时间
								'remark' => "顾客在" . timetodate ( time () ) . "使用微信支付该订单已得到微信服务器成功通知，记录订单流水。", // 订单流水原因
						);
						$statusresult = $statustable->add ( $statusinfo ); 					// 增加一笔取消订单的订单流水
						
						// 处理支付流水事务结果
						if ($payresult && $statusresult) {
							$ordermaintable->commit (); // 提交事务
							
							$einfo = $this->getEnterpriseInfo ( $this->securityinfo ['e_id'] ); // 商家信息
							
							// Step4：发送微信模板消息通知
							$domain = C ( 'DOMAIN' ); // 提取域名
							$fontcolor = "#00C957"; // 支付成功/翠绿色的字体颜色
							$tpldata = array (
									'total_price' => $ordermaininfo ['total_price'] . "元",
									'product_name' => "线上商城交易" . "商品...",
									'receive_address' => $ordermaininfo ['receive_address'],
									'visual_number' => $ordermaininfo ['visual_number'],
							);
							$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->securityinfo ['e_id'];
							// 策略模式发送付款微信模板消息
							$paynotify = new PayNotify ( $tpldata, $url, $fontcolor ); // 付款通知
							$msgwechater = new MsgToWechater ( $paynotify ); // 上下文类对象
							$sendresult = $msgwechater->sendMsgToWechater ( $einfo, $ordermaininfo ['openid'] ); // 发送模板消息
							
							// Step5：发送短信通知
							$type = 'PAYSUCCESS';
							$telNum = $ordermaininfo ['receive_tel'];
							$ename = $einfo ['e_name'];
							$orderNumber = $ordermaininfo ['visual_number'];
							$mobileMsg = new mobileMessage ();
							$sendresult = $mobileMsg->sendMsgNotify ( $telNum, $type, $ename, $orderNumber ); // 发送消息
							
						} else {
							$ordermaintable->rollback (); // 回滚事务
							$this->paynotify->setReturnParameter ( "return_code", "FAIL" ); 						// 微动响应微信支付失败，返回状态码
							$this->paynotify->setReturnParameter ( "return_msg", "订单业务处理失败，请求微信重发通知。" ); 	// 微动响应微信支付失败，返回信息
						}
					}
				} // if ($ordermaininfo)
			} // if double SUCCESS
		} // if wechat sign ok
		
		$returnXml = $this->paynotify->returnXml (); 	// 生成返回给微信的信息
		exit ( $returnXml ); 					// 通知微信平台接收情况
	}
	
}

/**
 * 微动refund微信支付退款类，继承自安全支付基类。
 * 微动平台订单微信退款使用此类。
 */
class WeActRefund extends WeActWechatOrder {
	
	/**
	 * 订单微信退款上下文环境。
	 * @var object $orderrefundcontext
	 */
	protected $orderrefundcontext;
	
	/**
	 * 微动微信退款类构造函数。
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类构造函数读取支付信息
		$this->orderrefundcontext = new OrderRefundContext ( $this->securityinfo ); 	// 构建统一下单上下文对象
	}
	
	/**
	 * ============共有函数部分=============
	 */
	
	/**
	 * 为一笔订单进行微信退款，退款金额是元，不是微信支付的分！
	 * 特别注意：调用本公有函数为某订单退款，无需更新订单退款状态、记录日志文件和退款信息，
	 * 这些步骤由系统一并托管完成，调用退款函数时只需关心退款前后业务流程即可。
	 * @param string $order_id （要退款的）订单编号
	 * @param double $refundmoney （要退款的）金额（元），支持部分退款。但注意退款金额是元，不是微信支付的分！
	 * @return boolean $refundsuccess 退款是否成功
	 */
	public function orderRefund($order_id = '', $refundmoney = 1) {
		$refundsuccess = false; // 默认退款不成功
		// 存在要退款的金额才进行退款
		if ($refundmoney > 0) {
			$orderinfo = $this->getOrderMainInfo ( $order_id ); // 得到订单主单信息
			if (! $orderinfo || $orderinfo ['pay_indeed'] < $refundmoney || ($orderinfo ['status_flag'] == 1 && $orderinfo ['normal_status'] >= 2)) {
				return false; // 没有订单信息或订单已经被删除、退款金额超出、甚至被退过款，都无法退款
			}
			
			// 准备订单退款参数
			$out_trade_no = $order_id; 									// 发生交易的订单号
			$out_refund_no = md5 ( uniqid ( rand (), true ) ); 			// 商户退款单号，商户自定义主键（商户退款单号，32 个字符内、可包含字母,确保在商户系统唯一。同个退款单号多次请求，财付通当一个单处理，只会退一次款。如果出现退款不成功，请采用原退款单号重新发起，避免出现重复退款。）
			$total_fee = intval ( $orderinfo ['pay_indeed'] * 100 ); 	// 总金额需与订单号out_trade_no对应，这里是订单实际支付的价格换算成分
			$refund_fee = intval ( $refundmoney * 100 ); 				// 要退款的金额（元乘以100换算成分，然后就可以退款微信支付了）
			//$password = "7iSm2HvKT5"; 								// 操作员密码（1.1版本以后需要md5后POST），这个参数V2版本以后已经不用了
			
			$refundinfo = array (
					'out_trade_no' => $out_trade_no, 
					'out_refund_no' => $out_refund_no, 
					'total_fee' => $total_fee, 
					'refund_fee' => $refund_fee, 
			);
			$refundResult = $this->orderrefundcontext->orderRefund ( $refundinfo ); // 调用订单微信退款接口
			
			// 处理退款结果
			$refundinfo = array (); // 最后返回给调用的处理结果
			// 先看与微信的通信结果
			if ($refundResult ["return_code"] == "FAIL") {
				// 通信出错
				$refundinfo ['errCode'] = 10001; // 通信错误
				$refundinfo ['errMsg'] = "与微信平台通信出错：" . $refundResult ['return_msg'] . " 微信平台可能正忙！"; // 通信出错的错误信息
			} else {
				// 通信不出错，看业务结果
				if ($refundResult ['result_code'] == "SUCCESS") {
					// 如果退款业务也正确，则把该笔订单设置成已经退款，附带退款金额
					$refundinfo ['errCode'] = 0 ; 										// 没有错误码
					$refundinfo ['errMsg'] = "ok"; 										// 没有错误信息
					
					// 启动事务过程，更新订单退款信息、记录微信退款结果
					$maintable = M ( "ordermain" ); 		// 实例化订单主表
					$refundtable = M ( "wechatrefund" ); 	// 实例化微信退款记录表
					$updateresult = false; 					// 微动数据库退款更新结果，默认false
					$recordresult = false; 					// 记录成功的微信退款结果到数据库，默认false
					
					$maintable->startTrans (); 
					
					// Part1：更新退款信息
					$updateinfo = array (
							'status_flag' => 1, 										// 订单退款状态（不管是不是退款状态，更新为退款状态）
							'refund_status' => 2, 										// 订单已经成功退款（状态更新到2）
							'refund_fee' => $refundmoney, 								// 订单退款金额（元，不是分）
							'refund_time' => time (), 									// 订单退款时间
					);
					// Part2：更新条件
					$refundmap = array (
							'order_id' => $order_id, // 当前退款订单
							'is_del' => 0, // 没有被删除的订单
					);
					$updateresult = $maintable->where ( $refundmap )->save ( $updateinfo ); // 将退款信息更新到订单主表中
					
					// 记录一笔退款记录
					$newrefund = array (
							'refund_id' => md5 ( uniqid ( rand (), true ) ), 			// 退款记录主键
							'appid' => $refundResult ['appid'], 						// 公众账号开发者appid
							'mch_id' => $refundResult ['mch_id'], 						// 微信支付商户号
							'sub_mch_id' => $refundResult ['sub_mch_id'], 				// 微信支付子商户号（不一定用得着）
							'device_info' => $refundResult ['device_info'], 			// 微信支付退款设备号（如果事先不绑定不一定有返回值）
							'sign' => $refundResult ['sign'], 							// 签名
							'transaction_id' => $refundResult ['transaction_id'], 		// 微信订单号
							'out_trade_no' => $refundResult ['out_trade_no'], 			// 商户系统内部的订单号,唯一标识一笔订单
							'out_refund_no' => $refundResult ['out_refund_no'], 		// 商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔.
							'total_fee' => $total_fee, 									// 订单总金额，单位为分
							'refund_fee' => $refundResult ['refund_fee'], 				// 退款总金额，单位为分,可以做部分退款,但是不能超过总金额
							'op_user_id' => $this->refund->config_mchid, 				// 操作员帐号, 默认为商户号
							'return_code' => $refundResult ["return_code"], 			// 退款返回状态码
							'return_msg' => $refundResult ["return_msg"], 
							'result_code' => $refundResult ['result_code'], 			// 业务结果：SUCCESS/FAIL SUCCESS代表退款申请接收成功，结果通过退款查询接口查询
							'err_code' => 0, 											// 错误码（这里记录的一定是成功的退款）
							'err_code_des' => "ok", 									// 错误代码描述（这里记录的一定是成功的退款）
							'refund_count' => 1, 										// 退款笔数（默认一笔订单退款一笔）
							'refund_channel' => "BALANCE", 								// ORIGINAL—原路退款，默认BALANCE—退回到余额
							'coupon_refund_fee' => $refundResult ['coupon_refund_fee'], // 现金券退款金额<=退款金额，退款金额-现金券退款金额为现金
							'refund_status' => "PROCESSING", 							// SUCCES—退款成功 FAIL—退款失败 PROCESSING—退款处理中 NOTSURE—未确定
							'add_time' => time (), 										// 记录退款的时间
					);
					$recordresult = $refundtable->add ( $newrefund ); 						// 插入一条退款记录
					
					// 处理事务结果
					if ($updateresult && $recordresult) {
						$maintable->commit (); 												// 提交事务
						$refundinfo ['local_refund'] = 1; 									// 微动本地数据库退款结果（调用业务可以根据此字段判断业务是否成功）
					} else {
						$maintable->rollback (); 											// 回滚事务
						$refundinfo ['local_refund'] = 0; 									// 微动更新订单、记录退款的处理结果
					}
				} else {
					$refundinfo ['errCode'] = $refundResult ['err_code']; 					// 业务错误代码（为0代表正确）
					$refundinfo ['errMsg'] = "退款错误描述：" . $refundResult ['err_code_des']; 	// 业务错误原因
				}
				// 一并返回退款信息给调用
				$refundinfo ['data'] = array (
						'result_code' => $refundResult ['result_code'], 				// 退款业务处理结果，成功为SUCCESS
						'appid' => $refundResult ['appid'], 							// 公众账号ID
						'mch_id' => $refundResult ['mch_id'], 							// 商户号
						'sub_mch_id' => $refundResult ['sub_mch_id'], 					// 子商户号
						'device_info' => $refundResult ['device_info'], 				// 设备号
						'sign' => $refundResult ['sign'], 								// 签名
						'transaction_id' => $refundResult ['transaction_id'], 			// 微信订单号
						'out_trade_no' => $refundResult ['out_trade_no'], 				// 商户订单号
						'out_refund_no' => $refundResult ['out_refund_no'], 			// 商户退款单号
						'refund_id' => $refundResult ['refund_id'], 					// 微信退款单号
						'refund_channel' => $refundResult ['refund_channel'], 			// 退款渠道
						'refund_fee' => $refundResult ['refund_fee'], 					// 退款金额
						'coupon_refund_fee' => $refundResult ['coupon_refund_fee'], 	// 现金券退款金额
				); // 其他退款信息
			}
			return $refundinfo;
		}
	}
	
	/**
	 * 查询退款状态。
	 * @param string $order_id 要查询的订单编号
	 * @return array $refundqueryresult 订单退款查询信息
	 */
	public function queryRefund($order_id = '') {
		if (empty ( $order_id )) {
			return false;
		}
		$refundqueryinfo ['out_trade_no'] = $order_id; // 商户订单号
		$refundqueryresult = $this->orderrefundcontext->orderRefundQuery ( $refundqueryinfo ); // 调用订单微信退款查询接口
		return $refundqueryresult;
	}
	
}

/**
 * 微动CheckBill对账单类，继承自安全支付基类。
 * 该类完成对商家订单对账、补单等一系列操作。
 */
class WeActCheckBill extends WeActWechatOrder {
	
	/**
	 * 对账单对象。
	 * @var object $checkbill
	 */
	private $checkbill;
	
	/**
	 * 对账单类构造函数。
	 * @param string $e_id 要对账的商家编号
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类：微动订单类构造函数
		$this->checkbill = new DownloadBill_pub ( $this->securityinfo ); // 构建本类对账单对象
	}
	
}

/**
 * 微动红包类。
 * 该类完成企业对粉丝的红包发放。
 */
class WeActRedPackage extends WeActSafePayHelper {
	
	/**
	 * 定义本类私有对象微信红包。
	 * @var object $redpackage
	 */
	private $redpackage;
	
	/**
	 * 传入企业编号。
	 * @param string $e_id
	 * @return boolean
	 */
	public function __construct($e_id = '') {
		parent::__construct ( $e_id ); // 调用父类构造函数
		$this->redpackage = new RedPackage_pub ( $this->securityinfo ); // 生成本类的对象
	}
	
	/**
	 * 发送红包给用户。
	 * @param string $openid 用户openid 要领取红包的微信用户。
	 * @param array $redpackageinfo 要发送的红包
	 * @return boolean $sendresult 发送结果
	 */
	public function sendRedPackageToWechater($openid = '', $redpackageinfo = NULL) {
		
		// 设置发送红包的参数
		$this->redpackage->setParameter ( "mch_billno", $this->securityinfo ['mchid'] . date ( "Ymd" ) . randCode ( 10, 1 ) );
		$this->redpackage->setParameter ( "send_name", $redpackageinfo ['send_name'] );
		$this->redpackage->setParameter ( "re_openid", $openid ); // 接收红包的微信用户openid
		$this->redpackage->setParameter ( "total_amount", $redpackageinfo ['total_amount'] );
		$this->redpackage->setParameter ( "total_num", $redpackageinfo ['total_num'] );
		$this->redpackage->setParameter ( "wishing", $redpackageinfo ['wishing'] );
		$this->redpackage->setParameter ( "client_ip", $redpackageinfo ['client_ip'] ); // 发送红包的服务器IP地址
		$this->redpackage->setParameter ( "act_name", $redpackageinfo ['act_name'] );
		$this->redpackage->setParameter ( "remark", $redpackageinfo ['remark'] );
		
		$sendresult = $this->redpackage->getPostResult ( "redpackage", true ); // 使用证书发送红包并记录日志文件。
		
		// 处理结果
		
		return $sendresult; // 返回结果
		
	}
	
}

?>