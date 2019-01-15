<?php
/**
 * 场景应用视图控制器。
 * @author 赵臣升。
 * CreateTime：2014/10/31 16:03:25.
 */
class ExpoAction extends Action {
	/**
	 * SceneApp视图。
	 * 通过sceneapp的version_tplpath——模板名来渲染要展现的模板。
	 * @author 赵臣升。
	 * CreateTime：2014/10/31 17:20:36.
	 */
	public function magazine() {
		$sceneappid = I ('issue');
		$sceneappid = str_replace ( ".shtml", "", $sceneappid );
		$sainfo = M('sceneapp')->where( array( 'sceneapp_id' => $sceneappid, 'is_del' => 0 ) )->find();
		if (!$sainfo) $this->error('您要访问的期刊不存在或已过期!');
		$authorize_open = C ( 'AUTHORIZE_OPEN' );
		if ($authorize_open && empty ( $_SESSION ['currentwechater'] [$sainfo ['e_id']] ['openid'] )) {
			$urlparams = $_REQUEST ['_URL_'];										// 某些URL方式可能没有e_id
			$authURL = '';
			$existe_id = false;														// 检测是否识别到e_id
			for($i = 0; $i < count ( $urlparams ); $i ++) {
				$authURL = $authURL . $urlparams [$i] . '/'; 						// 拼接路由参数
				if ($urlparams [$i] == 'e_id')
					$existe_id = true;
			}
			if (! $existe_id) {
				$authURL = $authURL . 'e_id/' . $sainfo ['e_id'] . '/';			// 未识别到e_id再拼接e_id
			}
			$wechatauth = A ( 'Home/WeChatAuthorize' );
			$wechatauth->getAuth ( $authURL, $sainfo ['e_id'] ); 						// 授权带参路由路径
		}
		$rh = A('SceneApp/ResourceHandle');
		$sainfo = $rh->getSceneResource($sainfo);		//处理资源信息
		$sh = A('SceneApp/ShareHandle');
		$sainfo = $sh->getShareInfo($sainfo);			//处理分享信息
		$this->sainfo = $sainfo;
		$this->display($sainfo ['version_tplpath']);	//渲染展现模板
	}
}
?>