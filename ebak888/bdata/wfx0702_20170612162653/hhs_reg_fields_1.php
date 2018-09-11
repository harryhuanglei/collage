<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_reg_fields`;");
E_C("CREATE TABLE `hhs_reg_fields` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `reg_field_name` varchar(60) NOT NULL,
  `dis_order` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `display` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_need` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_reg_fields` values('1','MSN','0','0','1','0');");
E_D("replace into `hhs_reg_fields` values('2','QQ','0','0','1','0');");
E_D("replace into `hhs_reg_fields` values('3','办公电话','0','0','1','0');");
E_D("replace into `hhs_reg_fields` values('4','家庭电话','0','0','1','0');");
E_D("replace into `hhs_reg_fields` values('5','手机','0','1','1','1');");
E_D("replace into `hhs_reg_fields` values('6','密码找回问题','0','0','1','0');");

require("../../inc/footer.php");
?>