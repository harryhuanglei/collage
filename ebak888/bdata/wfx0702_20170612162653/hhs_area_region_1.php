<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_area_region`;");
E_C("CREATE TABLE `hhs_area_region` (
  `shipping_area_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `region_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipping_area_id`,`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");
E_D("replace into `hhs_area_region` values('172','1');");
E_D("replace into `hhs_area_region` values('175','1');");
E_D("replace into `hhs_area_region` values('176','1');");
E_D("replace into `hhs_area_region` values('178','1');");
E_D("replace into `hhs_area_region` values('182','1');");
E_D("replace into `hhs_area_region` values('183','1');");
E_D("replace into `hhs_area_region` values('207','1');");
E_D("replace into `hhs_area_region` values('215','1');");
E_D("replace into `hhs_area_region` values('216','3280');");
E_D("replace into `hhs_area_region` values('217','1');");
E_D("replace into `hhs_area_region` values('218','24');");
E_D("replace into `hhs_area_region` values('232','1');");
E_D("replace into `hhs_area_region` values('233','2596');");
E_D("replace into `hhs_area_region` values('233','2597');");
E_D("replace into `hhs_area_region` values('233','2598');");
E_D("replace into `hhs_area_region` values('233','2599');");
E_D("replace into `hhs_area_region` values('233','2600');");
E_D("replace into `hhs_area_region` values('233','2601');");
E_D("replace into `hhs_area_region` values('235','1');");
E_D("replace into `hhs_area_region` values('236','1');");
E_D("replace into `hhs_area_region` values('237','1');");
E_D("replace into `hhs_area_region` values('238','2596');");
E_D("replace into `hhs_area_region` values('238','2597');");
E_D("replace into `hhs_area_region` values('238','2598');");
E_D("replace into `hhs_area_region` values('238','2599');");
E_D("replace into `hhs_area_region` values('238','2600');");
E_D("replace into `hhs_area_region` values('239','1');");

require("../../inc/footer.php");
?>