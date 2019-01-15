<?php
class Subscribe {
	public function judgeMember($wechaterinfo = NULL) {
		return $wechaterinfo ['subscribe'] == 1 ? true : false;
	}
}
?>