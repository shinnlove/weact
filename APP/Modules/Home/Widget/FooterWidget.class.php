<?php
class FooterWidget extends Widget {
	public function render ($data) {
		return $this->renderFile('traditionalFooter', $data);
	}
}
?>