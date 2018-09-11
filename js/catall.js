$(function () {
    $(".leftmenu").niceScroll({ cursorwidth: 0,cursorborder:0 });
	var array=new Array();
	$('.leftmenu li').each(function(){ 
		array.push($(this).position().top-45);
	});
	
	$('.leftmenu li').click(function() {
		var index=$(this).index();
		$('.leftmenu').delay(200).animate({scrollTop:array[index]},300);
		$(this).addClass('cur').siblings().removeClass();
		$('.content dl').eq(index).show().siblings().hide();
        $('.content').scrollTop(0);
	});
});



