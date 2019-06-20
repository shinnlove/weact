<?php
/**
 * 本控制器封装餐饮版块业务逻辑层经常性操作服务函数。
 * @author 赵臣升。
 * CreateTime：2014/12/16 00:09:25.
 */
class CateAction extends Action {
	
	/**
	 * 查找某企业的餐饮微商城链接地址。
	 * 特别注意：像S.Life这样的，S.Life餐饮是顶级导航菜单，进入后就是餐饮微商城主页，
	 * 但是又有点特殊，第一个版本进入后左边是微商城列表，右边是商品陈列；
	 * 第二个版本进入后直接上边是滑动的微商城列表，下边是商品陈列，
	 * 所以在餐饮版块，微商城主页和产品陈列是合并在一起的，并不像服装是分两个页面展现的，这点要注意。
	 * 在设定的时候要建议商家只设置一个餐饮导航（即一个餐饮微商城）。
	 * 
	 * 这个函数的思路是返回微商城主页（也就是商品类别列表和商品成列的合集），但是如果有多个餐饮微商城，直接返回主页。
	 * @param string $e_id 企业编号
	 * @return string $cateShopURL 返回企业的餐饮微商城地址
	 */
	public function cateShop($e_id = '') {
		$navtable = M ( 'navigation' );
		$searchnav = array(
				'e_id' => $e_id,
				'father_nav_id' => '-1', // 顶级导航下（如果是餐饮），一个导航就是一个餐饮微商城。
				'nav_type' => 4, // 餐饮类的超链接是4
				'is_del' => 0
		);
		$catenavinfo = $navtable->where( $searchnav )->select(); // 该商家下所有餐饮类别导航
		$navnum = count ( $catenavinfo ); // 统计导航个数
		if($navnum == 1) {
			$cateShopURL = U ( 'CateIndustry/MenuView/menu', array ( 'e_id' => $e_id, 'nav_id' => $catenavinfo [0] ['nav_id'] ), 'shtml', false, true );
		} else {
			$cateShopURL = U ( 'Home/Index/index', array ( 'e_id' => $e_id ), 'shtml', false, true ); // 没有餐饮微商城、有2个或以上，都跳到主页
		}
		return $cateShopURL;
	}
	
	/**
	 * -----------------餐饮部分业务逻辑-----------------
	 */
	
	/**
	 * 通过餐饮品的编号获取其视图信息（若有多张图片的话）。
	 * @param string $cate_id	餐饮的编号
	 * @param string $assemblepath	是否需要对图片组装路径
	 * @return array $viewinfo	注意返回视图信息，是二维数组
	 */
	public function getCateInfoViewById($cate_id = '', $assemblepath = FALSE) {
		$cateinfoview = array();		// 该餐饮信息视图
		if(! empty( $cate_id )) {
			$sql = 'ca.cate_id = \'' . $cate_id . '\' and ca.cate_id = ci.cate_id and ca.is_del = 0 and ci.is_del = 0';
			$model = new Model();
			$cateinfo = $model->table('t_cate ca, t_cateimage ci')->where($sql)->field('*')->select();
			if($assemblepath) {
				for($i = 0; $i < count( $cateinfo ); $i ++) {
					$cateinfo [$i] ['macro_path'] = assemblepath( $cateinfo [$i] ['macro_path'] );		// 组装路径
					if(! empty( $cateinfo [$i] ['micro_path'] )) $cateinfo [$i] ['micro_path'] = assemblepath( $cateinfo [$i] ['micro_path'] );
				}
			}
		}
		return $cateinfoview;
	}
	
	/**
	 * 通过餐饮的导航类别编号nav_id获取该导航列表下的餐饮品信息。
	 * @param string $nav_id	餐饮的类别编号
	 * @param boolean $assemblepath	是否组装图片路径，默认不组装
	 * @return array	$catelist	餐饮列表
	 */
	public function getCateListByNavId($nav_id = '', $assemblepath = FALSE) {
		$catelist = array();			// 该nav_id下的餐饮品列表$catelist
		if(! empty( $nav_id )) {
			$sql = 'ca.nav_id = \'' . $nav_id . '\' and ca.cate_id = ci.cate_id and ca.is_del = 0 and ci.is_del = 0';
			$model = new Model();
			$catelist = $model->table('t_cate ca, t_cateimage ci')->where($sql)->field('*')->select();
			if($assemblepath) {
				for($i = 0; $i < count( $catelist ); $i ++) {
					$catelist [$i] ['macro_path'] = assemblepath( $catelist [$i] ['macro_path'] );		// 组装路径
					if(! empty( $catelist [$i] ['micro_path'] )) $catelist [$i] ['micro_path'] = assemblepath( $catelist [$i] ['micro_path'] );
				}
			}
		}
		return $catelist;
	}
	
	/**
	 * -----------------餐车部分业务逻辑-----------------
	 */
	
