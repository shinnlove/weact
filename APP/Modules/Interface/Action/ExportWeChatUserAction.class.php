<?php
class ExportWeChatUserAction extends Action {
	public function exportWeChatUser () {
	
		$finaltitle = array(
				0 => '用户头像',
				1 => '用户编号',
				2 => '用户微信号',
				3 => '姓名',
				4 => '昵称',
				5 => '企业',
				6 => '联系信息',
				7 => '邮箱',
				8 => '性别',
				9 => '生日',
				10 => '地址',
				11 => '注册时间',
				12 => '语言',
				13 => '城市',
				14 => '省份',
				15 => '国家'
		);
		
		
		$sql = 'ci.openid = wu.openid and ci.e_id = \'201412021712300012\' and ci.is_del = 0 and wu.is_del = 0';
		$field = 'wu.head_img_url, ci.customer_id, ci.openid, ci.customer_name, ci.nick_name, ci.e_id, ci.contact_number, ci.email, ci.sex, ci.birthday, ci.customer_address, ci.register_time, wu.language, wu.city, wu.province, wu.country';
		$model = new Model();
		$wechatuserlist = $model->table('t_customerinfo ci, t_wechatuserinfo wu')->where($sql)->field($field)->select();
		
		for($i = 0; $i < count($wechatuserlist); $i ++){
			$wechatuserlist [$i] ['register_time'] = timetodate($wechatuserlist [$i] ['register_time']);
		}
		//p($wechatuserlist);die;
		
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
			$objActSheet->setCellValue ( $i . $j, $value ); // 调用设置单元格值的函数setCellValue
			$i ++;
		}
	
		/* 从第二行（j>=2）开始，打印excel文件内容
		 * $wechatuserlist中的每一行记为$value，
		* $value中的每个字段记为$value2
		*  */
		$j = 2;
		foreach ( $wechatuserlist as $rowvalue ) {
			$i = 'A'; // 每次循环从A列开始打印
			$objActSheet->getRowDimension($j)->setRowHeight(50);
			$objActSheet->getColumnDimension('A')->setWidth(9);
			$objActSheet->getStyle( $i . $j )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 首列水平居中貌似对图片没有用
			$objActSheet->getStyle( $i . $j )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // 首列垂直居中貌似对图片没有用
			foreach ( $rowvalue as $cellvalue ) {
				if($i == 'A') {
					$objDrawing = new PHPExcel_Worksheet_Drawing(); // 新建绘图类
					$objDrawing->setName('headimg'); // 图片名字
					$objDrawing->setDescription('头像'); // 图片描述
					$objDrawing->setPath( 'Updata/images/201412021712300012/cardstyle/thumb_547df4192143c.jpg' ); // 图片路径
					$objDrawing->setWidth(60); // 图片宽度
					$objDrawing->setHeight(60); // 图片高度
					$objDrawing->setCoordinates( $i . $j ); // 该图片对齐的格子
					$objDrawing->setWorksheet($objActSheet); // 写入绘图
				} else {
					$objActSheet->setCellValue ( $i . $j, ' ' . $cellvalue ); // 调用设置单元格值的函数setCellValue
					// $objActSheet->setCellValue($i.$j,strval($value2)); //调用设置单元格值的函数setCellValue
				}
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
	}
}
?>