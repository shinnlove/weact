<?php
/**
 * 订单管理控制器。
 * @author 王健。
 * @modify author 赵臣升。
 * @secondmodify author 胡福玲。
 */
class OrderManageAction extends PCViewLoginAction {
	
	/**
	 * 订单一览视图。
	 */
	public function orderView() {
		//推送当前商家下邮费模板主信息
		$pmmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		$postagemode = M('postagemode')->where($pmmap)->select();
	
		$this->postagemode = $postagemode;
		$this->display ();
	}
	
	/**
	 * 订单状态流水
	 */
	public function orderStatus() {
		$oid=I('oid');
		$this->oid = $oid;
		$this->display();
	}
	
	/**
	 * 快递信息查看与编辑处理
	 */
	public function deliverProduct(){
		$expmap = array(
				'order_id' => I('oid'),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'visual_number' => I('vid'),
				'is_del' => 0
		);
		$expinfo = M('order_express')->where($expmap)->find();
		
		if($expinfo['deliver_time'] == '-1' || $expinfo['deliver_time'] == ''){
			$expinfo['deliver_time'] = '';
		}else {
			$expinfo['deliver_time'] = timetodate($expinfo['deliver_time']);
		}
		$this->readonly = I('readonly');
		$this->readonlyfee = I('readonlyfee');
		$this->expinfo = $expinfo;
		$this->display();
	}
	
	/**
	 * 查看订单详情信息处理函数
	 */
	public function orderDetail(){
		$orderdetailmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'order_id' => I ( 'orderid' ), // 接收订单编号
				'is_del' => 0
		);
		
		$orderdetailinfo = M ( 'order_cinfo' )->where ( $orderdetailmap )->find (); // 查询订单详情信息
		$expinfo = M ( 'order_express' )->where ( $orderdetailmap )->find (); // 查询订单关联快递信息
		$orderstatus = "未知状态";
		
		if ($orderdetailinfo) {
			$orderdetailinfo ['order_time'] = timetodate ( $orderdetailinfo ['order_time'] ); // 时间初始化格式转换：时间戳->日期格式
			$orderdetailinfo ['payindeed'] = $orderdetailinfo ['pay_indeed']; // 默认设置实付款金额等于应付金额
			
			// 提取订单状态
			$statusflag = $orderdetailinfo ['status_flag'];
			$normalstatus = $orderdetailinfo ['normal_status'];
			$refundstatus = $orderdetailinfo ['refund_status'];
			
			$orderstatus = $this->formatOrderStatus ( $statusflag, $normalstatus, $refundstatus ); // 获取订单状态
			
			// 支付方式的初始化格式转换
			$paymethod = $orderdetailinfo ['pay_method'];
			if ($normalstatus >= 1) {
				if ($paymethod == 1) {
					$orderdetailinfo ['pay_method'] = '现金收讫';
				} else if ($paymethod == 2) {
					$orderdetailinfo ['pay_method'] = '微信支付';
				} else if ($paymethod == 3) {
					$orderdetailinfo ['pay_method'] = '刷卡支付';
				} else {
					$orderdetailinfo ['pay_method'] = '未选择';
				}
			} else {
				$orderdetailinfo ['pay_method'] = '/'; // 未付款时，设置支付方式为'/'
			}
		}
		
