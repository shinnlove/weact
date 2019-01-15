<?php
/**
 * 导购聊天请求控制器。
 * @author 赵臣升
 * CreateTime:2015/09/30 17:53:25.
 */ 
class GuideChatRequestAction extends LoginRequestAction {
	
	/**
	 * 用户请求在网页聊天窗上和导购进行对话。
	 */
	public function initWebChat() {
		$customer_id = $_SESSION ['currentcustomer'] ['customer_id'];
		$openid = $_SESSION ['currentwechater'] [$this->eid] ['openid'];
		
		// 尝试找寻顾客所选的导购
		$customerguidemap = array (
				'customer_id' => $customer_id, 
				'openid' => $openid, 
				'is_del' => 0,
		);
		$customerguideinfo = M ( 'guide_wechat_customer_info' )->where ( $customerguidemap )->find (); // 找到顾客所选的导购
		if ($customerguideinfo) {
			$e_id = $customerguideinfo ['e_id'];
			$subbranch_id = $customerguideinfo ['subbranch_id'];
			$guide_id = $customerguideinfo ['guide_id'];
			// 拼接聊天窗信息
			$chaturl = C ( 'DOMAIN' ) . __ROOT__ . "/WeMall/GuideChat/onlineWebChat/sid/" . $subbranch_id . "/eid/" . $e_id . "/gid/" . $guide_id . "/cid/" . $customer_id; // 跳转的网页聊天窗地址
			// 返回聊天窗地址信息
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			$this->ajaxresult ['data'] = array (
					'chaturl' => $chaturl, // 跳转聊天URL
			);
		} else {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "您还没有选择导购，请选择您的专属导购后再聊天。";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 返回给前台消息
	}
	
}
?>