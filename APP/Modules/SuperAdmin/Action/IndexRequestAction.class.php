<?php
/**
 * 超级管理员请求登录控制器。
 * @author 赵臣升
 * CreateTime:2015/08/01 21:13:36.
 */
class IndexRequestAction extends SARequestGuestAction {
	/**
	 * 登录检测。
	 */
	public function loginCheck() {
		$account = I ( 'acc' ); // 接收账号
		$password = I ( 'pwd' ); // 接收密码
		
		if (empty ( $account )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "登录账号不能为空";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		if (empty ( $password )) {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "登录密码不能为空";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		$sacheck = array (
				'acc' => $account, 
				'pass' => md5 ( $password ),
				'is_del' => 0
		);
		$sainfo = M ( 'weactsa' )->where ( $sacheck )->find ();
		if ($sainfo) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			session("administrator", $sainfo); // 保存当前登录
		} else {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "账号密码错误";
		}
		$this->ajaxReturn ( $this->ajaxresult );
	}
}
?>