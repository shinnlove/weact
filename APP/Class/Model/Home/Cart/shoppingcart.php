<?php
/**
 * 购物车类，封装一些购物车的常用操作，辅助购物车控制器和订单控制器的一些操作。
 * @author 王健，赵臣升。
 * CreateTime:2015/01/25 10:25:36.
 */
class ShoppingCart {
	
	/**
	 * 商家编号
	 * @var string $e_id
	 */
	private $e_id = '';
	
	/**
	 * 顾客编号
	 * @var string $customer_id
	 */
	private $customer_id = '';
	
	/**
	 * 顾客微信号，辅助变量
	 * @var string $openid
	 */
	private $openid = '';
	
	/**
	 * 购物车商品列表。
	 * @var array $cartproductlist
	 */
	private $cartproductlist = array ();
	
	/**
	 * 购物车构造函数，传入商家编号和顾客编号，生成某商家下某个顾客的购物车。
	 * 三个参数代表最后一个参数以openid代替顾客编号，当然先要查询customerinfo表转换成customer_id
	 * @param string $e_id 商家编号
	 * @param string $customer_id 顾客编号
	 * @param string $openid 顾客微信号
	 */
	function __construct($e_id = '', $customer_id = '', $openid = '') {
		$this->e_id = $e_id;
		$this->customer_id = $customer_id;
		if (! empty ( $openid )) $this->openidToCustomerId ( $e_id, $openid ); // 将openid转换成customer_id
	}
	
	/**
	 * 对内接口区域。
	 */
	
	/**
	 * 通过顾客的openid转换实现查找顾客customer_id
	 * @param string $e_id 商家编号
	 * @param string $openid 顾客微信号
	 */
	private function openidToCustomerId($e_id = '', $openid = '') {
		if (! empty ( $e_id ) && ! empty ( $openid )) {
			$switchmap = array (
					'e_id' => $e_id,
					'openid' => $openid,
					'is_del' => 0
			);
			$customerinfo = M ( 'customerinfo' )->where ( $switchmap )->find ();
			if ($customerinfo && ! empty ( $customerinfo ['openid'] )) {
				$this->customer_id = $customerinfo ['customer_id'];
			}
		}
	}
	
	/**
	 * 初始化购物车商品。
	 */
	private function initCartData() {
		$cartmap = array (
				'e_id' => $this->e_id,
				'customer_id' => $this->customer_id,
				'is_del' => 0
		);
		$cartproductlist = M ( 'cart_product_image' )->where ( $cartmap )->select (); // 初始化购物车
		for ($i = 0; $i < count ( $cartproductlist ); $i ++) {
			$cartproductlist [$i] ['macro_path'] = assemblepath ( $cartproductlist [$i] ['macro_path'] ); // 组装图片路径，2015/02/21 22:10:25
			$cartproductlist [$i] ['micro_path'] = assemblepath ( $cartproductlist [$i] ['micro_path'] ); // 组装图片路径，2015/02/21 22:10:25
		}
		$this->cartproductlist = $cartproductlist;
	}
	
	/**
	 * 对购物车的商品进行哈希索引包装。
	 * @return array $cartinfo 包装过的购物车信息
	 */
	private function checkWrapCartInfo() {
		$cartinfo = array ();
		for($i = 0; $i < count ( $this->cartproductlist ); $i ++) {
			$cartinfo [$this->cartproductlist [$i] ['cart_id']] = $this->cartproductlist [$i];
		}
		return $cartinfo;
	}
	
	/**
	 * 对外接口区域。
	 */
	
	/**
	 * 购物车视图展示使用：显示购物车的商品列表，先查询，然后统计价格。
	 * @return array $cartinfo 返回购物车信息
	 * @property array $this->cartproductlist 商品列表信息
	 * @property number $pricesum 商品总价格
	 */
	public function getCartData() {
		$this->initCartData (); // 先将购物车信息查询出来了
		$pricesum = 0; // 购物车商品总价s
		if (! empty ( $this->cartproductlist )) {
			for ($i = 0; $i < count ( $this->cartproductlist ); $i ++) {
				$pricesum += $this->cartproductlist [$i] ['amount'] * $this->cartproductlist [$i] ['current_price'];
				$this->cartproductlist [$i] ['availableamount'] = $this->cartproductlist [$i] ['storage_amount'] - $this->cartproductlist [$i] ['sell_amount'];
			}
		}
		$cartinfo = array (
				'productlist' => $this->cartproductlist,
				'pricesum' => $pricesum
		); // 购物车信息
		return $cartinfo;
	}
	
