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


if ($_REQUEST['act'] == 'getquan') {

	include_once('includes/cls_json.php');

	$json = new JSON();

	$result = array('error' => 1,'message'=>'您已经领取过了', 'content' => '');

	

	$bid  = isset($_REQUEST['bid']) ? intval($_REQUEST['bid']) : 0;

	if(! checkQuan($bid)){

		$res = getQuan($bid);

		$result['error']   = $res ? 0 : 1;

		$result['message'] = $res ? '' : '领取失败';

		$result['content'] = $res ? '领取成功' : '';

	}

	ob_end_clean();

	die($json->encode($result));

}


$suppliers_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$act  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'hot';

$page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page  = $page > 0 ? $page : 1;
$sort  = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'sort_order';
$size  = 8;
$order = '';
/* 缓存编号 */
$cache_id = 'store' . '-' . $suppliers_id  . '-' . $act . '-' . $sort . '-' . $page;
$cache_id = sprintf('%X', crc32($cache_id));

// if (!$smarty->is_cached('store.dwt', $cache_id))
// {
    $smarty->assign('id',      $suppliers_id);    // 分类
    $smarty->assign('act',      $act);    // 分类
    $smarty->assign('sort',      $sort);    // 排序

    $smarty->assign('root', $_SERVER['HTTP_HOST']);

    $suppliers = getSuppliers($suppliers_id);
    $smarty->assign('suppliers_name', $suppliers['suppliers_name']);
    $smarty->assign('page_title',      $suppliers['suppliers_name'].'的店铺');    // 页面标题

    $info = getPidInfo($suppliers['user_id']);
    if (empty($info)) {
     //  hhs_header("Location: /\n");
    }
    $smarty->assign('info', $info);

    $smarty->assign('categories',      get_categories_tree());

    $count = get_mall_goods_count(0, $act, $suppliers_id);
    
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
    $goodslist = get_mall_goods(0, $size, $page, $sort, $order, $act, $suppliers_id);


    assign_pager('store',$suppliers_id, $count, $size, $sort, $order, $page ,'', 0 , $act ); // 分页

						
						
    $smarty->assign('goods_list',       $goodslist);

    $timestamp=time();
    $smarty->assign('timestamp', $timestamp );
    $smarty->assign('appid', $appid);

    $class_weixin=new class_weixin($appid,$appsecret);
    $signature=$class_weixin->getSignature($timestamp);
    $smarty->assign('signature', $signature);
    $smarty->assign('imgUrl',$hhs->url() . $suppliers['supp_logo'] );
    $smarty->assign('title', $suppliers['suppliers_name'].'的店铺');
    $smarty->assign('desc', $_CFG['store_share_desc']  );

    $link= $hhs->url().substr($_SERVER['PHP_SELF'], 1).'?id='.$suppliers_id.'uid='.$uid;

    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );

	$sql = "select * from ".$hhs->table('supp_photo')." where is_check = 1 AND supp_id = ".$suppliers_id;
	$supp_photo = $db->getAll($sql);
    $smarty->assign('supp_photo',$supp_photo);

    /**
     * 销量
     * @var string
     */
    //获得区域级别
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }    
    $stores_info = get_suppliers_info($suppliers_id);
    $sql = "SELECT count(*) FROM ".$hhs->table('goods')." as g WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $suppliers_id . $where;
    $stores_info['goods_num'] = $db->getOne($sql);
    $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." as g WHERE `suppliers_id` = " .$suppliers_id . $where;
    $stores_info['sales_num'] = $db->getOne($sql);
    $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$suppliers_id . $where;
    $stores_info['sales_num'] += $db->getOne($sql);

    $smarty->assign('stores_info',$stores_info);
	
	$smarty->assign('quan_list',      quanList($suppliers_id));

    $smarty->display('store.dwt');
// }

/**
 * 获取数量
 * @param  [type] $cat_id [description]
 * @return [type]         [description]
 */
function get_mall_goods_count($cat_id,$type,$suppliers_id){
    $where   = "g.is_luck = 0 AND g.is_miao = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.`suppliers_id` = '".$suppliers_id."' " ;
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
function get_mall_goods($cat_id, $size, $page, $sort, $order, $type ,$suppliers_id){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_luck = 0 AND g.is_miao = 0 AND g.is_delete = 0 AND g.`suppliers_id` = '".$suppliers_id."' " ;
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
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price, g.ts_a, g.ts_b, g.ts_c , ' .
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
		
		$arr[$idx]['ts_a']  = $row['ts_a'];
		$arr[$idx]['ts_b']  = $row['ts_b'];
		$arr[$idx]['ts_c']  = $row['ts_c'];
        
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

function getSuppliers($suppliers_id){
    $sql = 'SELECT `suppliers_name` ,`user_id`,`supp_logo` '.
            'FROM ' . $GLOBALS['hhs']->table('suppliers') . 
            "WHERE `suppliers_id` = " . $suppliers_id;
    return $GLOBALS['db']->getRow($sql);
}

function quanList($suppliers_id){

	global $db,$hhs;



	$sql = "select b.`type_id`,b.`type_name`,b.`type_money`,b.`min_goods_amount`,b.`use_start_date`,b.`use_end_date`

	 		from ".$hhs->table('bonus_type')." as b 

	 		where b.suppliers_id = $suppliers_id and b.`is_online` = 1 and b.`send_end_date` > " . time() ;

	$bonus_lists = $db->getAll($sql);
	
	foreach ($bonus_lists as $key => $row) {
		
		$bonus_id = $db->getOne("select `bonus_id` from ".$hhs->table('user_bonus')." where `bonus_type_id` = '" .$row['type_id']. "' and `user_id` = 0 limit 1");
		
		if($bonus_id)
		{
			$rows[$key]['type_id'] = $row['type_id'];
			
			$rows[$key]['type_name'] = $row['type_name'];
			
			$rows[$key]['type_money'] = round($row['type_money']);
			
			$rows[$key]['min_goods_amount'] = round($row['min_goods_amount']);
			
			$rows[$key]['use_start_date'] = date("Y-m-d",$row['use_start_date']);

			$rows[$key]['use_end_date']   = date("Y-m-d",$row['use_end_date']);

			$rows[$key]['stamp']   = rand(1,4);

		}
	}

	return $rows;

}



function checkQuan($bid){

	if(!$bid)

		return true;



	global $db,$hhs;

	$sql = "select `bonus_id` from ".$hhs->table('user_bonus')."  where `bonus_type_id` = '" .$bid. "' and `user_id` = '".$_SESSION['user_id']."'";

	return $db->getOne($sql);

}



function getQuan($bid){

	if(!$bid)

		return false;

	global $db,$hhs;



	$bonus_id = $db->getOne("select `bonus_id` from ".$hhs->table('user_bonus')." where `bonus_type_id` = '" .$bid. "' and `user_id` = 0 ORDER BY RAND() limit 1");

	if (!$bonus_id) {

		return false;

	}

	$sql = "update ".$hhs->table('user_bonus')." set `user_id` = '".$_SESSION['user_id']."' where `user_id` = 0 and `bonus_type_id` = '" .$bid. "' and `bonus_id` = '".$bonus_id."'";



	return $db->query($sql);

}
?>
