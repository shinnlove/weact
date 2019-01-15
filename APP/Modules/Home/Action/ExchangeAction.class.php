<?php
class ExchangeAction extends MobileLoginAction {
	// 获取所有的有效兑换信息
	function getAllExchange($e_id) {
		$data = array (
			'e_id' => $e_id,
			'is_del' => 0 
		);
		return M ( "exchangescore" )->where ( $data )->select ();
	}
	
	// 积分兑换
	function exchangeScore() {
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'];
		$exchange_id = I ( 'exchange_id' );
		$data = array (
			'exchange_id' => $exchange_id,
			'e_id' => I ( 'e_id' ),
			'is_del' => 0
		);
		//获取积分兑换信息
		$exchangescore = M ( "exchangescore" );
		$exchange = $exchangescore->where ( $data )->select ();
		//已经没有可以兑换的数量
		if (($exchange [0] ['exchange_amount'] - $exchange [0] ['charged_num']) <= 0)
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		//获取用户积分
		$mymap ['customer_id'] = $customer_id;
		$customerscore = M ( 'customerscore' );
		$result = $customerscore->where ( $mymap )->sum ( 'change_amount' );
		if (! $result)
			$result = 0;
		//用户积分太少了
		if ($result < $exchange [0] ['exchange_score'])
			$this->ajaxReturn ( array ( 'status' => 2 ), 'json' );
		//1.用户兑换成功-----添加用户兑换结果
		$userexchange = M('userexchange');
		$userexchange_array = array(
			'user_exchange_id' => md5 ( uniqid ( rand (), true ) ),
			'exchange_id' => $exchange_id,
			'customer_id' => $customer_id,
			'exchange_time' => time(),
			'is_del' => 0
		);
		$userexchange->create($userexchange_array);
		$userexchange->add();
		//2.用户兑换成功-----更新积分兑换表信息
		$exchangescore->where($data)->setField('charged_num', $exchange [0] ['charged_num']+1);
		//3.用户兑换成功-----更新用户积分信息
		$exchangescore_array = array(
			'score_id' => md5 ( uniqid ( rand (), true ) ),
			'customer_id' => $customer_id,
			'change_amount' => 0 - $exchange [0] ['exchange_score'],
			'change_reason' => '积分兑换',
			'change_time' => time(),
		);
		$customerscore->create($exchangescore_array);
		$customerscore->add();
		$this->ajaxReturn ( array ( 'status' => 3 ), 'json' );
	}
	//所有客户的兑换信息
	public function myExchange(){
		$currentcustomer = session ( 'currentcustomer' );
		$customer_id = $currentcustomer ['customer_id'];
		$model = new Model();
		$e_id = I('e_id');
		$userexchange = $model->table('t_userexchange ue, t_exchangescore es')
		       ->where('ue.exchange_id = es.exchange_id and es.e_id='.$e_id.' and ue.customer_id='.$customer_id)
		       ->field('ue.*,es.*')
		       ->order('ue.exchange_time desc')
		       ->select();
		$this->userexchange = $userexchange;
		$this->total = count($userexchange);
		$this->display();
	}
}
?>