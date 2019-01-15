<?php
/**
 * 本控制器提供微动平台用户注册登录的服务。
 * @author 赵臣升。
 * CreateTime：2014/12/18 13:36:25.
 */
class RegisterLoginAction extends Action {
	/**
	 * 在customerinfo表中为新用户代注册账号。
	 * 只在没有该微信用户记录的情况下进行代注册。
	 *
	 * @param array $einfo
	 *        	企业信息
	 * @param array $wechatuserinfo
	 *        	微信用户信息
	 * @param string $subbranch_id
	 *        	哪家分店信息（默认托管注册总店信息）
	 * @return boolean $isregistered 是否注册（不注册可能是失败，也可能是已经存在不进行注册）
	 */
	public function wechatAuthRegister($einfo = NULL, $wechatuserinfo = NULL, $subbranch_id = '-1') {
		$isregistered = false; // 代注册标记
		if (! empty ( $wechatuserinfo )) {
			$customertable = M ( 'customerinfo' );
			$checkexist = array (
					'openid' => $wechatuserinfo ['openid'],
					'e_id' => $einfo ['e_id'],
					'is_del' => 0 
			);
			$existinfo = $customertable->where ( $checkexist )->find (); // 看是否有该用户
			if (! $existinfo) {
				$sex = $wechatuserinfo ['sex'] == 1 ? '男' : '女';
				$newcustomerinfo = array (
						'customer_id' => date ( 'YmdHms' ) . randCode ( 4, 1 ),
						'openid' => $wechatuserinfo ['openid'],
						'nick_name' => $wechatuserinfo ['nickname'],
						'e_id' => $einfo ['e_id'],
						'sex' => $sex,
						'register_time' => time (),
						'subordinate_shop' => $subbranch_id, 
						'remark' => "用户没有注册或扫码，直接进入平台，系统默认为其代注册本账号。"
				);
				$isregistered = $customertable->data ( $newcustomerinfo )->add (); // 新用户（第一次授权登录的用户）代注册
			}
		}
		return $isregistered;
	}
	
	/**
	 * 本函数故意不设置空数组$ciresult = array()，就算检测出空数据也直接原样返回，这样方便其他控制器判断代码不更改。
	 *
	 * @param string $e_id        	
	 * @param string $customer_id        	
	 * @return array $ciresult
	 */
	public function getCustomerInfoById($e_id = '', $customer_id = '') {
		$cimap = array (
				'customer_id' => $customer_id,
				'e_id' => $e_id,
				'is_del' => 0 
		);
		$ciresult = M ( 'customerinfo' )->where ( $cimap )->find ();
		return $ciresult;
	}
	
	/**
	 * 根据用户openid从customerinfo表中获取其信息。
	 * 本函数故意不设置空数组$ciresult = array()，就算检测出空数据也直接原样返回，这样方便其他控制器判断代码不更改。
	 *
	 * @param string $e_id
	 *        	商家编号
	 * @param string $openid
	 *        	用户微信号
	 * @return array $customerinfo 顾客表的用户信息
	 */
	public function getCustomerInfoByOpenId($e_id = '', $openid = '') {
		$cimap = array (
				'openid' => $openid,
				'e_id' => $e_id,
				'is_del' => 0 
		);
		$ciresult = M ( 'customerinfo' )->where ( $cimap )->find ();
		return $ciresult;
	}
}
?>