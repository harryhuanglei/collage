<?php echo $this->fetch('library/header.lbi'); ?>
<body >

<?php echo $this->fetch('library/lift_menu.lbi'); ?>
<?php if ($this->_var['action'] == 'bank_config'): ?>

    <script>
    function bank_config()
    {
      var bank_name = document.myform.bank_name.value;
      var bank_p_name = document.myform.bank_p_name.value;  
      var bank_account = document.myform.bank_account.value;
      //var bank_password = document.myform.bank_password.value;  
      if(bank_name=='')
      {
        alert('开户行名称不能为空');
        return false; 
      }
      if(bank_p_name=='')
      {
        alert('开户行姓名不能为空');
        return false; 
      }
      if(bank_account=='')
      {
        alert('银行账号不能为空');  
        return false;
      }
//      if(bank_password=='')
//      {
//        alert('提现密码不能为空');  
//        return false;
//      }
      
      if(bank_name.length>50)
      {
        alert('开户行名称不大于50个汉字');
        return false;
      }
      if(bank_p_name.length>30)
      {
        alert('开户行姓名不大于10个汉字或字母');
        return false;
      }
      var reg = /^[\d|\-|\s]+$/;
      if (!reg.test(bank_account)||bank_account.length>20)
      {
        alert('账号格式不正确');
        return false;
      }

//      if (!reg.test(bank_password)||bank_password.length!=6)
//      {
//        alert('提现密码格式不正确');
//        return false;
//      }
      
      return true;
      
    }
  
  </script>

      <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title_article.png" /><span>结算管理</span>

    </div>

    <div class="maincon">

      

      <div class="contitleedit">

            <span>账号设置</span>

            </div>

      <div class="conbox" style="font-size:16px;">

           <form action="index.php" name="myform" method="post" onsubmit="return bank_config();">

        
            

               <table width="100%" border="0" cellpadding="5" class="edittable" cellspacing="1">

        <div style="font-size:16px; text-align:center; color:#f60; padding-top:20px;">注意事项：开户行设置只能提交一次，提交后不可再编辑，请您确认好账户信息，如您的账户需要发生变更时，请于平台联系。</div>  
        
                 
                
                <tr>

                  <td width="28%" align="right" bgcolor="#FFFFFF">开户行名称： </td>

                  <td width="72%" align="left" bgcolor="#FFFFFF">
        
               <?php if ($this->_var['supp_row']['bank_name']): ?>
                <?php echo $this->_var['supp_row']['bank_name']; ?>
               <?php else: ?>
               
                 <input name="bank_name"  id='bank_name' type="text" class="input" value="<?php echo $this->_var['supp_row']['bank_name']; ?>" />可以输入50个汉字字符
               <?php endif; ?>
                 

                  </td>

                </tr>

                <tr>

                  <td align="right" bgcolor="#FFFFFF">开户行姓名：</td>

                  <td align="left" bgcolor="#FFFFFF">
                  
                  <?php if ($this->_var['supp_row']['bank_name']): ?>
                    <?php echo $this->_var['supp_row']['bank_p_name']; ?>
                  <?php else: ?>
                    <input type="text" name="bank_p_name" id='bank_p_name' class="input" value="<?php echo $this->_var['supp_row']['bank_p_name']; ?>" />
可以输入个10汉字或字母
                  <?php endif; ?>
                  
                </td>

                </tr>

                <tr>

                  <td align="right" bgcolor="#FFFFFF">开户行账号：</td>

                  <td align="left" bgcolor="#FFFFFF">
                  <?php if ($this->_var['supp_row']['bank_name']): ?>
                  <?php echo $this->_var['supp_row']['bank_account']; ?>
                  <?php else: ?>
                  <input type="text" name="bank_account" id='bank_account' class="input"  value="<?php echo $this->_var['supp_row']['bank_account']; ?>" />
                  可以输入20个数字
                  <?php endif; ?>
                  
                  
                  </td>

                </tr>
                <?php if ($this->_var['supp_row']['bank_name']): ?>
                <?php else: ?>
                <tr>

                  <td>&nbsp;</td>
        
                  <td bgcolor="#FFFFFF">
                  <input name="act" type="hidden" value="update_shipping_type" />
                  <input name="op" type="hidden" value="set" />
                    <input name="submit" type="submit" value="提交" class="btn" style="border:none;" />
                  </td>
        
                </tr>
                <?php endif; ?>

       </table> 

                

                </form>

            </div>

    </div>

        </div>
   <?php endif; ?> 
