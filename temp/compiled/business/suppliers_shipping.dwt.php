<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>商家管理平台</title>
<link href="templates/css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/haohaios.js"></script>
<script type="text/javascript" src="../js/user.js"></script>
<script type="text/javascript" src="../js/region.js"></script>
<script type="text/javascript" src="../js/utils.js"></script>
<script type="text/javascript" src="templates/js/main.js"></script>
<script type="text/javascript" src="templates/js/supp.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/listtable.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/validator.js"></script>
<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="templates/js/public_tab.js"></script>
<script>
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
</head>
<body onload="pageheight()">
 <?php echo $this->fetch('library/lift_menu.lbi'); ?>
    <?php if ($this->_var['action'] == 'add_point' || $this->_var['action'] == 'edit_point'): ?>
    <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_article.png" /><span>自提点管理</span>
		</div>
		<div class="maincon">
        		<?php if ($this->_var['action'] == 'add_point'): ?>
				<div class="contitlelist"><span>添加自提点</span><div class="titleright"><a href="?op=shipping&act=point_list">返回列表</a></div></div>
                <?php else: ?>
                <div class="contitlelist"><span>编辑自提点</span><div class="titleright"><a href="?op=shipping&act=point_list">返回列表</a></div></div>
                <?php endif; ?>
		<div class="conbox">
            
          
          <form enctype="multipart/form-data" action="index.php" method="post" name="theForm" onsubmit="return validate()">

        <table width="100%" id="general-table" align="center" class="edittable">
          <tr>
            <td class="right">自提点名称：</td>
            <td><input type="text" class="input"  name="shop_name" value="<?php echo htmlspecialchars($this->_var['point']['shop_name']); ?>" size="30" />&nbsp;
            <?php echo $this->_var['lang']['require_field']; ?>
            </td>
          </tr>
