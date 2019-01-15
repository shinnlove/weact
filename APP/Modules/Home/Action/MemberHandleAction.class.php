<?php
/*
 * 本控制器开放权限：登录会员。
 * 作用：处理会员登录后的操作和相关页面展示。
 * 
 * 本控制器函数一览：
 * 1、customerCenter()，显示会员中心页面；
 * 1-1、refreshScore()，刷新会员积分函数，配合显示会员中心页面用；
 * 2、customerInfo()，显示会员信息页面；
 * 3、customerScoreRecord()，显示会员积分详情页面；
 * 4、customerSign()，用户签到显示页面；
 * 5、customerAfterSign()，用户已经签到显示页面；
 * 6、customerSignSucceed()，用户签到成功显示页面；
 * 7、customerInvitation()，用户邀请码显示页面；
 * 8、customerCollection()，用户收藏夹显示页面；
 * 9、customerScoreShop()，用户积分商城显示页面；
 * 10、customerSafeCenter()，用户安全中心显示页面；
 * 11、customerPresent(),我的奖品页面；
 * 12、customerBinding()，客户绑定会员卡页面；
 * 13、customerCoupon()，我的优惠券页面。
 * 
 * customerInfoModify()，会员信息修改处理函数；
 * customerSignHandle()，会员签到处理函数（服务器端还验证了一次，怕用户回退按钮）；
 * customerCollectionDelete()；会员收藏夹页面删除收藏产品处理函数；
 * customerPwdModify()；会员安全中心的密码修改处理函数
 * bindMemberCard()；绑定会员卡处理函数
 * addCollection()；浏览购物的时候添加收藏夹按钮，该Action函数是否可以考虑移到ProductView控制器下?
 * 
 * */
class MemberHandleAction extends MobileLoginAction {
	/*----------------------展示页面、读取信息的Action----------------------------*/
	
	/**
	 * 初始化客户中心需要customer信息，从session中读取。
	 */
	public function _initialize() {
		parent::_initialize (); // 初始化MobileLogin控制器的登录控制方法（重要不能丢）
		$this->customer = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']]; // 推送当前顾客信息
	}
	
	/**
	 * 会员中心视图。
	 */
	public function customerCenter() {
		$map = array(
			'e_id' => $this->einfo ['e_id'],
			'is_del' => 0
		);
		$minfo = M ( 'memberinfo' )->where( $map )->find();								//查询商家的会员待遇信息
		$minfo ['membercard_path'] = assemblepath( $minfo ['membercard_path'] );		//组装路径
		$this->memberinfo = $minfo;
		$shopmap = array(
			'e_id' => $map['e_id'],
			'subbranch_id' => $this->customer ['subordinate_shop'],
			'is_del' => 0
		);
		$setable = M ( 'subbranch' );
		$seinfo = $setable->where( $shopmap )->find();
		if($seinfo){
			$this->seinfo = $seinfo ['subbranch_name'];
		}else{
			$this->seinfo = '暂无分店信息';
		}
		$sv = A ( 'Service/ServiceVersion' );
		$serviceinfo = $sv->mobileServiceNav ( $this->einfo ['e_id'] );
		$this->serviceinfo = $serviceinfo;
		$this->loginstyle = $this->einfo ['login_style'];
		$this->display();
	}
	
