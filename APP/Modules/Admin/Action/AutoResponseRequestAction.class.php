<?php
/**
 * 自动回复请求控制器。
 * @author Administrator
 * 
 */
class AutoResponseRequestAction extends PCRequestLoginAction {
	
	/**
	 * post请求展示商家已经设定的初次关注信息。
	 */
	public function requestInfoInit() {
		$request = array (
				'type' => I ( 'rt' ),
				'info_table' => 'msg' . I ( 'rt' ),
				'info_field' => 'msg' . I ( 'rt' ) . '_id',
				'content_id' => I ( 'cid' )
		);
	
		$requestinfo = array (
				'msg' => 'ok',
				'response_type' => $request ['type']
		); // 初始化请求信息数组
		$requestcondition = array (
				$request ['info_field'] => $request ['content_id'],
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		switch ($request ['type']) {
			case 'text' :
				$requestinfo ['content'] = M ( $request ['info_table'] )->where ( $requestcondition )->find (); 	// 文本是这样操作
				break;
			case 'news' :
				$maininfo = M ( $request ['info_table'] )->where ( $requestcondition )->find (); 	// 主图文信息的操作
				$maininfo ['add_time'] = timetodate ( $maininfo ['add_time'] ); // 格式化新增事件
				$maininfo ['latest_modify'] = timetodate ( $maininfo ['latest_modify'] ); // 格式化最后一次修改时间
				$requestinfo ['main_news'] = $maininfo; // 格式化的信息放入main_news中
				$requestinfo ['content'] = M ( $request ['info_table'] . 'detail' )->where ( $requestcondition )->order ( 'detail_order' )->select (); // 图文信息的操作
				$requestinfo ['article_count'] = count ( $requestinfo ['content'] ); 								// 图文再告诉一下是几条图文
				break;
			case 'link' :
				$requestinfo ['content'] = M ( $request ['info_table'] )->where ( $requestcondition )->find (); 	// 链接是这样操作
				break;
			default :
				$requestinfo ['content'] = M ( $request ['info_table'] )->where ( $requestcondition )->find (); 	// 默认是文本操作
				break;
		}
	
		if (! $requestinfo ['content']) {
			$requestinfo ['msg'] = 'error';
		}
		$this->ajaxReturn ( $requestinfo );
	}
	
	/**
	 * 首次关注确认提交函数。
	 * 对msgtext、msgnews、autoresponse三个表进行操作。
	 * Author：赵臣升。
	 * 略有不足：整个设置没有进行完整的事务过程。
	 */
	public function firstFocusConfirm() {
		// Forward前置工作：接收首次关注提交的数据
		$confirm = array (
				'type' => I ( 'type' ), 													// 接收首次关注提交的类型
				'news' => I ( 'news' ), 													// 接收首次关注提交的图文编号（可能是图文类型，也可能不是）
				'text' => stripslashes ( &$_POST ['text'] )  								// 进行不转义的接收，并且使用stripslashes去掉斜杠
		);
	
		/* --------------------Step1：对msgtext或msgnews表进行操作----------------------- */
		$content_id = ''; // 初始化首次关注的response_content_id
		if ($confirm ['type'] == 'news') {
			$content_id = $confirm ['news']; 												// 如果是图文类型，则回复的content_id存放发送过来的图文编号
			// 如果提交的首次关注确认是图文，对于msgnews表则是更改msg_use的用途
				
			$cancelfocus = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'msg_use' => 1,
					'is_del' => 0
			);
			$mntable = M ( 'msgnews' );
			$mntable->where ( $cancelfocus )->setField ( 'msg_use', 2 ); 	// 对于其他没设置为首次关注的图文，则要更改其msg_use类型
				
			$mn = array (
					'msgnews_id' => $content_id, 							// 如果是图文类型，则确认的$content_id存放发送过来的图文编号
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$mnresult = $mntable->where ( $mn )->setField ( 'msg_use', 1 ); // 首次关注图文msg_use是1
		} else {
			// 如果提交的首次关注是文本信息（有纯文字和超链接两种），文本信息都是以插入信息的方式
			$content_id = md5 ( uniqid ( rand (), true ) ); 				// 初始化文字信息主键
			if ($confirm ['type'] == 'text')
				$msg_type = 0; // 纯文本
			else
				$msg_type = 1; // 超链接
				
			$mttable = M ( 'msgtext' );
			$textreset = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'msg_use' => 1,
					'is_del' => 0
			);
			$mttable->where ( $textreset )->setField ( 'msg_use', 2 ); 		// 取消所有首次关注文本的关注效果（msg_use == 1）
			$addmt = array (
					'msgtext_id' => $content_id,
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'add_time' => time(),
					'msg_use' => 1, 										// 首次关注用
					'msg_type' => $msg_type, 								// 决定纯文字还是超链接，0代表纯文本，1代表超链接
					'content' => $confirm ['text']
			);
			$mtresult = $mttable->data ( $addmt )->add ();
		}
	
		/* -------------------Step2：对autoresponse表进行操作-------------------- */
		// Step2-1：初始化首次关注的信息（$focus数组给autoresponse用）
		$addflag = false; // 增加一条自动回复记录的标记，初始化为false，false代表不需要新增
		$focus = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 			// 当前商家
				'response_type' => 'subscribe', 							// 响应方式为关注
				'is_del' => 0  												// 没有被删除的
		);
		$artable = M ( 'autoresponse' );
		$arresult = $artable->where ( $focus )->find (); 					// 尝试找当前商家有没有设置过自动回复
		 
