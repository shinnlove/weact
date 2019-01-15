<?php
/**
 * 微动平台处理微信不同类型数据、做存储前处理的类。
 * 作者：赵臣升。
 * 时间：2014/06/22 20:18:25。
 * 本类作用：
 * 1、提供微信API接口的信息；
 * 2、对数据进行处理，返回微动数据库的格式。
 * 
 * 微信基础消息API接口罗列如下（新的菜单功能键不包括，请扩展）：
 * 
 * 大类一：
 * 
 * 1、微信text文本信息字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				微信公众账号的original_id
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>1348831860</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[text]]></MsgType>						文本为text
 * <Content><![CDATA[this is a test]]></Content>			文本消息内容
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * </xml>
 * 
 * 2、微信image图片信息字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				微信公众账号的original_id
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>1348831860</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[image]]></MsgType>						图片为image
 * <PicUrl><![CDATA[this is a url]]></PicUrl>				图片链接，已经上传到微信资源服务器上的绝对地址（可以直接引用）
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * <MediaId><![CDATA[media_id]]></MediaId>					图片消息媒体id，可以调用多媒体文件下载接口拉取图片数据。
 * </xml>
 * 
 * 3、微信voice语音信息字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>1357290913</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[voice]]></MsgType>						语音为voice
 * <MediaId><![CDATA[media_id]]></MediaId>					语音消息媒体id，可以调用多媒体文件下载接口拉取数据。
 * <Format><![CDATA[Format]]></Format>						语音格式，如amr，speex等
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * <Recognition><![CDATA[语音识别内容]]></Recognition>			经过微信服务器端语音识别技术识别后的文字内容
 * </xml>
 * 
 * 4、微信video视频信息字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>1357290913</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[video]]></MsgType>						视频为video
 * <MediaId><![CDATA[media_id]]></MediaId>					视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
 * <ThumbMediaId><![CDATA[thumb_media_id]]></ThumbMediaId>	视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * </xml>
 * 
 * 5、微信location位置信息字段（用户使用地理位置选择器，选定一个位置后，发送给微信公众账号的）
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>1351776360</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[location]]></MsgType>					地理位置为location
 * <Location_X>23.134521</Location_X>						地理位置维度
 * <Location_Y>113.358803</Location_Y>						地理位置经度
 * <Scale>20</Scale>										地图缩放大小
 * <Label><![CDATA[位置信息]]></Label>							地理位置信息
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * </xml>
 * 
 * 6、微信link链接信息字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				接收方微信号
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方微信号，若为普通用户，则是一个OpenID
 * <CreateTime>1351776360</CreateTime>						消息创建时间
 * <MsgType><![CDATA[link]]></MsgType>						链接消息为link
 * <Title><![CDATA[公众平台官网链接]]></Title>					消息标题
 * <Description><![CDATA[公众平台官网链接]]></Description>		消息描述
 * <Url><![CDATA[url]]></Url>								消息链接
 * <MsgId>1234567890123456</MsgId>							消息id，64位整型
 * </xml>
 * 
 * 大类二：
 * 
 * 1、微信订阅/取消订阅事件的字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[FromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[subscribe/unsubscribe]]></Event>			事件类型，subscribe(订阅)、unsubscribe(取消订阅)
 * <EventKey><![CDATA[]]></EventKey>						无论是普通订阅还是取消订阅，EventKey字段的data是空的，只有扫码关注的才不空
 * </xml>
 * 
 * 2、微信用户未关注，扫腾讯二维码关注后的事件推送（区别于二维码的关键在于EventKey的qrscene_后边的参数值）
 * <xml><ToUserName><![CDATA[toUser]]></ToUserName>			开发者微信号
 * <FromUserName><![CDATA[FromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[subscribe]]></Event>						事件类型，subscribe
 * <EventKey><![CDATA[qrscene_12313]]></EventKey>			事件KEY值，qrscene_为前缀，后面为二维码的参数值，特别注意，永久二维码的参数值为1~100000.
 * <Ticket><![CDATA[TICKET]]></Ticket>						二维码的ticket，可用来换取二维码图片
 * </xml>
 * 
 * 3、微信用户已关注的事件推送的字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[FromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[SCAN]]></Event>							事件类型，SCAN
 * <EventKey><![CDATA[SCENE_VALUE]]></EventKey>				事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id，SCENE_VALUE是一个整型变量，特别注意，永久二维码的参数值为1~100000.
 * <Ticket><![CDATA[TICKET]]></Ticket>						二维码的ticket，可用来换取二维码图片
 * </xml>
 * 
 * 4、微信用户上报地理位置事件的字段（该事件是用户允许公众号定位，进入公众号后5秒1次的上报事件，请不要在被动分享位置时提醒用户，会造成5秒1次的繁琐提醒）
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[fromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[LOCATION]]></Event>						事件类型，LOCATION
 * <Latitude>23.137466</Latitude>							地理位置纬度
 * <Longitude>113.352425</Longitude>						地理位置经度
 * <Precision>119.385040</Precision>						地理位置精度
 * </xml>
 * 
 * 5、微信用户点击菜单拉取消息事件推送的字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[FromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[CLICK]]></Event>							事件类型，CLICK
 * <EventKey><![CDATA[EVENTKEY]]></EventKey>				事件KEY值，与自定义菜单接口中KEY值对应
 * </xml>
 * 
 * 6、微信用户点击菜单跳转链接时事件推送的字段
 * <xml>
 * <ToUserName><![CDATA[toUser]]></ToUserName>				开发者微信号
 * <FromUserName><![CDATA[FromUser]]></FromUserName>		发送方帐号（一个OpenID）
 * <CreateTime>123456789</CreateTime>						消息创建时间 （整型）
 * <MsgType><![CDATA[event]]></MsgType>						消息类型，event
 * <Event><![CDATA[VIEW]]></Event>							事件类型，VIEW
 * <EventKey><![CDATA[http://www.qq.com]]></EventKey>		事件KEY值，设置的跳转URL
 * </xml>
 * 
 * */
