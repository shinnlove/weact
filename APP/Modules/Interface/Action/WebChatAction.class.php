<?php
/**
 * 微动网页聊天接口基础授权控制器，所有网页聊天的接口继承自此控制器，
 * 考虑到自身内部的原因，不采用token而采用CSRFToken来防御注入，功能未稳定之前先不引入。
 * @author 赵臣升。
 */
class WebChatAction extends Action {
	
	/**
	 * 数据格式是json。
	 * @var string $datatype 当数据格式是json的时候直接输出，当数据是jsonp格式的时候，加上回调函数。
	 */
	protected $datatype = "json";
	
	/**
	 * jsonp数据格式的回调函数名称
	 * @var string callback 跨域回调函数名称
	 */
	protected $callback = "weactCallback";
	
	/**
	 * 服务器响应信息。
	 * @var array $ajaxresult 服务器返回信息
	 */
	protected $ajaxresult = array (); 
	
	/**
	 * 接口基类初始化函数，验证口令正确性。
	*/
	public function _initialize() {
		$this->datatype = "jsonp"; // 设置消息可以跨域，2015/08/01 16:55:36.
		if ($this->datatype == "jsonp") {
			$this->callback = $_REQUEST ['callback']; // 接收回调函数名称，字段永远为callback，默认weactCallback
			if (empty ( $this->callback )) {
				$this->callback = "weactCallback";
			}
		}
		// Step1：接收消息参数
		$this->postInfo = file_get_contents ( 'php://input', 'r' ); // 获取网页端post过来的数据（特别注意：如果是post方式，access_token不在post数据里）
		$this->getInfo = $_GET; // GET获取网页端URL上的参数和URL路径（access_token放在URL上作为POST的地址）
		$this->fileInfo = $_FILES; // 尝试获取文件数据（文件类型接口数据会用到）
		
		if ($this->datatype == "jsonp" || empty ( $this->postInfo )) {
			$this->postInfo = $_REQUEST; // 防止jsonp请求失败
		}
		
		// Step2：对发送消息身份者进行验证
		$access_token = $this->getInfo ['access_token']; // 验证身份永远都是GET参数的access_token，接收发送过来的口令
		if (empty ( $access_token )) exit ( 'invalid token!' ); // 没有口令
		$validateinfo = $this->validateToken ( $access_token ); // 获得验证结果
		if (! $validateinfo) exit ( 'invalid token!' ); // 没查询到有口令
		
		// Step3：生成微动服务器签名
		$this->ajaxresult = $this->generateSignature ( $validateinfo ['const_token'] ); // 初始化微动签名
		
		// 临时处理URL
		$requesturl = implode ( "/", $this->getInfo ['_URL_'] ); // 获得三方请求的URL路由
		
		// Step4：通过验证记录日志
		if (! empty ( $this->postInfo )) {
			$this->logRecord ( $access_token, $requesturl, $this->postInfo, true ); // post记录
		} else if (! empty ( $this->getInfo ) && empty ( $this->fileInfo )) {
			$tempgetinfo = $this->getInfo;
			unset ( $tempgetinfo ['_URL_'] ); // 注销路由信息
			$this->logRecord ( $access_token, $requesturl, jsencode ( $tempgetinfo ) ); // get记录
		} else if (! empty ( $this->getInfo ) && ! empty ( $this->fileInfo )) {
			$tempgetinfo = $this->getInfo;
			unset ( $tempgetinfo ['_URL_'] ); // 注销路由信息
			$this->logRecord ( $access_token, $requesturl, jsencode ( $tempgetinfo ) ); // file记录
		} else {
			exit ( 'SUCCESS' ); // 直接输出接收OK，不做任何操作
		}
	}
	
