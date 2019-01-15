<?php
class ServiceNavHandleAction extends Action {
	public function navHandle() {
		// 先查询出微动所有导航信息
		$allservicenav = M ( 'servicenav' )->where ( $servicemap = array ( 'is_del' => 0 ) )->select (); // 所有的service_nav
		$allnavcount = count ( $allservicenav ); // 计算下总的导航数量
		
		$allnavlist = array (); // 全局总导航
		$alllevelcount = 0; // 所有导航的层级数量
		$finalnavlist = array (); // 全局串起葡萄的总导航（全哈希表：任何一个导航信息，可以不经过遍历，直接随机索引到）
		
		// 很重要的分桶处理，总体遍历一次，时间复杂度N，因为有了这个分桶，后续的for循环只会对每个导航进行一次！
		for($i = 0; $i < $allnavcount; $i ++) {
			if (! is_array ( $allnavlist [$allservicenav [$i] ['nav_level']])) {
				$allnavlist [$allservicenav [$i] ['nav_level']] = array (); // 为array_push开辟数组空间（不开辟array_push会空操作）
				$alllevelcount += 1; // 顺手统计导航层级数量
			}
			array_push ( $allnavlist [$allservicenav [$i] ['nav_level']], $allservicenav [$i] ); // 把所有导航按nav_level分类
		}
		
		// 先进行自底向上的哈希索引布局，最外层for循环遍历nav_level品种的次数（貌似4次），但是特别注意，顶级导航不用这么做，所以只要循环3次，总体遍历一次，时间复杂度N
		for ($k = $alllevelcount; $k > 1; $k --) {
			$handlearray = $allnavlist [$k]; // 取出当前要进行哈希索引的数组
			$hashresult = array (); // 存放哈希索引后的结果
			// Step1：把父级servicenav_id作为二维数组i，把自己的sort作为二维数组的j，保存自己
			for ($i = 0; $i < count ( $handlearray ); $i ++) {
				$hashresult [$handlearray [$i] ['father_id']] [$handlearray [$i] ['sort']] = $handlearray [$i];
			}
			// Step2：当一次level的顺序被检索完毕，立刻进行一次ksort键值排序
			foreach ($hashresult as $key => $value) {
				ksort ( $value );
			}
			// Step3：将哈希索引后的结果数组覆盖保存回原来的$allnavlist [$k]中
			$allnavlist [$k] = $hashresult;
		}
		
		// 架起葡萄藤（顶级导航nav_level == 1）即$allnavlist [1]，再再进行自顶向下串葡萄，总体遍历一次，时间复杂度N
		for ($i = 0; $i < count ( $allnavlist [1] ); $i ++) {
			$firstnav = $allnavlist [1] [$i]; // 取出一个顶级导航
			$topnavid = $firstnav ['servicenav_id']; // 当前操作顶级导航编号（取出来防止眼花缭乱）
			$finalnavlist [$topnavid] = $firstnav; // 最终导航串挂接当前顶级导航
			$secondnavlist = $allnavlist [2] [$topnavid]; // 取出当前顶级导航旗下的二级导航列表
			if (! empty ( $secondnavlist )) {
				// 如果当前取出的二级导航不空，挂接二级导航（二级导航下标sort必须从0开始）
				for ($j = 0; $j < count ( $secondnavlist ); $j ++) {
					$secondnavid = $secondnavlist [$j] ['servicenav_id']; // 取出二级导航列表中当前操作的某个二级导航编号（取出来防止眼花缭乱）
					$finalnavlist [$topnavid] ['children'] [$secondnavid] = $secondnavlist [$j]; // 挂接二级导航
					$thirdnavlist = $allnavlist [3] [$secondnavid]; // 取出当前操作二级导航的三级导航
					if (! empty ( $thirdnavlist )) {
						// 三级导航不空，挂接三级导航（三级导航下标sort必须从0开始）
						for ($k = 0; $k < count ( $thirdnavlist ); $k ++) {
							$thirdnavid = $thirdnavlist [$k] ['servicenav_id']; // 取出当前操作的某个三级导航编号（取出来防止眼花缭乱）
							$finalnavlist [$topnavid] ['children'] [$secondnavid] ['children'] [$thirdnavid] = $thirdnavlist [$k]; // 挂接三级导航
							$fourthnavlist = $allnavlist [4] [$thirdnavid]; // 取出四级导航
							if (! empty ( $fourthnavlist )) {
								// 四级导航不空，挂接四级导航（四级导航下标sort必须从0开始）
								for ($t = 0; $t < count ( $fourthnavlist ); $t ++) {
									$fourthnavid = $fourthnavlist [$t] ['servicenav_id']; // 取出当前操作的四级导航编号（取出来防止眼花缭乱）
									$finalnavlist [$topnavid] ['children'] [$secondnavid] ['children'] [$thirdnavid] ['children'] [$fourthnavid] = $fourthnavlist [$t]; // 挂接二级导航
								}
							} // if ! empty ( $fourthnavlist )
						}
					} // if ! empty ( $thirdnavlist )
				}
			} // if ! empty ( $secondnavlist )
		}
		p($finalnavlist);die; // 可视化最终串葡萄结果
	}
}
?>