class WeActDataHandle{
	/**
	 * 微动平台最终处理完的标准格式的数据。
	 * @var array
	 */
	private $data = array();
	
	/**
	 * 本类处理来自微信服务器的消息，转成本服务器合适的数据返回存表。
	 * */
	public function dataHandle($data = NULL){
		switch ($data ['MsgType']) {
			
			case 'text': 		// 第一种：微信推送消息类型是文本的
				//处理成文本信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
					'content' => $data ['Content'],				//将消息中的Content转成微动服务器的格式content
					'msg_id' => $data ['MsgId'],					//将消息中的MsgId转成微动服务器的格式msg_id
					'remark' => '用户发送文本消息'					//微动服务器remark标记该消息是用户发送文本消息
				);
				
				break;			//case：text结束
			
			case 'image': 		// 第二种：微信推送消息类型是文本的
				//处理成图片信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
					'pic_url' => $data ['PicUrl'],				//将消息中的PicUrl转成微动服务器的格式pic_url
					'msg_id' => $data ['MsgId'],					//将消息中的MsgId转成微动服务器的格式msg_id
					'media_id' => $data ['MediaId'],				//将消息中的MediaId转成微动服务器的格式media_id
					'remark' => '用户发送图片消息'					//微动服务器remark标记该消息是用户发送图片消息
				);
				
				break;
			
