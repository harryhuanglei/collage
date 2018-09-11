<?php

/**

 * 小舍电商 购物流程

 * ============================================================================

 * 版权所有 2005-2010 无锡三舍文化传媒有限公司，并保留所有权利。

 * 网站地址: http://www.baidu.com；

 * ----------------------------------------------------------------------------

 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

 * 使用；不允许对程序代码以任何形式任何目的的再发布。

 * ============================================================================

 * $Author: douqinghua $

 * $Id: flow.php 17218 2011-01-24 04:10:41Z douqinghua $

 */

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . 'includes/lib_orders.php');

/* 载入语言文件 */

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

/*------------------------------------------------------ */

//-- INPUT

/*------------------------------------------------------ */

if (!isset($_REQUEST['step']))

{

    $_REQUEST['step'] = "checkout";

}

/*------------------------------------------------------ */

//-- PROCESSOR

/*------------------------------------------------------ */

assign_template();

$smarty->assign('page_title',       '购物车');    // 页面标题

$smarty->assign('lang',             $_LANG);

$smarty->assign('show_marketprice', $_CFG['show_marketprice']);

$smarty->assign('data_dir',    DATA_DIR);       // 数据目录

/*获取商品的属性处理相应的属性库存*/
if($_REQUEST['step'] == 'post_attr_pro')
{
    include_once('includes/cls_json.php');
    $_POST['arr_attr_stock']=strip_tags(urldecode($_POST['arr_attr_stock']));
    $_POST['arr_attr_stock'] = json_str_iconv($_POST['arr_attr_stock']);
    $goods_id = intval($_POST['goods_id']);
    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;
    $arr_attr_stock = $json->decode($_POST['arr_attr_stock']);
    /*判断商品是否存在属性库存*/
    if($goods_id > 0)
    {
        /*获取商品总库存*/
        $sql = "SELECT goods_number FROM ". $GLOBALS['hhs']->table('goods') ." WHERE goods_id = '" . $goods_id . "' ";
        $goods_num = $GLOBALS['db']->getOne($sql);
        $result['goods_number'] = $goods_num;
        $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('products'). " WHERE goods_id = '$goods_id' LIMIT 0, 1";
        $prod = $GLOBALS['db']->getRow($sql);
        /*初始化属性库存*/
        $product_info['product_number'] = 0;
        if (is_spec($arr_attr_stock) && !empty($prod))
        {
            $product_info = get_products_info($goods_id, $arr_attr_stock);
            /*判断商品是否有属性*/
            $product_info['check_pro_attr'] = 1;
        }
        if ($product_info['product_number'] == 0)
        {
            $result['error'] = 1;
            $result['message'] = '对不起，商品无库存暂停销售';
            /*判断商品是否有属性且属性库存为零*/
            $product_info = array('product_number' => 0, 'product_id' => 0,'check_pro_attr' => 1);
        }else{
             $result['error'] = 2;
        }
        $result = array_merge($result,$product_info);
        die($json->encode($result));
    }
}



/*------------------------------------------------------ */

//-- 添加商品到购物车

/*------------------------------------------------------ */

if ($_REQUEST['step'] == 'add_to_cart')

{

    include_once('includes/cls_json.php');

    $_POST['goods']=strip_tags(urldecode($_POST['goods']));

    $_POST['goods'] = json_str_iconv($_POST['goods']);

    if (!empty($_REQUEST['goods_id']) && empty($_POST['goods']))

    {

        if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)

        {

            hhs_header("Location:./\n");

        }

        $goods_id = intval($_REQUEST['goods_id']);

        exit;

    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');

    $json  = new JSON;

    if (empty($_POST['goods']))

    {

        $result['error'] = 1;

        die($json->encode($result));

    }

    $goods = $json->decode($_POST['goods']);

    /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */

    if (empty($goods->spec) AND empty($goods->quick))

    {

        $sql = "SELECT a.attr_id, a.attr_name, a.attr_type, ".

            "g.goods_attr_id, g.attr_value, g.attr_price " .

        'FROM ' . $GLOBALS['hhs']->table('goods_attr') . ' AS g ' .

        'LEFT JOIN ' . $GLOBALS['hhs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .

        "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' " .

        'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';

        $res = $GLOBALS['db']->getAll($sql);

        if (!empty($res))

        {

            $spe_arr = array();

            foreach ($res AS $row)

            {

                $spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];

                $spe_arr[$row['attr_id']]['name']     = $row['attr_name'];

                $spe_arr[$row['attr_id']]['attr_id']     = $row['attr_id'];

                $spe_arr[$row['attr_id']]['values'][] = array(

                                                            'label'        => $row['attr_value'],

                                                            'price'        => $row['attr_price'],

                                                            'format_price' => price_format($row['attr_price'], false),

                                                            'id'           => $row['goods_attr_id']);

            }

            $i = 0;

            $spe_array = array();

            foreach ($spe_arr AS $row)

            {

                $spe_array[]=$row;

            }

            $result['error']   = ERR_NEED_SELECT_ATTR;

            $result['goods_id'] = $goods->goods_id;

            $result['parent'] = $goods->parent;

            // $result['message'] = '此商品为多属性商品，请到详情页购买';

            $result['message'] = $spe_array;

            //die(json_encode(array('error'=>22,'message'=>'asdf')));

            die($json->encode($result));

        }

    }

    /* 更新：如果是一步购物，先清空购物车 */

    if ($_CFG['one_step_buy'] == '1')

    {

        clear_cart();

    }

    /* 检查：商品数量是否合法 */

    if (!is_numeric($goods->number) || intval($goods->number) <= 0)

    {

        $result['error']   = 1;

        $result['message'] = $_LANG['invalid_number'];

    }
    /*是否仅限使用APP购买app_loaddown_url*/
    if($goods->goods_id > 0 && $_CFG['open_app'])
    {
       $sql = "SELECT goods_name,is_app FROM ". $GLOBALS['hhs']->table('goods') ." WHERE goods_id = '" . $goods->goods_id . "' ";
        $res = $GLOBALS['db']->getRow($sql);
        if($res['is_app'] > 0 && !empty($_CFG['app_loaddown_url']))
        {
            $result['error']   = 12;

            $result['message'] = '该商品仅限使用APP购买';

            $result['url']    =  $_CFG['app_loaddown_url'];
            die($json->encode($result));
        }
    }

	if ($_SESSION['user_id'] == 0)

    {

        $result['error']   = 11;

        $result['message'] = '您未登录';

        $url=urlencode('goods.php?id='.$goods->goods_id);

		$result['url']    =  "user.php";

    }

    /* 更新：购物车 */

    else

    {
        //判断是否需要关注

        $sql="select subscribe from ".$hhs->table('goods')." where goods_id=".$goods->goods_id;

        $subscribe=$db->getOne($sql);

        if($subscribe==1){

            $sql="select is_subscribe from ".$hhs->table('users')." where user_id=".$_SESSION['user_id'];

            $is_subscribe=$db->getOne($sql);

            if($is_subscribe==0){

                if(!empty($_CFG['subscribe_url'])){

                    $result['message']  = "您要购买的商品仅限使用微信关注后购买，点击确定立即去关注仅限使用微信";
                    $result['error']    =  7;
                    $result['url']    =  $_CFG['subscribe_url'];
                    die($json->encode($result));

                }

            }

        }
        /*判断商品限购商品数量这里和商品属性无关*/
        $sql="select limit_buy_bumber from ".$hhs->table('goods')." where goods_id=".$goods->goods_id;

        $limit_buy_bumber=$db->getOne($sql);
        $limit_buy_bumber = intval($limit_buy_bumber);
        /*判断限购数量*/
        if($limit_buy_bumber > 0)
        {
        	/*判断用户是否添加该商品进入购物车*/
        	$sql="select sum(goods_number) from ".$hhs->table('cart')." where session_id = '" .SESS_ID. "' AND goods_id=".$goods->goods_id;
        	$goods_number=$db->getOne($sql);
        	$goods_number = intval($goods_number);
        	if($goods_number >= $limit_buy_bumber)
        	{
        		$result['message']  = "您要购买的商品仅限购".$limit_buy_bumber;
                $result['error']    =  3;
                $result['url']    =  'flows.php?step=cart';
                die($json->encode($result));
        	}
        }

        if(!empty($goods->spec))

        {

            foreach ($goods->spec as  $key=>$val )

            {

                $goods->spec[$key]=intval($val);

            }

        }
        /*商品没有属性的情况下，只加入购物车*/
        // if($goods->type > 0)
        // {
        //     $goods->number = 1;
        // }
        // 更新：添加到购物车

        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent))

        {

            if ($_CFG['cart_confirm'] > 2)

            {

                $result['message'] = '';

            }

            else

            {

                $result['message'] = '该商品已加入购物车';

            }

            if (empty($goods->spec)) {

                $result['message'] = '';

            }

            $result['content'] = insert_cart_info();

            $result['one_step_buy'] = $_CFG['one_step_buy'];

        }

        else

        {

            $result['message']  = $err->last_message();

            $result['error']    = $err->error_no;

            $result['goods_id'] = stripslashes($goods->goods_id);

            if (is_array($goods->spec))

            {

                $result['product_spec'] = implode(',', $goods->spec);

            }

            else

            {

                $result['product_spec'] = $goods->spec;

            }

        }

    }

    $result['rec_type'] = $goods->rec_type;

    $result['type'] = $goods->type;
    /*立即加入购物车*/
    if($goods->type > 0)
    {
        $result['message'] = '加入购物车成功！';
    }
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'address_list')

