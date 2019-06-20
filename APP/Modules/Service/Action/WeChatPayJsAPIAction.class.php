<?php
/**
 * 本控制器用于处理商户H5网页调起的微信支付。
 * @author 赵臣升。
 * CreateTime：2014/12/02 15:33:26.
 */
class WeChatPayJsAPIAction extends Action {
	
	/**
	 * ====================PART1：一些jsAPI微信支付的常量部分。======================
	 */
	
	/**
	 * =======【JSAPI路径设置】==============获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面=====================
	 * @var string $CONST_JS_API_CALL_URL 原生js回调微信获得code的地址
	 */
	var $CONST_JS_API_CALL_URL = 'http://www.we-act.cn/APP/Class/API/WeChatPayV3/demo/js_api_call.php';
	
	/**
	 * =======【JSAPI成功后NOTIFY路径设置】==============支付成功后微信通知地址=====================
	 * 据说在支付相同路径下无法接收???
	 * @var string $NOTIFY_URL JsAPI支付后获得微信通知的地址
	 */
	var $CONST_NOTIFY_URL = 'http://www.we-act.cn/weact/Home/WeChatPayCallback/notifyurl/e_id/';
	
	/**
	 * ====================PART2：私有函数部分，处理敏感信息和关键步骤。======================
	 */
	
	/**
	 * 私有函数：从微动数据库里查询本次需要jsAPI操作的支付信息。
	 * @param string $wcpid 微信支付编号
	 * @return array $wechatpayinfo 微信支付信息
	 */
	private function extractWeChatPayInfo($wcpid = '') {
		$wechatpayinfo = array();
		if(! empty ( $wcpid )) {
			$payinfomap = array(
					'wechatpay_id' => $wcpid,
					'is_del' => 0
			);
			$wechatpayinfo = M ( 'wechatpayrecord' )->where( $payinfomap )->find(); // 通过主键寻找待支付信息
		}
		return $wechatpayinfo;
	}
	
	/**
	 * 私有函数：获取企业支付敏感信息。
	 * @param string $e_id 企业编号
	 * @return array $securityinfo 企业安全保密信息（支付信息）
	 */
	private function getSecurityInfo($e_id = '') {
		$securityinfo = array(); // 企业的安全信息
		if(! empty ( $e_id )) {
			$securitytable = M ( 'secretinfo' );
			$security = array(
					'e_id' => $e_id,
					'is_del' => 0
			);
			$securityinfo = $securitytable->where ( $security )->find (); // 查找企业敏感信息
		}
		return $securityinfo;
	}
	
