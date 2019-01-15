<?php
class ForumDataFormat {
	
	/**
	 * 主调函数，将本地数据格式化为前台所需数据，仅是数组格式及字段之间的转换，json请在控制器转。
	 * @param array $topicinfo	主题信息
	 * @param array $replyinfo	回复信息
	 * @param array $imageinfo	主题附带图片信息
	 * @param number $newmsginfo	当前用户新消息信息
	 * @return array	$formatdata	返回格式化的数据
	 */
	public function formatData($topicinfo = NULL, $replyinfo = NULL, $imageinfo = NULL, $newNextStart = 0, $newmsginfo = 0) {
		$formatdata = array(
				'errCode' => 0,
				'message' => '',
				'data' => $this->createPageData($topicinfo, $replyinfo, $imageinfo, $newNextStart, $newmsginfo),
				'showLogin' => null,
				'jumpURL' => null,
				'locationTime' => 2000
		);
		return $formatdata;
	}
	
	/**
	 * 产生最终数组里的data数据。
	 * @param string $topicinfo
	 * @param string $replyinfo
	 * @param string $imageinfo
	 * @param number $newNextStart
	 * @param number $newmsginfo
	 */
	public function createPageData($topicinfo = NULL, $replyinfo = NULL, $imageinfo = NULL, $newNextStart = 0, $newmsginfo = 0) {
		$pagedata = array(
				'threadList' => $this->formatThreadList($topicinfo, $replyinfo, $imageinfo),
				'nextStart' => $newNextStart,
				'newMsgCount' => $newmsginfo,
				'threadCount' => 0,									//同时有多少线程???
				'liveThreadCount' => 203,								//活跃线程有几个???
				'sitePV' => 0,										//site站点的page信息被刷新总次数（每打开一次就算刷新一次）
				'groupStar' => 2,
				'verifyDeveloper' => 1,
				'verifyStar' => 2,
				'fId' => 0,
				'isLive' => false,
				'enabledSmiley' => 1,									//允许使用手机版QQ表情
				'siteRankListTopThree' => $this->siteRankTopThree(),	//站点前三名信息
				'site' => $this->siteInfo()								//站点自身信息
		);
		return $pagedata;
	}
	
	/**
	 * 格式化回复数据。
	 * @param array $replyinfo	本次回复的数据信息
	 * @param number $topicreplycount	总得帖子的回复信息
	 * @return array $replyjson
	 */
	public function formatReplyData($replyinfo = NULL, $topicreplycount = 0) {
		$replyformat = array(
				'errCode' => 0,
				'message' => '回复成功',
				'data' => array(
						'avatar' => '/weact/Updata/images/201406261550250006/community/defaulthead.jpg',	//先使用默认图片
						'author' => '游客',
						'authorUid' => $replyinfo ['replier_id'],
						'groupStar' => 2,
						'verifyDeveloper' => 1,
						'verifyStar' => 2,
						'content' => $replyinfo ['reply_content'],
						'tId' => $replyinfo ['post_id'],
						'pId' => randCode( 4, 1 ),
						'toPId' => null,
						'toAuthor' => null,
						'isVerify' => null,
						'isLZ' => false,
						'verifyType' => 0,
						'rCount' => $topicreplycount,
						'authorHonor' => 0,
						'authorExpsNum' => 6,
						'authorExpsRank' => 2
				),
				'showLogin' => null,
				'jumpURL' => null,
				'locationTime' => 2000
		);
		return $replyformat;
	}
	
	/**
	 * 格式化前三名的信息。
	 */
	private function siteRankTopThree() {
		$topthree = array();
		return $topthree;
	}
	
	/**
	 * 格式化站点信息。
	 */
	private function siteInfo() {
		$siteinfo = array();
		return $siteinfo;
	}
	
