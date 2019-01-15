<?php
class ImportExcelDataAction extends Action {
	/**
	 * 导入excel视图。
	 */
	public function couponUse () {
		$this->display();
	}
	
	/**
	 * 导入excel表单提交处理。
	 * 先提交Excel，提交成功返回路径后再读取Excel。
	 */
	public function couponUserHandle () {
		$savePath = './Upload/';
		$uploadresult = $this->uploadExcel( $savePath );
		// p($uploadresult);die;
		
		// 加载第三方类库PHPExcel
		vendor ( 'PHPExcel.PHPExcel' );
		vendor ( 'PHPExcel.PHPExcel.IOFactory' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel5' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel2007' );
		
		// 加载excel文件
		$inputfilename = $uploadresult [0] ['savepath'] . $uploadresult [0] ['savename'];
		$extension = $uploadresult [0] ['extension'];
		
		switch ($extension) {
			case 'xls' :
				$objReader = new PHPExcel_Reader_Excel5 ();
				break;
			case 'xlsx' :
				$objReader = new PHPExcel_Reader_Excel2007 ();
				break;
			case 'csv' :
				$objReader = new PHPExcel_Reader_CSV ();
				break;
			default :
				$this->error ( '上传的文件类型不匹配，无法识别！' );
				break;
		}
		
		$objPHPExcel = $objReader->load ( $inputfilename );		// 读取Excel
		$currentsheet = $objPHPExcel->getActiveSheet (); 		// 获取活动工作薄
		$allcolumn = $currentsheet->getHighestColumn ();		// 获取最大列数
		$allrow = $currentsheet->getHighestRow ();				// 获取最大行数
		$allcolumnindex = PHPExcel_Cell::columnIndexFromString ( $allcolumn ); // 将列数的字母索引转换成数字（重要）
		
		// 开始读取数据
		$global = array();
		// 读取excel文件中的数据
		for($row = 2; $row <= $allrow; $row ++) {
			$singlerecord = array ();
			for($column = 0; $column < $allcolumnindex; $column ++) {
				$singlerecord [$column] = $currentsheet->getCellByColumnAndRow ( $column, $row )->getValue ();
			}
			$global [$row - 2] = $singlerecord;
		}
		
		// 格式化数据
		$couponuselist = array();
		for($i = 0; $i < count( $global ); $i ++) {
			$couponuselist [$i] ['coupon_sncode'] = $global [$i] [0];				// 券sn_code编号
			
			if(trim ( $global [$i] [2] ) == '已使用') {
				$couponuselist [$i] ['is_used'] = 1;
			} else if (trim ( $global [$i] [2] ) == '未使用'){
				$couponuselist [$i] ['is_used'] = 0;
			}
			
			if(trim ( $global [$i] [5] ) != '') {
				$singletime = trim ( $global [$i] [5] );
				$time = strtotime( $singletime );
				$couponuselist [$i] ['used_time'] = $time;							// 使用日期（可能有）
			}
			
			if(trim ( $global [$i] [6] ) != '') {
				$couponuselist [$i] ['used_remark'] = trim ( $global [$i] [6] );	// 顾客姓名（可能有）
			}
			
			if(trim ( $global [$i] [7] ) != '') {
				$couponuselist [$i] ['remark'] = trim ( $global [$i] [7] );			// VIP卡号（可能有）
			}
			
			if(trim ( $global [$i] [8] ) != '') {
				$couponuselist [$i] ['used_subbranch'] = '11d304db8a56fd997a43b3b627b69e5c';	// 杭州三角村店
				//$couponuselist [$i] ['used_subbranch'] = trim ( $global [$i] [8] );	// 消费门店（必须有）
			}
		}
		p($couponuselist);die;
	}
	
	
	/**
	 * CreateTime：2014/09/05 20:33:25.
	 * 完成与thinkphp相关的，文件上传类的调用。
	 * 特别注意：保存路径建议与主文件平级目录或者平级目录的子目录来保存（这也可以是变量）。
	 *
	 * @param string $savePath 要上传的excel的文件路径
	 * @param string $maxSize 默认最大上传文件的大小（一次提交的Excel不超过30000条记录，如果超过，分几次提交）
	 * @return array $fileinfo 返回上传完的文件信息（二维数组）
	 */
	public function uploadExcel($savePath = NULL, $maxSize = '15000000') {
		import ( 'ORG.Net.UploadFile' ); 				// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile ();					// 新建一个上传类
		/* 初始化上传类的一些设置 */
		$upload->savePath = $savePath;					// 设置上传路径
		$upload->saveRule = uniqid; 					// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 					// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'xls', 'xlsx' ); 	// 文件过滤器准许上传的文件类型
		$upload->allowTypes = array ( 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ); 	// 检测mime类型
		$upload->maxSize = $maxSize; 					// 文件大小控制在100MB以内。系统默认为-1，不限制上传大小（图片最好还限制下大小、此处可以作为变量）。如果不设定，默认上传100MB以内。
		$upload->autoSub = false;						// 是否开启子目录保存
		$upload->subType = 'date';						// 如果开启子目录保存，则使用日期方式命名
		$upload->dateFormat = 'ymd';					// 如果开启子目录保存，使用的日期方式命名的格式为：年月日
		/* 进行文件的上传 */
		if( !file_exists($upload->savePath) ) mkdirs($upload->savePath);						//如果不存在目录，则自动创建目录文件夹
		if ($upload->upload ()) {
			//调用upload会上传表单中所有的图片，并且返回的$info信息是一个二维数组（0、1），里边包含上传的文件信息
			return $upload->getUploadFileInfo ();		//如果上传成功，则直接取得信息
		} else {
			$this->error ( $upload->getErrorMsg () ); 	//如果上传失败，使用专门用来获取上传的错误信息的getErrorMsg()函数捕捉错误信息
		}
	}
	
