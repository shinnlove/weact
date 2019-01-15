<?php
/**
 * 导购信息与二维码扫码控制器
 * @author 微动团队，胡睿。
 */
class GuideInfoAction extends Action {
	/**
	 * 导购二维码名片功能视图，扫码选导购后，点击图文跳转的导购信息链接。
	 */
	public function guideIDCard() {
		// 接收并判断参数
		$guide_id = I ( 'gid' );
		if (empty ( $guide_id )) {
			$this->error ( '导购编号参数错误！' );
		}
		
		// 尝试查询导购信息
		$guidemap = array (
				'guide_id' => $guide_id, // 当前导购编号
				'is_del' => 0 
		);
		$guideinfo = M ( 'shopguide_subbranch' )->where ( $guidemap )->find (); // 从导购视图中查询导购信息
		if (! $guideinfo) {
			$this->error ( '不存在该导购！' );
		}
		
		// 对guideinfo信息进行一定的变换
		$guideinfo ['add_time'] = timetodate ( $guideinfo ['add_time'] );
		$guideinfo ['latest_modify'] = timetodate ( $guideinfo ['latest_modify'] );
		$guideinfo ['dimension_code'] = assemblepath ( $guideinfo ['dimension_code'], true );
		$guideinfo ['headimg'] = assemblepath ( $guideinfo ['headimg'], true );
		$guideinfo ['image_path'] = assemblepath ( $guideinfo ['image_path'], true ); // 分店图片
		unset ( $guideinfo ['account'] ); // 删除account
		unset ( $guideinfo ['password'] ); // 删除password
		
		$this->ginfo = $guideinfo;
		$this->display ();
	}
	
