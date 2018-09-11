<?php
/**
 * 小舍 公告管理文件
 * 
 * 
 */
define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if($_REQUEST['act'] == 'list'){
	
	$list= get_announcement_list();

	$smarty->assign('announcement_list',$list['announcement_list']);
	$smarty->assign('filter',       $list['filter']);
	$smarty->assign('record_count', $list['record_count']);
	$smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('full_page',1);
	$smarty->display('announcement_list.htm');
}

 if($_REQUEST['act'] == 'add'){
 	$smarty->display('announcement_add.htm');
}

if($_REQUEST['act'] == 'edit'){
	$id = $_GET['id'];
	$announcement_content = get_announcement_content($id);
	$smarty->assign('id',$id);
	$smarty->assign('announcement_content',$announcement_content);
	$smarty->display('announcement_edit.htm');
}

if($_REQUEST['act'] == 'update'){

	$titile = $_POST['title'];
	$content = $_POST['content'];
	$is_display = $_POST['is_display'];
	$add_time = $_POST['add_time'] ? local_strtotime($_POST['add_time']) : gmtime() ;
	
	$sql = "UPDATE ".$GLOBALS['hhs']->table('announcement')." SET title = "."'".$title."'".",  content = "."'".$content."'"." ,add_time = ".$add_time." ,is_display = ".$is_display." where id = ".$id;
	$row = $db->query($sql);

	$link = array(
			array(
					'href'=>"announcement.php?act=list",
					'text'=>'公告列表',
			),
	);

	sys_msg("更新公告成功！", 1, $link);
}


if($_REQUEST['act'] == 'insert'){

	$titile = $_POST['title'];
	$content = $_POST['content'];
	$is_display = $_POST['is_display'];
	$add_time = $_POST['add_time'] ? local_strtotime($_POST['add_time']) : gmtime() ;
	
	$sql = "INSERT INTO ".$GLOBALS['hhs']->table('announcement')." (title,content,add_time,is_display) VALUES ("."'".$titile."'".","."'".$content."'".","."'".$add_time."'".","."'".$is_display."'".")";
	
	$row = $db->query($sql);
	
	$link = array(
					array(
						'href'=>"announcement.php?act=list",
						'text'=>'公告列表',	
		),
	);
	
	sys_msg("添加公告成功！", 1, $link);
}
/*------------------------------------------------------ */

//-- 翻页、排序

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'query')

{

	$list = get_announcement_list();

	$smarty->assign('announcement_list',    $list['announcement_list']);

	$smarty->assign('filter',       $list['filter']);

	$smarty->assign('record_count', $list['record_count']);

	$smarty->assign('page_count',   $list['page_count']);

	$sort_flag  = sort_flag($list['filter']);

	make_json_result($smarty->fetch('announcement_list.htm'), '',

	array('filter' => $list['filter'], 'page_count' => $list['page_count']));

}

function get_announcement_content($id){
	
	$sql = "select * from ".$GLOBALS['hhs']->table('announcement')." where id = ".$id;
	$row = $GLOBALS['db']->getRow($sql);
	$row['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
	return  $row;  
}



function get_announcement_list(){
	
	/* 获得记录总数以及总页数 */
	$filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	
	$sql = "SELECT count(*) FROM ".$GLOBALS['hhs']->table('announcement');
	
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
	
	$filter = page_and_size($filter);	
	
	$sql = "SELECT  * FROM ".$GLOBALS['hhs']->table('announcement'). " ORDER BY $filter[sort_by] $filter[sort_order] ".

        " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
	
	$row = $GLOBALS['db']->getAll($sql);
	
	foreach ($row AS $key => $value){
		$row[$key]['add_time'] = local_date('Y-m-d H:i:s', $value['add_time']);
	}
	$arr = array('announcement_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}