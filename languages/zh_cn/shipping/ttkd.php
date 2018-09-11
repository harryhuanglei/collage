<?php


$_LANG['ttkd']          = '天天快递';
$_LANG['ttkd_desc']     = '一区首重为10元/KG，二区首重为12元/KG， 三区首重为16元/KG，续重均为5-12元/KG';
$_LANG['item_fee'] = '单件商品费用：';
$_LANG['base_fee'] = '首重费用';
$_LANG['step_fee'] = '续重每1000克或其零数的费用';
$_LANG['shipping_print'] = '<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:2.2cm;">&nbsp;</td>
  </tr>
</table>
<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:4.4cm; width:9.1cm;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td style="width:2cm; height:0.8cm;">&nbsp;</td>
    <td style="width:2.7cm;">{$shop_name}</td>
    <td style="width:1.2cm;">&nbsp;</td>
    <td>{$province}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:1.6cm;">{$shop_address}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:0.8cm;">{$shop_name}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td style="height:1.2cm;">{$service_phone}</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
    </table>
    </td>
    <td style="height:4.4cm; width:9.1cm;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td style="width:2cm; height:0.8cm;">&nbsp;</td>
    <td style="width:2.7cm;">{$order.consignee}</td>
    <td style="width:1.2cm;">&nbsp;</td>
    <td>{$order.region}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:1.6cm;">{$order.address}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:0.8cm;"></td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td style="height:1.2cm;">{$order.mobile}</td>
    <td>&nbsp;</td>
    <td>{$order.zipcode}</td>
    </tr>
    </table>
    </td>
  </tr>
</table>
<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:4.2cm;">&nbsp;</td>
  </tr>
</table>';

?>