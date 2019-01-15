<?php
/**
 * 简单信息增删改查控制器
 * @author Administrator
 *
 */
class SimpleInfoAction extends PCViewLoginAction {
	
	/*
	 * 表名及列名定义
	 */
	var $table_name = 'simpleinfo';
	var $cl_simple_info_id = 'simple_info_id';
	var $cl_eid = 'e_id';
	var $cl_nav_id = 'nav_id';
	var $cl_title = 'title';
	var $cl_html_content = 'html_content';
	var $cl_remark = 'remark';
	var $cl_is_del = 'is_del';
	
	/**
	 * 信息设置主页。
	 */
	public function index() {
		$this->e_id = $_SESSION ['curEnterprise'] ['e_id'];
		$this->display ();
	}
	
	/**
	 * 读取
	 */
	public function read() {
		$nav_id = I ( 'nav_id' );
		$nav_name = I ( 'nav_name' );
		
		if (! empty ( $nav_id )) {
			$this->simple_info_id = '';
			$this->nav_id = $nav_id;
			$this->e_id = $_SESSION ['curEnterprise'] ['e_id'];
			$this->nav_name = $nav_name;
			$this->title = '';
			$this->html_content = '';
			$this->state = 'add';
			
			$db_table = M ( $this->table_name );
			$where_string = array (
					$this->cl_eid => $_SESSION ['curEnterprise'] ['e_id'],
					$this->cl_nav_id => $nav_id,
					$this->cl_is_del => 0 
			);
			$result = $db_table->where ( $where_string )->find ();
			if ($result) {
				$this->simple_info_id = $result [$this->cl_simple_info_id];
				$this->title = $result [$this->cl_title];
				$this->html_content = $result [$this->cl_html_content];
				$this->state = 'edit';
			}
			$this->display ();
		}
	}
	
	/**
	 * 信息模板
	 */
	public function infoTemplate() {
		$tplinfo = array (
				'tplpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'] 
		);
		$tplManage = A ( 'Admin/TplManage' );
		$this->tplresult = $tplManage->EntrustTemplate ( $tplinfo );
		$this->display ();
	}
}
?>