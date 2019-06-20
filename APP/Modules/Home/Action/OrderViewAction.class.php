<?php
import ( 'Class.Model.Home.Cart.shoppingcart', APP_PATH, '.php' ); // 载入购物车类，辅助操作预订单和做商品提交成订单前的检测
import ( 'Class.Model.Home.Order.order', APP_PATH, '.php' ); // 载入订单类，辅助操作订单和查询订单信息等
/**
 * 处理订单控制器，包括预处理订单和提交订单；
 * 包括微信支付和历史订单。
 * @author 王健。
 * CreateTime:2014/05/20 13:25:36.
 */
class OrderViewAction extends MobileLoginAction {
	/**
	 * 订单预处理页面，进入需要检查是否有预处理订单（商品还在购物车）。
	 * 几个注意事项：
	 * 1、进入预订单页面必须有precheck=1标志，防止乱进
	 * 2、对购物车将要提交到预订单，和预订单要提交成订单，都有对购物车中商品进行库存和下架检查，可以写到shoppingcart中封装；
	 * 3、检查的时候的时候一次性读取一个视图，对每一件要提交支付的商品进行循环检查，时间复杂度一次数据库和一次遍历，成功返回ok，失败返回原因；
	 * 4、提交订单的时候，是一个事务过程，对商品sku中先进行扣除sell_amount，然后扣除OK，再去进行订单提交，订单提交ok，再删除购物车，否则就事务回滚；
	 * 5、允许部分结算购物车的话，则提交删除购物车也是部分删除；
	 * 6、订单如果因为超时被取消，则里边的商品库存应该回滚到sku表中。
	 */
	public function orderPreHandle() {
		$precheck = I ( 'precheck', 0 ); // 尝试接收precheck参数（以后可以改成CSRFToken，来预防一些安全性问题
		$pcidlist = explode ( ",", I ( 'pcid' ) ); // 接收购物车向预订单提交的内容，并分隔购物车中准备提交的商品到$pcidlist数组
		if (empty ( $precheck ) || empty ( $pcidlist )) {
			$this->error ('页面已经过期，请正确提交订单！'); // 屏蔽非正常途径或过期或恶意打开预订单页面，提交商品丢失也直接提示错误
		}
		// Step1：查询出被用户勾选准备结算的产品（一次查库+2n时间复杂度）
		$cart = new ShoppingCart ( $this->einfo ['e_id'], $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] );
		$this->cartlist = $cart->preOrderCartList ( $pcidlist ); // 为本页面生成要结算的产品信息
		// Step2：循环统计价格
		$originprice = 0;
		for($i = 0; $i < count ( $this->cartlist ); $i ++) {
			$originprice += $this->cartlist [$i] ['current_price'] * $this->cartlist [$i] ['amount'];
		}
		$this->originprice = $originprice; // 渲染模板最初价格
		// Step3：渲染模板顾客的一些联系和收货信息
		$this->cinfo = $_SESSION ['currentcustomer']; // 推送顾客信息
		// Step4：获得邮费信息
		$expmap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$expinfo = M ( 'expressfee' )->where ( $expmap )->select (); // 邮费信息可能有多个
		$this->expinfo = $expinfo;
		// Step5：读取顾客的收货地址列表
		$delimap = array (
				'e_id' => $this->einfo ['e_id'],
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'is_del' => 0
		);
		$this->deliverylist = M ( 'deliveryinfo' )->where ( $delimap )->select ();
		// Step6：读取顾客能用的优惠券信息
		$this->hascoupon = 0;
		// Step7：读取商品可能参加的活动信息
		