<?php if ($this->_var['action'] == 'supp_info'): ?>

   
    <script>
  region.isAdmin = true;
  </script>

  <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title_article.png" /><span>店铺资料</span>

    </div>
    <div class="maincon">
        <div class="contitleedit"><span>资料更新</span></div>
      <div class="conbox">
            <form action="index.php" enctype="multipart/form-data" method="post" name="myform">
           <table cellspacing="0" cellpadding="0" class="edittable">
          <tr>
            <td class="right">商家名称：</td>
            <td><?php if ($this->_var['supp_list']['is_check'] == 1): ?> <?php echo $this->_var['supp_list']['suppliers_name']; ?> <?php else: ?>
                           <input name="suppliers_name" type="text" size="40" id="suppliers_name"  class="input" value="<?php echo $this->_var['supp_list']['suppliers_name']; ?>" />
                           <?php endif; ?>
                        
                        </td>
          </tr>
                        <tr>
                  <td  align="right">店铺logo：</td>
                  <td>
                  <input name="supp_logo" type="file"  size="40" />
                  &nbsp;&nbsp;大小建议80像素*80像素&nbsp;&nbsp;   <?php if ($this->_var['supp_list']['supp_logo']): ?><a  href="./../<?php echo $this->_var['supp_list']['supp_logo']; ?>" target="_blank">查看</a><?php endif; ?>
                  </td>
                </tr>
                
                
                  <tr>
<!--            <td class="right">经度：</td>
            <td>
            <input name="longitude" type="text" size="10" id="longitude"  class="input" value="<?php echo $this->_var['supp_list']['longitude']; ?>" />                      </td>
          </tr>
                    <tr>
            <td class="right">纬度：</td>
            <td>
            <input name="latitude" type="text" size="10" id="latitude"  class="input" value="<?php echo $this->_var['supp_list']['latitude']; ?>" />
                      <a href="http://api.map.baidu.com/lbsapi/getpoint/" target="_blank">获取坐标</a>               </td>
          </tr> -->
                
                 
          
               

