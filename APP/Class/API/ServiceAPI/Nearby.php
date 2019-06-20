<?php
/**
 * 查询附近设施API。
 * @author 万路康。
 * CreateTime：2014/11/06 00:59:25.
 */
class Nearby {
	var $ak = 'q3FTBrW3uF7N6pBtcKPKIV6W'; // 百度查询附近的ak
	
	/**
	 * 查询附近设施的函数。
	 * @param string $queryContent        	
	 * @param string $location        	
	 * @param number $radius        	
	 * @return array 返回附近的设施数组
	 */
	public function queryNearby($queryContent = '', $location, $radius = 5000) {
		$url = "http://api.map.baidu.com/place/v2/search";
		$params = array (
				'ak' => $this->ak,
				'output' => 'json',
				'query' => $queryContent,
				'page_size' => 10,
				'page_num' => 0,
				'scope' => 2,
				'location' => $location,
				'radius' => $radius,
				'filter' => 'sort_name:distance' 
		);
		$jsonEncodeInfo = json_decode ( http ( $url, $params ), true );
		$final = '附近的' . '【' . $queryContent . '】';
		
		// 先给多图文的主图文赋值，显示城市名+天气预报，描述是详情请点击查看，picurl用的是服务器上的地址以方便访问外网，url是点进该图片的url，为中国天气网
		$queryArray [] = array (
				"Title" => $final,
				"Description" => "详情请点击查看",
				"PicUrl" => "http://www.we-act.cn/APP/Modules/Admin/Tpl/Public/images/platformimage/locationnearby.jpg", // 注意这个路径是写死的
				"Url" => "http://map.baidu.com/?latlng=" . $location . "&title=我的位置&content&autoOpen=true&l" 
		);
		
		// 获得所在城市名
		$cityapi = "http://api.map.baidu.com/geocoder";
		$lbsparams = array (
				'location' => $location,
				'coord_type' => 'gcj02',
				'output' => 'json',
				'src' => 'yourCompanyName|yourAppName' 
		);
		$cityInfo = json_decode ( http ( $cityapi, $lbsparams ), true );
		$cityName = $cityInfo ['result'] ['addressComponent'] ['city'];
		
		for($i = 0; $i < count ( $jsonEncodeInfo ['results'] ); $i ++) {
			$final = '【' . $jsonEncodeInfo ['results'] [$i] ['name'] . '】' . ' ' . '<' . $jsonEncodeInfo ['results'] [$i] ['detail_info'] ['distance'] . '米>' . ' ' . $jsonEncodeInfo ['results'] [$i] ['address'] . ' ' . '联系电话:' . $jsonEncodeInfo ['results'] [$i] ['telephone'];
			$navurl = urlencode("api.map.baidu.com/direction?origin=latlng:" . $location . "|name:我的位置&destination=" . $jsonEncodeInfo ['results'] [$i] ['name'] . "&mode=driving&region=" . $cityName . "&src=WeAct");
			$queryArray [] = array (
					"Title" => $final,
					"Description" => "详情请点击查看",
					"PicUrl" => '',
					"Url" => $navurl
			);
		}
		return $queryArray;
	}
	
	/**
	 * 根据用户openid和商家original_id去wechatmsginfo表里找寻用户最近一次的地理位置函数。
	 * @param array $einfo 消息目标商家信息
	 * @param string $bereplied	被商家回复的顾客openid
	 * @return boolean|string 如果没找到，就返回false，如果找到了，返回纬度,经度格式。
	 */
	public function searchUserLocation($einfo = NULL, $bereplied = '') {
		$finallocation = false; // 最终的用户位置信息，默认为没找到
		// Step1：根据企业的original_id和用户的openid去找用户所有记录（当然只取有x记录和y记录的）
		$wcmimap = array (
				'to_user_name' => $einfo ['original_id'],
				'from_user_name' => $bereplied,
				'is_del' => 0
		);
		$wcmitable = M ( 'wechatmsginfo' );
		$wcmiresult = $wcmitable->where ( $wcmimap )->order ( 'create_time desc' )->limit ( 1000 )->select (); // 查找该用户的最近微信消息（最多1000条，不可太多）
		// Step2：如果找到，继续尝试找到数组中最后一次用户的经度和纬度
		if ($wcmiresult) {
			for($i = 0; $i < count ( $wcmiresult ); $i ++) {
				if (! empty ( $wcmiresult [$i] ['location_x'] ) || ! empty ( $wcmiresult [$i] ['latitude'] )) {
					$position ["x"] = 0; // 声明位置坐标x变量
					$position ["y"] = 0; // 声明位置坐标y变量
					if (! empty ( $wcmiresult [$i] ['location_x'] )) {
						$position ["x"] = $wcmiresult [$i] ['location_x']; // 坐标不空，取坐标x
						$position ["y"] = $wcmiresult [$i] ['location_y'];
					} else if (! empty ( $wcmiresult [$i] ['latitude'] )) {
						$position ["x"] = $wcmiresult [$i] ['latitude']; // 经纬度不空，取经纬度
						$position ["y"] = $wcmiresult [$i] ['longitude'];
					}
					$standardlocation = $position ["x"] . ',' . $position ["y"];
					$finallocation = trim ( $standardlocation ); // 注意：这里是去掉前一个字符串两端的空格，不然代到url中会出错（不要去掉这句语言）
					break; // 只要找到一条最近的，就可以停止循环了，否则一直是循环中
				}
			}
		}
		return $finallocation;
	}
	