	/**
	 * 私有函数：由核心对外函数jsapiPayHandleV3调用，用以jsAPIPrepayId函数生成预支付id。
	 * 作用：生成jsAPI的UnifiedOrder_pub对象$unifiedOrder，并为其赋值后生成jsAPI的预支付prepayId。
	 * $securityinfo数组目前包含的6个敏感支付信息：key，sslcert_path，sslkey_path，appid，appsecret，mchid。
	 * @param array $securityinfo 商户的支付敏感信息
	 * @param string $openid 用户的openid，此参数在jsAPI方式支付的时候为必须参数
	 * @param array $wechatpayinfo 本次支付订单、商品等信息（如果愿意填写的话）
	 * @return string $prepay_id 返回jsAPI的统一支付对象生成的预支付参数$prepay_id
	 */
	private function jsAPIPrepayId($securityinfo = NULL, $openid = '', $wechatpayinfo = NULL) {
		import ( 'Class.API.WeChatPayV3.WxPayPubHelper.WxPayPlatformPubHelper', APP_PATH, '.php' );	// 载入微动平台微支付SDK
		
		$currentnotify = $this->CONST_NOTIFY_URL . $securityinfo ['e_id'];					// 拼接本次通知的地址：$currentnotify
		
		// =========步骤2：$unifiedOrder对象设置微信支付参数==================
		// jsAPI的必填参数，其中appid公众账号id, mch_id商户号, noncestr随机字符串, spbill_create_ip订单生成机器的IP（weact服务器地址）, sign签名等5项已填,商户无需重复填写
		$unifiedOrder = new UnifiedOrder_pub ( $securityinfo ); 							// 新建统一支付类
		$unifiedOrder->setParameter ( "openid", "$openid" ); 								// 用户的微信号openid,jsAPI下，此参数必须
		$unifiedOrder->setParameter ( "body", $wechatpayinfo ['body'] );					// 商品描述
		$unifiedOrder->setParameter ( "out_trade_no", $wechatpayinfo ['out_trade_no'] ); 	// 设置商户订单号，商户系统内部订单号，32个字符内、字母和数组，唯一性
		$unifiedOrder->setParameter ( "total_fee", $wechatpayinfo ['total_fee'] ); 			// 总金额，单位为分，不能带小数点
		$unifiedOrder->setParameter ( "notify_url", $currentnotify );						// 支付通知地址，接收微信支付成功通知
		$unifiedOrder->setParameter ( "trade_type", "JSAPI" ); 								// 本函数处理的交易类型:JSAPI（JSAPI、NATIVE、APP三种）
		// jsAPI的非必填参数，商户可根据实际情况选填
		// $unifiedOrder->setParameter("device_info","string32");							// 微信支付分配的终端设备号
		// $unifiedOrder->setParameter("attach","string127");								// 附加数据，原样返回
		// $unifiedOrder->setParameter("time_start","string14");							// 订单生成时间，yyyyMMddHHmmss，20091225091010，取自商户服务器
		// $unifiedOrder->setParameter("time_expire","string14");							// 订单失效时间，yyyyMMddHHmmss，格式同上，取自商户服务器
		// $unifiedOrder->setParameter("goods_tag","string32");								// 商品标记，该字段不能随便填，不适用请填写空
		// $unifiedOrder->setParameter("product_id","string32");							// 商品ID，只在trade_type为native时需要填写，id是二维码中商品ID，商户自行维护
		// $unifiedOrder->setParameter("sub_mch_id","string32");							// 子商户号（一般不用）
		if (isset ( $wechatpayinfo ['device_info'] )) $unifiedOrder->setParameter ( "device_info", $wechatpayinfo ['device_info'] );
		if (isset ( $wechatpayinfo ['attach'] )) $unifiedOrder->setParameter ( "attach", $wechatpayinfo ['attach'] );
		if (isset ( $wechatpayinfo ['time_start'] )) $unifiedOrder->setParameter ( "time_start", $wechatpayinfo ['time_start'] );
		if (isset ( $wechatpayinfo ['time_expire'] )) $unifiedOrder->setParameter ( "time_expire", $wechatpayinfo ['time_expire'] );
		if (isset ( $wechatpayinfo ['goods_tag'] )) $unifiedOrder->setParameter ( "goods_tag", $wechatpayinfo ['goods_tag'] );
		if (isset ( $wechatpayinfo ['product_id'] )) $unifiedOrder->setParameter ( "product_id", $wechatpayinfo ['product_id'] );
		if (isset ( $wechatpayinfo ['sub_mch_id'] )) $unifiedOrder->setParameter ( "sub_mch_id", $wechatpayinfo ['sub_mch_id'] );
		
		$prepay_id = $unifiedOrder->getPrepayId ();											// $unifiedOrder对象生成jsApi对象必须的重要参数：$prepay_id
		return $prepay_id;
	}
	
