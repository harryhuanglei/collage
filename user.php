<?php
/**
 * 小舍电商 会员中心
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: user.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_wxch.php');
require(dirname(__FILE__) . '/includes/lib_fenxiao.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$user_id = $_SESSION['user_id'];
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$back_act='';
// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =
array('login','act_login','weixin_login','register','act_register','act_edit_password','get_password','send_pwd_email','password', 'signin', 'add_tag', 'collect','collect_store', 'return_to_cart', 'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email','clear_history','qpassword_name', 'get_passwd_question', 'check_answer', 'oath' , 'oath_login', 'other_login','get_mobile_code','send_mobile_code');
/* 显示页面的action列表 */
$ui_arr = array('register', 'login', 'weixin_login', 'profile', 'order_list', 'team_list','order_detail','team_detail','edit_consignee', 'address_list', 'set_address', 'edit_address', 'collection_list','collect_store_list','del_collection',
'message_list', 'tag_list', 'get_password', 'reset_password', 'booking_list', 'add_booking', 'account_raply', 'integral_details',
'account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'bonus', 'group_buy', 'group_buy_detail', 'affiliate', 'comment_list','validate_email','track_packages', 'transform_points','qpassword_name', 'get_passwd_question', 'check_answer', 'refund', 'act_refund', 'fenxiao','level','money','moneycheck','lottery_list','pay_lib','luckdraw');
/* 未登录处理 */
if (empty($_SESSION['user_id']))
{
    if (!in_array($action, $not_login_arr))
    {
        if (in_array($action, $ui_arr))
        {
            /* 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳转到相应操作
            if ($action == 'login')
            {
                if (isset($_REQUEST['back_act']))
                {
                    $back_act = trim($_REQUEST['back_act']);
                }
            }
            else
            {}*/
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}
/* 如果是显示页面，对页面进行相应赋值 */
if (in_array($action, $ui_arr))
{
    assign_template();
    // $position = assign_ur_here(0, $_LANG['user_center']);
    $smarty->assign('page_title', $_LANG['user_center']); // 页面标题
    // $smarty->assign('ur_here',    $position['ur_here']);
    $sql = "SELECT value FROM " . $hhs->table('shop_config') . " WHERE id = 419";
    $row = $db->getRow($sql);
    $car_off = $row['value'];
    $smarty->assign('car_off',       $car_off);
    /* 是否显示积分兑换 */
    if (!empty($_CFG['points_rule']) && unserialize($_CFG['points_rule']))
    {
        $smarty->assign('show_transform_points',     1);
    }
    $smarty->assign('data_dir',   DATA_DIR);   // 数据目录
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
}
//用户中心欢迎页
if ($action == 'default')
{
    include_once(ROOT_PATH .'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	//获取订单数量
	$daifukuan = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' and order_status in (0,1,5) and pay_status=0");
	$daifahuo = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' " .order_query_sql('await_ship')." AND point_id = 0 ");
	$daihexiao = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' " .order_query_sql('await_ship')." AND point_id > 0 ");
	$daishouhuo = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' " .order_query_sql('shipped2'));
	$daipingjia = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' " .order_query_sql());
	$smarty->assign('daifukuan',        $daifukuan);
	$smarty->assign('daifahuo',        $daifahuo);
	$smarty->assign('daihexiao',        $daihexiao);
	$smarty->assign('daishouhuo',        $daishouhuo);
	$smarty->assign('daipingjia',        $daipingjia);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	$smarty->assign('surplus_amount', price_format($surplus_amount, false));
	//获取剩余积分
	$points = get_user_points($user_id);
	
	
	
    $user_info = get_profile($user_id);
    $smarty->assign('profile', $user_info);
	
	$sql = "SELECT mobile_phone from ".$hhs->table('users')." WHERE `user_id` = " .$user_id;
    $mobile_phone = $db->getOne($sql);
	$smarty->assign('mobile_phone', $mobile_phone);
	
	/*APP是否下载下载*/
	$smarty->assign('open_app', $_CFG['open_app']);
	$smarty->assign('app_loaddown_url', $_CFG['app_loaddown_url']);
	/*开启手机登陆*/
	
	$smarty->assign('open_mobile', $_CFG['open_mobile']);
	$smarty->assign('points', $points);
    if ($rank = get_rank_info())
    {
        $smarty->assign('rank_name', sprintf($_LANG['your_level'], $rank['rank_name']));
        if (!empty($rank['next_rank_name']))
        {
            $smarty->assign('next_rank_name', sprintf($_LANG['next_level'], $rank['next_rank'] ,$rank['next_rank_name']));
        }
    }
        /**
     * 申请供应商什么的
     */
    $sql = "SELECT `is_check`,`suppliers_id` from ".$hhs->table('suppliers')." WHERE `user_id` = " . $_SESSION['user_id'];
    $row = $db->getRow($sql);
    $smarty->assign('is_check',$row['is_check']);
    $smarty->assign('suppliers_id',$row['suppliers_id']);
    $smarty->assign('info',        get_user_default($user_id));
    $smarty->assign('user_notice', $_CFG['user_notice']);
    $smarty->assign('prompt',      get_user_prompt($user_id));
    $smarty->display('user_clips.dwt');
}
if ($action == 'luckdraw')
{
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $composite_status = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
	$ntime = gmtime();
	if($composite_status =='100')
    {
		$where .= " and l.luck_status = 0 and l.start_time < '$ntime' and '$ntime' < l.end_time ";
    }
    elseif($composite_status =='101')
    {
        $where .= "  and l.luck_status = 0 and '$ntime' > l.end_time";
    }
	elseif($composite_status =='102')
    {
        $where .= " and l.luck_status = 1";
    }
	else
	{
		$where = "";
	}
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). "as o left join ".$hhs->table('luckdraw')." as l on o.luckdraw_id = l.id WHERE o.user_id = '$user_id' and o.pay_status =2 and o.luckdraw_id > 0".$where);
    $pager  = get_pager('user.php', array('act' => $action,'composite_status'=>$_REQUEST['composite_status']), $record_count, $page);
    $orders = get_user_luckdraw($user_id, $pager['size'], $pager['start'],$where);
   /*  echo '<pre>';
    var_dump($orders);die; */
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->assign('composite_status',  $_REQUEST['composite_status']);
	$smarty->display('user_luckdraw.dwt');
}
/*pangbin积分明细修改过的代码*/
if ($action == 'integral_details')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	$smarty->assign('surplus_amount', price_format($surplus_amount, false));
	//获取剩余积分
	$points = get_user_points($user_id);
	$smarty->assign('points', $points);
	function get_my_points($user_id, $num = 10, $start = 0)
	{
		$where .= " where 1 AND (pay_points<>0) and user_id='$user_id'";
		$sql = "SELECT * FROM " . $GLOBALS['hhs']->table('account_log') . $where .
				" ORDER BY log_id DESC";
		$res = $GLOBALS['db']->selectLimit($sql, $num, $start);
		$arr = array();
		while ($row = $GLOBALS['db']->fetchRow($res))
		{
			$row['change_time']   = local_date('Y-m-d H:i:s', $row['change_time']);
			$arr[] = $row;
		}
		return $arr;
	}
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('account_log'). " WHERE user_id = '$user_id' and (pay_points<>0)");
    $pager  = get_pager('user.php', array('act' => $action), $record_count, $page);
	$smarty->assign('pager',  $pager);
    $smarty->assign('my_points', get_my_points($user_id, $pager['size'], $pager['start']));
    $smarty->display('user_account.dwt');
}
/*pangbin积分明细修改过的代码end*/
if($action == 'refund')
{
	$rec_id = $_REQUEST['rec_id'];
	$goods = get_order_goods_info($rec_id);
	if($goods['refund_status']>0)
	{
		die("invalid");
	}
	if(!can_refund($goods['order_id']) )
	{
		die("invalid");
	}
	$refund_reason_arr = array("无理由退货", "质量问题", "与描述不符");
	$options = array();
	foreach($refund_reason_arr as $k=>$v) 
	{
		$options[$v] = $v;
	}
	$smarty->assign('refund_reason_options', $options );
	$smarty->assign('refund_goods', $goods);
	$smarty->display("user_transaction.dwt");
}
if('act_refund' == $action)
{
	$rec_id = $_POST['rec_id'];
	$refund = $_POST;
	unset($refund['rec_id']);
	$refund['refund_pic1'] = (isset($_FILES['refund_pic1']['error']) && $_FILES['refund_pic1']['error'] == 0) || (!isset($_FILES['refund_pic1']['error']) && isset($_FILES['refund_pic1']['tmp_name']) && $_FILES['refund_pic1']['tmp_name'] != 'none')
         ? $_FILES['refund_pic1'] : array();
	$refund['refund_pic2'] = (isset($_FILES['refund_pic2']['error']) && $_FILES['refund_pic2']['error'] == 0) || (!isset($_FILES['refund_pic2']['error']) && isset($_FILES['refund_pic2']['tmp_name']) && $_FILES['refund_pic2']['tmp_name'] != 'none')
         ? $_FILES['refund_pic2'] : array();
	$refund['refund_pic3'] = (isset($_FILES['refund_pic3']['error']) && $_FILES['refund_pic3']['error'] == 0) || (!isset($_FILES['refund_pic3']['error']) && isset($_FILES['refund_pic3']['tmp_name']) && $_FILES['refund_pic3']['tmp_name'] != 'none')
         ? $_FILES['refund_pic3'] : array();
	if(refund_apply_order_goods($refund, $rec_id) )
	{
		show_message("成功申请退款", "订单列表", "user.php?act=order_list");
	}
	else
	{
		$GLOBALS['err']->show("订单列表", 'user.php?act=order_list');
	}
}
/* 显示会员注册界面 */
if ($action == 'register')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $hhs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);
    $smarty->assign('extend_info_list', $extend_info_list);
    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);
    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
