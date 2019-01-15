define('lib/QQBrowser', [], function(require, exports, module) {});
(function(){var ua=navigator.userAgent;var isAndroid=/android/ig.test(ua)&&/mqq/ig.test(ua);if(typeof mtt!=="undefined"){isAndroid=true}var isIos=/iphone|ipod|ios/ig.test(ua)&&/mqq/ig.test(ua);function getUA(){if(isAndroid){var info=x5.android.getBrowserParam();if(info){info=eval("("+info+")");var qua=info.qua+"";qua=qua.match(/([0-9\.]+)/ig,"");if(qua&&qua.length>0){return qua[0]}}}try{var ua=navigator.userAgent;var reg=/MQQBrowser\/(\d{2})/;var regRemoveDot=/\./g;ua=ua.replace(regRemoveDot,"");var res=reg.exec(ua);if(res&&res.length>1){return res[1]}return undefined}catch(e){return undefined}}var x5={commandQueue:[],commandQueueFlushing:false,resources:{base:!0}};x5.callbackId=0;x5.callbacks={};x5.callbackStatus={NO_RESULT:0,OK:1,CLASS_NOT_FOUND_EXCEPTION:2,ILLEGAL_ACCESS_EXCEPTION:3,INSTANTIATION_EXCEPTION:4,MALFORMED_URL_EXCEPTION:5,IO_EXCEPTION:6,INVALID_ACTION:7,JSON_EXCEPTION:8,ERROR:9};x5.createBridge=function(){var bridge=document.createElement("iframe");bridge.setAttribute("style","display:none;");bridge.setAttribute("height","0px");bridge.setAttribute("width","0px");bridge.setAttribute("frameborder","0");document.documentElement.appendChild(bridge);return bridge};x5.exec=function(successCallback,errorCallback,service,action,options){var callbackId=null;var command={className:service,methodName:action,options:{},arguments:[]};if(successCallback||errorCallback){callbackId=service+x5.callbackId++;x5.callbacks[callbackId]={success:successCallback,fail:errorCallback}}if(callbackId!=null){command.arguments.push(callbackId)}for(var i=0;i<options.length;++i){var arg=options[i];if(arg==undefined||arg==null){}else{if(typeof(arg)=="object"){command.options=arg}else{command.arguments.push(arg)}}}x5.commandQueue.push(JSON.stringify(command));if(x5.commandQueue.length==1&&!x5.commandQueueFlushing){if(!x5.bridge){x5.bridge=x5.createBridge()}x5.bridge.src="mtt:"+service+":"+action}};x5.getAndClearQueuedCommands=function(){var json=JSON.stringify(x5.commandQueue);x5.commandQueue=[];return json};x5.callbackSuccess=function(callbackId,args){if(x5.callbacks[callbackId]){if(args.status===x5.callbackStatus.OK){try{if(x5.callbacks[callbackId].success){x5.callbacks[callbackId].success(args.message)}}catch(e){console.log("Error in success callback: "+callbackId+" = "+e)}}if(!args.keepCallback){delete x5.callbacks[callbackId]}}};x5.callbackError=function(callbackId,args){if(x5.callbacks[callbackId]){try{if(x5.callbacks[callbackId].fail){x5.callbacks[callbackId].fail(args.message)}}catch(e){console.log("Error in error callback: "+callbackId+" = "+e)}if(!args.keepCallback){delete x5.callbacks[callbackId]}}};x5.ios=x5.ios||{};x5.ios.openType=function(succCallback,errCallback){x5.exec(succCallback,errCallback,"app","getAppShowType",[])};x5.ios.share=function(option,suc,err){x5.exec(suc,err,"app","share",[option])};x5.android=x5.android||{};x5.android.getBrowserParam=function(){var browserparam="";if(typeof mtt!=="undefined"){try{if(mtt.getBrowserParam){browserparam=mtt.getBrowserParam()+""}return browserparam}catch(e){return""}}else{return""}};if(window.qb_bridge==undefined){var version=getUA();if(version>=50){window.qb_bridge={nativeExec:function(service,action,callbackId,argsJson){return prompt(argsJson,"mtt:["+[service,action,callbackId]+"]")}}}else{console.log("Not a qq browser or version too old")}}qb_bridge.callbackId=Math.floor(Math.random()*2000000000);qb_bridge.callbacks={};qb_bridge.exec=function(success,fail,service,action,args){var callbackId=service+qb_bridge.callbackId++,argsJson=args?JSON.stringify(args):"";if(success||fail){qb_bridge.callbacks[callbackId]={success:success,fail:fail}}var ret=qb_bridge.nativeExec(service,action,callbackId,argsJson);if(ret==="true"){return true}else{if(ret==="false"){return false}else{return ret}}};qb_bridge.callbackFromNative=function(callbackId,args){var callback=qb_bridge.callbacks[callbackId];var argsJson=JSON.parse(args);if(callback){if(argsJson.succ){callback.success&&callback.success(argsJson.msg)}else{callback.fail&&callback.fail(argsJson.msg)}if(!argsJson.keep){delete qb_bridge.callbacks[callbackId]}}};x5.android.openType=function(suc,err){var ret=qb_bridge.exec(null,null,"qb","openType",{});var obj={};try{obj=JSON.parse(ret);suc&&suc(obj)}catch(e){err&&err()}};x5.android.share=function(option,suc,err){qb_bridge.exec(suc,err,"qb","share",option)};window.T5Kit={};for(var i in x5){T5Kit[i]=x5[i]}x5.getAppShowType=function(suc,err){if(!isAndroid&&!isIos){err&&err()}else{if(isAndroid){x5.android.openType(suc,err)}else{x5.ios.openType(suc,err)}}};x5.share=function(option,suc,err){if(!isAndroid&&!isIos){err&&err()}else{if(isAndroid){x5.android.share(option,suc,err)}else{x5.ios.share(option,suc,err)}}};x5.getQQBrowerVer=getUA;window.x5=x5})();
