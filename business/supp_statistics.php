<?php
define('IN_HHS', true);
//------------------------------------------------------------------------------------------
//发货订单统计
if($action == 'sale_list')
{
    $select_payment_sql = "select pay_id, pay_name from ".$GLOBALS['hhs']->table('payment')." where is_online = 1 and enabled = 1";
    $payment = $GLOBALS['db']->getAll($select_payment_sql);
    $smarty->assign('payment',$payment);
    $order_list = getSuppOrder($action);
    $smarty->assign('pager', $order_list['pager']);
    $smarty->assign('order_list',$order_list['orders']);
    $order_list['filter']['start_date']=local_date('Y-m-d H:i:s',$order_list['filter']['start_time']);
    $order_list['filter']['end_date']=local_date('Y-m-d H:i:s',$order_list['filter']['end_time']);
    $smarty->assign('filter',$order_list['filter']);
    $smarty->assign('totle_money',$order_list['totle_money']);
    $smarty->assign('action',$action);
    assign_query_info();
    $smarty->display('suppliers_statistics.dwt');
}
elseif ($action == 'sale_list_download')
{
    $order_list = getSuppOrder('',true,false);
    $filename='订单信息.csv';
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
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
elseif($action =='point_list')
{
    $select_payment_sql = "select pay_id, pay_name from ".$GLOBALS['hhs']->table('payment')." where is_online = 1 and enabled = 1";
    $payment = $GLOBALS['db']->getAll($select_payment_sql);
    $smarty->assign('payment',$payment);
    /*商家自提店*/
    $smarty->assign('suppliers_point_list',getSuppPoint($_SESSION['suppliers_id']));
    $order_list = getSuppOrder($action,false);
    $smarty->assign('pager', $order_list['pager']);
    $smarty->assign('order_list',$order_list['orders']);
    $order_list['filter']['start_date']=local_date('Y-m-d H:i:s',$order_list['filter']['start_time']);
    $order_list['filter']['end_date']=local_date('Y-m-d H:i:s',$order_list['filter']['end_time']);
    $smarty->assign('filter',$order_list['filter']);
    $smarty->assign('totle_money',$order_list['totle_money']);
    $smarty->assign('action',$action);
    assign_query_info();

    /* 显示页面 */
    $smarty->display('suppliers_statistics.dwt');
	
}
elseif($action =='point_list_download')
{
  	$order_list = getSuppOrder('',false,false);
    $filename='订单信息.csv';
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
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
/**
*
*获取商家订单统计信息
*
*@param  $point_id 自提订单
*
*@param  $suppliers_id 商家id
*
*
*
*
**/
function getSuppOrder($action=null,$is_point=true,$is_page=true)
{
    /*初始化首页*/
    $suppliers_id = intval($_SESSION['suppliers_id']);
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /*过滤条件*/
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);  
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
    $filter['pay_id'] = isset($_REQUEST['pay_id']) ? intval($_REQUEST['pay_id']) : -1;
    $filter['point_id'] = !empty($_REQUEST['point_id']) ? intval($_REQUEST['point_id']) : -1;
    $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : 4;
    $filter['transaction_id'] = empty($_REQUEST['transaction_id']) ? '' : trim($_REQUEST['transaction_id']);
    $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
    $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
    $where = "WHERE  o.suppliers_id=".$suppliers_id;
    /* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */
    $sql = "SELECT agency_id FROM " . $GLOBALS['hhs']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
    $agency_id = $GLOBALS['db']->getOne($sql);
    if($filter['point_id'] != -1)
    {
        $where .= " AND o.point_id = '$filter[point_id]'";
    }
    if($is_point)
    {
        $where .= " AND o.point_id = 0 ";
        switch($filter['composite_status'])
        {
                
                case 102 :
                $where .= "AND o.order_status = 5 AND o.shipping_status = 2 and o.pay_status = 2";
                break;
                case 3 :
                $where .= " AND o.order_status = 4  AND o.shipping_status = 0  AND o.pay_status = 3  ";
                break;
                    
                case 4 :
                $where .= " AND o.order_status IN (4,5) AND o.shipping_status IN (0,2)  AND o.pay_status IN (2,3)  ";
                break;
                case 5 :
                $where .= " AND o.order_status = 1  AND o.shipping_status = 0  AND o.pay_status = 2  ";
                break;
        }
    }else
    {
       $where .= " AND o.point_id > 0 "; 
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
    }
    if ($agency_id > 0)
    {
        $where .= " AND o.agency_id = '$agency_id' ";
    }
    if ($filter['pay_id'] != -1)
    {
        $where .= " AND o.pay_id  = '$filter[pay_id]'";
    }
    if($filter['transaction_id'])
    {
        $where .= " AND o.transaction_id  LIKE '%" . mysql_like_quote($filter['transaction_id']) . "%'";
    }
    if ($filter['order_sn'])
    {
        $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'"; 
    }
    if ($filter['start_time'])
    {
        $where .= " AND o.add_time >= '$filter[start_time]'";
    }
    if ($filter['end_time'])
    {
        $where .= " AND o.add_time <= '$filter[end_time]'";
    }
    /*记录总数*/
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " AS o ". $where;
    $record_count   = $GLOBALS['db']->getOne($sql);
    $arr=$filter;
    unset($arr['page']);
    $arr['act']=$action;
    $arr['op']='statistics';
    /*分页*/
    $pager  = get_pager('index.php', $arr, $record_count, $page);
    //统计总金额
    $totle_money_sql="select sum(goods_amount+shipping_fee+insure_fee+pack_fee+card_fee-bonus-integral_money) as totle_money from ".$GLOBALS['hhs']->table('order_info')." as o ".$where;
    $totle_money=$GLOBALS['db']->getOne($totle_money_sql);
    /* 查询 */
    $sql = "SELECT o.suppliers_id,u.uname, o.surplus,o.bonus, o.package_one, o.point_id,o.transaction_id,o.team_status,o.team_sign,o.team_first, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid," .
                "o.pay_status, o.consignee, o.tel,o.shipping_id,o.shipping_name,o.invoice_no, o.extension_code, o.extension_id,o.mobile, " .
            "(" . order_amount_field('o.') . ") AS total_fee, " .
            "IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer ".
            " FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .
            " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ". $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ";
    if($is_page)
    {
        $sql.=" LIMIT $pager[start],$pager[size]";
    }
    foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)
    {   
        $filter[$val] = stripslashes($filter[$val]);
    }
    $row = $GLOBALS['db']->getAll($sql);
    /*格式化数据*/
    foreach ($row AS $key => $value)
    {       
        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);
        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);
        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);
        $row[$key]['short_order_time'] = local_date('Y-m-d H:i', $value['add_time']);
        $row[$key]['short_pay_time'] = local_date('Y-m-d H:i', $value['pay_time']);
        $sql="select goods_name from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];
        $row[$key]['goods_namexy']=implode(',',$GLOBALS['db']->getCol($sql));
        $sql="select goods_id from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];
        $row[$key]['goods_idxy']=implode(',',$GLOBALS['db']->getCol($sql));
    }
    
    $arr = array('orders' => $row,'pager'=>$pager,'totle_money'=>$totle_money, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    
    return $arr;
}

/*获取指定商家的自提店*/
function getSuppPoint($suppliers_id = 0)
{
    $suppliers_id = intval($suppliers_id);
    $suppliers_point_sql = "select id, shop_name from ".$GLOBALS['hhs']->table('shipping_point')." where suppliers_id = ".$suppliers_id;
    $suppliers_point = $GLOBALS['db']->getAll($suppliers_point_sql);
    return $suppliers_point;
}

?>