<?php
/**
 * 这是WeMall分组下的config配置文件。
 */
return array (
		// 定义后台视图模板路径
		'TMPL_PARSE_STRING' => array (
				'__PUBLIC__' => __ROOT__ . '/' . APP_NAME . '/Modules/' . GROUP_NAME . '/Tpl/Public' 
		), 
		// 指定错误页面模板路径
		'TMPL_ACTION_ERROR' => './Public/Tpl/error.html',
		'TMPL_ACTION_SUCCESS' => './Public/Tpl/success.html' 
);
?>