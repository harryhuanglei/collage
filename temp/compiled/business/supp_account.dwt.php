<?php echo $this->fetch('library/header.lbi'); ?>
<body >


    <?php echo $this->fetch('library/lift_menu.lbi'); ?>
    <?php if ($this->_var['action'] == 'my_order'): ?>


      <div class="main" id="main">

		<div class="maintop">

			<img src="templates/images/title_article.png" /><span>结算管理</span>

		</div>

		<div class="maincon">

    	<div class="contitlelist">        

        <span>订单结算</span>

         <div class="searchdiv">

             <form method="get" name="account_form" action="index.php">

              <div>结算状态：</div>
              <select id="settlement_status" name="settlement_status">
              <option value="">请选择</option>
              <?php echo $this->html_options(array('options'=>$this->_var['lang']['account_settlement_status'],'selected'=>$this->_var['filter']['settlement_status'])); ?>

              </select>
			 <div>结算单号：</div>
              <input type="text"  value="<?php echo $this->_var['filter']['order_sn']; ?>" class="input" name="order_sn">
              <div>结算起止时间：</div>
             <input class="Wdate" value="<?php echo $this->_var['filter']['start_time']; ?>" type="text" name="start_time" readonly onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/>     
      <input class="Wdate" type="text" value="<?php echo $this->_var['filter']['end_time']; ?>" name="end_time" readonly onfocus="WdatePicker({dateFmt:'yyyy-M-d HH:mm'})"/>              
              <input type="hidden" name="act"  value="my_order" />
               <input type="hidden" name="op"  value="account" />
              <input type="submit" class="btn" name="" value="搜索">
              </form>
        </div>
		<div class="titleright">
		<a href="javascript:void(0);" id="account_download">导出</a>
		<a href="javascript:void(0);" id="account_print" target="_blank">打印</a>
		</div>
<script>
var settlement_status=document.forms['account_form'].settlement_status.value;
var order_sn=document.forms['account_form'].order_sn.value;
var start_time=document.forms['account_form'].start_time.value;
var end_time=document.forms['account_form'].end_time.value;

var str="settlement_status="+settlement_status+"&order_sn="+order_sn+"&start_time="+start_time+"&end_time="+end_time;
document.getElementById('account_download').href="index.php?op=account&act=account_download&"+str;
document.getElementById('account_print').href="index.php?op=account&act=account_print&"+str;

