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

require(ROOT_PATH . 'includes/lib_order.php');

/* 载入语言文件 */

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

/*------------------------------------------------------ */

//-- INPUT

/*------------------------------------------------------ */

if (!isset($_REQUEST['step']))

{

    $_REQUEST['step'] = "cart";

}

/*------------------------------------------------------ */

//-- PROCESSOR

/*------------------------------------------------------ */

// echo $_SESSION['goods_suppliers_id'];

assign_template();

$smarty->assign('page_title', $_LANG['shopping_flow']);    // 页面标题

$smarty->assign('lang',             $_LANG);

$smarty->assign('show_marketprice', $_CFG['show_marketprice']);

$smarty->assign('data_dir',    DATA_DIR);       // 数据目录

/*------------------------------------------------------ */

//-- 添加商品到购物车

/*------------------------------------------------------ */

if ($_REQUEST['step'] == 'add_to_cart')

{

    //清除掉上次的express_id

    unset($_SESSION['express_id']);

    include_once('includes/cls_json.php');

	//echo $_POST['goods'];exit;

    $luckdraw_id = $_POST['luckdraw_id'];

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
    /*判断用户是否登陆*/
    if($_SESSION['user_id'] == 0)
    {
        $result['error']   = 11;
        $result['message'] = '您未登录';
        $result['url']    =  "user.php";
    }else
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

                    $result['rec_type']    =  5;

                    die($json->encode($result));

                }

            }

        }
    }

    //判断是否已经参团

	if($goods->team_sign)

	{

		$where_team = '  oi.team_sign = ' . $goods->team_sign . ' and  oi.pay_status=0 and oi.order_status=0 and oi.user_id='.$_SESSION['user_id'].' ';

		$sql = 'select oi.order_id from ' . $hhs->table('order_info') . ' as oi  where ' . $where_team;

		$is_team_buy = $db->GetOne($sql);

		if($is_team_buy)

		{

			$result['message']  = "您已参团请先支付";

			$result['error']    =  10;

			$result['url']    =  "user.php?act=order_detail&order_id=".$is_team_buy;

			die($json->encode($result));

		}

	}
    
    //判断商品是否已经下架或删除

    $sql="select count(*) from ".$hhs->table('goods')." where goods_id =".$goods->goods_id." and is_delete=0 and is_on_sale=1 ";

    if($db->getOne($sql)==0){

        $result['error']   = 3;

        $result['message'] = $GLOBALS['_LANG']['not_on_sale'];

        $result['url'] = "index.php";

        die($json->encode($result));

    }

    //判断商品是否限购

    $limit_row=$db->getRow("select limit_buy_one,limit_buy_bumber,is_fresh ,promote_end_date,is_luck,is_miao from ".$hhs->table('goods')." where goods_id =".$goods->goods_id." and  is_delete=0  ");

    if($limit_row['limit_buy_one'] ==1)

    {

        $where = '  og.goods_id = ' . $goods->goods_id . ' and  oi.pay_status=2 ';

        $sql = 'select count(oi.order_id) from ' . $hhs->table('order_info') . ' as oi left join ' . $hhs->table('order_goods') . ' as og on oi.order_id = og.order_id where ' . $where . ' and oi.user_id = ' . $_SESSION['user_id'] . ' ';

        $is_buy = $db->GetOne($sql);

        if($is_buy)

        {

            $result['error']   = 5;

            $result['message'] = '该商品限购，一个用户只能购买一次';

            die($json->encode($result));

        }

    }

    if($limit_row['limit_buy_bumber'] && intval($goods->number) > $limit_row['limit_buy_bumber'])

    {

        $result['error']   = 5;

        $result['message'] = '该商品限购，一个用户只能购买'.$limit_row['limit_buy_bumber'];

        die($json->encode($result));

    }

    if($limit_row['is_fresh'] ==1)

    {

        $sql = 'select count(*) from ' . $hhs->table('order_info') . ' where user_id = ' . $_SESSION['user_id'] . ' ';

        $is_buy = $db->GetOne($sql);

        if($is_buy)

        {

            $result['error']   = 5;

            $result['message'] = '该商品仅限新人购买！';

            die($json->encode($result));

        }       

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

    // if($limit_row['is_miao'] && gmtime() > $limit_row['promote_end_date'])

    // {

    //     $result['error']   = 5;

    //     $result['message'] = '该商品活动已经结束了';

    //     die($json->encode($result));

    // }         

    /* 检查：商品数量是否合法 */

    if (!is_numeric($goods->number) || intval($goods->number) <= 0)

    {

        $result['error']   = 1;

        $result['message'] = $_LANG['invalid_number'];

    }

    /* 更新：购物车 */

    else

    {

        if(!empty($goods->spec))

        {

            foreach ($goods->spec as  $key=>$val )

            {

                $goods->spec[$key]=intval($val);

            }

        }

        $_SESSION['flow_type']=$goods->rec_type;

        if($goods->rec_type==5){

            $_SESSION['extension_code']='team_goods';

            $_SESSION['extension_id']=$goods->goods_id;

            $_SESSION['team_sign']=$goods->team_sign;

        }else{

            $_SESSION['extension_code']='';

            $_SESSION['extension_id']=$goods->goods_id;

            $_SESSION['team_sign']='';

        }

        // 更新：添加到购物车

        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent,$goods->rec_type,$goods->team_sign,$luckdraw_id ))

        {

            $rows = $GLOBALS['db']->getRow("select goods_brief,shop_price,goods_name,goods_thumb,suppliers_id from ".$GLOBALS['hhs']->table('goods')." where goods_id=".$goods->goods_id);

            $result['shop_price'] = price_format($rows['shop_price']);

            $result['goods_name'] = $rows['goods_name'];

            $result['goods_thumb'] = $rows['goods_thumb'];

            $result['goods_brief'] = $rows['goods_brief'];

            $result['goods_id'] = $goods->goods_id;

            $sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .

                   ' FROM ' . $GLOBALS['hhs']->table('cart') .

                   " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";

            $rowss = $GLOBALS['db']->GetRow($sql);

            $result['goods_price'] = price_format($rowss['amount']);

            $result['goods_number'] = $rowss['number'];

            //$result['cart_num'] = insert_cart_num();

            $result['content'] = insert_cart_info();

            /**

             * 添加商品suppliers_id到session，方便选择配送地址

             */

            $_SESSION['goods_suppliers_id'] = $rows['suppliers_id'];

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

    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'login')

{

    include_once('languages/'. $_CFG['lang']. '/user.php');

    /*

     * 用户登录注册

     */

    if ($_SERVER['REQUEST_METHOD'] == 'GET')

    {

        $smarty->assign('anonymous_buy', $_CFG['anonymous_buy']);

        /* 检查是否有赠品，如果有提示登录后重新选择赠品 */

        $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') .

                " WHERE session_id = '" . SESS_ID . "' AND is_gift > 0";

        if ($db->getOne($sql) > 0)

        {

            $smarty->assign('need_rechoose_gift', 1);

        }

        /* 检查是否需要注册码 */

        $captcha = intval($_CFG['captcha']);

        if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)

        {

            $smarty->assign('enabled_login_captcha', 1);

            $smarty->assign('rand', mt_rand());

        }

        if ($captcha & CAPTCHA_REGISTER)

        {

            $smarty->assign('enabled_register_captcha', 1);

            $smarty->assign('rand', mt_rand());

        }

    }

    else

    {

        include_once('includes/lib_passport.php');

        if (!empty($_POST['act']) && $_POST['act'] == 'signin')

        {

            $captcha = intval($_CFG['captcha']);

            if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)

            {

                if (empty($_POST['captcha']))

                {

                    show_message($_LANG['invalid_captcha']);

                }

                /* 检查验证码 */

                include_once('includes/cls_captcha.php');

                $validator = new captcha();

                $validator->session_word = 'captcha_login';

                if (!$validator->check_word($_POST['captcha']))

                {

                    show_message($_LANG['invalid_captcha']);

                }

            }

            $_POST['password']=isset($_POST['password']) ? trim($_POST['password']) : '';

            if ($user->login($_POST['username'], $_POST['password'],isset($_POST['remember'])))

            {

                update_user_info();  //更新用户信息

                recalculate_price(); // 重新计算购物车中的商品价格

                /* 检查购物车中是否有商品 没有商品则跳转到首页 */

                $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') . " WHERE session_id = '" . SESS_ID . "' ";

                if ($db->getOne($sql) > 0)

                {

                    hhs_header("Location: flow.php?step=checkout\n");

                }

                else

                {

                    hhs_header("Location:index.php\n");

                }

                exit;

            }

            else

            {

                $_SESSION['login_fail']++;

                show_message($_LANG['signin_failed'], '', 'flow.php?step=login');

            }

        }

        elseif (!empty($_POST['act']) && $_POST['act'] == 'signup')

        {

            if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)

            {

                if (empty($_POST['captcha']))

                {

                    show_message($_LANG['invalid_captcha']);

                }

                /* 检查验证码 */

                include_once('includes/cls_captcha.php');

                $validator = new captcha();

                if (!$validator->check_word($_POST['captcha']))

                {

                    show_message($_LANG['invalid_captcha']);

                }

            }

            if (register(trim($_POST['username']), trim($_POST['password']), trim($_POST['email'])))

            {

                /* 用户注册成功 */

                hhs_header("Location: flow.php?step=consignee\n");

                exit;

            }

            else

            {

                $err->show();

            }

        }

        else

        {

            // TODO: 非法访问的处理

        }

    }

}

