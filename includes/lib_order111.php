<?php

/**
 * 小舍电商 购物流程函数库
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: lib_order.php 17217 2014-05-12 06:29:08Z pangbin $
 */

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}

/**
 * 处理序列化的支付、配送的配置参数
 * 返回一个以name为索引的数组
 *
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config($cfg)
{
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();

        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }

        return $config;
    }
    else
    {
        return false;
    }
}
/**
 * 取得已安装的配送方式
 * @return  array   已安装的配送方式
 */
function shipping_list()
{
    $sql = 'SELECT shipping_id, shipping_name ' .
            'FROM ' . $GLOBALS['hhs']->table('shipping') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得配送方式信息
 * @param   int     $shipping_id    配送方式id
 * @return  array   配送方式信息
 */
function shipping_info($shipping_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('shipping') .
            " WHERE shipping_id = '$shipping_id' " .
            'AND enabled = 1';

    return $GLOBALS['db']->getRow($sql);
}
/**
 * 取得用户当前可用红包
 * @param   int     $user_id        用户id
 * @param   float   $goods_amount   订单商品金额
 * @return  array   红包数组
 */
function team_user_bonus($user_id, $goods_amount = 0,$supp_id)
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $sql = "SELECT t.free_all,t.type_id, t.type_name, t.type_money, b.bonus_id, t.use_start_date, t.use_end_date, t.min_goods_amount " .
            "FROM " . $GLOBALS['hhs']->table('bonus_type') . " AS t," .
                $GLOBALS['hhs']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$goods_amount' " .
            "AND b.user_id<>0 " .
            "AND b.suppliers_id='$supp_id' " .
            "AND b.user_id = '$user_id' " .
            "AND b.order_id = 0 and t.free_all != 1 ";

    return $GLOBALS['db']->getAll($sql);
}
/**
 * 取得可用的配送方式列表
 * @param   array   $region_id_list     收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 */
function available_shipping_list($region_id_list)
{
    if($_SESSION['goods_suppliers_id']){
        $andwhere = " and a.`supp_id` = " . $_SESSION['goods_suppliers_id'] .
            " and a.`shipping_id` = s.`shipping_id` ";
    }
    else{
        $andwhere = " and a.`supp_id` = 0 ";
    }
    $sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, ' .
            's.shipping_desc, s.insure, s.support_cod, a.configure ' .
        'FROM ' . $GLOBALS['hhs']->table('shipping') . ' AS s, ' .
            $GLOBALS['hhs']->table('shipping_area') . ' AS a, ' .
            $GLOBALS['hhs']->table('area_region') . ' AS r ' .
        'WHERE r.region_id ' . db_create_in($region_id_list) .
        ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 '.$andwhere.' ORDER BY s.shipping_order';
    return $GLOBALS['db']->getAll($sql);
}
/**
 * 取得可用的自提点列表
 * @param   array   $region_id_list     收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 */
function available_shipping_point_list($region_id_list)
{
    //**
    if($_SESSION['goods_suppliers_id']){
        $andwhere = " and a.`suppliers_id` = " . $_SESSION['goods_suppliers_id'] .
            " ";
    }
    else{
        $andwhere = " and a.`suppliers_id` = 0 ";
    }
    $sql = "SELECT a.*,rp.region_name as province,rc.region_name as city,rd.region_name as district " .
                " FROM " . $GLOBALS['hhs']->table('shipping_point'). " AS a left join " .
                    $GLOBALS['hhs']->table('region') . " AS rp on a.province=rp.region_id left join ".
                    $GLOBALS['hhs']->table('region') . " as rc on a.city=rc.region_id left join ".
                    $GLOBALS['hhs']->table('region') ." as rd on a.district=rd.region_id ".
                    " WHERE rp.region_id " . db_create_in($region_id_list) . $andwhere;

    return $GLOBALS['db']->getAll($sql);
}
/**
 * 取得某配送方式对应于某收货地址的区域信息
 * @param   int     $shipping_id        配送方式id
 * @param   array   $region_id_list     收货人地区id数组
 * @return  array   配送区域信息（config 对应着反序列化的 configure）
 */
function shipping_area_info($shipping_id, $region_id_list)
{
    if($_SESSION['goods_suppliers_id']){
        $andwhere = " and a.`supp_id` = " . $_SESSION['goods_suppliers_id'] .
            " and a.`shipping_id` = s.`shipping_id` ";
    }
    else{
        $andwhere = " and a.`supp_id` = 0 ";
    }
    $sql = 'SELECT s.shipping_code, s.shipping_name, ' .
                's.shipping_desc, s.insure, s.support_cod, a.configure ' .
            'FROM ' . $GLOBALS['hhs']->table('shipping') . ' AS s, ' .
                $GLOBALS['hhs']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['hhs']->table('area_region') . ' AS r ' .
            "WHERE s.shipping_id = '$shipping_id' " .
            'AND r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 '.$andwhere.' ';

    $row = $GLOBALS['db']->getRow($sql);

    if (!empty($row))
    {
        $shipping_config = unserialize_config($row['configure']);
        if (isset($shipping_config['pay_fee']))
        {
            if (strpos($shipping_config['pay_fee'], '%') !== false)
            {
                $row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
            }
            else
            {
                 $row['pay_fee'] = floatval($shipping_config['pay_fee']);
            }
        }
        else
        {
            $row['pay_fee'] = 0.00;
        }
    }

    return $row;
}

/**
 * 计算运费
 * @param   string  $shipping_code      配送方式代码
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   运费
 */
function shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='')
{
    if (!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }

    $filename = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
    if (file_exists($filename))
    {
        include_once($filename);

        $obj = new $shipping_code($shipping_config);

        return $obj->calculate($goods_weight, $goods_amount, $goods_number);
    }
    else
    {
        return 0;
    }
}

/**
 * 获取指定配送的保价费用
 *
 * @access  public
 * @param   string      $shipping_code  配送方式的code
 * @param   float       $goods_amount   保价金额
 * @param   mix         $insure         保价比例
 * @return  float
 */
function shipping_insure_fee($shipping_code, $goods_amount, $insure)
{
    if (strpos($insure, '%') === false)
    {
        /* 如果保价费用不是百分比则直接返回该数值 */
        return floatval($insure);
    }
    else
    {
        $path = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';

        if (file_exists($path))
        {
            include_once($path);

            $shipping = new $shipping_code;
            $insure   = floatval($insure) / 100;

            if (method_exists($shipping, 'calculate_insure'))
            {
                return $shipping->calculate_insure($goods_amount, $insure);
            }
            else
            {
                return ceil($goods_amount * $insure);
            }
        }
        else
        {
            return false;
        }
    }
}

/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的配送方式列表
 */
function payment_list()
{
    $sql = 'SELECT pay_id, pay_name ' .
            'FROM ' . $GLOBALS['hhs']->table('payment') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得支付方式信息
 * @param   int     $pay_id     支付方式id
 * @return  array   支付方式信息
 */
function payment_info($pay_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('payment') .
            " WHERE pay_id = '$pay_id' AND enabled = 1";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获得订单需要支付的支付费用
 *
 * @access  public
 * @param   integer $payment_id
 * @param   float   $order_amount
 * @param   mix     $cod_fee
 * @return  float
 */
function pay_fee($payment_id, $order_amount, $cod_fee=null)
{
    $pay_fee = 0;
    $payment = payment_info($payment_id);
    $rate    = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

    if (strpos($rate, '%') !== false)
    {
        /* 支付费用是一个比例 */
        $val     = floatval($rate) / 100;
        $pay_fee = $val > 0 ? $order_amount * $val /(1- $val) : 0;
    }
    else
    {
        $pay_fee = floatval($rate);
    }

    return round($pay_fee, 2);
}

/**
 * 取得可用的支付方式列表
 * @param   bool    $support_cod        配送方式是否支持货到付款
 * @param   int     $cod_fee            货到付款手续费（当配送方式支持货到付款时才传此参数）
 * @param   int     $is_online          是否支持在线支付
 * @return  array   配送方式数组
 */
function available_payment_list($support_cod, $cod_fee = 0, $is_online = false)
{
    $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc, pay_config, is_cod' .
            ' FROM ' . $GLOBALS['hhs']->table('payment') .
            ' WHERE enabled = 1 ';
    if (!$support_cod)
    {
        $sql .= 'AND is_cod = 0 '; // 如果不支持货到付款
    }
    if ($is_online)
    {
        $sql .= "AND is_online = '1' ";
    }
    $sql .= 'ORDER BY pay_order'; // 排序
    $res = $GLOBALS['db']->query($sql);

    $pay_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['is_cod'] == '1')
        {
            $row['pay_fee'] = $cod_fee;
        }

        $row['format_pay_fee'] = strpos($row['pay_fee'], '%') !== false ? $row['pay_fee'] :
        price_format($row['pay_fee'], false);
        $modules[] = $row;
    }

    include_once(ROOT_PATH.'includes/lib_compositor.php');

    if(isset($modules))
    {
        return $modules;
    }
}

/**
 * 取得包装列表
 * @return  array   包装列表
 */
function pack_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('pack');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_pack_fee'] = price_format($row['pack_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得包装信息
 * @param   int     $pack_id    包装id
 * @return  array   包装信息
 */
function pack_info($pack_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('pack') .
            " WHERE pack_id = '$pack_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中的商品总额来获得包装的费用
 *
 * @access  public
 * @param   integer $pack_id
 * @param   float   $goods_amount
 * @return  float
 */
function pack_fee($pack_id, $goods_amount)
{
    $pack = pack_info($pack_id);

    $val = (floatval($pack['free_money']) <= $goods_amount && $pack['free_money'] > 0) ? 0 : floatval($pack['pack_fee']);

    return $val;
}

/**
 * 取得贺卡列表
 * @return  array   贺卡列表
 */
function card_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('card');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_card_fee'] = price_format($row['card_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得贺卡信息
 * @param   int     $card_id    贺卡id
 * @return  array   贺卡信息
 */
function card_info($card_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('card') .
            " WHERE card_id = '$card_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中商品总额获得需要支付的贺卡费用
 *
 * @access  public
 * @param   integer $card_id
 * @param   float   $goods_amount
 * @return  float
 */
function card_fee($card_id, $goods_amount)
{
    $card = card_info($card_id);

    return ($card['free_money'] <= $goods_amount && $card['free_money'] > 0) ? 0 : $card['card_fee'];
}

/**
 * 取得订单信息
 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param   string  $order_sn   订单号
 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function order_info($order_id, $order_sn = '')
{
    /* 计算订单各种费用之和的语句 */
    $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
    $order_id = intval($order_id);
    if ($order_id > 0)
    {
        $sql = "SELECT *, " . $total_fee . " FROM " . $GLOBALS['hhs']->table('order_info') .
                " WHERE order_id = '$order_id'";
    }
    else
    {
        $sql = "SELECT *, " . $total_fee . "  FROM " . $GLOBALS['hhs']->table('order_info') .
                " WHERE order_sn = '$order_sn'";
    }
    $order = $GLOBALS['db']->getRow($sql);

    // if($order['extension_code']=='team_goods'&&$order['team_status']==1){
    //     $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$order['team_sign'];
    //     $pay_time=$GLOBALS['db']->getOne($sql);
    //     if($pay_time+$GLOBALS['_CFG']['team_suc_time']*24*3600<gmtime()){
    //         //取消订单
    //         $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2  where team_status=1 and team_sign=".$order['team_sign'];
    //         $GLOBALS['db']->query($sql);
    //         $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$order['team_sign'];
    //         $GLOBALS['db']->query($sql);
    //     }  
    // }
    /* 格式化金额字段 */
    if ($order)
    {
        $order['formated_goods_amount']   = price_format($order['goods_amount'], false);
        $order['formated_discount']       = price_format($order['discount'], false);
        $order['formated_tax']            = price_format($order['tax'], false);
        $order['formated_shipping_fee']   = price_format($order['shipping_fee'], false);
        $order['formated_insure_fee']     = price_format($order['insure_fee'], false);
        $order['formated_pay_fee']        = price_format($order['pay_fee'], false);
        $order['formated_pack_fee']       = price_format($order['pack_fee'], false);
        $order['formated_card_fee']       = price_format($order['card_fee'], false);
        $order['formated_total_fee']      = price_format($order['total_fee'], false);
        $order['formated_money_paid']     = price_format($order['money_paid'], false);
        $order['formated_bonus']          = price_format($order['bonus'], false);
        $order['formated_integral_money'] = price_format($order['integral_money'], false);
        $order['formated_surplus']        = price_format($order['surplus'], false);
        $order['formated_order_amount']   = price_format(abs($order['order_amount']), false);
        $order['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
    }

    return $order;
}

/**
 * 判断订单是否已完成
 * @param   array   $order  订单信息
 * @return  bool
 */
function order_finished($order)
{
    return $order['order_status']  == OS_CONFIRMED &&
        ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) &&
        ($order['pay_status']      == PS_PAYED   || $order['pay_status'] == PS_PAYING);
}

/**
 * 取得订单商品
 * @param   int     $order_id   订单id
 * @return  array   订单商品数组
 */
function order_goods($order_id)
{
    $sql = "SELECT og.rec_id, og.goods_id, og.goods_name, og.goods_attr, og.goods_sn, og.market_price, og.goods_number, " .  "og.goods_price, og.goods_attr, og.is_real, og.parent_id, og.is_gift, " .  "og.goods_price * og.goods_number AS subtotal, og.extension_code,g.goods_thumb,g.little_img,g.goods_brief " . "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS og ".  " LEFT JOIN ". $GLOBALS['hhs']->table('goods') . " AS g on og.goods_id=g.goods_id " . " WHERE order_id = '$order_id'";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;
    }

    //return $GLOBALS['db']->getAll($sql);
    return $goods_list;
}

