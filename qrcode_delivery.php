<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once('./includes/lib_order.php');
include_once('./business/includes/lib_main.php');
$sql="select * from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];
$user_info=$db->getRow($sql);


$smarty->assign('appid', $appid);
$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);
//$smarty->assign('jssdk', $class_weixin->getJsApiTicket() );

if(empty($_REQUEST['order_id'])){
    die('<script> alert("参数错误"); window.location="index.php";</script>');
}
$order_id=$_REQUEST['order_id'];

/* 取得参数 */
$order_id   = intval(trim($_REQUEST['order_id']));        // 订单id
$operation  = "split";       // 订单操作
/* 查询订单信息 */
$order = order_info($order_id);
/* 检查能否操作 */
$operable_list = operable_list($order);
if (!isset($operable_list[$operation]))
{
    die('Hacking attempt');
}

$delivery=array();
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
    $links[] = array('text' => $_LANG['order_info'], 'href' => 'suppliers.php?act=order_info&order_id=' . $order_id);
    show_message($_LANG['act_false'], 1, $links);
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
            if (empty($value['extension_code']) || $value['extension_code'] == 'virtual_card')
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

die('<script> alert("验证成功，可以提货"); window.location="index.php";</script>');
//$smarty->display('qrcode_delivery.dwt');

?>
