<?php
define('IN_HHS', true);
//订单列表
if($action =='goods_order' || $action =='goods_order2')
{	
	$smarty->assign('status_list', $_LANG['cs']);   // 订单状态
   	$smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);
    $smarty->assign('cs_await_pay',     CS_AWAIT_PAY);
   	$smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);
    if($action =='goods_order')
    {
        $order_list=get_order_list(true,$action);  
    }
    else
    {
        $order_list=get_order_list(true,$action);
    }
	$smarty->assign('pager', $order_list['pager']);
	$smarty->assign('order_list',$order_list['orders']);
	$order_list['filter']['start_date']=local_date('Y-m-d H:i:s',$order_list['filter']['start_time']);
	$order_list['filter']['end_date']=local_date('Y-m-d H:i:s',$order_list['filter']['end_time']);
	$smarty->assign('filter',$order_list['filter']);
	$smarty->assign('action',$action);
	$sql="select id,shop_name from ".$hhs->table('shipping_point')." where suppliers_id=".$suppliers_id;
    $point_list = $db->getAll($sql);
	$smarty->assign('point_list',$point_list);
	$smarty->display("supp_order.dwt");
}
/*------------------------------------------------------ */
//-- 一键发货
/*------------------------------------------------------ */
function responseMsg($error=0,$message='',$order_id=0)
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => $error,'message' =>$message,'order_id' => $order_id);
    die($json->encode($result));
}
if ($_REQUEST['act'] == 'quick_delivery')
{
    /*据说会坑人，我们总会试*/
    require('../includes/init2.php');
    require('../includes/lib_order.php');
    include_once(ROOT_PATH . 'business/includes/lib_mian.php');
    $suppliers_array = get_suppliers_info($_SESSION['suppliers_id']);
	if(!$_SESSION['role_id'])
	{
		$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$suppliers_array['user_name'];
	}
	else
	{
		$role_name = $db->getOne("select name from ".$hhs->table('supp_account')." where account_id='$_SESSION[role_id]'");
		$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$role_name;
	}
    $order_id = intval(trim($_REQUEST['order_id']));
    $invoice_no = intval(trim($_REQUEST['invoice_no']));
    if(!empty($order_id))
    {
    	/*查询订单*/
    	$order = order_info($order_id);
    	/* 查询：取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' =>$order['order_sn']));
        $attr = $_goods['attr'];
        $goods_list = $_goods['goods_list'];
        unset($_goods);
        /*分单确认*/
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳
        /*订单中的发货数量，由于下订单时库存已经减少，此处不加判断库存是否存在*/
        foreach($goods_list as $key => $value)
        {
            $send_number[$value['rec_id']] = intval($value['goods_number']);
        }
        /*处理订单*/
        $order_id = $order['order_id'];
        $delivery['user_id']  = intval($order['user_id']);
        $delivery['country']  = intval($order['country']);
        $delivery['province'] = intval($order['province']);
        $delivery['city']     = intval($order['city']);
        $delivery['district'] = intval($order['district']);
        $delivery['agency_id']    = intval($order['agency_id']);
        $delivery['insure_fee']   = floatval($order['insure_fee']);
        $delivery['shipping_fee'] = floatval($order['shipping_fee']);
        $delivery['order_sn'] = trim($order['order_sn']);
        $delivery['add_time'] = trim($order['add_time']);
        $delivery['how_oos'] = trim($order['how_oos']);
        $delivery['shipping_id'] = intval($order['shipping_id']);
        $delivery['consignee'] = trim($order['consignee']);
        $delivery['address'] = trim($order['address']);
        $delivery['sign_building'] = trim($order['sign_building']);
        $delivery['email'] = trim($order['email']);
        $delivery['zipcode'] = trim($order['zipcode']);
        $delivery['tel'] = trim($order['tel']);
        $delivery['mobile'] = trim($order['mobile']);
        $delivery['best_time'] = trim($order['best_time']);
        $delivery['postscript'] = trim($order['postscript']);
        $delivery['shipping_name'] = trim($order['shipping_name']);
        /* 取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
        $goods_list = $_goods['goods_list'];
        /* 生成发货单 */
        /* 获取发货单号和流水号 */
        $delivery['delivery_sn'] = get_delivery_sn();
        $delivery_sn = $delivery['delivery_sn'];
        /* 获取当前操作员 */
        $delivery['action_user'] = $_SESSION['admin_name'];
        /* 获取发货单生成时间 */
        $delivery['update_time'] = GMTIME_UTC;
        $sql ="select add_time from ". $GLOBALS['hhs']->table('order_info') ." WHERE order_sn = '" . $delivery['order_sn'] . "'";
        $delivery['add_time'] =  $GLOBALS['db']->GetOne($sql);
        /* 获取发货单所属供应商 */
        $delivery['suppliers_id'] = $_SESSION['suppliers_id'];
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

                }
            }
        }
        unset($filter_fileds, $delivery, $_delivery, $order_finish);
        /* 定单信息 */
        $_sended = & $send_number;
        $_goods['goods_list'] = $_goods['goods_list'];
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
        /* 记录log */
        order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note,$supp_opt_name);
        /* 查询订单信息 */
        $order = order_info($order_id);
        /* 标记订单为已确认 “已发货” */
        $delivery   = array();
        $order_id   = intval($order_id);        // 订单id
        $delivery_id   = intval($delivery_id);        // 发货单id
        $action_note    =  '一键发货';
        /* 根据发货单id查询发货单信息 */
        if (!empty($delivery_id))
        {
            $delivery_order = delivery_order_info($delivery_id);
        }
        else
        {
            responseMsg(3,'此发货单不存在！');
        }
        $_delivery['invoice_no'] = isset($invoice_no) ? trim($invoice_no) : '';
        $_delivery['update_time'] = GMTIME_UTC;
        $_delivery['status'] = 0; // 0，为已发货
        $query = $db->autoExecute($hhs->table('delivery_order'), $_delivery, 'UPDATE', "delivery_id = $delivery_id", 'SILENT');
        /* 标记订单为已确认 “已发货” */
        /* 更新发货时间 */
        $order_finish = get_all_delivery_finish($order_id);
        $shipping_status = ($order_finish == 1) ? SS_SHIPPED : SS_SHIPPED_PART;
        $arr['shipping_status']     = $shipping_status;
        $arr['shipping_time']       = GMTIME_UTC; // 发货时间
        $arr['invoice_no']          = trim($invoice_no);
        $arr['is_deal']          = 1;//一键发货
        update_order($order_id, $arr);
        /* 发货单发货记录log */
        order_action($order['order_sn'], OS_CONFIRMED, $shipping_status, $order['pay_status'], $action_note, $supp_opt_name, 1);
        $user_id=$order['user_id'];
        $wxch_order_name='shipping';
        include_once('../wxch_order.php');
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
                $sms->send($order['mobile'], sprintf($GLOBALS['_LANG']['order_shipped_sms'], $order['order_sn'],
                    local_date($GLOBALS['_LANG']['sms_time_format']), $GLOBALS['_CFG']['shop_name']), 0);
            }
        }
        responseMsg(0,'发货成功',$order_id);
        /* 清除缓存 */
        clear_cache_files();
    }
    else
    {
    	responseMsg(2,'参数错误！');
    }
    exit();
}
/*一键发货结束*/
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
	if(isset($_REQUEST['order_point_status']))
	{
		foreach($order_sn_list as $id_order)
        {
            $sql = "SELECT o.*,sp.shop_name,sp.province,sp.city,sp.district,sp.address,sp.mobile  FROM " . $hhs->table('order_info') .
                "AS o LEFT JOIN " . $hhs->table('shipping_point') . " AS sp ON sp.id = o.point_id  WHERE order_sn = '$id_order'" .
                " AND order_status = '1' AND shipping_status = '0' AND pay_status = '2'";
            $order = $db->getRow($sql);
			
            if($order)
            {
                $order_id = $order['order_id'];
				
				
				$province = $db->getOne("SELECT region_name FROM " . $hhs->table("region")." WHERE region_id = ".$order['province']);
				
				$city = $db->getOne("SELECT region_name FROM " . $hhs->table("region")." WHERE region_id = ".$order['city']);
				
				$district = $db->getOne("SELECT region_name FROM " . $hhs->table("region")." WHERE region_id = ".$order['district']);
				 //标记订单为已提醒 
                update_order($order_id, array('point_shop_remind' => 2, 'point_remaind_time' => gmtime()));
                
                 //记录log 
                order_action($order['order_sn'], 1, 0, 2, $action_note);
                 //发送邮件
                  $row = $db->getRow('SELECT openid FROM '.$hhs->table('users').' WHERE user_id = ' . $order['user_id']);
				  
				  
				   if($row['openid']){
                                    $openid = $row['openid'];
                                    $title  = '取货提醒&您的货物已到'.$order['shop_name'].'自提店！！！';
                                    $url    = 'http://' . $_SERVER['HTTP_HOST'] . '/user.php?act=order_detail&order_id='.$order_id;
									$shopinfo = '';
                                    $order_goods = $db->getAll("SELECT * FROM " . $hhs->table('order_goods') . "  WHERE `order_id` = '$order_id'");
                                    if(!empty($order_goods))
                                    {
                                        foreach($order_goods as $v)
                                        {
                                            if(empty($v['goods_attr']))
                                            {
                                                $shopinfo .= $v['goods_name'].'('.$v['goods_number'].'),';
                                            }
                                            else
                                            {
                                                $shopinfo .= $v['goods_name'].'（'.$v['goods_attr'].'）'.'('.$v['goods_number'].'),';
                                            }
                                        }
                                        $shopinfo = substr($shopinfo, 0, strlen($shopinfo)-1);
                                    }
									$remind_info = $db->getOne("SELECT value FROM " . $hhs->table('shop_config') . "  WHERE `code` = 'remind_info'");
									$wxch_consignee = "\r\n".$remind_info;
									
									if($order['mobile'])
									{
										$point_tel = '自提店电话：'.$order['mobile']."\r\n";
									}
									
									
									
                                    $desc   = '订单号：'.$order['order_sn']."\r\n".'商品信息：'.$shopinfo."\r\n".'自提店地址：'.$province.$city.$district.$order['address']."\r\n".$point_tel.$wxch_consignee;
                                    $weixin = new class_weixin($appid,$appsecret);
									
									
                                    //$weixin->send_wxmsg($openid, $title , $url , $desc ); 
									$data['title']= '尊敬的'.$row['uname'].'，您的货物已到'.$order['shop_name'];
									
									$data['order_sn']= $order['order_sn'];
									
									$data['add_time']= local_date('Y-m-d H:i:s',$order['add_time']);
									
									$data['order_status']= '可以提货';
									
									$data['point_address'] = $province.$city.$district.$order['address'].$order['mobile'];
									
									$data['desc'] = $remind_info;
									
									$weixin -> send_wxmsgdemo($openid,'dDR5ZkNiuWmgPEupM-srRWzawZhEJfl0a0jbEZvtExk',$url,$w_description,$picurl='',$data);
									}  
            }
        }
		
		show_message('提醒成功！');
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
    $order_list=get_order_list(false); 
    // $order_list=$arr['orders'];  
	$filename='订单信息.csv';
	header("Content-type:text/csv");
	header("Content-Disposition:attachment;filename=".$filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');
	
	/*
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=$filename.xls");
*/
	/* 订单概况 */
	$data = '订单ID' . ",";
	$data .= "订单价格,";
	$data .= "团购ID,";
	$data .= "订单编号,";
	$data .= "是否是团长,";
	$data .= "支付时间,";
	$data .= "商品编号,";
	$data .= "商品名称,";
	$data .= "购买数量,";
	$data .= "商品属性,";
	$data .= "顾客留言,";
	$data .= "团购开始时间,";
	$data .= "省,";
	$data .= "市,";
	$data .= "区县,";
	$data .= "地址,";
	$data .= "电话,";
	$data .= "收货人姓名,";
	$data .= "订单时间,";
	$data .= "快递编号,";
	$data .= "快递名称,";
	$data .= "快递单号,";
    $data .= "微信单号,";
    $data .= "支付金额,";
    $data .= "使用余额,";
    $data .= "使用红包,\n";
	$i=1;
	if(!empty($order_list['orders'])){
		foreach($order_list['orders'] as $k=>$v){
		    $sql="select g.*,og.goods_number,og.goods_attr,og.goods_price   from ".$hhs->table('order_goods')." as og left join ".
                    $hhs->table('goods')." as g on og.goods_id=g.goods_id where og.order_id=".$v['order_id']
            ;
            $goods_all=$db->getAll($sql);
            $goods=$goods_all[0];
		    /*
		    $sql="select pay_time from ".$hhs->table('order_info')." where order_id=".$v['team_sign'];
		    $pay_time=$db->getOne($sql);
		    $team_start_time=local_date("Y-m-d H:i:s",$pay_time);
		    */
		    $sql="select region_name from ".$hhs->table('region')." where region_id=".$v['province'];
		    $province=$db->getOne($sql);
		    $sql="select region_name from ".$hhs->table('region')." where region_id=".$v['city'];
		    $city=$db->getOne($sql);
		    $sql="select region_name from ".$hhs->table('region')." where region_id=".$v['district'];
		    $district=$db->getOne($sql);
		    
		    $data .= $v['order_id']. ",";
			$data .= $v['total_fee'].",";
			$data .= $v['team_sign'].",";
			$data .= $v['order_sn']."\t,";
			$data .= $v['team_first'].",";
			$data .= $v['formated_pay_time'].",";
			
			$data .= $goods['goods_sn'].",";
            $data .= $goods['goods_name'].",";
            
            $data .= intval($goods['goods_number']).',';
            //$data .= str_replace(array("\r\n","\n","\r"), '', trim($goods['goods_attr'])).',';
            //$data .= htmlspecialchars(str_replace(PHP_EOL, '', $goods['goods_attr'])).","; 
			$data .= trimall($goods['goods_attr']).","; 
			$data .=$v['postscript'].",";
			
			$data .= $v['team_start_time'].",";
			$data .= $province.",";
			$data .= $city.",";
			$data .= $district.",";
			$data .= $v['address'].",";
			$data .= $v['mobile']."\t,";
			$data .= $v['consignee'].",";
			$data .= $v['formated_order_time'].",";
			$data .= $v['shipping_id'].",";
			$data .= $v['shipping_name'].",";
			$data .= $v['invoice_no']."\t,";	
            $data .= $v['transaction_id']."\t,";
            $data .= floatval($v['money_paid']).",";
            $data .= floatval($v['surplus']).",";
            $data .= floatval($v['bonus']).",\n";
            if(count($goods_all)>1){
                foreach($goods_all as $kk=>$vv){
                    if($kk>0){
                        $data .= "\t,\t,\t,\t,\t,\t, ";
                        $data .= $vv['goods_sn'].",";
                        $data.=$vv['goods_name'].",";
                        $data .= intval($vv['goods_number']).',';
                        //$data .= trim($vv['goods_attr']).',';
                        $data .= htmlspecialchars(str_replace(PHP_EOL, '', $vv['goods_attr'])).","; 
                        $data .= "\t,\t,\t,\t,\t,\t,\t,\t,\t,\t,\t,\t ,";
                        $data.="\n";
                    }
                }
            }
		}
	}
	
	echo hhs_iconv(EC_CHARSET, 'GB2312', $data) . "\n";
	exit;
}
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
            $links = 'index.php?op=order&act=goods_order';
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
            $links = 'index.php?op=order&act=order_info&order_id=' . $order_id;
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
            $links = 'index.php?op=order&act=order_info&order_id=' . $order_id;
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
    show_message('操作成功','订单详情','index.php?op=order&act=order_info&order_id=' . $order_id,'info');
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
            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region,o.address,o.shop_name,o.mobile " .
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
            $url = 'index.php?op=order&act=delivery_list&order_sn='.$_REQUEST['order_sn'];
        }
        else
        {
            $url = 'index.php?op=order&act=shipping_delivery_list&order_sn='.$_REQUEST['order_sn'];
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
            hhs_header("Location: index.php?op=order&act=operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
        else
        {
            /* 多个订单 */
            hhs_header("Location: index.php?op=order&act=batch_operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
    //}
}
elseif($action =='delivery_list')
{
	$arr=get_delivery_list(true,1);
    
	$smarty->assign('delivery_list',$arr['delivery']);
	
    $smarty->assign('pager',$arr['pager']);
    $smarty->assign('filter',$arr['filter']);
    $smarty->assign('supp_account_list',get_supp_account_list($suppliers_id));
    //var_dump($_LANG);exit();
    $smarty->display("supp_order.dwt");
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
	
	
    $smarty->display("supp_order.dwt");
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
   $smarty->display("supp_order.dwt");	
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
			    show_message(sprintf($_LANG['act_good_vacancy'], $value['goods_name']),'返回列表', 'index.php?op=order&act=delivery_list&delivery_id='.$delivery_id, 'info');
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
    
    $user_id=$order['user_id'];
    $wxch_order_name='shipping';
    include_once('../wxch_order.php');
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
        $url = 'index.php?op=order&act=delivery_list';
    }   
    else{
        $url = 'index.php?op=order&act=shipping_delivery_list';
    }
    show_message('操作成功','返回列表', $url, 'info');
		// show_message('操作成功','返回列表', 'suppliers.php?act=delivery_list', 'info');
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
function trimall($str)
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str); 
}
?>