	public function upload () {
		/* $savePath = './Upload/';
		$uploadresult = $this->uploadExcel( $savePath ); */
		// p($uploadresult);die;
	
		// 加载第三方类库PHPExcel
		vendor ( 'PHPExcel.PHPExcel' );
		vendor ( 'PHPExcel.PHPExcel.IOFactory' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel5' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel2007' );
	
		// 加载excel文件
		//$inputfilename = $uploadresult [0] ['savepath'] . $uploadresult [0] ['savename'];
		//$extension = $uploadresult [0] ['extension'];
		$inputfilename = 'D:\srq.xlsx';
		$extension = 'xlsx';
		
		switch ($extension) {
			case 'xls' :
				$objReader = new PHPExcel_Reader_Excel5 ();
				break;
			case 'xlsx' :
				$objReader = new PHPExcel_Reader_Excel2007 ();
				break;
			case 'csv' :
				$objReader = new PHPExcel_Reader_CSV ();
				break;
			default :
				$this->error ( '上传的文件类型不匹配，无法识别！' );
				break;
		}
	
		$objPHPExcel = $objReader->load ( $inputfilename );		// 读取Excel
		$currentsheet = $objPHPExcel->getActiveSheet (); 		// 获取活动工作薄
		$allcolumn = $currentsheet->getHighestColumn ();		// 获取最大列数
		$allrow = $currentsheet->getHighestRow ();				// 获取最大行数
		$allcolumnindex = PHPExcel_Cell::columnIndexFromString ( $allcolumn ); // 将列数的字母索引转换成数字（重要）
	
		// 开始读取数据
		$global = array();
		// 读取excel文件中的数据
		for($row = 2; $row <= $allrow; $row ++) {
			$singlerecord = array ();
			for($column = 0; $column < $allcolumnindex; $column ++) {
				$singlerecord [$column] = $currentsheet->getCellByColumnAndRow ( $column, $row )->getValue ();
			}
			$global [$row - 2] = $singlerecord;
		}
		//p($global);die;
		$memberlist = array();
		$reflect = array(
				0 => 'original_id',
				1 => 'e_id',
				2 => 'member_card',
				3 => 'customer_name',
				4 => 'sex',
				5 => 'contact_number',
				6 => 'email',
				7 => 'id_card',
				8 => 'birthday',
				9 => 'customer_address',
				10 => 'member_level',
				11 => 'internal_staff',
				12 => 'card_start',
				13 => 'card_end',
				14 => 'subbranch_id',
				15 => 'subbranch_original_id'
		);
		for($i = 0; $i < count( $global ); $i ++){
			$memberlist [$i] ['offline_id'] = md5(uniqid(rand(),true));
			$memberlist [$i] ['add_time'] = time();
			for($j = 0; $j < 16; $j ++){
				$memberlist [$i] [$reflect [$j]] = $global [$i] [$j];
			}
		}
		//p($memberlist);die;
		$result = M ('offlinecustomer')->addAll($memberlist);
		p('completed');die;
	}
	
