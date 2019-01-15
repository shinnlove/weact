<?php
/**
 * 后台管理分店的控制器。
 * @author 梁思彬。
 * @modifyauthor 胡福玲。
 * 控制器原使用表：enterpriselocation，现在使用分店表subbranch。
 */
class SubbranchRequestAction extends PCRequestLoginAction {
	
	/**
	 * 店铺所有分店easyUI的post函数。
	 */
	function readSubbranches() {
		if (! IS_POST) _404('Sorry, Http://404, Not Found.',U('Admin/Location/subbranches','','',true));
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$subbranchmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$sbtable = M ( 'subbranch' );
		$total = $sbtable->where ( $subbranchmap )->count (); // 计算总数
		$subbranchlist = array ();
	
		if($total){
			$subbranchlist = $sbtable->where ( $subbranchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$subnum = count($subbranchlist);
			for($i = 0; $i < $subnum; $i ++){
				$subbranchlist [$i] ['add_time'] = timetodate( $subbranchlist [$i] ['add_time'] );
				$subbranchlist [$i] ['image_path'] = assemblepath($subbranchlist [$i] ['image_path']);
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $subbranchlist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 删除分店的post处理函数。
	 */
	function delSubbranch() {
		if (! IS_POST) _404('Sorry, Http://404, Not Found.', "{:U('Admin/Location/subbranches')}");
		$delmap = array (
				'subbranch_id' => I ( 'sid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
	
		//检查欲删除的分店下是否还存在导购
		$sgtable = M ( 'shopguide' );
		$sglist = $sgtable->where ( $delmap )->select();
		$sgnum = count($sglist);
		$ajaxinfo = array();
		//若尚存在导购，返回先行删除导购
		if($sgnum>0){
			$ajaxinfo = array(
					'errCode' => 10001,
					'errMsg' => '该分店下尚存在导购信息，请先行删除导购！'
			);
		}else{
			$sbtable = M ( 'subbranch' );
			$sbresult = $sbtable->where ( $delmap )->setField('is_del', 1);
			if ($sbresult){
				$ajaxinfo = array(
						'errCode' => 0,
						'errMsg' => 'ok！'
				);
			} else{
				$ajaxinfo = array(
						'errCode' => 10002,
						'errMsg' => '网络繁忙，请稍后再试！'
				);
			}
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/**
	 * 添加分店确认。
	 * 设计思路：这里直接接收信息即可，主键已经生成，无需重新md5.
	 * ModifyTime：2014/10/19 16:04:25.
	 */
	public function addSubbranchConfirm() {
		$subbranchinfo = array(
				'subbranch_id' => I('sid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'province' => I('prov'),
				'city' => I('city'),
				'county'=>I('area'),
				'subbranch_address' => I('addr'),
				'longitude' => I('lngx'),
				'latitude' => I('laty'),
				'subbranch_type' => I('ct'),
				'subbranch_code' => I('sc'),
				'subbranch_name' => I('sn'),
				'manager' => I('sm'),
				'contact_number' => I('sp'),
				'image_path' => I('lop'),
				'signs_path' => I('sip'),
				'subbranch_brand' => $_SESSION ['curEnterprise'] ['brand'],
				'add_time' => time(),
				'subbranch_description' => stripslashes(&$_POST['sd'])			//&$_POST转义的接收，再用stripcslashes删除多余的转义斜杠
		);
		$sbtable = M('subbranch');
		$result = $sbtable->data($subbranchinfo)->add();
		if ($result){
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		} else{
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	/**
	 * 编辑分店信息模块的图片上传。
	 * 使用ueditor富文本编辑器上传分店详情图片的函数。
	 * 特别注意：如果使用ueditor的传参方式，只能使用$_REQUEST原生态PHP来接收传参。
	 * Author：赵臣升。
	 * CreateTime：2014/10/19 15:32:25.
	 */
	public function subbranchImageHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/subbranch/' . $_REQUEST['sid'] . '/'; 	// 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); 											// 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); 										// 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 提交编辑分店信息后处理post的函数。
	 */
	public function editSubbranchConfirm() {
		$editdata = array (
				'subbranch_id' => I('sid'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'province' => I('prov'),
				'city' => I('city'),
				'county'=>I('area'),
				'subbranch_address' => I('addr'),
				'longitude' => I('lngx'),
				'latitude' => I('laty'),
				'subbranch_type' => I('ct'),
				'subbranch_code' => I('sc'),
				'subbranch_name' => I('sn'),
				'manager' => I('sm'),
				'contact_number' => I('sp'),
				'image_path' => I('lop'),
				'signs_path' => I('sip'),
				'subbranch_brand' => $_SESSION ['curEnterprise'] ['brand'],
				'latest_modify' => time(),
				'subbranch_description' => stripslashes(&$_POST['sd']),			//&$_POST转义的接收，再用stripcslashes删除多余的转义斜杠
		);
		$sbtable = M ( 'subbranch' );
		$result = $sbtable->save ( $editdata );
		if ($result){
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		} else{
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	/**
	 * 分店开闭状态的处理函数。
	 */
	public function closeSubbranch() {
		$csmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'subbranch_id' => I ( 'sid' )
		);
		$sbtable = M ( 'subbranch' );
		$csdata = $sbtable->where ( $csmap )->find();
		if($csdata['closed_status']==0){
			$csdata['closed_status']=1;
		}else if ($csdata['closed_status']==1){
			$csdata['closed_status']=0;
		}
		$result = $sbtable->where ( $map )->save ( $csdata );
		if ($result){
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		} else{
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	/**
	 * 根据关键字查找店铺的处理函数。
	 */
	public function searchSubbranch(){
		if (! IS_POST) _404 ( "Sorry,页面不存在",U('Admin/Location/subbranches','','',true));
	
		$data = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'searchcondition' => I('searchcondition'),
				'searchcontent'	=> I('searchcontent')
		);
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$sbtable = M ( 'subbranch' );
		$sbmap = array(
				'e_id' => $data['e_id'],
				$data['searchcondition'] => array('like','%'.$data['searchcontent'].'%'),
				'is_del' => 0
		);
		$total = $sbtable->where ( $sbmap )->count (); // 计算总数
		$subbranchlist = array ();
	
		if($total){
			$subbranchlist = $sbtable->where ( $sbmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			for($i = 0; $i < count($subbranchlist); $i ++){
				$subbranchlist [$i] ['add_time'] = timetodate( $subbranchlist [$i] ['add_time'] );
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $subbranchlist ) . '}';
		echo $json;
	}
	
	/**
	 * 为已经存在的店铺追加二维码，本请求为ajax请求。
	 * 特别注意：店铺二维码是腾讯的二维码，扫了以后要关注公众号的！
	 */
	public function appendSubQRCode() {
	
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/Location/subbranches', '', '', true ) ); // 防止恶意打开
	
		// 定义ajax返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		//接收参数
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; 		// 取当前的企业编号，要反复用到
		$sub_id = I ( 'subid' ); 							// 接收店铺编号
		$direct2browser = I ( 'direct2browser', 0 );
	
		// 检验店铺信息是否真实有效
		if (empty ( $sub_id )) {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "要生成二维码的店铺编号不能为空。";
			$this->ajaxReturn ( $ajaxresult );
		}
	
		$subtable = M ( 'subbranch' );
		$existmap = array (
				'subbranch_id' => $sub_id,
				'e_id' => $e_id,
				'is_del' => 0
		);
		$subinfo = $subtable->where ( $existmap )->find ();	// 尝试找到这个店铺信息
		if (! $subinfo) {
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "当前商家下不存在的店铺信息，请及时更新！";
			$this->ajaxReturn ( $ajaxresult );
		}
	
		// 检验当前商家是否有一条用于扫码关注店铺的图文，如果没有，添加一条
		$weaccount = $_SESSION['curEnterprise']['wechat_account'];
		$submsgmap = array (
				'e_id' => $e_id,
				'msg_description' => "欢迎关注".$weaccount,
				'is_del' => 0
		);
		$msgnewsinfo = M ( 'msgnews' )->where ( $submsgmap )->find ();
		if (! $msgnewsinfo) {
			$msgmaintable = M ( 'msgnews' ); 			// 图文主表
			$msgdetailtable = M ( 'msgnewsdetail' ); 	// 图文子表
			// 生成一条欢迎的单图文
			$msgnewsmain = array (
					'msgnews_id' => md5 ( uniqid ( rand (), true ) ), 		// 生成主表主键
					'e_id' => $e_id,
					'add_time' => time (),
					'msg_use' => 2, 										// 被动扫码回复
					'msg_category' => 0, 									// 单图文
					'msg_description' => $submsgmap['msg_description'], 	// 图文描述
			); // 图文主信息
			$ename = $_SESSION ['curEnterprise'] ['e_name']; 				// 企业名字
			$msgnewsdetail = array (
					'msgnewsdetail_id' => md5 ( uniqid ( rand (), true ) ), 							// 生成子表主键
					'msgnews_id' => $msgnewsmain ['msgnews_id'], 										// 主表主键
					'title' => $submsgmap['msg_description'],
					'author' => $ename, 																// 企业名称
					'cover_image' => "http://www.we-act.cn/weact/APP/Modules/Admin/Tpl/Public/images/platformimage/shopQR.png",
					'main_content' => "欢迎扫描" . $ename . "店铺二维码，点击进入...",
					'link_url' => "http://www.we-act.cn/weact/WeMall/Store/storeList/sid/" . $sub_id, 	// 扫码跳转链接逛店铺
					'detail_order' => -1																// 单图文直接是封面图文
			); // 图文子信息
			// 插入信息
			$mainresult = $msgmaintable->add ( $msgnewsmain ); 				// 添加主表信息
			$detailresult = $msgdetailtable->add ( $msgnewsdetail ); 		// 添加子表信息
			if (! $mainresult || ! $detailresult) {
				$ajaxresult ['errCode'] = 10005;
				$ajaxresult ['errMsg'] = "网络繁忙，请稍后再试试生成二维码！";
				$this->ajaxReturn ( $ajaxresult );
			}
			// 插入图文信息成功，继续往下走
			$msgnewsinfo = $msgnewsmain; 									// 主信息给到$msgnewsinfo
		}
	
		//开始生成二维码
		$codetable = M ( 'scenecode' );
		$codetable->startTrans (); 	// 开始事务过程
	
		$scene_str = $sub_id;		// 32位字符串，这里是店铺编号
		$permanent = true; 			// 要生成永久二维码
		$permanentuse = 1; 			// 永久使用二维码（scenecode里的）
		$qrcodetype = 2; 			// 2是店铺二维码
	
		$swc = A ( 'Service/WeChat' ); 																	// 实例化微信服务层对象
		$qrcodeinfo = $swc->newGenerateQRCode ( $_SESSION ['curEnterprise'], $scene_str, $permanent ); 	// 生成公众号永久二维码
	
		// 下载二维码，保存到磁盘指定位置
		$ticket_id = $qrcodeinfo ['ticket']; 																		// 二维码场景值
		$prefix = "qrscene"; 																						// 二维码场景值
		$relativepath = __ROOT__ . "/Updata/images/" . $e_id . "/dimensioncode/shopcode/";
		$savepath = $_SERVER ['DOCUMENT_ROOT'] . $relativepath;
		$filename = $prefix . "_" . $scene_str . "_" . $ticket_id . ".jpg"; 										// 文件名
		if (! is_dir ( $savepath )) mkdirs ( $savepath ); 															// 文件夹路径不存在创建路径
		$downloadresult = $swc->downloadQRCode ( $_SESSION ['curEnterprise'], $ticket_id, $savepath . $filename ); 	// 下载微信二维码
	
		$existcodeinfo = $codetable->where ( $existmap )->find (); 					// 查找scenecode表里是否有这样的二维码扫码回复，有就不重复生成了
		if (! $existcodeinfo) {
			// 往scenecode表里增加一条记录
			$scenemap = array (
					'scene_code_id' => md5 ( uniqid ( rand (), true ) ),
					'code_type' => $permanentuse, 									// 1是永久二维码
					'code_use' => $qrcodetype, 										// 2是店铺二维码
					'e_id' => $e_id, 												// 企业编号
					'subbranch_id' => $scene_str, 									// 当前生成二维码的分店编号
					'code_ticket' => $ticket_id,
					'code_param' => $scene_str,	 									// 二维码参数
					'create_time' => time (),
					//'code_info' => jsencode ( $scenedata ), 						// 压缩成json格式
					'code_path' => $relativepath . $filename, 						// 存相对路径
					'creator_id' => $e_id, 											// 当前企业编号
					'response_function' => 'responsenews', 							// 扫码店铺二维码回复图文
					'response_content_id' => $msgnewsinfo ['msgnews_id'] 			// 回复的图文id
			);
			$createresult = $codetable->add ( $scenemap ); 							// 添加进表
		} else {
			$createresult = true; 													// 原来已经存在这个店铺的二维码信息，这步直接默认成功
		}
	
		// 处理事务结果
		if ($createresult) {
			$codetable->commit (); 		// 提交事务
			$dimensioncodepath = "./Updata/images/" . $_SESSION ['curEnterprise'] ['e_id'] . "/dimensioncode/shopcode/"; 	// 压缩包要创建的文件夹路径
			//$zipfolder = "qrcode" . "_" . time (); 																		// 生成压缩包文件夹名（推荐英文）
			$zipfolder = "qrcode" . "_" .$sub_id;
			$foldername = $filename; 	// 图片文件名
			$subbranchinfo = array (
					'filepath' => $dimensioncodepath, 								// 压缩包要创建在哪个文件夹路径下
					'filename' => $zipfolder , 										// 压缩包的文件名（推荐英文）
					'filedata' => array (
							0 => array (
									'innerfoldername' => 'shopqrcode', 				// 压缩包解压后的文件夹名称
									'innerfile' => array (
											0 => $dimensioncodepath . $foldername 	// 压缩包中的图片路径（相对路径）
									)
							)
					),
			);
			$fileinfo [0] = $subbranchinfo; 										// 放到下标为0中
			$drz = A ( 'Service/DownloadRarZip' ); 									// 实例化压缩控制器
			$downresult = $drz->create_download_zip ( $fileinfo, $direct2browser );
		} else {
			$codetable->rollback (); 	// 撤销事务
			$ajaxresult ['errCode'] = 10006;
			$ajaxresult ['errMsg'] = "生成店铺二维码失败，请不要重复提交！";
		}
		if ($downresult){
			$ajaxresult = $downresult;
		} else {
			$ajaxresult ['errCode'] = 10007;
			$ajaxresult ['errMsg'] = "二维码下载失败，请不要重复操作！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 上传店铺logo和店招图片并插入数据库的函数。
	 * @return null | array fileinfo	如果上传成功，返回$fileinfos的信息；如果失败，什么都不返回。
	 */
	public function subImgUpload(){
		$saveFilePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/subbranches/subimg/';
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUpload ( $saveFilePath );
		$this->ajaxReturn ( $uploadresult );
	}
	
}
?>