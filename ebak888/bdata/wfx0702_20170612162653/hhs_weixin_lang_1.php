<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_weixin_lang`;");
E_C("CREATE TABLE `hhs_weixin_lang` (
  `lang_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `lang_name` varchar(64) NOT NULL,
  `lang_value` text NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_weixin_lang` values('1','regmsg','欢迎关注小舍软件，精心做产品，用心做服务是我们的遵旨，我们欢迎您体验小舍的产品，谏言献策，期待与您合作！\r\n产品咨询热线：029-87888753    \r\n投诉建议：029-88662605');");

require("../../inc/footer.php");
?>