<?php
define('IN_HHS', true);


//订单列表
if($action =='goods_order' || $action =='goods_order2')
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
	$smarty->display("supp_order.dwt");

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
		$smarty->display("supp_order.dwt");
    }
}
