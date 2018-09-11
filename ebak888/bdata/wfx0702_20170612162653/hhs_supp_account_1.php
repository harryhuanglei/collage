<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_supp_account`;");
E_C("CREATE TABLE `hhs_supp_account` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `suppliers_id` int(10) NOT NULL COMMENT '所创建的经销商ID',
  `sort_order` int(10) NOT NULL DEFAULT '50' COMMENT '排序',
  `is_check` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `account_password` varchar(100) NOT NULL,
  `account_type` int(10) NOT NULL DEFAULT '0',
  `address` varchar(288) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>