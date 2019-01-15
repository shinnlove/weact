<?php
/**
 * 本类别为从Excel导出数据的基类，定义一些Excel必有的东西。
 * @author 赵臣升。
 * CreateTime:2014/12/24 00:00:02.
 * 
 * 下面是总结的几个使用方法
 * include 'PHPExcel.php';
 * include 'PHPExcel/Writer/Excel2007.php'; // 输出.xlxs，Office2007格式的
 * include 'PHPExcel/Writer/Excel5.php'; // 用于输出.xls，Office2003格式的
 * $objPHPExcel = new PHPExcel(); // 创建一个excel
 * $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 保存excel—2007格式
 * $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel); // 2007早期版本格式
 * $objWriter->save("xxx.xlsx");
 * 直接输出到浏览器
 * $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
 * ob_end_clean (); // 清空缓存
 * header("Pragma: public");
 * header("Expires: 0");
 * header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
 * header("Content-Type:application/force-download");
 * header("Content-Type:application/vnd.ms-execl");
 * header("Content-Type:application/octet-stream");
 * header("Content-Type:application/download");;
 * header("Content-Disposition:attachment;filename = $excelFileName . '.xls' ");
 * header("Content-Transfer-Encoding:binary");
 * $objWriter->save('php://output');
 * ——————————————————————————————————————–
 * 设置excel的属性：
 * 创建人
 * $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
 * 最后修改人
 * $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
 * 标题
 * $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
 * 题目
 * $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
 * 描述
 * $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
 * 关键字
 * $objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
 * 种类
 * $objPHPExcel->getProperties()->setCategory("Test result file");
 * ——————————————————————————————————————–
 * 设置当前的sheet
 * $objPHPExcel->setActiveSheetIndex(0);
 * 设置sheet的name
 * $objPHPExcel->getActiveSheet()->setTitle('Simple');
 * 设置单元格的值
 * $objPHPExcel->getActiveSheet()->setCellValue('A1', 'String'); // 设置字符串值（我们到处前都在前面加个空格，数字的就不会乱码，当然也不能计算）
 * $objPHPExcel->getActiveSheet()->setCellValue('A2', 12); // 设置数字
 * $objPHPExcel->getActiveSheet()->setCellValue('A3', true); // ???
 * $objPHPExcel->getActiveSheet()->setCellValue('C5', '=SUM(C2:C4)'); // 设置C5的值为C2+C4
 * $objPHPExcel->getActiveSheet()->setCellValue('B8', '=MIN(B2:C5)'); // 设置B8的值为B2和C5中最小的
 * 合并单元格（从左/上到右/下）
 * $objPHPExcel->getActiveSheet()->mergeCells('A18:E22'); // A18到E22区块合并单元格
 * 分离单元格（从左/上到右/下）
 * $objPHPExcel->getActiveSheet()->unmergeCells('A28:B28'); // A28到E28区块合并单元格
 * 保护cell
 * $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true); // Needs to be set to true in order to enable any worksheet protection!
 * $objPHPExcel->getActiveSheet()->protectCells('A3:E13', 'PHPExcel'); // 对A3到E13的区隔进行保护（类似合并单元格）
 * 设置格式
 * // Set cell number formats
 * echo date('H:i:s') . " Set cell number formats\n";
 * $objPHPExcel->getActiveSheet()->getStyle('E4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
 * $objPHPExcel->getActiveSheet()->duplicateStyle( $objPHPExcel->getActiveSheet()->getStyle('E4'), 'E5:E13' );
 * 设置column宽width
 * $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true); // 设置B列自动列宽
 * $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); // 设置D列的列宽为12
 * 设置一行的高度height
 * $objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(30); // 第6行设置行高为30
 * 设置font
 * $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Candara'); // B1字体名字Candara
 * $objPHPExcel->getActiveSheet()->getStyle('D13')->getFont()->setSize(20); // D13字体大小20
 * $objPHPExcel->getActiveSheet()->getStyle('C6')->getFont()->setBold(true); // C6字体加粗
 * $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE); // 加下划线
 * $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE); //设置颜色
 * 设置align
 * $objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
 * $objPHPExcel->getActiveSheet()->getStyle('D12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
 * $objPHPExcel->getActiveSheet()->getStyle('D13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
 * $objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
 * //垂直居中
 * $objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
 * 设置column的border
 * $objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
 * $objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
 * 设置border的color
 * $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getLeft()->getColor()->setARGB('FF993300');
 * $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getTop()->getColor()->setARGB('FF993300');
 * $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
 * $objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getTop()->getColor()->setARGB('FF993300');
 * $objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
 * $objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getRight()->getColor()->setARGB('FF993300');
 * 设置填充颜色
 * $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
 * $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');
 * $objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
 * $objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->getStartColor()->setARGB('FF808080');
 * 加图片
 * $objDrawing = new PHPExcel_Worksheet_Drawing(); // 新建绘图类
 * $objDrawing->setName('Logo'); // 图片名字
 * $objDrawing->setDescription('Logo'); // 图片描述
 * $objDrawing->setPath('./images/officelogo.jpg'); // 图片路径
 * $objDrawing->setWidth(200); // 图片宽度
 * $objDrawing->setHeight(300); // 图片高度
 * $objDrawing->setWorksheet($objPHPExcel->getActiveSheet()); // 写入绘图
 * 第二种加图片用法：
 * $objDrawing = new PHPExcel_Worksheet_Drawing(); // 新建绘图类
 * $objDrawing->setName('Paid'); // 图片名字
 * $objDrawing->setDescription('Paid'); // 图片描述
 * $objDrawing->setPath('./images/paid.png'); // 图片路径
 * $objDrawing->setCoordinates('B15'); // 绘制到哪个格子
 * $objDrawing->setOffsetX(110); // 
 * $objDrawing->setRotation(25);
 * $objDrawing->getShadow()->setVisible(true);
 * $objDrawing->getShadow()->setDirection(45);
 * $objDrawing->setWorksheet($objPHPExcel->getActiveSheet()); // 绘制图片
 * //处理中文输出问题
 * 需要将字符串转化为UTF-8编码，才能正常输出，否则中文字符将输出为空白，如下处理：
 * $str = iconv('gb2312', 'utf-8', $str);
 * 或者你可以写一个函数专门处理中文字符串：
 * function convertUTF8($str = '')
 * {
 * if(empty($str)) return '';
 * return iconv('gb2312', 'utf-8', $str);
 * }
 * 从数据库输出数据处理方式，读取数据如：
 * $db = new Mysql($dbconfig);
 * $sql = "SELECT * FROM 表名";
 * $row = $db->GetAll($sql); // $row 为二维数组
 * $count = count($row);
 * for ($i = 2; $i <= $count+1; $i++) {
 * $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, convertUTF8($row[$i-2][1]));
 * $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, convertUTF8($row[$i-2][2]));
 * $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, convertUTF8($row[$i-2][3]));
 * $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, convertUTF8($row[$i-2][4]));
 * $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, convertUTF8(date("Y-m-d", $row[$i-2][5])));
 * $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, convertUTF8($row[$i-2][6]));
 * $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, convertUTF8($row[$i-2][7]));
 * $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, convertUTF8($row[$i-2][8]));
 * }
 *
 * 在默认sheet后，创建一个worksheet
 * //echo date('H:i:s') . " Create new Worksheet object\n"; // 提示生成Excel
 * $objPHPExcel->createSheet();
 * $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
 * $objWriter-save('php://output');
 */
class ExcelDataExport {
	
}
?>