/**
 * 取得订单总金额
 * @param   int     $order_id   订单id
 * @param   bool    $include_gift   是否包括赠品
 * @return  float   订单总金额
 */
function order_amount($order_id, $include_gift = true)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['hhs']->table('order_goods') .
            " WHERE order_id = '$order_id'";
    if (!$include_gift)
    {
        $sql .= " AND is_gift = 0";
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单商品总重量和总金额（对应 cart_weight_price）
 * @param   int     $order_id   订单id
 * @return  array   ('weight' => **, 'amount' => **, 'formated_weight' => **)
 */
function order_weight_price($order_id)
{
    $sql = "SELECT SUM(g.goods_weight * o.goods_number) AS weight, " .
                "SUM(o.goods_price * o.goods_number) AS amount ," .
                "SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS o, " .
                $GLOBALS['hhs']->table('goods') . " AS g " .
            "WHERE o.order_id = '$order_id' " .
            "AND o.goods_id = g.goods_id";

    $row = $GLOBALS['db']->getRow($sql);
    $row['weight'] = floatval($row['weight']);
    $row['amount'] = floatval($row['amount']);
    $row['number'] = intval($row['number']);

    /* 格式化重量 */
    $row['formated_weight'] = formated_weight($row['weight']);

    return $row;
}

/**
 * 获得订单中的费用信息
 *
 * @access  public
 * @param   array   $order
 * @param   array   $goods
 * @param   array   $consignee
 * @param   bool    $is_gb_deposit  是否团购保证金（如果是，应付款金额只计算商品总额和支付费用，可以获得的积分取 $gift_integral）
 * @return  array
 */
function order_fee($order, $goods, $consignee)
{
    global $db,$hhs;
    /* 初始化订单的扩展code */
    if (!isset($order['extension_code']))
    {
        $order['extension_code'] = '';
    }

    if ($order['extension_code'] == 'group_buy')
    {
        $group_buy = group_buy_info($order['extension_id']);
    }

    $total  = array('real_goods_count' => 0,
                    'gift_amount'      => 0,
                    'goods_price'      => 0,
                    'market_price'     => 0,
                    'discount'         => 0,
                    'pack_fee'         => 0,
                    'card_fee'         => 0,
                    'shipping_fee'     => 0,
                    'shipping_insure'  => 0,
                    'integral_money'   => 0,
                    'bonus'            => 0,
                    'surplus'          => 0,
                    'cod_fee'          => 0,
                    'pay_fee'          => 0,
                    'tax'              => 0,
                    'amount'                => 0,
                    'amount_formated'       => '0.00',
                    'shipping_fee_formated' => '0.00',
    );
    $weight = 0;

    /* 商品总价 */
    foreach ($goods AS $val)
    {
        /* 统计实体商品的个数 */
        if ($val['is_real'])
        {
            $total['real_goods_count']++;
        }

        $total['goods_price']  += $val['goods_price'] * $val['goods_number'];
        $total['market_price'] += $val['market_price'] * $val['goods_number'];
    }

    $total['saving']    = $total['market_price'] - $total['goods_price'];
    $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    $total['market_price_formated'] = price_format($total['market_price'], false);
    $total['saving_formated']       = price_format($total['saving'], false);

    /* 折扣 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'team_goods')
    {
        $discount = compute_discount();
        $total['discount'] = $discount['discount'];
        if ($total['discount'] > $total['goods_price'])
        {
            $total['discount'] = $total['goods_price'];
        }
    }
    $total['discount_formated'] = price_format($total['discount'], false);

    /* 税额 */
    if (!empty($order['need_inv']) && $order['inv_type'] != '')
    {
        /* 查税率 */
        $rate = 0;
        foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type)
        {
            if ($type == $order['inv_type'])
            {
                $rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
                break;
            }
        }
        if ($rate > 0)
        {
            $total['tax'] = $rate * $total['goods_price'];
        }
    }
    $total['tax_formated'] = price_format($total['tax'], false);

    /* 包装费用 */
    if (!empty($order['pack_id']))
    {
        $total['pack_fee']      = pack_fee($order['pack_id'], $total['goods_price']);
    }
    $total['pack_fee_formated'] = price_format($total['pack_fee'], false);

    /* 贺卡费用 */
    if (!empty($order['card_id']))
    {
        $total['card_fee']      = card_fee($order['card_id'], $total['goods_price']);
    }
    $total['card_fee_formated'] = price_format($total['card_fee'], false);

    /* 优惠劵 */

    if (!empty($order['bonus_id']))
    {
        $bonus          = bonus_info($order['bonus_id']);
        // 免单券
        if($bonus['free_all'] == 1)
        {
            return $total;
        }
        $total['bonus'] = $bonus['type_money'];
    }
    $total['bonus_formated'] = price_format($total['bonus'], false);

    /* 线下优惠劵 */
     if (!empty($order['bonus_kill']))
    {
        $bonus          = bonus_info(0,$order['bonus_kill']);
        $total['bonus_kill'] = $order['bonus_kill'];
        $total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
    }



    /* 配送费用 */
    //0元购的邮费
    if($goods[0]['is_zero'] == 1 )
    {
        $total['shipping_fee'] = $goods[0]['shipping_fee'];
        $total['shipping_insure'] = 0;
    }
    elseif($_SESSION['is_luck'] == 1 )
    {
        $total['shipping_fee'] = 0.00;
        $total['shipping_insure'] = 0;
    }
    else
    {
        $shipping_cod_fee = NULL;

        if ($order['shipping_id'] > 0 && $total['real_goods_count'] > 0)
        {
            // 邮费模板附带 express_id
            if(! empty($_SESSION['express_id']))
            {
                $express_id = $_SESSION['express_id'];

                $express = $db->getRow('SELECT * FROM '.$hhs->table('goods_express').' WHERE id=' . $express_id);
                $configure = array(
                    array('name'=>'item_fee','value'=>$express['shipping_fee']),
                    array('name'=>'base_fee','value'=>$express['shipping_fee']),
                    array('name'=>'basic_fee','value'=>$express['shipping_fee']),
                    array('name'=>'step_fee','value'=>$express['step_fee']),
                    array('name'=>'fee_compute_mode','value'=>$express['fee_compute_mode']),
                    array('name'=>'free_money','value'=>''),
                    array('name'=>'pay_fee','value'=>''),
                );
                $shipping_info = array(
                    'shipping_code' => $express['shipping_code'],
                    'shipping_name' => $express['shipping_name'],
                    'pay_fee'       => 0,
                    'insure'        => 0,
                    'support_cod'   => 0,
                    'configure'     => $configure,
                );
            }
            else{
                $region['country']  = $consignee['country'];
                $region['province'] = $consignee['province'];
                $region['city']     = $consignee['city'];
                $region['district'] = $consignee['district'];
                $shipping_info = shipping_area_info($order['shipping_id'], $region);
            }

            if (!empty($shipping_info))
            {
                if ($order['extension_code'] == 'group_buy')
                {
                    $weight_price = cart_weight_price(CART_GROUP_BUY_GOODS);
                }
                elseif($order['extension_code'] == 'team_goods'){
                    $weight_price = cart_weight_price(5);
                }
                else
                {
                    $weight_price = cart_weight_price();
                }

                // 查看购物车中是否全为免运费商品，若是则把运费赋为零
                $sql = 'SELECT count(*) FROM ' . $GLOBALS['hhs']->table('cart') . " WHERE  `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
                $shipping_count = $GLOBALS['db']->getOne($sql);

                $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);

                if (!empty($order['need_insure']) && $shipping_info['insure'] > 0)
                {
                    $total['shipping_insure'] = shipping_insure_fee($shipping_info['shipping_code'],
                        $total['goods_price'], $shipping_info['insure']);
                }
                else
                {
                    $total['shipping_insure'] = 0;
                }

                if ($shipping_info['support_cod'])
                {
                    $shipping_cod_fee = $shipping_info['pay_fee'];
                }
            }
        }        
    }


    $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);
    $total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);

    // 购物车中的商品能享受优惠劵支付的总额
    $bonus_amount = compute_discount_amount();
    // 优惠劵和积分最多能支付的金额为商品总额
    $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

    /* 计算订单总额 */
    if ($order['extension_code'] == 'group_buy' && $group_buy['deposit'] > 0)
    {
        $total['amount'] = $total['goods_price'];
    }
    else
    {
        $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
            $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

        // 减去优惠劵金额
        $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的优惠劵金额
        if(isset($total['bonus_kill']))
        {
            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
            $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
        }

        $total['bonus']   = $use_bonus;
        $total['bonus_formated'] = price_format($total['bonus'], false);

        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
        $max_amount      -= $use_bonus; // 积分最多还能支付的金额

    }

    /* 余额 */
    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0)
    {
        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
        {
            $order['surplus'] = $total['amount'];
            $total['amount']  = 0;
        }
        else
        {
            $total['amount'] -= floatval($order['surplus']);
        }
    }
    else
    {
        $order['surplus'] = 0;
        $total['amount']  = 0;
    }
    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = price_format($order['surplus'], false);

    /* 积分 */
    $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
    if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0)
    {
        $integral_money = value_of_integral($order['integral']);

        // 使用积分支付
        $use_integral            = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
        $total['amount']        -= $use_integral;
        $total['integral_money'] = $use_integral;
        $order['integral']       = integral_of_value($use_integral);
    }
    else
    {
        $total['integral_money'] = 0;
        $order['integral']       = 0;
    }
    $total['integral'] = $order['integral'];
    $total['integral_formated'] = price_format($total['integral_money'], false);

    /* 保存订单信息 */
    $_SESSION['flow_order'] = $order;

    $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';
    
    /* 支付费用 */
    if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS))
    {
        $total['pay_fee']      = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
    }

    $total['pay_fee_formated'] = price_format($total['pay_fee'], false);

    $total['amount']           += $total['pay_fee']; // 订单总额累加上支付费用
    $total['amount_formated']  = price_format($total['amount'], false);

    /* 取得可以得到的积分和优惠劵 */
    if ($order['extension_code'] == 'group_buy')
    {
        $total['will_get_integral'] = $group_buy['gift_integral'];
    }
    elseif ($order['extension_code'] == 'exchange_goods')
    {
        $total['will_get_integral'] = 0;
    }
    else
    {
        $total['will_get_integral'] = get_give_integral($goods);
    }
    $total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus(), false);
    $total['formated_goods_price']  = price_format($total['goods_price'], false);
    $total['formated_market_price'] = price_format($total['market_price'], false);
    $total['formated_saving']       = price_format($total['saving'], false);

    if ($order['extension_code'] == 'exchange_goods')
    {
        $sql = 'SELECT SUM(eg.exchange_integral) '.
               'FROM ' . $GLOBALS['hhs']->table('cart') . ' AS c,' . $GLOBALS['hhs']->table('exchange_goods') . 'AS eg '.
               "WHERE c.goods_id = eg.goods_id AND c.session_id= '" . SESS_ID . "' " .
               "  AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' " .
               '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
               'GROUP BY eg.goods_id';
        $exchange_integral = $GLOBALS['db']->getOne($sql);
        $total['exchange_integral'] = $exchange_integral;
    }

    return $total;
}

/**
 * 修改订单
 * @param   int     $order_id   订单id
 * @param   array   $order      key => value
 * @return  bool
 */
function update_order($order_id, $order)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('order_info'),
        $order, 'UPDATE', "order_id = '$order_id'");
}

/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 取得购物车商品
 * @param   int     $type   类型：默认普通商品
 * @return  array   购物车商品数组
 */