	/**
	 * 私有函数：检测jsapi支付信息是否完整（支付必要的字段信息是否存在），由callUpJsPayV3函数调用完成信息完整性的检测。
	 * @param array $jsapipayinfo jsapi支付信息数组
	 * @return boolean $completed true|false 返回jsapi支付信息是否完整
	 */
	private function jsAPIPayInfoCompleted($jsapipayinfo = NULL) {
		$completed = false; // 默认信息未完整
		if(! empty ( $jsapipayinfo )) {
			if (isset ( $jsapipayinfo ['body'] ) && isset ( $jsapipayinfo ['out_trade_no'] ) && isset ( $jsapipayinfo ['total_fee'] )) {
				$completed = true;
			}
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
		if(! empty ( $wechatpayinfo )) {
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
				$tobepaid = array(
						'wechatpay_id' => md5( uniqid( rand (), true ) ),
						'e_id' => $wechatpayinfo ['e_id'],
						'body' => $wechatpayinfo ['body'],
						'out_trade_no' => $wechatpayinfo ['out_trade_no'],
						'total_fee' => $wechatpayinfo ['total_fee'],
						'time_start' => $wechatpayinfo ['time_start'],
						'time_expire' => $wechatpayinfo ['time_end'],
						'openid' => $wechatpayinfo ['openid'],
						'create_time' => time ()
				); // 待支付信息
				$addpayresult = $payrectable->data ( $tobepaid )->add (); // 插入待支付信息
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
	 * 最核心的对外接口：商户JsAPI微支付处理函数jsapiPayHandleV3，本函数只提供对视图控制器生成最终支付参数的服务。
	 * 使用方法：在用户来到最终支付页面时，展示页面前由控制器调用生成最终的支付参数给支付页面。
	 * 特别注意：$payparameters是最终的订单支付参数，如果本函数执行完还是空值，说明商家的微支付信息没有准备好（如敏感的支付信息没有完整）。
	 * @param string $wechatpayid 微信待支付编号
	 * @return string $jsPayParameters 返回微信JSAPI支付的参数（json）
	 */
	public function jsapiPayHandleV3($wechatpayid = '') {
		$payparameters = ""; // 最终的订单支付参数，默认为空
		$payinfo = $this->extractWeChatPayInfo ( $wechatpayid ); // 提取待支付信息
		// =========步骤1：接收商户编号，查询数据库得到商户微支付安全信息（敏感），需要写个私有函数封装
		$securityinfo = $this->getSecurityInfo ( $payinfo ['e_id'] ); // 获取企业敏感信息
		// 读到商家微信支付的敏感信息，才去进行步骤2和步骤3
		if ($securityinfo) {
			// =========步骤2：$unifiedOrder对象设置微信支付参数==================
			$prepay_id = $this->jsAPIPrepayId ( $securityinfo, $payinfo ['openid'], $payinfo );
			
			// =========步骤3：使用$jsApi对象生成支付参数=====使用jsapi接口===========
			$jsApi = new JsApi_pub ( $securityinfo );											// 新建H5的js调起支付的对象$jsApi
			$jsApi->setPrepayId ( $prepay_id );													// 为$jsApi对象设置预支付参数
			$payparameters = $jsApi->getParameters (); 											// $jsApi生成最终支付参数
		}
		return $payparameters;
	}
	
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
		$infocompleted = false; // 支付信息完整性标记
		if(! empty ( $preparepayinfo )) {
			$securityinfo = $this->getSecurityInfo ( $preparepayinfo ['e_id'] ); // Step1：尝试获取企业敏感信息
			if ($securityinfo) {
				$infocompleted = $this->jsAPIPayInfoCompleted ( $preparepayinfo ); // Step2：检测支付信息完整性
				if ($infocompleted) {
					$payreadyinfo = $this->recordWeChatPayInfo ( $preparepayinfo ); // Step3：在数据库中生成一条待微信支付的信息
				}
			}
		}
		return $payreadyinfo;
	}
	
	/**
	 * 公有接口：检查有无要调起的微信支付信息，用户来到支付页面，由视图背后的控制器调用做判断。
	 * 最终支付页面的判断函数，如果是恶意进入则直接屏蔽掉。
	 * @param string $wcpid 微动微信支付编号
	 * @return boolean|array false|$existinfo 返回是否存在这样的信息：不存在返回false，存在返回info
	 */
	public function checkWeChatPayInfoExist($wcpid = '') {
		$existinfo = false; // 默认不存在这样的微信支付信息
		if(! empty ( $wcpid )) {
			$infomap = array (
					'wechatpay_id' => $wcpid,
					'is_del' => 0
			);
			$payinfo = M ( 'wechatpayrecord' )->where ( $infomap )->find ();
			if ($payinfo) {
				return $payinfo; // 确实存在一笔微信支付信息，才去调起微信支付，（以后再加上该笔信息未被支付）
			}
		}
		return $existinfo;
	}
	
	/**
	 * 获取企业信息。
	 * @param string $e_id 企业编号
	 * @return array $einfo 企业信息
	 */
	public function getEnterpriseInfo($e_id = '') {
		$einfo = array ();
		if(! empty ( $e_id )) {
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		}
		return $einfo;
	}
}
?>