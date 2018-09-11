<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $this->_var['lang']['cp_home']; ?></title>
<link rel="stylesheet" href="css/mobile.css" type="text/css" media="all" />
<script type="text/javascript" src="js/transport.js"></script>
<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<div class="container">
    <div class="main">
        <h1><?php echo $this->_var['lang']['cp_home']; ?></h1>
        <div class="mbox">
            <h3>今日概况</h3>
            <ul class="ult">
                <li>订单数（个）<br/><?php echo $this->_var['today_order']; ?><br/><span>昨日：<?php echo $this->_var['yestoday_order']; ?></span></li>
                <li>成团数（个）<br/><?php echo $this->_var['teamtoday']; ?><br/><span>昨日：<?php echo $this->_var['team_yestoday']; ?></span></li>
                <li>成交额（元）<br/><?php echo $this->_var['today_money']; ?><br/><span>昨日：<?php echo $this->_var['yestoday_money']; ?></span></li>
            </ul>
        </div>
        <div class="mbox">
            <h3>整体概况</h3>
            <ul class="ulb">
                <li>商品总数<br/><?php echo $this->_var['goodsnum']; ?></li>
                <li>共成团<br/><?php echo $this->_var['totle_team']; ?></li>
                <li>总成交额<br/><?php echo $this->_var['totle_money']; ?></li>
                <li>总订单量<br/><?php echo $this->_var['totle_count']; ?></li>
                <li><a href="order.php?act=list&composite_status=101&he=2">待核销订单<br/><?php echo $this->_var['weihe']; ?></a></li>
                <li>已核销订单<br/><?php echo $this->_var['yihe']; ?></li>
            </ul>
        </div>
    </div>
</div>
<div class="footer">
    <ul>
        <li><a href="index.php" class="cur">首页</a></li>
        <li><a href="order.php?act=list&composite_status=101&he=1">待发货订单</a></li>
        <li><a href="order.php?act=list&composite_status=101&he=2">待核销订单</a></li>
    </ul>
</div>
</body>
</html>