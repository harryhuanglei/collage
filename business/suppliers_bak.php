<?php 
define('IN_HHS', true);
require('../includes/init2.php');
require('../includes/lib_order.php');
require('../includes/lib_code.php');
//define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

$smarty->assign('temp_root_path',ROOT_PATH);
$smarty->assign('admin_path',ADMIN_PATH);
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/bonus.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
$_LANG['account_settlement_status'][1] = '待商家审核';
$_LANG['account_settlement_status'][2] = '待平台审核';
$_LANG['account_settlement_status'][3] = '已审核待结算';
$_LANG['account_settlement_status'][4] = '待商家确认账户信息';
$_LANG['account_settlement_status'][5] = '待付款';
$_LANG['account_settlement_status'][6] = '付款完成';
$_LANG['account_settlement_status'][7] = '商家确认已收款';
$_LANG['account_settlement_status'][10] = '商家有疑问';
$_LANG['account_settlement_status'][11] = '未通过平台审核';
/**
 * 解决跳转问题，这个是个渣渣问题。
 * 去管理后台查看自提的id，填写一下
 * 在所有的有问题post表单中发送一下这个订单的shippingID
 * 然后匹配一下
 */
$offlineID = $GLOBALS['db']->getOne("SELECT `shipping_id` from ".$GLOBALS['hhs']->table('shipping')." where `shipping_code` = 'cac'");
define('offlineID', $offlineID);//14
$smarty->assign('offlineID', offlineID);

$smarty->assign('lang', $_LANG);
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
include_once(ROOT_PATH . 'business/includes/lib_mian.php');
// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =array('get_goods_list','bonus_batch','delete_bonus_list','send_by_print','bonus_insert','delete_bonus','drop_goods','restore_goods','goods_import_act','login','register','is_supp_top','check_user_name','is_registered','act_login_sub','delivery_cancel_ship','get_cat_piclist','accounts_apply_del','edit_pic_update',
'delete_pic','operation_goods','update_delivery_pic','drop_delete_pic','pic_category_delete','pic_category_update',
'article_delete','article_insert','supp_update','act_register','get_cat_list',
'act_login','act_edit_profile','accounts_apply_act','order_print','delivery_ship','act_edit_password','change_order_card','add_goods_act','delete_goods','update_goods','logout','drop_image','user_message','edit_cate','add_cate','reply','supp_account_list','add_supp_account','supp_account_insert','supp_account_update','edit_account','shipping_type','update_shipping_type','order_code_check','brand_insert','update_insert','update_brand','allot_act','ad_update','ad_insert','factoryauthorized_update','factoryauthorized_insert','trademark_insert','trademark_update','trademark_delete','insert_user','update_user','delete_user','shipping_area_update','insert_point','update_point','delete_point');
/* 显示页面的action列表 */
$ui_arr = array('goods_order2','gen_bonus_excel','edit_bonus','bonus_list','send_bonus','add_bonus','bunus','goods_trash','factoryauthorized','category','order_operation','delivery_upload','goods_import','sale_order_download','sale_order','sale_list_download','sale_list','order_stats_download','order_stats','register','login','operate_post','operate','bank_config','delivery','commission_desc','shipping_config','my_goods','delivery_list','delivery_info','order_code_list','accounts_apply_list','order_code_check','supp_info','pic_category_add','get_pic','edit_pic','pic_add','pic_category_edit','article_edit','pic_list','pic_category','article_add','edit_goods','default','edit_password','add_goods','goods_list','my_order','account_detail','accounts_apply','accounts_apply_list','order_info','category','user_message','edit_cate','add_cate','reply','supp_update','supp_account_list','add_supp_account','supp_account_insert','supp_account_update','edit_account','shipping_type','update_shipping_type','goods_order','brand','edit_brand','add_brand','allot','ad','add_ad','edit_ad','ad_delete', 'add_factoryauthorized','edit_factoryauthorized','factoryauthorized_delete','article_list','article_edit','trademark','add_trademark','edit_trademark','my_qualification','my_user','add_user','edit_user','supp_shipping','shipping_area_list','shipping_area_edit','shipping_area_add','edit_print_template','shipping_delivery_list','point_list','add_point','edit_point','bussiness','delete_join');
$suppliers_id = $_SESSION['suppliers_id'];
$suppliers_array = get_suppliers_info($suppliers_id);
$smarty->assign('suppliers_array',$suppliers_array);
if(!$_SESSION['role_id'])
{
	$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$suppliers_array['user_name'];
}
else
{
	$role_name = $db->getOne("select name from ".$hhs->table('supp_account')." where account_id='$_SESSION[role_id]'");
	$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$role_name;
}

$smarty->assign('suppliers_id',$suppliers_id);


/* 未登录处理 */
if (empty($suppliers_id))
{
    if (!in_array($action, $not_login_arr))
    {
        if(in_array($action, $ui_arr))
        {
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'suppliers.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}

if($action == 'bussiness')
{
    header("Location:bussiness.php");
    exit();
}

if($action == 'delete_join'){
    $sql = $db->query("update ".$hhs->table('shipping_point')." set wx_name='',wx_openid='' where id=".$_REQUEST['id']);

    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $res = 'ok';
    make_json_result($res);
    exit();
}

/* 如果是显示页面，对页面进行相应赋值 */
if (in_array($action, $ui_arr))
{
    assign_template();
    $position = assign_ur_here(0, '店铺管理中心');
    $smarty->assign('page_title', $position['title']); // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);
  	$info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id='$suppliers_id'");
	$smarty->assign('info',$info);
    $smarty->assign('helps',      get_shop_help());        // 网店帮助
    $smarty->assign('data_dir',   DATA_DIR);   // 数据目录
    $smarty->assign('action',     $action);
	//设置左边菜单
	//$smarty->assign('action_list',get_action_list());
}

if($action =='register')
{
	$smarty->display("suppliers.dwt");
}
/*------------------------------------------------------ */
//-- 添加发放优惠劵的商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add_bonus_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $add_ids    = $json->decode($_GET['add_ids']);
    $args       = $json->decode($_GET['JSON']);
    $type_id    = $args[0];
	
	//限制使用优惠卷的商品
	$use_goods_sn  = strtolower($args[1]);
	
	/*
	if(!empty($use_goods_sn))
	{
		$arr = get_goods_list($filters);
	}
	
	*/
	
    foreach ($add_ids AS $key => $val)
    {
        $sql = "UPDATE " .$hhs->table('goods'). " SET bonus_type_id='$type_id',use_goods_sn='$use_goods_sn' WHERE goods_id='$val'";
		$db->query($sql, 'SILENT') or make_json_error($db->error());
    }

	/* 重新载入 */
    $arr = get_bonus_goods($type_id);
	$opt = array();

    foreach ($arr AS $key => $val)
    {
		if(!empty($val['use_goods_name']))
		{
		    $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name']."---唯一商品名称：[".$val['use_goods_name']."]",
                        'data'  => '');
		}
		else
		{
			$opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
		}
        
    }

    make_json_result($opt);
}

elseif($action =='insert_point')
{
    $sql = "INSERT INTO " .$hhs->table('shipping_point').
            " (shop_name, address,longitude,latitude,tel,suppliers_id,province,city,district) ".
            "VALUES".
            " ('$_POST[shop_name]','$_POST[address]','$_POST[longitude]','$_POST[latitude]','$_POST[tel]' ,'$suppliers_id','$_POST[province]','$_POST[city]','$_POST[district]')";
	$db->query($sql);
	show_message('添加自提点成功。', $_LANG['back_up_page'],'suppliers.php?act=point_list&shipping='.$_POST['shipping'] ,'info');
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
	show_message('编辑成功。', $_LANG['back_up_page'],'suppliers.php?act=point_list&shipping='.$_POST['shipping'] ,'info');
}
elseif($action =='delete_point')
{
	$sql = $db->query("delete from ".$hhs->table('shipping_point')." where id=".$_REQUEST['id']);
	show_message('删除成功', $_LANG['back_up_page'],'suppliers.php?act=point_list&shipping='.$_REQUEST['shipping'] ,'info');
	
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

    $smarty->display('suppliers_shipping.dwt');
}
elseif($_REQUEST['act'] =='drop_point_user')
{
    $id = intval($_GET['id']);
    $point_id = intval($_GET['point_id']);
    $shipping = intval($_GET['shipping']);
    $db->query("DELETE FROM " . $GLOBALS['hhs']->table('shipping_point_user') ." WHERE `id` = '".$id."' and `point_id` = '".$point_id."'");
    hhs_header("location:suppliers.php?act=edit_point&id=".$point_id."&shipping=".$shipping);
    exit();
}
//自提点
elseif($action =='point_list')
{
    $list = get_shipping_point_list($suppliers_id);
	$smarty->assign('shipping',$_REQUEST['shipping']);
    $smarty->assign('point_list',    $list);
    $smarty->display('suppliers_shipping.dwt');
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
	$smarty->display('suppliers_shipping.dwt');
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
	$smarty->display('suppliers_shipping.dwt');
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
    $smarty->display('suppliers_shipping.dwt');
}

//添加配送区域
elseif($action =='shipping_area_insert')
{
	/* 检查同类型的配送方式下有没有重名的配送区域 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table("shipping_area").
            " WHERE shipping_id='$_POST[shipping]' AND shipping_area_name='$_POST[shipping_area_name]' and supp_id = ".$suppliers_id." ";
    if ($db->getOne($sql) > 0)
    {
		
        show_message('已经存在一个同名的配送区域。',$_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
    }
    else
    {
        $shipping_code = $db->getOne("SELECT shipping_code FROM " .$hhs->table('shipping')." WHERE shipping_id='$_POST[shipping]'");
        $plugin  = ROOT_PATH.'/includes/modules/shipping/'. $shipping_code. ".php";

        if (!file_exists($plugin))
        {
			show_message('没有找到指定的配送方式的插件。',$_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
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
		show_message('添加配送区域成功。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'info');
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
	
	$smarty->display('suppliers_shipping.dwt');
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
        show_message('已经存在一个同名的配送区域。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');

    }
    else
    {
        $shipping_code = $db->getOne("SELECT shipping_code FROM " .$hhs->table('shipping'). " WHERE shipping_id='$_POST[shipping]'");
        $plugin        = '../includes/modules/shipping/'. $shipping_code. ".php";

        if (!file_exists($plugin))
        {
            show_message('没有找到指定的配送方式的插件。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
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
		 
		 show_message('编辑配送区域成功。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$_POST['shipping'] ,'error');
		 
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
    show_message('删除配送区域成功。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$shipping ,'info');
	
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
		show_message('请选择要删除的配送区域。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$shipping ,'info');
	}
	
    /* 返回 */
    show_message('删除配送区域成功。', $_LANG['back_up_page'],'suppliers.php?act=shipping_area_list&shipping='.$shipping ,'info');
	
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
		show_message('您的配送方式尚未安装，暂不能编辑模板。', $_LANG['back_up_page'],'suppliers.php?act=supp_shipping' ,'error');
    }

    $smarty->assign('shipping_id', $shipping_id);
	$smarty->display('suppliers_shipping.dwt');
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



/*------------------------------------------------------ */

//-- 删除发放优惠劵的商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'drop_bonus_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;


    $drop_goods     = $json->decode($_GET['drop_ids']);
    $drop_goods_ids = db_create_in($drop_goods);
    $arguments      = $json->decode($_GET['JSON']);
    $type_id        = $arguments[0];

    $db->query("UPDATE ".$hhs->table('goods')." SET bonus_type_id = 0,use_goods_sn=0 ".
                "WHERE bonus_type_id = '$type_id' AND goods_id " .$drop_goods_ids);

    /* 重新载入 */
    $arr = get_bonus_goods($type_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}
elseif($action  =='check_user_name')
{
		include_once('includes/cls_json.php');
		$json = new JSON;
		$result = array('error' => '', 'user_id' => '', 'mobile' =>'');
		include_once(ROOT_PATH . 'includes/lib_passport.php');
		$user_name = $_REQUEST['user_name'];
		$count = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where user_name='$user_name'");
		if($count)
		{
			$result['error'] =1;//错误 
		}
		else
		{
			$result['error'] =0;//成功 
		}
		echo $json->encode($result);
		exit;
}
elseif($action  =='my_user')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where 1 ";
	
	
	if($_REQUEST['keywords']!='')
	{
		$where .= " and user_name like '%%$_REQUEST[keywords]%%'";
	}

	$smarty->assign('keywords',$_REQUEST['keywords']);
	
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('users'). " $where and  suppliers_id = '$suppliers_id'");
	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);
	$users_list = get_suppliers_users($suppliers_id, $pager['size'], $pager['start'],0);
	
	
	foreach($users_list as $key => $val)
	{
		$users_list[$key]['add_date'] = local_date("Y-m-d H:i:s",$val['reg_time']);
	}
	
	$smarty->assign('pager',  $pager);
	$smarty->assign('users_list', $users_list);
    $smarty->display('suppliers_transaction.dwt');
}

elseif($action =='add_user')
{
	$smarty->assign('get_user_crop_cat', get_user_crop_cat());
	$smarty->assign('form_act','insert_user');
	$smarty->display("suppliers_transaction.dwt");
}

elseif($action =='insert_user')
{
	$users =& init_users();
	
	$username =  empty($_POST['username'])     ? '' : trim($_POST['username']);
	$email    =  empty($_POST['email'])  ? '' : trim($_POST['email']);
	$password =  empty($_POST['password'])     ? '' : trim($_POST['password']);
	$data['birthday'] = $_POST['birthdayYear'] . '-' .  $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
	$data['sex']      =  empty($_POST['sex']) ? '' : intval($_POST['sex']);
	$data['credit_line'] = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
	$data['reg_time'] = time();
	$data['suppliers_id'] = $suppliers_id;
	$data['acres'] = empty($_POST['acres']) ? '' : trim($_POST['acres']);
	$data['crop']  = empty($_POST['crop']) ? '' : trim($_POST['crop']);
	$data['user_card']  = empty($_POST['user_card']) ? '' : trim($_POST['user_card']);
	
	$data['msn']  = empty($_POST['msn']) ? '' : trim($_POST['msn']);
	$rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
	if($email=='')
	{
		$email = $username.'@qq.com';
	}
	
	if (!$users->add_user($username, $password, $email))
    {
        /* 插入会员数据失败 */
        if ($users->error == ERR_INVALID_USERNAME)
        {
            $msg = '用户名只能是由字母数字以及下划线组成。';
        }
        elseif ($users->error == ERR_USERNAME_NOT_ALLOW)
        {
            $msg = '用户名 %s 不允许注册';
        }
        elseif ($users->error == ERR_USERNAME_EXISTS)
        {
            $msg = '已经存在一个相同的用户名。';
        }
        elseif ($users->error == ERR_INVALID_EMAIL)
        {
            $msg = 'Email 不是合法的地址';
        }
        elseif ($users->error == ERR_EMAIL_NOT_ALLOW)
        {
            $msg = 'Email %s 不允许注册';
        }
        elseif ($users->error == ERR_EMAIL_EXISTS)
        {
            $msg = '您输入的电子邮件已存在，请换一个试试。';
        }
        else
        {
            //die('Error:'.$users->error_msg());
        }
		show_message($msg,'我的会员列表', 'suppliers.php?act=my_user', 'info');
    }
	
	  if($data[user_card])
	  {
		  $is_user_card = $db->getOne("select count(*) from ".$hhs->table('users')." where user_card='$data[user_card]'");
		  if($is_user_card)
		  {
			  $msg ='对不起，此卡号已存在';
			  show_message($msg,'我的会员列表', 'suppliers.php?act=my_user', 'info');
		  }
	  }
	 
		
		$db->autoExecute($hhs->table('users'), $data, 'UPDATE', "user_name = '$username'");
		show_message('添加成功','我的会员列表', 'suppliers.php?act=my_user', 'info');
	
	
	
}


//编辑商品

elseif($action =='edit_user')
{
	$user_id = $_GET['user_id'];
	$user = $db->getRow("select * from ".$hhs->table('users')." where user_id='$user_id'");
	$user['add_date']=date('Y-m-d',$user['reg_time']);
	
	$user['user_money']=price_format($user['user_money'], false);
	$smarty->assign('user',$user);
	$smarty->assign('user_id',$user_id);
	$smarty->assign('form_act','update_user');
	//种植分类
	$smarty->assign('get_user_crop_cat', get_user_crop_cat($user['crop']));
	
	$smarty->display("suppliers_transaction.dwt");	
}
elseif($action =='update_user')
{
	
	$username = empty($_POST['username']) ? '' : trim($_POST['username']);
    $password = empty($_POST['password']) ? '' : trim($_POST['password']);
    $email = empty($_POST['email']) ? '' : trim($_POST['email']);
    $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
    $sex = in_array($sex, array(0, 1, 2)) ? $sex : 0;
    $birthday = $_POST['birthdayYear'] . '-' .  $_POST['birthdayMonth'] . '-' . $_POST['birthdayDay'];
    $rank = empty($_POST['user_rank']) ? 0 : intval($_POST['user_rank']);
    $credit_line = empty($_POST['credit_line']) ? 0 : floatval($_POST['credit_line']);
	$update_user_money = empty($_POST['update_user_money']) ? 0 : intval($_POST['update_user_money']);
	$user_money = empty($_POST['user_money']) ? 0 : intval($_POST['user_money']);
	$user_id = empty($_POST['user_id']) ? 0 : intval($_POST['user_id']);
	
    $users  =& init_users();
	
	 if (!$users->edit_user(array('username'=>$username, 'password'=>$password, 'email'=>$email, 'gender'=>$sex, 'bday'=>$birthday ), 1))
    {
        if ($users->error == ERR_EMAIL_EXISTS)
        {
            $msg = '您输入的电子邮件已存在，请换一个试试。';
        }
        else
        {
            $msg = '修改会员资料失败';
        }
      	show_message($msg,'我的会员列表', 'suppliers.php?act=my_user', 'info');	
    }
    if(!empty($password))
    {
			$sql="UPDATE ".$hhs->table('users'). "SET `ec_salt`='0' WHERE user_name= '".$username."'";
			$db->query($sql);
	}
	
	
	/* 更新会员的其它信息 */
    $other =  array();
    $other['credit_line'] = $credit_line;
    $other['user_rank'] = $rank;
	$other['msn'] = isset($_POST['msn']) ? trim($_POST['msn']) : '';
	$other['acres'] = empty($_POST['acres']) ? '' : trim($_POST['acres']);
	$other['crop']  = empty($_POST['crop']) ? '' : trim($_POST['crop']);
	$other['user_card']  = empty($_POST['user_card']) ? '' : trim($_POST['user_card']);
	if($other[user_card])
	{
		$is_user_card = $db->getOne("select count(*) from ".$hhs->table('users')." where user_card='$other[user_card]' and user_name<>'$username'");
		if($is_user_card)
		{
			$msg ='对不起，此卡号已存在';
			show_message($msg,'我的会员列表', 'suppliers.php?act=my_user', 'info');
		}
	}
	$db->autoExecute($hhs->table('users'), $other, 'UPDATE', "user_name = '$username'");
	show_message('编辑成功','我的会员列表', 'suppliers.php?act=my_user', 'info');	
}


elseif($action =='delete_user')
{
	
	$sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id = '" . $_GET['user_id'] . "'";
    $username = $db->getOne($sql);
    /* 通过插件来删除用户 */
    $users =& init_users();
    $users->remove_user($username); //已经删除用户所有数据
    show_message('删除成功','返回列表', 'suppliers.php?act=my_user', 'info');
}





elseif($action =='gen_bonus_excel')
{
    @set_time_limit(0);
    /* 获得此线下优惠劵类型的ID */
    $tid  = !empty($_GET['tid']) ? intval($_GET['tid']) : 0;
    $type_name = $db->getOne("SELECT type_name FROM ".$hhs->table('bonus_type')." WHERE type_id = '$tid'");
    /* 文件名称 */
    $bonus_filename = $type_name .'_bonus_list';
    if (EC_CHARSET != 'gbk')
    {
        $bonus_filename = hhs_iconv('UTF8', 'GB2312',$bonus_filename);
    }
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$bonus_filename.xls");

    /* 文件标题 */
    if (EC_CHARSET != 'gbk')
    {
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['bonus_excel_file']) . "\t\n";
        /* 优惠劵序列号, 优惠劵金额, 类型名称(优惠劵名称), 使用结束日期 */
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['bonus_sn']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['type_money']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['type_name']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['use_enddate']) ."\t\n";
    }
    else
    {
        echo $_LANG['bonus_excel_file'] . "\t\n";
        /* 优惠劵序列号, 优惠劵金额, 类型名称(优惠劵名称), 使用结束日期 */
        echo $_LANG['bonus_sn'] ."\t";
        echo $_LANG['type_money'] ."\t";
        echo $_LANG['type_name'] ."\t";
        echo $_LANG['use_enddate'] ."\t\n";
    }

    $val = array();
    $sql = "SELECT ub.bonus_id, ub.bonus_type_id, ub.bonus_sn, bt.type_name, bt.type_money, bt.use_end_date ".
           "FROM ".$hhs->table('user_bonus')." AS ub, ".$hhs->table('bonus_type')." AS bt ".
           "WHERE bt.type_id = ub.bonus_type_id AND ub.bonus_type_id = '$tid' ORDER BY ub.bonus_id DESC";
    $res = $db->query($sql);

    $code_table = array();
    while ($val = $db->fetchRow($res))
    {
        echo $val['bonus_sn'] . "\t";
        echo $val['type_money'] . "\t";
        if (!isset($code_table[$val['type_name']]))
        {
            if (EC_CHARSET != 'gbk')
            {
                $code_table[$val['type_name']] = hhs_iconv('UTF8', 'GB2312', $val['type_name']);
            }
            else
            {
                $code_table[$val['type_name']] = $val['type_name'];
            }
        }
        echo $code_table[$val['type_name']] . "\t";
        echo local_date('Y-m-d', $val['use_end_date']);
        echo "\t\n";
    }	
}
elseif($action =='bonus_batch')
{
   /* 去掉参数：优惠劵类型 */
    $bonus_type_id = intval($_REQUEST['bonus_type']);
    /* 取得选中的优惠劵id */
    if (isset($_POST['bonus_id']))
    {
        $bonus_id_list = $_POST['bonus_id'];
        /* 删除优惠劵 */
        if (isset($_POST['bonus_list_drop']))
        {
            $sql = "DELETE FROM " . $hhs->table('user_bonus'). " WHERE bonus_id " . db_create_in($bonus_id_list);
            $db->query($sql);
   			 show_message('操作成功','返回列表', "suppliers.php?act=bonus_list&page=$page&bonus_type=$bonus_type_id", 'info');
        }
	}
	else
	{
		show_message("请先选择");
	}
}
elseif($action =='delete_bonus_list')
{
    $id = intval($_GET['bonus_id']);
	$bonus_type = $_GET['bonus_type'];
	$page = $_GET['page'];
    $db->query("DELETE FROM " .$hhs->table('user_bonus'). " WHERE bonus_id='$id'");
	show_message('操作成功','返回列表', "suppliers.php?act=bonus_list&page=$page&bonus_type=$bonus_type", 'info');
}
elseif($action =='bonus_list')
{
    $list = get_bonus_list($suppliers_id);
    /* 赋值是否显示优惠劵序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == SEND_BY_PRINT)
    {
        $smarty->assign('show_bonus_sn', 1);
    }
	
    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('pager',$list['pager']);
    $smarty->display('suppliers_bonus_type.dwt');
}
elseif($action =='send_by_print')
{
    @set_time_limit(0);

    /* 红下优惠劵的类型ID和生成的数量的处理 */
    $bonus_typeid = !empty($_POST['bonus_type_id']) ? $_POST['bonus_type_id'] : 0;
    $bonus_sum    = !empty($_POST['bonus_sum'])     ? $_POST['bonus_sum']     : 1;

    /* 生成优惠劵序列号 */
    $num = $db->getOne("SELECT MAX(bonus_sn) FROM ". $hhs->table('user_bonus')." where suppliers_id='$suppliers_id'");
    $num = $num ? floor($num / 10000) : 100000;
    for ($i = 0, $j = 0; $i < $bonus_sum; $i++)
    {
        $bonus_sn = ($num + $i) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $db->query("INSERT INTO ".$hhs->table('user_bonus')." (bonus_type_id, bonus_sn,suppliers_id) VALUES('$bonus_typeid', '$bonus_sn','$suppliers_id')");

        $j++;
    }
	show_message('生成成功','优惠券列表', 'suppliers.php?act=bonus_list&bonus_type='.$bonus_typeid, 'info');
}
elseif($action =='send_bonus')
{
    /* 取得参数 */
    $id = !empty($_REQUEST['id'])  ? intval($_REQUEST['id'])  : '';

    if ($_REQUEST['send_by'] == SEND_BY_USER)
    {
        $smarty->assign('id',           $id);
        // $smarty->assign('ranklist',     get_rank_list());

        // $smarty->display('bonus_by_user.htm');
        $smarty->assign('action','bonus_by_user');
        
    }
    if ($_REQUEST['send_by'] == SEND_BY_GOODS)
    {
        /* 查询此优惠劵类型信息 */
        $bonus_type = $db->GetRow("SELECT type_id, type_name FROM ".$hhs->table('bonus_type').
            " WHERE suppliers_id = '".$suppliers_id."' AND type_id='$_REQUEST[id]'");

        /* 查询优惠劵类型的商品列表 */
        $goods_list = get_bonus_goods($_REQUEST['id']);
		if(is_array($goods_list)){ //by mike add

			foreach($goods_list as $k=>$val){
				
				if(!empty($val['use_goods_name']))
				{
					$goods_list[$k]['goods_name'] = $val['goods_name']."---唯一商品名称：[".$val['use_goods_name']."]";
					
				}
				else
				{
					$goods_list[$k]['goods_name'] = $val['goods_name'];
				}
				
			}
		}
        /* 查询其他优惠劵类型的商品 */
        $sql = "SELECT goods_id FROM " .$hhs->table('goods').
               " WHERE bonus_type_id > 0 AND bonus_type_id <> '$_REQUEST[id]'";
        $other_goods_list = $db->getCol($sql);
        $smarty->assign('other_goods', join(',', $other_goods_list));

        /* 模板赋值 */
     	$smarty->assign('cate_list',     cat_list(0));
        $smarty->assign('bonus_type',  $bonus_type);
        $smarty->assign('goods_list',  $goods_list);
        $smarty->assign('action','bonus_by_goods');
    }
    elseif ($_REQUEST['send_by'] == SEND_BY_PRINT)
    {
        $smarty->assign('type_list',    get_bonus_type($suppliers_id));
		$smarty->assign('action','bonus_by_print');
    }
	$smarty->display('suppliers_bonus_type.dwt');
	
}
elseif($action =='delete_bonus')
{
    $id = intval($_GET['type_id']);
    $db->query("DELETE FROM ".$hhs->table('bonus_type')." WHERE type_id='$id'");

    /* 更新商品信息 */
    $db->query("UPDATE " .$hhs->table('goods'). " SET bonus_type_id = 0 WHERE bonus_type_id = '$id'");
    /* 删除用户的优惠劵 */
    $db->query("DELETE FROM " .$hhs->table('user_bonus'). " WHERE bonus_type_id = '$id'");
	show_message('操作成功','返回列表', 'suppliers.php?act=bunus', 'info');
}
elseif($action =='bonus_update')
{
    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);
    /* 对数据的处理 */
    $type_name   = !empty($_POST['type_name'])  ? trim($_POST['type_name'])    : '';
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;

    //参与线上领取
    $is_online  = !empty($_POST['is_online']) ? intval($_POST['is_online']) : 0;
    if($_POST['send_type'] < 3)
        $is_online  = 0;
    $free_all    = intval($_POST['free_all']);
    $only_first    = intval($_POST['only_first']);


    $sql = "UPDATE " .$hhs->table('bonus_type'). " SET ".
           "type_name       = '$type_name', ".
           "type_money      = '$_POST[type_money]', ".
           "use_start_date  = '$use_startdate', ".
           "use_end_date    = '$use_enddate', ".
           "is_online    = '$is_online', ".
           "free_all    = '$free_all', ".
           "only_first    = '$only_first', ".
		   
		   "send_start_date  = '$send_startdate', ".
           "send_end_date    = '$send_enddate', ".
           "number    = '$_POST[number]', ".
           "is_share    = '$_POST[is_share]', ".
		   
           "send_type       = '$_POST[send_type]', ".
           "min_amount      = '$min_amount', " .
           "min_goods_amount = '" . floatval($_POST['min_goods_amount']) . "' ".
           "WHERE type_id   = '$type_id'";

   $db->query($sql);	
	show_message('操作成功','返回列表', 'suppliers.php?act=bunus', 'info');

}
elseif($action =='edit_bonus')
{
    /* 获取优惠劵类型数据 */
    $type_id = !empty($_GET['type_id']) ? intval($_GET['type_id']) : 0;
    $bonus_arr = $db->getRow("SELECT * FROM " .$hhs->table('bonus_type'). " WHERE type_id = '$type_id'");
    $bonus_arr['send_start_date']   = local_date('Y-m-d', $bonus_arr['send_start_date']);
    $bonus_arr['send_end_date']     = local_date('Y-m-d', $bonus_arr['send_end_date']);
    $bonus_arr['use_start_date']    = local_date('Y-m-d', $bonus_arr['use_start_date']);
    $bonus_arr['use_end_date']      = local_date('Y-m-d', $bonus_arr['use_end_date']);
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('form_act',    'bonus_update');
    $smarty->assign('bonus_arr',   $bonus_arr);
    $smarty->display('suppliers_bonus_type.dwt');
	
}
elseif($action =='bonus_insert')
{
    /* 去掉优惠劵类型名称前后的空格 */
    $type_name   = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';
    /* 初始化变量 */
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
    /* 检查类型是否有重复 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table('bonus_type'). " WHERE type_name='$type_name' and suppliers_id='$suppliers_id'";
    if ($db->getOne($sql) > 0)
    {
        show_message('该名称已存在');
    }


    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);

    $is_share    = intval($_POST['is_share']);
    $number    = $is_share ? intval($_POST['number']):0;
    $free_all    = intval($_POST['free_all']);
    $only_first    = intval($_POST['only_first']);

    //参与线上领取
    $is_online  = !empty($_POST['is_online']) ? intval($_POST['is_online']) : 0;
    if($_POST['send_type'] < 3)
        $is_online  = 0;

    /* 插入数据库。 */
    $sql = "INSERT INTO ".$hhs->table('bonus_type')." (is_online,free_all,only_first,number,is_share,send_start_date,send_end_date,suppliers_id,type_name, type_money,use_start_date,use_end_date,send_type,min_amount,min_goods_amount)
    VALUES ('$is_online',
            '$free_all',
            '$only_first',
            '$number',
            '$is_share',
			'$send_startdate',
			'$send_enddate',
			'$suppliers_id',
			'$type_name',
            '$_POST[type_money]',
            '$use_startdate',
            '$use_enddate',
            '$_POST[send_type]',
            '$min_amount','" . floatval($_POST['min_goods_amount']) . "')";
    $db->query($sql);	
	show_message('操作成功','返回列表', 'suppliers.php?act=bunus', 'info');

	
}
/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => $val['shop_price']);
    }

    make_json_result($opt);
}
elseif($action =='add_bonus')
{
    $smarty->assign('action',       'add_bonus');

    $smarty->assign('form_act',     'bonus_insert');
    $smarty->assign('cfg_lang',     $_CFG['lang']);
    $next_month = local_strtotime('+1 months');
    $bonus_arr['send_start_date']   = local_date('Y-m-d');
    $bonus_arr['use_start_date']    = local_date('Y-m-d');
    $bonus_arr['send_end_date']     = local_date('Y-m-d', $next_month);
    $bonus_arr['use_end_date']      = local_date('Y-m-d', $next_month);
    $smarty->assign('bonus_arr',    $bonus_arr);
    $smarty->display('suppliers_bonus_type.dwt');
}
//优惠券
elseif($action =='bunus')
{
    $list = get_type_list($suppliers_id);
    $smarty->assign('type_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('pager',   $list['pager']);
    $smarty->display('suppliers_bonus_type.dwt');
}
elseif($action =='delivery_ship')
{
    /* 定义当前时间 */
    define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳
    /* 取得参数 */
    $delivery   = array();

    $order_id   = intval(trim($_REQUEST['order_id']));        // 订单id

    $delivery_id   = intval(trim($_REQUEST['delivery_id']));        // 发货单id

    $delivery['invoice_no'] = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';

    $action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
    /* 根据发货单id查询发货单信息 */
    if (!empty($delivery_id))
    {
        $delivery_order = delivery_order_info($delivery_id);
    }
    else
    {
        die('order does not exist');
    }
    /* 查询订单信息 */
    $order = order_info($order_id);
    /* 检查此单发货商品库存缺货情况 */

    $virtual_goods = array();

    $delivery_stock_sql = "SELECT DG.goods_id, DG.is_real, DG.product_id, SUM(DG.send_number) AS sums, IF(DG.product_id > 0, P.product_number, G.goods_number) AS storage, G.goods_name, DG.send_number

        FROM " . $GLOBALS['hhs']->table('delivery_goods') . " AS DG, " . $GLOBALS['hhs']->table('goods') . " AS G, " . $GLOBALS['hhs']->table('products') . " AS P

        WHERE DG.goods_id = G.goods_id

        AND DG.delivery_id = '$delivery_id'

        AND DG.product_id = P.product_id

        GROUP BY DG.product_id ";
    $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
    /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
    if(!empty($delivery_stock_result))
    {
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['storage'] || $value['storage'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
                /* 操作失败 */
                $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
                sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
                break;
            }
            /* 虚拟商品列表 virtual_card*/
            if ($value['is_real'] == 0)
            {
                $virtual_goods[] = array(
                               'goods_id' => $value['goods_id'],
                               'goods_name' => $value['goods_name'],
                               'num' => $value['send_number']
                               );
            }

        }

    }
    else
    {
        $delivery_stock_sql = "SELECT DG.goods_id, DG.is_real, SUM(DG.send_number) AS sums, G.goods_number, G.goods_name, DG.send_number
        FROM " . $GLOBALS['hhs']->table('delivery_goods') . " AS DG, " . $GLOBALS['hhs']->table('goods') . " AS G
        WHERE DG.goods_id = G.goods_id
        AND DG.delivery_id = '$delivery_id'
        GROUP BY DG.goods_id ";
        $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['goods_number'] || $value['goods_number'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
                /* 操作失败 */
                $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
               // sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
			    show_message(sprintf($_LANG['act_good_vacancy'], $value['goods_name']),'返回列表', 'suppliers.php?act=delivery_list&delivery_id='.$delivery_id, 'info');
			    break;
            }

            /* 虚拟商品列表 virtual_card*/
            if ($value['is_real'] == 0)
            {
                $virtual_goods[] = array(
                               'goods_id' => $value['goods_id'],
                               'goods_name' => $value['goods_name'],
                               'num' => $value['send_number']
                               );
            }
        }
    }
    /* 发货 */

    /* 处理虚拟卡 商品（虚货） */
    if (is_array($virtual_goods) && count($virtual_goods) > 0)
    {
        foreach ($virtual_goods as $virtual_value)
        {
            virtual_card_shipping($virtual_value,$order['order_sn'], $msg, 'split');
        }
    }
    /* 如果使用库存，且发货时减库存，则修改库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP)
    {
        foreach ($delivery_stock_result as $value)
        {
            /* 商品（实货）、超级礼包（实货） */
            if ($value['is_real'] != 0)
            {
                //（货品）
                if (!empty($value['product_id']))
                {
                    $minus_stock_sql = "UPDATE " . $GLOBALS['hhs']->table('products') . "
                                        SET product_number = product_number - " . $value['sums'] . "
                                        WHERE product_id = " . $value['product_id'];
                    $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
                }
                $minus_stock_sql = "UPDATE " . $GLOBALS['hhs']->table('goods') . "
                                    SET goods_number = goods_number - " . $value['sums'] . "
                                    WHERE goods_id = " . $value['goods_id'];
                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
            }
        }
    }
    /* 修改发货单信息 */

    $invoice_no = str_replace(',', '<br>', $delivery['invoice_no']);

    $invoice_no = trim($invoice_no, '<br>');

    $_delivery['invoice_no'] = $invoice_no;
	
	$_delivery['update_time'] = GMTIME_UTC;
	
	$_delivery['delivery_person'] = $_REQUEST['delivery_person'];
	

    $_delivery['status'] = 0; // 0，为已发货

    $query = $db->autoExecute($hhs->table('delivery_order'), $_delivery, 'UPDATE', "delivery_id = $delivery_id", 'SILENT');

    if (!$query)

    {

        /* 操作失败 */

        $links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);

        sys_msg($_LANG['act_false'], 1, $links);

    }



    /* 标记订单为已确认 “已发货” */

    /* 更新发货时间 */

    $order_finish = get_all_delivery_finish($order_id);

    $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;

    $arr['shipping_status']     =$shipping_status;

    $arr['shipping_time']       = GMTIME_UTC; // 发货时间

    $arr['invoice_no']          = trim($order['invoice_no'] . '<br>' . $invoice_no, '<br>');

    update_order($order_id, $arr);


    /* 发货单发货记录log */
    order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note,$supp_opt_name, 1);
    /* 如果当前订单已经全部发货 */

    if ($order_finish)
    {
        /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
        if ($order['user_id'] > 0)
        {
            /* 取得用户信息 */
            $user = user_info($order['user_id']);
            /* 计算并发放积分 */
            $integral = integral_to_give($order);
            log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));
            /* 发放红包 */
            //send_order_bonus($order_id);
			
        }
        /* 发送邮件 */

        $cfg = $_CFG['send_ship_email'];
        if ($cfg == '1')
        {
            $order['invoice_no'] = $invoice_no;
            $tpl = get_mail_template('deliver_notice');
            $smarty->assign('order', $order);
            $smarty->assign('send_time', local_date($_CFG['time_format']));
            $smarty->assign('shop_name', $_CFG['shop_name']);
            $smarty->assign('send_date', local_date($_CFG['date_format']));
            $smarty->assign('sent_date', local_date($_CFG['date_format']));
            $smarty->assign('confirm_url', $hhs->url() . 'receive.php?id=' . $order['order_id'] . '&con=' . rawurlencode($order['consignee']));
            $smarty->assign('send_msg_url',$hhs->url() . 'user.php?act=message_list&order_id=' . $order['order_id']);
            $content = $smarty->fetch('str:' . $tpl['template_content']);
            if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']))
            {
                $msg = $_LANG['send_mail_fail'];
            }
        }

        /* 如果需要，发短信 */

        if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '')
        {
            include_once('../includes/cls_sms.php');
            $sms = new sms();
			if($order['shipping_id'] ==10)
			{
          	 	 $sms->send($order['mobile'], sprintf($GLOBALS['_LANG']['order_tshipped_sms'], $order['order_sn'],
                local_date($GLOBALS['_LANG']['sms_time_format']), $GLOBALS['_CFG']['shop_name']), 0);
			}
			else
			{
          	  $sms->send($order['mobile'], sprintf($GLOBALS['_LANG']['order_shipped_sms'], $order['order_sn'],
                local_date($GLOBALS['_LANG']['sms_time_format']), $GLOBALS['_CFG']['shop_name']), 0);
			}

        }

    }	
    if(offlineID == $_REQUEST['shipping_id'] || offlineID == $_REQUEST['delivery_id']){
        $url = 'suppliers.php?act=delivery_list';
    }   
    else{
        $url = 'suppliers.php?act=shipping_delivery_list';
    }
    show_message('操作成功','返回列表', $url, 'info');
		// show_message('操作成功','返回列表', 'suppliers.php?act=delivery_list', 'info');
}
//佣金说明
elseif($action =='commission_desc')
{
	$sql = $db->getAll("select * from ".$hhs->table('category')." where commission<>0");
	$smarty->assign('category_list',$sql);
	$smarty->display("suppliers_transaction.dwt");
}
elseif($action =='delivery_cancel_ship')
{
    /* 取得参数 */
    $delivery = '';

    $order_id   = intval(trim($_REQUEST['order_id']));        // 订单id

    $delivery_id   = intval(trim($_REQUEST['delivery_id']));        // 发货单id

    $delivery['invoice_no'] = isset($_REQUEST['invoice_no']) ? trim($_REQUEST['invoice_no']) : '';

    $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';



    /* 根据发货单id查询发货单信息 */

    if (!empty($delivery_id))

    {

        $delivery_order = delivery_order_info($delivery_id);

    }

    else
    {
        die('order does not exist');
    }
    /* 查询订单信息 */
    $order = order_info($order_id);
    /* 取消当前发货单物流单号 */
    $_delivery['invoice_no'] = '';
    $_delivery['status'] = 2;
    $query = $db->autoExecute($hhs->table('delivery_order'), $_delivery, 'UPDATE', "delivery_id = $delivery_id", 'SILENT');
    if (!$query)
    {
        /* 操作失败 */
        $links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
        sys_msg($_LANG['act_false'], 1, $links);
        exit;
    }
    /* 修改定单发货单号 */
    $invoice_no_order = explode('<br>', $order['invoice_no']);
    $invoice_no_delivery = explode('<br>', $delivery_order['invoice_no']);
    foreach ($invoice_no_order as $key => $value)
    {
        $delivery_key = array_search($value, $invoice_no_delivery);
        if ($delivery_key !== false)
        {

            unset($invoice_no_order[$key], $invoice_no_delivery[$delivery_key]);

            if (count($invoice_no_delivery) == 0)

            {

                break;

            }

        }

    }

    $_order['invoice_no'] = implode('<br>', $invoice_no_order);



    /* 更新配送状态 */

    $order_finish = get_all_delivery_finish($order_id);

    $shipping_status = ($order_finish == -1) ? SS_SHIPPED_PART : SS_SHIPPED_ING;

    $arr['shipping_status']     = $shipping_status;

    if ($shipping_status == SS_SHIPPED_ING)

    {

        $arr['shipping_time']   = ''; // 发货时间

    }

    $arr['invoice_no']          = $_order['invoice_no'];

    update_order($order_id, $arr);



    /* 发货单取消发货记录log */

    order_action($order['order_sn'], $order['order_status'], $shipping_status, $order['pay_status'], $action_note,$supp_opt_name, 1);



    /* 如果使用库存，则增加库存 */

    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP)

    {

        // 检查此单发货商品数量

        $virtual_goods = array();

        $delivery_stock_sql = "SELECT DG.goods_id, DG.product_id, DG.is_real, SUM(DG.send_number) AS sums

            FROM " . $GLOBALS['hhs']->table('delivery_goods') . " AS DG

            WHERE DG.delivery_id = '$delivery_id'

            GROUP BY DG.goods_id ";

        $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

        foreach ($delivery_stock_result as $key => $value)

        {

            /* 虚拟商品 */

            if ($value['is_real'] == 0)

            {

                continue;

            }



            //（货品）

            if (!empty($value['product_id']))

            {

                $minus_stock_sql = "UPDATE " . $GLOBALS['hhs']->table('products') . "

                                    SET product_number = product_number + " . $value['sums'] . "

                                    WHERE product_id = " . $value['product_id'];

                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');

            }



            $minus_stock_sql = "UPDATE " . $GLOBALS['hhs']->table('goods') . "

                                SET goods_number = goods_number + " . $value['sums'] . "

                                WHERE goods_id = " . $value['goods_id'];

            $GLOBALS['db']->query($minus_stock_sql, 'SILENT');

        }

    }



    /* 发货单全退回时，退回其它 */

    if ($order['order_status'] == SS_SHIPPED_ING)
    {
        /* 如果订单用户不为空，计算积分，并退回 */
        if ($order['user_id'] > 0)
        {
            /* 取得用户信息 */
            $user = user_info($order['user_id']);
            /* 计算并退回积分 */
            $integral = integral_to_give($order);
            log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));
            /* todo 计算并退回红包 */
            return_order_bonus($order_id);
        }

    }
	show_message('操作成功','返回列表', 'suppliers.php?act=delivery_list', 'info');
}
elseif($action =='delivery')
{
    //
    
	$smarty->display("suppliers_transaction.dwt");		
}

