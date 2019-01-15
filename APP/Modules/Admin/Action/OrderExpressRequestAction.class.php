<?php 
/**
 * 订单快递信息请求数据
 */

class OrderExpressRequestAction extends PCViewLoginAction{
	/**
	 * 请求订单数据
	 */
	public function getAllOrderExpress(){
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 							// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 						// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'deliver_time'; 					// 按什么排序，默认按下单时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 					// 排序方式，下单时间降序排序
		$orderExpressMap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
		);
		$oeinfoview = M ( 'order_express' ); 														// 定义视图，该视图由订单主表、客户信息表和配送地址表连接而成
		$oeinfolist = array (); 																			// 订单信息数组
	
		$orderexpresstotal = $oeinfoview->where ( $orderExpressMap )->count (); 										// 计算当前商家下的订单总数
		if ($orderexpresstotal) {
			$oeinfolist = $oeinfoview->where ( $orderExpressMap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $oeinfolist ); $i ++) {
				//时间格式转换
				if($oeinfolist [$i] ['deliver_time']){
					$oeinfolist [$i] ['deliver_time'] = timetodate ( $oeinfolist [$i] ['deliver_time'] );
				}else{
					$oeinfolist [$i] ['deliver_time'] = '';		//如果快递时间为空，则赋值为空
				}
			}
		}
	
		$json = '{"total":' . $orderexpresstotal . ',"rows":' . json_encode ( $oeinfolist ) . '}';
		echo $json;
	}
}

?>