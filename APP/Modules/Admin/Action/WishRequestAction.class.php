<?php
/**
 * 许愿墙请求控制器。
 * @author 赵臣升。
 *
 */
class WishRequestAction extends PCRequestLoginAction {
	/**
	 * 读取用户许愿。
	 */
	public function read() {
	
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'wish_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		//根据条件搜索
		$searchcondition = I('searchcondition');
		$searchcontent = I('searchcontent');
		if ( $searchcontent == '' || $searchcontent == null)
			$search = ' and 1=1';
		else
			$search = ' and '.$searchcondition.' like \'%'.$searchcontent.'%\'';
	
		$wish = M ( "customer_wish" );
		$total = $wish->where ( 'is_del=0 and e_id=\'' . $current_enterprise ['e_id'].'\''.$search )->count (); // 计算总数
		$wishlist = array ();
		if($total){
			$wishlist = $wish->where ( 'is_del=0 and e_id=\'' . $current_enterprise ['e_id'].'\''.$search )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $wishlist ) . '}';
		echo $json;
	}
	
	/**
	 * 删除愿望。
	 */
	public function delWish(){
		$current_enterprise = session ( 'curEnterprise' );
		$rowdata = I('rowdata');
		$wish_ids = divide($rowdata, ',');
		$data = array(
				'e_id' => $current_enterprise['e_id']
		);
		for ($i = 0; $i < count($wish_ids); $i = $i+1){
			$wish = M('wish');
			$data['wish_id'] = $wish_ids[$i];
			$wish->where($data)->setField('is_del',1);
		}
		$this->ajaxReturn ( array ('status' => 1 ), 'json' );
	}
	
}
?>