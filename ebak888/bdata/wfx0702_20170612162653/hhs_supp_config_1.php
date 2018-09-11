<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_supp_config`;");
E_C("CREATE TABLE `hhs_supp_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `suppliers_id` varchar(10) NOT NULL,
  `shipping_type` varchar(10) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_p_name` varchar(100) NOT NULL,
  `bank_account` varchar(100) NOT NULL,
  `bank_password` varchar(100) NOT NULL COMMENT '提现密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>