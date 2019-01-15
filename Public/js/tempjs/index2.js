/**
 * 
 * 
 */
$(function(){
		var allNavCount = 9;
		for(i=0;i<=allNavCount/3;i++)
		{
				var layerstart = '<div class="layer" style="background: -webkit-gradient(linear, 0 0, 0 100%, from(#01acc6), to(#007fa8));">';
				var layerend = '</div>';
				var ulstart = '<ul>';
				var ulend = '</ul>';
				var navblock = layerstart+ulstart;
				var rowCount = 3;
				if(i==allNavCount/3){
					rowCount = allNavCount%3;
				}
				for(j=1;j<=rowCount;j++)
				{	
					var navli = '<li class="normal_li';
					if(j%3 != 0){
						navli += ' right_li';
					}else{
						navli += ' last_li';	
				}
				navli += '"><a href="#"><div class="menubtn">';
				navli += '<div class="menuimg"><img src="__PUBLIC__/images/nav_1.png" /></div>';
				navli += '<div class="menutitle" style="color:">';
				navli += 'ddkddd';
				navli += '"</div></div></a></li>';
					
				navblock += navli;
			}
			navblock += ulend+layerend;
			$("#content").append(navblock);
		}
}
);