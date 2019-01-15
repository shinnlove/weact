<?php
/**
 * 本控制器负责微营销板块：积分兑换管理。
 * @author 梁思彬。
 * 已对本控制器进行路径组装处理，代码已优化。
 */
class ExchangeAction extends PCViewLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则前台一改会出错）。
	 * DefineTime:2014/09/20 02:58:25.
	 * @var string	variable	DBfield
	 */
	var $table_name = 'exchangescore';
	var $cc_ex_id = 'exchange_id';
	var $cc_e_id = 'e_id';
	var $cc_ex_name = 'exchange_name';
	var $cc_ex_score = 'exchange_score';
	var $cc_ex_amount = 'exchange_amount';
	var $cc_charged_num = 'charged_num';
	var $cc_ex_imagepath = 'exchange_img_src';
	var $cc_is_del = 'is_del';
	
	/**
	 * 添加积分兑换信息前的页面。
	 */
	public function preAddExchange() {
		$this->display ();
	}
	
	/**
	 * 查看我的积分兑换信息前的页面。
	 */
	public function preMyExchange() {
		$this->display ();
	}
	
	/**
	 * 顾客兑换前页面。
	 */
	public function precustomerExchange() {
		$this->exchange_id = I ( 'exchange_id' );
		$this->display ();
	}
	
	
}
?>