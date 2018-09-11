<?php if ($this->_var['full_page']): ?>
<!-- $Id: users_list.htm 17053 2010-03-15 06:50:26Z sxc_shop $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<div class="form-div">
  <form action="javascript:searchUser()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
     <?php echo $this->_var['lang']['all_status']; ?>
    <select name="status" id="status">
      <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
      <?php echo $this->html_options(array('options'=>$this->_var['status_list'])); ?>
    </select>
    
    &nbsp;佣金状态： &nbsp;
    <select name="dstatus" id="dstatus">
      <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>
      <option value="1">已结算</option>
      <option value="2">未结算</option>
    </select>
    
    
    &nbsp;订单号： &nbsp;<input type="text" name="order_sn" value="<?php echo $this->_var['order_sn']; ?>" /> 
    &nbsp;分销会员： &nbsp;<input type="text" name="user_name" /> 
    &nbsp;所属商家： &nbsp;<input type="text" name="suppliers_name" /> 
    订单时间：
     <input class="Wdate" type="text" name="start_time" readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/>
      ~      
      <input class="Wdate" type="text" name="end_time" readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/><input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" />
  </form>
</div>

<form method="POST" action="" name="listForm" >

<!-- start users list -->
<div class="list-div" id="listDiv">
<?php endif; ?>
<!--用户列表部分-->
<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
 订单编号
    </th>
    <th>分销会员</th>
    
    <th>商品总额</th>
    <!-- <th>佣金比例</th> -->
    <th>佣金</th>
    <th>分销等级</th>
    <th>订单号</th>
    <th>商家</th>
    <th>下单时间</th>
    <th>消费会员</th>
    <th>订单状态</th>
    <th>结算状态</th> 
    <!-- <th>操作</th>-->
  </tr>
  <?php $_from = $this->_var['user_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['user']):
?>
  <tr>
    <td align="center"><?php echo $this->_var['user']['order_id']; ?></td>
    <td align="center" class="first-cell"><?php echo $this->_var['user']['distribution_user']; ?></td>
   
    <td align="center" class="first-cell"><?php echo $this->_var['user']['amount']; ?></td>
    <!-- <td align="center" class="first-cell"><?php echo $this->_var['user']['rate']; ?>%</td> -->
    <td align="center"><?php echo $this->_var['user']['money']; ?></td>
    <td align="center"><?php echo $this->_var['user']['level']; ?></td>
    <td align="center"><?php echo $this->_var['user']['order_sn']; ?></td>
    <td align="center"><?php echo $this->_var['user']['suppliers_name']; ?></td>
    <td align="center"><?php echo $this->_var['user']['add_time']; ?></td>
    <td align="center"><?php echo $this->_var['user']['buy_user']; ?></td>
    <td align="center"><?php echo $this->_var['lang']['os'][$this->_var['user']['order_status']]; ?>,<?php echo $this->_var['lang']['ps'][$this->_var['user']['pay_status']]; ?>,<?php echo $this->_var['lang']['ss'][$this->_var['user']['shipping_status']]; ?></td>
    <td align="center"><?php if ($this->_var['user']['update_at'] == 0): ?>未结算<?php else: ?>已结算 <?php echo $this->_var['user']['update_at']; ?><?php endif; ?></td>
     <!-- 
    <td align="center"><?php if ($this->_var['user']['status'] == 0): ?><a href="javascript:void(0);" onclick="settlement(<?php echo $this->_var['user']['id']; ?>,this);">结算</a><?php else: ?>已结算<?php endif; ?></td>
   -->
  </tr>
  <?php endforeach; else: ?>
  <tr><td class="no-records" colspan="12"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  <tr>
      <td colspan="4">
    </td>
      <td align="right" nowrap="true" colspan="8">
      <?php echo $this->fetch('page.htm'); ?>
      </td>
  </tr>
</table>

<?php if ($this->_var['full_page']): ?>
</div>
<!-- end users list -->
</form>
<script type="text/javascript" language="JavaScript">
<!--
listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

<?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>


onload = function()
{
  // document.forms['searchForm'].elements['keyword'].focus();
    // 开始检查订单
    startCheckOrder();
    getDownUrl();
}

/**
 * 搜索用户
 */
function searchUser()
{
    listTable.filter['composite_status'] = document.forms['searchForm'].elements['status'].value;
    listTable.filter['user_name'] = Utils.trim(document.forms['searchForm'].elements['user_name'].value);
	listTable.filter['dstatus'] = Utils.trim(document.forms['searchForm'].elements['dstatus'].value);
	listTable.filter['start_time'] = Utils.trim(document.forms['searchForm'].elements['start_time'].value);
	listTable.filter['end_time'] = Utils.trim(document.forms['searchForm'].elements['end_time'].value);
    listTable.filter['suppliers_name'] = Utils.trim(document.forms['searchForm'].elements['suppliers_name'].value);
    listTable.filter['order_sn'] = Utils.trim(document.forms['searchForm'].elements['order_sn'].value);
    listTable.filter['page'] = 1;
    listTable.loadList();
    getDownUrl();
}

function confirm_bath()
{
  userItems = document.getElementsByName('checkboxes[]');

  cfm = '<?php echo $this->_var['lang']['list_remove_confirm']; ?>';

  for (i=0; userItems[i]; i++)
  {
    if (userItems[i].checked && userItems[i].notice == 1)
    {
      cfm = '<?php echo $this->_var['lang']['list_still_accounts']; ?>' + '<?php echo $this->_var['lang']['list_remove_confirm']; ?>';
      break;
    }
  }

  return confirm(cfm);
}

function settlement(id){
	
	var args = "is_ajax=1&act=settlement_status&id=" + id + 
		"&composite_status=" + listTable.filter['composite_status'] + "&user_name=" + listTable.filter['user_name']
	      +"&dstatus="+listTable.filter['dstatus']+"&start_time="+listTable.filter['start_time']+"&end_time="+listTable.filter['end_time'];
    Ajax.call("distribution.php", args, listTable.listCallback, "GET", "JSON");
    
	//Ajax.call("distribution.php", "id="+id+"&act=settlement_status",response, "POST", "JSON");
		
}

function getDownUrl()
{
  var aTags = document.getElementsByTagName('A');
  for (var i = 0; i < aTags.length; i++)
  { 
    if (aTags[i].href.indexOf('download') >= 0)
    {
      aTags[i].href = "distribution.php?act=download&composite_status=" + listTable.filter['composite_status'] + "&user_name=" + listTable.filter['user_name']
      +"&dstatus="+listTable.filter['dstatus']+"&start_time="+listTable.filter['start_time']+"&end_time="+listTable.filter['end_time'];
    }
  }
}
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>