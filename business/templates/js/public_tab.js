// $(document).ready(function(){
// 	$('.second_menu a span').click(function(){
// 		$(this).parents('.second_menu').css('display','block');
// 	});
// });

$(function() {
	var url = location.href;
	//http://wfx.hostadmin.com.cn/business/index.php?op=order&act=goods_order
	var tmp = url.split('&');
	var act = 'my_goods';
	$.each(tmp, function(index, val) {
		if (val.indexOf('act=') > -1) {
			act = val.replace('act=','');
		}
	});
	var id = $("#menu_"+act);
	id.closest('li').find('.second_menu').show();
	id.addClass('choise');
});