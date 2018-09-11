/* $Id : shopping_flow.js 4865 2007-01-31 14:04:10Z paulgao $ */

var selectedShipping = null;
var selectedPayment  = null;
var selectedPack     = null;
var selectedCard     = null;
var selectedSurplus  = '';
var selectedBonus    = 0;
var selectedIntegral = 0;
var selectedOOS      = null;
var alertedSurplus   = false;

var groupBuyShipping = null;
var groupBuyPayment  = null;

/* *
 * 改变配送方式
 */
function selectShipping(obj)
{
  if (selectedShipping == obj)
  {
    return;
  }
  else
  {
    selectedShipping = obj;
  }

  var supportCod = obj.attributes['supportCod'].value + 0;
  var theForm = obj.form;

  for (i = 0; i < theForm.elements.length; i ++ )
  {
    if (theForm.elements[i].name == 'payment' && theForm.elements[i].attributes['isCod'].value == '1')
    {
      if (supportCod == 0)
      {
        theForm.elements[i].checked = false;
        theForm.elements[i].disabled = true;
      }
      else
      {
        theForm.elements[i].disabled = false;
      }
    }
  }

  if (obj.attributes['insure'].value + 0 == 0)
  {
    document.getElementById('HHS_NEEDINSURE').checked = false;
    document.getElementById('HHS_NEEDINSURE').disabled = true;
  }
  else
  {
    document.getElementById('HHS_NEEDINSURE').checked = false;
    document.getElementById('HHS_NEEDINSURE').disabled = false;
  }

  var now = new Date();
  Ajax.call('flow.php?step=select_shipping', 'shipping=' + obj.value, orderShippingSelectedResponse, 'GET', 'JSON');
}

/**
 *
 */
function orderShippingSelectedResponse(result)
{
  if (result.need_insure)
  {
    try
    {
      document.getElementById('HHS_NEEDINSURE').checked = true;
    }
    catch (ex)
    {
      alert(ex.message);
    }
  }

  try
  {
    if (document.getElementById('HHS_CODFEE') != undefined)
    {
      document.getElementById('HHS_CODFEE').innerHTML = result.cod_fee;
    }
  }
  catch (ex)
  {
    alert(ex.message);
  }

  orderSelectedResponse(result);
}

/* *
 * 改变支付方式
 */
