<?php

/**
 * 小舍电商 管理中心菜单数组
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: inc_menu.php 17217 2014-05-12 06:29:08Z pangbin $
*/

if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}
$modules['01_suppliers']['02_suppliers_add']        = 'suppliers.php?act=add';         // 商品列表
$modules['01_suppliers']['01_suppliers_list']       = 'suppliers.php?act=list';         // 商品列表
//$modules['01_suppliers']['03_suppliers_type']       = 'suppliers_type.php?act=list';   // 商品分类
//$modules['01_suppliers']['05_companys_list']        = 'companys.php?act=list';         // 商品列表
$modules['01_suppliers']['04_suppliers_accounts']   = 'suppliers.php?act=suppliers_accounts';         // 商品列表


$modules['02_cat_and_goods']['01_goods_list']       = 'goods.php?act=list';         // 商品列表
$modules['02_cat_and_goods']['020_suppliers_goods_list']       =    'suppliers_goods.php?act=list';   //'goods.php?act=list&is_supplier=1';       // 商品列表


$modules['02_cat_and_goods']['02_goods_add']        = 'goods.php?act=add';          // 添加商品
$modules['02_cat_and_goods']['03_category_list']    = 'category.php?act=list';
$modules['02_cat_and_goods']['06_comment_manage']   = 'comments_manage.php?act=list';//虚拟评论
$modules['02_cat_and_goods']['05_comment_manage']   = 'comment_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand_list'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type']       = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['11_goods_trash']      = 'goods.php?act=trash';        // 商品回收站
$modules['02_cat_and_goods']['12_batch_pic']        = 'picture_batch.php';
//$modules['02_cat_and_goods']['14_goods_export']     = 'goods_export.php?act=goods_export';
//$modules['02_cat_and_goods']['13_batch_add']        = 'goods_batch.php?act=add';
//$modules['02_cat_and_goods']['17_tag_manage']       = 'tag_manage.php?act=list';
//$modules['02_cat_and_goods']['50_virtual_card_list']   = 'goods.php?act=list&extension_code=virtual_card';
//$modules['02_cat_and_goods']['51_virtual_card_add']    = 'goods.php?act=add&extension_code=virtual_card';
//$modules['02_cat_and_goods']['52_virtual_card_change'] = 'virtual_card.php?act=change';
//$modules['02_cat_and_goods']['goods_auto']             = 'goods_auto.php?act=list';
$modules['02_cat_and_goods']['square']             = 'square.php?act=list';


$modules['03_promotion']['04_bonustype_list']       = 'bonus.php?act=list';
$modules['03_promotion']['05_luckmoney_list']       = 'luckmoney.php?act=list';
$modules['03_promotion']['06_luck_list']            = 'luckdraw.php?act=list';
//$modules['03_promotion']['07_card_list']            = 'card.php?act=list';
//$modules['03_promotion']['08_group_buy']            = 'group_buy.php?act=list';
$modules['03_promotion']['09_topic']                = 'topic.php?act=list';
//$modules['03_promotion']['12_favourable']           = 'favourable.php?act=list';
//$modules['03_promotion']['13_wholesale']            = 'wholesale.php?act=list';
//$modules['03_promotion']['14_package_list']         = 'package.php?act=list';
$modules['03_promotion']['15_exchange_goods']       = 'exchange_goods.php?act=list';

$modules['04_order']['02_order_list']               = 'order.php?act=list';
$modules['04_order']['021_team_list']               = 'order.php?act=team_list&is_luck=0';
$modules['04_order']['022_team_list']               = 'order.php?act=team_list&is_luck=1';

$modules['04_order']['03_point_order_list']        = 'order.php?act=point_order_list';

//$modules['04_order']['031_suppliers_team_list']               = 'order.php?act=suppliers_team_list';

//$modules['04_order']['022_team_head_list']               = 'order.php?act=team_list';
//$modules['04_order']['022_team_manage']               = 'order.php?act=team_manage';
//$modules['04_order']['03_order_query']              = 'order.php?act=order_query';
//$modules['04_order']['04_merge_order']              = 'order.php?act=merge';
//$modules['04_order']['05_edit_order_print']         = 'order.php?act=templates';
//$modules['04_order']['06_undispose_booking']        = 'goods_booking.php?act=list_all';
//$modules['04_order']['08_add_order']                = 'order.php?act=add';
$modules['04_order']['09_delivery_order']           = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order']               = 'order.php?act=back_list';

$modules['05_banner']['01_ad_position']                = 'ad_position.php?act=list';
$modules['05_banner']['02_ad_list']                    = 'ads.php?act=list';
//$modules['05_banner']['03_announcement_list']  = 'announcement.php?act=list';
//$modules['05_banner']['04_square_list'] = 'square_manage.php?act=list';

