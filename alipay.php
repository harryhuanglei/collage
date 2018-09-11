<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
    $smarty->assign('order_id', $_REQUEST['order_id'] );
	$smarty->display('alipay.dwt');

?>