elseif($_REQUEST['step']=='get_address')//西安php获取地址信息并改变当前用户的默认地址

{

    /*------------------------------------------------------ */

    //-- 改变配送方式

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    include_once('includes/lib_transaction.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0,'shipping_method'=>"");

    $address_id = $_REQUEST['id'];

    if(empty($_SESSION['user_id']))

    {

        $result['error'] = 1;

        echo $json->encode($result);

        exit;

    }

    if($address_id ==0)

    {

        if(count(get_consignee_list($_SESSION['user_id']))>4)

        {

            $result['error'] = 2;

            $result['s_address_id'] = get_user_address_id($_SESSION['user_id']);

            echo $json->encode($result);

            exit;

        }

    }

    $sql =  $GLOBALS['db']->query("update ". $GLOBALS['hhs']->table('users')." set address_id='$address_id' where user_id=".$_SESSION['user_id']);//改变当前用户的地址信息

    $address_row = get_user_address($address_id);

    $city_list = get_regions(2,$address_row['province']);

    $district_list = get_regions(3,$address_row['city']);

    $city_area = get_regions(4,$address_row['district']);

    $smarty->assign('address_row',$address_row);

    $smarty->assign('city_list',$city_list);

    $smarty->assign('district_list',$district_list);

    $smarty->assign('city_area',$city_area);

    $province_list = get_regions(1, 1);

    $smarty->assign('province_list',$province_list);

    //改变session地址信息

    $_SESSION['flow_consignee'] = stripslashes_deep($address_row);

    $result['content'] = $smarty->fetch('library/address.lbi');

    /*改变配送方式*/

    include_once('includes/lib_transaction.php');

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $consignee = get_consignee($_SESSION['user_id']);

    $_SESSION['flow_consignee'] = $consignee;

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    /*

     * 取得订单信息

     */

    $order = flow_order_info();

    $smarty->assign('order', $order);

    /*

     * 计算订单的费用

     */

    $total = order_fee($order, $cart_goods, $consignee);

    $smarty->assign('total', $total);

    // 0元购

    if($cart_goods[0]['is_zero'])

    {

        $smarty->assign('is_zero', 1);

    }

    else

    {

        $smarty->assign('is_zero', 0);

    }

    /* 取得配送列表 */

    // $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

    // $shipping_list     = available_shipping_list($region);

    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

    $shipping_list     = available_shipping_list($region);

    //没有收货地址

    if(empty($shipping_list))

    {

        $ip = real_ip();

        $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;

        $sina = file_get_contents($url);

        $sina = json_decode($sina);

        $sql = "SELECT `region_id` FROM ".$hhs->table('region')." WHERE `region_name` LIKE '%".$sina->country."%' OR `region_name` LIKE '%".$sina->province."%' OR `region_name` LIKE '%".$sina->city."%'";

        $rows = $db->getAll($sql);

        foreach ($rows as $key => $row) {

            $region[] = $row['region_id'];

        }

        $shipping_list     = available_shipping_list($region);

    }

    $point_list        = available_shipping_point_list($region);

    $cart_weight_price = cart_weight_price($flow_type);

    $insure_disabled   = true;

    $cod_disabled      = true;

    // 查看购物车中是否全为免运费商品，若是则把运费赋为零

    $sql = 'SELECT count(*) FROM ' . $hhs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";

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

    }

    $smarty->assign('shipping_list',   $shipping_list);

    $smarty->assign('point_list',   $point_list);

    $smarty->assign('insure_disabled', $insure_disabled);

    $smarty->assign('cod_disabled',    $cod_disabled);

    $result['shipping_method'] = $smarty->fetch('library/shipping_method.lbi');

    echo $json->encode($result);

    exit;

}

elseif($_REQUEST['step'] =='save_address')

{

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '','shipping_method'=>"");

    $address_list = $json->decode($_GET['address_list']);

   $consignee = array(

            'address_id'    =>$address_list->address_id,

            'consignee'     =>$address_list->consignee,

            'country'       =>1,

            'province'      =>$address_list->province,

            'city'          =>$address_list->city,

            'district'      =>$address_list->district,

             'area'      =>$address_list->area,

            'email'         =>$address_list->email,

            'address'       =>$address_list->address,

            'zipcode'       =>$address_list->zipcode,

            'tel'           =>$address_list->tel,

            'mobile'        =>$address_list->mobile,

            'best_time'     =>$address_list->best_time

        );

        if ($_SESSION['user_id'] > 0)

        {

            include_once(ROOT_PATH . 'includes/lib_transaction.php');

            /* 如果用户已经登录，则保存收货人信息 */

            $consignee['user_id'] = $_SESSION['user_id'];

            $save = save_consignee($consignee, true);

            if($save)

            {

                /* 获得用户所有的收货人信息 */

                $consignee_list = get_consignee_list($_SESSION['user_id']);

                foreach($consignee_list as $idx=>$value)

                {

                    $consignee_list[$idx]['province_name'] = get_regions_name($value['province']);

                    $consignee_list[$idx]['city_name'] = get_regions_name($value['city']);

                    $consignee_list[$idx]['district_name'] = get_regions_name($value['district']);

                    $consignee_list[$idx]['area_name'] = get_regions_name($value['area']);

                }

                $smarty->assign('consignee_list', $consignee_list);

                $result['content'] = $smarty->fetch('library/address_list.lbi');

            }

            $smarty->assign('s_address_id', get_user_address_id($_SESSION['user_id']));

            $result['error']= 1;

            $result['s_address_id'] = get_user_address_id($_SESSION['user_id']);

            $consignee['address_id'] = get_user_address_id($_SESSION['user_id']);

            $_SESSION['flow_consignee'] = $consignee;

            /*改变配送方式 addhuzhangfei*/

            include_once('includes/lib_transaction.php');

            /* 取得购物类型 */

            $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

            $consignee = get_consignee($_SESSION['user_id']);

            $_SESSION['flow_consignee'] = $consignee;

            /* 对商品信息赋值 */

            $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

            /*

             * 取得订单信息

             */

            $order = flow_order_info();

            $smarty->assign('order', $order);

            /*

             * 计算订单的费用

             */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total', $total);

            /* 取得配送列表 */

            $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

            $shipping_list     = available_shipping_list($region);

            $cart_weight_price = cart_weight_price($flow_type);

            $insure_disabled   = true;

            $cod_disabled      = true;

            // 查看购物车中是否全为免运费商品，若是则把运费赋为零

            $sql = 'SELECT count(*) FROM ' . $hhs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";

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

            }

            $smarty->assign('shipping_list',   $shipping_list);

            $smarty->assign('insure_disabled', $insure_disabled);

            $smarty->assign('cod_disabled',    $cod_disabled);

            $result['shipping_method'] = $smarty->fetch('library/shipping_method.lbi');

            echo $json->encode($result);

            exit;

        }

        else

        {

            $result['error']= 0;    

            echo $json->encode($result);

            exit;

        }

        /* 保存到session */

        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);

}

elseif ($_REQUEST['step'] == 'consignee')

