<?php
/**
 * 本控制器用来与微信提供的API接口对接。
 * @author 赵臣升。
 * 2014/06/22
 * */
class WeChatAPIAction extends Action {
	
	/**
	 * 微动提供开发者服务接收微信post消息的URL处理地址。
	 * 考虑到企业信息在接口中的频繁使用，第二版开始使用enterprise和enterpriseinfo两表联查视图（作者胡睿，t_einfo_manage），
	 * 此视图有is_del=0的区别，所以在代码中is_del就不加了，对于这一点如果有变更一定要注意！
	 */
	public function index() {
		
		import ( 'Class.API.WeChatAPI.WeactWechat', APP_PATH, '.php' ); 		// 载入微动微信SDK
		
		$speedtime1 = microtime_double (); 					// 接口测速代码（记录起点）
		
		// 接收传参查询企业信息，不在服务区直接退出
		$emap = array (
				'e_id' => $_REQUEST ['e_id'],
				//'is_del' => 0 // t_einfo_manage视图中有判断且无is_del字段，此处暂且去掉
		);
		$infofield = "e_id, e_name, brand, wechat_account, wechat_name, original_id, developer_token, appid, appsecret, msg_encode, aeskey"; // 定义部分需要的字段，可以自行添加，新增消息加密体系
		$einfo = M ( 'einfo_manage' )->where ( $emap )->field ( $infofield )->find (); 	// 找寻企业信息
		if (! $einfo) exit ( '' ); 							// 如果不是微动客户，不享有自动接口，回复空串通知微信已收到。
		
		$speedtime2 = microtime_double (); 					// 接口测速代码（记录查询企业信息）
		
		$weixin = new WeactWechat ( $einfo ); 				// 导入微信SDK，初始化企业、APPID和APPSecret，新建本类中的微信全局对象
		$weixin->valid (); 									// 校验开发者URL（如果有）
		
		// 通过验证才记录日志
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"]; 		// 获取微信post的数据
		if (empty ( $postStr )) exit ( '' ); 				// 消息为空（微信误发或者三方恶意POST），直接退出
		$msgdata = $weixin->request ( $postStr ); 			// 解析微信消息
		
		$speedtime3 = microtime_double (); 					// 接口测速代码（解析微信数据或者解密微信数据时间，I/O操作时间不计）
		
		/* Step3：获取回复信息先收起来，这里reply()返回的数组格式是：内容 ← 类型。如：array('您好', 'text') */
		list ( $content, $type ) = $this->responsecheck ( $einfo, $msgdata ); // list()是一种语言结构，跟数组类似。效果等同于收拾打包起来，需要的时候再拆开
		
		$speedtime4 = microtime_double (); 					// 接口测速代码（在微动平台查找回复内容）
		
		$dmid = array (); // 全局排重变量$dmid数组
		if ($msgdata ['MsgType'] == 'event') {
			$dmid = array (
					'from_user_name' => $msgdata ['FromUserName'],
					'create_time' => $msgdata ['CreateTime'] 
			);
		} else {
			$dmid = array (
					'msg_id' => $msgdata ['MsgId'] 
			);
		}
		$wcmiresult = M ( 'wechatmsginfo' )->where ( $dmid )->find ();
		if (! $wcmiresult) {
			// Step4-1：调用wechatDataRecord()函数将接收到的用户信息存入微动数据库
			$this->wechatDataRecord ( $msgdata ); // 将微信服务器发来的不同消息存入不同数据表中
		} 
		// Step4-2：响应当前请求给微信服务器
		$response = $weixin->response ( $content, $type ); // 响应list()中的$content, $type，exit到页面上
		echo $response; 
		
		// 记录接口测试信息
		$speedlist = array (
				0 => $speedtime1, 
				1 => $speedtime2, 
				2 => $speedtime3, 
				3 => $speedtime4,
		);
		$this->traceHttp ( $postStr, $response, $speedlist ); // 记录日志信息
	}
	
	/**
	 * 微动微信开放平台一键授权服务POST消息的URL处理地址。
	 */
	public function auth() {
		
		import ( 'Class.API.WeChatAPI.WeactWechatOpen', APP_PATH, '.php' ); 	// 载入微信开放平台SDK
		
		// 接收微信开放平台消息参数
		$appid = $_REQUEST ['appid'];
		$signature = $_GET ['signature'];
		$timestamp = $_GET ['timestamp'];
		$nonce = $_GET ['nonce'];
		$encrypt_type = $_GET ['encrypt_type'];
		$msg_signature = $_GET ['msg_signature'];
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"]; 	// 获取微信post的数据
		if (empty ( $postStr )) exit ( '' ); 			// 消息为空（微信误发或者三方恶意POST），直接退出
		
		$speedtime1 = microtime_double (); 				// 接口测速代码（记录起点）
		
		// 根据传参查询企业信息
		$einfo = array ();
		if ($appid == "wx570bc396a51b8ff8") {
			// 全网发布测试公众号wx570bc396a51b8ff8
			$einfo ['e_id'] = "-1"; // 不需要
			$einfo ['appid'] = $appid;
			$einfo ['appsecret'] = "-1"; // 不需要
			$einfo ['is_auth'] = 1; // 特别注意：打开一键授权
		} else {
			// 非全网发布使用
			$emap = array (
					'appid' => $appid,
					//'is_del' => 0 // t_einfo_manage视图中有判断且无is_del字段，此处暂且去掉
			);
			$infofield = "e_id, e_name, brand, wechat_account, wechat_name, original_id, developer_token, appid, appsecret, msg_encode, aeskey, is_auth"; // 定义部分需要的字段，可以自行添加，新增消息加密体系
			$einfo = M ( 'einfo_manage' )->where ( $emap )->field ( $infofield )->find (); // 找寻企业信息
		}
		if (! $einfo) exit ( '' ); // 如果不是微动客户，不享有自动接口，回复空串通知微信已收到。 
		
		$speedtime2 = microtime_double (); 				// 接口测速代码（记录查询企业信息）
		
		$weixin = new WeactWechatOpen ( $einfo ); 		// 新建本类中的微信全局对象
		$decodemsg = $weixin->openMsgDecode ( $postStr, $signature, $timestamp, $nonce, $msg_signature ); // 解密消息
		$msgdata = $weixin->extractXml ( $decodemsg ); 	// XML转二维数组
		$weixin->requestOpen ( $msgdata ); 				// 非常重要，一定要有，不然$this->data报错
		
		$speedtime3 = microtime_double (); 				// 接口测速代码（解析微信数据或者解密微信数据时间，I/O操作时间不计）
		
		$content = array ();
		$type = "text";
		if ($appid == "wx570bc396a51b8ff8") {
			// 全网发布情况
			if ($msgdata ['MsgType'] == "event") {
				$content = $msgdata ['Event'] . "from_callback"; // 拼接回复内容事件名+from_callback
				$type = "text"; // 修改回复类型为文本
			} else if ($msgdata ['MsgType'] == "text") {
				if ($msgdata ['Content'] == "TESTCOMPONENT_MSG_TYPE_TEXT") {
					$content = $msgdata ['Content'] . "_callback"; // 固定回复TESTCOMPONENT_MSG_TYPE_TEXT_callback
					$type = "text"; // 修改回复类型为文本
				} else {
					$query_auth_code = str_replace ( "QUERY_AUTH_CODE:", "", $msgdata ['Content'] ); // 抽取$query_auth_code
					$content = $query_auth_code . "_from_api"; // 固定回复格式
					$weixin->sendOpenMsg ( $content, $msgdata ['FromUserName'], $appid, $query_auth_code ); // 授权调用API发送客服消息
					exit ( "" ); // 回复微信需要的空串
				}
			}
		} else {
			// 正常状态
			/* Step3：获取回复信息先收起来，这里reply()返回的数组格式是：内容 ← 类型。如：array('您好', 'text') */
			list ( $content, $type ) = $this->responsecheck ( $einfo, $msgdata ); // list()是一种语言结构，跟数组类似。效果等同于收拾打包起来，需要的时候再拆开
		}
		
		$speedtime4 = microtime_double (); 				// 接口测速代码（在微动平台查找回复内容）
		
		$dmid = array (); // 全局排重变量$dmid数组
		if ($msgdata ['MsgType'] == 'event') {
			$dmid = array (
					'from_user_name' => $msgdata ['FromUserName'],
					'create_time' => $msgdata ['CreateTime'] 
			);
		} else {
			$dmid = array (
					'msg_id' => $msgdata ['MsgId'] 
			);
		}
		$wcmiresult = M ( 'wechatmsginfo' )->where ( $dmid )->find ();
		if (! $wcmiresult) {
			// Step4-1：调用wechatDataRecord()函数将接收到的用户信息存入微动数据库
			$this->wechatDataRecord ( $msgdata ); 		// 将微信服务器发来的不同消息存入不同数据表中
		} 
		
		// Step4-2：响应当前请求给微信服务器
		$response = $weixin->response ( $content, $type ); // 响应list()中的$content, $type，exit到页面上
		echo $response;
		
		// 记录接口测试信息
		$speedlist = array (
				0 => $speedtime1,
				1 => $speedtime2,
				2 => $speedtime3,
				3 => $speedtime4,
		);
		$this->traceHttp ( $decodemsg, $response, $speedlist, $appid, $type, $content ); // 记录日志信息
	}
	
	/*-----------------------------以下是自动回复的多态函数区域--------------------------------*/
	
