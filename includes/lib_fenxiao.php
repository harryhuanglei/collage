<?php
/**
 * 处理分销的所有事物
 */
/**
 * 设置用户的上级*
 * @param  [int] $user_id
 * @return [array]
 */
function setUserPids($user_id,$uid_1,$uid_2,$uid_3)
{
	$uid_1 = $uid_1 ? $uid_1 : 0;
	$uid_2 = $uid_2 ? $uid_2 : 0;
	$uid_3 = $uid_3 ? $uid_3 : 0;
	$sql = "UPDATE ".$GLOBALS['hhs']->table('users')." SET  `uid_1` = '".$uid_1."',`uid_2` = '".$uid_2."',`uid_3` = '".$uid_3."' WHERE `user_id` = '" . $user_id . "'"; 
	return $GLOBALS['db']->query($sql);
}
/**
 * 获取用户的上级*
 * @param  [int] $user_id
 * @return [array]
 */
function getUserPids($user_id)
{
	$sql = "SELECT u.`uid_1`,u.`uid_2`,u.`uid_3`,u1.`openid` as 'openid_1',u2.`openid` as 'openid_2',u3.`openid` as 'openid_3',u1.`uname` as 'uname_1',u2.`uname` as 'uname_2',u3.`uname` as 'uname_3' FROM ".$GLOBALS['hhs']->table('users')." as u 
		   left join ".$GLOBALS['hhs']->table('users')." as u1 on u.`uid_1` = u1.`user_id`
		   left join ".$GLOBALS['hhs']->table('users')." as u2 on u.`uid_2` = u2.`user_id`
		   left join ".$GLOBALS['hhs']->table('users')." as u3 on u.`uid_3` = u3.`user_id`
			 WHERE u.`user_id` = '" . $user_id . "'"; 
	return $GLOBALS['db']->getRow($sql);
}
/**
 * 获取商家分成比例*
 * @param  [int] $suppliers_id
 * @return [array]
 */
function getSupplierRate($suppliers_id)
{
	if(empty($suppliers_id))
	{
		return array(
			'percentage' => $GLOBALS['_CFG']['percentage'], 
			'rate_1' => $GLOBALS['_CFG']['rate_1'], 
			'rate_2' => $GLOBALS['_CFG']['rate_2'], 
			'rate_3' => $GLOBALS['_CFG']['rate_3'], 
		);
	}
	$sql = "SELECT `percentage`,`rate_1`,`rate_2`,`rate_3` FROM ".$GLOBALS['hhs']->table('suppliers')." WHERE `suppliers_id` = " . $suppliers_id;
	return $GLOBALS['db']->getRow($sql);
}

/**
 * 计算分成金额
 * @param  [float] $rate   [分成比例]
 * @param  [float] $amount [总金额]
 * @return [float]         [分成金额]
 */
function calcMoney($rate,$amount)
{
	return number_format($rate * $amount / 100,2,'.','');
}

/**
 * 获取下级
 * @param  [int]  $user_id 
 * @param  [int]  $level   
 * @param  integer $page    
 * @return [array]           
 */
function getFollows($user_id,$level = 1,$page = 1){
	$pageSize = 20;
	$skip = ($page - 1) * $pageSize;
	$sql = "SELECT `user_id`,`uname` as 'user_name',`headimgurl`  
			FROM ".$GLOBALS['hhs']->table('users').
			" WHERE `uid_".$level."` = '" . $user_id . "'" . 
			" limit " . $skip . ",".$pageSize ;
	return $GLOBALS['db']->getAll($sql);
}
/**
 * 获取下级人数
 * @param  [int]  $user_id 
 * @param  integer $level   
 * @return [int]           
 */
function getFollowsNum($user_id,$level = 1){
	$sql = "SELECT count(*)  
			FROM ".$GLOBALS['hhs']->table('users').
			" WHERE `uid_".$level."` = '" . $user_id . "'"; 
	return $GLOBALS['db']->getOne($sql);
}

/**
 * 获取分销列表
 * @param  [int]  $user_id 
 * @param  integer $page    
 * @return [array]           
 */
