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
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/tab.js"></script>
<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="templates/js/public_tab.js"></script>
<script>
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
<style type="text/css">
  .contitlelist .noPic{
    background-image: none;
    padding: 0 6px;
  }
  .bnts input[name="order_download"]{
      float: right;
  }
</style>
</head>

<body onload="pageheight()">
<?php echo $this->fetch('library/lift_menu.lbi'); ?>
<?php if ($this->_var['action'] == 'sale_list'): ?>

<div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_goods.png" /><span>统计报表</span>
		</div>
    <div class="maincon">
			<div class="contitlelist">
            	<span>发货订单统计</span>
                <div class="searchdiv">
                   <form name="form_order"  action="index.php" style="margin:0px" method="get">
                      <span class="noPic">订单号</span>
                      <input type="text" value="<?php echo $this->_var['filter']['order_sn']; ?>" class="input" name="order_sn">
                      <span class="noPic">微信支付单号</span>
                      <input type="text" value="<?php echo $this->_var['filter']['transaction_id']; ?>" class="input" name="transaction_id">
                      <span class="noPic">支付方式</span>
                      <select name="pay_id" id="pay_id">
                        <option value="-1">请选择</option>
                        <?php $_from = $this->_var['payment']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['v']):
?>
                          <option value="<?php echo $this->_var['v']['pay_id']; ?>" <?php if ($this->_var['filter']['pay_id'] == $this->_var['v']['pay_id']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['v']['pay_name']; ?></option>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                      </select>
                      <span class="noPic">订单状态</span>
                      <select name="composite_status" id="composite_status">
                        <option value="4" <?php if ($this->_var['filter']['composite_status'] == 4): ?> selected="selected"<?php endif; ?>>请选择</option>
                        <option value="5" <?php if ($this->_var['filter']['composite_status'] == 5): ?> selected="selected"<?php endif; ?>>待发货</option>
                        <option value="102" <?php if ($this->_var['filter']['composite_status'] == 102): ?> selected="selected"<?php endif; ?>>已完成</option>
                        <option value="3" <?php if ($this->_var['filter']['composite_status'] == 3): ?> selected="selected"<?php endif; ?>>已退款</option>
                      </select>
                      <span class="noPic">下单时间</span>
                      <input class="Wdate" value="<?php echo $this->_var['filter']['start_date']; ?>" type="text" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})" readonly name="start_time">&nbsp;&nbsp;
                      &nbsp;&nbsp;
                      <input class="Wdate" value="<?php echo $this->_var['filter']['end_date']; ?>" type="text" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})" readonly name="end_time">&nbsp;&nbsp;
                      <input name="act" type="hidden" value="sale_list" />
                      <input name="op" type="hidden" value="statistics" />
                      <input type="submit" name="submit" value="<?php echo $this->_var['lang']['query']; ?>" class="btn" />
                    </form>

                  </div>
      </div>
		  <div class="conbox">
      <form id="form_data" action="index.php" method="post" name="myform">
        <div class="bnts" style="position: static;">
           <input name="act" type="hidden" value="sale_list" />
            <input name="op" type="hidden" value="statistics" />
            <input type="button" onclick="order_d();" value="导出" name="order_download">
        </div>
       <script>
          var order_sn=document.forms['form_order'].order_sn.value;
          var transaction_id=document.forms['form_order'].transaction_id.value;
          var start_time=document.forms['form_order'].start_time.value;
          var end_time=document.forms['form_order'].end_time.value;
          var composite_status=document.forms['form_order'].composite_status.value;
          var pay_id=document.forms['form_order'].pay_id.value;
          var str="order_sn="+order_sn+"&composite_status="+composite_status+"&start_time="+start_time+"&end_time="+end_time+"&action=<?php echo $this->_var['action']; ?>"+"&pay_id="+pay_id;
          function order_d(){ 
            window.location="index.php?op=statistics&act=sale_list_download&"+str;
          }
      </script>
        <table cellspacing="0" cellpadding="0" class="listtable">
        <tr>
          <th class="center" width="50" >订单号</th>          
          <th class="center" width="80">支付时间</th>
          <th class="center" width="50">收货人</th>
          <th class="center" width="50">总金额</th>
          <th class="center" width="80">应付金额</th>
          <th class="center" width="80">红包抵扣</th>
          <th class="left" width="50">商品ID</th>
          <th class="left" >商品名称</th>
          <!--th class="center">商品数量</th-->
          <th class="center">微信单号</th>
        </tr>
        <?php $_from = $this->_var['order_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order_list_0_31897200_1497255375');if (count($_from)):
    foreach ($_from AS $this->_var['order_list_0_31897200_1497255375']):
