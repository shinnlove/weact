<?php
	//公用打印函数，作者：王健
	function p($array) {
		dump ( $array, 1, '<pre>', 0 );
	}

 	//组装图片路径
 	function combinePath($name,$path){
 		return $path.$name;
 	}
 	
 	//分隔字符串，（默认以逗号分隔），作者：王健，已经将此函数写入产品页面。
 	function divide($str,$param) {
 		//先将分隔参数替换成“，”
 		//$str1 = str_replace(',', '，', $str);
 		$str2 = explode($param, $str);
 		return $str2;
 	}

 	//发送邮件
	function SendMail($address,$title,$message) {
	    vendor('PHPMailer.class#phpmailer');		//导入ThinkPHP的模板

	    $mail=new PHPMailer();
	
	    // 设置PHPMailer使用SMTP服务器发送Email
	    $mail->IsSMTP();
	
	    // 设置邮件的字符编码，若不指定，则为'UTF-8'
	    $mail->CharSet='UTF-8';
	
	    // 添加收件人地址，可以多次使用来添加多个收件人
	    $mail->AddAddress($address);
	
	    // 设置邮件正文
	    $mail->Body = $message;
	
	    // 设置邮件头的From字段。
	    $mail->From = C('MAIL_ADDRESS');
	
	    // 设置发件人名字
	    $mail->FromName = 'WeAct';
	
	    // 设置邮件标题
	    $mail->Subject = $title;
	
	    // 设置SMTP服务器。
	    $mail->Host = C('MAIL_SMTP');
	
	    // 设置为"需要验证"
	    $mail->SMTPAuth = true;
	
	    // 设置用户名和密码。
	    $mail->Username = C('MAIL_LOGINNAME');
	    $mail->Password = C('MAIL_PASSWORD');
	
	    // 发送邮件。
	    return($mail->Send());
	}
	
	/**
	 * 检查登录后应该跳转的地址。
	 * @param string $refererURL	登录前的URL
	 * @return string $refererURL	检查后应该确实跳转的URL
	 */
	function afterloginURL($refererURL = '') {
		$regStart = strpos($refererURL, 'weact');
		$regLogin = strpos($refererURL, 'customerLogin');				// 匹配登录页面
		$regRegister = strpos($refererURL, 'customerRegister');			// 匹配注册页面
		if($regLogin || $regRegister) {
			if($regLogin) {
				$regLoginURL = substr($refererURL, $regStart, $regLogin - $regStart + 13);			// 客户登录页面地址
				$loginURL = 'Home/GuestHandle/customerLogin';			// 登录页面
				if($regLoginURL == $loginURL) {
					$refererURL = '';											// 如果是在登录页面或者注册页面，一律跳到会员中心（置空由前台判断）
				}
			}else if($regRegister) {
				$regRegisterURL = substr($refererURL, $regStart, $regRegister - $regStart + 16);	// 客户注册页面地址
				$registerURL = 'weact/Home/GuestHandle/customerRegister';		// 注册页面
				if($regRegisterURL == $registerURL) {
					$refererURL = '';											// 如果是在登录页面或者注册页面，一律跳到会员中心（置空由前台判断）
				}
			}
		}
		return $refererURL;
	}
	
	/**
	 *   生成随机字符串
	 *   @param int       $length  要生成的随机字符串长度
	 *   @param string    $type    随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
	 *   @return string */
	function randCode($length = 5, $type = 0) {
		$arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
		if ($type == 0) {
			array_pop($arr);
			$string = implode("", $arr);
		} elseif ($type == "-1") {
			$string = implode("", $arr);
		} else {
			$string = $arr[$type];
		}
		$count = strlen($string)-1;
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $string[rand(0, $count)];
		}
		return $code;
	}
	
	/*
	 * Author:luozegang
	* 生成随机码，以前用来作为主键，但是数据库改版后采用md5随机法，此函数已经很少用。
	*/
	function generate_uniqueid() {
		$currentdate = date ( 'YmdHms' );
		$randdata = randCode ( 4, 1 );
		return $currentdate . $randdata;
	}
	
	/*
	 * Author:shinnlove
	 * 生日日期过滤函数，3种情况:
	 * birthdayBeforeQuery()函数用于生成在此日期前的SQL查询语句
	 * birthdayBetweenQuery()函数用于生成在此日期前的SQL查询语句
	 * birthdayAfterQuery()函数用于生成在此日期前的SQL查询语句
	 */
	function birthdayBeforeQuery($sql, $dateEnd){
		return $sql.' AND DATE(birthday)<\''.$dateEnd.'\'';
	}
	
	function birthdayBetweenQuery($sql, $dateStart, $dateEnd){
		return $sql.' AND DATE(birthday) BETWEEN \''.$dateStart.'\' AND \''.$dateEnd.'\'';
	}
	
	function birthdayAfterQuery($sql, $dateStart){
		return $sql.' AND DATE(birthday)>\''.$dateStart.'\'';
	}
	
	/*
	 * Author:shinnlove
	 * 生日日期过滤函数，3种情况:
	 * registerBeforeQuery()函数用于生成在此日期前的SQL查询语句
	 * registerBetweenQuery()函数用于生成在此日期前的SQL查询语句
	 * registerAfterQuery()函数用于生成在此日期前的SQL查询语句
	 * 修改：返回的sql语句中的DATE(register_time)去掉了DATE格式
	 */
	function registerBeforeQuery($sql, $dateEnd){
		return $sql.' AND register_time <\''.$dateEnd.'\'';
	}
	
	function registerBetweenQuery($sql, $dateStart, $dateEnd){
		return $sql.' AND register_time BETWEEN \''.$dateStart.'\' AND \''.$dateEnd.'\'';
	}
	
	function registerAfterQuery($sql, $dateStart){
		return $sql.' AND register_time >\''.$dateStart.'\'';
	}
	
	/*
	 * Author:shinnlove
	* 获得日期过滤函数，3种情况:
	* getBeforeQuery()函数用于生成在此日期前的SQL查询语句
	* getBetweenQuery()函数用于生成在此日期前的SQL查询语句
	* getAfterQuery()函数用于生成在此日期前的SQL查询语句
	*/
	function getBeforeQuery($sql, $dateEnd){
		return $sql.' AND DATE(t_user_coupon.get_time)<\''.$dateEnd.'\'';
	}
	
	function getBetweenQuery($sql, $dateStart, $dateEnd){
		return $sql.' AND DATE(t_user_coupon.get_time) BETWEEN \''.$dateStart.'\' AND \''.$dateEnd.'\'';
	}
	
	function getAfterQuery($sql, $dateStart){
		return $sql.' AND DATE(t_user_coupon.get_time)>\''.$dateStart.'\'';
	}
	
	function unescape($str){
		$ret = '';
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++){
			if ($str[$i] == '%' && $str[$i+1] == 'u'){
				$val = hexdec(substr($str, $i+2, 4));
				if ($val < 0x7f) $ret .= chr($val);
				else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
				else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
				$i += 5;
			}
			else if ($str[$i] == '%'){
				$ret .= urldecode(substr($str, $i, 3));
				$i += 2;
			}
			else $ret .= $str[$i];
		}
		return $ret;
	}
	
	
	/*--------------------------以下为ThinkPHP提供的微信SDK-------添加时间：20140606，版本号：version 747------------------------*/
	
	/**
	 * 验证输入的是否是手机号
	 */
	function isMobile($mobile) {
		return preg_match("/^(?:13\d|14\d|15\d|18[0123456789])-?\d{5}(\d{3}|\*{3})$/", $mobile);
	}
	
	/**
	 * 验证输入的是否是电子邮件格式
	 */
	function isEmail($email) {
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
	
	/**
	 * 发送HTTP请求方法，目前只支持CURL发送请求
	 * @param  string $url    请求URL
	 * @param  array  $params 请求参数
	 * @param  string $method 请求方法GET/POST
	 * @return array  $data   响应数据
	 */
	function http($url, $params, $method = 'GET', $header = array(), $multi = false){
		$opts = array(
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER     => $header
		);
	
		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				throw new Exception('不支持的请求方式！');
		}
	
		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) throw new Exception('请求发生错误：' . $error);
		return  $data;
	}
	
	/**
	 * 从微信服务器下载多媒体文件的curl封装。
	 * @param string $url	url地址
	 * @param unknown $params	参数
	 * @return array	返回多媒体信息数组
	 */
	function downloadWeixinFile($url='', $params=array()){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$media = array_merge( array('header' => $httpinfo), array('body' => $package) );
		return $media;
	}
	
	/**
	 * 不转义中文字符和\/的 json 编码方法
	 * @param array $arr 待编码数组
	 * @return string
	 */
	function jsencode($arr) {
		$str = str_replace ( "\\/", "/", json_encode ( $arr ) );
		$search = "#\\\u([0-9a-f]+)#ie";
	
		if (strpos ( strtoupper(PHP_OS), 'WIN' ) === false) {
			$replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
		} else {
			$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
		}
	
		return preg_replace ( $search, $replace, $str );
	}
	
	// 数据保存到文件
	function data2file($filename, $arr=''){
		if(is_array($arr)){
			$con = var_export($arr,true);
			$con = "<?php\nreturn $con;\n?>";
		} else{
			$con = $arr;
			$con = "<?php\n $con;\n?>";
		}
		write_file($filename, $con);
	}
	
	/**
	 * 将standard obj转成array格式的函数
	 * @param standard obj $obj
	 */
	function objtoarr($obj){
		$ret = array();
		foreach($obj as $key =>$value){
			if(gettype($value) == 'array' || gettype($value) == 'object'){
				$ret[$key] = objtoarr($value);
			}
			else{
				$ret[$key] = $value;
			}
		}
		return $ret;
	}
	
	/**
	 * 系统加密方法
	 * @param string $data 要加密的字符串
	 * @param string $key  加密密钥
	 * @param int $expire  过期时间 单位 秒
	 * @return string
	 * @author winky
	 */
	function encrypt($data, $key = '', $expire = 0) {
		$key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
		$data = base64_encode($data);
		$x    = 0;
		$len  = strlen($data);
		$l    = strlen($key);
		$char = '';
	
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) $x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
	
		$str = sprintf('%010d', $expire ? $expire + time():0);
	
		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
		}
		return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
	}
	
	/**
	 * 系统解密方法
	 * @param  string $data 要解密的字符串 （必须是encrypt方法加密的字符串）
	 * @param  string $key  加密密钥
	 * @return string
	 * @author winky
	 */
	function decrypt($data, $key = ''){
		$key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
		$data   = str_replace(array('-','_'),array('+','/'),$data);
		$mod4   = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		$data   = base64_decode($data);
		$expire = substr($data,0,10);
		$data   = substr($data,10);
	
		if($expire > 0 && $expire < time()) {
			return '';
		}
		$x      = 0;
		$len    = strlen($data);
		$l      = strlen($key);
		$char   = $str = '';
	
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) $x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
	
		for ($i = 0; $i < $len; $i++) {
			if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
				$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
			}else{
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		return base64_decode($str);
	}
	
	function getTaskStatusStr($status = 0,$type = 'apply' , $company = ''){
		if ($type == 'comment') return '尚未作出评价';
		switch ($status) {
			case 0:
				return $type == 'apply' ? '已发出任务申请' : $company.'对你发出了任务邀请';
				break;
			case 1:
				return $type == 'apply' ? '企业已通过申请' : '已同意企业的邀请';
				break;
			case 2:
				return $type == 'apply' ? '企业已忽略你的申请' : '你已经忽略企业的邀请';
				break;
			case 3:
				return $type == 'apply' ? '你已完成该任务' : '该任务已经完成';
				break;
			default:
				return '未知的状态';
				break;
		}
	}
	
	function getArea($cache = true){
		$area = S ( 'S_Area' );
		if (empty ( $area ) || ! $cache) {
			// 缓存不存在，或者参数读取缓存。
			$areaModel = D('Area');
			$area = $areaModel -> where ('status = 3')->order ( 'sort,itemid' )->getField('itemid,title,pid,arrparentid,child');
			//把市的省拚出来
			foreach ($area as $k=>$v){
				//如果是顶级
				if ($v['pid']==0){
					$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
					$areaArr[$v['itemid']]['title'] = $v['title'];
					$areaArr[$v['itemid']]['pid'] = $v['pid'];
					$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
					$areaArr[$v['itemid']]['child'] = $v['child'];
					//上级
					$areaArr[$v['itemid']]['upitemid'] = $v['itemid'];
					$areaArr[$v['itemid']]['uptitle'] = $v['title'];
				}
				//查出上级的名称和ID
				else {
					$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
					$areaArr[$v['itemid']]['title'] = $v['title'];
					$areaArr[$v['itemid']]['pid'] = $v['pid'];
					$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
					$areaArr[$v['itemid']]['child'] = $v['child'];
					//上级
					$areaArr[$v['itemid']]['upitemid'] = $area[$v['pid']]['itemid'];
					$areaArr[$v['itemid']]['uptitle'] = $area[$v['pid']]['title'];
				}
			}
			$area = $areaArr;
			S ( 'S_Area' , $area );
		}
		return $area;
	}
	 
	function mkdirs($dir,$mode = 0777){
		if(is_dir($dir) || mkdir($dir,$mode)){
			return true;
		}
		if(!mkdirs(dirname($dir),$mode)){
			return false;
		}
		return mkdir($dir,$mode);
	}
	
	/**
	 * 组装图片路径函数。
	 * @param string $originalpath	形参传入要处理的图片路径
	 * @param boolean $realpath		是否需要全路径，默认为false
	 * @return string				返回组装完成的路径
	 */
	function assemblepath($originalpath = '', $realpath = FALSE){
		$finalpath = $originalpath;
		$http = "ttp://"; // http文件头
		$https = "ttps://"; // https文件头
		if (! empty ( $originalpath ) && ! strpos ( $originalpath, $http ) && ! strpos ( $originalpath, $https )) {
			$project_name = C ( 'PROJECT_NAME' );
			if (! strstr ( $originalpath, $project_name )) {
				if ($realpath) {
					//$finalpath = C ( 'SITE_URL' ). $project_name . $finalpath;	//没找到项目名称，拼接全路径+项目名称
                    $finalpath = C ( 'DOMAIN' ).$finalpath;
				} else {
					//$finalpath = '/' . $project_name . $finalpath;				//没找到项目名称，拼接项目名称
                    $finalpath = $finalpath;
				}
			} else {
				if ($realpath) $finalpath = C ( 'DOMAIN' ) . $finalpath;			//已经找到项目名称，拼接全路径（$finalpath里已经包含了/weact，有斜杠）
			}
		}
		return $finalpath;
	}
	
	/*--------------------------以下为ThinkPHP提供的微信SDK-------添加时间：20140606，版本号：version 747------------------------*/
	
	/**
	 * 该函数仅限于从微信平台根据openid同步用户add和exist和del的，判别用户信息相同与否请用autoPick。
	 * 用哈希键值分拣用户函数。
	 * 远程数据$remotedata，$remotedata [$i]就是一个字符串：openid的值，
	 * 本地数据$localdata， $localdata [$i]就是本地wechatuserinfo表的一条用户数据。
	 * @param array $remotedata 微信服务器返回数据
	 * @param array $localdata 本地用户数据
	 * @param string $hashkey 检索的哈希值
	 * @return array $autopickresult 自动分拣的结果
	 */
	function hashUserPick($remotedata, $localdata, $hashkey = ''){
		$add = array (); 												// 要新增的数组
		$exist = array (); 												// 已存在的用户
		$del = array (); 												// 要删除的数据数组
		$a = 0; 														// 循环变量a，用于add数组循环
		$e = 0; 														// 循环变量e，用于exist数组循环
		$d = 0; 														// 循环变量d，用于del数组循环
	
		// Step1：正散列找add用户，同时找出add和exist的用户
		$dbformat = array();
		for($i = 0; $i < count( $localdata ); $i ++){
			$dbformat [ $localdata [$i] [$hashkey] ] = $localdata [$i];	//本地格式化数组$dbformat的每个openid索引键值下存的是一条本地数据库wechatuserinfo表里的一位数组用户数据
		}
		for($j = 0; $j < count ( $remotedata ); $j ++) {
			if($dbformat [ $remotedata [$j] ]) {
				$exist [$e ++] = $remotedata [$j];
			} else{
				$add [$a ++] = $remotedata [$j];
			}
		}
		
		// Step2：反散列找del数组的用户
		$rmformat = array();
		for($i = 0; $i < count( $remotedata ); $i ++){
			$rmformat [ $remotedata [$i] ] = $remotedata [$i];			//远程格式化数组$rmformat的每个openid索引键值下存的是一条openid字符串
		}
		for($j = 0; $j < count ( $localdata ); $j ++) {
			if(! $rmformat [ $localdata [$j] [$hashkey] ]) $del [$d ++] = $localdata [$j];
		}
		$hashpickresult = array (
				'add' => $add,
				'addcount' => $a,
				'exist' => $exist,
				'existcount' => $e,
				'del' => $del,
				'delcount' => $d
		);
		return $hashpickresult;
	}
	
	/**
	 * 自动分拣函数，形参传入远程数据和本地数据，返回分拣后的数据。
	 * 按索引键值$identifykey散列分拣，返回比对的结果origin,add,update,del，
	 * 丢入几个字段，就几个字段一起比对，按对象比对的方式。
	 * @param array $remotedata 远程数据
	 * @param array $localdata 本地数据
	 * @param string $identifykey 主键字段
	 * @return array $autopickresult 自动分拣数据
	 */
	function autoPick($remotedata, $localdata, $identifykey) {
		$origin = array (); 										// 没有任何变动的数组
		$add = array (); 											// 要新增的数组
		$update = array (); 										// 要更新值的数组
		$delflag = array();											// 删除标记数组
		$del = array (); 											// 要删除的数据数组
		$o = 0; 													// 循环变量o，用于origin数组循环
		$a = 0; 													// 循环变量a，用于add数组循环
		$u = 0; 													// 循环变量u，用于update数组循环
		$d = 0; 													// 循环变量d，用于del数组循环
		// Step1：本地数据删除标记预处理
		for($i = 0; $i < count ( $localdata ); $i ++) {
			$delflag [$i] ['tobedeleted'] = 1; 						// 默认所有本地记录都要被删除
		}
		// Step2：循环匹配找
		for($i = 0; $i < count ( $remotedata ); $i ++) {
			$existflag = false; 									// 比对主键字段是否存在标记，区分update/origin和add的标记
			for($j = 0; $j < count ( $localdata ); $j ++) {
				if ($remotedata [$i] [$identifykey] == $localdata [$j] [$identifykey]) {
					$delflag [$j] ['tobedeleted'] = 0; 				// 找到该记录了，该记录不用被删除，标记置为0
					if ($remotedata [$i] == $localdata [$j]) {
						$origin [$o ++] = $remotedata [$i];
					} else {
						$update [$u ++] = $remotedata [$i];
					}
					$existflag = true; 								// 原记录有该主键标记置为true
					break; 											// 终止内层循环
				}
			}
			if (! $existflag) $add [$a ++] = $remotedata [$i];		//本地没有该数据，需要新增
		}
		// Step3：找本地需要删除的数据
		for($i = 0; $i < count ( $localdata ); $i ++) {
			if ($delflag [$i] ['tobedeleted'] == 1) $del [$d ++] = $localdata [$i];
		}
		$autopickresult = array (
				'origin' => $origin,
				'add' => $add,
				'update' => $update,
				'del' => $del
		);
		return $autopickresult;
	}
	
	/**
	 * 整型转完整日期格式
	 * @param number $time	格林尼治time()时间戳
	 * @param boolean $withouthms	不需要时分秒标志
	 * @return string	返回格式化的日期类型
	 */
	function timetodate($time = 0, $withouthms = FALSE) {
		if($withouthms) {
			return date("Y-m-d", $time);
		}else {
			return date("Y-m-d H:i:s", $time);
		}
	}
	
	/**
	 * 格式化成微信支付需要的时间格式yyyyMMddHHmmss。
	 * @param number $timenow
	 * @return string $wechatpaydate
	 */
	function formatwechatpaydate($timenow = 0) {
		$wechatpaydate = '';
		if(! empty ( $timenow )) {
			$year = date('Y', $timenow);
			$month = date('m', $timenow);
			$day = date('d', $timenow);
			$hour = date('H', $timenow);
			$minute = date('i', $timenow);
			$second = date('s', $timenow);
			$wechatpaydate = '' . $year . $month . $day. $hour. $minute. $second;
		}
		return $wechatpaydate;
	}
	
	/**
	 * 返回今天开始时间戳
	 */
	function todaystart() {
		return mktime( 0, 0, 0, date('m'), date('d'), date('Y') );
	}
	
	/**
	 * 返回今天结束时间戳
	 */
	function todayend() {
		return mktime( 23, 59, 59, date('m'), date('d'), date('Y') );
	}
	
	/**
	 * 扩展PHP5.2.6版本，使其能从字符串解析出csv格式数组。
	 * @param unknown $input
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape
	 * @param string $eol
	 * @return multitype:multitype: |boolean
	 */
