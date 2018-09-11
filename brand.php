<?php

/**
 * 小舍电商 品牌列表
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: brand.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

/* 获得请求的分类 ID */
if (!empty($_REQUEST['id']))
{
    $brand_id = intval($_REQUEST['id']);
}
if (!empty($_REQUEST['brand']))
{
    $brand_id = intval($_REQUEST['brand']);
}
if (empty($brand_id))
{
    /* 缓存编号 */
    $cache_id = sprintf('%X', crc32($_CFG['lang']));
    if (!$smarty->is_cached('brand_list.dwt', $cache_id))
    {
        assign_template();
        $position = assign_ur_here('', $_LANG['all_brand']);
        $smarty->assign('page_title',      $position['title']);    // 页面标题
        $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

        $smarty->assign('categories',      get_categories_tree()); // 分类树
        $smarty->assign('top_goods',       get_top10());           // 销售排行

        $smarty->assign('brand_list', get_brands());
    }
    $smarty->display('brand_list.dwt', $cache_id);
    exit();
}

/* 初始化分页信息 */
$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
$size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
$cate = !empty($_REQUEST['cat'])   && intval($_REQUEST['cat'])   > 0 ? intval($_REQUEST['cat'])   : 0;

/* 排序、显示方式以及类型 */
$default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
$default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
$default_sort_order_type   = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'shop_price' : 'last_update');

$sort  = (isset($_REQUEST['sort'])  && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'shop_price', 'last_update'))) ? trim($_REQUEST['sort'])  : $default_sort_order_type;
$order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')))                              ? trim($_REQUEST['order']) : $default_sort_order_method;
$display  = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text'))) ? trim($_REQUEST['display'])  : (isset($_COOKIE['HHS']['display']) ? $_COOKIE['HHS']['display'] : $default_display_type);
$display  = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
setcookie('HHS[display]', $display, gmtime() + 86400 * 7);

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

/* 页面的缓存ID */
$cache_id = sprintf('%X', crc32($brand_id . '-' . $display . '-' . $sort . '-' . $order . '-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-' . $cate));

if (!$smarty->is_cached('brand.dwt', $cache_id))
{
    $brand_info = get_brand_info($brand_id);

    if (empty($brand_info))
    {
        hhs_header("Location: ./\n");
        exit;
    }

    $smarty->assign('data_dir',    DATA_DIR);
    $smarty->assign('keywords',    htmlspecialchars($brand_info['brand_desc']));
    $smarty->assign('description', htmlspecialchars($brand_info['brand_desc']));

    /* 赋值固定内容 */
    assign_template();
    $position = assign_ur_here($cate, $brand_info['brand_name']);
    $smarty->assign('page_title',     $position['title']);   // 页面标题
    $smarty->assign('ur_here',        $position['ur_here']); // 当前位置
    $smarty->assign('brand_id',       $brand_id);


    $smarty->assign('show_marketprice', $_CFG['show_marketprice']);



    $smarty->assign('brand',           $brand_info);

    $count = goods_count_by_brand($brand_id, $cate);

    $goodslist = brand_get_goods($brand_id, $cate, $size, $page, $sort, $order);

    $smarty->assign('goods_list',      $goodslist);
    $smarty->assign('script_name', 'brand');

    assign_pager('brand',              $cate, $count, $size, $sort, $order, $page, '', $brand_id, 0, 0, $display); // 分页
    assign_dynamic('brand'); // 动态内容
}

$smarty->display('brand.dwt', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定品牌的详细信息
 *
 * @access  private
 * @param   integer $id
 * @return  void
 */
function get_brand_info($id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('brand') . " WHERE brand_id = '$id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获得指定的品牌下的商品总数
 *
 * @access  private
 * @param   integer     $brand_id
 * @param   integer     $cate
 * @return  integer
 */
function goods_count_by_brand($brand_id, $cate = 0)
{
    $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['hhs']->table('goods'). ' AS g '.
            "WHERE brand_id = '$brand_id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0";

    if ($cate > 0)
    {
        $sql .= " AND " . get_children($cate);
    }

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 获得品牌下的商品
 *
 * @access  private
 * @param   integer  $brand_id
 * @return  array
 */
function brand_get_goods($brand_id, $cate, $size, $page, $sort, $order)
{
    $cate_where = ($cate > 0) ? 'AND ' . get_children($cate) : '';

    /* 获得商品列表 */
    $sql = 'SELECT g.goods_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['hhs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.brand_id = '$brand_id' $cate_where".
            "ORDER BY $sort $order";

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        $arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
        $arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
    }

    return $arr;
}

?>
