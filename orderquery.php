<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init2.php');
require(dirname(__FILE__) . '/includes/lib_order.php');
require(dirname(__FILE__) . '/includes/lib_payment.php');

include_once(dirname(__FILE__) . '/includes/modules/payment/wxpay.php');

include_once(ROOT_PATH."wxpay/WxPayPubHelper.php");

$refund = new OrderQuery_pub();

$payment    = get_payment('wxpay');

$refund->setParameter("appid", $payment['wxpay_app_id']);
$refund->setParameter("mch_id", $payment['wxpay_mchid']);
$refund->wxpay_key = $payment['wxpay_key'];

$order_sn = trim($_GET['order_sn']);
$refund->setParameter("out_trade_no",$order_sn);//商户订单号

$stats = array(
'SUCCESS'=>'支付成功',
'REFUND'=>'转入退款',
'NOTPAY'=>'未支付',
'CLOSED'=>'已关闭',
'REVOKED'=>'已撤销（刷卡支付）',
'USERPAYING'=>'用户支付中',
'PAYERROR'=>'支付失败(其他原因，如银行返回失败)',
	);
//调用结果
ob_end_clean();
$result = $refund->getResult();
echo '查询订单：' . $order_sn;
if ($result['result_code'] == 'SUCCESS') {
	echo "\n交易状态：".$stats[$result['trade_state']];
	echo "\n实际支付：￥".price_format($result['total_fee']/100);
}
else{
	echo "\n订单状态：".$result['err_code_des'];
}