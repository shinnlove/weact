<?php
class ExcelAction extends Action {
	public function index() {
		// 导出Excel表格
		$data = stripslashes ( $_GET ['data'] );
		$decodedata = json_decode ( $data, true );
		// p($decodedata);die;
		$data = $decodedata ['rows'];
		$title = $decodedata ['columns'];
		// 如果商户是进入页面后就查询，即F('index'.$_SESSION['curEnterprise']['e_id'])==1，
		// 则$sqlcondition为其查询条件，否则$sqlcondition为该商户下所有未删除的用户信息
		if (F ( 'index' . $_SESSION ['curEnterprise'] ['e_id'] ) == 1) {
			$sqlcondition = F ( 'data' . $_SESSION ['curEnterprise'] ['e_id'] );
			$index = F ( 'index' . $_SESSION ['curEnterprise'] ['e_id'] );
			F ( 'index' . $_SESSION ['curEnterprise'] ['e_id'], $index + 1 );
		} else {
			$sqlcondition = array (
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0 
			);
		}
		for($i = 0; $i < count ( $title ); $i ++)
			$finaltitle [$i] ['title'] = $title [$i] ['title']; // 存储的是excel标题
		for($i = 0; $i < count ( $title ); $i ++)
			$finalfield [$i] ['field'] = $title [$i] ['field']; // 存储的是excel中的数值
		// p($finalfield);die;
		$citable = M ( 'customer_costume_info' );
		$ciresult = $citable->where ( $sqlcondition )->select ();
		
		$sbtable = M ( 'subbranch' );
		$sbmap = array (
				'subbranch_id' => '-1',
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		for($i = 0; $i < count ( $ciresult ); $i ++) {
			if ($ciresult [$i] ['subordinate_shop'] != null && $ciresult [$i] ['subordinate_shop'] != '-1' && $ciresult [$i] ['subordinate_shop'] != '') {
				$sbmap ['subbranch_id'] = $ciresult [$i] ['subordinate_shop'];
				$sbinfo = $sbtable->where ( $sbmap )->find ();
				if ($sbinfo) {
					$ciresult [$i] ['subordinate_shop'] = $sbinfo ['subbranch_name'];	//门店名称
				} else {
					$ciresult [$i] ['subordinate_shop'] = '';
				}
			} else {
				$ciresult [$i] ['subordinate_shop'] = '';
			}
			$ciresult [$i] ['register_time'] = timetodate( $ciresult [$i] ['register_time'] );
			for($j = 0; $j < count ( $finalfield ); $j ++)
				$finaldata [$i] [$finalfield [$j] ['field']] = $ciresult [$i] [$finalfield [$j] ['field']];
		}
		
		// $ciresult包含一行记录的所有字段，$finaldata的每条记录只有datagrid有的字段
		/*for($i = 0; $i < count ( $ciresult ); $i ++)
			for($j = 0; $j < count ( $finalfield ); $j ++)
				$finaldata [$i] [$finalfield [$j] ['field']] = $ciresult [$i] [$finalfield [$j] ['field']];*/
		
		$excelFileName = md5 ( uniqid ( rand (), true ) ); // 定义Excel文件的文件名
		$sheetTitle = "表格1"; // 设置所绘制的Sheet表单名
		
		/* 实例化类 */
		vendor ( 'PHPExcel.PHPExcel' ); // 导入WeChat的SDK
		$objPHPExcel = new PHPExcel (); // 新建一个PHPExcel类的对象$objPHPExcel
		
		/* 设置输出的excel文件为2007兼容格式 */
		$objWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel ); // 非2007格式
		// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 2007格式
		
		/* 设置当前的sheet的列宽 */
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 设置当前默认激活的工作表（Sheet1还是Sheet2还是Sheet3）
		$objActSheet = $objPHPExcel->getActiveSheet (); // 调用getActiveSheet函数得到激活的Sheet工作表对象（可以连写）
		for($i = 0; $i < count ( $finalfield ); $i ++) {
			$index = 65 + $i;
			$objPHPExcel->getActiveSheet ()->getColumnDimension ( chr ( $index ) )->setWidth ( 20 );
		}
		
		/* 设置所绘制的sheet工作单标题 */
		$objActSheet->setTitle ( $sheetTitle ); 
		
		/* 第一行（j=1）先打印表头 */
		$i = 'A';
		$j = 1;
		foreach ( $finaltitle as $value ) {
			foreach ( $value as $value2 ) {
				$objActSheet->setCellValue ( $i . $j, $value2 ); // 调用设置单元格值的函数setCellValue
				$i ++;
			}
		}
		
		/* 从第二行（j>=2）开始，打印excel文件内容
		 * $finaldata中的每一行记为$value，
		 * $value中的每个字段记为$value2
		 *  */
		$j = 2;
		foreach ( $finaldata as $rowvalue ) {
			$i = 'A'; // 每次循环从A列开始打印
			foreach ( $rowvalue as $cellvalue ) {
				$objActSheet->setCellValue ( $i . $j, ' ' . $cellvalue ); // 调用设置单元格值的函数setCellValue
				// $objActSheet->setCellValue($i.$j,strval($value2)); //调用设置单元格值的函数setCellValue
				$i ++; // 打印B列
			}
			$j ++;
		}
		
		/* 生成到浏览器，提供下载 */
		ob_end_clean (); // 清空缓存
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control:must-revalidate,post-check=0,pre-check=0" );
		header ( "Content-Type:application/force-download" );
		header ( "Content-Type:application/vnd.ms-execl" );
		header ( "Content-Type:application/octet-stream" );
		header ( "Content-Type:application/download" );
		header ( 'Content-Disposition:attachment;filename="' . $excelFileName . '.xls"' );
		header ( "Content-Transfer-Encoding:binary" );
		$saveresult = $objWriter->save ( 'php://output' );
		echo $saveresult;
		// $this->display();
	}
	
