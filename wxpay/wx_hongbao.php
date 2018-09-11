<?php

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}
include_once ROOT_PATH . 'includes/lib_payment.php';

include_once ROOT_PATH . 'includes/modules/payment/wxpay.php';
/**
* 微信红包类
*/
class hongbao extends Wxpay_client_pub
{
	var $weixin_config;

	function __construct() 

	{

		parent::__construct(); 

		//设置接口链接

		$this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";

		//设置curl超时时间

		$this->curl_timeout = $this->curltimeout;

		$this->weixin_config = $this->getWeixinConfig();
	}

	function getWeixinConfig(){
		return $GLOBALS['db']->getRow("SELECT `nick_name`,`send_name`,`wishing`,`act_name`,`remark`,`client_ip` FROM ".$GLOBALS['hhs']->table('weixin_config')." WHERE `id` = 1");
	}

	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	function check_sign_parameters(){
		if( $this->parameters["nonce_str"]    == null || 
			$this->parameters["mch_billno"]   == null || 
			$this->parameters["mch_id"]       == null || 
			$this->parameters["wxappid"]      == null || 
			$this->parameters["nick_name"]    == null || 
			$this->parameters["send_name"]    == null ||
			$this->parameters["re_openid"]    == null || 
			$this->parameters["total_amount"] == null || 
			$this->parameters["max_value"]    == null || 
			$this->parameters["total_num"]    == null || 
			$this->parameters["wishing"]      == null || 
			$this->parameters["client_ip"]    == null || 
			$this->parameters["act_name"]     == null || 
			$this->parameters["remark"]       == null || 
			$this->parameters["min_value"]    == null
			)
		{
			return false;
		}
		return true;
	}

	function send($re_openid,$total_amount,$min_value,$max_value,$total_num = 1,$act_name = false){

		$this->setParameter("re_openid",    $re_openid);//相对于医脉互通的openid
		$this->setParameter("total_amount", $total_amount);//付款金额，单位分
		$this->setParameter("min_value",    $min_value);//最小红包金额，单位分
		$this->setParameter("max_value",    $max_value);//最大红包金额，单位分
		$this->setParameter("total_num", 	$total_num);//红包収放总人数
		$this->setParameter("nonce_str",    $this->createNoncestr());//随机字符串，不长于32位
		$this->setParameter("mch_billno",   $this->wxpay_mchid.date('YmdHis').rand(1000, 9999));//订单号
		$this->setParameter("mch_id",       $this->wxpay_mchid);//商户号
		$this->setParameter("wxappid",      $this->wxpay_app_id);
		$this->setParameter("nick_name",    $this->weixin_config['nick_name']);//提供方名称
		$this->setParameter("send_name",    $this->weixin_config['send_name']);//红包发送者名称
		$this->setParameter("wishing", 		$this->weixin_config['wishing']);//红包祝福诧
		$this->setParameter("client_ip", 	$this->weixin_config['client_ip']);//调用接口的机器 Ip 地址
		$this->setParameter("act_name", 	$act_name?$act_name:$this->weixin_config['act_name']);//活劢名称
		$this->setParameter("remark", 		$this->weixin_config['remark']);//备注信息

		$this->setParameter('sign',         $this->getSign($this->parameters));//签名

		//检查参数是否完整
		if(! $this->check_sign_parameters()){
			return false;
		}
		$postXml     = $this->arrayToXml($this->parameters);
		$url         = $this->url;
		
		$responseXml = $this->postXmlSSLCurl($postXml,$url);
		
		//echo $responseXml;exit;
		
		$responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		//print_r($responseObj);exit;
		 return $responseObj->return_code ;
		
		//return $responseObj->return_code == 'SUCCESS' && $responseObj->result_code == 'SUCCESS';
	}
}