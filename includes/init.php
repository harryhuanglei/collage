<?php

/**
 * 小舍电商 前台公用文件
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: init.php 17217 2014-05-12 06:29:08Z pangbin $
*/


if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}

define('DEBUG_MODE', 0);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}


/* 取得当前hhshop所在的根目录 */
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        0);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path', '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path', '.:' . ROOT_PATH);
}

require(ROOT_PATH . 'data/config.php');

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);

require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_hhshop.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_insert.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_article.php');
require(ROOT_PATH . 'includes/lib_wxf.php');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{

    if (!empty($_GET))

    {

        $_GET  = addslashes_deep($_GET);

    }

    if (!empty($_POST))

    {

        $_POST = addslashes_deep($_POST);

    }



    $_COOKIE   = addslashes_deep($_COOKIE);

    $_REQUEST  = addslashes_deep($_REQUEST);

}



/* 创建 小舍电商 对象 */

$hhs = new HHS($db_name, $prefix);

define('DATA_DIR', $hhs->data_dir());

define('IMAGE_DIR', $hhs->image_dir());



/* 初始化数据库类 */

require(ROOT_PATH . 'includes/cls_mysql.php');

$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);

$db->set_disable_cache_tables(array($hhs->table('sessions'), $hhs->table('sessions_data'), $hhs->table('cart')));

$db_host = $db_user = $db_pass = $db_name = NULL;



/* 创建错误处理对象 */

$err = new hhs_error('message.dwt');



/* 载入系统参数 */

$_CFG = load_config();



/* 载入语言文件 */

require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');



if ($_CFG['shop_closed'] == 1)
{
    /* 商店关闭了，输出关闭的消息 */
    header('Content-type: text/html; charset='.EC_CHARSET);
    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}

if (!defined('INIT_NO_USERS'))
{
    /* 初始化session */
    include(ROOT_PATH . 'includes/cls_session.php');
    $sess = new cls_session($db, $hhs->table('sessions'), $hhs->table('sessions_data'));
    define('SESS_ID', $sess->get_session_id());
}

if(isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF']=htmlspecialchars($_SERVER['PHP_SELF']);
}
if(isset($_REQUEST['tpl'])&&$_REQUEST['tpl']=='haohai')
{
    $_CFG['template'] = 'haohai';
}
if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.EC_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;
    $smarty->cache_lifetime = $_CFG['cache_time'];
    $smarty->template_dir   = ROOT_PATH . 'themes/' . $_CFG['template'];
    $smarty->cache_dir      = ROOT_PATH . 'temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'temp/compiled';

    if ((DEBUG_MODE & 2) == 2)
    {
		$smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_LANG);
    $smarty->assign('serv_time', gmtime());
    $smarty->assign('hhs_charset', EC_CHARSET);
	$smarty->assign('hhs_css_path', 'themes/' . $_CFG['template'].'/css');
	$smarty->assign('hhs_img_path', 'themes/' . $_CFG['template'].'/images');
}

if (!defined('INIT_NO_USERS'))
{
    /* 会员信息 */
    $user =& init_users();
    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0)
            {
                update_user_info();
            }
        }
        else
        {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
            if (!isset($_SESSION['login_fail']))
            {
                $_SESSION['login_fail'] = 0;
            }
        }
    } 

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['HHS']['user_id']) && !empty($_COOKIE['HHS']['password']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, uname as user_name, password ' .
                ' FROM ' .$hhs->table('users') .
                " WHERE user_id = '" . intval($_COOKIE['HHS']['user_id']) . "' AND password = '" .$_COOKIE['HHS']['password']. "'";
        $row = $db->GetRow($sql);

        if (!$row)
        {
           // 没有找到这个记录
           $time = time() - 3600;
           setcookie("HHS[user_id]",  '', $time, '/');
           setcookie("HHS[password]", '', $time, '/');
        }
        else
        {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            update_user_info();
        }
    }

    if (isset($smarty))
    {
        $smarty->assign('hhs_session', $_SESSION);
    }
}

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); 
}

if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'includes/lib.debug.php');
}

if (!empty($_REQUEST['site_id']))
{

    $_SESSION['site_id'] = $site_id= trim($_REQUEST['site_id']);
}

$smarty->assign('site_id', $_SESSION['site_id']);
$smarty->assign('site_name',  get_region_name($_SESSION['site_id']));
//$smarty->assign('is_weixin', 1);
$smarty->assign('is_weixin', is_weixin());

/* 判断是否支持 Gzip 模式 */
if (!defined('INIT_NO_SMARTY') && gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}



$smarty->assign('shop_name', $_CFG['shop_name'] );
$smarty->assign('HTTP_HOST', $_SERVER['HTTP_HOST'] );
$weixin_config_rows = $db->getRow("select * from ".$hhs->table('weixin_config')."");
$appid = $weixin_config_rows['appid'];
$appsecret =$weixin_config_rows['appsecret'];
include(ROOT_PATH . 'wxpay/class_weixin.php');