		$this->orderstatus = $orderstatus;
		$this->detailinfo = $orderdetailinfo;
		$this->expinfo = $expinfo;
		// 区别账单通知信息中的订单详情显示
		$showtype = $_REQUEST ['showtype'];
		$this->showtype = $showtype;
		$this->display ();
	}
	
	/**
	 * 导出订单数据处理函数
	 * @author hufuling
	 */
	public function exportOrder(){
		//查询数据
		$exmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'e_mark_del' => 0,
				'is_del' => 0
		);
		$field ="order_time, visual_number, customer_id, customer_name, customer_tel, total_price, pay_method, pay_indeed, subbranch_id, receive_status, status_flag, logistics_method, express_number, receive_person, receive_tel, receive_address, remark, normal_status, refund_status" ;
		$orderlist = M( 'order_cinfo' )->where( $exmap )->field( $field )->order('order_time desc')->select();
		
		//格式化查询出的数据
		for ($i =0; $i < count ( $orderlist ); $i ++) {
			
			// 提取订单状态
			$statusflag = $orderlist [$i] ['status_flag'];
			$normalstatus = $orderlist [$i] ['normal_status'];
			$refundstatus = $orderlist [$i] ['refund_status'];
			
			// 支付方式格式化:因为is_payed在下面处理中会改变值，所有必须放在所有处理的最前面。
			if ($normalstatus >= 1) {
				if ($orderlist[$i]['pay_method'] == '0'){
					$orderlist[$i]['pay_method'] = '未选择';
				}else if ($orderlist[$i]['pay_method'] == '1'){
					$orderlist[$i]['pay_method'] = '现金支付';
				}else if ($orderlist[$i]['pay_method'] == '2'){
					$orderlist[$i]['pay_method'] = '微信支付';
				}else if ($orderlist[$i]['pay_method'] == '3'){
					$orderlist[$i]['pay_method'] = '刷卡支付';
				}else $orderlist[$i]['pay_method'] = '不明方式';
			} else {
				$orderlist[$i]['pay_method'] = '/';
			}
			
			$orderstatus = $this->formatOrderStatus ( $statusflag, $normalstatus, $refundstatus ); // 获取订单状态
			
			$orderlist [$i] ['status_flag'] = $orderstatus;
			
			// 标注商家接收方式
			if ($orderlist[$i]['receive_status'] == 0){
				$orderlist[$i]['receive_status'] = '未接收';
			}else {
				$orderlist[$i]['receive_status'] = '已接收';
			}
			
			// 标注快递方式
			if ($orderlist [$i] ['logistics_method'] == 0){
				$orderlist [$i] ['logistics_method'] = '快递';
			} else { 
				$orderlist [$i] ['logistics_method'] = '到店自提';
			}
			
			//处理订单所属分店
			if ($orderlist [$i] ['subbranch_id'] != null && $orderlist [$i] ['subbranch_id'] != '-1' && $orderlist [$i] ['subbranch_id'] != ''){
				$submap = array(
						'subbranch_id' => $orderlist [$i] ['subbranch_id'],
						'e_id' => $exmap['e_id'],
						'is_del' => 0
				);
				$subinfo = M('subbranch')->where($submap)->find();
				if($subinfo){
					$orderlist [$i] ['subbranch_id'] = $subinfo['subbranch_name'];
				}else {
					$orderlist [$i] ['subbranch_id'] = '云总店';
				}
			}else {
				$orderlist [$i] ['subbranch_id'] = '云总店';
			}
	
			//时间格式化
			$orderlist[$i]['order_time'] = timetodate( $orderlist[$i]['order_time'] );
			
			unset($orderlist [$i] ['normal_status'], $orderlist [$i] ['refund_status']);
		}
	
		// 准备标题准备打印
		$title = array (
				0 => '下单时间',
				1 => '订单编号',
				2 => '顾客编号',
				3 => '下单人名',
				4 => '联系电话',
				5 => '总价（元）',
				6 => '支付方式',
				7 => '实付款（元）',
				8 => '所属分店',
				9 => '店铺受理',
				10 => '订单状态',
				11 => '收货方式',
				12 => '快递单号',
				13 => '收货人',
				14 => '收货人电话',
				15 => '收货人地址',
				16 => '备注'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $orderlist, '订单对账详情'.time(), '所有订单信息一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
	/**
	 * 
	 * @param number $statusflag
	 * @param number $normalstatus
	 * @param number $refundstatus
	 * @return string $orderstatus 
	 */
	private function formatOrderStatus($statusflag = 0, $normalstatus = 0, $refundstatus = 0) {
		$orderstatus = "未知状态";
		if ($statusflag == 0) {
			if ($normalstatus == -2) {
				$orderstatus = '商家发货超时，交易取消';
			} else if ($normalstatus == -1) {
				$orderstatus = '顾客付款不及时，交易取消';
			} else if ($normalstatus == 0) {
				$orderstatus = '待付款';
			} else if ($normalstatus == 1) {
				$orderstatus = '等待卖家发货';
			} else if ($normalstatus == 2) {
				$orderstatus = '等待买家收货';
			} else if ($normalstatus == 3) {
				$orderstatus = '交易成功，待评价';
			} else if ($normalstatus == 4) {
				$orderstatus = '交易成功，已评价';
			}
		} else if ($statusflag == 1) {
			if ($refundstatus == 1) {
				$orderstatus = '退款申请待处理';
			} else if ($refundstatus == 2) {
				$orderstatus = '同意退款处理中';
			} else if ($refundstatus == 3) {
				$orderstatus = '拒绝退款协商中';
			} else if ($refundstatus == 4) {
				$orderstatus = '退款成功，交易关闭';
			} else if ($refundstatus == 5) {
				$orderstatus = '交易关闭';
			}
		}
		return $orderstatus;
	}
	
	/**
	 * =======================以下是邮费设定页面========================
	 */
	
	public function postageModeView(){
		$this->display();
	}
	
	/**
	 * 新增模板
	 */
	public function addPostageMode(){
		$this->mid = md5(uniqid(rand(),true));
		$this->display();
	}
	
	/**
	 * 编辑模板
	 */
	public function editPostageMode() {
		$editmap = array (
				'mode_id' => $_REQUEST ['mode_id'],
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$editinfo = M ( 'postagemode' )->where ( $editmap )->find ();
	
		$editdetailmap = array (
				'mode_id' => $editmap ['mode_id'],
				'is_del' => 0
		);
		$detailinfo = M ( 'postagedetail' )->where ( $editdetailmap )->select ();
	
		// 对不能勾选地区进行预处理
		$disabledregion = array (); 													// 存放不能勾选的地区数组
		$detailcount = count ( $detailinfo ); 											// 统计已经添加过的邮费地区数量
		for($i = 0; $i < $detailcount; $i ++) {
			if (! empty ($detailinfo [$i] ['designated_area'])) {
				$tempregions = explode ( ",", $detailinfo [$i] ['designated_area'] ); 	// 直接取出当前地区分割进入临时地区列表
				foreach ($tempregions as $singleregion) {
					array_push ( $disabledregion, $singleregion ); 						// 将临时地区数组中的地区一个个列入$disabledregion不能勾选地区数组中
				}
			}
		}
	
		// Step1：推送编辑的主信息
		$this->editinfo = $editinfo;
	
		// Step2：将二维数组以json方式打到页面上用js模板渲染
		$postageinfo ['detaillist'] = $detailinfo; 										// 将地区详情信息包装到detailist字段中
		$ajaxinfo = json_encode ( $postageinfo ); 										// json_encode 成ajaxinfo
		$this->detaillist = str_replace ( '"', '\\"', $ajaxinfo ); 						// 打到页面上对双引号转义下，防止出错
	
		// Step3：推送不能勾选地区数组到前台
		$this->disabledregion = implode ( ",", $disabledregion );
	
		$this->display();
	}

	
}
?>