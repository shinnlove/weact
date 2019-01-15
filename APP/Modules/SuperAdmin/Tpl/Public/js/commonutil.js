// commonutil
var util = {
	// artDialog alert
	alert:function(context, _callback){
		dialog({
			title:"温馨提示",
			id:"util-alert",
			fixed: true,
			content: context,
			width:300,
			cancel: false,
			okValue: '确定',
			backdropOpacity:"0.5",
			ok: function () {
				if(_callback){
					_callback();
				}
			}
		}).showModal(); // 模态框方式弹出，有遮罩层
	},
	// artDialog confirm
	confirm:function(context, okCallback, cancelCallback){
		dialog({
			title:"温馨提示",
			id:"util-confirm",
			fixed: true,
			content: context,
			width:300,
			okValue: '确定',
			cancelValue:'取消',
			backdropOpacity:"0.5",
			ok: okCallback,
			cancel:cancelCallback
		}).showModal(); // 模态框方式弹出，有遮罩层
	}, 
}