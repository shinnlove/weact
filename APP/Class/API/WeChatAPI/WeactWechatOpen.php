<?php
import ( 'Class.API.WeChatAPI.WeactWechat', APP_PATH, '.php' ); 	// 载入微信SDK
import ( 'Class.API.DecodeAPI.wxBizMsgCrypt', APP_PATH, '.php' ); 	// 载入微信开放平台加解密SDK
/**
 * 微动微信开放平台类，
 * 负责对微信开放平台发来的消息进行处理，对微动数据库进行读写操作。
 * @author 万路康，赵臣升。
 * @package 赵臣升。
 */
class WeactWechatOpen {
	
	/**
	 * 当前微信open类属于哪个component_appid。
	 * @var string $component_appid
	 */
	private $component_appid = "";
	
	/**
	 * 当前开放平台处理类的组件信息
	 * @var array $componentinfo
	 */
	private $componentinfo = array ();
	
	/**
	 * 本类构造时传入的企业信息。
	 * @var array $einfo
	 */
	private $einfo = array ();
	
	/**
	 * 微动微信SDK类对象
	 * @var object $weactwechat
	 */
	private $weactwechat;
	
	/**
	 * 微动微信开放平台类的构造函数。
	 * @param string $appid
	 */
	public function __construct($einfo = NULL) {
		$this->einfo = $einfo;
		$this->weactwechat = new WeactWechat ( $einfo ); // 新建一个weactwechat类对象
		$this->componentinfo = $this->getComponentInfoByPriority ();
	}
	
	/**
	 * 通过组件的appid获取组件信息。
	 * @param string $appid 组件的appid
	 * @return array $componentinfo 组件的componentinfo
	 */
	public function getComponentInfoById($component_appid = '') {
		$componentmap = array (
				'component_appid' => $component_appid, // 读取优先级为0的组件
				'is_del' => 0,
		);
		$componentinfo = M ( 'componentinfo' )->where ( $componentmap )->find (); // 从数据库中查到组件信息
		return $componentinfo;
	}
	
	/**
	 * 通过组件优先级获取组件信息。
	 */
	public function getComponentInfoByPriority() {
		$componentinfo = array (); // 组件信息
		$componentTable = M ( 'componentinfo' ); // 实例化组件表
		$componentmap = array (
				'component_priority' => C ( 'CURRENT_COMPONENT_PRIORITY' ), // 当前配置文件中使用的component组件优先级
				'is_del' => 0, // 没有被删除的
		);
		$componentinfo = $componentTable->where ( $componentmap )->find (); // 取出组件信息
		if (! $componentinfo) {
			$componentinfo = array ();
		}
		return $componentinfo;
	}
	
	/**
	 * 删除授权信息
	 * @param $authorizerappid 授权方的appid
	 */
	public function deleteAuthData($authorizer_appid = '') {
		$deleteresult = false; // 没有被删除
		if (! empty ( $authorizer_appid )) {
			// 依次修改einfo表和删除authorizerinfo表
			$authTable = M ( 'authorizerinfo' );
			$eTable = M ( 'enterpriseinfo' );
			
			$authorizermap = array (
					'authorizer_appid' => $authorizer_appid,
					'is_del' => 0
			);
			$authorizerinfo = $authTable->where ( $authorizermap )->find ();
			if ($authorizerinfo) {
				$authTable->startTrans ();
				//将authorither表中对应的eid记录is_del置为1
				$authdeldata = array (
						'is_del' => 1
				);
				$authdelresult = $authTable->where ( $authorizermap )->save ( $authdeldata );
				//将einfo表中对应的e_id记录的is_auth置为0
				$emap = array (
						'e_id' => $authorizerinfo ['e_id'],
						'is_auth' => 1,
						'is_del' => 0
				);
				$esavedata = array (
						'is_auth' => 0
				);
				$enterresult = $eTable->where ( $emap )->save ( $esavedata );
				
				if ($authdelresult && $enterresult) {
					$authTable->commit ();
					$deleteresult = true; // 解绑成功
				} else {
					$authTable->rollback ();
				}
			}
		}
		return $deleteresult;
	}
	
