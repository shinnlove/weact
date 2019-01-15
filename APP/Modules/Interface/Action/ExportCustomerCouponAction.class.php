<?php
class ExportCustomerCouponAction extends Action {
	public function exportCustomerCoupon () {
		
		$finaltitle = array(
				0 => '线上会员编号',
				1 => '会员微信号',
				2 => '线上会员账号',
				3 => '线下原版ERP会员编号',
				4 => '线上现用ERP会员编号',
				5 => '会员姓名',
				6 => '性别',
				7 => '身份证号',
				8 => '生日',
				9 => '开卡日期',
				10 => '到期日',
				11 => '分店名称',
				12 => '线下ERP终端名称',
				13 => '优惠券名称',
				14 => '获得时间',
				15 => '优惠券sn编号',
				16 => '优惠券sn密码'
		);
		
		$customercouponinfo = array();
		
		$model = new Model();
		$sql = 'ci.account = oc.contact_number and ci.customer_id = cc.customer_id and cc.e_id = \'201405291912250003\' and cc.coupon_id=\'whkhpujwfhkus320980rwghq\'';
		$field = 'ci.customer_id, ci.openid, ci.account, oc.original_id, oc.member_card, oc.customer_name, oc.sex, oc.id_card, oc.birthday, oc.card_start, oc.card_end, oc.subbranch_id, oc.subbranch_original_id, cc.coupon_name, cc.get_time, cc.coupon_sncode, cc.coupon_password';
		$customercouponinfo = $model->table('t_customerinfo ci, t_offlinecustomer oc, t_customercoupon cc')->where($sql)->order('cc.coupon_sncode')->field($field)->select();
		
		for($i = 0; $i < count($customercouponinfo); $i ++){
			$customercouponinfo [$i] ['get_time'] = timetodate($customercouponinfo [$i] ['get_time']);
		}
		
		//p(count($customercouponinfo));die;
		
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
		 * $customercouponinfo中的每一行记为$value，
		* $value中的每个字段记为$value2
		*  */
		$j = 2;
		foreach ( $customercouponinfo as $rowvalue ) {
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
	}
	
	public function createNZQ() {
		$finaltitle = array(
				0 => '券号',
				1 => '密码'
		);
		
		$basestart = 20151500000; // 基础序列
		
		$nzqinfo = array(); // 年终券数组
		for($i = 0; $i < 12398; $i ++) {
			$currentnumber = $basestart + $i;
			$nzqinfo [$i] = array(
					'coupon_sncode' => "NZQ" . $currentnumber,
					'coupon_password' => randCode( 8, 1 )
			);
		}
		
		$excelFileName = md5 ( uniqid ( rand (), true ) ); // 定义Excel文件的文件名
		$sheetTitle = "NZQ年终券12398张"; // 设置所绘制的Sheet表单名
		
		/* 实例化类 */
		vendor ( 'PHPExcel.PHPExcel' ); // 导入WeChat的SDK
		$objPHPExcel = new PHPExcel (); // 新建一个PHPExcel类的对象$objPHPExcel
		
		/* 设置输出的excel文件为2007兼容格式 */
		$objWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel ); // 非2007格式
		// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 2007格式
		
		/* 设置当前的sheet的列宽 */
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 设置当前默认激活的工作表（Sheet1还是Sheet2还是Sheet3）
		$objActSheet = $objPHPExcel->getActiveSheet (); // 调用getActiveSheet函数得到激活的Sheet工作表对象（可以连写）
		for($i = 0; $i < count ( $finaltitle ); $i ++) {
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
		 * $nzqinfo中的每一行记为$value，
		* $value中的每个字段记为$value2
		*  */
		$j = 2;
		foreach ( $nzqinfo as $rowvalue ) {
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
	}
	
	public function createSRQ() {
		$finaltitle = array(
				0 => '券号',
				1 => '密码'
		);
		
		$basestart = 52020150000; // 基础序列
		
		$nzqinfo = array(); // 年终券数组
		for($i = 0; $i < 1089; $i ++) {
			$currentnumber = $basestart + $i;
			$nzqinfo [$i] = array(
					'coupon_sncode' => "SRQ" . $currentnumber,
					'coupon_password' => randCode( 8, 1 )
			);
		}
		
		$excelFileName = md5 ( uniqid ( rand (), true ) ); // 定义Excel文件的文件名
		$sheetTitle = "SRQ生日券1089张"; // 设置所绘制的Sheet表单名
		
		/* 实例化类 */
		vendor ( 'PHPExcel.PHPExcel' ); // 导入WeChat的SDK
		$objPHPExcel = new PHPExcel (); // 新建一个PHPExcel类的对象$objPHPExcel
		
		/* 设置输出的excel文件为2007兼容格式 */
		$objWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel ); // 非2007格式
		// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 2007格式
		
		/* 设置当前的sheet的列宽 */
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 设置当前默认激活的工作表（Sheet1还是Sheet2还是Sheet3）
		$objActSheet = $objPHPExcel->getActiveSheet (); // 调用getActiveSheet函数得到激活的Sheet工作表对象（可以连写）
		for($i = 0; $i < count ( $finaltitle ); $i ++) {
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
		 * $nzqinfo中的每一行记为$value，
		* $value中的每个字段记为$value2
		*  */
		$j = 2;
		foreach ( $nzqinfo as $rowvalue ) {
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
	}
	
	public function exportActivityRank() {
		$sql = "SELECT 
    A.enroll_id as enroll_id,
    openid,
    (name) as pname,
	tel as tel,
    wish,
    add_time,
    B.likenum as likenum
FROM
    t_activityenroll as A
        inner join
    ((SELECT 
        enroll_id, count(e_id) as likenum
    FROM
        t_wishlike
    WHERE
        is_del = 0
    GROUP BY enroll_id
    ORDER BY likenum desc) as B) ON A.enroll_id = B.enroll_id
WHERE
    A.e_id = '201412021712300012' AND A.is_del = 0 ORDER BY likenum desc;";
		
		$finaltitle = array(
				0 => '报名编号',
				1 => '微信openid',
				2 => '姓名',
				3 => '联系电话',
				4 => '爱情宣言',
				5 => '参与时间',
				6 => '最终获得点赞'
		);
		
		$result = M ()->query($sql);
		for($i = 0; $i < count ( $result ); $i ++) {
			$result [$i] ['add_time'] = timetodate ( $result [$i] ['add_time'] );
		}
		
		$excelFileName = md5 ( uniqid ( rand (), true ) ); // 定义Excel文件的文件名
		$sheetTitle = "S.Life《一爱倾'诚'》活动名单"; // 设置所绘制的Sheet表单名
		
		/* 实例化类 */
		vendor ( 'PHPExcel.PHPExcel' ); // 导入WeChat的SDK
		$objPHPExcel = new PHPExcel (); // 新建一个PHPExcel类的对象$objPHPExcel
		
		/* 设置输出的excel文件为2007兼容格式 */
		$objWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel ); // 非2007格式
		// $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 2007格式
		
		/* 设置当前的sheet的列宽 */
		$objPHPExcel->setActiveSheetIndex ( 0 ); // 设置当前默认激活的工作表（Sheet1还是Sheet2还是Sheet3）
		$objActSheet = $objPHPExcel->getActiveSheet (); // 调用getActiveSheet函数得到激活的Sheet工作表对象（可以连写）
		for($i = 0; $i < count ( $finaltitle ); $i ++) {
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
		 * $nzqinfo中的每一行记为$value，
		* $value中的每个字段记为$value2
		*  */
		$j = 2;
		foreach ( $result as $rowvalue ) {
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
		
	}
}
?>