{

    /*------------------------------------------------------ */

    //-- 收货人信息

    /*------------------------------------------------------ */

    include_once('includes/lib_transaction.php');

    if ($_SERVER['REQUEST_METHOD'] == 'GET')

    {

        /* 取得购物类型 */

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /*

         * 收货人信息填写界面

         */

        if (isset($_REQUEST['direct_shopping']))

        {

            $_SESSION['direct_shopping'] = 1;

        }

        /* 取得国家列表、商店所在国家、商店所在国家的省列表 */

        $smarty->assign('country_list',       get_regions());

        $smarty->assign('shop_country',       $_CFG['shop_country']);

        $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

        /* 获得用户所有的收货人信息 */

        if ($_SESSION['user_id'] > 0)

        {

            $consignee_list = get_consignee_list($_SESSION['user_id']);

            if (count($consignee_list) < 5)

            {

                /* 如果用户收货人信息的总数小于 5 则增加一个新的收货人信息 */

                $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');

            }

        }

        else

        {

            if (isset($_SESSION['flow_consignee'])){

                $consignee_list = array($_SESSION['flow_consignee']);

            }

            else

            {

                $consignee_list[] = array('country' => $_CFG['shop_country']);

            }

        }

        $smarty->assign('name_of_region',   array($_CFG['name_of_region_0'],$_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

        $smarty->assign('consignee_list', $consignee_list);

        /* 取得每个收货地址的省市区列表 */

        $province_list = array();

        $city_list = array();

        $district_list = array();

        foreach ($consignee_list as $region_id => $consignee)

        {

            $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;

            $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;

            $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

            $province_list[$region_id] = get_regions(1, $consignee['country']);

            $city_list[$region_id]     = get_regions(2, $consignee['province']);

            $district_list[$region_id] = get_regions(3, $consignee['city']);

        }

        $smarty->assign('province_list', $province_list);

        $smarty->assign('city_list',     $city_list);

        $smarty->assign('district_list', $district_list);

        /* 返回收货人页面代码 */

        $smarty->assign('real_goods_count', exist_real_goods(0, $flow_type) ? 1 : 0);

    }

    else

    {

        /*

         * 保存收货人信息

         */

        $consignee = array(

            'address_id'    => empty($_POST['address_id']) ? 0  :   intval($_POST['address_id']),

            'consignee'     => empty($_POST['consignee'])  ? '' :   compile_str(trim($_POST['consignee'])),

            'country'       => empty($_POST['country'])    ? '' :   intval($_POST['country']),

            'province'      => empty($_POST['province'])   ? '' :   intval($_POST['province']),

            'city'          => empty($_POST['city'])       ? '' :   intval($_POST['city']),

            'district'      => empty($_POST['district'])   ? '' :   intval($_POST['district']),

            'email'         => empty($_POST['email'])      ? '' :   compile_str($_POST['email']),

            'address'       => empty($_POST['address'])    ? '' :   compile_str($_POST['address']),

            'zipcode'       => empty($_POST['zipcode'])    ? '' :   compile_str(make_semiangle(trim($_POST['zipcode']))),

            'tel'           => empty($_POST['tel'])        ? '' :   compile_str(make_semiangle(trim($_POST['tel']))),

            'mobile'        => empty($_POST['mobile'])     ? '' :   compile_str(make_semiangle(trim($_POST['mobile']))),

            'sign_building' => empty($_POST['sign_building']) ? '' :compile_str($_POST['sign_building']),

            'best_time'     => empty($_POST['best_time'])  ? '' :   compile_str($_POST['best_time']),

        );

        if ($_SESSION['user_id'] > 0)

        {

            include_once(ROOT_PATH . 'includes/lib_transaction.php');

            /* 如果用户已经登录，则保存收货人信息 */

            $consignee['user_id'] = $_SESSION['user_id'];

            save_consignee($consignee, true);

        }

        /* 保存到session */

        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);

        hhs_header("Location: flow.php?step=checkout\n");

        exit;

    }

}

elseif ($_REQUEST['act'] == 'drop_consignee')

{

    /*------------------------------------------------------ */

    //-- 删除收货人信息

    /*------------------------------------------------------ */

    /*

    include_once('includes/cls_json.php');

    include_once('includes/lib_transaction.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    $address_id = $_REQUEST['id'];

    if(empty($_SESSION['user_id']))

    {

        $result['error'] = 1;

        echo $json->encode($result);

        exit;

    }

    if (drop_consignee($address_id))

    {

            //获得用户所有的收货人信息

            $consignee_list = get_consignee_list($_SESSION['user_id']);

            foreach($consignee_list as $idx=>$value)

            {

                $consignee_list[$idx]['province_name'] = get_regions_name($value['province']);

                $consignee_list[$idx]['city_name'] = get_regions_name($value['city']);

                $consignee_list[$idx]['district_name'] = get_regions_name($value['district']);

            }

            $smarty->assign('consignee_list', $consignee_list);

            $result['error'] = 0;

            $result['address_count'] =count($consignee_list);

            $result['content'] = $smarty->fetch('library/address_list.lbi');

            $result['s_address_id'] = get_user_address_id($_SESSION['user_id']);

            echo $json->encode($result);

            exit;

    }

    else

    {

        $result['error'] = 2;

        echo $json->encode($result);

        exit;

    }*/

    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))

    {

        hhs_header("Location: flow.php?step=address_list\n");

        exit;

    }

    else

    {

        //show_message($_LANG['del_address_false']);

    }

}

elseif ($_REQUEST['step'] == 'address_list')

{

    /*------------------------------------------------------ */

    //-- 收货人信息

    /*------------------------------------------------------ */

    include_once('includes/lib_transaction.php');

	$luckdraw_id = $_REQUEST['luckdraw_id'];

    $smarty->assign('default_address_id',  get_user_address_id($_SESSION['user_id']) );

    //echo get_user_address_id($_SESSION['user_id']);exit();

    $consignee_list = get_consignee_list($_SESSION['user_id']);

    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

$smarty->assign('luckdraw_id',$luckdraw_id);

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

    $forward="flow.php?step=address_list";

    $smarty->assign('forward', $forward);

}

/* 设置默认地址 */

elseif ($_REQUEST['step'] == 'set_address')

{

	    $user_id = $_SESSION['user_id'];

        $address_id = empty($_REQUEST['id'])?0:intval($_REQUEST['id']);

        if($db->query("UPDATE " . $hhs->table('users') . " SET address_id = $address_id  WHERE user_id='$user_id'")){ 

			hhs_header("Location: flow.php?step=address_list\n");

        }

}

/* 删除收货地址 */

elseif ($_REQUEST['step'] == 'drop_consignee')

{

    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))

    {

        hhs_header("Location: flow.php?step=address_list\n");

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

	$luckdraw_id = $_REQUEST['luckdraw_id'];

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

    $smarty->assign('back_url',   "flow.php" );

    $smarty->assign('luckdraw_id',   $luckdraw_id );

    $smarty->display('edit_consignee.dwt');

    exit();

}

elseif ($_REQUEST['act'] == 'act_edit_consignee')

{

	$luckdraw_id = $_REQUEST['luckdraw_id'];

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

    if ($address_id=update_address($address))

    {   

        //hhs_header('location:flow.php?step=address_list');

		if($luckdraw_id){

			hhs_header('location:flow.php?step=checkout&address_id='.$address_id.'&luckdraw_id='.$luckdraw_id);

		}else{

			hhs_header('location:flow.php?step=checkout&address_id='.$address_id);

		}

        //show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');

    }

}

elseif ($_REQUEST['step'] == 'shipping_list')

{

    $consignee=$_SESSION['flow_consignee'] ;

    if(empty($consignee)){

        echo"<script>";

        echo"alert('请选择配送地址');";

        echo"window.location='flow.php?step=address_list';";

        echo"</script>";

        exit();

    }

    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

    $shipping_list     = available_shipping_list($region);

    $smarty->assign('shipping_list',$shipping_list);

    $smarty->assign('shipping_id',$_REQUEST['shipping_id']);

}

elseif ($_REQUEST['step'] == 'point_list')

{

    $sql="select shipping_id from ".$hhs->table('shipping')." where shipping_code='cac' ";

    $shipping_id=$db->getOne($sql);

    $point_list=get_shipping_point_list();

    $smarty->assign('point_list', $point_list);

    $smarty->assign('point_id',$_REQUEST['point_id']);

    $smarty->assign('shipping_id',$shipping_id);

}

elseif ($_REQUEST['step'] == 'checkout')