//    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}
/* 注册会员的处理 */
elseif ($action == 'act_register')
{
		include_once(ROOT_PATH . 'includes/lib_passport.php');
		$username = isset($_POST['username']) ? trim($_POST['username']) : '';
		$password = isset($_POST['password']) ? trim($_POST['password']) : '';
		$email	= isset($_POST['email']) ? trim($_POST['email']) : '';
		$other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
		$other['qq'] = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
		$other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
		$other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
		$other['mobile_phone'] = isset($_POST['extend_field5']) ? $_POST['extend_field5'] : '';
		$sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
		$passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';
		$back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
		if (m_register($username, $password, $email, $other) !== false)
		{
			/*把新注册用户的扩展信息插入数据库*/
			$sql = 'SELECT id FROM ' . $hhs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
			$fields_arr = $db->getAll($sql);
			$extend_field_str = '';	//生成扩展字段的内容字符串
			foreach ($fields_arr AS $val)
			{
				$extend_field_index = 'extend_field' . $val['id'];
				if(!empty($_POST[$extend_field_index]))
				{
					$temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
					$extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";
				}
			}
			$extend_field_str = substr($extend_field_str, 0, -1);
			if ($extend_field_str)	  //插入注册扩展数据
			{
				$sql = 'INSERT INTO '. $hhs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
				$db->query($sql);
			}
			/* 写入密码提示问题和答案 */
			if (!empty($passwd_answer) && !empty($sel_question))
			{
				$sql = 'UPDATE ' . $hhs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
				$db->query($sql);
			}
			$ucdata = empty($user->ucdata)? "" : $user->ucdata;
			$Loaction = 'index.php';
			hhs_header("Location: $Loaction\n");
		}
}
//  第三方登录接口
elseif($action == 'oath')
{
	$type = empty($_REQUEST['type']) ?  '' : $_REQUEST['type'];
	if($type == "taobao"){
		header("location:includes/website/tb_index.php");exit;
	}
	include_once(ROOT_PATH . 'includes/website/jntoo.php');
	$c = &website($type);
	if($c)
	{
		if (empty($_REQUEST['callblock']))
		{
			if (empty($_REQUEST['callblock']) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
			{
				$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? 'index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
			}
			else
			{
				$back_act = 'index.php';
			}
		}
		else
		{
			$back_act = trim($_REQUEST['callblock']);
		}
		if($back_act[4] != ':') $back_act = $hhs->url().$back_act;
		$open = empty($_REQUEST['open']) ? 0 : intval($_REQUEST['open']);
		$url = $c->login($hhs->url().'user.php?act=oath_login&type='.$type.'&callblock='.urlencode($back_act).'&open='.$open);
		if(!$url)
		{
			show_message( $c->get_error() , '首页', $hhs->url() , 'error');
		}
		header('Location: '.$url);
	}
	else
	{
		show_message('服务器尚未注册该插件！' , '首页',$hhs->url() , 'error');
	}
}
//  处理第三方登录接口
elseif($action == 'oath_login')
{
	$type = empty($_REQUEST['type']) ?  '' : $_REQUEST['type'];
	include_once(ROOT_PATH . 'includes/website/jntoo.php');
	$c = &website($type);
	if($c)
	{
		$access = $c->getAccessToken();
		if(!$access)
		{
			show_message( $c->get_error() , '首页', $hhs->url() , 'error');
		}
		$c->setAccessToken($access);
		$info = $c->getMessage();
		if(!$info)
		{
			show_message($c->get_error() , '首页' , $hhs->url() , 'error' , false);
		}
		if(!$info['user_id'])
			show_message($c->get_error() , '首页' , $hhs->url() , 'error' , false);
		$info_user_id = $type .'_'.$info['user_id']; //  加个标识！！！防止 其他的标识 一样  // 以后的ID 标识 将以这种形式 辨认
		$info['name'] = str_replace("'" , "" , $info['name']); // 过滤掉 逗号 不然出错  很难处理   不想去  搞什么编码的了
		if(!$info['user_id'])
			show_message($c->get_error() , '首页' , $hhs->url() , 'error' , false);
		$sql = 'SELECT user_name,password,aite_id FROM '.$hhs->table('users').' WHERE aite_id = \''.$info_user_id.'\' OR aite_id=\''.$info['user_id'].'\'';
		$count = $db->getRow($sql);
		if(!$count)   // 没有当前数据
		{
			if($user->check_user($info['name']))  // 重名处理
			{
				$info['name'] = $info['name'].'_'.$type.(rand(10000,99999));
			}
			$user_pass = $user->compile_password(array('password'=>$info['user_id']));
			$sql = 'INSERT INTO '.$hhs->table('users').'(user_name , password, aite_id , sex , reg_time , user_rank , is_validated) VALUES '.
					"('$info[name]' , '$user_pass' , '$info_user_id' , '$info[sex]' , '".gmtime()."' , '$info[rank_id]' , '1')" ;
			$db->query($sql);
		}
		else
		{
			$sql = '';
			if($count['aite_id'] == $info['user_id'])
			{
				$sql = 'UPDATE '.$hhs->table('users')." SET aite_id = '$info_user_id' WHERE aite_id = '$count[aite_id]'";
				$db->query($sql);
			}
			if($info['name'] != $count['user_name'])   // 这段可删除
			{
				if($user->check_user($info['name']))  // 重名处理
				{
					$info['name'] = $info['name'].'_'.$type.(rand()*1000);
				}
				$sql = 'UPDATE '.$hhs->table('users')." SET user_name = '$info[name]' WHERE aite_id = '$info_user_id'";
				$db->query($sql);
			}
		}
		$user->set_session($info['name']);
		$user->set_cookie($info['name']);
		update_user_info();
		recalculate_price();
		if(!empty($_REQUEST['open']))
		{
			die('<script>window.opener.window.location.reload(); window.close();</script>');
		}
		else
		{
			hhs_header('Location: '.$_REQUEST['callblock']);
		}
	}
}
//  处理其它登录接口
elseif($action == 'other_login')
{
	$type = empty($_REQUEST['type']) ?  '' : $_REQUEST['type'];
	session_start();
	$info = $_SESSION['user_info'];
	if(empty($info)){
		show_message("非法访问或请求超时！" , '首页' , $hhs->url() , 'error' , false);
	}
	if(!$info['user_id'])
		show_message("非法访问或访问出错，请联系管理员！", '首页' , $hhs->url() , 'error' , false);
	$info_user_id = $type .'_'.$info['user_id']; //  加个标识！！！防止 其他的标识 一样  // 以后的ID 标识 将以这种形式 辨认
	$info['name'] = str_replace("'" , "" , $info['name']); // 过滤掉 逗号 不然出错  很难处理   不想去  搞什么编码的了
	$sql = 'SELECT user_name,password,aite_id FROM '.$hhs->table('users').' WHERE aite_id = \''.$info_user_id.'\' OR aite_id=\''.$info['user_id'].'\'';
	$count = $db->getRow($sql);
	$login_name = $info['name'];
	if(!$count)   // 没有当前数据
	{
		if($user->check_user($info['name']))  // 重名处理
		{
			$info['name'] = $info['name'].'_'.$type.(rand()*1000);
		}
		$login_name = $info['name'];
		$user_pass = $user->compile_password(array('password'=>$info['user_id']));
		$sql = 'INSERT INTO '.$hhs->table('users').'(user_name , password, aite_id , sex , reg_time , user_rank , is_validated) VALUES '.
				"('$info[name]' , '$user_pass' , '$info_user_id' , '$info[sex]' , '".gmtime()."' , '$info[rank_id]' , '1')" ;
		$db->query($sql);
	}
	else
	{
		$login_name = $count['user_name'];
		$sql = '';
		if($count['aite_id'] == $info['user_id'])
		{
			$sql = 'UPDATE '.$hhs->table('users')." SET aite_id = '$info_user_id' WHERE aite_id = '$count[aite_id]'";
			$db->query($sql);
		}
	}
	$user->set_session($login_name);
	$user->set_cookie($login_name);
	update_user_info();
	recalculate_price();
	$redirect_url =  "http://".$_SERVER["HTTP_HOST"].str_replace("user.php", "index.php", $_SERVER["REQUEST_URI"]);
	header('Location: '.$redirect_url);
}
/* 微信用户自动登陆 */
elseif ($action == 'weixin_login')
{
	$user_name = !empty($_REQUEST['username']) ? $_GET['username'] : '';
	$pwd = !empty($_GET['pwd']) ? $_GET['pwd'] : '';
	$gourl = !empty($_GET['gourl']) ? $_GET['gourl'] : '';
	$remember = isset($_GET['remember']) ? $_GET['remember'] : 0;
	//记住用户名字
	if(!empty($remember)){
		setcookie("HHS[reuser_name]", $user_name, time() + 31536000, '/');
	}
	$reuser_name= isset($_COOKIE['HHS']['reuser_name']) ? $_COOKIE['HHS']['reuser_name'] : '';
	if(!empty($reuser_name)){
		$smarty->assign('reuser_name', $reuser_name);
	}
	if (empty($user_name) || empty($pwd))
	{
		hhs_header("Location:user.php\n");
		$login_faild = 1;
	}
	else
	{
		if ($user->check_user($user_name, $pwd) > 0)
		{
			$user->set_session($user_name);
			$user->set_cookie($user_name);
			update_user_info();
			//优化登陆跳转
			if($gourl){
				hhs_header("Location:$gourl\n");
				exit;
			}else{
				$sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') . " WHERE session_id = '" . SESS_ID . "' " . "AND parent_id = 0 AND is_gift = 0 AND rec_type = 0";
				if ($db->getOne($sql) > 0){
					hhs_header("Location:cart.php\n");
					exit;
				}else{
					hhs_header("Location:user.php\n");
					exit;
				}
			}
		}
		else
		{
			$login_faild = 1;
		}
	}
	$smarty->assign('login_faild', $login_faild);
	$smarty->display('login.dwt');
	exit;
}
/* 验证用户注册邮件 */
elseif ($action == 'validate_email')
{
    $hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
    if ($hash)
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');
        $id = register_hash('decode', $hash);
        if ($id > 0)
        {
            $sql = "UPDATE " . $hhs->table('users') . " SET is_validated = 1 WHERE user_id='$id'";
            $db->query($sql);
            $sql = 'SELECT user_name, email FROM ' . $hhs->table('users') . " WHERE user_id = '$id'";
            $row = $db->getRow($sql);
            show_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'user.php');
        }
    }
    show_message($_LANG['validate_fail']);
}
/* 验证用户注册用户名是否可以注册 */
elseif ($action == 'is_registered')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $username = trim($_GET['username']);
    $username = json_str_iconv($username);
    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}
/* 验证用户邮箱地址是否被注册 */
elseif($action == 'check_email')
{
    $email = trim($_GET['email']);
    if ($user->check_email($email))
    {
        echo 'false';
    }
    else
    {
        echo 'ok';
    }
}
/* 用户登录界面 */
elseif ($action == 'login')
{
    if (empty($back_act))
    {
        if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
        {
            $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        }
        else
        {
            $back_act = 'user.php';
        }
    }
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
    /*判断是否是从购物页面进行登录*/
    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}
