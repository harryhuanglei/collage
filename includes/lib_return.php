<?php



function can_refund($order_id) 

{

	$row = $GLOBALS['db']->getRow("select shipping_status,order_status from ".$GLOBALS['hhs']->table("order_info")." where order_id='$order_id'");

	return $row['shipping_status']==SS_RECEIVED;

}





function refund_apply_order_goods($refund, $rec_id)

{

	if($rec_id<=0 || $GLOBALS['db']->getOne("select refund_status from ".$GLOBALS['hhs']->table("order_goods")." where rec_id='$rec_id'")>0)

	{

		die("invalid");

	}



    $upload_size_limit = $GLOBALS['_CFG']['upload_size_limit'] == '-1' ? ini_get('upload_max_filesize') : $GLOBALS['_CFG']['upload_size_limit'];



    $last_char = strtolower($upload_size_limit{strlen($upload_size_limit)-1});



    switch ($last_char)

    {

        case 'm':

            $upload_size_limit *= 1024*1024;

            break;

        case 'k':

            $upload_size_limit *= 1024;

            break;

    }



	  if (empty($refund['refund_reason']))

    {

        $GLOBALS['err']->add("必须选择退款原因");

        return false;

    }

	  if (empty($refund['refund_desc']))

    {

        $GLOBALS['err']->add("必须选择退款说明");

        return false;

    }



    $refund['refund_pic1'] = refund_apply_order_goods_upload_ex($refund, 'refund_pic1', $upload_size_limit);

	$refund['refund_pic2'] = refund_apply_order_goods_upload_ex($refund, 'refund_pic2', $upload_size_limit); 

	$refund['refund_pic3'] = refund_apply_order_goods_upload_ex($refund, 'refund_pic3', $upload_size_limit); 

	if($refund['refund_pic1'] < 0 || $refund['refund_pic2'] < 0 || $refund['refund_pic3'] < 0)

	{

		return false;

	}

	$refund['refund_status'] = 1;

	$refund['refund_add_time'] = gmtime();

	$GLOBALS['db']->autoExecute($GLOBALS['hhs']->table('order_goods'), $refund, 'UPDATE', "rec_id = '" . $rec_id ."'");



    return true;

}





function refund_apply_order_goods_upload_ex($refund, $pic_name, $upload_size_limit) 

{

	if ($refund[$pic_name])

    {

        if($_FILES[$pic_name]['size'] / 1024 > $upload_size_limit)

        {

            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['upload_file_limit'], $upload_size_limit));

            return -1;

        }

        $refund_pic1 = upload_file($_FILES[$pic_name], 'feedbackimg');



        if ($refund_pic1 === false)

        {

			$GLOBALS['err']->add("无法上传");

            return -1;

        }

    }

    else

    {

        $refund_pic1 = '';

    }

	return $refund_pic1;

}



function get_order_goods_list($order_id, $where_ex="") 

{

	$sql = "select g.goods_name,g.goods_thumb,g.little_img,g.goods_id,og.goods_price,g.shop_price,og.refund_status,og.goods_number,og.refund_add_time,og.refund_confirm_time,og.goods_price*og.goods_number as refund_money,og.refund_reason,og.refund_desc,og.goods_attr,og.rec_id,og.refund_pic1,og.refund_pic2,og.refund_pic3 from ".$GLOBALS['hhs']->table("order_goods")." as og left join ".$GLOBALS['hhs']->table("goods")." as g on g.goods_id=og.goods_id where og.order_id='$order_id' ".$where_ex;

	$arr = $GLOBALS['db']->getAll($sql);
	

	foreach($arr as $k=>$v) 

	{

		$arr[$k]['url'] = build_uri('goods', array('gid'=>$v['goods_id']) );

		$arr[$k]['shop_price_fmt'] = price_format($v['shop_price']);

		$arr[$k]['goods_price_fmt'] = price_format($v['goods_price']);

		$arr[$k]['refund_add_time_fmt'] = local_date('Y-m-d H:i:s', $v['refund_add_time']);

		$arr[$k]['refund_confirm_time_fmt'] = local_date('Y-m-d H:i:s', $v['refund_confirm_time']);

		$arr[$k]['refund_pic1'] = empty($v['refund_pic1']) ? "" : "../data/feedbackimg/".$v['refund_pic1'];

		$arr[$k]['refund_pic2'] = empty($v['refund_pic2']) ? "" : "../data/feedbackimg/".$v['refund_pic2'];

		$arr[$k]['refund_pic3'] = empty($v['refund_pic3']) ? "" : "../data/feedbackimg/".$v['refund_pic3'];
		
		
		
		$arr[$k]['comment'] = get_is_comment($order_id,$v['goods_id']);
		$arr[$k]['nums'] = $nums;
		
		



	}

	return $arr;



}


