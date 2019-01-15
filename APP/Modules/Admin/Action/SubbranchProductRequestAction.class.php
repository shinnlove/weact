<?php
/**
 * 分店商品管理控制器，主要处理分店商品的分拨。
 * @author 胡睿
 * CreateTime:2015/05/18 21:54:36.
 */
class SubbranchProductRequestAction extends PCRequestLoginAction {
	
	/**
	 * 批量分拨商品到门店的ajax处理请求。
	 */
	public function batchAllocateProduct() {
		// 接收信息
		if (! IS_POST) {
			$this->error ( 'Sorry, page not exist!' ); // 防止恶意打开
		}
		
		$ajaxinfo = array (
				'pidlist' => I ( 'pidlist', '' ), // 接收要分割的商品编号
				'sidlist' => I ( 'sidlist', '' ) // 接收要分割的分店编号
		); // 分配给的分店编号（可能为空代表删除，也可能不空要比对）
		
		$pidlist = explode ( ",", $ajaxinfo ['pidlist'] ); // 切割商品数组
		$sidlist = explode ( ",", $ajaxinfo ['sidlist'] ); // 切割门店数组
		//p($pidlist);p($sidlist);die;
		$pidcount = count ( $pidlist ); // 统计本次要分拨的商品数量
		$sidcount = count ( $sidlist ); // 统计本次要分拨到的门店数量
		
		$sumsuccess = 0; // 本次分拨总计成功商品数量
		$subprotable = M ( 'subbranchproduct' ); // 实例化表结构，分店商品表
		$subprotable->startTrans ();
		
		// 对商品数组进行循环处理
		for ($i = 0; $i < $pidcount; $i ++) {
			
			$currentpid = $pidlist [$i]; // 当前循环分拨的这件商品
			
			// 初始化参数
			$newsubbranchlist = array (); // 本次提交的分店list
			$oldsubbranchlist = array (); // 数据库中保存的旧分店list
			$addsubbranchlist = array (); // 对比出来新增的分店list
			$delsubbranchlist = array (); // 对比出来要删除的分店list
			
			if (! empty ( $sidlist )) {
				$newsubbranchlist = $sidlist; // 如果接收到的分店列表不空
			}
			
			// 查询当前所有有这个商品的分店
			$proexistmap = array (
					'product_id' => $currentpid,
					'is_del' => 0
			);
			$subbranchexist = $subprotable->where ( $proexistmap )->distinct ( 'subbranch_id' )->select (); // 找寻有这个商品的分店
			if (! empty ( $subbranchexist )) {
				$oldsubbranchlist = $subbranchexist; // 查询到的原来的分店他
			}
			
			$this->compareProSellSubByArray ( $newsubbranchlist, $oldsubbranchlist, $delsubbranchlist, $addsubbranchlist ); // 比对得出add和del
			$addresult = $this->syncAddSub ( $addsubbranchlist, $currentpid ); // 添加分店商品
			$revokeresult = $this->syncDelSub ( $delsubbranchlist, $currentpid ); // 撤回分店商品
			
			if ($addresult && $revokeresult) {
				$sumsuccess += 1; // 对这件商品的分拨，两个步骤都操作成功，才算分拨成功
			} else {
				// 操作不成功，事务回滚，并且立刻终止循环
				$subprotable->rollback();
				$ajaxresult ['errCode'] = 10002;
				$ajaxresult ['errMsg'] = "分拨商品失败，网络繁忙，请不要重复提交!";
				break; // 立即阻止循环
			}
			
		}
		
		// 循环结束进行判断，如果成功数和要分拨的数量相同，则这个批量分拨是成功的
		if ($pidcount == $sumsuccess) {
			$subprotable->commit ();
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台参数
	}
	
	/**
	 * 接收前台ajax提交然后处理的函数，分发商品提交ajax处理函数。
	 */
	public function handleDistributor() {
		// 接收信息
		if (! IS_POST) {
			$this->error ( 'Sorry, page not exist!' ); // 防止恶意打开
		}
		// 准备返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
	
		$ajaxinfo = array (
				'product_id' => I ( 'pid', '' ), // 接收商品编号（要操作的商品编号绝对不能为空）
				'sidlist' => I ( 'sidlist', '' ) // 接收要分割的分店编号
		); // 分配给的分店编号（可能为空代表删除，也可能不空要比对）
	
		$subprotable = M ( 'subbranchproduct' ); // 实例化表结构，分店商品表
		// 初始化参数
		$newsubbranchlist = array (); // 本次提交的分店list
		$oldsubbranchlist = array (); // 数据库中保存的旧分店list
		$addsubbranchlist = array (); // 对比出来新增的分店list
		$delsubbranchlist = array (); // 对比出来要删除的分店list
	
		if (! empty ( $ajaxinfo ['sidlist'] )) {
			// 如果接收到的分店列表不空
			$newsubbranchlist = explode ( ',', $ajaxinfo ['sidlist'] ); // 切割成分店id列表
		}
	
		// 查询当前所有有这个商品的分店
		$proexistmap = array (
				'product_id' => $ajaxinfo ['product_id'],
				'is_del' => 0
		);
		$subbranchexist = $subprotable->where ( $proexistmap )->distinct ( 'subbranch_id' )->select (); // 找寻有这个商品的分店
		if (! empty ( $subbranchexist )) {
			$oldsubbranchlist = $subbranchexist; // 查询到的原来的分店他
		}
	
		$this->compareProSellSubByArray ( $newsubbranchlist, $oldsubbranchlist, $delsubbranchlist, $addsubbranchlist );
	
		// 在$subProTable模型中启动事务
		$subprotable->startTrans ();
	
		$addresult = $this->syncAddSub ( $addsubbranchlist, $ajaxinfo ['product_id'] ); // 添加分店商品
		$revokeresult = $this->syncDelSub ( $delsubbranchlist, $ajaxinfo ['product_id'] ); // 撤回分店商品
	
		if ($addresult && $revokeresult) {
			$subprotable->commit ();
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$subprotable->rollback();
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "分拨商品失败，请不要重复提交!";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 对数据库中的相关分店的商品售卖信息进行插入操作(考虑分店商品表和分店商品sku表，初始化库存同主店商品表和主店sku表)
	 * 1、针对subbranchproduct表相关元组的添加
	 * 2、针对subbranchsku表相关元组的添加
	 *
	 * @param array $arrayAdd:需要添加进入数据库的所有分店id
	 * @param string $pid:需要添加进入数据库的商品编号
	 */
	public function syncAddSub($arrayAdd, $pid) {
		if (empty ( $arrayAdd )) // 如果为空值
			return true;
		// 校验$pid合法性(查找该e_id下该商品是否存在)
		$proMap = array (
				'product_id' => $pid,
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$product = M ( "product" )->where ( $proMap )->find ();
		if (! $product) // 该e_id下不存在此编号的商品
			return true;
			
		// 通过t_productsizecolor得到所有商品库存信息
		$proSkuMap = array (
				'product_id' => $pid,
				'is_del' => 0
		);
		$proSkuList = M ( "productsizecolor" )->where ( $proSkuMap )->select ();
	
		$subProArray = array ();
		$subSkuArray = array ();
		$subSkuCount = 0;
		for($i = 0; $i < count ( $arrayAdd ); $i ++) { // 针对于每个分店
			// 第一步：拼接分店商品表添加信息
			$subProArray [$i] ['sub_pro_id'] = md5 ( uniqid ( rand (), true ) );
			$subProArray [$i] ['product_id'] = $pid;
			$subProArray [$i] ['subbranch_id'] = $arrayAdd [$i] ['subbranch_id'];
			$subProArray [$i] ['sub_storage'] = 0; // 分拨库存，不用理会主表数量，添加sku时候自动触发（但是必须要先添加主表）
			$subProArray [$i] ['sub_sell'] = 0; // 分店库存为重新开始卖
			/* $subProArray [$i] ['sub_storage'] = $product ['storage_amount'];
				$subProArray [$i] ['sub_sell'] = 0;	// 分店库存为重新开始卖 */
			$subProArray [$i] ['sub_storage_warn'] = $product ['storage_warn'];
			$subProArray [$i] ['browsed_amount'] = 0;
			$subProArray [$i] ['followed_amount'] = 0;
			$subProArray [$i] ['recommended_amount'] = 0;
			$subProArray [$i] ['add_time'] = time ();
			$subProArray [$i] ['latest_modify'] = time ();
			$subProArray [$i] ['on_shelf'] = $product ['on_shelf'];
			$subProArray [$i] ['onshelf_time'] = time ();
			$subProArray [$i] ['scanpay_id'] = $product ['scanpay_id'];
			$subProArray [$i] ['is_feature'] = $product ['is_feature'];
			$subProArray [$i] ['is_new'] = $product ['is_new'];
			$subProArray [$i] ['is_preferential'] = $product ['is_preferential'];
			$subProArray [$i] ['remark'] = "";
			$subProArray [$i] ['is_del'] = 0;
			// 第二步:拼接分店sku添加信息
			for($j = 0; $j < count ( $proSkuList ); $j ++) {
				$subSkuArray [$subSkuCount] ['sub_sku_id'] = md5 ( uniqid ( rand (), true ) );
				$subSkuArray [$subSkuCount] ['subbranch_id'] = $subProArray [$i] ['subbranch_id'];
				$subSkuArray [$subSkuCount] ['product_id'] = $subProArray [$i] ['product_id'];
				$subSkuArray [$subSkuCount] ['sub_pro_id'] = $subProArray [$i] ['sub_pro_id'];
				$subSkuArray [$subSkuCount] ['sizecolor_id'] = $proSkuList [$j] ['sizecolor_id'];
				$subSkuArray [$subSkuCount] ['sku_color'] = $proSkuList [$j] ['product_color'];
				$subSkuArray [$subSkuCount] ['sku_size'] = $proSkuList [$j] ['product_size'];
				$subSkuArray [$subSkuCount] ['size_order'] = $proSkuList [$j] ['size_order'];
				$subSkuArray [$subSkuCount] ['subsku_storage'] = $proSkuList [$j] ['storage_amount'];
				$subSkuArray [$subSkuCount] ['subsku_sell'] = 0;
				$subSkuArray [$subSkuCount] ['remark'] = "";
				$subSkuArray [$subSkuCount] ['is_del'] = 0;
				$subSkuCount ++;
			}
		}
	
		$flag = false;
		$subProTable = M ( "subbranchproduct" ); 
		$subSkuTable = M ( "subbranchsku" );
		// 在$subProTable模型中启动事务
		//$subProTable->startTrans ();
	
		$result1 = $subProTable->addAll ( $subProArray );
		$result2 = $subSkuTable->addAll ( $subSkuArray );
	
		if ($result1 && $result2) {
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 对数据库中的相关分店的商品售卖信息进行删除操作（此处为真删）
	 * 1、subbranchsku表相关元组的删除
	 * 2、subbranchproduct表相关元组的删除
	 *
	 * @param array $arrayDel:分店ID
	 *        	(需要从数据库中删除的数据,删除只需要送入主键即可)
	 * @param string $pid:需要进行删除的商品编号
	 */
	public function syncDelSub($arrayDel, $pid) {
		if (empty ( $arrayDel )) // 如果为空值
			return true;
			
		// 校验$pid合法性(查找该e_id下该商品是否存在)
		$proMap = array (
				'product_id' => $pid,
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$product = M ( "product" )->where ( $proMap )->find ();
		if (! $product) // 该e_id下不存在此编号的商品
			return true;
	
		$subProTable = M ( "subbranchproduct" );
		// 首先查询该删除的所有sub_pro_id
		$proSubMap = array (
				'product_id' => $pid,
				'subbranch_id' => array (
						'in',
						$arrayDel
				)
		);
		$subProIDList = $subProTable->where ( $proSubMap )->getField ( 'sub_pro_id', true );
	
		// 4、开启事务过程，通过$subProIDList中的sub_pro_id值删除表subbranchsku中对应sub_pro_id的数据
		$subSkuTable = M ( "subbranchsku" );
		/* 在subSkuTable表中启动事务，先针对subSkuTable进行删除操作（避免主外键关联导致的级联删除出错）
		 * 首先对库存表进行删除
		 * 1、在$subSkuTable中寻找$subProIDList中的sub_pro_id出现的次数
		 * 2、如果出现次数为0，那么本次删除操作成功（此处之所以加一个判断是因为成功了影响记录数为0）
		 * 3、如果出现次数不为0，那么开始删除，并保留删除操作结果状态位（出错返回false，成功返回影响记录数）
		*/
		$subSkuDelResult = true; // 保存subSkuTable的操作结果
		$subSkuMap = array (
				'sub_pro_id' => array (
						'in',
						$subProIDList
				)
		);
		$subSkuCount = $subSkuTable->where ( $subSkuMap )->count ();
	
		if ($subSkuCount != 0) { // 出现次数不为0，开始删除
			// 删除表subSku中所有sub_pro_id在$subProIDList中的记录，并返回结果
			$subSkuDelResult = $subSkuTable->where ( $subSkuMap )->delete ();
		}
		/*
		 * 接着对分店商品表进行删除
		 */
		$subProDelResult = $subProTable->where ( $proSubMap )->delete ();
	
		$flag = false;
		if ($subSkuDelResult && $subProDelResult) {
			// 提交事务
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 售卖某商品的所有分店的比对函数
	 *
	 * @param array $arrayNew:
	 *        	从界面接收到的最新数据 subbranch_id数组
	 * @param array $arrayOld:
	 *        	从数据库中查询出来的数据subbranch_id
	 * @param array $arrayDel:
	 *        	需要从数据库中删除的数据,删除只需要送入主键即可
	 * @param array $arrayAdd:
	 *        	需要添加进入数据库的数据，只需要送入一个分店id即可
	 */
	public function compareProSellSubByArray($arrayNew, $arrayOld, &$arrayDel, &$arrayAdd) {
		$arrayAddCount = 0;
		$arrayDelCount = 0;
	
		$arrayOldSet = array ();
	
		// 新的跟老的进行比对
		foreach ( $arrayOld as $valueOld ) {
			$key = $valueOld ['subbranch_id']; // 将分店编号作为key值
			// 初始化均将被删
			$arrayOldSet [$key] = 1;
		}
	
		foreach ( $arrayNew as $valueNew ) { // $valueNew存放的是subbranch_id
			if (isset ( $arrayOldSet [$valueNew] )) // 如果已经设置，那么就不删除
				$arrayOldSet [$valueNew] = 0;
			else { // 未设置，说明为添加
				$arrayAdd [$arrayAddCount] ['subbranch_id'] = $valueNew;
				$arrayAddCount ++;
			}
		}
	
		foreach ( $arrayOldSet as $key => $value ) { // 设置删除
			if ($value == 1) {
				$arrayDel [$arrayDelCount] = $key; // 送入需删除记录的主键
				$arrayDelCount ++;
			}
		}
	}
	
	
}
?>