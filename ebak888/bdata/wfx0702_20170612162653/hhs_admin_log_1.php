<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_admin_log`;");
E_C("CREATE TABLE `hhs_admin_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `log_info` varchar(255) NOT NULL DEFAULT '',
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`),
  KEY `log_time` (`log_time`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_admin_log` values('1','1495045716','1','删除文章: 近期天气','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('2','1495045718','1','删除文章: 520','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('3','1495045722','1','删除文章: 测试测试','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('4','1495045725','1','删除文章: 六一快乐','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('5','1495045727','1','删除文章: 123','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('6','1495045731','1','删除文章: test111111','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('7','1495045736','1','删除文章: 广场页面美化中','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('8','1495045741','1','删除文章: 如何兑换商品','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('9','1495045746','1','删除文章: 测试','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('10','1495045856','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('11','1495045859','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('12','1495045861','1','删除商品分类: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('13','1495045878','1','编辑支付方式: 支付宝','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('14','1495045898','1','编辑支付方式: 微信支付','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('15','1495046012','1','编辑商店设置: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('16','1495046677','1','编辑商店设置: ','0.0.0.0');");
E_D("replace into `hhs_admin_log` values('17','1496963990','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('18','1496964062','1','编辑权限管理: admin','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('19','1496964559','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('20','1496964937','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('21','1496966138','1','编辑广告: PC1','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('22','1496966151','1','编辑广告: PC2','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('23','1496966167','1','编辑广告: PC3','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('24','1496966183','1','编辑广告: PC4','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('25','1496966192','1','编辑广告: PC2','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('26','1496966807','1','编辑文章分类: 首页','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('27','1496966815','1','编辑文章分类: 拼团','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('28','1496966984','1','删除文章: 微营销APP即将上线，现订购APP端7折优惠','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('29','1496966992','1','删除文章: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('30','1496967327','1','删除文章: APP推送测试','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('31','1496967333','1','删除文章: 跨年钜惠，好礼送不停','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('32','1496967476','1','删除文章: 收银系统即将上线','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('33','1496967505','1','编辑文章: 关于我们','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('34','1496967525','1','编辑文章: 广场功能调整中','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('35','1496967554','1','编辑文章: 积分兑换流程','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('36','1496967797','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('37','1496967827','1','编辑商店设置: ','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('38','1496968062','1','编辑支付方式: 微信支付','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('39','1496968320','1','添加供货商管理: 1e21e21e21e','58.219.87.220');");
E_D("replace into `hhs_admin_log` values('40','1497220655','1','编辑广告: 秒杀','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('41','1497220668','1','编辑广告: 抽奖','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('42','1497220675','1','编辑广告: 众筹','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('43','1497220681','1','编辑广告: hanfneg','218.90.35.137');");
E_D("replace into `hhs_admin_log` values('44','1497227163','1','编辑权限管理: admin','218.90.35.137');");

require("../../inc/footer.php");
?>