{

	$luckdraw_id = $_REQUEST['luckdraw_id'];//抽奖活动id

    /*------------------------------------------------------ */

    //-- 订单确认

    /*------------------------------------------------------ */

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $today = date("w");

    $date_open = in_array($today, array(0,1,2,3)) ? 0 :1;

    $smarty->assign("date_open", $date_open );

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

    /*团购商品*/

    elseif($flow_type == 5){

        $smarty->assign('is_team_goods', 1);

    }    

    else

    {

        //正常购物流程  清空其他购物流程情况

        $_SESSION['flow_order']['extension_code'] = '';

    }

    /* 检查购物车中是否有商品 */

    $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') .

        " WHERE session_id = '" . SESS_ID . "' " .

        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";

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

    if ( !check_consignee_info($consignee, $flow_type))

    {

        /* 如果不完整则转向到收货人信息填写界面 */

		if($luckdraw_id){

			hhs_header("Location: /flow.php?step=edit_consignee&back_url=flow.php&luckdraw_id=".$luckdraw_id."\n");

		}else{

			hhs_header("Location: /flow.php?step=edit_consignee&back_url=flow.php\n");

		}

        exit;

    }

    $_SESSION['flow_consignee'] = $consignee;

    $smarty->assign('consignee', $consignee);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    $smarty->assign('goods_list', $cart_goods);
    if(empty($luckdraw_id)){
        $todayDate = gmtime();
        $luckdraw_goods = $GLOBALS['db']->getOne("SELECT goods_id FROM " . $GLOBALS['hhs']->table('luckdraw') . " WHERE start_time < ".$todayDate." and end_time >".$todayDate." and luck_status = 0 and goods_id = ".$cart_goods[0]['goods_id']);
        if($luckdraw_goods > 0)
        {
            show_message("该商品现已参加抽奖活动，请前往抽奖活动页面", "抽奖活动", "luckdraw.php");
        }
    }
    
    // teammem +-按钮

    if($_SESSION['extension_code']=='team_goods' && !empty($_SESSION['team_sign']) ){

        //通过团购分享购物显示更改数量的按钮

        $smarty->assign("teammem", 1  );

    }

    else{

        $smarty->assign("teammem", 0  );

    }

    // 夺宝商品直接为1，显示

    if($_SESSION['is_luck']){

        $smarty->assign("teammem", 1  );

    }

    //end

    /* 对是否允许修改购物车赋值 */

    // if ($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1')

    // {

    //     $smarty->assign('allow_edit_cart', 0);

    // }

    // else

    // {

    //     $smarty->assign('allow_edit_cart', 1);

    // }

    /*

     * 取得购物流程设置

     */

    $smarty->assign('config', $_CFG);

    /*

     * 取得订单信息

     */

    $order = flow_order_info();

    $smarty->assign('order', $order);

    /*

     * 计算订单的费用

     */

    $total = order_fee($order, $cart_goods, $consignee);

    $smarty->assign('total', $total);

    $smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));

    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

    /* 取得配送列表 */

    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

    $shipping_list     = available_shipping_list($region);

	

	

	

	

	

	

	

    $cart_weight_price = cart_weight_price($flow_type);

    $insure_disabled   = true;

    $cod_disabled      = true;

    // 查看购物车中是否全为免运费商品，若是则把运费赋为零

    $sql = 'SELECT count(*) FROM ' . $hhs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";

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

    }

    // 商品邮费模板

    $goods_id = $cart_goods[0]['goods_id'];

    $goods_express = $db->getAll('select * from '.$hhs->table('goods_express').' WHERE goods_id = ' . $goods_id .' AND region_id ' . db_create_in($region) .        ' group by shipping_id');

	

	

	

    /*pangbin 获取用户默认自提点 start*/

	$u_info = get_u_info($_SESSION['user_id']);

	$smarty->assign('u_point',         $u_info['u_point']);

	$smarty->assign('u_mobile',         $u_info['u_mobile']);

	/*pangbin 获取用户默认自提点 end*/

    if(!empty($goods_express)){

        $has_cac = false;

        $shipping_list = $goods_express;

        foreach ($shipping_list as $key => $item) {

            if($item['shipping_code'] == 'cac')

            {

                $has_cac = true;

                break;

            }

        }

        if(! $has_cac){

            $point_list = array();

        }

    }

	

	

    $smarty->assign('shipping_list',   $shipping_list);

    $smarty->assign('insure_disabled', $insure_disabled);

    $smarty->assign('cod_disabled',    $cod_disabled);

    $point_list        = available_shipping_point_list($region);

    $smarty->assign('point_list',   $point_list);

    /* 取得支付列表 */

    if ($order['shipping_id'] == 0)

    {

        $cod        = true;

        $cod_fee    = 0;

    }

    else

    {

        $shipping = shipping_info($order['shipping_id']);

        $cod = $shipping['support_cod'];

        if ($cod)

        {

            /* 如果是团购，且保证金大于0，不能使用货到付款 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $group_buy_id = $_SESSION['extension_id'];

                if ($group_buy_id <= 0)

                {

                    show_message('error group_buy_id');

                }

                $group_buy = group_buy_info($group_buy_id);

                if (empty($group_buy))

                {

                    show_message('group buy not exists: ' . $group_buy_id);

                }

                if ($group_buy['deposit'] > 0)

                {

                    $cod = false;

                    $cod_fee = 0;

                    /* 赋值保证金 */

                    $smarty->assign('gb_deposit', $group_buy['deposit']);

                }

            }

            if ($cod)

            {

                $shipping_area_info = shipping_area_info($order['shipping_id'], $region);

                $cod_fee            = $shipping_area_info['pay_fee'];

            }

        }

        else

        {

            $cod_fee = 0;

        }

    }

    // 给货到付款的手续费加<span id>，以便改变配送的时候动态显示

    $luckdraw = $GLOBALS['db']->getRow("select * from ".$GLOBALS['hhs']->table('luckdraw')." where luck_status = 0 AND goods_id=".$cart_goods[0][goods_id]);

    $payment_list = available_payment_list(1, $cod_fee);
    if(isset($payment_list))

    {

        foreach ($payment_list as $key => $payment)

        {

            if ($payment['is_cod'] == '1')

            {

                $payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';

            }

            /* 如果抽奖则不显示 */

            if ($payment['pay_code'] == 'alipay' && $luckdraw > 0)

            {

                unset($payment_list[$key]);

            }

            /* 如果有余额支付 */

            if ($payment['pay_code'] == 'balance')

            {

                /* 如果未登录或抽奖，不显示 */

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

    $smarty->assign('luckdraw', $luckdraw);

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

    /*if($_REQUEST['as']=='change'){

        if()

    }*/

    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')

        && $_SESSION['user_id'] > 0

        && $user_info['pay_points'] > 0

        && ($flow_type != CART_GROUP_BUY_GOODS ))

    {

        // 能使用积分

        $smarty->assign('allow_use_integral', 1);

        $smarty->assign('order_max_integral', flow_available_points());  // 可用积分

        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分

    }  

    /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */

    $goods_id = $cart_goods[0]['goods_id'];

    $sql = "SELECT bonus_allowed,bonus_free_all FROM ".$hhs->table("goods")." WHERE goods_id = " . $goods_id;

    $bonus_meta = $db->getRow($sql);

    $bonus_allowed = $bonus_meta['bonus_allowed'];

    $bonus_free_all = $bonus_meta['bonus_free_all'];

    // 取得用户可用优惠劵

    if(empty($_SESSION['team_sign'])){

        $team_first=true;

    }else{

        $team_first=false;

    }

    if ( $cart_goods[0]['bonus_allowed']

        && (!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1')

        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))

    {

        // 取得用户可用红包

        // $user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);

        $suppliers_id = $cart_goods[0]['suppliers_id'] ? $cart_goods[0]['suppliers_id'] :0;

        $user_bonus = team_user_bonus($_SESSION['user_id'], $total['goods_price'],$suppliers_id);

    }

    // 免单券

    if($bonus_free_all && $team_first)

    {

        $suppliers_id = $cart_goods[0]['suppliers_id'] ? $cart_goods[0]['suppliers_id'] :0;

        $user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price'],true, $bonus_free_all ,1,$suppliers_id);

    }

    else{

        foreach ($user_bonus as $key => $bonus) {

            if($bonus['free_all'] ==1)

                unset($user_bonus[$key]);

        }

    }

    if (!empty($user_bonus))

    {

        $muse_end_time=$user_bonus[0]['use_end_date'];

        $mbonus_id=$user_bonus[0]['bonus_id'];

        foreach ($user_bonus AS $key => $val)

        {

            if($muse_end_time<$val['use_end_date']){

                $muse_end_time=$val['use_end_date'];

                $mbonus_id=$val['bonus_id'];

            }

            $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);

            $user_bonus[$key]['use_startdate']   = local_date($GLOBALS['_CFG']['date_format'], $val['use_start_date']);

            $user_bonus[$key]['use_enddate']     = local_date($GLOBALS['_CFG']['date_format'], $val['use_end_date']);

			$user_bonus[$key]['suppliers_name'] = $user_bonus[$key]['suppliers_id']?get_suppliers_name($user_bonus[$key]['suppliers_id']):'自营店';

        }

        $smarty->assign('mbonus_id', $mbonus_id);

        $smarty->assign('bonus_list', $user_bonus);

    }    

    // 能使用优惠劵

    $smarty->assign('allow_use_bonus', $bonus_allowed);

    /* 如果使用缺货处理，取得缺货处理列表 */

    // if (!isset($_CFG['use_how_oos']) || $_CFG['use_how_oos'] == '1')

    // {

    //     if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos']))

    //     {

    //         $smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);

    //     }

    // }

    // 用户GEO信息

    if (empty($_SESSION['user_loc'])) {

        $sql="select lat,lng from ".$hhs->table("users")." where user_id='".$_SESSION['user_id']."' ";

        $_SESSION['user_loc'] = $db->getRow($sql);

    }

    $smarty->assign('lat', $_SESSION['user_loc']['lat']);

    $smarty->assign('lng', $_SESSION['user_loc']['lng']);

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

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $order['shipping_id'] = intval($_REQUEST['shipping']);

        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);

        $shipping_info = shipping_area_info($order['shipping_id'], $regions);

        //express 

        $express_id = isset($_REQUEST['express_id']) ? intval($_REQUEST['express_id']) : 0;

        $_SESSION['express_id'] = $express_id;

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和优惠劵 */

        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);

        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */

        if ($flow_type == CART_GROUP_BUY_GOODS)

        {

            $smarty->assign('is_group_buy', 1);

        }

        $result['cod_fee']     = $shipping_info['pay_fee'];

        if (strpos($result['cod_fee'], '%') === false)

        {

            $result['cod_fee'] = price_format($result['cod_fee'], false);

        }

        $result['need_insure'] = ($shipping_info['insure'] > 0 && !empty($order['need_insure'])) ? 1 : 0;

        $result['content']     = $smarty->fetch('library/order_total.lbi');

    }

    echo $json->encode($result);

    exit;

}

elseif ($_REQUEST['step'] == 'select_insure')

