<?php
/**
 * 小舍电商商家管理
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: suppliers.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require (dirname(__FILE__) . '/includes/init.php');
define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');
include_once (ROOT_PATH . 'includes/cls_image.php');
/*
include_once (ROOT_PATH . 'includes/cls_sms.php');
$sms = new sms();
*/
$smarty->assign('lang', $_LANG);
$smarty->assign('cfg_percentage', $_CFG['percentage']);
$image = new cls_image($_CFG['bgcolor']);
/*
$smarty->assign('site_list', get_sitelists());
if ($_REQUEST['act'] == 'get_sitelist') 
{
    $list = get_sitelists();
    foreach ($list as $idx => $value) 
    {
        $html .= '<input type="checkbox" name="site_id[]" value="' . $value['id'] . '">' . $value['name'] . '';
    }
    make_json_result($html);
}*/
/* ------------------------------------------------------ */
// -- 供货商列表
/* ------------------------------------------------------ */
if ($_REQUEST['act'] == 'list') 
{
    /* 检查权限 */
    admin_priv('suppliers_list');

    /* 查询 */

    $result = suppliers_list();

    //$smarty->assign('ranks', get_suppliers_type_list());

    /* 模板赋值 */

    $smarty->assign('ur_here', $_LANG['suppliers_list']); // 当前导航

    $smarty->assign('action_link', array(

        'href' => 'suppliers.php?act=add',

        'text' => $_LANG['add_suppliers']

    ));

    $smarty->assign('full_page', 1); // 翻页参数

    $smarty->assign('suppliers_list', $result['result']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    $smarty->assign('provinces', get_regions(1, 1));

    $smarty->assign('cities', get_regions(2, $suppliers['province_id']));

    /* 显示模板 */

    assign_query_info();

    $smarty->display('suppliers_list.htm');

} 

/* ------------------------------------------------------ */

// --Excel文件下载

/* ------------------------------------------------------ */

else if ($_REQUEST['act'] == 'download') 

    {

        /* 检查权限 */

        admin_priv('sup_list_down');

        $suppliers_list = suppliers_list(false);

        header("Content-type: application/vnd.ms-excel; charset=utf-8");

        header("Content-Disposition: attachment; filename=商家列表.xls");

        /* 文件标题 */

        echo hhs_iconv(EC_CHARSET, 'GB2312', '商家名称') . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', '电话') . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', '审核状态') . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', '注册日期') . "\t\n";

        $ck_status = array('未通过','已审核','审核中');

        $i = 1;

        foreach ($suppliers_list['result'] as $key => $value) 

        {

            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['suppliers_name']) . "\t";

            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['phone']) . "\t";

            echo hhs_iconv(EC_CHARSET, 'GB2312', $ck_status[$value['is_check']] ) . "\t";

            echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_date']) . "\t";

            echo "\n";

            $i ++;

        }

        exit();

    } 

    elseif ($_REQUEST['act'] == 'factoryauthorized') 

    {

        $list = get_factoryauthorized($_GET['id']);

        $smarty->assign('suppliers_id', $_REQUEST['id']);

        $smarty->assign('list', $list);

        $smarty->assign('full_page', 1); // 翻页参数

        $smarty->display('suppliers_factoryauthorized.htm');

    } 

    elseif ($_REQUEST['act'] == 'trademark') 

    {

        $list = get_trademark($_GET['id']);

        $smarty->assign('suppliers_id', $_REQUEST['id']);

        $smarty->assign('list', $list);

        $smarty->assign('full_page', 1); // 翻页参数

        $smarty->display('suppliers_trademark.htm');

    } 

    elseif ($_REQUEST['act'] == 'trademark_is_checked') 

    {

        check_authz_json('suppliers_list_manage');

        $id = intval($_REQUEST['id']);

        $sql = "SELECT id,is_checked

            FROM " . $hhs->table('suppliers_trademark') . "

            WHERE id = '$id'";

        $companys = $db->getRow($sql, TRUE);

        if ($companys['id']) 

        {

            $_companys['is_checked'] = empty($companys['is_checked']) ? 1 : 0;

            $db->autoExecute($hhs->table('suppliers_trademark'), $_companys, '', "id = '$id'");

            clear_cache_files();

            make_json_result($_companys['is_checked']);

        }

        exit();

    } 

    elseif ($_REQUEST['act'] == 'trademark_remove') 

    {

        $pic = $db->getOne("select pic from " . $hhs->table('suppliers_trademark') . " where id='$_REQUEST[id]'");

        unlink(ROOT_PATH . $pic);

        $sql = "DELETE FROM " . $hhs->table('suppliers_trademark') . "

            WHERE id = '$_REQUEST[id]'";

        $db->query($sql);

        $suppliers_id = $_REQUEST['suppliers_id'];

        $url = 'suppliers.php?act=trademark&suppliers_id=$suppliers_id' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

        hhs_header("Location: $url\n");

        exit();

    } 

    elseif ($_REQUEST['act'] == 'trademark_query') 

    {

        check_authz_json('suppliers_list_manage');

        $list = get_trademark($_GET['id']);

        $smarty->assign('list', $list);

        make_json_result($smarty->fetch('suppliers_trademark.htm'), '', 

        array(

            'filter' => '',

            'page_count' => ''

        ));

    } 

    elseif ($_REQUEST['act'] == 'factoryauthorized_is_checked') 

    {

        check_authz_json('suppliers_list_manage');

        $id = intval($_REQUEST['id']);

        $sql = "SELECT id,is_checked

            FROM " . $hhs->table('suppliers_factoryauthorized') . "

            WHERE id = '$id'";

        $companys = $db->getRow($sql, TRUE);

        if ($companys['id']) 

        {

            $_companys['is_checked'] = empty($companys['is_checked']) ? 1 : 0;

            $db->autoExecute($hhs->table('suppliers_factoryauthorized'), $_companys, '', "id = '$id'");

            clear_cache_files();

            make_json_result($_companys['is_checked']);

        }

        exit();

    } 

    elseif ($_REQUEST['act'] == 'factoryauthorized_remove') 

    {

        $pic = $db->getOne("select pic from " . $hhs->table('suppliers_factoryauthorized') . " where id='$_REQUEST[id]'");

        unlink(ROOT_PATH . $pic);

        $sql = "DELETE FROM " . $hhs->table('suppliers_factoryauthorized') . "

            WHERE id = '$_REQUEST[id]'";

        $db->query($sql);

        $suppliers_id = $_REQUEST['suppliers_id'];

        $url = 'suppliers.php?act=factoryauthorized&suppliers_id=$suppliers_id' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

        hhs_header("Location: $url\n");

        exit();

    } 

    elseif ($_REQUEST['act'] == 'factoryauthorized_query') 

    {

        check_authz_json('suppliers_list_manage');

        $list = get_factoryauthorized($_GET['id']);

        $smarty->assign('list', $list);

        make_json_result($smarty->fetch('suppliers_factoryauthorized.htm'), '', 

        array(

            'filter' => '',

            'page_count' => ''

        ));

    }    

    /* ------------------------------------------------------ */

    // -- 排序、分页、查询

    /* ------------------------------------------------------ */

    elseif ($_REQUEST['act'] == 'query') 

    {

        check_authz_json('suppliers_list');

        $result = suppliers_list();

        $smarty->assign('suppliers_list', $result['result']);

        $smarty->assign('filter', $result['filter']);

        $smarty->assign('record_count', $result['record_count']);

        $smarty->assign('page_count', $result['page_count']);

        /* 排序标记 */

        $sort_flag = sort_flag($result['filter']);

        $smarty->assign($sort_flag['tag'], $sort_flag['img']);

        make_json_result($smarty->fetch('suppliers_list.htm'), '', 

        array(

            'filter' => $result['filter'],

            'page_count' => $result['page_count']

        ));

    }

