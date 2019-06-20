<?php
/**
 * 微信SDK接口类。
 * @author 赵臣升、万路康。
 * CreateTime：2014/05/20.
 * 特别注意：该类是平台级的企业用户接口类。
 */
class ThinkWechat {
	
	/**
	 * 当前企业的e_id。
	 * @var String
	 */
	var $e_id='';
	
	/**
	 * 企业开发者APPID。
	 * @var String
	 * */
	var $APPID='';
	
	/**
	 * 企业开发者APPSECRET。
	 * @var String
	 * */
	var $APPSecret='';
	
	/**
	 * 微信推送过来的数据或响应数据
	 * @var array
	 */
	private $data = array();							//该$data数组是用来存解析xml后的数据

	/**
	 * 服务器主动发送给微信服务器的数据
	 * @var array
	*/
	private $send = array();
	
	/**
	 * 本类ThinkWechat的构造函数。
	 * @param string $e_id 企业编号
	 * @param string $appid 企业开发者APPID
	 * @param string $appsecret 企业开发者APPSECRET
	 */
	public function __construct($e_id='', $appid='', $appsecret=''){
		$this->e_id = $e_id;				//本类要操作的企业编号（微动平台下的）
		$this->APPID = $appid;				//本类要操作的APPID
		$this->APPSecret = $appsecret;		//本类要操作的$APPSecret
	}
	
	/**
	 * 返回当前类的商家编号。
	 * @return string
	 */
	public function gete_id(){
		return $this->e_id;
	}
	
	/**
	 * 返回当前类的商家APPID。
	 * @return string
	 */
	public function getAPPID(){
		return $this->APPID;
	}
	
	/**
	 * 返回当前类的APPSecret。
	 * @return string
	 */
	public function getAPPSecret(){
		return $this->APPSecret;
	}
	
	/**
	 * 向微动中控系统请求access_token函数。
	 * @return string $access_token 企业的token信息
	 */
	private function getToken() {
		$url = 'http://www.we-act.cn/Interface/ExportWeChat/getWeChatToken';	//请求获取accesstoken的url
		$params = array ();									//定义$params参数数组
		$params ['e_id'] = $this->e_id;						//商家编号
		$httpstr = http ( $url, $params );					//使用http方法（不知道是否为自定义，估计跟易班获取差不多）获取服务器返回数据$httpstr
		$jsonresult = json_decode ( $httpstr, true );		//使用json格式对数据解码，第二个参数为true时，将返回数组而非对象object
		return $jsonresult ['access_token'];				//返回token信息
	}
	
	/**
	 * 获取微信推送的数据，包含两个：1、验证真实性；2、接收一般消息。
	 * @return array 转换为数组后的数据
	 *
	 * 在这里声明下微信的数据格式：
	 * <xml>
	 *		<ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
	 *		<FromUserName><![CDATA[fromUser]]></FromUserName> 		发送方帐号（一个OpenID）
	 *		<CreateTime>1348831860</CreateTime>						消息创建时间 （整型）
	 *		<MsgType><![CDATA[text]]></MsgType>						消息类型
	 *		<Content><![CDATA[this is a test]]></Content>			消息内容
	 *		<MsgId>1234567890123456</MsgId>							消息id，64位整型，消息的主键，可以用来查重
	 * </xml>
	 *
	 *  2014/06/22 03:24:25 写入。
	 *
	 */
	public function request(){
		$errmsg = ""; // 全局错误码
		if (IS_GET){										//当前是否get请求 exit() 函数输出一条消息，并退出当前脚本。该函数是 die() 函数的别名。
			if ($this->auth()){
				exit ( $_GET['echostr'] );					//如果是get请求原样返回给微信服务器（开发者接入），一般微信发送数据，都是post。
			} else {
				$errmsg = "身份验证失败，拒绝响应非微信发来的数据，谢谢合作！";
				exit ( $errmsg );
			}
		} else {
			$xml = file_get_contents("php://input");	//php读取文件函数file_get_contents()，读入xml文件，file_get_contents("php://input")代表php文件输入流
			$xml = new SimpleXMLElement($xml);			//使用php的SimpleXMLElement()函数来解析xml
			$xml || exit;								//如果解析的xml是空文档则退出
			
			foreach ($xml as $key => $value) {			//使用php的foreach循环，将解码后的xml的键和值对应转成数组
				$this->data[$key] = strval($value);		//本类中定义了$data数组，将解码后的xml转成格式data['键值']=字符串值，strval()函数是php转字符串
			}
		}
		return $this->data;								//返回将xml转成的数组
	}
	