function get_is_comment($order_id,$goods_id)
{
	$sql = 'select count(*) from ' . $GLOBALS['hhs']->table('comment') . " where id_value='$goods_id' and order_id='$order_id'";
    return $GLOBALS['db']->GetOne($sql);
}


function refund_confirm_order_goods($rec_id, $refund_status) 

{

	$row = $GLOBALS['db']->getRow("select og.rec_id,o.user_id,og.goods_price*og.goods_number as refund_money,o.order_sn,og.goods_number,og.goods_name from ".$GLOBALS['hhs']->table("order_goods")." as og,".$GLOBALS['hhs']->table("order_info")." as o  where o.order_id=og.order_id and refund_status='1' and rec_id='$rec_id'");

	empty($row) ? die("inalid") : extract($row);

	if($rec_id>0)

	{

		if($refund_status == 2)

		{

			$change_desc = "订单{$order_sn}中的{$goods_name}退款成功,返还余额";

			log_account_change($user_id, $refund_money, 0, 0, 0, $change_desc, ACT_OTHER);

		}

		$GLOBALS['db']->query("update ".$GLOBALS['hhs']->table("order_goods")." set refund_status='$refund_status',refund_confirm_time='".gmtime()."' where rec_id='$rec_id'");

	}

}





function get_order_goods_info($rec_id) 

{

	$sql = "select og.goods_name,og.goods_price,og.goods_price*og.goods_number as subtotal,og.goods_number,og.goods_id,g.goods_thumb,og.refund_status,og.rec_id,o.order_id from ".$GLOBALS['hhs']->table("order_goods")." as og,".$GLOBALS['hhs']->table("order_info")." as o,".$GLOBALS['hhs']->table("goods")." as g where og.goods_id=g.goods_id and og.order_id=o.order_id and og.rec_id='$rec_id'";

	$row = $GLOBALS['db']->getRow($sql);

	$row['url'] = build_uri('goods', array('gid'=>$row['goods_id']) );

	return $row;

}





function get_user_orders_ex($user_id, $num = 10, $start = 0,$ext=null)

