<?php
/**
 * 本控制器负责微营销板块：积分兑换管理。
 * @author 梁思彬。
 * 已对本控制器进行路径组装处理，代码已优化。
 */
class ExchangeRequestAction extends PCRequestLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则前台一改会出错）。
	 * DefineTime:2014/09/20 02:58:25.
	 * @var string	variable	DBfield
	 */
	var $table_name = 'exchangescore';
	var $cc_ex_id = 'exchange_id';
	var $cc_e_id = 'e_id';
	var $cc_ex_name = 'exchange_name';
	var $cc_ex_score = 'exchange_score';
	var $cc_ex_amount = 'exchange_amount';
	var $cc_charged_num = 'charged_num';
	var $cc_ex_imagepath = 'exchange_img_src';
	var $cc_is_del = 'is_del';
	
	/**
	 * preMyExchange页面读取积分兑换信息的post请求。
	 * Author：梁思彬。
	 * 已经对该请求进行路径组装。
	 * ModifyTime：2014/09/20 02:31:25.
	 * Optimizer：赵臣升。
	 */
	public function readExchange() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
		$e_id = $_SESSION ['curEnterprise'] ['e_id'];
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : $this->cc_is_del;
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$exchangescore = M ( "exchangescore" );
		$total = $exchangescore->where ( 'e_id=' . $e_id )->count (); // 计算总数
		$exchangescorelist = array ();
	
		if($total){
			$exchangescorelist = $exchangescore->where ( 'e_id=' . $e_id )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$excnum = count($exchangescorelist);
			for($i=0; $i<$excnum; $i++){
				$exchangescorelist [$i] [$this->cc_ex_imagepath] = assemblepath($exchangescorelist [$i] [$this->cc_ex_imagepath]);	//对$exchangescorelist进行路径处理
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $exchangescorelist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 删除兑换活动的请求。
	 */
	public function delMyExchange() {
		$data = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_ex_id => I ( 'exchange_id' )
		);
		$exchangescore = M ( $this->table_name );
		$delresult = $exchangescore->where ( $data )->setField ( $this->cc_is_del, '1' );
		$this->redirect ( "Admin/Exchange/preMyExchange" );
	}
	
	/**
	 * 所有客户的兑换信息。
	 * Author：梁思彬。
	 * Optimized：赵臣升。
	 * 已经对该请求进行了路径组装。
	 */
	public function customerExchange() {
		if (! IS_POST) halt ( "Sorry,页面不存在" );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10;
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : $this->cc_is_del;
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc';
	
		$data = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id']
		);
		$exchange_id = $_GET ['exchange_id'];
		if ($exchange_id != '') $data [$this->cc_ex_id] = $exchange_id;
	
		$customer_exchange = M ( "customer_exchange" );			//视图，非表
		$total = $customer_exchange->where ( $data )->count (); // 计算总数
		$exchangechangelist = array ();
	
		if($total){
			$exchangechangelist = $customer_exchange->where ( $data )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			$excnum = count($exchangechangelist);
			for($i=0; $i<$excnum; $i++){
				$exchangechangelist [$i] [$this->cc_ex_imagepath] = assemblepath($exchangechangelist [$i] [$this->cc_ex_imagepath]);	//对$exchangechangelist进行路径处理
			}
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $exchangechangelist ) . '}';	// 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 确认用户积分兑换。
	 */
	public function subExchange() {
		$exchange_id = $_GET [ 'exchange_id' ];
	
		$data = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				'user_exchange_id' => I ( 'user_exchange_id' )
		);
		$userexchange = M ( 'userexchange' );
		$userexchange->where ( $data )->setField ( 'is_send', 1 );
		if ($exchange_id != '')
			$this->redirect ( 'Admin/Exchange/precustomerExchange?exchange_id=' . $exchange_id );
		else
			$this->redirect ( 'Admin/Exchange/precustomerExchange' );
	}
	
	/**
	 * 添加积分兑换信息函数。
	 * OriginalAuthor：梁思彬。
	 * OptimizedAuthor：赵臣升。
	 */
	public function addExchange() {
		$exchangeinfo = array(
				$this->cc_ex_id => md5 ( uniqid ( rand (), true ) ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_ex_name => I ( 'exchange_name' ),
				$this->cc_ex_score => I ( 'exchange_score' ),
				$this->cc_ex_amount => I ( 'exchange_amount' ),
				$this->cc_charged_num => 0,
				$this->cc_ex_imagepath => self::exchangeImageHandle()
		);
		$estable = M ( $this->table_name );
		$esresult = $estable->data($exchangeinfo)->add();
		$this->redirect ( "Admin/Exchange/preMyExchange" );
	}
	
	/**
	 * 上传exchange图片并插入数据库的函数。
	 * @return null | array fileinfo	如果上传成功，返回$fileinfos的信息；如果失败，什么都不返回。
	 */
	private function exchangeImageHandle(){
		$savePath = 'Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/exchange/'; 	// 保存路径建议与主文件平级目录或者平级目录的子目录来保存
		$commonhandle = A ( 'Admin/CommonHandle' ); 										// 实例化公有控制器
		$fileinfos = $commonhandle->uploadImage ( $savePath ); 								// 调用上传的uploadImage函数，传入路径
		if ($fileinfos) {
			return '/' . $fileinfos [0] ['savepath'] . $fileinfos [0] ['savename'];			// 成功则返回路径+文件名，记得要带上'/'
		} else {
			return null;
		}
	}
	
}
?>