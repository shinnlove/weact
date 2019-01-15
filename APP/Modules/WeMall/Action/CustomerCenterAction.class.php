<?php
/**
 * 个人中心控制器。
 * @author 赵臣升。
 * CreateTime:2015/04/29 14:02:25.
 */
class CustomerCenterAction extends LoginMallAction {
	/**
	 * 个人中心视图。
	 */
	public function myCenter() {
		$this->display ();
	}
	
	/**
	 * 我的导购视图。
	 */
	public function myGuide() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		
		$startindex = 0;
		//$perpage = 10;
		$perpage = 1;
		$firstinitdata = true;
		
		// 查询我已经选过的导购
		$jsondata = $this->getMyGuideByPage ( $this->eid, $customer_id, $startindex, $perpage, $firstinitdata );
		$ajaxinfo = json_encode ( $jsondata );
		
		$this->guidejson = $ajaxinfo;
		$this->display ();
	}
	
	/**
	 * 收货地址管理。
	 */
	public function deliveryManage() {
		$frompage = I ( 'from' ); //接收参数
		
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		
		$startindex = 0;
		$perpage = 1000; // 目前暂不支持分页查询（觉得没必要，所以一次性多读一点）
		$firstinitdata = true;
		
		$jsondata = $this->getDeliveryInfoByPage ( $this->eid, $customer_id, $startindex, $perpage, $firstinitdata );
		$ajaxinfo = jsencode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
		
		// 推送给前台信息
		$this->deliverylistjson = $finaljson; // 该用户的配送信息列表
		$this->deliverylist = $jsondata ['data'] ['deliverylist']; // 原始推送
		$this->frompage = $frompage; // 本页面是从哪个页面打开
		$this->display ();
	}
	
	/**
	 * 增加收货地址。
	 */
	public function addDelivery() {
		$frompage = I ( 'from' ); // 接收跳转过来页面的参数
		if (empty ( $frompage )) {
			$frompage = "customercenter"; // 如果没有跳转参数，默认从mycenter跳转过来
		}
		$this->frompage = $frompage; // 本页面是从哪个页面打开
		$this->display ();
	}
	
	/**
	 * 我的优惠券页面视图。
	 */
	public function myCoupon() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'querytype', 0 );
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$jsondata = $this->getMyCouponByPage ( $this->eid, $customer_id, $type, $startindex, $perpage, $firstinitdata );
		$ajaxinfo = json_encode ( $jsondata );
	
		$this->couponjson = $ajaxinfo;
		$this->display ();
	}
	
	/**
	 * ajax请求查询我的优惠券信息函数
	 */
	public function queryMyCoupon() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
	
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'querytype', 0 );
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 10; // 每页10条
	
		$requestresult = $this->getMyCouponByPage ( $this->eid, $customer_id, $type, $nextstart, $perpage ); // 分页读取收藏的商品信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取我的优惠券信息函数
	 * @param string $eid 商家编号
	 * @param string $cid 顾客id
	 * @param int $type 优惠券类型:0代表可用的优惠券(默认),1代表已使用的优惠券，2代表已过期的优惠券
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
		);
		// 表示要显示可用的优惠券
		if ($type == 0) { 
			$querymap ['_string'] = 'is_used = 0 AND is_del = 0 AND end_time >= now()'; // 未过期的优惠券
		} else {
			$querymap ['_string'] = 'is_used = 1 or is_del = 1 or end_time < now()'; // 已使用或已过期的优惠券
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
	 * 选择优惠券页面视图（只要可用的优惠券，并且在预订单处选择）
	 */
	public function selectCoupon() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 ); // 可用优惠券类型，0代表所有可用的优惠券(默认),1代表抵用券，2代表折扣券，3代表特价券；默认查询所有
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$availablecouponlist = $this->getMyCouponAvailableByPage ( $this->eid, $customer_id, $type, $startindex, $perpage, $firstinitdata ); // 获取当前用户第一页可用的优惠券
		
		$finaljson = json_encode ( $availablecouponlist ); // 压缩数据
		$this->couponjson = $finaljson; // 推送给前台数据
		$this->display ();
	}
	
	/**
	 * ajax请求查询我的优惠券信息函数
	 */
	public function queryMyCouponAvailable() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$type = I ( 'type', 0 ); 				// 可用优惠券类型，0代表所有可用的优惠券(默认),1代表抵用券，2代表折扣券，3代表特价券；默认查询所有
		$nextstart = I ( 'nextstart', 0 ); 		// 下一页导购开始的下标，默认为0
		$perpage = 10; 							// 每页10条
	
		$requestresult = $this->getMyCouponAvailableByPage ( $this->eid, $customer_id, $type, $nextstart, $perpage ); // 分页读取可用优惠券信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取我的可用的优惠券信息函数
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
				'is_del' => 0 						// 没有被删除
		);
		
		if ($type == 1) {
			$querymap ['coupon_type'] = 1; 			// 代表抵用券
		} else if ($type == 2){
			$querymap ['coupon_type'] = 2; 			// 代表折扣券
		} else if ($type == 3){
			$querymap ['coupon_type'] = 3; 			// 代表特价券
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
	 * 我的收藏视图。
	 */
	public function myCollection() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		
		$startindex = 0;
		$perpage = 10;
		$firstinitdata = true;
		
		$jsondata = $this->getCollectionByPage ( $this->eid, $customer_id, $startindex, $perpage, $firstinitdata );
		$ajaxinfo = json_encode ( $jsondata );
		
		$this->collectionjson = $ajaxinfo;
		$this->display ();
	}
	
	/**
	 * ajax请求查询商品收藏列表。
	 */
	public function queryCollection() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getCollectionByPage ( $this->eid, $customer_id, $nextstart, $perpage ); // 分页读取收藏的商品信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取收藏商品信息函数。
	 * 特别注意：2015/05/20之前顾客在一家分店只能看到这家分店的收藏，之后将其改为能看到所有店铺的收藏。
	 * 如果日后还要改动，可以在查询Step1的限制条件里带上subbranch_id，形参中传入即可。
	 * @param string $eid 商家编号
	 * @param string $cid 收藏夹所属顾客
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getCollectionByPage($eid = '', $cid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$coltable = M ( 'collection_product_image' ); // 实例化表结构或视图结构
		$orderby = "add_time desc"; // 定义要排序的方式（每个表都不一样）
		$collectionList = array (); // 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, // 当前商家下
				'customer_id' => $cid,
				'is_del' => 0 // 没有被删除的
		);
		$totalcount = $coltable->where ( $querymap )->count (); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$collectionList = $coltable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询收藏商品信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$collectionList [$i] ['add_time'] = timetodate ( $collectionList [$i] ['add_time'] );
				$collectionList [$i] ['macro_path'] = assemblepath ( $collectionList [$i] ['macro_path'] );
				$collectionList [$i] ['micro_path'] = assemblepath ( $collectionList [$i] ['micro_path'] );
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'collectionlist' => $collectionList
				),
				'nextstart' => $newnextstart
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ajax请求查询我的导购列表。
	 */
	public function queryMyGuide() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
	
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 10; // 每页10条
	
		$requestresult = $this->getMyGuideByPage ( $this->eid, $customer_id, $nextstart, $perpage ); // 分页读取我的导购信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取用户的所有导购信息函数。
	 * @param string $eid 商家编号
	 * @param string $cid 导购所属顾客
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getMyGuideByPage($eid = '', $cid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$gwcitable = M ( 'guide_wechat_customer_info' ); // 实例化表结构或视图结构
		$orderby = "choose_time desc"; // 定义要排序的方式（选择导购的时间）
		$myGuideList = array (); // 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, 		// 当前商家下
				'customer_id' => $cid,	// 这个顾客
				'is_del' => 0 			// 没有被删除的
		);
		$totalcount = $gwcitable->where ( $querymap )->count (); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$myGuideList = $gwcitable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询顾客所选导购信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$myGuideList [$i] ['register_time'] = timetodate ( $myGuideList [$i] ['register_time'] );
				$myGuideList [$i] ['subscribe_time'] = timetodate ( $myGuideList [$i] ['subscribe_time'] );
				$myGuideList [$i] ['head_img_url'] = assemblepath ( $myGuideList [$i] ['head_img_url'] );
			}
		}
		
		$myguidecount = count ( $myGuideList );
		$myguidetable = M ( 'shopguide_subbranch' ); // 修改查询shopguide表变成查询shopguide_subbranch视图（2015/07/02 16:17:25）
		for($i = 0; $i < $myguidecount; $i ++){
			$guide_id = $myGuideList [$i] ['guide_id']; // 导购编号
			$guidemap = array (
					'guide_id' => $guide_id, 
					'is_del' => 0
			);
			$guideinfo = $myguidetable->where ( $guidemap )->find ();
			// 补充导购信息
			$myGuideList [$i] ['guide_number'] = $guideinfo ['guide_number'];
			$myGuideList [$i] ['guide_name'] = $guideinfo ['guide_name'];
			$myGuideList [$i] ['headimg'] = assemblepath ( $guideinfo ['headimg'] );
			$myGuideList [$i] ['guide_level'] = $guideinfo ['guide_level'];
			$myGuideList [$i] ['star_level'] = $guideinfo ['star_level'];
			$myGuideList [$i] ['subbranch_name'] = $guideinfo ['subbranch_name'];
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'myguidelist' => $myGuideList 
				),
				'nextstart' => $newnextstart, // 下一条记录开始的地方
				'totalcount' => $totalcount // 所有的导购数量
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * 分页请求某用户地址信息函数。
	 * （暂时是一次性读取的，如果要做成分页读取，也可以，这里预留。）
	 */
	public function queryDelivery() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 取当前用户的customer_id
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getDeliveryInfoByPage ( $this->eid, $customer_id, $nextstart, $perpage ); // 分页读取收藏的商品信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取某用户地址信息函数。
	 * @param string $eid 商家编号
	 * @param string $customer_id 顾客编号
	 * @param number $nextstart 下一页开始的位置
	 * @param number $perpage 每页记录数
	 * @param string $firstinit 是否页面第一次初始化数据
	 * @return array $deliverylist 该用户的收货地址信息
	 */
	private function getDeliveryInfoByPage($eid = '', $customer_id = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE ) {
		$deliverytable = M ( 'deliveryinfo' ); // 实例化表结构或视图结构
		$orderby = "add_time desc"; // 定义要排序的方式（每个表都不一样）
		$deliverylist = array (); // 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, // 当前商家下
				'customer_id' => $customer_id, // 当前顾客的
				'is_del' => 0 // 没有被删除的
		);
		$totalcount = $deliverytable->where ( $querymap )->count (); // 计算该顾客配送信息的总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$deliverylist = $deliverytable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 分页查询该顾客的配送信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$deliverylist [$i] ['add_time'] = timetodate ( $deliverylist [$i] ['add_time'] );
				$deliverylist [$i] ['latest_modify'] = timetodate ( $deliverylist [$i] ['latest_modify'] );
				$temp = $deliverylist [$i];
				$temphandle = array (
						'id' => $deliverylist [$i] ['deliveryinfo_id'], // 主键
						'country' => "中国", // 地址默认都是中国
						'customerId' => $deliverylist [$i] ['customer_id'], // 顾客编号
						'province' => $deliverylist [$i] ['province'], // 所在省份
						'city' => $deliverylist [$i] ['city'], // 所在城市
						'district' => $deliverylist [$i] ['region'], // 所在地区
						'detail' => $deliverylist [$i] ['receive_address'], // 街道详细地址
						'mobile' => $deliverylist [$i] ['contact_number'], // 联系方式
						'personName' => $deliverylist [$i] ['receive_person'] // 收货人
				);
				$deliverylist [$i] ['jsoninfo'] = jsencode ( $temphandle ); // 将配送信息打包成json
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'deliverylist' => $deliverylist
				),
				'nextstart' => $newnextstart, // 下一页读取的地方
				'totalcount' => $totalcount // 所有配送信息地址总数 
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		
		return $ajaxresult; // 返回ajax信息
	}
	
}
?>