<?php

define('IN_HHS', true);

define('HHS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_SESSION['user_id'] > 0)
{

	$smarty->assign('user_name', $_SESSION['user_name']);

}

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');


$user_id = $_SESSION['user_id'] ? $_SESSION['user_id'] : '';

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';


/*我要分享和我要参团  */
if ($act == 'default'){
	include_once(ROOT_PATH . 'includes/lib_clips.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	$team_sign = isset($_REQUEST['team_sign']) ? intval($_REQUEST['team_sign']) : 0;
	$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
	$user_info=$db->getRow($sql);
	//$is_team是否可以参团 $is_teammen是否在团里
	
	$is_team=0;
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	if(empty($team_info['pay_time'])){
		hhs_header('location:index.php');
		exit();
	}
    
    if(($team_info['pay_time']+$_CFG['team_suc_time']*24*3600<gmtime())&&$team_info['team_status']==1){
        //处理退款
        do_team_refund($team_sign);
        $team_info['team_status'] = 0;
        // require_once(ROOT_PATH . 'includes/lib_order.php');
        // require_once(ROOT_PATH . 'includes/lib_payment.php');
        // require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
        
        // $sql="select o.`order_sn`,o.`order_id`,o.`money_paid`,o.`order_amount`,o.`team_sign`,u.`openid` from ".$GLOBALS['hhs']->table('order_info')." as o LEFT JOIN ".$GLOBALS['hhs']->table('users')." as u on u.`user_id` = o.`user_id` where team_sign=".$team_info['team_sign'];
        // $team_list= $GLOBALS['db']->getAll($sql);
        // foreach($team_list as $f){
        //     $order_sn=$f['order_sn'];
        //     $r= refund($order_sn,$f['money_paid']*100);
            
        //     if($r){
        //         $arr=array();
        //         $arr['order_status']    = OS_RETURNED;
        //         $arr['pay_status']  = PS_REFUNDED;
        //         $arr['shipping_status'] = 0;
        //         $arr['team_status']  = 3;
        //         $arr['money_paid']  = 0;
        //         $arr['order_amount']= $f['money_paid'] + $f['order_amount'];
        //         update_order($f['order_id'], $arr);

        //         $openid = $f['openid'];
        //         $title  = '退款提醒';
        //         $url    = 'user.php?act=order_detail&order_id='.$f['order_id'];
        //         $desc   = "您的订单已经成功退款，记得常来看看哦";

        //         $weixin = new class_weixin($appid,$appsecret);
        //         $weixin->send_wxmsg($openid, $title , $url , $desc );
        //     }                

        // }
        //处理结束

        // $sql = "UPDATE ". $hhs->table('order_info') ." SET team_status=3,order_status=2 WHERE team_status=1 and team_sign=".$team_sign;
        // $db->query($sql);   
        // $sql = "UPDATE ". $hhs->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$team_sign;
        // $db->query($sql);
    }
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	$smarty->assign('team_info', $team_info);
	
	if($team_info['team_status']!=1){
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
	
	$d_num_arr=array();
	for($i=0;$i<$d_num;$i++){
		$d_num_arr[]=$i;
	}
	$smarty->assign('d_num', $d_num);
	$smarty->assign('d_num_arr', $d_num_arr); 
	//用户是否参团
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign ." and user_id=".$user_id;
	$row=$db->getRow($sql);
	if(empty($row)){
	    $is_team=1;
	}else{
	    $is_team=0;
	    $smarty->assign('is_teammen', 1);
	    if($row['pay_status']==0&&$row['team_status']==0&&($row['order_status']==0||$row['order_status']==1)&&$team_info['team_status']==1){
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
    }

    //参团的人
    $sql="select u.user_name,u.uname,u.uname,u.headimgurl,o.pay_time,o.team_first from ".$hhs->table('order_info')." as o left join ".$hhs->table('users')." as u on o.user_id=u.user_id where team_sign=".$team_sign." and team_status>0 order by order_id ";
    $team_mem=$db->getAll($sql);
    foreach($team_mem as $k=>$v){
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
    
   // $smarty->assign('imgUrl', $user_info['headimgurl']);
   $smarty->assign('imgUrl','http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']);
   //'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
    $smarty->assign('title', "我参加了”".$goods_list[0]['goods_name']."“拼单，还差".$d_num."个人！"); 
    $smarty->assign('desc', mb_substr($_CFG['group_share_dec'], 0,30,'utf-8')  );//
    $link="http://" . $_SERVER['HTTP_HOST'] . "/share.php?team_sign=".$team_info['team_sign']."&uid=".$uid;
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
    $smarty->assign('group_share_ads', $_CFG['group_share_ads'] );
    
    $smarty->display('share_zero.dwt');

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
        hhs_header("location:share.php?team_sign=".$order['team_sign']);
        exit();
    }else{
        hhs_header("location:user.php?act=order_detail&order_id=".$order['order_id']);
        exit();
    }

}

?>