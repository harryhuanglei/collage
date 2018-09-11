<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_back_goods`;");
E_C("CREATE TABLE `hhs_back_goods` (
  `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `back_id` mediumint(8) unsigned DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_sn` varchar(60) DEFAULT NULL,
  `goods_name` varchar(120) DEFAULT NULL,
  `brand_name` varchar(60) DEFAULT NULL,
  `goods_sn` varchar(60) DEFAULT NULL,
  `is_real` tinyint(1) unsigned DEFAULT '0',
  `send_number` smallint(5) unsigned DEFAULT '0',
  `goods_attr` text,
  PRIMARY KEY (`rec_id`),
  KEY `back_id` (`back_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_back_goods` values('12','11','426','0','','好吃点（测试商品）',NULL,'HHS000426','1','1','');");
E_D("replace into `hhs_back_goods` values('11','10','515','0','','123456',NULL,'HHS000515','1','2','');");
E_D("replace into `hhs_back_goods` values('10','8','426','0','','好吃点（测试商品）',NULL,'HHS000426','1','1','');");
E_D("replace into `hhs_back_goods` values('9','7','363','0','','澳橙',NULL,'HHS000363','1','1','尺寸:11 \n');");
E_D("replace into `hhs_back_goods` values('13','12','425','0','','测试商品',NULL,'HHS000425','1','100','');");
E_D("replace into `hhs_back_goods` values('14','13','505','0','','车厘子团购',NULL,'HHS000505','1','1','产地:美国 \n');");
E_D("replace into `hhs_back_goods` values('15','14','511','0','','灵宝SOD苹果',NULL,'HHS000511','1','1','');");
E_D("replace into `hhs_back_goods` values('16','15','463','0','','拼团 井藤汉方植物酵素大麦若叶青汁 3g*20袋',NULL,'HHS000463','1','1','');");
E_D("replace into `hhs_back_goods` values('17','16','465','0','','SKINFOOD番茄臻彩水乳礼盒 180ml+140ml+20ml+20ml',NULL,'HHS000465','1','1','');");

require("../../inc/footer.php");
?>