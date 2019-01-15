<?php
/**
 * 终端UI风格ajax处理控制器。
 * @author Shinnlove
 *
 */
class UltimateUIRequestAction extends PCRequestLoginAction {
	/**
	 * UI列表风格确认。
	 */
	public function uiListConfirm(){
		$setinfo = array (
				'setpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'template_id' => I ( 'selected' )
		);
		$tplManage = A ( 'Admin/TplManage' );
		$rs = $tplManage->EntrustTemplate ( $setinfo );
		if($rs == 1){
			$this->ajaxReturn( array('status' => 1, 'msg' => '提交更改成功!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '模板更改失败!'.$rs['msg']) );
		}
	}
	
	/**
	 * UI按钮确认。
	 */
	public function uiButtonConfirm() {
		$setinfo = array (
				'setpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'template_id' => I ( 'selected' )
		);
		$tplManage = A ( 'Admin/TplManage' );
		$rs = $tplManage->EntrustTemplate ( $setinfo );
		if($rs == 1){
			$this->ajaxReturn( array('status' => 1, 'msg' => '提交更改成功!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '模板更改失败!' . $rs['msg']) );
		}
	}
	
}
?>