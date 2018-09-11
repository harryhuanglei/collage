<?php

/**
 * 小舍电商 商品详情
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: goods.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;


    $smarty->assign('id',           $goods_id);
    $smarty->assign('type',         0);
    $smarty->assign('cfg',          $_CFG);

    if ($goods === false)
    {
        /* 如果没有找到任何记录则跳回到首页 */
        hhs_header("Location: ./\n");
        exit;
    }
    else
    {

        $smarty->assign('goods',              $goods);
        $smarty->assign('goods_id',           $goods_id);
        $smarty->assign('page_title',   $goods['goods_name']); 
    }
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'ajax')
{
	
    include('includes/cls_json.php');
    
    $last = $_POST['last'];
	$amount = $_POST['amount'];
	$limit = "limit $last,$amount";
    $json   = new JSON;
	$commentlist = get_comment($goods_id, $limit);

    foreach($commentlist as $val){
    	$smarty->assign('item', $val);
		$res[]['comments_list']  = $GLOBALS['smarty']->fetch('library/comments_list_ajax.lbi');
    }

	
	
    die($json->encode($res));
}

$smarty->assign('now_time',  gmtime());           // 当前系统时间

$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$smarty->assign('appid', $appid);

$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);
$smarty->assign('imgUrl','http://' . $_SERVER['HTTP_HOST'].'/'.$goods['goods_thumb'] );
$smarty->assign('title', $goods['goods_name']);
$smarty->assign('desc', mb_substr($_CFG['goods_share_dec'], 0,30,'utf-8')  );

$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER[REQUEST_URI];

$smarty->assign('link', $link );
$smarty->assign('link2', urlencode($link) );


// comments
//$comments = assign_comment($goods_id,0,1);

//$smarty->assign('comments', $comments['comments'] );



$smarty->display('comments.dwt');


function get_comment($goods_id, $limit='')
{
    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('comment') .
            " WHERE id_value = '$goods_id' AND status = 1 AND parent_id = 0".
            ' ORDER BY comment_id DESC '.$limit;
	
	if(empty($limit)){
    	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	}else{
		$res = $GLOBALS['db']->query($sql);
	}

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
		$arr[$row['comment_id']]['id']       = $row['comment_id'];
        $arr[$row['comment_id']]['email']    = $row['email'];
        $arr[$row['comment_id']]['username'] = $row['user_name'];
        $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$row['comment_id']]['content']  = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
        $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
        $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		
		$comment_re = get_comment_re($row['comment_id']);
		$arr[$row['comment_id']]['re_username']     = $comment_re['user_name'].'回复：';
		$arr[$row['comment_id']]['re_content']     = $comment_re['content'];
		$arr[$row['comment_id']]['headimgurl'] = $GLOBALS['db']->getOne('SELECT headimgurl FROM ' .$GLOBALS['hhs']->table('users').
           " WHERE user_id = '".$row['user_id']."'");
        if($row['is_false'])
        {
            $arr[$row['comment_id']]['headimgurl'] = 'data/headimgurl/'.$GLOBALS['db']->getOne('SELECT headimgurl FROM ' .$GLOBALS['hhs']->table('users').
           " WHERE user_id = '".$row['user_id']."'");
        }  
    }
    return $arr;
}
function get_comment_re($parent_id){
	$sql = 'SELECT content,user_name FROM ' . $GLOBALS['hhs']->table('comment') ." WHERE parent_id = $parent_id";
	return $GLOBALS['db']->GetRow($sql);
}
?>