	/**
	 * 验证token是否正确的函数。
	 * @param string $CONST_TOKEN 固定token串，默认为weactinterface
	 * @param string $appid 开发者编号huhuiwangluo
	 * @param string $appsecret 开发者密钥cooperate
	 * @return boolean|array $tokencorrect 验证token正确性的结果|或者token信息
	 */
	private function validateToken($access_token = '') {
		$tokencorrect = false; // 默认token是不正确的
		if (! empty ( $access_token )) {
			$validatemap = array (
					'access_token' => $access_token,
					'is_del' => 0
			);
			$tokencorrect = M ( 'weacttoken' )->where ( $validatemap )->find (); // 验证签名是否在数据库中存在
		}
		return $tokencorrect;
	}
	
	/**
	 * 微动生成随机数字符串的函数。
	 * @return string $nonce 随机数字符串
	 */
	protected function generateNonce() {
		$timenow = time (); // 当前时间戳
		$randomcode = randCode ( 8, 1 ); // 随机8位数字
		$nonce = sha1 ( $timenow . $randomcode ); // 利用sha1进行加密
		return $nonce;
	}
	
	/**
	 * 生成微动签名函数。
	 * @param string $const_token 该开发者固定的Token
	 * @return array $signatureinfo 服务器签名信息
	 */
	protected function generateSignature($const_token = '') {
		$token = $const_token; // 获取固定token
		$timestamp = time (); // 当前时间戳
		$nonce = $this->generateNonce (); // 获取随机数nonce
		// Step2：生成签名
		$tmpArr = array ( $token, $timestamp, $nonce); // 准备工作：放入同一个数组中
		sort ( $tmpArr, SORT_STRING ); // Step1：按字母顺序表排序
		$tmpStr = implode ( $tmpArr ); // Step2：连接数组值变为字符串
		$signature = sha1 ( $tmpStr ); // Step3：进行sha1加密
		// 为本类ajaxresult赋值
		$signatureinfo = array (
				'timestamp' => $timestamp,
				'nonce' => $nonce,
				'signature' => $signature
		);
		return $signatureinfo;
	}
	
	/**
	 * 对三方发来的数据进行记录日志文件。
	 * @param string $access_token 发消息的三方是谁
	 * @param string $requesturl 请求接口的路由地址
	 * @param array $recordinfo 发来的消息
	 * @param boolean $is_post 是否为post消息
	 */
	private function logRecord($access_token = '', $requesturl = '', $recordinfo = NULL, $is_post = FALSE) {
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/InterfaceLog/webchat/"; // 网页聊天接口数据文件夹
		$filename = "webchatmsg" . timetodate ( time (), true ) . ".log"; // 文件名按天存放
		if (! file_exists ( $filepath ) ) mkdirs ( $filepath ); // 如果没有存在文件夹，直接创建文件夹
		$this->log_record ( $access_token, $requesturl, $filepath . $filename, $recordinfo, $is_post ); // 调用日志文件记录微信发来的post消息
	}
	
	/**
	 * 接收到微信消息并打印log日志文件函数log_record。
	 * @param string $access_token 发消息的三方是谁
	 * @param string $requesturl 请求接口的路由地址
	 * @param string $filepathname 文件路径和文件名
	 * @param string $context 要记录的日志文件内容
	 * @param boolean $is_post 是否为post消息
	 */
	private function log_record($access_token = '', $requesturl = '', $filepathname = '', $context = '', $is_post = FALSE ) {
		$msgtype = $is_post == true ? "post发送" : "get请求";
		$fp = fopen ( $filepathname, "a" ); // 打开文件获得读写所有权限
		flock ( $fp, LOCK_EX ); // 锁定日志文件
		fwrite ( $fp, "收到网页端聊天" . $msgtype . "消息：（尝试请求接口路由地址：" . $requesturl . "）" . strftime ( "%Y-%m-%d %H:%M:%S", time () ) . "\n" . $context . "\n\n" ); // 写入日志文件内容
		flock ( $fp, LOCK_UN ); // 解锁日志文件
		fclose ( $fp ); // 释放文件解锁
	}
	
}
?>