<?php
import ( 'Class.API.WeChatAPI.WeactWechat', APP_PATH, '.php' ); // 载入微动微信APISDK类

/**
 * 模板消息类。
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class TplMsgNotify {
	
	/**
	 * ==========Section1:模板消息类通信变量区==========
	 */
	
	/**
	 * 模板消息运行结果，如果出错，可以调用。
	 * @var string $error
	 */
	protected $error = "ok";
	
	/**
	 * 发送模板消息的结果。
	 * @var array $ajaxresult
	 */
	protected $ajaxresult = array (
			'errCode' => 10001, 
			'errMsg' => "微信接口繁忙，请稍后再试", 
	);
	
	/**
	 * 模板消息的字体RGB颜色，默认是#173177颜色，浅蓝色，可以更改。
	 * @var string $fontcolor 模板消息的字体RGB颜色
	 */
	protected $fontcolor = "#173177";
	
	/**
	 * ==========Section2:模板消息类设置变量区==========
	 */
	
	/**
	 * 微信模板消息类型。
	 * @var number $tpltype
	 */
	protected $tpltype = 0;
	
	/**
	 * 不同模板被商家添加后得到的不同ticket编号。
	 * @var string $tplticket 
	 */
	protected $tplticket = "";
	
	/**
	 * 不同模板消息跳转的URL。
	 * @var String $jumpurl
	 */
	protected $jumpurl = "";
	
	/**
	 * 不同模板消息的数组。
	 * @var array $tpldata
	 */
	protected $tpldata = array (); 
	
	/**
	 * ==========Section3:模板消息类函数区==========
	 */
	
	/**
	 * 虚函数：初始化具体的模板消息，留到子类实现。
	 */
	public function initData() {
		// To realize in descendant class ...
	}
	
	/**
	 * 获取该企业已经存在的模板消息。
	 * @param array $einfo 企业信息
	 * @param number $tpltype 模板类型
	 * @return array $tplinfo 模板消息
	 */
	public function getExistTemplate($einfo = NULL) {
		// 查询企业当前类型的模板
		$templatemap = array (
				'e_id' => $einfo ['e_id'], 		// 当前企业
				'tpl_type' => $this->tpltype, 	// 当前微信模板类型
				'is_del' => 0, 					// 没有删除
		);
		$templateinfo = M ( 'enterprise_tpl_msg' )->where ( $templatemap )->find ();
		if (! $templateinfo) {
			// 如果该企业还没有该类微信消息模板，则直接调用接口上传
			$wechatTplId = ""; 		// 微信模板编号
			$wechatTplName = ""; 	// 微信模板名称
			switch ($this->tpltype) {
				case 1:
					$wechatTplId = "OPENTM207018253"; // 下单成功模板
					$wechatTplName = "成功下单通知";
					break;
				case 2:
					$wechatTplId = "TM00398"; // 付款成功模板
					$wechatTplName = "付款成功通知";
					break;
				case 3:
					$wechatTplId = "TM00430"; // 退款成功
					$wechatTplName = "退款成功通知";
					break;
				case 4:
					$wechatTplId = "TM00850"; // 订单取消
					$wechatTplName = "订单取消通知";
					break;
				case 5:
					$wechatTplId = "OPENTM207574605"; // 订单取消
					$wechatTplName = "微信消息通知";
					break;
				default:
					$wechatTplId = "";
					$wechatTplName = "其他模板"; 
					break;
			}
			// Step1：创建微动微信SDK类对象
			$wechatapi = new WeactWechat ( $einfo ); 
			
			// Step2：利用API接口上传模板消息得到templateId
			$setresult = $wechatapi->setTemplateInfo ( $wechatTplId ); // 设置企业模板消息使用模板
			if ($setresult ['errcode'] != 0) {
				$this->error = $wechatapi->getError (); // 获取错误
				return false;
			}
			
			// Step3：把新得到的ticket给到本类中的ticket，并且存数据库
			$newtplinfo = array (
					'id' => md5 ( uniqid ( rand (), true ) ), 	// 订单主键
					'e_id' => $einfo ['e_id'], 					// 企业编号
					'tpl_type' => $this->tpltype, 				// 当前模板类型
					'tpl_id' => $wechatTplId, 					// 微信模板id
					'ticket' => $setresult ['template_id'], 	// 设置成功，则取出当前企业的ticket
					'title' => $wechatTplName, 					// 模板名称
					'primary_industry' => "IT科技", 				// 模板消息一级行业
					'second_industry' => "互联网|电子商务", 			// 模板消息二级行业
					'first_data' => "", 						// 模板消息抬头
					'remark_data' => "", 						// 模板消息备注
					'add_time' => time (), 						// 添加模板的时间
			);
			$addtplresult = M ( 'wechattplmsg' )->add ( $newtplinfo ); 	// 新增模板消息
			if ($addtplresult) {
				$templateinfo = $newtplinfo; 						// 把新生成的模板消息给到$templateinfo中
			} else {
				$this->error = "网络繁忙，为企业添加模板消息失败，请稍后再试。";
				return false;
			}
			
		}
		// 处理结果
		$this->tplticket = $templateinfo ['ticket']; // 取出商家模板ticket给到ticket
		
		return $templateinfo; // 返回商家自定义的模板信息
	}
	
	/**
	 * 发送模板消息函数。
	 * @param array $einfo 企业信息 
	 * @param string $openid 顾客openid
	 * @return array $ajaxresult 发送模板消息的结果 
	 */
	public function sendMsg($einfo = NULL, $openid = '') {
		// Step1：创建微动微信SDK类对象
		$wechatapi = new WeactWechat ( $einfo ); 
		
		// Step2：初始化对应微信模板消息的ticket
		$ticketinfo = $this->getExistTemplate ( $einfo ); 
		if (empty ( $ticketinfo )) {
			$this->tplticket = ""; // 没有ticket
			$this->error = "商家并没有添加ticket";
			return false;
		}
		
		// Step3：对模板消息加上商家DIY自定义的模板消息话语
		$this->tpldata ['first'] = array (
				'value' => $ticketinfo ['first_data'],
				'color' => $this->fontcolor, 
		); // 消息抬头
		$this->tpldata ['remark'] = array (
				'value' => $ticketinfo ['remark_data'],
				'color' => $this->fontcolor,
		); // 消息备注
		
		// Step4：发送模板消息并返回结果
		$sendresult = $wechatapi->sendTemplateMsg ( $openid, $this->tplticket, $this->jumpurl, $this->tpldata, $this->fontcolor ); 
		
		// Step5：处理发送模板消息结果
		if ($sendresult) {
			$this->ajaxresult ['errCode'] = 0; // 发送成功
			$this->ajaxresult ['errMsg'] = "ok"; 
		} else {
			$this->ajaxresult ['errCode'] = 10002; // 发送失败
			$this->ajaxresult ['errMsg'] = $wechatapi->getError (); // 得到模板消息发送错误原因
		}
		
		// Step6:将消息记录日志文件
		$recordmsg = "发送企业编号为" . $einfo ['e_id'] . "的模板消息，类型为" . $this->tpltype . "，发送给" . $openid . "的用户，发送结果为：" . $this->ajaxresult ['errMsg']; // 记录发送结果信息
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WechatLog/tplmsg/"; 	// 文件夹
		$filename = "recordtplmsg" . timetodate ( time (), true ) . ".log"; 		// 文件名按天存放
		if (! file_exists ( $filepath )) mkdirs ( $filepath ); 						// 如果没有存在文件夹，直接创建文件夹
		$this->logRecord ( $filepath . $filename, $recordmsg ); 					// 调用日志文件记录已发送的短信模板消息
		
		return $this->ajaxresult; // 返回模板消息发送结果
	}
	
	/**
	 * ==========Section4:模板消息类工具区==========
	 */
	
	/**
	 * 得到模板消息类构造运行的错误。
	 * @return string $errorinfo 模板消息错误运行原因。
	 */
	public function getError() {
		return $this->error; // 返回错误信息，除了ok以外都有错误。
	}
	
	/**
	 * 发送消息模板成功后打印log日志文件函数log_record。
	 * @param string $filepathname 文件路径和文件名
	 * @param string $context 要记录的日志文件内容
	 */
	private function logRecord($filepathname = '', $context = '') {
		$fp = fopen ( $filepathname, "a" ); // 打开文件获得读写所有权限
		flock ( $fp, LOCK_EX ); 			// 锁定日志文件
		fwrite ( $fp, "发送的消息模板：" . strftime ( "%Y-%m-%d %H:%M:%S", time () ) . "\n" . $context . "\n\n" ); // 写入日志文件内容
		flock ( $fp, LOCK_UN ); 			// 解锁日志文件
		fclose ( $fp ); 					// 释放文件解锁
	}
	
}