	/**
	 * ==========对外接口函数==========
	 */
	
	/**
	 * 解密微信开放平台发来的消息。
	 * @param xml $postStr 微信发来的ticket消息xml（加密）
	 * @param string $signature 微信签名
	 * @param string $timestamp 签名时间戳
	 * @param string $nonce 签名随机数
	 * @param string $msg_signature 消息签名
	 */
	public function decodeOpenMsg($postStr = NULL, $signature = '', $timestamp = '', $nonce = '', $msg_signature = '') {
		$decodemsg = array (); // 默认解密后的消息是空数组
		if (! empty ( $postStr ) && ! empty ( $signature ) && ! empty ( $timestamp ) && ! empty ( $nonce ) && ! empty ( $msg_signature )) {
			// 转换xml抽取appid
			$receiveinfo = $this->extractXml ( $postStr ); // 将收到的xml转成数组
			$appid = $receiveinfo ['AppId']; // 取出appid
			
			// 在微动平台查找收到ticket信息的组件
			$componentmap = array (
					'component_appid' => $appid,
					'is_del' => 0,
			);
			$componentinfo = M ( 'componentinfo' )->where ( $componentmap )->find ();
			if ($componentinfo) {
				$component_token = $componentinfo ['component_token']; 		// 取出组件token
				$encodingAesKey = $componentinfo ['component_aeskey']; 		// 取出组件aeskey
				
				//解密密文
				$msgcrypt = new WXBizMsgCrypt ( $component_token, $encodingAesKey, $appid );
				$trymsg = '';		//明文变量
				$errCode = $msgcrypt->decryptMsg ( $msg_signature, $timestamp, $nonce, $postStr, $trymsg );
				if ($errCode == 0) {
					$decodemsg = $this->extractXml ( $trymsg );
				}
			}
		}
		return $decodemsg;
	}
	
	/**
	 * 处理微信开放平台发来的
	 * @param string $ticketeventinfo
	 */
	public function handleTicketEvent($ticketeventinfo = NULL) {
		$handleresponse = ""; // 默认输出空消息
		$componentTable = M ( 'componentinfo' ); // 组件信息表
		$appid = $ticketeventinfo ['AppId']; // 取出appid
			
		// 在微动平台查找收到ticket信息的组件
		$componentmap = array (
				'component_appid' => $appid,
				'is_del' => 0,
		);
		$componentinfo = $componentTable->where ( $componentmap )->find ();
		if (! $componentinfo) {
			return $handleresponse; // 无效组件，直接将空消息输出
		}
		
		$component_secret = $componentinfo ['component_secret']; 	// 取出组件secret
		
		// 处理数据：根据infotype类型来判断是推送ticket还是取消授权
		$handleresponse = "SUCCESS"; // 有信息，不论是事件还是信息，都回复给微信SUCCESS
		if ($ticketeventinfo ['InfoType'] == 'unauthorized') {
			$authorizerappid = $ticketeventinfo ['AuthorizerAppid']; // 取出取消授权的AuthorizerAppid
			$this->deleteAuthData ( $authorizerappid );
		} else {
			// 否则的InfoType == 'component_verify_ticket'
			$componentVerifyTicket = $ticketeventinfo ['ComponentVerifyTicket']; // 直接取对应的ComponentVerifyTicket
			if (! empty ( $componentVerifyTicket )) {
				// 更新组件的ticket和access_token信息
				$currentTime = time (); // 取当前时间
				$componentAccessToken = $this->weactwechat->getNewComponentAccessToken ( $appid, $component_secret, $componentVerifyTicket ); // 重新获取组件的access_token
				// 需要更新组件的信息
				$updateinfo = array (
						'component_verify_ticket' => $componentVerifyTicket, // 新收到的ticket
						'component_verify_ticket_time' => $currentTime,
						'component_access_token' => $componentAccessToken, // 新的组件token
						'component_access_token_time' => $currentTime, // 刷新组件token时间
						'latest_modify' => $currentTime, // 记录最后一次修改时间
				);
				$updateresult = $componentTable->where ( $componentmap )->save ( $updateinfo ); // 将组件信息保存回去
				if (! $updateresult) {
					$handleresponse = ""; // 保存不成功直接将空消息输出
				}
			}
		}
		return $handleresponse;
	}
	
