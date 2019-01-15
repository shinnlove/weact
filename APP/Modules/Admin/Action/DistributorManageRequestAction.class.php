<?php
/**
 * 分销管理请求处理控制器。
 * @author 万路康，赵臣升。
 * CreateTime:2015/08/06 15:01:25.
 */
class DistributorManageRequestAction extends PCRequestLoginAction {
	/**
	 * 分销导航显示
	 */
	public function getAllDistributor() {
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$navmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id
				'is_del' => 0
		);
		
		$navinfoview = M ( 'distributornav' ); // 定义表
		$navinfolist = array (); // 商品信息数组
		
		$navtotal = $navinfoview->where ( $navmap )->count (); // 计算当前商家下的商品总数
		
		if ($navtotal) {
			$navinfolist = $navinfoview->where ( $navmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
		
			for($i = 0; $i < count ( $navinfolist ); $i ++) {
				$navinfolist [$i] ['add_time'] = timetodate ( $navinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$navinfolist [$i] ['latest_modify'] = timetodate ( $navinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$navinfolist [$i] ['image_path'] = assemblepath ( $navinfolist [$i] ['image_path'] ); // 特别注意：商品大图拼接路径
			} 
		}
		//$navinfolist = $this->checkProductSkuWarn ( $navinfolist ); // 检查库存是否报警
		$json = '{"total":' . $navtotal . ',"rows":' . json_encode ( $navinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 删除分销导航
	 */
	public function deleteNavConfirm() {
		$deloidlist = I ( 'navidlist' );
		$deloidarray=explode(',', $deloidlist);
		
		$delomap = array(
				'nav_id' => array('in',$deloidarray), 
				'is_del' => 0
		);
		
		$delp2pmap = array(
				'p2pnav_id' => array('in',$deloidarray),
				'is_del' => 0
		);
		
		$nav=M('distributornav');
		$pro=M('product');
		$nav->startTrans();//开始事物
		
		$deloresult1 = $nav->where($delomap)->delete();//删除distributornav表中记录
		$deloresult2=true;
		$procount=$pro->where($delp2pmap)->count();
		if($procount){
			$deloresult2 = $pro->where($delp2pmap)->setField('p2pnav_id', '-1');//移除该导航下的商品
		}
		if ($deloresult1 && $deloresult2 ){
			$nav->commit(); // 提交事务
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = 'ok';
		}else{
			$nav->rollback(); // 事务回滚
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = '信息删除失败，请检查网络状况!';
		} 
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 确认添加分销导航。
	 */
	public function addNavConfirm() {
		$receivedata = array (
				'chinese_name' => I ( 'cname' ),
				'english_name' => I ( 'ename' ),
				'image_path' => I ( 'navimg' ),
				'first_benefit_rate' => I ( 'firstrate' ),
				'second_benefit_rate' => I ( 'secondrate' ),
				'third_benefit_rate' => I ( 'thirdrate' ),
				'fourth_benefit_rate' => I ( 'fourthrate' ),
				'sibling_order' => I ( 'navorder' ),
				'visible_level' => I ( 'visible' ),
				'sn_code' => I ( 'sncode' ),
				'agent_open' => I ( 'agentopen' ), 
		);
		$addnavinfo = array (
				'nav_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'chinese_name' => $receivedata ['chinese_name'],
				'english_name' => $receivedata ['english_name'],
				'image_path' => $receivedata ['image_path'],
				'first_benefit_rate' => $receivedata ['first_benefit_rate'],
				'second_benefit_rate' => $receivedata ['second_benefit_rate'],
				'third_benefit_rate' => $receivedata ['third_benefit_rate'],
				'fourth_benefit_rate' => $receivedata ['fourth_benefit_rate'],
				'sibling_order' => $receivedata ['sibling_order'],
				'visible_level' => $receivedata ['visible_level'],
				'sn_code' => $receivedata ['sn_code'],
				'agent_open' => $receivedata ['agent_open'],
				'add_time' => time (),  
		);
		$addresult = M ( 'distributornav' )->add ( $addnavinfo ); // 添加导航
		if ($addresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "添加分销导航失败，请不要频繁提交。";
		}
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 根据不同情形、精确或模糊地根据查找条件查找商品，加强用户体验度。
	 * 如：商品条形码，商品名称等进行模糊查询；
	 * 导航进行精确查询；库存量等用区间查询。
	 */
	public function searchProduct() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'latest_modify'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); // 接收查询条件
		$content = I ( 'searchcontent' );
	
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'p2pnav_id' => $_REQUEST['p2pnavid'],
				'is_del' => 0
		);
		if ($condition == "product_number" || $condition == "product_name") {
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		} else if ($condition == "storage_amount") {
			$searchmap [$condition] = array ( 'elt', $content );
		} else if ($condition == "sell_amount") {
			$searchmap [$condition] = array ( 'egt', $content );
		} else if ($condition == "nav_name") {
			if ($content != -1) $searchmap [$condition] = $content; // 搜全部，不限制类别
		}
	
		$proinfoview = M ( 'product_image' ); // 定义视图，该视图由商品表和导航类别表连接而成，2015/05/02修改
		$proinfolist = array (); // 商品信息数组
	
		$prototal = $proinfoview->where ( $searchmap )->count (); // 计算当前商家下的商品总数
		
		if ($prototal) {
			$proinfolist = $proinfoview->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $proinfolist ); $i ++) {
				$proinfolist [$i] ['add_time'] = timetodate ( $proinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$proinfolist [$i] ['latest_modify'] = timetodate ( $proinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$proinfolist [$i] ['onshelf_time'] = timetodate ( $proinfolist [$i] ['onshelf_time'] ); // 商品上架时间转为可视化
				$proinfolist [$i] ['macro_path'] = assemblepath ( $proinfolist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$proinfolist [$i] ['micro_path'] = assemblepath ( $proinfolist [$i] ['micro_path'] ); // 特别注意：商品小图拼接路径
			}
		}
		//$proinfolist = $this->checkProductSkuWarn ( $proinfolist ); // 检查库存是否报警
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}	
	
	/**
	 * 代理导航下的商品显示
	 */	
	public function getDistributorProduct(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0,
				'p2pnav_id' => $_REQUEST['p2pnavid']
		);
		$navproinfoview = M ( 'product_image' ); 
		$navproinfolist = array (); // 商品信息数组
		
		$navprototal = $navproinfoview->where ( $promap )->count (); // 计算当前商家下的商品总数
		if ($navprototal) {
			$navproinfolist = $navproinfoview->where ( $promap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
		
			for($i = 0; $i < count ( $navproinfolist ); $i ++) {
				$navproinfolist [$i] ['add_time'] = timetodate ( $navproinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$navproinfolist [$i] ['latest_modify'] = timetodate ( $navproinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$navproinfolist [$i] ['onshelf_time'] = timetodate ( $navproinfolist [$i] ['onshelf_time'] ); // 商品上架时间转为可视化
				$navproinfolist [$i] ['macro_path'] = assemblepath ( $navproinfolist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$navproinfolist [$i] ['micro_path'] = assemblepath ( $navproinfolist [$i] ['micro_path'] ); // 特别注意：商品大图拼接路径
			}
		}
		$json = '{"total":' . $navprototal . ',"rows":' . json_encode ( $navproinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 移除选中的商品 
	 */
	public function removeProduct(){
		// 接收要移除的商品id列表
		$delid = I ( 'proid' ); 
		
		// 检测参数完整性
		if (empty ( $delid )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "移除失败，要移除的商品编号不能为空。";
			$this->ajaxReturn( $ajaxinfo ); 
		}
		
		// 将商品从分销导航下架
		$protable = M ( 'product' ); // 商品表
		$delproductmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => $delid,
				'is_del' => 0
		); 
		$removedata = array (
				'p2pnav_id' => -1, // 分销导航下架
				'p2pnav_settime' => -1, // 上架时间置空
		);	
		$delproductresult = $protable->where ( $delproductmap )->save ( $removedata ); 
				
		// 处理删除结果并返回
		if ($delproductresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "移除商品失败，" . $globalmsg . "请不要重复移除！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 将结果返回给前台
	}
	
	/**
	 * easyUI点击加号展开获取商品实时库存信息的ajax处理函数。
	 */
	public function getProductStorage() {
		$prostoremap = array (
				'product_id' => I ( 'pid' ),
				'is_del' => 0
		);
		$storageinfo = M ( 'product_sku' )->where ( $prostoremap )->select (); 
		if ($storageinfo) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'detaillist' => $storageinfo
					)
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10002,
					'errMsg' => '查询库存信息失败！',
					'data' => array ()
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 搜索框
	 */
	public function searchDistributor() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'latest_modify'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); // 接收查询条件
		$content = I ( 'searchcontent' );
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		if ($condition == "chinese_name" || $condition == "english_name") {
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		} 
		$navinfoview = M ( 'distributornav' ); // 
		$navinfolist = array (); // 分销 导航信息数组
		
		$navtotal = $navinfoview->where ( $searchmap )->count (); // 计算当前分销导航总数
		if ($navtotal) {
			$navinfolist = $navinfoview->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $navinfolist ); $i ++) {
				$navinfolist [$i] ['add_time'] = timetodate ( $navinfolist [$i] ['add_time'] ); // 添加代理导航时间转为可视化
				$navinfolist [$i] ['latest_modify'] = timetodate ( $navinfolist [$i] ['latest_modify'] ); // 最后一次修改代理导航信息时间转为可视化
				$navinfolist [$i] ['onshelf_time'] = timetodate ( $navinfolist [$i] ['onshelf_time'] ); // 代理导航上架时间转为可视化
				$navinfolist [$i] ['image_path'] = assemblepath ( $navinfolist [$i] ['image_path'] ); // 特别注意：代理导航图拼接路径
			}
		}
		$json = '{"total":' . $navtotal . ',"rows":' . json_encode ( $navinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 编辑分销导航信息。
	 */
	public function editNavConfirm() {
		$receivedata = array (
				'nav_id' => I ( 'nid' ),
				'chinese_name' => I ( 'cname' ),
				'english_name' => I ( 'ename' ),
				'image_path' => I ( 'navimg' ),
				'first_benefit_rate' => I ( 'firstrate' ),
				'second_benefit_rate' => I ( 'secondrate' ),
				'third_benefit_rate' => I ( 'thirdrate' ),
				'fourth_benefit_rate' => I ( 'fourthrate' ),
				'sibling_order' => I ( 'navorder' ),
				'visible_level' => I ( 'visible' ),
				'sn_code' => I ( 'sncode' ),
				'agent_open' => I ( 'agentopen' ), 
		);
		$savenavmap = array (
				'nav_id' => $receivedata ['nav_id'], 
				'is_del' => 0, // 没有被删除的 
		);
		$navsaveinfo = array (
				'chinese_name' => $receivedata ['chinese_name'],
				'english_name' => $receivedata ['english_name'],
				'image_path' => $receivedata ['image_path'],
				'first_benefit_rate' => $receivedata ['first_benefit_rate'],
				'second_benefit_rate' => $receivedata ['second_benefit_rate'],
				'third_benefit_rate' => $receivedata ['third_benefit_rate'],
				'fourth_benefit_rate' => $receivedata ['fourth_benefit_rate'],
				'sibling_order' => $receivedata ['sibling_order'],
				'visible_level' => $receivedata ['visible_level'],
				'sn_code' => $receivedata ['sn_code'],
				'agent_open' => $receivedata ['agent_open'],
				'latest_modify' => time (), // 最近一次修改时间
		); 
		$saveresult = M ( 'distributornav' )->where ( $savenavmap )->save ( $navsaveinfo ); // 保存回数据库
		if ($saveresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "保存失败，请不要重复提交。";
		}
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/distributornav/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
}
?>