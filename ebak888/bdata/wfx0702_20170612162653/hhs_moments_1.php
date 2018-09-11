<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_moments`;");
E_C("CREATE TABLE `hhs_moments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '朋友圈ID',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '朋友圈内容',
  `img_01` varchar(255) NOT NULL DEFAULT '' COMMENT '图片路径',
  `img_02` varchar(255) NOT NULL DEFAULT '' COMMENT '图片路径',
  `img_03` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `zan_num` int(10) unsigned DEFAULT '0',
  `comment_num` int(10) unsigned DEFAULT '0',
  `zan_user` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COMMENT='朋友圈表'");
E_D("replace into `hhs_moments` values('1','dawdawd','images/201612/1482083528727630100.jpg','','','2436','1482083528','3','1','4134,4744,3527');");
E_D("replace into `hhs_moments` values('2','dawdawd','images/201612/1482084061401063232.jpg','images/201612/1482084061065809306.jpg','','2436','1482084061','3','5','2436,4134,4744');");
E_D("replace into `hhs_moments` values('3','阿股份呢','images/201612/1482086102735294058.jpg','','','4744','1482086103','2','12','4134,2436');");
E_D("replace into `hhs_moments` values('4','测试排序','images/201612/1482112075229588020.jpg','','','4744','1482112075','2','14','2436,4744');");
E_D("replace into `hhs_moments` values('5','测试','images/201612/1482116585063804918.jpg','','','4011','1482116585','3','13','4011,2436,4698');");
E_D("replace into `hhs_moments` values('6','大大王','images/201612/1482177940184549139.jpg','','','2436','1482177940','2','3','2437,4698');");
E_D("replace into `hhs_moments` values('7','清清浅浅','images/201612/1482181000861695261.jpg','','','3537','1482181001','1','1','2436');");
E_D("replace into `hhs_moments` values('8','好喜欢','images/201612/1482187187345703976.jpg','','','4744','1482187187','2','2','4744,4847');");
E_D("replace into `hhs_moments` values('9','给对方','images/201612/1482187275954371241.jpg','images/201612/1482187346039294957.jpg','','4744','1482187348','3','5','4744,2437,4020');");
E_D("replace into `hhs_moments` values('10','d d q p','images/201612/1482196270804296033.jpg','','','4744','1482196270','2','5','4744,4720');");
E_D("replace into `hhs_moments` values('11','智小忌较他人短，胆弱莫恐旁人长；若问人心终显愧，清得浊念露初良……','images/201612/1482257631624409980.jpg','images/201612/1482257631409541769.jpg','images/201612/1482257632971871697.jpg','2436','1482257635','0','0','');");
E_D("replace into `hhs_moments` values('12','智小忌较他人短，胆弱莫恐旁人长；若问人心终显愧，清得浊念露初良…','images/201612/1482258883422405660.jpg','images/201612/1482258883848042932.jpg','images/201612/1482258883929540034.jpg','2436','1482258885','2','9','4740,4020');");
E_D("replace into `hhs_moments` values('13','规律','images/201612/1482261029839756796.jpg','images/201612/1482261029785547073.jpg','','2436','1482261029','2','0','4740,4626');");
E_D("replace into `hhs_moments` values('14','记录','images/201612/1482302991350824617.jpg','','','3919','1482302992','1','4','3919');");
E_D("replace into `hhs_moments` values('15','好吃要分享','images/201612/1482895669373177842.jpg','','','2540','1482895670','6','0','2540,3919,3642,4762,5077,5089');");
E_D("replace into `hhs_moments` values('16','这个软件怎么样？','images/201701/1483452533500696121.jpg','','','5042','1483452534','1','1','5042');");
E_D("replace into `hhs_moments` values('17','“鸡”会','images/201701/1483524006889321869.jpg','','','4036','1483524007','1','0','5042');");
E_D("replace into `hhs_moments` values('18','测测测','images/201701/1483642222075682232.jpg','','','3793','1483642222','1','0','3919');");
E_D("replace into `hhs_moments` values('19','测试','images/201701/1483642563918211464.jpg','images/201701/1483642564442928041.jpg','images/201701/1483642564024612514.jpg','3793','1483642565','3','1','3793,5077,3026');");
E_D("replace into `hhs_moments` values('20','大家好','images/201701/1483912925897278802.jpg','','','5068','1483912926','1','1','5068');");
E_D("replace into `hhs_moments` values('21','对接','images/201703/1488760926226398389.jpg','images/201703/1488760926256489629.jpg','images/201703/1488760926315974908.jpg','4744','1488760927','0','0','');");
E_D("replace into `hhs_moments` values('22','v换个','images/201703/1488761038585578742.jpg','','','4744','1488761039','0','0','');");
E_D("replace into `hhs_moments` values('23','图谋','images/201703/1488761077382122763.jpg','images/201703/1488761077064763653.jpg','images/201703/1488761077128562296.jpg','4740','1488761078','0','0','');");
E_D("replace into `hhs_moments` values('24','魔女突突','images/201703/1488761116442785051.jpg','images/201703/1488761116950274675.jpg','','4740','1488761117','0','0','');");

require("../../inc/footer.php");
?>