<?php







/**



 * HHSHOP 菜单管理程序



 * ============================================================================



 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。



 * 网站地址: http://www.hhshop.com；



 * ----------------------------------------------------------------------------



 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和



 * 使用；不允许对程序代码以任何形式任何目的的再发布。



 * ============================================================================



 * $Author: liubo $



 * $Id: weixin_menu.php 17217 2011-01-19 06:29:08Z liubo $



*/







define('IN_HHS', true);







require(dirname(__FILE__) . '/includes/init.php');



$exc = new exchange($hhs->table("weixin_menu"), $db, 'cat_id', 'cat_name');



/* act操作项的初始化 */



$_REQUEST['act'] = trim($_REQUEST['act']);



if (empty($_REQUEST['act']))



{



    $_REQUEST['act'] = 'list';



}

$smarty->assign('lang',     $_LANG);



if ($_REQUEST['act'] == 'list')



{
	/* 检查权限 */

    admin_priv('wx_share');


    $share_list = share_list();



    $smarty->assign('ur_here',     '分享数据统计');



   

    $smarty->assign('full_page',   1);



    $smarty->assign('share_list',        $share_list['row']);

    $smarty->assign('filter',       $share_list['filter']);

    $smarty->assign('record_count', $share_list['record_count']);

    $smarty->assign('page_count',   $share_list['page_count']);

    $smarty->assign('full_page',    1);

    

    assign_query_info();



    $smarty->display('wx_share_list.htm');



}







/*------------------------------------------------------ */



//-- 查询



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'query')



{



    $share_list = share_list();



    $smarty->assign('share_list',        $share_list['row']);

    $smarty->assign('filter',       $share_list['filter']);

    $smarty->assign('record_count', $share_list['record_count']);

    $smarty->assign('page_count',   $share_list['page_count']);

    $smarty->assign('full_page',    0);



    make_json_result($smarty->fetch('wx_share_list.htm'), '', array('filter' => $share_list['filter'], 'page_count' => $share_list['page_count']));



}







/*------------------------------------------------------ */



//-- 删除菜单



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'batch')
{
	if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
	{
		sys_msg('请选择要移除信息', 1);
	}

	$db->query("delete from ".$GLOBALS['hhs']->table('share_info')." where id ".db_create_in(join(',', $_POST['checkboxes'])));

	
    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => '返回列表', 'href' => 'wx_share.php?act=list');
    sys_msg('删除成功 ', 0, $lnk);
}





function share_list()

{

   

    $result = false;

    if ($result === false)
    {
		
	
		
        //$filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);

        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['uname']          = empty($_REQUEST['uname']) ? '' : trim($_REQUEST['uname']);
        $filter['is_suc']          = empty($_REQUEST['is_suc']) ? '' : trim($_REQUEST['is_suc']);
		$filter['start_time']          = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);
		$filter['end_time']          = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);
		$filter['share_type']          = empty($_REQUEST['share_type']) ? '' : trim($_REQUEST['share_type']);
		
        $where =  ' 1 ';
		
		
		
		if($filter['uname'])
		{
			$where .= " and u.uname='$filter[uname]'";
		}
		if($filter['is_suc']!='')
		{
			$where .= " and share_status='$filter[is_suc]'";
		}
		if($filter['share_type']!='')
		{
			$where .= " and share_type='$filter[share_type]'";
		}
		
		
		if($filter['start_time']!=''&&$filter['end_time'] !='')
		{
			$start_time = local_strtotime($filter['start_time']);
			$end_time   = local_strtotime($filter['end_time']);
			$where .= " and add_time>'$start_time' and add_time<'$end_time'";
		}
		

        /* 记录总数 */

        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('share_info'). " AS s left join  ".$GLOBALS['hhs']->table('users')."  as u on s.user_id=u.user_id WHERE  $where";
		
		

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);


        /* 分页大小 */

        $filter = page_and_size($filter);



        $sql = "SELECT s.*, u.user_name,u.uname,u.openid,u.headimgurl " .

            " FROM " . $GLOBALS['hhs']->table('share_info') . " AS s left join ".

            $GLOBALS['hhs']->table('users')." as u on s.user_id=u.user_id ".

            " WHERE  $where" .

            " ORDER BY $filter[sort_by] $filter[sort_order] ".

            " LIMIT " . $filter['start'] . ",$filter[page_size]";



    }

   

    $row = $GLOBALS['db']->getAll($sql);

    foreach($row as $k=>$v){

        $row[$k]['add_time']=local_date("Y-m-d H:i:s",$row[$k]['add_time'] );

    }

    return array('row' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

}



?>