if ($_REQUEST['act'] == 'accounts_query') {

    check_authz_json('suppliers_accounts');

    $result = suppliers_accounts_list();

    /* 显示模板 */

    $smarty->assign('suppliers_accounts_list', $result['result']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('total', $result['total']);

    /* 排序标记 */

    $sort_flag = sort_flag($result['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('suppliers_accounts.htm'), '', 

    array(

        'filter' => $result['filter'],

        'page_count' => $result['page_count']

    ));

} elseif ($_REQUEST['act'] == 'accounts_detail_query') {

    check_authz_json('suppliers_accounts');

    $result = suppliers_accounts_detail_list();

    /* 显示模板 */

    $smarty->assign('total_order_amount', $result['total_order_amount']);

    $smarty->assign('suppliers_accounts_detail', $result['result']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('total', $result['total']);

    /* 排序标记 */

    $sort_flag = sort_flag($result['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('suppliers_accounts_detail.htm'), '', 

    array(

        'filter' => $result['filter'],

        'page_count' => $result['page_count']

    ));

} 

elseif ($_REQUEST['act'] == 'settlement') 

{

    $suppliers_id = $_REQUEST['suppliers_id'];

    $suppliers_time_format = $_CFG['suppliers_time_format'];

    $result = suppliers_accounts();

    /* 模板赋值 */

    $smarty->assign('ur_here', '结算清单'); // 当前导航

    $smarty->assign('action_link', array(

        'href' => 'suppliers.php?act=add',

        'text' => '立即结算'

    ));

    $smarty->assign('full_page', 1); // 翻页参数

    $smarty->assign('accounts_list', $result['result']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    /* 显示模板 */

    assign_query_info();

    $smarty->display('suppliers_settlement.htm');

} 

elseif ($_REQUEST['act'] == 'settlement_act') {

    $suppliers_id = $_REQUEST['suppliers_id'];

    $suppliers_time_format = $_CFG['suppliers_time_format'];

    $wtime = local_strtotime("-$suppliers_time_format day");

    $ex_where = " settlement_status=0 and suppliers_id ='$suppliers_id' AND order_status " . db_create_in(array(

        OS_SPLITED,OS_CONFIRMED )) . " AND shipping_status " . db_create_in(array(

        SS_RECEIVED

    )) . "  AND pay_status " . db_create_in(array(

        PS_PAYED

    ));

    $order_amount_filed = "`money_paid`+`surplus`+bonus -shipping_fee ";

	$order_amount_filed1 = "`money_paid`+`surplus`+bonus ";

    // $sql = "select SUM(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee from " . $hhs->table('order_info') . " where suppliers_id ='$suppliers_id' AND " 

    $sql = "select SUM(".$order_amount_filed.") AS total_fee from " . $hhs->table('order_info') . " where suppliers_id ='$suppliers_id' AND " 

        . $ex_where;

		//echo $sql;exit;

    $total_all = floatval($db->getOne($sql));

    if($total_all < floatval($_CFG['min_money']))

    {

        $links = array(

            array(

                'href' => 'suppliers.php?act=suppliers_accounts&suppliers_id=' . $suppliers_id,

                'text' => '结算列表'

            )

        );

        sys_msg('所有未结订单金额不足最低结算金额，无法结算', 0, $links);

    }

    // $sql = "select order_id,order_sn,pay_time,add_time,(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee from " . $hhs->table('order_info') . " where ";

    $sql = "select order_id,order_sn,pay_time,shipping_fee,add_time,(".$order_amount_filed.") AS total_fee,(".$order_amount_filed1.") AS total_fee1,fenxiao_money from " . $hhs->table('order_info') . " where ";

    $sql .= $ex_where;

    //echo $sql;exit();

    $order_list = $db->getAll($sql);

    if ($order_list) 

    {

        // 插入结算总表中

        $settlement_sn = get_settlement_sn();

        $add_time = gmtime();

        $sql_insert = $db->query("insert into " . $hhs->table('suppliers_accounts') . " (suppliers_id,settlement_sn,add_time) values('$suppliers_id','$settlement_sn','$add_time')");

        $suppliers_accounts_id = $db->insert_id();

        $commission_all = 0;

        $total=0;

        // change the amount

        // $goods_amount = $db->getOne('select SUM(`goods_amount`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

        $order_amount = $db->getOne('select SUM('.$order_amount_filed.') from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

        $fenxiao_money = $db->getOne('select SUM(`fenxiao_money`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

        $shipping_fee =  $db->getOne('select SUM(`shipping_fee`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

	    $percentage = $db->getOne('SELECT `percentage` FROM '. $hhs->table('suppliers') .' WHERE suppliers_id =' . $suppliers_id);

		if($percentage==0)

		{

			$percentage = $GLOBALS['_CFG']['percentage'];

		}

        // 插入明细表

        foreach ($order_list as $idx => $value) 

        {

            $commission = $db->getOne("select (".$order_amount_filed.")  as total from " . $hhs->table('order_info') . " where order_id='$value[order_id]'");

            $commission = $commission*$percentage/100;

            $commission = number_format($commission,2,'.','');

            /*

            $commission = $db->getOne("select sum(goods_price*goods_number) as total from " . $hhs->table('order_goods') . " where order_id='$value[order_id]'");

            $commission = $commission*$percentage/100;

            $commission = number_format($commission,2,'.','');*/

			//$total_fee = $value['total_fee']+ $value['shipping_fee'];

            $db->query("insert into " . $hhs->table('suppliers_accounts_detal') . " (order_sn,order_id,order_time,commission,suppliers_accounts_id,amount,fenxiao_money) values ('$value[order_sn]','$value[order_id]','$value[pay_time]','$commission','$suppliers_accounts_id','$value[total_fee1]','$value[fenxiao_money]')");

            // 更新订单中的结算状态等信息

            $sql = "update " . $hhs->table('order_info') . " set settlement_status=1,commission='$commission',suppliers_accounts_id=" . $suppliers_accounts_id . " where order_id=" . $value['order_id'];

		    $db->query($sql);

            $commission_all = $commission_all + $commission;

            $total = $total + $value['total_fee'];

        }

        // 更新总表

        // $settlement_amount = $total - $commission_all;

        // $sql = $db->query("update " . $hhs->table('suppliers_accounts') . " set settlement_amount='$settlement_amount' ,settlement_status=1 where id='$suppliers_accounts_id'");

        //佣金

        $commission = number_format(($order_amount) * $percentage / 100,2,'.','');

        //实结

        $settlement_amount = $total - $commission - $fenxiao_money+$shipping_fee;

		$totals = $total+ $shipping_fee;

        //

        $sql = $db->query("update " . $hhs->table('suppliers_accounts') . " set settlement_amount='$settlement_amount' ,commission='$commission' ,total='$totals' ,fenxiao_money='$fenxiao_money' ,settlement_status=1 where id='$suppliers_accounts_id'"); 

        $user_id=$db->getOne("select user_id from " . $hhs->table('suppliers') . "

             where `suppliers_id`= " .$suppliers_id );

        $settlement_status = 1;

        $wxch_order_name = 'pay_msgs';

        require_once (ROOT_PATH . 'wxch_order.php');                

        $links = array(

            array(

                'href' => 'suppliers.php?act=suppliers_accounts&suppliers_id=' . $suppliers_id,

                'text' => '结算列表'

            )

        );

        sys_msg('操作成功', 0, $links);

    } 

    else 

    {

        $links = array(

            array(

                'href' => 'suppliers.php?act=suppliers_accounts&suppliers_id=' . $suppliers_id,

                'text' => '结算列表'

            )

        );

        sys_msg('暂无订单结算', 0, $links);

    }

} 

elseif ($_REQUEST['act'] == 'settlement_act2') 

{

    $start_date=local_date('Y-m-d 0:0:0',gmtime());

    $start_time=local_strtotime($start_date);

    $end_date=local_date('Y-m-d 23:59:59',gmtime());

    $end_time=local_strtotime($end_date);

    $order_amount_filed = "`money_paid`+`surplus`+bonus -shipping_fee";

	$order_amount_filed1 = "`money_paid`+`surplus`+bonus ";

    $sql="select * from ".$hhs->table('suppliers_accounts_log')." where add_time>'$start_time' and add_time<'$end_time' ";

    $log=$db->getRow($sql);

    if(empty($log)){

        $suppliers_time_format = $_CFG['suppliers_time_format'];

        $now_day = local_date("d");

        $wtime = local_strtotime("-$suppliers_time_format day");

        $ex_where = " settlement_status=0 and  order_status " . db_create_in(array(

            OS_SPLITED,OS_CONFIRMED

        )) . " AND shipping_status " . db_create_in(array(

            SS_RECEIVED

        )) . "  AND pay_status " . db_create_in(array(

            PS_PAYED

        ));

        $sql = "select * from " . $hhs->table('suppliers');

        $suppliers = $db->getAll($sql);

        foreach ($suppliers as $v) {

            $suppliers_id = $v[suppliers_id];

            /**

             * check the amount

             */

            // $sql = "select SUM(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee from " . $hhs->table('order_info') . " where suppliers_id ='$suppliers_id' " 

            $sql = "select SUM(".$order_amount_filed.") AS total_fee from " . $hhs->table('order_info') . " where suppliers_id ='$suppliers_id' and " 

                . $ex_where;

            $total_all = floatval($db->getOne($sql));

            if($total_all < floatval($_CFG['min_money']))

                continue;

            $sql = "select order_id,order_sn,pay_time,shipping_fee,add_time,(".$order_amount_filed.") AS total_fee,(".$order_amount_filed1.") AS total_fee1,fenxiao_money from " . $hhs->table('order_info') . " where suppliers_id='$suppliers_id'";

            $sql.= "  and  ". $ex_where;

            $order_list = $db->getAll($sql);

            if ($order_list) {

                // 插入结算总表中

                $settlement_sn = get_settlement_sn();

                $add_time = gmtime();

                $sql_insert = $db->query("insert into " . $hhs->table('suppliers_accounts') . " (suppliers_id,settlement_sn,add_time) values('$suppliers_id','$settlement_sn','$add_time')");

                $suppliers_accounts_id = $db->insert_id();

                $commission_all = 0;

                $total=0;

                // 插入明细表

                /*

                // change the amount

                $goods_amount = $db->getOne('select SUM(`money_paid`+`surplus`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . $ex_where);*/

                $order_amount = $db->getOne('select SUM('.$order_amount_filed.') from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

                $fenxiao_money = $db->getOne('select SUM(`fenxiao_money`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id." and " . $ex_where);

       			$shipping_fee =  $db->getOne('select SUM(`shipping_fee`) from '. $hhs->table('order_info') .' where suppliers_id ='.$suppliers_id . ' and '. $ex_where);

                $percentage = $db->getOne('SELECT `percentage` FROM '. $hhs->table('suppliers') .' WHERE suppliers_id =' . $suppliers_id);

		if($percentage==0)

		{

			$percentage = $GLOBALS['_CFG']['percentage'];

		}

                foreach ($order_list as $idx => $value)

                {

        /*

                    $commission = $db->getOne("select sum(goods_price*goods_number) as total from " . $hhs->table('order_goods') . " where order_id='$value[order_id]'");

                    $commission = $commission*$percentage/100;

                    $commission = number_format($commission,2,'.','');

*/

                    $commission = $db->getOne("select (".$order_amount_filed.")  as total from " . $hhs->table('order_info') . " where order_id='$value[order_id]'");

                    $commission = $commission*$percentage/100;

                    $commission = number_format($commission,2,'.','');

                    $db->query("insert into " . $hhs->table('suppliers_accounts_detal') . " (order_sn,order_id,order_time,commission,suppliers_accounts_id,amount,fenxiao_money) values ('$value[order_sn]','$value[order_id]','$value[pay_time]','$commission','$suppliers_accounts_id','$value[total_fee1]','$value[fenxiao_money]')");

                    // 更新订单中的结算状态等信息

                    $sql = "update " . $hhs->table('order_info') . " set settlement_status=1,commission='$commission',suppliers_accounts_id=" . $suppliers_accounts_id . " where order_id=" . $value['order_id'];

                    $db->query($sql);

                    $commission_all = $commission_all + $commission;

                    $total = $total + $value['total_fee'];

                }

                // 更新总表

                // $settlement_amount = $total - $commission_all;

                // $sql = $db->query("update " . $hhs->table('suppliers_accounts') . " set settlement_amount='$settlement_amount' ,settlement_status=1 where id='$suppliers_accounts_id'");

                //佣金

                $commission = number_format($order_amount * $percentage / 100,2,'.','');

                //实结

        		$settlement_amount = $total - $commission - $fenxiao_money+$shipping_fee;

				$totals = $total+ $shipping_fee;

                //

                $sql = $db->query("update " . $hhs->table('suppliers_accounts') . " set settlement_amount='$settlement_amount' ,commission='$commission' ,total='$totals',fenxiao_money='$fenxiao_money' ,settlement_status=1 where id='$suppliers_accounts_id'");

                $user_id=$v['user_id'];

                $settlement_status = 1;

                $wxch_order_name = 'pay_msgs';

                require_once (ROOT_PATH . 'wxch_order.php');                

            }

        }

        $sql="insert into ".$hhs->table('suppliers_accounts_log')." (add_time,admin_id) values (".gmtime().",'$_SESSION[admin_id]')";

        $db->query($sql);

        $links = array(

            array(

                'href' => 'suppliers.php?act=suppliers_accounts',

                'text' => '结算列表'

            )

        );

        sys_msg('结算单生成完毕', 0, $links);

    }else{

        $links = array(

            array(

                'href' => 'suppliers.php?act=suppliers_accounts',

                'text' => '结算列表'

            )

        );

        sys_msg('今天已经结算', 0, $links);

    }

} elseif ($_REQUEST['act'] == 'suppliers_accounts') 

{

    /* 检查权限 */

    admin_priv('suppliers_accounts');

    /* 查询 */

    $result = suppliers_accounts_list();

    //var_dump($result);exit();

    /* 模板赋值 */

    $smarty->assign('ur_here', '商家结算'); // 当前导航

    $smarty->assign('action_link', array(

        'href' => 'suppliers.php?act=account_download&uselastfilter=1',

        'text' => '结算单下载'

    ));

    $smarty->assign('action_link2', array(

        'href' => 'suppliers.php?act=account_print',

        'text' => '结算单打印'

    ));

    if ($_REQUEST['suppliers_id']) {

        $smarty->assign('action_link3', array(

            'href' => 'suppliers.php?act=settlement_act&suppliers_id=' . $_REQUEST['suppliers_id'],

            'text' => '订单结算'

        ));

    } else {

        $smarty->assign('action_link3', array(

            'href' => 'suppliers.php?act=settlement_act2',

            'text' => '结算'

        ));

    }

    $smarty->assign('full_page', 1); // 翻页参数

    $smarty->assign('suppliers_accounts_list', $result['result']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    /* 显示模板 */

    assign_query_info();

    $smarty->display('suppliers_accounts.htm');

} 

elseif ($_REQUEST['act'] == 'account_download') {

    $title = "结算单";

    $result = suppliers_accounts_list();

    $account = $result['result'];

    header("Content-type: application/vnd.ms-excel; charset=utf-8");

    header("Content-Disposition: attachment; filename=" . $title . ".xls");

    /* 文件标题 */

    echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '编号') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算单号') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '商家') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算月份') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算起始时间') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算截止时间') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算时间') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算金额') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '状态') . "\t\n";

    $total_settlement_amount = 0;

    foreach ($account as $key => $value) 

    {

        $total_settlement_amount += $value['settlement_amount'];

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['id']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_sn']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['suppliers_name']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_month']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['start_time']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['end_time']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_time']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_amount']) . "\t";

        echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['account_settlement_status'][$value['settlement_status']]) . "\t";

        echo "\n";

    }

    echo "\t\t\t\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', '金额合计') . "\t";

    echo hhs_iconv(EC_CHARSET, 'GB2312', $total_settlement_amount) . "\t\n";

    exit();

} 

elseif ($_REQUEST['act'] == 'account_print') {

    $title = "结算单";

    $result = suppliers_accounts_list();

    $account = $result['result'];

    $total_settlement_amount = 0;

    foreach ($account as $key => $value) 

    {

        $total_settlement_amount += $value['settlement_amount'];

        $account[$key]['settlement_status'] = $_LANG['account_settlement_status'][$account[$key]['settlement_status']];

        // 银行账户信息

        // $sql="select * from ".$hhs->table('');

    }

    $smarty->assign('title', $title);

    $smarty->assign('account', $account);

    $smarty->assign('total_settlement_amount', $total_settlement_amount);

    $html = $smarty->fetch('account_print.htm');

    echo $html;

    exit();

} 

elseif ($_REQUEST['act'] == 'pay') 

{

    /* 检查权限 */

    admin_priv('suppliers_accounts_manage');

    $suppliers_id = empty($_REQUEST['suppliers_id']) ? 0 : $_REQUEST['suppliers_id'];

    $supplier = $db->getRow("select phone,email,suppliers_name from " . $hhs->table('suppliers') . " where suppliers_id='$suppliers_id'");

    $id = empty($_REQUEST['id']) ? 0 : $_REQUEST['id'];

    $sql = "update " . $GLOBALS['hhs']->table('suppliers_accounts') . " set settlement_status=3 where id=" . $id;

    // echo $sql;exit;

    $row = $db->query($sql);

    if ($row > 0) {

        $sql = "select content from " . $GLOBALS['hhs']->table('sms_template') . " where id=2";

        $content = $db->getOne($sql); // get_sms_template(2);

    }

    $send = $sms->send($supplier['phone'], $content, '', 1);

    $url = 'suppliers.php?act=suppliers_accounts&page=' . $_REQUEST['page'];

    hhs_header("Location: $url");

    exit();

} 

elseif ($_REQUEST['act'] == 'detail') 

{

    /* 检查权限 */

    admin_priv('suppliers_accounts');

    $suppliers_accounts_id = empty($_REQUEST['suppliers_accounts_id']) ? 0 : intval($_REQUEST['suppliers_accounts_id']);

    $sql = "select * from " . $hhs->table('suppliers_accounts') . " where id=" . $suppliers_accounts_id;

    $suppliers_account = $db->getRow($sql);

    $smarty->assign('settlement_status', $suppliers_account['settlement_status']);

    $smarty->assign('suppliers_accounts_id', $suppliers_accounts_id);

    $result = suppliers_accounts_detail_list();

    //print_r($result);

    /* 模板赋值 */

    $smarty->assign('ur_here', '商家结算明细'); // 当前导航

    $smarty->assign('action_link', array(

        'href' => 'suppliers.php?act=account_detail_download',

        'text' => '结算明细下载'

    ));

    // $smarty->assign('action_link2', array(

    //     'href' => 'suppliers.php?act=account_detail_print',

    //     'text' => '结算明细打印'

    // ));

    $smarty->assign('full_page', 1); // 翻页参数

    $smarty->assign('suppliers_accounts_detail', $result['result']);

    $smarty->assign('suppliers_accounts_id', $_REQUEST['suppliers_accounts_id']);

    $smarty->assign('filter', $result['filter']);

    $smarty->assign('record_count', $result['record_count']);

    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('total', $result['total']);

    $smarty->assign('total_order_amount', $result['total_order_amount']);

    // $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    $sql = "SELECT * FROM " . $hhs->table('settlement_action') . " WHERE settlement_id = '$suppliers_accounts_id' ORDER BY log_time DESC,action_id DESC";

    $res = $db->query($sql);

    while ($row = $db->fetchRow($res)) {

        $row['status_name'] = $_LANG['account_settlement_status'][$row['status']];

        $row['action_time'] = local_date($_CFG['time_format'], $row['log_time']);

        $act_list[] = $row;

    }

    $smarty->assign('action_list', $act_list);

    $supp_row = $db->getRow("select * from" . $hhs->table('supp_config') . " where suppliers_id =  " . $suppliers_account['suppliers_id']);

    $smarty->assign('supp_row', $supp_row);

    /* 显示模板 */

    assign_query_info();

    $smarty->display('suppliers_accounts_detail.htm');

    exit();

} elseif ($_REQUEST['act'] == 'operate_post') {

    $id = intval($_REQUEST['id']);

    $action_note = $_REQUEST['action_note'];

    $settlement_status = 0;

    if (isset($_POST['checkok'])) {

        $settlement_status = 3;

        $sql = "update " . $hhs->table('suppliers_accounts') . " set settlement_status=3 where id=" . $id;

        $db->query($sql);

    } elseif (isset($_POST['checkno'])) {

        $settlement_status = 11;

        $sql = "update " . $hhs->table('suppliers_accounts') . " set settlement_status=11 where id=" . $id;

        $db->query($sql);

    } elseif (isset($_POST['request_check_account'])) {

        $settlement_status = 4;

        $sql = "update " . $hhs->table('suppliers_accounts') . " set settlement_status=4 where id=" . $id;

        $db->query($sql);

        $sql = "select suppliers_id from " . $hhs->table('suppliers_accounts') . "  where id=" . $id;

        $suppliers_id=$db->getOne($sql);

        $sql = "select phone from " . $hhs->table('suppliers') . "  where suppliers_id=" . $suppliers_id;

        $phone=$db->getOne($sql);

        if($phone)

        {

            $content="请登录商家管理中心核对您的账户信息，谢谢。";

            // $send = $sms->send($phone, $content, '', 1);

        }

    } elseif (isset($_POST['pay'])) {

        $settlement_status = 6;

        $sql = "update " . $hhs->table('suppliers_accounts') . " set settlement_status=6 where id=" . $id;

        $db->query($sql);

    }

    if(in_array($settlement_status, array(3,4,6,11)))

    {

        $settlement_sn = $db->getOne('SELECT `settlement_sn` FROM ' .$hhs->table('suppliers_accounts') . ' where id= ' .$id);

        $user_id=$db->getOne("select user_id from " . $hhs->table('suppliers') . " as s,".$hhs->table('suppliers_accounts')." as a where a.`suppliers_id`= s.`suppliers_id` and a.`id` = " .$id );

        $wxch_order_name = 'pay_msgs';

        require_once (ROOT_PATH . 'wxch_order.php');

    }

    settlement_action($id, $action_note, '平台');

    $links[] = array(

        'text' => '返回',

        'href' => 'suppliers.php?act=detail&suppliers_accounts_id=' . $id

    );

    sys_msg('操作成功', 0, $links);

} elseif ($_REQUEST['act'] == 'settlement_return') {

    $id = intval($_REQUEST['id']);

    $sql = "select * from " . $hhs->table('suppliers_accounts_detal') . " where id=" . $id;

    $row = $db->getRow($sql);

    if (! empty($row)) {

        $sql = "update " . $hhs->table('suppliers_accounts') . " set settlement_amount=settlement_amount-" . $row['amount'] . ",commission=commission-" . $row['commission'] . " where id=" . $row['suppliers_accounts_id'];

        $db->query($sql);

        $sql = "update " . $hhs->table('order_info') . " set settlement_status=0,commission=0,suppliers_accounts_id=0 where order_id=" . $row['order_id'];

        $db->query($sql);

        $sql = "delete from " . $hhs->table('suppliers_accounts_detal') . "  where id=" . $id;

        $db->query($sql);

        hhs_header('location:suppliers.php?act=detail&suppliers_accounts_id=' . $row['suppliers_accounts_id']);

    } else {

        hhs_header('location:suppliers.php?act=suppliers_accounts');

    }

} 

elseif ($_REQUEST['act'] == 'account_detail_download') {

    $title = "结算单明细";

    $result = suppliers_accounts_detail_list();

    $account_detail = $result['result'];

    header("Content-type: application/vnd.ms-excel; charset=utf-8");

    header("Content-Disposition: attachment; filename=" . $title . ".xls");

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

    $total_amount = $total_commission = $total_money = 0;

    foreach ($account_detail as $key => $value) 

    {

        $total_amount += $value['amount'];

        $total_commission += $value['commission'];

        $total_money += $value['money'];

        /**/

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

    echo hhs_iconv(EC_CHARSET, 'GB2312', $total_money) . "\t\n";

    exit();

} 

elseif ($_REQUEST['act'] == 'account_detail_print') {

    $title = "结算单明细";

    $result = suppliers_accounts_detail_list();

    $account_detail = $result['result'];

    $total_amount = $total_commission = $total_money = 0;

    foreach ($account_detail as $key => $value) 

    {

        $total_amount += $value['amount'];

        $total_commission += $value['commission'];

        $total_money += $value['money'];

    }

    // var_dump($account_detail);exit();

    $smarty->assign('title', $title);

    $smarty->assign('account_detail', $account_detail);

    $smarty->assign('total_amount', $total_amount);

    $smarty->assign('total_commission', $total_commission);

    $smarty->assign('total_money', $total_money);

    $html = $smarty->fetch('account_detail_print.htm');

    echo $html;

    exit();

}

/* ------------------------------------------------------ */

// -- 列表页编辑名称

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_suppliers_name') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

    $sql = "SELECT suppliers_id

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_name = '$name'

            AND suppliers_id <> '$id' ";

    if ($db->getOne($sql)) 

    {

        make_json_error(sprintf($_LANG['suppliers_name_exist'], $name));

    } 

    else 

    {

        /* 保存供货商信息 */

        $sql = "UPDATE " . $hhs->table('suppliers') . "

                SET suppliers_name = '$name'

                WHERE suppliers_id = '$id'";

        if ($result = $db->query($sql)) 

        {

            /* 记日志 */

            admin_log($name, 'edit', 'suppliers');

            clear_cache_files();

            make_json_result(stripslashes($name));

        } 

        else 

        {

            make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

        }

    }

} 

elseif ($_REQUEST['act'] == 'edit_delivery_score') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

	/* 保存供货商信息 */

	$sql = "UPDATE " . $hhs->table('suppliers') . "

			SET delivery_score = '$name'

			WHERE suppliers_id = '$id'";

    if ($result = $db->query($sql)) 

    {

        /* 记日志 */

        admin_log('评分', 'edit', 'suppliers');

        clear_cache_files();

        make_json_result(stripslashes($name));

    } 

    else 

    {

        make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

    }

} 

elseif ($_REQUEST['act'] == 'edit_service_score') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

	/* 保存供货商信息 */

	$sql = "UPDATE " . $hhs->table('suppliers') . "

			SET service_score = '$name'

			WHERE suppliers_id = '$id'";

    if ($result = $db->query($sql)) 

    {

        /* 记日志 */

        admin_log('评分', 'edit', 'suppliers');

        clear_cache_files();

        make_json_result(stripslashes($name));

    } 

    else 

    {

        make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

    }

} 

elseif ($_REQUEST['act'] == 'edit_description_score') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

	/* 保存供货商信息 */

	$sql = "UPDATE " . $hhs->table('suppliers') . "

			SET description_score = '$name'

			WHERE suppliers_id = '$id'";

    if ($result = $db->query($sql)) 

    {

        /* 记日志 */

        admin_log('评分', 'edit', 'suppliers');

        clear_cache_files();

        make_json_result(stripslashes($name));

    } 

    else 

    {

        make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

    }

} 

