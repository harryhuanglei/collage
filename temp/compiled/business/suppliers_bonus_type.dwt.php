<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>商家管理平台</title>
<link href="templates/css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/user.js"></script>
<script type="text/javascript" src="../js/region.js"></script>
<script type="text/javascript" src="../js/utils.js"></script>
<script type="text/javascript" src="templates/js/main.js"></script>
<script type="text/javascript" src="templates/js/supp.js"></script>
<script type="text/javascript" src="/js/transport.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/listtable.js"></script>
<script language="javascript" type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/validator.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/selectzone.js"></script>
<script type="text/javascript" src="templates/js/public_tab.js"></script>

<script>
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
</head>
<body> 
<?php echo $this->fetch('library/lift_menu.lbi'); ?>

<?php if ($this->_var['action'] == 'bonus_by_user'): ?>

   <div class="main" id="main">
    <div class="maintop">
      <img src="templates/images/title_article.png" /><span>优惠券发放</span>
    </div>
    <div class="maincon">
        <div class="contitlelist"><span>按用户发放</span></div>
      <div class="conbox">
<div class="main-div">
<form name="theForm" action="bonus.php" method="post" onsubmit="return validate();">
<div class="form-div">
会员<?php echo $this->_var['lang']['keywords']; ?>
      <input type="text" name="keyword" size="30" />
      <input type="button" name="search" value="<?php echo $this->_var['lang']['button_search']; ?>" onclick="searchUser();" />
</div>
<div class="list-div">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th><?php echo $this->_var['lang']['userlist']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
    <th><?php echo $this->_var['lang']['send_to_user']; ?></th>
  </tr>
  <tr>
    <td width="45%" align="center">
      <select name="user_search[]" id="user_search" size="15" style="width:260px" ondblclick="addUser()" multiple="true">
      </select>
    </td>
    <td align="center">
      <p><input type="button" value="&gt;" onclick="addUser()" class="button" /></p>
      <p><input type="button" value="&lt;" onclick="delUser()" class="button" /></p>
    </td>
    <td width="45%" align="center">
      <select name="user[]" id="user" multiple="true" size="15" style="width:260px" ondblclick="delUser()">
      </select>
    </td>
  </tr>
  <tr>
    <td align="center" colspan="3"><input type="submit" name="send_user" value="<?php echo $this->_var['lang']['confirm_send_bonus']; ?>" class="button" /></td>
  </tr>
</table>
</div>
<input type="hidden" name="id" value="<?php echo $this->_var['id']; ?>" />
<input type="hidden" name="act" value="send_by_user" />
</form>
</div>
  </div></div>

<script language="JavaScript">
<!--
document.forms['theForm'].elements['keyword'].focus();

onload = function()
{
    // 开始检查订单
    // startCheckOrder();
}
/**
* 按用户名搜索用户
*/
function searchUser()
{
  var eles = document.forms['theForm'].elements;

  /* 填充列表 */
  var keywords = Utils.trim(eles['keyword'].value);
  

    $.ajax({
        url: 'bonus.php?is_ajax=1&act=search_users',
        type: 'GET',
        data:  'keywords=' + keywords ,
        dataType: 'JSON',
        success: function (data) {
          searchUserResponse(data);
        }
      });

}

function searchUserResponse(result)
{
  var eles = document.forms['theForm'].elements;
  eles['user_search[]'].length = 0;

  if (result.error == 0)
  {
    for (i = 0; i < result.content.length; i++)
    {
      var opt = document.createElement('OPTION');
      opt.value = result.content[i].user_id;
      opt.text  = result.content[i].user_name+'['+result.content[i].uname+']';
      eles['user_search[]'].options.add(opt);
    }
  }
}

function validate2()
{
    var user_rank = document['theForm2'].elements['rank_id'].value;

    if (user_rank == 0)
    {
        alert(user_rank_empty);
        return false;
    }
    return true;
}

var submiting = false;

