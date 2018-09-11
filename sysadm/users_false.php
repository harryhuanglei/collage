<?php
/**
 * 小舍电商 会员管理程序
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: users.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    admin_priv('users_false');
    $sql = "SELECT rank_id, rank_name, min_points FROM ".$hhs->table('user_rank')." ORDER BY min_points ASC ";
    $rs = $db->query($sql);
    $ranks = array();
    while ($row = $db->FetchRow($rs))
    {
        $ranks[$row['rank_id']] = $row['rank_name'];
    }
    $smarty->assign('user_ranks',   $ranks);
    $smarty->assign('ur_here',      '用户列表');
    $smarty->assign('action_link',  array('text' => '添加用户
', 'href'=>'users_false.php?act=add_false'));
    $user_list = user_list();
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
    assign_query_info();
    $smarty->display('users_false_list.htm');
}
/*------------------------------------------------------ */
//-- ajax返回用户列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $user_list = user_list();
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $sort_flag  = sort_flag($user_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('users_false_list.htm'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}
/*------------------------------------------------------ */
//-- 添加虚拟会员帐号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_false')
{
    $smarty->assign('ur_here',          '添加用户');
	$smarty->assign('action_link',      array('text' => '用户列表', 'href'=>'users_false.php?act=list'));
	/* 载入国家 */
    $province_list = get_regions(1, 1);
    $smarty->assign('province_list',    $province_list);
	$smarty->assign('form_action',      'insert_false');
    assign_query_info();
	$smarty->display('users_info.htm');
}
/*------------------------------------------------------ */
//-- 添加会员帐号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert_false')
{
    /* 检查权限 */
    admin_priv('users_false');
    $username = empty($_POST['username']) ? '' : trim($_POST['username']);
	$sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
	/*处理图片*/
    $user_img_arr = getimagesize($_FILES['headimgurl']['tmp_name']);
    if($user_img_arr[0] > 64 || $goods_img_arr[1] > 64)
    {
        sys_msg('会员图片上传宽高64*64');   
    }
    $img_name = basename($image->upload_image($_FILES['headimgurl'],'headimgurl'));
	if(empty($img_name))
    {
        sys_msg('会员图片不能为空');   
    }
    $province = empty($_POST['province']) ? 0 : intval($_POST['province']);
	$city = empty($_POST['city']) ? 0 : intval($_POST['city']);
	$district = empty($_POST['district']) ? 0 : intval($_POST['district']);
	$addr = empty($_POST['address']) ? '' : trim($_POST['address']);
    /* 更新会员的其它信息 */
	$other =  array();
	$other['uname'] = $username;
	$other['user_name'] = 'wxf'.mt_rand(0,100);
	$other['is_false'] = 1;
	$other['headimgurl']  = $img_name;
	$other['reg_time']  = gmtime();
	$other['sex']  = $sex;
    $db->autoExecute($hhs->table('users'), $other, 'INSERT');
	$user_id = $db->insert_id();
	/*更新会员的收货地址*/
	$address = array();
	$address['consignee'] = $username;
	$address['user_id'] = $user_id;
	$address['province'] = $province;
	$address['city'] = $city;
	$address['district'] = $district;
	$address['address'] = $addr;
	$address['country'] = 1;
	$db->autoExecute($hhs->table('user_address'), $address, 'INSERT');
	$address_id = $db->insert_id();
	$db->autoExecute($hhs->table('users'), array('address_id' => $address_id), 'UPDATE',"user_id = '$user_id'");
    /* 记录管理员操作 */
    admin_log($_POST['username'], 'add', 'users');
    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users_false.php?act=list');
    sys_msg(sprintf($_LANG['add_success'], htmlspecialchars(stripslashes($_POST['username']))), 0, $link);
}
//编辑用户
elseif($_REQUEST['act'] == 'edit')
{
	/* 检查权限 */
    admin_priv('users_false');
	
	$user_id = intval($_REQUEST['id']);
	
	$sql = "SELECT u.address_id,u.user_id,u.user_name,u.uname,u.headimgurl, u.sex,ua.province,ua.city,ua.district,ua.address FROM " .$hhs->table('users'). " u LEFT JOIN " . $hhs->table('user_address') . " ua ON u.address_id = ua.address_id WHERE u.user_id=".$user_id;

    $user_info = $db->GetRow($sql);
	
	$user_info['headimgurl'] = '/data/headimgurl/'.$user_info['headimgurl'];
	
	$province_list = get_regions(1, 1);
	
	$city_list = get_regions(2, $user_info['province']);
	
	$district_list = get_regions(3, $user_info['city']);
	
	$smarty->assign('user_info', $user_info);
   
	$smarty->assign('province_list',    $province_list);
	
	$smarty->assign('city_list',    $city_list);
	
	$smarty->assign('district_list',    $district_list);
	
	$smarty->assign('form_action',    "update");
	
	assign_query_info();
    
	$smarty->display('users_info.htm');
	
}elseif ($_REQUEST['act'] == 'update')
{
	$user_id = empty($_POST['id']) ? 0 : intval($_POST['id']);
	
	$address_id = empty($_POST['address_id']) ? 0 : intval($_POST['address_id']);
	
	$username = empty($_POST['username']) ? '' : trim($_POST['username']);
	
	$sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
	
	$province = empty($_POST['province']) ? 0 : intval($_POST['province']);
	
	$city = empty($_POST['city']) ? 0 : intval($_POST['city']);
	
	$district = empty($_POST['district']) ? 0 : intval($_POST['district']);
	
	$addr = empty($_POST['address']) ? '' : trim($_POST['address']);
    //处理图片
    $user_img_arr = getimagesize($_FILES['headimgurl']['tmp_name']);
    if($user_img_arr[0] > 64 || $goods_img_arr[1] > 64)
    {
        sys_msg('会员图片上传宽高64*64');   
    }
	$img_name = basename($image->upload_image($_FILES['headimgurl'],'headimgurl'));
	
	/* 更新会员的其它信息 */
	
	$other =  array();
	
	if(!empty($img_name))
	{
		//删除用户照片
		$user_info = $db->getOne("SELECT headimgurl FROM " . $hhs->table('users') . " WHERE user_id = '" . $user_id . "'");
		
		if($user_info)
		{
			@unlink(ROOT_PATH.'/data/headimgurl/' . $user_info);
		}
		
		$other['headimgurl']  = $img_name;
	}
	$other['uname'] = $username;
	$other['is_false'] = 1;
	$other['reg_time']  = gmtime();
	$other['sex']  = $sex;
    $db->autoExecute($hhs->table('users'), $other, 'UPDATE',"user_id = '$user_id'");
	
	/*更新会员的收货地址*/
	
	$address = array();
	
	$address['consignee'] = $username;
	
	$address['user_id'] = $user_id;
	
	$address['province'] = $province;
	
	$address['city'] = $city;
	
	$address['district'] = $district;
	
	$address['address'] = $addr;
	
	$address['country'] = 1;
	
	$db->autoExecute($hhs->table('user_address'), $address, 'UPDATE',"address_id = '$address_id'");
	
    /* 记录管理员操作 */
    admin_log($_POST['username'], 'edit', 'users');
	
    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users_false.php?act=list');
    
	sys_msg(sprintf('修改成功', htmlspecialchars(stripslashes($_POST['username']))), 0, $link);

}



/*------------------------------------------------------ */
//-- 显示图片
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'show_image')
{

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $img_url = $_GET['img_url'];
    }
    else
    {
        if (strpos($_GET['img_url'], 'http://') === 0)
        {
            $img_url = $_GET['img_url'];
        }
        else
        {
            $img_url = '../' . $_GET['img_url'];
        }
    }
    $smarty->assign('img_url', $img_url);
    $smarty->display('goods_show_image.htm');
}







