<!-- $Id: agency_list.htm 14216 2008-03-10 02:27:21Z testyang $ -->



<?php if ($this->_var['full_page']): ?>

<?php echo $this->fetch('pageheader.htm'); ?>

<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js,../js/region.js')); ?>

<div class="form-div">

  <form action="javascript:search_supp()" name="searchForm">

    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />

    关键字： <input type="text" name="suppliers_name" id='suppliers_name' size="15" />

   

    审核状态：

    <select name="is_check" id='is_check'>

    <option value="">请选择</option>

    <option value="0">未通过</option>

    <option value="1">已审核</option>

    <option value="2">审核中</option>

    </select>

    

    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />

    

    <input type="button" value="导出"  onclick="export_supp();"class="button" />

  </form>

</div>





<form method="post" action="" name="listForm" onsubmit="return confirm(batch_drop_confirm);">



<div class="list-div" id="listDiv">



<?php endif; ?>

  <table cellpadding="3" cellspacing="1">

    <tr>

      <th> <input onclick='listTable.selectAll(this, "checkboxes")' type="checkbox" />

          <a href="javascript:listTable.sort('suppliers_id'); "><?php echo $this->_var['lang']['record_id']; ?></a><?php echo $this->_var['sort_suppliers_id']; ?> </th>

      <th><a href="javascript:listTable.sort('suppliers_name'); "><?php echo $this->_var['lang']['suppliers_name']; ?></a><?php echo $this->_var['sort_suppliers_name']; ?></th>



     

      <th>电话</th>

      <th>注册日期</th>

      <!-- 

      <th>推荐显示</th>

      <th>商城一楼</th>

      <th>商城二楼</th> -->

      <th>审核状态</th>

      <!-- 

      <th><a href="javascript:listTable.sort('comprehensive_score'); ">综合(分)</a>不超5分</th>

      <th><a href="javascript:listTable.sort('description_score'); ">描述（分）</a></th>

      <th><a href="javascript:listTable.sort('service_score'); ">服务（分）</a></th>

      <th><a href="javascript:listTable.sort('delivery_score'); ">发货（分）</a></th>

       -->

      <th><a href="javascript:listTable.sort('sort_order'); ">排序</a></th>

      <th><?php echo $this->_var['lang']['handler']; ?></th>

    </tr>

    <?php $_from = $this->_var['suppliers_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'suppliers');if (count($_from)):
    foreach ($_from AS $this->_var['suppliers']):
?>

    <tr>

      <td align="center"><input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['suppliers']['suppliers_id']; ?>" />

        <?php echo $this->_var['suppliers']['suppliers_id']; ?></td>

      <td align="center" class="first-cell">

        <span onclick="javascript:listTable.edit(this, 'edit_suppliers_name', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo htmlspecialchars($this->_var['suppliers']['suppliers_name']); ?>      </span></td>

   

      <td align="center" ><?php echo $this->_var['suppliers']['phone']; ?></td>

      <td align="center" ><?php if ($this->_var['suppliers']['add_date']): ?><?php echo $this->_var['suppliers']['add_date']; ?><?php else: ?>暂无<?php endif; ?></td>

      <!-- 

      <td align="center" ><img src="images/<?php if ($this->_var['suppliers']['is_top']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_top', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)" /></td>

      <td align="center" ><img src="images/<?php if ($this->_var['suppliers']['is_oneshow']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_oneshow', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)" /></td>

      <td align="center" ><img src="images/<?php if ($this->_var['suppliers']['is_twoshow']): ?>yes<?php else: ?>no<?php endif; ?>.gif" onclick="listTable.toggle(this, 'toggle_twoshow', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)" /></td>

     -->

        

      <td align="center">

      <?php if ($this->_var['suppliers']['is_check'] == 1): ?>已审核&nbsp;&nbsp;<a href="suppliers.php?act=check&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&is_check=1&page=<?php echo $this->_var['filter']['page']; ?>">取消审核</a><?php endif; ?>

      <?php if ($this->_var['suppliers']['is_check'] == 2): ?>审核中&nbsp;&nbsp;<a href="suppliers.php?act=check&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&is_check=2&page=<?php echo $this->_var['filter']['page']; ?>">立即审核</a><?php endif; ?>

      <?php if ($this->_var['suppliers']['is_check'] == 0): ?>未通过&nbsp;&nbsp;<a href="suppliers.php?act=check&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&is_check=-1&page=<?php echo $this->_var['filter']['page']; ?>">继续审核</a><?php endif; ?>

      </td>

      <!-- 

      <td align="center">&nbsp;<span onclick="javascript:listTable.edit(this, 'edit_comprehensive_score', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo $this->_var['suppliers']['comprehensive_score']; ?>&nbsp;&nbsp; </span></td>

      <td align="center">&nbsp;<span onclick="javascript:listTable.edit(this, 'edit_description_score', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo $this->_var['suppliers']['description_score']; ?>&nbsp;&nbsp; </span></td>

      <td align="center">&nbsp;<span onclick="javascript:listTable.edit(this, 'edit_service_score', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo $this->_var['suppliers']['service_score']; ?>&nbsp;&nbsp; </td>

      <td align="center">&nbsp;<span onclick="javascript:listTable.edit(this, 'edit_delivery_score', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo $this->_var['suppliers']['delivery_score']; ?>&nbsp;&nbsp; </td>

       -->

      <td align="center">

      <span onclick="javascript:listTable.edit(this, 'edit_suppliers_sort_order', <?php echo $this->_var['suppliers']['suppliers_id']; ?>)"><?php echo $this->_var['suppliers']['sort_order']; ?>&nbsp;&nbsp; </span>

      

      

      </td>

      <td width="280" class="bnt_a">

<?php if ($this->_var['suppliers']['is_check'] == 1): ?>

        <a href="suppliers_goods.php?act=list&suppliers_id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>">商品</a>

        <a href="suppliers.php?act=edit&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&page=<?php echo $this->_var['filter']['page']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>">详情</a>

        <!-- 

        <a href="suppliers.php?act=factoryauthorized&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&page=<?php echo $this->_var['filter']['page']; ?>" title="授权">授权</a>

        <a href="suppliers.php?act=trademark&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>" title="商标">商标</a>

         -->

        <a href="javascript:void(0);" onclick="listTable.remove(<?php echo $this->_var['suppliers']['suppliers_id']; ?>, '<?php echo $this->_var['lang']['drop_confirm']; ?>')" title="<?php echo $this->_var['lang']['remove']; ?>"><?php echo $this->_var['lang']['remove']; ?></a> 

        <a href="suppliers.php?act=suppliers_accounts&suppliers_id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>">结算</a>

        <a href="suppliers.php?act=ad&suppliers_id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>" title="查看">广告</a>

<?php else: ?>        

        <a href="suppliers.php?act=edit&id=<?php echo $this->_var['suppliers']['suppliers_id']; ?>&page=<?php echo $this->_var['filter']['page']; ?>" title="<?php echo $this->_var['lang']['edit']; ?>">详情</a>

<?php endif; ?>

             </td>



    </tr>



    <?php endforeach; else: ?>



    <tr><td class="no-records" colspan="21"><?php echo $this->_var['lang']['no_records']; ?></td></tr>



    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>



  </table>



<table id="page-table" cellspacing="0">



  <tr>



    <td>



      <input name="remove" type="submit" id="btnSubmit" value="<?php echo $this->_var['lang']['drop']; ?>" class="button" disabled="true" />



      <input name="act" type="hidden" value="batch" />



    </td>



    <td align="right" nowrap="true">



    <?php echo $this->fetch('page.htm'); ?>



    </td>



  </tr>



</table>







<?php if ($this->_var['full_page']): ?>



</div>



</form>





<script language="JavaScript">

    function search_supp()

    {

        listTable.filter['suppliers_name'] = Utils.trim(document.forms['searchForm'].elements['suppliers_name'].value);

	

		//listTable.filter['recommend_type_name'] = Utils.trim(document.forms['searchForm'].elements['recommend_type_name'].value);

		

		

		//listTable.filter['is_oneshow'] = Utils.trim(document.forms['searchForm'].elements['is_oneshow'].value);

		//listTable.filter['is_twoshow'] = Utils.trim(document.forms['searchForm'].elements['is_twoshow'].value);

		

		//listTable.filter['site_id'] = Utils.trim(document.forms['searchForm'].elements['site_id'].value);

		listTable.filter['is_check'] = Utils.trim(document.forms['searchForm'].elements['is_check'].value);

	

		//listTable.filter['rank_id'] = Utils.trim(document.forms['searchForm'].elements['rank_id'].value);

        listTable.filter['page'] = 1;

        

        listTable.loadList();

    }



</script>



<script type="text/javascript" language="javascript">



region.isAdmin = true;



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



      // 开始检查订单



      startCheckOrder();



  }



  

  





	//导出会员信息

 function export_supp()

 {

	 var keywords = document.getElementById('suppliers_name').value || '';

	 var is_check = document.getElementById('is_check').value || '';

	 var site_id = '';//document.getElementById('site_id').value;

	 var rank_id = '';//document.getElementById('rank_id').value;

	 var recommend_type_name = '';//document.getElementById('recommend_type_name').value;

	 location.href = 'suppliers.php?act=download&keywords='+keywords+'&is_check='+is_check+'&site_id='+site_id+'&rank_id='+rank_id+'&recommend_type_name='+recommend_type_name+'&code=1';

 } 



  //-->



</script>



<?php echo $this->fetch('pagefooter.htm'); ?>



<?php endif; ?>