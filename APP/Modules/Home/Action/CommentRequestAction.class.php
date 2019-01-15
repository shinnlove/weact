<?php
/**
 * 导购商品评论提交控制器。
 * @author 赵臣升。
 * CreateTime:2015/06/04 16:03:25.
 */
class CommentRequestAction extends MobileLoginRequestAction {
	
	/**
	 * 导购商品评论提交的ajax处理函数。
	 */
	public function guideProductConfirm() {
		$order_id = I ( 'oid', '' ); 					// 接收订单编号
		$gslist = stripslashes ( &$_POST ['gslist'] ); 	// 接收导购评价信息
		$pslist = stripslashes ( &$_POST ['pslist'] ); 	// 接收商品评价信息
		
		$guidescorelist = json_decode ( $gslist, true );
		$productscorelist = json_decode ( $pslist, true );
		
		// 提交参数的检验
		if (empty ( $order_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "评论失败，订单编号参数错误！";
			$this->ajaxReturn ( $this->ajaxresult ); 	// 返回给前台信息
		}
		if (empty ( $productscorelist )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "评论失败，评论信息不能为空！";
			$this->ajaxReturn ( $this->ajaxresult ); 	// 返回给前台信息
		}
		
		// 查询订单是否存在（如果评价成功，要将订单置为已评价）
		$maintable = M ( "ordermain" ); 	// 实例化订单主表
		$ordermap = array (
				'order_id' => $order_id, 	// 订单编号
				'is_del' => 0, 				// 没有被删除的
		);
		$maininfo = $maintable->where ( $ordermap )->find (); // 找到订单主信息
		if (! $maininfo) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "评论失败，所评价订单信息不存在，请及时刷新！";
			$this->ajaxReturn ( $this->ajaxresult ); 	// 返回给前台信息
		}
		// 判断订单是否可以评价
		if ($maininfo ['status_flag'] == 0) {
			// 正常流程中，处于已签收|待评价状态，未签收不能评价
			if ($maininfo ['normal_status'] < 3) {
				$this->ajaxresult ['errCode'] = 10005;
				$this->ajaxresult ['errMsg'] = "请先确认收货再评价订单！";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
			// 正常流程中，处于已评价状态，不能重新评价
			if ($maininfo ['normal_status'] >= 4) {
				$this->ajaxresult ['errCode'] = 10006;
				$this->ajaxresult ['errMsg'] = "您已评价过该订单，无需重复评价";
				$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
			}
		} else if ($maininfo ['status_flag'] == 1) {
			// 退款流程中的订单不能评价
			$this->ajaxresult ['errCode'] = 10007;
			$this->ajaxresult ['errMsg'] = "订单处于退款流程中，无法评价！";
			$this->ajaxReturn ( $this->ajaxresult ); // ajax返回给前台信息
		}
		
		// 评价准备工作
		$gctable = M ( "guidecomment" ); 				// 导购评论表
		$pctable = M ( "productcomment" ); 				// 商品评论表
		$statustable = M ( "orderstatusrecord" ); 		// 实例化订单流水表
		$customer_id = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id']; 		// 当前顾客编号
		$guidecomment = array (); 						// 导购评论
		$productcomment = array (); 					// 商品评论
		$guidecount = count ( $guidescorelist ); 		// 导购数量
		$productcount = count ( $productscorelist ); 	// 商品数量
		$addgcresult = false; 							// 添加导购评价是否成功
		$addpcresult = false; 							// 添加商品评价是否成功
		$commentsuccess = false; 						// 更新订单评价状态标记
	
		// 拼接导购评论
		for($i = 0; $i < $guidecount; $i ++) {
			$singlegc = array (
					'comment_id' => md5 ( uniqid ( rand (), true ) ), 		// 生成主键
					'guide_id' => $guidescorelist [$i] ['id'], 				// 导购编号
					'customer_id' => $customer_id, 							// 当前顾客
					'star_level' => $guidescorelist [$i] ['score'], 		// 导购得分
					'content' => $guidescorelist [$i] ['content'], 			// 评价内容
					'comment_time' => time (),
			);
			array_push ( $guidecomment, $singlegc ); 						// 压栈导购评论
		}
		
		// 拼接商品评论
		for($i = 0; $i < $productcount; $i ++){
			$singlepc = array (
					'pro_comment_id' => md5 ( uniqid ( rand (), true ) ), 	// 生成主键
					'e_id' => $this->einfo ['e_id'], 						// 当前商家编号
					'subbranch_id' => $productscorelist [$i] ['sid'], 		// 导购编号
					'customer_id' => $customer_id, 							// 当前顾客
					'order_id' => $order_id, 								// 当前评价所属订单
					'product_id' => $productscorelist [$i] ['id'], 			// 当前商品
					'content' => $productscorelist [$i] ['content'], 		// 评价内容
					'star_level' => $productscorelist [$i] ['score'], 		// 商品得分
					'comment_time' => time (),
			);
			array_push ( $productcomment, $singlepc ); 						// 压栈商品评论
		}
		
		// 事务过程添加两种评论
		$gctable->startTrans (); // 开启事务过程
		// Step1：添加导购评论
		if ($guidecount) {
			$addgcresult = $gctable->addAll ( $guidecomment ); 				// 一次性插入评价
		} else {
			$addgcresult = true; 											// 没有导购直接默认第一步成功
		}
		// Step2：添加商品评论（必做）
		$addpcresult = $pctable->addAll ( $productcomment ); 				// 添加商品评论
		// Step3：更新订单评论状态（2015/08/24 17:06:25更改订单评论状态，从is_appraised到status更改和插入流水表）
		$statusupdate = array (
				'status_flag' => 0, 				// 正常态
				'normal_status' => 4, 				// 更新为已评价状态（正常态4）
		);
		$commentsuccess = $maintable->where ( $ordermap )->save ( $statusupdate ); // 保存订单的评价状态
		
		// Step4：插入订单流水
		$refundstatus = $maininfo ['refund_status']; 						// 沿用原来订单的退款状态
		$statusinfo = array (
				'record_id' => md5 ( uniqid ( rand (), true ) ), 			// 流水表主键
				'e_id' => $this->einfo ['e_id'], 							// 当前商家
				'mall_type' => $maininfo ['mall_type'], 					// 订单来自哪个商城就是哪个商城的（特别注意云总店可以操作所有订单！！！2015/08/24 17:16:25）
				'order_id' => $order_id, 									// 这笔订单的主单编号
				'status_flag' => 0, 										// 订单处于正常流水状态（2.0版本新增订单流水状态）
				'normal_status' => 4, 										// 正常态0代表订单代付款（2.0版本新增订单流水状态）
				'refund_status' => $refundstatus, 							// 正常态的订单无退款异常（2.0版本新增订单流水状态）
				'add_time' => time (), 										// 订单流水添加的时间
				'remark' => "顾客在" . timetodate ( time () ) . "签收订单，记录订单流水。", // 订单流水原因
		);
		$addstatusresult = $statustable->add ( $statusinfo ); 				// 插入订单流水表
		
		// 根据结果返回信息
		if ($addgcresult && $addpcresult && $commentsuccess && $addstatusresult) {
			$gctable->commit (); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$gctable->rollback (); // 回滚事务
			$this->ajaxresult ['errCode'] = 10008;
			$this->ajaxresult ['errMsg'] = "评论失败，评论系统繁忙，请稍后再试！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
}
?>