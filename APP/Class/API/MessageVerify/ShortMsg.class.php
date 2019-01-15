<?php



require_once 'DDlog.class.php';
require_once 'Ucpaas.class.php';

/**
 * 发送短信的类
 */
class ShortMsg  {
	
	//退款成功模板
	private $refundTemplate = array('id' => 11799 ,'name' => '验证码','text' =>'尊敬的客户，您在{1}下的订单{2}已成功退款' );
	
	//付款成功模板
	private $paySuccessTemplate = array('id' => 11798 ,'name' => '付款成功通知','text' =>'尊敬的客户，您已为在{1}下的订单{2}成功付款' );
	
	//下单成功模板
	private $orderSuccessTemplate = array('id' => 11797 ,'name' => '下单成功通知','text' =>'尊敬的客户，您已成功在{1}下单，订单编号为{2}' );
	
	//取消定单模板
	private $cancelOrderTemplate = array('id' => 11958 ,'name' => '取消定单通知','text' =>'尊敬的客户，您已在{1}取消定单，订单编号为{2}' );
	
	// 注册验证码的模板
	private $yanzhengTemplate 	  =  array('id' => 10733, 'name' => '验证码', 'text' => '您注册{1}网站的验证码为{2}，请于{3}分钟内正确输入验证码');  
	
	// 找回密码的模板
	private $yanzhengFindCode 	  =  array('id' => 10733, 'name' => '验证码', 'text' => '您正在{1}重置密码，验证码为{2}，请于{3}分钟内正确输入验证码');  
	
	// 7天到期提醒短信
	private $deadline7dayTemplate = array('id' => 1266, 'name' => '7天到期提醒', 'text' => '尊敬的客户您好，你的客服系统还有7天即将到期，请及时续费，以免影响正常使用');
	
	// 通话结束后发送的短信通话报告
	private $telephoneReportTemplate = array('id' => 5121, 'name' => '通话报告', 'text' => '电话回拨报告：通话时间：{1}，时长：{2}，来电号码：{3}，地理位置：{4}');
	
	// 用户注册成功后，通知用户名和密码的短信
	private $registerSuccessTemplate = array('id' => 6520, 'name' => '注册账号成功通知', 'text' => '感谢注册使用{1}，您的注册账号：{2}，密码：{3}，有任何问题欢迎咨询{4}，官网：{5}');
	
	// 用户注册后，一直没有登陆的短信提醒
	private $registerNoLoginNotice = array('id' => 6711, 'name' => '注册未登陆提醒', 'text' => '感谢您注册和使用{1}，您注册后一直未登陆，如有任何问题，请随时和我们客服人员联系。电话：{2}，官网：{3}');
	
	// 用户注册后，登陆过，但是最近没有登陆的提醒
	private $noLoginNotice = array('id' => 6712, 'name' => '普通未登陆提醒', 'text' => '您注册的{1}，已有{2}天没有登陆过了，请记得多多登陆，才有更多客户哦~任何问题可随时咨询{3}');
	
	// 用户有新的留言提醒
	private $newCommentNotice = array('id' => 6713, 'name' => '留言提醒', 'text' => '您的网站有{1}条未读留言，请及时登陆查看，以免错过客户哦~有任何问题，随时咨询：{2}');
	
	
	
	
	//退款成功模板，第一个参数是联系电话，第二个参数为商家名称，第三个参数为订单号
	public function refundSuccess($toNumbers, $enterpriseName,$orderNumber){
		return $this->sendMsg($this->refundTemplate['id'], $toNumbers,$enterpriseName.','.$orderNumber);
	}
	
	//付款成功模板，第一个参数是联系电话，第二个参数为商家名称，第三个参数为订单号
	public function paySuccess($toNumbers, $enterpriseName,$orderNumber){
		return $this->sendMsg($this->paySuccessTemplate['id'], $toNumbers,$enterpriseName.','.$orderNumber);
	}
	
	//下单成功模板，第一个参数是联系电话，第二个参数为商家名称，第三个参数为订单号
	public function orderSuccess($toNumbers, $enterpriseName,$orderNumber){
		return $this->sendMsg($this->orderSuccessTemplate['id'], $toNumbers,$enterpriseName.','.$orderNumber);
	}
	
