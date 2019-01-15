<?php
/**
 * 街市控制器，实体店铺联盟逛街选店铺所用。
 * @author 赵臣升。
 * CreateTime:2015/06/28 20:18:00.
 */
class StreetMallAction extends Action {
	/**
	 * 供应商品店铺视图页面。
	 * 在微猫商城的街市中选取货架上供应该商品的商店陈列。
	 */
	public function supplyShop() {
		$subbranch_id = I ( 'sid', "-1" ); // 接收分店编号
		$product_id = I ( 'pid' ); // 接收要找寻的商品编号
		
		if (empty ( $product_id )) {
			$this->error ( "请先确定要选购的商品再逛街市！" );
		}
		
		// 查询商品信息
		$promap = array (
				'product_id' => $product_id, // 商品编号
				'is_del' => 0
		);
		$pinfolist = M ( 'product_image' )->where ( $promap )->limit ( 1 )->select ();
		$pinfo = $pinfolist [0]; // 二维数组变成一位数组
		$pinfo ['macro_path'] = assemblepath ( $pinfo ['macro_path'] ); // 组装图片路径
		$pinfo ['micro_path'] = assemblepath ( $pinfo ['micro_path'] ); // 组装图片路径
		
		// 查询企业信息
		$emap = array (
				'e_id' => $pinfo ['e_id'], 
				'is_del' => 0 
		);
		$einfo = M ( 'enterpriseinfo' )->where ( $emap )->find ();
		
		// 查询售卖商品的分店
		$nextstart = 0;
		$perpage = 10;
		$firstinit = true;
		$subsalelist = $this->subbranchProSell ( $product_id, $einfo ['e_id'], $nextstart, $perpage, $firstinit );
		
		$this->eid = $einfo ['e_id']; 		// 推送企业编号
		$this->einfo = $einfo; 				// 推送企业信息
		$this->pid = $pinfo ['product_id']; // 推送商品编号
		$this->pinfo = $pinfo; 				// 推送商品信息
		
		$this->o2oshop = jsencode ( $subsalelist ); // 推送O2O实体店售卖情况
		$this->display ();
	}
	
	public function subList() {
		// 接收参数并查数据库，分页读取数据
		$product_id = I('pid','');
		$e_id = $this->eid;
		$startindex = 0;
		$count = 10;
		$firstInitData = TRUE;
	
		$jsondata = $this->subbranchProSell ( $product_id, $e_id, $startindex, $count, $firstInitData );
		
		$ajaxinfo = json_encode ( $jsondata );
		$finaljson = str_replace ( '"', '\\"', $ajaxinfo );
	
		$this->subbranchjson = $finaljson;
		$this->display ();
	}
	
	public function requestSubList() {
		// 接收参数读取订单
		$product_id = I('pid','');
		$e_id = $this->eid;
		$startindex = $_REQUEST ['nextStart'];
		$count = 10;
	
		$ajaxinfo = $this->subbranchProSell ( $product_id, $e_id, $startindex, $count );
		$this->ajaxReturn ( $ajaxinfo );
	}
	
	/**
	 * 查询某商品在实体店铺的售卖情况。
	 * @param string $product_id 商品编号
	 * @param string $e_id 企业编号
	 * @param number $nextstart 下一页开始 
	 * @param number $perpage 每页几条
	 * @param boolean $firstinit 是否页面初始化数据
	 * @return array $salebranchlist 售卖该商品的实体店铺
	 */
	public function subbranchProSell($product_id = '', $e_id = '', $nextstart = 0, $perpage = 10, $firstinit = FALSE) {
		$proselltable = M ( "pro_sell_subbranch_list" ); 
		$orderby = "sub_sell desc"; // 各个分店卖出量从多到少的顺序
		$saleshoplist = array (); // 售卖店铺列表
		
		$proSellMap = array (
				'product_id' => $product_id, 
				'e_id' => $e_id, 
				'is_del' => 0 
		);
		$totalcount = $proselltable->where ( $proSellMap )->count ();
		
		// Step2：计算数据库剩余记录数量
		$recordleftnum = $totalcount - $nextstart; 					// 计算剩下需要请求的数据记录数量，比如统计出100条记录，下一条开始记录下标是97，最大下标99，则还有3条数据可请求：97,98,99
		
		// Step3：计算本次请求得到的数据数量、下次请求的起始位置
		$realgetnum = ($recordleftnum >= $perpage) ? $perpage : $recordleftnum; // 剩下的数据记录是否大于每页需要的数量，如果大于，就请求每页大小；如果小于，则请求剩余的数量
		
		if ($realgetnum < 0) $realgetnum = 0; 									// 必要的容错处理，防止$nextstart大于总数量
		
		$newnextstart = $nextstart + $realgetnum; 								// 本次如果请求成功，下一次再请求数据记录开始的下标
		
		// Step4：如果本次请求有数据可读，则请求查询数据
		if ($realgetnum) {
			$saleshoplist = $proselltable->where ( $proSellMap )->limit ( $nextstart, $realgetnum )->order ( $orderby )->select (); // 查询附近店铺信息
			
			// 可能需要的格式化信息（转换时间或路径等）
			for($i = 0; $i < $realgetnum; $i ++) {
				$saleshoplist [$i] ['image_path'] = assemblepath ( $saleshoplist [$i] ['image_path'] );
			}
		}
	
		// Step5：打包数据
		$ajaxresult = array (
				'data' => array (
						'saleshoplist' => $saleshoplist // 分店地址列表
				),
				'nextstart' => $newnextstart, // 下一条记录开始 
				'totalcount' => $totalcount // 总的记录数
		);
		
		// 如果不是初始化数据，说明是ajax请求，还要带上errCode和errMsg
		if (! $firstinit) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "ok";
		}
		return $ajaxresult; // 返回ajax信息
	}
	
}
?>