<?php
class IndexAction extends Action {
	/**
	 * 分店系统登录页面
	 */
	public function index() {
		$this->display();
	}
	
	/**
	 * 分店登录提交post函数。
	 */
	public function login() {
		if (! IS_POST) _404 ( '您要访问的页面不存在!', "{:U('Subbranch/Index/index')}");
		
		$ajaxresult = array();  //定义ajax返回参数
		
		//验证码是否输入正确
		$verify = $_SESSION['verify'];		//获取缓存中的验证码为MD5格式
		$testverify = md5($_REQUEST['ver']) ;
		
		if($verify != $testverify){
			//验证码输入错误，返回重新输入
			$ajaxresult = array(
					'errCode' => 10001,
					'errMsg' => '验证码输入错误！'
			);
		}else{
			//验证码输入正确，验证账号密码是否正确
			$logincheck = array(
					'auth_account' => I ( 'acc' ),
					'auth_password' => md5( I ( 'pwd' ) ),
					'is_del' => 0
			);
			$authinfo = M( 'login_subbranch' )->where( $logincheck )->find();
			
			if(!$authinfo){
				//账号或密码输入错误，返回重新输入
				$ajaxresult = array(
						'errCode' => 10002,
						'errMsg' => '用户名或密码错误!'
				);
			}else{
				//账号密码通过验证，检查该账号所属商家是否在服务有效期内，若不在，分店账号不可登录。
				$starttime = strtotime($authinfo['service_start_time']);
				$endtime = strtotime($authinfo['service_end_time']);
				$nowtime = time();
				
				if($nowtime<$starttime || $nowtime>$endtime){
					$ajaxresult = array(
							'errCode' => 10003,
							'errMsg' => '总店服务未开通，您没有访问权限!'
					);
				}else{
					//检查商家是否开放该分店登录权限,auth_open为0表示暂未开放权限
					if($authinfo['auth_open'] == 0){
						$ajaxresult = array(
								'errCode' => 10004,
								'errMsg' => '总店尚未向您开放访问权限!'
						);
					}else {
						//账号信息正常，将分店信息存入session
						session('curSubbranch', $authinfo);
						$ajaxresult = array(
								'errCode' => 0,
								'errMsg' => 'ok!'
						);
					}
				}
			}
		}
		$this->ajaxReturn($ajaxresult);
	}
	
	/**
	 * 分店用户登出post处理函数。
	 */
	public function loginOut(){
		session('curSubbranch',null);
		$this->ajaxReturn ( array ( 'errCode' => 0, 'errMsg' => '注销成功!' ), 'json' );
	}
	
	/**
	 * 添加验证码。
	 */
	public function getRandomVerifyCode() {
		import('ORG.Util.Image');
		Image::buildImageVerify (4,1,'png',60,23);
	}
	
	/**
	 * 登录成功后欢迎页面。
	 */
	public function welcome(){
		$this->display();
	}
}