<!-- $Id: ads_list.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>
<div class="form-div">
  <form action="javascript:search_ad()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    位置 <select name="position_id">
    <option value="">请选择</option>
     <!-- <?php $_from = $this->_var['ad_position_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'position');if (count($_from)):
    foreach ($_from AS $this->_var['position']):
?> -->
      <option value="<?php echo $this->_var['position']['position_id']; ?>"><?php echo $this->_var['position']['position_name']; ?></option>
      <!-- <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>-->
    </select>
    &nbsp;&nbsp;站点&nbsp;&nbsp;
   <select name="city_id" id="selCities" >
          <option value=''>请选择</option>
            <?php $_from = $this->_var['cities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['region']):
?>
              <option value="<?php echo $this->_var['region']['region_id']; ?>" <?php if ($this->_var['region']['region_id'] == $this->_var['ads']['city_id']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['region']['region_name']; ?></option>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

        </select>
  
    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />
  </form>
</div>
<form method="post" action="" name="listForm">
<!-- start ads list -->
<div class="list-div" id="listDiv">
<?php endif; ?>

<table cellpadding="3" cellspacing="1">
  <tr>
    <th><a href="javascript:listTable.sort('ad_name'); "><?php echo $this->_var['lang']['ad_name']; ?></a><?php echo $this->_var['sort_ad_name']; ?></th>
    <th>广告 </th>
    <th><a href="javascript:listTable.sort('position_id'); "><?php echo $this->_var['lang']['position_id']; ?></a><?php echo $this->_var['sort_position_id']; ?></th>
    <th>排序</th>
   <!-- <th><a href="javascript:listTable.sort('media_type'); "><?php echo $this->_var['lang']['media_type']; ?></a><?php echo $this->_var['sort_media_type']; ?></th>
    <th><a href="javascript:listTable.sort('start_date'); "><?php echo $this->_var['lang']['start_date']; ?></a><?php echo $this->_var['sort_start_date']; ?></th>
    <th><a href="javascript:listTable.sort('end_date'); "><?php echo $this->_var['lang']['end_date']; ?></a><?php echo $this->_var['sort_end_date']; ?></th>
    <th><a href="javascript:listTable.sort('click_count'); "><?php echo $this->_var['lang']['click_count']; ?></a><?php echo $this->_var['sort_click_count']; ?></th>-->
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  </tr>
  <?php $_from = $this->_var['ads_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
  <tr>
    <td align="center" class="first-cell">
    <span onclick="javascript:listTable.edit(this, 'edit_ad_name', <?php echo $this->_var['list']['ad_id']; ?>)"><?php echo htmlspecialchars($this->_var['list']['ad_name']); ?></span>
    </td>
    <td align="center" class="first-cell"><img src="../data/afficheimg/<?php echo $this->_var['list']['ad_code']; ?>" width="200"  height="50"></td>
    <td align="center"><span><?php if ($this->_var['list']['position_id'] == 0): ?><?php echo $this->_var['lang']['outside_posit']; ?><?php else: ?><?php echo $this->_var['list']['position_name']; ?><?php endif; ?></span>
    </td>
    <td align="center"><span onclick="listTable.edit(this, 'edit_sort_order', <?php echo $this->_var['list']['ad_id']; ?>)"><?php echo $this->_var['list']['order_sort']; ?></span></td>
   <!-- <td valign="middle"><span>
<div style="float:left;margin-right:10px;line-height:40px;"><?php echo $this->_var['list']['type']; ?></div>
<?php if (( $this->_var['list']['type'] == '图片' )): ?>
<div style="float:left;height:40px;max-width:200px;*width:200px;overflow:hidden;">
<img <?php if (strpos ( $this->_var['list']['ad_code'] , 'www' )): ?>src="<?php echo $this->_var['list']['ad_code']; ?>"<?php else: ?>src="../data/afficheimg/<?php echo $this->_var['list']['ad_code']; ?>" <?php endif; ?> height="40px" />
</div>
<?php endif; ?>
</span></td>
    <td align="center"><span><?php echo $this->_var['list']['start_date']; ?></span></td>
    <td align="center"><span><?php echo $this->_var['list']['end_date']; ?></span></td>
    <td align="right"><span><?php echo $this->_var['list']['click_count']; ?></span></td>
    <td align="right"><span><?php echo $this->_var['list']['ad_stats']; ?></span></td>-->
    <td align="center" class="bnt_a"><span>
      <a href="ads.php?act=edit&id=<?php echo $this->_var['list']['ad_id']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>"><?php echo $this->_var['lang']['edit']; ?></a>
      <a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['list']['ad_id']; ?>, '<?php echo $this->_var['lang']['drop_confirm']; ?>')" title="<?php echo $this->_var['lang']['remove']; ?>"><?php echo $this->_var['lang']['remove']; ?></a></span>
    </td>
  </tr>
  <?php endforeach; else: ?>
    <tr><td colspan="6" align="center" class="no-records"><?php echo $this->_var['lang']['no_ads']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  <tr>
    <td align="right" nowrap="true" colspan="6"><?php echo $this->fetch('page.htm'); ?></td>
  </tr>
</table>

<?php if ($this->_var['full_page']): ?>
</div>
<!-- end ad_position list -->
</form>
<script language="JavaScript">
    function search_ad()
    {
        listTable.filter['position_id'] = Utils.trim(document.forms['searchForm'].elements['position_id'].value);
		listTable.filter['city_id'] = Utils.trim(document.forms['searchForm'].elements['city_id'].value);
        listTable.filter['page'] = 1;
        listTable.loadList();
    }
</script>
<script type="text/javascript" language="JavaScript">
  listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
  listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

  <?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
  listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  
  onload = function()
  {
    // 开始检查订单
    startCheckOrder();
  }
  
</script>
<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>