/**
 * 下单成功的模板消息通知类，微信模板消息编号为TM.....
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class OrderNotify extends TplMsgNotify {
	
	/**
	 * 下单成功类的构造函数。
	 * @param array $tpldata 模板消息上包含的消息信息。
	 * @param string $jumpurl 模板消息要跳转的URL地址
	 * @param string $fontcolor 模板消息的字体颜色
	 */
	public function __construct($tpldata = NULL, $jumpurl = '', $fontcolor = "#173177") {
		$this->tpltype = 1; 			// 固定：下单成功的模板消息类别是1
		$this->fontcolor = $fontcolor; 	// 模板消息的字体颜色
		$this->jumpurl = $jumpurl; 		// 模板消息跳转URL地址
		$this->initData ( $tpldata ); 	// 初始化下单成功的模板消息
	}
	
	/**
	 * 初始化模板消息函数initData。
	 * @param array $tpldata 下单成功的模板消息
	 * @property string first_data 模板消息抬头
	 * @property string visual_number 订单编号
	 * @property string total_price 订单总价
	 * @property string receive_person 收货人姓名
	 * @property string receive_tel 收货人电话
	 * @property string receive_address 收货人地址
	 * @property string remark 订单备注消息
	 * @return array $tpldata 初始化后的模板消息
	 */
	public function initData($tpldata = NULL){
		$this->tpldata = array (
				'keyword1' => array (
						'value' => $tpldata ['visual_number'],
						'color' => $this->fontcolor, 
				), // 订单编号
				'keyword2' => array (
						'value' => $tpldata ['total_price'],
						'color' => $this->fontcolor, 
				), // 订单总价
				'keyword3' => array (
						'value' => $tpldata ['receive_person'],
						'color' => $this->fontcolor, 
				), // 收货人姓名
				'keyword4' => array (
						'value' => $tpldata ['receive_tel'],
						'color' => $this->fontcolor, 
				), // 收货人电话
				'keyword5' => array (
						'value' => $tpldata ['receive_address'],
						'color' => $this->fontcolor, 
				), // 收货人地址
		);
		return $this->tpldata;
	}
	
}

