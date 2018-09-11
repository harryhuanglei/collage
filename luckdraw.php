<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

$smarty->caching = false;

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';

if($act == 'list'){

    assign_template();

    $smarty->assign('page_title',$_CFG['shop_name']);    // 页面标题

    /* meta information */

	$smarty->assign('categories',  get_categories_tree()  ); // 分类树

    $smarty->assign('goods_list',    get_goodslist());   // 最新goods

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

    $smarty->display('luckdraw_list.dwt');

}

elseif ($act == 'view')

{

    assign_template();

	$act_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if ($act_id <= 0)

    {

        hhs_header("Location: ./\n");

        exit;

    }



    /* 取得团购活动信息 */

    $luckdraw_info = luckdraw_info($act_id);



    if (empty($luckdraw_info))

    {

        hhs_header("Location: ./\n");

        exit;

    }



	$goods_id = $luckdraw_info['goods_id'];

	$goods = goods_info($goods_id);

	$goods['luckdraw_price'] = $luckdraw_info['luckdraw_price'];

	$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性

    $smarty->assign('properties',          $properties['pro']);                              // 商品属性

    $smarty->assign('specification',       $properties['spe']);                              // 商品规格

    $smarty->assign('pictures',            get_goods_gallery($goods_id));                    // 商品相册




	$smarty->assign('goods_id', $goods_id);

	$smarty->assign('goods', $goods);

	$smarty->assign('luckdraw_info', $luckdraw_info);

	//print_r($luckdraw_info);



    if ($goods['suppliers_id']){

        $stores_info = get_suppliers_info($goods['suppliers_id']);



	    $current_region_type=get_region_type($_SESSION['site_id']);

        if($current_region_type<=2){

             $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";

        }elseif($current_region_type==3){

            $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";

        }

        $stores_info = get_suppliers_info($goods['suppliers_id']);

        $sql = "SELECT count(*) FROM ".$hhs->table('goods')." as g WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $goods['suppliers_id'].$where;

        $stores_info['goods_num'] = $db->getOne($sql);

        $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." as g WHERE `suppliers_id` = " .$goods['suppliers_id'].$where;

        $stores_info['sales_num'] = $db->getOne($sql);

        $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$goods['suppliers_id'].$where;

        $stores_info['sales_num'] += $db->getOne($sql);







        $smarty->assign('stores_info',$stores_info);

        $smarty->assign('qq',$stores_info['qq']);

    }

    else{

        $smarty->assign('qq',$_CFG['qq']);

    }





	// comments

    $comments = assign_comment($goods_id,0,1);

    $smarty->assign('comments', $comments['comments'] );

    $smarty->assign('comments_nums', $comments['pager']['record_count'] );

    $smarty->assign('rand_goods', getRandsGoods(6,$_SESSION['user_id']));

    $sql = "select g.shop_price as goods_price,g.team_price,g.goods_name,g.goods_id,g.goods_img,c.rec_id from ".$hhs->table("goods")." as g LEFT JOIN ".$hhs->table("collect_goods")." as c ON  c.user_id='".$_SESSION['user_id']."' and c.goods_id=g.goods_id where g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 order by rand() limit 6";



    $rands_goods = $db->getAll($sql);

    $smarty->assign('rands_goods', $rands_goods );

    $smarty->assign('appid', $appid);

    $timestamp=time();

    $smarty->assign('timestamp', $timestamp );

    $class_weixin=new class_weixin($appid,$appsecret);

    $signature=$class_weixin->getSignature($timestamp);

    $smarty->assign('signature', $signature);

    $show = 'http://'.$_SERVER['HTTP_HOST'].'/'.$goods['goods_thumb'];
    include_once(ROOT_PATH . 'includes/lib_image.php');
    $img = new image();
    /*要加水印的图片*/
    $file = ROOT_PATH.$goods['goods_img'];
    /*获取图片的类型*/
    $type = end(explode('.',$file));
    /*水印图片*/
    $sq = "select value from ".$hhs->table('shop_config')." where id=948";
    $share = $db->getOne($sq);
    $share = substr($share,3,strlen($share));
    $water = ROOT_PATH.$share;
    /*水印图片保存路径*/
    $end = "images/share/goods_share".$goods_id.".".$type;
    $img2 = ROOT_PATH.$end;
    $img->param($file)->water($img2,$water,8,100);
    $show = 'http://'.$_SERVER['HTTP_HOST'].'/'.$end;
    $smarty->assign('imgUrl',  $show);

    $smarty->assign('title', $_CFG['luck_title']);

    $smarty->assign('desc', mb_substr($_CFG['luck_desc'], 0,30,'utf-8'));

    $link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];//"/index.php";

    $smarty->assign('link', $link);

    $smarty->assign('link2', urlencode($link) );
    $smarty->assign('page_title',      $goods['goods_name']);    // 页面标题


	$smarty->assign('now_time',  gmtime());

	$smarty->assign('luckdraw_id',  $act_id);

	$smarty->display('luckdraw_goods.dwt');

}

elseif ($act == 'drawn')

