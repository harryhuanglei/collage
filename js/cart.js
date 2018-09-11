$(function() {
	// fa fa-minus
	$(".fa.fa-minus").click(function(event) {
		var num = parseInt($(this).next('span').text());
		if (num > 1) {
			var rec_id = $(this).closest('.cart_goods').data('id');
			--num;
			$(this).next('span').text(num);
			changeGoodsNums(rec_id, num);
		}
		if(num==1){
	    $(".fa-minus").addClass("hui");
	}
	});
	// fa fa-plus
	$(".fa.fa-plus").click(function(event) {
		var num = parseInt($(this).prev('span').text());
		var rec_id = $(this).closest('.cart_goods').data('id');
		++num;
		$(this).prev('span').text(num);
		if(num>1){
	    $(".fa-minus").removeClass("hui");
	    }
		changeGoodsNums(rec_id, num);
	});

	$(".drop").click(function(event) {
		if (!confirm('确定删除吗？')) {
			return;
		}
		var rec_id = $(this).closest('.cart_goods').data('id');
		dropGoods(rec_id);
	});

	$(".checkbox").click(function(event) {
		var rec_id = $(this).closest('.cart_goods').data('id');
		var is_checked = $(this).attr('checked');
		is_checked = is_checked ? 1 : 0;
		checkGoods(rec_id, is_checked);
	});

	$("#ck_all").click(function(event) {
		var is_checked = $(this).attr('checked');
		is_checked = is_checked ? true : false;
		$.each($(".checkbox"), function(index, val) {
			$(".checkbox").eq(index).attr('checked', is_checked);
		});
		checkAll(is_checked);
	});
});


function changeGoodsNums(rec_id, number) {
	Ajax.call('flows.php', 'step=update_cart&rec_id=' + rec_id + '&number=' + number, changeGoodsNumsResponse, 'GET', 'JSON');
}

function dropGoods(rec_id) {
	Ajax.call('flows.php', 'step=drop_goods&rec_id=' + rec_id, dropGoodsResponse, 'GET', 'JSON');
}

function checkGoods(rec_id, is_checked) {
	Ajax.call('flows.php', 'step=check_goods&rec_id=' + rec_id + '&is_checked=' + is_checked, checkGoodsResponse, 'GET', 'JSON');
}

function checkAll(is_checked) {
	is_checked = is_checked ? 1 : 0;
	Ajax.call('flows.php', 'step=check_all&is_checked=' + is_checked, checkAllResponse, 'GET', 'JSON');
}

function changeGoodsNumsResponse(res) {
	if (res.error) {
		alert(res.message);
		$("#rec_" + res.rec_id).find('span.num').text(res.goods_number);
	} else {
		$("#rec_" + res.rec_id).find('span.num').text(res.goods_number);
		$("#rec_" + res.rec_id).find('font').text(res.subtotal);
		setResault(res.data);
	}
}

function dropGoodsResponse(res) {
	if (res.error) {
		alert(res.message);
	} else {
		$("#rec_" + res.rec_id).remove();
		setResault(res.data);
	}
}

function checkGoodsResponse(res) {
	if (res.error) {
		alert(res.message);
	} else {
		setResault(res.data);
	}
}

function checkAllResponse(res) {
	if (res.error) {
		alert(res.message);
	} else {
		setResault(res.data);
	}
}

function setResault(data) {
	$('.cart_foot').find('span.count').text(data.count);
	$('.cart_foot').find('span.total').text(data.amount);
}