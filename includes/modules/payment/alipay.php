<?php

/**
 * HHSHOP 支付宝插件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.hhshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: douqinghua $
 * $Id: alipay.php 17217 2011-01-19 06:29:08Z douqinghua $
 */

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/alipay.php';

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
    $modules[$i]['desc']    = 'alipay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'HHSHOP TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.alipay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.2';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'alipay_account',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_key',               'type' => 'text',   'value' => ''),
        array('name' => 'alipay_partner',           'type' => 'text',   'value' => '')
    );

    return;
}

/**
 * 类
 */
class alipay
{

    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function alipay()
    {
    }

    function __construct()
    {
        $this->alipay();
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment,$notify_url='')
    {
        if (!defined('EC_CHARSET'))
        {
            $charset = 'utf-8';
        }
        else
        {
            $charset = EC_CHARSET;
        }




    $alipay_config=array();
    $alipay_config['partner']		= $payment['alipay_partner'];
    //安全检验码，以数字和字母组成的32位字符
    //如果签名方式设置为“MD5”时，请设置该参数
    $alipay_config['key']			= $payment['alipay_key'];
    //商户的私钥（后缀是.pen）文件相对路径
    //如果签名方式设置为“0001”时，请设置该参数
    //$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';
    //支付宝公钥（后缀是.pen）文件相对路径
    //如果签名方式设置为“0001”时，请设置该参数
   // $alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';
    //签名方式 不需修改
    $alipay_config['sign_type']    = 'MD5';

    //字符编码格式 目前支持 gbk 或 utf-8
    $alipay_config['input_charset']= 'utf-8';
    $alipay_config['cacert']='';
   // $alipay_config['cacert']    = ROOT_PATH .'mobile/includes/modules/cacert.pem';

    //ca证书路径地址，用于curl中ssl校验
    //请保证cacert.pem文件在当前文件夹目录中
    //$alipay_config['cacert']    = getcwd().'\\cacert.pem';

    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport']    = 'http';

    require_once(ROOT_PATH ."includes/modules/lib/alipay_submit.class.php");


$format = "xml";
//必填，不需要修改

//返回格式
$v = "2.0";
//必填，不需要修改

//请求号
$req_id = date('Ymdhis');
//必填，须保证每次请求都是唯一

//**req_data详细信息**
//服务器异步通知页面路径
if($notify_url==''){
    $notify_url =$GLOBALS['hhs']->url().'alipay/notify_url.php';
	$out_trade_no = $order['order_sn'].time();
	$call_back_url = $GLOBALS['hhs']->url().'alipay/alipay.php';
}else{
    $notify_url =$GLOBALS['hhs']->url().'alipay/notify_url_chong.php';
	$call_back_url = $GLOBALS['hhs']->url().'alipay/alipay_chong.php';
	$out_trade_no = $order['order_sn'];
}


//需http://格式的完整路径，不允许加?id=123这类自定义参数
//页面跳转同步通知页面路径


$seller_email = $payment['alipay_account'];
//必填
//商户订单号

//商户网站订单系统中唯一订单号，必填
//订单名称
$subject =$order['body'];
//必填

//付款金额
$total_fee = $order['order_amount'];
//必填
$body = $order['body'];
//请求业务参数详细
    $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><body>'.$body.'</body></direct_trade_create_req>';

//构造要请求的参数数组，无需改动
$para_token = array(
		"service" => "alipay.wap.trade.create.direct",
		"partner" =>  trim($alipay_config['partner']),
		"sec_id" =>  trim($alipay_config['sign_type']),
		"format"	=> $format,
		"v"	=> $v,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);




//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($para_token);

//URLDECODE返回的信息
$html_text = urldecode($html_text);

//解析远程模拟提交后返回的信息
$para_html_text = $alipaySubmit->parseResponse($html_text);


//获取request_token
$request_token = $para_html_text['request_token'];


/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

//业务详细
$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
//必填

//构造要请求的参数数组，无需改动
$parameter = array(
		"service" => "alipay.wap.auth.authAndExecute",
		"partner" => trim($alipay_config['partner']),
		"v"	=> $v,
		"sec_id" => trim($alipay_config['sign_type']),
		"format"	=> $format,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);


//构造要请求的参数数组，无需改动
//$parameter1 = array(
//		"service"       => 'alipay.wap.trade.create.direct',
//		"partner"       => $alipay_config['partner'],
//		"seller_id"  => $alipay_config['seller_id'],
//		"payment_type"	=> 1,
//		"notify_url"	=> $notify_url,
//		"return_url"	=> $call_back_url,
//		"_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
//		"out_trade_no"	=> $out_trade_no,
//		"subject"	=> $subject,
//		"total_fee"	=> $total_fee,
//		"show_url"	=> $call_back_url,
//		//"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
//		"body"	=> $body,
//		//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
//        //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。
//		
//);




$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');

//var_dump($html_text);exit;
//return $html_text; 


        return $html_text; 
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        
        $payment  = get_payment("alipay");
        $payment = unserialize_config($payment['pay_config']);
        $alipay_config=array();
        $alipay_config['partner']		= $payment['alipay_partner'];
        //安全检验码，以数字和字母组成的32位字符
        //如果签名方式设置为“MD5”时，请设置该参数
        $alipay_config['key']			= $payment['alipay_key'];
        //商户的私钥（后缀是.pen）文件相对路径
        //如果签名方式设置为“0001”时，请设置该参数
        $alipay_config['private_key_path']	= '';
         //$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';
        //支付宝公钥（后缀是.pen）文件相对路径
        //如果签名方式设置为“0001”时，请设置该参数
        $alipay_config['ali_public_key_path']= '';   
        //$alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';
        //签名方式 不需修改
        $alipay_config['sign_type']    = 'MD5';
    
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= 'utf-8';
        //$alipay_config['cacert']    = ROOT_PATH .'mobile/includes/modules/cacert.pem';
         $alipay_config['cacert']    =''; 
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        //$alipay_config['cacert']    = getcwd().'\\cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http

        $alipay_config['transport']    = 'http';

        require_once(ROOT_PATH ."includes/modules/lib/alipay_notify.class.php");

        $alipayNotify = new AlipayNotify($alipay_config);

        $verify_result = $alipayNotify->verifyReturn();
        
        if($verify_result)
        {
        
            $out_trade_no = trim($_GET['out_trade_no']);
            $order_sn=trim(substr($out_trade_no,0,13));
            $log_id=get_order_id_by_sn($order_sn);
            order_paid($log_id);
            //$sql = "SELECT l.`log_id` FROM " . $GLOBALS['hhs']->table('order_info')." as info LEFT JOIN ". $GLOBALS['hhs']->table('pay_log') ." as l  ON l.order_id=info.order_id        WHERE info.order_sn = '$order_sn'";
            //$order_log_id = $GLOBALS['db']->getOne($sql);
            return true;
        }
        else
        {
            return false;
        }

    }
	
	
	
	
	
