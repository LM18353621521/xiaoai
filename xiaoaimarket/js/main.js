$(function(){
	// 出现公共的menu
	$(".banner-tits").mouseenter(function(){
		$(".banner-left").show();
	})
	$(".banner-tits").mouseleave(function(){
		$(".banner-left").hide();
	})
})