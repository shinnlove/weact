<?php
import ( 'Class.API.WeChatAPI.TemplateMessage', APP_PATH, '.php' ); // 载入微动微信APISDK类
/**
 * 使用微信服务层的demo示例控制器。
 * 特别注意：可以复制demo代码，但是别忘记构造函数_initialize里，有初始化企业信息，要在调用函数前传入整个企业信息数组！
 * @author 赵臣升
 * CreateTime：2015/04/10 18:28:36.
 */
class WeChatUseDemoAction extends Action {
	
	/**
	 * =============PART1：本控制器的测试常量=============
	 */
	
	/**
	 * 固定测试用的企业编号，微动编号
	 * @var string $CONST_TEST_ENTERPRISEID 测试用微动的企业编号
	 */
	var $CONST_TEST_ENTERPRISEID = "201406261550250006";
	
	/**
	 * 固定测试用的顾客编号，默认是微动♥詩人灬Õ的openid账号
	 * @var string $CONST_TEST_OPENID 测试用的顾客编号
	 */
	var $CONST_TEST_OPENID = "oeovpty2ScWq6YXxuMG0hY5qHOGA";
	
	/**
	 * 固定测试用的企业信息，微动信息。
	 * @var array $CONST_EINFO 微动的企业信息，包含appid、appsecret
	 */
	var $CONST_EINFO = array ();
	
	/**
	 * =============PART2：本控制器的构造函数=============
	 */
	
	/**
	 * 本控制器初始化函数，读取企业信息。
	 */
	public function _initialize() {
		$emap = array (
				'e_id' => $this->CONST_TEST_ENTERPRISEID, // 微动
				'is_del' => 0
		);
		$this->CONST_EINFO = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 企业信息
	}
	
	/**
	 * =============PART3：调用服务层demo区域============
	 */
	
