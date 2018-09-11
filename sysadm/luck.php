<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . '/includes/lib_order.php');

/**
 * 抽奖
 */
if($_REQUEST['act'] == 'luck_start'){
	
	
		//$team_sign = $_REQUEST['team_sign'];
		
		$luck_id = $_REQUEST['luck_id'];
		
		//$team_sign_str = implode(',', $team_sign);
	
	$luck_status_sql = "select luck_status from ".$GLOBALS['hhs']->table('luckdraw')." where id = ".$luck_id;
	$luck_status = $GLOBALS['db']->getOne($luck_status_sql);
	
	//是否已经抽奖 1
	if($luck_status == 1){
		$sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." AND is_luckdraw = 1";
		$row =$GLOBALS['db']->getAll($sql);
		
		foreach ($row as $k=> $v){
			$order_id[] = $v['order_id'];
		}
		$prize_order_str = implode(',', $order_id);
	}else{
		//先给没有完成的订单退款
		//$doing_team_sign_sql = "select order_id  from ".$GLOBALS['hhs']->table('order_info')." where team_sign in (".$team_sign_str.") AND team_status = 1";
		$doing_team_sign_sql  = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." and team_status = 1";
		$doing_team_sign = $GLOBALS['db']->getAll($doing_team_sign_sql);
		
		if($doing_team_sign)
		{
			require_once(ROOT_PATH . 'includes/lib_payment.php');
			require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
			$get_luckdraw_info = get_luckdraw_info($luck_id);
			foreach($doing_team_sign as $key => $value)
			{
				$order = order_info($value['order_id']);
				$arr['order_status']    = OS_RETURNED;
				$arr['pay_status']  = PS_REFUNDED;
				$arr['shipping_status'] = 0;
				$arr['money_paid']  = 0;
				$arr['team_status']  = 3;
				$arr['order_amount']= $order['money_paid'];
				$order['shipping_status'] = 0;
				$refund_money =$order['money_paid']*100;
				$refund_surplus = 0;
				$refund_bonus = 0;
				$r=refund($order['order_sn'],$refund_money,$refund_surplus,$refund_bonus);
				if($r)
				{
				update_order($order['order_id'], $arr);
					/* 记录log */
					$action_note = '活动束结未成团退款';
					order_action($order['order_sn'], 4, $order['shipping_status'], 3, $action_note);
					if($order['transaction_id'])
					{
						$user_id=$order['user_id'];
						$order_id=$order['order_id'];
						$wxch_order_name='refund';
						include_once(ROOT_PATH . 'wxch_order.php');
					}
					$money = $refund_money/100;//提示消息退款金额
					$appid = $weixin_config_rows['appid'];
					$appsecret =$weixin_config_rows['appsecret'];
					$openid = get_opend_id($order['user_id']);
					$title  = '非常抱歉，'.$get_luckdraw_info['title'].'活动已结束！';
					$url    = 'user.php?act=order_list';
					$desc  = "商品名称:".$get_luckdraw_info['goods_name']."\r\n退款金额:".$money."元\r\n您的团未成功！！！！";
					$weixin = new class_weixin($appid,$appsecret);
					$weixin->send_wxmsg($openid, $title , $url , $desc );
				}
			}
		}
		//获取该抽奖活动下的所有已完成团购，接收页面传的团购编号数组，查询表(order_info)将已完成(team_status = 2)的筛选出来
		//	$complete_team_sign_sql = "select order_id  from ".$GLOBALS['hhs']->table('order_info')." where team_sign in (".$team_sign_str.") AND team_status = 2";
			$complete_team_sign_sql = "select order_id  from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." AND team_status = 2";
			$complete_team_sign = $GLOBALS['db']->getAll($complete_team_sign_sql);
			if($complete_team_sign == null){
				$links[] = array('text' =>'抽奖活动列表', 'href' => 'luckdraw.php?act=view&snatch_id=' . $luck_id);
				sys_msg('没有团成功的订单！抽奖无效！', 1, $links);
			}
		
			foreach ($complete_team_sign as $k=> $v){
					$order_id_arr[] = $v['order_id'];
			}
			//查询该抽奖活动下已完成的团购下用户
			$order_id_str = implode(',', $order_id_arr);
			$complete_team_sign_user_sql = "select distinct(user_id) from ".$GLOBALS['hhs']->table('order_info')." where order_id in (".$order_id_str.")";
			$complete_team_sign_user = $GLOBALS['db']->getAll($complete_team_sign_user_sql);
		
			foreach ($complete_team_sign_user as $k=> $v){
					$complete_team_sign_user_arr[] = $v['user_id'];
			}
			//获取设置的中奖数量
			$stock_num_sql="select stock_num from " . $GLOBALS['hhs']->table('luckdraw') ." where id=".$luck_id;
			$stock_num=$db->getOne($stock_num_sql);
			//随机抽取用户
			if(sizeof($complete_team_sign_user_arr) > $stock_num){
				$luck_user_arr_key = array_rand($complete_team_sign_user_arr,$stock_num);
			}else{
				$luck_user_arr_key = array_keys($complete_team_sign_user_arr);
			}
			if($stock_num == 1){
		 			//只有一个订单
		 			//获取该活动下该用户的所有订单
		 			$luck_user_id = $complete_team_sign_user_arr[$luck_user_arr_key];
		 			$get_luckdraw_user_order_sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where user_id = ".$luck_user_id." AND luckdraw_id = ".$luck_id." AND team_status = 2";	
					$get_luckdraw_user_order = $GLOBALS['db']->getAll($get_luckdraw_user_order_sql);
					
					foreach ($get_luckdraw_user_order as $k=> $v){
						$luckdraw_user_order_arr[] = $v['order_id'];
					}
					//抽取中奖订单
					$luck_order_key = array_rand($luckdraw_user_order_arr,1);
					$prize_order_str = $luckdraw_user_order_arr[$luck_order_key];
			}else{
					//所有中奖的用户
					foreach ($complete_team_sign_user_arr as $k=>$v){
							foreach ($luck_user_arr_key as $key=>$val){
								 if($k == $val){
								 		$luckdraw_user_arr[] = $v;
								 }
							}					
					}
					foreach ($luckdraw_user_arr as $item => $value){
						$get_luckdraw_user_order_sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where user_id = ".$value." AND luckdraw_id = ".$luck_id." AND team_status = 2";
						$get_luckdraw_user_order = $GLOBALS['db']->getAll($get_luckdraw_user_order_sql);
						
						foreach ($get_luckdraw_user_order as $k=> $v){
								$luckdraw_user_order_arr[] = $v['order_id'];
						}
						//抽取中奖订单
						$luck_order_key = array_rand($luckdraw_user_order_arr,1);
						$prize_order_arr[] = $luckdraw_user_order_arr[$luck_order_key];
						unset($luckdraw_user_order_arr);
					}
					$prize_order_str = implode(',', $prize_order_arr);
			}
			//更新订单表，中奖  luckdraw_id     is_luckdraw
			$update_luck_order_sql = "UPDATE ".$GLOBALS['hhs']->table('order_info')." SET  is_luckdraw = 1 where order_id in ( ".$prize_order_str.")";
			$db->query($update_luck_order_sql);
			//更新抽奖活动表，抽奖结束
			$update_luckdraw_sql = "UPDATE ".$GLOBALS['hhs']->table('luckdraw')." SET luck_status = 1 where id = ".$luck_id;
			$db->query($update_luckdraw_sql);
	}
	//查询订单
	$prize_order_list = get_prize_order_list($prize_order_str);
	foreach ($prize_order_list as $key => $val){
		$prize_order_list[$key]['add_time'] = local_date('Y-m-d H:i:s',$val['add_time']);
	}
	//查看是否已经发奖
	$get_give_log_sql = "select * from ".$GLOBALS['hhs']->table('give_prize_log')." where luckdraw_id = ".$luck_id;
	$get_give_log = $GLOBALS['db']->getRow($get_give_log_sql);
	if($get_give_log){
		//如果已经发奖，为避免各种原因导致的退款不完全，在此处查询该活动下没有团款的订单
		$select_luckdraw_no_refund_order_sql = "select count(*) from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." and order_status = 1 and pay_status = 2 and is_luckdraw = 0";		
		$luckdraw_no_refund_order = $GLOBALS['db']->getOne($select_luckdraw_no_refund_order_sql);
		if($luckdraw_no_refund_order){
			$smarty->assign('luckdraw_no_refund',$luckdraw_no_refund_order);
		}
		
		$smarty->assign('is_give',1);
	}
	
	
	//总数
	//$prize_order_list['filter']['prize_order_str'] = $prize_order_str;
	$smarty->assign('prize_order_list',$prize_order_list);
	$smarty->assign('full_page',        1);
	$smarty->assign('action_link2', array('href' => 'luck.php?act=download&prize_order_str='.$prize_order_str, 'text' => "导出"));
	// $smarty->assign('filter',    $prize_order_list['filter']);
	// $smarty->assign('record_count', $prize_order_list['record_count']);
	// $smarty->assign('page_count',   $prize_order_list['page_count']);
	$smarty->assign('luck_id',$luck_id);
	//$smarty->assign('all_team_sign',$team_sign_str);
	$smarty->display('luckdraw_order_list.htm');
}

