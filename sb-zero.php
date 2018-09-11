<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

require(dirname(__FILE__) . '/includes/lib_fenxiao.php');

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$sql = "select `pay_status` from ".$hhs->table('order_info')." where `order_id` = " . $order_id;
$pay_status = $db->getOne($sql);
if (empty($pay_status) || $pay_status < 2) {
	hhs_header("Location:/user.php?act=order_detail&order_id=".$order_id."\n");
	exit();
}

$goods_id = isset($_GET['goods_id']) ? intval($_GET['goods_id']) : 0;

if($_GET['uid'] != $_SESSION['user_id'])
{
	hhs_header("Location:/goods.php?id=$goods_id&uid=".intval($_GET['uid'])."\n");
	exit();
}


//商品

$goods = get_goods_info($goods_id);
//print_r($goods);

//分销者信息

$info = getPidInfo($uid);
//print_r($info);


$timestamp=time();

$smarty->assign('timestamp', $timestamp );

$smarty->assign('appid', $appid);



$class_weixin=new class_weixin($appid,$appsecret);

$signature=$class_weixin->getSignature($timestamp);

$smarty->assign('signature', $signature);

//$smarty->assign('jssdk', jssdk($appid,$secret,$timestamp));

$smarty->assign('imgUrl','http://' . $_SERVER['HTTP_HOST'].'/'.$goods['goods_thumb'] );

$smarty->assign('title', $goods['goods_name']);

$smarty->assign('desc', mb_substr($_CFG['goods_share_dec'], 0,30,'utf-8')  );

/*

if(($pos=strrpos($_SERVER[REQUEST_URI], "from"))!==false){

	$uri=substr($_SERVER[REQUEST_URI],0,$pos-1);

}else{

    $uri=$_SERVER[REQUEST_URI];

}*/



$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER[REQUEST_URI];

$smarty->assign('link', $link );

$smarty->assign('link2', urlencode($link) );



$smarty->assign('goods',$goods);

$smarty->assign('info', $info);

$smarty->display("sb-zero.dwt");	