elseif ($_REQUEST['act'] == 'edit_comprehensive_score') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

	/* 保存供货商信息 */

	$sql = "UPDATE " . $hhs->table('suppliers') . "

			SET comprehensive_score = '$name'

			WHERE suppliers_id = '$id'";

    if ($result = $db->query($sql)) 

    {

        /* 记日志 */

        admin_log('评分', 'edit', 'suppliers');

        clear_cache_files();

        make_json_result(stripslashes($name));

    } 

    else 

    {

        make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

    }

} 

elseif ($_REQUEST['act'] == 'edit_suppliers_sort_order') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_POST['id']);

    $name = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */

	/* 保存供货商信息 */

	$sql = "UPDATE " . $hhs->table('suppliers') . "

			SET sort_order = '$name'

			WHERE suppliers_id = '$id'";

    if ($result = $db->query($sql)) 

    {

        /* 记日志 */

        admin_log('编辑排序', 'edit', 'suppliers');

        clear_cache_files();

        make_json_result(stripslashes($name));

    } 

    else 

    {

        make_json_result(sprintf($_LANG['agency_edit_fail'], $name));

    }

}

/* ------------------------------------------------------ */

// -- 删除供货商

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove') 

{

    check_authz_json('suppliers_remove');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT *

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id']) 

    {

        @unlink(ROOT_PATH . $suppliers['supp_logo']);

        @unlink(ROOT_PATH . $suppliers['supp_banner']);

        @unlink(ROOT_PATH . $suppliers['business_scope']);

        @unlink(ROOT_PATH . $suppliers['certificate']);

        @unlink(ROOT_PATH . $suppliers['business_license']);

        @unlink(ROOT_PATH . $suppliers['cards']);

        $sql = "DELETE FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

        $db->query($sql);

        $sql = "DELETE FROM " . $hhs->table('suppliers_accounts') . "

            WHERE suppliers_id = '$id'";

        $db->query($sql);

        $sql = "DELETE FROM " . $hhs->table('suppliers_accounts_apply') . "

            WHERE suppliers_id = '$id'";

        $db->query($sql);

        /*

        $sql = "DELETE FROM " . $hhs->table('suppliers_companys') . "

            WHERE suppliers_id = '$id'";

        $db->query($sql);

        $suppliers_factoryauthorized = $db->getAll("select * from " . $hhs->table('suppliers_factoryauthorized') . " where supp_id='$id'");

        foreach ($suppliers_factoryauthorized as $idx => $v) 

        {

            @unlink(ROOT_PATH . $v['pic']);

        }

        $sql = "DELETE FROM " . $hhs->table('suppliers_factoryauthorized') . "

            WHERE supp_id = '$id'";

        $db->query($sql);

        */

        // $sql = "DELETE FROM " . $hhs->table('supp_account') . "

        // WHERE suppliers_id = '$id'";

        //$db->query($sql);

        // 删除开户行

        $sql = "DELETE FROM " . $hhs->table('supp_config') . "

            WHERE suppliers_id = '$id'";

        $db->query($sql);

        /*

        $supp_photo = $db->getAll("select * from " . $hhs->table('supp_photo') . " where supp_id='$id'");

        foreach ($supp_photo as $idx => $v) 

        {

            @unlink(ROOT_PATH . $v['photo_file']);

        }

        $sql = "DELETE FROM " . $hhs->table('supp_photo') . "

            WHERE supp_id = '$id'";

        $db->query($sql);

        $sql = "DELETE FROM " . $hhs->table('supp_pic_category') . "

            WHERE  	suppliers_id = '$id'";

        $db->query($sql);

        $supp_pic_list = $db->getAll("select * from " . $hhs->table('supp_pic_list') . " where suppliers_id='$id'");

        foreach ($supp_pic_list as $idx => $v) 

        {

            @unlink(ROOT_PATH . $v['pic']);

        }

        $sql = "DELETE FROM " . $hhs->table('supp_pic_list') . "

            WHERE  	suppliers_id = '$id'";

        $db->query($sql);

        $sql = "DELETE FROM " . $hhs->table('supp_site') . "

            WHERE  	supp_id = '$id'";

        $db->query($sql);

        */

        // 删除商家信息通知

        //$content = '尊敬的商家，您的店铺已被强制删除，如有疑问，请致电客服' . $_CFG['service_phone'];

        // 发邮箱通知

        //send_mail($_CFG['shop_name'] . $suppliers['suppliers_name'], $suppliers['email'], '店铺删除通知', $content, 1);

        // 发短信通知

        //$send = $sms->send($suppliers['phone'], $content, '', 1);

        /* 删除管理员、发货单关联、退货单关联和订单关联的供货商 */

        /*

        $table_array = array(

            'admin_user',

            'delivery_order',

            'back_order'

        );

        foreach ($table_array as $value) 

        {

            $sql = "DELETE FROM " . $hhs->table($value) . " WHERE suppliers_id = '$id'";

            $db->query($sql, 'SILENT');

        }*/

        /* 记日志 */

        admin_log($suppliers['suppliers_name'], 'remove', 'suppliers');

        /* 清除缓存 */

        clear_cache_files();

    }

    $url = 'suppliers.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    hhs_header("Location: $url\n");

    exit();

}