{

    /*------------------------------------------------------ */

    //-- 收货人信息

    /*------------------------------------------------------ */

    include_once('includes/lib_transaction.php');

    $smarty->assign('default_address_id',  get_user_address_id($_SESSION['user_id']) );

    //echo get_user_address_id($_SESSION['user_id']);exit();

    $consignee_list = get_consignee_list($_SESSION['user_id']);

    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

    //省 市

    foreach($consignee_list as $idx=>$value)

    {

        $consignee_list[$idx]['province_name'] = get_regions_name($value['province']);

        $consignee_list[$idx]['city_name'] = get_regions_name($value['city']);

        $consignee_list[$idx]['district_name'] = get_regions_name($value['district']);

        $consignee_list[$idx]['area_name'] = get_regions_name($value['area']);

    }

    $smarty->assign('consignee_list', $consignee_list);

    $address_row = get_user_address(get_user_address_id($_SESSION['user_id']));

    //当前地址id

   // $smarty->assign('s_address_id', get_user_address_id($_SESSION['user_id']));

    $smarty->assign('address_row',$address_row);

    /*

    $forward="flow.php?step=checkout";

    $smarty->assign('forward', $forward);*/

    $forward="flows.php?step=address_list";

    $smarty->assign('forward', $forward);

}

/* 设置默认地址 */

elseif ($_REQUEST['step'] == 'set_address')

{

	    $user_id = $_SESSION['user_id'];

        $address_id = empty($_REQUEST['id'])?0:intval($_REQUEST['id']);

        if($db->query("UPDATE " . $hhs->table('users') . " SET address_id = $address_id  WHERE user_id='$user_id'")){ 

			hhs_header("Location: flows.php?step=address_list\n");

        }

}

/* 删除收货地址 */

elseif ($_REQUEST['step'] == 'drop_consignee')

{

    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))

    {

        hhs_header("Location: flows.php?step=address_list\n");

        exit;

    }

    else

    {

        show_message($_LANG['del_address_false']);

    }

}

elseif ($_REQUEST['step'] == 'edit_consignee')

{

    //编辑收货人地址

    include_once('includes/lib_transaction.php');

    $address_id=$_REQUEST['address_id'];

    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('user_address') .

    " WHERE address_id = '$address_id' ";

    $consignee=$GLOBALS['db']->getRow($sql);

    $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 1;

    $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;

    $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

    $province_list = get_regions(1, 1);

    $city_list     = get_regions(2, $consignee['province']);

    $district_list = get_regions(3, $consignee['city']);

	/*pangbin start*/

	if(empty($address_id))

	{

		$region_type = get_region_type($_SESSION['site_id']); //获取定位地区类别

		switch ($region_type)

		{

			case 1:

				$province_on = $_SESSION['site_id'];

			break;

			case 2:

				$city_on = $_SESSION['site_id'];

				$province_on = get_region_parent($_SESSION['site_id']);

				$city_list     = get_regions(2, $province_on);

				$district_list = get_regions(3, $city_on);

			break;

			case 3:

				$district_on = $_SESSION['site_id'];

				$city_on = get_region_parent($district_on);

				$province_on = get_region_parent($city_on);

				$city_list     = get_regions(2, $province_on);

				$district_list = get_regions(3, $city_on);

			break;

		}

		$smarty->assign('province_on',    $province_on);

		$smarty->assign('city_on',    $city_on);

		$smarty->assign('district_on',    $district_on);	

	}

	/*pangbin end*/

    $smarty->assign('country_list',       get_regions());

    $smarty->assign('province_list',    $province_list);

    $smarty->assign('address',          $address_id);

    $smarty->assign('city_list',        $city_list);

    $smarty->assign('district_list',    $district_list);

    $smarty->assign('consignee',    $consignee);

    $smarty->assign('back_url',   "flows.php" );

    $smarty->display('edit_consignee.dwt');

    exit();

}

elseif ($_REQUEST['act'] == 'act_edit_consignee')

{

    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

    $smarty->assign('lang', $_LANG);

    $address = array(

        'user_id'    => $_SESSION['user_id'],

        'address_id' => intval($_POST['address_id']),

        'address_type' => intval($_POST['address_type']),

        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,

        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,

        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,

        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,

        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',

        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',

        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',

        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',

        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',

        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',

        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',

        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',

    );

    unset($_SESSION['flow_consignee']);

    if ($address_id=update_address($address))

    {   

        //hhs_header('location:flow.php?step=address_list');

        hhs_header('location:flows.php?step=checkout&address_id='.$address_id);

        //show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');

    }

}

/*------------------------------------------------------ */

//-- 购物车

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'checkout')

