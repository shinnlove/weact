<?php
/**
 * 积分商城管理ajax请求处理控制器。
 * @author wlk,胡睿。
 */
class ScoreProductManageAction extends PCRequestLoginAction {
/**
	 * 积分商城展示页面
	 */
	public function scoreProductView() {
		$this->display();
	}
	/**
	 * 导出积分商城数据
	 */
	public function exportScoreProduct(){
		$scoremap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		$field = "member_level,score_amount, product_type, product_number, product_name, sex, material,product_color,product_size, current_price, units, total_storage_amount,total_storage_left,total_sell_amount,  latest_modify"; // 字段
		$productlist = M ( 'score_product_sku' )->where ( $subbranchmap )->field ( $field )->select (); // 得到该商家所有商品信息
		// 格式化查询出的数据
		for ($i =0; $i < count ( $productlist ); $i ++) {
			switch($productlist[$i]['member_level']){
				case 1:
					$productlist[$i]['member_level'] = '一级会员专区';
			case 2:
				$productlist[$i]['member_level'] = '二级会员专区';
			case 3:
				$productlist[$i]['member_level'] = '三级会员专区';
			}
			if ($productlist [$i] ['product_type'] == 2) {
				$productlist [$i] ['product_type'] = "服装类商品";
			} else if ($productlist [$i] ['product_type'] == 5) {
				$productlist [$i] ['product_type'] = "常用商品";
			}
			if ($productlist [$i] ['sex'] == 0) {
				$productlist [$i] ['sex'] = "通用";
			} else if ($productlist [$i] ['sex'] == 1) {
				$productlist [$i] ['sex'] = "男";
			} else if ($productlist [$i] ['sex'] == 2) {
				$productlist [$i] ['sex'] = "女";
			}
			$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] ); // 更改时间
		}
		
		// 准备标题准备打印
		$title = array (
				0 => '会员等级专区',
				1 => '商品所需积分',
				2 => '类别',
				3 => '编号',
				4 => '名称',
				5 => '性别',
				6 => '质地',
				7 => '颜色',
				8 => '尺寸',
				9 => '价格',
				10 => '计量单位',
				11 => '原始入库数',
				12 => '当前库存数',
				13 => '当前卖出数',
				14 => '最近修改时间'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $productlist, $shopname.timetodate(time()), '积分商城商品一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
}
?>