elseif($action =='change_order_card')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('content' => '', 'result' => '', 'error' => '');
//	if($_SESSION['user_id'] =='')
//	{
//		$res['error'] =0;
//		die($json->encode($res));
//		exit;
//	}
	$id = $_REQUEST['id'];
	$sql = $db->query("update ".$ecs->table('order_info')." set is_delivery=1 where order_id='$id' and district='$district'");
	$res['error'] =1;
	die($json->encode($res));
	exit;
}
elseif($action =='order_code_check_act')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
	require_once(ROOT_PATH . 'includes/lib_code.php');
    $json = new JSON;
	$res    = array('content' => '', 'result' => '', 'error' => '');
	$order_sn = $_REQUEST['order_sn'];
	$order_code = $_REQUEST['order_code'];

	$rows = $db->getRow("select *,(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee from ".$hhs->table('order_info')." where pay_status=2 and order_sn='$order_sn' and msg_code='$order_code' and suppliers_id ='$suppliers_id' ");
	$smarty->assign('rows',$rows);
	$count  = $db->getOne("select count(*) from  ".$hhs->table('order_info')." where pay_status=2 and order_sn='$order_sn' and msg_code='$order_code' and suppliers_id ='$suppliers_id'  ");
	
   $sql = "SELECT do.delivery_id FROM " . $GLOBALS['hhs']->table("delivery_order") . "as do,".$GLOBALS['hhs']->table("order_info")." as oi where do.order_sn=oi.order_sn and do.order_sn='$order_sn' and oi.msg_code='$order_code' and oi.suppliers_id='$suppliers_id'";
   $delivery_id = $GLOBALS['db']->getOne($sql);
	if($delivery_id)
	{
		$res['error'] =1;
        $delivery_order = delivery_order_info($delivery_id);
		/* 取得用户名 */
		if ($delivery_order['user_id'] > 0)
		{
			$user = user_info($delivery_order['user_id']);
			if (!empty($user))
			{
				$delivery_order['user_name'] = $user['user_name'];
			}
		}
		/* 取得区域名 */
		$sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
					"'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
				"FROM " . $hhs->table('order_info') . " AS o " .
					"LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .
					"LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .
					"LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .
					"LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .
				"WHERE o.order_id = '" . $delivery_order['order_id'] . "'";
		$delivery_order['region'] = $db->getOne($sql);
		/* 是否保价 */
		$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
		/* 取得发货单商品 */
		$goods_sql = "SELECT *
					  FROM " . $hhs->table('delivery_goods') . "
					  WHERE delivery_id = " . $delivery_order['delivery_id'];
		$goods_list = $GLOBALS['db']->getAll($goods_sql);
		/* 是否存在实体商品 */
		$exist_real_goods = 0;
		if ($goods_list)
		{
			foreach ($goods_list as $value)
			{
				if ($value['is_real'])
				{
					$exist_real_goods++;
				}
			}
		}
		/* 模板赋值 */
		$smarty->assign('delivery_order', $delivery_order);
		$smarty->assign('exist_real_goods', $exist_real_goods);
		$smarty->assign('goods_list', $goods_list);
		$smarty->assign('delivery_id', $delivery_id); // 发货单id
		$smarty->assign('action_act', ($delivery_order['status'] == 2) ? 'delivery_ship' : 'delivery_cancel_ship');
		$res['content'] =$smarty->fetch('library/order_code.lbi');
		die($json->encode($res));
		exit;
	}
	else
	{
		$res['error'] =0;
		die($json->encode($res));
		exit;
	}
}
//发货管理管理

elseif($action =='delivery_list')
{
	$arr=get_delivery_list(true,1);
    
	$smarty->assign('delivery_list',$arr['delivery']);
	
    $smarty->assign('pager',$arr['pager']);
    $smarty->assign('filter',$arr['filter']);
    $smarty->assign('supp_account_list',get_supp_account_list($suppliers_id));
    //var_dump($_LANG);exit();
	$smarty->display("suppliers_transaction.dwt");	
}
//发货单管理
elseif($action =='shipping_delivery_list')
{
    $arr=get_delivery_list(true,0);
    $smarty->assign('delivery_list',$arr['delivery']);
    $smarty->assign('pager',$arr['pager']);
    $smarty->assign('filter',$arr['filter']);
    $smarty->assign('supp_account_list',get_supp_account_list($suppliers_id));
    //var_dump($_LANG);exit();
    $smarty->display("suppliers_transaction.dwt");
}
elseif($action =='delivery_upload'){
	$delivery_id = intval(trim($_REQUEST['delivery_id']));
	$smarty->assign('timestamp',time());
	$unique_salt =  md5('unique_salt'.time());
	$smarty->assign('unique_salt',$unique_salt);
	$smarty->assign('delivery_id',$delivery_id);
	$smarty->display("delivery_upload.dwt");
}
elseif ($_REQUEST['act'] == 'delivery_download'){
	$arr=get_delivery_list(false);
	$delivery_list=$arr['delivery'];
	$title="提货单";

	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");

	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
	/* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
	echo hhs_iconv(EC_CHARSET, 'GB2312', '分店') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '提货人') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '下单时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '提货时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '提货状态') . "\t\n";


	foreach($delivery_list AS $key => $value)
	{
		if($value['supp_account_name']) $supp_account_name=$value['supp_account_name'];
		else  $supp_account_name='未指派';
		echo hhs_iconv(EC_CHARSET, 'GB2312',$supp_account_name ) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['delivery_person']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";

		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['update_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['status_name']) . "\t";
		echo "\n";
	}
	exit;
}
elseif ($_REQUEST['act'] == 'delivery_print'){
	$arr=get_delivery_list(false);
	$delivery_list=$arr['delivery'];

	$title="结算单";

	$smarty->assign('title',$title);
	$smarty->assign('delivery_list',$delivery_list);
	$html=$smarty->fetch('delivery_print.dwt');
	echo $html;exit();

}

elseif($action =='delivery_info')
{
    $delivery_id = intval(trim($_REQUEST['delivery_id']));
    /* 根据发货单id查询发货单信息 */
    if (!empty($delivery_id))
    {
        $delivery_order = delivery_order_info($delivery_id);
    }
    else
    {
        die('order does not exist');
    }
    /* 取得用户名 */
    if ($delivery_order['user_id'] > 0)
    {
        $user = user_info($delivery_order['user_id']);
        if (!empty($user))
        {
            $delivery_order['user_name'] = $user['user_name'];
        }
    }
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $hhs->table('order_info') . " AS o " .
                "LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .

                "LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .

                "LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .

                "LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .

            "WHERE o.order_id = '" . $delivery_order['order_id'] . "'";

    $delivery_order['region'] = $db->getOne($sql);

    /* 是否保价 */

    $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
   /* 取得发货单商品 */
    $goods_sql = "SELECT *
                  FROM " . $hhs->table('delivery_goods') . "
                  WHERE delivery_id = " . $delivery_order['delivery_id'];

    $goods_list = $GLOBALS['db']->getAll($goods_sql);
    /* 是否存在实体商品 */
    $exist_real_goods = 0;
    if ($goods_list)
    {
        foreach ($goods_list as $key=> $value)
        {
            if ($value['is_real'])
            {
                $exist_real_goods++;
            }
       
        $sql="select * from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$delivery_order['order_id']." and goods_id=".$value['goods_id'];
        $good=$GLOBALS['db']->getRow($sql);
        $goods_list[$key]['goods_price']= $good['goods_price'];
        $goods_list[$key]['goods_amount']=$goods_list[$key]['goods_price']*$goods_list[$key]['send_number'];
        $total_goods_amount+=$goods_list[$key]['goods_amount'];
        $goods_list[$key]['goods_price']=price_format($goods_list[$key]['goods_price']);
        $goods_list[$key]['goods_amount']=price_format($goods_list[$key]['goods_amount']);
	  }
   }
	  
        
      $smarty->assign('total_goods_amount', price_format($total_goods_amount));
        //商家信息
        $sql = "SELECT o.suppliers_name,o.address,o.phone, concat( IFNULL(p.region_name, ''), " .
        		"'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        		"FROM " . $hhs->table('suppliers') . " AS o " .
        		"LEFT JOIN " . $hhs->table('region') . " AS p ON o.province_id = p.region_id " .
        		"LEFT JOIN " . $hhs->table('region') . " AS t ON o.city_id = t.region_id " .
        		"LEFT JOIN " . $hhs->table('region') . " AS d ON o.district_id = d.region_id " .
        		"WHERE o.suppliers_id = '" . $delivery_order['suppliers_id'] . "'";
        $suppliers_info=$db->getRow($sql);
        //echo $sql;exit();
        $smarty->assign('suppliers_info', $suppliers_info);  /* 模板赋值 */
    $smarty->assign('delivery_order', $delivery_order);
    $smarty->assign('exist_real_goods', $exist_real_goods);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('delivery_id', $delivery_id); // 发货单id
    $smarty->assign('action_act', ($delivery_order['status'] == 2) ? 'delivery_ship' : 'delivery_cancel_ship');
   $smarty->display("suppliers_transaction.dwt");	
}

elseif($action =='delivery_info_print')
{
	$delivery_id = intval(trim($_REQUEST['delivery_id']));
	/* 根据发货单id查询发货单信息 */
	if (!empty($delivery_id))
	{
		$delivery_order = delivery_order_info($delivery_id);
	}
	else
	{
		die('order does not exist');
	}
	/* 取得用户名 */
	if ($delivery_order['user_id'] > 0)
	{
		$user = user_info($delivery_order['user_id']);
		if (!empty($user))
		{
			$delivery_order['user_name'] = $user['user_name'];
		}
	}
	/* 取得区域名 */
	$sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
			"'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
			"FROM " . $hhs->table('order_info') . " AS o " .
			"LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .

			"LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .

			"LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .

			"LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .

			"WHERE o.order_id = '" . $delivery_order['order_id'] . "'";

	$delivery_order['region'] = $db->getOne($sql);
	/* 是否保价 */
	$order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
	/* 取得发货单商品 */
	$goods_sql = "SELECT *

	FROM " . $hhs->table('delivery_goods') . "

	WHERE delivery_id = " . $delivery_order['delivery_id'];

	$goods_list = $GLOBALS['db']->getAll($goods_sql);

	/* 是否存在实体商品 */

	$exist_real_goods = 0;
	//$order_id=$delivery_order['order_id'];
	//$order = order_info($order_id);
	
	if ($goods_list)
	{
		foreach ($goods_list as $key=>$value)
		{
			if ($value['is_real'])
			{
				$exist_real_goods++;
			}
			$sql="select * from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$delivery_order['order_id']." and goods_id=".$value['goods_id'];
			$good=$GLOBALS['db']->getRow($sql);
			$goods_list[$key]['goods_price']= $good['goods_price'];
			$goods_list[$key]['goods_amount']=$goods_list[$key]['goods_price']*$goods_list[$key]['send_number'];
			$total_goods_amount+=$goods_list[$key]['goods_amount'];
			$goods_list[$key]['goods_price']=price_format($goods_list[$key]['goods_price']);
			$goods_list[$key]['goods_amount']=price_format($goods_list[$key]['goods_amount']);
		}
	}
	$smarty->assign('total_goods_amount', price_format($total_goods_amount));
	//商家信息
	$sql = "SELECT o.suppliers_name,o.address,o.phone, concat( IFNULL(p.region_name, ''), " .
			"'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
			"FROM " . $hhs->table('suppliers') . " AS o " .		
			"LEFT JOIN " . $hhs->table('region') . " AS p ON o.province_id = p.region_id " .	
			"LEFT JOIN " . $hhs->table('region') . " AS t ON o.city_id = t.region_id " .
			"LEFT JOIN " . $hhs->table('region') . " AS d ON o.district_id = d.region_id " .	
			"WHERE o.suppliers_id = '" . $delivery_order['suppliers_id'] . "'";
	$suppliers_info=$db->getRow($sql);
	//echo $sql;exit();
	$smarty->assign('suppliers_info', $suppliers_info);
	$smarty->assign('current_time', local_date('Y-m-d',gmtime()));
	/* 模板赋值 */
	$smarty->assign('delivery_order', $delivery_order);
	$smarty->assign('exist_real_goods', $exist_real_goods);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('delivery_id', $delivery_id); // 发货单id
	$html=$smarty->fetch("delivery_info_print.dwt");
	echo $html;exit();
	
}
elseif($action =='my_order')
{
	$arr=get_account_list();
	$smarty->assign('account_list',$arr['account_list']);
	$smarty->assign('pager',          $arr['pager']);
	$smarty->assign('filter',          $arr['filter']);
	$smarty->display("suppliers_transaction.dwt");
}
elseif($_REQUEST['act'] == 'account_download'){
	$arr=get_account_list(false);
	$account=$arr['account_list'];
	
	#print_r($account);die;
	#print_r($_LANG);die;

	$title="结算单";
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");
	
	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
	/* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
	echo hhs_iconv(EC_CHARSET, 'GB2312', '序号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算单号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算起始时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算截止时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算金额') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '状态') . "\t\n";
	
	foreach($account AS $key => $value)
	{		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $key+1) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['start_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['end_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_amount']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['account_settlement_status'][$value['settlement_status']]) . "\t";
		echo "\n";
	}/*
	echo "\t\t\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '金额合计') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_settlement_amount) . "\t";
	*/
	exit;
}

elseif ($_REQUEST['act'] == 'account_print'){	
	$arr=get_account_list();
	$title="结算单";		
	$smarty->assign('title',$title);
	$smarty->assign('account',$arr['account_list']);
	$smarty->assign('total_settlement_amount',$arr['total_settlement_amount']);
	$html=$smarty->fetch('account_print.dwt');
	echo $html;exit();
}
elseif($action =='account_detail')
{//echo"";exit();
	$sql="select * from ".$hhs->table('suppliers_accounts')." where id=".$_GET['suppliers_accounts_id'];
	$suppliers_account=$db->getRow($sql);
	
	$smarty->assign('settlement_status',$suppliers_account['settlement_status']);
	
	$account_detail=account_detail_list();
	

	
	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')." where suppliers_id =  ".$suppliers_account['suppliers_id']);
	$smarty->assign('supp_row',$supp_row);
	
	$sql = "SELECT * FROM " . $hhs->table('settlement_action') . " WHERE settlement_id = '$_GET[suppliers_accounts_id]' ORDER BY log_time DESC,action_id DESC";
	$res = $db->query($sql);
	while ($row = $db->fetchRow($res))
	{
	    $row['status_name']    = $_LANG['account_settlement_status'][$row['status']];
	    $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
	    $act_list[] = $row;
	}
	$smarty->assign('action_list2', $act_list);
	
	$smarty->assign('account_detail',$account_detail['row']);
	$smarty->assign('total_amount',$account_detail['total_amount']);
	$smarty->assign('total_commission',$account_detail['total_commission']);
    $smarty->assign('total_fenxiao',$account_detail['total_fenxiao']); 
	$smarty->assign('total_money',$account_detail['total_money']); 
	$smarty->assign('suppliers_accounts_id',$_GET['suppliers_accounts_id']);
	$smarty->assign('pager',          $account_detail['pager']);
	$smarty->display("suppliers_transaction.dwt");
}
elseif($action =='account_detail_form'){
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$hhs->table('suppliers_accounts')." set remark='".$remark."' where id=".$id;
	$r=$db->query($sql);
	$result=array();	
	if($r>0){
		$result['error']   = 0;
		$result['content'] = '提交成功';
	}else{
		$result['error']   = 1;
		$result['content'] = '提交失败';
	}	
	$json   = new JSON;
	echo $json->encode($result);
}
elseif($action =='account_confirm')
{//确认无误
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set settlement_status=2,remark='$remark' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}
elseif($action =='check_accountok')
{//确认账户信息
    require(ROOT_PATH . 'includes/cls_json.php');
    $id=$_POST['id'];
    $remark=$_POST['remark'];
    $sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set remark='$remark',settlement_status='5' where id=".$id;
    $db->query($sql);
    //操作记录
    $suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
    settlement_action($id,$remark,$suppliers_info['suppliers_name']);
    $result['error']   = 0;
    $result['content'] = '提交成功';
    $json   = new JSON;
    echo $json->encode($result);
}
elseif($action =='account_cancel')
{//有疑义
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set remark='$remark',settlement_status='5' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}

elseif($action =='account_receive'){//确认收款
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=empty($_REQUEST['id'])?0:$_REQUEST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set settlement_status=7,remark='$remark' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}
elseif ($_REQUEST['act'] == 'account_detail_download'){

    $suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];
    $sql="select * from ". $GLOBALS['hhs']->table("suppliers_accounts") ." where id=".$suppliers_accounts_id;
    $row=$db->getRow($sql);
    $add_month=$row['add_month'];
    $title=substr($add_month,0,4)."年".substr($add_month,4,2)."月结算明细";
   
	
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");
	
	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '编号') . "\t";
    
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '交易单号') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商家') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '会员名称') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '支付方式') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '付款时间') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单金额') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '佣金') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算金额') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品名称') . "\t";
   
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品数量') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品单位') . "\t\n";
    
	$total_amount=$total_commission=$total_money=0;
	
	$row=account_detail_list();
	
	foreach($row['row'] AS $key => $value)
	{
		$total_amount+=$value['amount'];
		$total_commission+=$value['commission'];
		$total_money+=($value['amount']-$value['commission']);
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['id']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['transaction_order_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['suppliers_name']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['user_name']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['pay_name']) . "\t";
		
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_time']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['amount']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['commission']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['money']) . "\t";
		
		foreach($value['goods']['goods_list'] as $k=>$v){
		 
		    if($k!=0){
		        echo "\t\t\t\t\t\t\t\t\t\t\t";
		    }
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($v['goods_name'])) . "\t";
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($v['goods_number'])) . "\t";
		    $g=explode(' ', trim($v['goods_attr']));
		    $str="";
		    foreach($g as $f){
		        $tmp=explode(':', trim($f));
		        $p=strrpos( $tmp[1],'[');
		        if($p!==false){
		            $str.=substr($tmp[1],0,$p);
		        }
		         
		    }
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($str)) . "\t";
		    echo "\n";
		}

	}
	echo "\t\t\t\t\t\t\t";
	
	echo hhs_iconv(EC_CHARSET, 'GB2312', '金额合计') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_amount) . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_commission) . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_money) . "\t";
	exit;
    
    
}
elseif ($_REQUEST['act'] == 'account_detail_print'){
	$suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];

	$sql="select * from ". $GLOBALS['hhs']->table("suppliers_accounts") ." where id=".$suppliers_accounts_id;
	$row=$db->getRow($sql);
	$add_month=$row['add_month'];
	$title=substr($add_month,0,4)."年".substr($add_month,4,2)."月结算明细";
	$where=" where sat.suppliers_accounts_id=".$suppliers_accounts_id;
	$sql = "SELECT sat.* ".
			" FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat ".
			$where." ORDER BY sat.id desc ";
	$account_detail=$db->getAll($sql);
	$total_amount=$total_commission=$total_money=0;
	foreach($account_detail AS $key => $value)
	{
		$total_amount+=$value['amount'];
		$total_commission+=$value['commission'];
		$total_money+=($value['amount']-$value['commission']);			
		$account_detail[$key]['order_time']=local_date($GLOBALS['_CFG']['time_format'],$value['order_time']);		
		$account_detail[$key]['money']=$value['amount']-$value['commission'];
		$sql="select goods_number from ".$hhs->table('order_goods')." where order_id=".$value['order_id'];
		$goods_number=$db->getAll($sql);
		foreach($goods_number as $v){
			$account_detail[$key]['total_goods_num']=$v['goods_number'];
		}
		$sql="select goods_name from ".$hhs->table('order_goods')." where order_id=".$value['order_id'];
		$goods_name=$db->getAll($sql);
		foreach($goods_name as $v){
			$account_detail[$key]['goods_name']= substr($v['goods_name'].',',0,-1);
		}
	}
	$smarty->assign('title',$title);
	$smarty->assign('total_amount',$total_amount);
	$smarty->assign('total_commission',$total_commission);
	$smarty->assign('total_money',$total_money);
	$smarty->assign('account_detail',$account_detail);
	$html=$smarty->fetch('account_detail_print.dwt');
	echo $html;exit();
	 
}

elseif($action =='accounts_apply_act')

