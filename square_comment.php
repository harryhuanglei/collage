<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

if($_REQUEST['act'] == 'comment_list'){
		
	$order_id = $_REQUEST['order_id'];
	$goods_id = $_REQUEST['goods_id'];
	
	$sql	= "select goods_name,team_price,market_price,goods_thumb from ".$GLOBALS['hhs']->table('goods')." where goods_id = ".$goods_id;
	$row = $db->getRow($sql);
	$row['gallery']   = $db->getAll("select thumb_url from ".$hhs->table('goods_gallery')." where goods_id = '".$goods_id."' limit 3");
	$user_sql = "select u.uname,u.headimgurl,o.square,o.order_id from ".$GLOBALS['hhs']->table('order_info')." AS o INNER JOIN ".$GLOBALS['hhs']->table('users')." AS u ON o.user_id = u.user_id where order_id = ".$order_id;
	$row['users'] = $db->getRow($user_sql);
	$row['goods_id'] = $goods_id;
	
	$comment_sql = "select c.content,c.add_time,u.uname,u.headimgurl from ".$GLOBALS['hhs']->table('square_comment')." AS c INNER JOIN ".$GLOBALS['hhs']->table('users')." AS u ON c.comment_user_id = u.user_id where order_id = ".$order_id." AND goods_id = ".$goods_id;
	$comment = $db->getAll($comment_sql);
	foreach ($comment as $k => $v){
		$comment[$k]['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
	}
	$smarty->assign('comment',$comment);
	$smarty->assign('goods',$row);
	$smarty->display('square_comment.dwt');
}

if($_REQUEST['act'] == 'insert'){
	
	$order_id = $_POST['order_id'];
	$goods_id = $_POST['goods_id'];
	$content = addslashes($_POST['content']);
	$comment_user_id = $_SESSION['user_id'];
	$add_time = gmtime();
	if($content == null){
		$res = array(
				'isError' => 1
		);
		echo json_encode($res);
		exit();
	}
	$sql = "INSERT INTO "
			.$GLOBALS['hhs']->table('square_comment').
			"(content,order_id,goods_id,comment_user_id,add_time) VALUES ("
			."'".$content."'".",".$order_id.",".$goods_id.",".$comment_user_id.",".$add_time.
			")";
	$db->query($sql);
	$comment_sql = "select c.content,c.add_time,u.uname,u.headimgurl from ".$GLOBALS['hhs']->table('square_comment')." AS c INNER JOIN ".$GLOBALS['hhs']->table('users')." AS u ON c.comment_user_id = u.user_id where order_id = ".$order_id." AND goods_id = ".$goods_id;
	$comment = $db->getAll($comment_sql);
	
	$update_sql = "UPDATE ".$GLOBALS['hhs']->table('square_mes')." set comment_num = comment_num+1 where order_id = ".$order_id." AND goods_id = ".$goods_id;
	$db->query($update_sql);
	
	$res = array(
			'comment'=>$comment,
			'isError' => 0
	);
	echo json_encode($res);
	exit();
	
}

if($_REQUEST['act'] == 'announcement_comment_list'){
	$id = $_GET['id'];
	$sql = "select * from ".$GLOBALS['hhs']->table('announcement')." where id = ".$id;
	$row = $GLOBALS['db']->getRow($sql);
	$row['add_time'] = local_date('Y-m-d H:i:s',$row['add_time']);
	
	$comment_sql = "select sac.*,u.uname,u.headimgurl from "
			.$GLOBALS['hhs']->table('square_announcement_comment')." AS sac,"
			.$GLOBALS['hhs']->table('users')." AS u".
			" where sac.comment_user_id = u.user_id";
	
	$comment = $GLOBALS['db']->getAll($comment_sql);
	
	foreach ($comment as $k => $v){
		
		$comment[$k]['add_time'] = local_date('Y-m-d H:i:s ',$v['add_time']);
	
	}
	$smarty->assign('comment',$comment);
	$smarty->assign('announcement',$row);
	$smarty->display('square_announcement_comment.dwt');
}

if($_REQUEST['act'] == 'announcement_comment_insert'){
	$id = $_POST['id'];
	$comment_user_id = $_SESSION['user_id'];
	$content =addslashes($_POST['content']);
	if($content == null){
		$res = array(
			'isError' => 1
		);
		echo json_encode($res);
		exit();
	}
	$sql = "INSERT INTO ".$GLOBALS['hhs']->table('square_announcement_comment')." (content,add_time,announcement_id,comment_user_id) VALUES ("."'".$content."'".",".gmtime().",".$id.",".$comment_user_id.")";
	$db->query($sql);
	$comment_num_sql = "UPDATE ".$GLOBALS['hhs']->table('announcement')." SET comment_num = comment_num+1 where id = ".$id;
	$db->query($comment_num_sql);
	$res = array(
		'isError' => 0
	);
	echo json_encode($res);
	exit();
	
	
}
/**
 * 朋友圈评论列表
 * @author houwei <277096656@qq.com>
 */
if($_REQUEST['act'] == 'moments_comment_list'){
	$id = $_GET['id'];
	$sql = "select * from ".$GLOBALS['hhs']->table('moments')." where id = ".$id;
	$row = $GLOBALS['db']->getRow($sql);
	$row['add_time'] = local_date('Y-m-d H:i:s',$row['add_time']);
	
	$comment_sql = "select * from ".$GLOBALS['hhs']->table('square_moments_comment')." where moments_id = ".$id;
	$comment = $GLOBALS['db']->getAll($comment_sql);
	
	foreach ($comment as $k => $v){
		$comment[$k]['add_time'] = local_date('Y-m-d H:i:s ',$v['add_time']);
		$comment[$k]['uname'] = get_uinfo($v['comment_user_id'],'uname');
		$comment[$k]['headimgurl'] = get_uinfo($v['comment_user_id'],'headimgurl');
	}
	$smarty->assign('moments_comment',$comment);
	$smarty->assign('moments',$row);
	$smarty->display('square_moments_comment.dwt');
}
/**
 * 朋友圈评论
 * @author houwei <277096656@qq.com>
 */
if($_REQUEST['act'] == 'moments_comment_insert'){
	$id = $_POST['id'];
	$comment_user_id = $_SESSION['user_id'];
	$content =addslashes($_POST['content']);
	if($content == null){
		$res = array(
			'isError' => 1
		);
		echo json_encode($res);
		exit();
	}
	$sql = "INSERT INTO ".$GLOBALS['hhs']->table('square_moments_comment')." (content,add_time,moments_id,comment_user_id) VALUES ("."'".$content."'".",".gmtime().",".$id.",".$comment_user_id.")";
	
	if($db->query($sql)){
		$comment_num_sql = "UPDATE ".$GLOBALS['hhs']->table('moments')." SET comment_num = comment_num+1 where id = ".$id;
		$db->query($comment_num_sql);
		
		$comment_num_sql = "select comment_num from ".$GLOBALS['hhs']->table('moments')." where id = ".$id;
		$comment_num = $GLOBALS['db']->getOne($comment_num_sql);
		
		$res = array(
		'zan_num'=> $comment_num,
		'isError' => 0,
		'uname'=> get_uinfo($comment_user_id ,'uname'),
		'content'=> $content
		);
	}
	
	echo json_encode($res);
	exit();
	
	
}


function d($arr){
	echo "<pre>";
	var_dump($arr);
	die;
}