<?php
/**
 * 本控制器主要实现温馨提示。
 * bindEID是访问平台的时候没有绑定e_id跳转空白页面的情况。
 * @author Administrator
 */
class TipAction extends Action {
	public function bindEID() {
		$this->display ();
	}

    public function wash(){

        $cate_images = M('cateimage')->select();

        $num = count($cate_images);
        for ($i = 0; $i<$num; $i++) {
            $cate_image_id = $cate_images[$i]['cate_image_id'];
            $macro_path = $cate_images[$i]['macro_path'];
            $micro_path = $cate_images[$i]['micro_path'];

            $update_con = array(
                'cate_image_id' => $cate_image_id
            );

            if ($macro_path!="") {
                $value = substr($macro_path, 7);
                $corrent_value = "/Updata" . $value;

                $corrent_save = array(
                    'macro_path' => $corrent_value
                );

                M('cateimage')->where($update_con)->save($corrent_save);
            }

            if ($micro_path!="") {
                $value = substr($micro_path, 7);
                $corrent_value = "/Updata" . $value;

                $corrent_save = array(
                    'micro_path' => $corrent_value
                );

                M('cateimage')->where($update_con)->save($corrent_save);
            }
        }

        die;
    }
}
?>