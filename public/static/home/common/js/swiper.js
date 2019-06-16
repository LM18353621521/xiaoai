// swiperb
var imgs_div=document.getElementById("imgs");
//获取到图片轮播的ul对象数组
var imgsUl=imgs_div.getElementsByTagName("ul")[0];
//上一个
var prious=document.getElementById("preous");
//下一个
var next =document.getElementById("next");
var timer;
var animTimer;
var index=1;
play();
prious.onclick=function(){
	initImgs(imgsUl,index,1020,timer,animTimer);
	index-=1;
	if(index<1){
		index=2;
	}
	animate(imgsUl,1020,1020,2040,animTimer);
}
next.onclick=function(){
	initImgs(imgsUl,index,1020,timer,animTimer);
	index+=1;
	if(index>2){
		index=1;
	}
	animate(imgsUl,-1020,1020,2040);
}	

// // swiperc
// var imgs_divc=document.getElementById("imgsc");
// //获取到图片轮播的ul对象数组
// var imgsUlc=imgs_div.getElementsByTagName("ul")[0];
// //上一个
// var priousc=document.getElementById("preousc");
// //下一个
// var nextc =document.getElementById("nextc");
// var timerc;
// var animTimerc;
// var indexc=1;
// play();
// priousc.onclick=function(){
// 	initImgs(imgsUlc,indexc,1020,timerc,animTimerc);
// 	indexc-=1;
// 	if(indexc<1){
// 		indexc=2;
// 	}
// 	animate(imgsUlc,1020,1020,2040,animTimerc);
// }
// nextc.onclick=function(){
// 	initImgs(imgsUlc,indexc,1020,timerc,animTimerc);
// 	indexc+=1;
// 	if(indexc>2){
// 		indexc=1;
// 	}
// 	animate(imgsUlc,-1020,1020,2040);
// }	





// swiperaa
var imgs_divaa=document.getElementById("imgsaa");
//获取到图片轮播的ul对象数组
var imgsUlaa=imgs_divaa.getElementsByTagName("ul")[0];
//上一个
var priousaa=document.getElementById("preousaa");
//下一个
var nextaa =document.getElementById("nextaa");
var timeraa;
var animTimeraa;
var indexaa=1;
play();
priousaa.onclick=function(){
	initImgs(imgsUlaa,indexaa,943,timeraa,animTimeraa);
	indexaa-=1;
	if(indexaa<1){
		indexaa=2;
	}
	animate(imgsUlaa,943,943,1886,animTimeraa);
}
nextaa.onclick=function(){
	initImgs(imgsUlaa,indexaa,943,timeraa,animTimeraa);
	indexaa+=1;
	if(indexaa>2){
		indexaa=1;
	}
	animate(imgsUlaa,-943,943,1886);
}


// swiperab
var imgs_divab=document.getElementById("imgsab");
//获取到图片轮播的ul对象数组
var imgsUlab=imgs_divab.getElementsByTagName("ul")[0];
//上一个
var priousab=document.getElementById("preousab");
//下一个
var nextab =document.getElementById("nextab");
var timerab;
var animTimerab;
var indexab=1;
play();
priousab.onclick=function(){
	initImgs(imgsUlab,indexab,943,timerab,animTimerab);
	indexab-=1;
	if(indexab<1){
		indexab=2;
	}
	animate(imgsUlab,943,943,1886,animTimerab);
}
nextab.onclick=function(){
	initImgs(imgsUlab,indexab,943,timerab,animTimerab);
	indexab+=1;
	if(indexab>2){
		indexab=1;
	}
	animate(imgsUlab,-943,943,1886);
}


// swiperac
var imgs_divac=document.getElementById("imgsac");
//获取到图片轮播的ul对象数组
var imgsUlac=imgs_divac.getElementsByTagName("ul")[0];
//上一个
var priousac=document.getElementById("preousac");
//下一个
var nextac =document.getElementById("nextac");
var timerac;
var animTimerac;
var indexac=1;
play();
priousac.onclick=function(){
	initImgs(imgsUlac,indexac,943,timerac,animTimerac);
	indexac-=1;
	if(indexac<1){
		indexac=2;
	}
	animate(imgsUlac,943,943,1886,animTimerac);
}
nextac.onclick=function(){
	initImgs(imgsUlac,indexac,943,timerac,animTimerac);
	indexac+=1;
	if(indexac>2){
		indexac=1;
	}
	animate(imgsUlac,-943,943,1886);
}

// swiperad
var imgs_divad=document.getElementById("imgsad");
//获取到图片轮播的ul对象数组
var imgsUlad=imgs_divad.getElementsByTagName("ul")[0];
//上一个
var priousad=document.getElementById("preousad");
//下一个
var nextad =document.getElementById("nextad");
var timerad;
var animTimerad;
var indexad=1;
play();
priousad.onclick=function(){
	initImgs(imgsUlad,indexad,943,timerad,animTimerad);
	indexad-=1;
	if(indexad<1){
		indexad=2;
	}
	animate(imgsUlad,943,943,1886,animTimerad);
}
nextad.onclick=function(){
	initImgs(imgsUlad,indexad,943,timerad,animTimerad);
	indexad+=1;
	if(indexad>2){
		indexad=1;
	}
	animate(imgsUlad,-943,943,1886);
}



































function animate(obj,offset,sliderWidth,sliderWidthLength,timer){
	var newLeft=parseInt(obj.offsetLeft)+offset;
	if(newLeft>-sliderWidth){
		donghua(-sliderWidthLength,timer,obj);	
	}else if(newLeft<-sliderWidthLength){
		donghua(-sliderWidth,timer,obj);	
	}else{
		donghua(newLeft,timer,obj);
	}

}
function donghua(offset,timer,obj){
	clearInterval(timer);
	timer=setInterval(function(){
		obj.style.left=obj.offsetLeft+(offset-obj.offsetLeft)/10 + "px";
		if(obj.offsetLeft-offset<10&&obj.offsetLeft-offset>-10){//如果偏移量已经等于指定好的偏移量，则秦楚定时器
			obj.style.left=offset+"px";
			clearInterval(timer);
			//开启定时轮播
			play();			
		}
	},20);
}
function initImgs(obj,cur_index,sliderWidth,timer1,timer2){
	clearInterval(timer1);
	clearInterval(timer2);
	var off=cur_index*sliderWidth;
	obj.style.left=-off+"px";
}
function play(){
	timer=setInterval(function(){
		next.onclick();
	},2000)
}