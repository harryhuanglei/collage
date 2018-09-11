<?php

define('IN_HHS', true);

define('HHS_ADMIN', true);

require(dirname(__FILE__) . '/includes/init.php');


/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

assign_template();



    $smarty->display('post_sale.dwt');


?>