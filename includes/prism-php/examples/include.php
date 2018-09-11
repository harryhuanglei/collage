<?php
//初始化
require_once('../lib/client.php');

$url = 'http://127.0.0.1:8080/api';
$key = 'xkg3ydnm';
$secret = '56dygmyhrfuhuwrdst3c';

$c = new prism_client($url, $key, $secret);

//实时打印log
$c->set_logger(function($message){
    echo $message;
    flush();
});
