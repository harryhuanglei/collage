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

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 取得当前hhshop所在的根目录 */
define('ROOT_PATH', str_replace('includes/init2.php', '', str_replace('\\', '/', __FILE__)));


/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);

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

if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.EC_CHARSET);

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

    $smarty->assign('serv_time', gmtime());
    $smarty->assign('lang', $_LANG);
    $smarty->assign('hhs_charset', EC_CHARSET);
	$smarty->assign('hhs_css_path', 'themes/' . $_CFG['template'].'/css');
	$smarty->assign('hhs_img_path', 'themes/' . $_CFG['template'].'/images');

}
/**/


if (!defined('INIT_NO_USERS'))
{
    /* 会员信息 */
    $user =& init_users();

    if (!isset($_SESSION['user_id']))
    {
        /* 获取投放站点的名称 */
        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);
        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

        unset($site_name);
/*
        if (!defined('INGORE_VISIT_STATS'))
        {
            visit_stats();
        }*/
    }

    /*
    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            //如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 
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
    }*/

    /* 设置推荐会员 
    if (isset($_GET['u']))
    {
        set_affiliate();
    }*/

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['HHS']['user_id']) && !empty($_COOKIE['HHS']['password']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password ' .
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


$weixin_config_rows = $db->getRow("select * from ".$hhs->table('weixin_config')."");
$appid = $weixin_config_rows['appid'];
$appsecret =$weixin_config_rows['appsecret'];
include(ROOT_PATH . 'wxpay/class_weixin.php');
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
?>
