<?php
/***

               ／￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣￣
               |　
               ＼＿　 ＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿
        .--.     (  )
       /    \   ( )
      ## a  a  .
      (   '._)
       |'-- |
     _.\___/_   ___________NO BUGS_________________
   ."\> \Y/|<'.  '._.-'
  /  \ \_\/ /  '-' /
  | --'\_/|/ |   _/
  |___.-' |  |`'`
    |     |  |
    |    / './
   /__./` | |
      \   | |
       \  | |
       ;  | |
       /  | |
      |___\_.\_
      `-"--'---'
/**
 * 获取订单类型数量
 * @param  string $type [description]
 * @return [type]       [description]
 */
function getOrderTypeNums($start = 0,$end = 0, $type='payed')
{
	global $db,$hhs;
	$types = array('finished','await_ship','await_pay','unconfirmed','unprocessed','unpay_unship',
'shipped','payed','refund');
	if(! in_array($type, $types))
		return 0;

	if ($start && $end) {
		#
		$where = ' and add_time > "'.$start.'" and add_time < "'.$end.'"';
	}
	else
	{
		$where = '';
	}

	$sql = "SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE 1 " .$where.order_query_sql($type);

	return $db->getOne($sql);
}
/**
 * 获取商品数量
 * @return [type] [description]
 */
function getGoodsNums($where = '')
{
	global $db,$hhs;

	$sql = "SELECT COUNT(*) FROM " .$hhs->table('goods') .' where 1'. $where;
	return $db->getOne($sql);	
}

/**
 * 获取团购
 * @return [type] [description]
 */
function getGroupNums($start = 0,$end = 0, $team_status = 0)
{
	global $db,$hhs;

	$where = $team_status > 0 ? ' AND `team_status` = "'.$team_status.'" ' : '';

	if ($start && $end) {
		#
		$where .= ' and add_time > "'.$start.'" and add_time < "'.$end.'"';
	}
    $sql = 'SELECT COUNT(*) FROM ' .$hhs->table('order_info'). ' WHERE team_first = 1 ' . $where;
	return $db->getOne($sql);	
}

/**
 * 营销总额
 * @return [type] [description]
 */
function getOrderAmount($start = 0,$end = 0)
{
	global $db,$hhs;

	if ($start && $end) {
		#
		$where = ' and add_time > "'.$start.'" and add_time < "'.$end.'"';
	}
	else
	{
		$where = '';
	}

    $sql = 'SELECT sum(money_paid) FROM ' .$hhs->table('order_info'). ' WHERE 1 ' . $where . order_query_sql();

	return $db->getOne($sql);	
}

/**
 * 用户
 * @return [type] [description]
 */
function getUserNums($start = 0,$end = 0)
{
	global $db,$hhs;
	// $yesterday = date("Y-m-d",gmtime()-86400);
	// $start = strtotime($yestoday);
	// $end = $start+86400;
	if ($start && $end) {
		#
		$where = ' AND reg_time > ' . $start .' and reg_time < ' . $end;
	}
	else
	{
		$where = '';
	}
    $sql = 'SELECT COUNT(*) FROM ' .$hhs->table('users'). ' WHERE 1 ' . $where;
	return $db->getOne($sql);	
}

/**
 * 获取销售排行
 * @return [type] [description]
 */
 
function getGoodsOrders(){
	global $db,$hhs;
	$sql = 'SELECT 
		  g.goods_id,g.goods_sn,g.goods_name,sum(g.send_number) as send_nummber
		FROM
		  ' .$hhs->table('order_goods'). ' AS g JOIN '.$hhs->table('order_info').' AS i 
		on g.order_id = i.order_id WHERE i.order_status = 5 and i.shipping_status = 2 and i.pay_status = 2 group by g.goods_sn  
		ORDER BY  send_nummber DESC 
		LIMIT 25';
		/*$sql = 'SELECT 
		  g.goods_name,
		  g.goods_id,
		  sum(o.send_number) as send_number
		FROM
		  ' .$hhs->table('goods'). ' AS g,'.$hhs->table('order_goods').' AS o
		WHERE g.goods_sn = o.goods_sn group by o.goods_sn  
		ORDER BY  send_number DESC 
		LIMIT 25';*/
   /* $sql = 'SELECT 
		  g.goods_name,
		  g.goods_id,
		  g.sales_num as nums
		FROM
		  ' .$hhs->table('goods'). ' AS g
		WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 
		ORDER BY nums DESC,
		  g.`goods_id` ASC 
		LIMIT 25';*/
	return $db->getAll($sql);	
}


/**
 * 获取会员排行
 * @return [type] [description]
 */
