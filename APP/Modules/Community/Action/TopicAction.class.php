<?php
/**
 * 话题帖子控制器，负责处理新话题发表、显示话题详情等。
 * @author 赵臣升。
 * CreateTime：2014/12/26 18:57:25.
 */
class TopicAction extends CommunityGuestAction {
	/**
	 * 新话题视图。
	 */
	public function newTopic() {
		$this->pid = md5 ( uniqid ( rand (), true ) ); // 预先生成一个postid
		$this->display ();
	}
	
	
	public function checkLBS() {
		// 此处仅作模拟，真正的格式如下：
		$location = '{
						"errCode": 0,
						"message": "",
						"data": {
							"cityCode": 31000,
							"LBSInfo": {
								"province": "上海市",
								"city": "上海市"
							}
						},
						"showLogin": null,
						"jumpURL": null,
						"locationTime": 2000
					}';
		echo $location;
	}
	public function submitNewTopic() {
		$ajaxinfo = array (
				'pid' => $_REQUEST ['pid'], // 接收帖子编号
				'CSRFToken' => I ( 'CSRFToken' ),
				'sId' => I ( 'sId' ),
				'cityCode' => I ( 'cityCode' ),
				'LBSInfoLatitude' => I ( 'LBSInfoLatitude' ),
				'LBSInfoLongitude' => I ( 'LBSInfoLongitude' ),
				'LBSInfoProvince' => I ( 'LBSInfoProvince' ),
				'LBSInfoCity' => I ( 'LBSInfoCity' ),
				'LBSInfoStreet' => I ( 'LBSInfoStreet' ),
				'content' => I ( 'content' ), // 接收帖子内容
				'picIds' => I ( 'picIds' )  // 图片数组（全是图片id）
		);
		// 接收微社区提交的信息并存入数据库
		// p( $ajaxinfo );die;
	
		$publishsuc = false; // 帖子发表成功标记
		// Step1：先插入帖子
		$newpostinfo = array (
				'post_id' => $ajaxinfo ['pid'],
				// 'e_id' =>　$this->einfo ['e_id'], //传了e_id之后
				'e_id' => '201406261550250006', // 先设置为微动的编号
				// 'post_author_id' => $_Session ['currentwechater'] [$data ['e_id']] ['openid']
				'post_author_id' => 'oeovpty2ScWq6YXxuMG0hY5qHOGA', // 先设置一个固定值：诗人
				'post_author' => '游客',
				'lbs_info' => $ajaxinfo ['LBSInfoProvince'] . ' ' . $ajaxinfo ['LBSInfoCity'] . ' ' . $ajaxinfo ['LBSInfoStreet'],
				'post_content' => $ajaxinfo ['content'],
				'post_time' => time ()
		);
		// p($newpostinfo);die;
		$newpostresult = M ( 'communitypost' )->data ( $newpostinfo )->add ();
		if ($newpostresult && ! empty ( $ajaxinfo ['picIds'] )) {
			// $ajaxinfo ['picIds']中还有图片数组
			$allPic = array (); // 所有图片数组
			for($i = 0; $i < count ( $ajaxinfo ['picIds'] ); $i ++) {
				$allPic [$i] = array (
						'postimage_id' => md5 ( uniqid ( rand (), true ) ),
						'post_id' => $ajaxinfo ['pid'],
						'e_id' => '201406261550250006',
						'image_path' => '/' . 'Updata/images/201406261550250006/community/' . $ajaxinfo ['picIds'] [$i]
				);
			}
			$picAllResult = M ( 'communityimage' )->addAll ( $allPic );
			if ($picAllResult)
				$publishsuc = true;
		} else if ($newpostresult && empty ( $ajaxinfo ['picIds'] )) {
			$publishsuc = true; // 没有图片，发别帖子成功
		}
		if ($publishsuc) {
			$result = array (
					'errCode' => 0,
					'message' => '发表成功',
					'data' => array (
							'subscribeTip' => 1
					),
					'showLogin' => null,
					'jumpURL' => U ( "Community/MicroCommunity/myCommunity", array (
							"e_id" => "201406261550250006"
					), ".shtml", 0, true ),
					'locationTime' => 2000
			);
		} else {
			$result = array (
					'errCode' => 1,
					'message' => '发表失败，请检查网络状况!',
					'data' => array (
							'subscribeTip' => 0
					),
					'showLogin' => null,
					'jumpURL' => null,
					'locationTime' => 2000
			);
		}
		echo json_encode ( $result );
	}
	public function uploadImage() {
		$site_id = I ( 'sId' );
		$e_id = I ( 'e_id' );
		$imgid = I ( 'id' );
		$imgData = I ( 'pic' );
		p($_REQUEST ['e_id']);die;
		$currentimgId = md5 ( uniqid ( rand (), true ) );
		$imgReal = base64_decode ( str_replace ( 'data:image/jpeg;base64,', '', $imgData ) ); // 解码图片
		$saveURL = 'Updata/images/201406261550250006/community/topic00001/'; // 指定保存路径saveURL
		if (! file_exists ( $saveURL ))
			mkdirs ( $saveURL ); // 如果路径不存在，创建路径saveURL
		$saveresult = file_put_contents ( $saveURL . $currentimgId . '.jpg', $imgReal ); // 默认是直接存储在weact同级目录
		// 如果上传成功
		if (! empty ( $saveresult )) {
			// 注意返回格式
			$result = array (
					'errCode' => 0,
					'message' => '发表成功',
					'data' => array (
							'id' => $imgid,
							'picId' => $currentimgId
					),
					'showLogin' => null,
					'jumpURL' => null,
					'locationTime' => 2000
			);
			echo json_encode ( $result );
		}
	}
	
	public function topicDetail() {
		$this->display();
	}
	
	/**
	 * topicDetail格式化信息
	 */
	public function formatjson() {
		if ($_REQUEST ['start'] < 10) {
			$json = '{"errCode":0,"message":"","data":{"dataList":[{"content":"我今天穿了丝袜哟，想不想舔?","authorUid":142517093,"author":"胡福玲","createTime":1414150201,"tId":173,"pId":1236,"isLZ":false,"toPId":0,"toAuthorUid":0,"toAuthor":"","picIds":[],"appId":0,"appName":"","fCreatedTime":"2014-10-24 19:30","hCreatedTime":"2014-06-24 19:30","floorList":false,"floorCount":0,"authorExps":{"num":3,"rank":1,"limit":{"thread":4,"reply":0,"reward":0}},"avatar":"http://wx.qlogo.cn/mmopen/icicIvSc9Yf6UCeK5zYUH9W01via95ZpX9moyVicdPwDWwBTjqPGtwvwicKQzL2NHic3yq9sZhicjR89ibd7vbSnC5viacicgzQPhIsTRO/64?max-age=1296000","restCount":0}],"nextStart":10,"groupStar":2,"verifyDeveloper":1,"verifyStar":2,"sitePv":"81474","threadCount":220,"enabledSmiley":"1","site":{"sName":"TOPMENu7537u88c5u7f51","sId":"187471671"},"desc":0}}';
		} else {
			$json = '{"errCode":0,"message":"","data":{"dataList":[],"nextStart":10,"groupStar":2,"verifyDeveloper":1,"verifyStar":2,"sitePv":"81474","threadCount":220,"enabledSmiley":"1","site":{"sName":"TOPMENu7537u88c5u7f51","sId":"187471671"},"desc":0}}';
		}
		echo $json;
	}
}
?>