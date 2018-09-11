<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/includes/lib_order.php');
require_once(dirname(__FILE__) . '/includes/fun_stats.php');

$time  = gmtime();
$date  = date("Y-m-d",($time-86400));
$start = strtotime($date);
$end   = $start + 86400;

$all['order_nums']         = getOrderTypeNums();
$all['goods_nums']         = getGoodsNums();
$all['group_success_nums'] = getGroupNums(0,0,2);
$all['group_failed_nums']  = getGroupNums(0,0,4);
$all['user_nums']          = getUserNums();
$all['amount']             = getOrderAmount();

$yesterday['payed_order_nums']     = getOrderTypeNums($start,$end);
$yesterday['await_pay_order_nums'] = getOrderTypeNums($start,$end,'await_pay');
$yesterday['group_success_nums']   = getGroupNums($start,$end,2);
$yesterday['group_failed_nums']    = getGroupNums($start,$end,4);
$yesterday['user_nums']            = getUserNums($start,$end);
$yesterday['amount']               = getOrderAmount($start,$end);

$goods_list = getGoodsOrders();
$user_list  = getUserOrders();
$year_stats = getYearStats();
$full_mon_stats = getFullMonStats();

ob_end_clean();
make_json_result(compact('all','yesterday','goods_list','user_list','year_stats','full_mon_stats'));