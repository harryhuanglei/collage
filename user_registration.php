<?php

/**
 * HHSHOP 会员签到
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.hhshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: article.php 17217 2011-01-19 06:29:08Z liubo $
*/
define('IN_HHS', true);
define('HHS_ADMIN', true);
require(dirname(__FILE__) . '/includes/init.php');

//会员ID
$user_id  = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

//type类型
$act = $_REQUEST['act'];

if(empty($act))
{
   hhs_header("Location: index.php\n");
   exit;
}


//签到
if($act == 'registration')
{
	include('includes/cls_json.php');
	$json   = new JSON;
	$res    = array('error' => '', 'pay_points' => '' ,'system_integral' => 0);
	
	if($user_id < 1)
	{
		$res['error'] = 4;
		die($json->encode($res));
	}
	else
	{
		$user_info = $db->getRow("select * from ". $hhs->table('users') . "where user_id = ".$user_id);
		
		//系统设置签到积分
		$system_integral = $GLOBALS['_CFG']['qiandao_integral'];
		
		$today     = local_date("Ymd",gmtime());
		$last_time = local_date("Ymd",$user_info['registration_time']);
	 
		//判断最后一次签到的时间是否和当前时间相同
		if($today == $last_time)
		{
			$res['error'] = 2;
			die($json->encode($res));
		}
		else
		{
			$change_desc = "会员签到 增加".$system_integral." 积分";
			
			log_account_change($user_id, 0, 0, 0, $system_integral, $change_desc);
			//记录签到时间
			$db->query("update " . $hhs->table('users') . " set registration_time = '".gmtime()."' where user_id = $user_id ");

			$res['error'] = 1;
			$res['qiandao_integral'] = $system_integral;
			$pay_points = $db->getOne("select pay_points from ". $hhs->table('users') . "where user_id = ".$user_id);
			$res['pay_points'] = $pay_points.$GLOBALS['_CFG']['integral_name'];
			die($json->encode($res));
			
		}
	
	}
	

			
}



?>