{

    global $_CFG;

    /*判断组团的状态*/

    $sql="select * from ".$GLOBALS['hhs']->table('order_info') ." where user_id='$user_id' limit ".$start.",".$num;

    $orders=$GLOBALS['db']->getAll($sql);

    if(!empty($orders)){

        foreach($orders as $v){

            if($v['extension_code']=='team_goods'&&$v['team_status']==1 && $v['is_luck'] == 0 && $v['is_miao'] == 0){

                $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$v['team_sign'];

                $pay_time=$GLOBALS['db']->getOne($sql);

                if($pay_time && ($pay_time+$_CFG['team_suc_time']*24*3600)<gmtime()){

                    do_team_refund($v['team_sign']);

                    //取消订单

                    // $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2 where team_status=1 and team_sign=".$v['team_sign'];

                    // $GLOBALS['db']->query($sql);

                    // $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$v['team_sign'];

                    // $GLOBALS['db']->query($sql);

                }

            }

        }

    }

    include_once(ROOT_PATH . 'includes/lib_order.php');

    /* 取得订单列表 */

    $arr    = array();



    $sql = "SELECT team_num,teammen_num,square,is_comm,is_luck,luck_times,is_lucker,shipping_fee,order_id,share_pay_type, order_sn, order_status,integral, shipping_status, pay_status,pay_id, add_time,order_amount, shipping_name,shipping_id,invoice_no,point_id, " .

           "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee,extension_code,extension_id,team_sign,team_first,team_status,suppliers_id ".

           " FROM " .$GLOBALS['hhs']->table('order_info') .

           " WHERE user_id = '$user_id' ".$ext." ORDER BY add_time DESC";

    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

	



    while ($row = $GLOBALS['db']->fetchRow($res))

    {
        $row['order_status2'] = $row['order_status'];
        if ($row['order_status'] == OS_UNCONFIRMED)

        {

            $row['handler'] = "<a href=\"user.php?act=cancel_order&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_cancel']."')) return false;\">".$GLOBALS['_LANG']['cancel']."</a>";

        }

        else if ($row['order_status'] == OS_SPLITED)

        {

            /* 对配送状态的处理 */

            if ($row['shipping_status'] == SS_SHIPPED)

            {

                @$row['handler'] = "<a href=\"javascript:void(0);\" onclick='get_invoice(\"".$row['shipping_name']."\",\"".$row['invoice_no']."\");'>查看物流</a>";

                @$row['handler'] .= "<a href=\"user.php?act=affirm_received&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_received']."')) return false;\">".$GLOBALS['_LANG']['received']."</a>";

            }

            elseif ($row['shipping_status'] == SS_RECEIVED)

            {

                //@$row['handler'] = '<span style="color:red">'.$GLOBALS['_LANG']['ss_received'] .'</span>';

            }

            else

            {

                if ($row['pay_status'] == PS_UNPAYED)

                {

                    //@$row['handler'] = "<a href=\"user.php?act=order_detail&order_id=" .$row['order_id']. '">' .$GLOBALS['_LANG']['pay_money']. '</a>';

                }

                else

                {

                    //@$row['handler'] = "<a href=\"user.php?act=order_detail&order_id=" .$row['order_id']. '">' .$GLOBALS['_LANG']['view_order']. '</a>';

                }



            }

        }

        else

        {

            //$row['handler'] = '<span style="color:red">'.$GLOBALS['_LANG']['os'][$row['order_status']] .'</span>';

            if($row['team_sign'])

            {

                if($row['team_status'] ==1){

                    $row['handler'] = '<a href="javascript:;">团购进行中</a>';

                }

                elseif($row['team_status'] ==2)

                {

                    $row['handler'] = '<a href="javascript:;">组团成功</a>';

                    if($row['shipping_id'] == notExpress){

                        if($row['shipping_status'] <1 && $row['pay_status'] == 2)

                        $row['handler'] .= '<a href="javascript:;showCode('.$row['order_id'].')">核销二维码</a>';

                    }

                    else{

                        if ($row['shipping_status'] == SS_SHIPPED)

                        {

                            $row['handler'] .= "<a href=\"javascript:void(0);\" onclick='get_invoice(\"".$row['shipping_name']."\",\"".$row['invoice_no']."\");'>查看物流</a>";

                        }

                    }             

                }

                elseif($row['team_status'] ==3){

                    if($row['pay_status']>2)

                    $row['handler'] = '<a href="javascript:;">组团失败已退款</a>';

                    else

                    $row['handler'] = '<a href="javascript:;">组团失败待退款</a>';

                }

                elseif($row['team_status'] ==4){

                    $row['handler'] = '<a href="javascript:;">组团失败已退款</a>';

                }            

            }

            elseif($row['shipping_id'] == notExpress){

                if ($row['pay_status'] == 2 && $row['shipping_status'] != 2)

                $row['handler'] = '<a href="javascript:;showCode('.$row['order_id'].')">核销二维码</a>';

            }



        }



        if($row['shipping_status'] == 2)

        {

            // $sql = "select c.id_value from ".$GLOBALS['hhs']->table('order_info')." as o,".$GLOBALS['hhs']->table('order_goods')." as g,".$GLOBALS['hhs']->table('comment')." as c where o.order_id = '".$row['order_id']."' and g.`order_id` = o.`order_id` and g.`goods_id` = c.`id_value`";



            // $comment = $GLOBALS['db']->getOne($sql);
			
/*pangbin*/

/*
            if($row['is_comm'] == 0)

            {

                $sql = "select g.goods_id from ".$GLOBALS['hhs']->table('order_info')." as o,".$GLOBALS['hhs']->table('order_goods')." as g where o.order_id = '".$row['order_id']."' and g.`order_id` = o.`order_id`";

            $goods_id = $GLOBALS['db']->getOne($sql);



            $row['handler'] .= '<a href="javascript:void(0);" class="order_btn_buy" data-id="'.$goods_id.'" data-order_id="'.$row['order_id'].'">去评价</a>';

            }*/

        }
		



        $pay_online='';

        if($row['share_pay_type']>0&&$row['pay_status'] == PS_UNPAYED){

            $pay_online="<a class='state_btn_2' href='share_pay.php?id=".$row['order_id']."'>代付</a>";

        }

        if ($row['share_pay_type']==0&&$row['pay_status'] == PS_UNPAYED &&($row['order_status'] == OS_UNCONFIRMED || $row['order_status'] == OS_CONFIRMED))

        {

            

            $payment_info = array();

            $payment_info = payment_info($row['pay_id']);

            //无效支付方式

            if ($payment_info === false)

            {

                $row['pay_online'] = '';

            }

            else

            {

                //取得支付信息，生成支付代码

                $payment = unserialize_config($payment_info['pay_config']);

                //获取需要支付的log_id

                $row['log_id']    = get_paylog_id($row['order_id'], $pay_type = PAY_ORDER);

                $row['user_name'] = $_SESSION['user_name'];

                $row['pay_desc']  = $payment_info['pay_desc'];

                if($row['extension_id']){

                    $sql="select goods_name from ".$GLOBALS['hhs']->table('goods')." where goods_id=".$row['extension_id'];

                    $row['goods_name']=$GLOBALS['db']->getOne($sql);

                }

                /* 取得在线支付方式的支付按钮 */

                if($row['order_amount']>0){

                    if($payment_info['pay_code']!='alipay'){

                        /* 调用相应的支付方式文件 */

                        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');

                        $pay_obj    = new $payment_info['pay_code'];

                        $pay_online = $pay_obj->get_code($row, $payment);

                    

                    }else{

                        $pay_online ='<a class="state_btn_2" href="toalipay.php?order_id='.$row['order_id'].'"   >支付宝支付</a>';

                    }

                }

                

            }

        }

        

        $row['handler']=$pay_online.$row['handler'];

        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];

        

        /*

        if($row['order_status']==2){

            $row['order_status'] =  $GLOBALS['_LANG']['os'][$row['order_status']] ;

        }else{

            if($row['pay_status']==0){

                $row['order_status'] = $GLOBALS['_LANG']['ps'][$row['pay_status']];

            }else{

                $row['order_status'] =  $GLOBALS['_LANG']['ss'][$row['shipping_status']];// $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' .

            } 

        }*/

        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];

        //$GLOBALS['_LANG']['os'][$row['order_status']]. ',' . 

        $row['goods_list'] = get_order_goods_list($row['order_id']);
		


		
		

        $row['can_refund'] = can_refund($row['order_id']);



        $suppliers = $row['suppliers_id']?

            $GLOBALS['db']->getRow('select suppliers_name,supp_logo FROM '.$GLOBALS['hhs']->table('suppliers').' WHERE suppliers_id=' . $row['suppliers_id'])

        :array();





        $row['square'] = $row['team_status'] == 1 ? (empty($row['square']) ?1:0 ):0;

