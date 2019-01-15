<?php
/**
 * 分店店铺控制器。
 * @author Administrator
 * CreateTime:2015/04/29 14:02:25.
 */
class StoreAction extends GuestMallAction {
	/**
	 * 新店铺视图/限制列表（只显示绑定导购的店铺和最近3家店铺）。
	 */
	public function storeList() {
		$defaultlongitude = 116.3; // 默认在北京
		$defaultlatitude = 39.9; // 默认在北京
	
		$bindshopinfo = array (); // 所绑定的店铺信息（如果绑定，现在只绑定一家，如果现实也只显示一家）
		$subbranchlist = array (); // 最近X家店铺信息
	
		$nextstart = 0; // 进入店铺列表，从第一页开始读取
		$perpage = 3; // 默认只显示最近3家门店
	
		// 查询企业是否开启所有店铺都显示
		$emap = array (
				'e_id' => $this->eid,
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		$showall = $einfo ['all_subbranch']; // 是否显示所有分店
		if ($showall) {
			$perpage = 10; // 如果坚持显示所有分店，店铺列表还是按序最近10家
		} else {
			$showall = 0; // 默认不显示状态
		}
	
		// 检测当前游客是否绑定过导购
		if (isset ( $_SESSION ['currentcustomer'] )) {
			// 如果登录
			$checkguidemap = array (
					'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前登录的顾客
					'e_id' => $this->eid, // 当前商家
					'is_del' => 0
			);
			$selectedguide = M ( 'customerguide' )->where ( $checkguidemap )->find (); // 检测该顾客是否有导购
			if ($selectedguide) {
				// 如果有导购，显示该顾客绑定导购的分店信息
				$eid = $this->eid;
				$sid = $selectedguide ['subbranch_id']; // 提取出所选择的导购分店编号
				$query = "SELECT guide_id, guide_name, subbranch_id, subbranch_name, subbranch_address, image_path, add_time, latest_modify, image_path, longitude, latitude, calc_distance_function($defaultlongitude,$defaultlatitude,longitude,latitude) as distance from t_shopguide_subbranch ".
						"where e_id = '$eid' and subbranch_id = '$sid' and closed_status = 0 order by distance asc limit 1"; // 现在只查询一条当前顾客所绑定导购的分店信息
				$bindshopinfo = M ()->query ( $query );
				$bindcount = count ( $bindshopinfo ); // 店铺绑定数量
				// 格式化绑定店铺的信息（转换时间或路径等）
				for($i = 0; $i < $bindcount; $i ++) {
					$bindshopinfo [$i] ['add_time'] = timetodate ( $bindshopinfo [$i] ['add_time'] );
					$bindshopinfo [$i] ['latest_modify'] = timetodate ( $bindshopinfo [$i] ['latest_modify'] );
					$bindshopinfo [$i] ['image_path'] = assemblepath ( $bindshopinfo [$i] ['image_path'] );
					// 处理是否最近逛过
					//$subbranchlist [$i] ['is_visited'] = 1;
					$bindshopinfo [$i] ['is_visited'] = 0; // 临时关闭：2015/05/26 06:35:25临时关闭，建议尽快开启。
					// 处理经纬度
					if (! isset ( $bindshopinfo [$i] ['longitude'] )) {
						$subbranchlist [$i] ['longitude'] = 116.3;
					}
					if (! isset ( $bindshopinfo [$i] ['latitude'] )) {
						$subbranchlist [$i] ['latitude'] = 39.9;
					}
					//$subbranchlist [$i] ['distance'] = $this->formatDistance ( $this->getDistance ( $longitude, $latitude, $subbranchlist [$i] ['longitude'], $subbranchlist [$i] ['latitude'] ) );
					// 采用新的格式化
					$bindshopinfo [$i] ['distance'] = $this->formatDistance ( $bindshopinfo [$i] ['distance'] );
				}
			}
		}
	
		// 查询最近店铺
		$subbranchlist = $this->getSubbranchByPage ( $defaultlongitude, $defaultlatitude, $this->eid, $nextstart, $perpage ); // 查询分店列表
	
		// 打包数据
		$limitsubbranch = array (
				'data' => array (
						'bindlist' => array (
								'subbranchlist' => $bindshopinfo, // 绑定店铺列表
						),
						'nearlist' => $subbranchlist ['data'], // 最近X家店铺
				),
				'nextstart' => $subbranchlist ['nextstart'], // 下一页开始
				'totalcount' => $subbranchlist ['totalcount'], // 总的计数
		);
	
		// 推送数据
		$this->showall = $showall;
		$this->perpage = $perpage;
		$this->limitsubbranchjson = jsencode ( $limitsubbranch ); // js压缩数据
		$this->display ();
	}
	
	/**
	 * ajax分页请求查询店铺列表处理函数。
	 */
	public function querySubbranch() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意进入
		
		// 设置请求参数
		$longitude = I ( 'longitude', 116.3 ); // 接收用户经度
		$latitude = I ( 'latitude', 39.9 ); // 接收用户纬度
		$nextstart = I ( 'nextstart', 0 ); // 下一页开始s
		$perpage = I ( 'perpage', 10 ); // 默认每页10条店铺
		
		// 分页查询商品
		$subbranchlist = $this->getSubbranchByPage ( $longitude, $latitude, $this->eid, $nextstart, $perpage ); // 查询分店列表
		$this->ajaxReturn ( $subbranchlist ); // ajax返回
	}
	
