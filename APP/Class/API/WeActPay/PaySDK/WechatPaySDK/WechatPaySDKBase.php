<?php
/**
 * 微动-微信支付SDK封装
 * author:shinnlove
 * email:306086640@qq.com
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 		退款申请接口--Refund
 * 		退款查询接口--RefundQuery
 * 		对账单接口--DownloadBill
 * 		短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 * 		通用通知接口--Notify
 * 		Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 * 		静态链接二维码--NativeLink
 * 		JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 * 		trimString()，设置参数时需要用到的字符处理函数
 * 		createNoncestr()，产生随机字符串，不长于32位
 * 		formatBizQueryParaMap(),格式化参数，签名过程需要用到
 * 		getSign(),生成签名
 * 		arrayToXml(),array转xml
 * 		xmlToArray(),xml转 array
 * 		postXmlCurl(),以post方式提交xml到对应的接口url
 * 		postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
*/
import ( 'Class.API.WeActPay.PaySDK.WechatPaySDK.WechatPaySDKException', APP_PATH, '.php' ); // 微支付运行缓存错误记录类

/**
 * 所有接口的基类，定义一些共用的方法。
 */
class Common_util_pub {
	
	/**
	 * 商家安全支付信息
	 * @var array $securityinfo
	 */
	protected $securityinfo;
	
	/**
	 * 基类构造函数。
	 * @param array $securityinfo 企业安全支付信息
	 */
	public function __construct($securityinfo = NULL) {
		$this->securityinfo = $securityinfo; // 为SDK基类设置平台商户支付信息
	}
	
	/**
	 * ==========读写类变量工具函数==========
	 */
	
	/**
	 * 设置公众账号appid的函数。
	 * @param string $appid 公众号的appid
	 */
	public function setAPPID($appid = '') {
		$this->securityinfo ['appid'] = $appid; // 公众账号的appid
	}
	
	/**
	 * 设置财付通受理商id的函数。
	 * @param string $mchid 形参传入财付通受理商编号。
	 */
	public function setMCHID($mchid = '') {
		$this->securityinfo ['mch_id'] = $mchid; // 财付通受理商ID
	}
	
	/**
	 * 设置商户支付密钥函数。
	 * @param string $apikey 商户支付密钥
	 */
	public function setKEY($apikey = '') {
		$this->securityinfo ['apikey'] = $apikey;		//设置商户支付密钥
	}
	
	/**
	 * 设置商户证书绝对路径函数。
	 * @param string $sslcert_path 商户证书绝对路径
	 */
	public function setSSLCERT_PATH($sslcert_path = '') {
		$this->securityinfo ['sslcert_path'] = $sslcert_path; // 设置商户证书绝对路径
	}
	
	/**
	 * 设置商户支付密钥绝对路径函数。
	 * @param string $sslkey_path 商户支付密钥绝对路径函数
	 */
	public function setSSLKEY_PATH($sslkey_path = '') {
		$this->securityinfo ['sslkey_path'] = $sslkey_path; 		// 设置商户支付密钥绝对路径
	}
	
	/**
	 * ==========微信签名工具函数==========
	 */
	
	/**
	 * 去除字符串中空格函数。
	 * @param string $value
	 * @return string $ret
	 */
	protected function trimString($value) {
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen ( $ret ) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	
	/**
	 * 随机字符串生成函数（不长于32位）。
	 * @param number $length
	 * @return string
	 */
	protected function createNoncestr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789"; // 填充规则
		$str = ""; // 目标
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	
	/**
	 * 格式化微信参数，签名过程需要使用。
	 * @param array $paraMap
	 * @param boolean $urlencode
	 * @return string $reqPar 参数签名字符串
	 */
	protected function formatBizQueryParaMap($paraMap, $urlencode) {
		$buff = "";
		ksort ( $paraMap ); // 按照关键字对参数key进行字段排序
		foreach ( $paraMap as $k => $v ) {
			if ($urlencode) {
				$v = urlencode ( $v );
			}
			// $buff .= strtolower($k) . "=" . $v . "&"; // 暂时不用全部转成小写
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen ( $buff ) > 0) {
			$reqPar = substr ( $buff, 0, strlen ( $buff ) - 1 );
		}
		return $reqPar;
	}
	
	/**
	 * 基类生成支付签名函数。
	 * 特别注意：调用该函数前请先使用setKey函数设置Key。
	 */
	protected function getSign($Obj) {
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String."&key=".$this->securityinfo ['apikey']; // 使用apikey
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}
	
	/**
	 * ==========数组XML格式转换工具函数==========
	 */
	
	/**
	 * 数组转成XML格式函数。
	 * @param array $arr 需要转换的二维数组
	 * @return string $xml 变成字符串的xml文档
	 */
	protected function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
    /**
     * XML转成数组函数。
     * @param unknown $xml
     * @return mixed
     */
	protected function xmlToArray($xml) {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}
	
	/**
	 * ==========PHP网络通信函数==========
	 */
	
	/**
	 * PHP的CURL模拟POST普通HTTP请求到对应的接口URL，不带证书。
	 * @param xml|string $xml 需要post发送的数据
	 * @param string $url 要请求的httpurl
	 * @param number $second http连接超时秒数
	 * @return array|boolean http请求结果
	 */
	protected function postXmlCurl($xml, $url, $second = 30) {
		// 初始化curl
		$ch = curl_init ();
		// 设置超时
		curl_setopt ( $ch, CURLOP_TIMEOUT, $second );
		// 这里设置代理，如果有的话
		// curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		// curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		// 设置header
		curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
		// 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		// post提交方式
		curl_setopt ( $ch, CURLOPT_POST, TRUE );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $xml );
		// 运行curl
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		// 返回结果
		if ($data) {
			curl_close ( $ch );
			return $data;
		} else {
			$error = curl_errno ( $ch );
			echo "curl出错，错误码:$error" . "<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close ( $ch );
			return false;
		}
	}
	
