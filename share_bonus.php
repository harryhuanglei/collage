<?php

define('IN_HHS', true);

define('HHS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_SESSION['user_id'] > 0)
{

	$smarty->assign('user_name', $_SESSION['user_name']);

}
$appid=$weixin_config_rows['appid'];
$secret= $weixin_config_rows['appsecret'];
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

assign_template();
$user_id = $_SESSION['user_id'] ? $_SESSION['user_id'] : '';

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

/* 用户中心 */
if ($user_id <= 0){

	$smarty->assign('gourl', "share.php");

	$smarty->display('login.dwt');
	exit;
}

/*分享优惠券 */
if ($act == 'default'){
	include_once(ROOT_PATH . 'includes/lib_clips.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
	$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
	$user_info=$db->getRow($sql);
	
	$send_id = isset($_REQUEST['send_id']) ? trim($_REQUEST['send_id']) : 0;
	if(!empty($send_id)){
		$sql="select * from ".$hhs->table('send_bonus_type')." where send_id=".$send_id;
		$send_bonus_type=$db->getRow($sql);
		if(empty($send_bonus_type)){
		    echo'send_id参数错误';
		    exit();
		}
		
		if($send_bonus_type['user_id']==$_SESSION['user_id']){
		    //发放者打开
			hhs_header("Location:user.php");
			exit();
		}
		$sql="select * from ".$hhs->table('user_bonus')." where send_id=".$send_id." and user_id=0 ";
		$user_bonus=$db->getAll($sql);
		if(empty($user_bonus)){
		    //已经领完
		    $smarty->assign('status', 1 );
		    $smarty->display('share_bonus.dwt');
		    exit();
		}else{
		    //未领完
		    $sql="select * from ".$hhs->table('user_bonus')." where send_id=".$send_id." and user_id= ".$_SESSION['user_id'];
		    $temp=$db->getRow($sql);
		    if(!empty($temp)){
		        //已经领取过一次
		        $smarty->assign('status', 2 );
		        $smarty->display('share_bonus.dwt');
		        exit();
		    }else{
		        //成功领取
		        $bonus_id=$user_bonus[0]['bonus_id'];
		        $sql="update ".$hhs->table('user_bonus')." set user_id=".$_SESSION['user_id']." where bonus_id=".$bonus_id;
		        $db->query($sql);
		        $sql="select b.type_money,b.use_start_date,b.use_end_date from ".$hhs->table('user_bonus')." as u left join ".$hhs->table('bonus_type')." as b on u.bonus_type_id=b.type_id where u.bonus_id= ".$bonus_id;
		        $row=$db->getRow($sql);
		        
		        $smarty->assign('bonus_money', $row['type_money'] );
		        $smarty->assign('use_start_date',local_date("Y-m-d",$row['use_start_date'])  );
		        $smarty->assign('use_end_date', local_date("Y-m-d",$row['use_end_date']) );
		        $smarty->assign('status', 3 );
		        $smarty->display('share_bonus.dwt');
		        exit();
		    }
		    
		}
	}
	
	
	$order_id = isset($_REQUEST['order_id']) ? trim($_REQUEST['order_id']) : 0;
	if(empty($order_id)){
	    echo'order_id参数错误';
		exit();
	}
	//查询订单的商家id
	$suppliers_id = $GLOBALS['db']->getOne("select 	suppliers_id from ". $GLOBALS['hhs']->table('order_info') ." where order_id='$order_id'");
    $suppliers_id = $suppliers_id>0 ? $suppliers_id : 0;

	$arr=array();
	$bonus_list = order_bonus($order_id, $suppliers_id);

	$bonus_list1=array();
	$bonus_list2=array();
	foreach($bonus_list as $bonus){
		if($bonus['number'] ==0) continue;
		if($bonus['is_share']==0){
		    $bonus['use_start_date']=local_date("Y-m-d", $bonus['use_start_date']);
		    $bonus['use_end_date']=local_date("Y-m-d", $bonus['use_end_date']);
		    $bonus_list1[]=$bonus;
		}elseif($bonus['is_share']==1){//好友券
		    $bonus_list2[]=$bonus;
		}
	}
	
    $sql="select * from ".$hhs->table('send_bonus_type')." where send_order_id=".$order_id;
    $send_bonus=$db->getRow($sql);
    
	$smarty->assign('bonus_list1', $bonus_list1 );
	$smarty->assign('send_number', $send_bonus['send_number'] );
	$smarty->assign('send_id', $send_bonus['send_id'] );

	//$appid = 'wx92970832ecad238a';
	//$secret= '37c36d9099ee7635fe1f4ea5230412db';
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	
	
	$smarty->assign('goods_thumb', $user_info['headimgurl']);//'http://'.$_SERVER['HTTP_HOST']."/".$goods_list[0]['goods_thumb']
	$smarty->assign('shb_title', $_CFG['shb_title']);
	$smarty->assign('shb_desc',  mb_substr($_CFG['shb_desc'], 0,30,'utf-8') );//mb_substr($goods_list[0]['goods_brief'], 0,30,'utf-8')

	$link="http://" . $_SERVER['SERVER_NAME'] . "/share_bonus.php?send_id=".$send_bonus['send_id'] ;
	$smarty->assign('link', $link );
	$smarty->assign('link2', urlencode($link) );
	
	$smarty->assign('shop_name', $_CFG['shop_title'] );
	$smarty->display('share_bonus.dwt');
} 



/*
function jssdk($appid,$secret){

    $_title = '微信';
    $code = $_GET['code'];//获取code
    $_SESSION['code'] = $code;//设置code缓存给微信付账使用
    $auth = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code");//通过code换取网页授权access_token
    $jsonauth = json_decode($auth); //对JSON格式的字符串进行编码
    $arrayauth = get_object_vars($jsonauth);//转换成数组
    $openid = $arrayauth['openid'];//输出openid
    $access_token = $arrayauth['access_token'];
    $_SESSION['openid'] = $openid;
     
    $accesstoken = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret."");//获取access_token
    $token = json_decode($accesstoken); //对JSON格式的字符串进行编码
    $t = get_object_vars($token);//转换成数组
    $access_token = $t['access_token'];//输出access_token
     
    $jsapi = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi");
    $jsapi = json_decode($jsapi);
    $j = get_object_vars($jsapi);
    $jsapi = $j['ticket'];//get JSAPI
     
    $time = '14999923234';
    $noncestr= $time;
    $jsapi_ticket= $jsapi;
    $timestamp=$time;
    $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $and = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url."";
    $signature = sha1($and);
    return $signature;
}*/
?>