<?php
import ( 'MobileGuestAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileGuestAction
import ( 'MobileLoginAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileLoginAction
/**
 * 本控制器是餐饮列表。
 * 本控制器暂时需要授权登录openid支持。
 * @author 赵臣升。
 * CreateTime:2014/12/03 12:25:36.
 * 载入Home分组下的GuestCommon控制器继承后就有授权登录和企业信息了。
 */
class MenuViewAction extends MobileLoginAction {
	/**
	 * 餐饮菜单陈列模板视图1。
	 */
	public function menu() {
		$scate = A ('Service/Cate');
		$tid = I ( 'tid' );													//尝试接收桌子的table编号
		if (! empty ( $tid )) {
			$scate->checkScanTable( $this->einfo ['e_id'], $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'], $tid );
			$_SESSION ['table_id'] = $tid;									//如果餐桌编号不空（扫桌子二维码进入点餐），则把table_id存入session中
		}
		
		$this->nav_id = I ( 'nav_id' );										//接收当前菜单名称并推送到前台（post传回来查询）
		//获得菜单列表
		$navmap = array(
				'e_id' => $this->einfo ['e_id'],
				'father_nav_id' => $this->nav_id,
				'is_del' => 0
		);
		$this->navlist = M( 'navigation' )->where($navmap)->select();		//查询出当前菜单下的所有分类
		$navself = array(
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => $this->nav_id,
				'is_del' => 0
		);
		$this->navinfo = M( 'navigation' )->where($navself)->find();
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];			//从session中尝试读取用户openid
		$this->ismember = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['ismember'];		//从session中读取是否会员（前台按此变量显示价格）
		$cartinfo = $scate->getCartView( $this->einfo ['e_id'], $this->openid );
		$this->ordernum = count( $cartinfo );								//计算用户点菜的菜品数量
		$this->display ();
	}
	
	/**
	 * 餐饮菜单陈列视图。
	 */
	public function menu2() {
		$this->display ();
	}
	
	/**
	 * 获取一个餐饮类别里的所有餐饮内容。
	 */
	public function getCategory() {
		$scate = A ('Service/Cate');
		$categorymap = array(
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => I ( 'categoryId' ),
				'openid' => I ( 'openid' ),
				'is_del' => 0
		);
		$catelist = $scate->getCateListByNavId( $categorymap ['nav_id'], true );		// 获取餐饮列表（带组装路径）
		if($catelist) {
			for($i = 0; $i < count( $catelist ); $i ++) {
				$catelist [$i] ['order_number'] = 0;														//初始化已点餐数据为0
			}
			$cartlist = $scate->getCartView( $this->einfo ['e_id'], $categorymap ['openid'], true );			//查询出用户已经点的数量
			//依据我的餐车内已点单数量，对菜单列表中的产品进行数量叠加，特别注意：外层循环用cartlist时间复杂度比较低!
			for($j=0; $j<count($cartlist); $j++) {
				//外层循环要把餐车中每一条菜品的数量$cartlist [$j] ['amount']给到菜单列表的order_number字段；内层循环是要找出目标菜单列表
				for($k=0; $k<count($catelist); $k++) {
					if($cartlist [$j] ['cate_id'] == $catelist [$k] ['cate_id']) {
						$catelist [$k] ['order_number'] = $cartlist [$j] ['amount'];						//依据客户餐车更新菜单列表写入已点数量
						break;
					}
				}
			}
			import( 'Class.BusinessLogic.Cate.DataFormat', APP_PATH, '.php' );								//载入业务逻辑格式化数据
			$df = new DataFormat();
			$catefinal = $df->dataFormat($catelist);														//再格式化数据
			$this->ajaxReturn( array( 'categoryId' => $categorymap ['nav_id'], 'data' => $catefinal ) );
		}else {
			$this->ajaxReturn( array( 'categoryId' => $categorymap ['nav_id'], 'data' => array() ) );		//没有查询到就返回空数组
		}
	}
	
	/**
	 * 获得用户点菜的数目（索引匹配，时间复杂度较低）。
	 * CreateTime:2014/12/07 20:06:25.
	 */
	public function getCategoryOrder() {
		$scate = A ('Service/Cate');
		$requestinfo = array(
				'e_id' => $this->einfo ['e_id'],
				'nav_id' => I ( 'nav_id' ),
				'openid' => I ( 'openid' )
		);
		//获得当前栏目下的所有菜单列表
		$navmap = array(
				'e_id' => $this->einfo ['e_id'],
				'father_nav_id' => $requestinfo ['nav_id'],
				'is_del' => 0
		);
		$navlist = M( 'navigation' )->where($navmap)->select();			//查询出当前菜单下的所有分类（与用户所点单进行比较）
		//获取当前商家的所有菜品
		$catemap = array(
				'e_id' => $this->einfo ['e_id'],
				'is_del' => 0
		);
		$catelist = M( 'cate' )->where($catemap)->select();
		$formatcatelist = array();												//菜品索引数组
		for($i=0; $i<count($catelist); $i++){
			$formatcatelist [ $catelist [$i] ['cate_id'] ] = $catelist [$i];	//格式化数据方便索引
		}
		//格式化数据$numarray初始化
		$numarray = array();
		for($i=0; $i<count($navlist); $i++){
			$numarray [ $navlist [$i] ['nav_id'] ] = 0;				//先初始化置为0
		}
		//获得当前用户的餐车
		$cartlist = $scate->getCartView( $this->einfo ['e_id'], $requestinfo ['openid'] );
		//如果有点餐，更新格式化数据$numarray
		if($cartlist) {
			for($i=0; $i<count($cartlist); $i++){
				$numarray [ $formatcatelist [ $cartlist [$i] ['cate_id'] ] ['nav_id'] ] ++; 
			}
		}
		//返回给前台点餐的值
		$this->ajaxReturn( array( 'orderNumber' => $numarray ) );
	}
	
	/**
	 * 更改（增加/减少）菜品数量的post处理函数。
	 * 特别注意：如果是用户增加第一道菜，是添加记录；往后加菜/减菜则是进行更新记录。
	 */
	public function updateCateNumber(){
		//执行更新我的菜单某菜品数量操作后返回code等于0代表成功
		//Step1：接收要更新的信息
		$ajaxinfo = array(
				'e_id' => $this->einfo ['e_id'],
				'cate_id' => I ( 'dishid' ),
				'is_del' => 0
		);
		$numinfo = I ( 'o2uNum' );				//接收菜品要更新的信息
		//Step2：查询下当前要更新的菜品完整信息
		$cateinfo = M( 'cate' )->where( $ajaxinfo )->find();
		//Step3：查询用户的餐车情况，判断当前信息是否为第一道菜，增加一个用户微信编号信息查询餐车
		$ajaxinfo ['openid'] = I ( 'openid' );								//接收顾客的openid
		$catecartinfo = M( 'catecart' )->where( $ajaxinfo )->find();		//尝试查找当前顾客餐车中要更新的某一道菜品记录（一维数组）
		$result = false;													//执行数据库更新顾客餐车成功标记，先默认为失败
		if($catecartinfo) {
			//如果是已经有添加过的记录
			$catecartinfo ['amount'] = $numinfo;							//更新当前数量
			$catecartinfo ['latest_modify'] = time();						//更新最后一次修改时间
			$result = M( 'catecart' )->save($catecartinfo);					//将点菜的数量更新回数据库
		} else {
			//如果是点第一道菜
			$newcateorder = array(
					'cart_id' => md5( uniqid(rand(), true) ),
					'e_id' => $this->einfo ['e_id'],
					'openid' => $ajaxinfo ['openid'],
					'cate_id' => $ajaxinfo ['cate_id'],
					'amount' => 1,											//点的第一道菜
					'add_time' => time()
			);
			$result = M( 'catecart' )->add($newcateorder);					//将第一道点菜插入数据库
		}
		//Step4：执行相应的数据库操作（新增/更新）
		if($result) {
			$this->ajaxReturn( array( 'code' => 0 ) );						//成功向前台返回code=0
		} else {
			$this->ajaxReturn( array( 'code' => 1 ) );						//失败code=1
		}
	}
	
	/**
	 * （从我的菜单中）移除菜品数量的post处理函数。
	 */
	public function removeCateOrder() {
		//执行移除我的菜单上某菜品操作后返回code等于0代表成功
		$ajaxinfo = array(
				'e_id' => $this->einfo ['e_id'],
				'openid' => I ( 'openid' ),
				'cate_id' => I ( 'dishid' ),
				'is_del' => 0
		);
		$catecartinfo = M( 'catecart' )->where( $ajaxinfo )->find();		//用户餐车中某道菜的记录（一维数组）
		$failflag = 1;														//默认移除标记是失败的
		if($catecartinfo) {
			if($catecartinfo ['amount'] == 1) {
				$delmap = array(
						'cate_id' => $catecartinfo ['cate_id'],
						'is_del' => 0
				);
				$removeresult = M( 'catecart' )->where($delmap)->delete();
				if($removeresult) $failflag = 0;							//这样失败标记才为0，代表成功
			}
		}
		$this->ajaxReturn( array( 'code' => $failflag ) );
	}
}
?>