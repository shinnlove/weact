<?php
class ActivityAction extends MobileGuestAction {
	function getMyActivity() {
		$activity_id = I ( 'activity_id' );
		$e_id = I ( 'e_id' );
		$data = array (
			'activity_id' => $activity_id,
			'e_id' => $e_id,
			'is_del' => 0
		);
		//对满足条件的优惠券进行显示处理
		$activitylist = M ( "activity" )->where ( $data )->select ();
		if ($activitylist [0] ['activity_type'] == 0)
			$activitylist [0] ['activity'] = '直接打' . $activitylist [0] ['discount'] . '折';
		else if ($activitylist [0] ['activity_type'] == 1)
			$activitylist [0] ['activity'] = '直接减免' . $activitylist [0] ['discount'] . '元';
		else if ($activitylist [0] ['activity_type'] == 2) {
			$map = array (
				'activity_id' => $activitylist [0] ['activity_id'] 
			);
			$ladderdeductlist = M ( 'ladderdeduct' )->where ( $map )->select ();
			for($j = 0; $j < count ( $ladderdeductlist ); $j += 1)
				$activitylist [0] ['activity'] .= '满' . $ladderdeductlist [$j] ['reach_capacity'] . '元,减' . $ladderdeductlist [$j] ['alleviate_amount'] . '元  ';
		}
		$this->activity = $activitylist;
		$this->display ();
	}
	
	public function enroll() {
		$pinfo ['shareinfo'] = $this->getActivityShareInfo();
		$this->pageinfo = $pinfo;
		$this->display ();
	}
	
	public function expo() {
		$jsondata = $this->getDataByPage ( $this->einfo ['e_id'], 0, true );
		$ajaxinfo = json_encode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		// 推送分享信息
		$pinfo ['shareinfo'] = $this->getActivityShareInfo();
		
		// 尝试找到自己的enroll_id，方便进入详情
		$selfid = ''; // 默认当前用户没有报名过
		$selfmap = array (
				'e_id' => $this->einfo ['e_id'],
				'openid' => $openid,
				'is_del' => 0
		);
		$selfinfo = M ( 'enrollwish' )->where ( $selfmap )->find ();
		if ($selfinfo) $selfid = $selfinfo ['enroll_id'];
		
		$this->pageinfo = $pinfo;
		$this->selfid = $selfid;
		$this->openid = $openid;
		$this->jsondata = $finaljson;
		$this->display ();
	}
	