{

    /*------------------------------------------------------ */

    //-- 选定/取消配送的保价

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $order['need_insure'] = intval($_REQUEST['insure']);

        /* 保存 session */

        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和优惠劵 */

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

    if (empty($cart_goods) )

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

        /* 取得可以得到的积分和优惠劵 */

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

elseif ($_REQUEST['step'] == 'select_pack')

{

    /*------------------------------------------------------ */

    //-- 改变商品包装

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $order['pack_id'] = intval($_REQUEST['pack']);

        /* 保存 session */

        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和优惠劵 */

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

elseif ($_REQUEST['step'] == 'select_card')

{

    /*------------------------------------------------------ */

    //-- 改变贺卡

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $json = new JSON;

    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $order['card_id'] = intval($_REQUEST['card']);

        /* 保存 session */

        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 取得可以得到的积分和优惠劵 */

        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);

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

        if (empty($cart_goods))

        {

            $result['error'] = $_LANG['no_goods_in_cart'];

        }

        else

        {

            /* 取得订单信息 */

            $order = flow_order_info();

            $order['surplus'] = $surplus;

            /* 计算订单的费用 */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total', $total);

            /* 团购标志 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $smarty->assign('is_group_buy', 1);

            }

            $result['content'] = $smarty->fetch('library/order_total.lbi');

        }

    }

    $json = new JSON();

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'change_integral')

{

    /*------------------------------------------------------ */

    //-- 改变积分

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $points    = floatval($_GET['points']);

    $user_info = user_info($_SESSION['user_id']);

    /* 取得订单信息 */

    $order = flow_order_info();

    $flow_points = flow_available_points();  // 该订单允许使用的积分

    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)

    {

        $result['error'] = $_LANG['integral_not_enough'];

    }

    elseif ($points > $flow_points)

    {

        $result['error'] = sprintf($_LANG['integral_too_much'], $flow_points);

    }

    else

    {

        /* 取得购物类型 */

        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        $order['integral'] = $points;

        /* 获得收货人信息 */

        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */

        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

        if (empty($cart_goods))

        {

            $result['error'] = $_LANG['no_goods_in_cart'];

        }

        else

        {

            /* 计算订单的费用 */

            $total = order_fee($order, $cart_goods, $consignee);

            $smarty->assign('total',  $total);

            $smarty->assign('config', $_CFG);

            /* 团购标志 */

            if ($flow_type == CART_GROUP_BUY_GOODS)

            {

                $smarty->assign('is_group_buy', 1);

            }

            $result['content'] = $smarty->fetch('library/order_total.lbi');

            $result['error'] = '';

        }

    }

    $json = new JSON();

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'change_bonus')

{

    /*------------------------------------------------------ */

    //-- 改变优惠劵

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods) )

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)

        {

            if(($bonus['suppliers_id'] == $cart_goods[0]['suppliers_id'])or ($bonus['tongyong'])==1 )

            {

                $order['bonus_id'] = intval($_GET['bonus']);

            }

            else{

                $order['bonus_id'] = 0;

                if(intval($_GET['bonus'])>0)

                    $result['error'] = '该优惠券仅限发券商家使用';

            }

        }

        else

        {

            $order['bonus_id'] = 0;

            $result['error'] = $_LANG['invalid_bonus'];

        }

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 团购标志 */

        if ($flow_type == CART_GROUP_BUY_GOODS)

        {

            $smarty->assign('is_group_buy', 1);

        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');

    }

    $json = new JSON();

    die($json->encode($result));

}

// elseif ($_REQUEST['step'] == 'change_bonus')

// {

//     /*------------------------------------------------------ */

//     //-- 改变优惠劵

//     /*------------------------------------------------------ */

//     include_once('includes/cls_json.php');

//     $result = array('error' => '', 'content' => '');

//     /* 取得购物类型 */

//     $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

//     /* 获得收货人信息 */

//     $consignee = get_consignee($_SESSION['user_id']);

//     /* 对商品信息赋值 */

//     $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

//     if (empty($cart_goods) )

//     {

//         $result['error'] = $_LANG['no_goods_in_cart'];

//     }

//     else

//     {

//         /* 取得购物流程设置 */

//         $smarty->assign('config', $_CFG);

//         /* 取得订单信息 */

//         $order = flow_order_info();

//         $bonus = bonus_info(intval($_GET['bonus']));

//         if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)

//         {

//             $order['bonus_id'] = intval($_GET['bonus']);

//         }

//         else

//         {

//             $order['bonus_id'] = 0;

//             $result['error'] = $_LANG['invalid_bonus'];

//         }

//         /* 计算订单的费用 */

//         $total = order_fee($order, $cart_goods, $consignee);

//         $smarty->assign('total', $total);

//         /* 团购标志 */

//         if ($flow_type == CART_GROUP_BUY_GOODS)

//         {

//             $smarty->assign('is_group_buy', 1);

//         }

//         $result['content'] = $smarty->fetch('library/order_total.lbi');

//     }

//     $json = new JSON();

//     die($json->encode($result));

// }

elseif ($_REQUEST['step'] == 'change_needinv')

{

    /*------------------------------------------------------ */

    //-- 改变发票的设置

    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');

    $json = new JSON();

    $_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';

    $_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';

    $_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

        die($json->encode($result));

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1)

        {

            $order['need_inv']    = 1;

            $order['inv_type']    = trim(stripslashes($_GET['inv_type']));

            $order['inv_payee']   = trim(stripslashes($_GET['inv_payee']));

            $order['inv_content'] = trim(stripslashes($_GET['inv_content']));

        }

        else

        {

            $order['need_inv']    = 0;

            $order['inv_type']    = '';

            $order['inv_payee']   = '';

            $order['inv_content'] = '';

        }

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 团购标志 */

        if ($flow_type == CART_GROUP_BUY_GOODS)

        {

            $smarty->assign('is_group_buy', 1);

        }

        die($smarty->fetch('library/order_total.lbi'));

    }

}

elseif ($_REQUEST['step'] == 'change_oos')

{

    /*------------------------------------------------------ */

    //-- 改变缺货处理时的方式

    /*------------------------------------------------------ */

    /* 取得订单信息 */

    $order = flow_order_info();

    $order['how_oos'] = intval($_GET['oos']);

    /* 保存 session */

    $_SESSION['flow_order'] = $order;

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

elseif ($_REQUEST['step'] == 'check_integral')

{

    /*------------------------------------------------------ */

    //-- 检查用户输入的余额

    /*------------------------------------------------------ */

    $points      = floatval($_GET['integral']);

    $user_info   = user_info($_SESSION['user_id']);

    $flow_points = flow_available_points();  // 该订单允许使用的积分

    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)

    {

        die($_LANG['integral_not_enough']);

    }

    if ($points > $flow_points)

    {

        die(sprintf($_LANG['integral_too_much'], $flow_points));

    }

    exit;

}

/*------------------------------------------------------ */

//-- 完成所有订单操作，提交到数据库

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'json_done')