	/**
	 * 1-1、显示会员中心的分数，不管是第一次进入页面，还是后退按钮，都会查询数据库进行分数控制。
	 */
	public function refreshScore(){													//从session中取出当前登录的用户
		$freshScore = array (
			'e_id' => $this->einfo ['e_id'],
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],			//取出当前客户的编号
			'is_del' => 0
		);
		//$result = M('customerscore')->where($freshScore)->sum('change_amount');
		$result = M('customerinfo') -> where($freshScore) ->getField('total_score');
		if(! $result) $result = 0;												//如果用户没有积分，则显示0分，而不是空值，特别注意：在积分页面也是
		$this->ajaxReturn ( array ( 'status' => $result ), 'json' );
	}
	
	/**
	 * 2、显示会员修改资料视图。
	 */
	public function customerInfo() {
		$this->display();
	}

	/**
	 * 3、会员积分详情视图。
	 */
	public function customerScoreRecord(){
		$scoreRecord = array (
			'e_id' => $this->einfo ['e_id'],											//限制顾客的e_id商家条件
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],				//取得session中currentcustomer变量的customer_id值
			'is_del' => 0
		);
		$result = M('customerscore')->where($scoreRecord)->sum('change_amount');		//查出这个顾客的积分信息
		if(! $result) $result = 0;														//细节处理：如果用户没有积分，则显示0分，而不是空值，特别注意：在积分页面也是
		$this->sumScore = $result;														//推送总积分到前台
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/
		//step1：导入控件，并确定一共几条数据、每页展示几条数据
		import('ORG.Util.Page');														//导入分页控件
		$count = M ('customerscore')->where($scoreRecord)->count();						//计算出这个顾客在这家店铺的积分
		$page = new Page ( $count, 10);													//每页显示10条积分记录
		//step2：查询要显示的数据，并传送一些辅助数据到前台配合查询
		$scorelist = M ('customerscore')->where($scoreRecord)->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();	//依据分页状态显示
		for($i=0; $i<count($scorelist); $i++) {
			$scorelist [$i] ['change_time'] = timetodate( $scorelist [$i] ['change_time'] );	//处理整型时间可视化
		}
		$this->scorelist = $scorelist;
		$this->scorecount = count($scorelist);											//计算需要循环的次数给前台for循环用
		//step3：设置分页控件的主题与推送分页控件到前台
		$page->setConfig('theme','%upPage% %nowPage%/%totalPage% 页 %downPage%');			//设置分页主题
		$this->page = $page->show();													//page控件作为变量向前台推送，注意和前台代码的配合
		/*----------------↑以上为PHP导入分页控件代码，注意和前台的配合↑---------------*/
		$this->display();
	}
	
	/**
	 * 4、会员签到视图。
	 */
	public function customerSign(){
		$sql = 'change_time > ' . todaystart() . ' AND change_time < ' . todayend() . ' AND customer_id = ' . $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] . ' AND e_id = ' . $this->einfo ['e_id'] . ' AND is_del = 0';	//拼接SQL语句
		$customerscoreCount = M ( 'customerscore' )->where( $sql )->count ();		//查询是否签到
		if($customerscoreCount){				//如果已经签到，则直接跳转到签到后的页面
			$this->redirect( 'Home/MemberHandle/customerAfterSign?e_id='.$this->einfo ['e_id'] );
		}else {
			$this->display();
		}
	}
	
	/**
	 * 5、会员已签到视图。
	 */
	public function customerAfterSign(){
		$search = array (
			'e_id' => $this->einfo ['e_id'],											//限制顾客的e_id商家条件
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],				//取得session中currentcustomer变量的customer_id值
			'is_del' => 0
		);
		$signresult = M('customerscore')->where($search)->order('change_time desc')->limit(1)->select();
		$signresult [0] ['change_time'] = timetodate ( $signresult [0] ['change_time'] );//二维数组，注意[0]下标
		$this->cur = $signresult;
		$this->display();
	}
	
	/**
	 * 6、会员签到成功视图。
	 */
	public function customerSignSucceed(){
		$this->display();
	}
	
	/**
	 * 7、显示用户邀请码视图。
	 */
	public function customerInvitation(){
		$usermap = array (
			'e_id' => $this->einfo ['e_id'],										//限制顾客的e_id商家条件
			'inviter' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],				//搜索被邀请顾客inviter编号是当前顾客编号
			'is_del' => 0
		);
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/
		//step1：导入控件，并确定一共几条数据、每页展示几条数据
		import('ORG.Util.Page');													//导入分页控件
		$count = M ( 'customerinfo' ) ->where($usermap)->count();					//计算出这个顾客在这家店铺的邀请人数
		$page = new Page ( $count, 10);												//每页显示10条积分记录
		//step2：查询要显示的数据，并传送一些辅助数据到前台配合查询
		$follower = M ( 'customerinfo' )->where($usermap)->order('register_time desc')->limit($page->firstRow.','.$page->listRows)->select();	//根据分页找出粉丝
		$this->followlist = $follower;
		$this->followcount = count ( $follower );									//计算需要循环的次数给前台for循环用
		//step3：设置分页控件的主题与推送分页控件到前台
		$page->setConfig('theme','%upPage% %nowPage%/%totalPage% 页 %downPage%');		//设置分页主题
		$this->page = $page->show();												//page控件作为变量向前台推送，注意和前台代码的配合
		/*----------------↑以上为PHP导入分页控件代码，注意和前台的配合↑---------------*/
		//推送分享信息
		$name = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_name'];	//尝试获取客户名字
		if (! empty($name)) {
			$this->sharetitle = '我是' . $name . '，邀请你一起成为' . $this->einfo ['e_name'] . '的会员!';
		}else {
			$this->sharetitle = '我邀请你一起成为' . $this->einfo ['e_name'] . '的会员!';
		}
		$this->sharedesc = '快来注册' . $this->einfo ['e_name'] . '的会员吧，我还能获得积分哟!';
		$this->shareimg = $this->einfo ['e_square_logo']; 							// 分享图片的路径（父类中已经组装过绝对路径）
		$this->display();
	}
	
	/**
	 * 8、收藏夹视图。
	 */
	public function customerCollection(){
		$sql = 't_collection.product_id = t_product.product_id AND t_product.product_id = t_productimage.product_id AND t_collection.e_id = \''. $this->einfo ['e_id'] .'\' AND t_collection.customer_id = \'' . $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] . '\' AND t_collection.is_del = 0';
		$model = new Model();															//创建视图查询器
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/
		//step1：导入控件，并确定一共几条数据、每页展示几条数据
		import('ORG.Util.Page');														//导入分页控件
		$count = $model->table('t_collection, t_product, t_productimage')
		->where($sql)
		->field('t_product.product_id, t_product.product_name, t_product.e_id, t_product.nav_id, t_product.original_price, t_product.current_price, t_productimage.micro_path')
		->count();																		//计算顾客总收藏数
		$page = new Page ( $count, 10);													//每页显示10条积分记录
		//step2：查询要显示的数据，并传送一些辅助数据到前台配合查询
		$collectionlist = $model->table('t_collection, t_product, t_productimage')
		->where($sql)
		->field('t_product.product_id, t_product.product_name, t_product.e_id, t_product.nav_id,t_product.is_del, t_product.original_price, t_product.current_price, t_productimage.micro_path')
		->order('record_time desc')
		->limit($page->firstRow.','.$page->listRows)
		->select();
		$this->collectionlist = $collectionlist;
		$this->count = count($this->collectionlist);									//计算需要循环的次数给前台for循环用
		//step3：设置分页控件的主题与推送分页控件到前台
		$page->setConfig('theme','%upPage% %nowPage%/%totalPage% 页 %downPage%');			//设置分页主题
		$this->page = $page->show();
		/*----------------↑以上为PHP导入分页控件代码，注意和前台的配合↑---------------*/
		$this->display();
	}
	
	/**
	 * 9、用户积分换礼视图。
	 */
	
	/**
	 * 分页读取某customer_id(顾客ID)在积分商城不同会员等级对应的不同页卡中获取的商品列表
	 * 分为全部商品\一级会员商品\二级会员商品\三级会员商品
	 * 数据按换购积分数量进行降序排序，分页读取
	 */
	public function customerScoreShop(){
		// 接收参数并查数据库，分页读取数据
		$e_id = $this->einfo ['e_id'];
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		//查找 商家设定的会员等级名称
		$memberlevelmap = array(
				'e_id' => $e_id,
				'is_del' => 0
		);
		$levelname = M('memberlevel') -> where($memberlevelmap) ->order('level asc')->field('level_name,level')->select();
		$this->memberlevelname = $levelname;
		if(!I('querytype')){
			$query_type = $levelname[0]['level'];		//默认取数据库里面的第1条记录，即下标为0，可能是1级，也可能是2级也可能是3级
		}else{
			$query_type = I('querytype');
		}
		$startindex = 0;
		$count = 10;		//默认一次最多加载10条记录
		$firstInitData = true;
		
		$jsondata = $this->proExchangeListLimit( $e_id, $customer_id, $query_type, $startindex, $count, $firstInitData);
		$finaljson = json_encode ( $jsondata );
		
		$this->proExchangeListjson = $finaljson;
		$this->eid= $e_id;
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 推送微信用户编号
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		//p($jsondata);die;
		$this->display ();
	}
	
	/**
	 * 分页读取某商家$e_id下某$customer_id(顾客ID)在积分商城不同会员等级对应的不同页卡中获取的商品列表
	 * 查询类别（默认:全部可兑换商品0、一级会员可兑商品1、二级会员可兑商品2、三级会员可兑商品3）
	 * 因为这是ajax处理，所以post的时候，带上了要查询的页数nextStart（从第几页开始，默认查询一页）
	 */
	public function requestProExchangeList() {
		// 接收参数读取订单
		$e_id = $this->einfo ['e_id'];
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		//获取商家设定的等级标签名称
		$memberlevelmap = array(
				'e_id' => $e_id,
				'is_del' => 0
		);
		$levelname = M('memberlevel') -> where($memberlevelmap) ->order('level asc')->field('level_name,level')->select();
		$this->memberlevelname = $levelname;
		if(!I('querytype')){
			$query_type = $levelname[0]['level'];		//默认取数据库里面的第0条记录
		}else{
			$query_type = I('querytype');
		}
		$startindex = $_REQUEST ['nextstart'];
		$count = 10;
		$firstInitData = false;
	
		$ajaxinfo = $this->proExchangeListLimit( $e_id, $customer_id, $query_type, $startindex, $count, $firstInitData);
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 分页读取某商家$e_id下某$customer_id(顾客ID)在积分商城不同会员等级对应的不同页卡中所获取的商品列表数据（被显示页面和查询订单ajax处理函数调用
	 *
	 * @param string $e_id
	 *        	商家ID
	 * @param string $customer_id
	 *        	顾客ID(需不需要增加一个变量，显示该顾客有没有资格或者积分兑换该商品?)
	 * @param int $query_type
	 *        	查询类别（默认:全部可兑换商品0、一级会员可兑商品1、二级会员可兑商品2、三级会员可兑商品3）
	 * @param number $startindex
	 *        	从第几条开始
	 * @param number $count
	 *        	想要读取几条
	 * @param bool $firstInitData
	 *        	是否是第一次读取
	 * @return array $ajaxresult
	 * 			返回的ajax请求
	 */
	public function proExchangeListLimit($e_id = '', $customer_id = '', $query_type = 1, $startindex = 0, $count = 10, $firstInitData = FALSE) {
		if(!isset($startindex))$startindex=0;		//这里防止$startindex=null的情况
		$scoreprotable = M ( "score_product_image" );
	
		/** 建立查询条件
		 * 1)确定删选条件:e_id，member_level,is_use,is_del
		 * 2)为了减少数据库的查询时长，这里增加一个group字段(rule_id),为了不显示多张图片的重复
		 * 3)根据兑换所需积分数量进行降序排列
		 */
		$scorepromap = array (
				'e_id' => $e_id,
				'is_use'=>	1,	//当前正在被使用
				'is_del' => 0,
		);
		// 下面增加member_level的校验(几级会员可以进行兑换)
		if( $query_type == 1) {
			$scorepromap['member_level'] = 1;
		}
		else if( $query_type == 2) {
			$scorepromap['member_level'] = 2;
		}
		else if( $query_type == 3) {
			$scorepromap['member_level'] = 3;
		}

		$prolist = $scoreprotable->where($scorepromap)->group('rule_id')->order('score_amount desc')->limit( $startindex, $count)->select();
		//p($prolist);die;
		$realgetnum = 0; // 确定本次查询返回的数据量
		if( $prolist) {
			$realgetnum = count($prolist);
			for( $i = 0; $i < $realgetnum; $i++) {	// 数据转换操作
				$prolist [$i] ['macro_path'] = assemblepath($prolist [$i] ['macro_path']);
				$prolist [$i] ['micro_path'] = assemblepath($prolist [$i] ['micro_path']);
				unset($prolist [$i] ['html_description']);
				// $orderlist [$i] ['order_time'] = timetodate ( $orderlist [$i] ['order_time'] ); // 对$orderlist信息格式化时间
			}
		}else{
			$prolist = array();
		}

		$ajaxresult = array (
			'data' => array (
					'prolist' => $prolist, // 订单列表
					),
					'nextstart' => ($startindex + $realgetnum)
		);
		// 如果是加载页面
		if (! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
			
	/**
	 * 转自OrderAction
	 * 分页读取订单主单信息函数。
	 * 已按照最新订单流水状态进行过更改，2015/08/18 15:41:36.
	 *
	 * 判断查询订单条件：0为全部订单，1为待付款，2为待收货，3为待评价，4为已关闭。
	 * @param string $eid 商家编号
	 * @param string $customer_id 顾客编号
	 * @param number $nextstart 本次要请求的数据记录起始下标位置
	 * @param number $perpage 本次要请求的数据记录每页大小
	 * @param number $queryordertype 要查询的订单类型
	 * @param boolean $firstinit 本次是否第一次为页面初始化数据（若不是则为ajax请求）
	 * @return array $requestinfo 请求的数据信息
	 */
	public function getOrderListByPage($eid = '', $customer_id = '', $nextstart = 0, $perpage = 10, $queryordertype = 0, $firstinit = FALSE) {
		$ordermain = M ( 'ordermain' ); 			// 订单主表（用来统计数量和拉去主键的）
		$orderview = M ( 'orderinfo_view' ); 		// 实例化表结构或视图结构
		$orderby = "order_time desc"; 				// 定义要排序的方式（每个表都不一样）
		$orderlist = array (); 						// 本次请求的数据
		$orderbucket = array (); 					// 订单桶子
	
		// Step1：定义查询条件并计算总数量（不需要限制分店数量）
		$querymap = array (
				'e_id' => $eid, 					// 当前商家下
				'customer_id' => $customer_id, 		// 当前顾客
				'cus_mark_del' => 0, 				// 没有被顾客删除的（顾客删单了就无法看到了）！！！特别注意，一定要带上这个条件
				'is_del' => 0, 						// 没有被删除的
		);
	
		// 判断查询订单条件：0为全部订单，1为待付款，2为已付款|待发货，3为已发货|待收货，4为已收货|待评价，5为已评价，6为已关闭
		if ($queryordertype == 1) {
			// 待付款
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 0; 	// 已下单|待付款
		} else if ($queryordertype == 2) {
			// 已付款|待发货，但是商家没有发货的
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 1; 	// 已付款|待发货
		} else if ($queryordertype == 3) {
			// 已发货，待收货
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 2; 	// 已发货|待收货
		} else if ($queryordertype == 4) {
			$querymap ['status_flag'] = 0; 		// 正常状态
			$querymap ['normal_status'] = 3; 	// 已收货|待评价
		} else if ($queryordertype == 6) {
			// 额外（原标签卡代码，如果要用到的话，这里包含已完成和已结束）2015/08/18 20:38:26备注
			// 已关闭订单包括：交易完成|已评价、付款超时|发货超时关闭、退单退款一致|已退款4种
			$querymap ['_string'] = "(status_flag = 0 and normal_status <= 0) or (status_flag = 0 and normal_status >= 3) or (status_flag = 1 and refund_status >= 4)";
		}
	
		$totalcount = $ordermain->where ( $querymap )->count (); 				// 计算订单主表总数量
	
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 								// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
	
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
	
		if ($realgetnum < 0) $realgetnum = 0;			 						// 必要的容错处理，防止$nextstart大于总数量
	
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
	
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$ordermainlist = $ordermain->where ( $querymap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询订单主单信息
	
			$querynum = count ( $ordermainlist ); // 统计本次查询一共得到几条订单（数据库层返回的记录）
				
			$timenow = time (); 		// 取当前时间
			$orderidlist = array (); 	// 准备拼接在一起的订单主键数组
			for($i =0; $i < $querynum; $i ++) {
				// 特别备注：2015/08/25 20:52:36 增加对订单申请退款状态的判别，如果申请退款已经超过3天，则将该笔订单置为可以被顾客强制退款状态！！！
				if ($ordermainlist [$i] ['status_flag'] == 1 && $ordermainlist [$i] ['refund_status'] == 1 && ( $timenow - $ordermainlist [$i] ['refund_apply_time'] > 3 * 24 * 3600 )) {
					$ordermainlist [$i] ['compel_refund'] = 1; // 这笔订单可以被强制退款
				} else {
					$ordermainlist [$i] ['compel_refund'] = 0; // 这笔订单并不能被强制退款
				}
				// 继续原来的代码2015/08/25 20:52:36 以前
				array_push ( $orderidlist, $ordermainlist [$i] ['order_id'] );
				$ordermainlist [$i] ['detailinfo'] = array (); // 预先为子单开辟数组空间（为array_push准备）
				$orderbucket [$ordermainlist [$i] ['order_id']] = $ordermainlist [$i]; // 为每条订单开辟一个数组（主键一定不会重复，不判重了）
			}
			$orderidstring = implode ( ",", $orderidlist ); // 拼接order_id字符串
	
			$querymap ['order_id'] = array ( "in", $orderidstring ); // 增加订单主键的限制，用SQL IN查询
			$orderlist = $orderview->where ( $querymap )->order ( $orderby )->select (); // 查询出订单全信息（按子表一条记录算一条）
				
			$detailcount = count ( $orderlist ); // 计算处理子单的循环数量
			// 先进行信息的格式化，可能需要的格式化信息（转换时间或路径等）；再进行数组桶子的循环打包
			for($i = 0; $i < $detailcount; $i ++) {
				$orderlist [$i] ['order_time'] = timetodate ( $orderlist [$i] ['order_time'] );
				$orderlist [$i] ['macro_path'] = assemblepath ( $orderlist [$i] ['macro_path'] );
				$orderlist [$i] ['micro_path'] = assemblepath ( $orderlist [$i] ['micro_path'] );
	
				array_push ( $orderbucket [$orderlist [$i] ['order_id']] ['detailinfo'], $orderlist [$i] ); // 将一条子订单信息压入主订单信息中
			}
			// 因为子单是一起处理的，所以这里要对主单再单独count一次（这里用到了引用，小心出错）
			foreach($orderbucket as &$bucketvalue) {
				$bucketvalue ['detailcount'] = count ( $bucketvalue ['detailinfo'] ); // 再打上标记方便前台处理
			}
		}
	
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'orderlist' => $orderbucket
				),
				'nextstart' => $newnextstart, 	// 下一页订单开始位置
				'totalcount' => $totalcount, 	// 总的订单数量
		);
	
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
	
		return $ajaxresult; // 返回ajax信息
	}
	
	/**
	 * 10、客户安全中心视图。
	 */
	public function customerSafeCenter(){
		$user = array (
			'e_id' => I ( 'e_id' ),													//限制顾客的e_id商家条件
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],			//取得session中currentcustomer变量的customer_id值
			'is_del' => 0
		);
		$this->current = M ('customerinfo')->where($user)->find();					//为了检测到最新的客户敏感信息，再查询一次数据库。
		$this->display();
	}
	
	/**
	 * 11、会员我的奖品。
	 */
	public function customerPresent(){
		$this->display();
	}
	
	/**
	 * 12、客户绑定会员卡视图。
	 */
	public function customerBinding() {
		$this->display ();
	}
	
	/**
	 * 13、我的优惠券视图（此函数在合并Controller的时候丢失）。
	 * 原作者：梁思彬，回忆作者：赵臣升，回忆时间：2014/06/19 03:51:25。
	 * 特别注意：思彬把前台立即使用写死了，要修改。
	 */
	public function customerCoupon(){
		$sql = 'cc.coupon_id = tc.coupon_id AND cc.is_del = 0 AND tc.is_del = 0 AND cc.e_id=\'' . $this->einfo ['e_id'] . '\' AND cc.customer_id=\'' . $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] . '\'';
		$model = new Model();
		$usercoupon = $model->table('t_customercoupon cc, t_coupon tc')->where($sql)->field('*')->select();		//代码视图查询
		for($i = 0; $i < count( $usercoupon ); $i ++){
			$usercoupon [$i] ['get_time'] = timetodate ( $usercoupon [$i] ['get_time'] );	// 格式化时间
			$usercoupon [$i] ['used_time'] = timetodate ( $usercoupon [$i] ['used_time'] );
			$usercoupon [$i] ['coupon_cover'] = assemblepath ( $usercoupon [$i] ['coupon_cover'] );
			$usercoupon [$i] ['start_time'] = timetodate ( strtotime ( $usercoupon [$i] ['start_time'] ), true );
			$usercoupon [$i] ['end_time'] = timetodate ( strtotime ( $usercoupon [$i] ['end_time'] ), true );
			$usercoupon [$i] ['denomination'] = intval ( $usercoupon [$i] ['denomination'] ); // 如果是抵扣券，直接转整型，不需要小数点（总店额外加的）
		}
		$this->usercoupon = $usercoupon;
		$this->total = count( $usercoupon );
		$this->display();
	}
	
	/**
	 * 新增我的收货地址视图。
	 */
	public function addDeliveryInfo() {
		$frompage = I ( 'from' ); // 接收跳转过来页面的参数
		if (empty ( $frompage )) {
			$frompage = "customer"; // 如果没有跳转参数，默认从customercenter跳转过来
		}
		$this->frompage = $frompage; // 本页面是从哪个页面打开
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		$this->display();
	}
	
	/**
	 * 编辑我的收货地址视图。
	 */
	public function editDeliveryInfo() {
		$frompage = I ( 'from' ); // 接收跳转过来页面的参数
		if (empty ( $frompage )) {
			$frompage = "customer"; // 如果没有跳转参数，默认从customercenter跳转过来
		}
		$this->frompage = $frompage; // 本页面是从哪个页面打开
		
		$did = I ( 'did' ); // 接收要编辑的收货地址信息
		if (empty ( $did )) $this->error ( '您所编辑的收货地址不存在！' );
		$dinfomap = array (
				'deliveryinfo_id' => $did,
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$deliveryinfo = M ( 'deliveryinfo' )->where ( $dinfomap )->find ();
		$this->dinfo = $deliveryinfo;
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		
		$this->display();
	}
	
	/**
	 * 收货地址管理视图。
	 */
	public function deliveryManage() {
		$frompage = I ( 'from' ); // 接收参数
		$isscore = I('isscore');		//未积分商城而新加的参数，如果是积分商城订单页面跳转过来，则跳转到积分商城订单页面
		
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; // 取当前用户的customer_id
		
		$delimap = array (
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $customer_id,
				'is_del' => 0
		);
		$dinfolist = M ( 'deliveryinfo' )->where ( $delimap )->order ( 'add_time desc' )->select (); // 查询所有收货地址
		$this->dinfolist = $dinfolist; // 推送配送地址信息
		$this->addresscount = count ( $dinfolist ); // 推送客户地址数量
		$this->frompage = $frompage; // 从哪里跳过来
		
		// 公共类模板底部导航信息
		$navinfo = array ( 'e_id' => $this->einfo ['e_id'] ); // 导航信息
		$mobilecommon = A ( 'Home/MobileCommon' ); // 移动端控制器
		$this->pageinfo = $mobilecommon->selectCommonTpl ( $navinfo ); // 选择公共模板
		$this->isscore = $isscore;
		$this->display();
	}
	
	/*----------------------处理用户交互的函数Action----------------------------*/
	
	/**
	 * 绑定会员卡post处理函数。
	 */
	public function bindMemberCard() {
		$data = array (
			'e_id' => $this->einfo ['e_id'],										//取得顾客的e_id商家条件
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],			//取得session中currentcustomer变量的customer_id值
			'is_del' => 0
		);
		$map ['original_membercard'] = I ( 'memberCard' );
		$result = M ( 'customerinfo' )->where ( $data )->save( $map );
		if ($result) {
			$this->ajaxReturn ( array ('status' => 2 ), 'json' );
		} else {
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		}
	}

	/**
	 * 会员信息修改post处理函数。
	 */
	public function customerInfoModify(){
		if (! IS_POST) halt ( "页面不存在" );
		// 接收前台数据
		$data = array ( 'birthday' => I ( 'birthday' ) );
		
		//绑定店家测试信息是否修改
		$map = array (
			'e_id' => I('e_id'),
			'nick_name' => I ( 'nick_name' ),
			'customer_name' => I ( 'customer_name' ),
			'contact_number' => I ( 'contact_number' ),
			'sex' => I ( 'sex' ),
			'customer_address' => I ( 'customer_address' )
		);
		
		$map1 = $map;		//这里引入$map1的作用是为了单独处理birthday，$map1和$map的区别是后者还包含birthday字段
		//特别注意：这里处理$data ['birthday']，因为如果页面没填birthday字段的话，在存入数据库的时候会存入0000-00-00 00:00:00，这个值是有问题的********
		if(($data ['birthday']=="0000-00-00 00:00:00"||$data ['birthday']==null||$data ['birthday']=="")){
			$map['birthday']=null;
		}else{
			$map['birthday'] = $data ['birthday'];
		}
		$mymap['customer_id'] =  $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'];	//提取Session中用户
		$mymap['e_id'] =  $map ['e_id'];
		$duplicate = M ( 'customerinfo' )->where($map1)->find();				//判断是否没有更改，注意这里是用$map1，不含birthday字段
		//这里特别注意要将birthday字段单独处理，否则会导致通过$map去数据库中查找失败
		if($duplicate && $map['birthday'] == $duplicate['birthday']){
			$this->ajaxReturn(array( 'status' => 2 ),'json');					//没有更改任何信息
		}else{
			$result = M ( 'customerinfo' )->where($mymap)->save($map);
			if ($result) {
				$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );			//修改成功
			} else {
				$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );			//修改失败(数据库写入失败)
			}
		}
	}

	/**
	 * 处理用户签到post提交函数。
	 */
	public function customerSignHandle(){
		//再次判断会员是否签过到（以免页面后退或者过期）
		$sql = 'change_time > ' . todaystart() . ' AND change_time < ' . todayend() . ' AND customer_id = ' . $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] . ' AND e_id = ' . $this->einfo ['e_id'] . ' AND is_del = 0';	//拼接SQL语句
		$customerscore = M( 'customerscore' );	//实例化表
		$customerscoreCount = $customerscore->where($sql)->count ();		//查询是否签到
		if($customerscoreCount){				//如果已经签到，则直接跳转到签到后的页面
			$this->redirect('Home/MemberHandle/customerAfterSign?e_id=' . $this->einfo ['e_id']);//带上e_id跳转
		}else {
			//进行签到步骤
			$signmap = array(
				'score_id' => md5 (uniqid (rand(), true)),							//随机主键签到编号
				'e_id' => $this->einfo ['e_id'],									//取当前商家编号
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],		//取当前签到客户编号
				'change_time' => time(),											//积分变动时间：当前时间，取time
				'change_amount' => 1,												//积分变动原因：签到得1分
				'change_reason' => '签到积分'											//积分变动理由：签到积分
			);
			$signresult = $customerscore->data($signmap)->add();
			if($signresult) {
				$this->redirect( 'Home/MemberHandle/customerSignSucceed?e_id=' . $this->einfo ['e_id'] );	// 签到成功
			}else {
				$this->error ( '签到失败，网络繁忙，请稍后再试。' );
			}
		}
	}
	
	/**
	 * 用户收藏夹删除post处理函数
	 */
	public function customerCollectionDelete(){
		$collectionmap = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$result = M ( 'collection' )->where ( $collectionmap )->setField ( 'is_del', 1 );
		if (! $result) {
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );		// 删除失败
		} else {
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );		// 删除成功
		}
	}
	
	/**
	 * 安全中心修改密码和邮箱的post处理函数。
	 */
	public function customerPwdModify(){
		if(! IS_POST) halt("页面不存在");
		//接收前台数据
		$data = array (
			'password' => I ('password'),
			'email' => I ('email'),
			'e_id' => I ('e_id')
		);
		//获取当前用户
		$cusmap = session('currentcustomer');
		$mymap ['customer_id'] = $cusmap ['customer_id'];
		$map ['customer_id'] = $cusmap ['customer_id'];
		$map ['e_id'] = $data ['e_id'];			//特别注意，这个e_id是需要接收的，在此先手动设置!
		
		//如果提交的时候，密码和邮箱都为空，则服务器未接收到数据
		if(empty($data ['password']) && empty($data ['email'])) {
			$this->ajaxReturn( array ( 'status' => 0 ), 'json' );
		}else {
			//服务器已经接收到数据
			if($data['password'] != '') {
				$map['password'] = md5( $data['password'] );
			}
			if($data['email'] != '') {
				$map['email'] = $data['email'];
			}
			$duplicat = M('customerinfo')->where($map)->find();
			if($duplicat){
				$this->ajaxReturn( array ( 'status' => 2 ), 'json');
			}else {
				$result = M('customerinfo')->where($mymap)->save($map);
				if($result) {		
					$this->ajaxReturn( array ( 'status' => 1 ), 'json');	//修改成功
				}else {
					$this->ajaxReturn( array ( 'status' => 0 ), 'json');	//修改失败（数据库写入失败）
				}
			}
		}
	}
	
	/**
	 * 购物页面添加收藏夹post处理函数。
	 */
	public function addCollection(){
		$map = array(
				'e_id' => $this->einfo ['e_id'],								//商家编号
				'product_id' => I ( 'product_id' ),								//产品编号
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],	//顾客编号
				'is_del' => 0													//没有被删除
		);
		$exist = M('collection')->where( $map )->find();						//判断是否被收藏过
		//返回2代表收藏过了
		if($exist){
			$this->ajaxReturn ( array ( 'status' => 2 ), 'json' );
		} else{
			$map ['record_time'] = time();
			$map ['collection_id'] = md5 (uniqid (rand(), true));			//用md5码产生一个随机的32位编号
			$result = M ( 'collection' )->data( $map )->add ();
			if ($result) {
				$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );		//写入成功，收藏成功返回1
			}else{
				$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );		//写入失败(数据库写入失败)，收藏失败返回0
			}
		}
	}
	
	/**
	 * 我的订单视图。
	 */
	public function myOrder() {
		$checkwxpay = $_REQUEST ['checkwxpay']; // 尝试接收checkwxpay字段信息（如果有），该字段的信息是：是否是微信支付回跳
		$checkorder = $_REQUEST ['wxpayoid']; 	// 尝试接收wxpayoid字段信息（如果有），该字段信息是：如果微信支付，则有一笔微信支付的订单编号
		if (! empty ( $checkwxpay )) $this->checkWeChatPayStatus ( $checkorder ); // 如果是微信支付回跳，则直接调用检查微信支付成功是否成功
		
		$ordermap = array (
				'e_id' => $this->einfo ['e_id'],								// 取当前商家编号
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],	// 取当前客户编号
				'is_del' => 0
		);
		
		import ( 'ORG.Util.Page' );												// 导入ThinkPHP分页控件
		$count = M ( 'ordermain' )->where ( $ordermap )->count ();				// 计算客户订单数
		$page = new Page ( $count, 10 );										// 每页10条订单
		$limit = $page->firstRow . ',' . $page->listRows;
		
		$ordermainList = M ( 'order_cinfo' )->where ( $ordermap )->order ( 'order_time desc, is_payed asc, receive_status asc' )->limit ( $limit )->select ();
		$this->ordermainList = $ordermainList;
		$page->setConfig ( 'theme', '%upPage% %nowPage%/%totalPage% 页 %downPage%' );
		$this->page = $page->show ();
		$this->display();
	}
	
	/**
	 * 用户线上反馈视图。
	 */
	public function feedback() {
		//Step1：伪多态查找模板后统一赋值
		$this->pageinfo = A('Home/MobileCommon')->selectCommonTpl(array( 'e_id' => $this->einfo ['e_id'] ));		//利用下模板选择为自己区分中部导航和底部导航
		$this->display();
	}
	
	/**
	 * 用户线上反馈post提交处理函数。
	 */
	public function onlineQuestionSubmit() {
		//缩写：onlinequestion→oq
		$oqmap = array(
			'question_id' => md5 (uniqid (rand(), true)),
			'e_id' => $this->einfo ['e_id'],
			'question_author_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
			'question_author' => $_SESSION ['currentcustomer'] ['customer_name'],
			'question_time' => time(),
			'question_content' => I ( 'feedback' ),
			'question_type' => -1,	//根结点问题
		);
		$oqresult = M( 'onlinequestion' )->data($oqmap)->add();
		if($oqresult){
			$this->ajaxReturn( array( 'status' => 1, 'msg' => '提交成功！') );
		}else{
			$this->ajaxReturn( array( 'status' => 0, 'msg' => '提交失败！请稍后再试！') );
		}
	}
	
	/**
	 * 确定提交收货地址ajax。
	 */
	public function addDeliConfirm() {
		$delitable = M ( 'deliveryinfo' );
		$province = I ( 'province' );
		$city = I ( 'city' );
		$region = I ( 'region' );
		$address = I ( 'address' );
		$default = 1; // 默认地址
		$delimap = array (
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$dinfocount = $delitable->where ( $delimap )->count();
		if ($dinfocount) $default = 0; // 已有默认地址
		$dinfo = array (
				'deliveryinfo_id' => md5 ( uniqid( rand (), true ) ),
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'receive_person' => I ( 'name' ),
				'contact_number' => I ( 'mobile' ),
				'receive_address' => $address,
				'province' => $province,
				'city' => $city,
				'region' => $region,
				'default_selected' => $default,
				'add_time' => time (),
		);
		$addresult = $delitable->add ( $dinfo ); // 新增地址
		if ($addresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'delivery_id' => $dinfo ['deliveryinfo_id'], 
					),
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => '网络繁忙，请稍后再试！'
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 确定删除收货地址ajax。
	 */
	public function delDeliConfirm() {
		$delmap = array (
				'deliveryinfo_id' => I ( 'did' ),
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$delresult = M ( 'deliveryinfo' )->where ( $delmap )->setField ( 'is_del', 1 );
		if ($delresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => '网络繁忙，请稍后再试！'
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 确定编辑收货地址ajax。
	 */
	public function editDeliConfirm() {
		$province = I ( 'province' );
		$city = I ( 'city' );
		$region = I ( 'region' );
		$address = I ( 'address' );
		$dinfo = array (
				'deliveryinfo_id' => I ( 'did' ),
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'receive_person' => I ( 'name' ),
				'contact_number' => I ( 'mobile' ),
				'receive_address' => $address,
				'province' => $province,
				'city' => $city,
				'region' => $region,
				'latest_modify' => time ()
		);
		$editresult = M ( 'deliveryinfo' )->save($dinfo);
		if ($editresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => '网络繁忙，请稍后再试！'
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 检查一笔订单微信支付的结果。
	 * @param string $order_id 订单编号
	 */
	public function checkwechatPayStatus($order_id = '') {
		$payresult = $this->checkPaySuccessNotify ( $order_id );
		if ($payresult) {
			$ordermap = array (
					'order_id' => $order_id,
					'is_del' => 0
			);
			$updatepay = M ( 'ordermain' )->where ( $ordermap )->setField ( 'is_payed', 1 ); // 支付成功
		}
	}
	
	/**
	 * 检查通知结果。
	 * @param string $order_id 订单编号
	 * @return boolean $wxpaysuccess 检查微信支付通知结果
	 */
	public function checkPaySuccessNotify($order_id = '') {
		$wxpaysuccess = false;
		if (! empty ( $order_id )) {
			$checkpay = array (
					'out_trade_no' => $order_id, // 该笔订单
					'return_code' => 'SUCCESS', // 返回码成功
					'result_code' => 'SUCCESS', // 业务结果成功
					'is_del' => 0
			);
			$wxpaysuccess = M ( 'wechatpaynotify' )->where ( $checkpay )->find();
		}
		return $wxpaysuccess;
	}
	
}
?>