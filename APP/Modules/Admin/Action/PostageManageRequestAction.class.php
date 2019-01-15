<?php
/**
 * 邮费设定管理相关ajax请求控制器
 * @author hufuling
 *
 */
class PostageManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyUI的post请求，初始化读取邮费模板数据
	 */
	public function read(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'postageModeView' ) );
			
		$pmmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],                          					// 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'mode_sort';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$pmtbl = M ( 'postagemode' );
		$pminfolist = array ();
		$pmtotal = $pmtbl->where ( $pmmap )->count ();                                     				// 计算当前商家下不被删除餐饮商品的总数
		if($pmtotal){
			$pminfolist = $pmtbl->where ( $pmmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i<count($pminfolist); $i++){
				$pminfolist [$i] ['add_time'] = timetodate ( $pminfolist [$i] ['add_time'] ); 			// 添加商品时间转为可视化
			}
		}
		if ($pminfolist) {
			$json = '{"total":' . $pmtotal . ',"rows":' . json_encode ( $pminfolist ) . '}'; 			// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                            					// 否则就输出空数组
		}
		echo $json;
	}
	
	/**
	 * 单击模板详情按钮展开模板详情。
	 */
	public function getModeDetail() {
		$detailmap = array (
				'mode_id' => I ( 'mid' ), // 接收订单编号
				'is_del' => 0
		);
		$detailinfo = M ( 'postagedetail' )->where ( $detailmap )->select ();
	
		if ($detailinfo) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'detaillist' => $detailinfo
					)
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => '没有查到模板详情信息。',
					'data' => array ()
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 添加邮费模板请求。
	 */
	public function addPostageModeCfm(){
		// 新增邮费模板
		$postagedata = array(
				'mode_id' => I('mid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'mode_name' => I('mna'),
				'exp_company' => I('cuc'),
				'mode_sort' => I('mso'),
				'first_weight' => I('fwe'),
				'added_weight' => I('awe'),
				'status' => I('status'),
				'remark' => I('mre'),
				'add_time' => time()
		);
		$postageresult = M('postagemode')->add($postagedata);
	
		// 指定地区数据输入（接收）
		$regiondata = array (
				'districts' => $_REQUEST["vallist"], 				// 地区列表
				'firstfee' => $_REQUEST["deflist"], 				// 首重费用
				'addfee' => $_REQUEST["inclist"] 					// 续重费用
		);
		// 切分数据
		$regionlist = explode ( "|", $regiondata ['districts'] ); 	// 切割地区数组
		$firstfeelist = explode ( "|", $regiondata ['firstfee'] ); 	// 切割首重费用数组
		$addfeelist = explode ( "|", $regiondata ['addfee'] ); 		// 切割续重费用数组
	
		$regionlistnum = count ( $regionlist ); 					// 计算总数量
		$detaillist = array();
		for($i = 0; $i < $regionlistnum; $i ++) {
			$detaillist [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ),// 记录主键
					'mode_id' => $postagedata['mode_id'],
					'designated_area' => $regionlist [$i], 			// 该记录的地区
					'first_fee' => $firstfeelist [$i], 				// 该地区的首重费用
					'added_fee' => $addfeelist [$i], 				// 该地区的续重费用
			);
		}
		$detailresult = M('postagedetail')->addAll($detaillist);
	
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($postageresult && $detailresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '添加成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 删除模板处理函数
	 */
	public function delPostageMode(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$delidlist = I ( 'rowdata' ); 					// 接收要删除的邮费模板id列表
		$pmtbl = M ( 'postagemode' ); 					// 邮费模板表
		$pdtbl = M ( 'postagedetail' ); 				// 邮费模板详情表
	
		$delpmap = array(
				'mode_id' => array('in', $delidlist),
				'is_del' => 0
		);
	
		$delpdresult = $pdtbl->where($delpmap)->delete();
		$delpmap['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
		$delpmresult = $pmtbl->where($delpmap)->setField('is_del', 1);
	
		$ajaxresult = array ();
		if($delpdresult && $delpmresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '信息删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 指定地区的删除处理
	 */
	public function deleteDetail(){
		$delmap = array(
				'detail_id' => I('delid'),
				'is_del' => 0
		);
		$delresult = M('postagedetail')->where($delmap)->delete();
	
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($delresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '删除失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 按条件搜索模板
	 */
	public function searchMode(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'postageModeView' ) );
		 
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'mode_sort';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
		 
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 															// 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 										// 必须带上e_id
				'is_del' => 0
		);
		 
		if($condition == 'mode_name'){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if ($condition == 'exp_company') {
			if($content != -1){
				$searchmap [$condition] = $content;
			}
		}
		 
		$pmtbl = M ( 'postagemode' );
		$pminfolist = array ();
		$pmtotal = $pmtbl->where ( $searchmap )->count ();                                     				// 计算当前商家下不被删除餐饮商品的总数
		if($pmtotal){
			$pminfolist = $pmtbl->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i<count($pminfolist); $i++){
				$pminfolist [$i] ['add_time'] = timetodate ( $pminfolist [$i] ['add_time'] ); 			// 添加商品时间转为可视化
			}
		}
		if ($pminfolist) {
			$json = '{"total":' . $pmtotal . ',"rows":' . json_encode ( $pminfolist ) . '}'; 			// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                            					// 否则就输出空数组
		}
		echo $json;
	}
	
	/**
	 * 切换模板开闭状态
	 */
	public function changeStatus() {
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' );				// 接收参数
		$type = I ( 'type' );					// 接收操作类型
	
		// 定义要操作的范围
		$changemap = array (
				'mode_id' => array( 'in', explode ( ',', $rowdata ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$handleresult = false;					// 默认没成功
		if ($type == "on") {
			$onSave = array (
					'status' => 1,				// 模板开启状态
			);
			$handleresult = M ( 'postagemode' )->where ( $changemap )->save ( $onSave );
		} else if ($type == "off") {
			$offSave = array (
					'status' => 0, 				// 模板关闭状态
			);
			$handleresult = M ( 'postagemode' )->where ( $changemap )->save ( $offSave );
		}
	
		// 返回给前台结果
		$ajaxresult = array ();
		if ($handleresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "操作失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
}
?>