	/**
	 * 使用证书，并以post方式提交xml到对应的接口url函数。
	 * 特别注意：调用该函数前请先使用setSSLCERT_PATH函数和setSSLKEY_PATH函数设置证书路径和支付密钥路径。
	 */
	protected function postXmlSSLCurl($xml, $url, $second = 30) {
		$ch = curl_init ();
		// 超时时间
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $second );
		// 这里设置代理，如果有的话
		// curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		// curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		// 设置header
		curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
		// 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		// 设置证书
		// 使用证书：cert 与 key 分别属于两个.pem文件
		// 默认格式为PEM，可以注释
		curl_setopt ( $ch, CURLOPT_SSLCERTTYPE, 'PEM' );
		curl_setopt ( $ch, CURLOPT_SSLCERT, $this->securityinfo ['sslcert_path'] );
		// 默认格式为PEM，可以注释
		curl_setopt ( $ch, CURLOPT_SSLKEYTYPE, 'PEM' );
		curl_setopt ( $ch, CURLOPT_SSLKEY, $this->securityinfo ['sslkey_path'] );
		// post提交方式
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $xml );
		$data = curl_exec ( $ch );
		// 返回结果
		if ($data) {
			curl_close ( $ch );
			return $data;
		} else {
			$error = curl_errno ( $ch );
			echo "curl出错，错误码:$error" . "<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close ( $ch );
			return false;
		}
	}
	
	/**
	 * ==========日志工具函数==========
	 */
	
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
	
}

/**
 * 主动请求型接口的基类
 */
class Wxpay_client_pub extends Common_util_pub {
	
	/**
	 * 各个微信请求的接口链接地址
	 * @var string $url
	 */
	protected $url; 
	
	/**
	 * curl请求的超时时间
	 * @var number $curl_timeout
	 */
	protected $curl_timeout = 30; 
	
	/**
	 * 请求所需参数，类型为关联数组
	 * @var array $parameters
	 */
	protected $parameters; 
	
	/**
	 * 微信返回的响应，XML格式
	 * @var xml $response
	 */
	protected $response; 
	
	/**
	 * 返回参数，类型为关联数组，XML相应转成二维数组格式
	 * @var array $result
	 */
	protected $result; 
	
	/**
	 * SDK调用出错信息
	 * @var string $error
	 */
	protected $error; 
	
	/**
	 * ==========读写类变量函数==========
	 */
	
	/**
	 * 设置curl超时时间函数。
	 * @param number $curl_timeout	curl超时参数，必须给出大于0的整数。
	 */
	public function setCURL_TIMEOUT($curl_timeout = 30) {
		if ($curl_timeout > 0) $this->curl_timeout = $curl_timeout;	// 更改设置curl超时时间
	}
	
	/**
	 * ==========SDK参数校验设置函数==========
	 */
	
	/**
	 * 虚函数：校验SDK所需参数。
	 * @param array $sdkparams 需要检验的SDK参数
	 */
	public function checkSDKParams($checkparams = NULL) {
		// virtual function
	}
	
	/**
	 * 虚函数：设置SDK所需参数。
	 * @param string $sdkparams 需要设置的SDK参数
	 */
	public function setSDKParams($setparams = NULL) {
		// virtual function
	}
	
	/**
	 * ==========发送请求类函数==========
	 */
	
	/**
	 * 内部函数，由postXml调用，设置标配的请求参数、生成签名与接口参数xml函数。
	 * 特别注意：在使用此函数之前，请先调用setAPPID函数和setMCHID函数分别设置公众账号appid和财付通受理商id。
	 */
	protected function createXml() {
	   	$this->parameters ["appid"] = $this->securityinfo ['appid']; 		// 公众账号appid
	   	$this->parameters ["mch_id"] = $this->securityinfo ['mch_id'];		// 财付通受理商mchid
	    $this->parameters ["nonce_str"] = $this->createNoncestr ();			// 随机字符串
	    $this->parameters ["sign"] = $this->getSign ( $this->parameters );	// 签名
	    return $this->arrayToXml ( $this->parameters );
	}
	
