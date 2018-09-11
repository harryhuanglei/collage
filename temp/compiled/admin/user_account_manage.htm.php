<!-- $Id: user_account_manage.htm 14598 2008-05-21 07:41:15Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>
<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<div class="form-div">
  <form name="TimeInterval" action="user_account_manage.php" method="post" style="margin:0px">
    <?php echo $this->_var['lang']['start_date']; ?>&nbsp;
    <input class="Wdate" name="start_date" type="text" value='<?php echo $this->_var['start_date']; ?>' readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d',maxDate:'%y-%M-%d'})"/>&nbsp;&nbsp;
    <?php echo $this->_var['lang']['end_date']; ?>&nbsp;
    <input class="Wdate" name="end_date" type="text" value='<?php echo $this->_var['end_date']; ?>' readonly="readonly" onfocus="WdatePicker({dateFmt:'yyyy-M-d',maxDate:'%y-%M-%d'})"/>&nbsp;&nbsp;
    <input type="submit" name="submit" value="<?php echo $this->_var['lang']['query']; ?>" class="button" />
  </form>
</div>
<!-- start charger  -->
<div class="list-div">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="4" class="group-title"><?php echo $this->_var['lang']['user_account_info']; ?></th>
  </tr>
  <tr>
    <td width="20%"><a href="user_account.php?act=list&process_type=0&ispaid=1&start_date=<?php echo $this->_var['start_date']; ?>&end_date=<?php echo $this->_var['end_date']; ?>"><?php echo $this->_var['lang']['user_add_money']; ?></a></td>
    <td width="30%"><strong><?php echo $this->_var['account']['voucher_amount']; ?></strong></td>
    <td width="20%"><a href="user_account.php?act=list&process_type=1&ispaid=1&start_date=<?php echo $this->_var['start_date']; ?>&end_date=<?php echo $this->_var['end_date']; ?>"><?php echo $this->_var['lang']['user_repay_money']; ?></a></td>
    <td width="30%"><strong><?php echo $this->_var['account']['to_cash_amount']; ?></strong></td>
  </tr>
  <tr>
    <td><a href="users.php?act=list"><?php echo $this->_var['lang']['user_money']; ?></a></td>
    <td><strong><?php echo $this->_var['account']['user_money']; ?></strong></td>
    <td><a href="users.php?act=list"><?php echo $this->_var['lang']['frozen_money']; ?></a></td>
    <td><strong style="color: red"><?php echo $this->_var['account']['frozen_money']; ?></strong></td>
  </tr>
</table>
</div>
<!-- end charge -->
<br />
<!-- start -->
<div class="list-div">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th colspan="4" class="group-title"><?php echo $this->_var['lang']['surplus_info']; ?></th>
  </tr>
  <tr>
    <td width="20%"><a href="user_account_manage.php?act=surplus&start_date=<?php echo $this->_var['start_date']; ?>&end_date=<?php echo $this->_var['end_date']; ?>"><?php echo $this->_var['lang']['order_surplus']; ?></a></td>
    <td width="30%"><strong><?php echo $this->_var['account']['surplus']; ?></strong></td>
    <td width="20%"><a href="user_account_manage.php?act=surplus&start_date=<?php echo $this->_var['start_date']; ?>&end_date=<?php echo $this->_var['end_date']; ?>"><?php echo $this->_var['lang']['integral_money']; ?></a></td>
    <td width="30%"><strong ><?php echo $this->_var['account']['integral_money']; ?></strong></td>
  </tr>
  <tr>
    <td><a href="goods.php?act=list&amp;intro_type=is_new"><?php echo $this->_var['lang']['new_goods']; ?></a></td>
    <td><strong><?php echo $this->_var['goods']['new']; ?></strong></td>
    <td><a href="goods.php?act=list&amp;intro_type=is_best"><?php echo $this->_var['lang']['recommed_goods']; ?></a></td>
    <td><strong><?php echo $this->_var['goods']['best']; ?></strong></td>
  </tr>
  <tr>
    <td><a href="goods.php?act=list&amp;intro_type=is_hot"><?php echo $this->_var['lang']['hot_goods']; ?></a></td>
    <td><strong><?php echo $this->_var['goods']['hot']; ?></strong></td>
    <td><a href="goods.php?act=list&amp;intro_type=is_promote"><?php echo $this->_var['lang']['sales_count']; ?></a></td>
    <td><strong><?php echo $this->_var['goods']['promote']; ?></strong></td>
  </tr>
</table>
</div>
<!-- end  -->
<br />

<script type="Text/Javascript" language="JavaScript">
<!--
onload = function()
{
  /* 检查订单 */
  startCheckOrder();
}
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>