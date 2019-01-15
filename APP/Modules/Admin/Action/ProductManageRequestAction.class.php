<?php
/**
 * 商品管理ajax请求处理控制器。
 * @author 王健。
 * @modify 赵臣升，胡福玲。
 */
class ProductManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * 获得所有店铺信息,导出店铺数据的时候需要
	 */
	public function getAllShop(){
		$e_id = $_SESSION['curEnterprise']['e_id'];
		$subbranchmap = array(
				'e_id' => $e_id,
				'is_del' => 0
		);
		$subbranchresult = M('subbranch') -> where($subbranchmap) ->field('subbranch_id,subbranch_name')->select();
		if($subbranchresult){
			$shoplist = array(
					'0' => array(
						'subbranch_id' => 0,		//0表示导出总商品库
							'subbranch_name' => '总商品库'
					),
					'1' => array(
						'subbranch_id' => 1,		//1表示线上商城
							'subbranch_name' => '线上商城'
					)
			);
			$shoplist = array_merge($shoplist,$subbranchresult);
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'shoplist' => $shoplist
					)
			);
		}else{
			$ajaxinfo = array (
					'errCode' => -1,
					'errMsg' => '查询店铺信息失败',
					'data' => array (
							
					)
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 查看所有商品(响应商品列表页面的easyui的post请求)。
	 */
	public function getAllProduct() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );

		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
	
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$proinfoview = M ( 'product_image' ); // 定义视图，该视图由商品表和导航类别表连接而成，2015/05/02修改
		$proinfolist = array (); // 商品信息数组
	
		$prototal = $proinfoview->where ( $promap )->count (); // 计算当前商家下的商品总数
		if ($prototal) {
			$proinfolist = $proinfoview->where ( $promap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $proinfolist ); $i ++) {
				$proinfolist [$i] ['add_time'] = timetodate ( $proinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$proinfolist [$i] ['latest_modify'] = timetodate ( $proinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$proinfolist [$i] ['onshelf_time'] = timetodate ( $proinfolist [$i] ['onshelf_time'] ); // 商品上架时间转为可视化
				$proinfolist [$i] ['macro_path'] = assemblepath ( $proinfolist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$proinfolist [$i] ['micro_path'] = assemblepath ( $proinfolist [$i] ['micro_path'] ); // 特别注意：商品小图拼接路径
			}
		}
		$proinfolist = $this->checkProductSkuWarn ( $proinfolist ); // 检查库存是否报警
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * easyUI点击加号展开获取商品实时库存信息的ajax处理函数。
	 */
	public function getProductStorage() {
		$prostoremap = array (
				'product_id' => I ( 'pid' ),
				'is_del' => 0
		);
		$storageinfo = M ( 'product_sku_new' )->where ( $prostoremap )->group('sizecolor_id')->select (); // 修改后的新视图，2015/05/02修改
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
		$proinfolist = $this->checkProductSkuWarn ( $proinfolist ); // 检查库存是否报警
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 完成ueditor的上传(响应前台选中图片后的上传按钮)(商品描述图片)。
	 */
	public function ueditorImageUpload(){
		$savePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/product/' . $_REQUEST ['product_id'] . '/';		// 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); 											// 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); 										// 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 批量删除商品。
	 */
	public function deleteProduct(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$delid = I ( 'proid' ); // 接收要删除的商品id列表
	
		// 特别注意，删除商品是：1、删除product表中商品；2、删除productimage表中图片；3、删除productsizecolor表中颜色尺码规格，属于一整个事务操作。
		// 在product表中有e_id，在productimage和productsizecolor没有e_id，因此先不加e_id去删除后面两张表，再加上e_id删除前面一张表，3张表的操作为一个事务过程。
		// 准备工作
		$protable = M ( 'product' ); // 商品表
		$imagetable = M ( 'productimage' ); // 商品图片表
		$skutable = M ( 'productsizecolor' ); // 商品颜色尺码表
		$globalmsg = ""; // 全局错误消息
		$delproductmap = array (
				'product_id' => $delid,
				'is_del' => 0
		); // 定义删除的范围
		$imagetable->startTrans(); // 开始事务，默认第一张表启动
	
		// Step1:删除productimage表中的图片
		$delimageresult = $imagetable->where ( $delproductmap )->setField ( 'is_del', 1 ); // 删除这些商品图片
		if (! $delimageresult) $globalmsg = "删除商品图片失败！";
		// Step2：删除productsizecolor表中的sku
		$delskuresult = $skutable->where ( $delproductmap )->setField ( 'is_del', 1 ); // 删除这些商品颜色尺码
		if (! $delskuresult) $globalmsg = "删除商品颜色尺码失败！";
		// Step3：删除product表中的商品
		$delproductmap ['e_id'] = $_SESSION ['curEnterprise'] ['e_id']; // 再增加e_id限制
		$delproductresult = $protable->where ( $delproductmap )->setField ( 'is_del', 1 ); // 删除这些商品颜色尺码
		if (! $delproductresult) $globalmsg = "删除商品数据失败！";
	
		// 处理删除结果并返回
		if ($delimageresult && $delskuresult && $delproductresult) {
			$imagetable->commit(); // 提交事务
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$imagetable->rollback(); // 事务失败回滚事务
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "删除商品失败，" . $globalmsg . "请不要重复删除！"
			);
		}
		$this->ajaxReturn( $ajaxinfo ); // 将结果返回给前台
	}
	
	/**
	 * 商品批量上下架ajax处理函数。
	 */
	public function productFromShelf(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' ); // 接收参数
		$type = I ( 'type' ); // 接收操作类型
	
		// 定义要操作的范围
		$uppromap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array( 'in', explode ( ',', $rowdata ) ),
				'is_del' => 0
		);
	
		$handleresult = false; // 默认没成功
		if ($type == "on") {
			$onSave = array (
					'on_shelf' => 1, // 商品上架状态
					'onshelf_time' => time (), // 时间置为当前上架时间
			); // 定义商品上架参数
			$handleresult = M ( 'product' )->where ( $uppromap )->save ( $onSave );
		} else if ($type == "off") {
			$offSave = array (
					'on_shelf' => 0, // 商品下架状态
					'onshelf_time' => -1, // 时间置为-1
			); // 定义商品下架参数
			$handleresult = M ( 'product' )->where ( $uppromap )->save ( $offSave );
		}
	
		// 返回给前台结果
		if ($handleresult) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "操作失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 库存预警中的商品。
	 */
	public function storageTipPro(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'latest_modify'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
	
		/* if ($sort == "nav_name") {
		 $sort = "latest_modify"; // 修复这个精妙微小的bug
			}
			// 商品相关表已经包含nav_name，可排序，2015/05/02修改 */
	
		// Step1：找到报警的产品（下边是按这个顺序）
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'sku_storage_left' => array ( 'exp', '<= storage_warn' ), // exp：查询的条件不会被当成字符串
				'is_del' => 0
		);
	
		$proinfoview = M ( 'product_sku' ); // 定义视图，该视图由商品表和颜色尺寸表连接而成
		$proinfolist = array (); // 库存信息报警的商品信息数组
		$proinfolist = $proinfoview->where ( $promap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->group ( 'product_id' )->select (); // 当前easyUI页数那么多（不是全部）
	
		$productlist = array (); // 最重要推送的库存报警商品
		if ($proinfolist) {
			// Step2-1：打包主键
			$totalcount = count ( $proinfolist ); // 计算总数
			$proidlist = ""; // 定义product的id字符串连接
			for ($i = 0; $i < $totalcount - 1; $i ++) {
				$proidlist .= $proinfolist [$i] ['product_id'] . ",";
			}
			$proidlist .= $proinfolist [$totalcount - 1] ['product_id']; // 拼接最后一个
				
			// Step2-2：从product表中重新读取
			$finalpromap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
					'product_id' => array ( 'in', $proidlist ), // 从商品里找
					'is_del' => 0
			);
			$productlist = M ( 'product_image' )->where ( $finalpromap )->order ( '' . $sort . ' ' . $order )->select (); // 按照顺序找出这些商品
				
			// Step2-3：包装数据（格式化时间、组装路径和库存报警）
			for($i = 0; $i < $totalcount; $i ++) {
				$productlist [$i] ['add_time'] = timetodate ( $productlist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$productlist [$i] ['onshelf_time'] = timetodate ( $productlist [$i] ['onshelf_time'] ); // 商品上架时间转为可视化
				$productlist [$i] ['macro_path'] = assemblepath ( $productlist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$productlist [$i] ['micro_path'] = assemblepath ( $productlist [$i] ['micro_path'] ); // 特别注意：商品小图拼接路径
				$productlist [$i] ['warning'] = 1; // 特别注意：这里专门查询库存报警的，所以不调用checkProductSkuWarn函数，而直接将warning置为1
			}
		}
		$json = '{"total":' . $totalcount . ',"rows":' . json_encode ( $productlist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 设置某商品的sku单元预警提示。
	 */
	public function setStorageWarning(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$warnpro = array(
				'product_id' => I ( 'pid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$warninfo = array(
				'storage_warn' => I ( 'sn' ),
		);
		$setresult = M ( 'product' )->where ( $warnpro )->save ( $warninfo );
	
		if($setresult){
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		}else{
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "设置预警数量失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 检测商品sku报警的函数checkProductSkuWarn。
	 * 重要函数：读一次数据库，O（n²）检查库存报警函数，经常要调用到。
	 * @param array $productlist 商品数组，一般easyUI传来的是10条左右。
	 * @return array $productlist 带库存报警的商品数组
	 */
	private function checkProductSkuWarn($productlist = NULL) {
		//p('33');die;
		$totalcount = count ( $productlist );
		if ($totalcount == 0 || $totalcount >= 20) return $productlist; // 如果空数据或者数量太大影响数据库效率，直接原样返回不作处理
	
		// Step1：将商品主键预处理
		$checkidlist = ""; // 定义product的id字符串连接
		for ($i = 0; $i < $totalcount - 1; $i ++) {
			$checkidlist .= $productlist [$i] ['product_id'] . ",";
		}
		$checkidlist .= $productlist [$totalcount - 1] ['product_id']; // 拼接最后一个
	
		// Step2：定义搜索的数组，很重要
		$skucheckmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array ( 'in' , $checkidlist ), // 这些商品中
				'sku_storage_left' => array ( 'exp', '<= storage_warn' ), // 单个sku库存小于报警数量的商品
				'is_del' => 0
		);
	
		// Step3：查找出报警库存的product
		$warnprolist = M ( 'product_sku_new' )->where($skucheckmap)->group('sizecolor_id')->field('distinct product_id')->select(); // 按商品主键group by去重
		
		// Step4：对形参$productlist添加报警字段
		for ($i = 0; $i < count ( $productlist ); $i ++) {
			$warningflag = false; // 库存报警标志
			for ($j = 0; $j < count ( $warnprolist ); $j ++) {
				if ($productlist [$i] ['product_id'] == $warnprolist [$j] ['product_id']) {
					$warningflag = true; // 匹配商品编号，报名字段置为1
					break;
				}
			}
			if ($warningflag) {
				$productlist [$i] ['warning'] = 1; // 如果库存报警，warning就是1
			} else {
				$productlist [$i] ['warning'] = 0; // 如果库存不报警，warning就是0
			}
		}
	
		return $productlist; // 返回添加报警字段warning的商品列表
	}
	
	/**
	 * 设置sku新库存的ajax处理函数。最近一次修改wlk,2015/9/20/16:24
	 */
	public function setNewStorage(){
		$scmap = array(
				'sizecolor_id' => I ( 'scid' ),
				'is_del' => 0
		);
		$skutable = M ( 'productsizecolor' );
		$currentskuinfo = $skutable->where ( $scmap )->find ();
		$currentskuinfo ['storage_amount'] = $currentskuinfo ['storage_amount'] + I ( 'nosa', 0 );
		$currentskuinfo ['central_total_storage'] = $currentskuinfo ['central_total_storage'] + I ( 'ncsa', 0 );
		$setrst = $skutable->where ( $scmap )->save ( $currentskuinfo );
	
		if ($setrst) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => "ok"
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10000,
					'errMsg' => "设置新库存数量失败，网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 添加服饰类商品的提交处理函数。
	 */
	public function addCostumesConfirm() {
		// 接收提交商品的数据部分
		$productinfo = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ( 'nav_id' ),
				'nav_name' => I('navname'),
				'product_type' => I('product_type'),
				'product_number' => I ( 'product_number' ),
				'product_name' => I ( 'product_name' ),
				'sex' => I ( 'sex' ),
				'material' => I ( 'material' ),
				'original_price' => I('original_price'),
				'current_price' => I ( 'current_price' ),
				'units' => I ( 'units' ),
				'sell_amount' => 0,
				'html_description' => stripslashes ( $_POST ['html_description'] ), // 转义接收图文信息描述后去除反斜杠
				'add_time' => time (),
				'latest_modify' => time(),
				'on_shelf' => 0, // 新添加商品默认处于下架状态
				'weight' => I('weight'),
				'postage' => I('expinp'),
				'logistics' => I('loginp'),
				'cutprofit_type' => I('cutinp'),
				'score_type' => I('scoinp'),
				/* *根据新增的会员三级等级制度，此处代码可以略去
				 * 'score_amount' => I('sconee'),
				 *	'score_price' => I('monnee'),
				 * 
				 */			
				///////////////////////////////////////////////////////////				
				'is_feature' => 0,
				'is_new' => 0,
				'is_preferential' => 0,
				'is_del' => 0,
		);
	
		$cuttype = $productinfo['cutprofit_type'];
		if($cuttype == 1){
			$productinfo['cutprofit_amount'] = I('proper');
		}else if($cuttype == 2){
			$productinfo['cutprofit_amount'] = I('proamo');
		}else {
			$productinfo['cutprofit_amount'] = 0;
		}
		//用户购买商品获得的积分数处理，默认0表示不获得积分。
		$presenttype = I('buyinp');
		if($presenttype == 1){
			$productinfo['present_score'] = I('retamo');
		}else {
			$productinfo['present_score'] = 0;
		}
	
		// 接收颜色尺码和数量
		$colorlist = I ( 'colorList' ); // 颜色列表
		$sizelist = I ( 'sizeList' ); // 尺码列表
		$orderlist = I ( 'orderList' ); // 该尺码排序
		$amountlist = I ( 'amountList' ); // 该sku的数量
		$codelist = I ( 'codeList' );
	
		// 检查是添加商品的颜色和尺寸
		if (count ( $sizelist ) == 0) $this->error ( '你没有添加商品的颜色尺寸和数量！' );
		
		
		
		/////////////////////////////////////////////////////////////////////
		// 加入对于会员换购积分规则的接收
		$ruleGetArr = array();
		$ruleGetArr[0]['score_amount'] = I('level1_score');
		$ruleGetArr[0]['is_use'] = I('level1_switch');
		$ruleGetArr[1]['score_amount'] = I('level2_score');
		$ruleGetArr[1]['is_use'] = I('level2_switch');
		$ruleGetArr[2]['score_amount'] = I('level3_score');
		$ruleGetArr[2]['is_use'] = I('level3_switch');
		///////////////////////////////////////////////////////////////////////
		
	
		$protable = M ( 'product' ); // 商品表模型
		$protable->startTrans(); // 开始事务过程，添加一个商品一共有三个插入步骤
		$globalmsg = "ok"; // 总的提示信息
	
		$imgidpathlist = array(
				'img_id' => $_REQUEST['imgidlist'],
				'macro_path' => $_REQUEST['imgpathlist']
		);
	
		// 顺带再生成一张商品的二维码图片，一并插入数据库
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; // 准备商家编号
		$url = "http://www.we-act.cn/weact/Home/ProductView/productShow/e_id/" . $e_id . "/nav_id/" . $productinfo ['nav_id'] . "/product_id/" . $productinfo ['product_id']; // 要写入的二维码地址
		$usetype = "product"; // 二维码用途，可指定product,customer,guide,subbranch,nativepay等多种
		$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/productcode/" . $productinfo ['nav_id'] . "/" . $productinfo ['product_id'] . "/"; // 保存路径按导航、商品编号来存放，必须以./相对路径开头
		$saveqrname = $productinfo ['product_id']; // 是否指定二维码文件名，默认空就用md5生成文件名
		//$logopathname = $uploadresult [0] ['savepath'] . $uploadresult [0] ['savename']; // 默认用第一张图片作为要嵌入作为logo的图片（相对路径+文件名与后缀）
		$logopathname = $imgidpathlist['macro_path'][0];
	
		import ( 'Class.Common.phpqrcode.weactqrcode', APP_PATH, '.php' ); // 载入WeAct的二维码类
		$wqr = new WeActQRCode (); // 生成微动二维码类对象
		$createresult = $wqr->createQRCode ( $e_id, $url, $usetype, $saveqrpath, $saveqrname, $logopathname ); // 调用二维码函数生成二维码并返回生成结果
		if ($createresult ['errCode'] == 0) {
			$productinfo ['qr_code'] = $createresult ['data'] ['logoqrcode']; // 如果生成二维码成功，则$productinfo中加入二维码字段
		}
	
		// 事务过程Step1：添加商品信息到product表
		$addproductresult = $protable->data ( $productinfo )->add (); // 向product表中添加商品信息
		if (! $addproductresult) $globalmsg = "添加商品失败，可能已存在该商品，请不要重复提交！";
	
		// 事务过程Step2：批量插入产品图片到数据库操作
		for($i = 0; $i < count ( $imgidpathlist['img_id'] ); $i ++) {
			$imagelistinfo [$i] = array (
					'product_image_id' => $imgidpathlist['img_id'][$i],
					'product_id' => $productinfo ['product_id'],
					'macro_path' => $imgidpathlist['macro_path'][$i], // 去掉点的整个路径拼接文件名的原图
					'add_time' => time(),
					'is_del' => 0
			); // 循环拼接图片表记录的信息
			$relativepath = $imagelistinfo [$i]['macro_path']; 						// 不带weact的相对路径带文件名
			$pathlength = strlen ( $relativepath ); 								// 图片相对路径的长度
			$filenamespilt = strripos ( $relativepath, "/" ); 						// 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
			$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); 			// 抽出文件夹路径，注意：带斜杠
			$filename = substr ( $relativepath, $filenamespilt + 1 ); 				// 抽出文件名
			$imagelistinfo [$i]['micro_path'] = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
		}
		$addimagesresult = M ( 'productimage' )->addAll ( $imagelistinfo ); // 批量一次性插入图片，得到$addimagesresult的结果
		if (! $addimagesresult) $globalmsg = "添加商品图片失败，上传图片不符合规格或超过大小限制！";
	
		// 事务过程Step3：添加颜色尺码和数量
		$sizecolor = array (); // 尺寸颜色表
		for($i = 0; $i < count ( $sizelist ); $i ++) {
			$sizecolor [$i] = array(
					'sizecolor_id' => md5 ( uniqid ( rand (), true ) ),
					'product_id' => $productinfo ['product_id'],
					'product_color' => $colorlist [$i],
					'product_size' => $sizelist [$i],
					'size_order' => $orderlist [$i],
					'storage_amount' => $amountlist [$i],
					'bar_code' => $codelist [$i],
					'sell_amount' => 0, // 新添加默认卖出量为0
					'is_del' => 0
			);
		}
		$addskuresult = M ( 'productsizecolor' )->addAll ( $sizecolor ); // 批量一次性插入颜色和尺码，得到$addskuresult的结果
		if (! $addskuresult) $globalmsg = "添加商品尺码颜色失败，该规格颜色尺码数量不能为空！";
		
		//////////////////////////////////////////////////////////////
		// 事务过程Step4：添加或更新信息到t_productexchangerule表中去
		// 1)首先判定第一条规则在数据库中是否存在，如果存在，那么说明三条记录均存在，否则均不存在
		$checkRuleMap = array(
					'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
					'product_id'=>$productinfo ['product_id'],
					'is_del'=>0
		);
		$checkResult = M("productexchangerule")->where($checkRuleMap)->order('member_level asc')->select();
		// 2)假如三条记录均不存在，那么构造3条记录，插入数据库中
		if(!$checkResult) {	// 假使不存在任何记录
			// 首先构造三条数据的记录
			$ruleArr = array();
			for( $ruleIndex = 0; $ruleIndex < 3; $ruleIndex++)	{
				$ruleArr[$ruleIndex] = array(
						'rule_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
						'product_id'=>$productinfo ['product_id'],
						'member_level'=>$ruleIndex+1,	// 会员等级从1级开始算
						'score_amount'=>$ruleGetArr[$ruleIndex]['score_amount'],
						'score_price'=>0,
						'is_use'=>$ruleGetArr[$ruleIndex]['is_use'],
						'add_time'=>time(),
						'modify_time'=>time(),
						'remark'=>'',
						'is_del'=>0						
				);
			}
			$addRuleResult = M ("productexchangerule")->addAll ( $ruleArr ); // 批量一次性插入三条会员兑换规则
			if( !$addRuleResult) {
				$globalmsg = "会员积分兑换规则添加失败！请检查后重试!";
				$protable->rollback();	// 事务回滚
				$ajaxresult['errCode'] = 10002;
				$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
				$this->ajaxReturn($ajaxresult);
			}
		}		
		// 3)假如三条记录均存在，那么判断是否有改动，将改动的数据save到数据库中
		else {	// 三条记录均存在
			// 校验三条记录,判断有无改动，校验字段为score_amount,is_use,发生改变的话，连modify_time一并做修改 
			for( $saveIndex = 0; $saveIndex < 3; $saveIndex++){
				// 如果两者中任何一个不等，那么必须更新表中记录
				if( $checkResult[$saveIndex]['score_amount'] != $ruleGetArr[$saveIndex]['score_amount']
				|| $checkResult[$saveIndex]['is_use'] != $ruleGetArr[$saveIndex]['is_use'] ) {
					$saveData['rule_id'] = $checkResult[$saveIndex]['rule_id'];
					$saveData['score_amount'] = $ruleGetArr[$saveIndex]['score_amount'];
					$saveData['is_use'] = $ruleGetArr[$saveIndex]['is_use'];
					$saveData['modify_time'] = time();
					$saveRuleResult = M("productexchangerule")->save($saveData);
					if( !$saveRuleResult) {
						$globalmsg = "会员积分兑换规则"."$saveIndex+1"."级会员改变失败！请检查后重试!";
						$protable->rollback();	// 事务回滚
						$ajaxresult['errCode'] = 10002;
						$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
						$this->ajaxReturn($ajaxresult);
					}
				}
			}			
		}

		/////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////

		// 三个事务过程一起执行成功，算是商品添加成功，否则直接事务回退，商品添加失败。
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($addproductresult && $addimagesresult && $addskuresult){
			$protable->commit(); // 提交添加商品的事务
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '添加成功!'
			);
		} else {
			$protable->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 添加的非服装类商品信息确认。
	 */
	public function addCommodityConfirm(){
		// 接收提交商品的数据部分
		$Commodityinfo = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ( 'nav_id' ),
				'nav_name' => I('navname'),
				'product_type' => I('product_type'),
				'product_number' => I ( 'product_number' ),
				'product_name' => I ( 'product_name' ),
				'material' => I ( 'material' ),
				'original_price' => I('original_price'),
				'current_price' => I ( 'current_price' ),
				'units' => I ( 'units' ),
				'sell_amount' => 0,
				'html_description' => stripslashes ( $_POST ['html_description'] ), // 转义接收图文信息描述后去除反斜杠
				'add_time' => time (),
				'latest_modify' => time(),
				'on_shelf' => 0, // 新添加商品默认处于下架状态
				'weight' => I('weight'),
				'postage' => I('expinp'),
				'logistics' => I('loginp'),
				'cutprofit_type' => I('cutinp'),
				'score_type' => I('scoinp'),
				/* *根据新增的会员三级等级制度，此处代码可以略去
				 * 'score_amount' => I('sconee'),
				 *	'score_price' => I('monnee'),
				 * 
				 */			
				///////////////////////////////////////////////////////////	
				'is_feature' => 0,
				'is_new' => 0,
				'is_preferential' => 0,
				'is_del' => 0,
		);
		$cuttype = $Commodityinfo['cutprofit_type'];
		if($cuttype == 1){
			$Commodityinfo['cutprofit_amount'] = I('proper');
		}else if($cuttype == 2){
			$Commodityinfo['cutprofit_amount'] = I('proamo');
		}else {
			$Commodityinfo['cutprofit_amount'] = 0;
		}
		//用户购买商品获得的积分数处理，默认0表示不获得积分。
		$presenttype = I('buyinp');
		if($presenttype == 1){
			$Commodityinfo['present_score'] = I('retamo');
		}else {
			$Commodityinfo['present_score'] = 0;
		}
	
		// 接收颜色尺码和数量
		$colorlist = I ( 'colorList' ); // 颜色列表
		$sizelist = I ( 'sizeList' ); // 尺码列表
		$orderlist = I ( 'orderList' ); // 该尺码排序
		$amountlist = I ( 'amountList' ); // 该sku的数量
		$codelist = I ( 'codeList' ); // 该sku的barcode
	
		// 检查是添加商品的颜色和尺寸
		if (count ( $sizelist ) == 0) $this->error ( '你没有添加商品的颜色尺寸和数量！' );
		
		/////////////////////////////////////////////////////////////////////
		// 加入对于会员换购积分规则的接收
		$ruleGetArr = array();
		$ruleGetArr[0]['score_amount'] = I('level1_score');
		$ruleGetArr[0]['is_use'] = I('level1_switch');
		$ruleGetArr[1]['score_amount'] = I('level2_score');
		$ruleGetArr[1]['is_use'] = I('level2_switch');
		$ruleGetArr[2]['score_amount'] = I('level3_score');
		$ruleGetArr[2]['is_use'] = I('level3_switch');
		///////////////////////////////////////////////////////////////////////
	
		$protable = M ( 'product' ); 		// 商品表模型
		$protable->startTrans(); 			// 开始事务过程，添加一个商品一共有三个插入步骤
		$globalmsg = "ok"; 					// 总的提示信息
	
		//接收封面图片数据
		$imgidpathlist = array(
				'img_id' => $_REQUEST['imgidlist'],
				'macro_path' => $_REQUEST['imgpathlist']
		);
	
		// 顺带再生成一张商品的二维码图片，一并插入数据库
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; // 准备商家编号
		$url = "http://www.we-act.cn/weact/Home/ProductView/productShow/e_id/" . $e_id . "/nav_id/" . $Commodityinfo ['nav_id'] . "/product_id/" . $Commodityinfo ['product_id']; // 要写入的二维码地址
		$usetype = "product"; // 二维码用途，可指定product,customer,guide,subbranch,nativepay等多种
		$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/productcode/" . $Commodityinfo ['nav_id'] . "/" . $Commodityinfo ['product_id'] . "/"; // 保存路径按导航、商品编号来存放，必须以./相对路径开头
		$saveqrname = $Commodityinfo ['product_id']; // 是否指定二维码文件名，默认空就用md5生成文件名
		$logopathname = $imgidpathlist['macro_path'][0];
	
		import ( 'Class.Common.phpqrcode.weactqrcode', APP_PATH, '.php' ); // 载入WeAct的二维码类
		$wqr = new WeActQRCode (); // 生成微动二维码类对象
		$createresult = $wqr->createQRCode ( $e_id, $url, $usetype, $saveqrpath, $saveqrname, $logopathname ); // 调用二维码函数生成二维码并返回生成结果
		if ($createresult ['errCode'] == 0) {
			$Commodityinfo ['qr_code'] = $createresult ['data'] ['logoqrcode']; // 如果生成二维码成功，则$Commodityinfo中加入二维码字段
		}
	
		// 事务过程Step1：添加商品信息到product表
		$addproductresult = $protable->data ( $Commodityinfo )->add (); 				// 向product表中添加商品信息
		if(! $addproductresult) $globalmsg = "添加商品失败，可能已存在该商品，请不要重复提交！";
	
		// 事务过程Step2：批量插入产品图片到数据库操作
		for($i = 0; $i < count ( $imgidpathlist['img_id'] ); $i ++) {
			$imagelistinfo [$i] = array (
					'product_image_id' => $imgidpathlist['img_id'][$i],
					'product_id' => $Commodityinfo ['product_id'],
					'macro_path' => $imgidpathlist['macro_path'][$i], // 去掉点的整个路径拼接文件名的原图
					'add_time' => time(),
					'is_del' => 0
			); // 循环拼接图片表记录的信息
			$relativepath = $imagelistinfo [$i]['macro_path']; 						// 不带weact的相对路径带文件名
			$pathlength = strlen ( $relativepath ); 								// 图片相对路径的长度
			$filenamespilt = strripos ( $relativepath, "/" ); 						// 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
			$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); 			// 抽出文件夹路径，注意：带斜杠
			$filename = substr ( $relativepath, $filenamespilt + 1 ); 				// 抽出文件名
			$imagelistinfo [$i]['micro_path'] = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
		}
		$addimagesresult = M ( 'productimage' )->addAll ( $imagelistinfo ); // 批量一次性插入图片，得到$addimagesresult的结果
		if (! $addimagesresult) $globalmsg = "添加商品图片失败，上传图片不符合规格或超过大小限制！";
	
		// 事务过程Step3：添加颜色尺码和数量
		$sizecolor = array (); // 尺寸颜色表
		for($i = 0; $i < count ( $sizelist ); $i ++) {
			$sizecolor [$i] = array(
					'sizecolor_id' => md5 ( uniqid ( rand (), true ) ),
					'product_id' => $Commodityinfo ['product_id'],
					'product_color' => $colorlist [$i],
					'product_size' => $sizelist [$i],
					'size_order' => $orderlist [$i],
					'storage_amount' => $amountlist [$i],
					'bar_code' => $codelist [$i],
					'sell_amount' => 0, // 新添加默认卖出量为0
					'is_del' => 0
			);
		}
		$addskuresult = M ( 'productsizecolor' )->addAll ( $sizecolor ); // 批量一次性插入颜色和尺码，得到$addskuresult的结果
		if (! $addskuresult) $globalmsg = "添加商品尺码颜色失败，该规格颜色尺码数量不能为空！";
		
		
		//////////////////////////////////////////////////////////////
		// 事务过程Step4：添加或更新信息到t_productexchangerule表中去
		// 1)首先判定第一条规则在数据库中是否存在，如果存在，那么说明三条记录均存在，否则均不存在
		$checkRuleMap = array(
		'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
		'product_id'=>$Commodityinfo ['product_id'],
		'is_del'=>0
		);
		$checkResult = M("productexchangerule")->where($checkRuleMap)->order('member_level asc')->select();
		// 2)假如三条记录均不存在，那么构造3条记录，插入数据库中
		if(!$checkResult) {	// 假使不存在任何记录
			// 首先构造三条数据的记录
			$ruleArr = array();
			for( $ruleIndex = 0; $ruleIndex < 3; $ruleIndex++)	{
				$ruleArr[$ruleIndex] = array(
						'rule_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
						'product_id'=>$Commodityinfo ['product_id'],
						'member_level'=>$ruleIndex+1,	// 会员等级从1级开始算
						'score_amount'=>$ruleGetArr[$ruleIndex]['score_amount'],
						'score_price'=>0,
						'is_use'=>$ruleGetArr[$ruleIndex]['is_use'],
						'add_time'=>time(),
						'modify_time'=>time(),
						'remark'=>'',
						'is_del'=>0
				);
			}
			$addRuleResult = M ("productexchangerule")->addAll ( $ruleArr ); // 批量一次性插入三条会员兑换规则
			if( !$addRuleResult) {
				$globalmsg = "会员积分兑换规则添加失败！请检查后重试!";
				$protable->rollback();	// 事务回滚
				$ajaxresult['errCode'] = 10002;
				$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
				$this->ajaxReturn($ajaxresult);
			}
		}
		// 3)假如三条记录均存在，那么判断是否有改动，将改动的数据save到数据库中
		else {	// 三条记录均存在
			// 校验三条记录,判断有无改动，校验字段为score_amount,is_use,发生改变的话，连modify_time一并做修改
			for( $saveIndex = 0; $saveIndex < 3; $saveIndex++){
				// 如果两者中任何一个不等，那么必须更新表中记录
				if( $checkResult[$saveIndex]['score_amount'] != $ruleGetArr[$saveIndex]['score_amount']
						|| $checkResult[$saveIndex]['is_use'] != $ruleGetArr[$saveIndex]['is_use'] ) {
							$saveData['rule_id'] = $checkResult[$saveIndex]['rule_id'];
							$saveData['score_amount'] = $ruleGetArr[$saveIndex]['score_amount'];
							$saveData['is_use'] = $ruleGetArr[$saveIndex]['is_use'];
							$saveData['modify_time'] = time();
							$saveRuleResult = M("productexchangerule")->save($saveData);
							if( !$saveRuleResult) {
								$globalmsg = "会员积分兑换规则"."$saveIndex+1"."级会员改变失败！请检查后重试!";
								$protable->rollback();	// 事务回滚
								$ajaxresult['errCode'] = 10002;
								$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
								$this->ajaxReturn($ajaxresult);
							}
						}
			}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////
	
		// 三个事务过程一起执行成功，算是商品添加成功，否则直接事务回退，商品添加失败。
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($addproductresult && $addimagesresult && $addskuresult){
			$protable->commit(); // 提交添加商品的事务
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '添加成功!'
			);
		} else {
			$protable->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 前台新增或修改商品提交前，ajax检查修改或增加的商品信息是否合法。
	 * 比如：不能在add的时候添加一幕一样的商品名称，当然编辑的时候无所谓。
	 */
	public function checkModifyProduct() {
		$type = I ( 'type' );
		$nav_id = I ( 'nav_id' );
		$nav_type = I('nav_type');
		$product_name = I ( 'product_name' );
		$flag = $this->ishaschild ( $nav_id, $nav_type);
		//检查是否是在最后一级分类中添加的商品
		if ($flag) {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => '只能在最后一级分类中添加商品！'
			); // 如果不在最后一级分类中添加的商品
		} else {
			if ($type == "add") {
				//如果是最后一级分类，在检查该该分类下相同命名的商品是否已经存在
				$proCheckTemp = array(
						'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
						'nav_id' => $nav_id,
						'product_name' => $product_name,
						'is_del' => 0,
				);
				$rsCount = M ( 'product' )->where ( $proCheckTemp )->count ();
				if ($rsCount) {
					$ajaxinfo = array (
							'errCode' => 10000,
							'errMsg' => '已经存在相同的商品名称！请确认是否重复添加该商品！'
					); // 如果同类导航下有重名商品
				} else {
					$ajaxinfo = array (
							'errCode' => 0,
							'errMsg' => 'ok'
					);
				}
			} else if ($type == "edit") {
				$ajaxinfo = array (
						'errCode' => 0,
						'errMsg' => 'ok'
				);
			}
		}
		$this->ajaxReturn ( $ajaxinfo ); // 返回给前台
	}
	
	/**
	 * 读取微商自定义导航栏目
	 */
	public function getAllCategory() {
		$nav_id = I ( 'id', '-1' );			// 注意这里的id是固定的，不可以变更
		$nav_type = $_REQUEST['type'];
		$categorymap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_nav_id' => $nav_id,
				'nav_type' => $nav_type,
				'is_del' => 0
		);
		$navtbl = M('navigation');
		$categoryinfo = array ();
		if ($nav_id == '-1') {
			// 查询一级栏目
			$rs = $navtbl->where ( $categorymap )->order ( 'nav_order asc' )->select ();
			$items = array ();
			foreach ( $rs as $row ) {
				$temp ['id'] = $row ['nav_id'];
				$temp ['text'] = $row ['nav_name'];
				$row ['state'] = $this->ishaschild ( $row ['nav_id'], $row ['nav_type'] ) ? 'closed' : 'open';
				$temp ['state'] = $row ['state'];
				array_push ( $items, $temp );
			}
			$categoryinfo = $items;
		} else {
			// 查询子级栏目
			$rs = $navtbl->where ( $categorymap )->order ( 'nav_order asc' )->select ();
			$items = array ();
			foreach ( $rs as $row ) {
				$row ['state'] = $this->ishaschild ( $row ['nav_id'], $row ['nav_type'] ) ? 'closed' : 'open';
				$temp ['id'] = $row ['nav_id'];
				$temp ['text'] = $row ['nav_name'];
				$temp ['state'] = $row ['state'];
				array_push ( $items, $temp );
			}
			$categoryinfo = $items;
		}
		echo json_encode ( $categoryinfo );
	}
	
	/**
	 * 是否有子级导航
	 */
	function ishaschild($nav_id, $nav_type) {
		$navmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_nav_id' => $nav_id,
				'nav_type' => $nav_type,
				'is_del' => 0
		);
		$navchildren = M('navigation')->where($navmap)->count();
		return $navchildren > 0 ? true : false;
	}
	
	/**
	 * 处理前台删除数据库原有图片的ajax请求。
	 * 编辑时候添加的图片不包括在内。
	 */
	public function delProductImage() {
		$delimageinfo = array (
				'product_image_id' => I ( 'skuid' ),
				'product_id' => I ( 'pid' ),
				'is_del' => 0
		);
		$delresult = M ( 'productimage' )->where ( $delimageinfo )->setField ( 'is_del', 1 );
		if ($delresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => '网络繁忙，请稍候再试！'
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 修改商品sku信息函数。
	 * 本函数时间复杂度：2次数据库+2n遍历+upnum次数据库，其中upum是数据库更新sku记录条数。
	 * @param string $product_id 商品编号
	 * @param array $colorlist 颜色数组
	 * @param array $sizelist 尺码数组
	 * @param array $orderlist 尺码序号数组
	 * @param array $amountlist 该颜色尺码的数量
	 * @param array $selllist 销售量
	 * @return boolean $syncresult 同步标记
	 */
	private function modifySkuInfo ($product_id = '', $colorlist = NULL, $sizelist = NULL, $orderlist = NULL, $amountlist = NULL, $codelist = NULL, $selllist = NULL) {
		$syncresult = false; // 同步完成标记，默认false
		if (! empty ( $sizelist )) {
			// Step1：先查询原来的sku信息，时间复杂度：1次遍历n+1次数据库
			$skutable = M ( 'productsizecolor' ); // 颜色尺码表
			$skumap = array (
					'product_id' => $product_id,
					'is_del' => 0
			);
			$skuinfo = $skutable->where ( $skumap )->select (); // 商品sku信息
			$localskucount = count ( $skuinfo ); // 本地sku信息
			for ($i = 0; $i < $localskucount; $i ++) {
				$skuinfo [$i] ['tobedeleted'] = 1; // 默认原来的sku全都要被删除
			}
				
			// Step2：格式化新的sku信息，时间复杂度n
			$newlistcount = count ( $sizelist ); // 统计新的sku数量
			$newskuinfo = array (); // 新的格式化的sku缓存数组
			for ($i = 0; $i < $newlistcount; $i ++) {
				// 格式化新的sku信息
				$newskuinfo [$i] = array (
						'product_color' => $colorlist [$i],
						'product_size' => $sizelist [$i],
						'size_order' => $orderlist [$i],
						'amount_list' => $amountlist [$i],
						'bar_code' => $codelist [$i],
						'sell_amount' => $selllist [$i]
				);
			}
				
			// Step3：查找sku信息有没有存在变化
			$addskulist = array (); // 新添加的sku信息
			$updatesum = 0; // 总的更新记录数目
			for ($i = 0; $i < $newlistcount; $i ++) {
				$existflag = false; // 默认这个sku不存在
				for ($j = 0; $j < $localskucount; $j ++) {
					if ($newskuinfo [$i] ['product_color'] == $skuinfo [$j] ['product_color'] && $newskuinfo [$i] ['product_size'] == $skuinfo [$j] ['product_size']) {
						$existflag = true; // 原有sku存在，可能需要更新信息
						$skuinfo [$j] ['tobedeleted'] = 0; // 旧的sku存在，不需要删除
						// 进一步判断信息有无更新
						if ($newskuinfo [$i] ['size_order'] != $skuinfo [$j] ['size_order'] || ( $newskuinfo [$i] ['amount_list'] + $newskuinfo [$i] ['sell_amount'] ) != $skuinfo [$j] ['storage_amount'] || $newskuinfo [$i] ['bar_code'] != $skuinfo [$j] ['bar_code']) {
							// 尺码排序和库存有一个条件不相等，就是有变更，需要更新信息
							$skuinfo [$j] ['size_order'] = $newskuinfo [$i] ['size_order'];
							$skuinfo [$j] ['storage_amount'] = $newskuinfo [$i] ['amount_list'] + $newskuinfo [$i] ['sell_amount'];
							$skuinfo [$j] ['bar_code'] = $newskuinfo [$i] ['bar_code'];
							//$updatesum += $skutable->save ( $skuinfo); // 保存信息
							$updatesum += $skutable->save ( $skuinfo[$j]); // 纠正：$skuinfo[$j]才是要更新的一维信息
						}
					}
				}
				if (! $existflag) {
					$addskuinfo = array (); // 清空临时变量
					$addskuinfo = array (
							'sizecolor_id' => md5 ( uniqid ( rand (), true )),
							'product_id' => $product_id,
							'product_color' => $newskuinfo [$i] ['product_color'],
							'product_size' => $newskuinfo [$i] ['product_size'],
							'size_order' => $newskuinfo [$i] ['size_order'],
							'storage_amount' => $newskuinfo [$i] ['amount_list'] + $newskuinfo [$i] ['sell_amount'],
							'bar_code' => $newskuinfo [$i] ['bar_code'],
							'sell_amount' => 0,
					);
					array_push ( $addskulist, $addskuinfo ); // 该sku是新增的sku
				}
			}
				
			// Step4：如果有新增的，一次性新增进数据库（数据已经格式化好，读写数据库一次）
			if (! empty ( $addskulist )) {
				$addresult = $skutable->addAll ( $addskulist ); // 一次性添加新增的sku
			} else {
				$addresult = true; // 没有要添加的
			}
				
			// Step5：反向遍历找到要删除的旧sku（时间复杂度del的n+一次数据库时间）
			$delskuid = ""; // 要删除的id字符串
			for ($i = 0; $i < $localskucount; $i ++) {
				if ($skuinfo [$i] ['tobedeleted'] == 1) {
					$delskuid .= $skuinfo [$i] ['sizecolor_id'] . ","; // 将要删除的skuid拼接成字符串
				}
			}
			if (! empty ( $delskuid )) {
				$delskuid = substr ( $delskuid, 0, strlen ( $delskuid ) - 1 ); // 去掉最后的英文逗号
				$delskumap = array (
						'sizecolor_id' => array ( 'in', $delskuid ),
						'product_id' => $product_id,
						'is_del' => 0
				);
				//$delresult = $skutable->where ( $delskumap )->setField ( 'is_del', 1 ); // 删除这些用户删除的sku（逻辑删除注意数据库触发器是update is_del = 1，胡睿已加，2015/02/02 22:56:25，两句都可以用）
				$delresult = $skutable->where ( $delskumap )->delete(); // 删除这些sku
			} else {
				$delresult = true; // 没有要删除的
			}
		}
		if ($addresult && $delresult) {
			$syncresult = true; // 添加和删除都成功，才算成功
		}
		return $syncresult;
	}
	
	/**
	 * 商品修改表单提交处理函数。
	 */
	public function editCostumesConfirm() {
		//$originalchange = I ( 'originalchange' ); // 接收原来图片是否被变更
		//$imagecount = I ( 'imagetotalcount' ); // 从前台接受图片数量，接收总的图片张数
		// 接收颜色尺码和数量
		$colorlist = I ( 'colorList' ); // 颜色列表
		$sizelist = I ( 'sizeList' ); // 尺码列表
		$orderlist = I ( 'orderList' ); // 该尺码排序
		$amountlist = I ( 'amountList' ); // 该sku的数量
		$codelist = I ( 'codeList' );
		$selllist = I ( 'sellList' ); // 接收卖出量
	
		//完成服务器端的校验
		if (empty ( $colorlist )) $this->error ( "请至少添加一种该商品的颜色尺寸与数量！" );
	
		// 接收商品信息（库存数量无法编辑）
		$productinfo = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ( 'nav_id' ),
				'nav_name' => I('navname'),
				'product_type' => I('product_type'),
				'product_number' => I ( 'product_number' ),
				'product_name' => I ( 'product_name' ),
				'sex' => I ( 'sex' ),
				'material' => I ( 'material' ),
				'original_price' => I('original_price'),
				'current_price' => I ( 'current_price' ),
				'units' => I ( 'units' ),
				'html_description' => stripslashes ( $_POST ['html_description'] ), // 转义接收图文信息描述后去除反斜杠
				'latest_modify' => time (),
				'weight' => I('weight'),
				'postage' => I('expinp'),
				'logistics' => I('loginp'),
				'cutprofit_type' => I('cutinp'),
				'score_type' => I('scoinp'),
				/* *根据新增的会员三级等级制度，此处代码可以略去
				 * 'score_amount' => I('sconee'),
				 *	'score_price' => I('monnee'),
				 * 
				 */			
				///////////////////////////////////////////////////////////	
				'is_feature' => 0,
				'is_new' => 0,
				'is_preferential' => 0,
				'is_del' => 0,
		);
		//导购获得提成方式及数额处理，默认0表示不提成
		$cuttype = $productinfo['cutprofit_type'];
		if($cuttype == 1){
			$productinfo['cutprofit_amount'] = I('proper');
		}else if($cuttype == 2){
			$productinfo['cutprofit_amount'] = I('proamo');
		}else {
			$productinfo['cutprofit_amount'] = 0;
		}
		//用户购买商品获得的积分数处理，默认0表示不获得积分。
		$presenttype = I('buyinp');
		if($presenttype == 1){
			$productinfo['present_score'] = I('retamo');
		}else {
			$productinfo['present_score'] = 0;
		}
	
		/////////////////////////////////////////////////////////////////////
		// 加入对于会员换购积分规则的接收
		$ruleGetArr = array();
		$ruleGetArr[0]['score_amount'] = I('level1_score');
		$ruleGetArr[0]['is_use'] = I('level1_switch');
		$ruleGetArr[1]['score_amount'] = I('level2_score');
		$ruleGetArr[1]['is_use'] = I('level2_switch');
		$ruleGetArr[2]['score_amount'] = I('level3_score');
		$ruleGetArr[2]['is_use'] = I('level3_switch');
		///////////////////////////////////////////////////////////////////////
		
		//$originalprice = I ( 'original_price' ); // 接收编辑前的价格
		//if ($originalprice != $productinfo ['current_price']) $productinfo ['original_price'] = $productinfo ['current_price']; // 如果价格有变动，记录之前的价格
	
		// 准备执行事务过程
		$protable = M ( 'product' ); 			// 商品表模型，默认使用第一步操作数据库的表作为事务过程起点
		$globalmsg = "ok"; 						// 总的提示信息（成功或错误）
		$saveproductresult = false; 			// Step1：商品信息没保存成功
		$addimagesresult = false; 				// Step2：商品图片没处理成功
		$modifyskuresult = false; 				// Step3：商品SKU没处理成功
		$protable->startTrans(); 				// 开始事务过程，添加一个商品一共有三个插入步骤
	
		// 事务过程Step1：向product表中保存改动的商品信息
		$saveproductresult = $protable->save ( $productinfo );
		if (! $saveproductresult) $globalmsg = "商品提交修改失败，无信息变动或重复提交！";
	
		// 事务过程Step2：检测图片上传是否符合大小并批量插入数据库操作
		/* if ($imagecount) {
		 // 如果有图片上传，调用productImageUpload函数处理图片上传
		 $uploadresult = $this->productImageUpload ( $productinfo ['product_id'] ); // 物理上传图片（可能会失败）
		 if ($uploadresult) {
		 // 如果处理图片信息成功
		 $imagetable = M ( 'productimage' );
	
		 // Step1：如果变更了原来的首图，则更新它，剩下其余图片插入
		 if ($originalchange) {
		 $imagecount -= 1; // $imagecount先减去一张图片，如果待会还有图片，说明是新增的
		 $updateimagemap = array (
		 'product_id' => $productinfo ['product_id'],
		 'is_del' => 0
		 );
		 $imagelist = $imagetable->where ( $updateimagemap )->select (); // 尝试找出多张图片
		 $firstimage = $imagelist [0]; // 取出第一张图片信息
		 $firstimage ['macro_path'] = substr ( $uploadresult [0] ['savepath'], 1 ) . $uploadresult [0] ['savename']; // 去掉点的整个路径拼接文件名的原图
		 $firstimage ['micro_path'] = substr ( $uploadresult [0] ['savepath'], 1 ) . 'thumb_' . $uploadresult [0] ['savename']; // 去掉点的整个路径拼接文件名的缩略图
		 $updatestep1 = $imagetable->save ( $firstimage ); // 图片保存回去，算是更新了
		 } else {
		 $updatestep1 = true; // 没有变更第一张图，直接默认步骤一成功
		 }
	
		 // Step2：如果此时$imagecount还大于0，代表还有新图片上传，继续处理新图片数据库插入
		 if ($imagecount > 0) {
		 $imagelistinfo = array (); // 通过验证后、待插入的新图片数组
		 // 特别注意：使用$originalchange变量给$i，好处在于：$originalchange如果变更首图，从1开始循环；如果不变更首图，从0开始循环。
		 for ($i = $originalchange; $i < count ( $uploadresult ); $i ++) {
		 $imagelistinfo [$i] = array (
		 'product_image_id' => md5 ( uniqid ( rand (), true ) ),
		 'product_id' => $productinfo ['product_id'],
		 'macro_path' => substr ( $uploadresult [$i] ['savepath'], 1 ) . $uploadresult [$i] ['savename'], // 去掉点的整个路径拼接文件名的原图
		 'micro_path' => substr ( $uploadresult [$i] ['savepath'], 1 ) . 'thumb_' . $uploadresult [$i] ['savename'], // 去掉点的整个路径拼接文件名的缩略图
		 'add_time' => time(), // 添加时间，以区分后添加的在后边
		 'is_del' => 0
		 ); // 循环拼接图片表记录的信息
		 }
		 $updatestep2 = $imagetable->addAll ( $imagelistinfo ); // 批量一次性插入图片，得到$addimagesresult的结果
		 if(! $updatestep2) $globalmsg = "编辑商品图片失败，网络繁忙，请稍候再试！"; // 新图片插入不成功，也不算成功
		 } else {
		 $updatestep2 = true; // 没有多余图片，直接默认步骤2成功
		 }
		 if ($updatestep1 && $updatestep2) $addimagesresult = true; // 步骤一和二都成功，那才算图片变更处理完成
		 } else {
		 $globalmsg = "编辑商品图片失败，上传图片不符合规格或超过大小限制！"; // 新图片不符合规格，上传处理失败
		 }
		 } else {
		 $addimagesresult = true; // 没有图片上传，直接默认$addimagesresult是true
		 } */
	
		$imgidpathlist = array(
				'imgidlist' => $_REQUEST['imgidlist'],
				'delimgidlist' => $_REQUEST['delimglist'],
				'macropathlist' => $_REQUEST['imgpathlist']
		);
	
		if($imgidpathlist['imgidlist'] == '' || $imgidpathlist['imgidlist'] == null){
			$addimagesresult = true;
		}else{
			$j = 0;
			for($i = 0; $i < count ( $imgidpathlist['imgidlist']); $i ++) {
				//新增图片插入
				if($imgidpathlist['imgidlist'][$i] != null && $imgidpathlist['imgidlist'][$i] != ''){
					$imagelistinfo [$j] = array (
							'product_image_id' => $imgidpathlist['imgidlist'][$i],
							'product_id' => $productinfo ['product_id'],
							'macro_path' => $imgidpathlist['macropathlist'][$i], // 去掉点的整个路径拼接文件名的原图
							'add_time' => time(), // 添加时间，以区分，后添加的在后边
							'is_del' => 0
					); // 循环拼接图片表记录的信息
					$relativepath = $imagelistinfo [$j]['macro_path']; 						// 不带weact的相对路径带文件名
					$pathlength = strlen ( $relativepath ); 								// 图片相对路径的长度
					$filenamespilt = strripos ( $relativepath, "/" ); 						// 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
					$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); 			// 抽出文件夹路径，注意：带斜杠
					$filename = substr ( $relativepath, $filenamespilt + 1 ); 				// 抽出文件名
					$imagelistinfo [$j]['micro_path'] = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
					$j += 1;
				}
			}
				
			$addimagesresult = M ( 'productimage' )->addAll ( $imagelistinfo ); // 批量一次性插入图片，得到$addimagesresult的结果
			if (! $addimagesresult) $globalmsg = "添加商品图片失败，上传图片不符合规格或超过大小限制！";
		}
	
		//图片删除处理
		if($imgidpathlist['delimgidlist'] == '' || $imgidpathlist['delimgidlist'] == null){
			$delimgresult = true;
		}else{
			$delimgmap = array(
					'product_image_id' => array('in', $imgidpathlist['delimgidlist']),
					'is_del' => 0
			);
			$delimgresult = M('productimage')->where($delimgmap)->delete();
		}
	
	
		// 事务过程Step3：修改商品的sku信息
		$modifyskuresult = $this->modifySkuInfo ( $productinfo ['product_id'], $colorlist, $sizelist, $orderlist, $amountlist, $codelist, $selllist );
	
		
		//////////////////////////////////////////////////////////////
		// 事务过程Step4：添加或更新信息到t_productexchangerule表中去
		// 1)首先判定第一条规则在数据库中是否存在，如果存在，那么说明三条记录均存在，否则均不存在
		$checkRuleMap = array(
		'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
		'product_id'=>$productinfo ['product_id'],
		'is_del'=>0
		);
		$checkResult = M("productexchangerule")->where($checkRuleMap)->order('member_level asc')->select();
		// 2)假如三条记录均不存在，那么构造3条记录，插入数据库中
		if(!$checkResult) {	// 假使不存在任何记录
			// 首先构造三条数据的记录
			$ruleArr = array();
			for( $ruleIndex = 0; $ruleIndex < 3; $ruleIndex++)	{
				$ruleArr[$ruleIndex] = array(
						'rule_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
						'product_id'=>$productinfo ['product_id'],
						'member_level'=>$ruleIndex+1,	// 会员等级从1级开始算
						'score_amount'=>$ruleGetArr[$ruleIndex]['score_amount'],
						'score_price'=>0,
						'is_use'=>$ruleGetArr[$ruleIndex]['is_use'],
						'add_time'=>time(),
						'modify_time'=>time(),
						'remark'=>'',
						'is_del'=>0
				);
			}
			$addRuleResult = M ("productexchangerule")->addAll ( $ruleArr ); // 批量一次性插入三条会员兑换规则
			if( !$addRuleResult) {
				$globalmsg = "会员积分兑换规则添加失败！请检查后重试!";
				$protable->rollback();	// 事务回滚
				$ajaxresult['errCode'] = 10002;
				$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
				$this->ajaxReturn($ajaxresult);
			}
		}
		// 3)假如三条记录均存在，那么判断是否有改动，将改动的数据save到数据库中
		else {	// 三条记录均存在
			// 校验三条记录,判断有无改动，校验字段为score_amount,is_use,发生改变的话，连modify_time一并做修改
			for( $saveIndex = 0; $saveIndex < 3; $saveIndex++){
				// 如果两者中任何一个不等，那么必须更新表中记录
				if( $checkResult[$saveIndex]['score_amount'] != $ruleGetArr[$saveIndex]['score_amount']
						|| $checkResult[$saveIndex]['is_use'] != $ruleGetArr[$saveIndex]['is_use'] ) {
							$saveData['rule_id'] = $checkResult[$saveIndex]['rule_id'];
							$saveData['score_amount'] = $ruleGetArr[$saveIndex]['score_amount'];
							$saveData['is_use'] = $ruleGetArr[$saveIndex]['is_use'];
							$saveData['modify_time'] = time();
							$saveRuleResult = M("productexchangerule")->save($saveData);
							if( !$saveRuleResult) {
								$globalmsg = "会员积分兑换规则"."$saveIndex+1"."级会员改变失败！请检查后重试!";
								$protable->rollback();	// 事务回滚
								$ajaxresult['errCode'] = 10002;
								$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
								$this->ajaxReturn($ajaxresult);
							}
						}
			}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////
		// 三个事务过程一起执行成功，算是商品修改成功，否则直接事务回退，商品添加失败。
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($saveproductresult && $addimagesresult  && $delimgresult && $modifyskuresult){
			$protable->commit(); // 提交添加商品的事务
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '更新成功!'
			);
		} else {
			$protable->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '更新失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	
		/* if ($saveproductresult && $addimagesresult && $modifyskuresult) {
		 $protable->commit(); // 提交添加商品的事务
		 $this->redirect ( 'Admin/ProductManage/productView' ); // 最好先来个添加商品成功，然后再跳到商品列表中
		 } else {
		 $protable->rollback(); // 回滚事务
		 $this->error ( "编辑商品信息失败！" . $globalmsg );
		} */
	}
	
	/**
	 * 提交编辑日常商品（非服装商品）。
	 */
	public function editCommodityConfirm(){
		$colorlist = I ( 'colorList' ); // 颜色列表
		$sizelist = I ( 'sizeList' ); // 尺码列表
		$orderlist = I ( 'orderList' ); // 该尺码排序
		$amountlist = I ( 'amountList' ); // 该sku的数量
		$codelist = I ( 'codeList' );
		$selllist = I ( 'sellList' ); // 接收卖出量
	
		//完成服务器端的校验
		if (empty ( $colorlist )) $this->error ( "请至少添加一种该商品的颜色尺寸与数量！" );
	
		// 接收商品基本信息（库存数量无法编辑）
		$commodityInfo = array (
				'product_id' => I ( 'product_id' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ( 'nav_id' ),
				'nav_name' => I('navname'),
				'product_type' => I('product_type'),
				'product_number' => I ( 'product_number' ),
				'product_name' => I ( 'product_name' ),
				'material' => I ( 'material' ),
				'original_price' => I('original_price'),
				'current_price' => I ( 'current_price' ),
				'units' => I ( 'units' ),
				'html_description' => stripslashes ( $_POST ['html_description'] ), // 转义接收图文信息描述后去除反斜杠
				'latest_modify' => time (),
				'weight' => I('weight'),
				'postage' => I('expinp'),
				'logistics' => I('loginp'),
				'cutprofit_type' => I('cutinp'),
				'score_type' => I('scoinp'),
				/* *根据新增的会员三级等级制度，此处代码可以略去
				 * 'score_amount' => I('sconee'),
				 *	'score_price' => I('monnee'),
				 * 
				 */			
				///////////////////////////////////////////////////////////	
				'is_feature' => 0,
				'is_new' => 0,
				'is_preferential' => 0,
				'is_del' => 0,
		);
		//导购获得提成方式及数额处理，默认0表示不提成
		$cuttype = $commodityInfo['cutprofit_type'];
		if($cuttype == 1){
			$commodityInfo['cutprofit_amount'] = I('proper');
		}else if($cuttype == 2){
			$commodityInfo['cutprofit_amount'] = I('proamo');
		}else {
			$commodityInfo['cutprofit_amount'] = 0;
		}
		//用户购买商品获得的积分数处理，默认0表示不获得积分。
		$presenttype = I('buyinp');
		if($presenttype == 1){
			$commodityInfo['present_score'] = I('retamo');
		}else {
			$commodityInfo['present_score'] = 0;
		}
		
		/////////////////////////////////////////////////////////////////////
		// 加入对于会员换购积分规则的接收
		$ruleGetArr = array();
		$ruleGetArr[0]['score_amount'] = I('level1_score');
		$ruleGetArr[0]['is_use'] = I('level1_switch');
		$ruleGetArr[1]['score_amount'] = I('level2_score');
		$ruleGetArr[1]['is_use'] = I('level2_switch');
		$ruleGetArr[2]['score_amount'] = I('level3_score');
		$ruleGetArr[2]['is_use'] = I('level3_switch');
		///////////////////////////////////////////////////////////////////////
	
		// 准备执行事务过程
		$protable = M ( 'product' ); 			// 商品表模型，默认使用第一步操作数据库的表作为事务过程起点
		$globalmsg = "ok"; 						// 总的提示信息（成功或错误）
		$saveproductresult = false; 			// Step1：商品信息没保存成功
		$addimagesresult = false; 				// Step2：商品图片没处理成功
		$modifyskuresult = false; 				// Step3：商品SKU没处理成功
		$protable->startTrans(); 				// 开始事务过程，添加一个商品一共有三个插入步骤
	
		// 事务过程Step1：向product表中保存改动的商品信息
		$saveproductresult = $protable->save ( $commodityInfo );
		if (! $saveproductresult) $globalmsg = "商品提交修改失败，无信息变动或重复提交！";
	
		// 事务过程Step2：修改商品的图片信息
		$imgidpathlist = array(
				'imgidlist' => $_REQUEST['imgidlist'],
				'delimgidlist' => $_REQUEST['delimglist'],
				'macropathlist' => $_REQUEST['imgpathlist']
		);
	
		//新增封面图片处理
		if($imgidpathlist['imgidlist'] == '' || $imgidpathlist['imgidlist'] == null){
			$addimagesresult = true;
		}else{
			$j=0;
			for($i = 0; $i < count ( $imgidpathlist['imgidlist']); $i ++) {
				//新增图片插入
				if($imgidpathlist['imgidlist'][$i] != null && $imgidpathlist['imgidlist'][$i] != ''){
					$imagelistinfo [$j] = array (
							'product_image_id' => $imgidpathlist['imgidlist'][$i],
							'product_id' => $commodityInfo ['product_id'],
							'macro_path' => $imgidpathlist['macropathlist'][$i], // 去掉点的整个路径拼接文件名的原图
							'add_time' => time(), // 添加时间，以区分后添加的在后边
							'is_del' => 0
					); // 循环拼接图片表记录的信息
					$relativepath = $imagelistinfo [$j]['macro_path']; 						// 不带weact的相对路径带文件名
					$pathlength = strlen ( $relativepath ); 								// 图片相对路径的长度
					$filenamespilt = strripos ( $relativepath, "/" ); 						// 在相对路径中找出最后一个文件名带后缀之前的/的起始位置
					$folderpath = substr ( $relativepath, 0, $filenamespilt + 1 ); 			// 抽出文件夹路径，注意：带斜杠
					$filename = substr ( $relativepath, $filenamespilt + 1 ); 				// 抽出文件名
					$imagelistinfo [$j]['micro_path'] = $folderpath . "thumb_" . $filename; // 拼接处缩略图的相对路径
					$j += 1;
				}
			}
			$addimagesresult = M ( 'productimage' )->addAll ( $imagelistinfo ); // 批量一次性插入图片，得到$addimagesresult的结果
			if (! $addimagesresult) $globalmsg = "添加商品图片失败，上传图片不符合规格或超过大小限制！";
		}
	
		//图片删除处理
		if($imgidpathlist['delimgidlist'] == '' || $imgidpathlist['delimgidlist'] == null){
			$delimgresult = true;
		}else{
			$delimgmap = array(
					'product_image_id' => array('in', $imgidpathlist['delimgidlist']),
					'is_del' => 0
			);
			$delimgresult = M('productimage')->where($delimgmap)->delete();
		}
	
	
		// 事务过程Step3：修改商品的规格和库存信息
		/* $modifymap = array(
		 'product_id' => $commodityInfo ['product_id'],
		 'is_del' => 0
		);
		$modifydata = array(
		'product_size' => I('prosize'),
		'storage_amount' => I('totalamo')
		);
		$modifyskuresult = M('productsizecolor')->where($modifymap)->save($modifydata); */
		$modifyskuresult = $this->modifySkuInfo ( $commodityInfo ['product_id'], $colorlist, $sizelist, $orderlist, $amountlist, $codelist, $selllist );
	
		//////////////////////////////////////////////////////////////
		// 事务过程Step4：添加或更新信息到t_productexchangerule表中去
		// 1)首先判定第一条规则在数据库中是否存在，如果存在，那么说明三条记录均存在，否则均不存在
		$checkRuleMap = array(
		'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
		'product_id'=>$commodityInfo ['product_id'],
		'is_del'=>0
		);
		$checkResult = M("productexchangerule")->where($checkRuleMap)->order('member_level asc')->select();
		// 2)假如三条记录均不存在，那么构造3条记录，插入数据库中
		if(!$checkResult) {	// 假使不存在任何记录
			// 首先构造三条数据的记录
			$ruleArr = array();
			for( $ruleIndex = 0; $ruleIndex < 3; $ruleIndex++)	{
				$ruleArr[$ruleIndex] = array(
						'rule_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id'=> $_SESSION ['curEnterprise'] ['e_id'],
						'product_id'=>$commodityInfo ['product_id'],
						'member_level'=>$ruleIndex+1,	// 会员等级从1级开始算
						'score_amount'=>$ruleGetArr[$ruleIndex]['score_amount'],
						'score_price'=>0,
						'is_use'=>$ruleGetArr[$ruleIndex]['is_use'],
						'add_time'=>time(),
						'modify_time'=>time(),
						'remark'=>'',
						'is_del'=>0
				);
			}
			$addRuleResult = M ("productexchangerule")->addAll ( $ruleArr ); // 批量一次性插入三条会员兑换规则
			if( !$addRuleResult) {
				$globalmsg = "会员积分兑换规则添加失败！请检查后重试!";
				$protable->rollback();	// 事务回滚
				$ajaxresult['errCode'] = 10002;
				$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
				$this->ajaxReturn($ajaxresult);
			}
		}
		// 3)假如三条记录均存在，那么判断是否有改动，将改动的数据save到数据库中
		else {	// 三条记录均存在
			// 校验三条记录,判断有无改动，校验字段为score_amount,is_use,发生改变的话，连modify_time一并做修改
			for( $saveIndex = 0; $saveIndex < 3; $saveIndex++){
				// 如果两者中任何一个不等，那么必须更新表中记录
				if( $checkResult[$saveIndex]['score_amount'] != $ruleGetArr[$saveIndex]['score_amount']
						|| $checkResult[$saveIndex]['is_use'] != $ruleGetArr[$saveIndex]['is_use'] ) {
							$saveData['rule_id'] = $checkResult[$saveIndex]['rule_id'];
							$saveData['score_amount'] = $ruleGetArr[$saveIndex]['score_amount'];
							$saveData['is_use'] = $ruleGetArr[$saveIndex]['is_use'];
							$saveData['modify_time'] = time();
							$saveRuleResult = M("productexchangerule")->save($saveData);
							if( !$saveRuleResult) {
								$globalmsg = "会员积分兑换规则"."$saveIndex+1"."级会员改变失败！请检查后重试!";
								$protable->rollback();	// 事务回滚
								$ajaxresult['errCode'] = 10002;
								$ajaxresult['errMsg'] = '添加失败，请检查网络情况后重试!';
								$this->ajaxReturn($ajaxresult);
							}
						}
			}
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////
		
		// 三个事务过程一起执行成功，算是商品修改成功，否则直接事务回退，商品添加失败。
		$ajaxresult = array (); 									// 要返回的ajax信息
		if($saveproductresult && $addimagesresult  && $delimgresult || $modifyskuresult){
			$protable->commit(); // 提交添加商品的事务
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '更新成功!'
			);
		} else {
			$protable->rollback(); // 回滚事务
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '更新失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * flash上传图片。
	 */
	public function productImageUpload() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/product/' . $_REQUEST ['pid'] . '/';
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
	
	/**
	 * 处理下载商品二维码图片的ajax请求。
	 */
	public function downloadQRCode() {
		$product_id = I ( 'pid' );
		$direct2browser = I ( 'direct2browser', 0 );
	
		$dimensioncodepath = "./Updata/images/" . $_SESSION ['curEnterprise'] ['e_id'] . "/dimensioncode/productcode/" . $product_id . "/"; // 压缩包要创建的文件夹路径
		$zipfolder = "qrcode" . "_" . time (); 							// 生成压缩包文件夹名（推荐英文）
		$foldername = "product_logoqrcode_" . $product_id . ".png"; 	// 图片文件名
	
		$productinfo = array (
				'filepath' => $dimensioncodepath, 						// 压缩包要创建在哪个文件夹路径下
				'filename' => $zipfolder , 								// 压缩包的文件名（推荐英文）
				'filedata' => array (
						0 => array (
								'innerfoldername' => 'productqrcode', 	// 压缩包解压后的文件夹名称
								'innerfile' => array (
										0 => $dimensioncodepath . $foldername // 压缩包中的图片路径（相对路径）
								)
						)
				), // 要压缩的文件夹信息
		);
		$fileinfo [0] = $productinfo; // 放到下标为0中
		$drz = A ( 'Service/DownloadRarZip' ); // 实例化压缩控制器
		$createresult = $drz->create_download_zip ( $fileinfo, $direct2browser );
		$this->ajaxReturn ( $createresult );
	}
	
	/**
	 * 云总店的商品类导航请求。
	 */
	public function cloudNav() {
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
		$sql = "SELECT nav_id, nav_name FROM t_navigation where e_id = '" . $e_id . "' and (nav_type = 2 or nav_type = 5)
        and (nav_level = 2 or (nav_level = 1 and nav_id not in (select father_nav_id from t_navigation where e_id = '" . $e_id . "' and nav_level = 2 and (nav_type = 2 or nav_type = 5) and is_del = 0))) order by nav_level asc, nav_order asc;";
		
		$navlist = M ()->query ( $sql );
		$this->ajaxresult = array (
				'errCode' => 0, 
				'errMsg' => "ok", 
				'data' => array (
						'navlist' => $navlist, // 查询到
				) 
		);
		$this->ajaxReturn ( $this->ajaxresult );
	}
	
	/**
	 * 云总店商品上架到导航的确认。
	 */
	public function cloudOnshelf() {
		if (! IS_POST) $this->error ( "Sorry, 404 Error!" );
		
		// 接收参数
		$onshelflist = I ( 'onshelflist' ); // 要上架的商品列表
		$nav_id = I ( 'navid', '-1' ); // 要上架到哪个导航
		$nav_name = I ( 'navname', '默认分类' ); // 要上架到的导航名称
		
		// 检测参数完整性
		if (empty ( $onshelflist )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "上架失败，缺少要上架的商品编号参数！";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		if (empty ( $nav_id ) || $nav_id == "-1") {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "上架失败，缺少要上架的导航编号参数！";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 进行商品上架处理
		$onshelfmap = array (
				'product_id' => array ( "in", $onshelflist ), 
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$onshelfinfo = array (
				'nav_id' => $nav_id, // 上架的导航编号
				'nav_name' => $nav_name, // 上架的导航名字
				'on_shelf' => 1, // 上架状态
				'onshelf_time' => time (), // 上架时间
		);
		$onshelfresult = M ( 'product' )->where ( $onshelfmap )->save ( $onshelfinfo ); // 上架商品到云总店的特定导航下
		if ($onshelfresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "上架失败，网络繁忙，请不要重复提交！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回参数给前台
	}
	
	/**
	 * 获得分店列表。
	 */
	public function getSubbranch() {
		if (! IS_POST) $this->error ( "Sorry, 404 Error!" );
		
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 
				'is_del' => 0 
		);
		$subbranchlist = M ( 'subbranch' )->where ( $emap )->select (); // 分店列表
		
		$this->ajaxresult = array (
				'errCode' => 0, 
				'errMsg' => "ok", 
				'data' => array (
						'subbranchlist' => $subbranchlist, // 分店列表
				),
		);
		$this->ajaxReturn ( $this->ajaxresult ); // 返回参数给前台
	}
	
	/**
	 * 条件查询分店列表
	 */
	public function querySubbranch() {
		if (! IS_POST) $this->error ( "Sorry, 404 Error!" );
		
		$subbranchlist = array (); // 查询出来的店铺分店列表
		$querymap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 
				'is_del' => 0 
		);
		
		// 接受发送过来的参数
		$province = I ( 'province' ); 
		$city = I ( 'city' );
		$region = I ( 'region' );
		$sname = I ( 'sname' );
		// 处理参数
		if (empty ( $province ) && empty ( $city ) && empty ( $region ) && empty ( $sname )) {
			// 如果4个参数都空，表示查询所有分店，这里不在对查询条件做任何限制
		} else {
			if (! empty ( $province )) {
				$querymap ['province'] = array ( "like", "%" . $province . "%");
			}
			if (! empty ( $city )) {
				$querymap ['city'] = array ( "like", "%" . $city . "%");
			}
			if (! empty ( $region )) {
				$querymap ['county'] = array ( "like", "%" . $region . "%");
			}
			if (! empty ( $sname )) {
				$querymap ['subbranch_name'] = array ( "like", "%" . $sname . "%");
			}
		}
		
		$querylist = M ( 'subbranch' )->where ( $querymap )->select (); // 按需要查询分店列表
		if ($querylist) {
			$subbranchlist = $querylist; // 如果有店铺数据，给到subbranchlist
		}
		
		$this->ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => array (
						'subbranchlist' => $subbranchlist, // 分店列表
				),
		);
		$this->ajaxReturn ( $this->ajaxresult ); // 返回参数给前台
	}
	
	/**
	 * 批量设置商品类别：精选、折扣和新品。
	 */
	public function setProductTag() {
		$tag = I ( "tag" ); // 接收设置的标签
		
		$promap = array(
				'product_id' => array ( "in", explode ( ",", I ( 'pidlist' ) ) ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		
		$producttable = M ( 'product' );
		$taginfo = array (
				'latest_modify' => time (), // 最后一次设置时间
		);
		if ($tag == "feature") {
			$taginfo ['is_feature'] = 1;
		} else if ($tag == "preferential") {
			$taginfo ['is_preferential'] = 1;
		} else if ($tag == "new") {
			$taginfo ['is_new'] = 1;
		}
		$settagresult = $producttable->where ( $promap )->save ( $taginfo );
		if ($settagresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "设置失败，请不要重复提交。";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 取消商品的标签。
	 */
	public function cancelProductTag() {
		$tag = I ( "tag" ); // 接收设置的标签
		$product_id = I ( 'pid' ); // 接收商品的编号
		
		if (empty ( $product_id )) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "取消设置失败，需要的商品编号参数不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		$promap = array(
				'product_id' => $product_id,
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$producttable = M ( 'product' );
		$taginfo = array (
				'latest_modify' => time (), // 最后一次设置时间
		);
		if ($tag == "feature") {
			$taginfo ['is_feature'] = 0;
		} else if ($tag == "preferential") {
			$taginfo ['is_preferential'] = 0;
		} else if ($tag == "new") {
			$taginfo ['is_new'] = 0;
		}
		$canceltagresult = $producttable->where ( $promap )->save ( $taginfo );
		if ($canceltagresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "取消设置失败，请不要重复提交。";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 条件查询。
	 */
	public function conditionSearch() {
		$labeltag = I ( 'labeltag' ); 
		$pnum = I ( 'pnum' ); 
		$pname = I ( 'pname' ); 
		$storage = I ( 'storage' );
		$sellamount = I ( 'sellamount' ); 
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
	
		// 根据不同查询条件定义searchmap
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		
		// 过滤标签
		if ($labeltag == "1") {
			$searchmap ['is_feature'] = 1; // 精选
		} else if ($labeltag == "2") {
			$searchmap ['is_new'] = 1; // 新品
		} else if ($labeltag == "3") {
			$searchmap ['is_preferential'] = 1; // 折扣
		}
		
		// 过滤商品编号
		if (! empty ( $pnum )) {
			$searchmap ['product_number'] = array ( "like", "%" . $pnum . "%" ); // 商品编号
		}
		
		// 过滤商品名称
		if (! empty ( $pname )) {
			$searchmap ['product_name'] = array ( "like", "%" . $pname . "%" ); // 商品名称
		}
		
		// 过滤库存
		if ($storage == "1") {
			$searchmap ['_string'] = "storage_amount - sell_amount <= storage_warn"; // 库存量减去卖出量小于库存预警
		}
		
		// 过滤卖出量
		if (! empty ( $sellamount )) {
			$searchmap ['sell_amount'] = array ( 'egt', $sellamount );
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
		$proinfolist = $this->checkProductSkuWarn ( $proinfolist ); // 检查库存是否报警
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * 商品移入回收站。
	 */
	public function recycleProduct() {
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array ( "in", explode ( ",", I ( 'pidlist' ) ) ), 
				'is_del' => 0
		);
		$recycleresult = M ( 'product' )->where ( $promap )->setField ( "is_del", 1 );
		if ($recycleresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "删除回收商品失败，请不要重复提交。";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 上传csv表格。
	 */
	public function csvBatchHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/csvbatchimport/product/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUploadCSV ( $savepath ) );
	}
	
	/**
	 * 批量处理商品上传ajax提交。
	 */
	public function batchProductHandle() {
		$csvpath = I ( 'csvpath' );
		
		// 没有接收到文件直接毙掉
		if (empty ( $csvpath )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "要批量处理的csv文件路径不能为空。";
			$this->ajaxReturn ( $this->ajaxresult ); // 立刻返回给前台
		}
		
		// 开始处理文件
		
		// 导入类库
		vendor ( 'PHPExcel.PHPExcel' );
		vendor ( 'PHPExcel.PHPExcel.IOFactory' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel5' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel2007' );
		
		// 加载excel文件
		$inputfilename = $_SERVER ['DOCUMENT_ROOT'] . $csvpath; // 相对路径
		$extension = 'csv'; // 处理csv表格
		$objReader = null;
		
		switch ($extension) {
			case 'xls' :
				$objReader = new PHPExcel_Reader_Excel5 ();
				break;
			case 'xlsx' :
				$objReader = new PHPExcel_Reader_Excel2007 ();
				break;
			case 'csv' :
				$objReader = new PHPExcel_Reader_CSV (); 
				break;
			default :
				$this->error ( '上传的文件类型不匹配，无法识别！' );
				break;
		}
		setlocale(LC_ALL,NULL);
		// csv文件表格预设
		$objReader->setDelimiter(',')->setInputEncoding('GBK')->setEnclosure('"')->setLineEnding("\r\n")->setSheetIndex(0);
		$currentsheet = $objReader->load ( $inputfilename )->getActiveSheet (); 	// 获取活动工作薄
		$allcolumn = $currentsheet->getHighestColumn ();							// 获取最大列数
		$allrow = $currentsheet->getHighestRow ();									// 获取最大行数
		$allcolumnindex = PHPExcel_Cell::columnIndexFromString ( $allcolumn ); 		// 将列数的字母索引转换成数字（重要）
		
		$global = array();
		// 开始循环读取excel文件中的数据
		for($row = 1; $row <= $allrow; $row ++) {
			$singlerecord = array ();
			for($column = 0; $column < $allcolumnindex; $column ++) {
				//$cellName = PHPExcel_Cell::stringFromColumnIndex($column) . $row; // 转字母列
				//$currentsheet->getStyle ( $cellName )->getNumberFormat ()->setFormatCode ( PHPExcel_Cell_DataType::TYPE_STRING); // 写有效
				//$currentsheet->getStyle($cellName)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT); // 写有效
				//number_format ( $global [1] [3], '', '', '' ); // 条形码直接这么做，可以无逗号非科学计数法，但要确定在excel第几列
				$singlerecord [$column] = $currentsheet->getCellByColumnAndRow ( $column, $row )->getValue ();
				if ($column == 10 && $row > 2) {
					$singlerecord [$column] = number_format ( $singlerecord [$column], '', '', '' ); // 第十一列条形码去除科学计数法
				}
			}
			$global [$row - 1] = $singlerecord;
		}
		$batchresult = $this->addProBatch($global);
		$this->ajaxReturn ( $batchresult ); // 返回给前台
	}
	
	/**
	 * 全局导入变量。
	 * @var unknown
	 */
	protected $rule = array(
			0=>array(
					'Cname' => '商品编号',
					'Ename'=>'product_number',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			1=>array(
					'Cname' => '商品名称',
					'Ename'=>'product_name',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			2=>array(
					'Cname' => '性别',
					'Ename'=>'sex',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			3=>array(
					'Cname' => '质地',
					'Ename'=>'material',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			4=>array(
					'Cname' => '重量',
					'Ename'=>'weight',
					'type' =>'numeric',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			5=>array(
					'Cname' => '吊牌价',
					'Ename'=>'original_price',
					'type' =>'numeric',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			6=>array(
					'Cname' => '现售价',
					'Ename'=>'current_price',
					'type' =>'numeric',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			7=>array(
					'Cname' => 'SKU颜色',
					'Ename'=>'product_color',
					'type' =>'string',
					'length' => 5,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			8=>array(
					'Cname' => 'SKU尺码',
					'Ename'=>'product_size',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			9=>array(
					'Cname' => '库存量',
					'Ename'=>'storage_amount',
					'type' =>'numeric',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>1		//要求为整型
			),
			10=>array(
					'Cname' => '条形码',
					'Ename'=>'bar_code',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
			11=>array(
					'Cname' => '单位',
					'Ename'=>'units',
					'type' =>'string',
					'length' => 50,
					'isNull'=>1,	//要求为不空
					'isInt'=>0		//要求为整型
			),
	);
	
	/**
	 * 检测单元格是否合法，传入4个形参，
	 * $rowValue 单元格值，$type单元格值所属类型，$length单元格值受限长度，$isNull单元格数值是否为空
	 * 如果检测成功返回列数据，否则返回false
	*/
	public function checkCell($rowData,$type,$length,$isNull,$isInt){
		$returnData = array(
				'errCode' => 0,	//错误码
				'line' => 0,	//错误行号
				'type' => '无'	//错误类型
		);
		//$rowArray[$this->rule[$i]['Ename']]=array();	//转成key->value的形式
		for($i=0;$i<count($rowData);$i++){	//依次检测列数据里的每个值
			$rowValue=$rowData[$i];
			if($isNull){	//如果要求为不空
				if(strlen($rowValue)<1){	//以长度小于1来判断是否是空
					$returnData = array(
							'errCode' => 1000,
							'line' => $i+1,
							'type' => '输入不能为空'
					);
					return $returnData;
				}else{
					if($type=='numeric'){	//要求为数值型
						if(!is_numeric($rowValue)){
							$returnData = array(
									'errCode' => 1001,
									'line' => $i+1,	
									'type' => '输入要求为数值型'
							);
							return $returnData;
						}
						if(mb_strlen($rowValue,'utf-8')>$length){		//如果长度大于限度值
							$returnData = array(
									'errCode' => 1002,
									'line' => $i+1,
									'type' => '输入长度超过'.$length.'字符'
							);
							return $returnData;
						}
						if($rowValue+0<0){		//数值型要求大于等于0
							$returnData = array(
									'errCode' => 1005,
									'line' => $i+1,
									'type' => '输入要求大于等于0'
							);
							return $returnData;
						}
						if($isInt){
							$t1 = $rowValue;
							$t2 = intval($rowValue);
							if($t1!=$t2){		//对整型数值的判断
								$returnData = array(
										'errCode' => 1004,
										'line' => $i+1,
										'type' => '库存要求为整数'.($rowValue+0)
								);
								return $returnData;
							}
						}
						
					}
					if($type=='string'){	//要求为字符串型
						if(mb_strlen($rowValue,'utf-8')>$length){
							$returnData = array(
									'errCode' => 1002,
									'line' => $i+1,
									'type' => '输入长度超过'.$length.'字符'
							);
							return $returnData;
						}
					}
				}
			}else{	//要求为空
				if($type=='numeric'){
					if(!is_numeric($rowValue)){
						$returnData = array(
								'errCode' => 1001,
								'line' => $i+1,
								'type' => '输入要求为数值型'
						);
						return $returnData;
					}
					if(mb_strlen($rowValue,'utf-8')>$length){		//这里数字也有长度判断
						$returnData = array(
								'errCode' => 1002,
								'line' => $i+1,
								'type' => '输入长度超过'.$length.'字符'
						);
						return $returnData;
					}
					if($rowValue+0<0){		//数值型要求大于等于0
						$returnData = array(
								'errCode' => 1005,
								'line' => $i+1,
								'type' => '输入要求大于等于0'
						);
						return $returnData;
					}
					if($isInt){
						if(!is_int($rowValue+0)){		//对整型数值的判断
							$returnData = array(
									'errCode' => 1004,
									'line' => $i+1,
									'type' => '库存要求为整数'
							);
							return $returnData;
						}
					}
				}
				if($type=='string'){
					if(mb_strlen($rowValue,'utf-8')>$length){
						$returnData = array(
								'errCode' => 1002,
								'line' => $i+1,
								'type' => '输入长度超过'.$length.'字符'
						);
						return $returnData;
					}
				}
			}
		}
		return $returnData;
	}
	
	/**
	 * 转换数组格式,将2级下标从数字转换为关键字，并且将除第一列之外的数组组合成新的数组
	 * @param unknown $arrayCSV
	 * @return string
	 */
	public function changeNormalArray($arrayCSV){
		$formatArray = array();
		for($i=1;$i<count($arrayCSV);$i++){	//遍历每一行
			for($j=0;$j<count($arrayCSV[$i]);$j++){	//遍历每一列
				$formatArray [$i-1][$this->rule[$j]['Ename']]=$arrayCSV[$i][$j];
			}
		}
		return $formatArray;
	}
	
	/**
	 * 对上传的csv文件进行列名审核
	 */
	public function checkRowName($rowName){
		for($i=0;$i<count($rowName);$i++){
			if($rowName[$i]!=$this->rule[$i]['Cname']){
				//p($rowName[$i]);p($this->rule[$i]['Cname']);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 批量增加商品信息到数据库
	 * @param $arrayCSV从赵同学那里接收到二维数组，键名都是数字
	 * @return string
	 */
	public function addProBatch($arrayCSV){
		$csvLen = count($arrayCSV);
		if($csvLen < 2){	//如果只有列标题或者没有数据的情况
			$ajaxresult ['errCode'] = 10005;
			$ajaxresult ['errMsg'] = '您上传的csv文件无数据';
			$this->ajaxReturn($ajaxresult);
		}
		$csvRowNumber =count ( $arrayCSV [0] ) ;	//列数
		//审核列名的合法性
		$checkRowNameResult = $this->checkRowName($arrayCSV[0]);
		//p('$checkRo6wNameResult='.$checkRowNameResult);die;
		if(!$checkRowNameResult){
			$ajaxresult ['errCode'] = 10009;
			$ajaxresult ['errMsg'] = '您的csv文件标准列名没有对应，请下载标准的csv表格填写，请勿改动表头以及列序。';
			$this->ajaxReturn($ajaxresult);
		}
		//p($csvRowNumber);die;
		//验证所传数组数据是否合法
		for($i=0; $i<$csvRowNumber; $i++){	//	列循环,将循环次数少的放在外循环,$i表示列序
			$rowArray[$i] = array();	
			for($j=1;$j<$csvLen;$j++){	//遍历每一行获得列数组
				array_push($rowArray[$i],$arrayCSV[$j][$i]);
			}
			$type = $this->rule[$i]['type'];
			$length = $this->rule[$i]['length'];
			$isNull = $this->rule[$i]['isNull'];
			$isInt = $this->rule[$i]['isInt'];
			//p($type);p($length);p($isNull);
			$checkResult = $this->checkCell($rowArray[$i],$type,$length,$isNull,$isInt);	//传入列数据
			if($checkResult['errCode']!=0){
				$ajaxresult ['errCode'] = 10006;
				$ajaxresult ['errMsg'] = '第'.$checkResult['line'].'行'.'第'.($i+1).'列数据'.$checkResult['type'].'!';
				$this->ajaxReturn($ajaxresult);
			}
			//array_push($arrayNormalCSV,$checkResult);	//拼装标准数组格式
		}
		//验证部分完成之后，数组的
		$arrayNormalCSV = array();	//定义标准格式的csv数组,即二级键名为Ename
		$arrayNormalCSV= $this->changeNormalArray($arrayCSV);
		//p($arrayCSV);p($arrayNormalCSV);die;
		
		$ajaxresult ['errCode'] = 10001;
		$ajaxresult ['errMsg'] = '商品信息添加失败';
		$arrayProduct = array ();
		$arrayProSku = array();
		$arrayProImage = array();
		//$someFieldNull = 0;		//是否某些字段为空
		//$fieldNullLine = 0;		//空字段的行数
		$this->syncProBatch($arrayNormalCSV, $arrayProduct, $arrayProSku, $arrayProImage);
		//p($arrayProduct);p($arrayProSku);p($arrayProImage);die;
		$proTable = M ("product");
		$proSkuTable = M("productsizecolor");
		$proImageTable = M("productimage");
		// 在$Auth模型中启动事务
		$proTable->startTrans ();
		$result1 = $proTable->addAll( $arrayProduct);
		$result2 = $proSkuTable->addAll($arrayProSku);
		$result3 = $proImageTable->addAll($arrayProImage);
		//p($result1);p($result2);p($result3);die;
		if ($result1 && $result2 && $result3){
			$proTable->commit ();
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
			return $ajaxresult;
		}else{
			$proTable->rollback ();
			return $ajaxresult;
		}
	}
	
	/**
	 * 商家批量上传商品，商品信息数组转化为3个数组，分别插入t_product,t_productsizecolor,t_productimage
	 * @param array $arrayNormalCSV CSV表中的信息转换为标准2数组格式
	 * @param array $arrayProduct 需要插入到t_product表中的信息
	 * @param array $arrayProSku 需要插入到t_productsizecolor表中的信息
	 * @return array $arrayProImage 需要插入到t_productimage表中的信息
	 */
	public function syncProBatch($arrayNormalCSV, &$arrayProduct, &$arrayProSku, &$arrayProImage) {
		$proCount = 0;	// t_product表的记录数
		$proSkuCount = 0;
		$proImageCount = 0;
		// 为了方便维持t_product表中记录的唯一性构造
		// key值 product_number Value值 相关的product_id
		$arrayProSet = array();
		$index = 0; 	//数组arrayCSV的第几个元素
		foreach( $arrayNormalCSV as $value)
		{
			$proNo = $value['product_number'];	// 读取表格中某一行的商品编号字段
			if( !isset ( $arrayProSet [$proNo] ))	//因为一件产品有多条sku，所以要进行一次判断，一款商品有一个商品信息，一个商品图片信息，但是有多个sku信息
			{
				// 如果该字段在hash表中不存在,则说明是新的商品编号,需要进行如下操作:
				// 1、生成一条相应的t_product记录
				// t_product记录
				$arrayProduct[$proCount]['product_id'] = md5 ( uniqid ( rand (), true ) );
				$arrayProduct[$proCount]['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
				$arrayProduct[$proCount]['nav_id'] = "-1";
				$arrayProduct[$proCount]['nav_name'] = "未分类";
				$arrayProduct[$proCount]['product_type'] = 2;
				$arrayProduct[$proCount]['product_number'] = $value['product_number'];
				$arrayProduct[$proCount]['product_name'] = $value['product_name'];
				$arrayProduct[$proCount]['sex'] = $value['sex'];
				$arrayProduct[$proCount]['material'] = $value['material'];
				$arrayProduct[$proCount]['original_price'] = $value['original_price'];
				$arrayProduct[$proCount]['current_price'] = $value['current_price'];
				$arrayProduct[$proCount]['units'] = $value['units'];
				$arrayProduct[$proCount]['storage_amount'] = 0;
				$arrayProduct[$proCount]['sell_amount'] = 0;
				$arrayProduct[$proCount]['add_time'] = time();
				$arrayProduct[$proCount]['latest_modify'] = time();
				$arrayProduct[$proCount]['on_shelf'] = 0;
				$arrayProduct[$proCount]['onshelf_time'] = "-1";
				$arrayProduct[$proCount]['logistics'] = 0;
				$arrayProduct[$proCount]['postage'] = 0;
				$arrayProduct[$proCount]['weight'] = $value['weight'];
				$arrayProduct[$proCount]['is_del'] = 0;
				// 2、将该商品编号以及product_id存入到hash表$arrayProSet中，方便下次比对
				$arrayProSet[$proNo] = $arrayProduct[$proCount]['product_id'];
				// 3、生成一条相应的t_productimage记录
				// t_productimage记录
				$arrayProImage[$proImageCount]['product_image_id'] = md5 ( uniqid ( rand (), true ) );
				$arrayProImage[$proImageCount]['product_id'] = $arrayProSet[$proNo];
				$arrayProImage[$proImageCount]['macro_path'] = "/weact/APP/Modules/Admin/Tpl/Public/images/platformimage/clothesdefault.png";
				$arrayProImage[$proImageCount]['micro_path'] = "/weact/APP/Modules/Admin/Tpl/Public/images/platformimage/clothesdefault.png";
				$arrayProImage[$proImageCount]['add_time'] = time();
				$arrayProImage[$proImageCount]['is_del'] = 0;
				$proCount++;
				$proImageCount++;
			}
			/* 此处针对于新的sku，生成一条新的t_productsizecolor记录
			 * 1、此处商品的颜色、尺寸一样的不重复处理
			 */
			$arrayProSku[$proSkuCount]['sizecolor_id'] = md5 ( uniqid ( rand (), true ) );
			$arrayProSku[$proSkuCount]['product_id'] = $arrayProSet [$proNo];
			$arrayProSku[$proSkuCount]['product_color'] = $value['product_color'];	// 颜色
			$arrayProSku[$proSkuCount]['product_size'] = $value['product_size'];	// 尺寸
			$arrayProSku[$proSkuCount]['size_order'] = 0;
			$arrayProSku[$proSkuCount]['storage_amount'] = $value['storage_amount'];
			$arrayProSku[$proSkuCount]['sell_amount'] = 0;
			$arrayProSku[$proSkuCount]['bar_code'] = $value['bar_code'];
			//$arrayProSku[$proSkuCount]['scanpay_id'] = ;
			$arrayProSku[$proSkuCount]['remark'] = "";
			$arrayProSku[$proSkuCount]['is_del'] = 0;
			$proSkuCount++;
		}
	}
	
}
?>