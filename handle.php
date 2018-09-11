<?php
/**
 * 自提点扫码处理订单
 */
define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(dirname(__FILE__) . '/includes/lib_fenxiao.php');

date_default_timezone_set("Asia/Shanghai");

$order_id = intval($_GET['order_id']);

if(!$order_id){
	hhs_header("location:index.php");
	exit();
}
//订单
$sql = "SELECT `order_id`,`order_sn`,`order_status`,`shipping_status`,`pay_status`,`shipping_id`,`shipping_point`,`point_id`,`team_sign`,`team_first`,`package_one`,`add_time`,`checked_mobile`,`mobile`,`money_paid`,`bonus`,`surplus` FROM ".$hhs->table('order_info')." WHERE `order_id` = " . $order_id;
$info = $db->getRow($sql);
//已经处理过
if($info['shipping_status'] ==2)
{
	//hehe
	show_message('您刚才不是已经扫过了吗？','', 'index.php');
	exit();
}
elseif($info['pay_status'] =='3')
{
	show_message('该订单已退款不能再核销了','', 'index.php');
	exit();
	
}
elseif($info['order_status'] =='2')
{
	show_message('该订单已取消不能再核销了','', 'index.php');
	exit();
	
}

/**
 * 验证店主
 */
$user_id = $_SESSION['user_id'];
// $sql = "SELECT o.`wx_openid` FROM ".$hhs->table('shipping_point')." as o,".$hhs->table('users')." as u WHERE o.`wx_openid` = u.`openid` and u.`user_id` = ".$user_id." AND o.`id` = " . $info['point_id'];

$sql = "select `id` FROM  ".$hhs->table('shipping_point_user')." WHERE `point_id` = '".$info['point_id']."' AND `openid` = '".$_SESSION['xaphp_sopenid']."' ";
$wx_openid = $db->getOne($sql);

if(! $wx_openid){
	//hehe
	show_message('您不是该店店主！','', 'index.php');
	exit();
}
//打印相关
$sql = "select * FROM ".$hhs->table('shipping_point')." WHERE `id` = '".$info['point_id']."'";
$point = $db->getRow($sql);
define('SHOP_NAME', $point['shop_name']);

if ($point['has_printer']) {
	require(dirname(__FILE__) . '/includes/lib_'.$point['printer_type'].'.php');
	if($point['printer_type'] == 'feyin')
	{
		define('MEMBER_CODE', $point['device_code']);
		define('FEYIN_KEY', $point['device_key']);
		define('DEVICE_NO', $point['device_no']);

		//以下2项是平台相关的设置，您不需要更改
		define('FEYIN_HOST','my.feyin.net');
		define('FEYIN_PORT', 80);
	}
}
//同楼购
if($info['package_one'])
{
	if ($info['team_first'] > 1) {
		//hehe
		show_message('您不是团长！','', 'index.php');
		exit();
	}
	else{
		$sql = "SELECT u.`openid`,o.`order_sn`,o.`order_id`,o.`order_status`,o.`shipping_status`,o.`pay_status`,o.`shipping_id`,o.`shipping_point`,o.`point_id`,o.`team_sign`,o.`team_first`,o.`package_one`,o.`add_time`,o.`checked_mobile`,o.`mobile`,o.`money_paid`,o.`bonus`,o.`surplus`  
			FROM ".$hhs->table('order_info')." as o,
			".$hhs->table('users')." as u 
			WHERE o.`order_status` = 1 
			AND o.`pay_status` = 2 
			AND o.`team_sign` = " . $info['team_sign'] ."  
			AND o.`user_id` = u.`user_id`" ;
		$rows  = $db->getAll($sql);
		foreach ($rows as $key => $row) {
			$order_sn        = $row['order_sn'];
			$order_status    = 1;
			$shipping_status = 2;
			$pay_status      = 2;
			$note            = '到店取货一键处理';
			$username        = '自提点'.$info['point_id'].'店主';
			order_action($order_sn, $order_status, $shipping_status, $pay_status, $note, $username);
			//分销更新状态
			$update_at = gmtime();
			updateMoney($row['order_id'],$update_at);
			//打印
			handle_print($row);
			//消息
			$openid = $row['openid'];
			$title = '核销成功';
			$url = 'user.php?act=order_detail&order_id=' . $row['order_id'];
			$description = '您的订单团长已经成功提货！感谢您的惠顾，记得常来哦!';
			$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
			$weixin->send_wxmsg($openid, $title , $url , $description );
		}
		//更新状态
		$sql = "UPDATE ".$hhs->table('order_info')." SET `order_status` = 1,`shipping_status` = 2,`arranged_time`= '".time()."',`pay_status` = 2,`op` = '".$_SESSION['user_name']."',`op_uid` = '".$_SESSION['user_id']."' WHERE `team_sign` = " . $info['team_sign'];
		$db->query($sql);
	}
}
else{
	//更新状态
	$sql = "UPDATE ".$hhs->table('order_info')." SET `order_status` = 1,`shipping_status` = 2,`arranged_time`= '".time()."',`pay_status` = 2,`op` = '".$_SESSION['user_name']."',`op_uid` = '".$_SESSION['user_id']."' WHERE `order_id` = " . $order_id;
	$db->query($sql);

	$order_sn        = $info['order_sn'];
	$order_status    = 1;
	$shipping_status = 2;
	$pay_status      = 2;
	$note            = '到店取货一键处理';
	$username        = '自提点'.$info['point_id'].'店主';
	order_action($order_sn, $order_status, $shipping_status, $pay_status, $note, $username);

	//分销更新状态
	$update_at = gmtime();
	updateMoney($order_id,$update_at);

	handle_print($info);
	//发送消息
	$sql = "SELECT u.`openid` FROM ".$hhs->table('order_info')." as o,".$hhs->table('users')." as u WHERE o.`order_id` = " .$order_id .' AND o.`user_id` = u.`user_id`';
	$openid = $db->getOne($sql);
	if($openid)
	{
		$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
		$title = '核销成功';
		$url = 'user.php?act=order_detail&order_id=' . $info['order_id'];
		$description = '您已经成功提货！感谢您的惠顾，记得常来哦!';
		$weixin->send_wxmsg($openid, $title , $url , $description );
	}
}