/*------------------------------------------------------ */
//-- 批量删除会员帐号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch_remove')
{
    /* 检查权限 */
    admin_priv('users_false');
    if (isset($_POST['checkboxes']))
    {
		foreach($_POST['checkboxes'] as $v)
		{
			//删除会员评论
			drop_user_comment($v);
			//删除用户照片
			$user_info = $db->getOne("SELECT headimgurl FROM " . $hhs->table('users') . " WHERE user_id = '" . $v. "'");
			
			if($user_info)
			{
				@unlink(ROOT_PATH.'/data/headimgurl/' . $user_info);
			}
		}
        $sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id " . db_create_in($_POST['checkboxes']);
        $col = $db->getCol($sql);
        $usernames = implode(',',addslashes_deep($col));
        $count = count($col);
        /* 通过插件来删除用户 */
        $users =& init_users();
        $users->remove_user($col);

        admin_log($usernames, 'batch_remove', 'users');

        $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'users_false.php?act=list');
        sys_msg(sprintf($_LANG['batch_remove_success'], $count), 0, $lnk);
    }
    else
    {
        $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'users_false.php?act=list');
        sys_msg($_LANG['no_select_user'], 0, $lnk);
    }
}

/* 编辑用户名 */
elseif ($_REQUEST['act'] == 'edit_username')
{
    /* 检查权限 */
    check_authz_json('users_false');
    $username = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));
    $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    if ($id == 0)
    {
        make_json_error('NO USER ID');
        return;
    }
    if ($username == '')
    {
        make_json_error($GLOBALS['_LANG']['username_empty']);
        return;
    }
    $users =& init_users();
    if ($users->edit_user($id, $username))
    {
        if ($_CFG['integrate_code'] != 'hhshop')
        {
            /* 更新商城会员表 */
            $db->query('UPDATE ' .$hhs->table('users'). " SET user_name = '$username' WHERE user_id = '$id'");
        }
        admin_log(addslashes($username), 'edit', 'users');
        make_json_result(stripcslashes($username));
    }
    else
    {
        $msg = ($users->error == ERR_USERNAME_EXISTS) ? $GLOBALS['_LANG']['username_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
        make_json_error($msg);
    }
}
/*------------------------------------------------------ */
//-- 编辑email
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_email')
{
    /* 检查权限 */
    check_authz_json('users_false');

    $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $email = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));
    $users =& init_users();
    $sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id = '$id'";
    $username = $db->getOne($sql);
    if (is_email($email))
    {
        if ($users->edit_user(array('username'=>$username, 'email'=>$email)))
        {
            admin_log(addslashes($username), 'edit', 'users');

            make_json_result(stripcslashes($email));
        }
        else
        {
            $msg = ($users->error == ERR_EMAIL_EXISTS) ? $GLOBALS['_LANG']['email_exists'] : $GLOBALS['_LANG']['edit_user_failed'];
            make_json_error($msg);
        }
    }
    else
    {
        make_json_error($GLOBALS['_LANG']['invalid_email']);
    }
}

