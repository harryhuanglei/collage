<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_suppliers_accounts_apply`;");
E_C("CREATE TABLE `hhs_suppliers_accounts_apply` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account` decimal(10,2) NOT NULL,
  `suppliers_id` varchar(10) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `add_time` varchar(30) NOT NULL,
  `apply_desc` text NOT NULL,
  `pay_time` varchar(30) NOT NULL,
  `apply_status` varchar(10) NOT NULL DEFAULT '0',
  `rec_id` varchar(100) NOT NULL,
  `bank_p_name` varchar(100) NOT NULL,
  `bank_account` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>