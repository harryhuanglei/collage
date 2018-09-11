<?php
/**
 * 小舍电商 销售明细列表程序
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: sale_list.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
$smarty->assign('lang', $_LANG);
if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'query' ||  $_REQUEST['act'] == 'download'))
{
    /* 检查权限 */
    check_authz_json('sale_order_stats');
    if (strstr($_REQUEST['start_date'], '-') === false)
    {
        $_REQUEST['start_date'] = local_date('Y-m-d', $_REQUEST['start_date']);
        $_REQUEST['end_date'] = local_date('Y-m-d', $_REQUEST['end_date']);
    }
    /*------------------------------------------------------ */
    //--Excel文件下载
    /*------------------------------------------------------ */
    if ($_REQUEST['act'] == 'download')
    {
        $file_name = $_REQUEST['start_date'].'_'.$_REQUEST['end_date'] . '_sale';
        $goods_sales_list = get_sale_list_new(false);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$file_name.xls");
        /* 文件标题 */
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_REQUEST['start_date']. $_LANG['to'] .$_REQUEST['end_date']. $_LANG['sales_list']) . "\t\n";
        /* 货号,商品名称,所属商家,订单属性,销量,单价,日期,订单状态 */
        echo hhs_iconv(EC_CHARSET, 'GB2312', '货号') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '商品名称') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '所属商家') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '订单属性') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '销量') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '单价') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '日期') . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', '订单状态') . "\t\n";
        foreach ($goods_sales_list['sale_list_data'] AS $key => $value)
        {
            $position = isset($_GET['order_status']) ? $_GET['order_status'] : '';
            switch ($position){
                case 1: // 待确认
                    $order_status = '待确认';
                    break;
                case 2: // 已确认代付款
                    $order_status = '已确认代付款';
                    break;
                case 3: // 待发货
                    $order_status = '待发货';
                    break;
                case 4: // 已付款
                    $order_status = '已付款';
                    break;
                case 5: // 已发货
                    $order_status = '已发货';
                    break;
                case 6: // 已完成
                    $order_status = '已完成';
                    break;
                case 7: // 取消
                    $order_status = '取消';
                    break;
                case 8: // 已退款
                    $order_status = '已退款';
                    break;
                default:
                    $order_status = '全部';
            }
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_sn']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_name']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', ($value['suppliers_id']) ? $value['suppliers_name'] : '自营店') . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['extension_code'] ? '团购' : '单独购买') . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_num']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_price']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['sales_time']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $order_status) . "\t";
            echo "\n";
        }
        exit;
    }
    $sale_list_data = get_sale_list_new();
    $smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);
    make_json_result($smarty->fetch('sale_list.htm'), '', array('filter' => $sale_list_data['filter'], 'page_count' => $sale_list_data['page_count']));
}
/*---------------------------------------------------- */
//--商品订单列表
/*------------------------------------------------------ */
elseif(isset($_REQUEST['act']) && $_REQUEST['act'] == 'sale_detail')
{
    $goods_sn = $_GET['goods_sn'];
    /* 权限判断 */
    admin_priv('sale_order_stats');
    // 获取购买该产品的所有订单
    $sale_list_data = get_goods_order_list($goods_sn, false);
    /* 赋值到模板 */
    $smarty->assign('storeList',       $storelist);
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);
    $smarty->assign('order_list', $sale_list_data['sale_list_data']);
    $smarty->assign('ur_here',          $_LANG['sell_stats']);
    $smarty->assign('full_page',        1);
    $smarty->assign('ur_here',      $_LANG['sale_list']);
    $smarty->assign('cfg_lang',     $_CFG['lang']);
    /* 显示页面 */
    assign_query_info();
    $smarty->display('sale_detail.htm');
}
/*------------------------------------------------------ */
//--商品明细列表
/*------------------------------------------------------ */
else
{
    /* 权限判断 */
    admin_priv('sale_order_stats');
    /* 时间参数 */
    if (!isset($_REQUEST['start_date']))
    {
        $start_date = local_strtotime('-7 days');
    }
    if (!isset($_REQUEST['end_date']))
    {
        $end_date = local_strtotime('today');
    }
    $sale_list_data = get_sale_list_new();
    //获取商家列表
    $storelist = getStoreList();
    /* 赋值到模板 */
    $smarty->assign('storeList',       $storelist);
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);
    $smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
    $smarty->assign('ur_here',          $_LANG['sell_stats']);
    $smarty->assign('full_page',        1);
    $smarty->assign('ur_here',      $_LANG['sale_list']);
    $smarty->assign('cfg_lang',     $_CFG['lang']);
    /* 显示页面 */
    assign_query_info();
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : local_date('Y-m-d', $start_date);
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : local_date('Y-m-d', $end_date);
    $suppliers_id = isset($_GET['suppliers_id']) ? $_GET['suppliers_id'] : array();
    $order_status = isset($_GET['order_status']) ? $_GET['order_status'] : '';
    $team = isset($_GET['team']) ? $_GET['team'] : '';
    $smarty->assign('start_date', $start_date);
    $smarty->assign('end_date', $end_date);
    $smarty->assign('suppliers_id', $suppliers_id);
    $smarty->assign('order_status', $order_status);
    $smarty->assign('team', $team);
    $url = array(
        'act' => 'download',
        'start_date' => strtotime($start_date),
        'end_date' => strtotime($end_date),
        'suppliers_id' => $suppliers_id,
        'order_status' => $order_status,
        'team' => $team,
    );
    if(!isset($_REQUEST['is_ajax']))
        $smarty->assign('full_page',true);
    else
        $smarty->assign('full_page',false);
    $smarty->assign('action_link',  array('text' => $_LANG['down_sales'],'href'=>'/sysadm/sale_list.php?' . http_build_query($url)));
    $smarty->display('sale_list.htm');
}
/*------------------------------------------------------ */
//--获取销售明细需要的函数
/*------------------------------------------------------ */
/**
 * 取得销售明细数据信息
 * @param   bool  $is_pagination  是否分页
 * @return  array   销售明细数据
 */
