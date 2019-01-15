<?php
/**
 * 导购分组控制器， 该控制器管理导购管理分组用户。
 * @author 微动团队，胡睿,wlk。
 * CreateTime 2015/03/11 16:47:00
 */
class CustomerGroupAction extends PostInterfaceAction {
	
	/**
	 * 从微动数据库读取需要显示的所有组信息以及每个分组下的用户信息(用户进入分组页面)
	 * @param string $guide_id 导购id
	 * @return array
	 */
	public function queryAllGroupCustomer() {
		$guide_id = $this->params ['gid']; // 导购端post过来的参数
		$userGroupList = array ();
		if (empty ( $guide_id )) {
			$this->ajaxresult ['errCode'] = 49001;
			$this->ajaxresult ['errMsg'] = '导购编号gid不能为空';
			exit ( json_encode ( $this->ajaxresult ) );
		} else {
			$sgmap = array(
				'guide_id'=>$guide_id,
				'is_del'=>0		
			);
			$sgfind = M('shopguide')->where($sgmap)->find();
			if(!$sgfind) { // 如果没找到相关的导购信息
				$this->ajaxresult ['errCode'] = 49002;
				$this->ajaxresult ['errMsg'] = '导购ID不存在,请输入一个合法的导购ID';
				exit ( json_encode ( $this->ajaxresult ) );
			}
			
			$e_id = $sgfind['e_id'];
			
			// 1) 先获取该企业下所有可用的用户组信息
			$ugmap = array(
				'e_id' =>$e_id,
				'is_del'=>0		
			);
			$uglist = M('wechatusergroup')->where($ugmap)->order('group_id asc')->select();
			if(!$uglist) {
				$this->ajaxresult ['errCode'] = 49003;
				$this->ajaxresult ['errMsg'] = '无法获取用户组信息,请稍后重试';
				exit ( json_encode ( $this->ajaxresult ) );
			}
			// 2)获取当前导购粉丝所在的所有分组，假使该导购在某个分组下面没有粉丝，那么该分组亦无法显示（尝试建立视图半小时无果后放弃）
			$cusmap = array(
					'guide_id'=>$guide_id,
					'is_del'=>0
			);
			$list = M("guide_customer_group")->where($cusmap)->order('group_id asc')->select();
			if( is_null($list)) {	// 没有任何粉丝
				$this->ajaxresult['errCode'] = 0;
				$this->ajaxresult['errMsg'] = "ok";
				$this->ajaxresult['data']['cusGroup'] = array();
				exit ( json_encode ( $this->ajaxresult ) );
			}
			// 3)循环拼接返回的结果
			$resArray = array();
			$listIndex = 0;
			for( $i = 0; $i < count($uglist); $i++) {
				$group_id = $uglist[$i]['group_id'];	// 本次循环的组ID
				// 首先设置组信息
				$resArray[$group_id]['group_id'] = $group_id;
				$resArray[$group_id]['group_name'] = $uglist[$i]['group_name'];

				$cusIndex = 0;	// 该分组下的当前顾客编号从0开始
				while( $list[$listIndex]['group_id'] == $group_id) {	// 如果该顾客的组号恰巧为当前组

					$resArray[$group_id]['cusArray'][$cusIndex]['e_id'] = $list[$listIndex]['e_id'];
					$resArray[$group_id]['cusArray'][$cusIndex]['subbranch_id'] = $list[$listIndex]['subbranch_id'];
					$resArray[$group_id]['cusArray'][$cusIndex]['guide_id'] = $list[$listIndex]['guide_id'];
					$resArray[$group_id]['cusArray'][$cusIndex]['customer_id'] = $list[$listIndex]['customer_id'];
					$resArray[$group_id]['cusArray'][$cusIndex]['openid'] = $list[$listIndex]['openid'];					
					$resArray[$group_id]['cusArray'][$cusIndex]['nickname'] = $list[$listIndex]['nickname'];
					$resArray[$group_id]['cusArray'][$cusIndex]['guide_remarkname'] = $list[$listIndex]['guide_remarkname'];
					$resArray[$group_id]['cusArray'][$cusIndex]['customer_name'] = $list[$listIndex]['customer_name'];
					$resArray[$group_id]['cusArray'][$cusIndex]['sex'] = $list[$listIndex]['sex'];
					$resArray[$group_id]['cusArray'][$cusIndex]['contact_number'] = $list[$listIndex]['contact_number'];
					$resArray[$group_id]['cusArray'][$cusIndex]['email'] = $list[$listIndex]['email'];
					$resArray[$group_id]['cusArray'][$cusIndex]['birthday'] = $list[$listIndex]['birthday'];
					$resArray[$group_id]['cusArray'][$cusIndex]['customer_address'] = $list[$listIndex]['customer_address'];
					$resArray[$group_id]['cusArray'][$cusIndex]['register_time'] = $list[$listIndex]['register_time'];					
					$resArray[$group_id]['cusArray'][$cusIndex]['original_membercard'] = $list[$listIndex]['original_membercard'];
					$resArray[$group_id]['cusArray'][$cusIndex]['member_level'] = $list[$listIndex]['member_level'];
					$resArray[$group_id]['cusArray'][$cusIndex]['inviter'] = $list[$listIndex]['inviter'];
					$resArray[$group_id]['cusArray'][$cusIndex]['subscribe'] = $list[$listIndex]['subscribe'];
					$resArray[$group_id]['cusArray'][$cusIndex]['language'] = $list[$listIndex]['language'];
					$resArray[$group_id]['cusArray'][$cusIndex]['city'] = $list[$listIndex]['city'];
					$resArray[$group_id]['cusArray'][$cusIndex]['province'] = $list[$listIndex]['province'];
					$resArray[$group_id]['cusArray'][$cusIndex]['country'] = $list[$listIndex]['country'];					
					$resArray[$group_id]['cusArray'][$cusIndex]['head_img_url'] = $list[$listIndex]['head_img_url'];
					$resArray[$group_id]['cusArray'][$cusIndex]['subscribe_time'] = $list[$listIndex]['subscribe_time'];
					$resArray[$group_id]['cusArray'][$cusIndex]['latest_active'] = $list[$listIndex]['latest_active'];
					$resArray[$group_id]['cusArray'][$cusIndex]['remark'] = $list[$listIndex]['remark'];
					$timenow = time (); // 取当前时间
					$last_active_time = $list[$listIndex]['latest_active'];
					// 判别是否活跃
					if ( $timenow - $last_active_time > 0 && $timenow - $last_active_time < 172800) {
						$resArray[$group_id]['cusArray'][$cusIndex]['is_active'] = 1; // 活跃时间正常（非默认值）并且距离当前时间小于48小时，粉丝活跃
					} else {
						$resArray[$group_id]['cusArray'][$cusIndex]['is_active'] = 0; // 粉丝不活跃
					}
					//打包信息，替换数据库字段
					$resArray[$group_id]['cusArray'][$cusIndex]=$this->guideGroupInfoPackage($resArray[$group_id]['cusArray'][$cusIndex]);					
					$cusIndex++;
					$listIndex++;
				}
				// 根据最终的$cusIndex来判断本次循环是否为空循环
				if( $cusIndex == 0) {	// 如果为空循环					
					$resArray[$group_id]['cusArray'] = array();
				}				
			}
			$ajaxArray = array();
			$index = 0;
			//打包信息,将键名改为序号，而非$group_id
			foreach( $resArray as $key=>$value) {
				$ajaxArray[$index] = $resArray[$key];
				$index++;
			}
			$this->ajaxresult['errCode'] = 0;
			$this->ajaxresult['errMsg'] = "ok";
			$this->ajaxresult['data']['cusGroup'] = $ajaxArray;
			
			exit ( json_encode ( $this->ajaxresult ) );
		}
	}
	
