<?php
/**
 * 积分商城管理ajax请求处理控制器。
 * @author wlk,胡睿。
 */
class ScoreProductManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * 查看所有商品(响应商品列表页面的easyui的post请求)。
	 */
	public function getAllProduct() {
		//p(I());die;
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页(默认从第一页开始)
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
	
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
		$query_type = 0;
		$startindex = ($pagenum - 1) * $rowsnum;
		$count = $rowsnum;
		//接收查询参数
		$query_type = I('query_type',0);
		$product_number = I('product_number',0);
		$product_name = I('product_name','');
		$score_sell_amount = I('score_sell_amount',0);
		$prolist = $this->proExchangeListLimit( $e_id, $query_type, $product_number, $product_name,
				$score_sell_amount, $startindex, $count);
		$prototal = count($prolist);
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $prolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 分页读取某商家($e_id)积分商城商品中不同删选条件下所能获取的商品列表数据
	 *
	 * @param string $e_id
	 *        	商家ID
	 * @param int $query_type
	 *        	查询类别（默认:全部可兑换商品0、一级会员可兑商品1、二级会员可兑商品2、三级会员可兑商品3）
	 * @param string $product_number
	 * 			商品编号
	 * @param string $product_name
	 * 			商品名称
	 * @param int $score_sell_amount
	 *  		商品卖出量
	 * @param number $startindex
	 *        	从第几条开始
	 * @param number $count
	 *        	想要读取几条
	 * @return array $prolist
	 * 			返回的ajax请求
	 */
	public function proExchangeListLimit($e_id = '', $query_type = 0, $product_number = '', $product_name = '',
			$score_sell_amount = 0, $startindex = 0, $count = 10) {
		/** 建立查询条件
		 * 1)确定删选条件:e_id，is_use,is_del,member_level
		 * 2)为了减少数据库的查询时长，这里增加一个group字段(product_id),为了不显示多张图片的重复
		 * 3)之所以与之前移动端积分商城不同的group(rule_id)不同，是因为此处一个product_id只显示一次，通过展开获取多条
		 * 4)而之前的移动端积分商城是由于顾客需要去对应的等级购买，所以根据rule_id来确定
		 */
		$scoreprotable = M ( "score_product_image" );
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
		if( isset( $product_number)) {
			$scorepromap['product_number'] = array('like','%'.$product_number.'%');
		}
		if( isset( $product_name)) {
			$scorepromap['product_name'] = array('like','%'.$product_name.'%');
		}
		if( isset( $score_sell_amount)) {	// 此处为积分商城的某product_id对应的卖出量
			$scorepromap['score_sell_amount']  = array('egt',$score_sell_amount);
		}
			
		$prolist = $scoreprotable->where($scorepromap)->group('product_id')->order('add_time desc')->limit( $startindex, $count)->select();
		//p($prolist);die;
		if( !$prolist) {	// 如果查询出来没有数据 ，置为空数组
			$prolist = array();
		}
		else {
			for( $i = 0; $i < count($prolist); $i++) {	// 执行数据转换工作
				$prolist [$i] ['macro_path'] = assemblepath($prolist [$i] ['macro_path']);
				$prolist [$i] ['micro_path'] = assemblepath($prolist [$i] ['micro_path']);
				$prolist [$i] ['add_time'] = timetodate ( $prolist [$i] ['add_time'] );
				$prolist [$i] ['modify_time'] = timetodate ( $prolist [$i] ['modify_time'] );
			}
		}
		return $prolist;
	}
	
	/**
	 * easyUI点击加号展开获取商品实时库存信息和所属的积分商城类别的ajax处理函数。
	 */
	public function getScoreProductStorage() {
		$ajaxinfo = array (	// 初始化ajax返回
				'errCode'=>10001,
				'errMsg'=>'网络繁忙,请稍后重试!'	,
				'data'=>array()
		);
	
		// 首先读取库存的sku信息
		$skuajaxinfo = array(); // 初始化库存ajax用到的信息
		$proskumap = array (
				'product_id' => I ( 'pid' ),
				'is_del' => 0
		);
		$skuinfo = M ( 'score_product_sku' )->where ( $proskumap )->group('sizecolor_id')->order('size_order asc')->select (); // 修改后的新视图，2015/05/02修改
		if( !$skuinfo) {
			$this->ajaxReturn($ajaxinfo);
		}
		else { // 往data里面写入对应的sku信息
			for( $i = 0; $i < count($skuinfo); $i++){
				$skuajaxinfo[$i]['product_color'] = $skuinfo[$i]['product_color'];
				$skuajaxinfo[$i]['product_size'] = $skuinfo[$i]['product_size'];
				$skuajaxinfo[$i]['sku_storage_left'] = $skuinfo[$i]['sku_storage_left'];
				$skuajaxinfo[$i]['score_sell_amount'] = $skuinfo[$i]['score_sell_amount'];
			}
		}
		// 其次读取商品所属会员专区信息
		$scoreareainfo = array(); // 初始化会员专区信息
		$scoreareamap = array(
				'product_id'=>I('pid'),
				//'is_use'=>1,	// 被启用
				'is_del'=>0
		);
		$ruleinfo = M('productexchangerule')->where($scoreareamap)->order('member_level asc')->select();
		if(!$ruleinfo) {
			$this->ajaxReturn($ajaxinfo);
		}
		else {
			for( $j = 0; $j < count($ruleinfo); $j++) {
				switch($ruleinfo[$j]['member_level']){
					case 1:
						$scoreareainfo[$j]['member_level'] = '一级会员专区';
						break;
					case 2:
						$scoreareainfo[$j]['member_level'] = '二级会员专区';
						break;
					case 3:
						$scoreareainfo[$j]['member_level'] = '三级会员专区';
						break;
				}
				if($ruleinfo[$j]['is_use']==1){
					$scoreareainfo[$j]['is_use'] = '是';
				}else{
					$scoreareainfo[$j]['is_use'] = '否';
				}
				$scoreareainfo[$j]['score_amount'] = $ruleinfo[$j]['score_amount'];
				$scoreareainfo[$j]['rule_id'] = $ruleinfo[$j]['rule_id'];
			}
		}
		// 组装ajax返回
		$ajaxinfo = array (
				'errCode' => 0,
				'errMsg' => 'ok',
				'data' => array (
						'skulist' => $skuajaxinfo,
						'scorearealist'=>$scoreareainfo
				)
		);
		///p($scoreareainfo);die;
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * easyUI修改某件商品在所属会员专区的商品信息的ajax处理函数。
	 */
	public function changeProductExchangeRule() {
		$ajaxinfo = array (	// 初始化ajax返回
				'errCode'=> 10001,
				'errMsg'=>'网络繁忙,请稍后重试!'	,
		);
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
		// 加入对于会员换购积分规则的接收
		$ruleGetArr = array();
		$ruleGetArr['rule_id'] = I('rule_id');
		$ruleGetArr['score_amount'] = I('score_amount');
		$ruleGetArr['is_use'] = I('is_use');
	
		$checkRuleMap = array(
				'rule_id'=>$ruleGetArr['rule_id'],
				'is_del'=>0
		);
		$checkResult = M("productexchangerule")->where($checkRuleMap)->find();
		if( !$checkResult) {
			$this->ajaxReturn($ajaxinfo);
		}
	
		$ruletable = M('productexchangerule');
	
		/* 校验记录,判断有无改动，校验字段为score_amount,is_use,发生改变的话，连modify_time一并做修改
		 * 如果两者中任何一个不等，那么必须更新表中记录
		*/
		if( $checkResult['score_amount'] != $ruleGetArr['score_amount'] || $checkResult['is_use'] != $ruleGetArr['is_use'] ) {
			$saveData['rule_id'] = $ruleGetArr['rule_id'];
			$saveData['score_amount'] = $ruleGetArr['score_amount'];
			$saveData['is_use'] = $ruleGetArr['is_use'];
			$saveData['modify_time'] = time();
			$saveRuleResult = $ruletable->save($saveData);
			if( !$saveRuleResult) {
				$this->ajaxReturn($ajaxinfo);
			}
		}
		$ajaxinfo['errCode'] = 0;
		$ajaxinfo['errMsg'] = 'ok';
		$this->ajaxReturn($ajaxinfo);
	}
	
	
	/**
	 * easyUI删除某件商品在所属会员专区的商品信息的ajax处理函数 ( 此处为假删)
	 */
	public function delProductExchangeRule() {
		//p(I());die;
		$ajaxinfo = array (	// 初始化ajax返回
				'errCode'=> 10001,
				'errMsg'=>'网络繁忙,请稍后重试!'
		);
		
		
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array ( "in", explode ( ",", I ( 'pidlist' ) ) ),
				'is_del' => 0
		);
		
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
		$product_id = I('product_id');
	
		$saveRuleMap = array(
				'e_id'=>$e_id,
				'product_id'=>array ( "in", explode ( ",", I ( 'pidlist' ) ) ),
				'is_del'=>0
		);
		$saveRuleData['is_del'] = 1;
		$saveResult = M("productexchangerule")->where($saveRuleMap)->data($saveRuleData)->save();
		if( !$saveResult) {
			$this->ajaxReturn($ajaxinfo);
		}
		$ajaxinfo['errCode'] = 0;
		$ajaxinfo['errMsg'] = 'ok';
		$this->ajaxReturn($ajaxinfo);
	}
	
	
}
?>