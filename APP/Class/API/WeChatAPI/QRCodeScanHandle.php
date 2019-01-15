<?php
/**
 * 扫码腾讯微信二维码处理类。
 * 本类功能：腾讯微信二维码扫描后关注公众号事件的同时，可以进行一些额外的预处理，
 * 包括：
 * 0、默认预留0~1000；
 * 1、活动二维码，范围是1000~5000；
 * 2、门店二维码（用户扫码关注集粉的时候进行区分）范围是5000~10000；
 * 3、导购二维码（扫码关注公众号并且选导购），范围是：>=10000。
 * 当然，如果原来的二维码已经存在，不强制改变其参数。
 * @author 赵臣升
 * CreateTime:2015/03/06 21:08:25.
 */
class WeChatQRCodeScan {
	
	/**
	 * 处理顾客扫二维码（含未关注和已关注状态；默认、活动、门店及导购3种情况）。
	 * 不管是已关注状态下的扫码，还是未关注状态下的扫码，二维码的微动平台的作用是不会变的；
	 * 对未关注的用户，只是多一步扫描关注（可能涉及到门店的分类）和未注册用户的代注册。
	 * @param array $einfo 企业信息
	 * @param array $scancodeinfo 扫描二维码的信息
	 * @property string $ticket_id|$scancodeinfo ['Ticket'] 腾讯微信二维码编号
	 * @property string $param|$scancodeinfo ['EventKey'] 腾讯微信二维码附带参数
	 * @property string Event 是否为扫码关注，默认是已关注状态下的扫码事件，所以扫码关注的$scansubscribe默认值是false
	 * @return array $replyinfo 顾客扫码之后的回复信息
	 * @property string response_function 回复处理函数
	 * @property string response_content_id 回复内容
	 */
	public function qrScanHandle($einfo = NULL, $scancodeinfo = NULL) {
		$replyinfo = array (); // 顾客扫码之后的回复，包含response_function和response_content_id
		$ticketinfo = array (); // 默认二维码信息
		
		// 预处理1：获得扫二维码的值
		$param = null; // 默认二维码参数值0
		if ($scancodeinfo ['Event'] == 'subscribe') {
			$param = substr ( $scancodeinfo ['EventKey'], 8, strlen ( $scancodeinfo ['EventKey'] ) - 7 ); // 未关注去掉前缀"qrscene_"，提取二维码参数
		} else if ($scancodeinfo ['Event'] == 'SCAN') {
			$param = $scancodeinfo ['EventKey']; // 已关注则直接获得二维码参数
		}
		// 预处理2：获得二维码的ticket值
		$ticket_id = $scancodeinfo ['Ticket'];
		
		// 预处理3：防止空ticket_id和二维码值为空的情况
		if (! empty ( $ticket_id ) && ! empty ( $param )) {
			
			// 更新其微信用户信息：用户关注公众号（新用户关注或老用户重新关注两种情况）；老关注用户扫码，刷新活跃时间，更新微信用户信息
			
			$openid = $scancodeinfo ['FromUserName']; // 用户微信openid
			// Step1：调取扫码微信用户信息
			$swc = A ( 'Service/WeChat' ); // 实例化微信服务层
			$wechaterinfo = $swc->getUserInfo ( $einfo, $openid ); // 调用微信接口获取用户信息
			
			// Step2：检测wechatuserinfo表中是否有该用户，如果没有则新插入一条信息（新用户）；如果有更新其信息（老用户新关注）。
			$wutable = M ( 'wechatuserinfo' ); // 实例化微信用户表
			$wuexist = array (
					'enterprise_wechat' => $einfo ['original_id'], // 当前微信公众号original_id
					'openid' => $openid, // 当前微信用户
					'is_del' => 0 // 没有被删除的
			);
			$oldwuinfo = $wutable->where ( $wuexist )->find (); // 尝试找到微信用户信息
			if ($oldwuinfo) {
				if ($wechaterinfo ['subscribe'] == 0) {
					// 如果找到这样的用户，说明是老用户重新关注，系统使用接口返回的为其更新老用户信息
					$oldwuinfo ['subscribe'] = $wechaterinfo ['subscribe']; // 新关注啦
					$oldwuinfo ['nickname'] = $wechaterinfo ['nickname']; // 用户的昵称
					$oldwuinfo ['sex'] = $wechaterinfo ['sex']; // 用户的性别
					$oldwuinfo ['language'] = $wechaterinfo ['language']; // 用户使用的语言
					$oldwuinfo ['city'] = $wechaterinfo ['city']; // 用户所在城市
					$oldwuinfo ['province'] = $wechaterinfo ['province']; // 用户所在省份
					$oldwuinfo ['country'] = $wechaterinfo ['country']; // 用户所在国家
					$oldwuinfo ['head_img_url'] = $wechaterinfo ['headimgurl']; // 用户的头像
					$oldwuinfo ['subscribe_time'] = time (); // 老用户重新关注，时间用新关注的时间
					$oldwuinfo ['latest_active'] = time (); // 关注重新刷新活跃时间
					$regupresult = $wutable->save ( $oldwuinfo ); // 将用户的新信息更新回表中
				}
			} else {
				// 如果没找到这样的用户，说明是全新的用户，系统为其插入一条新微信用户信息
				$newwuinfo = array (
						'user_info_id' => md5 ( uniqid ( rand (), true ) ), // 生成主键
						'enterprise_wechat' => $einfo ['original_id'], // 企业微信号openid
						'group_id' => 0, // 新关注用户默认就在未分组里
						'subscribe' => 1, // 用户关注
						'openid' => $openid, // 当前微信用户openid
						'nickname' => $wechaterinfo ['nickname'], // 微信用户昵称
						'sex' => $wechaterinfo ['sex'], // 微信用户性别
						'language' => $wechaterinfo ['language'], // 微信用户使用语言
						'city' => $wechaterinfo ['city'], // 微信用户所在城市
						'province' => $wechaterinfo ['province'], // 微信用户所在省份
						'country' => $wechaterinfo ['country'], // 微信用户所在国家
						'head_img_url' => $wechaterinfo ['headimgurl'], // 微信用户头像
						'subscribe_time' => time (), // 用户的关注时间是当前
						'add_time' => time (), // 新增这条用户记录的时间
						'latest_active' => time (), // 新用户关注，最新活跃时间是现在
			
				);
				$regupresult = $wutable->add ( $newwuinfo ); // 将新微信用户信息插入到微信用户表中
			}
			
			// 接着处理二维码扫码事情：
			
			// Step1：双索引查询带场景的二维码，找到被扫描的二维码信息，缩写scenecode→sc
			$scmap = array (
					'e_id' => $einfo ['e_id'], // 当前企业的二维码，特别注意：如果发送给当前商家API下信息包里的二维码不是当前商家的，是不会触发在当前商家下自动注册用户的，除非商家和二维码吻合。
					'code_ticket' => $ticket_id, // 微信二维码的ticket_id
					'code_param' => $param, // 微信二维码的param
					'is_del' => 0
			);
			$sctable = M ( 'scenecode' );
			$ticketinfo = $sctable->where ( $scmap )->find (); 	// 查询用户所扫描二维码信息，特别注意：同时把分店信息查出来了，$ticketinfo里边subbranch字段的值，可能是-1、也可能是某个md5码。
			
			// Step2：双索引没找到了二维码信息
			if (! $ticketinfo) {
				// 通过ticket_id和param找不到二维码相关信息，说明一种可能：
				// 是G5G6老版本二维码（2015/03/06 23:12:25以前），并没有存code_param，双where找不到（限定条件过多），所以要重新只通过code_ticket进行锁定（不要param）；
				// 如果是G5G6老版本二维码（2015/03/06 23:12:25以前），还要再重新更新这个code_param值进去，以便下次能方便二维码锁定code_param；
				// 但是特别注意：鉴于小概率事件：ticket_id可能重复，更新的时候用select后，再选择code_param值为null或者空的记录去更新；以免新版本新生成的二维码重复老版本的ticket_id，让老版本二维码的永远无法识别到了。。
				unset ( $scmap ['code_param'] ); // 取消对code_param的索引
				
				$ticketinfolist = $sctable->where ( $scmap )->select (); // ticket_id单索引多张二维码select
				if (! empty ( $ticketinfolist )) {
					for ($i = 0; $i < count ( $ticketinfolist ); $i ++) {
						if (empty ( $ticketinfolist [$i] ['code_param'] )) {
							$ticketinfolist [$i] ['code_param'] = $param; // 将参数给到老版本二维码中
							$ticketinfo = $ticketinfolist [$i]; // 将信息给到老版本二维码中（后续使用这个变量）
							$updateversion = $sctable->save ( $ticketinfo ); // 将老版本二维码更新成新版本带参数二维码
							break; // 如果有，也只可能有一张需要更新
						}
					}
				}
			} else {
				// 通过ticket_id和param找到了二维码相关信息，说明两种情况：
				// 1、G5G6老版本二维码（2015/03/06 23:12:25以前）被更新成了新版本的二维码，也能通过新方式锁定到了；
				// 2、这是一张新版本的二维码
			}
			
			// Step3：如果数据库有相关二维码信息
			if (! empty ( $ticketinfo )) {
				
				// 预定义最终处理完事项后，默认查询到场景值的回复（这样分店可以和总店的关注信息不一样，就是通过这里），若是图文跳转链接则带上shop_id（无论用户怎么访问，注册的时候会关联openid进行更新，再也不会出现门店信息遗失了）
				$replyinfo = array (
						'response_function' => $ticketinfo ['response_function'],
						'response_content_id' => $ticketinfo ['response_content_id']
				);
				
				$available = true; // 默认这张二维码还是有效的（永久永远有效、临时最大半小时有效）
					
				// Step3-1：如果是一张临时二维码，检查其有效性（是否过时）/永久二维码不进入判断
				if ($ticketinfo ['code_type'] == 0) {
					$timenow = time (); // 取当前时间
					$timespan = $timenow - $ticketinfo ['create_time']; // 测试前记得用代码将表里的create_time改成整型
					if (empty ( $ticketinfo ['effective_time'] )) $ticketinfo ['effective_time'] = 1; // 没有定义时间，默认为1秒有效
					if ($timespan > $ticketinfo ['effective_time']) {
						$available = false; // 临时二维码失效
					}
				}
					
				// Step3-2：有效状态才对二维码做出反应；无效状态/没查询到二维码状态，都直接返回关注信息。
				if ($available) {
					
					// 根据微信用户openid检查该用户有没有在微动的商家平台注册过账号：（customerinfo→ci）
					$cinfo = array (); // 顾客信息
					$cimap = array (
							'openid' => $scancodeinfo ['FromUserName'],	// 当前用户的微信openid
							'e_id' => $einfo ['e_id'],						// 虽然微信平台说，同一个微信用户对于不同公众号的openid是唯一的，不同公众号openid是不同的，但是这里还是再加商家区分一下
							'is_del' => 0
					);
					$citable = M ( 'customerinfo' );
					$cinfo = $citable->where ( $cimap )->find (); // 计算有没有这样openid的用户存在
					// Step2-2：如果没找到这样的用户就替他注册一个扫码关注账号
					if (! $cinfo) {
						$delegatecustomer = array (); // 系统代理托管注册的账号（日后更新）
						$delegatecustomer ['customer_id'] = date ( 'YmdHms' ) . randCode ( 4, 1 ); 	// 产生顾客编号
						$delegatecustomer ['openid'] = $scancodeinfo ['FromUserName']; 				// 关联微信号
						$delegatecustomer ['e_id'] = $einfo ['e_id']; 								// 关联商家编号
						$delegatecustomer ['subordinate_shop'] = $ticketinfo ['subbranch_id']; 		// 关键的一步：帮用户先在微动商家平台用微信号注册，日后登录或注册再进行账号绑定更新数据
						$delegatecustomer ['register_time'] = time (); 								// 系统代为注册的时间
						$delegatecustomer ['remark'] = "用户（微信openid为" . $scancodeinfo ['FromUserName'] . "）在" . timetodate( time () ) . "扫码关注本公众号"; // 记录下微信扫码用户
						$newresult = $citable->add ( $delegatecustomer );							// 特别注意：用户账号和密码可以为空，否则记录插不进去。
						if ($newresult) {
							$cinfo = $delegatecustomer; // 注册信息给过去
						}
					}
					
					// 对各种二维码进行处理
					if ($ticketinfo ['code_use'] == 0) {
						// Step3-2-1：二维码用途默认0
						
					} else if ($ticketinfo ['code_use'] == 1) {
						// Step3-2-2：1代表二维码是活动推广
						
					} else if ($ticketinfo ['code_use'] == 2) {
						// Step3-2-3：2代表二维码时顾客扫码门店，
						// 如果找到这样的客户，看他有没有分店编号，如果没有，则绑定一个
						if ($scancodeinfo ['Event'] == 'SCAN' && $ticketinfo ['subbranch_id'] != '-1' && ! empty ( $ticketinfo ['subbranch_id'] )) {
							// 已关注用户再扫描，检测原来有没有分店编号，如果有的没有，绑定扫码分店
							if ($cinfo ['subordinate_shop'] == '-1' || empty ( $cinfo ['subordinate_shop'] ) ) {
								$cinfo ['subordinate_shop'] = $ticketinfo ['subbranch_id']; // 没有分店编号，绑定一个分店编号
								$bindresult = $citable->save ( $cinfo ); // 带主键的保存绑定信息
							}
						}
						// 附带门店参数
						$replyinfo ['params'] = array (
								'shop_id' => $ticketinfo ['subbranch_id'], // 门店编号
						);
					} else if ($ticketinfo ['code_use'] == 3) {
						// Step3-2-4：扫导购二维码
						$scancodeinfo ['code_ticket'] = $ticket_id; // 扫码字段中增加ticket
						$scancodeinfo ['code_param'] = $param; // 扫码字段中增加param
						$sg = A ( 'GuideApp/GuideInfo' );
						$sg->scanSelectGuide ( $scancodeinfo ); // 选导购
						// 附带导购参数
						$replyinfo ['params'] = array (
								'gid' => $ticketinfo ['guide_id'], // 选择导购
						);
					}
				} else {
					// 二维码过期，自动回复回复下欢迎关注
					$armap = array (
							'e_id' => $einfo ['e_id'],
							'response_type' => "subscribe",
							'is_del' => 0
					);
					$artable = M ( 'autoresponse' );
					$arresult = $artable->where ( $armap )->order ( 'add_response_time desc' )->limit ( 1 )->select (); 	// 查出最新添加的关于回复的一条记录
						
					$replyinfo = array (
							'response_function' => $arresult [0] ['response_function'],
							'response_content_id' => $arresult [0] ['response_content_id']
					);
				}
			} else {
				// 如果没有查询到场景值，则返回商家在平台预设欢迎关注本公众号的信息（此分支没有分店信息）
				$armap = array (
						'e_id' => $einfo ['e_id'],
						'response_type' => $scancodeinfo ['Event'],
						'is_del' => 0
				);
				$artable = M ( 'autoresponse' );
				$arresult = $artable->where ( $armap )->order ( 'add_response_time desc' )->limit ( 1 )->select (); 	// 查出最新添加的关于回复的一条记录
					
				$replyinfo = array (
						'response_function' => $arresult [0] ['response_function'],
						'response_content_id' => $arresult [0] ['response_content_id']
				);
			}
		}
		
		// 最终步骤返回回复信息$replyinfo
		return $replyinfo; 
	}
}
?>