{

    include_once('includes/lib_clips.php');

    include_once('includes/lib_payment.php');

    include_once('includes/cls_json.php');

    // include_once('includes/lib_fenxiao.php');

    $json = new JSON();

    $result = array('error' => 0,'message'=>'', 'content' => '');

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    $luckdraw_id = $_REQUEST['luckdraw_id'];

    /* 检查购物车中是否有商品 */

    $sql = "SELECT COUNT(*) FROM " . $hhs->table('cart') .

    " WHERE session_id = '" . SESS_ID . "' " .

    "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type'";

    if ($db->getOne($sql) == 0)

    {

        $result['error']=1;

        $result['message']=$_LANG['no_goods_in_cart'];

        $result['url']="index.php";

        die($json->encode($result));

        //show_message($_LANG['no_goods_in_cart'], '', '', 'warning');

    }

    //检查是否已满团购-》支付回调再判断一次

    if ( $_SESSION['extension_code']=='team_goods' && !empty($_SESSION['team_sign']) )

    { 

        //判断是否是自己的

        $sql="select count(*) from ".$hhs->table('order_info') ." where team_sign=".$_SESSION['team_sign']." and  pay_status > 1 and user_id=".$_SESSION['user_id'];

        $temp=$db->getOne($sql);

        if($temp>0){

            $result['error']=1;

			if($luckdraw_id){

				$result['url']="share.php?team_sign=".$_SESSION['team_sign']."&uid=".$uid."&luckdraw_id=".$luckdraw_id;

			}else{

				$result['url']="share.php?team_sign=".$_SESSION['team_sign']."&uid=".$uid;

			}

            die($json->encode($result));

        }

        $sql="select team_num from ".$hhs->table('order_info') ." where order_id=".$_SESSION['team_sign'];

        $team_num=$db->getOne($sql);

        //实际人数

        $sql="select count(*) from ".$hhs->table('order_info')." where team_sign=".$_SESSION['team_sign']." and team_status>0 ";

        $rel_num=$db->getOne($sql);

        if($team_num<=$rel_num){

            $result['error']=1;

			if($luckdraw_id){

				$result['url']="share.php?team_sign=".$_SESSION['team_sign']."&uid=".$uid."&luckdraw_id=".$luckdraw_id;

			}else{

				$result['url']="share.php?team_sign=".$_SESSION['team_sign']."&uid=".$uid;

			}

            die($json->encode($result));

        }

    }

    $consignee = get_consignee($_SESSION['user_id']);

    /* 检查商品库存 */

    /* 如果使用库存，且下订单时减库存，则减少库存 */

    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

    {

        $cart_goods_stock = get_cart_goods();

        $_cart_goods_stock = array();

        foreach ($cart_goods_stock['goods_list'] as $value)

        {

            $_cart_goods_stock[$value['rec_id']] = $value['goods_number'];

        }

        flow_cart_stock($_cart_goods_stock);

        unset($cart_goods_stock, $_cart_goods_stock);

    }

    /*

     * 检查用户是否已经登录

    * 如果用户已经登录了则检查是否有默认的收货地址

    * 如果没有登录则跳转到登录和注册页面

    */

    $_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;

    $_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';

    $_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';

    $_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';

    $_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';

    $_POST['postscript'] = isset($_POST['postscript']) ? urldecode($_POST['postscript']) : '';

    $_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';
    $order = array(

        'shipping_id'     =>intval($_POST['shipping_id']),

        'suppliers_id'     =>intval($_POST['suppliers_id']),

        'point_id'         =>intval($_POST['point_id']),

        'pay_id'          => intval($_POST['payment']),

        'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,

        'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,

        'card_message'    => trim($_POST['card_message']),

        'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,

        'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,

        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,

        'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,

        'inv_type'        => $_POST['inv_type'],

        'inv_payee'       => trim($_POST['inv_payee']),

        'inv_content'     => $_POST['inv_content'],

        'postscript'      => trim($_POST['postscript']),

        'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',

        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,

        'user_id'         => $_SESSION['user_id'],

        'is_miao'         => $_SESSION['is_miao'], //秒杀产品

        'is_luck'         => $_SESSION['is_luck'], //抽奖产品

        'luck_times'      => $_SESSION['luck_times'], //抽奖产品

        'add_time'        => gmtime(),

        'order_status'    => OS_UNCONFIRMED,

        'shipping_status' => SS_UNSHIPPED,

        'pay_status'      => PS_UNPAYED,

        'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])),

        'lat'           =>trim($_POST['lat']),

        'lng'           =>trim($_POST['lng']),

        'city_id'           =>intval($_POST['city_id']),

        'district_id'           =>intval($_POST['district_id']),

        'checked_mobile' =>trim($_POST['checked_mobile']),//自提点手机

        'package_one'    =>isset($_POST['package_one'])?intval($_POST['package_one']):0,//团长收货        

    );

    if (! empty($order['checked_mobile'])) {

        $db->query('update '.$hhs->table('user_address').' set `mobile` = "'.$order['checked_mobile'].'" where user_id = "'.$_SESSION['user_id'].'" AND address_id = "'.$consignee['address_id'].'"');

    }

    /* 扩展信息 */

    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)

    {

        $order['extension_code'] = $_SESSION['extension_code'];

        $order['extension_id'] = $_SESSION['extension_id'];

        $order['team_sign'] = $_SESSION['team_sign'];

        //$order['team_first'] = 1;

    }

    else

    {

        $order['extension_code'] = '';

        $order['extension_id'] = $_SESSION['extension_id'];

        $order['team_sign'] = 0;

        //$order['team_first'] = 0;

    }

    $user_id = $_SESSION['user_id'];

    $user_info = user_info($user_id);

    /* 检查积分余额是否合法 */

    if ($user_id > 0)

    {

        $user_info = user_info($user_id);

        $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);

        if ($order['surplus'] < 0)

        {

            $order['surplus'] = 0;

        }

		 // 查询用户有多少积分

        $flow_points = flow_available_points();  // 该订单允许使用的积分

        $user_points = $user_info['pay_points']; // 用户的积分总数

        $order['integral'] = min($order['integral'], $user_points, $flow_points);

        if ($order['integral'] < 0)

        {

            $order['integral'] = 0;

        }

    }

    else

    {

        $order['surplus']  = 0;

    }

    $order['integral'] = 0;

    /* 检查优惠劵是否存在 */

    if ($order['bonus_id'] > 0)

    {

        $bonus = bonus_info($order['bonus_id']);

        if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))

        {

            $order['bonus_id'] = 0;

        }

    }

    elseif (isset($_POST['bonus_sn']))

    {

        $bonus_sn = trim($_POST['bonus_sn']);

        $bonus = bonus_info(0, $bonus_sn);

        $now = gmtime();

        if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_date'])

        {

        }

        else

        {

            if ($user_id > 0)

            {

                $sql = "UPDATE " . $hhs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";

                $db->query($sql);

            }

            $order['bonus_id'] = $bonus['bonus_id'];

            $order['bonus_sn'] = $bonus_sn;

        }

    }

    /* 订单中的商品 */

    $cart_goods = cart_goods($flow_type);

    $gods_img = $cart_goods[0]['little_img'];

	//qiyongdong 2016-10-11关于抽奖

	/* $luckdraw = $GLOBALS['db']->getRow("select * from ".$GLOBALS['hhs']->table('luckdraw')." where luck_status = 0 AND goods_id=".$cart_goods[0][goods_id]);

	if($luckdraw){

		$ntime = gmtime();

		$luckdraw_start_time = $luckdraw[start_time];

		$luckdraw_end_time = $luckdraw[end_time];

		if($ntime>$luckdraw_start_time && $ntime<$luckdraw_end_time){

			$order['luckdraw_id'] = $luckdraw['id'];

		}else{

			$order['luckdraw_id']='';

		}

	} */

    $order['luckdraw_id'] = $luckdraw_id;

   // print_R($cart_goods);die;

    if (empty($cart_goods))

    {

        //show_message($_LANG['no_goods_in_cart'], $_LANG['back_home'], './', 'warning');

        $result['error']=1;

        $result['message']=$_LANG['no_goods_in_cart'];

        die($json->encode($result));

    }

    $order['suppliers_id'] = $cart_goods[0]['suppliers_id'];

    $order['goods_id'] = $cart_goods[0]['goods_id'];

    /* 检查商品总额是否达到最低限购金额 */

    if ($flow_type == CART_GENERAL_GOODS && cart_amount(true, CART_GENERAL_GOODS) < $_CFG['min_goods_amount'])

    {

        //show_message(sprintf($_LANG['goods_amount_not_enough'], price_format($_CFG['min_goods_amount'], false)));

        $result['error']=1;

        $result['message']=sprintf($_LANG['goods_amount_not_enough'], price_format($_CFG['min_goods_amount'], false));

        die($json->encode($result));

    }

    /* 收货人信息 */

    unset($consignee['region']);//剔除，表里面没有这个

	unset($consignee['user_id']);

    foreach ($consignee as $key => $value)

    {

        $order[$key] = addslashes($value);

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

    /* 订单中的总额 */

    $total = order_fee($order, $cart_goods, $consignee);

    $order['bonus']        = $total['bonus'];

    $order['goods_amount'] = $total['goods_price'];

    $order['discount']     = $total['discount'];

    $order['surplus']      = $total['surplus'];

    $order['tax']          = $total['tax'];

    // 购物车中的商品能享受优惠劵支付的总额

    $discount_amout = compute_discount_amount();

    // 优惠劵和积分最多能支付的金额为商品总额

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

            //show_message($_LANG['balance_not_enough']);

            $result['error']=2;

            $result['message']=$_LANG['balance_not_enough'];

            die($json->encode($result));

        }

        else

        {

            $order['surplus'] = $order['order_amount'];

            $order['order_amount'] = 0;

        }

    }

    /* 如果订单金额为0（使用余额或积分或优惠劵支付），修改订单状态为已确认、已付款 */

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

    //系统自带的分成

    $order['parent_id'] = 0;

    //区分购买类别

    if ($order['extension_code']=='team_goods')

    {

        $order['order_type'] = 2;//团购

    }

    else{

        if($cart_goods[0]['is_zero'] == 1){

            $order['order_type'] = 3;//0元购

        }

        else{

            $order['order_type'] = 1;//普通商城

        }

    }//qiyongdong by 20160712

	if($cart_goods[0]['is_zero'] == 1)

	{

		$order['order_type'] = 3;//0元购

		$order['extension_code']='';

	}

    $best_time = isset($_POST['best_time']) ? urldecode($_POST['best_time']) : '';

    $order['best_time'] = $best_time;

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

        "city_id,district_id,suppliers_id,order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

        "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id,rate_1,rate_2,rate_3) ".

        " SELECT city_id,district_id,suppliers_id,'$new_order_id', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".

        "goods_price, goods_attr, is_real, '$_SESSION[extension_code]' , parent_id, is_gift, goods_attr_id,rate_1,rate_2,rate_3".

        " FROM " .$hhs->table('cart') .

        " WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";

    $db->query($sql);

   /*pangbin 插入用户默认自提点 start*/

    $point_id= intval($_POST['point_id']);

	$u_mobile= $order['mobile'];

	if(!empty($point_id) && !empty($u_mobile))

	{

		$sql="update ".$hhs->table('users')." set u_point=$point_id,u_mobile=$u_mobile where user_id=".$_SESSION['user_id'];

	    $db->query($sql);	

	}

	/*pangbin 插入用户默认自提点 end*/

    if ($order['extension_code']=='team_goods' )

    {

        if(empty($order['team_sign'])){

            $sql = "UPDATE ". $hhs->table('order_info') ." SET team_sign=".$order['order_id'].",team_first=1  WHERE order_id=".$order['order_id'];

            $db->query($sql);

            $order['team_sign']=$order['order_id'];

            $order['team_first']=1;

        }else{

            $sql = "UPDATE ". $hhs->table('order_info') ." SET team_first=2  WHERE order_id=".$order['order_id'];

            $db->query($sql);

            $order['team_first']=2;

        }

    }

    /*积分买商品*/

    if($_POST['chage']==1){

        $sql = "select is_exchange from ".$hhs->table('order_info')." where order_id='$new_order_id'";

        $sta = $db->getOne($sql);

        if($sta == 0){

            $pay_points = $user_info['pay_points']-$_SESSION['exchange_integral'];

            $sql = "update ".$hhs->table('users')." set  pay_points='$pay_points' where user_id = $user_id";

            $rs = $db->query($sql);

            if($rs){

                $sql = "update ".$hhs->table('order_info')." set is_exchange=1 where order_id='$new_order_id'";

                $db->query($sql);

                $sql = "insert into ".$hhs->table('account_log')."(user_id,pay_points,change_time,change_desc,change_type) values('$user_id','-{$_SESSION['exchange_integral']}',".gmtime().",'消费积分减少',99)";

                $db->query($sql);

                require_once(ROOT_PATH.'/includes/modules/payment/wxpay.php');

                $sqs = "select openid from ".$hhs->table('users')." where user_id = $user_id";

                 $openid = $db->getOne($sqs);

                $url = 'user.php?act=order_detail&order_id='.$new_order_id.'&uid='.$user_id;

                $desc = "恭喜您成功兑换'".$cart_goods[0]['goods_name']."'商品，\r\n稍后我们会为您尽快安排发货";

                $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

                $img = $gods_img;

                $weixin->send_wxmsg($openid, '兑换成功' , $url , $desc,$img);

            }

        }        

    }

    /* 清空购物车 */

    clear_cart($flow_type);

    /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */

    clear_all_files();

    /* 插入支付日志 */

    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

    /* 如果使用库存，且下订单时减库存，则减少库存 */

    // if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)

    // {

    //     change_order_goods_storage($order['order_id'], true, SDT_PLACE);

    // }

    if($_SESSION['is_luck'])

    {

        $goods_number = $cart_goods[0]['goods_number'];

        $temp = array();

        $is_payed = $order['order_amount'] > 0 ? 0 : 1;

        for ($i=0; $i < $goods_number; $i++) { 

            $temp[] = '("'.$new_order_id.'","'.$order['team_sign'].'","'.$is_payed.'")';

        }

        $db->query('insert into '.$hhs->table('order_luck').' (`order_id`,`team_sign`,`is_payed`) VALUES ' . join(',',$temp));

        // 分享地址

        $result['url']="userbao.php?order_id=".$new_order_id;

    }

