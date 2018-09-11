<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');



if ((DEBUG_MODE & 2) != 2)

{

    $smarty->caching = false;

}

$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';



if ($action == 'getquan') {

	include_once('includes/cls_json.php');

	$json = new JSON();

	$result = array('error' => 1,'message'=>'您已经领取过了', 'content' => '');

	

	$bid  = isset($_REQUEST['bid']) ? intval($_REQUEST['bid']) : 0;

	if(! checkQuan($bid)){

		$res = getQuan($bid);

		$result['error']   = $res ? 0 : 1;

		$result['message'] = $res ? '' : '领取失败';

		$result['content'] = $res ? '领取成功' : '';

	}

	ob_end_clean();

	die($json->encode($result));

}



/* 缓存编号 */

$cache_id = 'yhq' . '-' . $_SESSION['user_rank'].'-'.$_CFG['lang'];

$cache_id = sprintf('%X', crc32($cache_id));



if (!$smarty->is_cached('yhq.dwt', $cache_id))
{
    $smarty->assign('page_title',      '优惠券');    // 页面标题
    $smarty->assign('quan_list',      quanList());
	



	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');
	$smarty->assign('title', $_CFG['shop_name'].'邀您来领优惠券');
	$smarty->assign('desc', mb_substr($_CFG['index_share_dec'], 0,30,'utf-8')  );


	$smarty->display('yhq.dwt');
}



function quanList(){

	global $db,$hhs;



	$sql = "select b.`type_id`,b.`type_name`,b.`type_money`,b.`min_goods_amount`,b.`use_start_date`,b.`use_end_date`,b.`suppliers_id`,s.`suppliers_name`

	 		from ".$hhs->table('bonus_type')." as b 

	 		left join ".$hhs->table('suppliers')."  as s ON s.`suppliers_id` = b.`suppliers_id`

	 		where b.`is_online` = 1 and b.`send_end_date` > " . time() ;

	$bonus_lists = $db->getAll($sql);
	
	foreach ($bonus_lists as $key => $row) {
		
		$bonus_id = $db->getOne("select `bonus_id` from ".$hhs->table('user_bonus')." where `bonus_type_id` = '" .$row['type_id']. "' and `user_id` = 0 limit 1");
		
		if($bonus_id)
		{
			$rows[$key]['type_id'] = $row['type_id'];
			
			$rows[$key]['type_name'] = $row['type_name'];
			
			$rows[$key]['type_money'] = $row['type_money'];
			
			$rows[$key]['min_goods_amount'] = $row['min_goods_amount'];
			
			$rows[$key]['use_start_date'] = date("Y-m-d",$row['use_start_date']);

			$rows[$key]['use_end_date']   = date("Y-m-d",$row['use_end_date']);

			$rows[$key]['stamp']   = rand(1,4);
			
			
			$rows[$key]['suppliers_name'] = $row['suppliers_name'];

			if(empty($row['suppliers_name']))

			$rows[$key]['suppliers_name']   = '自营店';	
		}
	}

	return $rows;

}



function checkQuan($bid){

	if(!$bid)

		return true;



	global $db,$hhs;

	$sql = "select `bonus_id` from ".$hhs->table('user_bonus')."  where `bonus_type_id` = '" .$bid. "' and `user_id` = '".$_SESSION['user_id']."'";

	return $db->getOne($sql);

}



function getQuan($bid){

	if(!$bid)

		return false;

	global $db,$hhs;



	$bonus_id = $db->getOne("select `bonus_id` from ".$hhs->table('user_bonus')." where `bonus_type_id` = '" .$bid. "' and `user_id` = 0 ORDER BY RAND() limit 1");

	if (!$bonus_id) {

		return false;

	}

	$sql = "update ".$hhs->table('user_bonus')." set `user_id` = '".$_SESSION['user_id']."' where `user_id` = 0 and `bonus_type_id` = '" .$bid. "' and `bonus_id` = '".$bonus_id."'";



	return $db->query($sql);

}

?>

