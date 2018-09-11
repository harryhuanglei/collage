<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/../includes/init2.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
require_once(ROOT_PATH . 'includes/lib_image.php');
require_once(ROOT_PATH . 'includes/lib_payment.php');
require_once(ROOT_PATH . 'includes/modules/payment/wxpay.php');
echo $_SESSION['xaphp_sopenid'];die;
$sq = "select value from ".$hhs->table('shop_config')." where id=948";
        $share = $db->getOne($sq);
echo $share;die;
$img = new image();
$file = ROOT_PATH.'/images/201604/1459817097223394630.png';
$type = end(explode('.',$file)); //'http://'.$_SERVER['HTTP_HOST']."/"
   $end = "/images/share/".gmtime().".".$type;
    $img2 = ROOT_PATH.$end;
    $show = 'http://'.$_SERVER['HTTP_HOST'].$end;
    $water = ROOT_PATH."/".'images/shuiyin.jpg';
$img->param($file)->water($img2,$water,9);
die;

$fp=fopen('a.txt','a');
fputs($fp,date('Y-m-d H:i:s')."\r\n");
fclose($fp);

$sql="select * from ".$hhs->table('order_info')." where extension_code='team_goods' and (team_status=3 or team_status=1)  ";
$order_list=$db->getAll($sql);
if(!empty($order_list)){
    foreach($order_list as $v){
        if($v['team_status']==1){
            $sql="select pay_time from ".$hhs->table('order_info')." where order_id=".$v['team_sign'];
            $pay_time=$db->getOne($sql);
            if(gmtime()-$pay_time >24*3600 ){

                $sql="update ".$GLOBALS['hhs']->table('order_info')." set team_status=3,order_status=2 where  team_sign=".$v['team_sign'];
                $GLOBALS['db']->query($sql);
                 
                $order_sn=$v['order_sn'];
                $r=refund($order_sn,$v['money_paid']*100);
                if($r){
                    $arr=array();
                    $arr['order_status']    = OS_RETURNED;
                    $arr['pay_status']  = PS_REFUNDED;
                    $arr['shipping_status'] = 0;
                    $arr['team_status']  = 3;
                    $arr['money_paid']  = 0;
                    $arr['order_amount']= $v['money_paid'] + $v['order_amount'];
                    update_order($v['order_id'], $arr);
                    
                    $user_id=$v['user_id'];
                    $wxch_order_name='refund';
                    $team_sign=$v['team_sign'];
                    $order_id=$v['order_id'];
                    require_once(ROOT_PATH . 'wxch_order.php');
                    
                }        
            }
        }
        if($v['team_status']==3&&$v['pay_status']==2){
            $order_sn=$v['order_sn'];
            $r=refund($order_sn,$v['money_paid']*100);
            if($r){
                $arr=array();
                $arr['order_status']    = OS_RETURNED;
                $arr['pay_status']  = PS_REFUNDED;
                $arr['shipping_status'] = 0;
                $arr['team_status']  = 3;
                $arr['money_paid']  = 0;
                $arr['order_amount']= $v['money_paid'] + $v['order_amount'];
                update_order($v['order_id'], $arr);
                
                $user_id=$v['user_id'];
                $wxch_order_name='refund';
                $team_sign=$v['team_sign'];
                $order_id=$v['order_id'];
                require_once(ROOT_PATH . 'wxch_order.php');
            }

            
        }
           
            /*
            //还剩10个小时的时候发送消息提醒
            if(gmtime()-$pay_time<11*3600 && gmtime()-$pay_time>9*3600){
                $user_id=$v['user_id'];
                $wxch_order_name='warn';
                $sql="select goods_name from ".$hhs->table('goods')." where goods_id=".$v['extension_id'];
                $goods_name=$db->getOne($sql);
                $team_sign=$v['team_sign'];
                require_once(ROOT_PATH . 'wxch_order.php');
            }*/
            //自动退款

    }

}

?>

