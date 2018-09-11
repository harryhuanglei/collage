<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_email_sendlist`;");
E_C("CREATE TABLE `hhs_email_sendlist` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `template_id` mediumint(8) NOT NULL,
  `email_content` text NOT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `pri` tinyint(10) NOT NULL,
  `last_send` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_email_sendlist` values('12','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437548996');");
E_D("replace into `hhs_email_sendlist` values('13','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437564299');");
E_D("replace into `hhs_email_sendlist` values('14','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437611528');");
E_D("replace into `hhs_email_sendlist` values('15','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437623505');");
E_D("replace into `hhs_email_sendlist` values('16','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437623739');");
E_D("replace into `hhs_email_sendlist` values('17','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437623756');");
E_D("replace into `hhs_email_sendlist` values('18','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624079');");
E_D("replace into `hhs_email_sendlist` values('19','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624241');");
E_D("replace into `hhs_email_sendlist` values('20','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624302');");
E_D("replace into `hhs_email_sendlist` values('21','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624599');");
E_D("replace into `hhs_email_sendlist` values('22','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624663');");
E_D("replace into `hhs_email_sendlist` values('23','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624890');");
E_D("replace into `hhs_email_sendlist` values('24','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437624905');");
E_D("replace into `hhs_email_sendlist` values('25','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437636130');");
E_D("replace into `hhs_email_sendlist` values('26','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437637233');");
E_D("replace into `hhs_email_sendlist` values('27','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437637244');");
E_D("replace into `hhs_email_sendlist` values('28','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437637256');");
E_D("replace into `hhs_email_sendlist` values('29','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437637270');");
E_D("replace into `hhs_email_sendlist` values('30','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437651910');");
E_D("replace into `hhs_email_sendlist` values('31','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437651921');");
E_D("replace into `hhs_email_sendlist` values('32','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437651929');");
E_D("replace into `hhs_email_sendlist` values('33','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706143');");
E_D("replace into `hhs_email_sendlist` values('34','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706177');");
E_D("replace into `hhs_email_sendlist` values('35','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706197');");
E_D("replace into `hhs_email_sendlist` values('36','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706210');");
E_D("replace into `hhs_email_sendlist` values('37','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706303');");
E_D("replace into `hhs_email_sendlist` values('38','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437706842');");
E_D("replace into `hhs_email_sendlist` values('39','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/www/phpnow/wwwroot/test.xakc.net/vshop/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1437720306');");
E_D("replace into `hhs_email_sendlist` values('40','','6','','0','1','1438235070');");
E_D("replace into `hhs_email_sendlist` values('41','','6','','0','1','1438327593');");
E_D("replace into `hhs_email_sendlist` values('42','','6','','0','1','1438329228');");
E_D("replace into `hhs_email_sendlist` values('43','','6','','0','1','1438329274');");
E_D("replace into `hhs_email_sendlist` values('44','','6','','0','1','1438331334');");
E_D("replace into `hhs_email_sendlist` values('45','','6','','0','1','1438331344');");
E_D("replace into `hhs_email_sendlist` values('46','','6','','0','1','1438331392');");
E_D("replace into `hhs_email_sendlist` values('47','','6','','0','1','1438331420');");
E_D("replace into `hhs_email_sendlist` values('48','','6','','0','1','1438332096');");
E_D("replace into `hhs_email_sendlist` values('49','','6','','0','1','1438332848');");
E_D("replace into `hhs_email_sendlist` values('50','','6','','0','1','1438336310');");
E_D("replace into `hhs_email_sendlist` values('51','','6','','0','1','1438337325');");
E_D("replace into `hhs_email_sendlist` values('52','','6','','0','1','1438337396');");
E_D("replace into `hhs_email_sendlist` values('53','','6','','0','1','1438406896');");
E_D("replace into `hhs_email_sendlist` values('54','','6','','0','1','1438406906');");
E_D("replace into `hhs_email_sendlist` values('55','','6','','0','1','1438406990');");
E_D("replace into `hhs_email_sendlist` values('56','','6','','0','1','1438410031');");
E_D("replace into `hhs_email_sendlist` values('57','','6','','0','1','1438411190');");
E_D("replace into `hhs_email_sendlist` values('58','','6','','0','1','1438411205');");
E_D("replace into `hhs_email_sendlist` values('59','','6','','0','1','1438411866');");
E_D("replace into `hhs_email_sendlist` values('60','','6','','0','1','1438411875');");
E_D("replace into `hhs_email_sendlist` values('61','','6','','0','1','1438412720');");
E_D("replace into `hhs_email_sendlist` values('62','','6','','0','1','1438412729');");
E_D("replace into `hhs_email_sendlist` values('63','','6','','0','1','1438413035');");
E_D("replace into `hhs_email_sendlist` values('64','','6','','0','1','1438420455');");
E_D("replace into `hhs_email_sendlist` values('65','','6','','0','1','1438420465');");
E_D("replace into `hhs_email_sendlist` values('66','','6','','0','1','1438574369');");
E_D("replace into `hhs_email_sendlist` values('67','','6','','0','1','1438582816');");
E_D("replace into `hhs_email_sendlist` values('68','','6','','0','1','1438738494');");
E_D("replace into `hhs_email_sendlist` values('69','','6','','0','1','1438738584');");
E_D("replace into `hhs_email_sendlist` values('70','','6','','0','1','1438739889');");
E_D("replace into `hhs_email_sendlist` values('71','','6','','0','1','1438740065');");
E_D("replace into `hhs_email_sendlist` values('72','','6','','0','1','1438740172');");
E_D("replace into `hhs_email_sendlist` values('73','','6','','0','1','1438740362');");
E_D("replace into `hhs_email_sendlist` values('74','','6','','0','1','1438760391');");
E_D("replace into `hhs_email_sendlist` values('75','','6','','0','1','1447930589');");
E_D("replace into `hhs_email_sendlist` values('76','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448265787');");
E_D("replace into `hhs_email_sendlist` values('77','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448265791');");
E_D("replace into `hhs_email_sendlist` values('78','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448265791');");
E_D("replace into `hhs_email_sendlist` values('79','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448266141');");
E_D("replace into `hhs_email_sendlist` values('80','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448266551');");
E_D("replace into `hhs_email_sendlist` values('81','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448266699');");
E_D("replace into `hhs_email_sendlist` values('82','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448266725');");
E_D("replace into `hhs_email_sendlist` values('83','','6','','0','1','1448266791');");
E_D("replace into `hhs_email_sendlist` values('84','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448266993');");
E_D("replace into `hhs_email_sendlist` values('85','','6','','0','1','1448529599');");
E_D("replace into `hhs_email_sendlist` values('86','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448529693');");
E_D("replace into `hhs_email_sendlist` values('87','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1448530764');");
E_D("replace into `hhs_email_sendlist` values('88','','6','','0','1','1448531523');");
E_D("replace into `hhs_email_sendlist` values('89','','6','','0','1','1448684552');");
E_D("replace into `hhs_email_sendlist` values('90','','6','','0','1','1448976414');");
E_D("replace into `hhs_email_sendlist` values('91','','6','','0','1','1449400934');");
E_D("replace into `hhs_email_sendlist` values('92','','6','','0','1','1449636070');");
E_D("replace into `hhs_email_sendlist` values('93','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/phpstudy/www/pts.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1451096699');");
E_D("replace into `hhs_email_sendlist` values('94','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453109575');");
E_D("replace into `hhs_email_sendlist` values('95','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453109575');");
E_D("replace into `hhs_email_sendlist` values('96','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453109575');");
E_D("replace into `hhs_email_sendlist` values('97','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453109575');");
E_D("replace into `hhs_email_sendlist` values('98','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453194753');");
E_D("replace into `hhs_email_sendlist` values('99','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453271488');");
E_D("replace into `hhs_email_sendlist` values('100','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453443121');");
E_D("replace into `hhs_email_sendlist` values('101','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453443444');");
E_D("replace into `hhs_email_sendlist` values('102','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1165) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1453443614');");
E_D("replace into `hhs_email_sendlist` values('103','','6','','0','1','1453443819');");
E_D("replace into `hhs_email_sendlist` values('104','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1462853556');");
E_D("replace into `hhs_email_sendlist` values('105','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1462975151');");
E_D("replace into `hhs_email_sendlist` values('106','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1462975152');");
E_D("replace into `hhs_email_sendlist` values('107','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1462977420');");
E_D("replace into `hhs_email_sendlist` values('108','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463032301');");
E_D("replace into `hhs_email_sendlist` values('109','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463033352');");
E_D("replace into `hhs_email_sendlist` values('110','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463033352');");
E_D("replace into `hhs_email_sendlist` values('111','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463033352');");
E_D("replace into `hhs_email_sendlist` values('112','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463033352');");
E_D("replace into `hhs_email_sendlist` values('113','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463033352');");
E_D("replace into `hhs_email_sendlist` values('114','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463102718');");
E_D("replace into `hhs_email_sendlist` values('115','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463102718');");
E_D("replace into `hhs_email_sendlist` values('116','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463102718');");
E_D("replace into `hhs_email_sendlist` values('117','','6','<br />\n<b>Parse error</b>:  syntax error, unexpected ''>'' in <b>/home/wwwroot/wfx.hostadmin.com.cn/includes/cls_template.php(1166) : eval()''d code</b> on line <b>1</b><br />\n','0','1','1463103773');");
E_D("replace into `hhs_email_sendlist` values('118','13456789876@139.com','6','','0','1','1480573840');");

require("../../inc/footer.php");
?>