elseif ($action == 'get_mobile_code')
{
    include_once 'includes/cls_json.php';
    $json = new JSON();
    $result = array('error' => '', 'mobile' => '');
    include_once ROOT_PATH . 'includes/lib_passport.php';
    if ($_REQUEST['mobile_code'] !== $_SESSION['validate_mobile_code'])
	{
        $result['error'] = 1;
    } else {
        $result['error'] = 0;
    }
    echo $json->encode($result);
    die;
}
elseif ($action == 'send_mobile_code')
{
    include_once 'includes/cls_json.php';
    $json = new JSON();
    $result = array('error' => '', 'mobile' => '');
    $mobile = trim($_REQUEST['mobile']);
	$code = getRandomCode(5);
	include_once ROOT_PATH . 'includes/cls_sms.php';
	$sms = new sms();
	$msg = '尊敬的用户，您的校验证码为：' . $code . '，请勿向任何人提供您收到的短信校验证码。';
	$send = $sms->send($mobile, $msg, '', 1);
	$_SESSION['validate_mobile_code'] = $code;
	if ($send)
	{
		$_SESSION['validate_mobile_num']++;
		$_SESSION['validate_mobile_code'] = $code;
		$result['error'] = 1;
	} 
	else 
	{
		$result['error'] = 3;
		//失败
		$result['code'] = $code;
	}
    echo $json->encode($result);
    die;
}
/* 处理会员的登录 */
elseif ($action == 'act_login')
{
	include_once(ROOT_PATH . 'includes/lib_passport.php');
    $mobile_phone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
	$code = isset($_POST['code']) ? trim($_POST['code']) : '';
	
	$sql="select user_id from ".$hhs->table('users')." where (mobile_phone=".$mobile_phone." or user_name = ".$mobile_phone.")";
	$user_id=$db->getOne($sql);
	$back_url = $_POST['back_act'];
	$_SESSION['user_id'] = $user_id;
			
	if($user_id){
		if($code == $_SESSION['validate_mobile_code'])
		{
			update_user_info();
			if($back_url)
			{
				show_message('登陆成功', '', $back_url);
			}
			show_message('登陆成功', '', 'user.php');
		}
		else
		{
			show_message('验证码错误', '', 'user.php');
		}
	}
	else
	{
		if($code == $_SESSION['validate_mobile_code'])
		{
			$ychar="0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z";
			$list=explode(",",$ychar);
			$password='';
			for($i=0;$i<6;$i++){
				$randnum=rand(0,35);
				$password.=$list[$randnum];
			}
			$sql="select user_id from ".$hhs->table('users')." order by user_id desc limit 1";
			$user_id=$db->getOne($sql)+1;
			$username = 'wx'.$user_id.mt_rand(0,100);
			$email    = '';
			$other['mobile_phone'] = $mobile_phone;
			if (register($username, $password, $email, $other) !== false)
			{
				
			}
		
		
			//注册新用户，更新用户状态已登录
			update_user_info();
			show_message('登陆成功', '', 'user.php');
		}
		else
		{
			show_message('验证码错误', '', 'user.php');
		}
	}
}
/* 处理 ajax 的登录请求 */
elseif ($action == 'signin')
{
    include_once('includes/cls_json.php');
    $json = new JSON;
    $username = !empty($_POST['username']) ? json_str_iconv(trim($_POST['username'])) : '';
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $captcha = !empty($_POST['captcha']) ? json_str_iconv(trim($_POST['captcha'])) : '';
    $result   = array('error' => 0, 'content' => '');
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($captcha))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
        /* 检查验证码 */
        include_once('includes/cls_captcha.php');
        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
    }
    if ($user->login($username, $password))
    {
        update_user_info();  //更新用户信息
        recalculate_price(); // 重新计算购物车中的商品价格
        $smarty->assign('user_info', get_user_info());
        $ucdata = empty($user->ucdata)? "" : $user->ucdata;
        $result['ucdata'] = $ucdata;
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    }
    else
    {
        $_SESSION['login_fail']++;
        if ($_SESSION['login_fail'] > 2)
        {
            $smarty->assign('enabled_captcha', 1);
            $result['html'] = $smarty->fetch('library/member_info.lbi');
        }
        $result['error']   = 1;
        $result['content'] = $_LANG['login_failure'];
    }
    die($json->encode($result));
}
/* 退出会员中心 */
elseif ($action == 'logout')
{
    if ((!isset($back_act)|| empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }
    $user->logout();
    $ucdata = empty($user->ucdata)? "" : $user->ucdata;
    show_message($_LANG['logout'] . $ucdata, array($_LANG['back_up_page'], $_LANG['back_home_lnk']), array($back_act, 'index.php'), 'info');
}
/* 个人资料页面 */
elseif ($action == 'profile')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $user_info = get_profile($user_id);
    /* 密码提示问题 */
    $smarty->assign('profile', $user_info);
    $smarty->display('user_profile.dwt');
}
/* 修改个人资料的处理 */
elseif ($action == 'act_edit_profile')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
	//include_once(ROOT_PATH . 'includes/lib_passport.php');
    $mobile_phone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
	$code = isset($_POST['code']) ? trim($_POST['code']) : '';
	$sql="select user_id from ".$hhs->table('users')." where (mobile_phone=".$mobile_phone." or user_name = ".$mobile_phone.")";
	$user_id=$db->getOne($sql);

	if($code == $_SESSION['validate_mobile_code'])
	{
		if($user_id){
		    show_message('手机号已存在', '', 'user.php?act=profile');
		}
		else
		{
			$sql="update ".$hhs->table('users')." set mobile_phone=$mobile_phone where user_id=".$_SESSION['user_id'];
	        $db->query($sql);
			show_message('手机号绑定成功', '', 'user.php?act=profile');
		}
	}
	else
	{
		show_message('验证码错误', '', 'user.php?act=profile');
	}
}
/* 密码找回-->修改密码界面 */
elseif ($action == 'get_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    if (isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);
        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
        }
        $smarty->assign('uid',    $uid);
        $smarty->assign('code',   $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
    else
    {
        //显示用户名和email表单
        $smarty->display('user_passport.dwt');
    }
}
/* 密码找回-->输入用户名界面 */
elseif ($action == 'qpassword_name')
{
    //显示输入要找回密码的账号表单
    $smarty->display('user_passport.dwt');
}
/* 密码找回-->根据注册用户名取得密码提示问题界面 */
elseif ($action == 'get_passwd_question')
{
    if (empty($_POST['user_name']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }
    else
    {
        $user_name = trim($_POST['user_name']);
    }
    //取出会员密码问题和答案
    $sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $hhs->table('users') . " WHERE user_name = '" . $user_name . "'";
    $user_question_arr = $db->getRow($sql);
    //如果没有设置密码问题，给出错误提示
    if (empty($user_question_arr['passwd_answer']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }
    $_SESSION['temp_user'] = $user_question_arr['user_id'];  //设置临时用户，不具有有效身份
    $_SESSION['temp_user_name'] = $user_question_arr['user_name'];  //设置临时用户，不具有有效身份
    $_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];   //存储密码问题答案，减少一次数据库访问
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
    $smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
    $smarty->display('user_passport.dwt');
}
/* 密码找回-->根据提交的密码答案进行相应处理 */
elseif ($action == 'check_answer')
{
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }
        /* 检查验证码 */
        include_once('includes/cls_captcha.php');
        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }
    }
    if (empty($_POST['passwd_answer']) || $_POST['passwd_answer'] != $_SESSION['passwd_answer'])
    {
        show_message($_LANG['wrong_passwd_answer'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'info');
    }
    else
    {
        $_SESSION['user_id'] = $_SESSION['temp_user'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_user_name']);
        $smarty->assign('uid',    $_SESSION['user_id']);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
}
/* 发送密码修改确认邮件 */
elseif ($action == 'send_pwd_email')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    /* 初始化会员用户名和邮件地址 */
    $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $email     = !empty($_POST['email'])     ? trim($_POST['email'])     : '';
    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);
    if ($user_info && $user_info['email'] == $email)
    {
        //生成code
         //$code = md5($user_info[0] . $user_info[1]);
        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);
        //发送邮件的函数
        if (send_pwd_email($user_info['user_id'], $user_name, $email, $code))
        {
            show_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], './', 'info');
        }
        else
        {
            //发送邮件出错
            show_message($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
        }
    }
    else
    {
        //用户名与邮件地址不匹配
        show_message($_LANG['username_no_email'], $_LANG['back_page_up'], '', 'info');
    }
}
/* 重置新密码 */
elseif ($action == 'reset_password')
{
    //显示重置密码的表单
    $smarty->display('user_passport.dwt');
}
/* 修改会员密码 */
elseif ($action == 'act_edit_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id      = isset($_POST['uid'])  ? intval($_POST['uid']) : $user_id;
    $code         = isset($_POST['code']) ? trim($_POST['code'])  : '';
    if (strlen($new_password) < 6)
    {
        show_message($_LANG['passport_js']['password_shorter']);
    }
    $user_info = $user->get_profile_by_id($user_id); //论坛记录
    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {
        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
			$sql="UPDATE ".$hhs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";
			$db->query($sql);
            $user->logout();
            show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
        }
        else
        {
            show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
        }
    }
    else
    {
        show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
    }
}
/* 添加一个优惠劵 */
elseif ($action == 'act_add_bonus')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $bouns_sn = isset($_POST['bonus_sn']) ? intval($_POST['bonus_sn']) : '';
    if (add_bonus($user_id, $bouns_sn))
    {
        show_message($_LANG['add_bonus_sucess'], $_LANG['back_up_page'], 'user.php?act=bonus', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=bonus');
    }
}
/* 查看订单列表 */
elseif ($action == 'order_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $composite_status = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
    $where=" AND is_luck = 0 ";
    //未付款
    if($_REQUEST['composite_status'] =='100')
    {
        $where = " and order_status in (0,1,5)  and pay_status=0 ";
    }
    //待收货
    if($_REQUEST['composite_status'] =='180')
    {
        $where .= order_query_sql('await_ship')." and point_id = 0 ";
    }
    //待核销
    if($_REQUEST['composite_status'] =='102')
    {
        $where .= order_query_sql('await_ship')." and point_id > 0 ";
    }
    /* 已发货订单：不论是否付款 */
    if($_REQUEST['composite_status'] =='120')
    {
        $where .= order_query_sql('shipped2');
    }
    /* 已完成订单 */
    if($_REQUEST['composite_status'] =='999')
    {
        $where .= order_query_sql();
    }
    //include_once(ROOT_PATH . 'wxpay/demo/wxch_order.php');
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id'" .$where);
    $pager  = get_pager('user.php', array('act' => $action,'composite_status'=>$_REQUEST['composite_status']), $record_count, $page);
    $orders = get_user_orders_ex($user_id, $pager['size'], $pager['start'],$where);
    $merge  = get_user_merge($user_id);
	$smarty->assign('root', $_SERVER['HTTP_HOST']);
    $smarty->assign('merge',  $merge);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->assign('composite_status',  $_REQUEST['composite_status']);
    $smarty->display('user_order.dwt');
}
/* 查看我的团订单列表 */
elseif ($action == 'team_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
	$composite_status = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : '';
	$where = ' AND `is_luck` = 0 ';
	switch ($composite_status) {
        case 999:
            $where .= " AND `team_status` > 2 ";
            break;
        case 120:
            $where .= " AND `team_status` = 2 ";
            break;
        case 100:
            $where .= " AND `team_status` = 1 ";
            break;
        default:
            $where .= " AND `team_status` > 0 ";
            break;
    }
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id' and extension_code='team_goods' and team_status>0 " . $where);
    $pager  = get_pager('user.php', array('act' => $action,'composite_status'=>$composite_status), $record_count, $page);
    $orders = get_user_team_orders($user_id, $pager['size'], $pager['start'], $where);
//	echo "<pre>";
//	
//	print_r($orders);
//	
//	exit;
    $merge  = get_user_merge($user_id);
    $smarty->assign('merge',  $merge);
    $smarty->assign('pager',  $pager);
	$smarty->assign('composite_status',  $composite_status);
    $smarty->assign('orders', $orders);
    $smarty->display('user_order.dwt');
}
elseif ($action == 'userbuyshop') {
	$where = " user_id = '$user_id'   and extension_id = 0 and pay_status = 2";
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info')." where ".$where);
	$pager  = get_pager('user.php', array('act' => $action), $record_count, $page);
	$userbuyshop = get_userbuyshop_goods($user_id, $pager['size'], $pager['start']);
	/*  echo "<pre>";
	var_dump($userbuyshop);die;  */
	$smarty->assign('pager',  $pager);
	$smarty->assign('userbuyshop', $userbuyshop);
	$smarty->display('user_userbuyshop.dwt');
}
/* 查看订单详情 */
elseif ($action == 'order_detail')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);
    if($order['is_luck']){
        $luck_rows = $db->getAll('select * from '.$hhs->table("order_luck").' WHERE order_id = "'.$order_id.'" ');
        $smarty->assign('luck_rows', $luck_rows);
    }
    $team = isset($_GET['team']) ? intval($_GET['team']) : 0;
    if($team>0 && !empty($order['team_sign']) && $order['team_status']!=0&&!empty($order['pay_time'])){//
        //成功的回调
        //include_once(ROOT_PATH .'successurl.php');
    	$luckdraw_id = $_REQUEST['luckdraw_id'];
		if($luckdraw_id){
			hhs_header("location:share.php?team_sign=".$order['team_sign']."&luckdraw_id=".$luckdraw_id);
		}else{
			hhs_header("location:share.php?team_sign=".$order['team_sign']);
		}
        exit();
    }
    if ($order === false)
    {
        //$err->show($_LANG['back_home_lnk'], './');
        hhs_header("location:index.php");
        exit;
    }
    /* 是否显示添加到购物车 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods')
    {
        $smarty->assign('allow_to_cart', 1);
    }
    /* 订单商品 */
    $goods_list = order_goods($order_id);
	foreach ($goods_list as $k=>$v){
		$sql = "select luckdraw_id from ".$GLOBALS['hhs']->table('order_info')." where order_id = ".$order_id;
		$luckdraw_id = $GLOBALS['db']->getOne($sql);
		$goods_list[$k]['luckdraw_id'] = $luckdraw_id;
	}
	//print_r($goods_list);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }
     /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], $user['user_money']));
            }
        }
    }
    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $payment_list = available_payment_list(false, 0, true);
        /* 过滤掉当前支付方式和余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }
    /* 订单状态 */
    $_LANG['os'][OS_UNCONFIRMED] = '未确认';
    $_LANG['os'][OS_CONFIRMED] = '已确认';
    $_LANG['os'][OS_SPLITED] = '已确认';
    $_LANG['os'][OS_SPLITING_PART] = '已确认';
    $_LANG['os'][OS_CANCELED] = '已取消';
    $_LANG['os'][OS_INVALID] = '无效';
    $_LANG['os'][OS_RETURNED] = '已退货';
    $_LANG['ss'][SS_UNSHIPPED] = '未发货';
    $_LANG['ss'][SS_PREPARING] = '配货中';
    $_LANG['ss'][SS_SHIPPED] = '配送中';//已发货
    $_LANG['ss'][SS_RECEIVED] = '已签收';//收货确认
    $_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
    $_LANG['ss'][SS_SHIPPED_ING] = '配货中'; // 已分单
    $_LANG['ps'][PS_UNPAYED] = '待支付';
    $_LANG['ps'][PS_PAYING] = '付款中';
    $_LANG['ps'][PS_PAYED] = '已付款';
    $_LANG['ps'][PS_REFUNDED] = '已退款';
    $_LANG['cancel'] = '取消订单';
    $_LANG['pay_money'] = '付款';
    $_LANG['view_order'] = '查看订单';
    $_LANG['received'] = '确认收货';
    $_LANG['ss_received'] = '已完成';
    $_LANG['confirm_received'] = '你确认已经收到货物了吗？';
    $_LANG['confirm_cancel'] = '您确认要取消该订单吗？取消后此订单将视为无效订单';
    $order['order_status_cy']=$order['order_status'] ;
    $order['pay_status_cy']=$order['pay_status'] ;
    $order['shipping_status_cy']=$order['shipping_status'] ;
    /*可进行的操作*/
    if ($order['order_status'] == 0)
    {
		@$order['handler'] = $order['pay_online'];
        $order['handler'] .= "<a class='state_btn_1' href=\"user.php?act=cancel_order&order_id=" .$order['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_cancel']."')) return false;\">取消订单</a>";
    }
    else if ($order['order_status'] == OS_SPLITED)
    {
        /* 对配送状态的处理 */
        if ($order['shipping_status'] == SS_SHIPPED)
        {
            @$order['handler'] = "<a class='state_btn_1' href=\"user.php?act=affirm_received&order_id=" .$order['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_received']."')) return false;\">".$GLOBALS['_LANG']['received']."</a>";
            @$order['handler'] .= "<a class='state_btn_2' href=\"javascript:void(0);\" onclick='get_invoice(\"".$order['shipping_name']."\",\"".$order['invoice_no']."\");'>查看物流</a>";
        }/*
        elseif ($row['shipping_status'] == SS_RECEIVED)
        {
            @$row['handler'] = '<span style="color:red">'.$GLOBALS['_LANG']['ss_received'] .'</span>';
        }*/
        else
        {
            if ($order['pay_status'] == PS_UNPAYED)
            {
                @$order['handler'] = $order['pay_online'];
            }     
        }
    }
    /* 订单 支付 配送 状态语言项 
    if($order['order_status']==2){
        if($order['extension_code']=='team_goods'){
            if($order['team_status']==3){
                $order['order_status'] = '待退款';
            }else{
                $order['order_status'] = '已退款';
            } 
        }else{
            $order['order_status'] = $_LANG['os'][$order['order_status']];
        }
    }else{
        if($order['pay_status']==0){
            $order['order_status'] = $_LANG['ps'][$order['pay_status']];
        }else{
            $order['order_status'] =   $GLOBALS['_LANG']['ss'][$order['shipping_status']];//$GLOBALS['_LANG']['ps'][$order['pay_status']] . ',' .
        }
    }*/
    $order['order_status'] = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
    /*
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
	*/
	$province=$db->getRow("select region_name from hhs_region where region_id='$order[province]'");
    $city=$db->getRow("select region_name from hhs_region where region_id='$order[city]'");
    $district=$db->getRow("select region_name from hhs_region where region_id='$order[district]'");
    $order['province']=$province['region_name'];
    $order['city']=$city['region_name'];
    $order['district']=$district['region_name'];
	if($order['point_id'])
	{
		$order['shipping_point'] = get_shipping_point_name($order['point_id']);
	}
    $order['add_time']=local_date("Y-m-d H:i:s",$order['add_time']);
    $smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('user_order.dwt');
}
/* 查看订单详情 */
elseif ($action == 'team_detail')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);
    if ($order === false)
    {    
        exit;
    }
    /* 订单商品 */
    $goods_list = order_goods($order_id);
    foreach ($goods_list AS $key => $value)
    {
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }
    /* 订单 支付 配送 状态语言项 */
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
	$smarty->assign('team_suc_time',$_CFG['team_suc_time']);
    //参团的人
    $sql="select u.user_name,u.headimgurl,o.pay_time from ".$hhs->table('order_info')." as o left join ".$hhs->table('users')." as u on o.user_id=u.user_id where team_sign=".$order['team_sign']." order by order_id";
	$team_mem=$db->getAll($sql);
	foreach($team_mem as $k=>$v){
	    $team_mem[$k]['date']=local_date('Y-m-d H:i:s',$v['pay_time']);
	}
	$smarty->assign('team_mem', $team_mem);
	$team_start=$team_mem[0]['pay_time'];
	$smarty->assign('team_start', $team_start);
	$smarty->assign('systime', gmtime());
    $smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->display('user_order.dwt');
}
/* 取消订单 */
elseif ($action == 'cancel_order')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if (cancel_order($order_id, $user_id))
    {
        hhs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}
