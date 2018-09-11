<?php

/**
 * 小舍电商 文章及文章分类相关函数库
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: lib_article.php 17217 2014-05-12 06:29:08Z pangbin $
*/

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}

/**
 * 获得文章分类下的文章列表
 *
 * @access  public
 * @param   integer     $cat_id
 * @param   integer     $page
 * @param   integer     $size
 *
 * @return  array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20 ,$requirement='')
{
    //取出所有非0的文章
    if ($cat_id == '-1')
    {
        $cat_str = 'cat_id > 0';
    }
    else
    {
        $cat_str = get_article_children($cat_id);
    }
    //增加搜索条件，如果有搜索内容就进行搜索
    if ($requirement != '')
    {
        $sql = 'SELECT *' .
               ' FROM ' .$GLOBALS['hhs']->table('article') .
               ' WHERE is_open = 1 AND title like \'%' . $requirement . '%\' ' .
               ' ORDER BY article_type DESC, add_time DESC';
    }
    else
    {

        $sql = 'SELECT *' .
               ' FROM ' .$GLOBALS['hhs']->table('article') .
               ' WHERE is_open = 1 AND ' . $cat_str .
               ' ORDER BY article_type DESC, add_time DESC';
    }

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);

    $arr = array();
    if ($res)
    {
        while ($row = $GLOBALS['db']->fetchRow($res))
        {

            $arr[] = $row;
        }
        foreach ($arr as $key => $row) {

            $arr[$key]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
            $arr[$key]['add_time']    = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        }
    }

    return $arr;
}

/**
 * 获得指定分类下的文章总数
 *
 * @param   integer     $cat_id
 *
 * @return  integer
 */
function get_article_count($cat_id ,$requirement='')
{
    global $db, $hhs;
    if ($requirement != '')
    {
        $count = $db->getOne('SELECT COUNT(*) FROM ' . $hhs->table('article') . ' WHERE ' . get_article_children($cat_id) . ' AND  title like \'%' . $requirement . '%\'  AND is_open = 1');
    }
    else
    {
        $count = $db->getOne("SELECT COUNT(*) FROM " . $hhs->table('article') . " WHERE " . get_article_children($cat_id) . " AND is_open = 1");
    }
    return $count;
}

?>