{

	$idx = $_REQUEST['idx'];

	$bank_password = $_REQUEST['bank_password'];

	$supp_config = get_supp_config($suppliers_id); 
	if($supp_config['bank_name']=='')
	{
		show_message('请先设置开户行名称');	
	}
	if($supp_config['bank_p_name']=='')
	{
		show_message('请先设置开户行姓名');	
	}
	if($supp_config['bank_account']=='')
	{
		show_message('请先设置开户行账号');	
	}


	if($supp_config['bank_password']=='')
	{
		show_message('请先设置支付密码');	
	}

	else

	{

		if($supp_config['bank_password']!=$bank_password)	

		{

			show_message('请输入正确的支付密码');	

		}

	}

	$sql = "select og.goods_price,og.goods_sn,og.rec_id,o.order_sn,og.goods_name,(og.goods_price*og.goods_number) as price,(g.commission*og.goods_number) as commission,og.goods_number,o.add_time,og.suppliers_accounts_status from ".$hhs->table('order_goods')." as og, ".$hhs->table('order_info')." as o,".$hhs->table('goods')." as g  where g.goods_id=og.goods_id and og.order_id = o.order_id  and rec_id in($idx) and og.suppliers_id='$suppliers_id' and og.suppliers_accounts_status=0";

	$list = $db->getAll($sql);

	$account_total ='';

	$price = '';

	foreach($list as $value)

	{

		$rec_ids[] = $value['rec_id'];  

		$account_total = $account_total+$value['commission'];

		$price = $price+$value['price'];	

	}

	$end_account_total = $price-$account_total;

	$bank_name = $_POST['bank_name'];

	$bank_p_name = $_POST['bank_p_name'];

	$bank_account = $_POST['bank_account'];

	$add_time = gmtime();

	$apply_desc = $_POST['apply_desc'];

	$sql = $db->query("insert into ".$hhs->table('suppliers_accounts_apply')." (account,suppliers_id,bank_name,bank_p_name,bank_account,add_time,apply_desc,rec_id) values ('$end_account_total','$suppliers_id','$bank_name','$bank_p_name','$bank_account','$add_time','$apply_desc','$rec_id')");

	show_message('申请成功','返回我的列表', 'suppliers.php?act=accounts_apply_list', 'info');

}


elseif($action =='accounts_apply_del')

{

	$id = $_GET['id'];

	$sql = $db->query("delete from ".$hhs->table('suppliers_accounts_apply')." where id='$id'");

    show_message('取消成功','返回列表', 'suppliers.php?act=accounts_apply_list', 'info');

}

elseif($action =='accounts_apply')
{
	if($_REQUEST['id']=='')
	{
		 show_message("请先选择要结算的订单");
	}
	$idx = join(",",$_REQUEST['id']);
	$sql = "select og.goods_price,og.goods_id,og.goods_sn,o.order_sn,og.goods_name,(og.goods_price*og.goods_number) as price,og.goods_number,o.add_time,og.suppliers_accounts_status from ".$hhs->table('order_goods')." as og, ".$hhs->table('order_info')." as o,".$hhs->table('goods')." as g  where og.rec_id in($idx) and g.goods_id=og.goods_id and og.order_id = o.order_id  and og.suppliers_id='$suppliers_id' and og.suppliers_accounts_status=0";
	$list = $db->getAll($sql);
	$account_total ='';
	$price_total = '';
	foreach($list as $value)
	{
		$account_total = $account_total+get_commission($value['goods_id'],$value['price']);	
		$price_total = $price_total+$value['price'];	
	}
	if($_CFG['agent_apply_account']>$price_total)
	{
		 show_message('金额满'.$_CFG['agent_apply_account']."才可提现");
	}
	else
	{
		$supp_config = $db->getRow("select * from ".$hhs->table('supp_config')." where suppliers_id='$suppliers_id'");
		$smarty->assign('supp_config',$supp_config);
		$smarty->assign('idx',$idx);
		$smarty->assign('account_total',$price_total-$account_total);
		$smarty->assign('price_total',$price_total);
		$smarty->assign('account_total',$account_total);
		$smarty->display("suppliers_transaction.dwt");
	}
}

elseif($action =='article_list')
{
	$cat_list = article_cat_list(52,'',true,0,true);
	
	
	$smarty->assign('cat_list',$cat_list);
	$cat_id =  empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
	$where = '';
	if($keywords)
	{
		$where .= " and title like '%%$keywords%%'";
	}
	elseif($cat_id!='')
	{
		$where .= " and cat_id='$cat_id'";
	}
	else
	{
		$sql = "select * from ".$hhs->table('article')." where suppliers_id='$suppliers_id'";
	}
	$sql = "select * from ".$hhs->table('article')." where suppliers_id='$suppliers_id' $where";
	$res = $db->getAll($sql);
	foreach($res as $idx=>$value)
	{
		$res[$idx]['add_time'] = date("Y-m-d",$value['add_time']);
	}
	$smarty->assign("list",$res);
   $smarty->display('suppliers_transaction.dwt');
}

elseif($action =='article_insert')
{
	$title = $_POST['title'];
	$author = $_POST['author'];
	$add_time = gmtime();
	$content = $_POST['content'];
	$cat_id =  empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
	$keywords = $_POST['keywords'];
	$description = $_POST['description'];
	$sql = $db->query("insert into ".$hhs->table('article')." (suppliers_id,article_type,author_email,keywords,description,title,author,add_time,content,cat_id,is_open) values ('$suppliers_id','0','$email','$keywords','$description','$title','$author','$add_time','$content','$cat_id','0')");
	show_message('发布成功，等待管理员审核...','返回技术文章列表', 'suppliers.php?act=article_list', 'info');
}

elseif($action =='article_delete')
{
	$id = $_REQUEST['id'];
	$sql = $db->query("delete from ".$hhs->table('article')." where article_id='$id'");
	show_message('删除成功');
}

elseif($action =='article_update')
{
	$title = $_POST['title'];
	$author = $_POST['author'];
	$add_time = gmtime();
	$content = $_POST['content'];
	$cat_id =  empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
	$article_id = $_POST['article_id'];
	$keywords = $_POST['keywords'];
	$description = $_POST['description'];
	$sql = $db->query("update  ".$hhs->table('article')." set title='$title',keywords='$keywords',description='$description',author='$author',add_time='$add_time',content='$content',cat_id='$cat_id',is_open=0 where article_id='$article_id'");
	show_message('编辑成功');
}
elseif($action =='article_add')
{
	include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件
	create_html_editor_xaphp('content','',$suppliers_id);
	//$smarty->assign('goods_cat_list', cat_list());
	$cat_list = article_cat_list(52,'',true,0,true);
	$smarty->assign('goods_cat_list',$cat_list);
	
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='article_edit')
{
	$id = $_REQUEST['id'];
	$rows = $db->getRow("select * from ".$hhs->table('article')." where article_id='$id'");
	$smarty->assign('rows',$rows);
	include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件
	create_html_editor_xaphp('content',$rows['content'],$suppliers_id);
	$cat_list = article_cat_list(52,$rows['cat_id'],true,0,true);
	$smarty->assign('goods_cat_list',$cat_list);
	$smarty->display('suppliers_transaction.dwt');	
}

elseif($action =='change_order_card')

{

    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

	$res    = array('content' => '', 'result' => '', 'error' => '');

	if($_SESSION['user_id'] =='')

	{

		$res['error'] =0;

		die($json->encode($res));

		exit;

	}

	$id = $_GET['id'];

	

    $id       = intval($_POST['id']);

	$tuan_buy_list=$db->getOne("SELECT t.order_sn,t.goods_id,g.is_real FROM ".$hhs->table('tuan_buy')." AS t LEFT JOIN ".$hhs->table("goods")." AS g ON t.goods_id=g.goods_id WHERE t.id='$id'");

	$order_sn=$tuan_buy_list['order_sn'];

	$is_real=intval($tuan_buy_list['is_real']);

	

	if ($db->query("UPDATE ".$hhs->table('tuan_buy')." SET is_sale='1' WHERE id='$id'"))

	{

		$order=array("order_status"=>OS_SPLITED,"shipping_status"=>SS_RECEIVED,"shipping_time"=>gmtime());

		if($is_real==0)//实体产品不修改订单状态

		{

			$GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('order_info'),

		$order, 'UPDATE', "order_sn = '$order_sn'");//修改订单状态

		}

	}

	$res['error'] =1;

	die($json->encode($res));

	exit;

}



elseif($action =='order_code_check')

{

	$smarty->assign('action','order_code_check');

	$smarty->display("suppliers_transaction.dwt");

}

elseif($action =='order_code_list')

{

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$filter['is_sale']     = intval($_REQUEST['is_sale']);

    $filter['sort_by']     = empty($_REQUEST['sort_by'])     ? 't.id' : trim($_REQUEST['sort_by']);

    $filter['sort_order']  = empty($_REQUEST['sort_order'])  ? 'DESC' : trim($_REQUEST['sort_order']);

    if(in_array($filter['is_sale'],array(1,2)))

	{

		if($filter['is_sale']==2)

		{

			$where .= " AND t.is_sale = '0'";

		}

		else

		{

			$where .= " AND t.is_sale = '$filter[is_sale]'";

		}

	}

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('users') . " AS u,".$GLOBALS['hhs']->table('tuan_buy')." AS t  LEFT join ".$GLOBALS['hhs']->table('order_info')." AS oi ON t.order_sn=oi.order_sn  WHERE t.user_id=u.user_id and oi.suppliers_id='$suppliers_id' $where";

    $record_count = $GLOBALS['db']->getOne($sql);

	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);

    /* 查询 */

    $sql = "SELECT t.*,u.user_name,oi.order_id ".

            " FROM ".$GLOBALS['hhs']->table('users')." AS u,".$GLOBALS['hhs']->table('tuan_buy')." AS t".

			" LEFT join ".$GLOBALS['hhs']->table('order_info')." AS oi ON t.order_sn=oi.order_sn ".

            " WHERE t.user_id=u.user_id and oi.suppliers_id='$suppliers_id' ".$where.

            " ORDER BY $filter[sort_by] $filter[sort_order] ".

            " LIMIT $pager[start], $pager[size]";

    $all = $GLOBALS['db']->getAll($sql);

    $arr = array();

	$all_goods_amount =0;

    foreach ($all AS $key => $row)

    {

        if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY))

        {

            $row['password'] = decrypt($row['password']);

        }

        elseif ($row['crc32'] == crc32(OLD_AUTH_KEY))

        {

            $row['password'] = decrypt($row['password'], OLD_AUTH_KEY);

        }

        else

        {

            $row['password'] = '***';

        }

		$order_sn = $row['order_sn'];

		

		$row['goods_amount'] = $GLOBALS['db']->getOne("select goods_price FROM ".$GLOBALS['hhs']->table('order_goods')."  where goods_id='$row[goods_id]' AND order_id='".$row['order_id']."'");

		

		$all_goods_amount = $all_goods_amount+$row['goods_amount'];



        $row['add_date'] =local_date($GLOBALS['_CFG']['date_format'], $row['add_date']);

		if($filter['keyword'])

		{

			if($filter['keyword'] == $row['password'])	

			{

				 $arr[] = $row;	

			}

			else

			{

					$arr[] = '';	

			}

		}

		else

		{

      		  $arr[] = $row;

		}

		

    }

	$smarty->assign('pager',$pager);

	$smarty->assign('card_list',$arr);

	$smarty->display("suppliers_transaction.dwt");



	

}


elseif($action =='order_download')
{
	$smarty->assign('status_list', $_LANG['cs']);   // 订单状态
	$smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);	
	$smarty->assign('cs_await_pay',     CS_AWAIT_PAY);
	$smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);
	$arr=get_order_list(false);	
	$order_list=$arr['orders'];
	$title="订单";
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");
	
	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
	/* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
	echo hhs_iconv(EC_CHARSET, 'GB2312', '序号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '提货码') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '购货人') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '下单时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '电话') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '地址') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '总金额') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '应付金额') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '订单状态') . "\t\n";
	
	$order_sn_list = $_POST['order_id'];
	 if($order_sn_list=='')
	 {
		 show_message('请先选择订单');
	 }
	foreach ($order_sn_list as $order_sn)
	{
		$value = order_info(0, $order_sn);
        if (empty($order))
        {
             continue;
        }
		echo hhs_iconv(EC_CHARSET, 'GB2312',$key+1 ) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['msg_code']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['buyer']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['short_order_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['mobile']?$value['mobile']:$value['tel']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['address']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['os'][$value['order_status']].",".$_LANG['ps'][$value['pay_status']].",".$_LANG['ss'][$value['shipping_status']]) . "\t";		
		echo "\n";
	}
	exit;

}
elseif($action =='order_operation')
{
	   $html = '';
	   $order_sn_list = $_POST['order_id'];
	   if($order_sn_list=='')
	   {
		   show_message('请先选择订单');
	   }
	   
	  if(@$_REQUEST['order_print'])
	  { 
			   foreach ($order_sn_list as $order_sn)
			   {
					/* 取得订单信息 */
					$order = order_info(0, $order_sn);
					if (empty($order))
					{
						continue;
					}
				/* 取得区域名 */
				$sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
						"'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
						"FROM " . $hhs->table('order_info') . " AS o " .
						"LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .
						"LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .
						"LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .
						"LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .
						"WHERE o.order_id = '$order[order_id]'";
				$order['region'] = $db->getOne($sql);
				/* 其他处理 */
				$order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
				$order['pay_time']      = @$order['pay_time'] > 0 ?
				local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
				$order['shipping_time'] = @$order['shipping_time'] > 0 ?
				local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
				$order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
				$order['invoice_no']    = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
				/* 此订单的发货备注(此订单的最后一条操作记录) */
				$sql = "SELECT action_note FROM " . $hhs->table('order_action').
				" WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";
				$order['invoice_note'] = $db->getOne($sql);
				/* 参数赋值：订单 */
				$smarty->assign('order', $order);
				/* 取得订单商品 */
				$goods_list = array();		
				$goods_attr = array();		
				$sql = "SELECT o.*, g.goods_number AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name " .
						"FROM " . $hhs->table('order_goods') . " AS o ".
						"LEFT JOIN " . $hhs->table('goods') . " AS g ON o.goods_id = g.goods_id " .
						"LEFT JOIN " . $hhs->table('brand') . " AS b ON g.brand_id = b.brand_id " .
						"WHERE o.order_id = '$order[order_id]' ";		
				$res = $db->query($sql);		
				while ($row = $db->fetchRow($res))		
				{	
					/* 虚拟商品支持 */		
					if ($row['is_real'] == 0)		
					{		
						/* 取得语言项 */
						$filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';
						if (file_exists($filename))	
						{	
							include_once($filename);
							if (!empty($_LANG[$row['extension_code'].'_link']))
							{
								$row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);
							}
						}
					}
					$row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
					$row['formated_goods_price']    = price_format($row['goods_price']);
					$goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组	
					$goods_list[] = $row;		
				}	
				$attr = array();	
				$arr  = array();		
				foreach ($goods_attr AS $index => $array_val)		
				{	
					foreach ($array_val AS $value)		
					{	
						$arr = explode(':', $value);//以 : 号将属性拆开
						$attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
				
					}	
				}
				$smarty->assign('goods_attr', $attr);	
				$smarty->assign('goods_list', $goods_list);	
				$smarty->template_dir = '../' . DATA_DIR;
				//echo $smarty->template_dir;exit;
				$html .= $smarty->fetch('order_print.html') .		
				'<div style="PAGE-BREAK-AFTER:always"></div>';
			}
			echo $html;
	  }
	  if(@$_REQUEST['order_download'])
	  {
		  $smarty->assign('status_list', $_LANG['cs']);   // 订单状态
		  $smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);	
		  $smarty->assign('cs_await_pay',     CS_AWAIT_PAY);
		  $smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);
		  $arr=get_order_list(false);	
		  $order_list=$arr['orders'];
		  $title="订单列表";
		  header("Content-type: application/vnd.ms-excel; charset=utf-8");
		  header("Content-Disposition: attachment; filename=".$title.".xls");
		  
		  /* 文件标题 */
		  echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
		  /* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '序号') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '提货码') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '购货人') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '下单时间') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '电话') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '地址') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '总金额') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '应付金额') . "\t";
		  echo hhs_iconv(EC_CHARSET, 'GB2312', '订单状态') . "\t\n";
		  
		  $order_sn_list = $_POST['order_id'];
		   if($order_sn_list=='')
		   {
			   show_message('请先选择订单');
		   }
		  foreach ($order_sn_list as $order_sn)
		  {
			  $value = order_info(0, $order_sn);
			  if (empty($value))
			  {
				   continue;
			  }
			  $value['short_order_time'] = local_date('Y-m-d',$value['add_time']);
			  echo hhs_iconv(EC_CHARSET, 'GB2312',$key+1 ) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['msg_code']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['short_order_time']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['mobile']?$value['mobile']:$value['tel']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['address']) . "\t";
			  
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
			  echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['os'][$value['order_status']].",".$_LANG['ps'][$value['pay_status']].",".$_LANG['ss'][$value['shipping_status']]) . "\t";		
			  echo "\n";
		  }
		  exit;

	  }
	  
}
else if($action =='order_download2'){
    $arr=get_order_list(false); 
    $order_list=$arr['orders'];  
    $title="订单列表";
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=".$title.".xls");
    
    /* 文件标题 */
    echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
    /* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
    echo hhs_iconv(EC_CHARSET, 'GB2312', '序号') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '会员名') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '下单时间') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '电话') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '地址') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '总金额') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '应付金额') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单状态') . "\t\n";
    
    
    foreach ($order_list as $key=>$value)
    {
       
        $value['short_order_time'] = local_date('Y-m-d',$value['add_time']);
        echo hhs_iconv(EC_CHARSET, 'GB2312',$key+1 ) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
        
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['user_name']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['short_order_time']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['mobile']?$value['mobile']:$value['tel']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['address']) . "\t";
        	
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total_fee']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', strip_tags($_LANG['os'][$value['order_status']].",".$_LANG['ps'][$value['pay_status']].",".$_LANG['ss'][$value['shipping_status']])) . "\t";
        echo "\n";
    }
    exit;

}
//订单列表
else if($action =='goods_order' || $action =='goods_order2')
{	
	$smarty->assign('status_list', $_LANG['cs']);   // 订单状态
   	$smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);
    $smarty->assign('cs_await_pay',     CS_AWAIT_PAY);

   	$smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);
   	
    if($action =='goods_order'){
        $order_list=get_order_list(true,$action);     
    }
    else{
        $order_list=get_order_list(true,$action);
    }
	
   
	$smarty->assign('pager', $order_list['pager']);

	$smarty->assign('order_list',$order_list['orders']);
	$order_list['filter']['start_date']=local_date('Y-m-d H:i:s',$order_list['filter']['start_time']);
	$order_list['filter']['end_date']=local_date('Y-m-d H:i:s',$order_list['filter']['end_time']);
	$smarty->assign('filter',$order_list['filter']);
	

	
	$smarty->assign('action',$action);

	$smarty->display("suppliers_transaction.dwt");

}


//订单详情

else if($action =='order_info')
{
    
	/* 根据订单id或订单号查询订单信息 */
    if (isset($_REQUEST['order_id']))
    {
        $order_id = intval($_REQUEST['order_id']);
        $order = order_info($order_id);
    }
    elseif (isset($_REQUEST['order_sn']))
    {
        $order_sn = trim($_REQUEST['order_sn']);
        $order = order_info(0, $order_sn);
    }
    else
    {
        /* 如果参数不存在，退出 */
        die('invalid parameter');
    }
    /* 如果订单不存在，退出 */
    if (empty($order))
    {
        die('order does not exist');
    }
    $link="http://" . $_SERVER['HTTP_HOST'] . "/qrcode_delivery.php?order_id=".$order_id;  //"/index.php";
    $smarty->assign('link', $link);
    
    /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
    $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
    $agency_id = $db->getOne($sql);
    if ($agency_id > 0)
    {
        if ($order['agency_id'] != $agency_id)
        {
            sys_msg($_LANG['priv_error']);
        }
    }
    /* 取得上一个、下一个订单号 */
    if (!empty($_COOKIE['ECSCP']['lastfilter']))
    {
        $filter = unserialize(urldecode($_COOKIE['ECSCP']['lastfilter']));
        if (!empty($filter['composite_status']))
        {
            $where = '';
            //综合状态
            switch($filter['composite_status'])
            {
                case CS_AWAIT_PAY :
                    $where .= order_query_sql('await_pay');
                    break;
                case CS_AWAIT_SHIP :
                    $where .= order_query_sql('await_ship');
                    break;
                case CS_FINISHED :
                    $where .= order_query_sql('finished');
                    break;
                default:
                    if ($filter['composite_status'] != -1)
                    {
                        $where .= " AND o.order_status = '$filter[composite_status]' ";
                    }
            }
        }
    }
    $sql = "SELECT MAX(order_id) FROM " . $hhs->table('order_info') . " as o WHERE order_id < '$order[order_id]'";
    if ($agency_id > 0)
    {
        $sql .= " AND agency_id = '$agency_id'";
    }
    if (!empty($where))
    {
        $sql .= $where;
    }
    $smarty->assign('prev_id', $db->getOne($sql));
    $sql = "SELECT MIN(order_id) FROM " . $hhs->table('order_info') . " as o WHERE order_id > '$order[order_id]'";
    if ($agency_id > 0)
    {
        $sql .= " AND agency_id = '$agency_id'";
    }
    if (!empty($where))
    {
        $sql .= $where;
    }
    $smarty->assign('next_id', $db->getOne($sql));
    /* 取得用户名 */
    if ($order['user_id'] > 0)
    {
        $user = user_info($order['user_id']);
        if (!empty($user))
        {
            $order['user_name'] = $user['user_name'];
        }
    }
    /* 取得所有办事处 */
    $sql = "SELECT agency_id, agency_name FROM " . $hhs->table('agency');
    $smarty->assign('agency_list', $db->getAll($sql));
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $hhs->table('order_info') . " AS o " .
                "LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .
                "LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .
                "LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .
                "LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .
            "WHERE o.order_id = '$order[order_id]'";
    $order['region'] = $db->getOne($sql);

    if($order['point_id']){
        $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region,o.address,o.shop_name,o.tel " .
        "FROM " . $GLOBALS['hhs']->table('shipping_point') . " AS o " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS p ON o.province = p.region_id " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS t ON o.city = t.region_id " .
            "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS d ON o.district = d.region_id " .
        "WHERE o.id = '$order[point_id]'";
        $point_info = $GLOBALS['db']->getRow($sql);
    }
    else{
        $point_info = array();
    }   
    $smarty->assign('point_info', $point_info); 
        
    /* 格式化金额 */
    if ($order['order_amount'] < 0)
    {
        $order['money_refund']          = abs($order['order_amount']);
        $order['formated_money_refund'] = price_format(abs($order['order_amount']));
    }
    /* 其他处理 */
    $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
    $order['pay_time']      = $order['pay_time'] > 0 ?
        local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
    $order['shipping_time'] = $order['shipping_time'] > 0 ?
        local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
    $order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
    $order['invoice_no']    = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];
    /* 取得订单的来源 */
    if ($order['from_ad'] == 0)
    {
        $order['referer'] = empty($order['referer']) ? $_LANG['from_self_site'] : $order['referer'];
    }
    elseif ($order['from_ad'] == -1)
    {
        $order['referer'] = $_LANG['from_goods_js'] . ' ('.$_LANG['from'] . $order['referer'].')';
    }
    else
    {
        /* 查询广告的名称 */
         $ad_name = $db->getOne("SELECT ad_name FROM " .$hhs->table('ad'). " WHERE ad_id='$order[from_ad]'");
         $order['referer'] = $_LANG['from_ad_js'] . $ad_name . ' ('.$_LANG['from'] . $order['referer'].')';
    }
    /* 此订单的发货备注(此订单的最后一条操作记录) */

    $sql = "SELECT action_note FROM " . $hhs->table('order_action').

           " WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";

    $order['invoice_note'] = $db->getOne($sql);
    /* 取得订单商品总重量 */
    $weight_price = order_weight_price($order['order_id']);
    $order['total_weight'] = $weight_price['formated_weight'];

    /* 取得订单操作记录 */
    $act_list = array();
    $sql = "SELECT * FROM " . $hhs->table('order_action') . " WHERE order_id = '$order[order_id]' ORDER BY log_time DESC,action_id DESC";

    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $row['order_status']    = $_LANG['os'][$row['order_status']];
        $row['pay_status']      = $_LANG['ps'][$row['pay_status']];
        $row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
        $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
        $act_list[] = $row;
    }

	
    $smarty->assign('opt_action_list', $act_list);

    /* 参数赋值：订单 */

    $smarty->assign('order', $order);
    /* 取得用户信息 */
    if ($order['user_id'] > 0)
    {
        /* 用户等级 */
        if ($user['user_rank'] > 0)
        {
            $where = " WHERE rank_id = '$user[user_rank]' ";
        }
        else
        {
            $where = " WHERE min_points <= " . intval($user['rank_points']) . " ORDER BY min_points DESC ";
        }
        $sql = "SELECT rank_name FROM " . $hhs->table('user_rank') . $where;
        $user['rank_name'] = $db->getOne($sql);

        // 用户红包数量

        $day    = getdate();

        $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $sql = "SELECT COUNT(*) " .

                "FROM " . $hhs->table('bonus_type') . " AS bt, " . $hhs->table('user_bonus') . " AS ub " .

                "WHERE bt.type_id = ub.bonus_type_id " .

                "AND ub.user_id = '$order[user_id]' " .

                "AND ub.order_id = 0 " .

                "AND bt.use_start_date <= '$today' " .

                "AND bt.use_end_date >= '$today'";

        $user['bonus_count'] = $db->getOne($sql);

        $smarty->assign('user', $user);
        // 地址信息
        $sql = "SELECT * FROM " . $hhs->table('user_address') . " WHERE user_id = '$order[user_id]'";
        $smarty->assign('address_list', $db->getAll($sql));
    }
    /* 取得订单商品及货品 */
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, g.suppliers_id, IFNULL(b.brand_name, '') AS brand_name, p.product_sn
            FROM " . $hhs->table('order_goods') . " AS o
                LEFT JOIN " . $hhs->table('products') . " AS p
                    ON p.product_id = o.product_id
                LEFT JOIN " . $hhs->table('goods') . " AS g
                    ON o.goods_id = g.goods_id
                LEFT JOIN " . $hhs->table('brand') . " AS b
                    ON g.brand_id = b.brand_id
            WHERE o.order_id = '$order[order_id]'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        /* 虚拟商品支持 */
        if ($row['is_real'] == 0)
        {
            /* 取得语言项 */
            $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';
            if (file_exists($filename))
            {
                include_once($filename);
                if (!empty($_LANG[$row['extension_code'].'_link']))
                {
                    $row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);
                }
            }
        }
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);
        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
        if ($row['extension_code'] == 'package_buy')
        {
            $row['storage'] = '';
            $row['brand_name'] = '';
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;
    }
    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);//以 : 号将属性拆开
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }
    $smarty->assign('goods_attr', $attr);
    $smarty->assign('goods_list', $goods_list);
    /* 取得能执行的操作列表 */
    $operable_list = operable_list($order);
    $smarty->assign('operable_list', $operable_list);
    /* 取得是否存在实体商品 */
    $smarty->assign('exist_real_goods', exist_real_goods($order['order_id']));
    /* 是否打印订单，分别赋值 */
    if (isset($_GET['print']))
    {
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('shop_url',     $hhs->url());
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['admin_name']);
        $smarty->template_dir = '../' . DATA_DIR;

        
        $smarty->display('order_print.html');
    }
    /* 打印快递单 */
    elseif (isset($_GET['shipping_print']))
    {
        //$smarty->assign('print_time',   local_date($_CFG['time_format']));
        //发货地址所在地
        $region_array = array();
        $region_id = !empty($_CFG['shop_country']) ? $_CFG['shop_country'] . ',' : '';
        $region_id .= !empty($_CFG['shop_province']) ? $_CFG['shop_province'] . ',' : '';
        $region_id .= !empty($_CFG['shop_city']) ? $_CFG['shop_city'] . ',' : '';
        $region_id = substr($region_id, 0, -1);
        $region = $db->getAll("SELECT region_id, region_name FROM " . $hhs->table("region") . " WHERE region_id IN ($region_id)");
        if (!empty($region))
        {
            foreach($region as $region_data)
            {
                $region_array[$region_data['region_id']] = $region_data['region_name'];
            }
        }
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('order_id',    $order_id);
        $smarty->assign('province', $region_array[$_CFG['shop_province']]);
        $smarty->assign('city', $region_array[$_CFG['shop_city']]);
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $shipping = $db->getRow("SELECT * FROM " . $hhs->table("shipping") . " WHERE shipping_id = " . $order['shipping_id']);
        //打印单模式
        if ($shipping['print_model'] == 2)
        {
            /* 可视化 */
            /* 快递单 */
            $shipping['print_bg'] = empty($shipping['print_bg']) ? '' : get_site_root_url() . $shipping['print_bg'];
            /* 取快递单背景宽高 */
            if (!empty($shipping['print_bg']))
            {
                $_size = @getimagesize($shipping['print_bg']);
                if ($_size != false)
                {
                    $shipping['print_bg_size'] = array('width' => $_size[0], 'height' => $_size[1]);
                }
            }
            if (empty($shipping['print_bg_size']))
            {
                $shipping['print_bg_size'] = array('width' => '1024', 'height' => '600');
            }
            /* 标签信息 */
            $lable_box = array();
            $lable_box['t_shop_country'] = $region_array[$_CFG['shop_country']]; //网店-国家
            $lable_box['t_shop_city'] = $region_array[$_CFG['shop_city']]; //网店-城市
            $lable_box['t_shop_province'] = $region_array[$_CFG['shop_province']]; //网店-省份
            $lable_box['t_shop_name'] = $_CFG['shop_name']; //网店-名称
            $lable_box['t_shop_district'] = ''; //网店-区/县
            $lable_box['t_shop_tel'] = $_CFG['service_phone']; //网店-联系电话
            $lable_box['t_shop_address'] = $_CFG['shop_address']; //网店-地址
            $lable_box['t_customer_country'] = $region_array[$order['country']]; //收件人-国家
            $lable_box['t_customer_province'] = $region_array[$order['province']]; //收件人-省份
            $lable_box['t_customer_city'] = $region_array[$order['city']]; //收件人-城市
            $lable_box['t_customer_district'] = $region_array[$order['district']]; //收件人-区/县
            $lable_box['t_customer_tel'] = $order['tel']; //收件人-电话
            $lable_box['t_customer_mobel'] = $order['mobile']; //收件人-手机
            $lable_box['t_customer_post'] = $order['zipcode']; //收件人-邮编
            $lable_box['t_customer_address'] = $order['address']; //收件人-详细地址
            $lable_box['t_customer_name'] = $order['consignee']; //收件人-姓名
            $gmtime_utc_temp = gmtime(); //获取 UTC 时间戳

            $lable_box['t_year'] = date('Y', $gmtime_utc_temp); //年-当日日期

            $lable_box['t_months'] = date('m', $gmtime_utc_temp); //月-当日日期

            $lable_box['t_day'] = date('d', $gmtime_utc_temp); //日-当日日期



            $lable_box['t_order_no'] = $order['order_sn']; //订单号-订单

            $lable_box['t_order_postscript'] = $order['postscript']; //备注-订单

            $lable_box['t_order_best_time'] = $order['best_time']; //送货时间-订单

            $lable_box['t_pigeon'] = '√'; //√-对号

            $lable_box['t_custom_content'] = ''; //自定义内容



            //标签替换
            $temp_config_lable = explode('||,||', $shipping['config_lable']);
            if (!is_array($temp_config_lable))
            {
                $temp_config_lable[] = $shipping['config_lable'];
            }
            foreach ($temp_config_lable as $temp_key => $temp_lable)
            {
                $temp_info = explode(',', $temp_lable);
                if (is_array($temp_info))
                {
                    $temp_info[1] = $lable_box[$temp_info[0]];
                }
                $temp_config_lable[$temp_key] = implode(',', $temp_info);
            }
            $shipping['config_lable'] = implode('||,||',  $temp_config_lable);
            $smarty->assign('shipping', $shipping);
            $smarty->display('print.htm');
        }
        elseif (!empty($shipping['shipping_print']))
        {
            /* 代码 */
            echo $smarty->fetch("str:" . $shipping['shipping_print']);
        }
        else
        {
            $shipping_code = $db->getOne("SELECT shipping_code FROM " . $hhs->table('shipping') . " WHERE shipping_id=" . $order['shipping_id']);
            if ($shipping_code)
            {
                include_once(ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php');
            }
            if (!empty($_LANG['shipping_print']))
            {
                echo $smarty->fetch("str:$_LANG[shipping_print]");
            }
            else
            {
                echo $_LANG['no_print_shipping'];
            }
        }
    }
    else
    {
        $smarty->assign('action','order_info');
		$smarty->display("suppliers_transaction.dwt");
    }
}

//账号设置

else if($action =='bank_config')

{

	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");

	$smarty->assign('supp_row',$supp_row);

	$smarty->display("suppliers_transaction.dwt");

}

elseif($action =='shipping_config')

{

	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");

	$smarty->assign('supp_row',$supp_row);

	$smarty->display("suppliers_transaction.dwt");

}

elseif($action =='shipping_config_act')

