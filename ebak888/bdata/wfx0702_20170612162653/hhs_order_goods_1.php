<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_order_goods`;");
E_C("CREATE TABLE `hhs_order_goods` (
  `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(120) NOT NULL DEFAULT '',
  `goods_sn` varchar(60) NOT NULL DEFAULT '',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '1',
  `market_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_attr` text NOT NULL,
  `send_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_real` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `extension_code` varchar(30) NOT NULL DEFAULT '',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_gift` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_attr_id` varchar(255) NOT NULL DEFAULT '',
  `refund_reason` varchar(255) NOT NULL DEFAULT '',
  `refund_desc` text NOT NULL,
  `refund_pic1` varchar(255) NOT NULL DEFAULT '',
  `refund_pic2` varchar(255) NOT NULL DEFAULT '',
  `refund_pic3` varchar(255) NOT NULL DEFAULT '',
  `refund_add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `refund_confirm_time` int(10) unsigned NOT NULL DEFAULT '0',
  `refund_confirm_desc` text NOT NULL,
  `refund_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `commission` decimal(10,2) DEFAULT NULL,
  `city_id` int(10) DEFAULT NULL,
  `district_id` int(10) DEFAULT NULL,
  `suppliers_id` int(10) DEFAULT NULL,
  `rate_1` decimal(10,2) DEFAULT '0.00',
  `rate_2` decimal(10,2) DEFAULT '0.00',
  `rate_3` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`rec_id`),
  KEY `order_id` (`order_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>