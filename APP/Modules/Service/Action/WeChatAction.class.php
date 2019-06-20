<?php
import ( 'Class.API.WeChatAPI.WeactWechat', APP_PATH, '.php' ); // 载入微动新集成微信SDK
/**
 * 微动调用微信API接口服务层。
 * @author 黄昀，赵臣升。
 * CreateTime：2014/12/18 10:46:36.
 * ModifyTime：2014/12/19 13:36:25.
 * LatestModify：2015/04/10 14:16:25.
 */
class WeChatAction extends Action {
	
	private $weixin;
	
	/**
	 * 返回微信错误。
	 * @return array $error 微信接口调用错误信息
	 */
	public function getError() {
		return $this->weixin->getError();
	}
	
	/**
	 * ----------------------Part1：直接调用微信API的Service--------------------------
	 */
	
	/**
	 * ----------------------功能：基础支持--------------------------
	 * 1、获取access_token（默认调用SDK的时候自己会去请求，Service层不提供）；2、获取微信服务器IP地址。
	 */
	
	/**
	 * ----------------------功能：接收消息--------------------------
	 * 1、验证消息真实性（放到SDK中去做，这里Service层并不处理）；2、接收普通消息；3、接收事件推送。
	 */
	
	/**
	 * 获取微信服务器地址列表。
	 * @param array $einfo 企业信息
	 * @return array $wechatiplist 微信服务器地址列表
	 */
	public function wechatServerIP($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo );
		$wechatiplist = $this->weixin->wechatServerIP ();
		return $wechatiplist;
	}
	
	/**
	 * ----------------------功能：发送消息--------------------------
	 * 1、自动回复（被动响应）；2、客服接口（主动发送）；3、群发接口；4、模板消息（业务通知）。
	 * 
	 * 1a、回复文本消息；1b、回复图片消息；1c、回复语音消息；1d、回复视频消息；1e、回复音乐消息；1f、回复图文消息。
	 * 
	 * 2a、发送（本地）文本消息；2b、发送（本地）图片消息；2c、发送（本地）语音消息；2d、发送（本地）视频消息；2e、发送（本地）音乐消息；2f、发送（本地）图文消息。
	 * 
	 * 3a、上传图文消息素材（订阅与服务认证后均可用）；3b、根据分组进行群发（订阅与服务认证后均可用）；
	 * 3c、根据openid列表群发（订阅不可用，服务号认证后可用）；3d、删除群发（订阅与服务认证后均可用）；
	 * 3e、预览接口（订阅与服务认证后均可用）； 3f、查询群发消息发送状态（订阅与服务认证后均可用）；
	 * 3g、事件推送群发结果。
	 * 
	 * 4a、设置所属行业；4b、获得模板ID；4c、发送模板消息；4d、事件推送。
	 */
	
	/**
	 * 微信接口：（公众号/客服）主动发送文本消息（文本消息内容已经给出）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收文本消息的微信用户openid
	 * @param string $text 要发送的文本信息
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送文本消息的结果
	 */
	public function sendText($einfo = NULL, $receiver = '', $text = '', $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendMsg ( $text, $receiver, 'text', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送本地数据库的文本消息给客户（文本消息形参是本地msgtext表中的某文本主键）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收文本消息的微信用户openid
	 * @param string $msgtext_id 发送的文本消息主键编号
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送文本消息的结果
	 */
	public function sendLocalText($einfo = NULL, $receiver = '', $msgtext_id = '', $kf_account = '') {
		//缩写：msgtext→mt
		$mtmap = array (
				'e_id' => $einfo ['e_id'],
				'msgtext_id' => $msgtext_id,
				'is_del' => 0
		);
		$mtresult = M ( 'msgtext' )->where ( $mtmap )->find (); // 从msgtext表中找到要发送的文本信息
		return $this->sendText ( $einfo, $receiver, $mtresult ['content'], $kf_account );
	}
	
	/**
	 * 微信接口：（公众号/客服）主动发送图片消息给用户，根据微信平台上传多媒体接口返回的media_id去发送图片消息。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收图片消息的微信用户openid
	 * @param string $media_id 上传至微信平台返回得到的多媒体编号
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送图片消息的结果
	 */
	public function sendImage($einfo = NULL, $receiver = '', $media_id = '', $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendMsg ( $media_id, $receiver, 'image', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送本地图片消息给客户（图片消息形参是本地msgimage表中的某图片主键）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收图片消息的微信用户openid
	 * @param string $msgimage_id 要发送的图片编号（主键非media_id）media_id。
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送图片消息的结果
	 */
	public function sendLocalImage($einfo = NULL, $receiver = '', $msgimage_id='', $kf_account = '') {
		//缩写：msgimage→mi
		$mimap = array (
				'e_id' => $einfo ['e_id'],
				'msgimage_id' => $msgimage_id,
				'is_del' => 0
		);
		$miresult = M ( 'msgimage' )->where ( $mimap )->find (); // 在本地数据库中找到这张图片信息
		return $this->sendImage ( $einfo, $receiver, $miresult ['media_id'], $kf_account );
	}
	
	/**
	 * 微信接口：（公众号/客服）主动发送语音消息给微信用户。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收语音消息的微信用户openid
	 * @param string $media_id 上传至微信平台返回得到的多媒体编号
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送语音消息的结果
	 */
	public function sendVoice($einfo = NULL, $receiver = '', $media_id = '', $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendMsg ( $media_id, $receiver, 'voice', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送本地语音消息给客户（语音消息形参是本地msgvoice表中的某语音主键）
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收语音消息的微信用户openid。
	 * @param string $msgvoice_id 要发送的声音编号。
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送语音消息的结果
	 */
	public function sendLocalVoice($einfo = NULL, $receiver = '', $msgvoice_id = '', $kf_account = '') {
		//缩写：msgvoice→mvi
		$mvimap = array (
				'e_id' => $einfo ['e_id'],
				'msgvoice_id' => $msgvoice_id,
				'is_del' => 0
		);
		$mviresult = M ( 'msgvoice' )->where ( $mvimap )->find (); // 找到这条语音消息
		return $this->sendVoice ( $einfo, $receiver, $mviresult ['media_id'], $kf_account );
	}
	
	/**
	 * 微信接口：（公众号/客服）主动发送视频消息给微信用户。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收视频消息的微信用户openid
	 * @param string $media_id 上传至微信平台返回得到的多媒体编号
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送视频消息的结果
	 */
	public function sendVideo($einfo = NULL, $receiver = '', $media_id = '', $thumb_media_id = '', $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$mediainfo = array (
				'media_id' => $media_id, 
				'thumb_media_id' => $thumb_media_id,
				'title' => "视频消息",
				'description' => "请查收",
		);
		$sendresult = $this->weixin->sendMsg ( $mediainfo, $receiver, 'video', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送本地视频消息给客户（视频消息形参是本地msgvideo表中的某视频主键）
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收视频消息的微信用户openid。
	 * @param string $msgvideo_id 要发送的视频编号。
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送语音消息的结果
	 */
	public function sendLocalVideo($einfo = NULL, $receiver = '', $msgvideo_id = '', $kf_account = ''){
		//缩写：msgvideo→mvd
		$mvdmap = array (
				'e_id' => $einfo ['e_id'],
				'msgvideo_id' => $msgvideo_id,
				'is_del' => 0
		);
		$mvdresult = M ( 'msgvideo' )->where ( $mvdmap )->find (); // 找到要发送的视频信息
		return $weixin->sendVideo ( $mvdresult ['media_id'], $receiver, 'video', $kf_account );
	}
	
	/**
	 * 微信接口：（公众号/客服）主动发送音乐消息给客户（音乐消息形参是本地msgmusic表中的某主键）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收音乐消息的用户openid
	 * @param array $musicinfo 要发送的音乐消息信息，必须包含如下字段值
	 * @property string title 音乐的标题
	 * @property string description 音乐的描述
	 * @property string musicurl 音乐2G/3G/4G下的网络路径
	 * @property string hqmusicurl 高质量WIFI音乐路径
	 * @property string thumb_media_id 音乐信息的一张专辑封面图（已经上传到微信的一张缩略图的media_id）
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送音乐消息的结果
	 */
	public function sendMusic($einfo = NULL, $receiver = '', $musicinfo = NULL, $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendMsg ( $musicinfo, $receiver, 'music', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送音乐消息给客户（音乐消息形参是本地msgmusic表中的某主键）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收音乐消息的用户openid
	 * @param string $msgnews_id 要发送的音乐消息编号
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送音乐消息的结果
	 */
	public function sendLocalMusic($einfo = NULL, $receiver = '', $msgmusic_id = '', $kf_account = ''){
		//缩写：msgmusic→mm
		$mmmap = array (
				'e_id' => $einfo ['e_id'],
				'msgmusic_id' => $msgmusic_id,
				'is_del' => 0
		);
		$mmresult = M ( 'msgmusic' )->where ( $mmmap )->find (); // 找到要发送的音乐信息
		// 拼接音乐消息具体内容
		$musicinfo = array(
				"title" => $mmresult ['title'], // 音乐标题
				"description" => $mmresult ['description'], // 音乐描述
				"musicurl" => $mmresult ['music_url'], // 音乐2G/3G/4G下的地址
				"hqmusicurl" => $mmresult ['hq_music_url'], // 音乐WIFI环境下高质量地址
				"thumb_media_id" => $mmresult ['thumb_media_id'], // 已经上传到微信公众平台的缩略图media_id
		);
		return $weixin->sendMusic ( $einfo, $receiver, $musicinfo, $kf_account );
	}
	
	/**
	 * 微信接口：（公众号/客服）主动发送图文消息给微信用户。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收图文消息的微信用户openid
	 * @param array $newsinfo 要发送的图文消息数组，每一条信息必须包含如下字段（默认第一条图文为封面）：
	 * @property string title 图文消息标题
	 * @property string description 图文消息描述
	 * @property string picurl 图文消息封面图
	 * @property string url 图文消息点击跳转链接
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 图文消息的发送结果
	 */
	public function sendNews($einfo = NULL, $receiver = '', $newsinfo = NULL, $kf_account = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendMsg ( $newsinfo, $receiver, 'news', $kf_account ); // 发送客服消息
		return $sendresult;
	}
	
	/**
	 * 微动拓展函数：（公众号/客服）主动发送图文消息给客户（图文消息形参是本地msgnews表中的某图文的主键）。
	 * @param array $einfo 企业信息
	 * @param string $receiver 接收图文消息的微信用户编号
	 * @param string $msgnews_id 发送图文消息的编号（微动本地msgnews表里的主键）
	 * @param string $kf_account 可选字段：客服人员账号（要发送该条消息的客服人员）
	 * @return array $sendresult 发送图文消息的结果
	 */
	public function sendLocalNews($einfo = NULL, $receiver = '', $msgnews_id = '', $kf_account = ''){
		//msgnews→mn
		$mnmap = array (
				'e_id' => $einfo ['e_id'], // 企业编号
				'msgnews_id' => $msgnews_id // 图文消息编号
		);
		$mnresult = M ( 'msgnews_info' )->where ( $mnmap )->select (); // 现在从图文视图里查找图文信息，以前是代码做视图拼接
		// 以前是做视图拼接，t_msgnews表是主表，别名p(parent)；t_msgnewsdetail表是子表，别名c(child)。
		/* $sql = 'p.msgnews_id = c.msgnews_id and p.is_del = 0 and c.is_del = 0 and p.msgnews_id = \''.$mnmap['msgnews_id'].'\' and p.e_id =\''.$mnmap['e_id'].'\'';
		$model = new Model();
		$mnresult =  $model->table('t_msgnews p, t_msgnewsdetail c')
		->where ( $sql )
		->order('p.add_time desc')
		->field('p.*, c.*')
		->select();	//查出图文 */
		
		//特别注意，主动发送图文消息是小写的
		$articlecount = count ( $mnresult ); // 统计图文条数
		$newsinfo = array (); // 最终图文消息
		if ($articlecount) {
			for($i = 0; $i < $articlecount; $i ++) {
				$newsinfo [$i] ['title'] = $mnresult [$i] ['title']; // 图文消息的标题
				$newsinfo [$i] ['description'] = $mnresult [$i] ['main_content']; // 图文消息内容
				$newsinfo [$i] ['picurl'] = assemblepath ( $mnresult [$i] ['cover_image'], true ); // 图文消息的封面地址，要组装绝对路径
				$newsinfo [$i] ['url'] = $mnresult [$i] ['link_url']; // 图文消息的原文链接
			}
		}
		return $this->sendNews ( $einfo, $receiver, $newsinfo, $kf_account );
	}
	
	/**
	 * 微信接口：调用微信API接口上传图文。
	 * @param array $einfo 企业信息
	 * @param array $newsarticles 图文信息的article
	 * @return array $uploadinfo 图文上传完毕后的返回信息
	 */
	public function uploadNewsMsg($einfo = NULL, $newsarticles = NULL) {
		$this->weixin = new WeactWechat ( $einfo );
		$newsinfo = $this->weixin->uploadNews ( $newsarticles );
		return $newsinfo;
	}
	
	/**
	 * 微信接口：群发图文接口。
	 * @param array $einfo 企业信息       	
	 * @param number $sendgroupid 要群发的微信用户组编号
	 * @param string $msgnewsid 要群发的图文编号
	 * @return array $sendresult 群发图文的结果
	 */
	public function groupSendNews($einfo = NULL, $sendgroupid = 0, $msgnewsid = '') {
		$this->weixin = new WeactWechat ( $einfo );
		
		// 要群发的图文
		$html_content = '<p style="clear: both; white-space: normal; padding: 0px; border: currentcolor; border-image-source: none; line-height: 24px;">
    <strong><span style="font-family: 宋体; font-size: 19px;">恭喜您成功获得</span></strong><strong><span style="font-family: Helvetica, sans-serif; font-size: 19px;">G5G6</span></strong><strong><span style="font-family: 宋体; font-size: 19px;">会员专享年终礼券&nbsp;</span></strong><strong><span style="font-family: Helvetica, sans-serif; font-size: 19px;">50</span></strong><strong><span style="font-family: 宋体; font-size: 19px;">元，请在“会员中心——我的优惠券”查看详情。</span></strong>
</p>
<p style="clear: both; white-space: normal; padding: 0px; border: currentcolor; border-image-source: none; line-height: 24px;">
    <strong><span style="color: red; font-family: 宋体; font-size: 20px;">提示语：<br/>本券使用之前请勿取消关注且请勿删除！</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: red; font-family: 宋体;">使用细则：</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: red; font-family: 宋体;">新VIP年终礼券使用细则：</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="background-color: yellow; color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">1.</span></strong><strong><span style="background-color: yellow; color: rgb(62, 62, 62); font-family: 宋体;">使用前若因删除或取消关注而造成券号密码遗失者，则视为自动放弃本券的使用权利；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="background-color: yellow; color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">2.</span></strong><strong><span style="background-color: yellow; color: rgb(62, 62, 62); font-family: 宋体;">若因手机故障等原因造成券号密码丢失者，请恕本平台概不负责；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">3.&nbsp;</span></strong><strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">G5G6VIP</span></strong><strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">年终礼券必须在有效期内使用有效，过期作废；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">4.&nbsp;</span></strong><strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">本券仅限新VIP开卡本人在开卡门店使用；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">5.&nbsp;</span></strong><strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">本券仅限在店铺购买正价商品时使用（皮带、袜子、鞋子除外）；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: Helvetica, sans-serif;">6.&nbsp;</span></strong><strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">为了您自己的权益，请妥善保管自己的券号和使用密码，勿泄露给他人；</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: 宋体;">7.登入会员中心后，<span style="color: rgb(255, 0, 0); background-color: rgb(255, 255, 0);">点击“我的优惠券”</span>即可查看券号和密码。</span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="color: rgb(62, 62, 62); font-family: 宋体;"><img src="https://mmbiz.qlogo.cn/mmbiz/YgIa0F4V3O6yicDK0R3JsIs55rmaU3lV9ibbSaVH11WarhELP5WM0PQslQgUibK2zvP3V4980t3QUHaPSL4iatBdpA/0" data-s="300,640" data-w="424" data-ratio="1.7146226415094339" style="max-width: 100%; height: auto !important;"/></span></strong>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <br/>
</p>
<p style="clear: both; white-space: normal; line-height: 24px;">
    <strong><span style="background-color: yellow; color: red; font-family: 宋体;">温馨提示：您在店铺使用此代金券时请及时将本内容出示给店铺导购查看，以方便您的使用。感谢您的配合。</span></strong>
</p>
<p style="clear: both; white-space: normal;">
    <strong><span style="color: rgb(62, 62, 62); font-family: 宋体; font-size: 14px;">如有任何疑议，请联系我们的微信平台或者致电<span style="color: rgb(255, 0, 0);">0571-85399617</span>，G5G6为您竭诚服务！</span></strong>
</p>
<p style="clear: both; white-space: normal;">
    <br/><img src="https://mmbiz.qlogo.cn/mmbiz/YgIa0F4V3O6yicDK0R3JsIs55rmaU3lV9uuXQBjzpye4BmkbfgwhKHUosd31iclPXUBDgYYBJ45h9ROqG61A20PQ/0" data-w="125" data-ratio="0.56" style="max-width: 100%; height: auto !important;"/><span style="color: rgb(255, 0, 0); font-size: 12px; background-color: rgb(146, 208, 80);">前往会员中心请点击【<strong>阅读原文</strong>】</span>
</p>
<p>
    <span style="color: rgb(255, 0, 0); font-size: 12px; background-color: rgb(146, 208, 80);"><br/></span>
</p>
<p>
    <br/>
</p>';
		
		$digest = '1.年终券券号和密码请一律前往“会员中心”进行查询；2.前往“会员中心”请点击正文中左下角的“阅读原文”进行登录；3.若有疑问，请拨打热线： 0571-85399617。';
		
		$imagepath = $_SERVER ['DOCUMENT_ROOT'] . __ROOT__ . '/Updata/123456.jpg';
		$mediatype = "image";
		
		$cover_media = $this->uploadMedia ( $einfo, $imagepath, $mediatype ); // 调用接口获得图片media_id
		
		$articleinfo = array (
				"articles" => array (
						0 => array (
								"thumb_media_id" => $cover_media,
								"author" => "G5G6",
								"title" => "恭喜您成功获取年终礼券 50元，请点击查看详情。",
								"content_source_url" => "http://www.we-act.cn/weact/Home/MemberHandle/customerCoupon/e_id/201405291912250003",
								"content" => $html_content,
								"digest" => $digest,
								"show_cover_pic" => "1"
						)
				)
		);
		
		$newsinfo = $this->uploadNewsMsg ( $einfo, $articleinfo ); // 调用接口上传图文信息并获得返回
		
		$sendresult = $this->weixin->publicGroupSendNews ( $groupsendinfo, $newsinfo ['media_id'] );
		return $sendresult;
	}
	
	/**
	 * ----------------------发送模板消息----------------------
	 */
	
	/**
	 * 设置企业模板消息。
	 * @param string $einfo 企业信息
	 * @param string $industry1 行业1编号，范围从1~41
	 * @param string $industry2 行业2编号，范围从1~41
	 * @return array $setresult 设置模板消息所属行业的结果
	 */
	public function setTemplateIndustry($einfo = NULL, $industry1 = "41", $industry2 = "41") {
		$this->weixin = new WeactWechat ( $einfo );
		$setresult = $this->weixin->setIndustry ( $industry1, $industry2 ); // 设置企业模板消息
		return $setresult;
	}
	
	/**
	 * 调用接口设置模板消息编号。
	 * @param string $einfo 企业信息
	 * @param string $template_id 微信公众平台的公共模板编号，不是企业自己的模板编号
	 * @return string $template 所使用的模板编号
	 */
	public function apiSetTemplate($einfo = NULL, $template_id = "TM00015") {
		$this->weixin = new WeactWechat ( $einfo );
		$template = $this->weixin->setTemplateInfo ( $template_id ); // 设置企业模板消息使用模板
		return $template;
	}
	
	/**
	 * 
	 * @param array $einfo 企业信息
	 * @param string $openid 要发送给的微信用户
	 * @param string $template_id 企业自己的模板消息编号
	 * @param string $url 点击模板消息跳转的URL地址
	 * @param array $tpldata 模板消息
	 * @param string $topcolor 模板消息顶部的颜色（并不是每个模板都可以设置顶部颜色的）
	 * @return array $sendresult 发送模板消息的结果
	 */
	public function sendTemplateMessage($einfo = NULL, $openid = "", $template_id = "", $linkurl = "", $tpldata = NULL, $topcolor = "#FF0000") {
		$this->weixin = new WeactWechat ( $einfo );
		$sendresult = $this->weixin->sendTemplateMsg ( $openid, $template_id, $linkurl, $tpldata, $topcolor ); // 发送微信模板消息
		return $sendresult;
	}
	
	/**
	 * ----------------------功能：用户管理--------------------------
	 * 1、用户分组管理；2、设置用户备注名；3、获取用户基本信息；4、获取用户列表。
	 * a、创建分组（并与本地数据库同步）；b、查询所有分组（并与本地数据库同步）；
	 * c、查询用户所在分组（单个查询和批量查询，并与本地数据库同步）；d、修改分组名字；
	 * e、移动用户分组（单个移动和批量移动，并与本地数据库同步）；
	 * f、设置用户备注名；g、获取用户信息；h、获取公众号关注者列表（并与本地数据库同步，更新信息放在后边）。
	 */
	
	/**
	 * 测试通过。
	 * 创建用户分组接口createUserGroup。
	 * 如果创建分组成功，立刻调用syncAddNewGroup同步分组函数（对于新添加的分组， 实际上就是往数据库里增加一条数据）。
	 * @param array $einfo 企业信息
	 * @param string $group_name 要创建的分组名
	 * @return array $createresult 创建分组的结果(返回php标准数组，而不是stdClass。)。
	 */
	public function createUserGroup($einfo = NULL, $group_name = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$createresult = $this->weixin->createGroup ( $group_name ); // json打包并调用接口发送
		if ($createresult) {
			$this->syncAddNewGroup ( $einfo, $createresult ); // 调用本类的对本地数据库同步的函数
		}
		return $createresult;
	}
	
	/**
	 * 测试通过。
	 * 查询某企业的微信平台所有分组信息，并与微动本地数据库同步syncWeChatUserGroupInfo。
	 * @param array $einfo 企业信息
	 * @return array $allgroupinfo 所有分组信息
	 */
	public function queryAllGroup($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo );
		$allgroupinfo = $this->weixin->queryAllGroup ();
		if ($allgroupinfo) {
			$syncresult = $this->syncWeChatUserGroupInfo ( $einfo, $allgroupinfo ); // 进行本地同步分组，形参是标准的微信返回数据解析后的数组
		}
		return $allgroupinfo;
	}
	
	/**
	 * 测试通过。
	 * 根据用户openid查询某个用户所在分组。
	 * 貌似查询用户接口后暂不需要同步信息。
	 * @param array $einfo 企业信息
	 * @param string $openid 用户微信openid
	 * @return array $queryresult 查询的结果
	 */
	public function queryUserGroup($einfo = NULL, $openid = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$queryinfo = $this->weixin->queryUserGroup ( $openid );
		return $queryinfo;
	}
	
	/**
	 * 批量查询用户所在组信息，时间复杂度更小。推荐一次查询20~50个用户的分组信息为最佳，测试通过。
	 * @param array $einfo 企业信息
	 * @param array $openidgroup 要查询的openid列表
	 * @return array|boolean $groupinfo|false 如果查询成功，返回组信息，如果查询失败，直接返回false
	 */
	public function batchQueryUserGroup($einfo = NULL, $openidgroup = NULL) {
		$this->weixin = new WeactWechat ( $einfo );
		$groupinfolist = $this->weixin->batchQueryUserGroup ( $openidgroup );
		return $groupinfolist;
	}
	
	/**
	 * 修改用户分组名接口modifyUserGroupName，已通过，已同步。
	 * 如果分组名修改成功，立即调用syncModifyGroupInfo的同步分组信息函数（可能本地还没有该分组，做一层判断）
	 * @param array $einfo 企业信息
	 * @param number $group_id 分组编号（0是默认分组，1是黑名单，2是星标组，101开始是自定义分组）
	 * @param string $modify_name 要修改的用户分组名
	 * @return array $modifyresult 修改用户分组名的结果。
	 */
	public function modifyUserGroupName($einfo = NULL, $group_id = 101, $modify_name = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$modifyresult = $this->weixin->alterGroupName ( $group_id, $modify_name );
		if ($modifyresult) {
			$syncresult = $this->syncUserGroupName ( $einfo, $group_id, $modify_name ); // 自动同步本地数据库用户组名
		}
		return $modifyresult;
	}
	
	/**
	 * 测试通过。
	 * 移动一个用户到某分组下的接口moveUserToGroup。
	 * 特别注意：如果移动用户成功，当且仅当移动到不是现在的分组里，
	 * 才会对wechatuserinfo表里的group_id同步，才会对企业分组表用户数量进行同步（不过暂时先不做同步）。
	 * @param array $einfo 企业信息
	 * @param string $openid 要移动的用户微信openid
	 * @param number $group_id 要移动的组编号
	 * @return array $moveresult 返回成功的信息|失败的错误码信息
	 */
	public function moveUserToGroup($einfo = NULL, $openid = '', $group_id = 0) {
		$this->weixin = new WeactWechat ( $einfo );
		$origingroupinfo = $this->weixin->queryUserGroup ( $openid ); // 查询下用户原来的分组
		$moveresult = $this->weixin->moveUserToGroup ( $openid, $group_id );
		if ($moveresult) {
			if (intval ( $origingroupinfo ['groupid'] ) != $group_id) {
				$this->syncModifyUserGroup ( $einfo, $openid, $group_id ); // 调用本类的对本地数据库同步的函数，同步wechatuserinfo表
				$this->syncMinusGroupNum ( $einfo, $origingroupinfo ['groupid'] ); // 继续对wechatusergroup表中的原来分组数量减少，
				$this->syncPlusGroupNum ( $einfo, $group_id ); // 对wechatusergroup表中的新分组数量增加
			}
		}
		return $moveresult; // 返回移动用户分组结果
	}
	
	/**
	 * 测试通过，此函数为原来的微动拓展函数，但是现在微信已经出台批量移动接口了，该函数建议暂时可以不用，若要用还需调试下正确性。
	 * 批量移动用户到某个分组内，从空间复杂度和时间复杂度上进行优化，特别注意：这些用户原来都属于同一个分组。
	 * 如果批量移动成功，再对这些用户的组信息进行更新。
	 * 考虑实时同步和预同步的问题，时间复杂度会有所不一样。
	 * 如果考虑实时同步，则一次批量移动的最佳体验是10~20个用户，批量移动26个用户用时5~6~6~8~9秒，12个用户需要8秒。
	 * 移动10342需要花时23分钟，平均1秒移动用户7.5个人。
	 * 超过50个用户，非实时同步最佳。
	 * @param array $einfo 企业信息
	 * @param array $openidlist 要移动的用户列表
	 * @param number $group_id 要移动到的组编号
	 * @return array $batchmoveresult 批量移动用户的结果。
	 */
	public function batchMoveUserToGroup($einfo = NULL, $openidlist = NULL, $group_id = 0) {
		$batchmoveresult = array (); // 批量移动用户到某分组的结果
		if (! empty ( $openidlist )) {
			$usernum = count ( $openidlist ); // 统计要移动的用户数量
			$movelist = array(); // 要移动的用户与组信息列表（二维数组）
			for($i = 0; $i < $usernum; $i ++) {
				$movelist [$i] = array(
						'openid' => $openidlist [$i],
						'to_groupid' => $group_id
				);
			} // 打包移动用户信息
			$k = 0;
			$this->weixin = new WeactWechat ( $einfo ); // 实例化微信类对象
			$queryinfo = $this->weixin->queryUserGroup( $openidlist [$k ++] ); // Step1：该用户原来所在分组
			while( $queryinfo && $k < $usernum ) {
				$queryinfo = $this->weixin->queryUserGroup ( $openidlist [$k ++] ); // Step1-1：该用户原来所在分组
			} // 某用户没关注或者查询失败了会出现errmsg，成功只会返回groupid，或者查询次数超过人数了（全部人都取消关注了，也要防止死循环 $k < $usernum）
			$batchmoveresult = $this->weixin->batchMoveUserToGroup ( $movelist ); // Step2：发送请求批量移动用户
			
			if (intval ( $queryinfo ['groupid'] ) != $group_id) {
				// 如果要批量移动过去的组和原来的组id不同才去移动，否则直接过
				$batchmoveresult = $this->weixin->batchMoveUserToGroup ( $movelist ); // Step2：发送请求批量移动用户
				$successnum = 0; // 统计总的移动成功数量
				for($i = 0; $i < $usernum; $i ++) {
					if($batchmoveresult [$i] ['errcode'] == 0) {
						$successnum ++;
						$this->syncModifyUserGroup ( $einfo, $openidlist [$i], $group_id );
					} // 如果这批次移动中，该次移动接口调用成功，就更新移动成功用户的分组信息，并把总移动用户成功数量+1
				}
				if ($successnum) {
					$this->syncMinusGroupNum ( $einfo, $queryinfo ['groupid'], $successnum ); // 继续对wechatusergroup表中的原来分组数量减少，传入数量进行重载
					$this->syncPlusGroupNum ( $einfo, $group_id, $successnum ); // 对wechatusergroup表中的新分组数量增加，传入数量进行重载
				} // 如果移动成功，wechatusergroup表组内的成员数量也要更新
			} else {
				$batchmoveresult = true; // 直接不用移动(这些用户原来就在这个组里)
			}
		}
		return $batchmoveresult;
	}
	
	/**
	 * 微信平台新更新的批量移动用户到某分组函数。
	 * @param array $einfo 企业信息
	 * @param array $openidlist 要批量移动的微信用户openid列表
	 * @param number $group_id 要移动到的用户分组编号
	 * @return boolean $batchmoveresult 批量移动微信用户结果
	 */
	public function newBatchMoveUserToGroup($einfo = NULL, $openidlist = NULL, $group_id = 0) {
		$this->weixin = new WeactWechat ( $einfo );
		$batchmoveresult = $this->weixin->newBatchMoveUserToGroup ( $openidlist, $group_id ); // 微信平台新接口，批量移动微信用户到某分组
		if ($batchmoveresult) {
			$allgroupinfo = $this->weixin->queryAllGroup (); // 考虑到可能用户来自不同组，不再一一对其进行同步，此处选择再调用queryAllGroup进行用户数量同步。
			$syncresult = $this->syncWeChatUserGroupInfo ( $einfo, $allgroupinfo ); // 先进行本地同步公众号分组，形参是标准的微信返回数据解析后的数组
			$usersync = $this->syncBatchUserGroup ( $einfo, $openidlist, $group_id ); // 再批量同步微信用户表自己的分组
		}
		return $batchmoveresult;
	}
	
	/**
	 * 删除分组，该接口由wlk于2015年9月20日补上
	 * 删除用户分组接口deleteGroup。
	 * 如果删除分组成功，立刻调用syncDeleteGroup同步分组函数（对于要删除的分组，前提是已经保证该分组下没有客户了）。
	 * @param array $einfo 企业信息
	 * @param string $group_name 要删除的分组名
	 * @return array $deletegroupresult 删除分组的结果(返回php标准数组，而不是stdClass。)。 
	 */
	public function deleteGroup($einfo = NULL,  $group_id = ''){
		$this->weixin = new WeactWechat ( $einfo );
		$deletegroupresult = $this->weixin->deleteGroup ($group_id ); // 微信平台新接口，批量移动微信用户到某分组
		if ($deletegroupresult) {
			$this->syncDeleteGroup ( $einfo, $deletegroupresult ); // 调用本类的对本地数据库同步的函数
		}
		return $deletegroupresult;
	}
	
	
	/**
	 * 修改某个用户的备注名。
	 * 对于返回成功（errcode == 0）的接口，要做一层判断，调用成功了才去同步本地。
	 * @param array $einfo 企业信息
	 * @param string $openid 用户微信号
	 * @param string $remarkname 备注名
	 * @return boolean $modifyresult 修改成功与否
	 */
	public function modifyUserRemarkName($einfo = NULL, $openid = '', $remarkname = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$modifyresult = $this->weixin->modifyUserRemark ( $openid, $remarkname );
		if ($modifyresult) {
			$syncresult = $this->syncWeChatUserRemark ( $einfo, $openid, $remarkname ); // 如果修改成功就执行同步
		}
		return $modifyresult;
	}
	
	/**
	 * 获得微信用户数据，已调通，已同步。
	 *
	 * @param array $einfo 企业信息
	 * @param string $open_id 用户微信号
	 * @return array $userinfo 返回获得的用户信息
	 */
	public function getUserInfo($einfo = NULL, $open_id = '') {
		$this->weixin = new WeactWechat ( $einfo ); // 新建微信接口类
		$userinfo = $this->weixin->getUserInfo ( $open_id ); // 获取用户信息
		if ($userinfo) {
			$syncresult = $this->syncWeChatUserInfo ( $einfo, $userinfo ); // 同步微信用户信息
		}
		return $userinfo;
	}
	
	/**
	 * 批量获取微信用户数据函数，推荐传入的第二个形参$openidlist在10~20条左右，对用户体验度最好。
	 * @param array $einfo 企业信息
	 * @param array $openidlist 微信用户openid列表
	 * @return array $userinfolist 从微信端最新更新的用户数据
	 */
	public function batchWeChatUserInfo($einfo = NULL, $openidlist = NULL) {
		$userinfolist = array();
		if(! empty ( $openidlist )) {
			$this->weixin = new WeactWechat ( $einfo ); // 实例化微信类对象
			$userinfolist = $this->weixin->batchGetUserInfo ( $openidlist );
		}
		return $userinfolist;
	}
	
	/**
	 * 获取公众号关注者用户列表。
	 * @param array $einfo 企业信息
	 * @return array $allsubscriber 所有的关注者
	 */
	public function allSubscriber($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微信类对象
		$allsubscriber = $this->weixin->getAllSubscriber (); // 获取所有关注者
		return $allsubscriber;
	}
	
	/**
	 * 拓展：获得是否已关注
	 *
	 * @param array $einfo
	 *        	企业信息
	 * @param string $open_id
	 *        	用户微信号
	 * @return boolean $subsriberesult 返回是否关注
	 */
	public function isSubscribed($einfo = NULL, $open_id = '') {
		$subsriberesult = false;
		if (! empty ( $open_id ))
			$userinfo = $this->getUserInfo ( $einfo, $open_id );
		if ($userinfo ['subscribe'] == 1)
			$subsriberesult = true;
		return $subsriberesult;
	}
	
	/**
	 * ----------------------功能：推广支持--------------------------
	 * 1、生成带参数的二维码；2、长链接转短链接接口。
	 */
	
	/**
	 * 生成带参数的二维码。
	 * @param array $einfo 要生成二维码的商家信息
	 * @param number $param 二维码的scene_id编号
	 * @param boolean $permanent 是否生成永久二维码，默认临时二维码
	 * @param number $expire 二维码时间
	 * @return array $qrcodeinfo 返回从微信服务器生成的二维码信息
	 */
	public function generateWeChatQRCode($einfo = NULL, $scene_id = 10000, $permanent = FALSE, $expire = 1800) {
		$this->weixin = new WeactWechat ( $einfo );
		$qrcodeinfo = $this->weixin->generateQRcode ( $scene_id, $permanent, $expire ); // 生成二维码信息
		return $qrcodeinfo; // 返回SDK生成的二维码信息
	}
	
	/**
	 * 2015/04/22微信平台更新二维码生成方式后的新生成二维码服务接口。
	 * @param array $einfo 要生成二维码的商家信息
	 * @param string $scene_id 二维码场景值
	 * @param boolean $permanent 是否要生成永久二维码
	 * @param number $expire 临时二维码有效时间（仅临时二维码需要此参数）
	 * @return array $qrcodeinfo 返回生成二维码的信息
	 */
	public function newGenerateQRCode($einfo = NULL, $scene_id = '', $permanent = FALSE, $expire = 604800) {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微信SDK类
		$qrcodeinfo = $this->weixin->newGenerateQRCode ( $scene_id, $permanent, $expire ); // 生成二维码信息
		return $qrcodeinfo; // 返回SDK生成的二维码信息
	}
	
	/**
	 * ----------------------功能：界面丰富--------------------------
	 * 自定义菜单：1、创建自定义菜单；2、查询自定义菜单；3、删除自定义菜单；4、自定义菜单事件（仅支持微信iPhone5.4.1以上版本）。
	 */
	
	/**
	 * 菜单被编码成$data，成为了字符串，最后函数返回，echo的也是字符串结果；
	 * 建表记录企业发送自定义菜单（这个步骤最好放到调用这个函数setmenu的结果后，如果成功就写入，不成功不用写入）。
	 * Author：赵臣升。
	 * CreateTime：2014/07/03 01:46:25.
	 * Database Read Count: twice.
	 * Function : Fully based on enterprise pre-set menu, auto send to weixin.
	 * 
	 * @param array $einfo 企业信息
	 * @return array $setresult 设置菜单是否成功
	 */
	public function setMenu($einfo = NULL) {
		// 缩写：customizedmenu→cm
		$cmmap = array (
				'e_id' => $einfo ['e_id'],
				'is_del' => 0
		);
		$cmtable = M ( 'customizedmenu' );
		$cmresult = $cmtable->where ( $cmmap )->order ( 'level asc, sibling_order asc' )->select (); // 先父后子，顺序从小到大

		$menudata = $this->assembleDBMenu ( $cmresult, true ); // 组装成微信需要的菜单格式（自带格式化级联菜单）

		$this->weixin = new WeactWechat ( $einfo ); // 实例化微动微信接口类
		$setresult = $this->weixin->setMenu ( $menudata );
		return $setresult; // 返回组装完毕的菜单数组信息 
	}
	
	/**
	 * 查询微动平台某企业的公众号菜单。
	 *
	 * @param array $einfo 企业信息
	 * @return array $menuinfo 企业使用的公众号的菜单
	 */
	public function queryMenu($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 新建微信类对象
		$menuinfo = $this->weixin->queryMenu (); // 查询微信自定义菜单
		return $menuinfo;
	}
	
	/**
	 * 删除商家自定义菜单。
	 * @param array $einfo 企业信息
	 */
	public function deleteMenu($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 新建微信类对象
		$deleteresult = $this->weixin->deleteMenu (); // 删除微信自定义菜单
		return $deleteresult;
	}
	
	/**
	 * ----------------------功能：素材管理--------------------------
	 * 上传下载多媒体文件。
	 * 图片（image）: 1M，仅支持JPG格式；语音（voice）：2M，播放长度不超过60s，仅支持AMR\MP3格式；
	 * 视频（video）：10MB，仅支持MP4格式；缩略图（thumb）：64KB，仅支持JPG格式）；1b、下载多媒体文件。
	 * 从微信上下载的多媒体，图片一般是jpg格式，语音一般是amr格式。
	 */
	
	/**
	 * 调用微信API上传多媒体获得media_id函数，
	 * 如果要回复微信用户多媒体，必须要经过这个函数返回media_id。
	 * @param string $einfo 企业信息
	 * @param string $mediapath 多媒体的绝对路径，必须是绝对路径
	 * @param string $mediatype 多媒体的类型，image|.jpg,voice|.amr|.mp3,video|.mp4,thumb|.jpg
	 * @return string $media_id 上传后得到的media_id
	 */
	public function uploadMedia($einfo = NULL, $mediapath = '', $mediatype = 'image') {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微动微信类对象
		$mediainfo = $this->weixin->uploadMedia ( $mediapath, $mediatype );
		return $mediainfo ['media_id'];
	}
	
	/**
	 * 调用微信API从微信服务器下载二维码图片。
	 * 特别注意：下载二维码文件名不能包含中文。
	 * @param array $einfo 企业信息
	 * @param string $ticket_id 二维码的ticket
	 * @param string $absolutepath 存放二维码图片的绝对路径
	 */
	public function downloadQRCode($einfo = NULL, $ticket_id = '', $absolutepath = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$downloadresult = $this->weixin->downloadQR ( $ticket_id, $absolutepath );
		return $downloadresult;
	}
	
	/**
	 * 调用微信API从微信服务器上下载多媒体文件。
	 * @param array $einfo 企业信息
	 * @param string $mediaid 要下载的多媒体MediaId（微信端的）
	 * @param string $savepath 要保存的多媒体路径（磁盘相对路径）
	 * @return array $downloadinfo 下载文件的信息
	 */
	public function downloadWechatMedia($einfo = NULL, $mediaid = '', $savepath = '') {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微信类对象
		$downloadinfo = $this->weixin->downloadMedia ( $mediaid, $savepath ); // 看似和下载二维码很相像，但是下载请求的路径地址不一样
		return $downloadinfo;
	}
	
	/**
	 * 上传永久图文素材。
	 * @param array $einfo 企业信息
	 * @param string $articleinfo 图文消息
	 * @return array $uploadnewsinfo 上传后的图文消息信息
	 * @property string type news 上传素材类型（图文）
	 * @property string media_id 图文消息编号
	 * @preperty number create_time 永久素材创建时间
	 */
	public function uploadPermanentNews($einfo = NULL, $articleinfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微信类对象
		$uploadnewsinfo = $this->weixin->addPermanentNews ( $articleinfo ); // 上传永久图文信息
		return $uploadnewsinfo;
	}
	
	/**
	 * 调用微信API上传永久多媒体获得media_id函数。
	 * @param array $einfo 企业信息
	 * @param string $mediapath 多媒体所在路径
	 * @param string $mediatype 多媒体类型
	 * @return string $media_id 多媒体上传完成获得的media_id编号 
	 */
	public function uploadPermanentMedia($einfo = NULL, $mediapath = '', $mediatype = 'image') {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微动微信类对象
		$mediainfo = $this->weixin->addPermanentMedia ( $mediapath, $mediatype );
		return $mediainfo ['media_id'];
	}
	
	/**
	 * 调用微信API、用media_id下载永久多媒体函数。
	 * @param array $einfo 企业信息
	 * @param string $mediaid 多媒体编号
	 * @param string $mediatype 多媒体类型
	 * @param string $savepath 多媒体保存路径
	 * @return array $mediainfo 下载的多媒体信息
	 */
	public function downloadPermanentMedia($einfo = NULL, $mediaid = '', $mediatype = 'image', $savepath = '') {
		$this->weixin = new WeactWechat ( $einfo ); // 实例化微动微信类对象
		$mediainfo = $this->weixin->getPermanentMedia ( $mediaid, $mediatype, $savepath ); // 下载多媒体接口
		return $mediainfo;
	}
	
	/**
	 * 微信长链接转换短链接。
	 */
	public function longURLTOShort($einfo = NULL, $longurl = '') {
		$this->weixin = new WeactWechat ( $einfo );
		$shorturl = $this->weixin->getShortURL ( $longurl );
		return $shorturl;
	}
	
	/**
	 * 调用微信API获取企业在微信公众平台永久素材的数量。
	 * @param array $einfo 企业信息
	 * @param string $type 永久多媒体素材类型
	 * @param number $offset 起始位置
	 * @param number $count 读取数量
	 * @return array $medialist 返回的列表
	 */
	public function permanentMediaList($einfo = NULL, $type = "image", $offset = 0, $count = 1) {
		$this->weixin = new WeactWechat ( $einfo ); // 新建微信服务层对象
		$medialist = $this->weixin->getPermanentMediaList ( $type, $offset, $count ); // 获取公众号多媒体资源
		return $medialist;
	}
	
	/**
	 * ----------------------功能：客服接口--------------------------
	 * 客服功能接口：
	 * 1、获取客服基本信息；2、获取在线客服接待信息；3、添加客服账号；
	 * 4、设置客服信息；5、上传客服头像；6、删除客服账号。
	 */
	
	/**
	 * 获取某公众号线上客服账号接口。
	 * @param array $einfo 企业信息
	 * @return array $customerservicelist 线上客服列表
	 */
	public function onlineServiceList($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 新建微信服务层对象
		$customerservicelist = $this->weixin->getOnlineServiceList (); // 获取在线客服列表
		return $customerservicelist;
	}
	
	/**
	 * 获取某公众号线上客服服务了多少顾客，若返回结果kf_online_list数组为空，则代表没有客服在线。
	 * @param array $einfo 企业信息
	 * @return array $statusinfo 客服服务信息
	 */
	public function onlineServiceStatus($einfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 微信类对象
		$statusinfo = $this->weixin->getOnlineServiceStatus ();
		return $statusinfo;
	}
	
	/**
	 * 添加微信公众号客服账号。
	 * @param array $einfo 企业信息
	 * @param array $accountinfo 客服账号信息，必须包含如下字段，而且密码必须md5加密
	 * @property string kf_account 客服人员登录账号
	 * @property string nickname 客服人员昵称
	 * @property string password 客服人员密码，必须md5加密
	 * @return array $addresult 添加客服账号的返回信息
	 */
	public function addServiceAccount($einfo = NULL, $accountinfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 微信类对象
		$addresult = $this->weixin->addOnlineServiceAccount ( $accountinfo );
		return $addresult;
	}
	
	/**
	 * 修改线上客服账号信息。
	 * @param array $einfo 企业信息
	 * @param array $modifyinfo 要修改的信息
	 * @property string kf_account 要修改的账号
	 * @property string nickname 要修改的客服昵称
	 * @property string password 要修改的客服密码
	 * @return array $modifyresult 修改的结果
	 */
	public function modifyOnlineServiceInfo($einfo = NULL, $modifyinfo = NULL) {
		$this->weixin = new WeactWechat ( $einfo ); // 微信接口类
		$modifyresult = $this->weixin->modifyOnlineServiceInfo ( $modifyinfo ); // 修改线上客服账号信息
		return $modifyresult;
	}
	
	/**
	 * 修改线上客服账号头像。
	 * @param array $einfo 企业信息
	 * @param string $account 客服账号
	 * @param string $mediapath 多媒体文件
	 * @return array $modifyresult 修改客服头像
	 */
	public function modifyOnlineServiceAvatar($einfo = NULL, $account = '', $mediapath = '' ) {
		$this->weixin = new WeactWechat ( $einfo ); // 微信类对象
		$modifyresult = $this->weixin->uploadOnlineServiceAvatar ( $account, $mediapath ); // 修改客服头像
		return $modifyresult;
	}
	
	/**
	 * 删除线上客服账号。
	 * @param array $einfo 企业信息
	 * @param string $account 要删除的线上客服账号信息
	 * @return array $deleteresult 删除线上客服账号信息结果
	 */
	public function delOnlineServiceAccount($einfo = NULL, $account = '') {
		$this->weixin = new WeactWechat ( $einfo ); // 微信类对象
		$deleteresult = $this->weixin->deleteOnlineServiceAccount ( $account ); // 删除账号信息
		return $deleteresult;
	}
	
	/**
	 * ----------------------Part2：微动与微信同步的Service--------------------------
	 */
	
	/**
	 * 特别注意：修复重大错误：2015/04/21 02:08:25.
	 * 对微信授权返回的微信用户信息进行同步函数。
	 * 本函数创建于合并授权登录与正常登录的晚上。
	 * 特别注意：只在关注情况下才去同步更新wechatuserinfo表，不关注就直接跳过更新了（原来关注，取消关注，信息可能已经获得，不想覆盖）。
	 * 检测wechatuserinfo表中有没有该用户记录:
	 * case a：如果有记录，检测用户是否关注公众号：如果关注则进行更新信息($syncOperated = true)；如果没有关注则跳过($syncOperated = false)；
	 * case b：如果没有记录，不管其是否关注，直接插入一条数据($syncOperated = true)。
	 *
	 * @param array $einfo
	 *        	企业信息
	 * @param array $wechatuserinfo
	 *        	微信用户信息
	 * @return boolean $syncOperated 是否执行了同步操作，可以选择性接收该返回值
	 */
	public function syncWeChatUserInfo($einfo = NULL, $wechatuserinfo = NULL) {
		$syncOperated = false; // 执行同步操作标记
		if (! empty ( $wechatuserinfo )) {
			$wechatusertable = M ( 'wechatuserinfo' );
			$checkexist = array (
					'enterprise_wechat' => $einfo ['original_id'],
					'openid' => $wechatuserinfo ['openid'],
					'is_del' => 0 
			);
			$existinfo = $wechatusertable->where ( $checkexist )->find (); // 找到微动存储的用户信息
			if ($existinfo && $wechatuserinfo ['subscribe']) {
				// $checkupdate列出比对字段与$wechatuserinfo比较（没变化就不更新，减少数据库读写）
				$checkupdate = array (
						'subscribe' => $existinfo ['subscribe'],
						'openid' => $existinfo ['openid'],
						'nickname' => $existinfo ['nickname'],
						'sex' => $existinfo ['sex'],
						'language' => $existinfo ['language'],
						'city' => $existinfo ['city'],
						'province' => $existinfo ['province'],
						'country' => $existinfo ['country'],
						'headimgurl' => $existinfo ['head_img_url'], // 特别注意：微动平台有下划线
						'subscribe_time' => $existinfo ['subscribe_time'],
						'remark' => $existinfo ['remark'] 
				);
				if ($checkupdate != $wechatuserinfo) {
					// 更新$existinfo的用户字段信息
					$existinfo = array (
							'user_info_id' => $existinfo ['user_info_id'], // 直接save必须给出主键
							'subscribe' => $wechatuserinfo ['subscribe'],
							'openid' => $wechatuserinfo ['openid'],
							'nickname' => $wechatuserinfo ['nickname'],
							'sex' => $wechatuserinfo ['sex'],
							'language' => $wechatuserinfo ['language'],
							'city' => $wechatuserinfo ['city'],
							'province' => $wechatuserinfo ['province'],
							'country' => $wechatuserinfo ['country'],
							'head_img_url' => $wechatuserinfo ['headimgurl'], // 特别注意：微动平台有下划线
							'subscribe_time' => $wechatuserinfo ['subscribe_time'],
							'remark' => $wechatuserinfo ['remark'] 
					);
					$wechatusertable->save ( $existinfo ); // 更新微信用户信息
					$syncOperated = true; // 同步完成
				}
			} else {
				$userinfo = array (
						'user_info_id' => md5 ( uniqid ( rand (), true ) ),
						'enterprise_wechat' => $einfo ['original_id'],
						'group_id' => 0, // 默认加入未分组
						'subscribe' => $wechatuserinfo ['subscribe'],
						'openid' => $wechatuserinfo ['openid'],
						'nickname' => $wechatuserinfo ['nickname'],
						'sex' => $wechatuserinfo ['sex'],
						'language' => $wechatuserinfo ['language'],
						'city' => $wechatuserinfo ['city'],
						'province' => $wechatuserinfo ['province'],
						'country' => $wechatuserinfo ['country'],
						'head_img_url' => $wechatuserinfo ['headimgurl'],
						'subscribe_time' => $wechatuserinfo ['subscribe_time'],
						'add_time' => time (),
						'remark' => $wechatuserinfo ['remark'] 
				);
				$syncOperated = $wechatusertable->add ( $userinfo ); // 插入新微信用户信息
			}
		}
		return $syncOperated;
	}
	
	/**
	 * 测试通过。
	 * 同步添加某分组函数，本函数由对外创建分组接口调用，在创建分组返回成功时，自动与本地数据库同步。
	 * @param string $einfo 企业信息
	 * @param string $newgroupinfo 新增加分组信息
	 * @return boolean $syncOperated 同步成功或失败
	 */
	public function syncAddNewGroup($einfo = NULL, $newgroupinfo = NULL) {
		$syncOperated = false; // 同步标记
		$newinfo = array (
				'groupinfo_id' => md5(uniqid(rand(),true)),
				'e_id' => $einfo ['e_id'],
				'group_id' => $newgroupinfo ['group'] ['id'],
				'group_name' => $newgroupinfo ['group'] ['name'],
				'count' => 0, // 新创建分组是没有用户的
				'add_time' => time()
		);
		$syncOperated = M ( 'wechatusergroup' )->add ( $newinfo );
		return $syncOperated;
	}
	
	/**
	 * 测试通过。
	 * 同步删除某分组函数，本函数由对外删除分组接口调用，在删除分组返回成功时，自动与本地数据库同步。
	 * @param string $einfo 企业信息
	 * @param string $delgroupinfo 要删除的分组信息
	 * @return boolean $syncOperated 同步成功或失败
	 */
	public function syncDeleteGroup($einfo = NULL, $delgroupinfo = NULL) {
		$syncOperated = false; // 同步标记
		$deleteinfo = array (
				'e_id' => $einfo ['e_id'],
				'group_id' => $delgroupinfo ['group'] ['id'],
				'group_name' => $delgroupinfo ['group'] ['name'],
		);
		$deleteoperation = array(
				'is_del' => 1
		);
		$syncOperated = M ( 'wechatusergroup' )->where ( $deleteinfo )->save($deleteoperation);
		return $syncOperated;
	}
	
	/**
	 * 对单个用户所在组信息进行同步的函数。
	 * @param array $einfo 企业信息
	 * @param string $openid 要同步的用户openid
	 * @param number $group_id 要同步的最新组信息
	 * @return boolean $syncOperated 同步成功或失败
	 */
	public function syncModifyUserGroup($einfo = NULL, $openid = '', $group_id = 0) {
		$syncOperated = false; // 同步标记
		if(! empty ( $openid )) {
			$wutable = M ( 'wechatuserinfo' );
			$syncmap = array(
					'enterprise_wechat' => $einfo ['original_id'],
					'openid' => $openid,
					'is_del' => 0
			);
			$syncOperated = $wutable->where( $syncmap )->setField( 'group_id', $group_id );
		}
		return $syncOperated;
	}
	
	/**
	 * 测试通过。
	 * 对企业微信用户表里的用户数量进行减少一个（某用户被移除该分组后调用）。
	 * 由moveUserToGroup和batchMoveUserToGroup主调。
	 * @param array $einfo 企业信息
	 * @param number $group_id 要减少数量的分组编号
	 * @param number $minuscount 减少的数量
	 * @return boolean $minusresult 减少数量的操作是否成功
	 */
	public function syncMinusGroupNum($einfo = NULL, $group_id = 0, $minuscount = 1) {
		$minusresult = 0; // 减少分组用户数量
		$minusmap = array(
				'e_id' => $einfo ['e_id'],
				'group_id' => $group_id,
				'is_del' => 0
		);
		$minusresult = M('wechatusergroup')->where($minusmap)->setDec( 'count', $minuscount );
		return $minusresult;
	}
	
	/**
	 * 测试通过。
	 * 对企业微信用户表里的用户数量进行增加一个（某用户被移入该分组后调用）。
	 * 由moveUserToGroup和batchMoveUserToGroup主调。
	 * @param array $einfo 企业信息
	 * @param number $group_id 要增加数量的分组编号
	 * @param number $pluscount 增加的个数
	 * @return boolean $plusresult 增加数量的操作是否成功
	 */
	public function syncPlusGroupNum($einfo = NULL, $group_id = 0, $pluscount = 1) {
		$plusresult = 0; // 增加分组用户数量
		$plusmap = array(
				'e_id' => $einfo ['e_id'],
				'group_id' => $group_id,
				'is_del' => 0
		);
		$plusresult = M('wechatusergroup')->where($plusmap)->setInc( 'count', $pluscount );
		return $plusresult;
	}
	
	/**
	 * 测试通过（庞大的同步函数）。
	 * 对调用微信查询所有分组接口成功后的同步操作。
	 * 第三稿，就第二稿中发现的致命错误进行修正，修正时间：2014/12/29 16:13:25.
	 * @author 原作者小万，黄昀第二稿，赵臣升最终修改组装。
	 * @param array $einfo 企业信息
	 * @param array $allgroupinfo 企业在微信平台所有查询的分组信息
	 * @return boolean $syncOperated 同步是否完成的标志
	 */
	public function syncWeChatUserGroupInfo($einfo = NULL, $allgroupinfo = NULL){
		$syncOperated = false; // 默认没有同步成功
		if(! empty ( $allgroupinfo )){
			
			// Step2：格式化微信数据
			for($i = 0; $i < count ( $allgroupinfo ['groups'] ); $i ++) {
				$array1 [$i] = array (
						'e_id' => $einfo ['e_id'],
						'group_id' => $allgroupinfo ['groups'] [$i] ['id'],
						'group_name' => $allgroupinfo ['groups'] [$i] ['name'],
						'count' => $allgroupinfo ['groups'] [$i] ['count']
				);
			}
			
			// Step3-1：查询本地微动数据
			$condition = array (
					'e_id' => $einfo ['e_id'],
					'is_del' => 0
			);
			$result = M ( 'wechatusergroup' )->where ( $condition )->select ();
			// Step3-2：格式化本地数据
			for($j = 0; $j < count ( $result ); $j ++) {
				$array2 [$j] = array (
						'e_id' => $result [$j] ['e_id'],
						'group_id' => $result [$j] ['group_id'],
						'group_name' => $result [$j] ['group_name'],
						'count' => $result [$j] ['count']
				);
			}
			// p($array1);p($array2);die;
			$resultgrouparray = autoPick ( $array1, $array2, 'group_id' );
			// p($resultgrouparray);die;
			
			$wutable = M ( 'wechatusergroup' ); // 需要用到的实例
			// Step4：开始运行同步算法
			// 处理删除的组信息（delete）
			$deletecount = 0; // 总的删除数量
			for($i = 0; $i < count ( $resultgrouparray ['del'] ); $i ++) {
				$deletepos = array (
						'e_id' => $resultgrouparray ['del'] [$i] ['e_id'],
						'group_id' => $resultgrouparray ['del'] [$i] ['group_id'],
						'is_del' => 0
				);
				$deletecount += $wutable->where ( $deletepos )->setField ( 'is_del', 1 );
			}
			// 处理修改的组信息(update)
			$updatecount = 0; // 总的更新组数量
			for($i = 0; $i < count ( $resultgrouparray ['update'] ); $i ++) {
				$singleupdatepos = array(
						'e_id' => $resultgrouparray ['update'] [$i] ['e_id'],
						'group_id' => $resultgrouparray ['update'] [$i] ['group_id'],
						'is_del' => 0
				); // 单次查找组信息
				$singleinfo = $wutable->where( $singleupdatepos )->find(); //找出信息
				// 更改其信息
				$singleinfo ['group_name'] = $resultgrouparray ['update'] [$i] ['group_name'];
				$singleinfo ['count'] = $resultgrouparray ['update'] [$i] ['count'];
				// 保存回数据库
				$updatecount += $wutable->where ( $singleupdatepos )->save ( $singleinfo );
			}
			// 处理增加的组信息（add）
			$addcount = 0; // 总得增加组数量
			for($i = 0; $i < count ( $resultgrouparray ['add'] ); $i ++) {
				$adddate [$i] = array (
						'groupinfo_id' => md5 ( uniqid ( rand (), true ) ),
						'e_id' => $einfo ['e_id'],
						'group_id' => $resultgrouparray ['add'] [$i] ['group_id'],
						'group_name' => $resultgrouparray ['add'] [$i] ['group_name'],
						'count' => $resultgrouparray ['add'] [$i] ['count'],
						'add_time' => time ()
				);
			}
			$addcount = M ( 'wechatusergroup' )->addAll ( $adddate );
			$syncOperated = true; // 同步完成
		}
		return $syncOperated;
	}
	
	/**
	 * 对本地数据库公众号的用户组名进行同步。
	 * @param array $einfo 企业信息
	 * @param number $group_id 公众号用户组id
	 * @param string $group_name 新修改的分组名
	 */
	public function syncUserGroupName($einfo = NULL, $group_id = 101, $group_name = '默认') {
		$syncresult = false; // 默认同步不成功
		$syncmap = array (
				'e_id' => $einfo ['e_id'],
				'group_id' => $group_id,
				'is_del' => 0
		);
		$syncresult = M ( 'wechatusergroup' )->where ( $syncmap )->setField ( 'group_name', $group_name );
		return $syncresult;
	}
	
	/**
	 * 同步单个移动某用户后的分组信息。
	 * @param array $einfo 企业信息
	 * @param string $openid 单个移动的微信用户openid
	 * @param number $groupid_now 该微信用户现在被移动到的分组编号
	 * @return boolean $syncresult 同步分组结果
	 */
	public function syncSingleUserGroup($einfo = NULL, $openid = '', $groupid_now = 0) {
		$syncmap = array (
				'enterprise_wechat' => $einfo ['original_id'],
				'openid' => $openid,
				'is_del' => 0
		);
		$syncresult = M ( 'wechatuserinfo' )->where ( $syncmap )->setField ( 'group_id', $groupid_now ); // 更新用户组号
		return $syncresult;
	}
	
	/**
	 * 同步批量移动用户后他们的分组信息。
	 * @param array $einfo 企业信息
	 * @param array $openidlist 批量移动的微信用户openid列表（格式参见微信接口需要格式）
	 * @param number $groupid_now 这些微信用户现在被移动到的分组编号
	 * @return boolean $syncresult 同步分组结果
	 */
	public function syncBatchUserGroup($einfo = NULL, $openidlist = NULL, $groupid_now = 0) {
		$openidstring = implode ( ",", $openidlist );
		$batchsyncmap = array (
				'enterprise_wechat' => $einfo ['original_id'],
				'openid' => array ( "in", $openidstring ), // openid列表
				'is_del' => 0
		);
		$syncresult = M ( 'wechatuserinfo' )->where ( $batchsyncmap )->setField ( 'group_id', $groupid_now ); // 更新用户组号
		return $syncresult;
	}
	
	/**
	 * 对本地微信用户表wechatuserinfo的备注名进行同步
	 * @param array $einfo 企业信息
	 * @param string $openid 用户微信号
	 * @param string $remarkname 备注名
	 * @return boolean $syncOperated 同步成功或失败
	 */
	public function syncWeChatUserRemark($einfo = NULL, $openid = '', $remarkname = '') {
		$syncOperated = false; // 同步成功与否的标记
		if (! empty ( $openid ) && ! empty ( $remarkname )) {
			$wutable = M ( 'wechatuserinfo' );
			$syncmap = array(
					'enterprise_wechat' => $einfo ['original_id'],
					'openid' => $openid,
					'is_del' => 0
			);
			$syncOperated = $wutable->where ( $syncmap )->setField ( 'remark', $remarkname );
		}
		return $syncOperated;
	}
	
	/**
	 * ----------------------Part3：微动菜单解码函数--------------------------
	 */
	
	/**
	 * 黑盒代码：将数据库的数组菜单组装成json需要的数组格式，能重载完成微信端的数据打包和微动页面视图的数据打包。
	 * @param array $dbmenulist 数据库原始菜单数据，格式必须是：先父后子、从小到大（本函数首先要变成本地父子级联菜单数组）
	 * @param boolean $towechat 是否打包成微信需要的json格式，默认是false打包给前台页面
	 * @return array $assemblemenulist 最终打包好的数组格式，能直接进行json_encode。
	 */
	public function assembleDBMenu($dbmenulist = NULL, $towechat = FALSE) {
		$assemblemenulist = array (); // 组装后的菜单列表
		if (! empty ( $dbmenulist )) {
			// Step1：现将数据库查询出来的菜单变成级联菜单
			$packagemenu = $this->formatCascadeMenu ( $dbmenulist );
			// Step2：再组装打包成json需要的格式
			if ($towechat) {
				// 如果需要将数据打包给微信，遍历层级菜单，进行微信打包
				foreach ($packagemenu as $wechatfatherkey => $wechatfathermenu) {
					$singlebutton = array (); // 单个父级菜单的button按钮
					if (! empty ( $wechatfathermenu ['children'] )) {
						// 该父级菜单下还有子级菜单
						$singlebutton ['name'] = $wechatfathermenu ['name']; // 只取父级菜单名字
						$singlebutton ['sub_button'] = array (); // 为它的孩子菜单开辟数组空间
						foreach ($wechatfathermenu ['children'] as $wechatchildkey => $wechatchildmenu) {
							// 先看类型
							$tempchildtype = $wechatchildmenu ['type']; // 类型
							$tempchildkey = $wechatchildmenu ['key']; // 事件
							$tempchildurl = $wechatchildmenu ['url']; // 地址
							if (empty ( $tempchildtype ) || ( empty ( $tempchildkey ) && empty ( $tempchildurl ) )) {
								// 容错处理：如果没有类型，或者key和url都空，则强制性规定菜单类型和事件
								$childbutton = array (
										'name' => $wechatchildmenu ['name'], // 名字
										'type' => "view", // 默认URL类型
										'url' => "http://www.we-act.cn/weact/Home/Index/index/e_id/" . $wechatfathermenu ['e_id'] // 默认跳转官网
								);
							} else {
								// 正常处理
								$childbutton = array (
										'name' => $wechatchildmenu ['name'], // 名字
										'type' => $wechatchildmenu ['type'] // 类型
								);
								// 再判断key和url要哪个
								if ($wechatchildmenu ['type'] == 'click') {
									$childbutton ['key'] = $wechatchildmenu ['key']; // 如果是点击推送图文类型的
								} else if ($wechatchildmenu ['type'] == 'view') {
									$childbutton ['url'] = $wechatchildmenu ['url']; // 如果是点击跳转URL类型的
								} else {
									// 其他新类型，暂留空，2015/03/26 20:14:25
								}
							}
							array_push ( $singlebutton ['sub_button'], $childbutton ); // 将子菜单加入父节点的孩子中
						}
					} else {
						$temptype = $wechatfathermenu ['type']; // 类型
						$tempkey = $wechatfathermenu ['key']; // 事件
						$tempurl = $wechatfathermenu ['url']; // 地址
						if (empty ( $temptype ) || ( empty ( $tempkey ) && empty ( $tempurl ) )) {
							// 容错处理：如果没有类型，或者key和url都空，则强制性规定菜单类型和事件
							$singlebutton = array (
									'name' => $wechatfathermenu ['name'], // 名字
									'type' => "view", // 默认URL类型
									'url' => "http://www.we-act.cn/weact/Home/Index/index/e_id/" . $wechatfathermenu ['e_id'] // 默认跳转官网
							);
						} else {
							// 正常处理
							
							// 该父级菜单下没有子级菜单
							$singlebutton = array (
									'name' => $wechatfathermenu ['name'], // 名字
									'type' => $wechatfathermenu ['type'] // 类型
							);
							// 再判断key和url要哪个
							if ($wechatfathermenu ['type'] == 'click') {
								$singlebutton ['key'] = $wechatfathermenu ['key']; // 如果是点击推送图文类型的
							} else if ($wechatfathermenu ['type'] == 'view') {
								$singlebutton ['url'] = $wechatfathermenu ['url']; // 如果是点击跳转URL类型的
							} else {
								// 其他新类型，暂留空，2015/03/26 20:14:25
							}
						}
					}
					array_push ( $assemblemenulist, $singlebutton ); // 将原始（或组装好的）父级菜单$singlebutton压入最终总菜单列表
				}
				// 发送给微信，还需要在菜单外边包装一层button
				$wrapmenu ['button'] = $assemblemenulist;
				unset ( $assemblemenulist );
				$assemblemenulist = $wrapmenu;
			} else {
				// 如果需要将数据打包给前台页面
				foreach ($packagemenu as $singlefatherkey => $singlefathermenu) {
					$tempfathermenu = array (
							'text' => $singlefathermenu ['name'], // 菜单名
							'id' => $singlefathermenu ['menu_id'], // 菜单编号
							'type' => $singlefathermenu ['type'], // 菜单类型
							'key' => $singlefathermenu ['key'], // 事件值（如果有）
							'url' => $singlefathermenu ['url'] // 跳转URL（如果有）
					);
					if (! empty ( $singlefathermenu ['children'] )) {
						// 如果孩子菜单不为空
						$tempfathermenu ['children'] = array (); // 为当前父级菜单数组children字段开辟孩子菜单数组
						foreach ($singlefathermenu ['children'] as $singlechildkey => $singlechildmenu) {
							$tempchildmenu = array (
									'text' => $singlechildmenu ['name'], // 菜单名
									'id' => $singlechildmenu ['menu_id'], // 菜单编号
									'type' => $singlechildmenu ['type'], // 菜单类型
									'key' => $singlechildmenu ['key'], // 事件值（如果有
									'url' => $singlechildmenu ['url'] // 跳转URL（如果有）
							);
							array_push ( $tempfathermenu ['children'], $tempchildmenu ); // 将当前孩子菜单push到父级菜单中
						}
					}
					array_push ( $assemblemenulist, $tempfathermenu ); // 将当前带孩子的父级菜单push到最终数组中
				}
			}
		}
		return $assemblemenulist;
	}
	
	/**
	 * 黑盒代码：将json数据解码后的二维数组菜单拆解成数据库需要的DB数组格式进行比对。
	 * @param array $remotemenulist 远程菜单数组
	 * @param boolean $fromwechat 从微信来的二维数组
	 * @return array $finalmenulist 最终打包好的数组格式，能直接进行json_encode。
	 */
	public function disassembleJsonMenu($remotemenulist = NULL, $fromwechat = FALSE) {
		$disassemblemenulist = array (); // 拆解成本地菜单后的菜单列表
		if (! empty ( $remotemenulist )) {
			if ($fromwechat) {
				// 如果从微信来的json解压数据
				$fatherorder = 1; // 父级菜单同级排序，从1开始
				foreach ($remotemenulist as $wechatfatherkey => $wechatfathermenu) {
					// Step1：要拆解的父级菜单，先将自己装入拆解菜单（先不考虑类型，放到后边做）
					$tempfather = array (
							'menu_id' => md5 ( uniqid ( rand (), true ) ), // 随机一个md5码作为主键，不然关联子菜单有问题
							'name' => $wechatfathermenu ['name'], // 菜单名字
							'level' => 0, // 父级菜单的level层级是0
							'father_menu_id' => "-1", // 父级菜单的father_menu_id永远是-1
							'sibling_order' => $fatherorder ++ // 父级菜单同级顺序
					);
					// 再将自己的孩子装入拆解菜单
					if (! empty ( $wechatfathermenu ['sub_button'] )) {
						// 如果发来的菜单本父级菜单有孩子菜单
						$tempfather ['type'] = null; // 本地：有孩子就无菜单类型
						$tempfather ['key'] = null; // 本地：有孩子就无菜单key
						$tempfather ['url'] = null; // 本地：有孩子就无菜单url
						array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单在孩子前加入拆解菜单中
						// 继续操作子级菜单
						$childorder = 1; // 子级菜单同级排序，也从1开始
						foreach ($wechatfathermenu ['sub_button'] as $wechatchildkey => $wechatchildmenu) {
							$tempchild = array (
									'name' => $wechatchildmenu ['name'], // 菜单名字
									'level' => 1, // 子级菜单的level层级是1
									'father_menu_id' => $tempfather ['menu_id'], // 子级菜单的father_menu_id不是-1（方便做层级关联）
									'type' => $wechatchildmenu ['type'], // 菜单类型
									'sibling_order' => $childorder ++ // 子级菜单同级顺序
							);
							if ($wechatchildmenu ['type'] == "click") {
								$tempchild ['key'] = $wechatchildmenu ['key']; // 图文click
								$tempchild ['url'] = null; // 无url
							} else if ($wechatchildmenu ['type'] == "view") {
								$tempchild ['url'] = $wechatchildmenu ['url']; // 菜单url
								$tempchild ['key'] = null; // 无key
							} else {
								// 新菜单类型，2015/03/27 02:10:25.
							}
							array_push ( $disassemblemenulist, $tempchild ); // 将子级菜单加入拆解菜单中
						}
					} else {
						// 如果父级菜单没有孩子菜单
						$tempfather ['type'] = $wechatfathermenu ['type']; // 注意：没有孩子就有type，有孩子就没有type
						if ($wechatfathermenu ['type'] == "click") {
							$tempfather ['key'] = $wechatfathermenu ['key']; // 图文click
							$tempfather ['url'] = null; // 无url
						} else if ($wechatfathermenu ['type'] == "view") {
							$tempfather ['url'] = $wechatfathermenu ['url']; // 菜单url
							$tempfather ['key'] = null; // 无key
						} else {
							// 新菜单类型，2015/03/27 02:10:25.
						}
						array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单加入拆解菜单中
					}
				}
			} else {
				// 如果从微动公众号菜单页面视图来的解压数据，直接解压成最原始的数据库格式二维数组（如果需要级联菜单，使用组装级联菜单函数）。
				$fatherorder = 1; // 父级菜单同级排序，从1开始
				foreach ($remotemenulist as $remotefatherkey => $remotefathermenu) {
					// Step1：要拆解的父级菜单，先将自己装入拆解菜单
					$tempfather = array (
							'name' => $remotefathermenu ['text'], // 菜单名字
							'menu_id' => $remotefathermenu ['id'], // 菜单编号
							'father_menu_id' => "-1", // 父级菜单是没有上级菜单编号的
							'level' => 0, // 父级菜单的level层级是0
							'type' => $remotefathermenu ['type'], // 菜单类型
							'key' => $remotefathermenu ['key'], // 菜单key（如果有）
							'url' => $remotefathermenu ['url'], // 菜单url（如果有）
							'sibling_order' => $fatherorder ++ // 父级菜单同级顺序
					);
					array_push ( $disassemblemenulist, $tempfather ); // 将父级菜单加入拆解菜单中
					// 再将自己的孩子装入拆解菜单
					if (! empty ( $remotefathermenu ['children'] )) {
						// 如果发来的菜单本父级菜单有孩子菜单
						$childorder = 1; // 子级菜单同级排序，也从1开始
						foreach ($remotefathermenu ['children'] as $remotechildkey => $remotechildmenu) {
							$tempchild = array (
									'name' => $remotechildmenu ['text'], // 菜单名字
									'menu_id' => $remotechildmenu ['id'], // 菜单编号
									'father_menu_id' => $tempfather ['menu_id'], // 当前子级菜单的父级菜单编号是当前外层循环菜单
									'level' => 1, // 子级菜单的level层级是1
									'type' => $remotechildmenu ['type'], // 菜单类型
									'key' => $remotechildmenu ['key'], // 菜单key（如果有）
									'url' => $remotechildmenu ['url'], // 菜单url（如果有）
									'sibling_order' => $childorder ++ // 子级菜单同级顺序
							);
							array_push ( $disassemblemenulist, $tempchild ); // 将子级菜单加入拆解菜单中
						}
					}
				}
			}
		}
		return $disassemblemenulist;
	}
	
	/**
	 * 黑盒代码：将微动本地数据库的二维数组菜单格式化成级联菜单的格式。
	 * 特别注意：使用本函数时，必须将父级菜单排在子级菜单前边，且同级菜单sibling_order从小到大（tp:->order ( 'level asc, sibling_order asc' )）。
	 * @param array $localmenulist 本地二维数组菜单列表
	 * @return array $cascademenulist 格式化后的级联菜单数组
	 */
	public function formatCascadeMenu($localmenulist = NULL) {
		$cascademenulist = array (); // 级联菜单数组
		if (! empty ( $localmenulist )) {
			$listnum = count ( $localmenulist ); // 计算总菜单数量
			for ($i = 0; $i < $listnum; $i ++) {
				// 因为按照level asc排序的，所以先取到的一定是父级菜单
				if ($localmenulist [$i] ['father_menu_id'] == "-1") {
					// 遍历到父级菜单，将其menu_id作为一个索引
					if (! is_array ( $cascademenulist [$localmenulist [$i] ['menu_id']])) {
						$cascademenulist [$localmenulist [$i] ['menu_id']] = $localmenulist [$i]; // 将父级菜单放进到他自己menu_id索引中
					}
				} else {
					// 遍历到子级菜单，将其放入父级菜单的数组中，但是注意顺序
					$fatherid = $localmenulist [$i] ['father_menu_id']; // 父级菜单menu_id，取出来方便后人理解
					$selforder = $localmenulist [$i] ['sibling_order']; // 自己在同级菜单中的顺序，取出来方便后人理解
					if (! is_array ( $cascademenulist [$fatherid] ['children'])) {
						$cascademenulist [$fatherid] ['children'] = array (); // 如果当前父级菜单还没有孩子菜单，开辟这个孩子菜单数组
					}
					array_push ( $cascademenulist [$fatherid] ['children'], $localmenulist [$i] ); // 放入这个数组中（这里数组下标从1开始，因为数据库同级顺序从1开始）
					//$cascademenulist [$fatherid] ['children'] [$selforder] = $localmenulist [$i]; // 放入这个数组中（这里数组下标从1开始，因为数据库同级顺序从1开始）
				}
			}
		}
		return $cascademenulist;
	}
	
	/**
	 * 黑盒代码：将formatCascadeMenu函数打包后的级联菜单拆解成本地数据库格式的菜单。
	 * @param array $cascademenulist 级联菜单
	 * @return array $localmenulist 本地菜单二维数组格式
	 */
	public function reverseCascadeMenu($cascademenulist = NULL) {
		$localmenulist = array (); // 本地菜单数组
		if (! empty ( $cascademenulist )) {
			foreach ($cascademenulist as $singlefathermenu) {
				$tempfather = $singlefathermenu;
				unset ( $tempfather ['children'] );
				array_push ( $localmenulist, $tempfather ); // 将父级菜单压入本地菜单中
				if (! empty ( $singlefathermenu ['children'] )) {
					// 如果该父级菜单还有孩子菜单，遍历该父节点的孩子菜单
					foreach ($singlefathermenu ['children'] as $singlechildmenu) {
						$singlechildmenu ['menu_id'] = md5 ( uniqid ( rand (), true ) ); // 赋予其孩子菜单主键
						array_push ( $localmenulist, $singlechildmenu ); // 将孩子菜单压入本地菜单中
					}
				}
			}
		}
		return $localmenulist;
	}
	
}
?>