//打印相关
function handle_print($info){
	global $hhs,$db,$_CFG;
	
	if(! defined('FEYIN_KEY'))
		return;

	$use_surplus = $info['surplus']?"余额支付：".$info['surplus']."元 ". PHP_EOL:'';
	$use_bonus   = $info['bonus']?"红包支付：".$info['bonus']."元 ". PHP_EOL:'';
	$money_paid  = "支付金额：".$info['money_paid']."元 ". PHP_EOL . PHP_EOL;
	$amount      = $info['money_paid'] + $info['surplus'] + $info['bonus'];
	$amount      = "合计：".$amount."元 ". PHP_EOL;

	//订单商品
	$sql = "select `goods_name`,`goods_number`,`goods_price` from ".$hhs->table('order_goods')." WHERE `order_id` = '".$info['order_id']."'";
	$rows = $db->getAll($sql);

	/*
	 自由格式的打印内容
	*/
	$msgNo       = $info['order_sn'].rand(100,999);
	$msgDetail   = "     ".$_CFG['shop_title']."欢迎您订购". PHP_EOL
	. PHP_EOL.
	"条目         单价（元）    数量". PHP_EOL.
	"------------------------------". PHP_EOL;
	foreach ($rows as $key => $goods) {
	    $msgDetail   .= $goods['goods_name']. PHP_EOL;
	    $msgDetail   .= "              ".$goods['goods_price']."          ".$goods['goods_number']. PHP_EOL;
	}


	$msgDetail   .= PHP_EOL .  
	"------------------------------". PHP_EOL . 
	// $use_surplus .//余额
	$use_bonus .//红包
	$money_paid .//实际支付
	$amount .//合计
	"客户单号：".$msgNo . PHP_EOL.
	"客户电话：".($info['checked_mobile']?$info['checked_mobile']:$info['mobile']).PHP_EOL .
	"订购时间：".date("Y-m-d H:i:s",$info['add_time']).PHP_EOL .PHP_EOL .
	"取货地址：".SHOP_NAME . PHP_EOL.
	"打印时间：".date("Y-m-d H:i:s");

	$freeMessage = array(
		'memberCode' => MEMBER_CODE, 
		'deviceNo'   => DEVICE_NO, 
		'msgDetail'  => $msgDetail,
		'msgNo'      => $msgNo,
	);

	sendFreeMessage($freeMessage);


}


show_message('核销成功','核销成功', 'index.php');


function sendFreeMessage($msg) {
	$msg['reqTime'] = number_format(1000*time(), 0, '', '');
	$content = $msg['memberCode'].$msg['msgDetail'].$msg['deviceNo'].$msg['msgNo'].$msg['reqTime'].FEYIN_KEY;
	$msg['securityCode'] = md5($content);
	$msg['mode']=2;

	return sendMessage($msg);
}

function sendMessage($msgInfo) {
	$client = new HttpClient(FEYIN_HOST,FEYIN_PORT);
	if(!$client->post('/api/sendMsg',$msgInfo)){ //提交失败
		return 'faild';
	}
	else{
		return $client->getContent();
	}
}