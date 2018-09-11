<?php
define('IN_HHS', true);
define('HHS_ADMIN', true);
require(dirname(__FILE__) . '/includes/init.php');
if ($_SESSION['user_id'] > 0)
{
	$smarty->assign('user_name', $_SESSION['user_name']);
}
else
{
	hhs_header("Location: ./user.php\n");
    exit;
}
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$user_id = $_SESSION['user_id'] ? $_SESSION['user_id'] : '';
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
/*我要分享和我要参团  */
if ($act == 'default')
{
	include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_image.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	$team_sign = isset($_REQUEST['team_sign']) ? intval($_REQUEST['team_sign']) : 0;
	$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
	$user_info=$db->getRow($sql);
	//$is_team是否可以参团 $is_teammen是否在团里
	$is_team=0;
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	if(empty($team_info['pay_time']))
    {
		hhs_header('location:index.php');
		exit();
	}
    if($team_info['pay_time'] && ($team_info['pay_time']+$_CFG['team_suc_time']*24*3600<gmtime())&&$team_info['team_status']==1 && $team_info['is_luck'] == 0 && $team_info['is_miao'] == 0)
    {
        //处理退款
        do_team_refund($team_sign);
        $team_info['team_status'] = 0;
    }
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	$smarty->assign('team_info', $team_info);
	if($team_info['team_status']!=1)
    {
	    $is_team=0;
	}
	/*
	$sql=" SELECT * FROM ".$hhs->table('goods')." where goods_id=".$team_info['extension_id'] ;
	$team_good=$db->getRow($sql);
	$smarty->assign('team_num', $team_good['team_num']);	//几人团
	*/
	$smarty->assign('team_num', $team_info['team_num']);	//几人团
	$sql=" SELECT count(*) FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_status>0" ;
	$count=$db->getOne($sql);
	//还差多少人
	$d_num=$team_info['team_num']-$count;
	$db_num=$team_info['team_num']-$team_info['teammen_num'];
	$d_num_arr=array();
	for($i=0;$i<$d_num;$i++)
    {
		$d_num_arr[]=$i;
	}
	$smarty->assign('db_num', $db_num);
	$smarty->assign('d_num', $d_num);
	$smarty->assign('d_num_arr', $d_num_arr);
	//用户是否参团
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where pay_status >1 and team_sign=".$team_sign ." and user_id=".$user_id;
	$row=$db->getRow($sql);
	if(empty($row))
    {
	    $is_team=1;
	}
    else
    {
	    $is_team=0;
	    $smarty->assign('is_teammen', 1);
	    if($row['pay_status']==0&&$row['team_status']==0&&($row['order_status']==0||$row['order_status']==1)&&$team_info['team_status']==1)
        {
	        echo"<script>";
	        echo"alert('您已经参团，等待您的支付');";
	        echo"window.location='user.php?act=order_detail&order_id=".$row['order_id']."';";
	        echo"</script>";exit();
	    }
	}
	$smarty->assign('is_team', $is_team);
	$smarty->assign('order', $row);
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    /*
    $sql = "SELECT *  FROM " . $GLOBALS['hhs']->table('order_info') .
            " WHERE team_sign = '$team_sign' and team_first=1 ";
    $order=$db->getRow($sql);
    if ($order === false)
    {
        exit;
    }*/
    /* 订单商品 */
    $goods_list = order_goods($team_sign);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
		$goods_id = $goods_list[$key]['goods_id'];
    }
	$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
    $smarty->assign('properties',          $properties['pro']);                              // 商品属性
    $smarty->assign('specification',       $properties['spe']);
	$goods_info = goods_info($goods_id);
    $smarty->assign('goods_info', $goods_info);
    if ($goods_info['suppliers_id'])
    {
        $stores_info = get_suppliers_info($goods_info['suppliers_id']);
		$sql = "SELECT count(*) FROM ".$hhs->table('goods')." as g WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $goods_info['suppliers_id'].$where;
        $stores_info['goods_num'] = $db->getOne($sql);
        $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." as g WHERE `suppliers_id` = " .$goods_info['suppliers_id'].$where;
        $stores_info['sales_num'] = $db->getOne($sql);
        $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$goods_info['suppliers_id'].$where;
        $stores_info['sales_num'] += $db->getOne($sql);
        $smarty->assign('stores_info',$stores_info);
    }
    //参团的人
    $sql="select u.user_name,u.uname,u.uname,u.headimgurl,o.pay_time,o.team_first,o.is_lucker from ".$hhs->table('order_info')." as o left join ".$hhs->table('users')." as u on o.user_id=u.user_id where team_sign=".$team_sign." and team_status>0 order by order_id ";
    $team_mem=$db->getAll($sql);
    foreach($team_mem as $k=>$v)
    {
        $team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
    }
    $team_start=$team_mem[0]['pay_time'];
    $smarty->assign('team_start', $team_start);
    $smarty->assign('systime', gmtime());
	$smarty->assign('team_suc_time',$_CFG['team_suc_time']);
    $smarty->assign('team_mem', $team_mem);
    //$smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('appid', $appid);
    //$smarty->assign('jssdk', jssdk($appid,$secret));
    $timestamp=time();
    $smarty->assign('timestamp', $timestamp );
    $class_weixin=new class_weixin($appid,$appsecret);
    $signature=$class_weixin->getSignature($timestamp);
    $smarty->assign('signature', $signature);
    //$smarty->assign('imgUrl', $user_info['headimgurl']);
    $luckdraw_id = $_REQUEST['luckdraw_id'];
    include_once(ROOT_PATH . 'includes/lib_image.php');
    $img = new image();
    /*要加水印的图片*/
    $file = ROOT_PATH.$goods_info['goods_img'];
    /*获取图片的类型*/
    $type = end(explode('.',$file));
    /*水印图片*/
    $sq = "select value from ".$hhs->table('shop_config')." where id=948";
    $share = $db->getOne($sq);
    $share = substr($share,3,strlen($share));
    $water = ROOT_PATH.$share;
    /*水印图片保存路径*/
    $end = "images/share/goods_share".$goods_info['goods_id'].".".$type;
    $img2 = ROOT_PATH.$end;
    $i_show = $img->param($file)->water($img2,$water,8,100);
    $shareGoodsPath = 'http://'.$_SERVER['HTTP_HOST'].'/'.$end;
    if(!$i_show)
    {
        $shareGoodsPath = 'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb'];
    }
	$smarty->assign('imgUrl',$shareGoodsPath);   //'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
    $smarty->assign('title', "【还差".$d_num."个人】我参加了“".$goods_list[0]['goods_name']."”拼单");
    $smarty->assign('desc', mb_substr($_CFG['group_share_dec'], 0,30,'utf-8')  );//
    if($luckdraw_id){
    	$link="http://" . $_SERVER['HTTP_HOST'] . "/share.php?team_sign=".$team_info['team_sign']."&uid=".$uid."&luckdraw_id=".$luckdraw_id;
    }else{
    	$link="http://" . $_SERVER['HTTP_HOST'] . "/share.php?team_sign=".$team_info['team_sign']."&uid=".$uid;
    }
    $smarty->assign('luckdraw_id',$luckdraw_id);
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
    $smarty->assign('group_share_ads', $_CFG['group_share_ads'] );
    $smarty->assign('rand_goods', getRandsGoods(6,$_SESSION['user_id']));
	$sql = "select g.shop_price as goods_price,g.team_price,g.is_team,g.goods_name,g.goods_id,g.goods_img,c.rec_id from ".$hhs->table("goods")." as g LEFT JOIN ".$hhs->table("collect_goods")." as c ON  c.user_id='".$_SESSION['user_id']."' and c.goods_id=g.goods_id where g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_miao = 0 AND g.is_luck = 0 order by rand() limit 6";
    $rands_goods = $db->getAll($sql);
    $smarty->assign('rands_goods', $rands_goods );
    $smarty->display('share.dwt');
}elseif($act == 'link'){
    $arr=array('error'=>0);
	$share_status=isset($_POST['share_status'])?$_POST['share_status']:1;
	$share_type=isset($_POST['share_type'])?$_POST['share_type']:1;
	$link_url=isset($_POST['link_url'])?$_POST['link_url']:'';
	$sql="insert into ".$hhs->table('share_info')." (user_id,share_status,share_type,link_url,add_time) value ('$_SESSION[user_id]','$share_status','$share_type',".
	" '$link_url',".gmtime()." ) ";
	$r=$db->query($sql);
	if($r){
		echo json_encode($arr);
		die();
	}
}
elseif($act == 'toalipay'){
    $order_id=isset($_REQUEST['order_id'])?$_REQUEST['order_id']:'';
    if(empty($order_id)){
        die('参数错误');
    }
    $sql=" SELECT * FROM ".$hhs->table('order_info')." where order_id=".$order_id;
    $order=$db->getRow($sql);
    if(!empty($order['team_sign']) && $order['team_status']!=0 && !empty($order['pay_time']) ){
    	if($order['luckdraw_id']){
    		hhs_header("location:share.php?team_sign=".$order['team_sign']);
    	}else{
    		hhs_header("location:share.php?team_sign=".$order['team_sign']."&luckdraw_id=".$luckdraw_id);
    	}
        exit();
    }else{
        hhs_header("location:user.php?act=order_detail&order_id=".$order['order_id']);
        exit();
    }
}
?>