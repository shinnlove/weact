<?php
/**
 * 这是Extend分组下的config配置文件。
 */
return array (
		// 定义后台视图模板路径
		'TMPL_PARSE_STRING' => array (
				'__PUBLIC__' => __ROOT__ . '/' . APP_NAME . '/Modules/' . GROUP_NAME . '/Tpl/Public' // css、js、images等的路径
		), 
		// 指定后台成功、错误和异常页面模板路径
		'TMPL_ACTION_ERROR' => 'Public/Tpl/error', // Extend下面的错误页面（控制器方式寻址）
		'TMPL_ACTION_SUCCESS' => 'Public/Tpl/success', // Extend下面的成功页面 （控制器方式寻址）
		'TMPL_EXCEPTION_FILE'   =>  APP_NAME . '/Modules/' . GROUP_NAME . '/Tpl/Public/Tpl/exception.html', // 异常页面的模板文件（文件方式寻址）
);
?>