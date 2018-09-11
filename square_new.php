<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
//2016-12-14 引进上传图片类
require(dirname(__FILE__) . '/includes/cls_image.php');

$act = trim($_GET['act']);
if ($act == 'create') {
    $square   = trim($_POST['square']);
    if(empty($square)){
        $res = array(
            'isError' => 1,
            'message' => '评论内容不能为空！'
        );
        echo json_encode($res);
        exit();
    }
    $order_id = intval($_POST['order_id']);
     $luckdraw_id_sql = "select luckdraw_id from ".$hhs->table('order_info')." where order_id = ".$order_id;
	    $luckdraw_id = $GLOBALS['db']->getOne($luckdraw_id_sql);
	    if($luckdraw_id != 0){
	        $res = array(
	            'isError' => 1,
	            'message' => '抽奖商品不能发布到参团广场！'
	        );
	    echo json_encode($res);
	    exit();
	    }
    $user_id  = $_SESSION['user_id'];
	$goods_id = $_POST['goods_id'];
    $sql = "update ".$hhs->table('order_info')." set `square` = '".$square."' where user_id = '".$user_id."' and `order_id` = '".$order_id."'";
    $db->query($sql);
    $insert_square_sql =  "INSERT INTO ".$GLOBALS['hhs']->table('square_mes')." ( order_id,goods_id,square_add_time) VALUES (".$order_id.",".$goods_id.",".gmtime().")";
    $db->query($insert_square_sql);
    $res = array(
        'isError' => 0
    );
    echo json_encode($res);
    exit();
}

if($act == 'zan'){
	$goods_id  =$_POST['goods_id'];
	$order_id = $_POST['order_id'];
	$user_id = $_SESSION['user_id'];
	
	$select_zan_log = "select id from ".$GLOBALS['hhs']->table('goods_zan_log')." where goods_id = ".$goods_id." AND user_id = ".$user_id." AND order_id = ".$order_id;
	$row = $GLOBALS['db']->getAll($select_zan_log);
	  if($row){
		$res = array(
			'isError' => 2,
		);
		echo json_encode($res);
		exit();
	}  
	
	$zan_log_sql = "INSERT INTO ".$GLOBALS['hhs']->table('goods_zan_log')." (goods_id,user_id,order_id) VALUES (".$goods_id.",".$user_id.",".$order_id.")";
	$db->query($zan_log_sql);
	
	$update_sql = "UPDATE ".$GLOBALS['hhs']->table('square_mes')." set zan_num = zan_num+1 where order_id = ".$order_id." AND goods_id = ".$goods_id;
	$db->query($update_sql);
	
	$zan_num = "select zan_num from".$GLOBALS['hhs']->table('square_mes')."where order_id = ".$order_id." AND goods_id = ".$goods_id;
	$num = $GLOBALS['db']->getOne($zan_num);
	
	$res = array(
        	'zan_num'=>$num,
			'isError' => 0
    );
    echo json_encode($res);
    exit();
	
}

/**
 * 公告赞
 * 
 */
if($act == 'announcement_zan'){
	
	$id = $_POST['id'];
	$user_id = $_SESSION['user_id'];
	$sql = "select id from ".$GLOBALS['hhs']->table('announcement')." where zan_user_id = ".$user_id. " and id = ".$id;
	$user = $GLOBALS['db']->getOne($sql);
	if($user){
		$res = array(
				'isError' => 2
		);
		echo json_encode($res);
		exit();
	}
	$zan_sql = "update ".$GLOBALS['hhs']->table('announcement')." set zan_num = zan_num+1 , zan_user_id = ".$user_id." where id = ".$id;
	$db->query($zan_sql);
	$zan_num_sql = "select zan_num from ".$GLOBALS['hhs']->table('announcement')." where id = ".$id;
	$zan_num = $GLOBALS['db']->getOne($zan_num_sql);

	$res = array(
			'zan_num'=>$zan_num,
			'isError' => 0
	);
	echo json_encode($res);
	exit();
}

/**
 * 朋友圈发布文本
 * @author houwei <277096656@qq.com>
 */
