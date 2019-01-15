<?php
/**
 * 此控制器处理微平台中的自定义回复模块。
 * @author 张华庆。
 * @optimized author 赵臣升。
 */
class AutoResponseAction extends PCViewLoginAction {
	
	/**
	 * 首次关注页面函数。
	 * 展示页面的时候同时在autoresponse表里查好商家预设定的一些值。
	 */
	public function firstFocus() {
		// Step1：初始化图文选择框里的图文信息
		$allnewsmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 					// 找到当前登录商家下
				'is_del' => 0
		);
		// 做视图拼接，查找主图文标题及对应的编号。msgnews表是主表，别名p(parent)；msgnewsdetail表是子表，别名c(child)。
		$sql = 'p.msgnews_id = c.msgnews_id and p.is_del = 0 and c.is_del = 0 and p.e_id = \'' . $allnewsmap ['e_id'] . '\' and c.detail_order = -1 '; // select框里的内容是每个图文的封面消息
		$model = new Model ();
		$allnews = $model->table ( 't_msgnews p, t_msgnewsdetail c' )->where ( $sql )->order ( 'p.add_time, c.detail_order asc' )->field ( 'p.msgnews_id, c.title' )->select (); // 查出全部图文的封面消息标题及对应的图文编号（详细内容在页面上post再查）
	
		// Step2：初始化回复信息：
		/**
		 * 此处设计理念：
		 * a)option_value用来标识类型;
		 * b)response_content_id用来标识选中的图文;
		 * c)response_info用来表示文本回复的内容，如果没有任何设定，默认是text且欢迎关注...
		*/
		$responseinfo = array (
				'option_value' => 'text', 											// 默认是text类型，option_value同时也是response.type
				'selected_news' => $allnews [0] ['msgnews_id'], 					// 默认选中的图文编号，初始化用图文表中查到的所有图文信息第一条表示
				'text_info' => '欢迎关注' . $_SESSION ['curEnterprise'] ['e_name']  	// 默认没设置的欢迎语
		);
	
		// Step3：查询首次关注信息，缩写autoresponse→ar
		$firstmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'response_type' => 'subscribe', 									// 响应类型为subscribe的回复方式
				'is_del' => 0  // 没有被删除的
		);
		$artable = M ( 'autoresponse' );
		$arresult = $artable->where ( $firstmap )->find (); 						// 尝试去找首次关注回复的记录
	
		// Step4：如果有预设定，则更改初始化info数组的值
		if ($arresult) {
			// 如果找到回复方式，则商家记录中已经定义了response_function和response_content_id
			$responseinfo ['option_value'] = substr ( $arresult ['response_function'], 8 ); // 提取商家的回复类型
			if ($responseinfo ['option_value'] == 'news') {
				$responseinfo ['selected_news'] = $arresult ['response_content_id']; 		// 如果首次关注方式是图文，设置新闻选择框选中的主键
			} else {
				// 如果首次关注方式是文本，无需设置selected_news，但是要推送是链接还是图文的标记msg_type
				$textmap = array (
						'msgtext_id' => $arresult ['response_content_id'], 					// 回复文本的主键
						'e_id' => $allnewsmap ['e_id'],
						'is_del' => 0
				);
				$textinfo = M ( 'msgtext' )->where ( $textmap )->find (); 					// 用主键去查找文本信息表里的信息
				if ($textinfo ['msg_type'] == 1) {
					$responseinfo ['option_value'] = 'link'; 								// 如果文本的类型为链接，修改默认属性option_value为link
				}
				$responseinfo ['text_info'] = json_encode ( $textinfo ['content'], true ); 	// 将首次关注文本信息放入$responseinfo['text_info']，无论是text还是link
			}
		}
		// 特别注意：一定要对textinfo进行jsencode处理，否则无法显示，json_encode怎么有问题？
		$this->responseinfo = $responseinfo; 										// responseinfo：推送前台显示的选择信息
		$this->option_news = $allnews; 												// option_news：推送前台图文信息
		$this->display ();
	}
	
	/**
	 * 所有已设置关键字回复一览
	 */
	public function keywordsReplyView() {
		$this->display();
	}
	
	/**
	 * 添加关键字视图页面。
	 */
	public function addKeyword(){
		// 初始化图文选择框里的图文信息
		$allnewsmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 找到当前登录商家下
				'detail_order' => -1, 							// select框里只要主图文
				'is_del' => 0
		);
		$allnews = M ( 'msgnews_info' )->where ( $allnewsmap )->order ( 'add_time desc, detail_order asc' )->field ( 'msgnews_id, title' )->select (); // 查出全部图文的封面消息标题及对应的图文编号（详细内容在页面上post再查）
		
		$this->option_news = $allnews; 							// option_news：推送前台图文信息
		$this->display();
	}
	
	/**
	 * 查看、编辑关键字当前信息
	 */
	public function editKeyword(){
		$aid = I ( 'aid' ); // 接受参数
		
		// 读取要编辑的关键字信息
		$automap = array (
				'autoresponse_id' => $aid,
				'is_del' => 0
		);
		$autoinfo = M ( 'autoresponse' )->where ( $automap )->find (); // 尝试从自动回复表里查找关键字回复
		if (! $autoinfo) {
			$this->error ( "所编辑的关键字已不存在。" );
		}
		
		//根据回复类型去不同的消息表读取关键字回复信息
		$autofun = $autoinfo ['response_function']; // 回复类型
		$autoconid = $autoinfo ['response_content_id']; // 回复消息编号
		$restype = substr ( $autofun, 8 ); 	// 提取回复类型type
		$autoinfo ['restype'] = $restype;
		
		if ($autofun == 'responsetext') {
			$textmap = array (
					'msgtext_id' => $autoconid,
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家
					'is_del' => 0
			);
			$textinfo = M ( 'msgtext' )->where ( $textmap )->find ();
			if ($textinfo ['msg_type'] == 1) {
				$autoinfo['restype'] = 'link';
			}
			//$autoinfo ['text_info'] = json_encode ( $textinfo ['content'], true ); // 怕html格式出错可用，勿删
			$autoinfo ['text_info'] = $textinfo ['content']; 
		} else if ($autofun == 'responseimage' || $autofun == 'responsevoice' || $autofun == 'responsevideo') {
			$pkfield = "msg" . $restype . "_id"; 	// 拼接多媒体主键
			$mediatable = M ( "msg" . $restype ); 	// 消息类型
			$mediamap = array (
					$pkfield => $autoconid, // 主键
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家
					'is_del' => 0
			);
			$mediainfo = $mediatable->where ( $mediamap )->find (); // 查询出多媒体信息
			$autoinfo ['media_path'] = assemblepath ( $mediainfo ['local_path'] ); // 组装路径
		} else if ($autofun == 'responsenews') {
			$autoinfo ['selected_news'] = $autoconid;
		}
		
		// 初始化图文选择框里的图文信息
		$allnewsmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 找到当前登录商家下
				'detail_order' => -1, 							// select框里只要主图文
				'is_del' => 0
		);
		$allnews = M ( 'msgnews_info' )->where ( $allnewsmap )->order ( 'add_time desc, detail_order asc' )->field ( 'msgnews_id, title' )->select (); // 查出全部图文的封面消息标题及对应的图文编号（详细内容在页面上post再查）
		
		$this->option_news = $allnews; 							// option_news：推送前台图文信息
		$this->autoinfo = $autoinfo;
		
		$this->display();
	}
	
}
?>