{

    /*------------------------------------------------------ */

    //-- 订单确认

    /*------------------------------------------------------ */

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 团购标志 */

    if ($flow_type == CART_GROUP_BUY_GOODS)

    {

        $smarty->assign('is_group_buy', 1);

    }

    /* 积分兑换商品 */

    elseif ($flow_type == CART_EXCHANGE_GOODS)

    {

        $smarty->assign('is_exchange_goods', 1);

    }

    else

    {

        //正常购物流程  清空其他购物流程情况

        $_SESSION['flow_order']['extension_code'] = '';

    }

    /* 检查购物车中是否有商品 */

    $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') .

        " WHERE session_id = '" . SESS_ID . "' " .

        "AND parent_id = 0 AND is_gift = 0 AND is_checked = 1 AND rec_type = '$flow_type'";

    if ($db->getOne($sql) == 0)

    {

        show_message($_LANG['no_goods_in_cart'], '', '', 'warning');

    }

    unset($_SESSION['flow_consignee']);

    $address_id = isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0;

    $consignee = $db->getRow('select * from '.$hhs->table('user_address').' where address_id= "'.$address_id.'" and user_id= "'.$_SESSION['user_id'].'"');

    if (empty($consignee)) {

        $consignee = get_consignee($_SESSION['user_id']);

    }

        $consignee['province_name'] = get_regions_name($consignee['province']);

        $consignee['city_name'] = get_regions_name($consignee['city']);

        $consignee['district_name'] = get_regions_name($consignee['district']);

        $consignee['area_name'] = get_regions_name($consignee['area']);

    /* 检查收货人信息是否完整 */

    if (!check_consignee_info($consignee, $flow_type))

    {

        /* 如果不完整则转向到收货人信息填写界面 */

        $url=urlencode('flows.php?step=address_list');

        hhs_header("Location: flows.php?step=edit_consignee&back_url=".$url."\n");

        exit;

    }

    $_SESSION['flow_consignee'] = $consignee;

    $smarty->assign('consignee', $consignee);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    /* 对是否允许修改购物车赋值 */

    if ($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1')

    {

        $smarty->assign('allow_edit_cart', 0);

    }

    else

    {

        $smarty->assign('allow_edit_cart', 1);

    }

    /*

     * 取得购物流程设置

     */

    $smarty->assign('config', $_CFG);

    /*

     * 取得订单信息

     */

    unset($_SESSION['flow_order']);

    $order = flow_order_info();

    $smarty->assign('order', $order);

    /* 计算折扣 */

    if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS)

    {

        $discount = compute_discount();

        $smarty->assign('discount', $discount['discount']);

        $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);

        $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));

    }

    /*

     * 计算订单的费用

     */

    $total = order_fee($order, $cart_goods, $consignee);

    $smarty->assign('total', $total);

    $smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));

    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

    /*pangbin 获取用户默认自提点 start*/

	$u_info = get_u_info($_SESSION['user_id']);

	$smarty->assign('u_point',         $u_info['u_point']);

	$smarty->assign('u_mobile',         $u_info['u_mobile']);

	/*pangbin 获取用户默认自提点 end*/

    /* 取得配送列表 */

    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

    $shipping_lists = array();

    $bonus_lists = array();

    foreach ($cart_goods as $suppliers_id => $value) {

        $goods_id_list = array();

        foreach ($value['goods_list'] as $goods) {

            $goods_id_list[] = $goods['goods_id'];

        }

        $shipping_list     = available_shipping_list($region,$suppliers_id,$goods_id_list);

        $cart_weight_price = cart_weight_price($flow_type,$suppliers_id);

        // 自提

        $point_list = array();

        foreach ($shipping_list as $key => $shipping) {

            if ($shipping['shipping_code'] == 'cac') {

                $point_list = available_point_list($region,$suppliers_id);

                if (empty($point_list)) {

                    unset($shipping_list[$key]);

                }

                break;

            }

        }

        // 查看购物车中是否全为免运费商品，若是则把运费赋为零

        $sql = 'SELECT count(*) FROM ' . $hhs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0 AND `suppliers_id` = '" . $suppliers_id. "'";

        $shipping_count = $db->getOne($sql);

        foreach ($shipping_list AS $key => $val)

        {

            $shipping_cfg = unserialize_config($val['configure']);

            $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),

            $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);

            $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);

            $shipping_list[$key]['shipping_fee']        = $shipping_fee;

            $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);

            $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?

                price_format($val['insure'], false) : $val['insure'];

            /* 当前的配送方式是否支持保价 */

            if ($val['shipping_id'] == $order['shipping_id'])

            {

                $insure_disabled = ($val['insure'] == 0);

                $cod_disabled    = ($val['support_cod'] == 0);

            }

            else{

                $insure_disabled   = true;

                $cod_disabled      = true;

            }

            $shipping_list[$key]['insure_disabled'] = $insure_disabled;

            $shipping_list[$key]['cod_disabled'] = $cod_disabled;

        }

        $cart_goods[$suppliers_id]['shipping_lists'] = $shipping_list;

        unset($shipping_list);

        $cart_goods[$suppliers_id]['point_list'] = $point_list;

        $user_bonus = array();

        $allow_use_bonus = 0;

        /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */

        if ((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1')

            && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))

        {

            // 取得用户可用红包

            $user_bonus = user_bonus($_SESSION['user_id'], $value['subtotal'], $suppliers_id);

			$user_bonus_ty = user_bonus_ty($_SESSION['user_id'], $value['subtotal']);

			if($suppliers_id > 0){

            $user_bonus = array_merge_recursive($user_bonus,$user_bonus_ty);

			}		

            if (!empty($user_bonus))

            {

                foreach ($user_bonus AS $key => $val)

                {

                    $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);

                }

                $allow_use_bonus = 1;

            }

            foreach($cart_goods[$suppliers_id]['goods_list'] as $v){

                $sql = "SELECT bonus_allowed FROM ".$hhs->table("goods")." WHERE goods_id = " . $v['goods_id'];

                $allow_use_bonus2 =  $db->getOne($sql);

                if($allow_use_bonus2 &&  $allow_use_bonus){

                    $allow_use_bonus = $allow_use_bonus2;

                    break;

                }

            }

            $allow_use_bonus = $allow_use_bonus && $allow_use_bonus2;

        }        

        $cart_goods[$suppliers_id]['bonus_list'] = $user_bonus;

        $cart_goods[$suppliers_id]['allow_use_bonus'] = $allow_use_bonus;

        unset($value);

    }
    $smarty->assign('goods_list', $cart_goods);

    $smarty->assign('nums', count($cart_goods));

    /* 取得支付列表 */

    $payment_list = available_payment_list(1);

    if(isset($payment_list))

    {

        foreach ($payment_list as $key => $payment)

        {

            if ($payment['is_cod'] == '1')

            {

                $payment_list[$key]['format_pay_fee'] = '<span id="HHS_CODFEE">' . $payment['format_pay_fee'] . '</span>';

            }

            /* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */

            if ($payment['pay_code'] == 'yeepayszx' && $total['amount'] > 300)

            {

                unset($payment_list[$key]);

            }

            /* 如果有余额支付 */

            if ($payment['pay_code'] == 'balance')

            {

                /* 如果未登录，不显示 */

                if ($_SESSION['user_id'] == 0)

                {

                    unset($payment_list[$key]);

                }

                else

                {

                    if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id'])

                    {

                        $smarty->assign('disable_surplus', 1);

                    }

                }

            }

        }

    }

    $smarty->assign('payment_list', $payment_list);

    $user_info = user_info($_SESSION['user_id']);

    /* 如果使用余额，取得用户余额 */

    if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1')

        && $_SESSION['user_id'] > 0

        && $user_info['user_money'] > 0)

    {

        // 能使用余额

        $smarty->assign('allow_use_surplus', 1);

        $smarty->assign('your_surplus', $user_info['user_money']);

    }

    /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */

    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')

        && $_SESSION['user_id'] > 0

        && $user_info['pay_points'] > 0

        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))

    {

        // 能使用积分

        $smarty->assign('allow_use_integral', 1);

        $smarty->assign('order_max_integral', flow_available_points());  // 可用积分

        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分

    }

    /* 保存 session */

    $_SESSION['flow_order'] = $order;

}

elseif ($_REQUEST['step'] == 'select_shipping')

{

    /*------------------------------------------------------ */

    //-- 改变配送方式

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $suppliers_id = isset($_REQUEST['suppliers_id']) ? intval($_REQUEST['suppliers_id']) : 0;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods[$suppliers_id]) || !check_consignee_info($consignee, $flow_type))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得订单信息 */

        $order = flow_order_info();

        $order[$suppliers_id]['order']['shipping_id'] = intval($_REQUEST['shipping_id']);

        $order[$suppliers_id]['order']['express_id']  = intval($_REQUEST['express_id']);

        $order[$suppliers_id]['order']['point_id']    = 0;
        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

// print_r($order);die();

        /* 计算订单的费用 */       

        $total = order_fee($order, $cart_goods, $consignee);

//print_r($cart_goods);die;

        $result['data'] = $total;

    }

    echo $json->encode($result);

    exit;

}

elseif ($_REQUEST['step'] == 'select_point')

{

    /*------------------------------------------------------ */

    //-- 改变配送方式

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $suppliers_id = isset($_REQUEST['suppliers_id']) ? intval($_REQUEST['suppliers_id']) : 0;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods[$suppliers_id]) || !check_consignee_info($consignee, $flow_type))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得订单信息 */

        $order = flow_order_info();

        $order[$suppliers_id]['order']['point_id']    = intval($_REQUEST['point_id']);

        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

// print_r($order);die();

        /* 计算订单的费用 */       

        $total = order_fee($order, $cart_goods, $consignee);

        $result['data'] = $total;

    }

    echo $json->encode($result);

    exit;

}

elseif ($_REQUEST['step'] == 'select_payment')

{

    /*------------------------------------------------------ */

    //-- 改变支付方式

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $order['pay_id'] = intval($_REQUEST['payment']);

        $payment_info = payment_info($order['pay_id']);

        $result['pay_code'] = $payment_info['pay_code'];

        /* 保存 session */

        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */

        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);

        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */

        if ($flow_type == CART_GROUP_BUY_GOODS)

        {

            $smarty->assign('is_group_buy', 1);

        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');

    }

    echo $json->encode($result);

    exit;

}

elseif ($_REQUEST['step'] == 'change_surplus')

{

    /*------------------------------------------------------ */

    //-- 改变余额

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $surplus   = floatval($_GET['surplus']);

    $user_info = user_info($_SESSION['user_id']);

    $suppliers_id = isset($_REQUEST['suppliers_id']) ? intval($_REQUEST['suppliers_id']) : 0;

    if ($user_info['user_money'] + $user_info['credit_line'] < $surplus)

    {

        $result['error'] = $_LANG['surplus_not_enough'];

    }

    else

    {

        /* 取得购物类型 */

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 获得收货人信息 */

        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */

        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

        if (!check_consignee_info($consignee, $flow_type))

        {

            $result['error'] = $_LANG['no_goods_in_cart'];

        }

        else

        {

            /* 取得订单信息 */

            $order = flow_order_info();

            $order[$suppliers_id]['order']['surplus'] = $surplus;

            // 重新计算余额

            $user_money = $user_info['user_money'];

            foreach ($order as $suppliers_id => $o) {

                $user_money -= $o['order']['surplus'];

                if($user_money<=0)

                    $order[$suppliers_id]['order']['surplus'] = 0;

            }

            /* 计算订单的费用 */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total', $total);

            /* 团购标志 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $smarty->assign('is_group_buy', 1);

            }

            $result['content'] = $smarty->fetch('library/order_totals.lbi');

        }

    }

    $json = new JSON();

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'change_bonus')

{

    /*------------------------------------------------------ */

    //-- 改变红包

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $suppliers_id = isset($_REQUEST['suppliers_id']) ? intval($_REQUEST['suppliers_id']) : 0;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods[$suppliers_id]) || !check_consignee_info($consignee, $flow_type))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus_id']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id'] && $bonus['suppliers_id'] == $suppliers_id) || $_GET['bonus'] == 0 || $bonus['tongyong'] == 1)

        {

            $order[$suppliers_id]['order']['bonus_id'] = intval($_GET['bonus_id']);

        }

        else

        {

            $order[$suppliers_id]['order']['bonus_id'] = 0;

            $result['error'] = $_LANG['invalid_bonus'];

        }

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $result['data'] = $total;

    }

    $json = new JSON();

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'check_surplus')

