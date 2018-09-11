<!-- $Id: goods_list.htm 17126 2010-04-23 10:30:26Z liuhui $ -->



<?php if ($this->_var['full_page']): ?>

<?php echo $this->fetch('pageheader.htm'); ?>

<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>



<!-- 商品搜索 -->

<?php echo $this->fetch('suppliers_goods_search.htm'); ?>

<!-- 商品列表 -->

<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">

  <!-- start goods list -->

  <div class="list-div" id="listDiv">

<?php endif; ?>

<table cellpadding="3" cellspacing="1">

  <tr>

    <th>

      <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />

      <a href="javascript:listTable.sort('goods_id'); "><?php echo $this->_var['lang']['record_id']; ?></a><?php echo $this->_var['sort_goods_id']; ?>

    </th>

    

    <th>商家</th>

    <th><a href="javascript:listTable.sort('goods_name'); "><?php echo $this->_var['lang']['goods_name']; ?></a><?php echo $this->_var['sort_goods_name']; ?></th>

    <th><a href="javascript:listTable.sort('goods_sn'); "><?php echo $this->_var['lang']['goods_sn']; ?></a><?php echo $this->_var['sort_goods_sn']; ?></th>

    <th><a href="javascript:listTable.sort('shop_price'); "><?php echo $this->_var['lang']['shop_price']; ?></a><?php echo $this->_var['sort_shop_price']; ?></th>

    <th><a href="javascript:listTable.sort('team_num'); ">参团人数</a><?php echo $this->_var['sort_team_num']; ?></th>

    <th><a href="javascript:listTable.sort('team_price'); ">团购价格</a><?php echo $this->_var['sort_team_price']; ?></th>

    <th>团购销量</th>

    

        <th><a href="javascript:listTable.sort('is_best'); "><?php echo $this->_var['lang']['is_best']; ?></a><?php echo $this->_var['sort_is_best']; ?></th>

    <!--th><a href="javascript:listTable.sort('is_new'); "><?php echo $this->_var['lang']['is_new']; ?></a><?php echo $this->_var['sort_is_new']; ?></th-->

    <th><a href="javascript:listTable.sort('is_hot'); "><?php echo $this->_var['lang']['is_hot']; ?></a><?php echo $this->_var['sort_is_hot']; ?></th>



    

    <th><a href="javascript:listTable.sort('is_on_sale'); "><?php echo $this->_var['lang']['is_on_sale']; ?></a><?php echo $this->_var['sort_is_on_sale']; ?></th>

    

    <!--th><a href="javascript:listTable.sort('is_tejia'); ">特价</a><?php echo $this->_var['sort_is_tejia']; ?></th-->

    <th><a href="javascript:listTable.sort('is_fresh'); ">新人专享</a><?php echo $this->_var['sort_is_fresh']; ?></th>

    <th><a href="javascript:listTable.sort('is_zero'); ">零元购</a><?php echo $this->_var['sort_is_zero']; ?></th>
    <th><a href="javascript:listTable.sort('is_app'); ">APP专享</a><?php echo $this->_var['sort_is_zero']; ?></th>


    

    

    <th><a href="javascript:listTable.sort('is_check'); ">审核</a><?php echo $this->_var['sort_is_check']; ?></th>



    <th><a href="javascript:listTable.sort('sort_order'); "><?php echo $this->_var['lang']['sort_order']; ?></a><?php echo $this->_var['sort_sort_order']; ?></th>

    <?php if ($this->_var['use_storage']): ?>

    <th><a href="javascript:listTable.sort('goods_number'); "><?php echo $this->_var['lang']['goods_number']; ?></a><?php echo $this->_var['sort_goods_number']; ?></th>

    <?php endif; ?>

    <th><?php echo $this->_var['lang']['handler']; ?></th>

  <tr>

  <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>

  <tr>

    <td align="center"><input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['goods']['goods_id']; ?>" /><?php echo $this->_var['goods']['goods_id']; ?></td>

    

    <td align="center" class="first-cell" ><?php echo $this->_var['goods']['suppliers_name']; ?></td>

    <td align="left" class="first-cell" style="<?php if ($this->_var['goods']['is_miao']): ?>color:red;<?php endif; ?>"><span onclick="listTable.edit(this, 'edit_goods_name', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></span><?php if ($this->_var['goods']['limit_buy_one'] == 1): ?><font style="color:#F00;">[限购]<?php endif; ?></font><?php if ($this->_var['goods']['is_zero'] == 1): ?><font style="color:#F00;">[0元购]<?php endif; ?></font></td>

    <td align="center"><span onclick="listTable.edit(this, 'edit_goods_sn', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['goods_sn']; ?></span></td>

    <td align="center"><span onclick="listTable.edit(this, 'edit_goods_price', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['shop_price']; ?>



    </span></td>

    <td align="center"><span onclick="listTable.edit(this, 'edit_team_num', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['team_num']; ?>



    </span></td>

    <td align="center"><span onclick="listTable.edit(this, 'edit_team_price', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['team_price']; ?>



    </span></td>

    <td align="center"><span onclick="listTable.edit(this, 'edit_sales_num', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['sales_num']; ?>



    </span></td>

    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_best']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_best', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td>



    

    <!--td align="center"><img src="images/<?php if ($this->_var['goods']['is_new']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_new', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td-->

    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_hot']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_hot', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td>

    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_on_sale']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_on_sale', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td>

    <!--td align="center"><img src="images/<?php if ($this->_var['goods']['is_tejia']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_tejia', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td-->

    

    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_fresh']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_fresh', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td>



    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_zero']): ?>yes<?php else: ?>no<?php endif; ?>.gif"/></td>
    <td align="center"><img src="images/<?php if ($this->_var['goods']['is_app']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_app', <?php echo $this->_var['goods']['goods_id']; ?>)" /></td>



   <td align="center">

        <?php if ($this->_var['goods']['is_check'] == 1): ?>已审核&nbsp;&nbsp;<a href="suppliers_goods.php?act=check&id=<?php echo $this->_var['goods']['goods_id']; ?>&is_check=1&page=<?php echo $this->_var['filter']['page']; ?>">取消审核</a><?php endif; ?>

    <?php if ($this->_var['goods']['is_check'] == 0): ?>审核中&nbsp;&nbsp;<a href="suppliers_goods.php?act=check&id=<?php echo $this->_var['goods']['goods_id']; ?>&is_check=0&page=<?php echo $this->_var['filter']['page']; ?>">立即审核</a><?php endif; ?>

    <?php if ($this->_var['goods']['is_check'] == 2): ?><span style="color:#F00;">未通过</span>&nbsp;&nbsp;<a href="suppliers_goods.php?act=check&id=<?php echo $this->_var['goods']['goods_id']; ?>&is_check=2&page=<?php echo $this->_var['filter']['page']; ?>">继续审核</a><?php endif; ?>

    </td>

   

    <td align="center"><span onclick="listTable.edit(this, 'edit_sort_order', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['sort_order']; ?></span></td>

    <?php if ($this->_var['use_storage']): ?>

    <td align="center"><span onclick="listTable.edit(this, 'edit_goods_number', <?php echo $this->_var['goods']['goods_id']; ?>)"><?php echo $this->_var['goods']['goods_number']; ?></span></td>

    <?php endif; ?>

    <td width="200" class="bnt_a">



      <a href="suppliers_goods.php?act=edit&goods_id=<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['code'] != 'real_goods'): ?>&extension_code=<?php echo $this->_var['code']; ?><?php endif; ?>&page=<?php echo $this->_var['filter']['page']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>"><?php echo $this->_var['lang']['edit']; ?></a>

      <a href="suppliers_goods.php?act=copy&goods_id=<?php echo $this->_var['goods']['goods_id']; ?><?php if ($this->_var['code'] != 'real_goods'): ?>&extension_code=<?php echo $this->_var['code']; ?><?php endif; ?>" title="<?php echo $this->_var['lang']['copy']; ?>"><?php echo $this->_var['lang']['copy']; ?></a>

      <a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['goods']['goods_id']; ?>, '<?php echo $this->_var['lang']['trash_goods_confirm']; ?>')" title="<?php echo $this->_var['lang']['trash']; ?>"><?php echo $this->_var['lang']['trash']; ?></a>

      <?php if ($this->_var['specifications'] [ $this->_var['goods']['goods_type'] ] != ''): ?><a href="suppliers_goods.php?act=product_list&goods_id=<?php echo $this->_var['goods']['goods_id']; ?>" title="<?php echo $this->_var['lang']['item_list']; ?>"><?php echo $this->_var['lang']['item_list']; ?></a><?php endif; ?>

      <?php if ($this->_var['add_handler']): ?>

        <?php $_from = $this->_var['add_handler']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'handler');if (count($_from)):
    foreach ($_from AS $this->_var['handler']):
