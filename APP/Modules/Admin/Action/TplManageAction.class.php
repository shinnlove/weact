<?php
/**
 * 微动模板管理控制器，该控制器负责模板的显示。
 * @author 赵臣升
 * LatestModify:2015/03/14 14:29:36.
 */
class TplManageAction extends PCViewLoginAction{
	/**
	 * 定义模板托管映射组。
	 * @var array	$reflect	模板托管映射路径
	 */
	var $reflect = array(
		//定义初始化信息
		'admin_microwebsite_indextemplate' => array ( 'template_category' => 0, 'template_type' => 1 ),		//本次初始化模板类别是页面、主页模板
		'admin_microshop_shopindextemplate' => array ( 'template_category' => 0, 'template_type' => 2 ),	//本次初始化模板类别是页面、微商城模板
		'admin_microshop_productlisttemplate' => array ( 'template_category' => 0, 'template_type' => 3 ),	//本次初始化模板类别是页面、后台的商品陈列模板
		'admin_navigationmenu_secondnavigation' => array ( 'template_category' => 0, 'template_type' => 4 ),//本次初始化模板类别是页面、信息二级导航模板
		'admin_simpleinfo_infotemplate' => array ( 'template_category' => 0, 'template_type' => 5 ),		//本次初始化模板类别是页面、信息展示模板
		'admin_ultimateui_uilisteffect' => array ( 'template_category' => 1, 'template_type' => 1 ),		//本次初始化模板类别是css、list模板
		'admin_ultimateui_uibutton' => array ( 'template_category' => 1, 'template_type' => 2 ),			//本次初始化模板类别是css、ui按钮模板
		//定义设置信息
		'admin_microwebsite_setindex' => array ( 'template_category' => 0, 'template_type' => 1 ),			//本次设置模板类别是页面、主页模板	
		'admin_microshop_setshopindex' => array ( 'template_category' => 0, 'template_type' => 2 ),			//本次设置模板类别是页面、微商城模板
		'admin_microshop_setproductlist' => array ( 'template_category' => 0, 'template_type' => 3 ),		//本次设置模板类别是页面、后台的商品陈列模板
		'admin_navigationmenu_setsecondnav' => array ( 'template_category' => 0, 'template_type' => 4 ),	//本次设置模板类别是页面、信息二级导航模板
		'admin_simpleinfo_setinfostyle' => array ( 'template_category' => 0, 'template_type' => 5 ),		//本次设置模板类别是页面、信息展示模板
		'admin_ultimateui_uilistconfirm' => array ( 'template_category' => 1, 'template_type' => 1 ),		//本次设置模板类别是css、list模板
		'admin_ultimateui_uibuttonconfirm' => array ( 'template_category' => 1, 'template_type' => 2 )		//本次设置模板类别是css、ui按钮模板
	);
	
	/**
	 * 定义表映射组。
	 * @var array	$tablereflect	最终的表映射
	 */
	var $tablereflect = array (
		0 => array ( 'templatetable' => 'templatetailor', 'tailortable' => 'personaltailor' ),				//template_category=0时候映射的表templatetailor,personaltailor
		1 => array ( 'templatetable' => 'csstemplate', 'tailortable' => 'csstailor' )						//template_category=1时候映射的表csstemplate,csstailor
	);
	
	/**
	 * 模板托管主调函数、Main()函数。
	 * @param array $info
	 * @return array|boolean	如果是设置模板的时候，返回array数组；如果是设置模板，返回的是true/false
	 */
	public function EntrustTemplate($info = NULL){
		if (isset ( $info ['tplpath'] )){
			return $this->InitTemplate ( $info ); // 如果是tplpath字段，代表是显示模板
		} else if (isset ( $info ['setpath'] )){
			return $this->setTemplate ( $info ); // 如果是setpath，代表是设置模板
		}
	}
	