	/**
	 * 检测某餐饮在购物车中是否存在函数。
	 * @param string $e_id	商家编号
	 * @param string $openid	用户微信号
	 * @param string $cate_id	某餐饮主键编号
	 * @return boolean|array $existinfo	该餐饮品在餐车中的存在信息，如果是false代表不存在该餐饮品
	 */
	public function checkCateInCart($e_id = '', $openid = '', $cate_id = '') {
		$existinfo = false;		// 该餐饮品在餐车中的存在信息，如果是false代表不存在该餐饮品
		$checkmap = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'cate_id' => $cate_id,
				'is_del' => 0
		);
		$cateinfo = M ( 'catecart' )->where( $checkmap )->find();
		if($cateinfo) $existinfo = $cateinfo;
		return $existinfo;
	}
	
	/**
	 * 通过商家、用户、餐饮信息更新餐车中餐饮品的数量信息。
	 * @param string $e_id	商家编号
	 * @param string $openid	用户微信编号
	 * @param string $cate_id	要更新的餐饮编号
	 * @param number $updateamount	本次要更新的数量（支持负数，负数代表减少数量）
	 * @param boolean $needreturn	需要返回更新后的数量，默认不返回（一次数据库）；如果返回操作两次数据库
	 * @return $updateresult	更新成功与否的标记，初始化为0，代表更新失败，如果更新成功，（如果需要返回值）返回更新后的数量。
	 */
	public function updateCartAmountByInfo($e_id = '', $openid = '', $cate_id = '', $updateamount = 0, $needreturn = FALSE) {
		$updateresult = 0;		// 更新成功标记兼更新后的数量
		$updatemap = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'cate_id' => $cate_id,
				'is_del' => 0
		);
		if($needreturn) {
			$needupdateinfo = M ( 'catecart' )->where( $updatemap )->find();		// 查出所需更新的记录
			$updateresult = $needupdateinfo ['amount'];								// 更新后的数量默认没变（没操作成功）
			$needupdateinfo ['amount'] += $updateamount;							// 尝试更改数量信息
			if($needupdateinfo ['amount'] > 0) {
				$updateresult = $needupdateinfo ['amount'];							// 更新数量后，值依然是正数，则确定无疑的去修改更新后返回的数量
				$needupdateinfo ['latest_modify'] = time();							// 修改更新数量时间
				M ( 'catecart' )->save($needupdateinfo);							// 保存回数据库（有主键直接存）
			}
		} else {
			if($updateamount > 0) {
				$updateresult = M ( 'catecart' )->where( $updatemap )->setInc( 'amount', $updateamount );	// 增加数量，不需要返回值
			} else {
				$updateresult = M ( 'catecart' )->where( $updatemap )->setDec( 'amount', $updateamount );	// 减少数量，不需要返回值
			}
		}
		return $updateresult;
	}
	
	/**
	 * 通过餐车主键更新餐车内餐饮品数量函数。
	 * @param string $cart_id	要更新的餐车主键编号
	 * @param number $updateamount	本次要更新的数量（支持负数，负数代表减少数量）
	 * @param boolean $needreturn	需要返回更新后的数量，默认不返回（一次数据库）；如果返回操作两次数据库
	 * @return $updateresult	更新成功与否的标记，初始化为0，代表更新失败，如果更新成功，（如果需要返回值）返回更新后的数量。
	 */
	public function updateCartAmountByCartId($cart_id = '', $updateamount = 0, $needreturn = FALSE) {
		$updateresult = 0;		// 更新成功标记兼更新后的数量
		$updatemap = array(
				'cart_id' => $cart_id,
				'is_del' => 0
		);
		if($needreturn) {
			$needupdateinfo = M ( 'catecart' )->where( $updatemap )->find();		// 查出所需更新的记录
			$updateresult = $needupdateinfo ['amount'];								// 更新后的数量默认没变（没操作成功）
			$needupdateinfo ['amount'] += $updateamount;							// 尝试更改数量信息
			if($needupdateinfo ['amount'] > 0) {
				$updateresult = $needupdateinfo ['amount'];							// 更新数量后，值依然是正数，则确定无疑的去修改更新后返回的数量
				$needupdateinfo ['latest_modify'] = time();							// 修改更新数量时间
				M ( 'catecart' )->save($needupdateinfo);							// 保存回数据库（有主键直接存）
			}
		} else {
			if($updateamount > 0) {
				$updateresult = M ( 'catecart' )->where( $updatemap )->setInc( 'amount', $updateamount );	// 增加数量，不需要返回值
			} else {
				$updateresult = M ( 'catecart' )->where( $updatemap )->setDec( 'amount', $updateamount );	// 减少数量，不需要返回值
			}
		}
		return $updateresult;
	}
	
	/**
	 * 批量向餐车中添加入餐品函数，餐品格式必须与数据库字段对应。
	 * @param string $catelist	餐品列表
	 * @return boolean $addresult	批量添加结果
	 */
	public function batchAddCart($catelist = NULL) {
		$addresult = false;		// 批量添加标志
		if(! empty( $catelist )) $addresult = M ( 'catecart' )->addAll( $catelist );	// 批量添加入餐车
		return $addresult;
	}
	
	/**
	 * 清空某商家下某用户的餐车里的所有信息。
	 * @param string $e_id	商家编号
	 * @param string $openid	用户微信编号
	 * @param string $realdelete	真删除该记录（默认假删除is_del = 1），建议删除餐车用真删除，因为记录比较多影响效率
	 * @return boolean	$clearcartsuccess	按需求清理餐车成功与否的标记
	 */
	public function clearCateCart($e_id = '', $openid = '', $realdelete = FALSE) {
		$clearcartsuccess = false;		//清理餐车成功标记
		$clearmap = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'is_del' => 0
		);
		if($realdelete) {
			$clearcartsuccess = M ( 'catecart' )->where( $clearmap )->delete();
		} else {
			$clearcartsuccess = M ( 'catecart' )->where( $clearmap )->setField( 'is_del', 1 );
		}
		return $clearcartsuccess;
	}
	
	/**
	 * 获得当前商家下当前用户的餐车及餐品信息视图。
	 * @param string $e_id	商家编号
	 * @param string $openid	顾客微信号
	 * @return array $cartlist	顾客的餐车
	 */
	public function getCartView($e_id = '', $openid = '', $formatdate = FALSE) {
		$sql = 'ct.e_id = \'' . $e_id . '\' and ct.openid = \''. $openid . '\' and ct.cate_id = ca.cate_id and ct.is_del = 0 and ca.is_del = 0';
		$model = new Model();
		$cartlist = $model->table('t_catecart ct, t_cate ca')->where($sql)->field('*')->select();
		if($formatdate) {
			for($i = 0; $i < count($cartlist); $i ++) {
				$cartlist [$i] ['add_time'] = timetodate( $cartlist [$i] ['add_time'] );	// 格式化添加入餐车的时间
				if(intval( $cartlist [$i] ['latest_modify'] ) != -1) {
					$cartlist [$i] ['latest_modify'] = timetodate( $cartlist [$i] ['latest_modify'] );	// 格式化最后一次修改时间
				}
			}
		}
		return $cartlist;
	}
	
	/**
	 * -----------------订单部分业务逻辑-----------------
	 */
	
	/**
	 * 检测某商家某用户是否有未支付的订单。
	 * 暂时未启用该函数，改用下边的函数orderSubmitPreCheck，2015/1/4。
	 * @param string $e_id 商家编号
	 * @param string $openid 用户微信编号
	 * @return boolean	$notpayexist	用户是否有存在未支付的订单，如果存在，返回true
	 */
	public function orderNotPayExist($e_id = '', $openid = '') {
		$notpayexist = false;		// 未支付订单存在
		$check = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'is_payed' => 0,		// 未曾支付的订单
				'is_del' => 0
		);
		$number = M ( 'cateordermain' )->where( $check )->count();
		if($number > 0) $notpayexist = true;
		return $notpayexist;
	}
	
	/**
	 * 检查用户在该商家能否继续提交订单的资格。
	 * 本来的考虑是有未支付的订单就不可以提交，现在考虑到先吃后付问题，把条件扩展到2个：
	 * a、订单全部已经支付；b、虽然有未支付的订单，但是也被店家接收了（线上不付钱店家是看不到订单的，这种b情况只在线下用餐时）。
	 * @param string $e_id 商家编号
	 * @param string $openid 用户微信编号
	 * @return boolean $submitPermit 提交订单的资格
	 */
	public function orderSubmitPreCheck($e_id = '', $openid = '') {
		$submitPermit = true; // 默认有提交订单的资格
		// Step1：检查有没有未支付的订单
		$precheck = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'is_payed' => 0,		// 未曾支付的订单
				'is_del' => 0
		);
		$notpayorder = M ( 'cateordermain' )->where( $precheck )->select(); // 如果不存在未支付的订单，直接就返回true了
		if($notpayorder) {
			// Step2：如果有，这些未支付的订单必须全部被店家接收，否则只要存在一笔未支付且未接受的订单，就不允许提交订单。
			for($i = 0; $i < count ( $notpayorder ); $i ++) {
				if($notpayorder [$i] ['receive_status'] == 0) {
					$submitPermit = false;
					break;
				}
			}
		}
		return $submitPermit;
	}
	
	/**
	 * 检查某笔订单能否被取消。
	 * 已经付款或被店家接收（只在线下）的订单不能取消，其他都可以。
	 * @param string $order_id 订单编号
	 * @return boolean $cancelPermit 取消订单的资格
	 */
	public function orderCancelPreCheck($order_id = '') {
		$cancelPermit = false; // 默认不可以取消订单
		if(! empty ( $order_id )) {
			$ordermap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$orderinfo = M ( 'cateordermain' )->where( $ordermap )->find();
			if($orderinfo) {
				if($orderinfo ['is_outsend'] == 0) {
					if($orderinfo ['is_payed'] == 0 && $orderinfo ['receive_status'] == 0) {
						$cancelPermit = true; // 是堂食，没有支付且没有被店家接收，可以取消，接收了或付钱了就不取消了
					}
				}else {
					if($orderinfo ['is_payed'] == 0) {
						$cancelPermit = true; // 是外卖且未支付可以取消（外卖情况的未支付代表店家看不到订单）
					}
				}
			}
		}
		return $cancelPermit;
	}
	
	/**
	 * 检查某笔订单可否被用户编辑。
	 * @param string $order_id 订单编号
	 * @return boolean $editPermit 编辑订单的资格
	 */
	public function editOrderPreCheck($order_id = '') {
		$editPermit = false; // 默认不可以编辑订单
		$condition1 = false; // 条件1：可以取消的订单可以被编辑
		$condition2 = false; // 条件2：没有选择过支付方式的订单可以被编辑
		if(! empty ( $order_id )) {
			$condition1 = $this->orderCancelPreCheck( $order_id );
			$ordermap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$orderinfo = M ( 'cateordermain' )->where( $ordermap )->find();
			if ( $orderinfo ['pay_method'] == 0 ) $condition2 = true;
			if($condition1 || $condition2) {
				$editPermit = true;
			}
		}
		return $editPermit;
	}
	
	/**
	 * 检测某笔订单是否可以被用户删除（屏蔽）。
	 * 1、订单完结态的订单可以被删除；
	 * 2、订单可以被取消就可以被删除，但是东西不退回餐车（因为是标记屏蔽）。
	 * @param string $order_id 要检查的订单编号
	 * @return boolean $cusmarkdelPermit 用户删除订单的资格
	 */
	public function cusMarkDelPreCheck($order_id = '') {
		$cusmarkdelPermit = false;
		$ordercompleted = $this->checkOrderCompleted( $order_id ); // 订单是否处于完结态
		$cancelavailable = $this->orderCancelPreCheck( $order_id ); // 订单是否可以被取消
		if($ordercompleted || $cancelavailable) {
			$cusmarkdelPermit = true;
		}
		return $cusmarkdelPermit;
	}
	
	/**
	 * 检测某笔订单是否处于完结状态（已付款（支付方式就无所谓了，已付款必然有支付方式）、店铺已经接收、已上齐餐品（必须，否则无法对账））
	 * @param string $order_id 要检查的订单编号
	 * @return boolean $iscompleted 订单是否完结
	 */
	public function checkOrderCompleted($order_id = '') {
		$iscompleted = false; // 默认订单并非完结态
		if(! empty ( $order_id )) {
			$checkcom = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$orderinfo = M ( 'cateordermain' )->where( $checkcom )->find(); // 查找出订单信息
			if($orderinfo) {
				if($orderinfo ['is_payed'] == 1 && $orderinfo ['receive_status'] == 1 && $orderinfo ['is_send'] == 1) {
					$iscompleted = true; // 满足已付款、已接收、已上齐餐品的订单才算是完结态
				}
			}
		}
		return $iscompleted;
	}
	
	/**
	 * 检测某个用户在某个商家是否存在没有备齐餐品、上餐中的订单。
	 * @param string $e_id 商家编号
	 * @param string $openid 用户微信号
	 * @param boolean|array $orderinfo|false 如果有订单信息，返回订单信息，如果没有，返回false。
	 */
	public function orderNotSendExist($e_id = '', $openid = ''){
		$notsend = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'is_send' => 0,
				'is_del' => 0
		);
		$orderinfo = M ( 'cateordermain' )->where( $notsend )->find();
		if($orderinfo) return $orderinfo;
		else
			return false;
	}
	
	/**
	 * 插入一条新订单操作。
	 * @param string $ordermain	订单主信息
	 * @param string $orderdetail	订单自信息
	 * @return boolean	$insertresult	插入一条完整新订单的结果
	 */
	public function insertNewOrder($ordermain = NULL, $orderdetail = NULL) {
		$insertresult = false;
		if(! empty( $ordermain ) && ! empty( $orderdetail )){
			$mainresult = M ( 'cateordermain' )->add($ordermain);
			$detailresult = M ('cateorderdetail')->addAll($orderdetail);
			if($mainresult && $detailresult) $insertresult = true;
		}
		return $insertresult;
	}
	
	/**
	 * 根据订单主键取消某条订单。
	 * @param string $order_id	要取消的订单主键信息
	 * @param boolean	$real_delete	是否真的要从数据库中删除数据，默认假删除，is_del = 0方便日后维护查询
	 * @param boolean $inforollback	是否要将订单中的信息回撤到餐车里，默认false不回撤
	 * @return boolean	$cancelresult	取消订单的操作结果
	 */
	public function cancelOrderById($order_id = '', $real_delete = FALSE, $inforollback = FALSE) {
		$cancelresult = false;
		if(! empty( $order_id )) {
			$orderinfo = $this->getOrderInfoById( $order_id );		// 查询出要取消的订单信息
			if(! empty( $orderinfo ) && ( $orderinfo [0] ['is_payed'] == 0 && $orderinfo [0] ['receive_status'] == 0 ) ) {
				// 如果需要回撤订单时退回餐车中，如果没有就新增；如果有就合并数量
				if($inforollback) {
					$rolladdlist = array();		// 回滚的餐饮产品新增列表
					for($i = 0, $j = 0; $i < count( $orderinfo ); $i ++) {
						// 查询本条记录在餐车中是否有
						$existinfo = $this->checkCateInCart( $orderinfo [$i] ['e_id'], $orderinfo [$i] ['openid'], $orderinfo [$i] ['cate_id'] );
						if($existinfo) {
							$this->updateCartAmountByCartId( $existinfo ['cart_id'], $orderinfo [$i] ['amount'] );	// 更新成合并后的数量（含修改更新时间）
						} else {
							$rolladdlist [$j ++] = array(
									'cart_id' => md5( uniqid( rand(), true ) ),
									'e_id' => $orderinfo [$i] ['e_id'],
									'customer_id' => $orderinfo [$i] ['customer_id'],
									'openid' => $orderinfo [$i] ['openid'],
									'cate_id' => $orderinfo [$i] ['cate_id'],
									'amount' => $orderinfo [$i] ['amount'],
									'add_time' => time(),
									'latest_modify' => ( $orderinfo [$i] ['amount'] > 1 ) ? ( time() + 1 ) : -1
							);
						}
					}
					if(! empty( $rolladdlist )) $this->batchAddCart( $rolladdlist );		// 需要添加列表不空才去添加
				}
				// 如果真删/假删
				$parentresult = false;		// 订单主表删除标记
				$childresult = false;		// 订单子表删除标记
				$delmap = array( 'order_id' => $order_id );
				if($real_delete) {
					$parentresult = M ( 'cateordermain' )->where( $delmap )->delete();		// 删主表
					$childresult = M ( 'cateorderdetail' )->where( $delmap )->delete();		// 删子表
				} else {
					$delmap ['is_del'] = 0;		// 补充参数
					$parentresult = M ( 'cateordermain' )->where( $delmap )->setField( 'is_del', 1 );	// 删主表
					$childresult = M ( 'cateorderdetail' )->where( $delmap )->setField( 'is_del', 1 );	// 删子表
				}
				if($parentresult && $childresult) $cancelresult = true;		// 删除成功确认
			}
		}
		return $cancelresult;
	}
	
	/**
	 * 通过订单的主键查询订单信息。
	 * 特别注意，这里返回的是一条订单主表、子表和餐饮详情表的三表联查视图。
	 * @param string $order_id	订单主键。
	 * @return array $orderinfo	订单的视图信息。
	 */
	public function getOrderInfoById($order_id = '', $formatdate = FALSE) {
		$sql = 'cm.order_id = cd.order_id and cd.cate_id = ca.cate_id and cm.order_id = \''. $order_id .'\' and cm.is_del = 0 and cd.is_del = 0';
		$field = 'cm.order_id, cm.e_id, cm.visual_number, cm.customer_id, cm.openid, cm.order_time, cm.total_price, cm.pay_method, cm.is_payed, cm.receive_status, cm.consume_subbranch_id, cm.consume_table_id, cd.detail_id, cd.price, cd.amount, cd.special_mark, ca.nav_id, ca.cate_id, ca.cate_name, ca.unit_name, ca.brief_description';
		$model = new Model();
		$ordercatelist = $model->table('t_cateordermain cm, t_cateorderdetail cd, t_cate ca')
		->where($sql)
		->field($field)
		->select();
		if($formatdate) {
			for($i = 0; $i < count($ordercatelist); $i ++) {
				$ordercatelist [$i] ['order_time'] = timetodate( $ordercatelist [$i] ['order_time'] );	// 格式化时间
			}
		}
		return $ordercatelist;
	}
	
	/**
	 * 通过商家编号分页获取商家所有订单主单信息（供后台easyUI读取数据用）。
	 * @param string $e_id	商家编号
	 * @param number $pagenum	第几页
	 * @param number $rowsnum	每页几条
	 * @param string $sort	按什么字段排序
	 * @param string $order	按什么方式排序，默认asc，可以传入asc/desc
	 * @param boolean $formatdate	是否需要格式化日期，默认需要格式化
	 * @return array $orderlist	订单信息列表
	 */
	public function getOrderListByPage($e_id = '', $pagenum = 1, $rowsnum = 10, $sort = 'is_del', $order = 'asc', $formatdate = TRUE) {
		$ordermap = array(
				'e_id' => $e_id,
				'is_del' => 0
		);
		$fathertable = M ('cateordermain');
		
		//现在有用户不在wechatuserinfo表中，不适合做视图，否则一连接订单没了
		//$sql = 'cm.openid = wu.openid and cm.e_id = \'' . $e_id . '\' and cm.is_del = 0 and wu.is_del = 0';
		//$field = 'cm.order_id, cm.e_id, cm.visual_number, cm.customer_id, cm.openid, cm.order_time, cm.total_price, cm.is_payed, cm.pay_indeed, cm.receive_status, cm.consume_subbranch_id, cm.consume_table_id, cm.is_send, wu.nickname';
		//$model = new Model();
		//$orderlist = $model->table('t_cateordermain cm, t_wechatuserinfo wu')->where($sql)->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->field($field)->select ();
		
		$ordercount = $fathertable->where($ordermap)->count(); // 总订单数
		$orderlist = $fathertable->where($ordermap)->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if(! empty( $orderlist )) {
			for($i = 0; $i < count( $orderlist ); $i ++) {
				if($formatdate) $orderlist [$i] ['order_time'] = timetodate( $orderlist [$i] ['order_time'] );			// 格式化时间
				// 加上用户昵称
				$usermap = array(
						'openid' => $orderlist [$i] ['openid'],
						'is_del' => 0
				);
				$wechaterinfo = M ( 'wechatuserinfo' )->where( $usermap )->find();
				$orderlist [$i] ['nick_name'] = $wechaterinfo ['nickname'];
			}
		}
		$jsoninfo = array(
				'total' => $ordercount,
				'rows' => $orderlist
		);
		return $jsoninfo;
	}
	
	/**
	 * 通过订单的主键查询订单的子信息（包括餐饮品信息），供后台post用。
	 * @param string $order_id	需要查询的订单编号
	 * @return array $detaillist	该订单的详情信息（子表）
	 */
	public function getOrderCateInfoById($order_id = '') {
		$detailcatelist = array();
		if(! empty( $order_id )) {
			$sql = 'cd.order_id = \'' . $order_id . '\' and cd.cate_id = ca.cate_id and ca.cate_id = ci.cate_id and cd.is_del = 0 and ca.is_del = 0 and ci.is_del = 0';
			$field = 'cd.detail_id, cd.order_id, cd.cate_id, cd.price, cd.amount, cd.special_mark, ca.cate_name, ca.unit_name, ca.member_price, ca.brief_description, ca.recommend_level, ca.hot_level, ca.is_new, ci.macro_path, ci.micro_path';
			$model = new Model();
			$detailcatelist = $model->table('t_cateorderdetail cd, t_cate ca, t_cateimage ci')->where($sql)->group('cd.detail_id')->field($field)->select();
			for($i = 0; $i < count( $detailcatelist ); $i ++) {
				$detailcatelist [$i] ['macro_path'] = assemblepath( $detailcatelist [$i] ['macro_path'] );		//组装路径
			}
		}
		return $detailcatelist;
	}
	
	/**
	 * 获取当前商家的某个用户的即时订单信息（未付款的）
	 * @param string $e_id	商家编号
	 * @param string $openid	用户微信号
	 * @param boolean $formatdate	缺省参数：是否需要格式化日期格式，默认false
	 * @return array $orderinfo	用户订单信息
	 */
	public function getOrderInfo($e_id = '', $openid = '', $formatdate = FALSE) {
		$sql = 'cm.order_id = cd.order_id and cd.cate_id = ca.cate_id and cm.e_id = \''. $e_id .'\' and cm.openid = \''. $openid .'\' and cm.is_payed = 0 and cm.is_del = 0 and cd.is_del = 0';
		$model = new Model();
		$ordercatelist = $model->table('t_cateordermain cm, t_cateorderdetail cd, t_cate ca')
		->where($sql)
		->field('cm.order_id, cm.e_id, cm.visual_number, cm.customer_id, cm.openid, cm.order_time, cm.total_price, cm.is_payed, cm.receive_status, cm.consume_subbranch_id, cm.consume_table_id, cd.detail_id, cd.price, cd.amount, cd.special_mark, ca.nav_id, ca.cate_id, ca.cate_name, ca.unit_name, ca.brief_description')
		->select();
		if($formatdate) {
			for($i = 0; $i < count($ordercatelist); $i ++) {
				$ordercatelist [$i] ['order_time'] = timetodate( $ordercatelist [$i] ['order_time'] );	// 格式化时间
			}
		}
		return $ordercatelist;
	}
	
	/**
	 * 获得订单主表信息（不包括子表信息）。
	 * @param string $order_id 订单编号
	 * @return array $ordermaininfo 订单主表信息（一维数组）
	 */
	public function getOrderMainInfoById($order_id = '') {
		$ordermaininfo = array(); // 订单主表信息（一维数组）
		if(! empty ( $order_id )) {
			$searchorder = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$ordermaininfo = M ( 'cateordermain' )->where( $searchorder )->find(); // 找到这条信息
		}
		return $ordermaininfo;
	}
	
	/**
	 * 为订单准备调起微信支付前的打包信息。
	 * 特别注意，进行微信支付，订单金额要乘以100转换成分。
	 * @param string $order_id 待微信支付的订单编号
	 * @return array $payinfo 订单待支付信息。
	 */
	public function prepareOrderWeChatPay($order_id = '') {
		$payinfo = array(); // 等待微信支付的信息包
		if(! empty ( $order_id )) {
			$maininfo = $this->getOrderMainInfoById ( $order_id ); // 查找订单主信息
			if($maininfo) {
				$payinfo = array(
						'e_id' => $maininfo ['e_id'],
						'openid' => $maininfo ['openid'],
						'body' => '支付餐饮订单 ' . $maininfo ['visual_number'],
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
	 * 检测某笔订单使用微信支付是否成功，可以选择性接收返回值。
	 * @param string $order_id 要检测的订单编号
	 * @return boolean $wechatpaysuccess 该笔订单经检测后微信支付是否成功状态：true|false
	 */
	public function checkWeChatPayStatus($order_id = '') {
		$wechatpaysuccess = false;
		if (! empty ( $order_id )) {
			$wechatpaysuccess = $this->checkPaySuccessNotify ( $order_id );
			if ($wechatpaysuccess) {
				// 支付检测成功的后续操作
				$orderinfo = $this->getOrderMainInfoById ( $order_id );
				$orderinfo ['is_payed'] = 1; // 订单已经支付
				$orderinfo ['pay_method'] = 2; // 支付方式是微信支付
				$orderinfo ['pay_indeed'] = $wechatpaysuccess ['total_fee'] / 100; // 实际支付了多少钱（除以100是元作为单位）
				$updateresult = M ( 'cateordermain' )->save( $orderinfo ); // 更新订单信息
			}
		}
		return $wechatpaysuccess;
	}
	
	/**
	 * 检测数据库中是否存在某笔订单的微信支付通知。
	 * 特别注意：该检测是敏感操作，请测试成功后封装使用，不要放在餐饮服务层。
	 * @param string $order_id 要坚持微信支付是否成功的订单编号
	 * @return boolean|array $wxpaysuccess 是否微信支付成功的标记或支付信息
	 */
	public function checkPaySuccessNotify($order_id = '') {
		$wxpaysuccess = false;
		if (! empty ( $order_id )) {
			$checkpay = array (
					'out_trade_no' => $order_id, // 该笔订单
					'return_code' => 'SUCCESS', // 返回码成功
					'result_code' => 'SUCCESS', // 业务结果成功
					'is_del' => 0
			);
			$wxpaysuccess = M ( 'wechatpaynotify' )->where( $checkpay )->find();
		}
		return $wxpaysuccess;
	}
	
	/**
	 * 线下门店系统接收一笔用户订单的处理函数。
	 * @param array $orderlist	要接收的订单编号数组
	 * @return boolean	$receiveresult	该笔订单接收状态
	 */
	public function subbranchOrderReceive($orderlist = NULL) {
		$receiveresult = false;		//订单成功接收标记，默认false
		if(! empty( $orderlist )){
			for($i = 0; $i < count($orderlist); $i ++){
				$receivemap = array(
						'order_id' => $orderlist [$i],
						'is_del' => 0
				);
				$receiveresult = M ( 'cateordermain' )->where( $receivemap )->setField( 'receive_status', 1 );
				if(! $receiveresult) break;
			}
		}
		return $receiveresult;
	}
	
	/**
	 * 线下门店系统现金/银行卡收讫后确认某条订单支付成功处理函数。
	 * @param string $order_id	要确认已支付的订单编号
	 * @return boolean $payedresult	订单支付成功更新的标记
	 */
	public function subbranchOrderPay($order_id = '') {
		$payedresult = false;	//订单支付成功标记，默认false
		if(! empty( $order_id )){
			$paymap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$ordertable = M ( 'cateordermain' );
			$orderinfo = $ordertable->where( $paymap )->find();	//先找出这条订单
			if($orderinfo ['pay_indeed'] == 0) {
				$orderinfo ['pay_indeed'] = $orderinfo ['total_price'];
			}
			$orderinfo ['is_payed'] = 1;						//订单已经支付
			$payedresult = $ordertable->save($orderinfo);		//将更新后的订单信息保存回数据库
		}
		return $payedresult;
	}
	
	/**
	 * 线下门店系统提醒用户订单上餐完成的处理函数。
	 * @param array $orderlist	要提醒上餐完成的订单编号数组
	 * @return boolean	$sendresult	该笔订单接收状态
	 */
	public function subbranchOrderCompleted($orderlist = NULL) {
		$sendresult = false;		//订单成功上餐完成标记，默认false
		if(! empty( $orderlist )){
			for($i = 0; $i < count($orderlist); $i ++){
				$sendmap = array(
						'order_id' => $orderlist [$i],
						'is_del' => 0
				);
				$sendresult = M ( 'cateordermain' )->where( $sendmap )->setField( 'is_send', 1 );
				if(! $sendresult) break;
			}
		}
		return $sendresult;
	}
	
	/**
	 * 通过查询条件确定订单信息（供后台easyUI读取数据用）。
	 * @param array $condition	订单查询条件
	 * @param number $pagenum	第几页
	 * @param number $rowsnum	每页几条
	 * @param string $sort	按什么字段排序
	 * @param string $order	按什么方式排序，默认asc，可以传入asc/desc
	 * @param boolean $formatdate	是否需要格式化日期，默认需要格式化
	 * @return array $orderinfo	订单信息。
	 */
	public function getOrderInfoByPageQuery($condition = NULL, $pagenum = 1, $rowsnum = 10, $sort = 'is_del', $order = 'asc', $formatdate = TRUE) {
		$fathertable = M ('cateordermain');
		$ordercount = $fathertable->where($condition)->count(); // 总订单数
		$orderlist = $fathertable->where( $condition )->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if(! empty( $orderlist )) {
			for($i = 0; $i < count( $orderlist ); $i ++) {
				if($formatdate) $orderlist [$i] ['order_time'] = timetodate( $orderlist [$i] ['order_time'] );			// 格式化时间
				// 加上用户昵称
				$usermap = array(
						'openid' => $orderlist [$i] ['openid'],
						'is_del' => 0
				);
				$wechaterinfo = M ( 'wechatuserinfo' )->where( $usermap )->find();
				$orderlist [$i] ['nick_name'] = $wechaterinfo ['nickname'];
			}
		}
		$jsoninfo = array(
				'total' => $ordercount,
				'rows' => $orderlist
		);
		return $jsoninfo;
	}
	
	/**
	 * 向用户发送点餐订单接收信息。
	 * @param array $orderlist	要提醒的订单编号
	 * @return $sendstatus	发送消息提醒成功与否。
	 */
	public function sendOrderInfo($orderlist = NULL) {
		$sendstatus = false;	//先默认没法送成功
		if(! empty( $orderlist )){
			$ordertable = M( 'cateordermain' );
			$einfo = array();		//全局e_id
			
			for($i = 0; $i < count( $orderlist ); $i ++){
				$ordermap = array(
						'order_id' => $orderlist [$i],
						'is_del' => 0
				);
				$singleorderinfo = $ordertable->where( $ordermap )->find();
				$emap = array(
						'e_id' => $singleorderinfo ['e_id'],
						'is_del' => 0
				);
				if($i == 0) {
					$einfo = M ( 'enterpriseinfo' )->where( $emap )->find();
				}
				//组装图文信息
				$tipinfo [] = array (
						"title" => '您的订单已经被接收!',
						"description" => "您的订单已经被接收，请选择支付方式。详情请点击查看。",
						"picurl" => 'http://www.we-act.cn/Updata/images/201412021712300012/cardstyle/547df4192143c.jpg',
						"url" => 'http://www.we-act.cn/weact/CateIndustry/CateOrder/orderInfo/e_id/201412021712300012/nav_id/cate00010'
				);
				import( 'Class.API.WeChatAPI.ThinkWechat', APP_PATH, '.php' );
				$weixin = new ThinkWechat( $einfo ['e_id'], $einfo ['appid'], $einfo ['appsecret'] );
				$sendstatus = $weixin->sendMsg( $tipinfo, $singleorderinfo ['openid'], 'news' );
			}
		}
		return $sendstatus;
	}
	
	/**
	 * 要通知已经缴费的订单编号。
	 * @param string $order_id	订单编号
	 * @return boolean $sendstatus	发送成功与否
	 */
	public function sendOrderPayInfo($order_id = '') {
		$sendstatus = false;		// 先默认没发送成功
		if(! empty( $order_id )) {
			$ordermap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$orderinfo = M( 'cateordermain' )->where( $ordermap )->find();
			$emap = array(
					'e_id' => $orderinfo ['e_id'],
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where( $emap )->find();
			$remindinfo = '您的订单已支付。';
			//发送消息
			import( 'Class.API.WeChatAPI.ThinkWechat', APP_PATH, '.php' );
			$weixin = new ThinkWechat( $einfo ['e_id'], $einfo ['appid'], $einfo ['appsecret'] );
			$sendstatus = $weixin->sendMsg( $remindinfo, $orderinfo ['openid'], 'text' );
		}
		return $sendstatus;
	}
	
	/**
	 * 向用户发送餐品已上齐的函数。
	 * @param array $orderlist	要提醒的订单编号
	 * @return $sendstatus	发送消息提醒成功与否。
	 */
	public function sendOrderCompletedInfo($orderlist = NULL) {
		$sendstatus = false;	//先默认没法送成功
		if(! empty( $orderlist )){
			$ordertable = M( 'cateordermain' );
			$einfo = array();		//全局e_id
				
			for($i = 0; $i < count( $orderlist ); $i ++){
				$ordermap = array(
						'order_id' => $orderlist [$i],
						'is_del' => 0
				);
				$singleorderinfo = $ordertable->where( $ordermap )->find();
				$emap = array(
						'e_id' => $singleorderinfo ['e_id'],
						'is_del' => 0
				);
				if($i == 0) {
					$einfo = M ( 'enterpriseinfo' )->where( $emap )->find();
				}
				// 发送提醒信息
				$tipinfo = '您的餐品已上齐，请慢用。';
				import( 'Class.API.WeChatAPI.ThinkWechat', APP_PATH, '.php' );
				$weixin = new ThinkWechat( $einfo ['e_id'], $einfo ['appid'], $einfo ['appsecret'] );
				$sendstatus = $weixin->sendMsg( $tipinfo, $singleorderinfo ['openid'], 'text' );
			}
		}
		return $sendstatus;
	}
	
	/**
	 * 设置某订单的入座桌号。
	 * @param string $order_id 要设置的订单号
	 * @param string $table_id 要设置的桌子号
	 * @return boolean $setresult 设置桌子号是否成功
	 */
	public function setTable($order_id = '', $table_id = ''){
		$setresult = false;		// 默认设置不成功
		if(! empty( $table_id )) {
			$setmap = array(
					'order_id' => $order_id,
					'is_del' => 0,
			);
			$setresult = M ( 'cateordermain' )->where( $setmap )->setField( 'consume_table_id', $table_id );
		}
		return $setresult;
	}
	
	/**
	 * 检测扫描桌号后系统应该如何智能识别桌号用来做什么函数。
	 * 顾客扫描桌子上二维码，会是如下的2种情况：
	 * 1、无订单状态、想绑定/更换桌子的：a)顾客确实想点单，并且从未扫描过二维码的、b)顾客已经点了单，还未提交订单；
	 * 2、有订单状态、想绑定/更换桌子的：顾客已经提交订单。
	 * 对于无订单状态，如果扫码，反复在内存中更新最新的桌子编号；
	 * 对于有订单状态，鉴于订单有三种状态信息：1、店铺确认接收；2、是否支付；3、上餐状况。
	 * 需要考虑到实际订单情况：
	 * a)、店铺刚接收，顾客想换桌，提交了订单也可能存在换桌；
	 * b)、顾客刚刚微信支付，还没上菜想换桌，支付后也可能存在换桌；
	 * c)、上餐齐了(is_send == 1)，想换桌，这种情况不切实际。
	 * 所以，应该以店铺备餐状况来判断是否可以换桌。
	 * 综上所述，处理方式：
	 * 1、对于有订单状态的a/b且非c，直接把当前的桌子编号存入内存，并更新订单表上的餐桌编号；
	 * 2、对于有订单状态的c，属于一笔订单的完结态，因此回到无订单状态下，周而复始进行第二次点餐。
	 * 代码逻辑简化：
	 * 拿着桌子编号和扫描用户微信号，检查用户是否有订单。若有且非c，更新内存+更新订单；如果没有，仅仅更新内存即可。
	 * 
	 * @param string $e_id 商家编号
	 * @params string $openid 用户微信号
	 * @params string $table_id 桌子编号
	 * @return string $table_id 返回被扫描的桌子编号
	 */
	public function checkScanTable($e_id = '', $openid = '', $table_id = ''){
		$checkok = true;	// 默认检查扫描桌子OK
		$notsendexist = $this->orderNotSendExist( $e_id, $openid );		// 检测是否存在备餐中的订单
		if($notsendexist) $checkok = $this->setTable( $notsendexist ['order_id'], $table_id );		// 如果有这样的订单
		return $checkok;
	}
	
	/**
	 * 获得用户历史订单记录（用户标注删除的订单不显示）。
	 * 现在是直接查询所有历史订单，以后要做到分页瀑布流查询。
	 * @param string $e_id 商家编号
	 * @param string $openid 用户编号
	 * @return array $orderlist 历史订单记录
	 */
	public function getHistoryOrder($e_id = '', $openid = '') {
		// 第一步，查询主订单
		$mainmap = array(
				'e_id' => $e_id,
				'openid' => $openid,
				'cus_mark_del' => 0, // 顾客标记删除的订单不予显示，但是在系统中还是存在
				'is_del' => 0
		);
		$historyorder = M ( 'cateordermain' )->where( $mainmap )->order( 'order_time desc' )->select(); // 按时间降序排列查出主订单
		$ordernum = count ( $historyorder ); // 建立订单总数临时变量$ordernum
		// 第二步，按年月排列，并格式化时间
		$yearmonthlist = array(); // 年月数组
		for($i = 0; $i < $ordernum; $i ++) {
			$year = date('Y', $historyorder [$i] ['order_time']);
			$month = date('m', $historyorder [$i] ['order_time']);
			$yearandmonth = $year . $month;
			if(! in_array ( $yearandmonth, $yearmonthlist )) array_push ( $yearmonthlist, $yearandmonth );
			//$historyorder [$i] ['order_time'] = timetodate ( $historyorder [$i] ['order_time'] ); // 后边还要用到time，暂时先不在这里格式化日期
		}
		// 第三步，最终格式化订单数据按年月先建立索引
		$orderformat = array(); // 被格式化的订单数组
		for($i = 0; $i < count ( $yearmonthlist ); $i ++) {
			$orderformat [ $yearmonthlist [$i] ] = array(); // 每一个月份就开辟年月key
		}
		// 第四步，订单子表与餐饮表进行视图查询
		for($i = 0; $i < $ordernum; $i ++) {
			$sql = 'cd.cate_id = ca.cate_id and cd.order_id = \'' . $historyorder [$i] ['order_id'] . '\' and cd.is_del = 0';
			$field = 'cd.price, cd.amount, cd.special_mark, ca.cate_id, ca.nav_id, ca.cate_name, ca.cate_type, ca.unit, ca.unit_name';
			$model = new Model();
			$detailinfo = $model->table( 't_cateorderdetail cd, t_cate ca' )->where( $sql )->field( $field )->select();
			$historyorder [$i] ['detaillist'] = $detailinfo;
			$historyorder [$i] ['detailcount'] = count ( $detailinfo );
		}
		// 第五步，对订单分拣入年月数组
		for($i = 0; $i < $ordernum; $i ++) {
			$currenttime = $historyorder [$i] ['order_time']; // 当前订单的日期
			$currentyear = date( 'Y', $currenttime );
			$currentmonth = date( 'm', $currenttime );
			$currentkey = $currentyear . $currentmonth;
			$historyorder [$i] ['order_time'] = timetodate ( $historyorder [$i] ['order_time'] ); // 在这里格式化时间
			array_push( $orderformat [ $currentkey ] , $historyorder [$i] );
		}
		// 第六步，对订单分组进行数量统计（方便循环）
		foreach ($orderformat as $key => $value) {
			$orderformat [$key] ['ordercount'] = count ( $orderformat [$key] );
			$orderformat [$key] ['yeargroup'] = substr( $key, 0, 4 ); // 年分组
			$orderformat [$key] ['monthgroup'] = substr( $key, 4, 2 ); // 月分组
		}
		return $orderformat;
	}
	
	/**
	 * 用户根据订单编号删除订单（做屏蔽处理，但并不真正删除订单）。
	 * @param string $e_id 企业编号
	 * @param string $openid 用户微信号（查询该用户还剩下多少没有屏蔽的订单）
	 * @param string $order_id 订单编号
	 * @return number $ordernumberleft 删除后该顾客还有的订单数。
	 */
	public function delOrderById($e_id = '', $openid = '', $order_id = '') {
		$ordernumberleft = -1; // 如果是返回-1，说明删除订单并没有成功
		if(! empty ( $order_id )) {
			$father_table = M ( 'cateordermain' );
			//$child_table = M ( 'cateorderdetail' );
			$delmap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$delmainresult = $father_table->where( $delmap )->setField( 'cus_mark_del', 1 ); // 处理主表就可以了，子表也索引不到
			//$deldetailresult = $child_table->where( $delmap )->setField( 'is_del', 1 ); // 暂时不必
			//if( $delmainresult && $deldetailresult ) {
			if( $delmainresult ) {
				$checkmap = array(
						'e_id' => $e_id,
						'openid' => $openid,
						'cus_mark_del' => 0, // 还没有被用户屏蔽删除的订单
						'is_del' => 0
				);
				$ordernumberleft = $father_table->where( $checkmap )->count();
			}
		}
		return $ordernumberleft;
	}
	
	/**
	 * 为餐饮订单选择一种支付方式。
	 * @param string $order_id 订单百年好
	 * @param number $method 支付方式，默认0是未选择，1是现金或刷卡支付，2是微信支付
	 * @return boolean $methodconfirm 订单的支付确认
	 */
	public function orderPayMethod($order_id = '', $method = 0) {
		$methodconfirm = false;
		if(! empty ( $order_id )) {
			$setmap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$methodconfirm = M ( 'cateordermain' )->where( $setmap )->setField( 'pay_method', $method );
		}
		return $methodconfirm;
	}
	
	/**
	 * 备注订单（分店系统用）。
	 * @param string $order_id 订单编号
	 * @param string $remark 订单备注内容
	 * @return boolean $remarkOperated 订单备注操作结果
	 */
	public function remarkOrder($order_id = '', $remark = '') {
		$remarkOperated = false;
		if(! empty ( $order_id )) {
			$remarkmap = array(
					'order_id' => $order_id,
					'is_del' => 0
			);
			$remarkOperated = M ( 'cateordermain' )->where( $remarkmap )->setField( 'remark', $remark );
		}
		return $remarkOperated;
	}
}
?>