{

    /*------------------------------------------------------ */

    //-- 检查用户输入的余额

    /*------------------------------------------------------ */

    $surplus   = floatval($_GET['surplus']);

    $user_info = user_info($_SESSION['user_id']);

    if (($user_info['user_money'] + $user_info['credit_line'] < $surplus))

    {

        die($_LANG['surplus_not_enough']);

    }

    exit;

}

/*------------------------------------------------------ */

//-- 完成所有订单操作，提交到数据库

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'done')

{

    include_once('includes/lib_clips.php');

    include_once('includes/lib_payment.php');

    include_once('includes/cls_json.php');

    $json  = new JSON;    

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 检查购物车中是否有商品 */

    $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') .

        " WHERE session_id = '" . SESS_ID . "' " .

        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";

    if ($db->getOne($sql) == 0)

    {

        // show_message($_LANG['no_goods_in_cart'], '', '', 'warning');

        $result = array(

            'error'    => 2, 

            'message'  => $_LANG['no_goods_in_cart'], 

            'url'      => './',

        );

        die($json->encode($result));                   

    }

    /* 检查商品库存 */

    /* 如果使用库存，且下订单时减库存，则减少库存 */

    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

    {

        $cart_goods_stock = get_cart_goods();

        $_cart_goods_stock = array();

        foreach ($cart_goods_stock['goods_list'] as $value)

        {

            foreach ($value['goods_list'] as $v) {

                $_cart_goods_stock[$v['rec_id']] = $v['goods_number'];

            }

        }

        flow_cart_stock($_cart_goods_stock);

        unset($cart_goods_stock, $_cart_goods_stock);

    }

    $consignee = get_consignee($_SESSION['user_id']);

    /* 检查收货人信息是否完整 */

    // if (!check_consignee_info($consignee, $flow_type))

    // {

    //      如果不完整则转向到收货人信息填写界面 

    //     hhs_header("Location: flows.php?step=consignee\n");

    //     exit;

    // }

    /* 订单中的商品 */

    $all_cart_goods = cart_goods($flow_type);

    if (empty($all_cart_goods))

    {

        // show_message($_LANG['no_goods_in_cart'], $_LANG['back_home'], './', 'warning');

        $result = array(

            'error'    => 2, 

            'message'  => $_LANG['no_goods_in_cart'], 

            'url'      => './',

        );

        die($json->encode($result));                   

    }

    $_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;

    $_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';

    $_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';

    $_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';

    $_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';

    $_POST['postscript'] = isset($_POST['postscript']) ? urldecode($_POST['postscript']) : '';

    $_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';

	//echo "<pre>";

//	print_r($_POST);

	//exit;

// 分单start

// 

/* 取得订单信息 */

$orders = flow_order_info();

$created_orders = array();// 创建的订单id

$usedSurplus    = 0.00;

$order_amount   = 0.00;

$order_goods_name = array();

foreach ($all_cart_goods as $suppliers_id => $suppliers_goods) {

    if (! is_numeric($suppliers_id)) {

        continue;

    }

    //重新定义cart_goods

    $cart_goods = $suppliers_goods['goods_list'];

    foreach ($cart_goods as $goods) {

        $order_goods_name[] = $goods['goods_name'];

    }

    $order = array(

        'shipping_id'     => intval($_POST['shipping']),

        'pay_id'          => intval($_POST['payment']),

        'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,

        'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,

        'card_message'    => trim($_POST['card_message']),

        'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,

        'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,

        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus'][$suppliers_id]) : 0,

        'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,

        'inv_type'        => $_POST['inv_type'],

        'inv_payee'       => trim($_POST['inv_payee']),

        'inv_content'     => $_POST['inv_content'],

        'postscript'      => trim($_POST['postscript']),

        'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',

        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,

        'user_id'         => $_SESSION['user_id'],

        'add_time'        => gmtime(),

        'order_status'    => OS_UNCONFIRMED,

        'shipping_status' => SS_UNSHIPPED,

        'pay_status'      => PS_UNPAYED,

        'agency_id'       => 0,

        'point_id'        => isset($_POST['point_id']) ? intval($_POST['point_id'][$suppliers_id]) : 0,

        );

	unset($orders[$suppliers_id]['order']['point_id']);

    // 合并SESSION中计算好的

    $order = array_merge($order,$orders[$suppliers_id]['order']);

    /* 扩展信息 */

    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)

    {

        $order['extension_code'] = $_SESSION['extension_code'];

        $order['extension_id'] = $_SESSION['extension_id'];

    }

    else

    {

        $order['extension_code'] = '';

        $order['extension_id'] = 0;

    }

    /* 检查积分余额是否合法 */

    $user_id = $_SESSION['user_id'];

    if ($user_id > 0)

    {

        $user_info = user_info($user_id);

        $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line'] - $usedSurplus);

        if ($order['surplus'] < 0)

        {

            $order['surplus'] = 0;

        }

        $usedSurplus      += $order['surplus'];//使用了的余额

        $_POST['surplus'] -= $order['surplus'];//post数据还剩余的余额

        // 查询用户有多少积分

        // $flow_points = flow_available_points();  // 该订单允许使用的积分

        // $user_points = $user_info['pay_points']; // 用户的积分总数

        // $order['integral'] = min($order['integral'], $user_points, $flow_points);

        // if ($order['integral'] < 0)

        // {

        //     $order['integral'] = 0;

        // }

    }

    else

    {

        $order['surplus']  = 0;

        $order['integral'] = 0;

    }

    /* 检查红包是否存在 */

    if ($order['bonus_id'] > 0)

    {

        $bonus = bonus_info($order['bonus_id']);

		if($bonus['tongyong']==1)

		{

			if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))

			{

				$order['bonus_id'] = 0;

			}

		}

		else

		{

			if (empty($bonus) || $bonus['suppliers_id'] != $suppliers_id || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))

			{

				$order['bonus_id'] = 0;

			}

		}

    }

	unset($consignee['user_id']);

    /* 收货人信息 */

    foreach ($consignee as $key => $value)

    {

        $order[$key] = addslashes($value);

    }

    // 自提点

    if ($order['point_id'] > 0) {

        $order['checked_mobile'] = trim($_POST['checked_mobile'][$suppliers_id]);

        $order['best_time']      = trim($_POST['best_time'][$suppliers_id]);

    }

    else{

        $order['checked_mobile'] = '';

        $order['best_time']      = '';

    }

    if (! empty($order['checked_mobile'])) {

        $db->query('update '.$hhs->table('user_address').' set `mobile` = "'.$order['checked_mobile'].'" where user_id = "'.$_SESSION['user_id'].'" AND address_id = "'.$consignee['address_id'].'"');

    } 

   /* 判断是不是实体商品 */

    foreach ($cart_goods AS $val)

    {

        /* 统计实体商品的个数 */

        if ($val['is_real'])

        {

            $is_real_good=1;

        }

    }

    if(isset($is_real_good))

    {

        $sql="SELECT shipping_id FROM " . $hhs->table('shipping') . " WHERE shipping_id=".$order['shipping_id'] ." AND enabled =1"; 

        //die(json_encode(array('error'=>2,'message'=>$db->getOne($sql) )));

        if(!$db->getOne($sql))

        {

           //show_message($_LANG['flow_no_shipping']);

            $result = array(

                'error'    => 2, 

                'message'  => '配送地址存在问题，请前往订单列表', 

                'url'      => 'user.php?act=order_list',

            );

            die($json->encode($result));           

        }

    }

    // print_r($suppliers_goods);

    /* 订单中的总额 */

    // $total = order_fee($order, $cart_goods, $consignee);

    $suppliers_fee = calc_suppliers_fee($order, $suppliers_goods['goods_list'], $consignee, $suppliers_id);

    $total = $suppliers_fee['total'];

    $order['bonus']        = $total['bonus'];

    $order['goods_amount'] = $total['goods_price'];

    $order['discount']     = $total['discount'];

    $order['surplus']      = $total['surplus'];

    $order['tax']          = $total['tax'];

    // 购物车中的商品能享受红包支付的总额

    $discount_amout = compute_discount_amount($suppliers_id);

    // 红包和积分最多能支付的金额为商品总额

    $temp_amout = $order['goods_amount'] - $discount_amout;

    if ($temp_amout <= 0)

    {

        $order['bonus_id'] = 0;

    }

    /* 配送方式 */

    if ($order['shipping_id'] > 0)

    {

        $shipping = shipping_info($order['shipping_id']);

        $order['shipping_name'] = addslashes($shipping['shipping_name']);

        /*如果不是自提初始化point_id*/
        if($shipping['shipping_code'] != 'cac')
        {
            $order['point_id'] = 0;
        }

    }
    $order['shipping_fee'] = $total['shipping_fee'];

    $order['insure_fee']   = $total['shipping_insure'];

    /* 支付方式 */

    if ($order['pay_id'] > 0)

    {

        $payment = payment_info($order['pay_id']);

        $order['pay_name'] = addslashes($payment['pay_name']);

    }

    $order['pay_fee'] = $total['pay_fee'];

    $order['cod_fee'] = $total['cod_fee'];

    /* 商品包装 */

    // if ($order['pack_id'] > 0)

    // {

    //     $pack               = pack_info($order['pack_id']);

    //     $order['pack_name'] = addslashes($pack['pack_name']);

    // }

    // $order['pack_fee'] = $total['pack_fee'];

    /* 祝福贺卡 */

    // if ($order['card_id'] > 0)

    // {

    //     $card               = card_info($order['card_id']);

    //     $order['card_name'] = addslashes($card['card_name']);

    // }

    // $order['card_fee']      = $total['card_fee'];

    $order['order_amount']  = number_format($total['amount'], 2, '.', '');

    /* 如果全部使用余额支付，检查余额是否足够 */

    if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)

    {

        if($order['surplus'] >0) //余额支付里如果输入了一个金额

        {

            $order['order_amount'] = $order['order_amount'] + $order['surplus'];

            $order['surplus'] = 0;

        }

        if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))

        {

            $result = array(

                'error'    => 2, 

                'message'  => $_LANG['balance_not_enough'], 

                'url'      => 'user.php?act=order_list',

            );

            die($json->encode($result));

            // show_message($_LANG['balance_not_enough']);

        }

        else

        {

            $order['surplus'] = $order['order_amount'];

            $order['order_amount'] = 0;

        }

    }

    /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */

    if ($order['order_amount'] <= 0)

    {

        $order['order_status'] = OS_CONFIRMED;

        $order['confirm_time'] = gmtime();

        $order['pay_status']   = PS_PAYED;

        $order['pay_time']     = gmtime();

        $order['order_amount'] = 0;

    }

    $order['integral_money']   = $total['integral_money'];

    $order['integral']         = $total['integral'];

    if ($order['extension_code'] == 'exchange_goods')

    {

        $order['integral_money']   = 0;

        $order['integral']         = $total['exchange_integral'];

    }

    $order['from_ad']          = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';

    $order['referer']          = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

    /* 记录扩展信息 */

    if ($flow_type != CART_GENERAL_GOODS)

    {

        $order['extension_code'] = $_SESSION['extension_code'];

        $order['extension_id'] = $_SESSION['extension_id'];

    }

    $order['parent_id'] = $parent_id;

    $order_amount += $order['order_amount'];

    /* 插入订单表 */

    $error_no = 0;

    do

    {

        $order['order_sn'] = get_order_sn(); //获取新订单号

        $GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();

        if ($error_no > 0 && $error_no != 1062)

        {

            die($GLOBALS['db']->errorMsg());

        }

    }

    while ($error_no == 1062); //如果是订单号重复则重新提交数据

    $new_order_id = $db->insert_id();

    $order['order_id'] = $new_order_id;

    /* 插入订单商品 */

    $sql = "INSERT INTO " . $hhs->table('order_goods') . "( " .

                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id,rate_1,rate_2,rate_3) ".

            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id,rate_1,rate_2,rate_3".

            " FROM " .$hhs->table('cart') .

            " WHERE session_id = '".SESS_ID."' AND is_checked=1 AND rec_type = '$flow_type' AND suppliers_id = '$suppliers_id'";

    $db->query($sql);

    /* 修改拍卖活动状态 */

    if ($order['extension_code']=='auction')

    {

        $sql = "UPDATE ". $hhs->table('goods_activity') ." SET is_finished='2' WHERE act_id=".$order['extension_id'];

        $db->query($sql);

    }

    /* 处理余额、积分、红包 */

    if ($order['user_id'] > 0 && $order['surplus'] > 0)

    {

        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));

    }

    if ($order['user_id'] > 0 && $order['integral'] > 0)

    {

        log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf($_LANG['pay_order'], $order['order_sn']));

    }

    if ($order['bonus_id'] > 0 && $temp_amout > 0)

    {

        use_bonus($order['bonus_id'], $new_order_id);

    }

    /* 如果使用库存，且下订单时减库存，则减少库存 */

    // if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

    // {

    //     change_order_goods_storage($order['order_id'], true, SDT_PLACE);

    // }

    /* 给商家发邮件 */

    /* 增加是否给客服发送邮件选项 */

    // if ($_CFG['send_service_email'] && $_CFG['service_email'] != '')

    // {

    //     $tpl = get_mail_template('remind_of_new_order');

    //     $smarty->assign('order', $order);

    //     $smarty->assign('goods_list', $cart_goods);

    //     $smarty->assign('shop_name', $_CFG['shop_name']);

    //     $smarty->assign('send_date', date($_CFG['time_format']));

    //     $content = $smarty->fetch('str:' . $tpl['template_content']);

    //     send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);

    // }

    /* 如果需要，发短信 */

    // if ($_CFG['sms_order_placed'] == '1' && $_CFG['sms_shop_mobile'] != '')

    // {

    //     include_once('includes/cls_sms.php');

    //     $sms = new sms();

    //     $msg = $order['pay_status'] == PS_UNPAYED ?

    //         $_LANG['order_placed_sms'] : $_LANG['order_placed_sms'] . '[' . $_LANG['sms_paid'] . ']';

    //     $sms->send($_CFG['sms_shop_mobile'], sprintf($msg, $order['consignee'], $order['tel']),'', 13,1);

    // }

    /* 如果订单金额为0 处理虚拟卡 */

    if ($order['order_amount'] <= 0)

    {

        $sql = "SELECT goods_id, goods_name, goods_number AS num FROM ".

               $GLOBALS['hhs']->table('cart') .

                " WHERE is_real = 0 AND extension_code = 'virtual_card'".

                " AND session_id = '".SESS_ID."' AND rec_type = '$flow_type'";

        $res = $GLOBALS['db']->getAll($sql);

        $virtual_goods = array();

        foreach ($res AS $row)

        {

            $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);

        }

        if ($virtual_goods AND $flow_type != CART_GROUP_BUY_GOODS)

        {

            /* 虚拟卡发货 */

            if (virtual_goods_ship($virtual_goods,$msg, $order['order_sn'], true))

            {

                /* 如果没有实体商品，修改发货状态，送积分和红包 */

                $sql = "SELECT COUNT(*)" .

                        " FROM " . $hhs->table('order_goods') .

                        " WHERE order_id = '$order[order_id]' " .

                        " AND is_real = 1";

                if ($db->getOne($sql) <= 0)

                {

                    /* 修改订单状态 */

                    update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                    /* 如果订单用户不为空，计算积分，并发给用户；发红包 */

                    if ($order['user_id'] > 0)

                    {

                        /* 取得用户信息 */

                        $user = user_info($order['user_id']);

                        /* 计算并发放积分 */

                        $integral = integral_to_give($order);

                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                        /* 发放红包 */

                        send_order_bonus($order['order_id']);

                    }

                }

            }

        }

    }

    /* 插入支付日志 */

    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

    $created_orders[] = $new_order_id;

}

