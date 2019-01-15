// JavaScript Document

!function(){
	function getAnimationCss(num){
		var css = '';
		var MAX_DEG = Math.PI/4;
		var X_WIDTH = 100;
		var midNum = num / 2;
		var hasMid = (num % 4 != 0);
		if(midNum % 2 ==0){
			var numEqualParts = midNum / 2;//角度等分为多少份
			for(var i=1;i<=num;i++){
				var isLeft = (i%2 === 1);
				var isRight = (i%2 === 0);
				var isUp = (i <= midNum);
				var isDown = (i > midNum);

				if(isLeft && isUp){
					var deg = MAX_DEG * ( (numEqualParts - (i+1)/2 +1) / numEqualParts );
					var x = -X_WIDTH;
					var y = -X_WIDTH * Math.tan(deg);
				}else if(isLeft && isDown){
					var deg = MAX_DEG * (  (i - midNum)/(2*numEqualParts) );
					var x = -X_WIDTH;
					var y = X_WIDTH * Math.tan(deg);
				}else if(isRight && isUp){
					var deg = MAX_DEG * ( (numEqualParts - i/2 +1) / numEqualParts );
					var x = X_WIDTH;
					var y = -X_WIDTH * Math.tan(deg);	
				}else if(isRight && isDown){
					var deg = MAX_DEG * (  (i - midNum -1)/(2*numEqualParts) );
					var x = X_WIDTH;
					var y = X_WIDTH * Math.tan(deg);
				}
				css += '#fourGridMenu1 .fourGridMenuItem:nth-child('+ i +'){\
						-webkit-animation : fourBtnsIn'+ i +'-1 .5s ease-in-out;\
					}\
					@-webkit-keyframes fourBtnsIn'+ i +'-1{\
						0%{-webkit-transform:translate('+x+'px,'+y+'px);}\
						100%{-webkit-transform:translate(0,0);}\
					}';
			}
		}
		else{
			var numEqualParts = (midNum + 1 )/ 2 -1;//角度等分为多少份
			for(var i=1;i<=num;i++){
				var isLeft = (i%2 === 1);
				var isRight = (i%2 === 0);
				var isUp = (i < midNum);
				var isDown = (i > midNum+1);
				var isMid = (i==midNum)||(i==midNum+1);
				if(isLeft && isUp){
					var deg = MAX_DEG * ( (numEqualParts - (i+1)/2 +1) / numEqualParts );
					var x = -X_WIDTH;
					var y = -X_WIDTH * Math.tan(deg);
				}else if(isLeft && isDown){
					var deg = MAX_DEG * (  (i-midNum)/(2*numEqualParts) );
					var x = -X_WIDTH;
					var y = X_WIDTH * Math.tan(deg);
				}else if(isLeft && isMid){
					var x = -X_WIDTH;
					var y = 0; 
				}else if(isRight && isUp){
					var deg = MAX_DEG * ( (numEqualParts - i/2 +1) / numEqualParts );
					var x = X_WIDTH;
					var y = -X_WIDTH * Math.tan(deg);	
				}else if(isRight && isDown){
					var deg = MAX_DEG * (  (i -midNum -1)/(2*numEqualParts) );
					var x = X_WIDTH;
					var y = X_WIDTH * Math.tan(deg);
				}else if(isRight && isMid){
					var x = X_WIDTH;
					var y = 0;
				}
				css += '#fourGridMenu1 .fourGridMenuItem:nth-child('+ i +'){\
						-webkit-animation : fourBtnsIn'+ i +'-1 .5s ease-in-out;\
					}\
					@-webkit-keyframes fourBtnsIn'+ i +'-1{\
						0%{-webkit-transform:translate('+x+'px,'+y+'px);}\
						100%{-webkit-transform:translate(0,0);}\
					}';
			}//for
		}
		return css;
	}
	var css = getAnimationCss(4);	
	var style = document.createElement('style');
	style.innerHTML = css;
	document.getElementsByTagName('head')[0].appendChild(style);
}();