<?php
import ( 'Class.API.WeChatAPI.WeactWechat', APP_PATH, '.php' ); // 载入微动新集成微信SDK
/**
 * 微信用户授权模块。
 * @author 赵臣升。
 * CreateTime:2015/10/01 12:25:36.
 */
class WechatUserModule {
	
	/**
	 * 微信用户授权构造函数。
	 */
	public function __construct() {
		// to do...
	}
	
	/**
	 * 通过微信用户的openid得到其信息。
	 * @param array $einfo 微信用户所属企业编号
	 * @param string $openid 微信用户编号
	 * @return array $userinfo 用户信息
	 */
	public function getUserInfoByOpenId($einfo = NULL, $openid = '', $wechatdetail = FALSE) {
		$userinfo = false;
		if (! empty ( $einfo ['original_id'] )) {
			
			$wechaterinfo = array (); // 微信用户信息
			$customerinfo = array (); // 微动用户信息
			
			// 尝试找寻微信用户信息
			$wechatermap = array (
					'enterprise_wechat' => $einfo ['original_id'], 			// 企业用户的original_id
					'openid' => $openid, 									// 当前企业的微信用户编号
					'is_del' => 0, 
			);
			$wechaterinfo = M ( 'wechatuserinfo' )->where ( $wechatermap )->find (); // 尝试找寻微信用户信息
			if ($wechaterinfo) {
				// 如果有微信用户信息，查找其关联的微动用户信息，如果没有为其代注册一条
				$customertable = M ( 'customerinfo' ); // 实例化微动用户表
				
				// 当前微信用户对应的微动用户
				$weactermap = array (
						'e_id' => $einfo ['e_id'],
						'openid' => $openid,
						'is_del' => 0,
				);
				$customerinfo = $customertable->where ( $weactermap )->find (); // 尝试找寻微动用户信息
				if (! $customerinfo) {
					// 微信openid对应的微动用户还不存在，需要快速注册一条
					$customerinfo = array (
							'customer_id' => md5 ( uniqid ( rand (), true ) ), 	// 微动用户编号
							'openid' => $openid, 								// 微信用户openid
							'customer_name' => "微信授权用户", 						// 微信授权代注册的微动用户名
							'nickname' => "微信授权用户", 							// 微信授权代注册的微动用户昵称
							'e_id' => $einfo ['e_id'], 							// 当前微信授权关联用户所属企业
							'user_type' => 1, 									// 微动用户类型为微信授权关联（重要！！！）
					);
					$customerok = $customertable->add ( $customerinfo ); 		// 注册微动关联微信用户信息
					if (! $customerok) {
						return false; // 微动用户注册不成功，依然返回false，必须保证授权微信用户关联一个微动用户（重要）
					}
				}
				// 程序走到这里，肯定两个用户都有了
				$userinfo = array (
						'weactuserinfo' => $customerinfo, 						// 微动用户信息
						'wechatuserinfo' => $wechaterinfo, 						// 微信用户信息
				);
			} else {
				// 如果没有微信用户信息
				$userinfo = $this->fastRegisterWechatUser ( $einfo, $openid ); 	// 快速注册微信用户并直接返回双平台用户信息
			}
		}
		// 判断是否需要微信用户详细信息
		if ($wechatdetail) {
			// 进一步调用微信用户信息接口获取用户信息
			$openid = $userinfo ['wechatuserinfo'] ['openid']; 			// 取出得到的openid
			$weactwechat = new WeactWechat ( $einfo );
			$getinfo = $weactwechat->getUserInfo ( $openid ); 			// 获取最新用户信息
			if ($getinfo ['subscribe'] == 1) {
				// 关注则继续补充信息
				$userinfo ['wechatuserinfo'] ['nickname'] = $getinfo ['nickname']; 		// 用户的微信昵称
				$userinfo ['wechatuserinfo'] ['sex'] = $getinfo ['sex']; 				// 性别
				$userinfo ['wechatuserinfo'] ['language'] = $getinfo ['language']; 		// 使用语言
				$userinfo ['wechatuserinfo'] ['city'] = $getinfo ['city']; 				// 所在城市
				$userinfo ['wechatuserinfo'] ['province'] = $getinfo ['province']; 		// 所在省份
				$userinfo ['wechatuserinfo'] ['country'] = $getinfo ['country']; 		// 所在国家
				$userinfo ['wechatuserinfo'] ['headimgurl'] = $getinfo ['headimgurl']; 	// 用户头像
				$userinfo ['wechatuserinfo'] ['subscribe_time'] = $getinfo ['subscribe_time']; 	// 用户关注公众号事件
				$userinfo ['wechatuserinfo'] ['remark'] = $getinfo ['remark']; 			// 公众号对用户备注
				$userinfo ['wechatuserinfo'] ['group_id'] = $getinfo ['groupid']; 		// 用户所在公众号组别（注意字段不一样）
			}
		}
		return $userinfo;
	}
	
