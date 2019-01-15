<?php
import ( 'MobileGuestAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileGuestAction
import ( 'MobileLoginAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileLoginAction
/**
 * 本控制器为餐饮订单。
 * @author 赵臣升。
 * CreateTime:2014/12/03 12:25:36.
 * 载入Home分组下的GuestCommon控制器继承后就有授权登录和企业信息了。
 */
class CateOrderAction extends MobileLoginAction {
	/**
	 * 订单信息视图。
	 */
	public function orderInfo() {
		$scate = A ('Service/Cate');
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];			// 推送openid
		$this->nickname = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['nickname'];		// 推送用户微信昵称
		$orderid = I ( 'oid' );
		$this->ordercatelist = $scate->getOrderInfoById( $orderid, true );	// 找到订单主信息（这里直接是一个视图，传入第二个形参true格式化时间）
		$this->listcount = ! empty( $this->ordercatelist ) ? count( $this->ordercatelist ) : 0 ;	// 订单上餐饮的数量（特别注意这里count对空对象的操作有问题）
		if(! empty ( $_SESSION ['table_id'] )) $this->tableinfo = $_SESSION ['table_id'];			// 如果扫描桌子，推送桌子编号
		$this->display ();
	}
	
	/**
	 * 订单详情视图。
	 */
	public function orderInfo2() {
		$this->display ();
	}
	
	/**
	 * 提交订单post处理函数。
	 */
	public function orderConfirm() {
		$scate = A ( 'Service/Cate' );
		// Step1：接收提交的ajax信息
		$ajaxinfo = array (
				'openid' => I ( 'openid' ),
				'e_id' => I ( 'e_id' ),
				'is_del' => 0
		);
		
		// Step2：检测用户当前是否有未结算的订单，如果有，则提示其结算或者取消才能进行下一笔订单
		//$orderexist = $scate->orderNotPayExist( $ajaxinfo ['e_id'], $ajaxinfo ['openid'] ); // 原来：有没有未支付的订单
		$available = $scate->orderSubmitPreCheck( $ajaxinfo ['e_id'], $ajaxinfo ['openid'] ); // 现在：是否满足提交订单的资格
		if(! $available) $this->ajaxReturn( array( 'errCode' => 20001, 'errMsg' => '您有未支付或未被接收的订单，请先结算或取消!' ) ); // 不满足资格不给提交
		
		// Step3：如果用户具体订单提交资格（早期是没有未结算的订单），再检查当前餐车是否有餐饮信息，有餐饮才去提交（防止页面过期或者history.goback造成的干扰）
		$cartlist = $scate->getCartView( $ajaxinfo ['e_id'], $ajaxinfo ['openid'], true );	// 获取用户餐车中信息
		if(empty( $cartlist )) $this->ajaxReturn( array( 'errCode' => 20002, 'errMsg' => '餐车中没有任何餐饮信息，请刷新餐车!' ) );
		
		// Step4：生成主订单信息（业务逻辑·主）
		$timenow = time ();			//取当前时间
		$ordermain = array (
				'order_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $this->einfo ['e_id'],
				'openid' => $ajaxinfo ['openid'],
				'order_time' => $timenow,
				'visual_number' => strval( $timenow . randCode( 4, 1 ) )
		);
		if (! empty ( $_SESSION ['table_id'] )) $ordermain ['consume_table_id'] = $_SESSION ['table_id'];	// 扫桌子上的二维码进入则记录桌子编号
		
		// Step5：生成子订单信息（业务逻辑·辅）
		$detaillist = array();			// 餐饮清单数组
		$pricefield = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['ismember'] == 1 ? 'member_price' : 'price';	//是会员使用会员价字段，不是会员使用原价字段
		$totalprice = 0;				// 餐饮总价
		for($i = 0; $i < count($cartlist); $i ++) {
			$detaillist [$i] = array(
					'detail_id' => md5 ( uniqid ( rand (), true ) ),
					'order_id' => $ordermain ['order_id'],
					'cate_id' => $cartlist [$i] ['cate_id'],
					'price' => $cartlist [$i] [$pricefield],
					'amount' => $cartlist [$i] ['amount']
			);
			$totalprice += $cartlist [$i] [$pricefield] * $cartlist [$i] ['amount'];		// 总价叠加
		}
		
		// Step6：补充主订单信息
		$ordermain ['total_price'] = $totalprice;
		
		// Step7：写入新订单（成功后顺带删除餐车里的餐品）
		$insertresult = $scate->insertNewOrder( $ordermain, $detaillist );
		if($insertresult) {
			$flushresult = $scate->clearCateCart( $ajaxinfo ['e_id'], $ajaxinfo ['openid'], true );	// 如果订单提交成功，该用户餐车里的东西会被清空（订单失败就退回餐车里）
			$this->ajaxReturn ( array ( 'errCode' => 0, 'errMsg' => 'ok', 'data' => array( 'newOrderId' => $ordermain ['order_id'] ) ) ); // 如果订单提交成功，返回订单编号
		} else {
			$this->ajaxReturn ( array ( 'errCode' => 10000, 'errMsg' => '网络繁忙，请稍后再试!' ) );
		}
	}
	
	/**
	 * 取消订单post处理函数，订单取消后，所有订单中的餐饮被退回餐车中，以待下次结算。
	 */
	public function orderCancel() {
		$scate = A ( 'Service/Cate' );
		// Step1：接收ajax信息
		$ajaxinfo = array(
				'order_id' => I ( 'order_id' ),
				'e_id' => I ( 'e_id' ),
				'openid' => I ( 'openid' )
		);
		// Step2：判断是否满足取消订单的条件（已经付款或被店家接收的订单是不能取消的）
		$cancelavailable = $scate->orderCancelPreCheck ( $ajaxinfo ['order_id'] );
		if($cancelavailable) {
			// Step3：满足取消条件，将该订单取消并将订单中的餐饮退回用户餐车中
			$cancelresult = $scate->cancelOrderById ( $ajaxinfo ['order_id'], false, true );
			if($cancelresult) {
				$this->ajaxReturn ( array ( 'errCode' => 0, 'errMsg' => 'ok' ) );
			} else {
				$this->ajaxReturn ( array ( 'errCode' => 10000, 'errMsg' => '网络繁忙，请稍后再试!' ) );
			}
		}else {
			$this->ajaxReturn ( array ( 'errCode' => 10001, 'errMsg' => '已支付或已备餐的订单不能被取消!' ) );
		}
	}
	
	/**
	 * 用户可否编辑订单的服务器端的检查。
	 */
	public function editOrderCheck() {
		$sc = A ( 'Service/Cate' );
		$checkresult = array(); // 最终检查结果
		$ajaxinfo = array(
				'e_id' => I ( 'e_id' ),
				'openid' => I ( 'openid' ),
				'order_id' => I ( 'oid' )
		);
		$editavailable = $sc->editOrderPreCheck ( $ajaxinfo ['order_id'] ); // 用户编辑订单资格的检查
		if($editavailable) {
			$checkresult = array(
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		}else {
			$checkresult = array(
					'errCode' => 10000,
					'errMsg' => '订单不能被编辑，请及时刷新页面!'
			);
		}
		$this->ajaxReturn( $checkresult );
	}
	
	/**
	 * 用户删除订单的post处理函数。
	 */
	public function delOrder() {
		$sc = A ( 'Service/Cate' );
		$ajaxinfo = array(
				'e_id' => I ( 'e_id' ),
				'openid' => I ( 'openid' ),
				'order_id' => I ( 'oid' )
		);
		// 做一个用户订单删除检测
		$cancelavailable = $sc->cusMarkDelPreCheck( $ajaxinfo ['order_id'] );
		if($cancelavailable) {
			$orderleft = $sc->delOrderById ( $ajaxinfo ['e_id'], $ajaxinfo ['openid'], $ajaxinfo ['order_id'] ); // 用户屏蔽订单处理
			$cancelresult = $sc->cancelOrderById ( $ajaxinfo ['order_id'] ); // 删除订单，餐品不退回餐车（后边两个参数默认false）
			
			if($orderleft >= 0) {
				$handleresult = array(
						'errCode' => 0,
						'errMsg' => 'ok',
						'data' => array(
								'ordernumber' => $orderleft
						)
				);
			}else {
				$handleresult = array(
						'errCode' => 30001,
						'errMsg' => '删除订单失败!'
				);
			}
		} else {
			$handleresult = array(
					'errCode' => 30002,
					'errMsg' => '不能删除未完结的订单!'
			);
		}
		$this->ajaxReturn( $handleresult );
	}
	
	/**
	 * 为订单选择支付方式。
	 */
	public function payMethodConfirm() {
		// 接收提交信息
		$scate = A ( 'Service/Cate' );
		$ajaxinfo = array(
				'order_id' => I ( 'order_id' ),
				'pay_method' => I ( 'method' )
		);
		$setresult = $scate->orderPayMethod( $ajaxinfo ['order_id'], $ajaxinfo ['pay_method'] );
		if($setresult) {
			$result = array(
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$result = array(
					'errCode' => 30001,
					'errMsg' => '设置订单支付方式失败，请刷新后再试!'
			);
		}
		$this->ajaxReturn( $result );
	}
	
	/**
	 * 微信支付。
	 */
	public function wechatPay() {
		// 接收提交信息
		$scate = A ( 'Service/Cate' ); // 餐饮控制器
		$jsapi = A ( 'Service/WeChatPayJsAPI' ); // 微信支付检验控制器
		$ajaxinfo = array(
				'order_id' => I ( 'order_id' ),
				'pay_method' => I ( 'method' )
		);
		$prepareinfo = $scate->prepareOrderWeChatPay ( $ajaxinfo ['order_id'] ); // 准备微信支付信息
		$readyinfo = $jsapi->callUpJsAPIPayV3 ( $prepareinfo ); // 准备调起微信支付
		if($readyinfo) {
			$params = array(
					'wcpid' => $readyinfo,
					'redirecturi' => 'http://www.we-act.cn/weact/CateIndustry/CateOrder/historyOrder/e_id/' . $this->einfo ['e_id'] . '/checkwxpay/1/wxpayoid/' . $ajaxinfo ['order_id']
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
	 * 单笔订单的详情。
	 */
	public function singleOrderInfo() {
		
	}
	
	/**
	 * 历史订单视图。
	 */
	public function historyOrder() {
		$scate = A ( 'Service/Cate' );
		$checkwxpay = $_REQUEST ['checkwxpay']; // 尝试接收checkwxpay字段信息（如果有），该字段的信息是：是否是微信支付回跳
		$checkorder = $_REQUEST ['wxpayoid']; 	// 尝试接收wxpayoid字段信息（如果有），该字段信息是：如果微信支付，则有一笔微信支付的订单编号
		if (! empty ( $checkwxpay )) $scate->checkWeChatPayStatus ( $checkorder ); // 如果是微信支付回跳，则直接调用检查微信支付成功是否成功
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];
		$this->openid = $openid;
		$this->historyorder = $scate->getHistoryOrder( $this->einfo ['e_id'], $openid );
		$this->nickname = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['nickname'];		// 推送用户微信昵称
		$this->cateshopurl = $scate->cateShop( $this->einfo ['e_id'] ); // 推送餐饮微商城主页，供跳转
		$this->display ();
	}
	
	/**
	 * 历史订单视图2。
	 */
	public function historyOrder2() {
		$this->display ();
	}
}
?>