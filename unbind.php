<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

$suppliers_id = isset($_GET['suppliers_id']) ? intval($_GET['suppliers_id']) : null;

$sql = "select * from ".$hhs->table('suppliers')." where `suppliers_id` = " . $suppliers_id;
$row = $db->getRow($sql);
//没找到
if(! $row){
    show_message('呃呃，出错了！','', 'index.php');
    exit();
}

/**
 * 重复性检查
 * @var [type]
 */
$user_id = $_SESSION['user_id'];
$sql = "select `suppliers_id` from ".$hhs->table('suppliers')." where `user_id` = " . $user_id ." and `suppliers_id` = " . $suppliers_id;
$find = $db->getOne($sql);
if($find)
{
	$sql = "update ".$hhs->table('suppliers')." set `user_id`='',`openid`='' where `suppliers_id` = " . $suppliers_id;
	$db->query($sql);
    show_message('解绑成功！','', 'store.php?id='.$suppliers_id);
    exit();
}
else{
    show_message('呃呃，出错了！','', 'index.php');
    exit();	
}
exit();
?>