<!--           <tr>
            <td width="30%" align="right">店铺形象照片：</td>
            <td>
      <input name="supp_banner" type="file"  size="40" />&nbsp;&nbsp;大小建议1200像素*200像素&nbsp;&nbsp;

                  <?php if ($this->_var['supp_list']['supp_banner']): ?><a  href="./../<?php echo $this->_var['supp_list']['supp_banner']; ?>" target="_blank">查看</a><?php endif; ?>
            </td>
          </tr> -->
         
        
        
                     <tr>
            <td class="right">区域选择：</td>
                        <td>
      	  <select name="city_id" class="input"   id="selCities"  onchange="region.changed(this, 3, 'selDistricts')">
          <option value='1'>全国</option>
            <?php $_from = $this->_var['cities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['region']):
?>
              <option value="<?php echo $this->_var['region']['region_id']; ?>" <?php if ($this->_var['region']['region_id'] == $this->_var['supp_list']['city_id']): ?>selected="selected"<?php endif; ?>><?php echo $this->_var['region']['region_name']; ?></option>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

        </select>
         <select name="district_id"  class="input"   id="selDistricts">

				<option value="0">请选择</option>

				<?php $_from = $this->_var['district_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>

				<option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['district']['region_id'] == $this->_var['supp_list']['district_id']): ?>selected="selected"<?php endif; ?>  ><?php echo $this->_var['district']['region_name']; ?></option>

				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

		</select>              
                        </td>
          </tr>
                   
                   <tr>
                
          <td width="30%" align="right">详细地址：</td>
          <td>
          <input name="address" type="text" size="40" id="address"  class="input"  value="<?php echo $this->_var['supp_list']['address']; ?>"/>
          </td>

        </tr>
    <tr>

          <td align="right">在线客服：</td>

          <td>

          <input name="qq" type="text"  size="40" class="input"  value="<?php echo $this->_var['supp_list']['qq']; ?>" />

            <span style="color:#FF0000" id="password_notice"> *申请网址 <a href="http://qiao.baidu.com/" target="_blank">http://qiao.baidu.com/</a></span>

          </td>

        </tr>
         <tr>

          <td width="30%" align="right">邮箱：</td>

          <td>

          <input name="email" type="text" size="40" id="email" class="input"  value="<?php echo $this->_var['supp_list']['email']; ?>" />

          </td>

        </tr>
        <?php if (0): ?>
        <tr>

          <td width="30%" align="right">备用邮箱：</td>
          <td>
          <input name="email1" type="text" size="40" id="email1" class="input"  value="<?php echo $this->_var['supp_list']['email1']; ?>" />
          </td>
        </tr>
        <?php endif; ?>
        
        <tr>
          <td width="30%" align="right">手机号码：</td>
          <td>
          <input name="phone" type="text" size="40" id="phone" class="input"  value="<?php echo $this->_var['supp_list']['phone']; ?>" />
          </td>
        </tr>
        
        <?php if (0): ?>
         <tr>
          <td width="30%" align="right">备用手机号码：</td>
          <td>
          <input name="phone1" type="text" size="40" id="phone1"   class="input"  value="<?php echo $this->_var['supp_list']['phone1']; ?>"  />
            <span id="phone_notice" style="color:#FF0000"> *</span>
          </td>
        </tr>

           
           <tr>
             <td align="right">地图坐标：</td>
             <td><input type="text" name="map_info"  class="input"   id="map_info" value="<?php echo $this->_var['supp_list']['map_info']; ?>" /> <a href="http://api.map.soso.com/doc/tooles/picker.html" target="_blank">地图坐标拾取</a></td>
           </tr>

       <?php if ($this->_var['supp_type'] == 'user'): ?>
         
          
            <tr>
          <td align="right">身份证正反复印件：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="business_license" id="business_license" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['business_license']): ?>
          <a href="/<?php echo $this->_var['supp_list']['business_license']; ?>"><img src="/<?php echo $this->_var['supp_list']['business_license']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        
        
         <tr>
          <td align="right">本人手持身份证：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="business_scope" id="business_scope" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['business_scope']): ?>
          <a href="/<?php echo $this->_var['supp_list']['business_scope']; ?>"><img src="/<?php echo $this->_var['supp_list']['business_scope']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        
        
         <?php else: ?>
         
         <tr>
          <td align="right">企业营业执照：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="business_license" id="business_license" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['business_license']): ?>
          <a href="/<?php echo $this->_var['supp_list']['business_license']; ?>"><img src="/<?php echo $this->_var['supp_list']['business_license']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        
        
         <tr>
          <td align="right">组织机构代码证：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="business_scope" id="business_scope" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['business_scope']): ?>
          <a href="/<?php echo $this->_var['supp_list']['business_scope']; ?>"><img src="/<?php echo $this->_var['supp_list']['business_scope']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        
         <tr>
          <td align="right">企业法人身份证：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="cards" id="cards" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['cards']): ?>
          <a href="/<?php echo $this->_var['supp_list']['cards']; ?>"><img src="/<?php echo $this->_var['supp_list']['cards']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        
         <tr>
          <td align="right">税务登记证：</td>
          <td>
          <?php if ($this->_var['supp_list']['is_check'] != 1): ?>
            <input name="certificate" id="certificate" type="file" >
          <?php endif; ?>
          <?php if ($this->_var['supp_list']['certificate']): ?>
          <a href="/<?php echo $this->_var['supp_list']['certificate']; ?>"><img src="/<?php echo $this->_var['supp_list']['certificate']; ?>" width="50" height="50"></a>
          <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
            <!--tr>
          <td align="right">商家描述：</td>
          <td>
        <?php echo $this->_var['FCKeditor']; ?>
          </td>
        </tr-->
        
        
                     <tr>

            <td class="right">&nbsp;</td>

            <td>

                        <input type="hidden" name="suppliers_id" value="<?php echo $this->_var['supp_list']['suppliers_id']; ?>">
               <input name="op" type="hidden" value="set">
                        <input name="act" type="hidden" value="supp_update">

                        <input type="submit" value="保 存" class="btn" name="subsupp"></td>

          </tr>

                    </table>

                   

                    </form>

              

            

 

             </div>

  </div>

  </div>         

