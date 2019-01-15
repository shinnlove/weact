<?php
/**
 * 活动请求控制器。
 * @author Administrator
 *
 */
class ActivityRequestAction extends PCRequestLoginAction {
	
	/**
	 * 获取商品数据
	 */
	public function read() {
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$product = M ( "product_image_list" );
		$total = $product->where ( 'is_del=0 and activity_id is null and e_id='.$current_enterprise['e_id'] )->count (); // 计算总数
		$productlist = array ();
		$productlist = $product->where ( 'is_del=0 and activity_id is null and e_id='.$current_enterprise['e_id'] )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $productlist ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	// 第一步-------添加活动
	// 页面点击下一步添加
	public function addActivity() {
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$current_enterprise = session ( 'curEnterprise' );
		$activity_id = md5 ( uniqid ( rand (), true ) );
		$data = array (
				'activity_id' => $activity_id,
				'e_id' => $current_enterprise ['e_id'],
				'activity_name' => I ( 'activity_name' ),
				'activity_type' => I ( 'activity_type' ),
				'activity_description' => &$_POST [ 'activity_description' ],
				'start_time' => I ( 'start_time' ),
				'end_time' => I ( 'end_time' ),
				'add_time' => date ( 'Y-m-d H:i:s' ),
				'is_del' => 0
		);
	
		// p($data);die;
		if ($data ['activity_type'] == 0 || $data ['activity_type'] == 1) {
			$data ['discount'] = I ( 'discount' );
			$activity = M ( 'activity' );
			$activity->create ( $data );
			$result = $activity->add ();
			// 添加活动结果
			if ($result)
				$this->ajaxReturn ( array (
						'status' => 1,
						'activity_id' => $activity_id
				), 'json' );
				else
					$this->ajaxReturn ( array (
							'status' => 0
					), 'json' );
		} elseif ($data ['activity_type'] == 2) {
				
			$reach_capacitys = I ( 'reach_capacitys' );
			$alleviate_amounts = I ( 'alleviate_amounts' );
			$activity = M ( 'activity' );
			$activity->create ( $data );
			$result = $activity->add ();
				
			$reach_capacity = divide ( $reach_capacitys, ',' );
			$alleviate_amount = divide ( $alleviate_amounts, ',' );
				
			for($i = 0; $i < count ( $reach_capacity ); $i += 1) {
	
				$data2 = array (
						'discount_id' => md5 ( uniqid ( rand (), true ) ),
						'activity_id' => $activity_id,
						'reach_capacity' => $reach_capacity [$i],
						'alleviate_amount' => $alleviate_amount [$i]
				);
				$ladderladderdeduct = M ( 'ladderdeduct' );
				$ladderladderdeduct->create ( $data2 );
				$ladderladderdeduct->add ();
			}
			$this->ajaxReturn ( array (
					'status' => 1,
					'activity_id' => $activity_id
			), 'json' );
		}
	}
	
	/**
	 * 上传图片
	 */
	public function imageUpload(){
		import('ORG.Net.UploadFile');
		$current_enterprise = session ( 'curEnterprise' );
		$upload = new UploadFile();// 实例化上传类
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->autoSub =false ;
		$upload->subType ='date' ;
		$upload->dateFormat ='ymd' ;
		$upload->savePath =  './Updata/images/'.$current_enterprise ['e_id'].'/activity/';// 设置附件上传目录
		//'<script>'.$callback.'('.json_encode($info).')</script>';
		if($upload->upload()){
			$info =  $upload->getUploadFileInfo();
			echo json_encode(array(
					'originalName'=>$info[0]['name'],
					'name'=>$info[0]['savename'],
					'url'=>'/Updata/images/'.$current_enterprise ['e_id'].'/activity/'.$info[0]['savename'],
					'title'=>htmlspecialchars($_POST['container'], ENT_QUOTES),
					'state'=>'SUCCESS'
			));
		}else{
			echo json_encode(array(
					'state'=>$upload->getErrorMsg()
			));
		}
	}
	
	// 第二步-----添加活动商品（每个商品只能参加一个活动）
	public function addActivityProduct() {
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$current_enterprise = session ( 'curEnterprise' );
		$activity_id = I ( 'activity_id' );
		$ids = I ( 'ids' );
		$product_id = divide ( $ids, ',' );
		// 查看activity_id是否有值
		if ($activity_id != null && $activity_id != '') {
			$data ['activity_id'] = $activity_id;
			for($i = 0; $i < count ( $product_id ); $i += 1) {
				$map ['product_id'] = $product_id [$i];
				$map ['e_id'] = $current_enterprise['e_id'];
				D ( 'product' )->where ( $map )->save ( $data );
			}
			$this->ajaxReturn ( array (
					'status' => 1
			), 'json' );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0
			), 'json' );
		}
	}
	
	//read my activity
	public function readMyActivity(){
		if (! IS_POST)
			halt ( "Sorry,页面不存在" );
		$current_enterprise = session ( 'curEnterprise' );
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'is_del';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
	
		$activity = M ( "activity" );
		$total = $activity -> count (); // 计算总数
		$activitylist = array ();
		$activitylist = $activity->where('e_id='.$current_enterprise['e_id'])->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		for ($i = 0 ;$i < count($activitylist); $i += 1){
			if ($activitylist[$i]['activity_type'] == 0)
				$activitylist[$i]['activity'] = '直接打'.$activitylist[$i]['discount'].'折';
			elseif ($activitylist[$i]['activity_type'] == 1)
			$activitylist[$i]['activity'] = '直接减免'.$activitylist[$i]['discount'].'元';
			elseif ($activitylist[$i]['activity_type'] == 2){
				$ladderdeduct = M('ladderdeduct');
				$map = array(
						'activity_id' => $activitylist[$i]['activity_id']
				);
				$ladderdeductlist = $ladderdeduct->where($map)->select();
				for ($j = 0; $j < count($ladderdeductlist); $j += 1)
					$activitylist[$i]['activity'] .= '满'.$ladderdeductlist[$j]['reach_capacity'].'元,减'.$ladderdeductlist[$j]['alleviate_amount'].'元  ';
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $activitylist ) . '}';
		// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	//删除我的活动
	public function delMyActivity(){
		$current_enterprise = session ( 'curEnterprise' );
		$activity_id = I('activity_id');
		$map = array(
				'activity_id' =>$activity_id,
				'e_id'=> $current_enterprise['e_id']
		);
		$activity = M('activity');
		$activity->where($map)->setField('is_del','1');
		$product = M('product');
		$product->where($map)->setField('activity_id',null);
		$this->redirect("Admin/Activity/preMyActivity");
	}
	
}
?>