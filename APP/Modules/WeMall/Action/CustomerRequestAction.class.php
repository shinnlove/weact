<?php
/**
 * 用户选导购、添加收藏等ajax请求控制器。
 * 将用户基本处理相关的ajax请求处理放到这里，代码不会集中在一个控制器中太多。
 * @author 赵臣升。
 * CreateTime:2015/05/15 21:22:36.
 */
class CustomerRequestAction extends LoginRequestAction {
	/**
	 * 用户选择导购ajax处理函数。
	 */
	public function selectGuide() {
		$guide_id = I ( 'gid' ); // 接收导购编号
		if (empty ( $guide_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "要选择的导购编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id']; // 顾客编号
		// 定义一个变量记录是选导购还是换导购
		$changetype = 0; 							// 选导购是0，换导购是1，默认是选导购
		$recordtable = M ( "changeguiderecord" ); 	// 实例化换导购记录表对象
		$cgtable = M ( "customerguide" ); 			// 顾客导购表
		
		// 根据$sid、$cid在t_customerguide表中查找，如果存在该记录，那么说明重复选择导购
		$cgmap = array (
				'customer_id' => $customer_id, 		// 当前顾客
				//'subbranch_id' => $this->sid, 		// 当前分店
				'is_del' => 0 
		);
		$cginfo = $cgtable->where ( $cgmap )->order ( "choose_time desc" )->find (); // 尝试寻找顾客在这家店选过的导购
		if ($cginfo) { 
			// 如果查出来了，可能是在该店重复选择某导购或更换该店的导购
			if ($cginfo ['guide_id'] == $guide_id) {
				// 如果导购重合
				$this->ajaxresult ['errCode'] = 10003;
				$this->ajaxresult ['errMsg'] = "您已选择该导购，请勿重复选择！";
				$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
			} else {
				$changetype = 1; // 这种情况是换导购
			}
		}
		
		// 如果扫码导购不是其自身的导购，两种情况：1、选导购 2、换导购
		$recordtable->startTrans (); // 开始事务
		// 事务1，对选换导购记录表插入一条记录（一直是增加的）：
		$recordinfo = array (
				'changerecord_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $this->eid, 			// 当前商家下
				'subbranch_id' => $this->sid, 	// 当前分店
				'customer_id' => $customer_id, 	// 当前顾客
				'guide_id' => $guide_id, 		// 当前导购
				'change_type' => $changetype, 	// 选导购是0，换是1
				'change_time' => time () 
		);
		if ($changetype == 0) {
			$recordinfo ['remark'] = "顾客在分店主页选择编号为" . $recordinfo ['guide_id'] . "的导购。";
		} else {
			$recordinfo ['remark'] = "顾客在分店主页更换编号为" . $recordinfo ['guide_id'] . "的导购。";
		}
		$recordresult = $recordtable->add ( $recordinfo ); // 步骤1：记录选换导购
		// 事务2：新增或更换customerguide表信息
		$updatecustomer = array (
				'customer_id' => $customer_id, 	// 当前顾客
				'e_id' => $this->eid, 			// 当前商家
				'subbranch_id' => $this->sid, 	// 选换的导购所属分店编号
				'guide_id' => $guide_id, 		// 导购编号
				'choose_time' => time (), 		// 选择导购时间
				'guide_remarkname' => '', 		// 特别注意：导购对用户的备注名要置空！！！
				'remark' => '',
				'is_del' => 0
		);
		$updateresult = false;
		if ($changetype == 0) {	
			// 0是选导购
			$updatecustomer ['cus_guide_id'] = md5 ( uniqid ( rand (), true ) );
			$updateresult = $cgtable->add ( $updatecustomer ); // 添加新导购记录
		} else {
			// 1是换导购
			$savemap = array (
					'customer_id' => $customer_id, // 当前顾客
					//'subbranch_id' => $this->sid, // 当前分店
					'subbranch_id' => $cginfo ['subbranch_id'], // 找到哪个重复换哪个
					'is_del' => 0
			);
			$updateresult = $cgtable->where ( $savemap )->save ( $updatecustomer ); 
		} 
		if ($recordresult && $updateresult) {
			$recordtable->commit (); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$recordtable->rollback (); // 有一个不成功就事务回滚
			$scanresult ['errCode'] = 10004;
			$scanresult ['errMsg'] = "选换导购失败，请稍后再试！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 检测某件商品是否被某用户收藏的ajax请求处理。
	 */
	public function checkCollected() {
		$pid = I ( 'pid' ); // 接收商品编号
		if (empty ( $pid )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "检测收藏的商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		$collectiontable = M ( 'collection' ); // 实例化收藏夹表
		// 先检测当前用户是否收藏过这件商品
		$checkexist = array (
				'product_id' => $pid, // 商品编号
				'e_id' => $this->eid, // 商家编号
				'subbranch_id' => $this->sid, // 分店编号（在哪家分店收藏的商品）
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前用户编号
				'is_del' => 0 // 没有被删除的
		);
		$existinfo = $collectiontable->where ( $checkexist )->find (); // 找到这件商品在这家分店有没有加过收藏夹
		if ($existinfo) {
			$this->ajaxresult ['data'] ['collected'] = 1; // 收藏过这件商品
			$this->ajaxresult ['data'] ['cid'] = $existinfo ['collection_id']; // 收藏夹编号
		} else {
			$this->ajaxresult ['data'] ['collected'] = 0; // 没有收藏过这件商品
		}
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 用户添加收藏的ajax请求处理。
	 */
	public function addCollection() {
		$pid = I ( 'pid' ); // 接收商品编号
		if (empty ( $pid )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "要收藏的商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		$collectiontable = M ( 'collection' ); // 实例化收藏夹表
		// 先检测当前用户是否收藏过这件商品
		$checkexist = array (
				'product_id' => $pid, // 商品编号
				'e_id' => $this->eid, // 商家编号
				'subbranch_id' => $this->sid, // 分店编号（在哪家分店收藏的商品）
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前用户编号
				'is_del' => 0 // 没有被删除的
		);
		$existinfo = $collectiontable->where ( $checkexist )->find (); // 发现这件商品在这家分店有没有加过收藏夹
		if ($existinfo) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "重复收藏的商品！";
			$this->ajaxresult ['data'] ['collected'] = 1; // 收藏过了
			$this->ajaxresult ['data'] ['cid'] = $existinfo ['collection_id']; // 收藏夹编号
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 若没有重复，则处理增加收藏
		$newcollection = array (
				'collection_id' => md5 ( uniqid ( rand (), true ) ), // 收藏夹编号
				'product_id' => $pid, // 商品编号
				'e_id' => $this->eid, // 商家编号
				'subbranch_id' => $this->sid, // 分店编号（在哪家分店收藏的商品）
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前用户编号
				'record_time' => time () 
		);
		$addresult = $collectiontable->add ( $newcollection ); // 添加收藏
		if ($addresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 用户删除收藏的ajax请求处理。
	 */
	public function deleteCollection() {
		$cid = I ( 'cid' ); // 接收参数，收藏夹编号
		$pid = I ( 'pid' ); // 尝试接收商品编号
		if (empty ( $cid ) && empty ( $pid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "收藏夹编号或商品编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		// 处理删除收藏
		$delmap = array (); // 删除条件
		if (! empty ( $cid )) {
			// 收藏夹方式删除
			$delmap = array (
					'collection_id' => $cid, // 收藏夹编号
					'is_del' => 0
			);
		} else {
			// 收藏夹方式删除
			$delmap = array (
					'product_id' => $pid, // 商品编号
					'e_id' => $this->eid, // 商家编号
					'subbranch_id' => $this->sid, // 分店编号（在哪家分店收藏的商品）
					'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前用户编号
					'is_del' => 0 // 没有被删除的
			);
		}
		$deleteresult = M ( 'collection' )->where ( $delmap )->delete ();
		if ($deleteresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} 
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 添加顾客配送地址信息的ajax请求处理。
	 */
	public function addDeliveryInfo() {
		$receive_person = I ( 'personName' ); // 接收要编辑的收货人信息
		$contact_number = I ( 'mobile' ); // 接收要编辑的联系电话
		$address = I ( 'detail' ); // 接收要编辑的地址
		$province = I ( 'province' ); // 接收省份
		$city = I ( 'city' ); // 接收城市
		$region = I ( 'district' ); // 接收地区
		
		// 检验参数完整性，以下参数少一个都毙掉
		if (empty ( $receive_person )) {
			$this->ajaxresult ['errCode'] = 10011;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，收货人不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $contact_number )) {
			$this->ajaxresult ['errCode'] = 10012;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，收货人联系方式不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $address )) {
			$this->ajaxresult ['errCode'] = 10013;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，收货地址不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $province )) {
			$this->ajaxresult ['errCode'] = 10014;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，省份不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $city )) {
			$this->ajaxresult ['errCode'] = 10015;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，城市不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $region )) {
			$this->ajaxresult ['errCode'] = 10016;
			$this->ajaxresult ['errMsg'] = "新增收货地址失败，地区不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过检验准备对数据库更新
		$deliverytable = M ( 'deliveryinfo' ); // 实例化配送信息表
		// 要插入的的新配送信息记录
		$newinfo = array (
				'deliveryinfo_id' => md5 ( uniqid ( rand (), true ) ), // 新配送地址的主键
				'e_id' => $this->eid, // 当前商家下
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前顾客
				'receive_person' => $receive_person,
				'contact_number' => $contact_number,
				'receive_address' => $address,
				'province' => $province,
				'city' => $city,
				'region' => $region,
				'add_time' => time ()
		);
		$addresult = $deliverytable->add ( $newinfo ); // 新增收货地址信息
		if ($addresult) {
			// 如果更新成功
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			// 前台需要这样的信息，一并返回一下
			$this->ajaxresult ['data'] = array (
					'id' => $newinfo ['deliveryinfo_id'],
					'province' => $province,
					'city' => $city,
					'district' => $region,
					'detail' => $address,
					'personName' => $receive_person,
					'mobile' => $contact_number
			);
		} else {
			// 如果更新失败
			$this->ajaxresult ['errCode'] = 10017;
			$this->ajaxresult ['errMsg'] = "新增信息失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 编辑顾客配送地址信息的ajax请求处理。
	 */
	public function editDeliveryInfo() {
		$deliveryid = I ( 'id' ); // 接收要编辑的deliveryid
		
		$receive_person = I ( 'personName' ); // 接收要编辑的收货人信息
		$contact_number = I ( 'mobile' ); // 接收要编辑的联系电话
		$address = I ( 'detail' ); // 接收要编辑的地址
		$province = I ( 'province' ); // 接收省份
		$city = I ( 'city' ); // 接收城市
		$region = I ( 'district' ); // 接收地区
		
		// 检验参数完整性，以下参数少一个都毙掉
		if (empty ( $deliveryid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，要编辑的信息编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $receive_person )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，收货人不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $contact_number )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，收货人联系方式不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $address )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，收货地址不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $province )) {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，省份不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $city )) {
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，城市不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		if (empty ( $region )) {
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，地区不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过检验准备对数据库更新
		$deliverytable = M ( 'deliveryinfo' ); // 实例化配送信息表
		// 要更新的记录条件
		$updatemap = array (
				'deliveryinfo_id' => $deliveryid, // 要更新的主键
				'e_id' => $this->eid, // 当前商家下
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前顾客
				'is_del' => 0
		); 
		// 要更新的记录信息
		$updateinfo = array (
				'receive_person' => $receive_person,
				'contact_number' => $contact_number,
				'receive_address' => $address,
				'province' => $province,
				'city' => $city,
				'region' => $region, 
				'latest_modify' => time ()
		); 
		$updateresult = $deliverytable->where ( $updatemap )->save ( $updateinfo ); // 更新收货地址信息
		if ($updateresult) {
			// 如果更新成功
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			// 前台需要这样的信息，一并返回一下
			$this->ajaxresult ['data'] = array (
					'id' => $deliveryid,
					'province' => $province,
					'city' => $city,
					'district' => $region,
					'detail' => $address,
					'personName' => $receive_person,
					'mobile' => $contact_number
			);
		} else {
			// 如果更新失败
			$this->ajaxresult ['errCode'] = 10009;
			$this->ajaxresult ['errMsg'] = "更新收货地址失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
	/**
	 * 删除顾客配送地址信息的ajax请求处理。
	 */
	public function deleteDeliveryInfo() {
		$deliveryid = I ( 'id' ); // 接收要编辑的deliveryid
		
		// 检验参数完整性
		if (empty ( $deliveryid )) {
			$this->ajaxresult ['errCode'] = 10018;
			$this->ajaxresult ['errMsg'] = "删除收货地址失败，要删除的信息编号不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
		}
		
		// 通过检验准备对数据库更新
		$deliverytable = M ( 'deliveryinfo' ); // 实例化配送信息表
		// 要更新的记录条件
		$deletemap = array (
				'deliveryinfo_id' => $deliveryid, // 要更新的主键
				'e_id' => $this->eid, // 当前商家下
				'customer_id' => $_SESSION ['currentcustomer'] ['customer_id'], // 当前顾客
				'is_del' => 0
		); 
		$deleteresult = $deliverytable->where ( $deletemap )->delete (); // 直接删除即可
		if ($deleteresult) {
			// 如果删除成功
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			// 如果删除失败
			$this->ajaxresult ['errCode'] = 10019;
			$this->ajaxresult ['errMsg'] = "删除收货地址失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前端ajax信息
	}
	
}
?>