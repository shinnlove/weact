<?php
/**
 * 生成下载压缩包控制器。
 * @author 万路康。
 * CreateTime:2015/05/22 16:31:36.
 */
class DownloadRarZipAction extends Action {
	/**
	 * 压缩文件的demo，
	 * 要压缩的文件码信息必须是在调用create_download_zip之前就准备好
	 */
	public function zipDemo() {
		$direct2browser = I ( 'direct2browser', 0 );
		$fileinfo = array ( // 测试数组
				0 => array (
						'filepath' => "./compose/", // 前两个参数是压缩包的信息，后一个参数，是压缩包内的信息
						'filename' => "test",
						'filedata' => array (
								0 => array (
										'innerfoldername' => 'file1/',
										'innerfile' => array (
												0 => "testPic/1.jpg" 
										) 
								),
								1 => array (
										'innerfoldername' => 'file2/',
										'innerfile' => array (
												0 => "testPic2/2.jpg" 
										) 
								) 
						) 
				) 
		);
		$createresult = $this->create_download_zip ( $fileinfo, $direct2browser );
		$this->ajaxReturn ( $createresult );
	}
	
	/**
	 * 
	 * @param array $fileinfo 为压缩文件信息
	 * @param bool $direct2browser 是否直接输出到浏览器
	 * @param string $overwrite 是否重写，默认为true
	 * @return array $ajaxresult
	 * $fileinfo为压缩信息，其形式如下
	 * $fileinfo = array(
	 * 		0=>array(
	 * 				'filepath'=>"./xxx/xxx/" //文件夹路径，若路径不存在，则创建
	 * 				'filename'=>"xxx.zip" //压缩包名字，若空，则采用md5自动生成
	 * 				'filedata'=>array(
 	 * 						0=>array(
	 * 							'innerfoldername'=>"./xxx/"	//若不为空，在压缩包中创建目录，若为空，则直接存放在压缩包的根目录下
	 * 							'innerfile'=>array(
	 * 									0=>"xxx/xxx.jpg"	//图片存放的路径，谨记:这里的路径前面没有'./' !!!!!!!
	 * 									1=>"xxx/xxx.jpg"
	 * 							)
	 * 						);
	 * 						1=>array(
	 *
	 * 						);
	 * 						...
	 *		 		)
	 * 			);
	 * 		1=>array(
	 * 		);
	 * 		...
	 */
	public function create_download_zip($fileinfo, $direct2browser = false, $overwrite = true) {
		// 准备调用返回参数
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试" 
		);
		
		$filesuffix = ".zip"; 				// 要生成压缩文件的后缀
		$files = array (); 					// 存放所有待解压的图片路径数组
		$filenames = array (); 				// 存放所有待解压的图片的名字数组
		$innerfoldernames = array (); 		// 压缩包内文件夹名称
		$zipcount = count ( $fileinfo );	// 计算要生成压缩包数量
		
		//如果压缩包的文件名为空，则取时间戳的md5码命名
		for($i = 0; $i < $zipcount; $i ++) {
			if (empty ($fileinfo [$i] ['filename'])) {
				$fileinfo [$i] ['filename'] = md5 ( uniqid ( rand () ,true ) ) . $filesuffix;	// 默认后缀名为.zip
			} else {
				$fileinfo [$i] ['filename'] .= $filesuffix; // 处理默认后缀.zip
			}
			for($j=0; $j<count($fileinfo [$i] ['filedata']);$j++){
				$fileinfo [$i] ['filedata'] [$j] ['innerfoldername'] = './' . $fileinfo [$i] ['filedata'] [$j] ['innerfoldername'] . '/';
			}
		}
		
