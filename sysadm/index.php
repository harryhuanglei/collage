<?php
/**
 * 小舍电商 控制台首页
 * ============================================================================
 * * 版权所有 2005-2012 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ============================================================================
 * $Author: pangbin $
 * $Id: index.php 17217 2011-01-19 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/includes/lib_order.php');
require(dirname(__FILE__) . '/includes/fun_stats.php');
/*------------------------------------------------------ */
//-- 框架
/*------------------------------------------------------ */
if ($_REQUEST['act'] == '')
{
	/*顶部配置*/
    // 获得管理员设置的菜单
    $lst = array();
    $nav = $db->GetOne('SELECT nav_list FROM ' . $hhs->table('admin_user') . " WHERE user_id = '" . $_SESSION['admin_id'] . "'");

    if (!empty($nav))
    {
        $arr = explode(',', $nav);

        foreach ($arr AS $val)
        {
            $tmp = explode('|', $val);
            $lst[$tmp[1]] = $tmp[0];
        }
    }

    // 获得管理员设置的菜单

    // 获得管理员ID
    $smarty->assign('send_mail_on',$_CFG['send_mail_on']);
    $smarty->assign('nav_list', $lst);
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('certi', $_CFG['certi']);
	
	$smarty->assign('hhs_version',  VERSION);
	
	
    include_once('includes/inc_menu.php');
    include_once('includes/inc_priv.php');

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    foreach ($modules AS $key => $val)
    {
        $menus[$key]['label'] = $_LANG[$key];
		$menus[$key]['lang'] = $key;
		$menus[$key]['font'] = $_FONT[$key];
        if (is_array($val))
        {
            foreach ($val AS $k => $v)
            {
                if ( isset($purview[$k]))
                {
                    if (is_array($purview[$k]))
                    {
                        $boole = false;
                        foreach ($purview[$k] as $action)
                        {
                             $boole = $boole || admin_priv($action, '', false);
                        }
                        if (!$boole)
                        {
                            continue;
                        }

                    }
                    else
                    {
                        if (! admin_priv($purview[$k], '', false))
                        {
                            continue;
                        }
                    }
                }
                if ($k == 'ucenter_setup' && $_CFG['integrate_code'] != 'ucenter')
                {
                    continue;
                }
                $menus[$key]['children'][$k]['label']  = $_LANG[$k];
                $menus[$key]['children'][$k]['action'] = $v;
            }
        }
        else
        {
            $menus[$key]['action'] = $val;
        }

        // 如果children的子元素长度为0则删除该组
        if(empty($menus[$key]['children']))
        {
            unset($menus[$key]);
        }

    }


    // $sql="select * from ".$hhs->table('order_info')." where extension_code='team_goods' and (team_status=3 or team_status=1) and team_first=1 and pay_status=2 and add_time>1440204356 ";
    // $order_list=$db->getAll($sql);
    
    // if(!empty($order_list) ){
		
    //     require_once(ROOT_PATH . 'includes/lib_payment.php');
    //     require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
    //     foreach($order_list as $v){
		
    //         if($v['team_status']==1){
    //             $sql="select pay_time from ".$hhs->table('order_info')." where order_id=".$v['team_sign'];
    //             $pay_time=$db->getOne($sql);
    //             if(gmtime()-$pay_time >$GLOBALS['_CFG']['team_suc_time']*24*3600 ){
                    
    //                 $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2 where  team_sign=".$v['team_sign'];
    //                 $GLOBALS['db']->query($sql);

    //                 $sql="select * from ".$GLOBALS['hhs']->table('order_info')." where team_sign=".$v['team_sign'];
    //                 $team_list= $GLOBALS['db']->getAll($sql);
    //                 foreach($team_list as $f){
    //                     $order_sn=$f['order_sn'];

    //                     $r=refund($f['order_sn'],$f['money_paid']*100);
                         
    //                     if($r){
    //                         $arr=array();
    //                         $arr['order_status']    = OS_RETURNED;
    //                         $arr['pay_status']  = PS_REFUNDED;
    //                         $arr['shipping_status'] = 0;
    //                         $arr['team_status']  = 3;
    //                         $arr['money_paid']  = 0;
    //                         $arr['order_amount']= $f['money_paid'] + $f['order_amount'];
    //                         update_order($f['order_id'], $arr);
                        
    //                         $user_id=$f['user_id'];
    //                         $wxch_order_name='refund';
    //                         $team_sign=$f['team_sign'];
    //                         $order_id=$f['order_id'];
    //                         include_once(ROOT_PATH . 'wxch_order.php');
                        
    //                     }
    //                 }
					  
    //             }
    //         }
            
    //         if($v['team_status']==3){
    //             $sql="select * from ".$GLOBALS['hhs']->table('order_info')." where team_sign=".$v['team_sign'];
    //             $team_list= $GLOBALS['db']->getAll($sql);
    //             foreach($team_list as $f){
    //                 $order_sn=$f['order_sn'];
    //                 $r= refund($order_sn,$f['money_paid']*100);
                    
    //                 if($r){
    //                     $arr=array();
    //                     $arr['order_status']    = OS_RETURNED;
    //                     $arr['pay_status']  = PS_REFUNDED;
    //                     $arr['shipping_status'] = 0;
    //                     $arr['team_status']  = 3;
    //                     $arr['money_paid']  = 0;
    //                     $arr['order_amount']= $f['money_paid'] + $f['order_amount'];
    //                     update_order($f['order_id'], $arr);
                    
    //                     $user_id=$f['user_id'];
    //                     $wxch_order_name='refund';
    //                     $team_sign=$f['team_sign'];
    //                     $order_id=$f['order_id'];
    //                     include_once(ROOT_PATH . 'wxch_order.php');
    //                 }
    //             }
                

    //         }
           
    //     }
    
    // }
    
  
    $smarty->assign('menus',     $menus);
    $smarty->assign('no_help',   $_LANG['no_help']);
    $smarty->assign('help_lang', $_CFG['lang']);
    $smarty->assign('charset', EC_CHARSET);
    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('shop_url', urlencode($hhs->url()));
    
    $smarty->display('index.htm');

}