function getUserOrders(){
	return array();
	// global $db,$hhs;
 //    $sql = 'SELECT 
	// 		  u.`user_id`,
	// 		  u.uname,
	// 		  (SELECT 
	// 		    COUNT(*) 
	// 		  FROM
	// 		    ' .$hhs->table('order_info'). ' AS oo 
	// 		  WHERE u.user_id = oo.user_id '.order_query_sql('finished', 'oo.').') AS nums,
	// 		  (SELECT 
	// 		    SUM(money_paid) 
	// 		  FROM
	// 		    ' .$hhs->table('order_info'). ' AS oo 
	// 		  WHERE u.user_id = oo.user_id '.order_query_sql('finished', 'oo.').') AS amount 
	// 		FROM
	// 		  ' .$hhs->table('order_info'). ' AS o,
	// 		  ' .$hhs->table('users'). ' AS u 
	// 		WHERE o.user_id = u.user_id '.order_query_sql('finished', 'o.').'
	// 		GROUP BY u.`user_id` 
	// 		ORDER BY nums DESC,
	// 		  amount DESC,
	// 		  u.`user_id` ASC 
	// 		LIMIT 20';
	// return $db->getAll($sql);	
}

/**
 * 月订单状态
 * @param  [type]  $start   [description]
 * @param  [type]  $end     [description]
 * @param  boolean $is_team [description]
 * @return [type]           [description]
 */
function getMonthStats($start ,$end, $is_team = false, $type='payed', $team_status = 0)
{
	global $db,$hhs;

	$where = $is_team ? ' and team_first = 1' : '';
	$where .= order_query_sql($type);
	$where .= $team_status > 0 ? ' AND `team_status` = "'.$team_status.'" ' : '';

    $sql = 'SELECT COUNT(*) FROM ' .$hhs->table('order_info'). ' WHERE pay_status = 2 and add_time >= ' . $start .' and add_time <= ' . $end . $where;
	return $db->getOne($sql);	
}

/**
 * 获取当年12个月数据
 * @return [type] [description]
 */
function getYearStats(){
	$time    = gmtime();
	$date    = getdate($time);
	$year    = $date['year'];
	$mon     = $date['mon'];
	$monData = array_fill(1,12,array(
		'order_payed'   =>0,
		'order_unpayed' =>0,
		'team'          =>0,
		'team_success'  =>0,
		'team_failed'   =>0,
	));

	for ($i=1; $i <= $mon; $i++) { 
		$mktime = mktime(0,0,0,$i,1,$year);
		$start  = strtotime(date("Y-m-d 00:00:00",$mktime));
		$end    = strtotime(date("Y-m-t 23:59:59",$mktime));
		$monData[$i]['order_payed']   = getMonthStats($start,$end);
		$monData[$i]['order_unpayed'] = getMonthStats($start,$end,false,'await_pay');
		$monData[$i]['team']          = getMonthStats($start,$end,true);
		$monData[$i]['team_success']  = getMonthStats($start,$end,true,'payed',2);		
		$monData[$i]['team_failed']   = getMonthStats($start,$end,true,'refund',4);		
	}
	return $monData;
}

function getFullMonStats()
{
	$time  = gmtime();
	$date  = getdate($time);
	$year  = $date['year'];
	$mon   = $date['mon'];
	$today = date("d",$time);
	$last  = date("t",$time);
	$data  = array_fill(1,$last,array(
		'order_payed'   =>0,
		'order_unpayed' =>0,
		'team'          =>0,
		'team_success'  =>0,
		'team_failed'   =>0,
	));	
	$max = 0;
	for ($i=1; $i <= $today; $i++) { 
		$mktime = mktime(0,0,0,$mon,$i,$year);
		$start  = strtotime(date("Y-m-d 00:00:00",$mktime));
		$end    = strtotime(date("Y-m-d 23:59:59",$mktime));
		$data[$i]['order_payed']   = getMonthStats($start,$end);
		$data[$i]['order_unpayed'] = getMonthStats($start,$end,false,'await_pay');
		$data[$i]['team']          = getMonthStats($start,$end,true);
		$data[$i]['team_success']  = getMonthStats($start,$end,true,'payed',2);		
		$data[$i]['team_failed']   = getMonthStats($start,$end,true,'refund',4);	
		$max = max($data[$i]['order_payed'],$max);	
	}
	$yaxis = array_fill(0, 4, $max);
	$per = ceil($max/5);
	for ($i=0; $i < 4; $i++) { 
		$yaxis[$i] = $i*$per;
	}
	return array(
		'yaxis' => $yaxis,
		'data' => $data,
	);
}

/**
 * 获取分销状态
 * @return [type] [description]
 */
function getFenxiaoStatus()
{
	$time  = gmtime();

	$start = local_strtotime(date("Y-m-1",time()));
	$end   = $time;
	return array(
		'all_money' => getFenxiaoAmount(0,$end),
		'all_allow' => getFenxiaoAmount(0,$end,true),
		'mon_money' => getFenxiaoAmount($start,$end),
		'mon_allow' => getFenxiaoAmount($start,$end,true),
	);
}

function getFenxiaoAmount($start,$end,$type=false)
{
	global $db,$hhs;
	$sql = 'SELECT sum(`money`) as amount FROM '.$hhs->table('fenxiao').' where `create_at`>"'.$start.'" AND  `create_at`<"'.$end.'"';
	if ($type) {
		$sql .= ' AND `update_at`>"'.$start.'" AND  `update_at`<"'.$end.'"';
	}
	return $db->getOne($sql);
}