<?php

/**

 * 小舍电商 行业管理

 * ============================================================================

 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。

 * 网站地址: http://www.baidu.com；

 * ----------------------------------------------------------------------------

 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

 * 使用；不允许对程序代码以任何形式任何目的的再发布。

 * ============================================================================

 * $Author: pangbin $

 * $Id: hangye.php 17217 2014-05-12 06:29:08Z pangbin $

*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

include_once(ROOT_PATH . 'includes/cls_image.php');

$image = new cls_image($_CFG['bgcolor']);

$exc = new exchange($hhs->table('hangye'), $db, 'id', 'name');

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

//-- 行业列表页面

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')

{

	/* 检查权限 */

    admin_priv('hangye_list');


    /* 取得参数：上级id */

    $pid = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);

    $smarty->assign('pid',    $pid);

    /* 返回上一级的链接 */

    if ($pid > 0)

    {

        $action_link = array('text' => '返回上一级', 'href' => 'hangye.php?act=list');

    }

    else

    {

        $action_link = '';

    }

    $smarty->assign('action_link',  $action_link);



    $sql  = 'SELECT * '.

           ' FROM ' .$GLOBALS['hhs']->table('hangye').

            " where pid =" . $pid;



    $hangyes = $db->getAll($sql);

    $smarty->assign('hangyes',   $hangyes);



    /* 模板赋值 */

    $smarty->assign('ur_here',    '行业列表');

     $smarty->assign('full_page',   1);



    $smarty->display('hangye_list.htm');

}

/*------------------------------------------------------ */

//-- 排序、分页、查询

/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'query')

{
	

    /* 获取行业数据 */

    $links_list = get_hangye_list();

    $smarty->assign('links_list',      $links_list['list']);

    $smarty->assign('filter',          $links_list['filter']);

    $smarty->assign('record_count',    $links_list['record_count']);

    $smarty->assign('page_count',      $links_list['page_count']);

    $sort_flag  = sort_flag($links_list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('hangye_list.htm'), '',

        array('filter' => $links_list['filter'], 'page_count' => $links_list['page_count']));

}



/*------------------------------------------------------ */

//-- 处理添加的链接

/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert')

{

    /* 变量初始化 */

    $name = $_REQUEST['name'];

    $pid = intval($_REQUEST['pid']);

    /* 插入数据 */

    $sql    = "INSERT INTO ".$hhs->table('hangye')." (pid,name) ".

              "VALUES ('$pid','$name')";

    $db->query($sql);



    $sql  = 'SELECT * '.

           ' FROM ' .$GLOBALS['hhs']->table('hangye').

            " where pid =" . $pid;



    $hangyes = $db->getAll($sql);

    $smarty->assign('hangyes',   $hangyes);



    make_json_result($smarty->fetch('hangye_list.htm'));

}



/*------------------------------------------------------ */

//-- 编辑链接名称

/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_name')

{

    // check_authz_json('hangye_manage');

    $id        = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 检查链接名称是否重复 */

    if ($exc->num("name", $name, $id) != 0)

    {

        make_json_error(sprintf('该名称已存在', $name));

    }

    else

    {

        if ($exc->edit("name = '$name'", $id))

        {

            clear_cache_files();

            make_json_result(stripslashes($name));

        }

        else

        {

            make_json_error($db->error());

        }

    }

}



/*------------------------------------------------------ */

//-- 删除行业

/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')

{

    $id = intval($_REQUEST['id']);



    $sql = "SELECT pid FROM " . $hhs->table('hangye') . " WHERE id = '$id'";

    $pid = $db->getOne($sql);



    $sql="DELETE FROM ". $hhs->table("hangye")." WHERE id=" . $id ." or pid=" . $id;

    $db->query($sql);



    $sql  = 'SELECT * '.

           ' FROM ' .$GLOBALS['hhs']->table('hangye').

            " where pid =" . $pid;



    $hangyes = $db->getAll($sql);

    $smarty->assign('hangyes',   $hangyes);



    make_json_result($smarty->fetch('hangye_list.htm'));

}

?>

