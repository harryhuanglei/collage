<?php
define('IN_HHS', true);
require('../includes/init2.php');
require('../includes/lib_order.php');
require('../includes/lib_code.php');

//define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$op  = isset($_REQUEST['op']) ? trim($_REQUEST['op']) : 'main';

$suppliers_id = $_SESSION['suppliers_id'];
$suppliers_array = get_suppliers_info($suppliers_id);
$smarty->assign('suppliers_array',$suppliers_array);

$smarty->assign('temp_root_path',ROOT_PATH);
$smarty->assign('admin_path',ADMIN_PATH);
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/bonus.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
$_LANG['account_settlement_status'][1] = '待商家审核';
$_LANG['account_settlement_status'][2] = '待平台审核';
$_LANG['account_settlement_status'][3] = '已审核待结算';
$_LANG['account_settlement_status'][4] = '待商家确认账户信息';
$_LANG['account_settlement_status'][5] = '待付款';
$_LANG['account_settlement_status'][6] = '付款完成';
$_LANG['account_settlement_status'][7] = '商家确认已收款';
$_LANG['account_settlement_status'][10] = '商家有疑问';
$_LANG['account_settlement_status'][11] = '未通过平台审核';
/**
 * 解决跳转问题，这个是个渣渣问题。
 * 去管理后台查看自提的id，填写一下
 * 在所有的有问题post表单中发送一下这个订单的shippingID
 * 然后匹配一下
 */
$offlineID = $GLOBALS['db']->getOne("SELECT `shipping_id` from ".$GLOBALS['hhs']->table('shipping')." where `shipping_code` = 'cac'");
define('offlineID', $offlineID);//14
$smarty->assign('offlineID', offlineID);

