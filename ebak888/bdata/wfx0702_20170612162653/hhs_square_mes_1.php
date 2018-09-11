<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_square_mes`;");
E_C("CREATE TABLE `hhs_square_mes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) DEFAULT NULL COMMENT '订单id',
  `goods_id` int(10) DEFAULT NULL COMMENT '商品id',
  `is_boutique` tinyint(1) DEFAULT '0' COMMENT '是否加精',
  `comment_num` int(10) DEFAULT '0' COMMENT '评论数量',
  `zan_num` int(10) DEFAULT '0' COMMENT '点赞数量',
  `square_add_time` char(10) DEFAULT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>