	public function resetPassword() {
		// 加载第三方类库PHPExcel
		vendor ( 'PHPExcel.PHPExcel' );
		vendor ( 'PHPExcel.PHPExcel.IOFactory' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel5' );
		vendor ( 'PHPExcel.PHPExcel.Reader.Excel2007' );
		
		// 加载excel文件
		//$inputfilename = $uploadresult [0] ['savepath'] . $uploadresult [0] ['savename'];
		//$extension = $uploadresult [0] ['extension'];
		$inputfilename = 'D:\resetpwd.xlsx';
		$extension = 'xlsx';
		
		switch ($extension) {
			case 'xls' :
				$objReader = new PHPExcel_Reader_Excel5 ();
				break;
			case 'xlsx' :
				$objReader = new PHPExcel_Reader_Excel2007 ();
				break;
			case 'csv' :
				$objReader = new PHPExcel_Reader_CSV ();
				break;
			default :
				$this->error ( '上传的文件类型不匹配，无法识别！' );
				break;
		}
		
		$objPHPExcel = $objReader->load ( $inputfilename );		// 读取Excel
		$currentsheet = $objPHPExcel->getActiveSheet (); 		// 获取活动工作薄
		$allcolumn = $currentsheet->getHighestColumn ();		// 获取最大列数
		$allrow = $currentsheet->getHighestRow ();				// 获取最大行数
		$allcolumnindex = PHPExcel_Cell::columnIndexFromString ( $allcolumn ); // 将列数的字母索引转换成数字（重要）
		
		// 开始读取数据
		$global = array();
		// 读取excel文件中的数据
		for($row = 2; $row <= $allrow; $row ++) {
			$singlerecord = array ();
			for($column = 0; $column < $allcolumnindex; $column ++) {
				$singlerecord [$column] = $currentsheet->getCellByColumnAndRow ( $column, $row )->getValue ();
			}
			$global [$row - 2] = $singlerecord;
		}
		//p($global);die;
		$memberlist = array();
		$reflect = array(
				0 => 'e_id',
				1 => 'customer_name',
				2 => 'account',
				3 => 'original_membercard'
		);
		for($i = 0; $i < count( $global ); $i ++){
			for($j = 0; $j < 4; $j ++){
				$memberlist [$i] [$reflect [$j]] = trim ( $global [$i] [$j] );
			}
		}
		//p($memberlist);die;
		$singlecustomer = array (); // 查找单个customer
		$infotable = M ( 'customerinfo' ); // 顾客表
		$operateinfo = array (); // 最终操作结果数组
		for($i = 0; $i < count ( $memberlist ); $i ++) {
			$singlecustomer = array (
					'e_id' => $memberlist [$i] ['e_id'],
					'account' => $memberlist [$i] ['account'],
					'is_del' => 0
			);
			$singleinfo = $infotable->where ( $singlecustomer )->find ();
			if($singleinfo) {
				$singleinfo ['password'] = md5 ( $singleinfo ['account'] ); // 重置密码
				if(empty ( $singleinfo ['customer_name'] ) && ! empty ( $memberlist [$i] ['customer_name'] )) {
					$singleinfo ['customer_name'] = $memberlist [$i] ['customer_name']; // 顾客姓名空重置姓名
				}
				if(empty ( $singleinfo ['original_membercard'] ) && ! empty ( $memberlist [$i] ['original_membercard'] )) {
					$singleinfo ['original_membercard'] = $memberlist [$i] ['original_membercard']; // 顾客会员卡号空重置会员卡号
				}
				$operateinfo [$i] = $infotable->save ( $singleinfo ); // 记录操作结果
				if($operateinfo [$i]) {
					$operateinfo [$i] = 'reset customer\'s password successfully!';
				} else {
					$operateinfo [$i] = 'reset encount a problem!';
				}
			}else {
				$operateinfo [$i] = 'This customer haven\'t register yet!';
			}
		}
		p('completed');p($operateinfo);die;
	}
	
}
?>