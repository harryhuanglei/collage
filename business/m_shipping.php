<?php
define('IN_HHS', true);


if($action =='insert_point')
{
    $sql = "INSERT INTO " .$hhs->table('shipping_point').
            " (shop_name, address,longitude,latitude,tel,suppliers_id,province,city,district) ".
            "VALUES".
            " ('$_POST[shop_name]','$_POST[address]','$_POST[longitude]','$_POST[latitude]','$_POST[tel]' ,'$suppliers_id','$_POST[province]','$_POST[city]','$_POST[district]')";
	$db->query($sql);
	show_message('添加自提点成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=point_list&shipping='.$_POST['shipping'] ,'info');
}
elseif($action =='update_point')
{
    $sql = "UPDATE " .$hhs->table('shipping_point').
            " SET shop_name='$_POST[shop_name]', ".
                "longitude='$_POST[longitude]' ,latitude='$_POST[latitude]' ,tel='$_POST[tel]',district='$_POST[district]',".
                "address='$_POST[address]', ".
                "province='$_POST[province]', ".
                "city='$_POST[city]', ".
                "district='$_POST[district]' ".
            "WHERE id='$_POST[id]'";

    $db->query($sql);
	show_message('编辑成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=point_list&shipping='.$_POST['shipping'] ,'info');
}
elseif($action =='delete_point')
{
	$sql = $db->query("delete from ".$hhs->table('shipping_point')." where id=".$_REQUEST['id']);
	show_message('删除成功', $_LANG['back_up_page'],'index.php?op=shipping&act=point_list&shipping='.$_REQUEST['shipping'] ,'info');
	
}
elseif($action =='add_point'||$action =='edit_point')
{
    $id = @$_REQUEST['id'];
    $province_list = get_regions(1, 1);
    $smarty->assign('province_list',    $province_list);
    if($id)
    {
        $sql = "SELECT *  FROM " . $GLOBALS['hhs']->table('shipping_point') ." where id=".$_REQUEST['id'];
        $point = $db->getRow($sql);
        $smarty->assign('point',$point);

        $city_list = get_regions(2, $point['province']);
        $district_list = get_regions(3, $point['city']);
        $smarty->assign('city_list',    $city_list);
        $smarty->assign('district_list',    $district_list);

        $sql = "select p.*,u.uname from " . $GLOBALS['hhs']->table('shipping_point_user') ." as p," . $GLOBALS['hhs']->table('users') ." as u WHERE u.`openid` = p.`openid` and p.`point_id` = " . $id;
        $rows = $db->getAll($sql);
        $smarty->assign('rows',$rows); 

    }
    $smarty->assign('shipping',$_REQUEST['shipping']);
    $smarty->assign('root',$hhs->get_domain());

    $smarty->display('m_shipping.dwt');
}
elseif($_REQUEST['act'] =='drop_point_user')
{
    $id = intval($_GET['id']);
    $point_id = intval($_GET['point_id']);
    $shipping = intval($_GET['shipping']);
    $db->query("DELETE FROM " . $GLOBALS['hhs']->table('shipping_point_user') ." WHERE `id` = '".$id."' and `point_id` = '".$point_id."'");
    hhs_header("location:index.php?op=shipping&act=edit_point&id=".$point_id."&shipping=".$shipping);
    exit();
}
//自提点
elseif($action =='point_list')
{
    $list = get_shipping_point_list($suppliers_id);
	$smarty->assign('shipping',$_REQUEST['shipping']);
    $smarty->assign('point_list',    $list);
    $smarty->display('m_shipping.dwt');
}
//配送方式
elseif($action  =='supp_shipping')
{
	$modules = read_modules('../includes/modules/shipping');

    for ($i = 0; $i < count($modules); $i++)
    {
        $lang_file = ROOT_PATH.'languages/' .$_CFG['lang']. '/shipping/' .$modules[$i]['code']. '.php';

        if (file_exists($lang_file))
        {
            include_once($lang_file);
        }

        /* 检查该插件是否已经安装 */
        $sql = "SELECT shipping_id, shipping_name, shipping_desc,shipping_code, insure, support_cod,shipping_order FROM " .$hhs->table('shipping'). " WHERE shipping_code='" .$modules[$i]['code']. "' ORDER BY shipping_order";
        $row = $db->GetRow($sql);

        if ($row)
        {
            /* 插件已经安装了，获得名称以及描述 */
            $modules[$i]['id']      = $row['shipping_id'];
            $modules[$i]['name']    = $row['shipping_name'];
            $modules[$i]['desc']    = $row['shipping_desc'];
            $modules[$i]['insure_fee']  = $row['insure'];
            $modules[$i]['cod']     = $row['support_cod'];
			$modules[$i]['shipping_code']     = $row['shipping_code'];
			
            $modules[$i]['shipping_order'] = $row['shipping_order'];
            $modules[$i]['install'] = 1;

            if (isset($modules[$i]['insure']) && ($modules[$i]['insure'] === false))
            {
                $modules[$i]['is_insure']  = 0;
            }
            else
            {
                $modules[$i]['is_insure']  = 1;
            }
        }
        else
        {
            $modules[$i]['name']    = $_LANG[$modules[$i]['code']];
            $modules[$i]['desc']    = $_LANG[$modules[$i]['desc']];
            $modules[$i]['insure_fee']  = empty($modules[$i]['insure'])? 0 : $modules[$i]['insure'];
            $modules[$i]['cod']     = $modules[$i]['cod'];
            $modules[$i]['install'] = 0;
        }
    }

    $smarty->assign('modules', $modules);
	$smarty->display('m_shipping.dwt');
}

