<?php

define('IN_HHS', true);
//团购列表

if($action =='team_order')

{	

	$smarty->assign('status_list', $_LANG['cs']);   // 订单状态

   	$smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);

    $smarty->assign('cs_await_pay',     CS_AWAIT_PAY);

   	$smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);

    $teammen_list=get_teammen_list(true,$action);  

	$smarty->assign('pager', $teammen_list['pager']);

	$smarty->assign('order_list',$teammen_list['orders']);

	$smarty->assign('filter',$teammen_list['filter']);

	$smarty->assign('action',$action);

	$smarty->display("supp_team_order.dwt");

}

else if($action =='teammen_list')

{
	/* 根据团标志查询团信息 */

    if (isset($_REQUEST['team_sign']))

    {
        $team_sign = intval($_REQUEST['team_sign']);

    }
    else

    {

        /* 如果参数不存在，退出 */

        die('invalid parameter');

    }
	
    /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */

    $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";

    $agency_id = $db->getOne($sql);

    if ($agency_id > 0)

    {

        if ($order['agency_id'] != $agency_id)

        {

            sys_msg($_LANG['priv_error']);

        }

    }
	
	$teammen_list = get_team_sign_list($team_sign,$action);
	//获得团信息
	
	$sql ="SELECT o.team_num,o.teammen_num,(o.team_num-o.teammen_num) as team_lack_num, o.team_status,o.team_sign,o.team_first,o.extension_code, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid,o.pay_status, o.consignee, o.address, o.email, o.tel,  o.extension_id, " .


            "(" . order_amount_field('o.') . ") AS total_fee FROM " . $GLOBALS['hhs']->table('order_info') . " AS o WHERE o.team_first = 1 and o.team_sign = ".$team_sign;
	
	$teammen_info = $db->getRow($sql);
	
	 if($teammen_info['team_sign'] )
	 {

            $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$teammen_info['team_sign'];

            $team_start_time=$GLOBALS['db']->getOne($sql);

            if($team_start_time)
			{

                $teammen_info['team_start_date'] = local_date('Y-m-d H:i:s', $team_start_time);

                $teammen_info['team_end_date'] = local_date('Y-m-d H:i:s', $team_start_time+$GLOBALS['_CFG']['team_suc_time']*24*3600);

            }

    }
	
	$smarty->assign('teammen_info',$teammen_info);
	
	$sql = "select goods_name,shop_price,team_num,team_price from ". $GLOBALS['hhs']->table('goods') ." where goods_id = ".$teammen_info['extension_id'];
	
	$goods_info = $db->getRow($sql);
	
	$smarty->assign('goods_info',$goods_info);
	
	$smarty->assign('pager', $teammen_list['pager']);

	$smarty->assign('teammen_list',$teammen_list['orders']);

	$smarty->assign('filter',$teammen_list['filter']);

	$smarty->assign('action',$action);

	$smarty->display("supp_team_order.dwt");


}



/*------------------------------------------------------ */

//-- 操作订单状态（载入页面）

/*------------------------------------------------------ */



elseif ($action == 'team_operate')

{

		if (isset($_REQUEST['team_sign']))

		{
			$team_sign = intval($_REQUEST['team_sign']);
	
		}
		else
	
		{
	
			/* 如果参数不存在，退出 */
	
			die('invalid parameter');
	
		}
	
        /* 查询：如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */

        $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";

        $agency_id = $db->getOne($sql);

        if ($agency_id > 0)

        {

            if ($order['agency_id'] != $agency_id)

            {

                sys_msg($_LANG['priv_error'], 0);

            }

        }
	
	//获取图案下的订单
	
	$sql ="SELECT order_id,order_sn,user_id,order_status,shipping_status,pay_status,consignee,money_paid,(" . order_amount_field() . ") AS total_fee,team_status,team_first,team_sign,team_num,teammen_num FROM " . $GLOBALS['hhs']->table('order_info') . " where team_sign = ".$team_sign;

	$team_order_list = $db->getAll($sql);
	
	/*指定团标志支付的订单的个数*/
	
	$sql="select count(*) from ".$GLOBALS['hhs']->table('order_info') ." where team_sign=".$team_sign." and team_status = 1 and pay_status = 2";
	/*立即成团时团数量重新修正，舞动舞动   死道友不死频道*/
    $team_num=$db->getOne($sql);

    foreach($team_order_list as $k => $v)
	{
		
		if($v['pay_status'] == 2)
		{
		
			$sql="update ".$hhs->table('order_info')." set team_status=2, team_num='".$team_num."',teammen_num='".$team_num."' where order_id=".$v['order_id'];
			
			$user_id=$v['user_id'];
        
			$wxch_order_name='teammem_suc';
			
			$team_sign=$v['team_sign'];
			
			include_once(ROOT_PATH . 'wxch_order.php');
		
		}elseif($v['pay_status'] < 2)
		{
			$sql="update ".$hhs->table('order_info')." set order_status=2, shipping_status=0,pay_status=0 where order_id=".$v['order_id'];
		}elseif($v['pay_status'] > 2)
		{
			$sql="update ".$hhs->table('order_info')." set team_status=2 where order_id=".$v['order_id'];
		}
			
		$db->query($sql);
	
	}
	
	show_message('设置成功','返回列表','index.php?op=team_order&act=team_order');

}


function trimall($str)

{

    $qian=array(" ","　","\t","\n","\r");

    $hou=array("","","","","");

    return str_replace($qian,$hou,$str); 

}

?>