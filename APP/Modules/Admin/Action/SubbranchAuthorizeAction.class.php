<?php
/**
 * 分店授权管理控制器
 * @author 胡福玲。
 */
class SubbranchAuthorizeAction extends PCViewLoginAction {
	
	/**
	 * 添加授权信息页面
	 */
	public function addAuthority() {
		$this->a_id = md5 ( uniqid ( rand (), true ) );										// 随机生成唯一的授权编号
		$submap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' =>0
		);
		$subinfo = M('subbranch')->where($submap)->order ( 'add_time asc' )->select();
		$this->sinfo = $subinfo;
		$this->display ();
	}
	
	/**
	 * 编辑分店授权信息页面
	 */
	public function editAuthority() {
	
		$amap = array(
				'auth_id' => I('auth_id'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$ainfo = M('subbranchauth')->where($amap)->find();
	
		$smap = array(
				'subbranch_id' => $ainfo['subbranch_id'],
				'e_id' => $ainfo['e_id'],
				'is_del' => 0
		);
		$sinfo = M('subbranch')->where($smap)->find();
	
		$this->ainfo = $ainfo;
		$this->sinfo = $sinfo;
		$this->display();
	}
	
	/**
	 * ==================================================================================
	 * 以下是微动授权分配企业账号和权限的功能部分，暂放于此20150525
	 */
	
	public function enterpriseAuthorize(){
		$navmap = array(
				'temporary_closed' => 0,
				'is_del' => 0
		);
		$navtbl = M ( "servicenav" );
		$allnavinfo = $navtbl->where($navmap)->select();
		$allnavcount = count ( $allnavinfo ); // 计算下总的导航数量
	
		/* 每一个层级导航视为树结构的一个节点,声明一个$naveach的数据结构,放入该节点的基本信息和下属节点的$naveach信息 */
		$naveach = array ();
		$allnavlist = array (); // 全局总导航
	
		for($i = 0; $i < $allnavcount; $i ++) {
			// 对于每一个读出的导航,先将基本信息放入naveach数组中
			$nav_id = $allnavinfo [$i] ['servicenav_id']; // 得到导航编号
			$naveach [$nav_id] = $allnavinfo [$i]; // 每一个层级导航相关编号下的信息
			// 拼装全局总导航
			if ($allnavinfo [$i] ['father_id'] == '-1') {
				// 挂接顶层
				$allnavlist [] = &$naveach [$nav_id]; // 挂接顶层
	
			} else {
				// 挂接非顶层
				$father_id = $allnavinfo [$i] ['father_id']; // 得到father_id,确定挂到哪儿
				$naveach [$father_id] ['children'] [] = &$naveach [$nav_id];
			}
		}
	
		$servicelistjsondata ['servicelist'] = $allnavlist;
		$this->servicelistjson = jsencode ( $servicelistjsondata ); // 推送json数据
		$this->servicelist = $allnavlist;
		$this->display();
	}
	
	public function showServicenav() {
	
		$version_id = I ( 'ev', '' );
	
		// 根据正确的输入参数确定过滤值
		$ServiceNav = M ( "servicenav" );
		// 查找类别为$version_id 的所有记录
		$map ['is_del'] = 0;
		if ($version_id == 1)
			$map ['service_version_id'] = 'basic00001';
		if ($version_id == 2)
			$map ['_string'] = 'service_version_id="basic00001" OR service_version_id="essential00002"';
		if ($version_id == 3)
			$map ['_string'] = 'service_version_id="basic00001" OR service_version_id="essential00002" OR service_version_id="master00003"';
		// 按照层级、同一层间排好顺序
		$weactAuthList = $ServiceNav->where ( $map)->order ( 'nav_level asc,sort asc')->select ();
	
	}
	
	public function serviceAuthConfirm(){
		//定义多处用到的变量
		$eid = I('eid');//企业编号
		$stime = I('est');
		$etime = I('eet');
	
		//enterprise表
		$enterprise = array(
				'e_id' => $eid,
				'service_version' => I('eve'),
				'service_start_time' => $stime,
				'service_end_time' => $etime,
		);
		$enterpriseresult = M('enterprise')->add($enterprise);
	
		//enterpriseinfo表
		$einfo = array(
				'e_info_id' => md5 ( uniqid( rand (), true ) ),
				'e_id' => $eid,
				'e_name' => I('ena'),
		);
		$einforesult = M('enterpriseinfo')->add($einfo);
	
		//enterpriseservice表
		$servicelist = I ( 'serlist' ); // 服务列表列表'servicenav_id' => I('ena'),
		for($i = 0; $i < count ( $servicelist ); $i ++) {
			$esdata [$i] = array(
					'service_id' => md5 ( uniqid( rand (), true ) ),
					'e_id' => $eid,
					'servicenav_id' => $servicelist [$i],
					'start_date' => $stime,
					'end_date' => $etime,
					'add_time' => time()
			);
		}
		$esresult = M ( 'enterpriseservice' )->addAll ( $esdata ); // 批量一次性插入颜色和尺码，得到$addskuresult的结果
	
		$eptable = M('enterprise');
		$eptable->startTrans(); // 开始事务过程，添加一个商品一共有三个插入步骤
	
	}
	
}
?>