<?php
/**
 * 本控制器处理后台用户管理这一模块的数据。
 * @author 赵臣升
 * CreateTime：2014/04/10.
 */
class CustomerRequestAction extends PCRequestLoginAction {
	
	/**
	 * 删除客户信息post处理函数。
	 * Author：赵臣升。
	 */
	public function del() {
		$customer_id = I ( 'customer_id' );
		$result = false;
		$customer = M ( "customerinfo" );
		$result = $customer->where ( 'customer_id=' . $customer_id )->setField ( 'is_del', 1 );
		if ($result == false) {
			echo json_encode ( array (
					'msg' => '删除出错！'
			) );
		} else {
			echo json_encode ( array (
					'success' => true
			) );
		}
	}
	
	/**
	 * 修改客户信息
	 */
	public function save() {
		$result = false;
		$customer = M ( 'customerinfo' );
		$customer_id = I ( 'cnum' );
		$data ['customer_name'] = $_REQUEST ['cnam'];
		$data ['nick_name'] = $_REQUEST ['nick'];
		$data ['account'] = $_REQUEST ['cacc'];
		//$data ['password'] = $_REQUEST ['cpas'];
		$data ['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
		$data ['contact_number'] = $_REQUEST ['cpho'];
		$data ['email'] = $_REQUEST ['mail'];
		$data ['sex'] = ($_REQUEST ['csex']==1)?'男' : '女';
		$data ['birthday'] = $_REQUEST ['cbir'];
		$data ['customer_address'] = $_REQUEST ['cadd'];
		$data ['register_time'] = strtotime($_REQUEST ['rtim']);
		$data ['original_membercard'] = $_REQUEST ['card'];
		$data ['member_level'] = $_REQUEST ['clev'];
		$data ['subordinate_shop'] = $_REQUEST ['shop'];
		$result = $customer->where ( 'customer_id=' . $customer_id )->save ( $data );
	
		if ($result) {
			$this->ajaxReturn( array('status' => 1, 'msg' => '已成功更新客户信息!') );
		} else {
			$this->ajaxReturn( array('status' => 0, 'msg' => '更新客户信息失败!') );
		}
		/* if ($result == true) {
		 echo json_encode ( array (
		 'success' => true
		 ) );
			} else {
			echo json_encode ( array (
			'msg' => '更新出错！'
			) );
			}  */
	}
	
	/**
	 * 增加客户信息post函数处理。
	 */
	public function addCustomerConfirm() {
		if(!IS_POST) _404( 'Sorry, 404 Error.', U( 'customerView' ) );
		//$customer_id = generate_uniqueid ();					//已经生成完了用户编号，直接接收插入即可
		$customerdata = array(
				'customer_id' => I('cnum'),
				'customer_name' => I('cnam'),
				'nick_name' => I('nick'),
				'account' => I('cacc'),
				'password' => md5( I('cpas') ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'contact_number' => I('cpho'),
				'email' => I('mail'),
				'sex' => ( I('csex') == 1 ) ? '男' : '女',
				'birthday' => I('cbir'),
				'customer_address' => I('cadd'),
				'register_time' => strtotime(I('rtim')),
				'original_membercard' => I('card'),
				'member_level' => I('clev', 0),
				'subordinate_shop' => I('shop', '-1')
		);
		$result = M ( 'customerinfo' )->add ( $customerdata );
		if ($result) {
			$this->ajaxReturn( array('status' => 1, 'msg' => '已成功添加客户!') );
		} else {
			$this->ajaxReturn( array('status' => 0, 'msg' => '添加客户失败。 请检查网络状况，并不要重复添加!') );
		}
	}
	
	/**
	 * 编辑用户提交确认post处理函数。
	 */
	public function editCustomerConfirm(){
	
	}
	
	/**
	 * easyUI的post请求，初始化读取客户数据。(目前已经修改完整，2014/08/22 14:59:25)
	 */
	public function read() {
		if (! IS_POST) _404 ( "Sorry, 404 Error!",U('Admin/Customer/customerView','','',true));
			
		// 缩写：customerinfo→ci
		$cimap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'is_del' => 0
		);
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'register_time';
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc';
	
		$citable = M ( 'customer_costume_info' );		//此处应该为视图信息，该视图有customerinfo和customerfitting file组成
		$total = $citable->where ( $cimap )->count (); // 计算当前商家下不被删除客户的总数
	
		$sbmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$sbtable = M ( 'subbranch' );
	
		$mlmap = array(
				'level' => '0',
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$mltable = M ( 'memberlevel' );
	
		$customerlist = array ();
		$total = $citable->where ( $cimap )->count();
	
		if($total){
			$customerlist = $citable->where ( $cimap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			$cusnum = count ( $customerlist );
			for($i = 0; $i < $cusnum; $i ++) {
				// 门店信息的处理
				if ($customerlist [$i] ['subordinate_shop'] != null && $customerlist [$i] ['subordinate_shop'] != '-1' && $customerlist [$i] ['subordinate_shop'] != '') {
					$sbmap ['subbranch_id'] = $customerlist [$i] ['subordinate_shop'];
					$sbinfo = $sbtable->where ( $sbmap )->find ();
					if ($sbinfo) {
						$customerlist [$i] ['subordinate_shop'] = $sbinfo ['subbranch_name'];	//门店名称
					} else {
						$customerlist [$i] ['subordinate_shop'] = '暂无门店信息';
					}
				} else {
					$customerlist [$i] ['subordinate_shop'] = '暂无门店信息';
				}
				//会员等级信息的处理
				if($customerlist [$i] ['member_level'] != null && $customerlist [$i] ['member_level'] != ''){
					$mlmap [ 'level' ] = $customerlist [$i] ['member_level'];
					$mlinfo = $mltable -> where($mlmap) -> find();
					if ($mlinfo) {
						$customerlist [$i] ['member_level'] = $mlinfo ['level_name'];           // 会员名称
					}else{
						$customerlist [$i] ['member_level'] = '普通会员';                         // 默认普通会员
					}
				}else {
					$customerlist [$i] ['member_level'] = '普通会员';
				}
				//时间戳转换日期显示的处理
				$customerlist [$i] ['register_time'] = timetodate( $customerlist [$i] ['register_time'] );
			}
		}
		//p($customerlist);die;
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $customerlist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 读取活跃用户列表，待完善
	 */
	public function activeRead() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
			
		// 做视图去查询今天登录的活跃客户，在customerinfo表和loginrecord表间联合查询
		//$today = date ( "Y-m-d" );
		$y = date('Y');
		$m = date('m');
		$d = date('d');
		$todatstart = mktime( 0, 0, 0, $m, $d, $y );
		$todatend = mktime( 23, 59, 59, $m, $d, $y );
		$sql = 'ci.customer_id = lr.customer_id AND lr.e_id = \'' . $_SESSION ['curEnterprise'] ['e_id'] . '\' AND operate_time > ' . $todatstart . ' AND operate_time < ' . $todatend . ' AND ci.is_del = 0 AND lr.is_del = 0';
		$model = new Model (); // 创建视图查询器
		/* ----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓--------------- */
		// 导入分页控件
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'operate_time'; // 按登录、登出的时间
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 进行一个降序排列（最近操作的显示在最前边）
	
		// 缩写：customerinfo→ci
		$cilist = array ();
		$total = $model->table ( 't_customerinfo ci, t_loginrecord lr' )->where ( $sql )->field ( '*' )->count (); // 计算顾客总数
		if ($total){
			$cilist = $model->table ( 't_customerinfo ci, t_loginrecord lr' )->where ( $sql )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->field ( '*' )->select ();
			$cinum = count($cilist);
			for($i=0;$i<$cinum;$i++){
				$cilist [$i] ['operate_time'] = timetodate( $cilist [$i] ['operate_time'] );
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $cilist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 条件查询，模糊匹配查找相应客户。
	 */
	public function conditionQuery() {
		if (! IS_POST) halt ( "Sorry,页面不存在" , U('Admin/Customer/customerView','','',true));
		$data = array (
				'customer_id' => I ( 'customer_id' ),
				'customer_name' => I ( 'customer_name' ),
				'sex' => I ( 'sex' ),
				'customer_address' => I ( 'customer_address' ),
				'email' => I ( 'email' ),
				'nick_name' => I ( 'nick_name' ),
				'contact_number' => I ( 'contact_number' ),
				'member_level' => I ( 'member_level' ),
				'birthdayStart' => I ( 'birthdayStart' ),
				'birthdayEnd' => I ( 'birthdayEnd' ),
				'register_timeStart' => I ( 'register_timeStart' ),
				'register_timeEnd' => I ( 'register_timeEnd' )
		);
	
		if(! empty ( $data ['register_timeStart'] )) $data ['register_timeStart'] = strtotime ($data ['register_timeStart']);
		if(! empty ( $data ['register_timeEnd'] )) {
			$data ['register_timeEnd'] = strtotime($data ['register_timeEnd']);
			//register_timeStart为该天的00:00:00,register_timeEnd也是，但是应该包括这一天，所有添加23:59:59
			$yearEnd = date('Y',$data ['register_timeEnd']);
			$monthEnd = date('m',$data ['register_timeEnd']);
			$dayEnd = date('d',$data ['register_timeEnd']);
			$data ['register_timeEnd'] = mktime(23, 59, 59, $monthEnd, $dayEnd, $yearEnd);
		}
	
		// -------------在此写入任意条件查询的逻辑判断SQL语句--------------------
		$sql = 'is_del = 0  AND e_id = ' . $_SESSION ['curEnterprise'] ['e_id']; // 根据条件查询当前商家的客户。
		if ($data ['customer_id'] != null || $data ['customer_id'] != '')
			$sql = $sql . ' AND customer_id LIKE \'%' . $data ['customer_id'] . '%\'';
		if ($data ['customer_name'] != null || $data ['customer_name'] != '')
			$sql = $sql . ' AND customer_name LIKE \'%' . $data ['customer_name'] . '%\'';
		if ($data ['sex'] != 0){
			if($data ['sex']==1)$data ['sex']='男';
			else if($data ['sex']==2)$data ['sex']='女';
			$sql = $sql . ' AND sex = \'' . $data ['sex'] . '\'';
		}
		if ($data ['customer_address'] != null || $data ['customer_address'] != '')
			$sql = $sql . ' AND customer_address LIKE \'%' . $data ['customer_address'] . '%\'';
		if ($data ['email'] != null || $data ['email'] != '')
			$sql = $sql . ' AND email LIKE \'%' . $data ['email'] . '%\'';
		if ($data ['nick_name'] != null || $data ['nick_name'] != '')
			$sql = $sql . ' AND nick_name LIKE \'%' . $data ['nick_name'] . '%\'';
		if ($data ['contact_number'] != null || $data ['contact_number'] != '')
			$sql = $sql . ' AND contact_number LIKE \'%' . $data ['contact_number'] . '%\'';
		if ($data ['member_level'] != null || $data ['member_level'] != '')
			$sql = $sql . ' AND member_level LIKE \'%' . $data ['member_level'] . '%\'';
		if (($data ['birthdayStart'] != null || $data ['birthdayStart'] != '') && ($data ['birthdayEnd'] != null || $data ['birthdayEnd'] != '')) {
			$sql = birthdayBetweenQuery ( $sql, $data ['birthdayStart'], $data ['birthdayEnd'] );
		} else if (($data ['birthdayStart'] != null || $data ['birthdayStart'] != '') && ($data ['birthdayEnd'] == null || $data ['birthdayEnd'] == '')) {
			$sql = birthdayAfterQuery ( $sql, $data ['birthdayStart'] );
		} else if (($data ['birthdayStart'] == null || $data ['birthdayStart'] == '') && ($data ['birthdayEnd'] != null || $data ['birthdayEnd'] != '')) {
			$sql = birthdayBeforeQuery ( $sql, $data ['birthdayEnd'] );
		}
	
		if (! empty ( $data ['register_timeStart'] ) && ! empty ( $data ['register_timeEnd'] ) ) {
			$sql = registerBetweenQuery ( $sql, $data ['register_timeStart'], $data ['register_timeEnd'] );
		} else if (($data ['register_timeStart'] != null || $data ['register_timeStart'] != '') && ($data ['register_timeEnd'] == null || $data ['register_timeEnd'] == '')) {
			$sql = registerAfterQuery ( $sql, $data ['register_timeStart'] );
		} else if (($data ['register_timeStart'] == null || $data ['register_timeStart'] == '') && ($data ['register_timeEnd'] != null || $data ['register_timeEnd'] != '')) {
			$sql = registerBeforeQuery ( $sql, $data ['register_timeEnd'] );
		}
	
		// -------------在此写入任意条件查询的逻辑判断SQL语句--------------------
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'register_time'; // 排序用代码
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序用代码
		$customer = M ( "customerinfo" );
		$conditionlist = array ();
		$total = $customer->where ( $sql )->count (); // 计算总数
	
		$index = 1; // 商户查寻的索引，如果为1表示第一次查询，每次查询都会重置为1，这样可以保证商户在下次进入页面不查询的情况下，$index++，这样可以判断$index是否为1来判断商户进入页面后是否查询，若没有查询则显示所有用户的信息而不是他之前查询过的用户信息的缓存
		F ( 'data' . $_SESSION ['curEnterprise'] ['e_id'], NUll ); // 一旦商户查询，则之前属于它的缓存都将清空，包括sql条件和查询索引
		F ( 'index' . $_SESSION ['curEnterprise'] ['e_id'], NUll );
		F ( 'data' . $_SESSION ['curEnterprise'] ['e_id'], $sql ); // 将$sql存入用户对应的缓存
		F ( 'index' . $_SESSION ['curEnterprise'] ['e_id'], $index ); // 将$index存入用户对应的缓存
		if ($total) {
			$conditionlist = $customer->where ( $sql )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			//对查询出来的数据进行格式标准化（时间、会员等级、所属门店）
			$sbmap = array (
					'subbranch_id' => '-1',
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$sbtable = M ( 'subbranch' );
				
			$mlmap = array(
					'level' => '0',
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'is_del' => 0
			);
			$mltable = M ( 'memberlevel' );
				
			$connum = count ( $conditionlist );
			for($i = 0; $i < $connum; $i ++) {
				//会员所属分店信息的处理
				if ($conditionlist [$i] ['subordinate_shop'] != null && $conditionlist [$i] ['subordinate_shop'] != '-1' && $conditionlist [$i] ['subordinate_shop'] != '') {
					$sbmap ['subbranch_id'] = $conditionlist [$i] ['subordinate_shop'];
					$sbinfo = $sbtable->where ( $sbmap )->find ();
					if ($sbinfo) {
						$conditionlist [$i] ['subordinate_shop'] = $sbinfo ['subbranch_name'];	//门店名称
					} else {
						$conditionlist [$i] ['subordinate_shop'] = '暂无门店信息';
					}
				} else {
					$conditionlist [$i] ['subordinate_shop'] = '暂无门店信息';
				}
				//会员等级信息的处理
				if($conditionlist [$i] ['member_level'] != null && $conditionlist [$i] ['member_level'] != ''){
					$mlmap [ 'level' ] = $conditionlist [$i] ['member_level'];
					$mlinfo = $mltable -> where($mlmap) -> find();
					if ($mlinfo) {
						$conditionlist [$i] ['member_level'] = $mlinfo ['level_name'];           // 会员名称
					}else{
						$conditionlist [$i] ['member_level'] = '普通会员';                         // 默认普通会员
					}
				}else {
					$conditionlist [$i] ['member_level'] = '普通会员';
				}
				//时间戳转换日期显示的处理
				$conditionlist [$i] ['register_time'] = timetodate( $conditionlist [$i] ['register_time'] );
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $conditionlist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	
	
}
?>