<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_refund_log`;");
E_C("CREATE TABLE `hhs_refund_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(20) NOT NULL,
  `out_refund_no` varchar(30) NOT NULL,
  `refund_fee` decimal(10,2) NOT NULL,
  `transaction_id` varchar(60) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `note` text,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>