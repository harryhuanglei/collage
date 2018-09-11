<?php



/**

 * 小舍电商 优惠劵类型的处理

 * ============================================================================

 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。

 * 网站地址: http://www.baidu.com；

 * ----------------------------------------------------------------------------

 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

 * 使用；不允许对程序代码以任何形式任何目的的再发布。

 * ============================================================================

 * $Author: pangbin $

 * $Id: luckmoney.php 17217 2014-05-12 06:29:08Z pangbin $

*/



define('IN_HHS', true);



require(dirname(__FILE__) . '/includes/init.php');



/* act操作项的初始化 */

if (empty($_REQUEST['act']))

{

    $_REQUEST['act'] = 'list';

}

else

{

    $_REQUEST['act'] = trim($_REQUEST['act']);

}





/* 初始化$exc对象 */

$exc = new exchange($hhs->table('luck'), $db, 'id', 'name');



/*------------------------------------------------------ */

//-- 优惠劵类型列表页面

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')

{
	/* 权限判断 */
    admin_priv('distribution_manage');


    $smarty->assign('ur_here',     '微信抢红包');

    $smarty->assign('action_link', array('text' => '添加', 'href' => 'luckmoney.php?act=add'));

    $smarty->assign('full_page',   1);



    $list = get_luck_list();





    $smarty->assign('luck_list',    $list['item']);

    $smarty->assign('filter',       $list['filter']);

    $smarty->assign('record_count', $list['record_count']);

    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    assign_query_info();

    $smarty->display('luckmoney.htm');

}



/*------------------------------------------------------ */

//-- 翻页、排序

/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'query')

{
	check_authz_json('distribution_manage');

    $list = get_luck_list();



    $smarty->assign('luck_list',    $list['item']);

    $smarty->assign('filter',       $list['filter']);

    $smarty->assign('record_count', $list['record_count']);

    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    make_json_result($smarty->fetch('luckmoney.htm'), '',

        array('filter' => $list['filter'], 'page_count' => $list['page_count']));

}



/*------------------------------------------------------ */

//-- 编辑优惠劵类型名称

/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'edit_name')

{

    check_authz_json('bonus_manage');



    $id = intval($_POST['id']);

    $val = json_str_iconv(trim($_POST['val']));



    /* 检查优惠劵类型名称是否重复 */

    if (!$exc->is_only('name', $id, $val))

    {

        make_json_error('活动名重复');

    }

    else

    {

        $exc->edit("name='$val'", $id);



        make_json_result(stripslashes($val));

    }

}



/*------------------------------------------------------ */

//-- 删除优惠劵类型

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'remove')

{

    check_authz_json('distribution_remove');



    $id = intval($_GET['id']);



    $exc->drop($id);





    /* 删除用户的优惠劵 */

    $db->query("DELETE FROM " .$hhs->table('luck_logs'). " WHERE luck_id = '$id'");



    $url = 'luckmoney.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);



    hhs_header("Location: $url\n");

    exit;

}



/*------------------------------------------------------ */

//-- 优惠劵类型添加页面

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'add')

{

    admin_priv('distribution_change');



    $smarty->assign('lang',         $_LANG);

    $smarty->assign('ur_here',      '添加红包');

    $smarty->assign('action_link',  array('href' => 'luckmoney.php?act=list', 'text' => '红包列表'));

    $smarty->assign('action',       'add');

    $smarty->assign('start_at',       date("Y-m-d H:i",strtotime("+1 day")));

    $smarty->assign('end_at',       date("Y-m-d H:i",strtotime("+31 day")));

    





    $smarty->assign('form_act',     'insert');

    $smarty->assign('cfg_lang',     $_CFG['lang']);



    assign_query_info();

    $smarty->display('luck_info.htm');

}



/*------------------------------------------------------ */

//-- 优惠劵类型添加的处理

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'insert')

{

    /* 去掉优惠劵类型名称前后的空格 */

    $name        = !empty($_POST['name']) ? trim($_POST['name']) : '';

    /* 检查类型是否有重复 */

    $sql = "SELECT COUNT(*) FROM " .$hhs->table('luck'). " WHERE `name`='$name'";

    if ($db->getOne($sql) > 0)

    {

        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');

        sys_msg('活动名已经存在', 0, $link);

    }



    

    /* 初始化变量 */

    $num         = !empty($_POST['num']) ? intval($_POST['num']) : 0;

    $money       = !empty($_POST['money']) ? intval($_POST['money']) : 0;

    $limit_times = !empty($_POST['limit_times']) ? intval($_POST['limit_times']) : 0;



    /* 获得日期信息 */

    $start_at = local_strtotime($_POST['start_at']);

    $end_at   = local_strtotime($_POST['end_at']);



    $lucks = generateBouns($money,$num);

    $num   = count($lucks);

    /* 插入数据库。 */

    $sql = "INSERT INTO ".$hhs->table('luck')." (name,num,money,limit_times,start_at, end_at)

    VALUES ('$name','$num','$money','$limit_times','$start_at','$end_at')";



    $db->query($sql);

    $luck_id = $db->insert_id();



    $tmp = array();

    foreach ($lucks as $key => $luck) {

        $tmp[] = '("'.$luck_id.'","'.($luck/100).'")';

        if($key && $key%200 == 0){

            $sql = "INSERT INTO ".$hhs->table('luck_logs')." (luck_id,money) VALUES" . join(',',$tmp);

            $db->query($sql);

            $tmp = array();

        }

        unset($luck);

    }

    if($tmp){

            $sql = "INSERT INTO ".$hhs->table('luck_logs')." (luck_id,money) VALUES" . join(',',$tmp);

            $db->query($sql);

    }

    /* 记录管理员操作 */

    admin_log($name, 'add', 'luck');



    /* 清除缓存 */

    clear_cache_files();



    /* 提示信息 */



    $link[0]['text'] = '返回列表';

    $link[0]['href'] = 'luckmoney.php?act=list';



    sys_msg($_LANG['add'] . "&nbsp;" .$name . "&nbsp;" . $_LANG['attradd_succed'],0, $link);



}





