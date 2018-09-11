<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_give_prize_log`;");
E_C("CREATE TABLE `hhs_give_prize_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `luckdraw_id` int(10) NOT NULL COMMENT '抽奖活动id',
  `add_time` char(10) NOT NULL COMMENT '发奖时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>