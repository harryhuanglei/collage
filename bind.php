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
$sql = "select `suppliers_id` from ".$hhs->table('suppliers')." where `user_id` = " . $user_id;
$nums = $db->getOne($sql);
if($nums)
{
    show_message('您已经绑定过了！','', 'store.php?id='.$nums);
    exit();
}

$sql = "update ".$hhs->table('suppliers')." as s,".$hhs->table('users')." as u set s.`user_id`=" . $user_id . ",s.`openid`=u.`openid` where `suppliers_id` = " . $suppliers_id ." and u.`user_id`=".$user_id;
$db->query($sql);

show_message('绑定成功！请销毁该二维码防止盗用！','', 'store.php?id='.$suppliers_id);
exit();
?>