/* ------------------------------------------------------ */

// -- 修改供货商状态

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'is_check') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT suppliers_id, is_check

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id']) 

    {

        $_suppliers['is_check'] = empty($suppliers['is_check']) ? 1 : 0;

        $db->autoExecute($hhs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");

        clear_cache_files();

        make_json_result($_suppliers['is_check']);

    }

    exit();

} 

elseif ($_REQUEST['act'] == 'toggle_top') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT suppliers_id, is_top

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id']) 

    {

        $_suppliers['is_top'] = empty($suppliers['is_top']) ? 1 : 0;

        $db->autoExecute($hhs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");

        clear_cache_files();

        make_json_result($_suppliers['is_top']);

    }

    exit();

} 

elseif ($_REQUEST['act'] == 'toggle_oneshow') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT suppliers_id, is_oneshow

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id']) 

    {

        $_suppliers['is_oneshow'] = empty($suppliers['is_oneshow']) ? 1 : 0;

        $db->autoExecute($hhs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");

        clear_cache_files();

        make_json_result($_suppliers['is_oneshow']);

    }

    exit();

} 

elseif ($_REQUEST['act'] == 'toggle_twoshow') 

{

    check_authz_json('suppliers_list_manage');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT suppliers_id, is_twoshow

            FROM " . $hhs->table('suppliers') . "

            WHERE suppliers_id = '$id'";

    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id']) 

    {

        $_suppliers['is_twoshow'] = empty($suppliers['is_twoshow']) ? 1 : 0;

        $db->autoExecute($hhs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");

        clear_cache_files();

        make_json_result($_suppliers['is_twoshow']);

    }

    exit();

}

/* ------------------------------------------------------ */

// -- 批量操作

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch') 

{

    /* 取得要操作的记录编号 */

    if (empty($_POST['checkboxes'])) 

    {

        sys_msg($_LANG['no_record_selected']);

    } 

    else 

    {

        /* 检查权限 */

        admin_priv('suppliers_list_manage');

        $ids = $_POST['checkboxes'];

        if (isset($_POST['remove'])) 

        {

            $sql = "SELECT *

                    FROM " . $hhs->table('suppliers') . "

                    WHERE suppliers_id " . db_create_in($ids);

            $suppliers = $db->getAll($sql);

            foreach ($suppliers as $key => $value) 

            {

                /* 判断供货商是否存在订单 */

                $sql = "SELECT COUNT(*)

                        FROM " . $hhs->table('order_info') . "AS O, " . $hhs->table('order_goods') . " AS OG, " . $hhs->table('goods') . " AS G

                        WHERE O.order_id = OG.order_id

                        AND OG.goods_id = G.goods_id

                        AND G.suppliers_id = '" . $value['suppliers_id'] . "'";

                $order_exists = $db->getOne($sql, TRUE);

                if ($order_exists > 0) 

                {

                    unset($suppliers[$key]);

                }

                /* 判断供货商是否存在商品 */

                $sql = "SELECT COUNT(*)

                        FROM " . $hhs->table('goods') . "AS G

                        WHERE G.suppliers_id = '" . $value['suppliers_id'] . "'";

                $goods_exists = $db->getOne($sql, TRUE);

                if ($goods_exists > 0) 

                {

                    unset($suppliers[$key]);

                }

                @unlink(ROOT_PATH . $value['supp_logo']);

                @unlink(ROOT_PATH . $value['supp_banner']);

                @unlink(ROOT_PATH . $value['business_scope']);

                @unlink(ROOT_PATH . $value['certificate']);

                @unlink(ROOT_PATH . $value['business_license']);

                @unlink(ROOT_PATH . $value['cards']);

                $sql = "DELETE FROM " . $hhs->table('suppliers_accounts') . "

					WHERE suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

                $sql = "DELETE FROM " . $hhs->table('suppliers_accounts_apply') . "

					WHERE suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

               // $sql = "DELETE FROM " . $hhs->table('suppliers_companys') . "

					//WHERE suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

     //            $suppliers_factoryauthorized = $db->getAll("select * from " . $hhs->table('suppliers_factoryauthorized') . " where supp_id='$value[suppliers_id]'");

     //            foreach ($suppliers_factoryauthorized as $idx => $v) 

     //            {

     //                @unlink(ROOT_PATH . $v['pic']);

     //            }

     //            $sql = "DELETE FROM " . $hhs->table('suppliers_factoryauthorized') . "

					// WHERE supp_id = '$value[suppliers_id]'";

     //            $db->query($sql);

                // $sql = "DELETE FROM " . $hhs->table('supp_account') . "

                // WHERE suppliers_id = '$value[suppliers_id]'";

                // $db->query($sql);

                $sql = "DELETE FROM " . $hhs->table('supp_config') . "

					WHERE suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

                $supp_photo = $db->getAll("select * from " . $hhs->table('supp_photo') . " where supp_id='$value[suppliers_id]'");

                foreach ($supp_photo as $idx => $v) 

                {

                    @unlink(ROOT_PATH . $v['photo_file']);

                }

                $sql = "DELETE FROM " . $hhs->table('supp_photo') . "

					WHERE supp_id = '$value[suppliers_id]'";

                $db->query($sql);

                $sql = "DELETE FROM " . $hhs->table('supp_pic_category') . "

					WHERE  	suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

                $supp_pic_list = $db->getAll("select * from " . $hhs->table('supp_pic_list') . " where suppliers_id='$value[suppliers_id]'");

                foreach ($supp_pic_list as $idx => $v) 

                {

                    @unlink(ROOT_PATH . $v['pic']);

                }

                $sql = "DELETE FROM " . $hhs->table('supp_pic_list') . "

					WHERE  	suppliers_id = '$value[suppliers_id]'";

                $db->query($sql);

                $sql = "DELETE FROM " . $hhs->table('supp_site') . "

					WHERE  	supp_id = '$value[suppliers_id]'";

                $db->query($sql);

            }

            if (empty($suppliers)) 

            {

                sys_msg($_LANG['batch_drop_no']);

            }

            $suppliers_names = '';

            foreach ($suppliers as $value) 

            {

                $suppliers_names .= $value['suppliers_name'] . '|';

            }

            $sql = "DELETE FROM " . $hhs->table('suppliers') . "

                WHERE suppliers_id " . db_create_in($ids);

            $db->query($sql);

            /* 更新管理员、发货单关联、退货单关联和订单关联的供货商 */

            $table_array = array(

                'admin_user',

                'delivery_order',

                'back_order'

            );

            foreach ($table_array as $value) 

            {

                $sql = "DELETE FROM " . $hhs->table($value) . " WHERE suppliers_id " . db_create_in($ids) . " ";

                $db->query($sql, 'SILENT');

            }

            /* 记日志 */

            foreach ($suppliers as $value) 

            {

                $suppliers_names .= $value['suppliers_name'] . '|';

            }

            admin_log($suppliers_names, 'remove', 'suppliers');

            /* 清除缓存 */

            clear_cache_files();

            sys_msg($_LANG['batch_drop_ok']);

        }

    }

}

/* ------------------------------------------------------ */

// -- 添加、编辑供货商

/* ------------------------------------------------------ */

elseif (in_array($_REQUEST['act'], array('add','edit'))) 

{

    /* 检查权限 */

    admin_priv('suppliers_add_manage');

    $hangye = $db->getAll("select id,name from " . $hhs->table('hangye'));

    $smarty->assign('hangye', $hangye);

    if ($_REQUEST['act'] == 'add') 

    {

            $smarty->assign('cities',    get_sitelists());

       // $smarty->assign('site_list', get_sitelists());

       // $smarty->assign('companys_list', get_company_lists());

        $suppliers = array();

        create_html_editor('suppliers_desc', '');

        /* 取得所有管理员， */

        /* 标注哪些是该供货商的('this')，哪些是空闲的('free')，哪些是别的供货商的('other') */

        /* 排除是办事处的管理员 */

        $sql = "SELECT user_id, user_name, CASE

                WHEN suppliers_id = 0 THEN 'free'

                ELSE 'other' END AS type

                FROM " . $hhs->table('admin_user') . "

                WHERE agency_id = 0

                AND action_list <> 'all'";

        $suppliers['admin_list'] = $db->getAll($sql);

        $smarty->assign('ur_here', $_LANG['add_suppliers']);

        $smarty->assign('action_link', array(

            'href' => 'suppliers.php?act=list',

            'text' => $_LANG['suppliers_list']

        ));

        $smarty->assign('form_action', 'insert');

        $suppliers['local_strtotime'] = local_date('Y-m-d', $suppliers['local_strtotime']);

        $smarty->assign('suppliers', $suppliers);

        assign_query_info();

        $smarty->display('suppliers_info.htm');

    } 

    elseif ($_REQUEST['act'] == 'edit') 

    {

        $suppliers = array();

        /* 取得供货商信息 */

        $id = $_REQUEST['id'];

        $sql = "SELECT * FROM " . $hhs->table('suppliers') . " WHERE suppliers_id = '$id'";

        $suppliers = $db->getRow($sql);

        if (count($suppliers) <= 0) 

        {

            sys_msg('suppliers does not exist');

        }

		$smarty->assign('cities',    get_sitelists());

   		$smarty->assign('district_list',    get_regions(3,$suppliers['city_id']));  

        create_html_editor('suppliers_desc', $suppliers['suppliers_desc']);

        /*

        $sitelist = get_sitelists();;

        $artice_site = $db->getAll("select * from " . $hhs->table('supp_site') . " where  supp_id='$_REQUEST[id]'");

        foreach ($artice_site as $idx => $value) 

        {

            $new_sitelist[] = $value['site_id'];

        }

        $shtml = '';

        $checked_type = array();

        foreach ($sitelist as $id => $v) 

        {

            if (@in_array($v['id'], $new_sitelist)) 

            {

                $checked = "checked=checked";

            } 

            else 

            {

                $checked = "";

            }

            $shtml .= "<input value=" . $v['id'] . " type=\"checkbox\" " . $checked . " name=\"site_id[]\">" . $v['name'] . "";

        }

        $smarty->assign('site_html', $shtml);

        $company_lists = get_company_lists();;

        $artice_site = $db->getAll("select * from " . $hhs->table('suppliers_companys') . " where  suppliers_id='$_REQUEST[id]'");

        foreach ($artice_site as $idx => $value) 

        {

            $new_company_lists[] = $value['companys_id'];

        }

        $chtml = '';

        $checked_type = array();

        foreach ($company_lists as $id => $v) 

        {

            if (@in_array($v['companys_id'], $new_company_lists)) 

            {

                $checked = "checked=checked";

            } 

            else 

            {

                $checked = "";

            }

            $chtml .= "<input value=" . $v['companys_id'] . " type=\"checkbox\" " . $checked . " name=\"companys_id[]\">" . $v['companys_name'] . "";

        }

        $smarty->assign('company_html', $chtml);

        */

        $supp_row = $db->getRow("select * from" . $hhs->table('supp_config') . "where suppliers_id = '" . $suppliers['suppliers_id'] . "' ");

        $smarty->assign('supp_row', $supp_row);

        // $suppliers['province_id'] = $db->getOne("select parent_id from ".$hhs->table('region')." where region_id='$suppliers[city_id]'");

        $photo_list = $db->getAll("SELECT * FROM " . $hhs->table('supp_photo') . "where supp_id = " . $_REQUEST['id'] . " order by photo_id asc ");

        $smarty->assign("photo_list", $photo_list);

        $smarty->assign('provinces', get_regions(1, 1));

        //$smarty->assign('cities', get_sitelists($suppliers['province_id']));

        $smarty->assign('district_list', get_regions(3, $suppliers['city_id']));

        $smarty->assign('ur_here', $_LANG['edit_suppliers']);

        $smarty->assign('action_link', array(

            'href' => 'suppliers.php?act=list',

            'text' => $_LANG['suppliers_list']

        ));

        $smarty->assign('form_action', 'update');

        $smarty->assign('suppliers', $suppliers);

        $smarty->assign('page', $_REQUEST['page']);

        // print_r($suppliers);

        assign_query_info();

        $smarty->display('suppliers_info.htm');

    }

}

/* ------------------------------------------------------ */

// -- 删除图片

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'drop_image') 

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