function get_sale_list($is_pagination = true)
{
    /* 时间参数 */
    $filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
    $filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);
    /* 查询数据的条件 */
    $where = " WHERE og.order_id = oi.order_id".

             " AND oi.add_time >= '".$filter['start_date']."' AND oi.add_time < '" . ($filter['end_date'] + 86400) . "'";
    $sql = "SELECT COUNT(og.goods_id) FROM " .
           $GLOBALS['hhs']->table('order_info') . ' AS oi,'.
           $GLOBALS['hhs']->table('order_goods') . ' AS og '.
           $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    /* 分页大小 */
    $filter = page_and_size($filter);
    $sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.goods_price '.
           'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn '.
           "FROM " . $GLOBALS['hhs']->table('order_goods')." AS og, ".$GLOBALS['hhs']->table('order_info')." AS oi ".
           $where. " ORDER BY sales_time DESC, goods_num DESC";
    if ($is_pagination)
    {
        $sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
    }
    $sale_list_data = $GLOBALS['db']->getAll($sql);
    foreach ($sale_list_data as $key => $item)
    {
        $sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);
        $sale_list_data[$key]['sales_time']  = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
    }
    $arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
/**
 * 取得销售明细数据信息
 * @param   bool  $is_pagination  是否分页
 * @return  array   销售明细数据
 */
function get_sale_list_new($is_pagination = true)
{
    /* 时间参数 */
    $filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
    $filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);
    $filter['order_status'] = isset($_REQUEST['order_status']) ? $_REQUEST['order_status'] : '';
    $filter['team'] = isset($_REQUEST['team']) ? $_REQUEST['team'] : '';
    /* 查询数据的条件 */
    $where = " WHERE oi.add_time >= '".$filter['start_date']."' AND oi.add_time < '" . ($filter['end_date'] + 86400) . "'";
    //商家筛选
    if(isset($_REQUEST['suppliers_id']) && $_REQUEST['suppliers_id'] != '') {
        $suppliers_id = is_array($_REQUEST['suppliers_id']) ? $_REQUEST['suppliers_id'] : (!is_null(json_decode($_REQUEST['suppliers_id'])) ? explode(',', json_decode($_REQUEST['suppliers_id'])) : explode(',', $_REQUEST['suppliers_id']));
        if(is_array($suppliers_id)) {
            $where .= ' and oi.suppliers_id in (' . implode(',', $suppliers_id) . ')';
            $filter['suppliers_id'] = implode(',', $suppliers_id);
        }else{
            $where .= ' and oi.suppliers_id = '.$suppliers_id;
            $filter['suppliers_id'] = $suppliers_id;
        }
    }
    //订单状态
    if(isset($_REQUEST['order_status']) && $_REQUEST['order_status']!='') $where .= getOrderStatus($_REQUEST['order_status']);
    //是否是团购
    if(isset($_REQUEST['team']) && $_REQUEST['team'] != '') {
        if($_REQUEST['team'] == 0){
            $where .= ' and oi.extension_code= ""';
        }else{
            $where .= ' and oi.extension_code= "team_goods"';
        }
    }
    $sql = 'SELECT og.goods_id FROM ' .
        $GLOBALS['hhs']->table('order_goods') . ' AS og LEFT JOIN ' .
        $GLOBALS['hhs']->table('order_info') . ' AS oi ON og.order_id=oi.order_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('suppliers') . ' AS s ON s.suppliers_id=oi.suppliers_id ' .
        $where .
        ' GROUP BY og.goods_sn';
    $filter['record_count'] = count($GLOBALS['db']->getAll($sql));
    /* 分页大小 */
    $filter = page_and_size($filter);
    $sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, sum(og.goods_number) as goods_num, og.goods_price, s.suppliers_name,s.suppliers_id,oi.extension_code,oi.extension_id FROM ' .
        $GLOBALS['hhs']->table('order_goods') . ' AS og LEFT JOIN ' .
        $GLOBALS['hhs']->table('order_info') . ' AS oi ON og.order_id=oi.order_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('suppliers') . ' AS s ON s.suppliers_id=oi.suppliers_id ' .
        $where .
        ' GROUP BY og.goods_sn' . ' ORDER BY og.goods_sn ASC';
    if ($is_pagination)
    {
        $sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
    }
    $sale_list_data = $GLOBALS['db']->getAll($sql);
    foreach ($sale_list_data as $key => $item)
    {
        $sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);
        $sale_list_data[$key]['sales_time']  = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
    }
    $arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