	/**
	 * 对数据进行签名认证，确保是微信发送的数据
	 * @param  string $token 微信开放平台设置的TOKEN
	 * @return boolean true-签名正确，false-签名错误
	 */
	private function auth(){
		/* Step1：获取数据 */
		$data = array($_GET['timestamp'], $_GET['nonce'], C ( 'WECHAT_TOKEN' ));	//thinkphp的C方法，从config配置中读取wechat_token（以后可以从表中读取商家设置的token值，不过这个方法是开发者介入，token是自己填写的），获取微信数据
		$sign = $_GET['signature'];													//获得微信签名
	
		/* Step2：对数据进行字典排序 */
		sort($data);										//使用php的sort()函数对数组进行排序
	
		/* Step3：生成签名 */
		$signature = sha1(implode($data));					//php自带的implode() 函数把数组元素组合为一个字符串，并使用sha1()方法进行加密
	
		return $signature === $sign;						//三个等号是比较两个变量的类型和值是否都相等，如果是，这个表达式return true，否则return false
	}
	
	/**
	 * 对接入后，每次发来的消息数据进行签名认证，确保是微信发送的数据（目前没有用，微信没有发送签名验证）
	 * @param  string $token 微信开放平台设置的TOKEN
	 * @return boolean true-签名正确，false-签名错误
	 */
	private function authmsg(){
		/* Step1：获取数据 */
		$data = array ( $_REQUEST ['timestamp'], $_REQUEST ['nonce'], C ( 'WECHAT_TOKEN' ) );	//thinkphp的C方法，从config配置中读取wechat_token（以后可以从表中读取商家设置的token值，不过这个方法是开发者介入，token是自己填写的），获取微信数据
		$sign = $_REQUEST ['signature'];													//获得微信签名
		
		/* Step2：对数据进行字典排序 */
		sort ( $data );										//使用php的sort()函数对数组进行排序
	
		/* Step3：生成签名 */
		$signature = sha1 ( implode ( $data ) );					//php自带的implode() 函数把数组元素组合为一个字符串，并使用sha1()方法进行加密
	
		return $signature === $sign;						//三个等号是比较两个变量的类型和值是否都相等，如果是，这个表达式return true，否则return false
	}
	
	/**
	 * 基础支持：获取微信服务器IP地址。
	 * @return array
	 */
	public function wechatServerIP() {
		$params = array(
				'access_token' => $this->getToken ()
		);
		$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip";
		$serverIP = http($url, $params);
		$serverinfo = json_decode($serverIP, true);
		return $serverinfo['ip_list'];
	}
	
	/**
	 * 被动响应微信发送的信息（被动回复）。
	 * @param string $to 接收用户名
	 * @param string $from 发送者用户名
	 * @param array  $content 回复信息，文本信息为string类型
	 * @param string $type 消息类型
	 * @param string $flag 是否新标刚接受到的信息
	 * @return string XML字符串
	 */
	public function response($content, $type = 'text'){
		/* 原来SDK的代码：定义本类的基础数据$data */
		$this->data = array(
				'ToUserName'   => $this->data ['FromUserName'],		//$weixin中有request的$data数据，取出FromUserName作为被回复者（用户）
				'FromUserName' => $this->data ['ToUserName'],		//$weixin中有request的$data数据，取出ToUserName作为回复者（公众号）
				'CreateTime'   => NOW_TIME,							//当前时间
				'MsgType'      => $type,							//取消息类型
		);
	
		/* 添加类型数据 */
		$this->$type($content);
	
		/* 添加状态 */
		//$this->data['FuncFlag'] = $flag;（这里我先省去了，形参中原来有个$flag = 0标识自己服务器的新旧消息）
		
		/* 将本类数组$data转换数据为XML */
		$xml = new SimpleXMLElement('<xml></xml>');
		$this->data2xml($xml, $this->data);
		exit($xml->asXML()); // 输出结果回应微信
	}
	
	/**
	 * 将数据用XML格式编码 dataToxml → data2xml
	 * @param  object $xml XML对象
	 * @param  mixed  $data 数据
	 * @param  string $item 数字索引时的节点名称
	 * @return string
	 */
	private function data2xml($xml, $data, $item = 'item') {
		foreach ($data as $key => $value) {
			/* 指定默认的数字key */
			is_numeric($key) && $key = $item;
	
			/* 添加子元素 */
			if(is_array($value) || is_object($value)){
				$child = $xml->addChild($key);
				$this->data2xml($child, $value, $item);
			} else {
				if(is_numeric($value)){
					$child = $xml->addChild($key, $value);
				} else {
					$child = $xml->addChild($key);
					$node  = dom_import_simplexml($child);
					$node->appendChild($node->ownerDocument->createCDATASection($value));
				}
			}
		}
	}
	
	/**
	 * 回复文本信息（被动回复）。
	 * @param  string $content 要回复的信息
	 */
	private function text($content){
		$this->data['Content'] = $content;
	}
	
	/**
	 * 回复音乐信息（被动回复）。
	 * @param  string $content 要回复的音乐
	 */
	private function music($music){
		$this->data['Music'] = $music;
	}
	
