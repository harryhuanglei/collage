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



//$xml ='<xml><appid><![CDATA[wx6e19992c431fe18b]]></appid>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[100]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[Y]]></is_subscribe>
//<mch_id><![CDATA[1283826401]]></mch_id>
//<nonce_str><![CDATA[atawuv0n11mb4vhp9wkpywy7coca6e32]]></nonce_str>
//<openid><![CDATA[oFw1nxOOCPQSbXzBNYOQPW2cxu-g]]></openid>
//<out_trade_no><![CDATA[14842044739487]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[B20861AD656E91255BD55ACA9351C63F]]></sign>
//<time_end><![CDATA[20170112150119]]></time_end>
//<total_fee>100</total_fee>
//<trade_type><![CDATA[JSAPI]]></trade_type>
//<transaction_id><![CDATA[4007532001201701126065089823]]></transaction_id>
//</xml>
//';

$notify->saveData($xml);
/*
	if(is_array($xml)){
	    $str=serialize($xml);
	}else{
	    $str=$xml;
	}
	$fp=fopen("ab.txt","w");
	fputs($fp, $str);
	fclose($fp);*/
//验证签名，并回应微信。

//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，

//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，

//尽可能提高通知的成功率，但微信不保证通知最终能成功。

if($notify->checkSign() == FALSE){

    $notify->setReturnParameter("return_code","FAIL");//返回状态码

    $notify->setReturnParameter("return_msg","签名失败");//返回信息

}else{

    $notify->setReturnParameter("return_code","SUCCESS");//设置返回码

    $notify->setReturnParameter("return_msg","OK");
}


$returnXml = $notify->returnXml();

echo $returnXml;
//==商户根据实际情况设置相应的处理流程，此处仅作举例=======



//以log文件形式记录回调信息

$log_ = new Log_();



$log_name="./notify_url.log";//log文件路径

$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");



if($notify->checkSign() == TRUE)

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

        if(isset($order['openid']) && !empty($order['openid']))
        {

                $openid = $order['openid'];
				
				$transaction_id = $order["transaction_id"];//微信订单号
        		$total_fee = intval($order["total_fee"])/100;//微信支付费用
				$log_id=$order["out_trade_no"];
			
				order_paid($log_id);


                //$sql = "select uname,user_id from ".$GLOBALS['hhs']->table('users')." where openid='$openid'";
//                $result = $GLOBALS['db']->getRow($sql);
//                $uid = $result['user_id'];
//                $uname = $result['uname'];
//                $sql="update ".$GLOBALS['hhs']->table('users')." set user_money=user_money+$total_fee where openid='$openid' ";
//                $rs = $GLOBALS['db']->query($sql);
//                if($rs){
//                    $sql = "insert into ".$GLOBALS['hhs']->table('account_log')."(user_id,user_money,change_time,change_desc,change_type) values('$uid','$total_fee','".gmtime()."','会员余额充值','0')";
//                    $GLOBALS['db']->query($sql);

                    $url = 'user.php?act=account_detail';

                    $desc = "充值金额：".$total_fee."\r\n充值状态：已充值";
 
                    $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);

                    $weixin->send_wxmsg($openid, '充值成功' , $url , $desc);
              //  }


        }
    }



    //商户自行增加处理流程,

    //例如：更新订单状态

    //例如：数据库操作

    //例如：推送支付完成信息

}

?>