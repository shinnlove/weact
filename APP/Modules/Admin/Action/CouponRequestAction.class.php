<?php
/**
 * 优惠券请求控制器。
 * @author Shinnlove
 *
 */
class CouponRequestAction extends PCRequestLoginAction {
	/**
	 * 读取店铺现有的优惠券。
	 */
	public function readMyCoupon(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/Coupon/shopCoupon', '', '', true ) ); // 防止恶意打开
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
	
		$couponmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		$coupontbl = M ( 'coupon' );
		$total = $coupontbl->where($couponmap)->count ();
	
		$couponlist = array ();
		if($total){
			$couponlist = $coupontbl->where($couponmap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
			$couponnum = count($couponlist);
			for ($i = 0; $i<$couponnum; $i++){
				$couponlist[$i]['add_time'] = timetodate($couponlist[$i]['add_time']);
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $couponlist ) . '}';
		echo $json;
	}
	
	/**
	 * 获取商品数据（暂时已经不用）
	 */
	public function read() {
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$product = M ( "product_image_list" );
		$total = $product->where ( 'is_del=0 and coupon_id is null and e_id='.$current_enterprise['e_id'] )->count (); // 计算总数
		$productlist = array ();
	
		$productlist = $product->where ( 'is_del=0 and coupon_id is null and e_id='.$current_enterprise['e_id'])->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $productlist ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	// 第一步，添加购物券信息
	public function addCoupon() {
		$current_enterprise = session ( 'curEnterprise' );
		$coupon_id = md5 ( uniqid ( rand (), true ) );
		$data = array (
				'coupon_id' => $coupon_id,
				'coupon_name' => I( 'coupon_name' ),
				'e_id' => $current_enterprise ['e_id'],
				'start_time' => I ( 'start_time' ),
				'end_time' => I ( 'end_time' ),
				'add_time' => date ( 'Y-m-d H:i:s' ),
				'denomination' => I ( 'denomination' ),
				'lowest_consume' => I ( 'lowest_consume' ),
				'publish_amount' => I ( 'publish_amount' ),
				'circulation' => I ( 'circulation' ),
				'original_price_only' => I ( 'original_price_only' ),
				'coupon_cover' => I ( 'coupon_cover' ),
				'advertise' => I ( 'advertise' ),
				'instruction' => I ( 'instruction' ),
				'remark' =>I('remark')
		);
		//p($data);die;
		$coupon = M ( 'coupon' );
		$coupon->create ( $data );
		$result = $coupon->add ();
		if($result)
			$this->ajaxReturn ( array ( 'status' => 1, 'coupon_id' => $coupon_id ), 'json' );
		else
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
	}
	
	//读取用户领取的优惠券
	public function readUserCoupon(){
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$searchcondition = I('searchcondition');
		$searchcontent = I('searchcontent');
		if($searchcontent == null || $searchcontent =='')
			$search = ' and 1=1 ';
		else $search = ' and '.$searchcondition.' like \'%'.$searchcontent.'%\'';
		$customer_coupon = M ( "customer_coupon" );
		$total = $customer_coupon->where('e_id='.$current_enterprise['e_id'].$search)->count (); // 计算总数
		$customercouponlist = array ();
		if($total){
			$customercouponlist = $customer_coupon->where('e_id='.$current_enterprise['e_id'].$search )->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $customercouponlist ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 分店信息初始化。
	 */
	public function subbranchInit(){
		$branchmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'closed_status' => 0,
				'is_del' => 0
		);
		$sbtable = M('subbranch');
		$total = $sbtable->where($branchmap)->count();
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$branchlist = array ();
		$branchlist = $sbtable->where( $branchmap )->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
	
		if($branchlist){
			$jsoninfo = '{"total":' . $total . ',"rows":' . json_encode ( $branchlist ) . '}';
		}
	
		echo $jsoninfo;
	}
	
	public function enteringCoupon(){
		$current_enterprise = session ( 'curEnterprise' );
		$data = array (
				'coupon_code' => I( 'coupon_code' ),
				'coupon_password' => I('coupon_password'),
				'e_id' => $current_enterprise ['e_id'],
				'customer_id' => I ( 'customer_id' ),
				'original_price_only' => I ( 'original_price_only' ),
				'entering_person' => I ( 'entering_person' ),
				'remark' =>I('remark')
		);
	
		if (! $data) {
			$this->ajaxReturn ( array ( 'status' => 2 ), 'json' );
		} else {
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		}
	}
	
	/**
	 * 获得树形展开商品。
	 */
	public function getTreeProduct() {
		$navID = I ( 'id' );					//这个id是系统指定的，不要更改，根据treeField字段来变化。
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'latest_modify'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$result = array ();											//因为easyUI的treegrid会递归的读取数据，所以这里要清空最终导航结果数组
		
		if ($navID == '-1') {
			// 查询已上架商品所有的导航
			$producttable = M ( 'product' ); // 实例化商品表
			$categorylist = array (); // 商品类别
			$categorymap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 
					'nav_id' => array ( "neq", "-1" ), // 已经分类的，排除未分类
					'is_del' => 0
			);
			$categorytotal = $producttable->where ( $categorymap )->group ( 'nav_name' )->count (); // 统计导航数量
			if ($categorytotal) {
				$categorylist = $producttable->where ( $categorymap )->group ( 'nav_name' )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 导航名字
				$categorycount = count ( $categorylist ); // 统计导航数量
				for($i = 0; $i < $categorycount; $i ++) {
					// 索引匹配导航的图标
					
					$navigationlist = $this->getProductNavInfo (); // 带索引的导航列表
					
					$currentnavid = $categorylist [$i] ['nav_id']; // 取出当前循环的导航编号
					
					$categorylist [$i] ['product_id'] = $currentnavid; 
					$categorylist [$i] ['micro_path'] = assemblepath ( $navigationlist [$currentnavid] ['nav_image_path'] );
					$categorylist [$i] ['product_number'] = '';
					$categorylist [$i] ['product_name'] = '';
					$categorylist [$i] ['sex'] = '';
					$categorylist [$i] ['original_price'] = '';
					$categorylist [$i] ['current_price'] = '';
					$categorylist [$i] ['units'] = '';
					$categorylist [$i] ['storage_amount'] = '';
					$categorylist [$i] ['sell_amount'] = '';
					$categorylist [$i] ['create_time'] = '';
				}
			}
			
			// 打包数据
			$items = array ();
			foreach ($categorylist as $row){
				$row ['state'] = 'closed';
				$row ['has_info'] = 1;
				array_push ( $items, $row );
			}
			$result ["rows"] = $items;
			$result ['total'] = $categorytotal;				//统计商品分类数目
		} else {
			// 查询树形导航展开下的商品
			$navmap = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 
					'nav_id' => $navID, 
					'is_del' => 0
			);
			$productlist = M ( 'product_image' )->where ( $navmap )->group ( 'product_id' )->order ( '' . $sort . ' ' . $order )->select (); // 导航下商品列表
			$productcount = count ( $productlist ); // 统计商品数量
			if ($productcount) {
				for($i = 0; $i < $productcount; $i ++) {
					$productlist [$i] ['micro_path'] = assemblepath ( $productlist [$i] ['micro_path'] ); // 组装图片路径
				}
			}
			
			$items = array ();
			foreach ($productlist as $row){
				$row ['state'] = 'open';
				$row ['has_info'] = 0;						//子级导航下是否存在信息或商品
				array_push ( $items, $row );
			}
			$result = $items;
		}
		echo json_encode($result);
	}
	
	/**
	 * 获取商品类导航图标。
	 * @return array $formatnavlist 格式化后的导航
	 */
	private function getProductNavInfo() {
		$formatnavlist = array (); // 格式化后的导航
		
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 
				'_string' => "nav_type = 2 or nav_type = 5", // 已经分类的，排除未分类
				'is_del' => 0
		);
		$navigationlist = M ( 'navigation' )->where ( $emap )->order ( 'nav_order asc' )->select ();
		$navcount = count ( $navigationlist ); // 统计nav数量
		for($i = 0; $i < $navcount; $i ++) {
			$formatnavlist [$navigationlist [$i] ['nav_id']] = array (
					'nav_id' => $navigationlist [$i] ['nav_id'], // 导航编号
					'nav_name' => $navigationlist [$i] ['nav_name'], // 导航名字
					'nav_image_path' => assemblepath ( $navigationlist [$i] ['nav_image_path'] ), // 导航图片
			);
		}
		return $formatnavlist;
	}
	
	/**
	 * 编辑优惠券使用详情图文的上传。
	 * 使用ueditor富文本编辑器上传优惠券使用详情图文的函数。
	 * 特别注意：如果使用ueditor的传参方式，只能使用$_REQUEST原生态PHP来接收传参。
	 * Author：赵臣升。
	 * CreateTime：2014/10/21 19:33:25.
	 */
	public function instructionImageHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/coupon/' . $_REQUEST['cid'] . '/'; 	// 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); 											// 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); 										// 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/*
	 * ==========以下是5.26临时使用的最简洁版本优惠券===========
	 */
	
	/**
	 * 添加优惠券简要信息。
	 */
	public function addBriefCouponCfm(){
		$briefdata = array (
				'coupon_id' => I('cid'),
				'coupon_type' => I( 'ct' ),
				'o2o_type' => 2,//1、线下优惠券；2、线上优惠券；3、线下线上通用优惠券。
				'original_price_only' => 0,//优惠券价格限制：1代表只有正价商品才能使用，0是默认值。
				'publish_type' => 1,//优惠券的发放方式：1、注册线上会员；2、绑定实体会员卡；3、抽奖活动获得；4、店铺指定发放。默认0是暂不进行任何操作，后台统一推送。
				'coupon_name' => I( 'cn' ),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'denomination' => I ( 'dn' ),
				'discount' => I ( 'cdis' ),
				'lowest_consume' => I ( 'lc' ),
				'publish_amount' => I ( 'pa' ),
				'add_time' => time(),
				'start_time' => I ( 'st' ),
				'end_time' => I ( 'et' ),
				'coupon_cover' => I ( 'ip' ),
				'advertise' => I ( 'aw' ),
				'instruction' => stripslashes ( &$_REQUEST ['ins'] ), 	// &$_REQUEST转义的接收，再用stripcslashes删除多余的转义斜杠
				'remark' =>I('cd')
		);
		$addcouponresult = M ( 'coupon' )->add($briefdata);
	
		$ajaxinfo=array();
		if ($addcouponresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "添加失败，请检查网络状况！"
			);
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/**
	 * 删除优惠券
	 */
	public function delMyCoupon(){
		$e_id = $_SESSION['curEnterprise']['e_id'];
		$c_id = I('coupon_id');
		$delmap = array(
		 	'coupon_id' =>$c_id,
		 	'e_id' => $e_id
		);
		$delres1 = M('coupon')->where($delmap)->setField('is_del','1');
		$delres2 = M('customercoupon')->where($delmap)->setField('is_del','1');
		$this->redirect("Admin/Coupon/shopCoupon");
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/Coupon/couponCover/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
	
	/*
	 * =====================小万，优惠券，添加优惠券==================
	 */
	
	/**
	 * 完整版添加优惠券确认
	 */
	public function addCouponConfirm(){
		//p(I());die;
		$stepfirstarray = json_decode ( stripslashes ( $_POST ['stepfirst'] ), true );
		$stepsecondarray = json_decode ( stripslashes ( $_POST ['stepsecond'] ), true );
		//p($stepfirstarray);p($stepsecondarray);die;
		$coupondata = array (
				'coupon_id' => md5 ( uniqid ( rand (), true ) ),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'coupon_type' => $stepfirstarray['ctype'],
				'o2o_type' => $stepfirstarray['o2otype'],//1、线下优惠券；2、线上优惠券；3、线下线上通用优惠券。
				'circulation' => $stepfirstarray['o2oshoptype'], //线下优惠券流通度，整形。1、全国各店通用；2、全国各店独立使用；3、部分门店通用；4、部分门店独立使用。该字段可空，线上优惠券没有流通度。
				'original_price_only' => $stepfirstarray['pricelimit'],//优惠券价格限制：1代表只有正价商品才能使用，0是默认值。
				'publish_type' => 1,//优惠券的发放方式：1、注册线上会员；2、绑定实体会员卡；3、抽奖活动获得；4、店铺指定发放。默认0是暂不进行任何操作，后台统一推送。
				'coupon_name' => $stepsecondarray['coupon_name'],
				'denomination' => $stepsecondarray['denomination'],
				'discount' => $stepsecondarray['discount'],
				'lowest_consume' => $stepsecondarray['lowest_consume'],
				'limit_person' => $stepsecondarray['limit_person'],	//限制优惠券可领取的人数，默认0代表不限制可领人数
				'limit_perget' => $stepsecondarray['limit_perget'] , 	//限制每次领取几张，默认1张，可以领更多张
				'add_time' => time(),
				'obsolete_type' => isset($stepsecondarray['obsolete_type'])?$stepsecondarray['obsolete_type']:0,	//优惠券过期（失效）方式：0、正常状态过期方式，从起始时间到结束时间；1、计时过期方式，从领取优惠券后计时，一段时间内才能使用。
				'coupon_cover' => $stepsecondarray['imgpath'],
				'advertise' => $stepsecondarray['advertise'],
				'instruction' => $stepsecondarray['instruction'], 	// &$_REQUEST转义的接收，再用stripcslashes删除多余的转义斜杠
				'remark' =>$stepsecondarray['remark'],
				//选择线上券适用的商品(来自easyui页面的选择)
				//选择线下券适用的店铺
		);
		if($stepsecondarray['obsolete_type']==0){
			$coupondata ['start_time'] = $stepsecondarray['start_time'];
			$coupondata ['end_time'] = $stepsecondarray['end_time'];
		}else{
			$coupondata ['obsolete_countdown'] = $stepsecondarray['obsolete_countdown'];
		}
		//p($coupondata);die;
		$coupon = M ( 'coupon' );
		$coupon->startTrans();
		$addcouponresult = $coupon->add($coupondata);
		if($coupondata['o2o_type']==1){	//将优惠券按线下和非线下来划分
			$couponsubbranchids = I('sids');	//获取商店ids字符串
			$couponsubbranchidsarray = explode(",",$couponsubbranchids);
			//将信息存储到优惠券分店表
			for($i=0;$i<count($couponsubbranchidsarray);$i++){
				$couponsubbranchdata[$i] = array(
						'couponsubbranch_id' => md5 ( uniqid( rand(), true ) ),
						'e_id' => $_SESSION['curEnterprise']['e_id'],
						'coupon_id' => $coupondata ['coupon_id'],
						'subbranch_id' => $couponsubbranchidsarray[$i],
						'add_time'=> $coupondata['add_time']
				);
			}
			$couponsubbranchresult = M('couponsubbranch') -> addAll($couponsubbranchdata);
		}else{
			$couponproductids = I('pids');	//获取商品ids字符串
			$couponproductidsarray = explode(",",$couponproductids);
			//将信息存储到优惠券商品表
			for($i=0;$i<count($couponproductidsarray);$i++){
				$couponproductdata[$i] = array(
						'couponproduct_id' => md5 ( uniqid( rand(), true ) ),
						'e_id' => $_SESSION['curEnterprise']['e_id'],
						'coupon_id' => $coupondata ['coupon_id'],
						'product_id' => $couponproductidsarray[$i],
						'add_time' => $coupondata['add_time']
				);
			}
			$couponproductresult = M('couponproduct') -> addAll($couponproductdata);
		}
	
	
		$ajaxinfo=array();
		//p($addcouponresult);p($couponsubbranchresult);die;
		if (($addcouponresult&&$couponsubbranchresult)||($addcouponresult&&$couponproductresult)) {
			$coupon->commit();
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$coupon->rollback();
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "添加失败，请检查网络状况！"
			);
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/**
	 * 查询优惠券使用店铺，返回datagrid数据
	 */
	public function couponUsedSubbranch(){
		$couponsubbranchmap = array(
				'coupon_id' => I('coupon_id')
				//'coupon_id' => 'be0e571da129ce1a8f2317c36bb76030'
		);
		$couponsubbranchresult = M('couponsubbranch') -> where($couponsubbranchmap) -> field('subbranch_id') -> select();
		//p($couponsubbranchresult);
		$subbranids = array();
		for($i=0;$i<count($couponsubbranchresult);$i++){
			array_push($subbranids,$couponsubbranchresult[$i]['subbranch_id']);
		}
		//p($subbranids);die;
		$subbranchmap = array(
				'subbranch_id' => array (
						'in' , $subbranids
				)
		);
		$total = M('subbranch') -> where($subbranchmap)->count();
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
		$branchlist = array ();
		$branchlist = M('subbranch')->where( $subbranchmap )->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		//$jsoninfo;
		if($branchlist){
			$jsoninfo = '{"total":' . $total . ',"rows":' . json_encode ( $branchlist ) . '}';
		}
		echo $jsoninfo;
	}
	
	/**
	 * 查询优惠券适用商品，返回datagrid数据
	 */
	public function couponUsedProduct(){
		//p(I());
		$couponproductmap = array(
				'coupon_id' => I('coupon_id')
				//'coupon_id' => 'be0e571da129ce1a8f2317c36bb76030'
		);
		$couponproductresult = M('couponproduct') -> where($couponproductmap) -> field('product_id') -> select();
		//p($couponproductresult);die;
		$productids = array();
		for($i=0;$i<count($couponproductresult);$i++){
			array_push($productids,$couponproductresult[$i]['product_id']);
		}
		$productmap = array(
				'product_id' => array (
						'in' , $productids
				)
		);
		$total = M('product') -> where($productmap)->count();
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
		$productlist = array ();
		$productlist = M('product')->where( $productmap )->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if($productlist){
			$jsoninfo = '{"total":' . $total . ',"rows":' . json_encode ( $productlist ) . '}';
		}
		//p($productlist);die;
		echo $jsoninfo;
	}	
	
}
?>