<tr>
            <td class="right">选择省份</td>
            <td>
            <select name="province" id="selProvinces" onChange="region.changed(this, 2, 'selCities')" style="border:1px solid #ccc;">
              <option value="0">请选择省份</option>
              <?php $_from = $this->_var['province_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?>
              <option value="<?php echo $this->_var['province']['region_id']; ?>" <?php if ($this->_var['point']['province'] == $this->_var['province']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['province']['region_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </select>
               <?php echo $this->_var['lang']['require_field']; ?>
            </td>
          </tr>
          <tr>
            <td class="right">选择城市</td>
            <td>
            <select name="city" id="selCities" onChange="region.changed(this, 3, 'selDistricts')" style="border:1px solid #ccc;">
              <option value="0">请选择城市</option>
              <?php $_from = $this->_var['city_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
              <option value="<?php echo $this->_var['city']['region_id']; ?>" <?php if ($this->_var['point']['city'] == $this->_var['city']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['city']['region_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
         </select>
               <?php echo $this->_var['lang']['require_field']; ?>
            </td>
          </tr>
          <tr>
            <td class="right">选择区域</td>
            <td>
            <select name="district" id="selDistricts"  style="border:1px solid #ccc;">
              <option value="0">请选择区域</option>
              <?php $_from = $this->_var['district_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>
              <option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['point']['district'] == $this->_var['district']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['district']['region_name']; ?></option>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>  
            </select> 
               <?php echo $this->_var['lang']['require_field']; ?>
            </td>
          </tr>
          <tr>
          
<!--           <tr>
            <td class="right">经度：</td>
            <td><input type="text" class="input"  name="longitude" value="<?php echo $this->_var['point']['longitude']; ?>" size="30" /></td>
          </tr>
          <tr>
            <td class="right">纬度：</td>
            <td><input type="text" class="input"  name="latitude" value="<?php echo $this->_var['point']['latitude']; ?>" size="30" />
            <a href="http://api.map.baidu.com/lbsapi/getpoint/" target="_blank">获取坐标</a>
            
            </td>
          </tr> -->
          <tr>
            <td class="right">地址：</td>
            <td><input type="text" class="input"  name="address" value="<?php echo htmlspecialchars($this->_var['point']['address']); ?>" size="30" />&nbsp;
            <?php echo $this->_var['lang']['require_field']; ?>
            </td>
          </tr>
           <tr>
            <td class="right">联系电话：</td>
            <td><input type="text" name="mobile"  class="input" value="<?php echo $this->_var['point']['mobile']; ?>" size="30" />&nbsp;
        
            </td>
            
             <tr>
            <td class="right">开启打印机</td>
            <td>
            <input type="radio" name="has_printer"  class="input"  value="1" <?php if ($this->_var['point']['has_printer'] == 1): ?>checked<?php endif; ?>/>&nbsp;是
            <input type="radio" name="has_printer"  class="input" value="0" <?php if ($this->_var['point']['has_printer'] != 1): ?>checked<?php endif; ?>/>&nbsp; 否
        
            </td>
          </tr>
          <tr>
            <td class="right">打印机</td>
            <td>
            <input type="radio" name="printer_type"  class="input" value="feyin" <?php if ($this->_var['point']['printer_type'] == 'feyin'): ?>checked<?php endif; ?>/>&nbsp;飞印
            </td>
          </tr>
          <tr>
            <td class="right">打印机终端编码</td>
            <td>
            <input type="text" name="device_no"   class="input" value="<?php echo $this->_var['point']['device_no']; ?>" size="60" />&nbsp;
            </td>
          </tr>
          <tr>
            <td class="right">打印机商户代码</td>
            <td>
            <input name="device_code" type="text" id="device_code"   class="input" value="<?php echo $this->_var['point']['device_code']; ?>" size="60" />&nbsp;登录飞印后在“API集成”->“获取API集成信息”获取
            </td>
          </tr> 
          <tr>
            <td class="right">打印机密钥</td>
            <td>
            <input type="text" name="device_key"   class="input"value="<?php echo $this->_var['point']['device_key']; ?>" size="60" />&nbsp;同上
            </td>
          </tr>         
            
            
            
            
            
            <?php if ($this->_var['point']['wx_openid']): ?>
           <tr id="bind-box">
            <td class="right">当前绑定：</td>
            <td><?php echo $this->_var['point']['wx_name']; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;delbind(<?php echo $this->_var['point']['id']; ?>);">取消绑定</a></td>
            <?php endif; ?>
            <?php if ($this->_var['point']['id'] > 0): ?>
            <tr>
              <td class="right">绑定核销员二维码</td>
              <td><img src="http://qr.liantu.com/api.php?text=<?php echo $this->_var['root']; ?>/join_qrcode.php?id=<?php echo $this->_var['point']['id']; ?>&w=200&m=10"  ></td>
            </tr>
            <tr>
              <td class="right">已绑定：</td>
              <td><div>
                <ul>
                <?php $_from = $this->_var['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                  <li><?php echo $this->_var['item']['uname']; ?> <span class="drop"><a href="?op=shipping&act=drop_point_user&id=<?php echo $this->_var['item']['id']; ?>&point_id=<?php echo $this->_var['point']['id']; ?>&shipping=<?php echo $this->_var['shipping']; ?>">删除</a></span></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
              </div>
              </td>
            </tr>
            <?php endif; ?>
          </tr>
            <tr>
   <td class="right">&nbsp;</td>
   <td>
            <input type="submit" value="立即提交" class="button"  />


  </td>
 </tr>
        </table>


       <input type="hidden" name="op" value="shipping" />
          <input type="hidden" name="id" value="<?php echo $this->_var['point']['id']; ?>" />
          <input type="hidden" name="shipping" value="<?php echo $this->_var['shipping']; ?>" />
          
    
        <input type="hidden" name="act" value="<?php if ($this->_var['action'] == 'add_point'): ?>insert_point<?php else: ?>update_point<?php endif; ?>" />
      </form>
            
            </div>
      </div>
 </div>
  <script language="JavaScript">
region.isAdmin=true;
function delbind(id){
  if(confirm('确定要此操作吗')){
    Ajax.call('index.php?op=shipping&act=delete_join&id='+id,null, delbindResponse, 'GET', 'JSON');
  }
}
function delbindResponse(res){
  alert('解绑成功!');
  $("#bind-box").remove();
}
function validate()
{
    var validator = new Validator('theForm');
    var shop_name  = document.forms['theForm'].elements['shop_name'].value;
    var address  = document.forms['theForm'].elements['address'].value;
    
    
    validator.required('shop_name', '自提点名称不能为空');
   
    validator.required('address', '地址不能为空');

    return validator.passed();
  
}
</script> 
    <?php endif; ?>
    <?php if ($this->_var['action'] == 'point_list'): ?>
    <div class="main" id="main">
    <div class="maintop">
    <img src="templates/images/title_goods.png" /><span>自提列表</span>
    </div><div class="maincon">
    <div class="contitlelist">
    <span>自提列表</span>
      <div class="titleright"><a href="?op=shipping&act=add_point">新建自提点</a></div>
    </div>
    <div class="conbox">
  
	<table cellspacing='1' cellpadding='3' class="listtable">
  <tr>
    <th>店铺名称</th>
    <th>地址</th>
    <th>操作</th>
  </tr>
  <?php $_from = $this->_var['point_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
  <tr>
    <td class="first-cell" nowrap="true">
      <?php echo $this->_var['item']['shop_name']; ?>
    </td>
    <td>
      <?php echo $this->_var['item']['province']; ?><?php echo $this->_var['item']['city']; ?><?php echo $this->_var['item']['district']; ?><?php echo $this->_var['item']['address']; ?>
    </td>
    
    <td align="center" nowrap="true">
      <a href="index.php?op=shipping&act=edit_point&id=<?php echo $this->_var['item']['id']; ?>&shipping=<?php echo $this->_var['shipping']; ?>" title="编辑">编辑</a>
     
      <a href="index.php?op=shipping&act=delete_point&id=<?php echo $this->_var['item']['id']; ?>&shipping=<?php echo $this->_var['shipping']; ?>" onclick="return confirm('确定要删除吗');" title="删除">删除</a>
    
    </td>
  </tr>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
</div>
</div>
</div>
    
  
    <?php endif; ?>
    
    
<?php if ($this->_var['action'] == 'supp_shipping'): ?>
    <div class="main" id="main">
    <div class="maintop">
    <img src="templates/images/title_goods.png" /><span>配送方式</span>
    </div><div class="maincon">
    <div class="contitlelist">
    <span>配送方式</span>
    </div>
    <div class="conbox">
  
	<table cellspacing="0" cellpadding="0" class="listtable">

	<tr>

		 <th>配送方式名称</th>
		 <th class="left">配送方式描述</th>
        <th class="left">保价费用</th> 	 	 	 	 	 	
        <th class="left">货到付款？</th>
        <th class="left">插件版本</th>
        <th class="left">插件作者</th>
        <th class="left">排序</th>
        <th>操作</th>
	</tr>

	<?php $_from = $this->_var['modules']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'module');if (count($_from)):
    foreach ($_from AS $this->_var['module']):
?>
    <?php if ($this->_var['module']['install'] == 1): ?>
    
	<tr>

	<td class="middle"><?php echo $this->_var['module']['name']; ?></td>

	<td><?php echo sub_str($this->_var['module']['desc'],"30"); ?></td>

	<td><?php echo $this->_var['module']['insure_fee']; ?></td>

	<td ><?php if ($this->_var['module']['cod'] == 1): ?>是<?php else: ?>否<?php endif; ?></td>

	<td> <?php echo $this->_var['module']['version']; ?></td>
	<td> <?php echo $this->_var['module']['author']; ?></td>
    
    <td> <?php echo $this->_var['module']['shipping_order']; ?></td>
    
    <td align="center"> 
  	  <a href="index.php?op=shipping&act=shipping_area_list&shipping=<?php echo $this->_var['module']['id']; ?>">设置区域</a>
         <?php if ($this->_var['module']['shipping_code'] == 'cac'): ?>
      	<a href="index.php?op=shipping&act=point_list&shipping=<?php echo $this->_var['module']['id']; ?>">设置取货点</a>
      <?php endif; ?>
	  <!--<a href="suppliers.php?act=edit_print_template&shipping=<?php echo $this->_var['module']['id']; ?>">编辑打印模板</a>-->
	</td>
  
	</tr>
    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
</div>
</div>
</div>
<?php endif; ?>




<?php if ($this->_var['action'] == 'shipping_area_list'): ?>
    <div class="main" id="main">
    <div class="maintop">
    <img src="templates/images/title_goods.png" /><span>系统设置</span>
    </div><div class="maincon">
              
    <div class="contitlelist">
    <span>配送区域</span>
    
    <div class="titleright"><a href="index.php?op=shipping&act=shipping_area_add&id=<?php echo $this->_var['shipping_id']; ?>">新建配送区域</a></div>
    </div>
    <div class="conbox">
  <form method="post" action="index.php" name="listForm" onsubmit="return confirm('您确定要删除选定的配送区域吗？')">
  
	<table cellspacing="0" cellpadding="0" class="listtable">

	<tr>

		 <th><input type="checkbox" onclick="listTable.selectAll(this, 'areas')" />编号</th>
		 <th class="left">配送区域名称</th>
        <th class="left">所辖地区</th> 	 	 	 	 	 	
        <th class="left">操作</th>
    </tr>
    
	<?php $_from = $this->_var['areas']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'area');if (count($_from)):
    foreach ($_from AS $this->_var['area']):
?>
	<tr>
 
	<td class="middle">
    <input type="checkbox" name="areas[]" value="<?php echo $this->_var['area']['shipping_area_id']; ?>" /><?php echo $this->_var['area']['shipping_area_id']; ?>
    </td>

	<td><?php echo $this->_var['area']['shipping_area_name']; ?></td>

	<td><?php echo $this->_var['area']['shipping_area_regions']; ?></td>

	<td>
    
    <a href="index.php?op=shipping&act=shipping_area_edit&id=<?php echo $this->_var['area']['shipping_area_id']; ?>&shipping_id=<?php echo $this->_var['shipping_id']; ?>">编辑</a> | 
    <a onclick="return confirm('您确定要删除选定的配送区域吗？');" href="index.php?op=shipping&act=shipping_area_remove&id=<?php echo $this->_var['area']['shipping_area_id']; ?>&shipping_id=<?php echo $this->_var['shipping_id']; ?>">移除</a>
    </td> 
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    
    <tr>

    <td colspan="4" align="center">
 <input type="hidden" name="op" value="shipping" />
      <input type="hidden" name="act" value="shipping_area_multi_remove" />

      <input type="hidden" name="shipping" value="<?php echo $_GET['shipping']; ?>" />

      <input type="submit" value="移除选定的配送区域" id="btnSubmit" class="button" />

    </td>

  </tr>
  
    </table>
    </form>
</div>
</div>
</div>
<?php endif; ?>


<?php if ($this->_var['action'] == 'shipping_area_edit' || $this->_var['action'] == 'shipping_area_add'): ?>

<div class="main" id="main">
    <div class="maintop">
    <img src="templates/images/title_goods.png" /><span>系统设置</span>
    </div><div class="maincon">
    <div class="contitlelist">
    <span>编辑配送区域</span>
    </div>
    

<div class="main-div">

<form method="post" action="index.php" name="theForm" onsubmit="return validate()" style="background:#FFF">

<fieldset style="border:1px solid #DDEEF2">

  <table >

    <tr>

      <td class="label">配送区域名称:</td>

<td><input type="text" name="shipping_area_name" maxlength="60" size="30" value="<?php echo $this->_var['shipping_area']['shipping_area_name']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>

    </tr>

  <?php if ($this->_var['shipping_area']['shipping_code'] == 'ems' || $this->_var['shipping_area']['shipping_code'] == 'yto' || $this->_var['shipping_area']['shipping_code'] == 'zto' || $this->_var['shipping_area']['shipping_code'] == 'sto_express' || $this->_var['shipping_area']['shipping_code'] == 'post_mail' || $this->_var['shipping_area']['shipping_code'] == 'sf_express' || $this->_var['shipping_area']['shipping_code'] == 'post_express'): ?>

    <tr>

    <td class="label">费用计算方式:</td>

    <td>

    <input type="radio"  <?php if ($this->_var['fee_compute_mode'] != 'by_number'): ?>checked="true"<?php endif; ?> onclick="compute_mode('<?php echo $this->_var['shipping_area']['shipping_code']; ?>','weight')" name="fee_compute_mode" value="by_weight" />按重量计算

    <input type="radio" <?php if ($this->_var['fee_compute_mode'] == 'by_number'): ?>checked="true"<?php endif; ?>  onclick="compute_mode('<?php echo $this->_var['shipping_area']['shipping_code']; ?>','number')" name="fee_compute_mode" value="by_number" />按商品件数计算

    </td>

    </tr>

  <?php endif; ?>

<?php if ($this->_var['shipping_area']['shipping_code'] != 'cac'): ?>

    <?php $_from = $this->_var['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'field');if (count($_from)):
    foreach ($_from AS $this->_var['field']):
?>

    <?php if ($this->_var['fee_compute_mode'] == 'by_number'): ?>

       <?php if ($this->_var['field']['name'] == 'item_fee' || $this->_var['field']['name'] == 'free_money' || $this->_var['field']['name'] == 'pay_fee'): ?>

            <tr id="<?php echo $this->_var['field']['name']; ?>" >

              <td class="label"><?php echo $this->_var['field']['label']; ?></td>

              <td><input type="text" name="<?php echo $this->_var['field']['name']; ?>"  maxlength="60" size="20" value="<?php echo $this->_var['field']['value']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>

            </tr>

            <?php else: ?>

            <tr id="<?php echo $this->_var['field']['name']; ?>" style="display:none">

              <td class="label"><?php echo $this->_var['field']['label']; ?></td>

              <td><input type="text" name="<?php echo $this->_var['field']['name']; ?>"  maxlength="60" size="20" value="<?php echo $this->_var['field']['value']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>

            </tr>

        <?php endif; ?>

    <?php else: ?>

        <?php if ($this->_var['field']['name'] != 'item_fee'): ?>

            <tr id="<?php echo $this->_var['field']['name']; ?>">

              <td class="label"><?php echo $this->_var['field']['label']; ?></td>

              <td><input type="text" name="<?php echo $this->_var['field']['name']; ?>"  maxlength="60" size="20" value="<?php echo $this->_var['field']['value']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>

            </tr>

        <?php else: ?>

            <tr id="<?php echo $this->_var['field']['name']; ?>" style="display:none">

              <td class="label"><?php echo $this->_var['field']['label']; ?></td>

              <td><input type="text" name="<?php echo $this->_var['field']['name']; ?>"  maxlength="60" size="20" value="<?php echo $this->_var['field']['value']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>

            </tr>

        <?php endif; ?>

     <?php endif; ?>

    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

<?php endif; ?>

  </table>

</fieldset>



<fieldset style="border:1px solid #DDEEF2">

  <legend style="background:#FFF">所辖地区:</legend>

  <table style="width:600px" align="center">

  <tr>

    <td id="regionCell">

      <?php $_from = $this->_var['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('id', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['id'] => $this->_var['region']):
?>

      <input type="checkbox" name="regions[]" value="<?php echo $this->_var['id']; ?>" checked="true" /> <?php echo $this->_var['region']; ?>&nbsp;&nbsp;

      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    </td>

  </tr>

  <tr>

    <td>

        <span  style="vertical-align: top">国家：</span>

        <select name="country" id="selCountries" onchange="region.changed(this, 1, 'selProvinces')" size="10" style="width:80px">

          <?php $_from = $this->_var['countries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');if (count($_from)):
    foreach ($_from AS $this->_var['country']):
?>

          <option value="<?php echo $this->_var['country']['region_id']; ?>"><?php echo htmlspecialchars($this->_var['country']['region_name']); ?></option>

          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

        </select>

        <span  style="vertical-align: top">省份：</span>

        <select name="province" id="selProvinces" onchange="region.changed(this, 2, 'selCities')" size="10" style="width:80px">

          <option value=''>请选择...</option>

        </select>

        <span  style="vertical-align: top">城市：</span>

        <select name="city" id="selCities" onchange="region.changed(this, 3, 'selDistricts')" size="10" style="width:80px">

          <option value=''>请选择...</option>

        </select>

        <span  style="vertical-align: top">区/县：</span>

        <select name="district" id="selDistricts" size="10" style="width:130px">

          <option value=''>请选择...</option>

        </select>

        <span  style="vertical-align: top"><input type="button" value="+" class="button" onclick="addRegion()" /></span>

    </td>

  </tr>

  </table >

</fieldset>



  <table >

  <tr>

    <td colspan="2" align="center">
      <input type="submit" value="确定" class="button" />
      <input type="reset" value="重置" class="button" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_action']; ?>" />
      
      <input type="hidden" name="op" value="shipping" />
      
      <input type="hidden" name="id" value="<?php echo $this->_var['shipping_area']['shipping_area_id']; ?>" />
      <input type="hidden" name="shipping" value="<?php echo $this->_var['shipping_area']['shipping_id']; ?>" />

    </td>

  </tr>

</table>



</form>

</div>

<script language="JavaScript">

<!--



region.isAdmin = true;

onload = function()

{

    document.forms['theForm'].elements['shipping_area_name'].focus();



    var selCountry = document.forms['theForm'].elements['country'];

    if (selCountry.selectedIndex <= 0)

    {

      selCountry.selectedIndex = 0;

    }



    region.loadProvinces(selCountry.options[selCountry.selectedIndex].value);



    // 开始检查订单

    startCheckOrder();

}



/**

 * 检查表单输入的数据

 */

function validate()
{

    validator = new Validator("theForm");
    validator.required('shipping_area_name', '配送区域名称不能为空。');
    validator.isInt('free_money', '免费额度不能为空且必须是一个整数。', true);
	
    var regions_chk_cnt = 0;

    for (i=0; i<document.getElementsByName('regions[]').length; i++)

    {

      if (document.getElementsByName('regions[]')[i].checked == true)

      {

        regions_chk_cnt++;

      }

    }



    if (regions_chk_cnt == 0)

    {

      validator.addErrorMsg('配送区域的所辖区域不能为空。');

    }


    return validator.passed();

}



/**

 * 添加一个区域

 */

function addRegion()

{

    var selCountry  = document.forms['theForm'].elements['country'];

    var selProvince = document.forms['theForm'].elements['province'];

    var selCity     = document.forms['theForm'].elements['city'];

    var selDistrict = document.forms['theForm'].elements['district'];

    var regionCell  = document.getElementById("regionCell");



    if (selDistrict.selectedIndex > 0)

    {

        regionId = selDistrict.options[selDistrict.selectedIndex].value;

        regionName = selDistrict.options[selDistrict.selectedIndex].text;

    }

    else

    {

        if (selCity.selectedIndex > 0)

        {

            regionId = selCity.options[selCity.selectedIndex].value;

            regionName = selCity.options[selCity.selectedIndex].text;

        }

        else

        {

            if (selProvince.selectedIndex > 0)

            {

                regionId = selProvince.options[selProvince.selectedIndex].value;

                regionName = selProvince.options[selProvince.selectedIndex].text;

            }

            else

            {

                if (selCountry.selectedIndex >= 0)

                {

                    regionId = selCountry.options[selCountry.selectedIndex].value;

                    regionName = selCountry.options[selCountry.selectedIndex].text;

                }

                else

                {

                    return;

                }

            }

        }

    }



    // 检查该地区是否已经存在

    exists = false;

    for (i = 0; i < document.forms['theForm'].elements.length; i++)

    {

      if (document.forms['theForm'].elements[i].type=="checkbox")

      {

        if (document.forms['theForm'].elements[i].value == regionId)

        {

          exists = true;

          alert(region_exists);

        }

      }

    }

    // 创建checkbox

    if (!exists)

    {

      regionCell.innerHTML += "<input type='checkbox' name='regions[]' value='" + regionId + "' checked='true' /> " + regionName + "&nbsp;&nbsp;";

    }

}



/**

 * 配送费用计算方式

 */

function compute_mode(shipping_code,mode)

{

    var base_fee  = document.getElementById("base_fee");

    var step_fee  = document.getElementById("step_fee");

    var item_fee  = document.getElementById("item_fee");

    if(shipping_code == 'post_mail' || shipping_code == 'post_express')

    {

     var step_fee1  = document.getElementById("step_fee1");

    }



    if(mode == 'number')

    {

      item_fee.style.display = '';

      base_fee.style.display = 'none';

      step_fee.style.display = 'none';

      if(shipping_code == 'post_mail' || shipping_code == 'post_express')

      {

       step_fee1.style.display = 'none';

      }

    }

    else

    {

      item_fee.style.display = 'none';

      base_fee.style.display = '';

      step_fee.style.display = '';

      if(shipping_code == 'post_mail' || shipping_code == 'post_express')

      {

       step_fee1.style.display = '';

      }

    }

}

//-->



</script>

<?php endif; ?> 




<?php if ($this->_var['action'] == 'edit_print_template'): ?>

  <div class="main" id="main">
    <div class="maintop">
    <img src="templates/images/title_goods.png" /><span>系统设置</span>
    </div><div class="maincon">
              
    <div class="contitlelist">
    <span>快递单模板</span>
     <div class="titleright"><a href="suppliers.php?act=supp_shipping">配送方式</a></div>
    </div>
    <div class="conbox">


  <table id="general-table" align="center" width="100%" cellpadding="3" cellspacing="1" border="0">

  <tr>

    <td colspan="2" width="100%">
    <strong>请选择模板的模式：</strong><input type="radio" name="model" id="model_1" value="1" <?php if ($this->_var['shipping']['print_model'] == 1): ?>checked="checked"<?php endif; ?> onclick="javascript:model_change('1');">代码模式&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="model" id="model_2" value="2" <?php if ($this->_var['shipping']['print_model'] == 2): ?>checked="checked"<?php endif; ?> onclick="javascript:model_change('2');">所见即所得模式<br/><span class="notice-span" style="display:block" id="noticeGoodsSN">选择"代码模式"可以切换到以前版本。建议您使用"所见即所得模式"。所有模式选择后，同样在打印模板中生效。</span></td>

  </tr>

  <tr>

    <th colspan="2" width="100%">编辑模板</th>

  </tr>



  <tr id="visual" <?php if ($this->_var['shipping']['print_model'] == 1): ?>style="display:none"<?php endif; ?>>

    <td colspan="2" width="100%"><iframe id="goods_desc___Frame" src="suppliers.php?act=print_index&shipping=<?php echo $this->_var['shipping_id']; ?>" width="99%" height="675" frameborder="0" scrolling="no"></iframe></td>

  </tr>

  

  <form method="post" name="theForm" action="index.php?op=shipping&act=do_edit_print_template&shipping=<?php echo $this->_var['shipping']['shipping_id']; ?>">

  <input type="hidden" name="print_model" value="1">

  <input type="hidden" name="shipping_name" value="<?php echo $this->_var['shipping']['shipping_name']; ?>">

  <tr id="code_shipping_print" <?php if ($this->_var['shipping']['print_model'] == 2): ?>style="display:none"<?php endif; ?>>

    <td width="75%"><textarea id="shipping_print" name="shipping_print" rows="26" cols="100" ><?php echo htmlspecialchars($this->_var['shipping']['shipping_print']); ?></textarea></td>

    <td align="left" valign="top" width="25%"><?php echo $this->_var['lang']['shipping_template_info']; ?></td>

  </tr>

  <tr id="code_submit" <?php if ($this->_var['shipping']['print_model'] == 2): ?>style="display:none"<?php endif; ?>>

    <td colspan="2" align="center" width="100%"><input type="submit" value="确定" class="button" /></td>

  </tr>
  </form>
  </table>
</div>
</div>
</div>


<script type="text/javascript">

<!--

var display_yes = (Browser.isIE) ? 'block' : '';



/**

 * 切换编辑模式

 */

function model_change(type)

{

  //获取表单对象

  switch (type)

  {

    case '1': //代码模式



        document.getElementById('code_shipping_print').style.display = display_yes;

        document.getElementById('code_submit').style.display = display_yes;



        document.getElementById('visual').style.display = 'none';



    break;



    case '2': //所见即所得模式



        document.getElementById('code_shipping_print').style.display = 'none';

        document.getElementById('code_submit').style.display = 'none';



        document.getElementById('visual').style.display = display_yes;



    break;

  }



  return true;



}

//-->

</script>
<?php endif; ?>
     

</body>
</html>