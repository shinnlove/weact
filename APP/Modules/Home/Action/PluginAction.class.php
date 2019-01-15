<?php
/*
 * 本控制器控制营销插件，如幸运大转盘和趣味刮刮卡。
 * 本控制器函数一览：
 * 1、luckyWheel()，幸运大转盘页面的展示；
 * 2、scratchCard()，趣味刮刮卡页面的展示；
 * 3、myWish()，我的心愿页面；
 * 4、addWish()，添加我的愿望页面；
 * 5、wishwall()，商家许愿墙页面。
 * 
 * luckyWheelPlayCheck()，大转盘玩之前的检查函数，如果已经玩过了，就不让用户再玩；
 * luckyWheelPlaySign()，大转盘玩的记录写入函数；
 * 
 * */
class PluginAction extends MobileLoginAction {
	
	/*----------------------↓展示页面、读取信息的Action↓----------------------------*/
	
	//1、幸运大转盘页面的展示
	public function luckyWheel(){
		$this->display();
	}
	
	//2、趣味刮刮卡页面的展示
	public function scratchCard(){
		$data = array(
			'e_id' => I('e_id')
		);

		$currentdate = date('Ymd');								//取得当前年月日
		$sql = 't_pluginactivity.plugin_activity_id = t_pluginprize.plugin_activity_id AND '.$currentdate.' BETWEEN date(plugin_activity_startdate) AND date(plugin_activity_enddate) AND t_pluginactivity.e_id = '.$data['e_id'].' AND t_pluginactivity.is_del = 0 AND t_pluginprize.is_del = 0';
		$model = new Model();
		$cpa = $model->table('t_pluginactivity, t_pluginprize')->where($sql)->field('*')->select();		//cpa是currentpluginactivity的简写
		
		/*
		 * 控制奖项算法
		 * */
		
		$total_people = $cpa[0]['participation'];				//获取参与此次抽奖的总人数
		$currentnumber = mt_rand(1, $total_people);				//随机一个数，这个数决定此次打开页面抽奖是否中奖
		$prize_total = 0;										//设置总奖项为0
		$flag = 0;												//中奖标志：0代表该奖项未出现
		for($i=0;$i<count($cpa);$i++){							//循环操作内存
			$cprizeamount = $cpa[$i]['prize_amount'] - $cpa[$i]['getprize_amount'];	//当前奖项剩余数$cprizeamount
			if($cprizeamount < 0) $cprizeamount = 0;			//防止程序出bug，如果$cprizeamount小于0，设置为0
			$cpa[$i]['prize_left'] = $cprizeamount;				//该奖的剩余奖项个数
			$prize_total += $cpa[$i]['prize_amount'];			//总奖项加上该奖的个数
			if(flag == 0){
				if($currentnumber <= $prize_total && $cprizeamount > 0) $flag = 1;	//如果中奖，且该奖有剩余，则中奖标志为1
				$cpa[$i]['prize_gain'] = $flag;					//当前奖项的prize_gain字段设置为1或0
			}
		}
		
		//如果中奖，则处理中奖情况
		$finalsncode = 'sncode';								//定义最后推送的sncode码
		$flagsn = 0;											//sn中奖标记0
		for($j=0;$j<count($cpa);$j++){
			if($cpa[$j]['prize_gain']==1){
				$finalsncode = $cpa[$j]['sncode'];				//如果中奖，则把sncode给到最后变量中
				$flagsn = 1;									//sn中奖标记置为1
				break;											//跳出检测中奖循环
			}
		}
		if($flagsn ==0) $finalsncode = md5 (uniqid (rand(), true));	//如果循环自然停止，没有中奖，则随机一个假的sncode码推送到前台
		
		//p('随机数:'.$currentnumber);p('总奖数:'.$prize_total);p($cpa);p('finalsncode:'.$finalsncode);die;
		
		$this->cpaid = $cpa[0]['plugin_activity_id'];			//本次活动的编号
		$this->cpa = $cpa;										//推送中奖信息
		$this->cpacount = count($cpa);							//奖项数目，用做循环也用作变量
		$this->flagsn = $flagsn;								//告诉前台是否中奖
		$this->sncode = $finalsncode;							//推送sncode码
		$this->customer = $_SESSION['currentcustomer'];			//推送当前顾客

		//剩余次数查询：Step 1 to 4。
		//Step1：设定不变查询条件$playtimeremain
		$playtimeremain = array(
			'e_id' => $data[e_id],
			'customer_id' => $_SESSION['currentcustomer']['customer_id'],
			'plugin_activity_id' => $cpa[0]['plugin_activity_id'],
			'is_del' => 0
		);
		//Step2：根据本次活动是否每日可玩，来确定是否要加上日期条件
		if($cpa[0]['everyday_permission']==1){								//该插件并不是每天都可以玩的，整个活动只能玩设定的次数
			$playtimeremain['date(play_time)'] = date('Ymd');				//绝对不能用这种格式执行ThinkPHP的SQL：date('Y-m-d')
		}
		//Step3：查询数据库获得玩的次数
		$ptmresult = M('pluginplayrecord')->where($playtimeremain)->count();//根据不同条件查询是否次数受限
		//Step4：如果次数超过限制，则根据是否每日可玩对应显示限制信息
		if($ptmresult >= $cpa[0]['total_times']) {							//如果次数受到限制
			if($cpa[0]['everyday_permission']==1){
				$this->error('您今天抽奖次数已经达到限制！请明天再来试试吧！');	//如果是每天可玩，显示今天次数达到上限。暂时就先这么处理，以后弄个单独的结束页面
			}else{
				$this->error('对本次活动您的抽奖次数已经达到限制！');			//如果是本活动只能玩几次，显示活动次数达到限制。暂时就先这么处理，以后弄个单独的结束页面
			}
		}
		
		$this->already = $ptmresult;										//玩了几次
		$this->total = $cpa[0]['total_times'];								//总共几次
		$this->display();
	}
	