	/**
	 * 快速注册微信用户。
	 * @param array $einfo 企业用户信息
	 * @param string $openid 当前企业的微信用户openid
	 * @return array|boolean $registerresult 注册成功返回用户信息，注册失败返回false
	 */
	private function fastRegisterWechatUser($einfo = NULL, $openid = '') {
		$registerresult = false;
		if (! empty ( $einfo ['original_id'] )) {
			$timenow = time (); // 当前系统时间戳
			
			$wechatertable = M ( 'wechatuserinfo' ); 	// 实例化微信用户表
			$customertable = M ( 'customerinfo' ); 		// 实例化顾客信息表
			
			$wechaterok = false; 						// 微信用户注册OK
			$customerok = false; 						// 微动用户注册OK
			
			$wechatertable->startTrans (); 				// 开启微信授权快速注册事务
			
			$wechaterinfo = array (
					'user_info_id' => md5 ( uniqid ( rand (), true ) ), 	// 微信用户主键
					'enterprise_wechat' => $einfo ['original_id'], 			// 企业用户的original_id
					'group_id' => 0, 										// 快速注册，默认新注册用户在微信用户的未分组，以后再分类也不迟
					'subscribe' => 0, 										// 快速注册，默认新注册用户未关注，比较保险
					'openid' => $openid, 									// 当前企业的微信用户编号
					'nickname' => "微信用户", 									// 快速注册，默认叫这个名字
					'sex' => 0, 											// 快速注册，默认用户性别未知
					'add_time' => $timenow, 								// 微信用户注册时间
					'latest_active' => $timenow, 							// 新用户注册默认活跃时间是当下
					'remark' => "微信用户模块通过openid统一为授权用户快速注册账号", 		// 快速注册备注
			);
			$wechaterok = $wechatertable->add ( $wechaterinfo ); 			// 注册微信用户信息
			
			// 当前微信用户对应的微动用户
			$weactermap = array (
					'e_id' => $einfo ['e_id'],
					'openid' => $openid, 
					'is_del' => 0,
			);
			$customerinfo = $customertable->where ( $weactermap )->find (); // 尝试找寻微动用户信息
			if ($customerinfo) {
				$customerok = true; // 微动顾客表注册已经OK
			} else {
				// 微信openid对应的微动用户还不存在，需要快速注册一条
				$customerinfo = array (
						'customer_id' => md5 ( uniqid ( rand (), true ) ), 	// 微动用户编号
						'openid' => $openid, 								// 微信用户openid
						'customer_name' => "微信授权用户", 						// 微信授权代注册的微动用户名
						'nickname' => "微信授权用户", 							// 微信授权代注册的微动用户昵称
						'e_id' => $einfo ['e_id'], 							// 当前微信授权关联用户所属企业
						'user_type' => 1, 									// 微动用户类型为微信授权关联（重要！！！）
				);
				$customerok = $customertable->add ( $customerinfo ); 		// 注册微动关联微信用户信息
			}
			
			// 处理微信授权快速注册事务过程
			if ($wechaterok && $customerok) {
				$wechatertable->commit (); // 提交微信用户快速注册事务
				// 快速注册成功返回双用户信息
				$registerresult = array (
						'weactuserinfo' => $customerinfo, 					// 微动用户信息
						'wechatuserinfo' => $wechaterinfo, 					// 微信用户信息
				);
			} else {
				$wechatertable->rollback (); // 回滚微信用户快速注册事务
			}
		}
		return $registerresult;
	}
	
}
?>