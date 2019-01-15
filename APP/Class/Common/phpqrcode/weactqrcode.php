<?php
include_once ( "phpqrcode.php" );	// 需要phpqrcode类支持
/**
 * 微动二维码类。
 * @author 赵臣升
 * CreateTime:2015/04/03 16:14:25.
 */
class WeActQRCode {
	/**
	 * 构造函数
	 */
	public function __construct() {
		// to do sth. ...
	}
	
	/**
	 * 生成二维码函数。
	 * @author 赵臣升。
	 * CreateTime:2015/04/03 21:40:25.
	 *
	 * @param string $e_id 商家编号，必须，该二维码是哪个商家的（存放文件夹目录）
	 * @param string $url 二维码URL地址（必须参数）
	 * @param string $usetype 二维码使用类型：product,cate,customer,guide,order,nativepay
	 * @param string $saveqrpath 保存二维码的文件夹路径，以./开头的相对路径
	 * @param string $saveqrname 不带后缀的二维码文件名
	 * @param string $logopathname 二维码需要嵌入的LOGO（可选参数，空就代表不嵌入），以./开头的相对路径
	 * @param string $errorcorrectionlv 二维码的质量，有L、M、Q、H4种分类（可选参数，有默认值）
	 * @param number $matrixsize 二维码大小，默认6（可选参数，有默认值）
	 * @param number $margin 二维码边距，默认2像素（可选参数，有默认值）
	 * @return array $createresult 系统返回数组信息
	 */
	public function createQRCode($e_id = '', $url = '', $usetype = '', $saveqrpath = '', $saveqrname = '', $logopathname = '', $errorcorrectionlv = 'Q', $matrixsize = 6, $margin = 2) {
		// 准备生成二维码的返回信息
		$createresult = array (
				'errCode' => 10001,
				'errMsg' => "系统繁忙，请稍后再生成二维码！",
				'data' => array ()
		);
		// 如果有商家编号并URL值不空才去生成二维码
		if (! empty ( $e_id ) && ! empty ( $url )) {
			// 二维码预处理
			if (empty ( $usetype )) {
				$usetype = "normal"; // Step1：没有指明二维码用途，默认二维码用途为普通
			}
			if (empty ( $saveqrpath )) {
				$saveqrpath = "./Updata/images/" . $e_id . "/dimensioncode/defaultcode/" . date ( 'Ymd' ) . "/"; // Step2：没有指明二维码存放的文件夹路径，直接放到默认文件夹路径下
			}
			if (! file_exists ( $saveqrpath )) {
				mkdirs ( $saveqrpath ); // Step3：如果不存在目录，则自动创建目录文件夹
			}
			if (empty ( $saveqrname )) {
				$saveqrname = md5 ( uniqid ( rand (), true ) ); // Step4：如果没有文件名，直接md5一个随机文件名
			}
			// 二维码信息内容处理
			$qrprefix = "_qrcode_"; // 二维码前缀
			$typesuffix = ".png"; // 默认二维码后缀
			$qrfullname = $usetype . $qrprefix . $saveqrname . $typesuffix; // 二维码图片全名（带后缀）
			$qrabsolutepath = $saveqrpath . $qrfullname; // 二维码完整保存的绝对路径（文件夹+全名+后缀）
			
			QRcode::png ( $url, $qrabsolutepath, $errorcorrectionlv, $matrixsize, $margin ); // Step5：生成二维码图片
			// 生成二维码成功
			$createresult ['errCode'] = 0;
			$createresult ['errMsg'] = "ok";
			$createresult ['data'] ['qrcode'] = substr ( $qrabsolutepath, 1 ); // 原始二维码图片路径
			// 如果存在LOGO，继续嵌入LOGO
			if (! empty ( $logopathname ) && file_exists ( $logopathname )) {
				// 如果有需要嵌入的LOGO文件名，并且存在这样一张图片
				// Step1：准备带logo二维码的信息
				$logoqrprefix = "_logoqrcode_"; // 带logo的二维码
				$logoqrfullname = $usetype . $logoqrprefix . $saveqrname . $typesuffix; // 带logo的二维码图片全名
				$logoqrabsolutepath = $saveqrpath . $logoqrfullname; // 带logo的二维码图片最终保存路径
	
				// Step2：生成带logo的二维码
				$qrimage = imagecreatefromstring ( file_get_contents ( $qrabsolutepath ) ); // 读取一张已有的二维码图片
				$logoimage = imagecreatefromstring ( file_get_contents ( $logopathname ) ); // 读取一张已存在并要嵌入作为二维码LOGO的图片
				$QR_width = imagesx ( $qrimage ); // 二维码图片宽度
				$QR_height = imagesy ( $qrimage ); // 二维码图片高度
				$logo_width = imagesx ( $logoimage ); // logo图片宽度
				$logo_height = imagesy ( $logoimage ); // logo图片高度
				$logo_qr_width = $QR_width / 5; // 二维码的宽度除5作为LOGO的宽度
				$scale = $logo_width / $logo_qr_width; // 得到LOGO的缩放比例scale
				$logo_qr_height = $logo_height / $scale; // LOGO图的高度也同比缩放
				$from_width = ($QR_width - $logo_qr_width) / 2; // 目标X/Y的坐标点
				// 重新组合图片并调整大小
				imagecopyresampled ( $qrimage, $logoimage, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height );
				$logoqrresult = imagepng ( $qrimage, $logoqrabsolutepath, 5 ); // 输出图片
				if ($logoqrresult) {
					$createresult ['data'] ['logoqrcode'] = substr ( $logoqrabsolutepath, 1 ); // 带logo的二维码图片路径
				}
			}
		}
		return $createresult;
	}
}
?>