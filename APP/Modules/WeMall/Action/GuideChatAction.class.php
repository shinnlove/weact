<?php
/**
 * 聊天窗控制器。
 */
class GuideChatAction extends GuestMallAction {
	
	/**
	 * 聊天窗页面
	 */
	public function onlineWebChat() {
		// 接收参数
		$guide_id = I ( 'gid' );
		$customer_id = I ( 'cid' );
		if (empty ( $guide_id ) || empty ( $customer_id )) {
			$this->error ( "请先选择该导购再咨询。" );
		}
		$this->gid = $guide_id; 	// 推送导购编号
		$this->cid = $customer_id; 	// 推送顾客编号
		$this->display();
	}
	
}
?>