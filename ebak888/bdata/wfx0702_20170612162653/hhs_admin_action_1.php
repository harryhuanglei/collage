<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_admin_action`;");
E_C("CREATE TABLE `hhs_admin_action` (
  `action_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `action_code` varchar(20) NOT NULL DEFAULT '',
  `relevance` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`action_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=183 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_admin_action` values('1','0','goods','');");
E_D("replace into `hhs_admin_action` values('2','0','cms_manage','');");
E_D("replace into `hhs_admin_action` values('3','0','users_manage','');");
E_D("replace into `hhs_admin_action` values('4','0','priv_manage','');");
E_D("replace into `hhs_admin_action` values('6','0','order_manage','');");
E_D("replace into `hhs_admin_action` values('7','0','promotion','');");
E_D("replace into `hhs_admin_action` values('10','0','db_manage','');");
E_D("replace into `hhs_admin_action` values('21','1','goods_manage','');");
E_D("replace into `hhs_admin_action` values('22','1','remove_back','');");
E_D("replace into `hhs_admin_action` values('23','1','cat_manage','');");
E_D("replace into `hhs_admin_action` values('24','1','cat_drop','cat_manage');");
E_D("replace into `hhs_admin_action` values('25','1','attr_manage','');");
E_D("replace into `hhs_admin_action` values('30','2','article_cat','');");
E_D("replace into `hhs_admin_action` values('31','2','article_manage','');");
E_D("replace into `hhs_admin_action` values('174','1','user_comment','');");
E_D("replace into `hhs_admin_action` values('179','1','brand_list','');");
E_D("replace into `hhs_admin_action` values('40','3','users_manage','');");
E_D("replace into `hhs_admin_action` values('41','3','users_drop','users_manage');");
E_D("replace into `hhs_admin_action` values('85','3','surplus_manage','account_manage');");
E_D("replace into `hhs_admin_action` values('43','4','admin_manage','');");
E_D("replace into `hhs_admin_action` values('44','4','admin_drop','admin_manage');");
E_D("replace into `hhs_admin_action` values('45','4','allot_priv','admin_manage');");
E_D("replace into `hhs_admin_action` values('46','4','logs_manage','');");
E_D("replace into `hhs_admin_action` values('47','4','logs_drop','logs_manage');");
E_D("replace into `hhs_admin_action` values('48','5','shop_config','');");
E_D("replace into `hhs_admin_action` values('49','5','ship_manage','');");
E_D("replace into `hhs_admin_action` values('50','5','payment','');");
E_D("replace into `hhs_admin_action` values('51','5','shiparea_manage','');");
E_D("replace into `hhs_admin_action` values('52','5','area_manage','');");
E_D("replace into `hhs_admin_action` values('53','6','order_os_edit','');");
E_D("replace into `hhs_admin_action` values('54','6','order_ps_edit','order_os_edit');");
E_D("replace into `hhs_admin_action` values('55','6','order_ss_edit','order_os_edit');");
E_D("replace into `hhs_admin_action` values('57','6','order_view','');");
E_D("replace into `hhs_admin_action` values('58','6','order_view_finished','');");
E_D("replace into `hhs_admin_action` values('59','6','repay_manage','');");
E_D("replace into `hhs_admin_action` values('61','6','sale_order_stats','');");
E_D("replace into `hhs_admin_action` values('70','1','goods_type','');");
E_D("replace into `hhs_admin_action` values('79','7','bonus_manage','');");
E_D("replace into `hhs_admin_action` values('75','5','friendlink','');");
E_D("replace into `hhs_admin_action` values('76','5','db_backup','');");
E_D("replace into `hhs_admin_action` values('77','5','db_renew','db_backup');");
E_D("replace into `hhs_admin_action` values('87','3','account_manage','');");
E_D("replace into `hhs_admin_action` values('88','5','flash_manage','');");
E_D("replace into `hhs_admin_action` values('89','5','navigator','');");
E_D("replace into `hhs_admin_action` values('180','7','topic_manage','');");
E_D("replace into `hhs_admin_action` values('96','5','cron','');");
E_D("replace into `hhs_admin_action` values('97','5','affiliate','');");
E_D("replace into `hhs_admin_action` values('105','1','picture_batch','');");
E_D("replace into `hhs_admin_action` values('109','5','sitemap','');");
E_D("replace into `hhs_admin_action` values('118','10','db_backup','');");
E_D("replace into `hhs_admin_action` values('119','10','db_renew','');");
E_D("replace into `hhs_admin_action` values('129','6','delivery_view','');");
E_D("replace into `hhs_admin_action` values('130','6','back_view','');");
E_D("replace into `hhs_admin_action` values('131','5','reg_fields','');");
E_D("replace into `hhs_admin_action` values('135','4','role_manage','');");
E_D("replace into `hhs_admin_action` values('136','7','distribution_manage','');");
E_D("replace into `hhs_admin_action` values('137','7','exchange_goods','');");
E_D("replace into `hhs_admin_action` values('138','7','exchange_manage','');");
E_D("replace into `hhs_admin_action` values('139','7','exchange_remove','');");
E_D("replace into `hhs_admin_action` values('140','7','bonus_change','');");
E_D("replace into `hhs_admin_action` values('141','7','bonus_remove','');");
E_D("replace into `hhs_admin_action` values('142','7','bonus_send','');");
E_D("replace into `hhs_admin_action` values('143','7','distribution_change','');");
E_D("replace into `hhs_admin_action` values('144','7','distribution_remove','');");
E_D("replace into `hhs_admin_action` values('145','0','sup_manage','');");
E_D("replace into `hhs_admin_action` values('146','145','suppliers_add_manage','');");
E_D("replace into `hhs_admin_action` values('147','145','suppliers_remove','');");
E_D("replace into `hhs_admin_action` values('148','145','suppliers_accounts','');");
E_D("replace into `hhs_admin_action` values('150','145','suppliers_list','');");
E_D("replace into `hhs_admin_action` values('151','0','ad_manage','');");
E_D("replace into `hhs_admin_action` values('152','151','ad_position','');");
E_D("replace into `hhs_admin_action` values('153','151','ads_list','');");
E_D("replace into `hhs_admin_action` values('154','151','ad_add','');");
E_D("replace into `hhs_admin_action` values('155','151','ad_remove','');");
E_D("replace into `hhs_admin_action` values('156','0','system_set','');");
E_D("replace into `hhs_admin_action` values('157','156','shop_cfg','');");
E_D("replace into `hhs_admin_action` values('158','156','site_cfg','');");
E_D("replace into `hhs_admin_action` values('159','156','hangye_list','');");
E_D("replace into `hhs_admin_action` values('160','156','pay_ment','');");
E_D("replace into `hhs_admin_action` values('161','156','shipping_cfg','');");
E_D("replace into `hhs_admin_action` values('162','156','areas_manage','');");
E_D("replace into `hhs_admin_action` values('163','0','wx_set','');");
E_D("replace into `hhs_admin_action` values('164','163','wx_jk','');");
E_D("replace into `hhs_admin_action` values('165','163','wx_set','');");
E_D("replace into `hhs_admin_action` values('166','163','wx_hf','');");
E_D("replace into `hhs_admin_action` values('167','163','wx_kw','');");
E_D("replace into `hhs_admin_action` values('168','163','wx_menu','');");
E_D("replace into `hhs_admin_action` values('169','163','wx_bonus','');");
E_D("replace into `hhs_admin_action` values('170','163','wx_share','');");
E_D("replace into `hhs_admin_action` values('171','6','point_order_view','');");
E_D("replace into `hhs_admin_action` values('177','7','luck_manage','');");
E_D("replace into `hhs_admin_action` values('176','1','square','');");
E_D("replace into `hhs_admin_action` values('178','145','sup_list_down','');");
E_D("replace into `hhs_admin_action` values('181','1','false_comm','');");
E_D("replace into `hhs_admin_action` values('182','3','users_false','');");

require("../../inc/footer.php");
?>