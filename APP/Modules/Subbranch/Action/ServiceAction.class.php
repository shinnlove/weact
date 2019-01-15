<?php
class ServiceAction extends CommonAction {
	public function index(){
		$this->loginshopname = $_SESSION ['curSubbranch'] ['subbranch_name'];
		
		$customertable = M ( 'customerinfo' );
		// 品牌全国会员数量（查询的是customerinfo表的会员，而不是微信关注用户）
		$cusmap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'], // 当前商家下
				'is_del' => 0
		);
		$allcuscount = $customertable->where ( $cusmap )->count ();
		
		// 分店会员数
		$cusmap ['subordinate_shop'] = $_SESSION ['curSubbranch'] ['subbranch_id']; // 当前分店下
		$subcuscount = $customertable->where ( $cusmap )->count (); // 计算该商家的分店会员数
		
		// 今日优惠券使用
		$couponmap = array (
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'], // 当前商家下
				'used_subbranch' => $_SESSION ['curSubbranch'] ['subbranch_id'], // 当前分店下
				'is_used' => 1, // 要已经使用
				'_string' => "used_time >= " . todaystart () . " and used_time <= " . todayend (),
				'is_del' => 0
		);
		$couponcount = M ( 'customercoupon' )->where ( $couponmap )->count ();
		
		$subservice = array(
				'is_del' => 0
		);
		$serviceinfo = M('subservicenav')->where($subservice)->select();	// 找出分店的导航
		$rootnav = array();		// 根节点导航
		
		// 抓出根节点导航
		for($i = 0; $i < count( $serviceinfo ); $i ++){
			if($serviceinfo [$i] ['father_id'] == '-1'){
				array_push($rootnav, $serviceinfo [$i]);
			}
		}
		
		// 抓出二级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count( $rootnav ); $i ++){
			$rootnav [$i] ['children'] = array();
			for($j = 0; $j < count( $serviceinfo ); $j ++){
				if($rootnav [$i] ['sub_servicenav_id'] == $serviceinfo [$j] ['father_id']){
					array_push($rootnav [$i] ['children'], $serviceinfo [$j]);
				}
			}
		}
		
		// 抓出三级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count( $rootnav ); $i ++){
			//$secondnavlist = $rootnav [$i] ['children'];	// 临时变量，理顺逻辑，千万不能推送到临时变量数组中，否则rootnav没反应
			for($j = 0; $j < count( $rootnav [$i] ['children'] ); $j ++){
				$rootnav [$i] ['children'] [$j] ['children'] = array();
				for($k = 0; $k < count( $serviceinfo ); $k ++){
					if($rootnav [$i] ['children'] [$j] ['sub_servicenav_id'] == $serviceinfo [$k] ['father_id']){
						array_push($rootnav [$i] ['children'] [$j] ['children'], $serviceinfo [$k]);
					}
				}
			}
		}
		
		$this->allcuscount = $allcuscount;
		$this->subcuscount = $subcuscount;
		$this->couponcount = $couponcount;
		
		$servicenavjsondata ['servicenavlist'] = $rootnav; 
		$this->servicenavlistjson = jsencode ( $servicenavjsondata ); // 推送json数据
		$this->servicenavlist = $rootnav;
		$this->display();
	}
	
	/**
	 * 初始化顶部导航按钮。
	 */
	public function initTopNav(){
		$subservice = array(
			'is_del' => 0
		);
		$serviceinfo = M('subservicenav')->where($subservice)->select();	// 找出分店的导航
		$rootnav = array();		// 根节点导航
		
		// 抓出根节点导航
		for($i = 0; $i < count( $serviceinfo ); $i ++){
			if($serviceinfo [$i] ['father_id'] == '-1'){
				array_push($rootnav, $serviceinfo [$i]);
			}
		}
		
		// 抓出二级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count( $rootnav ); $i ++){
			$rootnav [$i] ['children'] = array();
			for($j = 0; $j < count( $serviceinfo ); $j ++){
				if($rootnav [$i] ['sub_servicenav_id'] == $serviceinfo [$j] ['father_id']){
					array_push($rootnav [$i] ['children'], $serviceinfo [$j]);
				}
			}
		}
		
		// 抓出三级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count( $rootnav ); $i ++){
			//$secondnavlist = $rootnav [$i] ['children'];	// 临时变量，理顺逻辑，千万不能推送到临时变量数组中，否则rootnav没反应
			for($j = 0; $j < count( $rootnav [$i] ['children'] ); $j ++){
				$rootnav [$i] ['children'] [$j] ['children'] = array();
				for($k = 0; $k < count( $serviceinfo ); $k ++){
					if($rootnav [$i] ['children'] [$j] ['sub_servicenav_id'] == $serviceinfo [$k] ['father_id']){
						array_push($rootnav [$i] ['children'] [$j] ['children'], $serviceinfo [$k]);
					}
				}
			}
		}
		//p($rootnav);die;
		$this->ajaxReturn( $rootnav );
	}
	
}
?>