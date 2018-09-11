<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_share_pay_info`;");
E_C("CREATE TABLE `hhs_share_pay_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT '',
  `message` varchar(1000) DEFAULT '',
  `money` decimal(10,2) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `is_paid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_share_pay_info` values('1','','刷我滴卡，包你满足','630.00','5','0','1452886642','28','0');");
E_D("replace into `hhs_share_pay_info` values('2','','刷我滴卡，包你满足','0.01','5','0','1453057949','79','1');");
E_D("replace into `hhs_share_pay_info` values('3','','刷我滴卡，包你满足','0.01','32','0','1453071347','4','1');");
E_D("replace into `hhs_share_pay_info` values('4','','刷我滴卡，包你满足','0.01','3','0','1453152150','8','0');");
E_D("replace into `hhs_share_pay_info` values('5','','刷我滴卡，包你满足','58.05','21','0','1453683534','6','0');");
E_D("replace into `hhs_share_pay_info` values('6','','刷我滴卡，包你满足','32.99','109','0','1456004840','54','0');");
E_D("replace into `hhs_share_pay_info` values('7','','刷我滴卡，包你满足','40.60','214','0','1456867889','112','0');");
E_D("replace into `hhs_share_pay_info` values('8','','刷我滴卡，包你满足','0.20','82','0','1458281477','21','0');");
E_D("replace into `hhs_share_pay_info` values('9','','刷我滴卡，包你满足','1.00','50','0','1461198628','326','0');");
E_D("replace into `hhs_share_pay_info` values('10','','刷我滴卡，包你满足','1.25','747','0','1462346336','108','0');");
E_D("replace into `hhs_share_pay_info` values('11','','刷我滴卡，包你满足','555.00','413','0','1462810095','672','0');");
E_D("replace into `hhs_share_pay_info` values('12','','刷我滴卡，包你满足','200.02','922','0','1462916489','724','0');");
E_D("replace into `hhs_share_pay_info` values('13','','刷我滴卡，包你满足','5.00','1189','0','1463679559','915','0');");
E_D("replace into `hhs_share_pay_info` values('14','','刷我滴卡，包你满足','15.00','1271','0','1463821378','958','0');");
E_D("replace into `hhs_share_pay_info` values('15','','刷我滴卡，包你满足','0.02','1393','0','1464120812','1047','1');");
E_D("replace into `hhs_share_pay_info` values('16','','刷我滴卡，包你满足','685.50','76','0','1464571959','1177','0');");
E_D("replace into `hhs_share_pay_info` values('17','','刷我滴卡，包你满足','13101.00','1758','0','1465786813','1286','0');");
E_D("replace into `hhs_share_pay_info` values('18','','刷我滴卡，包你满足','2737.40','1818','0','1465855328','1290','0');");
E_D("replace into `hhs_share_pay_info` values('19','','刷我滴卡，包你满足','101.19','2216','0','1467573621','1639','0');");
E_D("replace into `hhs_share_pay_info` values('20','','刷我滴卡，包你满足','1.00','2439','0','1471993532','2787','0');");
E_D("replace into `hhs_share_pay_info` values('21','','刷我滴卡，包你满足','1.00','2461','0','1471993635','2787','0');");
E_D("replace into `hhs_share_pay_info` values('22','','刷我滴卡，包你满足','148.00','3527','0','1473325697','3057','0');");
E_D("replace into `hhs_share_pay_info` values('23','','刷我滴卡，包你满足','0.01','3483','0','1477257150','3722','1');");
E_D("replace into `hhs_share_pay_info` values('24','','刷我滴卡，包你满足','1.02','2870','0','1479759943','4463','1');");
E_D("replace into `hhs_share_pay_info` values('25','','刷我滴卡，包你满足','8.10','4546','0','1479972755','4718','0');");
E_D("replace into `hhs_share_pay_info` values('26','','刷我滴卡，包你满足','0.01','4546','0','1480005379','4720','0');");
E_D("replace into `hhs_share_pay_info` values('27','','刷我滴卡，包你满足','99.00','3642','0','1481819544','5622','0');");
E_D("replace into `hhs_share_pay_info` values('28','','刷我滴卡，包你满足','0.01','4565','0','1483225417','5994','0');");
E_D("replace into `hhs_share_pay_info` values('29','','刷我滴卡，包你满足','0.04','5137','0','1484110091','6188','0');");
E_D("replace into `hhs_share_pay_info` values('30','','刷我滴卡，包你满足','0.01','5525','0','1488806622','6638','0');");

require("../../inc/footer.php");
?>