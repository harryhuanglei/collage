<?php
define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
include(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/order.php');
require_once(ROOT_PATH . 'includes/lib_order.php');/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    admin_priv('distribution_manage');
	$smarty->assign('lang', $_LANG);
    $smarty->assign('status_list', $_LANG['cs']);   // 订单状态
    $smarty->assign('ur_here',     '分销佣金列表');
    $smarty->assign('action_link',  array('text' => '导出', 'href'=>'distribution.php?act=download'));
    $user_list = distribution_list();
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
    $order_sn = empty($_REQUEST['order_sn']) ? ''     : trim($_REQUEST['order_sn']);
    $smarty->assign('order_sn',    $order_sn);

    assign_query_info();
    $smarty->display('distribution_list.htm');
}
/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
   
    $user_list = distribution_list();
	$smarty->assign('lang', $_LANG);
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
   
    $sort_flag  = sort_flag($user_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    
    make_json_result($smarty->fetch('distribution_list.htm'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}
elseif ($_REQUEST['act'] == 'download')
{
    $filename='佣金结算信息';
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename.xls");
     	 		 	 	 	 	 	
    /* 订单概况 */
    $result = distribution_list(false);
    $data = '订单号' . "\t";
    $data .= "分销会员\t";
    $data .= "商品总额\t";
    $data .= "佣金比例\t";
    $data .= "佣金\t";
    $data .= "分销等级\t";
    $data .= "订单号\t";
    $data .= "下单时间\t";
    $data .= "消费会员\t";
    $data .= "订单状态\t";
    $data .= "结算状态\t\n";


    $i=1;
    if(!empty($result['user_list'])){
        foreach($result['user_list'] as $k=>$v){
            $data .= $v['order_id']."\t";
            $data .= $v['distribution_user']."\t";
            $data .= $v['amount']."\t";
            	
            $data .= $v['rate']."\t";
            $data .= $v['money']."\t";
            $data .= $v['level']."\t";
            $data .= $v['order_sn']."\t";
            $data .= $v['add_time']."\t";
            $data .= $v['buy_user']."\t";
         
            $data .= $_LANG['os'][$v['order_status']].','.$_LANG['ps'][$v['pay_status']].','.$_LANG['ss'][$v['shipping_status']]."\t";
            $data .= ($v['update_at'] == 0 ? '未结算' : '已结算' . $v['update_at']) ."\t\n";
        }
    }

    echo hhs_iconv(EC_CHARSET, 'GB2312', $data) . "\t";
    exit;

}




function distribution_list($is_page=true)
{
	 $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'f.order_id' : trim($_REQUEST['sort_by']);
	 $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);
	 $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
	 $filter['user_name'] = empty($_REQUEST['user_name']) ? ''     : trim($_REQUEST['user_name']);
     $filter['suppliers_name'] = empty($_REQUEST['suppliers_name']) ? ''     : trim($_REQUEST['suppliers_name']);
     $filter['order_sn'] = empty($_REQUEST['order_sn']) ? ''     : trim($_REQUEST['order_sn']);

	 $filter['dstatus'] = empty($_REQUEST['dstatus']) ? '-1'     : trim($_REQUEST['dstatus']);
	 
	 $filter['start_time'] = empty($_REQUEST['start_time']) ? ''     : trim($_REQUEST['start_time']); 
	 $filter['end_time'] = empty($_REQUEST['end_time']) ? ''     : trim($_REQUEST['end_time']);
	//$filter['user_id'] = empty($_REQUEST['user_id']) ? ''     : trim($_REQUEST['user_id']);
	 if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
	 {
         $filter['user_name'] = json_str_iconv($filter['user_name']);
	     $filter['suppliers_name'] = json_str_iconv($filter['suppliers_name']);
	 }
	 
	 $ex_where = ' WHERE 1 ';
	
	 if($filter['dstatus']!= -1)
	 {
		if($filter['dstatus']==2)
		{
			$ex_where.=" and f.update_at=0" ;
		}
		else
		{
			$ex_where.=" and f.update_at>0" ;
		}
	 }

	 if($filter['user_name'])
	 {
		$uid = $GLOBALS['db']->getOne("select user_id from ".$GLOBALS['hhs']->table('users')." where uname='$filter[user_name]'");
		
		if($uid)
		{
			$ex_where.=" and f.user_id='$uid'" ;
		}
	 }
     if($filter['suppliers_name'])
     {
        $suppliers_id = $GLOBALS['db']->getOne("select suppliers_id from ".$GLOBALS['hhs']->table('suppliers')." where suppliers_name='$filter[suppliers_name]'");

        if($suppliers_id)
        {
            $ex_where.=" and o.suppliers_id='$suppliers_id'" ;
        }
     }     
	 if($filter['start_time']&&$filter['end_time'])
	 {
		 $start_time = local_strtotime($filter['start_time']);
		 $end_time = local_strtotime($filter['end_time']);
		 $ex_where .= " AND o.add_time >= '$start_time' and  o.add_time <= '$end_time' ";
	 }
	
     if($filter['order_sn'])
     {
         $ex_where .= " AND o.order_sn = '".$filter['order_sn']."' ";
     }

         //综合状态
        switch($filter['composite_status'])
        {
            case CS_AWAIT_PAY :
                $ex_where .= order_query_sql('await_pay','o.');
                break;

            case CS_AWAIT_SHIP :
                $ex_where .= order_query_sql('await_ship','o.');
                break;

            case CS_FINISHED :
                $ex_where .= order_query_sql('finished','o.');
                break;

            case PS_PAYING :
                if ($filter['composite_status'] != -1)
                {
                    $ex_where .= " AND o.pay_status = '$filter[composite_status]' ";
                }
                break;
            case OS_SHIPPED_PART :
                if ($filter['composite_status'] != -1)
                {
                    $ex_where .= " AND o.shipping_status  = '$filter[composite_status]'-2 ";
                }
                break;
            default:
                if ($filter['composite_status'] != -1)
                {
                    $ex_where .= " AND o.order_status = '$filter[composite_status]' ";
                }
        }	 

	 $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('fenxiao') . " as f LEFT JOIN ".$GLOBALS['hhs']->table('order_info')." as o ON o.order_id=f.order_id ".$ex_where);
 
	  /* 分页大小 */
	  $filter = page_and_size($filter);
	
	  $sql = "SELECT f.*,o.order_status,o.pay_status,o.shipping_status,o.order_sn,o.user_id as 'buy_user_id',o.add_time,o.suppliers_id ".
			  " FROM " . $GLOBALS['hhs']->table('fenxiao')." as f left join ".$GLOBALS['hhs']->table('order_info')." as o on o.order_id=f.order_id " . $ex_where .
			  " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] ;
	if($is_page){
		$sql.= " LIMIT " . $filter['start'] . ',' . $filter['page_size'];
	} 
	
	set_filter($filter, $sql);
    $user_list = $GLOBALS['db']->getAll($sql);
	foreach($user_list as $idx=>$v)
	{
		$user_list[$idx]['distribution_user'] = $GLOBALS['db']->getOne("select uname as user_name from ".$GLOBALS['hhs']->table('users')." where user_id='$v[user_id]'");

		$user_list[$idx]['buy_user'] = $GLOBALS['db']->getOne("select uname as user_name from ".$GLOBALS['hhs']->table('users')." where user_id='$v[buy_user_id]'");

		$user_list[$idx]['add_time']  = local_date("Y-m-d H:i:s",$v['add_time']);
	   
       if($v['update_at'])
        $user_list[$idx]['update_at']  = local_date("Y-m-d H:i:s",$v['update_at']);

       if($v['suppliers_id']){        
        $user_list[$idx]['suppliers_name']  = $GLOBALS['db']->getOne("select suppliers_name from ".$GLOBALS['hhs']->table('suppliers')." where suppliers_id='$v[suppliers_id]'");
       }
       else{
        $user_list[$idx]['suppliers_name'] = '自营';
       }

		$user_list[$idx]['distribution_total']  = price_format($v['distribution_total']);
		$user_list[$idx]['goods_amount']  = price_format($v['goods_amount']);
		
		
	}
	
	
	
    $arr = array('user_list' => $user_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}