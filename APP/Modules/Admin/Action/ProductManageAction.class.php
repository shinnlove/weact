<?php
/**
 * 商品管理控制器。
 * @author 王健。
 * @modify 赵臣升，胡福玲。
 */
class ProductManageAction extends PCViewLoginAction {
	
	/**
	 * 商品列表页面显示分类。
	 * 该分类不加载商品数据。
	 */
	public function productView() {
		// 分类查找商品时读取分类
		$allpromap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$this->navlist = M ( 'product_image' )->where( $allpromap )->Distinct (true)->field ( 'nav_name' )->select (); // 现在改为从product_image视图中读取商品导航，2015/05/02修改
		$this->e_id = $_SESSION ['curEnterprise'] ['e_id']; // 推送企业编号
		$this->display ();
	}
	
	/**
	 * 查看商品详情。
	 */
	public function lookProductDetail(){
		$data = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id']
		);
	
		//查商品和类别
		$proMap = array (
				'product_id' => $data ['product_id'],
				'e_id' => $data ['e_id'],
				'is_del' => 0
		);
		$productDetail = M ( 'product' )->where ( $proMap )->find (); // product表已经包含导航名称，2015/05/02修改
		$this->productDetail = $productDetail;
	
		//查商品图片
		$proImgMap = array (
				'product_id' => $data ['product_id'],
				'is_del' => 0
		);
		$imageList = M ( 'productimage' )->where ( $proImgMap )->select ();
		$imagenum = count ( $imageList );
		for ($i = 0; $i < $imagenum; $i ++) {
			$imageList [$i] ['macro_path'] = assemblepath ( $imageList [$i] ['macro_path'] ); // 组装大图
			$imageList [$i] ['micro_path'] = assemblepath ( $imageList [$i] ['micro_path'] ); // 组装小图
		}
		$this->imageCount = $imagenum;
		$this->imageList = $imageList;
	