	/**
	 * （原）接收微信服务器每10分钟推送的ticket，消息中包含组件的appid可以进行分别处理（因为记录日志信息不方便现已不用）。
	 * @param xml $postStr 微信发来的ticket消息xml（加密）
	 * @param string $signature 微信签名
	 * @param string $timestamp 签名时间戳
	 * @param string $nonce 签名随机数
	 * @param string $msg_signature 消息签名
	 */
	public function handleAuthTicket($postStr = NULL, $signature = '', $timestamp = '', $nonce = '', $msg_signature = '') {
		$handleresponse = ""; // 最终回复微信开放平台的信息
		if (! empty ( $postStr ) && ! empty ( $signature ) && ! empty ( $timestamp ) && ! empty ( $nonce ) && ! empty ( $msg_signature )) {
			$componentTable = M ( 'componentinfo' ); // 组件信息表
			
			// 转换xml抽取appid
			$receiveinfo = $this->extractXml ( $postStr ); // 将收到的xml转成数组
			$appid = $receiveinfo ['AppId']; // 取出appid
			
			// 在微动平台查找收到ticket信息的组件
			$componentmap = array (
					'component_appid' => $appid,
					'is_del' => 0,
			);
			$componentinfo = $componentTable->where ( $componentmap )->find ();
			if (! $componentinfo) {
				return $handleresponse; // 无效组件，直接将空消息输出
			}
			
			$component_token = $componentinfo ['component_token']; 		// 取出组件token
			$component_secret = $componentinfo ['component_secret']; 	// 取出组件secret
			$encodingAesKey = $componentinfo ['component_aeskey']; 		// 取出组件aeskey
			
			//解密密文
			$msgcrypt = new WXBizMsgCrypt ( $component_token, $encodingAesKey, $appid );
			$msg = '';		//明文变量
			$errCode = $msgcrypt->decryptMsg ( $msg_signature, $timestamp, $nonce, $postStr, $msg );
			$data = $this->extractXml ( $msg );
			
			// 处理数据
			if (! empty ( $data )) {
				// 根据infotype类型来判断是推送ticket还是取消授权
				$handleresponse = "SUCCESS"; // 有信息，不论是事件还是信息，都回复给微信SUCCESS
				if ($data ['InfoType'] == 'unauthorized') {
					$authorizerappid = $data ['AuthorizerAppid']; // 取出取消授权的AuthorizerAppid
					$this->deleteAuthData ( $authorizerappid );
				} else {
					// 否则的InfoType == 'component_verify_ticket'
					$componentVerifyTicket = $data ['ComponentVerifyTicket']; // 直接取对应的ComponentVerifyTicket
					if (! empty ( $componentVerifyTicket )) {
						// 更新组件的ticket和access_token信息
						$currentTime = time (); // 取当前时间
						$componentAccessToken = $this->weactwechat->getNewComponentAccessToken ( $appid, $component_secret, $componentVerifyTicket ); // 重新获取组件的access_token
						// 需要更新组件的信息
						$updateinfo = array (
								'component_verify_ticket' => $componentVerifyTicket, // 新收到的ticket
								'component_verify_ticket_time' => $currentTime,
								'component_access_token' => $componentAccessToken, // 新的组件token
								'component_access_token_time' => $currentTime, // 刷新组件token时间
								'latest_modify' => $currentTime, // 记录最后一次修改时间
						);
						$updateresult = $componentTable->where ( $componentmap )->save ( $updateinfo ); // 将组件信息保存回去
						if (! $updateresult) {
							$handleresponse = ""; // 保存不成功直接将空消息输出
						}
					}
				}
			} 
		} 
		return $handleresponse; // 返回消息
	}
	
