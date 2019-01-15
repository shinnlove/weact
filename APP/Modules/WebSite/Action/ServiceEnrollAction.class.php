<?php
/**
 * 服务注册控制器。
 * @author 赵臣升。
 * CreateTime:2015/05/19 13:56:25.
 */
class ServiceEnrollAction extends Action {
	/**
	 * 注册登记信息页面视图。
	 */
	public function register() {
		$this->display ();
	}
	
	/**
	 * 表单提交处理的控制器。
	 */
	public function registerConfirm() {
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist!" ); // 表单处理action防止恶意打开。
		}
		
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		$applytable = M ( 'serviceapply' ); // 实例化申请表
		
		// 接收提交信息参数
		$registerinfo = array (
				'apply_id' => md5 ( uniqid ( rand (), true ) ), 		// 生成主键
				'company_name' => I ( 'companyname' ), 					// 公司名称
				'industry' => I ( 'industry' ), 						// 所属行业
				'location' => I ( 'location' ), 						// 所在地理位置
				'brand' => I ( 'brand' ), 								// 品牌名称
				'name' => I ( 'name' ), 								// 提交者姓名
				'cellphone' => I ( 'cellphone' ), 						// 手机号
				'email' => I ( 'email' ), 								// 电子邮件
				'wechat_account' => I ( 'wechataccount' ), 				// 个人微信账号
				'qq_number' => I ( 'qqnumber' ),  						// 个人QQ号码
				'wechat_public' => I ( 'wechatpublic' ), 				// 服务号微信账号名称
				'appid' => I ( 'appid' ), 								// 微信账号appid
				'appsecret' => I ( 'appsecret' ), 						// 微信账号appsecret
				'original_id' => I ( 'originalid' ), 					// 微信账号原始id
				'plan_time' => I ( 'plantime' ), 						// 计划开始时间
				'self_property' => I ( 'selfproperty' ), 				// 企业信息
				'recognize_way' => I ( 'recognizeway' ), 				// 认知微动的方式
				'focus_question' => I ( 'focusquestion' ), 				// 关注的焦点问题
				'your_expiration' => I ( 'yourexpiration' ), 			// 期望与期待
				'add_time' => time ()
		);
		
		// 检测是否有重复提交过，检测方式是，微信appid和appsecret
		$checkexist = array ( 
				'appid' => $registerinfo ['appid'], // 企业appid
				'appsecret' => $registerinfo ['appsecret'], // 企业appsecret
				'is_del' => 0
		);
		$existresult = $applytable->where ( $checkexist )->count (); // 统计当前提交信息中有相同appid和appsecret的记录是否存在
		if ($existresult) {
			// 如果有相同的信息
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "您已经提交过，请不要重复提交！";
			$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
		}
		
		// 真正提交表单
		$registerresult = $applytable->add ( $registerinfo ); // 添加注册信息
		if ($registerresult) {
			// 如果插入成功
			$ajaxresult ['errCode'] = 0; 
			$ajaxresult ['errMsg'] = "ok";
		} else {
			// 如果插入失败
			$ajaxresult ['errCode'] = 10003; 
			$ajaxresult ['errMsg'] = "信息录入失败，请不要频繁提交！";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 服务提交成功。
	 */
	public function registerSuccess() {
		$this->display ();
	}
}
?>