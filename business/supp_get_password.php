<?php
define('IN_HHS', true);

/* 操作项的初始化 */
if (empty($_SERVER['REQUEST_METHOD']))
{
    $_SERVER['REQUEST_METHOD'] = 'GET';
}
else
{
    $_SERVER['REQUEST_METHOD'] = trim($_SERVER['REQUEST_METHOD']);
}

/*------------------------------------------------------ */
//-- 填写管理员帐号和email页面
/*------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    //验证从邮件地址过来的链接
    if (!empty($_GET['act']) && $_GET['act'] == 'reset_pwd')
    {
        $code    = !empty($_GET['code']) ? trim($_GET['code'])  : '';
        $adminid = !empty($_GET['suppliers_id'])  ? intval($_GET['suppliers_id']) : 0;

        if ($adminid == 0 || empty($code))
        {
            hhs_header("Location: index.php?op=login&act=login\n");
            exit;
        }

        /* 以用户的原密码，与code的值匹配 */
        $sql = 'SELECT password FROM ' .$hhs->table('suppliers'). " WHERE suppliers_id = '$adminid'";
        $password = $db->getOne($sql);

        if (md5($adminid . $password) <> $code)
        {
            show_message('您执行了一个不合法的请求，请返回！');
			hhs_header("Location:index.php?op=login&act=login\n");
			exit;
        }
        else
        {
            $smarty->assign('adminid',  $adminid);
            $smarty->assign('code',     $code);
            $smarty->assign('form_act', 'reset_pwd');
        }
    }
    elseif (!empty($_GET['act']) && $_GET['act'] == 'forget_pwd')
    {
        $smarty->assign('form_act', 'forget_pwd');
    }

    $smarty->assign('ur_here', $_LANG['get_newpassword']);

    $smarty->display('supp_get_password.dwt');
}

/*------------------------------------------------------ */
//-- 验证管理员帐号和email, 发送邮件
/*------------------------------------------------------ */
else
{
    /* 发送找回密码确认邮件 */
    if (!empty($_POST['action']) && $_POST['action'] == 'get_pwd')
    {
        $admin_username = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
        $admin_email    = !empty($_POST['email'])     ? trim($_POST['email'])     : '';

        if (empty($admin_username) || empty($admin_email))
        {
            hhs_header("Location: index.php?op=login&act=login\n");
            exit;
        }

        /* 管理员用户名和邮件地址是否匹配，并取得原密码 */
        $sql = 'SELECT suppliers_id, password FROM ' .$hhs->table('suppliers').
               " WHERE user_name = '$admin_username' AND email = '$admin_email'";
        $admin_info = $db->getRow($sql);

        if (!empty($admin_info))
        {
            /* 生成验证的code */
            $admin_id = $admin_info['suppliers_id'];
            $code     = md5($admin_id . $admin_info['password']);

            /* 设置重置邮件模板所需要的内容信息 */
            $template    = get_mail_template('send_password');
            $reset_email = $hhs->url() . 'index.php?op=get_password&act=reset_pwd&suppliers_id='.$admin_id.'&code='.$code;

            $smarty->assign('user_name',   $admin_username);
            $smarty->assign('reset_email', $reset_email);
            $smarty->assign('shop_name',   $_CFG['shop_name']);
            $smarty->assign('send_date',   local_date($_CFG['date_format']));
            $smarty->assign('sent_date',   local_date($_CFG['date_format']));

            $content = $smarty->fetch('str:' . $template['template_content']);

            /* 发送确认重置密码的确认邮件 */
            if (send_mail($admin_username, $admin_email, $template['template_subject'], $content,
            $template['is_html']))
            {
                show_message('重置密码的邮件已经发到您的邮箱：'.$admin_email);
				hhs_header("Location:index.php?op=login&act=login\n");
			    exit;
            }
            else
            {
                show_message('邮件发送错误, 请检查您的邮件服务器设置!');
            }
        }
        else
        {
            /* 提示信息 */
            show_message('用户名与Email地址不匹配,请返回!');
        }
    }
    /* 验证新密码，更新管理员密码 */
    elseif (!empty($_POST['action']) && $_POST['action'] == 'reset_pwd')
    {
        $new_password = isset($_POST['password']) ? trim($_POST['password'])  : '';
        $adminid      = isset($_POST['adminid'])  ? intval($_POST['adminid']) : 0;
        $code         = isset($_POST['code'])     ? trim($_POST['code'])      : '';

        if (empty($new_password) || empty($code) || $adminid == 0)
        {
            hhs_header("Location: index.php?op=login&act=login\n");
            exit;
        }

        /* 以用户的原密码，与code的值匹配 */
        $sql = 'SELECT password FROM ' .$hhs->table('suppliers'). " WHERE suppliers_id = '$adminid'";
        $password = $db->getOne($sql);

        if (md5($adminid . $password) <> $code)
        {

            show_message('您执行了一个不合法的请求，请返回！');
			hhs_header("Location:index.php?op=login&act=login\n");
			exit;
        }

        //更新管理员的密码

        $sql = "UPDATE " .$hhs->table('suppliers'). "SET password = '".md5($new_password)."' ".
               "WHERE suppliers_id = '$adminid'";
        $result = $db->query($sql);
        if ($result)
        {

            show_message('您的新密码已修改成功！');
			hhs_header("Location:index.php?op=login&act=login\n");
			exit;
        }
        else
        {
            show_message('修改新密码失败！');
        }
    }
}
?>