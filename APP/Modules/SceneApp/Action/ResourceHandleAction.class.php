<?php
/**
 * 本控制器处理某场景应用需要的资源图片。
 * @author 赵臣升。
 * CreateTime：2014/10/31 15:52:25.
 */
class ResourceHandleAction extends Action {
	/**
	 * 本函数是主调函数，操作图片处理、视频处理与音乐处理等信息。
	 * 特别注意：视频的order代表在第几张图文处出现（包括封面在内，不包括擦图）。如果有多个视频，同类之间越小越靠前，比如7和11，代表在7和11的位置分别插入两段视频。
	 * 所以，要在$sceneinfo ['image'] ['common'] [$i] 处添加 ['video_url'] 字段记录视频的编号，不存代表不在此位置插入视频。图片不用后移，因为视频插入必须有一张底图，所以视频和图片并存。
	 * @param array $sceneinfo	场景应用信息，$sceneinfo数组中必须包含scene_id字段且有值。
	 * @return array $sceneinfo	返回带上资源信息的$sceneinfo数组（包含图片、音乐与视频）
	 */
	public function getSceneResource($sceneinfo = NULL) {
		//Step1：准备工作
		$videolist = array();			//视频资源清单
		$musiclist = array();			//音乐资源清单
		$imagelist = array();			//图片资源清单
		
		//Step2：拉取资源
		$resourcemap = array(
			'sceneapp_id' => $sceneinfo ['sceneapp_id'],
			'is_del' => 0
		);
		$resourcelist = M('sceneresource')->where($resourcemap)->order('resource_order asc')->select();			//一次性拉取所有资源，从小到大排列，越小越在前
		//顺手组装资源路径
		for($k = 0; $k<count($resourcelist); $k++) {
			if(strstr($resourcelist [$k] ['resource_url'], 'http://') && !strstr($resourcelist [$k] ['resource_url'], '/weact')) continue;		//如果图片或视频是来自非微动服务器，直接跳过组装
			$resourcelist [$k] ['resource_url'] = assemblepath($resourcelist [$k] ['resource_url']);			//使用相对路径
		}
		
		//Step3：分拣资源核心过程（数组压栈）
		for($i = 0; $i<count($resourcelist); $i++){
			if($resourcelist [$i] ['resource_type'] == -1) {
				array_push($videolist, $resourcelist [$i]);						//资源类型是-1代表是视频资源
			}else if($resourcelist [$i] ['resource_type'] == 0) {
				array_push($musiclist, $resourcelist [$i]);						//资源类型是0代表是音乐资源
			}else{
				$resourcelist [$i] ['video_url'] = '';							//图片资源挂接视频先置为空
				array_push($imagelist, $resourcelist [$i]);						//其他资源类型都是图片资源
			}
		}
		
		//Step4：分别调用函数处理视频、音乐和图片（图片中有多态函数处理特效）
		$sceneinfo = $this->getSceneImages($sceneinfo, $imagelist);				//Step1：处理场景图像信息，处理完后$sceneinfo多个image字段
		$sceneinfo ['music'] = $musiclist;										//Step2：挂接场景音乐信息，挂接$musiclist到$sceneinfo的music字段
		$sceneinfo = $this->appendVideo($sceneinfo, $videolist);				//Step3：挂接场景视频信息，将视频按出现位置挂接到对应的正文图文上
		return $sceneinfo;
	}
	
