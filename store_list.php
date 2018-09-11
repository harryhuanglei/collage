<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init2.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
	$hangye_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
	$smarty->assign('hangye_id',      $hangye_id);
	$hangye = getHangye();
	/*
	foreach ($hangye as $key => $row) {
		
		$children = getHangye($row['id']);
		$num = 0;
		foreach ($children as $child) {
			$num += $child['num'];
			unset($child);
		}
		$hangye[$key]['children'] = $children;
		$hangye[$key]['num'] = $num;

	}*/
	$smarty->assign('hangye',      $hangye);

    $store_list = store_list($hangye_id);
	$smarty->assign('store_list',      $store_list);
	
	
	
    $smarty->assign('page_title',      '店铺列表');    // 页面标题
	$smarty->display('store_list.dwt');

function store_list($hangye_id=0)
{
	$where = " where is_check=1  ";
	if($hangye_id>0){
		$children = getHangye_children($hangye_id);
		$str="".$hangye_id;
		if(!empty($children)){
			$str.= ",".implode(',',$children);
			
		}
		$where .=" and hangye_id in (".$str .")" ;
	}
	
	
	$current_region_type=get_region_type($_SESSION['site_id']);
	
	if($_SESSION['site_id']!=1) 
	{
		if($current_region_type<=2){
			 $where.=" and (city_id='".$_SESSION['site_id'] . "' or city_id=1) ";
		}elseif($current_region_type==3){
			$where.=" and (district_id='".$_SESSION['site_id'] . "' or city_id=1) ";
		} 
	}
	
	
    $sql = "select suppliers_id,suppliers_name,supp_logo,suppliers_desc,hangye_id,province_id,city_id from ".$GLOBALS['hhs']->table('suppliers').$where." order by sort_order ";
	$res = $GLOBALS['db']->getAll($sql);
	

	foreach ($res AS $k=>$row)
	{
		$res[$k]['province_id'] = get_region_name($row['province_id']);
		$res[$k]['city_id'] = get_region_name($row['city_id']);
		
		
    $sql = "SELECT count(*) FROM ".$GLOBALS['hhs']->table('goods')." WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $row['suppliers_id'];
    $res[$k]['goods_num'] = $GLOBALS['db']->getOne($sql);
	
	
    $sql = "SELECT sum(`sales_num`) FROM ".$GLOBALS['hhs']->table('goods')." WHERE `suppliers_id` = " .$row['suppliers_id'];
    $res[$k]['sales_num'] = $GLOBALS['db']->getOne($sql);
	
    $sql = "SELECT count(*) FROM  ".$GLOBALS['hhs']->table('order_goods')." as o,".$GLOBALS['hhs']->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$row['suppliers_id'];
    $res[$k]['sales_num'] += $GLOBALS['db']->getOne($sql);
	
	}
    return $res;
}
function getHangye($pid=0)
{
	return $GLOBALS['db']->getAll("select a.*,(SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('suppliers')." AS b WHERE b.hangye_id=a.id) AS num from ".$GLOBALS['hhs']->table('hangye')." as a where a.pid= ".$pid);
}
function getHangye_children($pid=0)
{
	return $GLOBALS['db']->getCol("select id from ".$GLOBALS['hhs']->table('hangye')." as a where a.pid= ".$pid);
}

?>
