<?php
import ( 'MobileGuestAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileGuestAction
/**
 * 本控制器是餐饮微商城。
 * @author 赵臣升。
 * CreateTime:2014/12/03 12:25:36.
 * 载入Home分组下的GuestCommon控制器继承后就有授权登录和企业信息了。
 */
class CateShopAction extends MobileGuestAction {
	/**
	 * 餐饮微商城主页视图。
	 */
	public function onlineShop2() {
		$this->display();
	}
}
?>