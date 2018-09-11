<?php

/**
 * 小舍电商 商品分类
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: category.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}

$cat_id = isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
$act  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'hot';

$page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page  = $page > 0 ? $page : 1;
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'sort_order';
$size  = 800;
$order = '';
/* 缓存编号 */
$cache_id = 'rank' . '-' . $act . '-' . $sort . '-' . $page;
$cache_id = sprintf('%X', crc32($cache_id));

if (!$smarty->is_cached('rank.dwt', $cache_id))
{
    $smarty->assign('cat_id',      $cat_id);    // 分类
    $smarty->assign('act',      $act);    // 分类
    $smarty->assign('sort',      $sort);    // 排序
    $smarty->assign('page_title',      '热卖商品');    // 页面标题

    $smarty->assign('categories',      get_categories_tree());

    $count = get_mall_goods_count($cat_id, $act);
    
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
    $goodslist = get_mall_goods($cat_id, $size, $page, $sort, $order, $act);

    $smarty->assign('goods_list',       $goodslist);
    
    assign_pager('rank',$cat_id, $count, $size, $sort, $order, $page, $act); // 分页


    $smarty->display('rank.dwt');
}

/**
 * 获取数量
 * @param  [type] $cat_id [description]
 * @return [type]         [description]
 */
function get_mall_goods_count($cat_id,$type){
    $where   = "g.is_luck = 0 AND g.is_miao = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_hot = 1 ";
    $where  .= $cat_id ?' and g.`cat_id` = "'.$cat_id.'" ':' ';
    $where  .= $type == 'hot' ?' and g.`is_team` = "1" ':' and g.`is_mall` = "1" ';
    //获得区域级别
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }

    $sql     = "select count(*) FROM ".$GLOBALS['hhs']->table('goods')." as g WHERE " . $where;
    return $GLOBALS['db']->getOne($sql);    
}

/**
 * 获取商品
 * @param  [type] $cat_id [description]
 * @param  [type] $size   [description]
 * @param  [type] $page   [description]
 * @param  [type] $sort   [description]
 * @param  [type] $order  [description]
 * @return [type]         [description]
 */
function get_mall_goods($cat_id, $size, $page, $sort, $order, $type){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_hot = 1  AND g.is_miao = 0 AND g.is_luck = 0 ";
    $where  .= $cat_id ?' and g.`cat_id` = "'.$cat_id.'" ':' ';
    $where  .= $type == 'hot' ?' and g.`is_team` = "1" ':' and g.`is_mall` = "1" ';
    //获得区域级别
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }

    $skip     = ($page - 1) * $size;

    $limit = " limit " . $skip . "," . $size;

    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price , ' .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .
            ' ,g.team_num,g.team_price '.
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY g.`".$sort."`, g.goods_id DESC" . $limit;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['goods_id']      = $row['goods_id'];
        $arr[$idx]['goods_name']    = $row['goods_name'];
        $arr[$idx]['goods_brief']   = $row['goods_brief'];
        $arr[$idx]['goods_number']  = $row['goods_number'];
        
        $arr[$idx]['market_price']  = price_format($row['market_price'],false);
        $arr[$idx]['shop_price']    = price_format($row['shop_price'],false);
        
        $arr[$idx]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$idx]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$idx]['url']           = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $arr[$idx]['team_num']      = $row['team_num'];
        $arr[$idx]['team_price']    = price_format($row['team_price'],false);
        $arr[$idx]['team_discount'] = number_format($row['team_price']/$row['market_price']*10,1);
        $arr[$idx]['little_img']    = empty($row['little_img']) ? 'images/no_pic640x350.jpg' : $row['little_img'];
		$arr[$idx]['attr']    = get_goods_attr($row['goods_id']);
		$user_id = $_SESSION['user_id'];
		if(empty($user_id)){
            $arr[$idx]['collect']        = 0;
        }else{
            $sql = "select rec_id,user_id from" . $GLOBALS['hhs']->table('collect_goods')." where user_id=" .$user_id ." and goods_id=".$row['goods_id'];
            $collectInfo = $GLOBALS['db']->getRow($sql);
            $arr[$idx]['collect'] = empty($collectInfo)?0:1;
        }
        unset($row);
    }

    return $arr;
}
?>
