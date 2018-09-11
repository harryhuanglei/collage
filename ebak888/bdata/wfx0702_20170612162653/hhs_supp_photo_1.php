<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_supp_photo`;");
E_C("CREATE TABLE `hhs_supp_photo` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `photo_file` varchar(255) DEFAULT NULL,
  `supp_id` int(11) DEFAULT NULL,
  `is_check` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `link` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(10) NOT NULL,
  PRIMARY KEY (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>