?>
          <tr>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['order_sn']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['short_pay_time']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['consignee']; ?>[TEL:<?php if ($this->_var['order_list_0_31897200_1497255375']['mobile']): ?><?php echo $this->_var['order_list_0_31897200_1497255375']['mobile']; ?><?php else: ?><?php echo $this->_var['order_list_0_31897200_1497255375']['tel']; ?><?php endif; ?>]</td>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['formated_total_fee']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['formated_order_amount']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['bonus']; ?></td>
            <td align="left" width="80"><?php echo $this->_var['order_list_0_31897200_1497255375']['goods_idxy']; ?></td>
            <td align="left"><?php echo $this->_var['order_list_0_31897200_1497255375']['goods_namexy']; ?></td>
            <!--td align="center"><?php echo $this->_var['order_list_0_31897200_1497255375']['goods_namexy']; ?></td-->
            <td align="center"><?php if ($this->_var['order_list_0_31897200_1497255375']['transaction_id']): ?><?php echo $this->_var['order_list_0_31897200_1497255375']['transaction_id']; ?><?php else: ?>无<?php endif; ?></td>
        </tr>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
总金额：<?php echo $this->_var['totle_money']; ?>
            </form> 
      </div>
            <?php echo $this->fetch('library/pages.lbi'); ?>  
    </div>
  </div>

<?php endif; ?>
<?php if ($this->_var['action'] == 'point_list'): ?>

<div class="main" id="main">
    <div class="maintop">
      <img src="templates/images/title_goods.png" /><span>统计报表</span>
    </div>
    <div class="maincon">
      <div class="contitlelist">
              <span>自提订单统计</span>
                <div class="searchdiv">
                   <form name="form_order"  action="index.php" style="margin:0px" method="get">
                      <span class="noPic">订单号</span>
                      <input type="text" value="<?php echo $this->_var['filter']['order_sn']; ?>" class="input" name="order_sn">
                      <span class="noPic">微信支付单号</span>
                      <input type="text" value="<?php echo $this->_var['filter']['transaction_id']; ?>" class="input" name="transaction_id">
                      <span class="noPic">支付方式</span>
                      <select name="pay_id" id="pay_id">
                        <option value="-1">请选择</option>
                        <?php $_from = $this->_var['payment']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['v']):