		$this->display();
	}
	
	/**
	 * 预订单提交成订单的ajax处理。
	 * 特别注意：封锁按钮不能让它重复提交，响应后按钮才解除封锁。
	 * Step1：接收准备提交订单的商品列表（购物车主键序列）
	 * Step2：接收订单的配送信息
	 * Step3：接收邮费选择信息
	 * Step4：接收优惠券的选择信息
	 * Step5：接收发票信息
	 */
	public function orderSubmitConfirm() {
		// 接收ajax提交信息，并构造主订单$ordermaininfo
		$timenow = time (); // 取现在时间
		$ordermaininfo = array (
				'order_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $this->einfo ['e_id'],
				'visual_number' => $timenow . randCode ( 4, 1 ), // 订单可视化编号
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'openid' => $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'],
				'order_time' => $timenow, // 下单时间
				'total_price' => I ( 'oripri' ), // 订单本身的总价（原价格）
				'express_fee' => I ( 'expfee' ), // 接收选择的邮费
				'is_invoice' => I ( 'needinvoice' ), // 是否需要发票
				'invoice_title' => I ( 'invoice' ), // 发票抬头
				'deliveryinfo_id' => I ( 'did' ), // 接收订单配送地址信息主键
				'is_send' => 0, // 未发货（数据库也有默认值）
				'is_payed' => 0, // 未支付（数据库也有默认值）
				'pay_indeed' => I ( 'finpri' ), // 实际支付（优惠打折信息叠加邮费后）
		); // 构造主订单信息
		
		// 获取购物车中的数据,构造详单$orderdetailList，完事addAll插入
		$cartmap = array (
				'e_id' => $ordermaininfo ['e_id'],
				'customer_id' => $ordermaininfo ['customer_id'],
				'is_del' => 0
		);
		$cartList = M ( 'cart_product_image' )->where ( $cartmap )->order ( 'add_time desc' )->select ();
		$orderdetailList = array(); // 存放订单详情的每一条记录
		$checkpricesum = 0; // 后台检查总价格
		$delcartid = ""; // 要删除的购物车主键列表
		for ($i = 0; $i < count ( $cartList ); $i += 1) {
			$orderdetailList [$i] = array (
					'detail_id' => md5 ( uniqid ( rand (), true ) ),
					'order_id' => $ordermaininfo ['order_id'],
					'product_id' => $cartList [$i] ['product_id'],
					'unit_price' => $cartList [$i] ['current_price'],
					'amount' => $cartList [$i] ['amount'],
					'pro_size' => $cartList [$i] ['product_size'],
					'pro_color' => $cartList [$i] ['product_color'],
					'is_del' => 0
			); // 循环构造详单信息
			$checkpricesum += $orderdetailList [$i] ['unit_price'] * $orderdetailList [$i] ['amount']; // 单价乘以数量叠加到总价上
			$delcartid .= $cartList [$i] ['cart_id'] . ","; // 拼接要删除的cart_id字符串
		}
		if ($checkpricesum != $ordermaininfo ['total_price']) $ordermaininfo ['total_price'] = $checkpricesum; // 以后台计算的价格为准
		$delcartid = substr ( $delcartid, 0, strlen ( $delcartid ) - 1 ); // 去除删除主键序列的尾部逗号
		
		// 再次处理实付款
		// step1,获取邮费
		$express_fee = $ordermaininfo ['expressfee'];
		
		// step2,获取计算的活动减价
		$activity_discount = 0.00;
		
		// step3,获取计算的优惠券抵扣的price
		$coupon_discount = 0.00;
		
		// step4,计算最终实付款
		$priceneedpay = $ordermaininfo ['total_price'] + $express_fee - $activity_discount - $coupon_discount; // 重新计算付款的价格
		if ($priceneedpay != $ordermaininfo ['pay_indeed']) $ordermaininfo ['pay_indeed'] = $priceneedpay; // 以后台计算的价格为准
		
		// 准备进行事务过程，共分四个步骤
		$maintable = M ( 'ordermain' ); // 订单主表（默认作为事务过程第一张操作的数据库表）
		$detailtable = M ( 'orderdetail' ); // 订单子表
		$carttable = M ( 'cart' ); // 购物车表
		$skutable = M ( 'productsizecolor' ); // 商品sku表，涉及卖出量与库存量
		$globalmsg = ""; // 事务过程全局信息
		$addmaininfo = false; // 步骤一标志，默认false
		$adddetailinfo = false; // 步骤二标志，默认false
		$delcartlist = false; // 步骤三标志，默认false
		$updateskuamount = false; // 步骤四标志，默认false
		$maintable->startTrans(); // 开始事务过程
		
		// Step1：尝试插入主订单
		$addmaininfo = $maintable->data ( $ordermaininfo )->add ();
		// Step2：尝试插入详单
		$adddetailinfo = $detailtable->addAll ( $orderdetailList );
		// Step3：插入详单成功后清空购物车（购物车反复读写，就不逻辑删除了，直接真删除）
		$delcartmap = array (
				'cart_id' => array ( 'in', $delcartid ), // 采用in的SQL语句，买的东西不会太多，效率在接受范围之内
				'e_id' => $ordermaininfo ['e_id'],
				'customer_id' => $ordermaininfo ['customer_id'],
				'is_del' => 0
		);
		$delcartlist = $carttable->where ( $delcartmap )->delete (); // 删除购物车
		// Step4：更新库存信息
		$upnumber = count ( $cartList ); // 要更新的sku库存数量
		$finalup = 0; // 最终更新的sku库存数量
		for ($i = 0; $i < $upnumber; $i ++) {
			$onceresult = false; // 每一次的更新标记置默认为false
			$skutemp = null; // 每次都清空临时索引数组
			$skutemp = array (
					'product_id' => $cartList [$i] ['product_id'],
					'product_size' => $cartList [$i] ['product_size'],
					'is_del' => 0
			); // 循环构造要更新的sku库存信息
			if ($cartList [$i] ['product_type'] == 2) $skutemp ['product_color'] = $cartList [$i] ['product_color']; // 谨慎点，服装类商品的颜色限制分开写
			$onceresult = $skutable->where ( $skutemp )->setInc ( 'sell_amount', $cartList [$i] ['amount'] ); // 更新卖出量（订单提交算是锁定卖出量了）
			if ($onceresult) $finalup += 1; // setInc返回的不是1或0，而是一次更新增加的总数量，所以如果大于0这次算更新库存成功
		}
		if ($finalup == $upnumber) $updateskuamount = true; // 更新量等于要更新条数，此事务步骤执行OK
		
		// Step5:处理错误，返回结果
		if ($addmaininfo && $adddetailinfo && $delcartlist && $updateskuamount) {
			$maintable->commit (); // 四个事务过程都执行成功，这次订单算是提交成功了
			$globalmsg = "ok"; // 订单事务过程执行成功信息
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => $globalmsg,
					'data' => array (
							'oid' => $ordermaininfo ['order_id'] // 订单提交成功，把主单号返回给前台，用以跳转
					)
			);
		} else {
			$maintable->rollback(); // 一个事务出错，一起回滚
			$globalmsg = "提交订单失败，网络繁忙，请稍后再试！"; // 主订单和详单插入失败、或是清空购物车与更新库存信息失败，都属于数据库问题，直接返回网络繁忙
			/* 调试用看每一步有无问题
			if (! $addmaininfo) $globalmsg = "1";
			else if (! $adddetailinfo) $globalmsg = "2";
			else if (! $delcartlist) $globalmsg = "3";
			else if (! $updateskuamount) $globalmsg = "4"; */
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => $globalmsg,
					'data' => array () // 不成功空信息
			);
		}
		$this->ajaxReturn( $ajaxresult ); // 返回给前台订单提交结果
	}
	
	/**
	 * 订单详情视图页面，订单被正式提交后显示，或查看订单详情时显示。
	 */
	public function orderInfo() {
		$order_id = I ( 'order_id' ); // 接收订单编号
		if (empty ( $order_id )) _404( 'Home/Order/myOrder', array( 'e_id' => $this->einfo ['e_id'] ), '' );
		$order = new Order ( $order_id, $this->einfo ['e_id'], $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'] ); // 初始化订单类对象
		$orderinfo = $order->getOrderView (); // 得到订单信息（初始化的时候已经传入了order_id，此处不必要再传参）
		if (empty ( $orderinfo )) $this->error('订单信息不存在或已过期！');
		// Step1：可以进入订单开始选择订单模板
		$navinfo = array (
				'e_id' => $this->einfo ['e_id'],
				'order_id' => $order_id,
				'is_del' => 0
		);
		// Step2：初始化模板信息
		$tpl_indexpath = strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ); 	// PHP自带函数，转为小写
		$mobilecommon = A ( 'Home/MobileCommon' ); 												// 实例化Home分组下，名为MobileCommon的控制器，创建其对象$mobilecommon
		$this->pageinfo = $mobilecommon->selectTpl ( $navinfo, $tpl_indexpath );
		unset ( $mobilecommon ); // 注销此对象释放内存
		// Step3：推送订单信息
		$this->oinfo = $orderinfo; // 向模板渲染订单信息
		$this->display($this->pageinfo ['template_realpath']); // 渲染要展示的模板
	}
	
	/**
	 * 订单付款成功页面。
	 */
	public function orderSuccess() {
		$data = array (
				'e_id' => I ( 'e_id' ),
				'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
				'order_id' => I ( 'order_id' ) 
		)
		;
		$ordermain = M ( 'ordermain' )->where ( $data )->find ();
		if (! $ordermain) {
			$this->error ( '页面不存在' );
		}
		$this->ordermain = $ordermain;
		$this->display ();
	}
	
	/**
	 * 申请微信支付ajax请求处理。
	 */
	public function wechatPay() {
		// 接收提交信息
		$jsapi = A ( 'Service/WeChatPayJsAPI' ); // 微信支付检验控制器
		$ajaxinfo = array (
				'order_id' => I ( 'oid' ),
				'pay_method' => I ( 'method' )
		);
		$prepareinfo = $this->prepareOrderWeChatPay ( $ajaxinfo ['order_id'] ); // 准备微信支付信息
		$readyinfo = $jsapi->callUpJsAPIPayV3 ( $prepareinfo ); // 准备调起微信支付
		if ($readyinfo) {
			$params = array(
					'wcpid' => $readyinfo,
					'redirecturi' => 'http://www.we-act.cn/Home/Order/myOrder/e_id/' . $this->einfo ['e_id'] . '/checkwxpay/1/wxpayoid/' . $ajaxinfo ['order_id']
			);
			$resultdata = array(
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => $params
			);
		} else {
			$resultdata = array(
					'errCode' => 20000,
					'errMsg' => '微信支付调起失败，请等待商家准备好微信支付。'
			);
		}
		$this->ajaxReturn( $resultdata );
	}
	
	/**
	 * 根据订单信息生成预支付信息。
	 * @param string $order_id 订单编号
	 * @return array $payinfo 预支付信息
	 */
	public function prepareOrderWeChatPay($order_id = '') {
		$payinfo = array(); // 等待微信支付的信息包
		if(! empty ( $order_id )) {
			$maininfo = $this->getOrderMainInfoById ( $order_id ); // 查找订单主信息
			if ($maininfo) {
				$payinfo = array(
						'e_id' => $maininfo ['e_id'],
						'openid' => $maininfo ['openid'],
						'body' => '支付商品订单 ' . $maininfo ['visual_number'],
						'out_trade_no' => $maininfo ['order_id'],
						'total_fee' => $maininfo ['total_price'] * 100,
						'time_start' => formatwechatpaydate ( $maininfo ['order_time'] ),
						'time_end' => formatwechatpaydate ( $maininfo ['order_time'] + 7200 ) // 默认2小后失效
				);
			}
		}
		return $payinfo;
	}
	
	/**
	 * 通过订单主键获取订单的信息，用以生成微信预支付。
	 * @param string $order_id 订单主键
	 * @return array $orderinfo 订单信息
	 */
	public function getOrderMainInfoById($order_id = '') {
		$orderinfo = array ();
		if (! empty( $order_id )) {
			$ordermap = array (
					'order_id' => $order_id,
					'is_del' => 0
			);
			$orderinfo = M ( 'ordermain' )->where ( $ordermap )->find ();
		}
		return $orderinfo;
	}
	
}
?>