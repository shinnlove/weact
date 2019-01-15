<?php
import ( 'Class.API.WeChatPayV3.WxPayPubHelper.WxPayPlatformPubHelper', APP_PATH, '.php' );	// 载入微动平台微支付SDK
/**
 * 微信支付的公共服务类。
 * @author 赵臣升。
 * 包含订单查询、对账单接口、退款申请接口、退款查询接口、
 */
class WeChatPayCommonAction extends Action {
	
	/**
	 * ==================PART1：微信支付公共服务类的公有函数部分===================
	 */
	
	/**
	 * 订单查询接口。
	 */
	public function orderQuery() {
		$e_id = '201406261550250006'; // 商家编号
		$out_trade_no = 'f9fa4c998c9f75665eafe5243e881e79'; // 订单编号
		
		$securityinfo = $this->getSecurityInfo ( $e_id ); // 获取商家安全信息
		$orderQuery = new OrderQuery_pub ( $securityinfo ); // 新建订单查询类对象，准备使用订单查询接口
		$orderQuery->setParameter ( "out_trade_no", $out_trade_no ); // 必填参数：要查询的商户订单号
		// 非必填参数，商户可根据实际情况选填
		//$orderQuery->setParameter ( "sub_mch_id", $sub_mch_id ); // 子商户号
		//$orderQuery->setParameter ( "transaction_id", $transaction_id ); // 微信订单号（如果与out_trade_no同时存在，则有限使用transaction_id）
		
		$orderQueryResult = $orderQuery->getResult (); // 获取订单查询结果
		
		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($orderQueryResult["return_code"] == "FAIL") {
			echo "通信出错：".$orderQueryResult['return_msg']."<br>";
		} else if($orderQueryResult["result_code"] == "FAIL"){
			echo "错误代码：".$orderQueryResult['err_code']."<br>";
			echo "错误代码描述：".$orderQueryResult['err_code_des']."<br>";
		} else{
			echo "交易状态：".$orderQueryResult['trade_state']."<br>";
			echo "设备号：".$orderQueryResult['device_info']."<br>";
			echo "用户标识：".$orderQueryResult['openid']."<br>";
			echo "是否关注公众账号：".$orderQueryResult['is_subscribe']."<br>";
			echo "交易类型：".$orderQueryResult['trade_type']."<br>";
			echo "付款银行：".$orderQueryResult['bank_type']."<br>";
			echo "总金额：".$orderQueryResult['total_fee']."<br>";
			echo "现金券金额：".$orderQueryResult['coupon_fee']."<br>";
			echo "货币种类：".$orderQueryResult['fee_type']."<br>";
			echo "微信支付订单号：".$orderQueryResult['transaction_id']."<br>";
			echo "商户订单号：".$orderQueryResult['out_trade_no']."<br>";
			echo "商家数据包：".$orderQueryResult['attach']."<br>";
			echo "支付完成时间：".$orderQueryResult['time_end']."<br>";
		}
	}
	
	
	/**
	 * 查询每个需要对账单接口的e_id,并进行对账单操作
	 */
	public function balanceBill(){
		//获取前一天的开始时间戳和结束时间戳mktime(hour,minute,second,month,day,year,is_dst)
		$datestarttime = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
		$dateendtime = mktime(23,59,59,date("m"),date("d")-1,date("Y"));
		//获取定账单日期(昨天),Y-m-d格式
		$bill_date = date("Ymd",strtotime("-1 day"));
		//查找支付时间(pay_time)在订单日期之内的所有企业编号(e_id)
		$ordermap = array(
				'pay_time' => array(
						'between' , array($datestarttime , $dateendtime)
				),
				'is_del' => 0
		);
		//获取e_id数组，查询结果格式如 array([0] => '201406261550250006')
		$eidlist = M('ordermain') -> where($ordermap) ->getField('e_id',true);
		//p($bill_date);p($eidlist);die;
		foreach ($eidlist as $eid){
			$this->downloadBill($eid, $bill_date);
		}
	}
	
