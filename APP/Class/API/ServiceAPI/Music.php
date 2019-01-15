<?php
/**
 * 点歌API类。
 * @author 赵臣升。
 * CreateTime：2014/11/06 00:59:25.
 */
class Music {
	
	/**
	 * 点歌函数。
	 * @param string $entity        	
	 * @return array $content 歌曲的内容数组
	 */
	public function getMusicOriginal($entity = '') {
		if ($entity == "") {
			$content = "请告诉我音乐名称噢O(∩_∩)O~";
		} else {
			if (strpos ( $entity, "@" )) {
				$music = explode ( "@", $entity );
				$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=" . $music [1] . "$$" . $music [0] . "$$$$"; // 歌手@歌名
			} else {
				$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=" . $entity . "$$"; // 直接歌名
			}
			
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$data = curl_exec ( $ch );
			
			$content = "检索失败";
			try {
				@$menus = simplexml_load_string ( $data, 'SimpleXMLElement', LIBXML_NOCDATA );
				if ($menus->count > 0 && isset ( $menus->url [0] ) && isset ( $menus->durl [0] )) {
					$url_prefix = substr ( $menus->url [0]->encode, 0, strripos ( $menus->url [0]->encode, '/' ) + 1 );
					$url_suffix = substr ( $menus->url [0]->decode, 0, strripos ( $menus->url [0]->decode, '&' ) );
					$durl_prefix = substr ( $menus->durl [0]->encode, 0, strripos ( $menus->url [0]->encode, '/' ) + 1 );
					$durl_suffix = substr ( $menus->durl [0]->decode, 0, strripos ( $menus->url [0]->decode, '&' ) );
					if (strpos ( $entity, "@" )) {
						$content = array (
								'Title' => $music [1],
								'Description' => $music [0],
								'MusicUrl' => $url_prefix . $url_suffix,
								'HQMusicUrl' => $durl_prefix . $durl_suffix 
						);
					} else {
						$content = array (
								'Title' => $entity,
								'Description' => 'Powered by WeAct.',
								'MusicUrl' => $url_prefix . $url_suffix,
								'HQMusicUrl' => $durl_prefix . $durl_suffix 
						);
					}
				}
			} catch ( Exception $e ) {
			}
		}
		return $content;
	}
	
	/**
	 * 最新版本的点歌函数。
	 * @param string $entity 歌名@歌手名
	 * @return array $music 歌曲信息
	 */
	public function getMusic($entity = '') {
		if ($entity == "") {
			$content = "请告诉我音乐名称噢O(∩_∩)O~";
		} else {
			$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=" . $entity . "$$"; // 直接歌名
			
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$data = curl_exec ( $ch );
				
			$content = "没有找到这首歌，换首歌试试吧。";
			try {
				// 新版本提取做法
				$musicdata = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
				
				$mainurllist = (array)$musicdata ['url'] [0]->encode;
				$suburllist = (array)$musicdata ['url'] [0]->decode;
				$mainurl = $mainurllist [0];
				$suburl = $suburllist [0];
				
				$prefix = substr ( $mainurl, 0, strripos ( $mainurl, '/') + 1 );
				$symbolexist = strpos ( $suburl, '&' );
				if ($symbolexist) {
					$suffix = substr ( $suburl, 0, strripos ( $suburl, '&') );
				} else {
					$suffix = $suburl;
				}
				//$suffix = substr ( $suburl, 0, strripos ( $suburl, '&') );
				//$suffix = $suburl; // 不切割也没关系，反而没错
				$fullurl = $prefix . $suffix;
				
				// 高品质连接（拓展，也可以都用$fullurl）
				$highmainurllist = (array)$musicdata ['durl'] [0]->encode;
				$highsuburllist = (array)$musicdata ['durl'] [0]->decode;
				$highmainurl = $highmainurllist [0];
				$highsuburl = $highsuburllist [0];
				
				$highprefix = substr ( $highmainurl, 0, strripos ( $highmainurl, '/') + 1 );
				$highsymbolexist = strpos ( $highsuburl, '&' );
				if ($highsymbolexist) {
					$highsuffix = substr ( $highsuburl, 0, strripos ( $highsuburl, '&') );
				} else {
					$highsuffix = $highsuburl;
				}
				//$suffix = substr ( $suburl, 0, strripos ( $suburl, '&') );
				//$highsuffix = $highsuburl; // 不切割也没关系，反而没错
				$highfullurl = $highprefix . $highsuffix;
				
				$content = array (
						'Title' => $entity,
						'Description' => 'Powered by WeAct Shinnlove.',
						'MusicUrl' => urldecode ( $fullurl ),
						'HQMusicUrl' => urldecode ( $highfullurl )
				);
			} catch ( Exception $e ) {
				
			}
		}
		return $content;
	}
	
}
?>