function validate()
{
  if(!submiting)
  {
    var idArr = new Array();
    var dest = document.getElementById('user');
    for (var i = 0; i < dest.options.length; i++)
    {
        dest.options[i].selected = "true";
        idArr.push(dest.options[i].value);
    }
    if (idArr.length <= 0)
    {
        alert(user_name_empty);
        return false;
    }
    else
    {
        submiting = true;
        return true;
    }
  }
  else
  {
    alert('Submitting...');
    return false;
  }
}

  function addUser()
  {
      var src = document.getElementById('user_search');
      var dest = document.getElementById('user');

      for (var i = 0; i < src.options.length; i++)
      {
          if (src.options[i].selected)
          {
              var exist = false;
              for (var j = 0; j < dest.options.length; j++)
              {
                  if (dest.options[j].value == src.options[i].value)
                  {
                      exist = true;
                      break;
                  }
              }
              if (!exist)
              {
                  var opt = document.createElement('OPTION');
                  opt.value = src.options[i].value;
                  opt.text = src.options[i].text;
                  dest.options.add(opt);
              }
          }
      }
  }

  function delUser()
  {
      var dest = document.getElementById('user');

      for (var i = dest.options.length - 1; i >= 0 ; i--)
      {
          if (dest.options[i].selected)
          {
              dest.options[i] = null;
          }
      }
  }

//-->
</script>

<?php endif; ?>
<?php if ($this->_var['action'] == 'bonus_by_goods'): ?>
   <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_article.png" /><span>优惠券发放</span>
		</div>
		<div class="maincon">
				<div class="contitlelist"><span>按商品发放</span></div>
			<div class="conbox">
<table>
<tr>
<td>
  <form action="javascript:searchGoods()" name="searchForm">
    搜索：
    
    <select name="cat_id"><option value="0"><?php echo $this->_var['lang']['all_category']; ?></option>
  <?php echo $this->_var['cat_list']; ?>

    
    </select>
    
    <input type="text" name="keyword" size="30" />
    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button_submit" />
  </form>
  </td>
  </tr>
</table>