elseif ($_REQUEST['act'] == 'check') 

{

    $is_check = $_REQUEST['is_check'];

    $id = $_REQUEST['id'];

    $smarty->assign('page', $_REQUEST['page']);

    $smarty->assign('id', $id);

    $check_desc = $db->getOne("select check_desc from " . $hhs->table('suppliers') . " where  suppliers_id='$id'");

    $smarty->assign('check_desc', $check_desc);

    $smarty->assign('is_check', $is_check);

    $smarty->display('suppliers_check.htm');

} 

elseif ($_REQUEST['act'] == 'check_act') 

{

    $is_check = intval($_REQUEST['is_check']);

    $is_sms = $_REQUEST['is_sms'];

    $is_email = $_REQUEST['is_email'];

    $id = $_REQUEST['id'];

    $check_desc = $_REQUEST['check_desc'];

    $rows = $db->getRow("select phone,email,suppliers_name,user_id from " . $hhs->table('suppliers') . " where suppliers_id='$id'");

    if($rows['user_id'] && in_array($is_check, array(0,1,2))){

        $user_id=$rows['user_id'];

		$uname = $db->getOne("select uname from " . $hhs->table('users') . " where user_id='$user_id'");

        $wxch_order_name='send_checked_result';

        $shareurl = $_SERVER['SERVER_NAME'].'/business/';

        include_once(ROOT_PATH . 'wxch_order.php');

        unset($user_id);

    } 

    $sql = $db->query("update " . $hhs->table('suppliers') . " set is_check='$is_check',check_desc='$check_desc' where suppliers_id='$id'");

    if (! empty($check_desc)) 

    {

        $desc = " 备注信息：" . $check_desc;

    }

	if ($is_check == 0) 

	{

	  $content = '您的店铺正在审核中，请耐心等待!' . $desc;

	}

	if ($is_check == 1) 

	{

		$content = '恭喜，您提交的店铺申请已审核通过，请<a href="' . $hhs->url() . 'business/">立即登录</a>上传商品!' . $desc;

	}

	if ($is_check == 2) 

	{

	   $content = '您的申请未通过审核，请致电客服 ' . $_CFG['service_phone'] . '' . $desc . '';

	}

	send_mail($_CFG['shop_name'] . $rows['suppliers_name'], $rows['email'], '店铺审核通知', $content, 1);

    if ($is_sms == 'on') 

    {

        if ($is_check == 0) 

        {

            $content = '尊敬的商家，您的店铺正在审核中，请您耐心等待！' . $desc;

        }

        if ($is_check == 1) 

        {

            $content = '尊敬的商家，您提交的店铺申请已审核通过，请您尽快登录时上传商品！如有疑问，请致电客服' . $_CFG['service_phone'] . $desc;

        }

        if ($is_check == 2) 

        {

            $content = '尊敬的商家，您的申请未通过审核，请致电客服' . $_CFG['service_phone'] . '' . $desc;

        }

        $send = $sms->send($rows['phone'], $content, '', 1);

    }

    $links = array(

        array(

            'href' => 'suppliers.php?act=list&page=' . $_REQUEST['page'],

            'text' => '返回列表'

        )

    );

    sys_msg('操作成功', 0, $links);

}