///////////////////////////////////////////////

///分单 end

    /* 清空购物车 */

    clear_cart();

    /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */

    clear_all_files();

    /* 取得支付信息，生成支付代码 */

    $payment = payment_info(intval($_POST['payment']));

    $pay_code   = $payment['pay_code'];

    if ($order_amount > 0)

    {

        if($pay_code=='wxpay'){

            include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');

            $order = array(

                'order_amount' => $order_amount,

                'order_sn' => time().rand(99,9999),

                'goods_name' => join(',',$order_goods_name),

            );

            $pay_obj    = new $payment['pay_code'];

            $pay_online = $pay_obj->get_code2($order, unserialize_config($payment['pay_config']), $created_orders);

        }

    }else{

        //require(ROOT_PATH . 'includes/lib_order.php');

        $result = array(

            'error'    => 1, 

            'message'  =>'user.php',

            'url'      => "user.php?act=order_detail&order_id=".$new_order_id."&uid=".$uid

        );

        pay_team_action($order['order_sn']);

        die($json->encode($result));

    }

    if(!empty($order['shipping_name']))

    {

        $order['shipping_name']=trim(stripcslashes($order['shipping_name']));

    }

    unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息

    unset($_SESSION['flow_order']);

    unset($_SESSION['direct_shopping']);

    $result = array(

        'error'    => 0, 

        'message'  => '', 

        'pay_code' => $pay_code, 

        'content'  => $pay_online,

        'order_id' => join(',',$created_orders),

        'url'      => 'user.php?act=order_list',

    );

    die($json->encode($result));

}