	/**
	 * 顾客扫码选择导购函数。
	 * 特别标注：紧急关闭：在多导购网页聊天窗没做好之前，这里不能放开，特别标注：2015/05/26 02:35:36（详见106行左右）.
	 * 胡睿在2015/05/26 07:59:36 将导购恢复成只能一对一，经测试无误。
	 * 
	 * @param array $scaninfo 用户扫码信息
	 * @property string FromUserName 扫码用户的微信openid
	 * @property string code_ticket 二维码ticket编号
	 * @property string code_param 二维码的参数
	 * 可选：
	 * @property int scantime 扫码时间
	 * @return boolean $selectresult 顾客选择导购是否完成，true代表选择OK，false代表选择失败
	 *        
	 * 步骤：
	 * 1、利用$scaninfo ['openid]去顾客表里找到扫码顾客的信息$customerinfo；
	 * 2、判断下empty ( $customerinfo ['guide_id'] )来确定是选导购0还是换导购1；
	 * 3、进行字段更新save，进行更换记录add(changeguiderecord)；
	 * 4、返回告诉我是否成功。
	 */
	public function scanSelectGuide($scaninfo = NULL) {
		// 扫描返回信息
		$scanresult = array (
				'errCode' => 10001,
				'errMsg' =>  "网络繁忙，请稍后再扫描！"
		);
		$cinfotable = M ( "customerinfo" ); // 实例化customerinfo表对象
		
		// Step1：首先判断openid在顾客表中存在否(不存在返回false)
		$cmap = array (
				'openid' => $scaninfo ['FromUserName'],
				'is_del' => 0 
		);
		$customerinfo = $cinfotable->where ( $cmap )->find ();
		if (empty ( $customerinfo )) {
			$scanresult ['errCode'] = 10002;
			$scanresult ['errMsg'] = "您还未进入平台，请先进入平台，再进行选导购操作！";
			return $scanresult;
		}
		
		// Step2：其次判断所扫的二维码信息是否在二维表里
		$codemap = array (
				'code_ticket' => $scaninfo ['code_ticket'],
				'code_param' => $scaninfo ['code_param'],
				'is_del' => 0 
		);
		$scenecode = M ( "scenecode" )->where ( $codemap )->find ();
		if (empty ( $scenecode ) || empty ( $scenecode ['guide_id'] )) {
			$scanresult ['errCode'] = 10003;
			$scanresult ['errMsg'] = "该导购二维码已过期！";
			return $scanresult;
		}
		
		/*
		 * 两者均满足的情况下（顾客在顾客表里，所扫二维码也确实是导购二维码）,分两种情况：
		 * 第一种:扫码导购不是其自身的导购，往t_changeguiderecord插入数据,并更新customer表中相关顾客的导购信息
		 * 第二种：如果扫码导购是其本身的导购，那么什么都不做，返回
		 */
		$sid = $scenecode ['subbranch_id'];		// 扫描出来的分店ID
		$gid = $scenecode ['guide_id'];			// 扫描出来的导购ID
		$cid = $customerinfo ['customer_id'];	// 扫码顾客ID
		// 定义一个变量记录是选导购还是换导购
		$changetype = 0;						// 选导购是0，换导购是1，默认是选导购
		
		// Step3：判断当前openid的顾客的导购是不是扫码导购
		// 根据$sid、$cid在t_customerguide表中查找，如果存在该记录，那么说明重复选择导购
		$cgmap = array (
				'customer_id' => $cid,
				//'subbranch_id' => $sid, // 紧急关闭：在多导购网页聊天窗没做好之前，这里不能放开，特别标注：2015/05/26 02:35:36.
				'is_del' => 0 
		);
		$cginfo = M ( "customerguide" )->where ( $cgmap )->order ( "choose_time desc" )->find ();
		
		if ($cginfo) { 
			// 如果查出来了，可能是在该店重复选择某导购或更换该店的导购
			// 如果导购重合
			if ($cginfo ['guide_id'] == $gid) {
				$scanresult ['errCode'] = 10004;
				$scanresult ['errMsg'] = "您已选择该导购，请勿重复选择！";
				return $scanresult;
			} else {
				$changetype = 1; // 这种情况是换导购
			}
		}
		
		// 如果扫码导购不是其自身的导购，两种情况：1、选导购 2、换导购
		$recordtable = M ( "changeguiderecord" ); // 实例化换导购记录表对象
		$recordtable->startTrans (); // 开始事务
		// 事务1，对选换导购记录表插入一条记录（一直是增加的）：
		$recordinfo = array (
				'changerecord_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $customerinfo ['e_id'],
				'subbranch_id'=> $sid, // 选换导购增加分店编号
				'customer_id' => $cid,
				'guide_id' => $gid,
				'change_type' => $changetype, // 选导购是0，换是1
				'change_time' => time () 
		);
		if ($changetype == 0) {
			$recordinfo ['remark'] = "顾客扫码选择编号为" . $recordinfo ['guide_id'] . "的导购";
		} else {
			$recordinfo ['remark'] = "顾客扫码更换编号为" . $recordinfo ['guide_id'] . "的导购";
		}
		$recordresult = $recordtable->add ( $recordinfo ); // 步骤1：记录选换导购
		// 事务2，
		$updatecustomer = array (
				'customer_id' => $customerinfo ['customer_id'], // 当前顾客
				'e_id' => $customerinfo ['e_id'], // 当前商家
				'subbranch_id' => $sid, // 选换的导购所属分店编号
				'guide_id' => $scenecode ['guide_id'], // 导购编号
				'choose_time' => time (),
				'guide_remarkname' => '', // 特别注意：导购对用户的备注名要置空！！！
				'remark' => '',
				'is_del' => 0
		);
		$updateresult = false;
		if ($changetype == 0) {	
			// 0是选导购
			$updatecustomer ['cus_guide_id'] = md5 ( uniqid ( rand (), true ) );
			$updateresult = M ( "customerguide" )->add ( $updatecustomer ); // 添加新导购记录
		} else {
			// 1是换导购
			$savemap = array (
					'customer_id' => $customerinfo ['customer_id'],
					//'subbranch_id' => $sid, // 现在的sid应该是
					'subbranch_id' => $cginfo ['subbranch_id'], // 更改当前第一次找出来的这条记录的信息：subbranch_id，guide_id，choose_time
			);
			$updateresult = M ( "customerguide" )->where ( $savemap )->save ( $updatecustomer ); 
		}		
		if ($recordresult && $updateresult) {
			$recordtable->commit (); // 提交事务
			$scanresult ['errCode'] = 0;
			$scanresult ['errMsg'] = "ok";
		} else {
			$recordtable->rollback (); // 有一个不成功就事务回滚
			$scanresult ['errCode'] = 10005;
			$scanresult ['errMsg'] = "选换导购失败，请稍后再试！";
		}
		return $scanresult;
	}
}
?>