/**
 *  发奖
 */
if($_REQUEST['act'] == 'give_prize'){
	
	$luck_id = $_POST['luck_id'];
	//$all_team_sign = $_POST['all_team_sign'];
	//$prize_order_arr = $_POST['order_id'];

	$prize_order_sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." and is_luckdraw = 1 ";
	$prize_order = $GLOBALS['db']->getAll($prize_order_sql);
	foreach ($prize_order as $k=> $v){
				$prize_order_arr[] = $v['order_id'];
		}

	$sql = "select team_sign from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." and team_status = 2 and is_luckdraw = 0";
	$team_sign_arr = $GLOBALS['db']->getAll($sql);

	foreach ($team_sign_arr as $k=> $v){
				$team_sign[] = $v['team_sign'];
		}
	$all_team_sign = implode(',', $team_sign);
	//获取中奖名单
	$prize_order_str = implode(',', $prize_order_arr);
	$prize_order_list = get_prize_order_list($prize_order_str);
	
	//获取活动信息
	$luckdraw_info = get_luckdraw_info($luck_id);
	
	//给中奖的用户发中奖通知
	foreach ($prize_order_list as $k => $v){
		$appid = $weixin_config_rows['appid'];
		$appsecret =$weixin_config_rows['appsecret'];
		$openid = get_opend_id($v['user_id']);
		$title  = "恭喜你，".$luckdraw_info['title']."中奖！";
		$url    = 'user.php?act=luckdraw';
		$desc  = "商品名称:".$luckdraw_info['goods_name']."\r\n您的团购订单中奖，稍后将为您发货！";
		$weixin = new class_weixin($appid,$appsecret);
		$weixin->send_wxmsg($openid, $title , $url , $desc );
	}
	//获取该活动参与抽奖的订单
	$luck_no_lucked_list = get_luck_no_lucked_list($luck_id,$all_team_sign);
	//给没中奖的订单退款	
	require_once(ROOT_PATH . 'includes/lib_payment.php');
	require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
	//d($luck_no_lucked_list);
	foreach ($luck_no_lucked_list as  $v){
	
		$order = order_info($v);
		
		$arr['order_status']    = OS_RETURNED;
		
		$arr['pay_status']  = PS_REFUNDED;
		
		$arr['shipping_status'] = 0;
		
		$arr['money_paid']  = 0;
		
		$arr['order_amount']= $order['money_paid'];
		
		$arr['is_luckdraw']  = 2;//没中奖
		
		$order['shipping_status'] = 0;
		
		$refund_money =$order['money_paid']*100;
		
		$refund_surplus = 0;
		
		$refund_bonus = 0;
		
		$r=refund($order['order_sn'],$refund_money,$refund_surplus,$refund_bonus);
		
		if($r){
			update_order($order['order_id'], $arr);
			/* 记录log */
			$action_note = '未中奖退款';
			order_action($order['order_sn'], 4, $order['shipping_status'], 3, $action_note);
			
			if($order['transaction_id']){
				$user_id=$order['user_id'];
				
				$order_id=$order['order_id'];
				
				$wxch_order_name='refund';
					
				include_once(ROOT_PATH . 'wxch_order.php');
				
			}
			$money = $refund_money/100;//提示消息退款金额
			$appid = $weixin_config_rows['appid'];
			$appsecret =$weixin_config_rows['appsecret'];
			$openid = get_opend_id($order['user_id']);
			$title  = "非常抱歉，".$luckdraw_info['title']."没有中奖！";
			$url    = 'user.php?act=luckdraw';
			$desc  = "商品名称:".$luckdraw_info['goods_name']."\r\n退款金额".$money."元";
			$weixin = new class_weixin($appid,$appsecret);
			$weixin->send_wxmsg($openid, $title , $url , $desc );
		}else{
			continue;
			// $links[] = array('text' =>'抽奖活动列表', 'href' => 'luck.php?act=luck_start&luck_id=' . $luck_id);
			// sys_msg('退款失败', 1, $links);
		}
	}
	$give_log_sql = "INSERT INTO ".$GLOBALS['hhs']->table('give_prize_log')."(`add_time`,`luckdraw_id`) VALUES(".gmtime().",".$luck_id.") ";
	$db->query($give_log_sql);
	 $links[] = array('text' =>'抽奖活动列表', 'href' => 'luck.php?act=luck_start&luck_id=' . $luck_id);
	sys_msg('发奖完成，未中奖订单已退款', 1, $links);
			
}
/**
 *  继续退款
 * 
 */