if($act == 'add_moments_text'){
	/* 返回标准数据 */
	$content = addslashes($_POST['content']);
	if(empty($content) || strlen($content) >= 255){
		$res = array(
				'status'=>0,
				'info' => '评论内容不能为空或超出字符限制！'
		);
		exit(json_encode($res));
	}
	$user_id =$_SESSION['user_id'];
	$count = count($_POST['images']);
	if($count > 3){
		$res = array(
				'status'=>0,
				'info' => '最多可上传3张图片！'
		);
		exit(json_encode($res));	
	}
	switch($count){
		case 1:
			$img_01 =  trim($_POST['images'][0]);
			$moments_insert_sql = "INSERT INTO ".$GLOBALS['hhs']->table('moments') . " (add_time,user_id,content,img_01) VALUES (".gmtime().",".$user_id.",'".$content."','".$img_01."')";
			break;
		case 2:
			$img_01 =  trim($_POST['images'][0]);
			$img_02 =  trim($_POST['images'][1]);
			$moments_insert_sql = "INSERT INTO ".$GLOBALS['hhs']->table('moments') . " (add_time,user_id,content,img_01,img_02) VALUES (".gmtime().",".$user_id.",'".$content."','".$img_01. "','".$img_02 ."')";
			break;
		case 3:
			$img_01 =  trim($_POST['images'][0]);
			$img_02 =  trim($_POST['images'][1]);
			$img_03 =  trim($_POST['images'][2]);
			$moments_insert_sql = "INSERT INTO ".$GLOBALS['hhs']->table('moments') . " (add_time,user_id,content,img_01,img_02,img_03) VALUES (".gmtime().",".$user_id.",'".$content."','".$img_01. "','".$img_02 ."','".$img_03."')";
			break;
		default:
			$moments_insert_sql = "INSERT INTO ".$GLOBALS['hhs']->table('moments') . " (add_time,user_id,content) VALUES (".gmtime().",".$user_id.",'".$content."')";
	}
	$aa = $db->query($moments_insert_sql);
	if($aa){
		$res = array(
				'status' =>1,
				'info'  => '发布成功！'
		);
	}else{
		$res = array(
				'status'=>0,
				'info' => '发布失败！'
		);
	}
	exit(json_encode($res));
}
/**
 * 朋友圈上传图片
 * @author houwei <277096656@qq.com>
 */
if($act == 'add_moments_img'){
	$upload = new cls_image;
	$info = $upload->upload_image($_FILES['file']);
	if($info){
		$path = $upload->make_thumb($info,640,640);
		unlink($info);
		exit($path);
	}else{
		exit($info);
	}	
} 
/**
 * 朋友圈点赞
 * @author houwei <277096656@qq.com>
 */
if($act == 'moments_zan'){
	
	$id = $_POST['id'];
	$user_id = $_SESSION['user_id'];
	$sql = "select zan_user from ".$GLOBALS['hhs']->table('moments')." where id = ".$id;
	$user = $GLOBALS['db']->getRow($sql);
	if(!empty($user['zan_user'])){
		
		if(strpos($user['zan_user'],$user_id) === false){
			
			$zan_user = $user['zan_user'] . ',' .$user_id;
			
			
		}else{
			$res = array(
				'isError' => 2
			);
			echo json_encode($res);
			exit();
		}	
		
	}else{	
		$zan_user = $user_id;
	}
	
	$zan_sql = "update ".$GLOBALS['hhs']->table('moments')." set zan_num = zan_num+1 , zan_user = '".$zan_user."' where id = ".$id;
	$db->query($zan_sql);
	$zan_num_sql = "select zan_num from ".$GLOBALS['hhs']->table('moments')." where id = ".$id;
	$zan_num = $GLOBALS['db']->getOne($zan_num_sql);

	$res = array(
			'zan_num'=>$zan_num,
			'isError' => 0
	);
	echo json_encode($res);
	exit();
}