/*------------------------------------------------------ */

//-- 更新购物车

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'update_cart')

{

    // if (isset($_POST['goods_number']) && is_array($_POST['goods_number']))

    // {

    //     flow_update_cart($_POST['goods_number']);

    // }

    // show_message($_LANG['update_cart_notice'], $_LANG['back_to_cart'], 'flow.php');

    $rec_id       = intval($_REQUEST['rec_id']);

    $goods_number = intval($_REQUEST['number']);

    if($rec_id && $goods_number){

        flow_update_cart(array(

            $rec_id => $goods_number

        ));

    }

    $cart_goods = get_cart_goods();

    $find       = false;

    $subtotal   = 0.00;

    foreach ($cart_goods['goods_list'] as $key => $cart_goods_list) {

        foreach ($cart_goods_list['goods_list'] as $goods) {

            if ($goods['rec_id'] == $rec_id) {

                $goods_number = $goods['goods_number'];

                $subtotal     = $goods['subtotal']; 

                $find         = true;

                break;

            }

        }

        if($find)

            break;

    }

    $data = array(

        'count'  => $cart_goods['total']['real_goods_count'],

        'amount' => $cart_goods['total']['goods_amount'],

    );

    include_once('includes/cls_json.php');

    $json  = new JSON;    

    $result = array(

        'error'        => 0, 

        'message'      => '', 

        'content'      => '', 

        'rec_id'       => $rec_id,

        'goods_number' => $goods_number,

        'subtotal'     => $subtotal,

        'data'         => $data,

    );

    die($json->encode($result));

    exit;

}

/*------------------------------------------------------ */

//-- 更新购物车

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'check_goods')

{

    $is_checked = intval($_REQUEST['is_checked']);

    $rec_id     = intval($_REQUEST['rec_id']);

    $sql = 'UPDATE '.$hhs->table('cart').' set `is_checked` = "'.$is_checked.'" WHERE rec_id = "'.$rec_id.'" and rec_type = 0';

    $db->query($sql);

    $cart_goods = get_cart_goods();

    $data = array(

        'count'  => $cart_goods['total']['real_goods_count'],

        'amount' => $cart_goods['total']['goods_amount'],

    );

    include_once('includes/cls_json.php');

    $json  = new JSON;    

    $result = array(

        'error'        => 0, 

        'message'      => '', 

        'content'      => '', 

        'data'         => $data,

    );

    die($json->encode($result));

    exit;    

}

/*------------------------------------------------------ */

//-- 更新购物车

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'check_all')

{

    $is_checked = intval($_REQUEST['is_checked']);

    $sql = 'UPDATE '.$hhs->table('cart').' set `is_checked` = "'.$is_checked.'"  WHERE rec_type = 0 and session_id="'.SESS_ID.'"';

    $db->query($sql);

    $cart_goods = get_cart_goods();

    $data = array(

        'count'  => $cart_goods['total']['real_goods_count'],

        'amount' => $cart_goods['total']['goods_amount'],

    );

    include_once('includes/cls_json.php');

    $json  = new JSON;    

    $result = array(

        'error'        => 0, 

        'message'      => '', 

        'content'      => '', 

        'data'         => $data,

    );

    die($json->encode($result));

    exit;    

}

/*------------------------------------------------------ */

//-- 删除购物车中的商品

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'drop_goods')

{

    $rec_id = intval($_GET['rec_id']);

    flow_drop_cart_goods($rec_id);

    unset($_SESSION['flow_order']);

    $cart_goods = get_cart_goods();

    $data = array(

        'count'  => $cart_goods['total']['real_goods_count'],

        'amount' => $cart_goods['total']['goods_amount'],

    );

    include_once('includes/cls_json.php');

    $json  = new JSON;    

    $result = array(

        'error'   => 0, 

        'message' => '', 

        'content' => '', 

        'rec_id'  => $rec_id, 

        'data'    => $data,

    );

    die($json->encode($result));

    exit;

}

elseif ($_REQUEST['step'] == 'clear')

{

    $sql = "DELETE FROM " . $hhs->table('cart') . " WHERE session_id='" . SESS_ID . "'";

    $db->query($sql);

    hhs_header("Location:index.php\n");

}

elseif ($_REQUEST['step'] == 'cart')

{

    /* 标记购物流程为普通商品 */

    $_SESSION['flow_type'] = CART_GENERAL_GOODS;

    /* 如果是一步购物，跳到结算中心 */

    if ($_CFG['one_step_buy'] == '1')

    {

        hhs_header("Location: flow.php?step=checkout\n");

        exit;

    }

    /* 取得商品列表，计算合计 */

    $cart_goods = get_cart_goods();

    $smarty->assign('goods_list', $cart_goods['goods_list']);

    $smarty->assign('total', $cart_goods['total']);

    //购物车的描述的格式化

    $smarty->assign('shopping_money',         sprintf($_LANG['shopping_money'], $cart_goods['total']['goods_price']));

    $smarty->assign('market_price_desc',      sprintf($_LANG['than_market_price'],

        $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));

    // 显示收藏夹内的商品

    if ($_SESSION['user_id'] > 0)

    {

        require_once(ROOT_PATH . 'includes/lib_clips.php');

        $collection_goods = get_collection_goods($_SESSION['user_id']);

        $smarty->assign('collection_goods', $collection_goods);

    }

    /* 取得优惠活动 */

    $favourable_list = favourable_list($_SESSION['user_rank']);

    usort($favourable_list, 'cmp_favourable');

    $smarty->assign('favourable_list', $favourable_list);

    /* 计算折扣 */

    $discount = compute_discount();

    $smarty->assign('discount', $discount['discount']);

    $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);

    $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));

    /* 增加是否在购物车里显示商品图 */

    $smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);

    /* 增加是否在购物车里显示商品属性 */

    $smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);

    /* 购物车中商品配件列表 */

    //取得购物车中基本件ID

    $sql = "SELECT goods_id " .

            "FROM " . $GLOBALS['hhs']->table('cart') .

            " WHERE session_id = '" . SESS_ID . "' " .

            "AND rec_type = '" . CART_GENERAL_GOODS . "' " .

            "AND is_gift = 0 " .

            "AND extension_code <> 'package_buy' " .

            "AND parent_id = 0 ";

    $parent_list = $GLOBALS['db']->getCol($sql);

    $fittings_list = get_goods_fittings($parent_list);

    $smarty->assign('fittings_list', $fittings_list);

}

$smarty->assign('currency_format', $_CFG['currency_format']);

$smarty->assign('integral_scale',  $_CFG['integral_scale']);

$smarty->assign('step',            $_REQUEST['step']);

$smarty->display('flows.dwt');

/*------------------------------------------------------ */

//-- PRIVATE FUNCTION

/*------------------------------------------------------ */

/**

 * 获得用户的可用积分

 *

 * @access  private

 * @return  integral

 */

function flow_available_points()

{

    $sql = "SELECT SUM(g.integral * c.goods_number) ".

            "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " . $GLOBALS['hhs']->table('goods') . " AS g " .

            "WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " .

            "AND c.rec_type = '" . CART_GENERAL_GOODS . "'";

    $val = intval($GLOBALS['db']->getOne($sql));

    return integral_of_value($val);

}

/**

 * 更新购物车中的商品数量

 *

 * @access  public

 * @param   array   $arr

 * @return  void

 */

function flow_update_cart($arr)

