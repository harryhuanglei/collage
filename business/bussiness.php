<?php 
define('IN_HHS', true);
require('../includes/init2.php');
require('../includes/lib_order.php');
require('../includes/lib_code.php');
//define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

$smarty->assign('temp_root_path',ROOT_PATH);
$smarty->assign('admin_path',ADMIN_PATH);

include_once ROOT_PATH . 'business/includes/lib_mian.php';
include_once ROOT_PATH . 'admin/includes/lib_goods.php';

$_LANG['trash_product_confirm'] = '您确实要把该货品删除吗？';
$smarty->assign('lang',         $_LANG);


include_once ROOT_PATH . 'business/includes/authcheck.php';

    assign_template();
    $position = assign_ur_here(0, '店铺管理中心');
    $smarty->assign('page_title', $position['title']);
    // 页面标题
    $smarty->assign('ur_here', $position['ur_here']);
    $info = $db->getRow('select * from ' . $hhs->table('suppliers') . " where suppliers_id='{$suppliers_id}'");
    $smarty->assign('info', $info);
    $smarty->assign('helps', get_shop_help());
    // 网店帮助
    $smarty->assign('data_dir', DATA_DIR);
    // 数据目录
    $smarty->assign('action', $action);
    //设置左边菜单
    $smarty->assign('action_list', get_action_list());

    $suppliers_array = get_suppliers_info($suppliers_id);
    $smarty->assign('suppliers_array', $suppliers_array);


/**
 * 获取尚未结算的订单
 * @var [type]
 */
