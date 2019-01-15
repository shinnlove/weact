<?php
/**
 * 本控制器处理场景应用分享的信息。
 * @author 赵臣升。
 * CreateTime：2014/10/31 15:51:25.
 */
class ShareHandleAction extends Action {
	/**
	 * 处理分享信息的函数。
	 * @param array $sceneinfo	场景应用信息，$sceneinfo数组中必须包含scene_id字段且有值。
	 * @return array $sceneinfo	返回带上分享信息的$sceneinfo数组
	 */
	public function getShareInfo($sceneinfo = NULL) {
		$sharemap = array(
			'sharelist_id' => $sceneinfo ['sharelist_id'],
			'share_type' => 1,		//sceneapp分享类型是1
			'is_del' => 0
		);
		$shareinfo = M('sharelist')->where($sharemap)->find();
		$shareinfo ['img_url'] = assemblepath($shareinfo ['img_url'], true);		//组装路径，分享头像必须使用绝对路径
		$sceneinfo ['shareinfo'] = $shareinfo;
		return $sceneinfo;
	}
}
?>