/* 缓存编号 */
 $cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

    assign_template();
	
    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    $smarty->assign('categories',      get_categories_tree()); // 分类树 
	$smarty->assign('action',     $action);
	$smarty->assign('announcement',get_announcement());

	$loading=$smarty->fetch('loading.html');
	$smarty->assign('loading',    $loading);
    $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';

    $smarty->assign('keywords',    $keywords);
	//$smarty->assign('goods_list',    get_goodslist('best'));
		
	$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'].'?uid='.$uid;//"/index.php";
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
	
	
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');
	$smarty->assign('title', $_CFG['square_title']);
	$smarty->assign('desc', mb_substr($_CFG['square_desc'], 0,30,'utf-8')  );
	//$smarty->assign('moments',get_moments());
	$arr1 = get_moments();
	$arr2 = get_goodslist();
	$arr3 =array();
	
		foreach ($arr2 as $key => $value) {
			$k = $value['add_time'];
			$arr3[$k]['uanme'] = $value['uname'];
			$arr3[$k]['goods_id']   = $value['goods_id'];
			$arr3[$k]['goods_name']   = $value['goods_name'];
			$arr3[$k]['goods_number'] = $value['goods_number'];
			$arr3[$k]['market_price'] = $value['market_price'];
			$arr3[$k]['shop_price']   = $value['shop_price'];
			$arr3[$k]['order_id'] = $value['order_id'];
			$arr3[$k]['goods_thumb'] = $value['goods_thumb'];
			$arr3[$k]['little_img']  = $value['little_img'];
			$arr3[$k]['goods_img']   = $value['goods_img'];
			$arr3[$k]['url']         = $value['url'];
			$arr3[$k]['team_price']  = $value['team_price'];
			$arr3[$k]['team_num']    = $value['team_num'];
			$arr3[$k]['need']    = $value['need'];
			$arr3[$k]['square']    = mb_substr($value['square'],0,60,'UTF-8') . '&nbsp;&nbsp;&nbsp;......';
			$arr3[$k]['team_id']    = $value['team_id'];
			$arr3[$k]['uname']       = $value['uname'];
			$arr3[$k]['headimgurl']  = $value['headimgurl'];
			$arr3[$k]['add_time']    = $value['add_time'];
			$arr3[$k]['zan_num'] = $value['zan_num'];
			$arr3[$k]['team_discount']    = $value['team_discount'];
			$arr3[$k]['iszan'] = $value['iszan'];
			$arr3[$k]['comment_num'] = $value['comment_num'];
			$arr3[$k]['luckdraw_id'] = $value['luckdraw_id'];
			$arr3[$k]['is_boutique'] = $value['is_boutique'];
			$arr3[$k]['buy_nums']    = $value['buy_nums'];
			$arr3[$k]['uid']= $value['uid'];
			$arr3[$k]['gallery']   = $value['gallery'];
			
		}
		foreach ($arr1 as $key => $value) {
			$k = $value['add_time'];
			$arr3[$k]['moments_id'] = $value['moments_id'];
			$arr3[$k]['add_time'] = $value['add_time'];
			$arr3[$k]['uname'] = $value['uname'];
			$arr3[$k]['headimgurl'] = $value['headimgurl'];
			$arr3[$k]['comment'] = $value['comment'];
			$arr3[$k]['zan_num'] = $value['zan_num'];
			$arr3[$k]['comment_num'] = $value['comment_num'];
			$arr3[$k]['img_01'] = $value['img_01'];
			$arr3[$k]['img_02'] = $value['img_02'];
			$arr3[$k]['img_03'] = $value['img_03'];
		}
	
	
		foreach($arr3 as $k =>$v){	
				$arr3[$k]['add_time'] = format_date(strtotime(local_date('Y-m-d H:i:s',$v['add_time'])));
		}
	krsort($arr3);
	//1481874564  1481874657
	//$aa = format_date(1481874564);
	//var_dump($aa);
	$smarty->assign('data',$arr3);
	$smarty->display('square_new.dwt'); 



/*函数调用*/	
function  d($arr){
	echo '<pre>';
	var_dump($arr);
	die;
}