/**
 * 获取所有商家列表
 * @return mixed
 */
function getStoreList()
{
    $sql = 'SELECT suppliers_id,suppliers_name FROM ' . $GLOBALS['hhs']->table('suppliers') . ' ORDER BY add_time';
    return $GLOBALS['db']->getAll($sql);
}
/**
 * 解析订单状态，并生成where
 * @param $position
 * @return string
 */
function getOrderStatus($position)
{
    switch ($position)
    {
        case 1: // 待确认
            $where = ' and order_status = 0';
            break;
        case 2: // 已确认代付款
            $where = ' and order_status = 1 and pay_status = 0';
            break;
        case 3: // 待发货
            $where = ' and order_status = 1 and pay_status = 2 and shipping_status = 0';
            break;
        case 4: // 已付款
            $where = ' and pay_status = 2';
            break;
        case 5: // 已发货
            $where = ' and shipping_status = 1';
            break;
        case 6: // 已完成
            $where = ' and shipping_status = 2';
            break;
        case 7: // 取消
            $where = ' and order_status = 2';
            break;
        case 8: // 已退款
            $where = ' and order_status = 4';
            break;
        default:
            $where = '';
    }
    return $where;
}
/**
 * 根据goods_id 获取订单信息
 * @param $goods_id
 * @param bool $is_pagination
 * @return array
 */
function get_goods_order_list($goods_sn, $is_pagination = true)
{
    $where = ' where og.goods_sn="' . $goods_sn.'"';
    $sql = 'SELECT oi.order_id,oi.order_sn,oi.extension_code,oi.add_time,oi.pay_time,u.uname,oi.point_id,oi.province,oi.city,oi.district,oi.address,oi.consignee,oi.mobile,oi.goods_amount,oi.shipping_fee,oi.insure_fee,oi.order_amount,oi.order_status,oi.pay_status,oi.shipping_status,oi.is_deal,oi.package_one,og.goods_id,og.goods_name,og.goods_number,og.goods_price,oi.transaction_id,oi.team_sign,oi.team_status FROM ' .
        $GLOBALS['hhs']->table('order_goods') . ' AS og LEFT JOIN ' .
        $GLOBALS['hhs']->table('order_info') . ' AS oi ON og.order_id=oi.order_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('suppliers') . ' AS s ON s.suppliers_id=oi.suppliers_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('users') . ' AS u ON u.user_id=oi.user_id ' .
        $where . ' ORDER BY oi.add_time DESC';
    if ($is_pagination)
    {
        //$sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
    }
    $sale_list_data = $GLOBALS['db']->getAll($sql);
    foreach ($sale_list_data as &$item)
    {
        $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        $item['pay_time'] = date('Y-m-d H:i:s', $item['pay_time']);
        $item['region'] = getRegionName($item['province']) . ' ' . getRegionName($item['city']) . ' ' .getRegionName($item['district']);
        $item['order_total'] = number_format($item['goods_amount'] + $item['shipping_fee'] + $item['insure_fee'], 2);
    }
    $arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
/**
 * 格式化地区名称
 * @param $id
 * @return mixed
 */
function getRegionName($id)
{
    if($id > 0)
    {
       $sql = 'SELECT region_name FROM ' . $GLOBALS['hhs']->table('region') . ' WHERE region_id=' . $id;
       $region_name = $GLOBALS['db']->getOne($sql); 
    }else
    {
        $region_name = '';
    }
    return $region_name;
}
?>