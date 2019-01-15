<?php
class ImportBaiShengDataAction extends Action {
	/**
	 * 通过顾客手机号码获得其信息。
	 */
	public function getCustomer() {
		$url = 'http://60.191.185.134:8011/ipos/api/vip_api.php';
		$params = array(
				'api_name' => 'api_user',
				'api_key' => '1315922587',
				'api_token' => 'a8d3bbc5cf84f91d39c4bd054a67c642',
				'opvip' => 1,
				'method' => 'get_custinfo',
				'mobile' => '18867158375'
		);
		$jsoninfo = http($url, $params);
		p($jsoninfo);die;
	}
}
?>