function get_goodslist()
{
	global $hhs,$db;
    $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
    $orderby = isset($_GET['orderby']) ? trim($_GET['orderby']) : '';
	
    $where = " AND o.`square`<> '' ";
    if($orderby == 'j'){
        $where .= "AND sm.order_id = o.order_id AND sm.goods_id = og.goods_id AND sm.is_boutique = 1 ";
        $orderby= "sm.square_add_time desc";
    }elseif($orderby == 'p'){
    	$where .= "AND sm.order_id = o.order_id AND sm.goods_id = og.goods_id";
    	$orderby = "sm.comment_num  desc";
    }elseif ($orderby == 'z'){
    	$where .= "AND sm.order_id = o.order_id AND sm.goods_id = og.goods_id";
    	$orderby = "sm.zan_num desc";
    }else{
    	$orderby= "sm.square_add_time desc";
    }
    
    $sql = "select DISTINCT o.luckdraw_id,o.square,o.order_id, sm.is_boutique ,sm.square_add_time ,sm.square_add_time , o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.uname,u.headimgurl from "
    		.$hhs->table('order_info')." as o, "
    		.$hhs->table('users')." as u , "
    		.$hhs->table('square_mes')." as sm ,"
    		.$hhs->table('order_goods').
    		" as og  where o.order_id = og.order_id and o.show_square = 1 and o.user_id = u.user_id and sm.order_id = o.order_id and sm.goods_id = og.goods_id"
    		.$where." order by " . $orderby;
   
    $res = $GLOBALS['db']->getAll($sql);
  // d($sql);
	$user_id = $_SESSION['user_id'];
    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $sql = "select g.is_on_sale,g.is_delete,g.goods_name,g.goods_id, g.goods_number, g.goods_thumb,g.little_img,g.goods_img, g.market_price, g.shop_price,g.team_price  from ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g where g.`goods_id` = o.`goods_id` and o.`order_id` = '".$row['order_id']."'";
        $goods = $db->getRow($sql);
        $zan_num_sql = "select zan_num from".$GLOBALS['hhs']->table('square_mes')."where order_id = ".$row['order_id']." AND goods_id = ".$goods['goods_id'];
		$zan_num = $GLOBALS['db']->getOne($zan_num_sql);
        $user_iszan_sql = "select id from ".$GLOBALS['hhs']->table('goods_zan_log')." where user_id = ".$user_id." AND order_id = ".$row['order_id']." AND goods_id = ".$goods['goods_id'];
        $user_iszan = $db->getOne($user_iszan_sql);
        $comment_num_sql = "select count(*) from ".$GLOBALS['hhs']->table('square_comment')." where order_id = ".$row['order_id']." AND goods_id = ".$goods['goods_id'];
        $comment_num = $db->getOne($comment_num_sql);
		if($goods['is_on_sale'] == 1 && $goods['is_delete'] == 0)
		{
			$arr[$idx]['goods_id']   = $goods['goods_id'];
			$arr[$idx]['goods_name']   = $goods['goods_name'];
			$arr[$idx]['goods_number'] = $goods['goods_number'];
			$arr[$idx]['market_price'] = price_format($goods['market_price'],false);
			$arr[$idx]['shop_price']   = price_format($goods['shop_price'],false);
			$arr[$idx]['order_id'] = $row['order_id'];
			$arr[$idx]['goods_thumb'] = get_image_path($goods['goods_id'], $goods['goods_thumb'], true);
			$arr[$idx]['little_img']  = get_image_path($goods['goods_id'], $goods['little_img'], true);
			$arr[$idx]['goods_img']   = get_image_path($goods['goods_id'], $goods['goods_img']);
			$arr[$idx]['url']         = build_uri('goods', array('gid'=>$goods['goods_id']), $goods['goods_name']);
			$arr[$idx]['team_price']  = price_format($goods['team_price'],false);
			$arr[$idx]['team_num']    = $row['team_num'];
			$arr[$idx]['need']    = $row['team_num'] - $row['teammen_num'];
			$arr[$idx]['square']    = $row['square'];
			$arr[$idx]['team_id']    = $row['team_sign'];
			$arr[$idx]['uname']       = $row['uname'];
			$arr[$idx]['headimgurl']  = $row['headimgurl'];
			$arr[$idx]['add_time']    = $row['square_add_time'];
			$arr[$idx]['zan_num'] = $zan_num;
			$arr[$idx]['team_discount']    = @number_format($goods['team_price']/$goods['market_price']*10,1);
			$arr[$idx]['iszan'] = $user_iszan;
			$arr[$idx]['comment_num'] = $comment_num;
			$arr[$idx]['luckdraw_id'] = $row['luckdraw_id'];
			$arr[$idx]['is_boutique'] = $row['is_boutique'];
			$arr[$idx]['buy_nums']    = $db->getOne("select count(*) from ".$hhs->table('order_goods')." where goods_id = '".$goods['goods_id']."'");
			$arr[$idx]['uid']=$user_id;
	
			$arr[$idx]['gallery']   = $db->getAll("select thumb_url from ".$hhs->table('goods_gallery')." where goods_id = '".$goods['goods_id']."' limit 3");
	  	}
    }

    return $arr;
}
/**
 * 查询朋友圈
 * @author houwei <277096656@qq.com>
 */
