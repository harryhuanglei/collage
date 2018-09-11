<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_luckdraw`;");
E_C("CREATE TABLE `hhs_luckdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `stock_num` int(10) NOT NULL COMMENT '抽奖库存',
  `goods_id` int(10) NOT NULL COMMENT '拼团商品id',
  `start_time` int(32) NOT NULL COMMENT '活动开始时间',
  `end_time` int(32) NOT NULL COMMENT '活动结束时间',
  `luck_status` int(10) NOT NULL COMMENT '抽奖状态 0正在进行',
  `content` text NOT NULL COMMENT '备注',
  `luckdraw_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '抽奖价格',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='抽奖活动'");

require("../../inc/footer.php");
?>