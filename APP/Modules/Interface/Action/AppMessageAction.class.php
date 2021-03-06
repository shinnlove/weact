<?php
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); 				// 载入微动微信APISDK类
/**
 * 平台接收APP发来信息。
 * @author 黄昀。
 * CreateTime:2015/03/16 22:08:30。
 */
class AppMessageAction extends PostInterfaceAction {
	/**
	 * APP消息接口。
	 */
	public function receiveAppMsg() {
		// 准备工作：有以下几种情况直接毙掉：
		if (empty ( $this->params ['appmsg_id'] )) {
			$this->ajaxresult ['errCode'] = 46100;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少本消息的主键编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['eid'] )) {
			$this->ajaxresult ['errCode'] = 46100;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少品牌商家编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['sid'] )) {
			$this->ajaxresult ['errCode'] = 46102;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少分店编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		if (empty ( $this->params ['from_guide'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息来自的导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		if (empty ( $this->params ['to_customer'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息要送达的顾客编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (! isset ( $this->params ['msg_type'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息类型！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['content'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息内容！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if ($this->params ['msg_type'] != 0 && empty ( $this->params ['mediapath'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少多媒体文件的路径！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 判断下该顾客是否选择当前导购，如果没有，则提示消息发不过去（不能骚扰不再选他做导购的顾客）
		$relationcheck = array (
				'e_id' => $this->params ['eid'], 				// 当前商家
				'subbranch_id' => $this->params ['sid'], 		// 当前分店
				'guide_id' => $this->params ['from_guide'], 	// 当前发送消息的导购
				'customer_id' => $this->params ['to_customer'], // 当前顾客
				'is_del' => 0 // 没有被删除的
		);
		$cinfo = M ( 'guide_wechat_customer_info' )->where ( $relationcheck )->order ( "choose_time desc" )->find (); // 找到要发送的顾客信息（这个错误很隐蔽！！！）
		if (! $cinfo) {
			$this->ajaxresult ['errCode'] = 10001; 				// 该顾客已经不再选择当前导购
			$this->ajaxresult ['errMsg'] = '消息发送失败，该顾客可能已经更换导购！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 顺带可能的格式化时间
		if (empty ( $this->params ['time'] )) {
			$this->params ['time'] = time (); // 如果没有时间，则以服务器收到消息的时间为准
		}
		if ($this->params ['msg_type'] < 0 || $this->params ['msg_type'] > 2) {
			$this->params ['msg_type'] = 0; // 特别注意：如果消息类型不对，则默认为文本，目前只支持3种：0文本，1图片，2语音，如果要扩展，这里的if范围要加大
		}
		
		// 实例化要操作的表
		$msginfotable = M ( 'guidechatmsginfo' ); // 导购顾客聊天消息与内容表
		
		// 首先：做消息的排重，不重复接收相同的消息
		$checkrepeat = array (
				'appmsg_id' => $this->params ['appmsg_id'], // 利用三方消息主键（必须唯一）
				'is_del' => 0
		);
		$existnum = $msginfotable->where ( $checkrepeat )->count (); // 检测该消息是否已经写入数据库
		if ($existnum) {
			// 如果数据库已经存在，代表服务器接收过了，可能是三方重发的消息，直接回复OK
			$this->ajaxresult ['errCode'] = 0; // 排重成功后，告知三方APP该条消息已经被接收过
			$this->ajaxresult ['errMsg'] = 'ok';
		} else {
			// 如果数据库没有存在记录，代表是新消息记录（或重发的新消息），服务器需要生成消息记录，并回复三方OK
			$domain = C ( 'DOMAIN' ); 	// 提取域名
			
			$newmsginfo = array (
					'chatrecord_id' => md5 ( uniqid ( rand (), true ) ), 	// 生成消息记录的主键
					'appmsg_id' => $this->params ['appmsg_id'], 			// 三方消息编号
					'belong_guide' => $this->params ['from_guide'], 		// 消息所属导购编号，2015/04/11 03:19:20添加该字段，用以方便搜索该导购的聊天记录
					'sender_id' => $this->params ['from_guide'], 			// 从哪个导购发来的
					'receiver_id' => $this->params ['to_customer'], 		// 消息发送给哪个顾客
					'chat_time' => $this->params ['time'], 					// 三方APP消息生成的时间（如果缺省则为微动服务器接收的时间）
					'receive_status' => 0, 									// 默认顾客未接收（经过微动发送给微信顾客接收后标志为1）
					'is_tocustomer' => 1, 									// 标志是导购发向顾客的消息
					'msg_type' => $this->params ['msg_type'], 				// 消息类型
					'msg_content' => $this->params ['content'] 				// 消息具体内容
			);
			if ($this->params ['msg_type'] == 1 || $this->params ['msg_type'] == 2) {
				// 如果该消息为图片或声音类型，必须再存储多媒体路径
				$mediapath = $this->params ['mediapath']; // 三方发来的多媒体路径（微动服务器端的多媒体）
				$newmsginfo ['media_path'] = str_replace ( $domain . __ROOT__, "", $mediapath ); // 剪切绝对的多媒体路径成相对路径，补充消息的media_path
			}
			
			$addchatmsgresult = $msginfotable->add ( $newmsginfo ); // 添加导购顾客聊天具体内容
			if ($addchatmsgresult) {
				/**
				 * ==========SECTION:进入小万消息发送到微信公众号消息流中==========
				 */
				$unreceivemsgmap = array (
						'sender_id' => $this->params ['from_guide'], 		// 当前导购发来的消息
						'receive_id' => $this->params ['to_customer'], 		// 当前顾客接收的消息
						'receive_status' => 0, 								// 未曾读过的消息
				);
				$receivemsgmap = array (
						'sender_id' => $this->params ['from_guide'], 		// 当前导购发来的消息
						'receive_id' => $this->params ['to_customer'], 		// 当前顾客接收的消息
						'receive_status' => 1, 								// 未曾读过的消息
				);
				$totalunreceivedmsg = $msginfotable->where ( $unreceivemsgmap )->count ();	//查找未读消息条数
				$msgaccount = $totalunreceivedmsg - 1;
				
				$sendflag = true; 		// 发送消息成功或者没有发消息，false只在发送失败的情况下
				$needsend = false; 		// 需要发送模板消息
				if ($msgaccount == 0) {
					// 说明本条消息为首次发送，取前一条已读消息
					$lastmsg = $msginfotable->where ( $receivemsgmap )->order ( 'chat_time desc' )->limit ( '0,1' )->select ();
					$lastreceivetime = $lastmsg [0] ['chat_time'];
					$timespan = time () - $lastreceivetime;	//获取上一条接受的消息距当前时间的时间差
					if ($timespan > 5 * 60) {	
						$needsend = true; // 如果大于5分钟，则发送模板消息，通知用户，需要发送模板消息
					}
				} else if ($msgaccount < 5) {
					// 有未读，取上一条***未读***消息，大于5分钟推送模板消息
					$newmsgmap = array (
							'sender_id' => $this->params ['from_guide'],
							'receive_id' => $this->params ['to_customer'],
							'receive_status' => 0	//未接受消息
					);
					$lastmsg = $msginfotable->where ( $unreceivemsgmap )->order ( 'chat_time desc' )->limit ( '1,1' )->select ();	//注意这里是limit(1,1)，即跳出上一条刚存的未读消息
					$lastunreceivetime =  $lastmsg [0] ['chat_time'];
					$timespan = time () - $lastunreceivetime;
					if ($timespan > 5 * 60) {
						$needsend = true; // 如果大于5分钟，则发送模板消息
					}
				} else { 
					if ($msgaccount % 5 == 0) {	
						$needsend = true; // 有大于等于5条，则条数是5的倍数则推送，需要发送模板消息
					}
				}
				
				// 如果需要发送，则发送模板消息
				if ($needsend) {
					// 查找企业信息
					$emap = array (
							'e_id' => $this->params ['eid'],
							'is_del' => 0,
					);
					$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); 						// 查询当前企业信息
					
					// 准备通知信息
					$msgnotify = "您有一条新消息，请注意查收。";
					if ($this->params ['msg_type'] == 0) {
						// 如果是文本，就把文本消息给它
						$mblen = mb_strlen ( $this->params ['content'] ); 							// 中文字符长度
						if ($mblen <= 100) {
							$msgnotify = mb_substr ( $this->params ['content'], 0, 50 ); 			// 如果是文本，长度一百内就附加消息
						} 
					} 
					
					// 发送微信模板消息通知
					$fontcolor = "#00C957"; 	// 群发消息是翠绿色的字体颜色
					$tpldata = array (
							'msg_notify' => $msgnotify, 											// 通知信息
							'msg_time' =>  date ( 'Y-m-d H:i:s' ),
							'msg_remark' => "您亲爱的导购给您发送了一条消息，请及时回复。",
					);
					$url = $domain . __ROOT__ . "/WeMall/GuideChat/onlineWebChat/sid/" . $this->params ['sid'] . "/eid/" . $this->params ['eid'] . "/gid/" . $this->params ['from_guide'] . "/cid/" . $this->params ['to_customer']; // 跳转的网页聊天窗地址
					// 策略模式发送下单微信模板消息
					$msgnotify = new APPMsgNotify ( $tpldata, $url, $fontcolor ); 					// 导购APP发来消息通知
					$msgwechater = new MsgToWechater ( $msgnotify ); 								// 上下文类对象
					$sendresult = $msgwechater->sendMsgToWechater ( $einfo, $cinfo ['openid'] ); 	// 发送模板消息
					if (! $sendresult) {
						$sendflag = false; // 如果没有发送成功，发送标识置为失败
					}
				}
				// $sendresult = $this->sendMessageToWechat ( $this->params ['eid'], $cinfo ['openid'], $this->params ['msg_type'], $this->params ['content'] ); // 调用微信服务新发送消息给顾客
				if ($sendflag) {	
					// 正常情况下(模板消息发送成功或不发送模板消息)
					//$newmsginfo ['receive_status'] = 1; // 微信发送成功，则顾客已经接收到
					//$updateread = $msginfotable->save ( $newmsginfo ); // 更新该条记录的接收时间
					$this->ajaxresult ['errCode'] = 0; // 告知三方APP接收消息OK
					$this->ajaxresult ['errMsg'] = 'ok';
				} else {
					//模板消息发送失败
					$this->ajaxresult ['errCode'] = 10001; // 事务提交后，告知三方APP接收消息OK
					$this->ajaxresult ['errMsg'] = '服务器忙，请稍后再发送！';
				}
			} else {
				$this->ajaxresult ['errCode'] = -1; // 告知三方接收消息失败
				$this->ajaxresult ['errMsg'] = '服务器忙，请稍后再发送！';
			}
		}
		
		// 不论服务器回复的是什么，最后再检查下有没有新消息，如果有，一并返回给三方
		$this->ajaxresult ['data'] ['newmsglist'] = $this->checkNewMessage ( $this->params ['from_guide'] ); // 检测该导购的新消息
		
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
	}
	
	/**
	 * 发送群组消息,参考发送文本消息接口(导购发送给顾客)，即上一个接口
	 */
	public function sendGroupMsg(){
		// 准备工作：有以下几种情况直接毙掉(跟上一个接口的主要区别是，不用传appmsg_id作为参数):
		if (empty ( $this->params ['eid'] )) {
			$this->ajaxresult ['errCode'] = 46100;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少品牌商家编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['sid'] )) {
			$this->ajaxresult ['errCode'] = 46102;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少分店编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		if (empty ( $this->params ['from_guide'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息来自的导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		} 
		if (empty ( $this->params ['to_group'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息要送达的顾客分组编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (! isset ( $this->params ['msg_type'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息类型！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['content'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息内容！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if ($this->params ['msg_type'] != 0 && empty ( $this->params ['mediapath'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少多媒体文件的路径！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 判断下该导购是否拥有这个分组，如果没有(可能已被删除)，则提示消息发不过去
		$relationcheck = array (
				'e_id' => $this->params ['eid'], // 当前商家
				'subbranch_id' => $this->params ['sid'], // 当前分店
				'guide_id' => $this->params ['from_guide'], // 当前发送消息的导购
				'group_id' => $this->params ['to_group'], // 当前顾客
				'is_del' => 0 // 没有被删除的
		);
		$cinfo = M ( 'guide_customer_group' )->where ( $relationcheck )->select (); // 找到要发送的分组信息,此处不仅用户判断，查询出的数据在下面也会用到
		if (! $cinfo) {
			$this->ajaxresult ['errCode'] = 10001; // 该顾客已经不再选择当前导购
			$this->ajaxresult ['errMsg'] = '消息发送失败，该分组可能已经不存在！';	//可能是删除了这个分组
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 顺带可能的格式化时间
		if (empty ( $this->params ['time'] )) {
			$this->params ['time'] = time (); // 如果没有时间，则以服务器收到消息的时间为准
		}
		if ($this->params ['msg_type'] < 0 || $this->params ['msg_type'] > 2) {
			$this->params ['msg_type'] = 0; // 特别注意：如果消息类型不对，则默认为文本，目前只支持3种：0文本，1图片，2语音，如果要扩展，这里的if范围要加大
		}
		
		// 如果数据库没有存在记录，代表是新消息记录（或重发的新消息），服务器需要生成消息记录，并回复三方OK
		for($i=0;$i<count($cinfo);$i++){
			$newmsginfo[$i] = array (
					'chatrecord_id' => md5 ( uniqid ( rand (), true ) ), // 生成消息记录的主键
					'appmsg_id' => md5 ( uniqid ( rand (), true ) ), // 三方消息编号
					'belong_guide' => $this->params ['from_guide'], // 消息所属导购编号，2015/04/11 03:19:20添加该字段，用以方便搜索该导购的聊天记录
					'sender_id' => $this->params ['from_guide'], // 从哪个导购发来的
					'receiver_id' => $cinfo [$i]['customer_id'], // 消息发送给哪个顾客
					'chat_time' => $this->params ['time'], // 三方APP消息生成的时间（如果缺省则为微动服务器接收的时间）
					'receive_status' => 0, // 默认顾客未接收（经过微动发送给微信顾客接收后标志为1）
					'is_tocustomer' => 1, // 标志是导购发向顾客的消息
					'msg_type' => $this->params ['msg_type'], // 消息类型
					'msg_content' => $this->params ['content'] // 消息具体内容
			);
			if ($this->params ['msg_type'] == 1 || $this->params ['msg_type'] == 2) {
				// 如果该消息为图片或声音类型，必须再存储多媒体路径
				$mediapath = $this->params ['mediapath']; // 三方发来的多媒体路径（微动服务器端的多媒体）
				$newmsginfo[$i] ['media_path'] = str_replace ( "http://www.we-act.cn/weact", "", $mediapath ); // 剪切绝对的多媒体路径成相对路径，补充消息的media_path
			}
		}
		// 实例化要操作的表
		$msginfotable = M ( 'guidechatmsginfo' ); // 导购顾客聊天消息与内容表
		$addchatmsgresult = $msginfotable->addAll ( $newmsginfo ); // 添加导购顾客聊天具体内容
		if ($addchatmsgresult) {
			$this->ajaxresult ['errCode'] = 0; // 告知三方APP接收消息OK
			$this->ajaxresult ['errMsg'] = 'ok';
			// 发送给微信用户（有聊天窗则取消微信消息流）
			for($i=0;$i<count($cinfo);$i++){
				$customer_id = $cinfo [$i]['customer_id'];
				$cusmap = array (
						'customer_id' => $customer_id, 
						'is_del' => 0,
				);
				$openid = M ( 'customerinfo' )->where ( $cusmap )->getField ( 'openid' );
				$this->sendMessageToWechat ( $this->params ['eid'], $openid, $this->params ['msg_type'], $this->params ['content'] );
			}
		} else {
			$this->ajaxresult ['errCode'] = -1; // 告知三方接收消息失败
			$this->ajaxresult ['errMsg'] = '服务器忙，请稍后再发送！';
		}
		// 不论服务器回复的是什么，最后再检查下有没有新消息，如果有，一并返回给三方
		$this->ajaxresult ['data'] ['newmsglist'] = $this->checkNewMessage ( $this->params ['from_guide'] ); // 检测该导购的新消息
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
	
	}
	
	/**
	 * 微动内部检查某商家下某导购的新消息的函数。
	 * @param string $guide_id 要查询新消息的导购编号
	 * @return array $newmsglist 某导购的未读新消息列表
	 */
	private function checkNewMessage($guide_id = '') {
		$newmsglist = array (); // 最终返回的新消息数组，可能为空，建议三方APP检查该数组的length
		if (! empty ( $guide_id )) {
			$msgtable = M ( 'guidechatmsginfo' ); // 实例化导购顾客聊天消息表对象
			$checkmsgmap = array (
					'receiver_id' => $guide_id, // 消息目标接收者是当前导购
					'receive_status' => 0, // 消息还没有被查看过
					'notify_count' => array ( "lt", 9 ), // 只通知三方APP次数小于等于8次的新消息，超过8次，默认为丢弃包
					'is_tocustomer' => 0, // 消息方向为：顾客发给导购（该字段：默认0代表顾客发送给导购，1代表导购发送给顾客）
					'is_del' => 0 // 没有被删除的消息
			);
			$msglist = $msgtable->where ( $checkmsgmap )->order ( 'chat_time asc' )->limit ( 20 )->select (); // 查询新消息，新来先查询，最多返回20条
			if ($msglist) {
				$newmsgcount = count ( $msglist ); // 统计新消息数目
				$newidlist = array (); // 新消息id列表
				for($i = 0; $i < $newmsgcount; $i ++) {
					// 组装基本信息
					$newmsglist [$i] = array (
							'msgid' => $msglist [$i] ['chatrecord_id'], // 新消息编号（需要三方确认的编号）
							'to_guide' => $guide_id, // 消息要送达的目标导购编号
							'from_customer' => $msglist [$i] ['sender_id'], // 从哪个顾客（微信用户）发来（是顾客编号，不是微信openid编号）
							'time' => $msglist [$i] ['chat_time'], // 消息时间
							'msg_type' => $msglist [$i] ['msg_type'], // 消息类型
							'content' => $msglist [$i] ['msg_content'] // 消息内容
					);
					// 组装媒体信息
					if ($msglist [$i] ['msg_type'] == 1 || $msglist [$i] ['msg_type'] == 2) {
						$newmsglist [$i] ['media_path'] = assemblepath ( $msglist [$i] ['media_path'], true ); // 如果是图片或语音消息则组装路径
						// 如果是图片，再同时返回一张缩略图
						if ($msglist [$i] ['msg_type'] == 1) {
							$relativepath = $msglist [$i] ['media_path']; // 不带weact的相对路径带文件名
							$pathlength = strlen ( $relativepath ); // 图片相对路径的长度
							$filenamespilt = strripos ( $relativepath, "/" ); // 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
							$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); // 抽出文件夹路径，注意：带斜杠
							$filename = substr ( $relativepath, $filenamespilt + 1 ); // 抽出文件名
							$thumbimagepath = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
							$newmsglist [$i] ['thumb_mediapath'] = assemblepath ( $thumbimagepath, true ); // 附加图片缩略小图绝对路径
						}
					}
					// 附带新消息通知三方的次数，方便三方判断网络环境
					$newmsglist [$i] ['notify_count'] = $msglist [$i] ['notify_count']; // 消息通知三方的次数
					array_push ( $newidlist, $msglist [$i] ['chatrecord_id'] ); // 将新消息主键压入主键数组中
				}
				// 新消息通知的次数加1
				$addnotifymap = array (
						'chatrecord_id' => array ( "in", join ( ",", $newidlist ) ),
						'is_del' => 0
				);
				$addnotifyresult = $msgtable->where ( $addnotifymap )->setInc ( 'notify_count' ); // 默认消息通知次数+1
			}
		}
		return $newmsglist;
	}
	
	/**
	 * 微动发送消息给顾客。
	 * @param string $e_id 商家编号
	 * @param string $openid 要接收消息的微信顾客openid
	 * @param number $msgtype 要发送的消息类型
	 * @param string $content 要发送的消息内容
	 * @return boolean $sendresult 消息是否成功送达微信用户
	 */
	private function sendMessageToWechat($e_id = '', $openid = '', $msgtype = 0, $content = '') {
		$sendresult = false;
		if (! empty ( $openid ) && ! empty ( $content )) {
			$wechatresult = array ();
			// 查询企业信息
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 找到企业信息
			
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
			switch($msgtype) {
				case 0:
					$wechatresult = $swc->sendText ( $einfo, $openid, $content ); // 发送文本消息给顾客
					break;
				case 1:
					$wechatresult = $swc->sendImage ( $einfo, $openid, $content ); // 发送图片消息给顾客，其中$content就是图片消息的mediaid
					break;
				case 2:
					$wechatresult = $swc->sendVoice ( $einfo, $openid, $content ); // 发送语音消息给顾客，其中$content就是语音消息的mediaid
					break;
				default:
					$wechatresult = $swc->sendText ( $einfo, $openid, $content ); // 发送文本消息给顾客
					break;
			}
			if ($wechatresult ['errcode'] == 0) {
				$sendresult = true; // 如果发送给微信用户成功，则发送消息标记成功
			}
		}
		return $sendresult;
	}
	
	/**
	 * 微动发送模板消息给顾客。
	 * @param string $e_id 商家编号
	 * @param string $openid 要接收消息的微信顾客openid
	 * @param number $msgtype 要发送的消息类型
	 * @param string $content 要发送的消息内容
	 * @return boolean $sendresult 消息是否成功送达微信用户
	 */
	private function sendTplMessageToCustomer($e_id = '', $customer_id='') {
		$template_id = 'bKr2d4QFGY7zJbahQO3K0-XUP27kZeS0z5armBHh1ts'; // 微信模板消息接口
		$emap = array (
				'e_id' => '201406261550250006', // 微动
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 企业信息
		// 查询openid
		$customerinfomap = array (
				'customer_id' => $customer_id,
				'is_del' => 0
		);
		$openid = M ( 'customerinfo' )->where ( $customerinfomap )->getField ( 'openid' );
		// $openid = 'oeovpt13JCmPNLaU6dTSh8mt68N4'; //wlk
		// $openid= 'oeovpty2ScWq6YXxuMG0hY5qHOGA'; //zcs
		$linkurl = 'http://192.168.0.42/wechat/chatindex.html';
		$tpldata = array (
				'first' => array (
						'value' => "",
						'color' => "#173177", 							// 模板消息字体的颜色
				), // 消息台头
				'keyword1' => array (
						'value' => "您有新的导购消息，请查收", 					// 消息发送人
						'color' => "#173177", 							// 模板消息字体的颜色
				), // 消息来自
				'keyword2' => array (
						'value' => timetodate ( time () ), 				// 消息发送时间
						'color' => "#173177", 							// 模板消息字体的颜色
				), // 发送时间
				'remark' => array (
						'value' => "亲爱的导购给您发送了一条消息，请及时回复导购。", 	// 模板消息备注
						'color' => "#173177", 							// 模板消息字体的颜色
				), // 备注
		
		);
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendTemplateMessage ( $einfo, $openid, $template_id, $linkurl, $tpldata ); // 设置企业模板消息模板
		p ( $sendresult ); die ();
		return $sendresult;
	}
	
	/**
	 * 三方APP查询服务器端有无新消息的接口。
	 * 每次查询新消息，微动总是选取最新未读的20条新消息，按时间升序排列，且最多只通知三方8次。
	 */
	public function queryNewMsg() {
		// 以下情况直接毙掉
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询新消息的导购编号！';
		} else {
			$this->ajaxresult ['data'] = array (
					'newmsglist' => $this->checkNewMessage ( $this->params ['gid'] ) // 检测该导购的新消息
			);
			$this->ajaxresult ['errCode'] = 0; // 查询消息接口成功
			$this->ajaxresult ['errMsg'] = "ok"; // 查询消息接口返回OK
		}
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
	}
	
	/**
	 * 三方APP查询某导购与某顾客的历史聊天记录接口。
	 */
	public function queryHistoryMsg() {
		// 以下几种情况直接毙掉
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询历史消息的导购编号！';
			exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
		}
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询历史消息的导购所对话顾客编号！';
			exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
		}
		
		// 处理没有传入next_start参数
		if (empty ( $this->params ['next_start'] ) ) {
			$this->params ['next_start'] = 0; // 如果不带下一条开始，默认从0开始查询历史消息
		}
		// 处理没有传入perpage参数
		$CONST_MAX_PER_PAGE = $this->params ['perpage']; // 每页读取多少条消息，一次查询多少条历史消息
		if (empty ( $CONST_MAX_PER_PAGE ) ) {
			// 如果没有每页读取多少条，默认读取50条消息
			$CONST_MAX_PER_PAGE = 50; 
		} else {
			// 如果有每页读取多少条，检测其数据合法性
			if ($CONST_MAX_PER_PAGE < 10) {
				$CONST_MAX_PER_PAGE = 10; // 小于一页10条默认一页10条
			} else if ($CONST_MAX_PER_PAGE > 100) {
				$CONST_MAX_PER_PAGE = 100; // 大于一页100条默认一页100条
			}
		}
		
		// 开始查询导购与该顾客的聊天记录并返回给三方APP
		$msgtable = M ( 'guidechatmsginfo' ); // 实例化表对象
		$historymsglist = array (); // 要回复三方APP的历史消息列表
		$nextstart = intval ( $this->params ['next_start'] ); // 下一条历史聊天记录开始的位置
		
		// 查询数据库现有消息记录条数
		$historymsgmap = array (
				'belong_guide' => $this->params ['gid'], // 所属导购的消息
				'_string' => "sender_id = '" . $this->params ['cid'] . "' OR receiver_id = '" . $this->params ['cid'] . "'", // 发送者是该顾客或者接收者是该顾客
				'is_del' => 0 // 没有被删除的
		);
		$historytotalcount = $msgtable->where ( $historymsgmap )->count (); // 统计导购与该顾客的历史聊天记录总数
		
		// 检测三方发来的下一页开始的记录是否合法
		if ($nextstart >= $historytotalcount) {
			$nextstart = $historytotalcount; // 如果发送过来的下一条请求超过数据库总记录数，直接将发过来的数量置为数据库最大
		}
		
		// 计算剩余要请求的消息条数
		$historymsgleft = $historytotalcount - $nextstart; // 计算剩下需要请求的历史消息数目，比如统计出100条历史消息，下一条开始历史消息下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// 处理不合理的计算结果
		if ($historymsgleft < 0) {
			$historymsgleft = 0; // 要调取的nextStart超过记录数，则将剩余消息处理为0条
		}
		
		$realGetNumber = ( $historymsgleft >= $CONST_MAX_PER_PAGE ) ? $CONST_MAX_PER_PAGE : $historymsgleft; // 剩下的历史消息还大于等于需要请求的历史消息数目吗，有的话，就满足需求，没有就按照剩下的数目请求
		
		$newNextStart = $nextstart + $realGetNumber; // 本次如果请求成功，下一次再请求历史消息开始的下标
		
		if($historymsgleft) {
			$querymsglist = $msgtable->where ( $historymsgmap )->order ( 'chat_time asc' )->limit ( $nextstart, $realGetNumber )->select (); // 默认一次性最多查询50条导购的聊天记录，按聊天时间降序排列
			$msgcount = count ( $querymsglist ); // 统计历史消息条数
			for($i = 0; $i < $msgcount; $i ++) {
				// 组装基本信息
				$historymsglist [$i] = array (
						'msgid' => $querymsglist [$i] ['chatrecord_id'], // 新消息编号（需要三方确认的编号）
						'msgfrom' => $querymsglist [$i] ['sender_id'], // 消息发送方编号
						'msgto' => $querymsglist [$i] ['receiver_id'], // 消息接收方编号
						'time' => $querymsglist [$i] ['chat_time'], // 消息时间
						'gtoc' => $querymsglist [$i] ['is_tocustomer'], // 消息方向，是否导购to顾客，是的话为1；否则为0，0代表顾客to导购
						'msg_type' => $querymsglist [$i] ['msg_type'], // 消息类型
						'content' => $querymsglist [$i] ['msg_content'] // 消息内容
				);
				// 组装媒体信息
				if ($querymsglist [$i] ['msg_type'] == 1 || $querymsglist [$i] ['msg_type'] == 2) {
					$historymsglist [$i] ['media_path'] = assemblepath ( $querymsglist [$i] ['media_path'], true ); // 如果是图片或语音消息则组装路径
					
					// 如果是图片，再同时返回一张缩略图
					if ($querymsglist [$i] ['msg_type'] == 1) {
						$relativepath = $querymsglist [$i] ['media_path']; // 不带weact的相对路径带文件名
						$pathlength = strlen ( $relativepath ); // 图片相对路径的长度
						$filenamespilt = strripos ( $relativepath, "/" ); // 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
						$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); // 抽出文件夹路径，注意：带斜杠
						$filename = substr ( $relativepath, $filenamespilt + 1 ); // 抽出文件名
						$thumbimagepath = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
						$historymsglist [$i] ['thumb_mediapath'] = assemblepath ( $thumbimagepath, true ); // 附加图片缩略小图绝对路径
					}
				}
				// 根据李楚阳的要求，方便APP消息判重，当消息从顾客发向导购，使用的是微动主键；当导购发向顾客，使用的是APP主键，方便判重
				if ($querymsglist [$i] ['is_tocustomer'] == 1) {
					$historymsglist [$i] ['msgid'] = $querymsglist [$i] ['appmsg_id']; // 如果是导购APP发来的历史消息，消息主键采用APP生成的方便判重
				} 
			}
		}
		
		// 将历史消息返回给三方APP
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] = array (
				'historymsg' => $historymsglist, // 本次的历史消息内容
				'next_start' => $newNextStart, // 下一次历史消息的起始点
				'total' => $historytotalcount // 总的顾客与导购聊天记录数，返回给三方方便统计与计算，灵活调取想要的消息记录
		); // 将查询到的$historymsglist返回给三方APP
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
	}
	
	/**
	 * APP接收消息的确认接口，三方接收到新消息后，必须回应微动服务器以确认接收到。
	 * 当没有收到微动服务器的回应，必须有一种机制，在30分钟内尝试8次重复确认接收。
	 */
	public function receiveMsgConfirm() {
		// 没有消息确认列表直接毙掉
		if (empty ( $this->params ['confirmlist'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要确认接收的新消息列表！';
			exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
		}
		// 处理接收
		$confirmlist = $this->params ['confirmlist']; // 提取出消息确认列表
		$confirmcount = count ( $confirmlist ); // 统计要确认的消息数
		$setconfirmlist = array (); // 要设置确认的数组
		for($i = 0; $i < $confirmcount; $i ++) {
			array_push ( $setconfirmlist, $confirmlist [$i] ['msgid'] ); // 将要确认的消息数组压栈
		}
		$setconfirmmap = array (
				'chatrecord_id' => array ( "in", join ( ",", $setconfirmlist ) ), // 拼接消息id
				'receive_status' => 0, // 未经过确认的消息
				'is_del' => 0
		);
		$setresult = M ( 'guidechatmsginfo' )->where ( $setconfirmmap )->setField ( 'receive_status', 1 ); // 将三方要确认接收的消息设置为已接收过
		if ($setresult) {
			$this->ajaxresult ['errCode'] = 0; // 处理成功，告知三方新消息确认接收已经收到
			$this->ajaxresult ['errMsg'] = "ok";
		}
		exit ( json_encode ( $this->ajaxresult ) ); // 返回给接口消息
	}
}
?>