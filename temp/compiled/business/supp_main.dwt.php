<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['info']['suppliers_name']; ?>商家管理平台</title>
<link href="templates/css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="templates/js/haohaios.js"></script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/user.js"></script>
<script type="text/javascript" src="../js/region.js"></script>
<script type="text/javascript" src="../js/utils.js"></script>
<script type="text/javascript" src="templates/js/main.js"></script>
<script type="text/javascript" src="templates/js/supp.js"></script>
<script type="text/javascript" src="templates/js/public_tab.js"></script>
<script type="text/javascript" src="../<?php echo $this->_var['admin_path']; ?>/js/listtable.js"></script>
<script type="text/javascript" src="../js/DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="templates/js/public_tab.js"></script>
<script type="text/javascript" src="../js/qrcode.js"></script>
<script>
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
</script>
<style type="text/css">
.show_tips{
	width:100%;
	height:100%;	
	position:fixed;
	top:0;
	margin:0 auto;
	background-color: rgba(0,0,0,.6);
	z-index:99999;
}
.tips_box{
	width: 220px;
	height: 220px;
	padding:10px;
	margin: 20% auto 0;
	color:#666;
	font-size:14px;
	line-height:22px;
	z-index:100001;
	position:relative;
}
.show_tips #qrcode{
	width: 220px;
	height: 220px;
	margin: 0 auto;
	background-image: url(templates/images/qrcode.png);
	background-size: 100% 100%;
	position: relative;
	background-position: 0 0;
	background-repeat: no-repeat; 
	padding: 10px;
}
.show_tips #qrcode img{
	width: 74%;
    display: block;
    margin: 0 auto;
    position: absolute;
    top: 43px;
    left: 6px;
    right: 1px;
}
.show_tips p{
	width: 100%;
	line-height: 20px;
	color: #fff;
	font-size: 14px;
	text-align: center;
	margin-top: 5px;
}
</style>
</head>
<body >


<?php if ($this->_var['action'] == 'default'): ?>
<?php echo $this->fetch('library/lift_menu.lbi'); ?>
<?php echo $this->fetch('library/show_tips.lbi'); ?>
	 <div class="main" id="main">
		<div class="maincon" style="border-radius: 20px;">
			<div class="maintop">
				<img src="templates/images/title-home.png" style="width:38px;height:38px;"/><span><?php echo $this->_var['info']['suppliers_name']; ?></span>
			</div>
			<div class="contitle">
				<img src="templates/images/ico01.png" /><span>统计信息</span>
			</div>
			<div class="condiv">
            <?php if ($_SESSION['role_id'] == ''): ?>
				<a href="index.php?op=goods&act=my_goods">
					<span>本店商品数量&nbsp;&nbsp;<b><?php echo $this->_var['goodsnum']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<a href="index.php?op=goods&act=my_goods">
					<span>停售商品数量&nbsp;&nbsp;<b><?php echo $this->_var['nogoodsnum']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<?php if (0): ?>
				<a href="suppliers.php?act=article_list">
					<span>技术文章数量&nbsp;&nbsp;<b><?php echo $this->_var['articlenum']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<?php endif; ?>
                <?php endif; ?> 
				<a href="index.php?op=order&act=shipping_delivery_list">
					<span>待发货订单数量&nbsp;&nbsp;<b><?php echo $this->_var['delivery_count']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
                 <?php if ($_SESSION['role_id'] == ''): ?>
               <a href="index.php?op=account&act=my_order&settlement_status=3">
					<span>已完成结算订单&nbsp;&nbsp;<b><?php echo $this->_var['receive_count']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<a href="index.php?op=account&act=my_order&settlement_status=1">
					<span>待商家审核结算&nbsp;&nbsp;<b><?php echo $this->_var['unpay_count']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
    <?php endif; ?>
<!--
                <a href="suppliers.php?act=order_code_list">

					<span>待验证订单&nbsp;&nbsp;<b><?php echo $this->_var['tuan_buy_count']; ?></b></span>

					<img src="templates/images/ico12.png"/>

				</a>-->

			</div>
			<div class="contitle">
				<img src="templates/images/ico01.png" /><span>今日发货订单信息</span>
			</div>
			<div class="condiv">
				<a href="#">
					<span>交易完成订单总金额&nbsp;&nbsp;<b><?php echo $this->_var['payOrderAmount']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<a href="#">
					<span>退款订单总金额&nbsp;&nbsp;<b><?php echo $this->_var['refundOrderAmount']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
			</div>
			<div class="contitle">
				<img src="templates/images/ico01.png" /><span>今日自提订单信息</span>
			</div>
			<div class="condiv">
				<a href="#">
					<span>交易完成订单总金额&nbsp;&nbsp;<b><?php echo $this->_var['payOrderAmountPoint']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
				<a href="#">
					<span>退款订单总金额&nbsp;&nbsp;<b><?php echo $this->_var['refundOrderAmountPoint']; ?></b></span>
					<img src="templates/images/ico12.png"/>
				</a>
			</div>

		</div>

	</div>

	<?php endif; ?>
<div class="back-top"></div>
</body>
</html>