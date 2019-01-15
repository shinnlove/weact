<?php
/**
 * 餐饮管理ajax请求控制器。
 * @author 胡福玲。
 */
class CateManageRequestAction extends PCRequestLoginAction {
	/**
	 * easyUI的post请求，初始化读取餐饮数据
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/CateManage/cateView', '', '', true ) );
			
		$cinmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],                          					// 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$cinview = M ( 'cate_image_nav' );
		$cateinfolist = array ();
		$cintotal = $cinview->where ( $cinmap )->count ();                                     				// 计算当前商家下不被删除餐饮商品的总数
		if($cintotal){
			$cateinfolist = $cinview->where ( $cinmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			$catenum = count($cateinfolist);
			for($i = 0; $i<$catenum; $i++){
				$cateinfolist [$i] ['add_time'] = timetodate ( $cateinfolist [$i] ['add_time'] ); 			// 添加商品时间转为可视化
				$cateinfolist [$i] ['latest_modify'] = timetodate ( $cateinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$cateinfolist [$i] ['onshelf_time'] = timetodate( $cateinfolist [$i] ['onshelf_time'] );
				$cateinfolist [$i] ['macro_path'] = assemblepath( $cateinfolist [$i] ['macro_path']);
				$cateinfolist [$i] ['micro_path'] = assemblepath( $cateinfolist [$i] ['micro_path']);
			}
		}
	
		$json = '{"total":' . $cintotal . ',"rows":' . json_encode ( $cateinfolist ) . '}'; 			// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 增加餐饮商品信息post函数处理。
	 */
	public function addCateConfirm() {
		$catedata = array (
				'cate_id' => I ('cid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ('cun'),
				'cate_name' => I ( 'cna' ),
				'cate_type' => I ( 'cct' ),
				'unit' => I ( 'cuu' ),
				'unit_name' => I ( 'una' ),
				'price' => I ( 'cpr' ),
				'member_price' => I ( 'mpr' ),
				'brief_description' => I ( 'brd' ),
				'recommend_level' => I ( 'crl' ),
				'hot_level' => I ( 'chl' ),
				'add_time' => time (),
				'is_new' => I('checked'),
				'off_shelf' => 1, 										// 新添加商品默认处于下架状态
				'description' => stripslashes ( &$_REQUEST ['des'] ) 	// &$_REQUEST转义的接收，再用stripcslashes删除多余的转义斜杠
		);
		$res1 = M ( 'cate' )->add ($catedata);
	
		$cateimg = array (
				'cate_image_id' => md5 ( uniqid ( rand (), true ) ),
				'cate_id' => $catedata ['cate_id'],
				'macro_path' => I('mip'),
				'micro_path' => str_replace('/weact', '', I('mii'))
		);
		$res2 = M ( 'cateimage' )->add ($cateimg);
	
		$ajaxresult = array (); 										// 要返回的ajax信息
		if ($res1 && $res2) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '餐饮商品信息添加成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息添加失败，请检查网络状况!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 编辑餐饮商品信息post函数处理。
	 */
	public function editCateConfirm() {
		$ecdata = array(
				'cate_id' => I ('cid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_id' => I ('cun'),
				'cate_name' => I ( 'cna' ),
				'cate_type' => I ( 'cct' ),
				'unit' => I ( 'cuu' ),
				'unit_name' => I ( 'una' ),
				'price' => I ( 'cpr' ),
				'member_price' => I ( 'mpr' ),
				'brief_description' => I ( 'brd' ),
				'recommend_level' => I ( 'crl' ),
				'hot_level' => I ( 'chl' ),
				'latest_modify' => time (),
				'is_new' => I('checked'),
				'description' => stripslashes ( &$_REQUEST ['des'] )	// &$_REQUEST转义的接收，再用stripcslashes删除多余的转义斜杠
		);
	
		$ecmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'cate_id' => I('cid'),
				'is_del' => 0
		);
		$ecrst = M('cate')->where($ecmap)->save($ecdata);
	
		$flag = I('flag');
		if($flag){
			$ecimgdata = array (
					'macro_path' => I('mip'),
					'micro_path' => str_replace('/weact', '', I('mii'))
			);
			$ecimap = array(
					'cate_id' => I('cid'),
					'is_del' => 0
			);
			$ecirst = M('cateimage')->where($ecimap)->save($ecimgdata);
		}else {
			$ecirst = true;
		}
		$ajaxresult = array (); 				// 要返回的ajax信息
		if ($ecrst && $ecirst) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '餐饮商品信息更新成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '信息更新失败，请不要重复提交!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 删除餐饮商品信息post处理函数。
	 */
	public function delCate(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$delidlist = I ( 'rowdata' ); 			// 接收要删除的餐饮商品id列表
		$ctbl = M ( 'cate' ); 					// 餐饮商品表
		$citbl = M ( 'cateimage' ); 			// 餐饮商品图片表
	
		$delcmap = array(
				'cate_id' => array('in', $delidlist),
				'is_del' => 0
		);
	
		$delcimgresult = $citbl->where($delcmap)->setField('is_del', 1);
		$delcmap['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
		$delcresult = $ctbl->where($delcmap)->setField('is_del', 1);
		if($delcimgresult&&$delcresult){
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		}else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "删除商品失败，请不要重复删除！"
			);
		}
		$this->ajaxReturn( $ajaxinfo );			// 将结果返回给前台
	}
	
	/**
	 * 商品标注或取消标注人气的处理函数。
	 */
	public function cateMarkPop(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' );				// 接收参数
		$lable = I ( 'lable' );					// 接收操作类型
	
		// 定义要操作的范围
		$markmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'cate_id' => array( 'in', explode ( ',', $rowdata ) ),
				'is_del' => 0
		);
	
		$handleresult = false;					// 默认没成功
		if ($lable == "ok") {
			$okSave['is_popular'] = 1;			// 商品标注人气状态
			$handleresult = M ( 'cate' )->where ( $markmap )->save ( $okSave );
		} else if ($lable == "no") {
			$noSave['is_popular']= 0;			// 商品取消标注人气状态
			$handleresult = M ( 'cate' )->where ( $markmap )->save ( $noSave );
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
	 * 餐饮商品批量上下架ajax处理函数。
	 */
	public function cateFromShelf(){
		if (! IS_POST) _404 ( "Sorry, page not found!" );
	
		$rowdata = I ( 'rowdata' );				// 接收参数
		$type = I ( 'type' );					// 接收操作类型
	
		// 定义要操作的范围
		$uppromap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'cate_id' => array( 'in', explode ( ',', $rowdata ) ),
				'is_del' => 0
		);
	
		$handleresult = false;					// 默认没成功
		if ($type == "on") {
			$onSave = array (
					'off_shelf' => 0,			// 商品上架状态
					'onshelf_time' => time (), 	// 时间置为当前上架时间
			); 									// 定义商品上架参数
			$handleresult = M ( 'cate' )->where ( $uppromap )->save ( $onSave );
		} else if ($type == "off") {
			$offSave = array (
					'off_shelf' => 1, 			// 商品下架状态
					'onshelf_time' => -1, 		// 时间置为-1
			); 									// 定义商品下架参数
			$handleresult = M ( 'cate' )->where ( $uppromap )->save ( $offSave );
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
	 * 搜索查询函数
	 */
	public function searchCate(){
	
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/CateManage/cateView', '', '', true ) );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 			// 接收查询条件
		$content =  I ( 'searchcontent' ) ;				// 接收查询内容
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		
		if($condition == "cate_name" || $condition == "nav_name"){
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		}else if($condition == "cate_type"){
			if($content == '食品' || $content == '食品类'){
				$searchmap [$condition] = '0';
			}else if($content == '饮料' || $content == '饮料类'){
				$searchmap [$condition] = '1';
			}else {
				$searchmap [$condition] = '2';
			}
		}
		
		$cinview = M ( 'cate_image_nav' );
		$cateinfolist = array ();
		$cintotal = $cinview->where ( $searchmap )->count ();                                     	// 计算当前商家下不被删除餐饮商品的总数
		if($cintotal){
			$cateinfolist = $cinview->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			$catenum = count($cateinfolist);
			for($i = 0; $i< $catenum; $i++){
				$cateinfolist [$i] ['add_time'] = timetodate ( $cateinfolist [$i] ['add_time'] ); 	// 添加商品时间转为可视化
				$cateinfolist [$i] ['onshelf_time'] = timetodate( $cateinfolist [$i] ['onshelf_time'] );
				$cateinfolist [$i] ['micro_path'] = assemblepath( $cateinfolist [$i] ['micro_path']);
			}
		}
		$json = '{"total":' . $cintotal . ',"rows":' . json_encode ( $cateinfolist ) . '}'; 	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 上传封面图片并插入数据库的函数。
	 * @return null | array fileinfo	如果上传成功，返回$fileinfos的信息；如果失败，什么都不返回。
	 */
	public function cateImgUpload(){
		$saveFilePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/cateimage/banner/';
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUpload ( $saveFilePath );
		$this->ajaxReturn ( $uploadresult );
	}
	
	/**
	 * 编辑餐饮商品详情描述中图片的上传。
	 * 特别注意：如果使用ueditor的传参方式，只能使用$_REQUEST原生态PHP来接收传参。
	 */
	public function ueImageUpload() {
		$savePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/cateimage/' . $_REQUEST ['cate_id'] . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' );                                                                          // 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath );                                                                     // 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
}
?>