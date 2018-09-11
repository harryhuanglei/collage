<?php

define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');

if (empty($_REQUEST['act']))
{
	$_REQUEST['act'] = 'list';
}
else
{
	$_REQUEST['act'] = trim($_REQUEST['act']);
}

if($_REQUEST['act'] == 'list'){
		$list = get_square_list();
	//	d($list);
		$smarty->assign('square',$list['square_list']);
		$smarty->assign('filter',       $list['filter']);
		$smarty->assign('record_count', $list['record_count']);
		$smarty->assign('page_count',   $list['page_count']);
		$smarty->assign('full_page',1);
		$smarty->display('square_manage_list.htm');
}
if($_REQUEST['act'] == 'change'){
	
	$order_id = $_POST['order_id'];
	$goods_id = $_POST['goods_id'];
	$is_boutique = $_POST['is_boutique'];
	//var_dump($is_boutique);
	if($is_boutique == 0){
		$sql = "update ".$GLOBALS['hhs']->table('square_mes')." set is_boutique = 1 where order_id = ".$order_id." and goods_id = ".$goods_id;
		$db->query($sql);
		$res = array(
				'is_boutique'=>'1',
				'isError'=> 0
		);	
		echo json_encode($res);
        exit();
	}else{
		$sql = "update ".$GLOBALS['hhs']->table('square_mes')." set is_boutique = 0 where order_id = ".$order_id." and goods_id = ".$goods_id;
		$db->query($sql);
		$res = array(
				'is_boutique'=>'0',
				'isError'=> 0
		);
		echo json_encode($res);
		exit();
		
	}
}


function get_square_list(){
	
	/* 获得记录总数以及总页数 */
	$filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	
	$count_sql = "select count(*) from "
			.$GLOBALS['hhs']->table('order_info')." as o, "
			.$GLOBALS['hhs']->table('users')." as u , "
			.$GLOBALS['hhs']->table('square_mes')." as sm ,"
			.$GLOBALS['hhs']->table('order_goods').
			" as og  where o.order_id = og.order_id and o.show_square = 1 and o.user_id = u.user_id AND o.square <> '' and sm.order_id = o.order_id and sm.goods_id = og.goods_id ";
	$filter['record_count'] = $GLOBALS['db']->getOne($count_sql);
	$filter = page_and_size($filter);
		
	$sql = "select DISTINCT o.luckdraw_id,o.square, sm.is_boutique ,sm.zan_num, sm.comment_num,sm.square_add_time, o.order_id, og.goods_id, og.goods_name,o.team_sign,o.team_num,o.teammen_num,(team_num - teammen_num) as need, o.add_time,u.uname,u.headimgurl from "
    		.$GLOBALS['hhs']->table('order_info')." as o, "
    		.$GLOBALS['hhs']->table('users')." as u , "
    		.$GLOBALS['hhs']->table('square_mes')." as sm ,"
    		.$GLOBALS['hhs']->table('order_goods').
    		" as og  where o.order_id = og.order_id and o.show_square = 1 and o.user_id = u.user_id AND o.square <> '' 
    		and sm.order_id = o.order_id and sm.goods_id = og.goods_id 
    		ORDER BY $filter[sort_by] $filter[sort_order] ".
	    	" LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
	$row = $GLOBALS['db']->getAll($sql);
	foreach ($row AS $key => $value){
		$row[$key]['square_add_time'] = local_date('Y-m-d H:i:s', $value['square_add_time']);
	}
	$arr = array('square_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
	
	
}

/*------------------------------------------------------ */

//-- 翻页、排序

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'query')

{

	$list = get_square_list();

	$smarty->assign('square',    $list['square_list']);

	$smarty->assign('filter',       $list['filter']);

	$smarty->assign('record_count', $list['record_count']);

	$smarty->assign('page_count',   $list['page_count']);

	$sort_flag  = sort_flag($list['filter']);

	make_json_result($smarty->fetch('square_manage_list.htm'), '',

	array('filter' => $list['filter'], 'page_count' => $list['page_count']));

}
function d($arr){
	echo '<pre>';
	var_dump($arr);die;
}