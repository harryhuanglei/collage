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
 */

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');

/*------------------------------------------------------ */
//-- 订单列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{

   
    /* 检查权限 */
    admin_priv('order_view');
    $refund_ex = 'refund_list' == $_REQUEST['act'] ? " refund_status>'0'" : "";

   
    $smarty->assign('action_link2', array('href' => 'statistics_point.php?act=download', 'text' => "导出"));
	
	$smarty->assign('suppliers_list_name', suppliers_list_name());
	
    $select_payment_sql = "select pay_id, pay_name from ".$GLOBALS['hhs']->table('payment')." where is_online = 1 and enabled = 1";
    $payment = $GLOBALS['db']->getAll($select_payment_sql);
    
    $smarty->assign('payment',$payment);
    $smarty->assign('status_list', $_LANG['cs']);   // 订单状态

    $smarty->assign('full_page',        1);

    $order_list = order_list($refund_ex);

    $smarty->assign('totle_money',   $order_list['totle_money']);
    $smarty->assign('order_list',   $order_list['orders']);
    //print_r($order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('statistics_order_list_point.htm');
}
//获取自提店列表
if($_REQUEST['act'] == 'get_point_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    
    $json = new JSON;
    
    $result = array('error' => 1,'content' =>'');
    
    $suppliers_id = $_POST['suppliers_id'];
    
    if($suppliers_id != '')
    {
        $suppliers_id = intval($suppliers_id);
        $point_list = getOrderPoint($suppliers_id);
    }
    
    if($point_list)
    {
        $result['error'] = 0;
        
        $result['content'] = $point_list;
    }
    
    die($json->encode($result));
}

elseif ($_REQUEST['act'] == 'download')
{
    admin_priv('order_view');
    $order_list = order_list('',false);
	
	

    $filename='订单信息.csv';
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');

    /*
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename.xls");*/
    /* 订单概况 */


    $data = "总金额"."\t,";
    $data .= $order_list['totle_money']."元,";
    $data .="\t,\t,\t,\t,\t,\t,\t,\n";
    $data .= "订单号,";
    $data .= "支付时间,";
    $data .= "收货人,";
    $data .= "总金额,";
    $data .= "应付金额,";
    $data .= "红包抵扣,";
    $data .= "商品ID,";
    $data .= "商品名称,";
    $data .= "商品数量,";
    $data .= "微信单号\n";
    $i=1;
    if(!empty($order_list['orders'])){
            
        foreach($order_list['orders'] as $k=>$v){
			
			$sql="select g.*,og.goods_number,og.goods_attr,og.goods_price   from ".$hhs->table('order_goods')." as og left join ".
                    $hhs->table('goods')." as g on og.goods_id=g.goods_id where og.order_id=".$v['order_id'];
            $goods_all=$db->getAll($sql);
            $goods=$goods_all[0];
        
            $data .= $v['order_sn']."\t,";
            $data .= $v['formated_pay_time'].",";
            $data .= $v['consignee'].",";
            $data .= $v['total_fee'].",";
            $data .= $v['order_amount'].",";
            $data .= floatval($v['bonus']).",";
            $data .= $goods['goods_id'].",";
            $data .= $goods['goods_name'].",";
            $data .= intval($goods['goods_number']).','; 
            $data .= $v['transaction_id']."\t,\n";
			if(count($goods_all)>1){
                foreach($goods_all as $kk=>$vv){
                    if($kk>0){
                        $data .= "\t,\t,\t,\t,\t,\t,\t";
						$data.=$vv['goods_id'].",";
                        $data.=$vv['goods_name'].",";
                        $data .= intval($vv['goods_number']).',';
                        $data.="\t,\n";
                    }
                }
            }
            
        }
    }

    echo hhs_iconv(EC_CHARSET, 'GB2312', $data) . "\n";
    exit;

}

