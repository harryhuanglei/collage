<?php
/**
 * 小舍电商商家管理
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: suppliers.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require (dirname(__FILE__) . '/includes/init.php');
include_once (ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
    $_LANG['os'][OS_UNCONFIRMED] = '未确认';
    $_LANG['os'][OS_CONFIRMED] = '已确认';
    $_LANG['os'][OS_CANCELED] = '<font color="red"> 取消</font>';
    $_LANG['os'][OS_INVALID] = '<font color="red">无效</font>';
    $_LANG['os'][OS_RETURNED] = '<font color="red">已退货</font>';//退货
    $_LANG['os'][OS_SPLITED] = '已分单';
    $_LANG['os'][OS_SPLITING_PART] = '部分分单';

    $_LANG['ss'][SS_UNSHIPPED] = '未发货';
    $_LANG['ss'][SS_PREPARING] = '配货中';
    $_LANG['ss'][SS_SHIPPED] = '已发货';
    $_LANG['ss'][SS_RECEIVED] = '收货确认';
    $_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
    $_LANG['ss'][SS_SHIPPED_ING] = '发货中';



    $_LANG['pos'][SS_UNSHIPPED] = '未核销';
    $_LANG['pos'][SS_PREPARING] = '配货中';
    $_LANG['pos'][SS_SHIPPED] = '已核销';
    $_LANG['pos'][SS_RECEIVED] = '已核销';
    $_LANG['pos'][SS_SHIPPED_PART] = '已发货(部分商品)';
    $_LANG['pos'][SS_SHIPPED_ING] = '发货中';



    $_LANG['ps'][PS_UNPAYED] = '未付款';
    $_LANG['ps'][PS_PAYING] = '付款中';
    $_LANG['ps'][PS_PAYED] = '已付款';
    $_LANG['ps'][PS_REFUNDED] = '已退款';

    $_LANG['team_status'][0] = '待付款';
    $_LANG['team_status'][1] = '正在进行中';
    $_LANG['team_status'][2] = '成功';
    $_LANG['team_status'][3] = '失败';
    $smarty->assign('lang', $_LANG);
/* ------------------------------------------------------ */
// -- 供货商列表
/* ------------------------------------------------------ */
if ($_REQUEST['act'] == 'list') 
{
    /* 检查权限 */
    admin_priv('suppliers_list');
    /* 查询 */
    $result = get_goods_order_list();
    /* 模板赋值 */
    $smarty->assign('status_list', $_LANG['cs']);
    $smarty->assign('ur_here', '销售明细'); // 当前导航
    $smarty->assign('full_page', 1); // 翻页参数
    $smarty->assign('order_list', $result['result']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    /* 显示模板 */
    assign_query_info();
    $smarty->display('sale_detail.htm');
}
/* ------------------------------------------------------ */
// -- 排序、分页、查询
/* ------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query') 
{
    check_authz_json('suppliers_list');
    $result = get_goods_order_list();
    $smarty->assign('order_list', $result['result']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    /* 排序标记 */
    make_json_result($smarty->fetch('sale_detail.htm'), '', 
    array(
        'filter' => $result['filter'],
        'page_count' => $result['page_count']
    ));
}
/**
 * 获取供应商列表信息
 * @access public
 * @param            
 * @return void
 */
function get_goods_order_list($is_down = true)
{
    $result = get_filter();
    if ($result === false) 
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
        /* 过滤信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'oi.order_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['goods_id'] = ! empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
        $where = " WHERE 1 AND og.goods_id = '".$filter['goods_id']."' ";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_goods') . ' as og ' . $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;
        /* 查询 */
        $sql = "SELECT (oi.goods_amount + oi.tax + oi.shipping_fee+ oi.insure_fee + oi.pay_fee + oi.pack_fee + oi.card_fee ) as total_fee,oi.order_id,oi.order_sn,oi.extension_code,oi.add_time,oi.pay_time,u.uname,oi.point_id,oi.province,oi.city,oi.district,oi.address,oi.consignee,oi.mobile,oi.goods_amount,oi.shipping_fee,oi.insure_fee,oi.order_amount,oi.order_status,oi.pay_status,oi.shipping_status,oi.is_deal,oi.package_one,og.goods_id,og.goods_name,og.goods_number,og.goods_price,oi.transaction_id,oi.team_sign,oi.team_status FROM " .$GLOBALS['hhs']->table('order_goods') . ' AS og LEFT JOIN ' .
        $GLOBALS['hhs']->table('order_info') . ' AS oi ON og.order_id=oi.order_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('suppliers') . ' AS s ON s.suppliers_id=oi.suppliers_id LEFT JOIN ' .
        $GLOBALS['hhs']->table('users') . ' AS u ON u.user_id=oi.user_id ' .
        $where ." ORDER BY $filter[sort_by] $filter[sort_order] ";
        if ($is_down == true) 
        {
            $sql .= "LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
        }
        set_filter($filter, $sql);
    } 
    else 
    {
        $sql = $result['sql'];
        $filter = $result['filter'];
    }
    $row = $GLOBALS['db']->getAll($sql);
    foreach ($row as $idx => $value) 
    {
        $row[$idx]['add_time'] = local_date('Y-m-d H:i:s', $value['add_time']);
        $row[$idx]['pay_time'] = local_date('Y-m-d H:i:s', $value['pay_time']);
        $row[$idx]['total_fee'] = price_format($value['total_fee']);
    }
    $arr = array(
        'result' => $row,
        'filter' => $filter,
        'page_count' => $filter['page_count'],
        'record_count' => $filter['record_count']
    );
    return $arr;
}
?>