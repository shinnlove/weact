<?php
/**
 * 网页端请求顾客信息的控制器，该接口类型为GET。
 * @author 胡睿
 * CreateTime:2015/07/08 15:01:36.
 */
class WebChatCustomerAction extends WebChatGetAction {
	
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
		
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
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
					'cellphone' => isset ( $packageinfo ['cellphone'] ) ? $packageinfo ['cellphone'] : "",
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
}
?>