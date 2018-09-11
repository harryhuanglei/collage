<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_brand`;");
E_C("CREATE TABLE `hhs_brand` (
  `brand_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(60) NOT NULL DEFAULT '',
  `brand_logo` varchar(80) NOT NULL DEFAULT '',
  `brand_desc` text NOT NULL,
  `site_url` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT '50',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`brand_id`),
  KEY `is_show` (`is_show`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_brand` values('13','欧莱雅','1471485417064150100.jpg','品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述品牌描述','http://www.ifeng.com','50','1');");
E_D("replace into `hhs_brand` values('22','其他','','','http://','50','1');");

require("../../inc/footer.php");
?>