	/**
	 * 判别是否查找附近的...函数。
	 * @param array $originalSpilt        	
	 * @return string 返回是否查找附近的...如果不是查找附近的，返回null，如果是查找附近的，返回附近的内容。
	 */
	public function isQueryNearby($originalSpilt = NULL) {
		$result = NULL;
		for($i = 0; $i < count ( $originalSpilt ); $i ++) {
			if ($originalSpilt [$i] ['word_tag'] == '130' && ($originalSpilt [$i] ['word'] == '附近' || $originalSpilt [$i] ['word'] == '周围' || $originalSpilt [$i] ['word'] == '周边' || 			// 词性130，如附近，周围，周边
			$originalSpilt [$i] ['word'] == '边上' || $originalSpilt [$i] ['word'] == '找' || $originalSpilt [$i] ['word'] == '最近')) {
				for($j = $i; $j < count ( $originalSpilt ); $j ++) // 从该词向后搜索要询问的内容，如附近（找/边上/周围/周边）的酒店
					if ($originalSpilt [$j] ['word_tag'] == '95') {
						$result = $originalSpilt [$j] ['word'];
						break;
					}
			} else if ($originalSpilt [$i] ['word_tag'] == '124' && ($originalSpilt [$i] ['word'] == '哪儿' || $originalSpilt [$i] ['word'] == '哪里' || $originalSpilt [$i] ['word'] == '何方' || $originalSpilt [$i] ['word'] == '何处')) { // 词性124，如哪儿、哪里、何处、何方等
				for($j = $i; $j < count ( $originalSpilt ); $j ++) // 从该词向后搜索要询问的内容，如哪里有酒店
					if ($originalSpilt [$j] ['word_tag'] == '95') {
						$result = $originalSpilt [$j] ['word'];
						break;
					}
				if (! $result) { // 如果没找到的话
					for($k = $i; $k >= 0; $k --) // 从该词向前搜索要询问的内容，如酒店在哪里
						if ($originalSpilt [$k] ['word_tag'] == '95') {
							$result = $originalSpilt [$k] ['word'];
							break;
						}
				}
			}
			if ($result)
				break; // 如果找到了就跳出for循环
		}
		if (! $result) { // 如果一个词没搜到，就搜索两个词，如酒店安在，安和在分别为一个词
			for($i = 0; $i < count ( $originalSpilt ) - 1; $i ++) {
				if (($originalSpilt [$i] ['word'] == '安' && $originalSpilt [$i + 1] ['word'] == '在') || ($originalSpilt [$i + 1] ['word'] == '何在') || 				// 此处包含 酒店何在这种情况
				($originalSpilt [$i] ['word'] == '什么' && $originalSpilt [$i + 1] ['word'] == '位置') || ($originalSpilt [$i] ['word'] == '什么' && $originalSpilt [$i + 1] ['word'] == '地方')) {
					for($j = $i + 2; $j < count ( $originalSpilt ); $j ++) // 注意这里j是从i+2开始取，即从第这两个词后的第一个词开始取
						if ($originalSpilt [$j] ['word_tag'] == '95') { // 如什么地方有酒店
							$result = $originalSpilt [$j] ['word'];
							break;
						}
					if (! $result) { // 如果没找到的话
						for($k = $i; $k >= 0; $k --) // 从第i个词向前搜索要询问的内容，如酒店安在，酒店在什么位置
							if ($originalSpilt [$k] ['word_tag'] == '95') {
								$result = $originalSpilt [$k] ['word'];
								break;
							}
					}
				}
				if ($result)
					break; // 如果找到了就跳出for循环
			}
		}
		// p($result);die;
		return $result;
	}
}
?>