	/**
	 * 内部函数，由getPostResult调用，主动发送xml包给远端服务器并得到结果。
	 * @param boolean $postssl 是否需要使用证书通信，默认false不需要证书
	 * @return xml $response 与微信服务器通信结果
	 */
	protected function postXml($postssl = FALSE) {
	    $xml = $this->createXml (); // 生成XML压缩包
	    if ($postssl) {
	    	$this->response = $this->postXmlSSLCurl ( $xml, $this->url, $this->curl_timeout ); 		// 使用证书通信
	    } else {
	    	$this->response = $this->postXmlCurl ( $xml, $this->url, $this->curl_timeout ); 	// 无证书直接通信
	    }
		return $this->response;
	}
	
	/**
	 * ==========公有对外接口函数==========
	 */
	
	/**
	 * 设置本请求类所需的字段参数和值。
	 * @param string $parameterfield 请求所需的字段名字
	 * @param string $parameterValue 请求所需的字段值
	 */
	public function setParameter ($parameterField = '', $parameterValue = '') {
		$this->parameters [$this->trimString ( $parameterField )] = $this->trimString ( $parameterValue ); // 设置请求所需参数字段名和值
	}
	
	/**
	 * 调试函数，调试成功后、发布前请删除，对参数不安全。
	 * @return array $parameters
	 */
	public function getParameter () {
		return $this->parameters;
	}
	
	/**
	 * 获取主动请求接口的结果，默认不使用证书；如果使用证书，则证书通信。返回二维数组信息。
	 * 得到接口POST信息后，如果传入形参则记录日志文件。
	 * 2015/08/30 17:29:36增加记录请求型接口的IO日志记录。
	 * @param string $postfrom 调用POST的模板名字（日志存放文件夹名）
	 * @param boolean $postssl 本次POST是否需要使用证书通信
	 * @return array $postresult POST接口结果转成二维数组的消息
	 */
	public function getPostResult($postfrom = "", $postssl = FALSE) {
		$this->postXml ( $postssl ); // 调用主动发送XML函数
		if (! empty ( $postfrom )) {
			$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/" . $postfrom . "/"; 	// 微信消息文件夹，分消息类型存放
			$filename = "record" . $postfrom . "msg" . date ( "Ymd" ) . ".log"; 					// 文件名按天存放
			$this->logResult ( $filepath, $filename, $this->response ); 							// 记录日志文件
		}
		$this->result = $this->xmlToArray ( $this->response ); // 解析XML成二维数组
		return $this->result;
	}
	
	/**
	 * 获取微信原始的xml结果，用于调试。
	 * @return xml $response 微信响应请求结果
	 */
	public function getXmlResponse() {
		return $this->response;
	}
	
}


/**
 * 统一支付接口类，继承自Wxpay_client_pub，间接继承自Common_util_pub。
 */
class UnifiedOrder_pub extends Wxpay_client_pub {
	