</script>
        </div>

		<div class="conbox" >
    	<form id="form_data" action="suppliers.php" method="post" name="myform">
    	 <table class="listtable"  width="100%" border="0" cellpadding="5" cellspacing="1">
        <tr>
        <!-- 
        <th >
        <input type="checkbox" name="checkbox" onclick='listTable.selectAll(this, "id")' /></th>
 -->
          <th align="center" bgcolor="#FFFFFF">结算单号</th>
          <!-- <th align="center" bgcolor="#FFFFFF">结算起止时间</th> --> 		
          <th align="center" bgcolor="#FFFFFF">总金额</th>
          <th align="center" bgcolor="#FFFFFF">平台佣金</th>
          <th align="center" bgcolor="#FFFFFF">分销佣金</th>
          <th align="center" bgcolor="#FFFFFF">结算总额</th>
 		  <th align="center" bgcolor="#FFFFFF">结算时间</th>
           <th align="center" bgcolor="#FFFFFF">状态</th>
			<th align="center" bgcolor="#FFFFFF">操作</th>
        </tr>

        <?php if ($this->_var['account_list']): ?>

        <?php $_from = $this->_var['account_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>

        <tr>
<!-- 
         <td class="checkbox"><input type="checkbox"  name="id[]" id="id" value="<?php echo $this->_var['item']['id']; ?>" /></td>
 -->
          <td align="center" bgcolor="#FFFFFF"><a href="index.php?op=account&act=account_detail&suppliers_accounts_id=<?php echo $this->_var['item']['id']; ?>"><?php echo $this->_var['item']['settlement_sn']; ?></a></td>
          <!-- <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['start_time']; ?><br /><?php echo $this->_var['item']['end_time']; ?></td>
           -->
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['total']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['commission']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['fenxiao_money']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['settlement_amount']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['add_time']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['lang']['account_settlement_status'][$this->_var['item']['settlement_status']]; ?></td>

           <td align="center" bgcolor="#FFFFFF"><a href="index.php?op=account&act=account_detail&suppliers_accounts_id=<?php echo $this->_var['item']['id']; ?>">明细</a></td>

        </tr>

        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

       

        <?php else: ?>

        <tr>

          <td colspan="5" bgcolor="#FFFFFF">暂无可结算订单</td>

        </tr>

        <?php endif; ?>

      </table>

       
</form>
<?php if (1): ?>
      <div class="blank"></div>
      <?php echo $this->fetch('library/pages.lbi'); ?>
      </div>
<?php endif; ?>
         </div>

     </div>
  <?php endif; ?>
  <?php if ($this->_var['action'] == 'account_detail'): ?>

      <div class="main" id="main">

		<div class="maintop">

			<img src="templates/images/title_article.png" /><span>结算管理</span>

		</div>

		<div class="maincon">

    	<div class="contitlelist">        
        <span>订单结算明细</span> 
        <div class="titleright">
<a href="index.php?op=account&act=account_detail_download&suppliers_accounts_id=<?php echo $this->_var['suppliers_accounts_id']; ?>">导出</a>
<a target="_blank" href="index.php?op=account&act=account_detail_print&suppliers_accounts_id=<?php echo $this->_var['suppliers_accounts_id']; ?>">打印</a>
</div>   
         
        </div>

		<div class="conbox" >

    	<form id="form_data" action="index.php" method="post" name="myform">

    	 <table class="listtable"  width="100%" border="0" cellpadding="5" cellspacing="1">

        <tr>

        <th align="center" bgcolor="#FFFFFF">序号</th>
          <th align="center" bgcolor="#FFFFFF">订单号</th>
          <th align="center" bgcolor="#FFFFFF">商品名称</th>
           <th align="center" bgcolor="#FFFFFF">订单时间</th>
           <th align="center" bgcolor="#FFFFFF">商品数量</th>		
          <th align="center" bgcolor="#FFFFFF">订单金额</th>
          <th align="center" bgcolor="#FFFFFF">平台佣金</th>
          <th align="center" bgcolor="#FFFFFF">分销佣金</th>
          <th align="center" bgcolor="#FFFFFF">结算金额</th>		
        </tr>
        <?php if ($this->_var['account_detail']): ?>

        <?php $_from = $this->_var['account_detail']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['name']['iteration']++;
?>
        <tr> 
         <td align="center" bgcolor="#FFFFFF"><?php echo $this->_foreach['name']['iteration']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><a href="index.php?op=order&act=order_info&order_id=<?php echo $this->_var['item']['order_id']; ?>" title="查看详情"><?php echo $this->_var['item']['order_sn']; ?></a></td>
          
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['goods_name']; ?></td>
          
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['order_time']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['total_goods_num']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['amount']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['commission']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['fenxiao_money']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['item']['money']; ?></td>

        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		<tr>
		 <td colspan="4" ></td> 
          <td align="center" >合计</td> 
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['total_amount']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['total_commission']; ?></td>
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['total_fenxiao']; ?></td>   
          <td align="center" bgcolor="#FFFFFF"><?php echo $this->_var['total_money']; ?></td>   
        </tr>
		<tr>
		  <td colspan="4" >备注：
		    <textarea name="accounts_desc" id="accounts_desc" cols="40" rows="3"><?php echo $this->_var['accounts_desc']; ?></textarea></td>
		  <td align="center" >&nbsp;</td>
		  <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
		  <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
		  <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
		  </tr>
        <tr>
		  <td colspan="4" align="left"> 
		  <!--<input type="text" style="width:350px;" id="remark" placeholder="如有问题，请在这里提交" >
		  <input type="button" name="" value="提交" onclick="account_detail_sub()">-->

         
         <table border="0">
         <tr>
         <?php if ($this->_var['settlement_status'] == 1 || $this->_var['settlement_status'] == 10): ?>
         <td>
         <input name="check" onclick="if (confirm('确定此操作')) account_confirm('<?php echo $this->_var['suppliers_accounts_id']; ?>','account_confirm')" type="button" class="button"  value="审核">
		</td>
        <td>
         <input name="check" onclick="if (confirm('确定此操作')) account_confirm('<?php echo $this->_var['suppliers_accounts_id']; ?>','account_cancel')" type="button" class="button"  value="有疑问">
		 </td>
		  <?php elseif ($this->_var['settlement_status'] == 2 || $this->_var['settlement_status'] == 3 || $this->_var['settlement_status'] == 5): ?>
		   <td ><?php echo $this->_var['lang']['account_settlement_status'][$this->_var['settlement_status']]; ?></td>
		   <?php elseif ($this->_var['settlement_status'] == 4): ?>
		   <td>
		   <input name="check" onclick="if (confirm('确定此操作')) account_confirm('<?php echo $this->_var['suppliers_accounts_id']; ?>','check_accountok')" type="button" class="button"  value="确认账户信息">
		   &nbsp;&nbsp;&nbsp;&nbsp;
			商家开户行：<?php echo $this->_var['supp_row']['bank_name']; ?> &nbsp;&nbsp;
			户名：<?php echo $this->_var['supp_row']['bank_p_name']; ?> &nbsp;&nbsp;
			账号：<?php echo $this->_var['supp_row']['bank_account']; ?> &nbsp;&nbsp;
          </td>
          <?php elseif ($this->_var['settlement_status'] == 6): ?>
          <td><input name="check" onclick="if (confirm('确定已收款了?')) account_confirm('<?php echo $this->_var['suppliers_accounts_id']; ?>','account_receive')" type="button" class="button"  value="确定已收款">
          </td>
          <?php endif; ?>
          </tr>
          </table> 
		  </td> 
		  <td colspan="3" align="left" bgcolor="#FFFFFF"></td>
		 </tr>
		 <tr>
<td  colspan="9" align="center">
<table border="0" width="100%">
<tr>
	<th>身份：</th>
	<th>操作时间</th>
	<th>结算单状态</th>
	<th>备注</th>
</tr>
<?php $_from = $this->_var['action_list2']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
<tr>
	<td><div align="center"><?php echo $this->_var['item']['action_user']; ?></div></td>
	<td><div align="center"><?php echo $this->_var['item']['action_time']; ?></div></td>
	<td><div align="center"><?php echo $this->_var['item']['status_name']; ?></div></td>
	<td><div align="center"><?php echo $this->_var['item']['action_note']; ?></div></td>
	
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
		  </td>		 
		 </tr>
        <?php else: ?>

        <tr>

          <td colspan="7" bgcolor="#FFFFFF">无可结算订单</td>

        </tr>

        <?php endif; ?>

      </table>
</form>
      <div class="blank"></div>
      </div>
         </div>
     </div>
       <?php endif; ?>
<script>
 function account_confirm(id,type)
 {
   var remark=document.getElementById('accounts_desc').value;   
    $.ajax({
        type:"post",//请求类型
        url:"index.php?op=account",//服务器页面地址
        data:"remark="+remark+"&act="+type+"&id="+id,//参数(可有可无)
        dataType:"json",//服务器返回结果类型(可有可无)
        error:function(data){//错误处理函数(可有可无)
           alert("ajax出错啦");
        },
        success:function(data){
          alert('操作成功');
         window.location.href='index.php?op=account&act=account_detail&suppliers_accounts_id='+id;
         //account_detail&suppliers_accounts_id=46 my_order
        }
      });
    return false;       
 }
</script>
</body>
</html>