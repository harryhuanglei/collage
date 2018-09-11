<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_user_bonus`;");
E_C("CREATE TABLE `hhs_user_bonus` (
  `bonus_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `bonus_sn` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `send_user_id` int(11) DEFAULT '0' COMMENT '发放人的user_id',
  `used_time` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `emailed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_act` int(11) NOT NULL DEFAULT '0' COMMENT '是否激活，好友券需要激活使用',
  `send_order_id` int(11) DEFAULT '0' COMMENT '下那个订单时发的优惠券',
  `send_id` int(11) DEFAULT '0' COMMENT '分享的链接id',
  `suppliers_id` int(11) NOT NULL DEFAULT '0',
  `is_attention_send` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bonus_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>