	/**
	 * 获取微信服务器地址。
	 */
	public function wechatServerIP() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$serveriplist = $swc->wechatServerIP ( $this->CONST_EINFO );
		p($serveriplist);die;
	}
	
	/**
	 * 向单个用户发送文本消息。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function sendText() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$text = "你好呀！"; // 要推送的图文
		$kfaccount = "shinnlove@WeActResource"; // 要发送图文的客服账号（可空）
		$sendresult = $swc->sendText ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $text, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 向单个用户发送本地文本消息。
	 */
	public function sendLocalText() {
		$textid = "text00001"; // 本地数据库一条消息的id
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$kfaccount = "shinnlove@WeActResource"; // 要发送图文的客服账号（可空）
		$sendresult = $swc->sendLocalText ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $textid, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 发送图片消息。
	 */
	public function sendImage() {
		$absolutepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/avatar.jpg"; // 图片在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype = 'image';
		$uploadresult = $this->uploadMedia ( $absolutepath, $mediatype );
		$kfaccount = "shinnlove@WeActResource"; // 要发送图文的客服账号（可空）
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendImage ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $uploadresult, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 发送语音消息。
	 */
	public function sendVoice() {
		$absolutepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/lovedock.mp3"; // 语音在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype = 'voice';
		$uploadresult = $this->uploadMedia ( $absolutepath, $mediatype );
		$kfaccount = "shinnlove@WeActResource"; // 要发送图文的客服账号（可空）
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendVoice ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $uploadresult, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 向单个用户发送语音消息。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function sendLocalVoice() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$voiceid = "34fd440739a1b52ef5739b98f56b1be3"; // 要推送的语音消息编号
		$kfaccount = "hurui@WeActResource"; // 要发送图文的客服账号（可空）
		$sendresult = $swc->sendLocalVoice ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $voiceid, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 发送视频消息。
	 */
	public function sendVideo() {
		$absolutepath1 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/avatar.jpg"; // 图片在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$absolutepath2 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/123456.mp4"; // 视频在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype1 = 'image';
		$thumbmedia = $this->uploadMedia ( $absolutepath1, $mediatype1 );
		$mediatype2 = 'video';
		$videomedia = $this->uploadMedia ( $absolutepath2, $mediatype2 );
		$kfaccount = "shinnlove@WeActResource"; // 要发送图文的客服账号（可空）
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendVideo ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $videomedia, $thumbmedia, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * ==============微信模板消息部分====================
	 */
	
	/**
	 * 为企业设置模板消息所属行业。
	 */
	public function setTemplateIndustry() {
		$industry1 = "1"; // 行业1，互联网，电子商务
		$industry2 = "16"; // 行业2，教育培训
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$setresult = $swc->setTemplateIndustry ( $this->CONST_EINFO, $industry1, $industry2 ); // 设置企业模板消息所属行业
		p($setresult);die;
	}
	
	/**
	 * 通过API设置企业需要使用的模板消息。
	 */
	public function apiSetTemplate() {
		$template_id = "TM00015"; // 设置消息模板
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$template = $swc->apiSetTemplate ( $this->CONST_EINFO, $template_id ); // 设置企业模板消息模板
		p($template);die;
	}
	
	/**
	 * 发送模板消息1。
	 */
	public function sendTemplateMessage1() {
		$openid = "oeovpty2ScWq6YXxuMG0hY5qHOGA"; // 设置模板消息发送给的客户
		$template_id = "FZImOTdE5lRkknlI3zv_e5WTNr0fLcJNXXwV47ZRzFg"; // 设置要使用的企业模板消息
		$linkurl = "http://www.we-act.cn/weact/WeMall/Store/storeIndex/sid/070b107fd7ecae417e7a2266ebd7bc9c"; // 设置模板消息跳转URL
		// 设置模板消息
		$tpldata = array (
				'first' => array (
						'value' => "其实，就算你潜水，大微动也是可以抓到你的，" . "\n" . "并不是每一滴牛奶都叫特仑苏，" . "\n" . "并不是 48小时就是你我的长江天堑，" . "\n" . "来吧， chat with me!",
						'color' => "#173177", // 模板消息字体的颜色
				), // 消息台头
				'keynote1' => array (
						'value' => "WeAct Resource 大微动", // 消息发送人
						'color' => "#173177", // 模板消息字体的颜色
				), // 消息来自
				'keynote2' => array (
						'value' => timetodate ( time () ), // 消息发送时间
						'color' => "#173177", // 模板消息字体的颜色
				), // 发送时间
				'remark' => array (
						'value' => "你亲爱的导购给你发送了一条消息，“亲，店里有件衬衫只要 2块钱，你还等什么呢，快来下笔订单吧。”，请进入店铺购买或及时回复导购。", // 模板消息备注
						'color' => "#173177", // 模板消息字体的颜色
				), // 备注
		);
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendTemplateMessage ( $this->CONST_EINFO, $openid, $template_id, $linkurl, $tpldata ); // 设置企业模板消息模板
		p($sendresult);die;
	}
	
	/**
	 * 发送模板消息2。
	 */
	public function sendTemplateMessage2() {
		$openid = "oeovpty2ScWq6YXxuMG0hY5qHOGA"; // 设置模板消息发送给的客户
		$template_id = "MVl8E4-aly6vmJkvkqxalEFBIpNpDj3FnYdhAwPEVho"; // 设置要使用的企业模板消息
		$linkurl = "http://edu.qq.com/zt2015/gkcf/index.htm"; // 设置模板消息跳转URL
		// 设置模板消息
		$tpldata = array (
				'first' => array (
						'value' => "您好，尊敬的考生，您的高考成绩已经可以查询：" . "\n" . "微信扫码WeAct Resource大微动教育，" . "\n" . "实时了解考试成绩和志愿申报！",
						'color' => "#173177", // 模板消息字体的颜色
				), // 消息台头
				'courseName' => array (
						'value' => "2015 全国统一高等教育入学考试", // 消息发送人
						'color' => "#173177", // 模板消息字体的颜色
				), // 课程名称
				'time' => array (
						'Data' => "2015年06月07日 08:00", // 考试时间
						'color' => "#173177", // 模板消息字体的颜色
				), // 考试时间
				'address' => array (
						'value' => "上海大微动教育集团普陀分校 综合楼 A 116", // 消息发送人
						'color' => "#173177", // 模板消息字体的颜色
				), // 考试地点
				'score' => array (
						'value' => "总分：596分", // 消息发送时间
						'color' => "#173177", // 模板消息字体的颜色
				), // 考试成绩
				'remark' => array (
						'value' => "感谢你对大微动高等教育的关心和认可。", // 模板消息备注
						'color' => "#173177", // 模板消息字体的颜色
				), // 备注
		);
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendTemplateMessage ( $this->CONST_EINFO, $openid, $template_id, $linkurl, $tpldata ); // 设置企业模板消息模板
		p($sendresult);die;
	}
	
	//发送模板消息——导购新消息提醒
	public function sendTplMessageToCustomer($e_id = '', $openid = '', $template_id = "", $linkurl = "") {
		$template_id = 'bKr2d4QFGY7zJbahQO3K0-XUP27kZeS0z5armBHh1ts';	//微信模板消息接口
		$emap = array (
				'e_id' => '201406261550250006', // 微动
				'is_del' => 0
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 企业信息
		$openid = 'oeovpt13JCmPNLaU6dTSh8mt68N4';	//wlk
		$openid= 'oeovpty2ScWq6YXxuMG0hY5qHOGA';	//zcs
		$linkurl = 'http://192.168.0.42/wechat/chatindex.html';
		$tpldata = array (
				'first' => array (
						'value' => "",
						'color' => "#173177", // 模板消息字体的颜色
				), // 消息台头
				'keyword1' => array (
						'value' => "您有新的导购消息，请查收", // 消息发送人
						'color' => "#173177", // 模板消息字体的颜色
				), // 消息来自
				'keyword2' => array (
						'value' => timetodate ( time () ), // 消息发送时间
						'color' => "#173177", // 模板消息字体的颜色
				), // 发送时间
				'remark' => array (
						'value' => "亲爱的导购给您发送了一条消息，请及时回复导购。", // 模板消息备注
						'color' => "#173177", // 模板消息字体的颜色
				), // 备注
		);
	
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$sendresult = $swc->sendTemplateMessage ( $einfo, $openid, $template_id, $linkurl, $tpldata ); // 设置企业模板消息模板
		p($sendresult);die;
		return $sendresult;
	}
	/**
	 * 模板消息下单通知。
	 */
	public function orderSubmitTplMsg() {
		$domain = C ( 'DOMAIN' ); // 提取域名
		$openid = "oeovpt13JCmPNLaU6dTSh8mt68N4"; // 万康康
		$fontcolor = "#DA70D6"; // 淡紫色的字体颜色
		$orderid = "e2260ae2b02ea3c72887e460ef96a341"; // 订单编号
		$tpldata = array (
				//'first_data' => "感谢" . $this->CONST_EINFO ['e_name'] . "用户下单购买我们的商品。",
				'visual_number' => "14353498330974",
				'total_price' => "9.00" . "元",
				'receive_person' => "赵臣升",
				'receive_tel' => "15021237551",
				'receive_address' => "上海市松江区文汇路300弄4#1007",
				//'remark' => "特别备注：" . "请老板快点发货，注意保持包裹完整噢。",
		);
		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->CONST_TEST_ENTERPRISEID;
		// 策略模式发送消息
		$ordernotify = new OrderNotify ( $tpldata, $url, $fontcolor ); // 下单通知
		$msgwechater = new MsgToWechater ( $ordernotify ); // 上下文类对象
		$sendresult = $msgwechater->sendMsgToWechater ( $this->CONST_EINFO, $this->CONST_TEST_OPENID ); // 发送模板消息
		p ( $sendresult ); die ();
	}
	
	/**
	 * 模板消息付款通知。
	 */
	public function sendPayTplMsg(){
		$domain = C ( 'DOMAIN' ); // 提取域名
		$openid = "oeovpt13JCmPNLaU6dTSh8mt68N4"; // 万康康
		$fontcolor = "#00C957"; // 翠绿色的字体颜色
		$orderid = "e2260ae2b02ea3c72887e460ef96a341"; // 订单编号
		$tpldata = array (
				//'first_data' => "您已成功支付本订单" . "14353498330974" . "，感谢您对我们的厚爱。",
				'total_price' => "9.00" . "元",
				'product_name' => "水蓝牛仔短裤" . "等商品...",
				'receive_address' => "上海市松江区文汇路300弄4#1007",
				'visual_number' => "14353498330974",
				//'remark' => "温馨提示：" . "快递收货，我们会及时打包快递给您；到店自提，请您到相关门店上门自取^_^。",
		);
		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->CONST_TEST_ENTERPRISEID;
		$paynotify = new PayNotify ( $tpldata, $url, $fontcolor ); // 付款通知
		$msgwechater = new MsgToWechater ( $paynotify ); // 上下文类对象
		$sendresult = $msgwechater->sendMsgToWechater ( $this->CONST_EINFO, $this->CONST_TEST_OPENID ); // 发送模板消息
		p ( $sendresult ); die ();
	}
	
	/**
	 * 模板消息退款通知。
	 */
	public function sendRefundTplMsg(){
		$domain = C ( 'DOMAIN' ); // 提取域名
		$openid = "oeovpt13JCmPNLaU6dTSh8mt68N4"; // 万康康
		$fontcolor = "#E3170D"; // 镉红色的字体颜色
		$orderid = "e2260ae2b02ea3c72887e460ef96a341"; // 订单编号
		$tpldata = array (
				//'first_data' => "尊敬的" . $this->CONST_EINFO ['e_name'] . "顾客，您的订单" . "14353498330974" . "经协商，现已成功退款，给您带来的不便还请谅解。",
				'total_price' => "9.00" . "元",
				'product_name' => "水蓝牛仔短裤" . "等商品...",
				'visual_number' => "14353498330974",
				//'remark' => "温馨提示：" . "欢迎您再次下单或联系我们的客服。",
		);
		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->CONST_TEST_ENTERPRISEID;
		// 策略模式退款通知
		$refundnotify = new RefundNotify ( $tpldata, $url, $fontcolor ); // 退款通知
		$msgwechater = new MsgToWechater ( $refundnotify ); // 上下文类对象
		$sendresult = $msgwechater->sendMsgToWechater ( $this->CONST_EINFO, $this->CONST_TEST_OPENID ); // 发送模板消息
		p($sendresult);die;
	}
	
	/**
	 * 订单超时取消通知。
	 */
	public function sendCancelTplMsg(){
		$domain = C ( 'DOMAIN' ); // 提取域名
		$openid = "oeovpt13JCmPNLaU6dTSh8mt68N4"; // 万康康
		$fontcolor = "#808A87"; // 冷灰色的字体颜色
		$orderid = "e2260ae2b02ea3c72887e460ef96a341"; // 订单编号
		$tpldata = array (
				//'first_data' => "尊敬的" . $this->CONST_EINFO ['e_name'] . "顾客，您的订单" . "14353498330974" . "因交易超时现已成功退款，给您带来的不便还请谅解。",
				'total_price' => "9.00" . "元",
				'product_name' => "水蓝牛仔短裤" . "等商品...",
				'receive_address' => "上海市松江区文汇路300弄4#1007",
				'visual_number' => "14353498330974",
				//'remark' => "温馨提示：" . "我们可能目前不便出售该商品，请联系我们的客服。",
		);
		$url = $domain . "/weact/Home/Order/myOrder/e_id/" . $this->CONST_TEST_ENTERPRISEID;
		// 策略模式退款通知
		$cancelnotify = new OrderCancelNotify ( $tpldata, $url, $fontcolor ); // 退款通知
		$msgwechater = new MsgToWechater ( $cancelnotify ); // 上下文类对象
		$sendresult = $msgwechater->sendMsgToWechater ( $this->CONST_EINFO, $this->CONST_TEST_OPENID ); // 发送模板消息
		p($sendresult);die;
	}
	
	/**
	 * ==============微信素材管理部分====================
	 */
	
	/**
	 * 获取企业在微信平台的永久素材。
	 */
	public function permanentMediaList() {
		$type = "news"; // 图文消息类型
		$offset = 0; // 从头开始读取
		$count = 20; // 一次读取最多20条
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$medialist = $swc->permanentMediaList ( $this->CONST_EINFO, $type, $offset, $count ); // 读取企业永久素材列表
		p($medialist);die;
	}
	
	/**
	 * 合并微信和微动的永久图文。
	 */
	public function mergePermanentNews() {
		//G('begin');
		
		$fathertable = M ( "msgnews" ); 		// 图文信息主表
		$childtable = M ( "msgnewsdetail" ); 	// 图文信息子表
		$remotelist = array (); 				// 远程图文信息数组
		$remoteformat = array (); 				// 远程格式化数组
		$localformat = array (); 				// 本地格式化数组
		$wechatnew = array (); 					// 从微信平台新增的图文（需要比对出）
		$wechatdel = array (); 					// 从微信平台删除的图文（需要比对后检索出）
		$uploadcount = 0; 						// 上传更新回微动数据库的图文消息条数
		
		// 准备请求参数
		$mediatype = "news"; 	// 图文消息类型
		$nextstart = 0; 		// 从第0条开始读取。
		$perpage = 20; 			// 默认一次处理10条图文的同步
		$oncemax = 100; 		// 一次最多拉取多少条
		
		// 调用递归函数获取公众平台图文
		$newsinfo = $this->getPermanentNews ( $this->CONST_EINFO, $mediatype, $nextstart, $perpage, $oncemax ); // 获取企业所在微信平台编辑的图文
		if (! empty ( $newsinfo ['item'] )) {
			$remotelist = $newsinfo ['item']; 	// 给如图文信息数组
			// 如果微信公众平台有图文，才去格式化远程数组
			foreach ($remotelist as $remotevalue) {
				$remoteformat [$remotevalue ['media_id']] = $remotevalue ['content']; // 把图文内容content放入相关media_id下，因为是主键，所以不用担心重复
			}
		}
		
		// 查询企业在微动平台的图文消息
		$emap = array (
				'e_id' => $this->CONST_TEST_ENTERPRISEID, 		// 当前企业
				'wechat_id' => array ( "neq", "-1" ), 			// 在微信平台已经有过注册的，微动新增的图文
				'is_del' => 0
		);
		$locallist = $fathertable->where ( $emap )->select (); 	// 查询企业在微动平台的图文
		if ($locallist) {
			// 如果微动本地有上传过的图文
			foreach ($locallist as $localvalue) {
				$localvalue ['tobe_deleted'] = 1; // 默认为需要被删除
				$localformat [$localvalue ['wechat_id']] = $localvalue; // 格式化
			}
		}
		
		// 检测出需要微动新增的部分
		foreach($remoteformat as $remoteformatkey => $remoteformatvalue) {
			if (! isset ( $localformat [$remoteformatkey] )) {
				array_push ( $wechatnew, $remoteformatvalue ); 	// 推入微信平台的新增图文数组
			} else {
				$localformat [$remoteformatkey] ['tobe_deleted'] = 0; // 不需要删除
			}
		}
		
		// 检测出需要微动删除的部分
		foreach($localformat as $localformatkey => $localformatvalue) {
			if ($localformatvalue ['tobe_deleted'] == 1) {
				array_push ( $wechatdel, $localformatvalue ); // 推入本地要删除的图文
			}
		}
		
		// 查询出需要上传到微信的微动图文
		$emap = array (
				'e_id' => $this->CONST_TEST_ENTERPRISEID, 		// 当前企业
				'wechat_id' => "-1", 							// 在微信平台已经有过注册的，微动新增的图文
				'is_del' => 0
		);
		$uploadlist = $fathertable->where ( $emap )->select (); // 查询企业在微动平台需要上传到微信的图文
		
		//p($remoteformat);p($localformat);p($wechatnew);p($wechatdel);p($uploadlist);die;
		
		// 对需要上传的图文循环上传
		$swc = A ( "Service/WeChat" ); // 实例化微信服务层
		foreach($uploadlist as $uploadvalue) {
			$msgnews_id = $uploadvalue ['msgnews_id']; // 提取图文消息主键
			
			$detailmap = array (
					'msgnews_id' => $msgnews_id, // 当前图文消息主键（作为外键）
					'is_del' => 0, 
			);
			$detaillist = $childtable->where ( $detailmap )->select (); // 查询图文详情信息
			if ($detaillist) {
				$detailcount = count ( $detaillist ); // 计算图文消息详情数量
				$currentlist = array (); // 当前图文格式化后的数组
				// 循环处理子图文
				for($i = 0; $i < $detailcount; $i ++) {
					// 先处理图片
					$imagepath = $_SERVER ['DOCUMENT_ROOT'] . str_replace ( "http://www.we-act.cn", "", $detaillist [$i] ['cover_image'] ); // 做成图片绝对路径
					$cover_media = $swc->uploadPermanentMedia ( $this->CONST_EINFO, $imagepath, "image" ); // 调用接口获得图片media_id（特别注意：永久图文使用的图片必须也是永久素材！！！）
					// 格式化子图文
					$tempdetail = array (
							"thumb_media_id" => $cover_media,
							"author" => $this->CONST_EINFO ['e_name'], // 作者就是企业
							"title" => isset ( $detaillist [$i] ['title'] ) ? $detaillist [$i] ['title'] : "默认图文", // 图文标题
							"content_source_url" => isset ( $detaillist [$i] ['original_url'] ) ? $detaillist [$i] ['original_url'] : "", 		// 原文链接
							"content" => isset ( $detaillist [$i] ['main_content'] ) ? $detaillist [$i] ['main_content'] : "这是一条默认的图文消息", 	// 图文主内容
							"digest" => isset ( $detaillist [$i] ['summary'] ) ? $detaillist [$i] ['summary'] : "", // 图文摘要
							"show_cover_pic" => "1",
					);
					array_push ( $currentlist, $tempdetail ); // 将格式化后的图文信息打包推入$currentlist中
				}
				$articleinfo = array (
						"articles" => $currentlist, // 格式化后的图文信息
				);
				$newsinfo = $swc->uploadPermanentNews ( $this->CONST_EINFO, $articleinfo ); // 调用接口上传图文信息并获得返回（这里不是uploadmedia，而是上传永久图文！！！）
				$wechat_id = $newsinfo ['media_id']; // 上传到微信平台后图文消息的编号
			}
			if (! empty ( $wechat_id )) {
				$uploadvalue ['wechat_id'] = $wechat_id; // 写入微信平台图文消息主键
				$uploadcount += $fathertable->save ( $uploadvalue ); // 更新回微动数据库
			}
		}
		
		p("upload " . $uploadcount . " news completed.");die;
		
		//G('end');
		//p($remotelist ['total_count']);p($remotelist);p("执行接口拉取" . $oncemax . "条图文消息，性能测试结果：\n总计用时：" . G('begin','end') . "s，花销内存：" . G('begin','end','m') . "kb。");die;
		
	}
	
	/**
	 * 每页20条的递归读取企业图文消息列表的函数。
	 * @param array $einfo 递归读取图文消息的企业
	 * @param string $mediatype 图文消息，默认news
	 * @param number $nextstart 下一页开始的位置
	 * @param number $perpage 每页读取图文数量
	 * @param number $oncemax 一次最多拉取多少条，如果不送这个参数，直接默认拉取100条，余下的下次再拉
	 * @return array $newslist 当前这一次读取后的图文消息（或许是本次读取的，或许是本次加上下一次返回的图文）
	 */
	public function getPermanentNews($einfo = NULL, $mediatype = "news", $nextstart = 0, $perpage = 20, $oncemax = 100) {
		$swc = A ( 'Service/WeChat' ); 					// 实例化微信服务层对象
		$medialist = $swc->permanentMediaList ( $einfo, $mediatype, $nextstart, $perpage ); // 读取企业永久素材列表
		
		$allcount = $medialist ['total_count']; 		// 微信平台总的图文消息
		$currentcount = $medialist ['item_count']; 		// 当前读到的图文记录数目
		$currentitem = $medialist ['item']; 			// 本次拉取的图文有可能为空
		
		$nextstart += $currentcount; 					// 游标后移
		if ($nextstart < $allcount && $nextstart < $oncemax) {
			// 如果还小于总数量或者也并没达到实际想拉取的图文数量，则进行再次循环， 继续去调用接口获取下一页的微信图文
			$nextlist = $this->getPermanentNews ( $einfo, $mediatype, $nextstart, $perpage, $oncemax ); // 下一波图文
			$nextcount = count ( $nextlist ['item'] ); 	// 统计下一次拉取图文消息的数量
			if ($nextcount > 0) {
				// 如果有拉取到下一波图文，则对下一波图文返回的值进行压栈，与原来的数组合并
				foreach ($nextlist ['item'] as $key => $value) {
					array_push ( $currentitem, $value ); // 后续图文压入图文列表栈中
				}
				$currentcount += $nextcount; 			// 这一波拉取图文的数量
			}
		} 
		// 打包递归返回的数组信息
		$medialist = array (
				'total_count' => $allcount, 			// 总的图文数量
				'item_count' => $currentcount, 			// 这一波拉取图文的数量（对于单次拉取是$perpage，对于多次，则是次数×$perpage）
				'item' => $currentitem, 				// 当前图文数量
		);
		return $medialist; // 有图文消息返回与下次一次获取图文合并后的图文数组；没有则直接返回本次（也是最后一次）获取的图文消息
	}
	
	/**
	 * 上传多媒体文件。
	 */
	public function uploadMedia($mediapath = '', $mediatype = 'image') {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$uploadresult = $swc->uploadMedia ( $this->CONST_EINFO, $mediapath, $mediatype ); // 上传多媒体文件
		return $uploadresult;
	}
	
	/**
	 * 向单个微信用户发送图文信息。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function sendLocalNews() {
		$newsid = "msg0000000000000001"; // 要推送的本地图文消息编号
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		
		$kfaccount = "hurui@WeActResource"; // 要发送图文的客服账号（可空）
		$sendresult = $swc->sendLocalNews ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $newsid, $kfaccount );
		p($sendresult);die;
	}
	
	/**
	 * 定点投放群发图文。
	 */
	public function groupSendNews() {
		$groupid = 123; // 要群发的分组
		$msgnewsid = "msg0000000000000001"; // 要群发的图文信息编号
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$groupsendresult = $swc->groupSendNews ( $this->CONST_EINFO, $groupid, $msgnewsid ); // 上传多媒体文件
		p($groupsendresult);die;
	}
	
	/**
	 * 从微信下载多媒体文件并保存到本地服务器。
	 */
	public function downloadMedia() {
		$absolutepath1 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/avatar.jpg"; // 图片在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype1 = 'image';
		$uploadresult1 = $this->uploadMedia ( $absolutepath1, $mediatype1 );
		
		$mediasavepath1 = "./Updata/testdownloadimage.jpg";
		$mediaid1 = $uploadresult1;
		
		$absolutepath2 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/lovedock.mp3"; // 声音在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype2 = 'voice';
		$uploadresult2 = $this->uploadMedia ( $absolutepath2, $mediatype2 );
		
		$mediasavepath2 = "./Updata/testdownload.amr";
		$mediaid2 = $uploadresult2;
		
		$swc = A ( 'Service/WeChat' );
		$fileinfo1 = $swc->downloadWechatMedia ( $this->CONST_EINFO, $mediaid1, $mediasavepath1 );
		$fileinfo2 = $swc->downloadWechatMedia ( $this->CONST_EINFO, $mediaid2, $mediasavepath2 );
		p($fileinfo1);p($fileinfo2);die;
	}
	
	/**
	 * 上传永久图片。
	 */
	public function uploadPermanentImage() {
		$absolutepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/avatar.jpg"; // 图片在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype = 'image';
		$uploadresult = $this->uploadPermanentMedia ( $absolutepath, $mediatype );
		p($uploadresult);die;
	}
	
	/**
	 * 上传永久声音。
	 */
	public function uploadPermanentVoice() {
		$absolutepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/lovedock.mp3"; // 图片在磁盘上的绝对路径 = 服务器项目路径 + 相对路径
		$mediatype = 'voice';
		$uploadresult = $this->uploadPermanentMedia ( $absolutepath, $mediatype );
		p($uploadresult);die;
	}
	
	/**
	 * 上传永久多媒体文件。
	 */
	public function uploadPermanentMedia($mediapath = '', $mediatype = 'image') {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$uploadresult = $swc->uploadPermanentMedia ( $this->CONST_EINFO, $mediapath, $mediatype ); // 上传多媒体文件
		return $uploadresult;
	}
	
	/**
	 * 下载永久多媒体文件。
	 */
	public function downloadPermanentMedia() {
		$imagemediaid = "n3FoVWXNsgHze6xsfQX7D0oMnO-wa6K7Tdacbb6Pw6Y";
		$imagemediatype = "image";
		$imagemediasavepath = "./Updata/downloadpermanentimage.jpg";
		
		$voicemediaid = "n3FoVWXNsgHze6xsfQX7D5jgph3XRE672aJ6jYjNLDI";
		$voicemediatype = "voice";
		$voicemediasavepath = "./Updata/downloadpermanentvoice.amr";
		
		$swc = A ( 'Service/WeChat' );
		$imagedownloadresult = $swc->downloadPermanentMedia ( $this->CONST_EINFO, $imagemediaid, $imagemediatype, $imagemediasavepath ); // 下载永久多媒体素材
		$voicedownloadresult = $swc->downloadPermanentMedia ( $this->CONST_EINFO, $voicemediaid, $voicemediatype, $voicemediasavepath ); // 下载永久多媒体素材
		p($imagedownloadresult);p($voicedownloadresult);die;
	}
	
	/**
	 * 微信长链接转换短链接接口。
	 */
	public function longURLToShort() {
		$longurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQH87zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1RVd0dzTDdsNjI2U1FlazJwbUNfAAIEMkk1VQMEAAAAAA==";
		$swc = A ( 'Service/WeChat' );
		$shorturl = $swc->longURLToShort ( $this->CONST_EINFO, $longurl );
		p($shorturl);die;
	}
	
	/**
	 * 设置某企业的微信公众号菜单。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function setMenu() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$setresult = $swc->setMenu ( $this->CONST_EINFO ); // 设置该企业的微信公众号菜单
		p($setresult);die;
	}
	
	/**
	 * 查询公众号菜单。
	 */
	public function queryMenu() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$queryresult = $swc->queryMenu ( $this->CONST_EINFO ); // 查询该企业的微信公众号菜单
		if ($queryresult) {
			p($queryresult);die;
		} else {
			p($swc->getError());die;
		}
	}
	
	/**
	 * 删除公众号菜单。
	 */
	public function deleteMenu() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$deleteresult = $swc->deleteMenu ( $this->CONST_EINFO ); // 删除该企业的微信公众号菜单
		p($deleteresult);die;
	}
	
	/**
	 * 生成微信二维码接口。
	 */
	public function generateWeChatQRCode() {
		$scene_id = "25846";
		$permanent = false;
		$expire = 1800;
		
		$scene_id2 = "19526";
		$permanent2 = true;
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$qrcodeinfo1 = $swc->generateWeChatQRCode ( $this->CONST_EINFO, $scene_id, $permanent, $expire ); // 生成临时二维码
		$qrcodeinfo2 = $swc->generateWeChatQRCode ( $this->CONST_EINFO, $scene_id2, $permanent2 ); // 生成公众号永久二维码
		p($qrcodeinfo1);p($qrcodeinfo2);die;
	}
	
	/**
	 * 微信平台新生成二维码接口，2015/04/22更新接口。
	 */
	public function newGenerateQRCode() {
		$scene_id = 34567; // 临时二维码场景值
		$permanent1 = false; // 临时二维码
		$expire = 604800; // 有效时间7天
		
		$scene_str = "qianqianqianqianqianqianqianqian"; // 32位字符串
		$permanent2 = true;
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$qrcodeinfo1 = $swc->newGenerateQRCode ( $this->CONST_EINFO, $scene_id, $permanent1, $expire ); // 生成临时二维码
		$qrcodeinfo2 = $swc->newGenerateQRCode ( $this->CONST_EINFO, $scene_str, $permanent2 ); // 生成公众号永久二维码
		p($qrcodeinfo1);p($qrcodeinfo2);die;
	}
	
	/**
	 * 微动拓展：增加门店二维码。
	 */
	public function addSubbranchQRCode() {
		
	}
	
	/**
	 * 微动拓展：生成导购二维码。
	 */
	public function addGuideQRCode() {
		// Step1：生成二维码
		$scene_str = "g20001"; // 32位字符串，这里是导购编号
		$permanent = true; // 要生成永久二维码
		$permanentuse = 1; // 永久使用二维码（scenecode里的）
		$qrcodetype = 3; // 3是导购二维码
		
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$qrcodeinfo = $swc->newGenerateQRCode ( $this->CONST_EINFO, $scene_str, $permanent ); // 生成公众号永久二维码
		
		// Step2：下载二维码，保存到磁盘指定位置
		$ticket_id = $qrcodeinfo ['ticket']; // 二维码场景值
		$prefix = "qrscene"; // 二维码场景值
		$relativepath = __ROOT__ . "/Updata/images/" . $this->CONST_TEST_ENTERPRISEID . "/dimensioncode/guidecode/" . $scene_str . "/";
		$savepath = $_SERVER ['DOCUMENT_ROOT'] . $relativepath;
		$filename = $prefix . "_" . $scene_str . "_" . $ticket_id . ".jpg"; // 文件名
		if (! is_dir ( $savepath )) mkdirs ( $savepath ); // 路径不存在创建路径
		
		$downloadresult = $swc->downloadQRCode ( $this->CONST_EINFO, $ticket_id, $savepath . $filename ); // 下载微信二维码
		
		// Step3：往scenecode表里增加一条记录
		$scenemap = array (
				'scene_code_id' => md5 ( uniqid ( rand (), true ) ),
				'code_type' => $permanentuse, // 1是永久二维码
				'code_use' => $qrcodetype, // 3是导购二维码
				'e_id' => $this->CONST_TEST_ENTERPRISEID, // 企业编号
				'subbranch_id' => "11d304db8a56fd997a43b3b627b69e5c", // 模拟杭州三角村（G5G6）分店
				'code_ticket' => $ticket_id,
				'code_param' => $scene_str, // 二维码参数
				'create_time' => time (),
				//'code_info' => jsencode ( $scenedata ), // 压缩成json格式
				'code_path' => $relativepath . $filename, // 存相对路径
				'creator_id' => $this->CONST_TEST_ENTERPRISEID,
				//'creator_id' => $personinfo ['customer_id'],
				'response_function' => 'responsenews',
				'response_content_id' => 'msg0000000000000017'
		);
		if ($qrcodetype == 3) {
			$scenemap ['guide_id'] = $scene_str; // 导购编号
		}
		$sctable = M ( 'scenecode' );
		$createresult = $sctable->data ( $scenemap )->add ();
		
		p('ok');die;
	}
	
	/**
	 * 下载微信二维码接口。
	 * 想要公众号反应，还需要同步到二维码表中，设置响应方式。
	 */
	public function downloadWeChatQRCode() {
		$savepath1 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/images/" . $this->CONST_TEST_ENTERPRISEID . "/dimensioncode/";
		$filename1 = "123.jpg";
		$savepath2 = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/images/" . $this->CONST_TEST_ENTERPRISEID . "/dimensioncode/";
		$filename2 = "abc.jpg";
		if (! is_dir ( $savepath1 )) mkdirs ( $savepath1 ); // 路径不存在创建路径
		if (! is_dir ( $savepath2 )) mkdirs ( $savepath2 ); // 路径不存在创建路径
		
		$tempticket = "gQET7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2xrd0R2SXpsa203cnBqQkdvMktfAAIEMkk1VQMECAcAAA=="; // 临时二维码ticket
		$ticket = "gQH87zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL1RVd0dzTDdsNjI2U1FlazJwbUNfAAIEMkk1VQMEAAAAAA=="; // 永久二维码ticket
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$downloadresult1 = $swc->downloadQRCode ( $tempticket, $tempticket, $savepath1 . $filename1 ); // 下载微信二维码
		$downloadresult2 = $swc->downloadQRCode ( $tempticket, $ticket, $savepath2 . $filename2 ); // 下载微信二维码
		p($downloadresult1);p($downloadresult2);die;
	}
	
	/**
	 * 创建某企业微信公众号新用户分组，对某个企业创建用户分组。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function createUserGroup() {
		$newgroupname = "群发生日券";
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$createresult = $swc->createUserGroup ( $this->CONST_EINFO, $newgroupname ); // 添加一个新的用户分组
		p($createresult);die;
	}
	
	/**
	 * 查询某企业的微信公众号所有用户分组。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function queryAllGroup() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$result = $swc->queryAllGroup ( $this->CONST_EINFO ); // 查询微信公众号所有用户分组
		p($result);die;
	}
	
	/**
	 * 通过openid查询某企业的某个微信用户所在哪个分组组号。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function queryUserGroup() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$result = $swc->queryUserGroup ( $this->CONST_EINFO, $this->CONST_TEST_OPENID );
		p($result);die;
	}
	
	/**
	 * 批量查询用户所在分组。
	 * 测试通过日期：2015/04/20 08:43:36.
	 */
	public function batchQueryUserGroup() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$wechatusermap = array (
				'e_id' => $this->CONST_TEST_ENTERPRISEID,
				'is_del' => 0
		);
		$wechatuserlist = M ( 'wechat_customer_info' )->where ( $wechatusermap )->limit ( 10 )->select (); // 从视图中查询用户
		$openidlist = array ();
		foreach ($wechatuserlist as $uservalue) {
			if (! empty ( $uservalue ['openid'] )) {
				array_push ( $openidlist, $uservalue ['openid'] ); // openid压栈
			}
		}
		$result = $swc->batchQueryUserGroup ( $this->CONST_EINFO, $openidlist );
		p($result);die;
	}
	
	/**
	 * 修改用户分组名。
	 * 测试通过日期：2015/04/20 08:43:36.
	 */
	public function modifyUserGroupName () {
		$groupid = 110; // 组编号必须存在！
		$newname = "亲爱的你";
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$modifyresult = $swc->modifyUserGroupName ( $this->CONST_EINFO, $groupid, $newname );
		p($modifyresult);die;
	}
	
	/**
	 * 将某用户移动到某个分组。
	 * 测试通过日期：2015/04/10 18:43:36.
	 */
	public function moveUserToGroup() {
		//$openid = 'oYN0nuM8A7bDD8ol5TwF1YwOYQzI'; // G5G6王学玲
		$group_id = 116; // 移动目标分组
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$moveresult = $swc->moveUserToGroup ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $group_id );
		p($moveresult);die;
	}
	
	/**
	 * 将用户批量移动到某分组。
	 */
	public function newBatchMoveUserToGroup() {
		$group_id = 116; // 移动目标分组
		$openid1 = $this->CONST_TEST_OPENID; 
		$openid2 = "oeovpt0O6cbZ5n8hHPVGsPFI84HY"; // 马军伟
		$openidlist = array ();
		array_push ( $openidlist, $openid1 );
		array_push ( $openidlist, $openid2 );
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$batchmoveresult = $swc->newBatchMoveUserToGroup ( $this->CONST_EINFO, $openidlist, $group_id );
		p($batchmoveresult);die;
	}
	
	/**
	 * 修改用户备注名。
	 */
	public function modifyUserRemarkName() {
		$remarkname = "亲爱滴";
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$remarkresult = $swc->modifyUserRemarkName ( $this->CONST_EINFO, $this->CONST_TEST_OPENID, $remarkname );
		p($remarkresult);die;
	}
	
	/**
	 * 获取用户信息。
	 */
	public function getUserInfo() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$userinfo = $swc->getUserInfo ( $this->CONST_EINFO, $this->CONST_TEST_OPENID );
		p($userinfo);die;
	}
	
	/**
	 * 获取某企业的客服人员列表。
	 */
	public function customerServiceList() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$kflist = $swc->onlineServiceList ( $this->CONST_EINFO );
		p($kflist);die;
	}
	
	/**
	 * 获取某企业的客服人员服务状态。
	 */
	public function onlineServiceStatus() {
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$statusinfo = $swc->onlineServiceStatus ( $this->CONST_EINFO );
		p($statusinfo);die;
	}
	
	/**
	 * 设置某企业的客服账号。
	 */
	public function addServiceAccount() {
		$kfinfo = array (
				"kf_account" => "shinnlove@WeActResource", // 客服：诗人账号
				"nickname" => "♥詩人灬Õ", // 客服昵称
				"password" => md5 ( "19881218" ), // 客服登录密码
		);
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$addresult = $swc->addServiceAccount ( $this->CONST_EINFO, $kfinfo ); // 添加账号
		p($addresult);die;
	}
	
	/**
	 * 修改线上客服信息。
	 */
	public function modifyOnlineServiceInfo() {
		// 要设置信息的客服账号
		$modifykfinfo = array (
				"kf_account" => "shinnlove@WeActResource", // 客服：诗人账号
				"nickname" => "♥詩人灬Õ", // 客服昵称
				"password" => md5 ( "19881218" ), // 客服登录密码
		);
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$modifyresult = $swc->modifyOnlineServiceInfo ( $this->CONST_EINFO, $modifykfinfo ); // 修改客服账号信息
		p($modifyresult);die;
	}
	
	/**
	 * 设置某企业的客服人员头像。
	 */
	public function setCustomerServiceAvatar() {
		$avatarpath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/Updata/avatar.jpg"; // 头像的绝对路径
		$kfaccount = "shinnlove@WeActResource"; // 要设置头像的客服人员账号
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$setavatar = $swc->modifyOnlineServiceAvatar ( $this->CONST_EINFO, $kfaccount, $avatarpath );
		p($setavatar);die;
	}
	
	/**
	 * 删除某企业线上客服账号。
	 */
	public function deleteOnlineService() {
		$deleteaccount = "shinnlove@WeActResource"; // 这个@符号不行
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$deleteresult = $swc->delOnlineServiceAccount ( $this->CONST_EINFO, $deleteaccount );
		p($deleteresult);die;
	}
	
}
?>