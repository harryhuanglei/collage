<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_suppliers_accounts_detal`;");
E_C("CREATE TABLE `hhs_suppliers_accounts_detal` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(30) NOT NULL,
  `order_id` varchar(30) NOT NULL,
  `order_time` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL COMMENT '佣金',
  `suppliers_accounts_id` int(10) NOT NULL,
  `fenxiao_money` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>