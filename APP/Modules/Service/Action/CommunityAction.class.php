<?php
/**
 * 微社区服务管理层。
 * @author 赵臣升。
 *
 */
class CommunityAction extends Action {
	
	
	
	/**
	 * 论坛公有化部分服务。
	 * 1、验证CSRFToken串是否正确；
	 * 2、匹配表情函数。
	 */
	
	/**
	 * 匹配替换表情函数。
	 * @param string $content 帖子或回复的正文。
	 * @return string $newcontent 进行表情匹配后的帖子或回复正文。
	 */
	public function checkEmotion($content = '') {
		import( 'Class.Common.Emotion.EmotionMobile', APP_PATH, '.php' );
		$em = new EmotionMobile();
		return $em->replaceEmotion( $content );
	}
	
	/**
	 * 论坛数据显示及初始化部分服务函数。
	 */
	
	/**
	 * 根据站点编号和下一条记录开始的下标来获取微博数据函数getDataByPage。
	 * 只返回data字段里的信息，这样可以统和首次和二次的操作。
	 *
	 * 实现思路：如果动态请求下一条微博的开始为0，代表是下拉刷新操作，这个时候请求的是首页记录，所以要考虑置顶微博的情况，其他$nextStart的情况不用考虑置顶微博。
	 *
	 * @param number $site_id 社区站点编号
	 * @param number $nextStart 下一条记录的下标
	 * @param boolean $firstInitData 是否页面打开请求数据标记
	 * @return array $topiclist 微博话题的列表
	 */
	public function getDataByPage($site_id = 0, $nextStart = 0, $firstInitData = FALSE){
		// 定义本函数内全局常量和变量
		$father_table = M ( 'communitypost' );				// 主表：帖子表
		$child_talble = M ( 'communityreply' );				// 子表：回复表
		$image_table = M ( 'communityimage' );				// 帖子图片表
		$CONST_MAX_PER_PAGE = 10;							// 定义一页最多请求主题数目（10条）
		$sticklist = array();								// 置顶微博信息列表
		$sticknum = 0;										// 置顶微博信息数目
		$noStickTopicNeedNum = 10;							// 还需要请求的非置顶微博数量（默认没有置顶微博）
		$topiclist = array();								// 当前页面的微博信息列表
		$newNextStart = 0;									// 请求成功后，下一次请求的话题开始下标
		$nosticklist = array();								// 请求成功的普通话题列表
		$replyinfo = array();								// 定义reply数组
		$imageinfo = array();								// 定义image数组
		$newmsgcount = 0;									// 当前用户的新消息提醒数目
		$finalreturn = array();								// 本函数本次请求结束的返回数据
	
		// 考虑到是否请求第一页数据的情况
		if($nextStart == 0) {
			$sticklist = $this->getStickTopic($site_id);		// 拉取置顶微博信息
			$sticknum = count( $sticklist );				// 统计置顶微博数量
			$noStickTopicNeedNum = $CONST_MAX_PER_PAGE - $sticknum;		// 还需要请求的非置顶微博数量
			// 有置顶微博的情况下，压入topicinfo数组里
			if(! empty( $sticklist )) {
				for($i = 0; $i < $sticknum; $i ++) {
					array_push($topiclist, $sticklist [$i]);			// 把置顶微博信息push到topic数组里
				}
			}
		}
		
		// 根据要请求的微博数据量$noStickTopicNeedNum，去读取（剩下的）微博数据，但在读取前要判断是否有那么多微博信息
		$requestinfo = $this->getNormalTopic( $site_id, $nextStart, $noStickTopicNeedNum );		// 获取非置顶的话题信息和下一页开始的数组信息
		$newNextStart = $requestinfo ['nextStart'];			// 下一次话题请求的开始下标
		$nosticklist = $requestinfo ['normallist'];			// 本次请求的非置顶话题列表
		if(! empty( $nosticklist )) {
			for($i = 0; $i < count( $nosticklist ); $i ++) {
				array_push($topiclist, $nosticklist [$i]);	// 把非置顶的微博信息push到topic数组里
			}
		}
		
		// 至此，本次请求的话题主要列表已经获得
	
		// 查找当前页的topic话题的回复与附图（顺便组装路径）
		for($i = 0; $i < count( $topiclist ); $i ++) {
			$checkreply = array(
					'postreply_id' => $topiclist [$i] ['post_id'], //字段有变更
					'reply_type' => 0, //查询回复帖子本身的回复
					'is_del' => 0
			);
			$checkimage = array(
					'postreply_id' => $topiclist [$i] ['post_id'], //字段有变更
					'belong_type' => 0, //查询回复帖子本身的回复
					'is_del' => 0
			);
			$replyinfo [$i] = $child_talble->where( $checkreply )->select();				//查找出帖子回复，本来想以帖子主键作为哈希索引，方便随机存取||想想还是用下标，方便循环组装，一个循环变量取3个东西
			$imageinfo [$i] = $image_table->where( $checkimage )->select();				//查找出帖子附图
			if(! empty( $imageinfo [$i] )) {
				for($j = 0; $j < count( $imageinfo [$i] ); $j ++) {
					$imageinfo [$i] [$j] ['image_path'] = assemblepath( $imageinfo [$i] [$j] ['image_path'] );	//顺便组装话题图片路径
				}
			}
		}
		// 更新最新回复消息（如果有），在communityreply表中checked==0的代表的是还没有被查看过的回复
		$msgcheck = array(
				'be_replied_id' => $currentwechater,
				'checked' => 0,															//没有被查看过的
				'is_del' => 0
		);
		$newmsgcount = $child_talble->where( $msgcheck )->count();						//统计出新消息
	
		// p($topiclist);p($replyinfo);p($imageinfo);die;
		// 至此，本次请求成功的话题列表，回复列表，以及话题的图片信息，都已经得到，下一步进行打包封装
	
		// 开始使用ForumDataFormat格式化当前页topic、回复、附图这些数据
		import( 'Class.BusinessLogic.Community.ForumDataFormat', APP_PATH, '.php' );	//载入业务逻辑层格式化微社区数据类
		$fdf = new ForumDataFormat();													//新建格式化对象
	
		if($firstInitData) {
			$finalreturn = $fdf->createPageData($topiclist, $replyinfo, $imageinfo, $newNextStart, $newmsgcount);	// 如果是初始化请求数据，这样打包数据
		} else {
			$finalreturn = $fdf->formatData($topiclist, $replyinfo, $imageinfo, $newNextStart, $newmsgcount);		// 如果是下拉刷新、上拽之类的请求，这样打包数据
		}
	
		// 返回数据
		return $finalreturn;
	}
	
