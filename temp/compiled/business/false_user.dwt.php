
<?php echo $this->fetch('library/header.lbi'); ?><body >
<?php echo $this->fetch('library/lift_menu.lbi'); ?>
<script type="text/javascript" src="templates/js/public_tab.js"></script>
<script type="text/javascript" src="templates/js/supp.js"></script>
<?php if ($this->_var['action'] == 'false_user'): ?>
 <div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_goods.png" /><span>虚拟会员管理</span>
		</div>
		<div class="maincon">
			<div class="contitlelist">
            	<span>会员列表</span>
                 <div class="searchdiv">
             <form method="get" action="index.php">
                <input type="text"   value="" class="input" name="keywords">
                <input type="hidden" name="act"  value="<?php echo $this->_var['action']; ?>" />
                <input type="hidden" name="op"  value="false" />
                <input type="submit" class="btn" name="" value="搜索">
            </form>
            
             </div>
             <div class="titleright"><a href="?op=false&act=add_false_user">添加虚拟用户</a></div>
            </div>

			<div class="conbox">
            <form method="post" action="index.php">
				<table cellspacing="0" cellpadding="0" class="listtable">
					<tr>
						<th><input onclick="listTable.selectAll(this, 'checkbox');" type="checkbox" name="checkbox" value="checkbox" /></th>
						<th class="left" >编号</th>
						<th class="left">会员图像</th>
						<th class="left" >会员昵称</th>
						<th class="left" >评论次数</th>
	            		<th class="left"  >会员性别</th>
						<th class="left"  >注册日期</th>
						<th  class="left" >操作列</th>
					</tr>
                    <?php $_from = $this->_var['user_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['user']):
?>
					<tr>
						<td class="checkbox"><input type="checkbox" name="checkbox[]" value="<?php echo $this->_var['user']['user_id']; ?>" /></td>
						<td class="left"><?php echo $this->_var['user']['user_id']; ?></td>
						<td><?php if ($this->_var['user']['headimgurl']): ?><img src="<?php echo $this->_var['user']['headimgurl']; ?>" /><?php else: ?>无图<?php endif; ?></td>
						<td><?php echo htmlspecialchars($this->_var['user']['user_name']); ?>[<?php if ($this->_var['user']['uname']): ?><?php echo $this->_var['user']['uname']; ?><?php else: ?><?php echo htmlspecialchars($this->_var['user']['user_name']); ?><?php endif; ?>]</td>
						<td><?php if ($this->_var['user']['comment_num']): ?><?php echo $this->_var['user']['comment_num']; ?>次<?php else: ?>暂无<?php endif; ?></td> 
	                    <td><?php if ($this->_var['user']['sex'] == 1): ?>女<?php else: ?>男<?php endif; ?></td>
						<td class="left"><?php echo $this->_var['user']['reg_time']; ?></td>
						<td class="left">
	                    	<a href="?op=false&act=edit_false_user&user_id=<?php echo $this->_var['user']['user_id']; ?>&page=<?php echo $this->_var['pager']['page']; ?>">编辑</a>
	                    </td>
					</tr>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</table>
<input name="act" type="hidden" value="my_user_batch" />
<input name="page" type="hidden" value="<?php echo $this->_var['pager']['page']; ?>" />
<input name="op" type="hidden" value="false" />
<table border="0">
	<tr>
		<td>
			<input class="button" type="submit" value="批量删除"  name="remove">
		</td>
	</tr>
</table>
</form> 
</div>
	<?php echo $this->fetch('library/pages.lbi'); ?>
</div>
</div>

<?php endif; ?>
<?php if ($this->_var['action'] == 'add_false_user' || $this->_var['action'] == 'edit_false_user'): ?>
    <!--script type="text/javascript" src="../js/goods_cat_region.js"></script-->
    <!--<script type="text/javascript" src="templates/js/jquery_common.min.js"></script>-->
   <!--script type="text/javascript" src="/js/transport.js"></script-->
    <script>
	region.isAdmin = true;
     
		function checkgoodsinfo()
		{
			var username = document.getElementById('username').value;
			var headimgurl  = document.getElementById('headimgurl').value;
		    var msg = '';
		    if(username=='')
		    {
				msg += '会员昵称不能为空。\n';
		    }
			if(headimgurl.length < 10)
			{
		
				msg += '请上传一张会员头像。\n';
			}
			if (msg.length > 0)
			{
				alert(msg);
				return false;
			}
			else
			{
				return true;
			}
		}
    </script>
 	<div class="main" id="main">
		<div class="maintop">
			<img src="templates/images/title_addgoods.png" /><span>虚拟会员管理</span>
		</div>
		<div class="maincon" style="color:#000;">
			<div class="contitleedit"><span><?php if ($this->_var['action'] == 'add_false_user'): ?>新增会员<?php else: ?>编辑会员<?php endif; ?></span></div>
      		<form action="index.php" enctype="multipart/form-data" method="post" name="myform" onsubmit="return checkgoodsinfo();">
		    <div class="conbox" >
				<table cellspacing="0" cellpadding="0" class="edittable">
					<tr>
						<td class="right" width="100">会员昵称：</td>
						<td><input type="text" value="<?php echo $this->_var['user_info']['uname']; ?>" name="username" class="input" size="35" id="username"><font style="color:#F00">*</font></td>
					</tr>
          <tr>
            <td class="right">性别:</td>
            <td>
              <input type="radio" name="sex" value="1" <?php if ($this->_var['user_info']['sex'] == 1): ?>checked="checked"<?php endif; ?>>女 
              <input type="radio" name="sex" value="2" <?php if ($this->_var['user_info']['sex'] == 2): ?>checked="checked"<?php endif; ?>>男
            </td>
          </tr>
          <tr>
			<td class="right">地区：</td>
            <td>
              <select name="province" id="selProvinces" onChange="region.changed(this, 2, 'selCities')" style="border:1px solid #ccc;">
                <option value="0">请选择省份</option>
                <?php $_from = $this->_var['province_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?>
                <option value="<?php echo $this->_var['province']['region_id']; ?>" <?php if ($this->_var['user_info']['province'] == $this->_var['province']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['province']['region_name']; ?></option>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </select>
          <select name="city" id="selCities" onChange="region.changed(this, 3, 'selDistricts')" style="border:1px solid #ccc;">
                <option value="0">请选择城市</option>
                <?php $_from = $this->_var['city_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
                <option value="<?php echo $this->_var['city']['region_id']; ?>" <?php if ($this->_var['user_info']['city'] == $this->_var['city']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['city']['region_name']; ?></option>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
           </select>
           <select name="district" id="selDistricts"  style="border:1px solid #ccc;">
                <option value="0">请选择区域</option>
                <?php $_from = $this->_var['district_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>
                <option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['user_info']['district'] == $this->_var['district']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['district']['region_name']; ?></option>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>  
              </select>
            </td>
					</tr>
          <tr>
            <td class="right" width="100">详细地址：</td>
            <td><input type="text" value="<?php echo $this->_var['user_info']['address']; ?>" name="address" class="input" size="35"></td>
          </tr>
               <tr>
                  <td  align="right">会员头像：</td>
                  <td>
                  <input name="headimgurl" type="file"  size="40" />
                  &nbsp;&nbsp;大小建议64像素*64像素&nbsp;&nbsp;   <?php if ($this->_var['user_info']['headimgurl']): ?><a  href="./../data/headimgurl/<?php echo $this->_var['user_info']['headimgurl']; ?>" target="_blank">查看</a><?php endif; ?>
                  </td>
                </tr>
        </table>
			</div>
        <table class="edittable">
			<tr>
				<td class="right">&nbsp;</td>
				<td>
	            	<input name="id" type="hidden" value="<?php echo $this->_var['user_info']['user_id']; ?>" />
	            	<input name="address_id" type="hidden" value="<?php echo $this->_var['user_info']['address_id']; ?>" />
					<input name="op" type="hidden" value="false" />
	            	<input name="act" type="hidden" value="<?php echo $this->_var['form_act']; ?>" />
	            	<input type="submit" value="保 存" class="btn">
	            </td>
			</tr>
        </table>
    </form>
  </div>
</div>
 <?php endif; ?>
</body>
</html>