<?php
/**
 * 本控制器处理在线客服事项。
 * CustomerService：在线客服。
 * 2014/06/18 22:30:26
 */
class CustomerServiceAction extends PCViewLoginAction {
	
	/**
	 * 在线客服。
	 */
	public function onlineService(){
		$this->display();
	}
	
	/**
	 * 问题追踪页面（即问题详情）
	 */
	public function onlineQuetionTrace(){
		$data = array(
				'question_id' => I('qid')
		);
		$this->qid = $data['question_id'];
		$this->display();
	}
	
	/**
	 * 回复文本。
	 */
	public function responseText(){
		$this->display();
	}
	
	public function addResponseText(){
		//添加新的文本回复
		$this->display();
	}
	
}
?>