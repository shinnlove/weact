<?php
/**
 * 积分商城控制器，管理积分商城的商品。
 * @author Loretta
 * @create time:2015/08/12 15:47:36.
 */
class ScoreExchangeRequestAction extends PCRequestLoginAction {
	
	/**
	 * 展示可供积分兑换的商品。
	 */
	public function getScoreExchangeProduct() {
		
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'add_time'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
		
		$promap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'], // 获取当前商家id，以便显示当前商家的客户
				'score_onshelf' => 1,
				'score_type' => 1, // 允许积分兑换（这个控制是在商品编辑页面）
				'is_del' => 0
		);
		$proinfoview = M ( 'product_image' ); // 定义视图，该视图由商品表和导航类别表连接而成，2015/05/02修改
		$proinfolist = array (); // 商品信息数组
		
		$prototal = $proinfoview->where ( $promap )->count (); // 计算当前商家下的商品总数
		if ($prototal) {
			$proinfolist = $proinfoview->where ( $promap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
		
			for($i = 0; $i < count ( $proinfolist ); $i ++) {
				$proinfolist [$i] ['add_time'] = timetodate ( $proinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$proinfolist [$i] ['latest_modify'] = timetodate ( $proinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$proinfolist [$i] ['score_onshelf_time'] = timetodate ( $proinfolist [$i] ['score_onshelf_time'] ); // 商品上架积分商城时间转为可视化
				$proinfolist [$i] ['macro_path'] = assemblepath ( $proinfolist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$proinfolist [$i] ['micro_path'] = assemblepath ( $proinfolist [$i] ['micro_path'] ); // 特别注意：商品小图拼接路径
			}
		}
		$proinfolist = $this->checkProductSkuWarn ( $proinfolist ); // 检查库存是否报警
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
	/**
	 * easyUI点击加号展开获取商品实时库存信息的ajax处理函数。
	 */
	public function getProductStorage() {
		$prostoremap = array (
				'product_id' => I ( 'pid' ),
				'is_del' => 0
		);
		$storageinfo = M ( 'product_sku' )->where ( $prostoremap )->select (); 
		if ($storageinfo) {
			$ajaxinfo = array (
					'errCode' => 0,
					'errMsg' => 'ok',
					'data' => array (
							'detaillist' => $storageinfo
					)
			);
		} else {
			$ajaxinfo = array (
					'errCode' => 10002,
					'errMsg' => '查询库存信息失败！',
					'data' => array ()
			);
		}
		$this->ajaxReturn( $ajaxinfo );
	}
	
	/**
	 * 从积分商城移除商品。
	 */
	public function removeProduct(){
		$delid = I ( 'proid' ); // 接收要移除的商品id列表
		
		// 检测参数完整性
		if (empty ( $delid )) {
			$this->ajaxresult ['errCode'] = 10003;
			$this->ajaxresult ['errMsg'] = "移除失败，要移除的商品编号不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		$protable = M ( 'product' ); // 商品表
		$delproductmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => $delid,
				'is_del' => 0
		); // 定义移除的范围
		
		$removedata = array (
				'score_onshelf' => 0,
				'score_onshelf_time' => -1 
		); // 设置修改字段
		
		$delproductresult = $protable->where ( $delproductmap )->save ( $removedata ); // 从积分商城下架这件商品
		// 处理结果并返回
		if ($delproductresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10004;
			$this->ajaxresult ['errMsg'] = "从积分商城移除商品失败，请不要重复移除！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 将结果返回给前台
	}
	
	/**
	 * 从积分商城批量移除商品。
	 */
	public function removebatch(){
		$delbatchidlist = I ( 'proidlist' );
		$deloidarray = explode ( ',', $delbatchidlist );
		
		// 检测参数完整性
		if (empty ( $deloidarray )) {
			$this->ajaxresult ['errCode'] = 10005;
			$this->ajaxresult ['errMsg'] = "移除失败，要移除的商品编号不能为空。";
			$this->ajaxReturn ( $this->ajaxresult );
		}
		
		$protable = M ( 'product' ); // 商品表
		
		$delbatchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array ( 'in', $deloidarray ),
				'is_del' => 0
		); // 定义批量移除的范围
		
		$removedata = array (
				'score_onshelf' => 0,
				'score_onshelf_time' => -1 
		); // 设置修改字段
		
		// 将product表中的商品下架积分商城
		$delbatchresult = $protable->where ( $delbatchmap )->save ( $removedata ); // 从积分商城下架这些商品
		// 处理结果并返回
		if ($delbatchresult) {
			$this->ajaxresult ['errCode'] = 0;
			$this->ajaxresult ['errMsg'] = "ok";
		} else {
			$this->ajaxresult ['errCode'] = 10006;
			$this->ajaxresult ['errMsg'] = "从积分商城移除商品失败，请不要重复移除！";
		}
		$this->ajaxReturn ( $this->ajaxresult ); // 将结果返回给前台
	}
	
	/**
	 * 检测商品sku报警的函数checkProductSkuWarn。
	 * 重要函数：读一次数据库，O（n²）检查库存报警函数，经常要调用到。
	 * @param array $productlist 商品数组，一般easyUI传来的是10条左右。
	 * @return array $productlist 带库存报警的商品数组
	 */
	private function checkProductSkuWarn($productlist = NULL) {
		$totalcount = count ( $productlist );
		if ($totalcount == 0 || $totalcount >= 20) return $productlist; // 如果空数据或者数量太大影响数据库效率，直接原样返回不作处理
	
		// Step1：将商品主键预处理
		$checkidlist = ""; // 定义product的id字符串连接
		for ($i = 0; $i < $totalcount - 1; $i ++) {
			$checkidlist .= $productlist [$i] ['product_id'] . ",";
		}
		$checkidlist .= $productlist [$totalcount - 1] ['product_id']; // 拼接最后一个
	
		// Step2：定义搜索的数组，很重要
		$skucheckmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'product_id' => array ( 'in' , $checkidlist ), // 这些商品中
				'sku_storage_left' => array ( 'exp', '<= storage_warn' ), // 单个sku库存小于报警数量的商品
				'is_del' => 0
		);
	
		// Step3：查找出报警库存的product
		$warnprolist = M ( 'product_sku' )->where ( $skucheckmap )->group ( 'product_id' )->select (); // 按商品主键group by去重
	
		// Step4：对形参$productlist添加报警字段
		for ($i = 0; $i < count ( $productlist ); $i ++) {
			$warningflag = false; // 库存报警标志
			for ($j = 0; $j < count ( $warnprolist ); $j ++) {
				if ($productlist [$i] ['product_id'] == $warnprolist [$j] ['product_id']) {
					$warningflag = true; // 匹配商品编号，报名字段置为1
					break;
				}
			}
			if ($warningflag) {
				$productlist [$i] ['warning'] = 1; // 如果库存报警，warning就是1
			} else {
				$productlist [$i] ['warning'] = 0; // 如果库存不报警，warning就是0
			}
		}
	
		return $productlist; // 返回添加报警字段warning的商品列表
	}
	
	/**
	 * 根据不同情形、精确或模糊地根据查找条件查找积分商城商品
	 * 如：商品条形码，商品名称等进行模糊查询；
	 * 导航进行精确查询；库存量等用区间查询。
	 */
	public function searchProduct() {
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'latest_modify'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'desc'; // 排序方式
	
		// 根据不同查询条件定义searchmap
		$condition = I ( 'searchcondition' ); 
		$content = I ( 'searchcontent' );
		
		$searchmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'score_onshelf' => 1,
				'score_type' => 1,
				'is_del' => 0
		);
		if ($condition == "product_number" || $condition == "product_name") {
			$searchmap [$condition] = array ( 'like', '%' . $content . '%' );
		} else if ($condition == "storage_amount") {
			$searchmap [$condition] = array ( 'elt', $content );
		} else if ($condition == "sell_amount") {
			$searchmap [$condition] = array ( 'egt', $content );
		} else if ($condition == "nav_name") {
			if ($content != -1) $searchmap [$condition] = $content; // 搜全部，不限制类别
		}
	
		$proinfoview = M ( 'product_image' ); 
		$proinfolist = array (); // 商品信息数组
	
		$prototal = $proinfoview->where ( $searchmap )->count (); // 计算当前商家下的商品总数
	
		if ($prototal) {
			$proinfolist = $proinfoview->where ( $searchmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select (); // 当前easyUI页数那么多（不是全部）
			for($i = 0; $i < count ( $proinfolist ); $i ++) {
				$proinfolist [$i] ['add_time'] = timetodate ( $proinfolist [$i] ['add_time'] ); // 添加商品时间转为可视化
				$proinfolist [$i] ['latest_modify'] = timetodate ( $proinfolist [$i] ['latest_modify'] ); // 最后一次修改商品信息时间转为可视化
				$proinfolist [$i] ['score_onshelf_time'] = timetodate ( $proinfolist [$i] ['score_onshelf_time'] ); // 商品上架积分商城时间转为可视化
				$proinfolist [$i] ['macro_path'] = assemblepath ( $proinfolist [$i] ['macro_path'] ); // 特别注意：商品大图拼接路径
				$proinfolist [$i] ['micro_path'] = assemblepath ( $proinfolist [$i] ['micro_path'] ); // 特别注意：商品小图拼接路径
			}
		}
		$json = '{"total":' . $prototal . ',"rows":' . json_encode ( $proinfolist ) . '}'; // 打包easyUI格式
		echo $json;
	}
	
}
?>