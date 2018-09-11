<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_luck_logs`;");
E_C("CREATE TABLE `hhs_luck_logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `luck_id` int(10) DEFAULT NULL COMMENT '红包id',
  `money` decimal(10,2) DEFAULT NULL COMMENT '红包金额',
  `user_id` int(10) DEFAULT NULL,
  `get_at` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `id` (`luck_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>