<?php
/**
 * 线上云总店商城游客管理控制器。
 * @author 赵臣升
 * CreateTime:2015/10/02 15:30:36.
 * 该控制器是原GuestCommon的简化版本，处理游客的业务逻辑更得心应手。
 */
class MobileGuestAction extends Action {
	/**
	 * 游客视图控制器，初始化企业信息推送等。
	 */
	public function _initialize() {
		// 接受参数部分
		$data = array (
				'e_id' => I ( 'e_id' ), 											// 页面接收传来的e_id
				'is_del' => 0,  													// 在服务期内，没有被删除的企业
		);
		if (empty ( $data ['e_id'] )) $this->redirect ( 'Home/Tip/bindEID' ); 		// 如果页面没有接收到e_id，则直接输出请绑定一个ID号
		// 校验企业部分
		$einfo = M ( 'enterpriseinfo' )->where ( $data )->find ();
		if (! $einfo) $this->redirect ( 'Home/Tip/bindEID' ); 						// 如果企业编号错误，或者企业已经被删除，直接跳转错误页面（2014/11/13 03:10:25
		// 组装信息部分
		$einfo ['e_index_logo'] = assemblepath ( $einfo ['e_index_logo'], true ); 	// 组装路径：2015/10/02 20:40:30，企业主业logo
		$einfo ['e_square_logo'] = assemblepath ( $einfo ['e_square_logo'], true ); // 组装路径：2014/09/17 20:40:30，使用绝对路径供分享用
		$einfo ['e_rect_logo'] = assemblepath ( $einfo ['e_rect_logo'], true ); 	// 组装路径：2014/09/17 20:40:30
		$einfo ['qr_code'] = assemblepath ( $einfo ['qr_code'], true ); 			// 组装路径：2015/10/02 20:40:30，企业二维码
		// 推送信息部分
		$this->enterprise = $einfo; 												// 始终推送到前台
		$this->einfo = $einfo; 														// 子类使用父类的$this->einfo企业信息变量（以后打算不用enterprise）
		$this->e_id = $data ['e_id']; 												// 推送e_id到前台，保留传统简洁风格（除非页面更改，否则必须有这个参数，后面的可能要用）
        // 从cookie中获取sso_token
        $this->sso_token = cookie('sso_token');
	}
	
}
?>