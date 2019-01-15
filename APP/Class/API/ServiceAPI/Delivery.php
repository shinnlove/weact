<?php
/**
 * 快递接口类。
 * @author 万路康。
 * CreateTime：2014/11/06 00:59:25.
 * API地址：http://www.ickd.cn/api/doc.html
 */
class Delivery {
	var $id = "108386";
	var $secret = "e5f4bb052cc515e85f217f7fc9d7d580";
	//var $id = '107033'; // 快递查询授权KEY
	//var $secret = '72cdef4998ec5b4cd67db94b0fdf962a'; // 快递查询授权secret
	var $type = 'json'; // 返回值类型：html|json(默认)|text|xml
	var $encode = 'utf8'; // 编码方式：gbk(默认)|utf8，PHP的json_decode必须使用utf8（官网备注）
	
	/**
	 * 快递查询函数。
	 * @param string $com        	
	 * @param string $nu        	
	 */
	public function queryDelivery($com = '', $nu = '') {
		$url = 'http://api.ickd.cn/';
		$params = array (
				'id' => $this->id,
				'secret' => $this->secret,
				'com' => $com,
				'nu' => $nu,
				'type' => $this->type,
				'encode' => $this->encode 
		);
		$jsonExpressInfo = json_decode ( http ( $url, $params ), true );
		
		$final = '抱歉，没有查询到该快递的相关信息。';
		if ($jsonExpressInfo ['status']) {
			$final = $jsonExpressInfo ['expTextName'] . "\n" . $jsonExpressInfo ['mailNo']; // 获得快递信息中的快递名和快递编号
			for($i = 0; $i < count ( $jsonExpressInfo ['data'] ) - 1; $i ++) {
				$final .= "\n" . $jsonExpressInfo ['data'] [$i] ['time'] . ' ' . $jsonExpressInfo ['data'] [$i] ['context'] . '；'; // 依次将每个时刻快递的配送信息赋值给$final
			}
			// 还是给$final的赋值，因为最后要加句号，所以最后一条信息单独写
			$final .= "\n" . $jsonExpressInfo ['data'] [count ( $jsonExpressInfo ['data'] ) - 1] ['time'] . ' ' . $jsonExpressInfo ['data'] [count ( $jsonExpressInfo ['data'] ) - 1] ['context'] . '。';
		}
		return $final;
	}
	
	/**
	 * 判断是否查询快递函数。
	 * @param string $originalSpilt        	
	 * @return boolean|array
	 */
	public function isQueryDelivery($originalSpilt = NULL) {
		$isQueryExpress = false; // 是否查询快递置为false
		$allName = ''; // 快递公司全称（为了拼接出快递公司全称）
		$endflag = false; // 快递公司全程拼接结束标记
		                  
		// 先看有没有快递二字
		for($i = 0; $i < count ( $originalSpilt ); $i ++) {
			if ($originalSpilt [$i] ['word_tag'] == 95 && $originalSpilt [$i] ['word'] == '快递') {
				$isQueryExpress = true;
				break;
			}
		}
		
		// 如果是查询快递，最终返回公司英文名和快递单号数组；如果不是查询快递，返回false。
		if ($isQueryExpress) {
			
			// 再找快递公司名字
			for($j = 0; $j < count ( $originalSpilt ); $j ++) {
				if ($originalSpilt [$j] ['word_tag'] == 100 || $originalSpilt [$j] ['word_tag'] == 95 || $originalSpilt [$j] ['word_tag'] == 170 || $originalSpilt [$j] ['word_tag'] == 10 || $originalSpilt [$j] ['word_tag'] == 60) {
					$endflag = true;
					$allName .= $originalSpilt [$j] ['word'];
				} else {
					if ($endflag) break;
				}
			}
			
			// 再找快递单号
			$sheetnumber = '';
			for($k = 0; $k < count ( $originalSpilt ); $k ++) {
				if ($originalSpilt [$k] ['word_tag'] == 90) {
					$sheetnumber = $originalSpilt [$k] ['word'];
					break;
				}
			}
			
			return array (
					'com' => $this->getExpressCompany ( $allName ),
					'nu' => $sheetnumber 
			);
		} else {
			return false;
		}
	}
	
