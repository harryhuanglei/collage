<?php

/**
 * 小舍电商 配送区域管理程序
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: shipping_area.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($hhs->table('shipping_point'), $db, 'id', 'shop_name');
/*------------------------------------------------------ */
//-- 配送区域列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    //$shipping_id = intval($_REQUEST['shipping']);
    
    $list = get_shipping_point_list();
    $smarty->assign('point_list',    $list['list']);

    $smarty->assign('ur_here',  '上门取货地点列表');
    $smarty->assign('action_link', array('href'=>'shipping_point.php?act=add' ,'text' => '添加取货地点'));
    //$smarty->assign('action_link', array('href'=>'shipping.php?act=list' ,'text' => '配送方式'));
    $smarty->assign('full_page', 1);

    assign_query_info();
    $smarty->display('shipping_point_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $ads_list = get_shipping_point_list();

   $smarty->assign('point_list',    $ads_list['list']);

    $sort_flag  = sort_flag($ads_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('shipping_point_list.htm'), '',
        array('filter' => $ads_list['filter'], 'page_count' => $ads_list['page_count']));
}
elseif($_REQUEST['act'] =='join_qr_code')
{
	$id = $_REQUEST['id'];
    $smarty->assign('ur_here',  '自提点管理员二维码绑定');
    $smarty->assign('action_link', array('href'=>'shipping_point.php?act=list' ,'text' => '返回自提点'));
	$smarty->assign("id",$id);
    $sql = "SELECT *  FROM " . $GLOBALS['hhs']->table('shipping_point') ." where id=".$_REQUEST['id'];
    $point = $db->getRow($sql);
	$smarty->assign('point',$point);

    $index_url=$GLOBALS['hhs']->url();
    $share_url=$index_url."join_qrcode.php?id=".$point['id'];
	$smarty->assign('share_url',$share_url);

    $sql = "select p.*,u.uname from " . $GLOBALS['hhs']->table('shipping_point_user') ." as p," . $GLOBALS['hhs']->table('users') ." as u WHERE u.`openid` = p.`openid` and p.`point_id` = " . $id;
    $rows = $db->getAll($sql);
    $smarty->assign('rows',$rows);

	$smarty->display('shipping_join_qr_code.htm');
}
elseif($_REQUEST['act'] =='drop_point_user')
{
    $id = intval($_GET['id']);
    $point_id = intval($_GET['point_id']);
    $db->query("DELETE FROM " . $GLOBALS['hhs']->table('shipping_point_user') ." WHERE `id` = '".$id."' and `point_id` = '".$point_id."'");
    hhs_header("location:shipping_point.php?act=join_qr_code&id=".$point_id);
    exit();
}
elseif($_REQUEST['act'] =='get_message')
{
  
  $wx_name = $db->getOne("select wx_name from ".$GLOBALS['hhs']->table('shipping_point')." where id=".$_REQUEST['id']);
  if($wx_name!='')
  {
	  $result =1;
  }
  else
  {
	  $result =2;
  }

  make_json_result($result);	
}
elseif($_REQUEST['act'] =='delete_join')
{
	$sql = $db->query("update ".$hhs->table('shipping_point')." set wx_name='',wx_openid='' where id=".$_REQUEST['id']);
    $lnk[] = array('text' => '返回', 'href'=>'shipping_point.php?act=join_qr_code&id='.$_REQUEST['id']);
    sys_msg('解绑成功', 0, $lnk);
	
}
/*------------------------------------------------------ */
//-- 新建配送区域
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add' )
{
    //admin_priv('shiparea_manage');
    
    $province_list = get_regions(1, 1);
    
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('form_act',    "insert");
    
    assign_query_info();
    $smarty->display('shipping_point_info.htm');
}

elseif ($_REQUEST['act'] == 'insert')
{
    //admin_priv('shiparea_manage');

 
    $sql = "INSERT INTO " .$hhs->table('shipping_point').
            " (shop_name, province,city,district, address,mobile,tel,has_printer,
printer_type,
device_no,
device_code,
device_key) ".
            "VALUES".
            " ('$_POST[shop_name]', '$_POST[province]','$_POST[city]','$_POST[district]','$_POST[address]','$_POST[mobile]','$_POST[mobile]', '$_POST[has_printer]','$_POST[printer_type]','$_POST[device_no]','$_POST[device_code]','$_POST[device_key]')";

    $db->query($sql);

    $new_id = $db->insert_Id();

    admin_log($_POST['shop_name'], 'add', 'shipping_point');

    $lnk[] = array('text' => '返回列表', 'href'=>'shipping_point.php?act=list');
    $lnk[] = array('text' => '继续添加', 'href'=>'shipping_point.php?act=add');
    sys_msg('添加成功', 0, $lnk);
    
}