elseif ($_REQUEST['act'] == 'luckdraw_refund'){
	$luck_id = $_REQUEST['luckdraw'];
	$select_luckdraw_no_refund_order_sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id." and order_status = 1 and pay_status = 2 and is_luckdraw = 0";
	$luckdraw_no_refund_order = $GLOBALS['db']->getAll($select_luckdraw_no_refund_order_sql);
	$luckdraw_info = get_luckdraw_info($luck_id);
	require_once(ROOT_PATH . 'includes/lib_payment.php');
	require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
	foreach ($luckdraw_no_refund_order as $k => $v){
		$order = order_info($v['order_id']);
		$arr['order_status']    = OS_RETURNED;
		$arr['pay_status']  = PS_REFUNDED;
		$arr['shipping_status'] = 0;
		$arr['money_paid']  = 0;
		$arr['order_amount']= $order['money_paid'];
		$arr['is_luckdraw']  = 2;//没中奖
		$order['shipping_status'] = 0;
		$refund_money =$order['money_paid']*100;
		$refund_surplus = 0;
		$refund_bonus = 0;
		$r=refund($order['order_sn'],$refund_money,$refund_surplus,$refund_bonus);
		if($r){
			update_order($order['order_id'], $arr);
			/* 记录log */
			$action_note = '未中奖退款';
			order_action($order['order_sn'], 4, $order['shipping_status'], 3, $action_note);
			if($order['transaction_id']){
				$user_id=$order['user_id'];
				$order_id=$order['order_id'];
				$wxch_order_name='refund';
				include_once(ROOT_PATH . 'wxch_order.php');
			}
			$money = $refund_money/100;//提示消息退款金额
			$appid = $weixin_config_rows['appid'];
			$appsecret =$weixin_config_rows['appsecret'];
			$openid = get_opend_id($order['user_id']);
			$title  = "非常抱歉，".$luckdraw_info['title']."没有中奖！";
			$url    = 'user.php?act=luckdraw';
			$desc  = "商品名称:".$luckdraw_info['goods_name']."\r\n退款金额".$money."元";
			$weixin = new class_weixin($appid,$appsecret);
			$weixin->send_wxmsg($openid, $title , $url , $desc );
		}
	}
	$links[] = array('text' =>'抽奖活动列表', 'href' => 'luck.php?act=luck_start&luck_id=' . $luck_id);
	sys_msg('操作完成', 1, $links);
}
/**
 * 导出中奖订单
 * 
 */
