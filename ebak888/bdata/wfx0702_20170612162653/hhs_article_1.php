<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_article`;");
E_C("CREATE TABLE `hhs_article` (
  `article_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` smallint(5) NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  `author` varchar(30) NOT NULL DEFAULT '',
  `author_email` varchar(60) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `article_type` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `file_url` varchar(255) NOT NULL DEFAULT '',
  `open_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `site_id` varchar(30) DEFAULT NULL,
  `show_type` varchar(10) DEFAULT NULL,
  `suppliers_id` int(11) DEFAULT NULL,
  `wx_url` text,
  PRIMARY KEY (`article_id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_article` values('36','14','关于我们','小舍','','','','1','1','1438381334','data/article/1462472687145539584.jpg','2','','','','','0','');");
E_D("replace into `hhs_article` values('60','23','积分兑换流程','<div align=\"left\">\r\n	<br />\r\n</div>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\" align=\"left\">\r\n	<span>1.</span><span style=\"font-family:宋体;\">在用户中心点击“积分商城”进入积分兑换页面</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\" align=\"left\">\r\n	<span>2.</span><span style=\"font-family:宋体;\">积分兑换页面展示出可供兑换的商品以及需要的积分数值</span><span><br />\r\n</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\" align=\"left\">\r\n	<span> 3.</span><span style=\"font-family:宋体;\">选中兑换商品进入商品详情页面兑换商品</span><span> <br />\r\n</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"text-indent:0cm;\" align=\"left\">\r\n	<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 4.</span><span style=\"font-family:宋体;\">进入兑换流程页面提交数据</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\" align=\"left\">\r\n	<span>5.</span><span style=\"font-family:宋体;\">数据提交后可查阅支付的订单信息</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\" align=\"left\">\r\n	<span>6.</span><span style=\"font-family:宋体;\">微信推送消息给用户</span> \r\n</p>\r\n<p class=\"MsoListParagraph\" style=\"margin-left:18.0pt;text-indent:0cm;\">\r\n	<span><br />\r\n</span> \r\n</p>','','','','0','1','1481790315','','0','','',NULL,NULL,NULL,'');");
E_D("replace into `hhs_article` values('59','34','广场功能调整中','<p>\r\n	广场功能调整中\r\n</p>\r\n<p>\r\n	<br />\r\n</p>','','','','0','1','1476826439','','0','','',NULL,NULL,NULL,'');");

require("../../inc/footer.php");
?>