<?php

/**
 * 小舍电商 列出所有分类及品牌
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: catalog.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');


$pcat_array = get_categories_tree();
foreach ($pcat_array as $key => $pcat_data)
{
    $pcat_array[$key]['name'] = $pcat_data['name'];

    if ($pcat_data['cat_id'])
	{

        foreach ($pcat_data['cat_id'] as $k => $v)
        {
			$pcat_array[$key]['cat_id'][$k]['name'] = $v['name'];
        }
    }
}

$arr=array();
foreach($pcat_array as $f){

    //默认右侧二级分类
    $category = get_cat_tree($f['id']);
	
	
    foreach($category as $key => $value)
    {
        foreach($value['cat_id'] as $val)
        {
            foreach($val['cat_id'] as $v)
            {
                if(empty($v['cat_id']))
                {
                    $category[$key]['is_level'] = 1;
                    break;
                }
            }
        }
    }
    $arr[$f['id']]=$category;
}
//print_r($arr);
$smarty->assign('arr',$arr);

assign_template();
$smarty->assign('pcat_array',$pcat_array);




    $link= $hhs->url().substr($_SERVER[SCRIPT_NAME], 1).'?uid='.$uid;
    $smarty->assign('link', $link );
    $smarty->assign('link2', urlencode($link) );
	
	
	$smarty->assign('appid', $appid);
	$timestamp=time();
	$smarty->assign('timestamp', $timestamp );
	$class_weixin=new class_weixin($appid,$appsecret);
	$signature=$class_weixin->getSignature($timestamp);
	$smarty->assign('signature', $signature);
	$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');
	$smarty->assign('title', $_CFG['mall_title']);
	$smarty->assign('desc', mb_substr($_CFG['mall_desc'], 0,30,'utf-8')  );








$smarty->display("catall.dwt");


/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer     $cat_id     分类编号
 * @return  array
 */
function get_cat_tree($cat_id)
{
    /*
     判断当前分类中全是是否是底级分类，
     如果是取出底级分类上级分类，
     如果不是取当前分类及其下的子分类
    */
	
	if($cat_id > 0)
	{
		$where = 'and cat_id = '.$cat_id;	
	}
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['hhs']->table('category') . " WHERE  is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql))
    {
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT cat_id,cat_name ,parent_id,is_show,cat_img ' .
                'FROM ' . $GLOBALS['hhs']->table('category') .
                "WHERE  is_show = 1 ".$where." ORDER BY sort_order ASC, cat_id ASC";
		
		$res = $GLOBALS['db']->getRow($sql);
		
        if ($res['is_show'])
        {
           $cat_arr[0]['id']   = $res['cat_id'];
           $cat_arr[0]['name'] = $res['cat_name'];
		   $cat_arr[0]['cat_img'] = $res['cat_img'];
		   $cat_arr[0]['cats'] = get_children($cat_id);
		   
		   $cat_arr[0]['brands'] = get_goods_cate_brands($cat_arr[0]['cats']);
		   
		   
		   if (isset($res['cat_id']) != NULL)
		   {
			   $cat_arr[0]['cat_id'] = get_child_tree_xaphp($res['cat_id']);
		   }
        }
        
    }
	

    if(isset($cat_arr))
    {
        return $cat_arr;
    }
}


function get_child_tree_xaphp($tree_id = 0)
{
	$three_arr = array();
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['hhs']->table('category') . " WHERE parent_id = '$tree_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $tree_id == 0)
    {
        $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show,cat_img ' .
                'FROM ' . $GLOBALS['hhs']->table('category') .
                "WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->getAll($child_sql);
		
        foreach ($res AS $key => $row)
        {
            if ($row['is_show'])
			{

               $three_arr[$key]['id']   = $row['cat_id'];
               $three_arr[$key]['name'] = $row['cat_name'];
               $three_arr[$key]['cat_img'] = $row['cat_img'];
               if (isset($row['cat_id']) != NULL)
               {
                       $three_arr[$key]['cat_id'] = get_child_tree_xaphp($row['cat_id']);
			   }
            }
        }
    }
    return $three_arr;
}

	function get_goods_cate_brands($cats)
	{
		if($cats)
		{
			$where = " and $cats ";	
		}
		$sql = "select g.brand_id,b.brand_name,b.brand_logo from ".$GLOBALS['hhs']->table('goods')." as g ".
		"left join ".$GLOBALS['hhs']->table('brand')." as b on g.brand_id = b.brand_id 
		where g.brand_id <> 0 $where GROUP BY b.brand_id ";
		$res = $GLOBALS['db']->getAll($sql);
		foreach($res as $key => $val)
		{
			$res[$key]['url'] = 'brand.php?id='.$val['brand_id'];
		}
		return $res;
	}

?>
