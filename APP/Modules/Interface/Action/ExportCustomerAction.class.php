<?php
/**
 * 本接口提供向三方的对接。
 * @author 黄昀,胡瑞,wlk(测试)
 * CreateTime 2015/03/12 21:20:00
 */
class ExportCustomerAction extends GetInterfaceAction {
	/**
	 * 导购顾客列表接口，一次性读取该导购的所有顾客。
	 */
	public function guideCustomerList() {
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 41002;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$guidecustomermap = array (
				'guide_id' => $this->params ['gid'],
				//'is_del' => 0
		);
		$customerlist = M ( 'guide_wechat_customer_info' )->where ( $guidecustomermap )->order ( "choose_time desc" )->Distinct ( "customer_id" )->select (); // 从视图中找到该导购的顾客列表
		if ($customerlist) {
			$timenow = time (); // 取当前时间
			// 判别是否活跃
			for ($i = 0; $i < count ( $customerlist ); $i ++) {
				if ($timenow - $customerlist [$i] ['latest_active'] > 0 && $timenow - $customerlist [$i] ['latest_active'] < 172800) {
					$customerlist [$i] ['is_active'] = 1; // 活跃时间正常（非默认值）并且距离当前时间小于48小时，粉丝活跃
				} else {
					$customerlist [$i] ['is_active'] = 0; // 粉丝不活跃
				}
				// 格式化时间
				$customerlist [$i] ['register_time'] = timetodate ( $customerlist [$i] ['register_time'] );
				$customerlist [$i] ['subscribe_time'] = timetodate ( $customerlist [$i] ['subscribe_time'] );				
			}		
			$customerlist = $this->customerListPackage ( $customerlist ); // 在for循环外格式化顾客信息列表
		} else {
			$customerlist = array (); // 导购还没有粉丝，就先置为空数组
		}
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = "ok";
		$this->ajaxresult ['data'] = $customerlist;
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
		
	//////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * 获取、同步单个顾客尺码、体型字段接口
	 */
	public function customerSizeInfo() {
		$getfield = "csize"; // 接口字段
		$dbfield = "size"; // 数据库字段;
		$this->getCusFileInfo( $getfield, $dbfield);		
	}
	
	/**
	 * 获取、同步单个顾客个人喜好字段接口
	 */
	public function customerWearPreferInfo() {
		$getfield = "cprefer"; // 接口字段
		$dbfield = "wear_prefer"; // 数据库字段;
		$this->getCusFileInfo( $getfield, $dbfield);
	}
	
	/**
	 * 获取、同步单个顾客详细备注字段接口
	 */
	public function customerDetailNoteInfo(){
		$getfield = "cdetail_remark"; // 接口字段
		$dbfield = "detail_remark"; // 数据库字段;
		$this->getCusFileInfo( $getfield, $dbfield);
	}
	
	/**
	 * 从t_customerfittingfile中获取某导购名下的某顾客的一些个人信息
	 * @param string $interfacefield	ajax返回的接口字段
	 * @param string $localdbfield		对应的本地数据库字段
	 */
	private function getCusFileInfo( $interfacefield = '', $localdbfield = ''){
		// 接收到的post数据中gid如果为空
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}		
		// 判断导购是否真实存在
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$guideinfo = M("shopguide")->where ( $guidemap )->find (); // 查找当前要修改信息的导购
		if(!$guideinfo){
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 接收到的post数据中cid如果为空
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 46106;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少顾客编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 判断顾客是否真实存在
		$cusmap = array (
				'customer_id' => $this->params ['cid'],
				'is_del' => 0
		);
		$cusinfo = M("customerinfo")->where ( $cusmap )->find (); // 查找当前要修改信息的导购
		if(!$cusinfo){
			$this->ajaxresult ['errCode'] = 46107;
			$this->ajaxresult ['errMsg'] = '不存在该顾客！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 判断当前顾客是否仍然在该导购名下
		$cusguidemap = array (
				'customer_id'=>$this->params ['cid'],
				'guide_id'=>$this->params ['gid'],
				'is_del'=>0
		);
		$cusguideResult = M("customerguide")->where($cusguidemap)->find();
		if(!$cusguideResult) {
			$this->ajaxresult ['errCode'] = 46314;
			$this->ajaxresult ['errMsg'] = '该顾客已经选换其他导购,无法获取其信息！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		// 针对需要得到的具体字段，返回相应的值
		$cusfitmap = array(
				'customer_id'=>$this->params['cid'],
				'is_del'=>0
		);
		$cusfitinfo = M("customerfittingfile")->where($cusfitmap)->find();
		if( $cusfitinfo) {	// 如果查找出来有记录 ,那么相应的进行替换就好
			$this->ajaxresult ['data'][$interfacefield] = $cusfitinfo[$localdbfield];
		}		
		else {
			$this->ajaxresult ['data'][$interfacefield] = "";	// 初始化返回的接口字段值为空
		}
		$this->ajaxresult ['errCode'] = 0;
		$this->ajaxresult ['errMsg'] = 'ok';

		exit ( json_encode ( $this->ajaxresult ) );
	}
		
	//////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////
	
	
	/**
	 * 获取、同步单个用户信息接口。
	 */
	public function customerInfo() {
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 41003;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		if (empty ( $this->params ['cid'] )) {
			$this->ajaxresult ['errCode'] = 41002;
			$this->ajaxresult ['errMsg'] = '接口参数错误，缺少客户编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$customermap = array (
				'guide_id' => $this->params ['gid'], // 当前导购的
				'customer_id' => $this->params ['cid'], // 当前顾客
				//'is_del' => 0
		);
		$customerinfo = M ( 'guide_wechat_customer_info' )->where ( $customermap )->find (); // 从视图中查询顾客
		if ($customerinfo) {
			// 如果有这样的顾客信息
			$customerinfo ['register_time'] = timetodate ( $customerinfo ['register_time'] );
			$customerinfo ['subscribe_time'] = timetodate ( $customerinfo ['subscribe_time'] );
			
			$timenow = time (); // 取当前时间
			// 判断是否活跃
			if ( ($timenow - $customerinfo ['latest_active'] > 0 )&& ($timenow - $customerinfo ['latest_active']  < 172800) ) {
				$customerinfo ['is_active'] = 1; // 活跃时间正常（非默认值）并且距离当前时间小于48小时，粉丝活跃
			} else {
				$customerinfo ['is_active'] = 0; // 粉丝不活跃
			}
			$this->ajaxresult ['data'] = $this->customerInfoPackage ( $customerinfo ); // 顾客信息打包
			
			
			/////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////
			/** 此处改动，根据新增表(顾客穿衣档案表t_customerfittingfile)确定尺码/体型、个人喜好、详细备注
			 *	1、初始化需要添加返回的$customerinfo的信息
			 *  2、读取穿衣档案表，如果有信息的话，则将相关信息写入$customerinfo
			 */
			$customerinfo['size'] = "";		// 尺码/体型字段
			$customerinfo['wear_prefer'] = ""; // 个人喜好
			$customerinfo['detail_remark'] = "";	// 对于该顾客的详细备注
			$cusfitmap = array(
				'customer_id'=>$this->params['cid'],
				'is_del'=>0,
			);
			$cusfitinfo = M("customerfittingfile")->where($cusfitmap)->find();
			if( $cusfitinfo) {	// 如果查找出来有记录 ,那么相应的进行替换就好
				$customerinfo['size'] = $cusfitinfo['size'];
				$customerinfo['wear_prefer'] = $cusfitinfo['wear_prefer'];
				$customerinfo['detail_remark'] = $cusfitinfo['detail_remark'];
			}
			$this->ajaxresult['data']['csize'] = $customerinfo['size'];
			$this->ajaxresult['data']['cprefer'] = $customerinfo['wear_prefer'];
			$this->ajaxresult['data']['cdetail_remark'] = $customerinfo['detail_remark'];
			///////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////////////////////////	

			
			
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			// 如果没有这样的顾客信息
			$this->ajaxresult ['errCode'] = 41003;
			$this->ajaxresult ['errMsg'] = '不存在该顾客，或该顾客已经选换其他导购，无法通过当前导购编号获取其信息！';
		}
		exit ( json_encode ( $this->ajaxresult ) );
	}
	
	/**
	 * 顾客信息打包函数。
	 * @param array $packageinfo 要打包的信息
	 */
	private function customerInfoPackage($packageinfo = NULL) {
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
	 * 顾客列表打包函数。
	 * @param array $packagelist 要打包的顾客列表
	 */
	private function customerListPackage($packagelist = NULL) {
		$finallist = array ();
		if (! empty ( $packagelist )) {
			for($i = 0; $i < count ( $packagelist ); $i ++) {
				$finallist [$i] = $this->customerInfoPackage ( $packagelist [$i] );
			}
		}
		return $finallist;
	}
}
?>