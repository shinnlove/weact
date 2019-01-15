<?php
/**
 * 导购控制器。
 * @author Administrator
 * CreateTime:2015/04/29 14:02:25.
 */
class GuideAction extends GuestMallAction {
	/**
	 * 店铺导购列表。
	 */
	public function guideList() {
		$this->display ();
	}
	
	/**
	 * ajax请求查询导购列表。
	 */
	public function queryGuideList() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$eid = $this->eid; // 商家编号
		$sid = $this->sid; // 分店编号
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getGuideByPage ( $eid, $sid, $nextstart, $perpage ); // 分页读取导购信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取导购信息函数。
	 * @param string $eid 商家编号
	 * @param string $sid 分店编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getGuideByPage($eid = '', $sid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$guidetable = M ( 'shopguide' ); // 实例化表结构或视图结构
		$orderby = "star_level desc, latest_modify desc"; // 定义要排序的方式（每个表都不一样）
		$guidelist = array (); // 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, 			// 当前商家下
				'subbranch_id' => $sid, 	// 当前分店下
				'is_del' => 0 				// 没有被删除的
		);
		
		$totalcount = $guidetable->where ( $querymap )->count (); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
		                                          
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$guidelist = $guidetable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询商品信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$guidelist [$i] ['add_time'] = timetodate ( $guidelist [$i] ['add_time'] );
				$guidelist [$i] ['latest_modify'] = timetodate ( $guidelist [$i] ['latest_modify'] );
				$guidelist [$i] ['headimg'] = assemblepath ( $guidelist [$i] ['headimg'] );
				unset($guidelist [$i] ['account']); // 账号不给
				unset($guidelist [$i] ['password']); // 密码不给
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'guidelist' => $guidelist 
				),
				'nextstart' => $newnextstart, // 下一页开始的位置
				'totalcount' => $totalcount // 总得导购数量
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * ajax在我的导购页面请求更换其他导购。
	 */
	public function shopOtherGuide() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$eid = $this->eid; // 商家编号
		$sid = $this->sid; // 分店编号
		$cgid = I ( 'cgid' ); // 当前顾客所选的导购编号（不显示这个导购）
		$nextstart = I ( 'nextstart', 0 ); // 下一页导购开始的下标，默认为0
		$perpage = 1000; // 每页1000条（一次性显示完整多少导购）
		
		$requestresult = $this->getOtherGuideByPage ( $eid, $sid, $cgid, $nextstart, $perpage ); // 分页读取该店铺其他导购信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取某店铺其他导购信息函数。
	 * @param string $eid 商家编号
	 * @param string $sid 分店编号
	 * @param string $cgid 当前所选导购编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getOtherGuideByPage($eid = '', $sid = '', $cgid = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$guidetable = M ( 'shopguide' ); // 实例化表结构或视图结构
		$orderby = "star_level desc, latest_modify desc"; // 定义要排序的方式（每个表都不一样）
		$guidelist = array (); // 本次请求的数据
	
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'e_id' => $eid, 						// 当前商家下
				'subbranch_id' => $sid, 				// 当前分店下
				'guide_id' => array ( "neq", $cgid ), 	// 不是当前导购
				'is_del' => 0 							// 没有被删除的
		);
	
		$totalcount = $guidetable->where ( $querymap )->count (); // 计算总数量
	
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; // 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
	
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
	
		if ($realgetnum < 0) $realgetnum = 0; // 必要的容错处理，防止$nextstart大于总数量
	
		$newnextstart = $nextstart + $realgetnum; // 本次如果请求成功，下一次再请求数据记录开始的下标
	
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$guidelist = $guidetable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询商品信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$guidelist [$i] ['add_time'] = timetodate ( $guidelist [$i] ['add_time'] );
				$guidelist [$i] ['latest_modify'] = timetodate ( $guidelist [$i] ['latest_modify'] );
				$guidelist [$i] ['headimg'] = assemblepath ( $guidelist [$i] ['headimg'] );
				unset($guidelist [$i] ['account']); // 账号不给
				unset($guidelist [$i] ['password']); // 密码不给
			}
		}
	
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'otherguidelist' => $guidelist
				),
				'nextstart' => $newnextstart, // 下一页开始的位置
				'totalcount' => $totalcount // 总得导购数量
		);
	
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * 导购评论列表。
	 */
	public function guideComment() {
		$guide_id = I ( 'gid', '' ); // 接收导购参数
		if (empty ( $guide_id )) {
			$this->error ( "导购参数错误！" );
		}
		
		// Step1：读取该导购信息
		$guidemap = array (
				'guide_id' => $guide_id,
				'is_del' => 0
		);
		$guideinfo = M ( 'shopguide' )->where ( $guidemap )->find ();
		if (! $guideinfo) {
			$this->error ( "该导购不存在或已离职！" );
		}
		
		// Step2：读取该导购评论列表
		$nextstart = 0; // 从第一条开始请求
		$perpage = 10; // 默认每页加载10条数据
		$commentlist = $this->getGuideCommentByPage ( $guide_id, $nextstart, $perpage, true ); // 加载导购评论
		
		$this->commentlist = jsencode ( $commentlist ); // json压缩数据
		$this->ginfo = $guideinfo; // 推送前台导购信息
		$this->display ();
	}
	
	/**
	 * ajax请求查询导购服务评价列表。
	 */
	public function queryGuideCommentList() {
		if (! IS_POST) $this->error ( "Sorry, page not exist!" ); // 防止恶意打开
		
		$guide_id = I ( 'gid' ); // 导购编号
		$nextstart = I ( 'nextstart', 0 ); // 下一页开始的下标，默认为0
		$perpage = 10; // 每页10条
		
		$requestresult = $this->getGuideCommentByPage ( $guide_id, $nextstart, $perpage ); // 分页读取导购服务评价信息
		$this->ajaxReturn ( $requestresult );
	}
	
	/**
	 * 分页读取导购服务评价信息函数。
	 * @param string $guide_id 要查询评价的导购编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	private function getGuideCommentByPage($guide_id = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$gcommenttable = M ( 'guide_comment_view' ); 				// 实例化表结构或视图结构
		$orderby = "comment_time desc"; 							// 定义要排序的方式（每个表都不一样）
		$gcommentlist = array (); 									// 本次请求的数据
		
		// Step1：定义查询条件并计算总数量
		$querymap = array (
				'guide_id' => $guide_id, 							// 当前导购编号
				'is_del' => 0 										// 没有被删除的
		);
		
		$totalcount = $gcommenttable->where ( $querymap )->count (); // 计算总数量
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$gcommentlist = $gcommenttable->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询导购服务评价信息
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$gcommentlist [$i] ['comment_time'] = timetodate ( $gcommentlist [$i] ['comment_time'] );
				$gcommentlist [$i] ['headimgurl'] = assemblepath ( $gcommentlist [$i] ['headimgurl'] );
			}
		}
		
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'gcommentlist' => $gcommentlist
				),
				'nextstart' => $newnextstart, // 下一页评论开始的地方
				'totalcount' => $totalcount // 总的评论数量
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