<!-- $Id: goods_list.htm 17126 2010-04-23 10:30:26Z liuhui $ -->
<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>
<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>

<div class="form-div">
  <form action="javascript:searchComment()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <?php echo $this->_var['lang']['search_comment']; ?> 昵称：<input type="text" name="uname" /> 
    分享类型：
     <select name="share_type">
    	<option value="">请选择</option>
     	<option value="1">分享给好友</option>
  		<option value="2">分享到朋友圈</option>
     <!--   
        <option value="3">分享到微博</option>-->
        <option value="4">分享到qq</option>
      
    </select>
    <select name="is_suc">
    	<option value="">是否成功</option>
     	<option value="1">成功</option>
  		<option value="2">取消</option>
    </select>
    分享时间：
    
        <input class="Wdate" type="text" name="start_time" readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/>
      ~       
      <input class="Wdate" type="text" name="end_time" readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/>
    
    <input type="submit" class="Button" value="<?php echo $this->_var['lang']['button_search']; ?>" />
    &nbsp;
    
  </form>
</div>
<!-- 商品列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start goods list -->
  <div class="list-div" id="listDiv">
<?php endif; ?>
<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('id'); "><?php echo $this->_var['lang']['record_id']; ?></a><?php echo $this->_var['sort_id']; ?>
    </th>
    <th>用户头像</th>
    <th>用户名</th>
    <th>用户ID</th>
    <th>分享类型</th>
    <th>分享状态</th>
   <th>分享时间</th>
   <th>分享链接</th>
   
  <tr>
  <?php $_from = $this->_var['share_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
  <tr>
    <td><div align="center">
      <input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['item']['id']; ?>" />
      <?php echo $this->_var['item']['id']; ?></div></td>
    
    <td align="right"><div align="center"><img src="<?php echo $this->_var['item']['headimgurl']; ?>" width="20" height="20"/></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['item']['uname']; ?></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['item']['openid']; ?></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['lang']['share_type'][$this->_var['item']['share_type']]; ?></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['lang']['share_status'][$this->_var['item']['share_status']]; ?></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['item']['add_time']; ?></div></td>
    <td align="right"><div align="center"><?php echo $this->_var['item']['link_url']; ?></div></td>

  </tr>
  <?php endforeach; else: ?>
  <tr><td class="no-records" colspan="10"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>

<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td align="right" nowrap="true">
    <?php echo $this->fetch('page.htm'); ?>
    </td>
  </tr>
</table>

<?php if ($this->_var['full_page']): ?>
</div>

<div>
  <input type="hidden" name="act" value="batch" />
  <input type="submit" value="移除" id="btnSubmit" name="btnSubmit" class="button" disabled="true" />
</div>
</form>

<script type="text/javascript">
  listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
  listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

  <?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
  listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

  
  onload = function()
  {
    startCheckOrder(); // 开始检查订单
    document.forms['listForm'].reset();
  }
  function searchComment()
  {
      var uname = Utils.trim(document.forms['searchForm'].elements['uname'].value);
	  var is_suc = Utils.trim(document.forms['searchForm'].elements['is_suc'].value);
      var start_time = Utils.trim(document.forms['searchForm'].elements['start_time'].value); 
	  var end_time = Utils.trim(document.forms['searchForm'].elements['end_time'].value); 
	  var share_type = Utils.trim(document.forms['searchForm'].elements['share_type'].value);
	  
	  
	  listTable.filter['share_type'] = share_type;
      listTable.filter['uname'] = uname;
	  listTable.filter['is_suc'] = is_suc;
	  listTable.filter['start_time'] = start_time;
	  listTable.filter['end_time'] = end_time;
      listTable.filter.page = 1;
      listTable.loadList();
      
  }


</script>
<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>