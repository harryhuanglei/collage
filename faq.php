<?php

/**
 * HHSHOP 文章内容
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.hhshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: article.php 17217 2011-01-19 06:29:08Z liubo $
*/


define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$article_id     = $_REQUEST['id'];
if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] < 0)
{
    $article_id = $db->getOne("SELECT article_id FROM " . $hhs->table('article') . " WHERE cat_id = '".intval($_REQUEST['cat_id'])."' ");
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('faq.dwt', $cache_id))
{
    /* 文章详情 */
    $article = get_article_info($article_id);

    if (empty($article))
    {
        hhs_header("Location: ./\n");
        exit;
    }

    if (!empty($article['link']) && $article['link'] != 'http://' && $article['link'] != 'https://')
    {
        hhs_header("location:$article[link]\n");
        exit;
    }


    $smarty->assign('id',               $article_id);


    $smarty->assign('article',      $article);
    $smarty->assign('keywords',     htmlspecialchars($article['keywords']));
    $smarty->assign('description', htmlspecialchars($article['description']));


    assign_template('a', $catlist);

    $position = assign_ur_here($article['cat_id'], $article['title']);
    $smarty->assign('page_title',   $position['title']);    // 页面标题
    $smarty->assign('ur_here',      $position['ur_here']);  // 当前位置
	$smarty->assign('categories',       get_categories_tree()); // 分类树

}


    $smarty->display('faq.dwt', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定的文章的详细信息
 *
 * @access  private
 * @param   integer     $article_id
 * @return  array
 */
function get_article_info($article_id)
{
    /* 获得文章的信息 */
    $sql = "SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ".
            "FROM " .$GLOBALS['hhs']->table('article'). " AS a ".
            "LEFT JOIN " .$GLOBALS['hhs']->table('comment'). " AS r ON r.id_value = a.article_id AND comment_type = 1 ".
            "WHERE a.is_open = 1 AND a.article_id = '$article_id' GROUP BY a.article_id";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        $row['comment_rank'] = ceil($row['comment_rank']);                              // 用户评论级别取整
        $row['add_time']     = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']); // 修正添加时间显示

        /* 作者信息如果为空，则用网站名称替换 */
        if (empty($row['author']) || $row['author'] == '_SHOPHELP')
        {
            $row['author'] = $GLOBALS['_CFG']['shop_name'];
        }
    }

    return $row;
}

?>