	/**
	 * 后台初始化模板的函数。
	 * Author：赵臣升。
	 * CreateTime：2014/08/13 17:07:25.
	 * 作用：传入e_id和模板路径，查询出现有的模板信息、商家选择模板的信息，添加至数组并返回。
	 * ❤设计思路：InitTemplate中包含多态的思想，通过传入数组信息（tplpath，e_id）来实现不同模板的不同初始化。
	 * $template_category变量是用来控制页面或者css类别；
	 * $template_type变量来进一步细化确认页面里的某一种或者css里的某一种模板；
	 * 其他类的action函数通过调用本方法，会传入自身所在的控制器+action函数名，进行筛选。
	 * 在初始化的时候，也会根据页面和css进行不同的初始化。
	 * 最后会根据商家的选择进行不同的确认❤。
	 * 
	 * @param array $info 模板所属企业信息和要展现模板的路径信息
	 * @property string tplpath 模板路径
	 * @property string e_id 企业编号
	 * @return array $tpresult $tpresult数组中新增一个字段selected，标记当前商家选择的模板
	 */
	public function InitTemplate($info = NULL) {
		// 准备工作一：映射模板分类与类型
		$template_category = $this->reflect [$info ['tplpath']] ['template_category']; 						// 模板的类别，如：0代表是页面模板、1代表是css模板
		$template_type = $this->reflect [$info ['tplpath']] ['template_type']; 								// 模板的分类，如：0,1代表是主页九宫格奢华模板、1,2代表是css模板下的ui按钮模板
		
		// 准备工作二：映射模板的两张基本表
		$templatetable = M ( $this->tablereflect [$template_category] ['templatetable'] ); 					// 映射微动模板表
		$tailortable = M ( $this->tablereflect [$template_category] ['tailortable'] ); 						// 映射模板私人定制表
		
		// Step1：找出所有微动提供的页面模板$tpresult
		$tplmap = array (
				'template_type' => $template_type, // 相应模板类型
				'obsolete' => 0, // 没有过期的
				'is_del' => 0 
		);
		$alltemplate = $templatetable->where ( $tplmap )->select ();			// 模板数据（也是最终模板数据$finalresult）
		
		// Step2：找出商家在该类别公用模板自定义的记录$ptresult（如果没有该记录，使用默认该类的默认模板）
		$ptmap = array (
				'e_id' => $info ['e_id'],
				'nav_customized' => 0, // 2015/03/14加：如果是nav_customized=1代表是企业自己定制的不同类模板（由navgitaion里的template_id去读取），nav_customized=0代表是公用模板
				'template_type' => $template_type,
				'is_del' => 0
		);
		$personaltpl = $tailortable->where ( $ptmap )->find ();				// 尝试找出商家定制的模板（也可能没有该记录、是null）
		
		//Step3：标记商家已选择的模板（在$alltemplate里选择）
		if ($personaltpl) {
			// 情形一：已选过模板，使用选中的模板去展示，最终展示的模板以selected字段值为准
			for ($i = 0; $i < count ( $alltemplate ); $i ++) {
				if ($alltemplate [$i] ['template_id'] == $personaltpl ['template_id']) {
					$alltemplate [$i] ['selected'] = 1;
				} else {
					$alltemplate [$i] ['selected'] = 0;
				}
			}
		} else {
			// 情形二：没有选过公用模板，使用默认的模板去展示，最终展示的模板以selected字段值为准
			for ($i = 0; $i < count ( $alltemplate ); $i ++) {
				if ($alltemplate [$i] ['default_selected'] == 1) {
					$alltemplate [$i] ['selected'] = 1;
				} else {
					$alltemplate [$i] ['selected'] = 0;
				}
			}
		}
		return $alltemplate;
	}
	
