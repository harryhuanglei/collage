<?php





if (!defined('IN_HHS'))



{



    die('Hacking attempt');



}



define('SSLCERT_PATH', ROOT_PATH ."wxpay/cacert/apiclient_cert.pem" );
define('SSLKEY_PATH', ROOT_PATH ."wxpay/cacert/apiclient_key.pem" );


$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/wxpay.php';







if (file_exists($payment_lang))



{



    global $_LANG;







    include_once($payment_lang);



}







/* 模块的基本信息 */



if (isset($set_modules) && $set_modules == TRUE)



{



    $i = isset($modules) ? count($modules) : 0;







    /* 代码 */



    $modules[$i]['code']    = basename(__FILE__, '.php');







    /* 描述对应的语言项 */



    $modules[$i]['desc']    = 'wxpay_desc';







    /* 是否支持货到付款 */



    $modules[$i]['is_cod']  = '0';







    /* 是否支持在线支付 */



    $modules[$i]['is_online']  = '1';







    /* 作者 */



    $modules[$i]['author']  = 'CAIYA TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://wx.qq.com';

    /* 版本号 */

    $modules[$i]['version'] = '0.0.1';

	/* 配置信息 */

    $modules[$i]['config']  = array(


        array('name' => 'wxpay_app_id',           'type' => 'text',   'value' => ''),



        array('name' => 'wxpay_app_secret',       'type' => 'text',   'value' => ''),



        array('name' => 'wxpay_mchid',        'type' => 'text',   'value' => ''),



        array('name' => 'wxpay_key',       'type' => 'text',   'value' => ''),
		
		
		array('name' => 'app_wxpay_app_id',           'type' => 'text',   'value' => ''),
		
		 array('name' => 'app_wxpay_mchid',        'type' => 'text',   'value' => ''),

		array('name' => 'app_wxpay_key',       'type' => 'text',   'value' => ''),
		
		array('name' => 'app_notifyurl',       'type' => 'text',   'value' => ''),


        array('name' => 'notifyurl',       'type' => 'text',   'value' => ''),



        array('name' => 'successurl',       'type' => 'text',   'value' => '')



    );

    return;
}



class WxPayConf_pub
{
	public $wxpay_app_id;
	public $wxpay_app_secret;
	public $wxpay_mchid;
	public $wxpay_key;
	public $notifyurl;
	public $successurl;
	public $curltimeout=30;



	/*

	const SSLCERT_PATH =  '/www/phpnow/wwwroot/test.xakc.net/vshop/wxpay/cacert/apiclient_cert.pem';

	

	const SSLKEY_PATH = '/www/phpnow/wwwroot/test.xakc.net/vshop/wxpay/cacert/apiclient_key.pem';

	 */

	

	

	function __construct() {



		$payment    = get_payment('wxpay');



//		



//    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment').



//           " WHERE pay_code = '$code' AND enabled = '1'";



//    $payment = $GLOBALS['db']->getRow($sql);



//



//    if ($payment)



//    {



//        $config_list = unserialize($payment['pay_config']);



//



//        foreach ($config_list AS $config)



//        {



//            $payment[$config['name']] = $config['value'];



//        }



//    }



//		



		



//		$payment['wxpay_app_id'] = 'wx0650d6f362b2a277';



//		$payment['wxpay_app_secret']='ad3bd950f6abb0833c9a3a85ad330b5';



//		$payment['wxpay_mchid']='1221660701';



//		$payment['wxpay_key']='884256c5194701168d5232dabbb50081';



//		$payment['notifyurl']='http://www.900lh.com/wxpay/demo/notify_url.php';



//		$payment['successurl']='http://www.900lh.com/mobile/user.php?act=order_info&id=';



		//var_dump($payment);exit;

		

		if(isset($payment)){
			$this->wxpay_app_id		=       $payment['wxpay_app_id'];
			$this->wxpay_app_secret	=       $payment['wxpay_app_secret'];
			$this->wxpay_mchid	=       $payment['wxpay_mchid'];
			$this->appwxpay_key 	=      $payment['app_wxpay_key'];
			$this->app_wxpay_app_id =      $payment['app_wxpay_app_id'];
			$this->app_wxpay_mchid  =      $payment['app_wxpay_mchid'];
			$this->wxpay_key	=       $payment['wxpay_key'];
			$this->notifyurl	=       $payment['notifyurl'];
			$this->app_notifyurl=      $payment['app_notifyurl'];
			
			$this->successurl	=       $payment['successurl'];
		}
		

	

	}



