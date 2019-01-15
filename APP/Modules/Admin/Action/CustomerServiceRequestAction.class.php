<?php
/**
 * 本控制器处理在线客服事项。
 * CustomerService：在线客服。
 * 2014/06/18 22:30:26
 */
class CustomerServiceRequestAction extends PCRequestLoginAction {
	
	/**
	 * 获取所有在线问题post请求getOnlineQuestion
	 */
	public function getOnlineQuestion(){
		//缩写：onlinequestion→oq
		$oqmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0			//没有被删除的问题
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'question_time';		//按问题提交时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';				//原序排列：先提交的问题先解答
	
		$oqtable = M ( "onlinequestion" );
		$total = $oqtable->where($oqmap)->count(); 										//计算顾客在该商家提出问题数目
		$oqlist = array();
		if($total){
			$oqlist = $oqtable->where ( $oqmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $oqlist ) . '}'; 		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 删除在线问题处理函数delOnlineQuestion
	 */
	public function delOnlineQuestion(){
		$data = array(
				'question_id' => I('qid')
		);
		//缩写：onlinequestion→oq
		$oqtable = M('onlinequestion');
		$oqresult = $oqtable->where($data)->setField('is_del', 1);
		if($oqresult){
			$this->ajaxReturn( array( 'status' => 1, 'msg' => '删除选中问题成功!' ), 'json');
		}else{
			$this->ajaxReturn( array( 'status' => 0, 'msg' => '提交失败，请稍后再试!' ), 'json');
		}
	}
	
	/**
	 * 搜索在线问题post请求searchOnlineQuestion
	 */
	public function searchOnlineQuestion(){
		if (!IS_POST) halt ( "Sorry,页面不存在！" );
	
		$data = array (
				'searchcondition' => I('searchcondition'),				//查询条件是哪个字段
				'searchcontent'	=> I('searchcontent')					//查询的内容
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'question_time';		//按添加刮刮卡时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';				//进行一个降序排列（最近添加的显示在最前边）
	
		//定义要查询的条件$condition
		$condition = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],			//查询哪个店家
				$data['searchcondition'] => array('like','%'.$data['searchcontent'].'%'),		//要查询的字段模糊匹配这个值
				'is_del' => 0											//没有被删除的
		);
	
		//缩写：onlinequestion→oq
		$oqtable = M ( "onlinequestion" );
		$total = $oqtable->where($condition)->count(); 				//计算满足查询条件的问题数
		$oqlist = array();
		if($total){
			$oqlist = $oqtable->where ( $condition )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $oqlist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 点击easyUI加号，展开得到详细回复信息
	 */
	public function getQuestionDetail(){
		$data = array(
				'reply_question_id' => I('qid'),
				'is_del' => 0
		);
	
		//缩写：questionreply→qr
		$qrtable = M('questionreply');								//实例化questionreply表
		$qrfirstresult = $qrtable->where($data)->find();			//查询出回复该问题的第一条回复
		$replymap = array(
				'reply_group_id' => $qrfirstresult['reply_group_id'],	//第一条回复所在组的所有回复或追问
				'is_del' => 0											//没有被删除的回复或追问
		);
		$replyresult = $qrtable->where($replymap)->select();		//把他们找出来
		if($replyresult){
			$this->ajaxReturn($replyresult, 'json');				//如果有数据，以json格式推送到前台
		}else{
			$this->ajaxReturn(array(), 'json');						//如果没有数据
		}
	}
	
	/**
	 * 提交回复客户信息处理AJAX post函数replyQuestionConfirm
	 */
	public function replyQuestionConfirm(){
		$data = array(
				'reply_id' => md5( uniqid( rand(), true) ),
				'reply_question_id' => I('rqid'),
				'replier_id' => $_SESSION['curEnterprise']['e_id'],		//客服回复顾客，所以回复者编号是当前登录的企业
				'replier_name' => $_SESSION['curEnterprise']['account'],//取当前企业的账号名
				'be_replied_person_id' => I('qaid'),
				'reply_time' => date('YmdHms'),
				'reply_content' => I('rc')
		);
		//Step1-2：先查询是否是第一次回复，如果是第一次回复，则要生成组id，然后再插;
		//Step1-2：如果不是第一次回复，查询第一次回复的组id，作为自己的组id，然后再插
		//Step1-3：客服回复顾客，可能是第一次直接回复问题，也可能是回复客户最后一次回复
		//缩写：questionreply→qr
		$qrtable = M('questionreply');
		$qrmap = array(
				'reply_question_id' => $data['reply_question_id']
				//这个地方还是不能用is_del，为了统一组编号，万一记录被删了，还是用这个组
		);
		$qrresult = $qrtable->where($qrmap)->find();
		if($qrresult){
			//Step1-1:有回复过
			$data['reply_group_id'] = $qrresult['reply_group_id'];	//取当前组编号作为自己组编号
			$data['reply_level'] = 1;								//不是沙发，reply_level取1
			//Step1-3：回复的编号是客户最后一次的回复
			$replylastmap = array(
					'reply_group_id' => $qrresult['reply_group_id'],
					'replier_id' => $data['be_replied_person_id'],
					'is_del' => 0		//特别注意：回复的是最后一条没有被删除的，所以要带上is_del=0
			);
			$qrlastresult = $qrtable->where($replylastmap)->order('reply_time desc')->limit(1)->select();	//找出客户最后一条有效回复
			if($qrlastresult){
				$data['reply_question_id'] = $qrlastresult[0]['reply_id'];	//如果这一条最后记录是客户留下的，回复客户最后一次回复编号，而不是自己的回复编号
			}
		}else{
			//Step1-2:没有回复过
			$data['reply_group_id'] = md5( uniqid( rand(), true) );	//自己生成一个组编号
			$data['reply_level'] = 0;								//坐沙发，reply_level取0
		}
		if($data['be_replied_person_id']!=$_SESSION['curEnterprise']['e_id']){
			//缩写：customerinfo→ci
			$cimap = array(
					'customer_id' => $data['be_replied_person_id'],
					'is_del' => 0
			);
			$citable = M('customerinfo');
			$ciresult = $citable->where($cimap)->find();
			$data['be_replied_person_name'] = $ciresult['customer_name'];	//如果回复的是客户（不是回复自己），则直接将被回复人名字设置为客户名字
		}
		if($qrtable->data($data)->add()){
			$this->ajaxReturn( array( 'status' => 1, 'msg' => '回复客户最后一次提问成功!' ), 'json');
		}else{
			$this->ajaxReturn( array( 'status' => 0, 'msg' => '回复失败，请稍后再试!' ), 'json');
		}
	}
	
	/**
	 * 问题追踪页面easyUI查询所有回复
	 */
	public function questionTraceDetail(){
		$data = array(
				'question_id' => I('qid')
				//不能加is_del=0，因为如果第一条记录被删除了，很有可能所有回复都找不到组id了！！！特别注意。而且如果这条记录没被删除，在group中也能找到！
		);
	
		//缩写：questionreply→qr
		$qrtable = M('questionreply');
		$qrresult = $qrtable->where($data)->find();
		$qrmap = array(
				'reply_group_id' => $qrresult['reply_group_id'],
				'is_del' => 0
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'reply_time';			//按回复时间顺序
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';				//先来先到，在前
	
		$total = $qrtable->where($qrmap)->count(); 				//计算满足查询条件的回复数
		$qralllist = array();
		if($total){
			$qralllist = $qrtable->where ( $qrmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $qralllist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 删除问题回复处理函数delQuestionReply
	 */
	public function delQuestionReply(){
		$data = array(
				'reply_id' => I('rid')
		);
	
		//缩写：questionreply→qr
		$qrtable = M('questionreply');
		$qrresult = $qrtable->where($data)->setField('is_del', 1);
		if($qrresult){
			$this->ajaxReturn( array( 'status' => 1, 'msg' => '已经成功删除选中记录!' ), 'json');
		}else{
			$this->ajaxReturn( array( 'status' => 0, 'msg' => '提交失败，请稍后再试!' ), 'json');
		}
	}
	
	
	public function getresponseText() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
	
		//缩写：autoresponse→ar
		$armap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//取当前商家编号
				'response_type' => 'text',
				'is_del' => 0
		);
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'add_response_time';		//按添加文本消息的时间
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';					//进行一个降序排列（最近添加的显示在最前边）
	
		$artable = M ( "autoresponse" );
		$total = $artable->where($armap)->count(); 			//计算满足条件的文本消息总数 */
		$arlist = array();
		if($total){
			$arlist = $artable->where ($armap)->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $arlist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	public function getDetailRt(){
	
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
	
		//缩写：autoresponse->ar
		$armap = array(
				'autoresponse_id' => I('arid'),			//查看文本回复的编号
		);
	
		$artable=M('autoresponse');
		$arresult = $artable->where($armap)->find();  //查询符合以文本回复的所有autoresponse_id
		//p($arresult);die;
		if($arresult['response_function'] == 'responsetext'){//以文本形式回复的消息
				
			$rtdresult = $this->responseByText ($arresult['response_content_id']);
				
		}
		else if($arresult['response_function'] == 'responsenews'){ //以新闻形式回复的消息
				
			$rtdresult = $this->responseByNews ($arresult['response_content_id']);
		}
		else{
			$rtdresult = '';
		}
		$this->ajaxReturn($rtdresult, 'json');				//用ajax返回结构体给前台，数据格式：json（一维数组）
	
	}
	
	private function responseByText($response_content_id){ //以文本形式回复
	
		//缩写：responsetextdetail→rtd
		$rtdmap = array(
				'msgtext_id' => $response_content_id
		);
		$rtdtable = M('msgtext');
		$rtdresult = $rtdtable->where($rtdmap)->find();	//根据msgtext_id查询回复文本内容
		return 	$rtdresult;
	}
	
	private function responseByNews($response_content_id){
	
		//缩写：responsenewsdetail→rnd
		$rndmap = array(
				'msgnews_id' => $response_content_id
		);
		$rndtable = M('msgnewsdetail');
		$rndresult = $rndtable->where($rndmap)->select();	//根据msgnews_id查询所有的回复新闻消息
		return 	$rndresult;
	
	}
	
	public function addResposeContent(){
		//插入新的回复数据
		$responseFunc=I('selectResponseFuc');
		if($responseFunc=='回复文本消息'){
			//msgtext->mt，首先添加文本信息表
			$mttable=M('msgtext');
			$mtdata['msgtext_id']=md5(uniqid (rand(), true)); //生成文本信息的id
			$mtdata['e_id']=$_SESSION['curEnterprise']['e_id'];
			$mtdata['add_time']=date('Y-m-d H:i:s',time());//获取当前时间
			$use=I('selectMsgUse'); //默认设置为0
			switch ($use){
				case "定时推送":
					$mtdata['msg_use']=1;
					break;
				case "被动响应":
					$mtdata['msg_use']=2;
					break;
				case "准备群发":
					$mtdata['msg_use']=3;
					break;
				default:
					$mtdata['msg_use']=4;
			}
			$mtdata['content']=I('textanswer');
			$mtdata['is_del']=0;
			//p($mtdata);die;
			if(!$mttable->add($mtdata)){
				echo '<script>;alert("添加失败！")</script>;';
				return;
			}
			//添加自动回复的表，autoresponse->atr
			$atrtable=M('autoresponse');
			$atrdata['autoresponse_id']=md5(uniqid (rand(), true));//生成自动回复的id
			$atrdata['e_id']=$_SESSION['curEnterprise']['e_id'];
			$atrdata['response_type']='text';
			$atrdata['keyword']=I('textquestion');//关键字
			$atrdata['response_function']='responsetext';
			$atrdata['response_content_id']=$mtdata['msgtext_id'];
			$atrdata['add_response_time']=date('Y-m-d H:i:s',time());
			$atrdata['is_del']=0;
			if($atrtable->add($atrdata)){
				echo '<script>;alert("添加成功!")</script>;';
			}
				
		}
		else if($responseFunc=='回复新闻消息'){
			//一些新闻消息的插入
		}
		else{
			//其他回复消息的插入
		}
		//$this->redirect('Admin/CustomerService/getresponseText');
	}
	
	//删除文本回复
	public function delResponseText(){
		if (! IS_POST) halt ( "Sorry,页面不存在！" );
		//接收删除信息
		$atrmap = array(
				'autoresponse_id' => I('cpaid'),				//删除哪个活动
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//哪个商家的
				'is_del' => 0
		);
		//首先要删除t_msgtext的记录
		$atrtable = M('autoresponse');
		$tempdata = $atrtable->where($atrmap)->find();
		//p($atrtable);die;
		$mgtmap = array(
				'msgtext_id' => $tempdata['response_content_id'],//找到msgtext的id
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//哪个商家的
				'is_del' => 0
		);
		//实例化表开始删除
		$mgttable = M('msgtext');
		$mgtresult = $mgttable->where($mgtmap)->setField('is_del', 1);//设置is_del字段为1，代表删除
		//删除t_autoresponse表的记录
		$atrresult = $atrtable->where($atrmap)->setField('is_del', 1);
	
		if($mgtresult&&$atrresult){
			$this->ajaxReturn( array('status' => '1'), 'json');			//成功返回状态status=1
		}else{
			$this->ajaxReturn( array('status' => '0'), 'json');			//失败返回状态status=0
		}
	}
	
	
}
?>