elseif($_REQUEST['act'] =='ad_del')

{

    admin_priv('suppliers_list_manage');

	$suppliers_id = $_REQUEST['suppliers_id'];

	$photo_id =  $_REQUEST['photo_id'];

	$photo_file = $_REQUEST['photo_file'];

	$sql = $db->query("delete from ".$hhs->table('supp_photo')." where photo_id='$photo_id'");

	unlink(ROOT_PATH . $photo_file);

    $links = array(

        array(

            'href' => 'suppliers.php?act=ad&suppliers_id=' . $suppliers_id,

            'text' => '返回列表'

        )

    );

    sys_msg('操作成功', 0, $links);

}

/* ------------------------------------------------------ */

// -- 商家广告

/* ------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'ad') 

{

	$suppliers_id = $_REQUEST['suppliers_id'];

    /* 检查权限 */

    admin_priv('suppliers_list_manage');

    /* 查询 */

    //$smarty->assign('ranks', get_suppliers_type_list());

    /* 模板赋值 */

    $smarty->assign('ur_here', '商家广告'); // 当前导航

    $smarty->assign('action_link', array(

        'href' => 'suppliers.php?act=add',

        'text' => '返回列表'

    ));

    $list = $db->getAll("select * from ".$hhs->table('supp_photo')." where supp_id='$suppliers_id'");

	$smarty->assign('supp_photo',$list);

    /* 显示模板 */

    assign_query_info();

    $smarty->display('suppliers_ad.htm');	

}

/* ------------------------------------------------------ */

// -- 提交添加、编辑供货商

/* ------------------------------------------------------ */

elseif (in_array($_REQUEST['act'], array(

    'insert',

    'update'

))) 

{

    /* 检查权限 */

    admin_priv('suppliers_add_manage');

    // print_r($_POST);die;

    $business_license = $image->upload_image($_FILES['business_license'], 'business_file');

    $cards = $image->upload_image($_FILES['cards'], 'business_file');

    $supp_banner = $image->upload_image($_FILES['supp_banner']);

    $business_scope = $image->upload_image($_FILES['business_scope'], 'business_file');

    $certificate = $image->upload_image($_FILES['certificate'], 'business_file');

    /**

     * percentage

     * add by luo 

     */

    if ($_REQUEST['act'] == 'insert') 

    {

        $supp_logo = $image->upload_image($_FILES['supp_logo']);

        /* 提交值 */

        $password = md5($_POST['password']);

        $suppliers = array(

            'suppliers_name' => trim($_POST['suppliers_name']),

            'business_scope' => trim($_POST['business_scope']),

            'suppliers_desc' => trim($_POST['suppliers_desc']),

            'province_id' => trim($_POST['province_id']),

            'city_id' => trim($_POST['city_id']),

            'district_id' => trim($_POST['district_id']),

            'suppliersID' => trim($_POST['suppliersID']),

            'url_name' => trim($_POST['url_name']),

            'qq' => $_POST['qq'],

            'show_photo' => $show_photo,

            'supp_logo' => $supp_logo,

            'supp_banner' => $supp_banner,

            'business_scope' => $business_scope,

            'certificate' => $certificate,

            'business_license' => $business_license,

            'cards' => $cards,

            'user_name' => $_POST['user_name'],

            'recommend_person' => $_POST['recommend_person'],

            'password' => $password,

            'show_type' => $show_type,

            'address' => trim($_POST['address']),

            'email' => trim($_POST['email']),

            'real_name' => trim($_POST['real_name']),

            'phone1' => trim($_POST['phone1']),

            'phone' => trim($_POST['phone']),

            'rank_id' => $_POST['rank_id'],

			'longitude' => $_POST['longitude'],

			'latitude' => $_POST['latitude'],

            'percentage' => floatval($_POST['percentage']),

            'rate_1' => floatval($_POST['rate_1']),

            'rate_2' => floatval($_POST['rate_2']),

			'rate_3' => floatval($_POST['rate_3']),

            'hangye_id' => intval($_POST['hangye_id']),

            'announcement' => $_POST['announcement'],

			'add_time' => gmtime(),

            'parent_id' => 0

        )

        ;

        /* 判断名称是否重复 */

        $sql = "SELECT suppliers_id

                FROM " . $hhs->table('suppliers') . "

                WHERE suppliers_name = '" . $suppliers['suppliers_name'] . "' ";

        if ($db->getOne($sql)) 

        {

            sys_msg($_LANG['suppliers_name_exist']);

        }

        $db->autoExecute($hhs->table('suppliers'), $suppliers, 'INSERT');

        $suppliers['suppliers_id'] = $db->insert_id();

//        foreach ($_POST['site_id'] as $key => $value) 

//

//        {

//            

//            $db->query("insert into " . $hhs->table('supp_site') . " (site_id,supp_id) values ('$value','$suppliers[suppliers_id]')");

//        }

//        

//        foreach ($_POST['companys_id'] as $key => $value) 

//

//        {

//            

//            $db->query("insert into " . $hhs->table('suppliers_companys') . " (companys_id,suppliers_id) values ('$value','$suppliers[suppliers_id]')");

//        }

        // if (isset($_POST['admins']))

        // {

        // $sql = "UPDATE " . $hhs->table('admin_user') . " SET suppliers_id = '" . $suppliers['suppliers_id'] . "', action_list = '" . SUPPLIERS_ACTION_LIST . "' WHERE user_id " . db_create_in($_POST['admins']);

        // $db->query($sql);

        // }

        // 建一个文件夹并修改权限

        $dir = ROOT_PATH . '/business/uploads/' . $suppliers['suppliers_id'] . '/';

        //

        is_dir($dir) or mkdir($dir, 0777);

        //

        chmod($dir, 0777);

        /* 记日志 */

        admin_log($suppliers['suppliers_name'], 'add', 'suppliers');

        /* 清除缓存 */

        clear_cache_files();

        /* 提示信息 */

        $links = array(

            array(

                'href' => 'suppliers.php?act=add',

                'text' => $_LANG['continue_add_suppliers']

            ),

            array(

                'href' => 'suppliers.php?act=list&page=' . $_POST['page'],

                'text' => $_LANG['back_suppliers_list']

            )

        )

        ;

        sys_msg($_LANG['add_suppliers_ok'], 0, $links);

    }

    if ($_REQUEST['act'] == 'update') 

    {

        $suppliers_id = $_POST['id'];

        $supp_logo = $_FILES['supp_logo'];

        $supp_logo = $image->upload_image($supp_logo);

        if ($_FILES['supp_logo']['error'] != 4 && $_FILES['supp_logo']['size'] != 0) {

            /* 如果 file_url 跟以前不一样，且原来的文件是本地文件，删除原来的文件 */

            $sql = "SELECT supp_logo FROM " . $hhs->table('suppliers') . " WHERE suppliers_id = " . $suppliers_id;

            $old_url = $db->getOne($sql);

            // echo $old_url;die;

            if ($old_url != '' && $old_url != $supp_logo && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false) 

            {

                @unlink(ROOT_PATH . $old_url);

            }

        }

        $site_ids = join(",", $_POST['site_id']);

        /* 提交值 */

        $suppliers = array(

            'id' => trim($_POST['id'])

        );

        $suppliers['new'] = array(

            'suppliers_name' => trim($_POST['suppliers_name']),

            'province_id' => trim($_POST['province_id']),

            'city_id' => trim($_POST['city_id']),

            'district_id' => trim($_POST['district_id']),

            'user_name' => $_POST['user_name'],

            'suppliersID' => trim($_POST['suppliersID']),

            'url_name' => trim($_POST['url_name']),

            'recommend_person' => trim($_POST['recommend_person']),

            'address' => trim($_POST['address']),

            'email' => trim($_POST['email']),

            'real_name' => trim($_POST['real_name']),

            'phone' => trim($_POST['phone']),

            'phone1' => trim($_POST['phone1']),

            'site_id' => $site_ids,

            'show_type' => $_POST['show_type'],

            'qq' => $_POST['qq'],

			'longitude' => $_POST['longitude'],

			'latitude' => $_POST['latitude'],

            'percentage' => floatval($_POST['percentage']),//佣金比例

            'rate_1' => floatval($_POST['rate_1']),//1佣金比例

            'rate_2' => floatval($_POST['rate_2']),//2佣金比例

            'rate_3' => floatval($_POST['rate_3']),//3佣金比例

            'hangye_id' => intval($_POST['hangye_id']),

            'suppliers_desc' => trim($_POST['suppliers_desc']),

            'rank_id' => $_POST['rank_id'],

            'announcement' => $_POST['announcement']

        )

        ;

        if ($business_license) 

        {

            $suppliers['new']['business_license'] = $business_license;

        }

        if ($cards) 

        {

            $suppliers['new']['cards'] = $cards;

        }

        if ($supp_logo) 

        {

            $suppliers['new']['supp_logo'] = $supp_logo;

        }

        if ($supp_banner) 

        {

            $suppliers['new']['supp_banner'] = $supp_banner;

        }

        if ($business_scope) 

        {

            $suppliers['new']['business_scope'] = $business_scope;

        }

        if ($certificate) 

        {

            $suppliers['new']['certificate'] = $certificate;

        }

        /* 取得供货商信息 */

        $sql = "SELECT * FROM " . $hhs->table('suppliers') . " WHERE suppliers_id = '" . $suppliers['id'] . "'";

        $suppliers['old'] = $db->getRow($sql);

        if (empty($suppliers['old']['suppliers_id'])) 

        {

            sys_msg('suppliers does not exist');

        }

        /* 判断名称是否重复 */

        $sql = "SELECT suppliers_id

                FROM " . $hhs->table('suppliers') . "

                WHERE suppliers_name = '" . $suppliers['new']['suppliers_name'] . "'

                AND suppliers_id <> '" . $suppliers['id'] . "'";

        if ($db->getOne($sql)) 

        {

            sys_msg($_LANG['suppliers_name_exist']);

        }

        // print_r($suppliers['new']);exit;

        if ($_POST['password']) 

        {

            $suppliers['new']['password'] = md5($_POST['password']);

        }

        // print_r($suppliers['new']);die;

        /* 保存供货商信息 */

        $db->autoExecute($hhs->table('suppliers'), $suppliers['new'], 'UPDATE', "suppliers_id = '" . $suppliers['id'] . "'");

       // $sql = $db->query("delete from " . $hhs->table('supp_site') . " where supp_id='$suppliers[id]'");

     //   

//        foreach ($_POST['site_id'] as $keys => $values) 

//

//        {

//            

//            $db->query("insert into " . $hhs->table('supp_site') . " (supp_id,site_id) values ('$suppliers[id]','$values')");

//        }

        //$sql = $db->query("delete from " . $hhs->table('suppliers_companys') . " where suppliers_id='$suppliers[id]'");

       // foreach ($_POST['companys_id'] as $key => $value) 

//

//        {

//            

//            $db->query("insert into " . $hhs->table('suppliers_companys') . " (suppliers_id,companys_id) values ('$suppliers[id]','$value')");

//        }

        // 更新开户行

        $bank_name = $_REQUEST['bank_name'];

        $bank_p_name = $_REQUEST['bank_p_name'];

        $bank_account = $_REQUEST['bank_account'];

        $supp_isset = $db->getOne("select id from" . $hhs->table('supp_config') . "where suppliers_id = '" . $suppliers['id'] . "' ");

        if ($supp_isset) 

        {

            // 存在则更新

            $sql = 'UPDATE ' . $hhs->table('supp_config') . " SET `bank_name`='$bank_name',`bank_p_name`='$bank_p_name',bank_account='$bank_account',bank_password='$bank_password'  WHERE `id`='" . $supp_isset . "'";

            $res = $db->query($sql);

        } 

        else 

        {

            // 不存在则添加

            $sql = "INSERT INTO " . $hhs->table('supp_config') . " (`suppliers_id`,`bank_name`, `bank_p_name`, `bank_account`, `bank_password`) VALUES ('$suppliers_id','$bank_name', '$bank_p_name','$bank_account','$bank_password')";

            $res = $db->query($sql);

        }

        /* 记日志 */

        admin_log($suppliers['old']['suppliers_name'], 'edit', 'suppliers');

        /* 清除缓存 */

        clear_cache_files();

        /* 提示信息 */

        $links[] = array(

            'href' => 'suppliers.php?act=list&page=' . $_POST['page'],

            'text' => $_LANG['back_suppliers_list']

        );

        sys_msg($_LANG['edit_suppliers_ok'], 0, $links);

    }

}

