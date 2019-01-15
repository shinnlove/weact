<?php
class Order {
	/**
	 * 查询订单视图的SQL语句。
	 * @var string $CONST_ORDER_QUERY_SQL
	 */
	private $CONST_ORDER_QUERY_SQL = "";
	
	/**
	 * 商家编号
	 * @var string $e_id
	 */
	private $e_id = '';
	
	/**
	 * 顾客编号
	 * @var string $customer_id
	 */
	private $customer_id = '';
	
	/**
	 * 顾客微信号，辅助变量
	 * @var string $openid
	 */
	private $openid = '';
	
	/**
	 * 订单编号，要初始化的订单号。
	 * @var string $order_id
	 */
	private $order_id = '';
	
	/**
	 * 订单的信息及商品列表视图。
	 * @var array $orderviewlist
	 */
	private $orderviewlist = array ();
	
	/**
	 * 订单构造函数，传入订单编号、商家编号和顾客编号，生成某商家下某个顾客的某个订单。
	 * 三个参数代表最后一个参数以openid代替顾客编号，当然先要查询customerinfo表转换成customer_id
	 * @param string $e_id 商家编号
	 * @param string $order_id 订单编号
	 * @param string $customer_id 顾客编号
	 * @param string $openid 顾客微信号（可选参数）
	*/
	function __construct($order_id = '', $e_id = '', $customer_id = '', $openid = '') {
		$this->e_id = $e_id;
		$this->customer_id = $customer_id;
		if (! empty ( $openid )) $this->openidToCustomerId ( $e_id, $openid ); // 将openid转换成customer_id
		$this->initOrderView ( $order_id, $e_id, $customer_id ); // 初始化订单视图赋值给类变量$orderviewlist
	}
	
	/**
	 * 对内接口区域。
	 */
	
	/**
	 * （该查询已经废弃不用）查询订单的视图信息。
	 * @param string $order_id 订单编号
	 * @param string $e_id 商家编号
	 * @param string $customer_id 顾客编号
	 * @return string $querysql 查询订单信息视图的SQL语句
	 */
	private function generateOrderQuery($order_id = '', $e_id = '', $customer_id = '') {
		$querysql = "select 
    A.order_id, 
	A.e_id, 
	A.visual_number, 
	A.customer_id,
	A.openid, 
	A.order_time, 
	A.total_price, 
	A.pay_method,
	A.is_payed, 
	A.pay_indeed, 
	A.receive_status, 
	A.express_id, 
	A.express_fee,
	A.is_invoice,
	A.invoice_title,
	A.is_send,
	A.remark,
	B.detail_id,
	B.product_id,
	B.unit_price,
	B.amount,
	B.pro_size,
	B.pro_color,
	B.specification,
	B.special_mark,
	C.nav_id,
	C.product_type,
	C.product_number,
	C.product_name,
	C.sex,
	C.material,
	C.original_price,
	C.current_price,
	C.units,
	C.activity_id,
	C.scanpay_id,
	C.off_shelf,
	D.macro_path,
	D.micro_path
from
    (( select * from t_ordermain where is_del = 0) as A)
        inner join
	((select * from t_orderdetail where is_del = 0) as B) ON A.order_id = B.order_id
        inner join
    ((select * from t_product where is_del = 0) as C) ON B.product_id = C.product_id
        inner join
    ((select * from t_productimage where is_del = 0) as D) ON C.product_id = D.product_id
where A.order_id = '" . $order_id . "' and A.e_id = '" . $e_id . "' and A.customer_id = '" . $customer_id . "'";
		return $querysql;
	}
	
	/**
	 * 通过顾客的openid转换实现查找顾客customer_id
	 * @param string $e_id 商家编号
	 * @param string $openid 顾客微信号
	 */
	private function openidToCustomerId($e_id = '', $openid = '') {
		if (! empty ( $e_id ) && ! empty ( $openid )) {
			$switchmap = array (
					'e_id' => $e_id,
					'openid' => $openid,
					'is_del' => 0
			);
			$customerinfo = M ( 'customerinfo' )->where ( $switchmap )->find ();
			if ($customerinfo && ! empty ( $customerinfo ['openid'] )) {
				$this->customer_id = $customerinfo ['customer_id'];
			}
		}
	}
	
	/**
	 * 初始化订单视图以及其商品列表。
	 * @param string $order_id 订单编号
	 * @param string $e_id 商家编号
	 * @param string $customer_id 顾客编号
	 */
	private function initOrderView($order_id = '', $e_id = '', $customer_id = '') {
		/* $this->CONST_ORDER_QUERY_SQL = $this->generateOrderQuery ( $order_id, $e_id, $customer_id );
		$model = new Model();
		$this->orderviewlist = $model->query ( $this->CONST_ORDER_QUERY_SQL ); // 初始化订单视图数据 */
		// 新订单视图查询
		$ordermap = array (
				'order_id' => $order_id,
				'e_id' => $e_id,
				'customer_id' => $customer_id,
				'cus_mark_del' => 0, // 这是前台调用订单，所以是没有被顾客删除的订单
				'is_del' => 0
		);
		$this->orderviewlist = M ( 'orderinfo_view' )->where ( $ordermap )->select ();
	}
	
	/**
	 * 对外接口区域。
	 */
	
	/**
	 * 获得订单信息。
	 */
	public function getOrderView() {
		for($i = 0; $i < count ( $this->orderviewlist ); $i ++) {
			$this->orderviewlist [$i] ['order_time'] = timetodate ( $this->orderviewlist [$i] ['order_time'] );
			$this->orderviewlist [$i] ['macro_path'] = assemblepath ( $this->orderviewlist [$i] ['macro_path'] );
			$this->orderviewlist [$i] ['micro_path'] = assemblepath ( $this->orderviewlist [$i] ['micro_path'] );
		}
		return $this->orderviewlist;
	}
}
?>