		if ($direct2browser) { 
			// 判断是否开启下载，如果开启的话就只下载$fileinfo[0]的信息，如果未开启，则压缩$fileinfo数组下面所有的信息
			if (! is_dir ( $fileinfo [0] ['filepath'] )) {
				mkdirs ( $fileinfo [0] ['filepath'] ); // 若文件路径不存在则创建
			}
			$destination = $fileinfo [0] ['filepath'] . $fileinfo [0] ['filename']; // 压缩文件存放目录
			$tempfilecount = count ( $fileinfo [0] ['filedata'] );
			for($i = 0; $i < $tempfilecount; $i ++) {
				for($j = 0; $j < count ( $fileinfo [0] ['filedata'] [$i] ['innerfile'] ); $j ++) {
					array_push ( $files, $fileinfo [0] ['filedata'] [$i] ['innerfile'] [$j] );
					$patharray = split ( '/', $fileinfo [0] ['filedata'] [$i] ['innerfile'] [$j] );
					$filename = end ( $patharray ); // 取数组的最后一个元素即为文件名
					array_push ( $filenames, $filename );
					array_push ( $innerfoldernames, $fileinfo [0] ['filedata'] [$i] ['innerfoldername'] );
				}
			}
		} else { 
			// 只是解压文件，不下载
			for($k = 0; $k < $zipcount; $k ++) { 
				// 遍历每个压缩信息
				if (! is_dir ( $fileinfo [$k] ['filepath'] )) {
					mkdirs ( $fileinfo [$k] ['filepath'] ); // 若文件路径不存在则创建
				}
				$destination = $fileinfo [$k] ['filepath'] . $fileinfo [$k] ['filename']; // 压缩文件存放目录
				for($i = 0; $i < count ( $fileinfo [$k] ['filedata'] ); $i ++) {
					for($j = 0; $j < count ( $fileinfo [$k] ['filedata'] [$i] ['innerfile'] ); $j ++) {
						array_push ( $files, $fileinfo [$k] ['filedata'] [$i] ['innerfile'] [$j] );
						$patharray = split ( '/', $fileinfo [$k] ['filedata'] [$i] ['innerfile'] [$j] );
						$filename = end ( $patharray ); // 取数组的最后一个元素即为文件名
						array_push ( $filenames, $filename );
						array_push ( $innerfoldernames, $fileinfo [$k] ['filedata'] [$i] ['innerfoldername'] );
					}
				}
			}
		}
		// 检测参数
		$valid_files = array ();
		if (is_array ( $files )) {
			foreach ( $files as $file ) {
				if (file_exists ( $file )) {
					$valid_files [] = $file;
				}
			}
		}
		if (count ( $valid_files )) {
			// create the archive php 系统自带类，具体使用方法参照：http://cn2.php.net/manual/en/class.ziparchive.php
			$zip = new ZipArchive ();
			if ($zip->open ( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true) {
				return false;
			}
			
			for($i = 0; $i < count ( $valid_files ); $i ++) {
				$zip->addFile ( $valid_files [$i], $innerfoldernames [$i] . $filenames [$i] );
			}
			// 到这里压缩文件生成成功
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
			if ($direct2browser) {
				$this->downloadZip ( $fileinfo [0] ['filepath'], $fileinfo [0] ['foldername'] );
			}
		}
		$ajaxresult ['data'] ['zipfilepath'] = assemblepath ( __ROOT__ . substr ( $destination, 1, strlen ( $destination ) - 1 ) );
		return $ajaxresult;
	}
	
	/**
	 * 如果需要将压缩文件输出到浏览器，则调用这个函数。
	 * @param unknown $file_dir
	 * @param unknown $file_name
	 */
	public function downloadZip($file_dir, $file_name) {
		if (! file_exists ( $file_dir . $file_name )) {
			exit ( "No file data." );
		} else {
			// 打开文件
			$file = fopen ( $file_dir . $file_name, "r" );
			// 输入文件标签
			header ( "Content-type: application/octet-stream" );
			header ( "Accept-Ranges: bytes" );
			header ( "Accept-Length: " . filesize ( $file_dir . $file_name ) );
			header ( "Content-Disposition: attachment; filename=" . $file_name );
			// 输出文件内容
			// 读取文件内容并直接输出到浏览器
			fclose ( $file );
			exit ( fread ( $file, filesize ( $file_dir . $file_name ) ) );
		}
	}
	
}
?>