			case 'voice': 		// 第三种：微信推送消息类型是文本的
				//处理成语音信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],				//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],			//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],				//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],						//将消息中的MsgType转成微动服务器的格式msg_type
					'media_id' => $data ['MediaId'],						//将消息中的MediaId转成微动服务器的格式media_id
					'voice_format' => $data ['Format'],					//将消息中的Format转成微动服务器的格式voice_format
					'msg_id' => $data ['MsgId'],							//将消息中的MsgId转成微动服务器的格式msg_id
					'recognition' => $data ['Recognition'],				//将消息中的Recongnition转成微动服务器的格式recongnition（这是语音文字识别的消息）
					'remark' => '用户发送语音消息'						//微动服务器remark标记该消息是用户发送语音消息
				);
				
				break;
			
			case 'video': 		// 第四种：微信推送消息类型是文本的
				//处理成视频信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
					'media_id' => $data ['MediaId'],				//将消息中的MediaId转成微动服务器的格式media_id
					'thumb_media_id' => $data ['ThumbMediaId'],	//将消息中的ThumbMediaId转成微动服务器的格式thumb_media_id
					'msg_id' => $data ['MsgId'],					//将消息中的MsgId转成微动服务器的格式msg_id
					'remark' => '用户发送视频消息'					//微动服务器remark标记该消息是用户发送视频消息
				);
				
				break;
			
			case 'location': 	// 第五种：微信推送消息类型是文本的
				//处理成地理位置信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
					'location_x' => $data ['Location_X'],		//将消息中的Location_X转成微动服务器的格式location_x
					'location_y' => $data ['Location_Y'],		//将消息中的Location_Y转成微动服务器的格式location_y
					'scale' => $data ['Scale'],					//将消息中的Scale转成微动服务器的格式scale
					'position_label' => $data ['Label'],			//将消息中的Label转成微动服务器的格式position_label
					'msg_id' => $data ['MsgId'],					//将消息中的MsgId转成微动服务器的格式msg_id
					'remark' => '用户主动分享地理位置给公众号'			//微动服务器remark标记该消息是用户主动分享地理位置给公众号
				);
				
				break;
			
			case 'link': 		// 第六种：微信推送消息类型是文本的
				//处理成超链接信息数据
				
				$this->data = array (
					'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
					'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
					'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
					'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
					'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
					'title' => $data ['Title'],					//将消息中的Title转成微动服务器的格式title
					'description' => $data ['Description'],		//将消息中的Description转成微动服务器的格式description
					'url' => $data ['Url'],						//将消息中的Url转成微动服务器的格式url
					'msg_id' => $data ['MsgId'],					//将消息中的MsgId转成微动服务器的格式msg_id
					'remark' => '用户发送超链接消息'					//微动服务器remark标记该消息是用户发送超链接消息
				);
				
				break;
			
			case 'event' : 		// 第七种：（独立于前6种，是接收事件推送类型）微信推送消息类型是事件的，微信平台提供的事件消息类型一共有6种，根据不同类型做出处理。

				switch ($data ['Event']) {
					case 'subscribe':	// case one ：订阅事件，1-1 用户未关注时，进行关注后的事件推送
						//处理订阅信息数据（一种直接关注、一种扫描二维码关注，第二种需要的字段多后两个）
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'event_key' => $data ['EventKey']			//将消息中的EventKey转成微动服务器的格式event_key（注意：如果是扫码关注，则该字段有qrscene_前缀）
						);
						if (! empty ( $data ['Ticket'] )) {
							$this->data ['ticket'] = $data ['Ticket']; //将消息中的Ticket转成微动服务器的格式ticket
							$this->data ['remark'] = "用户扫码订阅关注公众号";
						} else {
							$this->data ['remark'] = "用户普通订阅关注公众号"; // 普通关注方式，这里将其区分开来，以后有据可查
						}
						
						break;
					case 'SCAN':		// case two ：订阅时间，1-2 用户已关注时的事件推送
						//处理已经关注公众号数据
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'event_key' => $data ['EventKey'],			//将消息中的EventKey转成微动服务器的格式event_key
							'ticket' => $data ['Ticket'],				//将消息中的Ticket转成微动服务器的格式ticket
							'remark' => '用户已关注公众号扫码推送事件，扫的二维码参数是' . $data ['EventKey']				//微动服务器remark标记该消息是用户已关注公众号推送事件
						);
						
						break;
					case 'unsubscribe':	// case three : 取消订阅事件（数据格式同关注）
						//处理取消订阅事件数据
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type（事件类型）
							'remark' => "用户（微信openid编号为" . $data ['FromUserName'] . "）在" . timetodate ( $data ['CreateTime'] ) . "取消关注微信公众号"				//微动服务器remark标记该消息是用户取消订阅公众号事件
						);
						
						break;
					case 'CLICK': 		// case four ： 点击菜单拉取消息时的事件推送
						//处理按钮事件数据
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'event_key' => $data ['EventKey'],			//将消息中的EventKey转成微动服务器的格式event_key
							'remark' => '用户点击菜单拉取消息事件'				//微动服务器remark标记该消息是用户点击菜单拉取消息事件
						);
						
						break;
					case 'VIEW':		// case five : 点击菜单跳转链接时的事件推送
						//处理公众号对用户点击菜单跳转链接的反应事件数据
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'event_key' => $data ['EventKey'],			//将消息中的EventKey转成微动服务器的格式event_key
							'remark' => '用户点击菜单跳转链接'					//微动服务器remark标记该消息是用户点击菜单跳转链接事件
						);
						
						break;
					case 'LOCATION':	// case six ：上报地理位置事件：初次进入微信，上报地理位置；或者打开公众号，每5秒上报一次地理位置
						//处理用户地理位置数据
						
						$this->data = array (
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'latitude' => $data ['Latitude'],			//将消息中的Latitude转成微动服务器的格式latitude
							'longitude' => $data ['Longitude'],			//将消息中的Longitude转成微动服务器的格式longitude
							'position_precision' => $data ['Precision'],	//将消息中的Precision转成微动服务器的格式position_precision
							'remark' => '用户地理位置自动上报事件'				//微动服务器remark标记该消息是用户地理位置上报事件（打开公众号每5秒自动上报事件）
						);
						
						break;
					default :			// case default : 超出事件处理范围，直接返回联系客服
						//默认处理成默认类型（当前事件类型未知，虽然未知事件，但是还是记录一下数据。）
						
						$this->data = array(
							'wechat_info_id' => md5 ( uniqid ( rand (), true ) ),	//随机微动服务器接收微信消息编号
							'to_user_name' => $data ['ToUserName'],		//将消息中的ToUserName转成微动服务器的格式to_user_name
							'from_user_name' => $data ['FromUserName'],	//将消息中的FromUserName转成微动服务器的格式from_user_name
							'create_time' => $data ['CreateTime'],		//将消息中的CreateTime转成微动服务器的格式create_time
							'msg_type' => $data ['MsgType'],				//将消息中的MsgType转成微动服务器的格式msg_type
							'event_type' => $data ['Event'],				//将消息中的Event转成微动服务器的格式event_type
							'remark' => '未知/新事件'							//微动服务器remark标记该消息是未知事件
						);
						
						break;
				}
				break;
			
			default :		//其他类型：发送的$data ['MsgType']未知类型
				//处理成unknown的$data ['MsgType']未知类型
				break;
		}
		return $this->data;				//返回处理成微动服务器需要的数据格式$data数组
	}
}

?>