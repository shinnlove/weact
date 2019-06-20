<?php
/**
 * 测试网页聊天类，封装网页聊天SDK。
 * @author 赵臣升。
 * CreateTime:2015/07/08 14:50:36.
 */
class TestWebChatAction extends Action {

	/**
	 * =========PART1：固定常量区域===========
	 */
	
	var $WEB_CONST_TOKEN = "zhaodong"; // 临时固定token赵董
	var $WEB_CONST_APPID = "webappid"; // 临时固定网页端appid
	var $WEB_CONST_APPSECRET = "webappsecret"; // 临时固定网页端appsecret
	
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
	 * 网页聊天接口授权：access_token生成规则。
	 */
	public function generateToken() {
		return strtoupper ( md5 ( $this->WEB_CONST_TOKEN ) . md5 ( $this->WEB_CONST_APPID ) . md5 ( $this->WEB_CONST_APPSECRET ) ); // 网页端access_token生成规则
	}
	
	/**
	 * =========PART3：网页聊天窗信息接口部分===========
	 */
	
	/**
	 * 接口：请求查询导购信息接口，该接口为GET类型接口。
	 */
	public function getGuideInfo() {
		$url = "http://localhost/Interface/WebChatGuide/guideInfo";
		$params = array (
				'access_token' => $this->generateToken (), // 网页聊天窗的接口token
				'gid' => 'g10001' // 要请求的导购信息
		);
		$httpresult = http ( $url, $params ); // 请求数据
		$result = json_decode ( $httpresult, true ); // utf-8解码
		p ( $result ); die ();
	}
	
	/**
	 * =========PART4：网页聊天窗聊天接口部分===========
	 */
	
	/**
	 * 测试网页聊天窗的通信接口。
	 */
	public function sendMsg() {
		$url = "http://localhost/weact/Interface/WebChatMsg/receiveWebMsg?access_token=" . $this->generateToken ();
		$params = array (
				'webmsg_id' => 'xiaobaobaoxiaobaobao', // 模拟一个网页端消息的主键（用来排重）
				'eid' => '201406261550250006', // 商家编号
				'from_customer' => '201503021503437865', // 消息来自的顾客编号
				'to_guide' => 'g10001', // 消息送达的导购编号
				'msg_type' => 0, // 消息类型包括文本、图片、语音，默认0代表文本消息，1代表图片，2代表声音，3代表视频（短视频），4代表音乐，5代表商品图文推送
				'time' => time (), // 消息时间
				'content' => '在网页聊天窗上我想要一辆阿斯顿马丁！' // 消息内容
		);
		// 普通测试
		$jsoninfo = json_encode ( $params );
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$sendresult = json_decode ( $httpresult, true ); // decode utf-8
		p($sendresult); die ();
	}
	
	/**
	 * 测试查询某顾客新消息的通信接口。
	 */
	public function queryNewMsg() {
		$newmsglist = $this->queryNewMessage (); // 查询新消息
		p ( $newmsglist ); die ();
	}
	
	/**
	 * 测试网页端查询新消息后给出消息接收的确认。
	 */
	public function webMsgConfirm() {
		$newmsglist = $this->queryNewMessage (); // 查询新消息
		$newmsginfo = $newmsglist ['data'] ['newmsglist']; // 提取新消息二维数组
		$newmsgcount = count ( $newmsginfo ); // 得到新消息数目
		if ($newmsgcount) {
			// 如果有新消息
			$newmsgidlist = array (); // 新消息数组
			for($i = 0; $i < $newmsgcount; $i ++) {
				$newmsgidlist [$i] ['msgid'] = $newmsginfo [$i] ['msgid']; // 新消息确认数组
			}
			$url = "http://localhost/weact/Interface/WebChatMsg/receiveMsgConfirm?access_token=" . $this->generateToken (); // 新消息确认的地址
			$params = array (
					'confirmlist' => $newmsgidlist // 确认接收到的新消息列表数组
			);
			$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
			$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
			$confirmresult = json_decode ( $httpresult, true ); // decode utf-8
		}
		p($newmsglist);die;
	}
	
	/**
	 * 查询顾客和导购间某条消息的位置。
	 */
	public function queryMsgPosition() {
		$url = "http://localhost/weact/Interface/WebChatMsg/queryMsgPos?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001', // 消息所属导购编号
				'cid' => '201503021503437865', // 模拟某顾客编号
				'msg_id' => 'xiaobaobaoxiaobaobao', // 模拟某顾客和该导购聊天的某消息编号
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p($result);die;
	}
	
	/**
	 * 测试网页端查询某顾客与某导购的历史消息接口。
	 */
	public function queryHistoryMsg() {
		$url = "http://localhost/weact/Interface/WebChatMsg/queryHistoryMsg?access_token=" . $this->generateToken ();
		$params = array (
				'cid' => '201503021503437865', // 模拟某顾客编号
				'gid' => 'g10001', // 模拟与该顾客对话的某导购编号
				'next_start' => 10, // 下一条消息是要读取第几条，如果没有该参数，默认读取第一条消息（历史消息数组下标从0开始）
				'perpage' => 50, // 每页所查询的消息数目，最小10条，最大100条，如果不给出参数，默认一页50条消息
				'reverse' => 0, // 不传这个参数，或者传递这个参数为0，代表正序读取（时间顺序）；如果值为1，代表倒序，最后一条消息在最前
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		p($result);die;
	}
	
	/**
	 * =========PART5：本类共用私有函数部分===========
	 */
	
	/**
	 * 私有函数，模拟查询某导购的新消息。
	 * @return array $result 查询到的新消息
	 */
	private function queryNewMessage() {
		$url = "http://localhost/weact/Interface/WebChatMsg/queryNewMsg?access_token=" . $this->generateToken ();
		$params = array (
				'gid' => 'g10001' // 模拟某顾客的编号
		);
		$jsoninfo = json_encode ( $params ); // POST接口需要将数据打包成json再POST
		$httpresult = http ( $url, $jsoninfo, 'POST', $this->header, true ); // 发送数据
		$result = json_decode ( $httpresult, true ); // decode utf-8
		return $result;
	}
	
	/**
	 * 网页聊天窗例子。
	 */
	public function webChatDemo() {
		$this->display ();
	}
	
}
?>