	/**
	 * 设置模板函数，形参传入一组数组，进行不同的模板设置。
	 * Author：赵臣升。
	 * CreateTime：2014/08/13 20:34:25.
	 * @param array $setinfo 传入设置信息，包含：1、商家编号；2、控制器路径（判别设置模板类型）；3、要设置的模板编号。
	 * @property string setpath 要设置的模板信息路径（用作映射）
	 * @property string e_id 商家编号
	 * @property string template_id 要设置的模板主键
	 * @return boolean $setresult 返回设置成功或失败
	 */
	public function setTemplate($setinfo = NULL) {
		// 准备工作一：映射模板分类与类型
		$template_category = $this->reflect [$setinfo ['setpath']] ['template_category']; 					// 模板的类别，如：0代表是页面模板、1代表是css模板
		$template_type = $this->reflect [$setinfo ['setpath']] ['template_type']; 							// 模板的分类，如：0,1代表是主页九宫格奢华模板、1,2代表是css模板下的ui按钮模板
		
		// 准备工作二：映射模板的两张基本表
		$templatetable = M ( $this->tablereflect [$template_category] ['templatetable'] ); 					// 映射微动模板表
		$tailortable = M ( $this->tablereflect [$template_category] ['tailortable'] ); 						// 映射模板私人定制表
		
		$finalflag = false;										//模板替换成功标志（先设置为false）
		$addmap = array ();										//声明定制模板变量
		
		// Step1：查出所选择页面模板的详细信息
		$selectedmap = array (
				'template_type' => $template_type,
				'template_id' => $setinfo ['template_id'],
				'is_del' => 0
		);
		$selectedtpl = $templatetable->where ( $selectedmap )->find (); // 在微动模板表中找选中模板的信息$selectedtpl
		
		// Step2：检查商家有没有选择该类别的模板（特别注意，暂不带上template_id检索，因为不支持同种模板多样化选择，那是特殊服务）
		$premap = array (
				'e_id' => $setinfo ['e_id'], // 当前商家编号
				'nav_customized' => 0, // 2015/03/14加：如果是nav_customized=1代表是企业自己定制的不同类模板（由navgitaion里的template_id去读取），nav_customized=0代表是公用模板
				'template_type' => $template_type, // 当前模板类型
				'is_del' => 0
		);
		$ptresult = $tailortable->where ( $premap )->find (); // 在私人定制模板表中找
		
		// Step3：进行模板更替
		if ($ptresult) {
			// 商家选择过该类别模板
			if ($selectedtpl ['template_id'] != $ptresult ['template_id']) {
				// 如果不是当前选择的模板，则进行更换
				$ptresult ['template_id'] = $selectedtpl ['template_id'];
				$ptresult ['template_name'] = $selectedtpl ['template_name'];
				if ($template_category == 0) {
					$ptresult ['template_realpath'] = $selectedtpl ['template_realpath'];		//如果是页面模板
				} else if ($template_category == 1) {
					$ptresult ['css_content'] = $selectedtpl ['css_content'];					//如果是css模板
				}
				$ptresult['customized_time'] = timetodate ( time () );
				$setresult = $tailortable->save ( $ptresult ); // 保存回数据库
				if ($setresult) $finalflag = true;
			} else {
				$finalflag = true;	// 选择了一模一样的模板，不处理直接返回true
			}
		} else {
			// 商家没有选择过该类别模板，第一次选择模板
			if ($selectedtpl ['default_selected'] == 0) {
				//如果这次选择的模板不是默认模板
				$addmap = array (
						'tailor_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id' => $setinfo ['e_id'],
						'nav_customized' => 0, // 是公用模板，非自定义模板，2015/03/14
						'template_type' => $template_type,
						'template_id' => $setinfo ['template_id'],
						'template_name' => $selectedtpl ['template_name'],
						'customized_time' => timetodate ( time () ) 
				);
				if ($template_category == 0) {
					//商家选择的是页面模板（并且不是默认的）
					$addmap ['template_indexpath'] = $selectedtpl ['template_indexpath'];
					$addmap ['template_realpath'] = $selectedtpl ['template_realpath'];
				} else if ($template_category == 1) {
					//商家选择的是css模板（并且不是默认的）
					$addmap ['css_content'] = $selectedtpl ['css_content'];
				}
				$setresult = $tailortable->add ( $addmap );
				if ($setresult) $finalflag = true;
			} else {
				$finalflag = true;	// 选择了默认模板，直接返回true（不处理本次提交）
			}
		}
		//特别注意：最后对css模板更改进行一层过滤（如果更改的是列表风格：1、列表二级信息导航；2、默认信息详情；3、列表微商城；4、默认产品列表，则需要对他们定制css→costomized_css进行修改）
		if ($template_category == 1 && $template_type == 1) {
			$checkcss = array ( 
					'e_id' => $setinfo ['e_id'], 
					'nav_customized' => 0, // 是公用模板，2015/03/14改
					'is_del' => 0
			);
			$checktable = M ( 'personaltailor' );
			for ($i = 2; $i < 6; $i ++) {
				$checkcss ['template_type'] = $i;	//初始化检查模板css定制，从2开始，2，3，4，5一共4种模板类型是有css定制的（当然必须是列表式）
				$checkresult = $checktable->where ( $checkcss )->find ();
				if ($checkresult) {
					//如果有该类模板：微动模板表里，列表式4种为：0004，0008，0012，0014
					if ($checkresult ['template_id'] == '0004' || $checkresult ['template_id'] == '0008' || $checkresult ['template_id'] == '0012' || $checkresult ['template_id'] == '0014') {
						$checkresult ['customized_css'] = $addmap ['tailor_id'];
						$checktable->save ( $checkresult );						//有该类模板，并且是默认模板，则直接加上css
					}
				} else {
					//如果没有该类模板（说明商家使用列表式默认风格），则直接生成一条，并且定制为当前css
					//StepA：先查询数据
					$tplinfomap = array(
							'template_type' => $i,
							'obsolete' => 0,
							'default_selected' => 1,
							'is_del' => 0
					);
					$tpltable = M ( 'templatetailor' );
					$tplinfo = $tpltable->where ( $tplinfomap )->find ();
					//StepB：定制数据
					$addcheck = array(
							'tailor_id' => md5 ( uniqid ( rand (), true ) ),
							'e_id' => $setinfo ['e_id'],
							'nav_customized' => 0, // 定制一条nav_customized为0的公用模板数据
							'template_type' => $i,
							'template_id' => $tplinfo ['template_id'],
							'template_name' => $tplinfo ['template_name'],
							'template_indexpath' => $tplinfo ['template_indexpath'],
							'template_realpath' => $tplinfo ['template_realpath'],
							'customized_time' => timetodate ( time () ),
							'customized_css' => $addmap ['tailor_id']
					);
					$checktable->add ( $addcheck );								//没有该类模板，相当于使用默认模板，则直接生成一条加上该css的数据
				}
			}
		}
		return $finalflag;
	}
	
}
?>