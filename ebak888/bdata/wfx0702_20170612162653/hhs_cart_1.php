<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_cart`;");
E_C("CREATE TABLE `hhs_cart` (
  `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `session_id` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_sn` varchar(60) NOT NULL DEFAULT '',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(120) NOT NULL DEFAULT '',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_attr` text NOT NULL,
  `is_real` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `extension_code` varchar(30) NOT NULL DEFAULT '',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rec_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_gift` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_shipping` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_handsel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `goods_attr_id` varchar(255) NOT NULL DEFAULT '',
  `team_sign` int(11) DEFAULT '0' COMMENT '团的标示',
  `shared_by` int(10) DEFAULT '0' COMMENT '分享者，佣金分成',
  `bonus_allowed` tinyint(1) DEFAULT '1',
  `suppliers_id` int(10) DEFAULT '0',
  `city_id` int(10) DEFAULT NULL,
  `district_id` int(10) DEFAULT NULL,
  `is_zero` tinyint(1) DEFAULT '0' COMMENT '0元购',
  `shipping_fee` decimal(10,2) DEFAULT '0.00' COMMENT '0元购邮费',
  `is_team` tinyint(1) DEFAULT '0',
  `is_checked` tinyint(1) DEFAULT '1',
  `goods_img` varchar(250) DEFAULT NULL,
  `rate_1` decimal(10,2) DEFAULT '0.00',
  `rate_2` decimal(10,2) DEFAULT '0.00',
  `rate_3` decimal(10,2) DEFAULT '0.00',
  `luckdraw_id` int(10) NOT NULL,
  PRIMARY KEY (`rec_id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>