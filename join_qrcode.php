<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

$point_id = isset($_GET['id']) ? intval($_GET['id']) : null;

//没找到
if(! $point_id){
    show_message('呃呃，出错了！','', 'index.php');
    exit();
}

/**
 * 重复性检查
 * @var [type]
 */
$sql = "select `id` from ".$hhs->table('shipping_point_user')." where `point_id` = " . $point_id ." and `openid` = '".$_SESSION['xaphp_sopenid']."' ";
$nums = $db->getOne($sql);
if($nums)
{
    show_message('您已经绑定过了！','', 'index.php');
    exit();
}

$sql = "insert into ".$hhs->table('shipping_point_user')." set `point_id` = '".$point_id."',`openid` = '".$_SESSION['xaphp_sopenid']."' ";
$db->query($sql);

show_message('绑定成功！请销毁该二维码防止盗用！','', 'index.php');
exit();
?>
