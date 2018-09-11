<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
$cat_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
assign_template();
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'ajax')
{
    include('includes/cls_json.php');
	$id = $_GET['cat_id'];
	$children = get_children($id);
    $last = $_POST['last'];
	$amount = $_POST['amount'];
	$limit = "limit $last,$amount";
    $json   = new JSON;
	$goodslist = get_catgoods($children,$limit);
	
    foreach($goodslist as $val){
    	$smarty->assign('item', $val);
		$res[]['catgoods_list']  = $GLOBALS['smarty']->fetch('library/cat_goodslist_ajax.lbi');
    }
    die($json->encode($res));
}


    $smarty->assign('cat_id',      $cat_id);    // 分类
    $smarty->assign('page_title',      '分类商品');    // 页面标题

    $smarty->assign('categories',      get_categories_tree());


    $link= $hhs->url().substr($_SERVER[SCRIPT_NAME], 1).'?uid='.$uid;
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
	
	
    $smarty->display('category.dwt');


/*获取商品*/
function get_catgoods($cat_id, $limit=''){
    $where   = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_mall = 1  and is_luck = 0 and is_miao = 0 and is_fresh = 0 and is_zero = 0 ";
    $where  .= " and $cat_id";
    //获得区域级别
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price , ' .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            "WHERE $where ORDER BY g.sort_order, g.goods_id DESC " . $limit;

	$res = $GLOBALS['db']->query($sql);
    $arr = array();
	while ($row = $GLOBALS['db']->fetchRow($res))
    {
		$arr[$row['goods_id']]['goods_id']       = $row['goods_id'];

        $arr[$row['goods_id']]['goods_name']    = $row['goods_name'];
        $arr[$row['goods_id']]['goods_number']  = $row['goods_number'];
        
        $arr[$row['goods_id']]['market_price']  = '¥'.price_format($row['market_price'],false);
        $arr[$row['goods_id']]['shop_price']    = '¥'.price_format($row['shop_price'],false);
        
        $arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);


    }
    return $arr;
}
?>
