<?php
/**
 * 后台管理分店的控制器。
 * @author 梁思彬。
 * @modifyauthor 胡福玲。
 * 控制器原使用表：enterpriselocation，现在使用分店表subbranch。
 */
class SubbranchAction extends PCViewLoginAction {
	
	/**
	 * 店铺分店一览视图。
	 */
	public function subbranches() {
		$e_id = $_SESSION['curEnterprise']['e_id'];
		$this->e_id = $e_id;
		$this->display ();
	}
	
	/**
	 * 添加分店信息视图。
	 * 设计思路：现在后台生成好编号，传到前台就可以在ueditor上传的时候进行分文件夹。
	 */
	public function addSubbranch() {
		$this->sid = md5 ( uniqid ( rand (), true ) );				//先生成一个subbranch_id方便上传图片
		$this->display ();
	}
	
	/**
	 * 分店初始化视图，需要展示subbranch_id，让ueditor传图片到相应的文件夹下。
	 */
	public function editSubbranch() {
		$subbranchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'subbranch_id' => I ( 'sid' )
		);
		$sbtable = M('subbranch');
		$subbranchinfo = $sbtable->where($subbranchmap)->find();
		$subbranchinfo['image_path'] = assemblepath( $subbranchinfo['image_path'] );
		$subbranchinfo['signs_path'] = assemblepath( $subbranchinfo['signs_path'] );
		$this->sinfo = $subbranchinfo;
		$this->display();
	}
	
	/**
	 * 分店初始化页面，需要传递subbranch_id，让ueditor传图片到相应的文件夹下。
	 */
	public function preeditLocation() {
		$current_enterprise = session ( 'curEnterprise' );
		$map = array (
				'e_id' => $current_enterprise ['e_id'],
				'location_id' => I ( 'location_id' )
		);
		$enterprise_location = M ( 'subbranch' );
		$enterprise_location = $enterprise_location->where ( $map )->select ();
		$location_address = divide ( $enterprise_location [0] ['location_address'], ' ' );
		$enterprise_location [0] ['province'] = $location_address [0];
		$enterprise_location [0] ['city'] = $location_address [1];
		$enterprise_location [0] ['address'] = $location_address [2];
		$this->enterprise_location = $enterprise_location;
		$this->display ();
	}
	
}
?>