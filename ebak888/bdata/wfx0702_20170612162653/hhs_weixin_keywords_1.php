<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_weixin_keywords`;");
E_C("CREATE TABLE `hhs_weixin_keywords` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `contents` text NOT NULL,
  `pic` varchar(80) NOT NULL,
  `pic_tit` varchar(80) NOT NULL,
  `desc` text NOT NULL,
  `pic_url` varchar(255) NOT NULL,
  `count` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_weixin_keywords` values('112','购物说明','1','2','','','','','','6','1');");
E_D("replace into `hhs_weixin_keywords` values('115','亲亲','11','2','','1466363838149015875.jpg','11','555','www.baidu.com','8','1');");
E_D("replace into `hhs_weixin_keywords` values('117','帮助','帮助','1','亲，请问您有什么可以帮到您的？','','','','','6','1');");

require("../../inc/footer.php");
?>