{

    include_once('includes/cls_json.php');

    $json  = new JSON; 

    /* 处理 */

    foreach ($arr AS $key => $val)

    {

        $val = intval(make_semiangle($val));

        if ($val <= 0 || !is_numeric($key))

        {

            continue;

        }

        //查询：

        $sql = "SELECT `goods_id`, `goods_attr_id`, `product_id`, `extension_code` FROM" .$GLOBALS['hhs']->table('cart').

               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number, g.limit_buy_bumber,g.promote_start_date,g.promote_end_date ".

                "FROM " .$GLOBALS['hhs']->table('goods'). " AS g, ".

                    $GLOBALS['hhs']->table('cart'). " AS c ".

                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

		if($row['promote_start_date'] && $row['promote_end_date'])

		{

			$todayTime = gmtime();

			if($row['promote_start_date'] < $todayTime && $row['promote_end_date'] > $todayTime)

			{

				$is_miao = 1;

			}

		}

        if($row['limit_buy_bumber'] > 0 && $val > $row['limit_buy_bumber'])

        {

            $result = array(

                'error'        => 1, 

                'message'      => '该商品限购，一个用户只能购买' . $row['limit_buy_bumber'], 

                'goods_number'      => $row['limit_buy_bumber'], 

                'rec_id'       => $key,

                'content' => ''

            );

            die($json->encode($result));

        }

        //查询：系统启用了库存，检查输入的商品数量是否有效

        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')

        {

            if ($row['goods_number'] < $val)

            {

                // show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                // $row['goods_number'], $row['goods_number']));

                $result = array(

                    'error'        => 1, 

                    'message'      => sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                    $row['goods_number'], $row['goods_number']), 

                    'goods_number'      => $row['goods_number'], 

                    'rec_id'       => $key,

                    'content' => ''

                );

                die($json->encode($result));

                exit;

            }

            /* 是货品 */

            $goods['product_id'] = trim($goods['product_id']);

            if (!empty($goods['product_id']))

            {

                $sql = "SELECT product_number FROM " .$GLOBALS['hhs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $goods['product_id'] . "'";

                $product_number = $GLOBALS['db']->getOne($sql);

                if ($product_number < $val)

                {

                    // show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                    // $product_number['product_number'], $product_number['product_number']));

                    // exit;

                    $result = array(

                        'error'        => 1, 

                        'message'      => sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                    $product_number['product_number'], $product_number['product_number']), 

                        'goods_number'      => $product_number['product_number'], 

                        'rec_id'       => $key,

                        'content' => ''

                    );

                    die($json->encode($result));

                }

            }

        }

        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')

        {

            if (judge_package_stock($goods['goods_id'], $val))

            {

                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);

                exit;

            }

        }

        /* 查询：检查该项是否为基本件 以及是否存在配件 */

        /* 此处配件是指添加商品时附加的并且是设置了优惠价格的配件 此类配件都有parent_id goods_number为1 */

        $sql = "SELECT b.goods_number, b.rec_id

                FROM " .$GLOBALS['hhs']->table('cart') . " a, " .$GLOBALS['hhs']->table('cart') . " b

                WHERE a.rec_id = '$key'

                AND a.session_id = '" . SESS_ID . "'

                AND a.extension_code <> 'package_buy'

                AND b.parent_id = a.goods_id

                AND b.session_id = '" . SESS_ID . "'";

        $offers_accessories_res = $GLOBALS['db']->query($sql);

        //订货数量大于0

        if ($val > 0)

        {

            /* 判断是否为超出数量的优惠价格的配件 删除*/

            $row_num = 1;

            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))

            {

                if ($row_num > $val)

                {

                    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .

                            " WHERE session_id = '" . SESS_ID . "' " .

                            "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";

                    $GLOBALS['db']->query($sql);

                }

                $row_num ++;

            }

            /* 处理超值礼包 */

            if ($goods['extension_code'] == 'package_buy')

            {

                //更新购物车中的商品数量

                $sql = "UPDATE " .$GLOBALS['hhs']->table('cart').

                        " SET goods_number = '$val' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

            }

            /* 处理普通商品或非优惠的配件 */

            else

            {

                $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);

                $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id,$is_miao);

				$goods_row = $GLOBALS['db']->getRow("select is_miao,promote_price,promote_start_date,promote_end_date,promote_price from ".$GLOBALS['hhs']->table('goods')." where goods_id=".$goods['goods_id']);

			   /*if ($goods_row['is_miao']) {

						$promote_price = bargain_price($goods_row['promote_price'], $goods_row['promote_start_date'], $goods_row['promote_end_date']);

						if($promote_price>0)

						{

							$goods_price = $goods_row['promote_price'];

						}

					}*/ 

                //更新购物车中的商品数量

                $sql = "UPDATE " .$GLOBALS['hhs']->table('cart').

                        " SET goods_number = '$val', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

            }

        }

        //订货数量等于0

        else

        {

            /* 如果是基本件并且有优惠价格的配件则删除优惠价格的配件 */

            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))

            {

                $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .

                        " WHERE session_id = '" . SESS_ID . "' " .

                        "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";

                $GLOBALS['db']->query($sql);

            }

            $sql = "DELETE FROM " .$GLOBALS['hhs']->table('cart').

                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";

        }

        $GLOBALS['db']->query($sql);

    }

    /* 删除所有赠品 */

    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift <> 0";

    $GLOBALS['db']->query($sql);

}

/**

 * 检查订单中商品库存

 *

 * @access  public

 * @param   array   $arr

 *

 * @return  void

 */

function flow_cart_stock($arr)

{

    foreach ($arr AS $key => $val)

    {

        $val = intval(make_semiangle($val));

        if ($val <= 0 || !is_numeric($key))

        {

            continue;

        }

        $sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM" .$GLOBALS['hhs']->table('cart').

               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number, c.product_id ".

                "FROM " .$GLOBALS['hhs']->table('goods'). " AS g, ".

                    $GLOBALS['hhs']->table('cart'). " AS c ".

                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

        //系统启用了库存，检查输入的商品数量是否有效

        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')

        {

            if ($row['goods_number'] < $val)

            {

                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                $row['goods_number'], $row['goods_number']));

                exit;

            }

            /* 是货品 */

            $row['product_id'] = trim($row['product_id']);

            if (!empty($row['product_id']))

            {

                $sql = "SELECT product_number FROM " .$GLOBALS['hhs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";

                $product_number = $GLOBALS['db']->getOne($sql);

                if ($product_number < $val)

                {

                    show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                    $row['goods_number'], $row['goods_number']));

                    exit;

                }

            }

        }

        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')

        {

            if (judge_package_stock($goods['goods_id'], $val))

            {

                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);

                exit;

            }

        }

    }

}

/**

 * 删除购物车中的商品

 *

 * @access  public

 * @param   integer $id

 * @return  void

 */

function flow_drop_cart_goods($id)

{

    /* 取得商品id */

    $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('cart'). " WHERE rec_id = '$id'";

    $row = $GLOBALS['db']->getRow($sql);

    if ($row)

    {

        //如果是超值礼包

        if ($row['extension_code'] == 'package_buy')

        {

            $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .

                    " WHERE session_id = '" . SESS_ID . "' " .

                    "AND rec_id = '$id' LIMIT 1";

        }

        //如果是普通商品，同时删除所有赠品及其配件

        elseif ($row['parent_id'] == 0 && $row['is_gift'] == 0)

        {

            /* 检查购物车中该普通商品的不可单独销售的配件并删除 */

            $sql = "SELECT c.rec_id

                    FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " . $GLOBALS['hhs']->table('group_goods') . " AS gg, " . $GLOBALS['hhs']->table('goods'). " AS g

                    WHERE gg.parent_id = '" . $row['goods_id'] . "'

                    AND c.goods_id = gg.goods_id

                    AND c.parent_id = '" . $row['goods_id'] . "'

                    AND c.extension_code <> 'package_buy'

                    AND gg.goods_id = g.goods_id

                    AND g.is_alone_sale = 0";

            $res = $GLOBALS['db']->query($sql);

            $_del_str = $id . ',';

            while ($id_alone_sale_goods = $GLOBALS['db']->fetchRow($res))

            {

                $_del_str .= $id_alone_sale_goods['rec_id'] . ',';

            }

            $_del_str = trim($_del_str, ',');

            $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .

                    " WHERE session_id = '" . SESS_ID . "' " .

                    "AND (rec_id IN ($_del_str) OR parent_id = '$row[goods_id]' OR is_gift <> 0)";

        }

        //如果不是普通商品，只删除该商品即可

        else

        {

            $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') .

                    " WHERE session_id = '" . SESS_ID . "' " .

                    "AND rec_id = '$id' LIMIT 1";

        }

        $GLOBALS['db']->query($sql);

    }

    flow_clear_cart_alone();

}

/**

 * 删除购物车中不能单独销售的商品

 *

 * @access  public

 * @return  void

 */

function flow_clear_cart_alone()

{

    /* 查询：购物车中所有不可以单独销售的配件 */

    $sql = "SELECT c.rec_id, gg.parent_id

            FROM " . $GLOBALS['hhs']->table('cart') . " AS c

                LEFT JOIN " . $GLOBALS['hhs']->table('group_goods') . " AS gg ON c.goods_id = gg.goods_id

                LEFT JOIN" . $GLOBALS['hhs']->table('goods') . " AS g ON c.goods_id = g.goods_id

            WHERE c.session_id = '" . SESS_ID . "'

            AND c.extension_code <> 'package_buy'

            AND gg.parent_id > 0

            AND g.is_alone_sale = 0";

    $res = $GLOBALS['db']->query($sql);

    $rec_id = array();

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $rec_id[$row['rec_id']][] = $row['parent_id'];

    }

    if (empty($rec_id))

    {

        return;

    }

    /* 查询：购物车中所有商品 */

    $sql = "SELECT DISTINCT goods_id

            FROM " . $GLOBALS['hhs']->table('cart') . "

            WHERE session_id = '" . SESS_ID . "'

            AND extension_code <> 'package_buy'";

    $res = $GLOBALS['db']->query($sql);

    $cart_good = array();

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $cart_good[] = $row['goods_id'];

    }

    if (empty($cart_good))

    {

        return;

    }

    /* 如果购物车中不可以单独销售配件的基本件不存在则删除该配件 */

    $del_rec_id = '';

    foreach ($rec_id as $key => $value)

    {

        foreach ($value as $v)

        {

            if (in_array($v, $cart_good))

            {

                continue 2;

            }

        }

        $del_rec_id = $key . ',';

    }

    $del_rec_id = trim($del_rec_id, ',');

    if ($del_rec_id == '')

    {

        return;

    }

    /* 删除 */

    $sql = "DELETE FROM " . $GLOBALS['hhs']->table('cart') ."

            WHERE session_id = '" . SESS_ID . "'

            AND rec_id IN ($del_rec_id)";

    $GLOBALS['db']->query($sql);

}

