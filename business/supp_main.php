<?php
define('IN_HHS', true);
if($action =='default')
{
	$info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id='$suppliers_id'");
	$goodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where  is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
	$nogoodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where is_on_sale=0 and is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
	$articlenum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('article')." where  suppliers_id=".$_SESSION['suppliers_id']);
	/*发货订单完成*/
	$smarty->assign('payOrderAmount',getOrderAmount($_SESSION['suppliers_id']));
	/*发货订单退款*/
	$smarty->assign('refundOrderAmount',getOrderAmount($_SESSION['suppliers_id'],0,false,ture));
	/*自提订单完成*/
	$smarty->assign('payOrderAmountPoint',getOrderAmount($_SESSION['suppliers_id'],1));
	/*自提订单退款*/
	$smarty->assign('refundOrderAmountPoint',getOrderAmount($_SESSION['suppliers_id'],1,false,ture));
	$smarty->assign('info',$info);
	$smarty->assign('articlenum',$articlenum);
	$smarty->assign('goodsnum',$goodsnum);
	$smarty->assign('nogoodsnum',$nogoodsnum);
	$delivery_count = $db->getOne("SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " where suppliers_id='$suppliers_id' and order_status=1 and shipping_status=0 and pay_status=2 and point_id=0 and ( extension_id=0 or team_status=2)");
	$smarty->assign('delivery_count',$delivery_count);
	//已完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=7";
	$receive_count = $db->getOne($sql);
	$smarty->assign('receive_count',$receive_count);
	//未完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=1";
	$unpay_count = $db->getOne($sql);
	$smarty->assign('unpay_count',$unpay_count);
	
	$smarty->display("supp_main.dwt");	
}
/**
*
*获取商家当前日期的交易金额
*
*@param $point_id 是否是自提
*
*@param $finished 是否完成
*
*@param $refund 是否退款
*
*@return $amount Number 交易金额
**/
function getOrderAmount($suppliers_id=0,$point_id = 0,$finished = ture,$refund = false)
{
	$point_id = intval($point_id);
	$suppliers_id = intval($suppliers_id);
	/*当前日期*/
	$todayDate = local_date('Y-m-d',gmtime());
	$start_time = local_strtotime($todayDate.' 00:00:00');
	$end_time = local_strtotime($todayDate.' 23:59:59');
	/*初始化条件*/
	$where = " WHERE o.suppliers_id = ".$suppliers_id." AND oa.log_time > ".$start_time." AND oa.log_time < ".$end_time;
	/*发货订单完成的订单*/
	if($finished && $point_id == 0)
	{
		$where .= " AND oa.order_status = 5 AND oa.shipping_status = 2 AND oa.pay_status = 2 AND o.point_id = 0 ";
	}
	/*自提订单完成的订单*/
	if($finished && $point_id > 0)
	{
		$where .= " AND oa.order_status = 1 AND oa.shipping_status = 2 AND oa.pay_status = 2 AND o.point_id > 0 ";
	}
	/*发货订单退款*/
	if($refund && $point_id == 0)
	{
		$where .= " AND oa.pay_status = 3 AND o.point_id = 0 ";
	}
	/*自提订单退款*/
	if($refund && $point_id > 0)
	{
		$where .= " AND oa.pay_status = 3 AND o.point_id > 0 ";
	}
	/*查询语句*/
	$sql = "select sum(o.goods_amount+o.shipping_fee+o.insure_fee+o.pay_fee+o.pack_fee+o.card_fee) FROM ".$GLOBALS['hhs']->table('order_info')." AS o LEFT JOIN ".$GLOBALS['hhs']->table('order_action')." AS oa on o.order_id = oa.order_id ".$where;
    $amount = $GLOBALS['db']->getOne($sql);
    if($amount)
    {
    	return price_format($amount);
    }else
    {
    	return '0.00';
    }
}