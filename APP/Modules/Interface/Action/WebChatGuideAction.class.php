<?php
/**
 * 网页端请求导购信息的控制器，该接口类型为GET。
 * @author 赵臣升
 * CreateTime:2015/07/08 15:01:36.
 */
class WebChatGuideAction extends WebChatGetAction {
	
	/**
	 * 请求导购信息。
	 */
	public function guideInfo() {
		if (empty ( $this->params ['gid'] )) {
			$this->ajaxresult ['errCode'] = 46104;
			$this->ajaxresult ['errMsg'] = '查询导购接口参数错误，缺少导购编号！';
			exit ( json_encode ( $this->ajaxresult ) );
		}
		
		$guidemap = array (
				'guide_id' => $this->params ['gid'],
				'is_del' => 0
		);
		$guideinfo = M ( 'shopguide_subbranch' )->where ( $guidemap )->find (); // 查找相应导购信息
		if ($guideinfo) {
			// 导购信息
			$guideinfo ['add_time'] = timetodate ( $guideinfo ['add_time'] );
			$guideinfo ['latest_modify'] = timetodate ( $guideinfo ['latest_modify'] );
			$guideinfo ['dimension_code'] = assemblepath ( $guideinfo ['dimension_code'], true ); // 导购二维码，绝对地址
			$guideinfo ['headimg'] = assemblepath ( $guideinfo ['headimg'], true ); // 导购头像，绝对地址
			$guideinfo ['image_path'] = assemblepath ( $guideinfo ['image_path'], true ); // 分店图片，绝对地址
				
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = 'ok';
			$this->ajaxresult ['data'] = $this->guideLoginInfoPackage ( $guideinfo );
		} else {
			$this->ajaxresult ['errCode'] = 46105;
			$this->ajaxresult ['errMsg'] = '不存在该导购！';
		}
		if ($this->datatype == "jsonp") {
			echo $this->callback . "(" . json_encode ( $this->ajaxresult ) . ")"; // jsonp格式
		} else {
			exit ( json_encode ( $this->ajaxresult ) ); // 普通的json格式
		}
	}
	
	/**
	 * 将导购登录信息字段打包的函数。
	 * @param array $packageinfo 要打包的信息
	 */
	private function guideLoginInfoPackage($packageinfo = NULL) {
		$finalinfo = array (); // 最终打包的信息
		if (! empty ( $packageinfo )) {
			$finalinfo = array (
					'gid' => isset ( $packageinfo ['guide_id'] ) ? $packageinfo ['guide_id'] : "", // guide_id字段封装为gid
					'eid' => isset ( $packageinfo ['e_id'] ) ? $packageinfo ['e_id'] : "", // e_id字段封装为eid
					'sid' => isset ( $packageinfo ['subbranch_id'] ) ? $packageinfo ['subbranch_id'] : "", // subbranch_id字段封装为sid
					'gnumber' => isset ( $packageinfo ['guide_number'] ) ? $packageinfo ['guide_number'] : "", // guide_number字段封装为gnumber
					'gname' => isset ( $packageinfo ['guide_name'] ) ? $packageinfo ['guide_name'] : "", // guide_name字段封装为gname
					'nickname' => isset ( $packageinfo ['nickname'] ) ? $packageinfo ['nickname'] : "", // 导购昵称
					'sex' => isset ( $packageinfo ['sex'] ) ? $packageinfo ['sex'] : 0,
					'idcard' => isset ( $packageinfo ['id_card'] ) ? $packageinfo ['id_card'] : "", // id_card字段封装为idcard
					'birthday' => isset ( $packageinfo ['birthday'] ) ? $packageinfo ['birthday'] : "",
					'cellphone' => isset ( $packageinfo ['cellphone'] ) ? $packageinfo ['cellphone'] : "",
					'signature' => isset ( $packageinfo ['signature'] ) ? $packageinfo ['signature'] : "",
					'qrcode' => isset ( $packageinfo ['dimension_code'] ) ? $packageinfo ['dimension_code'] : "", // dimension_code字段封装为qrcode
					'headimg' => isset ( $packageinfo ['headimg'] ) ? $packageinfo ['headimg'] : "",
					'level' => isset ( $packageinfo ['guide_level'] ) ? $packageinfo ['guide_level'] : 0, // guide_level字段封装为level
					'type' => isset ( $packageinfo ['guide_type'] ) ? $packageinfo ['guide_type'] : 0, // guide_type字段封装为type
					'status' => isset ( $packageinfo ['busy_status'] ) ? $packageinfo ['busy_status'] : 1, // busy_status字段封装为status
					'star' => isset ( $packageinfo ['star_level'] ) ? $packageinfo ['star_level'] : 0.00, // 增加导购星级评定
					'sname' => isset ( $packageinfo ['subbranch_name'] ) ? $packageinfo ['subbranch_name'] : "", // subbranch_name字段封装为sname
					'brand' => isset ( $packageinfo ['subbranch_brand'] ) ? $packageinfo ['subbranch_brand'] : "", // subbranch_brand字段封装为brand
					'province' => isset ( $packageinfo ['province'] ) ? $packageinfo ['province'] : "",
					'city' => isset ( $packageinfo ['city'] ) ? $packageinfo ['city'] : "",
					'county' => isset ( $packageinfo ['county'] ) ? $packageinfo ['county'] : "",
					'address' => isset ( $packageinfo ['subbranch_address'] ) ? $packageinfo ['subbranch_address'] : "", // subbranch_address字段封装为address
					'stype' => isset ( $packageinfo ['subbranch_type'] ) ? $packageinfo ['subbranch_type'] : 0, // subbranch_type字段封装为stype
					'manager' => isset ( $packageinfo ['manager'] ) ? $packageinfo ['manager'] : "", // manager字段封装为manager
					'simg' => isset ( $packageinfo ['image_path'] ) ? $packageinfo ['image_path'] : "" // image_path字段封装为simg
			);
		}
		return $finalinfo;
	}
	
}
?>