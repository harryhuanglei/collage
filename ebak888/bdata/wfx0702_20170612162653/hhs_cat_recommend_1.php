<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_cat_recommend`;");
E_C("CREATE TABLE `hhs_cat_recommend` (
  `cat_id` smallint(5) NOT NULL,
  `recommend_type` tinyint(1) NOT NULL,
  PRIMARY KEY (`cat_id`,`recommend_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
E_D("replace into `hhs_cat_recommend` values('3','1');");
E_D("replace into `hhs_cat_recommend` values('3','2');");
E_D("replace into `hhs_cat_recommend` values('3','3');");
E_D("replace into `hhs_cat_recommend` values('5','1');");
E_D("replace into `hhs_cat_recommend` values('5','2');");
E_D("replace into `hhs_cat_recommend` values('5','3');");
E_D("replace into `hhs_cat_recommend` values('12','1');");
E_D("replace into `hhs_cat_recommend` values('12','2');");
E_D("replace into `hhs_cat_recommend` values('12','3');");
E_D("replace into `hhs_cat_recommend` values('13','3');");
E_D("replace into `hhs_cat_recommend` values('14','2');");
E_D("replace into `hhs_cat_recommend` values('14','3');");
E_D("replace into `hhs_cat_recommend` values('15','1');");
E_D("replace into `hhs_cat_recommend` values('15','2');");

require("../../inc/footer.php");
?>