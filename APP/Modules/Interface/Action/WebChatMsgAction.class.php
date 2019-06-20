<?php
/**
 * 网页端聊天消息接收控制器。
 * @author 赵臣升
 * CreateTime:2015/07/08 14:48:36.
 */
class WebChatMsgAction extends WebChatPostAction {
	/**
	 * WebChat消息接口。
	 */
	public function receiveWebMsg() {
		// 准备工作：有以下几种情况直接毙掉：
		if (empty ( $this->params ['webmsg_id'] )) {
			$this->ajaxresult ['errCode'] = 46100;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少网页聊天消息的主键编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (empty ( $this->params ['eid'] )) {
			$this->ajaxresult ['errCode'] = 46100;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少品牌商家编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (empty ( $this->params ['from_customer'] )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息来自的顾客编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (empty ( $this->params ['to_guide'] )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息要送达的导购编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (! isset ( $this->params ['msg_type'] )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息类型！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (empty ( $this->params ['content'] )) {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息内容！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if ($this->params ['msg_type'] != 0 && empty ( $this->params ['mediapath'] )) {
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少多媒体文件的路径！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		
		// 判断下该顾客是否选择当前导购，如果没有，则提示消息发不过去（不能骚扰不再选他做导购的顾客）
		$relationcheck = array (
				'e_id' => $this->params ['eid'], // 当前商家
				'guide_id' => $this->params ['to_guide'], // 当前发送消息的导购
				'customer_id' => $this->params ['from_customer'], // 当前顾客
				'is_del' => 0 // 没有被删除的
		);
		$cinfo = M ( 'guide_wechat_customer_info' )->where ( $relationcheck )->find (); // 找到要发送的顾客信息（这个错误很隐蔽！！！）
		if (! $cinfo) {
			$this->ajaxresult ['errCode'] = 10001; // 该顾客已经不再选择当前导购
			$this->ajaxresult ['errMsg'] = '消息发送失败，该顾客可能已经更换导购！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 顺带可能的格式化时间
		if (empty ( $this->params ['time'] )) {
			$this->params ['time'] = time (); // 如果没有时间，则以服务器收到消息的时间为准
		}
		if ($this->params ['msg_type'] < 0 || $this->params ['msg_type'] > 5) {
			$this->params ['msg_type'] = 0; // 特别注意：如果消息类型不对，则默认为文本，目前只支持3种：0文本，1图片，2语音，3视频（短视频），4音乐，5商品图文详情，如果还要扩展，这里的if范围要加大
		}
		
		// 实例化要操作的表
		$msginfotable = M ( 'guidechatmsginfo' ); // 导购顾客聊天消息与内容表
		
		// 首先：做消息的排重，不重复接收相同的消息
		$checkrepeat = array (
				'appmsg_id' => $this->params ['webmsg_id'], // 利用网页聊天消息主键（必须唯一）
				'is_del' => 0
		);
		$existnum = $msginfotable->where ( $checkrepeat )->count (); // 检测该消息是否已经写入数据库
		if ($existnum) {
			// 如果数据库已经存在，代表服务器接收过了，可能是网页端重发的消息，直接回复OK
			$this->ajaxresult ['errCode'] = 0; // 排重成功后，告知网页端该条消息已经被接收过
			$this->ajaxresult ['errMsg'] = 'ok';
		} else {
			// 如果数据库没有存在记录，代表是新消息记录（或重发的新消息），服务器需要生成消息记录，并回复网页端OK
			
			$newmsginfo = array (
					'chatrecord_id' => md5 ( uniqid ( rand (), true ) ), // 生成消息记录的主键
					'appmsg_id' => $this->params ['webmsg_id'], // 网页端消息编号
					'belong_guide' => $this->params ['to_guide'], // 消息所属导购编号，2015/04/11 03:19:20添加该字段，用以方便搜索该导购的聊天记录
					'sender_id' => $this->params ['from_customer'], // 从哪个顾客发来的
					'receiver_id' => $this->params ['to_guide'], // 消息发送给哪个导购
					'chat_time' => $this->params ['time'], // 网页端消息生成的时间（如果缺省则为微动服务器接收的时间）
					'receive_status' => 0, // 默认导购未接收（经过导购在APP端查询消息接收后标志为1）
					'is_tocustomer' => 0, // 标志是顾客发向导购的消息
					'msg_type' => $this->params ['msg_type'], // 消息类型
					'msg_content' => $this->params ['content'] // 消息具体内容
			);
			if ($this->params ['msg_type'] == 1 || $this->params ['msg_type'] == 2) {
				// 如果该消息为图片或声音类型，必须再存储多媒体路径
				$mediapath = $this->params ['mediapath']; // 三方发来的多媒体路径（微动服务器端的多媒体）
				$newmsginfo ['media_path'] = str_replace ( "http://www.we-act.cn", "", $mediapath ); // 剪切绝对的多媒体路径成相对路径，补充消息的media_path
			}
			
			$addchatmsgresult = $msginfotable->add ( $newmsginfo ); // 添加导购顾客聊天具体内容
			if ($addchatmsgresult) {
				$this->ajaxresult ['errCode'] = 0; // 接收并处理网页端消息成功
				$this->ajaxresult ['errMsg'] = 'ok';
			} else {
				$this->ajaxresult ['errCode'] = -1; // 接收并处理网页端消息失败
				$this->ajaxresult ['errMsg'] = '服务器忙，请稍后再发送！';
			}
		}
		
		// 不论服务器回复的是什么，最后再检查下有没有APP这边过来的新消息给网页端，如果有，一并返回给网页端
		$this->ajaxresult ['data'] ['newmsglist'] = $this->checkNewMessage ( $this->params ['from_customer'] ); // 检测该顾客的新消息
	
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
	}
	
	/**
	 * 微动内部检查某商家下某顾客的新消息的函数。
	 * @param string $customer_id 要查询新消息的顾客编号
	 * @return array $newmsglist 某导购的未读新消息列表
	 */
	private function checkNewMessage($customer_id = '') {
		$newmsglist = array (); // 最终返回的新消息数组，可能为空，建议三方APP检查该数组的length
		if (! empty ( $customer_id )) {
			$msgtable = M ( 'guidechatmsginfo' ); // 实例化导购顾客聊天消息表对象
			$checkmsgmap = array (
					'receiver_id' => $customer_id, // 消息目标接收者是当前顾客
					'receive_status' => 0, // 消息还没有被查看过
					'notify_count' => array ( "lt", 9 ), // 只通知三方APP次数小于等于8次的新消息，超过8次，默认为丢弃包
					'is_tocustomer' => 1, // 消息方向为：导购发给顾客（该字段：默认0代表顾客发送给导购，1代表导购发送给顾客）
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
							'to_customer' => $customer_id, // 消息要送达的目标顾客（微信用户）编号（是顾客编号，不是微信openid编号）
							'from_guide' => $msglist [$i] ['sender_id'], // 从哪个导购发来
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
	 * 网页端聊天查询服务器端有无新消息的接口。
	 * 每次查询新消息，微动总是选取最新未读的20条新消息，按时间升序排列，且最多只通知三方8次。
	 */
	public function queryNewMsg() {
		// 以下情况直接毙掉
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询新消息的导购编号！';
		} else {
			$this->ajaxresult ['data'] = array (
					'newmsglist' => $this->checkNewMessage ( $this->params ['cid'] ) // 检测该顾客的新消息
			);
			$this->ajaxresult ['errCode'] = 0; // 查询消息接口成功
			$this->ajaxresult ['errMsg'] = "ok"; // 查询消息接口返回OK
		}
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
	}
	
	/**
	 * 网页端聊天查询消息所在的记录位置。
	 */
	public function queryMsgPos() {
		// 如果没导购编号
		if (empty ($this->params ['gid'])) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息所属导购信息！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		// 如果没顾客编号
		if (empty ($this->params ['cid'])) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少消息所属顾客信息！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		// 如果没当前聊天记录的编号
		if (empty ($this->params ['msg_id'])) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少聊天消息编号信息！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		
		$msgtable = M ( 'guidechatmsginfo' ); // 导购顾客聊天记录表
		
		// Step1：查询当前记录的时间
		$currentmap = array (
				'appmsg_id' => $this->params ['msg_id'], // 给出查询的起始id编号
				'belong_guide' => $this->params ['gid'], // 消息所属导购编号
				'is_del' => 0
		);
		$currentinfo = $msgtable->where ( $currentmap )->find ();
		
		// 如果没查询到当前聊天记录的编号
		if (! $currentinfo) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = '接口参数错误，当前顾客和导购间该聊天消息不存在！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		
		// Step2：查询当前记录之前有多少条数据和总共有多少数据
		$msgbefore = array (
				'belong_guide' => $this->params ['gid'], // 属于当前导购的消息
				'_string' => "sender_id = '" . $this->params ['cid'] . "' or receiver_id = '" . $this->params ['cid'] . "'", // 当前顾客是发送者或者接收者
				'chat_time' => array ( "lt", $currentinfo ['chat_time'] ), // 小于这一秒的消息有多少条
		);
		$msgall = array (
				'belong_guide' => $this->params ['gid'], // 属于当前导购的消息
				'_string' => "sender_id = '" . $this->params ['cid'] . "' or receiver_id = '" . $this->params ['cid'] . "'", // 当前顾客是发送者或者接收者
		);
		$beforecount = $msgtable->where ( $msgbefore )->count (); // 查询当前时间点前面有几条
		$msgallcount = $msgtable->where ( $msgall )->count (); // 所有的消息数
		
		// Step3：查询这一秒有多少记录，该记录所在的位置
		$msgsecond = array (
				'belong_guide' => $this->params ['gid'], // 属于当前导购的消息
				'_string' => "sender_id = '" . $this->params ['cid'] . "' or receiver_id = '" . $this->params ['cid'] . "'", // 当前顾客是发送者或者接收者
				'chat_time' => array ( "eq", $currentinfo ['chat_time'] ), // 等于这一秒的消息
		);
		$secondlist = $msgtable->where ( $msgsecond )->order ( 'chat_time asc' )->select (); // 查询这一秒有多少该顾客和该导购之间的数据，按时间升序有序
		$listnum = count ( $secondlist ); // 统计数量
		if ($listnum > 1) {
			// 如果超过同一秒记录数在1条以上，确定偏移量
			for ($i = 0; $i < $listnum; $i ++) {
				if ($secondlist [$i] ['appmsg_id'] == $this->params ['msg_id']) {
					$beforecount += $i; // 如果当前的appmsg_id等于要请求的start_id
				}
			}
		}
		
		// 查询到数据库前面有几条，该条数据所在的数据库位置就是$beforecount
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] = array (
				'position' => $beforecount, // 本次的历史消息内容
				'totalcount' => $msgallcount, // 导购和顾客所有的聊天记录总数
		); 
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
	}
	
	/**
	 * 三方APP查询某导购与某顾客的历史聊天记录接口。
	 */
	public function queryHistoryMsg() {
		// 以下几种情况直接毙掉
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询历史消息的顾客编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少要查询历史消息的顾客所对话导购编号！';
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
		}
		
		// 处理没有传入reverse参数
		if (empty ( $this->params ['reverse'] ) ) {
			$this->params ['reverse'] = 0; // 如果不带reverse参数，默认正序查询历史消息
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
		
		// 开始查询导购与该顾客的聊天记录并返回给网页端
		$msgtable = M ( 'guidechatmsginfo' ); // 实例化表对象
		$historymsglist = array (); // 要回复三方APP的历史消息列表
		$nextstart = intval ( $this->params ['next_start'] ); // 下一条历史聊天记录开始的位置
		$msgorder = "asc"; // 默认按时间正序
		if ($this->params ['reverse'] == 1) {
			$msgorder = "desc"; // reverse时间倒序查询
		}
		
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
		
		if ($historymsgleft) {
			$querymsglist = $msgtable->where ( $historymsgmap )->order ( 'chat_time ' . $msgorder )->limit ( $nextstart, $realGetNumber )->select (); // 默认一次性最多查询50条导购的聊天记录，按聊天时间降序排列
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
		
		// 将历史消息返回给网页端
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] = array (
				'historymsg' => $historymsglist, // 本次的历史消息内容
				'next_start' => $newNextStart, // 下一次历史消息的起始点
				'total' => $historytotalcount // 总的顾客与导购聊天记录数，返回给三方方便统计与计算，灵活调取想要的消息记录
		); // 将查询到的$historymsglist返回给网页端
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
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
			if ($this->datatype == "jsonp") {
				echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
			} else {
				exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
			}
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
		} else {
			$this->ajaxresult ['errCode'] = 10002; // 处理失败，告知三方新消息不要频繁确认
			$this->ajaxresult ['errMsg'] = "确认消息失败，消息不存在或不要频繁确认。";
		}
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
	}
}
?>