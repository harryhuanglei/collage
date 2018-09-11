<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_announcement`;");
E_C("CREATE TABLE `hhs_announcement` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `add_time` char(10) NOT NULL,
  `is_display` smallint(1) NOT NULL COMMENT '是否显示 1显示  2隐藏',
  `zan_num` int(10) DEFAULT '0',
  `comment_num` int(10) DEFAULT '0',
  `zan_user_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_announcement` values('3','新广场测试公告','新广场测试公告新广场测试公告新广场测试公告新广场测试公告','1479162926','1','5','2','4011');");
E_D("replace into `hhs_announcement` values('4','新广场测试公告2','新广场测试公告2新广场测试公告2新广场测试公告2新广场测试公告2','1479162944','1','6','4','4542');");

require("../../inc/footer.php");
?>