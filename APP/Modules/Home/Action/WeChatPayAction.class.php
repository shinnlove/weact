<?php
import ( 'Class.API.WeActPay.Main.WeActSafePayHelper', APP_PATH, '.php' ); // 载入微动平台安全支付SDK
/**
 * 微信支付控制器，真正的支付付钱的目录地址（需在微信公众平台授权）。
 * 处理第二版、第三版微信支付和支付的通知与回调。
 * @author 赵臣升
 */
class WeChatPayAction extends Action {
	
	/**
	 * js调起公众号内支付V3。
	 */
	public function wechatPayV3() {
		$wechatpayid = $_REQUEST ['wcpid'];													// 接收页面传来的微信支付编号（微动平台）
		$this->redirecturi = $_REQUEST ['redirecturi'];										// 支付成功后重定向页面
		
		if (! empty ( $wechatpayid )) {
			// 检测待支付信息是否存在
			$infomap = array (
					'wechatpay_id' => $wechatpayid,
					'is_del' => 0
			);
			$payinfoexist = M ( 'wechatpayrecord' )->where ( $infomap )->find ();
			if ($payinfoexist) {
				$weactjsapi = new WeActJsAPIPay ( $payinfoexist ['e_id'] ); 				// 微动JSAPI安全支付类
				$finalparams = $weactjsapi->jsapiPayHandleV3 ( $payinfoexist );				// 尝试生成最终支付参数（商家有可能还没有准备好支付信息）
				if (! empty ( $finalparams )) {
					// 空的$finalparams代表商家微支付没有准备好（信息不完整）
					$payinfoexist ['body'] = substr ( $payinfoexist ['body'], 19 );			// 抽取订单号（临时性）
					$payinfoexist ['visual_fee'] = $payinfoexist ['total_fee'] / 100;      	// 除以100构造视觉钱（实际单位为分）
					$this->payinfo = $payinfoexist;											// 推送支付信息
					$this->einfo = $weactjsapi->getEnterpriseInfo ( $payinfoexist ['e_id'] ); // 获取企业信息
					$this->jsApiParameters = $finalparams; 									// 推送最终支付参数
					$this->display ();
				} else {
					$this->error ( "商家未准备好或已关闭微支付！" ); 									// 商家未准备好或已关闭微支付
				}
			} else {
				$this->error ( "哇呜，您用力过猛了。。。HOLD不住你啊！", "Home/Tip/bindEID" ); 			// 屏蔽恶意进入
			}
		} else {
			$this->error ( "哇呜，您用力过猛了。。。HOLD不住你啊！", "Home/Tip/bindEID" ); 				// 屏蔽恶意进入
		}
	}
	
	/**
	 * js调起公众号内支付V2。
	 */
	public function wechatPayV2(){
		
		import( 'Class.API.WeChatPayV2.CommonUtil', APP_PATH, '.php' );
		import( 'Class.API.WeChatPayV2.WxPayHelper', APP_PATH, '.php' );
		
		$commonUtil = new CommonUtil();		//初始化CommonUtil类
		$wxPayHelper = new WxPayHelper();	//初始化WxPayHelper类
		
		/**
		 * 设置微支付的参数，有1~15个参数，其中有必选也有可选参数。
		 * 参数1、7、15为固定参数。
		 * 参数2、3、4、5、6、12、13、14需要查数据库。
		 * 参数8、9、10、11在代码中处理、拼接。
		 * */
		$wxPayHelper->setParameter("bank_type", "WX");									//1、必须，银行通道类型：固定为“WX”
		$wxPayHelper->setParameter("body", "微动平台测试产品");								//2、必须，商品描述，128字节以下，或者订单描述也行
		$wxPayHelper->setParameter("attach", "该产品用于测试，是第一件产品。");					//3、必须，附加数据，128字节以下，原样返回（支付时看不到）
		$wxPayHelper->setParameter("partner", "1900000109");							//4、必须，商户号，注册时分配的财付通商户号partnerID（需认证通过），1900000109是一个微信的测试商户号
		$wxPayHelper->setParameter("out_trade_no", $commonUtil->create_noncestr());		//5、必须，商户订单号，32字节以下，商户系统内部（微动平台生成的）订单号，确保唯一
		$wxPayHelper->setParameter("total_fee", "2");									//6、必须，订单总金额，单位为分
		$wxPayHelper->setParameter("fee_type", "1");									//7、必须，支付币种，目前只支持1（人民币）
		$wxPayHelper->setParameter("notify_url", "http://www.baidu.com");				//8、必须，支付完成后通知的URL，必须给出绝对路径，如http://www.we-act.cn/......下的页面接收通知
		$wxPayHelper->setParameter("spbill_create_ip", "127.0.0.1");					//9、必须，订单生成的机器IP，用户浏览器端IP地址，可以获取，格式为IPV4
		//$wxPayHelper->setParameter("time_start", "20140616122536");					//10、可选，交易起始时间，取当前时间即可
		//$wxPayHelper->setParameter("time_expire", "20140616132536");					//11、可选，交易结束时间，默认1小时以后
		$wxPayHelper->setParameter("transport_fee", "1");								//12、可选，物流费用，单位为分，必须保证transport_fee+product_fee=total_fee
		$wxPayHelper->setParameter("product_fee", "1");									//13、可选，商品费用，单位为分，必须保证transport_fee+product_fee=total_fee
		$wxPayHelper->setParameter("goods_tag", "discount");							//14、可选，活动、优惠券等打折时可能用到的字符串标记
		$wxPayHelper->setParameter("input_charset", "GBK");								//15、必须，传入参数字符编码：GBK或者UTF-8，默认GBK
		
		$this->cbp = $wxPayHelper->create_biz_package();								//调用SDK函数create_biz_package()对本次订单生成详细信息包package，推送给前台js
		
		$this->display();
	}
	
	/**
	 * Native原生支付V3。
	 */
	public function nativeCallback(){
		$e_id = $_REQUEST ['e_id']; 								// 获取在平台被通知扫码的商家编号
		if (empty ( $e_id )) exit ( "" );
		
		$xml = $GLOBALS ['HTTP_RAW_POST_DATA']; 					// 接收微信发来的扫码支付数据
		
		$weactnative = new WeActNativePay ( $e_id ); 				// 实例化微动扫码支付类
		$handleresult = $weactnative->nativeScanHandle ( $xml ); 	// 处理扫码支付
		
		exit ( $handleresult ); // 返回给微信
	}
	
	/**
	 * 告警通知URL。
	 */
	public function payInfoOrWarn(){
		$this->display();
	}
	
	/**
	 * 维权通知URL。
	 */
	public function guardLegalRight(){
		$this->display();
	}
	
}
?>