	/**
	 * 记录微信接口信息及测速时间。
	 * @param xml $postStr XML格式的微信消息
	 * @param array $speedlist 测速时间组
	 * @return boolean true 接口信息记录完成
	 */
	private function traceHttp($postStr = NULL, $response = NULL, $speedlist = NULL, $openappid = '', $opentype = '', $opendecodemsg = '') {
		// Step1：创建文件
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/";
		$filename = "";
		if (empty ( $openappid )) {
			$filepath .= "wechatmsg/"; 														// 普通消息文件夹
			$filename = "recordmsg" . date ( 'Y-m-d' ) . ".log"; 							// 消息文件名按天存放
		} else {
			$filepath .= "wechatopenmsg/"; 													// 开放平台文件夹
			$filename = $openappid . "recordopenmsg" . date ( 'Y-m-d' ) . ".log"; 			// 消息文件名按天存放
		}
		if (! file_exists ( $filepath ) ) mkdirs ( $filepath ); 							// 如果没有存在文件夹，直接创建文件夹
		// Step2：获取请求身份
		$http_client_ip = $_SERVER ['REMOTE_ADDR']; 										// 客户端IP地址
		$query_string = $_SERVER ['QUERY_STRING']; 											// 获取请求参数
		$ip_judge = strpos ( $http_client_ip, "101.226" ) ? "WeiXin": "Unknown IP"; 		// 判断IP地址
		// Step3：组装请求信息
		$query_info = "---------------http request info---------------\n";
		$query_info .= "REMOTE_ADDR_IP : " . $http_client_ip . "; \n"; 						// 记录客户端的IP
		$query_info .= "FROM : " . $ip_judge . "; \n"; 										// 记录客户端身份
		$query_info .= "QUERY_STRING : " . $query_string . "; \n"; 							// 记录请求URL参数
		$msg_title = "---------------wechat msg package info---------------\n";
		$response_title = "---------------weact response msg info---------------\n";
		// Step4：计算测速时间
		$timespan1 = $speedlist [1] - $speedlist [0];
		$timespan2 = $speedlist [2] - $speedlist [1];
		$timespan3 = $speedlist [3] - $speedlist [2];
		// Step5：拼接接口测速信息
		$speedtitle = "---------------speed test info---------------\n";
		$speedtitle .= "INTERFACE_SPEED : \n";
		$speedinfo1 = "Step1: query enterprise info take: " . $timespan1. " s; \n"; 	// 记录查询企业信息时间
		$speedinfo2 = "Step2: parse wechatinfo msg take: " . $timespan2. " s; \n"; 		// 记录解析微信数据时间
		$speedinfo3 = "Step3: find weact response take: " . $timespan3. " s; \n"; 		// 记录微动平台自动回复时间
		$interface_speed = $speedtitle . $speedinfo1 . $speedinfo2 . $speedinfo3; 		// 接口测速信息叠加
		// Step6：拼接最后日志记录信息
		$finalwriteinfo = $query_info . $msg_title . $postStr . "\n" . $response_title . $response . "\n"; 
		if (! empty ( $openappid )) {
			// 开放平台消息加入明文回复信息方便查看
			$finalwriteinfo .= "---------------weact response msg decode info---------------\n";
			$finalwriteinfo .= "RESPONSE_TYPE : " . $opentype . ", \n"; 				// 开放回复消息类型
			$finalwriteinfo .= jsencode ( $opendecodemsg ) . "\n"; 						// 开放回复消息明文Json
		}
		$finalwriteinfo .= $interface_speed; 											// 加上接口测速信息
		// Step7：记录日志文件
		$fp = fopen ( $filepath . $filename, "a" ); 									// 打开文件获得读写所有权限
		flock ( $fp, LOCK_EX ); 														// 锁定日志文件
		fwrite ( $fp, strftime ( "%Y-%m-%d %H:%M:%S", time () ) . " 收到微信消息：\n" . $finalwriteinfo ); // 写入日志文件内容
		fwrite ( $fp, "---------------msg handle end---------------\n\n\n" ); 			// 消息处理结束
		flock ( $fp, LOCK_UN ); 														// 解锁日志文件
		fclose ( $fp ); 																// 释放文件解锁
		return true;
	}
	