/**

 * 比较优惠活动的函数，用于排序（把可用的排在前面）

 * @param   array   $a      优惠活动a

 * @param   array   $b      优惠活动b

 * @return  int     相等返回0，小于返回-1，大于返回1

 */

function cmp_favourable($a, $b)

{

    if ($a['available'] == $b['available'])

    {

        if ($a['sort_order'] == $b['sort_order'])

        {

            return 0;

        }

        else

        {

            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;

        }

    }

    else

    {

        return $a['available'] ? -1 : 1;

    }

}

/**

 * 取得某用户等级当前时间可以享受的优惠活动

 * @param   int     $user_rank      用户等级id，0表示非会员

 * @return  array

 */

function favourable_list($user_rank)

{

    /* 购物车中已有的优惠活动及数量 */

    $used_list = cart_favourable();

    /* 当前用户可享受的优惠活动 */

    $favourable_list = array();

    $user_rank = ',' . $user_rank . ',';

    $now = gmtime();

    $sql = "SELECT * " .

            "FROM " . $GLOBALS['hhs']->table('favourable_activity') .

            " WHERE CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .

            " AND start_time <= '$now' AND end_time >= '$now'" .

            " AND act_type = '" . FAT_GOODS . "'" .

            " ORDER BY sort_order";

    $res = $GLOBALS['db']->query($sql);

    while ($favourable = $GLOBALS['db']->fetchRow($res))

    {

        $favourable['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['start_time']);

        $favourable['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $favourable['end_time']);

        $favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);

        $favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);

        $favourable['gift']       = unserialize($favourable['gift']);

        foreach ($favourable['gift'] as $key => $value)

        {

            $favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);

            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('goods') . " WHERE is_on_sale = 1 AND goods_id = ".$value['id'];

            $is_sale = $GLOBALS['db']->getOne($sql);

            if(!$is_sale)

            {

                unset($favourable['gift'][$key]);

            }

        }

        $favourable['act_range_desc'] = act_range_desc($favourable);

        $favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);

        /* 是否能享受 */

        $favourable['available'] = favourable_available($favourable);

        if ($favourable['available'])

        {

            /* 是否尚未享受 */

            $favourable['available'] = !favourable_used($favourable, $used_list);

        }

        $favourable_list[] = $favourable;

    }

    return $favourable_list;

}

/**

 * 根据购物车判断是否可以享受某优惠活动

 * @param   array   $favourable     优惠活动信息

 * @return  bool

 */

function favourable_available($favourable)

{

    /* 会员等级是否符合 */

    $user_rank = $_SESSION['user_rank'];

    if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false)

    {

        return false;

    }

    /* 优惠范围内的商品总额 */

    $amount = cart_favourable_amount($favourable);

    /* 金额上限为0表示没有上限 */

    return $amount >= $favourable['min_amount'] &&

        ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);

}

/**

 * 取得优惠范围描述

 * @param   array   $favourable     优惠活动

 * @return  string

 */

function act_range_desc($favourable)

{

    if ($favourable['act_range'] == FAR_BRAND)

    {

        $sql = "SELECT brand_name FROM " . $GLOBALS['hhs']->table('brand') .

                " WHERE brand_id " . db_create_in($favourable['act_range_ext']);

        return join(',', $GLOBALS['db']->getCol($sql));

    }

    elseif ($favourable['act_range'] == FAR_CATEGORY)

    {

        $sql = "SELECT cat_name FROM " . $GLOBALS['hhs']->table('category') .

                " WHERE cat_id " . db_create_in($favourable['act_range_ext']);

        return join(',', $GLOBALS['db']->getCol($sql));

    }

    elseif ($favourable['act_range'] == FAR_GOODS)

    {

        $sql = "SELECT goods_name FROM " . $GLOBALS['hhs']->table('goods') .

                " WHERE goods_id " . db_create_in($favourable['act_range_ext']);

        return join(',', $GLOBALS['db']->getCol($sql));

    }

    else

    {

        return '';

    }

}

/**

 * 取得购物车中已有的优惠活动及数量

 * @return  array

 */

function cart_favourable()

{

    $list = array();

    $sql = "SELECT is_gift, COUNT(*) AS num " .

            "FROM " . $GLOBALS['hhs']->table('cart') .

            " WHERE session_id = '" . SESS_ID . "'" .

            " AND rec_type = '" . CART_GENERAL_GOODS . "'" .

            " AND is_gift > 0" .

            " GROUP BY is_gift";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $list[$row['is_gift']] = $row['num'];

    }

    return $list;

}

/**

 * 购物车中是否已经有某优惠

 * @param   array   $favourable     优惠活动

 * @param   array   $cart_favourable购物车中已有的优惠活动及数量

 */

function favourable_used($favourable, $cart_favourable)

{

    if ($favourable['act_type'] == FAT_GOODS)

    {

        return isset($cart_favourable[$favourable['act_id']]) &&

            $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] &&

            $favourable['act_type_ext'] > 0;

    }

    else

    {

        return isset($cart_favourable[$favourable['act_id']]);

    }

}

/**

 * 添加优惠活动（赠品）到购物车

 * @param   int     $act_id     优惠活动id

 * @param   int     $id         赠品id

 * @param   float   $price      赠品价格

 */

function add_gift_to_cart($act_id, $id, $price)

{

    $sql = "INSERT INTO " . $GLOBALS['hhs']->table('cart') . " (" .

                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".

                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".

            "SELECT '$_SESSION[user_id]', '" . SESS_ID . "', goods_id, goods_sn, goods_name, market_price, ".

                "'$price', 1, is_real, extension_code, 0, '$act_id', '" . CART_GENERAL_GOODS . "' " .

            "FROM " . $GLOBALS['hhs']->table('goods') .

            " WHERE goods_id = '$id'";

    $GLOBALS['db']->query($sql);

}

/**

 * 添加优惠活动（非赠品）到购物车

 * @param   int     $act_id     优惠活动id

 * @param   string  $act_name   优惠活动name

 * @param   float   $amount     优惠金额

 */

function add_favourable_to_cart($act_id, $act_name, $amount)

{

    $sql = "INSERT INTO " . $GLOBALS['hhs']->table('cart') . "(" .

                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".

                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".

            "VALUES('$_SESSION[user_id]', '" . SESS_ID . "', 0, '', '$act_name', 0, ".

                "'" . (-1) * $amount . "', 1, 0, '', 0, '$act_id', '" . CART_GENERAL_GOODS . "')";

    $GLOBALS['db']->query($sql);

}

/**

 * 取得购物车中某优惠活动范围内的总金额

 * @param   array   $favourable     优惠活动

 * @return  float

 */

function cart_favourable_amount($favourable)

{

    /* 查询优惠范围内商品总额的sql */

    $sql = "SELECT SUM(c.goods_price * c.goods_number) " .

            "FROM " . $GLOBALS['hhs']->table('cart') . " AS c, " . $GLOBALS['hhs']->table('goods') . " AS g " .

            "WHERE c.goods_id = g.goods_id " .

            "AND c.session_id = '" . SESS_ID . "' " .

            "AND c.rec_type = '" . CART_GENERAL_GOODS . "' " .

            "AND c.is_gift = 0 " .

            "AND c.goods_id > 0 ";

    /* 根据优惠范围修正sql */

    if ($favourable['act_range'] == FAR_ALL)

    {

        // sql do not change

    }

    elseif ($favourable['act_range'] == FAR_CATEGORY)

    {

        /* 取得优惠范围分类的所有下级分类 */

        $id_list = array();

        $cat_list = explode(',', $favourable['act_range_ext']);

        foreach ($cat_list as $id)

        {

            $id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0, false)));

        }

        $sql .= "AND g.cat_id " . db_create_in($id_list);

    }

    elseif ($favourable['act_range'] == FAR_BRAND)

    {

        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.brand_id " . db_create_in($id_list);

    }

    else

    {

        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.goods_id " . db_create_in($id_list);

    }

    /* 优惠范围内的商品总额 */

    return $GLOBALS['db']->getOne($sql);

}

?>