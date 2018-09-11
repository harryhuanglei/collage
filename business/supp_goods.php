<?php
define('IN_HHS', true);
if($action  =='my_goods')
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
	    /* 获取商品类型存在规格的类型 */
    $specifications = get_goods_type_specifications();
    $smarty->assign('specifications', $specifications);
	$smarty->assign('goods_status',$_REQUEST['goods_status']);
	$smarty->assign('is_check',$_REQUEST['is_check']);
	$smarty->assign('is_promote',$_REQUEST['is_promote']);
	$smarty->assign('is_season',$_REQUEST['is_season']);
	$smarty->assign('is_supp_top',$_REQUEST['is_supp_top']);
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('goods'). " $where and  suppliers_id = '$suppliers_id'");
	$pager  = get_pager('index.php', array('act' => $action,'op'=>'goods'), $record_count, $page);
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
    $smarty->display('supp_goods.dwt');
}
elseif($_REQUEST['act'] =='edit_shop_price')
{
    $goods_id       = intval($_POST['id']);
    $shop_price    = floatval($_POST['val']);
	
	$sql = $db->query("update ".$hhs->table('goods')." set  shop_price='$shop_price' where goods_id='$goods_id'");
	if($sql)
	{
            clear_cache_files();
            make_json_result(number_format($shop_price, 2, '.', ''));
	}
}
elseif($_REQUEST['act'] =='edit_team_price')
{
    $goods_id       = intval($_POST['id']);
    $team_price    = floatval($_POST['val']);
	
	$sql = $db->query("update ".$hhs->table('goods')." set  team_price='$team_price' where goods_id='$goods_id'");
	if($sql)
	{
            clear_cache_files();
            make_json_result(number_format($team_price, 2, '.', ''));
	}
}
elseif($_REQUEST['act'] =='edit_team_num')
{
    $goods_id       = intval($_POST['id']);
    $team_num    = floatval($_POST['val']);
	
	$sql = $db->query("update ".$hhs->table('goods')." set  team_num='$team_num' where goods_id='$goods_id'");
	if($sql)
	{
            clear_cache_files();
            make_json_result($team_num);
	}
}
elseif ($_REQUEST['act'] == 'check_products_goods_sn')
{
	include_once(ROOT_PATH . 'sysadm/includes/cls_exchange.php');
	$exc = new exchange($hhs->table('goods'), $db, 'goods_id', 'goods_name');
	//echo ROOT_PATH . 'sysadm/includes/cls_exchange.php';exit;
    $goods_id = intval($_REQUEST['goods_id']);
    $goods_sn = json_str_iconv(trim($_REQUEST['goods_sn']));
    $products_sn=explode('||',$goods_sn);
    if(!is_array($products_sn))
    {
        make_json_result('');
    }
    else
    {
        foreach ($products_sn as $val)
        {
            if(empty($val))
            {
                 continue;
            }
            if(is_array($int_arry))
            {
                if(in_array($val,$int_arry))
                {
                     make_json_error($val.'商品货号已经存在');
                }
            }
            $int_arry[]=$val;
            if (!$exc->is_only('goods_sn', $val, '0'))
            {
                make_json_error($val.'商品货号已经存在');
            }
            $sql="SELECT goods_id FROM ". $hhs->table('products')."WHERE product_sn='$val'";
            if($db->getOne($sql))
            {
                make_json_error($val.$_LANG['goods_sn_exists']);
            }
        }
    }
    /* 检查是否重复 */
    make_json_result('');
}
/*------------------------------------------------------ */
//-- 货品添加 执行
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_add_execute')
{
    $product['goods_id']        = intval($_POST['goods_id']);
    $product['attr']            = $_POST['attr'];
    $product['product_sn']      = $_POST['product_sn'];
    $product['product_number']  = $_POST['product_number'];
    /* 是否存在商品id */
    if (empty($product['goods_id']))
    {
        sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
    }
    /* 判断是否为初次添加 */
    $insert = true;
    if (product_number_count($product['goods_id']) > 0)
    {
        $insert = false;
    }
    /* 取出商品信息 */
    $sql = "SELECT goods_sn, goods_name, goods_type, shop_price FROM " . $hhs->table('goods') . " WHERE goods_id = '" . $product['goods_id'] . "'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
    }
    /*  */
    foreach($product['product_sn'] as $key => $value)
    {
        //过滤
        $product['product_number'][$key] = empty($product['product_number'][$key]) ? (empty($_CFG['use_storage']) ? 0 : $_CFG['default_storage']) : trim($product['product_number'][$key]); //库存
        //获取规格在商品属性表中的id
        foreach($product['attr'] as $attr_key => $attr_value)
        {
            /* 检测：如果当前所添加的货品规格存在空值或0 */
            if (empty($attr_value[$key]))
            {
                continue 2;
            }
            $is_spec_list[$attr_key] = 'true';
            $value_price_list[$attr_key] = $attr_value[$key] . chr(9) . ''; //$key，当前
            $id_list[$attr_key] = $attr_key;
        }
        $goods_attr_id = handle_goods_attr($product['goods_id'], $id_list, $is_spec_list, $value_price_list);
        /* 是否为重复规格的货品 */
        $goods_attr = sort_goods_attr_id_array($goods_attr_id);
        $goods_attr = implode('|', $goods_attr['sort']);
        if (check_goods_attr_exist($goods_attr, $product['goods_id']))
        {
            continue;
            //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_goods_attr'], 1, array(), false);
        }
        //货品号不为空
        if (!empty($value))
        {
            /* 检测：货品货号是否在商品表和货品表中重复 */
            if (check_goods_sn_exist($value))
            {
                continue;
                //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_goods_sn'], 1, array(), false);
            }
            if (check_product_sn_exist($value))
            {
                continue;
                //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_product_sn'], 1, array(), false);
            }
        }
        /* 插入货品表 */
        $sql = "INSERT INTO " . $GLOBALS['hhs']->table('products') . " (goods_id, goods_attr, product_sn, product_number)  VALUES ('" . $product['goods_id'] . "', '$goods_attr', '$value', '" . $product['product_number'][$key] . "')";
        if (!$GLOBALS['db']->query($sql))
        {
            continue;
            //sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_add_products'], 1, array(), false);
        }
        //货品号为空 自动补货品号
        if (empty($value))
        {
            $sql = "UPDATE " . $GLOBALS['hhs']->table('products') . "
                    SET product_sn = '" . $goods['goods_sn'] . "g_p" . $GLOBALS['db']->insert_id() . "'
                    WHERE product_id = '" . $GLOBALS['db']->insert_id() . "'";
            $GLOBALS['db']->query($sql);
        }
        /* 修改商品表库存 */
        $product_count = product_number_count($product['goods_id']);
		$update_goods = $db->query("update ".$hhs->table('goods')." set goods_number='$product_count' where goods_id='$product[goods_id]'");
        if ($update_goods)
        {
            //记录日志
           // admin_log($product['goods_id'], 'update', 'goods');
        }
    }
    clear_cache_files();
    /* 返回 */
    show_message('保存成功','返回货品列表', 'index.php?op=goods&act=product_list&goods_id=' . $product['goods_id'].'&page='.$_REQUEST['page']);
}
/*------------------------------------------------------ */
//-- 货品删除
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_remove')
{
    /* 是否存在商品id */
    if (empty($_REQUEST['id']))
    {
        show_message('请选择货品');
    }
    else
    {
        $product_id = intval($_REQUEST['id']);
    }
    /* 货品库存 */
    $product = get_product_info($product_id, 'product_number, goods_id');
    /* 删除货品 */
    $sql = "DELETE FROM " . $hhs->table('products') . " WHERE product_id = '$product_id'";
    $result = $db->query($sql);
    if ($result)
    {
        /* 修改商品库存 */
        if (update_goods_stock($product['goods_id'], $product_number - $product['product_number']))
        {
            //记录日志
           // admin_log('', 'update', 'goods');
        }
        //记录日志
        //admin_log('', 'trash', 'products');
      show_message('删除成功','返回商品列表','index.php?op=goods&act=product_list&goods_id='.$product['goods_id'].'&page='.$_REQUEST['page']);
    }
}
/*------------------------------------------------------ */
//-- 货品列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_list')
{
    /* 是否存在商品id */
    if (empty($_GET['goods_id']))
    {
		show_message('商品为空','返回列表', 'index.php?op=goods&act=my_goods', 'info');
    }
    else
    {
        $goods_id = intval($_GET['goods_id']);
    }
    /* 取出商品信息 */
    $sql = "SELECT goods_sn, goods_name, goods_type, shop_price FROM " . $hhs->table('goods') . " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
		show_message('商品为空','返回列表', 'index.php?op=goods&act=my_goods', 'info');
    }
    $smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
    $smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
    $smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
    $smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));
    /* 获取商品规格列表 */
    $attribute = get_goods_specifications_list($goods_id);
    if (empty($attribute))
    {
		show_message('没有商品属性','编辑商品', 'index.php?op=goods&act=edit_goods&goods_id='.$goods_id, 'info');
    }
    foreach ($attribute as $attribute_value)
    {
        //转换成数组
        $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
        $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
        $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
    }
    $attribute_count = count($_attribute);
    $smarty->assign('attribute_count',          $attribute_count);
    $smarty->assign('attribute_count_3',        ($attribute_count + 3));
    $smarty->assign('attribute',                $_attribute);
    $smarty->assign('product_sn',               $goods['goods_sn'] . '_');
    $smarty->assign('product_number',           $_CFG['default_storage']);
    /* 取商品的货品 */
    $product = product_list($goods_id, '');
    $smarty->assign('product_list', $product['product']);
    $smarty->assign('product_null', empty($product['product']) ? 0 : 1);
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);
    $smarty->assign('goods_id',     $goods_id);
	$smarty->assign('page',$_REQUEST['page']);
    $smarty->display('supp_goods.dwt');
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
	$pager  = get_pager('index.php', array('op'=>'goods','act' => $action), $record_count, $page);
	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);
	$smarty->assign('pager',  $pager);
    $smarty->assign('pic_list', $pic_list);
	$res['pic_list'] =$smarty->fetch('library/get_pic_list_photo.lbi');
	$res['pages'] =$smarty->fetch('library/pages.lbi');
	die($json->encode($res));
	//echo "[".$temp."]";
    exit;
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
	$pager  = get_pager('index.php', array('op'=>'goods','act' => $action), $record_count, $page);
	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);
	$smarty->assign('pager',  $pager);
    $smarty->assign('pic_list', $pic_list);
	$smarty->assign('timestamp',time());
	$unique_salt =  md5('unique_salt'.time());
	$smarty->assign('unique_salt',$unique_salt);
	$smarty->display('suppliers_get_photo.dwt');	
}
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
elseif($action =='restore_goods')
{
	$id = $_GET['goods_id'];
	$sql = $db->query("update   ".$hhs->table('goods')." set is_delete=0 where goods_id='$id'");
    show_message('还原成功','返回列表', 'index.php?op=goods&act=my_goods', 'info');
}
elseif($action =='get_pic')
{
	$cat_list =get_pic_cat_list($suppliers_id); 
	$smarty->assign('cat_list',$cat_list);
	$img_id = trim($_REQUEST['img_id']);
	if($img_id == 'goods_img_url')
	{
		$img_w = intval($_CFG['image_width']);
		$img_h = intval($_CFG['image_height']);
	}
	if($img_id == 'little_img')
	{
		$img_w = intval($_CFG['tuan_image_w']);
		$img_h = intval($_CFG['tuan_image_h']);
	}
	$smarty->assign('img_w',$img_w);
	$smarty->assign('img_h',$img_h);
    $smarty->assign('close_img',$_CFG['close_img']);
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where id>0";
	if($_REQUEST['cat_id'])
	{
		$where .=" and cat_id='$_REQUEST[cat_id]'";	
		$smarty->assign('cat_id',$_REQUEST[cat_id]);
	}
    $smarty->assign('img_id',$img_id);
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");
	$pager  = get_pager('index.php', array('act' => $action,'op' => 'goods','cat_id'=>$_REQUEST[cat_id],'img_id'=>$img_id), $record_count, $page);
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
	$pager  = get_pager('index.php', array('act' => $action,'op'=>'goods'), $record_count, $page);
	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);
	$smarty->assign('pager',  $pager);
    $smarty->assign('pic_list', $pic_list);
	$smarty->assign('timestamp',time());
	$unique_salt =  md5('unique_salt'.time());
	$smarty->assign('unique_salt',$unique_salt);
	$smarty->display('suppliers_get_photo.dwt');	
}
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
        show_message('操作成功','返回列表', "index.php?op=goods&act=my_goods&page=$page", 'info');  
    }
    else
    {
        show_message("请先选择");
    }
}
elseif ($action == 'get_attr')
{
    $goods_id   = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
    $goods_type = empty($_GET['goods_type']) ? 0 : intval($_GET['goods_type']);
    $content    = build_attr_html($goods_type, $goods_id);
    make_json_result($content);
}
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
elseif($action =='insert_goods')
{
	$data = $_POST;
    $express = $_POST['express_items'];
    $express = strip_tags(urldecode($express));
    $express = json_str_iconv(stripslashes($express));
	if($express==888)
	{
		$express='';
	}
    $data['express'] = $express;
    $data['is_mall'] = isset($_POST['is_mall']) ? intval($_POST['is_mall']) : 0;
    $data['is_zero'] = isset($_POST['is_zero']) ? intval($_POST['is_zero']) : 0;
    $data['is_team'] = isset($_POST['is_team']) ? intval($_POST['is_team']) : 0;
    $data['is_tejia'] = isset($_POST['is_tejia']) ? intval($_POST['is_tejia']) : 0;
    $data['is_fresh'] = isset($_POST['is_fresh']) ? intval($_POST['is_fresh']) : 0;
    $data['is_app'] = isset($_POST['is_app']) ? intval($_POST['is_app']) : 0;
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
	$original_img   = $_POST['goods_img_url']; // 原始图片
	$goods_img      = $original_img;   // 商品图片
	$goods_thumb = $image->make_thumb(ROOT_PATH.$original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
	$data['original_img'] = $original_img;
	$data['goods_img'] = $original_img;
	$data['goods_thumb'] = $goods_thumb;
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
	$data['lab_qgby']  = $_POST['lab_qgby'];
	$data['lab_zpbz']  = $_POST['lab_zpbz'];
	$data['lab_qtth']  = $_POST['lab_qtth'];
	$data['lab_jkbs']  = $_POST['lab_jkbs'];
	$data['lab_hwzy']  = $_POST['lab_hwzy'];
	$data['ts_a']  = $_POST['ts_a'];
	$data['ts_b']  = $_POST['ts_b'];
	$data['ts_c']  = $_POST['ts_c'];
	//判断4级分类取出最后一个分类ID
	//$data['cat_id'] = get_goods_cat($data);
    $db->autoExecute($hhs->table('goods'), $data, 'INSERT');
	$goods_id = $db->insert_id();
	foreach($_POST['site_id'] as $key=>$value)
	{
		  $db->query("insert into ".$hhs->table('goods_site')." (site_id,goods_id) values ('$value','$goods_id')");
	}
	if(!$_POST['goods_sn'])
	{
		$goods_sn_xaphp = generate_goods_sn($goods_id);
		$sql = $db->query("update ".$hhs->table("goods")." set goods_sn='$goods_sn_xaphp' where goods_id='$goods_id'");
	}
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
				
               /*属性图片处理*/
                $goods_attr_img = false;
               if($_FILES['attr_img_list']['error'][$key] == 0)
                {
                    /*数据重新定义*/
                    $files_attr_p['name'] = $_FILES['attr_img_list']['name'][$key];
                    $files_attr_p['type'] = $_FILES['attr_img_list']['type'][$key];
                    $files_attr_p['tmp_name'] = $_FILES['attr_img_list']['tmp_name'][$key];
                    $files_attr_p['error'] = $_FILES['attr_img_list']['error'][$key];
                    $files_attr_p['size'] = $_FILES['attr_img_list']['size'][$key];
                    $goods_attr_img = $image->upload_image($files_attr_p,'business/uploads/'.$suppliers_id);
                }
				
				
				
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
                $attr_team_price = $_POST['attr_team_price_list'][$key];
                if (!empty($attr_value))
                {
                    if (isset($goods_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                        $goods_attr_list[$attr_id][$attr_value]['attr_team_price'] = $attr_team_price;
                        if($goods_attr_img !== false)
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = $goods_attr_img; 
                        }else
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = ''; 
                        }
						
						
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                        $goods_attr_list[$attr_id][$attr_value]['attr_team_price'] = $attr_team_price;
						
                        if($goods_attr_img !== false)
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = $goods_attr_img; 
                        }else
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = ''; 
                        }
						
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
        /* 插入、更新、删除数据 */
        foreach ($goods_attr_list as $attr_id => $attr_value_list)
        {
		
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO " .$hhs->table('goods_attr'). " (attr_id, goods_id, attr_value, attr_price,attr_team_price,attr_img)".
                            "VALUES ('$attr_id', '$goods_id', '$attr_value', '$info[attr_price]', '$info[attr_team_price]','$info[attr_img]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$hhs->table('goods_attr'). " SET attr_price = '$info[attr_price]',attr_team_price = '$info[attr_team_price]' ";
                    if(!empty($info['attr_img']))
                    {
                        $sql .= ", attr_img = '$info[attr_img]' ";
                    }
                    $sql .= " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$hhs->table('goods_attr'). " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
			//	echo $sql;exit;
                $db->query($sql);
            }
        }
    }

    handle_express($goods_id,$data['express']);
	show_message('保存成功','我的商品列表', 'index.php?op=goods&act=my_goods', 'info');
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
    show_message('放入回收站成功','返回列表', 'index.php?op=goods&act=my_goods', 'info');
}
elseif($action =='update_goods')
{
	$data = $_POST;
    if($_POST['express_items']!=888){
        $express = $_POST['express_items'];
        $express = strip_tags(urldecode($express));
        $express = json_str_iconv(stripslashes($express));    
        $data['express'] = $express;
    }
	if(!$data['express'])
	{
		unset($data['express']);	
	}
    $data['is_mall'] = isset($_POST['is_mall']) ? intval($_POST['is_mall']) : 0;
    $data['is_zero'] = isset($_POST['is_zero']) ? intval($_POST['is_zero']) : 0;
    $data['is_team'] = isset($_POST['is_team']) ? intval($_POST['is_team']) : 0;
    $data['is_tejia'] = isset($_POST['is_tejia']) ? intval($_POST['is_tejia']) : 0;
    $data['is_fresh'] = isset($_POST['is_fresh']) ? intval($_POST['is_fresh']) : 0;
    $data['is_app'] = isset($_POST['is_app']) ? intval($_POST['is_app']) : 0;
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
	$goods_img_old = $db->getOne("select goods_img from ".$hhs->table('goods')." where goods_id = ".$data[goods_id]." ");
	if($goods_img_old!=$goods_img)
	{
		$goods_thumb = $image->make_thumb(ROOT_PATH.$original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
		$data['goods_thumb'] = $goods_thumb;
	}
	$data['original_img'] = $original_img;
	$data['promote_start_date'] = local_strtotime($data['promote_start_date']);
	$data['promote_end_date'] = local_strtotime($data['promote_end_date']);
	$data['goods_img'] = $goods_img;
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
                /*属性图片处理*/
                $goods_attr_img = false;
				
				
				
                if($_FILES['attr_img_list']['error'][$key] == 0)
                {
                    /*数据重新定义*/
                    $files_attr_p['name'] = $_FILES['attr_img_list']['name'][$key];
                    $files_attr_p['type'] = $_FILES['attr_img_list']['type'][$key];
                    $files_attr_p['tmp_name'] = $_FILES['attr_img_list']['tmp_name'][$key];
                    $files_attr_p['error'] = $_FILES['attr_img_list']['error'][$key];
                    $files_attr_p['size'] = $_FILES['attr_img_list']['size'][$key];
                    $goods_attr_img = $image->upload_image($files_attr_p,'business/uploads/'.$suppliers_id);
                }
				
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
                $attr_team_price = $_POST['attr_team_price_list'][$key];
                if (!empty($attr_value) || !empty($attr_team_price))
                {
                    if (isset($goods_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                        $goods_attr_list[$attr_id][$attr_value]['attr_team_price'] = $attr_team_price;
                        if($goods_attr_img !== false)
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = $goods_attr_img; 
                        }else
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = ''; 
                        }
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                        $goods_attr_list[$attr_id][$attr_value]['attr_team_price'] = $attr_team_price;
                        if($goods_attr_img !== false)
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = $goods_attr_img; 
                        }else
                        {
                           $goods_attr_list[$attr_id][$attr_value]['attr_img'] = ''; 
                        }
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
                    $sql = "INSERT INTO " .$hhs->table('goods_attr'). " (attr_id, goods_id, attr_value, attr_price,attr_team_price,attr_img)".
                            "VALUES ('$attr_id', '$goods_id', '$attr_value', '$info[attr_price]', '$info[attr_team_price]','$info[attr_img]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$hhs->table('goods_attr'). " SET attr_price = '$info[attr_price]',attr_team_price = '$info[attr_team_price]' ";
                    if(!empty($info['attr_img']))
                    {
                        $sql .= ", attr_img = '$info[attr_img]' ";
                    }
                    $sql .= " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$hhs->table('goods_attr'). " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
			//	echo $sql;exit;
                $db->query($sql);
            }
        }
    }
    clear_all_files();
    handle_express($goods_id,$data['express']);    
	show_message('编辑成功','我的商品列表', 'index.php?op=goods&act=my_goods', 'info');	
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
	$smarty->assign('cfg',$_CFG);
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
	$smarty->assign('supp_brand_list',get_supp_brand_list());
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
	$smarty->display("supp_goods.dwt");	
}
elseif($action =='goods_on_sale')
{
	$goods_id = $_REQUEST['goods_id'];	
	$id       = $_REQUEST['id'];
	if($id ==1)
	{
		$db->query("update ".$hhs->table('goods')." set is_on_sale=0 where goods_id='$goods_id'");
	}
	else
	{
		$db->query("update ".$hhs->table('goods')." set is_on_sale=1 where goods_id='$goods_id'");
	}
	getUrl('index.php?op=goods&act=my_goods');
}
elseif($action =='add_goods')
{
    $smarty->assign('unit_list', get_unit_list());
    $smarty->assign('cities',    get_sitelists());
	$smarty->assign('form_act','insert_goods');
	$smarty->assign('cfg',$_CFG);
	create_html_editor_xaphp('goods_desc','',$suppliers_id);
	create_html_editor_xaphp1('factory_desc','',$suppliers_id);
	$smarty->assign('cat_list',     cat_list(0));
	$smarty->assign('supp_brand_list',get_supp_brand_list());
// 运费模板
    $goods['express'] = '[]';
    $smarty->assign('goods', $goods);
    // 获取有效的快递方式
    $shipping_list = $db->getAll('SELECT shipping_id,shipping_name,shipping_code FROM '.$hhs->table('shipping').' WHERE `enabled` = 1');
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
	$smarty->display("supp_goods.dwt");
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
	$pager  = get_pager('index.php', array('act' => $action,'op'=>'goods'), $record_count, $page);
	$goods_list = get_suppliers_goods($suppliers_id, $pager['size'], $pager['start'],1);
	$smarty->assign('pager',  $pager);
	$smarty->assign('goods_list', $goods_list);
    $smarty->display('supp_goods.dwt');	
}
elseif($action =='restore_goods')
{
	$id = $_GET['goods_id'];
	$sql = $db->query("update   ".$hhs->table('goods')." set is_delete=0 where goods_id='$id'");
    show_message('还原成功','返回列表', 'index.php?op=goods&act=my_goods', 'info');
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
	 show_message('删除成功','返回列表', 'index.php?op=goods&act=my_goods', 'info');
}
?>