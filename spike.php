<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

$smarty->caching = false;

//分页获取
//code by luo
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

if($act == 'next')
{
    include('includes/cls_json.php');

    $json   = new JSON;
    $res    = array('err_msg' => '', 'result' => '');

    $page             = intval($_REQUEST['page']);
    $rows             = get_goodslist($page);

    $res['goodslist'] = $rows['goodslist'];
    $res['nextPage']  = $rows['nextPage'];

    header('Content-Type: application/json');
    echo $json->encode($res);
    exit();
}
/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('spike.dwt', $cache_id))
{
   assign_template();
    $smarty->assign('page_title',$_CFG['shop_name']);    // 页面标题
    /* meta information */
	$smarty->assign('categories',  get_categories_tree()  ); // 分类树
   $res = get_goodslist();
    $smarty->assign('goods_list',    $res['goodslist']);   // 最新goods
    $smarty->assign('nextPage',    $res['nextPage']);   // 最新goods
    /* 页面中的动态内容*/
    assign_dynamic('spike');
} 

$smarty->assign('now_time',  gmtime());           // 当前系统时间

    
$sql="select headimgurl from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
$user_info=$db->getRow($sql);
//$appid=$weixin_config_rows['appid'];
//$secret= $weixin_config_rows['appsecret'];

$smarty->assign('appid', $appid);
$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);

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
$smarty->display('spike.dwt');



function get_goodslist($page = 1)
{
    
	$where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 and g.is_miao = 1 and g.is_mall = 1 ";
	//获得区域级别
	$current_region_type=get_region_type($_SESSION['cid']); 
	if($current_region_type<=2){
	     $where.=" and (g.city_id='".$_SESSION['cid'] . "' or g.city_id=1) ";
	}elseif($current_region_type==3){
	    $where.=" and (g.district_id='".$_SESSION['cid'] . "' or g.city_id=1) ";
	}

    $pageSize = 100;
    //统计页面总数
    $sql    = "select count(*) FROM ".$GLOBALS['hhs']->table('goods')." as g WHERE " . $where;
    $allNum = $GLOBALS['db']->getOne($sql);
    $pages  = ceil($allNum/$pageSize);
    //page 溢出
    $page   = $page<=$pages ? $page : $pages;
    $skip     = ($page - 1) * $pageSize;
	if($skip<0)
	{
		$skip=0;
	}
    $limit = " limit " . $skip . "," . $pageSize;
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price AS shop_price, ' .
                " g.promote_price, g.goods_type, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .
            ' ,g.team_num,g.team_price,g.promote_price '.
            'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' .
         //   'LEFT JOIN ' . $GLOBALS['hhs']->table('member_price') . ' AS mp ' .
             //   "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where ORDER BY g.sort_order, g.goods_id DESC" . $limit;
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
	$gtime = gmtime();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['goods_name']         = $row['goods_name'];
        $arr[$idx]['goods_brief']        = $row['goods_brief'];
        $arr[$idx]['goods_number']       = $row['goods_number'];
        
        $arr[$idx]['market_price']       = price_format($row['market_price'],false);
        $arr[$idx]['shop_price']         = price_format($row['shop_price'],false);
        
        $arr[$idx]['goods_thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$idx]['goods_img']          = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$idx]['url']                = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $arr[$idx]['team_num']           = $row['team_num'];
        $arr[$idx]['team_price']         = price_format($row['promote_price'],false);
		
		$arr[$idx]['promote_price']         = price_format($row['promote_price'],false);
        
        $arr[$idx]['team_discount']      = number_format($row['promote_price']/$row['market_price']*10,1);
        $arr[$idx]['little_img']         = $row['little_img'];

        $arr[$idx]['promote_start_date1']        = ($row['promote_start_date']);
        $arr[$idx]['promote_end_date1']        = ($row['promote_end_date']);


        $arr[$idx]['start_date'] = local_date("Y-m-d H:i:s",$row['promote_start_date']);
        $arr[$idx]['end_date']   = local_date("Y-m-d H:i:s",$row['promote_end_date']);

        $arr[$idx]['promote_start_date']        = strtotime($arr[$idx]['start_date']);
        $arr[$idx]['promote_end_date']        = strtotime($arr[$idx]['end_date']);
		
		if($row['promote_start_date']>$gtime)
		{
			$arr[$idx]['sort_order'] =2;
	
		}
		elseif($row['promote_start_date']<$gtime&&$gtime<$row['promote_end_date'])
		{
			$arr[$idx]['sort_order'] =1;
		}
		else
		{
			$arr[$idx]['sort_order'] =3;
		}

    }
	$arr =   array_sort($arr,'sort_order','asc');
    //下一页是否存在
    $res['nextPage']  = $page < $pages ? (++$page) : 0;
    $res['goodslist'] = $arr;
    //改写返回array
    return $res;

    // return $arr;
}


?>