/*------------------------------------------------------ */
//-- 删除会员帐号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')
{
    /* 检查权限 */
    admin_priv('users_false');
    $sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
	//查询该会员是否有评论
	drop_user_comment($_GET['id']);
	//删除用户照片
	$user_info = $db->getOne("SELECT headimgurl FROM " . $hhs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'");
	if($user_info)
	{
		@unlink(ROOT_PATH.'/data/headimgurl/' . $user_info);
	}
    $username = $db->getOne($sql);
    /* 通过插件来删除用户 */
    $users =& init_users();
    $users->remove_user($username); //已经删除用户所有数据
    /* 记录管理员操作 */
    admin_log(addslashes($username), 'remove', 'users');
    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users_false.php?act=list');
    sys_msg(sprintf($_LANG['remove_success'], $username), 0, $link);
}
/*------------------------------------------------------ */
//-- 脱离推荐关系
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove_parent')
{
    /* 检查权限 */
    admin_priv('users_manage');

    $sql = "UPDATE " . $hhs->table('users') . " SET parent_id = 0 WHERE user_id = '" . $_GET['id'] . "'";
    $db->query($sql);

    /* 记录管理员操作 */
    $sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id = '" . $_GET['id'] . "'";
    $username = $db->getOne($sql);
    admin_log(addslashes($username), 'edit', 'users');

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'users.php?act=list');
    sys_msg(sprintf($_LANG['update_success'], $username), 0, $link);
}
/*------------------------------------------------------ */
//-- 查看用户推荐会员列表
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'aff_list')
{
    /* 检查权限 */
    admin_priv('users_manage');
    $smarty->assign('ur_here',      $_LANG['03_users_list']);

    $auid = $_GET['auid'];
    $user_list['user_list'] = array();

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    $smarty->assign('affiliate', $affiliate);

    empty($affiliate) && $affiliate = array();

    $num = count($affiliate['item']);
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i<=$num; $i++)
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
                $count++;
            }
        }
        $all_count += $count;

        if ($count)
        {
            $sql = "SELECT user_id, user_name, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
                    " FROM " . $GLOBALS['hhs']->table('users') . " WHERE user_id IN($up_uid)" .
                    " ORDER by level, user_id";
            $user_list['user_list'] = array_merge($user_list['user_list'], $db->getAll($sql));
        }
    }

    $temp_count = count($user_list['user_list']);
    for ($i=0; $i<$temp_count; $i++)
    {
        $user_list['user_list'][$i]['reg_time'] = local_date($_CFG['date_format'], $user_list['user_list'][$i]['reg_time']);
    }

    $user_list['record_count'] = $all_count;

    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('action_link',  array('text' => $_LANG['back_note'], 'href'=>"users.php?act=edit&id=$auid"));

    assign_query_info();
    $smarty->display('affiliate_list.htm');
}

