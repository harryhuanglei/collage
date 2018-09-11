<!doctype html>
<html lang="zh-CN">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="format-detection" content="telephone=no">
<title><?php echo $this->_var['page_title']; ?></title>
<link rel="shortcut icon" href="favicon.ico" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/style.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/flow.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/font-awesome.min.css" rel="stylesheet" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js,haohaios.js,shopping_flow.js,region.js')); ?>
</head>
<body>
<div class="container">
<?php if ($this->_var['step'] == "cart"): ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'cart.js')); ?>
    <?php if ($this->_var['goods_list']): ?>
    <div class="cart_box">
        <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'suppliers');if (count($_from)):
    foreach ($_from AS $this->_var['suppliers']):
?>
        <div class="cart_list">
            <div class="cart_supp"><img src="<?php echo empty($this->_var['suppliers']['logo']) ? $this->_var['shop_logo'] : $this->_var['suppliers']['logo']; ?>"><?php echo $this->_var['suppliers']['suppliers_name']; ?></div>
            <?php $_from = $this->_var['suppliers']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
            <div class="cart_goods" data-id="<?php echo $this->_var['goods']['rec_id']; ?>" id="rec_<?php echo $this->_var['goods']['rec_id']; ?>">
                  <div class="c_a"><input type="checkbox" class="checkbox" name="radio" id="goods_<?php echo $this->_var['goods']['rec_id']; ?>" <?php if ($this->_var['goods']['is_checked'] == 1): ?>checked<?php endif; ?>><label for="goods_<?php echo $this->_var['goods']['rec_id']; ?>"></label></div>
                <div class="c_b"><a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>"><img src="<?php echo $this->_var['goods']['goods_img']; ?>"></a></div>
                <div class="c_c">
                    <p class="tit"><?php echo $this->_var['goods']['goods_name']; ?></p>
                    <p class="attr"><?php echo $this->_var['goods']['goods_attr']; ?></p>
                    <p class="price">¥ <font><?php echo $this->_var['goods']['subtotal']; ?></font></p>
                </div>
                <div class="c_d">
                    <a class="drop"><span>删除</span></a>
                </div>
                <div class="nbox">
                    <i class="fa fa-minus hui"></i>
                    <span class="num" id="numAll"><?php echo $this->_var['goods']['goods_number']; ?></span>
                    <i class="fa fa-plus"></i>
                </div>
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>  
        </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <div class="cart_bnt"><input type="button" value="<?php echo $this->_var['lang']['clear_cart']; ?>" class="bnt_clear" onclick="location.href='flows.php?step=clear'" /></div>
    
    </div>
    <div class="cart_foot">
        <div class="linfo">
            <div class="la"><input type="checkbox" id="ck_all"><label for="ck_all">全选</label>共<span class="count"><?php echo $this->_var['total']['real_goods_count']; ?></span>件</div>
            <div class="lb"><font>合计 ¥<span class="total"><?php echo $this->_var['total']['goods_amount']; ?></span></font></div>
        </div>
        <div class="rbtn"><a href="flows.php?step=checkout">结算</a></div>
    </div>
    <?php else: ?>
    <div class="none-cont">
        <img src="themes/haohainew/images/goods_list_none.png" />
        <p>购物车还是空的，去挑选喜欢的商品吧！</p>
        <a href="index.php">去逛逛</a>
    </div>
    <?php endif; ?> 
   <?php endif; ?>
        <?php if ($this->_var['step'] == "consignee"): ?>
        
        <?php echo $this->smarty_insert_scripts(array('files'=>'region.js,utils.js')); ?>
        <script type="text/javascript">
          region.isAdmin = false;
          <?php $_from = $this->_var['lang']['flow_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
          var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          
          onload = function() {
            if (!document.all)
            {
              document.forms['theForm'].reset();
            }
          }
          
        </script>
        
        <?php $_from = $this->_var['consignee_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sn', 'consignee');if (count($_from)):
    foreach ($_from AS $this->_var['sn'] => $this->_var['consignee']):
?>
        <form action="flows.php" method="post" name="theForm" id="theForm" onsubmit="return checkConsignee(this)">
<div class="flowBox">
<h6><span><?php echo $this->_var['lang']['consignee_info']; ?></span></h6>
<?php echo $this->smarty_insert_scripts(array('files'=>'utils.js')); ?>
<table width="99%" align="center" border="0" cellpadding="5" cellspacing="1" bgcolor="#dddddd">
  <?php if ($this->_var['real_goods_count'] > 0): ?>
  
  <tr>
    <td bgcolor="#ffffff"><?php echo $this->_var['lang']['country_province']; ?>:</td>
    <td colspan="3" bgcolor="#ffffff">
    <select name="country" id="selCountries_<?php echo $this->_var['sn']; ?>" onchange="region.changed(this, 1, 'selProvinces_<?php echo $this->_var['sn']; ?>')" style="border:1px solid #ccc;">
        <option value="0"><?php echo $this->_var['lang']['please_select']; ?><?php echo $this->_var['name_of_region']['0']; ?></option>
        <?php $_from = $this->_var['country_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');if (count($_from)):
    foreach ($_from AS $this->_var['country']):
?>
        <option value="<?php echo $this->_var['country']['region_id']; ?>" <?php if ($this->_var['consignee']['country'] == $this->_var['country']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['country']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <br />
      <select name="province" id="selProvinces_<?php echo $this->_var['sn']; ?>" onchange="region.changed(this, 2, 'selCities_<?php echo $this->_var['sn']; ?>')" style="border:1px solid #ccc;">
        <option value="0"><?php echo $this->_var['lang']['please_select']; ?><?php echo $this->_var['name_of_region']['1']; ?></option>
        <?php $_from = $this->_var['province_list'][$this->_var['sn']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?>
        <option value="<?php echo $this->_var['province']['region_id']; ?>" <?php if ($this->_var['consignee']['province'] == $this->_var['province']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['province']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <br />
      <select name="city" id="selCities_<?php echo $this->_var['sn']; ?>" onchange="region.changed(this, 3, 'selDistricts_<?php echo $this->_var['sn']; ?>')" style="border:1px solid #ccc;">
        <option value="0"><?php echo $this->_var['lang']['please_select']; ?><?php echo $this->_var['name_of_region']['2']; ?></option>
        <?php $_from = $this->_var['city_list'][$this->_var['sn']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
        <option value="<?php echo $this->_var['city']['region_id']; ?>" <?php if ($this->_var['consignee']['city'] == $this->_var['city']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['city']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <br />
      <select name="district" id="selDistricts_<?php echo $this->_var['sn']; ?>" <?php if (! $this->_var['district_list'][$this->_var['sn']]): ?>style="display:none"<?php endif; ?> style="border:1px solid #ccc;">
        <option value="0"><?php echo $this->_var['lang']['please_select']; ?><?php echo $this->_var['name_of_region']['3']; ?></option>
        <?php $_from = $this->_var['district_list'][$this->_var['sn']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>
        <option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['consignee']['district'] == $this->_var['district']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['district']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
    <?php echo $this->_var['lang']['require_field']; ?> </td>
  </tr>
  <?php endif; ?>
  <tr>
    <td bgcolor="#ffffff"><?php echo $this->_var['lang']['consignee_name']; ?>:</td>
    <td bgcolor="#ffffff"><input name="consignee" type="text" class="inputBg" id="consignee_<?php echo $this->_var['sn']; ?>" value="<?php echo htmlspecialchars($this->_var['consignee']['consignee']); ?>" />
    <?php echo $this->_var['lang']['require_field']; ?> </td>
  </tr>
  <?php if ($this->_var['real_goods_count'] > 0): ?>
  
  <tr>
    <td bgcolor="#ffffff"><?php echo $this->_var['lang']['detailed_address']; ?>:</td>
    <td bgcolor="#ffffff"><input name="address" type="text" class="inputBg"  id="address_<?php echo $this->_var['sn']; ?>" value="<?php echo htmlspecialchars($this->_var['consignee']['address']); ?>" />
    <?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <?php endif; ?>
  <tr>
    <td bgcolor="#ffffff"><?php echo $this->_var['lang']['backup_phone']; ?>:</td>
    <td bgcolor="#ffffff"><input name="mobile" type="text" class="inputBg"  id="mobile_<?php echo $this->_var['sn']; ?>" value="<?php echo htmlspecialchars($this->_var['consignee']['mobile']); ?>" /></td>
  </tr>
  <tr>
    <td colspan="4" align="center" bgcolor="#ffffff">
    <input type="submit" name="Submit" class="bnt_blue_2" value="<?php echo $this->_var['lang']['shipping_address']; ?>" />
      <input type="hidden" name="step" value="consignee" />
      <input type="hidden" name="act" value="checkout" />
      <input name="address_id" type="hidden" value="<?php echo $this->_var['consignee']['address_id']; ?>" />
      </td>
  </tr>
</table>
</div>
        </form>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php endif; ?>
<?php if ($this->_var['step'] == 'address_list'): ?>
    <?php echo $this->smarty_insert_scripts(array('files'=>'utils.js,region.js,shopping_flow.js')); ?>
    <script type="text/javascript">
      region.isAdmin = false;
      <?php $_from = $this->_var['lang']['flow_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
      var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      
      onload = function() {
        if (!document.all)
        {
          document.forms['theForm'].reset();
        }
      }
      
    </script>
    <div class="consignee">
        <div class="address_list">
            <ul>
                <?php $_from = $this->_var['consignee_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sn', 'consignee');if (count($_from)):
    foreach ($_from AS $this->_var['sn'] => $this->_var['consignee']):
?>
                <li>
				    <a href="flows.php?step=checkout&address_id=<?php echo $this->_var['consignee']['address_id']; ?>" class="info">
                    <h4>收货人：<?php echo htmlspecialchars($this->_var['consignee']['consignee']); ?>　<?php echo htmlspecialchars($this->_var['consignee']['mobile']); ?></h4>
                    <p><span><?php echo $this->_var['consignee']['province_name']; ?><?php echo $this->_var['consignee']['city_name']; ?><?php echo $this->_var['consignee']['district_name']; ?><?php echo $this->_var['consignee']['address']; ?></span></p>
                    </a>
                    <p class="tools">
					    <span>
						    <?php if ($this->_var['consignee']['address_id'] == $this->_var['default_address_id']): ?>
							<a href="javascript:;" class="on"><em>默认地址</em></a>
							<?php else: ?>
							<a href="flows.php?step=set_address&id=<?php echo $this->_var['consignee']['address_id']; ?>"><em>设为默认地址</em></a>
							<?php endif; ?>
						</span>
                        <a href="javascript:;" class="bnt" onclick="IsSure();">删除</a>
                        <a href="flows.php?step=edit_consignee&address_id=<?php echo $this->_var['consignee']['address_id']; ?>" class="bnt">编辑</a>
                    </p>
                 </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
             </ul>
         </div>
         <div class="address_add"><a href="flows.php?step=edit_consignee&back_url=<?php echo $this->_var['forward']; ?>"><i class="fa fa-plus"></i>新增收货地址</a></div>
    </div> 
    <script type="text/javascript">
        function IsSure(even){
            var mes=confirm("确定删除该收获地址吗?");
            if(mes==true){ 
                   window.location='flows.php?step=drop_consignee&id=<?php echo $this->_var['consignee']['address_id']; ?> ';
                }
            else{
                return false; 
            }
        }
    </script>
    <?php endif; ?>
    
    
<?php if ($this->_var['step'] == 'shipping_list'): ?>
    <?php echo $this->smarty_insert_scripts(array('files'=>'utils.js,region.js,shopping_flow.js')); ?>
    <script type="text/javascript">
      region.isAdmin = false;
      <?php $_from = $this->_var['lang']['flow_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
      var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      
      onload = function() {
        if (!document.all)
        {
          document.forms['theForm'].reset();
        }
      }
      
    </script> 
    <div class="shipping_list">
        <ul>
            <?php $_from = $this->_var['shipping_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
        $this->_foreach['name']['iteration']++;
?>
            <li<?php if ($this->_var['item']['shipping_id'] == $this->_var['shipping_id']): ?> class="selected"<?php endif; ?> onclick="location='flows.php?step=checkout&shipping_id=<?php echo $this->_var['item']['shipping_id']; ?>'">
                <span></span>
                <h3><?php echo htmlspecialchars($this->_var['item']['shipping_name']); ?>　<?php if ($this->_var['item']['free_money'] > 0): ?><?php echo $this->_var['item']['free_money']; ?>元包邮<?php endif; ?></h3>
                <i class="fa fa-angle-right"></i>
            </li>
            <?php endforeach; else: ?>
            <div class="noshipping" onclick="history.go(-1);"><h3>您所填的收货地址无任何可用的配送方式</h3></div>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
<?php endif; ?> 
    
<?php if ($this->_var['step'] == 'point_list'): ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'utils.js,region.js,shopping_flow.js')); ?>
    <div class="shipping_list">
        <ul>
            <?php $_from = $this->_var['point_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
        $this->_foreach['name']['iteration']++;
?>
            <li<?php if ($this->_var['item']['id'] == $this->_var['point_id']): ?> class="selected"<?php endif; ?> onclick="location='flows.php?step=checkout&point_id=<?php echo $this->_var['item']['id']; ?>&shipping_id=<?php echo $this->_var['shipping_id']; ?>'">
                <span></span>
                <h3><?php echo htmlspecialchars($this->_var['item']['shop_name']); ?>　<?php echo $this->_var['item']['province']; ?><?php echo $this->_var['item']['city']; ?><?php echo $this->_var['item']['district']; ?><?php echo $this->_var['item']['address']; ?></h3>
                <i class="fa fa-angle-right"></i>
            </li>
            <?php endforeach; else: ?>
            <div class="noshipping" onclick="history.go(-1);"><h3>无任何取货地点可选</h3></div>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
<?php endif; ?>     
    
    <?php if ($this->_var['step'] == "checkout"): ?>
    <?php echo $this->smarty_insert_scripts(array('files'=>'cart_pay.js')); ?>
    <form action="flows.php?step=done" method="post" name="theForm" id="theForm" >
    <script type="text/javascript">
        var flow_no_payment = "<?php echo $this->_var['lang']['flow_no_payment']; ?>";
    </script>
	<div id="addr">
    <?php echo $this->fetch('library/consignees.lbi'); ?>
	</div>
    <div class="cart_box">
        <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'suppliers');if (count($_from)):
    foreach ($_from AS $this->_var['suppliers']):
?>
        <div class="cart_list">
            <div class="cart_supp"><img src="<?php echo empty($this->_var['suppliers']['logo']) ? $this->_var['shop_logo'] : $this->_var['suppliers']['logo']; ?>"><?php echo $this->_var['suppliers']['suppliers_name']; ?></div>
            <?php $_from = $this->_var['suppliers']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
            <div class="cart_goods checkout_goods" data-id="<?php echo $this->_var['goods']['rec_id']; ?>" id="rec_<?php echo $this->_var['goods']['rec_id']; ?>">
                <div class="c_b"><a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>"><img src="<?php echo $this->_var['goods']['goods_img']; ?>"></a></div>
                <div class="c_c">
                    <p class="tit"><?php echo $this->_var['goods']['goods_name']; ?></p>
                    <p class="attr"><?php echo $this->_var['goods']['goods_attr']; ?></p>
                    <p class="price">¥ <font><?php echo $this->_var['goods']['subtotal']; ?></font><span>X<?php echo $this->_var['goods']['goods_number']; ?></span></p>
                </div>
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            
            <?php if ($this->_var['suppliers']['bonus_list']): ?>
            <div class="djq" data-suppliers_id="<?php echo $this->_var['suppliers']['suppliers_id']; ?>">
                <span>可用代金券</span>
                <p>
                    <select name="bonus[<?php echo $this->_var['suppliers']['suppliers_id']; ?>]" class="inp">
                        <option value="0">请选择</option>
                        <?php $_from = $this->_var['suppliers']['bonus_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'bonus');if (count($_from)):
    foreach ($_from AS $this->_var['bonus']):
?>
                        <option value="<?php echo $this->_var['bonus']['bonus_id']; ?>">订单满<?php echo $this->_var['bonus']['min_goods_amount']; ?>元可用 - <?php echo $this->_var['bonus']['bonus_money_formated']; ?></option>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </select>
                </p>
            </div>
            <?php endif; ?>
  <script>
  function select_shipping(type,shipping_id,express){
    if(type==1){
      document.getElementById('addr').style.display='';
      document.getElementById('point_list').style.display='none';
	  //$("#select_point_id_"+express+" option").eq(0).attr("selected",true);
	  
    }else{
      document.getElementById('addr').style.display='none';
      document.getElementById('point_list').style.display='';
    }

  }
  </script>
            <?php if ($this->_var['suppliers']['shipping_lists']): ?>
            <div class="shipping" data-suppliers_id="<?php echo $this->_var['suppliers']['suppliers_id']; ?>">
                <span>送货方式</span>
                <?php $_from = $this->_var['suppliers']['shipping_lists']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'shipping');if (count($_from)):
    foreach ($_from AS $this->_var['shipping']):
?>
				
                <input onclick="select_shipping(<?php if ($this->_var['shipping']['shipping_code'] == 'cac'): ?>2<?php else: ?>1<?php endif; ?>,<?php echo $this->_var['shipping']['shipping_id']; ?>,'<?php echo $this->_var['suppliers']['suppliers_id']; ?>_<?php echo $this->_var['shipping']['shipping_id']; ?>')" type="radio" value="<?php echo $this->_var['shipping']['shipping_id']; ?>" name="shipping[<?php echo $this->_var['suppliers']['suppliers_id']; ?>]" id="shipping_<?php echo $this->_var['suppliers']['suppliers_id']; ?>_<?php echo $this->_var['shipping']['shipping_id']; ?>" data-express="<?php echo empty($this->_var['shipping']['id']) ? '0' : $this->_var['shipping']['id']; ?>" data-code="<?php echo $this->_var['shipping']['shipping_code']; ?>"><label for="shipping_<?php echo $this->_var['suppliers']['suppliers_id']; ?>_<?php echo $this->_var['shipping']['shipping_id']; ?>"><?php echo $this->_var['shipping']['shipping_name']; ?></label>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
            <?php endif; ?>

            
            <div class="point" id="point_list" style="display:none;">
			<?php if ($this->_var['suppliers']['point_list']): ?>
                <div class="item">
                    <span>自提地址</span>
					<p data-suppliers_id="<?php echo $this->_var['suppliers']['suppliers_id']; ?>">
                    <select name="point_id[<?php echo $this->_var['suppliers']['suppliers_id']; ?>]" id="select_point_id_<?php echo $this->_var['suppliers']['suppliers_id']; ?>" class="inp">
					    <option value="">请选择自提点</option>
                        <?php $_from = $this->_var['suppliers']['point_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                        <option value="<?php echo $this->_var['item']['id']; ?>" <?php if ($this->_var['item']['id'] == $this->_var['u_point']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['item']['shop_name']; ?> <?php echo $this->_var['item']['address']; ?> <?php echo $this->_var['item']['mobile']; ?></option>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </select>
                    </p>
                </div>
		<div class="item"><span>手机号码</span><p><input type="text" name="checked_mobile[<?php echo $this->_var['suppliers']['suppliers_id']; ?>]" class="inp" value="<?php echo $this->_var['u_mobile']; ?>"></p></div>
        <div class="item"><span>自提时间</span><p><input type="text" name="best_time[<?php echo $this->_var['suppliers']['suppliers_id']; ?>]" readonly id="appDateTime_<?php echo $this->_var['suppliers']['suppliers_id']; ?>" class="inp" value=""></p></div>
        
	<?php echo $this->smarty_insert_scripts(array('files'=>'mobiscroll2.js,mobiscroll.js,mobiscroll3.js')); ?>
	<link href="<?php echo $this->_var['hhs_css_path']; ?>/mobiscroll.css" rel="stylesheet" />
	<style>
.dwwl0,.dwwl4 {display: none;}
</style>
    <script type="text/javascript">
        $(function () {
			var currYear = (new Date()).getFullYear();	
			var nowData=new Date();
			  var ziti_start_time = <?php echo $this->_var['ziti_start_time']; ?>;
			  var ziti_end_time = <?php echo $this->_var['ziti_end_time']; ?>;
			var opt={};
			opt.date = {preset : 'date'};
			opt.datetime = {preset : 'datetime'};
			opt.time = {preset : 'time'};
			opt.default = {
				theme: 'android-ics light', //皮肤样式
		        display: 'modal', //显示方式 
		        mode: 'scroller', //日期选择模式
				dateFormat: 'yyyy年mm月dd日',
				timeFormat: 'H时',
				lang: 'zh',
				minDate:new Date(nowData.getFullYear(),nowData.getMonth(),nowData.getDate(),nowData.getHours()+ziti_start_time),
				maxDate:new Date(nowData.getFullYear(),nowData.getMonth(),nowData.getDate(),nowData.getHours()+ziti_end_time),

		        startYear: currYear, //开始年份
		        endYear: currYear //结束年份
				
			};
		  	var optDateTime = $.extend(opt['datetime'], opt['default']);
		    $("#appDateTime_<?php echo $this->_var['suppliers']['suppliers_id']; ?>").mobiscroll(optDateTime).datetime(optDateTime);
        });
    </script>   
        
        
        
<?php endif; ?>				
            </div>
            
            
            
            
            
        </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div> 
    
 

<div class="postscript">
    <input name="postscript" id="postscript" value="<?php echo htmlspecialchars($this->_var['order']['postscript']); ?>" placeholder="客官,留言调戏下客服吧! (*^__^*)~~~">
</div>
<div class="blank"></div>   
<div class="paymain">
    <h3>支付方式</h3>
<?php $_from = $this->_var['payment_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'payment');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['payment']):
        $this->_foreach['name']['iteration']++;
?>
<?php if ($this->_var['is_weixin']): ?>
  <div class="pay-box"><input type="radio" name="payment" id="payment_<?php echo $this->_var['payment']['pay_id']; ?>" value="<?php echo $this->_var['payment']['pay_id']; ?>"><label for="payment_<?php echo $this->_var['payment']['pay_id']; ?>" class="label-btn"></label><label for="payment_<?php echo $this->_var['payment']['pay_id']; ?>"><i class="ico_<?php echo $this->_var['payment']['pay_code']; ?>"></i><?php echo $this->_var['payment']['pay_name']; ?> <?php if ($this->_var['payment']['pay_code'] == 'balance'): ?>余额：<?php echo empty($this->_var['your_surplus']) ? '0.00' : $this->_var['your_surplus']; ?><?php endif; ?></label></div>
<?php else: ?>
<?php if ($this->_var['payment']['pay_code'] == 'alipay'): ?>
<div class="pay-box"><input type="radio" name="payment" id="payment_<?php echo $this->_var['payment']['pay_id']; ?>" value="<?php echo $this->_var['payment']['pay_id']; ?>"><label for="payment_<?php echo $this->_var['payment']['pay_id']; ?>" class="label-btn"></label><label for="payment_<?php echo $this->_var['payment']['pay_id']; ?>"><i class="ico_<?php echo $this->_var['payment']['pay_code']; ?>"></i><?php echo $this->_var['payment']['pay_name']; ?> </label></div>
<?php endif; ?>
<?php endif; ?>
  
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>

        </div>
        </div>
    </form>

    <div class="paydone">
      <div class="pay_l" id="pay_l_new">
<?php echo $this->fetch('library/order_totals.lbi'); ?>
          </div>
      </div>
      <div class="pay_r" id="pay_r_new">
          <input type="button" onclick="done();" value="立即支付" class="pay2_btn" style="width:100%;border-radius:0; float:right; margin:0;"/>                
      </div>
    </div>
<?php echo $this->smarty_insert_scripts(array('files'=>'utils.js')); ?>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>

var nums = <?php echo $this->_var['nums']; ?>;

function done(){
  var shipping_status = 0;
  var ok = true;
  $.each($('input[name^=shipping]'), function(index, val) {
    if($(this).attr('checked'))
    {
      ++shipping_status;
      if ($(this).attr('data-code') == 'cac') {
        var box = $(this).closest('div').next('div');

        var point_id = box.find('select').val() || 0;
        if (point_id == 0) {
          ok = false;
          alert('请选择自提点');
        }
        var phone = box.find('input[type=text]').val();
        if (! Utils.isMobile(phone)) {
          ok = false;
          alert('手机号码不正确');
        }
      }
    }
  });
  if (! ok) {return;}

  var aa=$('.shipping').length;
  if(aa<nums)
  {
    alert('商家不支持此区域配送')
    return false;
  }
  if(shipping_status<nums){
    alert('请选择配送方式');
    return false;
  }
  var payment  = $('input[name^=payment]:checked').val() || 0;
  if(payment == 0)
  {
    alert('请选择支付方式')
    return false;
  }

   //data = 'payment='+payment;
   
   $(".pay2_btn").val('正在支付');
    $.ajax({
      type: "POST",
      dataType: 'JSON',
      url: "flows.php?step=done",
      data:$("form").serialize(),
      success: function(data){
        done_response(data);
      }
   });
    //Ajax.call('flows.php?step=done', $("form").serialize(), done_response, 'POST', 'JSON');
}
//+"&lat="+lat+"&lng="+lng
function jsApiCall(code,returnrul){
	WeixinJSBridge.invoke('getBrandWCPayRequest',code,function(res){
			WeixinJSBridge.log(res.err_msg);
			//alert(res.err_code+'调试信息：'+res.err_desc+res.err_msg);		
			if(res.err_msg.indexOf('ok')>0){
				window.location.href=returnrul;
			}else{
				window.location.href=returnrul;
			}
		});
}
		function callpay(code,returnrul)
		{
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', jsApiCall);
			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			    }
			}else{
			    jsApiCall(code,returnrul);
			}
		}
		
function done_response(result){
	if(result.error==0){
		if(result.pay_code=='wxpay'){
			callpay(result.content.jsApiParameters,result.content.returnrul);
		}
		else if(result.pay_code=='alipay'){
			window.location='toalipay.php?order_id='+result.order_id;
		}
	}else if(result.error==1){
		//alert(result.url);
		window.location=result.url;
	}else if(result.error==2){
		alert(result.message);
		
	}
	
}
/*
wx.config({
    debug: false,//这里是开启测试，如果设置为true，则打开每个步骤，都会有提示，是否成功或者失败
    appId: '<?php echo $this->_var['appid']; ?>',
    timestamp: '<?php echo $this->_var['timestamp']; ?>',//这个一定要与上面的php代码里的一样。
    nonceStr: '<?php echo $this->_var['timestamp']; ?>',//这个一定要与上面的php代码里的一样。
    signature: '<?php echo $this->_var['signature']; ?>',
    jsApiList: [
      // 所有要调用的 API 都要加到这个列表中
        'checkJsApi',
        'openLocation',
        'getLocation'
    ]
});
wx.ready(function () {
    
    wx.checkJsApi({
    	
        jsApiList: [
            'getLocation'
        ],
        success: function (res) {
             //alert(JSON.stringify(res));
            // alert(JSON.stringify(res.checkResult.getLocation));
            if (res.checkResult.getLocation == false) {
            	document.forms[0].share_pay.disabled=false;
                alert('你的微信版本太低，不支持微信JS接口，请升级到最新的微信版本！');
                return;
            }
        }
    });
    wx.getLocation({
        success: function (res) {
            var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
            var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
            var speed = res.speed; // 速度，以米/每秒计
            var accuracy = res.accuracy; // 位置精度
           
            document.forms[0].share_pay.disabled=false;
            //alert(document.getElementById("share_pay").style.disabled);
            lat=latitude;
            lng=longitude;
            
        },
        cancel: function (res) {
        	document.forms[0].share_pay.disabled=false;
            //alert('用户拒绝授权获取地理位置');
        }
    });
    
   
    
}); */
</script>
    <?php endif; ?>

</div>
</body>
<script type="text/javascript">
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
<?php $_from = $this->_var['lang']['passport_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
var username_exist = "<?php echo $this->_var['lang']['username_exist']; ?>";
var compare_no_goods = "<?php echo $this->_var['lang']['compare_no_goods']; ?>";
var btn_buy = "<?php echo $this->_var['lang']['btn_buy']; ?>";
var is_cancel = "<?php echo $this->_var['lang']['is_cancel']; ?>";
var select_spe = "<?php echo $this->_var['lang']['select_spe']; ?>";
</script>

</html>
