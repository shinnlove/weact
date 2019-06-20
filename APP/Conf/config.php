<?php
return array(
		'PROJECT_NAME' => 'weact',				//项目名字(weact)
		
		//开启分组
		'APP_GROUP_LIST' => 'Admin,CateIndustry,Community,GuideApp,Home,Interface,Mobile,SceneApp,Service,Subbranch,SuperAdmin,WebSite,WeMall',	//特别注意不能有空格，有空格就立马错
		'DEFAULT_GROUP' => 'Home',
		'APP_GROUP_MODE' => 1,
		'APP_GROUP_PATH' => 'Modules',
		
		//配置数据库参数
		'DB_HOST' => '127.0.0.1',
		'DB_USER' => 'root',
		'DB_PWD' => '',
		//'DB_USER' => 'weact',
		//'DB_PWD' => 'weact@zeran',
		'DB_NAME' => 'weact',
		'DB_PREFIX' => 't_',
		
		//点语法默认解析
		'TMPL_VAR_IDENTIFY' => 'array',
		
		//解析模板路径
		'TMPL_FILE_DEPR' => '_',
		
		//默认过滤规则：字符串（防脚本注入）
		'DEFAULT_FILTER' => 'htmlspecialchars',
		
		//默认URL地址方法
		'URL_MODEL' => 2,
		
		//默认生成URL后缀名
		'URL_HTML_SUFFIX' => 'shtml',
		
		//开启路由
		'URL_ROUTER_ON' => true,
		
		'HOME_ACTION_PATH' => '/Modules/Home/Action',			//Home分组下控制器的路径
		
		'MAX_ADD_COUNT' => 7000, // 最大一次数据的插入量
		
		//配置邮件
		'MAIL_ADDRESS'=>'zhaochensheng1218@163.com', // 邮箱地址
		
		'MAIL_SMTP'=>'smtp.163.com', // 邮箱SMTP服务器
		
		'MAIL_LOGINNAME'=>'zhaochensheng1218@163.com', // 邮箱登录帐号
		
		'MAIL_PASSWORD'=>'19881218', // 邮箱密码
		
		// 云之讯电话平台的配置
		'YZX_ACCOUNT_SID' => '3f3c99e4db60130e4e8802dc72662520',
		'YZX_AUTH_TOKEN' => '9a29a1e7da1739195556945bf5e01d65',
		'YZX_WEACT_APP_ID' => 'fd6045f38b214280a553cedc0b21e3ad',
		
		/*----------微信SDK配置---------*/
		
		'SITE_URL' => 'http://www.we-act.cn/',
		'DOMAIN' => 'http://www.we-act.cn',
		'WECHAT_TOKEN' => '123456', // 开发者URL上的TOKEN
		
		'WECHATPAY_RECORD_VALIDTIME' => 7200, // 默认微动平台微信待支付记录是2小时，过期则重新生成待支付记录
		
		'CURRENT_COMPONENT_PRIORITY' => 0, // 当前测试/使用的组件优先级
		
		/*----------移动端本地调试配置---------*/
		
		'LOCAL_TEST_ON' => true, // 本地测试开启
		//'LOCAL_TEST_ON' => false, // 远程服务器测试关闭
		
		'AUTHORIZE_URL' => 'http://www.we-act.cn',				//授权地址
		//'AUTHORIZE_OPEN' => true,								//false默认本地网页授权关闭，true默认本地网页授权开启，具体是否开启还要看商家是否开启
		'AUTHORIZE_OPEN' => false,								//false默认本地网页授权关闭，true默认本地网页授权开启，具体是否开启还要看商家是否开启

        'WECHAT_AUTHORIZE_REDIRECT' => 'http://www.we-act.cn/Home/WeChatAuthorize/wechatAuthCallback',

);
?>