function cart_goods($type = CART_GENERAL_GOODS)
{
    $sql = "SELECT rec_id, user_id, goods_id, goods_name, goods_sn, goods_number, " .
            "market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, is_shipping, city_id,district_id,suppliers_id,shipping_fee,is_zero,bonus_allowed," .
            "goods_price * goods_number AS subtotal " .
            "FROM " . $GLOBALS['hhs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '$type'";

    $arr = $GLOBALS['db']->getAll($sql);

    /* 格式化价格及礼包商品 */
    foreach ($arr as $key => $value)
    {
        $goods_thumb = $GLOBALS['db']->getOne("SELECT `goods_thumb` FROM " . $GLOBALS['hhs']->table('goods') . " WHERE `goods_id`=".$arr[$key]['goods_id']);
        $arr[$key]['goods_thumb'] = get_image_path($arr[$key]['goods_id'], $goods_thumb, true);
        $little_img =$arr[$key]['little_img'] = $GLOBALS['db']->getOne("SELECT `little_img` FROM " . $GLOBALS['hhs']->table('goods') . " WHERE `goods_id`=".$arr[$key]['goods_id']);
        
        //$goods_kucun = $GLOBALS['db']->getOne("SELECT `goods_number` FROM " . $GLOBALS['hhs']->table('goods') . " WHERE `goods_id`=".$arr[$key]['goods_id']);
        $arr[$key]['goods_kucun'] = $GLOBALS['db']->getOne("SELECT `goods_number` FROM " . $GLOBALS['hhs']->table('goods') . " WHERE `goods_id`=".$arr[$key]['goods_id']);
        
        $arr[$key]['formated_market_price'] = price_format($value['market_price'], false);
        $arr[$key]['formated_goods_price']  = price_format($value['goods_price'], false);
        $arr[$key]['formated_subtotal']     = price_format($value['subtotal'], false);

        if ($value['extension_code'] == 'package_buy')
        {
            $arr[$key]['package_goods_list'] = get_package_goods($value['goods_id']);
        }
    }

    return $arr;
}

/**
 * 取得购物车总金额
 * @params  boolean $include_gift   是否包括赠品
 * @param   int     $type           类型：默认普通商品
 * @return  float   购物车总金额
 */
function cart_amount($include_gift = true, $type = CART_GENERAL_GOODS)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            " FROM " . $GLOBALS['hhs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '$type' ";

    if (!$include_gift)
    {
        $sql .= ' AND is_gift = 0 AND goods_id > 0';
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 检查某商品是否已经存在于购物车
 *
 * @access  public
 * @param   integer     $id
 * @param   array       $spec
 * @param   int         $type   类型：默认普通商品
 * @return  boolean
 */
function cart_goods_exists($id, $spec, $type = CART_GENERAL_GOODS)
{
    /* 检查该商品是否已经存在在购物车中 */
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('cart').
            "WHERE session_id = '" .SESS_ID. "' AND goods_id = '$id' ".
            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
            "AND rec_type = '$type'";

    return ($GLOBALS['db']->getOne($sql) > 0);
}

/**
 * 获得购物车中商品的总重量、总价格、总数量
 *
 * @access  public
 * @param   int     $type   类型：默认普通商品
 * @return  array
 */
function cart_weight_price($type = CART_GENERAL_GOODS)
{

    $package_row['weight'] = 0;
    $package_row['amount'] = 0;
    $package_row['number'] = 0;

    $packages_row['free_shipping'] = 1;

    /* 计算超值礼包内商品的相关配送参数 */
    $sql = 'SELECT goods_id, goods_number, goods_price FROM ' . $GLOBALS['hhs']->table('cart') . " WHERE extension_code = 'package_buy' AND session_id = '" . SESS_ID . "'";
    $row = $GLOBALS['db']->getAll($sql);

    if ($row)
    {
        $packages_row['free_shipping'] = 0;
        $free_shipping_count = 0;

        foreach ($row as $val)
        {
            // 如果商品全为免运费商品，设置一个标识变量
            $sql = 'SELECT count(*) FROM ' .
                    $GLOBALS['hhs']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['hhs']->table('goods') . ' AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";
            $shipping_count = $GLOBALS['db']->getOne($sql);

            if ($shipping_count > 0)
            {
                // 循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                $sql = 'SELECT SUM(g.goods_weight * pg.goods_number) AS weight, ' .
                    'SUM(pg.goods_number) AS number FROM ' .
                    $GLOBALS['hhs']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['hhs']->table('goods') . ' AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";

                $goods_row = $GLOBALS['db']->getRow($sql);
                $package_row['weight'] += floatval($goods_row['weight']) * $val['goods_number'];
                $package_row['amount'] += floatval($val['goods_price']) * $val['goods_number'];
                $package_row['number'] += intval($goods_row['number']) * $val['goods_number'];
            }
            else
            {
                $free_shipping_count++;
            }
        }

        $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
    }

    /* 获得购物车中非超值礼包商品的总重量 */
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
                    'SUM(c.goods_price * c.goods_number) AS amount, ' .
                    'SUM(c.goods_number) AS number '.
                'FROM ' . $GLOBALS['hhs']->table('cart') . ' AS c '.
                'LEFT JOIN ' . $GLOBALS['hhs']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
                "WHERE c.session_id = '" . SESS_ID . "' " .
                "AND rec_type = '$type' AND g.is_shipping = 0 AND c.extension_code != 'package_buy'";
    $row = $GLOBALS['db']->getRow($sql);

    $packages_row['weight'] = floatval($row['weight']) + $package_row['weight'];
    $packages_row['amount'] = floatval($row['amount']) + $package_row['amount'];
    $packages_row['number'] = intval($row['number']) + $package_row['number'];
    /* 格式化重量 */
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);

    return $packages_row;
}

/**
 * 添加商品到购物车
 *
 * @access  public
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格值对应的id数组
 * @param   integer $parent     基本件
 * @return  boolean
 */
function addto_cart($goods_id, $num = 1, $spec = array(), $parent = 0,$rec_type=0,$team_sign=0)
{
    $GLOBALS['err']->clean();
    $_parent_id = $parent;
    clear_cart(0);
    clear_cart(5);
    /* 取得商品信息 */
    $sql = "SELECT g.rate_1,g.rate_2,g.rate_3,g.is_miao,g.luck_times, g.is_luck, g.suppliers_id,g.city_id,g.district_id,g.team_num,g.team_price,g.goods_name, g.goods_sn, g.is_on_sale, g.is_real,g.is_zero,g.shipping_fee,g.is_team,g.bonus_allowed, ".
                "g.market_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, ".
                "g.promote_end_date, g.goods_weight, g.integral, g.extension_code, ".
                "g.goods_number, g.is_alone_sale, g.is_shipping,".
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
            " FROM " .$GLOBALS['hhs']->table('goods'). " AS g ".
            " LEFT JOIN " . $GLOBALS['hhs']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " WHERE g.goods_id = '$goods_id'" .
            " AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if (empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

        return false;
    }
    //抽奖产品，存一下 what ? insert order 有用
    $_SESSION['is_luck']    = $goods['is_luck'];
    $_SESSION['luck_times'] = $goods['luck_times'];
    $_SESSION['is_miao']    = $goods['is_miao'];

    // 抽奖产品重新定义team_sign
    if($_SESSION['is_luck']){
       $luck_row = $GLOBALS['db']->getRow('select team_sign,(team_num-teammen_num) as left_num from '.$GLOBALS['hhs']->table('order_info').' where extension_id="'.$goods_id.'" and team_first = 1 AND team_status = 1 AND pay_status=2 and luck_times = "'.$_SESSION['luck_times'].'" order by order_id desc');
       $_SESSION['team_sign'] = isset($luck_row['team_sign']) ? intval($luck_row['team_sign']) : 0;
       $goods['goods_number'] = isset($luck_row['left_num']) ? intval($luck_row['left_num']) : $goods['goods_number'];
    }

    /* 如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件 */
    if ($parent > 0)
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('cart') .
                " WHERE goods_id='$parent' AND session_id='" . SESS_ID . "' AND extension_code <> 'package_buy'";
        if ($GLOBALS['db']->getOne($sql) == 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);

            return false;
        }
    }

    /* 是否正在销售 */
    if ($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

        return false;
    }

    /* 不是配件时检查是否允许单独销售 */
    if (empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);

        return false;
    }

    /* 如果商品有规格则取规格商品信息 配件除外 */
    $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('products'). " WHERE goods_id = '$goods_id' LIMIT 0, 1";
    $prod = $GLOBALS['db']->getRow($sql);

    if (is_spec($spec) && !empty($prod))
    {
        $product_info = get_products_info($goods_id, $spec);
    }
    if (empty($product_info))
    {
        $product_info = array('product_number' => '', 'product_id' => 0);
    }

    /* 检查：库存 */
    if ($GLOBALS['_CFG']['use_storage'] == 1)
    {
        //检查：商品购买数量是否大于总库存
        if ($num > $goods['goods_number'])
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $goods['goods_number']), ERR_OUT_OF_STOCK);

            return false;
        }

        //商品存在规格 是货品 检查该货品库存
        if (is_spec($spec) && !empty($prod))
        {
            if (!empty($spec))
            {
                /* 取规格的货品库存 */
                if ($num > $product_info['product_number'])
                {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $product_info['product_number']), ERR_OUT_OF_STOCK);
    
                    return false;
                }
            }
        }       
    }

    /* 计算商品的促销价格 */
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);

    /* 初始化要插入购物车的基本件数据 */
    $parent = array(
        'user_id'        => $_SESSION['user_id'],
        'session_id'     => SESS_ID,
        'goods_id'       => $goods_id,
        'goods_sn'       => addslashes($goods['goods_sn']),
        'product_id'     => $product_info['product_id'],
        'goods_name'     => addslashes($goods['goods_name']),
        'market_price'   => $goods['market_price'],
        'goods_attr'     => addslashes($goods_attr),
        'goods_attr_id'  => $goods_attr_id,
        'is_real'        => $goods['is_real'],
        'extension_code' => $goods['extension_code'],
        'is_gift'        => 0,
        'is_shipping'    => $goods['is_shipping'],
        'rec_type'       => $rec_type,
        'team_sign'      => $team_sign,
        'city_id'        => $goods['city_id'],
        'district_id'    => $goods['district_id'],
        'suppliers_id'   => $goods['suppliers_id'],
        
        'bonus_allowed'  => $goods['bonus_allowed'],
        'is_zero'        => $goods['is_zero'],
        'shipping_fee'   => $goods['shipping_fee'],
        'is_team'        => $goods['is_team'],
        'rate_1'        => $goods['rate_1'],
        'rate_2'        => $goods['rate_2'],
        'rate_3'        => $goods['rate_3'],

    );

    /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
    /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
    /* 受此优惠 */
    $basic_list = array();
    $sql = "SELECT parent_id, goods_price " .
            "FROM " . $GLOBALS['hhs']->table('group_goods') .
            " WHERE goods_id = '$goods_id'" .
            " AND goods_price < '$goods_price'" .
            " AND parent_id = '$_parent_id'" .
            " ORDER BY goods_price";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $basic_list[$row['parent_id']] = $row['goods_price'];
    }

    /* 取得购物车中该商品每个基本件的数量 */
    $basic_count_list = array();
    if ($basic_list)
    {
        $sql = "SELECT goods_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['hhs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND parent_id = 0" .
                " AND extension_code <> 'package_buy' " .
                " AND goods_id " . db_create_in(array_keys($basic_list)) .
                " GROUP BY goods_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['goods_id']] = $row['count'];
        }
    }

    /* 取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件 */
    /* 一个基本件对应一个该商品配件 */
    if ($basic_count_list)
    {
        $sql = "SELECT parent_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['hhs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND goods_id = '$goods_id'" .
                " AND extension_code <> 'package_buy' " .
                " AND parent_id " . db_create_in(array_keys($basic_count_list)) .
                " GROUP BY parent_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['parent_id']] -= $row['count'];
        }
    }

    /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
    foreach ($basic_list as $parent_id => $fitting_price)
    {
        /* 如果已全部插入，退出 */
        if ($num <= 0)
        {
            break;
        }

        /* 如果该基本件不再购物车中，执行下一个 */
        if (!isset($basic_count_list[$parent_id]))
        {
            continue;
        }

        /* 如果该基本件的配件数量已满，执行下一个基本件 */
        if ($basic_count_list[$parent_id] <= 0)
        {
            continue;
        }

        /* 作为该基本件的配件插入 */
        $parent['goods_price']  = max($fitting_price, 0) + $spec_price; //允许该配件优惠价格为0
        $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
        $parent['parent_id']    = $parent_id;

        /* 添加 */
        $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('cart'), $parent, 'INSERT');

        /* 改变数量 */
        $num -= $parent['goods_number'];
    }

    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
        /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT goods_number FROM " .$GLOBALS['hhs']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
                " AND extension_code <> 'package_buy' " .
                " AND rec_type = '$rec_type'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新
        {
            $num += $row['goods_number'];
            if(is_spec($spec) && !empty($prod) )
            {
             $goods_storage=$product_info['product_number'];
            }
            else
            {
                $goods_storage=$goods['goods_number'];
            }
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods_storage)
            {
                //下面的不执行
                $goods_price = get_final_price($goods_id, $num, true, $spec);
                $sql = "UPDATE " . $GLOBALS['hhs']->table('cart') . " SET goods_number = '$num'" .
                       " , goods_price = '$goods_price'".
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                       " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
                       " AND extension_code <> 'package_buy' " .
                       "AND rec_type = '$rec_type'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
               $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);

                return false;
            }
        }
        else //购物车没有此物品，则插入
        {
            $goods_price = get_final_price($goods_id, $num, true, $spec);
            $parent['goods_price']  = max($goods_price, 0);
            if($rec_type==5){
                /**
                 * 秒殺的修正價格
                 */
                if ($goods['is_miao']) {
                    $promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
                    if($promote_price>0)
                    {
                        $goods['team_price'] = $goods['promote_price'];
                    }
                }
                $parent['goods_price']=$goods['team_price']+spec_price($spec,true);
            }
            
            $parent['goods_number'] = $num;
            $parent['parent_id']    = 0;
            $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('cart'), $parent, 'INSERT');
        }
    }

    /* 把赠品删除 */
    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);

    return true;
}

