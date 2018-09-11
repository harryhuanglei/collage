<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_order_luck`;");
E_C("CREATE TABLE `hhs_order_luck` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '中奖号码',
  `order_id` int(10) NOT NULL COMMENT '订单ID',
  `is_lucker` tinyint(1) DEFAULT '0' COMMENT '中奖标记',
  `is_payed` tinyint(1) DEFAULT '0' COMMENT '是否支付过订单',
  `team_sign` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `team_sign` (`team_sign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>