function getMoneyList($user_id,$page = 1,$level,$checked,$uid = 0)
{
	$pageSize = 10;
	$skip = ($page - 1) * $pageSize;
	$andwhere = $level ? " AND `level` = '$level'" :'';
	switch ($checked) {
		case '1'://已经结算
			$andwhere .= " AND `update_at` > 0";
			# code...
			break;
		case '2'://无效或未结算
			$andwhere .= " AND `update_at` = 0";
			# code...
			break;		
		default://全部
			# code...
			break;
	}
	if ($uid > 0) {
		$andwhere .= " AND o.`user_id` = '".$uid."' ";
	}
	$sql = "SELECT u.`user_id`,u.`uname` as 'user_name',u.`headimgurl`,
			f.`order_id`,f.`level`,f.`amount`,f.`money`,f.`create_at`,f.`update_at`,
			o.`order_sn`  
			FROM ".$GLOBALS['hhs']->table('fenxiao'). " as f,
			".$GLOBALS['hhs']->table('order_info'). " as o,
			".$GLOBALS['hhs']->table('users'). " as u".
			" WHERE f.`user_id` = '" . $user_id . "'" . 
			" AND f.`order_id` = o.`order_id` " . 
			" AND o.`user_id` = u.`user_id` " . $andwhere . 
			" limit " . $skip . ",".$pageSize ;
	$rows = $GLOBALS['db']->getAll($sql);
	//处理一下时间
	foreach ($rows as $key => $row) {
		$rows[$key]['create_at'] = local_date($GLOBALS['_CFG']['date_format'], $row['create_at']);
		//这个可能为0，判断一下
		if($row['update_at'])
			$rows[$key]['update_at'] = local_date($GLOBALS['_CFG']['date_format'], $row['update_at']);
		unset($row);
	}
	return $rows;
}

/**
 * 获取某人业绩
 * @param  [int] $user_id
 * @param  [int] $level 几级分销
 * @return [float]
 */
function getMoneyListCount($user_id,$level = false,$checked,$uid = 0){
	$andwhere = $level ? " AND `level` = '$level'" :'';
	switch ($checked) {
		case '1'://已经结算
			$andwhere .= " AND `update_at` > 0";
			# code...
			break;
		case '2'://无效或未结算
			$andwhere .= " AND `update_at` = 0";
			# code...
			break;		
		default://全部
			# code...
			break;
	}	
	if ($uid > 0) {
		$andwhere .= " AND o.`user_id` = '".$uid."' ";
	}
	//$sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('fenxiao'). " WHERE user_id = '$user_id'" . $andwhere;
	$sql = "SELECT COUNT(*)  
			FROM ".$GLOBALS['hhs']->table('fenxiao'). " as f,
			".$GLOBALS['hhs']->table('order_info'). " as o,
			".$GLOBALS['hhs']->table('users'). " as u".
			" WHERE f.`user_id` = '" . $user_id . "'" . 
			" AND f.`order_id` = o.`order_id` " . 
			" AND o.`user_id` = u.`user_id` " . $andwhere ;
	return $GLOBALS['db']->getOne($sql);
}
/**
 * 分销提成总金额
 * @param  [int] $user_id 
 * @param  [int] $checked,1 or 0 
 * @return [float]          
 */
function getMoneyAmount($user_id,$checked = '')
{
	switch ($checked) {
		case '1'://已经结算
			$andwhere = " AND `update_at` > 0";
			# code...
			break;
		case '2'://无效或未结算
			$andwhere = " AND `update_at` = 0";
			# code...
			break;		
		default://全部
			$andwhere = "";
			# code...
			break;
	}
	$sql = "SELECT sum(`money`) FROM ".$GLOBALS['hhs']->table('fenxiao')." WHERE `user_id` = '" . $user_id . "'" . $andwhere;
	return $GLOBALS['db']->getOne($sql);
}
/**
 * 插入分销信息
 * @param  [int] $order_id  
 * @param  [int] $user_id   
 * @param  [int] $level     
 * @param  [float] $amount    
 * @param  [float] $rate      
 * @param  [float] $money     
 * @param  [int] $create_at 
 * @return [booleanint]            
 */
function insertMoney($order_id,$user_id,$level,$amount,$rate,$money,$create_at)
{
	$sql = "insert into ".$GLOBALS['hhs']->table('fenxiao')."(`order_id`,`user_id`,`level`,`amount`,`rate`,`money`,`create_at`) 
		values 
		('".$order_id."','".$user_id."','".$level."','".$amount."','".$rate."','".$money."','".$create_at."') ";
	return $GLOBALS['db']->query($sql);
}
/**
 * 更新分成,
 * @param  [int] $order_id  
 * @param  [int] $update_at 
 * @return [boolean]            
 */
function updateMoney($order_id,$update_at)
{
	//查找 行
	$sql = "SELECT `user_id`,`money`,`level` FROM ".$GLOBALS['hhs']->table('fenxiao')." WHERE `order_id` = " . $order_id . " limit 3"; 
	$rows = $GLOBALS['db']->getAll($sql);
	foreach ($rows as $key => $row) {
		log_account_change($row['user_id'], $row['money'], 0, 0, 0, $row['level'].'级分销收入', 1);
		unset($row);
	}
	//更新分销状态
	$sql = "update ".$GLOBALS['hhs']->table('fenxiao')." set `update_at` = '".$update_at."' WHERE `order_id` = " . $order_id; 
	return $GLOBALS['db']->query($sql);
}