	/**
	 * 对账单接口。
	 */
	public function downloadBill ($e_id , $bill_date) {
		/* $e_id = '201412021712300012';
		$bill_date = "20150107"; // 设置对账单日期 */
		
		
		//p($eidlist);die;
		//$e_id = "201406261550250006";
		//$bill_date = "20150522"; // 对账单日期不能超过当天，微信在每日9点以后生成对前一天的账单
		
		
		$securityinfo = $this->getSecurityInfo ( $e_id ); // 获取商家支付信息
		$downloadBill = new DownloadBill_pub ( $securityinfo ); // 新建对账单对象，准备使用对账单接口
		//设置必填参数
		$downloadBill->setParameter ( "bill_date", $bill_date ); // 对账单日期
		$downloadBill->setParameter ( "bill_type", "ALL" ); // 账单类型（ALL代表查询当日所有订单）
		//非必填参数，商户可根据实际情况选填
		//$downloadBill->setParameter ( "device_info", $device_info ); //设备号
		
		$downloadBillResult = $downloadBill->getResult (); // Step1:对账单接口结果（返回csv的字符串）
		//p($downloadBillResult);die;
		$billinfo = substr ( $downloadBillResult, 3 ); // Step2:从第4个字符位置开始提取，能去掉csv格式的红点
		$billlist = str_getcsv ( $billinfo ); // Step3:直接解析字符串成csv表格形式
		for($i = 0; $i< count ( $billlist ); $i ++) {
			for($j = 0; $j < count ( $billlist [$i] ); $j ++) {
				$billlist [$i] [$j] = str_replace ( '`', '', $billlist [$i] [$j] ); // Step4:替换`这个符号
			}
		}
		//p( $billlist );die;
		$comparesult = $this->billCompare ( $e_id, $bill_date, $billlist );
		//p($comparesult);die;
		
		if ($downloadBillResult ['return_code'] == "FAIL") {
			echo "通信出错：" . $downloadBillResult ['return_msg'];
		} else {
			print_r('<pre>');
			echo "【对账单详情】"."</br>";
			print_r($downloadBill->getXmlResponse());
			print_r('</pre>');
			if ($comparesult) {
				print_r ( "【对账单成功】" );
			} else {
				print_r ( "【对账单失败】" );
			}
		}
	}
	