{

	$shipping_type = $_REQUEST['shipping_type'];

	$shipping_fee = $_REQUEST['shipping_fee'];

	$supp_isset = $db->getOne("select id from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");

	if($supp_isset){

		//存在则更新

		  $sql = 'UPDATE ' . $hhs->table('supp_config') . " SET `shipping_type`='$shipping_type',`shipping_fee`='$shipping_fee' WHERE `id`='" . $supp_isset. "'";			

		  $res = $db->query($sql);

		  if($res){

			show_message('修改成功', $_LANG['back_up_page'], './suppliers.php?act=shipping_config', 'info');

		  }

	} 

	else

	{

		//不存在则添加	

		  $sql = "INSERT INTO ". $hhs->table('supp_config') . " (`shipping_type`, `shipping_fee`) VALUES ('$shipping_type', '$shipping_fee')";

		  $res = $db->query($sql);

		  if($res){

			show_message('设置成功', $_LANG['back_up_page'], './suppliers.php?act=shipping_config', 'info');

		  }

	}

	

	

}



//更新运费类型

else if($action =='update_shipping_type')

{

	$bank_name = $_POST['bank_name'];

	$bank_p_name = $_POST['bank_p_name'];

	$bank_account = $_POST['bank_account'];

	$bank_password = $_POST['bank_password'];

	$supp_isset = $db->getOne("select id from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");



	if($supp_isset){

		//存在则更新

		  $sql = 'UPDATE ' . $hhs->table('supp_config') . " SET `bank_name`='$bank_name',`bank_p_name`='$bank_p_name',bank_account='$bank_account',bank_password='$bank_password'  WHERE `id`='" . $supp_isset. "'";			

		  $res = $db->query($sql);

		  if($res){

			show_message('修改成功', $_LANG['back_up_page'], './suppliers.php?act=bank_config', 'info');

		  }

	} 

	else

	{

		//不存在则添加	

		  $sql = "INSERT INTO ". $hhs->table('supp_config') . " (`suppliers_id`,`bank_name`, `bank_p_name`, `bank_account`, `bank_password`) VALUES ('$suppliers_id','$bank_name', '$bank_p_name','$bank_account','$bank_password')";

		  $res = $db->query($sql);

		  if($res){

			show_message('设置成功', $_LANG['back_up_page'], './suppliers.php?act=bank_config', 'info');

		  }

	}

}

//子账号管理

else if($action =='supp_account_list')
{
	$sql = "select * from ".$hhs->table('supp_account')."where suppliers_id = ".$suppliers_id." order by sort_order asc";
	$account_list = $db->getAll($sql);
	$smarty->assign('account_list',$account_list);
	$smarty->assign('action','supp_account');
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='allot')
{
	$sql = "select * from ".$hhs->table('supp_account')."where account_id = ".$_REQUEST['id'];
	$account_info = $db->getRow($sql);
	$smarty->assign('role_lists',get_role($account_info['role']));
	
	$smarty->assign('account_info',$account_info);
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action=='allot_act')
{
	$role         = $_REQUEST['role_action'];
	$account_id = $_REQUEST['account_id'];
	$role         = join(",",$role);
	
	
	$sql = 'UPDATE ' . $hhs->table('supp_account') . " SET  `role`='$role'  WHERE `account_id`='" . $account_id. "'";
	$res = $db->query($sql);
	if($res){

	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'info');

	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'error');

	}
	
}
else if($action =='add_supp_account')
{
	$smarty->assign('action','add_supp_account');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','supp_account_insert');
	$smarty->display('suppliers_transaction.dwt');
}
else if($action =='edit_account')
{

	$id = $_REQUEST['id'];

	$sql = "select * from ".$hhs->table('supp_account')."where account_id = ".$id;

	$account_info = $db->getRow($sql);

	$smarty->assign('account_info',$account_info);

	$smarty->assign('action','edit_supp_account');

	$smarty->assign('status','supp_account_update');

	$smarty->display('suppliers_transaction.dwt');

}

else if($action  == 'supp_account_insert')
{
	$account_name = $_REQUEST['account_name'];
	$name         = $_REQUEST['name'];
	$role         = $_REQUEST['role'];
	$is_check     = $_REQUEST['is_check'];
	$sort_order   = $_REQUEST['sort_order'];
	$suppliers_id = $_REQUEST['suppliers_id'];
	$account_type = $_REQUEST['account_type'];
	$address      = $_REQUEST['address'];
	$phone        = $_REQUEST['phone'];
	$account_password     = md5($_REQUEST['account_password']);
	$is_reg = $db->getOne("select count(*) from ".$hhs->table('supp_account')." where account_name='$account_name'"); 
	if($is_reg)
	{
		show_message('该账号已存在，请重新输入');
	}
	$sql = "INSERT INTO ". $hhs->table('supp_account') . " (`account_password`,`address`,`phone`,`account_name`, `name`, `role`,`is_check`,`sort_order`,`suppliers_id`,`account_type`) VALUES ('$account_password','$address','$phone','$account_name', '$name', '$role','$is_check','$sort_order','$suppliers_id','$account_type')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'error');
	}
}

else if($action  == 'supp_account_update')
{
	$account_name = $_REQUEST['account_name'];
	$name         = $_REQUEST['name'];
	$is_check     = $_REQUEST['is_check'];
	$sort_order   = $_REQUEST['sort_order'];
	$account_type = $_REQUEST['account_type'];
	$address      = $_REQUEST['address'];
	$account_id = $_REQUEST['account_id'];
	$phone        = $_REQUEST['phone'];
	$account_password     = md5($_REQUEST['account_password']);


	if($_REQUEST['account_password'] =='')
	{
		$sql = 'UPDATE ' . $hhs->table('supp_account') . " SET `name`='$name',`address`='$address',`phone`='$phone',`account_type`='$account_type' , `is_check`='$is_check', `sort_order`='$sort_order' WHERE `account_id`='" . $account_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('supp_account') . " SET `account_password`='$account_password',`phone`='$phone',`address`='$address',`account_type`='$account_type' ,`name`='$name', `is_check`='$is_check', `sort_order`='$sort_order' WHERE `account_id`='" . $account_id. "'";
	}
	
	$res = $db->query($sql);

	if($res){

	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'info');

	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'error');

	}

}



else if($action  == 'supp_account_delete'){

	

	$id = $_REQUEST['id'];

    $sql = 'delete from ' . $hhs->table('supp_account') . " WHERE `account_id`='" . $id. "'";

	$res = $db->query($sql);

	if($res){

	  show_message('删除成功', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'info');

	}else{

	  show_message('删除失败', $_LANG['back_up_page'], './suppliers.php?act=supp_account_list', 'error');

	}

	

}

if($_REQUEST['act'] =='get_cat_list')

{

    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

	$id =$_REQUEST['id'];

	$type = $_REQUEST['type']+1;

	$list = cat_list($id,$id,false,2,false);

	if($list)

	{

		foreach($list as $key=>$value)

		{

			$lists[$i]= $value;

		}

		if($type!=3)

		{

	  	  $starthtml = "<select name='cat_id_".$type."' id='cat_id'  onchange=\"get_cat_list(this.value,$type)\">";

		}

		else

		{

	  	  $starthtml = "<select name='cat_id_".$type."' id='cat_id' >";

		}

		foreach($list as $key=>$value)

		{

			$starthtml .= "<option value=".$value['id'].">".$value['name']."</option>";

		

		}

		$starthtml .= "</select>";

	}

	$res['html'] = $starthtml;

	$res['type'] = $type;

	

	 die($json->encode($res));

}

elseif($action =='get_cat_piclist')

{

	include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])

	{

		$where .=" and cat_id='$_REQUEST[cat_id]'";	

	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	

	$res['pic_list'] =$smarty->fetch('library/get_pic_list_photo.lbi');

	$res['pages'] =$smarty->fetch('library/pages.lbi');

	die($json->encode($res));

	//echo "[".$temp."]";

    exit;

	

}

elseif($action =='get_pic')

{

	$cat_list =get_pic_cat_list($suppliers_id); 

	$smarty->assign('cat_list',$cat_list);

	

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])

	{

		$where .=" and cat_id='$_REQUEST[cat_id]'";	

	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	$smarty->assign('timestamp',time());

	$smarty->assign('img_id', $_REQUEST['img_id']);
	
	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);

	$smarty->display('suppliers_get_pic.dwt');	

	

}

elseif($action =='get_photo')

{   

	$cat_list =get_pic_cat_list($suppliers_id); 

	$smarty->assign('cat_list',$cat_list);

	

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])

	{

		$where .=" and cat_id='$_REQUEST[cat_id]'";	

	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	$smarty->assign('timestamp',time());

	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);

	$smarty->display('suppliers_get_photo.dwt');	

	

}





elseif($action =='pic_add')

{

	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");

	$smarty->assign('cat_list',$cat_list);

	$smarty->assign('form_act','pic_insert');

	$smarty->assign('timestamp',time());

	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);



	$smarty->display('suppliers_transaction.dwt');

}

elseif($action =='pic_insert')
{
	$pic = $_POST['pics'];
	$cat_id = $_POST['cat_id'];
	if(empty($pic))
	{
		show_message('请先上传图片');
	}
	foreach($pic as $id=>$value)
	{
		$pic_name = $_POST['pic_name'][$id];
		$sql = $db->query("insert into ".$hhs->table('supp_pic_list')." (pic,cat_id,pic_name,suppliers_id) values ('$value','$cat_id','$pic_name','$suppliers_id')");
	}
	show_message('添加成功','返回列表','suppliers.php?act=pic_list');
}

elseif($action =='pic_category_insert')
{
	$cat_name = $_POST['cat_name'];
	$count = $db->getOne("select count(*) from ".$hhs->table('supp_pic_category')." where cat_name='$cat_name' and suppliers_id='$suppliers_id'");
	if($count)
	{
		show_message('该分类名称已存在');
	}

	$sql = $db->query("insert into ".$hhs->table('supp_pic_category')." (cat_name,suppliers_id) values ('$cat_name',$suppliers_id)");
	show_message('添加成功','返回列表','suppliers.php?act=pic_category','info');
}
elseif($action =='pic_category_delete')
{
	$id = $_GET['id'];
	$count = $db->getOne("select count(*) from ".$hhs->table('supp_pic_list')." where cat_id='$id' and suppliers_id='$suppliers_id'");
	if($count)
	{
		show_message('该分类下有图片，请先清空该分类下图片');
	}
	else
	{
		
		$db->query("delete from ".$hhs->table('supp_pic_category')." where id='$id'");	
		show_message('删除成功');
	}
}
elseif($action =='pic_category_edit')
{
	$id = $_REQUEST['id'];
	$rows = $db->getRow("select * from ".$hhs->table('supp_pic_category')." where id='$id'");
	$smarty->assign('rows',$rows);
	$smarty->assign('form_act','pic_category_update');
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='pic_category_update')
{
	$id = $_REQUEST['id'];
	$cat_name = $_REQUEST['cat_name'];
	$sql = $db->query("update ".$hhs->table('supp_pic_category')." set cat_name='$cat_name' where id='$id'");
	show_message('修改成功','返回列表','suppliers.php?act=pic_category','info');
}
elseif($action =='pic_category_add')
{
	$smarty->assign('form_act','pic_category_insert');
	$smarty->display('suppliers_transaction.dwt');	
}
elseif($action =='pic_category')
{
	$list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");
	$smarty->assign('list',$list);
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action=='delete_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
	$pic = $_REQUEST['pic'];
	@unlink(ROOT_PATH.$pic);
	$res['id'] =$_REQUEST['id'];
	die($json->encode($res));
	//echo "[".$temp."]";
    exit;
}
//上传提货单
elseif($action =='update_delivery_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
	$delivery_id = $_REQUEST['delivery_id'];
	$delivery_pic = $_REQUEST['delivery_pic'];
	$sql = $db->query("update ".$hhs->table('delivery_order')." set delivery_pic='$delivery_pic' where delivery_id='$delivery_id'");
	$res['err_msg']=0;
	die($json->encode($res));
	//echo "[".$temp."]";
    exit;
}


elseif($action =='edit_pic')
{
	$id = $_REQUEST['id'];

	$rows = $db->getRow("select * from ".$hhs->table('supp_pic_list')." where id='$id'");

	$smarty->assign('rows',$rows);

	$smarty->assign('form_act','edit_pic_update');

	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");

	$smarty->assign('cat_list',$cat_list);

	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='edit_pic_update')
{
	$data = $_POST;
	$pic = $image->upload_image($_FILES['pic'],'uploads');
	if($pic)
	{
		$data['pic'] = $pic;	
	}
	$db->autoExecute($hhs->table('supp_pic_list'), $data, 'UPDATE', "id = '$data[id]'");
	show_message('修改成功');
	getUrl('suppliers.php?act=pic_list');
}
elseif($action =='drop_delete_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

	$id = $_REQUEST['id'];

	$img = $db->getOne("select pic from ".$hhs->table('supp_pic_list')." where id='$id'");

	@unlink(ROOT_PATH.$img);

	$db->query("delete from ".$hhs->table('supp_pic_list')." where id='$id'");

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])
	{
		$where .=" and cat_id='$_REQUEST[cat_id]'";	
	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	$res['pic_list'] =$smarty->fetch('library/pic_list.lbi');

	$res['pages'] =$smarty->fetch('library/pages.lbi');

	die($json->encode($res));

	//echo "[".$temp."]";

    exit;
}
elseif($action =='pic_list')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where id>0";
	if($_REQUEST['cat_id'])

	{
		$where .=" and cat_id='$_REQUEST[cat_id]'";	
	}
	$smarty->assign('cat_id',$_REQUEST['cat_id']);
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");
	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);
	$pic_list = get_pic_list($suppliers_id, 14, $pager['start']);
	$smarty->assign('pager',  $pager);
    $smarty->assign('pic_list', $pic_list);
	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");
	$smarty->assign('cat_list',$cat_list);
    $smarty->display('suppliers_transaction.dwt');
}
elseif($action =='default')
{
	$info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id='$suppliers_id'");
	$goodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where  is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
	$nogoodsnum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods')." where is_on_sale=0 and is_delete=0 and suppliers_id=".$_SESSION['suppliers_id']);
	$articlenum=$db->getOne("SELECT COUNT(*) FROM " .$hhs->table('article')." where  suppliers_id=".$_SESSION['suppliers_id']);

	
	$smarty->assign('info',$info);
	$smarty->assign('articlenum',$articlenum);
	$smarty->assign('goodsnum',$goodsnum);
	$smarty->assign('nogoodsnum',$nogoodsnum);
	$delivery_count = $db->getOne("SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('delivery_order') . " where suppliers_id='$suppliers_id' and status=2");
	
	$smarty->assign('delivery_count',$delivery_count);
	//已完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=7";
	$receive_count = $db->getOne($sql);
	$smarty->assign('receive_count',$receive_count);
	//未完成结算订单
	$sql="SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . " where suppliers_id='$suppliers_id' and settlement_status=1";
	$unpay_count = $db->getOne($sql);
	$smarty->assign('unpay_count',$unpay_count);
	
	$smarty->display("suppliers_transaction.dwt");	
}
elseif($action =='login')
{
	$smarty->display("suppliers.dwt");
}
elseif($action =='logout')
{
	$user->logout();
	$_SESSION['suppliers_id'] = 0;
	$_SESSION['role_id'] = 0;
	$_SESSION['account_type'] = 0;
	hhs_header("Location:suppliers.php\n");
}



elseif($action  =='my_goods')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where is_delete='0' ";
	
	if($_REQUEST['goods_status'] != '')
	{
		$where .= " and is_on_sale='$_REQUEST[goods_status]' ";	
	}
	if($_REQUEST['is_check'] != '')
	{
		$where .= " and is_check='$_REQUEST[is_check]' ";	
	}
	if($_REQUEST['keywords']!='')
	{
		$where .= " and goods_name like '%%$_REQUEST[keywords]%%'";
	}
	if($_REQUEST['is_promote']!='')
	{
		$where .= " and is_promote= '$_REQUEST[is_promote]'";
	}
	if($_REQUEST['is_season']!='')
	{
		$where .= " and is_season= '$_REQUEST[is_season]'";
	}
	
	$smarty->assign('goods_status',$_REQUEST['goods_status']);
	$smarty->assign('is_check',$_REQUEST['is_check']);
	$smarty->assign('is_promote',$_REQUEST['is_promote']);
	$smarty->assign('is_season',$_REQUEST['is_season']);
	$smarty->assign('is_supp_top',$_REQUEST['is_supp_top']);
	
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods'). " $where and  suppliers_id = '$suppliers_id'");
	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);
	$goods_list = get_suppliers_goods($suppliers_id, $pager['size'], $pager['start'],0);
	
	/* 促销时间倒计时 */
	$time = gmtime();
	foreach($goods_list as $key => $val)
	{
		
		if ($time >= $val['promote_start_date'] && $time <= $val['promote_end_date'])
		{
		   $goods_list[$key]['gmt_end_time']  = $val['promote_end_date'];
		}
		else
		{
		   $goods_list[$key]['gmt_end_time'] = 0;
		}
	}
	
	$smarty->assign('pager',  $pager);
	$smarty->assign('goods_list', $goods_list);
    $smarty->display('suppliers_transaction.dwt');
}
elseif($action  =='my_goods_batch'){
   
    if (!empty($_POST['checkbox']))
    {
        $goods_id = !empty($_POST['checkbox']) ? join(',', $_POST['checkbox']) : 0;
        
        if(isset($_POST['remove'])){
            $sql = "DELETE FROM " . $hhs->table('goods'). " WHERE goods_id " . db_create_in($goods_id);
            $db->query($sql);
        }
        elseif(isset($_POST['up_sale'])){
            $sql = "update " . $hhs->table('goods'). " set is_on_sale=1 WHERE is_check = 1 AND goods_id " . db_create_in($goods_id);
            $db->query($sql);
        }
        elseif(isset($_POST['down_sale'])){
            $sql = "update " . $hhs->table('goods'). " set is_on_sale=0 WHERE is_check = 1 AND goods_id " . db_create_in($goods_id);
            $db->query($sql);
        }
       
        show_message('操作成功','返回列表', "suppliers.php?act=my_goods&page=$page", 'info');  
    }
    else
    {
        show_message("请先选择");
    }
}






elseif($action =='goods_trash')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where is_delete='1' ";
	
	if($_REQUEST['goods_status'] != '')
	{
		$where .= " and is_on_sale='$_REQUEST[goods_status]' ";	
	}
	if($_REQUEST['is_check'] != '')
	{
		$where .= " and is_check='$_REQUEST[is_check]' ";	
	}
	if($_REQUEST['keywords']!='')
	{
		$where .= " and goods_name like '%%$_REQUEST[keywords]%%'";
	}
	if($_REQUEST['is_promote']!='')
	{
		$where .= " and is_promote= '$_REQUEST[is_promote]'";
	}
	if($_REQUEST['is_season']!='')
	{
		$where .= " and is_season= '$_REQUEST[is_season]'";
	}
	
	$smarty->assign('goods_status',$_REQUEST['goods_status']);
	$smarty->assign('is_check',$_REQUEST['is_check']);
	$smarty->assign('is_promote',$_REQUEST['is_promote']);
	$smarty->assign('is_season',$_REQUEST['is_season']);
	$smarty->assign('is_supp_top',$_REQUEST['is_supp_top']);
	
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods'). " $where and  suppliers_id = '$suppliers_id'");
	$pager  = get_pager('suppliers.php', array('act' => $action), $record_count, $page);
	$goods_list = get_suppliers_goods($suppliers_id, $pager['size'], $pager['start'],1);
	
	$smarty->assign('pager',  $pager);
	$smarty->assign('goods_list', $goods_list);
    $smarty->display('suppliers_transaction.dwt');	
}
elseif($action=='is_season'){
	$goods_id       = intval($_POST['id']);
	$is_season         = intval($_POST['val']);
	
	$sql="update ".$hhs->table("goods")." set is_season = ".$is_season.", last_update=".gmtime()." where goods_id=".$goods_id;
	$r=$db->query($sql);
	//echo $sql;exit();
	if ($r>0)
	{
		make_json_result($is_season);
	}
}
elseif($action=='is_supp_top'){
	include_once(ROOT_PATH . 'includes/cls_json.php');
	$json = new JSON;
	$res    = array('content' => '', 'result' => '', 'error' => '');
	$goods_id       = intval($_POST['id']);
	$is_supp_top         = intval($_POST['val']);
	
	$sql="update ".$hhs->table("goods")." set is_supp_top = '".$is_supp_top."', last_update=".gmtime()." where goods_id=".$goods_id;
	$r=$db->query($sql);
//	//echo $sql;exit();
////	if ($r>0)
////	{
////		make_json_result($is_supp_top);
////	}
	$res['error'] =0;
	$res['content'] =$is_supp_top;
	die($json->encode($res));
//		exit;
}
//商品分类管理

else if($action  == 'category'){
	
	/*
	$sql = "select * from ".$hhs->table('goods_category')."where suppliers_id = ".$suppliers_id." order by sort_order asc";
	$cate_list = $db->getAll($sql);
	$smarty->assign('cate_list',$cate_list);
	
	*/
	$cate_list = my_cat_list($suppliers_id,0,0, false);

	$smarty->assign('cate_list',$cate_list);
	$smarty->assign('action','goods_category');
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='brand')
{
	$smarty->assign('supp_brand_list',get_supp_brand_list($suppliers_id));
	$smarty->assign('action','brand');
	$smarty->display('suppliers_transaction.dwt');
}
else if($action  == 'add_brand'){
	$smarty->assign('action','add_brand');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','brand_insert');
	$smarty->display('suppliers_transaction.dwt');
}

//添加商品分类
else if($action  == 'add_cate'){
	$smarty->assign('action','add_cate');
	$smarty->assign('cat_list',     my_cat_list($suppliers_id,0));
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','cate_insert');
	$smarty->display('suppliers_transaction.dwt');
}
else if($action  == 'brand_insert'){
	$brand_name   = $_REQUEST['brand_name'];
	$is_show      = $_REQUEST['is_show'];
	$sort_order   = $_REQUEST['sort_order'];
	$suppliers_id = $_REQUEST['suppliers_id'];
	
	
	     /*处理图片*/
    $brand_logo = basename($image->upload_image($_FILES['brand_logo'],'brandlogo'));

	$sql = "INSERT INTO ". $hhs->table('brand') . " (`brand_name`, `is_show`, `sort_order`,`supp_id`,`brand_logo`) VALUES ('$brand_name', '$is_show', '$sort_order','$suppliers_id','$brand_logo')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=brand', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=brand', 'error');
	}
}
else if($action  == 'cate_insert'){
	$cat_name     = $_REQUEST['cat_name'];
	$is_show      = $_REQUEST['is_show'];
	$sort_order   = $_REQUEST['sort_order'];
	$suppliers_id = $_REQUEST['suppliers_id'];
	$parent_id    = $_REQUEST['parent_id'];
	$sql = "INSERT INTO ". $hhs->table('goods_category') . " (`cat_name`, `is_show`, `sort_order`,`suppliers_id`,`parent_id`) VALUES ('$cat_name', '$is_show', '$sort_order','$suppliers_id','$parent_id')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=category', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=category', 'error');
	}
}
elseif($action =='edit_brand')
{
	$brand_id = $_REQUEST['brand_id'];
	$sql = "select * from ".$hhs->table('brand')."where brand_id = ".$brand_id;
	$brand_info = $db->getRow($sql);
	#print_r($cat_info);
	$smarty->assign('brand_info',$brand_info);
	$smarty->assign('action','edit_brand');
	$smarty->assign('status','update_brand');
	$smarty->display('suppliers_transaction.dwt');
}
elseif($action =='update_brand')
{
	$brand_id    = $_REQUEST['brand_id'];
	$brand_name  = $_REQUEST['brand_name'];
	$is_show     = $_REQUEST['is_show'];
	$sort_order  = $_REQUEST['sort_order'];
	$brand_logo = basename($image->upload_image($_FILES['brand_logo'],'brandlogo'));
	if($brand_logo)
	{
		$sql = 'UPDATE ' . $hhs->table('brand') . " SET `brand_name`='$brand_name', `is_show`='$is_show',`brand_logo`='$brand_logo', `sort_order`='$sort_order'  WHERE `brand_id`='" . $brand_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('brand') . " SET `brand_name`='$brand_name', `is_show`='$is_show', `sort_order`='$sort_order'  WHERE `brand_id`='" . $brand_id. "'";
	}
	
     #echo $sql;die;
	$res = $db->query($sql);
	if($res){
	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=edit_brand&brand_id='.$brand_id, 'info');
	}else{
	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=edit_brand&brand_id='.$brand_id, 'error');
	}
}
//编辑商品分类管理
else if($action  == 'edit_cate'){

	

	$cat_id = $_REQUEST['cat_id'];

	

	$sql = "select * from ".$hhs->table('goods_category')."where cat_id = ".$cat_id;

	

	$cat_info = $db->getRow($sql);

	

	#print_r($cat_info);

	


	$smarty->assign('cat_list',     my_cat_list($suppliers_id,0,$cat_info['parent_id']));
	$smarty->assign('cat_info',$cat_info);

	$smarty->assign('action','edit_cate');

	$smarty->assign('status','cate_update');

	$smarty->display('suppliers_transaction.dwt');

}



else if($action  == 'cate_update'){

	

	$cat_id    = $_REQUEST['cat_id'];

	$cat_name  = $_REQUEST['cat_name'];

	$is_show   = $_REQUEST['is_show'];

	$sort_order= $_REQUEST['sort_order'];

	

	

	$sql = 'UPDATE ' . $hhs->table('goods_category') . " SET `cat_name`='$cat_name', `is_show`='$is_show', `sort_order`='$sort_order'  WHERE `cat_id`='" . $cat_id. "'";

     #echo $sql;die;

	$res = $db->query($sql);

	 

	if($res){

	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=edit_cate&cat_id='.$cat_id, 'info');

	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=edit_cate&cat_id='.$cat_id, 'error');

	}

	

}



else if($action  == 'delete_cate'){

	

	$cat_id    = $_REQUEST['cat_id'];

	
	$count = $db->getOne("select count(*) from ".$hhs->table('goods')." where my_cat_id='$cat_id'");
	
	if($count)
	{
		 show_message('该分类下有商品不能删除', $_LANG['back_up_page'], 'suppliers.php?act=category', 'info');
	}
	
	/* 当前分类下是否有子分类 */
    $cat_count = $db->getOne('SELECT COUNT(*) FROM ' .$hhs->table('goods_category'). " WHERE parent_id='$cat_id'");
	if($cat_count)
	{
		 show_message('该分类下有子分类不能删除', $_LANG['back_up_page'], 'suppliers.php?act=category', 'info');
	}


   $sql = 'delete from ' . $hhs->table('goods_category') . " WHERE `cat_id`='" . $cat_id. "'";

    

	$res = $db->query($sql);

	 

	if($res){

	  show_message('删除成功', $_LANG['back_up_page'], 'suppliers.php?act=category', 'info');

	}else{

	  show_message('删除失败', $_LANG['back_up_page'], 'suppliers.php?act=category', 'error');

	}

	

}











elseif ($action == 'is_registered')

{

    include_once(ROOT_PATH . 'includes/lib_passport.php');



    $username = trim($_GET['username']);

	

	$sql = $db->getOne("select count(*) from ".$hhs->table('agent')." where user_name='$username'");



    if ($sql)

    {

        echo 'false';

    }

    else

    {

        echo 'true';

    }

}

elseif($action =='act_register')

{

	$data = $_POST;

	

	$user_name = $data['username'];	

	

	$count = $db->getOne("select count(*) from ".$hhs->table('agent')." where user_name='$user_name'");

	if($count)

	{

		 show_message('此用户名已存在');

		

	}

	$data['time'] =time();

	$data['password'] = md5($data['password']);

	$data['user_name'] = $data['username'];

	$db->autoExecute($hhs->table('agent'), $data, 'INSERT');

	

	show_message('申请成功，当前未经过申请，我们客户人员一个工作日内申请通过联系您', '返回首页', 'index.php', 'info');

}
elseif($action =='act_login_sub')
{
	$account_name = $_POST['user_name'];
	$account_password = md5($_POST['password']);
	$agent = $db->getRow("select suppliers_id,account_id,account_type from ".$hhs->table('supp_account')." where account_name='$account_name' and account_password ='$account_password' and is_check=1");
	if($agent)
	{
		  
		  $_SESSION['suppliers_id'] = $agent['suppliers_id'];		  
		  $_SESSION['role_id'] = $agent['account_id'];
		  $_SESSION['account_type'] = $agent['account_type'];
		  hhs_header("Location:suppliers.php?act=default\n");
	}
	else
	{
		  $sql = "select suppliers_id,is_check from ".$hhs->table('suppliers')." where user_name='$account_name' and password='$account_password'";
		  #echo $sql;die;
		  $rows = $db->getRow($sql);
		  
		  
		  if($rows)
		  {
			  if($rows['is_check'] != 1)
			  {
				  show_message('未通过审核！');
			  }
			  else
			  {
				 $_SESSION['suppliers_id'] = $rows['suppliers_id'];
				 hhs_header("Location:suppliers.php?act=default\n");
			  
			  }
			  	 
		  }
		  else
		  {
				 show_message('用户名或密码有误');
			  
		  }
		
	}
}
elseif($action =='act_login')
{

	$username = $_POST['user_name'];

	$password = $_POST['password'];

	

	//账号关联

	 if($user->login($username, $password))
	 {

		 $agent = $db->getRow("select suppliers_id,is_check from ".$hhs->table('suppliers')." where user_id='$_SESSION[user_id]'");
	 	 if($agent['is_check'] == 0||$agent['is_check'] == '')
		 {
			 show_message('您当前用户暂未通过审核或您还没提交申请');
			 	$user->logout();
			hhs_header("Location:suppliers.php\n");
		 }
		 else
		 {
			$_SESSION['suppliers_id'] = $agent['suppliers_id'];
			
			$dir = dirname(__FILE__).'/uploads/'.$_SESSION['suppliers_id'].'/';
		
			is_dir($dir) or mkdir($dir, 0777);
			hhs_header("Location:suppliers.php?act=default\n");
		 }

	 }

	 else

	 {

			 show_message('用户名或密码有误');

			 

		 

	 }

}

elseif($action =='act_edit_profile')
{
	$data = $_POST;

	$agent_pic= $image->upload_image($_FILES['agent_pic']);

	$agent_banner= $image->upload_image($_FILES['agent_banner']);

	

	if($agent_pic)

	{

		$data['agent_pic'] = $agent_pic;

	}

	if($agent_banner)

	{

		$data['agent_banner'] = $agent_banner;

	}

	

	

	

	$db->autoExecute($hhs->table('agent'), $data, 'UPDATE', "id = '$suppliers_id'");

	show_message('更新成功');

	

}

elseif($action =='edit_password')

{

	$smarty->display("suppliers_transaction.dwt");

	

}

/* 修改会员密码 */

elseif ($action == 'act_edit_password')
{

    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;

    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
	
	$comfirm_password = isset($_POST['comfirm_password']) ? trim($_POST['comfirm_password']) : '';
	
	
	$agent = $db->getRow("select suppliers_id,account_id,account_type,account_name,account_password from ".$hhs->table('supp_account')." where account_id='".$_SESSION['role_id']."' and account_password ='".md5($old_password)."' and is_check=1");
	
	$supp_info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id ='$_SESSION[suppliers_id]'");
	
	if($agent)
	{
		if (($agent['account_password'] != md5($old_password)))
		{
			show_message('旧密码输入不正确');
		}
		else if($new_password != $comfirm_password)
		{
			show_message('新密码和确认新密码不一致');
		}
		else if($old_password == $new_password)
		{
			show_message('原始密码和新的修改密码不能一样');
		}
	
		$p =  md5($new_password);
	
		$sql="UPDATE ".$hhs->table('supp_account'). "SET account_password='".$p."'  WHERE account_id= '".$_SESSION['role_id']."'";
		
	}
	else if($supp_info)
	{
		
		if (($supp_info && ($supp_info['password'] != md5($old_password))))
		{
			show_message('旧密码输入不正确');
		}
		else if($new_password != $comfirm_password)
		{
			show_message('新密码和确认新密码不一致');
		}
		else if($old_password == $new_password)
		{
			show_message('原始密码和新的修改密码不能一样');
		}
		else
		{
			$p =  md5($new_password);
			$sql="UPDATE ".$hhs->table('suppliers'). "SET password='".$p."'  WHERE suppliers_id= '".$_SESSION['suppliers_id']."'";
		}
	
		
	}
	
	$res = $db->query($sql);
	
	if($res)
	{
		unset($_SESSION['suppliers_id']);
		show_message('密码修改成功','返回登录', 'suppliers.php?act=login', 'info');
	}
	else
	{
		show_message('密码修改失败','返回', 'suppliers.php', 'info');
	}

     

}





//查看用户评论

elseif($action =='user_message')
{

	$list = get_comment_list($suppliers_id);

	#print_r($list);

	$smarty->assign('comment_list',$list);

	$smarty->assign('action','user_message');

	$smarty->display("suppliers_transaction.dwt");

}









