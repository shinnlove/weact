<?php
/**
 * 本控制器处理后台导购信息管理
 * @author 胡福玲
 * CreateTime 2015/02/28
 */
class GuideManageRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyUI读取导购的ajax请求。
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 以添加导购的时间降序排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$gimap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], 	// 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
	
		$gitbl = M ( 'shopguide_subbranch' );						// t_shopguide、t_subbranch视图
		$total = $gitbl->where ( $gimap )->count (); 				// 计算当前商家下不被删除的导购的总数
		$guidelist = array (); 										// 全局导购列表guidelist
	
		if ($total) {
			$guidelist = $gitbl->where ( $gimap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			$guidenum = count ( $guidelist );
			for($i = 0; $i < $guidenum; $i ++) {
				if ($guidelist [$i] ['birthday'] == '0000-00-00') {
					$guidelist [$i] ['birthday'] = '';
				}
				//时间戳转换日期显示的处理
				$guidelist [$i] ['add_time'] = timetodate ( $guidelist [$i] ['add_time'] );
				$guidelist [$i] ['headimg'] = assemblepath ( $guidelist [$i] ['headimg'] );
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $guidelist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 根据条件搜索导购信息处理函数
	 * 导购登录账号、工号、姓名和联系号码皆为模糊查找
	 * 导购所属店铺为精确查找
	 */
	public function searchGuide(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; 				// 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; 			// 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; 		// 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; 		// 排序方式
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 												// 接收查询条件
		$content = I ( 'searchcontent' ); 													// 接受查询内容
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		if ($condition == 'account' || $condition == 'guide_number' || $condition == 'guide_name' || $condition == 'cellphone') {
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		} else if ($condition == "subbranch_name") {
			if ($content != -1) $searchmap [$condition] = $content; 						// 搜全部，不限制类别
		}
	
		$ginfotbl = M ( 'shopguide_subbranch' );
		$ginfolist = array ();
		$gtotal = $ginfotbl->where ( $searchmap )->count ();
	
		if ($gtotal) {
			$ginfolist = $ginfotbl->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			$guidenum = count ( $ginfolist );
			for($i = 0; $i < $guidenum; $i ++) {
				if ($guidelist [$i] ['birthday'] == '0000-00-00') {
					$guidelist [$i] ['birthday'] = '';
				}
				$ginfolist [$i] ['add_time'] = timetodate ( $ginfolist [$i] ['add_time'] );		// 时间戳转换日期显示的处理
				$ginfolist [$i] ['headimg'] = assemblepath ( $ginfolist [$i] ['headimg'] );
			}
		}
	
		$json = '{"total":' . $gtotal . ',"rows":' . json_encode ( $ginfolist ) . '}'; 	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 删除导购信息post处理函数。
	 */
	public function del() {
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
		$delgidlist = I ( 'rowgdata' ); // 接收要删除的导购id列表
		$gtbl = M ( "shopguide" );
		$delguidemap = array (
				'guide_id' => array ( 'in' , $delgidlist ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		); // 定义shopguide表的删除范围
	
		$gresult = $gtbl->where ( $delguidemap )->setField ( 'is_del', 1 );
		if ($gresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "删除导购信息失败"
			);
		}
		$this->ajaxReturn( $ajaxinfo ); // 将结果返回给前台
	}
	
	/**
	 * 查看导购推荐商品详情。
	 * 特别注意：原来是一个顾客对应一个导购，不可能有很多个导购。
	 * 现在是一个顾客可以有很多个导购，所以原来的guide_customer_order视图已经不存在了；
	 * 现在将其修改为在order_cinfo视图里查询订单主表信息；
	 * CreateTime:2015/05/20 01:28:36.
	 */
	public function getGuideOrder(){
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10000,
				'errMsg' => "网络繁忙，查询导购订单信息失败。"
		);
	
		// 尝试查询该导购的最近3笔订单记录
		$gomap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'guide_id' => I ( 'gid' ),
				'is_del' => 0
		);
		$goinfolist = M ( 'order_cinfo' )->where ( $gomap )->order ( 'order_time desc' )->limit ( 3 )->select ();
	
		if ($goinfolist) {
			$gordernum = count ( $goinfolist ); // 统计查询到的订单数量（1~3）
			for ($i =0; $i < $gordernum; $i ++) {
				$goinfolist [$i] ['order_time'] = timetodate ( $goinfolist [$i] ['order_time'] );
				if ($goinfolist [$i] ['pay_method'] == 1) {
					$goinfolist [$i] ['pay_method'] = '现金收讫';
				} else if ($goinfolist [$i] ['pay_method'] == 2) {
					$goinfolist [$i] ['pay_method'] = '微信支付';
				} else if ($goinfolist [$i] ['pay_method'] == 3) {
					$goinfolist [$i] ['pay_method'] = '刷卡支付';
				} else {
					$goinfolist [$i] ['pay_method'] = '其他';
				}
			}
		} else {
			$goinfolist = array (); // 没有查询到就直接变成空数组
		}
	
		// 没有成交过订单和查询错误是两码事，查询了就直接返回OK
		$ajaxresult ['errCode'] = 0;
		$ajaxresult ['errMsg'] = "ok";
		$ajaxresult ['data'] ['detaillist'] = $goinfolist; // 导购的订单列表
		$this->ajaxReturn( $ajaxresult );
	}
	
	/**
	 * 新增导购信息处理函数。
	 */
	public function addGuideCfm(){
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
	
		//实例化导购信息表
		$gtbl = M('shopguide');
	
		//判断导购账号是否已存在
		$gaccmap = array(
				'account' => I ( 'gac' ),
				'is_del' => 0
		);
		$gaccinfo = $gtbl->where($gaccmap)->find();
		if ($gaccinfo){
			$ajaxresult = array (
					'errCode' => 10003,
					'errMsg' => "该导购账号已存在，请更改后重新添加！"
			);
			$this->ajaxReturn($ajaxresult);
		}
	
		$gdata = array (
				'guide_id' => I ( 'gid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'subbranch_id' => I ( 'gsi' ),
				'guide_number' => I ( 'gnu' ),
				'account' => $gaccmap['account'],
				'password' => md5 ( $gaccmap['account'] ),
				'guide_name' => I ( 'gna' ),
				'nickname' => I ( 'gni' ),
				'sex' => I ( 'gss' ),
				'id_card' => I ( 'gic' ),
				'birthday' => I ( 'gbd' ),
				'cellphone' => I ( 'gph' ),
				'guide_level' => I ( 'gls' ),
				'guide_type' => I ( 'gts' ),
				'headimg' => I ( 'ghp' ),
				'add_time' => time (),
		);
		$gres = $gtbl->add ( $gdata );
	
		if ($gres) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "导购添加失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 编辑修改导购信息的处理函数。
	 */
	public function editGuideCfm() {
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
	
		$gmap = array (
				'guide_id' => I ( 'gid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$gdata = array (
				'subbranch_id' => I ('gsi'),
				'guide_number' => I ('gnu'),
				'account' => I('gac'),
				//'password' => md5(I('gac')),			//编辑导购信息中不对导购登录密码做修改
				'guide_name' => I('gna'),
				'nickname' => I('gni'),
				'sex' => I('gss'),
				'id_card' => I('gic'),
				'birthday' => I('gbd'),
				'cellphone' => I('gph'),
				'guide_level' => I('gls'),
				'guide_type' => I('gts'),
				'headimg' => I('ghp'),
				'latest_modify' => time (),
		);
	
		$result = M ( 'shopguide' )->where ( $gmap )->save ( $gdata );
	
		if ($result) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		} else {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "导购信息更新失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 上传头像并插入数据库的函数。
	 * @return null | array fileinfo	如果上传成功，返回$fileinfos的信息；如果失败，什么都不返回。
	 */
	public function headImgUpload(){
		$saveFilePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/shopguide/' . $_REQUEST ['gid'] . "/";
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUpload ( $saveFilePath );
		$this->ajaxReturn ( $uploadresult );
	}
	
	/**
	 * 特别注意：导购二维码是腾讯的二维码，扫了以后要关注公众号的！
	 */
	public function createGuideQRCode() {
	
	}
	
	/**
	 * 为已经存在的导购追加二维码，本请求为ajax请求。
	 * 特别注意：导购二维码是腾讯的二维码，扫了以后要关注公众号的！
	 */
	public function appendGuideQRCode() {
		if (! IS_POST) _404 ( "Sorry, 404 Error, page not found!", U ( 'Admin/GuideManage/guideView', '', '', true ) ); // 防止恶意打开
	
		// 定义ajax返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		$e_id = $_SESSION ['curEnterprise'] ['e_id']; // 取当前的企业编号，要反复用到
		$guide_id = I ( 'gid' ); // 接收导购编号
		if (empty ( $guide_id )) {
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "要生成二维码名片的导购编号不能为空。";
			$this->ajaxReturn ( $ajaxresult );
		}
	
		// 参数通过检验：
		$guidetable = M ( 'shopguide' );
		// Step1、检查当前导购是否存在
		$guidemap = array (
				'guide_id' => $guide_id,
				'e_id' => $e_id,
				'is_del' => 0
		);
		$guideinfo = $guidetable->where ( $guidemap )->find (); // 尝试找到这个导购信息
		if (! $guideinfo) {
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "不存在或已离职的导购，请及时刷新！";
			$this->ajaxReturn ( $ajaxresult );
		}
	
		// Step2、检查当前导购是否有二维码名片，如果有，不给生成
		if (! empty ( $guideinfo ['dimension_code'] )) {
			$ajaxresult ['errCode'] = 10004;
			$ajaxresult ['errMsg'] = "导购的二维码名片已经存在，无需重复生成！";
			$this->ajaxReturn ( $ajaxresult );
		}
	
		// Step3、检查当前商家是否有一条用于扫码关注导购的图文，如果没有，添加一条
		$guidemsgmap = array (
				'e_id' => $e_id,
				'msg_description' => "欢迎选我做导购",
				'is_del' => 0
		);
		$msgnewsinfo = M ( 'msgnews' )->where ( $guidemsgmap )->find (); // 尝试找到这样一条图文
		if (! $msgnewsinfo) {
			// 如果没有这样的图文，生成一条
			$msgmaintable = M ( 'msgnews' ); // 图文主表
			$msgdetailtable = M ( 'msgnewsdetail' ); // 图文子表
			// 生成一条欢迎的单图文
			$msgnewsmain = array (
					'msgnews_id' => md5 ( uniqid ( rand (), true ) ), // 生成主表主键
					'e_id' => $e_id,
					'add_time' => time (),
					'msg_use' => 2, // 被动扫码回复
					'msg_category' => 0, // 单图文
					'msg_description' => "欢迎选我做导购", // 图文描述
			); // 图文主信息
			$ename = $_SESSION ['curEnterprise'] ['e_name']; // 企业名字
			$msgnewsdetail = array (
					'msgnewsdetail_id' => md5 ( uniqid ( rand (), true ) ), // 生成子表主键
					'msgnews_id' => $msgnewsmain ['msgnews_id'], // 主表主键
					'title' => "欢迎扫码选我做导购",
					'author' => $ename, // 企业名称
					'cover_image' => "http://www.we-act.cn/APP/Modules/Admin/Tpl/Public/images/platformimage/guidenewsimg.jpg",
					'main_content' => "欢迎扫描" . $ename . "导购二维码名片，已经为您匹配该导购。稍后您将收到导购的专属服务，请等待接入......",
					'link_url' => "http://www.we-act.cn/weact/WeMall/Store/storeList/sid/" . $guideinfo ['subbranch_id'], // 扫码跳转链接逛店铺（进入的是店铺列表，带上哪个导购的分店参数都一样）
					'detail_order' => -1 // 单图文直接是封面图文
			); // 图文子信息
			// 插入信息
			$mainresult = $msgmaintable->add ( $msgnewsmain ); // 添加主表信息
			$detailresult = $msgdetailtable->add ( $msgnewsdetail ); // 添加子表信息
			if (! $mainresult || ! $detailresult) {
				$ajaxresult ['errCode'] = 10005;
				$ajaxresult ['errMsg'] = "网络繁忙，请稍后再试试生成二维码名片！";
				$this->ajaxReturn ( $ajaxresult );
			}
			// 插入图文信息成功，继续往下走
			$msgnewsinfo = $msgnewsmain; // 主信息给到$msgnewsinfo
		}
	
		// Step4、生成微信的二维码
		$codetable = M ( 'scenecode' ); // 实例化scenecode表
		$codetable->startTrans (); // 开始事务过程
		// 事务1：插入scenecode表，生成的二维码
	
		// Step1-1：生成二维码
		$scene_str = $guide_id; // 32位字符串，这里是导购编号
		$permanent = true; // 要生成永久二维码
		$permanentuse = 1; // 永久使用二维码（scenecode里的）
		$qrcodetype = 3; // 3是导购二维码
	
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$qrcodeinfo = $swc->newGenerateQRCode ( $_SESSION ['curEnterprise'], $scene_str, $permanent ); // 生成公众号永久二维码
	
		// Step1-2：下载二维码，保存到磁盘指定位置
		$ticket_id = $qrcodeinfo ['ticket']; // 二维码场景值
		$prefix = "qrscene"; // 二维码场景值
		$relativepath = __ROOT__ . "/Updata/images/" . $e_id . "/dimensioncode/guidecode/" . $scene_str . "/";
		$savepath = $_SERVER ['DOCUMENT_ROOT'] . $relativepath;
		$filename = $prefix . "_" . $scene_str . "_" . $ticket_id . ".jpg"; // 文件名
		if (! is_dir ( $savepath )) mkdirs ( $savepath ); // 文件夹路径不存在创建路径
	
		$downloadresult = $swc->downloadQRCode ( $_SESSION ['curEnterprise'], $ticket_id, $savepath . $filename ); // 下载微信二维码
	
		// 检查scenecode表中是否有这样的记录
		$existcodemap = array (
				'e_id' => $e_id, // 当前商家
				'guide_id' => $guide_id, // 当前导购
				'is_del' => 0 // 没有被删除的
		);
		$existcodeinfo = $codetable->where ( $existcodemap )->find (); // 查找scenecode表里是否有这样的二维码扫码回复，有就不重复生成了
		if (! $existcodeinfo) {
			// Step1-3：往scenecode表里增加一条记录
			$scenemap = array (
					'scene_code_id' => md5 ( uniqid ( rand (), true ) ),
					'code_type' => $permanentuse, // 1是永久二维码
					'code_use' => $qrcodetype, // 3是导购二维码
					'e_id' => $e_id, // 企业编号
					'subbranch_id' => $guideinfo ['subbranch_id'], // 当前生成二维码名片的导购所在分店
					'guide_id' => $scene_str, // 导购编号
					'code_ticket' => $ticket_id,
					'code_param' => $scene_str, // 二维码参数（导购编号）
					'create_time' => time (),
					//'code_info' => jsencode ( $scenedata ), // 压缩成json格式
					'code_path' => $relativepath . $filename, // 存相对路径
					'creator_id' => $e_id, // 当前企业编号
					'response_function' => 'responsenews', // 扫码导购回复图文
					'response_content_id' => $msgnewsinfo ['msgnews_id'] // 回复的图文id
			);
			$createresult = $codetable->add ( $scenemap ); // 添加进表
		} else {
			$createresult = true; // 原来已经存在这个导购的欢迎信息，这步直接默认成功
		}
	
		// 事务2：更新shopguide中的二维码名片路径
		$guideinfo ['dimension_code'] = assemblepath ( $relativepath . $filename, true ); // 二维码名片用绝对路径
		$updateresult = $guidetable->save ( $guideinfo ); // 保存导购的二维码名片信息
	
		// 处理事务结果
		if ($createresult && $updateresult) {
			$codetable->commit (); // 提交事务
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
			$ajaxresult ['data'] ['qrcode'] = $guideinfo ['dimension_code']; // 附带上生成的二维码名片全路径
		} else {
			$codetable->rollback (); // 撤销事务
			$ajaxresult ['errCode'] = 10006;
			$ajaxresult ['errMsg'] = "生成导购二维码失败，请不要重复提交！";
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
}
?>