<form name="theForm">
<table cellspacing='1' cellpadding='3'>
  <tr>
    <th><?php echo $this->_var['lang']['all_goods']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
    <th><?php echo $this->_var['lang']['send_bouns_goods']; ?></th>
  </tr>
  <tr>
    <td width="45%" align="center"><input  type="hidden" name='use_goods_sn' size="10" /> 
      <select name="source_select" size="20" style="width:90%" ondblclick="sz.addItem(false, 'add_bonus_goods', bounsTypeId)" multiple="true">
      </select>
    </td>
    <td align="center">
      <p><input type="button" value="&gt;&gt;" onclick="sz.addItem(true, 'add_bonus_goods&op=bonus', bounsTypeId,this.form.elements['use_goods_sn'].value)" class="bonus_button" /></p>
      <p>&nbsp;</p>
      <p><input type="button" value="&gt;" onclick="sz.addItem(false, 'add_bonus_goods&op=bonus', bounsTypeId,this.form.elements['use_goods_sn'].value)" class="bonus_button" /></p>
       <p>&nbsp;</p>
      <p><input type="button" value="&lt;" onclick="sz.dropItem(false, 'drop_bonus_goods&op=bonus', bounsTypeId)" class="bonus_button" /></p>
      <p>&nbsp;</p>
      <p><input type="button" value="&lt;&lt;" onclick="sz.dropItem(true, 'drop_bonus_goods&op=bonus', bounsTypeId)" class="bonus_button" /></p>
    </td>
    <td width="45%" align="center">
      <select name="target_select" multiple="true" size="20" style="width:90%" ondblclick="sz.dropItem(false, 'drop_bonus_goods', bounsTypeId)">
        <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
        <option value="<?php echo $this->_var['goods']['goods_id']; ?>"><?php echo $this->_var['goods']['goods_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
    </td>
  </tr>
  <tr>
    <td colspan="3" align="center">

    <br />
    <input type="button"  class="button" value="<?php echo $this->_var['lang']['send']; ?>" onClick="javascript:history.back()" />
    </td>
  </td>
</table> 
</form>


			</div>
	</div>
</div>
<script language="JavaScript">
  var bounsTypeId = '<?php echo $this->_var['bonus_type']['type_id']; ?>';
  var elements    = document.forms['theForm'].elements;
  var sz          = new SelectZone(1, elements['source_select'], elements['target_select'], elements['use_goods_sn']);
  function searchGoods()
  {
    var elements  = document.forms['searchForm'].elements;
    var filters   = new Object;

    filters.suppliers_id =<?php echo $this->_var['suppliers_id']; ?>;
    filters.cat_id = elements['cat_id'].value;
	
    filters.keyword = Utils.trim(elements['keyword'].value);

    sz.loadOptions('get_goods_list&op=bonus', filters);
  }
</script>
<?php endif; ?>
<?php if ($this->_var['action'] == 'bonus_list'): ?>
  <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_goods.png" /><span>优惠券管理</span>
		</div>
        <div class="maincon">
			<div class="contitlelist">
            	<span>优惠券列表</span>
                 <div class="titleright"><a href="?op=bonus&act=bonus">优惠券类型</a></div>
            </div>
		  <div class="conbox">
          <form method="POST" action="index.php?op=bonus&act=bonus_batch&bonus_type=<?php echo $_GET['bonus_type']; ?>" name="listForm">
            <table cellpadding="3" cellspacing="1" class="listtable">
    <tr>
      <th>
        <input type="checkbox" name="checkbox" onclick='listTable.selectAll(this, "bonus_id")' /></th>
      <?php if ($this->_var['show_bonus_sn']): ?>
      <th><?php echo $this->_var['lang']['bonus_sn']; ?></th>
      <?php endif; ?>
      <th><?php echo $this->_var['lang']['bonus_type']; ?></th>
      <th><?php echo $this->_var['lang']['order_id']; ?></th>
      <th><?php echo $this->_var['lang']['user_id']; ?></th>
      <th><?php echo $this->_var['lang']['used_time']; ?></th>
      <?php if ($this->_var['show_mail']): ?>
      <th><a href="javascript:listTable.sort('emailed'); "><?php echo $this->_var['lang']['emailed']; ?></a><?php echo $this->_var['sort_emailed']; ?><?php echo $this->_var['sort_emailed']; ?></th>
      <?php endif; ?>
      <th>操作</th>
    </tr>
    <?php $_from = $this->_var['bonus_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'bonus');if (count($_from)):
    foreach ($_from AS $this->_var['bonus']):
?>
    <tr>
      <td><input type="checkbox"  name="bonus_id[]" id="bonus_id" value="<?php echo $this->_var['bonus']['bonus_id']; ?>" style="height:36px;line-height:36px;" /></td>
      <?php if ($this->_var['show_bonus_sn']): ?>
      <td><?php echo $this->_var['bonus']['bonus_sn']; ?></td>
      <?php endif; ?>
      <td><?php echo $this->_var['bonus']['type_name']; ?></td>
      <td><?php echo $this->_var['bonus']['order_sn']; ?></td>
      <td><?php if ($this->_var['bonus']['uname']): ?><?php echo $this->_var['bonus']['uname']; ?><?php else: ?><?php echo $this->_var['bonus']['user_name']; ?><?php endif; ?></td>
      <td ><?php echo $this->_var['bonus']['used_time']; ?></td>
      <?php if ($this->_var['show_mail']): ?>
      <td align="center"><?php echo $this->_var['bonus']['emailed']; ?></td>
      <?php endif; ?>
      <td align="center">
        <a href="index.php?op=bonus&act=delete_bonus_list&bonus_id=<?php echo $this->_var['bonus']['bonus_id']; ?>&bonus_type=<?php echo $_GET['bonus_type']; ?>&page=<?php echo $this->_var['pager']['page']; ?>" onclick="return confirm('确定要此操作吗');" >移除</a>
        <?php if ($this->_var['show_mail'] && $this->_var['bonus']['order_id'] == 0 && $this->_var['bonus']['email']): ?><a href="bonus.php?act=send_mail&bonus_id=<?php echo $this->_var['bonus']['bonus_id']; ?>"><?php echo $this->_var['lang']['send_mail']; ?></a><?php endif; ?></td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td class="no-records" colspan="11"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>

  <table cellpadding="4" cellspacing="0"  >
    <tr>
      <td>
      
      <input type="submit" name="bonus_list_drop" id="btnSubmit" value="<?php echo $this->_var['lang']['drop']; ?>" class="button"  />
      <?php if ($this->_var['show_mail']): ?><input type="submit" name="mail" id="btnSubmit1" value="<?php echo $this->_var['lang']['send_mail']; ?>" class="button" disabled="true" /><?php endif; ?></td>
      <td align="right"><?php echo $this->fetch('page.htm'); ?></td>
    </tr>
  </table>
  </form>
          </div>
               <?php echo $this->fetch('library/pages.lbi'); ?>
      </div>
 </div>

<?php endif; ?>
<?php if ($this->_var['action'] == 'bonus_by_print'): ?>
   <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_article.png" /><span>优惠券发放</span>
		</div>
		<div class="maincon">
				<div class="contitlelist"><span>线下发放</span></div>
			<div class="conbox">
<form action="index.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%"  class="edittable">
  <tr>
    <td class="right"><?php echo $this->_var['lang']['bonus_type_id']; ?>：</td>
    <td>
    <select name="bonus_type_id">
      <?php echo $this->html_options(array('options'=>$this->_var['type_list'],'selected'=>$_GET['id'])); ?>
    </select>
    </td>
  </tr>
   <tr>
      <td class="right"><?php echo $this->_var['lang']['send_bonus_count']; ?>：</td>
      <td>
      <input type="text" name="bonus_sum" class="input" size="30" maxlength="6" />
      </td>
    </tr>
    <td class="right">&nbsp;</td>
    <td><?php echo $this->_var['lang']['bonus_sn_notic']; ?>：</td>
   </tr>
   <tr>
   <td class="right">&nbsp;</td>
   <td>
    <input type="submit" value="立即发放" class="btn" />
 <input  type="hidden"value="bonus" class="btn"  name="op" />
  </td>
 </tr>
</table>  
<input type="hidden" name="act" value="send_by_print" />
</form>          
            
            </div>
    </div>
 </div>
<script language="JavaScript">
<!--
document.forms['theForm'].elements['bonus_sum'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("bonus_type_id", '选择类型');
    validator.required("bonus_sum",'数量不能为空');
    validator.isNumber("bonus_sum",   '数量必须是数字', true);
    return validator.passed();
}

</script>
<?php endif; ?>
<?php if ($this->_var['action'] == 'add_bonus' || $this->_var['action'] == 'edit_bonus'): ?>	
   <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_article.png" /><span>优惠券类型</span>
		</div>
		<div class="maincon">
        		<?php if ($this->_var['action'] == 'add_bonus'): ?>
				<div class="contitlelist"><span>添加优惠券类型</span><div class="titleright"><a href="?op=bonus&act=bonus">返回列表</a></div></div>
                <?php else: ?>
                <div class="contitlelist"><span>编辑优惠券类型</span><div class="titleright"><a href="?op=bonus&act=bonus">返回列表</a></div></div>
                <?php endif; ?>
			<div class="conbox">
            
          <form action="index.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%" class="edittable">
  <tr>
    <td class="right"><?php echo $this->_var['lang']['type_name']; ?>：</td>
    <td>
      <input type='text' name='type_name' class="input" maxlength="30" value="<?php echo $this->_var['bonus_arr']['type_name']; ?>" size='20' />    </td>
  </tr>
  <tr>
    <td class="right">
      <?php echo $this->_var['lang']['type_money']; ?>：</td>
    <td>
    <input type="text" name="type_money" class="input" value="<?php echo $this->_var['bonus_arr']['type_money']; ?>" size="20" />
此类型的优惠劵可以抵销的金额  </td>
  </tr>
  <tr>
    <td class="right"><?php echo $this->_var['lang']['min_goods_amount']; ?>：</td>
    <td><input name="min_goods_amount" class="input" type="text" id="min_goods_amount" value="<?php echo $this->_var['bonus_arr']['min_goods_amount']; ?>" size="20" />
只有商品总金额达到这个数的订单才能使用这种优惠劵 </td>
  </tr>
  <tr>
    <td class="right"><?php echo $this->_var['lang']['send_method']; ?>：</td>
    <td>
     <input type="radio" name="send_type" value="0" <?php if ($this->_var['bonus_arr']['send_type'] == 0): ?> checked="true" <?php endif; ?> onClick="showunit(0)"  /><?php echo $this->_var['lang']['send_by']['0']; ?>
      <input type="radio" name="send_type" value="1" <?php if ($this->_var['bonus_arr']['send_type'] == 1): ?> checked="true" <?php endif; ?> onClick="showunit(1)"  /><?php echo $this->_var['lang']['send_by']['1']; ?>
      <input type="radio" name="send_type" value="2" <?php if ($this->_var['bonus_arr']['send_type'] == 2): ?> checked="true" <?php endif; ?> onClick="showunit(2)"  /><?php echo $this->_var['lang']['send_by']['2']; ?>
      <input type="radio" name="send_type" value="3" <?php if ($this->_var['bonus_arr']['send_type'] == 3): ?> checked="true" <?php endif; ?> onClick="showunit(3)"  /><?php echo $this->_var['lang']['send_by']['3']; ?> 
      
      
      </td>
  </tr>
  <tr id="type_3" <?php if ($this->_var['bonus_arr']['is_online'] != 1): ?> style="display:none" <?php endif; ?>>
    <td class="right">参与线上领取：</td>
    <td>
      <input type="checkbox" value="1" name="is_online" <?php if ($this->_var['bonus_arr']['is_online'] == 1): ?> checked<?php endif; ?> >
    </td>
  </tr>
 <tr>
   <td class="right">是否是好友券：</td>
   <td>
      <input type="radio" name="is_share" value="0" <?php if ($this->_var['bonus_arr']['is_share'] == 0): ?> checked<?php endif; ?> onclick="document.getElementById('number_tr').style.display='none';">否      <input type="radio" name="is_share" value="1" <?php if ($this->_var['bonus_arr']['is_share'] == 1): ?> checked<?php endif; ?> onclick="document.getElementById('number_tr').style.display='';">是      <span>设置为好友券必须分享给好友使用</span> 
    </td>
 </tr>
 <tr>
   <td class="right">是否免单券：</td>
   <td>
      <input type="radio" name="free_all" value="0" <?php if ($this->_var['bonus_arr']['free_all'] == 0): ?> checked<?php endif; ?>>否      
      <input type="radio" name="free_all" value="1" <?php if ($this->_var['bonus_arr']['free_all'] == 1): ?> checked<?php endif; ?>>是
    </td>
 </tr>
 <tr>
   <td class="right">是否仅限团长使用：</td>
   <td>
      <input type="radio" name="only_first" value="0" <?php if ($this->_var['bonus_arr']['only_first'] == 0): ?> checked<?php endif; ?>>否      
      <input type="radio" name="only_first" value="1" <?php if ($this->_var['bonus_arr']['only_first'] == 1): ?> checked<?php endif; ?>>是
    </td>
 </tr> 
 <tr id="number_tr" style="display:none;">
    <td class="right">发放优惠券的数量：</td>
    <td>
      <input type="text" value="<?php echo $this->_var['bonus_arr']['number']; ?>" class="input" name="number"> 
    </td>
  </tr>
    <tr id="zxje"<?php if ($this->_var['bonus_arr']['send_type'] != 2): ?> style="display:none" <?php endif; ?>>
    <td class="right">
      <?php echo $this->_var['lang']['min_amount']; ?>：</td>
    <td>
      <input name="min_amount" class="input" type="text" id="min_amount" value="<?php echo $this->_var['bonus_arr']['min_amount']; ?>" size="20" />
	  只要订单金额达到该数值，就会发放优惠劵给用户
</td>
  </tr>
  <!--tr id='license' style="display:<?php if ($this->_var['form_act'] == 'bonus_update' && $this->_var['bonus_arr']['send_type'] == '3'): ?>table-row<?php else: ?>none<?php endif; ?>">
  <td class="right">优惠卷图片：</td>
    <td>
      <input type="file" name='bonus_img' />
      <?php if ($this->_var['bonus_arr']['bonus_img']): ?>
      <a  href="./../<?php echo $this->_var['bonus_arr']['bonus_img']; ?>" target="_blank">查看</a>
      &nbsp;大小建议107像素*48像素
      <?php endif; ?> 
      </td>
  </tr-->
  
  
  <tr>
    <td class="right">
    <?php echo $this->_var['lang']['send_startdate']; ?>：</td>
    <td>
      <input class="Wdate" name="send_start_date" type="text" size="22" value='<?php echo $this->_var['bonus_arr']['send_start_date']; ?>' readonly="readonly" onfocus="WdatePicker({minDate:'%y-%M-%d'})"/>
     如果选择按照商品发放，只有当前时间介于起始日期和截止日期之间时，此类型的优惠劵才可以发放    </td>
  </tr>
  <tr>
    <td class="right"><?php echo $this->_var['lang']['send_enddate']; ?>：</td>
    <td>
      <input class="Wdate" name="send_end_date" type="text" size="22" value='<?php echo $this->_var['bonus_arr']['send_end_date']; ?>' readonly="readonly" onfocus="WdatePicker({minDate:'%y-%M-%d'})"/></td>
  </tr>
  
  
  
  <tr>
    <td class="right">
	 
	<?php echo $this->_var['lang']['use_startdate']; ?>：</td>
    <td>
      <input class="Wdate" name="use_start_date" type="text" size="22" value='<?php echo $this->_var['bonus_arr']['use_start_date']; ?>' readonly="readonly" onfocus="WdatePicker({minDate:'%y-%M-%d'})"/>
只有当前时间介于起始日期和截止日期之间时，此类型的优惠劵才可以使用</td>
  </tr>
  <tr>
    <td class="right"><?php echo $this->_var['lang']['use_enddate']; ?>：</td>
    <td>
      <input class="Wdate" name="use_end_date" type="text" size="22" value='<?php echo $this->_var['bonus_arr']['use_end_date']; ?>' readonly="readonly" onfocus="WdatePicker({minDate:'%y-%M-%d'})"/></td>
  </tr>
  <tr>
    <td class="right">&nbsp;</td>
    <td>
      <input type="submit" value="提交"class="btn" />
			<input type="hidden" name="op" value="bonus" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_act']; ?>" />
      <input type="hidden" name="type_id" value="<?php echo $this->_var['bonus_arr']['type_id']; ?>" />    </td>
  </tr>
</table>
</form>
          
            
            </div>
      </div>
 </div>
  
<script>
document.forms['theForm'].elements['type_name'].focus();
function validate()
{
  validator = new Validator("theForm");
  validator.required("type_name",      '名称不能为空');
  validator.required("type_money",     '金额不能为空');
  validator.isNumber("type_money",     '金额必须是数字', true);
  validator.islt('send_start_date', 'send_end_date', '优惠劵发放开始日期不能大于结束日期');
  validator.islt('use_start_date', 'use_end_date', '优惠劵使用开始日期不能大于结束日期');
  if (document.getElementById(zxje).style.display == "")
  {
    var minAmount = parseFloat(document.forms['theForm'].elements['min_amount'].value);
    if (isNaN(minAmount) || minAmount <= 0)
    {
	  validator.addErrorMsg('请输入订单下限（大于0的数字）');
    }	
  }
  return validator.passed();
}
function gObj(obj)
{
  var theObj;
  if (document.getElementById)
  {
    if (typeof obj=="string") {
      return document.getElementById(obj);
    } else {
      return obj.style;
    }
  }
  return null;
}

function showunit(get_value)
{
  gObj("type_3").style.display =  (get_value == 3) ? "" : "none";
  gObj("zxje").style.display =  (get_value == 2) ? "" : "none";
  return;
}
/*
function show_hide(f)
{
	if(f==0)
	{
		$("#license").hide();
	}
	else
	{
		$("#license").show();
	}
}
*/
</script>
 <?php endif; ?>  
 
 <?php if ($this->_var['action'] == 'bonus'): ?>

  <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_goods.png" /><span>优惠券类型</span>
		</div>
        <div class="maincon">
			<div class="contitlelist">
            	<span>优惠券类型列表</span>
                 <div class="titleright"><a href="?op=bonus&act=add_bonus">添加优惠券类型</a></div>
            </div>
		  <div class="conbox">
<table cellspacing="0" cellpadding="0" class="listtable">
    <tr>
      <th class="left">类型名称</th>
      <th class="left">发放类型</th>
      <th class="left">优惠劵金额</th>
      <th class="left">订单下限</th>
      <th class="left">发放数量</th>
      <th class="left">使用数量</th>
        <th class="left">操作</th>
      
    </tr>
    <?php $_from = $this->_var['type_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'type');$this->_foreach['id'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['id']['total'] > 0):
    foreach ($_from AS $this->_var['type']):
        $this->_foreach['id']['iteration']++;
?>
    <tr>
      <td><?php echo htmlspecialchars($this->_var['type']['type_name']); ?></td>
      <td><?php echo $this->_var['type']['send_by']; ?></td>
      <td><?php echo $this->_var['type']['type_money']; ?></td>
      <td><?php echo $this->_var['type']['min_goods_amount']; ?></td>
      <td><?php echo $this->_var['type']['send_count']; ?></td>
       <td><?php echo $this->_var['type']['use_count']; ?></td>
      <td>
       <?php if ($this->_var['type']['send_type'] == 3): ?>
        <a href="?op=bonus&act=gen_bonus_excel&tid=<?php echo $this->_var['type']['type_id']; ?>"><?php echo $this->_var['lang']['report_form']; ?></a> |
        <?php endif; ?>
        <?php if ($this->_var['type']['send_type'] != 2): ?>
        <a href="?op=bonus&act=send_bonus&amp;id=<?php echo $this->_var['type']['type_id']; ?>&amp;send_by=<?php echo $this->_var['type']['send_type']; ?>"><?php echo $this->_var['lang']['send']; ?></a> |
        <?php endif; ?>
        <a href="?op=bonus&act=bonus_list&amp;bonus_type=<?php echo $this->_var['type']['type_id']; ?>"><?php echo $this->_var['lang']['view']; ?></a> |
        <a href="?op=bonus&act=edit_bonus&amp;type_id=<?php echo $this->_var['type']['type_id']; ?>"><?php echo $this->_var['lang']['edit']; ?></a> |
        <a href="?op=bonus&act=delete_bonus&amp;type_id=<?php echo $this->_var['type']['type_id']; ?>" onclick="return confirm('确定要此操作吗');">移除</a></span></td>
 </td> 
    </tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>
        </div>
             <?php echo $this->fetch('library/pages.lbi'); ?>
       </div>
        </div>
 <?php endif; ?> 
    
</body>
</html>