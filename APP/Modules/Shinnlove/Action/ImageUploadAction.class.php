<?php
/**
 * 自写js上传img模块控制器。
 * @author shinnlove
 *
 */
class ImageUploadAction extends Action {

	public function uploadPage() {
		$this->display ();
	}

	public function uploadHandle() {
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试。"
		);

		$ajaxinfo = array (
				'imgfile' => $_REQUEST ['img-upload'],
		);

		$currentimgId = md5 ( uniqid ( rand (), true ) ); // 为本次图片生成文件名（图片编号）
		$imgReal = base64_decode ( str_replace ( 'data:image/jpeg;base64,', '', $ajaxinfo ['imgfile'] ) ); // 解码图片
		$savepath = './Updata/images/testupload/'; // 可以分文件夹存
		$savename = $currentimgId . '.jpg';
		$imgexist = substr ( $savepath, 2 ) . $savename;
		if (! file_exists ( $savepath )) mkdirs ( $savepath ); // 如果路径不存在，创建路径saveURL
		$saveresult = file_put_contents ( $savepath . $savename, $imgReal ); // 在指定的文件夹路径储存图片
		// 如果上传成功
		if (! empty ( $saveresult )) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
			$ajaxresult ['data'] = array (
					'imgId' => $currentimgId,
					'uploadpath' => $imgexist
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
}
?>