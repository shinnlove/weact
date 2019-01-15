<?php
/**
 * 专属服务控制器。
 * 向三方提供微动平台的微信用户信息、同步等功能；同时接收一些三方发来的数据。
 * @author 微动团队。
 * CreateTime 2015/03/11 16:47:00
 */
class ExclusiveServiceAction extends GuideAppCommonAction {
	
	/**
	 * 获取导购粉丝列表。
	 * 1、粉丝列表必须分页显示，要求三方有本地缓存；
	 * 2、微动平台在查询某导购粉丝的时候，必须有粉丝消费等级的区分；
	 * 3、2的同时，对该粉丝进行一个活跃度的检验；
	 * 
	 */
	public function guideFansList() {
		$e_id = $this->eid; // 接收店编号
		$subbranch_id = $this->sid; // 别人跳转过来，带上url传递参数
		$guide_id = $this->gid; // 接收导购编号
		
		$jsondata = $this->fansListLimit ( $e_id, $subbranch_id, $guide_id, 0, 10, true ); // 调用分页读取函数，第一次读取数据
		$ajaxinfo = json_encode ( $jsondata ); // 因为要把数据打到页面上
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo ); // 因为json包含引号、如果页面上出现的引号不规则，就会引起js报错
		$this->e_id = $e_id;
		$this->subbranch_id = $subbranch_id; // 向前台推送分店编号
		$this->openid = $openid;
		$this->guide_id = $guide_id;
		$this->guidefansjson = $finaljson; // 将起始页的json数据打到页面上
		$this->display ();
	}
	
	/**
	 * 处理前台js分页ajax请求数据的函数，该函数主动调用分页读取粉丝函数
	 * 接收三方搜索参数，搜索某导购的粉丝（按昵称进行搜索并分页返回数据）的ajax处理函数。
	 * 1、微动平台在查询某导购粉丝的时候，必须有粉丝消费等级的区分；
	 * 2、1的同时，对该粉丝进行一个活跃度的检验；
	 */
	public function requestFansList() {
		$e_id = $this->eid; // 接收店编号
		$subbranch_id = $this->sid; // 别人跳转过来，带上url传递参数
		$guide_id = $this->gid; // 接收导购编号
		
		$search_nickname = I ( 'nickname', null ); // 接收待搜索的粉丝昵称
		$ajaxinfo = $this->fansListLimit ( $e_id, $subbranch_id, $guide_id, $_REQUEST ['nextStart'], 10, false, $search_nickname );
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 分页读取导购顾客的处理函数，该函数被页面展示和ajax处理函数调用。
	 * @param string $guide_id 导购编号
	 * @param string $e_id 商家编号
	 * @param string $subbranch_id 分店编号
	 * @param number $startindex 从第几页开始读取
	 * @param number $count 每页读取几条
	 * @param boolean $firstInitData 是否第一次读取数据
	 * @return array $finallist 最终读取的导购粉丝数据列表
	 */
	private function fansListLimit($e_id = '', $subbranch_id = '-1', $guide_id = '', $startindex = 0, $count = 10, $firstInitData = FALSE, $search_nickname = NULL) {
		$finallist = array (); // 最终返回的数据，如果为空返回空数组不返回null
		$customer = M ( "guide_wechat_customer_info" );
		// 建立查询条件
		$map = array (
			'guide_id' => $guide_id,
			'e_id' => $e_id,
			'subbranch_id' => $subbranch_id,
			'internal_staff' => 0,
			'is_del' => 0
		);
		// 如果是检测昵称的话判断条件附加上模糊查询
		if (! empty ( $search_nickname )){
			$map ['guide_remarkname'] = array ( 'like', '%' . $search_nickname . '%' );
		}
		// 得到满足条件的列表
		$customerlist = $customer->where ( $map )->limit ( $startindex, $count )->order ( 'register_time desc' )->select (); // 特别注意：一定要有一定的顺序去读取

		/*此处没看懂同步的意思！！！！
		 * 做一个for循环，将$customerlist中所有顾客（最多10条）的openid取出来，做一个同步
		 * 再次查询数据库得到最新的$customerlist
		 */


		if ($customerlist) {
			// 统计真实得到的条数
			$realcount = count ( $customerlist );
			// 数据的变换（改编），对$customerlist信息进行一定的变换
			for($i = 0; $i < $realcount; $i ++) {
				$customerlist [$i] ['register_time'] = timetodate ( $customerlist [$i] ['register_time'] );
				$customerlist [$i] ['subscribe_time'] = timetodate ( $customerlist [$i] ['subscribe_time'] );
				$customerlist [$i] ['level'] = 23; // 用户的等级状态，这里默认都是23级
				$customerlist [$i] ['active_status'] = 1; // 用户活跃状态，特别注意：这里以后要计算
				if ($customerlist [$i] ['sex'] == 1)
					$customerlist [$i] ['sex']='男';
				else
					$customerlist [$i] ['sex']='女';
			}
			$finallist = $customerlist;
		}
		$ajaxresult = array (
				'data' => array (
						'fansListinfo' => $finallist
				),
				'nextStart' => ($startindex + $realcount)
		);
		
		if (! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}

		return $ajaxresult;
	}
	
	/**
	 * 获取某个导购粉丝的信息，并与本地同步。
	 * 哪个商家、哪个分店的、哪个导购，然后要同步哪个顾客（微信用户）。
	 * 1、接收三方发来的请求参数，进行数据库查询；
	 * 2、微动用户并不一定是最新的，还需要跟微信进行一个同步（有函数fun1已经封装好了）；
	 * 3、要同步回微动本地数据库（fun1的默认形参有个叫autoSync = false，改成true，就会自动同步）；
	 * 4、将数据返回给三方。
	 */
	public function getFansInfo() {
		
	}
	
	/**
	 * 接收导购端APP消息的URL处理函数。
	 * 1、这是微动被动接收APP消息的地址，同时要对APP发来的消息类型有一个区分：
	 * a)文本的：直接存数据库/日志文件，调用微信客服发消息接口转发给微信服务器；
	 * b)图片（有文字有图片）的：接收表单提交过来的多媒体文件（图片），并且新建目录，存放到本地磁盘上，存放OK时，再调用微信上传多媒体接口；
	 * c)语音的：接收表单提交过来的语音媒体，存到本地磁盘上，转发给微信。
	 * 从导购端APP到微信用户的一条通路。
	 */
	public function receiveAppMessage() {
		
	}
	
	/**
	 * 发送微信端用户发来的消息给导购APP（如果用户已经选择过导购）
	 * a)文本的：直接存数据库/日志文件，调用三方接口将消息转发给三方；
	 * b)图片（有文字有图片）的：接收表单提交过来的多媒体文件（图片），并且新建目录，存放到本地磁盘上，存放OK时，再调用三方接口；
	 * c)语音的：接收表单提交过来的语音媒体，存到本地磁盘上，转发给三方。
	 */
	public function sendMessageToApp() {
		$appmsgurl = "http://www.sanfang.com/receiveMsg";
	}
}
?>