<?php
/**
 * 导购业绩数据控制器。
 * 该控制器实现如下功能：
 * 1、查询导购今日在线上订单的提成；
 * 2、查询导购所服务粉丝在线上成交的订单；
 * 3、查询这些订单的总价；
 * 4、查询导购所服务粉丝本周在线上交易订单的总价；
 * 5、查询导购的新增会员和会员数量；
 * 6、查询导购的业绩榜单排名（点击进入店铺内部导购排名，tab菜单切换店铺间业绩排名）
 * @author 微动团队，胡睿。
 * CreateTime 2015/03/11 21:17:00
 */
class PerformanceIndicatorAction extends GuideAppCommonAction {
	
	/**
	 * 导购业绩数据视图。
	 * 由三方跳转微动给出的URL地址，并带上相关三个参数。
	 * 微动接收参数并查询该导购当前的业绩情况。
	 */
	public function guidePerformance() {
		// Step1：
		$this->todayPerformance ();
		// Step2：
		$this->weekPerformance ();
		// Step3：
		$this->todayNewFansCount ();
		// Step4：
		$this->totalFansCount ();
		
		$this->display ();
	}
	
	/**
	 * 获取导购业绩。
	 *
	 * @return array $ajaxresult
	 */
	public function getPerformanceData() {
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！" 
		);
		
		$gid = $this->gid;
		$sid = $this->sid;
		
		$todayPerformance = $this->getPerformanceByType ( 1, $gid );
		$list ['todaycut'] = $todayPerformance ['cut'];
		$list ['todaynum'] = $todayPerformance ['num'];
		$list ['todayvolume'] = $todayPerformance ['volume'];
		$weekPerformance = $this->getPerformanceByType ( 2, $gid );
		$list ['weekcut'] = $weekPerformance ['cut'];
		$list ['weeknum'] = $weekPerformance ['num'];
		$list ['weekvolume'] = $weekPerformance ['volume'];
		$list ['rankNum'] = $this->getRankNum ( $gid );
		$list ['todayNewFans'] = $this->getTodayNewFansCount ( $gid );
		$list ['totalFans'] = $this->getTotalFansCount ( $gid );
		
		$this->todaycut = $list['todaycut'];
		$this->todaynum = $list['todaynum'];
		$this->todayvolume = $list['todayvolume'];

