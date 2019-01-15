<?php
/**
 * 企业登录主框架和服务权限控制器。
 * @author 赵臣升，胡福玲。
 * CreateTime:2015/07/18 16:20:36.
 */
class EnterpriseAction extends PCViewLoginAction {
	
	/**
	 * 企业登录后的主页视图。
	 * 查询出企业信息和企业所属服务：顶级导航信息。
	 */
	public function main() {
		$sevice_end_time = $_SESSION ['curEnterprise'] ['service_end_time'];
		$end_date = strtotime(substr($sevice_end_time,0,10));
		$start_date = strtotime("today");
		$betweendays = ($end_date-$start_date)/60/60/24;
		$this->betweendays = $betweendays;
		$this->brandname = $_SESSION ['curEnterprise'] ['e_name']; // 当前企业名字
		
		$customertable = M ( 'customerinfo' );
		// 品牌全国会员数量（查询的是customerinfo表的会员，而不是微信关注用户）
		$cusmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0 
		);
		$allcuscount = $customertable->where ( $cusmap )->count (); // 当前商家所有会员
		                                                            
		// 今日新增会员数
		$cusmap ['_string'] = "register_time >= " . todaystart () . " and register_time <= " . todayend ();
		$todaycuscount = $customertable->where ( $cusmap )->count (); // 计算该商家今日新增会员
		                                                              
		// 今日优惠券使用
		$couponmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_used' => 1, // 要已经使用
				'_string' => "used_time >= " . todaystart () . " and used_time <= " . todayend (),
				'is_del' => 0 
		);
		$couponcount = M ( 'customercoupon' )->where ( $couponmap )->count ();
		
		// 处理企业服务范围内的导航
		$servicemap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'_string' => 'start_date <= current_date() AND end_date >= current_date()', // 在企业服务期限内的服务
				'is_del' => 0 
		);
		$serviceinfo = M ( 'e_service_info' )->where ( $servicemap )->order ( 'nav_level asc, sort asc' )->select (); // 在视图中找出商家享有的导航
		$servicecount = count ( $serviceinfo );
		$rootnav = array (); // 根节点导航
		                     
		// 抓出根节点导航
		for($i = 0; $i < $servicecount; $i ++) {
			if ($serviceinfo [$i] ['father_id'] == '-1') {
				array_push ( $rootnav, $serviceinfo [$i] );
			}
		}
		
		// 抓出二级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count ( $rootnav ); $i ++) {
			$rootnav [$i] ['children'] = array ();
			for($j = 0; $j < count ( $serviceinfo ); $j ++) {
				if ($rootnav [$i] ['servicenav_id'] == $serviceinfo [$j] ['father_id']) {
					array_push ( $rootnav [$i] ['children'], $serviceinfo [$j] );
				}
			}
		}
		
		// 抓出三级节点导航（根节点数组长度比较小，放在外层做循环效率高）
		for($i = 0; $i < count ( $rootnav ); $i ++) {
			// $secondnavlist = $rootnav [$i] ['children']; // 临时变量，理顺逻辑，千万不能推送到临时变量数组中，否则rootnav没反应
			for($j = 0; $j < count ( $rootnav [$i] ['children'] ); $j ++) {
				$rootnav [$i] ['children'] [$j] ['children'] = array ();
				for($k = 0; $k < count ( $serviceinfo ); $k ++) {
					if ($rootnav [$i] ['children'] [$j] ['servicenav_id'] == $serviceinfo [$k] ['father_id']) {
						array_push ( $rootnav [$i] ['children'] [$j] ['children'], $serviceinfo [$k] );
					}
				}
			}
		}
		
		$this->todaycuscount = $todaycuscount;
		$this->couponcount = $couponcount;
		$this->allcuscount = $allcuscount;
		
		$servicenavjsondata ['servicenavlist'] = $rootnav;
		$this->servicenavlistjson = jsencode ( $servicenavjsondata ); // 推送json数据
		$this->servicenavlist = $rootnav;
		
		$this->display ();
	}
	
	/**
	 * 用户登出post处理函数。
	 */
	public function loginOut(){
		session('curEnterprise',null);
		$this->ajaxReturn ( array ( 'errCode' => 0, 'errMsg' => '注销成功!' ), 'json' );
	}
	
	
}
?>