	/**
	 * 获取某商家的置顶的帖子（最多5条，并按时间降序返回话题列表）。
	 * @param number $site_id 站点编号
	 * @return array $sticktopiclist 置顶话题列表
	 */
	public function getStickTopic($site_id = 0) {
		$sticklist = array();			// 置顶的微博列表
		$stickcheck = array(
				'site_id' => $site_id,
				'is_stick' => 1,		// 置顶的
				'is_del' => 0
		);
		$sticklist = M('communitypost')->where($stickcheck)->order('post_time desc')->limit(5)->select();		// 找出最近5条置顶的微博
		return $sticklist;
	}
	
	/**
	 * 请求非置顶话题的函数getNormalTopic。
	 * 如果说非置顶话题数量$normalnum有100条，那么最大允许的$nextStart开始话题下标就是99（数组下标从0开始）。
	 *
	 * @param number $site_id 商家微社区编号
	 * @param number $nextStart 下一条起始微博的数组下标（从0开始）
	 * @param number $needNumber 需要请求多少条normal话题数据
	 * @return array $finalinfo 本次请求的非置顶话题信息列表和下一条话题的起始下标
	 */
	public function getNormalTopic($site_id = 0, $nextStart = 0, $needNumber = 0) {
		$finalinfo = array();			// 最终返回的数组信息
		$normallist = array();			// 普通微博列表
		$normalcheck = array(
				'site_id' => $site_id,
				'is_stick' => 0,		// 不置顶的普通话题
				'is_del' => 0
		);
		$normalnum = M('communitypost')->where($normalcheck)->count();		// 先统计出普通话题数目（不需排序）
	
		$topicleftnum = $normalnum - $nextStart;		// 计算剩下需要请求的话题数目，比如统计出100条话题，下一条开始话题下标是97，最大下标99，则还有3条数据可请求：97,98,99
	
		$realGetNumber = ( $topicleftnum >= $needNumber ) ? $needNumber : $topicleftnum;	// 剩下的话题还大于等于需要请求的话题数目吗，有的话，就满足需求，没有就按照剩下的数目请求
	
		$newNextStart = $nextStart + $realGetNumber;	// 本次如果请求成功，下一次再请求话题开始的下标
	
		if($realGetNumber) {
			$normallist = M('communitypost')->where($normalcheck)->order('post_time desc')->limit($nextStart, $realGetNumber)->select();	// 请求可请求的话题，limit的第二个参数是length
		}
	
		$finalinfo ['nextStart'] = $newNextStart;		// 下一次请求开始
		$finalinfo ['normallist'] = $normallist;		// 本次请求的非置顶话题列表
	
		return $finalinfo;
	}
	
	/**
	 * 用户操作论坛部分服务函数。
	 */
	
	/**
	 * 对某帖子点赞处理。
	 * @param number $site_id 站点编号
	 * @param string $e_id 企业编号
	 * @param string $post_id 帖子编号
	 * @param string $liker 点赞用户编号
	 * @return boolean $likeoperated 点赞是否成功，成功返回数量，失败返回0，代表false。
	 */
	public function praiseTopic($site_id = 0, $e_id = '', $post_id = '', $liker = '') {
		$likeoperated = 0; // 点赞完毕标识
		// Step1：先往postreplylike表里插入一条点赞记录
		$newlike = array(
				'like_id' => md5(uniqid( rand(), true)),
				'site_id' => $site_id,
				'e_id' => $e_id,
				'postreply_id' => $post_id,
				'liker' => $liker,
				'like_type' => 0,
				'like_time' => time()
		);
		$likestep1 = M('postreplylike')->data($newlike)->add();
		// Step2：原来的帖子里点赞数量+1
		$likemap = array(
				'post_id' => $post_id,
				'site_id' => $site_id,
				'is_del' => 0
		);
		$likestep2 = M ( 'communitypost' )->where($likemap)->setInc('praise_count', 1);
		if($likestep1 && $likestep2) {
			$likeinfo = M ( 'communitypost' )->where($likemap)->find();
			$likeoperated = $likeinfo ['praise_count'];
		}
		return $likeoperated;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>