//支付类型，在线支付或者代付

$pay_type = intval($_POST['pay_type']);

if ($pay_type) {

    # 在线支付

    /* 处理余额、积分、优惠劵 */

    if ($order['user_id'] > 0 && $order['surplus'] > 0)

    {

        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));

    }

    if ($order['bonus_id'] > 0 && $temp_amout > 0)

    {

        use_bonus($order['bonus_id'], $new_order_id);

    }

    $order['goods_name'] = $cart_goods[0]['goods_name'];

    /* 取得支付信息，生成支付代码 */

    if ($order['order_amount'] > 0)

    {

        $payment = payment_info($order['pay_id']);

        if($payment['pay_code']=='wxpay'){

            include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');

            $pay_obj    = new $payment['pay_code'];

			 if($luckdraw_id){

				$pay_online = $pay_obj->get_code3($order, $luckdraw_id,unserialize_config($payment['pay_config']));

			}else{

				$pay_online = $pay_obj->get_code2($order,unserialize_config($payment['pay_config']));

			} 

        //    $pay_online = $pay_obj->get_code2($order, unserialize_config($payment['pay_config']));

            if($cart_goods[0]['is_zero'])

            {

                $pay_online['returnrul'] = "sb-zero.php?order_id=".$new_order_id."&uid=".$uid."&goods_id=".$cart_goods[0]['goods_id'];

            }

            $order['pay_desc'] = $payment['pay_desc'];

        }

        $result['error']=0;

        $result['message']='';

    }else{

        $result['error']=1;

        $result['message']='user.php';//不需支付直接下单

        if($order['extension_code']=='team_goods'&&$cart_goods[0]['is_zero']!=1){

        	$result['url']="user.php?act=order_detail&order_id=".$new_order_id."&team=1&uid=".$uid;

        	pay_team_action($order['order_sn']);

        }else if($cart_goods[0]['is_zero']){

            $result['url']="sb-zero.php?order_id=".$new_order_id."&uid=".$uid."&goods_id=".$cart_goods[0]['goods_id'];

			pay_team_action($order['order_sn']);

        }

		else if($_SESSION['is_luck'])

		{

			$result['url']="userbao.php?order_id=".$new_order_id;

		}

        else{

				$result['url']="user.php?act=order_detail&order_id=".$new_order_id."&uid=".$uid;

        }

        //改变团购状态

    }

}

else{

    if ($order['bonus_id'] > 0 && $temp_amout > 0)

    {

        use_bonus($order['bonus_id'], $new_order_id);

    }

    $result['error']=1;

        $result['url'] = "share_pay.php?act=go_to_pay&id=".$new_order_id;

}    

    if(!empty($order['shipping_name']))

    {

        $order['shipping_name']=trim(stripcslashes($order['shipping_name']));

    }

    unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息

    unset($_SESSION['flow_order']);

    unset($_SESSION['direct_shopping']);

    $result['content']=$pay_online;

    if($payment['pay_code']){

        $result['pay_code']=$payment['pay_code'];

        $result['order_id']=$order['order_id'];

    }

    //插入分销相关

    // $goods_id = $cart_goods[0]['goods_id'];

    // $allow_fenxiao = $db->getOne("SELECT `allow_fenxiao` FROM ".$hhs->table('goods')." WHERE goods_id = " .$goods_id);

    // if($allow_fenxiao)

    // {

    //     $users = getUserPids($user_id);

    //     $rates = getSupplierRate($order['suppliers_id']);

    //     $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

    //     if($users['uid_1'] > 0 && $rates['rate_1'] > 0){

    //         $money = calcMoney($rates['rate_1'],$order['order_amount']);

    //         if($money > 0.00)

    //         {

    //             insertMoney($order['order_id'],$users['uid_1'],1,$order['order_amount'],$rates['rate_1'],$money,$order['add_time']);

    //             $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;

    //             $weixin->send_wxmsg($users['openid_1'], '捷报' , 'user.php?act=fenxiao' , $description );

    //         }

    //     }

    //     if($users['uid_2'] > 0 && $rates['rate_2'] > 0){

    //         $money = calcMoney($rates['rate_2'],$order['order_amount']);

    //         if($money > 0.00){

    //             insertMoney($order['order_id'],$users['uid_2'],2,$order['order_amount'],$rates['rate_2'],$money,$order['add_time']);

    //             $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;

    //             $weixin->send_wxmsg($users['openid_2'], '捷报' , 'user.php?act=fenxiao' , $description );

    //         }

    //     }

    //     if($users['uid_3'] > 0 && $rates['rate_3'] > 0){

    //         $money = calcMoney($rates['rate_3'],$order['order_amount']);

    //         if($money > 0.00){

    //             insertMoney($order['order_id'],$users['uid_3'],3,$order['order_amount'],$rates['rate_3'],$money,$order['add_time']);

    //             $description = '您分享的商品经过长途跋涉 终于有回报了' . $money;

    //             $weixin->send_wxmsg($users['openid_3'], '捷报' , 'user.php?act=fenxiao' , $description );

    //         }

    //     }

    // }

    //**

    //插入抽奖夺宝相关

    if($_SESSION['is_luck'])

    {

        // $goods_number = $cart_goods[0]['goods_number'];

        // $temp = array();

        // $is_payed = $order['order_amount'] > 0 ? 0 : 1;

        // for ($i=0; $i < $goods_number; $i++) { 

        //     $temp[] = '("'.$new_order_id.'","'.$order['team_sign'].'","'.$is_payed.'")';

        // }

        // $db->query('insert into '.$hhs->table('order_luck').' (`order_id`,`team_sign`,`is_payed`) VALUES ' . join(',',$temp));

        // 分享地址

        $result['url']="userbao.php?order_id=".$new_order_id;

    }

    ob_end_clean();

    die($json->encode($result));

}

/*------------------------------------------------------ */

//-- 更新购物车

/*------------------------------------------------------ */

elseif($_REQUEST['step'] == 'update_cart')

{

    include_once('includes/cls_json.php');

    $json = new JSON();

    $result = array('error' => 0, 'content' => '');

    $rec_id = $_GET['rec_id'];

    $number = $_GET['number'];

    $goods_id = $_GET['goods_id'];

    $result['rec_id'] = $rec_id;

    /* 检查：库存 */

    if ($GLOBALS['_CFG']['use_storage'] == 1)

    {

        $goods_number = $GLOBALS['db']->getOne("select goods_number from ".$GLOBALS['hhs']->table('goods')." where goods_id='$goods_id'");

        if($number>$goods_number)

        {

             $result['error'] = 1 ;

             $result['message'] ='对不起,您选择的数量超出库存您最多可购买'.$goods_number."件";

             $result['number']=$goods_number;

             die($json->encode($result));

        }

    }

    $limit_buy_bumber = $db->getOne("select limit_buy_bumber from ".$hhs->table('goods')." where goods_id='$goods_id'");

    if ($number == 0)

    {

        $result['error'] = 1 ;

        $result['number'] = $number = 1;

        die($json->encode($result));

    }

    if($number>$limit_buy_bumber&&$limit_buy_bumber>0)

    {

        $result['error'] = 1 ;

        $result['message'] = '购买数量不可大于限购数量';

        $result['number'] = $limit_buy_bumber;

        die($json->encode($result));

    }

    if($_SESSION['is_luck']){

       $left_num = $GLOBALS['db']->getOne('select (team_num-teammen_num) as left_num from '.$GLOBALS['hhs']->table('order_info').' where extension_id="'.$goods_id.'" and team_status = 1 AND pay_status=2 order by order_id desc');

       // $left_num && $goods['goods_number'] = $left_num;

        if($left_num > 0 && $number>$left_num)

        {

            $result['error'] = 1 ;

            $result['message'] = '对不起,您选择的数量超出库存您最多可购买'.$left_num."件";

            $result['number'] = $left_num;

            die($json->encode($result));

        }

    }

    $sql = "UPDATE " . $GLOBALS['hhs']->table('cart') . " SET goods_number = '$number' WHERE rec_id = $rec_id";

    $GLOBALS['db']->query($sql);

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    $order = flow_order_info();

    $cart_goods = cart_goods($_SESSION['flow_type']); // 取得商品列表，计算合计

    $total = order_fee($order, $cart_goods, $consignee);

    $smarty->assign('total', $total);

    $result['content'] =$smarty->fetch("library/order_total.lbi");

    $result['number'] = $number ;

    die($json->encode($result));

}

