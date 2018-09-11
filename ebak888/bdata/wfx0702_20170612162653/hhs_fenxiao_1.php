<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_fenxiao`;");
E_C("CREATE TABLE `hhs_fenxiao` (
  `order_id` int(10) NOT NULL COMMENT '订单号',
  `user_id` int(10) NOT NULL COMMENT '分成uid',
  `level` tinyint(1) DEFAULT '1' COMMENT '分销等级',
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '订单分销总金额',
  `rate` decimal(10,2) DEFAULT '0.00' COMMENT '分销比例',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '分成金额',
  `create_at` int(10) DEFAULT '0' COMMENT '创建时间',
  `update_at` int(10) DEFAULT '0' COMMENT '更新时间，为0表示无效，非0表示已经到余额',
  KEY `user_id` (`user_id`),
  KEY `level` (`level`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>