?>
                          <option value="<?php echo $this->_var['v']['pay_id']; ?>" <?php if ($this->_var['filter']['pay_id'] == $this->_var['v']['pay_id']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['v']['pay_name']; ?></option>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                      </select>
                      <span class="noPic">自提店名称</span>
                      <select name="point_id" id="point_id">
                        <option value="-1">请选择</option>
                        <?php $_from = $this->_var['suppliers_point_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['v']):
?>
                          <option value="<?php echo $this->_var['v']['id']; ?>" <?php if ($this->_var['filter']['point_id'] == $this->_var['v']['id']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['v']['shop_name']; ?></option>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                      </select>
                      <span class="noPic">订单状态</span>
                      <select name="composite_status" id="composite_status">
                        <option value="4" <?php if ($this->_var['filter']['composite_status'] == 4): ?> selected="selected"<?php endif; ?>>请选择</option>
                        <option value="5" <?php if ($this->_var['filter']['composite_status'] == 5): ?> selected="selected"<?php endif; ?>>待核销</option>
                        <option value="102" <?php if ($this->_var['filter']['composite_status'] == 102): ?> selected="selected"<?php endif; ?>>已核销</option>
                        <option value="3" <?php if ($this->_var['filter']['composite_status'] == 3): ?> selected="selected"<?php endif; ?>>已退款</option>
                      </select>
                      <span class="noPic">下单时间</span>
                      <input class="Wdate" value="<?php echo $this->_var['filter']['start_date']; ?>" type="text" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})" readonly name="start_time">&nbsp;&nbsp;
                      &nbsp;&nbsp;
                      <input class="Wdate" value="<?php echo $this->_var['filter']['end_date']; ?>" type="text" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})" readonly name="end_time">&nbsp;&nbsp;
                      <input name="act" type="hidden" value="point_list" />
                      <input name="op" type="hidden" value="statistics" />
                      <input type="submit" name="submit" value="<?php echo $this->_var['lang']['query']; ?>" class="btn" />
                    </form>

                  </div>
      </div>
      <div class="conbox">
      <form id="form_data" action="index.php" method="post" name="myform">
        <div class="bnts" style="position: static;">
           <input name="act" type="hidden" value="point_list" />
            <input name="op" type="hidden" value="statistics" />
            <input type="button" onclick="order_d();" value="导出" name="order_download">
        </div>
       <script>
          var order_sn=document.forms['form_order'].order_sn.value;
          var transaction_id=document.forms['form_order'].transaction_id.value;
          var start_time=document.forms['form_order'].start_time.value;
          var end_time=document.forms['form_order'].end_time.value;
          var composite_status=document.forms['form_order'].composite_status.value;
          var pay_id=document.forms['form_order'].pay_id.value;
          var str="order_sn="+order_sn+"&composite_status="+composite_status+"&start_time="+start_time+"&end_time="+end_time+"&action=<?php echo $this->_var['action']; ?>"+"&pay_id="+pay_id;
          function order_d(){ 
            window.location="index.php?op=statistics&act=point_list_download&"+str;
          }
      </script>
        <table cellspacing="0" cellpadding="0" class="listtable">
        <tr>
          <th class="center" width="50">订单号</th>          
          <th class="center" width="80">支付时间</th>
          <th class="center" width="50">收货人</th>
          <th class="center" width="50">总金额</th>
          <th class="center" width="80">应付金额</th>
          <th class="center" width="80">红包抵扣</th>
          <th class="left" width="50">商品ID</th>
          <th class="left" >商品名称</th>
          <!--th class="center">商品数量</th-->
          <th class="center">微信单号</th>
        </tr>
        <?php $_from = $this->_var['order_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order_list_0_31994900_1497255375');if (count($_from)):
    foreach ($_from AS $this->_var['order_list_0_31994900_1497255375']):
?>
          <tr>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['order_sn']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['short_pay_time']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['consignee']; ?>[TEL:<?php if ($this->_var['order_list_0_31994900_1497255375']['mobile']): ?><?php echo $this->_var['order_list_0_31994900_1497255375']['mobile']; ?><?php else: ?><?php echo $this->_var['order_list_0_31994900_1497255375']['tel']; ?><?php endif; ?>]</td>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['formated_total_fee']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['formated_order_amount']; ?></td>
            <td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['bonus']; ?></td>
            <td align="left" width="80" ><?php echo $this->_var['order_list_0_31994900_1497255375']['goods_idxy']; ?></td>
            <td align="left" ><?php echo $this->_var['order_list_0_31994900_1497255375']['goods_namexy']; ?></td>
            <!--td align="center"><?php echo $this->_var['order_list_0_31994900_1497255375']['goods_namexy']; ?></td-->
            <td align="center"><?php if ($this->_var['order_list_0_31994900_1497255375']['transaction_id']): ?><?php echo $this->_var['order_list_0_31994900_1497255375']['transaction_id']; ?><?php else: ?>无<?php endif; ?></td>
        </tr>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
总金额：<?php echo $this->_var['totle_money']; ?>
            </form> 
      </div>
            <?php echo $this->fetch('library/pages.lbi'); ?>  
    </div>
  </div>
<?php endif; ?>
</body>
</html>