/**
 *  返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function user_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['rank'] = empty($_REQUEST['rank']) ? 0 : intval($_REQUEST['rank']);
        $filter['pay_points_gt'] = empty($_REQUEST['pay_points_gt']) ? 0 : intval($_REQUEST['pay_points_gt']);
        $filter['pay_points_lt'] = empty($_REQUEST['pay_points_lt']) ? 0 : intval($_REQUEST['pay_points_lt']);

        $filter['sort_by']    = empty($_REQUEST['sort_by'])    ? 'user_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);
		
        $ex_where = ' WHERE 1 AND is_false = 1';
        if ($filter['keywords'])
        {
            $ex_where .= " AND user_name LIKE '%" . mysql_like_quote($filter['keywords']) ."%' or  uname LIKE '%" . mysql_like_quote($filter['keywords']) ."%'";
        }
        if ($filter['rank'])
        {
            $sql = "SELECT min_points, max_points,is_subscribe, special_rank FROM ".$GLOBALS['hhs']->table('user_rank')." WHERE rank_id = '$filter[rank]'";
            $row = $GLOBALS['db']->getRow($sql);
            if ($row['special_rank'] > 0)
            {
                /* 特殊等级 */
                $ex_where .= " AND user_rank = '$filter[rank]' ";
            }
            else
            {
                $ex_where .= " AND rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']);
            }
        }
        if ($filter['pay_points_gt'])
        {
             $ex_where .=" AND pay_points >= '$filter[pay_points_gt]' ";
        }
        if ($filter['pay_points_lt'])
        {
            $ex_where .=" AND pay_points < '$filter[pay_points_lt]' ";
        }
        if ($filter['is_subscribe']!='')
        {
             $ex_where .=" AND is_subscribe = '$filter[is_subscribe]' ";
        }
        $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('users') . $ex_where);

        /* 分页大小 */
        $filter = page_and_size($filter);
        $sql = "SELECT comment_num,headimgurl,sex,user_id, user_name,is_subscribe,uname, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
                " FROM " . $GLOBALS['hhs']->table('users') . $ex_where .
                " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
                " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $user_list = $GLOBALS['db']->getAll($sql);

    $count = count($user_list);
    for ($i=0; $i<$count; $i++)
    {
        $user_list[$i]['reg_time'] = local_date($GLOBALS['_CFG']['date_format'], $user_list[$i]['reg_time']);
		
		$user_list[$i]['headimgurl'] = '/data/headimgurl/'.$user_list[$i]['headimgurl'];
    }

    $arr = array('user_list' => $user_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

//查询判断指定用户是否有评论并删除
function drop_user_comment($user_id)
{
	$user_id = intval($user_id);
	$user_num = $GLOBALS['db']->getRow(" select count(*) from ". $GLOBALS['hhs']->table('comment') . " where user_id = ".$user_id);
	if($user_num > 0)
	{
		$sql = "DELETE FROM " . $GLOBALS['hhs']->table('comment') .
                " WHERE user_id = '$user_id'";
        $GLOBALS['db']->query($sql);	
	}
}
?>