elseif ($_REQUEST['act'] == 'query')
{
    /* 检查权限 */
    admin_priv('order_view');

    $refund_ex = 'refund_query' == $_REQUEST['act'] ? " refund_status>'0'" : "";
    $order_list = order_list($refund_ex);
    
    $tpl_file = 'statistics_order_list_point.htm';
    $smarty->assign('totle_money',   $order_list['totle_money']);
    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $sort_flag  = sort_flag($order_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch($tpl_file), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}



/**
 *  获取订单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function order_list($refund_ex="",$is_page=true,$is_suppliers=false)
{

    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        
        $filter['pay_id'] = isset($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : -1;
		
		$filter['point_id'] = isset($_REQUEST['point_id']) ? intval($_REQUEST['point_id']) : '-1';

        $filter['suppliers_id'] = isset($_REQUEST['suppliers_id']) ? intval($_REQUEST['suppliers_id']) : '-1';

        $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : 4;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
        
        $filter['transaction_id'] = empty($_REQUEST['transaction_id']) ? -1 : $_REQUEST['transaction_id'];
        
        $where = " WHERE 1 and o.point_id > 0 ";

        if ($filter['order_sn'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['pay_id'] != -1)
        {
            $where .= " AND o.pay_id  = '".$filter['pay_id']."' ";
        }
		if ($filter['point_id'] != -1)
        {
            $where .= " AND o.point_id  = '".$filter['point_id']."' ";
        }
        if ($filter['suppliers_id'] != -1)
        {
            $where .= " AND o.suppliers_id  = '$filter[suppliers_id]' ";
        }
        if ($filter['start_time'])
        {
            $where .= " AND o.add_time >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND o.add_time <= '$filter[end_time]'";
        }
        
        
        if ($filter['transaction_id']!=-1)
        {
            $where .= " AND o.transaction_id like '%$filter[transaction_id]%' ";
        }
       

        switch($filter['composite_status'])
        {
            
            case 102 :
                $where .= "AND o.order_status = 1 AND o.shipping_status = 2 and o.pay_status = 2";
                break;

            case 3 :

                $where .= " AND o.order_status = 4  AND o.shipping_status = 0  AND o.pay_status = 3  ";
                break;
				
			case 4 :

                $where .= " AND o.order_status IN (1,4) AND o.shipping_status IN (0,2)  AND o.pay_status IN (2,3)  ";
                break;
			case 5 :

                $where .= " AND o.order_status = 1  AND o.shipping_status = 0  AND o.pay_status = 2  ";
                break;
        }
        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['HHSCP']['page_size']) && intval($_COOKIE['HHSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['HHSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM ". $GLOBALS['hhs']->table('order_info')." AS o ".$where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);

        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        if ($_REQUEST['page_size'])
        {
            $limit = "LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
        }
        //统计总金额
        $totle_money_sql="select sum(goods_amount+shipping_fee+insure_fee+pack_fee+card_fee-bonus-integral_money) as totle_money from ".$GLOBALS['hhs']->table('order_info')." as o ".$where;
        $totle_money=$GLOBALS['db']->getOne($totle_money_sql);
        /* 查询 */
        if($is_page){
            $sql = "SELECT u.uname, o.surplus,o.bonus, o.package_one, o.point_id,o.transaction_id,o.team_status,o.team_sign,o.team_first, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid," .
                "o.pay_status, o.consignee, o.tel,o.shipping_id,o.shipping_name,o.invoice_no, o.extension_code, o.extension_id,o.mobile, " .
                "(" . order_amount_field('o.') . ") AS total_fee, g.goods_id,g.goods_name, " .
                "IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer ".
                " FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .
                " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ".
                " LEFT JOIN " .$GLOBALS['hhs']->table('goods'). " AS g ON o.goods_id=g.goods_id ".
                " LEFT JOIN " .$GLOBALS['hhs']->table('order_goods'). " AS og ON o.order_id=og.order_id ".
                $where .
                " GROUP BY o.order_id ORDER BY $filter[sort_by] $filter[sort_order] ".
                " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";



        }else{

            $sql = "SELECT u.uname, o.surplus,o.bonus, o.package_one, o.point_id,o.transaction_id,o.team_status,o.team_sign,o.team_first, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid," .
                "o.pay_status, o.consignee, o.tel,o.shipping_id,o.shipping_name,o.invoice_no, o.extension_code, o.extension_id,o.mobile, " .
                "(" . order_amount_field('o.') . ") AS total_fee, g.goods_id,g.goods_name, " .
                "IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer ".
                " FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .
                " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ".
                " LEFT JOIN " .$GLOBALS['hhs']->table('goods'). " AS g ON o.goods_id=g.goods_id ".
                " LEFT JOIN " .$GLOBALS['hhs']->table('order_goods'). " AS og ON o.order_id=og.order_id ".
                $where .
                " GROUP BY o.order_id ORDER BY $filter[sort_by] $filter[sort_order] ";

        }
        foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)
        {
            $filter[$val] = stripslashes($filter[$val]);
        }
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式话数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);
        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);
        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
        $row[$key]['formated_order_time'] = local_date('Y-m-d H:i:s', $value['add_time']);
        $row[$key]['formated_pay_time'] = local_date('Y-m-d H:i:s', $value['pay_time']);
        /* 取得区域名 */
        $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS p ON o.province = p.region_id " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS t ON o.city = t.region_id " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS d ON o.district = d.region_id " .
            "WHERE o.order_id = '$value[order_id]'";
        $row[$key]['region'] = $GLOBALS['db']->getOne($sql);

        $row[$key]['refund_goods_list'] = get_order_goods_list($value['order_id'], " and refund_status>0");
        if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED)
        {
            /* 如果该订单为无效或取消则显示删除链接 */
            $row[$key]['can_remove'] = 1;
        }
        else
        {
            $row[$key]['can_remove'] = 0;
        }
        //商品信息
        $sql="select goods_price,goods_number from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];
        $goods=$GLOBALS['db']->getRow($sql);
        $row[$key]['goods_price'] =$goods['goods_price'];
        $row[$key]['goods_number'] = $goods['goods_number'];

        $sql="select goods_name from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];

        $row[$key]['goods_namexy']=implode(',',$GLOBALS['db']->getCol($sql));

        $sql="select goods_id from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];

        $row[$key]['goods_idxy']=implode(',',$GLOBALS['db']->getCol($sql));

    }
    $arr = array('orders' => $row,'totle_money'=>$totle_money, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

function d($arr){
    echo '<pre>';
    var_dump($arr);
    die;
}
/*获取自提店信息*/
function getOrderPoint($suppliers_id = 0)
{
	
	$suppliers_id = intval($suppliers_id);
	
	$sql="select id,shop_name from ".$GLOBALS['hhs']->table('shipping_point')." where suppliers_id=".$suppliers_id;
	
	return $GLOBALS['db']->getAll($sql);
}
?>
