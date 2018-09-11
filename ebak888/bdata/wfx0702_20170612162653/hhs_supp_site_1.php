<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_supp_site`;");
E_C("CREATE TABLE `hhs_supp_site` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `supp_id` int(10) NOT NULL,
  `site_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supp_id` (`supp_id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>