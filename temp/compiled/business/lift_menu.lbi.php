
<div class="headcon">

<h1 title="商家管理平台">
  <div class="logo_text"></div>
</h1>

<div class="headright">
  <div class="welcome"></div>

  <div class="operate">
    <a href="index.php?op=login&act=logout">退出登录</a>
    <?php if ($_SESSION['role_id'] == ''): ?>
    <a href="index.php?op=set&act=edit_password">修改密码</a>
    <?php endif; ?>
    <!-- 
            <a href="/store.php?id=<?php echo $this->_var['suppliers_id']; ?>" target="_blank">我的店铺</a>
  -->
</div>

</div>

</div>

<div class="leftcon" >
<div class="menu_top"></div>
<ul class="menu">

<li <?php if ($this->_var['action'] == 'default'): ?> class="choise"<?php endif; ?>>

  <a href="index.php?op=main&act=default" class="home_welcome">
    <span>欢迎页</span>
  </a>

</li>
<?php if ($this->_var['suppliers_array']['is_check'] == 1): ?>
<li >
  <a href="javascript:;" class="article">
    <span>商品管理</span>
  </a>

  <div class="second_menu" >

    <a href="index.php?op=goods&act=my_goods" id="menu_my_goods" class="goods <?php if ($this->_var['action'] == 'my_goods'): ?>choise<?php endif; ?>">
      <span> <i></i>
        商品列表
      </span>
    </a>

    <a href="index.php?op=goods&act=add_goods" id="menu_add_goods" class="goods <?php if ($this->_var['action'] == 'add_goods'): ?>choise<?php endif; ?>">
      <span> <i></i>
        新增商品
      </span>
    </a>

    <a href="index.php?op=pic&act=pic_list" id="menu_pic_list" class="goods <?php if ($this->_var['action'] == 'pic_list'): ?>choise<?php endif; ?>">
      <span>
        <i></i>
        相册管理
      </span>
    </a>

    <a href="index.php?op=goods&act=goods_trash" id="menu_goods_trash" class="goods <?php if ($this->_var['action'] == 'goods_trash'): ?>choise<?php endif; ?>">
      <span>
        <i></i>
        商品回收站
      </span>
    </a>

  </div>
  <div class="li_bottom"></div>
</li>

<li >
  <a href="javascript:;" class="article article2">
    <span>订单管理</span>
  </a>

  <div class="second_menu" >
    <a href="index.php?op=order&act=goods_order" id="menu_goods_order" class="goods  <?php if ($this->_var['action'] == 'goods_order'): ?>choise<?php endif; ?>">
      <span>
        <i></i>
        发货订单
      </span>
    </a>
    <a href="index.php?op=order&act=goods_order2" id="menu_goods_order2" class="goods  <?php if ($this->_var['action'] == 'goods_order2'): ?>choise<?php endif; ?>">
      <span>
        <i></i>
        自提订单
      </span>
    </a>
    <!-- 
                    <a href="?act=delivery" class="goods  <?php if ($this->_var['action'] == 'delivery'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      到店提货
    </span>
  </a>

  <a href="?act=delivery_list" class="goods  <?php if ($this->_var['action'] == 'delivery_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      提货管理
    </span>
  </a>
  -->
  <a href="index.php?op=order&act=shipping_delivery_list" id="menu_shipping_delivery_list" class="goods  <?php if ($this->_var['action'] == 'shipping_delivery_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      发货管理
    </span>
  </a>
</div>
<div class="li_bottom"></div>
</li>
<li >
<a href="javascript:;" class="article articl3">
  <span>结算管理</span>
</a>
<div class="second_menu" >
  <a href="index.php?op=set&act=bank_config" id="menu_bank_config" class="goods  <?php if ($this->_var['action'] == 'bank_config'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      开户行设置
    </span>
  </a>
  <a href="index.php?op=account&act=my_order" id="menu_my_order" class="goods  <?php if ($this->_var['action'] == 'my_order'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      订单结算
    </span>
  </a>
</div>
<div class="li_bottom"></div>
</li>

<li >
<a href="javascript:;" class="article article4">
  <span>商家管理</span>
</a>