/**
 * 清空购物车
 * @param   int     $type   类型：默认普通商品
 */
function clear_cart($type = CART_GENERAL_GOODS)
{
    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' AND rec_type = '$type'";
    $GLOBALS['db']->query($sql);
}

/**
 * 获得指定的商品属性
 *
 * @access      public
 * @param       array       $arr        规格、属性ID数组
 * @param       type        $type       设置返回结果类型：pice，显示价格，默认；no，不显示价格
 *
 * @return      string
 */
function get_goods_attr_info($arr, $type = 'pice')
{
    $attr   = '';

    if (!empty($arr))
    {
        $fmt = "%s:%s \n";
        // $fmt = "%s:%s[%s] \n";

        $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price ".
                "FROM ".$GLOBALS['hhs']->table('goods_attr')." AS ga, ".
                    $GLOBALS['hhs']->table('attribute')." AS a ".
                "WHERE " .db_create_in($arr, 'ga.goods_attr_id')." AND a.attr_id = ga.attr_id";
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $attr_price = round(floatval($row['attr_price']), 2);
            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value']);
            // $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function user_info($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('users') .
            " WHERE user_id = '$user_id'";
    $user = $GLOBALS['db']->getRow($sql);

    unset($user['question']);
    unset($user['answer']);

    /* 格式化帐户余额 */
    if ($user)
    {
//        if ($user['user_money'] < 0)
//        {
//            $user['user_money'] = 0;
//        }
        $user['formated_user_money'] = price_format($user['user_money'], false);
        $user['formated_frozen_money'] = price_format($user['frozen_money'], false);
    }

    return $user;
}

/**
 * 修改用户
 * @param   int     $user_id   订单id
 * @param   array   $user      key => value
 * @return  bool
 */
function update_user($user_id, $user)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('users'),
        $user, 'UPDATE', "user_id = '$user_id'");
}

/**
 * 取得用户地址列表
 * @param   int     $user_id    用户id
 * @return  array
 */
function address_list($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('user_address') .
            " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得用户地址信息
 * @param   int     $address_id     地址id
 * @return  array
 */
function address_info($address_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('user_address') .
            " WHERE address_id = '$address_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得用户当前可用优惠劵
 * @param   int     $user_id        用户id
 * @param   float   $goods_amount   订单商品金额
 * @return  array   优惠劵数组
 */
function user_bonus($user_id, $goods_amount = 0,$team_first=false, $type_id = false, $free_all = false, $suppliers_id)
{
    $day   = getdate();
    $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
    $where = '';
    if($team_first){//团长
        
    }else{
        $where .=" and t.only_first=0 ";
    }

    // 指定优惠券
    if($type_id)
    {
        $where .= " and t.type_id= '".$type_id."' ";
    }
    // 免单券
    if($free_all)
    {
        $where .= " and t.free_all= 1 ";
    }
    else{
        $where .= " AND t.min_goods_amount <= '$goods_amount' ";
    }
    $sql = "SELECT t.min_goods_amount,t.free_all, t.type_id, t.type_name, t.type_money, b.bonus_id,t.use_start_date, t.use_end_date  " .
        "FROM " . $GLOBALS['hhs']->table('bonus_type') . " AS t," .
        $GLOBALS['hhs']->table('user_bonus') . " AS b " .
        "WHERE t.type_id = b.bonus_type_id " .
        "AND t.use_start_date <= '$today' " .
        "AND t.use_end_date >= '$today' " .
        // "AND t.min_goods_amount <= '$goods_amount' " .
        "AND b.user_id<>0 " .
        "AND b.user_id = '$user_id' " .
        "AND t.suppliers_id = '$suppliers_id' " .
        "AND b.order_id = 0 ".$where;

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得优惠劵信息
 * @param   int     $bonus_id   优惠劵id
 * @param   string  $bonus_sn   优惠劵序列号
 * @param   array   优惠劵信息
 */
function bonus_info($bonus_id, $bonus_sn = '')
{
    $sql = "SELECT t.*, b.* " .
            "FROM " . $GLOBALS['hhs']->table('bonus_type') . " AS t," .
                $GLOBALS['hhs']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id ";
    if ($bonus_id > 0)
    {
        $sql .= "AND b.bonus_id = '$bonus_id'";
    }
    else
    {
        $sql .= "AND b.bonus_sn = '$bonus_sn'";
    }

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 检查优惠劵是否已使用
 * @param   int $bonus_id   优惠劵id
 * @return  bool
 */
function bonus_used($bonus_id)
{
    $sql = "SELECT order_id FROM " . $GLOBALS['hhs']->table('user_bonus') .
            " WHERE bonus_id = '$bonus_id'";

    return  $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 设置优惠劵为已使用
 * @param   int     $bonus_id   优惠劵id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function use_bonus($bonus_id, $order_id)
{
    $sql = "UPDATE " . $GLOBALS['hhs']->table('user_bonus') .
            " SET order_id = '$order_id', used_time = '" . gmtime() . "' " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

    $res = $GLOBALS['db']->query($sql);

    # 是否免单券
    $sql = "SELECT b.free_all from " . $GLOBALS['hhs']->table('user_bonus') .
            " as u, " . $GLOBALS['hhs']->table('bonus_type') .
            " as b WHERE u.bonus_id = '$bonus_id' AND u.`bonus_type_id` = b.`type_id`";

    $free_all = $GLOBALS['db']->getOne($sql);
    # 修改优惠券金额
    if ($free_all > 0) {
        $sql = "UPDATE " . $GLOBALS['hhs']->table('order_info') .
                " SET `bonus` = `goods_amount` + `shipping_fee` " .
                "WHERE order_id = '$order_id' LIMIT 1";

        $GLOBALS['db']->query($sql);
    }
    return $res;
}

/**
 * 设置优惠劵为未使用
 * @param   int     $bonus_id   优惠劵id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function unuse_bonus($bonus_id)
{
    $sql = "UPDATE " . $GLOBALS['hhs']->table('user_bonus') .
            " SET order_id = 0, used_time = 0 " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

    return  $GLOBALS['db']->query($sql);
}

/**
 * 计算积分的价值（能抵多少钱）
 * @param   int     $integral   积分
 * @return  float   积分价值
 */
function value_of_integral($integral)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
}

/**
 * 计算指定的金额需要多少积分
 *
 * @access  public
 * @param   integer $value  金额
 * @return  void
 */
function integral_of_value($value)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round($value / $scale * 100) : 0;
}

/**
 * 订单退款
 * @param   array   $order          订单
 * @param   int     $refund_type    退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param   string  $refund_note    退款说明
 * @param   float   $refund_amount  退款金额（如果为0，取订单已付款金额）
 * @return  bool
 */
function order_refund($order, $refund_type, $refund_note, $refund_amount = 0)
{
    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1)
    {
        die('anonymous, cannot return to account balance');
    }

    $amount = $refund_amount > 0 ? $refund_amount : $order['money_paid'];
    if ($amount <= 0)
    {
        return true;
    }

    if (!in_array($refund_type, array(1, 2, 3)))
    {
        die('invalid params');
    }

    /* 备注信息 */
    if ($refund_note)
    {
        $change_desc = $refund_note;
    }
    else
    {
        include_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/admin/order.php');
        $change_desc = sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']);
    }

    /* 处理退款 */
    if (1 == $refund_type)
    {
        log_account_change($user_id, $amount, 0, 0, 0, $change_desc);

        return true;
    }
    elseif (2 == $refund_type)
    {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0)
        {
            log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
        }

        /* user_account 表增加提款申请记录 */
        $account = array(
            'user_id'      => $user_id,
            'amount'       => (-1) * $amount,
            'add_time'     => gmtime(),
            'user_note'    => $refund_note,
            'process_type' => SURPLUS_RETURN,
            'admin_user'   => $_SESSION['admin_name'],
            'admin_note'   => sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']),
            'is_paid'      => 0
        );
        $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('user_account'), $account, 'INSERT');

        return true;
    }
    else
    {
        return true;
    }
}

/**
 * 获得购物车中的商品
 *
 * @access  public
 * @return  array
 */
function get_cart_goods()
{
    /* 初始化 */
    $goods_list = array();
    $total = array(
        'goods_price'  => 0, // 本店售价合计（有格式）
        'market_price' => 0, // 市场售价合计（有格式）
        'saving'       => 0, // 节省金额（有格式）
        'save_rate'    => 0, // 节省百分比
        'goods_amount' => 0, // 本店售价合计（无格式）
    );

    /* 循环、统计 */
    $sql = "SELECT *, IF(parent_id, parent_id, goods_id) AS pid " .
            " FROM " . $GLOBALS['hhs']->table('cart') . " " .
            " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'" .
            " ORDER BY pid, parent_id";
    $res = $GLOBALS['db']->query($sql);

    /* 用于统计购物车中实体商品和虚拟商品的个数 */
    $virtual_goods_count = 0;
    $real_goods_count    = 0;

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $total['goods_price']  += $row['goods_price'] * $row['goods_number'];
        $total['market_price'] += $row['market_price'] * $row['goods_number'];

        $row['subtotal']     = price_format($row['goods_price'] * $row['goods_number'], false);
        $row['goods_price']  = price_format($row['goods_price'], false);
        $row['market_price'] = price_format($row['market_price'], false);

        /* 统计实体商品和虚拟商品的个数 */
        if ($row['is_real'])
        {
            $real_goods_count++;
        }
        else
        {
            $virtual_goods_count++;
        }

        /* 查询规格 */
        if (trim($row['goods_attr']) != '')
        {
            $row['goods_attr']=addslashes($row['goods_attr']);
            $sql = "SELECT attr_value FROM " . $GLOBALS['hhs']->table('goods_attr') . " WHERE goods_attr_id " .
            db_create_in($row['goods_attr']);
            $attr_list = $GLOBALS['db']->getCol($sql);
            foreach ($attr_list AS $attr)
            {
                $row['goods_name'] .= ' [' . $attr . '] ';
            }
        }
        /* 增加是否在购物车里显示商品图 */
        if ($row['extension_code'] != 'package_buy')
        {
            $goods_thumb = $GLOBALS['db']->getOne("SELECT `goods_thumb` FROM " . $GLOBALS['hhs']->table('goods') . " WHERE `goods_id`='{$row['goods_id']}'");
            $row['goods_thumb'] = get_image_path($row['goods_id'], $goods_thumb, true);
        }
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;
    }
    $total['goods_amount'] = $total['goods_price'];
    $total['saving']       = price_format($total['market_price'] - $total['goods_price'], false);
    if ($total['market_price'] > 0)
    {
        $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
        100 / $total['market_price']).'%' : 0;
    }
    $total['goods_price']  = price_format($total['goods_price'], false);
    $total['market_price'] = price_format($total['market_price'], false);
    $total['real_goods_count']    = $real_goods_count;
    $total['virtual_goods_count'] = $virtual_goods_count;

    return array('goods_list' => $goods_list, 'total' => $total);
}

/**
 * 取得收货人信息
 * @param   int     $user_id    用户编号
 * @return  array
 */
function get_consignee($user_id)
{
    if (isset($_SESSION['flow_consignee']))
    {
        /* 如果存在session，则直接返回session中的收货人信息 */

        return $_SESSION['flow_consignee'];
    }
    else
    {
        /* 如果不存在，则取得用户的默认收货人信息 */
        $arr = array();

        if ($user_id > 0)
        {
            /* 取默认地址 */
            $sql = "SELECT ua.*,concat(IFNULL(p.region_name, ''), " .
                "' ', IFNULL(t.region_name, ''), ' ', IFNULL(d.region_name, '')) AS region ".
                    " FROM ".$GLOBALS['hhs']->table('users')." AS u ," . $GLOBALS['hhs']->table('user_address') . "AS ua ".
                        "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS p ON ua.province = p.region_id " .
                        "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS t ON ua.city = t.region_id " .
                        "LEFT JOIN " . $GLOBALS['hhs']->table('region') . " AS d ON ua.district = d.region_id " .
                    " WHERE u.user_id='$user_id' AND ua.address_id = u.address_id";

            $arr = $GLOBALS['db']->getRow($sql);
        }

        return $arr;
    }
}