	/**
	 * 活动详情视图。
	 */
	public function detail() {
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		$liketable = M ( 'wishlike' );
		// Step1：得到详情
		$wishmap = array (
				'enroll_id' => $_REQUEST ['wid'],
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$detailinfo = M ( 'activityenroll' )->where ( $wishmap )->find ();
		if (! $detailinfo) $this->redirect( 'Home/Activity/expo', array('e_id' => $this->einfo ['e_id']) );
		$detailinfo ['photo_path'] = assemblepath ( $detailinfo ['photo_path'] );
		$detailinfo ['add_time'] = timetodate ( $detailinfo ['add_time'] );
		// Step2：自己是否点过赞
		$todaystart = todaystart(); // 今天开始
		$todayend = todayend(); // 今天截止
		$selflikemap = array (
				'enroll_id' => $wishmap ['enroll_id'],
				'e_id' => $this->einfo ['e_id'],
				'liker' => $openid,
				'like_time' => array ( array( 'gt', $todaystart ), array( 'lt', $todayend ) ),
				'is_del' => 0
		);
		$selfliked = $liketable->where ( $selflikemap )->count (); // 统计自己有没有点过赞
		// Step3：得到点赞数量
		$likenum = $liketable->where ( $wishmap )->count ();
		// Step4：得到详情分享信息
		$pinfo ['shareinfo'] = $this->getDetailShareInfo ( $detailinfo );
		// Step5：如果有同类，随机推送同类
		$samemap = array (
				'enroll_id' => array ( 'neq', $wishmap ['enroll_id'] ), // 将自己排除在外，查询同类的祝福
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$samewishlist = M ( 'activityenroll' )->where ( $samemap )->limit ( 3 )->order ( 'rand()' )->select ();
		$samenumber = count ( $samewishlist ); // 统计同类数量
		for($i = 0; $i < $samenumber; $i ++) {
			$samewishlist [$i] ['photo_path'] = assemblepath ( $samewishlist [$i] ['photo_path'] );
		}
		$this->pageinfo = $pinfo;
		$this->dinfo = $detailinfo;
		$this->likenum = $likenum;
		$this->openid = $openid;
		$this->selfliked = $selfliked;
		$this->samewishlist = $samewishlist;
		$this->samenumber = $samenumber;
		$this->display();
	}
	
	/**
	 * 得到活动分享信息
	 * @return array $shareinfo 分享信息数组
	 */
	private function getActivityShareInfo() {
		$sharemap = array (
				'sharelist_id' => 'sharelist00007',
				'is_del' => 0
		);
		$shareinfo = M ( 'sharelist' )->where ( $sharemap )->find ();
		$shareinfo ['img_url'] = assemblepath( $shareinfo ['img_url'], true ); // 分享的必须是绝对路径
		$shareinfo ['desc'] = $shareinfo ['description'];
		$shareinfo ['appid'] = $this->einfo ['appid'];
		$shareinfo ['link'] = 'http://www.we-act.cn/Home/Activity/expo/e_id/201412021712300012';
		return $shareinfo;
	}
	
	/**
	 * 活动详情的分享信息
	 * @param array $detailinfo 详情信息
	 * @return array
	 */
	public function getDetailShareInfo($detailinfo = NULL) {
		$shareinfo ['appid'] = $this->einfo ['appid'];
		$shareinfo ['img_url'] = assemblepath ( $detailinfo ['photo_path'], true ); // 分享的必须是绝对路径
		$shareinfo ['title'] = '众爱卿，我正在参加S.Life《一爱倾“诚”》活动，为了得到空运的安第斯玫瑰和祝福，快来为我投票啊啊啊~~~';
		$shareinfo ['desc'] = $detailinfo ['name'] . '请大家来点赞相助得玫瑰。跨越18000公里空运来到这个城市的顶级玫瑰，全城限量19朵，一爱倾“诚”，只为你绽放！';
		$shareinfo ['link'] = 'http://www.we-act.cn/weact/Home/Activity/detail/e_id/201412021712300012?wid=' . $detailinfo ['enroll_id'];
		return $shareinfo;
	}
	
	/**
	 * 报名提交确认（还没有加上一个人只能提交一次的判断）。
	 */
	public function enrollConfirm() {
		$curtime = time();
		if($curtime > 1422979199) {
			$this->error('本活动已经结束！请及时关注排行榜和公众号，后续将进行奖品发放！');
		}
		$enrolltable = M ( 'activityenroll' );
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		// Step1：做个检查，提交过的不能重复提交
		$enrolledmap = array (
				'e_id' => $this->einfo ['e_id'],
				'openid' => $openid,
				'is_del' => 0
		);
		$enrollexist = $enrolltable->where ( $enrolledmap )->count ();
		if (!$enrollexist) {
			// Step2：检查通过允许提交
			$enrolldata = array (
					'enroll_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => $this->einfo ['e_id'],
					'openid' => $openid,
					'name' => I ( 'username' ),
					'tel' => I ( 'tel' ),
					//'qq' => I ( 'qq' ),
					'wish' => $_REQUEST ['wish'],
					'photo_path' => $this->uploadEnrollImage (),
					'add_time' => time ()
			);
			$enrollresult = $enrolltable->add ( $enrolldata );
			if($enrollresult) {
				$this->redirect( 'Home/Activity/expo', array(e_id => $this->einfo ['e_id']) );
			}else {
				$this->error('网络繁忙，报名失败，请稍后再试！');
			}
		} else {
			$this->error('请不要重复报名！');
		}
	}
	
	private function uploadEnrollImage() {
		$savePath = './Updata/images/' . $this->einfo ['e_id'] . '/plugin/enrollactivity/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); // 实例化公有控制器
		$uploadinfo = $commonhandle->uploadImage ( $savePath ); // 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
		if($uploadinfo) return substr( $uploadinfo [0] ['savepath'], 1 ) . $uploadinfo [0] ['savename'];
	}
	
	public function likeConfirm() {
		$curtime = time();
		if($curtime > 1422979199) {
			$ajaxresult = array (
					'errCode' => 10003,
					'errMsg' => '本活动已经结束！请及时关注排行榜和公众号，后续将进行奖品发放！'
			);
			$this->ajaxReturn($ajaxresult);
		}
		$liketable = M ( 'wishlike' );
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		$todaystart = todaystart(); // 今天开始
		p($todaystart);die;
		$todayend = todayend(); // 今天截止
		// 检查今天有没有点过赞
		$checkmap = array (
				'enroll_id' => I ( 'wid' ),
				'e_id' => $this->einfo ['e_id'],
				'liker' => $openid,
				'like_time' => array ( array( 'gt', $todaystart ), array( 'lt', $todayend ) ),
				'is_del' => 0
		);
		$checkresult = $liketable->where ( $checkmap )->find ();
		if ($checkresult) {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '您今天已点过赞，明天再来吧！也为其他情侣点个赞！'
			);
		} else {
			$checkmap ['like_id'] = md5( uniqid( rand (), true ) );
			$checkmap ['like_time'] = time();
			$likehandle = $liketable->add ( $checkmap );
			if ($likehandle) {
				$ajaxresult = array (
						'errCode' => 0,
						'errMsg' => 'ok'
				);
			}
		}
		$countmap = array (
				'enroll_id' => I ( 'wid' ),
				'e_id' => I ( 'e_id' ),
				'is_del' => 0
		);
		$likenum = $liketable->where ( $countmap )->count ();
		$ajaxresult ['data'] ['likenumber'] = $likenum;
		// 通知被点赞的人（如果关注新公众号的话）
		
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 分页请求数据
	 */
	public function pageRequestImage() {
		$ajaxinfo = $this->getDataByPage ( $this->einfo ['e_id'], $_REQUEST ['nextStart'] );
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 根据站点编号和下一条记录开始的下标来获取微博数据函数getDataByPage。
	 * 只返回data字段里的信息，这样可以统和首次和二次的操作。
	 *
	 * 实现思路：如果动态请求下一条微博的开始为0，代表是下拉刷新操作，这个时候请求的是首页记录，所以要考虑置顶微博的情况，其他$nextStart的情况不用考虑置顶微博。
	 * 
	 * @param string $e_id 商家编号
	 * @param number $nextStart 下一条记录的下标
	 * @param boolean $firstInitData 是否页面打开请求数据标记
	 * @return array $ 微博话题的列表
	 */
	public function getDataByPage($e_id = '', $nextStart = 0, $firstInitData = FALSE){
		// 定义本函数内全局常量和变量
		$enrolltable = M ( 'activityenroll' );
		$liketable = M ( 'wishlike' );
		$CONST_MAX_PER_PAGE = 10;							// 定义一页最多请求主题数目（10条）
		$enrollinfo = array (); // 登记注册信息
		
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		
		// 查询所有报名信息
		$infomap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$enrollnum = $enrolltable->where ( $infomap )->count ();
		
		$topicleftnum = $enrollnum - $nextStart;		// 计算剩下需要请求的话题数目，比如统计出100条话题，下一条开始话题下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		$realGetNumber = ( $topicleftnum >= $CONST_MAX_PER_PAGE ) ? $CONST_MAX_PER_PAGE : $topicleftnum;	// 剩下的话题还大于等于需要请求的话题数目吗，有的话，就满足需求，没有就按照剩下的数目请求
		
		$newNextStart = $nextStart + $realGetNumber;	// 本次如果请求成功，下一次再请求话题开始的下标
		
		if($topicleftnum) {
			$enrollinfo = $enrolltable->where ( $infomap )->order('add_time desc')->limit($nextStart, $realGetNumber)->select();	// 请求可请求的话题，limit的第二个参数是length
		}
		
		// 组装图片路径并且初始化点赞数
		$todaystart = todaystart(); // 今天开始
		$todayend = todayend(); // 今天截止
		$singlelikemap = array (
				'e_id' => $this->einfo ['e_id'],
				'liker' => $openid,
				'like_time' => array ( array( 'gt', $todaystart ), array( 'lt', $todayend ) ),
				'is_del' => 0
		); // 检测单个点赞信息
		for($i = 0; $i < $realGetNumber; $i ++) {
			$enrollinfo [$i] ['photo_path'] = assemblepath ( $enrollinfo [$i] ['photo_path'] ); // 组装路径
			// 检查是否点过赞
			$singlelikemap ['enroll_id'] = $enrollinfo [$i] ['enroll_id'];
			$likeinfo = $liketable->where ( $singlelikemap )->find ();
			if ($likeinfo) {
				$enrollinfo [$i] ['liked'] = 1;
			} else {
				$enrollinfo [$i] ['liked'] = 0;
			}
			// 统计点赞信息
			$numbermap = array (
					'enroll_id' => $enrollinfo [$i] ['enroll_id'],
					'e_id' => $this->einfo ['e_id'],
					'is_del' => 0
			);
			$enrollinfo [$i] ['likenum'] = $liketable->where ( $numbermap )->count();
		}
		
		$ajaxresult = array (
					'data' => array (
							'displayinfo' => $enrollinfo
					),
					'nextStart' => $newNextStart
			);
		if(! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
	
	public function rank() {
		$jsondata = $this->getRankByPage ( $this->einfo ['e_id'], 0, true );
		$ajaxinfo = json_encode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		$this->openid = $openid;
		$this->rankjson = $finaljson;
		$this->display();
	}
	
	public function requestRankInfo() {
		$ajaxinfo = $this->getRankByPage ( $this->einfo ['e_id'], $_REQUEST ['nextStart'] );
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 分页获取排名数据
	 * @param number $start 从第几条开始
	 * @param number $number 一次读取几条
	 */
	public function getRankByPage($e_id = '', $nextStart = 0, $firstInitData = FALSE) {
		$CONST_MAX_PER_PAGE = 10; // 固定每页10条数据
		$enrolltable = M ( 'activityenroll' );
		
		$rankmap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$rankcount = $enrolltable->where ( $rankmap )->count ();
		
		$rankleftnum = $rankcount - $nextStart;		// 计算剩下需要请求的话题数目，比如统计出100条话题，下一条开始话题下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		$realGetNumber = ( $rankleftnum >= $CONST_MAX_PER_PAGE ) ? $CONST_MAX_PER_PAGE : $rankleftnum;	// 剩下的话题还大于等于需要请求的话题数目吗，有的话，就满足需求，没有就按照剩下的数目请求
		//p($rankleftnum);p($realGetNumber);die;
		$newNextStart = $nextStart + $realGetNumber;	// 本次如果请求成功，下一次再请求话题开始的下标
		
		$sql = "SELECT A.enroll_id as enroll_id, openid, (name) as pname, wish, photo_path, add_time, B.likenum as likenum 
				FROM t_activityenroll as A  
				inner join 
				((SELECT enroll_id, count(e_id) as likenum FROM t_wishlike 
				WHERE is_del = 0 GROUP BY enroll_id ORDER BY likenum desc 
				limit " . $nextStart . "," . $realGetNumber . ") as B) ON A.enroll_id = B.enroll_id WHERE A.e_id = '" . $e_id . "' AND A.is_del = 0";
		$model = new Model();
		$rankinfo = $model->query ( $sql );
		$num = count ( $rankinfo );
		
		for($i = 0; $i < $num; $i ++){
			$rankinfo [$i] ['rank_position'] = $i + $nextStart + 1;
			$rankinfo [$i] ['add_time'] = timetodate ( $rankinfo [$i] ['add_time'] );
			$rankinfo [$i] ['photo_path'] = assemblepath ( $rankinfo [$i] ['photo_path'] );
			$rankinfo [$i] ['photo_path'] = substr ( $rankinfo [$i] ['photo_path'], 0, 62 ) . 'thumb_' . substr ( $rankinfo [$i] ['photo_path'], 62 ); // 使用缩略图
		}
		$ajaxresult = array (
				'data' => array (
						'rankinfo' => $rankinfo
				),
				'nextStart' => $newNextStart
		);
		if(! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
}
?>