	/**
	 * 公众号响应用户消息的检查函数。
	 * @param array $einfo 消息目标商家
	 * @param array $data 从微信服务器接收到的数据（数组形式）
	 * @return array $reply 返回服务器针对不同用户行为不同的响应数据
	 */
	private function responsecheck($einfo = NULL, $data = NULL){
		// 针对不同消息类型做出不同处理
		if ($data ['MsgType'] == 'event') {
			if ($data ['Event'] == 'subscribe') {
				// 此处写入用户关注事件（关注事件有两种类型：1、用户直接关注公众号；2、用户通过扫描二维码进入关注公众号），未关注是subscribe
				if (empty ( $data ['Ticket'] )) {
					// 如果没有$data['EventKey']值，说明是直接关注公众号：严重错误：特别注意，isset($data['EventKey'])什么都判断不出来，直接null，不是0或1
					// 特别警告：千万不能再用微信消息包里的EventKey来判断是扫码还是普通关注了，因为这个值现在被微信用作最后一笔交易订单的编号，而扫码一定是有Ticket值的（2015/06/02 18:37:26）。
					$reply = $this->responsesubscribe ( $einfo, $data );  // 回复关注公众号事件
				} else {
					// 如果有$data['EventKey']值，说明是扫描二维码的关注
					import ( 'Class.API.WeChatAPI.QRCodeScanHandle', APP_PATH, '.php' ); // 载入微动新扫码处理类
					$scanhandle = new WeChatQRCodeScan (); // 新建扫码处理类对象
					$replyinfo = $scanhandle->qrScanHandle ( $einfo, $data ); // 处理扫码事件
					$reply = $this->$replyinfo ['response_function'] ( $einfo, $replyinfo ); // 返回扫码处理回复信息
				}
			} else if ($data ['Event'] == 'unsubscribe') {
				// 此处写入用户取消关注事件（用户将无法收到消息，此处主要写本平台对自己数据库的一些处理记录）
				$reply = $this->responseunsubscribe ( $einfo, $data ); // 用户取消关注公众号
			} else if ($data ['Event'] == 'SCAN') {
				// 此处写入已关注用户扫描二维码事件，已关注是SCAN（已经关注再扫描，可以对原有没有分店的用户进行分店识别）
				import ( 'Class.API.WeChatAPI.QRCodeScanHandle', APP_PATH, '.php' ); // 载入微动扫码处理类
				$scanhandle = new WeChatQRCodeScan (); // 新建扫码处理类对象
				$replyinfo = $scanhandle->qrScanHandle ( $einfo, $data ); // 处理扫码事件
				if (! empty ( $replyinfo ['response_function'] )) {
					$reply = $this->$replyinfo ['response_function'] ( $einfo, $replyinfo ); // 返回扫码处理回复信息
				} else {
					$reply = $this->responsesuccess (); // 没有定义则直接回复收到，增加一层容错处理
				}
			} else if ($data ['Event'] == 'CLICK') {
				// 点击key类型菜单按钮的事件处理
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responmenuclick ( $einfo, $data ); // 响应菜单点击
			} else if ($data ['Event'] == 'VIEW') {
				// 此处接口：点击url菜单按钮的事件处理，可统计点击跳转网页按钮的点击率
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 此接口暂不做响应
			} else if ($data ['Event'] == 'scancode_push') {
				// 此处接口：扫码推事件的事件推送
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			} else if ($data ['Event'] == 'scancode_waitmsg') {
				// 此处接口：扫码推事件且弹出“消息接收中”提示框的事件推送
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			} else if ($data ['Event'] == 'pic_sysphoto') {
				// 此处接口：弹出系统拍照发图的事件推送
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			} else if ($data ['Event'] == 'pic_photo_or_album') {
				// 此处接口：弹出拍照或者相册发图的事件推送
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			} else if ($data ['Event'] == 'pic_weixin') {
				// 此处接口：弹出微信相册发图器的事件推送
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			} else if ($data ['Event'] == 'LOCATION') {
				$reply = $this->responselocation ( $einfo, $data ); // 用户进入公众号后，愿意提供地理位置给公众号的event事件，响应地理位置事件
			} else if ($data ['Event'] == 'location_select') {
				// 此处接口：弹出地理位置选择器的事件推送
				$reply = $this->responsesuccess (); // 暂且关闭此接口
			}
		} else {
			if ($data ['MsgType'] == 'text') {
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				// 检查顾客是否有选择导购，要不要将消息转发到导购聊天体系
				/* $cinfo = $this->checkCustomerGuide ( $einfo, $data ['FromUserName'] ); // 检查顾客是否有选择导购
				if (! empty ( $cinfo )) { 
					// 如果顾客有选择导购，则直接把该消息发给导购
					$msginfo = array (
							'msgid' => $data ['MsgId'], // 微信文本消息有主键
							'msg_type' => 0, // 消息类型文本
							'content' => $data ['Content'] // 文本消息内容
					);
					$trasmitflag = $this->trasmitMsgToGuide ( $cinfo ['customer_id'], $cinfo ['guide_id'], $msginfo );
					if ($trasmitflag) {
						return $this->responsesuccess (); // 告知微信成功接收该消息
					}
				} else {
					// 如果顾客没有选择导购，则继续使用自动回复
					$reply = $this->textSegHandle ( $einfo, $data ['Content'], $data ['FromUserName'] ); // text类型必须区分关键字→$data ['Content']，调用文本处理函数进行处理（传入内容和回复者）
				} */
				$reply = $this->textSegHandle ( $einfo, $data ['Content'], $data ['FromUserName'] ); // text类型必须区分关键字→$data ['Content']，调用文本处理函数进行处理（传入内容和回复者）
			} else if ($data ['MsgType'] == 'image') {
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				// 用户发送图片信息过来
				$downloadresult = $this->downloadMedia ( $einfo, $data ['MediaId'], $data ['MsgType'] ); // 下载多媒体文件
				
				// 检查顾客是否有选择导购，要不要将消息转发到导购聊天体系
				/* $cinfo = $this->checkCustomerGuide ( $einfo, $data ['FromUserName'] ); // 检查顾客是否有选择导购
				if (! empty ( $cinfo )) {
					// 如果顾客有选择导购，则直接把该消息发给导购
					$msginfo = array (
							'msgid' => $data ['MsgId'], // 微信图片消息有主键
							'msg_type' => 1, // 消息类型图片
							'content' => $data ['MediaId'] // 消息内容是多媒体的MediaId
					);
					// 保险起见，为了尽最大可能保证通信质量，多媒体文件如果没下载成功，就用微信的
					if ($downloadresult ['errCode'] == 0) {
						// 如果从微信服务器下载多媒体成功，则使用自己服务器的路径
						$msginfo ['mediapath'] = $downloadresult ['mediapath']; // 自己服务器的路径
					} else {
						// 如果从微信服务器下载多媒体失败，则使用微信服务器的路径
						$msginfo ['mediapath'] = $data ['PicUrl']; // 微信服务器的图片路径
					}
					$trasmitflag = $this->trasmitMsgToGuide ( $cinfo ['customer_id'], $cinfo ['guide_id'], $msginfo );
					if ($trasmitflag) {
						return $this->responsesuccess (); // 告知微信成功接收该消息
					}
				} else {
					$msginfo = "哇，这张图片真好看，需要我帮你打印吗（需微信公众号开通微信相册打印功能）^_^？"; // 暂且关闭此接口，先这么回应着
					$reply = $this->DIYresponsetext ( $msginfo ); // 暂且关闭此接口，自定义回复一下
				} */
				
				$msginfo = "哇，这张图片真好看，需要我帮你打印吗（需微信公众号开通微信相册打印功能）^_^？"; // 暂且关闭此接口，先这么回应着
				$reply = $this->DIYresponsetext ( $msginfo ); // 暂且关闭此接口，自定义回复一下
			} else if ($data ['MsgType'] == 'voice') {
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				// 用户发送语音过来
				$downloadresult = $this->downloadMedia ( $einfo, $data ['MediaId'], $data ['MsgType'] ); // 下载多媒体文件
				
				// 检查顾客是否有选择导购，要不要将消息转发到导购聊天体系
				/* $cinfo = $this->checkCustomerGuide ( $einfo, $data ['FromUserName'] ); // 检查顾客是否有选择导购
				if (! empty ( $cinfo )) {
					// 如果顾客有选择导购，则直接把该消息发给导购
					$msginfo = array (
							'msgid' => $data ['MsgId'], // 微信声音消息有主键
							'msg_type' => 2, // 消息类型为声音
							'content' => $data ['MediaId'] // 消息内容是多媒体的MediaId
					);
					// 保险起见，为了尽最大可能保证通信质量，多媒体文件如果没下载成功，就用使用自动回复
					if ($downloadresult ['errCode'] == 0) {
						// 如果从微信服务器下载多媒体成功，则使用自己服务器的路径
						$msginfo ['mediapath'] = $downloadresult ['mediapath']; // 自己服务器的路径
						$trasmitflag = $this->trasmitMsgToGuide ( $cinfo ['customer_id'], $cinfo ['guide_id'], $msginfo );
						if ($trasmitflag) {
							return $this->responsesuccess (); // 告知微信成功接收该消息，已经return掉了，不会往下走
						}
					}
					// 如果从微信服务器下载多媒体失败，则使用微信服务器的路径，没有声音路径，所以转到自动回复（这种情况很少发生）
				}  */
				$reply = $this->textSegHandle ( $einfo, $data ['Recognition'], $data ['FromUserName'] ); // voice类型必须区分关键字→$data ['Recognition']，调用文本处理函数进行处理（传入内容和回复者）
			} else if ($data ['MsgType'] == 'video' || $data ['MsgType'] == 'shortvideo') {
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				// 用户发送视频信息过来
				$downloadresult = $this->downloadMedia ( $einfo, $data ['MediaId'], $data ['MsgType'] ); // 下载多媒体文件
				
				// 检查顾客是否有选择导购，要不要将消息转发到导购聊天体系
				/* $cinfo = $this->checkCustomerGuide ( $einfo, $data ['FromUserName'] ); // 检查顾客是否有选择导购
				if (! empty ( $cinfo )) {
					// 如果顾客有选择导购，则直接把该消息发给导购
					$msginfo = array (
							'msgid' => $data ['MsgId'], // 微信视频消息有主键
							'msg_type' => 3, // 消息类型视频
							'content' => $data ['MediaId'] // 消息内容是多媒体的MediaId
					);
					// 保险起见，为了尽最大可能保证通信质量，多媒体文件如果没下载成功，则使用自动回复
					if ($downloadresult ['errCode'] == 0) {
						// 如果从微信服务器下载多媒体成功，则使用自己服务器的路径
						$msginfo ['mediapath'] = $downloadresult ['mediapath']; // 自己服务器的路径
						$trasmitflag = $this->trasmitMsgToGuide ( $cinfo ['customer_id'], $cinfo ['guide_id'], $msginfo );
						if ($trasmitflag) {
							return $this->responsesuccess (); // 告知微信成功接收该消息，已经return掉了，不会往下走
						}
					}
					// 如果从微信服务器下载多媒体失败，则使用自动回复，因为视频消息是没有路径的
				}  */
				$msginfo = "哇，这段视频不错，需要我帮你推广吗^_^？"; // 暂且关闭此接口，先这么回应着
				$reply = $this->DIYresponsetext ( $msginfo ); // 暂且关闭此接口，自定义回复一下
			} else if ($data ['MsgType'] == 'location') {
				// 用户打开定位器定位后，主动上报地理位置是小写的location，并且MsgType直接是location，而不是event事件
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$reply = $this->responselocation ( $einfo, $data, true ); // 响应用户打开定位器定位后，主动分享的地理位置$positiveshare = true
			} else if ($data ['MsgType'] == 'link') {
				// 用户发送链接过来
				//$refreshresult = $this->refreshLatestActive ( $einfo ['original_id'], $data ['FromUserName'], $data ['CreateTime'] ); // 刷新微信用户活跃时间
				$msginfo = "好的，收到，我马上点开看看！^_^"; // 暂且关闭此接口，先这么回应着
				$reply = $this->DIYresponsetext ( $msginfo ); // 暂且关闭此接口，自定义回复一下
			}
		}
		return $reply;
	}
	
	/**
	 * 回复成功函数.
	 * @return array $reply 正确收到微信消息并响应
	 */
	private function responsesuccess() {
		return array ( '', 'SUCCESS' ); // 直接回复成功
	}
	
	/**
	 * 回复文本类型消息函数，该函数消息内容可以自定义。
	 * @param string $diyinfo 想要自主回复的信息内容
	 * @return array $reply 自主回复的内容数组
	 */
	private function DIYresponsetext($diyinfo = '收到') {
		return array ( $diyinfo, 'text' ); // 直接回复成功
	}
	
	/**
	 * 响应用户关注事件推送。
	 * @param array $einfo 消息目标商家信息
	 * @param array $data 消息数据内容
	 * @return array $reply 最终回复内容
	 */
	private function responsesubscribe($einfo = NULL, $data = NULL) {
		$reply = array ( '', 'SUCCESS' ); // 最终的回复结果，默认接收成功
		if (! empty ( $data )) {
			// 做一个数据的同步
			$regupresult = $this->subscribeRegisterUpdate ( $einfo, $data ['FromUserName'] ); // 同步微信用户数据
			// 查询关注的回复
			$armap = array (
					'e_id' => $einfo ['e_id'],
					'response_type' => $data ['Event'],
					'is_del' => 0
			);
			$artable = M ( 'autoresponse' );
			$arresult = $artable->where ( $armap )->order ( 'add_response_time desc' )->limit ( 1 )->select (); // 找到事件消息对应设定的结果，取最新一条数据（注意查出来是个二维数组！）
			if ($arresult) {
				$reply = $this->$arresult [0] ['response_function'] ( $einfo, $arresult [0] ); // 调用多态函数
			}
		}
		return $reply;
	}
	
