<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_square_img_text`;");
E_C("CREATE TABLE `hhs_square_img_text` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `img` varchar(255) DEFAULT NULL,
  `text` text,
  `add_time` char(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `praise_num` mediumint(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>