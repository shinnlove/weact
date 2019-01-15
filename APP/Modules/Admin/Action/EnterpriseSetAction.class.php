<?php
/**
 * 商家在微平台的信息设置管理控制器。
 * @author 赵臣升、胡福玲、张华庆、万路康。
 * 作者一共有4个人。
 */
class EnterpriseSetAction extends PCViewLoginAction {
	
	/**
	 * 因为企业设置控制器会变更企业信息，所以要及时刷新当前企业用户登录信息。
	 * @return array $einfo 企业信息
	 */
	private function refreshEnterpriseInfo() {
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$einfo = M ( 'einfo_manage' )->where ( $emap )->find ();
		session ( 'curEnterprise', $einfo ); // 更新session
		return $einfo;
	}
	
	/**
	 * -------------PartI：企业信息页面，及一些企业信息设置处理函数。---------------------
	 */
	
	/**
	 * 企业信息页面的展示。
	 */
	public function enterpriseInfo() {
		$einfo = $this->refreshEnterpriseInfo (); // 更新缓存中的企业信息
	
		$einfo ['e_index_logo'] = assemblepath ( $einfo ['e_index_logo'] ); // 主页logo图片使用全路径
		$einfo ['e_square_logo'] = assemblepath ( $einfo ['e_square_logo'] ); // 正方形logo图片使用全路径
		$einfo ['e_rect_logo'] = assemblepath ( $einfo ['e_rect_logo'] ); // 矩形logo图片使用全路径
		$einfo ['qr_code'] = assemblepath ( $einfo ['qr_code'] ); // 二维码图片使用全路径
	
		$this->einfo = $einfo;
		$this->display ();
	}
	
	/**
	 * 绑定公众号页面。
	 */
	public function bindPublicNumber() {
		// 缩写enterpriseinfo→ei
		$eimap = array (
				'e_id' => $_SESSION ['curEnterprise'] [e_id],
				'is_del' => 0
		);
		$eitable = M ( 'enterpriseinfo' );
		$this->einfo = $eitable->where ( $eimap )->find ();
		$this->display ();
	}
	
	/**
	 * 配置微信支付页面。
	 */
	public function wechatPayV2() {
		$this->appinfo = $this->refreshEnterpriseInfo ();
		$this->display ();
	}
	
	/**
	 * 设置微信支付V3页面视图。
	 */
	public function wechatPayV3() {
		$emap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$this->secretinfo = M ( 'secretinfo' )->where ( $emap )->find ();
		$this->appinfo = $this->refreshEnterpriseInfo ();
		$this->display ();
	}
	
}
?>