<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init2.php');
require(dirname(__FILE__) . '/includes/lib_order.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}

   assign_template();
    $smarty->assign('page_title',$_CFG['shop_name']);    // 页面标题


$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order = order_info($order_id);
$sql = "select * from ".$hhs->table('order_luck')." where order_id = '".$order_id."'";
$rows = $db->getAll($sql);
if (empty($rows) || empty($order)) {
	exit();
}
if ($order['pay_status'] != 2) {
	hhs_header("Location: user.php?act=order_detail&order_id=".$order_id."\n");
	exit();
}
if($order['user_id'] != $_SESSION['user_id']){
	hhs_header("Location: goods.php?id=".$order['goods_id']."\n");
	exit();
}
$sql = "select * from ".$hhs->table('order_goods')." where order_id = '".$order_id."'";
$goods = $db->getRow($sql);

$smarty->assign('order',$order);
$smarty->assign('rows',$rows);
$smarty->assign('nums',count($rows));
$smarty->assign('goods',$goods);

$smarty->display('userbao.dwt');


?>
