<?php
/**
 * 积分商城商品管理请求处理控制器。
 * @author Loretta
 * @create time:2015/08/12 16:47:36.
 */
class ScoreExchangeAction extends PCViewLoginAction {
	/**
	 * 查看积分商城商品。
	 */
	public function scoreExchangeProduct() {
		// 分类查找商品时读取分类
		$allpromap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$this->e_id = $_SESSION ['curEnterprise'] ['e_id']; // 推送企业编号
		$this->display ();
	}
	
	/**
	 * 导出商品。
	 */
	public function exportProduct() {
		$productmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'score_onshelf' => 1,
				'score_type' => 1,
				'is_del' => 0
		);
		$field = "nav_name, product_type, product_number, product_name, sex, material, current_price, units, storage_amount, sell_amount, storage_warn, html_description, latest_modify"; // 字段
		$productlist = M ( 'product' )->where ( $productmap )->field ( $field )->select (); // 得到该商家所有商品信息
		// 格式化查询出的数据
		for ($i =0; $i < count ( $productlist ); $i ++) {
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
			$productlist [$i] ['storage_amount'] = $productlist [$i] ['storage_amount'] - $productlist [$i] ['sell_amount']; // 计算剩下的库存量
			$productlist [$i] ['latest_modify'] = timetodate ( $productlist [$i] ['latest_modify'] ); // 更改时间
		}
		// 准备标题准备打印
		$title = array (
				0 => '分类导航',
				1 => '类别',
				2 => '编号',
				3 => '名称',
				4 => '性别',
				5 => '质地',
				6 => '价格',
				7 => '计量单位',
				8 => '当前库存量',
				9 => '当前卖出量',
				10 => '库存预警下限',
				11 => '图文详情(html语言)',
				12 => '最近修改时间'
		);
	
		$excel = A ( 'Admin/Excel' ); // 新建excel对象
		$excel->exportData ( $title, $productlist, '商品详情'.time(), '所有商品一览表', false ); // 导出Excel数据(2007格式的有待再调试，先使用非2007的.xls格式)
	}
}
?>