<?php
/**
 * 餐饮管理控制器
 * @author 胡福玲。
 */
class CateManageAction extends PCViewLoginAction {
	/**
	 * 添加餐饮商品信息页面
	 */
	public function addCate() {
		$this->c_id = md5 ( uniqid ( rand (), true ) ); 													// 随机生成唯一的餐饮商品编号
		$navmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_nav_id' => array('neq','-1'),
				'nav_type' => 4,
				'is_del' =>0
		);
		$nninfo = M('navigation')->where($navmap)->order ( 'create_time asc' )->select();
		$this->nninfo = $nninfo;
		$this->display ();
	}
	
	/**
	 * 编辑餐饮商品信息页面
	 */
	public function editCate() {
		$navmap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'father_nav_id' => array('neq','-1'),
				'nav_type' => 4,
				'is_del' =>0
		);
		$nninfo = M('navigation')->where($navmap)->order ( 'create_time asc' )->select();
		$this->nninfo = $nninfo;										// 推送菜品分类导航信息
	
		$editcinmap = array(
				'cate_id' => I('cate_id'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$cinview = M ( 'cate_image_nav' );
		$cininf = $cinview->where($editcinmap)->find();
		$cininf['micro_path'] = assemblepath($cininf['micro_path']);
		$this->cinfo = $cininf;
	
		$this->display();
	}
	
}
?>