    /**
     * 响应操作
     */
    function respond_chong()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        
        $payment  = get_payment("alipay");
        $payment = unserialize_config($payment['pay_config']);
        $alipay_config=array();
        $alipay_config['partner']		= $payment['alipay_partner'];
      	$alipay_config['key']			= $payment['alipay_key'];
        $alipay_config['private_key_path']	= '';
        $alipay_config['ali_public_key_path']= '';   
        $alipay_config['sign_type']    = 'MD5';
        $alipay_config['input_charset']= 'utf-8';
        $alipay_config['cacert']    =''; 
        $alipay_config['transport']    = 'http';
        require_once(ROOT_PATH ."includes/modules/lib/alipay_notify.class.php");
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result)
        {
		
        
            $out_trade_no = trim($_GET['out_trade_no']);
           // $log_id=get_order_id_by_sn($out_trade_no);
			//echo $out_trade_no."----------".$log_id;exit;
		
            order_paid($out_trade_no);
            //$sql = "SELECT l.`log_id` FROM " . $GLOBALS['hhs']->table('order_info')." as info LEFT JOIN ". $GLOBALS['hhs']->table('pay_log') ." as l  ON l.order_id=info.order_id        WHERE info.order_sn = '$order_sn'";
            //$order_log_id = $GLOBALS['db']->getOne($sql);
            return true;
        }
        else
        {
            return false;
        }

    }	
	
	
	
	
	
	
	
	
}

?>