	/**
	 * 格式化threadList里的东西。
	 * @param array $topicinfo	主题信息
	 * @param array $replyinfo	回复信息
	 * @param array $imageinfo	主题附带图片信息
	 * @return array	$threadList	返回格式化的threadList数据
	 */
	private function formatThreadList($topicinfo = NULL, $replyinfo = NULL, $imageinfo = NULL) {
		$threadList = array();			//总的threadList数组
		$singlethread = array();		//单一的singlethread
		for($i = 0; $i < count( $topicinfo ); $i ++) {
			$singlethread = array();	//单一的singlethread，每次使用就清空
			$singlethread = array(
					'authorUid' => $topicinfo [$i] ['post_author_id'],					//话题帖子作者编号
					'author' => $topicinfo [$i] ['post_author'],						//作者名称
					//'avatar' => $topicinfo [$i] ['head_img_url'],						//作者头像，最好每次动态获取（现在先伪造一个字段）
					'avatar' => '/weact/Updata/images/201406261550250006/community/defaulthead.jpg',	//先使用默认图片
					'authorGender' => 0,												//对于性别统一默认为0
					'LBSInfo' => $topicinfo [$i] ['lbs_info'],							//作者的地理位置信息
					'tId' => $topicinfo [$i] ['post_id'],								//帖子编号
					'fId' => 0,															//不明字段，先处理为0
					'sId' => $topicinfo [$i] ['e_id'],									//商家编号（e_id）
					'parentId' => 0,													//不明字段，先处理为0
					'rCount' => count( $replyinfo [$i] ),								//本话题的回复数，特别注意$replyinfo是三维数组（一条回复信息算一维）
					'threadType' => 0,													//不明字段，先处理为0
					'title' => '',														//标题，貌似只有conteng，暂时置空
					'summary' => $topicinfo [$i] ['post_content'],						//话题内容content
					'hCreatedTime' => '<em>' . $this->howlong( $topicinfo [$i] ['post_time'] ) . '</em>',	//本话题发表创建时间
					'hLastPostTime' => '<em>' . $this->howlong( $topicinfo [$i] ['post_time'] ) . '</em>',	//作者最后一次发表话题时间
					'isStick' => intval($topicinfo [$i] ['is_stick']),					//是否置顶
					'showMore' => false,												//默认只显示3层回复
					'liveId' => 0,														//不明字段，先处理为0
					'hideMQ' => 0,														//不明字段，先处理为0
					'joinNumber' => 0,													//不明字段，先处理为0
					'joinUser' => array(),												//不明字段，看格式貌似是空数组
					'isEnd' => 0,														//是否最后一篇话题（注意检查，现在先不处理）
					'likeNum' => intval($topicinfo [$i] ['praise_count']),				//话题的点赞数目
					'shareLink' => $this->shareCommunity( $topicinfo [$i] ['e_id'] ),	//社区分享链接，暂时先这么做
					'share' => $this->shareQQZone( $topicinfo [$i] ['e_id'], $topicinfo [$i] ['post_id'], $topicinfo [$i] ['post_content'], $imageinfo [$i], $topicinfo [$i] ['head_img_url'] ),	//分享到QQ空间的信息
					'picNum' => count( $imageinfo [$i] ),								//本话题的附图数，特别注意$imageinfo是三维数组（一条附图算一维）
					'picUrls' => $this->postImage( $imageinfo [$i] ),					//帖子话题附图信息数组
					'videos' => array(),												//暂时没有视频，先置空
					'weishiInfo' => array(),											//暂时没有微视，先置空
					'showPicType' => 0,													//不明字段，先处理为0
					'closeJoin' => 0,													//不明字段，先处理为0
					'closeUpdate' => 0,													//不明字段，先处理为0
					'peId' => 0,														//不明字段，先处理为0
					'worldCup' => 0,													//是否世界杯
					'appId' => 0,														//不明字段，先处理为0
					'appName' => "",													//不明字段，先置空
					'authorHonor' => 0,													//不明字段，先处理为0
					'authorExps' => array()												//注意处理细节
			);
			
			if($singlethread ['rCount'] > 0) {
				// 处理回复人的信息：
				$cReplyinfo = $replyinfo [$i];				// 信息取出来
				$replycount = count( $cReplyinfo );			// 计算本条微博的回复数
				$replylist = array();						// 本条微博的回复列表
				for($j = 0; $j < $replycount; $j ++) {
					// 开始给单条回复格式化
					$isLZ = ( $cReplyinfo [$j] ['replier_id'] == $singlethread ['authorUid'] ) ? 1 : 0;
					$replylist [$j] = array(
							'tId' => $cReplyinfo [$j] ['post_id'],
							'pId' => $cReplyinfo [$j] ['reply_group_id'],	//???是否回复的回复???
							'isLZ' => $isLZ,
							'authorUid' => $cReplyinfo [$j] ['replier_id'],
							'author' => $cReplyinfo [$j] ['replier'],
							'avatar' => '/weact/Updata/images/201406261550250006/community/defaulthead.jpg',
							'content' => $cReplyinfo [$j] ['reply_content']
					);
				}
					
				$singlethread ['replyList'] = $replylist;	// 附加当前微博的回复表
			}
			
			//p($singlethread);die;
			//p( json_encode($singlethread) );die;
			$threadList [$i] = $singlethread;
		}
		return $threadList;
	}
	
