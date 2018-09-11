<?php

/**
 * 小舍电商 定期删除
 * ===========================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: pangbin $
 * $Id: ipdel.php 17217 2014-05-12 06:29:08Z pangbin $
 */

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/ipdel.php';
if (file_exists($cron_lang))
{
    global $_LANG;

    include_once($cron_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'ipdel_desc';

    /* 作者 */
    $modules[$i]['author']  = '小舍电商 TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.baidu.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'ipdel_day', 'type' => 'select', 'value' => '30'),
    );

    return;
}

empty($cron['ipdel_day']) && $cron['ipdel_day'] = 7;

$deltime = gmtime() - $cron['ipdel_day'] * 3600 * 24;
$sql = "DELETE FROM " . $hhs->table('stats') .
       "WHERE  access_time < '$deltime'";
$db->query($sql);

?>