	/**
	 * 更新购物车商品数量。
	 * @param string $cart_id 要更新商品所在的购物车编号
	 * @param number $updatenumber 要更新的数量，可正可负
	 * @return array $updateinfo 更新结果
	 * @property boolean result 更新成功与否
	 * @property number availableamount 该规格/尺寸和颜色商品总得剩余数量
	 * @property number currentamount 更新后该商品所在购物车的数量
	 */
	public function updateCartAmount($cart_id = '', $updatenumber = 0) {
		$result = false; // 默认数量没有更新成功
		$availableamount = 0; // 默认规格/尺寸和颜色商品总得剩余数量为0
		$cartmap = array (
				'cart_id' => $cart_id,
				'e_id' => $this->e_id,
				'is_del' => 0
		);
		$cartproductinfo = M ( 'cart_product_image' )->where ( $cartmap )->find (); // 查找要更新的信息
		// 超过数量欢迎往下减少，增加的话必须满足增加后不超过库存量才能执行，所以这里逻辑是或
		if ($updatenumber < 0 || $cartproductinfo ['amount'] + $updatenumber <= $cartproductinfo ['storage_amount'] - $cartproductinfo ['sell_amount']) {
			$cartproductinfo ['amount'] = $cartproductinfo ['amount'] + $updatenumber; // 允许变更数量
			$result = M ( 'cart' )->save ( $cartproductinfo );
		}
		$updateinfo = array (
				'result' => $result,
				'availableamount' => $cartproductinfo ['storage_amount'] - $cartproductinfo ['sell_amount'],
				'currentamount' => $cartproductinfo ['amount']
		);
		return $updateinfo;
	}
	
	/**
	 * 删除购物车某件商品。
	 * @param string $cart_id 要删除的商品所在购物车编号
	 * @return boolean $handleSucceed 操作成功与否的标记
	 */
	public function deleteCartProduct($cart_id = '') {
		$handleSucceed = false; // 默认没有操作成功
		if (! empty ( $cart_id )) {
			$carttable = M ( 'cart' );
			$delmap = array (
					'cart_id' => $cart_id,
					'e_id' => $this->e_id,
					'is_del' => 0
			);
			$exist = $carttable->where ( $delmap )->count ();
			if ($exist > 0) {
				$delresult = $carttable->where ( $delmap )->delete ();
				if($delresult) $handleSucceed = true;
			}
		}
		return $handleSucceed;
	}
	
	/**
	 * 对外接口：对某件商品进行下架检查，建议在最终提交前进行检查。
	 * @param string $product_id 商品编号
	 * @return boolean $offshelf 商品是否下架，已经下架返回true，没有下架返回false
	 */
	public function IsOffShelf($product_id = '') {
		$isoffshelf = false; // 默认商品没有下架
		if (! empty ( $product_id )) {
			$promap = array (
					'product_id' => $product_id,
					'is_del' => 0
			);
			$productinfo = M ( 'product' )->where ( $promap )->find (); // 找到商品信息
			if ($productinfo && $productinfo ['off_shelf'] == 1) {
				$isoffshelf = true; // 商品确实下架了
			}
		}
		return $isoffshelf;
	}
	
	/**
	 * 对外接口：检查所选商品当前库存函数。
	 * @param string $productinfo 要检查的商品信息
	 * @property string product_id 商品编号
	 * @property number product_type 商品类型（2是服装，5是非服装）
	 * @property string product_size 商品尺寸/规格
	 * @property string product_color 商品颜色
	 * @return number $storageamount 返回要检查的商品库存剩余数量
	 */
	public function checkStorage($productinfo = NULL) {
		$storageamount = 0; // 商品库存数量
		if (! empty ( $productinfo )) {
			$querymap = array (
					'product_id' => $productinfo ['product_id'],
					'product_size' => $productinfo ['product_size'],
					'is_del' => 0,
			);
			if (intval ( $productinfo ['product_type'] ) == 2) {
				$querymap ['product_color'] = $productinfo ['product_color'];
			}
			$sizecolorinfo = M ( 'productsizecolor' )->where ( $querymap )->find (); // 查询商品的尺寸/规格、颜色库存信息
			if ($sizecolorinfo) $storageamount = $sizecolorinfo ['storage_amount'] - $sizecolorinfo ['sell_amount']; // 统计该规格/尺寸、颜色商品的剩余数量
		}
		return $storageamount;
	}
	
