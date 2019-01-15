define(function(require, exports) {
	var ItemBuy = {
	     init:function(){
	    	 var _this=this;
	    	 $("body").on("click","a[ev=attr]",this.handleAttr) 
	    	 .on("click","a[ev=photo]",function(){
	             var picList= $(this).attr("data-imgs").split("^");
	             _this.handleOpenImgs(picList);
	         }).on("click",".mod-subject__slider-image",function(){
	             var picList= $(this).attr("data-imgs").split("^");
	             _this.handleOpenImgs(picList);
	         });
	    	 
	     },		
	     handleAttr:function (){
	         
	     },
	     handleOpenImgs:function(picList){
	    	   WeixinJSBridge.invoke("imagePreview", {
	                    current: picList[0],
	                    urls: picList
	                })
	     },
	    
			
	};
	
	exports.ItemBuy = ItemBuy;
	
})