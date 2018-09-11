<?php







/**



 * 小舍电商 站点管理



 * ============================================================================



 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。



 * 网站地址: http://www.baidu.com；



 * ----------------------------------------------------------------------------



 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和



 * 使用；不允许对程序代码以任何形式任何目的的再发布。



 * ============================================================================



 * $Author: pangbin $



 * $Id: site.php 17217 2014-05-12 06:29:08Z pangbin $



*/







define('IN_HHS', true);







require(dirname(__FILE__) . '/includes/init.php');



include_once(ROOT_PATH . 'includes/cls_image.php');



$image = new cls_image($_CFG['bgcolor']);







$exc = new exchange($hhs->table('site'), $db, 'id', 'name');







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



//-- 站点列表页面



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'list')



{



    /* 模板赋值 */



    $smarty->assign('ur_here',    '站点列表');



    $smarty->assign('action_link', array('text' => '添加站点', 'href' => 'site.php?act=add'));



     $smarty->assign('full_page',   1);







    /* 获取站点数据 */



    $links_list = get_site_list();







    $smarty->assign('links_list',      $links_list['list']);



    $smarty->assign('filter',          $links_list['filter']);



    $smarty->assign('record_count',    $links_list['record_count']);



    $smarty->assign('page_count',      $links_list['page_count']);







    $sort_flag  = sort_flag($links_list['filter']);



    $smarty->assign($sort_flag['tag'], $sort_flag['img']);







    assign_query_info();



    $smarty->display('site_list.htm');



}







/*------------------------------------------------------ */



//-- 排序、分页、查询



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'query')



{



    /* 获取站点数据 */



    $links_list = get_site_list();







    $smarty->assign('links_list',      $links_list['list']);



    $smarty->assign('filter',          $links_list['filter']);



    $smarty->assign('record_count',    $links_list['record_count']);



    $smarty->assign('page_count',      $links_list['page_count']);







    $sort_flag  = sort_flag($links_list['filter']);



    $smarty->assign($sort_flag['tag'], $sort_flag['img']);







    make_json_result($smarty->fetch('site_list.htm'), '',



        array('filter' => $links_list['filter'], 'page_count' => $links_list['page_count']));



}







/*------------------------------------------------------ */



//-- 添加新链接页面



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'add')



{



    admin_priv('site_cfg');



	



     $smarty->assign('provinces',    get_regions(1, '1'));



     $smarty->assign('cities',    get_regions(2,24));







    $smarty->assign('ur_here',    '站点列表');



    $smarty->assign('action_link', array('href'=>'site.php?act=list', 'text' => '站点列表'));



    $smarty->assign('action',      'add');



    $smarty->assign('form_act',    'insert');







    assign_query_info();



    $smarty->display('site_info.htm');



}







/*------------------------------------------------------ */



//-- 处理添加的链接



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'insert')



{



    /* 变量初始化 */



   		$site_logo = '';



 		$city_id = $_REQUEST['city_id'];



		$name = $_REQUEST['name'];



		$keywords = $_REQUEST['keywords'];



		$description = $_REQUEST['description'];



		$province_id = $_REQUEST['province_id'];



		$close = $_REQUEST['close'];



		



		$count = $db->getOne("select count(*) from ".$hhs->table('site')." where city_id='$city_id'");



		if($count)



		{



			sys_msg('此站点已开通');	



		}







    /* 处理上传的LOGO图片 */



        if ((isset($_FILES['site_logo']['error']) && $_FILES['site_logo']['error'] == 0) || (!isset($_FILES['site_logo']['error']) && isset($_FILES['site_logo']['tmp_name']) && $_FILES['site_logo']['tmp_name'] != 'none'))



        {



			



            $img_up_info = @basename($image->upload_image($_FILES['site_logo'], 'site_logo'));



            $site_logo   = DATA_DIR . '/site_logo/' .$img_up_info;



        }







        /* 插入数据 */



        $sql    = "INSERT INTO ".$hhs->table('site')." (close,name,province_id, city_id, site_logo, keywords,description) ".



                  "VALUES ('$close','$name','$province_id', '$city_id', '$site_logo', '$keywords','$description')";



        $db->query($sql);







     







        /* 清除缓存 */



        clear_cache_files();







        /* 提示信息 */



        $link[0]['text'] = '继续添加';



        $link[0]['href'] = 'site.php?act=add';







        $link[1]['text'] = '返回列表';



        $link[1]['href'] = 'site.php?act=list';







        sys_msg($_LANG['add'] . "&nbsp;" .stripcslashes($_POST['name']) . " " . '添加成功',0, $link);







  



}



elseif ($_REQUEST['act'] == 'is_close')



{



    check_authz_json('site_cfg');







    $id = intval($_REQUEST['id']);



	



    $sql = "SELECT id, close



            FROM " . $hhs->table('site') . "



            WHERE id = '$id'";



    $site = $db->getRow($sql, TRUE);



    if ($site['id'])



    {



        $_site['close'] = empty($site['close']) ? 1 : 0;



        $db->autoExecute($hhs->table('site'), $_site, '', "id = '$id'");



        clear_cache_files();



        make_json_result($_site['close']);



    }







    exit;



}



