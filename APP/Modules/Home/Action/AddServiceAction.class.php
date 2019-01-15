<?php
class AddServiceAction extends Action {
	public function add() {
		$allnav = M ( 'servicenav' )->select ();
		$serinfo = array (); // 要添加的服务信息
		for($i = 0; $i < count ( $allnav ); $i ++) {
			$serinfo [$i] = array (
					'service_id' => md5 ( uniqid ( rand (), true ) ),
					'e_id' => '201406132111091903',
					'servicenav_id' => $allnav [$i] ['servicenav_id'],
					'start_date' => '2015-05-20',
					'end_date' => '2016-05-20',
					'add_time' => time () 
			);
		}
		// p($serinfo);die;
		$result = M ( 'enterpriseservice' )->addAll ( $serinfo );
		p ( 'completed, set ' . $result . ' records.' );
		die ();
	}
	
	/**
	 * 添加权限到企业权限表中去
	 */
	public function addService() {
		/*
		 * 步骤1、从enterprise表中读取e_id,service_start_time,service_end_time信息
		 * 步骤2、从t_standardservice读取servicenav_id信息
		 * 步骤3、拼接$addData数据,添加到t_enterpriseservice表
		 */
		$epmap = array(
				'is_del'=>0
		);
		$eplist = M('enterprise')->where($epmap)->select();
	
		$useservice = array(
				'is_del'=>0
		);
		$serlist = M('standardservice')->where($useservice)->select();
	
		$addTable = M('enterpriseservice');
		$totalResult = true; // 初始化为成功
	
		$addTable->startTrans();
	
		for ( $i = 0; $i < count($eplist); $i++) {	// 外层对企业进行循环
			// 为了防止系统崩溃掉，采取批处理的方式进行处理,每次处理一个企业
			$addData = array();	// 初始化拼接的单个企业服务添加数组
			$addIndex = 0; // 针对单个企业的添加服务循环,增加一个服务的索引
			for( $j = 0; $j < count( $serlist); $j++) {	// 通过循环构造本次需要插入的单个企业的数据
				$addData[$addIndex]['service_id'] = md5 ( uniqid ( rand (), true ) );
				$addData[$addIndex]['e_id'] = $eplist[$i]['e_id'];
				$addData[$addIndex]['servicenav_id'] = $serlist[$j]['servicenav_id'];
				$addData[$addIndex]['start_date'] = date('Y-m-d',strtotime($eplist[$i]['service_start_time']));
				$addData[$addIndex]['end_date'] = date('Y-m-d',strtotime($eplist[$i]['service_end_time']));
				$addData[$addIndex]['special_mark'] = "";
				$addData[$addIndex]['temporary_closed'] = 0;
				$addData[$addIndex]['add_time'] = time();
				$addData[$addIndex]['remark'] = "";
				$addData[$addIndex]['is_del'] = 0;
				$addIndex++;
			}
			$addResult = $addTable->addAll($addData);
			if (! $addResult ) {
				$addTable->rollback ();
				$totalResult = false;
				break;
			}
		}
		if ($totalResult) { // 如果最终都成功
			$addTable->commit();
		}
	
		p($totalResult);die();
	}
	
	public function formatNavigation() {
		$navtable = M ( 'navigation' );
		$navmap = array (
				'is_del' => 0
		);
		$allnav = $navtable->where ( $navmap )->select ();
		$allnavcount = count ( $allnav );
		$totalmodify = 0;
		for($i = 0; $i < $allnavcount; $i ++) {
			if ($allnav [$i] ['father_nav_id'] == "-1") {
				$allnav [$i] ['nav_level'] = 1;
			} else {
				$allnav [$i] ['nav_level'] = 2;
			}
			$allnav [$i] ['add_time'] = strtotime ( $allnav [$i] ['create_time'] );
			$totalmodify += $navtable->save ( $allnav [$i] );
		}
		p($totalmodify."ok");die;
	}
	