/**
 * 查询购物车（订单id为0）或订单中是否有实体商品
 * @param   int     $order_id   订单id
 * @param   int     $flow_type  购物流程类型
 * @return  bool
 */
function exist_real_goods($order_id = 0, $flow_type = CART_GENERAL_GOODS)
{
    if ($order_id <= 0)
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "' AND is_real = 1 " .
                "AND rec_type = '$flow_type'";
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_goods') .
                " WHERE order_id = '$order_id' AND is_real = 1";
    }

    return $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 检查收货人信息是否完整
 * @param   array   $consignee  收货人信息
 * @param   int     $flow_type  购物流程类型
 * @return  bool    true 完整 false 不完整
 */
function check_consignee_info($consignee, $flow_type)
{
    if (exist_real_goods(0, $flow_type))
    {
        /* 如果存在实体商品 */
        $res = !empty($consignee['consignee']) &&
            !empty($consignee['country']) &&
            !empty($consignee['mobile']);
            //!empty($consignee['email']) &&
           // !empty($consignee['tel']);

        if ($res)
        {
            if (empty($consignee['province']))
            {
                /* 没有设置省份，检查当前国家下面有没有设置省份 */
                $pro = get_regions(1, $consignee['country']);
                $res = empty($pro);
            }
            elseif (empty($consignee['city']))
            {
                /* 没有设置城市，检查当前省下面有没有城市 */
                $city = get_regions(2, $consignee['province']);
                $res = empty($city);
            }
            elseif (empty($consignee['district']))
            {
                $dist = get_regions(3, $consignee['city']);
                $res = empty($dist);
            }
        }

        return $res;
    }
    else
    {
        /* 如果不存在实体商品 */
        return !empty($consignee['consignee']) &&
            !empty($consignee['email']) &&
            !empty($consignee['tel']);
    }
}

/**
 * 获得上一次用户采用的支付和配送方式
 *
 * @access  public
 * @return  void
 */
function last_shipping_and_payment()
{
    $sql = "SELECT shipping_id, pay_id " .
            " FROM " . $GLOBALS['hhs']->table('order_info') .
            " WHERE user_id = '$_SESSION[user_id]' " .
            " ORDER BY order_id DESC LIMIT 1";
    $row = $GLOBALS['db']->getRow($sql);

    if (empty($row))
    {
        /* 如果获得是一个空数组，则返回默认值 */
        $row = array('shipping_id' => 0, 'pay_id' => 0);
    }

    return $row;
}

/**
 * 取得当前用户应该得到的优惠劵总额
 */
function get_total_bonus()
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    /* 按商品发的优惠劵 */
    $sql = "SELECT SUM(c.goods_number * t.type_money)" .
            "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, "
                    . $GLOBALS['hhs']->table('bonus_type') . " AS t, "
                    . $GLOBALS['hhs']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id = g.goods_id " .
            "AND g.bonus_type_id = t.type_id " .
            "AND t.send_type = '" . SEND_BY_GOODS . "' " .
            "AND t.send_start_date <= '$today' " .
            "AND t.send_end_date >= '$today' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_total = floatval($GLOBALS['db']->getOne($sql));

    /* 取得购物车中非赠品总金额 */
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['hhs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            " AND is_gift = 0 " .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $amount = floatval($GLOBALS['db']->getOne($sql));

    /* 按订单发的优惠劵 */
    $sql = "SELECT FLOOR('$amount' / min_amount) * type_money " .
            "FROM " . $GLOBALS['hhs']->table('bonus_type') .
            " WHERE send_type = '" . SEND_BY_ORDER . "' " .
            " AND send_start_date <= '$today' " .
            "AND send_end_date >= '$today' " .
            "AND min_amount > 0 ";
    $order_total = floatval($GLOBALS['db']->getOne($sql));

    return $goods_total + $order_total;
}

/**
 * 处理优惠劵（下订单时设为使用，取消（无效，退货）订单时设为未使用
 * @param   int     $bonus_id   优惠劵编号
 * @param   int     $order_id   订单号
 * @param   int     $is_used    是否使用了
 */
function change_user_bonus($bonus_id, $order_id, $is_used = true)
{
    if ($is_used)
    {
        $sql = 'UPDATE ' . $GLOBALS['hhs']->table('user_bonus') . ' SET ' .
                'used_time = ' . gmtime() . ', ' .
                "order_id = '$order_id' " .
                "WHERE bonus_id = '$bonus_id'";
    }
    else
    {
        $sql = 'UPDATE ' . $GLOBALS['hhs']->table('user_bonus') . ' SET ' .
                'used_time = 0, ' .
                'order_id = 0 ' .
                "WHERE bonus_id = '$bonus_id'";
    }
    $GLOBALS['db']->query($sql);
}

/**
 * 获得订单信息
 *
 * @access  private
 * @return  array
 */
function flow_order_info()
{
    $order = isset($_SESSION['flow_order']) ? $_SESSION['flow_order'] : array();

    /* 初始化配送和支付方式 */
    if (!isset($order['shipping_id']) || !isset($order['pay_id']))
    {
        /* 如果还没有设置配送和支付 */
        if ($_SESSION['user_id'] > 0)
        {
            /* 用户已经登录了，则获得上次使用的配送和支付 */
            $arr = last_shipping_and_payment();

            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = $arr['shipping_id'];
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = $arr['pay_id'];
            }
        }
        else
        {
            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = 0;
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = 0;
            }
        }
    }

    if (!isset($order['pack_id']))
    {
        $order['pack_id'] = 0;  // 初始化包装
    }
    if (!isset($order['card_id']))
    {
        $order['card_id'] = 0;  // 初始化贺卡
    }
    if (!isset($order['bonus']))
    {
        $order['bonus'] = 0;    // 初始化优惠劵
    }
    if (!isset($order['integral']))
    {
        $order['integral'] = 0; // 初始化积分
    }
    if (!isset($order['surplus']))
    {
        $order['surplus'] = 0;  // 初始化余额
    }

    /* 扩展信息 */
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS )
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    return $order;
}

/**
 * 合并订单
 * @param   string  $from_order_sn  从订单号
 * @param   string  $to_order_sn    主订单号
 * @return  成功返回true，失败返回错误信息
 */
function merge_order($from_order_sn, $to_order_sn)
{
    /* 订单号不能为空 */
    if (trim($from_order_sn) == '' || trim($to_order_sn) == '')
    {
        return $GLOBALS['_LANG']['order_sn_not_null'];
    }

    /* 订单号不能相同 */
    if ($from_order_sn == $to_order_sn)
    {
        return $GLOBALS['_LANG']['two_order_sn_same'];
    }

    /* 取得订单信息 */
    $from_order = order_info(0, $from_order_sn);
    $to_order   = order_info(0, $to_order_sn);

    /* 检查订单是否存在 */
    if (!$from_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $from_order_sn);
    }
    elseif (!$to_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $to_order_sn);
    }

    /* 检查合并的订单是否为普通订单，非普通订单不允许合并 */
    if ($from_order['extension_code'] != '' || $to_order['extension_code'] != 0)
    {
        return $GLOBALS['_LANG']['merge_invalid_order'];
    }

    /* 检查订单状态是否是已确认或未确认、未付款、未发货 */
    if ($from_order['order_status'] != OS_UNCONFIRMED && $from_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $from_order_sn);
    }
    elseif ($from_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $from_order_sn);
    }
    elseif ($from_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $from_order_sn);
    }

    if ($to_order['order_status'] != OS_UNCONFIRMED && $to_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $to_order_sn);
    }
    elseif ($to_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $to_order_sn);
    }
    elseif ($to_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $to_order_sn);
    }

    /* 检查订单用户是否相同 */
    if ($from_order['user_id'] != $to_order['user_id'])
    {
        return $GLOBALS['_LANG']['order_user_not_same'];
    }

    /* 合并订单 */
    $order = $to_order;
    $order['order_id']  = '';
    $order['add_time']  = gmtime();

    // 合并商品总额
    $order['goods_amount'] += $from_order['goods_amount'];

    // 合并折扣
    $order['discount'] += $from_order['discount'];

    if ($order['shipping_id'] > 0)
    {
        // 重新计算配送费用
        $weight_price       = order_weight_price($to_order['order_id']);
        $from_weight_price  = order_weight_price($from_order['order_id']);
        $weight_price['weight'] += $from_weight_price['weight'];
        $weight_price['amount'] += $from_weight_price['amount'];
        $weight_price['number'] += $from_weight_price['number'];

        $region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
        $shipping_area = shipping_area_info($order['shipping_id'], $region_id_list);

        $order['shipping_fee'] = shipping_fee($shipping_area['shipping_code'],
            unserialize($shipping_area['configure']), $weight_price['weight'], $weight_price['amount'], $weight_price['number']);

        // 如果保价了，重新计算保价费
        if ($order['insure_fee'] > 0)
        {
            $order['insure_fee'] = shipping_insure_fee($shipping_area['shipping_code'], $order['goods_amount'], $shipping_area['insure']);
        }
    }

    // 重新计算包装费、贺卡费
    if ($order['pack_id'] > 0)
    {
        $pack = pack_info($order['pack_id']);
        $order['pack_fee'] = $pack['free_money'] > $order['goods_amount'] ? $pack['pack_fee'] : 0;
    }
    if ($order['card_id'] > 0)
    {
        $card = card_info($order['card_id']);
        $order['card_fee'] = $card['free_money'] > $order['goods_amount'] ? $card['card_fee'] : 0;
    }

    // 优惠劵不变，合并积分、余额、已付款金额
    $order['integral']      += $from_order['integral'];
    $order['integral_money'] = value_of_integral($order['integral']);
    $order['surplus']       += $from_order['surplus'];
    $order['money_paid']    += $from_order['money_paid'];

    // 计算应付款金额（不包括支付费用）
    $order['order_amount'] = $order['goods_amount'] - $order['discount']
                           + $order['shipping_fee']
                           + $order['insure_fee']
                           + $order['pack_fee']
                           + $order['card_fee']
                           - $order['bonus']
                           - $order['integral_money']
                           - $order['surplus']
                           - $order['money_paid'];

    // 重新计算支付费
    if ($order['pay_id'] > 0)
    {
        // 货到付款手续费
        $cod_fee          = $shipping_area ? $shipping_area['pay_fee'] : 0;
        $order['pay_fee'] = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);

        // 应付款金额加上支付费
        $order['order_amount'] += $order['pay_fee'];
    }

    /* 插入订单表 */
    do
    {
        $order['order_sn'] = get_order_sn();
        if ($GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('order_info'), addslashes_deep($order), 'INSERT'))
        {
            break;
        }
        else
        {
            if ($GLOBALS['db']->errno() != 1062)
            {
                die($GLOBALS['db']->errorMsg());
            }
        }
    }
    while (true); // 防止订单号重复

    /* 订单号 */
    $order_id = $GLOBALS['db']->insert_id();

    /* 更新订单商品 */
    $sql = 'UPDATE ' . $GLOBALS['hhs']->table('order_goods') .
            " SET order_id = '$order_id' " .
            "WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    include_once(ROOT_PATH . 'includes/lib_clips.php');
    /* 插入支付日志 */
    insert_pay_log($order_id, $order['order_amount'], PAY_ORDER);

    /* 删除原订单 */
    $sql = 'DELETE FROM ' . $GLOBALS['hhs']->table('order_info') .
            " WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    /* 删除原订单支付日志 */
    $sql = 'DELETE FROM ' . $GLOBALS['hhs']->table('pay_log') .
            " WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    /* 返还 from_order 的优惠劵，因为只使用 to_order 的优惠劵 */
    if ($from_order['bonus_id'] > 0)
    {
        unuse_bonus($from_order['bonus_id']);
    }

    /* 返回成功 */
    return true;
}

/**
 * 查询配送区域属于哪个办事处管辖
 * @param   array   $regions    配送区域（1、2、3、4级按顺序）
 * @return  int     办事处id，可能为0
 */
function get_agency_by_regions($regions)
{
    if (!is_array($regions) || empty($regions))
    {
        return 0;
    }

    $arr = array();
    $sql = "SELECT region_id, agency_id " .
            "FROM " . $GLOBALS['hhs']->table('region') .
            " WHERE region_id " . db_create_in($regions) .
            " AND region_id > 0 AND agency_id > 0";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['region_id']] = $row['agency_id'];
    }
    if (empty($arr))
    {
        return 0;
    }

    $agency_id = 0;
    for ($i = count($regions) - 1; $i >= 0; $i--)
    {
        if (isset($arr[$regions[$i]]))
        {
            return $arr[$regions[$i]];
        }
    }
}

/**
 * 获取配送插件的实例
 * @param   int   $shipping_id    配送插件ID
 * @return  object     配送插件对象实例
 */
function &get_shipping_object($shipping_id)
{
    $shipping  = shipping_info($shipping_id);
    if (!$shipping)
    {
        $object = new stdClass();
        return $object;
    }

    $file_path = ROOT_PATH.'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';

    include_once($file_path);

    $object = new $shipping['shipping_code'];
    return $object;
}

