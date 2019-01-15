<?php
/**
 * 插件管理ajax请求处理控制器。
 * @author Shinnlove
 *
 */
class PluginManageRequestAction extends PCRequestLoginAction {
	
	//添加大转盘活动处理函数
	public function addLuckyWheelConfirm(){
		//Step1：处理大转盘活动主信息
		$data = array(
				'plugin_activity_id' => md5(uniqid(rand(), true)),
				'plugin_activity_title' => I('activity_title'),
				//'plugin_id' => '0012',								//从微动平台提供的插件里查出是趣味刮刮卡插件的编号，代码已经在下边写好↓
				'plugin_name' => '幸运大转盘',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'plugin_activity_brief' => I('activity_brief'),
				'plugin_activity_detail' => I('activity_detail'),
				'participation' => I('participation'),
				'total_times' => I('total_times'),
				'add_activity_time' => I('startdate'),
				'plugin_activity_startdate' => I('startdate'),
				'plugin_activity_enddate' => I('enddate'),
				'activity_endtitle' => I('activity_endtitle'),
				'activity_endinfo' => I('activity_endinfo'),
				'keyword' => I('keyword'),
				'getprize_tip' => I('getprize_info'),
				'repeatplay_tip' => I('repeatplay_tip'),
				'hide_amount' => I('hide_amount'),					//接收不显示奖品框是否选中
				'everyday_permission' => I('play_everyday'),		//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),				//接收是否关注即弹出框是否选中
		);
	
		//Step1-0：查询大转盘插件编号
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'幸运大转盘\'')->find();
		$data['plugin_id'] = $psresult['plugin_id'];			//将微动提供的趣味刮刮卡插件编号查询出来给到data的plugin_id字段值中
	
		//Step1-1：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
		//Step2：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';				//特别注意：在添加奖品的时候就对奖品加上排序，方便日后编辑处理。
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step4：先插入活动数据，继而更新活动图片
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->data($data)->add();
	
