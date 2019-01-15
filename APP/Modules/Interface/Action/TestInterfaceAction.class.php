<?php
/**
 * 本控制器用来测试接口调试。
 * @author 微动团队。
 * CreateTime：2015/03/22 21:23:25.
 */
class TestInterfaceAction extends Action {
	
	/**
	 * =========PART1：固定常量区域===========
	 */
	
	var $IOS_CONST_TOKEN = "huhui"; // 固定token
	var $ios_appid = "appleapp"; // 固定ios_appid
	var	$ios_appsecret = "appleappinterface"; // 固定ios_appsecret
	var $ANDROID_CONST_TOKEN = "lichuyang"; // 固定token
	var $android_appid = "androidapp"; // 固定android_appid
	var	$android_appsecret = "appleandroidinterface"; // 固定android_appsecret
	
	var $header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' ); // 定义https请求文件头
	
	/**
	 * =========PART2：access_token规则===========
	 */
	
	/**
	 * 微动生成随机数字符串的函数。
	 * @return string $nonce 随机数字符串
	 */
	public function generateNonce() {
		$timenow = time (); // 当前时间戳
		$randomcode = randCode ( 8, 1 ); // 随机8位数字
		$nonce = sha1 ( $timenow . $randomcode ); // 利用sha1进行加密
		return $nonce;
	}
	
	/**
	 * 生成微动签名函数。
	 * @return string $signature 返回微动签名
	 */
	private function generateSignature() {
		$token = $this->CONST_TOKEN; // 获取固定token
		$timestamp = time (); // 当前时间戳
		$nonce = $this->generateNonce (); // 获取随机数nonce
		// Step2：生成签名
		$tmpArr = array ( $token, $timestamp, $nonce); // 准备工作：放入同一个数组中
		sort ( $tmpArr, SORT_STRING ); // Step1：按字母顺序表排序
		$tmpStr = implode ( $tmpArr ); // Step2：连接数组值变为字符串
		$signature = sha1 ( $tmpStr ); // Step3：进行sha1加密
		return $signature;
	}
	
	/**
	 * APP授权：access_token生成规则。
	 */
	public function generateToken() {
		//p(strtoupper ( md5 ( $this->IOS_CONST_TOKEN ) . md5 ( $this->ios_appid ) . md5 ( $this->ios_appsecret ) ));die; // IOS端token
		//p(strtoupper ( md5 ( $this->ANDROID_CONST_TOKEN ) . md5 ( $this->android_appid ) . md5 ( $this->android_appsecret ) ));die; // 安卓端token
		//return strtoupper ( md5 ( $this->IOS_CONST_TOKEN ) . md5 ( $this->ios_appid ) . md5 ( $this->ios_appsecret ) ); // IOS端access_token生成规则
		return strtoupper ( md5 ( $this->ANDROID_CONST_TOKEN ) . md5 ( $this->android_appid ) . md5 ( $this->android_appsecret ) ); // IOS端access_token生成规则
	}
	
	/**
	 * =========PART3：对外接口部分===========
	 */
	