	public function pwd() {
		p ( md5 ( '13466375607' ) );
		die ();
	}
	
	public function change() {
		$codetable = M ( 'scenecode' );
		$codemap = array (
				'is_del' => 0
		);
		$codelist = $codetable->where ( $codemap )->select ();
		for ($i = 0; $i < count ( $codelist ); $i ++) {
			$codelist [$i] ['add_time'] = strtotime ( $codelist [$i] ['create_time'] );
			$codetable->save ( $codelist [$i] );
		}
		p('ok');die;
	}
	
	public function addsubbranchpic() {
		$emap = array ( 
				'e_id' => '201406261550250006',
				'is_del' => 0
		 );
		$shoptable = M ( 'subbranch' );
		$shoplist = $shoptable->where ( $emap )->select ();
		$snum = count ( $shoplist );
		$savetotal = 0;
		for($i = 0; $i < $snum; $i ++) {
			if (empty ( $shoplist [$i] ['image_path'] )) {
				$absolutepath = "/Updata/images/" . $emap ['e_id'] . "/subbranch/" . $shoplist [$i] ['subbranch_id'] . "/default.png";
				$relativepath = "./Updata/images/" . $emap ['e_id'] . "/subbranch/" . $shoplist [$i] ['subbranch_id'] . "/";
				if (! is_dir( $relativepath )) mkdir ( $relativepath ); // 创建文件夹
				$shoplist [$i] ['image_path'] = "/Updata/images/" . $emap ['e_id'] . "/subbranch/" . $shoplist [$i] ['subbranch_id'] . "/default.png";
				$savetotal += $shoptable->save ( $shoplist [$i] ); // 保存店铺头像
			}
		}
		p('ok,' . $savetotal . 'records updated!');die;
	}
	