	/*



	//=======【基本信息设置】=====================================



	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看



	const APPID = 'wx4b56d1cfaa3a5574';  //wx4b56d1cfaa3a5574   //wx7bdd4eebef11c1a5  mp.wx 



	//受理商ID，身份标识



	const MCHID = '10018826';



	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看



	const KEY = 'zxsaqwedfcvgthg1247875414771fads';



	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看



	const APPSECRET = 'fcc5c94e31c6cd4d588195468d27f96f'; //15e36043bab60d5645b368cc7b9299f9



	



	//=======【JSAPI路径设置】===================================



	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面



	//const JS_API_CALL_URL = 'http://mp.shanmao.me/xm9902/mobile/order.php?act=done';


	//=======【证书路径设置】=====================================



	//证书路径,注意应该填写绝对路径



	const SSLCERT_PATH = 'D:\wnmp\www\xm9914\wxpay/cacert/apiclient_cert.pem';



	const SSLKEY_PATH = 'D:\wnmp\www\xm9914\wxpay/cacert/apiclient_key.pem';



	



	//=======【异步通知url设置】===================================



	//异步通知url，商户根据实际开发过程设定



	const NOTIFY_URL = 'http://121.40.148.177/xm9914/wxpay/demo/notify_url.php';



	//支付成功后跳转网址：



	const ZFSUCCESS_URL = 'http://121.40.148.177/xm9914/mobile/user.php?act=order_info&id=';







	//=======【curl超时设置】===================================



	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒



	const CURL_TIMEOUT = 30;*/



}







//include_once(ROOT_PATH."wxpay/WxPay.pub.config.php"); //支付信息配置文件。



include_once(ROOT_PATH."wxpay/WxPayPubHelper.php");
include_once(ROOT_PATH."wxpay/demo/log_.php");	