function get_moments(){
	$sql = "select * from ".$GLOBALS['hhs']->table('moments')." order by add_time desc";
	$arr = $GLOBALS['db']->getAll($sql);
	
	foreach ($arr as $k =>$v){
		$arr[$k]['moments_id'] = $v['id'];
		$arr[$k]['add_time'] = $v['add_time'];
		$arr[$k]['uname'] = get_uinfo($v['user_id'],'uname');
		$arr[$k]['headimgurl'] = get_uinfo($v['user_id'],'headimgurl');
		$arr[$k]['comment'] = get_comment_moments($v['id']);
		$arr[$k]['zan_num'] = $v['zan_num'];
		$arr[$k]['comment_num'] = $v['comment_num'];
		$arr[$k]['img_01'] = $v['img_01']?$v['img_01']:'';
		$arr[$k]['img_02'] = $v['img_02']?$v['img_02']:'';
		$arr[$k]['img_03'] = $v['img_03']?$v['img_03']:'';
	}
	
	return $arr;
}
/**
 * 查询朋友圈评论
 * @author houwei <277096656@qq.com>
 */
function get_comment_moments($id){
	$comment_sql = "select * from ".$GLOBALS['hhs']->table('square_moments_comment')." where moments_id = ".$id;
	$comment = $GLOBALS['db']->getAll($comment_sql);
	foreach ($comment as $k => $v){
		$comment[$k]['uname'] = get_uinfo($v['comment_user_id'],'uname');
		
	}
	return $comment;
}
/**
 * 
 * 查询公告
 */
function get_announcement(){
	$user_id= $_SESSION['user_id'];
	$sql = "select * from ".$GLOBALS['hhs']->table('announcement')." where is_display = 1 order by add_time desc limit  3";
	$arr = $GLOBALS['db']->getAll($sql);
	
	foreach ($arr as $k =>$v){
		$iszan_sql = "select id from ".$GLOBALS['hhs']->table('announcement')." where zan_user_id = ".$user_id." AND id = ".$v['id'];
		$iszan = $GLOBALS['db']->getOne($iszan_sql);
		$arr[$k]['iszan'] = $iszan;
		$arr[$k]['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
	}
	return $arr;
}

/**
 *生成随机名称 
 * 
 */
function random($length)
{
	$hash = 'HHS-';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++)
	{
	$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
/**
 * 
 * 压缩图片
 * @param $i 压缩比例
 */
function ResizeImage($uploadfile,$i,$name)
{
	//取得当前图片大小
	$width = imagesx($uploadfile);
	$height = imagesy($uploadfile);
	//生成缩略图的大小
	
		$newwidth = $width * $i;
		$newheight = $height * $i;
		if(function_exists("imagecopyresampled"))
		{
			$uploaddir_resize = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($uploaddir_resize, $uploadfile, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		}
		else
		{
			$uploaddir_resize = imagecreate($newwidth, $newheight);
			imagecopyresized($uploaddir_resize, $uploadfile, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		}
		 
		ImageJpeg ($uploaddir_resize,$name);
		ImageDestroy ($uploaddir_resize);

}
function format_date($time){
    $t=time()-$time;
    $f=array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.'前';
        }
    }
}

 
?>