/*------------------------------------------------------ */

//-- 优惠劵列表

/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'bonus_list')

{

    $smarty->assign('full_page',    1);

    $smarty->assign('ur_here',      $_LANG['bonus_list']);

    $smarty->assign('action_link',   array('href' => 'luckmoney.php?act=list', 'text' => '红包列表'));



    $list = get_luck_logs();





    $smarty->assign('logs_list',   $list['item']);

    $smarty->assign('filter',       $list['filter']);

    $smarty->assign('record_count', $list['record_count']);

    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    assign_query_info();

    $smarty->display('luck_log.htm');

}

/*------------------------------------------------------ */

//-- 删除优惠劵

/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove_bonus')

{

    check_authz_json('distribution_remove');



    $id = intval($_GET['id']);



    $db->query("DELETE FROM " .$hhs->table('luck_logs'). " WHERE id='$id'");



    $url = 'luckmoney.php?act=query_bonus&' . str_replace('act=remove_bonus', '', $_SERVER['QUERY_STRING']);



    hhs_header("Location: $url\n");

    exit;

}



/*------------------------------------------------------ */

//-- 批量操作

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'batch')

{

    /* 检查权限 */

    admin_priv('distribution_remove');



    /* 去掉参数：优惠劵类型 */

    $bonus_type_id = intval($_REQUEST['bonus_type']);



    /* 取得选中的优惠劵id */

    if (isset($_POST['checkboxes']))

    {

        $bonus_id_list = $_POST['checkboxes'];



        /* 删除优惠劵 */

        if (isset($_POST['drop']))

        {

            $sql = "DELETE FROM " . $hhs->table('luck_logs'). " WHERE id " . db_create_in($bonus_id_list);

            $db->query($sql);



            admin_log(count($bonus_id_list), 'remove', '微信红包');



            clear_cache_files();



            $link[] = array('text' => '返回列表',

                'href' => 'luckmoney.php?act=bonus_list&bonus_type='. $bonus_type_id);

            sys_msg('操作成功！', 0, $link);

        }

    }

    else

    {

        sys_msg('请选择行', 1);

    }

}

/*------------------------------------------------------ */

//-- 优惠劵列表翻页、排序

/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'query_bonus')

{

    $list = get_luck_logs();



    $smarty->assign('logs_list',   $list['item']);

    $smarty->assign('filter',       $list['filter']);

    $smarty->assign('record_count', $list['record_count']);

    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    make_json_result($smarty->fetch('luck_log.htm'), '',

        array('filter' => $list['filter'], 'page_count' => $list['page_count']));

}







/**

 * 获取优惠劵类型列表

 * @access  public

 * @return void

 */

function get_luck_list()

{

    $result = get_filter();

    if ($result === false)

    {

        /* 查询条件 */

        $filter['sort_by']    = 'id';

        $filter['sort_order'] = 'DESC';





        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('luck')." ";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);



        /* 分页大小 */

        $filter = page_and_size($filter);



        $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('luck'). " ORDER BY $filter[sort_by] $filter[sort_order]";



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

        $row['start_at'] = local_date($GLOBALS['_CFG']['date_format'], $row['start_at']);

        $row['end_at']   = local_date($GLOBALS['_CFG']['date_format'], $row['end_at']);

        $row['rest']     = $GLOBALS['db']->getOne("SELECT count(*) FROM " .$GLOBALS['hhs']->table('luck_logs'). " WHERE `user_id` is null and  `luck_id` = " . $row['id']);

        $arr[]           = $row;

    }



    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}





/**

 * 获取用户优惠劵列表

 * @access  public

 * @param   $page_param

 * @return void

 */

function get_luck_logs()

{

    /* 查询条件 */

    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);

    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $filter['luck_id'] = empty($_REQUEST['luck_id']) ? 0 : intval($_REQUEST['luck_id']);



    $where = empty($filter['luck_id']) ? '' : " WHERE l.luck_id='$filter[luck_id]'";



    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('luck_logs')."as l ". $where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    /* 分页大小 */

    $filter = page_and_size($filter);



    $sql = "SELECT l.*,u.`uname` as 'user_name',k.`name` ".

          " FROM ".$GLOBALS['hhs']->table('luck_logs'). " as l

            left join ".$GLOBALS['hhs']->table('users'). " as u ON u.`user_id` = l.`user_id`

            left join ".$GLOBALS['hhs']->table('luck'). " as k ON k.`id` = l.`luck_id`

           $where ".

          " ORDER BY ".$filter['sort_by']." ".$filter['sort_order'].

          " LIMIT ". $filter['start'] .", $filter[page_size]";

    $rows = $GLOBALS['db']->getAll($sql);



    $arr = array('item' => $rows, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}



function generateBouns($total,$num)

{

    $total = 100 * $total;

    $min = 100;

    $bonus = array();

    for ($i=1;$i<=$num;$i++)

    {

        if($total<100) continue;

        if($i<$num)

        {

            $top   = ($total-($num-$i)*$min)/($num-$i);//随机安全上限

            $k     = ($num-$i) > 3 ? ($num-$i) : 2;

            $top   = intval($total/$k);

            $money = max($min,rand($min,$top));

            $total = $total-$money;

            if($total<100){

                $money = $money + $total;

                $total = 0;

            }

        }

        else{

            $money = $total;

            $total = 0;

        }

        $bonus[] = $money;

    }

    shuffle($bonus);

    return $bonus;

}



?>

