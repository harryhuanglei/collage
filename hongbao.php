<?php

define('IN_HHS', true);
define('ROOT_PATH', dirname(__FILE__) . '/');

include_once ROOT_PATH . 'includes/init2.php';
include ROOT_PATH . 'wxpay/wx_hongbao.php';

$re_openid = 'oFw1nxI31ROrenKr395P9TxHacRg';
$total_amount = 100;

$res = $hongbao->send();
echo "<pre>";
print_r($res);
// echo Common_util_pub::getSign($hongbao->parameters,$payment['wxpay_key']);