<?php

/**
 * HHSHOP 文章分类
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.hhshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: article_cat.php 17217 2011-01-19 06:29:08Z liubo $
*/


define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/* 清除缓存 */
clear_cache_files();





    $smarty->assign('keywords',    htmlspecialchars($meta['keywords']));
    $smarty->assign('description', htmlspecialchars($meta['cat_desc']));


    $smarty->assign('suggestion',    get_suggestion());


$smarty->display('suggestion.dwt', $cache_id);

function get_suggestion()
{
   
    $sql = 'SELECT article_id, title' .
               ' FROM ' .$GLOBALS['hhs']->table('article') .
               ' WHERE is_open = 1 AND cat_id = 23 ' .
               ' ORDER BY article_id DESC';
	$res = $GLOBALS['db']->getAll($sql);
    return $res;
}

?>