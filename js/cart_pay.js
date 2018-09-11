$(function() {
	$('select[name^=bonus]').change(function(event) {
		var suppliers_id = $(this).closest('div').data('suppliers_id');
		var bonus_id = $(this).val();
		changeBonus(suppliers_id, bonus_id);
	});

	$('input[name^=shipping]').change(function(event) {
		var suppliers_id = $(this).closest('div').data('suppliers_id');
		var express_id = $(this).data('express');
		var shipping_id = $(this).val();
		selectShipping(suppliers_id, shipping_id, express_id);
		var code = $(this).data('code');

		if (code == 'cac') {
			$(this).closest('div').next('div').show();
		}
		else
		{
			$(this).closest('div').next('div').hide();
			$('input[name^=point_id]').attr('checked', false);
		}
	});	

	$('input[name^=point_id]').change(function(event) {
		var suppliers_id = $(this).closest('div').data('suppliers_id');
		var point_id = $(this).val();
		selectPoint(suppliers_id, point_id);
	});	
});

function changeBonus(suppliers_id, bonus_id)
{
	Ajax.call('flows.php', 'step=change_bonus&suppliers_id=' + suppliers_id + '&bonus_id=' + bonus_id, changeBonusResponse, 'GET', 'JSON');
}

function selectShipping(suppliers_id, shipping_id, express_id)
{
	Ajax.call('flows.php', 'step=select_shipping&suppliers_id=' + suppliers_id + '&shipping_id=' + shipping_id + '&express_id=' + express_id, selectShippingResponse, 'GET', 'JSON');
}

function selectPoint(suppliers_id, point_id)
{
	Ajax.call('flows.php', 'step=select_point&suppliers_id=' + suppliers_id + '&point_id=' + point_id, selectPointResponse, 'GET', 'JSON');
}

function changeBonusResponse(res)
{
	if (res.error) {
		alert(res.message);
	} else {
		setResault(res.data);
	}
}

function selectShippingResponse(res)
{
	if (res.error) {
		alert(res.message);
	} else {
		setResault(res.data);
	}
}

function selectPointResponse(res)
{
	if (res.error) {
		alert(res.message);
	} else {
		//setResault(res.data);
	}
}

function setResault(data) {
	$('.total').find('span.shipping_fee').text(data.shipping_fee);
	$('.total').find('span.amount_fee').text(data.amount_fee);
}