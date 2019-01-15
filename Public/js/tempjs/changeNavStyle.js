// JavaScript Document

/* This js helps the enterprises changes their second navigator list style.
 * Each style is functioning by jQuery click event.
 * Author : zhaochensheng.
 * Time : 2014/05/18 20:05:16
 */

var oFixed = $('.fixed');
var oUititle = $('.ui-title');
var oUibtnrighthome = $('.ui-btn-right_home');
var oPopmenuafter = $('#popmenu:after');
var oUibtnleftpre = $('.ui-btn-left_pre');
var oUibtnright = $('.ui-btn-right');

$(function(){
	/*0.默认天蓝色风格*/
	$('#default_style').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear, left top, left bottom, from(#6DACED), to(#3C63B9))");
		oFixed.css("box-shadow","0 1px 2px 0 rgba(0, 0, 0, 0.25)");
		oFixed.css("border-bottom","1px solid #305196");
		//Step2
		oUititle.css("text-shadow","0 1px #3C63B9");
		oUititle.css("color","#FFF");
		//Step3，暂时没用
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*1.暗红色风格*/
	$('#style1').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#D80000),to(#660000))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #520000");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*2.深红色风格*/
	$('#style2').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#DF0300),to(#9a0000))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #750200");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*3.鲜红色风格，从这里开始还没做*/
	$('#style3').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#E70303),to(#cc0000))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #960000");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*4.粉红色风格*/
	$('#style4').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#EE5C5C),to(#cc3333))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #A82B2B");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*5.玫红色风格*/
	$('#style5').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FF8BB7),to(#ea4c88))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #CE4277");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*6.浅紫色风格*/
	$('#style6').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#D66AD6),to(#993399))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #7C297C");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*7.紫色风格*/
	$('#style7').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#9962CF),to(#663399))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #542880");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*8.宝蓝色风格*/
	$('#style8').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#6161CF),to(#333399))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #25257C");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*9.深蓝色风格*/
	$('#style9').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#318FEC),to(#0066cc))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #0558AA");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*10.蓝色风格*/
	$('#style10').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#5DD1F8),to(#0099cc))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #0483AD");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*11.湖蓝色风格*/
	$('#style11').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#83F1F1),to(#66cccc))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #40B4B4");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*12.浅绿色风格*/
	$('#style12').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#A8F56B),to(#77cc33))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #66B428");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*13.绿色风格*/
	$('#style13').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#92D60B),to(#669900))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #598304");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*14.深绿色风格*/
	$('#style14').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#64BD0C),to(#336600))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #2A5301");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*15.黄绿色风格*/
	$('#style15').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#A0A002),to(#666600))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #535301");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*16.草黄色风格*/
	$('#style16').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#D3D303),to(#999900))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #868600");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*17.浅黄色风格*/
	$('#style17').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#EEEE4D),to(#cccc33))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #A7A712");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*18.柠檬黄色风格*/
	$('#style18').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FCFC00),to(#E5E600))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.3)");
		oFixed.css("border-bottom","1px solid #B9B905");
		//Step2
		oUititle.css("text-shadow","0");
		oUititle.css("color","#999");
		//Step3
		oUibtnrighthome.css("background","url(images/home.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*19.黄色风格*/
	$('#style19').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FFEC61),to(#ffcc33))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.2)");
		oFixed.css("border-bottom","1px solid #DBB131");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*20.橙黄色风格*/
	$('#style20').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FFE000),to(#ff9900))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.3)");
		oFixed.css("border-bottom","1px solid #E28B09");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*21.橙色风格*/
	$('#style21').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FFC200),to(#ff6600))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #DB5800");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*22.棕黄色风格*/
	$('#style22').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#FD9D67),to(#cc6633))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #B45221");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*23.浅棕色风格*/
	$('#style23').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#DA9E62),to(#996633))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #805122");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*24.棕色风格*/
	$('#style24').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#B88B61),to(#845634))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #634126");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*25.深棕色风格*/
	$('#style25').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#AC5600),to(#663300))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #472400");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.2)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*26.暗黑色风格*/
	$('#style26').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#3A3A3D),to(#151516))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.5)");
		oFixed.css("border-bottom","1px solid #000000");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.8)");
		oUititle.css("color","#B4B4B4");
		//Step3
		oUibtnrighthome.css("background","url(images/home.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#B4B4B4 transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*27.深灰色风格*/
	$('#style27').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#697077),to(#3F434E))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #000000");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.8)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*28.浅灰色风格*/
	$('#style28').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#A0A0A0),to(#666))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #777");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.8)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*29.冷灰色风格*/
	$('#style29').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#CFCDCD),to(#999999))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.4)");
		oFixed.css("border-bottom","1px solid #8B8B8B");
		//Step2
		oUititle.css("text-shadow","0 1px rgba(0, 0, 0, 0.8)");
		oUititle.css("color","#FFF");
		//Step3
		oUibtnrighthome.css("background","url(images/home2.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#ffffff transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre2.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh2.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
	/*30.纯白色风格*/
	$('#style30').click(function(){
		//Step1
		oFixed.css("background","-webkit-gradient(linear,left top,left bottom,from(#fff),to(#f9f9f9))");
		oFixed.css("box-shadow","0 1px 5px rgba(0, 0, 0, 0.15)");
		oFixed.css("border-bottom","1px solid #D1D1D1");
		//Step2
		oUititle.css("text-shadow","0");
		oUititle.css("color","#999");
		//Step3
		oUibtnrighthome.css("background","url(images/home.png) no-repeat center center");
		oUibtnrighthome.css("background-size","24px auto");
		//Step4
		oPopmenuafter.css("border-color", "#999 transparent");
		//Step5
		oUibtnleftpre.css("background", "url(images/pre.png) no-repeat center center");
		oUibtnleftpre.css("background-size", "24px auto");
		//Step6
		oUibtnright.css("background", "url(images/Refresh.png) no-repeat center center");
		oUibtnright.css("background-size", "28px auto");
	});
});