/**
 * 付款成功通知
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class PayNotify extends TplMsgNotify {

	/**
	 * 付款成功类的构造函数。
	 * @param array $tpldata 模板消息上包含的消息信息。
	 * @param string $jumpurl 模板消息要跳转的URL地址
	 * @param string $fontcolor 模板消息的字体颜色
	 */
	public function __construct($tpldata = NULL, $jumpurl = '', $fontcolor = "#173177") {
		$this->tpltype = 2; 			// 固定：付款成功的模板消息类别是2
		$this->fontcolor = $fontcolor; 	// 模板消息的字体颜色
		$this->jumpurl = $jumpurl; 		// 模板消息跳转URL地址
		$this->initData ( $tpldata ); 	// 初始化下单成功的模板消息
	}
	
	/**
	 * 初始化模板消息函数initData。
	 * @param array $tpldata 下单成功的模板消息
	 * @property string first_data 模板消息抬头
	 * @property string total_price 订单总价
	 * @property string product_name 付款商品
	 * @property string receive_address 收货人地址
	 * @property string visual_number 订单编号
	 * @property string remark 订单备注消息
	 * @return array $tpldata 初始化后的模板消息
	 */
	public function initData($tpldata = NULL) {
		$this->tpldata = array (
				'orderProductPrice' => array (
						'value' => $tpldata ['total_price'],
						'color' => $this->fontcolor, 
				), // 订单总价
				'orderProductName' => array (
						'value' => $tpldata ['product_name'],
						'color' => $this->fontcolor, 
				), // 商品名
				'orderAddress' => array (
						'value' => $tpldata ['receive_address'],
						'color' => $this->fontcolor, 
				), // 收货人地址
				'orderName' => array (
						'value' => $tpldata ['visual_number'],
						'color' => $this->fontcolor, 
				), // 订单编号
		);
		return $this->tpldata; 
	}
	
}

