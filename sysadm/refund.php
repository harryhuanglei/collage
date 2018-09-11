<?php

/**
 * 小舍电商 订单管理
 * ============================================================================
 * 版权所有 2005-2010 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17219 2011-01-27 10:49:19Z yehuaixiao $
 */

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
require_once(ROOT_PATH . 'includes/lib_payment.php');

require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
$sql="select * from ".$hhs->table('order_info')." where team_sign=".$_REQUEST['team_sign']." and team_status=3";
$refund_order=$db->getAll($sql);
$total_num=0;
$success_num=0;
foreach($refund_order as $v){
    $total_num++;
    $order_sn=$v['order_sn'];
    $r=refund($order_sn,$v['money_paid']*100);
    if($r){
    	$sql="update ".$hhs->table('order_info')." set team_status=4,pay_status=0 where order_id=".$v['order_id'];
    	$db->query($sql);
    	$success_num++;
    }
    $user_id=$v['user_id'];
    $order_id=$v['order_id'];
    $wxch_order_name='refund';
    include_once(ROOT_PATH . 'wxch_order.php');
}
$sql="update ".$hhs->table('order_info')." set order_status=2 where team_sign=".$_REQUEST['team_sign'];
$db->query($sql);


$links[] = array('text' => '团购订单列表', 'href' => 'order.php?act=team_list');
$links[] = array('text' => '团购列表', 'href' => 'order.php?act=team_manage');
sys_msg("共需退款".$total_num."单，成功退款".$success_num, 1, $links);

?>