	/**
	 * 快递公司映射函数。
	 * 
	 * @param
	 *        	string 快递公司中文名
	 * @return string 快递公司英文名
	 */
	private function getExpressCompany($companyName = '') {
		$allExpressCompany = array (
				'AAE' => 'aae',
				'安捷' => 'anjie',
				'安能' => 'anneng',
				'奥硕' => 'aoshuo',
				'Aramex国际' => 'aramex',
				'百千诚国际' => 'baiqian',
				'巴伦支' => 'balunzhi',
				'宝通达' => 'baotongda',
				'成都奔腾国际' => 'benteng',
				'长通' => 'changtong',
				'程光' => 'chengguang',
				'城际' => 'chengji',
				'城市100' => 'chengshi100',
				'传喜' => 'chuanxi',
				'传志' => 'chuanzhi',
				'出口易' => 'chukouyi',
				'CityLinkExpress' => 'citylink',
				'东方' => 'coe',
				'中国远洋运输' => 'coscon',
				'城市之星' => 'cszx',
				'大达' => 'dada',
				'大金' => 'dajin',
				'大田' => 'datian',
				'大洋' => 'dayang',
				'德邦' => 'debang',
				'德创' => 'dechuang',
				'DHL' => 'dhl',
				'店通' => 'diantong',
				'递四方速递' => 'disifang',
				'DPEX' => 'dpex',
				'D速' => 'dsu',
				'百福东方' => 'ees',
				'EMS' => 'ems',
				'E邮宝' => 'eyoubao',
				'凡宇' => 'fanyu',
				'Fardar' => 'fardar',
				'国际Fedex' => 'fedex',
				'Fedex国内' => 'fedexcn',
				'飞豹' => 'feibao',
				'原飞航' => 'feihang',
				'飞狐' => 'feihu',
				'飞特' => 'feite',
				'飞远' => 'feiyuan',
				'丰达' => 'fengda',
				'飞康达' => 'fkd',
				'高铁' => 'gaotie',
				'广东邮政' => 'gdyz',
				'邮政国内小包' => 'gnxb',
				'共速达' => 'gongsuda',
				'冠达' => 'guanda',
				'国通' => 'guotong',
				'山东海红' => 'haihong',
				'好来运' => 'haolaiyun',
				'昊盛' => 'haosheng',
				'河北建华' => 'hebeijianhua',
				'恒路' => 'henglu',
				'华诚' => 'huacheng',
				'华翰' => 'huahan',
				'华航' => 'huahang',
				'黄马甲' => 'huangmajia',
				'华企' => 'huaqi',
				'天地华宇' => 'huayu',
				'汇通' => 'huitong',
				'户通' => 'hutong',
				'海外环球' => 'hwhq',
				'佳吉快运' => 'jiaji',
				'佳怡' => 'jiayi',
				'佳宇' => 'jiayu',
				'加运美' => 'jiayunmei',
				'捷特' => 'jiete',
				'金大' => 'jinda',
				'京东' => 'jingdong',
				'京广' => 'jingguang',
				'晋越' => 'jinyue',
				'久易' => 'jiuyi',
				'急先达' => 'jixianda',
				'嘉里大通' => 'jldt',
				'康力' => 'kangli',
				'顺鑫' => 'kcs',
				'快捷' => 'kuaijie',
				'快优达速递' => 'kuaiyouda',
				'宽容' => 'kuanrong',
				'跨越' => 'kuayue',
				'蓝弧' => 'lanhu',
				'乐捷递' => 'lejiedi',
				'联昊通' => 'lianhaotong',
				'成都立即送' => 'lijisong',
				'上海林道货运' => 'lindao',
				'龙邦' => 'longbang',
				'门对门' => 'menduimen',
				'民邦' => 'minbang',
				'明亮' => 'mingliang',
				'闽盛' => 'minsheng',
				'尼尔' => 'nell',
				'港中能达' => 'nengda',
				'新顺丰' => 'nsf',
				'OCS' => 'ocs',
				'陪行' => 'peixing',
				'平安达' => 'pinganda',
				'中国邮政平邮' => 'pingyou',
				'全晨' => 'quanchen',
				'全峰' => 'quanfeng',
				'全日通' => 'quanritong',
				'全一' => 'quanyi',
				'日日顺' => 'ririshun',
				'日昱' => 'riyu',
				'RPX保时达' => 'rpx',
				'如风达' => 'rufeng',
				'赛澳递' => 'saiaodi',
				'三态速递' => 'santai',
				'伟邦' => 'scs',
				'圣安' => 'shengan',
				'晟邦' => 'shengbang',
				'盛丰' => 'shengfeng',
				'盛辉' => 'shenghui',
				'申通' => 'shentong',
				'世运' => 'shiyun',
				'顺丰' => 'shunfeng',
				'速呈宅配' => 'suchengzhaipei',
				'穗佳' => 'suijia',
				'速尔' => 'sure',
				'天天' => 'tiantian',
				'TNT' => 'tnt',
				'高考录取通知书' => 'tongzhishu',
				'合众' => 'ucs',
				'UPS' => 'ups',
				'USPS' => 'usps',
				'万博' => 'wanbo',
				'微特派' => 'weitepai',
				'祥龙运通' => 'xianglong',
				'新邦' => 'xinbang',
				'信丰' => 'xinfeng',
				'星程宅配' => 'xingchengzhaipei',
				'希优特' => 'xiyoute',
				'源安达' => 'yad',
				'亚风' => 'yafeng',
				'一邦' => 'yibang',
				'银捷' => 'yinjie',
				'亿顺航' => 'yishunhang',
				'优速' => 'yousu',
				'北京一统飞鸿' => 'ytfh',
				'远成' => 'yuancheng',
				'圆通' => 'yuantong',
				'越丰' => 'yuefeng',
				'宇宏' => 'yuhong',
				'誉美捷' => 'yumeijie',
				'韵达' => 'yunda',
				'运通中港' => 'yuntong',
				'增益' => 'zengyi',
				'宅急送' => 'zhaijisong',
				'郑州建华' => 'zhengzhoujianhua',
				'芝麻开门' => 'zhima',
				'济南中天万运' => 'zhongtian',
				'中铁快运' => 'zhongtie',
				'中通' => 'zhongtong',
				'忠信达' => 'zhongxinda',
				'中邮' => 'zhongyou',
				'AAE快递' => 'aae',
				'安捷快递' => 'anjie',
				'安能物流' => 'anneng',
				'奥硕物流' => 'aoshuo',
				'Aramex国际快递' => 'aramex',
				'百千诚国际物流' => 'baiqian',
				'巴伦支' => 'balunzhi',
				'宝通达' => 'baotongda',
				'成都奔腾国际快递' => 'benteng',
				'长通物流' => 'changtong',
				'程光快递' => 'chengguang',
				'城际快递' => 'chengji',
				'城市100' => 'chengshi100',
				'传喜快递' => 'chuanxi',
				'传志快递' => 'chuanzhi',
				'出口易物流' => 'chukouyi',
				'CityLinkExpress' => 'citylink',
				'东方快递' => 'coe',
				'中国远洋运输' => 'coscon',
				'城市之星' => 'cszx',
				'大达物流' => 'dada',
				'大金物流' => 'dajin',
				'大田物流' => 'datian',
				'大洋物流快递' => 'dayang',
				'德邦物流' => 'debang',
				'德创物流' => 'dechuang',
				'DHL快递' => 'dhl',
				'店通快递' => 'diantong',
				'递四方速递' => 'disifang',
				'DPEX快递' => 'dpex',
				'D速快递' => 'dsu',
				'百福东方物流' => 'ees',
				'EMS快递' => 'ems',
				'E邮宝' => 'eyoubao',
				'凡宇快递' => 'fanyu',
				'Fardar' => 'fardar',
				'国际Fedex' => 'fedex',
				'Fedex国内' => 'fedexcn',
				'飞豹快递' => 'feibao',
				'原飞航物流' => 'feihang',
				'飞狐快递' => 'feihu',
				'飞特物流' => 'feite',
				'飞远物流' => 'feiyuan',
				'丰达快递' => 'fengda',
				'飞康达快递' => 'fkd',
				'高铁快递' => 'gaotie',
				'广东邮政物流' => 'gdyz',
				'邮政国内小包' => 'gnxb',
				'共速达物流' => 'gongsuda',
				'共速达快递' => 'gongsuda',
				'冠达快递' => 'guanda',
				'国通快递' => 'guotong',
				'山东海红快递' => 'haihong',
				'好来运快递' => 'haolaiyun',
				'昊盛物流' => 'haosheng',
				'河北建华快递' => 'hebeijianhua',
				'恒路物流' => 'henglu',
				'华诚物流' => 'huacheng',
				'华翰物流' => 'huahan',
				'华航快递' => 'huahang',
				'黄马甲快递' => 'huangmajia',
				'华企快递' => 'huaqi',
				'天地华宇物流' => 'huayu',
				'汇通快递' => 'huitong',
				'户通物流' => 'hutong',
				'海外环球快递' => 'hwhq',
				'佳吉快运' => 'jiaji',
				'佳怡物流' => 'jiayi',
				'佳宇物流' => 'jiayu',
				'加运美快递' => 'jiayunmei',
				'捷特快递' => 'jiete',
				'金大物流' => 'jinda',
				'京东快递' => 'jingdong',
				'京广快递' => 'jingguang',
				'晋越快递' => 'jinyue',
				'久易快递' => 'jiuyi',
				'急先达物流' => 'jixianda',
				'嘉里大通物流' => 'jldt',
				'康力物流' => 'kangli',
				'顺鑫快递' => 'kcs',
				'快捷快递' => 'kuaijie',
				'快优达速递' => 'kuaiyouda',
				'宽容物流' => 'kuanrong',
				'跨越快递' => 'kuayue',
				'蓝弧快递' => 'lanhu',
				'乐捷递快递' => 'lejiedi',
				'联昊通快递' => 'lianhaotong',
				'成都立即送快递' => 'lijisong',
				'上海林道货运' => 'lindao',
				'龙邦快递' => 'longbang',
				'门对门快递' => 'menduimen',
				'民邦快递' => 'minbang',
				'明亮物流' => 'mingliang',
				'闽盛快递' => 'minsheng',
				'尼尔快递' => 'nell',
				'港中能达快递' => 'nengda',
				'新顺丰快递' => 'nsf',
				'OCS快递' => 'ocs',
				'陪行物流' => 'peixing',
				'平安达' => 'pinganda',
				'中国邮政平邮' => 'pingyou',
				'全晨快递' => 'quanchen',
				'全峰快递' => 'quanfeng',
				'全日通快递' => 'quanritong',
				'全一快递' => 'quanyi',
				'日日顺物流' => 'ririshun',
				'日昱物流' => 'riyu',
				'RPX保时达' => 'rpx',
				'如风达快递' => 'rufeng',
				'赛澳递' => 'saiaodi',
				'三态速递' => 'santai',
				'伟邦快递' => 'scs',
				'圣安物流' => 'shengan',
				'晟邦物流' => 'shengbang',
				'盛丰物流' => 'shengfeng',
				'盛辉物流' => 'shenghui',
				'申通快递' => 'shentong',
				'世运快递' => 'shiyun',
				'顺丰快递' => 'shunfeng',
				'速呈宅配' => 'suchengzhaipei',
				'穗佳物流' => 'suijia',
				'速尔快递' => 'sure',
				'天天快递' => 'tiantian',
				'TNT快递' => 'tnt',
				'高考录取通知书' => 'tongzhishu',
				'合众速递' => 'ucs',
				'UPS快递' => 'ups',
				'USPS快递' => 'usps',
				'万博快递' => 'wanbo',
				'微特派' => 'weitepai',
				'祥龙运通快递' => 'xianglong',
				'新邦物流' => 'xinbang',
				'信丰快递' => 'xinfeng',
				'星程宅配快递' => 'xingchengzhaipei',
				'希优特快递' => 'xiyoute',
				'源安达快递' => 'yad',
				'亚风快递' => 'yafeng',
				'一邦快递' => 'yibang',
				'银捷快递' => 'yinjie',
				'亿顺航快递' => 'yishunhang',
				'优速快递' => 'yousu',
				'北京一统飞鸿快递' => 'ytfh',
				'远成物流' => 'yuancheng',
				'圆通快递' => 'yuantong',
				'越丰快递' => 'yuefeng',
				'宇宏物流' => 'yuhong',
				'誉美捷快递' => 'yumeijie',
				'韵达快递' => 'yunda',
				'运通中港快递' => 'yuntong',
				'增益快递' => 'zengyi',
				'宅急送快递' => 'zhaijisong',
				'郑州建华快递' => 'zhengzhoujianhua',
				'芝麻开门快递' => 'zhima',
				'济南中天万运' => 'zhongtian',
				'中铁快运' => 'zhongtie',
				'中通快递' => 'zhongtong',
				'忠信达快递' => 'zhongxinda',
				'中邮物流' => 'zhongyou',
				'' => '' 
		);
		return $allExpressCompany [$companyName];
	}
}
?>