/* 收货地址列表界面*/
elseif ($action == 'address_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang',  $_LANG);
    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
    /* 获得用户所有的收货人信息 */
    $consignee_list = get_consignee_list($_SESSION['user_id']);
    foreach($consignee_list as $idx=>$value)
    {
        $consignee_list[$idx]['country_name']  = get_region_name($value['country']);
        $consignee_list[$idx]['province_name'] = get_region_name($value['province']);
        $consignee_list[$idx]['city_name']     = get_region_name($value['city']);
        $consignee_list[$idx]['district_name'] = get_region_name($value['district']);
    }
    if (count($consignee_list) < 5 && $_SESSION['user_id'] > 0)
    {
        /* 如果用户收货人信息的总数小于5 则增加一个新的收货人信息 */
        //$consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
    }
    $smarty->assign('consignee_list', $consignee_list);
    //取得国家列表，如果有收货人列表，取得省市区列表
    foreach ($consignee_list AS $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;
        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);
    }
    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$hhs->table('users'). " WHERE user_id='$user_id'");
    //赋值于模板
    $smarty->assign('real_goods_count', 1);
    $smarty->assign('shop_country',     $_CFG['shop_country']);
    $smarty->assign('shop_province',    get_regions(1, $_CFG['shop_country']));
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('address',          $address_id);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);
    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
    $smarty->display('user_address.dwt');
}
/* 设置默认地址 */
elseif ($action == 'set_address')
{
        $address_id = empty($_REQUEST['id'])?0:intval($_REQUEST['id']);
        if($db->query("UPDATE " . $hhs->table('users') . " SET address_id = $address_id  WHERE user_id='$user_id'")){ 
			hhs_header("Location: user.php?act=address_list\n");
        }
}
elseif ($action == 'edit_address')
{
    //编辑收货人地址
    $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
    $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
    $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;
    $province_list[$region_id] = get_regions(1, $consignee['country']);
    $city_list[$region_id]     = get_regions(2, $consignee['province']);
    $district_list[$region_id] = get_regions(3, $consignee['city']);
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('address',          $address_id);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('consignee',    $consignee);
    $smarty->assign('consignee',    $consignee);
    $smarty->assign('back_url',    $_REQUEST['back_url']);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang',  $_LANG);
    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
    /* 获得用户所有的收货人信息 */
    $consignee_list = get_consignee_list($_SESSION['user_id']);
    if (count($consignee_list) < 5 && $_SESSION['user_id'] > 0)
    {
        /* 如果用户收货人信息的总数小于5 则增加一个新的收货人信息 */
        $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
    }
    $smarty->assign('consignee_list', $consignee_list);
    //取得国家列表，如果有收货人列表，取得省市区列表
    foreach ($consignee_list AS $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;
        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);
    }
    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$hhs->table('users'). " WHERE user_id='$user_id'");
    //赋值于模板
    $smarty->assign('real_goods_count', 1);
    $smarty->assign('shop_country',     $_CFG['shop_country']);
    $smarty->assign('shop_province',    get_regions(1, $_CFG['shop_country']));
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('address',          $address_id);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);
    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
    $smarty->display('user_address.dwt');
}
/* 添加/编辑收货地址的处理 */
elseif ($action == 'act_edit_address')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);
    $address = array(
        'user_id'    => $user_id,
        'address_id' => intval($_POST['address_id']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        );
    if (update_address($address))
    {
        show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');
    }
}
elseif ($action == 'edit_consignee')
{
    //编辑收货人地址
    include_once('includes/lib_transaction.php');
    $address_id=$_REQUEST['address_id'];
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('user_address') .
    " WHERE address_id = '$address_id' ";
    $consignee=$GLOBALS['db']->getRow($sql);
    $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
    $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
    $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;
    $province_list = get_regions(1, 1);//get_regions(1, $consignee['country']);
    $city_list     = get_regions(2, $consignee['province']);
    $district_list = get_regions(3, $consignee['city']);
	/*pangbin start*/
	if(empty($address_id))
	{
		$region_type = get_region_type($_SESSION['site_id']); //获取定位地区类别
		switch ($region_type)
		{
			case 1:
				$province_on = $_SESSION['site_id'];
			break;
			case 2:
				$city_on = $_SESSION['site_id'];
				$province_on = get_region_parent($_SESSION['site_id']);
				$city_list     = get_regions(2, $province_on);
				$district_list = get_regions(3, $city_on);
			break;
			case 3:
				$district_on = $_SESSION['site_id'];
				$city_on = get_region_parent($district_on);
				$province_on = get_region_parent($city_on);
				$city_list     = get_regions(2, $province_on);
				$district_list = get_regions(3, $city_on);
				
			break;
		}
		$smarty->assign('province_on',    $province_on);
		$smarty->assign('city_on',    $city_on);
		$smarty->assign('district_on',    $district_on);	
	}
	/*pangbin end*/
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('address',          $address_id);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('consignee',    $consignee);
    $smarty->assign('consignee',    $consignee);
    $smarty->assign('back_url',    "user.php");
    $smarty->display('edit_consignee.dwt');
}
elseif ($action == 'act_edit_consignee')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);
    $address = array(
        'user_id'    => $_SESSION['user_id'],
        'address_id' => intval($_POST['address_id']),
		'address_type' => intval($_POST['address_type']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
		 'address_type'   => isset($_POST['address_type'])  ? intval($_POST['address_type']) : 0,
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
    );
    if (update_address($address))
    {  
        hhs_header('location:user.php?act=address_list');
        //show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');
    }
}
/* 删除收货地址 */
elseif ($action == 'drop_consignee')
{
    include_once('includes/lib_transaction.php');
    $consignee_id = intval($_GET['id']);
    if (drop_consignee($consignee_id))
    {
        hhs_header("Location: user.php?act=address_list\n");
        exit;
    }
    else
    {
        show_message($_LANG['del_address_false']);
    }
}
/* 显示收藏商品列表 */
elseif ($action == 'collection_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('collect_goods').
                                " WHERE user_id='$user_id' ORDER BY add_time DESC");
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('url',        $hhs->url());
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );
    $smarty->assign('lang_list',  $lang_list);
    $smarty->assign('user_id',  $user_id);
    $smarty->display('user_collection_list.dwt');
}
/* 显示收藏店铺列表 */
elseif ($action == 'collect_store_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('collect_store'). " WHERE user_id='$user_id' ORDER BY add_time DESC");
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('store_list', get_collection_store($user_id, $pager['size'], $pager['start']));
    $smarty->assign('url',        $hhs->url());
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );
    $smarty->assign('lang_list',  $lang_list);
    $smarty->assign('user_id',  $user_id);
    $smarty->display('user_collection_list.dwt');
}
/* 删除收藏的商品 */
elseif ($action == 'delete_collection')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$hhs->table('collect_goods'). " WHERE rec_id='$collection_id' AND user_id ='$user_id'" );
    }
    hhs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 删除收藏的商品 */