	/**
	 * 解密微信开放平台发来的消息。
	 * @param string $postStr
	 * @param string $signature
	 * @param string $timestamp
	 * @param string $nonce
	 * @param string $msg_signature
	 * @return string $decodemsg 解密后的XML文档消息
	 */
	public function openMsgDecode($postStr = NULL, $signature = '', $timestamp = '', $nonce = '', $msg_signature = '') {
		$component_token = $this->componentinfo ['component_token']; // 取出组件token
		$encodingAesKey = $this->componentinfo ['component_aeskey']; // 取出组件加密aeskey
		$component_appid = $this->componentinfo ['component_appid']; // 取出组件appid
		$decodedmsg = ""; // 被解密后的消息
		if (! empty ( $postStr ) && ! empty ( $signature ) && ! empty ( $timestamp ) && ! empty ( $nonce ) && ! empty ( $msg_signature )) {
			$msgcrypt = new WXBizMsgCrypt ( $component_token, $encodingAesKey, $component_appid ); // 以微信开放平台的方式解密密文
			$errCode = $msgcrypt->decryptMsg ( $msg_signature, $timestamp, $nonce, $postStr, $decodedmsg );
			if ($errCode != 0) {
				$decodedmsg = ""; // 解密出错返回空字符串
			}
		}
		return $decodedmsg;
	}
	
	/**
	 * 解析微信数据。
	 * @param array $msgdata 微信二维数组数据
	 */
	public function requestOpen($msgdata = NULL) {
		$this->weactwechat->requestOpen ( $msgdata ); 
	}
	
	/**
	 * 回复微信。
	 * @param array $content 要回复的内容
	 * @param string $type 回复的类型
	 * @param number $flag 是否新消息标记
	 */
	public function response($content = NULL, $type = 'text', $flag = 0) {
		$response = $this->weactwechat->response ( $content, $type, $flag ); // 回复微信消息。
		$finalresponse = "";
		
		// 开放平台回复模式
		$component_appid = $this->componentinfo ['component_appid'];
		$component_token = $this->componentinfo ['component_token'];
		$encodingAesKey = $this->componentinfo ['component_aeskey'];
		$timestamp = $_GET ['timestamp'];
		$nonce = $_GET ['nonce'];
		$openresponse = "";
		$msgcrypt = new WXBizMsgCrypt ( $component_token, $encodingAesKey, $component_appid );
		$errCode = $msgcrypt->encryptMsg ( $response, $timestamp, $nonce, $openresponse );
		if ($errCode == 0) {
			$finalresponse = $openresponse; // 开放平台回包加密成功
		} else {
			$finalresponse = ""; // 开放平台回包加密出错
		}
		return $finalresponse;
	}
	
	/**
	 * 发送开放平台消息。
	 * @param array $content 要发送的内容
	 * @param string $openid 要发送给的用户
	 * @param string $appid 使用的组件appid
	 * @param string $query_auth_code
	 * @param string $msgtype
	 */
	public function sendOpenMsg($content = NULL, $openid = '', $appid = '', $query_auth_code = '', $msgtype = 'text') {
		$this->weactwechat->sendOpenMsg ( $content, $openid, $appid, $query_auth_code, $msgtype ); // 发送开放平台消息
	}
	
	/**
	 * ==========微信开放平台接口区域==========
	 */
	
