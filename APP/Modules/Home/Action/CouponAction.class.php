<?php
/**
 * 线上总店优惠券管理控制器。
 * @author 梁思彬，赵臣升。
 */
class CouponAction extends MobileLoginAction {

	/**
	 * ===============优惠券详情===============
	 */
	
	/**
	 * 优惠券详情视图页面。
	 */
	public function couponInfo() {
		$coupon_id = I ( 'cid' ); // 接收优惠券编号
		$fromwhere = I ( 'from', 'customer' ); // 可能来自订单或者来自我的账户中心
		
		if (empty ( $coupon_id )) {
			$this->error ( "优惠券参数错误！" );
		}
		
		// 查找优惠券信息的条件范围
		$couponmap = array (
				'coupon_id' => $coupon_id,
		);
		//如果是从订单确认页面跳转过来，只推送可使用的优惠券信息
		if ($fromwhere == 'order') {
			$couponmap ['is_del'] = 0;
		}
		
		$couponinfo = M ( 'coupon' )->where ( $couponmap )->find (); // 查找优惠券信息
		$couponinfo ['coupon_cover'] = assemblepath ( $couponinfo ['coupon_cover'] ); // 组装优惠券封面图片路径
		
		if (! $couponinfo) {
			$this->error ( "优惠券不存在！" );
		}
		
		$this->couponinfo = $couponinfo; // 向前台推送优惠券信息
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		$this->display ();
	}
	
	/**
	 * ===============我的优惠券===============
	 */
	