elseif ($action == 'del_collection')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$hhs->table('collect_goods'). " WHERE goods_id='$collection_id' AND user_id ='$user_id'" );
    }
	exit;
}
/* 添加关注商品 */
elseif ($action == 'add_to_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$hhs->table('collect_goods'). "SET is_attention = 1 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    hhs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 取消关注商品 */
elseif ($action == 'del_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$hhs->table('collect_goods'). "SET is_attention = 0 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    hhs_header("Location: user.php?act=collection_list\n");
    exit;
}
elseif ($action == 'del_collect_store')
{
    $rec_id = (int)$_GET['id'];
    if ($rec_id)
    {
        $db->query('DELETE FROM' .$hhs->table('collect_store'). "  WHERE suppliers_id='$rec_id' AND user_id ='$user_id'" );
    }
    hhs_header("Location: user.php?act=collect_store_list\n");
    exit;
}
/* 显示留言列表 */
elseif ($action == 'message_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    $order_info = array();
    /* 获取用户留言的数量 */
    if ($order_id)
    {
        $sql = "SELECT COUNT(*) FROM " .$hhs->table('feedback').
                " WHERE parent_id = 0 AND order_id = '$order_id' AND user_id = '$user_id'";
        $order_info = $db->getRow("SELECT * FROM " . $hhs->table('order_info') . " WHERE order_id = '$order_id' AND user_id = '$user_id'");
        $order_info['url'] = 'user.php?act=order_detail&order_id=' . $order_id;
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " .$hhs->table('feedback').
           " WHERE parent_id = 0 AND user_id = '$user_id' AND user_name = '" . $_SESSION['user_name'] . "' AND order_id=0";
    }
    $record_count = $db->getOne($sql);
    $act = array('act' => $action);
    if ($order_id != '')
    {
        $act['order_id'] = $order_id;
    }
    $pager = get_pager('user.php', $act, $record_count, $page, 5);
    $smarty->assign('message_list', get_message_list($user_id, $_SESSION['user_name'], $pager['size'], $pager['start'], $order_id));
    $smarty->assign('pager',        $pager);
    $smarty->assign('order_info',   $order_info);
    $smarty->display('user_clips.dwt');
}
/* 显示评论列表 */
elseif ($action == 'comment_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /* 获取用户留言的数量 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table('comment').
           " WHERE parent_id = 0 AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, 5);
    $smarty->assign('comment_list', get_comment_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}
/* 添加我的留言 */
elseif ($action == 'act_add_message')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $message = array(
        'user_id'     => $user_id,
        'user_name'   => $_SESSION['user_name'],
        'user_email'  => $_SESSION['email'],
        'msg_type'    => isset($_POST['msg_type']) ? intval($_POST['msg_type'])     : 0,
        'msg_title'   => isset($_POST['msg_title']) ? trim($_POST['msg_title'])     : '',
        'msg_content' => isset($_POST['msg_content']) ? trim($_POST['msg_content']) : '',
        'order_id'=>empty($_POST['order_id']) ? 0 : intval($_POST['order_id']),
        'upload'      => (isset($_FILES['message_img']['error']) && $_FILES['message_img']['error'] == 0) || (!isset($_FILES['message_img']['error']) && isset($_FILES['message_img']['tmp_name']) && $_FILES['message_img']['tmp_name'] != 'none')
         ? $_FILES['message_img'] : array()
     );
    if (add_message($message))
    {
        show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'], 'user.php?act=message_list&order_id=' . $message['order_id'],'info');
    }
    else
    {
        $err->show($_LANG['message_list_lnk'], 'user.php?act=message_list');
    }
}
/* 标签云列表 */
elseif ($action == 'tag_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $good_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $smarty->assign('tags',      get_user_tags($user_id));
    $smarty->assign('tags_from', 'user');
    $smarty->display('user_clips.dwt');
}
/* 删除标签云的处理 */
elseif ($action == 'act_del_tag')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $tag_words = isset($_GET['tag_words']) ? trim($_GET['tag_words']) : '';
    delete_tag($tag_words, $user_id);
    hhs_header("Location: user.php?act=tag_list\n");
    exit;
}
/* 显示缺货登记列表 */
elseif ($action == 'booking_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /* 获取缺货登记的数量 */
    $sql = "SELECT COUNT(*) " .
            "FROM " .$hhs->table('booking_goods'). " AS bg, " .
                     $hhs->table('goods') . " AS g " .
            "WHERE bg.goods_id = g.goods_id AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('booking_list', get_booking_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}
/* 添加缺货登记页面 */
elseif ($action == 'add_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($goods_id == 0)
    {
        show_message($_LANG['no_goods_id'], $_LANG['back_page_up'], '', 'error');
    }
    /* 根据规格属性获取货品规格信息 */
    $goods_attr = '';
    if ($_GET['spec'] != '')
    {
        $goods_attr_id = $_GET['spec'];
        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
                "FROM " . $hhs->table('goods_attr') . " AS g, " .
                    $hhs->table('attribute') . " AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_attr_id " . db_create_in($goods_attr_id);
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
    }
    $smarty->assign('goods_attr', $goods_attr);
    $smarty->assign('info', get_goodsinfo($goods_id));
    $smarty->display('user_clips.dwt');
}
/* 添加缺货登记的处理 */
elseif ($action == 'act_add_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $booking = array(
        'goods_id'     => isset($_POST['id'])      ? intval($_POST['id'])     : 0,
        'goods_amount' => isset($_POST['number'])  ? intval($_POST['number']) : 0,
        'desc'         => isset($_POST['desc'])    ? trim($_POST['desc'])     : '',
        'linkman'      => isset($_POST['linkman']) ? trim($_POST['linkman'])  : '',
        'email'        => isset($_POST['email'])   ? trim($_POST['email'])    : '',
        'tel'          => isset($_POST['tel'])     ? trim($_POST['tel'])      : '',
        'booking_id'   => isset($_POST['rec_id'])  ? intval($_POST['rec_id']) : 0
    );
    // 查看此商品是否已经登记过
    $rec_id = get_booking_rec($user_id, $booking['goods_id']);
    if ($rec_id > 0)
    {
        show_message($_LANG['booking_rec_exist'], $_LANG['back_page_up'], '', 'error');
    }
    if (add_booking($booking))
    {
        show_message($_LANG['booking_success'], $_LANG['back_booking_list'], 'user.php?act=booking_list',
        'info');
    }
    else
    {
        $err->show($_LANG['booking_list_lnk'], 'user.php?act=booking_list');
    }
}
/* 删除缺货登记 */
elseif ($action == 'act_del_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        hhs_header("Location: user.php?act=booking_list\n");
        exit;
    }
    $result = delete_booking($id, $user_id);
    if ($result)
    {
        hhs_header("Location: user.php?act=booking_list\n");
        exit;
    }
}
/* 确认收货 */
elseif ($action == 'affirm_received')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_fenxiao.php');
    //payment_info
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if (affirm_received($order_id, $user_id))
    {
    	//分销更新状态
        $update_at = gmtime();
        updateMoney($order_id,$update_at);
        $order_info = order_info($order_id);
    	// 收货之后发优惠券
        if($_CFG['send_bonus_time'] == 1){
        	$bonus_list=send_order_bonus($order_id);
        }
      //  doFenxiao($order_info);
        if($order_info['team_sign'] > 0 && $_CFG['send_bonus_time'] == 0){
        	$bonus_list=send_order_bonus($order_id);
        }
        if(!empty($bonus_list)){
            hhs_header("Location: share_bonus.php?order_id=".$order_id );
            exit;
        }
        hhs_header("Location: user.php?act=order_list&composite_status=999\n");
        exit;
    }
    else
    {
        hhs_header("Location: user.php?act=order_list\n");
        exit;
        //$err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}
/* 会员退款申请界面 */
elseif ($action == 'account_raply')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	//获取剩余积分
	$points = get_user_points($user_id);
	$smarty->assign('points', $points);
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->display('user_account.dwt');
}
/* 会员预付款界面 */
elseif ($action == 'account_deposit')
{  
    include_once(ROOT_PATH . 'includes/lib_clips.php');
	    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	//获取剩余积分
	$points = get_user_points($user_id);
	$smarty->assign('points', $points);
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
	 //$_SESSION['user_id'] 
    $surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);
    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $account);
    $smarty->assign('op',   $user_id);
    $smarty->display('user_account.dwt');
}elseif($action == "pay_lib"){
    include_once(ROOT_PATH . 'includes/lib_order.php');
	include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
     include_once('includes/cls_json.php');
    $pay_type = $_REQUEST['pay_type'];
    $money = $_REQUEST['money'];
    $intro = $_REQUEST['intro'];
	
	
	
    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_REQUEST['rec_id'])      ? intval($_REQUEST['rec_id'])       : 0,
            'process_type' => isset($_REQUEST['surplus_type']) ? intval($_REQUEST['surplus_type']) : 0,
            'payment_id'   => isset($_REQUEST['payment_id'])   ? intval($_REQUEST['payment_id'])   : 0,
            'user_note'    => $intro,
            'amount'       => $money
    );
	
	$payment_id = $_REQUEST['payment_id'];
	$surplus['payment'] = $db->getOne("select pay_name from ".$hhs->table('payment')." where pay_id='$pay_type'");
	
	if ($surplus['rec_id'] > 0)
	{
		//更新会员账目明细
		$surplus['rec_id'] = update_user_account($surplus);
		
	}
	else
	{
		//插入会员账目明细
		$surplus['rec_id'] = insert_user_account($surplus, $money);
	}
 
	$order = array();
	$order['user_name']      = $_SESSION['user_name'];
	$order['order_amount']   = $money;
	//记录支付log
	$order['order_sn'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);
  
  	$order_sn = $order['order_sn'];
	
    $payment = payment_info(intval($pay_type));
    $pay_code   = $payment['pay_code'];
    $json = new JSON;
	
	
	
	
    if($pay_code=='wxpay'){
            include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
            $order=array('goods_name'=>'会员充值','order_amount'=>$money, 'order_sn' =>$order['order_sn']);
            /*$order = array(
                'order_amount' => $order_amount,
                'order_sn' => time().rand(99,9999),
                'goods_name' => join(',',$order_goods_name),
            );*/
            $pay_obj    = new $payment['pay_code'];
            $url ='http://'.$_SERVER['SERVER_NAME'].'/wxpay/demo/notify_url_chong.php';
            $pay_online = $pay_obj->get_code2($order, unserialize_config($payment['pay_config']),'',$url);
            $result = array(
            'error'    => 0, 
            'message'  => '', 
            'pay_code' => $pay_code, 
            'content'  => $pay_online,
            'url' => 'user.php?act=account_log'
        );
    }elseif($pay_code=='alipay'){
        $result = array( 'error'    => 0, 'pay_code'=>$pay_code,'m'=>$money,'order_sn'=>$order_sn);
    }
  die($json->encode($result));
}
/* 会员账目明细界面 */
elseif ($action == 'account_detail')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $account_type = 'user_money';
    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);
    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	//获取剩余积分
	$points = get_user_points($user_id);
    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $hhs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? $_LANG['account_inc'] : $_LANG['account_dec'];
        $row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }
    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
	$smarty->assign('points', $points);
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_account.dwt');
}
/* 会员充值和提现申请记录 */
elseif ($action == 'account_log')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
    $record_count = $db->getOne($sql);
    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
    //获取余额记录
    $account_log = get_account_log($user_id, $pager['size'], $pager['start']);
	//获取剩余积分
	$points = get_user_points($user_id);
	$smarty->assign('points', $points);
    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_account.dwt');
}
/* 对会员余额申请的处理 */
elseif ($action == 'act_account')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0)
    {
        show_message($_LANG['amount_gt_zero']);
    }
    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
            'amount'       => $amount
    );
    /* 退款申请的处理 */
    if ($surplus['process_type'] == 1)
    {
	    if ($amount < 1.00)
	    {
	        show_message('提现金额不得低于￥ 1.00 ');
	    }
	    if ($amount > 200.00)
	    {
	        show_message('提现金额不得高于￥ 200.00 ');
	    }    
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount)
        {
            $content = $_LANG['surplus_amount_error'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
        //插入会员账目明细
        $amount = '-'.$amount;
        $surplus['payment'] = '';
        $surplus['rec_id']  = insert_user_account($surplus, $amount);
        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0)
        {	/*
        	//红包提现
             include_once(ROOT_PATH . 'wxpay/wx_hongbao.php');
			 $total_amount = (-100) * $amount;
			 $min_value    = $total_amount;
			 $max_value    = $total_amount;
			 $re_openid    = $db->getOne("SELECT `openid` FROM ".$hhs->table('users')." WHERE `user_id` = '".$user_id."' ");
			 $hongbao = new hongbao();
			 $res = $hongbao->send($re_openid,$total_amount,$min_value,$max_value);
			 if($res){
			 	//更新支付状态
			     $sql = 'UPDATE ' .$GLOBALS['hhs']->table('user_account'). ' SET '.
			            "is_paid     = '1', ".
			            "paid_time   = '".gmtime()."', ".
			            "payment    = '微信红包支付' ".
			            "WHERE id   = '$surplus[rec_id]'";
			     $GLOBALS['db']->query($sql);
	             //更新会员余额数量
	             log_account_change($user_id, $amount, 0, 0, 0, '提现', 1);	
	             $content = '提现成功！';		    
			 }
			 else{
	             $content = $_LANG['surplus_appl_submit'];
			 }*/
			$content = $_LANG['surplus_appl_submit'];
            show_message($content, $_LANG['back_account_log'], 'user.php?act=account_log', 'info');
        }
        else
        {
            $content = $_LANG['process_false'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
    }
    /* 如果是会员预付款，跳转到下一步，进行线上支付的操作 */
    else
    {
        if ($surplus['payment_id'] <= 0)
        {
            show_message($_LANG['select_payment_pls']);
        }
        include_once(ROOT_PATH .'includes/lib_payment.php');
        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);
        $surplus['payment'] = $payment_info['pay_name'];
        if ($surplus['rec_id'] > 0)
        {
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        }
        else
        {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus, $amount);
        }
		
        $order = array();
        $order['user_name']      = $_SESSION['user_name'];
        $order['order_amount']   = $amount;
        //记录支付log
		
		
		
		
		
		
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);
        //生成伪订单号, 不足的时候补0
        $order = array();
        $order['order_sn']       = $surplus['rec_id'];
        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $amount;
        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);
        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $amount + $payment_info['pay_fee'];
        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);
		
        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');
        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($amount, false));
        $smarty->assign('order',   $order);
        $smarty->display('user_account.dwt');
    }
}
/* 删除会员余额 */
elseif ($action == 'cancel')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        hhs_header("Location: user.php?act=account_log\n");
        exit;
    }
    $result = del_user_account($id, $user_id);
    if ($result)
    {
        hhs_header("Location: user.php?act=account_log\n");
        exit;
    }
}
/* 会员通过帐目明细列表进行再付款的操作 */
elseif ($action == 'pay')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    //变量初始化
    $surplus_id = isset($_GET['id'])  ? intval($_GET['id'])  : 0;
    $payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
    if ($surplus_id == 0)
    {
        hhs_header("Location: user.php?act=account_log\n");
        exit;
    }
    //如果原来的支付方式已禁用或者已删除, 重新选择支付方式
    if ($payment_id == 0)
    {
        hhs_header("Location: user.php?act=account_deposit&id=".$surplus_id."\n");
        exit;
    }
    //获取单条会员帐目信息
    $order = array();
    $order = get_surplus_info($surplus_id);
    //支付方式的信息
    $payment_info = array();
    $payment_info = payment_info($payment_id);
    /* 如果当前支付方式没有被禁用，进行支付的操作 */
    if (!empty($payment_info))
    {
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);
        //生成伪订单号
        $order['order_sn'] = $surplus_id;
        //获取需要支付的log_id
        $order['log_id'] = get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS);
        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $order['amount'];
        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);
        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $order['surplus_amount'] + $payment_info['pay_fee'];
        //如果支付费用改变了，也要相应的更改pay_log表的order_amount
        $order_amount = $db->getOne("SELECT order_amount FROM " .$hhs->table('pay_log')." WHERE log_id = '$order[log_id]'");
        if ($order_amount <> $order['order_amount'])
        {
            $db->query("UPDATE " .$hhs->table('pay_log').
                       " SET order_amount = '$order[order_amount]' WHERE log_id = '$order[log_id]'");
        }
        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');
        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);
        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('order',   $order);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($order['surplus_amount'], false));
        $smarty->assign('action',  'act_account');
        $smarty->display('user_transaction.dwt');
    }
    /* 重新选择支付方式 */
    else
    {
        include_once(ROOT_PATH . 'includes/lib_clips.php');
        $smarty->assign('payment', get_online_payment_list());
        $smarty->assign('order',   $order);
        $smarty->assign('action',  'account_deposit');
        $smarty->display('user_transaction.dwt');
    }
}
/* 添加标签(ajax) */
elseif ($action == 'add_tag')
{
    include_once('includes/cls_json.php');
    include_once('includes/lib_clips.php');
    $result = array('error' => 0, 'message' => '', 'content' => '');
    $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $tag    = isset($_POST['tag']) ? json_str_iconv(trim($_POST['tag'])) : '';
    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['tag_anonymous'];
    }
    else
    {
        add_tag($id, $tag); // 添加tag
        clear_cache_files('goods'); // 删除缓存
        /* 重新获得该商品的所有缓存 */
        $arr = get_tags($id);
        foreach ($arr AS $row)
        {
            $result['content'][] = array('word' => htmlspecialchars($row['tag_words']), 'count' => $row['tag_count']);
        }
    }
    $json = new JSON;
    echo $json->encode($result);
    exit;
}
/* 添加收藏商品(ajax) */
elseif ($action == 'collect')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_GET['id'];
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('collect_goods') .
            " WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $result['error'] = 1;
            $result['message'] = $GLOBALS['_LANG']['collect_existed'];
            die($json->encode($result));
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['hhs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die($json->encode($result));
            }
            else
            {
                $result['error'] = 0;
                $result['message'] = $GLOBALS['_LANG']['collect_success'];
                die($json->encode($result));
            }
        }
    }
}
/* 添加收藏商品(ajax) */
elseif ($action == 'collect_store')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_REQUEST['id'];
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('collect_store') .
            " WHERE user_id='$_SESSION[user_id]' AND suppliers_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $result['error'] = 2;
            $result['message'] ='该店铺已经在收藏夹中';
            die($json->encode($result));
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['hhs']->table('collect_store'). " (user_id, suppliers_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 3;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die($json->encode($result));
            }
            else
            {
                $result['error'] = 0;
                $result['message'] = '收藏店铺成功';
                die($json->encode($result));
            }
        }
    }
}
/* 删除留言 */
elseif ($action == 'del_msg')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    if ($id > 0)
    {
        $sql = 'SELECT user_id, message_img FROM ' .$hhs->table('feedback'). " WHERE msg_id = '$id' LIMIT 1";
        $row = $db->getRow($sql);
        if ($row && $row['user_id'] == $user_id)
        {
            /* 验证通过，删除留言，回复，及相应文件 */
            if ($row['message_img'])
            {
                @unlink(ROOT_PATH . DATA_DIR . '/feedbackimg/'. $row['message_img']);
            }
            $sql = "DELETE FROM " .$hhs->table('feedback'). " WHERE msg_id = '$id' OR parent_id = '$id'";
            $db->query($sql);
        }
    }
    hhs_header("Location: user.php?act=message_list&order_id=$order_id\n");
    exit;
}
/* 删除评论 */
elseif ($action == 'del_cmt')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0)
    {
        $sql = "DELETE FROM " .$hhs->table('comment'). " WHERE comment_id = '$id' AND user_id = '$user_id'";
        $db->query($sql);
    }
    hhs_header("Location: user.php?act=comment_list\n");
    exit;
}
/* 合并订单 */
elseif ($action == 'merge_order')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    include_once(ROOT_PATH .'includes/lib_order.php');
    $from_order = isset($_POST['from_order']) ? trim($_POST['from_order']) : '';
    $to_order   = isset($_POST['to_order']) ? trim($_POST['to_order']) : '';
    if (merge_user_order($from_order, $to_order, $user_id))
    {
        show_message($_LANG['merge_order_success'],$_LANG['order_list_lnk'],'user.php?act=order_list', 'info');
    }
    else
    {
        $err->show($_LANG['order_list_lnk']);
    }
}
/* 将指定订单中商品添加到购物车 */
elseif ($action == 'return_to_cart')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '', 'content' => '');
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if ($order_id == 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['order_id_empty'];
        die($json->encode($result));
    }
    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    /* 检查订单是否属于该用户 */
    $order_user = $db->getOne("SELECT user_id FROM " .$hhs->table('order_info'). " WHERE order_id = '$order_id'");
    if (empty($order_user))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }
    else
    {
        if ($order_user != $user_id)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['no_priv'];
            die($json->encode($result));
        }
    }
    $message = return_to_cart($order_id);
    if ($message === true)
    {
        $result['error'] = 0;
        $result['message'] = $_LANG['return_to_cart_success'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }
}
/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_surplus')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查余额 */
    $surplus = floatval($_POST['surplus']);
    if ($surplus <= 0)
    {
        $err->add($_LANG['error_surplus_invalid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }
    include_once(ROOT_PATH . 'includes/lib_order.php');
    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单是否未付款，检查应付款金额是否大于0 */
    if ($order['pay_status'] != PS_UNPAYED || $order['order_amount'] <= 0)
    {
        $err->add($_LANG['error_order_is_paid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }
    /* 计算应付款金额（减去支付费用） */
    $order['order_amount'] -= $order['pay_fee'];
    /* 余额是否超过了应付款金额，改为应付款金额 */
    if ($surplus > $order['order_amount'])
    {
        $surplus = $order['order_amount'];
    }
    /* 取得用户信息 */
    $user = user_info($_SESSION['user_id']);
    /* 用户帐户余额是否足够 */
    if ($surplus > $user['user_money'] + $user['credit_line'])
    {
        $err->add($_LANG['error_surplus_not_enough']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }
    /* 修改订单，重新计算支付费用 */
    $order['surplus'] += $surplus;
    $order['order_amount'] -= $surplus;
    if ($order['order_amount'] > 0)
    {
        $cod_fee = 0;
        if ($order['shipping_id'] > 0)
        {
            $regions  = array($order['country'], $order['province'], $order['city'], $order['district']);
            $shipping = shipping_area_info($order['shipping_id'], $regions);
            if ($shipping['support_cod'] == '1')
            {
                $cod_fee = $shipping['pay_fee'];
            }
        }
        $pay_fee = 0;
        if ($order['pay_id'] > 0)
        {
            $pay_fee = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);
        }
        $order['pay_fee'] = $pay_fee;
        $order['order_amount'] += $pay_fee;
    }
    /* 如果全部支付，设为已确认、已付款 */
    if ($order['order_amount'] == 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED)
        {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = gmtime();
        }
        $order['pay_status'] = PS_PAYED;
        $order['pay_time'] = gmtime();
    }
    $order = addslashes_deep($order);
    update_order($order_id, $order);
    /* 更新用户余额 */
    $change_desc = sprintf($_LANG['pay_order_by_surplus'], $order['order_sn']);
    log_account_change($user['user_id'], (-1) * $surplus, 0, 0, 0, $change_desc);
    /* 跳转 */
    hhs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
    exit;
}
/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_payment')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查支付方式 */
    $pay_id = intval($_POST['pay_id']);
    if ($pay_id <= 0)
    {
        hhs_header("Location: ./\n");
        exit;
    }
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $payment_info = payment_info($pay_id);
    if (empty($payment_info))
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        hhs_header("Location: ./\n");
        exit;
    }
    /* 检查订单是否未付款和未发货 以及订单金额是否为0 和支付id是否为改变*/
    if ($order['pay_status'] != PS_UNPAYED || $order['shipping_status'] != SS_UNSHIPPED || $order['goods_amount'] <= 0 || $order['pay_id'] == $pay_id)
    {
        hhs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
        exit;
    }
    $order_amount = $order['order_amount'] - $order['pay_fee'];
    $pay_fee = pay_fee($pay_id, $order_amount);
    $order_amount += $pay_fee;
    $sql = "UPDATE " . $hhs->table('order_info') .
           " SET pay_id='$pay_id', pay_name='$payment_info[pay_name]', pay_fee='$pay_fee', order_amount='$order_amount'".
           " WHERE order_id = '$order_id'";
    $db->query($sql);
    /* 跳转 */
    hhs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
    exit;
}
/* 保存订单详情收货地址 */
elseif ($action == 'save_order_address')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    $address = array(
        'consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'     => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'address'   => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'zipcode'   => isset($_POST['zipcode'])   ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        'tel'       => isset($_POST['tel'])       ? compile_str(trim($_POST['tel']))        : '',
        'mobile'    => isset($_POST['mobile'])    ? compile_str(trim($_POST['mobile']))     : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'best_time' => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'order_id'  => isset($_POST['order_id'])  ? intval($_POST['order_id']) : 0
        );
    if (save_order_address($address, $user_id))
    {
        hhs_header('Location: user.php?act=order_detail&order_id=' .$address['order_id']. "\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}
/* 我的优惠劵列表 */
elseif ($action == 'bonus')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('user_bonus'). " WHERE user_id = '$user_id'");
	// $smarty->assign('send_bouns',$_REQUEST['send_bouns']);
	if(isset($_REQUEST['send_bouns'])){
		add_bonus($user_id, $_REQUEST['send_bouns']);
	}
    //$pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $bonus = get_user_bouns_list2($user_id);
	//print_r($bonus);
    if($_REQUEST['status']=='not_start'){
        $smarty->assign('status', 'not_start');
        $arr=$bonus['not_start'];
        $bonus=array();
        $bonus['not_start']=$arr;
    }elseif($_REQUEST['status']=='overdue'){
        $smarty->assign('status', 'overdue');
        $arr=$bonus['overdue'];
        $bonus=array();
        $bonus['overdue']=$arr;
    }
    //$smarty->assign('pager', $pager);
    $smarty->assign('bonus', $bonus);
    $smarty->display('user_bonus.dwt');
}
// 用户推荐页面
elseif ($action == 'affiliate')
{
    $goodsid = intval(isset($_REQUEST['goodsid']) ? $_REQUEST['goodsid'] : 0);
    if(empty($goodsid))
    {
        //我的推荐页面
        $page       = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
        $size       = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
        empty($affiliate) && $affiliate = array();
        if(empty($affiliate['config']['separate_by']))
        {
            //推荐注册分成
            $affdb = array();
            $num = count($affiliate['item']);
            $up_uid = "'$user_id'";
            $all_uid = "'$user_id'";
            for ($i = 1 ; $i <=$num ;$i++)
            {
                $count = 0;
                if ($up_uid)
                {
                    $sql = "SELECT user_id FROM " . $hhs->table('users') . " WHERE parent_id IN($up_uid)";
                    $query = $db->query($sql);
                    $up_uid = '';
                    while ($rt = $db->fetch_array($query))
                    {
                        $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                        if($i < $num)
                        {
                            $all_uid .= ", '$rt[user_id]'";
                        }
                        $count++;
                    }
                }
                $affdb[$i]['num'] = $count;
                $affdb[$i]['point'] = $affiliate['item'][$i-1]['level_point'];
                $affdb[$i]['money'] = $affiliate['item'][$i-1]['level_money'];
            }
            $smarty->assign('affdb', $affdb);
            $sqlcount = "SELECT count(*) FROM " . $hhs->table('order_info') . " o".
        " LEFT JOIN".$hhs->table('users')." u ON o.user_id = u.user_id".
        " LEFT JOIN " . $hhs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND (u.parent_id IN ($all_uid) AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)";
            $sql = "SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type FROM " . $hhs->table('order_info') . " o".
                    " LEFT JOIN".$hhs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $hhs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND (u.parent_id IN ($all_uid) AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)".
                    " ORDER BY order_id DESC" ;
            /*
                SQL解释：
                订单、用户、分成记录关联
                一个订单可能有多个分成记录
                1、订单有效 o.user_id > 0
                2、满足以下之一：
                    a.直接下线的未分成订单 u.parent_id IN ($all_uid) AND o.is_separate = 0
                        其中$all_uid为该ID及其下线(不包含最后一层下线)
                    b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0
            */
            $affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_register_all'], $affiliate['config']['level_register_up'], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));
        }
        else
        {
            //推荐订单分成
            $sqlcount = "SELECT count(*) FROM " . $hhs->table('order_info') . " o".
                    " LEFT JOIN".$hhs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $hhs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id = '$user_id' AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)";
            $sql = "SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $hhs->table('order_info') . " o".
                    " LEFT JOIN".$hhs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $hhs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id = '$user_id' AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)" .
                    " ORDER BY order_id DESC" ;
            /*
                SQL解释：
                订单、用户、分成记录关联
                一个订单可能有多个分成记录
                1、订单有效 o.user_id > 0
                2、满足以下之一：
                    a.订单下线的未分成订单 o.parent_id = '$user_id' AND o.is_separate = 0
                    b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0
            */
            $affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));
        }
        $count = $db->getOne($sqlcount);
        $max_page = ($count> 0) ? ceil($count / $size) : 1;
        if ($page > $max_page)
        {
            $page = $max_page;
        }
        $res = $db->SelectLimit($sql, $size, ($page - 1) * $size);
        $logdb = array();
        while ($rt = $GLOBALS['db']->fetchRow($res))
        {
            if(!empty($rt['suid']))
            {
                //在affiliate_log有记录
                if($rt['separate_type'] == -1 || $rt['separate_type'] == -2)
                {
                    //已被撤销
                    $rt['is_separate'] = 3;
                }
            }
            $rt['order_sn'] = substr($rt['order_sn'], 0, strlen($rt['order_sn']) - 5) . "***" . substr($rt['order_sn'], -2, 2);
            $logdb[] = $rt;
        }
        $url_format = "user.php?act=affiliate&page=";
        $pager = array(
                    'page'  => $page,
                    'size'  => $size,
                    'sort'  => '',
                    'order' => '',
                    'record_count' => $count,
                    'page_count'   => $max_page,
                    'page_first'   => $url_format. '1',
                    'page_prev'    => $page > 1 ? $url_format.($page - 1) : "javascript:;",
                    'page_next'    => $page < $max_page ? $url_format.($page + 1) : "javascript:;",
                    'page_last'    => $url_format. $max_page,
                    'array'        => array()
                );
        for ($i = 1; $i <= $max_page; $i++)
        {
            $pager['array'][$i] = $i;
        }
        $smarty->assign('url_format', $url_format);
        $smarty->assign('pager', $pager);
        $smarty->assign('affiliate_intro', $affiliate_intro);
        $smarty->assign('affiliate_type', $affiliate['config']['separate_by']);
        $smarty->assign('logdb', $logdb);
    }
    else
    {
        //单个商品推荐
        $smarty->assign('userid', $user_id);
        $smarty->assign('goodsid', $goodsid);
        $types = array(1,2,3,4,5);
        $smarty->assign('types', $types);
        $goods = get_goods_info($goodsid);
        $shopurl = $hhs->url();
        $goods['goods_img'] = (strpos($goods['goods_img'], 'http://') === false && strpos($goods['goods_img'], 'https://') === false) ? $shopurl . $goods['goods_img'] : $goods['goods_img'];
        $goods['goods_thumb'] = (strpos($goods['goods_thumb'], 'http://') === false && strpos($goods['goods_thumb'], 'https://') === false) ? $shopurl . $goods['goods_thumb'] : $goods['goods_thumb'];
        $goods['shop_price'] = price_format($goods['shop_price']);
        $smarty->assign('goods', $goods);
    }
    $smarty->assign('shopname', $_CFG['shop_name']);
    $smarty->assign('userid', $user_id);
    $smarty->assign('shopurl', $hhs->url());
    $smarty->assign('logosrc', 'themes/' . $_CFG['template'] . '/images/logo.gif');
    $smarty->display('user_clips.dwt');
}
//首页邮件订阅ajax操做和验证操作
elseif ($action =='email_list')
{
    $job = $_GET['job'];
    if($job == 'add' || $job == 'del')
    {
        if(isset($_SESSION['last_email_query']))
        {
            if(time() - $_SESSION['last_email_query'] <= 30)
            {
                die($_LANG['order_query_toofast']);
            }
        }
        $_SESSION['last_email_query'] = time();
    }
    $email = trim($_GET['email']);
    $email = htmlspecialchars($email);
    if (!is_email($email))
    {
        $info = sprintf($_LANG['email_invalid'], $email);
        die($info);
    }
    $ck = $db->getRow("SELECT * FROM " . $hhs->table('email_list') . " WHERE email = '$email'");
    if ($job == 'add')
    {
        if (empty($ck))
        {
            $hash = substr(md5(time()), 1, 10);
            $sql = "INSERT INTO " . $hhs->table('email_list') . " (email, stat, hash) VALUES ('$email', 0, '$hash')";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $hhs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = sprintf($_LANG['email_alreadyin_list'], $email);
        }
        else
        {
            $hash = substr(md5(time()),1 , 10);
            $sql = "UPDATE " . $hhs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_re_check'];
            $url = $hhs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        die($info);
    }
    elseif ($job == 'del')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $hash = substr(md5(time()),1,10);
            $sql = "UPDATE " . $hhs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $hhs->url() . "user.php?act=email_list&job=del_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        die($info);
    }
    elseif ($job == 'add_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = $_LANG['email_checked'];
        }
        else
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "UPDATE " . $hhs->table('email_list') . "SET stat = 1 WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_checked'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
    elseif ($job == 'del_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_invalid'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "DELETE FROM " . $hhs->table('email_list') . "WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_canceled'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
}
/* ajax 发送验证邮件 */
elseif ($action == 'send_hash_mail')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    include_once(ROOT_PATH .'includes/lib_passport.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '', 'content' => '');
    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    if (send_regiter_hash($user_id))
    {
        $result['message'] = $_LANG['validate_mail_ok'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $GLOBALS['err']->last_message();
    }
    die($json->encode($result));
}
else if ($action == 'order_query')
{
    $_GET['order_sn'] = trim(substr($_GET['order_sn'], 1));
    $order_sn = empty($_GET['order_sn']) ? '' : addslashes($_GET['order_sn']);
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();
    $result = array('error'=>0, 'message'=>'', 'content'=>'');
    if(isset($_SESSION['last_order_query']))
    {
        if(time() - $_SESSION['last_order_query'] <= 10)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['order_query_toofast'];
            die($json->encode($result));
        }
    }
    $_SESSION['last_order_query'] = time();
    if (empty($order_sn))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }
    $sql = "SELECT order_id, order_status, shipping_status, pay_status, ".
           " shipping_time, shipping_id, invoice_no, user_id ".
           " FROM " . $hhs->table('order_info').
           " WHERE order_sn = '$order_sn' LIMIT 1";
    $row = $db->getRow($sql);
    if (empty($row))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }
    $order_query = array();
    $order_query['order_sn'] = $order_sn;
    $order_query['order_id'] = $row['order_id'];
    $order_query['order_status'] = $_LANG['os'][$row['order_status']] . ',' . $_LANG['ps'][$row['pay_status']] . ',' . $_LANG['ss'][$row['shipping_status']];
    if ($row['invoice_no'] && $row['shipping_id'] > 0)
    {
        $sql = "SELECT shipping_code FROM " . $hhs->table('shipping') . " WHERE shipping_id = '$row[shipping_id]'";
        $shipping_code = $db->getOne($sql);
        $plugin = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
        if (file_exists($plugin))
        {
            include_once($plugin);
            $shipping = new $shipping_code;
            $order_query['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
        else
        {
            $order_query['invoice_no'] = (string)$row['invoice_no'];
        }
    }
    $order_query['user_id'] = $row['user_id'];
    /* 如果是匿名用户显示发货时间 */
    if ($row['user_id'] == 0 && $row['shipping_time'] > 0)
    {
        $order_query['shipping_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['shipping_time']);
    }
    $smarty->assign('order_query',    $order_query);
    $result['content'] = $smarty->fetch('library/order_query.lbi');
    die($json->encode($result));
}
/**
 * 分销列表
 * @var [type]
 */
elseif ($action == 'fenxiao')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
	$smarty->assign('info2',        get_user_default($user_id));
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
	$smarty->assign('surplus_amount', $surplus_amount);
	//获取剩余积分
	$points = get_user_points($user_id);
	$smarty->assign('points', $points);
	$level = isset($_REQUEST['level']) ? intval($_REQUEST['level']) : 0;
    $smarty->assign('level', $level);
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page = $page > 0 ? $page : 1;
	$fenxiao = getMoneyList($user_id,$page,$level);
    $smarty->assign('fenxiao', $fenxiao);
    $record_count = getMoneyListCount($user_id,$level);
	$pager  = get_pager('user.php', array('act' => $action,'level' => $level), $record_count, $page);
    $smarty->assign('pager', $pager);
	$amount = getMoneyAmount($user_id);
    $smarty->assign('amount', $amount);
	$info = getPidInfo($user_id);
    $smarty->assign('info', $info);
    $smarty->assign('root', $_SERVER['HTTP_HOST']);
	$level1_nums = getFollowsNum($user_id,1);
	$level2_nums = getFollowsNum($user_id,2);
	$level3_nums = getFollowsNum($user_id,3);
	$all_nums = $level1_nums + $level2_nums + $level3_nums;
    $smarty->assign('level1_nums', $level1_nums);
    $smarty->assign('level2_nums', $level2_nums);
    $smarty->assign('level3_nums', $level3_nums);
    $smarty->assign('all_nums', $all_nums);
	$checkedAmount    = price_format(getMoneyAmount($user_id,1));
	$notCheckedAmount = price_format(getMoneyAmount($user_id,0));
    $smarty->assign('checkedAmount', $checkedAmount);
    $smarty->assign('notCheckedAmount', $notCheckedAmount);
    $smarty->assign('record_count', $record_count);
    $smarty->display('user_fenxiao.dwt');
}
elseif ($action == 'level')
{
	$level = isset($_REQUEST['level']) ? intval($_REQUEST['level']) : 0;
    $smarty->assign('level', $level);
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page = $page > 0 ? $page : 1;
	$follows = getFollows($user_id,$level,$page);
    $smarty->assign('follows', $follows);
    $record_count = getFollowsNum($user_id,$level);
	$pager  = get_pager('user.php', array('act' => $action,'level' => $level), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->display('user_level.dwt');
}
elseif ($action == 'money' || $action == 'moneycheck')
{
	$checked = isset($_REQUEST['checked']) ? intval($_REQUEST['checked']) : '';
    $smarty->assign('checked', $checked);
	$level = isset($_REQUEST['level']) ? intval($_REQUEST['level']) : 0;
	$uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;
    $smarty->assign('level', $level);
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$page = $page > 0 ? $page : 1;
	$moneyList = getMoneyList($user_id,$page,$level,$checked,$uid);
    $smarty->assign('moneyList', $moneyList);
    $record_count = getMoneyListCount($user_id,$level,$checked,$uid);
	$pager  = get_pager('user.php', array('act' => $action,'level' => $level), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('action', $action);
    $smarty->display('user_money.dwt');
}
/* 清除商品浏览历史 */
elseif ($action == 'clear_history')
{
    setcookie('HHS[history]',   '', 1);
}
elseif ($action == 'lottery_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $composite_status = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
    $where=" and pay_status =2 and is_luck = 1 ";
    //未付款
    if($_REQUEST['composite_status'] =='100')
    {
        $where .= " and pay_status =2 and team_status=1 ";
    }
    //待收货
    if($_REQUEST['composite_status'] =='120')
    {
        $where .= " and order_status in (0,1,5) and team_status=2";
    }
    //評論
    if($_REQUEST['composite_status'] =='999')
    {
        $where .= "  and pay_status=2 and shipping_status=2 and is_comm = 0 ";
    }
    //include_once(ROOT_PATH . 'wxpay/demo/wxch_order.php');
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('order_info'). " WHERE user_id = '$user_id'" .$where);
    $pager  = get_pager('user.php', array('act' => $action,'composite_status'=>$_REQUEST['composite_status']), $record_count, $page);
    $orders = get_user_orders_ex($user_id, $pager['size'], $pager['start'],$where);
    $merge  = get_user_merge($user_id);
	foreach ($orders as $key => $value)
	{
		$orders[$key]['team_short_num'] = intval($value['team_num']-$value['teammen_num']);
		$orders[$key]['team_num_per'] = round(($value['teammen_num']/$value['team_num'])*100).'%';
	}
	$smarty->assign('root', $_SERVER['HTTP_HOST']);
    $smarty->assign('merge',  $merge);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->assign('composite_status',  $_REQUEST['composite_status']);
    $smarty->display('user_lottery_list.dwt');
}
function get_regions_name($region_id)
{
    return $GLOBALS['db']->getOne("select region_name from ".$GLOBALS['hhs']->table('region')." where region_id='$region_id'");
}
?>