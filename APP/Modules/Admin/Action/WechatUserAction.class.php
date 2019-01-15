<?php
/**
 * 微信平台用户控制器，本控制器读取微信平台用户数据并同步。
 * @author 赵臣升，黄昀。
 * CreateTime：2014/11/22 14:56:25.
 */
class WechatUserAction extends PCViewLoginAction {
	
	/**
	 * 微信用户分组视图（父框架）。
	 */
	public function wechatUser() {
		// Step1：实例化微信服务层对象，调取当前企业用户的用户组信息
		$swc = A ( 'Service/WeChat' ); // 实例化微信服务层对象
		$result = $swc->queryAllGroup ( $_SESSION ['curEnterprise'] ); // 查询微信公众号所有用户分组
	
		// Step2：格式化key字段
		$usergrouplist = array (); // 用户分组信息
		if (! empty ( $result ['groups'] )) {
			foreach($result ['groups'] as $singlegroup) {
				$temp ['id'] = $singlegroup ['id'];
				$temp ['text'] = $singlegroup ['name'];
				$temp ['count'] = $singlegroup ['count'];
				array_push ( $usergrouplist, $temp );
			}
		}
	
		// Step3：推送到前台
		$this->usergrouplist = jsencode ( $usergrouplist ); // 记得压缩成json形式
		$this->display ();
	}
	
	/**
	 * 微信用户easyUI页面视图（子框架）。
	 */
	public function userGroup() {
		$gid = I('gid');
		$gname = I('gname');
		$this->simpleSyncWeChatUser (); // 简单同步一次用户
		$this->gid = $gid;
		$this->gname = $gname;
		$this->display ();
	}
	
	/* -------------以下为微信接口测试区------------ */
	
	/**
	 * 本函数是给品牌商后台管理人员极速同步（数据冷启动的），平时最佳的同步是放在用户端。
	 * 极速同步：对微信所有用户进行一个简单的同步：
	 * 4万5用户（2万5新增）10秒左右，消耗内存130~180MB左右，其中哈希函数执行完4万条比对用时3.79~5.66秒之间，
	 * 一次性插入41794个微信用户，用时6.9456,6.9762,6.9008秒左右，消耗内存43.1MB。
	 */
	public function simpleSyncWeChatUser() {
	
		/* G('begin'); // 开始测试性能 */
	
		$swc = A ( 'Service/WeChat' ); // 实例化服务层类对象
		$einfo = $_SESSION ['curEnterprise']; // 企业信息
	
		// Step1：查询微信用户信息
		$allwechatuser = $swc->allSubscriber ( $einfo ); // 调用服务层查询所有关注者列表（该数据量可能会在万级别，对大型企业G5G6等）
		$openidlist = $allwechatuser ['data'] ['openid']; // 要同步的微信用户openid列表
		$listnum = count ( $openidlist ); // 统计经过微信公众平台返回的微信用户数量
	
		// Step2：查询本地微动数据并格式化用户信息
		$localusermap = array (
				'enterprise_wechat' => $einfo ['original_id'],
				'is_del' => 0
		);
		$localuserlist = M ( 'wechatuserinfo' )->where ( $localusermap )->select ();
	
		// Step3：哈希散列同步
		$pickresult = hashUserPick ( $openidlist, $localuserlist, 'openid' ); // 哈希散列
	
		// Step4：只做增原则：处理公众号增加的用户信息（add），用户等不起
		$CONST_MAX_ADDNUM = C ( 'MAX_ADD_COUNT' ); // 读取配置中最大的一次性插入数据允许条数
		$totaladdnum = count ( $pickresult ['add'] ); // 总的添加数量
		if ($totaladdnum) {
			$leftaddcount = $totaladdnum % $CONST_MAX_ADDNUM; // 先计算增加操作的余数
			$availabledivide = $totaladdnum - $leftaddcount; // 去掉余数能整除的数
			$addrecyclecount = intval ( $availabledivide / $CONST_MAX_ADDNUM ); // 计算增加操作的循环次数，保险起见转整数（小心PHP浮点型进入循环）
				
			// 根据addAll每次的最大次数来循环插入
			for($j = 0; $j < $addrecyclecount; $j ++) {
				$addrecycle = null; // 每次都将循环数组放空
				for($i = 0; $i < $CONST_MAX_ADDNUM; $i ++) {
					$addrecycle [$i] = array (
							'user_info_id' => md5 ( uniqid ( rand (), true ) ),
							'enterprise_wechat' => $einfo ['original_id'],
							'group_id' => 0, // 新增用户先默认为0，未分组，日后再同步
							'subscribe' => 1, // 新增subsribe肯定是1
							'openid' => $pickresult ['add'] [$j * $CONST_MAX_ADDNUM + $i],
							'subscribe_time' => time (),
							'add_time' => time ()
					);
				}
				$addrecycleresult = M ( 'wechatuserinfo' )->addAll ( $addrecycle ); // 循环以数据库最大承受能力来批量插入信息，数据库读写循环的次数
			}
			// 插入余下的数据
			$addleft = array (); // 剩下要插入的余数信息数组
			for($i = 0; $i < $leftaddcount; $i ++) {
				$addleft [$i] = array (
						'user_info_id' => md5 ( uniqid ( rand (), true ) ),
						'enterprise_wechat' => $einfo ['original_id'],
						'group_id' => 0, // 新增用户先默认为0，未分组，日后再同步
						'subscribe' => 1,
						'openid' => $pickresult ['add'] [$addrecyclecount * $CONST_MAX_ADDNUM + $i],
						'subscribe_time' => time (),
						'add_time' => time ()
				);
			}
			$addleftresult = M ( 'wechatuserinfo' )->addAll ( $addleft ); // 一次性插入余下的信息，数据库读写1次
		}
	
		/* G('end'); // 测试性能结束
	
		p("执行本段同步代码性能测试结果（公众号用户组的同步、微信用户及所属组信息同步）：\n总计用时：" . G('begin','end') . "s，花销内存：" . G('begin','end','m') . "kb。");die; */
	
	}
	
