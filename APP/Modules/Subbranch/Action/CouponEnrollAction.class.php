<?php
/**
 * 本控制器属于分店分组下第一个控制器，用以录入用户消费的优惠券。
 * @author 赵臣升，胡福玲，黄昀。
 * CreateTime：2014/10/22 19:09:25.
 */
class CouponEnrollAction extends CommonAction {
	/**
	 * 分店页面视图，需要传参数。
	 */
	public function couponConsume() {
		$this->display ();
	}
	
	/**
	 * 店铺对消费者使用优惠券后的录入提交post处理函数。
	 * Author：黄昀。
	 * CreateTime：2014/10/23 01:25:36.
	 * 特别注意：这里以后所有关于$data ['subbranch_id']的取值都要改成读Session来判断。
	 */
	public function useCoupon() {
		//全局变量
		$data = array (
				'subbranch_id' => $_SESSION ['curSubbranch'] ['subbranch_id'],
				'op_only' => I ( 'pri' )
		);
		
		// 先判断优惠券是否存在（特别注意，前台现在改成不要输用户编号，直接账号密码即可判断）
		$existmap = array(
				'coupon_sncode' => I ( 'csn' ),
				'is_del' => 0
		);
		$exist_coupon = M('customercoupon')->where( $existmap )->find();			//$exist_coupon是根据账号密码查询到的用户优惠券，这也作为录入后要更新的记录
		if (! $exist_coupon) {
			$this->ajaxReturn ( array ( 'status' => 2, 'msg' => '没有查到记录，优惠券不存在!' ), 'json' );
		} else {
			
			// 在此处判断优惠券是否过期：两种方式，根据obsolete_type来选择
			
			// 若存在优惠券并没过期，顺带判断优惠券账号密码是否正确
			if ( $exist_coupon ['coupon_password'] != I ( 'cps' ) ) {
				$this->ajaxReturn ( array ( 'status' => 5, 'msg' => '优惠券编号或密码错误!' ), 'json' );
			}
			// 顺带判断优惠券是否已使用
			if ($exist_coupon ['is_used']) {
				$this->ajaxReturn ( array ( 'status' => 3, 'msg' => '优惠券已使用!' ), 'json' );
			}
			// 如果优惠券还没有被使用：第三步查询出优惠券信息，并判断优惠券性质中的O2O类别，即线下还是线上
			$o2omap = array(
					'coupon_id' => $exist_coupon ['coupon_id'],
					'is_del' => 0
			);
			$coupon_info = M ( 'coupon' )->where ( $o2omap )->find ();				//$coupon_info代表这类优惠券的信息
			if ($coupon_info ['o2o_type'] == 2) {
				$this->ajaxReturn ( array ( 'status' => 4, 'msg' => '此优惠券仅限在线上微平台使用!' ), 'json' );
				// Section Online：线上优惠券，此处接口留出做为以后判断，先返回线上优惠券不在线下使用，  2014/10/23 20:08:23 黄昀备注。
			} else {
				// Section Offline：线下（通用）优惠券的线下部分判断：
				// Offline-Step1：全国与部分：3,4代表部分店铺，要查询优惠券门店表确定匹配度；而等于1或2表示全国，不用查优惠券门店表
				if ($coupon_info ['circulation'] == 3 || $coupon_info ['circulation'] == 4) {
					$subbranchcheck = array(
							'coupon_id' => $coupon_info ['coupon_id'],
							'subbranch_id' => $data ['subbranch_id'],		//这里以后改成读Session
							'is_del' => 0									//没有被添加记录后又修改删除优惠活动的门店
					);
					$current_shop = M ( 'couponsubbranch' )->where ( $subbranchcheck )->find ();
					if (! $current_shop) {
						$this->ajaxReturn ( array ( 'status' => 8, 'msg' => '本店不在此券使用范围内!' ), 'json' );
					}
				}
				// Offline-Step2：是否独立使用：2,4代表线下门店独立使用，要确定该优惠券是否可以在该店使用
				if ($coupon_info ['circulation'] == 2 || $coupon_info ['circulation'] == 4) {
					if($exist_coupon ['subbranch_id'] != $data ['subbranch_id']) {
						$this->ajaxReturn ( array ( 'status' => 9, 'msg' => '此优惠券仅限各门店独立使用，本券并非在本门店所领，无法在本门店使用' ), 'json' );
					}
				}
				// Offline-Step3：优惠券价格限制
				if( $coupon_info ['original_price_only'] && !$data['op_only'] ) {
					$this->ajaxReturn ( array ( 'status' => 6, 'msg' => '此优惠券只能使用在正价商品上' ), 'json' );
				}
				// 线下优惠券判断完毕，可以使用，更新用户的优惠券信息
				$exist_coupon ['is_used'] = 1;
				$exist_coupon ['used_time'] = time ();
				$exist_coupon ['used_subbranch'] = $data ['subbranch_id'];
				$exist_coupon ['used_for'] = I ( 'usf' );					//填写本次使用优惠券的消费信息
				$exist_coupon ['used_remark'] = I ( 'enp' );				//本次录入优惠券的工作人员
				$recordresult = M ( 'customercoupon' )->save ( $exist_coupon );
				if( $recordresult ) {
					$this->ajaxReturn ( array ( 'status' => 1, 'msg' => '优惠券使用成功!' ), 'json' );
				}else{
					$this->ajaxReturn ( array ( 'status' => 7, 'msg' => '网络繁忙，请不要重复提交!' ), 'json' );
				}
			}
		}
	}
	
