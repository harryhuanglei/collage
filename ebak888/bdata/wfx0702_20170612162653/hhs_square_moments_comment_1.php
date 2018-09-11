<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_square_moments_comment`;");
E_C("CREATE TABLE `hhs_square_moments_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL,
  `add_time` char(10) NOT NULL,
  `moments_id` int(10) NOT NULL COMMENT '朋友圈id',
  `comment_user_id` int(10) NOT NULL COMMENT '评论者id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>