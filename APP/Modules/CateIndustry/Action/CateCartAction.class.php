<?php
import ( 'MobileGuestAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileGuestAction
import ( 'MobileLoginAction', APP_PATH . C ( 'HOME_ACTION_PATH' ) ); // 载入Home分组下控制器MobileLoginAction
/**
 * 本控制器是我的菜单（我的购物车）。
 * @author 赵臣升
 * CreateTime:2014/12/03 12:25:36.
 * 载入Home分组下的GuestCommon控制器继承后就有授权登录和企业信息了。
 */
class CateCartAction extends MobileLoginAction {
	/**
	 * 我的餐车模板1视图。
	 */
	public function myCateCart() {
		$scate = A ('Service/Cate');
		$this->openid = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['openid'];		//从session中尝试读取用户openid
		$this->ismember = $_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['ismember'];	//从session中读取是否会员（前台按此变量显示价格）
		$this->subscribepage = 'http://mp.weixin.qq.com/s?__biz=MzAwNjEyNDQ3Ng==&mid=202831051&idx=1&sn=dab449e92397cbf3b1b11915af51fbeb#rd';	//该公众账号的关注页面，应该由查询数据库得到，现在暂时先使用测试常量
		$cartlist = $scate->getCartView( $this->einfo ['e_id'], $this->openid, true );			// 获取用户餐车中信息
		if($_SESSION ['currentwechater'] [$this->einfo ['e_id']] ['ismember']) {
			for($i = 0; $i < count($cartlist); $i ++) {
				$cartlist [$i] ['price'] = $cartlist [$i] ['member_price'];		//如果当前用户是会员，则把会员价格member_price给到price
			}
		}
		$this->cartlist = $cartlist;
		$this->cateshopurl = $scate->cateShop( $this->einfo ['e_id'] ); // 推送餐饮微商城主页，供跳转
		$this->display();
	}
	
	/**
	 * 我的餐车（菜单）视图。
	 */
	public function myCateCart2() {
		$this->display();
	}
	
	/**
	 * 清空用户餐车post处理函数。
	 */
	public function clearCateCart() {
		$scate = A ('Service/Cate');
		$clearresult = $scate->clearCateCart( $this->einfo ['e_id'], I ( 'openid' ), true );
		if($clearresult) {
			$this->ajaxReturn( array( 'status' => 1, 'errCode' => 0, 'errMsg' => 'ok' ) );
		} else {
			$this->ajaxReturn( array( 'status' => 0, 'errCode' => 10000, 'errMsg' => '网络繁忙，请稍后再试!' ) );
		}
	}
}
?>