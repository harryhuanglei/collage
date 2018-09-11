<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_agency`;");
E_C("CREATE TABLE `hhs_agency` (
  `agency_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `agency_name` varchar(255) NOT NULL,
  `agency_desc` text NOT NULL,
  PRIMARY KEY (`agency_id`),
  KEY `agency_name` (`agency_name`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_agency` values('1','北京海淀区办事处','所有北京海淀区的订单在此发货');");
E_D("replace into `hhs_agency` values('2','西安市区办事处','所有西安市区内的订单均在此发货');");

require("../../inc/footer.php");
?>