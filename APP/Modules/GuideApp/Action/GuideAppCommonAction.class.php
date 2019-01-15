<?php
/**
 * 导购APP总管控制器。
 * @author 微动团队，赵臣升。
 * 本控制器主要处理
 */
class GuideAppCommonAction extends Action {
	// _initialize为初始化函数
	public function _initialize() {
		// Step1：如果开启打开页面需要Token验证，首先验证token是否正确
		$access_token = I ( 'token', '-1' ); // 接收token
		
		// Step2：处理页面传参
		$eid = I ( 'eid' ); // 接收商家编号
		$sid = I ( 'sid' ); // 接收分店编号
		$gid = I ( 'gid' ); // 接收导购编号
		if (empty ( $eid ) || empty ( $sid ) || empty ( $gid )) {
			$this->error ( "参数错误，网络繁忙，请稍后再试！" ); // 处理非法打开
		}
		
		// Step3：检查当前商家下、当前分店下当前导购是不是存在，防止URL参数错误的情况
		$checkguide = array (
				'e_id' => $eid,
				'subbranch_id' => $sid,
				'guide_id' => $gid,
				'is_del' => 0
		);
		$guidetable = M ( 'shopguide' ); // 实例化导购表
		$ginfo = $guidetable->where ( $checkguide )->find (); // 尝试找出导购信息
		if (! $ginfo) {
			$this->error ( "品牌、分店或导购参数错误，网络繁忙，请稍后再试！" ); // 处理URL错误参数
		}
		
		// Step4：将参数保存在$this->中
		$this->eid = $eid; // 保存、推送给前台商家编号
		$this->sid = $sid; // 保存、推送给前台分店编号
		$this->gid = $gid; // 保存、推送给前台导购编号
	}
}
?>