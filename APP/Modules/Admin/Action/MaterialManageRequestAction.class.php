<?php
/**
 * 图文素材请求。
 * @author Administrator
 *
 */
class MaterialManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则会出错）。
	 * DefineTime:2014/09/30 20:45:25.
	 * @var string	variable	DBfield
	 */
	var $father_table = 'msgnews';
	var $child_table = 'msgnewsdetail';
	var $cc_m_id = 'msgnews_id';
	var $cc_e_id = 'e_id';
	var $cc_add = 'add_time';
	var $cc_latest = 'latest_modify';
	var $cc_category = 'msg_category';
	var $cc_md_id = 'msgnewsdetail_id';
	var $cc_title = 'title';
	var $cc_author = 'author';
	var $cc_cover = 'cover_image';
	var $cc_summary = 'summary';
	var $cc_content = 'main_content';
	var $cc_url = 'link_url'; // 图文详情地址
	var $cc_original = 'original_url'; // 原文链接
	var $cc_order = 'detail_order';
	var $cc_remark = 'remark';
	var $cc_is_del = 'is_del';
	
	/**
	 * 删除图文的操作。
	 * Author：赵臣升。
	 * CreateTime：2014/09/30 00:05:20.
	 * 特别注意：要删除图文不要单纯的删除，会导致菜单推送图文、扫码推送图文、自动回复图文、首次关注等地方使用图文出错，必须先解除关联。
	 * 最好提醒用户各种关联在哪里，让他去解除。
	 * 只有没有关联的图文才可以删除。
	 */
	public function delNewsConfirm(){
		$father_table = M($this->father_table);				//定义图文主表
		$child_table = M($this->child_table);			//定义图文子表
		$relation_a = M('autoresponse');			//图文可能关联的自动回复表
		$relation_c = M('customizedmenu');			//图文可能关联的自定义菜单表
		$relation_s = M('scenecode');				//图文可能关联的二维码场景值表
		$delnew [$this->cc_m_id] = I('mid');			//接收要删除的图文编号
	
		$errorCode = -1;							//错误代码，-1代表无错误
		$errorMsg = '';								//错误消息，空字符串代表无错误
		$successflag = true;						//删除图文成功标记，先置为成功
		$checkpoint = array(
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				'response_function' => 'responsenews',
				'response_content_id' => $delnew [$this->cc_m_id],
				$this->cc_is_del => 0
		);
		//Step1：检查自动回复表有无关联
		$racount = $relation_a->where($checkpoint)->count();
		if($racount > 0){
			$successflag = false;										//有关联，不能删除
			$errorCode = 100;											//100代表是自动回复表有关联
			$errorMsg = '您在自动回复设置过相关图文回复，需要先解除该图文的绑定!';		//提醒商户
		}
		//Step2：检查自定义菜单表有无关联
		if($successflag){
			$rccount = $relation_c->where($checkpoint)->count();
			if($rccount > 0){
				$successflag = false;									//有关联，不能删除
				$errorCode = 101;										//100代表是自动回复表有关联
				$errorMsg = '您在自定义菜单设置过相关图文回复，需要先解除该图文的绑定!';	//提醒商户
			}
		}
		//Step3：检查二维码场景值表有无关联
		if($successflag){
			$rscount = $relation_s->where($checkpoint)->count();
			if($rscount > 0){
				$successflag = false;									//有关联，不能删除
				$errorCode = 102;										//100代表是自动回复表有关联
				$errorMsg = '您在二维码场景设置过相关图文回复，需要先解除该图文的绑定!';	//提醒商户
			}
		}
	
		//Step4：如果没有任何的关联，再删除主图文和子图文
		$delcheck = array(
				$this->cc_m_id => $delnew [$this->cc_m_id],
				$this->cc_is_del => 0
		);
		if($successflag){
			$successflag = $child_table->where($delcheck)->setField($this->cc_is_del, 1);
			if($successflag){
				$delcheck [$this->cc_e_id] = $_SESSION ['curEnterprise'] ['e_id'];
				$successflag = $father_table->where($delcheck)->setField($this->cc_is_del, 1);
			}
		}
	
		//Step5：把后台处理结果反馈给前台
		if($successflag){
			$this->ajaxReturn( array('status' => 1, 'msg' => '相关图文已经删除!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'errorCode' => $errorCode, 'errorMsg' => $errorMsg) );
		}
	}
	
	/**
	 * 单图文信息添加。
	 * Author：张华庆。
	 */
	public function addSingleNewsConfirm() {
		if (! IS_POST) _404 ( "Sorry, page not exist!", U ( 'Admin/MaterialManage/newsView', '', '' ) );
	
		// 准备返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
	
		$title = I ( "title", "默认图文" ); 		// 子图文标题
		$coverurl = I ( 'coverurl' ); 			// 该图文封面
		$summary = I ( 'summary' ); 			// 单图文摘要
		$chainurl = I ( "chain", "" ); 			// 外链地址
		$originalurl = I ( "source_url", "" ); 	// 原文链接
		$author = $_SESSION ['curEnterprise'] ['e_name']; // 企业名称
	
		$mntable = M ( $this->father_table ); 	// 图文信息主表
		$mndtable = M ( $this->child_table ); 	// 图文信息子表
		$mntable->startTrans (); 				// 开启事务
	
		// Step1：首先往msgnews表中添加一条数据
		$mnmap = array (
				$this->cc_m_id => md5 ( uniqid ( rand (), true ) ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_add => time (),
				$this->cc_category => 0, 		// 单图文为0
				$this->cc_is_del => 0,
		);
		$mnresult = $mntable->add ( $mnmap );
	
		// Step2：然后在msgnewsdetail中添加一条数据
		$mndmap = array (
				$this->cc_md_id => md5 ( uniqid ( rand (), true ) ), 	// 图文子信息主键
				$this->cc_m_id => $mnmap [$this->cc_m_id], 				// 图文主表主键
				$this->cc_title => $title, 								// 子图文标题
				$this->cc_summary => $summary, 							// 单图文的图文摘要
				$this->cc_author => $author, 							// 图文作者
				$this->cc_cover => $coverurl, 							// 该图文封面
				$this->cc_order => -1, 									// 单图文封面图片直接是-1
				$this->cc_content => stripslashes ( &$_POST ['main_content'] ), // 注意转义接收图文消息图片
				$this->cc_original => $originalurl, 					// 原文链接
		);
		// 处理外链
		if (empty ( $chainurl )) {
			// 如果外链为空，代表不需要外链，直接用图文的详情地址代替
			$mndmap [$this->cc_url] = "http://www.we-act.cn/weact/Home/News/info/did/" . $mndmap [$this->cc_md_id]; // 图文详情地址
		} else {
			// 外链不空用外链地址
			$mndmap [$this->cc_url] = $chainurl;
		}
		$mndresult = $mndtable->add ( $mndmap );
	
		// 处理单图文添加结果
		if ($mnresult && $mndresult) {
			$mntable->commit ();
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$mntable->rollback ();
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "无任何改动或新增图文数目达到上限！";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前端消息
	}
	
	/**
	 * 编辑单图文post提交处理函数。
	 */
	public function editSingleNewsConfirm() {
		if (! IS_POST) _404 ( "Sorry, page not exist!", U ( 'Admin/MaterialManage/newsView', '', '' ) );
	
		// 准备返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
	
		$title = I ( "title", "默认图文" ); 			// 子图文标题
		$coverurl = I ( 'coverurl' ); 				// 该图文封面
		$summary = I ( 'summary' ); 				// 单图文摘要
		$chainurl = I ( "chain", "" ); 				// 外链地址
		$originalurl = I ( "source_url", "" ); 		// 原文链接
	
		$child_table = M ( $this->child_table ); 	// 图文信息子表
		$editmap = array (
				$this->cc_m_id => I ( 'mid' ),
				$this->cc_is_del => 0
		);
		$editinfo = $child_table->where ( $editmap )->find ();
		if ($editinfo) {
			$editinfo [$this->cc_title] = $title;
			$editinfo [$this->cc_cover] = $coverurl;
			$editinfo [$this->cc_summary] = $summary;
			$editinfo [$this->cc_content] = stripslashes ( &$_POST ['main_content'] );
			$editinfo [$this->cc_original] = $originalurl; // 原文链接
			$infourl = "http://www.we-act.cn/weact/Home/News/info/did/" . $editinfo [$this->cc_md_id];
			if ($chainurl != "") {
				// 如果跳转外链不空，则检测是否匹配默认URL
				if ($chainurl != $infourl) {
					// 如果不是默认URL，才去改变他
					$editinfo [$this->cc_url] = $chainurl;
				}
			} else {
				// 如果跳转外链空，则使用默认URL
				$editinfo [$this->cc_url] = $infourl;
			}
			$editinfo [$this->cc_latest] = time (); // 更新下最后一次修改的时间
			$successflag = $child_table->save ( $editinfo ); // 保存图文信息
			if ($successflag) {
				$ajaxresult ['errCode'] = 0;
				$ajaxresult ['errMsg'] = "ok";
				// 更新下主图文的时间
				$savemap = array (
						$this->cc_m_id => $editinfo [$this->cc_m_id],
						$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
						$this->cc_is_del => 0
				);
				$father_table = M ( $this->father_table );
				$saveinfo = M ( $this->father_table )->where ( $savemap )->setField ( $this->cc_latest, time () ); // 更新主消息的最后修改时间
			}
		} else {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "图文不存在或已删除，请不要重复提交！";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前端消息
	}
	
	/**
	 * 用户图文信息模块的图片上传。
	 * 使用ueditor富文本编辑器上传news图文消息图片的函数。
	 * Author：赵臣升。
	 * CreateTime：2014/09/27 03:17:25.
	 */
	public function singleNewsImageHandle(){
		$savePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/platforminfo/news/single/' . date ( 'Ymd' ) . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); // 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); // 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 用户多图文信息模块的图片上传。
	 * 使用ueditor富文本编辑器上传news图文消息图片的函数。
	 * Author：赵臣升。
	 * CreateTime：2014/09/27 03:17:25.
	 */
	public function multipleNewsImageHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/platforminfo/news/multiple/' . date('Ymd') . '/'; 	// 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); 											// 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); 										// 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 提交多图文post处理的控制器。
	 * Author：赵臣升。
	 * CreateTime：2014/09/28 15:20:25.
	 */
	public function addMulNewsConfirm() {
		if (! IS_POST) _404 ( "Sorry, page not exist!", U ( 'Admin/MaterialManage/newsView', '', '' ) ); // 防止恶意打开
	
		// 准备返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试。"
		);
	
		$mulnewsinfo = json_decode ( stripslashes ( &$_POST ['jsonData'] ), true ); // 转义接收post后，删除斜杠，再用json_decode解码，直接获得多图文数组
	
		// 在此处理这个多图文二维数组
		$author = $_SESSION ['curEnterprise'] ['e_name']; 	// 图文作者
		$parent_table = M ( $this->father_table ); 			// 图文主表
		$child_table = M ( $this->child_table ); 			// 图文子表
		$mulcount = count ( $mulnewsinfo ); 				// 本次提交多图文数量
	
		// Step1：判断图文消息是否超过限制，如果没有超过可以继续创建图文
		$overmap = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_is_del => 0
		);
		$newscount = $parent_table->where ( $overmap )->count ();
		if ($newscount > 300) {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "图文数量达到上限，请删除一些不必要的图文";
			$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
		}
	
		$parent_table->startTrans (); // 开启事务
	
		// Step2:创建一条图文消息，标记为多图文
		$parentinfo = array (
				$this->cc_m_id => md5 ( uniqid ( rand (), true ) ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_add => time (),
				'msg_use' => 0, 			// 多图文用处暂时未定
				$this->cc_category => 1, 	// 多图文为1
		);
		$parentresult = $parent_table->add ( $parentinfo ); // 插入主图文
	
		// Step3：生成子图文数组，准备直接addAll
		$childinfo = array (); // 子图文数组
		for($i = 0; $i < $mulcount; $i ++) {
			$childinfo [$i] = array (
					$this->cc_md_id => md5 ( uniqid ( rand (), true ) ), 	// 子图文主键
					$this->cc_m_id => $parentinfo [$this->cc_m_id], 		// 主图文主键
					$this->cc_title => $mulnewsinfo [$i] [$this->cc_title], // 子图文标题
					$this->cc_author => $author, 							// 图文作者是该企业
					$this->cc_cover => $mulnewsinfo [$i] ['coverurl'], 		// 封面图片
					$this->cc_content => $mulnewsinfo [$i] ['content'], 	// 图文详情
					$this->cc_original => $mulnewsinfo [$i] ['sourceurl'], 	// 原文链接
					$this->cc_order => ($i - 1), // 封面图文从-1开始
			); // 主图文是-1
			// 继续处理外链
			$chainurl = $mulnewsinfo [$i] ['chain']; // 提取外链
			if (empty ( $chainurl )) {
				// 如果外链为空，代表不需要外链，直接用图文的详情地址代替
				$childinfo [$i] [$this->cc_url] = "http://www.we-act.cn/weact/Home/News/info/did/" . $childinfo [$i] [$this->cc_md_id]; // 图文详情地址
			} else {
				// 外链不空用外链地址
				$childinfo [$i] [$this->cc_url] = $chainurl;
			}
		}
		$childresult = $child_table->addAll ( $childinfo ); // 插入子图文
	
		if ($parentresult && $childresult) {
			$parent_table->commit (); // 提交事务
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$parent_table->rollback (); // 回滚事务
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "网络繁忙，保存失败，请注意图文数据的格式，并不要重复提交。";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 编辑多图文提交确认的post地址。
	 * Author：赵臣升。
	 * CreateTime：2014/09/29 13:57:25.
	 * 设计思路：针对用户提交的信息，依次从头进行更新，1、如果没有则创建；2、如果有则全部覆盖；3、如果还有多余则删除（无法判断机械操作）。
	 * 覆盖的时候依据的条件是：当前商家（e_id），当前图文（外键msgnews_id），当前排序（detail_order）的一条没有被删除的（is_del）。
	 */
	public function editMulNewsConfirm() {
		if (! IS_POST) _404 ( "Sorry, page not exist!", U ( 'Admin/MaterialManage/newsView', '', '' ) ); // 防止恶意打开
	
		// 准备返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试。"
		);
	
		$editmulnewsinfo = json_decode ( stripslashes ( &$_POST ['jsonData'] ), true );				//转义接收post后，删除斜杠，再用json_decode解码，直接获得多图文数组
	
		//定义三种队列：1、$addQueue新增队列；2、$updateQueue更新队列；3、delQueue删除队列。
		$addQueue = array ();
		$updateQueue = array ();
		$delQueue = array ();
		$father_table = M ( $this->father_table ); // 实例化图文主表
		$child_table = M ( $this->child_table ); // 实例化图文子表
	
		// Step2-1：统计接收到的图文消息数目
		$receivecount = count ( $editmulnewsinfo ); // 接收到的图文有几条
		for($i = 0; $i < $receivecount; $i ++) {
			$editmulnewsinfo [$i] [$this->cc_order] = ($i - 1);		//处理新提交的数据$editmulnewsinfo有detail_order（仿），减1是考虑到封面图文顺序为-1
		}
	
		// Step2-2：统计数据库的图文消息数目
		$newsmap = array (
				$this->cc_m_id => $editmulnewsinfo [0] ['mid'],
				$this->cc_is_del => 0
		);
		$dbnewsinfo = $child_table->where ( $newsmap )->select ();
		$dbcount = count ( $dbnewsinfo ); // 数据库目前有几条图文
	
		// Step3-1：加入add或者del队列
		$min = 0;													//最少的图文条数
		if ($receivecount <= $dbcount) {
			$min = $receivecount;									//如果接收到的比数据库的更少（含等于）
			for($j = $receivecount; $j < $dbcount; $j ++) {
				array_push ( $delQueue, $dbnewsinfo [$j] );			//如果当前图文数目小于数据库的图文数目，说明数据库有需要删除的图文
			}
		} else {
			$min = $dbcount;										//如果接收到的比数据库多
			for($k = $dbcount; $k < $receivecount; $k ++) {
				array_push ( $addQueue, $editmulnewsinfo [$k] );	//如果当前图文数目大于数据库的图文数目，说明有新增加的图文
			}
		}
	
		// Step3-2：其他相同部分加入update队列
		for($i = 0; $i < $min; $i ++) {
			array_push ( $updateQueue, $editmulnewsinfo [$i] );
		}
	
		$child_table->startTrans (); // 图文子表开启事务过程
		$successflag = true;
	
		//Step4-1：先删除数据库多余图文
		$delnum = count ( $delQueue ); // 计算要删除的数量
		for($i = 0; $i < $delnum; $i++) {
			$delmap = null;											//清空删除数组
			$delmap = array (
					$this->cc_m_id => $delQueue [$i] [$this->cc_m_id],
					$this->cc_order => ($receivecount + $i - 1),		//特别注意：避开删除的致命错误❤❤❤
					$this->cc_is_del => 0
			);
			//$delresult = $child_table->where ( $delmap )->setField ( $this->cc_is_del, 1 );
			$delresult = $child_table->where ( $delmap )->delete (); // 没必要留下
			if (! $delresult) {
				$successflag = false;
				break;
			}
		}
	
		//Step4-2：再更新数据库相同编号图文
		$updatenum = count ( $updateQueue ); // 计算要更新的数量
		if ($updatenum && $successflag) {
			for($i = 0; $i < $updatenum; $i ++) {
				$updatemap = null;										//清空更新数组
				$updatemap = array (
						$this->cc_m_id => $updateQueue [$i] ['mid'],
						$this->cc_order => $updateQueue [$i] [$this->cc_order],
						$this->cc_is_del => 0
				);
				$upnewsinfo = $child_table->where ( $updatemap )->find ();
				if ($upnewsinfo) {
					//修改值
					$upnewsinfo [$this->cc_title] = $updateQueue [$i] [$this->cc_title];
					$upnewsinfo [$this->cc_cover] = $updateQueue [$i] ['coverurl'];
					$upnewsinfo [$this->cc_content] = $updateQueue [$i] ['content'];
					$upnewsinfo [$this->cc_order] = ($i - 1);			//特别注意：避开致命错误❤❤❤
					$upnewsinfo [$this->cc_original] = $updateQueue [$i] ['sourceurl']; // 原文链接
					// 外链留空代表使用默认，否则就使用填写的
					if (! empty ( $updateQueue [$i] ['chain'] )) {
						$upnewsinfo [$this->cc_url] = $updateQueue [$i] ['chain'];
					} else {
						$upnewsinfo [$this->cc_url] = "http://www.we-act.cn/weact/Home/News/info/did/" . $upnewsinfo [$this->cc_md_id];
					}
					$upnewsinfo [$this->cc_latest] = time (); // 最后一次更新的时间
					$successflag = $child_table->save ( $upnewsinfo );					//已经有主键，直接save，可能保存不成功（因为没改动）但是也要继续保存
					if (! $successflag) {
						break;
					}
				} else {
					$successflag = false;
					break;
				}
			}
		}
	
		//Step4-3：最后添加新增图文
		$addnum = count ( $addQueue ); // 计算要增加的数量
		if ($addnum && $successflag) {
			for($i = 0; $i < $addnum; $i ++) {
				$addinfo = null;												//清空新增数组
				$addinfo = array (
						$this->cc_md_id => md5 ( uniqid ( rand (), true ) ), 	// 重新生成详情主键
						$this->cc_m_id => $addQueue [$i] ['mid'],
						$this->cc_title => $addQueue [$i] [$this->cc_title],
						$this->cc_author => $_SESSION ['curEnterprise'] ['e_name'],
						$this->cc_cover => $addQueue [$i] ['coverurl'],
						$this->cc_content => $addQueue [$i] ['content'],
						$this->cc_order => $addQueue [$i] [$this->cc_order],	// 重要❤❤❤$addQueue [$i] ['detail_order']也可以是（$dbcount + $i - 1），因为前者被赋值过，所以等价。
						$this->cc_original => $addQueue [$i] ['sourceurl'], 	// 原文链接
				);
				// 处理外链
				if (! empty ( $addQueue [$i] ['chain'] )) {
					$upnewsinfo [$this->cc_url] = $addQueue [$i] ['chain'];
				} else {
					$upnewsinfo [$this->cc_url] = "http://www.we-act.cn/weact/Home/News/info/did/" . $addinfo [$this->cc_md_id];
				}
				$addresult = $child_table->add ( $addinfo ); // 添加这条图文
				if (! $addresult) {
					$successflag = false;
					break;
				}
			}
		}
	
		//Step5：更新主图文时间
		if ($successflag) {
			$mainmap = array (
					$this->cc_m_id => $editmulnewsinfo [0] ['mid'],
					$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
					$this->cc_is_del => 0
			);
			$saveresult = $father_table->where ( $mainmap )->setField ( $this->cc_latest, time () );
			if (! $saveresult) $successflag = false;
		}
	
		//Step6：返回给前台消息
		if ($successflag) {
			$child_table->commit (); // 提交事务
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$child_table->rollback (); // 回滚事务
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "网络繁忙，保存失败，请注意图文数据的格式，并不要重复提交。";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * easyUI上传图片。
	 * @param string $savePath
	 * @param string $maxSize
	 */
	private function omFileUploadImage($savePath = NULL, $maxSize = '10000000'){
		import ( 'ORG.Net.UploadFile' ); 				// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile ();					// 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath;					// 设置上传路径
		$upload->uploadReplace = true; 					// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');								// 文件过滤器准许上传的文件类型
		$upload->maxSize = $maxSize; 					// 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（图片最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->autoSub = false;						// 是否开启子目录保存
		$upload->subType = 'date';						// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd';					// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
	
		/* 进行文件的上传 */
		if( !file_exists($upload->savePath) ) mkdirs($upload->savePath);						//如果不存在目录，则自动创建目录文件夹
		if($upload->upload()){
			$fileinfo = $upload->getUploadFileInfo();
			echo json_encode(array(
					'fileName' => $fileinfo [0] ['savename'],														//向omfileupload返回图片上传后的文件名
					'fileUrl' => assemblepath(substr($fileinfo [0] ['savepath'], 1) . $fileinfo [0] ['savename'])	//向omfileupload返回文件路径
			));
		}else{
			echo json_encode(array(
					'response' => 'ERROR'
			));
		}
	}
	
	/**
	 * 单图文封面图片处理函数。
	 */
	public function singleNewsCoverHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/platforminfo/news/single/' . date('Ymd') . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$this->omFileUploadImage ( $savePath );
	}
	
	/**
	 * 多图文封面图片处理函数
	 */
	public function multipleNewsCoverHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/platforminfo/news/multiple/' . date ( 'Ymd' ) . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$this->omFileUploadImage ( $savePath );
	}
	
}
?>