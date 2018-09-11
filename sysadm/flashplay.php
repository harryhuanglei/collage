<?php


define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
$uri = $hhs->url();
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');

/*------------------------------------------------------ */
//-- 系统
/*------------------------------------------------------ */
if ($_REQUEST['act']== 'list')
{
    /* 判断系统当前设置 如果为用户自定义 则跳转到自定义 */
    if ($_CFG['index_ad'] == 'cus')
    {
        hhs_header("Location: flashplay.php?act=custom_list\n");
        exit;
    }

    $playerdb = get_flash_xml();
    foreach ($playerdb as $key => $val)
    {
        if (strpos($val['src'], 'http') === false)
        {
            $playerdb[$key]['src'] = $uri . $val['src'];
        }
    }

    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => ''),
        'cus' => array('text' => $_LANG['custom_set'], 'url' => 'flashplay.php?act=custom_list')
                       );

    assign_query_info();
    $flash_dir = ROOT_PATH . 'data/flashdata/';

    $smarty->assign('current', 'sys');
    $smarty->assign('group_list', $group_list);
    $smarty->assign('group_selected', $_CFG['index_ad']);
    $smarty->assign('uri', $uri);
    $smarty->assign('ur_here', $_LANG['flashplay']);
    $smarty->assign('action_link_special', array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add'));

    $smarty->assign('current_flashtpl', $_CFG['flash_theme']);
    $smarty->assign('playerdb', $playerdb);
    $smarty->display('flashplay_list.htm');
}
elseif($_REQUEST['act']== 'del')
{
    admin_priv('flash_manage');

    $id = (int)$_GET['id'];
    $flashdb = get_flash_xml();
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['id_error'], 0, $links);
    }

    if (strpos($rt['src'], 'http') === false)
    {
        @unlink(ROOT_PATH . $rt['src']);
    }
    $temp = array();
    foreach ($flashdb as $key => $val)
    {
        if ($key != $id)
        {
            $temp[] = $val;
        }
    }
    put_flash_xml($temp);

    hhs_header("Location: flashplay.php?act=list\n");
    exit;
}
elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('flash_manage');

    if (empty($_POST['step']))
    {
        $url = isset($_GET['url']) ? $_GET['url'] : 'http://';
        $src = isset($_GET['src']) ? $_GET['src'] : '';
        $sort = 0;
        $rt = array('act'=>'add','img_url'=>$url,'img_src'=>$src, 'img_sort'=>$sort);

        assign_query_info();

        $smarty->assign('action_link', array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list'));
        $smarty->assign('rt', $rt);
        $smarty->assign('ur_here', $_LANG['add_picad']);
        $smarty->display('flashplay_add.htm');
    }
    elseif ($_POST['step'] == 2)
    {
        if (!empty($_FILES['img_file_src']['name']))
        {
            if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['img_file_src']['name']));
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;
            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/' . $name;
            }
        }
        elseif (!empty($_POST['img_src']))
        {
            $src = $_POST['img_src'];

            if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
            {
                $src = get_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add');
            sys_msg($_LANG['src_empty'], 0, $links);
        }

        if (empty($_POST['img_url']))
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add');
            sys_msg($_LANG['link_empty'], 0, $links);
        }

        // 获取flash播放器数据
        $flashdb = get_flash_xml();

        // 插入新数据
        array_unshift($flashdb, array('src'=>$src, 'url'=>$_POST['img_url'], 'text'=>$_POST['img_text'] ,'sort'=>$_POST['img_sort']));

        // 实现排序
        $flashdb_sort   = array();
        $_flashdb       = array();
        foreach ($flashdb as $key => $value)
        {
            $flashdb_sort[$key] = $value['sort'];
        }
        asort($flashdb_sort, SORT_NUMERIC);
        foreach ($flashdb_sort as $key => $value)
        {
            $_flashdb[] = $flashdb[$key];
        }
        unset($flashdb, $flashdb_sort);

        put_flash_xml($_flashdb);
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}
elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('flash_manage');

    $id = (int)$_REQUEST['id']; //取得id
    $flashdb = get_flash_xml(); //取得数据
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['id_error'], 0, $links);
    }
    if (empty($_POST['step']))
    {
        $rt['act'] = 'edit';
        $rt['img_url'] = $rt['url'];
        $rt['img_src'] = $rt['src'];
        $rt['img_txt'] = $rt['text'];
        $rt['img_sort'] = empty($rt['sort']) ? 0 : $rt['sort'];

        $rt['id'] = $id;
        $smarty->assign('action_link', array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list'));
        $smarty->assign('rt', $rt);
        $smarty->assign('ur_here', $_LANG['edit_picad']);
        $smarty->display('flashplay_add.htm');
    }
    elseif ($_POST['step'] == 2)
    {
        if (empty($_POST['img_url']))
        {
            //若链接地址为空
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id);
            sys_msg($_LANG['link_empty'], 0, $links);
        }

        if (!empty($_FILES['img_file_src']['name']))
        {
            if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            //有上传
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['img_file_src']['name']));
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/' . $name;
            }
        }
        else if (!empty($_POST['img_src']))
        {
            $src =$_POST['img_src'];

            if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
            {
                $src = get_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id);
            sys_msg($_LANG['src_empty'], 0, $links);
        }

        if (strpos($rt['src'], 'http') === false && $rt['src'] != $src)
        {
            @unlink(ROOT_PATH . $rt['src']);
        }
        $flashdb[$id] = array('src'=>$src,'url'=>$_POST['img_url'],'text'=>$_POST['img_text'],'sort'=>$_POST['img_sort']);

        // 实现排序
        $flashdb_sort   = array();
        $_flashdb       = array();
        foreach ($flashdb as $key => $value)
        {
            $flashdb_sort[$key] = $value['sort'];
        }
        asort($flashdb_sort, SORT_NUMERIC);
        foreach ($flashdb_sort as $key => $value)
        {
            $_flashdb[] = $flashdb[$key];
        }
        unset($flashdb, $flashdb_sort);

        put_flash_xml($_flashdb);
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}

function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}

function put_flash_xml($flashdb)
{
    if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item item_url="' . $val['src'] . '" link="' . $val['url'] . '" text="' . $val['text'] . '" sort="' . $val['sort'] . '"/>';
        }
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml', $xml);
    }
    else
    {
        @unlink(ROOT_PATH . DATA_DIR . '/flash_data.xml');
    }
}

function get_url_image($url)
{
    $ext = strtolower(end(explode('.', $url)));
    if($ext != "gif" && $ext != "jpg" && $ext != "png" && $ext != "bmp" && $ext != "jpeg")
    {
        return $url;
    }

    $name = date('Ymd');
    for ($i = 0; $i < 6; $i++)
    {
        $name .= chr(mt_rand(97, 122));
    }
    $name .= '.' . $ext;
    $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

    $tmp_file = DATA_DIR . '/afficheimg/' . $name;
    $filename = ROOT_PATH . $tmp_file;

    $img = file_get_contents($url);

    $fp = @fopen($filename, "a");
    fwrite($fp, $img);
    fclose($fp);

    return $tmp_file;
}


?>