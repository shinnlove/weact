<?php
/**
 * 微动备份服务器上数据库的控制器。
 * @author 赵臣升
 * CreateTime:2015/04/09 14:25:36.
 */
class BackUpRefreshAction extends Action {
	/**
	 * 定期刷新数据库记录函数。
	 */
	public function refreshDBRecord() {
		$y = date('Y');
		$m = date('m');
		$d = 16;
		if($m == 1){
			$y = $y-1;
			$m = 12;
		}else{
			$m = $m-1;
		}
		$cutOffTime = mktime( 0, 0, 0, $m, $d, $y );
		
		$dellogin = M('loginrecord')->where("operate_time<$cutOffTime")->delete();
		$delauth = M('wechatauthorize')->where("grant_time<$cutOffTime")->delete();
		$delmsg = M('wechatmsginfo')->where("create_time<$cutOffTime")->delete();
	}
}
?>