if($row['point_id'])
	    {
		    $row['shipping_point'] = get_shipping_point_name($row['point_id']);
		
	    }
        $row['add_time']=local_date("Y-m-d H:i:s",$row['add_time']);

        $arr[] = array( 'order_id'       => $row['order_id'],

                        'order_sn'       => $row['order_sn'],
						
						'team_num'       => $row['team_num'],
						
						'teammen_num'       => $row['teammen_num'],

						'integral'       => $row['integral'],

                        'is_lucker'      => $row['is_lucker'],

                        'luck_times'     => $row['luck_times'],

                        'lucker_num'     => $row['team_status'] ==2 ? getLuckerNum($row['team_sign']) : 0,

                        'open_times'     => $row['is_luck'] && $row['team_status'] ==2 ? getOpenTime($row['team_sign']) : '',

                        'team_status'    => $row['team_status'],

                        'goods_list'     => $row['goods_list'],

                        'goods_num'      => count($row['goods_list']),

                        'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),

                        'order_status'   => $row['order_status'],
						'order_status2'   => $row['order_status2'],
						'shipping_status'   => $row['shipping_status'],
						

                        'total_fee'      => price_format($row['total_fee'], false),

                        'order_amount'   => price_format($row['order_amount'], false),

                        'can_refund'     => $row['can_refund'],

                        'shipping_fee'   => price_format($row['shipping_fee'], false),

                        'handler'        => $row['handler'],

                        'suppliers_name' => $suppliers['suppliers_name'],

                        'square' => $row['square'],

                        'supp_logo'      => $suppliers['supp_logo'],
						'add_time'       => $row['add_time'],
						'shipping_point'       => $row['shipping_point'],

                       );

	//	$arr[] = $row;

    }

	

	



    return $arr;

}



