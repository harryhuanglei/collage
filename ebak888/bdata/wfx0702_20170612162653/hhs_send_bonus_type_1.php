<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_send_bonus_type`;");
E_C("CREATE TABLE `hhs_send_bonus_type` (
  `send_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '发放者id',
  `add_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态1 开启 0 关 3 已发完',
  `type_id` int(11) NOT NULL COMMENT '优惠券类型',
  `send_order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `send_number` int(11) DEFAULT '0' COMMENT '发送的数量',
  PRIMARY KEY (`send_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>