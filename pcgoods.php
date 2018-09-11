<?php

/**
 * 小舍电商 商品详情
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: goods.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init2.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
assign_template();

$goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;


/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

$cache_id = $goods_id . '-' . $_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));
$smarty->assign('bonus_free_all',           isset($_REQUEST['bonus_free_all']) ? 1:0);
if (!$smarty->is_cached('pcgoods.dwt', $cache_id))
{
    $smarty->assign('image_width',  $_CFG['image_width']);
    $smarty->assign('image_height', $_CFG['image_height']);

    $smarty->assign('id',           $goods_id);
    $smarty->assign('type',         0);
    $smarty->assign('cfg',          $_CFG);
	
	

    /* 获得商品的信息 */
    $goods = get_goods_info($goods_id);
    if ($goods === false)
    {
        /* 如果没有找到任何记录则跳回到首页 */
        hhs_header("Location: ./\n");
        exit;
    }
    else
    {
        

        $smarty->assign('d_team_num', $goods['team_num']-1);
        $smarty->assign('goods_id',           $goods['goods_id']);
        $smarty->assign('promote_end_time',   $goods['gmt_end_time']);

        /* meta */
        if ($goods['promote_price_org'] > 0) {
            $goods['team_price'] = $goods['promote_price'];
        }

        /* current position */
        $smarty->assign('page_title',   $goods['goods_name']);                    // 页面标题

        $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性

        $smarty->assign('properties',          $properties['pro']);                              // 商品属性
        $smarty->assign('specification',       $properties['spe']);                              // 商品规格

        $smarty->assign('pictures',            get_goods_gallery($goods_id));                    // 商品相册
		
		$smarty->assign('categories',       get_categories_tree());
		$smarty->assign('one_cat_id',     ddd($goods['cat_id']));
     /**
     * 销量
     * @var string
     */
    $store_id = $goods['suppliers_id'];
    $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." WHERE `suppliers_id` = " .$store_id;
    $sales_num = $db->getOne($sql);
    $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$store_id;
    $sales_num += $db->getOne($sql);
    $smarty->assign('sales_num',$sales_num);


    }
}

if ($goods['suppliers_id']){
    $stores_info = get_suppliers_info($goods['suppliers_id']);
    $smarty->assign('stores_info',$stores_info);
    $smarty->assign('qq',$stores_info['qq']);
}
else{
    $smarty->assign('qq',$_CFG['qq']);
}


	
	
$sales_num = $goods['sales_num'];


if ($goods['suppliers_id']){  
    $stores_info = get_suppliers_info($goods['suppliers_id']);
    $sql = "SELECT count(*) FROM ".$hhs->table('goods')." as g WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $goods['suppliers_id'].$where;
    $stores_info['goods_num'] = $db->getOne($sql);
    $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." as g WHERE `suppliers_id` = " .$goods['suppliers_id'].$where;
    $stores_info['sales_num'] = $db->getOne($sql);
    $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$goods['suppliers_id'].$where;
    $stores_info['sales_num'] += $db->getOne($sql);

    $smarty->assign('stores_info',$stores_info);
}

$www="http://" . $_SERVER['HTTP_HOST'] . "/";
$smarty->assign('www',  $www);   

$smarty->assign('buy_num',get_buy_sum($goods_id)+$sales_num);

$smarty->assign('now_time',  gmtime());           // 当前系统时间


/* 更新点击次数 */
$db->query('UPDATE ' . $hhs->table('goods') . " SET click_count = click_count + 1 WHERE goods_id = '$_REQUEST[id]'");


    $smarty->assign('goods',              $goods);
    

    $smarty->display('pcgoods.dwt',      $cache_id);


/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */



/**
 * 获得商品选定的属性的附加总价格
 *
 * @param   integer     $goods_id
 * @param   array       $attr
 *
 * @return  void
 */
function get_attr_amount($goods_id, $attr)
{
    $sql = "SELECT SUM(attr_price) FROM " . $GLOBALS['hhs']->table('goods_attr') .
        " WHERE goods_id='$goods_id' AND " . db_create_in($attr, 'goods_attr_id');

    return $GLOBALS['db']->getOne($sql);
}
function get_buy_sum($goods_id)
{
    $sql = 'SELECT IFNULL(SUM(g.goods_number), 0) ' .
        'FROM ' . $GLOBALS['hhs']->table('order_info') . ' AS o, ' .
            $GLOBALS['hhs']->table('order_goods') . ' AS g ' .
        "WHERE o.order_id = g.order_id " .
        "AND o.order_status  in (0,1,5)  ".
        " AND o.shipping_status in (0,1,2) "  .
        " AND o.pay_status in (1,2) ".
        " AND g.goods_id = ".$goods_id;
    
    return $GLOBALS['db']->getOne($sql);
}

function get_regions_name($region_id)
{
    return $GLOBALS['db']->getOne("select region_name from ".$GLOBALS['hhs']->table('region')." where region_id='$region_id'");
}

function ddd($cat_id)
{
  $cat=$GLOBALS['db']->getRow("SELECT cat_id,parent_id FROM ".$GLOBALS['hhs']->table('category')." WHERE cat_id=".$cat_id);
  if($cat['parent_id']==0)
  {
 	 return $cat['cat_id'];
  }
  else
  {
 	 return ddd($cat['parent_id'],$cat['cat_id']);
  }
}
?>
