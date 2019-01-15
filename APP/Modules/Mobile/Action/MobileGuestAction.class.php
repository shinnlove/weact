<?php
/**
 * 移动端商城游客控制器，管理不同商家的移动端商城参数。
 * @author 赵臣升
 * CreateTime:2015/07/09 14:58:36.
 */
class MobileGuestAction extends Action {
	public function _initialize() {
		$e_id = I ( 'e_id' ); 														// 页面接收传来的e_id
		if (empty ( $e_id )) $this->redirect ( 'Home/Tip/bindEID' ); 				// 如果页面没有接收到e_id，则直接输出请绑定一个ID号
		$emap = array (
				'e_id' => I ( 'e_id' ), 											// 页面接收传来的e_id
				'is_del' => 0  														// 在服务器内，没有被删除的企业
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		if (! $einfo) $this->redirect ( 'Home/Tip/bindEID' ); 						// 如果企业编号错误，或者企业已经被删除，直接跳转错误页面（2014/11/13 03:10:25
		
		$einfo ['e_square_logo'] = assemblepath ( $einfo ['e_square_logo'], true ); // 组装路径
		$einfo ['e_rect_logo'] = assemblepath ( $einfo ['e_rect_logo'], true ); 	// 组装路径
		
		$this->einfo = $einfo; 														// 子类使用父类的$this->einfo企业信息变量
		$this->e_id = $e_id;
	}
}
?>