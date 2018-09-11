<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_settlement_action`;");
E_C("CREATE TABLE `hhs_settlement_action` (
  `action_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `settlement_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action_user` varchar(30) NOT NULL DEFAULT '',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `action_note` text NOT NULL,
  `log_time` int(11) unsigned NOT NULL DEFAULT '0',
  `settlement_sn` varchar(30) DEFAULT NULL COMMENT '结算编号',
  PRIMARY KEY (`action_id`),
  KEY `settlement_id` (`settlement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_settlement_action` values('1','4','平台','3','','1470938842','2016080861003');");
E_D("replace into `hhs_settlement_action` values('2','3','平台','3','','1470938897','2016080832206');");
E_D("replace into `hhs_settlement_action` values('3','3','平台','4','','1471107898','2016080832206');");
E_D("replace into `hhs_settlement_action` values('4','2','平台','3','','1471107988','2016080802148');");
E_D("replace into `hhs_settlement_action` values('5','1','小太阳','2','','1471759231','2016080868728');");
E_D("replace into `hhs_settlement_action` values('6','1','平台','3','','1471759277','2016080868728');");
E_D("replace into `hhs_settlement_action` values('7','1','平台','4','','1471759306','2016080868728');");
E_D("replace into `hhs_settlement_action` values('8','1','小太阳','5','','1471759322','2016080868728');");
E_D("replace into `hhs_settlement_action` values('9','1','平台','6','','1471759336','2016080868728');");
E_D("replace into `hhs_settlement_action` values('10','7','小太阳','2','fsdf','1471893207','2016082372258');");
E_D("replace into `hhs_settlement_action` values('11','7','平台','3','出生地','1473192173','2016082372258');");
E_D("replace into `hhs_settlement_action` values('12','7','平台','4','是多少','1473192198','2016082372258');");
E_D("replace into `hhs_settlement_action` values('13','7','小太阳','5','','1473192214','2016082372258');");
E_D("replace into `hhs_settlement_action` values('14','12','平台','3','d','1476841081','2016091973127');");
E_D("replace into `hhs_settlement_action` values('15','12','平台','4','','1476907437','2016091973127');");
E_D("replace into `hhs_settlement_action` values('16','17','平台','3','','1476911328','2016102052367');");
E_D("replace into `hhs_settlement_action` values('17','13','小太阳','2','','1477335662','2016102032739');");
E_D("replace into `hhs_settlement_action` values('18','20','平台','3','','1477649641','2016102797270');");
E_D("replace into `hhs_settlement_action` values('19','21','hhh','2','','1478538637','2016110753327');");
E_D("replace into `hhs_settlement_action` values('20','21','平台','3','','1479238092','2016110753327');");
E_D("replace into `hhs_settlement_action` values('21','22','平台','3','','1479238102','2016110780003');");
E_D("replace into `hhs_settlement_action` values('22','19','测试店铺勿删','5','','1479239115','2016102578114');");
E_D("replace into `hhs_settlement_action` values('23','19','平台','6','','1479239139','2016102578114');");
E_D("replace into `hhs_settlement_action` values('24','19','测试店铺勿删','7','','1479239152','2016102578114');");
E_D("replace into `hhs_settlement_action` values('25','10','平台','3','','1479339737','2016090188710');");
E_D("replace into `hhs_settlement_action` values('26','23','张老三的买卖','2','','1480301536','2016112531022');");
E_D("replace into `hhs_settlement_action` values('27','23','平台','3','','1480301556','2016112531022');");
E_D("replace into `hhs_settlement_action` values('28','23','平台','4','','1480301567','2016112531022');");
E_D("replace into `hhs_settlement_action` values('29','23','张老三的买卖','5','','1480301586','2016112531022');");
E_D("replace into `hhs_settlement_action` values('30','23','平台','6','','1480301605','2016112531022');");
E_D("replace into `hhs_settlement_action` values('31','23','张老三的买卖','7','收到','1480301625','2016112531022');");
E_D("replace into `hhs_settlement_action` values('32','13','平台','3','','1481316939','2016102032739');");
E_D("replace into `hhs_settlement_action` values('33','28','平台','3','','1481319451','2016112833060');");
E_D("replace into `hhs_settlement_action` values('34','13','平台','4','','1481433902','2016102032739');");
E_D("replace into `hhs_settlement_action` values('35','25','小太阳','2','','1481932248','2016112675312');");
E_D("replace into `hhs_settlement_action` values('36','28','平台','4','','1481936281','2016112833060');");
E_D("replace into `hhs_settlement_action` values('37','18','小太阳','5','','1482005705','2016102585306');");
E_D("replace into `hhs_settlement_action` values('38','18','平台','6','','1482107039','2016102585306');");
E_D("replace into `hhs_settlement_action` values('39','30','平台','3','','1483400081','2016121901717');");
E_D("replace into `hhs_settlement_action` values('40','25','平台','3','','1483556679','2016112675312');");
E_D("replace into `hhs_settlement_action` values('41','25','平台','4','','1483556690','2016112675312');");
E_D("replace into `hhs_settlement_action` values('42','25','平台','4','','1483556691','2016112675312');");
E_D("replace into `hhs_settlement_action` values('43','25','平台','4','','1483556691','2016112675312');");
E_D("replace into `hhs_settlement_action` values('44','25','小太阳','5','','1483568427','2016112675312');");
E_D("replace into `hhs_settlement_action` values('45','25','平台','6','666','1483568495','2016112675312');");
E_D("replace into `hhs_settlement_action` values('46','25','小太阳','7','333','1483568508','2016112675312');");
E_D("replace into `hhs_settlement_action` values('47','32','平台','3','ddd','1483593480','2017010551441');");
E_D("replace into `hhs_settlement_action` values('48','32','平台','4','dd','1483593537','2017010551441');");
E_D("replace into `hhs_settlement_action` values('49','32','小太阳','5','','1483593549','2017010551441');");
E_D("replace into `hhs_settlement_action` values('50','32','平台','6','','1483593575','2017010551441');");
E_D("replace into `hhs_settlement_action` values('51','32','小太阳','7','','1483593599','2017010551441');");
E_D("replace into `hhs_settlement_action` values('52','34','测试店铺勿删','5','','1489433559','2017021086442');");
E_D("replace into `hhs_settlement_action` values('53','34','平台','6','123','1489433684','2017021086442');");
E_D("replace into `hhs_settlement_action` values('54','34','测试店铺勿删','7','0000','1489433706','2017021086442');");

require("../../inc/footer.php");
?>