		$this->weekcut = $list['weekcut'];
		$this->weeknum = $list['weeknum'];
		$this->weekvolume = $list['weekvolume'];
		$this->rankNum = $list['rankNum'];
		$this->todayNewFans = $list['todayNewFans'];
		$this->totalFans = $list['totalFans'];		
		$this->display();
	}
	
	/**
	 * 统计导购订单提成、成交数、销售额
	 *
	 * @param int $type
	 *        	查询类型 日 、周 ( $type 分别为1、2)
	 * @param string $gid
	 *        	导购编号
	 * @return array
	 */
	private function getPerformanceByType($type = 1, $gid = '') {
		$array = array (
				'cut' => 0,		// 统计导购提成
				'num' => 0,		// 统计成交数
				'volume' => 0 	// 统计销售额
		);
		// 统计导购提成 ( change_type 0为提成 1为提现)
		$map = array (
				'is_del' => 0,
				'guide_id' => $gid,
				'change_type'=>0,
				'is_cancel' => 0 
		);
		if ($type == 1)
			$map ['_string'] = 'DATE_FORMAT(FROM_UNIXTIME(add_time),"%Y-%m-%d")  = curdate()';
		if ($type == 2)
			$map ['_string'] = 'YEARWEEK(CURDATE()) = YEARWEEK(FROM_UNIXTIME(add_time))';
		$result = M ( 'guideprofit' )->field ( "sum(moneychanged) as cut" )->where ( $map )->find ();
		if (! empty ( $result )) {
			$array ['cut'] = (empty ( $result ['cut'] )) ? 0 : $result ['cut'];
		}
		// 统计导购成交数和销售额
		$dealmap = array(
				'guide_id'=>$gid,
				'is_payed'=> 1,
				'timeout_cancel'=>0,
				'consult_cancel'=>0,
				'is_refund'=>0,
				'is_del'=>0
		);
		if ($type == 1)
			$dealmap ['_string'] = 'DATE_FORMAT(FROM_UNIXTIME(order_time),"%Y-%m-%d")  = curdate()';
		if ($type == 2)
			$dealmap ['_string'] = 'YEARWEEK(CURDATE()) = YEARWEEK(FROM_UNIXTIME(order_time))';
		$dealresult = M("ordermain")->field("ifnull( count(order_id), 0) as num, ifnull(sum(total_price),0.00) as volume")
		->where($dealmap)->find();
		$array['num'] = $dealresult['num'];
		$array['volume'] = $dealresult['volume'];		
		return $array;
	}
	
	/**
	 * 查询业绩榜单（显示的是该导购在该分店内部排名） 月度排行第几
	 *
	 * @param string $gid
	 *        	导购编号
	 * @return int
	 */
	private function getRankNum($gid = '') {
		if (empty ( $gid )) // 导购编号或者分店编号未定义、为0、为空字符串
			return 0;
			// 实例化空模型
		$Model = new Model ();
		$result = $Model->query ( "SELECT getGuideRankNum('$gid') as rank" );
		return $result [0] ['rank'];
	}
	
	/**
	 * 统计导购今日新增粉丝数量。
	 * 要得到这个信息，需要查询顾客选换导购表，要distinct，并且在消费者端增加每日导购选换次数限制
	 *
	 * @param string $gid
	 *        	导购编号
	 * @return int
	 */
	private function getTodayNewFansCount($gid) {
		$map = array (
				'is_del' => 0,
				'_string' => 'DATE_FORMAT(FROM_UNIXTIME(change_time),"%Y-%m-%d")  = curdate()',
				'guide_id' => $gid 
		);
		$result = M ( 'changeguiderecord' )->where ( $map )->Distinct ( true )->field ( 'customer_id' )->count ();
		if (! empty ( $result )) // $result非未定义、非0、非空字符串
			return $result;
		else // 未查询到相关记录,那么说明今日新增粉丝数量为0
			return 0;
	}
	
	/**
	 * PART4：统计导购总的粉丝数量。
	 * 需要查询guide_fans_num表的guide_id\fans_num
	 *
	 * @param string $gid
	 *        	导购编号
	 * @return int
	 */
	private function getTotalFansCount($gid) {
		if (empty ( $gid ))
			return 0; // 没有传导购编号直接返回前台
		$map = array (
				'is_del' => 0,
				'guide_id' => $gid 
		);
		$result = M ( 'guide_fans_num' )->where ( $map )->find ();
		if (! empty ( $result )) // $result为非未定义、非0、非空字符串
			return $result['fans_num'];
		else // 未查询到相关记录,那么说明粉丝数量为0
			return 0;
	}
	
	/**
	 * 设计思路：
	 * 业绩排行页面（包含店内导购排行、店铺之间业绩排行）(查询的是月度排行榜)，
	 * 默认显示店内导购业绩排行，
	 * 1、店内导购排行，接收导购参数后，要把自己的排名行高亮显示；
	 * 2、店铺之间排行，接收导购参数后，要查询导购所在的店铺，将该排行高亮。
	 */
	public function performanceRank() {
		// Step1：打开业绩排行页面，默认先显示本店铺内部导购排名
		$querytype = I ( 'querytype', 1 ); // querytype = 1代表查询导购；querytype = 2代表查询店铺排名
		$startindex = 0; // 默认起始页是0
		$count = 10; // 默认一页10条数据
		$firstInitData = TRUE; // 默认打开页面初始化数据
		                       
		// Step2：查询数据并打包
		$jsondata = $this->queryRankByPage ( $querytype, $startindex, $count, $firstInitData ); // 调用分页查询排名函数
		$ajaxinfo = json_encode ( $jsondata ); // 打包成json数据
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo ); // 对数据进行转义处理（前台页面防止出错）
		
		// Step3：推送数据给页面
		$this->rankjson = $finaljson; // 推送最终页面打开时，第一次的json数据
		$this->querytype = $querytype; // 推送给前台查询类型（1查询导购、2查询店铺）
		$this->display ();
	}
	
	/**
	 * 响应页面ajax查询参数的处理函数。
	 * 这是点击页卡（tab菜单）或上推下一页、下拉刷新等触发js查询业绩事件，响应post查询并返回。
	 */
	public function queryPerformanceInfo() {
		$querytype = I ( 'querytype', 1 ); // 查询类型
		$startindex = $_REQUEST ['nextStart']; // 下一页开始的位置
		$count = 10; // 每页查询几条
		$firstInitData = FALSE; // 是否初始化页面，这里是ajax分页处理，为false
		$ajaxinfo = $this->queryRankByPage ( $querytype, $startindex, $count, $firstInitData ); // 分页查询数据
		$this->ajaxReturn ( $ajaxinfo ); // 返回给前台数据
	}
	
	/**
	 * 分页查询月度业绩排行的函数(根据order_price求和进行排名的统计)
	 * @param number $querytype 查询类型，1为查询店铺内部导购排名；2为查询同一e_id下店铺之间排名
	 * @param number 本次请求从第几页开始
	 * @param number 本次请求需要一页多少数据量
	 * @param bool $firstInitData 是否是第一次打开页面的初始化数据
	 */
	public function queryRankByPage($querytype = 1, $startindex = 0, $count = 10, $firstInitData = FALSE) {
		$e_id = $this->eid;
		$subbranch_id = $this->sid;
		$guide_id = $this->gid;
		
		$realcount = 0;
		$ranklist = array (); // 排名数组
		if ($querytype == 1) {
			// 对于店铺内部导购排名,添加送入导购分店id
			// 建立查询(通过subbranch_id删选下)
			$ranklist = M ( "guide_rank" )->where ( "subbranch_id = '$subbranch_id'" )->order ( 'total_price desc' )->limit ( $startindex, $count )->select ();
			if ($ranklist) {
				// $result为非未定义、非0、非空字符串
				// 返回真实得到的条数
				$realcount = count ( $ranklist );
				for($i = 0; $i < $realcount; $i ++) {
					$ranklist [$i] ['is_mine'] = ($ranklist [$i] ['guide_id'] == $guide_id) ? 1 : 0; // 是不是当前导购
					$ranklist [$i] ['headimg'] = assemblepath ( $ranklist [$i] ['headimg'], true ); // 组装导购头像路径
				}
			}
		} else {
			// 通过e_id删选下	同一e_id下店铺之间排名
			$ranklist = M ( "subbranch_rank" )->where ( "e_id = '$e_id'" )->order ( 'total_price desc' )->limit ( $startindex, $count )->select ();
			if ($ranklist) {
				// $result为非未定义、非0、非空字符串
				// 返回真实得到的条数
				$realcount = count ( $ranklist );
				for($i = 0; $i < $realcount; $i ++) {
					$ranklist [$i] ['is_mine'] = ($ranklist [$i] ['subbranch_id'] == $subbranch_id) ? 1 : 0; // 是不是当前商铺
					$ranklist [$i] ['image_path'] = assemblepath ( $ranklist [$i] ['image_path'], true ); // 组装分店LOGO图片
				}
			}
		}
		if (! $ranklist)
			 $ranklist = array (); // 如果数据是null，立刻重置下空数组
		// 准备返回前台的信息
		$ajaxresult = array (
				'data' => array (
						'ranklist' => $ranklist 
				),
				'nextStart' => ($startindex + $realcount) 
		);
		if (! $firstInitData) {
			// 不是第一次打开页面调用，则需要带上ajax通信标志
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
}
?>