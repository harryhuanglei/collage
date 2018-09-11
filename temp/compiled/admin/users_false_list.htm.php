<?php if ($this->_var['full_page']): ?>

<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<div class="form-div">
  <form action="javascript:searchUser()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />

    &nbsp;<?php echo $this->_var['lang']['label_user_name']; ?> &nbsp;<input type="text" name="keyword" />
      
     <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" />
  </form>
</div>

<form method="POST" action="" name="listForm" onsubmit="return confirm_bath()">

<!-- start users list -->
<div class="list-div" id="listDiv">
<?php endif; ?>
<!--用户列表部分-->
<table cellpadding="3" cellspacing="1" class="un">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox">
      <a href="javascript:listTable.sort('user_id'); "><?php echo $this->_var['lang']['record_id']; ?></a><?php echo $this->_var['sort_user_id']; ?>
    </th>
	<th>会员图像</th>
    <th><a href="javascript:listTable.sort('user_name'); "><?php echo $this->_var['lang']['username']; ?></a><?php echo $this->_var['sort_user_name']; ?></th>
    <th>评论次数</th>
	<th>会员性别</th>
    
    <th><a href="javascript:listTable.sort('reg_time'); "><?php echo $this->_var['lang']['reg_date']; ?></a><?php echo $this->_var['sort_reg_time']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  <tr>
  <?php $_from = $this->_var['user_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['user']):
?>
  <tr>
    <td align="center"><input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['user']['user_id']; ?>" notice="<?php if ($this->_var['user']['user_money'] != 0): ?>1<?php else: ?>0<?php endif; ?>"/><?php echo $this->_var['user']['user_id']; ?></td>
    
	<td align="center"><?php if ($this->_var['user']['headimgurl']): ?><img src="<?php echo $this->_var['user']['headimgurl']; ?>" /><?php else: ?>无图<?php endif; ?></td>
	
	<td align="center" class="first-cell"><?php if ($this->_var['user']['is_subscribe'] == 1): ?><span style="color:#F00;">[粉丝]</span><?php endif; ?><?php echo htmlspecialchars($this->_var['user']['user_name']); ?>[<?php if ($this->_var['user']['uname']): ?><?php echo $this->_var['user']['uname']; ?><?php else: ?><?php echo htmlspecialchars($this->_var['user']['user_name']); ?><?php endif; ?>]</td>
    
	<td align="center"><?php if ($this->_var['user']['comment_num']): ?><?php echo $this->_var['user']['comment_num']; ?>次<?php else: ?>暂无<?php endif; ?></td>
	
	<td align="center"><?php if ($this->_var['user']['sex'] == 1): ?>女<?php else: ?>男<?php endif; ?></td>
	
    <td align="center"><?php echo $this->_var['user']['reg_time']; ?></td>
    <td align="center">
	<a href="users_false.php?act=edit&id=<?php echo $this->_var['user']['user_id']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>"><img src="images/icon_edit.gif" border="0" height="16" width="16" /></a>
      <a href="javascript:confirm_redirect('您确定要执行此操作？', 'users_false.php?act=remove&id=<?php echo $this->_var['user']['user_id']; ?>')" title="<?php echo $this->_var['lang']['remove']; ?>"><img src="images/icon_drop.gif" border="0" height="16" width="16" /></a>
    </td>
  </tr>
  <?php endforeach; else: ?>
  <tr><td class="no-records" colspan="7"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  <tr>
      <td colspan="2">
      <input type="hidden" name="act" value="batch_remove" />
      <input type="submit" id="btnSubmit" value="<?php echo $this->_var['lang']['button_remove']; ?>" disabled="true" class="button" /></td>
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
    document.forms['searchForm'].elements['keyword'].focus();
    // 开始检查订单
    startCheckOrder();
}


/**
 * 搜索用户
 */
function searchUser()
{
    listTable.filter['keywords'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
    listTable.filter['page'] = 1;
    listTable.loadList();
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
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>