		// Step2-2：根据商家是否设置过自动回复进行不同的操作：add/save
		if ($arresult) {
			$focus = null; 													// 清空focus数组
			$focus = $arresult; 											// 如果找到商家设置过的自动回复，则将其给focus数组
		} else {
			// 如果没有找到这样一条记录，代表商家没有设置过，还是默认的欢迎关注...，此时需要插入记录
			$addflag = true; 												// 先把新增标记变为true，代表此刻要新增
			$focus ['autoresponse_id'] = md5 ( uniqid ( rand (), true ) ); 	// 为focus数组生成主键
		}
		$focus ['response_function'] = 'response' . $confirm ['type']; 		// 将focus数组增加回复类型
		$focus ['add_response_time'] = time(); 								// 将focus数组的定制时间更改为现在的时间
	
		// Step2-3：吸收Step1中的response_content_id字段信息
		$focus ['response_content_id'] = $content_id; 						// 将Step1中的全局变量（要回复的信息主键）$content_id添加进focus数组里
		 
		// Step2-4：对autoresponse表进行操作
		if ($addflag) {
			$confirmresult = $artable->data ( $focus )->add (); 			// 如果新增标记为真，进行新增
		} else {
			$confirmresult = $artable->save ( $focus ); 					// 如果新增标记为假，则对变动过的focus数组信息进行保存
		}
	
