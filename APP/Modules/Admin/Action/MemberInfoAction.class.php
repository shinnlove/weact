<?php
/**
 * 本控制器处理微SCRM版块下，会员类信息的基本设置。
 * 2014/06/21
 * @author：钟建友。
 */
class MemberInfoAction extends PCViewLoginAction {
	
	/**
	 * 会员卡显示页面membercard。
	 * OriginalAuthor：钟建友。（写得不错）
	 * Optimized：赵臣升。
	 * Time:2014/09/26 03:35:25.
	 */
	public function memberCard(){
		//Step1：在此读出数据库中cardtemplate表中的信息，直接推送到前台显示，缩写：cardtemplate:ct
		$cardmap = array(
				'obsolete' => 0,								//没有过期
				'is_del' => 0									//没有删除
		);
		$cttable = M('cardtemplate');
		$ctresult = $cttable->where($cardmap)->select();	//当前系统所有的模板
	
		//Step2-1：查询商家的名片样式
		$currentcardmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],
				'is_del' => 0
		);
		$mitable = M('memberinfo');
		$miresult = $mitable->where($currentcardmap)->find();
	
		//Step2-2：判断商家当前名片类型和哪个。
		$defaultCard = 1;									//默认使用系统卡片标记为true
		if($miresult ['template_id'] == '-1'){
			$defaultCard = 0;								//默认不适用系统卡片
			$this->diypath = assemblepath($miresult ['membercard_path'], true);	//推送自定义路径（已组装）
			for($i=0; $i<count($ctresult); $i++){
				$ctresult[$i]['selected'] = 0;				//不适用系统卡片，直接全部标记为0
			}
		}else{
			//如果是系统卡片或者未选
			if($miresult ['template_id'] != null && $miresult ['template_id'] != ''){
				//已经选择过系统名片，则从中找出并标记选中的名片。
				for($i=0; $i<count($ctresult); $i++){
					if($ctresult[$i]['template_id'] == $miresult['template_id']){
						$ctresult[$i]['selected'] = 1;
					}else{
						$ctresult[$i]['selected'] = 0;
					}
				}
			}else{
				//没有选择过系统名片也没有自定义，标记系统默认的名片选中。
				for($i=0; $i<count($ctresult); $i++){
					if($ctresult[$i]['default_selected'] == 1){
						$ctresult[$i]['selected'] = 1;
					}else{
						$ctresult[$i]['selected'] = 0;
					}
				}
			}
		}
	
		//Step3：推送信息到页面。
		$this->defaultflag = $defaultCard;					//推送默认标志
		$this->card = $ctresult;							//推送格式化后的卡片数组
		$this->display();
	}
	
	/**
	 * 会员特权设置页面展示。
	 * Author：钟建友。
	 */
	public function memberPrivilege(){
		$mimap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$mitable = M('memberinfo');
		$this->pl = $mitable->where($mimap)->find();
		$this->display();
	}
	
	/**
	 * 该函数为设置会员等级页面。
	 */
	public function memberLevel(){
		$this->display();
	}
	
}
?>