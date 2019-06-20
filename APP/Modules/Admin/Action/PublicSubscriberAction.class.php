<?php
/**
 * 微信公众号控制器。
 * @author 万路康。
 * CreateTime:2014/06/20 14:31:25.
 */
class PublicSubscriberAction extends PCViewLoginAction {
	
	/**
	 * ==========Part1：用户页面视图区域=========
	 */
	
	/**
	 * 公众号菜单视图页面（打开的时候自动同步一次菜单），该视图是菜单列表。
	 */
	public function publicMenu() {
		//$this->autoSyncWechatMenu (); // 准备工作，打开页面自动同步菜单
		// Step1：查询企业的自定义菜单（如果有）
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$menulist = $menutable->where ( $emap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子、从小到大
		
		// Step2：特别注意：$packagemenu中，sibling_order越大，越在下边，最上边的子菜单序号是1；打包后的菜单，也有可能为空。
		$assembledmenu = $this->assembleDBMenu ( $menulist ); // 将菜单打包成前台需要的数组格式
		$this->menujson = jsencode ( $assembledmenu ); // 打包成json格式推送到前台
		$this->ename = $_SESSION ['curEnterprise'] ['e_name']; // 将企业名字推送到前台
		$this->display ();
	}
	
	/**
	 * 最内层框架的新增菜单信息页面（与响应方式）视图。
	 */
	public function addMenu() {
		$fathermenuid = $_REQUEST ['mid']; // 接收在哪个父级菜单下添加
		if (empty ( $fathermenuid )) $fathermenuid = -1; // 如果接收到空值，直接默认为-1
		$fathermenuname = "公众号自定义菜单";
		if ($fathermenuid != "-1") {
			// 不是在顶层下添加父级菜单，检查下当前的父级菜单是否存在
			$menumap = array (
					'menu_id' => $fathermenuid,
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$fathermenuinfo = M ( 'customizedmenu' )->where ( $menumap )->find (); // 找到当前要添加新菜单的父级菜单
			if ($fathermenuinfo) {
				$fathermenuname = $fathermenuinfo ['name']; // 如果当前父级菜单存在，更改其名字
			} else {
				$fathermenuid = "-1";
			}
		} 
		$this->mid = $fathermenuid; // 当前父级菜单的编号
		$this->mname = $fathermenuname; // 当前父级菜单的信息
		$this->display ();
	}
	
	/**
	 * 编辑菜单信息页面（及响应页面）视图。
	 * Author：赵臣升。
	 * CreateTime：2014/10/03 18:25:36.
	 * OptimizedTime：2014/10/05 15:36:36.
	 * 编辑菜单页面在整个菜单信息编辑及响应动作设定中使用最为频繁，其中url不再像首次关注那样往msgtext表里添加记录，而是直接写在菜单的响应方式上；
	 * 对于要编辑的菜单，可能性有两种：1、真菜单（无子级菜单）；2、假菜单（有子级菜单），对于后者只能修改名称，可以采用查询数据库展现不同模板的方式。
	 */
	public function editMenu() {
		$finaltpl = ''; // 最终要展现的模板
		// Step1：查询菜单信息
		$menumap = array (
				'menu_id' => I ( 'mid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0 
		);
		$menutable = M ( 'customizedmenu' );
		$menuinfo = $menutable->where ( $menumap )->find (); // 查询出当前要编辑的菜单
		$this->menuinfo = $menuinfo;
		
		// Step2：查询子级菜单信息（如果有）
		$childcheck = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_menu_id' => $menumap ['menu_id'],
				'is_del' => 0 
		);
		$childcount = $menutable->where ( $childcheck )->count ();
		if ($childcount > 0) {
			// 如果是个父级菜单（假菜单），只能修改其名字，不能修改响应方式（展现模板2）
			$finaltpl = 'editFatherMenu'; // 修改父级菜单模板
		} else {
			// 如果是真菜单（无子级菜单），则能修改名字、也能修改响应方式（展现原始模板）
			/* -----------------------------在此写入编辑真菜单初始化的一些操作开始------------------------------ */
			// Step1：初始化图文选择框里的图文信息
			$allnewsmap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 找到当前商家下
					'is_del' => 0 
			);
			// 做视图拼接，msgnews表是主表，别名p(parent)；msgnewsdetail表是子表，别名c(child)。
			$sql = 'p.msgnews_id = c.msgnews_id and p.is_del = 0 and c.is_del = 0 and p.e_id = \'' . $allnewsmap ['e_id'] . '\' and c.detail_order = -1 ';
			$model = new Model ();
			$allnews = $model->table ( 't_msgnews p, t_msgnewsdetail c' )->where ( $sql )->order ( 'p.add_time, c.detail_order asc' )->field ( 'p.msgnews_id, c.title' )->select (); // 查出全部
			
			// Step2：初始化回复信息：
			/**
			 * 此处设计理念：
			 * a)option_value用来标识类型;
			 * b)response_content_id用来标识选中的图文;
			 * c)response_info用来表示文本回复的内容，如果没有任何设定，默认是text且欢迎关注...
			 */
			$responseinfo = array (
					'option_value' => 'text', // 默认是text类型，option_value同时也是response.type
					'selected_news' => $allnews [0] ['msgnews_id'], // 默认选中的图文编号，初始化用图文表中查到的所有图文信息第一条表示
					'text_info' => '请选择菜单响应的方式，或直接在此输入响应文字。'  // 默认提醒设置菜单
			);
			
			// Step4：如果菜单有预设定，则更改初始化info数组的值
			if ($menuinfo ['type'] != null && $menuinfo ['type'] == "view") {
				// 如果菜单已经是跳转URL方式的type：view
				$responseinfo ['option_value'] = 'link'; // 修改菜单当前属性为超链接
				$responseinfo ['text_info'] = $menuinfo ['url']; // 提取菜单的url地址作为文本信息
			} else {
				if ($menuinfo ['response_function']) {
					// 如果找到回复方式，则菜单记录中已经定义了response_function和response_content_id
					$responseinfo ['option_value'] = substr ( $menuinfo ['response_function'], 8 ); // 提取菜单的回复类型
					if ($responseinfo ['option_value'] == 'news') {
						$responseinfo ['selected_news'] = $menuinfo ['response_content_id']; // 如果首次关注方式是图文，设置新闻选择框选中的主键
					} else {
						// 如果首次关注方式是文本，无需设置selected_news，但是要推送是链接还是图文的标记msg_type
						$textmap = array (
								'msgtext_id' => $menuinfo ['response_content_id'], // 回复文本的主键
								'e_id' => $allnewsmap ['e_id'],
								'is_del' => 0 
						);
						$textinfo = M ( 'msgtext' )->where ( $textmap )->find (); // 用主键去查找文本信息表里的信息
						if ($textinfo ['msg_type'] == 1) {
							$responseinfo ['option_value'] = 'link'; // 如果文本的类型为链接，修改默认属性option_value为link
						}
						$responseinfo ['text_info'] = $textinfo ['content']; // 将首次关注文本信息放入$responseinfo['text_info']，无论是text还是link
					}
				}
			}
			
			$this->responseinfo = $responseinfo; // responseinfo：推送前台显示的选择信息
			$this->option_news = $allnews; // option_news：推送前台图文信息
			/* -----------------------------在此写入编辑真菜单初始化的一些操作结束--------------------------------- */
		}

		$this->display ( $finaltpl );
	}
	
	/**
	 * 菜单设置框架页面初始化函数。
	 */
	public function menuFrameInit() {
		$this->display();
	}
	
	/**
	 * ==========Part2：用户页面视图动态提交ajax交互区域=========
	 */
	
	/**
	 * 前台提交新增菜单的ajax处理函数。
	 */
	public function addMenuConfirm() {
		if (! IS_POST) halt ( "sorry, page not exist!" ); // 防止恶意提交
		// 返回信息初始化
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		$menutable = M ( 'customizedmenu' ); // 初始化表对象
		// 判断新添加菜单是否满足条件
		$father_menu_id = I ( 'fid' ); // 接收要添加的父级菜单编号
		$menulevel = $father_menu_id == "-1" ? 0 : 1; // 新添加菜单的level
		$menucheck = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_menu_id' => $father_menu_id,
				'is_del' => 0
		);
		$samecount = $menutable->where ( $menucheck )->count (); // 计算相同类别的菜单有几个
		if ($father_menu_id == "-1" && $samecount >= 3) {
			// 新添加的菜单为父级菜单，但是父级菜单已经满3个了
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "父级菜单不能超过3个";
		} else if ($father_menu_id != "-1" && $samecount >= 5) {
			// 新添加的菜单为子级菜单，但是子级菜单已经满5个了
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "子级菜单不能超过5个";
		} else {
			// 允许添加新菜单的情况
			$newmenuinfo = array (
					'menu_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'level' => $menulevel,
					'father_menu_id' => $father_menu_id,
					'name' => I ( 'name' ),
					'sibling_order' => $samecount + 1, // 0个，sibling_order是1；3个sibling_order是4
					'add_time' => time ()
			);
			$addresult = $menutable->add ( $newmenuinfo ); // 添加新菜单
			if ($addresult) {
				$ajaxresult ['errCode'] = 0; // 添加菜单成功
				$ajaxresult ['errMsg'] = "ok"; // 返回OK信息
			}
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 编辑公众号父级菜单信息的post处理函数。
	 * Author：赵臣升。
	 * CreateTime：2014/10/05 16:24:25.
	 * 编辑父级菜单需要先验证，否则页面停留过久可能有变动。
	 */
	public function editFatherMenuConfirm() {
		$successflag = false; // 菜单修改名称成功标记，先置为false
		// Step1：要编辑的父级菜单信息
		$menutarget = array (
				'menu_id' => I ( 'id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		// Step2：新菜单名字
		$newinfo = array (
				'name' => I ( 'name' )
		);
	
		// Step3：服务器端验证父级菜单成立条件
		$childcheck = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_menu_id' => $menutarget ['menu_id'],
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$childcount = $menutable->where ( $childcheck )->count ();
		if ($childcount > 0) {
			// 验证通过，可编辑的父级菜单
			$currentmenu = $menutable->where ( $menutarget )->find ();
			if ($currentmenu ['name'] == $newinfo ['name']) {
				$this->ajaxReturn ( array (
						'status' => 0,
						'msg' => '菜单名称无改动!'
				) );
			}
			$successflag = $menutable->where ( $menutarget )->setField ( 'name', $newinfo ['name'] );
			if ($successflag) {
				$this->ajaxReturn ( array (
						'status' => 1,
						'msg' => '所编辑信息已经保存!'
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'msg' => '修改菜单名称失败，请不要重复提交!'
				) );
			}
		} else {
			// 验证失败，页面停留过久有变动
			$this->ajaxReturn ( array (
					'status' => 0,
					'msg' => '所编辑的父级菜单已过期，请及时刷新页面后再试!'
			) );
		}
	}
	
	/**
	 * 编辑公众号菜单信息或响应函数的post处理函数。
	 * 功能：
	 * 1、编辑菜单信息；
	 * 2、修改costomized_menu表中设置response_function和response_content_id，设置他们的响应方式。
	 * Author：赵臣升。
	 * CreateTime：2014/10/04 18:25:36.
	 */
	public function editMenuConfirm() {
		// Step1：接收提交的菜单信息及响应方式
		$editinfo = array (
				'name' => I ( 'name' ), // 接收菜单的名称
				'type' => I ( 'type' ), // 接收菜单的响应类型
				'news' => I ( 'news' ), // 接收响应图文消息的编号（如果有）
				'text' => stripslashes ( $_POST ['text'] )  // 接收响应文本或链接（如果有），并采用转义与删除斜杠的接收方法
		);
	
		// Step2：查找到要编辑的菜单信息
		$successflag = false; // 修改菜单响应方式成功的标记，先置为false
		$seekmenu = array (
				'menu_id' => I ( 'id' ), // 接收要编辑的菜单编号
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家
				'level' => I ( 'level' ), // 接收要编辑的菜单级数
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$editmenuinfo = $menutable->where ( $seekmenu )->find (); // 找到要编辑的菜单
		if (! $editmenuinfo)
			$this->ajaxReturn ( array (
					'status' => 0,
					'msg' => '当前要设置的菜单已经不存在，请及时刷新页面!'
			) );
				
			// Step3：服务器端对菜单响应的可编辑性进行一下验证（看是否有孩子节点）
			$childcheck = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'father_menu_id' => $seekmenu ['menu_id'],
					'is_del' => 0
			);
			$childcount = $menutable->where ( $childcheck )->count ();
			if ($childcount > 0) {
				$successflag = $menutable->where ( $seekmenu )->setField ( 'name', $editinfo ['name'] ); // 菜单有子级菜单，无法设置相应方式，只能编辑菜单名字
				if ($successflag) {
					$this->ajaxReturn ( array (
							'status' => 1,
							'msg' => '父级菜单只能设置名字不能设置响应方式，已提交保存!'
					) );
				} else {
					$this->ajaxReturn ( array (
							'status' => 0,
							'msg' => '父级菜单只能设置名字不能设置响应方式，网络繁忙，提交失败，请稍后再试!'
					) );
				}
			}
	
			// Step4：如果$childcount == 0，说明该菜单是叶子节点菜单（可以是父级菜单无子级，也可以是子级菜单，再通过level区分），可以编辑响应方式：对msgtext或msgnews表进行操作
			$response_function = 'response' . $editinfo ['type']; // 菜单响应的response_function
			$response_content_id = ''; // 菜单响应的response_content_id
			if ($editinfo ['type'] == 'news') {
				$response_content_id = $editinfo ['news']; // 如果是图文类型，则回复的content_id存放发送过来的图文编号
			} else {
				// 如果提交的首次关注是文本信息（有纯文字和超链接两种），文本信息都是以插入信息的方式
				$response_content_id = md5 ( uniqid ( rand (), true ) ); // 初始化文字信息主键
				$msg_type = ($editinfo ['type'] == 'text') ? 0 : 1; // 消息类型是0代表纯文本，是1代表超链接
					
				if ($msg_type == 0) {
					// 如果是纯文本，才添加到msgtext表中
					$addmt = array (
							'msgtext_id' => $response_content_id,
							'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
							'add_time' => time(),
							'msg_use' => 2, // 2代表被动响应，此处是响应菜单
							'msg_type' => $msg_type, // 决定纯文字还是超链接，0代表纯文本，1代表超链接
							'content' => $editinfo ['text']
					);
					$mttable = M ( 'msgtext' );
					$mtresult = $mttable->data ( $addmt )->add ();
				} else {
					// Step5-1：超链接就直接在菜单上编辑并保存
					$editmenuinfo ['type'] = 'view'; // 设置类型为view
					$editmenuinfo ['name'] = $editinfo ['name']; // 设置菜单新名字
					$editmenuinfo ['key'] = null; // 超链接类型菜单无key
					$editmenuinfo ['url'] = $editinfo ['text']; // 超链接给到菜单的url上
					$editmenuinfo ['response_function'] = null; // 超链接类型没有响应函数
					$editmenuinfo ['response_content_id'] = null; // 超链接类型没有响应消息编号
					// 可以再加一条latest_modify
					$successflag = $menutable->save ( $editmenuinfo ); // 保存新编辑的菜单信息
					if ($successflag) {
						$this->ajaxReturn ( array (
								'status' => 1,
								'msg' => '菜单信息及响应方式已提交保存!'
						) );
					} else {
						$this->ajaxReturn ( array (
								'status' => 0,
								'msg' => '菜单信息或响应方式提交失败，网络繁忙，请稍后再试!'
						) );
					}
				}
			}
			// Step5-2：图文和超链接的类型设置并保存
			$editmenuinfo ['type'] = 'click'; // 设置类型为click
			$editmenuinfo ['name'] = $editinfo ['name']; // 设置菜单新名字
			$editmenuinfo ['key'] = md5 ( uniqid ( rand (), true ) ); // 随即一个md5的32位码作为keyvalue
			$editmenuinfo ['url'] = null; // 按钮类型菜单无url
			$editmenuinfo ['response_function'] = $response_function; // 设置按钮类型菜单响应函数
			$editmenuinfo ['response_content_id'] = $response_content_id; // 设置按钮类型菜单响应消息编号
			// 可以再加一条latest_modify
			$successflag = $menutable->save ( $editmenuinfo ); // 保存新编辑的菜单信息
			if ($successflag) {
				$this->ajaxReturn ( array (
						'status' => 1,
						'msg' => '菜单信息及响应方式已提交保存!'
				) );
			} else {
				$this->ajaxReturn ( array (
						'status' => 0,
						'msg' => '菜单信息或响应方式提交失败，网络繁忙，请稍后再试!'
				) );
			}
	}
	
	/**
	 * 删除公众号（部分）菜单ajax响应函数。
	 */
	public function delMenuConfirm() {
		if (! IS_POST) halt ( "sorry, page not exist" ); // 防止恶意打开ajax处理。
		// Step1：初始化返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// Step2：接收
		$menu_id = $_REQUEST ['mid']; // 接收要删除的menu_id一定要用这种方式接收参数，I函数接收不到
		// Step3：处理删除
		if (! empty ( $menu_id )) {
			$menutable = M ( 'customizedmenu' ); // 菜单表
			$childmenumap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'father_menu_id' => $menu_id, // 找其孩子菜单
					'is_del' => 0
			);
			$childmenulist = $menutable->where ( $childmenumap )->select (); // 尝试获取其孩子菜单信息
			if ($childmenulist) {
				// 如果该菜单下有孩子节点，不允许其删除
				$ajaxresult ['errCode'] = 10002;
				$ajaxresult ['errMsg'] = "该菜单下还有子菜单，无法被删除！";
			} else {
				// 如果该菜单下没有孩子节点，允许其删除，用真删
				$delmenumap = array (
						'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
						'menu_id' => $menu_id, // 找其孩子菜单
						'is_del' => 0
				);
				$delresult = $menutable->where ( $delmenumap )->delete (); // 真的删除该菜单
				if ($delresult) {
					$ajaxresult ['errCode'] = 0;
					$ajaxresult ['errMsg'] = "ok";
				}
			}
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台删除结果信息
	}
	
	/**
	 * ajax处理函数：还原（同步）微信服务器菜单到本地数据库。
	 */
	public function syncMenu() {
		if (! IS_POST) halt ( 'sorry, page not exist!' );
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		$saveresult = $this->autoSyncWechatMenu (); // 自动同步微信菜单
		if ($saveresult) {
			// 如果新菜单和本地同步成功
			$ajaxresult ['errCode'] = 0; // 通讯成功
			$ajaxresult ['errMsg'] = "ok"; // 通讯信息OK
		} else {
			$ajaxresult ['errCode'] = 10002; // 通讯成功
			$ajaxresult ['errMsg'] = "您的微信公众号还没有微信认证或微信服务器正忙，请确认后再试！"; // 通讯信息OK
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台通信消息
	}
	
	/**
	 * ajax处理函数：本地保存用户前台的菜单数据。
	 */
	public function saveMenu() {
		if (! IS_POST) halt ( 'sorry, page not exist!' );
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// Step1：将页面提交过来的菜单格式化成级联形式
		$menutree = stripslashes ( $_REQUEST ['treeData'] ); // 接收前台页面传来的新菜单信息
		$pagetreedata = json_decode ( $menutree, true ); // json解码得到树的二维数组，此数组是级联形式
		$pageremotemenu = $this->disassembleJsonMenu ( $pagetreedata ); // 拆解json_decode后的二维级联数组，结果数组是数据库查询出来的形式
		$newcascademenu = $this->formatCascadeMenu ( $pageremotemenu ); // 格式化成同步所需的级联菜单形式
		// Step2：查询本地菜单，格式化成级联形式
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$dbmenulist = M ( 'customizedmenu' )->where ( $emap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子、从小到大
		$dbcascademenu = $this->formatCascadeMenu ( $dbmenulist ); // 格式化城同步所需的级联菜单格式
		// Step3：同步菜单
		$saveresult = $this->syncRemoteMenu ( $newcascademenu, $dbcascademenu ); // 同步用户新菜单
		if ($saveresult) {
			// 如果新菜单和本地同步成功
			$ajaxresult ['errCode'] = 0; // 通讯成功
			$ajaxresult ['errMsg'] = "ok"; // 通讯信息OK
		} 
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台通信消息
	}
	
	/**
	 * ajax处理函数：停用微信服务器的公众号菜单。
	 */
	public function invalidateMenu() {
	
	}
	
	/**
	 * ajax处理函数：预览用户页面的菜单。
	 */
	public function previewMenu() {
		if (! IS_POST) halt ( "sorry, page not exist!" );
		// 初始化返回信息
		$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => array ()
		);
		
		// Step1：查询企业的自定义菜单（如果有）
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$menulist = $menutable->where ( $emap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子、从小到大
		
		// Step2：将菜单格式化成级联菜单形式
		if ($menulist) {
			$ajaxresult ['data'] = $this->formatCascadeMenu ( $menulist ); // 组装成级联菜单给前台arttemplate
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台ajax信息
	}
	
	/**
	 * 向微信服务器发送用户保存的菜单处理的ajax函数publishMenu。
	 */
	public function publishMenu() {
		if (! IS_POST) halt ( 'sorry, page not exist!' );
		$needpublish = I ( 'publish' ); // 接收是否要发布菜单
		$setresult = false; // 返回错误
		// 定义错误返回值
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// 如果确实需要发布菜单，直接调用微信接口
		if ($needpublish) {
			$swc = A ( 'Service/WeChat' ); // 新建微信服务层类对象
			$setresult = $swc->setMenu ( $_SESSION ['curEnterprise'] ); // 设置企业菜单
			if ($setresult) {
				$ajaxresult ['errCode'] = 0;
				$ajaxresult ['errMsg'] = "ok";
			} else {
				$ajaxresult ['errCode'] = 10002;
				$ajaxresult ['errMsg'] = "菜单信息不完整或公众号微信认证失败，请确认后再试！";
			}
		} 
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 验证菜单，验证用户保存的菜单是否合法。
	 */
	public function validateMenu() {
		// 前面这40行代码只是从数据库中找到数组，仅供测试而已
		$cmmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		// 再次查数据库，从数据库中获得$totalfathercmmap
		$cmtable = M ( 'customizedmenu' );
		$cmresult = $cmtable->where ( $cmmap )->order ( 'sibling_order' )->select ();
		for($i = 0; $i < count ( $cmresult ); $i ++) {
			if ($cmresult [$i] ['father_menu_id'] == '-1') {
				$totalfathercmmap [$cmresult [$i] ['sibling_order'] - 1] = $cmresult [$i];
			}
		}
		ksort ( $totalfathercmmap );
		for($j = 0; $j < count ( $cmresult ); $j ++) {
			for($k = 0; $k < count ( $totalfathercmmap ); $k ++) {
				if ($cmresult [$j] ['father_menu_id'] == $totalfathercmmap [$k] ['menu_id']) {
					if ($k == 0) {
						$totalfathercmmap [0] ['sub_button'] [$cmresult [$j] ['sibling_order'] - 1] = $cmresult [$j];
					}
					if ($k == 1) {
						$totalfathercmmap [1] ['sub_button'] [$cmresult [$j] ['sibling_order'] - 1] = $cmresult [$j];
					}
					if ($k == 2) {
						$totalfathercmmap [2] ['sub_button'] [$cmresult [$j] ['sibling_order'] - 1] = $cmresult [$j];
					}
				}
			}
		}
		// p($totalfathercmmap);die;
		// 得到js要求的menutree的格式
		for($j = 0; $j < count ( $totalfathercmmap ); $j ++) {
			$fatherinfo [$j] ['text'] = $totalfathercmmap [$j] ['name'];
			$fatherinfo [$j] ['id'] = $totalfathercmmap [$j] ['menu_id'];
			for($i = 0; $i < count ( $totalfathercmmap [$j] ['sub_button'] ); $i ++) {
				$fatherinfo [$j] ['children'] [$i] ['text'] = $totalfathercmmap [$j] ['sub_button'] [$i] ['name'];
				$fatherinfo [$j] ['children'] [$i] ['id'] = $totalfathercmmap [$j] ['sub_button'] [$i] ['menu_id'];
				if ($totalfathercmmap [$j] ['sub_button'] [$i] ['key'] != null) {
					$fatherinfo [$j] ['children'] [$i] ['key'] = $totalfathercmmap [$j] ['sub_button'] [$i] ['key'];
				}
			}
		}
		$jsonbuttoninfo = $fatherinfo;
		// $buttoninfo=I('buttoninfo');
		// $buttoninfo='[{"text":"购物专区","id":"menu00013"},{"text":"品牌速递","id":"menu00014","children":[{"text":"品牌故事","id":"menu00016"},{"text":"加盟优势","id":"menu00017"}]},{"text":"我的","id":"menu00015","children":[{"text":"注册会员","id":"menu00018"},{"text":"我的积分","id":"menu00019"},{"text":"我的购物车","id":"menu00020"},{"text":"我的订单","id":"menu00021"},{"text":"我的反馈","id":"menu00022"}]}]}]';
		// $json = '{"a":"php","b":"mysql","c":3}';
		// $jsonbuttoninfo=json_decode($buttoninfo);
		p ( $fatherinfo );
		// p(!$jsonbuttoninfo[1]['type']);die;
		if (count ( $jsonbuttoninfo ) > 3)
			$this->ajaxReturn ( array (
					'status' => '0',
					'errortype' => '父级菜单数目不能超出3个'
			), 'json' );
		// return '父级菜单数目不能超出3个';
		for($i = 0; $i < count ( $jsonbuttoninfo ); $i ++) {
			if (strlen ( $jsonbuttoninfo [$i] ['text'] ) > 16) // 一个英文字符占1个字节，1个中文字符如果是utf-8编码的话占3个字节
				$this->ajaxReturn ( array (
						'status' => '0',
						'errortype' => '父级菜单名称不能超过16个字节'
				), 'json' );
			// return '父级菜单名称不能超过16个字节';
			if (count ( $jsonbuttoninfo [$i] ['children'] ) > 5)
				$this->ajaxReturn ( array (
						'status' => '0',
						'errortype' => '子级菜单数目不能超过5个'
				), 'json' );
			// return '子级菜单数目不能超过5个';
			if ($jsonbuttoninfo [$i] ['type'] == 'click' && ($jsonbuttoninfo [$i] ['key'] == null || $jsonbuttoninfo [$i] ['key'] == ''))
				$this->ajaxReturn ( array (
						'status' => '0',
						'errortype' => '如果是click类型的话，请填入关键字'
				), 'json' );
			// return '如果是click类型的话，请填入关键字';
			if ($jsonbuttoninfo [$i] ['type'] == 'view' && ($jsonbuttoninfo [$i] ['url'] == null || $jsonbuttoninfo [$i] ['url'] == ''))
				$this->ajaxReturn ( array (
						'status' => '0',
						'errortype' => '如果是view类型的话，请填入网页链接'
				), 'json' );
			// return '如果是view类型的话，请填入网页链接';
			if ($jsonbuttoninfo [$i] ['type'] != null && $jsonbuttoninfo [$i] ['type'] != '' && $jsonbuttoninfo [$i] ['type'] != 'click' && $jsonbuttoninfo [$i] ['type'] != 'view')
				$this->ajaxReturn ( array (
						'status' => '0',
						'errortype' => '菜单的响应动作类型，目前只有click、view两种类型'
				), 'json' );
			// return '菜单的响应动作类型，目前只有click、view两种类型';
			for($j = 0; $j < count ( $jsonbuttoninfo [$i] ['children'] ); $j ++) {
				if (strlen ( $jsonbuttoninfo [$i] [$j] ['text'] ) > 40)
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '子级菜单名称不能超过40个字节'
					), 'json' );
				// return '子级菜单名称不能超过40个字节';
				if (strlen ( $jsonbuttoninfo [$i] [$j] ['key'] ) > 128)
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '子级菜单关键字不能超过128个字节'
					), 'json' );
				// return '子级菜单关键字不能超过128个字节';
				if (strlen ( $jsonbuttoninfo [$i] [$j] ['url'] ) > 256)
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '子级菜单url不能超过256个字节'
					), 'json' );
				// return '子级菜单url不能超过256个字节';
				if ($jsonbuttoninfo [$i] [$j] ['type'] != null && $jsonbuttoninfo [$i] [$j] ['type'] != '' && $jsonbuttoninfo [$i] [$j] ['type'] != 'click' && $jsonbuttoninfo [$i] [$j] ['type'] != 'view')
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '菜单的响应动作类型，目前只有click、view两种类型'
					), 'json' );
				// return '菜单的响应动作类型，目前只有click、view两种类型';
				if ($jsonbuttoninfo [$i] [$j] ['type'] == 'click' && ($jsonbuttoninfo [$i] [$j] ['key'] == null || $jsonbuttoninfo [$i] [$j] ['key'] == ''))
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '如果是click类型的话，请填入关键字'
					), 'json' );
				// return '如果是click类型的话，请填入关键字';
				if ($jsonbuttoninfo [$i] [$j] ['type'] == 'view' && ($jsonbuttoninfo [$i] [$j] ['url'] == null || $jsonbuttoninfo [$i] [$j] ['url'] == ''))
					$this->ajaxReturn ( array (
							'status' => '0',
							'errortype' => '如果是view类型的话，请填入网页链接'
					), 'json' );
				// return '如果是view类型的话，请填入网页链接';
			}
		}
		$this->ajaxReturn ( array (
				'status' => '1',
				'errortype' => '您的菜单没有错误'
		), 'json' );
	}
	
	/**
	 * 清除该商家所有菜单。
	 * @return boolean $clearresult 清空菜单的结果
	 */
	public function syncClearAllMenu() {
		$clearresult = false; // 清除所有菜单结果
		$clearmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$clearresult = M ( 'customizedmenu' )->where ( $clearmap )->delete (); // 删除该商家的所有菜单
		return $clearresult;
	}
	
	/**
	 * ==========Part3：主动调用代码同步级联菜单区域=========
	 */
	
	/**
	 * 本地自动同步微信端菜单函数。
	 */
	public function autoSyncWechatMenu() {
		$autosyncresult = false; // 自动同步菜单结果
		// Step1：查询企业的自定义菜单（如果有）
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$menulist = $menutable->where ( $emap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子、从小到大
		$localmenu = $this->formatCascadeMenu ( $menulist );
		
		// step1:从腾讯服务器获得菜单信息
		$einfo = $_SESSION ['curEnterprise']; // 当前企业信息
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$menujson = $swc->queryMenu ( $_SESSION ['curEnterprise'] ); // 查询该企业的微信公众号菜单
		if ($menujson) {
			$menuinfo = $menujson ['menu'] ['button'];
			// Step2：解码成本地菜单格式
			$newmenulist = $this->disassembleJsonMenu ( $menuinfo, true ); // 解码微信菜单（本地数据库格式）
			$remotemenu = $this->formatCascadeMenu ( $newmenulist );
			
			$autosyncresult = $this->syncRemoteMenu ( $remotemenu, $localmenu ); // 级联菜单同步
		} 
		return $autosyncresult;
	}
	
	/**
	 * 远程同步菜单函数，特别注意：不管是远程还是本地菜单，一定要级联，可调用formatCascadeMenu进行级联格式化。
	 * 先调用distinguishMenu对远程菜单和本地菜单进行一个变动识别，然后再分别删除、更新和新增菜单。
	 * @param array $remotemenu 远程标杆级联菜单（不仅可以是微信数据，也可以是微动页面过来的数据）
	 * @param array $localmenu 本地要更新的级联菜单
	 * @return boolean $syncresult 同步远程菜单和本地菜单的结果，true为成功，false为失败
	 */
	public function syncRemoteMenu($remotemenu = NULL, $localmenu = NULL) {
		$syncresult = false; // 默认同步远程和本地菜单结果失败
		$distinguishresult = $this->distinguishMenu ( $remotemenu, $localmenu ); // 调用菜单分拣函数distinguishMenu，挑出远程菜单和本地菜单的add、update、delete三个数组
		// 先设置删除、更新和新增的标记
		$deleteresult = false; // 要删除的标记结果
		$updateresult = false; // 要更新的标记结果
		$addresult = false; // 要新增的标记结果
		if (! empty ( $distinguishresult )) {
			// Step1：先删除
			if (! empty ( $distinguishresult ['delete'] )) {
				$deleteresult = $this->syncDeleteMenu ( $distinguishresult ['delete'] ); // 先删除
			} else {
				$deleteresult = true; // 不删除
			}
			// Step2：再更新
			if (! empty ( $distinguishresult ['update'] )) {
				$updateresult = $this->syncUpdateMenu ( $distinguishresult ['update'] ); // 再更新
			} else {
				$updateresult = true; // 不更新
			}
			// Step3：最后新增
			if (! empty ( $distinguishresult ['add'] )) {
				$addresult = $this->syncAddMenu ( $distinguishresult ['add'] ); // 最后新增
			} else {
				$addresult = true; // 不新增
			}
		} else {
			$deleteresult = true; // 直接不删除
			$updateresult = true; // 直接不更新
			$addresult = true; // 直接不新增
		}
		if ($deleteresult && $updateresult && $addresult) {
			$syncresult = true; // 3个过程都执行成功，才算更新成功
		}
		return $syncresult;
	}
	
	/**
	 * ==========Part4：托管调用代码区分、同步菜单区域=========
	 */
	
	/**
	 * 测试通过黑盒代码：托管调用函数：为本地菜单同步新增菜单，传入本地数据库菜单格式的新增菜单二维数组列表。
	 */
	private function syncAddMenu($addmenulist = NULL) {
		$addresult = false; // 默认添加失败
		if (! empty ( $addmenulist )) {
			$addcount = count ( $addmenulist ); // 计算要新增的菜单数量
			$batchaddlist = array (); // 批量插入数组
			// 循环生成批量插入的数据
			for ($i = 0; $i < $addcount; $i ++) {
				// Part1：基本信息
				$batchaddlist [$i] = array (
						'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
						'level' => $addmenulist [$i] ['level'],
						'type' => $addmenulist [$i] ['type'],
						'name' => $addmenulist [$i] ['name'],
						'key' => $addmenulist [$i] ['key'],
						'url' => $addmenulist [$i] ['url'],
						'sibling_order' => $addmenulist [$i] ['sibling_order'],
						'add_time' => time () 
				);
				// Part2：级联信息（非常重要的代码段）
				if ($addmenulist [$i] ['level'] == 0) {
					$batchaddlist [$i] ['menu_id'] = $addmenulist [$i] ['menu_id']; // 如果是新的父级菜单，用比对的时候赋予的menu_id作为主键
					$batchaddlist [$i] ['father_menu_id'] = "-1"; // 如果是新的父级菜单，上级菜单是-1
				} else {
					$batchaddlist [$i] ['menu_id'] = md5 ( uniqid ( rand (), true ) ); // 如果是新的子级菜单，新生成一个menu_id作为主键
					$batchaddlist [$i] ['father_menu_id'] = $addmenulist [$i] ['father_menu_id']; // 如果是子级菜单，用比对的时候赋予的father_menu_id作为级联
				}
			}
			$addresult =  M ( 'customizedmenu' )->addAll ( $batchaddlist ); // 批量插入微信自定义菜单表中（至少一条）
		}
		return $addresult;
	}
	
	/**
	 * 测试通过黑盒代码：托管调用函数：为本地菜单同步更新菜单。
	 */
	private function syncUpdateMenu($updatemenulist = NULL) {
		$updateresult = false; // 默认更新失败
		if (! empty ( $updatemenulist )) {
			$updatecount = count ( $updatemenulist ); // 计算要更新的菜单数量
			$menutable = M ( 'customizedmenu' ); // 表对象
			$updatetotal = 0; // 总更新数量
			for($i = 0; $i < $updatecount; $i ++) {
				// 更新位置
				$updatemap = array (); // 每次循环都清空
				$updatemap ['menu_id'] = $updatemenulist [$i] ['menu_id']; // 要更新的菜单主键
				$updatemap ['is_del'] = 0; // 没有被删除的
				// 更新内容
				$updateinfo = array ();
				$updateinfo ['level'] = $updatemenulist [$i] ['level'];
				$updateinfo ['father_menu_id'] = $updatemenulist [$i] ['father_menu_id'];
				$updateinfo ['type'] = $updatemenulist [$i] ['type'];
				$updateinfo ['name'] = $updatemenulist [$i] ['name'];
				$updateinfo ['key'] = $updatemenulist [$i] ['key'];
				$updateinfo ['url'] = $updatemenulist [$i] ['url'];
				$updateinfo ['sibling_order'] = $updatemenulist [$i] ['sibling_order'];
				// 更新成功叠加
				$updatetotal += $menutable->where ( $updatemap )->save ( $updateinfo ); // 更新回去，如果成功，成功数量+1
			}
			$updateresult = $updatetotal; // 更新结果
		}
		return $updateresult;
	}
	
	/**
	 * 测试通过黑盒代码：托管调用函数：为本地菜单同步删除菜单。
	 */
	private function syncDeleteMenu($deletemenulist = NULL) {
		$deleteresult = false; // 默认更新失败
		if (! empty ( $deletemenulist )) {
			$deletecount = count ( $deletemenulist ); // 统计要删除的菜单数量
			$deleteidlist = ""; // 要删除的主键编号传承字符串
			$menutable = M ( 'customizedmenu' ); // 表对象
			for ($i = 0; $i < $deletecount; $i ++) {
				$deleteidlist .= $deletemenulist [$i] ['menu_id'] . ","; // 拼接主键id和英文逗号
			}
			if (! empty ( $deleteidlist )) {
				$deleteidlist = substr ( $deleteidlist, 0, strlen ( $deleteidlist ) - 1 ); // 去除末尾的英文逗号
				$deletemap = array (
						'menu_id' => array ( 'in', $deleteidlist ), // 在所列要删除菜单列表中的
						'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家下
						'is_del' => 0 // 未曾被删除的菜单
				);
				$deleteresult = $menutable->where ( $deletemap )->delete (); // 真的删除这些菜单
			}
		}
		return $deleteresult;
	}
	
	/**
	 * 黑盒代码：区分出远程（标杆）菜单对比本地（待更新）菜单哪里变动的函数，以二维数组格子化放置菜单后区分。
	 * 
	 * 格子数组设计理念图：（参见微信公众号菜单）
	 * -------------------------------
	 * |  (1,1)  |  (2,1)  |  (3,1)  |    子级菜单level1
	 * -------------------------------
	 * |  (1,2)  |  (2,2)  |  (3,2)  |    子级菜单level2
	 * -------------------------------
	 * |  (1,3)  |  (2,3)  |  (3,3)  |    子级菜单level3
	 * -------------------------------
	 * |  (1,4)  |  (2,4)  |  (3,4)  |    子级菜单level4
	 * -------------------------------
	 * |  (1,5)  |  (2,5)  |  (3,5)  |    子级菜单level5
	 * -------------------------------
	 * |  (0,1)  |  (0,2)  |  (0,3)  |    父级菜单level
	 * -------------------------------
	 *  父级菜单1(左) 父级菜单2(中) 父级菜单3(右)
	 *  
	 * @param array $remotemenu 远程菜单（以此菜单为准），数组需要级联
	 * @param array $localmenu 本地数据库菜单（需要以远程菜单为依据），数组需要级联
	 * @return array $distinguishresult 挑出菜单变动结果（origin,add,update,del4个菜单）
	 */
	public function distinguishMenu($remotemenu = NULL, $localmenu = NULL) {
		$distinguishresult = array (); // 区分菜单的结果
		$originmenulist = array (); // 没有变更的菜单
		$addmenulist = array (); // 新增的菜单
		$updatemenulist = array (); // 改动的菜单
		$deletemenulist = array (); // 要删除的菜单
		// 如果远程菜单和本地菜单都不空
		if (! empty ( $remotemenu ) && ! empty ( $localmenu )) {
			// Step1：创建格子（必须有，不要遍历到null值）
			$jmbox = array (); // 二维格子数组
			for($i = 1; $i <= 3; $i ++) {
				$jmbox [0] [$i] = array (); // 父级菜单最多3个，开辟父级菜单的数组空间
				for ($j = 1; $j <= 5; $j ++) {
					$jmbox [$i] [$j] = array (); // 子级菜单最多5个，开辟子级菜单的数组空间
				}
			}
			// Step2：将本地（级联）菜单放入格子中
			foreach ($localmenu as $singlefathermenu) {
				// 循环父级菜单
				$tempfather = $singlefathermenu; // copy一份副本
				unset ( $tempfather ['children'] ); // 注销该变量
				$jmbox [0] [$singlefathermenu ['sibling_order']] = $tempfather; // 父级菜单放入格子中
				if (! empty ( $singlefathermenu ['children'] )) {
					// 如果该父级菜单有孩子菜单
					foreach ($singlefathermenu ['children'] as $singlechildmenu) {
						$jmbox [$singlefathermenu ['sibling_order']] [$singlechildmenu ['sibling_order']] = $singlechildmenu; // 子级菜单放入格子中
					}
				}
			}
			// Step3：依照远程菜单对格子进行比对
			foreach ($remotemenu as $onefathermenu) {
				$tempremoteone = $onefathermenu; // copy 一份父级菜单的副本$tempremoteone
				unset ( $tempremoteone ['children'] ); // 注销该$tempremoteone父级菜单的children变量
				// 比对父级菜单
				$becompared = $jmbox [0] [$onefathermenu ['sibling_order']]; // 尝试寻找当前被比对的父级菜单对象
				if (! empty ( $becompared )) {
					// 情形一：如果对象不空，代表现在有，原来也有（但有可能会改动）
					$becompared ['need_remain'] = 1; // 都存在，该条菜单要保留（反向遍历的时候不会被删掉）
					$tempremoteone ['menu_id'] = $becompared ['menu_id']; // 很关键的一句话：如果比对出来是更改的，则把原来的主键给过去方便更新
					if ($becompared ['name'] == $onefathermenu ['name'] && $becompared ['type'] == $onefathermenu ['type'] && $becompared ['key'] == $onefathermenu ['key'] && $becompared ['url'] == $onefathermenu ['url']) {
						array_push ( $originmenulist, $tempremoteone ); // 将当前比对的远程父级菜单加入未变更菜单列表中
					} else {
						array_push ( $updatemenulist, $tempremoteone ); // 将当前比对的远程父级菜单加入需变更菜单列表中
					}
					$jmbox [0] [$onefathermenu ['sibling_order']] = $becompared; // 将比对过的记录放回格子里（带上字段need_remain==1）
				} else {
					// 情形二：如果对象空，代表这是一条新增的父级菜单，此时要新生成主键
					array_push ( $addmenulist, $tempremoteone ); // 将当前比对的远程父级菜单加入新增菜单列表中
				}
				// 尝试比对该父级菜单的孩子菜单（如果有的话）
				if (! empty ( $onefathermenu ['children'] )) {
					// 如果该父级菜单还有孩子菜单，继续比对孩子菜单
					foreach ($onefathermenu ['children'] as $onechildmenu) {
						// ========很关键的代码（处理新增必须的）=========
						if (! empty ( $becompared )) {
							$onechildmenu ['father_menu_id'] = $becompared ['menu_id']; // 如果原来有菜单，老父级菜单的编号给孩子的上级菜单
						} else {
							$onechildmenu ['father_menu_id'] = $onefathermenu ['menu_id']; // 原来没有老父级菜单，直接用新菜单$onefathermenu的menu_id
						}
						// ========很关键的代码（处理新增必须的）========
						$childbecompared = $jmbox [$onefathermenu ['sibling_order']] [$onechildmenu ['sibling_order']]; // 尝试寻找当前被比对的父级菜单对象
						if (! empty ( $childbecompared )) {
							// 如果对象不空，代表现在有，原来也有（但有可能会改动）
							$childbecompared ['need_remain'] = 1; // 都存在，该条孩子菜单要保留（反向遍历的时候不会被删掉）
							$onechildmenu ['menu_id'] = $childbecompared ['menu_id']; // 很关键的一句话：如果比对出来是更改的，则把原来的主键给过去方便更新
							if ($childbecompared ['name'] == $onechildmenu ['name'] && $childbecompared ['type'] == $onechildmenu ['type'] && $childbecompared ['key'] == $onechildmenu ['key'] && $childbecompared ['url'] == $onechildmenu ['url']) {
								array_push ( $originmenulist, $onechildmenu ); // 将当前比对的远程父级菜单加入未变更菜单列表中
							} else {
								array_push ( $updatemenulist, $onechildmenu ); // 将当前比对的远程父级菜单加入需变更菜单列表中
							}
							$jmbox [$onefathermenu ['sibling_order']] [$onechildmenu ['sibling_order']] = $childbecompared; // 将比对过的记录放回格子里（带上字段need_remain==1）
						} else {
							// 如果对象空，代表这是一条新增的
							array_push ( $addmenulist, $onechildmenu ); // 将当前比对的远程子级菜单加入新增菜单列表中
						}
					}
				}
			}
			// Step4：反向遍历一边格子，找到可能要删除的本地菜单
			for($i = 1; $i <= 3; $i ++) {
				if (! empty ( $jmbox [0] [$i] )) {
					// 父级菜单不空，处理可能要删除的父级菜单
					if (! isset ( $jmbox [0] [$i] ['need_remain'] )) {
						$temphandle = $jmbox [0] [$i]; // copy 一份副本
						unset ( $temphandle ['children'] ); // 注销孩子变量
						array_push ( $deletemenulist, $temphandle ); // 将要删除的父级菜单加入删除数组中
					}
					// 父级菜单不空，则处理可能要删除的孩子菜单
					for ($j = 1; $j <= 5; $j ++) {
						if (! empty ( $jmbox [$i] [$j] ) && ! isset ( $jmbox [$i] [$j] ['need_remain'] )) {
							array_push ( $deletemenulist, $jmbox [$i] [$j] ); // 如果子级菜单存在且没有打上标记，将要删除的子级菜单加入删除数组中
						}
					}
				}
			}
		} else if (empty ( $remotemenu ) && ! empty ( $localmenu )) {
			// 如果远程菜单空，但是本地菜单不空，说明都要删除，都加入$deletemenulist中
			$deletemenulist = $this->reverseCascadeMenu ( $localmenu ); // 本地菜单拆了级联
		} else if (! empty ( $remotemenu ) && empty ( $localmenu )) {
			// 如果远程菜单不空，但是本地菜单空，说明都要新增，都加入$addmenulist中
			$addmenulist = $this->reverseCascadeMenu ( $remotemenu ); // 远程菜单拆了级联
		}
		// 最终做打包处理
		$distinguishresult = array (
				'origin' => $originmenulist,
				'add' => $addmenulist,
				'update' => $updatemenulist,
				'delete' => $deletemenulist
		);
		return $distinguishresult;
	}
	
	/**
	 * ==========Part5：4个工具函数区域=========
	 * 1、组装本地数据库查询得到的二维数组形式打包给微信或前台的json所需要的形式数组；
	 * 2、拆解微信远程数据或前台送来的json数据格式数组为本地数据库二维数组形式（非级联菜单）；
	 * 3、将形如本地数据库查询得到的二维数组形式组装成级联菜单形式；
	 * 4、将级联菜单形式拆解成本地数据库二维数组形式。
	 */
	
	/**
	 * 黑盒代码：将数据库的数组菜单组装成json需要的数组格式，能重载完成微信端的数据打包和微动页面视图的数据打包。
	 * @param array $dbmenulist 数据库原始菜单数据，格式必须是：先父后子、从小到大（本函数首先要变成本地父子级联菜单数组）
	 * @param boolean $towechat 是否打包成微信需要的json格式，默认是false打包给前台页面
	 * @return array $assemblemenulist 最终打包好的数组格式，能直接进行json_encode。
	 */
	public function assembleDBMenu($dbmenulist = NULL, $towechat = FALSE) {
		$assemblemenulist = array (); // 组装后的菜单列表
		if (! empty ( $dbmenulist )) {
			// Step1：现将数据库查询出来的菜单变成级联菜单
			$packagemenu = $this->formatCascadeMenu ( $dbmenulist );
			// Step2：再组装打包成json需要的格式
			if ($towechat) {
				// 如果需要将数据打包给微信，遍历层级菜单，进行微信打包
				foreach ($packagemenu as $wechatfatherkey => $wechatfathermenu) {
					$singlebutton = array (); // 单个父级菜单的button按钮
					if (! empty ( $wechatfathermenu ['children'] )) {
						// 该父级菜单下还有子级菜单
						$singlebutton ['name'] = $wechatfathermenu ['name']; // 只取父级菜单名字
						$singlebutton ['sub_button'] = array (); // 为它的孩子菜单开辟数组空间
						foreach ($wechatfathermenu ['children'] as $wechatchildkey => $wechatchildmenu) {
							// 先看类型
							$tempchildtype = $wechatchildmenu ['type']; // 类型
							$tempchildkey = $wechatchildmenu ['key']; // 事件
							$tempchildurl = $wechatchildmenu ['url']; // 地址
							if (empty ( $tempchildtype ) || ( empty ( $tempchildkey ) && empty ( $tempchildurl ) )) {
								// 容错处理：如果没有类型，或者key和url都空，则强制性规定菜单类型和事件
								$childbutton = array (
										'name' => $wechatchildmenu ['name'], // 名字
										'type' => "view", // 默认URL类型
										'url' => "http://www.we-act.cn/weact/Home/Index/index/e_id/" . $wechatfathermenu ['e_id'] // 默认跳转官网
								);
							} else {
								// 正常处理
								$childbutton = array (
										'name' => $wechatchildmenu ['name'], // 名字
										'type' => $wechatchildmenu ['type'] // 类型
								);
								// 再判断key和url要哪个
								if ($wechatchildmenu ['type'] == 'click') {
									$childbutton ['key'] = $wechatchildmenu ['key']; // 如果是点击推送图文类型的
								} else if ($wechatchildmenu ['type'] == 'view') {
									$childbutton ['url'] = $wechatchildmenu ['url']; // 如果是点击跳转URL类型的
								} else {
									// 其他新类型，暂留空，2015/03/26 20:14:25
								}
							}
							array_push ( $singlebutton ['sub_button'], $childbutton ); // 将子菜单加入父节点的孩子中
						}
					} else {
						$temptype = $wechatfathermenu ['type']; // 类型
						$tempkey = $wechatfathermenu ['key']; // 事件
						$tempurl = $wechatfathermenu ['url']; // 地址
						if (empty ( $temptype ) || ( empty ( $tempkey ) && empty ( $tempurl ) )) {
							// 容错处理：如果没有类型，或者key和url都空，则强制性规定菜单类型和事件
							$singlebutton = array (
									'name' => $wechatfathermenu ['name'], // 名字
									'type' => "view", // 默认URL类型
									'url' => "http://www.we-act.cn/weact/Home/Index/index/e_id/" . $wechatfathermenu ['e_id'] // 默认跳转官网
							);
						} else {
							// 正常处理
								
							// 该父级菜单下没有子级菜单
							$singlebutton = array (
									'name' => $wechatfathermenu ['name'], // 名字
									'type' => $wechatfathermenu ['type'] // 类型
							);
							// 再判断key和url要哪个
							if ($wechatfathermenu ['type'] == 'click') {
								$singlebutton ['key'] = $wechatfathermenu ['key']; // 如果是点击推送图文类型的
							} else if ($wechatfathermenu ['type'] == 'view') {
								$singlebutton ['url'] = $wechatfathermenu ['url']; // 如果是点击跳转URL类型的
							} else {
								// 其他新类型，暂留空，2015/03/26 20:14:25
							}
						}
					}
					array_push ( $assemblemenulist, $singlebutton ); // 将原始（或组装好的）父级菜单$singlebutton压入最终总菜单列表
				}
				// 发送给微信，还需要在菜单外边包装一层button
				$wrapmenu ['button'] = $assemblemenulist;
				unset ( $assemblemenulist );
				$assemblemenulist = $wrapmenu;
			} else {
				// 如果需要将数据打包给前台页面
				foreach ($packagemenu as $singlefatherkey => $singlefathermenu) {
					$tempfathermenu = array (
							'text' => $singlefathermenu ['name'], // 菜单名
							'id' => $singlefathermenu ['menu_id'], // 菜单编号
							'type' => $singlefathermenu ['type'], // 菜单类型
							'key' => $singlefathermenu ['key'], // 事件值（如果有）
							'url' => $singlefathermenu ['url'] // 跳转URL（如果有）
					);
					if (! empty ( $singlefathermenu ['children'] )) {
						// 如果孩子菜单不为空
						$tempfathermenu ['children'] = array (); // 为当前父级菜单数组children字段开辟孩子菜单数组
						foreach ($singlefathermenu ['children'] as $singlechildkey => $singlechildmenu) {
							$tempchildmenu = array (
									'text' => $singlechildmenu ['name'], // 菜单名
									'id' => $singlechildmenu ['menu_id'], // 菜单编号
									'type' => $singlechildmenu ['type'], // 菜单类型
									'key' => $singlechildmenu ['key'], // 事件值（如果有
									'url' => $singlechildmenu ['url'] // 跳转URL（如果有）
							);
							array_push ( $tempfathermenu ['children'], $tempchildmenu ); // 将当前孩子菜单push到父级菜单中
						}
					}
					array_push ( $assemblemenulist, $tempfathermenu ); // 将当前带孩子的父级菜单push到最终数组中
				}
			}
		}
		return $assemblemenulist;
	}
	
	/**
	 * 黑盒代码：将json数据解码后的二维数组菜单拆解成数据库需要的DB数组格式进行比对。
	 * @param array $remotemenulist 远程菜单数组
	 * @param boolean $fromwechat 从微信来的二维数组
	 * @return array $finalmenulist 最终打包好的数组格式，能直接进行json_encode。
	 */
	public function disassembleJsonMenu($remotemenulist = NULL, $fromwechat = FALSE) {
		$disassemblemenulist = array (); // 拆解成本地菜单后的菜单列表
		if (! empty ( $remotemenulist )) {
			if ($fromwechat) {
				// 如果从微信来的json解压数据
				$fatherorder = 1; // 父级菜单同级排序，从1开始
				foreach ($remotemenulist as $wechatfatherkey => $wechatfathermenu) {
					// Step1：要拆解的父级菜单，先将自己装入拆解菜单（先不考虑类型，放到后边做）
					$tempfather = array (
							'menu_id' => md5 ( uniqid ( rand (), true ) ), // 随机一个md5码作为主键，不然关联子菜单有问题
							'name' => $wechatfathermenu ['name'], // 菜单名字
							'level' => 0, // 父级菜单的level层级是0
							'father_menu_id' => "-1", // 父级菜单的father_menu_id永远是-1
							'sibling_order' => $fatherorder ++ // 父级菜单同级顺序
					);
					// 再将自己的孩子装入拆解菜单
					if (! empty ( $wechatfathermenu ['sub_button'] )) {
						// 如果发来的菜单本父级菜单有孩子菜单
						$tempfather ['type'] = null; // 本地：有孩子就无菜单类型
						$tempfather ['key'] = null; // 本地：有孩子就无菜单key
						$tempfather ['url'] = null; // 本地：有孩子就无菜单url
						array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单在孩子前加入拆解菜单中
						// 继续操作子级菜单
						$childorder = 1; // 子级菜单同级排序，也从1开始
						foreach ($wechatfathermenu ['sub_button'] as $wechatchildkey => $wechatchildmenu) {
							$tempchild = array (
									'name' => $wechatchildmenu ['name'], // 菜单名字
									'level' => 1, // 子级菜单的level层级是1
									'father_menu_id' => $tempfather ['menu_id'], // 子级菜单的father_menu_id不是-1（方便做层级关联）
									'type' => $wechatchildmenu ['type'], // 菜单类型
									'sibling_order' => $childorder ++ // 子级菜单同级顺序
							);
							if ($wechatchildmenu ['type'] == "click") {
								$tempchild ['key'] = $wechatchildmenu ['key']; // 图文click
								$tempchild ['url'] = null; // 无url
							} else if ($wechatchildmenu ['type'] == "view") {
								$tempchild ['url'] = $wechatchildmenu ['url']; // 菜单url
								$tempchild ['key'] = null; // 无key
							} else {
								// 新菜单类型，2015/03/27 02:10:25.
							}
							array_push ( $disassemblemenulist, $tempchild ); // 将子级菜单加入拆解菜单中
						}
					} else {
						// 如果父级菜单没有孩子菜单
						$tempfather ['type'] = $wechatfathermenu ['type']; // 注意：没有孩子就有type，有孩子就没有type
						if ($wechatfathermenu ['type'] == "click") {
							$tempfather ['key'] = $wechatfathermenu ['key']; // 图文click
							$tempfather ['url'] = null; // 无url
						} else if ($wechatfathermenu ['type'] == "view") {
							$tempfather ['url'] = $wechatfathermenu ['url']; // 菜单url
							$tempfather ['key'] = null; // 无key
						} else {
							// 新菜单类型，2015/03/27 02:10:25.
						}
						array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单加入拆解菜单中
					}
				}
			} else {
				// 如果从微动公众号菜单页面视图来的解压数据，直接解压成最原始的数据库格式二维数组（如果需要级联菜单，使用组装级联菜单函数）。
				$fatherorder = 1; // 父级菜单同级排序，从1开始
				foreach ($remotemenulist as $remotefatherkey => $remotefathermenu) {
					// Step1：要拆解的父级菜单，先将自己装入拆解菜单
					$tempfather = array (
							'name' => $remotefathermenu ['text'], // 菜单名字
							'menu_id' => $remotefathermenu ['id'], // 菜单编号
							'father_menu_id' => "-1", // 父级菜单是没有上级菜单编号的
							'level' => 0, // 父级菜单的level层级是0
							'type' => $remotefathermenu ['type'], // 菜单类型
							'key' => $remotefathermenu ['key'], // 菜单key（如果有）
							'url' => $remotefathermenu ['url'], // 菜单url（如果有）
							'sibling_order' => $fatherorder ++ // 父级菜单同级顺序
					);
					array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单加入拆解菜单中
					// 再将自己的孩子装入拆解菜单
					if (! empty ( $remotefathermenu ['children'] )) {
						// 如果发来的菜单本父级菜单有孩子菜单
						$childorder = 1; // 子级菜单同级排序，也从1开始
						foreach ($remotefathermenu ['children'] as $remotechildkey => $remotechildmenu) {
							$tempchild = array (
									'name' => $remotechildmenu ['text'], // 菜单名字
									'menu_id' => $remotechildmenu ['id'], // 菜单编号
									'father_menu_id' => $tempfather ['menu_id'], // 当前子级菜单的父级菜单编号是当前外层循环菜单
									'level' => 1, // 子级菜单的level层级是1
									'type' => $remotechildmenu ['type'], // 菜单类型
									'key' => $remotechildmenu ['key'], // 菜单key（如果有）
									'url' => $remotechildmenu ['url'], // 菜单url（如果有）
									'sibling_order' => $childorder ++ // 子级菜单同级顺序
							);
							array_push ( $disassemblemenulist, $tempchild ); // 将子级菜单加入拆解菜单中
						}
					}
				}
			}
		}
		return $disassemblemenulist;
	}
	
	/**
	 * 黑盒代码：将微动本地数据库的二维数组菜单格式化成级联菜单的格式。
	 * 特别注意：使用本函数时，必须将父级菜单排在子级菜单前边，且同级菜单sibling_order从小到大（tp:->order ( 'level asc, sibling_order asc' )）。
	 * @param array $localmenulist 本地二维数组菜单列表
	 * @return array $cascademenulist 格式化后的级联菜单数组
	 */
	public function formatCascadeMenu($localmenulist = NULL) {
		$cascademenulist = array (); // 级联菜单数组
		if (! empty ( $localmenulist )) {
			$listnum = count ( $localmenulist ); // 计算总菜单数量
			for ($i = 0; $i < $listnum; $i ++) {
				// 因为按照level asc排序的，所以先取到的一定是父级菜单
				if ($localmenulist [$i] ['father_menu_id'] == "-1") {
					// 遍历到父级菜单，将其menu_id作为一个索引
					if (! is_array ( $cascademenulist [$localmenulist [$i] ['menu_id']])) {
						$cascademenulist [$localmenulist [$i] ['menu_id']] = $localmenulist [$i]; // 将父级菜单放进到他自己menu_id索引中
					}
				} else {
					// 遍历到子级菜单，将其放入父级菜单的数组中，但是注意顺序
					$fatherid = $localmenulist [$i] ['father_menu_id']; // 父级菜单menu_id，取出来方便后人理解
					$selforder = $localmenulist [$i] ['sibling_order']; // 自己在同级菜单中的顺序，取出来方便后人理解
					if (! is_array ( $cascademenulist [$fatherid] ['children'])) {
						$cascademenulist [$fatherid] ['children'] = array (); // 如果当前父级菜单还没有孩子菜单，开辟这个孩子菜单数组
					}
					array_push ( $cascademenulist [$fatherid] ['children'], $localmenulist [$i] ); // 放入这个数组中（这里数组下标从1开始，因为数据库同级顺序从1开始）
					//$cascademenulist [$fatherid] ['children'] [$selforder] = $localmenulist [$i]; // 放入这个数组中（这里数组下标从1开始，因为数据库同级顺序从1开始）
				}
			}
		}
		return $cascademenulist;
	}
	
	/**
	 * 黑盒代码：将formatCascadeMenu函数打包后的级联菜单拆解成本地数据库格式的菜单。
	 * @param array $cascademenulist 级联菜单
	 * @return array $localmenulist 本地菜单二维数组格式
	 */
	public function reverseCascadeMenu($cascademenulist = NULL) {
		$localmenulist = array (); // 本地菜单数组
		if (! empty ( $cascademenulist )) {
			foreach ($cascademenulist as $singlefathermenu) {
				$tempfather = $singlefathermenu;
				unset ( $tempfather ['children'] );
				array_push ( $localmenulist, $tempfather ); // 将父级菜单压入本地菜单中
				if (! empty ( $singlefathermenu ['children'] )) {
					// 如果该父级菜单还有孩子菜单，遍历该父节点的孩子菜单
					foreach ($singlefathermenu ['children'] as $singlechildmenu) {
						$singlechildmenu ['menu_id'] = md5 ( uniqid ( rand (), true ) ); // 赋予其孩子菜单主键
						array_push ( $localmenulist, $singlechildmenu ); // 将孩子菜单压入本地菜单中
					}
				}
			}
		}
		return $localmenulist;
	}
	
	/**
	 * ==========Part6：以下为代码测试区域=========
	 */
	
	/**
	 * 测试disassembleJsonMenu函数能否正常解码本地页面菜单数据。
	 */
	public function testDisassemble() {
		$json = '[{
		"text": "公众号自定义菜单",
		"id":"-1",
		"expanded": true,
		"children":[{"text":"O2O商城","id":"menu00001","type":null,"key":null,"url":null,"children":[{"text":"餐饮","id":"menu00004","type":"view","key":null,"url":"http://www.we-act.cn/weact/CateIndustry/MenuView/menu/e_id/201406261550250006/nav_id/33adc56235495770730c5ab7c3cfdecb.shtml"},{"text":"服饰","id":"menu00005","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/CustomerView/showNavList/e_id/201406261550250006/nav_id/325f8de929243e0cfbecd32c40bbcb92.shtml"},{"text":"家居","id":"menu00006","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/CustomerView/showNavList/e_id/201406261550250006/nav_id/335c36c5e829446a0364d408ace34b38.shtml"},{"text":"我的菜单","id":"menu00007","type":"view","key":null,"url":"http://www.we-act.cn/weact/CateIndustry/CateOrder/historyOrder/e_id/201406261550250006"},{"text":"我的购物车","id":"menu00008","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/CartView/shoppingCart/e_id/201406261550250006"}]},{"text":"会员中心","id":"menu00002","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/MemberHandle/customerCenter/e_id/201406261550250006"},{"text":"微平台","id":"menu00003","type":null,"key":null,"url":null,"children":[{"text":"微官网","id":"menu00009","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/Index/index/e_id/201406261550250006/"},{"text":"微商城","id":"menu00010","type":"view","key":null,"url":"http://www.we-act.cn/weact/Home/CustomerView/showNavList/e_id/201406261550250006/nav_id/325f8de929243e0cfbecd32c40bbcb92.shtml"},{"text":"关于微动","id":"menu00012","type":"click","key":"about_weact","url":null}]}]	}]';
	
		$remotemenu = json_decode ( $json, true ); // json_decode解码测试数据
		$disassembleresult = $this->disassembleJsonMenu ( $remotemenu [0] ['children'] ); // 调用disassembleJsonMenu函数解码成本地菜单
		$cascademenu = $this->formatCascadeMenu ( $disassembleresult ); // 级联的菜单
		p($disassembleresult);p($cascademenu);die;
	}
	
	/**
	 * 测试微信方面菜单disassembleJsonMenu函数。
	 */
	public function testWechatDisassemble() {
		// step1:从腾讯服务器获得菜单信息
		$einfo = $_SESSION ['curEnterprise']; // 当前企业信息
		import ( 'Class.API.WeChatAPI.ThinkWechat', APP_PATH, '.php' ); // 导入WeChat的SDK
		$weixin = new ThinkWechat ( $einfo ['e_id'], $einfo ['appid'], $einfo ['appsecret'] ); // 实例化微信SDK类对象$weixin
		$menujson = $weixin->queryMenu ();
		$menuinfo = $menujson ['menu'] ['button'];
		// Step2：解码成本地菜单格式
		$newmenulist = $this->disassembleJsonMenu ( $menuinfo, true ); // 解码微信菜单（本地数据库格式）
		$cascademenulist = $this->formatCascadeMenu ( $newmenulist );
		p($newmenulist);p($cascademenulist);die;
	}
	
	/**
	 * 测试区分菜单不同点的代码。
	 */
	public function testSyncMenu() {
		// Step1：查询企业的自定义菜单（如果有）
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$menutable = M ( 'customizedmenu' );
		$menulist = $menutable->where ( $emap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子、从小到大
		$localmenu = $this->formatCascadeMenu ( $menulist );
		
		// step1:从腾讯服务器获得菜单信息
		$einfo = $_SESSION ['curEnterprise']; // 当前企业信息
		import ( 'Class.API.WeChatAPI.ThinkWechat', APP_PATH, '.php' ); // 导入WeChat的SDK
		$weixin = new ThinkWechat ( $einfo ['e_id'], $einfo ['appid'], $einfo ['appsecret'] ); // 实例化微信SDK类对象$weixin
		$menujson = $weixin->queryMenu ();
		$menuinfo = $menujson ['menu'] ['button'];
		// Step2：解码成本地菜单格式
		$newmenulist = $this->disassembleJsonMenu ( $menuinfo, true ); // 解码微信菜单（本地数据库格式）
		$remotemenu = $this->formatCascadeMenu ( $newmenulist );
		
		$result = $this->syncRemoteMenu ( $remotemenu, $localmenu );
		if ($result) {
			p("success!");die;
		} else {
			p("failed!");die;
		}
	}
	
}
?>