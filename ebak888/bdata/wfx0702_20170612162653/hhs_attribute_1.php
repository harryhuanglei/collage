<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_attribute`;");
E_C("CREATE TABLE `hhs_attribute` (
  `attr_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attr_name` varchar(60) NOT NULL DEFAULT '',
  `attr_input_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `attr_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `attr_values` text NOT NULL,
  `attr_index` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_linked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `attr_group` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attr_id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=273 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_attribute` values('250','36','特色','0','1','','0','0','0','0');");
E_D("replace into `hhs_attribute` values('249','36','尺寸','0','1','','0','0','0','0');");
E_D("replace into `hhs_attribute` values('248','36','颜色','0','1','','0','0','0','0');");
E_D("replace into `hhs_attribute` values('271','53','正品','1','1','坏\r\n好','0','0','0','0');");
E_D("replace into `hhs_attribute` values('255','36','品种','0','1','','0','0','0','0');");
E_D("replace into `hhs_attribute` values('272','53','价格','0','1','','0','0','0','0');");

require("../../inc/footer.php");
?>