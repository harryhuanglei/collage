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
require(dirname(__FILE__) . '/includes/init2.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
assign_template();
if(checkmobile()){
	hhs_header("Location: ./index.php\n");
    exit;
}
$cat_id = isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
$page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page  = $page > 0 ? $page : 1;
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'sort_order';
$size  = 12;
$order = '';
/* 缓存编号 */
$cache_id = 'default' . '-' . $cat_id . '-' . $sort . '-' . $page;
$cache_id = sprintf('%X', crc32($cache_id));
if (!$smarty->is_cached('default.dwt', $cache_id))
{
    $smarty->assign('cat_id',      $cat_id);    // 分类
    $smarty->assign('sort',      $sort);    // 排序
    $smarty->assign('page_title',      '精品商城');    // 页面标题
    $smarty->assign('categories',      get_categories_tree());
    $count = get_mall_goods_count($cat_id);
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
    $goodslist = get_mall_goods($cat_id, $size, $page, $sort, $order);
    $smarty->assign('miao_list',    get_promote_goods());  // 限时秒杀
    $smarty->assign('tuan_list',    get_typeof_goods('team',$_CFG['index_show_team_num'],1,$_SESSION['user_id']));  // 拼团专区
    $smarty->assign('tejia_list',    get_typeof_goods('mall',$_CFG['index_show_mall_num'],1));   // 精品商城
    $smarty->assign('goods_list',       $goodslist);
    assign_pager('pc',$cat_id, $count, $size, $sort, $order, $page); // 分页
    /*首页楼层*/
    $floor_list = getFloorList();
    $smarty->assign('floor_list',       $floor_list);
    $smarty->display('default.dwt');
}
/**
 * 获取数量
 * @param  [type] $cat_id [description]
 * @return [type]         [description]
 */
function get_mall_goods_count($cat_id){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ";
    $where  .= $cat_id ?' and g.`cat_id` = "'.$cat_id.'" ':' ';
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
function get_mall_goods($cat_id, $size, $page, $sort, $order){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ";
    $where  .= $cat_id ?' and g.`cat_id` = "'.$cat_id.'" ':' ';
    $skip     = ($page - 1) * $size;
    $limit = " limit " . $skip . "," . $size;
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.is_team, g.goods_name_style, g.market_price, g.shop_price , ' .
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
		    $arr[$idx]['is_team']    = $row['is_team'];
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
        unset($row);
    }
    return $arr;
}
/*获取楼层*/
function getFloorList()
{
    $sql = 'SELECT cat_id,cat_name ,parent_id,cat_img,is_show ' .' FROM ' . $GLOBALS['hhs']->table('category') ." WHERE parent_id = 0 AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res AS $row)
    {
        if ($row['is_show'])
        {
            $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
            $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
            $cat_arr[$row['cat_id']]['url']  = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
            $cat_arr[$row['cat_id']]['img'] = $row['cat_img'];
            $cat_img = !empty($row['cat_img']) ?  $row['cat_img'] : 'images/no_pic.jpg';
            $cat_arr[$row['cat_id']]['a_img'] = "http://" . $_SERVER['HTTP_HOST'].$cat_img;
            $cat_arr[$row['cat_id']]['goods_list'] = getCateryGoods($row['cat_id']);
            $cat_arr[$row['cat_id']]['floor_ad_list'] = floorListPositionByName('PC楼层广告',5,$row['cat_id']);
        }
    }
    return $cat_arr;
}
/*获取分类下的商品*/
function getCateryGoods($cate_id)
{
  $cate_id = intval($cate_id);
  $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 and is_best = 1 ";
  $where  .= $cate_id ? " AND ".get_children($cate_id)." " : ' ';
  $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.is_team, g.goods_name_style, g.market_price, g.shop_price , ' .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .
            ' ,g.team_num,g.team_price '.
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY g.sort_order DESC limit 6 ";
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['goods_id']      = $row['goods_id'];
        $arr[$idx]['goods_name']    = $row['goods_name'];
        $arr[$idx]['is_team']    = $row['is_team'];
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
        unset($row);
    }
    return $arr;
}
function checkmobile() {
 global $_G;
 $mobile = array();
//各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
 static $touchbrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
    'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
    'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
    'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
    'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
    'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
    'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
//window手机浏览器数组【猜的】
 static $mobilebrowser_list =array('windows phone');
//wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
 static $wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
   'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
   'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');
 $pad_list = array('pad', 'gt-p1000');
 $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
 if(dstrpos($useragent, $pad_list)) {
  return false;
 }
 if(($v = dstrpos($useragent, $mobilebrowser_list, true))){
  $_G['mobile'] = $v;
  return '1';
 }
 if(($v = dstrpos($useragent, $touchbrowser_list, true))){
  $_G['mobile'] = $v;
  return '2';
 }
 if(($v = dstrpos($useragent, $wmlbrowser_list))) {
  $_G['mobile'] = $v;
  return '3'; //wml版
 }
 $brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
 if(dstrpos($useragent, $brower)) return false;
 $_G['mobile'] = 'unknown';
//对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器
 if(isset($_G['mobiletpl'][$_GET['mobile']])) {
  return true;
 } else {
  return false;
 }
}
function dstrpos($string, $arr, $returnvalue = false) {
 if(empty($string)) return false;
 foreach((array)$arr as $v) {
  if(strpos($string, $v) !== false) {
   $return = $returnvalue ? $v : true;
   return $return;
  }
 }
 return false;
}
/*获取首页楼层广告*/
function floorListPositionByName($position_name,$num,$cate_id)
{
    $cate_id = intval($cate_id);
    $where = '';
    $where .= $cate_id ? " and ad.cate_id='$cate_id'" : ''; 
    $redirect_uri="http://" . $_SERVER['HTTP_HOST']."/";
    $arr = array( );
    $sql = "select ap.ad_width,ap.ad_height,ad.ad_id,ad.ad_name,ad.ad_code,ad.media_type,ad.ad_link,ad.ad_id from ".$GLOBALS['hhs']->table( "ad_position" )." as ap left join ".$GLOBALS['hhs']->table( "ad" )." as ad on ad.position_id = ap.position_id where ap.position_name='".$position_name.( "'".$where." and ad.enabled=1 order by order_sort limit ".$num );
    $res = $GLOBALS['db']->getAll( $sql );
    foreach ( $res as $idx => $row )
    {
            $arr[$row['ad_id']]['name'] = $row['ad_name'];
            $arr[$row['ad_id']]['code'] = $row['ad_code'];
            $arr[$row['ad_id']]['url'] = $row['ad_link'];
            $arr[$row['ad_id']]['image'] = $redirect_uri."/data/afficheimg/".$row['ad_code'];
            $arr[$row['ad_id']]['content'] = "<a href='".$arr[$row['ad_id']]['url']."' target='_blank'><img src='data/afficheimg/".$row['ad_code']."' width='".$row['ad_width']."' height='".$row['ad_height']."' /></a>";
            $arr[$row['ad_id']]['width'] = $row['ad_width'];
            $arr[$row['ad_id']]['height'] = $row['ad_height'];
    }
    return $arr;
}
?>