if ($action == 'default'|| $action == 'waitcheck') {
	$order_status    = 5;//已确认
	$shipping_status = 2;//已收货
	$pay_status      = 2;//已付款

	$page   = $_REQUEST['page'] ? intval($_REQUEST['page']) : 1;
	$limit  = 10;
	$offset = ($page - 1) * $limit;
	//今天
	$today = local_date("d");
	//订单添加时间
	$add_time = get_latest_time($_CFG['suppliers_time_format']);
	//搜索时间
	$start_time = $_REQUEST['start_time'] ? strtotime($_REQUEST['start_time']) : 0;
	$end_time   = $_REQUEST['end_time'] ? strtotime($_REQUEST['end_time']) : 0;

	$start_time = $start_time > $add_time ? $add_time : null;
	$end_time   = $end_time > $add_time ? $add_time : null;
	// $where =" where o.`order_id` = og.`order_id` " . 
	// 		" and o.`add_time` <= " . $add_time . 
	// 		" and o.`order_status` = " . $order_status . 
	// 		" and o.`shipping_status` = " . $shipping_status . 
	// 		" and o.`pay_status` = " . $pay_status . 
	// 		" and o.`settlement_sn` = ''" . //结算单号为空
	// 		" and og.`suppliers_id` = " . $suppliers_id;

	// $sql = "select og.`goods_name`,og.`goods_price`,o.`order_sn`, FROM_UNIXTIME(o.`add_time`,'%Y-%m-%d %h:%i:%s') AS 'add_time' from ".$hhs->table('order_info')." as o,".
	// 		$hhs->table('order_goods')." as og".
	// 		$where . 
	// 		" order by og.`rec_id` desc ".
	// 		" limit " . $offset . ','. $limit;
	$filter = array();

	$where =" where " . 
			" o.`add_time` <= " . $add_time . 
			" and o.`order_status` " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . 
			" and o.`shipping_status` " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . 
			" and o.`pay_status` " . db_create_in(array(PS_PAYED, PS_PAYING)) . 
			" and o.`settlement_sn` = ''" . //结算单号为空
			" and o.`suppliers_id` = " . $suppliers_id;
	if($start_time)
	{
		$where .= " o.`add_time` >= " . $start_time ;
		$filter['start_time'] = $start_time;
	}
	if($end_time){
		$where .= " o.`add_time` <= " . $end_time ;
		$filter['end_time'] = $end_time;
	}
	//展示的行
	$sql = "select o.`order_sn`,o.`order_id`,o.`settlement_sn`, FROM_UNIXTIME(o.`add_time`,'%Y-%m-%d %h:%i:%s') AS 'add_time',(o.`goods_amount` + o.`shipping_fee` + o.`insure_fee` + o.`pay_fee` + o.`pack_fee` + o.`card_fee` + o.`tax`) as 'amount' from ".$hhs->table('order_info')." as o ".
			$where . 
			" order by o.`order_id` desc ".
			" limit " . $offset . ','. $limit;
	$rows = $db->getAll($sql);

	//未结算金额
	$sql = "select sum(o.`goods_amount` + o.`shipping_fee` + o.`insure_fee` + o.`pay_fee` + o.`pack_fee` + o.`card_fee`) as 'amount' from ".$hhs->table('order_info')." as o ". $where;
	$amount = $db->getOne($sql);
	
    /* 记录总数 */
    $sql = "SELECT COUNT(*) from ".$hhs->table('order_info')." as o ". $where;
    $record_count = $GLOBALS['db']->getOne($sql);


	$pager = get_pager('bussiness.php', $filter, $record_count, $page);
	$smarty->assign('pager', $pager);
    $smarty->assign('goods_list', $rows);
    $smarty->assign('action', $action);
    $smarty->display('bussiness.dwt');
}
else if($action == 'apply')
{
	$add_time = get_latest_time($_CFG['suppliers_time_format']);
	$allow_apply_date = $add_time + 86400*3;
	$today = local_strtotime(date('Y-m-d'));
	//允许申请结算
	if($today>=$add_time && $today<=$allow_apply_date)
	{
	    /**
	     * 当期申请是否有记录
	     */
		$month = local_date('Ymd', $add_time);
	    $sql = "select count(*) from ".$hhs->table('suppliers_accounts')." where `add_month` = ".$month." and suppliers_id=" . $suppliers_id;
	    if($db->getOne($sql))
	    {
			$msg = "没有任何有效未结算订单！";
			show_message($msg, '未结算列表', 'bussiness.php', 'info');
			exit();
	    }

	    /* 记录总数 */
	    $sql = "SELECT COUNT(*) from ".$hhs->table('order_info')." as o ". $where;
	    $record_count = $GLOBALS['db']->getOne($sql);
	    if($record_count)
	    {
	    	$settlement_sn = get_settlement_sn();
			$now_time = gmtime();
			$suppliers_time_format = $_CFG['suppliers_time_format'];
			//结算周期
			if($suppliers_time_format==15)
			{
				$month = local_date('Ymd', $add_time);
				$start_time = local_strtotime(date('Y-m-01 00:00:00', $add_time));
				$end_time   =  local_strtotime(date('Y-m-15 23:59:59', $add_time));
			}
			if($suppliers_time_format==30)
			{
				$month = local_date('Ymd', $add_time);
				$start_time = local_strtotime(date('Y-m-01 00:00:00', $add_time));
				$end_time   =  local_strtotime(date('Y-m-t 23:59:59', $add_time));
			}

			$sql_insert = $db->query("insert into ".$hhs->table('suppliers_accounts')." (suppliers_id,start_time,end_time,add_month,settlement_sn,add_time) values('$suppliers_id','$start_time','$end_time','$month','$settlement_sn','$now_time')");
			$suppliers_accounts_id = $db->insert_id();
			// 未结算的订单
			$where =" where " . 
					" o.`add_time` <= " . $add_time . 
					" and o.`order_status` " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) . 
					" and o.`shipping_status` " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . 
					" and o.`pay_status` " . db_create_in(array(PS_PAYED, PS_PAYING)) . 
					" and o.`settlement_sn` = ''" . //结算单号为空
					" and o.`suppliers_id` = " . $suppliers_id;
			$sql="select order_id,order_sn,pay_time,add_time,(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee from ".$hhs->table('order_info')." as o " . $where;
			$order_list = $db->getAll($sql);
			/**
			 * 以下来自copy ，没仔细看代码
			 * file sysadm/suppliers.php
			 * act = settlement_act
			 * @var integer
			 */
			$commission_all = 0;
			foreach($order_list as $idx=>$value)
			{
				$commission = $db->getOne("select (commission*goods_number) as total from ".$hhs->table('order_goods')." where order_id='$value[order_id]'");
				$sql = $db->query("insert into ".$hhs->table('suppliers_accounts_detal')." (order_sn,order_id,order_time,commission,suppliers_accounts_id,amount) values ('$value[order_sn]','$value[order_id]','$value[pay_time]','$commission','$suppliers_accounts_id','$value[total_fee]')");
				$commission_all = $commission_all+$commission;
				$total = $total+$value['total_fee'];
			}
			$settlement_amount = $total-$commission_all;
			$sql = $db->query("update ".$hhs->table('suppliers_accounts')." set settlement_amount='$settlement_amount' ,settlement_status=1 where id='$suppliers_accounts_id'");
			/**
			 * copy end
			 */
			//更新order_info结算订单
			$sql = "update ".$hhs->table('order_info')." set `settlement_sn` = '".$settlement_sn."' where `suppliers_id` = " . $suppliers_id . " and `settlement_sn` = '' and `add_time` <= " . $add_time;

			$db->query($sql);


			$msg = "申请成功！申请结算订单号：！" .$settlement_sn;
			//日志
			aclogs($suppliers_accounts_id,$msg,1);

			show_message($msg, '结算订单', 'bussiness.php?act=view&id=' . $suppliers_accounts_id, 'info');
	    }
	    else
	    {
			$msg = "没有任何有效未结算订单！";
			show_message($msg, '未结算列表', 'bussiness.php', 'info');
	    }
	}
	else
	{
		$msg = "申请超期，请下个结算周期再申请！";
		show_message($msg, '未结算列表', 'bussiness.php', 'info');
	}
	exit();
}
else if($action == 'view')
{
	$id            = intval($_REQUEST['id']);
	$page          = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$filter['act'] = $action;
	$filter['id']  = $id;

	//权限
	$sql = "SELECT `settlement_status` from ".$hhs->table('suppliers_accounts')." where id =" .$id . " and `suppliers_id` = " . $suppliers_id;
	$settlement_status = $db->getOne($sql);
	if(! $settlement_status)
	{
		$msg = "没有相关权限！";
		show_message($msg, '未结算列表', 'bussiness.php', 'info');
	}
    $smarty->assign('settlement_status', $settlement_status);

    $record_count = $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('suppliers_accounts_detal') . " as d,".$hhs->table('suppliers_accounts')." as a where a.`suppliers_id` = ".$suppliers_id." and a.`id` = d.`suppliers_accounts_id` and a.`id` = ".$id);
    $pager = get_pager('bussiness.php', $filter, $record_count, $page);
    $goods_list = get_accounts_detal_views($id, $pager['size'], $pager['start']);

    $smarty->assign('logs', aclogslist($id));

    $smarty->assign('id', $id);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('bussiness.dwt');

}
else if($action == 'checkout')
{
	$id = intval($_REQUEST['id']);
	if(!$id)
		die('error');
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set settlement_status=4 where id=".$id ." and `suppliers_id` = " .$suppliers_id . " and settlement_status=3";
	$db->query($sql);
	$msg = "操作成功！";
	//日志
	aclogs($id,'确认已收款',1);

	show_message($msg, '未结算列表', 'bussiness.php', 'info');
}
else if($action == 'apply5')
{
	$id          = intval($_REQUEST['id']);
	$msg         = strip_tags($_REQUEST['msg']);
	$is_supplier = 1;
	//权限
	$sql = "SELECT `settlement_status` from ".$hhs->table('suppliers_accounts')." where id =" .$id . " and `suppliers_id` = " . $suppliers_id;
	$settlement_status = $db->getOne($sql);
	if($settlement_status == 5)	
	{
		$msg = "投递申诉成功！请等待回复！";
		aclogs($id,$msg,$is_supplier);
		show_message($msg, '结算订单详情', 'bussiness.php?act=view&id=' . $id, 'info');		
	}
	else{
		$msg = "无法操作！";
		show_message($msg, '结算订单详情', 'bussiness.php?act=view&id=' . $id, 'info');		
	}
}
/**
 * 获取未结算的有效订单截止日期
 * @param  [int] $cfg_date [description]
 * @return [string]           [description]
 */
function get_latest_time($cfg_date)
{
	$today =local_date('d');
	//系统设置结算时间 == 15,半月结算
	if ($cfg_date == 15) 
	{
		//当前月
		if($today > $cfg_date)
		{
			$add_time = local_strtotime(date('Y-m-15 23:59:59'));
		}
		//上月最后一天
		else
		{
			$add_time = local_strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));
		}
	}
	//$cfg_date == 30
	else
	{
		//上月最后一天
		$add_time = local_strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));
	}
	return $add_time;
}

function get_accounts_detal_views($id, $size, $start){

	$sql = "SELECT *,FROM_UNIXTIME(`order_time`,'%Y-%m-%d %h:$i:%s') as 'order_time',(amount-commission) as money from ".$GLOBALS['hhs']->table('suppliers_accounts_detal')." where `suppliers_accounts_id` = ".$id." order by `id` asc limit " . $start .",".$size;
	return $GLOBALS['db']->getAll($sql);
}