elseif ($_REQUEST['act'] == 'download')
{

	$prize_order_str = $_GET['prize_order_str'];
	$order_list = get_prize_order_list($prize_order_str);
	
	$filename='中奖订单信息.csv';

	header("Content-type:text/csv");

	header("Content-Disposition:attachment;filename=".$filename);

	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');

	header('Expires:0');

	header('Pragma:public');

	/* 订单概况 */
	$data = '订单ID' . ",";
	$data .= "订单价格,";
	$data .= "团购ID,";
	$data .= "订单编号,";
	$data .= "是否是团长,";
	$data .= "支付时间,";
	$data .= "商品编号,";
	$data .= "商品名称,";
	$data .= "购买数量,";
	$data .= "商品属性,";
	$data .= "团购开始时间,";
	$data .= "省,";
	$data .= "市,";
	$data .= "区县,";
	$data .= "地址,";
	$data .= "电话,";
	$data .= "收货人姓名,";
	$data .= "订单时间,";
	$data .= "快递编号,";
	$data .= "快递名称,";
	$data .= "快递单号,";
	$data .= "微信单号,";
	$data .= "支付金额,";
	//$data .= "使用余额,";
	$data .= "使用红包,\n";
	$i=1;

	if(!empty($order_list)){

		foreach($order_list as $k=>$v){

					$sql="select g.*,og.goods_number,og.goods_attr,og.goods_price   from ".$hhs->table('order_goods')." as og left join ".

					$hhs->table('goods')." as g on og.goods_id=g.goods_id where og.order_id=".$v['order_id'];

					$goods_all=$db->getAll($sql);

					$goods=$goods_all[0];

					$sql="select region_name from ".$hhs->table('region')." where region_id=".$v['province'];

					$province=$db->getOne($sql);

					$sql="select region_name from ".$hhs->table('region')." where region_id=".$v['city'];

					$city=$db->getOne($sql);

					$sql="select region_name from ".$hhs->table('region')." where region_id=".$v['district'];

					$district=$db->getOne($sql);
					
					$v['team_first'] = $v['team_first'] == 1 ? '是' : '-';
					
					$data .= $v['order_id']. ",";

					$data .= $v['total_fee'].",";

					$data .= $v['team_sign'].",";

					$data .= $v['order_sn']."\t,";

					$data .= $v['team_first'].",";

					$data .= local_date('Y-m-d H:i:s', $v['pay_time']).",";

					$data .= $goods['goods_sn'].",";

					$data .= $goods['goods_name'].",";

					$data .= intval($goods['goods_number']).',';

					$data .= str_replace(array("\r\n","\n","\r"), '', trim($goods['goods_attr'])).",";

					$data .= local_date('Y-m-d H:i:s', $v['pay_time']).",";

					$data .= $province.",";

					$data .= $city.",";

					$data .= $district.",";

					$data .= $v['address'].",";

					$data .= $v['mobile']."\t,";

					$data .= $v['consignee'].",";

					$data .= local_date('Y-m-d H:i:s', $v['add_time']).",";

					$data .= $v['shipping_id'].",";

					$data .= $v['shipping_name'].",";
				
					$data .= $v['invoice_no']."\t,";
					
					$data .= $v['transaction_id']."\t,";

					$data .= floatval($v['money_paid']).",";

			//		$data .= floatval($v['surplus']).",";

					$data .= floatval($v['bonus']).",\n";
		}
	}

	echo hhs_iconv(EC_CHARSET, 'GB2312', $data) . "\n";

	exit;

}

