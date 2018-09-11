<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_shipping_point`;");
E_C("CREATE TABLE `hhs_shipping_point` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `province` smallint(5) unsigned NOT NULL DEFAULT '0',
  `city` smallint(5) unsigned NOT NULL DEFAULT '0',
  `district` smallint(5) unsigned NOT NULL DEFAULT '0',
  `address` varchar(300) NOT NULL,
  `shop_name` varchar(300) DEFAULT NULL,
  `longitude` varchar(100) NOT NULL,
  `latitude` varchar(100) NOT NULL,
  `tel` varchar(30) NOT NULL,
  `suppliers_id` int(10) NOT NULL,
  `wx_name` varchar(100) NOT NULL,
  `wx_openid` varchar(100) NOT NULL,
  `has_printer` tinyint(1) DEFAULT '0',
  `printer_type` varchar(20) DEFAULT NULL,
  `device_no` varchar(200) DEFAULT NULL,
  `device_code` varchar(200) DEFAULT NULL,
  `device_key` varchar(200) DEFAULT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>