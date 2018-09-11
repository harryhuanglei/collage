<?php
define('IN_HHS', true);
if($action =='default')
{
	$info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id='$suppliers_id'");
	$goodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where  is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
   
	$nogoodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where is_on_sale=0 and is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
	$articlenum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('article')." where  suppliers_id=".$_SESSION['suppliers_id']);
    /*统计*/
    $today = strtotime(date('Y-m-d',gmtime())); 
    $yestoday = $today-24*3600;
    $today_order = $db->getRow("select COUNT(order_id) as count,SUM(money_paid) as money from ".$hhs->table('order_info')." where pay_time>'$today' and suppliers_id='{$_SESSION['suppliers_id']}'");
   
    $yestoday_order = $db->getRow("select COUNT(order_id) as count,SUM(money_paid) as money from ".$hhs->table('order_info')." where pay_time>'$yestoday' and pay_time<'$today' and suppliers_id='{$_SESSION['suppliers_id']}'");
     $teamtoday = $db->getOne("select count(order_id) from ".$hhs->table('order_info')." where extension_code='team_goods' and pay_time>'$today' and suppliers_id='{$_SESSION['suppliers_id']}'"); 
    $team_yestoday = $db->getOne("select count(order_id) from ".$hhs->table('order_info')." where extension_code='team_goods' and pay_time>'$yestoday' and pay_time<'$today' and suppliers_id='{$_SESSION['suppliers_id']}'");
    $totle_order = $db->getRow("select COUNT(order_id) as count,SUM(money_paid) as money from ".$hhs->table('order_info')." where  suppliers_id='{$_SESSION['suppliers_id']}'");
    $totle_team = $db->getOne("select count(order_id) from ".$hhs->table('order_info')." where extension_code='team_goods' and suppliers_id='{$_SESSION['suppliers_id']}'");
    $yihe = $db->getOne("select COUNT(order_id) as count from ".$hhs->table('order_info')." where point_id > 0 and op_uid!='' and suppliers_id='{$_SESSION['suppliers_id']}'");
    $weihe = $db->getOne("select COUNT(order_id) as count from ".$hhs->table('order_info')." where point_id > 0 and op_uid='' and suppliers_id='{$_SESSION['suppliers_id']}' and ((extension_code = 'team_goods' and team_status = 2 and pay_status = 2) or (pay_status = 2))");
    $smarty->assign('today_order',$today_order['count']);
    $smarty->assign('today_money',$today_order['money']);
    
    $smarty->assign('yestoday_order',$yestoday_order['count']);
    $smarty->assign('yestoday_money',$yestoday_order['money']);
    
    $smarty->assign('teamtoday',$teamtoday);
    $smarty->assign('team_yestoday',$team_yestoday);
    
    $smarty->assign('totle_count',$totle_order['count']);
    $smarty->assign('totle_money',$totle_order['money']);
    $smarty->assign('totle_team',$totle_team);
    $smarty->assign('yihe',$yihe);
    $smarty->assign('weihe',$weihe);
    /*统计结束*/
	$smarty->assign('info',$info);
	$smarty->assign('articlenum',$articlenum);
	$smarty->assign('goodsnum',$goodsnum);
	$smarty->assign('nogoodsnum',$nogoodsnum);
	$delivery_count = $db->getOne("SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('delivery_order') . " where suppliers_id='$suppliers_id' and status=2");
	
	$smarty->assign('delivery_count',$delivery_count);
	//已完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=7";
	$receive_count = $db->getOne($sql);
	$smarty->assign('receive_count',$receive_count);
	//未完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=1";
	$unpay_count = $db->getOne($sql);
	$smarty->assign('unpay_count',$unpay_count);
	
	$smarty->display("m_main.dwt");	
}