/**
 * 检查是否有购买记录
 * 新人专区，没有购买过任何产品
 * @param  [int] $user_id
 * @return [boolean]
 */
function checkNotBuyBefore($user_id){
	$sql = "SELECT `order_id` FROM ".$GLOBALS['hhs']->table('order_info')." WHERE `user_id` = " . $user_id . ""; 
	return $GLOBALS['db']->getOne($sql);
}

/**
 * 获取来自谁的分销【一般是一级】
 * @param  [int] $user_id
 * @return [array]
 */
function getPidInfo($user_id){
	$sql = "SELECT `uname` as 'user_name',`headimgurl`,`mobile_phone` FROM ".$GLOBALS['hhs']->table('users')." WHERE `user_id` = '" . $user_id . "'"; 
	return $GLOBALS['db']->getRow($sql);
}

/**
 * 插入订单分销相关
 * @param  [type] $order_id [description]
 * @return [type]           [description]
 */
function doFenxiao($order)
{
	global $hhs,$db;
	$rates = getFen($order['order_id']);
	$users = getUserPids($order['user_id']);
	$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
	$amount = 0;
	foreach ($rates as $key => $money) {
		
		if($users['uid_'.$key] > 0 && $money > 0){
			$amount +=$money;
			$money = number_format($money,2,'.','');
            insertMoney($order['order_id'],$users['uid_'.$key],$key,$order['goods_amount'],0,$money,$order['add_time']);

            $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;
            $weixin->send_wxmsg($users['openid_'.$key], '捷报' , 'user.php?act=fenxiao' , $description );
		}
	}
	$db->query('update '.$hhs->table('order_info').' set fenxiao_money='.$amount.' WHERE order_id = '.$order['order_id']);
	# 获取允许分销的订单商品
	// $sql = "SELECT sum(o.goods_number*o.goods_price) as goods_amount FROM ".$hhs->table('goods')." as g,".$hhs->table('order_goods')." as o WHERE g.goods_id = o.goods_id AND g.allow_fenxiao = 1 AND o.order_id = " . $order['order_id'];
	// $goods = $db->getRow($sql);

	// if ($goods) {
 //        $users = getUserPids($order['user_id']);
 //        $rates = getSupplierRate($order['suppliers_id']);
 //        $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

 //        if($users['uid_1'] > 0 && $rates['rate_1'] > 0){
 //            $money = calcMoney($rates['rate_1'],$goods['goods_amount']);
 //            if($money > 0.00)
 //            {
 //                insertMoney($order['order_id'],$users['uid_1'],1,$goods['goods_amount'],$rates['rate_1'],$money,$order['add_time']);

 //                $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;
 //                $weixin->send_wxmsg($users['openid_1'], '捷报' , 'user.php?act=fenxiao' , $description );
 //            }
 //        }
 //        if($users['uid_2'] > 0 && $rates['rate_2'] > 0){
 //            $money = calcMoney($rates['rate_2'],$goods['goods_amount']);
 //            if($money > 0.00){
 //                insertMoney($order['order_id'],$users['uid_2'],2,$goods['goods_amount'],$rates['rate_2'],$money,$order['add_time']);

 //                $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;
 //                $weixin->send_wxmsg($users['openid_2'], '捷报' , 'user.php?act=fenxiao' , $description );
 //            }
 //        }
 //        if($users['uid_3'] > 0 && $rates['rate_3'] > 0){
 //            $money = calcMoney($rates['rate_3'],$goods['goods_amount']);
 //            if($money > 0.00){
 //                insertMoney($order['order_id'],$users['uid_3'],3,$goods['goods_amount'],$rates['rate_3'],$money,$order['add_time']);

 //                $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;
 //                $weixin->send_wxmsg($users['openid_3'], '捷报' , 'user.php?act=fenxiao' , $description );
 //            }
 //        }
	// }
}

/**
 * 获取分销金额
 * @param  [type] $order_id [description]
 * @return [type]           [description]
 */
function getFen($order_id)
{
	global $db,$hhs,$_CFG;

	$money = array();
	$money[1] = $db->getOne('SELECT sum(`rate_1`) as rate FROM '.$hhs->table('order_goods').' WHERE order_id='.$order_id);
	$money[2] = $db->getOne('SELECT sum(`rate_2`) as rate FROM '.$hhs->table('order_goods').' WHERE order_id='.$order_id);
	$money[3] = $db->getOne('SELECT sum(`rate_3`) as rate FROM '.$hhs->table('order_goods').' WHERE order_id='.$order_id);
	return $money;
}