//$modules['06_stats']['report_guest']                = 'guest_stats.php?act=list';
//$modules['06_stats']['report_order']                = 'order_stats.php?act=list';
//$modules['06_stats']['report_sell']                 = 'sale_general.php?act=list';
$modules['06_stats']['sale_list']                   = 'sale_list.php?act=list';
//$modules['06_stats']['sell_stats']                  = 'sale_order.php?act=goods_num';
//$modules['06_stats']['report_users']                = 'users_order.php?act=order_num';
//$modules['06_stats']['visit_buy_per']               = 'visit_sold.php?act=list';
$modules['06_stats']['statistics_order']			= 'statistics.php?act=list';
$modules['06_stats']['statistics_order_point']      = 'statistics_point.php?act=list';

$modules['07_content']['03_article_list']           = 'article.php?act=list';
$modules['07_content']['02_articlecat_list']        = 'articlecat.php?act=list';
//$modules['07_content']['vote_list']                 = 'vote.php?act=list';


$modules['08_members']['03_users_list']             = 'users.php?act=list';
// $modules['08_members']['04_users_add']              = 'users.php?act=add';
//$modules['08_members']['05_user_rank_list']         = 'user_rank.php?act=list';
//$modules['08_members']['06_list_integrate']         = 'integrate.php?act=list';
//$modules['08_members']['08_unreply_msg']            = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account']           = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage']    = 'user_account_manage.php?act=list';
$modules['08_members']['10_user_distribution']      = 'distribution.php?act=list';
$modules['08_members']['11_users_list']              = 'users_false.php?act=list';

$modules['10_priv_admin']['admin_logs']             = 'admin_logs.php?act=list';
$modules['10_priv_admin']['admin_list']             = 'privilege.php?act=list';
$modules['10_priv_admin']['admin_role']             = 'role.php?act=list';
//$modules['10_priv_admin']['agency_list']            = 'agency.php?act=list';
//$modules['10_priv_admin']['suppliers_list']         = 'suppliers.php?act=list'; // 供货商

$modules['11_system']['01_shop_config']             = 'shop_config.php?act=list_edit';
$modules['11_system']['02_payment_list']            = 'payment.php?act=list';
$modules['11_system']['03_shipping_list']           = 'shipping.php?act=list';
//$modules['11_system']['04_mail_settings']           = 'shop_config.php?act=mail_settings';
$modules['11_system']['05_area_list']               = 'area_manage.php?act=list';
//$modules['11_system']['07_cron_schcron']            = 'cron.php?act=list';
//$modules['11_system']['08_friendlink_list']         = 'friend_link.php?act=list';
//$modules['11_system']['sitemap']                    = 'sitemap.php';
//$modules['11_system']['captcha_manage']             = 'captcha_manage.php?act=main';
//$modules['11_system']['ucenter_setup']              = 'integrate.php?act=setup&code=ucenter';
//$modules['11_system']['navigator']                  = 'navigator.php?act=list';
//$modules['11_system']['021_reg_fields']             = 'reg_fields.php?act=list';
//$modules['11_system']['website']                    = 'website.php?act=list';
//$modules['11_system']['flashplay']                  = 'flashplay.php?act=list';
$modules['11_system']['021_site']                   = 'site.php?act=list';
$modules['11_system']['022_hangye']                   = 'hangye.php?act=list';
//$modules['11_system']['03_sms_send']                   = 'sms.php?act=display_send_ui';

//$modules['12_template']['05_edit_languages']        = 'edit_languages.php?act=list';
//$modules['12_template']['mail_template_manage']     = 'mail_template.php?act=list';


$modules['13_backup']['02_db_manage']               = 'database.php?act=backup';
//$modules['13_backup']['03_db_optimize']             = 'database.php?act=optimize';
//$modules['13_backup']['04_sql_query']               = 'sql.php?act=main';

$modules['14_wxch']['01_wxconfig']               = 'wxch.php?act=wxconfig';
$modules['14_wxch']['02_config']             = 'wxch.php?act=config';
$modules['14_wxch']['03_regmsg']               = 'wxch-ent.php?act=regmsg';
$modules['14_wxch']['04_keywords']               = 'wxch.php?act=keywords';
$modules['14_wxch']['05_menu']             = 'weixin_menu.php?act=list';
$modules['14_wxch']['08_share']             = 'wx_share.php?act=list';
$modules['14_wxch']['06_bonus']               = 'wxch.php?act=bonus';
?>