<div class="second_menu" >

  <a href="index.php?op=set&act=supp_info" id="menu_supp_info" class="goods  <?php if ($this->_var['action'] == 'supp_info'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      店铺设置
    </span>
  </a>
  <!--a href="index.php?op=luckdraw&act=luckdraw_list" id="menu_luckdraw_list" class="goods  <?php if ($this->_var['action'] == 'luckdraw_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      抽奖管理
    </span>
  </a-->
  <a href="index.php?op=bonus&act=bonus" id="menu_bonus" class="goods  <?php if ($this->_var['action'] == 'bonus'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      优惠券管理
    </span>
  </a>
  <a href="index.php?op=set&act=ad" id="menu_ad" class="goods  <?php if ($this->_var['action'] == 'ad'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      广告轮播
    </span>
  </a>
  <a href="index.php?op=shipping&act=supp_shipping" id="menu_supp_shipping" class="goods  <?php if ($this->_var['action'] == 'supp_shipping'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      配送方式
    </span>
  </a>
  <a href="index.php?op=set&act=user_message" id="menu_user_message" class="goods  <?php if ($this->_var['action'] == 'user_message'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      用户评论
    </span>
  </a>
  <a href="index.php?op=false&act=false_message" id="menu_false_message" class="goods  <?php if ($this->_var['action'] == 'false_message'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      虚拟评论
    </span>
  </a>
  <a href="index.php?op=false&act=false_user" id="menu_user_message" class="goods  <?php if ($this->_var['action'] == 'false_user'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      虚拟会员
    </span>
  </a>
</div>
<div class="li_bottom"></div>
</li>
<li >
<a href="javascript:;" class="article article5">
  <span>系统设置</span>
</a>
<div class="second_menu" >
  <a href="index.php?op=set&act=edit_password" id="menu_edit_password" class="goods  <?php if ($this->_var['action'] == 'edit_password'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      密码修改
    </span>
  </a>
</div>
<div class="li_bottom"></div>
</li>
<li >
<a href="javascript:;" class="article article6">
  <span>统计报表</span>
</a>
<div class="second_menu" >
  <!--a href="index.php?op=statistical&act=order_stats" id="menu_order_stats" class="goods  <?php if ($this->_var['action'] == 'order_stats'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      订单统计
    </span>
  </a>
  <a href="index.php?op=statistical&act=sale_list" id="menu_sale_list" class="goods  <?php if ($this->_var['action'] == 'sale_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      销售明细
    </span>
  </a>
  <a href="index.php?op=statistical&act=sale_order" id="menu_sale_order" class="goods  <?php if ($this->_var['action'] == 'sale_order'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      销售排行
    </span>
  </a-->
  <a href="index.php?op=statistics&act=sale_list" id="menu_sale_list" class="goods  <?php if ($this->_var['action'] == 'sale_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      发货订单统计
    </span>
  </a>
  <a href="index.php?op=statistics&act=point_list" id="menu_point_list" class="goods  <?php if ($this->_var['action'] == 'point_list'): ?>choise<?php endif; ?>">
    <span>
      <i></i>
      自提订单统计
    </span>
  </a>
</div>

</li>
<?php else: ?>
<li >
<a href="javascript:;" class="goods">
  <span>店铺管理</span>
</a>
<div class="second_menu" <?php if ($this->_var['action'] != ''): ?><?php else: ?> style="display:none;"<?php endif; ?>>
  <a href="?act=supp_info" id="menu_supp_info" class="goods choise">
    <span>
      <i></i>
      账号设置
    </span>
  </a>
</div>

</li>
<?php endif; ?>
</ul>

</div>

<script type="text/javascript">

$(document).ready(function(){
   $(".menu li").click(function(){
      if($(this).find(".second_menu").css("display")=='none')
      {
        $(this).find(".second_menu").slideDown("slow");
        $(this).siblings().find(".second_menu").slideUp("slow");
      }
      else if($(this).find(".second_menu").css("display")=='block'){
        $(this).find(".second_menu").slideUp("slow");
        $(this).siblings().find(".second_menu").slideUp("slow");
      }
      else{
        $(this).find(".second_menu").slideUp("slow");
        $(this).siblings().find(".second_menu").slideDown("slow");
      }
  });
}); 

</script>

<script>

function get_cat_list(id,type)

{

  $.ajax({
        url: 'user.php',
        type: 'GET',
        dataType : 'JSON' ,
        data:  'act=get_cat_list&id=' + id+'&type='+type ,
        success: function (data) {
          get_cat_listResponse(data);
        }
      });

}

function get_cat_listResponse(result)