	/**
	 * 从微动数据库读取需要显示的所有组信息(用户进入分组页面)
	 * @param string $e_id 商家信息
	 * @return array
	 */
	public function queryAllGroup() {
		$e_id = $this->params ['eid']; // 导购端post过来的参数，这里要注意，一个eid下的所有导购的分组都是一样的，组内粉丝是不同的。
		$userGroupList = array ();
		if (empty ( $e_id )) {
			$this->ajaxresult ['errCode'] = 49002;
			$this->ajaxresult ['errMsg'] = '商家编号eid不能为空';
		} else {
			$userGroupMap = array (
					'e_id' => $e_id,
					'is_del' => 0 
			);
			$userGroupList = M ( "wechatusergroup" )->where ( $userGroupMap )->order ( 'group_id asc' )->field ( 'group_id,group_name,count' )->select();
			if(is_null($userGroupList)){	//如果$userGroupList为空，表示数据库没查询到数据
				$userGroupList = array();
			}
			//打包信息，替换数据库字段
			for($i=0;$i<count($userGroupList);$i++){
				$userGroupList[$i]=$this->guideGroupCustomerInfoPackage($userGroupList[$i]);
			}
			//$userGroupList=$this->guideGroupCustomerInfoPackage($userGroupList);
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = 'ok';
			$this->ajaxresult ['data'] ['userGroupList'] = $userGroupList; //
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 查询某导购某个分组下的顾客信息(用户点击某个分组展开时)
	 * @return array
	 */
	public function queryCustomerGroup() {
		// 获取参数信息
		$e_id = $this->params ['eid'];
		$guide_id = $this->params ['gid'];
		$group_id = $this->params ['groupid'];
		
		// 对参数的校验
		if (empty ( $e_id )) {
			$this->ajaxresult ['errCode'] = 49003;
			$this->ajaxresult ['errMsg'] = '查询分组下导购粉丝接口参数错误，企业编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $guide_id )) {
			$this->ajaxresult ['errCode'] = 49004;
			$this->ajaxresult ['errMsg'] = '查询分组下导购粉丝接口参数错误，导购编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// ❤❤❤特别注意：这里空字符串和0在PHP弱类型比较中空字符串和0相等，三类型比较就不等
		if ($group_id === "") {
			$this->ajaxresult ['errCode'] = 49005;
			$this->ajaxresult ['errMsg'] = '查询分组下导购粉丝接口参数错误，分组编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$userGroupUserList = array ();
		// 首先查询当前$e_id有没有此$group_id的分组
		$egMap = array (
				'e_id' => $e_id,
				'group_id' => $group_id,
				'is_del' => 0 
		);
		$egResult = M ( "wechatusergroup" )->where ( $egMap )->find ();
		if (! $egResult) { 
			$this->ajaxresult ['errCode'] = 49006; // 如果该e_id下不存在该分组,直接返回
			$this->ajaxresult ['errMsg'] = "查询分组下导购粉丝接口参数错误，不存在的分组。";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 下面是得到分组的信息
		$cusmap = array (
				'e_id' => $e_id,
				'guide_id' => $guide_id,
				'group_id' => $group_id,
				'is_del' => 0 
		);
		$groupcustomerlist = M ( "guide_customer_group" )->where ( $cusmap )->select();
		if(is_null($groupcustomerlist)){	//如果数据库里没查找出来返回时null，要把它改为数组的形式
			$groupcustomerlist = array();
		}
		//打包信息，替换数据库字段
		for($i=0;$i<count($groupcustomerlist);$i++){
			$groupcustomerlist[$i]=$this->guideGroupInfoPackage($groupcustomerlist[$i]);
		}
		//$groupcustomerlist=$this->guideCustomerGroupInfoPackage($groupcustomerlist);
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] ['userList'] = $groupcustomerlist; // !!!写个函数封装一下字段
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 修改某个顾客所在的分组
	 * 1、查看该顾客是否属于当前导购，不是的话返回(之前不用校验$customer_id的有效性，如果其无效的话，在customerguide表中也查不出来)
	 * 2、校验分组存不存在，可能在变动的过程中，后台已经将该分组ID删除(此处不是外键，应加上e_id进行同步判断，防止group_id重复)
	 * 2、查看该顾客当前所属的分组，如果是$group_id指向的分组，那么不用做任何改变，否则更新分组ID
	 */
	public function changeCustomerGroup() {
		// 得到当前商家的品牌ID\导购ID
		$e_id = $this->params ['eid']; // 获取商家编号
		$guide_id = $this->params ['gid']; // 获取导购编号
		$customer_id = $this->params ['cid']; // 获取客户编号
		$group_id = $this->params ['togroupid']; // 获取分组编号
		
		if (empty ( $e_id )) {
			$this->ajaxresult ['errCode'] = 49007;
			$this->ajaxresult ['errMsg'] = '修改顾客分组接口参数错误，商家编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $guide_id )) {
			$this->ajaxresult ['errCode'] = 49008;
			$this->ajaxresult ['errMsg'] = '修改顾客分组接口参数错误，导购编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $customer_id )) {
			$this->ajaxresult ['errCode'] = 49009;
			$this->ajaxresult ['errMsg'] = '修改顾客分组接口参数错误，顾客编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// ❤❤❤特别注意：这里空字符串和0在PHP弱类型比较中空字符串和0相等，三类型比较就不等
		if ($group_id === "") {
			$this->ajaxresult ['errCode'] = 49010;
			$this->ajaxresult ['errMsg'] = '修改顾客分组接口参数错误，分组编号不能为空！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		// 1、查看该顾客是否属于当前导购，不是的话返回
		$cgMap = array (
				'customer_id' => $customer_id, 
				'guide_id' => $guide_id, 
				'is_del' => 0 
		);
		$cgResult = M ( "customerguide" )->where ( $cgMap )->find ();
		if (! $cgResult) { 
			// 该顾客已更换导购
			$this->ajaxresult ['errCode'] = 49011;
			$this->ajaxresult ['errMsg'] = "该顾客已更换导购，请稍后重试!";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 2、校验分组ID $group_id存不存在
		$ugMap = array (
				'e_id' => $e_id,
				'group_id' => $group_id,
				'is_del' => 0 
		);
		$ugResult = M ( "wechatusergroup" )->where ( $ugMap )->find ();
		//p(M('wechatusergroup')->getLastSql());die;
		if (! $ugResult) { 
			// 该分组不存在的话
			$this->ajaxresult ['errCode'] = 49012;
			$this->ajaxresult ['errMsg'] = "该分组已经不存在，请稍后重试";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 3、根据视图查看$customer_id所属的分组ID,并与$group_id进行比较
		$cusGroupMap = array (
				'customer_id' => $customer_id,
				'is_del' => 0 
		);
		$cusGroupResult = M ( 'customer_group' )->where ( $cusGroupMap )->find ();
		if (! $cusGroupResult) { // 说明该顾客ID不存在
			$this->ajaxresult ['errCode'] = 49013;
			$this->ajaxresult ['errMsg'] = "该顾客编号不存在，请检查！";
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if ($cusGroupResult ['group_id'] == $group_id) {
			$this->ajaxresult ['errCode'] = 49014;
			$this->ajaxresult ['errMsg'] = "没有切换分组！";
		} else {
			// 调用微信接口同步分组信息，包括本地数据库更新和微信端同步(接口中都已实现)
			$openid = $cusGroupResult ['openid'];
			$emap = array (
					'e_id' => $e_id,
					'is_del' => 0 
			);
			$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
			
			$wechat = A ( 'Service/WeChat' );
			$moveResult = $wechat->moveUserToGroup ( $einfo, $openid, $group_id );
			if ($moveResult) {
				$this->ajaxresult ['errCode'] = 0;
				$this->ajaxresult ['errMsg'] = "OK!";
			}
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	
	/**
	 * 将查询分组粉丝信息字段打包的函数。
	 * @param array $packageinfo 要打包的信息
	 */
	private function guideGroupInfoPackage($packageinfo = NULL) {

		$finalinfo = array (); // 最终打包的信息
		if (! empty ( $packageinfo )) {
			$finalinfo = array (				
					'eid' => isset ( $packageinfo ['e_id'] ) ? $packageinfo ['e_id'] : "", // e_id字段封装为eid
					'sid' => isset ( $packageinfo ['subordinate_shop'] ) ? $packageinfo ['subordinate_shop'] : "", // subordinate_shop字段封装为sid
					'gid' => isset ( $packageinfo ['guide_id'] ) ? $packageinfo ['guide_id'] : "", // guide_id字段封装为gid
					'cid' => isset ( $packageinfo ['customer_id'] ) ? $packageinfo ['customer_id'] : "", // customer_id字段封装为cid
					'openid' => isset ( $packageinfo ['openid'] ) ? $packageinfo ['openid'] : "",
					'nickname' => isset ( $packageinfo ['nickname'] ) ? $packageinfo ['nickname'] : "",
					'guide_remarkname' => isset ( $packageinfo ['guide_remarkname'] ) ? $packageinfo ['guide_remarkname'] : "", // 返回导购对顾客备注名
					'cname' => isset ( $packageinfo ['customer_name'] ) ? $packageinfo ['customer_name'] : "", // customer_name字段封装为cname
					'sex' => isset ( $packageinfo ['sex'] ) ? $packageinfo ['sex'] : 0,
					'cellphone' => isset ( $packageinfo ['contact_number'] ) ? $packageinfo ['contact_number'] : "",
					'email' => isset ( $packageinfo ['email'] ) ? $packageinfo ['email'] : "",
					'birthday' => isset ( $packageinfo ['birthday'] ) ? $packageinfo ['birthday'] : "",
					'address' => isset ( $packageinfo ['customer_address'] ) ? $packageinfo ['customer_address'] : "", // customer_address字段封装为address
					'register_time' => isset ( $packageinfo ['register_time'] ) ? $packageinfo ['register_time'] : -1,
					'membercard' => isset ( $packageinfo ['original_membercard'] ) ? $packageinfo ['original_membercard'] : "", // original_membercard字段封装为membercard
					'level' => isset ( $packageinfo ['member_level'] ) ? $packageinfo ['member_level'] : 0, // member_level字段封装为level
					'inviter' => isset ( $packageinfo ['inviter'] ) ? $packageinfo ['inviter'] : "",
					'subscribe' => isset ( $packageinfo ['subscribe'] ) ? $packageinfo ['subscribe'] : 0,
					'groupid' => isset ( $packageinfo ['group_id'] ) ? $packageinfo ['group_id'] : 0, // group_id字段封装为groupid
					'language' => isset ( $packageinfo ['language'] ) ? $packageinfo ['language'] : "",
					'city' => isset ( $packageinfo ['city'] ) ? $packageinfo ['city'] : "",
					'province' => isset ( $packageinfo ['province'] ) ? $packageinfo ['province'] : "",
					'country' => isset ( $packageinfo ['country'] ) ? $packageinfo ['country'] : "",
					'headimgurl' => isset ( $packageinfo ['head_img_url'] ) ? $packageinfo ['head_img_url'] : "", // head_img_url字段封装为headimgurl
					'subscribe_time' => isset ( $packageinfo ['subscribe_time'] ) ? $packageinfo ['subscribe_time'] : -1,
					'latest_active' => isset ( $packageinfo ['latest_active'] ) ? $packageinfo ['latest_active'] : -1,
					'is_active' => isset ( $packageinfo ['is_active'] ) ? $packageinfo ['is_active'] : 0,
					'remark' => isset ( $packageinfo ['remark'] ) ? $packageinfo ['remark'] : ""					
			);
		}
		return $finalinfo;
	}
	
	/**
	 * 将查询分组信息字段打包的函数。
	 * @param array $packageinfo 要打包的信息
	 */
	private function guideGroupCustomerInfoPackage($packageinfo = NULL) {
		$finalinfo = array (); // 最终打包的信息
		if (! empty ( $packageinfo )) {
			$finalinfo = array (
					'groupid' => isset ( $packageinfo ['group_id'] ) ? $packageinfo ['group_id'] : "", // guide_id字段封装为gid
					'groupname' => isset ( $packageinfo ['group_name'] ) ? $packageinfo ['group_name'] : "", // guide_name字段封装为gname
					'count' => isset ( $packageinfo ['count'] ) ? $packageinfo ['count'] : "",
			);
		}
		return $finalinfo;
	}
	
}