	//3、分页显示我的许愿页面
	public function myWish() {
		$data = array (
			'e_id' => I ( 'e_id' ),
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
			'is_del' => 0
		);
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/
		//step1：导入控件，并确定一共几条数据、每页展示几条数据
		import('ORG.Util.Page');														//导入分页控件
		$count = M ( 'wish' )->where ( $data )->count();								//计算出这个顾客在这家店铺的许愿数
		$page = new Page ( $count, 10);													//每页显示10条许愿记录
		//step2：查询要显示的数据，并传送一些辅助数据到前台配合查询
		$wishinfo = M ( 'wish' )->where ( $data )->order ( 'wish_time desc' )->limit($page->firstRow.','.$page->listRows)->select();	//依据分页状态显示
		$wishcount = count( $wishinfo );
		for($i=0; $i<$wishcount; $i++){
			$wishinfo [$i] ['wish_time'] = timetodate( $wishinfo [$i] ['wish_time'] );	//转换整型时间为日期型
		}
		$this->wishlist = $wishinfo;
		$this->wishcount = $wishcount;													//计算需要循环的次数给前台for循环用
		//step3：设置分页控件的主题与推送分页控件到前台
		$page->setConfig('theme','%upPage% %nowPage%/%totalPage% 页 %downPage%');			//设置分页主题
		$this->page = $page->show();													//page控件作为变量向前台推送，注意和前台代码的配合
		/*----------------↑以上为PHP导入分页控件代码，注意和前台的配合↑---------------*/
		$this->display ();
	}
	
	//4、添加许愿页面
	public function addWish() {
		$this->display ();
	}
	
	//5、商家的许愿墙页面
	public function wishwall() {
		$data = array (
			'e_id' => I ( 'e_id' ),
			'is_del' => 0
		);
		$this->wishlist = M ( 'wish' )->where ( $data )->limit ( 10 )->order ( 'wish_time desc' )->select ();
		$this->display ();
	}
	
	/*----------------------↑展示页面、读取信息的Action↑----------------------------*/
	
	/*----------------------↓处理用户交互的函数Action↓----------------------------*/
	
	//大转盘玩之前的检查函数，如果已经玩过了，就不让用户再玩
	public function luckyWheelPlayCheck(){
		$data = array(
			'e_id' => I('e_id')
		);
		$map['customer_id'] = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'];	//从session中获取当前顾客ID
		$currentdate = date('Ymd');				//取得当前时间（年月日）
		$sql='date(play_time) = '.$currentdate.' AND customer_id = '.$map['customer_id'].' AND e_id = '.$data['e_id'].' AND is_del = 0';	//拼接SQL语句
		$result = M("pluginplayrecord")->where($sql)->count("play_record_id");		//查询是否玩过插件
		if($result){
			$this->ajaxReturn ( array ( 'status' => false ), 'json' );
		}else {
			$this->ajaxReturn ( array ( 'status' => true ), 'json' );
		}
	}
	
	//大转盘玩的记录写入函数
	public function luckyWheelPlaySign(){
		$data = array(
			'e_id' => I('e_id')
		);
		$playSignMap['play_record_id'] = md5 ( uniqid ( rand (), true ) );			//md5生成该顾客32位唯一玩的记录编号
		$playSignMap['e_id'] = $data['e_id'];										//商家编号
		$playSignMap['customer_id'] = $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'];//取得用户编号
		$playSignMap['plugin_activity_id'] = '0011';								//特别注意：此处要写入大转盘的编号，以后可能查表或者直接存名字
		$playSignMap['play_time'] = date ( 'YmdHms' );								//玩的时间
		$playSignMap['play_result'] = '谢谢惠顾';										//特别注意：本句代码还要再改，以后变成接收中奖参数
		$playrecord = M("pluginplayrecord");										//实例化表
		if ($playrecord->create ( $playSignMap )) {
			$result = $playrecord->add ();
			if ($result) {
				// 记录写入成功
				$this->ajaxReturn ( array ( 'status' => true ), 'json' );
			} else {
				// 记录写入失败(数据库写入失败)
				$this->ajaxReturn ( array ( 'status' => false ), 'json' );
			}
		} else {
			$this->error ( $playrecord->getError () );
		}
	}
	