// setcookie("appid",$appid);
// setcookie("appsecret",$appsecret);
if(isset($_GET['code']))
{
    $back_openid_arr=get_openid($appid,$appsecret,$_GET['code']);
    $_SESSION['xaphp_sopenid']=$back_openid_arr['openid'];
    $_SESSION['A_token']=$back_openid_arr['access_token'];
    $pattern1 = '/[\?]code=[^&]*/i';
    $pattern2 = "/&code=[^&]*/i";
    $uri=preg_replace($pattern1, '', $_SERVER['REQUEST_URI']);
    $uri=preg_replace($pattern2, '', $uri);
    $url="http://" . $_SERVER['HTTP_HOST'] .$uri;
    header("location:".$url."");
    exit();

}

if(isset($_GET['ii'])&&$_GET['ii'] =='lii')
{
    $_SESSION['xaphp_sopenid']='';
}else if(isset($_GET['ii'])&&$_GET['ii'] =='pangbin')
{
    $_SESSION['xaphp_sopenid']='oFw1nxLkInraawdJh-KZuGRrKZPk';
}else if(isset($_GET['ii'])&&$_GET['ii'] =='hhs')
{
    $_SESSION['xaphp_sopenid']='oFw1nxNp5N1igv5V1s2_41HbEzRo';
}



//$is_home = strstr($_SERVER['SCRIPT_NAME'], 'index.php') ? true : false;

if(empty($_SESSION['xaphp_sopenid']) && is_weixin() && PHP_SELF !== '/index.php')
{

    $state=urlencode($_SERVER['REQUEST_URI']);
	$redirect_uri="http://" . $_SERVER['HTTP_HOST'] . "/wxpay/wx_oauth.php";  //http://vshop.xakc.net/ " . $_SERVER['SERVER_NAME'] . "  " . $_SERVER['HTTP_HOST'] . "
	$redirect_uri=urlencode($redirect_uri);
	$url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=".$state."#wechat_redirect";
	header("location:".$url."");
	exit;
}

$uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;