/**
 * 退款成功通知
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class RefundNotify extends TplMsgNotify {

	/**
	 * 退款成功类的构造函数。
	 * @param array $tpldata 模板消息上包含的消息信息。
	 * @param string $jumpurl 模板消息要跳转的URL地址
	 * @param string $fontcolor 模板消息的字体颜色
	 */
	public function __construct($tpldata = NULL, $jumpurl = '', $fontcolor = "#173177") {
		$this->tpltype = 3; 			// 固定：退款成功的模板消息类别是3
		$this->fontcolor = $fontcolor; 	// 模板消息的字体颜色
		$this->jumpurl = $jumpurl; 		// 模板消息跳转URL地址
		$this->initData ( $tpldata ); 	// 初始化下单成功的模板消息
	}
	
	/**
	 * 初始化模板消息函数initData。
	 * @param array $tpldata 下单成功的模板消息
	 * @property string first_data 模板消息抬头
	 * @property string total_price 订单总价
	 * @property string product_name 付款商品
	 * @property string visual_number 订单编号
	 * @property string remark 订单备注消息
	 * @return array $tpldata 初始化后的模板消息
	 */
	public function initData($tpldata = NULL) {
		$this->tpldata = array (
				'orderProductPrice' => array (
						'value' => $tpldata ['total_price'],
						'color' => $this->fontcolor, 
				), // 订单总价
				'orderProductName' => array (
						'value' => $tpldata ['product_name'],
						'color' => $this->fontcolor, 
				), // 商品名称
				'orderName' => array (
						'value' => $tpldata ['visual_number'],
						'color' => $this->fontcolor, 
				), // 订单编号
		);
		return $this->tpldata;
	}
	
}

/**
 * 订单取消通知类。
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class OrderCancelNotify extends TplMsgNotify {
	
	/**
	 * 订单取消类的构造函数。
	 * @param array $tpldata 模板消息上包含的消息信息。
	 * @param string $jumpurl 模板消息要跳转的URL地址
	 * @param string $fontcolor 模板消息的字体颜色
	 */
	public function __construct($tpldata = NULL, $jumpurl = '', $fontcolor = "#173177") {
		$this->tpltype = 4; 			// 固定：订单取消的模板消息类别是4
		$this->fontcolor = $fontcolor; 	// 模板消息的字体颜色
		$this->jumpurl = $jumpurl; 		// 模板消息跳转URL地址
		$this->initData ( $tpldata ); 	// 初始化下单成功的模板消息
	}
	
	/**
	 * 初始化模板消息函数initData。
	 * @param array $tpldata 下单成功的模板消息
	 * @property string first_data 模板消息抬头
	 * @property string total_price 订单总价
	 * @property string product_name 付款商品
	 * @property string receive_address 订单收货地址
	 * @property string visual_number 订单编号
	 * @property string remark 订单备注消息
	 * @return array $tpldata 初始化后的模板消息
	 */
	public function initData($tpldata = NULL) {
		$this->tpldata = array (
				'orderProductPrice' => array (
						'value' => $tpldata ['total_price'],
						'color' => $this->fontcolor, 
				), // 订单总价
				'orderProductName' => array (
						'value' => $tpldata ['product_name'],
						'color' => $this->fontcolor, 
				), // 订单商品名
				'orderAddress' => array (
						'value' => $tpldata ['receive_address'],
						'color' => $this->fontcolor, 
				), // 订单下单地址
				'orderName' => array (
						'value' => $tpldata ['visual_number'],
						'color' => $this->fontcolor, 
				), // 订单名字
		);
		return $this->tpldata;
	}
	
}

