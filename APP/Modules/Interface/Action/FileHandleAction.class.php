<?php
/**
 * 微动文件处理接口控制器。
 * @author 赵臣升。
 */
class FileHandleAction extends Action {
	/**
	 * 导购头像上传使用的函数。
	 * js线程托管提交单图片的处理函数（较新），并且压缩成前台需要格式，后台只要直接ajaxReturn即可返回处理结果。
	 * 特别注意：1、不能用于托管处理多图片；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/03/15 20:33:25.
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @param boolean $imageThumb 是否要生成缩略图
	 * @return array $delegateresult js线程托管上传图片结果
	 */
	public function threadSingleUpload($savePath = '', $maxSize = 1500000, $imageThumb = true) {
		$uploadresult = $this->uploadImage ( $savePath, $maxSize, $imageThumb ); // 处理上传及文件夹路径
		
		$delegateresult = array (); // 返回信息
		if (is_array ( $uploadresult )) {
			// 如果上传成功
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$wifirelativepath = $filepath . $uploadresult [0] ['savename']; // wifi高质量图片
			$nowifirelativepath = $filepath . "thumb_" . $uploadresult [0] ['savename']; // nowifi缩略图
			$wifipath = assemblepath ( $wifirelativepath, true ); // 组装图片路径（绝对路径）
			$nowifipath = assemblepath ( $nowifirelativepath, true ); // 组装图片路径（绝对路径）
			$imageid = md5 ( uniqid ( rand (), true ) ); // 如果需要，返回个图片id编号
				
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'appimageid' => $imageid, // 微动服务器msgimage表中的主键
							'wifipath' => $wifipath, // 图片上传到微动服务器的WIFI下的原图片
							'nowifipath' => $nowifipath 
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 41001,
					'errMsg' => $uploadresult,
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 微动聊天图片接收和上传到微信服务器的函数。
	 * 线程托管提交单图片的处理函数（较新），并且压缩成前台需要格式，后台只要直接ajaxReturn即可返回处理结果。
	 * 特别注意：1、不能用于托管处理多图片；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/03/15 20:33:25.
	 * @param string $e_id 这张图片所属的商家编号
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K），微信默认最大接收2MB图片
	 * @param boolean $imageThumb 是否要生成缩略图
	 * @return array $delegateresult js线程托管上传图片结果
	 */
	public function wechatSingleUpload($e_id = '', $savePath = '', $maxSize = 2600000, $imageThumb = true) {
		$uploadresult = $this->uploadImage ( $savePath, $maxSize, $imageThumb ); // 处理上传及文件夹路径
		
		$delegateresult = array (); // 返回信息
		if (is_array ( $uploadresult )) {
			// 如果上传成功，会得到文件信息数组
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$wifirelativepath = $filepath . $uploadresult [0] ['savename']; // wifi高质量图片
			$nowifirelativepath = $filepath . "thumb_" . $uploadresult [0] ['savename']; // nowifi缩略图
			$wifipath = assemblepath ( $wifirelativepath, true ); // 组装图片路径（绝对路径）
			$nowifipath = assemblepath ( $nowifirelativepath, true ); // 组装图片路径（绝对路径）
			$picturefilepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . $wifirelativepath; // 图片绝对路径
			
			// 一张在微动服务器上的图片地址
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
			// 得到企业信息
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 找到当前企业信息
			$uploadmediaid = $swc->uploadMedia ( $einfo, $picturefilepath, 'image' ); // 调用微信服务层的上传多媒体接口上传一张图片
			if (! empty ( $uploadmediaid )) {
				// 上传到微信服务器都成功了
				$imageid = md5 ( uniqid ( rand (), true ) ); // 如果需要，返回个图片id编号
				// 向msgimage中插入一条记录
				$msgimginfo = array (
						'msgimage_id' => $imageid,
						'e_id' => $e_id,
						'local_path' => $wifirelativepath,
						'add_time' => time (),
						'msg_use' => 5, // 首次关注1、定时推送2、被动响应3、准备群发4,未设置用途是0,5代表导购对顾客发送的聊天图片（暂定，可根据msgimage中该字段的用途修改）
						'media_id' => $uploadmediaid
				);
				$addinfo = M ( 'msgimage' )->add ( $msgimginfo ); // 向微动的微信图片表中插入该条图片记录
					
				// 注意返回格式
				$delegateresult = array (
						'errCode' => 0,
						'errMsg' => 'ok',
						'data' => array (
								'appimageid' => $imageid, // 微动服务器msgimage表中的主键
								'mediaid' => $uploadmediaid, // 微信服务器端的图片MediaId
								'mediapath' => $wifipath, // 微动服务器的图片路径
								'wifipath' => $wifipath, // 图片上传到微动服务器的WIFI下的原图片
								'nowifipath' => $nowifipath // 图片上传到微动服务器的2G/3G/4G下的缩略图片，建议三方显示在导购用户手机上
						)
				);
			} else {
				// 上传到微信服务器失败了
				$delegateresult = array (
						'errCode' => 41001,
						'errMsg' => "微信服务器忙，请稍后再尝试发送图片消息！",
						'data' => array ()
				);
			}
		} else {
			$delegateresult = array (
					'errCode' => 41001,
					'errMsg' => $uploadresult,
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 微动聊天语音接收和上传到微信服务器的函数。
	 * 线程托管提交单图片的处理函数（较新），并且压缩成前台需要格式，后台只要直接ajaxReturn即可返回处理结果。
	 * 特别注意：1、不能用于托管处理多图片；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/03/15 20:33:25.
	 * @param string $e_id 这张图片所属的商家编号
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K），微信默认最大接收5MB语音
	 * @return array $delegateresult js线程托管上传图片结果
	 */
	public function wechatVoiceUpload($e_id = '', $savePath = '', $maxSize = 5300000) {
		$uploadresult = $this->uploadVoice ( $savePath, $maxSize ); // 处理语音上传及文件夹路径
		
		$delegateresult = array (); // 返回信息
		if (is_array ( $uploadresult )) {
			// 如果上传成功，会得到文件信息数组
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$voicerelativepath = $filepath . $uploadresult [0] ['savename']; // 语音文件的去点相对路径
			$voiceabsolutepath = assemblepath ( $voicerelativepath, true ); // 组装语音的路径（绝对路径）
			$voicefilepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . $voicerelativepath; // 语音文件的绝对路径
			
			// 将语音上传到微信服务器
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
			// 得到企业信息
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find (); // 找到当前企业信息
			$uploadmediaid = $swc->uploadMedia ( $einfo, $voicefilepath, 'voice' ); // 调用微信服务层的上传多媒体接口上传一条语音
			if (! empty ( $uploadmediaid )) {
				// 上传到微信服务器都成功了
				$voiceid = md5 ( uniqid ( rand (), true ) ); // 如果需要，返回个语音id编号
				// 向msgimage中插入一条记录
				$msgvoiceinfo = array (
						'msgvoice_id' => $voiceid,
						'e_id' => $e_id,
						'local_path' => $voicerelativepath,
						'add_time' => time (),
						'msg_use' => 5, // 首次关注1、定时推送2、被动响应3、准备群发4,未设置用途是0,5代表导购对顾客发送的聊天语音（暂定，可根据msgvoice中该字段的用途修改）
						'media_id' => $uploadmediaid
				);
				$addinfo = M ( 'msgvoice' )->add ( $msgvoiceinfo ); // 向微动的微信语音表中插入该条语音记录
					
				// 注意返回格式
				$delegateresult = array (
						'errCode' => 0,
						'errMsg' => 'ok',
						'data' => array (
								'appvoiceid' => $voiceid, // 微动服务器msgvoice表中的主键
								'mediaid' => $uploadmediaid, // 微信服务器端的语音MediaId
								'mediapath' => $voiceabsolutepath, // 语音上传到微动服务器的绝对路径
						)
				);
			} else {
				// 上传到微信服务器失败了
				$delegateresult = array (
						'errCode' => 41001,
						'errMsg' => "微信服务器忙，请稍后再尝试发送语音消息！",
						'data' => array ()
				);
			}
		} else {
			$delegateresult = array (
					'errCode' => 41001,
					'errMsg' => $uploadresult,
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * Original Author：王健。
	 * Perfect Package：赵臣升。
	 * Latest Modify: 赵臣升。
	 * ModifyTime：2014/09/05 20:33:25.
	 * LatestModify:2015/03/16 02:09:20.
	 * 图片上传函数uploadImage()。
	 * 完成与thinkphp相关的，文件上传类的调用。
	 * 特别注意：保存路径建议与主文件平级目录或者平级目录的子目录来保存（这也可以是变量）。
	 *
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @param boolean $imageThumb 是否开启缩略图模式
	 * @return array $fileinfo 返回上传完的文件信息（二维数组）
	 */
	public function uploadImage($savePath = '', $maxSize = 1500000, $imageThumb = true) {
		import ( 'ORG.Net.UploadFile' ); // 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); // 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; // 设置上传路径
		$upload->saveRule = uniqid; // 上传文件的文件名保存规则
		$upload->uploadReplace = true; // 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'jpg', 'jpeg', 'png', 'gif' ); // 文件过滤器准许上传的文件类型
		$upload->allowTypes = array ( 'image/png', 'image/jpg', 'image/jpeg', 'image/gif' ); // 检测mime类型
		$upload->maxSize = $maxSize; // 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（图片最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->thumb = $imageThumb; // 是否开启图片文件缩略图（默认为开启缩略图状态）
		$upload->thumbMaxWidth = '100,200,300,400,500';
		$upload->thumbMaxHeight = '100,200,300,400,500'; // 缩略图在30KB左右
		$upload->thumbType = 0; // 1按原图比例生成缩略图，0按照指定规格裁剪
		$upload->autoSub = false; // 是否开启子目录保存
		$upload->subType = 'date'; // 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; // 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		if ($upload->upload ()) {
			// 调用upload会上传表单中所有的图片，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			return $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
		} else {
			return $upload->getErrorMsg (); //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
		}
	}
	
	/**
	 * @author：赵臣升。
	 * CreateTime：2014/09/05 20:33:25.
	 * 声音上传函数uploadVoice()。
	 * 完成与thinkphp相关的，文件上传类的调用。
	 * 特别注意：保存路径建议与主文件平级目录或者平级目录的子目录来保存（这也可以是变量）。
	 *
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @return array $fileinfo 返回上传完的文件信息（二维数组）
	 */
	public function uploadVoice($savePath = '', $maxSize = 5600000) {
		import ( 'ORG.Net.UploadFile' ); // 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); // 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; // 设置上传路径
		$upload->saveRule = uniqid; // 上传文件的文件名保存规则
		$upload->uploadReplace = true; // 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'mp3', 'wma', 'wav', 'amr' ); // 文件过滤器准许上传的文件类型
		$upload->allowTypes = array ( 'application/octet-stream', 'application/octet-stream', 'application/octet-stream', 'application/octet-stream' ); // 检测mime类型
		$upload->maxSize = $maxSize; // 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（语音最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->autoSub = false; // 是否开启子目录保存
		$upload->subType = 'date'; // 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; // 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		if ($upload->upload ()) {
			// 调用upload会上传表单中所有的图片，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			return $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
		} else {
			return $upload->getErrorMsg (); //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
		}
	}
}
?>