	/**
	 * 预订单视图的购物车预结算商品列表（可能是购物车全部商品，也可能是购物车部分商品，支持部分提交结算）
	 * @param array $cartidlist 提交成预订单的部分购物车cart_id
	 * @return array $prepaycartlist 预订单将要支付的购物车信息
	 */
	public function preOrderCartList($cartidlist = NULL) {
		$prepaycartlist = array (); // 预订单将要支付的购物车信息
		$this->initCartData (); // 先将购物车信息查询出来给到类中变量
		$cartinfo = $this->checkWrapCartInfo(); // 对购物车进行哈希所以包装，时间复杂度n
		if (! empty ( $cartidlist )) {
			// 将$cartinfo的信息直接加入到$prepaycartlist中，特别注意$cartidlist [$i]存的是一条cart_id
			for ($i = 0; $i < count ( $cartidlist ); $i ++) {
				$prepaycartlist [$i] = $cartinfo [$cartidlist [$i]];
			}
		}
		return $prepaycartlist;
	}
	
	/**
	 * 购物车提交成预订单，预订单提交成订单进行的商品下架与否和库存数量检测。
	 * @param array $cartidlist 要检测的购物车编号（购物车商品部分提交/全部提交）
	 * @return array $checkresult 检测信息
	 * @property string errCode 错误码（错误码0代表检测通过）
	 * @property string errMsg 错误信息（错误信息ok代表检测通过，跟错误码为0是一起的）
	 */
	public function cartSubmitCheck($cartidlist = NULL) {
		$this->initCartData (); // 先将购物车信息查询出来给到类中变量
		$cartinfo = $this->checkWrapCartInfo(); // 对购物车进行哈希所以包装，时间复杂度n
		$errCode = 10000; // 默认检测未通过
		$errMsg = "网络繁忙"; // 默认错误信息：网络繁忙
		$errorexist = false; // 存在错误默认否（一切正常），检测的时候发现一处错误，就是errorexist=true了
		if (! empty ( $cartidlist )) {
			// 因为checkWrapCartInfo函数对购物车打包包装过了，所以只需一次循环了。传入的idlist小于等于购物车list，$cartidlist [$i]存的就是一条购物车主键。
			for ($i = 0; $i < count ( $cartidlist ); $i ++) {
				if (empty ( $cartinfo [$cartidlist [$i]] )) {
					$errorexist = true; // 如果要检测的购物车主键索引不到购物车信息，说明购物车信息被更改了，有错，做网络繁忙返回处理
					break; // 出错立刻停止检测
				} else {
					// 如果主键索引到了，代表存在这样的购物车信息，检测商品是否下架或库存是否不满足要求
					if ($cartinfo [$cartidlist [$i]] ['off_shelf'] == 1) {
						$errCode = 10001;
						$errMsg = "有商品已下架，无法提交订单，请刷新后重新操作！";
						$errorexist = true;
						break; // 出错立刻停止检测
					} else if ($cartinfo [$cartidlist [$i]] ['amount'] > $cartinfo [$cartidlist [$i]] ['storage_amount'] - $cartinfo [$cartidlist [$i]] ['sell_amount']) {
						$errCode = 10002;
						$errMsg = "购物车中有商品数超过当前库存数，无法提交订单，请刷新后重新操作！";
						$errorexist = true;
						break; // 出错立刻停止检测
					}
				}
			}
			if (! $errorexist) {
				// 循环检测停止后，如果不存在问题，则直接更新错误信息为正确
				$errCode = 0; // 检测已通过
				$errMsg = "ok"; // 没有错误消息
			}
		}
		$checkresult = array (
				'errCode' => $errCode,
				'errMsg' => $errMsg
		); // 定义最终结果
		return $checkresult;
	}
}
?>