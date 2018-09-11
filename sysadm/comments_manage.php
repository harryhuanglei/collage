<?php
/**
 * 小舍电商 用户评论管理程序
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: comment_manage.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
/*------------------------------------------------------ */
//-- 获取没有回复的评论列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    admin_priv('false_comm');
    $smarty->assign('ur_here',      '虚拟评论列表');
	$smarty->assign('action_link',  array('text' => '添加虚拟评论', 'href'=>'comments_manage.php?act=add'));
    $smarty->assign('full_page',    1);
    $list = get_comment_list();
    $smarty->assign('comment_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    assign_query_info();
    $smarty->display('comments_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页、搜索、排序
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'query')
{
    $list = get_comment_list();
    $smarty->assign('comment_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('comments_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 回复用户评论(同时查看评论详情)
/*------------------------------------------------------ */
if ($_REQUEST['act']=='reply')
{
    $comment_info = array();
    $reply_info   = array();
    $id_value     = array();
    /* 获取评论详细信息并进行字符处理 */
    $sql = "SELECT * FROM " .$hhs->table('comment'). " WHERE comment_id = '$_REQUEST[id]'";
    $comment_info = $db->getRow($sql);
    $comment_info['content']  = str_replace('\r\n', '<br />', htmlspecialchars($comment_info['content']));
    $comment_info['content']  = nl2br(str_replace('\n', '<br />', $comment_info['content']));
    $comment_info['add_time'] = local_date($_CFG['time_format'], $comment_info['add_time']);
    /* 获得评论回复内容 */
    $sql = "SELECT * FROM ".$hhs->table('comment'). " WHERE parent_id = '$_REQUEST[id]'";
    $reply_info = $db->getRow($sql);
    if (empty($reply_info))
    {
        $reply_info['content']  = '';
        $reply_info['add_time'] = '';
    }
    else
    {
        $reply_info['content']  = nl2br(htmlspecialchars($reply_info['content']));
        $reply_info['add_time'] = local_date($_CFG['time_format'], $reply_info['add_time']);
    }
    /* 获取管理员的用户名和Email地址 */
    $sql = "SELECT user_name, email FROM ". $hhs->table('admin_user').
           " WHERE user_id = '$_SESSION[admin_id]'";
    $admin_info = $db->getRow($sql);
    /* 取得评论的对象(文章或者商品) */
    if ($comment_info['comment_type'] == 0)
    {
        $sql = "SELECT goods_name FROM ".$hhs->table('goods').
               " WHERE goods_id = '$comment_info[id_value]'";
        $id_value = $db->getOne($sql);
    }
    else
    {
        $sql = "SELECT title FROM ".$hhs->table('article').
               " WHERE article_id='$comment_info[id_value]'";
        $id_value = $db->getOne($sql);
    }

    /* 模板赋值 */
    $smarty->assign('msg',          $comment_info); //评论信息
    $smarty->assign('admin_info',   $admin_info);   //管理员信息
    $smarty->assign('reply_info',   $reply_info);   //回复的内容
    $smarty->assign('id_value',     $id_value);  //评论的对象
    $smarty->assign('send_fail',   !empty($_REQUEST['send_ok']));
    $smarty->assign('ur_here',      $_LANG['comment_info']);
    $smarty->assign('action_link',  array('text' => $_LANG['05_comment_manage'],
    'href' => 'comments_manage.php?act=list'));
    /* 页面显示 */
    assign_query_info();
    $smarty->display('comments_info.htm');
}
/*------------------------------------------------------ */
//-- 回复用户评论(同时查看评论详情)
/*------------------------------------------------------ */
if ($_REQUEST['act']=='add')
{
	//会员信息
	$user_list = get_user_list();
	foreach($user_list as $k =>$v)
	{
		$user_list[$k]['province'] = get_regions_name($v['province']);
		$user_list[$k]['city'] = get_regions_name($v['city']);
	}
	$smarty->assign('user_list',     $user_list);
    $smarty->assign('form_action',     'insert');
    $goods['option']      = '<option value="0">请先搜索商品生成选项列表</option>';
    $smarty->assign('goods',       $goods);
    $smarty->assign('ur_here',      '添加虚拟评论');
    $smarty->assign('action_link',  array('text' => '虚拟评论列表',
    'href' => 'comments_manage.php?act=list'));
    /* 页面显示 */
    assign_query_info();
   $smarty->display('false_comment_info.htm');
}
/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'search_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $filters = $json->decode($_GET['JSON']);
    $arr = get_goods_list($filters);
    make_json_result($arr);
}
if ($_REQUEST['act'] == 'insert')
{
	/*初始化数据*/
	$user_id         = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;
	$id_value         = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
	$content        = !empty($_POST['content']) ? trim($_POST['content']) : '';
    $comment_rank = !empty($_POST['comment_rank']) ? intval($_POST['comment_rank']) : 0;
	$is_false = 1;
	$status = 1;
	$add_time = gmtime();
	$ip_address = real_ip();
	$user_name = $GLOBALS['db']->getOne("SELECT uname ".
                " FROM " . $GLOBALS['hhs']->table('users')." WHERE user_id = ".$user_id );
    /* 插入数据库。 */
    $sql = "INSERT INTO ".$hhs->table('comment')." (user_id,content,id_value,user_name, add_time,status,is_false,comment_rank,ip_address)
    VALUES ('$user_id','$content','$id_value','$user_name','$add_time','$status','$is_false','$comment_rank','$ip_address')";
    $db->query($sql);
    $com_id = $db->insert_id();
	//更新用户评论次数
	if($com_id > 0)
	{
		$sql = "UPDATE " .$hhs->table('users'). " SET comment_num = comment_num+1 WHERE user_id = '$user_id'";
		$db->query($sql);	
	}
    /* 记录管理员操作 */
    admin_log($user_name, 'add', 'comment');
    /* 清除缓存 */
    clear_cache_files();
    /* 提示信息 */
    $link[0]['text'] = '返回列表';
    $link[0]['href'] = 'comments_manage.php?act=list';
    sys_msg('添加' . "&nbsp;" .$user_name . "&nbsp;" .'虚拟评论成功',0, $link);
}


/*------------------------------------------------------ */
//-- 处理 回复用户评论
/*------------------------------------------------------ */
if ($_REQUEST['act']=='action')
{
    /* 获取IP地址 */
    $ip     = real_ip();
    /* 获得评论是否有回复 */
    $sql = "SELECT comment_id, content, parent_id FROM ".$hhs->table('comment').
           " WHERE parent_id = '$_REQUEST[comment_id]'";
    $reply_info = $db->getRow($sql);
    if (!empty($reply_info['content']))
    {
        /* 更新回复的内容 */
        $sql = "UPDATE ".$hhs->table('comment')." SET ".
               "email     = '$_POST[email]', ".
               "user_name = '$_POST[user_name]', ".
               "content   = '$_POST[content]', ".
               "add_time  =  '" . gmtime() . "', ".
               "ip_address= '$ip', ".
               "status    = 0".
               " WHERE comment_id = '".$reply_info['comment_id']."'";
    }
    else
    {
        /* 插入回复的评论内容 */
        $sql = "INSERT INTO ".$hhs->table('comment')." (comment_type, id_value, email, user_name , ".
                    "content, add_time, ip_address, status, parent_id) ".
               "VALUES('$_POST[comment_type]', '$_POST[id_value]','$_POST[email]', " .
                    "'$_SESSION[admin_name]','$_POST[content]','" . gmtime() . "', '$ip', '0', '$_POST[comment_id]')";
    }
    $db->query($sql);

    /* 更新当前的评论状态为已回复并且可以显示此条评论 */
    $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_POST[comment_id]'";
    $db->query($sql);

    /* 邮件通知处理流程 */
    if (!empty($_POST['send_email_notice']) or isset($_POST['remail']))
    {
        //获取邮件中的必要内容
        $sql = 'SELECT user_name, email, content ' .
               'FROM ' .$hhs->table('comment') .
               " WHERE comment_id ='$_REQUEST[comment_id]'";
        $comment_info = $db->getRow($sql);

        /* 设置留言回复模板所需要的内容信息 */
        $template    = get_mail_template('recomment');

        $smarty->assign('user_name',   $comment_info['user_name']);
        $smarty->assign('recomment', $_POST['content']);
        $smarty->assign('comment', $comment_info['content']);
        $smarty->assign('shop_name',   "<a href='".$hhs->url()."'>" . $_CFG['shop_name'] . '</a>');
        $smarty->assign('send_date',   date('Y-m-d'));

        $content = $smarty->fetch('str:' . $template['template_content']);

        /* 发送邮件 */
        if (send_mail($comment_info['user_name'], $comment_info['email'], $template['template_subject'], $content, $template['is_html']))
        {
            $send_ok = 0;
        }
        else
        {
            $send_ok = 1;
        }
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 记录管理员操作 */
    admin_log(addslashes($_LANG['reply']), 'edit', 'users_comment');

    hhs_header("Location: comments_manage.php?act=reply&id=$_REQUEST[comment_id]&send_ok=$send_ok\n");
    exit;
}
/*------------------------------------------------------ */
//-- 更新评论的状态为显示或者禁止
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'check')
{
    if ($_REQUEST['check'] == 'allow')
    {
        /* 允许评论显示 */
        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_REQUEST[id]'";
        $db->query($sql);

        //add_feed($_REQUEST['id'], COMMENT_GOODS);

        /* 清除缓存 */
        clear_cache_files();

        hhs_header("Location: comments_manage.php?act=reply&id=$_REQUEST[id]\n");
        exit;
    }
    else
    {
        /* 禁止评论显示 */
        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 0 WHERE comment_id = '$_REQUEST[id]'";
        $db->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        hhs_header("Location: comments_manage.php?act=reply&id=$_REQUEST[id]\n");
        exit;
    }
}

/*------------------------------------------------------ */
//-- 删除某一条评论
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    $id = intval($_GET['id']);
    $sql = "DELETE FROM " .$hhs->table('comment'). " WHERE comment_id = '$id'";
    $res = $db->query($sql);
    if ($res)
    {
        $db->query("DELETE FROM " .$hhs->table('comment'). " WHERE parent_id = '$id'");
    }

    admin_log('', 'remove', 'ads');

    $url = 'comments_manage.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    hhs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 批量删除用户评论
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'batch')
{
    $action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'deny';
    if (isset($_POST['checkboxes']))
    {
        switch ($action)
        {
            case 'remove':
                $db->query("DELETE FROM " . $hhs->table('comment') . " WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
                $db->query("DELETE FROM " . $hhs->table('comment') . " WHERE " . db_create_in($_POST['checkboxes'], 'parent_id'));
                break;

           case 'allow' :
               $db->query("UPDATE " . $hhs->table('comment') . " SET status = 1  WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
               break;

           case 'deny' :
               $db->query("UPDATE " . $hhs->table('comment') . " SET status = 0  WHERE " . db_create_in($_POST['checkboxes'], 'comment_id'));
               break;

           default :
               break;
        }

        clear_cache_files();
        $action = ($action == 'remove') ? 'remove' : 'edit';
        admin_log('', $action, 'adminlog');

        $link[] = array('text' => $_LANG['back_list'], 'href' => 'comments_manage.php?act=list');
        sys_msg(sprintf($_LANG['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
    }
    else
    {
        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'comments_manage.php?act=list');
        sys_msg($_LANG['no_select_comment'], 0, $link);
    }
}
//获取商品属性
if($_REQUEST['act'] == 'goodsAttr')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
	$json = new JSON;
	$id = intval($_REQUEST['id']);
	$attribute = get_goods_specifications_list($id);
	foreach ($attribute as $k => $attribute_value)
    {
        //转换成数组
        $result[$k]['attr_values'][] = $attribute_value['attr_value'];
        $result[$k]['attr_id'] = $attribute_value['attr_id'];
        $result[$k]['attr_name'] = $attribute_value['attr_name'];
    }
	print_r($result);
	die($json->encode($result));
}
/**
 * 获取评论列表
 * @access  public
 * @return  array
 */
function get_comment_list()
{
    /* 查询条件 */
    $filter['keywords']     = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = (!empty($filter['keywords'])) ? " AND content LIKE '%" . mysql_like_quote($filter['keywords']) . "%' " : '';
	
	$where .= " and is_false = 1 ";

    $sql = "SELECT count(*) FROM " .$GLOBALS['hhs']->table('comment'). " WHERE parent_id = 0 $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 获取评论数据 */
    $arr = array();
    $sql  = "SELECT * FROM " .$GLOBALS['hhs']->table('comment'). " WHERE parent_id = 0 $where " .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT ". $filter['start'] .", $filter[page_size]";
    $res  = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $sql = ($row['comment_type'] == 0) ?
            "SELECT goods_name FROM " .$GLOBALS['hhs']->table('goods'). " WHERE goods_id='$row[id_value]'" :
            "SELECT title FROM ".$GLOBALS['hhs']->table('article'). " WHERE article_id='$row[id_value]'";
        $row['title'] = $GLOBALS['db']->getOne($sql);

        /* 标记是否回复过 */
//        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('comment'). " WHERE parent_id = '$row[comment_id]'";
//        $row['is_reply'] =  ($GLOBALS['db']->getOne($sql) > 0) ?
//            $GLOBALS['_LANG']['yes_reply'] : $GLOBALS['_LANG']['no_reply'];

        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

        $arr[] = $row;
    }
    $filter['keywords'] = stripslashes($filter['keywords']);
    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
//获得会员列表

function get_user_list()
{

	return $GLOBALS['db']->getAll("SELECT u.sex,u.headimgurl,u.user_id, u.user_name,u.uname,ua.province,ua.city,ua.district,ua.address ".
                " FROM " . $GLOBALS['hhs']->table('users')." AS u LEFT JOIN ".$GLOBALS['hhs']->table('user_address')." AS ua ON u.address_id = ua.address_id WHERE u.is_false = 1 ");
		

}
function get_regions_name($region_id)
{



    return $GLOBALS['db']->getOne("select region_name from ".$GLOBALS['hhs']->table('region')." where region_id='$region_id'");



}
function get_goods_properties($goods_id)
{
    /* 对属性进行重新排序和分组 */
    $sql = "SELECT attr_group ".
            "FROM " . $GLOBALS['hhs']->table('goods_type') . " AS gt, " . $GLOBALS['hhs']->table('goods') . " AS g ".
            "WHERE g.goods_id='$goods_id' AND gt.cat_id=g.goods_type";
    $grp = $GLOBALS['db']->getOne($sql);

    if (!empty($grp))
    {
        $groups = explode("\n", strtr($grp, "\r", ''));
    }

    /* 获得商品的规格 */
    $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ".
                "g.goods_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . $GLOBALS['hhs']->table('goods_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['hhs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            "WHERE g.goods_id = '$goods_id' " .
            'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';
    $res = $GLOBALS['db']->getAll($sql);

    $arr['pro'] = array();     // 属性
    $arr['spe'] = array();     // 规格
    $arr['lnk'] = array();     // 关联的属性

    foreach ($res AS $row)
    {
        $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

        if ($row['attr_type'] == 0)
        {
            $group = (isset($groups[$row['attr_group']])) ? $groups[$row['attr_group']] : $GLOBALS['_LANG']['goods_attr'];

            $arr['pro'][$group][$row['attr_id']]['name']  = $row['attr_name'];
            $arr['pro'][$group][$row['attr_id']]['value'] = $row['attr_value'];
        }
        else
        {
            $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
            $arr['spe'][$row['attr_id']]['name']     = $row['attr_name'];
            $arr['spe'][$row['attr_id']]['values'][] = array(
                                                        'label'        => $row['attr_value'],
                                                        'price'        => $row['attr_price'],
                                                        'format_price' => price_format(abs($row['attr_price']), false),
                                                        'id'           => $row['goods_attr_id']);
        }

        if ($row['is_linked'] == 1)
        {
            /* 如果该属性需要关联，先保存下来 */
            $arr['lnk'][$row['attr_id']]['name']  = $row['attr_name'];
            $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
        }
    }

    return $arr;
}

?>
