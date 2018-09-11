<div class="start-left">
  <div class="content-left">
    <!-- $Id: start.htm 17216 2014-05-12 06:03:12Z pangbin $ -->
    <link href="styles/main.css" rel="stylesheet" type="text/css" />
    <h1>
      <?php if ($this->_var['action_link']): ?>
      <span class="action-span"><a href="<?php echo $this->_var['action_link']['href']; ?>"><?php echo $this->_var['action_link']['text']; ?></a></span>
      <?php endif; ?>
      <?php if ($this->_var['action_link2']): ?>
      <span class="action-span"><a href="<?php echo $this->_var['action_link2']['href']; ?>"><?php echo $this->_var['action_link2']['text']; ?></a>&nbsp;&nbsp;</span>
      <?php endif; ?>
      <?php if ($this->_var['action_link3']): ?>
      <span class="action-span"><a href="<?php echo $this->_var['action_link3']['href']; ?>"><?php echo $this->_var['action_link3']['text']; ?></a>&nbsp;&nbsp;</span>
      <?php endif; ?>
      <span class="action-span1"><a href="index.php?act=main"><?php echo $this->_var['shop_name']; ?>管理后台</a> </span><span id="search_id" class="action-span1"><?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></span>
      <div style="clear:both"></div>
    </h1>
    <!-- start personal message -->
    <?php if ($this->_var['admin_msg']): ?>
    <div class="list-div" style="border: 1px solid #CC0000">
      <table cellspacing='1' cellpadding='3'>
        <tr>
          <th><?php echo $this->_var['lang']['pm_title']; ?></th>
          <th><?php echo $this->_var['lang']['pm_username']; ?></th>
          <th><?php echo $this->_var['lang']['pm_time']; ?></th>
        </tr>
        <?php $_from = $this->_var['admin_msg']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msg');if (count($_from)):
    foreach ($_from AS $this->_var['msg']):
?>
          <tr align="center">
            <td align="left"><a href="message.php?act=view&id=<?php echo $this->_var['msg']['message_id']; ?>"><?php echo sub_str($this->_var['msg']['title'],60); ?></a></td>
            <td><?php echo $this->_var['msg']['user_name']; ?></td>
            <td><?php echo $this->_var['msg']['send_date']; ?></td>
          </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </table>
      </div>
    <br />
    <?php endif; ?>
    <!-- end personal message -->

    <div class="tab-div">
      <!-- tab bar -->
      <div id="tabbody-div">
        <ul id="con_one_1">
           <!-- <li><a href="order.php?act=refund_list">退换货申请<br/><strong><?php echo $this->_var['refund_goods']; ?></strong></a></li>-->
			<!--li><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['await_ship']; ?>"><?php echo $this->_var['lang']['await_ship']; ?><br/><strong><?php echo $this->_var['order']['await_ship']; ?></strong></a></li>
            <li><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['unconfirmed']; ?>"><?php echo $this->_var['lang']['unconfirmed']; ?><br/><strong><?php echo $this->_var['order']['unconfirmed']; ?></strong></a></li>
            <li><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['await_pay']; ?>"><?php echo $this->_var['lang']['await_pay']; ?><br/><strong><?php echo $this->_var['order']['await_pay']; ?></strong></a></li>
            <li><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['finished']; ?>"><?php echo $this->_var['lang']['finished']; ?><br/><strong><?php echo $this->_var['order']['finished']; ?></strong></a></li-->
          <li><?php echo $this->_var['lang']['goods_count']; ?><br/><strong><?php echo $this->_var['goods']['total']; ?></strong></li>
          <li><a href="goods.php?act=list&stock_warning=1"><?php echo $this->_var['lang']['warn_goods']; ?><br/><strong><?php echo $this->_var['goods']['warn']; ?></strong></a></li>
        </ul>
      </div>
    </div>

    <!--整体营销概况 star-->
    <div class="content-left">
      <!-- $Id: start.htm 17216 2014-05-12 06:03:12Z pangbin $ -->
      <h1>
        <span class="action-span1"><a href="index.php?act=main">整体营销概况</a><span style="color:red;">&nbsp;&nbsp;总成交额是指交易完成的订单金额统计</span> </span>
        <div style="clear:both"></div>
      </h1>
      <!-- start personal message -->
      <?php if ($this->_var['admin_msg']): ?>
      <div class="list-div" style="border: 1px solid #CC0000">
        <table cellspacing='1' cellpadding='3'>
          <tr>
            <th><?php echo $this->_var['lang']['pm_title']; ?></th>
            <th><?php echo $this->_var['lang']['pm_username']; ?></th>
            <th><?php echo $this->_var['lang']['pm_time']; ?></th>
          </tr>
          <?php $_from = $this->_var['admin_msg']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msg');if (count($_from)):
    foreach ($_from AS $this->_var['msg']):