//回复用户评论(同时查看评论详情)

if ($action =='reply')

{

	

    $comment_info = array();

    $reply_info   = array();

    $id_value     = array();



    /* 获取评论详细信息并进行字符处理 */

    $sql = "SELECT * FROM " .$hhs->table('comment'). " WHERE comment_id = '$_REQUEST[id]'";

    $comment_info = $db->getRow($sql);

    $comment_info['content']  = str_replace('\r\n', '<br />', htmlspecialchars($comment_info['content']));

    $comment_info['content']  = nl2br(str_replace('\n', '<br />', $comment_info['content']));

    $comment_info['add_time'] = local_date($_CFG['time_format'], $comment_info['add_time']);



    /* 获得评论回复内容 */

    $sql = "SELECT * FROM ".$hhs->table('comment'). " WHERE parent_id = '$_REQUEST[id]'";

    $reply_info = $db->getRow($sql);



    if (empty($reply_info))

    {

        $reply_info['content']  = '';

        $reply_info['add_time'] = '';

    }

    else

    {

        $reply_info['content']  = nl2br(htmlspecialchars($reply_info['content']));

        $reply_info['add_time'] = local_date($_CFG['time_format'], $reply_info['add_time']);

    }

    /* 获取管理员的用户名和Email地址 */

    $sql = "SELECT user_name, email FROM ". $hhs->table('admin_user').

           " WHERE user_id = '$_SESSION[admin_id]'";

    $admin_info = $db->getRow($sql);



    /* 取得评论的对象(文章或者商品) */

    if ($comment_info['comment_type'] == 0)

    {

        $sql = "SELECT goods_name FROM ".$hhs->table('goods').

               " WHERE goods_id = '$comment_info[id_value]'";

        $id_value = $db->getOne($sql);

    }

    else

    {

        $sql = "SELECT title FROM ".$hhs->table('article').

               " WHERE article_id='$comment_info[id_value]'";

        $id_value = $db->getOne($sql);

    }



    /* 模板赋值 */

    $smarty->assign('msg',          $comment_info); //评论信息

    $smarty->assign('admin_info',   $admin_info);   //管理员信息

    $smarty->assign('reply_info',   $reply_info);   //回复的内容

    $smarty->assign('id_value',     $id_value);  //评论的对象

    $smarty->assign('send_fail',   !empty($_REQUEST['send_ok']));



    $smarty->assign('ur_here',      $_LANG['comment_info']);

    $smarty->assign('action_link',  array('text' => $_LANG['05_comment_manage'],

    'href' => 'comment_manage.php?act=list'));



    /* 页面显示 */

    $smarty->assign('action','reply');

	$smarty->display("suppliers_transaction.dwt");

}





if ($action == 'update_comment_status')

{

    if ($_REQUEST['check'] == 'allow')

    {

        /* 允许评论显示 */

        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_REQUEST[id]'";

        $db->query($sql);



        //add_feed($_REQUEST['id'], COMMENT_GOODS);



        /* 清除缓存 */

        clear_cache_files();

        hhs_header("Location:?act=reply&id=$_REQUEST[id]\n");

        exit;

    }

    else

    {

        /* 禁止评论显示 */

        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 0 WHERE comment_id = '$_REQUEST[id]'";

        $db->query($sql);



        /* 清除缓存 */

        clear_cache_files();



        hhs_header("Location:?act=reply&id=$_REQUEST[id]\n");

        exit;

    }

}

/*------------------------------------------------------ */

//-- 处理 回复用户评论

/*------------------------------------------------------ */

if ($action=='action')
{
    /* 获取IP地址 */

    $ip     = real_ip();



    /* 获得评论是否有回复 */

    $sql = "SELECT comment_id, content, parent_id FROM ".$hhs->table('comment').

           " WHERE parent_id = '$_REQUEST[comment_id]'";

    $reply_info = $db->getRow($sql);
    if (!empty($reply_info['content']))
    {
        /* 更新回复的内容 */
        $sql = "UPDATE ".$hhs->table('comment')." SET ".
               "email     = '$_POST[email]', ".
               "user_name = '$_POST[user_name]', ".

               "content   = '$_POST[content]', ".

               "add_time  =  '" . gmtime() . "', ".

               "ip_address= '$ip', ".

               "status    = 0".

               " WHERE comment_id = '".$reply_info['comment_id']."'";

    }
    else
    {

        /* 插入回复的评论内容 */
        $sql = "INSERT INTO ".$hhs->table('comment')." (comment_type, id_value, email, user_name , ".

                    "content, add_time, ip_address, status, parent_id) ".

               "VALUES('$_POST[comment_type]', '$_POST[id_value]','$_POST[email]', " .

                    "'$_SESSION[admin_name]','$_POST[content]','" . gmtime() . "', '$ip', '0', '$_POST[comment_id]')";

    }
    $db->query($sql);
    /* 更新当前的评论状态为已回复并且可以显示此条评论 */
    $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_POST[comment_id]'";

    $db->query($sql);
    /* 清除缓存 */

    clear_cache_files();
    hhs_header("Location:?act=reply&id=$_REQUEST[comment_id]\n");

    exit;

}
/*------------------------------------------------------ */

//-- 删除某一条评论

/*------------------------------------------------------ */

elseif ($action == 'delete_comment')
{
    $id = intval($_GET['id']);
    $sql = "DELETE FROM " .$hhs->table('comment'). " WHERE comment_id = '$id'";
    $res = $db->query($sql);
    if ($res)
    {
        $db->query("DELETE FROM " .$hhs->table('comment'). " WHERE parent_id = '$id'");
    }
    show_message('删除成功', $_LANG['back_up_page'], './suppliers.php?act=user_message', 'info');
    exit;
}

/*------------------------------------------------------ */

//-- 切换商品类型

/*------------------------------------------------------ */

elseif ($action == 'get_attr')
{
    $goods_id   = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
    $goods_type = empty($_GET['goods_type']) ? 0 : intval($_GET['goods_type']);
    $content    = build_attr_html($goods_type, $goods_id);
    make_json_result($content);
}
elseif($action =='add_goods')
{
    /*
	$sql = "select * from ".$hhs->table('category')." where is_show = 1 AND parent_id = 0 ";
	$cat_one  = $db->GetAll($sql);
	$smarty->assign('cat_one',$cat_one);
	*/
	
    /*
	//我的分类
	$sql = "select * from ".$hhs->table('goods_category')."where suppliers_id = ".$suppliers_id;
	$cate_list = $db->getAll($sql);
	$smarty->assign('cate_list',$cate_list);
	$smarty->assign('brand_list', get_brand_list());
	*/
    $smarty->assign('unit_list', get_unit_list());

    $smarty->assign('cities',    get_sitelists());
	$smarty->assign('form_act','insert_goods');
	create_html_editor_xaphp('goods_desc','',$suppliers_id);
	create_html_editor_xaphp1('factory_desc','',$suppliers_id);
	//$_SESSION['suppliers_id']=$suppliers_id;
	
	//$smarty->assign('goods_cat_list', cat_list());
	/*
	$site_lists = $db->getAll("select * from ".$hhs->table('supp_site')." where supp_id ='$suppliers_id'");
	foreach($site_lists as $idx=> $value)
	{
		$site_lists_array[$idx]['id'] = $value['site_id'];

		//$site_lists_array[$idx]['name'] =get_site_name($value['site_id']);
	}*/
	//$smarty->assign('supp_brand_list',get_supp_brand_list($suppliers_id));
	
	//$smarty->assign('supp_companys_list',get_supp_companys_list($suppliers_id));
	//$smarty->assign('site_lists',$site_lists_array);
	//$smarty->assign('goods_cat_list', cat_list('', '',true,1,false));
	$smarty->assign('cat_list',     cat_list(0));

// 运费模板
    $goods['express'] = '[]';

    $smarty->assign('goods', $goods);
    // 获取有效的快递方式
    $shipping_list = $db->getAll('SELECT shipping_id,shipping_name,shipping_code FROM '.$hhs->table('shipping').' WHERE `enabled` = 1');
    // $shipping_lists = array();
    // foreach ($shipping_list as $key => $shipping) {
    //     $shipping_lists[$shipping['shipping_id']] = $shipping['shipping_name'];
    //     unset($shipping);
    // }
    $smarty->assign('shipping_list', $shipping_list);

    // 获取所有的省，市，区
    $all_province = get_region_cache('all_province',1);
    $all_citys    = get_region_cache('all_citys',2);
    $all_regions  = get_region_cache('all_regions',3);

    $smarty->assign('all_province', json_encode($all_province));
    $smarty->assign('all_citys', json_encode($all_citys));
    $smarty->assign('all_regions', json_encode($all_regions));
// end运费模板
// 	
    // 优惠券
    $bonus_list = $db->getAll("SELECT `type_id`,`type_name` from ".$hhs->table('bonus_type')." WHERE free_all=1 and suppliers_id = '".$suppliers_id."' ");
    $smarty->assign('bonus_list', $bonus_list);

	$smarty->assign('goods_type_list', goods_type_list($goods['goods_type']));
	$smarty->display("suppliers_transaction.dwt");
	
}

elseif($action =='insert_goods')
{
	$data = $_POST;

    $express = $_POST['express_items'];
    $express = strip_tags(urldecode($express));
    $express = json_str_iconv(stripslashes($express));    
    $data['express'] = $express;

    $data['is_mall'] = isset($_POST['is_mall']) ? intval($_POST['is_mall']) : 0;
    $data['is_zero'] = isset($_POST['is_zero']) ? intval($_POST['is_zero']) : 0;
    $data['is_team'] = isset($_POST['is_team']) ? intval($_POST['is_team']) : 0;
    $data['is_tejia'] = isset($_POST['is_tejia']) ? intval($_POST['is_tejia']) : 0;
    $data['is_fresh'] = isset($_POST['is_fresh']) ? intval($_POST['is_fresh']) : 0;
    $data['is_best'] = isset($_POST['is_best']) ? intval($_POST['is_best']) : 0;
    $data['is_new'] = isset($_POST['is_new']) ? intval($_POST['is_new']) : 0;
    $data['is_hot'] = isset($_POST['is_hot']) ? intval($_POST['is_hot']) : 0;
	
	if ($_POST['goods_sn'])
    {
        $sql = "SELECT COUNT(*) FROM " . $hhs->table('goods') .
                " WHERE goods_sn = '$_POST[goods_sn]' AND is_delete = 0 AND goods_id <> '$_POST[goods_id]'";
        if ($db->getOne($sql) > 0)
        {
           show_message('商品编号不能重复');
        }
    }

    if(empty($data['goods_name']))
    {
        show_message('商品名称不能为空！');
    }
    if($data['is_team'])
    {
        $data['is_zero'] = 0;
        if(empty($data['team_price']) || floatval($data['team_price']) <= 0.00 )
        {
            show_message('团购价格不能为空！');
        }
        if(empty($data['team_num']) || intval($data['team_num']) <= 0 )
        {
            show_message('参团人数不能为空！');
        }
    }
    if($data['is_zero']){
        $data['is_team']       = 0;
        $_POST['shop_price']   = 0;
        // $_POST['market_price'] = 0;
        $_POST['team_price']   = 0;
        $_POST['team_num']     = 0;
        $_POST['sales_num']    = 0;
        $data['shipping_fee']  = floatval($_POST['shipping_fee']);
    }
    else{
        $data['shipping_fee'] = 0;
        if(empty($data['shop_price']) || floatval($data['shop_price']) <= 0.00 )
        {
            show_message('商品价格不能小于0.01！');
        }
        if(empty($data['market_price']) || floatval($data['market_price']) <= 0.00 )
        {
            show_message('市场价格不能小于0.01！');
        }        
    }


    if(empty($data['goods_img_url'])){
        show_message('商品图片不能为空！');
    }
    if(empty($data['little_img'])){
        show_message('商品小图不能为空！');
    }   

	$original_img   = $_POST['goods_img_url']; // 原始图片
	$goods_img      = $original_img;   // 商品图片
	$goods_thumb = $image->make_thumb( $original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
	$goods_img = $image->make_thumb($original_img , $GLOBALS['_CFG']['image_width'],  $GLOBALS['_CFG']['image_height']);
	$data['original_img'] = $original_img;
	$data['goods_img'] = $original_img;
	$data['goods_thumb'] = $original_img;
	$data['suppliers_id'] =$suppliers_id;
	$data['term_of_validity']=strtotime($data['term_of_validity']);
	$data['cat_id']     =  empty($_POST['cat_id'])     ? '' : intval($_POST['cat_id']);
	
	$data['my_cat_id']  =  empty($_POST['my_cat_id'])  ? '' : intval($_POST['my_cat_id']);
	$data['shop_price'] =  empty($_POST['shop_price']) ? '' : trim($_POST['shop_price']);
	$data['market_price'] =  empty($_POST['market_price']) ? '' : trim($_POST['market_price']);
	$data['goods_brief']=  empty($_POST['goods_brief'])? '' : trim($_POST['goods_brief']);
	$data['last_update'] = time();
	$data['is_on_sale'] =0;
	$data['goods_weight'] =  !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
	$data['unit'] = $_POST['unit'];
	$data['brand_id'] = $_POST['brand_id'];
	$data['companys_id'] = $_POST['companys_id'];
	$data['factory_desc'] =$_POST['factory_desc']; 
	$data['is_shipping'] =$_POST['is_shipping'];
	$data['keywords'] = $_POST['keywords']; 
	$data['is_package'] = $_POST['is_package']; 
	$data['promote_buy_num'] = $_POST['promote_buy_num'];	
	$data['promote_start_date'] = local_strtotime($_POST['promote_start_date']);
	$data['promote_end_date'] = local_strtotime($_POST['promote_end_date']);
	$data['goods_authorization'] =  $image->upload_image($_FILES['goods_authorization'],'business/uploads/'.$suppliers_id);

	$data['team_num'] = intval($_POST['team_num']);
	$data['team_price'] = floatval($_POST['team_price']);
	$data['sales_num'] = intval($_POST['sales_num']);
    $data['guige'] = $_POST['guige']; 
    $data['goods_brief'] = $_POST['goods_brief']; 

    $data['limit_buy_bumber'] = $_POST['limit_buy_bumber']; 
	$data['limit_buy_one'] = $_POST['limit_buy_one']; 

    $data['discount_type'] = isset($_POST['discount_type']) ? $_POST['discount_type'] : 0;
    $data['discount_amount'] = isset($_POST['discount_amount']) ? $_POST['discount_amount'] : 0;


	$data['district_id']  = $_POST['district_id'];
	$data['city_id']  = $_POST['city_id'];
	$data['subscribe']  = $_POST['subscribe'];
    $data['bonus_allowed'] = intval($_POST['bonus_allowed']);
    $data['allow_fenxiao'] = intval($_POST['allow_fenxiao']);
	//判断4级分类取出最后一个分类ID
	//$data['cat_id'] = get_goods_cat($data);

    $db->autoExecute($hhs->table('goods'), $data, 'INSERT');
	$goods_id = $db->insert_id();
	
	foreach($_POST['site_id'] as $key=>$value)
	{
		  $db->query("insert into ".$hhs->table('goods_site')." (site_id,goods_id) values ('$value','$goods_id')");
	}
	
	
	$goods_sn_xaphp = generate_goods_sn($goods_id);
	$sql = $db->query("update ".$hhs->table("goods")." set goods_sn='$goods_sn_xaphp' where goods_id='$goods_id'");
    foreach($_POST['photos'] as $val)
	{	
		if($val!="")
		{
			$gerray['img_url']=$val;
			$gerray['goods_id']=$goods_id;
			$gerray['img_original']=$val;
			$gerray['thumb_url'] = $val;
			$db->autoExecute($hhs->table('goods_gallery'), $gerray, 'INSERT');
		}
	}
	/* 处理属性 */
    if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list'])))
    {
        // 取得原有的属性值
        $goods_attr_list = array();
        $keywords_arr = explode(" ", $_POST['keywords']);
        $keywords_arr = array_flip($keywords_arr);
        if (isset($keywords_arr['']))
        {
            unset($keywords_arr['']);
        }
        $sql = "SELECT attr_id, attr_index FROM " . $hhs->table('attribute') . " WHERE cat_id = '$goods_type'";
        $attr_res = $db->query($sql);
        $attr_list = array();
        while ($row = $db->fetchRow($attr_res))
        {
            $attr_list[$row['attr_id']] = $row['attr_index'];
        }
        $sql = "SELECT g.*, a.attr_type
                FROM " . $hhs->table('goods_attr') . " AS g
                    LEFT JOIN " . $hhs->table('attribute') . " AS a
                        ON a.attr_id = g.attr_id
                WHERE g.goods_id = '$goods_id'";
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $goods_attr_list[$row['attr_id']][$row['attr_value']] = array('sign' => 'delete', 'goods_attr_id' => $row['goods_attr_id']);
        }
        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {
            foreach ($_POST['attr_id_list'] AS $key => $attr_id)
            {
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
                if (!empty($attr_value))
                {
                    if (isset($goods_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    $val_arr = explode(' ', $attr_value);
                    foreach ($val_arr AS $k => $v)
                    {
                        if (!isset($keywords_arr[$v]) && $attr_list[$attr_id] == "1")
                        {
                            $keywords_arr[$v] = $v;
                        }
                    }
                }
            }
        }
        $keywords = join(' ', array_flip($keywords_arr));
        $sql = "UPDATE " .$hhs->table('goods'). " SET keywords = '$keywords' WHERE goods_id = '$goods_id' LIMIT 1";
        $db->query($sql);
        /* 插入、更新、删除数据 */
        foreach ($goods_attr_list as $attr_id => $attr_value_list)
        {
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO " .$hhs->table('goods_attr'). " (attr_id, goods_id, attr_value, attr_price)".
                            "VALUES ('$attr_id', '$goods_id', '$attr_value', '$info[attr_price]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$hhs->table('goods_attr'). " SET attr_price = '$info[attr_price]' WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$hhs->table('goods_attr'). " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                $db->query($sql);
            }
        }
    }

    handle_express($goods_id,$data['express']);
	show_message('保存成功','我的商品列表', 'suppliers.php?act=my_goods', 'info');
}

elseif($action =='add_goods_act')
{
	 show_message('保存成功','我的商品列表', 'suppliers.php?act=goods_list', 'info');
}
elseif($action =='delete_goods')
{
	$id = $_GET['goods_id'];
	$rows = $db->getRow("select * from ".$hhs->table('goods')." where goods_id='$id'");
//	if($rows['is_check']==1||$rows['is_on_sale']==1)
//	{
//	    show_message('商品上架或已经审核后不能删除','返回列表', 'suppliers.php?act=my_goods', 'info');	
//	}
	$sql = $db->query("update   ".$hhs->table('goods')." set is_delete=1 where goods_id='$id'");
    show_message('放入回收站成功','返回列表', 'suppliers.php?act=my_goods', 'info');
}
elseif($action =='operation_goods')
{
	if(!$_POST['goods_id'])
	{
		show_message('请先选择商品');
	}
	if($_POST['restore'])
	{
		foreach($_POST['goods_id'] as $goods_id)
		{
			$sql = $db->query("update   ".$hhs->table('goods')." set is_delete=0 where goods_id='$goods_id'");
		}
		show_message('还原成功','返回列表', 'suppliers.php?act=my_goods', 'info');
	}
	if($_POST['drop'])
	{
		
		foreach($_POST['goods_id'] as $goods_id)
		{
			/* 取得商品信息 */
			$sql = "SELECT goods_id, goods_name, is_delete, is_real, goods_thumb, " .
						"goods_img, original_img " .
					"FROM " . $hhs->table('goods') .
					" WHERE goods_id = '$goods_id'";
			$goods = $db->getRow($sql);
		   
		
		
			/* 删除商品图片和轮播图片 */
			if (!empty($goods['goods_thumb']))
			{
				@unlink('../' . $goods['goods_thumb']);
			}
			if (!empty($goods['goods_img']))
			{
				@unlink('../' . $goods['goods_img']);
			}
			if (!empty($goods['original_img']))
			{
				@unlink('../' . $goods['original_img']);
			}
			/* 删除商品 */
			
			 $sql = "DELETE FROM " . $hhs->table('goods') .
					" WHERE goods_id = '$goods_id'";
			$db->query($sql);
		
			/* 删除商品的货品记录 */
			$sql = "DELETE FROM " . $hhs->table('products') .
					" WHERE goods_id = '$goods_id'";
			$db->query($sql);
		
		
			/* 删除商品相册 */
			$sql = "SELECT img_url, thumb_url, img_original " .
					"FROM " . $hhs->table('goods_gallery') .
					" WHERE goods_id = '$goods_id'";
			$res = $db->query($sql);
			while ($row = $db->fetchRow($res))
			{
				if (!empty($row['img_url']))
				{
					@unlink('../' . $row['img_url']);
				}
				if (!empty($row['thumb_url']))
				{
					@unlink('../' . $row['thumb_url']);
				}
				if (!empty($row['img_original']))
				{
					@unlink('../' . $row['img_original']);
				}
			}
		
			$sql = "DELETE FROM " . $hhs->table('goods_gallery') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
		
			/* 删除相关表记录 */
			$sql = "DELETE FROM " . $hhs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('goods_article') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('goods_attr') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('goods_cat') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('member_price') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('group_goods') . " WHERE parent_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('group_goods') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('link_goods') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('link_goods') . " WHERE link_goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('tag') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('comment') . " WHERE comment_type = 0 AND id_value = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('booking_goods') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
			$sql = "DELETE FROM " . $hhs->table('goods_activity') . " WHERE goods_id = '$goods_id'";
			$db->query($sql);
    	}
		show_message('操作成功','返回列表', 'suppliers.php?act=goods_trash', 'info');
	}
}
elseif($action =='drop_goods')
{
	$goods_id = $_GET['goods_id'];
	
    /* 取得商品信息 */
    $sql = "SELECT goods_id, goods_name, is_delete, is_real, goods_thumb, " .
                "goods_img, original_img " .
            "FROM " . $hhs->table('goods') .
            " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
   


    /* 删除商品图片和轮播图片 */
    if (!empty($goods['goods_thumb']))
    {
        @unlink('../' . $goods['goods_thumb']);
    }
    if (!empty($goods['goods_img']))
    {
        @unlink('../' . $goods['goods_img']);
    }
    if (!empty($goods['original_img']))
    {
        @unlink('../' . $goods['original_img']);
    }
    /* 删除商品 */
	
	 $sql = "DELETE FROM " . $hhs->table('goods') .
            " WHERE goods_id = '$goods_id'";
    $db->query($sql);

    /* 删除商品的货品记录 */
    $sql = "DELETE FROM " . $hhs->table('products') .
            " WHERE goods_id = '$goods_id'";
    $db->query($sql);


    /* 删除商品相册 */
    $sql = "SELECT img_url, thumb_url, img_original " .
            "FROM " . $hhs->table('goods_gallery') .
            " WHERE goods_id = '$goods_id'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        if (!empty($row['img_url']))
        {
            @unlink('../' . $row['img_url']);
        }
        if (!empty($row['thumb_url']))
        {
            @unlink('../' . $row['thumb_url']);
        }
        if (!empty($row['img_original']))
        {
            @unlink('../' . $row['img_original']);
        }
    }

    $sql = "DELETE FROM " . $hhs->table('goods_gallery') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);

    /* 删除相关表记录 */
    $sql = "DELETE FROM " . $hhs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('goods_article') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('goods_attr') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('goods_cat') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('member_price') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('group_goods') . " WHERE parent_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('group_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('link_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('link_goods') . " WHERE link_goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('tag') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('comment') . " WHERE comment_type = 0 AND id_value = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('booking_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $hhs->table('goods_activity') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
	
	 show_message('删除成功','返回列表', 'suppliers.php?act=my_goods', 'info');
	
}
elseif($action =='restore_goods')
{
	$id = $_GET['goods_id'];

	$sql = $db->query("update   ".$hhs->table('goods')." set is_delete=0 where goods_id='$id'");
    show_message('还原成功','返回列表', 'suppliers.php?act=my_goods', 'info');
}

//商品导入
elseif($action =='goods_import')
{

	$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
	/*
	//获得授权商家
	$company_list =get_supp_companys_list($suppliers_id);
	$smarty->assign('company_list',$company_list);
	$company_id_array = array();
	foreach($company_list as $idx=>$value)
	{
		$company_id_array[] = $value['companys_id'];
	}
	$company_ids = join(",",$company_id_array);
	
	
	if(empty($company_ids))
	{
		$company_ids = 0;
	}*/
	
	
	$where = " where is_delete =0";
	
	/*
	if(isset($_REQUEST['companys_id']))
	{
		$where .= " and companys_id ='$_REQUEST[companys_id]'";
	}
	else
	{
		$where .= " and companys_id in($company_ids)";
	}*/
	
	
/*
	$record_count = $db->getOne("select count(*) from ".$hhs->table('companys_goods')." $where");
	$arr['act']='goods_import';
	$arr['companys_id']=$_REQUEST[companys_id];
	$pager  = get_pager('suppliers.php', $arr, $record_count, $page);
	$sql_list = $db->getAll("select * from ".$hhs->table('companys_goods')." $where order by goods_id desc limit $pager[start],10");
*/
	$smarty->assign('sql_list',$sql_list);
	$smarty->assign('pager',$pager);
	$smarty->display("suppliers_transaction.dwt");	
}
elseif($action =='goods_import_act')
{
	$goods_ids = $_POST['goods_id'];
	if($_POST['goods_id']='')
	{
	show_message('请先选择要导入的商品');		
	}
	
	foreach($goods_ids as $idx=>$values)
	{
		$value = $db->getRow("select * from ".$hhs->table('companys_goods')." where goods_id='$values'");
		$sql = $db->query("insert into ".$hhs->table('goods')." (is_on_sale,goods_name,cat_id,brand_id,goods_img,goods_thumb,original_img,goods_desc,companys_id,suppliers_id,goods_type) values ('0','$value[goods_name]','$value[cat_id]','$value[brand_id]','$value[goods_img]','$value[goods_thumb]','$value[original_img]','$value[goods_desc]','$value[companys_id]','$suppliers_id','$value[goods_type]')");
		$new_goods_id = $db->insert_id();
		$goods_sn_xaphp = generate_goods_sn($new_goods_id);
	    $sql = $db->query("update ".$hhs->table("goods")." set goods_sn='$goods_sn_xaphp' where goods_id='$new_goods_id'");
		$gerray_list= $db->getAll("select img_url,img_desc,thumb_url,img_original 	 from ".$hhs->table("company_goods_gallery")." where goods_id='$value[goods_id]'");
		foreach($gerray_list as $key=>$v)
		{
			$v['goods_id'] = $new_goods_id;
			$db->autoExecute($hhs->table('goods_gallery'), $v, 'INSERT');
		}
		//商品熟悉
		$goods_attr = $db->getAll("select * from ".$hhs->table("companys_goods_attr")." where goods_id='$value[goods_id]'");
		
		
		foreach($goods_attr as $ke=>$f)
		{
			$f['goods_id'] = $new_goods_id;
			unset($f['goods_attr_id']);
			$db->autoExecute($hhs->table('goods_attr'), $f, 'INSERT');
		}
		
		
	}
	show_message('导入成功','返回列表进行编辑', 'suppliers.php?act=my_goods', 'info');	

	
}