//配送区域
elseif($action =='shipping_area_list')
{
	$shipping_id = intval($_REQUEST['shipping']);
	if($suppliers_id > 0)
	{
		$list = get_shipping_area_list($shipping_id,$suppliers_id);	
	}    
    $smarty->assign('areas',$list);
	$smarty->assign('shipping_id',$shipping_id);
	$smarty->display('m_shipping.dwt');
}

//添加配送区域
elseif ($action =='shipping_area_add')
{
    $shipping = $db->getRow("SELECT shipping_name, shipping_code FROM " .$hhs->table('shipping'). " WHERE shipping_id='$_REQUEST[id]'");

    $set_modules = 1;
    include_once(ROOT_PATH.'includes/modules/shipping/'.$shipping['shipping_code'].'.php');

    $fields = array();
    foreach ($modules[0]['configure'] AS $key => $val)
    {
        $fields[$key]['name']   = $val['name'];
        $fields[$key]['value']  = $val['value'];
        $fields[$key]['label']  = $_LANG[$val['name']];
    }
    $count = count($fields);
    $fields[$count]['name']     = "free_money";
    $fields[$count]['value']    = "0";
    $fields[$count]['label']    = $_LANG["free_money"];

    /* 如果支持货到付款，则允许设置货到付款支付费用 */
    if ($modules[0]['cod'])
    {
        $count++;
        $fields[$count]['name']     = "pay_fee";
        $fields[$count]['value']    = "0";
        $fields[$count]['label']    = $_LANG['pay_fee'];
    }

    $shipping_area['shipping_id']   = 0;
    $shipping_area['free_money']    = 0;

    $smarty->assign('shipping_area',    array('shipping_id' => $_REQUEST['id'], 'shipping_code' => $shipping['shipping_code']));
    $smarty->assign('fields',           $fields);
    $smarty->assign('form_action',      'shipping_area_insert');
    $smarty->assign('countries',        get_regions());
    $smarty->assign('default_country',  $_CFG['shop_country']);
    $smarty->display('m_shipping.dwt');
}