?>
            <tr align="center">
              <td align="left"><a href="message.php?act=view&id=<?php echo $this->_var['msg']['message_id']; ?>"><?php echo sub_str($this->_var['msg']['title'],60); ?></a></td>
              <td><?php echo $this->_var['msg']['user_name']; ?></td>
              <td><?php echo $this->_var['msg']['send_date']; ?></td>
            </tr>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </table>
        </div>
      <br />
      <?php endif; ?>
      <!-- end personal message -->

      <div class="tab-div">
        <div id="tabbody-div">
          <ul>
              <li class="order-li"><a href="users.php?act=list">会员总数<br/><strong><?php echo $this->_var['table']['all']['user_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=team_list&team_status=2">共成团<br/><strong><?php echo $this->_var['table']['all']['group_success_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=team_list&team_status=3">拼团失败<br/><strong><?php echo $this->_var['table']['all']['group_failed_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=list">总成交额<br/><strong><?php echo empty($this->_var['table']['all']['amount']) ? '0.00' : $this->_var['table']['all']['amount']; ?></strong></a></li>
          </ul>
        </div>
      </div>
      <!--发货订单统计信息start-->
      <h1>
        <span class="action-span1"><a href="index.php?act=main">发货订单概况</a></span>
        <div style="clear:both"></div>
      </h1>
      <div class="tab-div">
        <div id="tabbody-div">
          <ul>
              <li class="order-li"><a href="order.php?act=list&composite_status=0">待确认<br/><strong><?php echo $this->_var['table']['yesterday']['unConfirmed']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=list&composite_status=101">待发货<br/><strong><?php echo $this->_var['table']['yesterday']['awaitShip']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=list&composite_status=102">已完成<br/><strong><?php echo $this->_var['table']['yesterday']['paidOrderNum']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=list&composite_status=3">已退款<br/><strong><?php echo $this->_var['table']['yesterday']['orderRefund']; ?></strong></a></li>
          </ul>
        </div>
      </div>
      <!--发货订单统计信息end-->
      <!--自提订单统计信息start-->
      <h1>
        <span class="action-span1"><a href="index.php?act=main">自提订单概况</a></span>
        <div style="clear:both"></div>
      </h1>
      <div class="tab-div">
        <div id="tabbody-div">
          <ul>
              <li class="order-li"><a href="order.php?act=point_order_list&composite_status=0">待确认<br/><strong><?php echo $this->_var['table']['yesterday']['unConfirmedPoint']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=point_order_list&composite_status=101">待核销<br/><strong><?php echo $this->_var['table']['yesterday']['awaitShipPoint']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=point_order_list&composite_status=102">已核销<br/><strong><?php echo $this->_var['table']['yesterday']['paidOrderNumPoint']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=point_order_list&composite_status=3">已退款<br/><strong><?php echo $this->_var['table']['yesterday']['orderRefundPoint']; ?></strong></a></li>
          </ul>
        </div>
      </div>
      <!--自提订单统计信息end-->
      <h1>
        <span class="action-span1"><a href="index.php?act=main">昨日营销概况</a><span style="color:red;">&nbsp;&nbsp;总成交额是指交易完成的订单金额统计</span> </span>
        <div style="clear:both"></div>
      </h1>
      <div class="tab-div">
        <div id="tabbody-div">
          <ul>
              <li class="order-li"><a href="users.php?act=list">会员总数<br/><strong><?php echo $this->_var['table']['yesterday']['user_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=team_list&team_status=2">共成团<br/><strong><?php echo $this->_var['table']['yesterday']['group_success_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=team_list&team_status=3">拼团失败<br/><strong><?php echo $this->_var['table']['yesterday']['group_failed_nums']; ?></strong></a></li>
              <li class="order-li"><a href="order.php?act=list">总成交额<br/><strong><?php echo empty($this->_var['table']['yesterday']['amount']) ? '0.00' : $this->_var['table']['yesterday']['amount']; ?></strong></a></li>
          </ul>
        </div>
      </div>
    </div>
      <h1>
        <span class="action-span1"><a href="index.php?act=main">分销概况</a>  </span>
        <div style="clear:both"></div>
      </h1>
      <div class="tab-div">
        <div id="tabbody-div">
          <ul>
            <li class="order-li"><a href="distribution.php?act=list">总金额<br/><strong><?php echo empty($this->_var['table']['fenxiao']['all_money']) ? '0.00' : $this->_var['table']['fenxiao']['all_money']; ?></strong></a></li>
            <li class="order-li"><a href="distribution.php?act=list&dstatus=1">有效金额<br/><strong><?php echo empty($this->_var['table']['fenxiao']['all_allow']) ? '0.00' : $this->_var['table']['fenxiao']['all_allow']; ?></strong></a></li>
            <li class="order-li"><a href="distribution.php?act=list">当月总金额<br/><strong><?php echo empty($this->_var['table']['fenxiao']['mon_money']) ? '0.00' : $this->_var['table']['fenxiao']['mon_money']; ?></strong></a></li>
            <li class="order-li"><a href="distribution.php?act=list&dstatus=1">当月有效金额<br/><strong><?php echo empty($this->_var['table']['fenxiao']['mon_allow']) ? '0.00' : $this->_var['table']['fenxiao']['mon_allow']; ?></strong></a></li>
          </ul>
        </div>
      </div>
    </div>    
    <!--整体营销概况 end-->
  </div>
</div>

<!--content-right star-->
<!--div class="content-right">
  <!--热销商品排行榜 star-->
  <!--div class="hot-goods public-start" style="width: 68%;">
    <h1>热销商品排行榜<a href="goods.php?act=list">查看全部 > </a></h1>
    <div class="paihang" style="height: auto;">
      <ul class="paihang-ul">
        <li class="paihang-num">排行</li>
        <li class="paihang-name">商品名</li>
        <li class="paihang-xiaoliang">销量</li>
        <li class="paihang-price">商品id</li>
      </ul>
      <?php $_from = $this->_var['table']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['foo']['iteration']++;
?>
      <ul class="paihang-ul">
        <li class="paihang-num"><?php echo $this->_foreach['foo']['iteration']; ?></li>
        <li class="paihang-name"><?php echo $this->_var['item']['goods_name']; ?></li>
        <li class="paihang-xiaoliang"><?php echo $this->_var['item']['send_nummber']; ?></li>
        <li class="paihang-price"><?php echo $this->_var['item']['goods_id']; ?></li>
      </ul>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
  </div>
  <!--热销商品排行榜 end-->
  
  <!--会员消费排行榜 star-->
<!--   <div class="vip-price public-start">
    <h1>会员消费排行榜<a href="users.php?act=list">查看全部 > </a></h1>
    <div class="paihang">
      <ul class="paihang-ul">
        <li class="paihang-num">排行</li>
        <li class="paihang-name">会员名</li>
        <li class="paihang-xiaoliang">订单数</li>
        <li class="paihang-price">消费金额</li>
      </ul>
      <?php $_from = $this->_var['table']['user_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['foo']['iteration']++;
?>
      <ul class="paihang-ul">
        <li class="paihang-num"><?php echo $this->_foreach['foo']['iteration']; ?></li>
        <li class="paihang-name"><?php echo empty($this->_var['item']['uname']) ? 'no name' : $this->_var['item']['uname']; ?></li>
        <li class="paihang-xiaoliang"><?php echo $this->_var['item']['nums']; ?></li>
        <li class="paihang-price"><?php echo empty($this->_var['item']['amount']) ? '0.00' : $this->_var['item']['amount']; ?></li>
      </ul>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
  </div> -->
  <!--会员消费排行榜 end-->
</div>
<!--content-right end-->


<!--月营销概况曲线图 star-->
<!--div class="json-table">
  <!-- $Id: start.htm 17216 2014-05-12 06:03:12Z pangbin $ -->
  <!--h1>
    <span class="action-span1"><a href="index.php?act=main">月营销概况曲线图</a> </span>
    <div style="clear:both"></div>
  </h1>
  <div id="jsonTable"></div>
</div>
<!--月营销概况曲线图 end-->


<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js')); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'tabs.js')); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'highcharts.js')); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'json_table.js')); ?>
<script>
    var yaxis = [<?php echo $this->_var['table']['yaxis']; ?>];
</script>


<script type="Text/Javascript" language="JavaScript">
    var series =  [] ;
    var data1 = [];
    var data2 = [];
    <?php $_from = $this->_var['table']['full_mon_stats']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>

    data1.push(<?php echo $this->_var['item']['team_success']; ?>);
    data2.push(<?php echo $this->_var['item']['team_failed']; ?>);
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    series = [
      {
        'name': '拼团成功',
        'data': data1
      },
      {
        'name': '拼团失败',
        'data':data2
      }
    ] ;



    onload = function()
    {

      //jsonTable
        jsonTable();
    }

</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