{



  if(document.getElementById('show_cat_list_'+result.type))

  {

    document.getElementById('show_cat_list_'+result.type).innerHTML = result.html;

    //alert(result.html);

  }

}







 /**

   * 鏂板?涓€涓??鏍

   */

  function addSpec(obj)

  {

      var src   = obj.parentNode.parentNode;

      var idx   = rowindex(src);

      var tbl   = document.getElementById('attrTable');

      var row   = tbl.insertRow(idx + 1);

      var cell1 = row.insertCell(-1);

      var cell2 = row.insertCell(-1);

      var regx  = /<a([^>]+)<\/a>/i;



      cell1.className = 'label';

      cell1.innerHTML = src.childNodes[0].innerHTML.replace(/(.*)(addSpec)(.*)(\[)(\+)/i, "$1removeSpec$3$4-");

      cell2.innerHTML = src.childNodes[1].innerHTML.replace(/readOnly([^\s|>]*)/i, '');

  }



  /**

   * 鍒犻櫎瑙勬牸鍊

   */

  function removeSpec(obj)

  {

      var row = rowindex(obj.parentNode.parentNode);

      var tbl = document.getElementById('attrTable');



      tbl.deleteRow(row);

  }

  

  

function checkpic_category()

{

  if(document.myform.cat_name.value=='')

  {

    alert('请输入分类名称');

    return false;

  }

  return true;

}



function editPassword()

{

  var frm              = document.forms['formPassword'];

  var old_password     = frm.elements['old_password'].value;

  var new_password     = frm.elements['new_password'].value;

  var confirm_password = frm.elements['comfirm_password'].value;



  var msg = '';

  var reg = null;



  if (old_password.length == 0)

  {

    msg +=  '旧密码不能为空\n';

  }



  if (new_password.length == 0)

  {

    msg +=  '新密码不能为空\n';

  }



  if (confirm_password.length == 0)

  {

    msg += '确认密码不能为空\n';

  }



  if (new_password.length > 0 && confirm_password.length > 0)

  {

    if (new_password != confirm_password)

    {

      msg +=   '新密码俩次输入不一致\n';

    }

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
function checkarticle()
{
  if(document.myform.title.value=='')
  {
    alert('请输入标题');
    return false;
  }
  if(document.myform.cat_id.value=='')
  {
    alert('请选择分类');
    return false;
  }
  return true;
}





  

/**

 * 添加图片

**/

  function addImg(obj)

  {

   

    var src  = obj.parentNode.parentNode; 

   

    var idx  = rowindex(src);

      var tbl  = document.getElementById('gallery-table');

      var row  = tbl.insertRow(idx + 1);

  

      //var cell = row.insertCell(-1);

      row.innerHTML = src.innerHTML.replace(/(.*)(addImg)(.*)(\[)(\+)/i, "$1removeImg$3$4-");

    

    

  }

  

  

  function removeImg(obj)

  {

      var row = rowindex(obj.parentNode.parentNode);

      var tbl = document.getElementById('gallery-table');

      tbl.deleteRow(row);

  }





  /**

   * 删除已上传图片

   */

  function dropImg(imgId)

  {

    

    $.ajax({
        url: 'index.php?op=goods&act=drop_image',
        type: 'GET',
        data:  'img_id='+imgId ,
        dataType : 'JSON' ,
        success: function (data) {
          dropImgResponse(data);
        }
      });

  }



  function dropImgResponse(result)

  {

      if (result.error == 0)

      {

          document.getElementById('gallery_' + result.content).style.display = 'none';

      }

  }
//提货单打印
  function de_print()
  {
    window.open("index.php?op=order&act=delivery_info_print&delivery_id=<?php echo $this->_var['delivery_order']['delivery_id']; ?>");
  }
 //切换
  function toggle(obj, act, id)
  {
    var val = (obj.innerHTML.match(/是/i)) ? 0 : 1;
    $.ajax({
        url: 'this.url',
        type: 'POST',
        dataType : 'JSON' ,
        data:  'act='+act+'&val=' + val + '&id=' +id ,
        async:false,
        success: function (res) {
             if (res.message)
              {
                alert(res.message);
              }
              if (res.error == 0)
              {
                obj.innerHTML = (res.content > 0) ? '是' : '否';
              }
        }
      });

   
  }

  function account_detail_sub(){
    var remark=document.getElementById('remark').value;   
    if(remark==''){
      alert('请填写内容');
      return false;
    }
    $.ajax({
        type:"post",//请求类型
        url:"index.php",//服务器页面地址
        data:"remark="+remark+"&act=account_detail_form&id=<?php echo $this->_var['suppliers_accounts_id']; ?>",//参数(可有可无)
        dataType:"json",//服务器返回结果类型(可有可无)
        error:function(data){//错误处理函数(可有可无)
           alert("ajax出错啦");
        },
        success:function(data){
          alert(data.content);          
        }
      });
    return false;       
 }
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