<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_share_info`;");
E_C("CREATE TABLE `hhs_share_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `link_url` varchar(200) DEFAULT NULL COMMENT '链接字符串',
  `share_type` tinyint(4) DEFAULT '1' COMMENT '1 给好友 2 朋友圈 3 微博 4 qq',
  `share_status` tinyint(4) DEFAULT '1' COMMENT '1 成功 2 取消分享',
  `add_time` int(11) DEFAULT NULL,
  `child_id` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>