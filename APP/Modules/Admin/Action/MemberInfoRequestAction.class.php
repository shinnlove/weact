<?php
/**
 * 本控制器处理微SCRM版块下，会员类信息基本设置ajax请求。
 * 2014/06/21
 * @author：钟建友。
 */
class MemberInfoRequestAction extends PCRequestLoginAction {
	
	/**
	 * 提交选取卡片样式，修改会员卡的信息，卡片样式id和路径。
	 * OriginalAuthor：钟建友。
	 */
	public function cardSelectConfirm(){
		//Step1：接收提交的信息
		$selectinfo = array(
				'template_id' => I('finalselect'),
				'membercard_path' => I('finalpath')
		);
	
		//Step2：判断商家是否有设置过名片信息
		$mimap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$mitable = M('memberinfo');
		$miresult = $mitable->where($mimap)->find();	//查找商家有没有定义过名片样式
	
		//Step3：进行修改或新增处理。
		$mifinal = false;
		if($miresult){
			//如果商家已经定义过会员中心的信息（修改记录）
			$miresult ['template_id'] = $selectinfo ['template_id'];
			$miresult ['membercard_path'] = $selectinfo ['membercard_path'];
			$mifinal = $mitable->save($miresult);
		}else{
			//如果商家还没有定义过会员中心信息（生成一条记录）
			$selectinfo ['memberinfo_id'] = md5(uniqid(rand(), true));
			$selectinfo ['e_id'] = $_SESSION ['curEnterprise'] ['e_id'];
			$mifinal = $mitable->data($selectinfo)->add();
		}
	
		if($mifinal) {
			$this->ajaxReturn( array('status' => 1, 'msg' => '保存成功!') );
		} else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '保存失败，请不要重复提交!') );
		}
	}
	
	/**
	 * 自定义名片表单提交。
	 * OriginalAuthor：钟建友。
	 */
	public function addCardConfirm() {
		//Step1：判断商家是否有设置过名片信息
		$mimap = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$mitable = M('memberinfo');
		$miresult = $mitable->where($mimap)->find();	//查找商家有没有定义过名片样式
	
		//Step2：进行修改或新增名片处理
		$mifinal = false;
		if($miresult){
			//如果商家有定义过名片样式
			$miresult ['template_id'] = '-1';
			$miresult ['membercard_path'] = self::cardImageHandle();
			$mifinal = $mitable->save($miresult);
		}else{
			//如果商家没有定义过名片样式（需要生成一条记录）
			$cardinfo = array(
					'memberinfo_id' => md5(uniqid(rand(), true)),
					'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
					'template_id' => '-1',
					'membercard_path' => self::cardImageHandle()
			);
			$mifinal = $mitable->data($cardinfo)->add();
		}
	
		//Step3：做出调整后响应前台
		if($mifinal){
			$this->redirect('Admin/MemberInfo/memberCard');
		}else{
			$this->error('上传自定义名片错误!');
		}
	}
	
	/**
	 * 上传card名片并插入数据库的函数。
	 * @return null | array fileinfo	如果上传成功，返回$fileinfos的信息；如果失败，什么都不返回。
	 */
	private function cardImageHandle(){
		$savePath = 'Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/cardstyle/'; 	// 保存路径建议与主文件平级目录或者平级目录的子目录来保存
		$commonhandle = A ( 'Admin/CommonHandle' ); 										// 实例化公有控制器
		$fileinfos = $commonhandle->uploadImage ( $savePath ); 								// 调用上传的uploadImage函数，传入路径
		if ($fileinfos) {
			return '/' . $fileinfos [0] ['savepath'] . $fileinfos [0] ['savename'];			// 成功则返回路径+文件名，记得要带上'/'
		} else {
			return null;
		}
	}
	
	/**
	 * 该函数为会员等级页面easyUI请求数据的post。
	 */
	public function allMemberLevel(){
		if (! IS_POST) halt ( "Error, HTTP 404!",U('Admin/MemberInfo/memberLevel','','',true));
	
		//缩写：memberlevel→ml
		$mlmap = array(
				'e_id' => $_SESSION['curEnterprise']['e_id'],	//取当前商家编号
				'is_del' => 0
		);
	
		$mltable = M('memberlevel');							//查询会员等级表
	
		$pagenum = isset($_POST ['page']) ? intval($_POST['page']) : 1;
		$rowsnum = isset($_POST ['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'level';		//按等级高低排序
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';		//顺序排序
	
		$total = $mltable->where($mlmap)->count();				//计算该商家的所有已设置的会员等级数目
		$mllist = array();
		if($total){
			$mllist = $mltable->where ( $mlmap )->limit ( ($pagenum - 1) * $rowsnum . ',' . $rowsnum )->order ( ''.$sort.' '.$order )->select ();
		}
	
		$json = '{"total":' . $total . ',"rows":' . json_encode ( $mllist ) . '}'; // 重要，easyui的标准数据格式，数据总数和数据内容在同一个json中
		echo $json;
	}
	
	/**
	 * 响应前台添加/编辑会员等级确认post请求的函数。
	 */
	public function setLevelConfirm(){
		$mlresult = false;					//先置否
		$mltable = M('memberlevel');
		$mlinfo = array(
				'member_level_id' => md5 (uniqid (rand(), true)),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'level' => I('lv', 0),
				'level_name' => I('ln'),
				'description' => I('bd'),
				'min_consumption' => I('mc'),
				//'discount' => I('dis')		//很遗憾由于时间有限，微动1.6暂时放弃了该功能
		);
		if(I('ef') == 1) {
			$mlinfo ['member_level_id'] = I('mp');
			$mlresult = $mltable->save($mlinfo);
		}else{
			$mlresult = $mltable->data($mlinfo)->add();
		}
		if($mlresult){
			$this->ajaxReturn( array( 'status' => 1, msg => 'ok') );
		}else{
			$this->ajaxReturn( array( 'status' => 0, msg => '请稍后再试!') );
		}
	}
	
	/**
	 * 响应前台删除会员等级确认post请求的函数。
	 */
	public function delLevelConfirm(){
		$mltable = M('memberlevel');
		$mlmap = array(
				'member_level_id' => I('mp', '-1'),
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$delresult = $mltable->where($mlmap)->setField('is_del', 1);
		if($delresult){
			$this->ajaxReturn( array( 'status' => 1, msg => 'ok') );
		}else{
			$this->ajaxReturn( array( 'status' => 0, msg => '请稍后再试!') );
		}
	}
	
	/**
	 * 用户信息模块的图片上传。
	 * 使用ueditor富文本编辑器上传memberinfo图片的函数。
	 * Author：赵臣升。
	 * CreateTime：15:35:25.
	 */
	public function infoImageHandle(){
		$savePath = './Updata/images/' . $_SESSION['curEnterprise']['e_id'] . '/memberCenter/'; // 保存路径建议与主文件平级目录或者平级目录的子目录来保存（特别注意：这个./不能漏掉，否则图片无法完成上传。）
		$commonhandle = A ( 'Admin/CommonHandle' ); 											// 实例化公有控制器
		$commonhandle->ueditorUploadImage ( $savePath ); 										// 调用上传的ueditorUploadImage函数，传入路径，会输出json信息给ueditor
	}
	
	/**
	 * 用户提交特权内容post处理函数。
	 */
	public function privilegeConfirm(){
		$info = unescape($_POST['info']);									//接收信息，I()里面用name值传递，接收的数据经过escape函数处理，取值时要加符号&，再用unescape还原数据
		$memberinfo = array(
				'e_id' => $_SESSION ['curEnterprise'] ['e_id'],
				'is_del' => 0
		);
		$mitable = M('memberinfo');
		$miresult = $mitable->where($memberinfo)->find();
	
		if($miresult){
			$miresult ['member_right'] = $info;
			$setresult = $mitable->save($miresult);							//如果原来已经有编辑过信息，直接保存信息（查询的数据有主键）
		}else{
			$memberinfo ['memberinfo_id'] = md5(uniqid(rand(), true));		//产生主键
			$memberinfo ['member_right'] = $info;
			$setresult = $mitable->data($memberinfo)->add();
		}
		if($setresult){
			$this->ajaxReturn( array('status' => 1, 'msg' => '保存成功!') );
		}else{
			$this->ajaxReturn( array('status' => 0, 'msg' => '请不要重复提交相同信息或非法信息!') );
		}
	}
	
}
?>