<?php
/**
 * 此控制器处理微官网的各种设置。
 * 包括：
 * 1、官网首页模板；
 * 2、幻灯片设定；
 * 3、导航文字样式。
 * @author 骆泽刚、赵臣升。
 * 修改日期：2014/09/06 16:25:25.
 * 该控制器对图片的上传、路径的组装都做的非常好。
 */
class MicrowebsiteRequestAction extends PCRequestLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则会出错）。
	 * DefineTime:2014/09/18 21:57:25.
	 * @var string variable DBfield
	 */
	var $table_name = 'slider';
	var $cc_slider_id = 'slider_id';
	var $cc_e_id = 'e_id';
	var $cc_image_path = 'image_path';
	var $cc_target_url = 'target_url';
	var $cc_create_time = 'create_time';
	var $cc_latest_modify = 'latest_modify';
	var $cc_is_del = 'is_del';
	
	/**
	 * 设置官网首页模板函数。
	 */
	public function setIndex() {
		$setinfo = array (
				'setpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				'template_id' => I ( 'selected' )
		);
		$tplManage = A ( 'Admin/TplManage' );
		$result = $tplManage->EntrustTemplate ( $setinfo );
		if ($result) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '提交更改成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10001,
					'errMsg' => '网络繁忙，请稍后再试!'
			);
		}
		$this->ajaxReturn ( $ajaxresult ); // 将信息返回给前台
	}
	
	/**
	 * 读取幻灯片信息readSlider()，返回easyUI数据格式，用post请求读取。
	 */
	public function readSlider() {
		if (! IS_POST) halt ( "Sorry，页面不存在。", U('Admin/Microwebsite/slider','','',true));
			
		// 缩写：slider→sd
		$sdmap = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 从session中取出当前企业信息，取当前商家编号e_id
				$this->cc_is_del => 0
		); // 没有被删除的slider
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : $this->cc_create_time; // 按添加或者修改slider的时间排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 进行一个降序排列（最近添加的显示在最前边）
	
		// 缩写：slider→sd
		$sdtable = M ( $this->table_name );
		$sdlist = array (); // slider数组
		$total = $sdtable->where ( $sdmap )->count (); // 计算该商家的slider数目
		if ($total) {
			$sdlist = $sdtable->where ( $sdmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			// 对slider的路径进行组装
			for($i = 0; $i < count ( $sdlist ); $i ++) {
				$sdlist [$i] [$this->cc_image_path] = assemblepath ( $sdlist [$i] [$this->cc_image_path] ); // 对$sdlist进行路径处理
			}
		}
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $sdlist ) . '}'; // 将数据格式化成easyUI标准格式
		echo $json;
	}
	
	/**
	 * 添加幻灯片处理函数。
	 */
	public function addSliderConfirm() {
		$data = array(
				$this->cc_slider_id => md5 ( uniqid ( rand (), true ) ),		//对幻灯片的主键初始化
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],			//取当前商家编号
				$this->cc_image_path => I('imgp'),
				$this->cc_target_url => I ( 'turl' ),
				$this->cc_create_time => time (),
				'remark' => I ( 'srem' )
		);
		if (empty ( $data [$this->cc_target_url] )) $data [$this->cc_target_url] = '#';	//如果没有设置导航url，直接设置为<a href="#"></a>
	
		$sdtable = M ( $this->table_name );
		$sdresult = $sdtable->data ( $data )->add ();
	
		$ajaxinfo = array();
		if ($sdresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/**
	 * 编辑幻灯片确认处理函数。
	 */
	public function editSliderConfirm(){
		$sliderinfo = array (
				$this->cc_slider_id => I ( 'slid' ) ,						//接收编辑的slider_id
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],			//取当前商家编号e_id
				$this->cc_image_path => I('imgp'),
				$this->cc_target_url => I ( 'turl' ),
				$this->cc_latest_modify => time (),
				'remark' => I ( 'srem' )
		);
	
		$sdtable = M ( $this->table_name );
		$editresult = $sdtable->save ( $sliderinfo );
	
		$ajaxinfo = array();
		if ($editresult) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok'
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10000,
					'errMsg' => "网络繁忙，请稍后再试！"
			);
		}
		$this->ajaxReturn($ajaxinfo);
	}
	
	/**
	 * 删除幻灯片确认。
	 */
	public function delSliderConfirm(){
		$delmap = array (
				$this->cc_slider_id => I ( 'sid' ) //接收要删除的幻灯片主键
		);
		$db_table = M ( $this->table_name );				//数据库表实例化
		$result = $db_table->where ( $delmap )->setField ( $this->cc_is_del, 1 );
		if ($result) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '删除成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '网络繁忙，请稍后再试!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 处理单个图片的webuploader的ajax请求。
	 */
	public function singleUploadHandle() {
		$savepath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/slider/'; // 可以分文件夹存
		$commonhandle = A ( 'Admin/CommonHandle' );
		$this->ajaxReturn ( $commonhandle->threadSingleUpload ( $savepath ) );
	}
	
}
?>