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
</head>
<body> 
<?php echo $this->fetch('library/lift_menu.lbi'); ?>
<?php if ($this->_var['action'] == 'false_message'): ?>
 <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title_article.png" /><span>用户评论管理</span>

    </div>

    <div class="maincon">

      

      <div class="contitlelist">

            <span>用户评论</span>
            <div class="titleright"><a href="?op=false&act=add_false_message">添加虚拟评论</a></div>
            </div>

      <div class="conbox">

        <table cellspacing="0" cellpadding="0" class="listtable">

          <tr>

                      <th class="center">用户名称</th>

                      <th class="center">类型</th>

            <th class="center">评论对象</th>

            <th class="center">IP地址</th>

                      <th class="center">评论时间</th>

                      <th class="center">状态</th>

                      <th>操作</th>

          </tr>

                

                <?php $_from = $this->_var['comment_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment');if (count($_from)):
    foreach ($_from AS $this->_var['comment']):
?>

      <tr>

   

    <td class="center"><?php if ($this->_var['comment']['user_name']): ?><?php echo $this->_var['comment']['user_name']; ?><?php else: ?><?php echo $this->_var['lang']['anonymous']; ?><?php endif; ?></td>

    <td class="center"><?php if ($this->_var['comment']['comment_type'] == 1): ?>文章<?php else: ?>商品<?php endif; ?></td>

    <td class="center"><a href="./../<?php if ($this->_var['comment']['comment_type'] == '0'): ?>goods<?php else: ?>article<?php endif; ?>.php?id=<?php echo $this->_var['comment']['id_value']; ?>" target="_blank"><?php echo $this->_var['comment']['title']; ?></td>

    <td class="center"><?php echo $this->_var['comment']['ip_address']; ?></td>

    <td align="center"><?php echo $this->_var['comment']['add_time']; ?></td>

    <td align="center"><?php if ($this->_var['comment']['status'] == 0): ?>隐藏<?php else: ?>显示<?php endif; ?></td>

    <td align="center">

      <a href="?op=set&act=reply&amp;id=<?php echo $this->_var['comment']['comment_id']; ?>">查看详情</a> |

      <a href="?op=set&act=delete_comment&id=<?php echo $this->_var['comment']['comment_id']; ?>" onclick="return confirm('确定要此操作吗');">删除</a>

    </td>

  </tr>

    <?php endforeach; else: ?>

    <tr><td class="no-records" colspan="10"><?php echo $this->_var['lang']['no_records']; ?></td></tr>

    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>

    

        </table>

                

      </div>

    </div>

        </div>
<?php echo $this->fetch('library/pages.lbi'); ?>
<?php endif; ?>
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
<?php if ($this->_var['action'] == 'add_false_message' || $this->_var['action'] == 'edit_false_message'): ?>	
   <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_article.png" /><span>虚拟评论</span>
		</div>
		<div class="maincon">
        		<?php if ($this->_var['action'] == 'add_false_message'): ?>
				<div class="contitlelist"><span>添加虚拟评论</span><div class="titleright"><a href="?op=false&act=false_message">返回列表</a></div></div>
                <?php else: ?>
                <div class="contitlelist"><span>编辑虚拟评论</span><div class="titleright"><a href="?op=false&act=false_message">返回列表</a></div></div>
                <?php endif; ?>
			<div class="conbox">  
      <form action="index.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table width="100%" class="edittable">
  <tr>
    <td class="right">会员昵称：</td>
    <td>
      <select name="user_id"> 
        <?php $_from = $this->_var['user_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?> 
        <option value="<?php echo $this->_var['item']['user_id']; ?>" ><?php echo $this->_var['item']['uname']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>        
      </select>  
    </td>
  </tr>
  <tr>
            <td class="right" width="100">关键词：</td>
            <td><input type="text" name="keyword" size="30" id="keyw"/><input type="button" value="搜索" class="button_submit" onclick ="searchGoods();"/></td>
          </tr>
          <tr>
            <td class="right" width="100">商品名称：</td>
            <td><select name="goods_id" id="goods_name">
            <?php echo $this->_var['luckdraw_info']['option']; ?>
      </select></td>
          </tr>
          <script language="JavaScript">
            function searchGoods()
            {
              var eles = document.getElementById('keyw');

              /* 填充列表 */
              var keywords = Utils.trim(eles.value);
                $.ajax({
                    url: 'supp_false.php?is_ajax=1&act=search_goods',
                    type: 'GET',
                    data:  'keywords=' + keywords ,
                    dataType: 'JSON',
                    success: function (data) {
                      searchGoodsResponse(data);
                    }
                  });

            }
            function searchGoodsResponse(result)
            {
              var eles = document.getElementById('goods_name');
              eles.length = 0;
              if (result.error == 0)
              {
                for (i = 0; i < result.content.length; i++)
                {
                  var opt = document.createElement('OPTION');
                  opt.value = result.content[i].goods_id;
                  opt.text  = result.content[i].goods_name;
                  eles.options.add(opt);
                }
              }
            }
                  
          </script>
          </tr>
          <tr>    <td class="label">星级：</td>    <td>      <input type="radio" name="comment_rank" value="1">一星      <input type="radio" name="comment_rank" value="2">二星      <input type="radio" name="comment_rank" value="3">三星      <input type="radio" name="comment_rank" value="4" checked="checked">四星      <input type="radio" name="comment_rank" value="5">五星    </td> </tr>
  <tr>
    <td class="label">评论内容：</td>
    <td>
    
    
<textarea name="content" cols="50" rows="5"></textarea>      
      
      </td>
  </tr>
  <tr>
    <td class="right">&nbsp;</td>
    <td>
      <input type="submit" value="提交"class="btn" />
			<input type="hidden" name="op" value="false" />
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
  var goods_name = document.forms['theForm'].elements['goods_id'].value;
  var content = document.forms['theForm'].elements['content'].value;
  if(goods_name == 0)
  {
    alert('请选择评论商品');
    return false;
  }
  if(content == '')
  {
    alert('请选择评论内容');
    return false;
  }
  return true;
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
</body>
</html>