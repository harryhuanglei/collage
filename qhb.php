<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

if ($action == 'getresttimes') {
	/*include_once('includes/cls_json.php');
	$json = new JSON();*/
	$result = array('error' => 1,'message'=>'', 'content' => 0);
	$luck = getLuck();
	$today = time();
	if($luck){
		$times = getJoinTimes($luck['id']);
		$rest = $luck['limit_times'] - $times;
		if($rest){
			$cur_date = intval($luck['start_at']- $today);
			$cur_end_date = intval($luck['end_at']- $today);
			if($cur_date > 0)
			{
				$result['error']   = 1;
				$result['message'] = '新的活动正在紧张筹备，请留意！';
			}elseif($cur_end_date < 0)
			{
				$result['error']   = 1;
				$result['message'] = '活动已过期！下次请起早！';//
			}
			else
			{
				$result['error']   = 0;
				$result['rest'] = $rest;
			}
		}
		else
		{
			$result['error']   = 1;
			$result['content'] = 0;
			$result['rest'] = 0;
			
			
			
			$result['message'] = '您的摇一摇次数使用完，请下次再来！';
		}
	}
	else{
		$result['error']   = 1;
		$result['message'] = '活动已过期！下次请起早！';
	}
	ob_end_clean();
	die(json_encode($result));
}
if ($action == 'getluck') {
	include_once('includes/cls_json.php');
	$json = new JSON();

	$result = array('error' => 1,'message'=>'','rest' => 0, 'content' => 0);
	$luck = getLuck();
	if($luck){
		$times = getJoinTimes($luck['id']);
		$rest = $luck['limit_times'] - $times;
		if($rest > 0){
			setJoinTimes($luck['id']);

			$money = getLuckMoney($luck['id'],$luck['name']);
			$result['error']   = 0;
			$result['content'] = $money ? floatval($money) : 0;
			$result['rest']    = ($rest - 1);
		}
		else
		{
			$result['message'] = '剩余次数不足';
		}
	}
	else{
			$result['message'] = '来晚啦！';
	}
	ob_end_clean();
	die($json->encode($result));
}


/* 缓存编号 */
$cache_id = 'qhb' . '-' . $_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));

if (!$smarty->is_cached('qhb.dwt', $cache_id))
{
	$sql="select headimgurl from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
	$user_info=$db->getRow($sql);
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', $user_info['headimgurl'] );
	$smarty->assign('title', $_CFG['hb_share_title']);
	$smarty->assign('desc', mb_substr($_CFG['hb_share_dec'], 0,30,'utf-8')  );
	/*
	$smarty->assign('title', 'aaa'.$_CFG['index_share_title']);
	$smarty->assign('desc', mb_substr('bbbb'.$_CFG['index_share_dec'], 0,30,'utf-8')  );
	*/
	$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'].'?uid='.$uid;
	$smarty->assign('link', $link);
	$smarty->assign('link2', urlencode($link) );

	$is_subscribe = $db->getOne("SELECT `is_subscribe` FROM ".$hhs->table('users')." WHERE `user_id` ='".$_SESSION['user_id']."'");
	
	$smarty->assign('subscribe_url', $_CFG['subscribe_url']);
	$smarty->assign('is_subscribe', $is_subscribe);
    $smarty->assign('page_title',      '抢红包');    // 页面标题
	$smarty->display('qhb.dwt');
}
//获取参与活动次数
function getJoinTimes($luck_id){
	return $GLOBALS['db']->getOne("SELECT count(*) FROM ".$GLOBALS['hhs']->table('luck_join')." where `luck_id` = '".$luck_id."' AND `user_id` = " . $_SESSION['user_id']);
}
//参与次数+1
function setJoinTimes($luck_id){
	return $GLOBALS['db']->query("INSERT INTO ".$GLOBALS['hhs']->table('luck_join')." set `luck_id` = '".$luck_id."' , `user_id` = " . $_SESSION['user_id']);
}
//获取最近一期活动
function getLuck(){
	return $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['hhs']->table('luck')." ORDER BY `id` DESC");
}

//参与活动，获取红包
function getLuckMoney($luck_id,$luck_name){
	$luck = rand(1,3);
	//设置了一个概率，30%;
	if($luck%3 !=1)
		return false;
	//获取一行
	$logs = $GLOBALS['db']->getRow("SELECT `id`,`money` FROM ".$GLOBALS['hhs']->table('luck_logs')." where `luck_id` = '".$luck_id."' AND `user_id` is null order by rand() ");
	if(! $logs)
		return false;
	//更新行
	$res = $GLOBALS['db']->query("update ".$GLOBALS['hhs']->table('luck_logs')." SET `user_id` = " . $_SESSION['user_id'] ." where `luck_id` = '".$luck_id."' and `user_id` is null and `id` = " . $logs['id']);
	if($res){
		//更新成功,发放红包
		sendLuckMoney($logs['money'],$luck_name);
		return $logs['money'];
	}
	return false;
}

// 发放红包现金
function sendLuckMoney($amount,$act_name){
	global $db,$hhs;
    include_once(ROOT_PATH . 'wxpay/wx_hongbao.php');

	$total_amount = (100) * $amount;
	$min_value    = $total_amount;
	$max_value    = $total_amount;
	$re_openid    = $db->getOne("SELECT `openid` FROM ".$hhs->table('users')." WHERE `user_id` = '".$_SESSION['user_id']."' ");
	
	$hongbao = new hongbao();
	return $hongbao->send($re_openid,$total_amount,$min_value,$max_value,1,$act_name);
}
?>
