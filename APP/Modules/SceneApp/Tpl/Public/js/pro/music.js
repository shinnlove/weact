  window.onload=function(){
	var id = document.getElementById("loading");
    setTimeout(function(){document.body.removeChild(id)},1000);
    if($("#audio_btn").attr("url").indexOf("mp3")>1){
	  var url = $("#audio_btn").attr("url"); 
	  var auto = is_open=='on' ? 'autoplay' : '';
	  var html = '<audio loop  src="'+url+'" id="media" '+auto+' ></audio>';
	  setTimeout(function(){
		  $("#audio_btn").html(html);
		  $("#audio_btn").show().attr("class",is_open);
	 },500);
	  
	  $("#audio_btn").on('touchstart',function(){
		  var type = $("#audio_btn").attr("class");
		  var media = $("#media").get(0);
		  if(type=="on"){
		    media.pause(); 
			$("#audio_btn").attr("class","off");
		  }else{
			media.play();
			$("#audio_btn").attr("class","on"); 
	      }  
	  })
    }
	
} 
