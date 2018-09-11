<?php 
define('IN_HHS', true);
require('../includes/init2.php');
require('../includes/lib_order.php');
require('../includes/lib_code.php');
//define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

$smarty->assign('temp_root_path',ROOT_PATH);
$smarty->assign('admin_path',ADMIN_PATH);
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/bonus.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');

$suppliers_id = $_SESSION['suppliers_id'];
if(empty($suppliers_id))
{
    header("Location:index.php");
    die();
}
/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    die();
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 优惠劵发送页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'send')
{
    admin_priv('bonus_manage');

    /* 取得参数 */
    $id = !empty($_REQUEST['id'])  ? intval($_REQUEST['id'])  : '';

    assign_query_info();

    $smarty->assign('ur_here',      $_LANG['send_bonus']);
    $smarty->assign('action_link',  array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list']));

    if ($_REQUEST['send_by'] == SEND_BY_USER)
    {
        $smarty->assign('id',           $id);
        $smarty->assign('ranklist',     get_rank_list());

        $smarty->display('bonus_by_user.htm');
    }
    elseif ($_REQUEST['send_by'] == SEND_BY_GOODS)
    {
        /* 查询此优惠劵类型信息 */
        $bonus_type = $db->GetRow("SELECT type_id, type_name,is_share FROM ".$hhs->table('bonus_type').
            " WHERE type_id='$_REQUEST[id]'");

        /* 查询优惠劵类型的商品列表 */
        if($bonus_type['is_share']==1){
            $sql = "SELECT goods_id, goods_name FROM " .$GLOBALS['hhs']->table('goods').
            " WHERE share_bonus_type = ".$_REQUEST['id'];
            $goods_list = $GLOBALS['db']->getAll($sql);
        }else{
            $goods_list = get_bonus_goods($_REQUEST['id']);
        }
        

        /* 查询其他优惠劵类型的商品 */
        $sql = "SELECT goods_id FROM " .$hhs->table('goods').
               " WHERE bonus_type_id > 0 AND bonus_type_id <> '$_REQUEST[id]'";
        $other_goods_list = $db->getCol($sql);
        $smarty->assign('other_goods', join(',', $other_goods_list));

        /* 模板赋值 */
        $smarty->assign('cat_list',    cat_list());
        $smarty->assign('brand_list',  get_brand_list());

        $smarty->assign('bonus_type',  $bonus_type);
        $smarty->assign('goods_list',  $goods_list);

        $smarty->display('bonus_by_goods.htm');
    }
    elseif ($_REQUEST['send_by'] == SEND_BY_PRINT)
    {
        $smarty->assign('type_list',    get_bonus_type());

        $smarty->display('bonus_by_print.htm');
    }
}

/*------------------------------------------------------ */
//-- 处理优惠劵的发送页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'send_by_user')
{
	 
    $sql = 'SELECT COUNT(user_id) FROM ' . $hhs->table('users'). "";
    $usernum = $db->getOne($sql);
    $user_list  = array();
    $start      = empty($_REQUEST['start']) ? 0 : intval($_REQUEST['start']);
    $limit      = empty($_REQUEST['limit']) ? $usernum : intval($_REQUEST['limit']);
    $validated_email = empty($_REQUEST['validated_email']) ? 0 : intval($_REQUEST['validated_email']);
    $send_count = 0;

    if (isset($_REQUEST['send_rank']))
    {
        /* 按会员等级来发放优惠劵 */
        $rank_id = intval($_REQUEST['rank_id']);

        if ($rank_id > 0)
        {
            $sql = "SELECT min_points, max_points, special_rank FROM " . $hhs->table('user_rank') . " WHERE rank_id = '$rank_id'";
            $row = $db->getRow($sql);
            if ($row['special_rank'])
            {
                /* 特殊会员组处理 */
                $sql = 'SELECT COUNT(*) FROM ' . $hhs->table('users'). " WHERE user_rank = '$rank_id'";
                $send_count = $db->getOne($sql);
                if($validated_email)
                {
                    $sql = 'SELECT user_id, email, user_name FROM ' . $hhs->table('users').
                            " WHERE user_rank = '$rank_id' AND is_validated = 1".
                            " LIMIT $start, $limit";
                }
                else
                {
                     $sql = 'SELECT user_id, email, user_name FROM ' . $hhs->table('users').
                                " WHERE user_rank = '$rank_id'".
                                " LIMIT $start, $limit";
                }
            }
            else
            {
                $sql = 'SELECT COUNT(*) FROM ' . $hhs->table('users').
                       " WHERE rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']);
                $send_count = $db->getOne($sql);

                if($validated_email)
                {
                    $sql = 'SELECT user_id, email, user_name FROM ' . $hhs->table('users').
                        " WHERE rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']) .
                        " AND is_validated = 1 LIMIT $start, $limit";
                }
                else
                {
                     $sql = 'SELECT user_id, email, user_name FROM ' . $hhs->table('users').
                        " WHERE rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']) .
                        " LIMIT $start, $limit";
                }

            }

            $user_list = $db->getAll($sql);
            $count = count($user_list);
        }
    }
    elseif (isset($_REQUEST['send_user']))
    {
        /* 按会员列表发放优惠劵 */
        /* 如果是空数组，直接返回 */
        if (empty($_REQUEST['user']))
        {
            echo '<script>alert("请选择用户");history.go(-1);</script>';
            exit();
        }

        $user_array = (is_array($_REQUEST['user'])) ? $_REQUEST['user'] : explode(',', $_REQUEST['user']);
        $send_count = count($user_array);

        $id_array   = array_slice($user_array, $start, $limit);

        /* 根据会员ID取得用户名和邮件地址 */
        $sql = "SELECT user_id, email, user_name FROM " .$hhs->table('users').
               " WHERE user_id " .db_create_in($id_array);
        $user_list  = $db->getAll($sql);
        $count = count($user_list);
    }

    /* 发送优惠劵 */
    $loop       = 0;
    $bonus_type = bonus_type_info($_REQUEST['id']);

    $tpl = get_mail_template('send_bonus');
    $today = local_date($_CFG['date_format']);

    foreach ($user_list AS $key => $val)
    {
        /* 发送邮件通知 */
        $smarty->assign('user_name',    $val['user_name']);
        $smarty->assign('shop_name',    $GLOBALS['_CFG']['shop_name']);
        $smarty->assign('send_date',    $today);
        $smarty->assign('sent_date',    $today);
        $smarty->assign('count',        1);
        $smarty->assign('money',        price_format($bonus_type['type_money']));

        $sql = "INSERT INTO " . $hhs->table('user_bonus') .
                "(suppliers_id,bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) " .
                "VALUES ('$suppliers_id','$_REQUEST[id]', 0, '$val[user_id]', 0, 0, " .BONUS_MAIL_FAIL. ")";
        $db->query($sql);
        $user_id=$val['user_id'];
        $user = $GLOBALS['db']->getRow("select openid,uname from ".$GLOBALS['hhs']->table('users')." where user_id=".$user_id);
        $money = $GLOBALS['db']->getOne("select type_money from ".$GLOBALS['hhs']->table('bonus_type')." where type_id=".$_REQUEST['id']);
        // $wxch_order_name='send_order_bonus';
        // include_once(ROOT_PATH . 'wxch_order.php');

        $w_title       = '发送优惠券提醒';
        $w_url         = 'user.php?act=bonus';
        $w_description = '亲爱的'.$user['uname'].'您好！恭喜您获得了1个红包，金额为'.$money;
        $picurl        = '';

        $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

        $weixin->send_wxmsg($user['openid'], $w_title , $w_url , $w_description ,$picurl);


        if ($loop >= $limit)
        {
            break;
        }
        else
        {
            $loop++;
        }
    }
    show_message('发送成功','优惠券类型列表', '/business/index.php?op=bonus&act=bonus', 'info');
}

/*------------------------------------------------------ */
//-- 搜索用户
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'search_users')
{
    $keywords = json_str_iconv(trim($_GET['keywords']));
    if($keywords){
        $sql = "SELECT user_id,uname, user_name FROM " . $hhs->table('users') .
        " WHERE user_name LIKE '%" . mysql_like_quote($keywords) . "%' or  uname LIKE '%" . mysql_like_quote($keywords) . "%' OR user_id LIKE '%" . mysql_like_quote($keywords) . "%'";
    }else{
        $sql = "SELECT user_id,uname, user_name FROM " . $hhs->table('users') ;
    }
    
    $row = $db->getAll($sql);
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $res = array('error' => 0, 'message' => '', 'content' => $row);

    echo $json->encode($res);
    exit();
}