elseif ($_REQUEST['step'] == 'link_buy')

{

    $goods_id = intval($_GET['goods_id']);

    if (!cart_goods_exists($goods_id,array()))

    {

        addto_cart($goods_id);

    }

    hhs_header("Location:./flow.php\n");

    exit;

}

/*------------------------------------------------------ */

//-- 删除购物车中的商品

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'drop_goods')

{

    $rec_id = intval($_GET['id']);

    flow_drop_cart_goods($rec_id);

    hhs_header("Location: flow.php\n");

    exit;

}

/* 把优惠活动加入购物车 */

elseif ($_REQUEST['step'] == 'add_favourable')

{

    /* 取得优惠活动信息 */

    $act_id = intval($_POST['act_id']);

    $favourable = favourable_info($act_id);

    if (empty($favourable))

    {

        show_message($_LANG['favourable_not_exist']);

    }

    /* 判断用户能否享受该优惠 */

    if (!favourable_available($favourable))

    {

        show_message($_LANG['favourable_not_available']);

    }

    /* 检查购物车中是否已有该优惠 */

    $cart_favourable = cart_favourable();

    if (favourable_used($favourable, $cart_favourable))

    {

        show_message($_LANG['favourable_used']);

    }

    /* 赠品（特惠品）优惠 */

    if ($favourable['act_type'] == FAT_GOODS)

    {

        /* 检查是否选择了赠品 */

        if (empty($_POST['gift']))

        {

            show_message($_LANG['pls_select_gift']);

        }

        /* 检查是否已在购物车 */

        $sql = "SELECT goods_name" .

                " FROM " . $hhs->table('cart') .

                " WHERE session_id = '" . SESS_ID . "'" .

                " AND rec_type = '" . CART_GENERAL_GOODS . "'" .

                " AND is_gift = '$act_id'" .

                " AND goods_id " . db_create_in($_POST['gift']);

        $gift_name = $db->getCol($sql);

        if (!empty($gift_name))

        {

            show_message(sprintf($_LANG['gift_in_cart'], join(',', $gift_name)));

        }

        /* 检查数量是否超过上限 */

        $count = isset($cart_favourable[$act_id]) ? $cart_favourable[$act_id] : 0;

        if ($favourable['act_type_ext'] > 0 && $count + count($_POST['gift']) > $favourable['act_type_ext'])

        {

            show_message($_LANG['gift_count_exceed']);

        }

        /* 添加赠品到购物车 */

        foreach ($favourable['gift'] as $gift)

        {

            if (in_array($gift['id'], $_POST['gift']))

            {

                add_gift_to_cart($act_id, $gift['id'], $gift['price']);

            }

        }

    }

    elseif ($favourable['act_type'] == FAT_DISCOUNT)

    {

        add_favourable_to_cart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);

    }

    elseif ($favourable['act_type'] == FAT_PRICE)

    {

        add_favourable_to_cart($act_id, $favourable['act_name'], $favourable['act_type_ext']);

    }

    /* 刷新购物车 */

    hhs_header("Location: flow.php\n");

    exit;

}

elseif ($_REQUEST['step'] == 'clear')

{

    $sql = "DELETE FROM " . $hhs->table('cart') . " WHERE session_id='" . SESS_ID . "'";

    $db->query($sql);

    hhs_header("Location:./index.php\n");

}

elseif ($_REQUEST['step'] == 'drop_to_collect')

{

    if ($_SESSION['user_id'] > 0)

    {

        $rec_id = intval($_GET['id']);

        $goods_id = $db->getOne("SELECT  goods_id FROM " .$hhs->table('cart'). " WHERE rec_id = '$rec_id' AND session_id = '" . SESS_ID . "' ");

        $count = $db->getOne("SELECT goods_id FROM " . $hhs->table('collect_goods') . " WHERE user_id = '$_SESSION[user_id]' AND goods_id = '$goods_id'");

        if (empty($count))

        {

            $time = gmtime();

            $sql = "INSERT INTO " .$GLOBALS['hhs']->table('collect_goods'). " (user_id, goods_id, add_time)" .

                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

            $db->query($sql);

        }

        flow_drop_cart_goods($rec_id);

    }

    hhs_header("Location: flow.php\n");

    exit;

}

/* 验证优惠劵序列号 */

elseif ($_REQUEST['step'] == 'validate_bonus')

{

    $bonus_sn = trim($_REQUEST['bonus_sn']);

    if (is_numeric($bonus_sn))

    {

        $bonus = bonus_info(0, $bonus_sn);

    }

    else

    {

        $bonus = array();

    }

//    if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0)

//    {

//        die($_LANG['bonus_sn_error']);

//    }

//    if ($bonus['min_goods_amount'] > cart_amount())

//    {

//        die(sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false)));

//    }

//    die(sprintf($_LANG['bonus_is_ok'], price_format($bonus['type_money'], false)));

    $bonus_kill = price_format($bonus['type_money'], false);

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */

    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))

    {

        $result['error'] = $_LANG['no_goods_in_cart'];

    }

    else

    {

        /* 取得购物流程设置 */

        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */

        $order = flow_order_info();

        if (((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)

        {

            //$order['bonus_kill'] = $bonus['type_money'];

            $now = gmtime();

            if ($now > $bonus['use_end_date'])

            {

                $order['bonus_id'] = '';

                $result['error']=$_LANG['bonus_use_expire'];

            }

            else

            {

                $order['bonus_id'] = $bonus['bonus_id'];

                $order['bonus_sn'] = $bonus_sn;

            }

        }

        else

        {

            //$order['bonus_kill'] = 0;

            $order['bonus_id'] = '';

            $result['error'] = $_LANG['invalid_bonus'];

        }

        /* 计算订单的费用 */

        $total = order_fee($order, $cart_goods, $consignee);

        if($total['goods_price']<$bonus['min_goods_amount'])

        {

         $order['bonus_id'] = '';

         /* 重新计算订单 */

         $total = order_fee($order, $cart_goods, $consignee);

         $result['error'] = sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false));

        }

        $smarty->assign('total', $total);

        /* 团购标志 */

        if ($flow_type == CART_GROUP_BUY_GOODS)

        {

            $smarty->assign('is_group_buy', 1);

        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');

    }

    $json = new JSON();

    die($json->encode($result));

}

/*------------------------------------------------------ */

//-- 添加礼包到购物车

/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'add_package_to_cart')

{

    include_once('includes/cls_json.php');

    $_POST['package_info'] = json_str_iconv($_POST['package_info']);

    $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');

    $json  = new JSON;

    if (empty($_POST['package_info']))

    {

        $result['error'] = 1;

        die($json->encode($result));

    }

    $package = $json->decode($_POST['package_info']);

    /* 商品数量是否合法 */

    if (!is_numeric($package->number) || intval($package->number) <= 0)

    {

        $result['error']   = 1;

        $result['message'] = $_LANG['invalid_number'];

    }

    else

    {

        /* 添加到购物车 */

        if (add_package_to_cart($package->package_id, $package->number))

        {

            $result['content'] = insert_cart_info();

        }

        else

        {

            $result['message']    = $err->last_message();

            $result['error']      = $err->error_no;

            $result['package_id'] = stripslashes($package->package_id);

        }

    }

    die($json->encode($result));

}

else

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

assign_dynamic('shopping_flow');

$smarty->assign('luckdraw_id',$luckdraw_id);

$smarty->display('flow.dwt');

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

        $sql = "SELECT g.goods_name, g.goods_number ".

                "FROM " .$GLOBALS['hhs']->table('goods'). " AS g, ".

                    $GLOBALS['hhs']->table('cart'). " AS c ".

                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";

        $row = $GLOBALS['db']->getRow($sql);

        //查询：系统启用了库存，检查输入的商品数量是否有效

        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')

        {

            if ($row['goods_number'] < $val)

            {

                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                $row['goods_number'], $row['goods_number']));

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

                    show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],

                    $product_number['product_number'], $product_number['product_number']));

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

                $goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);

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

function get_regions_name($region_id)

{

    return $GLOBALS['db']->getOne("select region_name from ".$GLOBALS['hhs']->table('region')." where region_id='$region_id'");

}

function get_shipping_point_list()

{

    if($_SESSION['goods_suppliers_id']){

        $andwhere = " WHERE `suppliers_id` = " . $_SESSION['goods_suppliers_id'];

    }

    else{

        $andwhere = '';

    }

    $sql = "SELECT a.*,rp.region_name as province,rc.region_name as city,rd.region_name as district " .

        " FROM " . $GLOBALS['hhs']->table('shipping_point'). " AS a left join " .

        $GLOBALS['hhs']->table('region') . " AS rp on a.province=rp.region_id left join ".

        $GLOBALS['hhs']->table('region') . " as rc on a.city=rc.region_id left join ".

        $GLOBALS['hhs']->table('region') ." as rd on a.district=rd.region_id " . $andwhere;

    $list=$GLOBALS['db']->getAll($sql);

    return $list;

}

?>