{

	$smarty->assign('page_title','中奖纪录');    // 页面标题

	$act_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if ($act_id <= 0)

    {

        hhs_header("Location: ./\n");

        exit;

    }



    /* 取得团购活动信息 */

    $luckdraw_info = luckdraw_info($act_id);



    if (empty($luckdraw_info))

    {

        hhs_header("Location: ./\n");

        exit;

    }

	$goods_id = $luckdraw_info['goods_id'];

	$goods = goods_info($goods_id);

    if($goods['end_date'] < gmtime())

    {

        $sql = "select u.uname,u.headimgurl,o.order_sn,o.mobile,o.is_lucker from ".$hhs->table('order_goods')." as g,".$hhs->table('order_info')." as o ,".$hhs->table('users')." as u where o.order_id = g.order_id and o.user_id=u.user_id and g.goods_id = " . $goods_id .' and o.luckdraw_id = '.$act_id.' and o.is_luckdraw = 1 group by o.order_id';



        $orders = $db->getAll($sql);

        foreach ($orders as $key => $order) {

            $orders[$key]['mobile'] = hidtel($order['mobile']);

            unset($order);

        }

    }

	$smarty->assign('goods_id', $goods_id);

	$smarty->assign('goods', $goods);

	$smarty->assign('luckdraw_info', $luckdraw_info);

	$smarty->assign('team_mem',      $orders);

	$smarty->display('luckdraw_drawn.dwt');

}

function get_goodslist()

{



	$where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ";

	//获得区域级别

	/*$current_region_type=get_region_type($_SESSION['cid']);

	if($current_region_type<=2){

	     $where.=" and (g.city_id='".$_SESSION['cid'] . "' or g.city_id=1) ";

	}elseif($current_region_type==3){

	    $where.=" and (g.district_id='".$_SESSION['cid'] . "' or g.city_id=1) ";

	}*/

    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_number, g.suppliers_id, g.goods_name_style, g.market_price, g.shop_price AS shop_price, ' .

                " g.promote_price, g.goods_type, " .

                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img,g.little_img ' .

            ' ,g.team_num,g.team_price,g.promote_price,g.promote_price,l.id,l.start_time,l.end_time,l.stock_num,l.luck_status,l.title,l.luckdraw_price FROM '

			. $GLOBALS['hhs']->table('luckdraw'). " as l left join "

            . $GLOBALS['hhs']->table('goods')." as g on l.goods_id=g.goods_id ".

            "WHERE $where ORDER BY g.sort_order, g.goods_id DESC limit 50";

			//echo $sql;exit;

    $res = $GLOBALS['db']->getAll($sql);



    $arr = array();

	$gtime = gmtime();

    foreach ($res AS $idx => $row)

    {

		//pangbin增加判断是否开奖

        if($row['end_date'] < $gtime)

        {

            $sql = "select u.uname,u.headimgurl,o.order_sn,o.mobile,o.is_lucker from ".$GLOBALS['hhs']->table('order_goods')." as g,

	".$GLOBALS['hhs']->table('order_info')." as o ,".$GLOBALS['hhs']->table('users')." as u ".

	"where o.order_id = g.order_id and o.user_id=u.user_id and g.goods_id = " .$row['goods_id'] .' and o.luckdraw_id = '.$row['id'].' and o.is_luckdraw = 1 group by o.order_id';

            $orders = $GLOBALS['db']->getAll($sql);

        }







        $arr[$idx]['id']         = $row['id'];

		$arr[$idx]['goods_name']         = $row['goods_name'];

		$arr[$idx]['title']         = $row['title'];

        $arr[$idx]['goods_brief']        = $row['goods_brief'];

        $arr[$idx]['goods_number']       = $row['goods_number'];



        $arr[$idx]['market_price']       = price_format($row['market_price'],false);

        $arr[$idx]['shop_price']         = price_format($row['shop_price'],false);



        $arr[$idx]['goods_thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);

        $arr[$idx]['goods_img']          = get_image_path($row['goods_id'], $row['goods_img']);

  //      $arr[$idx]['url']                = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);



		$arr[$idx]['url']              = "luckdraw.php?act=view&id=".$row['id'];



        $arr[$idx]['team_num']           = $row['team_num'];

        $arr[$idx]['team_price']         = price_format($row['team_price'],false);

        $arr[$idx]['luckdraw_price']         = price_format($row['luckdraw_price'],false);

        $arr[$idx]['little_img']         = $row['little_img'];



        $arr[$idx]['promote_start_date1']        = ($row['start_time']);

        $arr[$idx]['promote_end_date1']        = ($row['end_time']);

		$arr[$idx]['team_mem']        = count($orders);







        $arr[$idx]['start_date'] = local_date("Y-m-d H:i:s",$row['start_time']);

        $arr[$idx]['end_date']   = local_date("Y-m-d H:i:s",$row['end_time']);



        $arr[$idx]['promote_start_date']        = strtotime($arr[$idx]['start_date']);

        $arr[$idx]['promote_end_date']        = strtotime($arr[$idx]['end_date']);

		if($row['start_time']>$gtime)

		{

			$arr[$idx]['sort_order'] =2;



		}

		elseif($row['start_time']<$gtime&&$gtime<$row['end_time'])

		{

			$arr[$idx]['sort_order'] =1;

		}

		else

		{

			$arr[$idx]['sort_order'] =3;

		}



    }

	$arr =   array_sort($arr,'sort_order','asc');

    return $arr;

}



function luckdraw_info($act_id)

{



    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('luckdraw') .

            "WHERE id = '$act_id' " ;

    $info = $GLOBALS['db']->getRow($sql);



    /* 如果为空，返回空数组 */

    if (empty($info))

    {

        return array();

    }

    /* 格式化时间 */

    $info['start_date'] = local_date('Y-m-d H:i', $info['start_time']);

    $info['end_date'] = local_date('Y-m-d H:i', $info['end_time']);





    /* 状态 */

    $info['status'] = $info['luck_status'];



    return $info;

}

function hidtel($phone){

    $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话

    if($IsWhat == 1){

        return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);

    }else{

        return  preg_replace('/(1[345678]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);

    }

}



?>

