<?php
/**
 * 小舍电商 权限对照表
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: sunxiaodong $
 * $Id: inc_priv.php 15503 2008-12-24 09:22:45Z sunxiaodong $
*/
if (!defined('IN_HHS'))
{
    die('Hacking attempt');
}
//商家
$purview['02_suppliers_add']         = 'suppliers_add_manage';
$purview['01_suppliers_list']         = 'suppliers_list';
$purview['03_suppliers_type']         = 'suppliers_type_manage';
$purview['05_companys_list']         = 'companys_manage';
$purview['04_suppliers_accounts']     = 'suppliers_accounts';
//商品管理权限
$purview['01_goods_list']        = array('goods_manage', 'remove_back');
$purview['02_goods_add']         = 'goods_manage';
$purview['03_category_list']     = array('cat_manage', 'cat_drop');   //分类添加、分类转移和删除
// $purview['05_comment_manage']    = 'comment_priv';
// $purview['06_goods_brand_list']  = 'brand_manage';
$purview['08_goods_type']        = 'attr_manage';   //商品属性
$purview['11_goods_trash']       = array('goods_manage', 'remove_back');
$purview['12_batch_pic']         = 'picture_batch';
//$purview['14_goods_export']      = 'goods_export';
//$purview['17_tag_manage']        = 'tag_manage';
//$purview['50_virtual_card_list'] = 'virualcard';
//$purview['51_virtual_card_add']  = 'virualcard';
$purview['06_comment_manage'] = 'false_comm';
$purview['06_goods_brand_list']           = 'brand_list';
//促销管理权限
$purview['04_bonustype_list']    = 'bonus_manage';
$purview['06_luck_list']    = 'luck_manage';
//$purview['06_pack_list']         = 'pack';
//$purview['07_card_list']         = 'card_manage';
//$purview['08_group_buy']         = 'group_by';
$purview['09_topic']             = 'topic_manage';
//$purview['12_favourable']        = 'favourable';
//$purview['13_wholesale']         = 'whole_sale';
//$purview['14_package_list']      = 'package_manage';
$purview['15_exchange_goods']    = 'exchange_goods';  //赠品管理
//文章管理权限
$purview['02_articlecat_list']   = 'article_cat';
$purview['03_article_list']      = 'article_manage';
//$purview['vote_list']            = 'vote_priv';
//会员管理权限
$purview['03_users_list']        = 'users_manage';
$purview['04_users_add']         = 'users_manage';
$purview['11_users_list']    = 'users_false';
$purview['09_user_account']      = 'surplus_manage';
//$purview['06_list_integrate']    = 'integrate_users';
//$purview['08_unreply_msg']       = 'feedback_priv';
$purview['10_user_account_manage'] = 'account_manage';
//权限管理
$purview['admin_logs']           = array('logs_manage', 'logs_drop');
$purview['admin_list']           = array('admin_manage', 'admin_drop', 'allot_priv');
$purview['agency_list']          = 'agency_manage';
//$purview['suppliers_list']          = 'suppliers_manage'; // 供货商
//$purview['admin_role']             = 'role_manage';
//商店设置权限
//$purview['01_shop_config']       = 'shop_config';
//$purview['shp_webcollect']            = 'webcollect_manage';
//$purview['02_payment_list']      = 'payment';
//$purview['03_shipping_list']     = array('ship_manage','shiparea_manage');
$purview['04_mail_settings']     = 'shop_config';
//$purview['05_area_list']         = 'area_manage';
//$purview['07_cron_schcron']      = 'cron';
//$purview['08_friendlink_list']   = 'friendlink';
//$purview['sitemap']              = 'sitemap';
//$purview['captcha_manage']       = 'shop_config';
//$purview['navigator']            = 'navigator';
//$purview['ucenter_setup']        = 'integrate_users';
//$purview['021_reg_fields']       = 'reg_fields';
//$purview['website']              = 'website';
$purview['021_site']             = 'site_manage';
//广告管理
//$purview['z_clicks_stats']       = 'ad_manage';
//$purview['ad_position']          = 'ad_manage';
//$purview['ad_list']              = 'ad_manage';
//订单管理权限
$purview['02_order_list']        = 'order_view';
$purview['021_team_list']        = 'order_view';
$purview['03_order_query']       = 'order_view';
$purview['04_merge_order']       = 'order_os_edit';
$purview['05_edit_order_print']  = 'order_os_edit';
//$purview['06_undispose_booking'] = 'booking';
$purview['08_add_order']         = 'order_edit';
$purview['09_delivery_order']    = 'delivery_view';
$purview['10_back_order']        = 'back_view';
$purview['03_point_order_list']   = 'point_order_view';
//报表统计权限
$purview['report_guest']         = 'client_flow_stats';
$purview['report_users']         = 'client_flow_stats';
$purview['visit_buy_per']        = 'client_flow_stats';
$purview['searchengine_stats']   = 'client_flow_stats';
$purview['report_order']         = 'sale_order_stats';
$purview['report_sell']          = 'sale_order_stats';
$purview['sale_list']            = 'sale_order_stats';
$purview['sell_stats']           = 'sale_order_stats';
//模板管理
//$purview['05_edit_languages']    = 'lang_edit';
//$purview['mail_template_manage'] = 'mail_template';
//数据库管理
$purview['02_db_manage']         = array('db_backup', 'db_renew');
//$purview['03_db_optimize']       = 'db_optimize';
//$purview['04_sql_query']         = 'sql_query';
$purview['01_wxconfig']          = 'wx_jk';
$purview['02_config']            = 'wx_set';
$purview['03_regmsg']            = 'wx_hf';
$purview['04_keywords']          = 'wx_kw';
$purview['05_menu']              = 'wx_menu';
$purview['06_bonus']             = 'wx_bonus';
$purview['07_point']             = 'point';
$purview['08_share']             = 'wx_share';
//广告位置
$purview['01_ad_position']             = 'ad_position';
$purview['02_ad_list']             = 'ads_list';
?>