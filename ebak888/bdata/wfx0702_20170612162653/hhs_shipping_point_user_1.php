<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_shipping_point_user`;");
E_C("CREATE TABLE `hhs_shipping_point_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `point_id` int(10) NOT NULL,
  `openid` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `point_id` (`point_id`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>