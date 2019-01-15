<?php
/**
 * 天气查询类。
 * @author 万路康。
 * CreateTime：2014/11/06 00:59:25.
 * API地址：http://www.weather.com.cn/
 */
class Weather {
	var $ak = 'q3FTBrW3uF7N6pBtcKPKIV6W'; // 查询中国天气网用的ak
	
	/**
	 * 查询天气API封装。
	 * Author：万路康。
	 * CreateTime：2014/07/17 03:04:16.
	 *
	 * @param string $cityName        	
	 * @return array 返回经过json处理的天气数组。
	 */
	private function queryWeather($cityName = '上海') {
		$url = "http://api.map.baidu.com/telematics/v3/weather"; // 拼接发送信息API的URL请求地址
		$params = array (
				'location' => $cityName,
				'output' => 'json',
				'ak' => $this->ak 
		);
		return json_decode ( http ( $url, $params ), true ); // 调用Common公有的http()函数发送给中国天气网
	}
	
	/**
	 * 获取天气信息函数。
	 * Author：万路康。
	 * @param string $cityName	要获取天气的城市中文名
	 * @return array 可发送给微信的天气数组格式
	 */
	public function getWeatherInfo($cityName = '上海') {
		$weatherinfo = $this->queryWeather ( $cityName ); 	// 调用天气API获得天气所有信息
		if ($weatherinfo ["error"] != 0) {
			return $weatherinfo ["status"];
		}
		$curHour = ( int ) date ( 'H', time () ); 			// 获得当前小时
		$weather = $weatherinfo ["results"] [0]; 			// 从所有天气信息中取天气结果信息那个数组，当中不包括穿衣运动等
		
		$temperatureMsg = mb_substr ( $weather ["weather_data"] [0] ["date"], 10, 11, 'utf-8' ); // 获得包含温度信息的字符串，如（实时温度：32℃）
		for($i = 0; $i < strlen ( $temperatureMsg ); $i ++) // 逐一字符串筛选出温度数值
			if ($temperatureMsg [$i] >= '0' && $temperatureMsg [$i] <= '9') $temperature .= $temperatureMsg [$i];
		// 先给多图文的主图文赋值，显示城市名+天气预报，描述是详情请点击查看，picurl用的是服务器上的地址以方便访问外网，url是点进该图片的url，为中国天气网
		$weatherArray [] = array ( 
				"Title" => $weather ['currentCity'] . "天气预报",
				"Description" => "详情请点击查看",
				"PicUrl" => "http://www.we-act.cn/weact/APP/Modules/Admin/Tpl/Public/images/platformimage/weatherpic.jpg", // 注意这个路径是死的
				"Url" => "http://mobile.weather.com.cn/" 
		);
		// 再给第1条副图文赋值，依次显示pm2.5,感冒情况和运动的适宜情况
		$weatherArray [] = array ( 
				"Title" => '实时:' . $temperature . '℃ ' . ' ' . 'PM2.5:' . $weather ['pm25'] . ' ' . $weather ['index'] [2] ['title'] . ':' . ' ' . 				// 感冒信息
				$weather ['index'] [2] ['zs'] . ' ' . $weather ['index'] [3] ['title'] . ':' . 				// 运动信息
				$weather ['index'] [3] ['zs'],
				"Description" => "详情请点击查看",
				"PicUrl" => "",
				"Url" => "http://mobile.weather.com.cn/" 
		);
		// 第2条副图文，显示穿衣的建议
		$weatherArray [] = array ( 
				"Title" => $weather ["index"] [0] ["des"],
				"Description" => "详情请点击查看",
				"PicUrl" => "",
				"Url" => "http://mobile.weather.com.cn/" 
		);
		switch ($weather ["weather_data"] [1] ["date"]) { 
			// 通过下一天是星期几来确定今天是是星期几，因为当天的星期不能从数组获得所有只能通过下一天的天气获得
			case '周一' :
				$weekstr = '周日';
				break;
			case '周二' :
				$weekstr = '周一';
				break;
			case '周三' :
				$weekstr = '周二';
				break;
			case '周四' :
				$weekstr = '周三';
				break;
			case '周五' :
				$weekstr = '周四';
				break;
			case '周六' :
				$weekstr = '周五';
				break;
			case '周日' :
				$weekstr = '周六';
				break;
		}
		for($i = 0; $i < count ( $weather ["weather_data"] ); $i ++) {
			// 第3到第6天副图文赋值，依次显示四天的天气预报，包含日期，星期，温度，风向，风级和天气
			$weatherArray [] = array (
				"Title" => (date ( "m-d", strtotime ( "+$i day" ) ) . ' ' . 				// strtotime("+$i day")来控制日期的增加
					($i == 0 ? $weekstr : $weather ["weather_data"] [$i] ["date"])) . " " . // 若显示的是第一条，则显示$weekstr，因为$weather["weather_data"][0]["date"]中包含中文冒号，会导致后面额数字无法显示，所有采用其它形式
					$weather ["weather_data"] [$i] ["temperature"] . " " . 					// 显示温度
					$weather ["weather_data"] [$i] ["wind"] . " " . 						// 显示风向和风的强度
					$weather ["weather_data"] [$i] ["weather"], 							// 显示天气是多云还是晴朗等
				"Description" => "详情请点击查看",
				"PicUrl" => (($curHour >= 6) && ($curHour < 18)) ? 							// 如果时间是在6到18点之间的，则显示白天的天气图片，否则显示夜晚的天气图片
					$weather ["weather_data"] [$i] ["dayPictureUrl"] : $weather ["weather_data"] [$i] ["nightPictureUrl"],
				"Url" => "http://mobile.weather.com.cn/"  									// url为中国天气网
			);
		}
		return $weatherArray;
	}
}
?>