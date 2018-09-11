<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_share_log`;");
E_C("CREATE TABLE `hhs_share_log` (
  `goods_id` int(10) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>