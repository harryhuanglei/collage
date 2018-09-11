<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_article_cat`;");
E_C("CREATE TABLE `hhs_article_cat` (
  `cat_id` smallint(5) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL DEFAULT '',
  `cat_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `cat_desc` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '50',
  `show_in_nav` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `cat_type` (`cat_type`),
  KEY `sort_order` (`sort_order`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_article_cat` values('14','文章分类','1','','','0','0','0');");
E_D("replace into `hhs_article_cat` values('15','公众号推送信息','1','','','0','0','14');");
E_D("replace into `hhs_article_cat` values('37','APP推送文章','1','','','0','0','14');");
E_D("replace into `hhs_article_cat` values('23','常见问题','1','','','0','0','32');");
E_D("replace into `hhs_article_cat` values('32','新品发布会','1','','','0','0','34');");
E_D("replace into `hhs_article_cat` values('34','广场文章','1','','','0','0','0');");
E_D("replace into `hhs_article_cat` values('38','头条快报','1','','','0','0','0');");

require("../../inc/footer.php");
?>