	/**
	 * 我的优惠券页面视图。
	 */
	public function myCoupon() {
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 );
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$jsondata = $this->getMyCouponByPage ( $this->einfo ['e_id'], $customer_id, $type, $startindex, $perpage, $firstinitdata );
		$ajaxinfo = json_encode ( $jsondata );
		$this->couponjson = $ajaxinfo;
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		$this->display ();
	}
	
	/**
	 * ajax请求查询我的优惠券信息函数
	 */
	public function queryMyCoupon() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
	
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 );
		$nextstart = I ( 'nextstart', 0 ); // 下一页优惠券开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getMyCouponByPage ( $this->einfo ['e_id'], $customer_id, $type, $nextstart, $perpage ); // 分页读取我的优惠券信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取我的优惠券信息函数
	 * @param string $eid 商家编号
	 * @param string $cid 顾客id
	 * @param int $type 优惠券类型:0代表可用的优惠券(默认),-1代表已使用或已过期的优惠券
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getMyCouponByPage($eid = '', $cid = '', $type = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$coupontable = M ( 'customer_coupon' ); // 实例化所需的视图结构
		$couponlist = array (); // 本次请求的数据
		// Step1：定义查询条件:is_del=1表示已过期或已不存在的优惠券
		$querymap = array (
				'e_id' => $eid, // 当前商家下
				'customer_id' => $cid,
				'is_del' => 0, // 没有被删除的
		);
		
		// 根据不同查询条件选择优惠券信息
		if ($type == -1) {
			unset($querymap ['is_del']);
			$querymap ["_string"] = "is_del = 1 or is_used = 1 or end_time < now()"; // 过期或已结束的优惠券
		} else if ($type == 1) {
			$querymap ['coupon_type'] = 1; 		// 代表抵用券
		} else if ($type == 2){
			$querymap ['coupon_type'] = 2; 		// 代表折扣券
		} else if ($type == 3){
			$querymap ['coupon_type'] = 3; 		// 代表特价券
		} else {
			// 表示要显示所有可用优惠券，不做查询字段限制
			$querymap ["_string"] = "is_used = 0 AND end_time >= now()"; // 没有使用过的、在有效期范围内的可用优惠券
		}
		
		$totalcount = $coupontable->where ( $querymap )->count (); 	// 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
	
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
	
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
	
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
	
		if ($realgetnum) {
			$couponlist = $coupontable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( 'get_time desc' )->select (); // 查询可用优惠券的信息
			// 处理优惠券路径和时间
			for($i = 0; $i < $realgetnum; $i ++) {
				$couponlist [$i] ['get_time'] = timetodate ( $couponlist [$i] ['get_time'] );
				$couponlist [$i] ['used_time'] = timetodate ( $couponlist [$i] ['used_time'] );
				$couponlist [$i] ['coupon_cover'] = assemblepath ( $couponlist [$i] ['coupon_cover'] );
				$couponlist [$i] ['start_time'] = timetodate ( strtotime ( $couponlist [$i] ['start_time'] ), true );
				$couponlist [$i] ['end_time'] = timetodate ( strtotime ( $couponlist [$i] ['end_time'] ), true );
				$couponlist [$i] ['denomination'] = intval ( $couponlist [$i] ['denomination'] ); // 如果是抵扣券，直接转整型，不需要小数点（总店额外加的）
				unset($couponlist [$i] ['advertise']); // 防止优惠券列表出错，取消advertise字段
				unset($couponlist [$i] ['instruction']); // 防止优惠券列表出错，取消instruction字段
			}
		}
	
		// 打包数据
		$ajaxresult = array (
				'data' => array (
						'couponlist' => $couponlist // 可用优惠券列表
				),
				'nextstart' => $newnextstart, // 下一条记录开始
				'totalcount' => $totalcount // 总的记录数
		);
	
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ===============选择可用优惠券===============
	 */
	
	/**
	 * 选择使用优惠券视图页面，预订单跳转过来选择。
	 */
	public function selectCoupon() {
		$frompage = I ( 'from' ); // 接收参数
		$this->frompage = $frompage; // 从哪里跳过来
		
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 ); // 可用优惠券类型，0代表所有可用的优惠券(默认),1代表抵用券，2代表折扣券，3代表特价券；默认查询所有
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$availablecouponlist = $this->getMyCouponAvailableByPage ( $this->einfo ['e_id'], $customer_id, $type, $startindex, $perpage, $firstinitdata ); // 获取当前用户第一页可用的优惠券
		$finaljson = jsencode ( $availablecouponlist ); // 压缩数据
		$this->couponjson = $finaljson; // 推送给前台数据
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		$this->display ();
	}
	
	/**
	 * ajax请求查询我的可用优惠券信息函数。
	 */
	public function queryMyCouponAvailable() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 ); 				// 可用优惠券类型，0代表所有可用的优惠券(默认),1代表抵用券，2代表折扣券，3代表特价券；默认查询所有
		$nextstart = I ( 'nextstart', 0 ); 		// 下一页导购开始的下标，默认为0
		$perpage = 10; 							// 每页10条
		
		$requestresult = $this->getMyCouponAvailableByPage ( $this->einfo ['e_id'], $customer_id, $type, $nextstart, $perpage ); // 分页读取可用优惠券信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取我的可用的优惠券信息函数，用于线上总店用户购物的时候选取优惠券。
	 * @param string $eid 商家编号
	 * @param string $customer_id 顾客id
	 * @param int $type 可用的优惠券类型:0代表所有可用的优惠券(默认),1代表抵用券，2代表折扣券，3代表特价券
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getMyCouponAvailableByPage($eid = '', $customer_id = '', $type = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$coupontable = M ( 'customer_coupon' ); // 实例化所需的视图结构
		$couponlist = array (); // 本次请求的数据
		
		// Step1：定义查询条件
		$querymap = array (
				'e_id' => $eid, 					// 当前商家下
				'customer_id' => $customer_id, 		// 当前顾客
				'is_used' => 0,						// 未使用的
				'_string' => "end_time >= now()",	// 未过期的
				'is_del' => 0 						// 没有被删除的
		);
		
		if ($type == 1) {
			$querymap ['coupon_type'] = 1; 		// 代表抵用券
		} else if ($type == 2){
			$querymap ['coupon_type'] = 2; 		// 代表折扣券
		} else if ($type == 3){
			$querymap ['coupon_type'] = 3; 		// 代表特价券
		} 
		
		$totalcount = $coupontable->where ( $querymap )->count (); 	// 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		if ($realgetnum) {
			$couponlist = $coupontable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( 'get_time desc' )->select (); // 查询可用优惠券的信息
			// 处理优惠券路径和时间
			for($i = 0; $i < $realgetnum; $i ++) {
				$couponlist [$i] ['get_time'] = timetodate ( $couponlist [$i] ['get_time'] );
				$couponlist [$i] ['used_time'] = timetodate ( $couponlist [$i] ['used_time'] );
				$couponlist [$i] ['coupon_cover'] = assemblepath ( $couponlist [$i] ['coupon_cover'] );
				$couponlist [$i] ['start_time'] = timetodate ( strtotime ( $couponlist [$i] ['start_time'] ), true );
				$couponlist [$i] ['end_time'] = timetodate ( strtotime ( $couponlist [$i] ['end_time'] ), true );
				$couponlist [$i] ['denomination'] = intval ( $couponlist [$i] ['denomination'] ); // 如果是抵扣券，直接转整型，不需要小数点（总店额外加的）
				unset($couponlist [$i] ['advertise']); // 防止优惠券列表出错，取消advertise字段
				unset($couponlist [$i] ['instruction']); // 防止优惠券列表出错，取消instruction字段
			}
		}
		
		// 打包数据
		$ajaxresult = array (
				'data' => array (
						'couponlist' => $couponlist // 可用优惠券列表
				),
				'nextstart' => $newnextstart, // 下一条记录开始
				'totalcount' => $totalcount, // 总的记录数
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ===============店家活动优惠券===============
	 */
	
	/**
	 * 店家所有可领取的优惠券列表。
	 */
	public function giftList() {
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$giftcouponlist = $this->getGiftCouponByPage ( $this->einfo ['e_id'], $startindex, $perpage, $firstinitdata ); // 获取当前用户第一页可用的优惠券
		$finaljson = jsencode ( $giftcouponlist ); // 压缩数据
		$this->couponjson = $finaljson; // 推送给前台数据
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display ();
	}
	
	/**
	 * ajax请求查询店家可领取的优惠券信息函数。
	 */
	public function queryGiftCoupon() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$nextstart = I ( 'nextstart', 0 ); 		// 下一页优惠券开始的下标，默认为0
		$perpage = 10; 							// 每页10条
		
		$requestresult = $this->getGiftCouponByPage ( $this->einfo ['e_id'], $nextstart, $perpage ); // 分页读取店家优惠券信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取商家可用的优惠券信息函数，用于新主页点击有礼。
	 * @param string $eid 商家编号
	 * @param number $nextstart 下一页开始
	 * @param number $perpage 每页读取
	 * @param boolean $firstinit 是否初始化
	 * @return array $couponlistinfo 返回优惠券列表
	 */
	private function getGiftCouponByPage($eid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$coupontable = M ( 'coupon' ); // 实例化优惠券表
		$couponlist = array (); // 本次请求的数据
		$datenow = timetodate ( time () );
		
		// Step1：定义查询条件
		$querymap = array (
				'e_id' => $eid, 							// 当前商家下
				'start_time' => array ( "lt", $datenow ), 	// 优惠券起始时间
				'end_time' => array ( "gt", $datenow ), 	// 优惠券结束时间
				'is_del' => 0 								// 没有被删除的
		);
		
		$totalcount = $coupontable->where ( $querymap )->count (); 	// 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		if ($realgetnum) {
			$couponlist = $coupontable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( 'add_time desc' )->select (); // 查询可用优惠券的信息
			// 处理优惠券路径和时间
			for($i = 0; $i < $realgetnum; $i ++) {
				$couponlist [$i] ['add_time'] = timetodate ( $couponlist [$i] ['add_time'] );
				$couponlist [$i] ['coupon_cover'] = assemblepath ( $couponlist [$i] ['coupon_cover'] );
				$couponlist [$i] ['start_time'] = timetodate ( strtotime ( $couponlist [$i] ['start_time'] ), true );
				$couponlist [$i] ['end_time'] = timetodate ( strtotime ( $couponlist [$i] ['end_time'] ), true );
				$couponlist [$i] ['denomination'] = intval ( $couponlist [$i] ['denomination'] ); // 如果是抵扣券，直接转整型，不需要小数点（总店额外加的）
				unset($couponlist [$i] ['advertise']); // 防止优惠券列表出错，取消advertise字段
				unset($couponlist [$i] ['instruction']); // 防止优惠券列表出错，取消instruction字段
			}
		}
		
		// 打包数据
		$ajaxresult = array (
				'data' => array (
						'couponlist' => $couponlist // 可用优惠券列表
				),
				'nextstart' => $newnextstart, // 下一条记录开始
				'totalcount' => $totalcount, // 总的记录数
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ===============以前的领取优惠券===============
	 */
	
	// 获取优惠券信息
	public function getMyCoupon() {
		$data = array (
			'coupon_id' => I ( 'coupon_id' ),
			'e_id' => I ( 'e_id' ) 
		);
		$coupon = M ( 'coupon' );
		$result = $coupon->where ( $data )->select ();
		if ($result) {
			$this->coupon = $result;
		}
		$this->display ();
	}
	
	//判断优惠券是否有效
	function checkCoupon($coupon_id, $e_id) {
		$map = array (
				'coupon_id' => $coupon_id,
				'e_id' => $e_id
		);
		$coupon = M ( 'coupon' );
		$result = $coupon->where ( $map )->select ();
		if ($result [0]['is_del'] == 1) {
			// 优惠券已经过期或者已经被删除
			return 1;
		} else if(0 >= $result[0]['coupon_amount']) {
			// 优惠券已经领取完毕
			return 2;
		}else{
			return 3;
		}
	}
	
	// 领取优惠券
	public function addCoupon() {
		if (!$_SESSION ['currentcustomer']||$_SESSION['currentcustomer']==null) {
			//没有登录（这样判断是有问题的，万一不是同一店家的客户登陆着没有退出）
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
		$coupon_id = I ( 'coupon_id' );
		$e_id = I ( 'e_id' );
		//判断优惠券
		$checkresult = self::checkCoupon($coupon_id,$e_id);

		if ($checkresult == 1){
			$this->ajaxReturn ( array ( 'status' => 4 ), 'json' );
		}
		else if ($checkresult == 2){
		    $this->ajaxReturn ( array ( 'status' => 5 ), 'json' );
		}else{
		
			$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'];
			$customercoupon_id = md5 ( uniqid ( rand (), true ) );
		
			$data = array (
				'customercoupon_id' => $customercoupon_id,
				'coupon_name' => I ( 'coupon_name' ),
				'coupon_id' => $coupon_id,
				'e_id' => I ( 'e_id' ),
				'customer_id' => $customer_id,
				'get_time' => date ( 'YmdHms' ) 
			);
		
			$user_coupon = M ( 'customercoupon' );
			$map ['customer_id'] = $customer_id;
			$map ['coupon_id'] = I ( 'coupon_id' );
			$searchresult = $user_coupon->where ( $map )->select ();

			if ($searchresult) {
				// 领取失败，用户已经领取
				$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
			} else {
				$user_coupon->create ( $data );
				$result = $user_coupon->add ();

				if ($result) {
					// 领取成功后优惠券数目减1
					$map2 = array(
						'coupon_id' =>$coupon_id,
						'e_id' =>$e_id
					);
					$coupon = M('coupon');
					$result2 = $coupon ->where($map2)->select();
					$last = $result2[0]['coupon_amount']-1;
					if ($last == 0){
						$data2 = array(
					   		'coupon_amount' => $last,
							'is_del' =>1
						);
					}else{
						$data2 = array(
							'coupon_amount' => $last
						);
					}
					$coupon->where($map2)->save($data2);
					// 领取成功
					$this->ajaxReturn ( array ( 'status' => 2 ), 'json' );
				} else {
					// 领取失败
					$this->ajaxReturn ( array ( 'status' => 3 ), 'json' );
				}
			}
		}
	}
}
?>