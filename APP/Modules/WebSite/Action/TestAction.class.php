<?php
class TestAction extends GuestCommonAction {
	
	public function authorize() {
		$openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid']; // 获取微信用户信息
		$this->infojson = json_encode ( $openid );
		$this->display ();
	}
	
}
?>