<?php
/**
 * 微社区主页控制器。
 * @author 赵臣升。
 * 
 */
class MicroCommunityAction extends CommunityGuestAction {
	/**
	 * 微社区视图。
	 */
	public function myCommunity() {
		$sc = A ( 'Service/Community' ); // 实例化服务层
		$topicdata = $sc->getDataByPage ( $this->site_id, 0, true ); // 确实是第一次请求数据
		$finaljson = json_encode ( $topicdata );
		$finaljson = str_replace('"', '\\"', $finaljson); // 特别注意：第一次直接渲染的时候要对双引号进行转义
		$this->firstpage = $finaljson; // 推送第一页json数据
		$this->display ();
	}
	
	/**
	 * 微社区分页请求话题数据post处理函数。
	 */
	public function getTopicByPage() {
		$sc = A ( 'Service/Community' );
		$ajaxinfo = array (
				'site_id' => $_REQUEST ['sId'],
				'e_id' => $_REQUEST ['e_id'],
				'start' => $_REQUEST ['start'],
				'resType' => $_REQUEST ['resType'],
				'isAjax' => $_REQUEST ['isAjax'] 
		);
		$jsoninfo = $sc->getDataByPage ( $ajaxinfo ['site_id'], $ajaxinfo ['start'] );
		$finaljson = json_encode ( $jsoninfo ); // 特别注意：第二次以上渲染的时候不需要对双引号转义
		echo $finaljson;
	}
	
	/**
	 * 点赞话题post处理函数。
	 */
	public function likeTopic() {
		$ajaxinfo = array (
				'site_id' => I ( 'sId' ),
				'e_id' => I ( 'e_id' ),
				'post_id' => I ( 'tId' ),
				'csrf_token' => I ( 'CSRFToken' ),
				'openid' => $_SESSION ['currentwechater'] [$this->e_id] ['openid'] 
		);
		
		$sc = A ( 'Service/Community' );
		$likeresult = $sc->praiseTopic ( $ajaxinfo ['site_id'], $ajaxinfo ['e_id'], $ajaxinfo ['post_id'], $ajaxinfo ['openid'] ); // 处理两步赞同步，然后再返回总赞数量
		if ($likeresult) {
			$response = array (
					'errCode' => 0,
					'message' => '已赞',
					'data' => array (
							'likeNumber' => $likeresult 
					),
					'showLogin' => null,
					'jumpURL' => null,
					'locationTime' => 2000 
			);
			$this->ajaxReturn ( $response );
		}
	}
	
	/**
	 * 回复社区主页提交处理post函数。
	 */
	public function replyConfirm() {
		$swc = A('Service/WeChat');
		$sc = A('Service/Community');
		$ajaxinfo = array (
				'CSRFToken' => I ( 'CSRFToken' ),
				'site_id' => $_REQUEST ['sId'],
				'e_id' => $_REQUEST ['e_id'], // 接受商家编号
				'post_id' => I ( 'tId' ), // 接收帖子编号
				'pId' => I ( 'pId' ),
				'content' => I ( 'content' ), // 接收帖子内容
				'picIds' => I ( 'picIds' ), // 图片数组（全是图片id）
				'reply_type' => I ( 'type' ), // 在社区主页提交的都是对帖子的回复，所以类型都是0
				'openid' => $_SESSION ['currentwechater'] [$this->e_id] ['openid'] 
		);
		// p($ajaxinfo);die;
		$postmap = array (
				'site_id' => $ajaxinfo ['site_id'],
				'post_id' => $ajaxinfo ['post_id'],
				'is_del' => 0 
		);
		$postinfo = M ( 'communitypost' )->where ( $postmap )->find ();
		// p($postinfo);die;
		
		// 处理当前用户信息
		$currentwechater = $swc->getUserInfo( $this->einfo, $this->openid ); //获取当前用户数据
		if($currentwechater ['subscribe'] == 0) {
			$currentwechater ['nickname'] = '游客';
			$currentwechater ['headimgurl'] = 'APP/Modules/Community/Tpl/Public/Images/defaulthead.jpg'; // 使用默认头像
		}
		
		$main_content = $sc->checkEmotion($ajaxinfo ['content']);
		
		// 处理主回复
		$replyinfo = array (
				'reply_id' => md5 ( uniqid ( rand (), true ) ),
				'postreply_id' => $ajaxinfo ['post_id'],
				'site_id' => $ajaxinfo ['site_id'],
				'e_id' => $ajaxinfo ['e_id'],
				'reply_type' => $ajaxinfo ['reply_type'],
				'reply_group_id' => randCode ( 4, 1 ), // 随机一个组号
				'reply_level' => 0,
				'replier_id' => $currentwechater ['openid'],
				'replier' => $currentwechater ['nickname'],
				'be_replied_id' => $postinfo ['post_author_id'],
				'be_replied' => $postinfo ['post_author'],
				'reply_content' => $main_content,
				'reply_time' => time () 
		);
		
		$picturelist = array (); // 声明回复的图片数组变量
		// 处理回复图片
		if (! empty ( $ajaxinfo ['picIds'] )) {
			$pic = $ajaxinfo ['picIds'];
			for($i = 0; $i < count ( $pic ); $i ++) {
				$picturelist [$i] = array (
						'image_id' => md5 ( uniqid ( rand (), true ) ),
						'postreply_id' => $ajaxinfo ['post_id'],
						'site_id' => $ajaxinfo ['site_id'],
						'e_id' => $ajaxinfo ['e_id'],
						'image_path' => '/Updata/images/' . $ajaxinfo ['e_id'] . '/community/postinfo/' . date ( 'Ymd' ) . '/' . $pic [$i],
						'belong_type' => 1, // 这里属于回复的图片，只有发帖子的地方才是belong_type = 0
						'add_time' => time () 
				);
			}
		}
		// 调用回复函数进行回复
		$replyresult = $this->replyTopic ( $replyinfo, $picturelist );
		if ($replyresult) {
			import ( 'Class.BusinessLogic.Community.ForumDataFormat', APP_PATH, '.php' ); // 载入业务逻辑层格式化微社区数据类
			$fdf = new ForumDataFormat (); // 新建格式化对象
			$replynum = $this->countPostReply ( $ajaxinfo ['post_id'] );
			$formatresult = $fdf->formatReplyData ( $replyinfo, $replynum );
			$this->ajaxReturn ( $formatresult );
		}
	}
	
	/**
	 * 回复话题微博函数。
	 * 
	 * @param string $replyinfo        	
	 * @param string $picturelist        	
	 * @return boolean $replyresult	回复提交成功结果
	 */
	public function replyTopic($replyinfo = NULL, $picturelist = NULL) {
		$replyresult = false; // 回复成功结果
		$replyresult = M ( 'communityreply' )->add ( $replyinfo );
		if ($replyresult && ! empty ( $picturelist )) {
			$picturelist = M ( 'communityimage' )->addAll ( $picturelist );
		}
		return $replyresult;
	}
	
	/**
	 * 统计帖子被回复的总数目。
	 * 
	 * @param string $post_id        	
	 * @return number $replytotal	返回该帖子被回复的总数目
	 */
	public function countPostReply($post_id = '') {
		$replytotal = 0; // 总得回复数
		$checkcount = array (
				'postreply_id' => $post_id,
				'reply_type' => 0, // 对帖子回复
				'is_del' => 0 
		);
		$replytotal = M ( 'communityreply' )->where ( $checkcount )->count ();
		return $replytotal;
	}
	
	
	
	
	public function delTopic() {
		p ( I ( '' ) );
		die ();
	}
	
	
}
?>