<?php
/**
 * 测试原生js的XMLHttpRequest提交。
 * @author Administrator
 *
 */
class TestXHRLoadAction extends Action {
	/**
	 * 上传页面视图。
	 */
	public function loadPage() {
		$this->display ();
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/testupload/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$result = $commonhandle->threadSingleUpload ( $savepath );
		$this->ajaxReturn ( $result );
	}
}
?>