/**
 * APP消息通知类。
 * @author 赵臣升，万路康
 * CreateTime:2015/9/30 15:22
 */
class APPMsgNotify extends TplMsgNotify {
	
	/**
	 * APP消息通知类的构造函数。
	 * @param array $tpldata 模板消息上包含的消息信息。
	 * @param string $jumpurl 模板消息要跳转的URL地址
	 * @param string $fontcolor 模板消息的字体颜色
	 */
	public function __construct($tpldata = NULL, $jumpurl = '', $fontcolor = "#173177") {
		$this->tpltype = 5; 			// 固定：APP消息通知类模板类型是5
		$this->fontcolor = $fontcolor;	// 模板消息的字体颜色
		$this->jumpurl = $jumpurl;		// 模板消息跳转URL地址
		$this->initData ( $tpldata );	// 初始化下单成功的模板消息
	}
	
	/**
	 * 初始化模板消息函数initData
	 * @param array $tpldata 下单成功的模板消息
	 * @property string msg_notify 消息通知
	 * @property string msg_time 消息时间
	 * @property string msg_remark 消息详情
	 * @return array $tpldata 初始化后的模板消息
	 */
	public function initData($tpldata = NULL) {
		$this->tpldata = array (
				'first' => array (
						'value' => '',	// 默认模板消息抬头为空
						'color' => $this->fontcolor,
				), // 消息抬头
				'keyword1' => array (
						'value' => $tpldata ['msg_notify'],
						'color' => $this->fontcolor,
				), // 通知消息
				'keyword2' => array (
						'value' => $tpldata ['msg_time'],
						'color' => $this->fontcolor,
				), // 消息时间
				'remark' => array (
						'value' => $tpldata ['msg_remark'],
						'color' => $this->fontcolor,
				), // 消息备注
		);
		return $this->tpldata;
	}
	
}

/**
 * 策略模式上下文类，发送模板消息给微信用户的类。
 * @author 万路康、胡睿。
 * CreateTime:2015/08/22 15:44:36.
 */
class MsgToWechater {
	
	/**
	 * 上下文中包含的策略对象，即模板消息对象。
	 * @var TplMsgNotify $msgnotify 
	 */
	private $msgnotify = NULL; // 子类对象
	
	/**
	 * 上下文构造函数，传入不同的模板消息具体类。
	 * @param TplMsgNotify $msgnotify 具体不同的模板消息类
	 */
	public function __construct($msgnotify = NULL) {
		$this->msgnotify = $msgnotify; // 本类要操作的模板消息类
	}
	
	/**
	 * 上下文共有对外接口，发送模板消息给用户。
	 * @param string $einfo 模板消息要发送给哪个企业。
	 * @param string $openid 微信用户openid
	 * @param array $sendresult 发送模板消息的结果
	 */
	public function sendMsgToWechater($einfo = NULL, $openid = '') {
		return $this->msgnotify->sendMsg ( $einfo, $openid ); // 发送微信模板消息给微信用户
	}
	
}

?>