<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

$act = trim($_GET['act']);
if ($act == 'create') {
    $square   = trim($_POST['square']);
    if(empty($square)){
        $res = array(
            'isError' => 1,
            'message' => '评论内容不能为空！'
        );
        echo json_encode($res);
        exit();
    }
    $order_id = intval($_POST['order_id']);
    $luckdraw_id_sql = "select luckdraw_id from ".$hhs->table('order_info')." where order_id = ".$order_id;
    $luckdraw_id = $GLOBALS['db']->getOne($luckdraw_id_sql);
    if($luckdraw_id != 0){
        $res = array(
            'isError' => 1,
            'message' => '抽奖商品不能发布到参团广场！'
        );
    echo json_encode($res);
    exit();
    }
    $sql = "select goods_id from ".$GLOBALS['hhs']->table('order_info')." where order_id = ".$order_id;
    $goods_id = $GLOBALS['db']->getOne($sql);
    $todayDate = gmtime();
    $sql = "SELECT goods_id FROM " . $GLOBALS['hhs']->table('luckdraw') . " WHERE start_time < ".$todayDate." and end_time >".$todayDate." and goods_id = ".$goods_id;
    $luckdraw_goods = $GLOBALS['db']->getOne($sql);
        if($luckdraw_goods > 0){
            $res = array(
                'isError' => 1,
                'message' => '抽奖商品不能发布到参团广场！'
            );
            echo json_encode($res);
            exit();
        }
    $user_id  = $_SESSION['user_id'];
    $sql = "update ".$hhs->table('order_info')." set `square` = '".$square."' where user_id = '".$user_id."' and `order_id` = '".$order_id."'";
    $db->query($sql);    
    $res = array(
        'isError' => 0
    );
    echo json_encode($res);
    exit();
}

/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));    assign_template();
    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    $smarty->assign('categories',      get_categories_tree()); // 分类树 
    /* meta information */
	$smarty->assign('action',     $action);
	$loading=$smarty->fetch('loading.html');	
	$smarty->assign('loading',    $loading);
    $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
    $smarty->assign('keywords',    $keywords);    $smarty->assign('goods_list',    get_goodslist('best'));
	$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'].'?uid='.$uid;//"/index.php";    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$luckdraw_id = @$_REQUEST['luckdraw_id'];
	$smarty->assign('luckdraw_id',$luckdraw_id);	
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');
	$smarty->assign('title', $_CFG['square_title']);
	$smarty->assign('desc', mb_substr($_CFG['square_desc'], 0,30,'utf-8')  );
		$smarty->display('square.dwt');

function get_goodslist()
{
	global $hhs,$db;
    $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
    $orderby = isset($_GET['orderby']) ? trim($_GET['orderby']) : '';

    if($orderby == 'hot'){
        $orderby = 'need asc';
    }
    else{
        $orderby = 'order_id desc';
    }
    $where = " AND o.`square`<> '' ";
    if (!empty($keywords))
    {
        $where = " AND g.goods_name LIKE '%" . mysql_like_quote($keywords) . "%'";
        $sql = "select o.square,o.order_id,o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.uname,u.headimgurl from ".$hhs->table('order_info')." as o,".$hhs->table('order_goods')." as g,".$hhs->table('users')." as u where o.show_square = 1   and o.team_status = 1 and o.user_id = u.user_id AND g.`order_id` = o.`order_id` ".$where." order by " . $orderby;        // and o.team_status = 1 AND o.order_status = 1
    }
    else{
        $sql = "select o.square,o.order_id,o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.uname,u.headimgurl from ".$hhs->table('order_info')." as o,".$hhs->table('users')." as u where o.show_square = 1 and o.team_status = 1  and o.user_id = u.user_id  ".$where." order by " . $orderby; //and o.team_status = 1 AND o.order_status = 1
    }

    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $sql = "select g.is_on_sale,g.is_delete,g.goods_name,g.goods_id, g.goods_number, g.goods_thumb,g.little_img,g.goods_img, g.market_price, g.shop_price,g.team_price  from ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g where g.`goods_id` = o.`goods_id` and o.`order_id` = '".$row['order_id']."'";
        $goods = $db->getRow($sql);
		if($goods['is_on_sale'] == 1 && $goods['is_delete'] == 0)
		{
			$arr[$idx]['goods_id']   = $goods['goods_id'];
			$arr[$idx]['goods_name']   = $goods['goods_name'];
			$arr[$idx]['goods_number'] = $goods['goods_number'];
			$arr[$idx]['market_price'] = price_format($goods['market_price'],false);
			$arr[$idx]['shop_price']   = price_format($goods['shop_price'],false);
			
			$arr[$idx]['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
			$arr[$idx]['little_img']  = get_image_path($goods['goods_id'], $goods['little_img'], true);
			$arr[$idx]['goods_img']   = get_image_path($goods['goods_id'], $goods['goods_img']);
			$arr[$idx]['url']         = build_uri('goods', array('gid'=>$goods['goods_id']), $goods['goods_name']);
			$arr[$idx]['team_price']  = price_format($goods['team_price'],false);
			$arr[$idx]['team_num']    = $row['team_num'];
			$arr[$idx]['need']    = $row['team_num'] - $row['teammen_num'];
			$arr[$idx]['square']    = $row['square'];
			$arr[$idx]['team_id']    = $row['team_sign'];
			$arr[$idx]['uname']       = $row['uname'];
			$arr[$idx]['headimgurl']  = $row['headimgurl'];
			$arr[$idx]['add_time']    = local_date("Y-m-d H:i:s",$row['add_time']);
			
			$arr[$idx]['team_discount']    = @number_format($goods['team_price']/$goods['market_price']*10,1);
	
			$arr[$idx]['buy_nums']    = $db->getOne("select count(*) from ".$hhs->table('order_goods')." where goods_id = '".$goods['goods_id']."'");
	
	
			$arr[$idx]['gallery']   = $db->getAll("select thumb_url from ".$hhs->table('goods_gallery')." where goods_id = '".$goods['goods_id']."' limit 3");
	  	}
    }
     foreach ($arr as $key => $value) {
        $todayDate = gmtime();
        $luckdraw_goods_sql = "SELECT goods_id FROM " . $GLOBALS['hhs']->table('luckdraw') . " WHERE start_time < ".$todayDate." and end_time >".$todayDate." and goods_id = ".$value['goods_id'];
        $luckdraw_goods = $GLOBALS['db']->getOne($luckdraw_goods_sql);
        if($luckdraw_goods > 0)
        {
            unset($arr[$key]);
        }
    }

    return $arr;
}


?>
