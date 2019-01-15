<?php
/**
 * 消费者端的导购控制器。
 * 该控制器处理导购列表的显示，处理顾客选择导购。
 * @author 胡睿
 * CreateTime 19:51:30.
 */
class GuideAction extends MobileGuestAction {
	/**
	 * 导购列表视图。
	 */
	public function guideList() {
		$subbranch_id = I ( 'sid', '-1' ); // 别人跳转过来，带上url传递参数
		$jsondata = $this->guideListLimit ( $subbranch_id, 0, 10, true );
		$ajaxinfo = json_encode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 当前访问用户微信openid
		$this->subbranch_id = $subbranch_id; // 向前台推送分店编号
		$this->openid = $openid;
		$this->guidejson = $finaljson;
		$this->display ();
	}
	
	/**
	 * ajax请求导购列表。
	 */
	public function requestGuideListInfo() {
		$subbranch_id = I ( 'sid', '-1' ); // 别人跳转过来，带上url传递参数
		$ajaxinfo = $this->guideListLimit ( $subbranch_id, $_REQUEST ['nextStart'], 10, false);
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 分页获取导购列表数据
	 * 
	 * @param string $subbranch_id
	 *        	分店ID
	 * @param string $openid
	 *        	顾客ID
	 * @param number $startindex
	 *        	从第几条开始
	 * @param number $count
	 *        	想要读取几条
	 * @param number $firstInitData
	 *        	是否是第一次读取
	 */
	public function guideListLimit( $subbranch_id = '-1', $startindex = 0, $count = 10, $firstInitData = FALSE) {
		$finallist = array ();
		
		$Shop = M ( "shopguide_subbranch" );
		// 建立查询条件
		$map = array (
				'subbranch_id' => $subbranch_id, 
				'is_del' => 0 
		);
		// 得到满足条件的列表
		$list = $Shop->where ( $map )->limit ( $startindex, $count )->select ();
		
		if ($list) {
			// 返回真实得到的条数
			$realcount = count ( $list );
			// 数据的变换（改编）
			for($i = 0; $i < $realcount; $i ++) {
				// 对guideinfo信息进行一定的变换
				$list [$i] ['add_time'] = timetodate ( $list [$i] ['add_time'] );
				$list [$i] ['latest_modify'] = timetodate ( $list [$i] ['latest_modify'] );
				$list [$i] ['dimension_code'] = assemblepath ( $list [$i] ['dimension_code']);
				$list [$i] ['headimg'] = assemblepath ( $list [$i] ['headimg'] );
				$list [$i] ['image_path'] = assemblepath ( $list [$i] ['image_path']); // 分店图片
				unset ( $list [$i] ['account'] ); // 删除account
				unset ( $list [$i] ['password'] ); // 删除password
				$list [$i] ['is_guide'] = ( $_SESSION ['currentcustomer'] ['guide_id'] == $list [$i] ['guide_id'] ) ? 1:0;
			}
			$finallist = $list;
		}
		
		$ajaxresult = array (
				'data' => array (
						'guideListinfo' => $finallist 
				),
				'nextStart' => ($startindex + $realcount) 
		);
		
		if (! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
	
	/**
	 * 顾客选择导购函数
	 *
	 * @param string $guide_id 选择的导购ID
	 * @return boolean $selectresult 顾客选择导购是否完成，true代表选择OK，false代表选择失败
	 *
	 * 步骤：
	 * 1、利用$openid去顾客表里找到扫码顾客的信息$customerinfo；
	 * 2、判断下empty ( $customerinfo ['guide_id'] )来确定是选导购0还是换导购1；
	 * 3、进行字段更新save，进行更换记录add(changeguiderecord)；
	 * 4、返回成功或者失败
	 */
	public function selectGuide() {
		//if (! IS_POST) _404 ( "sorry, page not exist!", U ('Home/Guide/guideList', array ( 'e_id' => $this->einfo ['e_id'] )) ); // 这句话有问题，要带上分店参数
		if (! IS_POST) $this->error ( "sorry, page not exist!" );
		
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' =>  "网络繁忙，请稍后再试！"
		);
		
		$guide_id = I ( 'gid' ); // 接收导购编号
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 获取当前用户微信openid
		$cinfotable = M ( "customerinfo" ); // 实例化customerinfo表对象
		
		// 首先根据openid在顾客表中查找$customerinfo
		$cmap = array (
				'openid' => $openid,
				'is_del' => 0
		);
		$customerinfo = $cinfotable->where ( $cmap )->find (); // 此处前端控制，必然会存在一条记录
		if (empty ( $customerinfo )) {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "请先关注公众号后再扫描！";
			$this->ajaxReturn ( $ajaxresult );
		}
		
		/*
		 * 分两种情况：
		 * 第一种:导购不是其自身的导购，往t_changeguiderecord插入数据,并更新customer表中相关顾客的导购信息
		 * 第二种：如果导购是其本身的导购，那么什么都不做，返回
		 */
		
		// Step3：判断当前openid的顾客的导购是不是扫码导购
		if ($customerinfo ['guide_id'] == $guide_id) {
			// 说明导购重合
			$ajaxresult ['errCode'] = 10004;
			$ajaxresult ['errMsg'] = "您已选择该导购，请勿重复选择！";
			$this->ajaxReturn ( $ajaxresult );
		}
		
		// 如果扫码导购不是其自身的导购，两种情况：1、选导购 2、换导购
		$recordtable = M ( "changeguiderecord" ); // 实例化换导购记录表对象
		$recordtable->startTrans (); // 开始事务
		// 事务1：
		$recordinfo = array (
				'changerecord_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $customerinfo ['customer_id'],
				'guide_id' => $guide_id,
				'change_type' => empty ( $customerinfo ['guide_id'] ) ? 0 : 1, // 选导购是0，换是1
				'change_time' => time ()
		);
		if ($recordinfo ['change_type'] == 0) {
			$recordinfo ['remark'] = "顾客选择编号为" . $recordinfo ['guide_id'] . "的导购。";
		} else {
			$recordinfo ['remark'] = "顾客更换编号为" . $recordinfo ['guide_id'] . "的导购。";
		}
		$recordresult = $recordtable->add ( $recordinfo ); // 步骤1：记录选换导购
		// 事务2：
		$updatecustomer = array (
				'customer_id' => $customerinfo ['customer_id'],
				'guide_id' => $guide_id
		);
		$updateresult = $cinfotable->save ( $updatecustomer ); // 步骤2：顾客表更新导购编号
		if ($recordresult && $updateresult) {
			$recordtable->commit (); // 提交事务
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$recordtable->rollback (); // 有一个不成功就事务回滚
			$ajaxresult ['errCode'] = 10005;
			$ajaxresult ['errMsg'] = "提交失败，请稍后再试！";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
}
?>