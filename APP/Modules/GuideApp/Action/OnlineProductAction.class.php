<?php
/**
 * 导购APP库存信息功能，
 * 该功能完成线上商品的搜索、加入推荐夹。
 * @author 微动团队。
 * CreateTime 2015/03/11 16:47:00
 */
class OnlineProductAction extends GuideAppCommonAction {
	/**
	 * 线上商品库存信息视图。
	 *
	 * 双方对接方式。
	 * 该页面由三方公司的APP主界面跳转过来到微动平台下，必须的参数是：
	 * a)商家编号；b)分店编号；c)导购编号；
	 * 微动接收这三个参数，并且进行相关商家的线上商品读取。
	 *
	 * 初始化的时候显示所有线上商品信息，并且分页读取。
	 */
	public function onlineProductStorage() {
		// 初始化页面的查询
		$query_text = I ( 'querycontent', '' );
		$query_type = I ( 'querytype', 0 ); // 上新
		$is_asc = I ( 'querysort', 0 ); // 递减
		$startindex = 0;
		$count = 10;
		$firstInitData = TRUE;
		
		$jsondata = $this->productListLimit ( $this->sid, $query_text, $query_type, $is_asc, $startindex, $count, $firstInitData );
		// 将信息打包到前台
		$ajaxinfo = json_encode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
		$this->productlistjson = $finaljson;
		$this->display ();
	}
	
	/**
	 * 接收参数读取商品信息。
	 */
	public function requestOnlineProductStorage() {
		// 接收参数读取商品
		$query_text = I ( 'querycontent', '' );
		$query_type = I ( 'querytype', 0 ); // 上新
		$is_asc = I ( 'querysort', 0 ); // 递减
		$startindex = $_REQUEST ['nextStart'];
		$count = 10;
		
		$ajaxinfo = $this->productListLimit ( $this->sid, $query_text, $query_type, $is_asc, $startindex, $count );
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 接收页面上ajax请求查询并处理的函数。
	 * 第一版：
	 * 当点击商品搜索按钮，或是进行上新、人气、销量等排序时，进行一个order排序。
	 * 但是不论是搜索还是排序，都从所有线上商品里面读取。
	 *
	 * @param string $e_id 商家ID
	 * @param string $query_text 搜索框内容
	 * @param int $query_type 查询类别（上新0、热销1、人气2、价格3、分类4）
	 * @param bool $is_asc 是否递增排序
	 * @param number $startindex 从第几条开始
	 * @param number $count 想要读取几条
	 * @param bool $firstInitData 是否是第一次读取
	 */
	public function productListLimit($subbranch_id = '-1', $query_text = '', $query_type = 0, $is_asc = 0, $startindex = 0, $count = 10, $firstInitData = FALSE) {
		$productview = M ( "subbranch_product_image" ); // 实例化分店商品视图
		$productlist = array (); // 商品列表
		
		// 建立查询条件
		$promap = array (
				'subbranch_id' => $subbranch_id, // 当前分店
				'product_name' => array (
						'like',
						'%' . $query_text . '%' 
				),
				'is_del' => 0 
		);
		// 得到满足条件的列表
		if ($query_type == 0) // 上新
			$orderstring = ($is_asc == true) ? ('onshelf_time asc') : ('onshelf_time desc');
		if ($query_type == 1) // 热销
			$orderstring = ($is_asc == true) ? ('sell_amount asc') : ('sell_amount desc');
		if ($query_type == 2) // 人气
			$orderstring = ($is_asc == true) ? ('followed_amount asc') : ('followed_amount desc');
		if ($query_type == 3) // 价格
			$orderstring = ($is_asc == true) ? ('current_price asc') : ('current_price desc');
		if ($query_type == 4) // 分类
			$orderstring = ($is_asc == true) ? ('nav_name asc') : ('nav_name desc');
		
		$listcount = $productview->where ( $promap )->count ();
		if ($listcount) {
			$productlist = $productview->where ( $promap )->order ( $orderstring )->limit ( $startindex, $count )->select ();
			
			// 对查询出来的数据进行相应的处理
			
			// 返回真实得到的条数
			$realcount = count ( $productlist );
			// 数据的变换（改编）
			for($i = 0; $i < $realcount; $i ++) {
				// 对guideinfo信息进行一定的变换
				$productlist [$i] ['add_time'] = timetodate ( $productlist [$i] ['add_time'] );
				$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] );
				$productlist [$i] ['onshelf_time'] = timetodate ( $productlist [$i] ['onshelf_time'] );
				$productlist [$i] ['macro_path'] = assemblepath ( $productlist [$i] ['macro_path'], true );
				$productlist [$i] ['micro_path'] = assemblepath ( $productlist [$i] ['micro_path'], true ); // 分店图片
				$productlist [$i] ['link_url'] = U ( 'WeMall/Product/productDetail', array('sid' => $this->sid, 'pid' => $productlist [$i] ['product_id']), '', 0, true );
				unset ( $productlist [$i] ['is_del'] ); // 删除is_del字段
				unset ( $productlist[$i]['html_description'] );
			}
		}
		
