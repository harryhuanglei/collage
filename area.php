<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
/*
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}*/

/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

/*
if (!$smarty->is_cached('index.dwt', $cache_id))
{*/
    assign_template();
    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('goods_list',    get_goodslist());   // 最新文章

    $smarty->assign('playerdb',        get_flash_xml());
    /* 页面中的动态内容
    assign_dynamic('index');
} 
*/
$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
$user_info=$db->getRow($sql);
//$appid=$weixin_config_rows['appid'];
//$secret= $weixin_config_rows['appsecret'];

$smarty->assign('appid', $appid);
$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);
//$smarty->assign('jssdk', $class_weixin->getJsApiTicket() );

//$smarty->assign('signature', jssdk($appid,$secret, $timestamp));

$smarty->assign('imgUrl', $user_info['headimgurl'] );
$smarty->assign('title', $_CFG['index_share_title']);
$smarty->assign('desc', mb_substr($_CFG['index_share_dec'], 0,30,'utf-8')  );
/*
$smarty->assign('title', 'aaa'.$_CFG['index_share_title']);
$smarty->assign('desc', mb_substr('bbbb'.$_CFG['index_share_dec'], 0,30,'utf-8')  );
*/
$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//"/index.php";
$smarty->assign('link', $link);

$smarty->assign('link2', urlencode($link) );

$loading=$smarty->fetch('loading.html');
$smarty->assign('loading',    $loading);
echo get_citys_id();exit;
$smarty->display('index.dwt');


function get_goodslist()
{
	$where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0";
    $sql = 'SELECT g.goods_id, g.goods_name,g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .
            ' ,g.team_num,g.team_price '.
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['hhs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where ORDER BY g.sort_order, g.goods_id";
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['goods_name']          = $row['goods_name'];
        $arr[$idx]['goods_brief']       = $row['goods_brief'];
        
        $arr[$idx]['market_price']    = price_format($row['market_price'],false);
		$arr[$idx]['shop_price']    = price_format($row['shop_price'],false);
		
        $arr[$idx]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$idx]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$idx]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $arr[$idx]['team_num']    = $row['team_num'];
        $arr[$idx]['team_price']    = price_format($row['team_price'],false);
        
        $arr[$idx]['team_discount']    = number_format($row['team_price']/$row['market_price']*10,1);
        $arr[$idx]['little_img']        = $row['little_img'];
        
        $sql="select suppliers_name from ".$GLOBALS['hhs']->table('suppliers')." where suppliers_id=".$row['suppliers_id'];
        $arr[$idx]['suppliers_name']= $GLOBALS['db']->getOne($sql);
    }

    return $arr;
}



function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}


?>