//
elseif ($_REQUEST['act'] == 'changeteamstatus')
{
    // 自动收货
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_fenxiao.php');
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $now = gmtime();
    $affirm_received_time = $GLOBALS['_CFG']['affirm_received_time']*24*3600;
    $sql = "select order_id,user_id from ".$hhs->table('order_info')." where `shipping_status` = 1 and `shipping_time` < " . ($now - $affirm_received_time) .' limit 50';
    $rows = $db->getAll($sql);
    foreach ($rows as $key => $row) {
        $order_id = $row['order_id'];
        $user_id  = $row['user_id'];
       if (affirm_received($order_id, $user_id))
       {
            //分销更新状态
            $update_at = gmtime();
            updateMoney($order_id,$update_at);
            //发送红包，据说会重复发送
            // $bonus_list=send_order_bonus($order_id);        
       }
    }
    // end自动收货

    $old_pay_time = gmtime() - $GLOBALS['_CFG']['team_suc_time']*24*3600;
    $sql="select team_sign,pay_time from ".$hhs->table('order_info')." where is_luck = 0 and  extension_code='team_goods' and team_status in(1,3) and team_first=1 and pay_status=2 and pay_time< ".$old_pay_time." order by order_id desc LIMIT 20";

    $order_list=$db->getAll($sql);
    $nums = count($rows);
    if(!empty($order_list) ){
        
        require_once(ROOT_PATH . 'includes/lib_order.php');
        require_once(ROOT_PATH . 'includes/lib_payment.php');
        require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
        foreach($order_list as $v){
            ++$nums;
        
            // $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2 where  team_sign=".$v['team_sign'];
            // $GLOBALS['db']->query($sql);

            $sql="select o.`order_sn`,o.`order_id`,o.`money_paid`,o.`order_amount`,o.`team_sign`,u.`openid` from ".$GLOBALS['hhs']->table('order_info')." as o LEFT JOIN ".$GLOBALS['hhs']->table('users')." as u on u.`user_id` = o.`user_id` where o.pay_status=2 and  team_sign=".$v['team_sign'];
            $team_list= $GLOBALS['db']->getAll($sql);
            foreach($team_list as $f){
                $order_sn=$f['order_sn'];
                $r= refund($order_sn,$f['money_paid']*100);
                
                if($r){
                    $arr=array();
                    $arr['order_status']    = OS_RETURNED;
                    $arr['pay_status']  = PS_REFUNDED;
                    $arr['shipping_status'] = 0;
                    $arr['team_status']  = 3;
                    $arr['money_paid']  = 0;
                    $arr['order_amount']= $f['money_paid'] + $f['order_amount'];
                    update_order($f['order_id'], $arr);

                    $openid = $f['openid'];
                    $title  = '退款提醒';
                    $url    = 'user.php?act=order_detail&order_id='.$f['order_id'];
                    $desc   = "您的订单已经成功退款，记得常来看看哦";

                    $weixin = new class_weixin($appid,$appsecret);
                    $weixin->send_wxmsg($openid, $title , $url , $desc );

                    change_order_goods_storage($f['order_id'], false, SDT_PLACE);
                }                

            }
           
        }
    
    }

    make_json_result($nums);
}
/*订单未支付通知提醒*/
elseif ($_REQUEST['act'] == 'cancel_order_remind')
{
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $unpaidOrder = getOrderUnpaid();
    $unpaidOrderDone = getOrderUnpaid(true);
    $cancel_order_remind = isset($_CFG['cancel_order_remind']) ? floatval($_CFG['cancel_order_remind']) : 6;
    $cancel_order_done = isset($_CFG['cancel_order_done']) ? floatval($_CFG['cancel_order_done']) : 10;
    /*支付提醒statr*/
    if($unpaidOrder)
    {
        foreach ($unpaidOrder as $key => $value) 
        {
            /*判断是否存在openid*/
            if($value['openid'])
            {
                $nums = 1;
                $shopinfo = '';
                $url = 'user.php?act=order_detail&order_id='.$value['order_id'];
                if(($value['add_time']+$cancel_order_remind*3600) <= gmtime())
                {
                    $arr['cancel_remind'] = 1;
                    /*添加记录*/
                    order_action($value['order_sn'], $value['order_status'], $value['shipping_status'], $value['pay_status'], '订单支付提醒');
                    $title = '订单支付提醒';
                    $remindInfo = '您的订单将于'.round($cancel_order_done-$cancel_order_remind,2).'小时后取消，请点击立即支付';
                    /*更新订单*/
                    update_order($value['order_id'], $arr);
                    if(!empty($value['order_goods']))
                    {
                        foreach($value['order_goods'] as $v)
                        {
                            if(empty($v['goods_attr']))
                            {
                                $shopinfo .= $v['goods_name'].'('.$v['goods_number'].'),';
                            }
                            else
                            {
                                $shopinfo .= $v['goods_name'].'（'.$v['goods_attr'].'）'.'('.$v['goods_number'].'),';
                            }
                        }
                        $shopinfo = substr($shopinfo, 0, strlen($shopinfo)-1);
                    }
                    /*发送信息*/
                    $desc   = '订单号：'.$value['order_sn']."\r\n".'商品信息：'.$shopinfo."\r\n".$remindInfo;
                    $weixin = new class_weixin($appid,$appsecret);
                    $weixin->send_wxmsg($value['openid'], $title , $url , $desc );
                }
            }  
        }
    }
    /*支付提醒end*/
    /*取消未支付订单*/
    if($unpaidOrderDone)
    {
        foreach ($unpaidOrderDone as $key => $value) 
        {
            /*判断是否存在openid*/
            if($value['openid'])
            {
                $nums = 2;
                $shopinfo = '';
                $url = 'index.php';
                if(($value['add_time']+$cancel_order_done*3600) <= gmtime())
                {
                    $title = '订单取消提醒';
                    $arr['order_status']    = 2;
                    $arr['pay_status']  = 0;
                    $arr['shipping_status'] = 0;
                    /*添加记录*/
                    order_action($value['order_sn'], $arr['order_status'], $arr['shipping_status'], $arr['pay_status'], '系统取消未支付单');
                    $remindInfo = '您的订单系统自动取消，点击进入商城';
                    /*更新订单*/
                    update_order($value['order_id'], $arr);
                    if(!empty($value['order_goods']))
                    {
                        foreach($value['order_goods'] as $v)
                        {
                            if(empty($v['goods_attr']))
                            {
                                $shopinfo .= $v['goods_name'].'('.$v['goods_number'].'),';
                            }
                            else
                            {
                                $shopinfo .= $v['goods_name'].'（'.$v['goods_attr'].'）'.'('.$v['goods_number'].'),';
                            }
                        }
                        $shopinfo = substr($shopinfo, 0, strlen($shopinfo)-1);
                    }
                    /*发送信息*/
                    $desc   = '订单号：'.$value['order_sn']."\r\n".'商品信息：'.$shopinfo."\r\n".$remindInfo;
                    $weixin = new class_weixin($appid,$appsecret);
                    $weixin->send_wxmsg($value['openid'], $title , $url , $desc );
                }
            }  
        }
    }
    make_json_result($nums);
}
elseif ($_REQUEST['act'] == 'sendlatestnews')
{
   //make_json_error('暂时禁止了');
    $article = $db->getRow("select article_id,title,description,file_url,wx_url from ".$hhs->table('article')." where cat_id='15' order by article_id desc");
  
	
	
	if(empty($article))
    {
        make_json_error('请先添加推送文章');
        exit();
    }

    $last_login = gmtime() - 86400*2;
    $today = strtotime(date("Y-m-d"));

    $sql = "SELECT openid,user_id FROM ".$hhs->table('users')." WHERE `is_subscribe` = 1 and last_login > " . $last_login . " AND is_send < " . $today . " LIMIT 300";

    $rows = $db->getAll($sql);
    foreach ($rows as $key => $row) {
        $openid = $row['openid'];
        $title  = $article['title'];
        $desc   = $article['description'];
        $wx_url    = $article['wx_url'] ? $article['wx_url'] :'article.php?id='.$article['article_id'];
        $picurl = $article['file_url'];
        $weixin = new class_weixin($appid,$appsecret);
        $weixin->send_wxmsg($openid, $title , $wx_url , $desc ,$picurl);
        $db->query("update ".$hhs->table('users')." set is_send = ".$today." WHERE user_id = " . $row['user_id']);
    }
    $nums = count($rows);
    if($nums)
    {
        make_json_result($nums);
        exit();
    }
    else{
        make_json_error('发送完毕！');
        exit();
    }
}
/*------------------------------------------------------ */
//-- 计算器
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'calculator')
{
    $smarty->display('calculator.htm');
}