//	function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n') {
//		if (is_string($input) && !empty($input)) {
//			$output = array();
//			$tmp    = preg_split("/".$eol."/",$input);
//			if (is_array($tmp) && !empty($tmp)) {
//				while (list($line_num, $line) = each($tmp)) {
//					if (preg_match("/".$escape.$enclosure."/",$line)) {
//						while ($strlen = strlen($line)) {
//							$pos_delimiter       = strpos($line,$delimiter);
//							$pos_enclosure_start = strpos($line,$enclosure);
//							if (
//									is_int($pos_delimiter) && is_int($pos_enclosure_start)
//									&& ($pos_enclosure_start < $pos_delimiter)
//							) {
//								$enclosed_str = substr($line,1);
//								$pos_enclosure_end = strpos($enclosed_str,$enclosure);
//								$enclosed_str = substr($enclosed_str,0,$pos_enclosure_end);
//								$output[$line_num][] = $enclosed_str;
//								$offset = $pos_enclosure_end+3;
//							} else {
//								if (empty($pos_delimiter) && empty($pos_enclosure_start)) {
//									$output[$line_num][] = substr($line,0);
//									$offset = strlen($line);
//								} else {
//									$output[$line_num][] = substr($line,0,$pos_delimiter);
//									$offset = (
//											!empty($pos_enclosure_start)
//											&& ($pos_enclosure_start < $pos_delimiter)
//									)
//									?$pos_enclosure_start
//									:$pos_delimiter+1;
//								}
//							}
//							$line = substr($line,$offset);
//						}
//					} else {
//						$line = preg_split("/".$delimiter."/",$line);
//	
//						/*
//						 * Validating against pesky extra line breaks creating false rows.
//						*/
//						if (is_array($line) && !empty($line[0])) {
//							$output[$line_num] = $line;
//						}
//					}
//				}
//				return $output;
//			} else {
//				return false;
//			}
//		} else {
//			return false;
//		}
//	}
	
	/**
	 * 调试专用函数，记录信息到日志。
	 * @param string|array $loginfo 要记录的日志信息
	 */
	function debugLog($loginfo = NULL) {
		$filepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . "/WeChatLog/globaldebug/"; 	// 全局dubug文件夹
		$filename = "debug" . date ( "Ymd" ) . ".log"; 									// 文件名按天存放
		globalLog ( $filepath, $filename, json_encode ( $loginfo ) ); // 记录文件信息
	}
	
	/**
	 * 全局打印日志文件函数。
	 * CreateTime:2015/08/30 17:33:25.
	 * @author shinnlove
	 * @param string $filefolder 日志文件存放的文件夹名
	 * @param string $filename 日志文件存档的文件名
	 * @param string $loginfo 日志文件需要记录的信息
	 */
	function globalLog($filefolder = "", $filename = "", $loginfo = NULL) {
		$logsuccess = false; // 记录日志文件失败
		if (! empty ( $filefolder ) && ! empty ( $filename )) {
			// 如果文件夹路径和文件名都不空，则记录日志文件
			if (! is_dir ( $filefolder ) ) mkdirs ( $filefolder ); // 如果没有存在文件夹，直接创建文件夹
			$fp = fopen ( $filefolder . $filename, "a" ); // 所有权限打开这个日志文件，文件夹路径+文件名
			flock ( $fp, LOCK_EX ); 	// 锁定文件读写权限
			fwrite ( $fp, "全局日志记录时间：" . strftime ( "%Y-%m-%d %H:%M:%S", time () ) . "\n" . $loginfo . "\n\n" ); // 记录日志信息
			flock ( $fp, LOCK_UN ); 	// 解锁文件读写权限
			fclose ( $fp ); 			// 关闭文件句柄
			$logsuccess = true; 		// 到此日志文件记录成功
		}
		return $logsuccess;
	}
	
	/**
	 * 获取毫秒时间
	 * @return number
	 */
	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		$currentsecond = (float)$usec + (float)$sec;
		return $currentsecond;
	}
	
	/**
	 * 获取毫秒时间
	 * @return number
	 */
	function microtime_double() {
		list($usec, $sec) = explode(" ", microtime());
		$currentsecond = (double)$usec + (double)$sec;
		return $currentsecond;
	}
	
?>