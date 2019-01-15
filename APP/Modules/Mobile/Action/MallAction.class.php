<?php
import ( "Class.API.WeChatJSSDK.jsconfig", APP_PATH, ".php" ); // 载入jssdk
/**
 * 移动端集成商城主页。
 * @author 赵臣升
 * CreateTime:2015/07/09 14:28:36.
 */
class MallAction extends MobileGuestAction {
	
	/**
	 * 集成商城主页。
	 */
	public function index() {
		// 处理商家的信息详情（随机选一个）
		$nid = "-1"; // 商家某信息详情导航
		$emap = array (
				'e_id' => $this->einfo ['e_id'], // 当前商家
				'is_del' => 0
		);
		$infolist = M ( 'simpleinfo' )->where ( $emap )->order ( "create_time desc" )->limit ( 1 )->select ();
		if (! empty ( $infolist )) {
			$nid = $infolist [0] ['nav_id']; // 取导航
		}
		$this->nid = $nid; // 信息导航编号
		
		// 处理商家的实体店铺
		$sid = "-1";
		$subbranchlist = M ( 'subbranch' )->where ( $emap )->limit ( 1 )->select ();
		if (! empty ( $subbranchlist )) {
			$sid = $subbranchlist [0] ['subbranch_id']; // 如果有添加店铺，取出第一条获得的店铺的编号
		}
		$this->sid = $sid;
		
		// 处理商家的分销店铺
		$this->did = "-1"; // 分销商城暂时不开放2015/07/09 16:31:25.
		
		// 处理商家的活动导航
		
		// 处理商家的JSSDK
		$jssdk = new JSSDK ( $this->einfo ['e_id'], $this->einfo ['appid'], $this->einfo ['appsecret'] ); 
		$signpackage = $jssdk->getSignPackage (); 
		$this->signpackage = $signpackage; 
		
		// 显示页面
		$this->display ();
	}
	
}
?>