<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_shell`;");
E_C("CREATE TABLE `hhs_shell` (
  `code` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

require("../../inc/footer.php");
?>