/**
 * 获取用户openid
 * 
 */
function get_opend_id($user_id){
	
	$sql = "select openid from ".$GLOBALS['hhs']->table('users')." where user_id = ".$user_id;
	$row = $GLOBALS['db']->getOne($sql);
	return $row;
	
}

/**
 * 
 * 获取所有成功并参与抽奖但没有中奖的订单
 * @param $luck_id        int       活动id
 * @param $team_sign   array   拼团id 
 */
function get_luck_no_lucked_list($luck_id,$team_sign_str){
	
	$complete_team_no_lucked_sql = "select order_id  from ".$GLOBALS['hhs']->table('order_info')." where team_sign in (".$team_sign_str.") AND team_status = 2 AND is_luckdraw = 0";
	$complete_team_no_lucked = $GLOBALS['db']->getAll($complete_team_no_lucked_sql);
	foreach ($complete_team_no_lucked as $k=> $v){
		foreach ($v as $key => $val){
			unset($key);
			$order_id[] = $val;
		}
	}
	return $order_id;
}

/**
 *   获取中奖订单列表
 *  @param $order_id_arr 中奖订单id string
 */
function get_prize_order_list($prize_order_str){
	
	// $prize_order_count_sql = "select count(*) from ".$GLOBALS['hhs']->table('goods')." as g INNER JOIN ".$GLOBALS['hhs']->table('order_info')." as o where o.extension_id = g.goods_id AND o.order_id in (".$prize_order_str.")";
	// $filter['record_count'] = $GLOBALS['db']->getOne($prize_order_count_sql);
	// $filter = page_and_size($filter);
	
	// $prize_order_list_sql = "select g.goods_name,o.invoice_no,o.consignee,o.transaction_id,o.money_paid,o.surplus,o.bonus,o.point_id,o.shipping_id,o.shipping_name,o.team_sign,o.address,o.mobile,o.add_time,o.team_first,g.goods_sn,g.goods_name,o.pay_time,o.order_sn,o.province,o.city,o.district,u.uname,o.order_id,o.consignee, o.add_time ,u.headimgurl,o.user_id, " . order_amount_field('o.') . " AS total_fee from ".$GLOBALS['hhs']->table('goods')." as g INNER JOIN ".$GLOBALS['hhs']->table('order_info')." as o  INNER JOIN ".$GLOBALS['hhs']->table('users'). " as u where u.user_id  = o.user_id AND o.extension_id = g.goods_id AND o.order_id in (".$prize_order_str.")  "
	// 		." LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]"; 
	// $prize_order_list = $GLOBALS['db']->getAll($prize_order_list_sql);
	
	$prize_order_list_sql = "select g.goods_name,o.invoice_no,o.consignee,o.transaction_id,o.money_paid,o.surplus,o.bonus,o.point_id,o.shipping_id,o.shipping_name,o.team_sign,o.address,o.mobile,o.add_time,o.team_first,g.goods_sn,g.goods_name,o.pay_time,o.order_sn,o.province,o.city,o.district,u.uname,o.order_id,o.consignee, o.add_time ,u.headimgurl,o.user_id, " . order_amount_field('o.') . " AS total_fee from ".$GLOBALS['hhs']->table('goods')." as g INNER JOIN ".$GLOBALS['hhs']->table('order_info')." as o  INNER JOIN ".$GLOBALS['hhs']->table('users'). " as u where u.user_id  = o.user_id AND o.extension_id = g.goods_id AND o.order_id in (".$prize_order_str.")";
			
	$prize_order_list = $GLOBALS['db']->getAll($prize_order_list_sql);

	//$arr = array('prize_order_list' => $prize_order_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $prize_order_list;
}

