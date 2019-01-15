<?php
/**
 * 新浪分词类。
 * @author 赵臣升。
 * CreateTime：2014/11/06 12:13:16.
 */
class SegmentWord {
	
	/**
	 * 请求SAE分词系统。
	 * @param string $context	请求分词的语句	
	 */
	public function querySegmentWord($context = '') {
		$spiltResult = array ();
		if (! empty ( $context )) { 
			$header = array (
					"Content-type" => "text/html",
					"charset" => "utf-8" 
			); 													// 定义https请求文件头
			$url = 'http://1.wanlukang.sinaapp.com'; 			// 定义API请求的URL地址
			$params = array (); 								// 定义发送数据的数组
			$params ['context'] = $context; 					// 定义文本内容
			$httpstr = http ( $url, $params ); 					// 调用Common公有函数中的http()函数请求新浪分词服务器返回数据
			$spiltResult = json_decode ( $httpstr, true ); 		// 将返回的数据以utf-8格式解码
		}
		return $spiltResult; 									// 返回解码后的数据
	}
}
?>