		//查商品颜色、尺寸和数量
		$proSCAMap =  array (
				'product_id' => $data ['product_id'],
				'is_del' => 0
		);
		$scaList = M ( 'productsizecolor' )->where ( $proSCAMap )->order ( 'size_order asc' )->select ();
		$this->scaList = $scaList;
		$this->display ();
	}
	
	/**
	 * 选择商品分类页面，利用产生的product_id跳转至costumes或commondity页面。
	 */
	public function addProduct() {
		$this->product_id = md5 ( uniqid ( rand (), true ) ); // 预先产生一个product编号，ueditor就可以根据这个编号分文件夹上传图片了
		$this->display ();
	}
	
	/**
	 * 根据要添加的商品类别跳转到添加具体商品视图页面。
	 */
	public function addProductDetail(){
		$product_type = I ( 'product_type' );
		$this->product_id = I ( 'product_id' );
		$this->product_type= $product_type;
		//若为服饰
		if ($product_type == 2){
			$this->display ( 'addCostumesProduct' ); 	// 服装类商品
		} else {
			$this->display ( 'addCommodityProduct' ); 	// 非服装类商品
		}
	}
	
	/**
	 * 根据要编辑的商品类别跳转到添加具体商品视图页面。
	 */
	public function editProductDetail() {
		$product_type = I ( 'product_type' );
		$this->product_id = I ( 'product_id' );
		$this->product_type= $product_type;
	
		$promap = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$productinfo = M ( 'product' )->where ( $promap )->find (); 											// 查找出商品信息，2015/05/02改
		$productinfo ['html_description'] = json_encode ( $productinfo ['html_description'] ); 					// 防止用户乱输入，直接进行转义处理
		$this->productinfo = $productinfo; 																		// 推送商品信息
	
		$commap = array (
				'product_id' => I ( 'product_id' ),
				'is_del' => 0
		);
		$editimglist = array ();
		$editimgidlist = array ();
		$imagelist = M ( 'productimage' )->where ( $commap )->order ( 'add_time asc' )->select (); 				// 查找出商品的图片列表信息
		for ($i = 0; $i < count ( $imagelist ); $i ++) {
			$imagelist [$i] ['macro_path'] = assemblepath ( $imagelist [$i] ['macro_path'] );
			$imagelist [$i] ['micro_path'] = assemblepath ( $imagelist [$i] ['micro_path'] );
			array_push ( $editimglist, $imagelist [$i] ['micro_path'] );
			array_push ( $editimgidlist, $imagelist [$i] ['product_image_id'] );
		}
		//获取会员专区积分设定信息
		$proexchangemap = array(
				'product_id' => I('product_id'),
				'is_del' => 0
		);
		$proexchangeresult = M('productexchangerule') -> where($proexchangemap)->order('member_level asc')->select();
		//p($proexchangeresult);die;
		$this->levelinfo = $proexchangeresult;
		$this->imagecount = count ( $imagelist ); // 统计有几张图片
		$this->imagelist = $imagelist; // 商品图片数组
		$this->editimglist = implode ( ",", $editimglist );
		$this->editimgidlist = implode ( ",", $editimgidlist );
	
		$sizecolorlist = M ( 'productsizecolor' )->where ( $commap )->order ( 'size_order asc' )->select(); 	// 查找出商品的颜色尺码信息
		$this->skuinfo = $sizecolorlist; 																		// sku信息
		if ($product_type == 2) {
			$this->display ( 'editCostumesProduct' ); 															// 跳转服装类商品编辑页
		} else {
			$this->display ( 'editCommodityProduct' ); 															// 跳转非服装类商品编辑页
		}
	}
	
	/**
	 * 导出商品。
	 */
	public function exportProduct2() {
		// 查询出数据，2015/05/02修改
		$productmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$field = "nav_name, product_type, product_number, product_name, sex, material, current_price, units, storage_amount, sell_amount, storage_warn, html_description, latest_modify"; // 字段
		$productlist = M ( 'product' )->where ( $productmap )->field ( $field )->select (); // 得到该商家所有商品信息
		// 格式化查询出的数据
		for ($i =0; $i < count ( $productlist ); $i ++) {
			if ($productlist [$i] ['product_type'] == 2) {
				$productlist [$i] ['product_type'] = "服装类商品";
			} else if ($productlist [$i] ['product_type'] == 5) {
				$productlist [$i] ['product_type'] = "常用商品";
			}
			if ($productlist [$i] ['sex'] == 0) {
				$productlist [$i] ['sex'] = "通用";
			} else if ($productlist [$i] ['sex'] == 1) {
				$productlist [$i] ['sex'] = "男";
			} else if ($productlist [$i] ['sex'] == 2) {
				$productlist [$i] ['sex'] = "女";
			}
			$productlist [$i] ['storage_amount'] = $productlist [$i] ['storage_amount'] - $productlist [$i] ['sell_amount']; // 计算剩下的库存量
			$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] ); // 更改时间
		}
		// 准备标题准备打印
		$title = array (
				0 => '分类导航',
				1 => '类别',
				2 => '编号',
				3 => '名称',
				4 => '性别',
				5 => '质地',
				6 => '价格',
				7 => '计量单位',
				8 => '当前库存量',
				9 => '当前卖出量',
				10 => '库存预警下限',
				11 => '图文详情(html语言)',
				12 => '最近修改时间'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $productlist, '商品详情'.time(), '所有商品一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
	/**
	 * 导出商品。
	 */
	public function exportProduct() {
		$shoptype = I('optval');		//导出类型，如果是总店的话是0，分店的话是subbranch_id
		$shopname = I('optname');		//导出店铺的名字
		// 查询出数据，2015/09/03修改
		if($shoptype==0){		//查询的是云总库
			$productmap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$field = "nav_name, product_type, product_number, product_name, sex, material,product_color,product_size, current_price, units, central_total_storage,central_total_left, central_total_sell, storage_warn, latest_modify"; // 字段
			$productlist = M ( 'product_sku' )->where ( $productmap )->field ( $field )->select (); // 得到该商家所有商品信息
		}else if($shoptype==1){		//查询的是线上商城
			$productmap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$field = "nav_name, product_type, product_number, product_name, sex, material,product_color,product_size, current_price, units, storage_amount,sku_storage_left, sell_amount, storage_warn, latest_modify"; // 字段
			$productlist = M ( 'product_sku' )->where ( $productmap )->field ( $field )->select (); // 得到该商家所有商品信息
		}else{
			$subbranchmap = array(
					'subbranch_id' => I('optval'),
					'is_del' => 0
			);
			$field = "nav_name, product_type, product_number, product_name, sex, material,sku_color,sku_size, current_price, units, subsku_storage,sub_sku_storage_left,subsku_sell, sub_storage_warn, latest_modify"; // 字段
			$productlist = M ( 'subbranch_product_sku' )->where ( $subbranchmap )->field ( $field )->select (); // 得到该商家所有商品信息
		}
		// 格式化查询出的数据
		for ($i =0; $i < count ( $productlist ); $i ++) {
			if ($productlist [$i] ['product_type'] == 2) {
				$productlist [$i] ['product_type'] = "服装类商品";
			} else if ($productlist [$i] ['product_type'] == 5) {
				$productlist [$i] ['product_type'] = "常用商品";
			}
			if ($productlist [$i] ['sex'] == 0) {
				$productlist [$i] ['sex'] = "通用";
			} else if ($productlist [$i] ['sex'] == 1) {
				$productlist [$i] ['sex'] = "男";
			} else if ($productlist [$i] ['sex'] == 2) {
				$productlist [$i] ['sex'] = "女";
			}
			$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] ); // 更改时间
		}
		
		// 准备标题准备打印
		$title = array (
				0 => '分类导航',
				1 => '类别',
				2 => '编号',
				3 => '名称',
				4 => '性别',
				5 => '质地',
				6 => '颜色',
				7 => '尺寸',
				8 => '价格',
				9 => '计量单位',
				10 => '原始入库数',
				11 => '当前库存数',
				12 => '当前卖出数',
				13 => '库存预警下限',
				14 => '最近修改时间'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $productlist, $shopname.timetodate(time()), '所有商品一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
	
	/**
	 * ERP商品视图页面。
	 */
	public function ERPProductView() {
		$this->e_id = $_SESSION['curEnterprise'] ['e_id'];
		$this->display ();
	}
	
	/**
	 * 批量导入商品视图页面。
	 */
	public function batchImportProduct() {
		$this->display ();
	}
	
}
?>