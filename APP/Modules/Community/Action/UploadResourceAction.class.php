<?php
/**
 * 本控制器用来处理接收用户上传图片等资源。
 * @author 赵臣升。
 * CreateTime:2014/12/26 16:34:25.
 */
class UploadResourceAction extends Action {
	/**
	 * 上传回复图片的接收函数。
	 */
	public function uploadReplyImage() {
		$ajaxinfo = array (
				'site_id' => $_REQUEST ['sId'],
				'e_id' => $_REQUEST ['e_id'],
				'id' => $_REQUEST ['id'],
				'pic' => $_REQUEST ['pic'] 
		);
		
		$currentimgId = md5 ( uniqid ( rand (), true ) ); // 为本次图片生成文件名（图片编号）
		$imgReal = base64_decode ( str_replace ( 'data:image/jpeg;base64,', '', $ajaxinfo ['pic'] ) ); // 解码图片
		$saveURL = 'Updata/images/' . $ajaxinfo ['e_id'] . '/community/postinfo/' . date ( 'Ymd' ) . '/'; // 指定保存路径saveURL
		if (! file_exists ( $saveURL )) mkdirs ( $saveURL ); // 如果路径不存在，创建路径saveURL
		$saveresult = file_put_contents ( $saveURL . $currentimgId . '.jpg', $imgReal ); // 在指定的文件夹路径储存图片
		// 如果上传成功
		if (! empty ( $saveresult )) {
			// 注意返回格式
			$result = array (
					'errCode' => 0,
					'message' => '发表成功',
					'data' => array (
							'id' => $ajaxinfo ['id'],
							'picId' => $currentimgId 
					),
					'showLogin' => null,
					'jumpURL' => null,
					'locationTime' => 2000 
			);
			echo json_encode ( $result );
		}
	}
}
?>