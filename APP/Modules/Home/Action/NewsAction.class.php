<?php
/**
 * 图文信息控制器。
 * @author 赵臣升。
 * CreateTime:2015/06/07 15:53:26.
 */
class NewsAction extends Action {
	/**
	 * 图文信息详情页面。
	 */
	public function info() {
		$detail_id = I ( "did", "" ); // 接收图文消息详情编号
		// 检测参数
		if (empty ( $detail_id )) {
			$this->error ( "图文消息参数错误！" );
		}
		// 查询图文消息详情
		$newsview = M ( "msgnews_info" ); // 图文视图
		$detailmap = array (
				'msgnewsdetail_id' => $detail_id, // 某一条图文详情主键
				'is_del' => 0, // 没有被删除的
		);
		$newsdetailinfo = $newsview->where ( $detailmap )->find (); // 找到这条图文信息
		if (! $newsdetailinfo) {
			$this->error ( "原作者已经删除该图文！" );
		}
		
		$newsdetailinfo ['add_time'] = timetodate ( $newsdetailinfo ['add_time'] ); // 格式化时间
		$newsdetailinfo ['cover_image'] = assemblepath ( $newsdetailinfo ['cover_image'], true ); // 组装图片路径
		
		// 推送到前台
		$this->dinfo = $newsdetailinfo; // 推送图文详情消息
		$this->display ();
	}
}
?>