<?php endif; ?>
  <?php if ($this->_var['action'] == 'ad'): ?>
  <div class="main" id="main">
    <div class="maintop">
      <img src="templates/images/title_goods.png" /><span>广告轮播</span>
    </div>
        <div class="maincon">
      <div class="contitlelist">
              <span>广告列表</span>
                 <div class="titleright"><a href="?op=set&act=add_ad">新增广告</a></div>
            </div>
      <div class="conbox">
<table cellspacing="0" cellpadding="0" class="listtable">
    <tr>
      <th class="left">名称</th>
      <th class="left">图片</th>
      <th class="left">连接</th>
      <th class="left">排序</th>
      <th class="left">操作</th>
    </tr>
    <?php $_from = $this->_var['ad_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['id'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['id']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['id']['iteration']++;
?>
    <tr>
      <td><?php echo $this->_var['item']['name']; ?></td>
      <td><img src='/<?php echo $this->_var['item']['photo_file']; ?>' width="100" height="50"></td>
      <td><?php echo $this->_var['item']['link']; ?></td>
      <td><?php echo $this->_var['item']['sort_order']; ?></td>
      <td>
       <a href="?op=set&act=edit_ad&id=<?php echo $this->_var['item']['photo_id']; ?>">编辑</a> |
    <a href="?op=set&act=ad_delete&id=<?php echo $this->_var['item']['photo_id']; ?>" onclick="return confirm('确定要此操作吗');">删除</a> 
      </td>
    </tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>
        </div>
       </div>
        </div>
 <?php endif; ?>
 <?php if ($this->_var['action'] == 'edit_ad' || $this->_var['action'] == 'add_ad'): ?>
     <div class="main" id="main">
    <div class="maintop">
      <img src="templates/images/title_article.png" /><span>广告轮播</span>
    </div>
    <div class="maincon">
            <?php if ($this->_var['action'] == 'edit_ad'): ?>
        <div class="contitlelist"><span>编辑广告</span><div class="titleright"><a href="?op=set&act=ad">返回列表</a></div></div>
                <?php else: ?>
                <div class="contitlelist"><span>添加广告</span><div class="titleright"><a href="?op=set&act=ad">返回列表</a></div></div>
                <?php endif; ?>
      <div class="conbox">
            <form action="index.php" enctype="multipart/form-data" method="post" name="myform" onsubmit="return checkad();">
           <table cellspacing="0" cellpadding="0" class="edittable">
          <tr>
            <td class="right">名称：</td>
            <td> 
                        <input type="text" name="name" value="<?php echo $this->_var['ad_info']['name']; ?>" class="input" size="35" /></td>
                      </tr>
                    <tr>
            <td class="right">图片：</td>
            <td> 
                        <input id="photo_file" name="photo_file" type="file" multiple>&nbsp;&nbsp;大小970像素*320像素　  
                        <?php if ($this->_var['ad_info']['photo_file']): ?><a href="/<?php echo $this->_var['ad_info']['photo_file']; ?>" target="_blank">查看</a><?php endif; ?>
                        
                        </td>
          </tr>
                    <tr>
            <td class="right">连接：</td>
            <td>
<input type="text" name="link" value="<?php echo $this->_var['ad_info']['link']; ?>" class="input" size="35" />
                        </td> 
          </tr>
                    <tr>
            <td class="right">排序：</td>
            <td>
<input type="text" name="sort_order" value="<?php echo $this->_var['ad_info']['sort_order']; ?>" class="input" size="35" />
                        </td> 
          </tr>
                    <tr>
            <td class="right">&nbsp;</td>
            <td>
                        <input type="hidden" name="suppliers_id" value="<?php echo $this->_var['suppliers_id']; ?>">
                        <input type="hidden" name="photo_id" value="<?php echo $this->_var['ad_info']['photo_id']; ?>">
                        <input type="hidden" name="act"  value="<?php echo $this->_var['status']; ?>">
                        <input type="hidden" name="op"  value="set">
                    <input type="submit" value="<?php if ($this->_var['action'] == 'edit_ad'): ?>修 改 <?php else: ?>添 加<?php endif; ?>" class="btn" name="subsupp">
                        </td>
          </tr>
                    </table>
                    </form>
             </div>

  </div>

  </div>         
 <?php endif; ?> 
 <?php if ($this->_var['action'] == 'edit_password'): ?>
    <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title.png" /><span><?php echo $this->_var['info']['suppliers_name']; ?></span>

    </div>

    <div class="maincon">

        <div class="contitleedit"><span>修改密码</span></div>

      <div class="conbox">

            <form name="formPassword" action="index.php" method="post" onSubmit="return editPassword()" >

     <table width="100%" border="0" cellpadding="5" class="edittable" cellspacing="1">

        <tr>

          <td  align="right" bgcolor="#FFFFFF">旧密码：</td>

          <td align="left" bgcolor="#FFFFFF"><input name="old_password" type="password" size="25"  class="input" /></td>

        </tr>

        <tr>

          <td align="right" bgcolor="#FFFFFF">新密码：</td>

          <td align="left" bgcolor="#FFFFFF"><input name="new_password" type="password" size="25"  class="input"/></td>

        </tr>


        <tr>

          <td  align="right" bgcolor="#FFFFFF">新密码确认：</td>

          <td align="left" bgcolor="#FFFFFF"><input name="comfirm_password" type="password" size="25" class="input" /></td>

        </tr>

        <tr>

          <td>&nbsp;</td>

          <td bgcolor="#FFFFFF">
          
          <input name="op" type="hidden" value="set" />
          <input name="act" type="hidden" value="act_edit_password" />

            <input name="submit" type="submit" class="btn" style="border:none;" value="确认修改" />

          </td>

        </tr>

      </table>

    </form>
      </div>
    </div>
</div>
   <?php endif; ?>    
   
    <?php if ($this->_var['action'] == 'user_message'): ?>
      <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title_article.png" /><span>用户评论管理</span>

    </div>

    <div class="maincon">

      

      <div class="contitlelist">

            <span>用户评论</span>

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

      <?php endif; ?> 

      

    <?php if ($this->_var['action'] == 'reply'): ?>  
     <div class="main" id="main">

    <div class="maintop">

      <img src="templates/images/title_article.png" /><span>用户评论管理</span>

    </div>

    <div class="maincon">
        <div class="contitleedit"><span>用户评论详情</span></div>
      <div class="conbox">
            <form action="index.php" enctype="multipart/form-data" method="post" name="myform">
           <table cellspacing="0" cellpadding="0" class="edittable">
<tr>

      <td>

      <a href="mailto:<?php echo $this->_var['msg']['email']; ?>">

      <b><?php if ($this->_var['msg']['user_name']): ?><?php echo $this->_var['msg']['user_name']; ?><?php else: ?>匿名用户<?php endif; ?></b></a>&nbsp;于

      &nbsp;<?php echo $this->_var['msg']['add_time']; ?>&nbsp;于&nbsp;<b><?php echo $this->_var['id_value']; ?></b>&nbsp;发表评论

    </td>

    </tr>

    <tr>

      <td><hr color="#dadada" size="1"></td>

    </tr>

    <tr>

      <td>

        <div style="overflow:hidden; word-break:break-all;"><?php echo $this->_var['msg']['content']; ?></div>

        <div align="right"><b><?php echo $this->_var['lang']['comment_rank']; ?>:</b> <?php echo $this->_var['msg']['comment_rank']; ?>&nbsp;&nbsp;<b>IP地址</b>:<?php echo $this->_var['msg']['ip_address']; ?></div>

      </td>

    </tr>
    <tr>
      <td align="center">
        <?php if ($this->_var['msg']['status'] == "0"): ?>

        <input type="button" onclick="location.href='?op=set&act=update_comment_status&check=allow&id=<?php echo $this->_var['msg']['comment_id']; ?>'" class="btn" value="允许显示" />
        <?php else: ?>
        <input type="button" onclick="location.href='?op=set&act=update_comment_status&check=forbid&id=<?php echo $this->_var['msg']['comment_id']; ?>'" class="btn" value="禁止显示"  />

        <?php endif; ?>

    </td>
    </tr>
                    </table>
                    </form>

<?php if ($this->_var['reply_info']['content']): ?>


<div class="maincon">
				<div class="contitleedit"><span>回复</span></div>
			<div class="conbox"> 
      <table cellspacing="0" cellpadding="0" class="edittable">
    <tr>
      <td>
      操作人员&nbsp;<a href="mailto:<?php echo $this->_var['msg']['email']; ?>"><b><?php echo $this->_var['reply_info']['user_name']; ?></b></a>&nbsp;于
      &nbsp;<?php echo $this->_var['reply_info']['add_time']; ?>&nbsp;回复
    </td>
    </tr>
    <tr>
      <td><hr color="#dadada" size="1"></td>
    </tr>
    <tr>
      <td>

        <div style="overflow:hidden; word-break:break-all;"><?php echo $this->_var['reply_info']['content']; ?></div>

        <div align="right"><b>IP地址</b>: <?php echo $this->_var['reply_info']['ip_address']; ?></div>

      </td>

    </tr>

  </table>

</div>

<?php endif; ?> 
<script language="javascript">

	function comment_validate()

	{

		var frm              = document.forms['comment_Form'];

	  	var content     = frm.elements['content'].value;

	  	if(content == 0){

			alert('回复的评论内容不能为空!');

			return false;	

		}

	}

</script>



<form method="post" action="?op=set&act=action" name="comment_Form" onsubmit="return comment_validate()">
  <table cellspacing="0" cellpadding="0" class="edittable">
  <tr><th colspan="2" align="left">
  <strong>回复评论</strong>
  </th></tr>
  <tr>

    <td>用户名:</td>
    <td><input name="user_name" type="text" value="商家" class="input"  /></td>
  </tr>

  <tr>
    <td>回复内容:</td>
    <td><textarea name="content" cols="50" rows="4" wrap="VIRTUAL"><?php echo $this->_var['reply_info']['content']; ?></textarea></td>
  </tr>
  <?php if ($this->_var['reply_info']['content']): ?>
  <tr>
    <td>&nbsp;</td>
    <td>提示: 此条评论已有回复, 如果继续回复将更新原来回复的内容!</td>
  </tr>
  <?php endif; ?>
 <tr>

    <td>&nbsp;</td>

    <td>

      <input name="submit" type="submit" value="确定" class="btn">
      <input type="hidden" name="comment_id" value="<?php echo $this->_var['msg']['comment_id']; ?>">

      <input type="hidden" name="comment_type" value="<?php echo $this->_var['msg']['comment_type']; ?>">

      <input type="hidden" name="id_value" value="<?php echo $this->_var['msg']['id_value']; ?>">

    </td>

  </tr>

</table>

</form>

  </div>

  </div> 
    <?php endif; ?>   
</body>
</html>