function getLuckerNum($team_sign){

    $sql="select id from ".$GLOBALS['hhs']->table('order_luck') ." where team_sign='$team_sign' and is_lucker = 1";



    return $GLOBALS['db']->getOne($sql);

}



function getOpenTime($team_sign)

{

    $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info') ." where team_sign='$team_sign' and pay_status = 2 order by order_id desc";



    $pay_time = $GLOBALS['db']->getOne($sql);



    return local_date($GLOBALS['_CFG']['time_format'], $pay_time);

}
function get_userbuyshop_goods($user_id,$num = 10, $start = 0,$ext=null){	$arr    = array();	$sql = "SELECT o.square,o.order_id, o.add_time,og.goods_id,og.goods_name,g. goods_thumb," .			"(o.goods_amount + o.shipping_fee + o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee + o.tax - discount) AS total_fee ".			" FROM " .$GLOBALS['hhs']->table('order_info') .			" AS o INNER JOIN ".$GLOBALS['hhs']->table('order_goods').			" AS og ON o.order_id = og.order_id  INNER JOIN ".$GLOBALS['hhs']->table('goods')." AS g ON og.goods_id = g.goods_id			WHERE o.user_id = '$user_id'  and o.extension_id = 0 and o.pay_status = 2 ".$ext." ORDER BY add_time DESC";		$res = $GLOBALS['db']->SelectLimit($sql, $num, $start);	while ($row = $GLOBALS['db']->fetchRow($res)){			$arr[] = array('order_id'       => $row['order_id'],					'goods_id'=>$row['goods_id'],					'goods_name'=>$row['goods_name'],					'square' => $row['square'],					'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),					'total_fee'      => price_format($row['total_fee'], false),					'goods_thumb' => $row['goods_thumb'],					);		}		return $arr;	}


function get_user_team_orders($user_id, $num = 10, $start = 0,$ext=null)

{

    /*判断组团的状态*/

    // $sql="select * from ".$GLOBALS['hhs']->table('order_info') ." where user_id='$user_id' limit ".$start.",".$num;

    // $orders=$GLOBALS['db']->getAll($sql);

    // if(!empty($orders)){

    //     foreach($orders as $v){

    //         if($v['extension_code']=='team_goods'&&$v['team_status']==1 && $v['is_luck'] == 0 && $v['is_miao'] == 0){

    //             $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where is_luck=0 and  order_id=".$v['team_sign'];

    //             $pay_time=$GLOBALS['db']->getOne($sql);

    //             if($pay_time+$GLOBALS['_CFG']['team_suc_time']*24*3600<gmtime()){

    //                 do_team_refund($v['team_sign']);

    //                 //取消订单

    //                 // $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2 where team_status=1 and team_sign=".$v['team_sign'];

    //                 // $GLOBALS['db']->query($sql);

    //                 // $sql = "UPDATE ". $GLOBALS['hhs']->table('order_info') ." SET order_status=2 WHERE team_status=0 and team_sign=".$v['team_sign'];

    //                 // $GLOBALS['db']->query($sql);

    //             }

    //         }

    //     }

    // }



    /* 取得订单列表 */

    $arr    = array();

    $sql = "SELECT square,extension_id,team_sign,team_num,team_first,team_status,order_id, order_sn, order_status, shipping_status, pay_status, add_time, luckdraw_id, " .

        "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".

        " FROM " .$GLOBALS['hhs']->table('order_info') .

        " WHERE user_id = '$user_id' and  extension_code='team_goods' and team_status>0 ".$ext." ORDER BY add_time DESC";

    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);



    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        if ($row['order_status'] == OS_UNCONFIRMED)

        {

            $row['handler'] = "<a href=\"user.php?act=cancel_order&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_cancel']."')) return false;\">".$GLOBALS['_LANG']['cancel']."</a>";

        }

        else if ($row['order_status'] == OS_SPLITED)

        {

            /* 对配送状态的处理 */

            if ($row['shipping_status'] == SS_SHIPPED)

            {

                @$row['handler'] = "<a href=\"user.php?act=affirm_received&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_received']."')) return false;\">".$GLOBALS['_LANG']['received']."</a>";

            }

            elseif ($row['shipping_status'] == SS_RECEIVED)

            {

                @$row['handler'] = '<span style="color:red">'.$GLOBALS['_LANG']['ss_received'] .'</span>';

            }

            else

            {

                if ($row['pay_status'] == PS_UNPAYED)

                {

                    @$row['handler'] = "<a href=\"user.php?act=order_detail&order_id=" .$row['order_id']. '">' .$GLOBALS['_LANG']['pay_money']. '</a>';

                }

                else

                {

                    @$row['handler'] = "<a href=\"user.php?act=order_detail&order_id=" .$row['order_id']. '">' .$GLOBALS['_LANG']['view_order']. '</a>';

                }



            }

        }

        else

        {

            $row['handler'] = '<span style="color:red">'.$GLOBALS['_LANG']['os'][$row['order_status']] .'</span>';

        }



        $row['square'] = $row['team_status'] == 1 ? (empty($row['square']) ?1:0 ):0;

        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];

        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];

        $row['team_status'] = $GLOBALS['_LANG']['team_status'][$row['team_status']];

        

        $row['goods_list'] = get_order_goods_list($row['order_id']);

        $row['can_refund'] = can_refund($row['order_id']);



        $arr[] = array('order_id'       => $row['order_id'],

            'order_sn'       => $row['order_sn'],

            'extension_id'       => $row['extension_id'],

            'team_sign'       => $row['team_sign'],

            'goods_list' => $row['goods_list'],

            'team_first' => $row['team_first'],

            'team_status' => $row['team_status'],

            'square' => $row['square'],

            'team_num' => $row['team_num'],

            'goods_num' => count($row['goods_list']),

            'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),

            'order_status'   => $row['order_status'],

            'total_fee'      => price_format($row['total_fee'], false),

            'can_refund' => $row['can_refund'],
			'luckdraw_id'=>$row['luckdraw_id'],
            'handler'        => $row['handler']);

    }
	



    return $arr;

}

?>