/*------------------------------------------------------ */
//-- 清除缓存
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'clear_cache')
{
    clear_all_files();

    sys_msg($_LANG['caches_cleared']);
}


/*------------------------------------------------------ */
//-- 主窗口，起始页
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'main')
{

    //后台页面曲线数据
    $time  = gmtime();
    $date  = date("Y-m-d",($time-86400));
    $start = local_strtotime($date);
    $end   = $start + 86400;

    $all['order_nums']         = getOrderTypeNums();
    $all['goods_nums']         = getGoodsNums();
    $all['group_success_nums'] = getGroupNums(0,0,2);
    $all['group_failed_nums']  = getGroupNums(0,0,3);
    $all['user_nums']          = getUserNums();
    $all['amount']             = getOrderAmount();

    $yesterday['payed_order_nums']     = getOrderTypeNums($start,$end);
    $yesterday['await_pay_order_nums'] = getOrderTypeNums($start,$end,'await_pay');
    $yesterday['group_success_nums']   = getGroupNums($start,$end,2);
    $yesterday['group_failed_nums']    = getGroupNums($start,$end,3);
    $yesterday['user_nums']            = getUserNums($start,$end);
    $yesterday['amount']               = getOrderAmount($start,$end);

    $goods_list = getGoodsOrders();
    /*发货订单start*/
    $yesterday['paidOrderNum']     = GetOrderNum('','finished');
    $yesterday['unConfirmed'] = GetOrderNum('','unconfirmed');
    $yesterday['awaitShip']   = GetOrderNum('','await_ship');
    $yesterday['orderRefund']   = GetOrderNum('','refund');
    
    /*发货订单end*/
    /*自提订单start*/
    $yesterday['paidOrderNumPoint']     = GetOrderNum(1,'finished');
    $yesterday['unConfirmedPoint'] = GetOrderNum(1,'unconfirmed');
    $yesterday['awaitShipPoint']   = GetOrderNum(1,'await_ship');
    $yesterday['orderRefundPoint']   = GetOrderNum(1,'refund');
    /*自提订单end*/
    $user_list  = getUserOrders();
    $year_stats = getYearStats();
    $data = getFullMonStats();
    $full_mon_stats = $data['data'];
    $yaxis = join(',',$data['yaxis']);

    $fenxiao = getFenxiaoStatus();

    $smarty->assign('table', compact('all','yesterday','goods_list','user_list','year_stats','full_mon_stats','yaxis','fenxiao'));
    
    //开店向导第一步
    if(isset($_SESSION['shop_guide']) && $_SESSION['shop_guide'] === true)
    {
        unset($_SESSION['shop_guide']);//销毁session

        hhs_header("Location: ./index.php?act=first\n");

        exit();
    }

    $gd = gd_version();

    /* 检查文件目录属性 */
    $warning = array();

    if ($_CFG['shop_closed'])
    {
        $warning[] = $_LANG['shop_closed_tips'];
    }

    if (file_exists('../install'))
    {
        $warning[] = $_LANG['remove_install'];
    }

    if (file_exists('../upgrade'))
    {
        $warning[] = $_LANG['remove_upgrade'];
    }
    
    if (file_exists('../demo'))
    {
        $warning[] = $_LANG['remove_demo'];
    }

    $open_basedir = ini_get('open_basedir');
    if (!empty($open_basedir))
    {
        /* 如果 open_basedir 不为空，则检查是否包含了 upload_tmp_dir  */
        $open_basedir = str_replace(array("\\", "\\\\"), array("/", "/"), $open_basedir);
        $upload_tmp_dir = ini_get('upload_tmp_dir');

        if (empty($upload_tmp_dir))
        {
            if (stristr(PHP_OS, 'win'))
            {
                $upload_tmp_dir = getenv('TEMP') ? getenv('TEMP') : getenv('TMP');
                $upload_tmp_dir = str_replace(array("\\", "\\\\"), array("/", "/"), $upload_tmp_dir);
            }
            else
            {
                $upload_tmp_dir = getenv('TMPDIR') === false ? '/tmp' : getenv('TMPDIR');
            }
        }

        if (!stristr($open_basedir, $upload_tmp_dir))
        {
            $warning[] = sprintf($_LANG['temp_dir_cannt_read'], $upload_tmp_dir);
        }
    }

    $result = file_mode_info('../cert');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'cert', $_LANG['cert_cannt_write']);
    }

    $result = file_mode_info('../' . DATA_DIR);
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'data', $_LANG['data_cannt_write']);
    }
    else
    {
        $result = file_mode_info('../' . DATA_DIR . '/afficheimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/afficheimg', $_LANG['afficheimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/brandlogo');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/brandlogo', $_LANG['brandlogo_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/cardimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/cardimg', $_LANG['cardimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/feedbackimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/feedbackimg', $_LANG['feedbackimg_cannt_write']);
        }

        $result = file_mode_info('../' . DATA_DIR . '/packimg');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], DATA_DIR . '/packimg', $_LANG['packimg_cannt_write']);
        }
    }

    $result = file_mode_info('../images');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['images_cannt_write']);
    }
    else
    {
        $result = file_mode_info('../' . IMAGE_DIR . '/upload');
        if ($result < 2)
        {
            $warning[] = sprintf($_LANG['not_writable'], IMAGE_DIR . '/upload', $_LANG['imagesupload_cannt_write']);
        }
    }

    $result = file_mode_info('../temp');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['tpl_cannt_write']);
    }

    $result = file_mode_info('../temp/backup');
    if ($result < 2)
    {
        $warning[] = sprintf($_LANG['not_writable'], 'images', $_LANG['tpl_backup_cannt_write']);
    }

    if (!is_writeable('../' . DATA_DIR . '/order_print.html'))
    {
        $warning[] = $_LANG['order_print_canntwrite'];
    }
    clearstatcache();

    $smarty->assign('warning_arr', $warning);
    

    /* 管理员留言信息 */
    $sql = 'SELECT message_id, sender_id, receiver_id, sent_time, readed, deleted, title, message, user_name ' .
    'FROM ' . $hhs->table('admin_message') . ' AS a, ' . $hhs->table('admin_user') . ' AS b ' .
    "WHERE a.sender_id = b.user_id AND a.receiver_id = '$_SESSION[admin_id]' AND ".
    "a.readed = 0 AND deleted = 0 ORDER BY a.sent_time DESC";
    $admin_msg = $db->GetAll($sql);

    $smarty->assign('admin_msg', $admin_msg);

    /* 取得支持货到付款和不支持货到付款的支付方式 */
    $ids = get_pay_ids();

    /* 已完成的订单 */
    $order['finished']     = $db->GetOne('SELECT COUNT(*) FROM ' . $hhs->table('order_info').
    " WHERE 1 " . order_query_sql('finished'));
    $status['finished']    = CS_FINISHED;

    /* 待发货的订单： */
    $where.= " and ((o.extension_code='team_goods' and o.team_status=2  ".order_query_sql('await_ship').") or (o.extension_code!='team_goods' ".order_query_sql('await_ship').") )";
    
    $order['await_ship']   = $db->GetOne('SELECT COUNT(*)'.
    ' FROM ' .$hhs->table('order_info') .
    " as o WHERE 1 " .$where );
    $status['await_ship']  = CS_AWAIT_SHIP;
    
    /* 待付款的订单： */
    $order['await_pay']    = $db->GetOne('SELECT COUNT(*)'.
    ' FROM ' .$hhs->table('order_info') .
    " WHERE 1 " . order_query_sql('await_pay'));
    $status['await_pay']   = CS_AWAIT_PAY;

    /* “未确认”的订单 */
    $order['unconfirmed']  = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('order_info').
    " WHERE 1 " . order_query_sql('unconfirmed'));
    $status['unconfirmed'] = OS_UNCONFIRMED;

    /* “部分发货”的订单 */
    $order['shipped_part']  = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('order_info').
    " WHERE  shipping_status=" .SS_SHIPPED_PART);
    $status['shipped_part'] = OS_SHIPPED_PART;

