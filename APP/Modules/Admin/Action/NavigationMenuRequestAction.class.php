<?php
/**
 * 非商品类导航栏目的增删改查控制器
 *
 * 此控制器处理微商城下的导航：
 * 包括：
 * 1、自定义总导航；
 * 2、二级导航样式；
 * 3、悬浮导航样式。
 *
 */
class NavigationMenuRequestAction extends PCRequestLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则会出错）。
	 * DefineTime:2014/09/16 03:20:25.
	 * @var string	variable	DBfield
	 */
	var $table_name = 'navigation';
	var $cc_nav_id = 'nav_id';
	var $cc_e_id = 'e_id';
	var $cc_fathernav_id = 'father_nav_id';
	var $cc_nav_level = 'nav_level';
	var $cc_nav_name = 'nav_name';
	var $cc_nav_english = 'nav_english';
	var $cc_nav_imagepath = 'nav_image_path';
	var $cc_nav_url = 'nav_url';
	var $cc_channel = 'channel';
	var $cc_nav_type = 'nav_type';
	var $cc_nav_order = 'nav_order';
	var $cc_description = 'description';
	var $cc_display_tailor = 'display_tailor';
	var $cc_add_time = 'add_time';
	var $cc_latest_modify = 'latest_modify';
	var $cc_temporary_stop = 'temporary_stop';
	var $cc_is_del = 'is_del';
	
	/**
	 * ==============PART2：ajax处理部分==============
	 */
	
	/**
	 * (已兼容多类型导航)删除某导航。
	 */
	public function deleteNav() {
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/navigation', '', '', true ) ); // 防止恶意打开
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => '网络繁忙，请稍后再试！'
		);
		// 接收要删除的导航信息
		$delnavmap = array (
				$this->cc_nav_id => I ( 'nid', '-1' ),
				$this->cc_nav_type => I ( 'ntype', 1 ), // 默认导航是1
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_is_del => 0
		);
		// 直接毙掉的情况
		if ($this->navExistChild ( $delnavmap )) {
			// Case1：如果导航下存在二级导航，直接返回无法删除，请选择无下级导航的导航。
			$ajaxresult ['errCode'] = 10002;
			$ajaxresult ['errMsg'] = "该导航下有子级菜单，请先移动或删除其子级菜单！";
			$this->ajaxReturn ( $ajaxresult ); // 直接返回
		}
		// 允许删除的情形：
		$removeresult = $this->removeNavInfoPro ( $delnavmap ); // 移除该导航的内容
		if ($removeresult ['errCode'] == 0) {
			$delresult = M ( $this->table_name )->where ( $delnavmap )->setField ( $this->cc_is_del, 1 ); // 删除该导航
			if ($delresult) {
				$ajaxresult ['errCode'] = 0;
				$ajaxresult ['errMsg'] = "ok";
			} else {
				$ajaxresult ['errCode'] = 10004;
				$ajaxresult ['errMsg'] = "删除导航失败，请稍后再试！";
			}
		} else {
			$ajaxresult ['errCode'] = $removeresult ['errCode'];
			$ajaxresult ['errMsg'] = $removeresult ['errMsg'];
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * (已兼容多类型导航)移除导航下服装、餐品或商品的post函数。
	 * Author：赵臣升。
	 * CreateTime：2014/10/03 10:30:40.
	 * 移除导航内容的思想：
	 * 一、对于叶子节点导航：
	 * 1、信息类：直接去simpleinfo表里清空该导航下隶属的一条信息。
	 * 2、服装类：在商品表中，直接将以他为父级菜单的服装(type=2)nav_id编号改为-1→该服装会进入未分类。
	 * 3、超链接类（必然是叶子导航）：直接将超链接置空即可。
	 * 4、餐品类：在餐品表中，直接将以他为父级菜单的餐品的nav_id编号改为-1→餐品会进入未分类。
	 * 5、商品类：在商品表中，直接将以他为父级菜单的商品(type=5)nav_id编号改为-1→该商品会进入未分类。
	 * 二、对于父级导航：
	 * 1、信息类；2、服装类；4、餐品类；5、商品类，并且不可能是3超链接类。
	 * 对于这几种导航直接告知无法进行清除，请选择一个叶子节点导航。
	 *
	 * 操作：
	 * 对于接收到的要清空信息（商品）的导航，直接判断有没有下属导航，如果没有才继续清空。
	 *
	 * LatestModify：2015/04/01 19:32:25.
	 * ModifyFor：兼容多种类型导航（信息、服装、餐饮、商品）。
	 */
	public function clearNavConfirm(){
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/navigation', '', '', true ) ); // 防止恶意打开
	
		// 准备ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		// 接收页面发来的ajax信息
		$clearnavinfo = array (
				'nav_id' => I ( 'nid' ),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'nav_type' => I ( 'ntype', -1 )
		);
		// 直接毙掉的情况
		if ($this->navExistChild ( $clearnavinfo )) {
			// Case1：如果导航下存在二级导航，直接返回无法删除，请选择无下级导航的导航。
			$ajaxresult ['errCode'] = 10008;
			$ajaxresult ['errMsg'] = "要移除内容的导航下有子级菜单，请选择没有子级菜单的导航！";
			$this->ajaxReturn ( $ajaxresult ); // 直接返回
		}
	
		// 检测通过
		$removeresult = $this->removeNavInfoPro ( $clearnavinfo ); // 移除导航下的内容（信息或商品）
		if ($removeresult ['errCode'] == 0) {
			$ajaxresult ['errCode'] = 0;
			$ajaxresult ['errMsg'] = "成功清除该导航下的" . $removeresult ['data'] . "！";
		} else {
			$ajaxresult ['errCode'] = $removeresult ['errCode'];
			$ajaxresult ['errMsg'] = $removeresult ['errMsg'];
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	/**
	 * (已兼容多类型导航，已兼容重复移除操作)移除导航下的信息、服装、超链接地址、餐品或商品的处理函数。
	 * @param array $navinfo 要移除内容的某导航信息
	 * @property string e_id 必须字段，商家编号
	 * @property string nav_type 必须字段：导航类型
	 * @return array $removeresult 导航下内容是否被移除
	 */
	public function removeNavInfoPro($navinfo = NULL) {
		$removeresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		); // 准备要移除的信息
		if (! empty ( $navinfo )) {
			// Case2：如果导航是叶子节点导航
			$successflag = false;									// 移除导航下属内容成功标记
			$type = '类型';											// 导航类型
			switch ($navinfo ['nav_type']) {
				case 1:
					$type = '信息';									//如果是信息类叶子节点导航，去simpleinfo表里删除该导航所属的信息
					$infoclear = array (
							'e_id' => $navinfo ['e_id'],
							'nav_id' => $navinfo ['nav_id'],
							'is_del' => 0
					);
					$exist = M ( 'simpleinfo' )->where ( $infoclear )->count ();
					if ($exist) {
						$successflag = M ( 'simpleinfo' )->where ( $infoclear )->setField ( 'is_del', 1 );
					} else {
						$successflag = true; // 没信息直接过
					}
					break;
				case 2:
					$type = '服装';									//如果是服装类叶子节点导航
					$costumesclear = array (
							'e_id' => $navinfo ['e_id'],
							'nav_id' => $navinfo ['nav_id'],
							'product_type' => 2, // 2是服装类商品
							'is_del' => 0
					);
					$exist = M ( 'product' )->where ( $costumesclear )->count ();
					if ($exist) {
						$successflag = M ( 'product' )->where ( $costumesclear )->setField ( 'nav_id', '-1' ); // 将服装移入未分类中
					} else {
						$successflag = true; // 没服装直接过
					}
					break;
				case 3:
					$type = '超链接';									//如果是超链接导航（必然是叶子节点）
					$successflag = M ( $this->table_name )->where ( $navinfo )->setField ( 'nav_url', '' ); // 超链接直接清空nav_url
					break;
				case 4:
					$type = '餐品';									//如果是超链接导航（必然是叶子节点）
					$catemap = array (
							'e_id' => $navinfo ['e_id'],
							'nav_id' => $navinfo ['nav_id'],
							'is_del' => 0
					);
					$exist = M ( 'cate' )->where ( $catemap )->count ();
					if ($exist) {
						$successflag = M ( 'cate' )->where ( $catemap )->setField ( 'nav_id', '-1' ); // 将餐饮表中的餐品导航设置为-1（未分类）
					} else {
						$successflag = true; // 没餐品直接过
					}
					break;
				case 5:
					$type = '商品';									//如果是超链接导航（必然是叶子节点）
					$proclear = array (
							'e_id' => $navinfo ['e_id'],
							'nav_id' => $navinfo ['nav_id'],
							'product_type' => 5, // 5是非服装类商品
							'is_del' => 0
					);
					$exist = M ( 'product' )->where ( $proclear )->count ();
					if ($exist) {
						$successflag = M ( 'product' )->where ( $proclear )->setField ( 'nav_id', '-1' ); // 将商品列入未分类
					} else {
						$successflag = true; // 没商品直接过
					}
					break;
				default:
					$type = '未知类型';
					$removeresult ['errCode'] = 10003;
					$removeresult ['errMsg'] = "未知类型导航，请确认操作无误！";
					break;
			}
			// 如果成功了，就返回OK信息
			if ($successflag) {
				$removeresult ['errCode'] = 0;
				$removeresult ['errMsg'] = "ok";
				$removeresult ['data'] = $type;
			}
		}
		return $removeresult;
	}
	
	/**
	 * (已兼容多类型导航)重置所有导航的post处理函数。
	 * Author：赵臣升。
	 * CreateTime：2014/10/02 22:16:25.
	 * 处理方法：
	 * 1、将所有导航一并删除；
	 * 2、将所有信息导航关联的simpleinfo内容删除；
	 * 3、将所有商品放入未分类下，不做删除。
	 *
	 * LatestModify：2015/04/01 19:32:25.
	 * ModifyFor：兼容多种类型导航（信息、服装、餐饮、商品）。
	 */
	public function resetAllNav() {
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/navigation', '', '', true ) ); // 防止恶意打开
	
		// 初始化ajax返回信息
		$ajaxresult = array (
				'errCode' => 10001,
				'errMsg' => "网络繁忙，请稍后再试！"
		);
		$successflag = false;				//定义重置成功标志为false
		// 表信息
		$navtable = M ( $this->table_name ); // 初始化导航表
		$infotable = M ( 'simpleinfo' ); // 信息表
		$protable = M ( 'product' ); // 服装和商品表
		$catetable = M ( 'cate' ); // 餐饮表
		// 准备企业信息
		$resetmap = array (
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$navcount = $navtable->where ( $resetmap )->count (); // 统计下企业当前的导航数目
		if ($navcount > 0) {
			// 如果有导航，则需要重置所有导航
			$navtable->startTrans (); // 开启事务过程
				
			$successflag = $navtable->where ( $resetmap )->setField ( 'is_del', 1 );
			if ($successflag) {
				// Step1：（如果有信息）删除该商家下所有信息详情记录
				$delinforesult = $infotable->where ( $resetmap )->setField ( 'is_del', 1 );
				// Step2：（如果有服装或商品）将该商家所有商品列入未分类，nav_id变成'-1'
				$delproresult = $protable->where ( $resetmap )->setField ( 'nav_id', '-1' );
				// Step3：（如果有餐品）将该商家所有餐品列入未分类，nav_id变成'-1'
				$delcateresult = $catetable->where ( $resetmap )->setField( 'nav_id', '-1' );
				// 处理结果
				$navtable->commit (); // 提交事务
				$ajaxresult ['errCode'] = 0;
				$ajaxresult ['errMsg'] = "所有导航信息已被重置，若有商品，暂被列入未分类！";
			} else {
				$navtable->rollback (); // 撤销事务
				$ajaxresult ['errCode'] = 10002;
				$ajaxresult ['errMsg'] = "重置所有导航出错，请尝试分条删除！";
			}
		} else {
			$ajaxresult ['errCode'] = 10003;
			$ajaxresult ['errMsg'] = "当前没有任何导航信息！";
		}
		$this->ajaxReturn ( $ajaxresult ); // 返回给前台信息
	}
	
	/**
	 * 读取微商自定义导航栏目,只读取服务类导航（非商品分类）,但不含'无上级导航'，此处给simpleinfo的index页面用的。
	 */
	public function readWithoutRoot() {
		$father_nav_id = $_REQUEST[$this->cc_fathernav_id];
		$db_table = M($this->table_name);
	
		$where_string = array(
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_fathernav_id => I('id','-1'),
				$this->cc_nav_type => 1,
				$this->cc_is_del => 0
		);
	
		$rs =  $db_table->where($where_string)->order($this->cc_nav_order.' asc')->select();
		$items = array ();				//清空$items数组
		foreach ( $rs as $row ) {
			$temp['id'] = $row[$this->cc_nav_id];
			$temp['text'] = $row[$this->cc_nav_name];
			$temp['state'] = $this->navExistChild($row) ? 'closed' : 'open';
			if($row[$this->cc_nav_id] == $father_nav_id){
				$temp['checked'] = "true";
			}else{
				$temp['checked'] = "false";
			}
			array_push ( $items, $temp);
		}
	
		echo json_encode($items);
	}
	
	/**
	 * 初始化所有微商的导航菜单，并带有贴心的提示。
	 * OriginalAuthor：骆泽刚。
	 * ModifyAuthor：赵臣升。
	 * ModifyTime：2014/09/13 02:28:25.
	 */
	public function allNavInit() {
		$navID = I ( 'id', '-1' );
	
		$pagenum = isset ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1; // 第几页
		$rowsnum = isset ( $_POST ['rows'] ) ? intval ( $_POST ['rows'] ) : 10; // 每页几条
		$sort = isset ( $_POST ['sort'] ) ? strval ( $_POST ['sort'] ) : 'nav_order'; // 按什么排序
		$order = isset ( $_POST ['order'] ) ? strval ( $_POST ['order'] ) : 'asc'; // 排序方式
	
		$result = array ();								//因为easyUI的treegrid会递归的读取数据，所以这里要清空最终导航结果数组
		$navtable = M ( $this->table_name );
	
		$currentmap = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家下
				$this->cc_fathernav_id => $navID,						//注意这里的id是固定的，不可以变更
				$this->cc_is_del => 0
		);
	
		if ($navID == '-1') {
			//查询一级栏目
			$result ['total'] = $navtable->where ( $currentmap )->count ();
			$rs = $navtable->where ( $currentmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( '' . $sort . ' ' . $order )->select ();
			//在此进行图片格式显示的处理（非常重要）：
			for($i = 0; $i < count ( $rs ); $i ++) {
				$rs [$i] [$this->cc_nav_imagepath] = assemblepath ( $rs [$i] [$this->cc_nav_imagepath] );	//对$rs里的导航图片进行路径处理
			}
	
			$items = array ();
			foreach ($rs as $row) {
				$row ['state'] = $this->navExistChild ( $row ) ? 'closed' : 'open';
				if (! $this->navExistChild ( $row )) {
					$row ['has_info'] = $this->navExistInfo ( $row ) ? 1 : 0;					//如果导航下不存在子级导航，判断是否存在信息或商品
				} else {
					$row ['has_info'] = 2;	//2代表顶级导航有子级菜单
				}
				array_push ( $items, $row );
			}
			$result ["rows"] = $items;
		} else {
			//查询子级栏目
			$rs = $navtable->where ( $currentmap )->order ( '' . $sort . ' ' . $order )->select ();
			//在此进行图片格式显示的处理（非常重要）：
			for($i = 0; $i < count ( $rs ); $i ++) {
				$rs [$i] [$this->cc_nav_imagepath] = assemblepath ( $rs [$i] [$this->cc_nav_imagepath] );	//对$rs里的导航图片进行路径处理
			}
				
			$items = array ();
			foreach ($rs as $row) {
				$row ['state'] = $this->navExistChild ( $row ) ? 'closed' : 'open';
				$row ['has_info'] = $this->navExistInfo ( $row ) ? 1 : 0;						//子级导航下是否存在信息或商品
				array_push ( $items, $row );
			}
			$result = $items;
		}
		echo json_encode ( $result );
	}
	
	/**
	 * 读取微商顶级导航的easyUI post请求。（该函数目前不用）
	 * 说明：easyUI的combotree会循环递归的去用同样的方法来初始化，所以在一级导航出来以后，二级导航也会跟着查询出来。
	 * 要注意格式。
	 * OriginalAuthor：骆泽刚。
	 * ImproveAuthor：赵臣升。
	 * ModifyTime：2014/09/08 04:10:25。
	 * @param number $nav_type	combotree的传参
	 * @return echo json	将查到的导航数据用json按照easyUI指定格式打包。
	 */
	public function readNavInfo($nav_type = 1) {
		$navmap = array(
				$this->cc_e_id => $_SESSION['curEnterprise'] ['e_id'],
				$this->cc_fathernav_id => I('id','-1'),
				$this->cc_nav_type => $nav_type,
				$this->cc_is_del => 0
		);
	
		$navtable = M($this->table_name);
		$rs =  $navtable->where($navmap)->order('nav_order asc')->select();
		$items = array ();							//声明（清空）存放导航数据的数组
	
		if ($navmap[$this->cc_fathernav_id] == '-1'){
			//如果是查询一级栏目，设置item的格式
			$items[0]['id'] = '-1';
			$items[0]['text'] = '无上级导航';
			$items[0]['state'] = 'open';
		}
	
		foreach ( $rs as $row ) {
			$temp['id'] = $row[$this->cc_nav_id];
			$temp['text'] = $row[$this->cc_nav_name];
			$row ['state'] = $this->navExistChild($row) ? 'closed' : 'open';
			if($row[$this->cc_nav_id] == $father_nav_id){
				$temp['checked'] = "true";
			}else{
				$temp['checked'] = "false";
			}
			$temp['state'] = $row['state'];
			array_push ( $items, $temp );			//只能添加数字键值对
		}
	
		echo json_encode($items);
	}
	
	/**
	 * (已兼容多类型导航)根据不同类别读取顶级导航函数，在下拉框更改的时候发送post请求读取。
	 * Author：赵臣升。
	 * CreateTime：2014/09/08 21:13:25.
	 * 最新备注：
	 * 目前只支持两级导航，如果要多级导航，不建议这么做，会相当复杂。
	 * 理由一：要对每个相同类型的导航进行判断，是否有信息，没有信息还可以挂接同类的；
	 * 理由二：要反复读取导航，虽然已经留有nav_level来标识导航的等级，可是出现4级、5级，代码一样要彻底重新写过；
	 * 理由三：容易出错，还要考虑兼容之前商家的导航，情形比较多，面对众多用户的情况下，贸然更改会导致数据库与前台出错；
	 * 理由四：后台页面的select选择框要改为easyUI的combotree了;
	 * 理由五：设计导航的时候是空当接龙方式挂接，如果层级过多，挂接会出错，而且情况会越来越复杂。
	 * 建议多级做法：三级以后不采用空当接龙方式挂接，直接建个小分类即可，二级删除，小分类也一起删除。
	 *
	 * LatestModify：2015/04/01 19:32:25.
	 * ModifyFor：兼容多种类型导航（信息、服装、餐饮、商品）。
	 */
	public function typeReadTopNav() {
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/addNavigation', '', '', true ) ); // 防止恶意进入
		$nav_type = I ( 'nt', 1 ); // 接收选择的导航参数
	
		// 初始化查询条件，注意这里$this->cc_fathernav_id => '-1'限制了导航挂接最多两级
		$navmap = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_fathernav_id => '-1', // 目前只支持在顶级导航下面挂接二级导航，不支持出现三级导航
				$this->cc_is_del => 0
		);
		// 针对超链接情况将导航类型分为两种
		if ($nav_type == 3) {
			$navmap [$this->cc_nav_type] = array ( 'neq', 3 ); // 如果是超链接导航，则不查询本身是超链接的顶级导航（也不可能被访问到）
		} else {
			$navmap [$this->cc_nav_type] = $nav_type; // 超链接导航可以任意选择信息、服装、餐饮和商品，无需区分
		}
		$navtable = M ( $this->table_name );
		$navresult = $navtable->where ( $navmap )->select (); // 查询可以挂接导航的潜在父级导航，找到的是一个二维数组
	
		$checknavcount = count ( $navresult ); // 可能可以挂接下级导航的待检验导航数量（循环遍历数）
		$finalnav = array (); // 最终检验出可以挂接导航的顶级导航
		for($i = 0; $i < $checknavcount; $i ++) {
			if ($this->topNavEmpty ( $navresult [$i] )) {
				array_push ( $finalnav, $navresult [$i] ); //如果顶级导航下没有信息，才可以添加二级导航，否则必须删除导航下的信息
			}
		}
		// 准备要向前台返回的信息
		$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => $finalnav
		);
		$this->ajaxReturn( $ajaxresult, 'json' );
	}
	
	/**
	 * (已兼容多类型导航)判断该导航下是否有信息的navEmpty函数、不检查二级导航是否存在。（重要函数，与navExistInfo形同意不同，有本质区别！）
	 * Author：赵臣升。
	 * CreateTime：2014/09/09 16:31:25.
	 * @param array $navinfo 传入导航的信息（一维数组）
	 * @property number nav_type 导航类型
	 * @return boolean $result 最终导航是否为空，若为true代表导航空、为false代表导航下有信息。
	 * LatestModify：2015/04/01 19:32:25.
	 * ModifyFor：兼容多种类型导航（信息、服装、餐饮、商品）。
	 */
	private function topNavEmpty($navinfo = NULL) {
		$isempty = false; // 默认导航不空、导航下有东西（防止挂错）
		if (! empty ( $navinfo )) {
			$childinfotable = ''; // 初始化要查询的（信息、商品、餐饮）表
			$childinfomap = array (
					$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家下的
					$this->cc_nav_id => $navinfo [$this->cc_nav_id], // 当前导航下的
					$this->cc_is_del => 0 // 没有被删除的
			);
			// 根据不同类别额外加码
			if ($navinfo [$this->cc_nav_type] == 1) {
				$childinfotable = 'simpleinfo'; // 信息类查询信息表
			} else if ($navinfo [$this->cc_nav_type] == 2) {
				$childinfotable = 'product'; // 服装类查询商品表
				$childinfomap ['product_type'] = 2; // 服装的商品类型为2
			} else if ($navinfo [$this->cc_nav_type] == 4) {
				$childinfotable = 'cate'; // 餐饮类查询餐饮cate表
			} else if ($navinfo [$this->cc_nav_type] == 5) {
				$childinfotable = 'product'; // 商品类查询商品表，但类型等于5
				$childinfomap ['product_type'] = 5; // 非服装的商品类型为5
			} else {
				return $isempty; // 防止多类别导航或不知类型导航，直接默认false
			}
			$haschildinfo = M ( $childinfotable )->where ( $childinfomap )->select ();
			if (! $haschildinfo) $isempty = true; // 没有内容的话，该顶级导航就是空的
		}
		return $isempty;
	}
	
	/**
	 * (已兼容多类型导航)导航的推荐信息（预判推荐功能）。
	 * 作用：在添加导航的时候，因为导航名称改变，所引起的下边上传图片尺寸推荐变动。
	 * 特别注意：不是查询父级导航的模板，而是查询自己导航的模板。例如：自己作为顶级导航，查主页的模板；自己作为二级导航，查二级模板。
	 * Author：赵臣升。
	 * CreateTime：2014/09/09 05:23:25.
	 * 设计思路：
	 * 新增导航有4种可能性：1、顶级信息导航；2、顶级商品导航；3、二级信息导航（2种模板）；4、二级商品导航（微商城、4种模板）
	 * 顶级信息导航、商品导航或超链接导航：只在选择了九宫格幻灯片版，需要上传图标；在选择了九宫格国际版，需要填写英文
	 * 二级信息导航条件：father_nav_id不是-1，且nav_type是1，有两种模板，分别上传图标和横版图
	 * 二级商品导航（微商城）条件：father_nav_id不是-1，且nav_type是2，有4种模板，上传的图标不一样。
	 * 二级超链接导航条件：father_nav_id不是-1，且nav_type是3，此时，根据父级导航的性质，决定其自身模板展现形式。
	 * 兼容G5G6这样的特殊多模板商家。
	 */
	public function navRecommend() {
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/addNavigation', '', '', true ) ); // 防止恶意进入
	
		$finalinfo = array (); //定义最终推送的模板信息
	
		//Step1：接收新增导航信息。
		$navInfo = array (
				$this->cc_fathernav_id => I ( 'fn', '-1' ), // 新增导航的父级导航id。若无父级导航值为-1，代表新增导航作为顶级导航。
				$this->cc_nav_type => I ( 'ct', 1 ) // 新增的导航自身所属类别。
		);
	
		//Step2：预判新增导航可能类型，做出相对应的温馨提示。先找到要提示的模板信息。
		$template_type = 1; // 初始化模板信息变量$template_type
		if ($navInfo [$this->cc_fathernav_id] == '-1') {
			// 处理新增导航为顶级导航情况（含信息类、服装类、餐饮类、超链接类、商品类）
			$template_type = 1; // 该种情况只要查询主页导航即可。
		} else {
			// 处理二级导航情况。
			$fathermap = array (
					$this->cc_nav_id => $navInfo [$this->cc_fathernav_id], // 二级导航的父级导航编号
					$this->cc_is_del => 0 // 没有被删除的
			);
			$fatherinfo = M ( $this->table_name )->where ( $fathermap )->find();	//先查一下父级节点的信息
				
			//特别注意：超链接导航类型，先将其nav_type转成父级导航的类型，因为他跟随父级导航类型来展现其自身（图标随父级节点展现）。
			if($navInfo [$this->cc_nav_type] == 3) {
				$navInfo [$this->cc_nav_type] = $fatherinfo [$this->cc_nav_type];			//处理二级超链接导航，改成父级导航的类型（子从父类）
			}
			if ($navInfo [$this->cc_nav_type] == 1){
				$template_type = 4; // 处理二级信息导航
			} else if ($navInfo [$this->cc_nav_type] == 2 || $navInfo [$this->cc_nav_type] == 5){
				$template_type = 2; // 处理二级服装、商品导航（微商城）
			} else {
				$template_type = 6; // 餐饮类的模板信息
			}
		}
	
		if (! empty ( $fatherinfo [$this->cc_display_tailor] ) && $template_type != 1){
			//处理像G5G6这样多二级（信息、商品)模板的商家
			//做一个视图拼接，带上where条件，查出要推送的信息。
			$sql = 'nav.display_tailor = per.tailor_id and per.template_id = tem.template_id and nav.is_del = 0 and per.is_del = 0 and tem.is_del = 0 and nav.e_id = \'' . $_SESSION ['curEnterprise'] ['e_id'] . '\'';
			$model = new Model();
			$finalinfo =  $model->table('t_navigation nav, t_personaltailor per, t_templatetailor tem')->where ( $sql )->field('tem.template_name, tem.nav_image_size, tem.remark ')->find();	//查出特殊模板信息
				
		} else if ($template_type != 6){
			//Step3：常规处理：根据$template_type得到要查找的模板信息。
				
			//Step3-1：找出所有微动该类导航的模板
			$tplmap = array (
					'template_type' => $template_type, // 当前模板类型
					'obsolete' => 0, // 没有过期的
					$this->cc_is_del => 0 // 没有被删除的
			);
			$templatetable = M ( 'templatetailor' );
			$tpresult = $templatetable->where ( $tplmap )->select ();			// 模板数据（也是最终模板数据$finalresult）
			$templatecount = count ( $tpresult ); // 统计该类模板微动提供的模板数量
				
			//Step3-2：找出商家在该类别模板自定义的记录$ptresult（如果没有该记录，使用默认该类的默认模板）
			$ptmap = array (
					$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家
					'nav_customized' => 0, // 不是特别定制模板
					'template_type' => $template_type, // 当前模板类型
					$this->cc_is_del => 0 // 没有被删除的
			);
			$tailortable = M ( 'personaltailor' );
			$ptresult = $tailortable->where ( $ptmap )->find ();				// 尝试找出商家定制的模板（也可能没有该记录、是null）
				
			//Step3-3：标记商家已选择的模板（在$tpresult里选择）
			if ($ptresult) {
				for($i = 0; $i < $templatecount; $i ++) {
					if ($tpresult [$i] ['template_id'] == $ptresult ['template_id']){
						$tpresult [$i] ['selected'] = 1;
					} else {
						$tpresult [$i] ['selected'] = 0;
					}
				}
			} else {
				for($i = 0; $i < $templatecount; $i ++){
					if ($tpresult [$i] ['default_selected'] == 1){
						$tpresult [$i] ['selected'] = 1;
					} else {
						$tpresult [$i] ['selected'] = 0;
					}
				}
			}
			// 检测下选了哪个模板
			for($j = 0; $j < $templatecount; $j ++) {
				if ($tpresult [$j] ['selected'] == 1) {
					$finalinfo = $tpresult [$j];
					break;
				}
			}
		}
		// 准备要返回给前台的信息
		$ajaxresult = array (
				'errCode' => 0,
				'errMsg' => "ok",
				'data' => $finalinfo
		);
		//Step4：推送温馨提示信息给前台。
		$this->ajaxReturn( $ajaxresult, 'json' );
	}
	
	/**
	 * (已兼容多类型导航)添加新导航确认函数，该函数处理addNavigation的表单提交。
	 * Author：赵臣升。
	 * CreateTime：2014/09/10 15:10:25.
	 *
	 * LatestModify：2015/04/01 22:00:25.
	 * ModifyFor：Optimized。
	 */
	public function addNavConfirm() {
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/addNavigation', '', '', true ) ); // 防止恶意进入
	
		// Step1：接收特殊参数
		$nav_url = I ( 'link_url' ); // 获取所添加导航的跳转链接（只有超链接导航才生效）
		$setmiddle = I ( 'set_middle', 0 ); // 尝试接收置中导航，默认0代表不置中
		$temporary_stop = I ( 'temporary_stop', 0 ); // 尝试接收临时停用导航，默认0代表不停用导航
	
		// Step2：接收提交过来的新导航数据
		$navinfo = array (
				$this->cc_nav_id => md5 ( uniqid ( rand (), true ) ),			// 新导航主键
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],			// 商家编号
				$this->cc_fathernav_id => I ( 'father_nav_selected', '-1' ),	// 获取所添加导航的父级导航id，无上级导航则默认是-1
				$this->cc_nav_name => I ( 'nav_name' ),							// 获取所添加导航的名字
				$this->cc_nav_english => I ( 'nav_english' ),					// 获取所添加导航的英文名
				$this->cc_nav_imagepath => I ( 'nav_image_path' ), 				// 导航图片
				$this->cc_nav_type => I ( 'nav_type_selected', 1 ),				// 获取所添加导航的类型，默认是1：信息类导航
				$this->cc_nav_order => I ( 'nav_order', 0 ),					// 获取所添加导航的编号
				$this->cc_description => I ( 'nav_description'),				// 获取所添加导航的描述（可空）
				$this->cc_add_time => time (), 									// 当前添加导航的时间
		);
		// 格式化一些数据
		if ($navinfo [$this->cc_fathernav_id] == "-1") {
			$navinfo [$this->cc_nav_level] = 1; // 如果上级导航是-1，代表nav_level是1
		} else {
			$navinfo [$this->cc_nav_level] = 2; // 如果上级导航是一个导航编号，代表nav_level是2
		}
		if ($navinfo [$this->cc_nav_type] == 3) $navinfo [$this->cc_nav_url] = $nav_url; // 超链接类型导航追加导航URL
		if ($setmiddle) {
			$navinfo [$this->cc_channel] = 1; // 4格顶级导航居中显示，转换checkbox数据展现形式
		} else {
			$navinfo [$this->cc_channel] = 0; // 4格顶级导航不居中显示，转换checkbox数据展现形式
		}
		if ($temporary_stop) {
			$navinfo [$this->cc_temporary_stop] = 1; // 转换checkbox数据展现形式，设置该导航是否临时停用
		} else {
			$navinfo [$this->cc_temporary_stop] = 0; // 转换checkbox数据展现形式，设置该导航正常开启
		}
	
		// Step3：新导航信息插入数据库
		$addresult = M ( $this->table_name )->add ( $navinfo );
		if ($addresult) {
			$this->redirect ( 'Admin/NavigationMenu/navigation' ); // 用户没有上传图片，添加完导航信息后直接跳转
		} else {
			$this->error ( "新增导航失败，网络繁忙，请稍后再试！" );
		}
	}
	
	/**
	 * (最新上传导航LOGO图标函数)线程托管方式上传nav导航图片并返回图片路径的函数。
	 */
	public function navImgUpload() {
		$saveFilePath = './Updata/images/' . $_SESSION ['curEnterprise'] ['e_id'] . '/navigation/';
		$common = A ( 'Admin/CommonHandle' );
		$uploadresult = $common->threadSingleUpload ( $saveFilePath );
		$this->ajaxReturn ( $uploadresult );
	}
	
	/**
	 * (已兼容多类型导航)判断某个导航下是否有子级菜单的函数。
	 * @param array $navinfo 导航信息数组，这里只需要一个字段：nav_id即传入要判断的导航id编号
	 * @return boolean true|false 返回true代表有子级菜单，返回false代表没有子级菜单
	 */
	private function navExistChild($navinfo = NULL){
		$navmap = array (
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家下
				$this->cc_fathernav_id => $navinfo [$this->cc_nav_id], // 当前导航下
				$this->cc_is_del => 0 // 没有被删除的
		);
		$childnavcount = M ( $this->table_name )->where ( $navmap )->count ();
		// 这里显示导航是否添加信息还要按类别去判断
		return $childnavcount ? true : false;	//尝试找出所有的子级菜单
	}
	
	/**
	 * (已兼容多类型导航)判断某个导航下是否有添加信息的函数。
	 * @param array $navinfo 传入要判断的导航信息（数组）
	 * @return boolean true|false 返回true代表导航下有信息或商品，返回false代表没有信息或商品
	 */
	private function navExistInfo($navinfo = NULL){
		$exist = false; // 默认某个导航下还没有存在任何信息
		if (! empty ( $navinfo )) {
			$childinfotable = ''; // 初始化要查询的（信息、商品、餐饮）表
			$childinfomap = array (
					$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'], // 当前商家下的
					$this->cc_nav_id => $navinfo [$this->cc_nav_id], // 当前导航下的
					$this->cc_is_del => 0 // 没有被删除的
			);
			// 根据不同类别额外加码
			if ($navinfo [$this->cc_nav_type] == 1) {
				$childinfotable = 'simpleinfo'; // 信息类查询信息表
			} else if ($navinfo [$this->cc_nav_type] == 2) {
				$childinfotable = 'product'; // 服装类查询商品表
				$childinfomap ['product_type'] = 2; // 服装的商品类型为2
			} else if ($navinfo [$this->cc_nav_type] == 4) {
				$childinfotable = 'cate'; // 餐饮类查询餐饮cate表
			} else if ($navinfo [$this->cc_nav_type] == 5) {
				$childinfotable = 'product'; // 商品类查询商品表，但类型等于5
				$childinfomap ['product_type'] = 5; // 非服装的商品类型为5
			} else {
				return $exist; // 防止多类别导航或不知类型导航，直接默认false
			}
			$haschildinfo = M ( $childinfotable )->where ( $childinfomap )->select ();
			if ($haschildinfo) $exist = true; // 如果有内容，就代表是存在的
		}
		return $exist;
	}
	
	/**
	 * (已兼容多类型导航)修改导航确认函数。
	 * Form表单提交至此。
	 * Author：赵臣升。
	 * CreateTime：2014/09/16 01:19:25.
	 *
	 * LatestModify：2015/04/01 22:00:25.
	 * ModifyFor：Optimized。
	 */
	public function editNavConfirm(){
		if (! IS_POST) _404 ( "sorry, page not exist!", U ( 'Admin/NavigationMenu/navigation', '', '', true ) ); // 防止恶意进入
	
		// Step1：接收特殊参数
		$nav_url = I ( 'link_url' ); // 获取所添加导航的跳转链接（只有超链接导航才生效）
		$setmiddle = I ( 'set_middle', 0 ); // 尝试接收置中导航，默认0代表不置中
		$temporary_stop = I ( 'temporary_stop', 0 ); // 尝试接收临时停用导航，默认0代表不停用导航
	
		// Step2：接收提交过来的新导航数据
		$editnavinfo = array (
				$this->cc_nav_id => I ( 'current_edit' ),						// 所编辑的导航主键
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],			// 商家编号
				$this->cc_fathernav_id => I ( 'current_father_nav', '-1' ),	// 获取所添加导航的父级导航id，无上级导航则默认是-1
				$this->cc_nav_name => I ( 'nav_name' ),							// 获取所添加导航的名字
				$this->cc_nav_english => I ( 'nav_english' ),					// 获取所添加导航的英文名
				$this->cc_nav_imagepath => I ( 'nav_image_path' ), 				// 导航图片
				$this->cc_nav_type => I ( 'nav_type_selected', 1 ),				// 获取所添加导航的类型，默认是1：信息类导航
				$this->cc_nav_order => I ( 'nav_order', 0 ),					// 获取所添加导航的编号
				$this->cc_description => I ( 'nav_description'),				// 获取所添加导航的描述（可空）
				$this->cc_latest_modify => time (), 							// 当前编辑导航的时间
		);
		// 格式化一些数据
		if ($editnavinfo [$this->cc_fathernav_id] == "-1") {
			$editnavinfo [$this->cc_nav_level] = 1; // 如果上级导航是-1，代表nav_level是1
		} else {
			$editnavinfo [$this->cc_nav_level] = 2; // 如果上级导航是一个导航编号，代表nav_level是2
		}
		if ($editnavinfo [$this->cc_nav_type] == 3) $editnavinfo [$this->cc_nav_url] = $nav_url; // 超链接类型导航追加导航URL
		if ($setmiddle) {
			$editnavinfo [$this->cc_channel] = 1; // 4格顶级导航居中显示，转换checkbox数据展现形式
		} else {
			$editnavinfo [$this->cc_channel] = 0; // 4格顶级导航不居中显示，转换checkbox数据展现形式
		}
		if ($temporary_stop) {
			$editnavinfo [$this->cc_temporary_stop] = 1; // 转换checkbox数据展现形式，设置该导航是否临时停用
		} else {
			$editnavinfo [$this->cc_temporary_stop] = 0; // 转换checkbox数据展现形式，设置该导航正常开启
		}
	
		$editresult = M ( $this->table_name )->save ( $editnavinfo );								// 带主键的save导航信息
		if ($editresult) {
			$this->redirect ( 'Admin/NavigationMenu/navigation' ); // 编辑完后直接跳转导航一览
		} else {
			$this->error ( "编辑导航信息提交失败，网络繁忙，请稍后再试！" );
		}
	}
	
	/**
	 * 二级导航模板设置确认函数。
	 * Function：从页面接收AJAX的POST传值，调用多态方法设置模板。
	 */
	public function setSecondNav(){
		$setinfo = array(
				'setpath' => strtolower ( GROUP_NAME . '_' . MODULE_NAME . '_' . ACTION_NAME ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				'template_id' => I ( 'selected' )
		);
		$tplManage = A ( 'Admin/TplManage' );
		$rs = $tplManage->EntrustTemplate ( $setinfo );				//调用设置模板的多态函数setTemplate并传入形参$setinfo
		if ($rs) {
			$ajaxresult = array (
					'errCode' => 0,
					'errMsg' => '提交更改成功!'
			);
		} else {
			$ajaxresult = array (
					'errCode' => 10002,
					'errMsg' => '模板更改失败!'
			);
		}
		$this->ajaxReturn ( $ajaxresult );
	}
	
	
}
?>