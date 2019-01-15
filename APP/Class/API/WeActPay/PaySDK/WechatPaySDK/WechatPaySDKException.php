<?php
/**
 * 微信支付运行错误记录。
 * @author Shinnlove
 *
 */
class SDKRuntimeException extends Exception {
	public function errorMessage() {
		return $this->getMessage ();
	}
}

?>