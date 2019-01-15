<?php
class DataFormat {
	/**
	 * 格式化微动数据为大众点评数据。
	 * @param string $catelist	形参传入菜品列表数组
	 */
	public function dataFormat($catelist = NULL) {
		$total = count( $catelist );			//总数量
		$formatlist = array();					//格式化后数据
		if($total > 0) {
			for($i=0; $i<$total; $i++){
				$formatlist [$i] ['dishid'] = $catelist [$i] ['cate_id'];						//主键
				$formatlist [$i] ['categoryId'] = $catelist [$i] ['nav_id'];					//菜品分类编号
				$formatlist [$i] ['dName'] = $catelist [$i] ['cate_name'];						//餐饮菜品名称
				$formatlist [$i] ['dTaste'] = $this->tasteInit($catelist [$i] ['hot_level']);	//餐饮口感（是否辣，辣味等级）
				$formatlist [$i] ['dUnitName'] = $catelist [$i] ['unit_name'];					//单位
				$formatlist [$i] ['dPrice'] = $catelist [$i] ['price'];							//价格
				$formatlist [$i] ['dDescribe'] = $catelist [$i] ['brief_description'];			//描述
				$formatlist [$i] ['dIsHot'] = $catelist [$i] ['recommend_level'];				//推荐人气
				$formatlist [$i] ['dSubCount'] = 0;												//点餐次数，从数据库交易成功的订单中读取，暂且设定为0
				$formatlist [$i] ['dPicture'] = $catelist [$i] ['micro_path'];					//图片信息
				$formatlist [$i] ['dPictureBig'] = $catelist [$i] ['macro_path'];					//图片信息
				if(floatval($catelist [$i] ['member_price']) > 0) {
					$formatlist [$i] ['dIsSpecial'] = 1;										//会员特价标记为1
					$formatlist [$i] ['dSpecialPrice'] = $catelist [$i] ['member_price'];		//会员特价
				} else {
					$formatlist [$i] ['dIsSpecial'] = 0;
				}
				$formatlist [$i] ['o2uNum'] = $catelist [$i] ['order_number'];					//当前菜品点单数目
				$formatlist [$i] ['allShopInfo'] = '';											//这里必须要空字符串，否则加第一道菜的时候会弹出对话框（大众点评也是空）
			}
		}
		return $formatlist;
	}
	
	/**
	 * 餐饮的口味根据辣等级分类函数。
	 * @param number $hot_level	餐饮口味整型
	 * @return string $taste	餐饮口味描述
	 */
	private function tasteInit($hot_level = 0) {
		$taste = '大众';
		if($hot_level == 0) {
			$taste = '不辣';
		}else if($hot_level == 1) {
			$taste = '微辣';
		}else if($hot_level == 2) {
			$taste = '中辣';
		}else if($hot_level == 3) {
			$taste = '重辣';
		}else if($hot_level == 4) {
			$taste = '麻辣';
		}
		return $taste;
	}
}
?>