	/**
	 * 导出顾客信息的Excel表函数。
	 */
	public function excelForCustomer() {
		
	}
	
	/**
	 * 将数据导出到Excel的函数。
	 * @param array $title 标题（第一行抬头）
	 * @param array $data 数据（真实数据，列数要跟标题匹配）
	 * @param string $finalname 文件名（如果空使用随机md5码命名）
	 * @param string $sheetname 表格名（如果空使用默认创建时的名字sheet1）
	 * @param boolean $default2007 默认使用2007Excel，即.xlsx格式
	 */
	public function exportData($title = NULL, $data = NULL, $finalname = '', $sheetname = '', $default2007 = TRUE) {
		// 导入WeChat的SDK
		vendor ( 'PHPExcel.PHPExcel' );
		//vendor ( 'PHPExcel.PHPExcel.IOFactory' );
		//vendor ( 'PHPExcel.PHPExcel.Reader.Excel5' );
		//vendor ( 'PHPExcel.PHPExcel.Reader.Excel2007' );
		$objPHPExcel = new PHPExcel (); // 新建一个PHPExcel类的对象$objPHPExcel
		
		// 定义全局excel读写对象与后缀名
		$objWriter = null; // 全局读写excel对象
		$extension = ""; // 默认的非2007文件后缀
		$minetype = ""; // 文件类型
		if ($default2007) {
			$extension = ".xlsx"; // 2007excel后缀
			$minetype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
			$objWriter = new PHPExcel_Writer_Excel2007 ( $objPHPExcel ); // 设置输出的excel文件为2007兼容格式
		} else {
			$extension = ".xls"; // 默认的非2007文件后缀
			$minetype = "application/vnd.ms-execl";
			$objWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel ); // 非2007格式
		}
		
		// 定义Excel文件的文件名
		$excelFileName = md5 ( uniqid ( rand (), true ) );
		if (! empty ( $finalname )) $excelFileName = $finalname;
		
		// 设置所绘制的Sheet表单名
		$sheetTitle = "Sheet1";
		if (! empty ( $sheetname )) $sheetTitle = $sheetname;
		
		/* 激活一张工作表并设置列宽 */
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 设置当前默认激活的工作表（Sheet1还是Sheet2还是Sheet3）
		$objActSheet = $objPHPExcel->getActiveSheet (); // 调用getActiveSheet函数得到激活的Sheet工作表对象（可以连写）
		for($i = 0; $i < count ( $finalfield ); $i ++) {
			$index = 65 + $i;
			$objPHPExcel->getActiveSheet ()->getColumnDimension ( chr ( $index ) )->setWidth ( 20 ); // 设置当前的sheet的列宽
		}
		
		/* 设置所绘制的sheet工作单标题 */
		$objActSheet->setTitle ( $sheetTitle );
		
		/* 第一行（i=1）先打印表头 */
		$i = 1;
		$j = 'A'; // 列循环标记
		foreach ( $title as $value ) {
			$objActSheet->setCellValue ( $j . $i, $value ); // 调用设置单元格值的函数setCellValue
			$j ++;
		}
		
		/* 从第二行（i>=2）开始，打印excel文件内容，$data中的每一行记为$rowvalue，$rowvalue中的每个字段记为$cellvalue。 */
		$i = 2;
		foreach ( $data as $rowvalue ) {
			$j = 'A'; // 每次循环从A列开始打印
			foreach ( $rowvalue as $cellvalue ) {
				$objActSheet->setCellValue ( $j . $i, ' ' . $cellvalue ); // 调用设置单元格值的函数setCellValue(加个空格就不会导致长数字转成科学计数法、10的次方型)
				// $objActSheet->setCellValue ( $j . $i, strval ( $value2 ) ); //调用设置单元格值的函数setCellValue
				$j ++; // 打印B列
			}
			$i ++;
		}
		
		/* 生成到浏览器，提供下载 */
		ob_end_clean (); // 清空缓存
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control:must-revalidate,post-check=0,pre-check=0" );
		header ( "Content-Type:application/force-download" );
		header ( "Content-Type:" . $minetype );
		header ( "Content-Type:application/octet-stream" );
		header ( "Content-Type:application/download" );
		header ( "Content-Disposition:attachment;filename='" . $excelFileName . $extension . "'" ); // 文件名与后缀
		header ( "Content-Transfer-Encoding:binary" );
		$saveresult = $objWriter->save ( "php://output" );
	}
	
}
?>