/*------------------------------------------------------ */
//-- 优惠劵列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'bonus_list')
{
    $smarty->assign('full_page',    1);
    $smarty->assign('ur_here',      $_LANG['bonus_list']);
    $smarty->assign('action_link',   array('href' => 'bonus.php?act=list', 'text' => $_LANG['04_bonustype_list']));

    $list = get_bonus_list();

    /* 赋值是否显示优惠劵序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == SEND_BY_PRINT)
    {
        $smarty->assign('show_bonus_sn', 1);
    }

    /* 赋值是否显示发邮件操作和是否发过邮件 */
    elseif ($bonus_type['send_type'] == SEND_BY_USER)
    {
        $smarty->assign('show_mail', 1);
    }

    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('bonus_list.htm');
}

/*------------------------------------------------------ */
//-- 优惠劵列表翻页、排序
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'query_bonus')
{
    $list = get_bonus_list();

    /* 赋值是否显示优惠劵序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == SEND_BY_PRINT)
    {
        $smarty->assign('show_bonus_sn', 1);
    }

    /* 赋值是否显示发邮件操作和是否发过邮件 */
    elseif ($bonus_type['send_type'] == SEND_BY_USER)
    {
        $smarty->assign('show_mail', 1);
    }

    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('bonus_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 删除优惠劵
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove_bonus')
{
    check_authz_json('bonus_manage');

    $id = intval($_GET['id']);

    $db->query("DELETE FROM " .$hhs->table('user_bonus'). " WHERE bonus_id='$id'");

    $url = 'bonus.php?act=query_bonus&' . str_replace('act=remove_bonus', '', $_SERVER['QUERY_STRING']);

    hhs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'batch')
{
    /* 检查权限 */
    admin_priv('bonus_manage');

    /* 去掉参数：优惠劵类型 */
    $bonus_type_id = intval($_REQUEST['bonus_type']);

    /* 取得选中的优惠劵id */
    if (isset($_POST['checkboxes']))
    {
        $bonus_id_list = $_POST['checkboxes'];

        /* 删除优惠劵 */
        if (isset($_POST['drop']))
        {
            $sql = "DELETE FROM " . $hhs->table('user_bonus'). " WHERE bonus_id " . db_create_in($bonus_id_list);
            $db->query($sql);

            admin_log(count($bonus_id_list), 'remove', 'userbonus');

            clear_cache_files();

            $link[] = array('text' => $_LANG['back_bonus_list'],
                'href' => 'bonus.php?act=bonus_list&bonus_type='. $bonus_type_id);
            sys_msg(sprintf($_LANG['batch_drop_success'], count($bonus_id_list)), 0, $link);
        }

        /* 发邮件 */
        elseif (isset($_POST['mail']))
        {
            $count = send_bonus_mail($bonus_type_id, $bonus_id_list);
            $link[] = array('text' => $_LANG['back_bonus_list'],
                'href' => 'bonus.php?act=bonus_list&bonus_type='. $bonus_type_id);
            sys_msg(sprintf($_LANG['success_send_mail'], $count), 0, $link);
        }
    }
    else
    {
        sys_msg($_LANG['no_select_bonus'], 1);
    }
}

/**
 * 获取优惠劵类型列表
 * @access  public
 * @return void
 */
function get_type_list()
{
    /* 获得所有优惠劵类型的发放数量 */
    $sql = "SELECT bonus_type_id, COUNT(*) AS sent_count".
            " FROM " .$GLOBALS['hhs']->table('user_bonus') .
            " GROUP BY bonus_type_id";
    $res = $GLOBALS['db']->query($sql);

    $sent_arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $sent_arr[$row['bonus_type_id']] = $row['sent_count'];
    }

    /* 获得所有优惠劵类型的发放数量 */
    $sql = "SELECT bonus_type_id, COUNT(*) AS used_count".
            " FROM " .$GLOBALS['hhs']->table('user_bonus') .
            " WHERE used_time > 0".
            " GROUP BY bonus_type_id";
    $res = $GLOBALS['db']->query($sql);

    $used_arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $used_arr[$row['bonus_type_id']] = $row['used_count'];
    }

    $result = get_filter();
    if ($result === false)
    {
        /* 查询条件 */
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'type_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

		$filter['suppliers_id'] = empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id']);
		if($filter['suppliers_id']!='')
		{
			$where = " where suppliers_id='$filter[suppliers_id]'";
		}


        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('bonus_type')." $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

		

        $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('bonus_type'). " $where ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['send_by'] = $GLOBALS['_LANG']['send_by'][$row['send_type']];
        $row['send_count'] = isset($sent_arr[$row['type_id']]) ? $sent_arr[$row['type_id']] : 0;
        $row['use_count'] = isset($used_arr[$row['type_id']]) ? $used_arr[$row['type_id']] : 0;
		$row['suppliers_name'] = get_suppliers_name($row['suppliers_id']);

        $arr[] = $row;
    }

    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 查询优惠劵类型的商品列表
 *
 * @access  public
 * @param   integer $type_id
 * @return  array
 */
function get_bonus_goods($type_id)
{
    $sql = "SELECT goods_id, goods_name FROM " .$GLOBALS['hhs']->table('goods').
            " WHERE bonus_type_id = '$type_id'";
    $row = $GLOBALS['db']->getAll($sql);

    return $row;
}

/**
 * 获取用户优惠劵列表
 * @access  public
 * @param   $page_param
 * @return void
 */
function get_bonus_list()
{
    /* 查询条件 */
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'bonus_type_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['bonus_type'] = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);

    $where = empty($filter['bonus_type']) ? '' : " WHERE bonus_type_id='$filter[bonus_type]'";

    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('user_bonus'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT ub.*, u.user_name, u.email, o.order_sn, bt.type_name ".
          " FROM ".$GLOBALS['hhs']->table('user_bonus'). " AS ub ".
          " LEFT JOIN " .$GLOBALS['hhs']->table('bonus_type'). " AS bt ON bt.type_id=ub.bonus_type_id ".
          " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=ub.user_id ".
          " LEFT JOIN " .$GLOBALS['hhs']->table('order_info'). " AS o ON o.order_id=ub.order_id $where ".
          " ORDER BY ".$filter['sort_by']." ".$filter['sort_order'].
          " LIMIT ". $filter['start'] .", $filter[page_size]";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['used_time'] = $val['used_time'] == 0 ?
            $GLOBALS['_LANG']['no_use'] : local_date($GLOBALS['_CFG']['date_format'], $val['used_time']);
        $row[$key]['emailed'] = $GLOBALS['_LANG']['mail_status'][$row[$key]['emailed']];
    }

    $arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 取得优惠劵类型信息
 * @param   int     $bonus_type_id  优惠劵类型id
 * @return  array
 */
function bonus_type_info($bonus_type_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('bonus_type') .
            " WHERE type_id = '$bonus_type_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 发送优惠劵邮件
 * @param   int     $bonus_type_id  优惠劵类型id
 * @param   array   $bonus_id_list  优惠劵id数组
 * @return  int     成功发送数量
 */
function send_bonus_mail($bonus_type_id, $bonus_id_list)
{
    /* 取得优惠劵类型信息 */
    $bonus_type = bonus_type_info($bonus_type_id);
    if ($bonus_type['send_type'] != SEND_BY_USER)
    {
        return 0;
    }

    /* 取得属于该类型的优惠劵信息 */
    $sql = "SELECT b.bonus_id, u.user_name, u.email " .
            "FROM " . $GLOBALS['hhs']->table('user_bonus') . " AS b, " .
                $GLOBALS['hhs']->table('users') . " AS u " .
            " WHERE b.user_id = u.user_id " .
            " AND b.bonus_id " . db_create_in($bonus_id_list) .
            " AND b.order_id = 0 " .
            " AND u.email <> ''";
    $bonus_list = $GLOBALS['db']->getAll($sql);
    if (empty($bonus_list))
    {
        return 0;
    }

    /* 初始化成功发送数量 */
    $send_count = 0;

    /* 发送邮件 */
    $tpl   = get_mail_template('send_bonus');
    $today = local_date($GLOBALS['_CFG']['date_format']);
    foreach ($bonus_list AS $bonus)
    {
        $GLOBALS['smarty']->assign('user_name',    $bonus['user_name']);
        $GLOBALS['smarty']->assign('shop_name',    $GLOBALS['_CFG']['shop_name']);
        $GLOBALS['smarty']->assign('send_date',    $today);
        $GLOBALS['smarty']->assign('sent_date',    $today);
        $GLOBALS['smarty']->assign('count',        1);
        $GLOBALS['smarty']->assign('money',        price_format($bonus_type['type_money']));

        $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
        if (add_to_maillist($bonus['user_name'], $bonus['email'], $tpl['template_subject'], $content, $tpl['is_html'], false))
        {
            $sql = "UPDATE " . $GLOBALS['hhs']->table('user_bonus') .
                    " SET emailed = '" . BONUS_MAIL_SUCCEED . "'" .
                    " WHERE bonus_id = '$bonus[bonus_id]'";
            $GLOBALS['db']->query($sql);
            $send_count++;
        }
        else
        {
            $sql = "UPDATE " . $GLOBALS['hhs']->table('user_bonus') .
                    " SET emailed = '" . BONUS_MAIL_FAIL . "'" .
                    " WHERE bonus_id = '$bonus[bonus_id]'";
            $GLOBALS['db']->query($sql);
        }
    }

    return $send_count;
}

function add_to_maillist($username, $email, $subject, $content, $is_html)
{
    $time = time();
    $content = addslashes($content);
    $template_id = $GLOBALS['db']->getOne("SELECT template_id FROM " . $GLOBALS['hhs']->table('mail_templates') . " WHERE template_code = 'send_bonus'");
    $sql = "INSERT INTO "  . $GLOBALS['hhs']->table('email_sendlist') . " ( email, template_id, email_content, pri, last_send) VALUES ('$email', $template_id, '$content', 1, '$time')";
    $GLOBALS['db']->query($sql);
    return true;
}
function get_suppliers_list()
{
    $sql = 'SELECT *
            FROM ' . $GLOBALS['hhs']->table('suppliers') . '
            WHERE is_check = 1
            ORDER BY suppliers_name ASC';
    $res = $GLOBALS['db']->getAll($sql);

    if (!is_array($res))
    {
        $res = array();
    }

    return $res;
}

?>
