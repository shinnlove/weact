<?php
/**
 * 本控制器封装TP框架的一些组件，用以上传图片及文件。
 * @author 微动团队
 */
class CommonHandleAction extends Action {
	/**
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
			$wifipath = assemblepath ( $wifirelativepath ); // 组装图片路径
			$nowifipath = assemblepath ( $nowifirelativepath ); // 组装图片路径
			$imageid = md5 ( uniqid ( rand (), true ) ); // 如果需要，返回个图片id编号
			
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'imgId' => $imageid,
							'wifipath' => $wifipath,
							'nowifipath' => $nowifipath,
							'uploadpath' => $wifirelativepath,
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 10001,
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
	 * Original Author：王健。
	 * Perfect Package：赵臣升。
	 * Latest Modify: 赵臣升。
	 * ModifyTime：2014/09/24 16:23:25.
	 * LatestModify:2015/03/16 02:09:20.
	 * ueditor的图片上传函数ueditorUploadImage()。
	 * 由ueditor发起的，完成与thinkphp相关的，文件上传类的调用。
	 * 特别注意：保存路径建议与主文件平级目录或者平级目录的子目录来保存（这也可以是变量）。
	 *
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 */
	public function ueditorUploadImage($savePath = '', $maxSize = 1500000) {
		import ( 'ORG.Net.UploadFile' ); // 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); // 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; // 设置上传路径
		$upload->uploadReplace = true; // 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'jpg', 'gif', 'png', 'jpeg' ); // 文件过滤器准许上传的文件类型
		$upload->maxSize = $maxSize; // 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（图片最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->autoSub = false; // 是否开启子目录保存
		$upload->subType = 'date'; // 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; // 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		if ($upload->upload ()) {
			$fileinfo = $upload->getUploadFileInfo ();
			echo json_encode ( array (
					'originalName' => $fileinfo [0] ['name'],
					'name' => $fileinfo [0] ['savename'],
					'url' => substr ( $savePath, 1 ) . $fileinfo [0] ['savename'], // 返回路径去掉.
					'title' => htmlspecialchars ( $_POST ['container'], ENT_QUOTES ),
					'state' => 'SUCCESS' 
			) );
		} else {
			echo json_encode ( array (
					'state' => $upload->getErrorMsg () 
			) );
		}
	}
	
	/**
	 * 微动js线程托管提交证书的处理函数。
	 * 特别注意：1、不能用于托管处理多个证书上传；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/03/15 20:33:25.
	 * 
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @return array $saveinfo 保存文件的信息
	 */
	public function threadSingleUploadCert($savePath = '', $maxSize = 15000) {
		import ( 'ORG.Net.UploadFile' ); // 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); // 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; 					// 设置上传路径
		$upload->saveRule = uniqid; 					// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 					// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'p12', 'pem' ); 	// 文件过滤器准许上传的文件类型
		$upload->maxSize = $maxSize; 					// 证书大小控制在10KB以内。系统默认为-1，不限制上传大小
		$upload->autoSub = false; 						// 是否开启子目录保存
		$upload->subType = 'date'; 						// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; 					// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		
		$delegateresult = array (); // 托管上传证书结果
		if ($upload->upload ()) {
			// 调用upload会上传表单中所有的图片，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			$uploadresult = $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
			
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$certrelativepath = $filepath . $uploadresult [0] ['savename']; // 证书相对路径
			$absolutepath = assemblepath ( $certrelativepath, true ); // 组装证书绝对路径
			
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'cert_path' => $absolutepath
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 10001,
					'errMsg' => $upload->getErrorMsg (), //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 微动js线程托管提交csv表格的处理函数。
	 * 特别注意：1、不能用于托管处理多个csv文件上传；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/03/15 20:33:25.
	 * 
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @return array $saveinfo 保存文件的信息
	 */
	public function threadSingleUploadCSV($savePath = '', $maxSize = 1500000) {
		import ( 'ORG.Net.UploadFile' ); // 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); // 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; 					// 设置上传路径
		$upload->saveRule = uniqid; 					// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 					// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'csv', 'xls' ); 	// 文件过滤器准许上传的文件类型
		$upload->allowTypes = array ( 'application/vnd.ms-excel', 'application/vnd.ms-excel' ); // 检测mime类型
		$upload->maxSize = $maxSize; 					// 证书大小控制在10KB以内。系统默认为-1，不限制上传大小
		$upload->autoSub = false; 						// 是否开启子目录保存
		$upload->subType = 'date'; 						// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; 					// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
	
		$delegateresult = array (); // 托管上传证书结果
		if ($upload->upload ()) {
			// 调用upload会上传表单中所有的图片，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			$uploadresult = $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
				
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$csvrelativepath = $filepath . $uploadresult [0] ['savename']; // csv文件相对路径
			$filerelativepath = assemblepath ( $csvrelativepath ); // 带weact的csv相对文件路径
			$absolutepath = assemblepath ( $csvrelativepath, true ); // 组装csv文件绝对路径
				
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'filepath' => $absolutepath, // 文件网络路径
							'relativepath' => $filerelativepath, // 文件相对路径
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 10001,
					'errMsg' => $upload->getErrorMsg (), //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 微动js线程托管提交voice流媒体文件的处理函数。
	 * 特别注意：1、不能用于托管处理多个voice流媒体文件上传；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/08/08 21:04:25.
	 * 
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @return array $saveinfo 保存文件的信息
	 */
	public function threadSingleUploadVoice($savePath = '', $maxSize = 2100000) {
		import ( 'ORG.Net.UploadFile' ); 			// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); 				// 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; 				// 设置上传路径
		$upload->saveRule = uniqid; 				// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 				// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'mp3', 'wma', 'wav', 'amr' ); // 文件过滤器准许上传的文件类型
		//$upload->allowTypes = array ( 'audio/mpeg', 'audio/x-ms-wma', 'audio/x-wav', 'application/octet-stream' ); // 检测mime类型，webuploader上传不了wav和mp3的文件流媒体，在此暂不做限制
		$upload->maxSize = $maxSize; 				// 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（语音最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->autoSub = false; 					// 是否开启子目录保存
		$upload->subType = 'date'; 					// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; 				// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		
		$delegateresult = array (); 				// 托管上传声音流媒体文件结果
		if ($upload->upload ()) {
			// 调用upload会上传流媒体文件，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			$uploadresult = $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
			
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$voicerelativepath = $filepath . $uploadresult [0] ['savename']; 	// voice文件相对路径
			$filerelativepath = assemblepath ( $voicerelativepath ); 			// 带weact的voice相对文件路径
			$absolutepath = assemblepath ( $voicerelativepath, true ); 			// 组装voice文件绝对路径
		
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'filepath' => $absolutepath, // 文件网络路径
							'relativepath' => $filerelativepath, // 文件相对路径
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 10001,
					'errMsg' => $upload->getErrorMsg (), //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 微动js线程托管提交video视频文件的处理函数。
	 * 特别注意：1、不能用于托管处理多个video视频文件上传；2、$savePath一定要用./开头，因为本函数包含提取.的操作！
	 * Author: 赵臣升。
	 * CreateTime：2015/08/08 21:36:25.
	 *
	 * @param string $savePath 要上传的文件路径
	 * @param number $maxSize 默认最大上传文件的大小（以字节B为单位，1024代表1K）
	 * @return array $saveinfo 保存文件的信息
	 */
	public function threadSingleUploadVideo($savePath = '', $maxSize = 10500000) {
		import ( 'ORG.Net.UploadFile' ); 			// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile (); 				// 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath; 				// 设置上传路径
		$upload->saveRule = uniqid; 				// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 				// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'mp4' ); 		// 文件过滤器准许上传的文件类型
		$upload->allowTypes = array ( 'video/mp4' ); // 检测mime类型
		$upload->maxSize = $maxSize; 				// 文件大小控制在10MB以内。系统默认为-1，不限制上传大小（语音最好还限制下大小、此处可以作为变量）。如果不设定，默认上传10MB以内。
		$upload->autoSub = false; 					// 是否开启子目录保存
		$upload->subType = 'date'; 					// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd'; 				// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if (! file_exists ( $upload->savePath )) mkdirs ( $upload->savePath ); // 如果不存在目录，则自动创建目录文件夹
		
		$delegateresult = array (); 				// 托管上传视频文件结果
		if ($upload->upload ()) {
			// 调用upload会上传视频文件，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			$uploadresult = $upload->getUploadFileInfo (); // 如果上传成功，则直接取得保存后的文件信息
			
			$filepath = substr ( $uploadresult [0] ['savepath'], 1, strlen ( $uploadresult [0] ['savepath'] ) - 1 ); // 去掉.的文件夹
			$voicerelativepath = $filepath . $uploadresult [0] ['savename']; 	// video文件相对路径
			$filerelativepath = assemblepath ( $voicerelativepath ); 			// 带weact的video相对文件路径
			$absolutepath = assemblepath ( $voicerelativepath, true ); 			// 组装video文件绝对路径
	
			// 注意返回格式
			$delegateresult = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'filepath' => $absolutepath, // 文件网络路径
							'relativepath' => $filerelativepath, // 文件相对路径
					)
			);
		} else {
			$delegateresult = array (
					'errCode' => 10001,
					'errMsg' => $upload->getErrorMsg (), //如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
					'data' => array ()
			);
		}
		return $delegateresult; // 返回托管上传信息
	}
	
	/**
	 * 生成二维码的demo例子。
	 */
	public function qrCodeDemo () {
		$e_id = "201406261550250006"; // 准备商家编号
		$url = "http://www.we-act.cn/Home/ProductView/productShow/e_id/201406261550250006/nav_id/wehome0001/product_id/whp00006.shtml"; // 要写入的二维码地址
		$usetype = "product"; // 二维码用途，可指定product,customer,guide,subbranch,nativepay等多种
		$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/productcode/wehome0001/whp00006/"; // 保存路径按导航、商品编号来存放，必须以./相对路径开头
		$saveqrname = ""; // 是否指定二维码文件名，默认空就用md5生成文件名
		$logopathname = "./Updata/01.jpg"; // 准备好的要嵌入作为logo的图片（相对路径+文件名与后缀）
	
		import ( 'Class.Common.phpqrcode.weactqrcode', APP_PATH, '.php' ); // 载入WeAct的二维码类
		$wqr = new WeActQRCode (); // 生成微动二维码类对象
		$createresult = $wqr->createQRCode ( $e_id, $url, $usetype, $saveqrpath, $saveqrname, $logopathname ); // 调用二维码函数生成二维码并返回生成结果
	
		if ($createresult ['errCode'] == 0) {
			$qrcode = assemblepath ( $createresult ['data'] ['qrcode'] );
			$logoqrcode = assemblepath ( $createresult ['data'] ['logoqrcode'] );
			echo '<img src="' . $qrcode . '" alt="" /><br /><img src="' . $logoqrcode . '" alt="" />';
		} else {
			echo '生成二维码失败!';
		}
	}
	
}
?>