		$ajaxresult = array (
				'data' => array (
						'productinfo' => $productlist 
				),
				'nextStart' => ($startindex + $realcount) 
		);

		if (! $firstInitData) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		return $ajaxresult;
	}
	
	/**
	 * 在库存信息功能下，将某件商品加入某导购的某个编号推荐夹的ajax处理函数（添加）
	 * 接收参数后，将其添加到推荐夹（微动建议一个推荐夹里最多放置15件商品）
	 */
	public function addGuideRecommend() {
		$Recommend = M ( "guiderecommend" ); // 实例化导购推荐表对象
		$data ['g_recommend_id'] = md5 ( uniqid ( rand (), true ) );
		$data ['e_id'] = $this->eid;
		$data ['subbranch_id'] = $this->sid;
		$data ['guide_id'] = $this->gid;
		$data ['collection_id'] = I ( 'collection_id', '-1' ); // 导购文件夹编号
		$data ['sub_pro_id'] = I ( 'sub_pro_id', '-1' );
		$data ['recommend_sort'] = 0;
		$data ['add_time'] = time ();
		$data ['remark'] = '';
		$data ['is_del'] = 0;
		$result = $Recommend->add ( $data );
		
		$ajaxresult = array ();
		if ($result == FALSE){	 // 写入失败
			$ajaxresult ['errCode'] = 10001;
			$ajaxresult ['errMsg'] = '商品添加推荐夹失败';
		} else {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 将导购的某个推荐夹编号下的某件商品移除出推荐夹（ 删除）
	 * 此处采取的是真删
	 */
	public function delGuideRecommend() {
		$Recommend = M ( "guiderecommend" ); // 实例化导购推荐表对象
		$map = array (
				'e_id' => $this->eid,
				'subbranch_id' => $this->sid,
				'guide_id' => $this->gid,
				'collection_id' => I ( 'collection_id', '-1' ), // 导购文件夹编号
				'sub_pro_id' => I ( 'sub_pro_id', '-1' ), // 商品编号
				'is_del' => 0 
		);
		$result = $Recommend->where ( $map )->limit (1 )->delete ();
		
		$ajaxresult = array ();
		if ($result == FALSE){	// 写入失败
			$ajaxresult ['errCode'] = 10001;
			$ajaxresult ['errMsg'] = '删除商品推荐夹中商品失败';
		} else {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = 'ok';
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * 查询某个收藏夹下面到底有多少件商品
	 * 并且把商品的product_id和视图t_product_image(product_id)连接,并返回
	 */
	public function queryGuideRecommend() {
		$Recommend = M ( "guiderecommend" ); // 实例化导购推荐表对象
		$map = array (
				'e_id' => $this->eid,
				'subbranch_id' => $this->sid,
				'guide_id' => $this->gid,
				'collection_id' => I ( 'collection_id', '-1' ), // 导购文件夹编号
				'is_del' => 0
		);
		// 查询导购的某个推荐夹下都有哪些商品，返回product_id
		$list = $Recommend->where ( $map )->getField ( 'sub_pro_id', true );
		
		// 针对于$list结果拼装$imglist，初始化为空数组，只有在$list不为空且$imglist不为空才去改变它
		$imglist = array();
		// 对查询出来的数据进行相应的处理
		if ($list) {
			// 返回某个收藏夹下商品的条数
			$productimage = M ( "subbranch_product_image" );
			$imgmap = array(
					'sub_pro_id'=>array ('in',$list),
					'is_del' => 0
			);
			$imglist = $productimage->where ( $imgmap )->select ();
			if ($imglist) {
				$count = count ( $imglist );
				// 数据的变换（改编）
				for($i = 0; $i < $count; $i++) {
					// 对查询出来的imglist信息进行一定的变换
					unset( $imglist [$i] ['is_del'] );
					unset( $imglist[$i]['original_price']);
					unset( $imglist[$i]['html_description']);
					unset( $imglist[$i]['browsed_amount']);
					unset( $imglist[$i]['followed_amount']);
					unset( $imglist[$i]['recommanded_amount']);
					unset( $imglist[$i]['bought_amount']);
					unset($imglist[$i]['storage_amount']);
					unset($imglist[$i]['sell_amount']);
					unset($imglist[$i]['total_storage_amount']);
					unset($imglist[$i]['add_time']);
					unset($imglist[$i]['latest_modify']);
					unset($imglist[$i]['onshelf_time']);
					$list [$i] ['macro_path'] = assemblepath ( $list [$i] ['macro_path'] );
					$list [$i] ['micro_path'] = assemblepath ( $list [$i] ['micro_path'] ); // 分店图片
				}
			}		
		}
		$ajaxresult = array (
				'data' => array (
						'productimage' => $imglist 
				) 
		);
		// 查询不考虑失败，如果没有数据的话，返回的是空数组
		$ajaxresult ['errCode'] = 0;
		$ajaxresult ['errMsg'] = 'ok';
		$this->ajaxReturn ( $ajaxresult );
	}
}
?>