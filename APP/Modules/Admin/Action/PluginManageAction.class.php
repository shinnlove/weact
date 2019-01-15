<?php
/**
 * 插件管理控制器。
 * @author Shinnlove
 *
 */
class PluginManageAction extends PCViewLoginAction {
	
	//大转盘一览表页面
	public function luckyWheel(){
		$this->display();
	}
	
	//添加、编辑大转盘活动页面
	public function addLuckyWheel(){
		$data = array(
				'plugin_activity_id' => I('plugin_activity_id')			//试着接收传来的插件活动编号
		);
		if($data['plugin_activity_id']=='') {						//如果没有接收到编号，则是新添加活动，直接让用户添加活动；否则是编辑活动，如下↓
			$this->editflag = 0;									//推送编辑标记：0为false
			$this->display();
		}else{
			//缩写：editPluginActivity→epa
			$epamap = array(
					'plugin_activity_id' => $data['plugin_activity_id'],//当前活动编号
					'is_del' => 0
			);
			//做视图拼接，pluginactivity表是主表，别名p(parent)；pluginprize表是子表，别名c(child)。
			$sql = 'p.plugin_activity_id = c.plugin_activity_id and p.is_del = 0 and c.is_del = 0 and p.plugin_activity_id = \''.$epamap['plugin_activity_id'].'\'';
			$model = new Model();
			$eparesult =  $model->table('t_pluginactivity p, t_pluginprize c')->where ( $sql )->order('c.prize_order')->field('p.*, c.*')->select();	//查出活动
			$this->editflag = 1;									//推送编辑标记：1为true，前台判断
			$this->epainfo = $eparesult;							//推送活动信息给前台，前台根据情况选择
			$this->display();										//展示页面
		}
	}
	
	//大转盘活动中奖结果详情页面winPrizeResultLA
	public function winPrizeResultLA(){
		$this->paid = I('paid');
		$pamap['plugin_activity_id'] = $this->paid;
		$patable = M('pluginactivity');
		$this->cpa = $patable->where($pamap)->find();		//查出活动的一些信息，显示在标题栏上
		$this->display();									//展示页面
	}
	
}
?>