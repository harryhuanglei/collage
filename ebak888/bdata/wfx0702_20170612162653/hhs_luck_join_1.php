<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_luck_join`;");
E_C("CREATE TABLE `hhs_luck_join` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `luck_id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `join_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `luck_id` (`luck_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>