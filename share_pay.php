<?php

define('IN_HHS', true);

define('HHS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
include_once(ROOT_PATH . 'includes/lib_clips.php');
include_once(ROOT_PATH . 'includes/lib_transaction.php');

include_once(ROOT_PATH . 'includes/lib_payment.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

$user_id = $_SESSION['user_id'] ? $_SESSION['user_id'] : '';
$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
$user_info=$db->getRow($sql);
$smarty->assign('user_info', $user_info );

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$order_id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;
$smarty->assign('order_id', $order_id );

$smarty->assign('appid', $appid);
$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);
/*我要分享和我要参团  */
if ($act=='default'){
    $order_info=order_info($order_id);
    $smarty->assign('progress', number_format($order_info['money_paid']*100/($order_info['money_paid']+$order_info['order_amount']),2)  );
    $smarty->assign('order_info', $order_info );

    if($_REQUEST['showwxpaytitle']==1){//别人的
        
        if(floatval($order_info['order_amount'])<=0.00){
            echo"<script>";
            echo"alert('已经付过了');";
            echo"window.location='index.php';";
            echo"</script>";

            //hhs_header("location:index.php");
            exit();
            
        }
        //这里别人付款
        $smarty->assign('order_goods', order_goods($order_id) );

        $payment = payment_info($order_info['pay_id']);
        
        include_once('includes/modules/payment/wxpay.php');
        
        $pay_obj    = new wxpay();
        
        $pay_online = $pay_obj->get_code2($order_info, unserialize_config($payment['pay_config']) );
        
        $smarty->assign('code', json_encode($pay_online['jsApiParameters']) );  
        
        $smarty->assign('returnrul', $pay_online['returnrul'] );
        
        $smarty->display('share_to_pay.dwt');
        
    }else{//自己

        $smarty->assign('imgUrl', $user_info['headimgurl']);//'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
        $smarty->assign('title', "找人代付");
        $smarty->assign('desc', mb_substr($order_info['wxdesc'], 0,30,'utf-8')  );//
        $link="http://" . $_SERVER['HTTP_HOST'] . "/share_pay.php?showwxpaytitle=1&id=".$order_id;
        $smarty->assign('link', $link );
        $smarty->assign('link2', urlencode($link) );
        $smarty->display('share_pay.dwt');
    }
    
}
elseif($act=='go_to_pay'){

    $order_info=order_info($order_id);
    $smarty->assign('goods_list', order_goods($order_id));
    $smarty->assign('order', $order_info );
    $smarty->display("go_share_pay.dwt");
   
}

elseif($act=='success'){
    
    $order_info=order_info($order_id);
    $smarty->assign('order_info', $order_info );
    $sql="select count(*) from ".$hhs->table("share_pay_info")." where order_id=".$order_id." and user_id=".$_SESSION['user_id']." and is_paid=1";
    $pay_c=$db->getOne($sql);

    if($order_info['pay_status']==2 && $pay_c>0){
        $smarty->assign('imgUrl', $user_info['headimgurl']);//'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
        $smarty->assign('title', "找人代付");
        $smarty->assign('desc', "付款成功！订单编号：".$order_info['order_sn']  );//
        $link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        $smarty->assign('link', $link );
        $smarty->assign('link2', urlencode($link) );
        $smarty->assign('is_success', 1 );
    }else{
        $smarty->assign('is_success', 0 );
        hhs_header("location:index.php");
        exit();
    }
    $smarty->display('share_success.dwt');
}
elseif($act=='to_confirm'){
    $wxdesc=$_REQUEST['wxdesc'];
    $share_pay_type=$_REQUEST['share_pay_type'];
    $sql="update ".$hhs->table('order_info')." set wxdesc='$wxdesc',share_pay_type='$share_pay_type' where order_id=".$order_id;
    $db->query($sql);
    $order_info=order_info($order_id);
    $smarty->assign('progress', number_format($order_info['money_paid']*100/($order_info['money_paid']+$order_info['order_amount']),2)  );
    $smarty->assign('order_info', $order_info );
    
    $smarty->assign('imgUrl', $user_info['headimgurl']);//'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
    $smarty->assign('title', "代付");
    $smarty->assign('desc', mb_substr($order_info['wxdesc'], 0,30,'utf-8')  );//
    $link="http://" . $_SERVER['HTTP_HOST'] . "/share_pay.php?showwxpaytitle=1&id=".$order_id;
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
    
    $smarty->display('share_pay.dwt');
    //var_dump($order_info);
}elseif($act=='share_pay_check'){//检查团购的
    
    include_once('includes/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0,'message'=>'', 'content' => '');

    $order_info=order_info($order_id);
    if($order_info['pay_status']==2){
        $result['error']=2;
        $result['message']="别人已经付过了";
        $result['url']="index.php";
        die($json->encode($result));
    }
    if($order_info['extension_code']=='team_goods' && $order_info['is_first']==1){
        $sql="select team_num from ".$hhs->table('order_info') ." where order_id=".$order_info['team_sign'];
        $team_num=$db->getOne($sql);
        //实际人数
        $sql="select count(*) from ".$hhs->table('order_info')." where team_sign=".$order_info['team_sign']." and team_status>0 ";
        $rel_num=$db->getOne($sql);
        if($team_num<=$rel_num){//
            $result['error']=2;
            $result['message']="对不起，团购人数已满";
            $result['url']="share.php?team_sign=".$order_info['team_sign'];
            die($json->encode($result));
        }   
    }
    $name=trim($_REQUEST['name']);
    $message=trim($_REQUEST['message']);
    $money=floatval(trim($_REQUEST['money']));
   
    $sql="select id from ".$hhs->table('share_pay_info')." where user_id='$_SESSION[user_id]' and order_id=".$order_info['order_id'] ;
    if($db->getOne($sql)>0){
        $sql="update ".$hhs->table("share_pay_info").
        " set name='$name',message='$message',".
        "money='$money',user_id='$_SESSION[user_id]',".
        "status=0,addtime=".gmtime().",order_id='$order_id',is_paid=0 where id=".$id;
        $db->query($sql);
    }else{
        $sql="insert into ".$hhs->table("share_pay_info")." (name,message,money,user_id,status,addtime,order_id,is_paid) values ('$name','$message','$money','$_SESSION[user_id]',0,".gmtime().",'$order_id',0) ";
        $db->query($sql);   
    }
    die($json->encode($result));
}



?>