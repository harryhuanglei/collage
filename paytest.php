<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init2.php');
require(dirname(__FILE__) . '/includes/lib_payment.php');
require_once(dirname(__FILE__) . '/wxpay/class_weixin.php');
require(dirname(__FILE__) . '/includes/modules/payment/wxpay.php');
require(dirname(__FILE__) . '/includes/lib_order.php');

$orsn='2016051319873';
pay_team_action($orsn);
echo "string";
// $order_id = '140';
// send_order_bonus($order_id);

// echo $GLOBALS['appid'];
// $openid = 'oFw1nxDt5Dnjgjr1DJrIan2Dcz7Y';

// $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
// $r= $weixin->send_wxmsg($openid, '不要随意更改商品信息~~' , '' , '');
// print_r($r);