	/**
	 * 生成新门店二维码的代码。
	 */
	public function addSubbranchQRCode() {
		// 全局变量
		$e_id = '201405291912250003'; // 要生成门店二维码的商家为G5G6
		$brand = "G5G6"; // 要生成门店的品牌商家名称
		$subbranchname = "江苏宝应二店"; // 新门店名称
		$subbranchaddress = "江苏省扬州市宝应县叶挺路大发商贸城1-118"; // 新门店地址
		$responsefunction = "responsenews"; // 扫码回复动作
		$responsecontentid = "msg0000000000000008"; // 扫码回复动作内容编号
		
		// 查找企业信息
		$emap = array (
				'e_id' => $e_id,
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		
		// 准备信息
		$finalinfo = "failed!"; // 最后的信息
		$subbranchtable = M ( 'subbranch' ); // 分店表实例化
		$scenecodetable = M ( 'scenecode' ); // 二维码表实例化
		$addsubbranchresult = false; // 增加门店结果
		$addscenecoderesult = false; // 增加二维码结果
		$downloadcoderesult = false; // 下载二维码结果
		$swc = A ( 'Service/WeChat' ); // 微信生成二维码类实例化
		
		$subbranchtable->startTrans (); // 生成二维码过程开启
		
		// Step1：准备分店信息
		$subbranchinfo = array (
				'subbranch_id' => md5 ( uniqid ( rand (), true ) ), // 新门店主键
				'e_id' => $e_id, // 品牌编号
				'subbranch_name' => $subbranchname, // 品牌店名称
				'subbranch_brand' => $brand, // 品牌名称
				'add_time' => time (), // 添加门店的时间
				'subbranch_address' => $subbranchaddress, // 门店地址
		);
		$addsubbranchresult = $subbranchtable->add ( $subbranchinfo ); // 添加分店信息
		
		// 二维码信息内容处理
		$codeparam = $subbranchinfo ['subbranch_id']; // 二维码参数
		$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/shopcode/"; // 存放到门店二维码文件夹下
		$usetype = "subbranch"; // 门店用二维码
		$qrprefix = "_qrcode_"; // 二维码前缀
		$saveqrname = $codeparam; // 门店编号
		$typesuffix = ".jpg"; // 默认二维码后缀
		$qrfullname = $usetype . $qrprefix . $saveqrname . $typesuffix; // 二维码图片全名（带后缀），绝对不要用中文名去保存
		$qrabsolutepath = $saveqrpath . $qrfullname; // 二维码完整保存的绝对路径（文件夹+全名+后缀）
		
		$codeinfo = $swc->newGenerateQRCode ( $einfo, $codeparam, true ); // 主键作为scene_id，调用微信接口生成门店二维码
		$downloadcoderesult = $swc->downloadQRCode ( $einfo, $codeinfo ['ticket'], $qrabsolutepath ); // 下载二维码
		
		// 准备二维码的信息
		$codeinfo = array (
				'scene_code_id' => md5 ( uniqid ( rand (), true ) ), // 二维码主键
				'code_type' => 1, // 二维码类型为1代表永久二维码，门店二维码是永久二维码
				'code_use' => 2, // 二维码用途为2代表门店二维码
				'e_id' => $e_id, // 生成二维码的商家
				'subbranch_id' => $subbranchinfo ['subbranch_id'], // 生成门店二维码的门店编号
				'code_ticket' => $codeinfo ['ticket'], // 二维码ticket_id
				'code_param' => $codeparam, // 二维码参数
				'code_path' => $saverelativeqrpath . $qrfullname, // 二维码的路径
				'creator_id' => $e_id, // 二维码生成者：该商家
				'response_function' => $responsefunction, // 扫码回复动作
				'response_content_id' => $responsecontentid, // 扫码回复内容主键
				'create_time' => time (), // 生成二维码的时间
		);
		$addscenecoderesult = $scenecodetable->add ( $codeinfo ); // 往场景二维码表中增加一条记录
		
		if ($addsubbranchresult && $downloadcoderesult && $addscenecoderesult) {
			$subbranchtable->commit (); // 提交事务
			$finalinfo = "success!";
		} else {
			$subbranchtable->rollback (); // 事务回滚
		}
		p($finalinfo);die;
	}
	
	/**
	 * 压缩图片。
	 */
	public function thumbImage() {
		import ( 'ORG.Util.Image' ); // 载入类库图片类
		
		$imagepath = "./Updata/123456.jpg"; // 图片地址
		$thumbimagepath = "./Updata/thumb_123456.jpg"; // 缩略图图片地址
		
		$thumbMaxWidth = '300'; // 宽度最大300
		$thumbMaxHeight = '300'; // 高度最大300，缩略图在30KB左右
		
		Image::thumb ( $imagepath, $thumbimagepath, '', $thumbMaxWidth, $thumbMaxHeight ); // thumb是等比例压缩，优先满足宽或高的任意一个
		p('success');die;
	}
	
	public function who() {
		$emap = array (
				'e_id' => "201406261550250006",
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		$openid = "oeovpt4XZz5S3e44JgjEuniOU4-U";
		$swc = A ( 'Service/WeChat' );
		$userinfo = $swc->getUserInfo ( $einfo, $openid );
		p($userinfo);die;
	}
	
	/**
	 * 为账户里的企业添加店铺优惠券功能。
	 */
	public function addCouponService() {
		$CONST_SERVICE_NAV_ID = "000000000000042001"; // 店铺优惠券功能
		
		// 查找所有的微动企业
		$emap = array (
				'is_del' => 0
		);
		$elist = M ( 'enterprise' )->where ( $emap )->select (); // 查询企业列表
		
		// 一一取出编号，固定servicenav_id，去enterpriseservice表里查询，有没有店铺优惠券这项功能，如果没有就添加，有就跳过
		$totaladd = 0; 							// 总的添加记录数
		$timestart = time (); 					// 当前的秒数
		$timeend = $timestart + 31536000; 		// 当前结束秒
		$estable = M ( 'enterpriseservice' ); 	// 实例化企业服务表
		$ecount = count ( $elist ); 			// 统计企业数量
		for($i = 0; $i < $ecount; $i ++) {
			$checkexist = array (); // 先置空
			
			$checkexist = array (
					'e_id' => $elist [$i] ['e_id'],
					'servicenav_id' => $CONST_SERVICE_NAV_ID, 
					'is_del' => 0
			);
			$tempexist = $estable->where ( $checkexist )->count (); // 计算有没有这个服务
			
			if (! $tempexist) {
				// 如果不存在服务，就添加一条
				$addservice = array ();
				
				$addservice = array (
						'service_id' => md5 ( uniqid ( rand (), true ) ), 
						'e_id' => $elist [$i] ['e_id'], 
						'servicenav_id' => $CONST_SERVICE_NAV_ID, 
						'start_date' => timetodate ( $timestart, true ), 
						'end_date' => timetodate ( $timeend, true ), 
						'add_time' => time () 
				);
				$totaladd += $estable->add ( $addservice ); // 向企业服务表中添加店铺优惠券的使用信息
			}
		}
		p("set " . $totaladd . " records.");die;
	}
	
	public function cure() {
		$sql = "SELECT * FROM t_enterpriseinfo where appid is not null";
		$elist = M ()->query ( $sql ); // 查询有填写appid的企业
		
		$totalcure = 0; // 总治疗数
		$ecount = count ( $elist ); // 计算企业数量
		$etable = M ( 'enterpriseinfo' ); // 实例化企业表
		for($i = 0; $i < $ecount; $i ++) {
			if ($elist [$i] ['authorize_open'] == 0 || $elist [$i] ['login_style'] == 0) {
				$elist [$i] ['authorize_open'] = 1;
				$elist [$i] ['login_style'] = 1;
				$totalcure += $etable->save ( $elist [$i] );
			}
		}
		p("cure " . $totalcure . " people.");die;
	}
	
	public function clearEnterprise() {
		$clearsql = "SELECT * FROM t_enterpriseinfo where appid is null and e_name is null";
		
		$etable = M ( 'enterprise' );
		$einfotable = M ( 'enterpriseinfo' );
		
		$result = M ()->query ( $clearsql );
		$num = count ( $result );
		
		for($i = 0; $i < $num; $i ++){
			$emap = array (
					'e_id' => $result [$i] ['e_id'],
					'is_del' => 0
			);
			$edelresult += $etable->where ( $emap )->delete ();
			$einfodelresult += $einfotable->where ( $emap )->delete ();
		}
		p($edelresult);p($einfodelresult);die;
	}
	
	public function makeurl() {
		$detailtable = M ( "msgnewsdetail" );
		$newsmap = array (
				'is_del' => 0
		);
		$detaillist = $detailtable->where ( $newsmap )->select ();
		
		$detailcount = count ( $detaillist ); 
		$totalhandle = 0;
		for($i = 0; $i < $detailcount; $i ++) {
			if ($detaillist [$i] ['title'] == "欢迎扫码选我做导购") {
				$detaillist [$i] ['link_url'] = $detaillist [$i] ['original_url'];
			} else {
				$detaillist [$i] ['link_url'] = "http://www.we-act.cn/weact/Home/News/info/did/" . $detaillist [$i] ['msgnewsdetail_id']; // 取出图文主键编号生成跳转链接
				
			}
			$totalhandle += $detailtable->save ( $detaillist [$i] );
		}
		p($totalhandle);die;
	}
	
	/**
	 * 格式化邮费。
	 */
	public function formatPostage() {
		$updatecount = 0;
		$producttable = M ( 'product' );
		$postagemap = array (
				'_string' => "postage is null",
		);
		$productlist = $producttable->where ( $postagemap )->select ();
		$listnum = count ( $productlist );
		for($i = 0; $i < $listnum; $i ++) {
			$productlist [$i] ['postage'] = 0;
			$updatecount += $producttable->save ( $productlist [$i] );
		}
		p($updatecount.'ok ');die;
	}
	
}
?>