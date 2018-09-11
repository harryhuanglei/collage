<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_role`;");
E_C("CREATE TABLE `hhs_role` (
  `role_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(60) NOT NULL DEFAULT '',
  `action_list` text NOT NULL,
  `role_describe` text,
  PRIMARY KEY (`role_id`),
  KEY `user_name` (`role_name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_role` values('2','客户演示专用权限','goods_manage,remove_back,cat_manage,cat_drop,attr_manage,goods_type,picture_batch,article_cat,article_manage,users_manage,users_drop,surplus_manage,account_manage,order_os_edit,order_ps_edit,order_ss_edit,order_view,order_view_finished,repay_manage,sale_order_stats,delivery_view,back_view,bonus_manage,wx_jk,wx_set,wx_hf,wx_kw,wx_menu,wx_bonus,wx_share','  ');");
E_D("replace into `hhs_role` values('5','业务员','order_os_edit,order_ps_edit,order_ss_edit,order_view,order_view_finished,repay_manage,sale_order_stats,delivery_view,back_view','');");
E_D("replace into `hhs_role` values('8','财务','surplus_manage,account_manage,admin_manage,allot_priv,logs_manage,logs_drop',' ');");
E_D("replace into `hhs_role` values('9','成有','goods_manage,remove_back,cat_manage,cat_drop,attr_manage,user_comment,brand_remove,goods_type,brand_add,picture_batch,square,article_cat,article_manage,users_manage,users_drop,surplus_manage,account_manage,admin_manage,admin_drop,allot_priv,logs_manage,logs_drop,role_manage,order_os_edit,order_ps_edit,order_ss_edit,order_view,order_view_finished,repay_manage,sale_order_stats,delivery_view,back_view,point_order_view,bonus_manage,distribution_manage,exchange_goods,exchange_manage,exchange_remove,bonus_change,bonus_remove,bonus_send,distribution_change,distribution_remove,luck_manage,db_backup,db_renew,suppliers_add_manage,suppliers_remove,suppliers_accounts,suppliers_list,ad_position,ads_list,ad_add,ad_remove,shop_cfg,site_cfg,hangye_list,pay_ment,shipping_cfg,areas_manage,wx_jk,wx_set,wx_hf,wx_kw,wx_menu,wx_bonus,wx_share','型英');");

require("../../inc/footer.php");
?>