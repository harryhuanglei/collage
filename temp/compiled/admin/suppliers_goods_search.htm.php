<!-- $Id: goods_search.htm 16790 2009-11-10 08:56:15Z wangleisvn $ -->
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/region.js')); ?>

<div class="form-div">
  <form action="javascript:searchGoods()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
    <?php if ($_GET['act'] != "trash"): ?>
    <!-- 分类 -->
    <select name="cat_id"><option value="0"><?php echo $this->_var['lang']['goods_cat']; ?></option><?php echo $this->_var['cat_list']; ?></select>
   
    <!-- 推荐 -->
      区域选择
     <select name="city_id" id="selCities"  onchange="region.changed(this, 3, 'selDistricts')">

          <option value=''>请选择</option>

            <?php $_from = $this->_var['cities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['region']):
?>

              <option value="<?php echo $this->_var['region']['region_id']; ?>" <?php if ($this->_var['region']['region_id'] == $this->_var['goods']['city_id']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['region']['region_name']; ?></option>

            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

        </select>
         <select name="district_id" id="selDistricts">

				<option value="0">请选择</option>

				<?php $_from = $this->_var['district_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>

				<option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['district']['region_id'] == $this->_var['goods']['district_id']): ?>selected="selected"<?php endif; ?>  ><?php echo $this->_var['district']['region_name']; ?></option>

				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

		</select> 
     <?php if ($this->_var['suppliers_exists'] == 1): ?>    
      <!-- 供货商 -->
      <select name="suppliers_id"><option value="0">商家</option><?php echo $this->html_options(array('options'=>$this->_var['suppliers_list_name'],'selected'=>$_GET['suppliers_id'])); ?></select>
      <?php endif; ?>

         <select name="goods_sale_type">
     <option value=''>类型</option>
     <option value="is_team">拼团</option>
      <option value="is_mall">商城</option>
     <option value="is_tejia">特价</option>
     <option value="is_miao">秒杀</option>
     <option value="is_zero">0元</option>
      <option value="is_fresh">新人</option>
     <option value="allow_fenxiao">分销</option>
     </select>
      <!-- 上架 -->
      <select name="is_on_sale"><option value=''><?php echo $this->_var['lang']['intro_type']; ?></option><option value="1"><?php echo $this->_var['lang']['on_sale']; ?></option><option value="0"><?php echo $this->_var['lang']['not_on_sale']; ?></option></select>
    <?php endif; ?>
    <!-- 关键字 -->
    <?php echo $this->_var['lang']['keyword']; ?> <input type="text" name="keyword" size="15" />
    审核状态
    <select name="is_check" id="is_check">
      <option value="">请选择</option>
      <option value="0">审核中</option>
      <option value="1">审核通过</option>
      <option value="2">审核未过</option>
    </select>
    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />
  </form>
</div>


<script language="JavaScript">
region.isAdmin = true;

    function searchGoods()
    {

        <?php if ($_GET['act'] != "trash"): ?>
        listTable.filter['cat_id'] = document.forms['searchForm'].elements['cat_id'].value;
        listTable.filter['is_check'] = document.forms['searchForm'].elements['is_check'].value;
        //listTable.filter['brand_id'] = document.forms['searchForm'].elements['brand_id'].value;
   
		listTable.filter['city_id'] = document.forms['searchForm'].elements['city_id'].value;
		listTable.filter['district_id'] = document.forms['searchForm'].elements['district_id'].value;
        listTable.filter['is_on_sale'] = document.forms['searchForm'].elements['is_on_sale'].value;
        listTable.filter['suppliers_id'] = document.forms['searchForm'].elements['suppliers_id'].value;
		listTable.filter['goods_sale_type'] = document.forms['searchForm'].elements['goods_sale_type'].value;

        
        <?php endif; ?>

        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;

        listTable.loadList();
    }
</script>
