<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_weixin_config`;");
E_C("CREATE TABLE `hhs_weixin_config` (
  `id` int(1) NOT NULL,
  `token` varchar(100) NOT NULL,
  `appid` char(18) NOT NULL,
  `appsecret` char(32) NOT NULL,
  `access_token` char(150) NOT NULL,
  `dateline` int(10) unsigned NOT NULL,
  `nick_name` varchar(250) DEFAULT NULL,
  `send_name` varchar(250) DEFAULT NULL,
  `wishing` varchar(250) DEFAULT NULL,
  `act_name` varchar(250) DEFAULT NULL,
  `remark` varchar(250) DEFAULT NULL,
  `client_ip` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
E_D("replace into `hhs_weixin_config` values('1','xiaoshe','111','1111','H-WKRiZNZXcDGsdCBPA8k7jKEt1olRuGDHL8_P8-9LOuEcTmiAWhObvblezRKoK2_LEKaAvDOP-_d99lZzEHqPgmAlRjBKvpUmbu3cMm3IY','1440940167','小舍软件','小舍软件','周年庆典','周年庆典','周年庆典','');");

require("../../inc/footer.php");
?>