function refund($order_sn,$refund_fee,$refund_surplus = true,$refund_bonus = true){

    $out_trade_no = $order_sn;//输入需退款的订单号
    //商户退款单号，商户自定义，此处仅作举例
    $time_stamp=time();
    //$out_refund_no = "$out_trade_no"."$time_stamp";
    $out_refund_no = "$out_trade_no";
    //总金额需与订单号out_trade_no对应，demo中的所有订单的总金额为1分
    $order_info=order_info(0,$order_sn);
    if($refund_surplus && $order_info['surplus']>0.00)
    {
    	order_refund($order_info, 1, '余额支付退款', $order_info['surplus']);
    	if($order_info['surplus'] == $order_info['order_amount'])//表示已经退款了
    	{
    		return true;
    	}
    }



    $refund_bonus && $order_info['bonus_id'] && unuse_bonus($order_info['bonus_id']); 

    $refund_bonus && return_order_bonus($order_info['order_id']);


    if($order_info['money_paid'] == 0.00 )
	{
    	return true;
	}


    $total_fee = $order_info['money_paid']*100 ;
	$payment = payment_info($order_info['pay_id']);

	
	if($payment['pay_code'] =='alipay')
	{
		order_refund($order_info, 1, '支付宝支付退款', $order_info['money_paid']);
		return true;
	}

    //使用退款接口

    //设置必填参数
    //appid已填,商户无需重复填写
    //mch_id已填,商户无需重复填写
    //noncestr已填,商户无需重复填写
    //sign已填,商户无需重复填写
    $payment    = get_payment('wxpay');
	

	
	if($order_info['is_app_buy']==1)
	{
		
	    $refund = new Refundapp_pub();
		$refund->setParameter("appid", $payment['app_wxpay_app_id']);
		$refund->setParameter("mch_id", $payment['app_wxpay_mchid']);
		$refund->appwxpay_key	=       $payment['app_wxpay_key'];
		$refund->setParameter("transaction_id",$order_info['transaction_id']);//商户订单号
		// $refund->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$refund->setParameter("out_refund_no","$out_refund_no");//商户退款单号
		$total_fee = $order_info['wechat_total_fee']>0 ? $order_info['wechat_total_fee'] : $order_info['money_paid']*100;
		//$refund->setParameter("wxpay_key",$payment['appwxpay_key']);//总金额
		$refund->setParameter("total_fee","$total_fee");//总金额
		$refund->setParameter("refund_fee","$refund_fee");//退款金额
		$refund->setParameter("op_user_id",$payment['app_wxpay_mchid']);//操作员
		
		$ssl['APPSSLCERT_PATH'] = ROOT_PATH ."wxpay/appcacert/apiclient_cert.pem" ;
		$ssl['APPSSLKEY_PATH'] =ROOT_PATH ."wxpay/appcacert/apiclient_key.pem" ;

		
		$refundResult = $refund->getResult($ssl);
		
	}
	else
	{
	    $refund = new Refund_pub();
		$refund->setParameter("appid", "$payment[wxpay_app_id]");
		$refund->setParameter("mch_id", $payment['wxpay_mchid']);
		$refund->wxpay_key	=       $payment['wxpay_key'];
		$refund->setParameter("transaction_id",$order_info['transaction_id']);//商户订单号
		// $refund->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$refund->setParameter("out_refund_no","$out_refund_no");//商户退款单号
		$total_fee = $order_info['wechat_total_fee']>0 ? $order_info['wechat_total_fee'] : $order_info['money_paid']*100;
		$refund->setParameter("total_fee","$total_fee");//总金额
		$refund->setParameter("refund_fee","$refund_fee");//退款金额
		$refund->setParameter("op_user_id",$payment['wxpay_mchid']);//操作员
		$refundResult = $refund->getResult();
	}

    // echo "<pre>";

  //   print_r($refundResult);

    //商户根据实际情况设置相应的处理流程,此处仅作举例

    /*

    if($refundResult["return_code"] == "FAIL"){

        $status=0;

    }elseif($refundResult['result_code']=='SUCCESS'){

        $status=1;

    }

    

    $sql="select count(*) from ".$GLOBALS['hhs']->table("account_log")." where out_refund_no='$out_refund_no' ";

    if($GLOBALS['db']->getOne()>0){

    	$sql="update ".$GLOBALS['hhs']->table("account_log")." set transaction_id='$refundResult[transaction_id]', status='$status',note='".serialize($refundResult)."' where out_refund_no='$out_refund_no' ";

    	$GLOBALS['db']->query($sql);

    }else{

        

        $sql="insert into ".$GLOBALS['hhs']->table("account_log")." (order_sn,out_refund_no,refund_fee,transaction_id,status,note)".

            " values ('$refundResult[out_trade_no]','$refundResult[out_refund_no]','$refundResult[refund_fee]','$refundResult[transaction_id]','$status','".serialize($refundResult)."' )";

        $GLOBALS['db']->query($sql);

    }*/

    if($order_info['surplus']>0.00){
    

   //$sql="insert into ".$GLOBALS['hhs']->table("refund_log")." (order_sn,out_refund_no,refund_fee,transaction_id,status,note)".

    //    " values ('$order_sn','$out_refund_no','$refund_fee','$refundResult[transaction_id]',0,'".serialize($refundResult)."' )";

    //$GLOBALS['db']->query($sql);

    }

    

    if ($refundResult["return_code"] == "FAIL") {

    

       // echo "通信出错：".$refundResult['return_msg']."<br>";
	file_put_contents("mylog.log","---===".$refundResult['return_msg']."\r\n",FILE_APPEND);


       return false;

    

    }

    

    else{

       if($refundResult['result_code']=='SUCCESS'){



           return true;

       }

       

    /*

        echo "业务结果：".$refundResult['result_code']."<br>";

    

        echo "错误代码：".$refundResult['err_code']."<br>";

    

        echo "错误代码描述：".$refundResult['err_code_des']."<br>";

    

        echo "公众账号ID：".$refundResult['appid']."<br>";

    

        echo "商户号：".$refundResult['mch_id']."<br>";

    

        echo "子商户号：".$refundResult['sub_mch_id']."<br>";

    

        echo "设备号：".$refundResult['device_info']."<br>";

    

        echo "签名：".$refundResult['sign']."<br>";

    

        echo "微信订单号：".$refundResult['transaction_id']."<br>";

    

        echo "商户订单号：".$refundResult['out_trade_no']."<br>";

    

        echo "商户退款单号：".$refundResult['out_refund_no']."<br>";

    

        echo "微信退款单号：".$refundResult['refund_idrefund_id']."<br>";

    

        echo "退款渠道：".$refundResult['refund_channel']."<br>";

    

        echo "退款金额：".$refundResult['refund_fee']."<br>";

    

        echo "现金券退款金额：".$refundResult['coupon_refund_fee']."<br>";

    */

    }

}