	/**
	 * 本函数只被getComonentAccessToken调用来更新本地组件的最新component_access_token信息。
	 * @param string $component_appid
	 * @param string $component_access_token
	 * @return boolean $saveresult 更新结果，若成功返回true，否则返回false
	 */
	private function updateComponentAccessToken($component_appid = '', $component_access_token = '') {
		$componentmap = array (
				'component_appid' => $component_appid, // 要保存的组件appid
				'is_del' => 0
		);
		$savedata = array (
				'component_access_token' => $component_access_token,
				'component_access_token_time' => time (), // 取当前时间
		);
		$saveresult = M ( 'componentinfo' )->where ( $componentmap )->save ( $savedata ); // 保存component信息
		return $saveresult;
	}
	
	/**
	 * 获取组件可用access_token，如果超时会自动重新获取，并将最新token存入数据库。
	 * @param string $component_appid 要得到access_token的组件appid
	 * @return string $access_token 组件的可用access_token
	 */
	private function getComonentAccessToken($component_appid = '') {
		$access_token = "";
	
		$tokenavailable = false; // token不可用
	
		$componentmap = array (
				'component_appid' => $component_appid, // 要获取token的组件appid
				'is_del' => 0,
		);
		$componentinfo = M ( 'componentinfo' )->where ( $componentmap )->find (); // 寻找组件信息
		if ($componentinfo) {
			if (! empty ( $componentinfo ['component_access_token'] ) && (time () - $componentinfo ['component_access_token_time'] < 7000)) {
				$access_token = $componentinfo ['component_access_token']; // 组件token不空（容错）并且不超时情况下，component_access_token还可用
			} else {
				// 组件token空或者超时，则重新获取
				$component_secret = $componentinfo ['component_secret']; // 取出secret
				$component_ticket = $componentinfo ['component_verify_ticket']; // 取出ticket
				$component_access_token = $this->weactwechat->getNewComponentAccessToken ( $component_appid, $component_secret, $component_ticket ); // 重新获取access_token
				$saveresult = $this->updateComponentAccessToken ( $component_appid, $component_access_token );
				if ($saveresult) {
					$access_token = $component_access_token; // 使用新的组件access_token
				}
			}
		}
		return $access_token;
	}
	
	/**
	 * 对外接口一：在企业管理后台为某个组件生成pre_auth_code参数供页面传参跳转授权。
	 * @param string $component_appid 要生成pre_auth_code的组件appid
	 * @return string $pre_auth_code 为该组件生成的pre_auth_code
	 */
	public function generatePreAuthCode($component_appid = '') {
		if (empty ( $component_appid )) {
			$component_appid = $this->componentinfo ['component_appid'];
		}
		$component_access_token = $this->getComonentAccessToken ( $component_appid ); // 先获取组件的token
		$pre_auth_code = $this->weactwechat->getPreAuthCode ( $component_appid, $component_access_token );
		return $pre_auth_code;
	}
	
