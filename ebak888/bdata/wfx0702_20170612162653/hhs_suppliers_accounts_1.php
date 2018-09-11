<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_suppliers_accounts`;");
E_C("CREATE TABLE `hhs_suppliers_accounts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `suppliers_id` int(10) NOT NULL,
  `settlement_status` int(10) NOT NULL DEFAULT '0' COMMENT '结算状态 1待审核 2已审核  3已付款待确认  4已完成',
  `start_time` varchar(30) NOT NULL,
  `end_time` varchar(30) NOT NULL,
  `total` decimal(10,2) DEFAULT '0.00' COMMENT '总金额',
  `settlement_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '结算总额',
  `commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '结算佣金',
  `add_time` varchar(30) NOT NULL COMMENT '结算时间',
  `add_month` varchar(30) NOT NULL,
  `settlement_sn` varchar(30) NOT NULL COMMENT '结算单号',
  `remark` text,
  `fenxiao_money` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>