$smarty->assign('lang', $_LANG);
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
include_once(ROOT_PATH . 'business/includes/lib_mian.php');


    if(isset($_REQUEST['submit'])){
        $file = $_FILES["file"];
        
        if($file["name"]==null)
            show_message('上传文件不能为空', 1, array(), false);
        $pos=strrpos($file["name"],".");
        $ext=substr($file["name"],$pos+1);
        if($ext!='csv'){
        	 $links[] = array('text' => '重新上传', 'href' => 'order.php?act=import');
             show_message("请使用csv格式的文件", 1, $links);
        }
        $filename=$file['name'];
        $newpath=$file['tmp_name'];
        /*
        $field="(order_id,total_fee,team_sign,order_sn,team_first,formated_pay_time,goods_sn
        ,province,city,district,address,mobile,consignee,formated_add_time,
        shipping_id,shipping_name,invoice_no)";*/
       
        if(is_file($newpath)) {
            $tmp = file_get_contents($newpath);
            $tmp =hhs_iconv('gb2310', EC_CHARSET, $tmp);
            $list= explode("\n", $tmp);
            unset($list[0]);
            $total_num=0;
            foreach($list as $k=>$v){
                $t = explode(',', $v);
                
                foreach($t as $k1=>$v1){
                    //$t[$k1]=preg_replace("/\"/",'',$v1);
                    $t[$k1]=$v1=str_replace("\"", "", $v1);
                    $t[$k1]=$v1=str_replace("'", "", $v1);
                    $t[$k1]=$v1=trim($v1);   
                }
                $list[$k]=$t;
                if(empty($t[0])){
                    unset($list[$k]);
                    continue;
                }
                $total_num++;
            }
            $success_num=0;
            if(!empty($list)){
                foreach($list as $t) {
                    
                    //$t = explode(',', $v);
                    $sql="select region_id from ".$hhs->table('region')." where region_name ='$t[12]' ";
                    $province=$db->getOne($sql);
                    $sql="select region_id from ".$hhs->table('region')." where region_name ='$t[13]' ";
                    $city=$db->getOne($sql);
                    $sql="select region_id from ".$hhs->table('region')." where region_name ='$t[14]' ";
                    $district=$db->getOne($sql);
                    //order_sn='$t[3]',
                    if(!empty($province)&&!empty($city)&&!empty($district)){
                        $field=" set province='$province',city='$city',district='$district',
                        address='$t[15]',mobile='$t[16]',consignee='$t[17]',
                        shipping_id='$t[19]',shipping_name='$t[20]',invoice_no='$t[21]' ";
                        $sql="update " . $hhs->table('order_info') .$field." where order_id=".$t[0];
                        $r=$db->query($sql);
                        /*发货*/
                        $invoice_no=$t[21];
                        $order_id=$t[0];

                        /* 查询：根据订单id查询订单信息 */
                        if(!empty($invoice_no)){
                            $order = order_info($order_id);
                            $operable_list=operable_list($order);

					

                            if($operable_list[split]){
                                include('split.php');
                        		$success_num++;
                                $row = $db->getRow('SELECT openid FROM '.$hhs->table('users').' WHERE user_id = ' . $order['user_id']);
                                if($row['openid']){
                                    $openid = $row['openid'];
                                    $title  = '您的订单已经发货啦！';
                                    $url    = 'user.php?act=order_detail&order_id='.$order_id;

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
                                    if($order['pay_status'] == 0)
                                    {
                                        $pay_status = '支付状态：未付款';
                                    }
                                    elseif($order['pay_status'] == 1)
                                    {
                                        $pay_status = '支付状态：付款中';
                                    }
                                    elseif($order['pay_status'] == 2)
                                    {
                                        $pay_status = '支付状态：已付款';
                                    }
                                    $wxch_address = "\r\n收件地址：".$order['address'];
                                    $wxch_consignee = "\r\n收件人：".$order['consignee'];

                                    if($order['order_amount'] == '0.00')
                                    {
                                        $order['order_amount'] = $order['money_paid'];
                                    }

                                    $desc   = '订单号：'.$order['order_sn']."\r\n".'商品信息：'.$shopinfo."\r\n".$pay_status.$wxch_consignee.$wxch_address;

                                    $weixin = new class_weixin($appid,$appsecret);
                                    $weixin->send_wxmsg($openid, $title , $url , $desc );            
                                }
                            }
                        }
                    }  
                }
            }
            $link = $_SESSION['refer'];
            $message='共'.$total_num."条数据，成功修改".$success_num."条数据";
            unset($_SESSION['refer']);
            show_message($message, '返回列表',$link);
        }
    }else{
    	$_SESSION['refer'] = $_SERVER['HTTP_REFERER'];
        $smarty->display('order_import.dwt');
    }

function sys_msg($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true)
{
    if (count($links) == 0)
    {
        $links[0]['text'] = $GLOBALS['_LANG']['go_back'];
        $links[0]['href'] = 'javascript:history.go(-1)';
    }

    assign_query_info();

    $GLOBALS['smarty']->assign('ur_here',     $GLOBALS['_LANG']['system_message']);
    $GLOBALS['smarty']->assign('msg_detail',  $msg_detail);
    $GLOBALS['smarty']->assign('msg_type',    $msg_type);
    $GLOBALS['smarty']->assign('links',       $links);
    $GLOBALS['smarty']->assign('default_url', $links[0]['href']);
    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);

    $GLOBALS['smarty']->display('message.htm');

    exit;
}

function update_order_virtual_goods($order_id, $_sended, $virtual_goods)
{
    if (!is_array($_sended) || empty($order_id))
    {
        return false;
    }
    if (empty($virtual_goods))
    {
        return true;
    }
    elseif (!is_array($virtual_goods))
    {
        return false;
    }

    foreach ($virtual_goods as $goods)
    {
        $sql = "UPDATE ".$GLOBALS['hhs']->table('order_goods'). "
                SET send_number = send_number + '" . $goods['num'] . "'
                WHERE order_id = '" . $order_id . "'
                AND goods_id = '" . $goods['goods_id'] . "' ";
        if (!$GLOBALS['db']->query($sql, 'SILENT'))
        {
            return false;
        }
    }

    return true;
}