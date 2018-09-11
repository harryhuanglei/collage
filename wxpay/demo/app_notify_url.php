<?php
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
 * 这里举例使用log文件形式记录回调信息。
*/

define('IN_HHS', true);
require('../../includes/init2.php');
require('../../includes/lib_payment.php');
require('../../includes/lib_order.php');
require('../../includes/modules/payment/wxpay.php');

//使用通用通知接口
$notify = new Notify_pub();
//var_dump($notify);
//存储微信的回调
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];

//$xml ='<xml><appid><![CDATA[wxec66b7095c2cec16]]></appid>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1409912402]]></mch_id>
//<nonce_str><![CDATA[yrdj4picno8ew8gfntifvv59gd81f74x]]></nonce_str>
//<openid><![CDATA[oBholwARBwwkalPMOB-8535kCxEw]]></openid>
//<out_trade_no><![CDATA[2016120156305]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[CB185166881BC2D409F587A13D2CF7D1]]></sign>
//<time_end><![CDATA[20161201164025]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[APP]]></trade_type>
//<transaction_id><![CDATA[4004382001201612011407605150]]></transaction_id>
//</xml>';

$notify->saveData($xml);

//$log_ = new Log_();
//$log_name="./notify_url_app.log";//log文件路径
//$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");


if($notify->appcheckSign() == FALSE){

	$notify->setReturnParameter("return_code","FAIL");//返回状态码

	$notify->setReturnParameter("return_msg","签名失败");//返回信息

}else{

	$notify->setReturnParameter("return_code","SUCCESS");//设置返回码

	$notify->setReturnParameter("return_msg","OK");
}


$returnXml = $notify->returnXml();

echo $returnXml;

	if($notify->appcheckSign() == TRUE)

	{
	    
		if ($notify->data["return_code"] == "FAIL") {

			//此处应该更新一下订单状态，商户自行增删操作

			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");

		}

		elseif($notify->data["result_code"] == "FAIL"){

			//此处应该更新一下订单状态，商户自行增删操作

			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");

		}

		else{
            
		    
			$order = $notify->getData();
		
			
			$transaction_id = $order["transaction_id"];//微信订单号
			$total_fee = $order["total_fee"];//微信支付费用




if(isset($order['attach']) && !empty($order['attach']))
{
	$attach = explode(',', $order['attach']);
	foreach ($attach as $order_id) {
			$sql="select order_sn from ".$hhs->table('order_info')." where order_id='$order_id' ";
			$orsn =  $db->getOne($sql);
			$odid=get_order_id_by_sn($orsn); 

			//确保只发一次
			$sql="select pay_status from ".$hhs->table('order_info')." where order_sn='$orsn' ";
			$pay_status=$db->getOne($sql);
			
			order_paid($odid);
			
			$sql="update ".$GLOBALS['hhs']->table('order_info')." set transaction_id='$transaction_id',order_status=1,pay_status=2,wechat_total_fee='$total_fee' where order_sn='$orsn' ";
			$GLOBALS['db']->query($sql);
			
			//确保只发一次
			if($pay_status!=2){
			    pay_team_action($orsn);
			}
			
			//代付
			$sql="select * from ".$hhs->table('order_info')."  where order_sn='".$orsn."'";
			$order_info=$db->getRow($sql);
			if($order_info['share_pay_type']>0){

			    $sql="update ". $hhs->table('share_pay_info') ." set is_paid=1 where   order_id=".$order_info['order_id'];
			    
			    $db->query($sql);
			    
			}
	}
}
else{
			$orsn = $order["out_trade_no"];
			
			$odid=get_order_id_by_sn($orsn);
			
			//确保只发一次
			$sql="select pay_status from ".$hhs->table('order_info')." where order_sn='$orsn' ";
			$pay_status=$db->getOne($sql);
			
			order_paid($odid);
			
			$sql="update ".$GLOBALS['hhs']->table('order_info')." set transaction_id='$transaction_id',order_status=1,pay_status=2,wechat_total_fee='$total_fee' where order_sn='$orsn' ";
			$GLOBALS['db']->query($sql);
			
			//确保只发一次
			if($pay_status!=2){
			    pay_team_action($orsn);
			}
			
			//代付
			$sql="select * from ".$hhs->table('order_info')."  where order_sn='".$orsn."'";
			$order_info=$db->getRow($sql);
			if($order_info['share_pay_type']>0){

			    $sql="update ". $hhs->table('share_pay_info') ." set is_paid=1 where   order_id=".$order_info['order_id'];
			    
			    $db->query($sql);
			    
			}

			//此处应该更新一下订单状态，商户自行增删操作

			//$log_->log_result($log_name,"【支付成功】:\n".$order["out_trade_no"]."\n");
}
		}

		

		//商户自行增加处理流程,

		//例如：更新订单状态

		//例如：数据库操作

		//例如：推送支付完成信息

	}




?>