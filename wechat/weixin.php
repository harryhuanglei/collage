<?php
define('IN_HHS', true);

//error_reporting(0);

require(dirname(__FILE__) . '/../includes/init2.php');

require('callback.php');

$wechatObj = new wechatCallbackapi();

$ecdb -> prefix = $hhs -> prefix;

$wechatObj -> valid($db,$ecdb);

$base_url = 'http://' . $_SERVER['SERVER_NAME'] . '/';

$db -> prefix = $hhs -> prefix;

$wechatObj -> responseMsg($db, $user, $base_url);




?>