/**



 * 类



 */



class wxpay



{



	






    /**



     * 生成支付代码



     * @param   array   $order      订单信息



     * @param   array   $payment    支付方式信息



     */



    function get_code($order, $payment,$direct=false)



    {



    if(!empty($_SESSION['xaphp_sopenid'])){

	    $openid=$_SESSION['xaphp_sopenid'];

	}else{

	    $openid=$_COOKIE['xaphp_sopenid'];

	}

	if(empty($openid)){

		return "";

	}



	//$openid = "oSxZVuNcC7qArMKsIgPeHeoHOydA";



	$unifiedOrder = new UnifiedOrder_pub();	



	$conf = new WxPayConf_pub();	



	//$returnrul = $conf->successurl.$order["order_id"];

    if($order['extension_code']=='team_goods'){

        $returnrul = $conf->successurl.$order["order_id"]."&team=1";

    }else{

        $returnrul = $conf->successurl.$order["order_id"];

    }

	//var_dump($returnrul);

    //http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']

	//exit;  



	$unifiedOrder->setParameter("openid","$openid");//商品描述



	$unifiedOrder->setParameter("body",$order['order_sn']);//商品描述



	//自定义订单号，此处仅作举例



	$timeStamp = time();



	//$out_trade_no = WxPayConf_pub::APPID."$timeStamp";



	$unifiedOrder->setParameter("out_trade_no",$order['order_sn']);//商户订单号 



	$unifiedOrder->setParameter("total_fee",floatval($order['order_amount']) * 100);//总金额



	$unifiedOrder->setParameter("notify_url",$conf->notifyurl);//通知地址 



	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型



	$unifiedOrder->setParameter("is_subscribe","Y");//交易类型

	if($order['goods_name'])

	$unifiedOrder->setParameter("body",mb_substr($order['goods_name'],0,30,'utf-8') );

	//非必填参数，商户可根据实际情况选填


	$prepay_id = $unifiedOrder->getPrepayId();



	$jsApi = new JsApi_pub();



	$jsApi->setPrepayId($prepay_id);



	$jsApiParameters = $jsApi->getParameters();



	/*

	if($direct){

	    $pay_online=$jsApi->getbutton2($jsApiParameters,$returnrul,$order);    

	}else{*/

	    $pay_online=$jsApi->getbutton($jsApiParameters,$returnrul,$order);    

	    

	//}

     return $pay_online;
}



 

function get_code2($order, $payment,$multi = array(),$notify_url='')



{

	//$uid = $_SESSION['user_name'];



	//$wxid = uidrwxid($uid);



	//var_dump($uid);



	//var_dump($wxid);



	//echo return_url('wxpay');



	//  @$openid=$_COOKIE['sopenid'];



	//echo $_COOKIE['sopenid']."dfdfds";exit;



	//echo $uid;exit;



	$openid=$_SESSION['xaphp_sopenid'];//测试



	if(empty($openid)){



		return "";



	}


	//$openid = "oSxZVuNcC7qArMKsIgPeHeoHOydA";



	$unifiedOrder = new UnifiedOrder_pub();



	$conf = new WxPayConf_pub();



	

	if($order['share_pay_type']>0){

	    

	    $returnrul = "share_pay.php?act=success&id=".$order["order_id"];

	}

	else{

	    if($order['extension_code']=='team_goods'){
				if($luckdraw_id!=0){
					$returnrul = $conf->successurl.$order["order_id"]."&team=1&luckdraw_id=".$luckdraw_id;
				}else{
					$returnrul = $conf->successurl.$order["order_id"]."&team=1";
				}	
	       

	    }else{

	        $returnrul = $conf->successurl.$order["order_id"];

	    }

	}

	if($_SESSION['is_luck'])

		$returnrul = "userbao.php?order_id=".$order["order_id"];

	if(!empty($multi))

	{

		$returnrul = "user.php?act=order_list";

		$unifiedOrder->setParameter("attach",join(',',$multi));//分单order_id

	}

	//var_dump($returnrul);



	//exit;



	$unifiedOrder->setParameter("openid","$openid");//商品描述



	$unifiedOrder->setParameter("body",$order['order_sn']);//商品描述



	//自定义订单号，此处仅作举例



	$timeStamp = time();







	//$out_trade_no = WxPayConf_pub::APPID."$timeStamp";



	$unifiedOrder->setParameter("out_trade_no",$order['order_sn']);//商户订单号



	$unifiedOrder->setParameter("total_fee",floatval($order['order_amount']) * 100);//总金额

    

    $url = $notify_url =='' ? $conf->notifyurl : $notify_url; //是否自定义通知地址

	$unifiedOrder->setParameter("notify_url",$url);//通知地址



	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型

	

	$unifiedOrder->setParameter("is_subscribe","Y");//交易类型

	if($order['goods_name'])

	$unifiedOrder->setParameter("body",mb_substr($order['goods_name'],0,30,'utf-8') );

	

	//非必填参数，商户可根据实际情况选填



	//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号



	//$unifiedOrder->setParameter("device_info","XXXX");//设备号



	//$unifiedOrder->setParameter("attach","XXXX");//附加数据



	//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间



	//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间



	//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记



	//$unifiedOrder->setParameter("openid","XXXX");//用户标识



	//$unifiedOrder->setParameter("product_id","XXXX");//商品ID





	$prepay_id = $unifiedOrder->getPrepayId();



	$jsApi = new JsApi_pub();



	$jsApi->setPrepayId($prepay_id);



	$jsApiParameters = $jsApi->getParameters();



	

	return array('jsApiParameters'=>json_decode($jsApiParameters,true),

			'returnrul'=>$returnrul) ;

	



	//return $pay_online;



} 

function get_code3($order,$luckdraw_id, $payment,$multi = array(),$notify_url='')

{
	$openid=$_SESSION['xaphp_sopenid'];//测试

	if(empty($openid)){

		return "";

	}

	$unifiedOrder = new UnifiedOrder_pub();

	$conf = new WxPayConf_pub();

	if($order['share_pay_type']>0){

		$returnrul = "share_pay.php?act=success&id=".$order["order_id"];

	}

	else{

				$returnrul = $conf->successurl.$order["order_id"]."&team=1&luckdraw_id=".$luckdraw_id;

	}

	if($_SESSION['is_luck'])

		$returnrul = "userbao.php?order_id=".$order["order_id"];

	if(!empty($multi))

	{

		$returnrul = "user.php?act=order_list";

		$unifiedOrder->setParameter("attach",join(',',$multi));//分单order_id

	}

	$unifiedOrder->setParameter("openid","$openid");//商品描述

	$unifiedOrder->setParameter("body",$order['order_sn']);//商品描述

	//自定义订单号，此处仅作举例

	$timeStamp = time();

	$unifiedOrder->setParameter("out_trade_no",$order['order_sn']);//商户订单号

	$unifiedOrder->setParameter("total_fee",floatval($order['order_amount']) * 100);//总金额

	$url = $notify_url =='' ? $conf->notifyurl : $notify_url; //是否自定义通知地址

	$unifiedOrder->setParameter("notify_url",$url);//通知地址

	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型

	$unifiedOrder->setParameter("is_subscribe","Y");//交易类型

	if($order['goods_name'])

		$unifiedOrder->setParameter("body",mb_substr($order['goods_name'],0,30,'utf-8') );

	$prepay_id = $unifiedOrder->getPrepayId();



	$jsApi = new JsApi_pub();



	$jsApi->setPrepayId($prepay_id);



	$jsApiParameters = $jsApi->getParameters();



	return array('jsApiParameters'=>json_decode($jsApiParameters,true),

			'returnrul'=>$returnrul) ;
}
//微信app支付
function app_wxpay($order, $payment,$multi = array(),$notify_url='')

{
	
	
	

	$unifiedOrder = new UnifiedOrderapp_pub();

	if(!empty($multi))
	{
		$returnrul = "user.php?act=order_list";
		$unifiedOrder->setParameter("attach",join(',',$multi));//分单order_id
	}

	
	$conf = new WxPayConf_pub();
	if($order['goods_name'])
	$unifiedOrder->setParameter("body",mb_substr($order['goods_name'],0,30,'utf-8') );
	//自定义订单号，此处仅作举例
	$timeStamp = time();
	
	$unifiedOrder->setParameter("out_trade_no",$order['order_sn']);//商户订单号
	$unifiedOrder->setParameter("total_fee",floatval($order['order_amount']) * 100);//总金额
	$url = $notify_url =='' ? $conf->app_notifyurl : $notify_url; //是否自定义通知地址
	
	$unifiedOrder->setParameter("notify_url",$url);//通知地址
	$unifiedOrder->setParameter("trade_type","APP");//交易类型
	$result = $unifiedOrder->createXml();
	//$prepay_id = $unifiedOrder->getPrepayId();
	//$jsApiParameters = $jsApi->getParameters();
	//$jsApiParameters = json_decode($result,true);
	
	
	
	$timeStamp = time();
	$result_a = array(
			  'appid'=>$result['appid'],
			  'partnerid'=>$result['mch_id'],
			  'prepayid'=>$result['prepay_id'],
			  'noncestr'=>$result['nonce_str'],
			  'timestamp'=>$timeStamp,
			  'package'=>'Sign=WXPay'
	);
	
	
	$sign = $unifiedOrder->getagensigntwo($result_a);

	$result_a['sign'] = $sign;

	
//	$jsApiParameters['prepay_id'] = $prepay_id;
//	unset($jsApiParameters['appId']);
//	$jsApiParameters['appid'] = $payment['app_wxpay_app_id'];
//	$jsApiParameters['wxpay_mchid'] = $payment['app_wxpay_mchid'];



	return array('jsApiParameters'=>$result_a,
	'returnrul'=>$returnrul) ;
}


	