//    $today_start = mktime(0,0,0,date('m'),date('d'),date('Y'));
    $order['stats']        = $db->getRow('SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount' .
    ' FROM ' .$hhs->table('order_info'));

    $smarty->assign('order', $order);
    $smarty->assign('status', $status);

    /* 商品信息 */
    $goods['total']   = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real = 1');
    $virtual_card['total'] = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['new']     = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_new = 1 AND is_real = 1');
    $virtual_card['new']     = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_new = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['best']    = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_best = 1 AND is_real = 1');
    $virtual_card['best']    = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_best = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $goods['hot']     = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_hot = 1 AND is_real = 1');
    $virtual_card['hot']     = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_hot = 1 AND is_real=0 AND extension_code=\'virtual_card\'');

    $time             = gmtime();
    $goods['promote'] = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND promote_price>0' .
    " AND promote_start_date <= '$time' AND promote_end_date >= '$time' AND is_real = 1");
    $virtual_card['promote'] = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND promote_price>0' .
    " AND promote_start_date <= '$time' AND promote_end_date >= '$time' AND is_real=0 AND extension_code='virtual_card'");

    /* 缺货商品 */
    if ($_CFG['use_storage'])
    {
        $sql = 'SELECT COUNT(*) FROM ' .$hhs->table('goods'). ' WHERE is_delete = 0 AND goods_number <= warn_number AND is_real = 1';
        $goods['warn'] = $db->GetOne($sql);
        $sql = 'SELECT COUNT(*) FROM ' .$hhs->table('goods'). ' WHERE is_delete = 0 AND goods_number <= warn_number AND is_real=0 AND extension_code=\'virtual_card\'';
        $virtual_card['warn'] = $db->GetOne($sql);
    }
    else
    {
        $goods['warn'] = 0;
        $virtual_card['warn'] = 0;
    }
    $smarty->assign('goods', $goods);
    $smarty->assign('virtual_card', $virtual_card);

    /* 访问统计信息 */
    $today  = local_getdate();
    $sql    = 'SELECT COUNT(*) FROM ' .$hhs->table('stats').
    ' WHERE access_time > ' . (mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']) - date('Z'));

    $today_visit = $db->GetOne($sql);
    $smarty->assign('today_visit', $today_visit);

    $online_users = $sess->get_users_count();
    $smarty->assign('online_users', $online_users);

    /* 最近反馈 */
    $sql = "SELECT COUNT(f.msg_id) ".
    "FROM " . $hhs->table('feedback') . " AS f ".
    "LEFT JOIN " . $hhs->table('feedback') . " AS r ON r.parent_id=f.msg_id " .
    'WHERE f.parent_id=0 AND ISNULL(r.msg_id) ' ;
    $smarty->assign('feedback_number', $db->GetOne($sql));

    /* 未审核评论 */
    $smarty->assign('comment_number', $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('comment') .
    ' WHERE status = 0 AND parent_id = 0'));

    $mysql_ver = $db->version();   // 获得 MySQL 版本

    /* 系统信息 */
    $sys_info['os']            = PHP_OS;
    $sys_info['ip']            = $_SERVER['SERVER_ADDR'];
    $sys_info['web_server']    = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['php_ver']       = PHP_VERSION;
    $sys_info['mysql_ver']     = $mysql_ver;
    $sys_info['zlib']          = function_exists('gzclose') ? $_LANG['yes']:$_LANG['no'];
    $sys_info['safe_mode']     = (boolean) ini_get('safe_mode') ?  $_LANG['yes']:$_LANG['no'];
    $sys_info['safe_mode_gid'] = (boolean) ini_get('safe_mode_gid') ? $_LANG['yes'] : $_LANG['no'];
    $sys_info['timezone']      = function_exists("date_default_timezone_get") ? date_default_timezone_get() : $_LANG['no_timezone'];
    $sys_info['socket']        = function_exists('fsockopen') ? $_LANG['yes'] : $_LANG['no'];

    if ($gd == 0)
    {
        $sys_info['gd'] = 'N/A';
    }
    else
    {
        if ($gd == 1)
        {
            $sys_info['gd'] = 'GD1';
        }
        else
        {
            $sys_info['gd'] = 'GD2';
        }

        $sys_info['gd'] .= ' (';

        /* 检查系统支持的图片类型 */
        if ($gd && (imagetypes() & IMG_JPG) > 0)
        {
            $sys_info['gd'] .= ' JPEG';
        }

        if ($gd && (imagetypes() & IMG_GIF) > 0)
        {
            $sys_info['gd'] .= ' GIF';
        }

        if ($gd && (imagetypes() & IMG_PNG) > 0)
        {
            $sys_info['gd'] .= ' PNG';
        }

        $sys_info['gd'] .= ')';
    }

    /* IP库版本 */
    $sys_info['ip_version'] = hhs_geoip('255.255.255.0');

    /* 允许上传的最大文件大小 */
    $sys_info['max_filesize'] = ini_get('upload_max_filesize');

    $smarty->assign('sys_info', $sys_info);

    /* 缺货登记 */
    $smarty->assign('booking_goods', $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('booking_goods') . ' WHERE is_dispose = 0'));

    /* 退款申请 */
    $smarty->assign('new_repay', $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('user_account') . ' WHERE process_type = ' . SURPLUS_RETURN . ' AND is_paid = 0 '));
	
	/* 退换货申请 */
	$smarty->assign('refund_goods', $GLOBALS['db']->getOne("select count(*) from ".$GLOBALS['hhs']->table("order_goods")." where refund_status=1") );


    assign_query_info();
    
    $smarty->assign('hhs_release',  RELEASE);
    $smarty->assign('hhs_lang',     $_CFG['lang']);
    $smarty->assign('hhs_charset',  strtoupper(EC_CHARSET));
    $smarty->assign('install_date', local_date($_CFG['date_format'], $_CFG['install_date']));
    $smarty->display('start.htm');
}
elseif ($_REQUEST['act'] == 'main_api')
{
    require_once(ROOT_PATH . '/includes/lib_base.php');
    $data = read_static_cache('api_str');

    if($data === false || API_TIME < date('Y-m-d H:i:s',time()-43200))
    {
        include_once(ROOT_PATH . 'includes/cls_transport.php');
        $hhs_version = VERSION;
        $hhs_lang = $_CFG['lang'];
        $hhs_release = RELEASE;
        $php_ver = PHP_VERSION;
        $mysql_ver = $db->version();
        $order['stats'] = $db->getRow('SELECT COUNT(*) AS oCount, IFNULL(SUM(order_amount), 0) AS oAmount' .
    ' FROM ' .$hhs->table('order_info'));
        $ocount = $order['stats']['oCount'];
        $oamount = $order['stats']['oAmount'];
        $goods['total']   = $db->GetOne('SELECT COUNT(*) FROM ' .$hhs->table('goods').
    ' WHERE is_delete = 0 AND is_alone_sale = 1 AND is_real = 1');
        $gcount = $goods['total'];
        $hhs_charset = strtoupper(EC_CHARSET);
        $hhs_user = $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('users'));
        $hhs_template = $db->getOne('SELECT value FROM ' . $hhs->table('shop_config') . ' WHERE code = \'template\'');
        $style = $db->getOne('SELECT value FROM ' . $hhs->table('shop_config') . ' WHERE code = \'stylename\'');
        if($style == '')
        {
            $style = '0';
        }
        $hhs_style = $style;
        $shop_url = urlencode($hhs->url());

        $patch_file = file_get_contents(ROOT_PATH.ADMIN_PATH."/patch_num");

        $apiget = "ver= $hhs_version &lang= $hhs_lang &release= $hhs_release &php_ver= $php_ver &mysql_ver= $mysql_ver &ocount= $ocount &oamount= $oamount &gcount= $gcount &charset= $hhs_charset &usecount= $hhs_user &template= $hhs_template &style= $hhs_style &url= $shop_url &patch= $patch_file ";
        
        $f=ROOT_PATH . 'data/config.php'; 
        file_put_contents($f,str_replace("'API_TIME', '".API_TIME."'","'API_TIME', '".date('Y-m-d H:i:s',time())."'",file_get_contents($f)));
        
        write_static_cache('api_str', $api_str);
    }
    else 
    {
        echo $data;
    }

}