/**
 * 改变订单中商品库存
 * @param   int     $order_id   订单号
 * @param   bool    $is_dec     是否减少库存
 * @param   bool    $storage     减库存的时机，1，下订单时；0，发货时；
 */
function change_order_goods_storage($order_id, $is_dec = true, $storage = 0)
{
    /* 查询订单商品信息 */
    switch ($storage)
    {
        case 0 :
            $sql = "SELECT goods_id, SUM(send_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $GLOBALS['hhs']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
        break;

        case 1 :
            $sql = "SELECT goods_id, SUM(goods_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $GLOBALS['hhs']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
        break;
    }

    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] != "package_buy")
        {
            if ($is_dec)
            {
                change_goods_storage($row['goods_id'], $row['product_id'], - $row['num']);
            }
            else
            {
                change_goods_storage($row['goods_id'], $row['product_id'], $row['num']);
            }
            $GLOBALS['db']->query($sql);
        }
        else
        {
            $sql = "SELECT goods_id, goods_number" .
                   " FROM " . $GLOBALS['hhs']->table('package_goods') .
                   " WHERE package_id = '" . $row['goods_id'] . "'";
            $res_goods = $GLOBALS['db']->query($sql);
            while ($row_goods = $GLOBALS['db']->fetchRow($res_goods))
            {
                $sql = "SELECT is_real" .
                   " FROM " . $GLOBALS['hhs']->table('goods') .
                   " WHERE goods_id = '" . $row_goods['goods_id'] . "'";
                $real_goods = $GLOBALS['db']->query($sql);
                $is_goods = $GLOBALS['db']->fetchRow($real_goods);

                if ($is_dec)
                {
                    change_goods_storage($row_goods['goods_id'], $row['product_id'], - ($row['num'] * $row_goods['goods_number']));
                }
                elseif ($is_goods['is_real'])
                {
                    change_goods_storage($row_goods['goods_id'], $row['product_id'], ($row['num'] * $row_goods['goods_number']));
                }
            }
        }
    }

}

/**
 * 商品库存增与减 货品库存增与减
 *
 * @param   int    $good_id         商品ID
 * @param   int    $product_id      货品ID
 * @param   int    $number          增减数量，默认0；
 *
 * @return  bool               true，成功；false，失败；
 */
function change_goods_storage($good_id, $product_id, $number = 0)
{
    if ($number == 0)
    {
        return true; // 值为0即不做、增减操作，返回true
    }

    if (empty($good_id) || empty($number))
    {
        return false;
    }

    $number = ($number > 0) ? '+ ' . $number : $number;

    /* 处理货品库存 */
    $products_query = true;
    if (!empty($product_id))
    {
        $sql = "UPDATE " . $GLOBALS['hhs']->table('products') ."
                SET product_number = product_number $number
                WHERE goods_id = '$good_id'
                AND product_id = '$product_id'
                LIMIT 1";
        $products_query = $GLOBALS['db']->query($sql);
    }

    /* 处理商品库存 */
    $sql = "UPDATE " . $GLOBALS['hhs']->table('goods') ."
            SET goods_number = goods_number $number
            WHERE goods_id = '$good_id'
            LIMIT 1";
    $query = $GLOBALS['db']->query($sql);

    if ($query && $products_query)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 取得支付方式id列表
 * @param   bool    $is_cod 是否货到付款
 * @return  array
 */
function payment_id_list($is_cod)
{
    $sql = "SELECT pay_id FROM " . $GLOBALS['hhs']->table('payment');
    if ($is_cod)
    {
        $sql .= " WHERE is_cod = 1";
    }
    else
    {
        $sql .= " WHERE is_cod = 0";
    }

    return $GLOBALS['db']->getCol($sql);
}

/**
 * 生成查询订单的sql
 * @param   string  $type   类型
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_query_sql($type = 'finished', $alias = '')
{
    /* 已完成订单 */
    if ($type == 'finished')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_RECEIVED)) .
               " AND {$alias}pay_status " . db_create_in(array(PS_PAYED,PS_PAYING )) . " "; //, SS_SHIPPED
    }
    /* 待发货订单 */
    elseif ($type == 'await_ship')
    {
        return " AND   {$alias}order_status " .
                 db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
               " AND   {$alias}shipping_status " .
                 db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING)) .
               " AND ( {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(true)) . ") ";
    }
    /* 待付款订单 */
    elseif ($type == 'await_pay')
    {
        return " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
               " AND   {$alias}pay_status = '" . PS_UNPAYED . "'" .
               " AND ( {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(false)) . ") ";
    }
    /* 未确认订单 */
    elseif ($type == 'unconfirmed')
    {
        return " AND {$alias}order_status = '" . OS_UNCONFIRMED . "' ";
    }
    /* 未处理订单：用户可操作 */
    elseif ($type == 'unprocessed')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status = '" . SS_UNSHIPPED . "'" .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 未付款未发货订单：管理员可操作 */
    elseif ($type == 'unpay_unship')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING)) .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 已发货订单：不论是否付款 */
    elseif ($type == 'shipped')
    {
        return " AND {$alias}order_status = '" . OS_CONFIRMED . "'" .
               " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " ";
    }
    /* 已付款订单：*/
    elseif ($type == 'payed')
    {
        return " AND {$alias}pay_status = 2 ";
    }
    /* 已付款订单：*/
    elseif ($type == 'refund')
    {
        return " AND {$alias}pay_status > 2 ";
    }
    /* 已发货订单：未收货 */
    elseif ($type == 'shipped2')
    {
        return " AND {$alias}shipping_status=1 ";
    }
    else
    {
        die('函数 order_query_sql 参数错误'.$type);
    }
}

/**
 * 生成查询订单总金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_amount_field($alias = '')
{
    return "   {$alias}goods_amount + {$alias}tax + {$alias}shipping_fee" .
           " + {$alias}insure_fee + {$alias}pay_fee + {$alias}pack_fee" .
           " + {$alias}card_fee ";
}

/**
 * 生成计算应付款金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_due_field($alias = '')
{
    return order_amount_field($alias) .
            " - {$alias}money_paid - {$alias}surplus - {$alias}integral_money" .
            " - {$alias}bonus - {$alias}discount ";
}

/**
 * 计算折扣：根据购物车和优惠活动
 * @return  float   折扣
 */
function compute_discount()
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['hhs']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE));
    $favourable_list = $GLOBALS['db']->getAll($sql);
    if (!$favourable_list)
    {
        return 0;
    }

    /* 查询购物车商品 */
    $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id " .
            "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " . $GLOBALS['hhs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (!$goods_list)
    {
        return 0;
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
                $total_amount += $goods['subtotal'];
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        else
        {
            continue;
        }

        /* 如果金额满足条件，累计折扣 */
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);

                $favourable_name[] = $favourable['act_name'];
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                $discount += $favourable['act_type_ext'];

                $favourable_name[] = $favourable['act_name'];
            }
        }
    }

    return array('discount' => $discount, 'name' => $favourable_name);
}

/**
 * 取得购物车该赠送的积分数
 * @return  int     积分数
 */