//编辑商品
elseif($action =='edit_goods')
{
    /*
	//我的分类
	$sql = "select * from ".$hhs->table('goods_category')."where suppliers_id = ".$suppliers_id;
	$cate_list = $db->getAll($sql);
	$smarty->assign('cate_list',$cate_list);
	*/
    $smarty->assign('unit_list', get_unit_list());
	$goods_id = $_GET['goods_id'];
	$goods = $db->getRow("select * from ".$hhs->table('goods')." where goods_id='$goods_id'");
    /* 根据商品重量的单位重新计算 */
    if ($goods['goods_weight'] > 0)
    {
        $goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
        $smarty->assign('weight_unit', $is_add ? '1' : ($goods['goods_weight'] >= 1 ? '1' : '0.001'));
    }    
	/*
	//1,2,3,4级分类
	$cat_arr=array('cat_one','cat_two','cat_three','cat_four');
	$each_cat=get_each_cat($goods['cat_id']);
	$smarty->assign("each_cat", $each_cat);
	foreach($each_cat as $k=>$v){
	    $sql = "select * from ".$hhs->table('category')." where is_show = 1 AND parent_id =  ".$v;
	    $$cat_arr[$k] = $db->GetAll($sql);
	    if(!empty($$cat_arr[$k])){
	        $smarty->assign($cat_arr[$k], $$cat_arr[$k]);
	    }
	}
	*/
    $smarty->assign('cities',    get_sitelists());
	$smarty->assign('district_list',    get_regions(3,$goods['city_id']));	
	$goods['term_of_validity']=date('Y-m-d',$goods['term_of_validity']);
	$goods['promote_start_date']=local_date('Y-m-d H:i:s',$goods['promote_start_date']);
	$goods['promote_end_date']=local_date('Y-m-d H:i:s',$goods['promote_end_date']);
	$goods['cat_name']=$db->GetOne("select cat_name from ".$hhs->table('category')."where cat_id = ".$goods['cat_id']." ");
	include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件
	create_html_editor_xaphp('goods_desc',$goods['goods_desc'],$suppliers_id);
	
	create_html_editor_xaphp1('factory_desc',$goods['factory_desc'],$suppliers_id);
	//$smarty->assign('supp_companys_list',get_supp_companys_list($suppliers_id));
	//$smarty->assign('goods_cat_list', cat_list('', '',true,1,false));
	//$smarty->assign('brand_list', get_brand_list());
 	//$smarty->assign('cat_list1', cat_list(0, $goods['cat_id'],true,3,false));
	$smarty->assign('goods_type_list', goods_type_list($goods['goods_type']));
	$smarty->assign('goods_attr_html', build_attr_html($goods['goods_type'], $goods['goods_id']));
	//$smarty->assign('supp_brand_list',get_supp_brand_list($suppliers_id));
    if(empty($goods['express'])) $goods['express'] = '[]';
	$smarty->assign('goods',$goods);
	/*
	$data_site_lists = $db->getAll("select * from ".$hhs->table('goods_site')." where goods_id ='$goods[goods_id]'");
	foreach($data_site_lists as $id=>$v)
	{
		$data_site_lists_array[] = $v['site_id'];
	}
	$site_lists = $db->getAll("select * from ".$hhs->table('supp_site')." where supp_id ='$suppliers_id'");
	foreach($site_lists as $idx=> $value)
	{
		if(in_array($value['site_id'],$data_site_lists_array))
		{
			$checked = 'checked';	
		}
		else
		{
			$checked ='';	
		}
		$site_html .= '<span style="margin-right:10px"><input value="'.$value['site_id'].'" '.$checked.' name="site_id[]" type="checkbox">&nbsp;'.get_site_name($value['site_id']).'</span>';
	}
	$smarty->assign('site_html',$site_html);
	*/
	 /* 图片列表 */
 
     $sql = "SELECT * FROM " . $hhs->table('goods_gallery') . " WHERE goods_id = '$goods_id'";
     $img_list = $db->getAll($sql);
	//print_r($img_list);exit;
	$smarty->assign('img_list',$img_list);
	$smarty->assign('form_act','update_goods');
    $smarty->assign('cat_list', cat_list(0, $goods['cat_id']));

// 运费模板
    // 获取有效的快递方式
    $shipping_list = $db->getAll('SELECT shipping_id,shipping_name,shipping_code FROM '.$hhs->table('shipping').' WHERE `enabled` = 1');
    // $shipping_lists = array();
    // foreach ($shipping_list as $key => $shipping) {
    //     $shipping_lists[$shipping['shipping_id']] = $shipping['shipping_name'];
    //     unset($shipping);
    // }
    $smarty->assign('shipping_list', $shipping_list);

    // 获取所有的省，市，区
    $all_province = get_region_cache('all_province',1);
    $all_citys    = get_region_cache('all_citys',2);
    $all_regions  = get_region_cache('all_regions',3);

    $smarty->assign('all_province', json_encode($all_province));
    $smarty->assign('all_citys', json_encode($all_citys));
    $smarty->assign('all_regions', json_encode($all_regions));
// end运费模板 

    $bonus_list = $db->getAll("SELECT `type_id`,`type_name` from ".$hhs->table('bonus_type')." WHERE free_all=1 and suppliers_id = '".$suppliers_id."' ");
    $smarty->assign('bonus_list', $bonus_list);

//     
	$smarty->display("suppliers_transaction.dwt");	
}
elseif($action =='update_goods')
{
	$data = $_POST;

    $express = $_POST['express_items'];
    $express = strip_tags(urldecode($express));
    $express = json_str_iconv(stripslashes($express));    
    $data['express'] = $express;

    $data['is_mall'] = isset($_POST['is_mall']) ? intval($_POST['is_mall']) : 0;
    $data['is_zero'] = isset($_POST['is_zero']) ? intval($_POST['is_zero']) : 0;
    $data['is_team'] = isset($_POST['is_team']) ? intval($_POST['is_team']) : 0;
    $data['is_tejia'] = isset($_POST['is_tejia']) ? intval($_POST['is_tejia']) : 0;
    $data['is_fresh'] = isset($_POST['is_fresh']) ? intval($_POST['is_fresh']) : 0;
    $data['is_best'] = isset($_POST['is_best']) ? intval($_POST['is_best']) : 0;
    $data['is_new'] = isset($_POST['is_new']) ? intval($_POST['is_new']) : 0;
    $data['is_hot'] = isset($_POST['is_hot']) ? intval($_POST['is_hot']) : 0;

    if(empty($data['goods_name']))
    {
        show_message('商品名称不能为空！');
    }
    if($data['is_team'])
    {
        $data['is_zero'] = 0;
        if(empty($data['team_price']) || floatval($data['team_price']) <= 0.00 )
        {
            show_message('团购价格不能为空！');
        }
        if(empty($data['team_num']) || intval($data['team_num']) <= 0 )
        {
            show_message('参团人数不能为空！');
        }
    }
    if($data['is_zero']){
        $data['is_team']      = 0;
        $data['team_price']   = 0;
        $data['shop_price']   = 0;
        $data['market_price'] = 0;
        $data['team_num']     = 0;
        $data['sales_num']    = 0;
        $data['shipping_fee'] = floatval($_POST['shipping_fee']);
        if($data['shipping_fee'] == 0.00){
            show_message('请设置0元购邮费！');
        }
    }
    else{
        $data['shipping_fee'] = 0;
        if(empty($data['shop_price']) || floatval($data['shop_price']) <= 0.00 )
        {
            show_message('商品价格不能小于0.01！');
        }
        if(empty($data['market_price']) || floatval($data['market_price']) <= 0.00 )
        {
            show_message('市场价格不能小于0.01！');
        }        
    }


    if(empty($data['goods_img_url'])){
        show_message('商品图片不能为空！');
    }
    if(empty($data['little_img'])){
        show_message('商品小图不能为空！');
    }  
    if(empty($data['goods_img_url'])){
        show_message('商品图片不能为空！');
    }
    if(empty($data['little_img'])){
        show_message('商品小图不能为空！');
    }  
    
	//查看商品是否为未通过
	$is_check = $db->getOne("select is_check from ".$hhs->table('goods')." where goods_id = ".$data[goods_id]." ");
	if($is_check == 2)
	{
		$data['is_check'] = 0;
	}

	$original_img   = $data['goods_img_url']; // 原始图片
	$goods_img      = $original_img;   // 商品图片
	$goods_thumb = $image->make_thumb($original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
	$data['original_img'] = $original_img;
	
	$data['promote_start_date'] = local_strtotime($data['promote_start_date']);
	$data['promote_end_date'] = local_strtotime($data['promote_end_date']);
	$data['goods_img'] = $goods_img;
	$data['goods_thumb'] = $original_img;

	$data['cat_id'] =  empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);

	$data['my_cat_id'] =  empty($_POST['my_cat_id']) ? '' : intval($_POST['my_cat_id']);

	$data['last_update'] =time();

	$data['term_of_validity']=strtotime($data['term_of_validity']);
	$data['market_price'] = $_POST['market_price'];
	$data['companys_id'] = $_POST['companys_id'];
	$data['is_on_sale'] =0;
	$data['is_check'] =0;
	$data['goods_weight'] = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
	$data['factory_desc'] =$_POST['factory_desc']; 
	$data['unit'] = $_POST['unit'];
	$goods_type = $data['goods_type'];
	$data['is_shipping'] = $_POST['is_shipping'];
	$data['keywords'] = $_POST['keywords'];
	$data['is_package'] = $_POST['is_package'];
	$data['promote_buy_num'] = $_POST['promote_buy_num'];	
	$goods_id = $data['goods_id'];
    $data['guige'] = $_POST['guige']; 
	$data['goods_brief'] = $_POST['goods_brief']; 

    $data['limit_buy_bumber'] = $_POST['limit_buy_bumber']; 
    $data['limit_buy_one'] = $_POST['limit_buy_one']; 
	
	$data['city_id'] = $_POST['city_id']; 
	$data['district_id'] = $_POST['district_id']; 
    $data['subscribe'] = intval($_POST['subscribe']);
    $data['bonus_allowed'] = intval($_POST['bonus_allowed']);
	$data['allow_fenxiao'] = intval($_POST['allow_fenxiao']);
	//判断4级分类取出最后一个分类ID
	//$data['cat_id'] = get_goods_cat($data);
	
    $data['discount_type'] = isset($_POST['discount_type']) ? $_POST['discount_type'] : 0;
    $data['discount_amount'] = isset($_POST['discount_amount']) ? $_POST['discount_amount'] : 0;
	
	$goods_authorization = $image->upload_image($_FILES['goods_authorization'],'business/uploads/'.$suppliers_id);

	$pw_img = $image->upload_image($_FILES['pw_img']);

	if($goods_authorization)
	{
		$data['goods_authorization'] =$goods_authorization;
	}
	if($pw_img)
	{
		$data['pw_img'] =$pw_img;
	}
	$db->autoExecute($hhs->table('goods'), $data, 'UPDATE', "goods_id = '$data[goods_id]'");
	
	/*
	$sql = $db->query("delete from ".$hhs->table('goods_site')." where goods_id='$data[goods_id]'");
	foreach($_POST['site_id'] as $keys=>$values)
	{
		$db->query("insert into ".$hhs->table('goods_site')." (goods_id,site_id) values ('$data[goods_id]','$values')");
	}*/
	foreach($_POST['photos'] as $val)
	{
		if($val!="")
		{
			$gerray['img_url']=$val;
			$gerray['goods_id']=$data['goods_id'];
			$gerray['img_original']=$val;
			$gerray['thumb_url'] = $val;
			//print_r($gerray);
			//exit;
			$db->autoExecute($hhs->table('goods_gallery'), $gerray, 'INSERT');
		}
	}	/* 处理属性 */

    if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list'])))
	{

        // 取得原有的属性值
		$goods_attr_list = array();
        $keywords_arr = explode(" ", $_POST['keywords']);
        $keywords_arr = array_flip($keywords_arr);
        if (isset($keywords_arr['']))
        {
            unset($keywords_arr['']);
        }
        $sql = "SELECT attr_id, attr_index FROM " . $hhs->table('attribute') . " WHERE cat_id = '$goods_type'";
        $attr_res = $db->query($sql);
        $attr_list = array();
        while ($row = $db->fetchRow($attr_res))
        {
            $attr_list[$row['attr_id']] = $row['attr_index'];
        }
        $sql = "SELECT g.*, a.attr_type
                FROM " . $hhs->table('goods_attr') . " AS g
                    LEFT JOIN " . $hhs->table('attribute') . " AS a
                        ON a.attr_id = g.attr_id
                WHERE g.goods_id = '$goods_id'";
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $goods_attr_list[$row['attr_id']][$row['attr_value']] = array('sign' => 'delete', 'goods_attr_id' => $row['goods_attr_id']);
        }
        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {

            foreach ($_POST['attr_id_list'] AS $key => $attr_id)

            {

                $attr_value = $_POST['attr_value_list'][$key];

                $attr_price = $_POST['attr_price_list'][$key];

                if (!empty($attr_value))
                {
                    if (isset($goods_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    $val_arr = explode(' ', $attr_value);
                    foreach ($val_arr AS $k => $v)
                    {
                        if (!isset($keywords_arr[$v]) && $attr_list[$attr_id] == "1")
                        {
                            $keywords_arr[$v] = $v;
                        }
                    }
                }
            }
        }
        $keywords = join(' ', array_flip($keywords_arr));
        $sql = "UPDATE " .$hhs->table('goods'). " SET keywords = '$keywords' WHERE goods_id = '$goods_id' LIMIT 1";
        $db->query($sql);
        /* 插入、更新、删除数据 */
        foreach ($goods_attr_list as $attr_id => $attr_value_list)
        {
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO " .$hhs->table('goods_attr'). " (attr_id, goods_id, attr_value, attr_price)".
                            "VALUES ('$attr_id', '$goods_id', '$attr_value', '$info[attr_price]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$hhs->table('goods_attr'). " SET attr_price = '$info[attr_price]' WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$hhs->table('goods_attr'). " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                $db->query($sql);
            }
        }
    }
    clear_all_files();

    handle_express($goods_id,$data['express']);    
    
	show_message('编辑成功','我的商品列表', 'suppliers.php?act=my_goods', 'info');	
}

/*------------------------------------------------------ */

//-- 显示图片

/*------------------------------------------------------ */
elseif ($action == 'show_image')
{
    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $img_url = $_GET['img_url'];
    }
    else
    {
        if (strpos($_GET['img_url'], 'http://') === 0)
        {
            $img_url = $_GET['img_url'];
        }
        else
        {
            $img_url = '../' . $_GET['img_url'];
        }
    }
	#print_r($img_url);die;
    $smarty->assign('img_url', $img_url);
    $smarty->display('suppliers_show_image.html');
}
/*------------------------------------------------------ */

//-- 删除图片

/*------------------------------------------------------ */
elseif ($action == 'del_image')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
    $img_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    /* 删除图片文件 */
    $sql = "SELECT img_url, thumb_url, img_original " .
            " FROM " . $GLOBALS['hhs']->table('goods_gallery') .
            " WHERE img_id = '$img_id'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row['img_url'] != '' && is_file($row['img_url']))
    {
        @unlink($row['img_url']);
    }
    if ($row['thumb_url'] != '' && is_file($row['thumb_url']))
    {
        @unlink($row['thumb_url']);
    }
    if ($row['img_original'] != '' && is_file( $row['img_original']))
    {
        @unlink($row['img_original']);
    }
    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('goods_gallery') . " WHERE img_id = '$img_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
	$res['id'] =$img_id;
	die($json->encode($res));
	//echo "[".$temp."]";

    exit;
}
//----------------------------------------------------------------------------------------
elseif($action=='supp_info')
{ 
    $supplier=$db->getRow("SELECT * FROM " .$hhs->table('suppliers')."where suppliers_id='$suppliers_id'");
	$regions=$db->getAll("SELECT * FROM " .$hhs->table('region'));
	// $smarty->assign('provinces',    get_regions(1, '1'));
     $smarty->assign('cities',    get_sitelists());
	 $smarty->assign('district_list',    get_regions(3,$supplier['city_id']));
    $supplier["term_validity"]=date("Y-m-d",strtotime($supplier["term_validity"]));

    $smarty->assign("regions_list",$regions);
	
	create_html_editor_xaphp('suppliers_desc',$supplier['suppliers_desc'],$suppliers_id);

	$smarty->assign("supp_list",$supplier);
	$photo_list = $regions=$db->getAll("SELECT * FROM " .$hhs->table('supp_photo')."where supp_id = ".$suppliers_id);
	$smarty->assign("photo_list",$photo_list);

	$smarty->display("suppliers_transaction.dwt");
}


elseif($action =='factoryauthorized')
{
	$sql = "select * from ".$hhs->table('suppliers_factoryauthorized')."where supp_id = ".$suppliers_id." order by id asc";
	$ad_list = $db->getAll($sql);
	foreach($ad_list as $idx=>$v)
	{
		$ad_list[$idx]['add_time'] = local_date("Y-m-d",$V['add_time']);
	}
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','factoryauthorized');
	$smarty->display('suppliers_info.dwt');
}

elseif($action =='trademark')
{
	$sql = "select * from ".$hhs->table('suppliers_trademark')."where supp_id = ".$suppliers_id." order by id asc";
	$ad_list = $db->getAll($sql);
	foreach($ad_list as $idx=>$v)
	{
		$ad_list[$idx]['add_time'] = local_date("Y-m-d",$V['add_time']);
	}
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','trademark');
	$smarty->display('suppliers_info.dwt');
}

elseif($action =='add_trademark')
{
	$smarty->assign('action','add_trademark');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','trademark_insert');
	$smarty->display('suppliers_info.dwt');;
}
else if($action =='edit_trademark')
{
	$id = $_REQUEST['id'];
	$sql = "select * from ".$hhs->table('suppliers_trademark')."where id = ".$id;
	$ad_info = $db->getRow($sql);
	$smarty->assign('ad_info',$ad_info);
	$smarty->assign('action','edit_trademark');
	$smarty->assign('status','trademark_update');
	$smarty->display('suppliers_info.dwt');

}
else if($action  == 'trademark_insert')
{
	$name = $_REQUEST['name'];
	$time = gmtime();
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$sql = "INSERT INTO ". $hhs->table('suppliers_trademark') . " (`name`, `supp_id`,`pic`,`add_time`) VALUES ('$name','$suppliers_id','$photo_file','$time')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'error');
	}
}
else if($action  == 'trademark_update')
{
	$name = $_REQUEST['name'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$photo_id = $_REQUEST['photo_id'];
	if($photo_file)
	{
		$old_photo_file = $db->getOne("select pic from ".$hhs->table('suppliers_trademark')." where `id`='" . $photo_id. "'");
		if($old_photo_file)
		{
			@unlink(ROOT_PATH . $old_photo_file);
		}
		$sql = 'UPDATE ' . $hhs->table('suppliers_trademark') . " SET `name`='$name',`pic`='$photo_file'  WHERE `id`='" . $photo_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('suppliers_trademark') . " SET `name`='$name'  WHERE `id`='" . $photo_id. "'";
	}
	$res = $db->query($sql);
	if($res){
	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'info');
	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'error');
	}
}
else if($action  == 'trademark_delete')
{
	$id = $_REQUEST['id'];
	$old_photo_file = $db->getOne("select pic from ".$hhs->table('suppliers_trademark')." where id='" . $photo_id. "'");
	if($old_photo_file)
	{
		@unlink(ROOT_PATH . $old_photo_file);
	}
    $sql = 'delete from ' . $hhs->table('suppliers_trademark') . " WHERE `id`='" . $id. "'";
	$res = $db->query($sql);
	if($res){
	  show_message('删除成功', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'info');
	}else{
	  show_message('删除失败', $_LANG['back_up_page'], './suppliers.php?act=trademark', 'error');
	}
}
//我的资质
else if($action =='my_qualification')
{
	$sql = "select * from ".$hhs->table('suppliers')."where suppliers_id = ".$suppliers_id." ";
	$supp_list = $db->getRow($sql);
	$smarty->assign('supp_list',$supp_list);
	$smarty->assign('action','my_qualification');
	$smarty->display('suppliers_info.dwt');

}




elseif($action =='ad')
{
	$sql = "select * from ".$hhs->table('supp_photo')."where supp_id = ".$suppliers_id." order by photo_id asc";

	$ad_list = $db->getAll($sql);
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','ad');
	$smarty->display('suppliers_info.dwt');
}

elseif($action =='factoryauthorized')
{
	$sql = "select * from ".$hhs->table('suppliers_factoryauthorized')."where supp_id = ".$suppliers_id." order by id asc";
	$ad_list = $db->getAll($sql);
	foreach($ad_list as $idx=>$v)
	{
		$ad_list[$idx]['add_time'] = local_date("Y-m-d",$V['add_time']);
	}
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','factoryauthorized');
	$smarty->display('suppliers_info.dwt');
}
/*
elseif($action =='trademark')
{
	$sql = "select * from ".$hhs->table('suppliers_trademark')."where supp_id = ".$suppliers_id." order by id asc";
	$ad_list = $db->getAll($sql);
	foreach($ad_list as $idx=>$v)
	{
		$ad_list[$idx]['add_time'] = local_date("Y-m-d",$V['add_time']);
	}
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','trademark');
	$smarty->display('suppliers_info.dwt');
}
*/

else if($action =='add_factoryauthorized')
{
	$smarty->assign('action','add_factoryauthorized');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','factoryauthorized_insert');
	$smarty->display('suppliers_info.dwt');
}

else if($action =='add_ad')
{
	$smarty->assign('action','add_ad');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','ad_insert');
	$smarty->display('suppliers_info.dwt');
}
else if($action =='edit_factoryauthorized')
{
	$id = $_REQUEST['id'];
	$sql = "select * from ".$hhs->table('suppliers_factoryauthorized')."where id = ".$id;
	$ad_info = $db->getRow($sql);
	$smarty->assign('ad_info',$ad_info);
	$smarty->assign('action','edit_factoryauthorized');
	$smarty->assign('status','factoryauthorized_update');
	$smarty->display('suppliers_info.dwt');

}
else if($action =='edit_ad')
{
	$id = $_REQUEST['id'];
	$sql = "select * from ".$hhs->table('supp_photo')."where photo_id = ".$id;
	$ad_info = $db->getRow($sql);
	$smarty->assign('ad_info',$ad_info);
	$smarty->assign('action','edit_ad');
	$smarty->assign('status','ad_update');
	$smarty->display('suppliers_info.dwt');

}
else if($action  == 'factoryauthorized_insert')
{
	$name = $_REQUEST['name'];
	$time = gmtime();
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$sql = "INSERT INTO ". $hhs->table('suppliers_factoryauthorized') . " (`name`, `supp_id`,`pic`,`add_time`) VALUES ('$name','$suppliers_id','$photo_file','$time')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'error');
	}
}
else if($action  == 'ad_insert')
{
	$name = $_REQUEST['name'];
	$link         = $_REQUEST['link'];
	$sort_order    = $_REQUEST['sort_order'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$sql = "INSERT INTO ". $hhs->table('supp_photo') . " (`name`,`link`, `sort_order`, `supp_id`,`photo_file`) VALUES ('$name','$link', '$sort_order', '$suppliers_id','$photo_file')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './suppliers.php?act=ad', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './suppliers.php?act=ad', 'error');
	}
}
else if($action  == 'factoryauthorized_update')
{
	$name = $_REQUEST['name'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$photo_id = $_REQUEST['photo_id'];
	if($photo_file)
	{
		$old_photo_file = $db->getOne("select pic from ".$hhs->table('suppliers_factoryauthorized')." where `id`='" . $photo_id. "'");
		if($old_photo_file)
		{
			@unlink(ROOT_PATH . $old_photo_file);
		}
		$sql = 'UPDATE ' . $hhs->table('suppliers_factoryauthorized') . " SET `name`='$name',`photo_file`='$photo_file'  WHERE `id`='" . $photo_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('suppliers_factoryauthorized') . " SET `name`='$name'  WHERE `id`='" . $photo_id. "'";
	}
	$res = $db->query($sql);
	if($res){
	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'info');
	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'error');
	}
}
else if($action  == 'ad_update')
{
	$name = $_REQUEST['name'];
	$link     = $_REQUEST['link'];
	$sort_order   = $_REQUEST['sort_order'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$photo_id = $_REQUEST['photo_id'];
	if($photo_file)
	{
		$old_photo_file = $db->getOne("select photo_file from ".$hhs->table('supp_photo')." where `photo_id`='" . $photo_id. "'");
		if($old_photo_file)
		{
			@unlink(ROOT_PATH . $old_photo_file);
		}
		$sql = 'UPDATE ' . $hhs->table('supp_photo') . " SET `name`='$name',`link`='$link' ,`photo_file`='$photo_file', `sort_order`='$sort_order'  WHERE `photo_id`='" . $photo_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('supp_photo') . " SET `name`='$name',`link`='$link' , `sort_order`='$sort_order'  WHERE `photo_id`='" . $photo_id. "'";
	}
	$res = $db->query($sql);
	if($res){
	   show_message('编辑成功', $_LANG['back_up_page'], './suppliers.php?act=ad', 'info');
	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './suppliers.php?act=ad', 'error');
	}
}
else if($action  == 'factoryauthorized_delete')
{
	$id = $_REQUEST['id'];
	$old_photo_file = $db->getOne("select pic from ".$hhs->table('suppliers_factoryauthorized')." where id='" . $photo_id. "'");
	if($old_photo_file)
	{
		@unlink(ROOT_PATH . $old_photo_file);
	}
    $sql = 'delete from ' . $hhs->table('suppliers_factoryauthorized') . " WHERE `id`='" . $id. "'";
	$res = $db->query($sql);
	if($res){
	  show_message('删除成功', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'info');
	}else{
	  show_message('删除失败', $_LANG['back_up_page'], './suppliers.php?act=factoryauthorized', 'error');
	}
}
else if($action  == 'ad_delete')
{
	$id = $_REQUEST['id'];
	$old_photo_file = $db->getOne("select photo_file from ".$hhs->table('supp_photo')." where photo_id='" . $photo_id. "'");
	if($old_photo_file)
	{
		@unlink(ROOT_PATH . $old_photo_file);
	}
    $sql = 'delete from ' . $hhs->table('supp_photo') . " WHERE `photo_id`='" . $id. "'";
	$res = $db->query($sql);
	if($res){
	  show_message('删除成功', $_LANG['back_up_page'], './suppliers.php?act=ad', 'info');
	}else{
	  show_message('删除失败', $_LANG['back_up_page'], './suppliers.php?act=ad', 'error');
	}
}


elseif($action=='supp_update')
{    
	   $show_photo = $_FILES['show_photo'];
       $business_scope = trim($_POST['business_scope']);
		$province_id = trim($_POST['province_id']);
		$city_id = trim($_POST['city_id']);
		$district_id = trim($_POST['district_id']);
		$address = trim($_POST['address']);
		$email = trim($_POST['email']);				$shopowner_phone = trim($_POST['shopowner_phone']);		
		$phone = trim($_POST['phone']);
		$qq = trim($_POST['qq']);
		$suppliers_desc = trim($_POST['suppliers_desc']);
		$url_name = trim($_POST['url_name']);
		$suppliers_id = trim($_POST['suppliers_id']);
		$map_info = $_POST['map_info'];
		$supp_type = trim($_POST['supp_type']);
		//商品LOGO
		$supp_logo   = $_FILES['supp_logo'];
		$supp_banner = $_FILES['supp_banner'];
		//检测为个人入住还是商户入驻
		$supp_type      = trim($_REQUEST['supp_type']);
		//商品LOGO
		$supp_logo        = $image->upload_image($supp_logo);
		$supp_banner     = $image->upload_image($_FILES['supp_banner']);
		//身份证照片（双面）
		$shenfen_phone    = $image->upload_image($shenfen_phone);
		//营业执照照片（双面）
		$business_license = $image->upload_image($_FILES['business_license'],'business_file');
		$business_scope = $image->upload_image($_FILES['business_scope'],'business_file');
		$cards = $image->upload_image($_FILES['cards'],'business_file');
		$certificate = $image->upload_image($_FILES['certificate'],'business_file');
		$real_name =$_POST['real_name'];
		$email1 = $_POST['email1'];
		$phone1 = $_POST['phone1'];
		
		$longitude = $_POST['longitude'];
		$latitude = $_POST['latitude'];
		
		
				if(!empty($url_name)){
    		$count = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where url_name='$url_name' and suppliers_id<>'$suppliers_id'");    
    		if($count)     
    		{    
    			show_message('输入的二级域名系统中已存在，请重新输入');    
    		}		}
		
	   $where="";
	   if(!empty($supp_logo)){
		$where.=" ,supp_logo='".$supp_logo."'";
	   }
	   if(!empty($show_photo))
	   {
			$where.=" ,show_photo='".$show_photo."'";
	   }
	   if(!empty($business_scope)){
		$where.=" ,business_scope='".$business_scope."'";
	   }
	   if(!empty($business_license)){
		$where.=" ,business_license='".$business_license."'";
	   }
	   if(!empty($cards)){
		$where.=" ,cards='".$cards."'";
	   }
	   if(!empty($certificate)){
		$where.=" ,certificate='".$certificate."'";
	   }
	   if(!empty($supp_banner)){
		$where.=" ,supp_banner='".$supp_banner."'";
	   }
	   
	   

	$sql = "UPDATE " .$hhs->table('suppliers'). " SET longitude='".$longitude."',latitude='".$latitude."',business_scope = '".$business_scope."',email1='".$email1."', phone1='".$phone1."', url_name='".$url_name."',province_id = '".$province_id."', city_id = '".$city_id."',district_id = '".$district_id."',address = '".$address."',email = '".$email."',phone = '".$phone."',  shopowner_phone = '".$shopowner_phone."' ,map_info='$map_info',suppliers_desc = '".$suppliers_desc."',qq = '".$qq."',real_name = '".$real_name."',identification_card = '".$identification_card."',company_name = '".$company_name."',business_license_number = '".$business_license_number."'".$where." WHERE suppliers_id = '".$suppliers_id."'";
	$res = $db->query($sql);
	if($res){
		show_message('编辑成功', $_LANG['back_up_page'], 'suppliers.php?act=supp_info', 'info');
	}else{
		show_message('编辑失败', $_LANG['back_up_page'], 'suppliers.php?act=supp_info', 'error');
	}
	/*

		$data["term_validity"]=strtotime($date["term_validity"]);
	move_uploaded_file($_FILES["business_license"]["tmp_name"],"images/".$_FILES["business_license"]["name"]);

		move_uploaded_file($_FILES["enterprise_license"]["tmp_name"],"images/".$_FILES["enterprise_license"]["name"]);

		$data["business_license"]="images/".$_FILES["business_license"]["name"];

		$data["enterprise_license"]="images/".$_FILES["enterprise_license"]["name"];

		

		$db->autoExecute($hhs->table('suppliers'), $data, 'UPDATE', "suppliers_id = '$data[suppliers_id]'");

	    show_message('更新成功','企业资料', 'suppliers.php?act=supp_info', 'info');	

		*/

}
/*------------------------------------------------------ */
//-- 删除图片
/*------------------------------------------------------ */

elseif ($action == 'drop_image')
{
    $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);
    /* 删除图片文件 */
    $sql = "SELECT * " .

            " FROM " . $GLOBALS['hhs']->table('supp_photo') .

            " WHERE photo_id = '$img_id'";

    $row = $GLOBALS['db']->getRow($sql);
    if ($row['photo_file'] != '' && is_file('../' . $row['photo_file']))
    {

        @unlink('../' . $row['photo_file']);

    }
    /* 删除数据 */

    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('supp_photo') . " WHERE photo_id = '$img_id' LIMIT 1";

    $GLOBALS['db']->query($sql);
    clear_cache_files();

    make_json_result($img_id);

}






/*------------------------------------------------------ */
//-- 操作订单状态（载入页面）
/*------------------------------------------------------ */