	/**
	 * 处理场景图像信息函数，分为处理公有和私有两种方式。
	 * @param array $sceneinfo	整体场景资源信息数组
	 * @param array $imagelist	需要处理的图片清单
	 * @return array	$sceneinfo	返回处理后带图片效果的整体场景资源信息数组
	 */
	private function getSceneImages($sceneinfo = NULL, $imagelist = NULL) {
		//Step1：公有的大图文图片的处理
		$imagecommon = array();								//定义公有的正文大图片数组
		for($i=0; $i<count($imagelist); $i++){
			if($imagelist [$i] ['resource_type'] == 1) array_push($imagecommon, $imagelist [$i]);			//类型1代表公有大图文，压栈
		}
		$sceneinfo ['image'] ['common'] = $imagecommon;							//将公有的正文大图片数组挂接到image下的common数组里
		$sceneinfo ['image'] ['commoncount'] = count($imagecommon);				//将公有的正文大图片数量放到commoncount里，给for循环用
		
		//Step2：对版本2expo、3extend、4master分别进行特殊效果图片的处理，使用多态函数。
		if($sceneinfo ['version_type'] == 2 || $sceneinfo ['version_type'] == 3 || $sceneinfo ['version_type'] == 4) {
			$polymorphic = $sceneinfo ['version_tplpath'] . 'ImageHandle';		//拼接多态函数名称
			$sceneinfo ['image'] [ $sceneinfo ['version_tplpath'] ] = $this->$polymorphic($imagelist);		//调用多态函数，例如extend版图片返回在$sceneinfo ['image'] ['extend']数组里
		}
		
		//Step3：对版本3：extend版的相册图进行插入处理，同时更新common大图state的数量。
		if($sceneinfo ['version_type'] == 3) {
			$finalarray = array();												//extend版本下图片数组
			$sumcount = count( $sceneinfo ['image'] ['common'] ) + count( $sceneinfo ['image'] ['extend'] );//总场景数
			for( $j = 0, $k = 0; $j < $sumcount; $j ++ ){
				if( count( $sceneinfo ['image'] ['extend'] [$j] ) > 0 ) {
					array_push( $finalarray, $sceneinfo ['image'] ['extend'] [$j] );
				}else{
					array_push( $finalarray, $sceneinfo ['image'] ['common'] [$k++] );
				}
			}
			$sceneinfo ['image'] ['common'] = $finalarray;							//加入extend版本后重新刷新common数组
			$sceneinfo ['image'] ['commoncount'] = count($finalarray);				//加入extend版本后重新刷新common数组个数
		}
		return $sceneinfo;
	}
	
	/**
	 * 附加视频到序号图文上，如果有，就把视频路径添加到video_url字段里。
	 * @param array $sceneinfo	附加视频前的场景信息
	 * @param array $videolist	需要附加的视频列表（可以有多个）
	 * @return array $sceneinfo	返回附加视频后的场景信息
	 */
	private function appendVideo($sceneinfo = NULL, $videolist = NULL) {
		if(count($videolist) == 0) return $sceneinfo;							//没有视频需要处理，原样返回
		for($i=0; $i<count($videolist); $i++) {
			$sceneinfo ['image'] ['common'] [ $videolist [$i] ['resource_order'] - 2 ] ['video_url'] = $videolist [$i] ['resource_url'];	//视频地址放到编号图片位置（并且封面-1，数组下标-1）
		}
		return $sceneinfo;
	}
	
	/**
	 * 展览版图片处理函数主要解决浮动png图片的读取。
	 * 多态函数之一。
	 * @author 赵臣升
	 * CreateTime：2014/10/31 18:03:25.
	 * @param array $imageall	传入图片数组
	 * @return array $imageall	返回带浮动png图片的数组，image数组下多个expo字段数组
	 */
	private function expoImageHandle($imageall = NULL) {
		
	}
	
	/**
	 * 扩展版图片处理函数主要解决相册飞入的图片读取。
	 * 多态函数之二。
	 * @author 赵臣升
	 * CreateTime：2014/10/31 18:03:25.
	 * @param array $imageall	传入图片数组
	 * @return array $imageall	返回带相册飞入图片的数组，image数组下多个extend字段数组
	 */
	private function extendImageHandle($imageall = NULL) {
		$extend = array();									//相册式图片数组
		for($i = 0; $i<count($imageall); $i++){
			if($imageall [$i] ['resource_type'] == 9) {
				$extend [ $imageall [$i] ['resource_order'] ] [ $imageall [$i] ['group_inner_order'] ] = $imageall [$i];	//如果是相册飞入图片压缩到指定位置
			}
		}
		return $extend;
	}
	
	/**
	 * 大师版图片处理函数主要解决擦图前后模糊与清晰图片的读取。
	 * 多态函数之三。
	 * @author 赵臣升
	 * CreateTime：2014/10/31 18:03:25.
	 * @param array $imageall 传入图片数组
	 * @return array $imageall	返回带擦图的图片的数组，image数组下多个master字段数组
	 */
	private function masterImageHandle($imageall = NULL) {
		$effect = array();
		for($i = 0; $i < count($imageall); $i++) {
			if($imageall [$i] ['resource_type'] == 2) {
				$effect ['before'] = $imageall [$i] ['resource_url'];
			} else if($imageall [$i] ['resource_type'] == 3) {
				$effect ['after'] = $imageall [$i] ['resource_url'];
			}
		}
		return $effect;
	}
}
?>