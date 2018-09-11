<?php
/**
 * 充值通知接口demo
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
 include_once ROOT_PATH."languages/zh_cn/wx_msg.php";
//使用通用通知接口
$notify = new Notify_pub();

//var_dump($notify);

//存储微信的回调

$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
$notify->saveData($xml);
if($notify->appcheckSign() == FALSE){
    $notify->setReturnParameter("return_code","FAIL");//返回状态码
    $notify->setReturnParameter("return_msg","签名失败");//返回信息
}else{
    $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
    $notify->setReturnParameter("return_msg","OK");
}
$returnXml = $notify->returnXml();
echo $returnXml;
//==商户根据实际情况设置相应的处理流程，此处仅作举例=======

$log_ = new Log_();
$log_name="./notify_url_app.log";//log文件路径

$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
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
        $total_fee = intval($order["total_fee"])/100;//微信支付费用
		$log_id=$order["out_trade_no"];
		order_paid($log_id);

  }



    //商户自行增加处理流程,

    //例如：更新订单状态

    //例如：数据库操作

    //例如：推送支付完成信息

}

?>