	/**
	 * 检测发微博的时间距今多久，返回中文语言的时间差。
	 * @param number $checktime
	 * @return string $howlong
	 */
	private function howlong($checktime = 0) {
		$howlong = '暂无时间信息';					//定义相隔多久的变量
		$timenow = time();						//获取当前时间
		$timespan = $timenow - $checktime;		//计算时间差（秒数，整型）
		if($timespan > 0) {
			if($timespan < 60) {
				$howlong = '刚刚';
			} else if($timespan >= 60 && $timespan < 3600) {
				$howlong = intval ( $timespan/60 ) . '分钟前';
			} else if($timespan >= 3600 && $timespan < 86400) {
				$howlong = intval ( $timespan/3600 ) . '小时前';
			} else if($timespan >= 86400 && $timespan < 604800) {
				$howlong = intval ( $timespan/86400 ) . '天前';
			} else if($timespan >= 604800 && $timespan < 2419200) {
				$howlong = intval ( $timespan/86400 ) . '周前';
			} else if($timespan >= 2419200) {
				$howlong = date('Y-m-d H:i:s', $checktime);	//超过一个月直接显示时间
			} 
		}
		return $howlong;
	}
	
	/**
	 * 分享微社区。
	 */
	private function shareCommunity($e_id = '') {
		$shareURL = 'http://www.we-act.cn/weact/Community/MicroCommunity/myCommunity/e_id/' . $e_id;
		return $shareURL;
	}
	
	/**
	 * 分享到QQ空间的信息。
	 * @param string $e_id	商家编号
	 * @param string $pid	话题帖子编号
	 * @param string $content	话题内容
	 * @param string $shareimg	分享图标（如果有附图，就用第一张附图，如果没有附图，就用作者头像）
	 * @param string $headimage	作者头像
	 * @return array	$QQZoneShareURL分享在QQ空间的信息
	 */
	private function shareQQZone($e_id = '', $pid = '', $content = '', $imageinfo = NULL, $headimage = '') {
		$shareimg = '';										//最终要分享的图片
		if(! empty( $imageinfo )) {
			$shareimg = $imageinfo [0] ['image_path'];		//如果有附图，就用第一张附图信息
		} else {
			$shareimg = $headimage;							//没有附图用作者的头像
		}
		$detailURL = 'http://www.we-act.cn/weact/Community/MicroCommunity/topicDetail/e_id/' . $e_id . '/pid/' . $pid;
		$title = '快来看看这个话题';
		$targetURL = $this->shareCommunity($e_id);
		$QQZoneShareURL = $detailURL . '&title=' . $title . '&summary=' . $content . '&targetUrl=' . $targetURL . '&source=connect_qzone&pageUrl=' . $detailURL . '&imageUrl=' . $shareimg . '&t=0&type=qzone';
		return array(
				'qq' => '',
				'qzone' => $QQZoneShareURL
		);
	}
	
	/**
	 * 返回主题的图片信息数组。
	 * @param string $imageinfo	原始的主题图片数组
	 * @return array	$postimage	格式化的主题图片信息
	 */
	private function postImage($imageinfo = NULL) {
		$postimage = array();
		if(! empty( $imageinfo )) {
			for($i = 0; $i < count( $imageinfo ); $i ++) {
				$postimage [$i] = array(
						'small' => $imageinfo [$i] ['image_path'],
						'big' => $imageinfo [$i] ['image_path'],
						'middle' => $imageinfo [$i] ['image_path'],
						'large' => $imageinfo [$i] ['image_path']
				);
			}
		}
		return $postimage;
	}
	
	
	
	
	
	
	
	
	
	
	
	
}
?>