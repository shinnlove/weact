//显示分享按钮
  function onBridgeReady(){
       document.addEventListener('WeixinJSBridgeReady', function onBridgeReady()
      {
        WeixinJSBridge.call('hideToolbar');
      });
       document.addEventListener('WeixinJSBridgeReady', function onBridgeReady()  {
         WeixinJSBridge.call('showOptionMenu');
         });
  }

  if (typeof WeixinJSBridge == "undefined"){
      if( document.addEventListener ){
          document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
      }else if (document.attachEvent){
          document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
          document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
      }
  }else{
      onBridgeReady();
  }
  onBridgeReady();
  //控制微信浏览器
  document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
    // 发送给好友
    WeixinJSBridge.on('menu:share:appmessage', function(argv) {
      WeixinJSBridge.invoke('sendAppMessage', {
        "appid": "wxdabfde3a0aad2361",
        "img_url": "http://www.we-act.cn/weact/Updata/images/201405291912250003/logo/square.png",
        "img_width": "160",
        "img_height": "160",
        "link": "http://www.we-act.cn/weact/index.php/Home/CustomView/showSimpleInfo/e_id/201405291912250003/nav_id/000000000000000331",
        "desc": "G5G6夏秋时装发布暨产品订货会\n(wifi下浏览更佳)",
        "title": "G5G6 激舞青春 激流未来"
      }, function(res) {
      _report('send_msg', res.err_msg);
      })
    });

    // 分享到朋友圈http://203.195.162.47/
    WeixinJSBridge.on('menu:share:timeline', function(argv) {
      WeixinJSBridge.invoke('shareTimeline', {
      "appid": "wxdabfde3a0aad2361",
        "img_url": "http://www.we-act.cn/weact/Updata/images/201405291912250003/logo/square.png",
        "img_width": "160",
        "img_height": "160",
        "link": "http://www.we-act.cn/weact/index.php/Home/CustomView/showSimpleInfo/e_id/201405291912250003/nav_id/000000000000000331",
        "desc": "G5G6夏秋时装发布暨产品订货会\n(wifi下浏览更佳)",
        "title": "G5G6 激舞青春 激流未来"
      }, function(res) {
        _report('timeline', res.err_msg);
      });
    });
  }, false);