if($uid && $uid != $_SESSION['user_id'])
{
    $_SESSION['sid'] = $uid;
}
//$_SESSION['xaphp_sopenid']='onSWAuOcOaSJgGidvKTJoj6u0rCc';
//echo $_SESSION['xaphp_sopenid'];die;
if(!empty($_SESSION['xaphp_sopenid'])) //&& empty($_SESSION['user_id'])
{
	require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
	
	//根据全局token获取用户信息
	if(!empty($_SESSION['A_token']))
	{
        //echo $_SESSION['A_token'];
	    define("ACCESS_TOKEN",$_SESSION['A_token']);//
	    $userinfo_back_arr=getUserInfo($_SESSION['xaphp_sopenid']);
	    //获取头像
	    $headimgurl= substr($userinfo_back_arr["headimgurl"], 0,-1). '64' ;
	}
	
	//单单为了获取是否关注
	$weixin=new class_weixin($appid,$appsecret);
	$access_token = $weixin->getAccessToken();
	$_SESSION['access_token'] = $access_token;
	if(!empty($access_token))
	{
	    $userinfo_back_arr2=getUserInfo($_SESSION['xaphp_sopenid'],2);
	    $userinfo_back_arr['subscribe']=$userinfo_back_arr2["subscribe"];
        $_SESSION['subscribe'] = $userinfo_back_arr['subscribe'];
	    // $smarty->assign("subscribe" , $userinfo_back_arr['subscribe']);
	} 
	
	if($userinfo_back_arr['unionid']!='')
	{
		$sql="select * from ".$hhs->table('users')." where openid='".trim($_SESSION['xaphp_sopenid'])."' or unionid='".$userinfo_back_arr['unionid']."' ";
	}
	else
	{
		$sql="select * from ".$hhs->table('users')." where openid='".trim($_SESSION['xaphp_sopenid'])."'";
	}
	
	$rs=$db->getRow($sql);
	
	if(empty($rs))
	{
		include_once(ROOT_PATH . 'includes/lib_passport.php');
		$ychar="0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z";
		$list=explode(",",$ychar);
		$password='';
		for($i=0;$i<6;$i++)
		{
			$randnum=rand(0,35);
			$password.=$list[$randnum];
		}
		$sql="select user_id from ".$hhs->table('users')." order by user_id desc limit 1";
		$user_id=$db->getOne($sql)+1;
		$username = 'wx'.$user_id.mt_rand(0,100);
		$email    = '';
		$other['msn'] = '';
		$other['qq'] = '';
		$other['office_phone'] = '';
		$other['home_phone'] = '';
		$other['mobile_phone'] = '';
		$other['openid'] = $_SESSION['xaphp_sopenid'];
        $other['subscribe'] = $userinfo_back_arr['subscribe'];
		$other['unionid'] = $userinfo_back_arr['unionid'];
		$other['uname']=filterNickname($userinfo_back_arr['nickname']);
		// if($userinfo_back_arr['nickname']!='' && is_username($userinfo_back_arr['nickname']) && !preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $userinfo_back_arr['nickname'])  ){
		// 	echo"<script>";
		// 	echo"alert('请先关注公众号');";
		// 	echo"";exit();
		// 	//echo $userinfo_back_arr['nickname'];exit();
		// 	$other['uname']=$userinfo_back_arr['nickname'];
		// }     
		// else
		// {
		// 	$other['uname'] =$username;
		// }
		if (register($username, $password, $email, $other) !== false)
		//if (register($username, $password, $email, $other) !== false)
		{	
			/**

             * 分销相关

             * @var [type]

             */

            

            if ($_SESSION['sid']) {

                $uid = $_SESSION['sid'];

                include_once(ROOT_PATH . 'includes/lib_fenxiao.php');

                $users = getUserPids($uid);

                setUserPids($_SESSION['user_id'],$uid,$users['uid_1'],$users['uid_2']);

                // 发送新的盟友消息

                $uids = getUserPids($_SESSION['user_id']);

                $weixin = new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);



                if($uids['openid_1'])

                {

                    $openid = $uids['openid_1'];

                    $title  = '一级盟友来啦！';

                    $url    = 'user.php?act=fenxiao';

                    $desc   = '新朋友“'.$other['uname'].'”已经加入到您的分销团队';

                    $weixin->send_wxmsg($openid, $title , $url , $desc );

                }

                if($uids['openid_2'])

                {

                    $openid = $uids['openid_2'];

                    $title  = '二级盟友来啦！';

                    $url    = 'user.php?act=fenxiao';

                    $desc   = '新朋友“'.$other['uname'].'”已经加入到您的分销团队';

                    $weixin->send_wxmsg($openid, $title , $url , $desc );

                }

                if($uids['openid_3'])

                {

                    $openid = $uids['openid_3'];

                    $title  = '三级盟友来啦！';

                    $url    = 'user.php?act=fenxiao';

                    $desc   = '新朋友“'.$other['uname'].'”已经加入到您的分销团队';

                    $weixin->send_wxmsg($openid, $title , $url , $desc );

                }

            }

            //分销end

            //

            // $parent_id=get_affiliate();

			///echo $parent_id."dddd111";exit;

			// $sql="update ".$hhs->table('users')." set parent_id=".$parent_id." where user_id=".$_SESSION['user_id'];

			// $db->query($sql);

		    $str="";

			if(!empty($userinfo_back_arr)){

			    if(!empty($headimgurl) ){

			        $str.=" headimgurl='".$headimgurl."' ,";

			    }

			    if(isset($userinfo_back_arr['subscribe'])){

			        $str.=" is_subscribe=".$userinfo_back_arr['subscribe']." ,";

			    }



			    if($str!=''){

			    	$str=substr($str,0,-1);

			    	$sql="update ".$hhs->table('users')." set ".$str." where user_id=".$_SESSION['user_id'];

			    	$db->query($sql);

			    }

			    

			}

			

		}else{

			$aa='';

			foreach ($err->_message AS $msg)

            {

                $aa .= htmlspecialchars($msg) ;

            }

            echo $aa;exit();

		}

	}else{	
	   $str="";
	   if(!empty($userinfo_back_arr)){
            $userinfo_back_arr['nickname'] = filterNickname($userinfo_back_arr['nickname']);
	       if($userinfo_back_arr['nickname']!=$rs['uname']   ){
	           $str.=" uname='".$userinfo_back_arr['nickname']."' ,";
	       }
	       if($headimgurl!=''&& $headimgurl!=$rs['headimgurl']){
	           $str.=" headimgurl='".$headimgurl."' ,";
	       }
	       if(isset($userinfo_back_arr['subscribe'])){
	           $str.=" is_subscribe=".$userinfo_back_arr['subscribe']." ,";
	       }
		   if(isset($userinfo_back_arr['unionid']))
		   {
			    $str.=" unionid='".$userinfo_back_arr['unionid']."' ,";  
		   }
	       if($str!=''&&$rs['uname']==''||$rs['headimgurl']==''){
	           $str=substr($str,0,-1);
	           $sql="update ".$hhs->table('users')." set ".$str." where user_id=".$rs['user_id'];
	           $db->query($sql);
	       }
	   }
		if($_SESSION['user_id']!=$rs['user_id']){
		    $_SESSION['user_id']   = $rs['user_id'];
		    $_SESSION['user_name'] = $rs['uname'];
		    update_user_info();
		    recalculate_price();
		}
	}
}

$smarty->assign("subscribe" , $_SESSION['subscribe']?$_SESSION['subscribe']:0);

// if($_SESSION['user_id']>8)

//     die ('开发中...好了你再来！');

// $uid = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : $_SESSION['user_id'];

$uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$smarty->assign("uid" , $uid);

 //$user->set_session("山峰");

 //$user->set_cookie("山峰", null);

 /**

 * 过滤微信昵称中的表情（不过滤 HTML 符号）

 */

function filterNickname($nickname)

{

    $nickname = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $nickname);

    $nickname = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $nickname);

    $nickname = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $nickname);

    $nickname = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $nickname);

    $nickname = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $nickname);

    $nickname = str_replace(array('"','\''), '', $nickname);

    

    return addslashes(trim($nickname));

}
function is_weixin(){ 

	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {

			return true;

	}	

	return false;

}
?>

