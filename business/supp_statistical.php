<?php
define('IN_HHS', true);
//------------------------------------------------------------------------------------------
//订单统计
if($action == 'order_stats')
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

?>