function get_give_integral()
{
        $sql = "SELECT SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price))" .
                "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " .
                          $GLOBALS['hhs']->table('goods') . " AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND c.session_id = '" . SESS_ID . "' " .
                "AND c.goods_id > 0 " .
                "AND c.parent_id = 0 " .
                "AND c.rec_type = 0 " .
                "AND c.is_gift = 0";

        return intval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单应该赠送的积分数
 * @param   array   $order  订单
 * @return  int     积分数
 */
function integral_to_give($order)
{
    /* 判断是否团购 */
    if ($order['extension_code'] == 'group_buy')
    {
        include_once(ROOT_PATH . 'includes/lib_goods.php');
        $group_buy = group_buy_info(intval($order['extension_id']));

        return array('custom_points' => $group_buy['gift_integral'], 'rank_points' => $order['goods_amount']);
    }
    else
    {
        $sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS custom_points, SUM(og.goods_number * IF(g.rank_integral > -1, g.rank_integral, og.goods_price)) AS rank_points " .
                "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS og, " .
                          $GLOBALS['hhs']->table('goods') . " AS g " .
                "WHERE og.goods_id = g.goods_id " .
                "AND og.order_id = '$order[order_id]' " .
                "AND og.goods_id > 0 " .
                "AND og.parent_id = 0 " .
                "AND og.is_gift = 0 AND og.extension_code != 'package_buy'";

        return $GLOBALS['db']->getRow($sql);
    }
}

/**
 * 发优惠劵：发货时发优惠劵
 * @param   int     $order_id   订单号
 * @return  bool
 */
function send_order_bonus($order_id)
{
    global $hhs;
    global $db;
    //查询订单的商家id
    $suppliers_id = $GLOBALS['db']->getOne("select  suppliers_id from ". $GLOBALS['hhs']->table('order_info') ." where order_id='$order_id'");
    /* 取得订单应该发放的优惠劵 */
    $bonus_list = order_bonus($order_id,$suppliers_id);
//var_dump($bonus_list);exit();

    /* 如果有优惠劵，统计并发送 */
    $array = array();
    if ($bonus_list)
    {
        /* 用户信息 */
        $sql = "SELECT u.openid, u.user_id, u.user_name, u.email, o.team_first " .
                "FROM " . $GLOBALS['hhs']->table('order_info') . " AS o, " .
                          $GLOBALS['hhs']->table('users') . " AS u " .
                "WHERE o.order_id = '$order_id' " .
                "AND o.user_id = u.user_id ";
        $user = $GLOBALS['db']->getRow($sql);

        /* 统计 */
        $count = 0;
        $money = '';
        foreach ($bonus_list AS $bonus)
        {
            // 团长才发的免单券
            // if($bonus['free_all'] == 1)
            // {
            //     if($user['team_first']>1)
            //     continue; // 不是团长
            // }
            /* 修改用户优惠劵 */
            if($bonus['is_share']==1){
                $sql="INSERT INTO " . $GLOBALS['hhs']->table('send_bonus_type') . " (user_id, type_id,add_time,send_order_id,send_number) " .
                        "VALUES( '$user[user_id]','$bonus[type_id]','".gmtime()."','$order_id','$bonus[share_number]')"; 
                $GLOBALS['db']->query($sql); 
                $send_id=$GLOBALS['db']->insert_id();
                //好友券自己不能用
                for($i=0;$i<$bonus['share_number'];$i++){
                    $sql = "INSERT INTO " . $GLOBALS['hhs']->table('user_bonus') . " (bonus_type_id, send_user_id,send_order_id,send_id,suppliers_id) " .
                        "VALUES('$bonus[type_id]', '$user[user_id]','$order_id','$send_id','$suppliers_id')";
                   $GLOBALS['db']->query($sql);
                    /*
                    for ($i = 0; $i < $bonus['number']; $i++)
                    {
                    if (!$GLOBALS['db']->query($sql))
                    {
                    return $GLOBALS['db']->errorMsg();
                    }
                    }*/
                   
                }
                
            }else{
                
                $sql = "INSERT INTO " . $GLOBALS['hhs']->table('user_bonus') . " (bonus_type_id, user_id,send_order_id,suppliers_id) " .
                    "VALUES('$bonus[type_id]', '$user[user_id]','$order_id','$suppliers_id')";
                for ($i = 0; $i < $bonus['number']; $i++)
                {
                    if (!$GLOBALS['db']->query($sql))
                    {
                        return $GLOBALS['db']->errorMsg();
                    }
                }
                $count += $bonus['number'];
                $money .= price_format($bonus['type_money']) . ' [' . $bonus['number'] . '], ';
            }
            if($bonus['free_all'])
            {
                $goods = $db->getRow("SELECT goods_id,goods_name,goods_img FROM " . $hhs->table('goods') . " WHERE `bonus_free_all` = '".$bonus['type_id']."' ");
                if (empty($goods)) {
                    continue;
                }
                $w_title = '免单券';
                $w_url = 'goods.php?id=' . $goods['goods_id'].'&bonus_free_all='.$bonus['type_id'];
                $w_description='恭喜您成功获得了一次团长免单优惠活动的机会，团长免单劵稍后会发放至您的账户请注意查收';
                $picurl= $goods['goods_img'];
            }
            else{
                $w_title = '发送优惠券提醒';
                $w_url = 'user.php?act=bonus';
                $w_description='亲爱的'.$uname.'您好！恭喜您获得了1个红包，金额为'.$bonus['type_money'];
                $picurl='';
            }
            $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

            $weixin->send_wxmsg($user['openid'], $w_title , $w_url , $w_description ,$picurl);
            
        }
// die('您好！恭喜您获得了'.$count.'个红包，金额分别为'.$money);
        /* 如果有优惠劵，发送邮件 */
        if ($count > 0)
        {
            // if($bonus['free_all'])
            // {
            //     $goods = $db->getRow("SELECT goods_id,goods_name,goods_img FROM " . $hhs->table('goods') . " WHERE `bonus_free_all` = '".$bonus['type_id']."' ");
            //     $w_title = '免单券';
            //     $w_url = 'goods.php?id=' . $goods['goods_id'].'&bonus_free_all='.$bonus['type_id'];
            //     $w_description='恭喜您成功获得了一次团长免单优惠活动的机会，团长免单劵稍后会发放至您的账户请注意查收';
            //     $picurl= $goods['goods_img'];
            // }
            // else{
            //     $w_title = '发送优惠券提醒';
            //     $w_url = 'user.php?act=bonus';
            //     $w_description='亲爱的'.$uname.'您好！恭喜您获得了'.$count.'个红包，金额分别为'.$money;
            //     $picurl='';
            // }
            // $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

            // $weixin->send_wxmsg($user['openid'], $w_title , $w_url , $w_description ,$picurl);
            
            // $user_id=$user['user_id'];
            // $uname = $GLOBALS['db']->getOne("select uname from ".$GLOBALS['hhs']->table('users')." where user_id=".$user_id);
            // $order_id=$order_id;
            // $wxch_order_name='send_order_bonus';
            // include_once(ROOT_PATH . 'wxch_order.php');
//            $tpl = get_mail_template('send_bonus');
//            $GLOBALS['smarty']->assign('user_name', $user['user_name']);
//            $GLOBALS['smarty']->assign('count', $count);
//            $GLOBALS['smarty']->assign('money', $money);
//            $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
//            $GLOBALS['smarty']->assign('send_date', local_date($GLOBALS['_CFG']['date_format']));
//            $GLOBALS['smarty']->assign('sent_date', local_date($GLOBALS['_CFG']['date_format']));
//            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
//            send_mail($user['user_name'], $user['email'], $tpl['template_subject'], $content, $tpl['is_html']);
        }
    }

    return $bonus_list;
    //return true;
}

/**
 * 返回订单发放的优惠劵
 * @param   int     $order_id   订单id
 */
function return_order_bonus($order_id)
{
    /* 取得订单应该发放的优惠劵 */
    $bonus_list = order_bonus($order_id);

    /* 删除 */
    if ($bonus_list)
    {
        /* 取得订单信息 */
        $order = order_info($order_id);
        $user_id = $order['user_id'];

        foreach ($bonus_list AS $bonus)
        {
            $sql = "DELETE FROM " . $GLOBALS['hhs']->table('user_bonus') .
                    " WHERE bonus_type_id = '$bonus[type_id]' " .
                    "AND user_id = '$user_id' " .
                    "AND order_id = '0' LIMIT " . $bonus['number'];
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 取得订单应该发放的优惠劵
 * @param   int     $order_id   订单id
 * @return  array
 */
function order_bonus($order_id,$suppliers_id)
{
    /* 查询按商品发的优惠劵 */
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
    
    //发放的普通优惠券
    $sql = "SELECT b.free_all, b.type_id, b.type_money,b.is_share, SUM(o.goods_number) AS number,b.number as share_number,use_start_date,use_end_date " .
            "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS o, " .
                      $GLOBALS['hhs']->table('goods') . " AS g, " .
                      $GLOBALS['hhs']->table('bonus_type') . " AS b " .
            " WHERE o.order_id = '$order_id' " .
            " AND o.is_gift = 0 " .
            " AND o.goods_id = g.goods_id " .
            " AND g.bonus_type_id = b.type_id  " .
             " AND b.suppliers_id = '$suppliers_id' " .
            " AND b.send_type = '" . SEND_BY_GOODS . "' " .
            " AND b.send_start_date <= '$today' " .
            " AND b.send_end_date >= '$today' " .
            " GROUP BY b.type_id ";
        
    $list = $GLOBALS['db']->getAll($sql);
//分享的优惠券
    $sql = "SELECT b.free_all, b.type_id, b.type_money,b.is_share, 1 as number,b.number as share_number,use_start_date,use_end_date  " .
        "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS o, " .
        $GLOBALS['hhs']->table('goods') . " AS g, " .
        $GLOBALS['hhs']->table('bonus_type') . " AS b " .
        " WHERE o.order_id = '$order_id' " .
        " AND o.is_gift = 0 " .
        " AND o.goods_id = g.goods_id " .
        " AND b.suppliers_id = '$suppliers_id' " .
        " AND  g.share_bonus_type = b.type_id  " .
        " AND b.send_type = '" . SEND_BY_GOODS . "' " .
        " AND b.send_start_date <= '$today' " .
        " AND b.send_end_date >= '$today' " .
        " GROUP BY b.type_id ";
    $list2 = $GLOBALS['db']->getAll($sql);
    
    /* 查询定单中非赠品总金额 */
    $amount = order_amount($order_id, false);

    /* 查询订单日期 */

    $sql = "SELECT add_time " .
            " FROM " . $GLOBALS['hhs']->table('order_info') .
            " WHERE order_id = '$order_id' LIMIT 1";
    $order_time = $GLOBALS['db']->getOne($sql);

    /* 查询按订单发的优惠劵 */
    $sql = "SELECT free_all, type_id, type_money,is_share ,IFNULL(FLOOR('$amount' / min_amount), 1) AS number , number as share_number,use_start_date,use_end_date " .
            " FROM " . $GLOBALS['hhs']->table('bonus_type') .
            "WHERE send_type = '" . SEND_BY_ORDER . "' " .
            " AND suppliers_id = '$suppliers_id' " . 
            "AND send_start_date <= '$order_time' " .
            "AND min_amount <= '$amount' " .
            "AND send_end_date >= '$order_time' order by type_money DESC LIMIT 1";

    $list = array_merge($list,$list2, $GLOBALS['db']->getAll($sql));

    return $list;
}

/**
 * 计算购物车中的商品能享受优惠劵支付的总额
 * @return  float   享受优惠劵支付的总额
 */
function compute_discount_amount()
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['hhs']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE));
    $favourable_list = $GLOBALS['db']->getAll($sql);
    if (!$favourable_list)
    {
        return 0;
    }

    /* 查询购物车商品 */
    $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id " .
            "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " . $GLOBALS['hhs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (!$goods_list)
    {
        return 0;
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
                $total_amount += $goods['subtotal'];
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false)
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        else
        {
            continue;
        }
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                $discount += $favourable['act_type_ext'];
            }
        }
    }


    return $discount;
}

/**
 * 添加礼包到购物车
 *
 * @access  public
 * @param   integer $package_id   礼包编号
 * @param   integer $num          礼包数量
 * @return  boolean
 */
function add_package_to_cart($package_id, $num = 1)
{
    $GLOBALS['err']->clean();

    /* 取得礼包信息 */
    $package = get_package_info($package_id);

    if (empty($package))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

        return false;
    }

    /* 是否正在销售 */
    if ($package['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

        return false;
    }

    /* 现有库存是否还能凑齐一个礼包 */
    if ($GLOBALS['_CFG']['use_storage'] == '1' && judge_package_stock($package_id))
    {
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], 1), ERR_OUT_OF_STOCK);

        return false;
    }

    /* 检查库存 */
//    if ($GLOBALS['_CFG']['use_storage'] == 1 && $num > $package['goods_number'])
//    {
//        $num = $goods['goods_number'];
//        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
//
//        return false;
//    }

    /* 初始化要插入购物车的基本件数据 */
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $package_id,
        'goods_sn'      => '',
        'goods_name'    => addslashes($package['package_name']),
        'market_price'  => $package['market_package'],
        'goods_price'   => $package['package_price'],
        'goods_number'  => $num,
        'goods_attr'    => '',
        'goods_attr_id' => '',
        'is_real'       => $package['is_real'],
        'extension_code'=> 'package_buy',
        'is_gift'       => 0,
        'rec_type'      => CART_GENERAL_GOODS
    );

    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
         /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT goods_number FROM " .$GLOBALS['hhs']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '" . $package_id . "' ".
                " AND parent_id = 0 AND extension_code = 'package_buy' " .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新
        {
            $num += $row['goods_number'];
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num > 0)
            {
                $sql = "UPDATE " . $GLOBALS['hhs']->table('cart') . " SET goods_number = '" . $num . "'" .
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$package_id' ".
                       " AND parent_id = 0 AND extension_code = 'package_buy' " .
                       " AND rec_type = '" . CART_GENERAL_GOODS . "'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                return false;
            }
        }
        else //购物车没有此物品，则插入
        {
            $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('cart'), $parent, 'INSERT');
        }
    }

    /* 把赠品删除 */
    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);

    return true;
}

/**
 * 得到新发货单号
 * @return  string
 */
function get_delivery_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 检查礼包内商品的库存
 * @return  boolen
 */
function judge_package_stock($package_id, $package_num = 1)
{
    $sql = "SELECT goods_id, product_id, goods_number
            FROM " . $GLOBALS['hhs']->table('package_goods') . "
            WHERE package_id = '" . $package_id . "'";
    $row = $GLOBALS['db']->getAll($sql);
    if (empty($row))
    {
        return true;
    }

    /* 分离货品与商品 */
    $goods = array('product_ids' => '', 'goods_ids' => '');
    foreach ($row as $value)
    {
        if ($value['product_id'] > 0)
        {
            $goods['product_ids'] .= ',' . $value['product_id'];
            continue;
        }

        $goods['goods_ids'] .= ',' . $value['goods_id'];
    }

    /* 检查货品库存 */
    if ($goods['product_ids'] != '')
    {
        $sql = "SELECT p.product_id
                FROM " . $GLOBALS['hhs']->table('products') . " AS p, " . $GLOBALS['hhs']->table('package_goods') . " AS pg
                WHERE pg.product_id = p.product_id
                AND pg.package_id = '$package_id'
                AND pg.goods_number * $package_num > p.product_number
                AND p.product_id IN (" . trim($goods['product_ids'], ',') . ")";
        $row = $GLOBALS['db']->getAll($sql);

        if (!empty($row))
        {
            return true;
        }
    }

    /* 检查商品库存 */
    if ($goods['goods_ids'] != '')
    {
        $sql = "SELECT g.goods_id
                FROM " . $GLOBALS['hhs']->table('goods') . "AS g, " . $GLOBALS['hhs']->table('package_goods') . " AS pg
                WHERE pg.goods_id = g.goods_id
                AND pg.goods_number * $package_num > g.goods_number
                AND pg.package_id = '" . $package_id . "'
                AND pg.goods_id IN (" . trim($goods['goods_ids'], ',') . ")";
        $row = $GLOBALS['db']->getAll($sql);

        if (!empty($row))
        {
            return true;
        }
    }

    return false;
}