	/**
	 * 由authCodeForAuthorizerInfo调用，通过预授权码接口获取了授权者信息后，对本地进行插入保存或更新。
	 * @param string $component_appid 组件的appid
	 * @param string $authorizerinfo 需要更新的授权者信息。
	 * @return boolean $recordresult 插入本地授权者信息结果
	 */
	private function updateAuthorizerInfo($component_appid = '', $authorizerinfo = NULL) {
		$updateresult = false; // 默认没有更新成功
		
		$authorizerTable = M ( 'authorizerinfo' ); // 实例化授权者表
		$einfoTable = M ( 'enterpriseinfo' ); // 企业信息表
		
		// 从接口结果$authorizerinfo中取出信息
		$authorizer_appid = $authorizerinfo ['authorization_info'] ['authorizer_appid'];
		$authorizer_access_token = $authorizerinfo ['authorization_info'] ['authorizer_access_token'];
		$authorizer_refresh_token = $authorizerinfo ['authorization_info'] ['authorizer_refresh_token'];
		
		$component_access_token = $this->getComonentAccessToken ( $component_appid ); // 获取组件access_token
		$authorizer_accountinfo = $this->weactwechat->getAuthorizerAccountInfo ( $component_appid, $component_access_token, $authorizer_appid ); // 调取授权人账户信息
		
		$nick_name = $authorizer_accountinfo ['authorizer_info'] ['nick_name'];
		$head_img = $authorizer_accountinfo ['authorizer_info'] ['head_img'];
		$service_type_info = $authorizer_accountinfo ['authorizer_info'] ['service_type_info'] ['id'];
		$verify_type_info = $authorizer_accountinfo ['authorizer_info'] ['verify_type_info'] ['id'];
		$username = $authorizer_accountinfo ['authorizer_info'] ['user_name'];
		$alias = $authorizer_accountinfo ['authorizer_info'] ['alias'];
		$qrcode = $authorizer_accountinfo ['authorizer_info'] ['qrcode_url'];
		$currentTime = time ();
		
		// 组装授权者信息准备存入表
		$authorizerData = array (
				'authorizer_info_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $this->einfo ['e_id'],
				'authorized_component_appid' => $component_appid, 		// 授权给了哪个组件
				'authorizer_appid' => $authorizer_appid, 				// 授权方的appid
				'authorizer_access_token' => $authorizer_access_token,
				'expires_in' => 7200,
				'authorizer_access_token_time' => $currentTime,
				'authorizer_refresh_token' => $authorizer_refresh_token,
				'user_name' => $username,
				'nick_name' => $nick_name,
				'head_img' => $head_img,
				'service_type_info' => $service_type_info,
				'verify_type_info' => $verify_type_info,
				'alias' => $alias,
				'qrcode' => $qrcode,
				'add_time' => $currentTime,
		);
		
		// 检测该授权者有没有授权过
		$existmap = array (
				'authorizer_appid' => $authorizer_appid, 				// 授权方的appid
				'is_del' => 0,
		);
		$existinfo = $authorizerTable->where ( $existmap )->find (); 	// 尝试找到授权者信息
		if ($existinfo) {
			// 如果表里有这个授权者信息，更新
			$authorizerData ['authorizer_info_id'] = $existinfo ['authorizer_info_id']; // 使用原来的主键
			$updateresult = $authorizerTable->save ( $authorizerData ); // 带主键的更新进去
		} else {
			// 如果表里没有这个授权者信息，插入
			$updateresult = $authorizerTable->add ( $authorizerData ); 	// 插入进去
		}
		// 如果保存成功
		if ($updateresult) {
			// 企业表信息
			$emap = array (
					'e_id' => $this->einfo ['e_id'],
					'is_del' => 0,
			);
			$updateresult = $einfoTable->where ( $emap )->setField ( 'is_auth', 1 ); // 保存企业授权开启
		}
		return $updateresult;
	}
	
	/**
	 * 对外接口二：授权者允许授权，得到其授权码，通过授权码获取授权者信息，仅该接口可以获取授权者的refresh_token，注意保存。
	 * @param string $query_auth_code 组件预授权码
	 */
	public function authCodeForAuthorizerInfo($query_auth_code = '') {
		$component_appid = $this->componentinfo ['component_appid']; // 直接使用默认component_appid
		$component_access_token = $this->getComonentAccessToken ( $component_appid ); // 先获取组件的token
		$authorizerinfo = $this->weactwechat->getAuthorizerInfo ( $component_appid, $component_access_token, $query_auth_code ); // 再获取授权者信息
		if (! empty ( $authorizerinfo ['authorization_info'] ) && $authorizerinfo ['authorization_info'] ['authorizer_appid'] != "wx570bc396a51b8ff8") {
			$recordresult = $this->updateAuthorizerInfo ( $component_appid, $authorizerinfo ); // 非全网发布测试公众号，则更新微动授权者信息（wx570bc396a51b8ff8这个公众号为全网测试公众号）
		}
		return $authorizerinfo;
	}
	