		// Step3：返回给前台信息
		if ($confirmresult) {
			$this->ajaxReturn ( array (
					'status' => 1,
					'msg' => '设置首次关注信息成功!'
			), 'json' );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'msg' => '设置首次关注信息失败!'
			), 'json' );
		}
	}
	
	/**
	 * 读取已设置的关键字。
	 */
	public function readAllKeywords(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/AutoResponse/keywordsReplyView', '', '', true ) ); // 防止恶意打开
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_response_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
	
		$responsemap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'keyword' => array('neq','null'),
				'is_del' => 0
		);
	
		$responsetbl = M ( 'autoresponse' );
		$total = $responsetbl->where($responsemap)->count ();
	
		$keywordslist = array ();
		if($total){
			$keywordslist = $responsetbl->where($responsemap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
			$keynum = count($keywordslist);
			for ($i = 0; $i<$keynum; $i++){
				$keywordslist[$i]['add_response_time'] = timetodate($keywordslist[$i]['add_response_time']);
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $keywordslist ) . '}';
		echo $json;
	}
	
	/**
	 * 添加关键字确认。
	 */
	public function addKeywordCfm() {
		// 接收参数
		$keyword = I ( 'keyword' ); // 关键字
		$restype = I ( 'type' ); // 回复类型:text、link、image、voice、video、news
		$responsefunction = "response"; // 回复函数
		$resinfo = ""; // 回复内容
		
		// 检测参数完整性
		if (empty ( $keyword )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "关键字不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		if (empty ( $restype )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "关键字回复类型不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		} else {
			if ($restype != "text" && $restype != "link" && $restype != "image" && $restype != "voice" && $restype != "video" && $restype != "news") {
				$this->ajaxresult ['errCode'] = 10004;
				$this->ajaxresult ['errMsg'] = "关键字回复类型不合法。";
				$this->ajaxReturn ( $this->ajaxresult );
			}
		}
		
		// 回复内容特殊性单独接收
		if ($restype == 'text' || $restype == 'link'){
			$resinfo = stripslashes ( &$_POST ['res'] ); // 回复文本或链接需要转义（怕html格式）
			$responsefunction .= "text"; // 回复文本
		} else {
			$resinfo = I ( 'res' ); // 回复内容：图片、声音或视频路径，或图文编号
			$responsefunction .= $restype; // 回复相应类型
		}
		
		// 检测内容完整性
		if (empty ( $resinfo )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "关键字回复内容不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		$artable = M ( 'autoresponse' ); // 实例化表
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; // 提取企业编号 
		
		// 检测相同关键字是否存在
		$keymap = array (
				'e_id' => $e_id, 
				'keyword' => $keyword, // 相同关键字
				'is_del' => 0,
		);
		$keyexist = $artable->where ( $keymap )->count (); 
		if ($keyexist) {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "相同关键字的回复已经存在，请不要重复新增该关键字，可对该关键字编辑。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 相同关键字不存在可以新增，开启事务过程
		$artable->startTrans(); // 开始事务过程，添加一个关键字一共有两个插入步骤：自动回复表一个步骤，对应消息类型表里一个步骤
		
		// 准备插入表t_autoresponse的数据
		$resdata = array (
				'autoresponse_id' => md5 ( uniqid ( rand (), true ) ), 	// 新主键
				'e_id' => $e_id, 										// 当前企业
				'response_type' => 'text', 								// 关键字回复，类型肯定是文本
				'keyword' => $keyword, 									// 关键字
				'response_function' => $responsefunction, 				// 回复函数
				'add_response_time' => time (), 						// 新增时间
		);
		
		// 根据type回复类型决定对哪张表t_msgtext、t_msgimage、t_msgnews、t_msgnewsdetail进行操作
		if ($restype == 'text' || $restype == 'link') {
			$textdata = array (
					'msgtext_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $e_id,
					'msg_use' => 2, // 2表示被动响应
					'content' => $resinfo, 
					'add_time' => time (),
			);
			if ($restype == 'text') {
				$textdata ['msg_type'] = 0; // 文本
			} else if ($restype == 'link') {
				$textdata ['msg_type'] = 1; // 超链接
			}
			$textresult = M ( 'msgtext' )->add ( $textdata ); // 插入文本
			if ($textresult) {
				$resdata ['response_content_id'] = $textdata ['msgtext_id'];
			} else {
				$artable->rollback (); // 新增失败就rollback
				$this->ajaxReturn ( $this->ajaxresult ); // 插入失败是网络繁忙
			}
		} else if ($restype == "image" || $restype == "voice" || $restype == "video") {
			$pkfield = "msg" . $restype . "_id"; // 媒体表主键
			$mediatable = M ( "msg" . $restype ); // 实例化媒体表
			// 媒体类型文件
			$mediadata = array (
					$pkfield => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $e_id,
					'msg_use' => 2, // 2表示被动响应
					'local_path' => $resinfo,
					'add_time' => time (),
			);
			$addmedia = $mediatable->add ( $mediadata ); // 往多媒体表里插入多媒体
			if ($addmedia) {
				$resdata ['response_content_id'] = $mediadata [$pkfield];
			} else {
				$artable->rollback (); // 新增失败就rollback
				$this->ajaxReturn ( $this->ajaxresult ); // 插入失败是网络繁忙
			}
		} else if ($restype == 'news') {
			$newsmap = array (
					'msgnews_id' => $resinfo, // 图文消息主键
					'e_id' => $e_id, 
					'is_del' => 0,
			);
			$newsinfo = M ( 'msgnews' )->where ( $newsmap )->find ();
			if ($newsinfo) {
				$resdata ['response_content_id'] = $newsinfo ['msgnews_id'];
			} else {
				$this->ajaxresult ['errCode'] = 10007;
				$this->ajaxresult ['errMsg'] = "关键字所设图文已不存在，请及时刷新页面。";
				$this->ajaxReturn ( $this->ajaxresult ); // 图文消息不存在了，直接报错
			}
		}
		
		// Step2：往t_autoresponse插入数据，因为前面一步出错直接回滚，所以这里只需要判断第二张表是否插入成功即可
		$resresult = $artable->add ( $resdata );
		if ($resresult) {
			$artable->commit (); 
			$this->ajaxresult ['errCode'] = 0; 
			$this->ajaxresult ['errMsg'] = "ok"; 
		} else {
			$artable->rollback (); // 回滚事务
			$this->ajaxresult ['errCode'] = 10008; 
			$this->ajaxresult ['errMsg'] = "添加失败，请检查网络状况，并不要重复提交!"; 
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 对现有关键字进行编辑、修改提交确认
	 */
	public function editKeywordCfm(){
		// 接收参数
		$autoid = I ( 'a_id' ); // 要编辑的关键字数据记录主键
		$keyword = I ( 'keyword' ); // 关键字
		$restype = I ( 'type' ); // 回复类型:text、link、image、voice、video、news
		$responsefunction = "response"; // 回复函数（待拼接）
		$resinfo = ""; // 回复内容（待分类接收）
		$artable = M ( 'autoresponse' ); // 实例化自动回复表
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; // 当前商家编号
		
		// 检测参数完整性
		if (empty ( $autoid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "要编辑的自动回复编号不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		if (empty ( $keyword )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "关键字不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		if (empty ( $restype )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "关键字回复类型不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		} else {
			if ($restype != "text" && $restype != "link" && $restype != "image" && $restype != "voice" && $restype != "video" && $restype != "news") {
				$this->ajaxresult ['errCode'] = 10005;
				$this->ajaxresult ['errMsg'] = "关键字回复类型不合法。";
				$this->ajaxReturn ( $this->ajaxresult );
			}
		}
		
		// 回复内容特殊性单独接收
		if ($restype == 'text' || $restype == 'link'){
			$resinfo = stripslashes ( &$_POST ['res'] ); // 回复文本或链接需要转义（怕html格式）
			$responsefunction .= "text"; // 回复文本
		} else {
			$resinfo = I ( 'res' ); // 回复内容：图片、声音或视频路径，或图文编号
			$responsefunction .= $restype; // 回复相应类型
		}
		
		// 检测内容完整性
		if (empty ( $resinfo )) {
			$this->ajaxresult ['errCode'] = 10006; 
			$this->ajaxresult ['errMsg'] = "关键字回复内容不能为空。"; 
			$this->ajaxReturn ( $this->ajaxresult ); 
		}
		
		// 检测要修改的记录是否存在
		$automap = array (
				'autoresponse_id' => $autoid, // 当前要编辑的自动回复编号
				'e_id' => $e_id, // 当前商家
				'is_del' => 0
		);
		$autoinfo = $artable->where ( $automap )->find (); // 查询这条自定义回复记录$autoinfo
		if (! $autoinfo) {
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "要修改的回复记录不存在。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		//开启事务过程
		$texttbl = M ( 'msgtext' );
		$newstbl = M ( 'msgnews' );
		$artable->startTrans(); // 开始事务过程，编辑一个关键字一共有两个插入步骤
		$autodata = array (
				'keyword' => $keyword, // 最终要修改的自动回复信息
				'response_function' => $responsefunction, // 本次新的回复类型
		);
		
		// 比较修改前后的type值是否一致,若一致则直接保存信息，若不一致则新增一条回复消息（主要针对msgtext、msgimage）
		if ($autodata ['response_function'] == $autoinfo ['response_function']) {
			// 如果跟原来的回复响应类型一致
			if ($restype == 'text' || $restype == 'link') {
				// 如果是文本或者超链接就编辑该文字
				$autodata ['response_content_id'] = $autoinfo ['response_content_id']; // 类型一致并且是文本，则主键不用修改
				$msg_type = 0; // 默认文本
				if ($restype == 'link') {
					$msg_type = 1; // 超链接
				}
				$textmap = array (
						'msgtext_id' => $autoinfo ['response_content_id'],
						'e_id' => $e_id, // 当前企业
						'is_del' => 0
				);
				$textdata = array (
						'msg_type' => $msg_type,
						'content' => $resinfo,
						'latest_modify' => time (), // 增加文本修改时间
				);
				$textresult = $texttbl->where ( $textmap )->save ( $textdata ); // 保存编辑的文本
				if (! $textresult) {
					$artable->rollback (); // 文本编辑保存失败就rollback
					$this->ajaxresult ['errCode'] = 10008; 
					$this->ajaxresult ['errMsg'] = "回复文本保存失败，请不要重复提交。"; 
					$this->ajaxReturn ( $this->ajaxresult ); 
				}
			}
		} else {
			// 回复类型不一致，默认原来的消息类型都没有，统一新增处理，根据type回复类型决定对哪张表进行操作
			if ($restype == 'text' || $restype == 'link') {
				$textdata = array (
						'msgtext_id' => md5 ( uniqid ( rand (), true ) ), 
						'e_id' => $e_id, 
						'msg_use' => 2, // 2表示被动响应
						'content' => $resinfo,
						'add_time' => time (),
				);
				if ($restype == 'text') {
					$textdata ['msg_type'] = 0; // 文本
				} else if ($restype == 'link') {
					$textdata ['msg_type'] = 1; // 超链接
				}
				$textresult = $texttbl->add ( $textdata ); // 插入文本
				if ($textresult) {
					$autodata ['response_content_id'] = $textdata ['msgtext_id']; // 文本插入成功则把新消息主键给autodata
				} else {
					$artable->rollback (); // 新增失败就rollback
					$this->ajaxresult ['errCode'] = 10008;
					$this->ajaxresult ['errMsg'] = "回复文本保存失败，请不要重复提交。";
					$this->ajaxReturn ( $this->ajaxresult ); // 插入失败是网络繁忙
				}
			} 
		}
		
		// 考虑到多媒体表的素材可能是永久素材|经常变动，所以每一次改变就新上传一次
		if ($restype == "image" || $restype == "voice" || $restype == "video") {
			$pkfield = "msg" . $restype . "_id"; // 媒体表主键
			$mediatable = M ( "msg" . $restype ); // 实例化媒体表
			// 媒体类型文件
			$mediadata = array (
					$pkfield => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $e_id,
					'msg_use' => 2, // 2表示被动响应
					'local_path' => $resinfo,
					'add_time' => time (),
			);
			$addmedia = $mediatable->add ( $mediadata ); // 往多媒体表里插入多媒体
			if ($addmedia) {
				$autodata ['response_content_id'] = $mediadata [$pkfield]; // 多媒体插入成功则把新消息主键给autodata
			} else {
				$artable->rollback (); // 多媒体新增失败就rollback
				$this->ajaxresult ['errCode'] = 10009;
				$this->ajaxresult ['errMsg'] = "多媒体新增失败，网络繁忙，请稍后再试。";
				$this->ajaxReturn ( $this->ajaxresult ); // 插入失败是网络繁忙
			}
		}
		
		// 如果是图文消息，必定更换主键，只需要查询图文消息是否存在
		if ($restype == 'news') {
			$newsmap = array (
					'msgnews_id' => $resinfo, // 图文消息主键
					'e_id' => $e_id,
					'is_del' => 0,
			);
			$newsinfo = M ( 'msgnews' )->where ( $newsmap )->find ();
			if ($newsinfo) {
				$autodata ['response_content_id'] = $newsinfo ['msgnews_id']; // 图文消息主键给autodata
			} else {
				$artable->rollback (); // 图文不存在就rollback
				$this->ajaxresult ['errCode'] = 10010;
				$this->ajaxresult ['errMsg'] = "关键字所设图文已不存在，请及时刷新页面。";
				$this->ajaxReturn ( $this->ajaxresult ); // 图文消息不存在了，直接报错
			}
		}
		
		// 再对autoresponse进行保存
		if ($autodata ['response_function'] == $autoinfo ['response_function'] && $autodata ['response_content_id'] == $autoinfo ['response_content_id'] && $autodata ['keyword'] == $autoinfo ['keyword']) {
			$autonewresult = true; // autoresponse表没有任何改动，不用保存直接默认成功
		} else {
			$autonewresult = $artable->where ( $automap )->save ( $autodata ); // 保存到自动回复表
		}
		
		// 处理自动回复的保存结果
		if ($autonewresult) {
			$artable->commit();
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$artable->rollback(); // 回滚事务
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "修改关键字回复失败，网络繁忙，请不要重复提交!";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 回复给前台
	}
	
	/**
	 * 删除已有关键字
	 * 不仅要处理autoresponse表，还要根据回复类型找出相应的消息表对相关条目进行处理
	 * 若回复类型是news,则只需处理autoresponse
	 */
	public function delKeyword(){
		$e_id = $_SESSION['curEnterprise']['e_id'];
		$a_id = I('aid');
		$ajaxresult = array();
	
		$autotbl = M('autoresponse');
		$autotbl->startTrans();//开启事务过程
	
		$delmap = array(
				'autoresponse_id' =>$a_id,
				'e_id' => $e_id,
				'is_del' => 0
		);
		$ainfo = $autotbl->where($delmap)->find();
	
		if($ainfo){
			//删除自动回复关键字
			$delauto = $autotbl->where($delmap)->setField('is_del','1');
				
			//根据回复类型分别对回复消息表信息进行删除
			$resfun = $ainfo['response_function'];//回复类型
			$resconid = $ainfo['response_content_id'];//回复消息编号
			if($resfun=='responsetext'){
				$texttbl = M('msgtext');
				$textmap = array(
						'msgtext_id' => $resconid,
						'is_del' => 0
				);
				$deltext = $texttbl->where($textmap)->setField('is_del','1');
			}else if($resfun=='responseimage'){
				$imagetbl = M('msgimage');
				$imagemap = array(
						'msgimage_id' => $resconid,
						'is_del' => 0
				);
				$delimage = $imagetbl->where($imagemap)->setField('is_del','1');
			}else if($resfun=='responsenews'){
				$delnews = true;
			}
		}else {
			$ajaxresult = array(
					'errCode' => '10001',
					'errMsg' => '该关键字回复信息有误，请勿重复操作！'
			);
			$this->ajaxReturn($ajaxresult);
		}
	
		if($delauto && ($deltext || $delimage || $delnews)){
			$autotbl->commit();
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '删除成功!'
			);
		} else {
			$autotbl->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/AutoResponse/medias/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
	
	/**
	 * 首次关注、自动回复等请求显示图文列表信息的ajax处理函数。
	 * CreateTime:2015/08/11 16:45:36.
	 * 已能成功渲染图文列表。
	 */
	public function requestNewsInfo() {
		$newsid = I ( 'nid' );
		
		// 检测参数完整性
		if (empty ( $newsid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "请求的图文消息编号不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 查询图文消息
		$newsview = M ( 'msgnews_info' );
		$newsmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 当前企业
				'msgnews_id' => $newsid, // 当前请求图文
				'is_del' => 0
		);
		$newslist = $newsview->where ( $newsmap )->order ( 'detail_order asc' )->select (); // 查询图文消息信息
		$newscount = count ( $newslist );
		if (! $newscount) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "所请求的图文消息已经不存在。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 格式化图文消息信息
		for($i = 0; $i < $newscount; $i ++) {
			$newslist [$i] ['add_time'] = timetodate ( $newslist [$i] ['add_time'] );
			$newslist [$i] ['latest_modify'] = timetodate ( $newslist [$i] ['latest_modify'] );
			$newslist [$i] ['cover_image'] = assemblepath ( $newslist [$i] ['cover_image'] );
			unset($newslist [$i] ['main_content']);
		}
		
		// 返回给前台信息
		$this->ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => array (
						'newslist' => $newslist, // 图文列表
				), 
		);
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 上传声音文件服务器端支持。
	 */
	public function voiceHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/platforminfo/autoresponse/voice/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUploadVoice ( $savepath ) );
	}
	
	/**
	 * 上传视频文件服务器端支持。
	 */
	public function videoHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/platforminfo/autoresponse/video/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUploadVideo ( $savepath ) );
	}
	
}
?>