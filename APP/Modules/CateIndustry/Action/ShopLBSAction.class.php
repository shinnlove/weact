<?php
import ( 'MobileGuestAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileGuestAction
/**
 * 本控制器处理商家的店铺列表和顾客的LBS定位。
 * @author 赵臣升。
 *
 */
class ShopLBSAction extends MobileGuestAction {
	/**
	 * 店铺列表。
	 */
	public function shopList() {
		
		$submap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$subbranch = M ('subbranch')->where($submap)->select();
		for($i = 0; $i < count ( $subbranch ); $i ++){
			$subbranch [$i] ['image_path'] = assemblepath( $subbranch [$i] ['image_path'] );
		}
		$this->scount = count ( $subbranch );
		$this->sinfo = $subbranch;
		$this->display();
	}
}
?>