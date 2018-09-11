<?php

$action_note = '批量处理订单';
$batch          = 1;// 是否批处理

/* 查询：是否保价
 $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
*/
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
                //发货数量
                $send_number[$goods_value['rec_id']][$pg_value['g_p']]=$pg_value['send'];

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
            $send_number[$goods_value['rec_id']]  =$goods_list[$key]['send'];

        }

    }
}

/* 定义当前时间 */
define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳
$suppliers_id = 0;
$order_id=$order['order_id'];
$delivery['order_sn']=$order['order_sn'];
$delivery['add_time']=$order['order_time'];
$delivery['user_id']=$order['user_id'];
$delivery['how_oos']=$order['how_oos'];
$delivery['shipping_id']=$order['shipping_id'];
$delivery['shipping_fee']=$order['shipping_fee'];
$delivery['consignee']=$order['consignee'];
$delivery['address']=$order['address'];
$delivery['country']=$order['country'];
$delivery['province']=$order['province'];
$delivery['city']=$order['city'];
$delivery['district']=$order['district'];
$delivery['sign_building']=$order['sign_building'];
$delivery['email']=$order['email'];
$delivery['zipcode']=$order['zipcode'];
$delivery['tel']=$order['tel'];
$delivery['mobile']=$order['mobile'];
$delivery['best_time']=$order['best_time'];
$delivery['postscript']=$order['postscript'];
$delivery['insure_fee']=$order['insure_fee'];
$delivery['agency_id']=$order['agency_id'];
$delivery['shipping_name']=$order['shipping_name'];
$operation='split';

$action_note =  '批量修改订单';
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
    $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
    sys_msg(sprintf($_LANG['order_splited_sms'], $order['order_sn'],
    $_LANG['os'][OS_SPLITED], $_LANG['ss'][SS_SHIPPED_ING], $GLOBALS['_CFG']['shop_name']), 1, $links);
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
    $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
    sys_msg($_LANG['act_false'], 1, $links);
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
$delivery['update_time'] = GMTIME_UTC;
$delivery_time = $delivery['update_time'];
$sql ="select add_time from ". $GLOBALS['hhs']->table('order_info') ." WHERE order_sn = '" . $delivery['order_sn'] . "'";
$delivery['add_time'] =  $GLOBALS['db']->GetOne($sql);
/* 获取发货单所属供应商 */
$delivery['suppliers_id'] = $suppliers_id;
/* 设置默认值 */
$delivery['status'] = 2; // 正常
$delivery['order_id'] = $order_id;
/* 过滤字段项 */
$filter_fileds = array(
    'order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
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
            if (empty($value['extension_code']) || $value['extension_code'] == 'team_goods' || $value['extension_code'] == 'virtual_card')
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
    $links[] = array('text' => $_LANG['order_info'], 'href' => 'order.php?act=info&order_id=' . $order_id);
    sys_msg($_LANG['act_false'], 1, $links);
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

    /* 更新订单的虚拟卡 商品（虚货） */
    $_virtual_goods = isset($virtual_goods['virtual_card']) ? $virtual_goods['virtual_card'] : '';
    update_order_virtual_goods($order_id, $_sended, $_virtual_goods);

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
order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note);

/* 清除缓存
 clear_cache_files();
*/

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

/* 是否保价
 $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
*/
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

/* 模板赋值
 $smarty->assign('delivery_order', $delivery_order);
$smarty->assign('exist_real_goods', $exist_real_goods);
$smarty->assign('goods_list', $goods_list);
$smarty->assign('delivery_id', $delivery_id); // 发货单id
*/
/* 显示模板
 $smarty->assign('ur_here', $_LANG['delivery_operate'] . $_LANG['detail']);
$smarty->assign('action_link', array('href' => 'order.php?act=delivery_list&' . list_link_postfix(), 'text' => $_LANG['09_delivery_order']));
$smarty->assign('action_act', ($delivery_order['status'] == 2) ? 'delivery_ship' : 'delivery_cancel_ship');
assign_query_info();
$smarty->display('delivery_info.htm');
exit; //
*/

/* 取得参数 */
$delivery   = array();
$delivery['invoice_no'] = $invoice_no;
$action_note    = '批量处理';

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
/*
$invoice_no = str_replace(',', '<br>', $delivery['invoice_no']);
$invoice_no = trim($invoice_no, '<br>');*/


$_delivery['invoice_no'] = $invoice_no;
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
$arr['shipping_status']     = $shipping_status;
$arr['shipping_time']       = GMTIME_UTC; // 发货时间
//$arr['invoice_no']          = $invoice_no;//trim($order['invoice_no'] . '<br>' . $invoice_no, '<br>');
update_order($order_id, $arr);


/* 发货单发货记录log */
order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, null, 1);

// $user_id=$order['user_id'];
// $wxch_order_name='shipping';
// include_once('../wxch_order.php');

/* 如果当前订单已经全部发货 */
if ($order_finish)
{ 
    /* 如果订单用户不为空，计算积分，并发给用户；发优惠劵 */
    if ($order['user_id'] > 0)
    {
        /* 取得用户信息 */
        $user = user_info($order['user_id']);

        /* 计算并发放积分 */
        $integral = integral_to_give($order);

        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

        /* 发放优惠劵
         //这里不发优惠券，收货时发
        send_order_bonus($order_id);
        */
    }

    /* 发送邮件
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
    */
    /* 如果需要，发短信
    if ($GLOBALS['_CFG']['sms_order_shipped'] == '1' && $order['mobile'] != '')
    {
    include_once('../includes/cls_sms.php');
    $sms = new sms();
     $sms->send($order['mobile'], sprintf($GLOBALS['_LANG']['order_shipped_sms'], $order['order_sn'],
         local_date($GLOBALS['_LANG']['sms_time_format']), $GLOBALS['_CFG']['shop_name']), 0);
         
        }*/
        }



        /* 操作成功
        $links[] = array('text' => $_LANG['09_delivery_order'], 'href' => 'order.php?act=delivery_list');
        $links[] = array('text' => $_LANG['delivery_sn'] . $_LANG['detail'], 'href' => 'order.php?act=delivery_info&delivery_id=' . $delivery_id);
    sys_msg($_LANG['act_ok'], 0, $links); */

              
                

                
?>