	/**
	 * 优惠券使用情况视图页面。
	 */
	public function couponRecord() {
		$this->e_id = $_SESSION ['curSubbranch'] ['e_id'];
		$this->subbranch_id = $_SESSION ['curSubbranch'] ['subbranch_id'];
		$this->display();
	}
	
	/**
	 * easyUI的post请求，初始化读取优惠券使用记录数据。
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'couponRecord', '', '', 1, false ) );
	
		$ccmap = array(
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
				'subbranch_id' => $_SESSION ['curSubbranch'] ['subbranch_id'],
				'is_del' => 0,
				'is_used' => 1
		);
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'used_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$sql = 'cc.is_del = 0 and su.is_del = 0 and cc.used_subbranch = su.subbranch_id and cc.e_id = \''.$ccmap['e_id'].'\'';
		$model = new Model();
		$recordlist = array ();
		$recordlist = $model->table('t_customercoupon cc, t_subbranch su')->where($sql)->field('*')->select();
		for($i = 0; $i<count($recordlist); $i++){
			$recordlist [$i] ['get_time'] = timetodate( $recordlist [$i] ['get_time'] );
			$recordlist [$i] ['used_time'] = timetodate( $recordlist [$i] ['used_time'] );
		}
		if ($recordlist) {
			$json = '{"total":' . count( $recordlist ) . ',"rows":' . json_encode ( $recordlist ) . '}';  // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                              // 否则就输出空数组
		}
		echo $json;
	}
	
	/**
	 * 条件查询优惠券消费信息的post处理函数。
	 */
	public function conditionSearchRecord(){
		if (! IS_POST) _404 ( "Sorry, 404 Error!", U ( 'couponRecord', '', '', 1, false ) );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'used_time';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
	
		$conditon = array(
				'e_id' => $_SESSION ['curSubbranch'] ['e_id'],
				'subbranch_id' => $_SESSION ['curSubbranch'] ['subbranch_id'],
				'searchcondition' => I('searchcondition'),
				'searchcontent'	=> I('searchcontent'),
				'is_used' => 1,
				'is_del' => 0
		);
	
		$sql = 'cc.is_del = 0 and su.is_del = 0 and cc.used_subbranch = su.subbranch_id and cc.e_id = \''.$conditon['e_id'].'\' and cc.' . $conditon ['searchcondition'] . ' LIKE \'%' . $conditon ['searchcontent'] . '%\'';
		$model = new Model();
		$recordlist = array ();
		$recordlist = $model->table('t_customercoupon cc, t_subbranch su')->where($sql)->field('*')->select();
		for($i = 0; $i<count($recordlist); $i++){
			$recordlist [$i] ['get_time'] = timetodate( $recordlist [$i] ['get_time'] );
			$recordlist [$i] ['used_time'] = timetodate( $recordlist [$i] ['used_time'] );
		}
		if ($recordlist) {
			$json = '{"total":' . count( $recordlist ) . ',"rows":' . json_encode ( $recordlist ) . '}';  // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		} else {
			$json = json_encode ( array () );                                              // 否则就输出空数组
		}
		echo $json;
	}
}
?>