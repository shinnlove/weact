<?php
/**
 * 线上云总店商城登录顾客管理控制器。
 * @author 赵臣升
 * CreateTime:2015/10/02 15:30:36.
 * 该控制器是原LoginCommon的简化版本，处理登录的业务逻辑更得心应手。
 *
 * // TODO：SSO分离，账号登录没问题，微信授权这一块还要再做调整和测试。本分支先移除SSO，让链路先打通。代码迁移到MobileSSOLoginAction中。
 *
 */
class MobileSSOLoginAction extends MobileGuestAction {
    /**
     * 登录控制器初始化。
     */
    public function _initialize() {
        parent::_initialize (); 	// 先调用父类的初始化构造函数，初始化企业信息推送等
//		$this->checkLocalDebug (); 	// 检查本地调试
//        unset($_SESSION ['currentcustomer']);
//		if (empty ( $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] )) {
        if (empty($this->sso_token)) {
            // TODO：这里从原来检验session变成检验请求头的cookie里的token
            $requesturl = C ( 'DOMAIN' ) . $_SERVER ['REQUEST_URI']; 					// 获取当前要访问的需登录的URL地址
            // 当前商家的顾客没有登录，则根据不同方式去登录
            if ($this->einfo ['login_style'] == 1) {
                // 采用微信静默授权方式登录
                import ( 'Class.API.WeChatAPI.WechatAuthorize', APP_PATH, '.php' ); 	// 载入微信授权模块
                $wechatauth = new WechatAuthorize ();
                $wechatauth->authorizeForCode ( $this->einfo, $requesturl ); 			// 静默授权
            } else {
                // 其他一律采用默认微动登录方式
                // TODO：这里开始跳转到sso登录
                $back_url = "http://www.we-act.cn" . $_SERVER['REQUEST_URI'];
                $this->getSSOToken("weact", $this->einfo ['e_id'], $back_url);

                // 这是微动老代码
                $loginurl = U ( 'Home/GuestHandle/customerLogin', '', '', false, true );
                $loginparams = "?e_id=" . $this->einfo ['e_id'] . "&redirecturi=" . urlencode ( $requesturl ); // 必须是这种路由拼接方式
                header ( 'Location: ' . $loginurl . $loginparams ); 					// 带上回跳参数转到登录页面
            }
        } else {
            // 常量
            $verify_url = "http://sso.we-act.cn/weactplus/public/sso/verify/ssotoken";
            $header = array ( 'Content-type' => 'text/html', 'charset' => 'utf-8' ); // 定义https请求文件头
            $params = array (
                'app_name' => 'weact',
                'mch_id' => $this->einfo ['e_id'],
                'sso_token' => $this->sso_token
            );
            $json_info = json_encode ( $params ); // POST接口需要将数据打包成json再POST
            $http_result = http ( $verify_url, $json_info, 'POST', $header, true );
            $result = json_decode ( $http_result, true ); // decode utf-8
            if ($result['errCode'] != 0) {
                // token 无效，继续跳转sso
                $back_url = "http://www.we-act.cn" . $_SERVER['REQUEST_URI'];
                $this->getSSOToken("weact", $this->einfo ['e_id'], $back_url);
            } else {
                // token有效，将用户信息保存起来
                $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] = $result['data']['user_info'];
            }
        }
    }

    /**
     * 检查当前模式是否本地测试环境。
     */
    private function checkLocalDebug() {
        $local_test = C ( 'LOCAL_TEST_ON' ); // 判断是否本地测试（远程服务器端是false配置）
        if ($local_test) {
            $openid = "";
            if ($this->einfo ['e_id'] == "201405011018300002") {
                $openid = "oN_J8ji0p59PDs_X9Dy5lFa43EoU"; 											// 真实模拟一个G5G6测试的openid
            } else if ($this->einfo ['e_id'] == "201406261550250006") {
                $openid = "oeovpty2ScWq6YXxuMG0hY5qHOGA"; 											// 真实模拟一个微动测试的openid
            } else if ($this->einfo ['e_id'] == "201412021712300012") {
                $openid = "ovNEts3zv0n7REfdDfi0WKEOyCSM"; 											// 真实模拟一个米拉雅测试的openid
            }
            // 如需测试其他企业，找到企业下openid即可继续扩展......

            import ( 'Class.BusinessLogic.UserModule.WechatUserModule', APP_PATH, '.php' ); 		// 载入微信授权模块
            $wechatuser = new WechatUserModule ();
            $userinfo = $wechatuser->getUserInfoByOpenId ( $this->einfo, $openid ); 				// 通过openid得到信息
            $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] = $userinfo ['weactuserinfo'];
            $_SESSION ['currentwechater'] [$this->einfo ['e_id']] = $userinfo ['wechatuserinfo'];
        }
    }

    /**
     * 跳转SSO系统获取登录信息。
     *
     * @param string $app_name          请求sso登录的系统名
     * @param string $mch_id            商户id
     * @param string $redirect_uri      请求跳转回来的uri地址
     */
    private function getSSOToken($app_name = 'weact', $mch_id = '', $redirect_uri = 'REDIRECT_URI') {
        $ssoURL = 'http://sso.we-act.cn/weactplus/public/sso/login/auth';
        $redirectURL = $ssoURL . '?app_name=' . $app_name . '&mch_id=' . $mch_id . '&redirect_uri=' . urlencode ( $redirect_uri );
        // 跳转sso并且exit打断代码往下执行
        header ( 'Location:' . $redirectURL);
        exit;
    }

}
?>