	/**
	 * 分页读取店铺函数。
	 * @param float $longitude 用户所在经度，默认在北京
	 * @param float $latitude 用户所在纬度，默认在北京
	 * @param string $eid 本次要请求的分店数据所属商家
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getSubbranchByPage($longitude = 116.3, $latitude = 39.9, $eid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$subtable = M ( 'subbranch' ); 							// 实例化表结构或视图结构
		$orderby = "add_time desc, latest_modify desc"; 			// 定义要排序的方式（每个表都不一样）
		$subbranchlist = array (); 										// 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, 			// 当前商家下
				'closed_status' => 0, 		// 开店状态下
				'is_del' => 0 				// 没有被删除的
		);
		$totalcount = $subtable->where ( $querymap )->count (); 	// 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
	
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
	
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
	
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
	
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			//$subbranchlist = $subtable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询附近店铺信息
			// 按距离远近排序
			$query = "SELECT subbranch_id, subbranch_name, subbranch_address, image_path, calc_distance_function($longitude,$latitude,longitude,latitude) as distance from t_subbranch ".
					 "where e_id = '$eid' and closed_status = 0 and is_del = 0 order by distance asc limit $nextstart, $realgetnum";
			$subbranchlist = M ()->query ( $query );
			
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$subbranchlist [$i] ['add_time'] = timetodate ( $subbranchlist [$i] ['add_time'] );
				$subbranchlist [$i] ['latest_modify'] = timetodate ( $subbranchlist [$i] ['latest_modify'] );
				$subbranchlist [$i] ['image_path'] = assemblepath ( $subbranchlist [$i] ['image_path'] );
				// 处理是否最近逛过
				//$subbranchlist [$i] ['is_visited'] = 1;
				$subbranchlist [$i] ['is_visited'] = 0; // 临时关闭：2015/05/26 06:35:25临时关闭，建议尽快开启。
				// 处理经纬度
				if (! isset ( $subbranchlist [$i] ['longitude'] )) {
					$subbranchlist [$i] ['longitude'] = 116.3;
				}
				if (! isset ( $subbranchlist [$i] ['latitude'] )) {
					$subbranchlist [$i] ['latitude'] = 39.9;
				}
				//$subbranchlist [$i] ['distance'] = $this->formatDistance ( $this->getDistance ( $longitude, $latitude, $subbranchlist [$i] ['longitude'], $subbranchlist [$i] ['latitude'] ) );
				// 采用新的格式化
				$subbranchlist [$i] ['distance'] = $this->formatDistance ( $subbranchlist [$i] ['distance'] );
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'subbranchlist' => $subbranchlist // 分店地址列表
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
	 * 店铺主页视图。
	 */
	public function storeIndex() {
		// 额外面对选择导购跳转图文的参数：
		$selectskip = I ( 'selectskip', 0 ); // 选择导购后图文页面超链接跳转
		
		// 设置请求参数：基本参数部分
		$nextstart = 0; 				// 从0开始查询
		$perpage = 10; 					// 默认每页10条商品
		$sortfield = "onshelf_time"; 	// 默认按上架时间排序
		$sortorder = "desc"; 			// 默认排序0是desc
		// 设置请求参数：搜索方式参数部分（默认优先查询关键字，然后是导航，然后是属性分类）
		$searchparams ['querytype'] = 1; // 店铺精选
		
		// 分页查询商品
		$pro = A ( 'WeMall/Product' ); // 实例化控制器
		$finalresult = $pro->getProductByPage ( $this->sid, $searchparams, $nextstart, $perpage, $sortfield, $sortorder, true ); // 查询商品列表
		
		// 分页查询导购
		$guide = A ( 'WeMall/Guide' );
		$guideresult = $guide->getGuideByPage ( $this->eid, $this->sid, $nextstart, $perpage, true ); // 查询导购列表
		
		// 检测顾客是否登录，如果登录，是否在该分店有导购
		$hasguide = 0; // 默认当前顾客在该分店没有选过导购
		if (isset ( $_SESSION ['currentcustomer'] )) {
			// 如果登录
			$checkguidemap = array (
					'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前登录的顾客
					'e_id' => $this->eid, // 当前商家
					'subbranch_id' => $this->sid, // 当前分店
					'is_del' => 0
			);
			$guideinfo = M ( 'guide_customer_info' )->where ( $checkguidemap )->find (); // 查询该顾客在当前门店下的导购信息
			if ($guideinfo) {
				$hasguide = 1; // 有导购
				$this->gname = $guideinfo ['guide_name']; // 推送导购名字
			}
		}
		
		$this->selectd = $selectskip; 						// 是否需要跳过选导购（选择导购后的图文跳转）
		$this->hasguide = $hasguide; 						// 推送顾客是否有导购
		$this->querytype = $searchparams ['querytype']; 	// 推送查询类型（店铺精选）
		$this->productlistjson = jsencode ( $finalresult ); // 将数据打包成json
		$this->guidelistjson = jsencode ( $guideresult ); 	// 将导购数据打包成json
		$this->display ();
	}
	
