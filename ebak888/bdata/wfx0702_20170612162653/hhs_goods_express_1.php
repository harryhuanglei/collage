<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_goods_express`;");
E_C("CREATE TABLE `hhs_goods_express` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) NOT NULL,
  `province_id` mediumint(8) DEFAULT '0' COMMENT '省',
  `city_id` mediumint(8) DEFAULT '0' COMMENT '市',
  `region_id` mediumint(8) DEFAULT '0' COMMENT '区',
  `shipping_id` smallint(5) DEFAULT NULL COMMENT '快递id',
  `shipping_name` varchar(30) DEFAULT NULL,
  `shipping_code` varchar(30) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT NULL COMMENT '起步价',
  `step_fee` decimal(10,2) DEFAULT NULL COMMENT '每次加价',
  `fee_compute_mode` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>