elseif (@$_REQUEST['act'] == 'operate')
{
    $order_id = '';
    /* 检查权限 */

    
    /* 取得订单id（可能是多个，多个sn）和操作备注（可能没有） */
    if(isset($_REQUEST['order_id']))
    {
        $order_id= $_REQUEST['order_id'];
    }
    $batch          = isset($_REQUEST['batch']); // 是否批处理
    $action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

    /* 确认 */
    if (isset($_POST['confirm']))
    {
        $require_note   = false;
        $action         = $_LANG['op_confirm'];
        $operation      = 'confirm';
    }
    /* 付款 */
    elseif (isset($_POST['pay']))
    {
        $require_note   = $_CFG['order_pay_note'] == 1;
        $action         = $_LANG['op_pay'];
        $operation      = 'pay';
    }
    /* 未付款 */
    elseif (isset($_POST['unpay']))
    {

        $require_note   = $_CFG['order_unpay_note'] == 1;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
        $action         = $_LANG['op_unpay'];
        $operation      = 'unpay';
    }
    /* 配货 */
    elseif (isset($_POST['prepare']))
    {
        $require_note   = false;
        $action         = $_LANG['op_prepare'];
        $operation      = 'prepare';
    }
    /* 分单 */
    elseif (isset($_POST['ship']))
    {

        $order_id = intval(trim($order_id));
        $action_note = trim($action_note);

        /* 查询：根据订单id查询订单信息 */
        if (!empty($order_id))
        {
            $order = order_info($order_id);
        }
        else
        {
            die('order does not exist');
        }

        

        /* 查询：如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
        $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
        $agency_id = $db->getOne($sql);
        if ($agency_id > 0)
        {
            if ($order['agency_id'] != $agency_id)
            {
                sys_msg($_LANG['priv_error'], 0);
            }
        }

        /* 查询：取得用户名 */
        if ($order['user_id'] > 0)
        {
            $user = user_info($order['user_id']);
            if (!empty($user))
            {
                $order['user_name'] = $user['user_name'];
            }
        }

        /* 查询：取得区域名 */
        $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                    "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                "FROM " . $hhs->table('order_info') . " AS o " .
                    "LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .
                    "LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .
                    "LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .
                    "LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .
                "WHERE o.order_id = '$order[order_id]'";
        $order['region'] = $db->getOne($sql);

        /* 查询：其他处理 */
        $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
        $order['invoice_no']    = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];

        /* 查询：是否保价 */
        $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

        /* 查询：是否存在实体商品 */
        $exist_real_goods = exist_real_goods($order_id);

        /* 查询：取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' =>$order['order_sn']));

        $attr = $_goods['attr'];
        $goods_list = $_goods['goods_list'];
        unset($_goods);

        /* 查询：商品已发货数量 此单可发货数量 */
        if ($goods_list)
        {
            foreach ($goods_list as $key=>$goods_value)
            {
                if (!$goods_value['goods_id'])
                {
                    continue;
                }

                /* 超级礼包 */
                if (($goods_value['extension_code'] == 'package_buy') && (count($goods_value['package_goods_list']) > 0))
                {
                    $goods_list[$key]['package_goods_list'] = package_goods($goods_value['package_goods_list'], $goods_value['goods_number'], $goods_value['order_id'], $goods_value['extension_code'], $goods_value['goods_id']);

                    foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value)
                    {
                        $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = '';
                        /* 使用库存 是否缺货 */
                        if ($pg_value['storage'] <= 0 && $_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP)
                        {
                            $goods_list[$key]['package_goods_list'][$pg_key]['send'] = $_LANG['act_good_vacancy'];
                            $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                        }
                        /* 将已经全部发货的商品设置为只读 */
                        elseif ($pg_value['send'] <= 0)
                        {
                            $goods_list[$key]['package_goods_list'][$pg_key]['send'] = $_LANG['act_good_delivery'];
                            $goods_list[$key]['package_goods_list'][$pg_key]['readonly'] = 'readonly="readonly"';
                        }
                    }
                }
                else
                {
                    $goods_list[$key]['sended'] = $goods_value['send_number'];
                    $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];

                    $goods_list[$key]['readonly'] = '';
                    /* 是否缺货 */
                    if ($goods_value['storage'] <= 0 && $_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP)
                    {
                        $goods_list[$key]['send'] = $_LANG['act_good_vacancy'];
                        $goods_list[$key]['readonly'] = 'readonly="readonly"';
                    }
                    elseif ($goods_list[$key]['send'] <= 0)
                    {
                        $goods_list[$key]['send'] = $_LANG['act_good_delivery'];
                        $goods_list[$key]['readonly'] = 'readonly="readonly"';
                    }
                }
            }
        }

        /* 模板赋值 */
        $smarty->assign('order', $order);
        $smarty->assign('exist_real_goods', $exist_real_goods);
        $smarty->assign('goods_attr', $attr);
        $smarty->assign('goods_list', $goods_list);
        $smarty->assign('order_id', $order_id); // 订单id
        $smarty->assign('operation', 'split'); // 订单id
        $smarty->assign('action_note', $action_note); // 发货操作信息
		//子账号信息
		$sql = "select * from ".$hhs->table('supp_account')."where suppliers_id = ".$suppliers_id."  and account_type=1   and is_check=1 order by sort_order asc";
	    $account_list = $db->getAll($sql);

		$smarty->assign('account_list', $account_list);
      
        /* 显示模板 */
        $smarty->assign('ur_here', $_LANG['order_operate'] . $_LANG['op_split']);
        assign_query_info();
        $smarty->display('order_delivery_info.dwt');
        exit;
    }
	    /* 退货 */
    elseif (isset($_POST['return']))
    {
        $require_note   = $_CFG['order_return_note'] == 1;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
        $action         = $_LANG['op_return'];
        $operation      = 'return';

    }
 /* 收货确认 */
    elseif (isset($_POST['receive']))
    {
        $require_note   = $_CFG['order_receive_note'] == 1;
        $action         = $_LANG['op_receive'];
        $operation      = 'receive';
    }	    /* 未发货 */
    elseif (isset($_POST['unship']))
    {
        /* 检查权限 */
        admin_priv('order_ss_edit');

        $require_note   = $_CFG['order_unship_note'] == 1;
        $action         = $_LANG['op_unship'];
        $operation      = 'unship';
    }


    /* 未发货 */
    elseif (isset($_POST['unship']))
    {
        /* 检查权限 */
        admin_priv('order_ss_edit');

        $require_note   = $_CFG['order_unship_note'] == 1;
        $action         = $_LANG['op_unship'];
        $operation      = 'unship';
    }
    /* 收货确认 */
    elseif (isset($_POST['receive']))
    {
        $require_note   = $_CFG['order_receive_note'] == 1;
        $action         = $_LANG['op_receive'];
        $operation      = 'receive';
    }
    /* 取消 */
    elseif (isset($_POST['cancel']))
    {
        $require_note   = $_CFG['order_cancel_note'] == 1;
        $action         = $_LANG['op_cancel'];
        $operation      = 'cancel';
        $show_cancel_note   = true;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
    }
    /* 无效 */
    elseif (isset($_POST['invalid']))
    {
        $require_note   = $_CFG['order_invalid_note'] == 1;
        $action         = $_LANG['op_invalid'];
        $operation      = 'invalid';
    }
    /* 售后 */
    elseif (isset($_POST['after_service']))
    {
        $require_note   = true;
        $action         = $_LANG['op_after_service'];
        $operation      = 'after_service';
    }
    /* 退货 */
    elseif (isset($_POST['return']))
    {
        $require_note   = $_CFG['order_return_note'] == 1;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
        $action         = $_LANG['op_return'];
        $operation      = 'return';

    }
    /* 指派 */
    elseif (isset($_POST['assign']))
    {
        /* 取得参数 */
        $new_agency_id  = isset($_POST['agency_id']) ? intval($_POST['agency_id']) : 0;
        if ($new_agency_id == 0)
        {
            sys_msg($_LANG['js_languages']['pls_select_agency']);
        }

        /* 查询订单信息 */
        $order = order_info($order_id);

        /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
        $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
        $admin_agency_id = $db->getOne($sql);
        if ($admin_agency_id > 0)
        {
            if ($order['agency_id'] != $admin_agency_id)
            {
                sys_msg($_LANG['priv_error']);
            }
        }

        /* 修改订单相关所属的办事处 */
        if ($new_agency_id != $order['agency_id'])
        {
            $query_array = array('order_info', // 更改订单表的供货商ID
                                 'delivery_order', // 更改订单的发货单供货商ID
                                 'back_order'// 更改订单的退货单供货商ID
            );
            foreach ($query_array as $value)
            {
                $db->query("UPDATE " . $hhs->table($value) . " SET agency_id = '$new_agency_id' " .
                    "WHERE order_id = '$order_id'");

            }
        }

        /* 操作成功 */
        $links[] = array('href' => 'order.php?act=list&' . list_link_postfix(), 'text' => $_LANG['02_order_list']);
        sys_msg($_LANG['act_ok'], 0, $links);
    }
   
   
   
    /* 批量打印订单 */
    elseif (isset($_POST['print']))
    {
        if (empty($_POST['order_id']))
        {
            sys_msg($_LANG['pls_select_order']);
        }

        /* 赋值公用信息 */
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('shop_url',     $hhs->url());
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['admin_name']);

        $html = '';
        $order_sn_list = explode(',', $_POST['order_id']);
        foreach ($order_sn_list as $order_sn)
        {
            /* 取得订单信息 */
            $order = order_info(0, $order_sn);
            if (empty($order))
            {
                continue;
            }

            /* 根据订单是否完成检查权限 */
            if (order_finished($order))
            {
                if (!admin_priv('order_view_finished', '', false))
                {
                    continue;
                }
            }
            else
            {
                if (!admin_priv('order_view', '', false))
                {
                    continue;
                }
            }

            /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
            $sql = "SELECT agency_id FROM " . $hhs->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
            $agency_id = $db->getOne($sql);
            if ($agency_id > 0)
            {
                if ($order['agency_id'] != $agency_id)
                {
                    continue;
                }
            }

            /* 取得用户名 */
            if ($order['user_id'] > 0)
            {
                $user = user_info($order['user_id']);
                if (!empty($user))
                {
                    $order['user_name'] = $user['user_name'];
                }
            }

            /* 取得区域名 */
            $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                    "FROM " . $hhs->table('order_info') . " AS o " .
                        "LEFT JOIN " . $hhs->table('region') . " AS c ON o.country = c.region_id " .
                        "LEFT JOIN " . $hhs->table('region') . " AS p ON o.province = p.region_id " .
                        "LEFT JOIN " . $hhs->table('region') . " AS t ON o.city = t.region_id " .
                        "LEFT JOIN " . $hhs->table('region') . " AS d ON o.district = d.region_id " .
                    "WHERE o.order_id = '$order[order_id]'";
            $order['region'] = $db->getOne($sql);

            /* 其他处理 */
            $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
            $order['pay_time']      = $order['pay_time'] > 0 ?
                local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][PS_UNPAYED];
            $order['shipping_time'] = $order['shipping_time'] > 0 ?
                local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][SS_UNSHIPPED];
            $order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
            $order['invoice_no']    = $order['shipping_status'] == SS_UNSHIPPED || $order['shipping_status'] == SS_PREPARING ? $_LANG['ss'][SS_UNSHIPPED] : $order['invoice_no'];

            /* 此订单的发货备注(此订单的最后一条操作记录) */
            $sql = "SELECT action_note FROM " . $hhs->table('order_action').
                   " WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";
            $order['invoice_note'] = $db->getOne($sql);

            /* 参数赋值：订单 */
            $smarty->assign('order', $order);

            /* 取得订单商品 */
            $goods_list = array();
            $goods_attr = array();
            $sql = "SELECT o.*, g.goods_number AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name " .
                    "FROM " . $hhs->table('order_goods') . " AS o ".
                    "LEFT JOIN " . $hhs->table('goods') . " AS g ON o.goods_id = g.goods_id " .
                    "LEFT JOIN " . $hhs->table('brand') . " AS b ON g.brand_id = b.brand_id " .
                    "WHERE o.order_id = '$order[order_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                /* 虚拟商品支持 */
                if ($row['is_real'] == 0)
                {
                    /* 取得语言项 */
                    $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $_CFG['lang'] . '.php';
                    if (file_exists($filename))
                    {
                        include_once($filename);
                        if (!empty($_LANG[$row['extension_code'].'_link']))
                        {
                            $row['goods_name'] = $row['goods_name'] . sprintf($_LANG[$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);
                        }
                    }
                }

                $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
                $row['formated_goods_price']    = price_format($row['goods_price']);

                $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
                $goods_list[] = $row;
            }

            $attr = array();
            $arr  = array();
            foreach ($goods_attr AS $index => $array_val)
            {
                foreach ($array_val AS $value)
                {
                    $arr = explode(':', $value);//以 : 号将属性拆开
                    $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
                }
            }

            $smarty->assign('goods_attr', $attr);
            $smarty->assign('goods_list', $goods_list);

            $smarty->template_dir = '../' . DATA_DIR;
            
            $html .= $smarty->fetch('order_print.html') .
                '<div style="PAGE-BREAK-AFTER:always"></div>';
        }

        echo $html;
        exit;
    }
    /* 去发货 */
    elseif (isset($_POST['to_delivery']))
    {
        /**
         * 跳转问题
         */
        if(isset($_POST['shipping_id']) && intval($_POST['shipping_id']) == offlineID)
        {
            $url = 'suppliers.php?act=delivery_list&order_sn='.$_REQUEST['order_sn'];
        }
        else
        {
            $url = 'suppliers.php?act=shipping_delivery_list&order_sn='.$_REQUEST['order_sn'];
        }
        // $url = 'suppliers.php?act=delivery_list&order_sn='.$_REQUEST['order_sn'];

        hhs_header("Location: $url\n");
        exit;
    }

    /* 直接处理还是跳到详细页面
    if (($require_note && $action_note == '') || isset($show_invoice_no) || isset($show_refund))
    {

        // 模板赋值 
        $smarty->assign('require_note', $require_note); // 是否要求填写备注
        $smarty->assign('action_note', $action_note);   // 备注
        $smarty->assign('show_cancel_note', isset($show_cancel_note)); // 是否显示取消原因
        $smarty->assign('show_invoice_no', isset($show_invoice_no)); // 是否显示发货单号
        $smarty->assign('show_refund', isset($show_refund)); // 是否显示退款
        $smarty->assign('anonymous', isset($anonymous) ? $anonymous : true); // 是否匿名
        $smarty->assign('order_id', $order_id); // 订单id
        $smarty->assign('batch', $batch);   // 是否批处理
        $smarty->assign('operation', $operation); // 操作

        // 显示模板 
        $smarty->assign('ur_here', $_LANG['order_operate'] . $action);
        assign_query_info();
        $smarty->display('order_operate.htm');
    }
    else
    { */
        /* 直接处理 */
        if (!$batch)
        {
            /* 一个订单 */
            hhs_header("Location: suppliers.php?act=operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
        else

        {
            /* 多个订单 */
            hhs_header("Location: suppliers.php?act=batch_operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
    //}
}
/*------------------------------------------------------ */
//-- 操作订单状态（处理提交）
/*------------------------------------------------------ */

elseif (@$_REQUEST['act'] == 'operate_post')
{

    /* 取得参数 */
    $order_id   = intval(trim($_REQUEST['order_id']));        // 订单id
    $operation  = $_REQUEST['operation'];       // 订单操作

    /* 查询订单信息 */
    $order = order_info($order_id);

    /* 检查能否操作 */
    $operable_list = operable_list($order);
    if (!isset($operable_list[$operation]))
    {
        die('Hacking attempt');
    }

    /* 取得备注信息 */
    $action_note = $_REQUEST['action_note'];

    /* 初始化提示信息 */
    $msg = '';
    /* 配货 */
    if ('prepare' == $operation)
    {
        /* 标记订单为已确认，配货中 */
        if ($order['order_status'] != OS_CONFIRMED)
        {
            $arr['order_status']    = OS_CONFIRMED;
            $arr['confirm_time']    = gmtime();
        }
        $arr['shipping_status']     = SS_PREPARING;
        update_order($order_id, $arr);

        /* 记录log */
        order_action($order['order_sn'], OS_CONFIRMED, SS_PREPARING, $order['pay_status'], $action_note);

        /* 清除缓存 */
        clear_cache_files();
    }
    /* 分单确认 */
    elseif ('split' == $operation)
    {
        /* 定义当前时间 */
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳

        /* 获取表单提交数据 */
        array_walk($_REQUEST['delivery'], 'trim_array_walk');
        $delivery = $_REQUEST['delivery'];
        array_walk($_REQUEST['send_number'], 'trim_array_walk');
        array_walk($_REQUEST['send_number'], 'intval_array_walk');
        $send_number = $_REQUEST['send_number'];
        $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
        $delivery['user_id']  = intval($delivery['user_id']);
        $delivery['country']  = intval($delivery['country']);
        $delivery['province'] = intval($delivery['province']);
        $delivery['city']     = intval($delivery['city']);
        $delivery['district'] = intval($delivery['district']);
        $delivery['agency_id']    = intval($delivery['agency_id']);
        $delivery['insure_fee']   = floatval($delivery['insure_fee']);
        $delivery['shipping_fee'] = floatval($delivery['shipping_fee']);

        /* 订单是否已全部分单检查 */
        if ($order['order_status'] == OS_SPLITED)
        {
            /* 操作失败 */
            $links = 'suppliers.php?act=goods_order';
            show_message(sprintf($_LANG['order_splited_sms'], $order['order_sn'],
                    $_LANG['os'][OS_SPLITED], $_LANG['ss'][SS_SHIPPED_ING], $GLOBALS['_CFG']['shop_name']), '订单列表', $links);
        }

        /* 取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
        $goods_list = $_goods['goods_list'];

        /* 检查此单发货数量填写是否正确 合并计算相同商品和货品 */
        if (!empty($send_number) && !empty($goods_list))
        {
            $goods_no_package = array();
            foreach ($goods_list as $key => $value)
            {
                /* 去除 此单发货数量 等于 0 的商品 */
                if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list']))
                {
                    // 如果是货品则键值为商品ID与货品ID的组合
                    $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);

                    // 统计此单商品总发货数 合并计算相同ID商品或货品的发货数
                    if (empty($goods_no_package[$_key]))
                    {
                        $goods_no_package[$_key] = $send_number[$value['rec_id']];
                    }
                    else
                    {
                        $goods_no_package[$_key] += $send_number[$value['rec_id']];
                    }

                    //去除
                    if ($send_number[$value['rec_id']] <= 0)
                    {
                        unset($send_number[$value['rec_id']], $goods_list[$key]);
                        continue;
                    }
                }
                else
                {
                    /* 组合超值礼包信息 */
                    $goods_list[$key]['package_goods_list'] = package_goods($value['package_goods_list'], $value['goods_number'], $value['order_id'], $value['extension_code'], $value['goods_id']);

                    /* 超值礼包 */
                    foreach ($value['package_goods_list'] as $pg_key => $pg_value)
                    {
                        // 如果是货品则键值为商品ID与货品ID的组合
                        $_key = empty($pg_value['product_id']) ? $pg_value['goods_id'] : ($pg_value['goods_id'] . '_' . $pg_value['product_id']);

                        //统计此单商品总发货数 合并计算相同ID产品的发货数
                        if (empty($goods_no_package[$_key]))
                        {
                            $goods_no_package[$_key] = $send_number[$value['rec_id']][$pg_value['g_p']];
                        }
                        //否则已经存在此键值
                        else
                        {
                            $goods_no_package[$_key] += $send_number[$value['rec_id']][$pg_value['g_p']];
                        }

                        //去除
                        if ($send_number[$value['rec_id']][$pg_value['g_p']] <= 0)
                        {
                            unset($send_number[$value['rec_id']][$pg_value['g_p']], $goods_list[$key]['package_goods_list'][$pg_key]);
                        }
                    }

                    if (count($goods_list[$key]['package_goods_list']) <= 0)
                    {
                        unset($send_number[$value['rec_id']], $goods_list[$key]);
                        continue;
                    }
                }

                /* 发货数量与总量不符 */
                if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list']))
                {
                    $sended = order_delivery_num($order_id, $value['goods_id'], $value['product_id']);
                    if (($value['goods_number'] - $sended - $send_number[$value['rec_id']]) < 0)
                    {
                        /* 操作失败 */
                        $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
                        sys_msg($_LANG['act_ship_num'], 1, $links);
                    }
                }
                else
                {
                    /* 超值礼包 */
                    foreach ($goods_list[$key]['package_goods_list'] as $pg_key => $pg_value)
                    {
                        if (($pg_value['order_send_number'] - $pg_value['sended'] - $send_number[$value['rec_id']][$pg_value['g_p']]) < 0)
                        {
                            /* 操作失败 */
                            $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
                            sys_msg($_LANG['act_ship_num'], 1, $links);
                        }
                    }
                }
            }
        }
        /* 对上一步处理结果进行判断 兼容 上一步判断为假情况的处理 */
        if (empty($send_number) || empty($goods_list))
        {
            /* 操作失败 */
            $links = 'suppliers.php?act=order_info&order_id=' . $order_id;
            show_message($_LANG['act_false'], '返回', $links);
        }

        /* 检查此单发货商品库存缺货情况 */
        /* $goods_list已经过处理 超值礼包中商品库存已取得 */
        $virtual_goods = array();
        $package_virtual_goods = array();
        foreach ($goods_list as $key => $value)
        {
            // 商品（超值礼包）
            if ($value['extension_code'] == 'package_buy')
            {
                foreach ($value['package_goods_list'] as $pg_key => $pg_value)
                {
                    if ($pg_value['goods_number'] < $goods_no_package[$pg_value['g_p']] && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP) || ($_CFG['use_storage'] == '0' && $pg_value['is_real'] == 0)))
                    {
                        /* 操作失败 */
                        $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
                        sys_msg(sprintf($_LANG['act_good_vacancy'], $pg_value['goods_name']), 1, $links);
                    }

                    /* 商品（超值礼包） 虚拟商品列表 package_virtual_goods*/
                    if ($pg_value['is_real'] == 0)
                    {
                        $package_virtual_goods[] = array(
                                       'goods_id' => $pg_value['goods_id'],
                                       'goods_name' => $pg_value['goods_name'],
                                       'num' => $send_number[$value['rec_id']][$pg_value['g_p']]
                                       );
                    }
                }
            }
            // 商品（虚货）
            elseif ($value['extension_code'] == 'virtual_card' || $value['is_real'] == 0)
            {
                $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('virtual_card') . " WHERE goods_id = '" . $value['goods_id'] . "' AND is_saled = 0 ";
                $num = $GLOBALS['db']->GetOne($sql);
                if (($num < $goods_no_package[$value['goods_id']]) && !($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE))
                {
                    /* 操作失败 */
                    $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
                    sys_msg(sprintf($GLOBALS['_LANG']['virtual_card_oos'] . '【' . $value['goods_name'] . '】'), 1, $links);
                }

                /* 虚拟商品列表 virtual_card*/
                if ($value['extension_code'] == 'virtual_card')
                {
                    $virtual_goods[$value['extension_code']][] = array('goods_id' => $value['goods_id'], 'goods_name' => $value['goods_name'], 'num' => $send_number[$value['rec_id']]);
                }
            }
            // 商品（实货）、（货品）
            else
            {
                //如果是货品则键值为商品ID与货品ID的组合
                $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);

                /* （实货） */
                if (empty($value['product_id']))
                {
                    $sql = "SELECT goods_number FROM " . $GLOBALS['hhs']->table('goods') . " WHERE goods_id = '" . $value['goods_id'] . "' LIMIT 0,1";
                }
                /* （货品） */
                else
                {
                    $sql = "SELECT product_number
                            FROM " . $GLOBALS['hhs']->table('products') ."
                            WHERE goods_id = '" . $value['goods_id'] . "'
                            AND product_id =  '" . $value['product_id'] . "'
                            LIMIT 0,1";
                }
                $num = $GLOBALS['db']->GetOne($sql);

                if (($num < $goods_no_package[$_key]) && $_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == SDT_SHIP)
                {
                    /* 操作失败 */
                    $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
                    sys_msg(sprintf($_LANG['act_good_vacancy'], $value['goods_name']), 1, $links);
                }
            }
        }

        /* 生成发货单 */
        /* 获取发货单号和流水号 */
        $delivery['delivery_sn'] = get_delivery_sn();
        $delivery_sn = $delivery['delivery_sn'];
        /* 获取当前操作员 */
        $delivery['action_user'] = $_SESSION['admin_name'];
        /* 获取发货单生成时间 */
      //  $delivery['update_time'] = GMTIME_UTC;
        $delivery_time = $delivery['update_time'];
        $sql ="select add_time from ". $GLOBALS['hhs']->table('order_info') ." WHERE order_sn = '" . $delivery['order_sn'] . "'";
        $delivery['add_time'] =  $GLOBALS['db']->GetOne($sql);
        /* 获取发货单所属供应商 */
        $delivery['suppliers_id'] = $suppliers_id;
		
		$delivery['supp_account_id'] = $_REQUEST['supp_account_id'];
		
        /* 设置默认值 */
        $delivery['status'] = 2; // 正常
        $delivery['order_id'] = $order_id;
        /* 过滤字段项 */
        $filter_fileds = array(
                               'order_sn','supp_account_id', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
                               'consignee', 'address', 'country', 'province', 'city', 'district', 'sign_building',
                               'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
                               'agency_id', 'delivery_sn', 'action_user', 'update_time',
                               'suppliers_id', 'status', 'order_id', 'shipping_name'
                               );
        $_delivery = array();
        foreach ($filter_fileds as $value)
        {
            $_delivery[$value] = $delivery[$value];
        }
        /* 发货单入库 */
        $query = $db->autoExecute($hhs->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
        $delivery_id = $db->insert_id();

        if ($delivery_id)
        {
            $delivery_goods = array();

            //发货单商品入库
            if (!empty($goods_list))
            {
                foreach ($goods_list as $value)
                {
                    // 商品（实货）（虚货）
                    if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card' || $value['extension_code'] == 'team_goods')
                    {
                        $delivery_goods = array('delivery_id' => $delivery_id,
                                                'goods_id' => $value['goods_id'],
                                                'product_id' => $value['product_id'],
                                                'product_sn' => $value['product_sn'],
                                                'goods_id' => $value['goods_id'],
                                                'goods_name' => addslashes($value['goods_name']),
                                                'brand_name' => addslashes($value['brand_name']),
                                                'goods_sn' => $value['goods_sn'],
                                                'send_number' => $send_number[$value['rec_id']],
                                                'parent_id' => 0,
                                                'is_real' => $value['is_real'],
                                                'goods_attr' => addslashes($value['goods_attr'])
                                                );

                        /* 如果是货品 */
                        if (!empty($value['product_id']))
                        {
                            $delivery_goods['product_id'] = $value['product_id'];
                        }

                        $query = $db->autoExecute($hhs->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
                    }
                    // 商品（超值礼包）
                    elseif ($value['extension_code'] == 'package_buy')
                    {
                        foreach ($value['package_goods_list'] as $pg_key => $pg_value)
                        {
                            $delivery_pg_goods = array('delivery_id' => $delivery_id,
                                                    'goods_id' => $pg_value['goods_id'],
                                                    'product_id' => $pg_value['product_id'],
                                                    'product_sn' => $pg_value['product_sn'],
                                                    'goods_name' => $pg_value['goods_name'],
                                                    'brand_name' => '',
                                                    'goods_sn' => $pg_value['goods_sn'],
                                                    'send_number' => $send_number[$value['rec_id']][$pg_value['g_p']],
                                                    'parent_id' => $value['goods_id'], // 礼包ID
                                                    'extension_code' => $value['extension_code'], // 礼包
                                                    'is_real' => $pg_value['is_real']
                                                    );
                            $query = $db->autoExecute($hhs->table('delivery_goods'), $delivery_pg_goods, 'INSERT', '', 'SILENT');
                        }
                    }
                }
            }
        }
        else 
        {
            /* 操作失败 */
            $links = 'suppliers.php?act=order_info&order_id=' . $order_id;
            show_message($_LANG['act_false'], '返回', $links);
        }
		
		//生成提货单是否给发短信
		if($_CFG['sms_order_user_shipped']==1&&$_delivery['mobile']!='')
		{
			include_once('includes/cls_sms.php');
			$sms = new sms();
			$distribution_time = local_date("Y-m-d H:i:s",gmtime());
			if($_delivery['supp_account_id'])
			{
				$supp_account_row  = $db->getRow("select address,phone from ".$hhs->table('supp_account')." where account_id='$_delivery[supp_account_id]'");
				if($_delivery['shipping_id']==10)
				{
					$msg = "尊敬的用户，您的提货单已生成，请您去$supp_account_row[address]提货，商家联系联系电话：$supp_account_row[phone]";
				}
				else
				{
					$msg = "尊敬的用户，您的订单号：$_delivery[order_sn]，已于".$distribution_time."发货，请您注意查收。";
				}
			}
			else
			{
				if($_delivery['shipping_id']==10)
				{
					$msg = "尊敬的用户，您的订单号：$_delivery[order_sn]，请您去$suppliers_array[address] 提货，商家联系电话：$suppliers_array[phone]";
				}
				else
				{
					$msg = "尊敬的用户，您的订单号：$_delivery[order_sn]，已于".$distribution_time."发货，请您注意查收。";
				}
			}
			$sms->send($_delivery['mobile'],$msg,'', 13,1);
		}		
        unset($filter_fileds, $delivery, $_delivery, $order_finish);
        /* 定单信息更新处理 */
        if (true)
        {
            /* 定单信息 */
            $_sended = & $send_number;
            foreach ($_goods['goods_list'] as $key => $value)
            {
                if ($value['extension_code'] != 'package_buy')
                {
                    unset($_goods['goods_list'][$key]);
                }
            }
            foreach ($goods_list as $key => $value)
            {
                if ($value['extension_code'] == 'package_buy')
                {
                    unset($goods_list[$key]);
                }
            }
            $_goods['goods_list'] = $goods_list + $_goods['goods_list'];
            unset($goods_list);


            /* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
            update_order_goods($order_id, $_sended, $_goods['goods_list']);

            /* 标记订单为已确认 “发货中” */
            /* 更新发货时间 */
            $order_finish = get_order_finish($order_id);
            $shipping_status = SS_SHIPPED_ING;
            if ($order['order_status'] != OS_CONFIRMED && $order['order_status'] != OS_SPLITED && $order['order_status'] != OS_SPLITING_PART)
            {
                $arr['order_status']    = OS_CONFIRMED;
                $arr['confirm_time']    = GMTIME_UTC;
            }
            $arr['order_status'] = $order_finish ? OS_SPLITED : OS_SPLITING_PART; // 全部分单、部分分单
            $arr['shipping_status']     = $shipping_status;
            update_order($order_id, $arr);
        }
		
		
		

        /* 记录log */
        order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note,$supp_opt_name);

        /* 清除缓存 */
        clear_cache_files();
    }
    /* 设为未发货 */
    elseif ('unship' == $operation)
    {
        /* 检查权限 */
        admin_priv('order_ss_edit');

        /* 标记订单为“未发货”，更新发货时间, 订单状态为“确认” */
        update_order($order_id, array('shipping_status' => SS_UNSHIPPED, 'shipping_time' => 0, 'invoice_no' => '', 'order_status' => OS_CONFIRMED));

        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], SS_UNSHIPPED, $order['pay_status'], $action_note,$supp_opt_name);

        /* 如果订单用户不为空，计算积分，并退回 */
        if ($order['user_id'] > 0)
        {
            /* 取得用户信息 */
            $user = user_info($order['user_id']);

            /* 计算并退回积分 */
            $integral = integral_to_give($order);
            log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));

            /* todo 计算并退回优惠劵 */
            return_order_bonus($order_id);
        }

        /* 如果使用库存，则增加库存 */
        if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_SHIP)
        {
            change_order_goods_storage($order['order_id'], false, SDT_SHIP);
        }

        /* 删除发货单 */
        del_order_delivery($order_id);

        /* 将订单的商品发货数量更新为 0 */
        $sql = "UPDATE " . $GLOBALS['hhs']->table('order_goods') . "
                SET send_number = 0
                WHERE order_id = '$order_id'";
        $GLOBALS['db']->query($sql, 'SILENT');

        /* 清除缓存 */
        clear_cache_files();
    }
    /* 收货确认 */
    elseif ('receive' == $operation)
    {
        /* 标记订单为“收货确认”，如果是货到付款，同时修改订单为已付款 */
        $arr = array('shipping_status' => SS_RECEIVED);
        $payment = payment_info($order['pay_id']);
        if ($payment['is_cod'])
        {
            $arr['pay_status'] = PS_PAYED;
            $order['pay_status'] = PS_PAYED;
        }
        update_order($order_id, $arr);

        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], $action_note,$supp_opt_name);
    }

    /* 退货 */
    elseif ('return' == $operation)
    {
        /* 定义当前时间 */
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳

        /* 过滤数据 */
        $_REQUEST['refund'] = isset($_REQUEST['refund']) ? $_REQUEST['refund'] : '';
        $_REQUEST['refund_note'] = isset($_REQUEST['refund_note']) ? $_REQUEST['refund'] : '';

        /* 标记订单为“退货”、“未付款”、“未发货” */
        $arr = array('order_status'     => OS_RETURNED,
                     'pay_status'       => PS_UNPAYED,
                     'shipping_status'  => SS_UNSHIPPED,
                     'money_paid'       => 0,
                     'invoice_no'       => '',
                     'order_amount'     => $order['money_paid']);
        update_order($order_id, $arr);

        /* todo 处理退款 */
        if ($order['pay_status'] != PS_UNPAYED)
        {
            $refund_type = 1;//$_REQUEST['refund'];
            $refund_note = $_REQUEST['refund'];
            order_refund($order, $refund_type, $refund_note);
        }

        /* 记录log */
        order_action($order['order_sn'], OS_RETURNED, SS_UNSHIPPED, PS_UNPAYED, $action_note);

        /* 如果订单用户不为空，计算积分，并退回 */
        if ($order['user_id'] > 0)
        {
            /* 取得用户信息 */
            $user = user_info($order['user_id']);

            $sql = "SELECT  goods_number, send_number FROM". $GLOBALS['hhs']->table('order_goods') . "
                WHERE order_id = '".$order['order_id']."'";

            $goods_num = $db->query($sql);
            $goods_num = $db->fetchRow($goods_num);

            if($goods_num['goods_number'] == $goods_num['send_number'])
            {
                /* 计算并退回积分 */
                $integral = integral_to_give($order);
                log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf($_LANG['return_order_gift_integral'], $order['order_sn']));
            }
            /* todo 计算并退回优惠劵 */
            return_order_bonus($order_id);

        }

        /* 如果使用库存，则增加库存（不论何时减库存都需要） */
        if ($_CFG['use_storage'] == '1')
        {
            if ($_CFG['stock_dec_time'] == SDT_SHIP)
            {
                change_order_goods_storage($order['order_id'], false, SDT_SHIP);
            }
            elseif ($_CFG['stock_dec_time'] == SDT_PLACE)
            {
                change_order_goods_storage($order['order_id'], false, SDT_PLACE);
            }
        }

        /* 退货用户余额、积分、优惠劵 */
        return_user_surplus_integral_bonus($order);

        /* 获取当前操作员 */
        $delivery['action_user'] = $_SESSION['admin_name'];
        /* 添加退货记录 */
        $delivery_list = array();
        $sql_delivery = "SELECT *
                         FROM " . $hhs->table('delivery_order') . "
                         WHERE status IN (0, 2)
                         AND order_id = " . $order['order_id'];
        $delivery_list = $GLOBALS['db']->getAll($sql_delivery);
        if ($delivery_list)
        {
            foreach ($delivery_list as $list)
            {
                $sql_back = "INSERT INTO " . $hhs->table('back_order') . " (delivery_sn, order_sn, order_id, add_time, shipping_id, user_id, action_user, consignee, address, Country, province, City, district, sign_building, Email,Zipcode, Tel, Mobile, best_time, postscript, how_oos, insure_fee, shipping_fee, update_time, suppliers_id, return_time, agency_id, invoice_no) VALUES ";

                $sql_back .= " ( '" . $list['delivery_sn'] . "', '" . $list['order_sn'] . "',
                              '" . $list['order_id'] . "', '" . $list['add_time'] . "',
                              '" . $list['shipping_id'] . "', '" . $list['user_id'] . "',
                              '" . $delivery['action_user'] . "', '" . $list['consignee'] . "',
                              '" . $list['address'] . "', '" . $list['country'] . "', '" . $list['province'] . "',
                              '" . $list['city'] . "', '" . $list['district'] . "', '" . $list['sign_building'] . "',
                              '" . $list['email'] . "', '" . $list['zipcode'] . "', '" . $list['tel'] . "',
                              '" . $list['mobile'] . "', '" . $list['best_time'] . "', '" . $list['postscript'] . "',
                              '" . $list['how_oos'] . "', '" . $list['insure_fee'] . "',
                              '" . $list['shipping_fee'] . "', '" . $list['update_time'] . "',
                              '" . $list['suppliers_id'] . "', '" . GMTIME_UTC . "',
                              '" . $list['agency_id'] . "', '" . $list['invoice_no'] . "'
                              )";
                $GLOBALS['db']->query($sql_back, 'SILENT');
                $back_id = $GLOBALS['db']->insert_id();

                $sql_back_goods = "INSERT INTO " . $hhs->table('back_goods') . " (back_id, goods_id, product_id, product_sn, goods_name,goods_sn, is_real, send_number, goods_attr)
                                   SELECT '$back_id', goods_id, product_id, product_sn, goods_name, goods_sn, is_real, send_number, goods_attr
                                   FROM " . $hhs->table('delivery_goods') . "
                                   WHERE delivery_id = " . $list['delivery_id'];
                $GLOBALS['db']->query($sql_back_goods, 'SILENT');
            }
        }

        /* 修改订单的发货单状态为退货 */
        $sql_delivery = "UPDATE " . $hhs->table('delivery_order') . "
                         SET status = 1
                         WHERE status IN (0, 2)
                         AND order_id = " . $order['order_id'];
        $GLOBALS['db']->query($sql_delivery, 'SILENT');

        /* 将订单的商品发货数量更新为 0 */
        $sql = "UPDATE " . $GLOBALS['hhs']->table('order_goods') . "
                SET send_number = 0
                WHERE order_id = '$order_id'";
        $GLOBALS['db']->query($sql, 'SILENT');

        /* 清除缓存 */
        clear_cache_files();
    }
    elseif ('after_service' == $operation)
    {
        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '[' . $_LANG['op_after_service'] . '] ' . $action_note,$supp_opt_name);
    }
    else
    {
        // die('invalid params');
    }

    /* 操作成功 */
   // $links[] = array('text' => $_LANG['order_info'], 'href' => 'suppliers.php?act=info&order_id=' . $order_id);
    show_message('操作成功','订单详情','suppliers.php?act=order_info&order_id=' . $order_id,'info');
}
//------------------------------------------------------------------------------------------
//订单统计
elseif($action == 'order_stats')
{
    /* 随机的颜色数组 */
    $color_array = array('33FF66', 'FF6600', '3399FF', '009966', 'CC3399', 'FFCC33', '6699CC', 'CC3366');

	//加入商家
	$other_where = " AND suppliers_id = ".$suppliers_id." ";
	
    /* 计算订单各种费用之和的语句 */
    $total_fee = " SUM(" . order_amount_field() . ") AS total_turnover ";

    /* 取得订单转化率数据 */
    $sql = "SELECT COUNT(*) AS total_order_num, " .$total_fee.
           " FROM " . $hhs->table('order_info').
           " WHERE 1 $other_where " . order_query_sql('finished');
    $order_general = $db->getRow($sql);
    $order_general['total_turnover'] = floatval($order_general['total_turnover']);

	
    /* 取得商品总点击数量 */
    $sql = 'SELECT SUM(click_count) FROM ' .$hhs->table('goods') .' WHERE is_delete = 0';
    $click_count = floatval($db->getOne($sql));

    /* 每千个点击的订单数 */
    $click_ordernum = $click_count > 0 ? round(($order_general['total_order_num'] * 1000)/$click_count,2) : 0;

    /* 每千个点击的购物额 */
    $click_turnover = $click_count > 0 ? round(($order_general['total_turnover'] * 1000)/$click_count,2) : 0;

    /* 时区 */
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

    /* 时间参数 */
    $is_multi = empty($_POST['is_multi']) ? false : true;

    /* 时间参数 */
    if (isset($_POST['start_date']) && !empty($_POST['end_date']))
    {
        $start_date = local_strtotime($_POST['start_date']);
        $end_date = local_strtotime($_POST['end_date']);
        if ($start_date == $end_date)
        {
            $end_date   =   $start_date + 86400;
        }
    }
    else
    {
        $today      = strtotime(local_date('Y-m-d'));   //本地时间
        $start_date = $today - 86400 * 6;
        $end_date   = $today + 86400;               //至明天零时
    }

    $start_date_arr = array();
    $end_date_arr = array();
    if(!empty($_POST['year_month']))
    {
        $tmp = $_POST['year_month'];

        for ($i = 0; $i < count($tmp); $i++)
        {
            if (!empty($tmp[$i]))
            {
                $tmp_time = local_strtotime($tmp[$i] . '-1');
                $start_date_arr[] = $tmp_time;
                $end_date_arr[]   = local_strtotime($tmp[$i] . '-' . date('t', $tmp_time));
            }
        }
    }
    else
    {
        $tmp_time = local_strtotime(local_date('Y-m-d'));
        $start_date_arr[] = local_strtotime(local_date('Y-m') . '-1');
        $end_date_arr[]   = local_strtotime(local_date('Y-m') . '-31');;
    }

    /* 按月份交叉查询 */
    if ($is_multi)
    {
        /* 订单概况 */
        $order_general_xml = "<chart caption='$_LANG[order_circs]' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";
        $order_general_xml .= "<categories><category label='$_LANG[confirmed]' />" .
                                "<category label='$_LANG[succeed]' />" .
                                "<category label='$_LANG[unconfirmed]' />" .
                                "<category label='$_LANG[invalid]' /></categories>";
        foreach($start_date_arr AS $k => $val)
        {
            $seriesName = local_date('Y-m',$val);
            $order_info = get_orderinfo($start_date_arr[$k], $end_date_arr[$k],$suppliers_id);
            $order_general_xml .= "<dataset seriesName='$seriesName' color='$color_array[$k]' showValues='0'>";
            $order_general_xml .= "<set value='$order_info[confirmed_num]' />";
            $order_general_xml .= "<set value='$order_info[succeed_num]' />";
            $order_general_xml .= "<set value='$order_info[unconfirmed_num]' />";
            $order_general_xml .= "<set value='$order_info[invalid_num]' />";
            $order_general_xml .= "</dataset>";
        }
        $order_general_xml .= "</chart>";

        /* 支付方式 */
        $pay_xml = "<chart caption='$_LANG[pay_method]' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";

        $payment = array();
        $payment_count = array();

        foreach($start_date_arr AS $k => $val)
        {
             $sql = 'SELECT i.pay_id, p.pay_name, i.pay_time, COUNT(i.order_id) AS order_num ' .
                'FROM ' .$hhs->table('payment'). ' AS p, ' .$hhs->table('order_info'). ' AS i '.
                "WHERE  p.pay_id = i.pay_id AND i.order_status = '" .OS_CONFIRMED. "' ".
                "AND i.pay_status > '" .PS_UNPAYED. "' AND i.shipping_status > '" .SS_UNSHIPPED. "' ".
                "AND i.add_time >= '$start_date_arr[$k]' AND i.add_time <= '$end_date_arr[$k]' ".$other_where." ".
                "GROUP BY i.pay_id ORDER BY order_num DESC";
				
			
				
             $pay_res = $db->query($sql);
             while ($pay_item = $db->FetchRow($pay_res))
             {
                $payment[$pay_item['pay_name']] = null;

                $paydate = local_date('Y-m', $pay_item['pay_time']);

                $payment_count[$pay_item['pay_name']][$paydate] = $pay_item['order_num'];
             }
        }

        $pay_xml .= "<categories>";
        foreach ($payment AS $k => $val)
        {
            $pay_xml .= "<category label='$k' />";
        }
        $pay_xml .= "</categories>";

        foreach($start_date_arr AS $k => $val)
        {
            $date = local_date('Y-m', $start_date_arr[$k]);
            $pay_xml .= "<dataset seriesName='$date' color='$color_array[$k]' showValues='0'>";
            foreach ($payment AS $k => $val)
            {
                $count = 0;
                if (!empty($payment_count[$k][$date]))
                {
                  $count = $payment_count[$k][$date];
                }

                $pay_xml .= "<set value='$count' name='$date' />";
            }
            $pay_xml .= "</dataset>";
        }
        $pay_xml .= "</chart>";

        /* 配送方式 */
        $ship = array();
        $ship_count = array();

        $ship_xml = "<chart caption='$_LANG[shipping_method]' shownames='1' showvalues='0' decimals='0' outCnvBaseFontSize='12' baseFontSize='12' >";

        foreach($start_date_arr AS $k => $val)
        {
             $sql = 'SELECT sp.shipping_id, sp.shipping_name AS ship_name, i.shipping_time, COUNT(i.order_id) AS order_num ' .
               'FROM ' .$hhs->table('shipping'). ' AS sp, ' .$hhs->table('order_info'). ' AS i ' .
               'WHERE sp.shipping_id = i.shipping_id ' . order_query_sql('finished') .
               "AND i.add_time >= '$start_date_arr[$k]' AND i.add_time <= '$end_date_arr[$k]' $other_where " .
               "GROUP BY i.shipping_id ORDER BY order_num DESC";

             $ship_res = $db->query($sql);
             while ($ship_item = $db->FetchRow($ship_res))
             {
                $ship[$ship_item['ship_name']] = null;

                $shipdate = local_date('Y-m', $ship_item['shipping_time']);

                $ship_count[$ship_item['ship_name']][$shipdate] = $ship_item['order_num'];
             }
        }

        $ship_xml .= "<categories>";
        foreach ($ship AS $k => $val)
        {
            $ship_xml .= "<category label='$k' />";
        }
        $ship_xml .= "</categories>";

        foreach($start_date_arr AS $k => $val)
        {
            $date = local_date('Y-m', $start_date_arr[$k]);

            $ship_xml .= "<dataset seriesName='$date' color='$color_array[$k]' showValues='0'>";
            foreach ($ship AS $k => $val)
            {
                $count = 0;
                if (!empty($ship_count[$k][$date]))
                {
                    $count = $ship_count[$k][$date];
                }
                $ship_xml .= "<set value='$count' name='$date' />";
            }
            $ship_xml .= "</dataset>";
        }
        $ship_xml .= "</chart>";
    }
    /* 按时间段查询 */
    else
    {
        /* 订单概况 */
        $order_info = get_orderinfo($start_date, $end_date,$suppliers_id);

        $order_general_xml = "<graph caption='".$_LANG['order_circs']."' decimalPrecision='2' showPercentageValues='0' showNames='1' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

        $order_general_xml .= "<set value='" .$order_info['confirmed_num']. "' name='" . $_LANG['confirmed'] . "' color='".$color_array[5]."' />";

        $order_general_xml .= "<set value='" .$order_info['succeed_num']."' name='" . $_LANG['succeed'] . "' color='".$color_array[0]."' />";

        $order_general_xml .= "<set value='" .$order_info['unconfirmed_num']. "' name='" . $_LANG['unconfirmed'] . "' color='".$color_array[1]."'  />";

        $order_general_xml .= "<set value='" .$order_info['invalid_num']. "' name='" . $_LANG['invalid'] . "' color='".$color_array[4]."' />";
        $order_general_xml .= "</graph>";

        /* 支付方式 */
        $pay_xml = "<graph caption='" . $_LANG['pay_method'] . "' decimalPrecision='2' showPercentageValues='0' showNames='1' numberPrefix='' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

        $sql = 'SELECT i.pay_id, p.pay_name, COUNT(i.order_id) AS order_num ' .
           'FROM ' .$hhs->table('payment'). ' AS p, ' .$hhs->table('order_info'). ' AS i '.
           "WHERE  p.pay_id = i.pay_id " . order_query_sql('finished') .
           " AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' ".$other_where." ".
           "GROUP BY i.pay_id ORDER BY order_num DESC";

        $pay_res= $db->query($sql);

        while ($pay_item = $db->FetchRow($pay_res))
        {
            $pay_xml .= "<set value='".$pay_item['order_num']."' name='".$pay_item['pay_name']."' color='".$color_array[mt_rand(0,7)]."'/>";
        }
        $pay_xml .= "</graph>";

        /* 配送方式 */
        $ship_xml = "<graph caption='".$_LANG['shipping_method']."' decimalPrecision='2' showPercentageValues='0' showNames='1' numberPrefix='' showValues='1' showPercentageInLabel='0' pieYScale='45' pieBorderAlpha='40' pieFillAlpha='70' pieSliceDepth='15' pieRadius='100' outCnvBaseFontSize='13' baseFontSize='12'>";

        $sql = 'SELECT sp.shipping_id, sp.shipping_name AS ship_name, COUNT(i.order_id) AS order_num ' .
               'FROM ' .$hhs->table('shipping'). ' AS sp, ' .$hhs->table('order_info'). ' AS i ' .
               'WHERE  sp.shipping_id = i.shipping_id ' . order_query_sql('finished') .
               "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' $other_where " .
               "GROUP BY i.shipping_id ORDER BY order_num DESC";
        $ship_res = $db->query($sql);

        while ($ship_item = $db->fetchRow($ship_res))
        {
            $ship_xml .= "<set value='".$ship_item['order_num']."' name='".$ship_item['ship_name']."' color='".$color_array[mt_rand(0,7)]."' />";
        }

        $ship_xml .= "</graph>";

    }
    /* 赋值到模板 */
    $smarty->assign('order_general',       $order_general);
    $smarty->assign('total_turnover',      price_format($order_general['total_turnover']));
    $smarty->assign('click_count',         $click_count);         //商品总点击数
    $smarty->assign('click_ordernum',      $click_ordernum);      //每千点订单数
    $smarty->assign('click_turnover',      price_format($click_turnover));  //每千点购物额

    $smarty->assign('is_multi',            $is_multi);

    $smarty->assign('order_general_xml',   $order_general_xml);
    $smarty->assign('ship_xml',            $ship_xml);
    $smarty->assign('pay_xml',             $pay_xml);

    $smarty->assign('ur_here',             $_LANG['report_order']);
    $smarty->assign('start_date',          local_date($_CFG['date_format'], $start_date));
    $smarty->assign('end_date',            local_date($_CFG['date_format'], $end_date));

    for ($i = 0; $i < 5; $i++)
    {
        if (isset($start_date_arr[$i]))
        {
            $start_date_arr[$i] = local_date('Y-m', $start_date_arr[$i]);
        }
        else
        {
            $start_date_arr[$i] = null;
        }
    }
    $smarty->assign('start_date_arr', $start_date_arr);

    if (!$is_multi)
    {
        $filename = local_date('Ymd', $start_date) . '_' . local_date('Ymd', $end_date);
        $smarty->assign('action_link',  array('text' => $_LANG['down_order_statistics'], 'href' => 'order_stats.php?act=download&start_date=' . $start_date . '&end_date=' . $end_date . '&filename=' . $filename));
    }
	$smarty->assign('fstart_date',$start_date);
	$smarty->assign('fend_date',$end_date);

    assign_query_info();
    $smarty->display('suppliers_statistical.dwt');
}
elseif ($action == 'order_stats_download')
{
    $filename = '订单统计报表';
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename.xls");
    $start_date = empty($_REQUEST['start_date']) ? strtotime('-20 day') : intval($_REQUEST['start_date']);
    $end_date   = empty($_REQUEST['end_date']) ? time() : intval($_REQUEST['end_date']);
    /* 订单概况 */
    $order_info = get_orderinfo($start_date, $end_date);
    $data = $_LANG['order_circs'] . "\n";
    $data .= "$_LANG[confirmed] \t $_LANG[succeed] \t $_LANG[unconfirmed] \t $_LANG[invalid] \n";
    $data .= "$order_info[confirmed_num] \t $order_info[succeed_num] \t $order_info[unconfirmed_num] \t $order_info[invalid_num]\n";
    $data .= "\n$_LANG[pay_method]\n";

    /* 支付方式 */
    $sql = 'SELECT i.pay_id, p.pay_name, COUNT(i.order_id) AS order_num ' .
            'FROM ' .$hhs->table('payment'). ' AS p, ' .$hhs->table('order_info'). ' AS i '.
            "WHERE p.pay_id = i.pay_id " . order_query_sql('finished') .
            "AND i.add_time >= '$start_date' AND i.add_time <= '$end_date' ".
            "GROUP BY i.pay_id ORDER BY order_num DESC";
    $pay_res= $db->getAll($sql);
    foreach ($pay_res AS $val)
    {
        $data .= $val['pay_name'] . "\t";
    }
    $data .= "\n";
    foreach ($pay_res AS $val)
    {
        $data .= $val['order_num'] . "\t";
    }

   

    echo hhs_iconv(EC_CHARSET, 'GB2312', $data) . "\t";
    exit;

}
elseif ($action == 'sale_general')
{
	/* 取得查询类型和查询时间段 */
	if (empty($_POST['query_by_year']) && empty($_POST['query_by_month']))
	{
		if (empty($_GET['query_type']))
		{
			/* 默认当年的月走势 */
			$query_type = 'month';
			$start_time = local_mktime(0, 0, 0, 1, 1, intval(date('Y')));
			$end_time   = gmtime();
		}
		else
		{
			/* 下载时的参数 */
			$query_type = $_GET['query_type'];
			$start_time = $_GET['start_time'];
			$end_time   = $_GET['end_time'];
		}
	}
	else
	{
			if (isset($_POST['query_by_year']))
			{
				/* 年走势 */
				$query_type = 'year';
				$start_time = local_mktime(0, 0, 0, 1, 1, intval($_POST['year_beginYear']));
				$end_time   = local_mktime(23, 59, 59, 12, 31, intval($_POST['year_endYear']));
			}
			else
			{
				/* 月走势 */
				$query_type = 'month';
				$start_time = local_mktime(0, 0, 0, intval($_POST['month_beginMonth']), 1, intval($_POST['month_beginYear']));
				$end_time   = local_mktime(23, 59, 59, intval($_POST['month_endMonth']), 1, intval($_POST['month_endYear']));
				$end_time   = local_mktime(23, 59, 59, intval($_POST['month_endMonth']), date('t', $end_time), intval($_POST['month_endYear']));
		
			}
		}
		
		/* 分组统计订单数和销售额：已发货时间为准 */
		$format = ($query_type == 'year') ? '%Y' : '%Y-%m';
		$sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(shipping_time), '$format') AS period, COUNT(*) AS order_count, " .
					"SUM(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee - discount) AS order_amount " .
				"FROM " . $hhs->table('order_info') .
				" WHERE (order_status = '" . OS_CONFIRMED . "' OR order_status >= '" . OS_SPLITED . "')" .
				" AND (pay_status = '" . PS_PAYED . "' OR pay_status = '" . PS_PAYING . "') " .
				" AND (shipping_status = '" . SS_SHIPPED . "' OR shipping_status = '" . SS_RECEIVED . "') " .
				" AND shipping_time >= '$start_time' AND shipping_time <= '$end_time'" .
				" GROUP BY period ";
		$data_list = $db->getAll($sql);	
    /* 赋值查询时间段 */
    $smarty->assign('start_time',   local_date('Y-m-d', $start_time));
    $smarty->assign('end_time',     local_date('Y-m-d', $end_time));

    /* 赋值统计数据 */
    $xml = "<chart caption='' xAxisName='%s' showValues='0' decimals='0' formatNumberScale='0'>%s</chart>";
    $set = "<set label='%s' value='%s' />";
    $i = 0;
    $data_count  = '';
    $data_amount = '';
    foreach ($data_list as $data)
    {
        $data_count  .= sprintf($set, $data['period'], $data['order_count'], chart_color($i));
        $data_amount .= sprintf($set, $data['period'], $data['order_amount'], chart_color($i));
        $i++;
    }

    $smarty->assign('data_count',  sprintf($xml, '', $data_count)); // 订单数统计数据
    $smarty->assign('data_amount', sprintf($xml, '', $data_amount));    // 销售额统计数据
    
    $smarty->assign('data_count_name',  $_LANG['order_count_trend']); 
    $smarty->assign('data_amount_name',  $_LANG['order_amount_trend']); 

    /* 根据查询类型生成文件名 */
    if ($query_type == 'year')
    {
        $filename = date('Y', $start_time) . "_" . date('Y', $end_time) . '_report';
    }
    else
    {
       $filename = date('Ym', $start_time) . "_" . date('Ym', $end_time) . '_report';
    }
    $smarty->assign('action_link',
    array('text' => $_LANG['down_sales_stats'],
          'href'=>'sale_general.php?act=download&filename=' . $filename .
            '&query_type=' . $query_type . '&start_time=' . $start_time . '&end_time=' . $end_time));

    /* 显示模板 */
    $smarty->assign('ur_here', $_LANG['report_sell']);
    assign_query_info();
    $smarty->display('suppliers_statistical.dwt');
}
elseif($action =='sale_list')
{
    if (!isset($_REQUEST['start_date']))
    {
        $start_date = local_strtotime('-7 days');
	}
	else
	{
		$start_date = local_strtotime($_REQUEST['start_date']);	
	}
	
    if (!isset($_REQUEST['end_date']))
    {
        $end_date = local_strtotime('today');
    }
	else
	{
		$end_date = local_strtotime($_REQUEST['end_date']);
	}
    
    $sale_list_data = get_sale_list(true,$suppliers_id);
    /* 赋值到模板 */
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);
    $smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
    $smarty->assign('ur_here',          $_LANG['sell_stats']);
	$smarty->assign('pager', $sale_list_data['pager']);
    $smarty->assign('full_page',        1);
    $smarty->assign('start_date',       local_date('Y-m-d', $start_date));
    $smarty->assign('end_date',         local_date('Y-m-d', $end_date));
    $smarty->assign('ur_here',      $_LANG['sale_list']);
    $smarty->assign('cfg_lang',     $_CFG['lang']);
	$smarty->assign('fstart_date',local_date('Y-m-d', $start_date));
	$smarty->assign('fend_date',local_date('Y-m-d', $end_date));
  
    /* 显示页面 */
    $smarty->display('suppliers_statistical.dwt');
	
}
elseif($action =='sale_list_download')
{
  	 $file_name = $_REQUEST['start_date'].'_'.$_REQUEST['end_date'] . '_sale';
        $goods_sales_list = get_sale_list(false,$suppliers_id);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=销售明细.xls");

        /* 文件标题 */
        echo hhs_iconv(EC_CHARSET, 'GB2312',$_REQUEST['start_date']. $_LANG['to'] .$_REQUEST['end_date']. $_LANG['sales_list']) . "\t\n";

        /* 商品名称,订单号,商品数量,销售价格,销售日期 */
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['goods_name']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['order_sn']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['amount']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_price']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_date']) . "\t\n";

        foreach ($goods_sales_list['sale_list_data'] AS $key => $value)
        {
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_name']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', '[ ' . $value['order_sn'] . ' ]') . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['goods_num']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['sales_price']) . "\t";
            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['sales_time']) . "\t";
            echo "\n";
        }
        exit;	
}
elseif($action =='sale_order')
{

    /* 时间参数 */
    if (!isset($_REQUEST['start_date']))
    {
        $_REQUEST['start_date'] = local_strtotime('-1 months');
    }
	else
	{
		$_REQUEST['start_date'] = local_strtotime($_REQUEST['start_date']);
	}

    if (!isset($_REQUEST['end_date']))
    {
        $_REQUEST['end_date'] = local_strtotime('+1 day');
    }
	else
	{
		$_REQUEST['end_date'] = local_strtotime($_REQUEST['end_date']);
	}
    $goods_order_data = get_sales_order(true,$suppliers_id);

    /* 赋值到模板 */
    $smarty->assign('ur_here',          $_LANG['sell_stats']);
    $smarty->assign('goods_order_data', $goods_order_data['sales_order_data']);
    $smarty->assign('filter',           $goods_order_data['filter']);
	$smarty->assign('pager',			$goods_order_data['pager']);
    $smarty->assign('record_count',     $goods_order_data['record_count']);
    $smarty->assign('page_count',       $goods_order_data['page_count']);
    $smarty->assign('filter',           $goods_order_data['filter']);
    $smarty->assign('full_page',        1);
    $smarty->assign('sort_goods_num',   '<img src="images/sort_desc.gif">');
    $smarty->assign('start_date',       local_date('Y-m-d', $_REQUEST['start_date']));
    $smarty->assign('end_date',         local_date('Y-m-d', $_REQUEST['end_date']));

    $smarty->assign('fstart_date',     $_REQUEST['start_date']);
    $smarty->assign('fend_date',       $_REQUEST['end_date']);
    $smarty->display('suppliers_statistical.dwt');
}
elseif($action =='sale_order_download')
{
        $goods_order_data = get_sales_order(false,$suppliers_id);
        $goods_order_data = $goods_order_data['sales_order_data'];

        $filename = $_REQUEST['start_date'] . '_' . $_REQUEST['end_date'] .'sale_order';

        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=销售排行.xls");

        $data  = "$_LANG[sell_stats]\t\n";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['order_by']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['goods_name']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['goods_sn']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_amount']) . "\t";
		 echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_sum']) . "\t";
        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['percent_count']) . "\t\n";




        foreach ($goods_order_data AS $k => $row)
        {
            $order_by = $k + 1;
            $data .= "$order_by\t$row[goods_name]\t$row[goods_sn]\t$row[goods_num]\t$row[turnover]\t$row[wvera_price]\n";
        }

       
            echo hhs_iconv(EC_CHARSET, 'GB2312', $data);
       
        exit;
	
}