		//Step5：插入奖品信息
		//缩写：pluginprize→pp
		$pptable = M('pluginprize');
		for($j=0; $j<$i; $j++){
			$finalprize[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));			//随机奖品的主键
			$finalprize[$j]['sncode'] = md5(uniqid(rand(), true));					//随机奖品的sncode码
			$finalprize[$j]['plugin_activity_id'] = $data['plugin_activity_id'];	//奖品的外键
			$finalprize[$j]['getprize_amount'] = 0;									//默认初始中奖数0
			$pptable->data($finalprize[$j])->add();									//特别注意：不要漏掉$j
		}
	
		//Step6：处理活动开始、结束图片信息，导入上传类
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->upLA ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPathLA方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPathLA ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPathLA ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPathLA ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}else{											//两幅都没有自定义活动开始图片上传，则用系统默认的图片
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-start.jpg';
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-end.jpg';
			if($patable->save ( $data ))$this->redirect ( 'Admin/PluginManage/luckyWheel' );
			else{
				$this->error ( '添加活动失败！' );
			}
		}
	
		//结论：特别注意，将数据分别存入两张表的过程，如果以后会使用事物过程，最好采用事物来操作
	}
	
	//图片上传函数upLA
	private function upLA($activityinfo) {
		// 完成与thinkphp相关的，文件上传类的调用
		import ( 'ORG.Net.UploadFile' ); 	// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile ();
		$upload->maxSize = '1000000'; 		// 默认为-1，不限制上传大小
		$upload->savePath = 'Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/plugin/luckyWheel/' . $activityinfo['plugin_activity_id'] . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存
		$upload->saveRule = uniqid; 		// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 		// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'jpg', 'jpeg', 'png', 'gif' ); // 准许上传的文件类型
		$upload->allowTypes = array ( 'image/png', 'image/jpg', 'image/jpeg', 'image/gif' ); // 检测mime类型
		$upload->thumb = true; 				// 是否开启图片文件缩略图
		$upload->thumbMaxWidth = '200,100';
		$upload->thumbMaxHeight = '200,100';
		//目录如果没有，会自动创建文件夹的！——2014/06/12
		if(!file_exists($upload->savePath)){
			mkdir($upload->savePath);
		}
		if ($upload->upload ()) {
			$info = $upload->getUploadFileInfo ();
			return $info;
		} else {
			$this->error ( $upload->getErrorMsg () ); // 专门用来获取上传的错误信息的
		}
	}
	
	//插入图片路径函数insertImgPathLA
	private function insertImgPathLA($upinfos, $activityinfo, $numflag) {
		//缩写：$activitystartimage→asi
		$asitable = M ( 'pluginactivity' );
		$data = array();
		if($numflag==2){
			for($i = 0; $i < count ( $upinfos ); $i++) {
				$savepath = $upinfos [$i] ['savepath'];
				if($i==0){
					$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
				if($i==1){
					$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
			}
		}else if($numflag==0){		//只有第一幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-end.jpg';
		}else if($numflag==1){		//只有第二幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-start.jpg';
			$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
		}
		$data['plugin_activity_id'] = $activityinfo['plugin_activity_id'];
		if ($asitable->where($update)->save ($data)) {
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 修改大转盘活动处理函数
	 */
	public function editLuckyWheelConfirm(){
		//Step1-1：处理刮刮卡活动主信息，准备进入编辑修改阶段
		$data = array(
				'plugin_activity_id' => I('activity_id'),					//接收编辑活动的编号
				'plugin_activity_title' => I('activity_title'),				//接收活动标题
				'plugin_activity_brief' => I('activity_brief'),				//接收活动简介
				'plugin_activity_detail' => I('activity_detail'),			//接收活动详情
				'participation' => I('participation'),						//接收参与人数
				'total_times' => I('total_times'),							//接收总共可玩次数
				'latest_modify_time' => date('YmdHms'),						//最后一次修改时间是当前
				'plugin_activity_startdate' => I('startdate'),				//接收活动起始时间
				'plugin_activity_enddate' => I('enddate'),					//接收活动结束时间
				'activity_endtitle' => I('activity_endtitle'),				//接收活动结束标题
				'activity_endinfo' => I('activity_endinfo'),				//接收活动结束信息
				'keyword' => I('keyword'),									//接收活动关键字
				'getprize_tip' => I('getprize_info'),						//接收活动兑奖信息
				'repeatplay_tip' => I('repeatplay_tip'),					//接收重复抽奖提醒
				'hide_amount' => I('hide_amount'),							//接收不显示奖品框是否选中
				'everyday_permission' => I('play_everyday'),				//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),						//接收是否关注即弹出框是否选中
		);
	
		//Step1-2：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
	
		//Step2-1：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step3：根据plugin_activity_id主键更新pluginactivity的信息
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->save($data);				//特别注意：此$data有主键，所以直接可以使用save($data)。
	
		//Step4-1：先把原来奖品查询出来，放到修改记录表中，此步骤为了避免商户和顾客产生中奖纠纷。
		//缩写：pluginprize→pp，previouspluginprize→ppp，pluginprizemodify→ppm
		$pppmap = array( 'plugin_activity_id' => $data['plugin_activity_id'] );
		$pptable = M('pluginprize');
		$previouspp = $pptable->where($pppmap)->order('prize_order')->select();		//查询出原来的奖品信息$previouspp
	
		$ppmtable = M('pluginprizemodify');
		$pppdata = array();
		for($k=0; $k<count($previouspp); $k++){
			$pppdata[$k]['prize_modify_id'] = md5(uniqid(rand(), true));			//生成修改奖品主键
			$pppdata[$k]['plugin_prize_id'] = $previouspp[$k]['plugin_prize_id'];
			$pppdata[$k]['sncode'] = $previouspp[$k]['sncode'];
			$pppdata[$k]['plugin_activity_id'] = $previouspp[$k]['plugin_activity_id'];
			$pppdata[$k]['prize_order'] = $previouspp[$k]['prize_order'];			//奖品排序，方便接下来操作
			$pppdata[$k]['prize_name'] = $previouspp[$k]['prize_name'];
			$pppdata[$k]['prize_content'] = $previouspp[$k]['prize_content'];
			$pppdata[$k]['prize_amount'] = $previouspp[$k]['prize_amount'];
			$pppdata[$k]['prize_deadline'] = $previouspp[$k]['prize_deadline'];
			$pppdata[$k]['modify_time'] = date('YmdHms');							//修改奖品时间
			$pppdata[$k]['remark'] = $previouspp[$k]['remark'];
			$pppdata[$k]['is_del'] = $previouspp[$k]['is_del'];
			$ppmtable->data($pppdata[$k])->add();									//向pluginprizemodify表中添加修改记录
		}
	
		//Step4-2：更新原来的奖品信息，要更新的奖品信息是$updatepp
		//特别注意$i是本次编辑后提交过来的奖品数目，$i有两种情况：小于等于原来的奖品数；大于原来的奖品数（原来奖品数：count($previouspp)）
		for($j=0; $j<$i; $j++){
			//本次提交奖品数是$i，for循环一直循环下去，不论是比原来奖品多少，直到停止，试想：如果本次奖品数目小于原来，for循环根本走不到else部分。
			if($j<count($previouspp)){
				//如果循环的次数是小于或等于原来奖品数部分，则采用在原来的奖品上修改处理，注意$j从0开始，所以此处不取等号！
				$updatepp[$j]['plugin_prize_id'] = $previouspp[$j]['plugin_prize_id'];		//原来奖品的主键
				$updatepp[$j]['sncode'] = $previouspp[$j]['sncode'];						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $previouspp[$j]['plugin_activity_id'];//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的最新名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的最新内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的最新数量
	
				$updatepp[$j]['getprize_amount'] = $previouspp[$j]['getprize_amount'];		//默认初始中奖数0
				$pptable->save($updatepp[$j]);												//特别注意：不要漏掉$j
			}else{
				//如果循环的次数是大于原来奖品数部分，则大于部分采用插入处理。
				$updatepp[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));				//随机奖品的主键
				$updatepp[$j]['sncode'] = md5(uniqid(rand(), true));						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $data['plugin_activity_id'];			//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的数量
	
				$updatepp[$j]['getprize_amount'] = 0;										//默认初始中奖数0
				$pptable->data($updatepp[$j])->add();										//特别注意：不要漏掉$j
			}
		}
		//如果本次编辑奖品有删除一些奖品，则进行剪切
		if($i<count($previouspp)){
			$conditionmap['plugin_activity_id'] = $data['plugin_activity_id'];				//设定要删除的条件
			$tempcount = count($previouspp) - $i;
			for($kkk=0;$kkk<$tempcount;$kkk++){
				$pptable->where($conditionmap)->order('prize_order desc')->limit(1)->setField('is_del', 1) ;		//降序排列后，从尾部删除多余的奖品，注意添加奖品的时候加上排序
			}
		}
	
		//Step5：更新本次活动主信息的图片（如果有改动的话才更新）20140618貌似这个图片更新还是有点小问题
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->upLA ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPathLA方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPathLA ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPathLA ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPathLA ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/luckyWheel' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}
	}
	
	/**
	 * 读取商家添加的大转盘信息，缩写：luckyWheelActivity→la
	 */
	public function getCurrentLA() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
	
		//缩写：luckywheelActivity→la
		$lamap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id']	//取当前商家编号
				//'is_del' => 0									//此字段已经不需要限制，因为is_del=0显示进行中，is_del=1显示活动已经过期。
		);
	
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');						//查询微动平台查件表
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'幸运大转盘\'')->find();	//寻找幸运大转盘插件的编号
		$lamap['plugin_id'] = $psresult['plugin_id'];		//将微动提供的趣味刮刮卡插件编号查询出来给到data的plugin_id字段值中
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加大转盘时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($lamap)->count(); 			//计算满足条件的大转盘活动总数
	
		//缩写：luckywheelActivity→la
		$lalist = array();
		$lalist = $patable->where ( $lamap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $lalist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/*
	 * 动态加载每条活动的详情和奖品信息，点击加号展开详情信息请求getDetailLA
	 * */
	public function getDetailLA(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
	
		//缩写：prizedetail→pd，详情表中已经有外键，无需e_id了
		$pdmap = array(
				'plugin_activity_id' => I('cpaid')				//当前查看活动的编号
		);
		$pdtable = M('pluginprize');
		$pdresult = $pdtable->where($pdmap)->order('prize_order')->select();	//根据活动编号查询奖品信息
		$this->ajaxReturn($pdresult, 'json');				//用ajax返回结构体给前台，数据格式：json（一维数组）
	}
	
	/**
	 * 删除某个大转盘活动函数delLA
	 */
	public function delLA(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
		//接收删除信息
		$delmap = array(
				'plugin_activity_id' => I('cpaid'),				//删除哪个活动
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//哪个商家的
				'is_del' => 0
		);
		//实例化表开始删除
		$deltable = M('pluginactivity');
		$delresult = $deltable->where($delmap)->setField('is_del', 1);	//设置is_del字段为1，代表删除
		if($delresult){
			$this->ajaxReturn( array('status' => '1'), 'json');			//成功返回状态status=1
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');			//失败返回状态status=0
		}
	}
	
	/**
	 * 自定义条件和内容查询大转盘函数searchLuckyWheel
	 */
	public function searchLuckyWheel(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
	
		$data = array (
				'searchcondition' => I('searchcondition'),				//查询条件是哪个字段
				'searchcontent'	=> I('searchcontent')					//查询的内容
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//定义要查询的条件$condition
		$condition = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],			//查询哪个店家
				'plugin_name' => '幸运大转盘',							//要搜索的插件名称是幸运大转盘
				$data['searchcondition'] => array('like','%'.$data['searchcontent'].'%')		//要查询的字段模糊匹配这个值
				//'is_del' => 0											//没有被删除的（此处注释，因为不管是0还是1，都有代表意义，1代表活动终止的）
		);
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($condition)->count(); 			//计算满足条件的大转盘活动总数
	
		//缩写：luckywheelActivity→la
		$lalist = array();
		$json = null;
		$lalist = $patable->where ( $condition )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if($lalist != null){
			$json = '{"total":' . $total . ',"rows":' . json_encode ( $lalist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		}else{
			$json = json_encode (array());					//如果没有查询到任何东西，要输出空数组，告诉商家没查询到数据
		}
		echo $json;
	}
	
	/**
	 * 检查当前有无幸运大转盘活动进行中函数checkCurrentLA
	 */
	public function checkCurrentLA(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array( 'check' => I('checkstatus') );
		$checkmap = array(
				'plugin_name' => '幸运大转盘',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		//缩写pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->where($checkmap)->order('add_activity_time desc')->limit(1)->select();
		if($paresult){
			$currentdate = date ( "Y-m-d H:i:s" );
			//特别注意$paresult是一个二维数组，带上0下标再取其字段！
			if( $currentdate >= $paresult[0]['plugin_activity_startdate'] && $currentdate <= $paresult[0]['plugin_activity_enddate'] ){
				$this->ajaxReturn( array('status' => '1'), 'json');
			}else{
				$this->ajaxReturn( array('status' => '0'), 'json');
			}
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');
		}
	}
	
	//显示当前大转盘获奖信息的函数
	public function currentPrizeInfoLA(){
		$data = array(
				'plugin_activity_id' => I('paid')
		);
	
		//做视图去查询奖品信息、中奖人信息、和中奖纪录，三表联查
		$sql = 'tu.customer_id = tc.customer_id AND tu.plugin_prize_id = tp.plugin_prize_id AND tu.plugin_activity_id = \''.$data['plugin_activity_id'].'\' AND tu.is_del = 0 AND tc.is_del = 0 AND tp.is_del = 0';
		$model = new Model();														//创建视图查询器
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/												//导入分页控件
		$total = $model->table('t_userpluginprize tu, t_customerinfo tc, t_pluginprize tp')
		->where($sql)
		->field('*')
		->count();																		//计算顾客总收藏数
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'getprize_id';			//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';				//进行一个降序排列（最近添加的显示在最前边）
	
		//缩写：luckywheelActivity→la
		$upplist = array();
		$json = null;
	
		$upplist = $model->table('t_userpluginprize tu, t_customerinfo tc, t_pluginprize tp')
		->where($sql)
		->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )
		->order ( ''.$sort.' '.$order )
		->field('*')
		->select();
	
		if($upplist != null){
			$json = '{"total":' . $total . ',"rows":' . json_encode ( $upplist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		}else{
			$json = json_encode (array());					//如果没有查询到任何东西，要输出空数组，告诉商家没查询到数据
		}
		echo $json;
	
	}
	
	//大转盘活动商户确认发放奖品处理函数sendPrizeConfirmLA()，AJAX提交，AJAX返回。
	public function sendPrizeConfirmLA(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array(
				'getprize_id' => I('gpid')
		);
		//缩写：userpluginprize→upp
		$upptable = M('userpluginprize');
		$upprs = $upptable->where($data)->setField('send_status', 1);
		if($upprs){
			$this->ajaxReturn( array('status' => 1, 'msg' => '已经将奖品成功发放给客户！') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '请检查网络状况，并稍后重试！') );
		}
	}
	
	//大转盘活动商户确认删除中奖纪录处理函数delPrizeConfirmLA()，AJAX提交，AJAX返回。
	public function delPrizeConfirmLA(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array(
				'getprize_id' => I('gpid')
		);
		//缩写：userpluginprize→upp
		$upptable = M('userpluginprize');
		$upprs = $upptable->where($data)->setField('is_del', 1);
		if($upprs){
			$this->ajaxReturn( array('status' => 1, 'msg' => '删除当前中奖纪录成功！') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '请检查网络状况，并稍后重试！') );
		}
	}
	
	





	/*-----------------------------------↓以下开始是趣味刮刮卡后台的代码↓----------------------------------*/
	
	
	//刮刮卡一览表页面
	public function scratchCard(){
		$this->display();
	}
	
	//添加、编辑刮刮卡活动页面
	public function addScratchCard(){
		$data = array(
				'plugin_activity_id' => I('plugin_activity_id')			//试着接收传来的插件活动编号
		);
		if($data['plugin_activity_id']=='') {						//如果没有接收到编号，则是新添加活动，直接让用户添加活动；否则是编辑活动，如下↓
			$this->editflag = 0;									//推送编辑标记：0为false
			$this->display();
		}else{
			//缩写：editPluginActivity→epa
			$epamap = array(
					'plugin_activity_id' => $data['plugin_activity_id'],//当前活动编号
					'is_del' => 0
			);
			//做视图拼接，pluginactivity表是主表，别名p(parent)；pluginprize表是子表，别名c(child)。
			$sql = 'p.plugin_activity_id = c.plugin_activity_id and p.is_del = 0 and c.is_del = 0 and p.plugin_activity_id = \''.$epamap['plugin_activity_id'].'\'';
			$model = new Model();
			$eparesult =  $model->table('t_pluginactivity p, t_pluginprize c')->where ( $sql )->order('c.prize_order')->field('p.*, c.*')->select();	//查出活动
			$this->editflag = 1;									//推送编辑标记：1为true，前台判断
			$this->epainfo = $eparesult;							//推送活动信息给前台，前台根据情况选择
			$this->display();										//展示页面
		}
	}
	
	//添加刮刮卡活动处理函数
	public function addScratchCardConfirm(){
		//Step1：处理刮刮卡活动主信息
		$data = array(
				'plugin_activity_id' => md5(uniqid(rand(), true)),
				'plugin_activity_title' => I('activity_title'),
				//'plugin_id' => '0012',								//从微动平台提供的插件里查出是趣味刮刮卡插件的编号，代码已经在下边写好↓
				'plugin_name' => '趣味刮刮卡',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'plugin_activity_brief' => I('activity_brief'),
				'plugin_activity_detail' => I('activity_detail'),
				'participation' => I('participation'),
				'total_times' => I('total_times'),
				'add_activity_time' => I('startdate'),
				'plugin_activity_startdate' => I('startdate'),
				'plugin_activity_enddate' => I('enddate'),
				'activity_endtitle' => I('activity_endtitle'),
				'activity_endinfo' => I('activity_endinfo'),
				'keyword' => I('keyword'),
				'getprize_tip' => I('getprize_info'),
				'repeatplay_tip' => I('repeatplay_tip'),
				'hide_amount' => I('hide_amount'),					//接收不显示奖品框是否选中
				'everyday_permission' => I('play_everyday'),		//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),				//接收是否关注即弹出框是否选中
		);
	
		//Step1-0：查询刮刮卡插件编号
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'趣味刮刮卡\'')->find();
		$data['plugin_id'] = $psresult['plugin_id'];			//将微动提供的趣味刮刮卡插件编号查询出来给到data的plugin_id字段值中
	
		//Step1-1：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
		//Step2：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';				//特别注意：在添加奖品的时候就对奖品加上排序，方便日后编辑处理。
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step4：先插入活动数据，继而更新活动图片
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->data($data)->add();
	
		//Step5：插入奖品信息
		//缩写：pluginprize→pp
		$pptable = M('pluginprize');
		for($j=0; $j<$i; $j++){
			$finalprize[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));			//随机奖品的主键
			$finalprize[$j]['sncode'] = md5(uniqid(rand(), true));					//随机奖品的sncode码
			$finalprize[$j]['plugin_activity_id'] = $data['plugin_activity_id'];	//奖品的外键
			$finalprize[$j]['getprize_amount'] = 0;									//默认初始中奖数0
			$pptable->data($finalprize[$j])->add();									//特别注意：不要漏掉$j
		}
	
		//Step6：处理活动开始、结束图片信息，导入上传类
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->up ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPath方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPath ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPath ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPath ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}else{											//两幅都没有自定义活动开始图片上传，则用系统默认的图片
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/scratchcard/activity-scratch-card-start.jpg';
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/scratchcard/activity-scratch-card-end.jpg';
			if($patable->save ( $data ))$this->redirect ( 'Admin/PluginManage/scratchCard' );
			else{
				$this->error ( '添加活动失败！' );
			}
		}
	
		//结论：特别注意，将数据分别存入两张表的过程，如果以后会使用事物过程，最好采用事物来操作
	}
	
	//图片上传函数up
	private function up($activityinfo) {
		// 完成与thinkphp相关的，文件上传类的调用
		import ( 'ORG.Net.UploadFile' ); 	// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile ();
		$upload->maxSize = '1000000'; 		// 默认为-1，不限制上传大小
		$upload->savePath = 'Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/plugin/scratchCard/' . $activityinfo['plugin_activity_id'] . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存
		$upload->saveRule = uniqid; 		// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 		// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'jpg', 'jpeg', 'png', 'gif' ); // 准许上传的文件类型
		$upload->allowTypes = array ( 'image/png', 'image/jpg', 'image/jpeg', 'image/gif' ); // 检测mime类型
		$upload->thumb = true; 				// 是否开启图片文件缩略图
		$upload->thumbMaxWidth = '200,100';
		$upload->thumbMaxHeight = '200,100';
		//目录如果没有，会自动创建文件夹的！——2014/06/12
		if(!file_exists($upload->savePath)){
			mkdir($upload->savePath);
		}
		if ($upload->upload ()) {
			$info = $upload->getUploadFileInfo ();
			return $info;
		} else {
			$this->error ( $upload->getErrorMsg () ); // 专门用来获取上传的错误信息的
		}
	}
	
	//插入图片路径函数insertImgPath
	private function insertImgPath($upinfos, $activityinfo, $numflag) {
		//缩写：$activitystartimage→asi
		$asitable = M ( 'pluginactivity' );
		$data = array();
		if($numflag==2){
			for($i = 0; $i < count ( $upinfos ); $i++) {
				$savepath = $upinfos [$i] ['savepath'];
				if($i==0){
					$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
				if($i==1){
					$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
			}
		}else if($numflag==0){		//只有第一幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/scratchcard/activity-scratch-card-end.jpg';
		}else if($numflag==1){		//只有第二幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/scratchcard/activity-scratch-card-start.jpg';
			$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
		}
		$data['plugin_activity_id'] = $activityinfo['plugin_activity_id'];
		if ($asitable->where($update)->save ($data)) {
			return true;
		}else{
			return false;
		}
	}
	
	//修改刮刮卡活动处理函数
	public function editScratchCardConfirm(){
		//Step1-1：处理刮刮卡活动主信息，准备进入编辑修改阶段
		$data = array(
				'plugin_activity_id' => I('activity_id'),					//接收编辑活动的编号
				'plugin_activity_title' => I('activity_title'),				//接收活动标题
				'plugin_activity_brief' => I('activity_brief'),				//接收活动简介
				'plugin_activity_detail' => I('activity_detail'),			//接收活动详情
				'participation' => I('participation'),						//接收参与人数
				'total_times' => I('total_times'),							//接收总共可玩次数
				'latest_modify_time' => date('YmdHms'),						//最后一次修改时间是当前
				'plugin_activity_startdate' => I('startdate'),				//接收活动起始时间
				'plugin_activity_enddate' => I('enddate'),					//接收活动结束时间
				'activity_endtitle' => I('activity_endtitle'),				//接收活动结束标题
				'activity_endinfo' => I('activity_endinfo'),				//接收活动结束信息
				'keyword' => I('keyword'),									//接收活动关键字
				'getprize_tip' => I('getprize_info'),						//接收活动兑奖信息
				'repeatplay_tip' => I('repeatplay_tip'),					//接收重复抽奖提醒
				'hide_amount' => I('hide_amount'),							//接收不显示奖品框是否选中
				'everyday_permission' => I('play_everyday'),				//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),						//接收是否关注即弹出框是否选中
		);
	
		//Step1-2：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
	
		//Step2-1：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step3：根据plugin_activity_id主键更新pluginactivity的信息
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->save($data);				//特别注意：此$data有主键，所以直接可以使用save($data)。
	
		//Step4-1：先把原来奖品查询出来，放到修改记录表中，此步骤为了避免商户和顾客产生中奖纠纷。
		//缩写：pluginprize→pp，previouspluginprize→ppp，pluginprizemodify→ppm
		$pppmap = array( 'plugin_activity_id' => $data['plugin_activity_id'] );
		$pptable = M('pluginprize');
		$previouspp = $pptable->where($pppmap)->order('prize_order')->select();		//查询出原来的奖品信息$previouspp
	
		$ppmtable = M('pluginprizemodify');
		$pppdata = array();
		for($k=0; $k<count($previouspp); $k++){
			$pppdata[$k]['prize_modify_id'] = md5(uniqid(rand(), true));			//生成修改奖品主键
			$pppdata[$k]['plugin_prize_id'] = $previouspp[$k]['plugin_prize_id'];
			$pppdata[$k]['sncode'] = $previouspp[$k]['sncode'];
			$pppdata[$k]['plugin_activity_id'] = $previouspp[$k]['plugin_activity_id'];
			$pppdata[$k]['prize_order'] = $previouspp[$k]['prize_order'];			//奖品排序，方便接下来操作
			$pppdata[$k]['prize_name'] = $previouspp[$k]['prize_name'];
			$pppdata[$k]['prize_content'] = $previouspp[$k]['prize_content'];
			$pppdata[$k]['prize_amount'] = $previouspp[$k]['prize_amount'];
			$pppdata[$k]['prize_deadline'] = $previouspp[$k]['prize_deadline'];
			$pppdata[$k]['modify_time'] = date('YmdHms');							//修改奖品时间
			$pppdata[$k]['remark'] = $previouspp[$k]['remark'];
			$pppdata[$k]['is_del'] = $previouspp[$k]['is_del'];
			$ppmtable->data($pppdata[$k])->add();									//向pluginprizemodify表中添加修改记录
		}
	
		//Step4-2：更新原来的奖品信息，要更新的奖品信息是$updatepp
		//特别注意$i是本次编辑后提交过来的奖品数目，$i有两种情况：小于等于原来的奖品数；大于原来的奖品数（原来奖品数：count($previouspp)）
		for($j=0; $j<$i; $j++){
			//本次提交奖品数是$i，for循环一直循环下去，不论是比原来奖品多少，直到停止，试想：如果本次奖品数目小于原来，for循环根本走不到else部分。
			if($j<count($previouspp)){
				//如果循环的次数是小于或等于原来奖品数部分，则采用在原来的奖品上修改处理，注意$j从0开始，所以此处不取等号！
				$updatepp[$j]['plugin_prize_id'] = $previouspp[$j]['plugin_prize_id'];		//原来奖品的主键
				$updatepp[$j]['sncode'] = $previouspp[$j]['sncode'];						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $previouspp[$j]['plugin_activity_id'];//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的最新名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的最新内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的最新数量
	
				$updatepp[$j]['getprize_amount'] = $previouspp[$j]['getprize_amount'];		//默认初始中奖数0
				$pptable->save($updatepp[$j]);												//特别注意：不要漏掉$j
			}else{
				//如果循环的次数是大于原来奖品数部分，则大于部分采用插入处理。
				$updatepp[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));				//随机奖品的主键
				$updatepp[$j]['sncode'] = md5(uniqid(rand(), true));						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $data['plugin_activity_id'];			//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的数量
	
				$updatepp[$j]['getprize_amount'] = 0;										//默认初始中奖数0
				$pptable->data($updatepp[$j])->add();										//特别注意：不要漏掉$j
			}
		}
		//如果本次编辑奖品有删除一些奖品，则进行剪切
		if($i<count($previouspp)){
			$conditionmap['plugin_activity_id'] = $data['plugin_activity_id'];				//设定要删除的条件
			$tempcount = count($previouspp) - $i;
			for($kkk=0;$kkk<$tempcount;$kkk++){
				$pptable->where($conditionmap)->order('prize_order desc')->limit(1)->setField('is_del', 1) ;		//降序排列后，从尾部删除多余的奖品，注意添加奖品的时候加上排序
			}
		}
	
		//Step5：更新本次活动主信息的图片（如果有改动的话才更新）20140618貌似这个图片更新还是有点小问题
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->up ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPath方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPath ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPath ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPath ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/scratchCard' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}
	}
	
	//读取商家添加的刮刮卡信息，缩写：scratchCardActivity→sa
	public function getCurrentSA() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
	
		//缩写：scratchActivity→sa
		$samap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id']	//取当前商家编号
				//'is_del' => 0									//此字段已经不需要限制，因为is_del=0显示进行中，is_del=1显示活动已经过期。
		);
	
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');						//查询微动平台查件表
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'趣味刮刮卡\'')->find();	//寻找趣味刮刮卡插件的编号
		$samap['plugin_id'] = $psresult['plugin_id'];		//将微动提供的趣味刮刮卡插件编号查询出来给到data的plugin_id字段值中
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($samap)->count(); 			//计算满足条件的刮刮卡活动总数
	
		//缩写：scratchActivity→sa
		$salist = array();
		$salist = $patable->where ( $samap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $salist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/*
	 * 动态加载每条活动的详情和奖品信息，点击加号展开详情信息请求getDetailSA
	 * */
	public function getDetailSA(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
	
		//缩写：prizedetail→pd，详情表中已经有外键，无需e_id了
		$pdmap = array(
				'plugin_activity_id' => I('cpaid'),				//当前查看活动的编号
				'is_del' => 0
		);
		$pdtable = M('pluginprize');
		$pdresult = $pdtable->where($pdmap)->order('prize_order')->select();	//根据活动编号查询奖品信息
		$this->ajaxReturn($pdresult, 'json');				//用ajax返回结构体给前台，数据格式：json（一维数组）
	}
	
	/*
	 * 删除某个刮刮卡活动函数delSA
	 * */
	public function delSA(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
		//接收删除信息
		$delmap = array(
				'plugin_activity_id' => I('cpaid'),				//删除哪个活动
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//哪个商家的
				'is_del' => 0
		);
		//实例化表开始删除
		$deltable = M('pluginactivity');
		$delresult = $deltable->where($delmap)->setField('is_del', 1);	//设置is_del字段为1，代表删除
		if($delresult){
			$this->ajaxReturn( array('status' => '1'), 'json');			//成功返回状态status=1
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');			//失败返回状态status=0
		}
	}
	
	/*
	 * 自定义条件和内容查询刮刮卡函数searchScratchCard
	 * */
	
	public function searchScratchCard(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
	
		$data = array (
				'searchcondition' => I('searchcondition'),				//查询条件是哪个字段
				'searchcontent'	=> I('searchcontent')					//查询的内容
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//定义要查询的条件$condition
		$condition = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],			//查询哪个店家
				'plugin_name' => '趣味刮刮卡',							//要搜索的插件名称是趣味刮刮卡
				$data['searchcondition'] => array('like','%'.$data['searchcontent'].'%')		//要查询的字段模糊匹配这个值
				//'is_del' => 0											//没有被删除的（此处注释，因为不管是0还是1，都有代表意义，1代表活动终止的）
		);
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($condition)->count(); 			//计算满足条件的刮刮卡活动总数
	
		//缩写：scratchActivity→sa
		$salist = array();
		$json = null;
		$salist = $patable->where ( $condition )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if($salist != null){
			$json = '{"total":' . $total . ',"rows":' . json_encode ( $salist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		}else{
			$json = json_encode (array());					//如果没有查询到任何东西，要输出空数组，告诉商家没查询到数据
		}
		echo $json;
	}
	
	/*
	 * 检查当前有无趣味刮刮卡活动进行中函数checkCurrentSA
	 * */
	public function checkCurrentSA(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array( 'check' => I('checkstatus') );
		$checkmap = array(
				'plugin_name' => '趣味刮刮卡',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		//缩写pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->where($checkmap)->order('add_activity_time desc')->limit(1)->select();
		$currentdate = date ( "Y-m-d H:i:s" );
		//特别注意$paresult是一个二维数组，带上0下标再取其字段！
		if( $currentdate >= $paresult[0]['plugin_activity_startdate'] && $currentdate <= $paresult[0]['plugin_activity_enddate'] ){
			$this->ajaxReturn( array('status' => '1'), 'json');
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');
		}
	}
	
	//活动中奖结果详情页面winPrizeResult
	public function winPrizeResult(){
		$this->paid = I('paid');
		$pamap['plugin_activity_id'] = $this->paid;
		$patable = M('pluginactivity');
		$this->cpa = $patable->where($pamap)->find();		//查出活动的一些信息，显示在标题栏上
		$this->display();									//展示页面
	}
	
	//显示当前获奖信息的函数
	public function currentPrizeInfo(){
		$data = array(
				'plugin_activity_id' => I('paid')
		);
	
		//做视图去查询奖品信息、中奖人信息、和中奖纪录，三表联查
		$sql = 'tu.customer_id = tc.customer_id AND tu.plugin_prize_id = tp.plugin_prize_id AND tu.plugin_activity_id = \''.$data['plugin_activity_id'].'\' AND tu.is_del = 0 AND tc.is_del = 0 AND tp.is_del = 0';
		$model = new Model();														//创建视图查询器
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/												//导入分页控件
		$total = $model->table('t_userpluginprize tu, t_customerinfo tc, t_pluginprize tp')
		->where($sql)
		->field('*')
		->count();																		//计算顾客总收藏数
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'getprize_id';			//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';				//进行一个降序排列（最近添加的显示在最前边）
	
		//缩写：scratchActivity→sa
		$upplist = array();
		$json = null;
	
		$upplist = $model->table('t_userpluginprize tu, t_customerinfo tc, t_pluginprize tp')
		->where($sql)
		->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )
		->order ( ''.$sort.' '.$order )
		->field('*')
		->select();
	
		if($upplist != null){
			$json = '{"total":' . $total . ',"rows":' . json_encode ( $upplist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		}else{
			$json = json_encode (array());					//如果没有查询到任何东西，要输出空数组，告诉商家没查询到数据
		}
		echo $json;
	}
	
	//商户确认发放奖品处理函数sendPrizeConfirm()，AJAX提交，AJAX返回。
	public function sendPrizeConfirm(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array(
				'getprize_id' => I('gpid')
		);
		//缩写：userpluginprize→upp
		$upptable = M('userpluginprize');
		$upprs = $upptable->where($data)->setField('send_status', 1);
		if($upprs){
			$this->ajaxReturn( array('status' => 1, 'msg' => '已经将奖品成功发放给客户！') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '请检查网络状况，并稍后重试！') );
		}
	}
	
	//商户确认删除中奖纪录处理函数delPrizeConfirm()，AJAX提交，AJAX返回。
	public function delPrizeConfirm(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array(
				'getprize_id' => I('gpid')
		);
		//缩写：userpluginprize→upp
		$upptable = M('userpluginprize');
		$upprs = $upptable->where($data)->setField('is_del', 1);
		if($upprs){
			$this->ajaxReturn( array('status' => 1, 'msg' => '删除当前中奖纪录成功！') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '请检查网络状况，并稍后重试！') );
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*-----------------------------------↓以下开始是谁能一站到底后台的代码↓----------------------------------*/
	public function whoStand(){
		$this->display();
	}
	
	//添加、编辑一站到底活动页面
	public function addWhoStand(){
		$data = array(
				'plugin_activity_id' => I('plugin_activity_id')			//试着接收传来的插件活动编号
		);
		if($data['plugin_activity_id']=='') {						//如果没有接收到编号，则是新添加活动，直接让用户添加活动；否则是编辑活动，如下↓
			$this->editflag = 0;									//推送编辑标记：0为false
			$this->display();
		}else{
			//缩写：editPluginActivity→epa
			$epamap = array(
					'plugin_activity_id' => $data['plugin_activity_id'],//当前活动编号
					'is_del' => 0
			);
			//做视图拼接，pluginactivity表是主表，别名p(parent)；pluginprize表是子表，别名c(child)。
			$sql = 'p.plugin_activity_id = c.plugin_activity_id and p.is_del = 0 and c.is_del = 0 and p.plugin_activity_id = \''.$epamap['plugin_activity_id'].'\'';
			$model = new Model();
			$eparesult =  $model->table('t_pluginactivity p, t_pluginprize c')->where ( $sql )->order('c.prize_order')->field('p.*, c.*')->select();	//查出活动
			$this->editflag = 1;									//推送编辑标记：1为true，前台判断
			$this->epainfo = $eparesult;							//推送活动信息给前台，前台根据情况选择
			$this->display();										//展示页面
		}
	}
	
	//添加一站到底活动处理函数
	public function addWhoStandConfirm(){
		//Step1：处理一站到底活动主信息
		$data = array(
				'plugin_activity_id' => md5(uniqid(rand(), true)),
				'plugin_activity_title' => I('activity_title'),
				//'plugin_id' => '0012',								//从微动平台提供的插件里查出是趣味刮刮卡插件的编号，代码已经在下边写好↓
				'plugin_name' => '一站到底',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'plugin_activity_brief' => I('activity_brief'),
				'plugin_activity_detail' => I('activity_detail'),
				//'participation' => I('participation'),
				//'total_times' => I('total_times'),
				'add_activity_time' => I('startdate'),
				'plugin_activity_startdate' => I('startdate'),
				'plugin_activity_enddate' => I('enddate'),
				//'activity_endtitle' => I('activity_endtitle'),
				//'activity_endinfo' => I('activity_endinfo'),
				'keyword' => I('keyword'),
				'getprize_tip' => I('getprize_info'),
				//'repeatplay_tip' => I('repeatplay_tip'),
				//'hide_amount' => I('hide_amount'),					//接收不显示奖品框是否选中
				//'everyday_permission' => I('play_everyday'),		//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),				//接收是否关注即弹出框是否选中
		);
	
		//Step1-0：查询一站到底插件编号
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'一站到底\'')->find();
		$data['plugin_id'] = $psresult['plugin_id'];			//将微动提供的趣味刮刮卡插件编号查询出来给到data的plugin_id字段值中
	
		//Step1-1：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
		//Step2：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';				//特别注意：在添加奖品的时候就对奖品加上排序，方便日后编辑处理。
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step4：先插入活动数据，继而更新活动图片
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->data($data)->add();
	
		//Step5：插入奖品信息
		//缩写：pluginprize→pp
		$pptable = M('pluginprize');
		for($j=0; $j<$i; $j++){
			$finalprize[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));			//随机奖品的主键
			$finalprize[$j]['sncode'] = md5(uniqid(rand(), true));					//随机奖品的sncode码
			$finalprize[$j]['plugin_activity_id'] = $data['plugin_activity_id'];	//奖品的外键
			$finalprize[$j]['getprize_amount'] = 0;									//默认初始中奖数0
			$pptable->data($finalprize[$j])->add();									//特别注意：不要漏掉$j
		}
	
		//Step6：处理活动开始、结束图片信息，导入上传类
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->upWS ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPathLA方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPathLA ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPathLA ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPathLA ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}else{											//两幅都没有自定义活动开始图片上传，则用系统默认的图片
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-start.jpg';
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-end.jpg';
			if($patable->save ( $data ))$this->redirect ( 'Admin/PluginManage/whoStand' );
			else{
				$this->error ( '添加活动失败！' );
			}
		}
	
		//结论：特别注意，将数据分别存入两张表的过程，如果以后会使用事物过程，最好采用事物来操作
	}
	
	//图片上传函数upWS
	private function upWS($activityinfo) {
		// 完成与thinkphp相关的，文件上传类的调用
		import ( 'ORG.Net.UploadFile' ); 	// 将上传类UploadFile.class.php拷到Lib/Org文件夹下
		$upload = new UploadFile ();
		$upload->maxSize = '1000000'; 		// 默认为-1，不限制上传大小
		$upload->savePath = 'Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/plugin/luckyWheel/' . $activityinfo['plugin_activity_id'] . '/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存
		$upload->saveRule = uniqid; 		// 上传文件的文件名保存规则
		$upload->uploadReplace = true; 		// 如果存在同名文件是否进行覆盖
		$upload->allowExts = array ( 'jpg', 'jpeg', 'png', 'gif' ); // 准许上传的文件类型
		$upload->allowTypes = array ( 'image/png', 'image/jpg', 'image/jpeg', 'image/gif' ); // 检测mime类型
		$upload->thumb = true; 				// 是否开启图片文件缩略图
		$upload->thumbMaxWidth = '200,100';
		$upload->thumbMaxHeight = '200,100';
		//目录如果没有，会自动创建文件夹的！——2014/06/12
		if(!file_exists($upload->savePath)){
			mkdir($upload->savePath);
		}
		if ($upload->upload ()) {
			$info = $upload->getUploadFileInfo ();
			return $info;
		} else {
			$this->error ( $upload->getErrorMsg () ); // 专门用来获取上传的错误信息的
		}
	}
	
	//插入图片路径函数insertImgPathWS
	private function insertImgPathWS($upinfos, $activityinfo, $numflag) {
		//缩写：$activitystartimage→asi
		$asitable = M ( 'pluginactivity' );
		$data = array();
		if($numflag==2){
			for($i = 0; $i < count ( $upinfos ); $i++) {
				$savepath = $upinfos [$i] ['savepath'];
				if($i==0){
					$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
				if($i==1){
					$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [$i] ['savename'];
				}
			}
		}else if($numflag==0){		//只有第一幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
			$data['activity_endimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-end.jpg';
		}else if($numflag==1){		//只有第二幅
			$savepath = $upinfos [0] ['savepath'];
			$data['activity_startimage'] = '/weact/APP/Tpl/Admin/Public/images/luckywheel/activity-lucky-wheel-start.jpg';
			$data['activity_endimage'] = __ROOT__ .'/'. $savepath . $upinfos [0] ['savename'];
		}
		$data['plugin_activity_id'] = $activityinfo['plugin_activity_id'];
		if ($asitable->where($update)->save ($data)) {
			return true;
		}else{
			return false;
		}
	}
	
	//修改谁能一站到底处理函数
	public function editWhoStandConfirm(){
		//Step1-1：处理刮刮卡活动主信息，准备进入编辑修改阶段
		$data = array(
				'plugin_activity_id' => I('activity_id'),					//接收编辑活动的编号
				'plugin_activity_title' => I('activity_title'),				//接收活动标题
				'plugin_activity_brief' => I('activity_brief'),				//接收活动简介
				'plugin_activity_detail' => I('activity_detail'),			//接收活动详情
				'participation' => I('participation'),						//接收参与人数
				'total_times' => I('total_times'),							//接收总共可玩次数
				'latest_modify_time' => date('YmdHms'),						//最后一次修改时间是当前
				'plugin_activity_startdate' => I('startdate'),				//接收活动起始时间
				'plugin_activity_enddate' => I('enddate'),					//接收活动结束时间
				'activity_endtitle' => I('activity_endtitle'),				//接收活动结束标题
				'activity_endinfo' => I('activity_endinfo'),				//接收活动结束信息
				'keyword' => I('keyword'),									//接收活动关键字
				'getprize_tip' => I('getprize_info'),						//接收活动兑奖信息
				'repeatplay_tip' => I('repeatplay_tip'),					//接收重复抽奖提醒
				'hide_amount' => I('hide_amount'),							//接收不显示奖品框是否选中
				'everyday_permission' => I('play_everyday'),				//接收是否每天可以玩框是否选中
				'attention_alert' => I('focus_alert'),						//接收是否关注即弹出框是否选中
		);
	
		//Step1-2：进行判断完善活动信息
		if($data['hide_amount'])$data['hide_amount'] = 1;		//如果隐藏奖品数量
		else{
			$data['hide_amount'] = 0;							//如果不隐藏奖品数量
		}
		if($data['everyday_permission'])$data['everyday_permission'] = 1;	//如果每天可以玩
		else{
			$data['everyday_permission'] = 0;							//如果不是每天可以玩
		}
		if($data['attention_alert'])$data['attention_alert'] = 1;		//如果关注即弹出选中
		else{
			$data['attention_alert'] = 0;							//如果不是关注即弹出
		}
	
		//Step2-1：处理奖品类信息
		$prizedata = array(
				'first_prize' => '一等奖',
				'first_prize_content' => I('first_prize'),
				'first_prize_amount' => I('first_prize_amount'),
				'second_prize' => '二等奖',
				'second_prize_content' => I('second_prize'),
				'second_prize_amount' => I('second_prize_amount'),
				'third_prize' => '三等奖',
				'third_prize_content' => I('third_prize'),
				'third_prize_amount' => I('third_prize_amount'),
				'fourth_prize' => '四等奖',
				'fourth_prize_content' => I('fourth_prize'),
				'fourth_prize_amount' => I('fourth_prize_amount'),
				'fifth_prize' => '五等奖',
				'fifth_prize_content' => I('fifth_prize'),
				'fifth_prize_amount' => I('fifth_prize_amount'),
				'sixth_prize' => '六等奖',
				'sixth_prize_content' => I('sixth_prize'),
				'sixth_prize_amount' => I('sixth_prize_amount')
		);
		//Step2-2：进行判断完善奖品类信息
		$finalprize = array();		//定义最终奖品数组$finalprize
		$i = 0;						//奖品数组从0开始，对一至六等奖各自检查
		if($prizedata['first_prize_content']){
			$finalprize[$i]['prize_name'] = '一等奖';
			$finalprize[$i]['prize_order'] = '1';
			$finalprize[$i]['prize_content'] = $prizedata['first_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['first_prize_amount'];
			$i += 1;
		}
		if($prizedata['second_prize_content']){
			$finalprize[$i]['prize_name'] = '二等奖';
			$finalprize[$i]['prize_order'] = '2';
			$finalprize[$i]['prize_content'] = $prizedata['second_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['second_prize_amount'];
			$i += 1;
		}
		if($prizedata['third_prize_content']){
			$finalprize[$i]['prize_name'] = '三等奖';
			$finalprize[$i]['prize_order'] = '3';
			$finalprize[$i]['prize_content'] = $prizedata['third_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['third_prize_amount'];
			$i += 1;
		}
		if($prizedata['fourth_prize_content']){
			$finalprize[$i]['prize_name'] = '四等奖';
			$finalprize[$i]['prize_order'] = '4';
			$finalprize[$i]['prize_content'] = $prizedata['fourth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fourth_prize_amount'];
			$i += 1;
		}
		if($prizedata['fifth_prize_content']){
			$finalprize[$i]['prize_name'] = '五等奖';
			$finalprize[$i]['prize_order'] = '5';
			$finalprize[$i]['prize_content'] = $prizedata['fifth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['fifth_prize_amount'];
			$i += 1;
		}
		if($prizedata['sixth_prize_content']){
			$finalprize[$i]['prize_name'] = '六等奖';
			$finalprize[$i]['prize_order'] = '6';
			$finalprize[$i]['prize_content'] = $prizedata['sixth_prize_content'];
			$finalprize[$i]['prize_amount'] = $prizedata['sixth_prize_amount'];
			$i += 1;
		}
	
		//Step3：根据plugin_activity_id主键更新pluginactivity的信息
		//缩写：pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->save($data);				//特别注意：此$data有主键，所以直接可以使用save($data)。
	
		//Step4-1：先把原来奖品查询出来，放到修改记录表中，此步骤为了避免商户和顾客产生中奖纠纷。
		//缩写：pluginprize→pp，previouspluginprize→ppp，pluginprizemodify→ppm
		$pppmap = array( 'plugin_activity_id' => $data['plugin_activity_id'] );
		$pptable = M('pluginprize');
		$previouspp = $pptable->where($pppmap)->order('prize_order')->select();		//查询出原来的奖品信息$previouspp
	
		$ppmtable = M('pluginprizemodify');
		$pppdata = array();
		for($k=0; $k<count($previouspp); $k++){
			$pppdata[$k]['prize_modify_id'] = md5(uniqid(rand(), true));			//生成修改奖品主键
			$pppdata[$k]['plugin_prize_id'] = $previouspp[$k]['plugin_prize_id'];
			$pppdata[$k]['sncode'] = $previouspp[$k]['sncode'];
			$pppdata[$k]['plugin_activity_id'] = $previouspp[$k]['plugin_activity_id'];
			$pppdata[$k]['prize_order'] = $previouspp[$k]['prize_order'];			//奖品排序，方便接下来操作
			$pppdata[$k]['prize_name'] = $previouspp[$k]['prize_name'];
			$pppdata[$k]['prize_content'] = $previouspp[$k]['prize_content'];
			$pppdata[$k]['prize_amount'] = $previouspp[$k]['prize_amount'];
			$pppdata[$k]['prize_deadline'] = $previouspp[$k]['prize_deadline'];
			$pppdata[$k]['modify_time'] = date('YmdHms');							//修改奖品时间
			$pppdata[$k]['remark'] = $previouspp[$k]['remark'];
			$pppdata[$k]['is_del'] = $previouspp[$k]['is_del'];
			$ppmtable->data($pppdata[$k])->add();									//向pluginprizemodify表中添加修改记录
		}
	
		//Step4-2：更新原来的奖品信息，要更新的奖品信息是$updatepp
		//特别注意$i是本次编辑后提交过来的奖品数目，$i有两种情况：小于等于原来的奖品数；大于原来的奖品数（原来奖品数：count($previouspp)）
		for($j=0; $j<$i; $j++){
			//本次提交奖品数是$i，for循环一直循环下去，不论是比原来奖品多少，直到停止，试想：如果本次奖品数目小于原来，for循环根本走不到else部分。
			if($j<count($previouspp)){
				//如果循环的次数是小于或等于原来奖品数部分，则采用在原来的奖品上修改处理，注意$j从0开始，所以此处不取等号！
				$updatepp[$j]['plugin_prize_id'] = $previouspp[$j]['plugin_prize_id'];		//原来奖品的主键
				$updatepp[$j]['sncode'] = $previouspp[$j]['sncode'];						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $previouspp[$j]['plugin_activity_id'];//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的最新名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的最新内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的最新数量
	
				$updatepp[$j]['getprize_amount'] = $previouspp[$j]['getprize_amount'];		//默认初始中奖数0
				$pptable->save($updatepp[$j]);												//特别注意：不要漏掉$j
			}else{
				//如果循环的次数是大于原来奖品数部分，则大于部分采用插入处理。
				$updatepp[$j]['plugin_prize_id'] = md5(uniqid(rand(), true));				//随机奖品的主键
				$updatepp[$j]['sncode'] = md5(uniqid(rand(), true));						//随机奖品的sncode码
				$updatepp[$j]['plugin_activity_id'] = $data['plugin_activity_id'];			//奖品的外键
	
				$updatepp[$j]['prize_order'] = $finalprize[$j]['prize_order'];				//奖品的最新名称
				$updatepp[$j]['prize_name'] = $finalprize[$j]['prize_name'];				//奖品的名称
				$updatepp[$j]['prize_content'] = $finalprize[$j]['prize_content'];			//奖品的内容
				$updatepp[$j]['prize_amount'] = $finalprize[$j]['prize_amount'];			//奖品的数量
	
				$updatepp[$j]['getprize_amount'] = 0;										//默认初始中奖数0
				$pptable->data($updatepp[$j])->add();										//特别注意：不要漏掉$j
			}
		}
		//如果本次编辑奖品有删除一些奖品，则进行剪切
		if($i<count($previouspp)){
			$conditionmap['plugin_activity_id'] = $data['plugin_activity_id'];				//设定要删除的条件
			$tempcount = count($previouspp) - $i;
			for($kkk=0;$kkk<$tempcount;$kkk++){
				$pptable->where($conditionmap)->order('prize_order desc')->limit(1)->setField('is_del', 1) ;		//降序排列后，从尾部删除多余的奖品，注意添加奖品的时候加上排序
			}
		}
	
		//Step5：更新本次活动主信息的图片（如果有改动的话才更新）20140618貌似这个图片更新还是有点小问题
		if ($_FILES['picture']['size'][0]!=0||$_FILES['picture']['size'][1]!=0) {			//有自定义活动开始图片上传
			$upinfos = null;
			$upinfos = $this->upLA ( $data );						//直接传入整个activity的信息$data
			if ($upinfos != null) {
				// 写入数据库的自定义活动图片insertImgPathLA方法
				if($_FILES['picture']['size'][0]!=0 && $_FILES['picture']['size'][1]==0){			//如果只有开始图片
					if ($this->insertImgPathLA ( $upinfos, $data, 0)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else if($_FILES['picture']['size'][0]==0 && $_FILES['picture']['size'][1]!=0){		//如果只有结束图片
					if ($this->insertImgPathLA ( $upinfos, $data, 1)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}else{																				//如果两幅图片都有
					if ($this->insertImgPathLA ( $upinfos, $data, 2)) {
						$this->redirect ( 'Admin/PluginManage/whoStand' );
					} else {
						$this->error ( '写入数据库失败！' );
					}
				}
			} else {
				$this-> error ( '上传文件异常，请与系统管理员联系！' );
			}
		}
	}
	
	//读取商家添加的一站到底信息，缩写：whostand->ws
	public function getCurrentWS() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
	
		$lamap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id']	//取当前商家编号
				//'is_del' => 0									//此字段已经不需要限制，因为is_del=0显示进行中，is_del=1显示活动已经过期。
		);
		//p($_SESSION['curEnterprise']['e_id']);die;
		//缩写：pluginservice→ps
		$pstable = M('pluginservice');						//查询微动平台查件表
		$psresult = $pstable->where('is_del = 0 and plugin_name = \'一站到底\'')->find();	//寻找幸运大转盘插件的编号
		$lamap['plugin_id'] = $psresult['plugin_id'];		//将微动提供的一站到底插件编号查询出来给到data的plugin_id字段值中
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加一站到底时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($lamap)->count(); 			//计算满足条件的一站到底活动总数
		//p($total);die;
		//缩写：whostand→ws
		$lalist = array();
		$lalist = $patable->where ( $lamap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $lalist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/*
	 * 动态加载每条活动的详情，点击加号展开详情信息请求getDetailWS
	 * */
	public function getDetailWS(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
	
		//缩写：prizedetail→pd，详情表中已经有外键，无需e_id了
		$pdmap = array(
				'plugin_activity_id' => I('cpaid')				//当前查看活动的编号
		);
		$pdtable = M('pluginprize');
		$pdresult = $pdtable->where($pdmap)->order('prize_order')->select();	//根据活动编号查询奖品信息
		$this->ajaxReturn($pdresult, 'json');				//用ajax返回结构体给前台，数据格式：json（一维数组）
	}
	
	/*
	 * 删除某个一站到底活动函数delWS
	 * */
	public function delWS(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
		//接收删除信息
		$delmap = array(
				'plugin_activity_id' => I('cpaid'),				//删除哪个活动
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//哪个商家的
				'is_del' => 0
		);
		//实例化表开始删除
		$deltable = M('pluginactivity');
		$delresult = $deltable->where($delmap)->setField('is_del', 1);	//设置is_del字段为1，代表删除
		if($delresult){
			$this->ajaxReturn( array('status' => '1'), 'json');			//成功返回状态status=1
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');			//失败返回状态status=0
		}
	}
	
	/*
	 * 自定义条件和内容查询一站到底函数searchWhoStand
	 * */
	
	public function searchWhoStand(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
	
		$data = array (
				'searchcondition' => I('searchcondition'),				//查询条件是哪个字段
				'searchcontent'	=> I('searchcontent')					//查询的内容
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_activity_time';		//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		//定义要查询的条件$condition
		$condition = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],			//查询哪个店家
				'plugin_name' => '一站到底',							//要搜索的插件名称是一站到底
				$data['searchcondition'] => array('like','%'.$data['searchcontent'].'%')		//要查询的字段模糊匹配这个值
				//'is_del' => 0											//没有被删除的（此处注释，因为不管是0还是1，都有代表意义，1代表活动终止的）
		);
	
		//缩写：pluginactivity→pa
		$patable = M ( "pluginactivity" );
		$total = $patable->where($condition)->count(); 			//计算满足条件的一站到底活动总数
	
		$lalist = array();
		$json = null;
		$lalist = $patable->where ( $condition )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		if($lalist != null){
			$json = '{"total":' . $total . ',"rows":' . json_encode ( $lalist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		}else{
			$json = json_encode (array());					//如果没有查询到任何东西，要输出空数组，告诉商家没查询到数据
		}
		echo $json;
	}
	
	/*
	 * 检查当前有一站到底活动进行中函数checkCurrentWS
	 * */
	public function checkCurrentWS(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
		$data = array( 'check' => I('checkstatus') );
		$checkmap = array(
				'plugin_name' => '一站到底',
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		//缩写pluginactivity→pa
		$patable = M('pluginactivity');
		$paresult = $patable->where($checkmap)->order('add_activity_time desc')->limit(1)->select();
		if($paresult){
			$currentdate = date ( "Y-m-d H:i:s" );
			//特别注意$paresult是一个二维数组，带上0下标再取其字段！
			if( $currentdate >= $paresult[0]['plugin_activity_startdate'] && $currentdate <= $paresult[0]['plugin_activity_enddate'] ){
				$this->ajaxReturn( array('status' => '1'), 'json');
			}else{
				$this->ajaxReturn( array('status' => '0'), 'json');
			}
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');
		}
	}
	
	
	public function editQuestionMsg(){
		$this->display();
	}
	
	
	//第二部，添加新活动
	public function addActivity() {
		$current_enterprise = session ( 'curEnterprise' );
		$question_activity_id = md5 ( uniqid ( rand (), true ) );
		$data = array (
				'question_activity_id' => $question_activity_id,
				'e_id' => $current_enterprise ['e_id'],
				'activity_title' => I('activityTitle'),
				'activity_brief' => I('activityBrief'),
				'activity_detail' => I('activityDescription'),
				'participation' => '0',
				'add_activity_time' => date ( 'Y-m-d H:i:s' ),
				'latest_modify_time' => date ( 'Y-m-d H:i:s' ),
				'activity_startdate' => I('startDate'),
				'activity_enddate' => I('endDate'),
				'keyword' => I('key'),
				'random_ask' => '0',
				'is_del' => '0'
			
		);
		//p($data);
		$qatable = M ( 'questionactivity' );
		$result = $qatable->data($data)->add();
	
		if($result)
			$this->ajaxReturn ( array ( 'status' => 1, 'question_activity_id' => $question_activity_id ), 'json' );
		else
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
	}
	
	//添加谁能一站到底题目
	public function addQuestionList(){
		$question_activity_id = I ( 'question_activity_id' );
		$this->assign ( 'question_activity_id', $question_activity_id );
		$this->display();
	}
	
	// 获取题目数据
	public function read() {
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$qbtable = M ( "questionbank" );
		$total = $qbtable->where ( 'is_del=0')->count (); // 计算总数
		$questionlist = array ();
	
		$questionlist = $qbtable->where ( 'is_del=0')->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		//p($questionlist);die;
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $questionlist ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		//p($json);die;
		echo $json;
	}
	//增加客户信息
	public function add() {
		$result = false;
		$customer = M ( "customerinfo" );
		$customer_id = generate_uniqueid();
		$e_id = generate_uniqueid();
		$data ['customer_id'] = $customer_id;
		$data ['customer_name'] = $_REQUEST ['customer_name'];
		$data ['nick_name'] = $_REQUEST ['nick_name'];
		$data ['account'] = $_REQUEST ['account'];
		$data ['password'] = md5($_REQUEST ['password']);
		$data ['e_id'] = $e_id;
		$data ['contact_number'] = $_REQUEST ['contact_number'];
		$data ['email'] = $_REQUEST ['email'];
		$data ['sex'] = $_REQUEST ['sex'];
		$data ['birthday'] = $_REQUEST ['birthday'];
		$data ['customer_address'] = $_REQUEST ['customer_address'];
		$data ['register_time'] = $_REQUEST ['register_time'];
		$data ['member_level'] = $_REQUEST ['member_level'];
		$result = $customer->add ( $data );
		if ($result == true) {
			echo json_encode ( array (
					'success' => true
			) );
		} else {
			echo json_encode ( array (
					'msg' => '添加出错！'
			) );
		}
	}
	
	
	//第二部，添加活动题目
	public function addActivityQuestions(){
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$current_enterprise = session ( 'curEnterprise' );
		$question_activity_id = I ( 'question_activity_id' );
		$ids = I ( 'ids' );
		$question_id = divide ( $ids, ',' );
		// 查看$question_activity_id是否有值
		if ($question_activity_id != null && $question_activity_id != '') {
			//$data ['question_activity_id'] = $question_activity_id;
			//首先清空数据库中之前已选题目
			$qaltable=M( 'questionactivitylist' );
			$clearmap = array(
					'question_activity_id' => I ( 'question_activity_id' ),
					'is_del'=>'0'
			);
			$qaltable->where($clearmap)->setField('is_del', '1');
			for($i = 0; $i < count ( $question_id ); $i += 1) {
				$qalmap = array(
						'list_record_id'=>md5(uniqid(rand(), true)),
						'question_activity_id'=>'637f95b672a379409b57d2f489675179',
						'question_id'=>$question_id [$i],
						'question_order'=>$i,
						'is_del'=>0
				);
				$qaltable->data( $qalmap )->add();
			}
			$this->ajaxReturn ( array ('status' => 1 ), 'json' );
		} else {
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	//read activity questions
	public function readActivityQuestion(){
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
		$qaltable = M ( 'questionactivitylist' );
		$qalmap=array(
				'question_activity_id'=>'637f95b672a379409b57d2f489675179',
				'is_del'=>'0'
		);
		$total = $qaltable->where($qalmap)->select(); // 所有该活动的题目
		$qbtable=M('questionbank');
		for($i=0;$i<count($total);$i++){
			$qbmap = array(
					'question_id'=>$total[$i]['question_id'],
					'is_del'=>'0'
			);
			$temp=$qbtable->where($qbmap)->limit( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
			$questionjoin[$i]=$temp[0];
		}
		$json = '{"total":' . count($total) . ',"rows":' . json_encode ( $questionjoin ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	//display my question
	public function preMyQuestion(){
		$this->display();
	}
	
	//增加新题目
	public function addNewQuestion(){
		$result = false;
		$qaltable = M('questionbank');
		$data = array(
				'question_id' => md5 ( uniqid ( rand (), true ) ),
				'question_group' =>$_REQUEST ['group_id'],
				'question_order' => 1,
				'question' => $_REQUEST ['title'],
				'option_a' => $_REQUEST ['option_a'],
				'option_b' => $_REQUEST ['option_b'],
				'option_c' =>c,
				'option_d'=>d,
				'answer' =>$_REQUEST ['correct_option'],
				'answer_reason' =>$_REQUEST ['answer_explain'],
				'question_type' => 4,
				'correct_count' => '0',
				'wrong_count' => '0',
				'question_score' => '2',
				'is_del' => '0',
		);
		$result= $qaltable->add($data);
		if ($result) {
			echo json_encode ( array (
					'success' => true
			) );
		} else {
			echo json_encode ( array (
					'msg' => '添加出错！'
			) );
		}
	}
	
	//clear my questions
	public function  clearMyQuestion(){
		$qaltable = M('questionactivitylist');
		$qalmap=array(
				'question_activity_id'=>I('question_activity_id'),
				'is_del'=>'0'
		);
		$is_del=$qaltable->where($qalmap)->setField('is_del','1');
		if($is_del){
			$this->ajaxReturn ( array ('status' => 1 ), 'json' );
		} else {
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	
	//delete my questions
	public function delMyquestion(){
		$question_activity_id = I ( 'question_activity_id' );
		$ids = I ( 'ids' );
		$question_id = divide ( $ids, ',' );
		// 查看$question_activity_id是否有值
		if ($question_activity_id != null && $question_activity_id != '') {
			$qaltable=M('questionactivitylist');
			for($i = 0; $i < count ( $question_id ); $i += 1) {
				$delmap = array(
						'question_activity_id'=>'637f95b672a379409b57d2f489675179',
						'question_id'=>$question_id [$i],
						'is_del'=>0
				);
				$qaltable->where( $delmap )->setField('is_del','1');
			}
			$this->ajaxReturn ( array ('status' => 1 ), 'json' );
		} else {
			$this->ajaxReturn ( array ( 'status' => 0 ), 'json' );
		}
	}
	//success
	public function success(){
		$this->display();
	}
	
	public function test(){
		$data=I('');
		p($data);die;
	}
	
	
}
?>