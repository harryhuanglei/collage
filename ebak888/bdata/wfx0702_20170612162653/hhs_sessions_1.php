<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_sessions`;");
E_C("CREATE TABLE `hhs_sessions` (
  `sesskey` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `expiry` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `adminid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `user_name` varchar(60) NOT NULL,
  `user_rank` tinyint(3) NOT NULL,
  `discount` decimal(3,2) NOT NULL,
  `email` varchar(60) NOT NULL,
  `data` char(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sesskey`),
  KEY `expiry` (`expiry`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8");
E_D("replace into `hhs_sessions` values('7f875939ac932316cd94f6aa93bf7366','1497255242','0','0','112.3.57.72','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('2641ef73825e1d35dc17c7268d916b49','1497255918','1','0','218.90.35.137','wx110','0','1.00','0','a:5:{s:10:\"login_fail\";i:0;s:9:\"last_time\";s:10:\"1497227117\";s:7:\"last_ip\";s:13:\"218.90.35.137\";s:12:\"access_token\";N;s:17:\"access_token_time\";i:1497255910;}');");
E_D("replace into `hhs_sessions` values('1bef99d9792cffbefe1af764e47739f4','1497254332','0','0','122.96.42.201','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");
E_D("replace into `hhs_sessions` values('240b7e88dc895fc139af684aca47c23c','1497256072','0','1','218.90.35.137','0','0','0.00','0','a:4:{s:10:\"admin_name\";s:5:\"admin\";s:11:\"action_list\";s:3:\"all\";s:10:\"last_check\";i:1497227272;s:12:\"suppliers_id\";s:1:\"0\";}');");
E_D("replace into `hhs_sessions` values('bb8c30df665bb49bb2f4585b21813831','1497255150','0','0','122.96.42.201','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('b3681c6a3c087c43aa178892d67f58ab','1497255879','0','0','123.149.5.82','0','0','0.00','0','a:3:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";s:12:\"suppliers_id\";s:1:\"1\";}');");
E_D("replace into `hhs_sessions` values('fd1b9563f1550b1b86337be6713fd705','1497255896','0','0','218.90.35.137','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('7374fca70eddc3432cf5fbbc803131e7','1497255854','0','0','42.156.251.191','0','0','1.00','0','a:5:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";s:10:\"login_fail\";i:0;s:12:\"access_token\";N;s:17:\"access_token_time\";i:1497255854;}');");
E_D("replace into `hhs_sessions` values('a1597e799d6574d866462bbd49194d25','1497255847','0','0','42.156.251.191','0','0','1.00','0','a:3:{s:10:\"login_fail\";i:0;s:12:\"access_token\";N;s:17:\"access_token_time\";i:1497255847;}');");
E_D("replace into `hhs_sessions` values('33f023dcf57757405fe0b387119e49f7','1497255846','0','0','42.156.251.191','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");
E_D("replace into `hhs_sessions` values('868372f4aee57c2c49fec50d245341ff','1497256018','0','1','123.149.5.82','0','0','0.00','0','a:4:{s:10:\"admin_name\";s:5:\"admin\";s:11:\"action_list\";s:3:\"all\";s:10:\"last_check\";i:1497227193;s:12:\"suppliers_id\";s:1:\"0\";}');");
E_D("replace into `hhs_sessions` values('920c344c17348d472c6568993e13a284','1497255832','0','0','42.156.251.208','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");
E_D("replace into `hhs_sessions` values('fb0ce2833d7d0afb03958d0fedcf3fa6','1497255831','0','0','42.156.251.194','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('a7adaf2683efa7eb57c22afe343bda5b','1497255831','0','0','42.156.251.194','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('e5f480c6d8d8a4fd08b99819950f163f','1497255391','0','0','112.3.57.72','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");
E_D("replace into `hhs_sessions` values('0244c04650d1816f55e1f12788720cff','1497254949','0','0','101.226.33.218','0','0','0.00','0','a:0:{}');");
E_D("replace into `hhs_sessions` values('c11592968addf10f5481b67da7dd721f','1497254661','0','0','101.226.33.221','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");
E_D("replace into `hhs_sessions` values('34fb078767538b8a34a321c8480ed9ec','1497255828','0','0','42.156.251.208','0','0','0.00','0','a:2:{s:7:\"from_ad\";i:0;s:7:\"referer\";s:6:\"本站\";}');");

require("../../inc/footer.php");
?>