function suppliers_accounts_list()

{

    $result = get_filter();

    if ($result === false) 

    {

        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);

        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['settlement_sn'] = empty($_REQUEST['settlement_sn']) ? '' : trim($_REQUEST['settlement_sn']);

        $filter['suppliers_id'] = empty($_REQUEST['suppliers_id']) ? 0 : intval($_REQUEST['suppliers_id']);

        $filter['suppliers_name'] = empty($_REQUEST['suppliers_name']) ? '' : trim($_REQUEST['suppliers_name']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);

        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);

        $filter['add_month'] = empty($_REQUEST['add_month']) ? '' : trim($_REQUEST['add_month']);

        $filter['settlement_status'] = empty($_REQUEST['settlement_status']) ? '' : intval($_REQUEST['settlement_status']);

        $where = 'WHERE 1 ';

        /* 分页大小 */

        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if ($filter['settlement_sn'] != '') 

        {

            $where .= " and sa.settlement_sn = '" . $filter['settlement_sn'] . "'";

        }

        if ($filter['suppliers_name'] != '') 

        {

            $sql = "select suppliers_id from " . $GLOBALS['hhs']->table('suppliers') . " where suppliers_name like '%" . $filter['suppliers_name'] . "%'";

            $filter['suppliers_id'] = $GLOBALS['db']->getOne($sql);

            $where .= " and sa.suppliers_id = '" . $filter['suppliers_id'] . "'";

        }

        if ($filter['suppliers_id'] != '') 

        {

            $where .= " and sa.suppliers_id = '" . $filter['suppliers_id'] . "'";

        }

        /*

        if ($filter['start_time'] != '') 

        {

            $where .= " and sa.start_time >= '" . local_strtotime($filter['start_time']) . "'";

        }

        if ($filter['end_time'] != '') 

        {

            $where .= " and sa.end_time <= '" . local_strtotime($filter['end_time']) . "'";

        }*/

        if ($filter['start_time'] != '')

        {

            $where .= " and sa.add_time >= '" . local_strtotime($filter['start_time']) . "'";

        }

        if ($filter['end_time'] != '')

        {

            $where .= " and sa.add_time <= '" . local_strtotime($filter['end_time']) . "'";

        }

        if ($filter['add_month'] != '') 

        {

            $where .= " and sa.add_month = '" . $filter['add_month'] . "'";

        }

        if ($filter['settlement_status'] != '') 

        {

            $where .= " and sa.settlement_status = " . $filter['settlement_status'];

        }

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_REQUEST['page_size']);

        } 

        elseif (isset($_COOKIE['HHSCP']['page_size']) && intval($_COOKIE['HHSCP']['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_COOKIE['HHSCP']['page_size']);

        } 

        else 

        {

            $filter['page_size'] = 15;

        }

        // echo $where;

        /* 记录总数 */

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . ' as sa ' . $sql1 . $where;

        // echo $sql;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */

        $sql = "SELECT sa.*,(sa.settlement_amount-sa.commission) as money, " . 

        "(select suppliers_name from " . $GLOBALS['hhs']->table("suppliers") . " where suppliers_id=sa.suppliers_id) as suppliers_name " . 

        " FROM " . $GLOBALS['hhs']->table("suppliers_accounts") . " as sa " . 

        $where . " ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order'] . "	

		LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);

    } 

    else 

    {

        $sql = $result['sql'];

        $filter = $result['filter'];

    }

    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row as $idx => $value) 

    {

        $row[$idx]['add_time'] = local_date('Y-m-d H:i:s', $value['add_time']);

        $row[$idx]['add_month'] = substr($value['add_month'], 0, 4) . "-" . substr($value['add_month'], 4, 2);

        $row[$idx]['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['start_time']);

        $row[$idx]['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['end_time']);

    }

    $arr = array(

        'result' => $row,

        'filter' => $filter,

        'page_count' => $filter['page_count'],

        'record_count' => $filter['record_count']

    );

    return $arr;

}

function suppliers_accounts_detail_list()

{

    $result = get_filter();

    if ($result === false) 

    {

        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);

        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);

        $filter['suppliers_accounts_id'] = empty($_REQUEST['suppliers_accounts_id']) ? 0 : intval($_REQUEST['suppliers_accounts_id']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);

        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);

        $where = 'WHERE 1 ';

        /* 分页大小 */

        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if ($filter['order_sn'] != '') 

        {

            $where .= " and sat.order_sn = '" . $filter['order_sn'] . "'";

        }

        if ($filter['suppliers_accounts_id'] != '') 

        {

            $where .= " and sat.suppliers_accounts_id = '" . $filter['suppliers_accounts_id'] . "'";

        }

        if ($filter['start_time'] != '') 

        {

            $where .= " and sat.order_time >= '" . local_strtotime($filter['start_time']) . "'";

        }

        if ($filter['end_time'] != '') 

        {

            $where .= " and sat.order_time <= '" . local_strtotime($filter['end_time']) . "'";

        }

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_REQUEST['page_size']);

        } 

        elseif (isset($_COOKIE['HHSCP']['page_size']) && intval($_COOKIE['HHSCP']['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_COOKIE['HHSCP']['page_size']);

        } 

        else 

        {

            $filter['page_size'] = 15;

        }

        /* 记录总数 */

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts_detal') . ' as sat ' . $sql1 . $where;

        // echo $sql;exit();

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */

        $sql = "SELECT sat.*,(sat.amount-sat.commission-sat.fenxiao_money) as money,o.suppliers_id,o.consignee,o.pay_name,o.user_id " . 

        " FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat left join " .

         $GLOBALS['hhs']->table("order_info") . " as o on sat.order_id=o.order_id " .

        $where . " ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);

    } 

    else 

    {

        $sql = $result['sql'];

        $filter = $result['filter'];

    }

    $row = $GLOBALS['db']->getAll($sql);

    $total_amount = $total_commission = $total_money = 0;

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

    }

    $total = array(

        "total_amount" => $total_amount,

        "total_commission" => $total_commission,

        "total_fenxiao" => $total_fenxiao,

        "total_money" => $total_money

    )

    ;

    //总结算金额

    $rowxy = $GLOBALS['db']->getAll("SELECT sat.*,(sat.amount) as money,o.suppliers_id,o.consignee,o.pay_name,o.user_id  FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat left join " .$GLOBALS['hhs']->table("order_info") . " as o on sat.order_id=o.order_id where sat.suppliers_accounts_id = '" . $filter['suppliers_accounts_id']."'");

	foreach ($rowxy as $idx => $value) 

    {   

        $total_order_amount += $rowxy[$idx]['amount'];

		$total_order_commission += $rowxy[$idx]['commission'];

        $total_order_fenxiao += $rowxy[$idx]['fenxiao_money'];

        $total_order_money += ($rowxy[$idx]['amount']- $rowxy[$idx]['commission'] - $rowxy[$idx]['fenxiao_money']);

    }

	$total_order_amount = array(

		"total_amount" => $total_order_amount,

        "total_money" => $total_order_money

	);

    $arr = array(

        'result' => $row,

        'filter' => $filter,

        'total' => $total,

        'page_count' => $filter['page_count'],

        'total_order_amount' => $total_order_amount,

        'record_count' => $filter['record_count']

    );

    return $arr;

}

