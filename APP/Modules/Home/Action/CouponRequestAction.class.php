<?php
/**
 * 总店优惠券领取请求控制器。
 * @author 赵臣升。
 * CreateTime:2015/06/26 18:39:36.
 */
class CouponRequestAction extends MobileLoginRequestAction {
	/**
	 * 用户提交领取优惠券。
	 */
	public function getCouponConfirm() {
		// 常规全局变量参数
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; 				// 提交领取优惠券的顾客
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 获取当前微信用户的openid
		
		// step1、对于优惠券本身是否存在的校验
		// 1)优惠券coupon_id字段不为空
		$coupon_id = I ( 'cid', '' ); // 需要领取的优惠券id
		if (empty ( $coupon_id )) {
			$this->ajaxresult ['errCode'] = 10001;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，优惠券参数错误！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 2) 该商家存在该优惠券(未主动删除)且未过期(数据库定时任务会自动将t_coupon中正常状态过期的优惠券is_del置为1)
		$couponMap = array (
				'coupon_id' => $coupon_id,
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0 
		);
		$couponFound = M ( "coupon" )->where ( $couponMap )->find (); // 查询这张优惠券是否存在
		if (empty ( $couponFound )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，该优惠券不存在或已过期";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 3) publish_amount还有张数未领取，仅针对publish_amount!=0的时候进行判断
		$publishAmount = $couponFound ['publish_amount']; // 提取优惠券发放张数
		if (! empty ( $publishAmount )) { 
			// 不为0，且不为空,表示确实有张数领取限制
		    // 首先对t_customercoupon进行核算，统计已领取的数量
			$cusCouponTotalMap = array (
					'e_id' => $this->einfo ['e_id'],
					'coupon_id' => $coupon_id,
					'is_del' => 0 
			);
			$couponGetCount = M ( "customercoupon" )->where ( $cusCouponTotalMap )->count ();
			if ($couponGetCount >= $publishAmount) {
				// 如果已领取的数量超过发布数量
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "优惠券领取失败，该优惠券已经被抢光了！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			}
		}
		
		// step2、对于客户是否可领取的校验
		// 1)customer_id和openid不为空
		if (empty ( $customer_id )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，请先登录后再领券，请勿通过特殊手段！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $openid )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，请您先（微信授权）登录后再领券";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 2)open_id是否关注公众号，通过t_wechatuserinfo中的subscribe进行判断
		$wechatMap = array (
				'openid' => $openid,
				'subscribe' => 1, // 必须已关注
				'is_del' => 0 
		);
		$wechatResult = M ( "wechatuserinfo" )->where ( $wechatMap )->find ();
		if (empty ( $wechatResult )) {
			// 未关注
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，请先关注，未关注不能领取优惠券！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// 3)用户优惠券表查询是否已经领用过
		$cusCouponGetMap = array (
				'e_id' => $this->einfo ['e_id'], // 当前商家
				'coupon_id' => $coupon_id, // 当前优惠券
				'customer_id' => $customer_id, // 当前顾客
				'is_del' => 0 
		);
		$couponGet = M ( "customercoupon" )->where ( $cusCouponGetMap )->find ();
		if (! empty ( $couponGet )) { 
			// 如果非空,那么说明已领取过优惠券，不能重复领取
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，不能重复领券！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		// Step3:开始领券,往数据库里插入记录
		$cusCouponData = array (
				'customercoupon_id' => md5 ( uniqid ( rand (), true ) ), // 主键
				'e_id' => $this->einfo ['e_id'], 						// 当前商家
				'subbranch_id' => '-1', 						// -1代表在线上、总店或不区分门店情况下使用(通用)，此处不要存subbranch_id
				'coupon_id' => $coupon_id, 						// 所领取的优惠券编号
				'coupon_name' => $couponFound ['coupon_name'], 	// 优惠券名称
				'customer_id' => $customer_id, 					// 当前领券顾客
				'get_time' => time (), 							// 领券时间
				'is_del' => 0 
		);
		$result = M ( "customercoupon" )->add ( $cusCouponData );
		if ($result) {
			// 成功领取优惠券
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			// 如果插入失败，进行提示
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "优惠券领取失败，网络繁忙，请不要重复申领！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 读取我的可用的优惠券信息。
	 * @param string $eid 商家编号
	 * @param string $cid 顾客id
	 * @return array $requestinfo 请求的数据信息
	 */
	public function myAvailableCoupon() {
		$coupontable = M ( 'customer_coupon' ); // 实例化所需的视图结构
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; 			// 提交领取优惠券的顾客
		$availablelist = array (); // 最终的优惠券
		
		// Step1：定义查询条件
		$querymap = array (
				'e_id' => $this->einfo ['e_id'], 	// 当前商家下
				'customer_id' => $customer_id, 		// 当前顾客
				'_string' => "is_used = 0 AND end_time >= now()", // 没有使用过的、在有效期范围内的可用优惠券
				'is_del' => 0 // 未过期的
		);
		$availablecount = $coupontable->where ( $querymap )->count (); // 计算有没有可用优惠券
		if ($availablecount) {
			$availablelist = $coupontable->where ( $querymap )->order ( 'get_time desc' )->select (); // 查询出这些可用的优惠券
			for($i = 0; $i < $availablecount; $i ++) {
				$availablelist [$i] ['get_time'] = timetodate ( $availablelist [$i] ['get_time'] );
				$availablelist [$i] ['used_time'] = timetodate ( $availablelist [$i] ['used_time'] );
				$availablelist [$i] ['coupon_cover'] = assemblepath ( $availablelist [$i] ['coupon_cover'] );
				$availablelist [$i] ['start_time'] = timetodate ( strtotime ( $availablelist [$i] ['start_time'] ), true );
				$availablelist [$i] ['end_time'] = timetodate ( strtotime ( $availablelist [$i] ['end_time'] ), true );
				$availablelist [$i] ['denomination'] = intval ( $availablelist [$i] ['denomination'] ); // 如果是抵扣券，直接转整型，不需要小数点（总店额外加的）
				unset($availablelist [$i] ['instruction']); // 不要这个字段，容易报错
			}
		}
		
		$ajaxresult = array (
				'data' => array (
						'couponlist' => $availablelist, 
				),
				'totalcount' => $availablecount,
		);
		
		$ajaxresult ['errCode'] = 0;
		$ajaxresult ['errMsg'] = "ok";
		
		$this->ajaxReturn ( $ajaxresult ); // 返回ajax信息
	}
	
}
?>