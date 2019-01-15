<?php
/**
 * 提效神器控制器，
 * 该控制器处理导购业绩目标显示，统计导购已完成的业绩数量，
 * 计算未完成的数量，剩余的天数，做除法算出每日还需完成多少指标业绩才能达标
 * @author 微动团队。
 * CreateTime 2015/03/11 21:17:00.
 */
class PromoteEfficiencyAction extends GuideAppCommonAction {
	/**
	 * 业绩目标展示页面
	 * 该页面由三方跳转，带上参数商家编号、分店编号、导购编号
	 * 导购业绩目标展示、已经完成的业绩、未完成的数量；
	 * 当月剩余的天数，计算出平均每天还需完成量；
	 * 将数据推送到页面上
	 */
	public function accelerateEfficiency() {
		$eid = $this->eid;
		$sid = $this->sid;
		$gid = $this->gid;
		// 数据库中需要解决导购业绩目标,导购当日业绩，当月业绩，
		$Model = new Model ();
		// 此处查询已经考虑了未查到结果的情况，那么置为0
		$query = "SELECT getGuideSalesTarget('$gid') as target, getGuideTodaySales('$gid') as todaySales, 
		getGuideMonthSales('$gid') as monthSales";
		$list = $Model->query ( $query );
		// 查询结果
		$result = $list[0];
		// 销量查询结果
		$salesTarget = $result['target'];
		$salesToday = $result['todaySales'];
		$salesMonth = $result['monthSales'];
		// 查询本月一共多少天
		$BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
		$EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
		$NowDate = date("Y-m-d",time());
		// 时间统计
		$daysPass = $this->getDiffDay($BeginDate, $NowDate); // 从月初到昨晚23:59:59已过去时间
		$daysHave = $this->getDiffDay($NowDate, $EndDate) + 1; // 从今天00:00:00到月底时间
		// 月度指标显示、月度已完成业绩、月度未完成业绩
		// 剩余天数、每天需完成业绩
		// 本日已完成业绩、本日还需业绩
		$salesData = array();
		// 平均日业绩
		$salesData['averDayFinish'] = sprintf ( "%.2f", $salesMonth / $daysPass);
		$salesData ['monthTarget'] = sprintf ( "%.2f",$salesTarget);
		$salesData ['monthFinish'] = sprintf ( "%.2f",$salesMonth);
		$monthSalesDiff = sprintf ( "%.2f",$salesTarget - $salesMonth);
		$salesData ['monthLeft'] = ($monthSalesDiff >= 0) ? $monthSalesDiff : 0;
		$salesData ['daysHave'] = $daysHave;
		$daysTargetSales = sprintf ( "%.2f", $salesData ['monthLeft'] / $daysHave ); // 最小为1，不用考虑0值的可能性
		$salesData ['daysTarget'] = sprintf ( "%.2f",$daysTargetSales); // 每日指标(包括今天在内)
		$salesData ['todayFinish'] = sprintf ( "%.2f",$salesToday); // 今日已完成指标
		$daySalesDiff = sprintf ( "%.2f",$daysTargetSales - $salesToday);
		$salesData ['todayLeft'] = ($daySalesDiff >= 0) ? $daySalesDiff : 0;		
		$timenow = time ();
		$today = timetodate ( $timenow, true );
		$salesData ['today'] = $today; // 今天的日期
		$this->data = $salesData;

		// 最开始本月业绩目标为0的时候，设置一个最初的百分比
		$lastFinishPer = 100;	// 截止到昨天完成的百分比
		$todayFinishPer = 0; 	// 今日完成百分比
		$leftPer = 0; 		// 剩余百分比
		
		if( $salesTarget != 0)
		{
			$lastFinishPer = ($salesMonth - $salesToday) / $salesTarget * 100;
			$lastFinishPer = ($lastFinishPer > 100) ? 100 : $lastFinishPer;			
			// 如果截止到今天凌晨已经完成目标
			if ($lastFinishPer == 100) {
				$todayFinishPer = 0; // 今日完成百分比
				$leftPer = 0; // 剩余百分比
			} 
			else {	// 未完成目标，剩余百分比在(0-100)之间
					$todayFinishPer = $salesToday / $salesTarget * 100;	// 今天完成的百分比
					// 根据截止到今天的销售额是否达标，来决定今日占据的百分比
					$todayFinishPer = ($salesMonth > $salesTarget) ? (100 - $lastFinishPer) : $todayFinishPer;		
					$leftPer = 100-$lastFinishPer-$todayFinishPer;
				}
		}
		$pieData['lastFinishPer'] = sprintf ( "%.2f",$lastFinishPer);
		$pieData['todayFinishPer'] = sprintf ( "%.2f",$todayFinishPer);
		$pieData['leftPer'] = sprintf ( "%.2f",$leftPer);
		
		$this->pieData = $pieData; 
		$this->display();
	}
	
	/**
	 * 查询两个日期的间隔时间（1号跟2号算作相隔一天）
	 *
	 * @param date $begindate 起始时间 格式：2015-02-03
	 * @param date $enddate 结束时间格式: 2015-02-26
	 * @return int 两个日期间隔的天数
	 */
	private function getDiffDay($begindate, $enddate) {
		$Date_Format_Begin = explode ( "-", $begindate );		
		$Date_Format_End = explode ( "-", $enddate );		
		$d1 = mktime ( 0, 0, 0, $Date_Format_Begin[1], $Date_Format_Begin[2], $Date_Format_Begin [0] );		
		$d2 = mktime ( 0, 0, 0, $Date_Format_End [1], $Date_Format_End [2], $Date_Format_End [0] );		
		$d = round ( ($d2 - $d1) / 3600 / 24 );
		return $d;
	}
	
}
?>