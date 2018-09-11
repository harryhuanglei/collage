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
require_once(dirname(__FILE__) . '/includes/lib_fenxiao.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}

$cat_id = isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;

$page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page  = $page > 0 ? $page : 1;
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'sort_order';
 
$size = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
$order = '';
/* 缓存编号 */
$cache_id = 'mall' . '-' . $cat_id . '-' . $sort . '-' . $page;
$cache_id = sprintf('%X', crc32($cache_id));

if (!$smarty->is_cached('mall.dwt', $cache_id))
{
    $smarty->assign('cat_id',      $cat_id);    // 分类
    $smarty->assign('sort',      $sort);    // 排序
    $smarty->assign('page_title',      '精品商城');    // 页面标题

    $smarty->assign('categories',      get_categories_tree());
	
	$smarty->assign('one_cat_id',dds($cat_id));
	
	$cat_array = get_categories_tree($cat_id);
	if (!empty($cat_array[$cat_id]['cat_id']))
	{
		foreach ($cat_array[$cat_id]['cat_id'] as $key => $child_data)
		{
			$cat_array[$cat_id]['cat_id'][$key]['name'] = $child_data['name'];
		}
		$smarty->assign('cat_children', $cat_array[$cat_id]['cat_id']);
	}
	else if (!empty($cat_array))
	{
		$smarty->assign('cat_children', $cat_array);
	}
	$children = get_children($cat_id);
	
	
	$pid = $GLOBALS['db']->getOne("SELECT parent_id FROM ".$GLOBALS['hhs']->table('category')." WHERE cat_id=".$cat_id);
	$smarty->assign('pid',      $pid);
	$sub_cat = $cat_array[$cat_id]['cat_id'];
	if($sub_cat){
	    $sub_cat = 1;
	}
	else
	{
	    $sub_cat = 0;
	}
	$smarty->assign('sub_cat', $sub_cat);
	
	
    $count = get_mall_goods_count($children);
	
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
	
    $goodslist = get_mall_goods($children, $size, $page, $sort, $order);

    $smarty->assign('goods_list',       $goodslist);
	
    assign_pager('mall',$cat_id, $count, $size, $sort, $order, $page); // 分页

    $info = getPidInfo($_SESSION['user_id']);
	/*
    $timestamp=time();
    $smarty->assign('timestamp', $timestamp );
    $smarty->assign('appid', $appid);

	

    $class_weixin=new class_weixin($appid,$appsecret);
    $signature=$class_weixin->getSignature($timestamp);
    $smarty->assign('signature', $signature);
    $smarty->assign('imgUrl',$info['headimgurl'] );
    $smarty->assign('title', $info['user_name'].'推荐的店铺');
    $smarty->assign('desc', $_CFG['user_share_desc']  );
*/
    $link= $hhs->url().$_SERVER["REQUEST_URI"];
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
	
	
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');
	$smarty->assign('title', $_CFG['mall_title']);
	$smarty->assign('desc', mb_substr($_CFG['mall_desc'], 0,30,'utf-8')  );
	
	


    $smarty->display('mall.dwt');
}

/**
 * 获取数量
 * @param  [type] $cat_id [description]
 * @return [type]         [description]
 */
function get_mall_goods_count($cat_id){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_mall = 1 and is_luck = 0 and is_miao = 0 and is_fresh = 0 and is_zero = 0 ";
    $where  .= " and $cat_id";
    //获得区域级别
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }

    //$sql     = "select count(*) FROM ".$GLOBALS['hhs']->table('goods')." as g WHERE " . $where;
	$sql     = "select count(*) FROM ".$GLOBALS['hhs']->table('goods')." as g " . 
	'LEFT JOIN ' . $GLOBALS['hhs']->table('category') . ' AS c ON c.cat_id = g.cat_id WHERE ' .
	$where;
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
function get_mall_goods($cat_id, $size, $page, $sort, $order){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_mall = 1 and is_luck = 0 and is_miao = 0 and is_fresh = 0 and is_zero = 0 ";
    $where  .= " and $cat_id";
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
			'LEFT JOIN ' . $GLOBALS['hhs']->table('category') . ' AS c ON c.cat_id = g.cat_id ' .
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
        $arr[$idx]['little_img']    = $row['little_img'];
		
		$arr[$idx]['attr']    = get_goods_attr($row['goods_id']);
        unset($row);
    }

    return $arr;
}


?>