/*------------------------------------------------------ */
//-- 关于 小舍电商
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'about_us')
{
    assign_query_info();
    $smarty->display('about_us.htm');
}

/*------------------------------------------------------ */
//-- 拖动的帧
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'drag')
{
    $smarty->display('drag.htm');;
}

/*------------------------------------------------------ */
//-- 检查订单
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'check_order')
{
    if (empty($_SESSION['last_check']))
    {
        $_SESSION['last_check'] = gmtime();

        make_json_result('', '', array('new_orders' => 0, 'new_paid' => 0));
    }

    /* 新订单 */
    $sql = 'SELECT COUNT(*) FROM ' . $hhs->table('order_info').
    " WHERE add_time >= '$_SESSION[last_check]'";
    $arr['new_orders'] = $db->getOne($sql);

    /* 新付款的订单 */
    $sql = 'SELECT COUNT(*) FROM '.$hhs->table('order_info').
    ' WHERE pay_time >= ' . $_SESSION['last_check'];
    $arr['new_paid'] = $db->getOne($sql);

    $_SESSION['last_check'] = gmtime();

    if (!(is_numeric($arr['new_orders']) && is_numeric($arr['new_paid'])))
    {
        make_json_error($db->error());
    }
    else
    {
        make_json_result('', '', $arr);
    }
}

