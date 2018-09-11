<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_supp_action`;");
E_C("CREATE TABLE `hhs_supp_action` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL,
  `action_name` varchar(30) NOT NULL,
  `action_link` varchar(30) NOT NULL,
  `action_act` varchar(30) NOT NULL,
  `parent_id` varchar(30) NOT NULL,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>