// function suppliers_accounts_detail_list()

// {

//     $result = get_filter();

//     if ($result === false)

//     {

//         $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

//         /* 过滤信息 */

//         $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);

//         $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

//         $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);

//         $filter['suppliers_accounts_id'] = empty($_REQUEST['suppliers_accounts_id']) ? 0 : intval($_REQUEST['suppliers_accounts_id']);

//         $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);

//         $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);

//         $where = 'WHERE 1 ';

//         /* 分页大小 */

//         $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

//         if ($filter['order_sn'] != '')

//         {

//             $where .= " and sat.order_sn = '" . $filter['order_sn'] . "'";

//         }

//         if ($filter['suppliers_accounts_id'] != '')

//         {

//             $where .= " and sat.suppliers_accounts_id = '" . $filter['suppliers_accounts_id'] . "'";

//         }

//         if ($filter['start_time'] != '')

//         {

//             $where .= " and sat.order_time >= '" . local_strtotime($filter['start_time']) . "'";

//         }

//         if ($filter['end_time'] != '')

//         {

//             $where .= " and sat.order_time <= '" . local_strtotime($filter['end_time']) . "'";

//         }

//         if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)

//         {

//             $filter['page_size'] = intval($_REQUEST['page_size']);

//         }

//         elseif (isset($_COOKIE['HHSCP']['page_size']) && intval($_COOKIE['HHSCP']['page_size']) > 0)

//         {

//             $filter['page_size'] = intval($_COOKIE['HHSCP']['page_size']);

//         }

//         else

//         {

//             $filter['page_size'] = 15;

//         }

//         /* 记录总数 */

//         $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts_detal') . ' as sat ' . $sql1 . $where;

//         // echo $sql;exit();

//         $filter['record_count'] = $GLOBALS['db']->getOne($sql);

//         $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

//         /* 查询 */

//         $sql = "SELECT sat.*,(sat.amount-sat.commission) as money " .

//             " FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat " .

//             $where . " ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order'];

//         // . "LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

//         set_filter($filter, $sql);

//     }

//     else

//     {

//         $sql = $result['sql'];

//         $filter = $result['filter'];

//     }

//     $row = $GLOBALS['db']->getAll($sql);

//     $total_amount = $total_commission = $total_money = 0;

//     foreach ($row as $idx => $value)

//     {

//         $row[$idx]['order_time'] = local_date('Y-m-d', $value['order_time']);

//         $total_amount += $row[$idx]['amount'];

//         $total_commission += $row[$idx]['commission'];

//         $total_money += ($row[$idx]['amount'] - $row[$idx]['commission']);

//     }

//     $total = array(

//         "total_amount" => $total_amount,

//         "total_commission" => $total_commission,

//         "total_money" => $total_money

//     )

//     ;

//     $arr = array(

//         'result' => $row,

//         'filter' => $filter,

//         'total' => $total,

//         'page_count' => $filter['page_count'],

//         'record_count' => $filter['record_count']

//     );

//     return $arr;

// }

/**

 *

 *

 *

 *

 * 获取供应商列表信息

 *

 *

 *

 *

 *

 * @access public

 *        

 *        

 *        

 * @param            

 *

 *

 *

 *

 *

 *

 *

 *

 * @return void

 *

 *

 *

 */

function suppliers_list($is_down = true)

{

    $result = get_filter();

    if ($result === false) 

    {

        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'suppliers_id' : trim($_REQUEST['sort_by']);

        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        // var_dump($filter);

        $filter['city_id'] = $_REQUEST['city_id'];

        $filter['site_id'] = ! empty($_REQUEST['site_id']) ? intval($_REQUEST['site_id']) : $_SESSION['site_id'];

        $filter['province_id'] = empty($_REQUEST['province_id']) ? '' : trim($_REQUEST['province_id']);

        $filter['rank_id'] = empty($_REQUEST['rank_id']) ? '' : trim($_REQUEST['rank_id']);

        $filter['is_check'] = isset($_REQUEST['is_check']) ? $_REQUEST['is_check'] : '';

        $filter['is_oneshow'] = empty($_REQUEST['is_oneshow']) ? '' : trim($_REQUEST['is_oneshow']);

        $filter['is_twoshow'] = empty($_REQUEST['is_twoshow']) ? '' : trim($_REQUEST['is_twoshow']);

        $filter['recommend_type_name'] = empty($_REQUEST['recommend_type_name']) ? '' : trim($_REQUEST['recommend_type_name']);

        $where = 'WHERE 1 ';

        /* 分页大小 */

        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        // if(($_REQUEST['sort_by'] =='is_oneshow' or $_REQUEST['sort_by'] =='is_twoshow')&&!$_REQUEST['page'])

        // {

        // $filter['page']=1;

        // }

        $filter['suppliers_name'] = empty($_REQUEST['suppliers_name']) ? '' : trim($_REQUEST['suppliers_name']);

        $filter['city_id'] = empty($_REQUEST['city_id']) ? '' : trim($_REQUEST['city_id']);

        if ($filter['recommend_type_name'] != '') 

        {

            $where .= " and s.recommend_person like '%" . $filter['recommend_type_name'] . "%'";

        }

        if ($filter['suppliers_name'] != '') 

        {

            $where .= " and s.suppliers_name like '%" . $filter['suppliers_name'] . "%'";

        }

        if ($filter['city_id'] != '') 

        {

            $city_id = $filter['city_id'];

            $where .= " and s.city_id = '$city_id'";

        }

        if ($filter['province_id'] != '') 

        {

            $province_id = $filter['province_id'];

            $where .= " and s.province_id = '$province_id'";

        }

        if (isset($filter['is_check']) && $filter['is_check'] != "") 

        {

            $is_check = $filter['is_check'];

            $where .= " and s.is_check = '$is_check'";

        }

        if ($filter['is_oneshow'] != '') 

        {

            $is_oneshow = $filter['is_oneshow'];

            if ($is_oneshow == 2) 

            {

                $is_oneshow = 0;

            }

            $where .= " and s.is_oneshow = '$is_oneshow'";

        }

        if ($filter['is_twoshow'] != '') 

        {

            $is_twoshow = $filter['is_twoshow'];

            if ($is_twoshow == 2) 

            {

                $is_twoshow = 0;

            }

            $where .= " and s.is_twoshow = '$is_twoshow'";

        }

        if ($filter['rank_id'] != '') 

        {

            $is_check = $filter['rank_id'];

            $where .= " and s.rank_id = '$is_check'";

        }

        if ($filter['site_id'] != '') 

        {

            $where .= "  AND ss.site_id ='$filter[site_id]' AND ss.supp_id = s.suppliers_id";

            $sql1 = "," . $GLOBALS['hhs']->table('supp_site') . " as ss ";

        }

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_REQUEST['page_size']);

        } 

        elseif (isset($_COOKIE['HHSCP']['page_size']) && intval($_COOKIE['HHSCP']['page_size']) > 0) 

        {

            $filter['page_size'] = intval($_COOKIE['HHSCP']['page_size']);

        } 

        else 

        {

            $filter['page_size'] = 15;

        }

        /* 记录总数 */

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers') . ' as s ' . $sql1 . $where;

        // echo $sql;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter['page_count'] = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */

        $sql = "SELECT s.*

                FROM " . $GLOBALS['hhs']->table("suppliers") . " as s

				$sql1

                $where

                ORDER BY $filter[sort_by] $filter[sort_order] ";

        if ($is_down == true) 

        {

            $sql .= "LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        }

        set_filter($filter, $sql);

    } 

    else 

    {

        $sql = $result['sql'];

        $filter = $result['filter'];

    }

    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row as $idx => $value) 

    {

        $row[$idx]['term_validity'] = local_date('Y-m-d', $value['term_validity']);

        $row[$idx]['add_date'] = local_date('Y-m-d H:i:s', $value['add_time']);

    }

    // echo "<pre>";

    // print_r($row);

    $arr = array(

        'result' => $row,

        'filter' => $filter,

        'page_count' => $filter['page_count'],

        'record_count' => $filter['record_count']

    );

    return $arr;

}

function get_factoryauthorized($supp_id)

{

    $sql = $GLOBALS['db']->getAll("select * from " . $GLOBALS['hhs']->table('suppliers_factoryauthorized') . " where supp_id='$supp_id'");

    foreach ($sql as $idx => $v) 

    {

        $sql[$idx]['add_time'] = local_date("Y-m-d", $v['add_time']);

    }

    return $sql;

}

function get_trademark($supp_id)

{

    $sql = $GLOBALS['db']->getAll("select * from " . $GLOBALS['hhs']->table('suppliers_trademark') . " where supp_id='$supp_id'");

    foreach ($sql as $idx => $v) 

    {

        $sql[$idx]['add_time'] = local_date("Y-m-d", $v['add_time']);

    }

    return $sql;

}

function get_order_goods($order)

{

    $goods_list = array();

    $goods_attr = array();

    $sql = "SELECT o.*, g.suppliers_id AS suppliers_id,IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name, p.product_sn " .

        "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS o ".

        "LEFT JOIN " . $GLOBALS['hhs']->table('products') . " AS p ON o.product_id = p.product_id " .

        "LEFT JOIN " . $GLOBALS['hhs']->table('goods') . " AS g ON o.goods_id = g.goods_id " .

        "LEFT JOIN " . $GLOBALS['hhs']->table('brand') . " AS b ON g.brand_id = b.brand_id " .

        "WHERE o.order_id = '$order[order_id]' ";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        // 虚拟商品支持

        if ($row['is_real'] == 0)

        {

            /* 取得语言项 */

            $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php';

            if (file_exists($filename))

            {

                include_once($filename);

                if (!empty($GLOBALS['_LANG'][$row['extension_code'].'_link']))

                {

                    $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);

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

            $row['package_goods_list'] = get_package_goods_list($row['goods_id']);

        }

        //处理货品id

        $row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];

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

    return array('goods_list' => $goods_list, 'attr' => $attr);

}

?>