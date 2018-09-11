<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_luck`;");
E_C("CREATE TABLE `hhs_luck` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL COMMENT '活动名',
  `money` int(10) DEFAULT NULL COMMENT '总金额',
  `num` int(10) DEFAULT NULL COMMENT '总人数',
  `limit_times` tinyint(1) DEFAULT '1' COMMENT '每人限领次数',
  `start_at` int(10) DEFAULT NULL,
  `end_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>