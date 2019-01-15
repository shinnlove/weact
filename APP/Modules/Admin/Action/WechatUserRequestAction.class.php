<?php
/**
 * 微信平台用户ajax请求处理控制器，本控制器读取微信平台用户数据并同步。
 * @author 赵臣升，黄昀。
 * CreateTime：2014/11/22 14:56:25.
 */
class WechatUserRequestAction extends PCRequestLoginAction {
	
	/**
	 * easyUI分页读取并同步微信分组用户。
	 * 10条数据在4秒左右，请限制前台easyUI不要过多读取用户。
	 */
	public function readGroupUser() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!" );
	
		$einfo = $_SESSION ['curEnterprise']; // 企业信息
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'subscribe_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		// 定义查询条件（必须，确定企业用户的）
		$weusermap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				// 'subscribe'=> 1, // 取消关注的人可能也会重新再关注，再给次机会，也让他可以同步
				'is_del' => 0
		);
		if ((!empty ( $_REQUEST ['gid'] ) && $_REQUEST ['gid'] != '-1') ||$_REQUEST ['gid'] == '0')$weusermap ['group_id'] = $_REQUEST ['gid']; // 接收框架传值限制group
		// easyUI查询本地数据
		$subscriberlist = array ();
		$wuin = M ( 'einfo_wechatuser' );
		
		$total = $wuin->where ( $weusermap )->count (); // 统计数据库总的数据
		if ($total) {
			$subscriberlist = $wuin->where ( $weusermap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 分页查询到的微动数据库数据
			$listnum = count ( $subscriberlist ); // 计算本次同步的人数
			
			$swc = A ( 'Service/WeChat' ); // 新建微信服务层对象
			// 打包本地用户openid列表，准备给服务层批量操作
			for ($i = 0; $i < $listnum; $i ++) {
				$openidlist [$i] = $subscriberlist [$i] ['openid']; // 本地数据库订阅者openid
			}
			$wxinfolist = $swc->batchWeChatUserInfo ( $einfo, $openidlist ); // 调用微信服务层批量读取用户信息接口，比单个调用快多了
			$wxgrouplist = $swc->batchQueryUserGroup ( $einfo, $openidlist ); // 调用微信服务层批量读取用户分组接口，比单个调用快多了
			//p($wxinfolist);die;	
			$remoteinfolist = array (); // 微信服务器端的远程用户信息（最新）
			$localinfolist = array (); // 微动本地的微信用户信息（较新，待同步）
			$unsubscribelist = ""; // 取消关注的用户微信openid列表
			for ($i = 0; $i < $listnum; $i ++) {
				if ($wxinfolist [$i] ['subscribe'] != 0) {
					// 用户关注的情况下才去同步，否则就代表他取消关注
					// 格式化微信远程数据
					$remoteinfolist [$i] = array (
							'group_id' => $wxgrouplist [$i] ['groupid'], // ！！！特别注意这个变量：$wxgrouplist，从微信用户分组接口查询来的
							'subscribe' => $wxinfolist [$i] ['subscribe'],
							'openid' => $wxinfolist [$i] ['openid'],
							'nickname' => $wxinfolist [$i] ['nickname'],
							'sex' => $wxinfolist [$i] ['sex'],
							'language' => $wxinfolist [$i] ['language'],
							'city' => $wxinfolist [$i] ['city'],
							'province' => $wxinfolist [$i] ['province'],
							'country' => $wxinfolist [$i] ['country'],
							'headimgurl' => $wxinfolist [$i] ['headimgurl'], // 注意这里为了筛选不一样才这么做，本地head_img_url是带了下划线的
							'subscribe_time' => $wxinfolist [$i] ['subscribe_time']
					);
	
					// 格式化微动本地数据
					$localinfolist [$i] = array (
							'group_id' => $subscriberlist [$i] ['group_id'], // 微动本地原有的分组
							'subscribe' => $subscriberlist [$i] ['subscribe'],
							'openid' => $subscriberlist [$i] ['openid'],
							'nickname' => $subscriberlist [$i] ['nickname'],
							'sex' => $subscriberlist [$i] ['sex'],
							'language' => $subscriberlist [$i] ['language'],
							'city' => $subscriberlist [$i] ['city'],
							'province' => $subscriberlist [$i] ['province'],
							'country' => $subscriberlist [$i] ['country'],
							'headimgurl' => $subscriberlist [$i] ['head_img_url'], // 注意这里为了筛选不一样才这么做，本地head_img_url是带了下划线的
							'subscribe_time' => $subscriberlist [$i] ['subscribe_time']
					);
				} else {
					// 用户取消关注，则直接将其subscribe置为0；但依然保留他的信息，将其本地副本原样作为新信息
						
					$unsubscribelist .= $subscriberlist [$i] ['openid'] . ","; // 拼接取消关注的用户微信openid列表字符串
						
					// 不更新数据，使用微动本地数据去格式化微信远程数据，这样做，autoPick自动筛选函数会将其归类于original，所以就不会变更信息
					$remoteinfolist [$i] = array (
							'group_id' => $subscriberlist [$i] ['groupid'],
							'subscribe' => 0, // 微信用户不再关注，直接置为0
							'openid' => $subscriberlist [$i] ['openid'],
							'nickname' => $subscriberlist [$i] ['nickname'],
							'sex' => $subscriberlist [$i] ['sex'],
							'language' => $subscriberlist [$i] ['language'],
							'city' => $subscriberlist [$i] ['city'],
							'province' => $subscriberlist [$i] ['province'],
							'country' => $subscriberlist [$i] ['country'],
							'headimgurl' => $subscriberlist [$i] ['head_img_url'], // 注意这里为了筛选不一样才这么做，本地head_img_url是带了下划线的
							'subscribe_time' => $subscriberlist [$i] ['subscribe_time']
					);
						
					// 格式化微动本地数据
					$localinfolist [$i] = $remoteinfolist [$i]; // 原样再赋值给本地数据一下
					$localinfolist [$i] ['subscribe'] = $subscriberlist [$i] ['subscribe']; // 但要注意，这个本地的subscribe依然遵从本地原来的
				}
			}
			// 先处理掉取消关注的人
			if (! empty ( $unsubscribelist )) {
				$unsubscribelist = substr ( $unsubscribelist, 0, strlen ( $unsubscribelist ) - 1 ); // 去掉最后一个逗号
				$cancelmap = array (
						'enterprise_wechat' => $einfo ['original_id'],
						'openid' => array ( 'in' , $unsubscribelist ), // 在取消列表中的
						'is_del' => 0
				);
				$cancelresult = M ( 'wechatuserinfo' )->where ( $updatedate )->setField( 'subscribe', 0 ); // 取消这些人的关注
			}
				
			// 再抽出要更新的人更新
			$pickresult = autoPick ( $remoteinfolist, $localinfolist, 'openid' ); // 利用极速同步算法直接解决两个数组的不同，返回original/update/add/del4中数组情况
			// p($pickresult);die;
			// 更新微信服务器端远程数据到本地（不存在add和del，需要update）
			for($i = 0; $i < count ( $pickresult ['update'] ); $i ++) {
				$updatedate = array (
						'enterprise_wechat' => $einfo ['original_id'],
						'openid' => $pickresult ['update'] [$i] ['openid'],
						'is_del' => 0
				);
				$newinfo = array (
						'group_id' => $pickresult ['update'] [$i] ['group_id'],
						'subscribe' => $pickresult ['update'] [$i] ['subscribe'],
						'nickname' => $pickresult ['update'] [$i] ['nickname'],
						'sex' => $pickresult ['update'] [$i] ['sex'],
						'language' => $pickresult ['update'] [$i] ['language'],
						'city' => $pickresult ['update'] [$i] ['city'],
						'province' => $pickresult ['update'] [$i] ['province'],
						'country' => $pickresult ['update'] [$i] ['country'],
						'head_img_url' => $pickresult ['update'] [$i] ['headimgurl'] // 特别注意这个字段在本地不同
				);
					
				$updateresult = M ( 'wechatuserinfo' )->where ( $updatedate )->save ( $newinfo ); // 更新最新用户信息
			}
				
			$subscriberlist = $wuin->where ( $weusermap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 重查更新后数据重新输出
			for ($i = 0; $i < count ( $subscriberlist ); $i ++) {
				$subscriberlist [$i] ['add_time'] = timetodate ( $subscriberlist [$i] ['add_time'] ); // 格式化添加时间
				$subscriberlist [$i] ['subscribe_time'] = timetodate ( $subscriberlist [$i] ['subscribe_time'] );  // 格式化关注时间
			}
		}
	
		// 打包输出json数据给前台
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $subscriberlist ) . '}';
		echo $json;
	}
	
	//编辑/添加分组，保存新分组名
	public function editMenuConfirm(){
		//p(I());die;
		$e_id = $_SESSION['curEnterprise']['e_id'];
		$einfo['e_id'] = $e_id;
		$group_name = I('group_name');
		$group_id = I('group_id');
		if($group_id==-1){		//表示是添加分组
			$swc = A ( 'Service/WeChat' ); // 新建微信服务层对象
			$result = $swc->createUserGroup($einfo,$group_name);
			if($result){
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
				$this->ajaxReturn ( $this->ajaxresult );
			}else{
				$this->ajaxresult ['errCode'] = 40001;
				$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后重试";
				$this->ajaxReturn ( $this->ajaxresult );
			}
		}else{
			$swc = A ( 'Service/WeChat' ); // 新建微信服务层对象
			$result = $swc->modifyUserGroupName($einfo,$group_id,$group_name);
			if($result){
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "ok";
				$this->ajaxReturn ( $this->ajaxresult );
			}else{
				$this->ajaxresult ['errCode'] = 40001;
				$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后重试";
				$this->ajaxReturn ( $this->ajaxresult );
			}
		}
			
		
	
		$this->ajaxReturn($this->ajaxresult);
		
	}
	
	/**
	 * 删除分组
	 */
	public function delGroup(){
		//p(I());die;
		$group_id = I('group_id');
		$einfo['e_id'] = $_SESSION['curEnterprise']['e_id'];
		$swc = A ( 'Service/WeChat' ); // 新建微信服务层对象
		$result = $swc->deleteGroup($einfo,$group_id);
		if($result){
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
			$this->ajaxReturn ( $this->ajaxresult );
		}else{
			$this->ajaxresult ['errCode'] = 40001;
			$this->ajaxresult ['errMsg'] = "网络繁忙，请稍后重试";
			$this->ajaxReturn ( $this->ajaxresult );
		}
	}
}
?>