	/**
	 * 用户取消关注解绑函数。
	 * @param array $einfo 消息目标商家信息
	 * @param array $data 消息数据内容
	 * @return array $reply 回复的内容
	 */
	private function responseunsubscribe($einfo = NULL, $data = NULL) {
		$reply = array ( '', 'SUCCESS' ); // 最终的回复结果，默认接收成功
		if (! empty ( $data )) {
			$usermap = array (
					'enterprise_wechat' => $einfo ['original_id'], // 企业的微信id
					'openid' => $data ['FromUserName'], // 某用户
					'is_del' => 0
			);
			$cancelresult = M ( 'wechatuserinfo' )->where ( $usermap )->setField ( 'subscribe', 0 ); // 将该用户的关注状态改为非关注
		}
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复文本内容。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 要回复的消息内容（含主键）
	 * @return array $reply 回复的内容
	 */
	private function responsetext($einfo = NULL, $responseinfo = NULL){
		$msgtext_id = $responseinfo ['response_content_id']; // 取出回复信息中的文本消息主键
		// 缩写msgtext→mt
		$mtmap = array (
				'msgtext_id' => $msgtext_id,
				'e_id' => $einfo ['e_id'],
				'is_del' => 0 
		);
		$mtresult = M ( 'msgtext' )->where ( $mtmap )->find ();
		$reply = array ( $mtresult ['content'], 'text' );
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复图片内容（回复图片未完善）。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 要发送图片内容（含主键）
	 * @return array $reply 回复的内容
	 */
	private function responseimage($einfo = NULL, $responseinfo = NULL){
		$msgimage_id = $responseinfo ['response_content_id']; // 取出回复信息中的图片消息主键
		//缩写msgimage→mi
		$mimap = array (
				'msgimage_id' => $msgimage_id,
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$miresult = M ( 'msgimage' )->where ( $mimap )->find ();
		$reply = array ( $miresult ['media_id'], 'image' );
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复语音内容（回复语音未完善）。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 要发送声音内容（含主键）
	 * @return array $reply 回复的内容
	 */
	private function responsevoice($einfo = NULL, $responseinfo = NULL){
		$msgvoice_id = $responseinfo ['response_content_id']; // 取出回复信息中的语音消息主键
		//缩写msgvoice→mvc
		$mvcmap = array(
				'msgvoice_id' => $msgvoice_id,
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$mvcresult = M ( 'msgvoice' )->where ( $mvcmap )->find ();
		$reply = array ( $mvcresult ['media_id'], 'voice' );
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复视频内容（回复视频未完善）。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 要发送视频内容（含主键）
	 * @return array $reply 回复的内容
	 */
	private function responsevideo($einfo = NULL, $responseinfo = NULL){
		$msgvideo_id = $responseinfo ['response_content_id']; // 取出回复信息中的视频消息主键
		//缩写msgvideo→mvd
		$mvdmap = array (
				'msgvideo_id' => $msgvideo_id,
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$mvdresult = M ( 'msgvideo' )->where ( $mvdmap )->find ();
		$title = isset ( $mvdresult ['title'] ) ? $mvdresult ['title'] : "视频消息";
		$description = isset ( $mvdresult ['description'] ) ? $mvdresult ['description'] : "请注意查收";
		
		$mediapath = $_SERVER ['DOCUMENT_ROOT'] . assemblepath ( $mvdresult ['local_path'] ); // 获取绝对路径
		$mediatype = "video";
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$media_id = $swc->uploadMedia ( $einfo, $mediapath, $mediatype ); // 上传多媒体文件
		
		$videoinfo = array (
				'MediaId' => $media_id, 
				'Title' => $title, 
				'Description' => $description, 
		);
		$reply = array ( $videoinfo, 'video' );
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复音乐内容（回复音乐未完善）。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 要发送音乐内容（含主键）
	 * @return array $reply 回复的内容
	 */
	private function responsemusic($einfo = NULL, $responseinfo = NULL){
		$msgmusic_id = $responseinfo ['response_content_id']; // 取出回复信息中的音乐消息主键
		//把音乐信息入库
		$musicmap = array (
				'msgmusic_id' => $msgmusic_id,
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$musicresult = M ( 'msgmusic' )->where ( $musicmap )->find ();
		// 组装音乐信息
		$reply = array( $musicresult ['title'], $musicresult ['description'], $musicresult ['music_url'], $musicresult ['hq_music_url'], $musicresult ['thumb_media_id'], 'music');
		return $reply;
	}
	
	/**
	 * 响应消息并被动回复图文消息。
	 * @param array $einfo 消息目标商家信息
	 * @param array $responseinfo 消息编号
	 * @property string $response_content_id(msgnews_id) 图文消息主键
	 * @property string $params 图文消息参数数组
	 * @return array $reply 回复的内容
	 */
	private function responsenews($einfo = NULL, $responseinfo = NULL) {
		$reply = array ( '', 'SUCCESS' ); // 最终的回复结果，默认接收成功
		if (! empty ( $responseinfo )) {
			$msgnews_id = $responseinfo ['response_content_id']; // 取出回复信息中的图文消息主键
			$params = $responseinfo ['params']; // 取出图文消息中可能需要拼接的参数
			// 查询图文信息
			$newsmap = array (
					'e_id' => $einfo ['e_id'],
					'msgnews_id' => $msgnews_id // 当前图文消息编号
			);
			$newsinfo = M ( 'msgnews_info' )->where ( $newsmap )->order ( 'detail_order asc' )->select (); // 从视图中查出图文
			if ($newsinfo) {
				$content = array (); // 声明最后推送的图文变量
				for($i = 0; $i < count ( $newsinfo ); $i ++) {
					$content [$i] ['Title'] = $newsinfo [$i] ['title'];
					$content [$i] ['Description'] = $newsinfo [$i] ['summary']; // 图文摘要（不是主信息）
					$content [$i] ['PicUrl'] = assemblepath ( $newsinfo [$i] ['cover_image'], true ); // 组装下图片路径，必须是绝对路径
					if (! empty ( $params )) {
						$content [$i] ['Url'] = $newsinfo [$i] ['link_url'] . "?" . http_build_query ( $params ); // 如果是有参数的，带上自己的参数（有子分店的，带上子分店的id编号（注意URL格式））
					} else {
						$content [$i] ['Url'] = $newsinfo [$i] ['link_url'];
					}
				}
				$reply = array ( $content, 'news' );
			}
		}
		return $reply;
	}
	
	/**
	 * 点击菜单响应事件函数。
	 * @param array $einfo 消息目标商家信息
	 * @param array $data 消息数据内容
	 * @return array $reply 最终回复内容
	 */
	private function responmenuclick($einfo = NULL, $data = NULL) {
		$reply = array ( '', 'SUCCESS' ); // 最终的回复结果，默认接收成功
		if (! empty ( $data )) {
			// 缩写：customizedmenu→cm
			$cmmap = array (
					'e_id' => $einfo ['e_id'],
					'type' => 'click',
					'key' => $data ['EventKey'], // 相应的key键码
					'is_del' => 0
			);
			$cmresult = M ( 'customizedmenu' )->where ( $cmmap )->find ();
			if ($cmresult) {
				$reply = $this->$cmresult ['response_function'] ( $einfo, $cmresult ); // 调用多态函数
			}
		}
		return $reply;
	}
	
	/**
	 * 响应用户允许公众号定位的事件（该事件如果用户允许，打开公众号就有）
	 * @param array $einfo 消息目标商家信息
	 * @param array $data 消息数据内容
	 * @param boolean $positiveshare 用户打开地址定位器主动分享，默认为被动向公众号上报地理位置
	 * @return array $reply 最终回复内容
	 */
	private function responselocation($einfo = NULL, $data = NULL, $positiveshare = FALSE) {
		$reply = array ( '', 'SUCCESS' ); // 最终的回复结果，默认接收成功
		if (! empty ( $data )) {
			$armap = array (
					'e_id' => $einfo ['e_id'],
					'is_del' => 0
			);
			// 根据不同地理位置来区分函数重载，并锁定搜索参数
			if ($positiveshare) {
				$armap ['response_type'] = $data ['MsgType']; // 打开地址定位器主动上报，MsgType就是小写的location
			} else {
				$armap ['response_type'] = $data ['MsgType']; // 默认为被动向公众号上报地理位置，MsgType就是event
				$armap ['response_event'] = $data ['Event']; // 默认为被动向公众号上报地理位置，Event事件类型是大写的LOCATION
			}
			$artable = M ( 'autoresponse' );
			$arresult = $artable->where ( $armap )->order ( 'add_response_time desc' )->limit ( 1 )->select (); // 找到事件消息对应设定的结果，取最新一条数据（注意查出来是个二维数组！）
			if ($arresult) {
				$reply = $this->$arresult [0] ['response_function'] ( $einfo, $arresult [0] ); // 查询到有添加结果，直接调用多态函数
			}
		}
		return $reply;
	}
	
	/**
	 * 刷新微信用户表中某微信用户最后一次活跃的时间（已经用触发器解决问题）。
	 * 特别注意：普通关注和扫码关注（包括已关注用户扫码），其实不用调用本函数刷新活跃时间，因为这两种关注的时候系统在代注册或代更时已经刷新了活跃时间。
	 * 其他Excel列出的要更新活跃时间的必须调用本函数进行刷新活跃时间，为了方便，现罗列如下：（2015/04/14 22:21:25.）
	 * 1、点击菜单推图文；2、点击菜单跳链接；3、点击扫码识别二维码；4、点击扫码等待；5、弹出系统拍照发图；6、弹出相册发图；7、相册发图推事件；
	 * 8、发送文本消息；9、发送图片消息；10、发送声音消息；11、发送视频消息；12、主动发送地理位置；13、发送链接消息。
	 * 
	 * @param string $original_id 企业微信original_id
	 * @param string $openid 微信用户的openid（据说对公众号唯一）
	 * @param number $activetime 最后一次活跃的时间戳
	 * @return boolean $refreshresult 返回是否成功更新微信用户的活跃时间
	 */
	private function refreshLatestActive($original_id = '', $openid = '', $activetime = 0) {
		$refreshresult = false; // 最后一次更新的结果
		if (! empty ( $openid )) {
			$refreshmap = array (
					'enterprise_wechat' => $original_id, // 企业微信号original_id
					'openid' => $openid, // 微信用户编号
					'is_del' => 0 // 没有被删除的微信用户
			);
			$refreshresult = M ( 'wechatuserinfo' )->where ( $refreshmap )->setField ( 'latest_active', $activetime ); // 更新微信用户表时间
		}
		return $refreshresult;
	}
	
	/**
	 * 用户关注公众号，系统帮该用户代注册。
	 * 如果用户是新用户关注，则直接在customerinfo表中生成记录；
	 * 如果用户是老用户（customerinfo表为准）重新关注，则更新其在wechatuserinfo表中的信息即可。
	 * 值得一提的是：调用获取微信用户信息接口，大概需要3秒返回，所以建议只在关注的时候更新比较好，活跃度的时候不要频繁更新信息。
	 * @param array $e_id 企业信息
	 * @param string $openid 微信用户编号
	 * @return boolean $regupresult 为该微信用户注册或更新的结果
	 */
	private function subscribeRegisterUpdate($einfo = NULL, $openid = '') {
		$regupresult = false; // 注册或更新用户信息的结果
		if (! empty ( $openid )) {
			
			// 不管是新用户，还是老用户新关注，肯定是要获取其信息的，所以直接调用微信获取信息接口
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层
			$wechaterinfo = $swc->getUserInfo ( $einfo, $openid ); // 调用微信接口获取用户信息
			
			// Step1：检测是否新用户，如果是新用户，系统为其代注册微动账号
			$citable = M ( 'customerinfo' ); // 实例化顾客表
			$customerexist = array (
					'e_id' => $einfo ['e_id'], // 当前企业
					'openid' => $openid, // 当前微信用户openid
					'is_del' => 0 // 没有被删除的
			);
			$cinfo = $citable->where ( $customerexist )->find (); // 从customerinfo表中尝试找这个新关注用户的信息
			if (! $cinfo) {
				// 如果没找到这样的顾客，说明该顾客是新关注的，需要为他代注册一条customerinfo信息
				$newcinfo = array (
						'customer_id' => date ( 'YmdHms' ) . randCode ( 4, 1 ), 	// 产生顾客编号
						'openid' => $openid, // 用户微信openid
						'e_id' => $einfo ['e_id'], // 企业编号
						'nickname' => $wechaterinfo ['nickname'], // 取微信用户的nickname作为微动用户的nickname
						'register_time' => time (), // 系统代注册时间
						'remark' => "用户（微信openid为" . $openid . "）在" . timetodate( time () ) . "以非扫码方式（自己搜索或朋友名片推荐）关注本公众号，系统为其代注册账号，非扫码关注无门店区分。"
				);
				// 如果微信用户填写了性别，就读取其性别
				if ($wechaterinfo ['sex'] == 1) {
					$newcinfo ['sex'] = "男"; 
				} else if ($wechaterinfo ['sex'] == 2) {
					$newcinfo ['sex'] = "女";
				}
				$addcustomerresult = $citable->add (); // 将新关注的顾客插入顾客信息表中
			}
			
			// Step2：检测wechatuserinfo表中是否有该用户，如果没有则新插入一条信息（新用户）；如果有更新其信息（老用户新关注）。
			$wutable = M ( 'wechatuserinfo' ); // 实例化微信用户表
			$wuexist = array (
					'enterprise_wechat' => $einfo ['original_id'], // 当前微信公众号original_id
					'openid' => $openid, // 当前微信用户
					'is_del' => 0 // 没有被删除的
			);
			$oldwuinfo = $wutable->where ( $wuexist )->find (); // 尝试找到微信用户信息
			if ($oldwuinfo && ! empty ( $wechaterinfo ['subscribe_time'] )) {
				// 如果找到这样的用户，说明是老用户重新关注，系统使用接口返回的为其更新老用户信息
				$oldwuinfo ['subscribe'] = $wechaterinfo ['subscribe']; // 新关注啦
				$oldwuinfo ['nickname'] = $wechaterinfo ['nickname']; // 用户的昵称
				$oldwuinfo ['sex'] = $wechaterinfo ['sex']; // 用户的性别
				$oldwuinfo ['language'] = $wechaterinfo ['language']; // 用户使用的语言
				$oldwuinfo ['city'] = $wechaterinfo ['city']; // 用户所在城市
				$oldwuinfo ['province'] = $wechaterinfo ['province']; // 用户所在省份
				$oldwuinfo ['country'] = $wechaterinfo ['country']; // 用户所在国家
				$oldwuinfo ['head_img_url'] = $wechaterinfo ['headimgurl']; // 用户的头像
				$oldwuinfo ['subscribe_time'] = time (); // 老用户重新关注，时间用新关注的时间
				$oldwuinfo ['latest_active'] = time (); // 关注重新刷新活跃时间
				$regupresult = $wutable->save ( $oldwuinfo ); // 将用户的新信息更新回表中
			} else {
				// 如果没找到这样的用户，说明是全新的用户，系统为其插入一条新微信用户信息
				$newwuinfo = array (
						'user_info_id' => md5 ( uniqid ( rand (), true ) ), // 生成主键
						'enterprise_wechat' => $einfo ['original_id'], // 企业微信号openid
						'group_id' => 0, // 新关注用户默认就在未分组里
						'subscribe' => 1, // 用户关注
						'openid' => $openid, // 当前微信用户openid
						'nickname' => $wechaterinfo ['nickname'], // 微信用户昵称
						'sex' => $wechaterinfo ['sex'], // 微信用户性别
						'language' => $wechaterinfo ['language'], // 微信用户使用语言
						'city' => $wechaterinfo ['city'], // 微信用户所在城市
						'province' => $wechaterinfo ['province'], // 微信用户所在省份
						'country' => $wechaterinfo ['country'], // 微信用户所在国家
						'head_img_url' => $wechaterinfo ['headimgurl'], // 微信用户头像
						'subscribe_time' => time (), // 用户的关注时间是当前
						'add_time' => time (), // 新增这条用户记录的时间
						'latest_active' => time (), // 新用户关注，最新活跃时间是现在
						
				);
				$regupresult = $wutable->add ( $newwuinfo ); // 将新微信用户信息插入到微信用户表中
			}
		}
		return $regupresult;
	}
	
	/*-----------------------------以上是自动回复的多态函数区域--------------------------------*/
	
	/*-------------------------------微动数据库记录---------------------------------*/
	
	/**
	 * 将用户发来的信息存入数据库，对于微信服务器发送的信息，也要存表做记录。
	 * @param array $data 接收的数据
	 */
	private function wechatDataRecord($data = NULL) {
		//缩写：wechatmsginfo→wcmi
		$wcmitable = M ( 'wechatmsginfo' );						//实例化微信基础接口信息表
		import ( 'Class.API.WeChatAPI.WeActDataHandle', APP_PATH, '.php' );//加载微信SDK基础API接口
		$weactdh = new WeActDataHandle ();						//实例化微信基础信息处理接口
		$data = $weactdh->dataHandle ( $data );					//调用类函数处理微信信息，返回给本函数存入数据库
		$wcmiresult = $wcmitable->data ( $data )->add ();		//将服务器数据插入数据库
	}
	
	/**
	 * 收到多媒体消息后，自动从微信服务器下载多媒体文件。
	 * @param array $einfo 企业信息
	 * @param string $mediatype 多媒体文件类型，有image,voice,video
	 * @return array $downloadresult 下载多媒体成功与否信息
	 */
	private function downloadMedia($einfo = NULL, $mediaid = '', $mediatype = 'image') {
		$downloadresult = array (
				'errCode' => 10001,
				'errMsg' => "下载多媒体文件失败！"
		); // 下载多媒体文件是否成功标志，默认为下载失败
		if (! empty ( $mediaid )) {
			// 下载多媒体文件的准备信息
			//$mediafilepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__; // 多媒体文件存放的文件夹路径（下载到磁盘必须绝对路径）
			$mediafilepath = ""; // 多媒体文件存放的文件夹路径（下载到磁盘必须绝对路径）
			$mediafilename = md5 ( uniqid ( rand (), true ) ); // 本次保存多媒体文件的文件名，也用md5 uniqid保证不会重复
			$mediasuffix = ""; // 多媒体文件的后缀名
			// 根据不同媒体类型判断后缀和路径名称
			switch ($mediatype) {
				case "image":
					$mediasuffix = ".jpg"; // 如果是图片类型文件，默认保存.jpg格式
					$mediafilepath = "./Updata/images/" . $einfo ['e_id'] . "/customerfiles/wechatmedia/image/" . date ( "Ymd" ) . "/"; // 存放微信用户上传图片的文件夹路径
					break;
				case "voice":
					$mediasuffix = ".amr"; // 如果是语音类型文件，默认保存.amr格式
					$mediafilepath = "./Updata/images/" . $einfo ['e_id'] . "/customerfiles/wechatmedia/voice/" . date ( "Ymd" ) . "/"; // 存放微信用户上传声音的文件夹路径
					break;
				case "video":
					$mediasuffix = ".mp4"; // 如果是视频类型文件，默认保存.mp4格式
					$mediafilepath = "./Updata/images/" . $einfo ['e_id'] . "/customerfiles/wechatmedia/video/" . date ( "Ymd" ) . "/"; // 存放微信用户上传视频的文件夹路径
					break;
				default:
					return $downloadresult; // 未识别格式直接返回下载不成功
					break;
			}
			$mediafullname = $mediafilename . $mediasuffix; // 多媒体文件的全名，带后缀
			if (! file_exists ( $mediafilepath )) mkdirs ( $mediafilepath ); // 如果文件夹路径不存在，创建文件夹
			$mediaabsolutepath = $mediafilepath . $mediafullname; // 多媒体保存的最终全路径带上文件名与后缀
			
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层
			$fileinfo = $swc->downloadWechatMedia ( $einfo, $mediaid, $mediaabsolutepath ); // 调用接口下载多媒体文件
			if ($fileinfo ['errCode'] == 0) {
				// 如果多媒体从微信服务器下载成功
				
				$downloadresult ['errCode'] = 0; 
				$downloadresult ['errMsg'] = "ok";
				$downloadresult ['mediapath'] = "http://www.we-act.cn" . substr ( $mediaabsolutepath, 1 ); // 附加上自己服务器的多媒体文件路径
				
				// 如果多媒体是图片，为了方便APP手机显示，再生成一大一小缩略图，小图出现在手机聊天界面，点击后可以预览，像微信一样可下载原图
				if ($mediatype == "image") {
					import ( 'ORG.Util.Image' ); // 载入类库图片类
					$thumbimagepath = $mediafilepath . "thumb_" . $mediafullname; // 缩略图相对路径
					$thumbMaxWidth = '300'; // 宽度最大300
					$thumbMaxHeight = '300'; // 高度最大300，缩略图在30KB左右
					Image::thumb ( $mediaabsolutepath, $thumbimagepath, '', $thumbMaxWidth, $thumbMaxHeight ); // thumb是等比例压缩，优先满足宽或高的任意一个
					//$downloadresult ['thumb_mediapath'] = "http://www.we-act.cn/weact" . substr ( $thumbimagepath, 1 ); // 如果是图片，附带一张缩略图（缩略图绝对路径）
				}
			}
		}
		return $downloadresult; // 将下载多媒体文件信息数组返回
	}
	
	/**
	 * 多媒体上传记录的函数，需要定时刷新（3天过期）。
	 * @param string $uploader 上传多媒体的商家
	 * @param string $rminfo 多媒体上传信息，从微信服务器返回
	 * @param string $path 多媒体在微动服务器的路径，用来预览或者刷新media_id
	 * @return 上传成功true，失败false
	 */
	private function multimediaUploadRecord($uploader='', $rminfo=NULL, $path=''){
		if ($rminfo ['media_id'] != null || $rminfo ['media_id'] != '') {
			$multiDataTable = '';
			switch ($rminfo ['type']) {
				case 'image' :
					$multiDataTable = M ( 'msgimage' );
					break;
				case 'voice' :
					$multiDataTable = M ( 'msgvoice' );
					break;
				case 'music' :
					$multiDataTable = M ( 'msgmusic' );
					break;
				case 'video' :
					$multiDataTable = M ( 'msgvideo' );
					break;
				default :
					$multiDataTable = M ( 'msgimage' );
					break;
			}
			$mdt = array (
					'msg' . $rminfo ['type'] . '_id' => md5 ( uniqid ( rand (), true ) ), // 随机一个微动的主键
					'e_id' => $uploader, // 上传多媒体的商家$uploader
					'local_path' => $path, // 多媒体在微动平台的路径
					'add_time' => $rminfo ['created_at'], // 微信返回的时间
					'msg_use' => 1, // 默认选择1，以后如果改动则变成其他的
					'media_id' => $rminfo ['media_id'] 
			); // 微信返回的media_id

			$mdtresult = $multiDataTable->data ( $mdt )->add (); // 插入多媒体数据
			return true;
		} else {
			return false;
		}
	}
	
	/*-------------------------------微动数据库记录---------------------------------*/
	
	/*-----------------------------以下是本平台主动调用微信接口的函数--------------------------------*/
	
	/**
	 * 示例 三、微信发送音乐示例。
	 */
	public function sendMusicAPI(){
		$wmimap = array(
			'to_user_name' => 'gh_dc1923302e67',
			'is_del' => 0
		);
		$wmitable = M('wechatmsginfo');
		$wmiresult = $wmitable->where($wmimap)->Distinct(true)->field('from_user_name')->select();
		
		for($i=0;$i<count($wmiresult);$i++){
			p($this->sendText('201406261550250006', $wmiresult[$i][from_user_name], 'text00008'));
			p($this->sendMusic('201406261550250006', $wmiresult[$i][from_user_name], 'music00001'));
		}
	}
	
	/*-----------------------------以上是本平台主动调用微信接口的函数--------------------------------*/
	
	/*--------------------一些应用类的接口区域----------------------*/
	
	/**
	 * 小黄鸡API接口函数。
	 * Author：赵臣升。
	 * CreateTime：2014/06/29 21:46:25。
	 * @param string $msg 用户的消息
	 * @param array $einfo 消息目标商家信息
	 * @return string SimSimi回复内容
	 */
	private function SimSimi($msg = '', $einfo = NULL) {
		$finalcontent = "您好，您提交的信息我们已经记下，会迅速提交给客服处理，请等待客服与您联系！";
		import ( 'Class.API.ServiceAPI.Simsimi', APP_PATH, '.php' );
		$sim = new Simsimi ();
		$responseResult = $sim->callSimsimi ( $msg );
		if ($responseResult ['result'] == 100 && ! empty ( $responseResult ['response'] )) {
			// 请求小黄鸡API只有在result字段等于100才表示成功，如果不成功（如KEY过期等原因），直接走到else分支。
			$simResRecord = array (
					'response_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $einfo ['e_id'],
					'user_ask' => $msg,
					'sim_response' => $responseResult ['response'],
					'result' => $responseResult ['result'],
					'sim_res_id' => $responseResult ['id'],
					'post_msg' => $responseResult ['msg'],
					'add_time' => time () 
			);
			// 缩写：simsimiresponse→ssr
			$ssrtable = M ( 'simsimiresponse' );
			$ssrresult = $ssrtable->data ( $simResRecord )->add (); // 对$responseResult和$msg做出存数据库，吸收小黄鸡的数据，日后做分析。
			if ($ssrresult) {
				$finalcontent = $responseResult ['response'];
			}
		} 
		return $finalcontent;
	}
	
	/**
	 * 图灵机器人API接口函数。
	 * Author：赵臣升。
	 * CreateTime：2014/06/29 21:46:25。
	 * @param string $msg 用户的消息
	 * @param array $einfo 消息目标商家信息
	 * @return string 图灵机器人回复内容
	 */
	private function TuLingResponse($msg = '', $einfo = NULL) {
		$finalcontent = "您好，您提交的信息我们已经记下，会迅速提交给客服处理，请等待客服与您联系！";
		import ( 'Class.API.ServiceAPI.TuLing123', APP_PATH, '.php' );
		$tuling = new TuLingAutoResponse ();
		$responseResult = $tuling->autoResponse ( $msg );
		if (! empty ( $responseResult )) {
			$finalcontent = $responseResult; // 图灵自动回复不空才将结果返回给图灵
		}
		return $finalcontent;
	}
	
	/**
	 * 判断是否为点歌函数。
	 * @param string $orderMusic 要检查是否是点歌的话。
	 * @return boolean|array 如果检查到是要点歌，就返回点歌信息数组；如果不是点歌，返回false。
	 */
	public function isOrderMusic($orderMusic = ''){
		$music = false;
		$temp = mb_substr($orderMusic, 0, 2, 'utf-8');
		if($temp == '点歌'){
			$order = mb_substr($orderMusic, 2, strlen($orderMusic) - 2, 'utf-8');
			$final = trim($order);
			$musicresult = $this->getMusicInfo($final);
			$musicresult ['ThumbMediaId'] = '_BQeY9t0kclUh4WybzqOFkusZtTIwxkOPfnOX1uJ45qCbKP2zQExWh7D0m87He5f';
			$music = $musicresult;
		}
		return $music;
	}
	
	/**
	 * 判断是否在点歌（新）。
	 * @param array $wordslist 经过分词后的词组
	 * @param string $context 未分词前的一整句话
	 * @return boolean $isorder 是否在点歌
	 */
	public function isOrderMusicInfo($wordslist = NULL, $context = '') {
		$isorder = false; // 默认不是点歌
		$wordcount = count ( $wordslist );
		for($i = 0; $i < $wordcount; $i ++) {
			if ($wordslist [$i] ['word'] == "点歌" || $wordslist [$i] ['word'] == "点了歌" 
					|| $wordslist [$i] ['word'] == "点了首歌" || $wordslist [$i] ['word'] == "点了一首歌" 
					|| $wordslist [$i] ['word'] == "歌曲" || $wordslist [$i] ['word'] == "歌名" 
					|| $wordslist [$i] ['word'] == "听歌" || $wordslist [$i] ['word'] == "神曲") {
				// 找到这样的词汇
				$musicname = "";
				$musictag = $wordslist [$i] ['word']; // 记录这个词汇
				$position = strpos ( $context, $musictag ); // 找到起始位置
				$nameprefix = trim ( substr ( $context, 0, $position ) ); // 去除空格向前找
				$namesuffix = trim ( substr ( $context, $position + strlen ( $musictag ) ) ); // 去除空格向后找
				
				if (! empty ( $nameprefix ) || ! empty ( $namesuffix )) {
					$isorder = true; // 确实在找歌
					if (! empty ( $namesuffix )) {
						$musicname = $namesuffix; // 后半段给他（优先考虑后半段）
					} else {
						$musicname = $nameprefix; // 前半段给他
					}
				} 
				if ($isorder) {
					// 如果点歌，找寻点歌信息
					return $this->getMusicInfo ( $musicname );
				}
			}
		}
		return $isorder; // 并不是点歌
	}
	
	/**
	 * 获得音乐信息的API函数。
	 * @param string $entity
	 * $entity	eg.	凤凰传奇@最炫民族风
	 * @return array $content = array(
	 *						'Title' => $entity,
	 *						'Description' => 'Powered by WeAct.',
	 *						'MusicUrl' => $url_prefix.$url_suffix,
	 *						'HQMusicUrl' => $durl_prefix.$durl_suffix
	 *					);
	 */
	public function getMusicInfo($entity){
		import( 'Class.API.ServiceAPI.Music', APP_PATH, '.php' );
		$music = new Music();
		$musicinfo = $music->getMusic ( $entity );
		//$musicinfo ['ThumbMediaId'] = ""; // 记得再带上一张封面缩略图信息
		return $musicinfo;
	}
	
	/*--------------------一些应用类的接口区域----------------------*/
	
	/**
	 * 检测顾客是否有选择导购，如果有，将消息转发给导购。
	 * @param array $einfo 企业信息
	 * @param string $openid 微信用户的openid
	 * @return array $cinfo 顾客的信息，根据字段guide_id来判断是否
	 */
	private function checkCustomerGuide($einfo = NULL, $openid = '') {
		$guidecinfo = array (); // 接收消息的顾客信息
		if (! empty ( $openid )) {
			$cmap = array (
					'e_id' => $einfo ['e_id'], // 企业编号
					'openid' => $openid, // 顾客编号
					'is_del' => 0 // 没有被删除的顾客
			);
			$guidecinfo = M ( 'guide_wechat_customer_info' )->where ( $cmap )->find (); // 找到该顾客信息（只找一条消息）
		}
		return $guidecinfo; // 若该顾客选择了导购，返回导购编号，若没有选择导购返回空字符串
	}
	
	/**
	 * 将顾客消息转发给导购的函数。
	 * @param string $from_customerid 消息从哪个顾客来
	 * @param string $to_guideid 消息要发送给哪个导购
	 * @param array $msginfo 消息内容数组，必须包含如下字段信息：
	 * @property string msgid 微信文本、图片、声音、视频等多媒体文件的微信消息id
	 * @property string msg_type 消息类型，文本消息为0，语音消息为1，声音消息为2
	 * @property string content 消息内容 如果是文本，则是文本内容，如果是多媒体，则是多媒体微信的mediaid
	 * @property string mediapath 多媒体消息的微动服务器路径（如果是多媒体消息），文本消息该字段不必要
	 * @return array $confirmresult 返回给微信服务器，微动已经收到该消息，并转发给相关导购/客服人员。
	 */
	private function trasmitMsgToGuide($from_customerid = '', $to_guideid = '', $msginfo = NULL) {
		$transmitsuccess = false; // 默认消息转发给导购失败
		if (! empty ( $to_guideid ) && ! empty ( $msginfo )) {
			// 如果导购编号不空，并且消息内容也不空，则转发消息给导购
			$newcustomermsg = array (
					'chatrecord_id' => md5 ( uniqid ( rand (), true ) ), // 微动新消息主键
					'appmsg_id' => $msginfo ['msgid'], // 微信文本、图片、声音、视频等多媒体文件的微信消息id
					'belong_guide' => $to_guideid, // 消息属于哪个导购
					'sender_id' => $from_customerid, // 发送消息的顾客编号
					'receiver_id' => $to_guideid, // 接收消息的导购编号
					'chat_time' => time (), // 顾客发送消息的时间
					'receive_status' => 0, // 默认是新消息，导购端APP还没有接收
					'is_tocustomer' => 0, // 该消息是否导购发送给顾客，这里0代表不是，而是顾客发送给导购
					'msg_type' => $msginfo ['msg_type'], // 取消息类型
					'msg_content' => $msginfo ['content'] // 取消息内容
			);
			if ($msginfo ['msg_type'] == 1 || $msginfo ['msg_type'] == 2 || $msginfo ['msg_type'] == 3 || $msginfo ['msg_type'] == 4) {
				// 如果是多媒体类型消息，则把多媒体消息的服务器路径一并附带上
				$newcustomermsg ['media_path'] = $msginfo ['mediapath']; // 注意$msginfo里多媒体路径字段是mediapath
			}
			$transmitsuccess = M ( 'guidechatmsginfo' )->add ( $newcustomermsg ); // 将新消息插入导购顾客聊天消息表中
		}
		return $transmitsuccess; // 返回转发消息给导购的结果
	}
	
	/**
	 * 特别注意：该函数调用应该在丰富多彩的API（如天气、点歌等）之后
	 * 该函数处理功能先后顺序：
	 * 1、先分词；
	 * 2、先考虑关键字；
	 * 3、再调用智能检索函数；
	 * 4、实在无法匹配调用小黄鸡接口。
	 * 处理微信接收到的文本消息函数。
	 * Author：赵臣升。
	 * CreateTime：2014/07/19 01:05:20.
	 * 天气预报函数.
	 * Author：万路康。
	 * CreateTime：2014/07/19 00:08:10.
	 * @param array $einfo 消息目标商家信息
	 * @param string $context 客户发来的文本
	 * @param string $bereplied 被回复的顾客
	 * @return array $finalreply 最终处理得到的文本回复信息
	 */
	private function textSegHandle($einfo = NULL, $context = '你好', $bereplied = '') {
		// Step1：进行SAE智能分词
		import ( 'Class.API.ServiceAPI.SegmentWord', APP_PATH, '.php' );
		$sw = new SegmentWord ();
		$originalSpilt = $sw->querySegmentWord ( $context ); // 使用SAE分词API返回分词数组
		$originalMessage = ''; // 未经分割的原始字符串（用户提问）
	
		// Step2：判断是否查询附近信息接口
		import ( 'Class.API.ServiceAPI.Nearby', APP_PATH, '.php' );
		$nb = new Nearby ();
		$content = $nb->isQueryNearby ( $originalSpilt ); // 判断是否查询附近的...
		if ($content) {
			$location = $nb->searchUserLocation ( $einfo, $bereplied ); // 调用函数搜索用户位置，因为要查询该顾客的地理位置，所以要传入被回复者openid
			if (! $location) return array ( '您还没有分享过地理位置噢。', 'text' );
			else {
				return array ( $nb->queryNearby ( $content, $location ), 'news' ); // 可在queryNearby第三个参数中传入方圆半径
			}
		}
	
		// Step3：判断是否天气接口
		$queryWeather = false;
		for ($i = 0; $i < count ( $originalSpilt ); $i ++) {
			if ($originalSpilt [$i] ['word'] == '天气') {
				$queryWeather = true;
				break;
			}
		}
		if ($queryWeather) {
			for($i = 0; $i < count ( $originalSpilt ); $i ++) {
				if ($originalSpilt [$i] ['word_tag'] == 102) {
					import( 'Class.API.ServiceAPI.Weather', APP_PATH, '.php' ); // 载入天气类
					$wea = new Weather();
					return array ( $wea->getWeatherInfo ( $originalSpilt [$i] ['word'] ), 'news' ); // 返回天气信息
				}
			}
		}
	
		// Step4：判断是否快递
		import ( 'Class.API.ServiceAPI.Delivery', APP_PATH, '.php' );
		$deli = new Delivery ();
		$expressInfo = $deli->isQueryDelivery ( $originalSpilt ); // 判断是否查询快递
		if (! empty ( $expressInfo )) {
			return array ( $deli->queryDelivery ( $expressInfo ['com'], $expressInfo ['nu'] ), 'text' ); // 返回查询快递信息
		}
	
		// Step5：判断是否点歌接口
		$musicinfo = $this->isOrderMusicInfo ( $originalSpilt, $context );
		if ($musicinfo) {
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务类
			$musicmediapath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Public/images/musicmedia.jpg"; // 音乐文件封面所列图
			$mediainfo = $swc->uploadMedia ( $einfo, $musicmediapath, "image" ); // 上传缩略图
			$musicinfo ['ThumbMediaId'] = $mediainfo; // 附带音乐封面缩略图
			return array ( $musicinfo, 'music' ); // 返回所查询音乐的信息（带音乐格式的）
		}
	
		// Step6：其他情况考虑分词系统的关键字过滤网（特别注意：添加自动回复的时候，最好把相关的关键词也添加进去）
		for($i = 0, $k = 0; $i < count ( $originalSpilt ); $i ++) {
			$originalMessage .= $originalSpilt [$i] ['word'];
			if ($originalSpilt [$i] ['word_tag'] == '95' || $originalSpilt [$i] ['word_tag'] == '97' || $originalSpilt [$i] ['word_tag'] == '99' || $originalSpilt [$i] ['word_tag'] == '105') {
				$pickupArray [$k] ['word'] = $originalSpilt [$i] ['word'];
				$pickupArray [$k] ['word_tag'] = $originalSpilt [$i] ['word_tag'];
				$k ++;
			} else {
				// 此处进行数据库查表，如果匹配关键字也添加进来！特别注意：查到才进行。
				// 缩写：replykeyword→rk
				$rktable = M ( 'replykeyword' ); // 实例化关键字表
				$rkmap = array (
						'e_id' => $einfo ['e_id'], // 当前商家的编号
						'keyword_label' => $originalSpilt [$i] ['word'], // 把这个词放入关键字标签中
						'is_del' => 0
				); // 没有被删除的
				$rkresult = $rktable->where ( $rkmap )->order ( 'priority desc' )->limit ( 1 )->select (); // 按优先级查出最上边一条关键词相关的
				if ($rkresult) {
					// 如果不在词性考虑内的词语是商家设定的关键词，也把它加入到pickupArray中来
					if ($rkresult [0] ['keyword_label'] == $originalSpilt [$i] ['word']) {
						$pickupArray [$k] ['word'] = $originalSpilt [$i] ['word'];
						$pickupArray [$k] ['word_tag'] = $originalSpilt [$i] ['word_tag'];
						$k ++;
					}
				}
			}
		}
	
		// 调整数组
		for ($i = 0; $i < count ( $pickupArray ); $i ++) {
			$adjustArray [$i] = $pickupArray [$i] ['word'];
		}
		// Step8：进行人工智能检索
		$finalreply = $this->AIRetrieved ( $originalMessage, array_reverse ( $adjustArray, true ), $einfo );
		return $finalreply;
	}
	
	/**
	 * 人工智能检索回复信息函数。
	 * @param string $originMsg	原始的顾客提问
	 * @param array $pickupArray 经过SAE系统切割的分词数组（带词性）
	 * @param array $einfo 消息目标商家信息
	 * @return array 如果检索到信息、返回数组形式的信息；如果没有检索到，调用小黄鸡接口返回信息。
	 */
	private function AIRetrieved($originMsg = '', $pickupArray = NULL, $einfo = NULL) {
		// p($pickupArray);
		$arraynum = count ( $pickupArray ); // 统计要检索的词语个数$arraynum
		if ($arraynum > 0) {
			$length = $arraynum >= 10 ? 10 : $arraynum; // 获得分词数组的长度，如果超过10个词语，默认只检索前10个
			// Step1：多个词以上（大于1个词），会进入Step1的for循环，如果只有一个词，下边这个for循环被跳过
			for ($i = $length - 1; $i > 0; $i --) {
				// 第一层循环：一次组合拆分匹配
				$sql1 = ''; // 第一种匹配模式，直接等于原始字符串，如：我爱微动
				$sql2 = ''; // 第二种匹配模式：原始字符串前后加百分号，如：%我爱微动%
				$sql3 = ''; // 第三种匹配模式：根据分词数组，每个分词前后都有百分号，如：%我%爱%微动%
				// 计算每个长度的3种sql语句
				for($j = $length - 1 - $i; $j <= $length - 1; $j ++) {
					$sql1 .= $pickupArray [$j];
					$sql3 .= $pickupArray [$j] . '%';
				}
				$sql2 = '%' . $sql1 . '%';
				$sql3 = '%' . $sql3;
					
				// 查询条件1
				$sqlCondition1 = array (
						'e_id' => $einfo ['e_id'],
						'keyword' => $sql1,
						'is_del' => 0
				);
				// 查询条件2
				$sqlCondition2 = array (
						'e_id' => $einfo ['e_id'],
						'keyword' => array ( 'like', $sql2 ),
						'is_del' => 0
				);
				// 查询条件3
				$sqlCondition3 = array (
						'e_id' => $einfo ['e_id'],
						'keyword' => array ( 'like', $sql3 ),
						'is_del' => 0
				);
				// p($sqlCondition1);p($sqlCondition2);p($sqlCondition3);
				// 缩写：autoresponse→ar
				$artable = M ( 'autoresponse' );
				$arresult1 = $artable->where ( $sqlCondition1 )->order ( 'add_response_time desc' )->limit ( 1 )->select ();
				if ($arresult1) {
					return $this->$arresult1 [0] ['response_function'] ( $einfo, $arresult1 [0] ); // 调用多态回复函数
				} else {
					$arresult2 = $artable->where ( $sqlCondition2 )->order ( 'add_response_time desc' )->limit ( 1 )->select ();
					if ($arresult2) {
						return $this->$arresult2 [0] ['response_function'] ( $einfo, $arresult2 [0] ); // 调用多态回复函数
					} else {
						$arresult3 = $artable->where ( $sqlCondition3 )->order ( 'add_response_time desc' )->limit ( 1 )->select ();
						if ($arresult3) {
							return $this->$arresult3 [0] ['response_function'] ( $einfo, $arresult3 [0] ); // 调用多态回复函数
						}
					}
				}
			} // for（Step1）
			// Step2：考虑多词联查失败，进行一个词的逐个查询（从后往前匹配）；或者一开始就只有一个词，查询一次解决问题
			for ($i = $length - 1; $i >= 0; $i --) {
				// 直接相等的匹配
				$singleCondition1 = array (
						'e_id' => $einfo ['e_id'],
						'keyword' => $pickupArray [$i],
						'is_del' => 0
				);
				// 模糊查询的匹配
				$singleCondition2 = array (
						'e_id' => $einfo ['e_id'],
						'keyword' => array ( 'like', '%' . $pickupArray [$i] . '%' ),
						'is_del' => 0
				);
			
				// 缩写：autoresponse→ar
				$artable = M ( 'autoresponse' );
				$singleresult1 = $artable->where ( $singleCondition1 )->order ( 'add_response_time desc' )->limit ( 1 )->select ();
				if ($singleresult1) {
					$response = $this->$singleresult1 [0] ['response_function'] ( $einfo, $singleresult1 [0] );
					return $response; // 调用多态回复函数
				} else {
					$singleresult2 = $artable->where ( $singleCondition2 )->order ( 'add_response_time desc' )->limit ( 1 )->select ();
					if ($singleresult2) {
						return $this->$singleresult2 [0] ['response_function'] ( $einfo, $singleresult2 [0] ); // 调用多态回复函数
					}
				}
			}
		}
		// p($originMsg);p($einfo ['e_id']);
		//return array ( $this->SimSimi ( $originMsg, $einfo ), 'text' ); // 以上搜索都没匹配到的话（包括大于等于1个词和没词的情况）
		return array ( $this->TuLingResponse ( $originMsg, $einfo ), 'text' ); // 以上搜索都没匹配到的话（包括大于等于1个词和没词的情况）
	}
	
	/**
	 * ==========微信开放平台接口测试==========
	 */
	
	/**
	 * 接收授权信息和取消授权信息接口，带事件处理和接收机制。
	 */
	public function recAuthEvent() {
		
		import ( 'Class.API.WeChatAPI.WeactWechatOpen', APP_PATH, '.php' ); 	// 载入微信开放平台SDK
		
		$speedtime1 = microtime_double (); 					// 接口测速代码（记录起点）
		
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"]; 		// 获取微信post的数据
		$signature = $_GET ['signature']; 					// 从URL上获取微信签名
		$timestamp = $_GET ['timestamp']; 					// 从URL上获取微信签名时间戳
		$nonce = $_GET ['nonce']; 							// 从URL上获取微信签名随机数
		$encrypt_type = $_GET ['encrypt_type']; 			// 从URL上获取微信加密方法
		$msg_signature = $_GET ['msg_signature']; 			// 从URL上获取消息签名
		if (empty ( $postStr )) exit ( '' ); 				// 消息为空（微信误发或者三方恶意POST），直接退出
		
		$wechat = new WeactWechatOpen (); 					// 实例化微信开放平台类
		
		$decodeMsg = $wechat->decodeOpenMsg ( $postStr, $signature, $timestamp, $nonce, $msg_signature ); // 解密微信开放平台发来的消息
		
		$speedtime2 = microtime_double (); 					// 接口测速代码（记录解密消息的时间）
		
		//$handleresult = $wechat->handleAuthTicket ( $postStr, $signature, $timestamp, $nonce, $msg_signature ); // （原）处理开放平台发来的ticket
		
		$handleresult = ""; 								// 默认回复空字符串
		if (! empty ( $decodeMsg )) {
			$handleresult = $wechat->handleTicketEvent ( $decodeMsg ); // 处理开放平台发来的ticket或event
		}
		echo $handleresult;
		
		$speedtime3 = microtime_double (); 					// 接口测速代码（记录处理完ticket时间）
		
		// 记录接口信息
		$speedlist = array (
				0 => $speedtime1, 
				1 => $speedtime2, 
				2 => $speedtime3, 
		);
		$this->traceOpenHttp ( $postStr, $decodeMsg, $handleresult, $speedlist );
		
	}
	
	/**
	 * 追踪微信开放平台的http请求，ticket或者事件。
	 * @param xml $postStr 微信开放平台发来的消息包xml格式加密
	 * @param xml $decodeMsg 微信开放平台发来的消息包xml解密
	 * @param string $response 微动平台的回复，是空字符串还是SUCCESS
	 * @param array $speedlist 接口测速时间列表
	 * @return boolean true 追踪结果
	 */
	private function traceOpenHttp($postStr = NULL, $decodeMsg = NULL, $response = NULL, $speedlist = NULL) {
		// Step1：创建文件
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/auth/"; 		// 文件夹
		$filename = "receiveticketevent" . date ( 'Y-m-d' ) . ".log"; 					// 文件名按天存放
		if (! file_exists ( $filepath ) ) mkdirs ( $filepath ); 						// 如果没有存在文件夹，直接创建文件夹
		// Step2：获取请求身份
		$http_client_ip = $_SERVER ['REMOTE_ADDR']; 									// 客户端IP地址
		$query_string = $_SERVER ['QUERY_STRING']; 										// 获取请求参数
		$ip_judge = strpos ( $http_client_ip, "101.226" ) ? "WeiXin": "Unknown IP"; 	// 判断IP地址
		// Step3：组装请求信息
		$query_info = "---------------http request info---------------\n";
		$query_info .= "REMOTE_ADDR_IP : " . $http_client_ip . "; \n"; 					// 记录客户端的IP
		$query_info .= "FROM : " . $ip_judge . "; \n"; 									// 记录客户端身份
		$query_info .= "QUERY_STRING : " . $query_string . "; \n"; 						// 记录请求URL参数
		$msg_title = "---------------open wechat msg package info---------------\n";
		$decode_msg_title = "---------------open wechat msg decode info---------------\n";
		// Step4：计算测速时间
		$timespan1 = $speedlist [1] - $speedlist [0];
		$timespan2 = $speedlist [2] - $speedlist [1];
		// Step5：拼接接口测速信息
		$speedtitle = "---------------speed test info---------------\n";
		$speedtitle .= "INTERFACE_SPEED : \n";
		$speedinfo1 = "Step1: decode open msg info: " . $timespan1. " s; \n"; 			// 记录解析微信开放平台消息时间
		$speedinfo2 = "Step2: ticket or event to handle: " . $timespan2. " s; \n"; 		// 记录微动处理微信开放平台消息包的时间
		$interface_speed = $speedtitle . $speedinfo1 . $speedinfo2; 					// 接口测速信息叠加
		// Step6：拼接最后日志记录信息
		$finalwriteinfo = $query_info . $msg_title . $postStr . "\n" . $decode_msg_title . jsencode ( $decodeMsg ) . "\n" . $interface_speed . "\n"; // 加上接口测速信息
		// Step7：记录日志文件
		$fp = fopen ( $filepath . $filename, "a" ); 									// 打开文件获得读写所有权限
		flock ( $fp, LOCK_EX ); 														// 锁定日志文件
		fwrite ( $fp, strftime ( "%Y-%m-%d %H:%M:%S", time () ) . " 收到微信开放平台事件或ticket消息：\n" . $finalwriteinfo ); // 写入日志文件内容
		fwrite ( $fp, "---------------msg handle end---------------\n\n\n" ); 			// 消息处理结束
		flock ( $fp, LOCK_UN ); 														// 解锁日志文件
		fclose ( $fp ); 																// 释放文件解锁
		return true;
	}
	
}
?>