?>

        <a href="<?php echo $this->_var['handler']['url']; ?>&goods_id=<?php echo $this->_var['goods']['goods_id']; ?>" title="<?php echo $this->_var['handler']['title']; ?>"><?php echo $this->_var['handler']['title']; ?></a>

        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

      <?php endif; ?>

    </td>

  </tr>

  <?php endforeach; else: ?>

  <tr><td class="no-records" colspan="11"><?php echo $this->_var['lang']['no_records']; ?></td></tr>

  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>

</table>

<!-- end goods list -->



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

  <select name="type" id="selAction" onchange="changeAction()">

    <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>

    <option value="trash"><?php echo $this->_var['lang']['trash']; ?></option>

    <option value="on_sale"><?php echo $this->_var['lang']['on_sale']; ?></option>

    <option value="not_on_sale"><?php echo $this->_var['lang']['not_on_sale']; ?></option>

    <!--<option value="best"><?php echo $this->_var['lang']['best']; ?></option>

    <option value="not_best"><?php echo $this->_var['lang']['not_best']; ?></option>

    <option value="new"><?php echo $this->_var['lang']['new']; ?></option>

    <option value="not_new"><?php echo $this->_var['lang']['not_new']; ?></option>

    <option value="hot"><?php echo $this->_var['lang']['hot']; ?></option>

    <option value="not_hot"><?php echo $this->_var['lang']['not_hot']; ?></option>-->

    <option value="move_to"><?php echo $this->_var['lang']['move_to']; ?></option>

	

  </select>

  <select name="target_cat" style="display:none">

    <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option><?php echo $this->_var['cat_list']; ?>

  </select>

	<?php if ($this->_var['suppliers_list'] > 0): ?>

  <!--二级主菜单：转移供货商-->

  <select name="suppliers_id" style="display:none">

    <option value="-1"><?php echo $this->_var['lang']['select_please']; ?></option>

    <option value="0"><?php echo $this->_var['lang']['lab_to_shopex']; ?></option>

    <?php $_from = $this->_var['suppliers_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sl');$this->_foreach['sln'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sln']['total'] > 0):
    foreach ($_from AS $this->_var['sl']):
        $this->_foreach['sln']['iteration']++;
?>

      <option value="<?php echo $this->_var['sl']['suppliers_id']; ?>"><?php echo $this->_var['sl']['suppliers_name']; ?></option>

    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

  </select>

  <!--end!-->

	<?php endif; ?>  

  <?php if ($this->_var['code'] != 'real_goods'): ?>

  <input type="hidden" name="extension_code" value="<?php echo $this->_var['code']; ?>" />

  <?php endif; ?>

  <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" id="btnSubmit" name="btnSubmit" class="button" disabled="true" />

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



  /**

   * @param: bool ext 其他条件：用于转移分类

   */

  function confirmSubmit(frm, ext)

  {

      if (frm.elements['type'].value == 'trash')

      {

          return confirm(batch_trash_confirm);

      }

      else if (frm.elements['type'].value == 'not_on_sale')

      {

          return confirm(batch_no_on_sale);

      }

      else if (frm.elements['type'].value == 'move_to')

      {

          ext = (ext == undefined) ? true : ext;

          return ext && frm.elements['target_cat'].value != 0;

      }

      else if (frm.elements['type'].value == '')

      {

          return false;

      }

      else

      {

          return true;

      }

  }



  function changeAction()

  {

      var frm = document.forms['listForm'];



      // 切换分类列表的显示

      frm.elements['target_cat'].style.display = frm.elements['type'].value == 'move_to' ? '' : 'none';

			

			<?php if ($this->_var['suppliers_list'] > 0): ?>

      frm.elements['suppliers_id'].style.display = frm.elements['type'].value == 'suppliers_move_to' ? '' : 'none';

			<?php endif; ?>



      if (!document.getElementById('btnSubmit').disabled &&

          confirmSubmit(frm, false))

      {

          frm.submit();

      }

  }



</script>

<?php echo $this->fetch('pagefooter.htm'); ?>

<?php endif; ?>