	/**
	 * 回复图文信息（被动回复）。
	 * @param  string $news 要回复的图文内容
	 */
	private function news($news){
		$articles = array();
		foreach ($news as $key => $value) {
			$articles[$key]['Title'] = $value['Title'];
			$articles[$key]['Description'] = $value['Description'];
			$articles[$key]['PicUrl'] = $value['PicUrl'];
			$articles[$key]['Url'] = $value['Url'];
			if($key >= 9) { break; } //最多只允许10调新闻
		}
		$this->data['ArticleCount'] = count($articles);
		$this->data['Articles'] = $articles;
	}
	
	/**
	 * 主动发送消息（客服接口），此函数为类外可以调用的客服消息发送函数。
	 * 该函数执行消息的打包，最终发送还需要调用本类内部的私有函数send。
	 * @param string $content 要发送的消息内容
	 * @param string $openid 要发送给的微信用户openid
	 * @param string $type 要发送消息的类型
	 * @param string $kf_account 可选字段：（微信6.0.2版本以上可用）客服账号昵称及自定义头像
	 * @return array 微信服务器返回的发送结果信息（json_decode后的数组格式）
	 */
	public function sendMsg($content, $openid = '', $type = 'text', $kf_account = '') {
		/* 基础数据 $send = array();已经简写 */
		$this->send ['touser'] = $openid;					//设置数组$send（当前函数中的变量$this->）的touser（发送给谁）
		$this->send ['msgtype'] = $type;					//设置数组$send的发送类型：文本信息类型
	
		/* 添加类型数据 */
		$sendtype = 'send' . $type; // 拼接不同的函数名称
		$this->$sendtype ( $content ); // 多态调用不同函数名
		
		/* 处理新添加的客服信息 */
		if (! empty ( $kf_account )) {
			$this->send ['customservice'] ['kf_account'] = $kf_account; // 如果客服账号不空，则添加客服账号
		}
		
		/* 发送 */
		$sendjson = jsencode ( $this->send );				// 压缩要发送的数据包，采用无转义字符方式压缩
		$restr = $this->send ( $sendjson );					// 调用本SDK中类函数send，真正发送给微信数据，并且获得微信服务器的返回值
		$sendresult = json_decode ( $restr, true ); 		// 将http返回的信息解码
		return $sendresult;
	}
	
	/**
	 * 主动发送（客服接口）给微信用户的消息。
	 * @param string $jsondata json数据
	 * @return string 微信返回信息
	 */
	private function send($jsondata = NULL) {
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;		//拼接发送信息API的URL请求地址
		$restr = http ( $url, $jsondata, 'POST', array ( "Content-type: text/html; charset=utf-8" ), true );	//调用Common公有的http()函数发送给微信服务器
		return $restr;																						//返回微信服务器返回结果，是个字符串格式
	}
	
	/**
	 * 主动发送（客服接口）文本消息。
	 * @param string $content 要发送的信息
	 */
	private function sendtext($content) {
		$this->send ['text'] = array (
				'content' => $content
		);
	}
	
	/**
	 * 主动发送（客服接口）图片消息。
	 * @param string $content 要发送的信息
	 */
	private function sendimage($content) {
		$this->send ['image'] = array (
				'media_id' => $content
		);
	}
	
	/**
	 * 主动发送（客服接口）视频消息。
	 * @param string $content 要发送的信息
	 */
	private function sendvideo($video){
		list (
				$video ['media_id'],
				$video ['title'],
				$video ['description']
		) = $video;
	
		$this->send ['video'] = $video;
	}
	
	/**
	 * 主动发送（客服接口）语音消息。
	 * @param string $content 要发送的信息
	 */
	private function sendvoice($content) {
		$this->send ['voice'] = array (
				'media_id' => $content
		);
	}
	
	/**
	 * 主动发送（客服接口）音乐消息。
	 * @param string $content 要发送的信息
	 */
	private function sendmusic($music) {
		$this->send ['music'] = $music;
	}
	
	/**
	 * 主动发送（客服接口）图文消息。
	 * 已经修复原来SDK的错误。
	 * 修复时间：2014/07/05 02:23:25.
	 * Author：赵臣升。
	 * @param string $news 要回复的图文内容
	 *
	 * $news消息格式：（注意都是小写）.
	 * $content[$i]['title'] = '标题';
	 * $content[$i]['description'] = '描述';
	 * $content[$i]['picurl'] = '封面地址';
	 * $content[$i]['url'] = '具体地址';
	 */
	private function sendnews($news){
		$articles = array ();
		$k = 0; // 图文条数
		foreach ($news as $key => $value) {
			if ($k > 9) { break; } // 最多只允许10条图文（也最多只装入10条图文）
			$articles [$k] ['title'] = $value ['title'];
			$articles [$k] ['description'] = $value ['description'];
			$articles [$k] ['url'] = $value ['url'];
			$articles [$k] ['picurl'] = $value ['picurl'];
			$k ++; // 赋值一条图文后，k值+1
		}
		$finalnews ['articles'] = $articles; // 图文放入articles索引中
		$this->send ['news'] = $finalnews; // 整个articles放入news索引中
	}
	