	//用户玩插件记录表
	public function pluginPlay(){
		$data = array(
			'e_id' => I('e_id'),
			'cid' => I('cid'), 
			'cpaid' => I('cpaid'),
			'award' => I('award')
		);
		$playrecord = M("pluginplayrecord");
		$playrecordmap = array(
			'play_record_id' => md5 ( uniqid ( rand (), true ) ),
			'e_id' => $data['e_id'],
			'customer_id' => $data['cid'],
			'plugin_activity_id' => $data['cpaid'],
			'play_time' => date ( 'YmdHms' ),
			'play_result' => $data['award']
		);
		if ( $playrecord ->data ( $playrecordmap )->add ()) {
			$this->ajaxReturn( array('status => 1') ,'json');
		}else{
			$this->ajaxReturn( array('status => 0') ,'json');
		}
	}
	
	//添加许愿处理函数
	public function add() {
		$map = array (
			'e_id' => I ( 'e_id' ),
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
			'wish_id' => md5 ( uniqid ( rand (), true ) ),	//生成32位唯一id码
			'wish_content' => trim ( I ( 'wish_content' ) ),
			'wish_time' => time()
		);
		$wisher = I ( 'wisher' );
		if ($wisher != '') $map ['wisher'] = trim ( $wisher );
		$wish = M ( 'wish' );
		$wish->create ( $map );
		$result = $wish->add ();
		$this->redirect ( "Home/Plugin/myWish?e_id=" . $map ['e_id'] );
	}
	
	//删除许愿处理函数
	public function delwish() {
		$data = array (
			'e_id' => I ( 'e_id' ),
			'wish_id' => I ( 'wish_id' ),
			'customer_id' => $_SESSION ['currentcustomer'] [$this->einfo ['e_id']] ['customer_id'],
			'is_del' => 0
		);
		$result = M ( 'wish' )->where ( $data )->setField ( 'is_del', 1 );
		$this->redirect ( "Home/Plugin/myWish?e_id=" . $data ['e_id'] );
	}
	
	public function winPrizeCommit(){
		$data = array(
			'e_id' => I ( 'e_id' ),
			'cpaid' => I('cpaid'),
			'vsnc' => I ( 'vsnc' ),
			'cid' => I('cid'),
			'tel' => I ( 'tel' )
		);
		//step1：先验证sncode，如果sncode正确，才能中奖
		$checkmap = array(
			'sncode' => $data['vsnc'],
			'is_del' => 0
		);
		$checkresult = M('pluginprize')->where($checkmap)->find();
		if($checkresult){
			//step2：处理中奖
			//step2-1：插入一条数据，用户中奖了
			$winmap = array(
				'getprize_id' => md5 (uniqid (rand(), true)),					//随机生成中奖编号
				'plugin_activity_id' => $data['cpaid'],							//外键，中奖活动编号（方便后台查询中奖信息）
				'plugin_prize_id' => $checkresult['plugin_prize_id'],			//外键：中奖编号
				'customer_id' => $_SESSION['currentcustomer']['customer_id'],	//取当前客户编号
				'contact_number' => $data['tel'],								//存入客户的兑奖联系电话
				'getprize_time' => time(),								//中奖提交时间
				'send_status' => 0,												//默认奖品未发送
				'get_status' =>0												//默认奖品未收到
			);
			$repeatmap = array(
				'plugin_prize_id' => $checkresult['plugin_prize_id'],			//外键：中奖编号
				'customer_id' => $_SESSION['currentcustomer']['customer_id'],	//取当前客户编号
				'is_del' => 0
			);
			$uptable = M('userpluginprize');
			$upcount = $uptable -> where($repeatmap)->count();
			if(!$upcount){
				if ( $uptable ->data ( $winmap )->add ()) {
					//step2-2：获得奖品数目-1
					$pid['plugin_prize_id'] = $checkresult['plugin_prize_id']; 	//获取是哪个奖项
					$inresult = M('pluginprize')->where($pid)->setInc('getprize_amount',1); //中奖数+1
					if($inresult){
						$this->ajaxReturn ( array ( 'status' => 1,'msg'=>'提交中奖信息成功!' ), 'json' );
					}else{
						$this->ajaxReturn ( array ( 'status' => 0,'msg'=>'奖项更新失败!' ), 'json' );
					}
				}else {
					$this->ajaxReturn ( array ( 'status' => 0,'msg'=>'数据库插入出错!' ), 'json' );			// 写入失败(数据库写入失败)，收藏失败返回0
				}
			}
			else{
				$this->ajaxReturn( array( 'status' => 0,'msg' => '请勿重复提交!' ) ,'json');
			}
		}else{
			$this->ajaxReturn( array( 'status' => 0,'msg' => '您的sncode有误，请正确参与游戏!' ) ,'json');	
		}
		
	}
	
	/*----------------------↑处理用户交互的函数Action↑----------------------------*/
	
}
?>