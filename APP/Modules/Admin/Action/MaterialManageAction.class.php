<?php
/**
 * 此控制器处理微平台中的素材管理模块。
 * @author Administrator
 *
 */
class MaterialManageAction extends PCViewLoginAction {
	
	/**
	 * 定义本控制器常用表与一些常用的数据库字段名。
	 * 规则：cc代表current class当前类；好处是容易修改表字段 、容易查错；I函数接收的前台变量名就算同名也一律不改（否则会出错）。
	 * DefineTime:2014/09/30 20:45:25.
	 * @var string	variable	DBfield
	 */
	var $father_table = 'msgnews';
	var $child_table = 'msgnewsdetail';
	var $cc_m_id = 'msgnews_id';
	var $cc_e_id = 'e_id';
	var $cc_add = 'add_time';
	var $cc_latest = 'latest_modify';
	var $cc_category = 'msg_category';
	var $cc_md_id = 'msgnewsdetail_id';
	var $cc_title = 'title';
	var $cc_author = 'author';
	var $cc_cover = 'cover_image';
	var $cc_summary = 'summary';
	var $cc_content = 'main_content';
	var $cc_url = 'link_url'; // 图文详情地址
	var $cc_original = 'original_url'; // 原文链接
	var $cc_order = 'detail_order';
	var $cc_remark = 'remark';
	var $cc_is_del = 'is_del';
	
	/**
	 * 图文素材管理页面展示。
	 * Author：赵臣升。
	 */
	public function newsView(){
		//在此写入读数据库的代码，推送到前台显示...... 2014/07/02 20:21:25
		//缩写：msgnews→mn
		$mnmap = array(
				$this->cc_e_id => $_SESSION['curEnterprise']['e_id'],    //从session中读取当前商家e_id
				$this->cc_is_del => 0
		);
		$mntable = M($this->father_table);
		/*----------------↓以下为PHP导入分页控件代码，注意和前台的配合↓---------------*/
		//step1：导入控件，并确定一共几条数据、每页展示几条数据
		import('ORG.Util.Page');
		$count = $mntable->where($mnmap)->count();
		$page = new Page ( $count, 10);
		//step2：查询要显示的数据，并传送一些辅助数据到前台配合查询
		$mnresult = $mntable->where($mnmap)->order('add_time desc')->limit($page->firstRow.','.$page->listRows)->select();	//依据分页状态显示
		for($i = 0; $i < count( $mnresult ); $i ++){
			$mnresult [$i] ['add_time'] = timetodate($mnresult [$i] ['add_time']);
		}
		/** 此处循环查询数据库是因为图文页面数据格式需要，以后可以在内存中组装。 */
		$mndtable = M($this->child_table);
		for($i=0; $i<count($mnresult); $i++){
			$mndmap = array(
					$this->cc_m_id => $mnresult[$i][$this->cc_m_id],
					$this->cc_is_del => 0
			);
			$mndresult = $mndtable->where($mndmap)->order($this->cc_order)->select();	//子图文按detail_order序排列❤，重要!!!
			$mnresult[$i]['newsdetail'] = $mndresult;
		}
		$this->finalinfo = $mnresult;
		$this->finalcount = count($mnresult);
		//step3：设置分页控件的主题与推送分页控件到前台
		$page->setConfig('theme','%upPage% %nowPage%/%totalPage% 页 %downPage%');			//设置分页主题
		$this->page = $page->show();													//page控件作为变量向前台推送，注意和前台代码的配合
		/*----------------↑以上为PHP导入分页控件代码，注意和前台的配合↑---------------*/
	
		$this->display();
	}
	
	/**
	 * 单图文页面的展示。
	 */
	public function singleNews(){
		$this->display();
	}
	
	/**
	 * 编辑单图文页面的展示。
	 */
	public function editSingleNews(){
		$singlenewsmap = array (
				$this->cc_m_id => I ( 'mid' ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_is_del =>　0
		);
		$child_table = M ( $this->child_table );
		$singleresult = $child_table->where ( $singlenewsmap )->find ();
		$this->singlenewsinfo = $singleresult;
		$this->editcontent = json_encode ( $singleresult [$this->cc_content] );
		$this->display();
	}
	
	/**
	 * 多图文页面的展示。
	 */
	public function multipleNews(){
		$this->display();
	}
	
	/**
	 * 编辑多图文消息页面。
	 * Author：赵臣升。
	 * CreateTime：2014/09/28 16:22:25.
	 */
	public function editMultipleNews() {
		$mulnewsmap = array (
				$this->cc_m_id => I ( 'mid' ),
				$this->cc_e_id => $_SESSION ['curEnterprise'] ['e_id'],
				$this->cc_is_del =>　0
		);
		$child_table = M ( $this->child_table );
		$mulresult = $child_table->where ( $mulnewsmap )->order ( $this->cc_order )->select ();
		$this->mulnewsinfo = json_encode ( $mulresult );
		$this->display();
	}
	
}
?>