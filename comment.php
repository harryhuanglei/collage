<?php
define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

$user_id = $_SESSION['user_id'];
if (empty($user_id)) {
    exit();
}

$act = trim($_GET['act']);

if ($act == 'create') {
    $content      = trim($_POST['comment']);
    $comment_rank = intval($_POST['stars']);
    $id_value     = intval($_POST['id_value']);
    $add_time     = gmtime();
    $ip_address   = real_ip();
    $order_id     = intval($_POST['order_id']);
    $user_name    = $_SESSION['user_name'];

    $sql = "select comment_id from ".$hhs->table('comment')." where user_id = '".$user_id."' and id_value = '".$id_value."' and order_id = '".$order_id."' ";
    $comment_id = $db->getOne($sql);
    if ($comment_id) {
        $res = array(
            'isError' => 1,
            'message' => '已评论过了！'
        );
        echo json_encode($res);
        exit();
    }

    
    $sql = "insert into ".$hhs->table('comment')." (`user_name`,`user_id`,`content`,`comment_rank`,`id_value`,`add_time`,`ip_address`,`order_id`) values ('$user_name','$user_id','$content','$comment_rank','$id_value','$add_time','$ip_address','$order_id')";
    $db->query($sql);
    $id = $db->insert_id();
    if ($id) {
    	$db->query('update '.$hhs->table('order_info').' set `is_comm` = 1 where `order_id` = "'.$order_id.'"');
    	$res = array(
    		'isError' => 0
    	);
    }
    else{
    	$res = array(
    		'isError' => 1,
    		'message' => '评论失败，请重试！'
    	);
    }
    echo json_encode($res);
    exit();
}

?>