function d($arr){
	echo '<pre>';
	var_dump($arr);
	die;
}

/*------------------------------------------------------ */

//-- 翻页、排序

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'query')

{

	check_authz_json('luck_manage');
	$prize_order_str = $_REQUEST['prize_order_str'];
	$list = get_prize_order_list($prize_order_str);
	$list['filter']['prize_order_str'] = $prize_order_str;
	$smarty->assign('prize_order_list',    $list['prize_order_list']);
	
	$smarty->assign('filter',       $list['filter']);

	$smarty->assign('record_count', $list['record_count']);

	$smarty->assign('page_count',   $list['page_count']);

	$sort_flag  = sort_flag($list['filter']);

	$smarty->assign($sort_flag['tag'], $sort_flag['img']);

	make_json_result($smarty->fetch('luckdraw_order_list.htm'), '',

	array('filter' => $list['filter'], 'page_count' => $list['page_count']));

}
function get_luckdraw_info($luck_id)
{

	$luck_id = intval($luck_id);
	//查找活动信息
	$get_luckdraw_info_sql = "select l.title,g.goods_name  from ".$GLOBALS['hhs']->table('luckdraw')." as l left join ". $GLOBALS['hhs']->table('goods') . " AS g ON g.goods_id = l.goods_id where l.id =".$luck_id;
		
	return $GLOBALS['db']->getRow($get_luckdraw_info_sql);

}