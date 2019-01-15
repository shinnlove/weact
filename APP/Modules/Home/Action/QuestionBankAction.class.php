<?php
/**
 * 一站到底答题。
 * @author 小万，赵臣升。
 *
 */
class QuestionBankAction extends MobileGuestAction {
	/**
	 * 一站到底（问卷的答题页面）
	 */
	public function questionPage() {
		$qamap = array (
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$qainfo = M ( 'questionactivity' )->where ( $qamap )->find(); // 找到当前商家开展的答题活动
		if(! qainfo) _404( '答题活动已经结束' );
		
		$sql = 'qal.question_id = qb.question_id and qal.question_activity_id = \'' . $qainfo ['question_activity_id'] . '\' and qal.is_del = 0 and qb.is_del = 0';
		$field = 'qal.list_record_id, qal.question_activity_id, qal.question_order, qal.question_id, qb.question, qb.question_group, qb.question, qb.option_a, qb.option_b, qb.option_c, qb.option_d, qb.option_e, qb.option_f, qb.answer, qb.answer_reason, qb.goto_question, qb.question_type, qb.group_type, qb.question_score';
		$model = new Model();
		$questionlist = $model->table ( 't_questionactivitylist qal, t_questionbank qb' )->where ( $sql )->field ( $field )->order ( 'qal.question_order' )->select ();
		
		$questionnumber = count ( $questionlist ); // 计算问题数量
		// 对题目的选项进行分数估值
		for($i = 0; $i < $questionnumber; $i ++) {
			if($questionlist [$i] ['answer'] == '-1') {
				// 问卷方式，没有正确答案，如此每个选项都可以获得类似正确答案的分数
				$questionlist [$i] ['option_a_score'] = $questionlist [$i] ['question_score'];
				$questionlist [$i] ['option_b_score'] = $questionlist [$i] ['question_score'];
				$questionlist [$i] ['option_c_score'] = $questionlist [$i] ['question_score'];
				$questionlist [$i] ['option_d_score'] = $questionlist [$i] ['question_score'];
				$questionlist [$i] ['option_e_score'] = $questionlist [$i] ['question_score'];
				$questionlist [$i] ['option_f_score'] = $questionlist [$i] ['question_score'];
			} else {
				// 一站到底方式，有正确答案，只有标准答案才有分数，其他答案都是0
				$questionlist [$i] ['option_a_score'] = 0;
				$questionlist [$i] ['option_b_score'] = 0;
				$questionlist [$i] ['option_c_score'] = 0;
				$questionlist [$i] ['option_d_score'] = 0;
				$questionlist [$i] ['option_e_score'] = 0;
				$questionlist [$i] ['option_f_score'] = 0;
				$field = 'option_' . $questionlist [$i] ['answer'] . '_score';
				$questionlist [$i] [$field] = $questionlist [$i] ['question_score'];
			}
		}
		
		$this->qainfo = $qainfo; // 推送答题活动信息
		$this->qlistinfo = $questionlist; // 推送答题列表信息
		$this->total = $questionnumber; // 推送答题数量（用作前台for循环）
		$this->display ();
	}
	
	/**
	 * 答题结果提交处理。
	 */
	public function validateAnswer() {
		$qbmap = array (
				'question_id' => I ( 'qid' ),
				'answer' => I ( 'choice' ),
				'is_del' => 0 
		);
		$qbtable = M ( 'questionbank' );
		
		$this->qbresult = $qbtable->where ( $qbmap )->select ();
		if ($this->qbresult) {
			$is_correct = 1;
		} else {
			$is_correct = 0;
		}
		// 记录用户答题情况
		$armap = array (
				'answer_record_id' => md5 ( uniqid ( rand (), true ) ),
				'question_activity_id' => '0',
				'question_id' => I ( 'qid' ),
				'openid' => 'oeovpt13JCmPNLaU6dTSh8mt68N4',
				'answer_time' => date ( 'Y-m-d H:m:s' ),
				'user_choice' => I ( 'choice' ),
				'is_correct' => $is_correct,
				'is_del' => '0' 
		);
		$artable = M ( 'questionanswer' );
		$artable->data ( $armap )->add ();
		if ($is_correct) {
			$this->ajaxReturn ( array (
					'status' => 1 
			), 'json' );
		} else {
			$this->ajaxReturn ( array (
					'status' => 0 
			), 'json' );
		}
	}
}
?>