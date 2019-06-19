<?php
/**
 * 简单信息增删改查控制器
 * @author Administrator
 *
 */
class SimpleInfoRequestAction extends PCRequestLoginAction {
	
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
	
	/*
	 * 删除
	 */
	/* public function del() {
		$current_enterprise = session ( 'curEnterprise' );
		$id_key = I ( $this->cl_simple_info_id );
		$result = false;
		$db_table = M ( $this->table_name );
		$result = $db_table->Where ( array (
				$this->cl_eid => $current_enterprise [$this->cl_eid],
				$this->cl_simple_info_id => $id_key 
		) )->setField ( $this->cl_is_del, 1 );
		if ($result) {
			echo json_encode ( array (
					'success' => true,
					'msg' => '删除成功!' 
			) );
		} else {
			echo json_encode ( array (
					'msg' => '删除出错!' 
			) );
		}
	} */
	
	/**
	 * 删除简单信息函数
	 * @author hufuling
	 */
	public function delInfo(){
		$delmap = array(
				'simple_info_id' => I('simple_info_id'),
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'nav_id' => I('nav_id'),
				'is_del' => 0
		);
		$sitbl = M('simpleinfo');
		$siInfo = $sitbl->where($delmap)->find();
		$sidelresult = false;
		$ajaxinfo = array();
		if($siInfo){
			$sidelresult = $sitbl->where($delmap)->setField ( 'is_del', 1 );
			if($sidelresult){
				$ajaxinfo=array(
						'errCode' => 0,
						'errMsg' => '删除成功！'
				);
			}else{
				$ajaxinfo=array(
						'errCode' => 10001,
						'errMsg' => '删除失败，请稍后再试！'
				);
			}
		}else{
			$ajaxinfo=array(
					'errCode' => 10002,
					'errMsg' => '该信息不存在，请勿重复操作！'
			);
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/*
	 * 修改
	 */
	public function save() {
		// $current_enterprise = session('curEnterprise');
		
		// 请求输入
		$id_key = I ( $this->cl_simple_info_id );
		$data [$this->cl_title] = I ( $this->cl_title );
		$data [$this->cl_html_content] = stripslashes ( $_REQUEST [$this->cl_html_content] );
		
		$result = false;
		$db_table = M ( $this->table_name );
		$result = $db_table->where ( array (
				$this->cl_simple_info_id => $id_key 
		) )->save ( $data );
		
		if ($result) {
			echo json_encode ( array (
					'success' => true,
					'msg' => '保存成功!' 
			) );
		} else {
			echo json_encode ( array (
					'msg' => '添加出错！' 
			) );
		}
	}
	
	/*
	 * 添加
	 */
	public function add() {
		$db_table = M ( $this->table_name );
		$data [$this->cl_simple_info_id] = md5 ( uniqid ( rand (), true ) );
		$data [$this->cl_eid] = $_SESSION ['curEnterprise'] ['e_id'];
		$data [$this->cl_nav_id] = $_REQUEST [$this->cl_nav_id];
		$data [$this->cl_title] = $_REQUEST [$this->cl_title];
		$data [$this->cl_html_content] = stripslashes ( &$_REQUEST [$this->cl_html_content] );
		
		$result = $db_table->add ( $data );
		if ($result) {
			echo json_encode ( array (
					'success' => true,
					'msg' => '保存成功!' 
			) );
		} else {
			echo json_encode ( array (
					'msg' => '添加出错！' 
			) );
		}
	}
	
	/**
	 * 通过easyUI下拉框选中的变动读取simpleinfo的信息。
	 */
	public function readNavInfo() {
		$nav_id = I ( 'nav_id' );
		$nav_name = I ( 'nav_name' );
		$resultdata = array ();
		
		if (! empty ( $nav_id )) {
			$resultdata [$this->cl_nav_id] = $nav_id;
			$resultdata ['nav_name'] = $nav_name;
			$resultdata [$this->cl_simple_info_id] = '';
			$resultdata [$this->cl_title] = '';
			$resultdata [$this->cl_html_content] = '';
			$resultdata ['state'] = 'add';
			
			$db_table = M ( $this->table_name );
			$navinfomap = array (
					$this->cl_eid => $_SESSION ['curEnterprise'] ['e_id'],
					$this->cl_nav_id => $nav_id,
					$this->cl_is_del => 0 
			);
			$result = $db_table->where ( $navinfomap )->find ();
			if ($result) {
				$resultdata [$this->cl_simple_info_id] = $result [$this->cl_simple_info_id];
				$resultdata [$this->cl_title] = $result [$this->cl_title];
				$resultdata [$this->cl_html_content] = $result [$this->cl_html_content];
				$resultdata ['state'] = 'edit';
			}
			$this->ajaxReturn ( $resultdata, 'json' );
		}
	}
	
	/**
	 * 编辑信息详情模块富文本编辑器使用的图片上传。
	 * 使用ueditor富文本编辑器上传信息详情图片的函数。
	 * Author：王健。
	 * CreateTime：2014/09/27 03:17:25.
	 */
	public function simpleInfoImageHandle() {
		$savePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/simpleinfo/' . date ( 'Ymd' ) . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); // 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); // 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 设置信息样式。
	 */
	public function setInfoStyle() {
		$setinfo = array (
				'setpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'template_id' => I ( 'selected' ) 
		);
		$tplManage = A ( 'Admin/TplManage' );
		$rs = $tplManage->EntrustTemplate ( $setinfo );
		if ($rs == 1) {
			$this->ajaxReturn ( array (
					'status' => 1,
					'msg' => '提交更改成功!' 
			) );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0,
					'msg' => '模板更改失败!' . $rs ['msg'] 
			) );
		}
	}
}
?>