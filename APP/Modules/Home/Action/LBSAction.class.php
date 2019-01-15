<?php
class LBSAction extends MobileGuestAction {
	public function preshop() {
		$this->display ();
	}
	
	// 获取周围的店铺
	public function getshop() {
		$x = I ( 'x' );
		$y = I ( 'y' );
		$e_id = I ( 'e_id' );
		$j = 0;
		$subbranch = M ( 'subbranch' );
		$mapurl = "http://api.map.baidu.com/geosearch/v3/nearby?ak=D577599b31147ee7d5147992bf063b21&geotable_id=59809&location=" . $y . "," . $x . "&radius=200000&tags=店铺&sortby=distance:1";
		$maphtml = file_get_contents ( $mapurl );
		$maphtml = json_decode ( $maphtml );
		$shoplist = $maphtml->contents;
		for($i = 0; $i < count ( $shoplist ); $i += 1) {
			
			$location_id = $shoplist [$i]->location_id;
			$address = $shoplist [$i]->address;
			$distance = $shoplist [$i]->distance;
			$title = $shoplist [$i]->title;
			$location_x = $shoplist [$i]->location [0];
			$location_y = $shoplist [$i]->location [1];
			$map ['subbranch_id'] = $location_id;
			$map ['e_id'] = $e_id;
			
			$enterprise = $subbranch->where ( $map )->select ();
			
			if ($enterprise) {
				$data [$j] ['location_id'] = $location_id;
				$data [$j] ['address'] = $address;
				$data [$j] ['title'] = $title;
				$data [$j] ['x'] = $location_x;
				$data [$j] ['y'] = $location_y;
				$data [$j] ['distance'] = $distance;
				$data [$j] ['location_description'] = $enterprise [0] ['location_description'];
				$j = $j+1;
			}
		}
		$this->ajaxReturn ( array (
				'total' => count ( $data ),
				'shop' => $data 
		), 'json' );
	}
	
	// 根据ip定位（有缺陷）
	public function getNearbyShop() {
		// 获取客户端ip信息
		$client_ip = get_client_ip ();
		// 获取客户端经纬度
		$this->client_ip = $client_ip;
		// p($client_ip);die;
		$url = "http://api.map.baidu.com/location/ip?ak=D577599b31147ee7d5147992bf063b21&ip=" . $client_ip . "&coor=bd09ll";
		$html = file_get_contents ( $url );
		$html = json_decode ( $html );
		$x = $html->content->point->x;
		$y = $html->content->point->y;
		$this->ajaxReturn ( array (
				'x' => $x,
				'y' => $y 
		), 'json' );
	}
	// 显示当前店铺地图
	public function map() {
		$x = I ( 'x' );
		$y = I ( 'y' );
		$map ['subbranch_id'] = I ( 'location_id' );
		$subbranch = M ( 'subbranch' );
		$subbranch = $subbranch->where ( $map )->select ();
		$this->enterprise_location = $subbranch;
		$this->address = $subbranch [0] ['subbranch_address'];
		$this->title = $current_enterprise [0] ['subbranch_name'];
		$this->x = $x;
		$this->y = $y;
		$this->display ();
	}
}
?>