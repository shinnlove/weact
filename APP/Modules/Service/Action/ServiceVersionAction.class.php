<?php
/**
 * 服务版本控制器。
 * @author 赵臣升。
 *
 */
class ServiceVersionAction extends Action  {
	
	/**
	 * 移动端服务导航。
	 * @param string $e_id 商家编号
	 * @return array $serviceinfo 商家导航信息
	 */
	public function mobileServiceNav($e_id = '') {
		$serviceinfo = array (); // 商家导航信息
		$sql = 'sn.mobile_display = 1 and es.e_id = \'' . $e_id . '\' and sn.servicenav_id = es.servicenav_id and es.temporary_closed = 0 and sn.is_del = 0 and es.is_del = 0';
		$field = '*';
		$model = new Model ();
		$serviceinfo = $model->table ( 't_servicenav sn, t_enterpriseservice es' )->where ( $sql )->order ('sn.sort')->field ( $field )->select ();
		for ($i = 0; $i < count ( $serviceinfo ); $i ++) {
			$serviceinfo [$i] ['url'] = U ( $serviceinfo [$i] ['mobile_group'].'/'.$serviceinfo [$i] ['mobile_controller'].'/'.$serviceinfo [$i] ['mobile_action'], array('e_id' => $this->einfo ['e_id']), '', false, true );
		}
		return $serviceinfo;
	}
	
	/**
	 * 查询与组装商家nav的函数。
	 * @param string $e_id 商家编号
	 * @return array $serviceinfo 商家导航信息
	 */
	public function getServiceNav($e_id = '') {
		$serviceinfo = array (); // 商家导航信息
		$sql = 'sn.servicenav_id = es.servicenav_id and es.temporary_closed = 0 and sn.is_del = 0 and es.is_del = 0';
		$field = '*';
		$model = new Model ();
		$serviceinfo = $model->table ( 't_servicenav sn, t_enterpriseservice es' )->where ( $sql )->field ( $field )->select ();
		p($serviceinfo);die;
		return $serviceinfo;
	}
	
	/**
	 * 为商家添加服务导航。
	 */
	public function addServiceNav() {
		$servicemap = array (
				'is_del' => 0
		);
		$serviceinfo = M ( 'servicenav' )->where( $servicemap )->select();
		//p($serviceinfo);die;
		$singleservice = array (); // 单个服务
		for($i = 0; $i < count ( $serviceinfo ); $i ++) {
			$singleservice = array (
					'service_id' => md5 ( uniqid( rand (), true ) ),
					'e_id' => '201405291912250003',
					'servicenav_id' => $serviceinfo [$i] ['servicenav_id'],
					'start_date' => '2015-01-11',
					'end_date' => '2016-01-11'
			);
			$result = M ( 'enterpriseservice' )->add ( $singleservice );
		}
		p('ok');die;
	}
	
}
?>