	// 同步组和用户的代码，超过3万没有半小时搞不定
	public function syncWeChatUser() {
	
		G('begin'); // 开始测试性能
	
		$swc = A ( 'Service/WeChat' ); // 实例化服务层类对象
		$einfo = $_SESSION ['curEnterprise']; // 企业信息
	
		// Step1：同步公众号分组信息
		$allgroupinfo = $swc->queryAllGroup ( $einfo ); // 调用服务层查询所有分组函数（该函数同时进行一次分组大同步）
	
		// Step2：同步组内用户信息
		$allwechatuser = $swc->allSubscriber ( $einfo ); // 调用服务层查询所有关注者列表（该数据量可能会在万级别，对大型企业G5G6等）
	
		// Step3：查询微信用户的分组并格式化组信息
		$listnum = count ( $allwechatuser ['data'] ['openid'] ); // 统计经过微信公众平台返回的微信用户数量
		$openidlist = array (); // 要同步的微信用户openid列表
		$remoteuserinfo = array (); // 微信公众平台远程格式化用户数据
		for ($i = 0; $i < $listnum; $i ++) {
			$openidlist [$i] = $allwechatuser ['data'] ['openid'] [$i]; // 给到$openidlist
		}
		$wxgrouplist = $swc->batchQueryUserGroup ( $einfo, $openidlist ); // 调用微信服务层批量读取用户分组接口，比单个调用快多了
		// 格式化微信平台信息
		for ($i = 0; $i < $listnum; $i ++) {
			$remoteuserinfo [$i] = array (
					'openid' => $allwechatuser ['data'] ['openid'] [$i],
					'group_id' => $wxgrouplist [$i] ['groupid']
			); // 微信公众平台的用户信息
		}
	
		// Step4：查询本地微动数据并格式化用户信息
		$localusermap = array (
				'enterprise_wechat' => $einfo ['original_id'],
				'is_del' => 0
		);
		$localuserlist = M ( 'wechatuserinfo' )->where ( $localusermap )->select ();
		$localuserinfo = array (); // 本地格式化用户数据
		for($i = 0; $i < count ( $localuserlist ); $i ++) {
			$localuserinfo [$i] = array (
					'openid' => $localuserlist [$i] ['openid'],
					'group_id' => $localuserlist [$i] ['group_id']
			);
		}
		// p($remoteuserinfo);p($localuserinfo);die;
		$resultuserarray = autoPick ( $remoteuserinfo, $localuserinfo, 'openid' ); // 利用极速同步算法直接解决两个数组的不同，返回original/update/add/del4中数组情况
		// p($resultuserarray);die;
	
		// Step5：先删原则：先处理取消关注/删除的用户信息（delete）
		for($i = 0; $i < count ( $resultuserarray ['del'] ); $i ++) {
			$deletemap = array (
					'enterprise_wechat' => $einfo ['original_id'],
					'openid' => $resultuserarray ['del'] [$i] ['openid'],
					'subscribe' => 1, // 对已经是0的则不再操作
					'is_del' => 0
			);
			//$deleteresult = M ( 'wechatuserinfo' )->where ( $deletemap )->setField ( 'is_del', 1 ); // 本来是要逻辑删除这些人的
			$deleteresult = M ( 'wechatuserinfo' )->where ( $deletemap )->setField ( 'subscribe', 0 ); // 将这些人置为未关注
		}
	
		// Step6：再改原则：再处理修改的用户信息(update)，纯粹修改一个分组信息（量不会很大的，除非是批量代码移动分组，那也在移动的时候已经同步好了，见服务层的WeChatAction）
		for($i = 0; $i < count ( $resultuserarray ['update'] ); $i ++) {
			$updatemap = array (
					'enterprise_wechat' => $einfo ['original_id'],
					'openid' => $resultuserarray ['update'] [$i] ['openid'],
					'is_del' => 0,
			);
			$updateresult = M ( 'wechatuserinfo' )->where ( $updatemap )->setField ( 'group_id', $resultuserarray ['update'] [$i] ['group_id'] ); // 变更其组信息
		}
	
		// Step7：最后增原则：处理增加的用户信息（add）
		$CONST_MAX_ADDNUM = C ( 'MAX_ADD_COUNT' ); // 读取配置中最大的一次性插入数据允许条数
		$totaladdnum = count ( $resultuserarray ['add'] ); // 总的添加数量
		$leftaddcount = $totaladdnum % $CONST_MAX_ADDNUM; // 先计算增加操作的余数
		$availabledivide = $totaladdnum - $leftaddcount; // 去掉余数能整除的数
		$addrecyclecount = intval ( $availabledivide / $CONST_MAX_ADDNUM ); // 计算增加操作的循环次数，保险起见转整数（小心PHP浮点型进入循环）
		// 根据addAll每次的最大次数来循环插入
		for($j = 0; $j < $addrecyclecount; $j ++) {
			$addrecycle = null; // 每次都将循环数组放空
			for($i = 0; $i < $CONST_MAX_ADDNUM; $i ++) {
				$addrecycle [$i] = array (
						'user_info_id' => md5 ( uniqid ( rand (), true ) ),
						'enterprise_wechat' => $einfo ['original_id'],
						'group_id' => $resultuserarray ['add'] [$j * $CONST_MAX_ADDNUM + $i] ['group_id'],
						'subscribe' => 1, // 新增subsribe肯定是1
						'openid' => $resultuserarray ['add'] [$j * $CONST_MAX_ADDNUM + $i] ['openid'],
						'subscribe_time' => time (),
						'add_time' => time ()
				);
			}
			$addrecycleresult = M ( 'wechatuserinfo' )->addAll ( $addrecycle ); // 循环以数据库最大承受能力来批量插入信息，数据库读写循环的次数
		}
		// 插入余下的数据
		$addleft = array (); // 剩下要插入的余数信息数组
		for($i = 0; $i < $leftaddcount; $i ++) {
			$addleft [$i] = array (
					'user_info_id' => md5 ( uniqid ( rand (), true ) ),
					'enterprise_wechat' => $einfo ['original_id'],
					'group_id' => $resultuserarray ['add'] [$addrecyclecount * $CONST_MAX_ADDNUM + $i] ['group_id'],
					'subscribe' => 1,
					'openid' => $resultuserarray ['add'] [$addrecyclecount * $CONST_MAX_ADDNUM + $i] ['openid'],
					'subscribe_time' => time (),
					'add_time' => time ()
			);
		}
		$addleftresult = M ( 'wechatuserinfo' )->addAll ( $addleft ); // 一次性插入余下的信息，数据库读写1次
	
		G('end'); // 测试性能结束
	
		p("执行本段同步代码性能测试结果（公众号用户组的同步、微信用户及所属组信息同步）：\n总计用时：" . G('begin','end') . "s，花销内存：" . G('begin','end','m') . "kb。");die;
	}
	
	
	/**
	 * 以下为对分组菜单操作
	 */
	//编辑分组名
	public function editGroup(){
		$group_id = I('mid',-1);
		$group_name = I('group_name');
		$this->group_id = $group_id;
		$this->group_name = $group_name;
		$this->display();
	}
	
}
?>