	/**
	 * 退款申请接口，需要证书。
	 * 特别注意：证书应该放在用户下载不到的地方，避免被盗用。
	 */
	public function refund() {
		$e_id = '201406261550250006';
		
		// 小万订单退款
		/* $out_trade_no = "f9fa4c998c9f75665eafe5243e881e79"; // 发生交易的订单号
		$out_refund_no = "123456789"; // 商户退款单号，商户自定义主键（商户退款单号，32 个字符内、可包含字母,确保在商户系统唯一。同个退款单号多次请求，财付通当一个单处理，只会退一次款。如果出现退款不成功，请采用原退款单号重新发起，避免出现重复退款。）
		$total_fee = "200"; // 总金额需与订单号out_trade_no对应，demo中的所有订单的总金额为100分
		$refund_fee = "100"; // 要退款的金额 */
		
		// Lily订单退款
		$out_trade_no = "5c30532930c84cfae12080ca24351d0f"; // 发生交易的订单号
		$out_refund_no = "refund123456789"; // 商户退款单号，商户自定义主键（商户退款单号，32 个字符内、可包含字母,确保在商户系统唯一。同个退款单号多次请求，财付通当一个单处理，只会退一次款。如果出现退款不成功，请采用原退款单号重新发起，避免出现重复退款。）
		$total_fee = "5600"; // 总金额需与订单号out_trade_no对应，demo中的所有订单的总金额为100分
		$refund_fee = "5600"; // 要退款的金额 
		
		//$password = "7iSm2HvKT5"; // 操作员密码（1.1版本以后需要md5后POST），这个参数V2版本以后已经不用了
		
		$securityinfo = $this->getSecurityInfo ( $e_id ); // 获取商家安全信息
		// 转换证书为本地路径
		$securityinfo ['cert_p12'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['cert_p12'] );
		$securityinfo ['sslcert_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['sslcert_path'] );
		$securityinfo ['sslkey_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['sslkey_path'] );
		$securityinfo ['rootca_pem'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['rootca_pem'] );
		$refund = new Refund_pub ( $securityinfo ); // 新建退款申请对象
		//设置必填参数
		$refund->setParameter ( "out_trade_no", $out_trade_no ); // 商户订单号
		$refund->setParameter ( "out_refund_no", $out_refund_no ); // 商户退款单号
		$refund->setParameter ( "total_fee", $total_fee ); // 总金额
		$refund->setParameter ( "refund_fee",  $refund_fee  ); // 退款金额（支持部分退款）
		$refund->setParameter ( "op_user_id", $refund->config_mchid ); // 操作员（当前商户）
		//$refund->setParameter ( "op_user_passwd", md5 ( $password ) ); // 操作员密码（当前商户登录密码） // 这个参数V2版本以后已经不用了
		
		//非必填参数，商户可根据实际情况选填
		//$refund->setParameter ( "sub_mch_id", $sub_mch_id ); // 子商户号
		//$refund->setParameter ( "device_info", $device_info ); // 设备号
		//$refund->setParameter ( "transaction_id", $transaction_id ); // 微信订单号
		
		$refundResult = $refund->getResult(); // 调用接口返回结果
		
		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($refundResult["return_code"] == "FAIL") {
			echo "通信出错：".$refundResult['return_msg']."<br>";
		} else {
			echo "业务结果：".$refundResult['result_code']."<br>";
			echo "错误代码：".$refundResult['err_code']."<br>";
			echo "错误代码描述：".$refundResult['err_code_des']."<br>";
			echo "公众账号ID：".$refundResult['appid']."<br>";
			echo "商户号：".$refundResult['mch_id']."<br>";
			echo "子商户号：".$refundResult['sub_mch_id']."<br>";
			echo "设备号：".$refundResult['device_info']."<br>";
			echo "签名：".$refundResult['sign']."<br>";
			echo "微信订单号：".$refundResult['transaction_id']."<br>";
			echo "商户订单号：".$refundResult['out_trade_no']."<br>";
			echo "商户退款单号：".$refundResult['out_refund_no']."<br>";
			echo "微信退款单号：".$refundResult['refund_idrefund_id']."<br>";
			echo "退款渠道：".$refundResult['refund_channel']."<br>";
			echo "退款金额：".$refundResult['refund_fee']."<br>";
			echo "现金券退款金额：".$refundResult['coupon_refund_fee']."<br>";
		}
	}
	
	/**
	 * 退款查询接口，需要证书。
	 */
	public function refundQuery() {
		//使用退款查询接口
		$e_id = '201406261550250006';
		$out_trade_no = 'f9fa4c998c9f75665eafe5243e881e79'; // 要查询的订单编号
		
		$securityinfo = $this->getSecurityInfo ( $e_id ); // 获取支付信息
		// 转换证书为本地路径
		$securityinfo ['cert_p12'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['cert_p12'] );
		$securityinfo ['sslcert_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['sslcert_path'] );
		$securityinfo ['sslkey_path'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['sslkey_path'] );
		$securityinfo ['rootca_pem'] = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $securityinfo ['rootca_pem'] );
		$refundQuery = new RefundQuery_pub ( $securityinfo ); // 新建退款对象进行退款查询
		//设置必填参数
		$refundQuery->setParameter( "out_trade_no", $out_trade_no ); // 商户订单号
		// $refundQuery->setParameter( "out_refund_no", $out_refund_no ); // 商户退款单号
		// $refundQuery->setParameter( "refund_id", $refund_id ); // 微信退款单号
		// $refundQuery->setParameter( "transaction_id", $transaction_id ); // 微信退款单号
		//非必填参数，商户可根据实际情况选填
		//$refundQuery->setParameter ( "sub_mch_id", $sub_mch_id ); // 子商户号
		//$refundQuery->setParameter ( "device_info", $device_info ); // 设备号
		
		$refundQueryResult = $refundQuery->getResult(); //退款查询接口结果
		
		//商户根据实际情况设置相应的处理流程,此处仅作举例
		if ($refundQueryResult["return_code"] == "FAIL") {
			echo "通信出错：".$refundQueryResult['return_msg']."<br>";
		} else {
			echo "业务结果：".$refundQueryResult['result_code']."<br>";
			echo "错误代码：".$refundQueryResult['err_code']."<br>";
			echo "错误代码描述：".$refundQueryResult['err_code_des']."<br>";
			echo "公众账号ID：".$refundQueryResult['appid']."<br>";
			echo "商户号：".$refundQueryResult['mch_id']."<br>";
			echo "子商户号：".$refundQueryResult['sub_mch_id']."<br>";
			echo "设备号：".$refundQueryResult['device_info']."<br>";
			echo "签名：".$refundQueryResult['sign']."<br>";
			echo "微信订单号：".$refundQueryResult['transaction_id']."<br>";
			echo "商户订单号：".$refundQueryResult['out_trade_no']."<br>";
			echo "退款笔数：".$refundQueryResult['refund_count']."<br>";
			echo "商户退款单号：".$refundQueryResult['out_refund_no']."<br>";
			echo "微信退款单号：".$refundQueryResult['refund_id']."<br>";
			echo "退款渠道：".$refundQueryResult['refund_channel']."<br>";
			echo "退款金额：".$refundQueryResult['refund_fee']."<br>";
			echo "现金券退款金额：".$refundQueryResult['coupon_refund_fee']."<br>";
			echo "退款状态：".$refundQueryResult['refund_status']."<br>";
		}
	}
	
	/**
	 * 生成原生扫码支付二维码。
	 */
	public function nativeQRCode() {
		$e_id = '201406261550250006';
		$product_id = "00511572047eaf428da8281b323934ab"; // 自定义商品id，微动牛仔系列衬衫
		
		$productmap = array (
				'product_id' => $product_id,
				'is_del' => 0
		);
		$productinfo = M ( 'product_image' )->where ( $productmap )->find (); // 获取商品详情（主要是要一张图片）
		
		$securityinfo = $this->getSecurityInfo ( $e_id ); // 获取企业支付信息
		
		//设置静态链接
		$nativeLink = new NativeLink_pub ( $securityinfo );
		
		// 设置静态链接参数必填参数：product_id（在微动应该是scanpayid）
		$nativeLink->setParameter ( "product_id", "$product_id" ); // 商品id
		// 获取链接（返回的链接是长链接）
		$product_url = $nativeLink->getUrl ();
		
		//使用短链接转换接口
		$shortUrl = new ShortUrl_pub ( $securityinfo );
		//设置必填参数（扫码支付URL）
		$shortUrl->setParameter ( "long_url", "$product_url" ); // URL链接
		//获取短链接
		$codeUrl = $shortUrl->getShortUrl(); // 长链接转成短链接（扫码成功率更高）
		
		// 利用这个URL地址生成二维码
		
		// 初始化配置变量
		$usetype = "nativescan"; // 二维码用途，可指定product,customer,guide,subbranch,nativepay等多种
		$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/nativescan/" . $product_id . "/"; // 保存路径按导航、商品编号来存放，必须以./相对路径开头
		$saveqrname = $product_id; // 是否指定二维码文件名，默认空就用md5生成文件名
		$logopathname = $_SERVER ['DOCUMENT_ROOT'] . assemblepath ( $productinfo ['macro_path'] ); // 默认用第一张图片作为要嵌入作为logo的图片（相对路径+文件名与后缀）
		$url = $codeUrl; // 商品扫码支付二维码URL地址
		
		import ( 'Class.Common.phpqrcode.weactqrcode', APP_PATH, '.php' ); // 载入WeAct的二维码类
		$wqr = new WeActQRCode (); // 生成微动二维码类对象
		$createresult = $wqr->createQRCode ( $e_id, $url, $usetype, $saveqrpath, $saveqrname, $logopathname ); // 调用二维码函数生成二维码并返回生成结果
		if ($createresult) {
			p('生成商品扫码支付二维码成功！');die;
		} else {
			p('生成商品扫码支付二维码失败！');die;
		}
	}
	
	/**
	 * ==================PART2：微信支付公共服务类的私有函数部分===================
	 */
	
	/**
	 * 获得商家的安全支付信息。
	 * @param string $e_id 商家编号
	 * @return array $securityinfo 返回商家的支付信息
	 */
	private function getSecurityInfo($e_id = '') {
		$securityinfo = array (); // 企业的安全信息
		if (! empty ( $e_id )) {
			$securitytable = M ( 'secretinfo' );
			$security = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$securityinfo = $securitytable->where ( $security )->find (); // 查找企业敏感信息
		}
		return $securityinfo;
	}
	
	/**
	 * 调用微信对账单接口后，与数据库同步。
	 * @param string $e_id 企业编号
	 * @param array $wechatbilllist 微信账单列表
	 * @return boolean $checkbillresult 对账结果（true|false）
	 * [0] => Array
	 (
	 [0] => 交易时间
	 [1] => 公众账号ID			appid
	 [2] => 商户号				mch_id
	 [3] => 子商户号			sub_mch_id
	 [4] => 设备号				device_info
	 [5] => 微信订单号			transaction_id
	 [6] => 商户订单号			out_trade_no
	 [7] => 用户标识			openid
	 [8] => 交易类型			trade_type
	 [9] => 交易状态			result_code
	 [10] => 付款银行			bank_type
	 [11] => 货币种类			fee_type
	 [12] => 总金额			total_fee
	 [13] => 企业红包金额
	 [14] => 微信退款单号
	 [15] => 商户退款单号
	 [16] => 退款金额
	 [17] => 企业红包退款金额
	 [18] => 退款类型
	 [19] => 退款状态
	 [20] => 商品名称
	 [21] => 商户数据包
	 [22] => 手续费
	 [23] => 费率
	 )
	
	 [1] => Array
	 (
	 [0] => 2015-05-22 20:47:37
	 [1] => wxdb3bb7c95c0d5932
	 [2] => 10029370
	 [3] => 0
	 [4] =>
	 [5] => 1001530314201505220154549003
	 [6] => f9fa4c998c9f75665eafe5243e881e79
	 [7] => oeovpt13JCmPNLaU6dTSh8mt68N4
	 [8] => JSAPI
	 [9] => SUCCESS
	 [10] => ICBC_DEBIT
	 [11] => CNY
	 [12] => 2.00
	 [13] => 0.00
	 [14] => 0
	 [15] => 0
	 [16] => 0
	 [17] => 0
	 [18] =>
	 [19] =>
	 [20] => 支付商品订单 14322988206973
	 [21] =>
	 [22] => 0.01000
	 [23] => 0.60%
	 )
	 */
	public function billCompare($e_id = "", $billdate = "20150522", $wechatbilllist = NULL) {
		$billlistlen = count ( $wechatbilllist ); 	// 统计对账单表格行数（包括表头）
		if ($billlistlen <= 3) {
			return true; 							// 没订单直接返回
		}
		$checkbillresult = false; 					// 对账结果，默认为失败
		// 准备全局变量
		$notifytable = M ( 'wechatpaynotify' ); 	// 实例化支付通知表结构
		$localformatbill = array (); 				// 数据库原来的记录（从数据库中查询得到）
		$remoteformatbill = array (); 				// 格式化后的对账单数组
		$addlist = array (); 						// 要添加的数组
		$updatelist = array (); 					// 要更新的数组
		$dellist = array (); 						// 要删除的（只存入out_trade_no一个字段）数组
		$addresult = true; 							// 补单结果，因为后边会赋值的所以一开始true也没有关系（失败了就会改为false）
		$updateresult = true; 						// 更新单结果
		$deleteresult = true; 						// 删除单结果
		
		// Step1：格式化传来的csv表格，放入$formatbilllist
		for($i = 1; $i < $billlistlen - 2; $i ++) {
			// Step1：筛选字段
			$tempbill = array (
					'appid' => $wechatbilllist [$i] [1], 			// 商户appid
					'mch_id' => $wechatbilllist [$i] [2], 			// 商户号
					'transaction_id' => $wechatbilllist [$i] [5], 	// 交易号
					'out_trade_no' => $wechatbilllist [$i] [6], 	// 订单号
					'openid' => $wechatbilllist [$i] [7], 			// 微信用户openid
					'trade_type' => $wechatbilllist [$i] [8], 		// 交易类型
					'result_code' => $wechatbilllist [$i] [9], 		// 交易结果
					'bank_type' => $wechatbilllist [$i] [10], 		// 银行类型
					'fee_type' => $wechatbilllist [$i] [11], 		// 币种类型
					'total_fee' => intval ( $wechatbilllist [$i] [12] * 100 ), // 转化以分为单位
					'time_end' => date ( "YmdHis", strtotime ( $wechatbilllist [$i] [0] ) ), 	// 交易完成时间（格式化一下）
			);
			// Step2：存入索引
			$remoteformatbill [$tempbill ['out_trade_no']] = $tempbill; // 主键作为下标索引不会重复，作为哈希索引进去也行
		}
		
		// Step2：拉取数据库需要对账的记录
		$wechatpaymap = array (
				'e_id' => $e_id, // 当前对账的企业
				'time_end'  => array(
						'between', array ( $billdate . '000000', $billdate . '235959' )
				)
		);
		$localbilllist = $notifytable->where ( $wechatpaymap )
		->field ( 'paynotify_id, appid, mch_id, transaction_id, out_trade_no, openid, trade_type, result_code, bank_type, fee_type, total_fee' )->select (); // 查询本地记录
		// 将本地账单数组改成键值对索引
		foreach($localbilllist as $localsinglebill) {
			$localsinglebill ['tobe_deleted'] = 1; // 先置为1，默认为都要删除
			$localformatbill [$localsinglebill ['out_trade_no']] = $localsinglebill; // 将本地支付通知信息其放入索引中
		}
		
		// 遍历新数组，生成增，改数组
		foreach($remoteformatbill as $remotekey => $remotevalue) {
			if (isset ( $localformatbill [$remotekey] )) {
				// 如果在本地数组中存在，可能是original或update
				$localformatbill [$remotekey] ['tobe_deleted'] = 0; 	// 如果原来的存在，就将其置为不用删除
				$templocalcheck = $localformatbill [$remotekey]; 		// 取出要比对的值
				unset ( $templocalcheck ['paynotify_id'] ); 			// 去除$templocalcheck主键paynotify_id
				unset ( $templocalcheck ['tobe_deleted'] ); 			// 去除$templocalcheck要删除的字段tobe_deleted
				if ($templocalcheck != $remotevalue) {
					// 有一个变量不等，就更新：比的是$templocalcheck，push的是$remotevalue
					$remotevalue ['paynotify_id'] = $localformatbill [$remotekey] ['paynotify_id']; // 带上主键
					array_push ( $updatelist, $remotevalue );			// 要更新的信息（带主键）
				}
			} else {
				$newbill = $remotevalue;
				$newbill ['paynotify_id'] = md5 ( uniqid ( rand (), true ) ); 	// 生成补单主键
				$newbill ['e_id'] = $e_id; 										// 当前企业
				$newbill ['is_subscribe'] = "Y"; 								// 补单完成，默认当时用户确实关注（无从得知了）
				$newbill ['time_end'] = $billdate . "120000"; 					// 订单完结时间就算当日的中午12点，这样补单的也在当天接收到
				$newbill ['return_code'] = "SUCCESS"; 							// 得到微信响应，这里对账算作是得到响应
				$newbill ['receive_time'] = time (); 							// 接收时间就取现在时间，算是系统现在补单
				$newbill ['remark'] = "该订单的支付通知接收遗漏，当前记录由系统补单完成。"; 		// 补单理由
				array_push ( $addlist, $newbill ); 								// 将远程数组加入新增的中
				// 可以在此进行补单，并对支付成功的订单进行已支付处理，但是事务startTrans()要在这个之前了
			}
		}
		
		// 生成删除数组
		foreach($localformatbill as $localdelkey => $localdelvalue){
			if ($localdelvalue ['tobe_deleted'] == 1) {
				array_push ( $dellist, $localdelvalue ['out_trade_no'] );
			}
		}
		
		// 开始事务操作
		$notifytable->startTrans (); 
		if (! empty ( $dellist )) {
			$delnotifymap = array (
					'out_trade_no' => array ( "in", implode ( ",", $dellist ) ), // 外键在其中
					'is_del' => 0 // 没有被删除的
			);
			$deleteresult = $notifytable->where ( $delnotifymap )->delete (); // 首先执行循环删除操作
		}
		if (! empty ( $updatelist )) {
			foreach ($updatelist as $singleupdate){
				$updateresult += $notifytable->save ( $singleupdate ); // 循环更新
			}
		}
		if (! empty ( $addlist )){
			$addresult = $notifytable->addAll ( $addlist ); // 批量增
		}
		//p($deleteresult);p($updateresult);p($addresult);die;
		if ($deleteresult && $updateresult && $addresult) {
			// 三个步骤都执行成功，才提交事务
			$notifytable->commit ();
			$checkbillresult = true;
		} else {
			$notifytable->rollback ();
		}
		return $checkbillresult; // 返回结果
	}
	
}
?>