//添加配送区域
elseif($action =='shipping_area_insert')
{
	/* 检查同类型的配送方式下有没有重名的配送区域 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table("shipping_area").
            " WHERE shipping_id='$_POST[shipping]' AND shipping_area_name='$_POST[shipping_area_name]' and supp_id = ".$suppliers_id." ";
    if ($db->getOne($sql) > 0)
    {
		
        show_message('已经存在一个同名的配送区域。',$_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
    }
    else
    {
        $shipping_code = $db->getOne("SELECT shipping_code FROM " .$hhs->table('shipping')." WHERE shipping_id='$_POST[shipping]'");
        $plugin  = ROOT_PATH.'/includes/modules/shipping/'. $shipping_code. ".php";

        if (!file_exists($plugin))
        {
			show_message('没有找到指定的配送方式的插件。',$_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
        }
        else
        {
            $set_modules = 1;
            include_once($plugin);
        }

        $config = array();
        foreach ($modules[0]['configure'] AS $key => $val)
        {
            $config[$key]['name']   = $val['name'];
            $config[$key]['value']  = $_POST[$val['name']];
        }

        $count = count($config);
        $config[$count]['name']     = 'free_money';
        $config[$count]['value']    = empty($_POST['free_money']) ? '' : $_POST['free_money'];
        $count++;
        $config[$count]['name']     = 'fee_compute_mode';
        $config[$count]['value']    = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];
        /* 如果支持货到付款，则允许设置货到付款支付费用 */
        if ($modules[0]['cod'])
        {
            $count++;
            $config[$count]['name']     = 'pay_fee';
            $config[$count]['value']    =  make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
        }

        $sql = "INSERT INTO " .$hhs->table('shipping_area').
                " (shipping_area_name, shipping_id, configure,supp_id) ".
                "VALUES".
                " ('$_POST[shipping_area_name]', '$_POST[shipping]', '" .serialize($config). "', '" .$suppliers_id. "')";

        $db->query($sql);
        $new_id = $db->insert_Id();

        /* 添加选定的城市和地区 */
        if (isset($_POST['regions']) && is_array($_POST['regions']))
        {
            foreach ($_POST['regions'] AS $key => $val)
            {
                $sql = "INSERT INTO ".$hhs->table('area_region')." (shipping_area_id, region_id) VALUES ('$new_id', '$val')";
				$db->query($sql);
            }
        }
		show_message('添加配送区域成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'info');
	}
}
//编辑配送区域
elseif($action =='shipping_area_edit')
{
	$sql = "SELECT a.shipping_name, a.shipping_code, a.support_cod, b.* ".
            "FROM " .$hhs->table('shipping'). " AS a, " .$hhs->table('shipping_area'). " AS b ".
            "WHERE b.shipping_id=a.shipping_id AND b.shipping_area_id='$_REQUEST[id]' and b.supp_id = ".$suppliers_id." ";
    $row = $db->getRow($sql);

    $set_modules = 1;
    include_once(ROOT_PATH.'includes/modules/shipping/'.$row['shipping_code'].'.php');

    $fields = unserialize($row['configure']);
    /* 如果配送方式支持货到付款并且没有设置货到付款支付费用，则加入货到付款费用 */
    if ($row['support_cod'] && $fields[count($fields)-1]['name'] != 'pay_fee')
    {
        $fields[] = array('name'=>'pay_fee', 'value'=>0);
    }

    foreach ($fields AS $key => $val)
    {
       /* 替换更改的语言项 */
       if ($val['name'] == 'basic_fee')
       {
            $val['name'] = 'base_fee';
       }
//       if ($val['name'] == 'step_fee1')
//       {
//            $val['name'] = 'step_fee';
//       }
//       if ($val['name'] == 'step_fee2')
//       {
//            $val['name'] = 'step_fee1';
//       }

       if ($val['name'] == 'item_fee')
       {
           $item_fee = 1;
       }
       if ($val['name'] == 'fee_compute_mode')
       {
           $smarty->assign('fee_compute_mode',$val['value']);
           unset($fields[$key]);
       }
       else
       {
           $fields[$key]['name'] = $val['name'];
           $fields[$key]['label']  = $_LANG[$val['name']];
       }
    }

    if(empty($item_fee))
    {
        $field = array('name'=>'item_fee', 'value'=>'0', 'label'=>empty($_LANG['item_fee']) ? '' : $_LANG['item_fee']);
        array_unshift($fields,$field);
    }

    /* 获得该区域下的所有地区 */
    $regions = array();

    $sql = "SELECT a.region_id, r.region_name ".
            "FROM ".$hhs->table('area_region')." AS a, ".$hhs->table('region'). " AS r ".
            "WHERE r.region_id=a.region_id AND a.shipping_area_id='$_REQUEST[id]'";
    $res = $db->query($sql);
    while ($arr = $db->fetchRow($res))
    {
        $regions[$arr['region_id']] = $arr['region_name'];
    }

    assign_query_info();
    $smarty->assign('ur_here',          $row['shipping_name'] .' - '. $_LANG['edit_area']);
    $smarty->assign('id',               $_REQUEST['id']);
    $smarty->assign('fields',           $fields);
    $smarty->assign('shipping_area',    $row);
    $smarty->assign('regions',          $regions);
	$smarty->assign('form_action',      'shipping_area_update');
    $smarty->assign('countries',        get_regions());
    $smarty->assign('default_country',  1);
	
	$smarty->display('m_shipping.dwt');
}
elseif($action =='shipping_area_update')
{
	
	/* 检查同类型的配送方式下有没有重名的配送区域 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table("shipping_area").
            " WHERE shipping_id='$_POST[shipping]' AND ".
                    "shipping_area_name='$_POST[shipping_area_name]' AND ".
                    " supp_id = ".$suppliers_id." AND  shipping_area_id<>'$_POST[id]'";
    if ($db->getOne($sql) > 0)
    {
        show_message('已经存在一个同名的配送区域。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');

    }
    else
    {
        $shipping_code = $db->getOne("SELECT shipping_code FROM " .$hhs->table('shipping'). " WHERE shipping_id='$_POST[shipping]'");
        $plugin        = '../includes/modules/shipping/'. $shipping_code. ".php";

        if (!file_exists($plugin))
        {
            show_message('没有找到指定的配送方式的插件。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
        }
        else
        {
            $set_modules = 1;
            include_once($plugin);
        }

        $config = array();
        foreach ($modules[0]['configure'] AS $key => $val)
        {
            $config[$key]['name']   = $val['name'];
            $config[$key]['value']  = $_POST[$val['name']];
        }

        $count = count($config);
        $config[$count]['name']     = 'free_money';
        $config[$count]['value']    = empty($_POST['free_money']) ? '' : $_POST['free_money'];
        $count++;
        $config[$count]['name']     = 'fee_compute_mode';
        $config[$count]['value']    = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];
        if ($modules[0]['cod'])
        {
            $count++;
            $config[$count]['name']     = 'pay_fee';
            $config[$count]['value']    =  make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
        }

        $sql = "UPDATE " .$hhs->table('shipping_area').
                " SET shipping_area_name='$_POST[shipping_area_name]', supp_id = ".$suppliers_id.", ".
                    "configure='" .serialize($config). "' ".
                "WHERE shipping_area_id='$_POST[id]'";
				
        $db->query($sql);
        /* 过滤掉重复的region */
        $selected_regions = array();
        if (isset($_POST['regions']))
        {
            foreach ($_POST['regions'] AS $region_id)
            {
                $selected_regions[$region_id] = $region_id;
            }
        }

        // 查询所有区域 region_id => parent_id
        $sql = "SELECT region_id, parent_id FROM " . $hhs->table('region');
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $region_list[$row['region_id']] = $row['parent_id'];
        }

        // 过滤掉上级存在的区域
        foreach ($selected_regions AS $region_id)
        {
            $id = $region_id;
            while ($region_list[$id] != 0)
            {
                $id = $region_list[$id];
                if (isset($selected_regions[$id]))
                {
                    unset($selected_regions[$region_id]);
                    break;
                }
            }
        }

        /* 清除原有的城市和地区 */
        $db->query("DELETE FROM ".$hhs->table("area_region")." WHERE shipping_area_id='$_POST[id]'");

        /* 添加选定的城市和地区 */
        foreach ($selected_regions AS $key => $val)
        {
            $sql = "INSERT INTO ".$hhs->table('area_region')." (shipping_area_id, region_id) VALUES ('$_POST[id]', '$val')";
            $db->query($sql);
        }
		 
		 show_message('编辑配送区域成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
		 
    }
	
}
	
	
//删除配送区域
elseif($action =='shipping_area_remove')
{
	$shipping = intval($_GET['shipping_id']);
	$id = intval($_GET['id']);
    $db->query("DELETE FROM " .$hhs->table('shipping_area'). " WHERE shipping_area_id='$id'");
    $db->query('DELETE FROM '.$hhs->table('area_region').' WHERE shipping_area_id='.$id);
	
    /* 返回 */
    show_message('删除配送区域成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$shipping ,'info');
	
}

//批量删除配送区域
elseif($action =='shipping_area_multi_remove')
{
	$shipping = intval($_REQUEST['shipping']);

	if (isset($_POST['areas']) && count($_POST['areas']) > 0)
    {
        $i = 0;
        foreach ($_POST['areas'] AS $v)
        {
            $db->query("DELETE FROM " .$hhs->table('shipping_area'). " WHERE shipping_area_id='$v'");
			$db->query('DELETE FROM '.$hhs->table('area_region').' WHERE shipping_area_id='.$v);
            $i++;
        }
	}
	else
	{
		show_message('请选择要删除的配送区域。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$shipping ,'info');
	}
	
    /* 返回 */
    show_message('删除配送区域成功。', $_LANG['back_up_page'],'index.php?op=shipping&act=shipping_area_list&shipping='.$shipping ,'info');
	
}

//编辑打印模板
elseif($action =='edit_print_template')
{
	$shipping_id = !empty($_GET['shipping']) ? intval($_GET['shipping']) : 0;

    /* 检查该插件是否已经安装 */
    $sql = "SELECT * FROM " .$hhs->table('shipping'). " WHERE shipping_id=$shipping_id";
    $row = $db->GetRow($sql);
    if ($row)
    {
        include_once(ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php');
        $row['shipping_print'] = !empty($row['shipping_print']) ? $row['shipping_print'] : '';
        $row['print_model'] = empty($row['print_model']) ? 1 : $row['print_model']; //兼容以前版本
		$smarty->assign('shipping', $row);
    }
    else
    {
		show_message('您的配送方式尚未安装，暂不能编辑模板。', $_LANG['back_up_page'],'index.php?op=shipping&act=supp_shipping' ,'error');
    }

    $smarty->assign('shipping_id', $shipping_id);
	$smarty->display('m_shipping.dwt');
}

/*------------------------------------------------------ */
//-- 模板Flash编辑器
/*------------------------------------------------------ */
elseif ($action =='print_index')
{
	
    $shipping_id = !empty($_GET['shipping']) ? intval($_GET['shipping']) : 0;

    /* 检查该插件是否已经安装 取值 */
    $sql = "SELECT * FROM " .$hhs->table('shipping'). " WHERE shipping_id = '$shipping_id' LIMIT 0,1";
    $row = $db->GetRow($sql);
    if ($row)
    {
        include_once(ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php');
        $row['shipping_print'] = !empty($row['shipping_print']) ? $row['shipping_print'] : '';
        $row['print_bg'] = empty($row['print_bg']) ? '' : get_site_root_url() . $row['print_bg'];
    }
    $smarty->assign('shipping', $row);
    $smarty->assign('shipping_id', $shipping_id);
    $smarty->display('print_index.htm');
}

 
/*------------------------------------------------------ */
//-- 模板Flash编辑器
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'recovery_default_template')
{
    /* 检查登录权限 */
    admin_priv('ship_manage');

    $shipping_id = !empty($_POST['shipping']) ? intval($_POST['shipping']) : 0;

    /* 取配送代码 */
    $sql = "SELECT shipping_code FROM " .$hhs->table('shipping'). " WHERE shipping_id = '$shipping_id'";
    $code = $db->GetOne($sql);

    $set_modules = true;
    include_once(ROOT_PATH . 'includes/modules/shipping/' . $code . '.php');

    /* 恢复默认 */
    $db->query("UPDATE " .$hhs->table('shipping'). " SET print_bg = '" . addslashes($modules[0]['print_bg']) . "',  config_lable = '" . addslashes($modules[0]['config_lable']) . "' WHERE shipping_code = '$code' LIMIT 1");

    $url = "shipping.php?act=edit_print_template&shipping=$shipping_id";
    hhs_header("Location: $url\n");
}

/*------------------------------------------------------ */
//-- 模板Flash编辑器 上传图片
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'print_upload')
{
    //设置上传文件类型
    $allow_suffix = array('jpg', 'png', 'jpeg');

    $shipping_id = !empty($_POST['shipping']) ? intval($_POST['shipping']) : 0;

    //接收上传文件
    if (!empty($_FILES['bg']['name']))
    {
        if(!get_file_suffix($_FILES['bg']['name'], $allow_suffix))
        {
            echo '<script language="javascript">';
            echo 'parent.alert("' . sprintf($_LANG['js_languages']['upload_falid'], implode('，', $allow_suffix)) . '");';
            echo '</script>';
            exit;
        }

        $name = date('Ymd');
        for ($i = 0; $i < 6; $i++)
        {
            $name .= chr(mt_rand(97, 122));
        }
        $name .= '.' . end(explode('.', $_FILES['bg']['name']));
        $target = ROOT_PATH . '/images/receipt/' . $name;

        if (move_upload_file($_FILES['bg']['tmp_name'], $target))
        {
            $src = '/images/receipt/' . $name;
        }
    }

    //保存
    $sql = "UPDATE " .$hhs->table('shipping'). " SET print_bg = '$src' WHERE shipping_id = '$shipping_id'";
    $res = $db->query($sql);
    if ($res)
    {
        echo '<script language="javascript">';
        echo 'parent.call_flash("bg_add", "' . get_site_root_url() . $src . '");';
        echo '</script>';
    }
}

/*------------------------------------------------------ */
//-- 模板Flash编辑器 删除图片
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'print_del')
{
    /* 检查权限 */
    check_authz_json('ship_manage');

    $shipping_id = !empty($_GET['shipping']) ? intval($_GET['shipping']) : 0;
    $shipping_id = json_str_iconv($shipping_id);

    /* 检查该插件是否已经安装 取值 */
    $sql = "SELECT print_bg FROM " .$hhs->table('shipping'). " WHERE shipping_id = '$shipping_id' LIMIT 0,1";
    $row = $db->GetRow($sql);
    if ($row)
    {
        if (($row['print_bg'] != '') && (!is_print_bg_default($row['print_bg'])))
        {
            @unlink(ROOT_PATH . $row['print_bg']);
        }

        $sql = "UPDATE " .$hhs->table('shipping'). " SET print_bg = '' WHERE shipping_id = '$shipping_id'";
        $res = $db->query($sql);
    }
    else
    {
        make_json_error($_LANG['js_languages']['upload_del_falid']);
    }

    make_json_result($shipping_id);
}

/*------------------------------------------------------ */
//-- 编辑打印模板
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_print_template')
{
    admin_priv('ship_manage');

    $shipping_id = !empty($_GET['shipping']) ? intval($_GET['shipping']) : 0;

    /* 检查该插件是否已经安装 */
    $sql = "SELECT * FROM " .$hhs->table('shipping'). " WHERE shipping_id=$shipping_id";
    $row = $db->GetRow($sql);
    if ($row)
    {
        include_once(ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php');
        $row['shipping_print'] = !empty($row['shipping_print']) ? $row['shipping_print'] : '';
        $row['print_model'] = empty($row['print_model']) ? 1 : $row['print_model']; //兼容以前版本

        $smarty->assign('shipping', $row);
    }
    else
    {
        $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'shipping.php?act=list');
        sys_msg($_LANG['no_shipping_install'] , 0, $lnk);
    }

    $smarty->assign('ur_here', $_LANG['03_shipping_list'] .' - '. $row['shipping_name'] .' - '. $_LANG['shipping_print_template']);
    $smarty->assign('action_link', array('text' => $_LANG['03_shipping_list'], 'href' => 'shipping.php?act=list'));
    $smarty->assign('shipping_id', $shipping_id);

    assign_query_info();

    $smarty->display('shipping_template.htm');
}

/*------------------------------------------------------ */
//-- 编辑打印模板
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'do_edit_print_template')
{
    /* 检查权限 */
    admin_priv('ship_manage');

    /* 参数处理 */
    $print_model = !empty($_POST['print_model']) ? intval($_POST['print_model']) : 0;
    $shipping_id = !empty($_REQUEST['shipping']) ? intval($_REQUEST['shipping']) : 0;

    /* 处理不同模式编辑的表单 */
    if ($print_model == 2)
    {
        //所见即所得模式
        $db->query("UPDATE " . $hhs->table('shipping'). " SET config_lable = '" . $_POST['config_lable'] . "', print_model = '$print_model'  WHERE shipping_id = '$shipping_id'");
    }
    elseif ($print_model == 1)
    {
        //代码模式
        $template = !empty($_POST['shipping_print']) ? $_POST['shipping_print'] : '';

        $db->query("UPDATE " . $hhs->table('shipping'). " SET shipping_print = '" . $template . "', print_model = '$print_model' WHERE shipping_id = '$shipping_id'");
    }

    /* 记录管理员操作 */
    admin_log(addslashes($_POST['shipping_name']), 'edit', 'shipping');

    $lnk[] = array('text' => $_LANG['go_back'], 'href'=>'shipping.php?act=list');
    sys_msg($_LANG['edit_template_success'], 0, $lnk);
}
if($action == 'delete_join'){
    $sql = $db->query("update ".$hhs->table('shipping_point')." set wx_name='',wx_openid='' where id=".$_REQUEST['id']);

    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $res = 'ok';
    make_json_result($res);
    exit();
}
?>

