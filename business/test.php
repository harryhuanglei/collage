<?php
define('IN_HHS', true);
require('../includes/init2.php');
require('../includes/lib_order.php');
require('../includes/lib_code.php');

//define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$op  = isset($_REQUEST['op']) ? trim($_REQUEST['op']) : 'main';

$smarty->assign('temp_root_path',ROOT_PATH);
$smarty->assign('admin_path',ADMIN_PATH);
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/bonus.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
$_LANG['account_settlement_status'][1] = '待商家审核';
$_LANG['account_settlement_status'][2] = '待平台审核';
$_LANG['account_settlement_status'][3] = '已审核待结算';
$_LANG['account_settlement_status'][4] = '待商家确认账户信息';
$_LANG['account_settlement_status'][5] = '待付款';
$_LANG['account_settlement_status'][6] = '付款完成';
$_LANG['account_settlement_status'][7] = '商家确认已收款';
$_LANG['account_settlement_status'][10] = '商家有疑问';
$_LANG['account_settlement_status'][11] = '未通过平台审核';
/**
 * 解决跳转问题，这个是个渣渣问题。
 * 去管理后台查看自提的id，填写一下
 * 在所有的有问题post表单中发送一下这个订单的shippingID
 * 然后匹配一下
 */
$offlineID = $GLOBALS['db']->getOne("SELECT `shipping_id` from ".$GLOBALS['hhs']->table('shipping')." where `shipping_code` = 'cac'");
define('offlineID', $offlineID);//14
$smarty->assign('offlineID', offlineID);

$smarty->assign('lang', $_LANG);
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
include_once(ROOT_PATH . 'business/includes/lib_mian.php');
// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =array('get_goods_list','bonus_batch','delete_bonus_list','send_by_print','bonus_insert','delete_bonus','drop_goods','restore_goods','goods_import_act','login','register','is_supp_top','check_user_name','is_registered','act_login_sub','delivery_cancel_ship','get_cat_piclist','accounts_apply_del','edit_pic_update',
'delete_pic','operation_goods','update_delivery_pic','drop_delete_pic','pic_category_delete','pic_category_update',
'article_delete','article_insert','supp_update','act_register','get_cat_list',
'act_login','act_edit_profile','accounts_apply_act','order_print','delivery_ship','act_edit_password','change_order_card','add_goods_act','delete_goods','update_goods','logout','drop_image','user_message','edit_cate','add_cate','reply','supp_account_list','add_supp_account','supp_account_insert','supp_account_update','edit_account','shipping_type','update_shipping_type','order_code_check','brand_insert','update_insert','update_brand','allot_act','ad_update','ad_insert','factoryauthorized_update','factoryauthorized_insert','trademark_insert','trademark_update','trademark_delete','insert_user','update_user','delete_user','shipping_area_update','insert_point','update_point','delete_point');
/* 显示页面的action列表 */
$ui_arr = array('main','goods_order2','gen_bonus_excel','edit_bonus','bonus_list','send_bonus','add_bonus','bunus','goods_trash','factoryauthorized','category','order_operation','delivery_upload','goods_import','sale_order_download','sale_order','sale_list_download','sale_list','order_stats_download','order_stats','register','login','operate_post','operate','bank_config','delivery','commission_desc','shipping_config','my_goods','delivery_list','delivery_info','order_code_list','accounts_apply_list','order_code_check','supp_info','pic_category_add','get_pic','edit_pic','pic_add','pic_category_edit','article_edit','pic_list','pic_category','article_add','edit_goods','default','edit_password','add_goods','goods_list','my_order','account_detail','accounts_apply','accounts_apply_list','order_info','category','user_message','edit_cate','add_cate','reply','supp_update','supp_account_list','add_supp_account','supp_account_insert','supp_account_update','edit_account','shipping_type','update_shipping_type','goods_order','brand','edit_brand','add_brand','allot','ad','add_ad','edit_ad','ad_delete', 'add_factoryauthorized','edit_factoryauthorized','factoryauthorized_delete','article_list','article_edit','trademark','add_trademark','edit_trademark','my_qualification','my_user','add_user','edit_user','supp_shipping','shipping_area_list','shipping_area_edit','shipping_area_add','edit_print_template','shipping_delivery_list','point_list','add_point','edit_point','bussiness','delete_join');
$suppliers_id = $_SESSION['suppliers_id'];
$suppliers_array = get_suppliers_info($suppliers_id);
$smarty->assign('suppliers_array',$suppliers_array);
if(!$_SESSION['role_id'])
{
	$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$suppliers_array['user_name'];
}
else
{
	$role_name = $db->getOne("select name from ".$hhs->table('supp_account')." where account_id='$_SESSION[role_id]'");
	$supp_opt_name = $suppliers_array['suppliers_name'].'_'.$role_name;
}

$smarty->assign('suppliers_id',$suppliers_id);


/* 未登录处理 */
if (empty($suppliers_id))
{
    if (!in_array($action, $not_login_arr))
    {
        if(in_array($action, $ui_arr))
        {
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'suppliers.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
			$op = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}
$smarty->assign('action',$action);

if($action=='default'){
    $smarty->display('index.htm');

}
else{
    include_once(ROOT_PATH . 'business/supp_'.$op.'.php');

}
?>