/*------------------------------------------------------ */



//-- 编辑链接名称



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'edit_site_name')



{



    check_authz_json('site_cfg');







    $id        = intval($_POST['id']);



    $link_name = json_str_iconv(trim($_POST['val']));







    /* 检查链接名称是否重复 */



    if ($exc->num("name", $link_name, $id) != 0)



    {



        make_json_error(sprintf('该名称已存在', $link_name));



    }



    else



    {



        if ($exc->edit("name = '$link_name'", $id))



        {



            clear_cache_files();



            make_json_result(stripslashes($link_name));



        }



        else



        {



            make_json_error($db->error());



        }



    }



}



/*------------------------------------------------------ */



//-- 站点编辑页面



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'edit')



{



    admin_priv('site_cfg');







    /* 取得站点数据 */



    $sql = "SELECT * ".



           "FROM " .$hhs->table('site'). " WHERE id = '".intval($_REQUEST['id'])."'";



    $link_arr = $db->getRow($sql);









     $smarty->assign('provinces',    get_regions(1,1));



     $smarty->assign('cities',    get_regions(2,$link_arr['province_id']));











    /* 模板赋值 */



    $smarty->assign('ur_here',    '编辑站点');



    $smarty->assign('action_link', array('href'=>'site.php?act=list&' . list_link_postfix(), 'text' => '站点列表'));



    $smarty->assign('form_act',    'update');



    $smarty->assign('action',      'edit');







    $smarty->assign('type',        $type);



    $smarty->assign('link_logo',   $link_logo);



    $smarty->assign('link_arr',    $link_arr);







    assign_query_info();



    $smarty->display('site_info.htm');



}







/*------------------------------------------------------ */



//-- 编辑链接的处理页面



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'update')



{



    /* 变量初始化 */



    $id         = (!empty($_REQUEST['id']))      ? intval($_REQUEST['id'])      : 0;



	$city_id = $_REQUEST['city_id'];



	$name = $_REQUEST['name'];



	$keywords = $_REQUEST['keywords'];



	$description = $_REQUEST['description'];



	$province_id = $_REQUEST['province_id'];





    $close = intval($_REQUEST['close']) ? 1 : 0;



    /* 如果有图片LOGO要上传 */



    if ((isset($_FILES['site_logo']['error']) && $_FILES['site_logo']['error'] == 0) || (!isset($_FILES['site_logo']['error']) && isset($_FILES['site_logo']['tmp_name']) && $_FILES['site_logo']['tmp_name'] != 'none'))



    {



        $img_up_info = @basename($image->upload_image($_FILES['site_logo'], 'site_logo'));



        $link_logo   = ", site_logo = ".'\''. DATA_DIR . '/site_logo/'.$img_up_info.'\'';



    }







    /* 更新信息 */



    $sql = "UPDATE " .$hhs->table('site'). " SET ".



            "city_id = '$city_id', ".



            "name = '$name', ".



			"province_id = '$province_id', ".



			"close = '$close', ".



			



			"keywords = '$keywords' ".



            $link_logo.',  '.



			"description = '$description' ".



            "WHERE id = '$id'";







    $db->query($sql);



    /* 记录管理员操作 */







    /* 清除缓存 */



    clear_cache_files();







    /* 提示信息 */



    $link[0]['text'] = '返回列表';



    $link[0]['href'] = 'site.php?act=list&' . list_link_postfix();







    sys_msg('编辑成功',0, $link);



}











/*------------------------------------------------------ */



//-- 删除站点



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'remove')



{



    check_authz_json('site_cfg');







    $id = intval($_GET['id']);







    /* 获取链子LOGO,并删除 */



    $link_logo = $exc->get_name($id, "site_logo");







    if ((strpos($link_logo, 'http://') === false) && (strpos($link_logo, 'https://') === false))



    {



        $img_name = basename($link_logo);



        @unlink(ROOT_PATH. DATA_DIR . '/site_logo/'.$img_name);



    }







    $exc->drop($id);



    clear_cache_files();







    $url = 'site.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);







    hhs_header("Location: $url\n");



    exit;



}















/* 获取站点数据列表 */



function get_site_list()



{



    $result = get_filter();



    if ($result === false)



    {



        $filter = array();



        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);



        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);







        /* 获得总记录数据 */



        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['hhs']->table('site');



        $filter['record_count'] = $GLOBALS['db']->getOne($sql);







        $filter = page_and_size($filter);







        /* 获取数据 */



        $sql  = 'SELECT * '.



               ' FROM ' .$GLOBALS['hhs']->table('site').



                " ORDER by $filter[sort_by] $filter[sort_order]";







        set_filter($filter, $sql);



    }



    else



    {



        $sql    = $result['sql'];



        $filter = $result['filter'];



    }



    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);







    $list = array();



    while ($rows = $GLOBALS['db']->fetchRow($res))



    {



		$rows['region_name'] =get_region_name($rows['city_id']);



        $list[] = $rows;



    }











    return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



}







?>