	/**
	 * 仅被getAuthorizerAccessTokenById调用更新授权方微动平台的token值。
	 * @param string $authorizer_appid 授权方的appid
	 * @param string $authorizer_access_token 新获得的access_token
	 */
	private function updateAuthorizerAccessToken($authorizer_appid = '', $authorizer_access_token = '') {
		$authorizermap = array (
				'authorizer_appid' => $authorizer_appid, // 要更新的授权方appid
				'is_del' => 0,
		);
		$updatetokeninfo = array (
				'authorizer_access_token' => $authorizer_access_token, // 最新的token
				'authorizer_access_token_time' => time (), // 取当前时间作为获取时间
		);
		$updateresult = M ( 'authorizerinfo' )->where ( $authorizermap )->save ( $updatetokeninfo ); // 更新本地授权者信息表
		return $updateresult;
	}
	
	/**
	 * 对外接口三：根据企业编号来获取授权者access_token。
	 * @param string $einfo 企业信息
	 * @property string e_id 企业编号
	 * @return string $authorizer_access_token 授权企业的access_token
	 */
	public function getAuthorizerAccessTokenById($einfo = NULL) {
		$authorizer_access_token = ""; // 要获取的授权者信息
		if (! empty ( $einfo ['e_id'] )) {
			$authorizertokenmap = array (
					'e_id' => $einfo ['e_id'], // 企业编号
					'is_del' => 0,
			);
			$authorizerinfo = M ( 'authorizerinfo' )->where ( $authorizertokenmap )->find (); // 在微动本地查询授权者信息
			if ($authorizerinfo && ! empty ( $authorizerinfo ['authorizer_refresh_token'] )) {
				// 存在这样的授权者信息，并且authorizer_refresh_token（唯一）也是正确存在的

				$tokenavailable = false; 		// 默认token不可用
				if (! empty ( $authorizerinfo ['authorizer_access_token'] ) && (time () - $authorizerinfo ['authorizer_access_token_time'] < 7000)) {
					$tokenavailable = true; 	// 如果token不空，并且在可用时间内，则这个token可用
					$authorizer_access_token = $authorizerinfo ['authorizer_access_token']; // 给到这个token
				}

				// 如果authorizer_access_token不可用，则重新换取一次authorizer_access_token
				if (! $tokenavailable) {
					$component_appid = $authorizerinfo ['authorized_component_appid']; 				// 取出该授权者授权给了哪个component
					$component_access_token = $this->getComonentAccessToken ( $component_appid ); 	// 得到这个组件的component_access_token
					$authorizer_appid = $authorizerinfo ['authorizer_appid']; 						// 取出授权者appid
					$authorizer_refresh_token = $authorizerinfo ['authorizer_refresh_token']; 		// 取出授权者的refresh_token
					$new_access_token = $this->weactwechat->getNewAuthorizerToken ( $component_appid, $component_access_token, $authorizer_appid, $authorizer_refresh_token ); // 重新获取authorizer_access_token 
					if (! empty ( $new_access_token )) {
						// 如果获取到了token，则更新到本地数据库
						$updateresult = $this->updateAuthorizerAccessToken ( $authorizer_appid, $new_access_token ); // 将最新的authorizer_access_token更新到本地数据库
						if ($updateresult) {
							$authorizer_access_token = $new_access_token; // 给到这个token
						}
					}
				}
			}
		}
		return $authorizer_access_token;
	}
	
	/**
	 * 对外接口四：输出当前使用的component_appid
	 * @return string $component_appid
	 */
	public function getCurrentComponentAppId() {
		return $this->componentinfo ['component_appid']; 
	}
	
	/**
	 * ==========工具函数区域==========
	 */
	
	/**
	 * 工具函数：XML文档解析成数组，并将键值转成小写
	 * @param xml $xml 要转换的xml的数组
	 * @return array $data 转换后的二维数组
	 */
	public function extractXml($xml) {
		$data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		//return array_change_key_case($data, CASE_LOWER);
		return $data;
	}
	
}
?>