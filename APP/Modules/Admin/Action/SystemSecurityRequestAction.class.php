<?php
class SystemSecurityRequestAction extends PCRequestLoginAction {
	/**
	 * 修改密码前原密码校验
	 */
	public function checkOldPwd(){
		//接收原密码参数
		$oldpwd = I('op');
		$oldpwd = md5($oldpwd);
	
		//查询当前商家账号信息
		$escmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		$escinfo = M('enterprise')->where($escmap)->find();
		$existpwd = $escinfo['password'];
	
		//校验当前输入的原密码与enterprise表中存储的登录密码是否一致
		if ($oldpwd == $existpwd){
			$this->ajaxReturn ( array ( 'status' => 1 ), 'json' );
		} else{
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	/**
	 * 安全中心修改密码确认处理
	 */
	public function alterPwdConfirm(){
		//接收新密码参数
		$oldpwd = I('op');
		$newpwd = I('cp');
	
		//查询当前商家账号信息
		$newpwdmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
	
		);
	
		$ajaxresult = array();
		//校验新密码与原密码是否相同
		if ($oldpwd == $newpwd){
			$ajaxresult = array(
					'errCode' => 10002,
					'errMsg' => '新密码与原密码一致，请不要重复提交！'
			);
		} else {
			//保存新密码
			$newpwd = md5($newpwd);
			$newpwddata['password'] = $newpwd;
			$newpwdresult = M('enterprise')->where($newpwdmap)->save($newpwddata);
	
			if ($newpwdresult){
				$ajaxresult = array(
						'errCode' => 0,
						'errMsg' => 'ok'
				);
			} else {
				$ajaxresult = array(
						'errCode' => 10001,
						'errMsg' => '网络繁忙，请不要重复提交！'
				);
			}
		}
		$this->ajaxReturn ( $ajaxresult );
	}
}
?>