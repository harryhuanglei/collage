<?php
define('IN_HHS', true);
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$user_id = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : '';
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';


if($user_id=='')
{
	$results['content'] = '请先登录';
	$results['error'] =1;
	echo $json->encode($results);
	die();
}
if ($act == 'default'){
	include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_image.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	$team_sign = isset($_REQUEST['team_sign']) ? intval($_REQUEST['team_sign']) : 0;
	$sql="select * from ".$hhs->table('users')." where user_id=".$user_id;
	$user_info=$db->getRow($sql);
	//$is_team是否可以参团 $is_teammen是否在团里
	
	$is_team=0;
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	if(empty($team_info['pay_time'])){
		$results['content'] = '未支付跳转到首页';
		$results['error'] =1;
		echo $json->encode($results);
		die();
	}
	
    
    if($team_info['pay_time'] && ($team_info['pay_time']+$_CFG['team_suc_time']*24*3600<gmtime())&&$team_info['team_status']==1 && $team_info['is_luck'] == 0 && $team_info['is_miao'] == 0){
        //处理退款
        do_team_refund($team_sign);
        $team_info['team_status'] = 0;
    }
    
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_first=1 " ;
	$team_info=$db->getRow($sql);
	
	$results['team_info'] =$team_info;//团详情
	
	
	if($team_info['team_status']!=1){
	    $is_team=0;
	}
	/*
	$sql=" SELECT * FROM ".$hhs->table('goods')." where goods_id=".$team_info['extension_id'] ;
	$team_good=$db->getRow($sql);
	$smarty->assign('team_num', $team_good['team_num']);	//几人团
	*/

	$results['team_num'] =$team_info['team_num'];//几人团
	
	$sql=" SELECT count(*) FROM ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_status>0" ;
	$count=$db->getOne($sql);
	//还差多少人
	$d_num=$team_info['team_num']-$count;
	
	$db_num=$team_info['team_num']-$team_info['teammen_num'];
	
	$d_num_arr=array();
	for($i=0;$i<$d_num;$i++){
		$d_num_arr[]=$i;
	}
	
	$results['d_num'] = $d_num;
	
	$results['d_num_arr'] = $d_num_arr;
	
	//用户是否参团
	$sql=" SELECT * FROM ".$hhs->table('order_info')." where pay_status >1 and team_sign=".$team_sign ." and user_id=".$user_id;
	$row=$db->getRow($sql);
	if(empty($row)){
	    $results['is_team'] =1;
	}else{
	    $is_team=0;
	    $results['is_team'] =0;
	    if($row['pay_status']==0&&$row['team_status']==0&&($row['order_status']==0||$row['order_status']==1)&&$team_info['team_status']==1){
			$results['order_id'] =$row['order_id'];
			$results['content'] = '您已经参团，等待您的支付';
			$results['error'] =2;
			echo $json->encode($results);
			die();
	    } 
	}
	
	$results['order'] =$order;
	

    $goods_info = goods_info($row['goods_id']);
	//$results['goods_info'] =$goods_info;
	
	//print_r($goods_info);
	
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    
   
    /*
    $sql = "SELECT *  FROM " . $GLOBALS['hhs']->table('order_info') .
            " WHERE team_sign = '$team_sign' and team_first=1 ";
    $order=$db->getRow($sql);
    if ($order === false)
    {
        exit;
    }*/
    
    /* 订单商品 */
    $goods_list = order_goods($team_sign);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
		$goods_id = $goods_list[$key]['goods_id'];
    }
	$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性

	
	$results['properties'] =$properties['pro'];

   

    //参团的人
    $sql="select u.user_name,u.uname,u.uname,u.headimgurl,o.pay_time,o.team_first,o.is_lucker from ".$hhs->table('order_info')." as o left join ".$hhs->table('users')." as u on o.user_id=u.user_id where team_sign=".$team_sign." and team_status>0 order by order_id ";
    $team_mem=$db->getAll($sql);
    foreach($team_mem as $k=>$v){
        $team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
    }
    $team_start=$team_mem[0]['pay_time'];
	
	$results['team_start'] = $team_start;//拼团开始时间
	$results['systime']  = gmtime();
  	$results['team_suc_time'] =$_CFG['team_suc_time'];
    
	$results['team_mem'] =$team_mem;
	
   
    //$smarty->assign('order',      $order);
   
    $results['goods_list'] = $goods_list;
    
  
   // $smarty->assign('imgUrl', $user_info['headimgurl']);


	$results['share_title'] =  "【还差".$d_num."个人】我参加了“".$goods_list[0]['goods_name']."”拼单";
	
	$results['share_desc'] =  mb_substr($_CFG['group_share_dec'], 0,30,'utf-8');

	$results['error'] =0;
	echo $json->encode($results);
	die();

}