	/**
	 * 店铺详情。
	 */
	public function storeDetail() {
		// 查询店铺的导购信息
		$nextstart = 0; // 读取所有导购
		$perpage = 1000; // 默认最大1000个导购
		$ga = A ( 'WeMall/Guide' ); // 实例化导购控制器
		$guidelist = $ga->getGuideByPage ( $this->eid, $this->sid, $nextstart, $perpage, true );
		$this->guidelistjson = jsencode ( $guidelist );
		$this->display ();
	}
	
	/**
	 * 例子：计算两点之间的距离。
	 */
	public function testDistance() {
		$lng1 = 121.215508;
		$lat1 = 31.050747;
		$lng2 = 121.007907;
		$lat2 = 30.690789;
		$distance = $this->getDistance ( $lng1, $lat1, $lng2, $lat2 );
		p ( $distance ); die ();
	}
	
	/**
	 * 根据两点间的经纬度计算距离。
	 * @param float $lng 经度值
	 * @param float $lat 纬度值
	 * @return float $distance 距离（米）
	 */
	public function getDistance($lng1, $lat1, $lng2, $lat2) {
		$earthRadius = 6371393; // approximate radius of earth in meters
		
		/* Convert these degrees to radians to work with the formula */
		$lat1 = ($lat1 * pi ()) / 180;
		$lng1 = ($lng1 * pi ()) / 180;
		$lat2 = ($lat2 * pi ()) / 180;
		$lng2 = ($lng2 * pi ()) / 180;
		
		/* Using the Haversine formula http://en.wikipedia.org/wiki/Haversine_formula calculate the distance */
		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow ( sin ( $calcLatitude / 2 ), 2 ) + cos ( $lat1 ) * cos ( $lat2 ) * pow ( sin ( $calcLongitude / 2 ), 2 );
		$stepTwo = 2 * asin ( min ( 1, sqrt ( $stepOne ) ) );
		$calculatedDistance = $earthRadius * $stepTwo;
		
		return round ( $calculatedDistance );
	}
	
	/**
	 * 格式化店铺距离。
	 * @param number $meters 距离米数
	 * @return string $format 格式化的结果
	 */
	public function formatDistance($meters = 1.0){
		$format = "未知距离"; // 格式化距离结果
		if ($meters >= 0 && $meters < 100) {
			$format = "<100米";
		} else if ($meters >= 100 && $meters < 200) {
			$format = "<200米";
		} else if ($meters >= 200 && $meters < 500) {
			$format = "<500米";
		} else if ($meters >= 500 && $meters < 1000) {
			$format = "<1千米";
		} else if ($meters >= 1000 && $meters < 2000) {
			$format = "<2千米";
		} else if ($meters >= 2000 && $meters < 5000) {
			$format = "<5千米";
		} else if ($meters >= 5000 && $meters < 10000) {
			$format = "<10千米";
		} else if ($meters >= 10000 && $meters < 15000) {
			$format = "<15千米";
		} else if ($meters >= 15000 && $meters < 20000) {
			$format = "<20千米";
		} else if ($meters >= 20000 && $meters < 30000) {
			$format = "<30千米";
		} else if ($meters >= 30000 && $meters < 40000) {
			$format = "<40千米";
		} else if ($meters >= 40000 && $meters < 50000) {
			$format = "<50千米";
		} else if ($meters >= 50000 && $meters < 60000) {
			$format = "<60千米";
		} else if ($meters >= 60000 && $meters < 70000) {
			$format = "<70千米";
		} else if ($meters >= 70000 && $meters < 80000) {
			$format = "<80千米";
		} else if ($meters >= 80000 && $meters < 90000) {
			$format = "<90千米";
		} else if ($meters >= 90000 && $meters < 100000) {
			$format = "<100千米";
		} else if ($meters >= 100000 && $meters < 120000) {
			$format = "<120千米";
		} else if ($meters >= 120000 && $meters < 140000) {
			$format = "<140千米";
		} else if ($meters >= 140000 && $meters < 150000) {
			$format = "<150千米";
		} else if ($meters >= 150000 && $meters < 160000) {
			$format = "<160千米";
		} else if ($meters >= 160000 && $meters < 180000) {
			$format = "<180千米";
		} else if ($meters >= 180000 && $meters < 200000) {
			$format = "<200千米";
		} else {
			$format = ">200千米";
		}
		return $format;
	}
	
}
?>