	//取消定单模板，第一个参数是联系电话，第二个参数为商家名称，第三个参数为订单号
	public function cancelOrder($toNumbers, $enterpriseName,$orderNumber){
		return $this->sendMsg($this->cancelOrderTemplate['id'], $toNumbers,$enterpriseName.','.$orderNumber);
	}
	
	// 发送注册码短信, 第一个参数是要发送的验证码，由外面的程序生成， 第二个参数是发送给谁
	public function sendRegistYanzheng($yanzhengma, $toNumbers){
		return $this->sendMsg($this->yanzhengTemplate['id'], $toNumbers, $yanzhengma.",5");
	}
	
	// 重置(找回)密码短信, 第一个参数是要发送的验证码id，由外面的程序生成， 第二个参数是发送给谁
	public function findCodeYanzheng($yanzhengma, $toNumbers){
		return $this->sendMsg($this->yanzhengTemplate['id'], $toNumbers, $yanzhengma.",5");
	}
	
	
	// 发送7天到期提醒短信, $toNumbers最多可发送100个号码， 用英文的逗号隔开
	public function send7dayDeadline($toNumbers){
		return $this->sendMsg($this->deadline7dayTemplate['id'], $toNumbers,"");
	}
	
	// 在通话结束后发送的短信通话报告
	public function sendTelephoneReport($toNumbers, $create_time, $chat_time, $guest_tel, $guest_location){
		return $this->sendMsg($this->telephoneReportTemplate['id'], $toNumbers,$create_time.",". $chat_time.",". $guest_tel.",". $guest_location);
	}
	
	// 发送注册成功的通知用户名和密码的短信
	public function sendRegisterSuccessMsg($toNumbers, $appName, $username, $passwd, $serviceTel){
		return $this->sendMsg($this->registerSuccessTemplate['id'], $toNumbers,$appName.",". $username.",". $passwd.",". $serviceTel.","."www.baidu.com");
	}
    
	
	// 发送提醒短信，提醒注册后一直没有登陆的用户
	public function sendRegisterNoLoginNotice($toNumbers){
		return $this->sendMsg($this->registerNoLoginNotice['id'], $toNumbers,"微动平台,400-089-6501,www.baidu.com");
	}
	
	// 注册后有登陆的，但是最近几天没有登陆的用户
	public function sendNoLoginNotice($toNumbers, $days){
		return $this->sendMsg($this->noLoginNotice['id'], $toNumbers,"微动平台,".$days.",400-089-6501");
	}
	
	// 注册后有登陆的，但是最近几天没有登陆的用户
	public function sendNewCommentNotice($toNumbers, $comment_number){
		return $this->sendMsg($this->newCommentNotice['id'], $toNumbers,$comment_number.",400-089-6501");
	}
	
	// 访客评价--差评通知
	/*toNumbers:电话号码
	 *account_name: 账号名
	 *user_serve_name:需要通知的管理员名称
	 *getCommentUserName：收到差评的工号
	 *score:差评-不满意等
	 * */
	public function sendBadCommentSmsNotice($toNumbers,$account_name,$user_serve_name,$getCommentUserName,$score){
		
	}
	
	
	// 发送短信的基本函数，其他函数调动本函数发送短信
	/* 返回值
	 * 成功返回0；
	 * 失败返回respcode
	 */
	private function sendMsg($templateId, $toNumbers, $param){
		$options['accountsid'] 	= C("YZX_ACCOUNT_SID");
		$options['token']		= C("YZX_AUTH_TOKEN");
		$appId					= C("YZX_WEACT_APP_ID");
		
		$ucpaas = new Ucpaas($options);
		$result = $ucpaas->templateSMS($appId,$toNumbers,$templateId,$param);
		ddlog::MakeRecord("发送短信 ". $accountSid." ".$accountToken." ".$appId." ".$toNumbers." ".$templateId." ".$param,0);
		ddlog::MakeRecord($result,0);
		
		// 发送短信后返回的结果
		$sms_result_array = json_decode($result,true);
		
		// 打印log
		ddlog::MakeRecord("发送短信结果 " . $templateId. " " .$toNumbers." ". $sms_result_array['resp']['respCode'] . " ". $sms_result_array['resp']['failure'].
		" ". $sms_result_array['resp']['templateSMS']['createDate']. " ". $sms_result_array['resp']['templateSMS']['smsId'] , 0);
		
		if($sms_result_array['resp']['respCode'] == "000000"){
			return 0;
		}
		return $sms_result_array['resp']['respCode'];
	}

}
?>