	/**
	 * 统一支付接口类构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo	商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $apikey	商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";	// 设置统一下单类的接口链接
	}
	
	/**
	 * 检测统一下单参数完整度。
	 * @param array $checkparams 订单支付等信息
	 * @return boolean $iscompleted true|false 统一下单信息是否完整
	 */
	public function checkSDKParams($checkparams = NULL) {
		try {
			// 检测必填参数
			if (empty ( $checkparams )) {
				throw new SDKRuntimeException ( "统一下单接口信息不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['trade_type'] )) {
				throw new SDKRuntimeException ( "统一下单接口支付方式不能为空，如JSAPI、NATIVE或APP！" . "<br>" );
			}
			// 按照不同类型来检测
			switch ( $checkparams ['trade_type'] ) {
				case "JSAPI":
					if (empty ( $checkparams ['openid'] )) {
						throw new SDKRuntimeException ( "缺少用户的微信号openid，jsAPI支付下，此参数必须！" . "<br>" );
					}
					break;
				case "NATIVE":
					if (empty ( $checkparams ['product_id'] )) {
						throw new SDKRuntimeException ( "缺少原生扫码支付商品的product_id，NATIVE支付下，此参数必须！" . "<br>" );
					}
					break;
				case "APP":
					// check APP
					break;
				case "WAP":
					// check WAP
					break;
				default:
					throw new SDKRuntimeException ( "支付类型不明确，请指定支付类型！" . "<br>" );
					break;
			}
			// 检测常规参数
			if (empty ( $checkparams ['out_trade_no'] )) {
				throw new SDKRuntimeException ( "统一下单接口商户订单号不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['body'] )) {
				throw new SDKRuntimeException ( "统一下单接口商品描述不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['total_fee'] )) {
				throw new SDKRuntimeException ( "统一下单接口总金额不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['notify_url'] )) {
				throw new SDKRuntimeException ( "统一下单接口支付异步通知地址不能为空！" . "<br>" );
			}
		} catch (SDKRuntimeException $e) {
			$this->error = $e->errorMessage (); // 捕捉错误信息
			return false;
		}
		
		// 检测都通过
		return true;
	}
	
	/**
	 * 生成预支付id函数，获取prepay_id。
	 */
	public function getPrepayId() {
		$prepay_id = $this->result ["prepay_id"]; // 提取$prepay_id
		return $prepay_id;
	}
	
}

/**
 * 订单查询接口
 */
class OrderQuery_pub extends Wxpay_client_pub {
	
	/**
	 * 订单查询接口类构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/pay/orderquery";	// 设置订单查询接口链接
	}
	
	/**
	 * 检测订单查询接口信息完整度。
	 * @param array $checkparams 订单待查询信息
	 * @return boolean $iscompleted true|false 订单查询信息是否完整
	 */
	public function checkSDKParams($checkparams = NULL) {
		try {
			// 检测必填参数
			if (empty ( $checkparams )) {
				throw new SDKRuntimeException ( "订单查询接口信息不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['out_trade_no'] ) && empty ( $checkparams ['transaction_id'] )) {
				throw new SDKRuntimeException ( "订单查询接口out_trade_no和transaction_id至少填一个！" . "<br>" );
			}
		} catch (SDKRuntimeException $e) {
			$this->error = $e->errorMessage (); // 捕捉错误信息
			return false;
		}
		
		// 检测都通过
		return true;
	}
	
	/**
	 * 设置订单查询参数。
	 * @param array $setparams 订单支付等信息
	 */
	public function setSDKParams($setparams = NULL) {
		// 订单查询out_trade_no或者transaction_id必须填写一个
		if (isset ( $setparams ['out_trade_no'] )) $this->setParameter ( "out_trade_no", $setparams ['out_trade_no'] ); 		// 商户系统内部订单号
		if (isset ( $setparams ['transaction_id'] )) $this->setParameter ( "transaction_id", $setparams ['transaction_id'] ); 	// 微信服务器订单号
	}
	
}


/**
 * 退款申请接口
 */
class Refund_pub extends Wxpay_client_pub {
	
	/**
	 * 退款申请接口构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $appsecret 公众账号的appsecret
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund"; // 设置退款申请接口链接
	}
	
	/**
	 * 设置订单退款参数。
	 * @param array $setparams 订单退款等信息
	 */
	public function setSDKParams($setparams = NULL) {
		// 微信退款的必填参数，其中appid公众账号id, mch_id商户号, noncestr随机字符串, sign签名等5项由基类生成
		$this->setParameter ( "out_trade_no", $setparams ['out_trade_no'] ); 		// 商户订单号
		$this->setParameter ( "out_refund_no", $setparams ['out_refund_no'] ); 		// 商户退款单号
		$this->setParameter ( "total_fee", $setparams ['total_fee'] ); 				// 总金额
		$this->setParameter ( "refund_fee",  $setparams ['refund_fee'] ); 			// 退款金额（支持部分退款）
		$this->setParameter ( "op_user_id", $this->securityinfo ['mch_id'] ); 		// 操作员（当前商户）
		// 非必填参数，商户可根据实际情况选填
		if (isset ( $setparams ['op_user_passwd'] )) $this->setParameter ( "op_user_passwd", md5 ( $setparams ['op_user_passwd'] ) ); // 操作员密码（当前商户登录密码），这个参数V2版本以后已经不用了
		if (isset ( $setparams ['device_info'] )) $this->setParameter ( "device_info", $setparams ['device_info'] ); 	// 设备号
		if (isset ( $setparams ['transaction_id'] )) $this->setParameter ( "transaction_id", $setparams ['transaction_id'] ); 	// 微信订单号
	}
	
	/**
	 * 检测微信退款参数完整度。
	 * @param array $checkparams 微信退款等信息
	 * @return boolean $iscompleted true|false 微信退款信息是否完整
	 */
	public function checkSDKParams($checkparams = NULL) {
		try {
			// 检测必填参数
			if (empty ( $checkparams )) {
				throw new SDKRuntimeException ( "微信退款接口信息不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['out_trade_no'] ) && empty ( $checkparams ['transaction_id'] )) {
				throw new SDKRuntimeException ( "退款申请接口中，out_trade_no、transaction_id至少填一个！" . "<br>" );
			}
			if (empty ( $checkparams ['total_fee'] )) {
				throw new SDKRuntimeException ( "退款申请接口中，缺少必填参数total_fee！" . "<br>" );
			}
			if (empty ( $checkparams ['refund_fee'] )) {
				throw new SDKRuntimeException ( "退款申请接口中，缺少必填参数refund_fee！" . "<br>" );
			}
		} catch (SDKRuntimeException $e) {
			$this->error = $e->errorMessage (); // 捕捉错误信息
			return false;
		}
		
		// 检测都通过
		return true;
	}
	
}

/**
 * 退款查询接口类。
 */
class RefundQuery_pub extends Wxpay_client_pub {
	
	/**
	 * 退款查询接口构造函数。
	 * 在构造函数中传入六个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo	商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key	商户支付密钥Key
	 * @property string $sslcert_path	商户证书路径（微支付V3版本）
	 * @property string $sslkey_path	商户支付密钥路径（微支付V3版本）
	 * @property string $appid	公众账号的appid
	 * @property string $appsecret	公众账号的appsecret
	 * @property string $mchid	财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/pay/refundquery"; 	// 设置退款查询接口链接	
	}
	
	/**
	 * 设置订单退款参数。
	 * @param array $setparams 订单退款等信息
	 */
	public function setSDKParams($setparams = NULL) {
		// 微信退款查询的必填参数，其中appid公众账号id, mch_id商户号, noncestr随机字符串, sign签名等5项由基类生成
		$this->setParameter ( "out_trade_no", $setparams ['out_trade_no'] ); 		// 要查询微信退款的商户订单号
		// 非必填参数，商户可根据实际情况选填
		if (isset ( $setparams ['out_refund_no'] )) $this->setParameter ( "out_refund_no", $setparams ['out_refund_no'] ); 		// 商户某一笔退单号
		if (isset ( $setparams ['transaction_id'] )) $this->setParameter ( "transaction_id", $setparams ['transaction_id'] ); 	// 微信订单号
		if (isset ( $setparams ['refund_id'] )) $this->setParameter ( "refund_id", $setparams ['refund_id'] ); 					// 微信某一笔退单号
	}
	
	/**
	 * 检测微信退款查询参数完整度。
	 * @param array $checkparams 微信退款等信息
	 * @return boolean $iscompleted true|false 微信退款信息是否完整
	 */
	public function checkSDKParams($checkparams = NULL) {
		try {
			// 检测必填参数
			if (empty ( $checkparams )) {
				throw new SDKRuntimeException ( "微信退款查询接口信息不能为空！" . "<br>" );
			}
			if (empty ( $checkparams ['out_trade_no'] ) && empty ( $checkparams ['out_refund_no'] ) && empty ( $checkparams ['transaction_id'] ) && empty ( $checkparams ['refund_id'] )) {
				throw new SDKRuntimeException ( "退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！" . "<br>" );
			}
		} catch (SDKRuntimeException $e) {
			$this->error = $e->errorMessage (); // 捕捉错误信息
			return false;
		}
		
		// 检测都通过
		return true;
	}
	
}


/**
 * 对账单接口类。
 */
class DownloadBill_pub extends Wxpay_client_pub {
	
	/**
	 * 对账单接口类构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo	商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key	商户支付密钥Key
	 * @property string $sslcert_path	商户证书路径（微支付V3版本）
	 * @property string $sslkey_path	商户支付密钥路径（微支付V3版本）
	 * @property string $appid	公众账号的appid
	 * @property string $mchid	财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/pay/downloadbill"; 	// 设置对账单接口链接
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{		
		try 
		{
			if($this->parameters["bill_date"] == null ) 
			{
				throw new SDKRuntimeException("对账单接口中，缺少必填参数bill_date！"."<br>");
			}
		   	$this->parameters["appid"] = $this->config_appid;					//公众账号ID
		   	$this->parameters["mch_id"] = $this->config_mchid;					//财付通受理商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();			//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);		//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 获取对账单。
	 * @return xml $bill 微信对账单
	 */
	public function getBillResult () {
		return $this->postXml (); // 返回表格类型的数据
	}
	
	/**
	 * 处理csv表格（需要PHP5.3以上，含fgetcsv函数）
	 * @param unknown $handle
	 * @return multitype:
	 */
	public function input_csv($handle) {
		$out = array ();
		$n = 0;
		while ($data = fgetcsv($handle, 10000)) {
			$num = count($data);
			for ($i = 0; $i < $num; $i++) {
				$out[$n][$i] = $data[$i];
			}
			$n++;
		}
		return $out;
	}
	
}


/**
 * 被动响应型接口基类（通知）
 */
class Wxpay_server_pub extends Common_util_pub {
	
	/**
	 * 接收到的数据，类型为关联数组
	 * @var array $data
	 */
	protected $data; 
	
	/**
	 * 返回参数，类型为关联数组
	 * @var array $returnParameters
	 */
	protected $returnParameters; 
	
	/**
	 * ==========通知接受类公有对外接口函数==========
	 */
	
	/**
	 * 调用这个函数前必须设置Key，其父类（各类的共用类）中已经有setKey函数了，所以直接调用继承过来的setKey函数即可设置。。
	 * 函数思想：
	 * 既然已经将xml解析成data数组，则把数组给到临时变量temp，清空sign字段，对data数据包重新生成一次签名，比对。
	 * @return boolean true|false 返回签名验证是否通过。
	 */
	public function checkSign() {
		$tmpData = $this->data; // 拷贝副本
		unset($tmpData['sign']); // 注销副本变量$tmpData定义的字段sign
		$sign = $this->getSign($tmpData); //本地进行签名算法
		if ($this->data['sign'] == $sign) {
			return TRUE; // 重新生成的签名与data中签名一致，说明微信身份消息验证成功（是微信发来的）
		}
		return FALSE;
	}
	
	/**
	 * 将微信的请求xml转换成关联数组，以方便数据处理，保存在本类的变量$data里。
	 */
	public function saveData($xml, $datafrom = "") {
		if (! empty ( $datafrom )) {
			// 如果需要记录消息日志
			$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/" . $datafrom . "/" . date ( "Ymd" ) . "/"; 	// 微信消息文件夹，分消息类型存放
			$filename = "record" . $postfrom . "msg" . date ( "Ymd" ) . ".log"; 		// 文件名按天存放
			$this->logResult ( $filepath, $filename, "【接收到微信的" . $datafrom . "通知】:\n" . $xml ); 	// 记录日志文件
		}
		$this->data = $this->xmlToArray($xml);
	}
	
	/**
	 * 获取本类中的关联数组数据，前提是必须要先调用saveData才有值。
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * 设置响应微信的xml返回数据
	 */
	public function setReturnParameter($parameterField, $parameterValue) {
		$this->returnParameters [$this->trimString ( $parameterField )] = $this->trimString ( $parameterValue );
	}
	
	/**
	 * ==========工具函数==========
	 */
	
	/**
	 * 生成接口参数xml
	 */
	function createXml() {
		return $this->arrayToXml($this->returnParameters);
	}
	
	/**
	 * 将xml数据返回微信。
	 * 情况有2种：return_code:success|fail，如果fail有msg
	 */
	public function returnXml() {
		return $this->createXml ();
	}
	
}


/**
 * 通用通知接口类，因为需要对微信发来的消息进行签名运算比对，所以需要商户的支付密钥APIKEY。
 */
class Notify_pub extends Wxpay_server_pub {
	
	/**
	 * 通用通知接口的构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
	}
	
}


/**
 * 请求商家获取商品信息接口类。
 */
class NativeCall_pub extends Wxpay_server_pub {
	
	/**
	 * 请求商家获取商品信息类接口构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo	商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
	}
	
	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		if($this->returnParameters["return_code"] == "SUCCESS"){
		   	$this->returnParameters["appid"] = $this->config_appid;							//公众账号ID
		   	$this->returnParameters["mch_id"] = $this->config_mchid;						//财付通受理商户号
		    $this->returnParameters["nonce_str"] = $this->createNoncestr();					//随机字符串
		    $this->returnParameters["sign"] = $this->getSign($this->returnParameters);		//签名
		}
		return $this->arrayToXml($this->returnParameters);
	}
	
	/**
	 * 获取product_id
	 */
	function getProductId()
	{
		$product_id = $this->data["product_id"];
		return $product_id;
	}
	
}

/**
 * 静态链接二维码(暂时无子类)
 */
class NativeLink_pub extends Common_util_pub {
	
	var $parameters;//静态链接参数
	
	var $url;//静态链接
	
	var $config_appid = '';				//公众账号的appid
	
	var $config_mchid = '';				//财付通商户号编号
	
	/**
	 * 静态链接二维码类的构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
	}
	
	/**
	 * 设置公众账号appid的函数。
	 * @param string $appid	公众号的appid
	 */
	public function setAPPID($appid = '') {
		$this->config_appid = $appid;	//公众账号的appid
	}
	
	/**
	 * 设置财付通受理商id的函数。
	 * @param string $mchid	形参传入财付通受理商编号。
	 */
	public function setMCHID($mchid = '') {
		$this->config_mchid = $mchid;	//财付通受理商ID
	}
	
	/**
	 * 设置参数
	 */
	public function setParameter($parameterField, $parameterValue) {
		$this->parameters[$this->trimString($parameterField)] = $this->trimString($parameterValue);
	}
	
	/**
	 * 生成Native支付链接二维码
	 */
	private function createLink() {
		try 
		{		
			if($this->parameters["product_id"] == null) 
			{
				throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！"."<br>");
			}			
		   	$this->parameters ["appid"] = $this->config_appid;						//公众账号ID
		   	$this->parameters ["mch_id"] = $this->config_mchid;						//财付通受理商户号
		   	$this->parameters ["time_stamp"] = strval ( time () );					//时间戳
		    $this->parameters ["nonce_str"] = $this->createNoncestr();				//随机字符串
		    $this->parameters ["sign"] = $this->getSign($this->parameters);			//签名    		
			$bizString = $this->formatBizQueryParaMap($this->parameters, false); // 格式化参数
		    $this->url = "weixin://wxpay/bizpayurl?".$bizString;
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	/**
	 * 返回链接
	 */
	public function getUrl() { 
		$this->createLink();
		return $this->url;
	}
	
}

/**
 * 短链接转换接口
 */
class ShortUrl_pub extends Wxpay_client_pub {

	/**
	 * 短链接转换接口类。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/tools/shorturl"; 	// 设置短链接转换接口链接
	}
	
	/**
	 * 长链接转成短链接的生成接口参数xml
	 */
	public function createXml() {
		try {
			if ($this->parameters ["long_url"] == null) {
				throw new SDKRuntimeException ( "短链接转换接口中，缺少必填参数long_url！" . "<br>" );
			}
			$this->parameters ["appid"] = $this->config_appid; // 公众账号ID
			$this->parameters ["mch_id"] = $this->config_mchid; // 财付通受理商户号
			$this->parameters ["nonce_str"] = $this->createNoncestr (); // 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); // 签名
			return $this->arrayToXml ( $this->parameters );
		} catch ( SDKRuntimeException $e ) {
			die ( $e->errorMessage () );
		}
	}

	/**
	 * 长链接转换短链接，已经调通，改正了微信原版的错误（写SDK的人粗心分神了一下，2015/05/24 02:14:36改正确）
	 */
	public function getShortUrl() {
		$this->getPostResult ( "shorturl" ); // POST数据并且转成数组形式
		return $this->result ["short_url"]; // 提取短链接返回
	}
	
}


/**
 * JSAPI支付——H5网页端调起支付接口
 */
class JsApi_pub extends Common_util_pub {
	
	var $code;							// code码，用以获取openid
	
	var $openid;						// 用户的openid
	
	var $parameters;					// jsapi参数，格式为json
	
	var $prepay_id;						// 使用统一支付接口得到的预支付id
	
	var $curl_timeout = 30;				// curl超时设置变量
	
	/**
	 * JSAPI网页调起支付类的构造函数，传入6个参数。
	 * 在构造函数中传入六个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $appsecret 公众账号的appsecret
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
	}
	
	/**
	 * 设置公众账号appsecret的函数。
	 * @param string $appsecret	公众号的appsecret
	 */
	public function setAPPSECRET($appsecret = '') {
		$this->securityinfo ['appsecret'] = $appsecret;	//公众账号的appsecret
	}
	
	/**
	 * 设置curl超时时间函数。
	 * @param number $curl_timeout	curl超时参数，必须给出大于0的整数。
	 */
	public function setCURL_TIMEOUT($curl_timeout = 30) {
		if ($curl_timeout > 0) $this->curl_timeout = $curl_timeout;	//设置curl超时时间
	}
	
	/**
	 * ==========JSAPI模式下openid必须存在，以下为获取openid的函数。==========
	 */
	
	/**
	 * 	作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->config_appid;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}
	
	/**
	 * 	作用：生成可以获得openid的url
	 */
	function createOauthUrlForOpenid()
	{
		$urlObj["appid"] = $this->config_appid;
		$urlObj["secret"] = $this->config_appsecret;
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
	
	/**
	 * 作用：设置code
	 */
	function setCode($code_) {
		$this->code = $code_;
	}
	
	/**
	 * 授权获取openid函数：通过curl向微信提交code，以获取openid。
	 * 我们平台已经有授权。
	 */
	function getOpenid() {
		$url = $this->createOauthUrlForOpenid();
        //初始化curl
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//运行curl，结果以jason形式返回
        $res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->openid = $data['openid'];
		return $this->openid;
	}
	
	/**
	 * ==========设置预支付prepay_id与签名串函数。==========
	 */
	
	/**
	 * 设置预支付prepay_id函数。
	 * @param string $prepayId
	 */
	public function setPrepayId($prepayId = '') {
		$this->prepay_id = $prepayId;
	}
	
	/**
	 * 生成jsapi的参数。
	 * @return string parameters jsapi的参数
	 */
	public function getParameters() {
		$jsApiObj ["appId"] = $this->securityinfo ['appid'];
		$timeStamp = time ();
		$jsApiObj ["timeStamp"] = "$timeStamp";
		$jsApiObj ["nonceStr"] = $this->createNoncestr ();
		$jsApiObj ["package"] = "prepay_id=$this->prepay_id";
		$jsApiObj ["signType"] = "MD5";
		$jsApiObj ["paySign"] = $this->getSign ( $jsApiObj );
		$this->parameters = json_encode ( $jsApiObj );
		return $this->parameters;
	}
	
}

/**
 * 微信刷卡支付类。
 * 该类继承自微信支付平台SDK的请求型基类（请求刷卡支付下单查询等），属于新增微信支付种类。
 *
 * @author Shinnlove
 * CreateTime:2015/08/30 00:54:25
 */
class MicroPay_pub extends Wxpay_client_pub {
	
	/**
	 * 统一支付接口类构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo
	 * @example 数组 $securityinfo 信息：
	 * @property string $apikey
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 							// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/pay/micropay"; 		// 设置刷卡支付接口链接
	}
	
	/**
	 * 生成刷卡支付接口所需参数xml
	 */
	public function createXml() {
		try {
			// 检测必填参数
			if ($this->parameters ["out_trade_no"] == null) {
				throw new SDKRuntimeException ( "提交被扫支付API接口中，缺少必填参数out_trade_no！" . "<br>" );
			} elseif ($this->parameters ["body"] == null) {
				throw new SDKRuntimeException ( "提交被扫支付API接口中，缺少必填参数body！" . "<br>" );
			} elseif ($this->parameters ["total_fee"] == null) {
				throw new SDKRuntimeException ( "提交被扫支付API接口中，缺少必填参数total_fee！" . "<br>" );
			} elseif ($this->parameters ["auth_code"] == null) {
				throw new SDKRuntimeException ( "提交被扫支付API接口中，缺少必填参数auth_code！" . "<br>" );
			}
			$this->parameters ["appid"] = $this->config_appid; 						// 公众账号ID
			$this->parameters ["mch_id"] = $this->config_mchid; 					// 财付通受理商户号
			$this->parameters ["spbill_create_ip"] = $_SERVER ['REMOTE_ADDR']; 		// 终端ip
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 签名
			return $this->arrayToXml ( $this->parameters );
		} catch ( SDKRuntimeException $e ) {
			die ( $e->errorMessage () );
		}
	}
	
}


/**
 * 微信红包类。
 * 微信红包需要使用双向证书。
 */
class RedPackage_pub extends Wxpay_client_pub {

	/**
	 * 退款申请接口构造函数。
	 * 在构造函数中传入5个配置变量的值，调用父类的设置函数进行设置。
	 * @param array $securityinfo 商户支付信息
	 * @example 数组 $securityinfo 信息：
	 * @property string $key 商户支付密钥Key
	 * @property string $sslcert_path 商户证书路径（微支付V3版本）
	 * @property string $sslkey_path 商户支付密钥路径（微支付V3版本）
	 * @property string $appid 公众账号的appid
	 * @property string $appsecret 公众账号的appsecret
	 * @property string $mchid 财付通商户号编号
	 */
	public function __construct($securityinfo = NULL) {
		parent::__construct ( $securityinfo ); 											// 调用基类Common_util_pub构造函数设置商户支付信息
		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";		// 设置红包发放接口
	}
	
	/**
	 * 微信红包类生成接口参数XML。
	 */
	public function createXml() {
		try {
			// 检测必填参数
			if ($this->parameters ["mch_billno"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数mch_billno！" . "<br>" );
			} else if ($this->parameters ["send_name"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数send_name！" . "<br>" );
			} else if ($this->parameters ["re_openid"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数re_openid！" . "<br>" );
			} else if ($this->parameters ["total_amount"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数total_amount！" . "<br>" );
			} else if ($this->parameters ["total_num"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数total_num！" . "<br>" );
			} else if ($this->parameters ["wishing"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数wishing！" . "<br>" );
			} else if ($this->parameters ["client_ip"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数client_ip！" . "<br>" );
			} else if ($this->parameters ["act_name"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数act_name！" . "<br>" );
			} else if (mb_strlen ( $this->parameters ["act_name"] ) > 32) {
				throw new SDKRuntimeException ( "红包发放接口中，必填参数act_name必须少于32个字符.！" . "<br>" );
			} else if ($this->parameters ["remark"] == null) {
				throw new SDKRuntimeException ( "红包发放接口中，缺少必填参数remark！" . "<br>" );
			}
			$this->parameters ["wxappid"] = $this->config_appid; 					// 特别注意：奇葩的字段wxappid，红包新网页文档和原pdf文档好像格格不入
			$this->parameters ["mch_id"] = $this->config_mchid; 					// 财付通受理商户号
			$this->parameters ["nonce_str"] = $this->createNoncestr (); 			// 随机字符串
			$this->parameters ["sign"] = $this->getSign ( $this->parameters ); 		// 签名
			return $this->arrayToXml ( $this->parameters );
		} catch ( SDKRuntimeException $e ) {
			die ( $e->errorMessage () );
		}
	}
	
}

?>