/*------------------------------------------------------ */
//-- 编辑配送区域
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit')
{
    //admin_priv('shiparea_manage');
   $smarty->assign('ur_here', '编辑');
    
   $sql = "SELECT *  FROM " . $GLOBALS['hhs']->table('shipping_point') ." where id=".$_REQUEST['id'];
   $point = $db->getRow($sql);
   $province_list = get_regions(1, 1);
   $city_list = get_regions(2, $point['province']);
   $district_list = get_regions(3, $point['city']);
   
   $smarty->assign('point', $point);
   $smarty->assign('province_list',    $province_list);
   $smarty->assign('city_list',    $city_list);
   $smarty->assign('district_list',    $district_list);
   $smarty->assign('form_act',    "update");
   
    assign_query_info();
    
    $smarty->assign('id',               $_REQUEST['id']);
    $smarty->display('shipping_point_info.htm');
}

elseif ($_REQUEST['act'] == 'update')
{
 
    $sql = "UPDATE " .$hhs->table('shipping_point').
            " SET shop_name='$_POST[shop_name]', ".
                "province='$_POST[province]',city='$_POST[city]' ,mobile='$_POST[mobile]' ,tel='$_POST[mobile]' ,district='$_POST[district]',".
                "address='$_POST[address]', ".
                "has_printer='$_POST[has_printer]', ".
                "printer_type='$_POST[printer_type]', ".
                "device_no='$_POST[device_no]', ".
                "device_code='$_POST[device_code]', ".
                "device_key='$_POST[device_key]' ".
            "WHERE id='$_POST[id]'";

    $db->query($sql);

    admin_log($_POST['shop_name'], 'edit', 'shipping_point');

    $lnk[] = array('text' => '返回列表', 'href'=>'shipping_point.php?act=list' );
    
    sys_msg('修改成功', 0, $lnk);

}

/*------------------------------------------------------ */
//-- 批量删除配送区域
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'multi_remove')
{
    admin_priv('shiparea_manage');

    if (isset($_POST['areas']) && count($_POST['areas']) > 0)
    {
        $i = 0;
        foreach ($_POST['areas'] AS $v)
        {
            $db->query("DELETE FROM " .$hhs->table('shipping_area'). " WHERE shipping_area_id='$v'");
            $i++;
        }

        /* 记录管理员操作 */
        admin_log('', 'batch_remove', 'shipping_area');
    }
    /* 返回 */
    $links[0] = array('href'=>'shipping_area.php?act=list&shipping=' . intval($_REQUEST['shipping']), 'text' => $_LANG['go_back']);
    sys_msg($_LANG['remove_success'], 0, $links);
}

/*------------------------------------------------------ */
//-- 编辑配送区域名称
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_area')
{
    /* 检查权限 */
    check_authz_json('shiparea_manage');

    /* 取得参数 */
    $id  = intval($_POST['id']);
    $val = json_str_iconv(trim($_POST['val']));

    /* 取得该区域所属的配送id */
    $shipping_id = $exc->get_name($id, 'shipping_id');

    /* 检查是否有重复的配送区域名称 */
    if (!$exc->is_only('shipping_area_name', $val, $id, "shipping_id = '$shipping_id'"))
    {
        make_json_error($_LANG['repeat_area_name']);
    }

    /* 更新名称 */
    $exc->edit("shipping_area_name = '$val'", $id);

    /* 记录日志 */
    admin_log($val, 'edit', 'shipping_area');

    /* 返回 */
    make_json_result(stripcslashes($val));
}

/*------------------------------------------------------ */
//-- 删除配送区域
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')
{
    //check_authz_json('shiparea_manage');

    $id = intval($_GET['id']);
    $name = $exc->get_name($id);
    $exc->drop($id);
  
    admin_log($name, 'remove', 'shipping_point');

    $list = get_shipping_point_list();
    $smarty->assign('point_list', $list);
    make_json_result($smarty->fetch('shipping_point_list.htm'));
}

/**
 * 取得站点列表
 * @param   int     $shipping_id    配送id
 */
function get_shipping_point_list()
{
	$filter = array();
    $filter['type']    = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
	
	
	$where =' where a.id>0';
	if($filter['type']==1)
	{
		$where .= " and a.suppliers_id>0";
	}
	elseif($filter['type']==2)
	{
		$where .= " and a.suppliers_id=0";
	}
	if($filter['keywords'])
	{
		$where .= " and shop_name like '%%$filter[keywords]%%'";
	}

    $sql = "SELECT a.*,rp.region_name as province,rc.region_name as city,rd.region_name as district " .
                " FROM " . $GLOBALS['hhs']->table('shipping_point'). " AS a left join " .
                    $GLOBALS['hhs']->table('region') . " AS rp on a.province=rp.region_id left join ".
                    $GLOBALS['hhs']->table('region') . " as rc on a.city=rc.region_id left join ".
                    $GLOBALS['hhs']->table('region') ." as rd on a.district=rd.region_id  $where";
	
    $list=$GLOBALS['db']->getAll($sql);
     return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
