<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_nav`;");
E_C("CREATE TABLE `hhs_nav` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `ctype` varchar(10) DEFAULT NULL,
  `cid` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `ifshow` tinyint(1) NOT NULL,
  `vieworder` tinyint(1) NOT NULL,
  `opennew` tinyint(1) NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `item_icon` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `ifshow` (`ifshow`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_nav` values('2','','0','选购中心','1','2','0','pick_out.php','top','');");
E_D("replace into `hhs_nav` values('3','','0','查看购物车','1','0','0','flow.php','top','');");
E_D("replace into `hhs_nav` values('4','','0','团购商品','1','20','0','group_buy.php','middle','');");
E_D("replace into `hhs_nav` values('6','','0','标签云','1','5','6','tag_cloud.php','top','');");
E_D("replace into `hhs_nav` values('7','','0','免责条款','1','1','0','article.php?id=1','bottom','');");
E_D("replace into `hhs_nav` values('8','','0','隐私保护','1','2','0','article.php?id=2','bottom','');");
E_D("replace into `hhs_nav` values('9','','0','咨询热点','1','3','0','article.php?id=3','bottom','');");
E_D("replace into `hhs_nav` values('10','','0','联系我们','1','4','0','article.php?id=4','bottom','');");
E_D("replace into `hhs_nav` values('11','','0','公司简介','1','5','0','article.php?id=5','bottom','');");
E_D("replace into `hhs_nav` values('12','','0','批发方案','1','6','0','wholesale.php','bottom','');");
E_D("replace into `hhs_nav` values('14','','0','配送方式','1','7','0','myship.php','bottom','');");
E_D("replace into `hhs_nav` values('15','','0','留言板','1','99','0','message.php','middle','');");
E_D("replace into `hhs_nav` values('18','c','4','手机通讯','0','14','0','category.php?id=5','middle','');");
E_D("replace into `hhs_nav` values('21','','0','优惠活动','1','21','0','activity.php','middle','');");
E_D("replace into `hhs_nav` values('23','','0','报价单','1','6','0','quotation.php','top','');");
E_D("replace into `hhs_nav` values('25','','0','积分商城','1','24','0','exchange.php','middle','');");
E_D("replace into `hhs_nav` values('26','c','3','数码配件','0','13','0','category.php?id=3','middle','');");
E_D("replace into `hhs_nav` values('28','a','3','资讯中心','0','102','0','article_cat.php?id=3','middle','');");

require("../../inc/footer.php");
?>