	/**
	 * 微信平台创建分组接口。
	 * @param array $groupinfo 创建分组的数组信息
	 * @return array $jsonresult 创建分组的结果的数组信息
	 */
	public function createGroup($groupinfo = NULL){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );					//定义https请求文件头
		$access_token = $this->getToken ();															//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $access_token;		//拼接发送信息API的URL请求地址
		$jsoninfo = jsencode($groupinfo);
		$jsonresult = http ( $url, $jsoninfo, 'POST', $header, true );								//调用Common公有的http()函数发送给微信服务器
		$createresult = json_decode($jsonresult, true);
		return $createresult;
	}
	
	/**
	 * 查询公众号所有用户分组。
	 * @return array $groupinfo 返回企业的分组信息。
	 */
	public function queryAllGroup(){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );			//定义https请求文件头
		$params ['access_token'] = $this->getToken ();										//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get";								//拼接发送信息API的URL请求地址
		$queryresult = http ( $url, $params );												//调用Common公有的http()函数发送给微信服务器
		$groupinfo = json_decode($queryresult, true);
		return $groupinfo;
	}
	
	/**
	 * 查询用户所在分组，传入array数组：openid和值。
	 * @param array $userinfo 用户的openid数组。
	 * @return array $groupinfo 包含组id的组信息。
	 */
	public function queryUserGroup($userinfo = NULL){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );								//定义https请求文件头
		$access_token = $this->getToken ();																		//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=" . $access_token;					//拼接发送信息API的URL请求地址
		$jsoninfo = jsencode($userinfo);
		$jsonresult = http ( $url, $jsoninfo, 'POST', $header, true );											//调用Common公有的http()函数发送给微信服务器
		$queryresult = json_decode($jsonresult, true);
		return $queryresult;
	}
	
	/**
	 * 拓展：批量查询用户所在分组，传入用户微信openid的一位数组。
	 * @param array $openidgroup 要批量查询的用户openid，一位数组
	 * @return array $batchqueryresult 批量查询用户分组结果
	 */
	public function batchQueryUserGroup($openidgroup = NULL){
		if(empty ( $openidgroup )) return false;
		$batchqueryresult = array(); // 批量查询用户分组的结果
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$access_token = $this->getToken ();
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=" . $access_token;
		$num = count ( $openidgroup );
		for($i = 0; $i < $num; $i ++) {
			$singlequery = array(
					'openid' => $openidgroup [$i]
			);
			$singlejsoninfo = jsencode( $singlequery );
			$singlejsonresult = http ( $url, $singlejsoninfo, 'POST', $header, true );						//调用Common公有的http()函数发送给微信服务器
			$batchqueryresult [$i] = json_decode( $singlejsonresult, true );
		}
		return $batchqueryresult;
	}
	
	/**
	 * 修改分组名。（特别注意，组id只增不减）
	 * @param array $newgroupinfo
	 * @return boolean|string 修改成功返回true，修改失败返回失败信息。
	 */
	public function alterGroupName($newgroupinfo = NULL){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token=" . $access_token;				//拼接发送信息API的URL请求地址
		$jsoninfo = jsencode( $newgroupinfo );
		$jsonresult = http ( $url, $jsoninfo, 'POST', $header, true );
		$alterresult = json_decode( $jsonresult, true );
		return $alterresult;
	}
	
	/**
	 * 移动用户分组函数。
	 * @param array $moveinfo 要移动的用户和组信息数组
	 * @return array $moveresult 移动用户到某个分组的结果（数组）
	 */
	public function moveUserToGroup($moveinfo = NULL) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . $access_token;		//拼接发送信息API的URL请求地址
		$jsoninfo = jsencode($moveinfo);
		$jsonresult = http ( $url, $jsoninfo, 'POST', $header, true );										//调用Common公有的http()函数发送给微信服务器
		$moveresult = json_decode($jsonresult, true);
		return $moveresult;
	}
	
	/**
	 * 批量移动用户到某分组的函数，从时间复杂度上对单次移动进行优化。
	 * 推荐最佳的批量移动数量为20~50条，不要太多，否则用户等待时间比较长。
	 * 形参数组格式：$movelist
	 * $movelist = array(
	 *     0 => array( 'openid' => $openid0, 'groupid' => groupid0 ),
	 *     1 => array( 'openid' => $openid1, 'groupid' => groupid1 ),
	 *     2 => array( 'openid' => $openid2, 'groupid' => groupid2 ),
	 *     ...
	 * );
	 * @param array $movelist 要移动的用户和组的信息二维数组
	 * @return array $batchmoveresult 批量移动用户到组结果的二维数组，就是单次调用结果的二维数组集合。
	 */
	public function batchMoveUserToGroup($movelist = NULL) {
		if(empty ( $movelist )) return false;
		$batchmoveresult = array(); // 批量移动用户到组结果
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . $access_token;		//拼接发送信息API的URL请求地址
		$num = count( $movelist );
		for($i = 0; $i < $num; $i ++) {
			$singlejsoninfo = jsencode( $movelist [$i] );
			$singlejsonresult = http ( $url, $singlejsoninfo, 'POST', $header, true );
			$batchmoveresult [$i] = json_decode( $singlejsonresult, true );
		}
		return $batchmoveresult;
	}
	
	/**
	 * 设置某个用户的备注名接口。
	 * @param array $remarkinfo	要修改的用户及备注名数组。
	 * @return array $modifyresult 修改备注名的结果。
	 */
	public function modifyUserRemark($remarkinfo) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=" . $access_token;	//拼接发送信息API的URL请求地址
		$jsoninfo = jsencode($remarkinfo);
		$jsonresult = http ( $url, $jsoninfo, 'POST', $header, true );									//调用Common公有的http()函数发送给微信服务器
		$modifyresult = json_decode($jsonresult, true);
		return $modifyresult;
	}
	
	/**
	 * 单个获取微信用户的基本资料，传入用户的openid（每个公众号对于同一个用户的openid都不同，防止恶意集粉。）。
	 * @param string $openid 要查询的用户微信openid
	 * @return array $userinfo 某微信用户的资料（如果关注公众号）
	 */
	public function getUserInfo($openid = '') {
		if(empty( $openid )) return false;
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info';	//定义API请求的URL地址
		$params = array ();										//定义发送数据的数组
		$params ['access_token'] = $this->getToken ();			//获取access_token
		$params ['openid'] = $openid;							//装入openid
		$httpstr = http ( $url, $params );						//调用Common公有函数中的http()函数请求微信服务器返回数据
		$userinfo = json_decode ( $httpstr, true );					//将返回的数据以utf-8格式解码
		return $userinfo;
	}
	
	/**
	 * 批量获取微信用户数组，从时间复杂度和空间复杂度上进行优化。
	 * @param array $openidlist 形参传入数组形式的openid
	 * @return array $userinfolist 微信用户信息列表
	 */
	public function batchGetUserInfo($openidlist = NULL) {
		if(empty( $openidlist )) return false;
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info';	//定义API请求的URL地址
		$params = array ();										//定义发送数据的数组
		$params ['access_token'] = $this->getToken ();			//获取access_token
		$userinfolist = array(); //用户信息列表
		for($i = 0; $i < count($openidlist); $i ++) {
			$params ['openid'] = $openidlist [$i];
			$userinfolist [$i] = json_decode( http ( $url, $params ), true );
		}
		return $userinfolist;
	}
	
	/**
	 * API 四、获取公众号关注列表，该函数直接可以被类对象调用，获取用户，看实例化的时候传参.
	 * @return array 当前公众号关注者列表
	 * Author：赵臣升
	 */
	public function getAllSubscriber() {
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get';	//定义API请求的URL地址（获取关注者列表的URL）
		$params = array ();										//定义发送数据的数组
		$params ['access_token'] = $this->getToken ();			//获取access_token
		$params ['next_openid'] = '';							//下一个用户的openid，如果没有下一个，则为空字符串
		$httpstr = http ( $url, $params );						//调用Common公有函数中的http()函数请求微信服务器返回数据
		$harr = json_decode ( $httpstr, true );					//将返回的数据以utf-8格式解码
		while($harr['next_openid']!=''){
			$params ['next_openid'] = $harr['next_openid'];
			$httpstrnext = http ( $url, $params );
			$harrnext = json_decode ( $httpstrnext, true );
			//如果下一个拉取接口得到的data中的openid数组不空，才做出用户openid数组合并
			if($harrnext['data']['openid']){
				$harr['data']['openid'] = array_merge($harr['data']['openid'], $harrnext['data']['openid']);
			}
			$harr['next_openid'] = $harrnext['next_openid'];
		}
		return $harr;											//返回解码后的数据（应该是数组形式，具体格式参见微信API）
	}
	
	/**
	 * 产生二维码的接口。
	 * @param array $scenedata 要编码的二维码信息
	 * @return array $codeinfo 微信服务器返回的请求二维码结果
	 */
	public function generateQRcode($scenedata = NULL){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->getToken ();	//定义API请求的URL地址（获取access_token的URL）
		$jsoninfo = jsencode ( $scenedata ); // 将数据打包
		$mQRcode = http ( $url, $jsoninfo, 'POST', $header, true );	//调用Common共有函数http()进行请求
		$codeinfo = json_decode ( $mQRcode, true ); // 以utf-8的形式解码
		return $codeinfo;
	}
	
	/**
	 * 下载二维码接口。
	 * @param string $ticket_id 要下载的二维码ticket_id
	 * @param string $savefinal 最终保存下载二维码图片的路径
	 * @return array $result 返回是否下载成功的信息数组
	 */
	public function downloadQR($ticket_id = '', $savefinalpath = ''){
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';		//定义API请求查询菜单的URL地址
		$params = array ();											//定义发送数据的数组
		$params ['ticket'] = $ticket_id;							//获取当前要下载的media_id
		$fileInfo = downloadWeixinFile ( $url, $params );			//调用Common公有函数中的downloadWeixinFile()函数请求微信服务器返回数据
		$localFile = fopen ( $savefinalpath, 'w' );					//打开文件流，写文件方式
		$result = array ( 
				'errCode' => 0, 
				'errMsg' => "下载图片失败，请稍后再试！" 
		); // 默认没下载成功
		// 尝试下载
		if ($localFile !== false){
			if (fwrite($localFile, $fileInfo['body']) !== false){
				fclose($localFile); // 关闭文件读写流
				$result = array ( 
						'errCode' => 0, 
						'errMsg' => "ok", 
						'code_path' => $savefinalpath 
				); // 将成功写入的信息返回
			}
		}
		return $result;
	}
	
	/**
	 * 长链接转短链接接口。
	 * @param array $urlinfo	链接信息数组。
	 * @return array	经过json解码后的数组。
	 */
	public function getShortURL($urlinfo) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );							//定义https请求文件头
		$jsoninfo = jsencode($urlinfo);
		$access_token = $this->getToken ();																	//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token=" . $access_token;					//拼接发送信息API的URL请求地址
		$jsonResponse = http ( $url, $jsoninfo, 'POST', $header, true );									//调用Common公有的http()函数发送给微信服务器
		$shortinfo = json_decode($jsonResponse, true);
		return $shortinfo;
	}
	
	/**
	 * API 六、设置自定义菜单
	 * @param  string $data 菜单的str
	 * @return string  返回的结果；
	 */
	public function setMenu($data = NULL){
		$access_token = $this->getToken();																//获取微信access_token
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;			//定义API的URL
		$menustr = http($url, $data, 'POST', array("Content-type: text/html; charset=utf-8"), true);	//调用Common共有函数http()进行请求
		$setinfo = json_decode($menustr, true);
		return $setinfo;																				//返回定制菜单结果
	}
	
	/**
	 * API 七、查询商家自定义菜单。
	 * @return array $menu 数组格式的菜单
	 */
	public function queryMenu(){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/get';		//定义API请求查询菜单的URL地址
		$params = array ();											//定义发送数据的数组
		$params ['access_token'] = $this->getToken();				//获取当前商家的access_token
		$httpstr = http ( $url, $params );							//调用Common公有函数中的http()函数请求微信服务器返回数据
		$menu = json_decode ( $httpstr, true );						//将返回的数据以utf-8格式解码
		return $menu;
	}
	
	/**
	 * API 八、删除商家自定义菜单。
	 * @return true	删除菜单成功;false 删除菜单失败。
	 */
	public function deleteMenu(){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/delete';		//定义API请求删除菜单的URL地址
		$params = array ();											//定义发送数据的数组
		$params ['access_token'] = $this->getToken();				//获取当前商家的access_token
		$httpstr = http ( $url, $params );							//调用Common公有函数中的http()函数请求微信服务器返回数据
		$result = json_decode ( $httpstr, true );						//将返回的数据以utf-8格式解码
		if($result['errmsg']=='ok'){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * API 一、上传多媒体接口，把数据发送到微信服务器。
	 * 特别注意：$mediapath请传入多媒体文件的绝对路径。
	 * @param string $mediapath	多媒体在window下的绝对路径：@$mediapath
	 * @param string $type 多媒体类型，有image,voice,video,thumb4种
	 * @return array $httpinfo 上传多媒体得到微信服务器端的返回
	 */
	public function uploadMedia($mediapath = '', $type = 'image'){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );//定义https请求文件头
		$access_token = $this->getToken ();										//先获取access_token令牌
		$filedata ['media'] = '@'.$mediapath;
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $access_token . "&type=" . $type;	//拼接发送信息API的URL请求地址
		$httpinfo = http ( $url, $filedata, 'POST', $header, true );			//调用Common公有的http()函数发送给微信服务器
		return json_decode ( $httpinfo, true );									//将返回的数据以utf-8格式解码
	}
	
	/**
	 * 上传图文API接口。
	 * @param array $articleinfo 要上传的图文信息
	 * @return array $uploadnewsresult 上传图文得到的返回结果
	 */
	public function uploadNews($articleinfo = NULL){
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );		//定义https请求文件头
		$access_token = $this->getToken ();												//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=" . $access_token;	//拼接发送信息API的URL请求地址
		$httpinfo = http ( $url, jsencode( $articleinfo ), 'POST', $header, true );		//调用Common公有的http()函数发送给微信服务器
		return json_decode ( $httpinfo, true );											//将返回的数据以utf-8格式解码
	}
	
	/**
	 * 群发图文接口。
	 * @param array $groupsendinfo 分组群发信息
	 * @return array $groupsendresult 群发结果
	 */
	public function publicGroupSendNews($groupsendinfo) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );		//定义https请求文件头
		$access_token = $this->getToken ();												//先获取access_token令牌
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . $access_token;	//拼接发送信息API的URL请求地址
		$httpinfo = http ( $url, jsencode( $groupsendinfo ), 'POST', $header, true );	//调用Common公有的http()函数发送给微信服务器
		return json_decode ( $httpinfo, true );											//将返回的数据以utf-8格式解码
	}
	
	/**
	 * 微信下载多媒体接口API，依据media_id从微信服务器下载多媒体信息。
	 * 终于调通，调通时间：2015/04/12 22:41:25.
	 * 该接口不同于其他微信接口，还有http请求头。
	 * @param string $media_id 形参传入多媒体的编号
	 * @param string $savefinalpath 多媒体文件最终保存路径
	 * @return array $downloadresult 下载多媒体文件是否成功
	 */
	public function downloadMedia($media_id = '', $savefinalpath = '') {
		// 定义全局返回码
		$downloadresult = array (
				'errCode' => 10001,
				'errMsg' => "下载多媒体文件失败，请稍后再试！"
		); // 默认没下载成功
		
		$url = 'http://file.api.weixin.qq.com/cgi-bin/media/get';	//定义API请求查询菜单的URL地址
		$params = array ( 
				'access_token' => $this->getToken(),				//获取当前商家的access_token
				'media_id' => $media_id 							//获取当前要下载的media_id
		);
		// 下载多媒体
		$fileInfo = downloadWeixinFile ( $url, $params );			//调用Common公有函数中的downloadWeixinFile()函数请求微信服务器返回数据
		$httpresult = json_decode ( $fileInfo ['body'], true ); 	// utf-8解码数据
		
		// // 微信端文件是否成功返回，如果文件出错
		if (isset ( $httpresult ['errcode'] ) && $httpresult ['errcode'] != 0 ) {
			// 如果有错误信息
			$downloadresult ['errCode'] = $httpresult ['errcode'];  // 错误码给他
			return $downloadresult; // 返回错误信息
		}
		
		// 如果不出错，尝试下载多媒体文件，并写入本地磁盘文件
		$localFile = fopen ( $savefinalpath, 'w' );					//打开文件流，写文件方式
		if ($localFile !== false) {
			// 如果创建文件成功，则写入文件
			if (fwrite ( $localFile, $fileInfo ['body'] ) !== false) {
				fclose ( $localFile ); // 关闭文件读写流
				// 将成功写入的信息返回
				$downloadresult ['errCode'] = 0;
				$downloadresult ['errMsg'] = "ok";
				$downloadresult ['mediapath'] = $savefinalpath;
			}
		}
		return $downloadresult;
	}
	
	/**
	 * API 五、订阅、未订阅公众号的用户授权登录后获取用户信息的函数。
	 * Author：赵臣升。
	 * CreateTime：2014/07/05 20:13:25.
	 * @param string $code 形参传入用户授权后获得的code
	 * @param string $state 形参传入用户授权后获得的state（商家或者微动定义的）
	 * @return array $finaluser 返回从微信请求的数据
	 */
	public function authorize($code='', $state=''){
		//用户同意授权才去换取access_token
	
		//Step1：利用code换取access_token
		header ( "Content-type: text/html; charset=utf-8" );		//定义https请求文件头
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token';	//定义API请求的URL地址（获取access_token的URL）
		$params = array ();											//定义发送数据的数组
		$params ['appid'] = $this->APPID;							//获取当前商家的APPID
		$params ['secret'] = $this->APPSecret;						//获取当前商家的APPSecret
		$params ['code'] = $code;									//下一个用户的openid，如果没有下一个，则为空字符串
		$params ['grant_type'] = 'authorization_code';				//下一个用户的openid，如果没有下一个，则为空字符串
		$httpstr = http ( $url, $params );							//调用Common公有函数中的http()函数请求微信服务器返回数据
		$finaldata = json_decode ( $httpstr, true );				//将返回的数据以utf-8格式解码
		//$finaldata = array( 'access_token' => value1, 'expires_in' => value2, 'refresh_token' => value3, 'openid' => value4, 'scope' => value5 );
	
		//Step2：利用access_token换取用户的信息
		header ( "Content-type: text/html; charset=utf-8" );		//定义https请求文件头
		$url = 'https://api.weixin.qq.com/sns/userinfo';			//定义API请求的URL地址（获取用户数据的URL）
		$parameters = array ();										//定义发送数据的数组
		$parameters ['access_token'] = $finaldata['access_token'];	//获取当前商家的APPID
		$parameters ['openid'] = $finaldata['openid'];				//获取当前商家的APPSecret
		$parameters ['lang'] = 'zh_CN';								//下一个用户的openid，如果没有下一个，则为空字符串
		$httpsstr = http ( $url, $parameters );						//调用Common公有函数中的http()函数请求微信服务器返回数据
		$finaluser = json_decode ( $httpsstr, true );				//将返回的数据以utf-8格式解码
		//$finaluser也是数组
		return $finaluser;
	}
	
	/**
	 * API 五、订阅、未订阅公众号的用户授权登录后获取用户信息的函数。
	 * Author：赵臣升。
	 * CreateTime：2014/07/05 20:13:25.
	 * @param string $code	形参传入用户授权后获得的code
	 * @param string $state	形参传入用户授权后获得的state（商家或者微动定义的）
	 * @return array $finaluser	返回从微信请求的数据
	 */
	public function baseAuthorize($code='', $state=''){
		//Step1：利用code换取access_token
		header ( "Content-type: text/html; charset=utf-8" );		//定义https请求文件头
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token';	//定义API请求的URL地址（获取access_token的URL）
		$params = array ();											//定义发送数据的数组
		$params ['appid'] = $this->APPID;							//获取当前商家的APPID
		$params ['secret'] = $this->APPSecret;						//获取当前商家的APPSecret
		$params ['code'] = $code;									//下一个用户的openid，如果没有下一个，则为空字符串
		$params ['grant_type'] = 'authorization_code';				//下一个用户的openid，如果没有下一个，则为空字符串
		$httpstr = http ( $url, $params );							//调用Common公有函数中的http()函数请求微信服务器返回数据
		$finaldata = json_decode ( $httpstr, true );				//将返回的数据以utf-8格式解码
		//$finaldata = array( 'access_token' => value1, 'expires_in' => value2, 'refresh_token' => value3, 'openid' => value4, 'scope' => value5 );
	
		return $finaldata;
	}
	
	/**
	 * 获取公众号在线客服列表。
	 * @return array $kflist 客服信息数组，参见微信API文档。
	 */
	public function getOnlineServiceList() {
		$url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist';
		$params = array (
				'access_token' => $this->getToken()
		);
		$ajaxresult = http ( $url, $params );
		return json_decode ( $ajaxresult, true ); // 以utf-8格式解码返回
	}
	
	/**
	 * 获取公众号在线客服的状态（服务了多少个客户）。
	 * @return array kefuonlinelist 在线客服状态等信息
	 */
	public function getOnlineServiceStatus() {
		$url = 'https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist';
		$params = array (
				'access_token' => $this->getToken()
		);
		$ajaxresult = http ( $url, $params );
		return json_decode ( $ajaxresult, true ); // 以utf-8格式解码返回
	}
	
	/**
	 * 为公众号添加客服账号接口，POST类型。
	 * @param array $accountinfo 客服账号信息数组
	 * @property string kf_account 客服人员登录的账号
	 * @property string nickname 客服的昵称信息
	 * @property string password 客服人员登录的密码，必须是经过md5函数加密后的密码，不能是明文
	 */
	public function addOnlineServiceAccount($accountinfo = NULL) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' ); //定义https请求文件头
		$url = 'https://api.weixin.qq.com/customservice/kfaccount/add?access_token=' . $this->getToken();
		$ajaxinfo = jsencode ( $accountinfo );
		$ajaxresult = http ( $url, $ajaxinfo, 'POST', $header, true );
		return json_decode ( $ajaxresult, true );
	}
	
	/**
	 * 设置客服信息,账号不能更改，昵称，密码可以改，POST类型接口。
	 * @param array $modifyinfo 要修改的账号信息
	 * @property string kf_account 要修改的账号
	 * @property string nickname 要修改的客服昵称
	 * @property string password 要修改的客服密码
	 */
	public function modifyOnlineServiceInfo($modifyinfo = NULL) {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' ); //定义https请求文件头
		$url = 'https://api.weixin.qq.com/customservice/kfaccount/update?access_token=' . $this->getToken();
		$ajaxinfo = jsencode ( $modifyinfo );
		$ajaxresult = http ( $url, $ajaxinfo, 'POST', $header, true );
		return json_decode ( $ajaxresult, true );
	}
	
	/**
	 * 上传客服头像，该接口为media类型POST接口。
	 * @param string $mediapath 客服的头像jpg图片绝对路径。
	 */
	public function uploadOnlineServiceAvatar($account = '', $mediapath = '') {
		$header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' );//定义https请求文件头
		$access_token = $this->getToken ();										//先获取access_token令牌
		$filedata ['media'] = '@' . $mediapath;
		$url = "http://api.weixin.qq.com/customservice/kfacount/uploadheadimg?access_token=" . $access_token . "&kf_account=" . $account;	//拼接发送信息API的URL请求地址
		$httpinfo = http ( $url, $filedata, 'POST', $header, true );			//调用Common公有的http()函数发送给微信服务器
		return json_decode ( $httpinfo, true );									//将返回的数据以utf-8格式解码
	}
	
	/**
	 * 公众号删除客服账号接口，特别注意该接口是GET。
	 * @param string $account 要删除的账号
	 */
	public function deleteOnlineServiceAccount($account = '') {
		$url = 'https://api.weixin.qq.com/customservice/kfaccount/del';
		$params = array (
				'access_token' => $this->getToken (),
				'kf_account' => $account
		);
		$ajaxresult = http ( $url, $params );
		return json_decode ( $ajaxresult, true ); // 以utf-8格式解码返回
	}
	
}
?>