function selectPayment(obj)
{
  if (selectedPayment == obj)
  {
    return;
  }
  else
  {
    selectedPayment = obj;
  }

  Ajax.call('flow.php?step=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}
/* *
 * 团购购物流程 --> 改变配送方式
 */
function handleGroupBuyShipping(obj)
{
  if (groupBuyShipping == obj)
  {
    return;
  }
  else
  {
    groupBuyShipping = obj;
  }

  var supportCod = obj.attributes['supportCod'].value + 0;
  var theForm = obj.form;

  for (i = 0; i < theForm.elements.length; i ++ )
  {
    if (theForm.elements[i].name == 'payment' && theForm.elements[i].attributes['isCod'].value == '1')
    {
      if (supportCod == 0)
      {
        theForm.elements[i].checked = false;
        theForm.elements[i].disabled = true;
      }
      else
      {
        theForm.elements[i].disabled = false;
      }
    }
  }

  if (obj.attributes['insure'].value + 0 == 0)
  {
    document.getElementById('HHS_NEEDINSURE').checked = false;
    document.getElementById('HHS_NEEDINSURE').disabled = true;
  }
  else
  {
    document.getElementById('HHS_NEEDINSURE').checked = false;
    document.getElementById('HHS_NEEDINSURE').disabled = false;
  }

  Ajax.call('group_buy.php?act=select_shipping', 'shipping=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 团购购物流程 --> 改变支付方式
 */
function handleGroupBuyPayment(obj)
{
  if (groupBuyPayment == obj)
  {
    return;
  }
  else
  {
    groupBuyPayment = obj;
  }

  Ajax.call('group_buy.php?act=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 改变商品包装
 */
function selectPack(obj)
{
  if (selectedPack == obj)
  {
    return;
  }
  else
  {
    selectedPack = obj;
  }

  Ajax.call('flow.php?step=select_pack', 'pack=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 改变祝福贺卡
 */
function selectCard(obj)
{
  if (selectedCard == obj)
  {
    return;
  }
  else
  {
    selectedCard = obj;
  }

  Ajax.call('flow.php?step=select_card', 'card=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 选定了配送保价
 */
function selectInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('flow.php?step=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 团购购物流程 --> 选定了配送保价
 */
function handleGroupBuyInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('group_buy.php?act=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
  if (result.error)
  {
    alert(result.error);
    //location.href = './';
  }

  try
  {
    var layer = document.getElementById("HHS_ORDERTOTAL");

    layer.innerHTML = (typeof result == "object") ? result.content : result;

    if (result.payment != undefined)
    {
      var surplusObj = document.forms['theForm'].elements['surplus'];
      if (surplusObj != undefined)
      {
        surplusObj.disabled = result.pay_code == 'balance';
      }
    }
  }
  catch (ex) { }
}

/* *
 * 改变余额
 */
function changeSurplus(val)
{
  if (selectedSurplus == val)
  {
    return;
  }
  else
  {
    selectedSurplus = val;
  }

  Ajax.call('flow.php?step=change_surplus', 'surplus=' + val, changeSurplusResponse, 'GET', 'JSON');
}
/* *
 * 改变余额
 */
function flowschangeSurplus(val)
{
  if (selectedSurplus == val)
  {
    return;
  }
  else
  {
    selectedSurplus = val;
  }
  Ajax.call('flows.php?step=change_surplus', 'surplus=' + val, flowschangeSurplusResponse, 'GET', 'JSON');
}
function flowschangeSurplusResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById("HHS_SURPLUS_NOTICE").innerHTML = obj.error;
      document.getElementById('HHS_SURPLUS').value = '0';
      document.getElementById('HHS_SURPLUS').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById("HHS_SURPLUS_NOTICE").innerHTML = '';
    }
    catch (ex) { }
	
	document.getElementById("pay_l_new").innerHTML = obj.content;
    //orderSelectedResponse(obj.content);
  }
}
/* *
 * 改变余额回调函数
 */
function changeSurplusResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById("HHS_SURPLUS_NOTICE").innerHTML = obj.error;
      document.getElementById('HHS_SURPLUS').value = '0';
      document.getElementById('HHS_SURPLUS').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById("HHS_SURPLUS_NOTICE").innerHTML = '';
    }
    catch (ex) { }
    orderSelectedResponse(obj.content);
  }
}

/* *
 * 改变积分
 */
function changeIntegral(val)
{
  if (selectedIntegral == val)
  {
    return;
  }
  else
  {
    selectedIntegral = val;
  }

  Ajax.call('flow.php?step=change_integral', 'points=' + val, changeIntegralResponse, 'GET', 'JSON');
}

/* *
 * 改变积分回调函数
 */
function changeIntegralResponse(obj)
{
  if (obj.error)
  {
    try
    {
      document.getElementById('HHS_INTEGRAL_NOTICE').innerHTML = obj.error;
      document.getElementById('HHS_INTEGRAL').value = '0';
      document.getElementById('HHS_INTEGRAL').focus();
    }
    catch (ex) { }
  }
  else
  {
    try
    {
      document.getElementById('HHS_INTEGRAL_NOTICE').innerHTML = '';
    }
    catch (ex) { }
    orderSelectedResponse(obj.content);
  }
}

/* *
 * 改变红包
 */
function changeBonus(val)
{
	
  if (selectedBonus == val)
  {
    return;
  }
  else
  {
    selectedBonus = val;
  }
  
  document.getElementById('HHS_BONUS').value = val;
  
  var img = document.getElementsByTagName("img");
  for (var i = 0; i<img.length; i++ ){
	  var pre = img[i].id.substring(0, 13) ;
  	  if(pre=='yellow_coupon'){
  		  img[i].src = "themes/haohaios/images/white_4bbd64b.png";
  	  }
  }
  if(val!=0){
	  document.getElementById('yellow_coupon'+val).src = "themes/haohaios/images/yellow_bd15f0c.png";
  }
  

  Ajax.call('flow.php?step=change_bonus', 'bonus=' + val, orderSelectedResponse, 'GET', 'JSON');

}


/*pangbin 新模板增加 start*/
function changeBonus_new(val)
{
  if (selectedBonus == val)
  {
    return;
  }
  else
  {
    selectedBonus = val;
  }
  document.getElementById('HHS_BONUS').value = val;
  var pre=$('#yellow_coupon'+val).attr("class");
  
  	  if(pre=='coupons hui'){
		  $('#yellow_coupon'+val).removeClass("hui");
		  $('#yellow_coupon'+val).siblings().addClass("hui");
  	  }
  Ajax.call('flow.php?step=change_bonus', 'bonus=' + val, orderSelectedResponse, 'GET', 'JSON');
}
/*pangbin 新模板增加 end*/

var is_up=1;
function pack_up(){
	if(is_up==0){
		is_up=1;
		document.getElementById('goTenPay').className = "pay3_item pay2_wx pay2_selected";
		document.getElementById('animate_set').style.display = "";
		
	}else{
		is_up=0;
		document.getElementById('goTenPay').className = "pay3_item pay2_wx ";
		document.getElementById('animate_set').style.display = "none";
		
	}/**/
}  

/* *
 * 改变红包的回调函数
 */
function changeBonusResponse(obj)
{
  if (obj.error)
  {
    alert(obj.error);

    try
    {
      document.getElementById('HHS_BONUS').value = '0';
    }
    catch (ex) { }
  }
  else
  {

	
    orderSelectedResponse(obj); 
  }
}

/**
 * 验证红包序列号
 * @param string bonusSn 红包序列号
 */
function validateBonus(bonusSn)
{
  Ajax.call('flow.php?step=validate_bonus', 'bonus_sn=' + bonusSn, validateBonusResponse, 'GET', 'JSON');
}

function validateBonusResponse(obj)
{

if (obj.error)
  {
    alert(obj.error);
    orderSelectedResponse(obj.content);
    try
    {
      document.getElementById('HHS_BONUSN').value = '0';
    }
    catch (ex) { }
  }
  else
  {
    orderSelectedResponse(obj.content);
  }
}

/* *
 * 改变发票的方式
 */
function changeNeedInv()
{
  var obj        = document.getElementById('HHS_NEEDINV');
  var objType    = document.getElementById('HHS_INVTYPE');
  var objPayee   = document.getElementById('HHS_INVPAYEE');
  var objContent = document.getElementById('HHS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invType    = obj.checked ? (objType != undefined ? objType.value : '') : '';
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objType.disabled = objPayee.disabled = objContent.disabled = ! obj.checked;
  if(objType != null)
  {
    objType.disabled = ! obj.checked;
  }

  Ajax.call('flow.php?step=change_needinv', 'need_inv=' + needInv + '&inv_type=' + encodeURIComponent(invType) + '&inv_payee=' + encodeURIComponent(invPayee) + '&inv_content=' + encodeURIComponent(invContent), orderSelectedResponse, 'GET');
}

/* *
 * 改变发票的方式
 */
function groupBuyChangeNeedInv()
{
  var obj        = document.getElementById('HHS_NEEDINV');
  var objPayee   = document.getElementById('HHS_INVPAYEE');
  var objContent = document.getElementById('HHS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objPayee.disabled = objContent.disabled = ! obj.checked;

  Ajax.call('group_buy.php?act=change_needinv', 'need_idv=' + needInv + '&amp;payee=' + invPayee + '&amp;content=' + invContent, null, 'GET');
}

/* *
 * 改变缺货处理时的处理方式
 */
function changeOOS(obj)
{
  if (selectedOOS == obj)
  {
    return;
  }
  else
  {
    selectedOOS = obj;
  }

  Ajax.call('flow.php?step=change_oos', 'oos=' + obj.value, null, 'GET');
}

function getListvalues(objs)
{
	var selectvalues="";
	for(var i=0;i<objs.length;i++)
	{	
		if (objs[i].checked)
		{
			selectvalues+=objs[i].value+",";
		}
	}
	return selectvalues;
}
/* *
 * 检查提交的订单表单
 */
function checkOrderForm(frm)
{
  var paymentSelected = false;
  var shippingSelected = false;
/*
  if(document.getElementsByName("address_id")[0]){
	  var  address_id = getListvalues(document.getElementsByName("address_id"));
	  if(address_id=="0,"||address_id=='')
	  {
			alert('请先选择收货地址');
			return false;
	  } 
  }*/
  
  /* 检查是否选择了支付配送方式
  for (i = 0; i < frm.elements.length; i ++ )
  {

    if (frm.elements[i].name == 'payment' && frm.elements[i].checked)
    {
      paymentSelected = true;
    }
  }*/

  if(frm.elements.payment.value ){
	  paymentSelected=true;
  }
	  
  if ( ! paymentSelected)
  {
    alert(flow_no_payment);
    return false;
  }

  // 检查用户输入的余额
  if (document.getElementById("HHS_SURPLUS"))
  {
    var surplus = document.getElementById("HHS_SURPLUS").value;
    var error   = Utils.trim(Ajax.call('flow.php?step=check_surplus', 'surplus=' + surplus, null, 'GET', 'TEXT', false));

    if (error)
    {
      try
      {
        document.getElementById("HHS_SURPLUS_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
      return false;
    }
  }

  // 检查用户输入的积分
  if (document.getElementById("HHS_INTEGRAL"))
  {
    var integral = document.getElementById("HHS_INTEGRAL").value;
    var error    = Utils.trim(Ajax.call('flow.php?step=check_integral', 'integral=' + integral, null, 'GET', 'TEXT', false));

    if (error)
    {
      return false;
      try
      {
        document.getElementById("HHS_INTEGRAL_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
    }
  }
  frm.action = frm.action + '?step=done';
  return true;
}

/* *
 * 检查收货地址信息表单中填写的内容
 */
function checkConsignee(frm)
{
  var msg = new Array();
  var err = false;
/*
  if (frm.elements['country'] && frm.elements['country'].value == 0)
  {
    msg.push(country_not_null);
    err = true;
  }*/
  if (Utils.isEmpty(frm.elements['consignee'].value))
  {
    err = true;
    //msg.push(consignee_not_null);
    document.getElementById('updateTip1').style.display='';
    return false;
  }else{

    var reg = /^[\u4e00-\u9fa5]+$/;

    if(!reg.test(frm.elements['consignee'].value))
    {

      err = true;
      document.getElementById('updateTip1').style.display='';
      return false;

    }else
    {

      document.getElementById('updateTip1').style.display='none';
    }
	  
  }
  
  document.getElementById('updateTip2').style.display='none';
  if (Utils.isEmpty(frm.elements['mobile'].value))
  {
    err = true;
    //msg.push('手机号码不能为空');
    document.getElementById('updateTip2').style.display='';
    return false;
  }else{
	  /*
	  var reg = /^1[3|4|5|7|8]\d{9}$/;
	  if(!reg.test(frm.elements['mobile'].value)){
		  err = true;
		 // msg.push('手机号码格式不正确');
		  document.getElementById('updateTip2').style.display='';
		  return false;
	  }*/
	  var reg = /^\d{6,12}$/;
	  if(!reg.test(frm.elements['mobile'].value)){
		  err = true;
		 // msg.push('手机号码格式不正确');
		  document.getElementById('updateTip2').style.display='';
		  return false;
	  }
	  else if(frm.elements['mobile'].value.length!=11)
	  {
		err = true;
		  document.getElementById('updateTip2').style.display='';
		  return false;
	  }
  }
  
  if (frm.elements['province'] && frm.elements['province'].value == 0 && frm.elements['province'].length > 1)
  {
     err = true;
    //msg.push(province_not_null);
     document.getElementById('updateTip3').style.display='';
     return false;
  }else{
	  document.getElementById('updateTip3').style.display='none';
  }

  if (frm.elements['city'] && frm.elements['city'].value == 0 )
  {
    err = true;
    //msg.push(city_not_null);
    document.getElementById('updateTip4').style.display='';
    return false;
  }else{
	  document.getElementById('updateTip4').style.display='none';
  }

  if (frm.elements['district'] && frm.elements['district'].length > 1)
  {
    if (frm.elements['district'].value == 0)
    {
      err = true;
      //msg.push(district_not_null);
      document.getElementById('updateTip5').style.display='';
      return false;
    }else{
    	document.getElementById('updateTip5').style.display='none';
    }
  }

  
/*  if ( ! Utils.isEmail(frm.elements['email'].value))
  {
    err = true;
    msg.push(invalid_email);
  }*/

  if (frm.elements['address'] && Utils.isEmpty(frm.elements['address'].value))
  {
    err = true;
    //msg.push(address_not_null);
    document.getElementById('updateTip6').style.display='';
    return false;
  }else{
	  document.getElementById('updateTip6').style.display='none';
  }
  return true;

/*  if (frm.elements['zipcode'] && frm.elements['zipcode'].value.length > 0 && (!Utils.isNumber(frm.elements['zipcode'].value)))
  {
    err = true;
    msg.push(zip_not_num);
  }*/

  /*if (Utils.isEmpty(frm.elements['tel'].value))
  {
    err = true;
    msg.push(tele_not_null);
  }
  else
  {
    if (!Utils.isTel(frm.elements['tel'].value))
    {
      err = true;
      msg.push(tele_invaild);
    }
  }*/

 
 /*
  if (err)
  {
    message = msg.join("\r\n");
    alert(message);
  }*/
  //return ! err;
}





function show_address(address_id)
{
//alert(address_id);
  Ajax.call('flow.php', 'step=get_address&id=' + address_id, get_addressResponse, 'GET', 'JSON');
}
function get_addressResponse(result)
{
	if(result.error== 1)
	{
		window.location.href='flow.php?step=login';
	}
	else if(result.error==2)
	{
		alert('您说添加的地址信息添加不得超过5条');
		document.getElementById("address_id_"+result.s_address_id).checked = "checked";
	}
	else
	{
   		document.getElementById('show_address').innerHTML =result.content;
	}
}


function show_address1(address_id)
{
//alert(address_id);
  Ajax.call('flow.php', 'step=get_address&id=' + address_id, get_addressResponse1, 'GET', 'JSON');
}
function get_addressResponse1(result)
{
	if(result.error== 1)
	{
		window.location.href='flow.php?step=login';
	}
	else if(result.error==2)
	{
		alert('您说添加的地址信息添加不得超过5条');
		document.getElementById("address_id_"+result.s_address_id).checked = "checked";
	}
	else
	{
   		document.getElementById('show_address').innerHTML ='';
		/*更新配送方式*/
		if(result.shipping_method !="")
		document.getElementById('shipping_method_id').innerHTML =result.shipping_method;
	}
}

function save_address()
{
	var consignee = document.getElementById('consignee').value;
	//var email     = document.getElementById('email').value;
	var provinces = document.getElementById('selProvinces').value;
	var city = document.getElementById('selCities').value;
	var district = document.getElementById('selDistricts').value;
	//var area = document.getElementById('selarea').value;

	var address = document.getElementById('address').value;
    //var tel = document.getElementById('tel').value;
	var mobile = document.getElementById('mobile').value;
	//var zipcode = document.getElementById('zipcode').value;
	var address_id = document.getElementById('hidden_address_id').value;
	//var best_time = document.getElementById('best_time_0').value;
	var address_list  = new Object();
	var i = 0;
	
	var msg = new Array();
	var err = false;
	if(consignee=='')
	{
		msg.push(consignee_not_null);
		err = true;
	}
  if (provinces && provinces == 0)
  {
    err = true;
    msg.push(province_not_null);
  }
  if (city&& city == 0)
  {
    err = true;
    msg.push(city_not_null);
  }
  if (district&&district == 0)
  {
      err = true;
      msg.push(district_not_null);
  }
/*  if(email!='')
  {
  if ( ! Utils.isEmail(email))
  {
    err = true;
    msg.push(invalid_email);
  }
  }*/

  if (Utils.isEmpty(address))
  {
    err = true;
    msg.push(address_not_null);
  }
  

 // if (zipcode=='')
//  {
//    err = true;
//    msg.push(zip_not_num);
//  }

//  if (Utils.isEmpty(tel))
//  {
//    err = true;
//    msg.push(tele_not_null);
//  }
//  else
//  {
//    if (!Utils.isTel(tel))
//    {
//      err = true;
//      msg.push(tele_invaild);
//    }
//  }
  if (!Utils.isTel(mobile)||!(/^1[3|5|8][0-9]\d{4,8}$/.test(mobile)))
  {
    err = true;
    msg.push(mobile_invaild);
  }
  
  
  if (err)
  {
	message = msg.join("\n");
	alert(message);
  }
  else
  {
 	address_list.consignee = consignee;
	address_list.province = provinces;
	address_list.city = city;
	address_list.district = district;
//	address_list.area = area;
	//address_list.email = email;
	address_list.address = address;
	//address_list.zipcode = zipcode;
	//address_list.tel = tel;
	address_list.mobile = mobile;
	address_list.address_id = address_id;
	//address_list.best_time = best_time;
	Ajax.call('flow.php?step=save_address', 'address_list='+ obj2str(address_list),addressResponse, 'GET', 'JSON');
  err=false;
  }
}
function drop_consignee(address_id)
{
	if (confirm('确定要删除吗'))
	{
	
	  Ajax.call('flow.php', 'step=drop_consignee&id=' + address_id,drop_consigneeResponse, 'GET', 'JSON');
	}
}
function drop_consigneeResponse(result)
{
	if(result.error ==1)
	{
		window.location.href='flow.php?step=login';
	}
	else
	{
		if(result.address_count)
		{
			document.getElementById('show_address_list').innerHTML =result.content;	
			document.getElementById("address_id_"+result.s_address_id).checked = "checked";//选中当前的
		}
		else
		{
			document.getElementById('show_address_list').innerHTML =result.content;	
			document.getElementById("address_id_0").checked = "checked";//选中当前的
		//关闭层
			show_address(0);

		}
	}
}
function addressResponse(result)
{
	if(result.error ==0)
	{
		window.location.href='flow.php?step=login';
	}
	else
	{
		document.getElementById('show_address_list').innerHTML =result.content;
		document.getElementById("address_id_"+result.s_address_id).checked = "checked";//选中当前的
		//关闭层
		document.getElementById("show_table_address").style.display  = 'none';
	}
}