/**
 * 退回余额、积分、优惠劵（取消、无效、退货时），把订单使用余额、积分、优惠劵设为0
 * @param   array   $order  订单信息
 */
function return_user_surplus_integral_bonus($order)
{
	/* 处理余额、积分、优惠劵 */
	if ($order['user_id'] > 0 && $order['surplus'] > 0)
	{
		$surplus = $order['money_paid'] < 0 ? $order['surplus'] + $order['money_paid'] : $order['surplus'];
		log_account_change($order['user_id'], $surplus, 0, 0, 0, sprintf($GLOBALS['_LANG']['return_order_surplus'], $order['order_sn']));
		$GLOBALS['db']->query("UPDATE ". $GLOBALS['hhs']->table('order_info') . " SET `order_amount` = '0' WHERE `order_id` =". $order['order_id']);
	}

	if ($order['user_id'] > 0 && $order['integral'] > 0)
	{
		log_account_change($order['user_id'], 0, 0, 0, $order['integral'], sprintf($GLOBALS['_LANG']['return_order_integral'], $order['order_sn']));
	}

	if ($order['bonus_id'] > 0)
	{
		unuse_bonus($order['bonus_id']);
	}

	/* 修改订单 */
	$arr = array(
			'bonus_id'  => 0,
			'bonus'     => 0,
			'integral'  => 0,
			'integral_money'    => 0,
			'surplus'   => 0
	);
	update_order($order['order_id'], $arr);
}


function get_goods_cat($data)
{
	if($data['cat_four'] == "" && $data['cat_three'] != "")
	{
		$data['cat_id']	= $data['cat_three'];
		
	}
	else if($data['cat_three'] == "" && $data['cat_two'] != "")
	{
		
		$data['cat_id']	= $data['cat_two'];
	}
	else if($data['cat_two'] == "" )
	{
		$data['cat_id']	= $data['cat_one'];
	}
	else
	{
		$data['cat_id']	= $data['cat_four'];
	}
	
	return $data['cat_id'];
}

function get_each_cat($cat_id){
    if($cat_id<=0){
    	return array(0);
    }
    $arr=array();
    $parent_id=$cat_id; 
    $arr[]=$parent_id;
    do{
        $sql="select parent_id from ".$GLOBALS['hhs']->table('category')." where cat_id = ".$parent_id;
        $parent_id=$GLOBALS['db']->getOne($sql);
        $arr[]=$parent_id;     
    }while($parent_id>0);
    return array_reverse($arr);  
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function my_cat_list($suppliers_id,$cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
	static $res = NULL;

    if ($res === NULL)
    {
        
            $sql = "SELECT c.cat_id, c.cat_name,  c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children ".
                'FROM ' . $GLOBALS['hhs']->table('goods_category') . " AS c ".
                "LEFT JOIN " . $GLOBALS['hhs']->table('goods_category') . " AS s ON s.parent_id=c.cat_id  where c.suppliers_id = ".$suppliers_id." ".
                "GROUP BY c.cat_id ".
                'ORDER BY c.parent_id, c.sort_order ASC';
		
            $res = $GLOBALS['db']->getAll($sql);


            $sql = "SELECT cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $GLOBALS['hhs']->table('goods') .
                    " WHERE is_delete = 0 AND is_on_sale = 1 " .
                    " GROUP BY cat_id";
            $res2 = $GLOBALS['db']->getAll($sql);

            $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $GLOBALS['hhs']->table('goods_cat') . " AS gc , " . $GLOBALS['hhs']->table('goods') . " AS g " .
                    " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " .
                    " GROUP BY gc.cat_id";
            $res3 = $GLOBALS['db']->getAll($sql);

            $newres = array();
            foreach($res2 as $k=>$v)
            {
                $newres[$v['cat_id']] = $v['goods_num'];
                foreach($res3 as $ks=>$vs)
                {
                    if($v['cat_id'] == $vs['cat_id'])
                    {
                    $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
                    }
                }
            }

            foreach($res as $k=>$v)
            {
                $res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
            }
          
    }

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }

    $options = my_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

    $children_level = 99999; //大于这个分类的将被删除
    if ($is_show_all == false)
    {
        foreach ($options as $key => $val)
        {
            if ($val['level'] > $children_level)
            {
                unset($options[$key]);
            }
            else
            {
                if ($val['is_show'] == 0)
                {
                    unset($options[$key]);
                    if ($children_level > $val['level'])
                    {
                        $children_level = $val['level']; //标记一下，这样子分类也能删除
                    }
                }
                else
                {
                    $children_level = 99999; //恢复初始值
                }
            }
        }
    }

    /* 截取到指定的缩减级别 */
    if ($level > 0)
    {
        if ($cat_id == 0)
        {
            $end_level = $level;
        }
        else
        {
            $first_item = reset($options); // 获取第一个元素
            $end_level  = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options AS $key => $val)
        {
            if ($val['level'] >= $end_level)
            {
                unset($options[$key]);
            }
        }
    }

    if ($re_type == true)
    {
        $select = '';
        foreach ($options AS $var)

        {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0)
            {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
        }

        return $select;
    }
    else
    {
        foreach ($options AS $key => $value)
        {
            $options[$key]['url'] = build_uri('category', array('cid' => $value['cat_id']), $value['cat_name']);
        }

        return $options;
    }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function my_cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();

    if (isset($cat_options[$spec_cat_id]))
    {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0]))
    {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
       
            while (!empty($arr))
            {
                foreach ($arr AS $key => $value)
                {
                    $cat_id = $value['cat_id'];
                    if ($level == 0 && $last_cat_id == 0)
                    {
                        if ($value['parent_id'] > 0)
                        {
                            break;
                        }

                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['cat_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] == 0)
                        {
                            continue;
                        }
                        $last_cat_id  = $cat_id;
                        $cat_id_array = array($cat_id);
                        $level_array[$last_cat_id] = ++$level;
                        continue;
                    }

                    if ($value['parent_id'] == $last_cat_id)
                    {
                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['cat_name'];
						
						
						
						
                        unset($arr[$key]);

                        if ($value['has_children'] > 0)
                        {
                            if (end($cat_id_array) != $last_cat_id)
                            {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id    = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++$level;
                        }
						else
						{
							$options[$cat_id]['is_last']  = 1;
						}
                    }
                    elseif ($value['parent_id'] > $last_cat_id)
                    {
                        break;
                    }
                }

                $count = count($cat_id_array);
                if ($count > 1)
                {
                    $last_cat_id = array_pop($cat_id_array);
                }
                elseif ($count == 1)
                {
                    if ($last_cat_id != end($cat_id_array))
                    {
                        $last_cat_id = end($cat_id_array);
                    }
                    else
                    {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = array();
                        continue;
                    }
                }

                if ($last_cat_id && isset($level_array[$last_cat_id]))
                {
                    $level = $level_array[$last_cat_id];
                }
                else
                {
                    $level = 0;
                }
            }
            
        
        $cat_options[0] = $options;
    }
    else
    {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id)
    {
        return $options;
    }
    else
    {
        if (empty($options[$spec_cat_id]))
        {
            return array();
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options AS $key => $value)
        {
            if ($key != $spec_cat_id)
            {
                unset($options[$key]);
            }
            else
            {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options AS $key => $value)
        {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level']))
            {
                break;
            }
            else
            {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

function account_detail_list(){

    $suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];
    
    $where=" where sat.suppliers_accounts_id=".$suppliers_accounts_id;
    
    $sql = "SELECT sat.*,(sat.amount-sat.commission-sat.fenxiao_money) as money,o.suppliers_id,o.consignee,o.pay_name,o.user_id " .
    
        " FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat left join " .
    
        $GLOBALS['hhs']->table("order_info") . " as o on sat.order_id=o.order_id " .
    
        $where . " ORDER BY sat.id desc" ;
    
    $row=$GLOBALS['db']->getAll($sql);
    foreach ($row as $idx => $value)
    
    {
    
        $row[$idx]['order_time'] = local_date('Y-m-d', $value['order_time']);
    
        $total_amount += $row[$idx]['amount'];
    
        $total_commission += $row[$idx]['commission'];
    
        $total_fenxiao += $row[$idx]['fenxiao_money'];

        $total_money += ($row[$idx]['amount'] - $row[$idx]['commission'] - $row[$idx]['fenxiao_money']);
    
        $row[$idx]['suppliers_name'] = get_suppliers_name($value['suppliers_id']);
        if($value['user_id']){
            $row[$idx]['user_name'] = $GLOBALS['db']->getOne("select user_name from hhs_users where user_id=".$value['user_id']);
        }
        $transaction_order_sn = $GLOBALS['db']->getOne("select order_sn from ".$GLOBALS['hhs']->table('order_info')." where order_id='$value[new_parent_id]'");
    
        $row[$idx]['transaction_order_sn'] = $transaction_order_sn;
    
        $temp=array('order_id'=>$value['order_id']);
        $order_goods=get_order_goods($temp);
    
        $row[$idx]['goods'] =$order_goods;
        /*
        $sql="select goods_name from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];
        $goods_name=$db->getAll($sql);*/
        $str="";
        $total_goods_num=0;
        foreach($order_goods['goods_list'] as $v){
            $str.=$v['goods_name']."<br>";
            $total_goods_num+=$v['goods_number'];
        }
        
        $row[$idx]['goods_name']=$str;
        $row[$idx]['total_goods_num']=$total_goods_num;
    }
    
    return array('row'=>$row,'total_amount'=>$total_amount,'total_commission'=>$total_commission,'total_money'=>$total_money,'total_fenxiao'=>$total_fenxiao);

}

?>