/*------------------------------------------------------ */
//-- Totolist操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'save_todolist')
{
    $content = json_str_iconv($_POST["content"]);
    $sql = "UPDATE" .$GLOBALS['hhs']->table('admin_user'). " SET todolist='" . $content . "' WHERE user_id = " . $_SESSION['admin_id'];
    $GLOBALS['db']->query($sql);
}

elseif ($_REQUEST['act'] == 'get_todolist')
{
    $sql     = "SELECT todolist FROM " .$GLOBALS['hhs']->table('admin_user'). " WHERE user_id = " . $_SESSION['admin_id'];
    $content = $GLOBALS['db']->getOne($sql);
    echo $content;
}
// 邮件群发处理
elseif ($_REQUEST['act'] == 'send_mail')
{
    if ($_CFG['send_mail_on'] == 'off')
    {
        make_json_result('', $_LANG['send_mail_off'], 0);
        exit();
    }
    $sql = "SELECT * FROM " . $hhs->table('email_sendlist') . " ORDER BY pri DESC, last_send ASC LIMIT 1";
    $row = $db->getRow($sql);

    //发送列表为空
    if (empty($row['id']))
    {
        make_json_result('', $_LANG['mailsend_null'], 0);
    }

    //发送列表不为空，邮件地址为空
    if (!empty($row['id']) && empty($row['email']))
    {
        $sql = "DELETE FROM " . $hhs->table('email_sendlist') . " WHERE id = '$row[id]'";
        $db->query($sql);
        $count = $db->getOne("SELECT COUNT(*) FROM " . $hhs->table('email_sendlist'));
        make_json_result('', $_LANG['mailsend_skip'], array('count' => $count, 'goon' => 1));
    }

    //查询相关模板
    $sql = "SELECT * FROM " . $hhs->table('mail_templates') . " WHERE template_id = '$row[template_id]'";
    $rt = $db->getRow($sql);

    //如果是模板，则将已存入email_sendlist的内容作为邮件内容
    //否则即是杂质，将mail_templates调出的内容作为邮件内容
    if ($rt['type'] == 'template')
    {
        $rt['template_content'] = $row['email_content'];
    }

    if ($rt['template_id'] && $rt['template_content'])
    {
        if (send_mail('', $row['email'], $rt['template_subject'], $rt['template_content'], $rt['is_html']))
        {
            //发送成功

            //从列表中删除
            $sql = "DELETE FROM " . $hhs->table('email_sendlist') . " WHERE id = '$row[id]'";
            $db->query($sql);

            //剩余列表数
            $count = $db->getOne("SELECT COUNT(*) FROM " . $hhs->table('email_sendlist'));

            if($count > 0)
            {
                $msg = sprintf($_LANG['mailsend_ok'],$row['email'],$count);
            }
            else
            {
                $msg = sprintf($_LANG['mailsend_finished'],$row['email']);
            }
            make_json_result('', $msg, array('count' => $count));
        }
        else
        {
            //发送出错

            if ($row['error'] < 3)
            {
                $time = time();
                $sql = "UPDATE " . $hhs->table('email_sendlist') . " SET error = error + 1, pri = 0, last_send = '$time' WHERE id = '$row[id]'";
            }
            else
            {
                //将出错超次的纪录删除
                $sql = "DELETE FROM " . $hhs->table('email_sendlist') . " WHERE id = '$row[id]'";
            }
            $db->query($sql);

            $count = $db->getOne("SELECT COUNT(*) FROM " . $hhs->table('email_sendlist'));
            make_json_result('', sprintf($_LANG['mailsend_fail'],$row['email']), array('count' => $count));
        }
    }
    else
    {
        //无效的邮件队列
        $sql = "DELETE FROM " . $hhs->table('email_sendlist') . " WHERE id = '$row[id]'";
        $db->query($sql);
        $count = $db->getOne("SELECT COUNT(*) FROM " . $hhs->table('email_sendlist'));
        make_json_result('', sprintf($_LANG['mailsend_fail'],$row['email']), array('count' => $count));
    }
}
/**
*
*获取订单数量
*
*@param $point_id 自提店id
*
*@param $type 订单状态
*
 ━━━━━━神兽出没━━━━━━
 * 　　　┏┓　　　┏┓
 * 　　┏┛┻━━━┛┻┓
 * 　　┃　　　　　　　┃
 * 　　┃　　　━　　　┃
 * 　　┃　┳┛　┗┳　┃
 * 　　┃　　　　　　　┃
 * 　　┃　　　┻　　　┃
 * 　　┃　　　　　　　┃
 * 　　┗━┓　　　┏━┛Code is far away from bug with the animal protecting
 * 　　　　┃　　　┃    神兽保佑,代码无bug
 * 　　　　┃　　　┃
 * 　　　　┃　　　┗━━━┓
 * 　　　　┃　　　　　　　┣┓
 * 　　　　┃　　　　　　　┏┛
 * 　　　　┗┓┓┏━┳┓┏┛
 * 　　　　　┃┫┫　┃┫┫
 * 　　　　　┗┻┛　┗┻┛
 *
 * ━━━━━━感觉萌萌哒━━━━━━
**/
function GetOrderNum($point_id=0,$type='finished')
{
    /*初始化数据*/
    $where = " WHERE 1 ";
    if($type == 'refund')
    {
        $where .= " AND order_status = 4 AND shipping_status = 0 AND pay_status = 3 ";
    }else
    {
        $where .= order_query_sql($type);
    }
    if($type == 'await_ship')
    {
        $where .= " AND ((extension_code='team_goods' AND team_status=2 ) or (extension_code!='team_goods')) ";
    }
    $point_id = isset($point_id) ? intval($point_id) : 0;
    if($point_id > 0)
    {
        $where .= " AND point_id > 0 ";
    }else
    {
        $where .= " AND point_id = 0 ";
    }
    /*查询语句*/
    $sql = "SELECT COUNT(*) FROM  ". $GLOBALS['hhs']->table('order_info') .$where;
    return $GLOBALS['db']->getOne($sql);
}
/**
*
*获取当前日期的微支付订单信息
*
*@param $is_remind 是否提醒过
*
*@return array
**/
function getOrderUnpaid($is_remind=false)
{
    /*初始化条件*/
    $where = " WHERE o.order_status < 2 AND o.shipping_status = 0 AND o.pay_status < 2 ";
    if($is_remind)
    {
        $where .= " AND o.cancel_remind = 1 ";
    }else
    {
        $where .= " AND o.cancel_remind = 0 ";
    }
    $unpaidOrderSql = "SELECT o.add_time,o.order_sn,o.cancel_remind,o.order_id,o.user_id,u.openid,o.order_status,o.shipping_status,o.pay_status FROM  ". $GLOBALS['hhs']->table('order_info') 
    ." AS o LEFT JOIN ".$GLOBALS['hhs']->table('users')." AS u ON o.user_id = u.user_id "
    .$where;
    $unpaidOrderList = $GLOBALS['db']->getAll($unpaidOrderSql);
    /*格式化数据*/
    if($unpaidOrderList)
    {
        foreach ($unpaidOrderList as $key => $value) 
        {
            $unpaidOrderList[$key]['order_goods'] = getOrderGoodsList($value['order_id']);
        }
    }
    return $unpaidOrderList;
}
/*获取只定订单下的商品信息*/
function getOrderGoodsList($order_id = 0)
{
    $order_id = intval($order_id);
    return $GLOBALS['db']->getAll("SELECT goods_id,goods_name,goods_attr,goods_number FROM " . $GLOBALS['hhs']->table('order_goods') . "  WHERE `order_id` = '$order_id'");
}
?>
