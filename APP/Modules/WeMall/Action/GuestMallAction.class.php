<?php
/**
 * 游客访问控制器，不对登录做控制
 * @author zhaochensheng
 * CreateTime:2015/04/29 14:02:25.
 */
class GuestMallAction extends Action {
	/**
	 * 游客控制器初始化。
	 */
	public function _initialize() {
		$subbranch_id = I ( 'sid' ); // 接收分店编号
		if (empty ( $subbranch_id )) $this->error ( "please bind a shop id." ); // 没有接收到分店编号
		
		$subbranchmap = array (
				'subbranch_id' => $subbranch_id, // 分店编号
				'is_del' => 0
		);
		$sinfo = M ( 'subbranch' )->where ( $subbranchmap )->find (); // 分店信息
		if (! $sinfo) $this->error ( "please bind a shop id." ); // 查无此分店
		
		// 分店编号正确，将信息写入当前控制器的全局变量中
		$sinfo ['image_path'] = assemblepath ( $sinfo ['image_path'] ); // 组装店铺logo
		$sinfo ['signs_path'] = assemblepath ( $sinfo ['signs_path'] ); // 组装店招
		$this->sinfo = $sinfo; // 分店信息
		$this->eid = $sinfo ['e_id']; // 分店商家编号
		$this->sid = $sinfo ['subbranch_id']; // 分店编号
		
	}
	
	/**
	 * 登录页面。
	 */
	public function login() {
		$lastvisit = I ( 'from' ); // 最后一次访问的页面URL
		if (empty ( $lastvisit )) {
			$lastvisit = U ( 'WeMall/Store/storeIndex', array ( 'sid' => $this->sid ), '' ); // 如果没有接收到来自的地址，默认访问店铺首页
		}
		$this->from = $lastvisit; // 推送页面来自的位置
		$this->display ();
	}
	
}
?>