function pay_team_action($orsn){
    /*是团购改变订单*/
    include_once ROOT_PATH."languages/zh_cn/wx_msg.php";
    include_once(ROOT_PATH . 'includes/lib_fenxiao.php');
    
    $sql="select * from ".$GLOBALS['hhs']->table('order_info')."  where order_sn='".$orsn."'";
    $order_info=$GLOBALS['db']->getRow($sql);

    // 減少庫存
    change_order_goods_storage($order_info['order_id'], true, SDT_PLACE);
    
    //发送支付消息
    $openid=$GLOBALS['db']->getOne("select openid from ".$GLOBALS['hhs']->table('users')." where user_id=".$order_info['user_id'] );
    $goods_name = $GLOBALS['db']->getOne("select goods_name from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$order_info['order_id'] );
    $url = 'user.php?act=order_detail&order_id='.$order_info['order_id'];
    $desc = '订单号：'.$order_info['order_sn']."\r\n".'商品信息：'.$goods_name."\r\n支付金额：".price_format($order_info['money_paid']+$order_info['surplus'])."\r\n支付状态：已支付\r\n收货人：".$order_info['consignee'].",".$order_info['mobile']."\r\n".$order_info['address'];

    $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
    $weixin->send_wxmsg($openid, '支付成功' , $url , $desc);


    if($order_info['extension_code']=='team_goods'){
        $team_sign=$order_info['team_sign'];
        $openid=$GLOBALS['db']->getOne("select openid from ".$GLOBALS['hhs']->table('users')." where user_id=".$order_info['user_id'] );
        $t_openid = $GLOBALS['db']->getOne("select u.openid from ".$GLOBALS['hhs']->table('order_info')." as oi left join ".$GLOBALS['hhs']->table('users')." as u on oi.user_id=u.user_id where oi.team_first=1 and oi.team_sign=".$team_sign);
        //加入分成
        $sql="select team_num,discount_type,discount_amount,shared_money,shared_allow from ".$GLOBALS['hhs']->table('goods')." where goods_id=".$order_info['extension_id'];
        $rs=$GLOBALS['db']->getRow($sql);
        $team_num=$rs['team_num'];
        
        $discount_type=$rs['discount_type'];
        $discount_amount=$rs['discount_amount'];
        //加入分成
        $shared_money=$rs['shared_money'];
        $shared_allow=$rs['shared_allow'];

        if($order_info['team_first']==1){
            //若是团长记录下团的人数
            //加入分成
            if($discount_type==1&&$order_info['refund_sign']==0){
                $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_num='$team_num',discount_type ='$discount_type',discount_amount=money_paid+order_amount,shared_allow = '$shared_allow',shared_money='$shared_money' WHERE order_id=".$order_info['order_id'];
            }elseif($discount_type==2&&$order_info['refund_sign']==0){
                $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_num='$team_num',discount_type ='$discount_type', discount_amount='$discount_amount',shared_allow = '$shared_allow',shared_money='$shared_money' WHERE order_id=".$order_info['order_id'];
            }else{
                $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_num='$team_num', discount_amount=0,shared_allow = '$shared_allow',shared_money='$shared_money'  WHERE order_id=".$order_info['order_id'];
            }
            $GLOBALS['db']->query($sql);
            // 先开团，后付款的情况,
            if ($order_info['is_luck'] == 1 || $order_info['is_miao'] == 1) {
                $luck_times = $order_info['luck_times'];
                $sql = "SELECT teammen_num,team_sign from ". $GLOBALS['hhs']->table('order_info') ." WHERE goods_id = '".$order_info['goods_id']."' AND luck_times >= '".$luck_times."' AND team_status = 1";
                $team = $GLOBALS['db']->getRow($sql);
                if ($team) {
                    // 修复team_sign
                    $team_sign  = $team['team_sign'];
                    $order_info['team_first'] = 2;
                    $order_info['team_sign']  = $team_sign;
                    $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_first=2, team_sign = '".$team_sign."' WHERE order_id=".$order_info['order_id'];
                    $GLOBALS['db']->query($sql);
                }
            }

        }
        else{
            $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_num='$team_num', discount_amount=0,shared_allow = '$shared_allow',shared_money='$shared_money'  WHERE order_id=".$order_info['order_id'];
            $GLOBALS['db']->query($sql);
        }
        $sql="select team_num from ".$GLOBALS['hhs']->table('order_info') ." where order_id=".$order_info['team_sign'];
        $team_num=$GLOBALS['db']->getOne($sql);
        //团共需人数和状态
        $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_status=1,team_num='$team_num' WHERE order_id=".$order_info['order_id'];
        $GLOBALS['db']->query($sql);
        //实际人数
        if($order_info['is_luck'] ==1){
            $sql = 'UPDATE ' . $GLOBALS['hhs']->table('order_luck') .
                        " SET is_payed = '1',team_sign = '".$order_info['team_sign']."' " .
                   "WHERE order_id = '".$order_info['order_id']."'";
            $GLOBALS['db']->query($sql);

            //夺宝方式，以购买分数为依据
            $sql="select count(*) from ".$GLOBALS['hhs']->table('order_luck')." where is_payed = 1 and team_sign=".$team_sign."";
            $rel_num=$GLOBALS['db']->getOne($sql);
        }
        else{
            // 团购方式，以订单号为依据
            $sql="select count(*) from ".$GLOBALS['hhs']->table('order_info')." where team_sign=".$team_sign." and team_status>0 ";
            $rel_num=$GLOBALS['db']->getOne($sql);
        }

        //存储实际人数
        $sql="update ".$GLOBALS['hhs']->table('order_info')." set teammen_num='$rel_num' where team_sign=".$team_sign;
        $GLOBALS['db']->query($sql);
        
        if($order_info['team_first']==1){
            if($order_info['is_luck'])
            {
                $_team_msg['pay']['desc'] = '感谢您的参与，请等待结果揭晓~';
                $weixin->send_wxmsg($openid, $_team_msg['pay']['title'] , 'userbao.php?order_id='.$order_info['order_id'] , $_team_msg['pay']['desc']  );
            }
            else{
             $weixin->send_wxmsg($openid, $_team_msg['pay']['title'] , 'share.php?team_sign='.$team_sign , $_team_msg['pay']['desc']  );
               
            }
            
        }
        // elseif($order_info['team_first']==2)
        // { 
        //     if($team_num<=$rel_num){
        //         $weixin->send_wxmsg($openid, $_team_msg['team_suc_mem']['title'] , 'share.php?team_sign='.$team_sign , $_team_msg['team_suc_mem']['desc']);
                 
        //     }else{
        //         $weixin->send_wxmsg($openid, $_team_msg['mem_pay']['title'] , 'share.php?team_sign='.$team_sign , $_team_msg['mem_pay']['title'] );
        //     }
        // }
       
        if($team_num<=$rel_num){
            $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET team_status=2 WHERE team_status=1 and team_sign=".$team_sign;
            $GLOBALS['db']->query($sql);
            //取消未参团订单
            $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$team_sign;
            $GLOBALS['db']->query($sql);

            //抽奖产品
            if($order_info['is_luck'] > 0)
            {
                $GLOBALS['db']->query('update '.$GLOBALS['hhs']->table('goods').' set luck_times = luck_times + 1 where goods_id = "'.$order_info['extension_id'].'"');//goods_number = team_num, 
                //抽奖流程
                // 所有的份
                $sql = "select `id`,`order_id` FROM ".$GLOBALS['hhs']->table('order_luck')." WHERE `is_payed` = 1 AND `team_sign` = " . $team_sign;
                $rows = $GLOBALS['db']->getAll($sql);

                //所有的分数 -1
                $nums     = count($rows)-1;
                //产生随机数
                $rand_num = rand(0,$nums);
                //随机行,即为中奖行
                $rand_row = $rows[$rand_num];
                //更新行状态
                $sql = 'update '.$GLOBALS['hhs']->table('order_luck').' set `is_lucker` = 1 where id = "'.$rand_row['id'].'"';
                $GLOBALS['db']->query($sql);

                // //更订单团购状态,已经收货
                // $sql = 'update '.$GLOBALS['hhs']->table('order_info').' set `shipping_status` = 2 where  `pay_status` = 2 AND order_id <> "'.$rand_row['order_id'].'" AND `team_sign` = ' . $team_sign;
                // $GLOBALS['db']->query($sql);

                // //更订单行状态，中奖
                // $sql = 'update '.$GLOBALS['hhs']->table('order_info').' set `is_lucker` = 1 where order_id = "'.$rand_row['order_id'].'"';
                // $GLOBALS['db']->query($sql);



                $sql = "select u.`user_id`,u.`openid`,o.`order_id`,o.`money_paid`,o.`transaction_id` FROM ".$GLOBALS['hhs']->table('order_info')." as o,".$GLOBALS['hhs']->table('users')." as u WHERE u.`user_id` = o.`user_id` AND o.`pay_status` = 2 AND `order_status` = 1 AND o.`team_sign` = " . $team_sign;
                $rows = $GLOBALS['db']->getAll($sql);

                foreach ($rows as $key => $row) {
                    //中奖
                    if($row['order_id'] == $rand_row['order_id'])
                    {
                        $title = '运气真好，中奖了！';
                        $mark  = '请等待接收奖品！';
                        //更新lucker状态
                        $sql = "update ".$GLOBALS['hhs']->table('order_info')." set `is_lucker` = 1 WHERE `order_id` = " . $row['order_id'];
                        $GLOBALS['db']->query($sql);
                    }
                    //没中奖
                    else{
                        $title = '手气差了那么一点点，差点儿就中奖了！';
                        $mark  = '';
                        //更新状态
                        $sql = "update ".$GLOBALS['hhs']->table('order_info')." set `is_lucker` = 0, `shipping_status` = 2 WHERE `order_id` = " . $row['order_id'];
                        $GLOBALS['db']->query($sql);                        
                        // 微信红包退款
                        // if(    $row['transaction_id']//微信支付
                        //     && $row['money_paid'] >= 1.00//大于1.00
                        //     )
                        // {
                        //     $re_openid    = $row['openid'];
                        //     $total_amount = $row['money_paid'] * 100;
                        //     $min_value    = $row['money_paid'] * 100;
                        //     $max_value    = $row['money_paid'] * 100;
                        //     $total_num    = 1;
                        //     $act_name     = '抽奖活动未中奖退款';
                        //     $hongbao      = new hongbao();
                        //     $res = $hongbao->send($re_openid,$total_amount,$min_value,$max_value,$total_num,$act_name);
                        //     if($res)
                        //     {
                        //         //更新状态
                        //         $sql = "update ".$GLOBALS['hhs']->table('order_info')." set `shipping_status` = 2,`is_hongbao_back` = 1 WHERE `order_id` = " . $row['order_id'];
                        //         $GLOBALS['db']->query($sql);
                        //         $mark = '微信红包已退款。';
                        //     }
                        // }
                    }
                    $desc = '您参与的夺宝结果出来了！';
                    //发消息
                    if($row['openid'])
                    $weixin->send_wxmsg($row['openid'], $title , 'user.php?act=lottery_list' , $desc . $title . $mark);
                
                    // 开团之后发优惠券
                    if($GLOBALS['_CFG']['send_bonus_time'] == 0){
                        send_order_bonus($row['order_id']);
                    }                 
                    //$weixin->send_wxmsg($row['openid'], $title , 'share.php?team_sign='.$team_sign , $desc . $title . $mark);
                }
            }
            else{
                //判断团长是否有优惠，要重新取数据
                $sql="select order_id,user_id,refund_sign,discount_type, transaction_id, discount_amount, money_paid,order_amount,order_sn,shared_allow,shared_money from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$team_sign;
                $r=$GLOBALS['db']->getRow($sql);
                //团长免单
                //目前只有微信可以退款
                $payment=payment_info($order_info['pay_id']);
                //使用余额支付的不能退款
                if($payment['pay_code']=='wxpay' && $r['transaction_id']){
                    if($r['discount_type']==1&&$r['refund_sign']==0){//团长免单
                        $f=refund($r['order_sn'], $r['money_paid']*100);
                        if($f){
                            $arr['refund_sign']  = 1;
                            // $arr['pay_status']   = 3;
                            $arr['money_paid']   = 0;
                            // $arr['order_amount'] = $r['money_paid'] + $r['order_amount'];
                            update_order($team_sign, $arr);

                            
                            $weixin->send_wxmsg($t_openid, $_team_msg['refund_team_first']['title'], 'share.php?team_sign='.$team_sign , $_team_msg['refund_team_first']['desc']);

                        }

                    }elseif($r['discount_type']==2&&$r['refund_sign']==0){
                        $f=refund($r['order_sn'], $r['discount_amount']*100);
                        if($f){
                            $arr['refund_sign']  = 1;
                            $arr['money_paid']   = $r['money_paid']-$r['discount_amount'];
                            // $arr['order_amount'] = $r['discount_amount'] + $r['order_amount'];
                            update_order($team_sign, $arr);

                            $weixin->send_wxmsg($t_openid, $_team_msg['refund_team_discount']['title'],  'share.php?team_sign='.$team_sign , $_team_msg['refund_team_discount']['desc'] );
                        }

                    }
                }

                // 开团成功通知
                $sql = "select u.`user_id`,u.`openid`,o.`order_id` FROM ".$GLOBALS['hhs']->table('order_info')." as o,".$GLOBALS['hhs']->table('users')." as u WHERE o.pay_status = 2 AND u.`user_id` = o.`user_id` AND o.`team_sign` = " . $team_sign;
                $rows = $GLOBALS['db']->getAll($sql);
                foreach ($rows as $key => $row) {
                    if($row['openid'])
                    $weixin->send_wxmsg($row['openid'], $_team_msg['team_suc_first']['title'] , 'share.php?team_sign='.$team_sign , $_team_msg['team_suc_first']['title'] );
                    // 开团之后发优惠券
                    if($GLOBALS['_CFG']['send_bonus_time'] == 0){
                        send_order_bonus($row['order_id']);
                    }                    
                }
            }
        }
        

    }
    doFenxiao($order_info);
    //发送通知
    send_msg_to_suppliers($order_info['suppliers_id']);
    // 支付之后发优惠券
    if($GLOBALS['_CFG']['send_bonus_time'] ==2){
        send_order_bonus($order_info['order_id']);
    }
}
//给商家发送消息
function send_msg_to_suppliers($suppliers_id){
    if (!$suppliers_id) {
        return false;
    }
    // $sql = "SELECT `openid`,`phone`,`user_id`,`email` FROM " . $GLOBALS['hhs']->table('suppliers') . " WHERE `suppliers_id` = " . $suppliers_id;
    $sql = "SELECT u.`openid` from " . $GLOBALS['hhs']->table('suppliers') . " as s left join " . $GLOBALS['hhs']->table('users') . " as u on u.`user_id` = s.`user_id` WHERE s.`suppliers_id` = " . $suppliers_id;
    $s = $GLOBALS['db']->getRow($sql);

    if($s['openid'])
    {
        $weixin = new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
        $openid = $s['openid'];
        $title  = '您的店铺有新订单啦！';
        $url    = '';
        $desc   = '请抓紧时间处理订单';

        $weixin->send_wxmsg($openid, $title , $url , $desc );
    }

}
?>
