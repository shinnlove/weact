<?php
/**
 * 游客移动端处理，该控制器目的在于对原线上移动端进行重构。
 * @author 赵臣升。
 * 2015/06/18 19:23:25.
 */
class MobileGuestRequestAction extends Action {
	
	/**
	 * ajax返回信息。
	 * @var array $ajaxresult
	 */
	protected $ajaxresult = array (
			'errCode' => 10001,
			'errMsg' => "网络繁忙，请稍后再试！"
	);
	
	/**
	 * 游客移动端控制器初始化。
	*/
	public function _initialize() {
		if (! IS_POST) {
			$this->error ( "Sorry, page not exist！" ); // ajax提交地址不能被随意打开
		}
		
		$e_id = I ( 'e_id' );
		if (empty ( $e_id )) {
			$this->ajaxresult ['errCode'] = 10002;
			$this->ajaxresult ['errMsg'] = "请求失败，商家编号不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 查询企业信息
		$emap = array (
				'e_id' => $e_id, 	// 当前企业
				'is_del' => 0,  	// 没有被删除的
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		if (! $einfo) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "请求失败，商家不在服务区。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		// 改编企业信息
		$einfo ['e_index_logo'] = assemblepath ( $einfo ['e_index_logo'], true ); 	// 组装路径：2014/09/17 20:40:30，使用绝对路径供分享用
		$einfo ['e_square_logo'] = assemblepath ( $einfo ['e_square_logo'], true );
		$einfo ['e_rect_logo'] = assemblepath ( $einfo ['e_rect_logo'], true );
		$einfo ['qr_code'] = assemblepath ( $einfo ['qr_code'], true );
		
		$this->einfo = $einfo; 														// 子类使用父类的$this->einfo企业信息变量（以后打算不用enterprise）
		$this->e_id = $einfo ['e_id'];
	}
	
}
?>