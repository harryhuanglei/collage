<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_vote_option`;");
E_C("CREATE TABLE `hhs_vote_option` (
  `option_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vote_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `option_name` varchar(250) NOT NULL DEFAULT '',
  `option_count` int(8) unsigned NOT NULL DEFAULT '0',
  `option_order` tinyint(3) unsigned NOT NULL DEFAULT '100',
  PRIMARY KEY (`option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_vote_option` values('1','1','论坛','0','100');");
E_D("replace into `hhs_vote_option` values('2','1','朋友','0','100');");
E_D("replace into `hhs_vote_option` values('3','1','友情链接','0','100');");
E_D("replace into `hhs_vote_option` values('4','2','你从哪里知道的本站','0','100');");

require("../../inc/footer.php");
?>