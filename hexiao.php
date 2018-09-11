<?php

define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
$order_id = intval($_GET['order_id']);
if(!$order_id){
	hhs_header("location:index.php");
	exit();
}

$order = order_info($order_id);if($order['op_uid'] != 0){	$select_op_user_sql = "select uname from ".$GLOBALS['hhs']->table('users')." where user_id = ".$order['op_uid'];	$op_user = $GLOBALS['db']->getOne($select_op_user_sql);}

//print_r($order);
    assign_template();
    $position = assign_ur_here('','核销');	    $smarty->assign('user',$op_user);    
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
	$smarty->assign('order',         $order);

	$smarty->assign('root', 'http://'.$_SERVER['HTTP_HOST']);
	$smarty->display('hexiao.dwt');

	
	
function order_info($order_id)
{
    $order_id = intval($order_id);
    $sql = "SELECT order_id,add_time,point_id, arranged_time,op_uid FROM " . $GLOBALS['hhs']->table('order_info') ." WHERE order_id = '$order_id'";
    $order = $GLOBALS['db']->getRow($sql);
    if ($order)
    {
		$order['goods_list']     = order_goods($order['order_id']);
        $order['add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']-8*60*60);
		$order['shipping_point']   = get_shipping_point_name($order['point_id']);				$order['arranged_time'] = local_date("Y-m-d H:i:s", $order['arranged_time']-8*60*60);
    }
    return $order;
}
function order_goods($order_id)
{
    $sql = "SELECT og.rec_id, og.goods_id, og.goods_name, og.goods_attr, og.goods_sn, og.market_price, og.goods_number, " .  "og.goods_price, og.goods_attr, og.is_real, og.parent_id, og.is_gift, " .  "og.goods_price * og.goods_number AS subtotal, og.extension_code,g.goods_thumb,g.little_img,g.goods_brief " . "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS og ".  " LEFT JOIN ". $GLOBALS['hhs']->table('goods') . " AS g on og.goods_id=g.goods_id " . " WHERE order_id = '$order_id'";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;
    }
    return $goods_list;
}

?>
