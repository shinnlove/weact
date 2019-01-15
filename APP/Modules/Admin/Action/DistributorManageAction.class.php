<?php
/**
 * 正式分销商管理管理控制器。
 * @author 赵臣升
 * CreateTime:2015/02/26 17:23:36.
 */
class DistributorManageAction extends Action {
	/**
	 * 分销商申请视图。
	 */
	public function distributorApply() {
		$this->display();
	}
	
	/**
	 * ajax进行post获取分销商申请列表。
	 */
	public function getAllDistributorApply() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'apply_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$dismap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$disinfoview = M ( 'distributor_applyinfo' ); // 定义视图，该视图由distributor表和distributorinfo表联查而成
		$disinfolist = array (); // 分销商信息数组
		
		$distotal = $disinfoview->where ( $dismap )->count (); // 计算当前商家品牌下的分销商总数
		
		if ($distotal) {
			$disinfolist = $disinfoview->where ( $dismap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $disinfolist ); $i ++) {
				$disinfolist [$i] ['apply_time'] = timetodate ( $disinfolist [$i] ['add_time'] ); // 分销商加盟时间转为可视化
				$disinfolist [$i] ['approve_time'] = timetodate ( $disinfolist [$i] ['approve_time'] ); // 分销商加盟时间转为可视化
			}
		}
		$json = '{"total":' . $distotal . ',"rows":' . json_encode ( $disinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 正式分销商视图。
	 */
	public function distributorView() {
		$this->display();
	}
	
	/**
	 * 分销商一览页面的easyUI获取所有分销商功能。
	 */
	public function getAllDistributor() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$dismap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$disinfoview = M ( 'distributor_shopinfo' ); // 定义视图，该视图由distributor表和distributorinfo表联查而成
		$disinfolist = array (); // 分销商信息数组
		
		$distotal = $disinfoview->where ( $dismap )->count (); // 计算当前商家品牌下的分销商总数
		if ($distotal) {
			$disinfolist = $disinfoview->where ( $dismap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $disinfolist ); $i ++) {
				$disinfolist [$i] ['add_time'] = timetodate ( $disinfolist [$i] ['add_time'] ); // 分销商加盟时间转为可视化
				$disinfolist [$i] ['shop_logo'] = assemblepath ( $disinfolist [$i] ['shop_logo'] ); // 分销商加盟分店logo组装路径
			}
		}
		$json = '{"total":' . $distotal . ',"rows":' . json_encode ( $disinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 查看代理导航下的商品
	 */
	public function distributorProduct() {
		$p2pnavid=I('p2pnavid');
		$allpromap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$this->navlist = M ( 'product_image' )->where( $allpromap )->Distinct (true)->field ( 'nav_name' )->select ();
		$this->p2pnavid = $p2pnavid;
		$this->display();
	}
	
	/**
	 * 得到分销商的详情信息。
	 * 包括：1、店铺销售业绩；2、上级分销商；3、下级分销商。
	 */
	public function getDistributorDetail() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
		
		// 接收页面发来的参数
		$superiorid = I ( 'sid' ); // 接收上级分销店编号
		$selfid = I ( 'did' ); // 接收自身分销店编号
		
		// 查询店铺自身业绩
		$saleinfo = $this->saleInfo ( $selfid );
		
		// 查询上下级信息
		$superiorinfo = $this->superiorDetailInfo ( $superiorid ); // 查询上级、上上级信息
		$subordinateinfo = $this->subordinateDetailInfo ( $selfid ); // 查询下级、下下级信息
		
		// 打包信息：1、业绩信息；2、上级信息；3、下级信息。
		$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => array (
						'saleinfo' => $saleinfo, 
						'superiorinfo' => $superiorinfo, 
						'subordinateinfo' => $subordinateinfo 
				)
		);
		// 返回给前台
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 查询分销商店铺业绩。
	 * @param string $distributor_id 分销商编号
	 * @return array $distributorinfo 分销商信息
	 */
	public function saleInfo($distributor_id = '') {
		$saleinfo = array ();
		if (! empty ( $distributor_id )) {
			
		}
		return $saleinfo;
	}
	
	/**
	 * 根据自己的上级编号查询上级、上上级分销商详情信息。
	 * @param string $superior_id 上级分销商编号
	 * @return array $superiorinfo 上级、上上级分销商信息
	 */
	public function superiorDetailInfo($superior_id = '') {
		$superiorinfo = array (); // 上级、上上级信息数组
		if (! empty ( $superior_id )) {
			$superiormap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'p_distributor_id' => $superior_id, // parent的distributor_id就是自己的superior_id
					'is_del' => 0
			);
			$superiorlist = M ( 'distributor_superior' )->where ( $superiormap )->find (); // 从视图中查询上级列表信息
			if ($superiorlist) {
				// 进入此if则至少有上级parent，开始筛选parent的信息
				$superiorinfo ['parent'] = array (
						'e_id' => $superiorlist ['e_id'],
						'chain_group_id' => $superiorlist ['chain_group_id'],
						'distributor_id' => $superiorlist ['p_distributor_id'],
						'customer_id' => $superiorlist ['p_customer_id'],
						'superior_id' => $superiorlist ['p_superior_id'],
						'chain_level' => $superiorlist ['p_chain_level'],
						'open_status' => $superiorlist ['p_open_status'],
						'add_time' => timetodate ( $superiorlist ['p_add_time'] ), // 顺带格式化时间
						'shop_name' => $superiorlist ['p_shop_name'],
						'shop_notice' => $superiorlist ['p_shop_notice'],
						'shop_manager' => $superiorlist ['p_shop_manager'],
						'contact_number' => $superiorlist ['p_contact_number'],
						'shop_logo' => assemblepath ( $superiorlist ['p_shop_logo'] ), // 顺带组装图片路径
						'remark' => $superiorlist ['p_remark'],
				);
				if (! empty ( $superiorlist ['gp_distributor_id'] )) {
					// 进入此if则还有上上级grandparent，开始筛选grandparent的信息
					$superiorinfo ['grandparent'] = array (
							'e_id' => $superiorlist ['e_id'],
							'chain_group_id' => $superiorlist ['chain_group_id'],
							'distributor_id' => $superiorlist ['gp_distributor_id'],
							'customer_id' => $superiorlist ['gp_customer_id'],
							'superior_id' => $superiorlist ['gp_superior_id'],
							'chain_level' => $superiorlist ['gp_chain_level'],
							'open_status' => $superiorlist ['gp_open_status'],
							'add_time' => timetodate ( $superiorlist ['gp_add_time'] ), // 顺带格式化时间
							'shop_name' => $superiorlist ['gp_shop_name'],
							'shop_notice' => $superiorlist ['gp_shop_notice'],
							'shop_manager' => $superiorlist ['gp_shop_manager'],
							'contact_number' => $superiorlist ['gp_contact_number'],
							'shop_logo' => assemblepath ( $superiorlist ['gp_shop_logo'] ), // 顺带组装图片路径
							'remark' => $superiorlist ['gp_remark'],
					);
				}
			}
		}
		return $superiorinfo;
	}
	
	/**
	 * 将自己的分销编号作为别人的上级，查询自己的下级、下下级分销商详情信息。
	 * @param string $self_id 自身分销编号
	 * @return array $subordinateinfo 下级、下下级分销商信息
	 */
	public function subordinateDetailInfo($self_id = '') {
		$subordinateinfo = array (); // 下级、下下级信息数组
		if (! empty ( $self_id )) {
			$subordinatemap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'c_superior_id' => $self_id, // 自己的distributor_id就是child的superior_id
					'is_del' => 0
			);
			$subordinatelist = M ( 'distributor_subordinate' )->where ( $subordinatemap )->find (); // 从视图中查询信息
			if ($subordinatelist) {
				// 进入此if则至少有下级child，开始筛选child的信息
				$subordinateinfo ['child'] = array (
						'e_id' => $subordinatelist ['e_id'],
						'chain_group_id' => $subordinatelist ['chain_group_id'],
						'distributor_id' => $subordinatelist ['c_distributor_id'],
						'customer_id' => $subordinatelist ['c_customer_id'],
						'superior_id' => $subordinatelist ['c_superior_id'],
						'chain_level' => $subordinatelist ['c_chain_level'],
						'open_status' => $subordinatelist ['c_open_status'],
						'add_time' => timetodate ( $subordinatelist ['c_add_time'] ), // 顺带格式化时间
						'shop_name' => $subordinatelist ['c_shop_name'],
						'shop_notice' => $subordinatelist ['c_shop_notice'],
						'shop_manager' => $subordinatelist ['c_shop_manager'],
						'contact_number' => $subordinatelist ['c_contact_number'],
						'shop_logo' => assemblepath ( $subordinatelist ['c_shop_logo'] ), // 顺带组装图片路径
						'remark' => $subordinatelist ['c_remark'],
				);
				if (! empty ( $subordinatelist ['gc_distributor_id'] )) {
					// 进入此if则还有上上级grandparent，开始筛选grandparent的信息
					$subordinateinfo ['grandchild'] = array (
							'e_id' => $subordinatelist ['e_id'],
							'chain_group_id' => $subordinatelist ['chain_group_id'],
							'distributor_id' => $subordinatelist ['gc_distributor_id'],
							'customer_id' => $subordinatelist ['gc_customer_id'],
							'superior_id' => $subordinatelist ['gc_superior_id'],
							'chain_level' => $subordinatelist ['gc_chain_level'],
							'open_status' => $subordinatelist ['gc_open_status'],
							'add_time' => timetodate ( $subordinatelist ['gc_add_time'] ), // 顺带格式化时间
							'shop_name' => $subordinatelist ['gc_shop_name'],
							'shop_notice' => $subordinatelist ['gc_shop_notice'],
							'shop_manager' => $subordinatelist ['gc_shop_manager'],
							'contact_number' => $subordinatelist ['gc_contact_number'],
							'shop_logo' => assemblepath ( $subordinatelist ['gc_shop_logo'] ), // 顺带组装图片路径
							'remark' => $subordinatelist ['gc_remark'],
					);
				}
			}
		}
		return $subordinateinfo;
	}
	
	/**
	 * 增加分销导航视图页面。
	 */
	public function addDistributorNav() {
		$this->display ();
	}
	
	/**
	 * 编辑分销导航视图页面。
	 */
	public function editDistributorNav() {
		$navid = I ( 'navid' );
		if (empty ( $navid )) {
			$this->error ( "所编辑的分销导航编号不能为空。" );
		}
		// 查询分销导航
		$navmap = array (
				'nav_id' => $navid, // 导航编号
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 商家编号
				'is_del' => 0, // 没有被删除的
		);
		$navinfo = M ( 'distributornav' )->where ( $navmap )->find (); // 找到分销导航
		$navinfo ['image_path'] = assemblepath ( $navinfo ['image_path'] ); // 组装图片路径
		if (! $navinfo) {
			$this->error ( "所编辑的分销导航不存在。" );
		}
		$this->navinfo = $navinfo; 
		$this->display ();
	}
	
	/**
	 * 分销导航一览视图
	 */
	public function distributorNav() {
		$this->display ();
	}
	
}
?>