	/**
	 * 接口：测试APP登录接口，该接口为POST类型接口。
	 */
	public function userLogin() {
		$url = "http://localhost/weact/Interface/ExportAppLogin/checkLogin?access_token=" . $this->generateToken ();
		$params = array (
				'account' => "15010002001", // 诗人测试账号
				'password' => md5 ( "19881218" ) // 诗人账号密码
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购密码接口，该接口为POST类型接口。
	 */
	public function modifyGuidePassword() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuidePassword?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001', // 要修改密码的导购编号
				'old_pwd' => md5 ( '19881218' ), // md5加密的原密码
				'new_pwd' => md5 ( '180019' ) // md5加密新密码
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 模拟三方公司调用更改导购在线或挂起状态接口的代码，该接口为POST类型。
	 */
	public function alterGuideStatus() {
		$url = "http://localhost/weact/Interface/ImportGuide/alterGuideStatus?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001',
				'status' => 2
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：测试导购信息接口，该接口为GET类型接口。
	 */
	public function getGuideInfo() {
		$url = "http://localhost/weact/Interface/ExportGuide/guideInfo";
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' => 'g10002'
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购姓名接口，该接口为POST类型。
	 */
	public function modifyGuideName() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideName?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001',
				'name' => "李晓倩"
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购昵称接口，该接口为POST类型。
	 */
	public function modifyGuideNickname() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideNickname?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001',
				'nickname' => "小倩"
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购性别接口，该接口为POST类型。
	 */
	public function modifyGuideSex() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideSex?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001',
				'sex' => 2
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购身份证号接口，该接口为POST类型。
	 */
	public function modifyGuideIdCard() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideIDCard?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10002',
				'id' => "330482198812180019"
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购生日接口，该接口为POST类型。
	 */
	public function modifyGuideBirthday() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideBirthday?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001',
				'birthday' => timetodate ( time (), true )
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购手机号接口，该接口为POST类型。
	 */
	public function modifyGuideCellphone() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideCellphone?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10002',
				'cellphone' => "15021237551" 
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购个性签名接口，该接口为POST类型。
	 */
	public function modifyGuideSignature() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyGuideSignature?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10002',
				'signature' => "心有多大，舞台就有多大。"
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口，修改导购粉丝备注名的接口，该接口为POST类型。
	 */
	public function modifyFansRemarkName() {
		$url = "http://localhost/weact/Interface/ImportGuide/modifyFansRemarkName?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g20001',
				'cid' => '201406030406178523',
				'remarkname' => "棉花糖"
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口：修改导购头像接口，该接口为POST类型。
	 */
	public function modifyGuideHeadImage() {
		$gid = "g10025"; // 模拟一个导购编号
		$picturepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ ."/Updata/123.jpg"; // 模拟一张要发送的图片（本地磁盘上必须有这张图片）
		$url = "http://localhost/weact/Interface/ReceiveAppFile/modifyGuideHeadImage?access_token=" . $this->generateToken () . "&gid=" . $gid;
		$filedata ['media'] = "@" . $picturepath; // 文件数据
		$httpresult = http ( $url, $filedata, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * ***********************开始***导购修改顾客个人信息接口*********************************
	 */
	
	/**
	 * 接口:修改顾客性别接口
	 */
	public function modifyCustomerSex(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerSex?access_token=" . $this->generateToken ();
		$params = array (
				'sex' => 1,
				'gid' =>'201506081506297979',
				'cid' => '201506191406274478'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $httpresult ); die ();
	}
	
	/**
	 * 接口:修改顾客生日接口
	 */
	public function modifyCustomerBirthday(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerBirthday?access_token=" . $this->generateToken ();
		$params = array (
				'birth' => "1878-12-19 00:00:00",
				'gid' =>'201506081506297979',
				'cid' => '201506191406274478'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口:修改顾客手机号码接口
	 */
	public function modifyCustomerCellphone(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerCellphone?access_token=" . $this->generateToken ();
		$params = array (
				'contactnum' => "15021237552",
				'gid' =>'201506081506297979',
				'cid' => '201506191406274478'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口:修改顾客尺码/体型接口
	 */
	public function modifyCustomerSize(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerSize?access_token=" . $this->generateToken ();
		$params = array (
				'size' => "XXL",
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口:修改顾客个人穿衣喜好接口
	 */
	public function modifyCustomerWearPrefer(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerWearPrefer?access_token=" . $this->generateToken ();
		$params = array (
				'wearprefer' => "宽松款式",
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * 接口:修改顾客详细备注接口
	 */
	public function modifyCustomerDetailRemark(){
		$url = "http://localhost/weact/Interface/ImportCustomer/modifyCustomerDetailRemark?access_token=" . $this->generateToken ();
		$params = array (
				'detailremark' => "爆款哦",
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p ( $result ); die ();
	}
	
	/**
	 * **************************结束***导购修改顾客个人信息接口*********************************
	 */
	
	/**
	 * 获取、同步单个顾客尺码、体型字段接口
	 */
	public function customerSizeInfo(){
		$url = "http://localhost/weact/Interface/ExportCustomer/customerSizeInfo";
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $httpresult ); die ();
	}
	
	/**
	 * 获取、同步单个顾客尺码、体型字段接口
	 */
	public function customerWearPreferInfo(){
		$url = "http://localhost/weact/Interface/ExportCustomer/customerWearPreferInfo";
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $httpresult ); die ();
	}
	
	/**
	 * 获取、同步单个顾客尺码、体型字段接口
	 */
	public function customerDetailNoteInfo(){
		$url = "http://localhost/weact/Interface/ExportCustomer/customerDetailNoteInfo";
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' =>'g10001',
				'cid' => '201503162003178829'
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $httpresult ); die ();
	}
	
	
	/**
	 * 获取某导购的顾客列表，该接口为GET类型接口。
	 */
	public function getGuideCustomerList() {
		$url = 'http://localhost/weact/Interface/ExportCustomer/guideCustomerList';
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' => 'g10001' // 模拟某导购的编号
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 接口：测试顾客信息接口，该接口为GET类型接口。
	 */
	public function getCustomerInfo() {
		$url = 'http://localhost/weact/Interface/ExportCustomer/customerInfo';
		$params = array (
				'access_token' => $this->generateToken (),
				'gid' => 'g10001', // 当前顾客的导购
				'cid' => '201503021503437865' // 模拟♥詩人灬Õ的编号
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * =========PART4：聊天接口部分===========
	 */
	
	/**
	 * 测试导购APP的通信接口。
	 */
	public function sendMsg() {
		$CONST_NORMAL_TEST = 1; // 是否正常测试
		$url = "http://localhost/weact/Interface/AppMessage/receiveAppMsg?access_token=" . $this->generateToken ();
		$params = array (
				'appmsg_id' => 'woxiangyaopaoche', // 模拟一个三方消息的主键（用来排重）
				'msg_type' => 0, // 消息类型包括文本、图片、语音，默认0代表文本消息，1代表图片，2代表声音
				'eid' => '201406261550250006', // 商家编号
				'sid' => '070b107fd7ecae417e7a2266ebd7bc9c', // 分店编号
				'from_guide' => 'g10001', // 消息来自的导购编号
				'to_customer' => '201503021503437865', // 消息送达的顾客编号
				'time' => time (), // 消息时间
				'content' => '我想要一辆阿斯顿马丁！' // 消息内容
		);
		
		if ($CONST_NORMAL_TEST) {
			// 普通测试
			$jsoninfo = json_encode ( $params );
			$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
			$sendresult = json_decode ( $httpresult, true ); // decode utf-8
			p($sendresult); die ();
		} else {
			// 压力测试
			$responselist = array (); // 服务器响应数组
			for($i = 0; $i < 26; $i ++) {
				$params ['appmsg_id'] = md5 ( uniqid ( rand (), true ) ); // 模拟一个三方消息的主键（用来排重）
				//$params ['appmsg_id'] = 'yizhidabenzhu'; // 模拟一个三方消息的主键（用来排重）
				$jsoninfo = json_encode ( $params );
				$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
				$responselist [$i] = json_decode ( $httpresult, true ); // decode utf-8
			}
			p($responselist); die ();
		}
	}
	
	/**
	 * 测试导购APP的通信接口。
	 */
	public function sendGroupMsg() {
		$CONST_NORMAL_TEST = 1; // 是否正常测试
		$url = "http://www.we-act.cn/weact/Interface/AppMessage/sendGroupMsg?access_token=" . $this->generateToken ();
		$params = array (
				'msg_type' => 0, // 消息类型包括文本、图片、语音，默认0代表文本消息，1代表图片，2代表声音
				'eid' => '201406261550250006', // 商家编号
				'sid' => '070b107fd7ecae417e7a2266ebd7bc9c', // 分店编号
				'from_guide' => 'g10001', // 消息来自的导购编号
				'to_group' => '100', // 消息送达的顾客编号
				'time' => time (), // 消息时间
				'content' => '我想要一辆阿斯顿马丁！' // 消息内容
		);
	
		if ($CONST_NORMAL_TEST) {
			// 普通测试
			$jsoninfo = json_encode ( $params );
			$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
			$sendresult = json_decode ( $httpresult, true ); // decode utf-8
			p($sendresult); die ();
		} else {
			// 压力测试
			$responselist = array (); // 服务器响应数组
			for($i = 0; $i < 20; $i ++) {
				$params ['appmsg_id'] = md5 ( uniqid ( rand (), true ) ); // 模拟一个三方消息的主键（用来排重）
				//$params ['appmsg_id'] = 'yizhidabenzhu'; // 模拟一个三方消息的主键（用来排重）
				$jsoninfo = json_encode ( $params );
				$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
				$responselist [$i] = json_decode ( $httpresult, true ); // decode utf-8
			}
			p($responselist); die ();
		}
	}
	
	/**
	 * 测试导购发送图片的通信接口。
	 */
	public function sendPicture() {
		// 第一步，调用微动上传图片接口，返回获得picture的id和path
		$picturepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/123456.jpg"; // 模拟一张要发送的图片（本地磁盘上必须有这张图片）
		$uploadresult = $this->uploadMedia ( $picturepath, 'image' ); // 上传图片多媒体
		
		// 第二步，调用微动发送消息接口，将返回的mediaid发送到微动服务器
		$url = "http://localhost/weact/Interface/AppMessage/receiveAppMsg?access_token=" . $this->generateToken (); // 向微动发送消息的地址
		$params = array (
				'appmsg_id' => 'yizhidabenzhu2', // 模拟一个三方消息的主键（用来排重）
				'msg_type' => 1, // 消息类型包括文本、图片、语音，默认0代表文本消息，1代表图片，2代表声音
				'eid' => '201406261550250006', // 商家编号
				'sid' => '070b107fd7ecae417e7a2266ebd7bc9c', // 分店编号
				'from_guide' => 'g10001', // 消息来自的导购编号
				'to_customer' => '201503021503437865', // 消息送达的顾客编号
				'time' => time (), // 消息时间
				'content' => $uploadresult ['data'] ['mediaid'], // 微动上传接口返回的media_id
				'mediapath' => $uploadresult ['data'] ['mediapath'] // 微动上传接口返回的原图路径
		);
		$picinfo = json_encode ( $params );
		$imghttpresult = http ( $url, $picinfo, 'POST', $this->header, true ); // 发送数据
		$sendresult = json_decode ( $imghttpresult, true ); // decode utf-8
		p($sendresult); die ();
	}
	
	/**
	 * 测试导购发送音频文件的通信接口。
	 */
	public function sendVoice() {
		// 第一步，调用微动上传图片接口，返回获得picture的id和path
		$voicepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/lovedock.mp3"; // 模拟一张要发送的图片（本地磁盘上必须有这段语音）
		$uploadresult = $this->uploadMedia ( $voicepath, 'voice' ); // 上传声音多媒体
		
		// 第二步，调用微动发送消息接口，将返回的mediaid发送到微动服务器
		$url = "http://localhost/weact/Interface/AppMessage/receiveAppMsg?access_token=" . $this->generateToken (); // 向微动发送消息的地址
		$params = array (
				'appmsg_id' => 'yizhidabenzhu36', // 模拟一个三方消息的主键（用来排重）
				'msg_type' => 2, // 消息类型包括文本、图片、语音，默认0代表文本消息，1代表图片，2代表声音
				'eid' => '201406261550250006', // 商家编号
				'sid' => '070b107fd7ecae417e7a2266ebd7bc9c', // 分店编号
				'from_guide' => 'g10001', // 消息来自的导购编号
				'to_customer' => '201503021503437865', // 消息送达的顾客编号
				'time' => time (), // 消息时间
				'content' => $uploadresult ['data'] ['mediaid'], // 微动上传接口返回的media_id
				'mediapath' => $uploadresult ['data'] ['mediapath'] // 微动上传接口返回的声音文件路径
		);
		$voiceinfo = json_encode ( $params );
		$voicehttpresult = http ( $url, $voiceinfo, 'POST', $this->header, true ); // 发送数据
		$voicesendresult = json_decode ( $voicehttpresult, true ); // decode utf-8
		p($voicesendresult); die ();
	}
	
	/**
	 * 测试查询某导购新消息的通信接口。
	 */
	public function queryNewMsg() {
		$newmsglist = $this->queryNewMessage (); // 查询新消息
		p ( $newmsglist ); die ();
	}
	
	/**
	 * 测试三方APP查询新消息后给出消息接收的确认。
	 */
	public function appMsgConfirm() {
		$newmsglist = $this->queryNewMessage (); // 查询新消息
		$newmsginfo = $newmsglist ['data'] ['newmsglist']; // 提取新消息二维数组
		$newmsgcount = count ( $newmsginfo ); // 得到新消息数目
		if ($newmsgcount) {
			// 如果有新消息
			$newmsgidlist = array (); // 新消息数组
			for($i = 0; $i < $newmsgcount; $i ++) {
				$newmsgidlist [$i] ['msgid'] = $newmsginfo [$i] ['msgid']; // 新消息确认数组
			}
			
			$url = "http://localhost/weact/Interface/AppMessage/receiveMsgConfirm?access_token=" . $this->generateToken (); // 新消息确认的地址
			$params = array (
					'confirmlist' => $newmsgidlist // 确认接收到的新消息列表数组
			);
			$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
			$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
			$confirmresult = json_decode ( $httpresult, true ); // decode utf-8
			p($httpresult);p($confirmresult);die;
		}
		p($newmsglist);die;
	}
	
	/**
	 * 测试三方APP查询某导购与某顾客的历史消息接口。
	 */
	public function queryHistoryMsg() {
		$url = "http://localhost/weact/Interface/AppMessage/queryHistoryMsg?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001', // 模拟某导购的编号
				'cid' => '201503021503437865', // 模拟该导购对话的顾客编号
				'next_start' => 10, // 下一条消息是要读取第几条，如果没有该参数，默认读取第一条消息（历史消息数组下标从0开始）
				'perpage' => 50 // 每页所查询的消息数目，最小10条，最大100条，如果不给出参数，默认一页50条消息
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p($result);die;
	}
	
	/**
	 * =========PART5：导购顾客分组接口===========
	 */
	
	/**
	 * 查询企业所有分组以及对应分组下的粉丝(可能为空)。
	 */
	public function queryAllGroupCustomer() {
		$url = "http://localhost/weact/Interface/CustomerGroup/queryAllGroupCustomer?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001'
		);
		$jsoninfo = json_encode ( $params );
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 查询企业所有分组。
	 */
	public function queryAllGroup() {
		$url = "http://localhost/weact/Interface/CustomerGroup/queryAllGroup?access_token=" . $this->generateToken ();
		$params = array (
				'eid' => '201406261550250006'
		);
		$jsoninfo = json_encode ( $params );
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 查询某个导购在某个分组下的粉丝（可能为空）。
	 */
	public function queryCustomerGroup() {
		$url = "http://localhost/weact/Interface/CustomerGroup/queryCustomerGroup?access_token=" . $this->generateToken ();
		$params = array (
				'eid' => '201406261550250006',
				'gid' => 'g10001',
				'groupid' => 0
		);
		$jsoninfo = json_encode ( $params );
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * 修改顾客分组。
	 */
	public function changeCustomerGroup() {
		$url = "http://localhost/weact/Interface/CustomerGroup/changeCustomerGroup?access_token=" . $this->generateToken ();
		$params = array (
				'eid' => '201406261550250006',
				'gid' => 'g10001',
				'cid' => '201503021503437865',
				'togroupid' => 0
		);
		$jsoninfo = json_encode ( $params );
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * =========PART5：本类共用私有函数部分===========
	 */
	
	/**
	 * 私有函数，上传多媒体文件到微动服务器并得到返回值。
	 * @param string $mediapath 多媒体文件在磁盘上的绝对路径
	 * @param string $mediatype 多媒体文件类型，分为：image,voice,video,music,thumb等类型
	 * @return array $uploadresult 返回上传多媒体文件的结果
	 */
	private function uploadMedia($mediapath = '', $mediatype = 'image') {
		// 第一步，调用微动上传图片接口，返回获得picture的id和path
		$eid = '201406261550250006'; // 假设微动商家导购发送给微动顾客消息
		$url = "http://localhost/weact/Interface/ReceiveAppFile/appUploadMedia?access_token=" . $this->generateToken () . "&eid=" . $eid . "&type=" . $mediatype; // 向微动上传多媒体的地址
		$filedata ['media'] = "@" . $mediapath; // 文件数据
		$httpresult = http ( $url, $filedata, 'POST', $this->header, true ); // 发送数据
		$uploadresult = json_decode ( $httpresult, true ); // utf-8解码
		return $uploadresult; // 返回多媒体文件上传后的结果
	}
	
	/**
	 * 私有函数，模拟查询某导购的新消息。
	 * @return array $result 查询到的新消息
	 */
	private function queryNewMessage() {
		$url = "http://localhost/weact/Interface/AppMessage/queryNewMsg?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001' // 模拟某导购的编号
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		return $result;
	}
	
}
?>