	 /**



	 * 是否支持微信支付



	 * @return bool



	 */



	public function is_show_pay($agent) {



		$ag1  = strstr($agent,"MicroMessenger");



		$ag2 = explode("/",$ag1);



		$ver = floatval($ag2[1]);



		if ( $ver < 5.0 || empty($aid) ){



			return false;



    	}else{



    		return true;



    	}



	}   



	



	



	/**



	* 接受通知处理订单。



	* @param undefined $log_id



	* 20141125



*/



	function respond()



    { 



	$notify = new Notify_pub();







	//存储微信的回调



	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	



	$notify->saveData($xml);



	



	if($notify->checkSign() == FALSE){



		$notify->setReturnParameter("return_code","FAIL");//返回状态码



		$notify->setReturnParameter("return_msg","签名失败");//返回信息



	}else{



		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码



	}



	$returnXml = $notify->returnXml();



	echo $returnXml;



	



	$log_ = new Log_();



	$log_name=ROOT_PATH."wxpay/demo/notify_url.log";//log文件路径



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



			$log_id=$order["out_trade_no"];



			order_paid($log_id);



			//$wxpay = new wxpay();



			//$wxpay->respond($order["out_trade_no"]);			



			//此处应该更新一下订单状态，商户自行增删操作



			$log_->log_result($log_name,"【支付成功】:\n".$order["out_trade_no"]."\n");



		}



		



		



	}



    }



	



}











?>